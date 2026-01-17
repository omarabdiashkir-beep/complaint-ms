<?php
$host = 'localhost';
$user = 'root';
$pass = '123';
$db_name = 'complaint_system';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>