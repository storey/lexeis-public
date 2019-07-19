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

// Get database info
$db = get_db($dbname=$LEXICON_DB_NAME);

$data = get_data();

$page = intval($data["page"]);
$perPage = intval($data["perPage"]);

$startIndex = $page*$perPage;

// Get a page of roots
$roots = array();

$infoArgs = array();
$pageInfo = getPageInfo($db, "roots", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];

  $root = $row['root'];

  $myLemma = $row['root'];
  if ($row['lemma_in_dict'] == 0) {
    $myLemma = "";
  }

  // For listing roots, we don't need the list of matching lemmata.
  $matchingLemmas = array();

  $r = array(
    'index' => $row['root_index'],
    'name' => $root,
    'associatedLemma' => $myLemma,
    'description' => $row['description'],
    'matchingLemmas' => $matchingLemmas,
  );
  array_push($roots, $r);
}

// Get total number of roots
$size = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any roots for page \"" . $page . "\".";
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
    'list' => $roots,
    'size' => $size
  );
}

$db->close();
echoResult($return);
?>
