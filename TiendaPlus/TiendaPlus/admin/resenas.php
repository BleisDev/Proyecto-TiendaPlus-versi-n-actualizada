<?php
// admin/resenas.php
session_start();
include("../backend/conexion.php");

// Opcional: proteger que solo admin entre
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../web/login.php");
    exit;
}

$sql = "SELECT r.id, r.comentario, r.calificacion, r.fecha, u.nombre AS usuario, p.nombre AS producto 
        FROM resenas r
        LEFT JOIN usuarios u ON r.usuario_id = u.id
        LEFT JOIN productos p ON r.producto_id = p.id
        ORDER BY r.fecha DESC";
$result = $conn->query($sql);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reseñas - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-4">Reseñas de clientes</h1>

    <table class="table table-striped table-hover">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Usuario</th>
          <th>Producto</th>
          <th>Calificación</th>
          <th>Comentario</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while($r = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['usuario'] ?: '—') ?></td>
              <td><?= htmlspecialchars($r['producto'] ?: '—') ?></td>
              <td><?= str_repeat('★', max(1,intval($r['calificacion']))) ?></td>
              <td><?= nl2br(htmlspecialchars($r['comentario'])) ?></td>
              <td><?= $r['fecha'] ?></td>
              <td>
                <form method="POST" action="../backend/eliminar_resena.php" onsubmit="return confirm('Eliminar reseña?');">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">No hay reseñas registradas.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
