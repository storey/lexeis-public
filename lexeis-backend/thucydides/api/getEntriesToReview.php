<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();


// Only allow access for appropriate individuals
$errorReturn = array(
  'isError' => true,
  'proofList' => array(),
  'finalizeList' => array(),
  'numToProof' => -1,
  'numToFinalize' => -1,
);
$errorMessageKey = "message";

if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

$LEMMATA_TO_DISPLAY = 5;

$db = get_db($dbname=$LEXICON_DB_NAME);

// Get entries to proofread
$numToProof = getNumMatches($db, "entries_to_proof", "");
$allProofs = getMatchesList($db, "entries_to_proof", "");
if ($numToProof == 0) {
  $proofsFull = array();
  $remainderToProof = 0;
} else if ($numToProof <= $LEMMATA_TO_DISPLAY) {
  $proofsFull = $allProofs;
  $remainderToProof = 0;
} else {
  $proofsFull = array_slice($allProofs, 0, $LEMMATA_TO_DISPLAY);
  $remainderToProof = $numToProof - $LEMMATA_TO_DISPLAY;
}
$proofs = array();
if (sizeof($proofsFull) > 0){
  foreach ($proofsFull as $p) {
    array_push($proofs, $p["lemma"]);
  }
}

// Get entries to finalize
$numToFinalize = getNumMatches($db, "entries_to_finalize", "");
$allFinalizes = getMatchesList($db, "entries_to_finalize", "");
if ($numToFinalize == 0) {
  $finalizesFull = array();
  $remainderToFinalize = 0;
} else if ($numToFinalize <= $LEMMATA_TO_DISPLAY) {
  $finalizesFull = $allFinalizes;
  $remainderToFinalize= 0;
} else {
  $finalizesFull = array_slice($allFinalizes, 0, $LEMMATA_TO_DISPLAY);
  $remainderToFinalize = $numToFinalize - $LEMMATA_TO_DISPLAY;
}
$finalizes = array();
if (sizeof($finalizesFull) > 0) {
  foreach ($finalizesFull as $f) {
    array_push($finalizes, $f["lemma"]);
  }
}

$return = array(
  'message' => "",
  'isError' => false,
  'proofList' => $proofs,
  'finalizeList' => $finalizes,
  'numToProof' => $remainderToProof,
  'numToFinalize' => $remainderToFinalize,
);

$db->close();
echoResult($return);
?>
