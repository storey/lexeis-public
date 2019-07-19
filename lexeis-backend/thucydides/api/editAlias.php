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

$aliasid = intval($data["aliasid"]);

// Get matching lemmata from the database
$databaseMatches = getMatchesList($db, "aliases_id", $aliasid);

if (count($databaseMatches) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "There is no alias with id $aliasid";

  $db->close();
  echoResult($return);
  return;
}

$row = $databaseMatches[0];

// If new alias already exists, don't edit it
if (array_key_exists("newAlias", $data)) {
  $newAlias = $data["newAlias"];

  if (getNumMatches($db, "aliases", $newAlias) > 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "Alias \"$newAlias\" already exists.";

    $db->close();
    echoResult($return);
    return;
  }
}

// If lemma does not exist, don't edit it
if (array_key_exists("lemma", $data)) {
  $newLemma = $data["lemma"];

  if (getNumMatches($db, "lemmata", $newLemma) == 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "Lemma \"$newLemma\" does not exist.";

    $db->close();
    echoResult($return);
    return;
  }
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

  //
  if (array_key_exists("newAlias", $data)) {
    $alias = $data["newAlias"];
    $old = $row["alias"];

    $msg = "Failed to update alias.";
    $query = "UPDATE aliases SET alias=? WHERE aliasid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $alias, $aliasid);
    $stmt->execute();

    // Delete old search term
    $msg = "Failed to delete old ways to search for alias.";
    $query = "DELETE FROM search_lemmata WHERE aliasid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $aliasid);
    $stmt->execute();


    // add search info
    $msg = "Failed to add search info for new alias.";
    addSearchLemmata($db, $alias, $aliasid, true);

    $msg = "Failed to record update operation in change log.";
    changeLogAtomic($db, 38, $aliasid, $old, $alias);
  }

  // Update short definition
  if (array_key_exists("lemma", $data)) {
    $lemmaRow = getMatchesList($db, "lemmata", $data["lemma"])[0];
    $lemmaid = $lemmaRow["lemmaid"];
    $old = $row["lemmaid"];

    $msg = "Failed to update lemma.";
    $query = "UPDATE aliases SET lemmaid=? WHERE aliasid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $lemmaid, $aliasid);
    $stmt->execute();

    $msg = "Failed to record lemma id update in change log.";
    changeLogAtomic($db, 39, $aliasid, $old, $lemmaid);
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
