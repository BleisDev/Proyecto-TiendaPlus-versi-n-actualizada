<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../web/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM resenas WHERE id = ? LIMIT 1");
    $stmt->bind_param("i",$id);
    $stmt->execute();
}
header("Location: ../admin/resenas.php");
exit;
