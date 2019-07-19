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

$old_sg = $data["item"];

// Get matching semantic groups from the database
$databaseMatches = getMatchesList($db, "semantic_groups", $old_sg);

// If semantic group does not exist, don't edit it
if (count($databaseMatches) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "No semantic groups match $old_sg";

  $db->close();
  echoResult($return);
  return;
}

$row = $databaseMatches[0];

// if new semantic group already exists, don't edit it
if (array_key_exists("newItem", $data)) {
  $newGroup = $data["newItem"];

  if (getNumMatches($db, "semantic_groups", $newGroup) > 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "Semantic Group \"$newGroup\" already exists.";

    $db->close();
    echoResult($return);
    return;
  }
}

// Edit semantic group
$sg_id = $row["group_index"];

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

  if (array_key_exists("newItem", $data)) {
    $sg = $data["newItem"];

    $msg = "Failed to update semantic group name.";
    $query = "UPDATE semantic_groups SET group_name=? WHERE group_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $sg, $sg_id);
    $stmt->execute();

    $msg = "Failed to record semantic group name update operation in change log.";
    changeLogAtomic($db, 30, $sg_id, $old_sg, $sg);
  }

  if (array_key_exists("displayType", $data)) {
    $label = $data["displayType"];
    $old = $row["label_class"];

    $msg = "Failed to update semantic group label.";
    $query = "UPDATE semantic_groups SET label_class=? WHERE group_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $label, $sg_id);
    $stmt->execute();

    $msg = "Failed to record label update operation in change log.";
    changeLogAtomic($db, 31, $sg_id, $old, $label);
  }

  if (array_key_exists("description", $data)) {
    $desc = $data["description"];
    $old = $row["description"];

    $msg = "Failed to update semantic group description.";
    $query = "UPDATE semantic_groups SET description=? WHERE group_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $desc, $sg_id);
    $stmt->execute();

    $msg = "Failed to record description update operation in change log.";
    changeLogAtomic($db, 32, $sg_id, $old, $desc);
  }

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
