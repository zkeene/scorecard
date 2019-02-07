<?php
define('SERVER_NAME', 'localhost');
define('USERNAME', 'zac');
define('PASSWORD', 'picard47');
define ('DATABASE', 'kpnquality');

$conn = new mysqli(SERVER_NAME, USERNAME, PASSWORD, DATABASE);

if ($conn->connect_errno) {
	echo 'Connection error: ';
	echo $conn->connect_error;
	exit;
}
?>