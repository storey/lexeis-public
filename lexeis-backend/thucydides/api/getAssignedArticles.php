<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$page = intval($data["page"]);
$perPage = intval($data["perPage"]);

// Only allow access for contributors
$errorReturn = array(
  'isError' => true,
  'list' => array(),
  'size' => 0,
  'contributors' => array(),
);
$errorMessageKey = "message";
if (!accessGuard(1, $errorReturn, $errorMessageKey)) { return; }

// Default user id for testing
if (!$in_production) {
  $id = 6;
}

// Get database info
$db = get_db($dbname=$LEXICON_DB_NAME);

$startIndex = $page*$perPage;

// Get list of articles assigned to this user
$articles = array();

$infoArgs = array(
  "id" => $id,
);
$pageInfo = getPageInfo($db, "assigned_articles", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];

  if (array_key_exists("old_long_id", $row)) {
    $has_old = $row["old_long_id"];
  } else {
    $has_old = false;
  }

  $draftSubmitted = false;
  if (getNumMatches($db, "long_definitions_by_lemma_author", $row["lemmaid"], $id) > 0) {
    $draftSubmitted = true;
  }

  // users don't need to knwo this
  $assignedName = "";

  $article = array(
    "lemmaid" => $row["lemmaid"],
    "lemma" => $row["lemma"],
    "hasOld" => $has_old,
    "assigned" => $assignedName,
    "draftSubmitted" => $draftSubmitted,
  );
  array_push($articles, $article);
}

// Get total number of aliases
$numArticles = $pageInfo["totalCount"];

if ($numRows == 0) {
  $errorText = "Could not find any entries for page \"" . $page . "\".";
  $return = array(
    'message' => $errorText,
    'isError' => true,
    'list' => array(),
    'size' => $numArticles,
    'contributors' => array(),
  );
} else {
  $return = array(
    'message' => "",
    'isError' => false,
    'list' => $articles,
    'size' => $numArticles,
    'contributors' => array(),
  );
}

$db->close();
echoResult($return);
?>
