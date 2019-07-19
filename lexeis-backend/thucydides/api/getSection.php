<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$code = escapeString($db, $data["locationSpec"]);

$splt = explode(".", $code);

// Get list of tokens in this section
$condition = getMatchSQL($splt);
$textQuery = "SELECT * FROM text_storage WHERE $condition ORDER BY true_word_index ASC;";

$words = getMatches($db, $textQuery);
$sectionWords = array();
$numWords = 0;
while($row = getNextItem($db, $words)) {
  array_push($sectionWords, array(
    "tokenIndex" => -1,
    "token" => getProperlyCapitalizedToken($row),
    "lemma" => "",
    "lemmaMeaning" => "",
    "context" => -1,
  ));
  $numWords += 1;
}

// Section does not exist
if ($numWords == 0) {
  $db->close();
  $errorMessage = "Section " . htmlspecialchars($code) . " does not exist.";
  $result = array(
    "sectionCode" => "ERROR",
    "tokens" => array(),
    "note" => $errorMessage,
  );

  $myJSON = json_encode(array($result));

  echo $myJSON;
  return;
}

$condition = getMatchSQL($splt, "t.");
$lemmaQuery = "SELECT * FROM instance_information AS i INNER JOIN text_storage AS t on i.token_index = t.token_index WHERE $condition ORDER BY t.true_word_index ASC;";
$lemmas = getMatches($db, $lemmaQuery);
while($row = getNextItem($db, $lemmas)) {
  $index = intval($row['true_word_index']) - 1;
  $sectionWords[$index]['tokenIndex'] = $row['token_index'];
  $sectionWords[$index]['lemma'] = $row['lemma'];
  $sectionWords[$index]['lemmaMeaning'] = $row['lemma_meaning'];
  $sectionWords[$index]['context'] = $row['context_type'];
}

$sectionObj = array(
  "sectionCode" => $code,
  "tokens" => $sectionWords,
  "note" => "",
);

$result = array($sectionObj);

$db->close();
echoResult($result);
?>
