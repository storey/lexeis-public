<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
  'users' => array(),
  'changeTypes' => array(),
);
$errorMessageKey = "message";
if (!accessGuard(3, $errorReturn, $errorMessageKey)) { return; }

// default id for testing
if (!$in_production) {
  $id = 1;
}

$lexeisDB = get_mysql_db("", $db="lexeis");
$users = getUsersList($lexeisDB);

// Get change types
$changeTypes = array(
  array(
    "id" => -1,
    "name" => "All",
  ),
);
foreach($CHANGE_TYPE_TO_STRING as $key => $val) {
  $change = array(
    "id" => $key,
    "name" => $val,
  );
  array_push($changeTypes, $change);
}

$return = array(
  'message' => "",
  'isError' => false,
  'users' => $users,
  'changeTypes' => $changeTypes,
);

$lexeisDB->close();
echoResult($return);
?>
