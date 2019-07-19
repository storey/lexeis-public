<?php
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
  'list' => array(),
  'size' => -1,
);
$errorMessageKey = "message";
if (!accessGuard(3, $errorReturn, $errorMessageKey)) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);


$page = intval($data["page"]);
$perPage = intval($data["perPage"]);

$startIndex = $page*$perPage;

// Get full list of backups
$fullList = scandir($BACKUP_DIR, 1);
$backupList = array();
for ($i = 0; $i < sizeof($fullList); $i++) {
  $fname = $fullList[$i];
  if (substr($fname, 0, strlen($BACKUP_PREFIX)) == $BACKUP_PREFIX) {
    array_push($backupList, $fname);
  }
}

// Get list of backups on this page
$backups = array();
$numRows = 0;
for ($i = $startIndex; $i < $startIndex + $perPage && $i < sizeof($backupList); $i++) {
  $numRows++;
  $filename = $backupList[$i];
  $date = substr($filename, strlen($BACKUP_PREFIX));
  $splt = explode("_", explode(".sql", $date)[0]);
  $timestamp = $splt[0] . " at " . $splt[1];
  $b = array(
    'filename' => $filename,
    'timestamp' => $timestamp,
  );
  array_push($backups, $b);
}


$size = sizeof($backupList);

if ($numRows == 0) {
  $errorText = "Could not find any backups for page \"" . $page . "\".";
  $return = array(
    'message' => $errorText,
    'isError' => true,
    'list' => array(),
    'size' => $size,
  );
} else {
  $return = array(
    'message' => "",
    'isError' => false,
    'list' => $backups,
    'size' => $size
  );
}

$db->close();
echoResult($return);
?>
