<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

// Only allow access for administrators
$errorReturn = array(
  'token' => "ERROR_TOKEN",

  'hasLongDefinition' => False,
  'fullDefinition' => array(),
  'authorName' => "",

  'occurrences' => array(),
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

$userIDToName = getuserIDToName(get_mysql_db("", $db="lexeis"));

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$lemma = $data["searchQuery"];

$occurrences = array();
$matches = getMatchesList($db, "tokens_by_lemma", $lemma);
for ($i = 0; $i < sizeof($matches); $i++) {
  $row = $matches[$i];
  $sectionCode = getLocationCode($row);
  $meaning = $row["lemma_meaning"];
  $index = $row["token_index"];
  $token = $row["token"];
  array_push($occurrences, array($sectionCode, $meaning, $index, $token));
}

$return = array();

$matches = getMatchesList($db, "lemmata", $lemma);
$numRows = sizeof($matches);

if ($numRows > 0) {
  $row = $matches[0];

  $longDefInfo = getLongDefInfo($db, $row["long_def_id"], $userIDToName);
  $hasLongDef = $longDefInfo["hasLongDef"];
  $oldLongDef = $longDefInfo["oldLongDef"];
  $longDef = $longDefInfo["longDef"];
  $authorName = $longDefInfo["authorName"];

  $return = array(
    'token' => $row['lemma'],
    'message' => "",

    'hasLongDefinition' => $hasLongDef,
    'oldLongDefinition' => $oldLongDef,
    'fullDefinition' => $longDef,
    'authorName' => $authorName,

    'occurrences' => $occurrences,
  );
}

# if there were no results, return an error
if ($numRows == 0) {
  $errorText = "Could not find a match for lemma \"" . htmlspecialchars($data["searchQuery"]) . "\".";
  $return = $errorReturn;
  $return[$errorMessageKey] = $errorText;
}

$db->close();
echoResult($return);
?>
