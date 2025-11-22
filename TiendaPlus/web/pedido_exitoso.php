<?php
session_start();
$pedido = $_SESSION['ultimo_pedido'] ?? null;

if (!$pedido) {
    echo "<p>No hay pedido reciente.</p>";
    exit;
}

$conn = new mysqli("localhost", "root", "", "TiendaPlus");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener fecha real del pedido desde la base de datos
$id = $pedido['id'];
$result = $conn->query("SELECT fecha FROM pedidos WHERE id = $id");
$fecha = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['fecha'] : date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pedido Exitoso - Tienda Plus</title>
<style>
body {
  font-family: 'Arial', sans-serif;
  background: #fff5f9;
  margin: 0; padding: 0;
}
.contenedor {
  max-width: 800px;
  background: #fff;
  margin: 40px auto;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
h1 {
  color: #ff4081;
  text-align: center;
}
p {
  color: #333;
  margin: 6px 0;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: center;
}
th {
  background: #ffe4ef;
}
.total {
  background: #ffe4ef;
  font-weight: bold;
}
.boton {
  display: inline-block;
  background: #ff4081;
  color: white;
  padding: 12px 20px;
  text-decoration: none;
  border-radius: 8px;
  margin-top: 20px;
  text-align: center;
}
.boton:hover { background: #ff77a9; transition: 0.3s; }
</style>
</head>
<body>
<div class="contenedor">
  <h1>🧾 Pedido realizado con éxito</h1>

  <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido['cliente']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($pedido['email']) ?></p>
  <p><strong>Dirección:</strong> <?= htmlspecialchars($pedido['direccion']) ?></p>
  <p><strong>Ciudad:</strong> <?= htmlspecialchars($pedido['ciudad']) ?></p>
  <p><strong>Método de pago:</strong> <?= htmlspecialchars($pedido['metodo_pago']) ?></p>
  <p><strong>Fecha del pedido:</strong> <?= date("d/m/Y H:i:s", strtotime($fecha)) ?></p>

  <h3>Resumen de productos</h3>
  <table>
    <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr>
    <?php
    $total = 0;
    foreach ($pedido['productos'] as $item):
        $subtotal = $item['precio'] * $item['cantidad'];
        $total += $subtotal;
    ?>
    <tr>
      <td><?= htmlspecialchars($item['nombre']) ?></td>
      <td><?= $item['cantidad'] ?></td>
      <td>$<?= number_format($item['precio'], 0, ',', '.') ?></td>
      <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="total"><td colspan="3">Total pagado</td><td>$<?= number_format($total, 0, ',', '.') ?></td></tr>
  </table>

  <!-- Botón volver -->
  <div style="text-align:center;">
    <a href="catalogo.php" class="boton">🛍️ Volver al catálogo</a>
  </div>

  <!-- Botón descargar factura -->
  <?php
  $pedido_id = $pedido['id'] ?? null;
  if ($pedido_id) {
      echo '
      <div style="text-align:center; margin-top:15px;">
          <a href="../backend/factura.php?id=' . $pedido_id . '" target="_blank"
             style="display:inline-block; background:#ff4081; color:white; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:bold;">
              📄 Descargar factura
          </a>
      </div>';
  }
  ?>

</div>
</body>
</html>

