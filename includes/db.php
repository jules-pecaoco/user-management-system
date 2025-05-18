<?php
require_once 'config.php';



function connect_db() {
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  return $conn;
}

// Get database connection
function get_db() {
  static $conn;

  if (!isset($conn)) {
    $conn = connect_db();
  }

  return $conn;
}
