<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";
require_once "compilePrepTexts.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for editors
$errorReturn = array(
  'message' => "",
  'isError' => true,
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// get database
$db = get_db($dbname=$LEXICON_DB_NAME);

// for this we are sending a file with FormData, so we use _POST
$data = $_POST;

$tokenIndex = intval($data["tokenIndex"]);

// get matching roots from the database
$databaseMatches = getMatchesList($db, "tokens", $tokenIndex);

// if root does not exist, don't edit it
if (count($databaseMatches) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "No tokens have index $tokenIndex";

  $db->close();
  echoResult($return);
  return;
}

// Store lemma
$lemma = $data["lemma"];

// Make sure new info is valid
// if lemma doesn't exist, invalid
if (array_key_exists("newLemma", $data)) {
  $lemma = $data["newLemma"];

  if (getNumMatches($db, "lemmata", $lemma) == 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "Lemma \"$lemma\" does not exist.";

    $db->close();
    echoResult($return);
    return;
  }
}
// if lemma meaning doesn't exist, invalid
if (array_key_exists("lemmaMeaning", $data) || array_key_exists("newLemma", $data)) {
  $currentLemma = $lemma;
  if (array_key_exists("newLemma", $data)) {
    $currentLemma = $data["newLemma"];
  }
  $meaning = $data["oldMeaning"];
  if (array_key_exists("lemmaMeaning", $data)) {
    $meaning = $data["lemmaMeaning"];
  }

  // Grab long definition for the lemma
  $results = getMatchesList($db, "long_definition_by_lemma", $lemma);

  $identifiers = array();
  if (sizeof($results) > 0) {
    // Get list of valid identifiers
    $long_defs = json_decode($results[0]["long_def"], $assoc=true);
    if (sizeof($long_defs) > 0) {
      $long_def = $long_defs[0];
      $identifiers = extractIdentifiers($long_def);
    }
  }

  $meaning_included = false;
  foreach($identifiers as $i) {
    if ($meaning == $i) {
      $meaning_included = true;
    }
  }
  if ($meaning == "") {
    $meaning_included = true;
  }

  if (!$meaning_included) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "The definition of \"$currentLemma\" has no subheading \"$meaning\". Please check the definition and choose a valid meaning (or leave it blank for now).";

    $db->close();
    echoResult($return);
    return;
  }
}


// Edit the token
$row = $databaseMatches[0];

// ---- Atomic commit
// Default to successful result
$return = array(
  'message' => "",
  'isError' => false,
);

// Error message
$msg = "";
try {
  $db->autocommit(false);

  if (array_key_exists("newLemma", $data)) {
    $lemma = $data["newLemma"];
    $old = $row["lemma"];

    $msg = "Failed to update token lemma.";
    $query = "UPDATE instance_information SET lemma=? WHERE token_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) { throw new Exception('mysql error.'); }
    $stmt->bind_param('si', $lemma, $tokenIndex);
    $stmt->execute();

    $msg = "Failed to record lemma update operation in change log.";
    changeLogAtomic($db, 33, $tokenIndex, $old, $lemma);
  }

  if (array_key_exists("lemmaMeaning", $data)) {
    $meaning = $data["lemmaMeaning"];
    $old = $row["lemma_meaning"];

    $msg = "Failed to update lemma meaning.";
    $query = "UPDATE instance_information SET lemma_meaning=? WHERE token_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) { throw new Exception('mysql error.'); }
    $stmt->bind_param('si', $meaning, $tokenIndex);
    $stmt->execute();

    $msg = "Failed to record lemma meaning update operation in change log.";
    changeLogAtomic($db, 34, $tokenIndex, $old, $meaning);
  }

  if (array_key_exists("context", $data)) {
    $context = $data["context"];
    $old = $row["context_type"];

    $msg = "Failed to update context.";
    $query = "UPDATE instance_information SET context_type=? WHERE token_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) { throw new Exception('mysql error.'); }
    $stmt->bind_param('ii', $context, $tokenIndex);
    $stmt->execute();

    $msg = "Failed to record context update operation in change log.";
    changeLogAtomic($db, 35, $tokenIndex, $old, $context);
  }


  // Update frequency counts
  if (array_key_exists("newLemma", $data)) {
    // Adjust the frequencies of the two lemmata
    $newLemma = $data["newLemma"];
    $oldLemma = $row["lemma"];

    // get lemma info
    $newLemmaInfo = getMatchesList($db, "lemmata", $newLemma)[0];
    $oldLemmaInfo = getMatchesList($db, "lemmata", $oldLemma)[0];

    $newLemmaFrequencyAll = intval($newLemmaInfo["frequency_all"]) + 1;
    $oldLemmaFrequencyAll = intval($oldLemmaInfo["frequency_all"]) - 1;

    // Update old lemma
    $msg = "Failed to update old lemma frequencies.";
    $query = "UPDATE lemmata SET frequency_all=? WHERE lemma=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) { throw new Exception('mysql error.'); }
    $stmt->bind_param('is', $oldLemmaFrequencyAll, $oldLemma);
    $stmt->execute();

    // Update new lemma
    $msg = "Failed to update new lemma frequencies.";
    $query = "UPDATE lemmata SET frequency_all=? WHERE lemma=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) { throw new Exception('mysql error.'); }
    $stmt->bind_param('is', $newLemmaFrequencyAll, $newLemma);
    $stmt->execute();
  }

  $db->commit();
} catch (Exception $e) {
  // On failure, undo everything from try block
  $db->rollback();

  $return = $errorReturn;
  $return[$errorMessageKey] = $msg;
}
// turn autocommit back on
$db->autocommit(true);
// ---- End Atomic Commit

// Update prep text files
if (!$return["isError"]) {
  $sectionCode = getLocationCode($row);
  changeSection($db, $sectionCode);
}
$db->close();
echoResult($return);
?>
