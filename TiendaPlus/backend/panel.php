<?php
session_start();
require_once("conexion.php");

// Solo admin puede entrar
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../web/login.php");
    exit;
}

// Parámetros
$tabla = $_GET['tabla'] ?? 'dashboard';
$accion = $_GET['accion'] ?? 'listar';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Tablas permitidas
$tablas_permitidas = ['usuarios', 'categorias', 'productos', 'carrito', 'pedidos', 'detalle_pedido', 'resenas'];
if (!in_array($tabla, $tablas_permitidas) && $tabla !== 'dashboard') {
    $tabla = 'dashboard';
}

// ============================================
// PROCESAR ACCIONES CRUD
// ============================================

// ELIMINAR
if ($accion === 'eliminar' && $id > 0) {
    try {
        $stmt = $conn->prepare("DELETE FROM $tabla WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conn->error);
        }
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: panel.php?tabla=$tabla&msg=eliminado");
            exit;
        } else {
            throw new Exception("Error ejecutando consulta: " . $stmt->error);
        }
    } catch (Exception $e) {
        header("Location: panel.php?tabla=$tabla&error=" . urlencode($e->getMessage()));
        exit;
    }
}

// ============================================
// API: OBTENER DETALLES DE PEDIDO
// ============================================
if ($accion === 'detalles' && $tabla === 'pedidos' && $id > 0) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                dp.id,
                dp.producto_id,
                dp.cantidad,
                dp.precio,
                dp.talla,
                p.nombre as producto_nombre,
                p.imagen as producto_imagen,
                (dp.cantidad * dp.precio) as subtotal
            FROM detalle_pedido dp
            LEFT JOIN productos p ON dp.producto_id = p.id
            WHERE dp.pedido_id = ?
            ORDER BY dp.id
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $detalles = [];
        $total = 0;
        
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
            $total += $row['subtotal'];
        }
        
        $stmt->close();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'detalles' => $detalles,
            'total' => $total
        ]);
        exit;
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// CREAR / ACTUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($accion === 'crear' || $accion === 'editar')) {
    
    try {
        if ($tabla === 'usuarios') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $rol = $_POST['rol'];
            
            // Validaciones
            if (empty($nombre) || empty($email) || empty($password) || empty($rol)) {
                throw new Exception("Todos los campos son obligatorios");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Email inválido");
            }
            
            if ($accion === 'crear') {
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?");
                $stmt->bind_param("ssssi", $nombre, $email, $password, $rol, $id);
            }
            
        } elseif ($tabla === 'categorias') {
            $nombre = trim($_POST['nombre']);
            
            if (empty($nombre)) {
                throw new Exception("El nombre es obligatorio");
            }
            
            if ($accion === 'crear') {
                $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
                $stmt->bind_param("s", $nombre);
            } else {
                $stmt = $conn->prepare("UPDATE categorias SET nombre=? WHERE id=?");
                $stmt->bind_param("si", $nombre, $id);
            }
            
        } elseif ($tabla === 'productos') {
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);
            $precio = floatval($_POST['precio']);
            $stock = intval($_POST['stock']);
            $imagen = trim($_POST['imagen']);
            $categoria_id = intval($_POST['categoria_id']);
            $destacado = isset($_POST['destacado']) ? 1 : 0;
            $tallas = isset($_POST['tallas']) ? trim($_POST['tallas']) : null;
            
            if (empty($nombre) || empty($descripcion) || $precio <= 0 || $stock < 0 || empty($imagen) || $categoria_id <= 0) {
                throw new Exception("Todos los campos son obligatorios y deben ser válidos");
            }
            
            if ($accion === 'crear') {
                $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, tallas, imagen, categoria_id, destacado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdissii", $nombre, $descripcion, $precio, $stock, $tallas, $imagen, $categoria_id, $destacado);
            } else {
                $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, tallas=?, imagen=?, categoria_id=?, destacado=? WHERE id=?");
                $stmt->bind_param("ssdissiii", $nombre, $descripcion, $precio, $stock, $tallas, $imagen, $categoria_id, $destacado, $id);
            }
            
        } elseif ($tabla === 'carrito') {
            $usuario_id = intval($_POST['usuario_id']);
            $producto_id = intval($_POST['producto_id']);
            $cantidad = intval($_POST['cantidad']);
            
            if ($usuario_id <= 0 || $producto_id <= 0 || $cantidad <= 0) {
                throw new Exception("Todos los campos deben ser válidos");
            }
            
            if ($accion === 'crear') {
                $stmt = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad, fecha_agregado) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iii", $usuario_id, $producto_id, $cantidad);
            } else {
                $stmt = $conn->prepare("UPDATE carrito SET usuario_id=?, producto_id=?, cantidad=? WHERE id=?");
                $stmt->bind_param("iiii", $usuario_id, $producto_id, $cantidad, $id);
            }
            
        } elseif ($tabla === 'pedidos') {
            $usuario_id = intval($_POST['usuario_id']);
            $total = floatval($_POST['total']);
            $estado = $_POST['estado'];
            
            if ($usuario_id <= 0 || $total <= 0 || empty($estado)) {
                throw new Exception("Todos los campos son obligatorios y deben ser válidos");
            }
            
            if ($accion === 'crear') {
                $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, fecha, total, estado) VALUES (?, NOW(), ?, ?)");
                $stmt->bind_param("ids", $usuario_id, $total, $estado);
            } else {
                $stmt = $conn->prepare("UPDATE pedidos SET usuario_id=?, total=?, estado=? WHERE id=?");
                $stmt->bind_param("idsi", $usuario_id, $total, $estado, $id);
            }
            
        } elseif ($tabla === 'detalle_pedido') {
            $pedido_id = intval($_POST['pedido_id']);
            $producto_id = intval($_POST['producto_id']);
            $cantidad = intval($_POST['cantidad']);
            $precio = floatval($_POST['precio']);
            
            if ($pedido_id <= 0 || $producto_id <= 0 || $cantidad <= 0 || $precio <= 0) {
                throw new Exception("Todos los campos deben ser válidos");
            }
            
            if ($accion === 'crear') {
                $stmt = $conn->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio);
            } else {
                $stmt = $conn->prepare("UPDATE detalle_pedido SET pedido_id=?, producto_id=?, cantidad=?, precio=? WHERE id=?");
                $stmt->bind_param("iiidi", $pedido_id, $producto_id, $cantidad, $precio, $id);
            }
            
        } elseif ($tabla === 'resenas') {
            $usuario_id = intval($_POST['usuario_id']);
            $producto_id = intval($_POST['producto_id']);
            $comentario = trim($_POST['comentario']);
            $calificacion = intval($_POST['calificacion']);
            
            if ($usuario_id <= 0 || $producto_id <= 0 || empty($comentario) || $calificacion < 1 || $calificacion > 5) {
                throw new Exception("Todos los campos son obligatorios y la calificación debe estar entre 1 y 5");
            }
            
            if ($accion === 'crear') {
                $stmt = $conn->prepare("INSERT INTO resenas (usuario_id, producto_id, comentario, calificacion, fecha) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("iisi", $usuario_id, $producto_id, $comentario, $calificacion);
            } else {
                $stmt = $conn->prepare("UPDATE resenas SET usuario_id=?, producto_id=?, comentario=?, calificacion=? WHERE id=?");
                $stmt->bind_param("iisii", $usuario_id, $producto_id, $comentario, $calificacion, $id);
            }
        }
        
        if (!isset($stmt)) {
            throw new Exception("Tabla no válida");
        }
        
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conn->error);
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: panel.php?tabla=$tabla&msg=guardado");
            exit;
        } else {
            throw new Exception("Error ejecutando consulta: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        header("Location: panel.php?tabla=$tabla&accion=$accion" . ($id > 0 ? "&id=$id" : "") . "&error=" . urlencode($e->getMessage()));
        exit;
    }
}

// Obtener datos para editar
$registro = null;
if ($accion === 'editar' && $id > 0) {
    $stmt = $conn->prepare("SELECT * FROM $tabla WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $registro = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// Consultas para listar
if ($accion === 'listar') {
    $usuarios = $conn->query("SELECT * FROM usuarios");
    $categorias = $conn->query("SELECT * FROM categorias");
    $productos = $conn->query("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id");
    $carritos = $conn->query("SELECT ca.*, u.nombre AS usuario, p.nombre AS producto FROM carrito ca LEFT JOIN usuarios u ON ca.usuario_id = u.id LEFT JOIN productos p ON ca.producto_id = p.id");
    $pedidos = $conn->query("SELECT pe.*, u.nombre AS usuario FROM pedidos pe LEFT JOIN usuarios u ON pe.usuario_id = u.id");
    $detalle_pedido = $conn->query("SELECT dp.*, p.nombre AS producto FROM detalle_pedido dp LEFT JOIN productos p ON dp.producto_id = p.id");
    $resenas = $conn->query("SELECT r.*, u.nombre AS usuario, p.nombre AS producto FROM resenas r LEFT JOIN usuarios u ON r.usuario_id = u.id LEFT JOIN productos p ON r.producto_id = p.id");
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - TiendaPlus</title>

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
            line-height: 1.6;
        }

        /* HEADER */
        header {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: #ffffff;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 2px;
        }

        .logo span {
            color: #d4a574;
            font-style: italic;
        }

        .header-actions a {
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border: 2px solid #d4a574;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .header-actions a:hover {
            background: #d4a574;
            color: #2c2c2c;
        }

        /* CONTAINER */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px;
            display: flex;
            gap: 30px;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: #ffffff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar h3 {
            font-family: 'Playfair Display', serif;
            color: #2c2c2c;
            margin-bottom: 20px;
            font-size: 20px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #2c2c2c;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }

        .sidebar-menu a:hover {
            background: #f5f5f5;
            color: #d4a574;
            transform: translateX(5px);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: #ffffff;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
        }

        /* ALERTS */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* DASHBOARD CARDS */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(212, 165, 116, 0.2);
        }

        .stat-card .icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #d4a574;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #2c2c2c;
        }

        /* CARD */
        .card {
            background: #ffffff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f5f5f5;
        }

        .card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: #2c2c2c;
        }

        /* BUTTONS */
        .btn {
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: #ffffff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 165, 116, 0.3);
        }

        .btn-success {
            background: #10b981;
            color: #ffffff;
        }

        .btn-warning {
            background: #f59e0b;
            color: #ffffff;
        }

        .btn-danger {
            background: #ef4444;
            color: #ffffff;
        }

        .btn-secondary {
            background: #6c757d;
            color: #ffffff;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        /* TABLE */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: #ffffff;
        }

        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 15px;
            border-bottom: 1px solid #f5f5f5;
        }

        table tbody tr {
            transition: background 0.2s;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        /* FORM */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c2c2c;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #d4a574;
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <header>
        <div class="header-content">
            <div class="logo">Tienda<span>Plus</span> <small style="font-size: 14px; font-weight: 400;">Admin</small>
            </div>
            <div class="header-actions">
                <a href="../web/index.php"><i class="fas fa-arrow-left"></i> Volver a Tienda</a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <h3>Menú</h3>
            <ul class="sidebar-menu">
                <li><a href="panel.php?tabla=dashboard" class="<?= $tabla === 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a></li>
                <li><a href="panel.php?tabla=usuarios" class="<?= $tabla === 'usuarios' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> Usuarios
                    </a></li>
                <li><a href="panel.php?tabla=categorias" class="<?= $tabla === 'categorias' ? 'active' : '' ?>">
                        <i class="fas fa-tags"></i> Categorías
                    </a></li>
                <li><a href="panel.php?tabla=productos" class="<?= $tabla === 'productos' ? 'active' : '' ?>">
                        <i class="fas fa-box"></i> Productos
                    </a></li>
                <li><a href="panel.php?tabla=carrito" class="<?= $tabla === 'carrito' ? 'active' : '' ?>">
                        <i class="fas fa-shopping-cart"></i> Carrito
                    </a></li>
                <li><a href="panel.php?tabla=pedidos" class="<?= $tabla === 'pedidos' ? 'active' : '' ?>">
                        <i class="fas fa-receipt"></i> Pedidos
                    </a></li>
                <li><a href="panel.php?tabla=detalle_pedido" class="<?= $tabla === 'detalle_pedido' ? 'active' : '' ?>">
                        <i class="fas fa-list"></i> Detalle Pedidos
                    </a></li>
                <li><a href="panel.php?tabla=resenas" class="<?= $tabla === 'resenas' ? 'active' : '' ?>">
                        <i class="fas fa-star"></i> Reseñas
                    </a></li>
            </ul>
        </aside>

        <!-- MAIN CONTENT -->
    <main class="main-content">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <?php 
                    if ($_GET['msg'] === 'guardado') {
                        echo 'Registro guardado exitosamente';
                    } elseif ($_GET['msg'] === 'eliminado') {
                        echo 'Registro eliminado exitosamente';
                    } else {
                        echo 'Operación realizada con éxito';
                    }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    if ($_GET['error'] === '1') {
                        echo 'Error al realizar la operación';
                    } else {
                        echo htmlspecialchars(urldecode($_GET['error']));
                    }
                ?>
            </div>
        <?php endif; ?>

            <?php if ($tabla === 'dashboard'): ?>
                <!-- DASHBOARD -->
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <h3>Usuarios</h3>
                        <div class="number">
                            <?= $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'] ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="icon"><i class="fas fa-box"></i></div>
                        <h3>Productos</h3>
                        <div class="number">
                            <?= $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'] ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="icon"><i class="fas fa-receipt"></i></div>
                        <h3>Pedidos</h3>
                        <div class="number">
                            <?= $conn->query("SELECT COUNT(*) as total FROM pedidos")->fetch_assoc()['total'] ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="icon"><i class="fas fa-tags"></i></div>
                        <h3>Categorías</h3>
                        <div class="number">
                            <?= $conn->query("SELECT COUNT(*) as total FROM categorias")->fetch_assoc()['total'] ?></div>
                    </div>
                </div>

                <div class="card">
                    <h2 style="font-family: 'Playfair Display', serif; color: #2c2c2c; margin-bottom: 15px;">Bienvenido al
                        Panel Administrativo</h2>
                    <p style="color: #6c757d;">Gestiona todos los aspectos de TiendaPlus desde este panel. Utiliza el menú
                        lateral para navegar entre las diferentes secciones.</p>
                </div>

            <?php elseif ($accion === 'crear' || $accion === 'editar'): ?>
                <!-- FORMULARIO CREAR/EDITAR -->
                <div class="card">
                    <div class="card-header">
                        <h2><?= $accion === 'crear' ? 'Crear' : 'Editar' ?>     <?= ucfirst(str_replace('_', ' ', $tabla)) ?>
                        </h2>
                    </div>

                    <form method="POST">
                        <?php if ($tabla === 'usuarios'): ?>
                            <div class="form-group">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" value="<?= $registro['nombre'] ?? '' ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $registro['email'] ?? '' ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control"
                                    value="<?= $registro['password'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Rol</label>
                                <select name="rol" class="form-control" required>
                                    <option value="cliente" <?= ($registro['rol'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente
                                    </option>
                                    <option value="admin" <?= ($registro['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin
                                    </option>
                                </select>
                            </div>

                        <?php elseif ($tabla === 'categorias'): ?>
                            <div class="form-group">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" value="<?= $registro['nombre'] ?? '' ?>"
                                    required>
                            </div>

                        <?php elseif ($tabla === 'productos'): ?>
                            <div class="form-group">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" value="<?= $registro['nombre'] ?? '' ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control"
                                    required><?= $registro['descripcion'] ?? '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Precio</label>
                                <input type="number" name="precio" class="form-control" value="<?= $registro['precio'] ?? '' ?>"
                                    step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Stock</label>
                                <input type="number" name="stock" class="form-control" value="<?= $registro['stock'] ?? '' ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tallas</label>
                                <input type="text" name="tallas" class="form-control" value="<?= $registro['tallas'] ?? '' ?>"
                                    placeholder="Ej: S,M,L,XL,XXL o Única">
                                <small style="color: #666; font-size: 12px;">Separar tallas con comas. Dejar vacío si no aplica.</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Imagen</label>
                                <input type="text" name="imagen" class="form-control" value="<?= $registro['imagen'] ?? '' ?>"
                                    required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Categoría</label>
                                <select name="categoria_id" class="form-control" required>
                                    <?php
                                    $cats = $conn->query("SELECT * FROM categorias");
                                    while ($cat = $cats->fetch_assoc()):
                                        ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($registro['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= $cat['nombre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="destacado" <?= ($registro['destacado'] ?? 0) == 1 ? 'checked' : '' ?>>
                                    <label class="form-label" style="margin: 0;">Destacado</label>
                                </div>
                            </div>

                        <?php elseif ($tabla === 'carrito'): ?>
                            <div class="form-group">
                                <label class="form-label">Usuario</label>
                                <select name="usuario_id" class="form-control" required>
                                    <?php
                                    $users = $conn->query("SELECT * FROM usuarios");
                                    while ($user = $users->fetch_assoc()):
                                        ?>
                                        <option value="<?= $user['id'] ?>" <?= ($registro['usuario_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                            <?= $user['nombre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Producto</label>
                                <select name="producto_id" class="form-control" required>
                                    <?php
                                    $prods = $conn->query("SELECT * FROM productos");
                                    while ($prod = $prods->fetch_assoc()):
                                        ?>
                                        <option value="<?= $prod['id'] ?>" <?= ($registro['producto_id'] ?? '') == $prod['id'] ? 'selected' : '' ?>>
                                            <?= $prod['nombre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control"
                                    value="<?= $registro['cantidad'] ?? '' ?>" required>
                            </div>

                        <?php elseif ($tabla === 'pedidos'): ?>
                            <div class="form-group">
                                <label class="form-label">Usuario</label>
                                <select name="usuario_id" class="form-control" required>
                                    <?php
                                    $users = $conn->query("SELECT * FROM usuarios");
                                    while ($user = $users->fetch_assoc()):
                                        ?>
                                        <option value="<?= $user['id'] ?>" <?= ($registro['usuario_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                            <?= $user['nombre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total</label>
                                <input type="number" name="total" class="form-control" value="<?= $registro['total'] ?? '' ?>"
                                    step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-control" required>
                                    <option value="pendiente" <?= ($registro['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>
                                        Pendiente</option>
                                    <option value="procesando" <?= ($registro['estado'] ?? '') === 'procesando' ? 'selected' : '' ?>>Procesando</option>
                                    <option value="completado" <?= ($registro['estado'] ?? '') === 'completado' ? 'selected' : '' ?>>Completado</option>
                                    <option value="cancelado" <?= ($registro['estado'] ?? '') === 'cancelado' ? 'selected' : '' ?>>
                                        Cancelado</option>
                                </select>
                            </div>

                        <?php elseif ($tabla === 'detalle_pedido'): ?>
                            <div class="form-group">
                                <label class="form-label">Pedido</label>
                                <select name="pedido_id" class="form-control" required>
                                    <?php
                                    $peds = $conn->query("SELECT * FROM pedidos");
                                    while ($ped = $peds->fetch_assoc()):
                                        ?>
                                        <option value="<?= $ped['id'] ?>" <?= ($registro['pedido_id'] ?? '') == $ped['id'] ? 'selected' : '' ?>>
                                            Pedido #<?= $ped['id'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Producto</label>
                                <select name="producto_id" class="form-control" required>
                                    <?php
                                    $prods = $conn->query("SELECT * FROM productos");
                                    while ($prod = $prods->fetch_assoc()):
                                        ?>
                                        <option value="<?= $prod['id'] ?>" <?= ($registro['producto_id'] ?? '') == $prod['id'] ? 'selected' : '' ?>>
                                            <?= $prod['nombre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control"
                                    value="<?= $registro['cantidad'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Precio</label>
                                <input type="number" name="precio" class="form-control" value="<?= $registro['precio'] ?? '' ?>"
                                    step="0.01" required>
                            </div>

                        <?php elseif ($tabla === 'resenas'): ?>
                            <div class="form-group">
                                <label class="form-label">Usuario</label>
                                <select name="usuario_id" class="form-control" required>
                                    <?php
                                    $users = $conn->query("SELECT * FROM usuarios");
                                    while ($user = $users->fetch_assoc()):
                                        ?>
                                        <option value="<?= $user['id'] ?>" <?= ($registro['usuario_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                            <?= $user['nombre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Producto</label>
                                <select name="producto_id" class="form-control" required>
                                    <?php
                                    $prods = $conn->query("SELECT * FROM productos");
                                    while ($prod = $prods->fetch_assoc()):
                                        ?>
                                        <option value="<?= $prod['id'] ?>" <?= ($registro['producto_id'] ?? '') == $prod['id'] ? 'selected' : '' ?>>
                                            <?= $prod['nombre'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Comentario</label>
                                <textarea name="comentario" class="form-control"
                                    required><?= $registro['comentario'] ?? '' ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Calificación (1-5)</label>
                                <input type="number" name="calificacion" class="form-control"
                                    value="<?= $registro['calificacion'] ?? '' ?>" min="1" max="5" required>
                            </div>
                        <?php endif; ?>

                        <div style="display: flex; gap: 10px; margin-top: 30px;">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="panel.php?tabla=<?= $tabla ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <!-- LISTAR REGISTROS -->
                <div class="card">
                    <div class="card-header">
                        <h2><?= ucfirst(str_replace('_', ' ', $tabla)) ?></h2>
                        <a href="panel.php?tabla=<?= $tabla ?>&accion=crear" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Crear Nuevo
                        </a>
                    </div>

                    <div class="table-responsive">
                        <?php if ($tabla === 'usuarios'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($u = $usuarios->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $u['id'] ?></td>
                                            <td><?= $u['nombre'] ?></td>
                                            <td><?= $u['email'] ?></td>
                                            <td><span class="badge badge-info"><?= $u['rol'] ?></span></td>
                                            <td>
                                                <a href="panel.php?tabla=usuarios&accion=editar&id=<?= $u['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="panel.php?tabla=usuarios&accion=eliminar&id=<?= $u['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        <?php elseif ($tabla === 'categorias'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($c = $categorias->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $c['id'] ?></td>
                                            <td><?= $c['nombre'] ?></td>
                                            <td>
                                                <a href="panel.php?tabla=categorias&accion=editar&id=<?= $c['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="panel.php?tabla=categorias&accion=eliminar&id=<?= $c['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        <?php elseif ($tabla === 'productos'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Categoría</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($p = $productos->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $p['id'] ?></td>
                                            <td><?= $p['nombre'] ?></td>
                                            <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                                            <td><?= $p['stock'] ?></td>
                                            <td><?= $p['categoria'] ?></td>
                                            <td>
                                                <a href="panel.php?tabla=productos&accion=editar&id=<?= $p['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="panel.php?tabla=productos&accion=eliminar&id=<?= $p['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        <?php elseif ($tabla === 'carrito'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($ca = $carritos->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $ca['id'] ?></td>
                                            <td><?= $ca['usuario'] ?></td>
                                            <td><?= $ca['producto'] ?></td>
                                            <td><?= $ca['cantidad'] ?></td>
                                            <td><?= $ca['fecha_agregado'] ?></td>
                                            <td>
                                                <a href="panel.php?tabla=carrito&accion=editar&id=<?= $ca['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="panel.php?tabla=carrito&accion=eliminar&id=<?= $ca['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        <?php elseif ($tabla === 'pedidos'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($pe = $pedidos->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $pe['id'] ?></td>
                                            <td><?= $pe['usuario'] ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($pe['fecha'])) ?></td>
                                            <td>$<?= number_format($pe['total'], 0, ',', '.') ?></td>
                                            <td><span class="badge badge-warning"><?= $pe['estado'] ?></span></td>
                                            <td>
                                                <button onclick="toggleDetallesPedido(<?= $pe['id'] ?>)" 
                                                    class="btn btn-info btn-sm" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="panel.php?tabla=pedidos&accion=editar&id=<?= $pe['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="panel.php?tabla=pedidos&accion=eliminar&id=<?= $pe['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <!-- Fila expandible para detalles -->
                                        <tr id="detalles-row-<?= $pe['id'] ?>" class="detalles-pedido-row" style="display:none;">
                                            <td colspan="6" style="padding: 0; background: #f8f9fa;">
                                                <div id="detalles-<?= $pe['id'] ?>" class="detalles-container">
                                                    <div class="loading-detalles">
                                                        <i class="fas fa-spinner fa-spin"></i> Cargando detalles...
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        <?php elseif ($tabla === 'detalle_pedido'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pedido #</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($dp = $detalle_pedido->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $dp['id'] ?></td>
                                            <td>#<?= $dp['pedido_id'] ?></td>
                                            <td><?= $dp['producto'] ?></td>
                                            <td><?= $dp['cantidad'] ?></td>
                                            <td>$<?= number_format($dp['precio'], 0, ',', '.') ?></td>
                                            <td>
                                                <a href="panel.php?tabla=detalle_pedido&accion=editar&id=<?= $dp['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="panel.php?tabla=detalle_pedido&accion=eliminar&id=<?= $dp['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>

                        <?php elseif ($tabla === 'resenas'): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Producto</th>
                                        <th>Comentario</th>
                                        <th>Calificación</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($r = $resenas->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $r['id'] ?></td>
                                            <td><?= $r['usuario'] ?></td>
                                            <td><?= $r['producto'] ?></td>
                                            <td><?= substr($r['comentario'], 0, 50) ?>...</td>
                                            <td><?= str_repeat('⭐', $r['calificacion']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                                            <td>
                                                <a href="panel.php?tabla=resenas&accion=editar&id=<?= $r['id'] ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="panel.php?tabla=resenas&accion=eliminar&id=<?= $r['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Sistema de Detalles de Pedidos
        const detallesCargados = new Set();

        function toggleDetallesPedido(pedidoId) {
            const row = document.getElementById('detalles-row-' + pedidoId);
            const container = document.getElementById('detalles-' + pedidoId);
            
            // Si ya está visible, ocultarlo
            if (row.style.display !== 'none') {
                row.style.display = 'none';
                return;
            }
            
            // Si ya se cargaron los detalles, solo mostrar
            if (detallesCargados.has(pedidoId)) {
                row.style.display = 'table-row';
                return;
            }
            
            // Mostrar fila y cargar detalles
            row.style.display = 'table-row';
            
            fetch('panel.php?tabla=pedidos&accion=detalles&id=' + pedidoId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarDetalles(pedidoId, data.detalles, data.total);
                        detallesCargados.add(pedidoId);
                    } else {
                        container.innerHTML = '<div class="error-detalles">Error al cargar detalles: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="error-detalles">Error al cargar los detalles del pedido</div>';
                });
        }

        function mostrarDetalles(pedidoId, detalles, total) {
            const container = document.getElementById('detalles-' + pedidoId);
            
            if (detalles.length === 0) {
                container.innerHTML = '<div class="empty-detalles">No hay productos en este pedido</div>';
                return;
            }
            
            let html = `
                <div class="detalles-header">
                    <h4><i class="fas fa-shopping-bag"></i> Detalles del Pedido #${pedidoId}</h4>
                </div>
                <table class="tabla-detalles">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Talla</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            detalles.forEach(item => {
                const tallaDisplay = item.talla ? item.talla : '<span style="color:#999;">N/A</span>';
                html += `
                    <tr>
                        <td><strong>${item.producto_nombre || 'Producto eliminado'}</strong></td>
                        <td>${tallaDisplay}</td>
                        <td>${item.cantidad}</td>
                        <td>$${Number(item.precio).toLocaleString('es-CO')}</td>
                        <td>$${Number(item.subtotal).toLocaleString('es-CO')}</td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" style="text-align: right;"><strong>TOTAL:</strong></td>
                            <td><strong>$${Number(total).toLocaleString('es-CO')}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            `;
            
            container.innerHTML = html;
        }
    </script>

    <style>
        /* Estilos para detalles de pedidos */
        .detalles-pedido-row {
            transition: all 0.3s ease;
        }
        
        .detalles-container {
            padding: 20px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .loading-detalles, .error-detalles, .empty-detalles {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .error-detalles {
            color: #dc3545;
        }
        
        .detalles-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d4a574;
        }
        
        .detalles-header h4 {
            margin: 0;
            color: #2c2c2c;
            font-size: 18px;
        }
        
        .detalles-header i {
            color: #d4a574;
            margin-right: 8px;
        }
        
        .tabla-detalles {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .tabla-detalles thead {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
        }
        
        .tabla-detalles th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        
        .tabla-detalles tbody tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        .tabla-detalles tbody tr:hover {
            background: #f8f9fa;
        }
        
        .tabla-detalles td {
            padding: 12px;
            font-size: 14px;
        }
        
        .tabla-detalles tfoot {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .tabla-detalles tfoot td {
            padding: 15px 12px;
            font-size: 16px;
            color: #2c2c2c;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            color: white;
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
        }
    </style>

</body>

</html>