<?php
require_once "../../api/database_utils.php";
require_once "lexiconUtils.php";
require_once "recompilePrepTexts.php";
// $matches = getMatches($db, $query);
// $row = $matches->fetchArray(SQLITE3_ASSOC);

// $matches = $mysqli->query("SELECT id,longit,lat FROM map_nodes ORDER BY id ASC");
// $matches->data_seek(0);
// $row = $res->fetch_assoc();

//This script can take a while, so give it time.
set_time_limit(1200);

// used to count through the various dummy users to avoid request limits
$userCount = 1;

// Convert from sqlite to mysql
if ($IN_PRODUCTION) {
  $mysqli = get_mysql_db("", $dbname=$LEXICON_DB_NAME);//$userCount
} else {
  $mysqli = get_mysql_db("", $dbname=$LEXICON_DB_NAME);
}

$USE_MYSQL = false;
$db = get_lite_db("lexicon_database.db");

// handle overflow of requests on the server
$queryIndex = 0;
function addQuery($query) {
  global $LEXICON_DB_NAME;
  global $queryIndex;
  global $userCount;
  global $mysqli;
  global $IN_PRODUCTION;
  if ($IN_PRODUCTION && $queryIndex % 74900 == 0) {
    $userCount++;
    //echo("<br/>-  Moving to Pericles " . $userCount . ", <br/>");
    $mysqli = get_mysql_db("", $dbname=$LEXICON_DB_NAME);//$userCount
  }
  if (!$mysqli->query($query)) {
      echo "Data Insertion failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }
  $queryIndex++;
}


// Given a definition object with no key passages, extract the associated raw definition
function extractDefinition($ld) {
  $result = "";
  if ($ld["text"]["identifier"] != "") {
    // Getting last from https://stackoverflow.com/a/35957563
    $ident = array_values(array_slice(explode(".", $ld["text"]["identifier"]), -1))[0];
    $result .= $ident . ". ";
  }
  $result .= $ld["text"]["start"];
  foreach ($ld["text"]["refList"] as $kp) {
    $result .= " " . $kp["ref"] . $kp["note"];
  }

  foreach ($ld["subList"] as $sl) {
    $result .= " " . extractDefinition($sl);
  }

  return $result;
}

// extract a raw definiton from a betant/old finished definition
function extractRawFromComplete($longDefString) {
  $longDefString = str_replace("\\\"", "\"", $longDefString);
  $longDefString = str_replace("\\\\", "\\", $longDefString);
  $ld = json_decode($longDefString, $assoc=true)[0];

  return extractDefinition($ld);
}

// Create change log
// Native mysql timestamp ends at 2038 which is not far enough away to warrant use
if (!$mysqli->query("DROP TABLE IF EXISTS change_log") ||
    !$mysqli->query("CREATE TABLE change_log (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            userid INT UNSIGNED NOT NULL,
            tstamp VARCHAR(30) NOT NULL,
            change_type INT UNSIGNED NOT NULL,
            context VARCHAR(128) NOT NULL,
            before_value TEXT(1000),
            after_value TEXT(1000),
            is_undo TINYINT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            INDEX (userid),
            INDEX (change_type)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

// Create issues reprot list
if (!$mysqli->query("DROP TABLE IF EXISTS issue_reports") ||
    !$mysqli->query("CREATE TABLE issue_reports (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            userid INT UNSIGNED NOT NULL,
            useremail VARCHAR(100) NOT NULL,
            tstamp VARCHAR(30) NOT NULL,
            location TEXT(1000),
            comment TEXT(1000),
            resolved TINYINT UNSIGNED NOT NULL,
            resolved_user INT UNSIGNED NOT NULL,
            resolved_tstamp VARCHAR(30) NOT NULL,
            resolved_comment TEXT(1000),
            PRIMARY KEY (id),
            INDEX (userid)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}


// Status is how "reviewed" the lemmata is;
// 0 means no review at all,
// 1 means okay draft,
// 2 means well proofread.
// has_long_def TINYINT UNSIGNED NOT NULL,
if (!$mysqli->query("DROP TABLE IF EXISTS lemmata") ||
    !$mysqli->query("CREATE TABLE lemmata (
            lemmaid INT UNSIGNED NOT NULL AUTO_INCREMENT,
            lemma VARCHAR(128) NOT NULL,
            short_def TEXT(1000) NOT NULL,
            long_def_id INT UNSIGNED NOT NULL,
            part_of_speech VARCHAR(64) NOT NULL,
            semantic_group TEXT(100) NOT NULL,
            root TEXT(100) NOT NULL,
            compounds TEXT(200) NOT NULL,
            frequency_all INT UNSIGNED NOT NULL,
            has_illustration TINYINT UNSIGNED NOT NULL,
            illustration_source TEXT(1000) NOT NULL,
            illustration_alt TEXT(1000) NOT NULL,
            illustration_caption TEXT(1000) NOT NULL,
            bibliography_text TEXT(1000) NOT NULL,
            assigned INT UNSIGNED NOT NULL,
            status TINYINT UNSIGNED NOT NULL,
            deleted TINYINT UNSIGNED NOT NULL,
            PRIMARY KEY (lemmaid),
            INDEX (lemma(10))
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}


// Status is 0 for needs editor review, 1 for edited, 2 for rejected, 3 for accepted
// There may be multiple accepted articles for a single lemma; the one
// references by the lemma is the on used.
if (!$mysqli->query("DROP TABLE IF EXISTS long_definitions") ||
    !$mysqli->query("CREATE TABLE long_definitions (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            long_def_raw TEXT(10000) NOT NULL,
            long_def TEXT(100000) NOT NULL,
            old_long_def TINYINT UNSIGNED NOT NULL,
            authorid INT UNSIGNED NOT NULL,
            custom_author VARCHAR(256) NOT NULL,
            lemmaid INT UNSIGNED NOT NULL,
            status TINYINT UNSIGNED NOT NULL,
            later_draft_id INT UNSIGNED NOT NULL,
            PRIMARY KEY (id)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

if (!$mysqli->query("DROP TABLE IF EXISTS search_lemmata") ||
    !$mysqli->query("CREATE TABLE search_lemmata (
            searchid INT UNSIGNED NOT NULL AUTO_INCREMENT,
            lemmaid INT UNSIGNED,
            aliasid INT UNSIGNED,
            search_text VARCHAR(64) NOT NULL,
            PRIMARY KEY (searchid),
            INDEX (search_text(8))
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

// Sort tokens by lower case, ignoring accents
$db->createFunction("unicode_lower", function($data) {
  return unaccentedKeepNumbers(mb_strtolower($data));
});
$query ="SELECT * FROM lemmata ORDER BY unicode_lower(lemma) ASC;";
$matches = getMatches($db, $query);
$index = 0;
$longDefIndex = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $lemma = $mysqli->real_escape_string($row["lemma"]);
  $short_def = $mysqli->real_escape_string($row["short_def"]);
  $long_def_raw = $mysqli->real_escape_string($row["long_def_raw"]);
  $old_long_def = intval($row["old_long_def"]);
  $long_def = $mysqli->real_escape_string($row["long_def"]);
  $part_of_speech = $mysqli->real_escape_string($row["part_of_speech"]);

  $sgs = json_decode($row["semantic_group"]);
  sort($sgs);
  $semantic_group = $mysqli->real_escape_string(json_encode($sgs));

  $rs = json_decode($row["root"]);
  sort($rs);
  $root = $mysqli->real_escape_string(json_encode($rs));

  $cs = json_decode($row["compounds"]);
  sort($cs);
  $compounds = $mysqli->real_escape_string(json_encode($cs));


  $frequency_all = $mysqli->real_escape_string($row["frequency_all"]);
  $has_illustration = $mysqli->real_escape_string($row["has_illustration"]);
  $illustration_source = $mysqli->real_escape_string($row["illustration_source"]);
  $illustration_alt = $mysqli->real_escape_string($row["illustration_alt"]);
  $illustration_caption = $mysqli->real_escape_string($row["illustration_caption"]);
  $bibliography_text = $mysqli->real_escape_string($row["bibliography_text"]);


  $lemma_long_def = 0;
  $lemma_status = 0;
  if ($row["has_long_def"] == 1) { // $has_long_def = $mysqli->real_escape_string($row["has_long_def"]);
    $longDefIndex++;
    $lemma_long_def = $longDefIndex;

    // If this is not an old long definition, it is at least a draft
    if ($old_long_def != 1) {
      $lemma_status = 1;
    }

    $authorid = intval($row["author_id"]);
    $custom_author = $mysqli->real_escape_string($row["custom_author"]);
    $lemmaid = $index;
    $qp1 = "long_def_raw,long_def,old_long_def,authorid,custom_author,lemmaid,status,later_draft_id";
    $qp2 = "'$long_def_raw','$long_def',$old_long_def,$authorid,'$custom_author',$lemmaid,3,0"; // status is 3 because it is accepted by default
    $query = "INSERT INTO long_definitions(" . $qp1 . ") VALUES (" . $qp2 . ");";
    if (!$mysqli->query($query)) {
        echo "Data Insertion failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
  }


  // Add the lemma
  $qp1 = "lemma,short_def,long_def_id,part_of_speech,semantic_group,root,compounds,frequency_all,has_illustration,illustration_source,illustration_alt,illustration_caption,bibliography_text,assigned,status,deleted";
  $qp2 = "'$lemma','$short_def'," . $lemma_long_def . ",'$part_of_speech','$semantic_group','$root','$compounds',$frequency_all,$has_illustration,'$illustration_source','$illustration_alt','$illustration_caption','$bibliography_text',0,$lemma_status,0";
  $query = "INSERT INTO lemmata(" . $qp1 . ") VALUES (" . $qp2 . ");";
  if (!$mysqli->query($query)) {
      echo "Data Insertion failed: (" . $mysqli->errno . ") " . $mysqli->error;
  }

  // Add search lemmata
  addSearchLemmata($mysqli, $row["lemma"], $index, false);
}
echo("lemmata: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


// get an associative array for fast searching
$lemmaToID = array();
$query ="SELECT * FROM lemmata;";
$matches = $mysqli->query($query);
$matches->data_seek(0);
while($row = $matches->fetch_assoc()) {
  $lemmaToID[$row["lemma"]] = $row["lemmaid"];
}


// end of user 1;

// Table for aliases , e.g. πλείων -> πολύς
if (!$mysqli->query("DROP TABLE IF EXISTS aliases") ||
    !$mysqli->query("CREATE TABLE aliases (
            aliasid INT UNSIGNED NOT NULL AUTO_INCREMENT,
            alias VARCHAR(128) NOT NULL,
            lemmaid INT UNSIGNED,
            deleted TINYINT UNSIGNED NOT NULL,
            PRIMARY KEY (aliasid),
            INDEX (alias(8)),
            INDEX (lemmaid)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$query ="SELECT * FROM aliases ORDER BY lemma, alias;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;
  $alias = $mysqli->real_escape_string($row["alias"]);
  $lemmaid = $lemmaToID[$row["lemma"]];

  $query = "INSERT INTO aliases(alias, lemmaid, deleted) VALUES ('$alias',$lemmaid,0);";
  addQuery($query);

  // Add search lemmata
  $aliasid = $index;
  addSearchLemmata($mysqli, $row["alias"], $aliasid, true);
}
echo("aliases: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


$aliasToID = array();
$query ="SELECT * FROM aliases;";
$matches = $mysqli->query($query);
$matches->data_seek(0);
while($row = $matches->fetch_assoc()) {
  $aliasToID[$row["alias"]] = $row["aliasid"];
}


if (!$mysqli->query("DROP TABLE IF EXISTS compounds") ||
    !$mysqli->query("CREATE TABLE compounds (
            compound_index INT UNSIGNED NOT NULL AUTO_INCREMENT,
            compound VARCHAR(64) NOT NULL,
            description TEXT(1000) NOT NULL,
            lemma_in_dict TINYINT UNSIGNED NOT NULL,
            deleted TINYINT UNSIGNED NOT NULL,
            PRIMARY KEY (compound_index)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$compoundToIndex = array();

$query ="SELECT * FROM compounds ORDER BY compound;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $compound_index = $mysqli->real_escape_string($row["compound_index"]);
  $compound = $mysqli->real_escape_string($row["compound"]);

  // store index associated with the compound
  $compoundToIndex[$row["compound"]] = $index;

  $description = $mysqli->real_escape_string($row["description"]);
  $lemma_in_dict = $mysqli->real_escape_string($row["lemma_in_dict"]);

  $qp1 = "compound,description,lemma_in_dict,deleted";
  $qp2 = "'$compound','$description',$lemma_in_dict,0";
  $query = "INSERT INTO compounds(" . $qp1 . ") VALUES (" . $qp2 . ");";
  addQuery($query);
}
echo("compounds: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


if (!$mysqli->query("DROP TABLE IF EXISTS compound_lemma_link") ||
    !$mysqli->query("CREATE TABLE compound_lemma_link (
            cl_link_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            compound_index INT UNSIGNED NOT NULL,
            lemmaid INT UNSIGNED NOT NULL,
            PRIMARY KEY (cl_link_id),
            INDEX (compound_index)
          ) ENGINE = INNODB"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$query ="SELECT * FROM compound_lemma_link ORDER BY unicode_lower(lemma), compound;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $compound_index = $compoundToIndex[$mysqli->real_escape_string($row["compound"])];
  $lemma_id = $lemmaToID[$row["lemma"]];

  $qp1 = "compound_index,lemmaid";
  $qp2 = "'$compound_index',$lemma_id";
  $query = "INSERT INTO compound_lemma_link(" . $qp1 . ") VALUES (" . $qp2 . ");";
  addQuery($query);
}
echo("compound_lemma_link: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


if (!$mysqli->query("DROP TABLE IF EXISTS roots") ||
    !$mysqli->query("CREATE TABLE roots (
            root_index INT UNSIGNED NOT NULL AUTO_INCREMENT,
            root VARCHAR(64) NOT NULL,
            description TEXT(1000) NOT NULL,
            lemma_in_dict TINYINT UNSIGNED NOT NULL,
            deleted TINYINT UNSIGNED NOT NULL,
            PRIMARY KEY (root_index)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$stemToIndex = array();

$query ="SELECT * FROM roots ORDER BY root;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $root_index = $mysqli->real_escape_string($row["root_index"]);
  $stem = $mysqli->real_escape_string($row["root"]);

  // store index associated with the compound
  $stemToIndex[$row["root"]] = $index;

  $description = $mysqli->real_escape_string($row["description"]);
  $lemma_in_dict = $mysqli->real_escape_string($row["lemma_in_dict"]);

  $qp1 = "root,description,lemma_in_dict,deleted";
  $qp2 = "'$stem','$description',$lemma_in_dict,0";
  $query = "INSERT INTO roots(" . $qp1 . ") VALUES (" . $qp2 . ");";
  addQuery($query);
}
echo("roots: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


if (!$mysqli->query("DROP TABLE IF EXISTS root_lemma_link") ||
    !$mysqli->query("CREATE TABLE root_lemma_link (
            rl_link_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            root_index INT UNSIGNED NOT NULL,
            lemmaid INT UNSIGNED NOT NULL,
            PRIMARY KEY (rl_link_id),
            INDEX (root_index)
          ) ENGINE = INNODB"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$query ="SELECT * FROM root_lemma_link ORDER BY unicode_lower(lemma), stem;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $root_index = $stemToIndex[$mysqli->real_escape_string($row["stem"])];
  $lemma_id = $lemmaToID[$row["lemma"]];

  $qp1 = "root_index,lemmaid";
  $qp2 = "'$root_index',$lemma_id";
  $query = "INSERT INTO root_lemma_link(" . $qp1 . ") VALUES (" . $qp2 . ");";

  addQuery($query);
}
echo("root_lemma_link: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


if (!$mysqli->query("DROP TABLE IF EXISTS semantic_groups") ||
    !$mysqli->query("CREATE TABLE semantic_groups (
            group_index INT UNSIGNED NOT NULL AUTO_INCREMENT,
            group_name VARCHAR(128) NOT NULL,
            label_class VARCHAR(128) NOT NULL,
            description TEXT(1000) NOT NULL,
            deleted TINYINT UNSIGNED NOT NULL,
            PRIMARY KEY (group_index)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$query ="SELECT * FROM semantic_groups ORDER BY group_name;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $group_index = $mysqli->real_escape_string($row["group_index"]);
  $group_name = $mysqli->real_escape_string($row["group_name"]);
  $label_class = intval($row["label_class"]);
  $description = $mysqli->real_escape_string($row["description"]);

  $qp1 = "group_name,label_class,description,deleted";
  $qp2 = "'$group_name','$label_class','$description',0";
  $query = "INSERT INTO semantic_groups(" . $qp1 . ") VALUES (" . $qp2 . ");";
  addQuery($query);
}
echo("semantic_groups: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


if (!$mysqli->query("DROP TABLE IF EXISTS semantic_lemma_link") ||
    !$mysqli->query("CREATE TABLE semantic_lemma_link (
            sg_link_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            semantic_group INT UNSIGNED NOT NULL,
            lemmaid INT UNSIGNED NOT NULL,
            PRIMARY KEY (sg_link_id),
            INDEX (semantic_group)
          ) ENGINE = INNODB"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$query ="SELECT * FROM semantic_lemma_link ORDER BY unicode_lower(lemma), semantic_group;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  // Database indexes from 0, we index from 1
  $sg = $row["semantic_group"] + 1;
  $lemma_id = $lemmaToID[$row["lemma"]];

  $qp1 = "semantic_group,lemmaid";
  $qp2 = "'$sg',$lemma_id";
  $query = "INSERT INTO semantic_lemma_link(" . $qp1 . ") VALUES (" . $qp2 . ");";

  addQuery($query);
}
echo("semantic_lemma_link: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


if (!$mysqli->query("DROP TABLE IF EXISTS text_storage") ||
    !$mysqli->query("CREATE TABLE text_storage (
            sequence_index INT UNSIGNED NOT NULL,
            token_index BIGINT NOT NULL,
            token VARCHAR(128) NOT NULL,
            book SMALLINT UNSIGNED NOT NULL,
            chapter SMALLINT UNSIGNED NOT NULL,
            section SMALLINT UNSIGNED NOT NULL,
            word_index INT UNSIGNED NOT NULL,
            true_word_index INT UNSIGNED NOT NULL,
            PRIMARY KEY (sequence_index),
            INDEX (token_index),
            INDEX (book),
            INDEX (chapter),
            INDEX (section)
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}

$query ="SELECT * FROM text_storage;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $sequence_index = $mysqli->real_escape_string($row["sequence_index"]);
  $token_index = $mysqli->real_escape_string($row["token_index"]);
  $token = $mysqli->real_escape_string($row["token"]);
  $book = $mysqli->real_escape_string($row["book"]);
  $chapter = $mysqli->real_escape_string($row["chapter"]);
  $section = $mysqli->real_escape_string($row["section"]);
  $word_index = $mysqli->real_escape_string($row["word_index"]);
  $true_word_index = $mysqli->real_escape_string($row["true_word_index"]);


  $qp1 = "sequence_index,token_index,token,book,chapter,section,word_index,true_word_index";
  $qp2 = "$sequence_index,$token_index,'$token',$book,$chapter,$section,$word_index,$true_word_index";
  $query = "INSERT INTO text_storage(" . $qp1 . ") VALUES (" . $qp2 . ");";
  addQuery($query);
}
echo("text_storage: " . $index . " entries.<br/>");
if (ob_get_length()){ ob_flush(); }
flush();


if (!$mysqli->query("DROP TABLE IF EXISTS instance_information") ||
    !$mysqli->query("CREATE TABLE instance_information (
            token_index BIGINT NOT NULL,
            lemma VARCHAR(64) NOT NULL,
            lemma_meaning VARCHAR(128) NOT NULL,
            context_type INT UNSIGNED NOT NULL,
            PRIMARY KEY (token_index),
            INDEX (lemma(10))
          ) ENGINE = INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"))
{
    echo("Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error);
}


$query ="SELECT * FROM instance_information;";
$matches = getMatches($db, $query);
$index = 0;
while($row = $matches->fetchArray(SQLITE3_ASSOC)) {
  $index++;

  $token_index = $mysqli->real_escape_string($row["token_index"]);
  $lemma = $mysqli->real_escape_string($row["lemma"]);
  $lemma_meaning = $mysqli->real_escape_string($row["lemma_meaning"]);
  $context_type = $mysqli->real_escape_string($row["context_type"]);


  $qp1 = "token_index,lemma,lemma_meaning,context_type";
  $qp2 = "$token_index,'$lemma','$lemma_meaning',$context_type";
  $query = "INSERT INTO instance_information(" . $qp1 . ") VALUES (" . $qp2 . ");";
  addQuery($query);
}
echo("instance_information: " . $index . " entries.<br/>");

echo("Updating valid 2-letter combos...<br/>");
$USE_MYSQL = true;
// Create the file of possible starting alphabetic combos
updateAlphaCombos($mysqli);

// Compile the preprocessed texts
echo("Compiling Prep Texts...<br/>Percent Done: ");
compile_texts($mysqli, false);

$db->close();
$mysqli->close();

// Create a backup
echo("<br/><br/>Creating initial backup...");
createNewBackup();

echo("<br/>Done");
?>
