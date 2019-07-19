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
$old_status = intval($data["oldStatus"]);

// Get matching lemmata from the database
$databaseMatches = getMatchesList($db, "lemmata", $lemma);

// if lemma does not exist, don't add it
if (count($databaseMatches) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "No lemmas match $lemma";

  $db->close();
  echoResult($return);
  return;
}

$row = $databaseMatches[0];

// Update lemma status
$lemmaid = $row["lemmaid"];
$new_status = $old_status + 1;

// limit status to max status (this variable is defined in lexiconUtils)
if ($new_status > $MAX_STATUS) {
  $new_status = $MAX_STATUS;
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

  $msg = "Failed to update lemma status.";
  $query = "UPDATE lemmata SET status=? WHERE lemmaid=?;";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('ii', $new_status, $lemmaid);
  $stmt->execute();

  $msg = "Failed to record status update operation in change log.";
  changeLogAtomic($db, 19, $lemmaid, $old_status, $new_status);

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

$db->close();
echoResult($return);
?>
