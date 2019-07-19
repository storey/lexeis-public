<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";


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

$backup_result = createNewBackup();

$result = $backup_result["result"];
$output = $backup_result["output"];
$backup_name = $backup_result["name"];

// For debugging:
// echo("Command: " . $command);
// echo("<br/>");
// echo("Result:");
// var_dump($result);
// echo("<br/>");
// echo("Output:");
// var_dump($output);
// echo("<br/>");
// echo("<br/>");
// echo("<br/>");

if($result==0){
  /* Success */
  $return = array(
    'message' => "Backup \"$backup_name\" successfully created.",
    'isError' => false,
  );
}
else {
  // Return response
  $return = array(
    'message' => $output,
    'isError' => true,
  );
}


echoResult($return);
?>
