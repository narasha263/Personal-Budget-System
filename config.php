<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "budget";
$port = "3307";
$conn = mysqli_connect($servername, $username, $password, $database, $port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
