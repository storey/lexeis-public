<?php
// Recompile all items in prepTexts
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";
require_once "compilePrepTexts.php";

// Compile all of the texts
function compile_texts($db, $send_full_messages) {
  global $TEXT_DIVISIONS_LOWER;
  // File locations
  $DIR_TMP = "prepTexts_tmp/";
  $DIR_FINAL = "prepTexts/";

  // Info for sending updates
  $MIN_UPDATE_SIZE = 20;
  // Book, chapter, section
  $DIVISION_UPDATES = [10, 10, 70];

  // Message index
  $message_index = 0;

  // Get list of files that need to be recompiled
  $allTexts = getFullPreppedTextList($db);

  // Create folder for storing info
  if(is_dir($DIR_TMP) == true) {
    deleteDir($DIR_TMP);
  }
  mkdir($DIR_TMP);
  $running_pct = 0;

  $num_divisions = sizeof($TEXT_DIVISIONS_LOWER);
    for ($divisionIndex = $num_divisions-1; $divisionIndex >= 0; $divisionIndex--) {
    // Update text part (this starting with sections, then chapters, then books)
    $divisionStr = $TEXT_DIVISIONS_LOWER[$divisionIndex] . "s";
    $updates = $DIVISION_UPDATES[$divisionIndex];

    // info for keeping track of progress
    $i = 0;
    $total = sizeof($allTexts[$divisionStr]);
    $target = $total/$updates;
    $base_pct = $running_pct;
    $full_pct = $running_pct + $updates;

    // For each code
    foreach ($allTexts[$divisionStr] as $code) {
      // Send an update message if we are at the correct percentage
      if ($total > $MIN_UPDATE_SIZE && $i > $target) {
        $target += $total/$updates;
        $pct = round((($i/$total) * ($full_pct - $base_pct))+ $base_pct);
        send_message($send_full_messages, $message_index, $divisionStr, $pct, false);
        $message_index++;
      }

      // Compile the given text 
      $file = $DIR_TMP . $code . ".txt";
      $content = compileText($db, $divisionIndex, $code, $DIR_TMP);
      file_put_contents($file, $content);

      $i++;
    }
    $running_pct = $full_pct;
  }

  // Move all files
  if(is_dir($DIR_FINAL) == true) {
    deleteDir($DIR_FINAL);
  }
  rename($DIR_TMP, $DIR_FINAL);

  send_message($send_full_messages, "CLOSE", "Process complete", 100, true);
}

// True only if this is the main file
if (get_included_files()[0] == __FILE__) {
  write_headers();

  header('Content-Type: text/event-stream');
  // recommended to prevent caching of event data.
  header('Cache-Control: no-cache');

  // get database
  $db = get_db($dbname=$LEXICON_DB_NAME);

  compile_texts($db, true);

  $db->close();
}
?>
