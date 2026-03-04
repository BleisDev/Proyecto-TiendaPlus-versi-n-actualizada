<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'backend/conexion.php';
$stmt=$conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (1, 1, 1)");
if(!$stmt) {
    die("Prepare failed: " . $conn->error);
}
if(!$stmt->execute()) {
    echo "Execute failed: " . $stmt->error;
} else {
    echo "Success";
}
