<?php
// This contains info specific to the given lexicon. Ideally you would need to
// change just this file to adapt for a new lexicon. Also, you'll need to
// Update the XML handling in createLexiconExport.php

$AUTHOR_NAME = "Thucydides";

// Database name
$LEXICON_DB_NAME = "thucydides";

// For interacting with db data in master.php
$DB_DATA_KEY = "thucydides";

$INDEX_TO_CONTEXT_NAME = array(
  "Narrative",
  "Speech (Direct)",
  "Speech (Indirect)",
  "Authorial"
);

// $INDEX_TO_CONTEXT_NAME = array(
//   "Book 1",
//   "Book 2",
//   "Book 3",
//   "Book 4",
//   "Book 5",
//   "Book 6",
//   "Book 7",
//   "Book 8",
// );
$NUM_CONTEXTS = sizeof($INDEX_TO_CONTEXT_NAME);

$LEMMA_STATUS_NAME = array(
  "Unreviewed",
  "Draft",
  "Partially Proofread",
  "Finalized"
);

$LOGIN_CONFIG_FILE = "../../api/thuclex_config.cnf";




// True if we should include some surrounding context for prepared texts
$INCLUDE_SURROUNDING = False;
// The highest sequence number
// SELECT MAX(sequence_index) FROM text_storage;
$MAX_SEQUENCE = 167558;


$TEXT_DIVISIONS = array("Book", "Chapter", "Section");

$TEXT_DIVISIONS_LOWER = array("book", "chapter", "section");

// Given a row, get location specification as an array
function getLocationArr($row) {
  global $TEXT_DIVISIONS_LOWER;
  $arr = array();
  for ($i = 0; $i < sizeof($TEXT_DIVISIONS_LOWER); $i++) {
    array_push($arr, $row[$TEXT_DIVISIONS_LOWER[$i]]);
  }
  return $arr;
}

// Given a row, get location specification as an array, but only first $limit parts
function getLocationSubArr($row, $limit) {
  global $TEXT_DIVISIONS_LOWER;
  $arr = array();
  for ($i = 0; $i < $limit; $i++) {
    array_push($arr, $row[$TEXT_DIVISIONS_LOWER[$i]]);
  }
  return $arr;
}

// Given a row, return the associated location code, e.g. 1.102.1
function getLocationCode($row) {
  return implode(".", getLocationArr($row));
}

// Get match query
// $loc should be an array with the parts in order
// $prefix is the table name
function getMatchSQL($loc, $prefix="") {
  global $TEXT_DIVISIONS_LOWER;

  $arr = array();
  for ($i = 0; $i < sizeof($TEXT_DIVISIONS_LOWER); $i++) {
    array_push($arr, $prefix . $TEXT_DIVISIONS_LOWER[$i] . "='" . $loc[$i] . "'");
  }
  // e.g. "t.book=2 AND t.chapter=10 AND t.section=1"
  return implode(" AND ", $arr);
}

// Get order query
// $prefix is the table name
function getOrderSQL($prefix="") {
  global $TEXT_DIVISIONS_LOWER;

  $arr = array();
  for ($i = 0; $i < sizeof($TEXT_DIVISIONS_LOWER); $i++) {
    array_push($arr, $prefix . $TEXT_DIVISIONS_LOWER[$i] . " ASC");
  }
  // e.g. "t.book ASC, t.chapter ASC, t.section ASC"
  return implode(", ", $arr);
}
