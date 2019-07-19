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
  'list' => array(),
  'size' => -1,
);
$errorMessageKey = "message";
if (!accessGuard(2, $errorReturn, $errorMessageKey)) { return; }

$data = get_data();

$db = get_db($dbname=$LEXICON_DB_NAME);

$alias = $data["alias"];

$return = array(
  'aliasid' => -1,
  'alias' => "There is no alias $alias",
  'lemma' => "",
  'error' => true,
);

// Get list of aliases
$matches = getMatchesList($db, "aliases_with_lemma", $alias);
if (sizeof($matches) > 0) {
  $row = $matches[0];
  $return = array(
    'aliasid' => $row['aliasid'],
    'alias' => $row['alias'],
    'lemma' => $row['lemma'],
    'error' => false,
  );
}

$db->close();
echoResult($return);
?>
