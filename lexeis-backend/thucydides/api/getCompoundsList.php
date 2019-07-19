<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for editors
$errorReturn = array(
  'isError' => true,
  'list' => array(),
  'size' => -1,
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);


$page = intval($data["page"]);
$perPage = intval($data["perPage"]);

$startIndex = $page*$perPage;

// Get a page of compounds
$compounds = array();

$infoArgs = array();
$pageInfo = getPageInfo($db, "compounds", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];

  $compound = $row['compound'];

  $myLemma = $row['compound'];
  if ($row['lemma_in_dict'] == 0) {
    $myLemma = "";
  }

  // For listing compounds, we don't need the list of matching lemmata.
  $matchingLemmas = array();

  $c = array(
    'index' => $row['compound_index'],
    'name' => $compound,
    'associatedLemma' => $myLemma,
    'description' => $row['description'],
    'matchingLemmas' => $matchingLemmas,
  );
  array_push($compounds, $c);
}

// Get total number of compounds
$size = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any compounds for page \"" . $page . "\".";
  $return = array(
    'message' => $errorText,
    'isError' => true,
    'list' => array(),
    'size' => $size,
  );
} else {
  $return = array(
    'message' => "",
    'isError' => false,
    'list' => $compounds,
    'size' => $size
  );
}

$db->close();
echoResult($return);
?>
