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

$userID = intval($data["id"]);
$articles = json_decode($data["articles"]);

$return = array(
  'message' => "Success",
  'isError' => false,
);

foreach ($articles as $a) {
  $lemmaid = intval($a);

  $oldAssigned = 0;

  $matches = getMatchesList($db, "lemmata_id_not_deleted", $lemmaid);
  if (sizeof($matches) > 0) {
    $oldAssigned = $matches[0]["assigned"];
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

    $msg = "Failed to assign article.";
    $query = "UPDATE lemmata SET assigned=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('ii', $userID, $lemmaid);
    $stmt->execute();

    $msg = "Failed to record operation in change log.";
    changeLogAtomic($db, 5, $lemmaid, $oldAssigned, $userID);

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
}

$db->close();
echoResult($return);
?>
