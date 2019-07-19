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

// Get this page of aliases
$aliases = array();

$pageInfo = getPageInfo($db, "aliases", $perPage, $startIndex, array());
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
    $row = $matches[$i];

  $a = array(
    'alias' => $row['alias'],
    'lemma' => $row['lemma'],
    'error' => false,
  );
  array_push($aliases, $a);
}

// Get total number of aliases
$size = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any aliases for page \"" . $page . "\".";
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
    'list' => $aliases,
    'size' => $size
  );
}

$db->close();
echoResult($return);
?>
