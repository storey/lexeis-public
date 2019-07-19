<?php
require_once "../../api/database_utils.php";
require_once "./lexiconInfo.php";
// This file contains functions for recompiling the files in the prepTexts folder.


function getTokenInfo($row) {
  if (!array_key_exists("lemma", $row) || $row["lemma"] == "") {
    // Handle punctuation
    $res = array(
      "text" => $row["token"],
      "isPunct" => true,
      "context" => -1
    );
  } else {
    $str = "@" . $row["lemma"];
    if ($row["lemma_meaning"] != "") {
      $str .= ";" . $row["lemma_meaning"];
    }
    $str .= "@" . getProperlyCapitalizedToken($row) . "@";
    $res = array(
      "text" => $str,
      "isPunct" => false,
      "context" => intval($row["context"])
    );
  }
  return $res;
}

// -----------------------------------------

// Compile a section and return the text
function compileSectionText($db, $code, $baseURL) {
  global $INCLUDE_SURROUNDING;
  global $MAX_SEQUENCE;

  // Precondition: this should be sanitized, not user input.
  $codeArr = explode(".", $code);

  $tokens = array();
  $sequenceIndices = array();

  $textMatch = getMatchSQL($codeArr, "t.");
  $textQuery = "SELECT t.sequence_index as seq_index, t.token as token, t.word_index as word_index, i.lemma as lemma, i.lemma_meaning as lemma_meaning, i.context_type as context FROM text_storage as t LEFT JOIN instance_information as i ON t.token_index = i.token_index WHERE $textMatch ORDER BY t.true_word_index ASC;";

  $words = getMatches($db, $textQuery);
  while($row = getNextItem($db, $words)) {
    array_push($sequenceIndices, $row["seq_index"]);
    array_push($tokens, getTokenInfo($row));
  }

  if ($INCLUDE_SURROUNDING) {
    $book = $codeArr[0];

    $token_check = "token=';' OR token='·' OR token='.'"; //
    $prevTokens = array();
    $firstIndex = $sequenceIndices[0];
    $startQuery = "SELECT * FROM text_storage AS t INNER JOIN (SELECT MAX(sequence_index) as s_index FROM text_storage WHERE sequence_index < $firstIndex AND (($token_check) OR book!=$book OR sequence_index=0)) AS m on t.sequence_index = m.s_index";

    $startToken = getNextItem($db, getMatches($db, $startQuery));
    $startIndex = intval($startToken["sequence_index"]);//$firstIndex - 10;

    // get location array for start token
    $startLoc = getLocationArr($startToken);

    // Avoid error where first token is not shown
    if ($startIndex == 0) {
      $startIndex = -1;
    }

    // Don't show starting dots if this is the start of a book.
    if ($startLoc[0] == $codeArr[0] && $startIndex != -1) {
      array_push($prevTokens, array(
        "text" => "...",
        "isPunct" => true,
        "context" => -2
      ));
    }


    $prevQuery = "SELECT t.token as token, t.word_index as word_index, i.lemma as lemma, i.lemma_meaning as lemma_meaning, i.context_type as context FROM text_storage as t LEFT JOIN instance_information as i ON t.token_index = i.token_index WHERE t.sequence_index > $startIndex AND t.sequence_index < $firstIndex ORDER BY t.sequence_index ASC;";
    $words = getMatches($db, $prevQuery);
    while($row = getNextItem($db, $words)) {
      $res = getTokenInfo($row);
      $res["context"] = -2;
      array_push($prevTokens, $res);
    }

    // If this will only be ... , don't show anything
    if (sizeof($prevTokens) <= 1) {
      $prevTokens = array();
    }


    $nextTokens = array();
    $lastIndex = $sequenceIndices[sizeof($sequenceIndices)-1];
    $endQuery = "SELECT * FROM text_storage AS t INNER JOIN (SELECT MIN(sequence_index) as s_index FROM text_storage WHERE sequence_index > $lastIndex AND (($token_check) OR book!=$book OR sequence_index=$MAX_SEQUENCE)) AS m on t.sequence_index = m.s_index";
    $endToken = getNextItem($db, getMatches($db, $endQuery));
    $endIndex = intval($endToken["sequence_index"]);

    // get location array for start token
    $endLoc = getLocationArr($endToken);

    // Don't show character of next book if we spill over
    if ($endLoc[0] != $codeArr[0]) {
      $endIndex--;
    }

    $nextQuery = "SELECT t.token as token, t.word_index as word_index, i.lemma as lemma, i.lemma_meaning as lemma_meaning, i.context_type as context FROM text_storage as t LEFT JOIN instance_information as i ON t.token_index = i.token_index WHERE t.sequence_index > $lastIndex AND t.sequence_index <= $endIndex ORDER BY t.sequence_index ASC;";
    $words = getMatches($db, $nextQuery);
    while($row = getNextItem($db, $words)) {
      $res = getTokenInfo($row);
      $res["context"] = -2;
      array_push($nextTokens, $res);
    }


    // Don't show ending dots if we hit a book end
    if ($endLoc[0] == $codeArr[0] && $endIndex != $MAX_SEQUENCE) {
      array_push($nextTokens, array(
        "text" => " ...",
        "isPunct" => true,
        "context" => -2
      ));
    }


    // If this will only be ... , don't show anything
    if (sizeof($nextTokens) <= 1) {
      $nextTokens = array();
    }

    $tokens = array_merge($prevTokens, $tokens, $nextTokens);
  }

  // Include initial punctuation in context.
  if ($tokens[0]["context"] == -1 && sizeof($tokens) > 1) {
    $tokens[0]["context"] = $tokens[1]["context"];
  }
  // Set context of punctuation to match those around it if both are the same.
  for ($i = 1; $i < sizeof($tokens); $i++) {
    $currentContext = $tokens[$i]["context"];

    if ($currentContext == -1) {
      $prevContext = $tokens[$i-1]["context"];
      $next = 1;
      $nextContext = -1;
      while ($i+$next < sizeof($tokens) && $nextContext == -1) {
        $nextContext = $tokens[$i+$next]["context"];
        $next++;
      }

      if ($prevContext == $nextContext || $i + $next == sizeof($tokens)) {
        $tokens[$i]["context"] = $prevContext;
      }
    }
  }

  $textPieces = array();
  // true if we skip next space; we don't want a starting space so starts true
  $skipNextSpace = true;
  // Previous context
  $prevContext = -1;
  // Combine tokens together
  for ($i = 0; $i < sizeof($tokens); $i++) {
    $t = $tokens[$i]["text"];
    $isPunct = $tokens[$i]["isPunct"];
    $c = $tokens[$i]["context"];

    $intoContext = ($c != $prevContext && $c != -1);
    $outContext = ($c != $prevContext && $prevContext != -1);

    // True if this is a piece of punctuation that should attach to the next word.
    $isStartPunct = preg_match("/^[\(\<\[“«]/iu", $t);
    $isEndPunct = $isPunct && !$isStartPunct && !preg_match("/^[†]$/iu", $t);


    // Clean up prior context if necessary
    if ($outContext) {
      $piece ="</span>";
    } else {
      $piece = "";
    }

    // Start with a space in most circumstances, unless the last token wanted
    // to skip a space or this token wants to hug the previous one.
    if ($skipNextSpace || $isEndPunct) {
      $piece .= "";
    } else {
      $piece .= " ";
    }

    // Open context if necessary
    if ($intoContext) {
      $contextType = "";
      if ($c == -2) {
        $contextType = "x";
      } else {
        $contextType = "" . intval($c);
      }
      $piece .= "<span class=\"context-$contextType\">";
    }

    $piece .= $t;

    // Add piece to array
    array_push($textPieces, $piece);

    // only skip next space if this is a piece of starting punctuation
    if ($isStartPunct) {
      $skipNextSpace = true;
    } else {
      $skipNextSpace = false;
    }
    // Update previous context to current context
    $prevContext = $c;
  }

  $text = join("", $textPieces);
  // Always end with a span
  return "<p> " . $text . "</span> </p>";
}

// Compile a chapter and return the text
function compileChapterText($db, $code, $baseURL) {
  global $INCLUDE_SURROUNDING;
  // Get section names
  $sectionNames = array();
  $numParts = 0;
  // Get all sections
  foreach (glob($baseURL . "$code.*.txt") as $filename) {
    $pieces = explode(".", $filename);
    // Only Sections
    if (sizeof($pieces) == 4) {
      $sectionNames[$pieces[2]] = $filename;
      $numParts++;
    }
  }
  // Sort section names
  ksort($sectionNames);


  // Get Sections
  $sections = array();

  $index = 0;
  foreach($sectionNames as $sectionNumber => $filename) {
    $section = file_get_contents($filename);

    $labelStr = "<span class=\"sectionLabel\">$sectionNumber</span>";
    $section = str_replace("<p>", "<p> $labelStr", $section);

    if ($INCLUDE_SURROUNDING) {
      // Remove start context for all but first
      if ($index > 0) {
        $section = mb_ereg_replace("<p>(.*?)<span class=\"context-x\">.*?</span>", "<p>\\1", $section);
      } else {
        // Hack to prevent next step from removing whole section
        $section = mb_ereg_replace("<p>(.*?)<span class=\"context-x\">", "<p>\\1<span class=\"context-y\">", $section);
      }

      // Remove end context for all but end
      if ($index < $numParts-1) {
        $section = mb_ereg_replace("<span class=\"context-x\">.*?</span> </p>", "</p>", $section);
      } else {
        $section = mb_ereg_replace("<span class=\"context-x\">", "</p> <p> <span class=\"context-x\">", $section);
      }

      // Undo change from above
      if ($index == 0) {
        $section = mb_ereg_replace("<p>(.*?)<span class=\"context-y\">(.*?)</span>", "<p> <span class=\"context-x\">\\2</span> </p> <p> \\1", $section);
      }
    }

    array_push($sections, $section);
    $index++;
  }
  return join(" ", $sections);
}

// Compile a book and return the text
function compileBookText($db, $code, $baseURL) {
  global $INCLUDE_SURROUNDING;

  // Get chapter names
  $chapterNames = array();
  // Get all subchapters
  foreach (glob($baseURL . "$code.*.txt") as $filename) {
    $pieces = explode(".", $filename);
    // Only Chapters
    if (sizeof($pieces) == 3) {
      $chapterNames[$pieces[1]] = $filename;
    }
  }
  // Sort chapter names
  ksort($chapterNames);


  // Get Chapters
  $chapters = array();

  foreach($chapterNames as $chapterNumber => $filename) {
    $chapter = "<div> <h3>Chapter $chapterNumber</h3> ";

    $chapter .= file_get_contents($filename);

    $chapter .= " </div>";

    if ($INCLUDE_SURROUNDING) {
      // Remove all context
      $chapter = mb_ereg_replace("<span class=\"context-x\">.*?</span>", "", $chapter);
    }

    array_push($chapters, $chapter);
  }
  return join(" ", $chapters);
}

// compile a text
function compileText($db, $divisionIndex, $code, $baseURL) {
  if ($divisionIndex == 0) {
    return compileBookText($db, $code, $baseURL);
  } else if ($divisionIndex == 1) {
    return compileChapterText($db, $code, $baseURL);
  } else {
    // This allows us to get away with using -1
    return compileSectionText($db, $code, $baseURL);
  }
}

// Update the prepared text file for a given section.
function updateFile($db, $divisionIndex, $code) {
  $file = "prepTexts/$code.txt";
  $content = compileText($db, $divisionIndex, $code, "prepTexts/");
  file_put_contents($file, $content);
}

// Make changes to a section and its parent chapter and book
function changeSection($db, $code) {
  global $TEXT_DIVISIONS;
  $num_divisions = sizeof($TEXT_DIVISIONS);

  $parts = explode(".", $code);

  for ($i = $num_divisions-1; $i >= 0; $i--) {
    $spec = implode(".", array_slice($parts, 0, $i+1));
    updateFile($db, $i, $spec);
  }
}

// Get full list of prepared texts
function getFullPreppedTextList($db) {
  global $TEXT_DIVISIONS_LOWER;
  $num_divisions = sizeof($TEXT_DIVISIONS_LOWER);

  $res = array();

  // Get list of reach level of texts
  for ($i = $num_divisions-1; $i >= 0; $i--) {
    $groupName = $TEXT_DIVISIONS_LOWER[$i] . "s";

    $res[$groupName] = array();

    // e.g "book, chapter, sections"
    $names = implode(", ", array_slice($TEXT_DIVISIONS_LOWER, 0, $i+1));
    // get options
    $query = "SELECT $names FROM text_storage GROUP BY $names";
    $matches = getMatches($db, $query);

    while($row = getNextItem($db, $matches)) {
      $code = implode(".", array_slice(getLocationSubArr($row, $i+1), 0, $i+1));
      array_push($res[$groupName], $code);
    }
  }
  return $res;
}
?>
