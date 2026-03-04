<?php
require_once('../backend/conexion.php');
session_start();

if (!isset($_GET['pedido_id'])) {
    echo "No se especificó un número de pedido.";
    exit;
}

$pedido_id = intval($_GET['pedido_id']);

// Obtener información del pedido
$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id=?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener detalle del pedido
$stmt = $conn->prepare("SELECT p.nombre, d.cantidad, d.precio 
                        FROM detalle_pedido d 
                        JOIN productos p ON d.producto_id = p.id 
                        WHERE d.pedido_id=?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$detalles = $stmt->get_result();
$stmt->close();

if (!$pedido) {
    echo "Pedido no encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Pedido #<?= $pedido_id ?> - TiendaPlus</title>

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
            background: #fafafa;
            color: #2c2c2c;
            padding: 40px 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header */
        .invoice-header {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .invoice-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            margin-bottom: 10px;
        }

        .invoice-header .logo {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .invoice-header .logo span {
            color: #d4a574;
            font-style: italic;
        }

        .invoice-number {
            font-size: 18px;
            color: #d4a574;
            font-weight: 600;
        }

        /* Content */
        .invoice-content {
            padding: 40px;
        }

        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-section h3 {
            font-size: 16px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .info-section p {
            margin-bottom: 8px;
            color: #666;
            line-height: 1.6;
        }

        .info-section strong {
            color: #2c2c2c;
            font-weight: 600;
        }

        /* Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        .invoice-table thead {
            background: #fafafa;
        }

        .invoice-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c2c2c;
            border-bottom: 2px solid #d4a574;
        }

        .invoice-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }

        .invoice-table tr:hover {
            background: #fafafa;
        }

        .invoice-table .text-right {
            text-align: right;
        }

        .invoice-table .product-name {
            font-weight: 500;
            color: #2c2c2c;
        }

        /* Summary */
        .invoice-summary {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 16px;
        }

        .summary-row.total {
            font-size: 24px;
            font-weight: 700;
            color: #2c2c2c;
            padding-top: 20px;
            border-top: 2px solid #d4a574;
            margin-top: 15px;
        }

        .summary-row.total .amount {
            color: #d4a574;
        }

        /* Actions */
        .invoice-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #f0f0f0;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #2c2c2c;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* Footer */
        .invoice-footer {
            text-align: center;
            padding: 30px;
            background: #fafafa;
            color: #999;
            font-size: 14px;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-container {
                box-shadow: none;
            }

            .invoice-actions {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .invoice-info {
                grid-template-columns: 1fr;
            }

            .invoice-actions {
                flex-direction: column;
            }

            .invoice-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-container">

        <!-- Header -->
        <div class="invoice-header">
            <div class="logo">
                Tienda<span>Plus</span>
            </div>
            <h1><i class="fas fa-file-invoice"></i> Factura de Compra</h1>
            <div class="invoice-number">Pedido #<?= $pedido_id ?></div>
        </div>

        <!-- Content -->
        <div class="invoice-content">

            <!-- Info -->
            <div class="invoice-info">
                <div class="info-section">
                    <h3><i class="fas fa-user"></i> Información del Cliente</h3>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['cliente']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($pedido['email']) ?></p>
                    <p><strong>Dirección:</strong> <?= htmlspecialchars($pedido['direccion']) ?></p>
                    <p><strong>Ciudad:</strong> <?= htmlspecialchars($pedido['ciudad']) ?></p>
                </div>

                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Detalles del Pedido</h3>
                    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></p>
                    <p><strong>Estado:</strong> <?= ucfirst(htmlspecialchars($pedido['estado'])) ?></p>
                    <p><strong>Método de Pago:</strong> <?= htmlspecialchars($pedido['metodo_pago']) ?></p>
                </div>
            </div>

            <!-- Table -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    while ($row = $detalles->fetch_assoc()):
                        $subtotal = $row['cantidad'] * $row['precio'];
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td class="product-name"><?= htmlspecialchars($row['nombre']) ?></td>
                            <td class="text-right"><?= $row['cantidad'] ?></td>
                            <td class="text-right">$<?= number_format($row['precio'], 0, ',', '.') ?></td>
                            <td class="text-right">$<?= number_format($subtotal, 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Summary -->
            <div class="invoice-summary">
                <div class="summary-row total">
                    <span>Total a Pagar:</span>
                    <span class="amount">$<?= number_format($total, 0, ',', '.') ?></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="invoice-actions">
                <a href="javascript:window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Imprimir Factura
                </a>
                <a href="../web/index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Volver a la Tienda
                </a>
            </div>

        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p><i class="fas fa-shield-alt"></i> Gracias por tu compra en TiendaPlus</p>
            <p>© <?= date("Y") ?> TiendaPlus. Todos los derechos reservados.</p>
        </div>

    </div>

</body>

</html>