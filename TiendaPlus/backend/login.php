<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Conexión segura y comprobar ruta
$conexionRuta = realpath(__DIR__ . '/../backend/conexion.php');
if (!$conexionRuta) {
    die("❌ ERROR: No se encuentra conexion.php");
}
include_once($conexionRuta);

// Si enviaron el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === "" || $password === "") {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {
        $sql = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $sql->bind_param("s", $email);
        $sql->execute();
        $result = $sql->get_result();

        if ($result && $result->num_rows > 0) {
            $usuario = $result->fetch_assoc();

            if (password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];

                // Redirige correctamente
                header("Location: index.php");
                exit;
            } else {
                $error = "❌ Contraseña incorrecta.";
            }
        } else {
            $error = "❌ Este correo no está registrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5" style="max-width:420px;">
        <h3 class="text-center mb-4">🔐 Iniciar Sesión</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Correo</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Ingresar</button>

            <p class="text-center mt-3">
                ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
            </p>
        </form>
    </div>

</body>

</html>