<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";


// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
  'changeList' => array(),
  'numChanges' => -1,
);
$errorMessageKey = "message";
if (!accessGuard(3, $errorReturn, $errorMessageKey)) { return; }

// Get user info
$userDB = get_mysql_db("", $db="lexeis");
$userIDToEmail = getuserIDToEmail($userDB);
$userIDToName = getuserIDToName($userDB);

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);


$page = intval($data["page"]);
$perPage = intval($data["perPage"]);
$userID = intval($data["userID"]);
$changeTypeID = intval($data["changeTypeID"]);

$startIndex = $page*$perPage;


// Get list of changes
$changes = array();
$infoArgs = array(
  "userID" => $userID,
  "changeTypeID" => $changeTypeID,
);
$pageInfo = getPageInfo($db, "changelog", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];
  // Get readable version of the context
  $change = array(
    "id" => $row["id"],
    "user" => $userIDToEmail[intval($row["userid"])],
    "tstamp" => $row["tstamp"],
    "change_type" => $row["change_type"],
    "change_type_readable" => $CHANGE_TYPE_TO_STRING[$row["change_type"]],
    "context" => $row["context"],
    "context_readable" => getReadableChangeContext($db, $row["change_type"], $row["context"]),
    "before_value" => $row["before_value"],
    "before_value_readable" => getReadableChangeValue($db, $row["change_type"], $row["before_value"], $userIDToName),
    "after_value" => $row["after_value"],
    "after_value_readable" => getReadableChangeValue($db, $row["change_type"], $row["after_value"], $userIDToName),
  );
  array_push($changes, $change);
}


$numChanges = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any entries for page \"" . $page . "\".";
  $return = array(
    'message' => $errorText,
    'isError' => true,
    'list' => array(),
    'size' => $numChanges,
  );
} else {
  $return = array(
    'message' => "",
    'isError' => false,
    'list' => $changes,
    'size' => $numChanges
  );
}

$db->close();
echoResult($return);
?>
