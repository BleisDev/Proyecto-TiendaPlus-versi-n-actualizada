<?php
// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión
$conn = new mysqli("localhost", "root", "", "TiendaPlus");
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

// --- Actualizar estado del pedido ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['estado'])) {
    $id = intval($_POST['id']);
    $estado = $_POST['estado'];
    $stmt = $conn->prepare("UPDATE pedidos SET estado=? WHERE id=?");
    $stmt->bind_param("si", $estado, $id);
    $stmt->execute();
    $stmt->close();
}

// --- Eliminar pedido ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $id = intval($_POST['eliminar_id']);
    $conn->query("DELETE FROM detalle_pedido WHERE pedido_id = $id");
    $conn->query("DELETE FROM pedidos WHERE id = $id");
    echo "<script>location.href='pedidos.php';</script>";
    exit;
}

// --- Consultar pedidos ---
$sql = "SELECT p.id, u.nombre AS usuario, p.fecha, p.total, p.estado 
        FROM pedidos p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha DESC";
$result = $conn->query($sql);

// --- Consultar detalles de pedido si se selecciona uno ---
$detalles = [];
if (isset($_GET['ver'])) {
    $pedido_id = intval($_GET['ver']);
    $query_detalle = "SELECT dp.producto_id, pr.nombre AS producto, dp.cantidad, dp.precio
                      FROM detalle_pedido dp
                      LEFT JOIN productos pr ON dp.producto_id = pr.id
                      WHERE dp.pedido_id = $pedido_id";
    $detalles = $conn->query($query_detalle);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos - Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background: #343a40;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            display: block;
            margin: 10px 0;
            text-decoration: none;
            padding: 10px;
            border-radius: 8px;
        }
        .sidebar a:hover {
            background: #495057;
        }
        table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        th {
            background: #343a40;
            color: white;
        }
        .detalle {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar">
      <h2 class="text-white">Admin</h2>
      <a href="index.php">Dashboard</a>
      <a href="usuarios.php">Usuarios</a>
      <a href="productos.php">Productos</a>
      <a href="carritos.php">Carritos</a>
      <a href="pedidos.php" class="bg-secondary">Pedidos</a>
      <a href="categorias.php">Categorías</a>
      <a href="resenas.php">Reseñas</a>
    </div>

    <!-- Contenido principal -->
    <div class="col-md-10 p-4">
      <h1 class="mb-4">📦 Pedidos</h1>

      <?php if ($result && $result->num_rows > 0): ?>
      <table class="table table-striped table-hover shadow">
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
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['usuario'] ?? 'No asignado') ?></td>
            <td><?= $row['fecha'] ?></td>
            <td>$<?= number_format($row['total'], 2) ?></td>
            <td>
              <form method="POST" class="d-flex">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <select name="estado" class="form-select form-select-sm me-2">
                  <option value="Pendiente" <?= $row['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                  <option value="Aceptado" <?= $row['estado'] === 'Aceptado' ? 'selected' : '' ?>>Aceptado</option>
                  <option value="Enviado" <?= $row['estado'] === 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                  <option value="Entregado" <?= $row['estado'] === 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
              </form>
            </td>
            <td>
              <a href="pedidos.php?ver=<?= $row['id'] ?>" class="btn btn-sm btn-info">Ver Detalle</a>
              <form method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este pedido?')">
                <input type="hidden" name="eliminar_id" value="<?= $row['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted">No hay pedidos registrados aún.</p>
      <?php endif; ?>

      <!-- Detalle del pedido -->
      <?php if (!empty($detalles) && $detalles->num_rows > 0): ?>
      <div class="detalle shadow">
        <h4>🧾 Detalle del Pedido #<?= $pedido_id ?></h4>
        <table class="table table-bordered mt-3">
          <thead class="table-dark">
            <tr>
              <th>ID Producto</th>
              <th>Nombre</th>
              <th>Cantidad</th>
              <th>Precio Unitario</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $suma = 0;
              while ($d = $detalles->fetch_assoc()): 
                $subtotal = $d['cantidad'] * $d['precio'];
                $suma += $subtotal;
            ?>
            <tr>
              <td><?= $d['producto_id'] ?></td>
              <td><?= htmlspecialchars($d['producto']) ?></td>
              <td><?= $d['cantidad'] ?></td>
              <td>$<?= number_format($d['precio'], 2) ?></td>
              <td>$<?= number_format($subtotal, 2) ?></td>
            </tr>
            <?php endwhile; ?>
            <tr>
              <td colspan="4" class="text-end fw-bold">Total</td>
              <td class="fw-bold">$<?= number_format($suma, 2) ?></td>
            </tr>
          </tbody>
        </table>
        <a href="pedidos.php" class="btn btn-secondary mt-2">⬅ Volver a lista</a>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
