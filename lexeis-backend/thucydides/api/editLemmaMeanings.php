<?php
// Edit the specific meanings of multiple textual occurrences of a lemma
// At once

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

$lemma = $data["lemma"];
$changes = json_decode($data["changes"]);

// If there are no changes, just skip
if (sizeof($changes) == 0) {
  $return = array(
    'message' => "",
    'isError' => false,
  );

  $db->close();
  echoResult($return);
  return;
}

// Grab long definition for the lemma
$results = getMatchesList($db, "long_definition_by_lemma", $lemma);

// Get list of valid identifiers
$long_def = json_decode($results[0]["long_def"], $assoc=true)[0];
$identifiers = extractIdentifiers($long_def);

// Make sure all changes are valid
foreach ($changes as $c) {
  $section = $c[0];
  $meaning = $c[1];
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
    $return[$errorMessageKey] = "The definition of \"$lemma\" has no subheading \"$meaning\", which is the meaning chosen for section $section. Please check the definition and choose a valid meaning (or leave it blank for now).";

    $db->close();
    echoResult($return);
    return;
  }
}

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

  // Update all changes
  foreach ($changes as $c) {
    $section = $c[0];
    $meaning = $c[1];
    $tokenIndex = intval($c[2]);
    $oldMeaning = getMatchesList($db, "instance_by_index", $tokenIndex)[0]["lemma_meaning"];


    $msg = "Failed to update the meaning in section $section to \"$meaning\".";
    $query = "UPDATE instance_information SET lemma_meaning=? WHERE token_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) { throw new Exception('mysql error.'); }
    $stmt->bind_param('si', $meaning, $tokenIndex);
    $stmt->execute();

    $msg = "Failed to record lemma meaning update operation for section $section in change log.";
    changeLogAtomic($db, 34, $tokenIndex, $oldMeaning, $meaning);
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

// update sections
if (!$return["isError"]) {
  foreach ($changes as $c) {
    $section = $c[0];

    changeSection($db, $section);
  }
}

$db->close();
echoResult($return);
?>
