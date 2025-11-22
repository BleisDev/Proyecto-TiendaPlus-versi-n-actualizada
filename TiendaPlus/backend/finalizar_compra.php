<?php
session_start();
require_once('../backend/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../web/login.php");
    exit;
}

// 2. Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ../web/carrito.php");
    exit;
}

// 3. Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 4. Datos del checkout (POST)
$direccion = $_POST['direccion'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$metodo_pago = $_POST['pago'] ?? 'tarjeta';
$envio = $_POST['envio_seleccionado'] ?? '';

$map_pago = [
    'tarjeta' => 'Tarjeta de crédito',
    'pse' => 'Transferencia PSE',
    'mercadopago' => 'Mercado Pago',
    'efectivo' => 'Efectivo'
];
$metodo_pago_txt = $map_pago[$metodo_pago] ?? $metodo_pago;

// 5. Calcular carrito
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

// 6. Costo de envío
$envio_costo = 0;
if ($envio === "medellin")
    $envio_costo = 10000;
if ($envio === "otras_antioquia")
    $envio_costo = 15000;

$total = $subtotal + $envio_costo;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pago - TiendaPlus</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #2c2c2c;
            margin-bottom: 10px;
        }

        .header h1 span {
            color: #d4a574;
            font-style: italic;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #d4a574;
            font-size: 20px;
        }

        .info-box {
            background: #fafafa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #d4a574;
        }

        .info-box p {
            margin: 5px 0;
            color: #666;
        }

        .info-box strong {
            color: #2c2c2c;
        }

        .products-list {
            list-style: none;
            padding: 0;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #fafafa;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .product-name {
            color: #2c2c2c;
            font-weight: 500;
        }

        .product-price {
            color: #d4a574;
            font-weight: 600;
        }

        .totals {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            color: #666;
        }

        .total-row.final {
            border-top: 2px solid #d4a574;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 24px;
            font-weight: 700;
            color: #2c2c2c;
        }

        .total-row.final .amount {
            color: #d4a574;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
            margin-top: 30px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #2c2c2c;
            margin-top: 10px;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .confirmation-card {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="confirmation-card">

            <div class="header">
                <h1>Tienda<span>Plus</span></h1>
                <p><i class="fas fa-check-circle" style="color: #d4a574;"></i> Confirmación de Compra</p>
            </div>

            <!-- Cliente -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-user"></i>
                    <span>Información del Cliente</span>
                </div>
                <div class="info-box">
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                </div>
            </div>

            <!-- Envío -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Dirección de Envío</span>
                </div>
                <div class="info-box">
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($direccion) ?></p>
                    <p><strong>Ciudad:</strong> <?= htmlspecialchars($ciudad) ?></p>
                </div>
            </div>

            <!-- Pago -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-credit-card"></i>
                    <span>Método de Pago</span>
                </div>
                <div class="info-box">
                    <p><strong><?= htmlspecialchars($metodo_pago_txt) ?></strong></p>
                </div>
            </div>

            <!-- Productos -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Productos</span>
                </div>
                <ul class="products-list">
                    <?php foreach ($items as $p): ?>
                        <li class="product-item">
                            <span class="product-name">
                                <?= htmlspecialchars($p['nombre']) ?>
                                <small style="color: #999;">x<?= $p['cantidad'] ?></small>
                            </span>
                            <span class="product-price">
                                $<?= number_format($p['precio'] * $p['cantidad'], 0, ',', '.') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Totales -->
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>$<?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                <div class="total-row">
                    <span>Envío:</span>
                    <span>$<?= number_format($envio_costo, 0, ',', '.') ?></span>
                </div>
                <div class="total-row final">
                    <span>Total a Pagar:</span>
                    <span class="amount">$<?= number_format($total, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Botones -->
            <form action="procesar_pago.php" method="POST">
                <input type="hidden" name="direccion" value="<?= htmlspecialchars($direccion) ?>">
                <input type="hidden" name="ciudad" value="<?= htmlspecialchars($ciudad) ?>">
                <input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($metodo_pago) ?>">
                <input type="hidden" name="envio" value="<?= htmlspecialchars($envio) ?>">

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-lock"></i> Confirmar y Pagar
                </button>
            </form>

            <a href="../web/checkout.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Checkout
            </a>

        </div>
    </div>

</body>

</html>