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

// Get a page of semantic groups
$semantic_groups = array();

$infoArgs = array();
$pageInfo = getPageInfo($db, "semantic_groups", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];

  $sg = $row['group_name'];

  // For listing roots, we don't need the list of matching lemmata.
  $matchingLemmas = array();

  $s = array(
    'index' => $row['group_index'],
    'name' => $sg,
    'labelClass' => $row['label_class'],
    'description' => $row['description'],
    'matchingLemmas' => $matchingLemmas,
  );
  array_push($semantic_groups, $s);
}


// Get total number of semantic groups
$size = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any semantic groups for page \"" . $page . "\".";
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
    'list' => $semantic_groups,
    'size' => $size
  );
}

$db->close();
echoResult($return);
?>
