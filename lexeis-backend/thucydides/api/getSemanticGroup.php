<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$semGroup = intval($data["searchQuery"]);

// Get list of matching lemmas
$matches = getMatchesList($db, "lemmata_by_sg", $semGroup);
$matchingLemmas = array();

for ($i = 0; $i < sizeof($matches); $i++) {
  $row = $matches[$i];
  array_push($matchingLemmas, array(
    'token' => $row['lemma'],
    'destination' => $row['lemma'],
    'shortDef' => $row['short_def']
  ));
}

// get the group information
$return = array();
$matches = getMatchesList($db, "semantic_groups_index", $semGroup);
$numRows = sizeof($matches);
if ($numRows > 0) {
  $row = $matches[0];

  $return = array(
    'index' => $row['group_index'],
    'name' => $row['group_name'],
    'labelClass' => $row['label_class'],
    'description' => $row['description'],
    'matchingLemmas' => $matchingLemmas,
  );
}

if ($numRows == 0) {
  $errorText = "Could not find a match for semantic group with index \"" . htmlspecialchars($data["searchQuery"]) . "\".";
  $return = array(
    'index' => -1,
    'name' => "",
    'labelClass' => "",
    'description' => $errorText,
    'matchingLemmas' => array(),
  );
}

$db->close();
echoResult($return);
?>
