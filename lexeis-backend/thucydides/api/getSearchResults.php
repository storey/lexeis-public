<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$searchQ = $data["searchQuery"];

// get matching lemmas
$return = array();
$lemmata = array();
$aliases = array();

$matches = getMatchesList($db, "search_results", $searchQ);
for ($i = 0; $i < sizeof($matches); $i++) {
  $row = $matches[$i];
  $lemmaid = $row['lemmaid'];
  $aliasid = $row['aliasid'];

  if ($lemmaid !== null) {
    array_push($lemmata, $lemmaid);
  } else {
    array_push($aliases, $aliasid);
  }
}

// get only unique lemmas
$lemmata = array_unique($lemmata);
// get only unique aliases
$aliases = array_unique($aliases);

// get lemma short definitions
foreach($lemmata as $lemmaid) {
  $matches = getMatchesList($db, "lemmata_id_not_deleted", $lemmaid);
  $numMatches = sizeof($matches);

  if ($numMatches > 0) {
    $match = $matches[0];
    $shortDef = $match['short_def'];
    $lemma = $match['lemma'];
    array_push($return, array(
      'token' => $lemma,
      'destination' => $lemma,
      'shortDef' => $shortDef,
      'isAlias' => false,
    ));
  }
}

// get aliases + short definitions
foreach($aliases as $aliasid) {
  $matches = getMatchesList($db, "aliases_search", $aliasid);
  $numMatches = sizeof($matches);

  if ($numMatches > 0) {
    $match = $matches[0];
    $lemma = $match['lemma'];
    $alias = $match['alias'];
    $shortDef = "";
    array_push($return, array(
      'token' => $alias,
      'destination' => $lemma,
      'shortDef' => $shortDef,
      'isAlias' => true,
    ));
  }
}

$db->close();
echoResult($return);
?>
