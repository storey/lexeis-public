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

$alias = $data["alias"];
$lemma = $data["lemma"];

// if alias already exists, don't add it
if (getNumMatches($db, "aliases", $alias) > 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "Alias $alias already exists.";

  $db->close();
  echoResult($return);
  return;
}

// if lemma doesn't exist, don't add alias
$lemmata = getMatchesList($db, "lemmata", $lemma);
if (sizeof($lemmata) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "Lemma $lemma does not exist.";

  $db->close();
  echoResult($return);
  return;
}

$lemmaObj = $lemmata[0];

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

  // create lemma
  $msg = "Failed to add alias.";
  $query = "INSERT INTO aliases(alias,lemmaid, deleted) VALUES (?,?,0);";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param("si", $alias, $lemmaObj["lemmaid"]);
  $stmt->execute();
  $aliasid = $stmt->insert_id;

  $msg = "Failed to add search info.";
  addSearchLemmata($db, $alias, $aliasid, true);

  // log change
  $msg = "Failed to record operation in change log.";
  changeLogAtomic($db, 36, $aliasid, "", "");
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
