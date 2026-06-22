<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "mrmukpe4_creativetheka_newest";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Ensure emojis / unicode are stored correctly */
$conn->set_charset("utf8mb4");
