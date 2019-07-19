<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";


// Only allow access for administrators
$errorReturn = array(
  "id" => -1,
  "is_user" => False,
  "email" => "",
  "tstamp" => "",
  "location" => "",
  "resolved" => false,
  "resolved_user" => "",
  "resolved_tstamp" => "",
  "resolved_comment" => "",
);
$errorMessageKey = "comment";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// Get user info
$userIDToEmail = getuserIDToEmail(get_mysql_db("", $db="lexeis"));

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);


$issue_id = intval($data["id"]);


$issue = array();
$matches = getMatchesList($db, "issue_reports", $issue_id);
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];

  $resolved = false;
  if ($row["resolved"] == 1) {
    $resolved = true;
  }

  $is_user = true;
  $email = "";
  $userid = intval($row["userid"]);
  if ($userid == 0) {
    $is_user = false;
    $email = $row["useremail"];
  } else {
    $email = $userIDToEmail[$userid];
  }

  $issue = array(
    "id" => $row["id"],
    "is_user" => $is_user,
    "email" => $email,
    "tstamp" => $row["tstamp"],
    "location" => $row["location"],
    "comment" => $row["comment"],
    "resolved" => $resolved,
    "resolved_user" => $userIDToEmail[intval($row["resolved_user"])],
    "resolved_tstamp" => $row["resolved_tstamp"],
    "resolved_comment" => $row["resolved_comment"],
  );
}

if ($numRows > 0) {
  $return = $issue;
} else {
  $errorText = "Could not find issue " . $issue_id . ".";
  $return = array(
    "id" => -1,
    "is_user" => false,
    "email" => "",
    "tstamp" => "",
    "location" => "",
    "comment" => $errorText,
    "resolved" => false,
    "resolved_user" => "",
    "resolved_tstamp" => "",
    "resolved_comment" => "",
  );
}

$db->close();
echoResult($return);
?>
