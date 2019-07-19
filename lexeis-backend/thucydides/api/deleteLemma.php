<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

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

$data = get_data();

$lemma = $data["lemma"];
$lemmaid = intval($data["lemmaid"]);

$matches = getMatchesList($db, "tokens_by_lemma", $lemma);
$occurrences = sizeof($matches);
if ($occurrences > 0) {
  $return = $errorReturn;
  $message = "You cannot delete a lemma that appears in the text. This lemma appears $occurrences times, including at ";
  if (sizeof($matches) == 1) {
    $loc = getSectionCode($matches[0]);
    $message .= $loc . ".";
  } else {
    $loc0 = getLocationCode($matches[0]);
    $loc1 = getLocationCode($matches[1]);
    $message .= $loc0 . " and " . $loc1 . ".";
  }
  $return[$errorMessageKey] = $message;

  $db->close();
  echoResult($return);
  return;
}

$matches = getMatchesList($db, "aliases_by_lemmaid", $lemmaid);
$occurrences = sizeof($matches);
if ($occurrences > 0) {
  $return = $errorReturn;
  $message = "You cannot delete a lemma that has aliases. This lemma has $occurrences aliases, including ";
  if (sizeof($matches) == 1) {
    $message .= $matches[0]["alias"] . ". ";
  } else {
    $message .= $matches[0]["alias"] . " and " . $matches[1]["alias"] . ". ";
  }
  $message .= "Please delete these aliases and then try again.";
  $return[$errorMessageKey] = $message;

  $db->close();
  echoResult($return);
  return;
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

  $msg = "Failed to delete lemma.";
  $query = "UPDATE lemmata SET deleted=1 WHERE lemmaid=?;";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('i', $lemmaid);
  $stmt->execute();

  $msg = "Failed to record operation in change log.";
  changeLogAtomic($db, 7, $lemmaid, "", "");

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

// If this was successful, update the set of valid alpha combos, as one may be gone.
if (!$return["isError"]) {
  updateAlphaCombos($db);
}

$db->close();
echoResult($return);
?>
