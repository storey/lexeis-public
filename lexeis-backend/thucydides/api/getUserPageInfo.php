<?php
require_once "../../api/database_utils.php";
require_once "../../api/login_util.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$return = array(
  "isError" => false,
  "message" => "",
  "assignedArticles" => 0,
  "unwrittenArticles" => 0,
  "unapprovedDrafts" => 0,
  "entriesToProofread" => 0,
  "entriesToFinalize" => 0,
  "unresolvedIssues" => 0,
);

$db = get_db($dbname=$LEXICON_DB_NAME);

// For users, show number of articles assigned to them
if (hasAccess(1)) {
  $assigned = getNumMatches($db, "assigned_articles", $id);
  $return["assignedArticles"] = $assigned;
}

// For editors, include more information
if (hasAccess(2)) {
  $articles = getNumMatches($db, "unwritten_articles", $id);
  $return["unwrittenArticles"] = $articles;
  $drafts = getNumMatches($db, "unapproved_drafts", $id);
  $return["unapprovedDrafts"] = $drafts;
  $proof = getNumMatches($db, "entries_to_proof", $id);
  $return["entriesToProofread"] = $proof;
  $final = getNumMatches($db, "entries_to_finalize", $id);
  $return["entriesToFinalize"] = $final;
  $issues = getNumMatches($db, "unresolved_issues", $id);
  $return["unresolvedIssues"] = $issues;
}

$db->close();
echoResult($return);
?>
