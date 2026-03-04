<?php
require_once 'conexion.php';

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    echo json_encode($stmt->fetch());
} else {
    $stmt = $pdo->query("SELECT * FROM productos");
    echo json_encode($stmt->fetchAll());
}
?>
