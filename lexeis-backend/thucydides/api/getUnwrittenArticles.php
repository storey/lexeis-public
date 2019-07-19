<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$page = intval($data["page"]);
$perPage = intval($data["perPage"]);
$getAssignedArticles = boolval($data["getAssigned"]);
$rootFilter = $data["rootFilter"];
$semanticFilter = intval($data["semanticFilter"]);
$freqFilter = intval($data["freqFilter"]);

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
  'list' => array(),
  'size' => -1,
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// default id for testing
if (!$in_production) {
  $id = 1;
}


$lexeisDB = get_mysql_db("", $db="lexeis");
$userIDToName = getuserIDToName($lexeisDB);

$db = get_db($dbname=$LEXICON_DB_NAME);

$startIndex = $page*$perPage;


$articles = array();

$infoArgs = array(
  "assigned" => $getAssignedArticles,
  "root" => $rootFilter,
  "sem" => $semanticFilter,
  "freq" => $freqFilter,
);
$pageInfo = getPageInfo($db, "unwritten_articles", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($j = 0; $j < $numRows; $j++) {
  $row = $matches[$j];

  $long_def_id = $row["long_def_id"];
  $has_old = False;

  $long_defs = getMatchesList($db, "long_definitions", $long_def_id);
  for ($i = 0; $i < sizeof($long_defs); $i++){
    if (intval($long_defs[$i]["old_long_def"]) == 1) {
      $has_old = True;
      break;
    }
  }

  $assignedName = "";
  $assigned = intval($row["assigned"]);
  if ($row["assigned"] > 0) {
    $assignedName = $userIDToName[$assigned];
  }

  $semanticGroup = array();

  $matches2 = getMatchesList($db, "semantic_lemma_link_plus_group", $row["lemmaid"]);
  $numRows2 = sizeof($matches2);
  for ($i = 0; $i < $numRows2; $i++){
    $row2 = $matches2[$i];
    $sg = array(
      "index" => $row2["group_index"],
      "name" => $row2["group_name"],
      "displayType" => $row2["label_class"]
    );
    array_push($semanticGroup, $sg);
  }


  $root = array();
  $matches2 = getMatchesList($db, "root_lemma_link_plus_group", $row["lemmaid"]);
  $numRows2 = sizeof($matches2);
  for ($i = 0; $i < $numRows2; $i++){
    array_push($root, $matches2[$i]["root"]);
  }

  $frequencyAll = $row["frequency_all"];

  $article = array(
    "lemmaid" => $row["lemmaid"],
    "lemma" => $row["lemma"],
    "semanticGroups" => $semanticGroup,
    "root" => $root,
    "freq" => $frequencyAll,
    "hasOld" => $has_old,
    "assigned" => $assignedName,
  );
  array_push($articles, $article);
}


// Get total number of unwritten articles matching the criteria
$numArticles = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any entries for page \"" . $page . "\".";
  $return = array(
    'message' => $errorText,
    'isError' => true,
    'list' => array(),
    'size' => $numArticles,
  );
} else {
  $return = array(
    'message' => "",
    'isError' => false,
    'list' => $articles,
    'size' => $numArticles,
  );
}

$db->close();
echoResult($return);
?>
