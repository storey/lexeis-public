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

$alias = $data["alias"];
$aliasid = intval($data["aliasid"]);

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
  $query = "UPDATE aliases SET deleted=1 WHERE aliasid=?;";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('i', $aliasid);
  $stmt->execute();

  // Delete search terms
  $msg = "Failed to delete search options.";
  $query = "DELETE FROM search_lemmata WHERE aliasid=?;";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('i', $aliasid);
  $stmt->execute();

  $msg = "Failed to record operation in change log.";
  changeLogAtomic($db, 37, $aliasid, "", "");

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
