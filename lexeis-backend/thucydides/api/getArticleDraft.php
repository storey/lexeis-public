<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

// Only allow access for contributors
$errorReturn = array(
  'id' => -1,
  'author' => '',
  'lemma' => '',
  'longDef' => '',
  'occurrences' => array(),
  'status' => -1,
  'successor' => 0,
);
$errorMessageKey = "raw";
if (!accessGuard(1, $errorReturn, $errorMessageKey)) { return; }

// Get user info
$userIDToName = getuserIDToName(get_mysql_db("", $db="lexeis"));
$userIDToEmail = getuserIDToEmail(get_mysql_db("", $db="lexeis"));

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);


$articleID = intval($data["id"]);

// Get article
$article = array();
$articleAuthorID = -1;
$matches = getMatchesList($db, "long_definitions_article_info", $articleID);
$numRows = sizeof($matches);
for ($i = 0; $i < $numRows; $i++) {
  $row = $matches[$i];

  $articleAuthorID = $row["aid"];

  // immediately break if this is a contributor trying to view another's article.
  if (!hasAccess(2) && $articleAuthorID != $id) {
    break;
  }

  $authorString = $userIDToName[intval($articleAuthorID)] . " (" . $userIDToEmail[intval($articleAuthorID)] . ")";

  // If there is a custom author, use them as the author
  if ($row["custom_author"] != "") {
    $name = $row["custom_author"] . ". Submitted by " . $authorString;
  }

  $lemid = intval($row["lemmaid"]);
  $lemma = $row["lemma"];

  // context size to get
  $WINDOW_SIZE = 40;

  $occurrences = getOccurrences($db, $lemma, $WINDOW_SIZE);

  // Sometimes a user will make minor changes but keep the prior author; to keep
  // track of this difference, we include this field.
  $articleModifier = $userIDToName[intval($row["change_user"])];

  $article = array(
    "id" => $row["id"],
    "author" => $authorString,
    "articleModifier" => $articleModifier,
    "lemmaid" => $lemid,
    "lemma" => $lemma,
    "raw" => $row["long_def_raw"],
    "longDef" => $row["long_def"],
    "occurrences" => $occurrences,
    "status" => $row["status"],
    "successor" => $row["later_draft_id"],
  );
}

if ($numRows == 0) {
  $errorText = "Article with ID " . $articleID . " does not exist.";
  $return = $errorReturn;
  $return[$errorMessageKey] = $errorText;
} elseif (!hasAccess(2) && $articleAuthorID != $id) {
    $errorText = "This article draft was written by another contributor, so you do not have permission to view it.";
    $return = $errorReturn;
    $return[$errorMessageKey] = $errorText;
} else {
  $return = $article;
}

$db->close();
echoResult($return);
?>
