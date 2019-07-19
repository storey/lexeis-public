<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

$lemma = "err";
if (isset($_GET["lemma"])) {
  $lemma = $_GET["lemma"];
} else {
  echo("Error downloading occurrences. Let an administrator know about this. Sorry!");
  return;
}

$WINDOW_SIZE = 40;

$columnHeaders = array("Location");
$columnHeaders = array_merge($columnHeaders, $TEXT_DIVISIONS);
$columnHeaders = array_merge($columnHeaders, array("Context", "Prev", "Token", "Next", "Full Context"));

$rows = array();

$db = get_db($dbname=$LEXICON_DB_NAME);

$return = array();
$occMatches = getMatchesList($db, "tokens_by_lemma", $lemma);
for ($j = 0; $j < sizeof($occMatches); $j++) {
  $row = $occMatches[$j];

  $loc = getLocationCode($row);
  $locArray = getLocationArr($row);
  $token = $row["token"];
  $index = $row["token_index"];
  $contextType = $INDEX_TO_CONTEXT_NAME[$row["context_type"]];

  $seqIndex = $row["sequence_index"];
  $context = "";
  $prev = "";
  $next = "";
  if ($seqIndex != -1) {
    $query = "SELECT * FROM text_storage WHERE sequence_index>='" . ($seqIndex-$WINDOW_SIZE) . "' AND sequence_index<='" . ($seqIndex+$WINDOW_SIZE) . "';";
    $matches = getMatches($db, $query);
    while($row2 = getNextItem($db, $matches)) {
      $i = $row2["sequence_index"] - $seqIndex;
      $context = $context . $row2["token"] . " ";
      if ($i == -1) {
        $prev = $row2["token"];
      } else if ($i == 1) {
        $next = $row2["token"];
      }
    }
  }

  $csv_row = array($loc);
  $csv_row = array_merge($csv_row, $locArray);
  $csv_row = array_merge($csv_row, array($contextType, $prev, $token, $next, $context));
  array_push($rows, $csv_row);
}

$db->close();
toCSV($columnHeaders, $rows, $lemma);

?>
