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

$data = get_data();

$index = intval($data["index"]);

$matches = getMatchesList($db, "compound_lemma_link_plus_lemma", $index);
$occurrences = sizeof($matches);
if ($occurrences > 0) {
  $return = $errorReturn;
  $message = "You cannot delete a compound that is linked to a lemma. This compound is linked to $occurrences lemmata, including ";
  if (sizeof($matches) == 1) {
    $message .= $matches[0]["lemma"] . ".";
  } else {
    $message .= $matches[0]["lemma"] . " and " . $matches[1]["lemma"] . ".";
  }
  $return[$errorMessageKey] = $message;

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

  $msg = "Failed to delete compound.";
  $query = "UPDATE compounds SET deleted=1 WHERE compound_index=?;";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param('i', $index);
  $stmt->execute();

  $msg = "Failed to record operation in change log.";
  changeLogAtomic($db, 21, $index, "", "");

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
