<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// get database
$db = get_db($dbname=$LEXICON_DB_NAME);

$data = get_data();

$issueID = intval($data["id"]);

$resolverID = $id;
$comment = $data["comment"];

date_default_timezone_set("UTC");
$tstamp = date(DATE_RFC2822);

// -------------------------------

// ---- Atomic commit
// Default to successful result
$return = array(
  'message' => "Success",
  'isError' => false,
);

// Error message
$msg = "";
try {
  $db->autocommit(false);

  $msg = "Failed to resolve issue.";
  $query = "UPDATE issue_reports SET resolved=1, resolved_user=?, resolved_tstamp=?, resolved_comment=? WHERE id=?;";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('issi', $resolverID, $tstamp, $comment, $issueID);
  $stmt->execute();

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
