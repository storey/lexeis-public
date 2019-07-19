<?php
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

// Only allow access for contributors
$errorReturn = array(
  'isError' => true,
);
$errorMessageKey = "message";
if (!accessGuard(1, $errorReturn, $errorMessageKey)) { return; }

// default id for testing
if (!$in_production) {
  $id = 1;
}
$db = get_db($dbname=$LEXICON_DB_NAME);

$data = get_data();

$keep_author = boolval($data["keepAuthor"]);
$old_author = intval($data["oldAuthorID"]);
$custom_author = $data["customAuthor"];
$was_old_def = intval($data["wasOldDef"]);
$long_def_raw = $data["longDefRaw"];
$long_def = $data["longDef"];
$lemmaid = intval($data["lemmaid"]);
$predecessorID = intval($data["predecessor"]);

$return = array(
  'message' => "Success",
  'isError' => false,
);

// If this isn't editing a prior article, just add the new article
if ($predecessorID == 0) {
  $authorid = $id;
  $old_long_def = 0;
  if ($keep_author) {
    $authorid = $old_author;
    $old_long_def = $was_old_def;
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

    $msg = "Failed to add article.";
    $query = "INSERT INTO long_definitions (long_def_raw,long_def,old_long_def,authorid,custom_author,lemmaid,status,later_draft_id) VALUES (?,?,?,?,?,?,0,0);";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('ssiisi', $long_def_raw, $long_def, $old_long_def, $authorid, $custom_author, $lemmaid);
    $stmt->execute();

    $new_id = $stmt->insert_id;

    $msg = "Failed to record article addition in change log.";
    changeLogAtomic($db, 1, "$new_id", "", "");

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
} else { // We are editing a prior article
  // Get matching lemmata from the database
  $databaseMatches = getMatchesList($db, "long_definitions", $predecessorID);

  if (count($databaseMatches) == 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "No articles with id $predecessorID";

    $db->close();
    echoResult($return);
    return;
  }

  $row = $databaseMatches[0];
  $old_author = $row["authorid"];
  $old_custom_author = $row["custom_author"];
  $old_lemma_id = $row["lemmaid"];

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

    $msg = "Failed to add article.";
    $query = "INSERT INTO long_definitions (long_def_raw,long_def,old_long_def,authorid,custom_author,lemmaid,status,later_draft_id) VALUES (?,?,0,?,?,?,0,0);";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('ssisi', $long_def_raw, $long_def, $old_author, $old_custom_author, $old_lemma_id);
    $stmt->execute();

    $new_id = $stmt->insert_id;
    // Save new article id
    $return["message"] = "$new_id";

    $msg = "Failed to record article addition in change log.";
    changeLogAtomic($db, 1, "$new_id", "", "");

    $msg = "Failed to update old article.";
    $query = "UPDATE long_definitions SET status=1, later_draft_id=? WHERE id=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('ii', $new_id, $predecessorID);
    $stmt->execute();

    $msg = "Failed to record old article update in change log.";
    changeLogAtomic($db, 2, "$predecessorID", "", "$new_id");

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
