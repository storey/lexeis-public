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
$userOnly = boolval($data["userArticlesOnly"]);

// Only allow access for appropriate individuals
$errorReturn = array(
  'isError' => true,
  'list' => array(),
  'size' => 0,
);
$errorMessageKey = "message";

$accessRequired = 2;
if ($userOnly) {
  $accessRequired = 1;
}
if (!accessGuard($accessRequired, $errorReturn, $errorMessageKey)) { return; }

// Set id to default for testing
if (!$in_production) {
  $id = 1;
}

// Get user info
$userIDToName = getuserIDToName(get_mysql_db("", $db="lexeis"));

$db = get_db($dbname=$LEXICON_DB_NAME);

$startIndex = $page*$perPage;

$articles = array();
$infoArgs = array(
  "userOnly" => $userOnly,
  "id" => $id,
);
$pageInfo = getPageInfo($db, "article_drafts", $perPage, $startIndex, $infoArgs);
$matches = $pageInfo["pageItems"];
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];

  // Sometimes a user will make minor changes but keep the prior author; to keep
  // track of this difference, we include this field.
  $articleModifier = $userIDToName[intval($row["change_user"])];

  $name = $userIDToName[intval($row["aid"])];
  // If there is a custom author, use them as the author
  if ($row["custom_author"] != "") {
    $name = $row["custom_author"];
  }

  $article = array(
    "id" => $row["id"],
    "author" => $name,
    "articleModifier" => $articleModifier,
    "lemmaid" => $row["lemmaid"],
    "lemma" => $row["lemma"],
    "raw" => $row["raw"],
    "longDef" => $row["long_def"],
    "status" => $row["status"],
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
  );
} else {
  $return = array(
    'message' => "",
    'isError' => false,
    'list' => $articles,
    'size' => $numArticles
  );
}

$db->close();
echoResult($return);
?>
