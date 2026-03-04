<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$conn = new mysqli("localhost", "root", "", "TiendaPlus");
if ($conn->connect_error) {
  die("❌ Error de conexión: " . $conn->connect_error);
}
$result = $conn->query("SELECT id, nombre, email, rol FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .sidebar { height: 100vh; background: #343a40; padding: 20px; }
    .sidebar a { color: white; display: block; margin: 10px 0; text-decoration: none; padding: 10px; border-radius: 8px; }
    .sidebar a:hover { background: #495057; }
    table { background: white; border-radius: 12px; overflow: hidden; }
    th { background: #343a40; color: white; }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-2 sidebar">
      <h2 class="text-white">Admin</h2>
      <a href="index.php">Dashboard</a>
      <a href="usuarios.php" class="bg-secondary">Usuarios</a>
      <a href="productos.php">Productos</a>
      <a href="carritos.php">Carritos</a>
      <a href="pedidos.php">Pedidos</a>
      <a href="categorias.php">Categorías</a>
      <a href="resenas.php">Reseñas</a>
      <a href="logout.php" class="text-danger">Cerrar sesión</a>
    </div>

    <!-- Contenido -->
    <div class="col-md-10 p-4">
      <h1 class="mb-4">👥 Usuarios registrados</h1>

      <?php if ($result->num_rows > 0): ?>
        <table class="table table-striped table-hover shadow">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['nombre']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['rol']) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No hay usuarios registrados aún.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
