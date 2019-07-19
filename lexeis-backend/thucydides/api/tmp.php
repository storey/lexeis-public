<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

$db = get_db($dbname=$LEXICON_DB_NAME);



return $results;

?>
