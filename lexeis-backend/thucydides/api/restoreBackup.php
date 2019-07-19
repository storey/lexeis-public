<?php
require_once "../../api/master.php";
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";
require_once "recompilePrepTexts.php";

// =============================================================================

//This script can take a while, so give it time.
set_time_limit(1200);

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for editors
$errorReturn = array(
  'message' => "",
  'isError' => true,
);
$errorMessageKey = "message";
if (!accessGuard(3, $errorReturn, $errorMessageKey)) { return; }


$data = get_data();

$backup_name = $data["filename"];
$filename = $BACKUP_DIR . $backup_name;


// if file doesn't exist, return error
if(!file_exists($filename)) {
  $return = array(
    'message' => "File \"$filename\" does not exist.",
    'isError' => true,
  );

  $db->close();
  echoResult($return);
  return;
}

// Command is normally mysql
$restore_command = $db_data[$DB_DATA_KEY]["restore_command"];
$database = $db_data[$DB_DATA_KEY]["database"];

$command = $restore_command . " --defaults-extra-file=$LOGIN_CONFIG_FILE " . $database . " 2>&1 < " . $filename;

$result = exec($command, $output);


// Update alpha combos and compile texts
$db = get_db($dbname=$LEXICON_DB_NAME);
updateAlphaCombos($db);

if($result==""){
  /* Success */
  $return = array(
    'message' => "Backup \"$backup_name\" successfully restored.",
    'isError' => false,
  );
}
else {
  // Return response
  $return = array(
    'message' => $result,
    'isError' => true,
  );
}

$db->close();
echoResult($return);
?>
