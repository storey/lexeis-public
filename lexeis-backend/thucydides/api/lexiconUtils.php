<?php
require_once __DIR__ . "/../../api/master.php";
require_once __DIR__ . "/lexiconInfo.php";

$in_production = False;//True;//

// Location of mysql backups
$BACKUP_DIR = "../backups/";
$BACKUP_PREFIX = "database_backup_";

$MAX_STATUS = 3;

// add something to the changelist
// Change type:
// 1: Add article (no before/after)
// 2: Add edited version of article (after: id of successor article)
// 3: reject article draft (no before/after)
// 4: accept article draft (no before/after)
// 5: assign lemma to a user
// 6: add a lemma
// 7: delete a lemma
// 8: change lemma's lemma
// 9: change lemma's short def
// 10: change lemma's part of speech
// 11: change lemma's semantic groups
// 12: change lemma's roots
// 13: change lemma's compound parts
// 14: change lemma's has illustration
// 15: change lemma's illustration alt text
// 16: change lemma's illustration caption
// 17: change lemma's illustration image
// 18: change lemma's bibliography text
// 19: change lemma's status
// 20: add a compound
// 21: delete a compound
// 22: change a compound's name
// 23: change a compound's description
// 24: add a root
// 25: delete a root
// 26: change a root's name
// 27: change a root's description
// 28: add a semantic group
// 29: delete a semantic group
// 30: change a semantic group's name
// 31: change a semantic group's label type
// 32: change a semantic group's description
// 33: change a token's lemma
// 34: change a token's lemma meaning
// 35: change a token's context
// 36: Add an alias
// 37: Delete an alias
// 38: Change an alias's alias
// 39: Change an alias's lemma
$CHANGE_TYPE_TO_STRING = array(
  1  => "Add new article",
  2  => "Add edited article",
  3  => "Reject article",
  4  => "Accept article",
  5  => "Assign article",
  6  => "Add a lemma",
  7  => "Delete a lemma",
  8  => "Change lemma's lemma",
  9  => "Change lemma's short def",
  10 => "Change lemma's part of speech",
  11 => "Change lemma's semantic groups",
  12 => "Change lemma's roots",
  13 => "Change lemma's compound parts",
  14 => "Change lemma's has illustration",
  15 => "Change lemma's illustration alt text",
  16 => "Change lemma's illustration caption",
  17 => "Change lemma's illustration image",
  18 => "Change lemma's bibliography text",
  19 => "Change lemma's status",
  20 => "Add a compound",
  21 => "Delete a compound",
  22 => "Change a compound's name",
  23 => "Change a compound's description",
  24 => "Add a root",
  25 => "Delete a root",
  26 => "Change a root's name",
  27 => "Change a root's description",
  28 => "Add a semantic group",
  29 => "Delete a semantic group",
  30 => "Change a semantic group's name",
  31 => "Change a semantic group's label type",
  32 => "Change a semantic group's description",
  33 => "Change a token's lemma",
  34 => "Change a token's lemma meaning",
  35 => "Change a token's context",
  36 => "Add an alias",
  37 => "Delete an alias",
  38 => "Change an alias's alias",
  39 => "Change an alias's lemma",
);

// Add to changelog as part of an atomic transaction
function changeLogAtomic($db, $change_type, $context, $before, $after, $isUndo=0) {
  GLOBAL $id;
  date_default_timezone_set("UTC");
  $tstamp = date(DATE_RFC2822);
  $query = "INSERT INTO change_log(userid,tstamp,change_type,context,before_value,after_value,is_undo) VALUES (?,?,?,?,?,?,?);";
  $stmt = $db->prepare($query);
  if (!$stmt) {
    throw new Exception("mysql error.");
  }
  $stmt->bind_param('isisssi', $id, $tstamp, $change_type, $context, $before, $after, $isUndo);
  $stmt->execute();

  return $stmt;
}

// Return the human readable version of a change's context (e.g. instead of lemma
// id, show lemma )
function getReadableChangeContext($db, $type, $context) {
  $ret = "";
  if (1 <= $type && $type <= 4) { // article
    $lem = getMatchesList($db, "lemma_for_article", $context)[0]["lemma"];
    $ret = "lemma: " . $lem . " (article id: " . $context . ")";
  } else if (5 <= $type && $type <= 19) {
    $lem = getMatchesList($db, "lemmata_id", $context)[0]["lemma"];
    $ret = "lemma: " . $lem . " (id: " . $context . ")";
  } else if (20 <= $type && $type <= 23) { // compound
    $c = getMatchesList($db, "compounds_id", $context)[0]["compound"];
    $ret = $c . " (id: " . $context . ")";
  } else if (24 <= $type && $type <= 27) { // root
    $r = getMatchesList($db, "roots_id", $context)[0]["root"];
    $ret = $r . " (id: " . $context . ")";
  } else if (28 <= $type && $type <= 32) { // semantic group
    $s = getMatchesList($db, "semantic_groups_id", $context)[0]["group_name"];
    $ret = $s . " (id: " . $context . ")";
  } else if (33 <= $type && $type <= 35) { // token
    $token = getMatchesList($db, "tokens", $context)[0];

    $ret = $token["token"] . ", " . getLocationCode($token) . " (token index: " . $context. ")";
  } else if (36 <= $type && $type <= 39) { // alias
    $alias = getMatchesList($db, "aliases_id", $context)[0]["alias"];
    $ret = "alias: " . $alias . " (id: " . $context . ")";
  }
  return $ret;
}

// Return the human readable version of a change's context (e.g. instead of lemma
// id, show lemma )
function getReadableChangeValue($db, $type, $val, $userIDToName) {
  global $INDEX_TO_CONTEXT_NAME;
  global $LEMMA_STATUS_NAME;

  $ret = "";
  if ($type == 5) {
    $ret = $userIDToName[$val];
  } else if ($type == 11) { // compound parts
    $indices = json_decode(stripcslashes($val));
    $list = array();
    foreach ($indices as $i) {
      $item = getMatchesList($db, "semantic_groups_id", $i)[0]["group_name"];
      array_push($list, $item);
    }
    $ret = implode(", ", $list);
  } else if ($type == 12) { // roots
    $indices = json_decode(stripcslashes($val));
    $list = array();
    foreach ($indices as $i) {
      $item = getMatchesList($db, "roots_id", $i)[0]["root"];
      array_push($list, $item);
    }
    $ret = implode(", ", $list);
  } else if ($type == 13) { // compound parts
    $indices = json_decode(stripcslashes($val));
    $list = array();
    foreach ($indices as $i) {
      $item = getMatchesList($db, "compounds_id", $i)[0]["compound"];
      array_push($list, $item);
    }
    $ret = implode(", ", $list);
  } else if ($type == 14) { // has illustration
    if ($val == 1) {
      $ret = "Yes";
    } else {
      $ret = "No";
    }
  } else if ($type == 19) { // lemma status
    $ret = $LEMMA_STATUS_NAME[$val];
  } else if ($type == 35) { // lemma context
    $ret = $INDEX_TO_CONTEXT_NAME[$val];
  } else if ($type == 39) { // lemma
    $lem = getMatchesList($db, "lemmata_id", $val)[0]["lemma"];
    $ret = "lemma: " . $lem . " (id: " . $val . ")";
  } else {
    $ret = $val;
  }
  return $ret;
}

// Given a category and a search term, get a query for selecting matching entries
function getMatchesQuery($db, $category, $searchTerm, $searchTerm2="") {
  $query = "";
  if ($category == "compounds") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT * FROM compounds WHERE BINARY compound='$searchTerm' AND deleted=0;";
  } else if ($category == "compounds_id") {
    $query = "SELECT * FROM compounds WHERE compound_index=$searchTerm;";
  } else if ($category == "compounds_available_sorted") {
    $query = "SELECT * FROM compounds WHERE deleted=0 ORDER BY compound;";
  } else if ($category == "roots") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT * FROM roots WHERE BINARY root='$searchTerm' AND deleted=0;";
  } else if ($category == "roots_id") {
    $query = "SELECT * FROM roots WHERE root_index=$searchTerm;";
  } else if ($category == "roots_available_sorted") {
    $query = "SELECT * FROM roots WHERE deleted=0 ORDER BY root;";
  } else if ($category == "semantic_groups") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT * FROM semantic_groups WHERE group_name='$searchTerm' AND deleted=0;";
  } else if ($category == "semantic_groups_index") {
    $query = "SELECT * FROM semantic_groups WHERE group_index=$searchTerm AND deleted=0;";
  } else if ($category == "semantic_groups_id") {
    $query = "SELECT * FROM semantic_groups WHERE group_index=$searchTerm;";
  } else if ($category == "semantic_groups_available_sorted") {
    $query = "SELECT * FROM semantic_groups WHERE deleted=0 ORDER BY group_name;";
  } else if ($category == "pos_available_sorted") {
    $query = "SELECT DISTINCT part_of_speech FROM lemmata ORDER BY part_of_speech;";
  } else if ($category == "lemmata") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT * FROM lemmata WHERE BINARY lemma='$searchTerm' AND deleted=0;";
  } else if ($category == "lemmata_id") {
    $query = "SELECT * FROM lemmata WHERE lemmaid=$searchTerm;";
  } else if ($category == "lemmata_id_not_deleted") {
    $query = "SELECT * FROM lemmata WHERE lemmaid=$searchTerm AND deleted=0;";
  } else if ($category == "lemmata_all") {
    $query = "SELECT * FROM lemmata WHERE deleted=0;";
  } else if ($category == "lemmata_by_compound") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT l.lemma AS lemma, l.short_def AS short_def FROM lemmata AS l INNER JOIN compound_lemma_link AS link ON l.lemmaid = link.lemmaid INNER JOIN compounds AS c ON link.compound_index = c.compound_index WHERE BINARY c.compound='$searchTerm' AND l.deleted=0 ORDER BY l.lemma;";
  } else if ($category == "lemmata_by_root") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT l.lemma AS lemma, l.short_def AS short_def FROM lemmata AS l INNER JOIN root_lemma_link AS link ON l.lemmaid = link.lemmaid INNER JOIN roots AS r ON link.root_index = r.root_index WHERE BINARY r.root='$searchTerm' AND l.deleted=0 ORDER BY l.lemma;";
  } else if ($category == "lemmata_by_sg") {
    $searchTerm = intval($searchTerm);
    $query = "SELECT lemmata.lemma AS lemma, lemmata.short_def AS short_def FROM lemmata INNER JOIN semantic_lemma_link as link ON lemmata.lemmaid = link.lemmaid WHERE link.semantic_group=$searchTerm AND lemmata.deleted=0 ORDER BY lemmata.lemma;";
  } else if ($category == "aliases") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT * FROM aliases WHERE BINARY alias='$searchTerm' AND deleted=0;";
  } else if ($category == "aliases_with_lemma") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT * FROM aliases AS a INNER JOIN lemmata AS l ON a.lemmaid = l.lemmaid WHERE BINARY a.alias='$searchTerm' AND a.deleted=0;";
  } else if ($category == "aliases_by_id_with_lemma") {
    $query = "SELECT * FROM aliases AS a INNER JOIN lemmata AS l ON a.lemmaid = l.lemmaid WHERE BINARY a.aliasid=$searchTerm AND a.deleted=0;";
  } else if ($category == "aliases_by_lemmaid") {
    $query = "SELECT * FROM aliases WHERE lemmaid=$searchTerm AND deleted=0;";
  } else if ($category == "aliases_id") {
    $query = "SELECT * FROM aliases WHERE aliasid=$searchTerm;";
  } else if ($category == "search_results") {
    $searchTerm = escapeString($db, $searchTerm);
    // need capitalized version as well
    $searchTerm2 = mb_convert_case($searchTerm, MB_CASE_TITLE, 'UTF-8');
    $query = "SELECT * FROM search_lemmata WHERE BINARY search_text='$searchTerm' OR BINARY search_text='$searchTerm2';";
  } else if ($category == "matching_lemmata") {
    $searchQ = escapeString($db, $searchTerm);

    // need capitalized version as well
    $searchQ2 = mb_convert_case($searchQ, MB_CASE_TITLE, 'UTF-8');

    // for ως, ος
    $searchQ3 = str_replace('σ', 'ς', $searchQ);
    $searchQ4 = str_replace('σ', 'ς', $searchQ2);

    // get matching lemmata
    if (mb_strlen($searchQ) == 1) {
      $query ="SELECT DISTINCT l.lemma as lemma, l.short_def as def FROM search_lemmata AS s RIGHT JOIN lemmata AS l ON s.lemmaid=l.lemmaid WHERE (BINARY s.search_text='$searchQ' OR BINARY s.search_text='$searchQ2' OR BINARY s.search_text='$searchQ3' OR BINARY s.search_text='$searchQ4') AND l.deleted=0;";
    } else {
      $query ="SELECT DISTINCT l.lemma as lemma, l.short_def as def FROM search_lemmata AS s RIGHT JOIN lemmata AS l ON s.lemmaid=l.lemmaid WHERE (s.search_text LIKE '$searchQ%' OR s.search_text LIKE '$searchQ2%' OR s.search_text LIKE '$searchQ3%' OR s.search_text LIKE '$searchQ4%') AND l.deleted=0;";
    }
  } else if ($category == "unresolved_issues") {
    $query = "SELECT * FROM issue_reports WHERE resolved=0;";
  } else if ($category == "aliases_search") {
    $query = "SELECT * FROM aliases AS a INNER JOIN lemmata AS l ON a.lemmaid = l.lemmaid WHERE a.aliasid=$searchTerm AND a.deleted=0;";
  } else if ($category == "compound_lemma_link") {
    $query = "SELECT * FROM compound_lemma_link WHERE compound_index=$searchTerm;";
  } else if ($category == "compound_lemma_link_plus_lemma") {
    $query = "SELECT * FROM compound_lemma_link AS c INNER JOIN lemmata AS l ON c.lemmaid = l.lemmaid WHERE c.compound_index=$searchTerm;";
  } else if ($category == "compound_lemma_link_plus_group") {
    $query = "SELECT * FROM compound_lemma_link as link INNER JOIN compounds as c ON link.compound_index = c.compound_index WHERE link.lemmaid=$searchTerm;";
  } else if ($category == "root_lemma_link") {
    $query = "SELECT * FROM root_lemma_link WHERE root_index=$searchTerm;";
  } else if ($category == "root_lemma_link_plus_lemma") {
    $query = "SELECT * FROM root_lemma_link AS s INNER JOIN lemmata AS l ON s.lemmaid = l.lemmaid WHERE s.root_index=$searchTerm;";
  } else if ($category == "root_lemma_link_plus_group") {
    $query = "SELECT * FROM root_lemma_link as link INNER JOIN roots as s ON link.root_index = s.root_index WHERE link.lemmaid=$searchTerm;";
  } else if ($category == "semantic_lemma_link") {
    $query = "SELECT * FROM semantic_lemma_link WHERE semantic_group=$searchTerm;";
  } else if ($category == "semantic_lemma_link_plus_lemma") {
    $query = "SELECT * FROM semantic_lemma_link AS s INNER JOIN lemmata AS l ON s.lemmaid = l.lemmaid WHERE s.semantic_group=$searchTerm;";
  } else if ($category == "semantic_lemma_link_plus_group") {
    $query = "SELECT * FROM semantic_lemma_link as link INNER JOIN semantic_groups as s ON link.semantic_group = s.group_index WHERE link.lemmaid=$searchTerm;";
  } else if ($category == "instance_information") {
    $query = "SELECT * FROM instance_information WHERE BINARY lemma='$searchTerm';";
  } else if ($category == "instance_by_index") {
    $query = "SELECT * FROM instance_information WHERE token_index=$searchTerm;";
  } else if ($category == "long_definitions") {
    $query = "SELECT * FROM long_definitions WHERE id=$searchTerm;";
  } else if ($category == "long_definitions_for_lemmaid") {
    $query = "SELECT * FROM long_definitions WHERE lemmaid=$searchTerm AND status=0;";
  } else if ($category == "long_definition_by_lemma") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT d.long_def as long_def FROM lemmata as l INNER JOIN long_definitions as d ON l.long_def_id = d.id WHERE BINARY l.lemma='$searchTerm';";
  } else if ($category == "long_definitions_by_lemma_author") {
    $query = "SELECT * FROM long_definitions WHERE lemmaid=$searchTerm AND authorid=$searchTerm2;";
  } else if ($category == "lemmata_long_def") {
    $query = "SELECT d.id as id, d.authorid as aid, d.custom_author as custom_author, d.lemmaid as lemmaid, d.long_def_raw as long_def_raw, d.later_draft_id as later_draft_id, l.lemma as lemma, d.long_def_raw as raw, d.long_def as long_def, d.status as status FROM long_definitions AS d INNER JOIN lemmata AS l ON d.lemmaid = l.lemmaid WHERE d.id=$searchTerm;";
  } else if ($category == "long_definitions_article_info") {
    $query = "SELECT d.id as id, d.authorid as aid, d.custom_author as custom_author, d.lemmaid as lemmaid, d.long_def_raw as long_def_raw, d.later_draft_id as later_draft_id, l.lemma as lemma, d.long_def_raw as raw, d.long_def as long_def, d.status as status, c.userid as change_user FROM long_definitions AS d INNER JOIN lemmata AS l ON d.lemmaid = l.lemmaid INNER JOIN change_log AS c ON c.context = d.id WHERE d.id=$searchTerm;";
    // ^ this is rather complicated but basically we need to grab the definition,
    // the associated lemma (from lemmata) and the user who actually made the
    // change (from the changelog). We can't just grab everything because status
    // has different meanings in long_definitions and lemmata

  } else if ($category == "tokens") {
    $query = "SELECT * FROM instance_information AS i INNER JOIN text_storage AS t ON i.token_index = t.token_index WHERE i.token_index=$searchTerm;";
  } else if ($category == "tokens_by_lemma") {
    $searchTerm = escapeString($db, $searchTerm);
    $query = "SELECT * FROM instance_information AS i INNER JOIN text_storage AS t ON i.token_index = t.token_index WHERE BINARY i.lemma='$searchTerm';";
  } else if ($category == "entries_to_proof") {
    $query = "SELECT * FROM lemmata WHERE status=1 ORDER BY lemmaid ASC;";
  } else if ($category == "entries_to_finalize") {
    $query = "SELECT * FROM lemmata WHERE status=2 ORDER BY lemmaid ASC;";
  } else if ($category == "lemma_for_article") {
    $query = "SELECT * FROM long_definitions AS d INNER JOIN lemmata as l ON d.lemmaid = l.lemmaid WHERE d.id=$searchTerm;";
  } else if ($category == "changes") {
    $query = "SELECT * FROM change_log WHERE id=$searchTerm;";
  } else if ($category == "assigned_articles") {
    $query = "SELECT * FROM lemmata WHERE status=0 AND assigned=$searchTerm AND deleted=0;";
  } else if ($category == "unwritten_articles") {
    $query = "SELECT * FROM lemmata WHERE status=0 AND deleted=0 AND assigned=0;";
  } else if ($category == "unapproved_drafts") {
    $query = "SELECT * FROM long_definitions WHERE status=0 ;";
  } else if ($category == "issue_reports") {
    $query = "SELECT * FROM issue_reports WHERE id=$searchTerm;";
  } else if ($category == "unresolved_issues") {
    $query = "SELECT * FROM issue_reports WHERE resolved=0;";
  } else {
    throw new Exception("\"$category\" is not a valid category for getNumMatches.");
  }
  return $query;
}

// Get the number of matches given a category and an entry in that category
function getNumMatches($db, $category, $searchTerm, $searchTerm2="") {
  $query = getMatchesQuery($db, $category, $searchTerm, $searchTerm2);
  $query = str_replace("SELECT *", "SELECT count(*)", $query);

  $matches = getMatches($db, $query);
  $row = getNextItem($db, $matches);
  $numRows = intval($row["count(*)"]);

  return $numRows;
}

// Get a list of matches given a category and an entry in that category
function getMatchesList($db, $category, $searchTerm, $searchTerm2="") {
  $query = getMatchesQuery($db, $category, $searchTerm, $searchTerm2);

  $matches = getMatches($db, $query);

  $results = array();
  while($row = getNextItem($db, $matches)) {
    array_push($results, $row);
  }

  return $results;
}

// Get frequency for a lemma
function getFrequency($db, $lemmaid, $includeContexts=True) {
  global $NUM_CONTEXTS;
  $query = "SELECT count(*) FROM instance_information AS i LEFT JOIN lemmata AS l ON BINARY i.lemma=l.lemma WHERE l.lemmaid=$lemmaid;";
  $freqAll = getNextItem($db, getMatches($db, $query))["count(*)"];

  $res = array(
    "all" => $freqAll
  );

  if ($includeContexts) {
    $contextFrequencies = array();
    for ($i = 0; $i < $NUM_CONTEXTS; $i++) {
      $query = "SELECT count(*) FROM instance_information AS i LEFT JOIN lemmata AS l ON BINARY i.lemma=l.lemma WHERE l.lemmaid=$lemmaid AND i.context_type=$i;";
      $freq = getNextItem($db, getMatches($db, $query))["count(*)"];
      array_push($contextFrequencies, $freq);
    }
    $res["contexts"] = $contextFrequencies;
  }


  return $res;

}

// Get info for a page of of items
function getPageInfo($db, $category, $perPage, $offset, $infoArgs) {
  if ($category == "aliases") {
    $queryCore = "SELECT * FROM aliases AS a INNER JOIN lemmata AS l ON a.lemmaid = l.lemmaid WHERE a.deleted=0";
    $orderBy = "a.alias ASC";
    $totalQuery = "SELECT count(*) FROM aliases WHERE deleted=0;";
  } else if ($category == "article_drafts") {
    // Get either list of user's articles or list of all articles
    if ($infoArgs["userOnly"]) {
      $condition = "d.authorid=" . $infoArgs["id"];
    } else {
      $condition = "d.status=0";
    }
    $queryCore = "SELECT d.id as id, d.authorid as aid, d.lemmaid as lemmaid, d.custom_author as custom_author, l.lemma as lemma, d.long_def_raw as raw, d.long_def as long_def, d.status as status, c.userid as change_user FROM long_definitions AS d INNER JOIN lemmata AS l ON d.lemmaid = l.lemmaid INNER JOIN change_log AS c ON c.context = d.id WHERE $condition AND c.change_type=1";
    // ^ this is rather complicated but basically we need to grab the definition,
    // the associated lemma (from lemmata) and the user who actually made the
    // change (from the changelog). We can't just grab everything because status
    // has different meanings in long_definitions and lemmata

    $orderBy = "d.id DESC";
    $totalQuery = "SELECT count(*) FROM long_definitions AS d INNER JOIN lemmata AS l ON d.lemmaid = l.lemmaid INNER JOIN change_log AS c ON c.context = d.id WHERE $condition AND c.change_type=1;";
  } else if ($category == "assigned_articles") {
    $id = $infoArgs["id"];
    $queryCore = "SELECT l.lemmaid as lemmaid, l.lemma as lemma, d.old_long_def as old_long_def FROM lemmata AS l LEFT JOIN long_definitions AS d on l.long_def_id = d.id WHERE l.status=0 AND l.assigned=$id AND l.deleted=0";
    // ^ this is rather complicated but basically we need to grab the definition,
    // the associated lemma (from lemmata) and the user who actually made the
    // change (from the changelog). We can't just grab everything because status
    // has different meanings in long_definitions and lemmata

    $orderBy = "l.lemmaid DESC";
    $totalQuery = "SELECT count(*) FROM lemmata WHERE status=0 AND assigned=$id;";
  } else if ($category == "unwritten_articles") {
    $getAssignedArticles = $infoArgs["assigned"];
    $rootFilter = $infoArgs["root"];
    $semanticFilter = $infoArgs["sem"];
    $freqFilter = $infoArgs["freq"];

    $assignedString = "l.assigned=0";
    if ($getAssignedArticles) {
      $assignedString = "l.assigned>0";
    }

    $conditionString = "l.status=0 AND l.deleted=0 AND " . $assignedString;

    // -1 means no filtering
    if ($freqFilter == 0) {
      $conditionString .= " AND l.frequency_all >= 25";
    } elseif ($freqFilter == 1) {
      $conditionString .= " AND l.frequency_all >= 5 AND l.frequency_all < 25";
    } elseif ($freqFilter == 2) {
      $conditionString .= " AND l.frequency_all >= 2 AND l.frequency_all < 5";
    } elseif ($freqFilter == 3) {
      $conditionString .= " AND l.frequency_all = 1";
    }

    $queryBody = "";
    // Get list of either assigned or unassigned articles
    if ($rootFilter != "" && $semanticFilter != -1) {
      $queryBody ="FROM root_lemma_link AS link INNER JOIN lemmata AS l ON link.lemmaid = l.lemmaid INNER JOIN roots ON link.root_index = roots.root_index INNER JOIN semantic_lemma_link AS link2 ON link2.lemmaid = l.lemmaid WHERE BINARY roots.root='$rootFilter' AND link2.semantic_group=$semanticFilter AND $conditionString";
    } elseif ($rootFilter != "") {
      $queryBody ="FROM root_lemma_link AS link INNER JOIN lemmata AS l ON link.lemmaid = l.lemmaid INNER JOIN roots ON link.root_index = roots.root_index WHERE BINARY roots.root='$rootFilter' AND $conditionString";
    } elseif ($semanticFilter != -1) {
      $queryBody ="FROM semantic_lemma_link INNER JOIN lemmata AS l ON semantic_lemma_link.lemmaid = l.lemmaid WHERE semantic_lemma_link.semantic_group=$semanticFilter AND $conditionString";
    } else {
      $queryBody ="FROM lemmata AS l WHERE $conditionString";
    }

    $queryCore = "SELECT l.* " . $queryBody;

    $orderBy = "l.lemmaid ASC";
    $totalQuery = "SELECT count(*) $queryBody;";
  } else if ($category == "changelog") {
    $userID = $infoArgs["userID"];
    $changeTypeID = $infoArgs["changeTypeID"];

    // Organize filters
    $filters = array();
    if ($userID != -1) {
      array_push($filters, "userid=$userID");
    }
    if ($changeTypeID != -1) {
      array_push($filters, "change_type=$changeTypeID");
    }

    $filter = "";
    if (sizeof($filters) > 0) {
      $filter = "WHERE " . join(" AND ", $filters);
    }

    $queryCore = "SELECT * FROM change_log $filter";

    $orderBy = "id DESC";
    $totalQuery = "SELECT count(*) FROM change_log $filter;";
  } else if ($category == "compounds") {
    $queryCore = "SELECT * FROM compounds WHERE deleted=0";

    $orderBy = "compound ASC";
    $totalQuery = "SELECT count(*) FROM compounds WHERE deleted=0;";
  } else if ($category == "roots") {
    $queryCore = "SELECT * FROM roots WHERE deleted=0";

    $orderBy = "root ASC";
    $totalQuery = "SELECT count(*) FROM roots WHERE deleted=0;";
  } else if ($category == "semantic_groups") {
    $queryCore = "SELECT * FROM semantic_groups WHERE deleted=0";

    $orderBy = "group_name ASC";
    $totalQuery = "SELECT count(*) FROM semantic_groups WHERE deleted=0;";
  } else if ($category == "issue_reports") {
    $showResolved = $infoArgs["resolved"];
    if ($showResolved) {
      $condition = "";
    } else {
      $condition = "WHERE resolved=0";
    }
    $queryCore = "SELECT * FROM issue_reports $condition";

    $orderBy = "id DESC";
    $totalQuery = "SELECT count(*) FROM issue_reports $condition;";
  } else {
    throw new Exception("\"$category\" is not a valid category for getPageInfo.");
  }


  $items = array();

  $pageQuery = $queryCore . " ORDER BY $orderBy LIMIT $perPage OFFSET $offset;";
  $matches = getMatches($db, $pageQuery);
  $numRows = 0;
  while($row = getNextItem($db, $matches)) {
    $numRows++;
    array_push($items, $row);
  }

  $totalCount = getNextItem($db, getMatches($db, $totalQuery))["count(*)"];

  return array(
    "pageItems" => $items,
    "totalCount" => $totalCount,
  );
}

// Add search lemmata
function addSearchLemmata($db, $val, $val_id, $isAlias) {
  $searchLemma = searchVersion($val);
  $unaccentedLemma = unaccented($val);
  $betacodeLemma = betacode($val);
  $unbetaLemma = unaccentedBetacode($val);
  $latinLemma = latinApproximation($val);


  // add search info
  $search = array(
    $searchLemma,
    $unaccentedLemma,
    $betacodeLemma,
    $unbetaLemma,
    $latinLemma
  );

  $lastIndex = mb_strlen($searchLemma)-1;
  if (mb_substr($searchLemma, -1) == "ς") {
    array_push($search, mb_substr($searchLemma, 0, $lastIndex) . "σ");
    array_push($search, mb_substr($unaccentedLemma, 0, mb_strlen($unaccentedLemma)-1) . "σ");
  }

  // TODO: this is harder to do without having access to the list of compounds,
  // and could end up being quite tricky. But here's the code I was using before
  //   # συν ξυν
  // if ("σύν" in self.compoundParts):
  //     searches.append(re.sub(r'σ(ύ|υ)', r'ξ\1', self.searchLemma))
  //     searches.append(re.sub(r'συ', r'ξυ', self.unaccentedLemma))
  //     searches.append(re.sub(r'su', r'cu', self.betacodeLemma))
  //     searches.append(re.sub(r'su', r'cu', self.betacodeUnaccentedLemma))
  //     searches.append(re.sub(r'su', r'xu', self.latinApproximationLemma))

  foreach($search as $s) {
    if ($isAlias) {
      $query = "INSERT INTO search_lemmata(lemmaid,aliasid,search_text) VALUES (NULL,?,?);";
    } else {
      $query = "INSERT INTO search_lemmata(lemmaid,aliasid,search_text) VALUES (?,NULL,?);";
    }
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param("is", $val_id, $s);
    $stmt->execute();
  }
}

// Add compound parts
function addCompoundParts($db, $compoundsJSON, $lemmaid) {
  foreach(json_decode($compoundsJSON) as $c) {
    $compound = $c;

    $query = "INSERT INTO compound_lemma_link(compound_index,lemmaid) VALUES (?,?);";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param("ii", $compound, $lemmaid);
    $stmt->execute();
  }
}

// Add compound parts
function addRoots($db, $rootsJSON, $lemmaid) {
  foreach(json_decode($rootsJSON) as $r) {
    $root = $r;

    $query = "INSERT INTO root_lemma_link(root_index,lemmaid) VALUES (?,?);";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param("ii", $root, $lemmaid);
    $stmt->execute();
  }
}

// Add compound parts
function addSemanticGroups($db, $sgJSON, $lemmaid) {
  foreach(json_decode($sgJSON) as $group) {
    $sg = $group;
    $query = "INSERT INTO semantic_lemma_link(semantic_group,lemmaid) VALUES (?,?);";
    $stmt = $db->prepare($query);
    if (!$stmt) {
      throw new Exception('mysql error.');
    }
    $stmt->bind_param("ii", $sg, $lemmaid);
    $stmt->execute();
  }
}

// Get compounds, roots, and semantic groups for the lemma with given id.
// Return the group ids if $groupsAsNumbers is true
function getLemmaGroups($db, $lemmaid, $groupsAsNumbers) {
  $semanticGroups = array();
  $matches = getMatchesList($db, "semantic_lemma_link_plus_group", $lemmaid);
  $numRows = sizeof($matches);
  for ($i = 0; $i < $numRows; $i++){
    $row = $matches[$i];
    if ($groupsAsNumbers) {
      array_push($semanticGroups, $row["group_index"]);
    } else {
      $sg = array(
        "index" => $row["group_index"],
        "name" => $row["group_name"],
        "displayType" => $row["label_class"]
      );
      array_push($semanticGroups, $sg);
    }
  }

  $roots = array();
  $matches = getMatchesList($db, "root_lemma_link_plus_group", $lemmaid);
  $numRows = sizeof($matches);
  for ($i = 0; $i < $numRows; $i++){
    $row = $matches[$i];
    if ($groupsAsNumbers) {
      array_push($roots, $row["root_index"]);
    } else {
      array_push($roots, $row["root"]);
    }
  }

  $compoundParts = array();
  $matches = getMatchesList($db, "compound_lemma_link_plus_group", $lemmaid);
  $numRows = sizeof($matches);
  for ($i = 0; $i < $numRows; $i++){
    $row = $matches[$i];
    if ($groupsAsNumbers) {
      array_push($compoundParts, $row["compound_index"]);
    } else {
      array_push($compoundParts, $row["compound"]);
    }
  }

  return array(
    "compounds" => $compoundParts,
    "roots" => $roots,
    "sgs" => $semanticGroups,
  );
}

// Get information about a long definition
function getLongDefInfo($db, $def_id, $userIDToName) {
  $hasLongDef = False;
  $oldLongDef = False;
  $longDef = array();
  $rawLongDef = "";
  $authorName = "";
  $priorArticle = False;
  $priorAuthor = "";
  $priorAuthorID = 0;
  $priorCustomAuthor = "";

  // if long def id is 0, there is no long def
  if ($def_id != 0) {
    $hasLongDef = True;
    $query ="SELECT * FROM long_definitions WHERE id=$def_id;";
    $matches = getMatches($db, $query);
    while($row = getNextItem($db, $matches)) {
      $longDef = json_decode($row['long_def']);
      $rawLongDef = $row['long_def_raw'];

      if ($row['old_long_def'] == 1) {
        $oldLongDef = True;
      } else {
        $oldLongDef = False;
      }

      $priorArticle = True;
      $priorAuthorID = intval($row["authorid"]);
      $priorAuthor = $userIDToName[$priorAuthorID];
      $priorCustomAuthor = $row["custom_author"];

      $authorName = $priorAuthor;
      if ($priorCustomAuthor != "") {
        $authorName = $priorCustomAuthor;
      }
    }
  }

  return array(
    "hasLongDef" => $hasLongDef,
    "oldLongDef" => $oldLongDef,
    "longDef" => $longDef,
    "rawLongDef" => $rawLongDef,
    "authorName" => $authorName,
    "priorArticle" => $priorArticle,
    "priorAuthor" => $priorAuthor,
    "priorAuthorID" => $priorAuthorID,
    "priorCustomAuthor" => $priorCustomAuthor,
  );
}

// Get a list of matches given a category and an entry in that category
// provided the changes come after the provided index
function getLaterChanges($db, $index, $type, $context) {
  $index = intval($index);
  $type = intval($type);
  $context = escapeString($db, $context);

  $query = "SELECT * FROM change_log WHERE change_type=$type AND BINARY context='$context' AND id > $index;";

  $matches = getMatches($db, $query);

  $results = array();
  while($row = getNextItem($db, $matches)) {
    array_push($results, $row);
  }

  return $results;
}

// get occurrences for a lemma
function getOccurrences($db, $lemma, $windowSize) {
  $occurrences = array();

  $lemma = escapeString($db, $lemma);

  $order = getOrderSQL("t.");
  $occQuery = "SELECT * FROM instance_information AS i INNER JOIN text_storage AS t on i.token_index = t.token_index WHERE BINARY i.lemma='" . $lemma . "' ORDER BY $order, t.true_word_index ASC;";
  $occMatches = getMatches($db, $occQuery);
  while($row = getNextItem($db, $occMatches)) {
    $sectionCode = getLocationCode($row);
    $contextType = $row["context_type"];
    $token = $row["token"];
    $index = $row["token_index"];

    // get context
    $seqIndex = -1;
    $query = "SELECT sequence_index FROM text_storage WHERE token_index='$index';";
    $matches = getMatches($db, $query);
    while($row2 = getNextItem($db, $matches)) {
      $seqIndex = $row2["sequence_index"];
    }

    $contextPre = "";
    $contextPost = "";
    $prev = "";
    $next = "";
    if ($seqIndex != -1) {
      $query = "SELECT * FROM text_storage WHERE sequence_index>='" . ($seqIndex-$windowSize) . "' AND sequence_index<='" . ($seqIndex+$windowSize) . "';";
      $matches = getMatches($db, $query);
      while($row2 = getNextItem($db, $matches)) {
        $i = $row2["sequence_index"] - $seqIndex;
        if ($i < 0) {
          $contextPre = $contextPre . $row2["token"] . " ";
        } else if ($i > 0) {
          $contextPost = $contextPost . $row2["token"] . " ";
        }
        if ($i == -1) {
          $prev = $row2["token"];
        } else if ($i == 1) {
          $next = $row2["token"];
        }
      }
      // echo($contextPre . "\n");
      // echo($query . "\n");
    }

    array_push($occurrences, array($sectionCode, $contextType, $prev, $token, $next, $contextPre, $contextPost));
  }
  return $occurrences;
}

// given a storage location and a file, try saving it there
function uploadImage($storage_dir, $f, $name) {

  $file_loc_default = $storage_dir . basename($f["name"]);

  // get informationa about storing the file
  $file_info = pathinfo($file_loc_default);

  // create the path to the file (renaming it to the lemma + a timestamp)
  $file_loc = $file_info["dirname"] . "/" . $name . "_" . time() . "." . $file_info["extension"];

  $image_type = strtolower(pathinfo($file_loc)["extension"]);
  $image_location = pathinfo($file_loc)["basename"];

  $upload_success = True;
  $upload_message = "";

  $check = getimagesize($f["tmp_name"]);
  if($check !== false) {
      // echo "File is an image - " . $check["mime"] . ".";
      $upload_success = True;
  } else {
      // echo "File is not an image.";
      $upload_message = "File is not an image.";
      $upload_success = False;
  }

  // Check file size
  if ($f["size"] > 3000000) {
    $upload_message = "This file is too large. It must be smaller than 3 megabytes";
    $upload_success = False;
  }

  // limit file types
  // Allow certain file formats
  if($image_type != "jpg" && $image_type != "png" && $image_type != "jpeg" && $image_type != "gif" ) {
      $upload_message = "Only JPG, JPEG, PNG & GIF files are allowed.";
      $upload_success = False;
  }

  if ($upload_success) {
    $result = move_uploaded_file($f["tmp_name"], $file_loc);
    if (!$result) {
      $upload_message = "There was an error uploading your file.";
      $upload_success = False;
    }
  }

  return array(
    "message" => $upload_message,
    "success" => $upload_success,
    "location" => $image_location,
  );
}

// Encode an object using JSON and echo it.
function echoResult($r) {
  $myJSON = json_encode($r);
  echo($myJSON);
}

// Extract identifiers from a long definitiong
function extractIdentifiers($long_def) {
  $identifiers = array($long_def["text"]["identifier"]);

  foreach($long_def["subList"] as $sub) {
    $identifiers = array_merge($identifiers, extractIdentifiers($sub));
  }

  return $identifiers;
}

// Generate a json file of all valid alpha combos
function updateAlphaCombos($db) {
  $matches = getMatchesList($db, "lemmata_all", "");

  // Get list of valid combos
  $valids = array();
  foreach ($matches as $m) {
    $lemma = $m["lemma"];
    $l1 = noDiacritics(mb_strtoupper(mb_substr($lemma, 0, 1)));
    $l2 = noDiacritics(mb_strtolower(mb_substr($lemma, 1, 2)));
    if ($l2 == "ς") {
      $l2 = "σ";
    }
    if ($l2 == "") {
      $l2 = "_";
    }
    if (!array_key_exists($l1, $valids)) {
      $valids[$l1] = array();
    }
    if (!array_key_exists($l2, $valids[$l1])) {
      $valids[$l1][$l2] = array();
    }
  }

  $combos = array();

  // Sort by first letter
  $l1s = array();
  foreach ($valids as $l1 => $nothing) {
    array_push($l1s, $l1);
  };
  sort($l1s);

  foreach ($l1s as $l1) {
    if (!array_key_exists($l1, $combos)) {
      $combos[$l1] = array();
    }
    // echo("<h2>" . $l1 . "</h2><ul>");

    // Sort by second letter
    $l2s = array();
    foreach ($valids[$l1] as $l2 => $empty) {
      array_push($l2s, $l2);
    };
    sort($l2s);


    foreach ($l2s as $l2) {
      $combo = $l1 . $l2;
      $link = "/wordList/" . $l1 . "/" . $l2;
      $tmp = array(
        "active" => false,
        "link" => $link,
        "text" => $combo,
      );
      array_push($combos[$l1], $tmp);

      // echo("<li>" . $combo . "</li>");
    }
    // echo("</ul>");
  }

  $result = array(
    "error" => false,
    "message" => "",
    "combos" => $combos
  );

  $contents = json_encode($result);
  $file = "../assets/alphaCombos.json";
  file_put_contents($file, $contents);

}

// =============================================================================
// =============================================================================
// =============================================================================

// Function for calling a command with standard input passed
// $stdin is a list of things to pass.
// Adapted from https://stackoverflow.com/a/2390755
// Basically, we are supposed to pass the password for mysql through standard in
// for security reasons, which requires more complex code.
function commandWithStdIn($command, $stdin) {
  $descriptorspec = array(
     0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
     1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
  );

  $result = "Failed to open process.";
  $output = array();
  $process = proc_open($command, $descriptorspec, $pipes);

  if (is_resource($process)) {
      // $pipes now looks like this:
      // 0 => writeable handle connected to child stdin
      // 1 => readable handle connected to child stdout

      foreach ($stdin as $in) {
        fwrite($pipes[0], $in);
      }
      fclose($pipes[0]);

      $output = stream_get_contents($pipes[1]);
      fclose($pipes[1]);

      // It is important that you close any pipes before calling
      // proc_close in order to avoid a deadlock
      $result = proc_close($process);
  }

  return array(
    "result" => $result,
    "output" => $output,
  );
}

/**
 * https://stackoverflow.com/questions/155097/microsoft-excel-mangles-diacritics-in-csv-files/1648671#1648671
 * Export an array as downladable Excel CSV
 * @param array   $header
 * @param array   $data
 * @param string  $filename
 */
function toCSV($header, $data, $filename) {
  $sep  = "\t";
  $eol  = "\n";
  $csv  =  count($header) ? '"'. implode('"'.$sep.'"', $header).'"'.$eol : '';
  foreach($data as $line) {
    $csv .= '"'. implode('"'.$sep.'"', $line).'"'.$eol;
  }
  $encoded_csv = mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
  header('Content-Description: File Transfer');
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
  header('Content-Transfer-Encoding: binary');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');
  header('Content-Length: '. strlen($encoded_csv));
  echo chr(255) . chr(254) . $encoded_csv;
  exit;
}


// Delete a directory
// https://stackoverflow.com/a/3349792
function deleteDir($dirPath) {
  if (!is_dir($dirPath)) {
    throw new InvalidArgumentException("$dirPath must be a directory");
  }
  if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
    $dirPath .= '/';
  }
  $files = glob($dirPath . '*', GLOB_MARK);
  foreach ($files as $file) {
    if (is_dir($file)) {
      deleteDir($file);
    } else {
      unlink($file);
    }
  }

  $store = $dirPath . ".DS_Store";
  if (is_file($store)) {
    unlink($store);
  }
  rmdir($dirPath);
}

// Create a new backup
function createNewBackup($base_path="") {
  global $DB_DATA_KEY;
  global $BACKUP_DIR;
  global $BACKUP_PREFIX;
  global $db_data;

  // Create folder for storing info
  if(is_dir($base_path . $BACKUP_DIR) != true) {
    mkdir($base_path . $BACKUP_DIR);
  }

  date_default_timezone_set("UTC");

  // Backup code adapted from https://stackoverflow.com/a/2170213
  $backup_name = $BACKUP_PREFIX . date('Y-m-d_H-i-s') . ".sql";
  $filename = $base_path . $BACKUP_DIR . $backup_name;

  $backup_command = $db_data[$DB_DATA_KEY]["backup_command"];
  $database = $db_data[$DB_DATA_KEY]["database"];
  $username = $db_data[$DB_DATA_KEY]["username"];
  $pass = $db_data[$DB_DATA_KEY]["password"];
  // mysqldump
  $command = $backup_command . " " . $database . " -p --user=" . $username . " --single-transaction 2>&1 >" . $filename;
  $stdin = array("$pass");
  $command_result = commandWithStdIn($command, $stdin);

  if ($command_result["result"] != 0) {
      // Delete empty output file
      if (file_exists($filename)) {
        unlink($filename);
      }
  }

  $command_result["name"] = $backup_name;

  return $command_result;
}

// Send a message
// Adapted from https://www.html5rocks.com/en/tutorials/eventsource/basics/
function send_message($send_full_messages, $id, $message, $progress, $complete) {
  // Send full messages if we are supposed to
  if ($send_full_messages) {
    $data = array(
      "isError" => false,
      "isDefault" => false,
      "message" => $message,
      "progress" => $progress,
      "complete" => $complete
    );

    echo("id: $id" . PHP_EOL);
    echo("data: " . json_encode($data) . PHP_EOL);
    echo(PHP_EOL);
  } else { // otherwise, a short message
    echo($progress . " ");
  }



  // Flush message
  if (ob_get_length()){ ob_flush(); }
  flush();
}

// =============================================================================
// =============================================================================
// =============================================================================


$betacodeCharMap = array(
    "COMBINING DIAERESIS" => "+",
    "COMBINING GREEK YPOGEGRAMMENI" => "|",
    "COMBINING COMMA ABOVE" => ")",
    "COMBINING REVERSED COMMA ABOVE" => "(",
    "COMBINING ACUTE ACCENT" => "/",
    "COMBINING GRAVE ACCENT" => "\\",
    "COMBINING GREEK PERISPOMENI" => "=",
    "α" => "a",
    "β" => "b",
    "γ" => "g",
    "δ" => "d",
    "ε" => "e",
    "ζ" => "z",
    "η" => "h",
    "θ" => "q",
    "ι" => "i",
    "κ" => "k",
    "λ" => "l",
    "μ" => "m",
    "ν" => "n",
    "ξ" => "c",
    "ο" => "o",
    "π" => "p",
    "ρ" => "r",
    "σ" => "s",
    "ς" => "s",
    "τ" => "t",
    "υ" => "u",
    "φ" => "f",
    "χ" => "x",
    "ψ" => "y",
    "ω" => "w",
    "Α" => "A",
    "Β" => "B",
    "Γ" => "G",
    "Δ" => "D",
    "Ε" => "E",
    "Ζ" => "Z",
    "Η" => "H",
    "Θ" => "Q",
    "Ι" => "I",
    "Κ" => "K",
    "Λ" => "L",
    "Μ" => "M",
    "Ν" => "N",
    "Ξ" => "C",
    "Ο" => "O",
    "Π" => "P",
    "Ρ" => "R",
    "Σ" => "S",
    "Τ" => "T",
    "Υ" => "U",
    "Φ" => "F",
    "Χ" => "X",
    "Ψ" => "Y",
    "Ω" => "W"
);


$latinCharMap = array(
    "COMBINING DIAERESIS" => "",
    "COMBINING GREEK YPOGEGRAMMENI" => "i",
    "COMBINING COMMA ABOVE" => "",
    "COMBINING REVERSED COMMA ABOVE" => "h",
    "COMBINING ACUTE ACCENT" => "",
    "COMBINING GRAVE ACCENT" => "",
    "COMBINING GREEK PERISPOMENI" => "",
    "α" => "a",
    "β" => "b",
    "γ" => "g",
    "δ" => "d",
    "ε" => "e",
    "ζ" => "z",
    "η" => "e",
    "θ" => "th",
    "ι" => "i",
    "κ" => "k",
    "λ" => "l",
    "μ" => "m",
    "ν" => "n",
    "ξ" => "x",
    "ο" => "o",
    "π" => "p",
    "ρ" => "r",
    "σ" => "s",
    "ς" => "s",
    "τ" => "t",
    "υ" => "u",
    "φ" => "ph",
    "χ" => "kh",
    "ψ" => "ps",
    "ω" => "o",
    "Α" => "A",
    "Β" => "B",
    "Γ" => "G",
    "Δ" => "D",
    "Ε" => "E",
    "Ζ" => "Z",
    "Η" => "E",
    "Θ" => "TH",
    "Ι" => "I",
    "Κ" => "K",
    "Λ" => "L",
    "Μ" => "M",
    "Ν" => "N",
    "Ξ" => "X",
    "Ο" => "O",
    "Π" => "P",
    "Ρ" => "R",
    "Σ" => "S",
    "Τ" => "T",
    "Υ" => "U",
    "Φ" => "PH",
    "Χ" => "KH",
    "Ψ" => "PS",
    "Ω" => "O"
);

// split a utf8 string
// from http://php.net/manual/en/function.mb-split.php
function splitUnicode($s) {
  return preg_split('//u', $s, null, PREG_SPLIT_NO_EMPTY);
}

// true if there are combining characters in c
function isCombining($c) {
  return mb_strlen(Normalizer::normalize($c, $form=Normalizer::FORM_D)) > 1;
}

// no diacritics
function noDiacritics($c) {
  return mb_substr(Normalizer::normalize($c, $form=Normalizer::FORM_D), 0, 1);
}

# get associated betacode character
function getBetacodeChar($c) {
  global $betacodeCharMap;
  if (IntlChar::isalpha($c)) {
    return $betacodeCharMap[$c];
  }
  else {
    return $betacodeCharMap[IntlChar::charName($c)];
  }
}


function getLatinChar($c) {
  global $latinCharMap;
  if (IntlChar::isalpha($c)) {
    return $latinCharMap[$c];
  }
  else {
    return $latinCharMap[IntlChar::charName($c)];
  }
}

function preprocess($lemma) {
  return mb_ereg_replace('[,\d\s]', '', $lemma);
  // return re.sub(r'[,\d\s]', r'', $lemma);
}

function preprocessKeepNumbers($lemma) {
  return mb_ereg_replace('[,\s]', '', $lemma);
}

# get searchable version of the lemma
function searchVersion($lemma) {
  $lemma = preprocess($lemma);
  return mb_strtolower($lemma);
}

# get unaccented
function unaccented($lemma) {
  $lemma = preprocess($lemma);

  $unacc = "";
  foreach(splitUnicode($lemma) as $c) {
    $unacc .= noDiacritics($c);
  }
  // $unacc = "".join([c for c in unicodedata.normalize("NFD", $lemma) if unicodedata.combining(c) == 0]);
  return mb_strtolower($unacc);
}

# get unaccented with numbers
function unaccentedKeepNumbers($lemma) {
  $lemma = preprocessKeepNumbers($lemma);

  $unacc = "";
  foreach(splitUnicode($lemma) as $c) {
    $unacc .= noDiacritics($c);
  }
  // $unacc = "".join([c for c in unicodedata.normalize("NFD", $lemma) if unicodedata.combining(c) == 0]);
  return mb_strtolower($unacc);
}

# get betacode
function betacode($lemma) {
  $lemma = preprocess($lemma);
  $uncombined_lemma = Normalizer::normalize($lemma, $form=Normalizer::FORM_D);
  $bcode = "";
  foreach(splitUnicode($uncombined_lemma) as $c) {
    $bcode .= getBetacodeChar($c);
  }
  // $bcode = "".join([getBetacodeChar(c) for c in unicodedata.normalize("NFD", $lemma)]);
  return  mb_strtolower($bcode);
}

# get unaccented betacode
function unaccentedBetacode($lemma) {
  $lemma = preprocess($lemma);
  return betacode(unaccented($lemma));
}

# get latin approximation
function latinApproximation($lemma) {
  $lemma = preprocess($lemma);

  $uncombined_lemma = Normalizer::normalize($lemma, $form=Normalizer::FORM_D);
  $base = "";
  foreach(splitUnicode($uncombined_lemma) as $c) {
    $base .= getLatinChar($c);
  }
  //$base = "".join([getLatinChar(c) for c in unicodedata.normalize("NFD", $lemma)]);

  $new = mb_ereg_replace('([aeiou])h', 'h\1', $base);
  //$new = re.sub(r'([aeiou])h', r'h\1', $base);
  return mb_strtolower($new);
}


$run_tests = False;
if ($run_tests) {
  $test = array(
      "αβαλ",
      "αβγδεζηθικλμνξοπρσςτυφχψω",
      "ῇβαλ",
      "ἀβάλ",
      "ἄβαλ",
      "ἄβαλ,2",
      "Ἄβαλ",
      "ῃβἆλἁὰάϊ",
      "ἱἱἱβαλ",
  );

  foreach($test as $s) {
    echo("Original: " . $s);
    echo("<br>");
    echo("Unaccented: " . unaccented($s));
    echo("<br>");
    echo("Bcode: " . betacode($s));
    echo("<br>");
    echo("Unaccented betacode: " . unaccentedBetacode($s));
    echo("<br>");
    echo("Latin approx: " . latinApproximation($s));
    echo("<br>");
    echo("---");
    echo("<br>");
    echo("<br>");
  }
}


?>
