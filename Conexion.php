<?php
$server = "localhost";
$user = "root";
$pass = "";
$db = "pokedex";

$conexion = new mysqli($server, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
    
}


?>