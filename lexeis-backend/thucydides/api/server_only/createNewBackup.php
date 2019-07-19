<?php
require_once __DIR__ . "/../../../api/database_utils.php";
require_once __DIR__ . "/../../../api/access_guard.php";
require_once __DIR__ . "/../lexiconUtils.php";


write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for editors
$errorReturn = array(
  'message' => "",
  'isError' => true,
);
$errorMessageKey = "message";

$backup_result = createNewBackup(__DIR__ . "/../");

$result = $backup_result["result"];
$output = $backup_result["output"];
$backup_name = $backup_result["name"];

if($result==0){
  /* Success */
  echo("Backup \"$backup_name\" successfully created.");
}
else {
  // Return response
  echo($output);
}
?>
