<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../user_login.php"; // To get user ID
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$userIDToName = getuserIDToName(get_mysql_db("", $db="lexeis"));

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$s = $data["searchQuery"];

// context size to get
$WINDOW_SIZE = 40;

$occurrences = getOccurrences($db, $s, $WINDOW_SIZE);

// Get matching lemmata
$matches = getMatchesList($db, "lemmata", $s);
$numRows = sizeof($matches);

if ($numRows > 0) {
  $row = $matches[0];

  $longDefInfo = getLongDefInfo($db, $row["long_def_id"], $userIDToName);
  $hasLongDef = $longDefInfo["hasLongDef"];
  $oldLongDef = $longDefInfo["oldLongDef"];
  $longDef = $longDefInfo["longDef"];
  $rawLongDef = $longDefInfo["rawLongDef"];
  $authorName = $longDefInfo["authorName"];
  $priorArticle = $longDefInfo["priorArticle"];
  $priorAuthor = $longDefInfo["priorAuthor"];
  $priorAuthorID = $longDefInfo["priorAuthorID"];
  $priorCustomAuthor = $longDefInfo["priorCustomAuthor"];

  // Get groups for this lemma
  $groups = getLemmaGroups($db, $row['lemmaid'], False);
  $compoundParts = $groups["compounds"];
  $roots = $groups["roots"];
  $semanticGroups = $groups["sgs"];

  $freq = getFrequency($db, $row["lemmaid"]);

  if ($row['has_illustration'] == 1) {
    $hasIllustration = True;
  } else {
    $hasIllustration = False;
  }

  $pending = getNumMatches($db, "long_definitions_for_lemmaid", $row['lemmaid']) > 0;

  $assignedToUser = $id == intval($row['assigned']);

  $return = array(
    'token' => $row['lemma'],
    'id' => $row['lemmaid'],
    'search' => array(), // not necessary for article info
    'shortDef' => $row['short_def'],

    'hasLongDefinition' => $hasLongDef,
    'oldLongDefinition' => $oldLongDef,
    'fullDefinition' => $longDef,
    'rawFullDefinition' => $rawLongDef,

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

    'priorArticle' => $priorArticle,
    'priorAuthor' => $priorAuthor,
    'priorAuthorID' => $priorAuthorID,
    'priorCustomAuthor' => $priorCustomAuthor,

    'articlePending' => $pending,
    'assignedToUser' => $assignedToUser,
    'lemmaStatus' => $row['status'],
  );
}

# if there were no results, return an error
if ($numRows == 0) {
  $errorText = "Could not find a match for lemma \"" . htmlspecialchars($data["searchQuery"]) . "\".";
  $return = array(
    'token' => "ERROR_TOKEN",
    'id' => -1,
    'search' => array(),
    'shortDef' => $errorText,

    'hasLongDefinition' => False,
    'fullDefinition' => array(),
    'rawFullDefinition' => '',

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
    'priorArticle' => false,
    'priorAuthor' => "",
    'priorAuthorID' => 0,
    'articlePending' => false,
    'assignedToUser' => false,
    'lemmaStatus' => 0,
  );
}

$db->close();
echoResult($return);
?>
