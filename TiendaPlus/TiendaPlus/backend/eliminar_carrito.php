<?php
session_start();

if (!isset($_GET['id'])) {
    header("Location: ../web/carrito.php");
    exit;
}

$id = intval($_GET['id']);

// Eliminar del carrito en sesión
if (isset($_SESSION['carrito'][$id])) {
    unset($_SESSION['carrito'][$id]);
}

// Si además guardas el carrito en la base de datos, limpia también:
if (isset($_SESSION['usuario_id'])) {
    include_once('conexion.php');
    $usuario_id = $_SESSION['usuario_id'];
    $conn->query("DELETE FROM carrito WHERE usuario_id = $usuario_id AND id_producto = $id");
}

header("Location: ../web/carrito.php");
exit;
?>
