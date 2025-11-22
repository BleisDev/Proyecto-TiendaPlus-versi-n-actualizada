<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$baseDatos = "TiendaPlus";

// Crear la conexión con nombre $conn
$conn = new mysqli($host, $usuario, $contrasena, $baseDatos);

if ($conn->connect_error) {
    die("❌ Error de conexión a la base de datos: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
