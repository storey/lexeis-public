<?php
require_once "../../api/master.php";
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";

// ===
$ZIP_NAME = "lexicon_export.zip";

write_headers();

// Ignore HTTP request with method OPTIONS
if (options_request()) { return; }

if (!hasAccess(3)) {
  echo("You do not have appropriate access to do this.");
  return;
}

// Zip files (from https://stackoverflow.com/a/1754359)
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$ZIP_NAME);
header('Content-Length: ' . filesize($ZIP_NAME));
readfile($ZIP_NAME);
?>
