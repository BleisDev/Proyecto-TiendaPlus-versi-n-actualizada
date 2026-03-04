<?php
include("../backend/conexion.php"); // Asegúrate de que esta ruta sea correcta

// --- Crear categoría ---
if (isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO categorias (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
    if ($conn->query($sql)) {
        echo "<script>alert('Categoría agregada correctamente');</script>";
    } else {
        echo "<script>alert('Error al agregar categoría');</script>";
    }
}

// --- Editar categoría ---
if (isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "UPDATE categorias SET nombre='$nombre', descripcion='$descripcion' WHERE id=$id";
    if ($conn->query($sql)) {
        echo "<script>alert('Categoría actualizada correctamente');</script>";
    } else {
        echo "<script>alert('Error al actualizar categoría');</script>";
    }
}

// --- Eliminar categoría ---
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conn->query("DELETE FROM categorias WHERE id=$id");
    echo "<script>alert('Categoría eliminada'); window.location='categorias.php';</script>";
}

// --- Consultar todas las categorías ---
$resultado = $conn->query("SELECT * FROM categorias");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Categorías</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        h1 { text-align: center; color: #333; }
        form { background: white; padding: 20px; margin-bottom: 30px; border-radius: 10px; width: 400px; margin: auto; }
        table { width: 80%; margin: auto; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #e91e63; color: white; }
        tr:hover { background: #f1f1f1; }
        input[type="text"], textarea { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #e91e63; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #c2185b; }
    </style>
</head>
<body>

<h1>Gestión de Categorías</h1>

<!-- Formulario para agregar nueva categoría -->
<form method="POST" action="">
    <input type="hidden" name="accion" value="crear">
    <label>Nombre:</label>
    <input type="text" name="nombre" required>
    <label>Descripción:</label>
    <textarea name="descripcion" required></textarea>
    <button type="submit">Agregar Categoría</button>
</form>

<!-- Tabla de categorías existentes -->
<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Acciones</th>
    </tr>

    <?php while ($fila = $resultado->fetch_assoc()) { ?>
    <tr>
        <td><?= $fila['id'] ?></td>
        <td><?= $fila['nombre'] ?></td>
        <td><?= $fila['descripcion'] ?></td>
        <td>
            <!-- Botón Editar -->
            <form method="POST" action="" style="display:inline-block;">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" value="<?= $fila['id'] ?>">
                <input type="text" name="nombre" value="<?= $fila['nombre'] ?>" required>
                <input type="text" name="descripcion" value="<?= $fila['descripcion'] ?>" required>
                <button type="submit">Guardar</button>
            </form>

            <!-- Botón Eliminar -->
            <a href="categorias.php?eliminar=<?= $fila['id'] ?>" onclick="return confirm('¿Eliminar esta categoría?')">
                <button>Eliminar</button>
            </a>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>
