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
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// get database
$db = get_db($dbname=$LEXICON_DB_NAME);

// for this we are sending a file with FormData, so we use _POST
$data = $_POST;

$item = $data["item"];
$label = $data["displayType"];
$description = $data["description"];

// if semantic groups already exists, don't add it
if (getNumMatches($db, "semantic_groups", $item) > 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "Semantic Group \"$item\" already exists.";

  $db->close();
  echoResult($return);
  return;
}

// ---- Atomic commit
// Default to successful result
$return = array(
  'message' => "",
  'isError' => false,
);

// Error message
$msg = "";
try {
  $db->autocommit(false);

  $msg = "Failed to add semantic group.";
  $query = "INSERT INTO semantic_groups(group_name, label_class, description, deleted) VALUES (?,?,?,0);";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('sss', $item, $label, $description);
  $stmt->execute();
  // Save group id
  $group_id = $stmt->insert_id;
  $return["message"] = $group_id;

  $msg = "Failed to record operation in change log.";
  changeLogAtomic($db, 28, $group_id, "", "");

  $db->commit();
} catch (Exception $e) {
  // On failure, undo everything from try block
  $db->rollback();

  $return = $errorReturn;
  $return[$errorMessageKey] = $msg;
}
// turn autocommit back on
$db->autocommit(true);
// ---- End Atomic Commit

$db->close();
echoResult($return);
?>
