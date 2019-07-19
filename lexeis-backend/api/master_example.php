<?php
// Master.php is not included in this repository because it has sensitive
// information, but here's what it looks like broadly


// $IN_PRODUCTION = false;
$IN_PRODUCTION = true;

$USE_MYSQL = true;
// $USE_MYSQL = false;

if($IN_PRODUCTION) {
  $backup_command = "mysqldump";
  $restore_command = "mysql";
  $db_hostname = "";
  $db_database = "";
  $db_username = "" . $suffix;
  $lexeis = array(
    "hostname" => "HOST",
    "database" => "DB",
    "username" => "USER",
    "password" => "PASS",
    "port" => 8889,
    "backup_command" => $backup_command,
    "restore_command" => $restore_command,
  );
  $thucydides = array(
    "hostname" => "HOST",
    "database" => "DB",
    "username" => "USER",
    "password" => "PASS",
    "port" => 8889,
    "backup_command" => $backup_command,
    "restore_command" => $restore_command,
  );
} else {
  $backup_command = "/Applications/MAMP/Library/bin/mysqldump";
  $restore_command = "/Applications/MAMP/Library/bin/mysql";
  $lexeis = array(
    "hostname" => "127.0.0.1",
    "database" => "lexeisdb",
    "username" => "lexeisadmin",
    "password" => "PASS",
    "port" => 8889,
    "backup_command" => $backup_command,
    "restore_command" => $restore_command,
  );
  $thucydides = array(
    "hostname" => "127.0.0.1",
    "database" => "thuclex",
    "username" => "pericles",
    "password" => "PASS",
    "port" => 8889,
    "backup_command" => $backup_command,
    "restore_command" => $restore_command,
  );
}

$db_data = array(
  "lexeis" => $lexeis,
  "thucydides" => $thucydides,
);
?>
