<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
  'list' => array(),
  'size' => 0,
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// Get user info
$userIDToEmail = getuserIDToEmail(get_mysql_db("", $db="lexeis"));

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);


$page = intval($data["page"]);
$perPage = intval($data["perPage"]);
$showResolved = boolval($data["showResolved"]);

$startIndex = $page*$perPage;

// Get list of matching issues
$issues = array();
$infoArgs = array(
  "resolved" => $showResolved,
);
$pageInfo = getPageInfo($db, "issue_reports", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
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
  array_push($issues, $issue);
}

// Get total number of issues
$numIssues = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any entries for page \"" . $page . "\".";
  $return = array(
    'message' => $errorText,
    'isError' => true,
    'list' => array(),
    'size' => $numIssues,
  );
} else {
  $return = array(
    'message' => "",
    'isError' => false,
    'list' => $issues,
    'size' => $numIssues
  );
}

$db->close();
echoResult($return);
?>
