<?php include("conexion.php"); ?>
<?php
$id = $_GET['id'];
$resultado = $conn->query("SELECT * FROM productos WHERE id=$id");
$producto = $resultado->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $sql = "UPDATE productos SET nombre='$nombre', precio='$precio' WHERE id=$id";
    if ($conn->query($sql)) {
        header("Location: productos.php");
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Producto</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h2>Editar Producto</h2>
<form method="POST">
  <div class="mb-3">
    <label>Nombre</label>
    <input type="text" name="nombre" value="<?= $producto['nombre'] ?>" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Precio</label>
    <input type="number" name="precio" value="<?= $producto['precio'] ?>" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">Actualizar</button>
  <a href="productos.php" class="btn btn-secondary">Volver</a>
</form>

</body>
</html>
