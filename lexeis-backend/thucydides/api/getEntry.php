<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "lexiconUtils.php";

$userIDToName = getuserIDToName(get_mysql_db("", $db="lexeis"));

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$s = $data["searchQuery"];
$groupsAsNumbers = boolval($data["groupsAsNumbers"]);


$occurrences = array();
$matches = getMatchesList($db, "tokens_by_lemma", $s);
for ($i = 0; $i < sizeof($matches); $i++) {
  $row = $matches[$i];
  $sectionCode = getLocationCode($row);
  $context = $row["context_type"];
  array_push($occurrences, array($sectionCode, $context));
}

// Get matching lemmata
$matches = getMatchesList($db, "lemmata", $s);
$numRows = sizeof($matches);

if ($numRows > 0) {
  $row = $matches[0];


  $longDefInfo = getLongDefInfo($db, $row["long_def_id"], $userIDToName);
  $hasLongDef = $longDefInfo["hasLongDef"];
  $oldLongDef = $longDefInfo["oldLongDef"];
  $longDef = $longDefInfo["longDef"];
  $authorName = $longDefInfo["authorName"];

  // Get groups for this lemma
  $groups = getLemmaGroups($db, $row['lemmaid'], $groupsAsNumbers);
  $compoundParts = $groups["compounds"];
  $roots = $groups["roots"];
  $semanticGroups = $groups["sgs"];

  $freq = getFrequency($db, $row["lemmaid"]);

  if ($row['has_illustration'] == 1) {
    $hasIllustration = True;
  } else {
    $hasIllustration = False;
  }

  $return = array(
    'lemmaid' => $row['lemmaid'],
    'token' => $row['lemma'],
    'search' => array(),
    'shortDef' => $row['short_def'],

    'hasLongDefinition' => $hasLongDef,
    'oldLongDefinition' => $oldLongDef,
    'fullDefinition' => $longDef,
    'authorName' => $authorName,

    'partOfSpeech' => $row['part_of_speech'],
    'semanticGroups' => $semanticGroups,
    'stemType' => $roots,
    'compoundParts' => $compoundParts,
    'frequency' => $freq,

    'hasIllustration' => $hasIllustration,
    'illustrationLink' => $row['illustration_source'],
    'illustrationAlt' => $row['illustration_alt'],
    'illustrationCaption' => $row['illustration_caption'],

    'bibliographyText' => $row['bibliography_text'],

    'occurrences' => $occurrences,

    'status' => $row['status']
  );
}

# if there were no results, return an error
if ($numRows == 0) {
  $errorText = "Could not find a match for lemma \"" . htmlspecialchars($data["searchQuery"]) . "\".";
  $return = array(
    'token' => "ERROR_TOKEN",
    'search' => array(),
    'shortDef' => $errorText,

    'hasLongDefinition' => False,
    'fullDefinition' => array(),
    'authorName' => "",

    'partOfSpeech' => "",
    'semanticGroups' => array(),
    'stemType' => array(),
    'compoundParts' => array(),
    'frequency' => array(),

    'hasKeyPassage' => False,
    'keyPassageLocation' => "",
    'keyPassageText' => "",
    'keyPassageTranslation' => "",

    'hasIllustration' => False,
    'illustrationLink' => "",
    'illustrationAlt' => "",
    'illustrationCaption' => "",

    'bibliographyText' => "",

    'occurrences' => array(),

    'status' => 0,
  );
}

$db->close();
echoResult($return);
?>
