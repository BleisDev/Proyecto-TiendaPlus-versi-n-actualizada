<?php
include("../backend/conexion.php");
$result = $conexion->query("
  SELECT p.*, c.nombre AS categoria
  FROM productos p
  LEFT JOIN categorias c ON p.categoria_id = c.id
  ORDER BY p.id DESC
");
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Productos</title></head>
<body>
<h1>Productos</h1>
<a href="crear_producto.php">➕ Nuevo producto</a>
<table border="1">
  <tr><th>ID</th><th>Imagen</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Acciones</th></tr>
  <?php while($p = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $p['id'] ?></td>
    <td><?php if($p['imagen']): ?><img src="../web/<?= $p['imagen'] ?>" width="60"><?php endif;?></td>
    <td><?= htmlspecialchars($p['nombre']) ?></td>
    <td>$<?= number_format($p['precio'],2) ?></td>
    <td><?= $p['stock'] ?></td>
    <td><?= $p['categoria'] ?></td>
    <td>
      <a href="editar_producto.php?id=<?= $p['id'] ?>">Editar</a>
      |
      <a href="eliminar_producto.php?id=<?= $p['id'] ?>" onclick="return confirm('Eliminar?')">Eliminar</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
</body>
</html>

