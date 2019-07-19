<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$root = $data["searchQuery"];

// Get list of matching lemmas
$matches = getMatchesList($db, "lemmata_by_root", $root);
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
$matches = getMatchesList($db, "roots", $root);
$numRows = sizeof($matches);
if ($numRows > 0) {
  $row = $matches[0];

  // only include associated lemmas if they are in the dictionary
  $myLemma = $row['root'];
  if ($row['lemma_in_dict'] == 0) {
    $myLemma = "";
  }

  $return = array(
    'index' => $row['root_index'],
    'name' => $row['root'],
    'description' => $row['description'],
    'associatedLemma' => $myLemma,
    'matchingLemmas' => $matchingLemmas,
  );
}

if ($numRows == 0) {
  $errorText = "Could not find a match for root group \"" . htmlspecialchars($data["searchQuery"]) . "\".";
  $return = array(
    'index' => -1,
    'name' => "",
    'description' => $errorText,
    'associatedLemma' => "",
    'matchingLemmas' => array(),
  );
}

$db->close();
echoResult($return);
?>
