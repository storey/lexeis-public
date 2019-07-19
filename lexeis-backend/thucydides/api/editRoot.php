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

//$data = get_data();
// for this we are sending a file with FormData, so we use _POST
$data = $_POST;

$old_root = $data["item"];

// get matching roots from the database
$databaseMatches = getMatchesList($db, "roots", $old_root);

// if root does not exist, don't edit it
if (count($databaseMatches) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "No roots match $old_root";

  $db->close();
  echoResult($return);
  return;
}

$row = $databaseMatches[0];

// if new root already exists, don't edit it
if (array_key_exists("newItem", $data)) {
  $newRoot = $data["newItem"];

  if (getNumMatches($db, "roots", $newRoot) > 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "Root \"$newRoot\" already exists.";

    $db->close();
    echoResult($return);
    return;
  }
}

// Edit root
$root_id = $row["root_index"];


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
    $root = $data["newItem"];

    // Determine whether this lemma is in the dictionary
    $in_dict = 0;
    if (getNumMatches($db, "lemmata", $root) > 0) {
      $in_dict = 1;
    }

    $msg = "Failed to update root lemma.";
    $query = "UPDATE roots SET root=?, lemma_in_dict=? WHERE root_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('sii', $root, $in_dict, $root_id);
    $stmt->execute();

    $msg = "Failed to record root lemma update operation in change log.";
    changeLogAtomic($db, 26, $root_id, $old_root, $root);
  }

  if (array_key_exists("description", $data)) {
    $desc = $data["description"];
    $old = $row["description"];

    $msg = "Failed to update root description.";
    $query = "UPDATE roots SET description=? WHERE root_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $desc, $root_id);
    $stmt->execute();

    $msg = "Failed to record description update operation in change log.";
    changeLogAtomic($db, 27, $root_id, $old, $desc);
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
