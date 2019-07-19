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
$description = $data["description"];

// if compound already exists, don't add it
if (getNumMatches($db, "compounds", $item) > 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "Compound $item already exists.";

  $db->close();
  echoResult($return);
  return;
}

// Add Compound

// Determine whether this lemma is in the dictionary
$in_dict = 0;
if (getNumMatches($db, "lemmata", $item) > 0) {
  $in_dict = 1;
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

  $msg = "Failed to add compound.";
  $query = "INSERT INTO compounds(compound, description, lemma_in_dict, deleted) VALUES (?,?,?,0);";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('ssi', $item, $description, $in_dict);
  $stmt->execute();

  $msg = "Failed to record operation in change log.";
  changeLogAtomic($db, 20, $stmt->insert_id, "", "");

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
