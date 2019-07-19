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

$old_lemma = $data["lemma"];
$lemma = $old_lemma;

// Get matching lemmata from the database
$databaseMatches = getMatchesList($db, "lemmata", $old_lemma);

if (count($databaseMatches) == 0) {
  $return = $errorReturn;
  $return[$errorMessageKey] = "No lemmas match $lemma";

  $db->close();
  echoResult($return);
  return;
}

$row = $databaseMatches[0];

// If new lemma already exists, don't edit it
if (array_key_exists("newLemma", $data)) {
  $newLemma = $data["newLemma"];
  $lemma = $newLemma;

  if (getNumMatches($db, "lemmata", $newLemma) > 0) {
    $return = $errorReturn;
    $return[$errorMessageKey] = "Lemma \"$newLemma\" already exists.";

    $db->close();
    echoResult($return);
    return;
  }
}

// Edit lemma
$lemmaid = $row["lemmaid"];

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

  // New changes mean lemma has to be proofread again.
  if ($row["status"] > 1) {
    $msg = "Failed to update lemma status.";
    $query = "UPDATE lemmata SET status=1 WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $lemmaid);
    $stmt->execute();
  }


  //
  if (array_key_exists("newLemma", $data)) {
    $lemma = $data["newLemma"];

    $msg = "Failed to update lemma.";
    $query = "UPDATE lemmata SET lemma=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $lemma, $lemmaid);
    $stmt->execute();

    // Delete old search term
    $msg = "Failed to delete old ways to search for lemma.";
    $query = "DELETE FROM search_lemmata WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $lemmaid);
    $stmt->execute();


    // add search info
    $msg = "Failed to add search info for new lemma.";
    addSearchLemmata($db, $lemma, $lemmaid, false);

    // Update compounds linked to this lemma (if any)
    $msg = "Failed to update compounds associated with new lemma.";
    $query = "UPDATE compounds SET lemma_in_dict=1 WHERE compound=?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param("s", $lemma);
    $stmt->execute();
    $msg = "Failed to update compounds associated with old lemma.";
    $query = "UPDATE compounds SET lemma_in_dict=0 WHERE compound=?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param("s", $old_lemma);
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
    $msg = "Failed to update roots associated with old lemma.";
    $query = "UPDATE roots SET lemma_in_dict=0 WHERE root=?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param("s", $old_lemma);
    $stmt->execute();

    $msg = "Failed to record compound lemma update operation in change log.";
    changeLogAtomic($db, 8, $lemmaid, $old_lemma, $lemma);
  }

  // Update short definition
  if (array_key_exists("shortDef", $data)) {
    $short_def = $data["shortDef"];
    $old = $row["short_def"];

    $msg = "Failed to update short definition.";
    $query = "UPDATE lemmata SET short_def=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $short_def, $lemmaid);
    $stmt->execute();

    $msg = "Failed to record short definition update in change log.";
    changeLogAtomic($db, 9, $lemmaid, $old, $short_def);
  }

  // Update part of speech
  if (array_key_exists("pos", $data)) {
    $part_of_speech = $data["pos"];
    $old = $row["part_of_speech"];

    $msg = "Failed to update part of speech.";
    $query = "UPDATE lemmata SET part_of_speech=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $part_of_speech, $lemmaid);
    $stmt->execute();

    $msg = "Failed to record short definition update in change log.";
    changeLogAtomic($db, 10, $lemmaid, $old, $part_of_speech);
  }

  // Update semantic groups
  if (array_key_exists("semanticGroups", $data)) {
    $semantic_group = $data["semanticGroups"];
    $old = $row["semantic_group"];

    // Update lemma's semantic groups
    $msg = "Failed to update semantic groups.";
    $query = "UPDATE lemmata SET semantic_group=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $semantic_group, $lemmaid);
    $stmt->execute();

    // Delete old lemma semantic group links
    $msg = "Failed to delete semantic group links.";
    $query = "DELETE FROM semantic_lemma_link WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $lemmaid);
    $stmt->execute();

    // Create new lemma semantic group links
    $msg = "Failed to add new semantic groups links.";
    addSemanticGroups($db, $data["semanticGroups"], $lemmaid);

    $msg = "Failed to record semantic group update in change log.";
    changeLogAtomic($db, 11, $lemmaid, $old, $semantic_group);
  }

  // Update roots
  if (array_key_exists("roots", $data)) {
    $roots = $data["roots"];
    $old = $row["root"];

    // Update lemma's roots
    $msg = "Failed to update roots.";
    $query = "UPDATE lemmata SET root=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $roots, $lemmaid);
    $stmt->execute();

    // Delete old lemma root links
    $msg = "Failed to delete old root links.";
    $query = "DELETE FROM root_lemma_link WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $lemmaid);
    $stmt->execute();

    // Create new lemma root links
    $msg = "Failed to add new root links.";
    addRoots($db, $data["roots"], $lemmaid);

    $msg = "Failed to record root updates in change log.";
    changeLogAtomic($db, 12, $lemmaid, $old, $roots);
  }

  // Update compounds
  if (array_key_exists("compoundParts", $data)) {
    $compounds = $data["compoundParts"];
    $old = $row["compounds"];

    // Update lemma's semantic groups
    $msg = "Failed to update compound parts.";
    $query = "UPDATE lemmata SET compounds=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $compounds, $lemmaid);
    $stmt->execute();

    // Delete old lemma semantic group links
    $msg = "Failed to delete compound part - lemma links.";
    $query = "DELETE FROM compound_lemma_link WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('i', $lemmaid);
    $stmt->execute();

    // Create new lemma semantic group links
    $msg = "Failed to add new compound part - lemma links.";
    addCompoundParts($db, $data["compoundParts"], $lemmaid);

    $msg = "Failed to record compound parts update in change log.";
    changeLogAtomic($db, 13, $lemmaid, $old, $compounds);
  }

  // Update illustration info
  if (array_key_exists("hasIllustration", $data)) {
    $has_illustration_bool = boolval(json_decode($data["hasIllustration"]));
    $old = $row["has_illustration"];

    $has_illustration = 0;
    if ($has_illustration_bool) {
      $has_illustration = 1;
    }

    $msg = "Failed to update whether this lemma has an illustration.";
    $query = "UPDATE lemmata SET has_illustration=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $has_illustration, $lemmaid);
    $stmt->execute();

    $msg = "Failed to record short definition update in change log.";
    changeLogAtomic($db, 14, $lemmaid, $old, $has_illustration);
  }

  // Update part of speech
  if (array_key_exists("caption", $data)) {
    $illustration_alt = $data["caption"];
    $illustration_caption = $data["caption"];
    $old_alt = $row["illustration_alt"];
    $old_cap = $row["illustration_caption"];

    $msg = "Failed to update illustration caption.";
    $query = "UPDATE lemmata SET illustration_alt=?, illustration_caption=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('ssi', $illustration_alt, $illustration_caption, $lemmaid);
    $stmt->execute();

    $msg = "Failed to record caption update in change log.";
    changeLogAtomic($db, 15, $lemmaid, $old_alt, $illustration_alt);
    changeLogAtomic($db, 16, $lemmaid, $old_cap, $illustration_caption);
  }

  // Update Illustration
  if (array_key_exists("illustration", $_FILES)) {
    //$illustration = $data["illustration"];
    // file upload adapted from https://www.w3schools.com/php/php_file_upload.asp

    // storage directory for assets
    $storage_dir = __DIR__ . "/../assets/illustrations/";
    $f = $_FILES["illustration"];

    $upload_result = uploadImage($storage_dir, $f, unaccentedBetacode($lemma));
    if (!$upload_result["success"]) {
      $msg = "Failed to upload image: " . $upload_result["message"] . "; ";
      throw new Exception("mysql error.");
    }

    $illustration_source = $upload_result["location"];
    $old = $row["illustration_source"];

    $msg = "Failed to update image upload.";
    $query = "UPDATE lemmata SET illustration_source=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $illustration_source, $lemmaid);
    $stmt->execute();

    $msg = "Failed to record illustration update in change log.";
    changeLogAtomic($db, 17, $lemmaid, $old, $illustration_source);
  }

  // Update bibliography
  if (array_key_exists("bibliography", $data)) {
    $bibliography_text = $data["bibliography"];
    $old = $row["bibliography_text"];

    $msg = "Failed to update bibliography.";
    $query = "UPDATE lemmata SET bibliography_text=? WHERE lemmaid=?;";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param('si', $bibliography_text, $lemmaid);
    $stmt->execute();

    $msg = "Failed to record bibliography update in change log.";
    changeLogAtomic($db, 18, $lemmaid, $old, $bibliography_text);
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
