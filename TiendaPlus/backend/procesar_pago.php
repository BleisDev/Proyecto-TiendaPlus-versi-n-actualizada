<?php
session_start();
require_once('../backend/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../web/login.php");
    exit;
}

// Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ../web/carrito.php");
    exit;
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Datos del checkout (POST)
$direccion = $_POST['direccion'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$metodo_pago = $_POST['metodo_pago'] ?? 'tarjeta';
$envio = $_POST['envio'] ?? '';

$map_pago = [
    'tarjeta' => 'Tarjeta de crédito',
    'pse' => 'Transferencia PSE',
    'mercadopago' => 'Mercado Pago',
    'efectivo' => 'Efectivo'
];
$metodo_pago_txt = $map_pago[$metodo_pago] ?? $metodo_pago;

// Calcular carrito
$subtotal = 0;
$items = [];

foreach ($_SESSION['carrito'] as $item) {
    $producto_id = $item['id'];

    $sql = $conn->prepare("SELECT nombre, precio FROM productos WHERE id = ?");
    $sql->bind_param("i", $producto_id);
    $sql->execute();
    $prod = $sql->get_result()->fetch_assoc();
    $sql->close();

    if ($prod) {
        $prod_precio = $prod['precio'];
        $cantidad = $item['cantidad'];

        $items[] = [
            'id' => $producto_id,
            'nombre' => $prod['nombre'],
            'precio' => $prod_precio,
            'cantidad' => $cantidad
        ];

        $subtotal += $prod_precio * $cantidad;
    }
}

// Costo de envío
$envio_costo = 0;
if ($envio === "medellin")
    $envio_costo = 10000;
if ($envio === "otras_antioquia")
    $envio_costo = 15000;

$total = $subtotal + $envio_costo;

// PROCESAR PAGO (insertar en BD)
try {
    $conn->begin_transaction();

    // Insertar pedido (solo con las columnas que existen en tu tabla)
    $estado = 'pendiente';

    $stmt = $conn->prepare("
        INSERT INTO pedidos (usuario_id, fecha, total, estado)
        VALUES (?, NOW(), ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar statement: " . $conn->error);
    }

    $stmt->bind_param("ids", $usuario_id, $total, $estado);

    if (!$stmt->execute()) {
        throw new Exception("Error al insertar pedido: " . $stmt->error);
    }

    $pedido_id = $stmt->insert_id;
    $stmt->close();

    // Insertar detalles del pedido
    $stmt_det = $conn->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");

    if (!$stmt_det) {
        throw new Exception("Error al preparar detalle: " . $conn->error);
    }

    foreach ($items as $item) {
        $stmt_det->bind_param("iiid", $pedido_id, $item['id'], $item['cantidad'], $item['precio']);
        if (!$stmt_det->execute()) {
            throw new Exception("Error al insertar detalle: " . $stmt_det->error);
        }
    }
    $stmt_det->close();

    // Limpiar carrito de BD
    $stmt_clear = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
    $stmt_clear->bind_param("i", $usuario_id);
    $stmt_clear->execute();
    $stmt_clear->close();

    $conn->commit();

    // Limpiar carrito de sesión
    unset($_SESSION['carrito']);

    // Redirigir a factura
    header("Location: factura.php?pedido_id=" . $pedido_id);
    exit;

} catch (Exception $e) {
    if ($conn->errno) {
        $conn->rollback();
    }

    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Error - TiendaPlus</title>
        <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' rel='stylesheet'>
        <style>
            body { font-family: 'Poppins', sans-serif; background: #f5f5f5; padding: 50px; text-align: center; }
            .error-box { background: white; padding: 40px; border-radius: 15px; max-width: 500px; margin: 0 auto; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
            h1 { color: #e74c3c; margin-bottom: 20px; }
            p { color: #666; line-height: 1.6; }
            .btn { display: inline-block; margin-top: 20px; padding: 12px 30px; background: #d4a574; color: white; text-decoration: none; border-radius: 8px; }
            .btn:hover { background: #c49563; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>⚠️ Error al Procesar el Pago</h1>
            <p><strong>Detalle del error:</strong></p>
            <p style='background: #fee; padding: 15px; border-radius: 8px; color: #c33;'>" . htmlspecialchars($e->getMessage()) . "</p>
            <a href='../web/checkout.php' class='btn'>Volver al Checkout</a>
        </div>
    </body>
    </html>";
    exit;
}
?>