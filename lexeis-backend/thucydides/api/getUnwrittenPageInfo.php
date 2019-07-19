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
  'contributors' => array(),
  'rootGroups' => array(),
  'semanticGroups' => array(),
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// default id for testing
if (!$in_production) {
  $id = 1;
}

$lexeisDB = get_mysql_db("", $db="lexeis");
$contributors = getContributorsList($lexeisDB);

$db = get_db($dbname=$LEXICON_DB_NAME);

// Get root groups
$rootGroups = array(
  array(
    "id" => -1,
    "name" => "All",
  ),
);
$matches = getMatchesList($db, "roots_available_sorted", "");
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++){
  $row = $matches[$i];
  $root = array(
    "id" => $row["root_index"],
    "name" => $row["root"],
  );
  array_push($rootGroups, $root);
}

// Get semantic groups
$semanticGroups = array(
  array(
    "id" => -1,
    "name" => "All",
  ),
);
$matches = getMatchesList($db, "semantic_groups_available_sorted", "");
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++){
  $row = $matches[$i];

  $sg = array(
    "id" => $row["group_index"],
    "name" => $row["group_name"],
  );
  array_push($semanticGroups, $sg);
}

$return = array(
  'message' => "",
  'isError' => false,
  'contributors' => $contributors,
  'rootGroups' => $rootGroups,
  'semanticGroups' => $semanticGroups,
);

$db->close();
echoResult($return);
?>
