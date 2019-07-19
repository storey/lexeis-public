<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
  'partsOfSpeech' => array(),
  'compoundParts' => array(),
  'rootGroups' => array(),
  'semanticGroups' => array(),
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// Get database info
$db = get_db($dbname=$LEXICON_DB_NAME);

// get parts of speech
$poss = array();
$matches = getMatchesList($db, "pos_available_sorted", "");
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++){
  $row = $matches[$i];
  $pos = $row["part_of_speech"];
  array_push($poss, $pos);
}

// Get compound parts
$compounds = array();
$matches = getMatchesList($db, "compounds_available_sorted", "");
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++){
  $row = $matches[$i];
  $compound = array(
    "id" => $row["compound_index"],
    "name" => $row["compound"],
  );
  array_push($compounds, $compound);
}

// Get root groups
$rootGroups = array();
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
$semanticGroups = array();
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
  'partsOfSpeech' => $poss,
  'compoundParts' => $compounds,
  'rootGroups' => $rootGroups,
  'semanticGroups' => $semanticGroups,
);

$db->close();
echoResult($return);
?>
