<?php
$servername = "127.0.0.1:3307";  // Changed from "db" to "127.0.0.1:3307"
$username = "root";  
$password = ""; 
$dbname = "edoc"; 

$database = new mysqli($servername, $username, $password, $dbname);

if ($database->connect_error) {
    die("Échec de la connexion : " . $database->connect_error);
}
?>