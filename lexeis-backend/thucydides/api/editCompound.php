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

$old_compound = $data["item"];

// Get matching compounds from the database
$databaseMatches = getMatchesList($db, "compounds", $old_compound);

// if compound does not exist, don't edit it
if (count($databaseMatches) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "No compound parts match $old_compound";

  $db->close();
  echoResult($return);
  return;
}

$row = $databaseMatches[0];

// if new compound already exists, don't edit it
if (array_key_exists("newItem", $data)) {
  $newCompound = $data["newItem"];

  if (getNumMatches($db, "compounds", $newCompound) > 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "Compound \"$newCompound\" already exists.";

    $db->close();
    echoResult($return);
    return;
  }
}

// Edit compound
$compound_id = $row["compound_index"];

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
    $compound = $data["newItem"];

    // Determine whether this lemma is in the dictionary
    $in_dict = 0;
    if (getNumMatches($db, "lemmata", $compound) > 0) {
      $in_dict = 1;
    }

    $msg = "Failed to update compound lemma.";
    $query = "UPDATE compounds SET compound=?, lemma_in_dict=? WHERE compound_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('sii', $compound, $in_dict, $compound_id);
    $stmt->execute();

    $msg = "Failed to record compound lemma update operation in change log.";
    changeLogAtomic($db, 22, $compound_id, $old_compound, $compound);
  }

  if (array_key_exists("description", $data)) {
    $desc = $data["description"];
    $old = $row["description"];

    $msg = "Failed to update compound description.";
    $query = "UPDATE compounds SET description=? WHERE compound_index=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $desc, $compound_id);
    $stmt->execute();

    $msg = "Failed to record description update operation in change log.";
    changeLogAtomic($db, 23, $compound_id, $old, $desc);
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
