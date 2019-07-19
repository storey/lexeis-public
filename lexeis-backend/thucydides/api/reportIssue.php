<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../user_login.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$db = get_db($dbname=$LEXICON_DB_NAME);

$data = get_data();

$email = $data["email"];
$userid = 0;
$location = $data["location"];
$comment = $data["comment"];

if ($loggedIn) {
  $email = "";
  $userid = $id;
}

date_default_timezone_set("UTC");
$tstamp = date(DATE_RFC2822);

// -------------------------------

$errorReturn = array(
  'isError' => True,
);
$errorMessageKey = "message";

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

  $msg = "Failed to report issue.";
  $query = "INSERT INTO issue_reports(userid, useremail, tstamp, location, comment, resolved, resolved_user, resolved_tstamp, resolved_comment) VALUES (?,?,?,?,?,0,0,'','');";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('issss', $userid,$email,$tstamp,$location,$comment);
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
