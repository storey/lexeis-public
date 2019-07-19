<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for administrators
$errorReturn = array(
  'isError' => true,
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

// get database
$db = get_db($dbname=$LEXICON_DB_NAME);

$data = get_data();

$articleID = intval($data["id"]);
$accepted = boolval($data["accepted"]);

$return = array(
  'message' => "Success",
  'isError' => false,
);

if ($accepted) {
  // Get this article's lemma id
  $article = array();
  $lemmaid = -1;
  $old = 0;

  $matches = getMatchesList($db, "long_definitions", $articleID);
  $numRows = sizeof($matches);

  for ($i = 0; $i < $numRows; $i++) {
    $row = $matches[$i];

    $lemmaid = intval($row["lemmaid"]);
    $old = intval($row["old_long_def"]);
  }
  if ($numRows <= 0) {
    // if there is no article with this id
    $return = array(
      'message' => "Request Failed: no article with id " . $articleID,
      'isError' => true,
    );

    $db->close();
    echoResult($return);
    return;
  }

  $lemmas = getMatchesList($db, "lemmata_id", $lemmaid);
  if (sizeof($lemmas) == 0) {
    // if there is no article with this id
    $return = array(
      'message' => "Request Failed: lemma with id " . $lemmaid,
      'isError' => true,
    );

    $db->close();
    echoResult($return);
    return;
  }
  $oldArticleID = $lemmas[0]["long_def_id"];


  $status = 1;
  // if this is just an edit on an existing old definition, status stays 0
  if ($old == 1) {
    $status = 0;
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

    $msg = "Failed to update lemmata long def.";
    $query = "UPDATE lemmata SET long_def_id=?, status=?, assigned=0 WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('iii', $articleID, $status, $lemmaid);
    $stmt->execute();

    $msg = "Failed to update article status.";
    $query = "UPDATE long_definitions SET status=3 WHERE id=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $articleID);
    $stmt->execute();

    $msg = "Failed to record change in change log.";
    changeLogAtomic($db, 4, "$articleID", "$oldArticleID", "");

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
} else {
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

    $msg = "Failed to update article status.";
    $query = "UPDATE long_definitions SET status=2 WHERE id=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $articleID);
    $stmt->execute();

    $msg = "Failed to record change in change log.";
    changeLogAtomic($db, 3, "$articleID", "", "");

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
