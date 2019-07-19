<?php
require_once __DIR__ . "/master.php";

// object for storing the thucydides lexicon
class liteDB extends SQLite3 {
  function __construct($name) {
   $this->open($name);
  }
}

// if we aren't in production, allow communication between different localhosts.
function write_headers() {
  global $IN_PRODUCTION;
  if (!$IN_PRODUCTION) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
  }
}

// True if the request is OPTIONS
function options_request() {
  global $_SERVER;
  return $_SERVER['REQUEST_METHOD'] === "OPTIONS";
}

// extract the query information passed to this file
function get_data() {
  // from https://stackoverflow.com/questions/9597052/how-to-retrieve-request-payload
  $request_body = file_get_contents('php://input');
  $data = get_object_vars(json_decode($request_body));
  return $data;
}

// get the sqlite db
function get_lite_db($dbName) {
  $lite = new liteDB($dbName);
  if(!$lite) {
    echo $lite->lastErrorMsg();
    echo "Failed to open sqlite DB";
    return;
  }
  return $lite;
}

// get the mysql db
function get_mysql_db($suffix="", $dbname="lexeis") {
  global $IN_PRODUCTION;
  global $db_data;

  $db_hostname = $db_data[$dbname]["hostname"];
  $db_database =  $db_data[$dbname]["database"];
  $db_username = $db_data[$dbname]["username"] . $suffix;
  $db_password =  $db_data[$dbname]["password"];
  $db_port = $db_data[$dbname]["port"];

  if($IN_PRODUCTION) {
      $mysqli = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);
  } else {
      $mysqli = mysqli_connect($db_hostname, $db_username, $db_password, $db_database, $db_port);
  }
  if ($mysqli->connect_errno) {
      echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
      return;
  }

  # THIS LINE IS ESSENTIAL FOR GETTING THE ENCODING TO BE CORRECT!!!
  $encoding_query = "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_general_ci';";
  $mysqli->query($encoding_query);

  return $mysqli;
}

// get the database object
function get_db($dbname="lexeis") {
  global $USE_MYSQL;
  if ($USE_MYSQL) {
    return get_mysql_db("", $dbname);
  } else {
    return get_lite_db($dbname);
  }
}


// given a row, captialize the token if it is the first token in a section.
function getProperlyCapitalizedToken($row) {
  $t = $row['token'];
  // capitalize first token of each section
  if (intval($row['word_index']) == 1) {
    $t = mb_convert_case($t, MB_CASE_TITLE, 'UTF-8');
  }
  return $t;
}

// escape using the given database
function escapeString($db, $str) {
  global $USE_MYSQL;
  if ($USE_MYSQL) {
    return $db->real_escape_string($str);
  } else {
    // this fails for strings with \0 in them; but I don't think that will happen here.
    // And if it does, only for the initial database setup, where you can handle
    // it pretty easily.
    return $db->escapeString($str);
  }
}

// run a query
function runQuery($db, $query) {
  global $USE_MYSQL;
  return $db->query($query);
}

// get matches for a given database
function getMatches($db, $query) {
  global $USE_MYSQL;
  $m = $db->query($query);
  // echo("Error: (" . $db->errno . ") " . $db->error);
  if ($USE_MYSQL) {
    $m->data_seek(0);
  }
  return $m;
}

// get the next item for query
function getNextItem($db, $matches) {
  global $USE_MYSQL;
  if ($USE_MYSQL) {
    return $matches->fetch_assoc();
  } else {
    return $matches->fetchArray(SQLITE3_ASSOC);
  }
}
?>
