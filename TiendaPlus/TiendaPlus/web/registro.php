<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Conexión a la base de datos
require_once('../backend/conexion.php');

$error = "";
$success = "";

// Procesar formulario de registro
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email_raw = $_POST['email'] ?? '';
    $email = filter_var(trim($email_raw), FILTER_SANITIZE_EMAIL);
    $pass = trim($_POST['pass'] ?? '');

    // Validaciones del servidor
    if (empty($nombre) || empty($email) || empty($pass)) {
        $error = "❌ Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ El correo no tiene un formato válido.";
    } elseif (strlen($pass) < 6) {
        $error = "❌ La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Verificar si el correo ya está registrado
        $sqlCheck = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $sqlCheck->bind_param("s", $email);
        $sqlCheck->execute();
        $resultadoCheck = $sqlCheck->get_result();

        if ($resultadoCheck->num_rows > 0) {
            $error = "❌ El correo '$email' ya está registrado. <a href='login.php'>Inicia sesión aquí</a>";
        } else {
            // Cifrar contraseña y guardar
            $clave_segura = password_hash($pass, PASSWORD_DEFAULT);
            $rol = 'cliente';

            $sql = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            $sql->bind_param("ssss", $nombre, $email, $clave_segura, $rol);

            if ($sql->execute()) {
                $nuevo_id = $conn->insert_id;

                // Iniciar sesión automáticamente
                $_SESSION['usuario_id'] = $nuevo_id;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_rol'] = $rol;

                // Mensaje de éxito
                $success = "✅ ¡Registro exitoso! Bienvenido/a, <strong>$nombre</strong>. Serás redirigido en 2 segundos...";

                // Redirigir después de 2 segundos
                header("refresh:2;url=index.php");
            } else {
                $error = "❌ Error al crear el usuario: " . $conn->error;
            }
        }
        $sqlCheck->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Tienda Plus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .registro-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .registro-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
        }

        .registro-card h2 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }

        .registro-card p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-registro {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px;
            border: none;
            width: 100%;
            transition: transform 0.2s ease;
        }

        .btn-registro:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>

    <div class="registro-container">
        <div class="registro-card">
            <h2>🛍️ Crear Cuenta</h2>
            <p>Únete a Tienda Plus</p>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="formRegistro" novalidate>
                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" placeholder="Ej: María García" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="tu@email.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="pass" class="form-control" placeholder="Mínimo 6 caracteres" required>
                    <small class="text-muted">Debe tener al menos 6 caracteres</small>
                </div>

                <button type="submit" name="registrar" class="btn btn-registro">
                    Registrarme
                </button>

                <div class="login-link">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const form = document.querySelector("#formRegistro");

            form.addEventListener("submit", function (e) {
                const nombre = document.querySelector("input[name='nombre']");
                const email = document.querySelector("input[name='email']");
                const pass = document.querySelector("input[name='pass']");
                let errores = [];

                // Validaciones del cliente
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (nombre.value.trim() === "") {
                    errores.push("⚠️ El nombre es obligatorio.");
                }
                if (email.value.trim() === "") {
                    errores.push("⚠️ El correo es obligatorio.");
                } else if (!emailRegex.test(email.value.trim())) {
                    errores.push("⚠️ El correo no es válido.");
                }
                if (pass.value.trim() === "") {
                    errores.push("⚠️ La contraseña es obligatoria.");
                } else if (pass.value.trim().length < 6) {
                    errores.push("⚠️ La contraseña debe tener al menos 6 caracteres.");
                }

                // Mostrar errores si existen
                if (errores.length > 0) {
                    e.preventDefault();

                    // Remover alerta anterior si existe
                    const alertaAnterior = document.querySelector(".alert-danger");
                    if (alertaAnterior) alertaAnterior.remove();

                    // Crear nueva alerta
                    let alerta = document.createElement("div");
                    alerta.className = "alert alert-danger";
                    alerta.innerHTML = errores.join("<br>");

                    // Insertar antes del formulario
                    form.parentNode.insertBefore(alerta, form);

                    return false;
                }
            });
        });
    </script>

</body>

</html>