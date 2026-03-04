<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "TiendaPlus");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$usuario_id = $_SESSION['usuario_id'];

// Consultar datos del usuario
$stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Consultar historial de pedidos del usuario
$pedidos = [];
$query = $conn->prepare("SELECT id, fecha, total, estado FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC");
$query->bind_param("i", $usuario_id);
$query->execute();
$res = $query->get_result();
while ($row = $res->fetch_assoc()) {
    $pedidos[] = $row;
}
$query->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Perfil - Tienda Plus</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
  --rosa: #ff69b4;
  --rosa-claro: #ffe5f0;
  --gris: #f5f5f5;
  --texto: #333;
}
body {
  font-family: 'Poppins', Arial, sans-serif;
  background: var(--gris);
  margin: 0;
  padding: 0;
}
.header {
  background: white;
  padding: 15px 30px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.header h2 {
  color: var(--rosa);
  font-weight: 600;
  margin: 0;
}
.header a {
  text-decoration: none;
  color: var(--rosa);
  font-weight: 500;
}
.container {
  max-width: 900px;
  background: white;
  margin: 40px auto;
  padding: 40px;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.profile-header {
  text-align: center;
  margin-bottom: 30px;
}
.profile-header img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--rosa);
  box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.profile-header h1 {
  color: var(--texto);
  margin-top: 15px;
  font-size: 1.8em;
}
.profile-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
  margin-top: 30px;
}
.info-card {
  background: var(--rosa-claro);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  text-align: center;
}
.info-card h3 {
  color: var(--rosa);
  font-size: 1.1em;
  margin-bottom: 10px;
}
.info-card p {
  color: var(--texto);
  font-size: 1.05em;
  margin: 0;
}

/* Estilo tabla de pedidos */
.table-section {
  margin-top: 50px;
}
.table-section h2 {
  color: var(--rosa);
  text-align: center;
  margin-bottom: 20px;
}
table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
th {
  background: var(--rosa);
  color: white;
  padding: 12px;
  text-align: center;
  font-weight: 500;
}
td {
  padding: 12px;
  text-align: center;
  border-bottom: 1px solid #eee;
}
tr:hover {
  background: var(--rosa-claro);
}
.badge {
  padding: 6px 10px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
}
.badge.pendiente { background: #f39c12; }
.badge.procesando { background: #3498db; }
.badge.completado { background: #2ecc71; }
.badge.cancelado { background: #e74c3c; }

.btn-back {
  display: block;
  width: fit-content;
  margin: 40px auto 0;
  background: var(--rosa);
  color: white;
  text-decoration: none;
  padding: 12px 25px;
  border-radius: 8px;
  font-weight: 500;
  transition: 0.3s;
}
.btn-back:hover {
  background: #ff85c1;
  transform: translateY(-2px);
}
@media (max-width: 700px) {
  .profile-info {
    grid-template-columns: 1fr;
  }
  .container {
    margin: 20px;
    padding: 25px;
  }
}
</style>
</head>
<body>
  <div class="header">
    <h2>🛍️ Tienda Plus</h2>
    <a href="logout.php">Cerrar sesión</a>
  </div>

  <div class="container">
    <div class="profile-header">
      <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Avatar Usuario">
      <h1>👩‍💻 <?= htmlspecialchars($usuario['nombre']) ?></h1>
      <p style="color:#777;">Cliente registrado</p>
    </div>

    <div class="profile-info">
      <div class="info-card">
        <h3>📧 Correo electrónico</h3>
        <p><?= htmlspecialchars($usuario['email']) ?></p>
      </div>
      <div class="info-card">
        <h3>🎁 Nivel de cliente</h3>
        <p>Miembro Plata</p>
      </div>
    </div>

    <div class="table-section">
      <h2>📦 Historial de pedidos</h2>
      <?php if (count($pedidos) > 0): ?>
      <table>
        <tr>
          <th># Pedido</th>
          <th>Fecha</th>
          <th>Total</th>
          <th>Estado</th>
        </tr>
        <?php foreach ($pedidos as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= date("d/m/Y H:i", strtotime($p['fecha'])) ?></td>
          <td>$<?= number_format($p['total'], 0, ',', '.') ?></td>
          <td><span class="badge <?= strtolower($p['estado']) ?>"><?= ucfirst($p['estado']) ?></span></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php else: ?>
        <p style="text-align:center; color:#555;">🕓 No tienes pedidos registrados todavía.</p>
      <?php endif; ?>
    </div>

    <a href="index.php" class="btn-back">🏠 Volver a la Tienda</a>
  </div>
</body>
</html>

