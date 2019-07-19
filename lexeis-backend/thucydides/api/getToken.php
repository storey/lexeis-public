<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$tokenIndex = intval($data["index"]);


$result = array();
$numMatches = 0;

$matches = getMatchesList($db, "tokens", $tokenIndex);
$numMatches = sizeof($matches);
for ($i = 0; $i < $numMatches; $i++){
  $row = $matches[$i];

  $sectionCode = getLocationCode($row);
  $sectionLink = getLocationCode($row);

  $result = array(
    "tokenIndex" => $row['token_index'],
    "token" => $row['token'],
    "lemma" => $row['lemma'],
    "lemmaMeaning" => $row['lemma_meaning'],
    "context" => $row['context_type'],
    "sectionCode" => $sectionCode,
    "sectionLink" => $sectionLink,
  );
}

if ($numMatches == 0) {
  $result = array(
    "tokenIndex" => -1,
    "token" => "There is no token with index $tokenIndex",
    "lemma" => "",
    "lemmaMeaning" => "",
    "context" => -1,
    "sectionCode" => "",
    "sectionLink" => "",
  );
}

$db->close();
echoResult($result);
?>
