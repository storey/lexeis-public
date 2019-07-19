<?php
// This file contains a function for
require_once __DIR__ . "/master.php";
require_once __DIR__ . "/login_util.php";
// This file sets
// $loggedIn, $id, $email, and $name;
require_once __DIR__ . "/../user_login.php";

// True if user access is at least given level
function hasAccess($accessLevel) {
  global $IN_PRODUCTION;
  global $email;
  return !$IN_PRODUCTION || getUserInfo($email)["accessNumber"] >= $accessLevel;
}

// Require user to be logged in and have an access level of at least $accessLevel
// Return true if access is granted.
// If access is not granted, print out an error with a specified message
function accessGuard($accessLevel, $error, $messageKey) {
  global $IN_PRODUCTION;
  global $loggedIn;

  $accessGranted = true;
  $errorText = "";

  if ($IN_PRODUCTION && !$loggedIn) {
    $errorText = "There has been an error with your login credentials. Please log out and log back in.";
    $accessGranted = false;
  } else if ($accessLevel > 0 && !hasAccess($accessLevel)) {
    $errorText = "You do not have appropriate access to do this.";
    $accessGranted = false;
  }

  if (!$accessGranted) {
    $error[$messageKey] = $errorText;
    $myJSON = json_encode($error);
    echo $myJSON;
  }
  return $accessGranted;
}

?>
