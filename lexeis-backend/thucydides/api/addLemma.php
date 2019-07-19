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

$lemma = $data["lemma"];
$short_def = $data["shortDef"];
$long_def_id = 0;
$part_of_speech = $data["pos"];
$semantic_group = $data["semanticGroups"];
$root = $data["roots"];
$compounds = $data["compoundParts"];
$frequency_all = 0;
$has_illustration_bool = boolval(json_decode($data["hasIllustration"]));

$has_illustration = 0;
if ($has_illustration_bool) {
  $has_illustration = 1;
}

$illustration_alt = $data["caption"];
$illustration_caption = $data["caption"];
$bibliography_text = $data["bibliography"];


if ($has_illustration_bool) {
  //$illustration = $data["illustration"];
  // file upload adapted from https://www.w3schools.com/php/php_file_upload.asp

  // storage directory for assets
  $storage_dir = __DIR__ . "/../assets/illustrations/";
  $f = $_FILES["illustration"];

  $upload_result = uploadImage($storage_dir, $f, unaccentedBetacode($lemma));
} else {
  $upload_result = array(
    "success" => true,
    "location" => "",
  );
}

$illustration_source = "";

// If upload failed, return error
if (!$upload_result["success"]) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "Failed to upload image: " . $upload_result["message"];

  $db->close();
  echoResult($return);
  return;
}

$illustration_source = $upload_result["location"];

// if lemma already exists, don't add it
if (getNumMatches($db, "lemmata", $lemma) > 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "Lemma $lemma already exists.";

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

  // create lemma
  $msg = "Failed to add lemma.";
  $qp1 = "lemma,short_def,long_def_id,part_of_speech,semantic_group,root,compounds,frequency_all,has_illustration,illustration_source,illustration_alt,illustration_caption,bibliography_text,assigned,status,deleted";
  $qp2 = "?,?,?,?,?,?,?,?,?,?,?,?,?,0,0,0";
  $bind = "ssissssiissss";
  $query = "INSERT INTO lemmata(" . $qp1 . ") VALUES (" . $qp2 . ");";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param($bind, $lemma,$short_def,$long_def_id,$part_of_speech,$semantic_group,$root,$compounds,$frequency_all,$has_illustration,$illustration_source,$illustration_alt,$illustration_caption,$bibliography_text);
  $stmt->execute();
  $lemmaid = $stmt->insert_id;

  $msg = "Failed to add search info.";
  addSearchLemmata($db, $lemma, $lemmaid, false);

  // Add compounds
  $msg = "Failed to add compound lemma link.";
  addCompoundParts($db, $data["compoundParts"], $lemmaid);

  // Add roots
  $msg = "Failed to add stem lemma link.";
  addRoots($db, $data["roots"], $lemmaid);

  // Add semantic groups
  $msg = "Failed to add semantic lemma link.";
  addSemanticGroups($db, $data["semanticGroups"], $lemmaid);

  // Update compounds linked to this lemma (if any)
  $msg = "Failed to update compounds associated with new lemma.";
  $query = "UPDATE compounds SET lemma_in_dict=1 WHERE compound=?";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param("s", $lemma);
  $stmt->execute();

  // Update roots linked to this lemma (if any)
  $msg = "Failed to update roots associated with new lemma.";
  $query = "UPDATE roots SET lemma_in_dict=1 WHERE root=?";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception('mysql error.');
  }
  $stmt->bind_param("s", $lemma);
  $stmt->execute();

  // log change
  $msg = "Failed to record operation in change log.";
  changeLogAtomic($db, 6, $lemmaid, "", "");
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

// If this was successful, update the set of valid alpha combos, as there may be a new one.
if (!$return["isError"]) {
  updateAlphaCombos($db);
}

$db->close();
echoResult($return);
?>
