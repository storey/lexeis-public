<?php
// This file sets
// $loggedIn, $id, $email, and $name;
require_once "../user_login.php";


require_once "database_utils.php";
require_once "login_util.php";

write_headers();

$userInfo = array(
  "loggedIn" => $loggedIn,
  "firstName" => "",
  "id" => -1,
  "accessLevel" => 0,
);

if ($loggedIn) {
  $uInfo = getUserInfo($email);
  $userInfo["firstName"] = $uInfo["fname"];
  $userInfo["id"] = $uInfo["id"];
  $userInfo["accessLevel"] = $uInfo["accessNumber"];
}

echo(json_encode($userInfo));
?>
