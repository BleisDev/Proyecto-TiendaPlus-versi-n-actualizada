<?php
session_start();
include_once("../backend/conexion.php");

// Si el usuario ha iniciado sesión, guardamos su ID
$usuario_id = $_SESSION['usuario_id'] ?? null;

// --- Inicializar carrito de sesión ---
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// --- Sincronizar carrito con base de datos (solo si el usuario inició sesión) ---
if ($usuario_id) {
    $sql = $conn->prepare("SELECT c.producto_id, c.cantidad, c.talla, p.nombre, p.precio, p.imagen, p.tallas
                           FROM carrito c 
                           INNER JOIN productos p ON c.producto_id = p.id 
                           WHERE c.usuario_id = ?");
    $sql->bind_param("i", $usuario_id);
    $sql->execute();
    $resultado = $sql->get_result();

    $carritoBD = [];
    while ($fila = $resultado->fetch_assoc()) {
        // Crear clave única incluyendo talla si existe
        $clave = $fila['producto_id'] . ($fila['talla'] ? '_' . $fila['talla'] : '');
        $carritoBD[$clave] = [
            'id' => $fila['producto_id'],
            'nombre' => $fila['nombre'],
            'precio' => $fila['precio'],
            'imagen' => $fila['imagen'],
            'cantidad' => $fila['cantidad'],
            'talla' => $fila['talla'],
            'tallas_disponibles' => $fila['tallas']
        ];
    }

    $_SESSION['carrito'] = array_replace($_SESSION['carrito'], $carritoBD);
}

// --- ACTUALIZAR cantidad ---
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $id = intval($_POST['id']);
    $cantidad = intval($_POST['cantidad']);
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad'] = $cantidad;

        if ($usuario_id) {
            $sql = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
            $sql->bind_param("iii", $cantidad, $usuario_id, $id);
            $sql->execute();
        }
    }
    exit;
}

// --- ELIMINAR producto ---
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = intval($_POST['id']);
    unset($_SESSION['carrito'][$id]);

    if ($usuario_id) {
        $sql = $conn->prepare("DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?");
        $sql->bind_param("ii", $usuario_id, $id);
        $sql->execute();
    }
    exit;
}

$mensaje = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':
            $mensaje = "Producto agregado al carrito";
            break;
        case 'updated':
            $mensaje = "Cantidad actualizada";
            break;
        case 'deleted':
            $mensaje = "Producto eliminado";
            break;
    }
}

$total = 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - TiendaPlus</title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: #2c2c2c;
        }

        /* Header */
        header {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-top {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: #ffffff;
            padding: 8px 0;
            font-size: 13px;
            text-align: center;
        }

        .header-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 80px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: #2c2c2c;
            letter-spacing: 2px;
            text-decoration: none;
        }

        .logo span {
            color: #d4a574;
            font-style: italic;
        }

        nav {
            display: flex;
            gap: 30px;
        }

        nav a {
            text-decoration: none;
            color: #2c2c2c;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #d4a574;
        }

        .header-icons {
            display: flex;
            gap: 20px;
            font-size: 20px;
        }

        .header-icons a {
            color: #2c2c2c;
            transition: color 0.3s;
        }

        .header-icons a:hover {
            color: #d4a574;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 16px;
            color: #ccc;
        }

        /* Container */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 40px;
        }

        /* Mensaje */
        .mensaje {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 3px 15px rgba(212, 165, 116, 0.3);
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Carrito Items */
        .carrito-items {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .carrito-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .carrito-item:last-child {
            border-bottom: none;
        }

        .item-image img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }

        .item-info h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 5px;
        }

        .item-talla {
            margin: 8px 0;
        }

        .talla-badge {
            display: inline-block;
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
        }

        .talla-badge i {
            margin-right: 5px;
        }

        .item-price {
            font-size: 20px;
            font-weight: 700;
            color: #d4a574;
        }

        .item-quantity input {
            width: 70px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .item-quantity input:focus {
            outline: none;
            border-color: #d4a574;
        }

        .item-remove button {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }

        .item-remove button:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        /* Empty State */
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-cart i {
            font-size: 100px;
            color: #d4a574;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #2c2c2c;
        }

        .empty-cart p {
            color: #666;
            margin-bottom: 30px;
        }

        /* Summary */
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-row.total {
            border-bottom: none;
            font-size: 24px;
            font-weight: 700;
            color: #2c2c2c;
            padding-top: 20px;
        }

        .summary-row.total span:last-child {
            color: #d4a574;
        }

        /* Buttons */
        .cart-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #2c2c2c;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
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

        /* Footer */
        footer {
            background: #2c2c2c;
            color: #ffffff;
            text-align: center;
            padding: 30px;
            margin-top: 80px;
        }

        footer p {
            margin: 0;
            color: #999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-main {
                padding: 15px 20px;
                flex-wrap: wrap;
            }

            .container {
                padding: 0 20px;
            }

            .carrito-item {
                grid-template-columns: 80px 1fr;
                gap: 15px;
            }

            .item-price,
            .item-quantity,
            .item-remove {
                grid-column: 2;
            }

            .page-header h1 {
                font-size: 32px;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header>
        <div class="header-top">
            <span><i class="fas fa-truck"></i> Envío gratis en compras superiores a $150.000</span>
        </div>

        <div class="header-main">
            <a href="index.php" class="logo">
                Tienda<span>Plus</span>
            </a>

            <nav>
                <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="catalogo.php"><i class="fas fa-th"></i> Catálogo</a>
                <a href="nosotros.php"><i class="fas fa-info-circle"></i> Nosotros</a>
                <a href="contactanos.php"><i class="fas fa-envelope"></i> Contacto</a>
            </nav>

            <div class="header-icons">
                <a href="login.php" title="Mi cuenta"><i class="fas fa-user"></i></a>
                <a href="resenas.php" title="Favoritos"><i class="fas fa-heart"></i></a>
                <a href="carrito.php" title="Carrito" style="color: #d4a574;"><i class="fas fa-shopping-bag"></i></a>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <h1><i class="fas fa-shopping-bag"></i> Mi Carrito</h1>
        <p>Revisa tus productos antes de continuar</p>
    </section>

    <!-- Container -->
    <div class="container">

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['carrito'])): ?>
            <div class="carrito-items">
                <div class="empty-cart">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p>Agrega productos para comenzar tu compra</p>
                    <a href="catalogo.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Ir al Catálogo
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="carrito-items">
                <?php foreach ($_SESSION['carrito'] as $id => $item):
                    if (!isset($item['nombre']) || !isset($item['precio']))
                        continue;
                    $subtotal = $item['precio'] * $item['cantidad'];
                    $total += $subtotal;
                    ?>
                    <div class="carrito-item" id="fila<?= $id ?>">
                        <div class="item-image">
                            <?php if (!empty($item['imagen'])): ?>
                                <img src="img/<?= htmlspecialchars($item['imagen']) ?>"
                                    alt="<?= htmlspecialchars($item['nombre']) ?>">
                            <?php endif; ?>
                        </div>

                        <div class="item-info">
                            <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                            <?php if (!empty($item['talla']) && $item['talla'] !== 'Única'): ?>
                                <div class="item-talla">
                                    <span class="talla-badge">
                                        <i class="fas fa-tag"></i> Talla: <?= htmlspecialchars($item['talla']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="item-price">$<?= number_format($item['precio'], 0, ',', '.') ?></div>
                        </div>

                        <div class="item-quantity">
                            <input type="number" id="cantidad<?= $id ?>" value="<?= $item['cantidad'] ?>" min="1"
                                onchange="actualizarCantidad('<?= $id ?>')">
                        </div>

                        <div class="item-price">
                            $<?= number_format($subtotal, 0, ',', '.') ?>
                        </div>

                        <div class="item-remove">
                            <button onclick="eliminarProducto('<?= $id ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?= number_format($total, 0, ',', '.') ?></span>
                </div>

                <div class="cart-actions">
                    <a href="catalogo.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Seguir Comprando
                    </a>
                    <a href="checkout.php" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Proceder al Pago
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 TiendaPlus. Todos los derechos reservados.</p>
    </footer>

    <script>
        function actualizarCantidad(id) {
            const cantidad = document.getElementById("cantidad" + id).value;
            fetch("carrito.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "accion=actualizar&id=" + id + "&cantidad=" + cantidad
            }).then(() => {
                window.location.href = "carrito.php?msg=updated";
            });
        }

        function eliminarProducto(id) {
            if (!confirm("¿Eliminar este producto del carrito?")) return;

            fetch("carrito.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "accion=eliminar&id=" + id
            }).then(() => {
                window.location.href = "carrito.php?msg=deleted";
            });
        }
    </script>

</body>

</html>