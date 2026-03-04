<?php
session_start();

// Seguridad: verificar si es administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "TiendaPlus");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener el ID del pedido
$pedido_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pedido_id <= 0) {
    die("<h2>❌ Pedido no válido</h2>");
}

// Consultar datos principales del pedido
$sql_pedido = "
    SELECT p.*, u.nombre AS usuario_nombre
    FROM pedidos p
    LEFT JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.id = ?
";
$stmt = $conn->prepare($sql_pedido);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) {
    die("<h2>❌ Pedido no encontrado</h2>");
}

// Consultar los productos del pedido
$sql_detalle = "
    SELECT dp.producto_id, pr.nombre, dp.cantidad, dp.precio
    FROM detalle_pedido dp
    JOIN productos pr ON pr.id = dp.producto_id
    WHERE dp.pedido_id = ?
";
$stmt = $conn->prepare($sql_detalle);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$detalle = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle del Pedido #<?= $pedido_id ?> | Panel Administrador</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
  --azul: #ff4081;
  --gris-fondo: #f3f4f6;
  --gris-borde: #e5e7eb;
  --texto: #1f2937;
}
body {
  font-family: 'Segoe UI', Tahoma, sans-serif;
  background: var(--gris-fondo);
  color: var(--texto);
  margin: 0;
  padding: 0;
}
.container {
  max-width: 1000px;
  background: white;
  margin: 40px auto;
  padding: 30px 40px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}
h1 {
  color: var(--azu);
  font-size: 26px;
  margin: 0;
}
.info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 15px;
  margin-top: 15px;
  font-size: 15px;
}
.info div {
  background: #f9fafb;
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid var(--gris-borde);
}
.info strong {
  color: var(--azul);
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 25px;
  border-radius: 10px;
  overflow: hidden;
}
th {
  background: var(--azul);
  color: white;
  text-align: left;
  padding: 12px;
}
td {
  border-bottom: 1px solid var(--gris-borde);
  padding: 10px;
}
tr:hover {
  background: #f1f5f9;
}
.total {
  text-align: right;
  font-weight: bold;
  background: #f9fafb;
  padding: 12px;
}
.btn-back {
  display: inline-block;
  margin-top: 30px;
  padding: 12px 28px;
  background: var(--azul);
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}
.btn-back:hover {
  background: #1d4ed8;
  transform: translateY(-2px);
}
footer {
  text-align: center;
  color: #6b7280;
  margin-top: 40px;
  font-size: 14px;
}
</style>
</head>
<body>
<div class="container">
  <header>
    <h1>📦 Detalle del Pedido #<?= $pedido['id'] ?></h1>
  </header>

  <section class="info">
    <div><strong>Cliente:</strong><br><?= htmlspecialchars($pedido['cliente']) ?></div>
    <div><strong>Correo:</strong><br><?= htmlspecialchars($pedido['email']) ?></div>
    <div><strong>Dirección:</strong><br><?= htmlspecialchars($pedido['direccion']) ?></div>
    <div><strong>Ciudad:</strong><br><?= htmlspecialchars($pedido['ciudad']) ?></div>
    <div><strong>Método de pago:</strong><br><?= htmlspecialchars($pedido['metodo_pago']) ?></div>
    <div><strong>Estado:</strong><br><?= ucfirst($pedido['estado']) ?></div>
    <div><strong>Fecha:</strong><br><?= date("d/m/Y H:i", strtotime($pedido['fecha'])) ?></div>
  </section>

  <table>
    <thead>
      <tr>
        <th>ID Producto</th>
        <th>Nombre del Producto</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $total = 0;
    while ($fila = $detalle->fetch_assoc()):
        $subtotal = $fila['precio'] * $fila['cantidad'];
        $total += $subtotal;
    ?>
      <tr>
        <td><?= $fila['producto_id'] ?></td>
        <td><?= htmlspecialchars($fila['nombre']) ?></td>
        <td><?= $fila['cantidad'] ?></td>
        <td>$<?= number_format($fila['precio'], 0, ',', '.') ?></td>
        <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
      </tr>
    <?php endwhile; ?>
      <tr>
        <td colspan="4" class="total">Total del pedido:</td>
        <td class="total">$<?= number_format($total, 0, ',', '.') ?></td>
      </tr>
    </tbody>
  </table>

  <a href="javascript:history.back()" class="btn-back">⬅ Volver al Panel de Pedidos</a>
</div>

<footer>
  © <?= date('Y') ?> Tienda Plus | Panel de Administración
</footer>
</body>
</html>
