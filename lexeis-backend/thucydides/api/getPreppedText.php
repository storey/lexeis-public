<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);


$sectionCode = $data["sectionCode"];

$split = explode(".", $sectionCode);
$invalid = false;
if (sizeof($split) > sizeof($TEXT_DIVISIONS)) {
  $invalid = true;
}
for ($i = 0; $i < sizeof($split); $i++) {
  if (!is_numeric($split[$i])) {
    $invalid = true;
  }
}

if ($invalid) {
  $errorMessage = "Text section \"" . $sectionCode . "\" is invalid.";
  $res = array(
    "sectionCode" => "ERROR",
    "rawHTML" => "",
    "note" => $errorMessage,
  );
  $db->close();
  echoResult($res);
  return;
}



$fileLoc = "prepTexts/" . $sectionCode . ".txt";
$rawHTML = file_get_contents($fileLoc);


if ($rawHTML === False) {
  $errorMessage = "Text section \"" . $sectionCode . "\" does not exist.";
  // $errorMessage = "Text section \"" . $fileLoc . "\" does not exist.";
  $res = array(
    "sectionCode" => "ERROR",
    "rawHTML" => "",
    "note" => $errorMessage,
  );
} else {
  $res = array(
    "sectionCode" => $sectionCode,
    "rawHTML" => $rawHTML,
    "note" => "",
  );
}

$db->close();
echoResult($res);
?>
