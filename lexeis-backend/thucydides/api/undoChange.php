<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

// Only allow access for editors
$errorReturn = array(
  'message' => "",
  'isError' => true,
);
$errorMessageKey = "message";


// Undo a deletion
function undoDelete($db, $change) {
  global $errorReturn;
  global $errorMessageKey;

  $changeID = $change["id"];
  $changeType = $change["change_type"];
  // Prepare information for delete
  if ($changeType == 7) { // Delete a lemma
    $item_name = "lemma";
    $name_search_type = "lemmata";
    $id_search_type = $name_search_type . "_id";
    $name_key = "lemma";
    $undo_query = "UPDATE lemmata SET deleted=0 WHERE lemmaid=?;";
    $undo_change_type = 6;
  } else if ($changeType == 21) { // Delete a compound group
    $item_name = "compound";
    $name_search_type = "compounds";
    $id_search_type = $name_search_type . "_id";
    $name_key = "compound";
    $undo_query = "UPDATE compounds SET deleted=0 WHERE compound_index=?;";
    $undo_change_type = 20;
  } else if ($changeType == 25) { // Delete a root
    $item_name = "root";
    $name_search_type = "roots";
    $id_search_type = $name_search_type . "_id";
    $name_key = "root";
    $undo_query = "UPDATE roots SET deleted=0 WHERE root_index=?;";
    $undo_change_type = 24;
  } else if ($changeType == 29) { // Delete a semantic group
    $item_name = "semantic group";
    $name_search_type = "semantic_groups";
    $id_search_type = $name_search_type . "_id";
    $name_key = "group_name";
    $undo_query = "UPDATE semantic_groups SET deleted=0 WHERE group_index=?;";
    $undo_change_type = 28;
  } else {
    // Cannot Undo
    $return = $errorReturn;
    $return[$errorMessageKey] = "$changeType is not a valid delete operation.";
    return $return;
  }



  $undo_index = $change["context"];
  // Get the item (lemma/semantic group/etc) with the given id
  $item = getMatchesList($db, $id_search_type, $undo_index)[0];
  // If item is no longer deleted, tell them they don't need to undelete.
  if ($item["deleted"] == 0) {
    $return[$errorMessageKey] = "This $item_name has already been un-deleted. ";
    return $return;
  }

  // If a new item with the same name exists, tell them to make changes
  // to the new one
  $name = $item[$name_key];
  $existingLemma = getNumMatches($db, $name_search_type, $name);
  if ($existingLemma > 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "A new $item_name $name has been created. Make your changes on the new $item_name with this name.";
    return $return;
  }

  // Otherwise, undo deletion

  // ---- Atomic commit
  // Default to successful result
  $return = array(
    'message' => "Change $changeID has been undone.",
    'isError' => false,
  );


  // Error message
  $msg = "";
  try {
    $db->autocommit(false);

    $msg = "Failed to undo delete.";
    $stmt = $db->prepare($undo_query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $undo_index);
    $stmt->execute();

    $msg = "Failed to record operation in change log.";
    changeLogAtomic($db, $undo_change_type, $undo_index, "", "", 1);

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

  return $return;
}





# ==============================================================================
# ==============================================================================

write_headers();
// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }
if (!accessGuard(3, $errorReturn, $errorMessageKey)) { return; }
// get database
$db = get_db($dbname=$LEXICON_DB_NAME);

// Make sure a change with this id exists
$data = get_data();
$changeID = intval($data["id"]);
$changes = getMatchesList($db, "changes", $changeID);
if (sizeof($changes) == 0) {
  $return = $errorReturn;
  $message = "There is no change with ID $changeID.";
  $return[$errorMessageKey] = $message;

  $db->close();
  echoResult($return);
  return;
}

// Get the change
$change = $changes[0];
$changeType = $change["change_type"];

if ($changeType == 7 || $changeType == 21 || $changeType == 25 || $changeType == 29) { // Deletes
  $return = undoDelete($db, $change);
} else {
  // Cannot Undo
  $return = $errorReturn;
  $return[$errorMessageKey] = "This change cannot be undone at the moment.";
}

$db->close();
echoResult($return);
?>
