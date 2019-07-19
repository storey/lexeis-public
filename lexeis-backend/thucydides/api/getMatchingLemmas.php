<?php
// Given two letters, find lemmata that start with those letters
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$searchQ = $data["searchQuery"];

$return = array();
$matches = getMatchesList($db, "matching_lemmata", $searchQ);
for ($i = 0; $i < sizeof($matches); $i++) {
  $row = $matches[$i];

  array_push($return, array(
    'token' => $row["lemma"],
    'shortDef' => $row["def"]
  ));
}

$db->close();
echoResult($return);
?>
