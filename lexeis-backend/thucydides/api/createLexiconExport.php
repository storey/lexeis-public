<?php
require_once "../../api/master.php";
require_once "../../api/database_utils.php";
require_once "../../api/access_guard.php";
require_once "lexiconUtils.php";

require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;

// Get XML text file
function getXML($db) {
  global $SEND_FULL_MESSAGES;
  global $TEXT_DIVISIONS_LOWER;

  $XML_HEADER = <<<XML
<TEI.2>
<teiHeader status="new" type="text">
  <fileDesc>
    <titleStmt>
      <title>The Peloponnesian War</title>
      <author>Thucydides</author>
      <sponsor>Perseus Project, Tufts University</sponsor>
      <principal>Gregory Crane</principal>
      <respStmt>
        <resp>Prepared under the supervision of</resp>
        <name>Lisa Cerrato</name>
        <name>William Merrill</name>
        <name>Elli Mylonas</name>
        <name>David Smith</name>
      </respStmt>
    </titleStmt>

    <extent>about 197Kb</extent>

    <publicationStmt>
      <publisher>Trustees of Tufts University</publisher>
      <pubPlace>Medford, MA</pubPlace>
      <authority>Perseus Project</authority>
    </publicationStmt>

    <sourceDesc default="NO">
      <biblStruct default="NO">
        <monogr>
        <author>Thucydides</author>
        <title>The Peloponnesian War</title>
        <title type="full">Historiae in two volumes</title>

        <imprint><publisher>Oxford, Oxford University Press</publisher><date>1942</date></imprint>
        </monogr>
      </biblStruct>
    </sourceDesc>
  </fileDesc>

  <encodingDesc>
    <refsDecl doctype="TEI.2">
      <state unit="book" delim="." />
      <state unit="chapter" delim="." />
      <state unit="section" />
    </refsDecl>
  </encodingDesc>

  <profileDesc>
    <langUsage default="NO">
      <language id="greek" usage="100">Greek</language>
    </langUsage>
    <textClass>
      <keywords scheme="genre">
        <term>prose</term>
      </keywords>
    </textClass>
  </profileDesc>
  <revisionDesc>
    <change>
      <date>9/2010</date><respStmt><name>Helma Dik</name><resp>(n/a)</resp></respStmt><item>speaker indications added by Hugh Wynne; q type="treaty" added for truces and alliances.</item><item>Λέσβον for λέσβον</item>
    </change>
    <change>
      <date>8/1991</date><respStmt><name>EM</name><resp>(n/a)</resp></respStmt><item>Revise DTDs and recheck texts.</item>
    </change>
  </revisionDesc>
</teiHeader>
XML;

  $xml = array($XML_HEADER);
  array_push($xml, "<text>\n<body>");

  // Get valid text text division combos
  $textDivs = implode(", ", $TEXT_DIVISIONS_LOWER);
  $query = "SELECT $textDivs FROM text_storage GROUP BY $textDivs;";
  $matches = getMatches($db, $query);
  $textPieces = array();
  while($row = getNextItem($db, $matches)) {
    array_push($textPieces, getLocationArr($row));
  }

  // Print each part of the text
  //print("Generating XML text file...");
  $nextPct = 1;
  $multip = 100/sizeof($textPieces);

  $prev = array(0, 0, 0);
  for ($i = 0; $i < sizeof($textPieces); $i++) {
    $piece = $textPieces[$i];
    $update = false;
    $s = "";
    if ($piece[0] > $prev[0]) {
      $update = true;
      if ($prev[0] != 0) {
        $s .= "</p>\n</div1>\n";
      }
      $s .= "<div1 n=\"" . $piece[0] . "\" type=\"book\" org=\"uniform\" sample=\"complete\">\n<p>\n";
    }
    if ($update || $piece[1] > $prev[1]) {
      $s .= "<milestone unit=\"chapter\" n=\"" . $piece[1] . "\" />\n";
    }

    $s .= "<milestone unit=\"section\" n=\"" . $piece[2] . "\" />";

    $sectionTokens = array();

    $textCondition = getMatchSQL($piece);
    $query = "SELECT token, token_index FROM text_storage WHERE $textCondition;";
    $matches = getMatches($db, $query);
    while($row = getNextItem($db, $matches)) {
      $index = intval($row["token_index"]);
      if ($index == -1) {
        $token = $row["token"];
        if ($token == "<") {
          $token = "&lt;";
        } else if ($token == ">") {
          $token = "&gt;";
        }
        array_push($sectionTokens, "<w>" . $token . "</w> ");
      } else {
        array_push($sectionTokens, "<w id=\"" . $row["token_index"] . "\">" . $row["token"] . "</w> ");
      }
    }

    $s .= implode("", $sectionTokens);
    array_push($xml, $s);

    $prev = $piece;


    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "xml_$i", "Building XML of text...", $nextPct, false);
      $nextPct++;
    }
  }
  unset($matches);
  //print("Done.<br/>");

  array_push($xml, "</p>\n</div1>\n</body>\n</text>\n</TEI.2>");

  return implode("\n", $xml);
}

// Create an sqlite database
function createLiteDB($mysqli, $filename, $textfile) {
  global $SEND_FULL_MESSAGES;
  global $USE_MYSQL;
  $USE_MYSQL = false;
  $lite = get_lite_db($filename);

  // Empirically there is a limit on the number of simultaneous inserts the
  // reclaim server can do; doing them one at a time takes forever but
  // doing too many at once silently fails. 500 doesn't work, 450 does.
  $MAX_INSERTS = 450;

  // Create Lexicon table
  runQuery($lite, 'DROP TABLE IF EXISTS Lexicon');
  runQuery($lite, "CREATE TABLE `Lexicon` (
  	`lexid`	integer NOT NULL,
  	`token`	text,
  	`code`	text,
  	`lemma`	text,
  	`alt_lsj`	varchar ( 64 ) DEFAULT NULL,
  	`note`	varchar ( 20 ) DEFAULT NULL,
  	`blesslemma`	boolean DEFAULT NULL,
  	`blesslex`	boolean DEFAULT NULL,
  	PRIMARY KEY(`lexid`)
  );");

  // Create frequences table
  runQuery($lite, 'DROP TABLE IF EXISTS frequencies');
  runQuery($lite, "CREATE TABLE `frequencies` (
  	`lemma`	TEXT,
  	`rank`	integer,
  	`count`	integer,
  	`rate`	real,
  	`lookupform`	TEXT
  );");

  // Create parses table
  runQuery($lite, 'DROP TABLE IF EXISTS parses');
  runQuery($lite, "CREATE TABLE `parses` (
  	`parseid`	integer,
  	`tokenid`	integer,
  	`lex`	integer,
  	`meaning`	text,
  	`code`	text,
  	`lemma`	text,
  	`authority`	text,
  	`file`	text,
  	`prob`	float,
  	PRIMARY KEY(`parseid`)
  );");

  // Create shortdefs table
  runQuery($lite, 'DROP TABLE IF EXISTS tokens');
  runQuery($lite, "CREATE TABLE `shortdefs` (
  	`lemma`	text,
  	`def`	text
  );");

  // Create sqlitemanager_extras table
  runQuery($lite, 'DROP TABLE IF EXISTS sqlitemanager_extras');
  runQuery($lite, "CREATE TABLE `sqlitemanager_extras` (
  	`name`	varchar,
  	`value`	varchar,
  	`kind`	varchar
  );");

  // Create tokens table
  runQuery($lite, 'DROP TABLE IF EXISTS tokens');
  runQuery($lite, "CREATE TABLE `tokens` (
  	`tokenid`	integer,
  	`content`	text,
  	`seq`	integer,
  	`type`	text,
  	`file`	text,
  	PRIMARY KEY(`tokenid`)
  );");

  // get total number of token-lemma combinations
  $matches = $mysqli->query("SELECT count(*) FROM (SELECT t.token, i.lemma FROM text_storage AS t LEFT JOIN instance_information AS i on t.token_index=i.token_index WHERE t.token_index != -1 GROUP BY BINARY t.token, BINARY i.lemma) AS sub;");
  $matches->data_seek(0);
  $row = $matches->fetch_assoc();
  $total_token_lemma_combos = intval($row["count(*)"]);

  // get total number of non-punctuation tokens
  $matches = $mysqli->query("SELECT count(*) FROM text_storage WHERE token_index > 0;");
  $matches->data_seek(0);
  $row = $matches->fetch_assoc();
  $total_tokens = intval($row["count(*)"]);

  // get total number of lemmata
  $matches = $mysqli->query("SELECT count(*) FROM lemmata;");
  $matches->data_seek(0);
  $row = $matches->fetch_assoc();
  $total_lemmata= intval($row["count(*)"]);

  // get total number of lemmata with a frequency at least 100
  $matches = $mysqli->query("SELECT count(*) FROM lemmata WHERE frequency_all >= 100;");
  $matches->data_seek(0);
  $row = $matches->fetch_assoc();
  $total_lemmata_over_100 = intval($row["count(*)"]);

  // Add to Lexicon
  //echo("Adding Lexicon entries to the database...");
  $nextPct = 1;
  $factor = 2;
  $multip = $factor*50/($total_token_lemma_combos);

  $textfile = escapeString($lite, $textfile);
  $lexEntries = array();

  $matches = $mysqli->query("SELECT t.token as tok, i.lemma as lem FROM text_storage AS t LEFT JOIN instance_information AS i on t.token_index=i.token_index WHERE t.token_index != -1 GROUP BY BINARY t.token, BINARY i.lemma ORDER BY t.token ASC;");
  $matches->data_seek(0);
  $lexid = 1;
  $inserts = array();
  while ($row = $matches->fetch_assoc()) {
    $token = escapeString($lite, $row["tok"]);

    $lemma = escapeString($lite, $row["lem"]);
    // runQuery($lite, "INSERT INTO `Lexicon`(`lexid`,`token`,`code`,`lemma`) VALUES ($lexid, '$token', '?', '$lemma');");
    array_push($inserts, "($lexid, '$token', '?', '$lemma')");

    $t = $row["tok"];
    if (!array_key_exists($t, $lexEntries)) {
      $lexEntries[$t] = array();
    }
    $lexEntries[$t][$row["lem"]] = $lexid;

    $lexid++;

    if ($lexid*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "db_lex_$lexid", "Adding lexicon entries to database...", $nextPct/$factor, false);
      $nextPct++;
    }
  }

  // Run in batches to prevent overloading multiple insert
  $start = 0;
  while ($start < sizeof($inserts)) {
    $arr = array_slice($inserts, $start, $MAX_INSERTS);
    runQuery($lite, "INSERT INTO `Lexicon`(`lexid`,`token`,`code`,`lemma`) VALUES " . implode(", ", $arr) . ";");
    $start += $MAX_INSERTS;

    $prog = round(50 + 50*$start/sizeof($inserts), 2);
    send_message($SEND_FULL_MESSAGES, "db_lex_p2_$start", "Adding lexicon entries to database...", $prog, false);
  }

  send_message($SEND_FULL_MESSAGES, "db_lex_done", "Adding lexicon entries to database...", 100, false);

  //echo("Done.<br/>");

  // Add to tokens, parses tables
  //echo("Adding tokens to the database...");
  $nextPct = 1;
  $factor = 4;
  $multip = $factor*50/($total_tokens);

  $textfile = escapeString($lite, $textfile);
    // A little different from the original, as we ignore punctuation
  $matches = $mysqli->query("SELECT * FROM text_storage AS t LEFT JOIN instance_information AS i on t.token_index=i.token_index WHERE t.token_index != -1 ORDER BY t.token_index ASC;");
  $matches->data_seek(0);
  $parseid = 1;
  $inserts1 = array();
  $inserts2 = array();
  while ($row = $matches->fetch_assoc()) {
    $token = escapeString($lite, $row["token"]);
    $tokenid = intval($row["token_index"]);

    $token_type = "word";
    $lemma = escapeString($lite, $row["lemma"]);
    $meaning = $row["lemma_meaning"];

    $seq = intval($row["sequence_index"]) + 1;
    // runQuery($lite, "INSERT INTO `tokens`(`tokenid`,`content`,`seq`,`type`,`file`) VALUES ($tokenid,'$token',$seq,'$token_type','$textfile');");
    array_push($inserts1, "($tokenid,'$token',$seq,'$token_type','$textfile')");

    $lexid = $lexEntries[$row["token"]][$row["lemma"]];
    // runQuery($lite, "INSERT INTO `parses`(`parseid`,`tokenid`,`lex`,`meaning`,`code`,`lemma`,`authority`,`file`,`prob`) VALUES ($parseid,$tokenid,$lexid,'$meaning',NULL,NULL,'lexeis',NULL,1);");
    array_push($inserts2, "($parseid,$tokenid,$lexid,'$meaning',NULL,NULL,'lexeis',NULL,1)");

    $parseid++;

    if ($parseid*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "db_tokens_$parseid", "Adding tokens to database...", $nextPct/$factor, false);
      $nextPct++;
    }
  }

  // Run in batches to prevent overloading multiple insert
  $start = 0;
  while ($start < sizeof($inserts1)) {
    $arr = array_slice($inserts1, $start, $MAX_INSERTS);
    runQuery($lite, "INSERT INTO `tokens`(`tokenid`,`content`,`seq`,`type`,`file`) VALUES " . implode(", ", $arr) . ";");
    $start += $MAX_INSERTS;

    $prog = round(50 + 25*$start/sizeof($inserts1), 2);
    send_message($SEND_FULL_MESSAGES, "db_tokens_p2_$start", "Adding tokens to database...", $prog, false);
  }

  // Run in batches to prevent overloading multiple insert
  $start = 0;
  while ($start < sizeof($inserts2)) {
    $arr = array_slice($inserts2, $start, $MAX_INSERTS);
    runQuery($lite, "INSERT INTO `parses`(`parseid`,`tokenid`,`lex`,`meaning`,`code`,`lemma`,`authority`,`file`,`prob`) VALUES " . implode(", ", $arr) . ";");
    $start += $MAX_INSERTS;

    $prog = round(75 + 25*$start/sizeof($inserts2), 2);
    send_message($SEND_FULL_MESSAGES, "db_tokens_p3_$start", "Adding tokens to database...", $prog, false);
  }

  send_message($SEND_FULL_MESSAGES, "db_tokens_done", "Adding tokens to database...", 100, false);
  //echo("Done.<br/>");

  // Add frequencies table
  //echo("Adding frequencies to the database...");
  $nextPct = 1;
  $multip = 50/$total_lemmata_over_100;

  $matches = $mysqli->query("SELECT * FROM lemmata WHERE frequency_all >= 100 ORDER BY frequency_all DESC;");
  $matches->data_seek(0);
  $rank = 1;
  $inserts = array();
  while ($row = $matches->fetch_assoc()) {
    $lemma = escapeString($lite, $row["lemma"]);
    $count = $row["frequency_all"];
    // I'm not actually sure how this value was calculated in the original so I'll leave it at 0.
    $rate = 0;// floatval($count)/floatval($total_tokens);
    // runQuery($lite, "INSERT INTO `frequencies`(`lemma`,`rank`,`count`,`rate`,`lookupform`) VALUES ('$lemma', $rank, $count, $rate, '$lemma');");
    array_push($inserts, "('$lemma', $rank, $count, $rate, '$lemma')");
    $rank++;

    if ($rank*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "db_freqs_$rank", "Adding frequencies to database...", $nextPct, false);
      $nextPct++;
    }
  }

  // Run in batches to prevent overloading multiple insert
  $start = 0;
  while ($start < sizeof($inserts)) {
    $arr = array_slice($inserts, $start, $MAX_INSERTS);
    runQuery($lite, "INSERT INTO `frequencies`(`lemma`,`rank`,`count`,`rate`,`lookupform`) VALUES " . implode(", ", $arr) . ";");
    $start += $MAX_INSERTS;

    $prog = round(50 + 50*$start/sizeof($inserts), 2);
    send_message($SEND_FULL_MESSAGES, "db_freqs_p2_$start", "Adding frequencies to database...", $prog, false);
  }

  send_message($SEND_FULL_MESSAGES, "db_freqs_done", "Adding frequencies to database...", 100, false);
  //echo("Done.<br/>");

  // Add shortdefs
  //echo("Adding short defs to the database...");
  $nextPct = 1;
  $factor = 2;
  $multip = $factor*50/($total_lemmata);

  $matches = $mysqli->query("SELECT * FROM lemmata;");
  $matches->data_seek(0);
  $count = 0;
  $inserts = array();
  while ($row = $matches->fetch_assoc()) {
    $lemma = escapeString($lite, $row["lemma"]);
    $def = escapeString($lite, $row["short_def"]);
    // runQuery($lite, "INSERT INTO `shortdefs`(`lemma`,`def`) VALUES ('$lemma', '$def');");
    array_push($inserts, "('$lemma', '$def')");

    $count++;
    if ($count*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "db_defs_$count", "Adding short defs to database...", $nextPct/$factor, false);
      $nextPct++;
    }
  }

  // Run in batches to prevent overloading multiple insert
  $start = 0;
  while ($start < sizeof($inserts)) {
    $arr = array_slice($inserts, $start, $MAX_INSERTS);
    runQuery($lite, "INSERT INTO `shortdefs`(`lemma`,`def`) VALUES " . implode(", ", $arr) . ";");
    $start += $MAX_INSERTS;

    $prog = round(50 + 50*$start/sizeof($inserts), 2);
    send_message($SEND_FULL_MESSAGES, "db_defs_p2_$start", "Adding short defs to database...", $prog, false);
  }

  send_message($SEND_FULL_MESSAGES, "db_defs_done", "Adding short defs to database...", 100, false);
  //echo("Done.<br/>");

  // Add sqlitemanager_extra data
  $matches = $mysqli->query("SELECT * FROM lemmata;");
  $value = "select distinct tokens.content from parses, tokens where parses.tokenid = tokens.tokenid and parses.lemma = '<unknown>'";
  $value = escapeString($lite, $value);

  runQuery($lite, "INSERT INTO `sqlitemanager_extras`(`name`,`value`,`kind`) VALUES ('unknownlisting','$value','Report');");

  // same value ofr this one
  runQuery($lite, "INSERT INTO `sqlitemanager_extras`(`name`,`value`,`kind`) VALUES ('listunknowns','$value','Report');");

  $value = "This db tries its (i.e. the manager of this db tries her..) hardest to avoid deprecated characters, but of course they might creep in. This means, among other things, that tonos variants should always be used where available (and not oxia), and the middle dot (00B7) instead of the ano teleia (0387). The apostrophe used is the modifier letter apostrophe: 02BC. This enables including the apostrophe when an entire word is selected, thereby distinguishing word forms with and without an elided vowel.";
  $value = escapeString($lite, $value);
  runQuery($lite, "INSERT INTO `sqlitemanager_extras`(`name`,`value`,`kind`) VALUES ('Deprecated characters','$value','Note');");

  $value = "Capitalized words are generally the most unreliable, due to a glitch in our process when we first set up this database. Credit, however, for what there is here that is correct, starts with the original morpheus data from the Perseus Project at Tufts University. Then to indefatigable disambiguators such as Martin Mueller, who handled all of early hexameter poetry in the database, and Francesco Mambrini who did all of Aeschylus. The prose comes mainly from me. The original design of the full database (tokens, parses, lexicon, shortdefs) is by Richard Whaling, and it has stood me in good stead for a long time now. Please report problems through the parsing problem form at http://tinyurl.com/parsingproblem; Addendum: original data from Helma Dik has been modified as part of the Lexeis Project.";
  $value = escapeString($lite, $value);
  runQuery($lite, "INSERT INTO `sqlitemanager_extras`(`name`,`value`,`kind`) VALUES ('Credits and caution!','$value','Note');");

  $value = "This is the full sqlite.db as far as Thuc. is concerned. Table Tokens - one row for each token, including punctuation (needed for automatic parser). Tokenid uniquely identifies in full database, sequence determines sequential place in text. Parses - tokenids here get associated with possible parses and probabilities. Authority = \'HD\': I picked this parse. Probability 1 or .001 does not mean much! Also these do not add up to 1 a lot of the time, because, since parsing, I have removed entries from the \'lexicon\' table and their corresponding parses. Key feature here is the \'lex\' column; it corresponds to lexid (sorry) in the Lexicon table, where you find the actual, written out, token-code-lemma for each item in the parse table (barring exceptional circumstances like words broken by editorial <> in the text; those are lemmatized within the parse table). Shortdefs - you are familiar with these already.";
  $value = escapeString($lite, $value);
  runQuery($lite, "INSERT INTO `sqlitemanager_extras`(`name`,`value`,`kind`) VALUES ('More tables and fields (Thucydides)','$value','Note');");

  $value = "Lexicon table: token, pos code, lemma, alt_lsj (for alternative lexicon entries, NOT necessarily in lsj, for instance where LSJ lemmatization is different from that in db, or where derivatives are found inside a different LSJ entry). blesslex: this individual token-pos-lemma combination has been selected as the appropriate parse in context by a human at least once. blesslemma: this lemma was once selected by a human, but not necessarily for this token. notes: E.g. NCA - not classical Attic, in hopes that a parser might be taught to ignore these, and to reassure users about dialect forms popping up as often inappropriate parses. shortdefs table: lemma plus definition; short names transliterated following conservative conventions for ease of linking to the reference works in the collection.";
  $value = escapeString($lite, $value);
  runQuery($lite, "INSERT INTO `sqlitemanager_extras`(`name`,`value`,`kind`) VALUES ('Tables and fields','$value','Note');");


  // Create file_seq index
  runQuery($lite, "CREATE INDEX `file_seq` ON `tokens` (
  	`file`,
  	`seq`
  );");

  // Create freqbylemma index
  runQuery($lite, "CREATE INDEX `freqbylemma` ON `frequencies` (`lemma`);");

  // Create headwordlookup index
  runQuery($lite, "CREATE INDEX `headwordlookup` ON `shortdefs` (`lemma`);");

  // Create headwords index
  runQuery($lite, "CREATE INDEX `headwords` ON `Lexicon` (`token`);");

  // Create parse_tokens index
  runQuery($lite, "CREATE INDEX `parse_tokens` ON `parses` (`tokenid`);");

  // Create parsesbylemma index
  runQuery($lite, "CREATE INDEX `parsesbylemma` ON `parses` (`lemma`);");

  // Create parsesbylex index
  runQuery($lite, "CREATE INDEX `parsesbylex` ON `parses` (`lex`);");

  unset($matches);
  unset($row);
  unset($lexEntries);

  $lite->close();
  $USE_MYSQL = true;
}

// Given an integer, get the letter for the associated column in excel
// columns indexed from 0. (so getExcelLetter(0) -> "A")
$EXCEL_CONVERSION = array(
  "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"
);
// Might break for 3 letters
function getExcelLetter($num) {
  global $EXCEL_CONVERSION;

  if ($num >= 26) {
    $end = $num % 26;
    $rest = (($num - $end)/26) - 1;
    return getExcelLetter($rest) . $EXCEL_CONVERSION[$end];
  }

  return $EXCEL_CONVERSION[$num];
}

// Create an excel file from an array
function createXLS($rows, $filepath) {

  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();

  for ($i = 0; $i < sizeof($rows); $i++) {
    $row = $rows[$i];
    for ($j = 0; $j < sizeof($row); $j++) {
      $cell = getExcelLetter($j) . ($i+1);
      $sheet->setCellValue($cell, $row[$j]);
    }
  }

  $writer = new Xlsx($spreadsheet);
  $writer->save($filepath);

  unset($spreadsheet);
  unset($writer);
}

// Create the dictionary of lemmata
function createLemmaXLS($db, $filepath) {
  global $SEND_FULL_MESSAGES;
  $rows = array();

  $header_row = array("Matched", "Lemma", "Short Definition", "Compounds", "Roots", "Sphere", "Part of Communication", "Frequency", "Illustration Caption", "Bibliography", "Notes");
  array_push($rows, $header_row);

  //echo("Creating Lemma Excel...");
  $total_lemmata = getNumMatches($db, "lemmata_all", "");
  $nextPct = 1;
  $multip = 100/$total_lemmata;

  $matches = getMatches($db, "SELECT * FROM lemmata WHERE deleted=0 ORDER BY lemma;");
  $i = 0;
  while($row = getNextItem($db, $matches)) {
    $groups = getLemmaGroups($db, $row["lemmaid"], False);
    $semantic_groups = array();
    for ($j = 0; $j < sizeof($groups["sgs"]); $j++) {
      array_push($semantic_groups, $groups["sgs"][$j]["name"]);
    }

    $compoundStr = join("; ", $groups["compounds"]);
    $rootStr = join("; ", $groups["roots"]);
    $semanticStr = join("; ", $semantic_groups);
    $sheet_row = array($i, $row["lemma"], $row["short_def"], $compoundStr, $rootStr, $semanticStr, $row["part_of_speech"], $row["frequency_all"], $row["illustration_caption"], $row["bibliography_text"], "");
    array_push($rows, $sheet_row);
    $i++;

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "xls_lemmata_$i", "Creating Lemmata Excel File...", $nextPct, false);
      $nextPct++;
    }
  }
  //echo("Done</br>");

  createXLS($rows, $filepath);
  unset($rows);
  return;
}

// Given a row with location information, get the tokens for that row
function getTextPiece($db, $row) {
  $token = $row["token"];

  $res = "";

  $conditionString = getMatchSQL(getLocationArr($row), "t.");
  $matches = getMatches($db, "SELECT t.token as token FROM text_storage AS t WHERE t.token_index>0 AND $conditionString ORDER BY t.true_word_index;");
  $i = 0;
  while($row2 = getNextItem($db, $matches)) {
    $t = $row2["token"];
    $res .= "$t ";
    $i++;
  }
  return $res;
}

// Get a text sequence starting with the token whose row is $info that is unique
// within $text.
// This might break for something really long, but that doesn't happen in Thucyides
// and I think it would be quite unlikely.
function findUniqueToken($db, $info, $tokens, $extraTokens, $text) {
  $count = substr_count($text, $tokens);
  if ($count == 1) {
    return $tokens;
  }
  $textCondition = getMatchSQL(getLocationArr($info), "t.");

  // Check whether this is the first one, which is only true when we're looking
  // At one token.
  if ($extraTokens == 0) {
    $myIndex = $info["token_index"];
    $t = $info["token"];
    $query = "SELECT MIN(t.token_index) as first_index FROM text_storage AS t WHERE $textCondition AND BINARY t.token='$t';";
    $firstIndex = getNextItem($db, getMatches($db, $query))["first_index"];
    // If this is the first occurrence, just return it.
    if ($myIndex == $firstIndex) {
      return $tokens;
    }
  }
  // If we want an occurrence after the first, we need more context, so grab
  // one additional word. This is slow but will only happen maybe 5 times a book.
  $extraTokens++;
  $tokenList = array();
  $wi_start = $info["word_index"];
  $wi_end = $wi_start + $extraTokens;
  $query = "SELECT token FROM text_storage AS t INNER JOIN instance_information AS i on t.token_index=i.token_index WHERE $textCondition AND t.word_index >= $wi_start AND t.word_index <= $wi_end;";
  $matches = getMatches($db, $query);
  while ($row = getNextItem($db, $matches)) {
    $t = $row["token"];
    array_push($tokenList, $t);
  }
  $newTokens = join(" ", $tokenList);
  // This would only happen if we wanted the second occurrence and all tokens
  // after it to the end of the section *also* occurred in the same order after
  // the first occurrence. This would make it super hard for a layperson
  // to specify this and would require you to start using tokenids.
  // This doesn't happen in thucydides and seems unlikely.
  if ($newTokens == $tokens) {
    return $row["token_index"];
  }
  return findUniqueToken($db, $info, $newTokens, $extraTokens, $text);
}

// Given information about a token, get the token and the minimum number of
// Following tokens to disambiguate it within its piece of the text
function getStartToken($db, $row, $text) {
  $t = $row["token"];
  return findUniqueToken($db, $row, $t, 0, $text);
}

// Create an excel file with context information
function createContextXLS($db, $filepath) {
  global $SEND_FULL_MESSAGES;
  global $INDEX_TO_CONTEXT_NAME;
  $rows = array();

  $header_row = array("Book", "Chapter Start", "Section Start", "First Word", "Chapter End", "Section End", "Last Word", "Type", "Description", "Notes");
  array_push($rows, $header_row);

  //echo("Creating Context Excel...");
  $total_tokens = getNextItem($db, getMatches($db, "SELECT count(*) FROM instance_information AS i INNER JOIN text_storage AS t ON i.token_index = t.token_index;"))["count(*)"];
  $nextPct = 1;
  $multip = 100/$total_tokens;

  $matches = getMatches($db, "SELECT * FROM instance_information AS i INNER JOIN text_storage AS t ON i.token_index = t.token_index ORDER BY t.book, t.chapter, t.section, t.true_word_index;");
  $i = 0;

  $priorContext = -1;
  $priorBook = -1;
  $priorChapter = -1;
  $priorSection = -1;
  $priorToken = "";
  $sectionText = "";
  // info for a context part
  $info = array(
    "book" => 0,
    "chapter_start" => 0,
    "section_start" => 0,
    "first_word" => "",
    "chapter_end" => 0,
    "section_end" => 0,
    "last_word" => "",
    "type" => -1
  );

  while($row = getNextItem($db, $matches)) {
    $context = $row["context_type"];
    $book = $row["book"];
    $chapter = $row["chapter"];
    $section = $row["section"];
    $token = $row["token"];

    if ($section != $priorSection || $chapter != $priorChapter || $book != $priorBook) {
      $sectionText = getTextPiece($db, $row);
    }

    if ($context != $priorContext || $book != $priorBook) {
      // If this isn't the very start, add the previous context
      if ($i > 0) {
        $info["chapter_end"] = $priorChapter;
        $info["section_end"] = $priorSection;

        $info["last_word"] = $priorToken;
        $sheet_row = array($info["book"], $info["chapter_start"], $info["section_start"], $info["first_word"], $info["chapter_end"], $info["section_end"], $info["last_word"], $INDEX_TO_CONTEXT_NAME[$info["type"]], "", "");
        array_push($rows, $sheet_row);
      }

      // Create new context
      $info["book"] = $book;
      $info["chapter_start"] = $chapter;
      $info["section_start"] = $section;
      $info["type"] = $context;

      $info["first_word"] = getStartToken($db, $row, $sectionText);
    }
    $priorContext = $context;
    $priorBook = $book;
    $priorChapter = $chapter;
    $priorSection = $section;
    $priorToken = $token;

    $i++;
    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "xls_context_$i", "Creating Context Excel File...", $nextPct, false);
      $nextPct++;
    }
  }
  // Add last one
  $sheet_row = array($info["book"], $info["chapter_start"], $info["section_start"], $info["first_word"], $info["chapter_end"], $info["section_end"], $info["last_word"], $INDEX_TO_CONTEXT_NAME[$info["type"]], "", "");
  array_push($rows, $sheet_row);
  //echo(" Done</br>");

  createXLS($rows, $filepath);
  unset($rows);
  return;
}

// Create a list of aliases
function createAliasXLS($db, $filepath) {
  global $SEND_FULL_MESSAGES;
  $rows = array();

  $header_row = array("Alias", "Lemma");
  array_push($rows, $header_row);

  //echo("Creating Alias Excel...");
  $total = getNextItem($db, getMatches($db, "SELECT count(*) FROM aliases WHERE deleted=0"))["count(*)"];
  $nextPct = 1;
  $multip = 100/$total;

  $matches = getMatches($db, "SELECT a.alias as alias, l.lemma as lemma FROM aliases AS a INNER JOIN lemmata AS l on a.lemmaid=l.lemmaid WHERE l.deleted=0 AND a.deleted=0 ORDER BY l.lemma ASC, a.alias ASC;");
  $i = 0;
  while($row = getNextItem($db, $matches)) {
    $sheet_row = array($row["alias"], $row["lemma"]);
    array_push($rows, $sheet_row);
    $i++;

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "xls_alias_$i", "Creating Alias Excel File...", $nextPct, false);
      $nextPct = floor($i*$multip)+1;
    }
  }
  //echo(" Done</br>");

  createXLS($rows, $filepath);
  unset($rows);
  return;
}

// Create a list of compounds
function createCompoundXLS($db, $filepath) {
  global $SEND_FULL_MESSAGES;
  $rows = array();

  $header_row = array("Compound", "Description");
  array_push($rows, $header_row);

  //echo("Creating Compound Excel...");
  $total = getNextItem($db, getMatches($db, "SELECT count(*) FROM compounds WHERE deleted=0"))["count(*)"];
  $nextPct = 1;
  $multip = 100/$total;

  $matches = getMatches($db, "SELECT * FROM compounds WHERE deleted=0 ORDER BY compound ASC;");
  $i = 0;
  while($row = getNextItem($db, $matches)) {
    $sheet_row = array($row["compound"], $row["description"]);
    array_push($rows, $sheet_row);
    $i++;

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "xls_compound_$i", "Creating Compound Excel File...", $nextPct, false);
      $nextPct = floor($i*$multip)+1;
    }
  }
  //echo(" Done</br>");

  createXLS($rows, $filepath);
  unset($rows);
  return;
}

// Create a list of roots
function createRootXLS($db, $filepath) {
  global $SEND_FULL_MESSAGES;
  $rows = array();

  $header_row = array("Root", "Description");
  array_push($rows, $header_row);

  //echo("Creating Root Excel...");
  $total = getNextItem($db, getMatches($db, "SELECT count(*) FROM roots WHERE deleted=0"))["count(*)"];
  $nextPct = 1;
  $multip = 100/$total;

  $matches = getMatches($db, "SELECT * FROM roots WHERE deleted=0 ORDER BY root ASC;");
  $i = 0;
  while($row = getNextItem($db, $matches)) {
    $sheet_row = array($row["root"], $row["description"]);
    array_push($rows, $sheet_row);
    $i++;

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "xls_roots_$i", "Creating Roots Excel File...", $nextPct, false);
      $nextPct = floor($i*$multip)+1;
    }
  }
  //echo(" Done</br>");

  createXLS($rows, $filepath);
}

// Create a list of semantic groups
function createSemanticXLS($db, $filepath) {
  global $SEND_FULL_MESSAGES;
  $rows = array();

  $header_row = array("Semantic Group", "Description", "Label Type");
  array_push($rows, $header_row);

  //echo("Creating Semantic Group Excel...");
  $total = getNextItem($db, getMatches($db, "SELECT count(*) FROM semantic_groups WHERE deleted=0"))["count(*)"];
  $nextPct = 1;
  $multip = 100/$total;

  $matches = getMatches($db, "SELECT * FROM semantic_groups WHERE deleted=0 ORDER BY group_name ASC;");
  $i = 0;
  while($row = getNextItem($db, $matches)) {
    $sheet_row = array($row["group_name"], $row["description"], $row["label_class"]);
    array_push($rows, $sheet_row);
    $i++;

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "xls_semantic_$i", "Creating Semantic Groups Excel File...", $nextPct, false);
      $nextPct = floor($i*$multip)+1;
    }
  }
  //echo(" Done</br>");

  createXLS($rows, $filepath);
  unset($rows);
  return;
}

// Create files for articles
function createArticleFiles($db, $base_dir) {
  global $SEND_FULL_MESSAGES;
  $sub_dir = "articles/";

  $storage_dir = $base_dir . $sub_dir;
  if(is_dir($storage_dir) == true) {
    deleteDir($storage_dir);
  }
  mkdir($storage_dir);

  //echo("Creating Articles...");
  $total = getNextItem($db, getMatches($db, "SELECT count(*) FROM lemmata WHERE deleted=0 AND long_def_id != 0;"))["count(*)"];
  $nextPct = 1;
  $multip = 100/$total;

  $matches = getMatches($db, "SELECT l.lemma as lem, d.long_def_raw as raw, d.old_long_def as old, d.authorid as aid, d.custom_author as custom_author FROM lemmata AS l INNER JOIN long_definitions AS d on l.long_def_id=d.id WHERE l.deleted=0;");
  $i = 0;
  $files = array();
  while($row = getNextItem($db, $matches)) {
    $header = array(
      "lemma" => $row["lem"],
      "old" => $row["old"],
      "authorid" => $row["aid"],
      "custom_author" => $row["custom_author"],
    );
    $headerText = "Header Information (don't change this):" . json_encode($header);
    $splitter = "~~~~~~~~~~~~~~~~~";
    $raw_article = $row["raw"];
    $article_text = $headerText . "\n" . $splitter . "\n" . $raw_article;

    // filesystems are annoying with capitals so this is a workaround
    $lower_lem = mb_strtolower($row["lem"]);
    if ($lower_lem != $row["lem"]) {
      $lower_lem .= "_cap";
    }
    $sub_save = $sub_dir . $lower_lem . ".txt";
    $filepath = $base_dir . $sub_save;
    // Save file and add it to list for zip
    file_put_contents($filepath, $article_text);
    array_push($files, array($filepath, $sub_save));
    $i++;

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "articles_$i", "Creating Articles...", $nextPct, false);
      $nextPct = floor($i*$multip)+1;
    }
  }
  //echo(" Done</br>");

  return $files;
}

// Create files for articles
function createIllustrationFiles($db) {
  global $SEND_FULL_MESSAGES;

  $ILLUSTRATION_PATH = "../assets/illustrations/";

  //echo("Creating Articles...");
  $total = getNextItem($db, getMatches($db, "SELECT count(*) FROM lemmata WHERE deleted=0 AND has_illustration != 0;"))["count(*)"];
  $nextPct = 1;
  $multip = 100/$total;

  $matches = getMatches($db, "SELECT * FROM lemmata WHERE deleted=0 AND has_illustration != 0;");
  $i = 0;
  $files = array();
  while($row = getNextItem($db, $matches)) {
    $name = $row["illustration_source"];
    $splt = explode(".", $name);
    $extension = $splt[sizeof($splt) - 1];
    $newname = $row["lemma"] . "." . $extension;
    array_push($files, array($ILLUSTRATION_PATH . $name, "illustrations/" . $newname));

    $i++;

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "illustrations_$i", "Getting Illustrations...", $nextPct, false);
      $nextPct = floor($i*$multip)+1;
    }
  }
  //echo(" Done</br>");

  return $files;
}

// Given a phpword section, print the long def in a slightly pretty format
function printLongDef($section, $long_def) {
  $def = json_decode($long_def, true)[0];
  printLongDefRecursive($section, $def, 0);
}

// Function for recursive long definition printing
function printLongDefRecursive($section, $def, $depth) {
  $TAB = "  ";

  $indent = "";
  for ($i = 0; $i < $depth; $i++) {
    $indent .= $TAB;
  }

  // Create text
  $text = $indent;
  $idsplt = explode(".", $def["text"]["identifier"]);
  $identifier = $idsplt[sizeof($idsplt)-1];
  if ($identifier != "") {
    $text .= $identifier . ". ";
  } else {
    $text .= "";
  }
  $text .= $def["text"]["start"];
  $refs = $def["text"]["refList"];
  $kps = $def["text"]["keyPassageList"];

  for ($i = 0; $i < sizeof($refs); $i++) {
    $ref = $refs[$i];
    $text .= $ref["ref"] . $ref["note"];
  }
  // TODO: maybe make this a list instead of just indenting?
  $section->addText(htmlspecialchars($text), "standard");

  // Add key passages
  $subIndent = $indent . $TAB;
  if (sizeof($kps) > 0) {
    $kptext = "Key Passage:";
    if (sizeof($kps) > 1) {
      $kptext = "Key Passages:";
    }
    $section->addText(htmlspecialchars($subIndent . $kptext), "bold");
  }

  for ($i = 0; $i < sizeof($kps); $i++) {
    $passage = $kps[$i];
    $textrun = $section->addTextRun();
    $textrun->addText(htmlspecialchars($subIndent . " "), "standard");
    $textrun->addText(htmlspecialchars($passage["ref"]), "italic");
    $textrun->addText(htmlspecialchars(": " . $passage["greek"] . " \"" . $passage["english"] . "\""), "standard");
  }

  // Add children
  for ($i = 0; $i < sizeof($def["subList"]); $i++) {
    $sub = $def["subList"][$i];
    printLongDefRecursive($section, $sub, $depth+1);
  }
}

// Create a printable version of the dictionary
// TODO: you'll probably have to make changes and tweaks to get the formatting
// Correct, but here's an example of generally how this would work
function createPrintableDictionary($db, $filepath) {
  global $AUTHOR_NAME;
  global $SEND_FULL_MESSAGES;

  $lemmata_and_aliases = array();
  $total = 0;
  $matches = getMatches($db, "SELECT * FROM search_lemmata GROUP BY lemmaid, aliasid ORDER BY search_text;");
  while($row = getNextItem($db, $matches)) {
    if ($row["lemmaid"] != null) {
      $obj = array(
        "id" => $row["lemmaid"],
        "isLemma" => true
      );
    } else {
      $obj = array(
        "id" => $row["aliasid"],
        "isLemma" => false
      );
    }
    array_push($lemmata_and_aliases, $obj);

    $total++;
  }

  $wordDoc = new PhpWord();
  $wordDoc->addFontStyle("standard", array('size' => 12));
  $wordDoc->addFontStyle("lemma", array('bold' => true, 'size' => 14));
  $wordDoc->addFontStyle("bold", array('bold' => true, 'size' => 12));
  $wordDoc->addFontStyle("italic", array('italic' => true, 'size' => 12));
  $wordDoc->addTitleStyle(1, array('bold' => true, 'size' => 24), array('spaceAfter' => 240));

  $section = $wordDoc->addSection();
  $section->addTitle($AUTHOR_NAME . " Lexicon", 1);

  $nextPct = 1;
  $multip = 100/$total;

  for ($i = 0; $i < sizeof($lemmata_and_aliases); $i++) {
    $item = $lemmata_and_aliases[$i];

    if ($item["isLemma"]) {
      $lem = getMatchesList($db, "lemmata_id", $item["id"])[0];

      if ($lem["deleted"] != 0) {
        continue;
      }

      $section->addText(htmlspecialchars($lem["lemma"]), "lemma");

      if ($lem["long_def_id"] != 0) {
        $defs = getMatchesList($db, "lemmata_long_def", $lem["long_def_id"]);
        if (sizeof($defs) == 0) {
            echo("BAD LONG DEF: " . $lem["lemma"] . ", " . $lem["long_def_id"] . "\n");
        }
        $def = $defs[0];
        printLongDef($section, $def["long_def"]);
      } else {
        $section->addText(htmlspecialchars($lem["short_def"]), "standard");
      }

      $section->addText("", "standard");
    } else {
      $alias = getMatchesList($db, "aliases_by_id_with_lemma", $item["id"])[0];
      if ($alias["deleted"] != 0) {
        continue;
      }
      // print($alias["alias"] . "\n");
      $section->addText($alias["alias"], "lemma");
      $section->addText("See " . $alias["lemma"] . "\n", "standard");
      $section->addText("", "standard");
    }

    if ($i*$multip > $nextPct) {
      send_message($SEND_FULL_MESSAGES, "printable_$i", "Creating Word Document...", $nextPct, false);
      $nextPct = floor($i*$multip)+1;
    }
  }

  $wordDoc->save($filepath, "Word2007");
}

function generateAllFiles() {
  global $AUTHOR_NAME;
  global $LEXICON_DB_NAME;
  global $DIR_TMP;
  global $SEND_FULL_MESSAGES;

  $ZIP_NAME = "lexicon_export.zip";

  // Create folder for storing info
  if(is_dir($DIR_TMP) == true) {
    deleteDir($DIR_TMP);
  }
  mkdir($DIR_TMP);
  unlink($ZIP_NAME);

  $zip_files = array();
  $db = get_db($dbname=$LEXICON_DB_NAME);

  // XML file
  $xml = getXML($db);

  $filename = "text.xml";
  $filepath = $DIR_TMP . $filename;
  file_put_contents($filepath, $xml);
  unset($xml);
  array_push($zip_files, array($filepath, $filename));
  $textfile = $filename;

  // Thucydides DB
  $filename = "$AUTHOR_NAME.db";
  $filepath = $DIR_TMP . $filename;
  createLiteDB($db, $filepath, $textfile);
  array_push($zip_files, array($filepath, $filename));

  // Dictionary Excel
  $filename = "lemmata.xlsx";
  $filepath = $DIR_TMP . $filename;
  createLemmaXLS($db, $filepath);
  array_push($zip_files, array($filepath, $filename));

  // Contexts
  $filename = "contexts.xlsx";
  $filepath = $DIR_TMP . $filename;
  createContextXLS($db, $filepath);
  array_push($zip_files, array($filepath, $filename));

  // Aliases
  $filename = "aliases.xlsx";
  $filepath = $DIR_TMP . $filename;
  createAliasXLS($db, $filepath);
  array_push($zip_files, array($filepath, $filename));

  // Compounds
  $filename = "compounds.xlsx";
  $filepath = $DIR_TMP . $filename;
  createCompoundXLS($db, $filepath);
  array_push($zip_files, array($filepath, $filename));

  // Roots
  $filename = "roots.xlsx";
  $filepath = $DIR_TMP . $filename;
  createRootXLS($db, $filepath);
  array_push($zip_files, array($filepath, $filename));

  // Semantic Groups
  $filename = "semanticGroups.xlsx";
  $filepath = $DIR_TMP . $filename;
  createSemanticXLS($db, $filepath);
  array_push($zip_files, array($filepath, $filename));

  // Articles
  $article_files = createArticleFiles($db, $DIR_TMP);
  $zip_files = array_merge($zip_files, $article_files);

  // Illustrations
  $illustration_files = createIllustrationFiles($db);
  $zip_files = array_merge($zip_files, $illustration_files);

  // Word output
  $filename = $AUTHOR_NAME . ".docx";
  $filepath = $DIR_TMP . $filename;
  createPrintableDictionary($db, $filepath);
  array_push($zip_files, array($filepath, $filename));


  // Close database
  $db->close();

  send_message($SEND_FULL_MESSAGES, "zipping", "Zipping Files...", 90, false);
  // Zip files (from https://stackoverflow.com/a/1754359)
  $zip = new ZipArchive;
  $zip->open($ZIP_NAME, ZipArchive::CREATE);
  foreach ($zip_files as $a) {
    $file_location = $a[0];
    $file_destination = $a[1];
    $zip->addFile($file_location, $file_destination);
  }
  $zip->close();

  send_message($SEND_FULL_MESSAGES, "CLOSE", "Process complete", 100, true);
}

// =============================================================================
// =============================================================================
// =============================================================================

// Create directory for export info
$DIR_TMP = "lexicon_export/";
$SEND_FULL_MESSAGES = true;

// True only if this is the main file
if (get_included_files()[0] == __FILE__) {
  //This script can take a while, so give it time.
  set_time_limit(2400);

  // We also need lots of space
  // It feels like we shouldn't hit the normal limit unless there were issues
  // with the garbage collection, but I haven't managed to figure it out.
  ini_set('memory_limit','400M');

  write_headers();

  // Ignore HTTP request with method OPTIONS
  if (options_request()) { return; }

  if (!hasAccess(3)) {
    echo("You do not have appropriate access to do this.");
    return;
  }

  header('Content-Type: text/event-stream');
  // recommended to prevent caching of event data.
  header('Cache-Control: no-cache');

  // generate files
  generateAllFiles();
}
?>
