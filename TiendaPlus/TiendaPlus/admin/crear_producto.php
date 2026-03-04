<?php include("conexion.php"); ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $sql = "INSERT INTO productos (nombre, precio) VALUES ('$nombre', '$precio')";
    if ($conn->query($sql)) {
        header("Location: productos.php");
    } else {
        echo "Error al guardar: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Producto</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h2>Crear Producto</h2>
<form method="POST">
  <div class="mb-3">
    <label>Nombre</label>
    <input type="text" name="nombre" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Precio</label>
    <input type="number" name="precio" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-success">Guardar</button>
  <a href="productos.php" class="btn btn-secondary">Volver</a>
</form>

</body>
</html>
