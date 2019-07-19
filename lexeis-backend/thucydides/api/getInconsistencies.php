<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

//This script can take a while, so give it time.
set_time_limit(1200);

// given a long definition object, extract all the references in it
function extract_refs($long_def) {
  $ld = $long_def["text"];
  $refs = array();

  // var_dump($long_def);
  foreach ($ld["refList"] as $ref) {
    array_push($refs, $ref["ref"]);
  }
  foreach ($long_def["subList"] as $child) {
    $refs = array_merge($refs, extract_refs($child));
  }
  sort($refs);
  $refs = array_unique($refs);

  return $refs;
}

// Write headers, control access, etc
write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
  'incList' => array(),
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// Get database
$db = get_db($dbname=$LEXICON_DB_NAME);

$data = get_data();


$inconsistenciesType = $data["type"];

// Maximum number of inconsistencies to show
$MAX_NUM = 100;

if ($inconsistenciesType == "ZeroRefLemmata") {
  // Get list of lemmata with zero references
  $query = "SELECT l.lemma as lemma FROM lemmata AS l LEFT JOIN instance_information AS i on l.lemma=i.lemma WHERE l.deleted=0 AND i.token_index IS NULL;";
  $incs = array();
  $num = 0;
  $matches = getMatches($db, $query);
  while(($row = getNextItem($db, $matches)) && $num < $MAX_NUM) {
    $lem = $row["lemma"];
    $num++;
    array_push($incs, array($lem, "Lemma does not occur in the text."));
  }

  if ($num == $MAX_NUM) {
    array_push($incs, array("", "And more (list cut off at 100 lemmata)."));
  }

  $return = array(
    'message' => "",
    'isError' => false,
    'incList' => $incs,
  );
} else if ($inconsistenciesType == "NonexistentLemmata") {
  // Get list of lemmata referenced that don't exist
  $query = "SELECT *, i.lemma as lemma FROM instance_information AS i INNER JOIN text_storage AS t ON i.token_index=t.token_index LEFT JOIN lemmata AS l on i.lemma = l.lemma WHERE l.lemmaid IS null;";
  // lemmaid
  $incs = array();
  $num = 0;
  $matches = getMatches($db, $query);
  while(($row = getNextItem($db, $matches)) && $num < $MAX_NUM) {
    $loc = getLocationCode($row);
    $token = $row["token"];
    $lem = $row["lemma"];
    $num++;
    array_push($incs, array($lem, "Lemma does not have an article. ($token at $loc)"));
  }

  if ($num == $MAX_NUM) {
    array_push($incs, array("", "And more (list cut off at 100 lemmata)."));
  }
  $return = array(
    'message' => "",
    'isError' => false,
    'incList' => $incs,
  );
} else if ($inconsistenciesType == "InvalidArticleRefs") {
  // Get list of invalid article references
  $query = "SELECT l.lemma, d.long_def FROM lemmata AS l INNER JOIN long_definitions AS d ON l.long_def_id=d.id WHERE l.deleted=0 AND d.old_long_def=0;";
  // lemmaid
  $incs = array();
  $matches = getMatches($db, $query);
  $index = 0;
  $num = 0;
  while(($row = getNextItem($db, $matches)) && $num < $MAX_NUM) {
    $long_def_str = $row["long_def"];
    $long_def = json_decode($row["long_def"], $assoc=true)["0"];
    $refs = extract_refs($long_def);
    $bad_refs = array();
    foreach ($refs as $ref) {
      $refParts = explode(".", $ref);
      $locationMatch = getMatchSQL($refParts, "t.");
      $q2 = "SELECT count(*) FROM instance_information AS i INNER JOIN text_storage AS t ON i.token_index = t.token_index WHERE BINARY i.lemma=\"" . $row["lemma"] . "\" AND $locationMatch;";//
      $row2 = getNextItem($db, getMatches($db, $q2));
      $numOccurrences = $row2["count(*)"];
      if ($numOccurrences == 0) {
        array_push($bad_refs, $ref);
      }
    }

    if (sizeof($bad_refs) > 0) {
      $num++;
      array_push($incs, array($row["lemma"], "References to locations where lemma does not occur: " . implode(", ", $bad_refs)));
    }
    $index++;
  }

  if ($num == $MAX_NUM) {
    array_push($incs, array("", "And more (list cut of at 100 lemmata)."));
  }

  $return = array(
    'message' => "",
    'isError' => false,
    'incList' => $incs,
  );
} else {
  $errorText = "Invalid Inconsistencies Type \"" . $inconsistenciesType ."\".";
  $return = array(
    'message' => $errorText,
    'isError' => true,
    'incList' => array(),
  );
}

$db->close();
echoResult($return);
?>
