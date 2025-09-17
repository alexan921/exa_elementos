<?php
$host = "localhost";
$dbname = "mielementos";
$username = "root";
$password = "";

try {
    $PDO = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>
