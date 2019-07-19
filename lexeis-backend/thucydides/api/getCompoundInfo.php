<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$compound = $data["searchQuery"];

// Get list of matching lemmas
$matches = getMatchesList($db, "lemmata_by_compound", $compound);
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
$matches = getMatchesList($db, "compounds", $compound);
$numRows = sizeof($matches);
if ($numRows > 0) {
  $row = $matches[0];

  $myLemma = $row['compound'];
  if ($row['lemma_in_dict'] == 0) {
    $myLemma = "";
  }

  $return = array(
    'index' => $row['compound_index'],
    'name' => $row['compound'],
    'associatedLemma' => $myLemma,
    'description' => $row['description'],
    'matchingLemmas' => $matchingLemmas,
  );
}

if ($numRows == 0) {
  $errorText = "Could not find a match for compound part \"" . htmlspecialchars($data["searchQuery"]) . "\".";
  $return = array(
    'index' => -1,
    'name' => "",
    'associatedLemma' => "",
    'description' => $errorText,
    'matchingLemmas' => array(),
  );
}

$db->close();
echoResult($return);
?>
