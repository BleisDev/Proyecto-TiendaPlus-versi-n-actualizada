<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Conexión segura
$conexionRuta = realpath(__DIR__ . '/../backend/conexion.php');
if (!$conexionRuta) {
    die("❌ ERROR: No se encuentra conexion.php");
}
include_once($conexionRuta);

$errorLogin = "";
$errorRegistro = "";
$successRegistro = "";
$mostrarFormulario = 'login'; // Por defecto mostrar login

// --- PROCESAR LOGIN ---
if (isset($_POST['login'])) {
    $correo = trim($_POST['correo'] ?? '');
    $pass = trim($_POST['pass'] ?? '');

    if (empty($correo) || empty($pass)) {
        $errorLogin = "Todos los campos son obligatorios.";
    } else {
        // Buscar usuario por email
        $sql = $conn->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $sql->bind_param("s", $correo);
        $sql->execute();
        $res = $sql->get_result();

        if ($res->num_rows > 0) {
            $usuario = $res->fetch_assoc();

            // Verificar contraseña
            $passwordValido = false;

            // Verificar si la contraseña está hasheada o en texto plano
            if (password_verify($pass, $usuario['password'])) {
                // Contraseña hasheada correcta
                $passwordValido = true;
            } elseif ($pass === $usuario['password']) {
                // Contraseña en texto plano (para compatibilidad con datos antiguos)
                $passwordValido = true;
            }

            if ($passwordValido) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['nombre'] = $usuario['nombre']; // Compatibilidad
                $_SESSION['rol'] = $usuario['rol']; // Compatibilidad

                // Redirigir según rol
                if ($usuario['rol'] === 'admin' || $usuario['rol'] === 'administrador') {
                    header("Location: ../backend/panel.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $errorLogin = "Contraseña incorrecta.";
            }
        } else {
            $errorLogin = "No existe una cuenta con este correo.";
        }
        $sql->close();
    }
}

// --- PROCESAR REGISTRO ---
if (isset($_POST['accion']) && $_POST['accion'] === 'registro') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $pass = trim($_POST['pass'] ?? '');

    $mostrarFormulario = 'registro'; // Mantener en tab de registro

    // Validaciones
    if (empty($nombre) || empty($correo) || empty($pass)) {
        $errorRegistro = "Todos los campos son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errorRegistro = "El correo no tiene un formato válido.";
    } elseif (strlen($pass) < 6) {
        $errorRegistro = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Verificar si el correo ya existe
        $sqlCheck = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $sqlCheck->bind_param("s", $correo);
        $sqlCheck->execute();
        $resultCheck = $sqlCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            $errorRegistro = "El correo '$correo' ya está registrado.";
            $mostrarFormulario = 'registro';
        } else {
            // Registrar usuario
            $passHash = password_hash($pass, PASSWORD_DEFAULT);
            $rol = 'cliente';

            $sql = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            $sql->bind_param("ssss", $nombre, $correo, $passHash, $rol);

            if ($sql->execute()) {
                $nuevo_id = $conn->insert_id;

                // Iniciar sesión automáticamente
                $_SESSION['usuario_id'] = $nuevo_id;
                $_SESSION['usuario_nombre'] = $nombre;
                $_SESSION['usuario_rol'] = $rol;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['rol'] = $rol;

                // Mensaje de éxito simple y claro
                $successRegistro = "¡Registro exitoso! Bienvenido/a <strong>$nombre</strong>. Redirigiendo a la tienda...";
                header("refresh:2;url=index.php");
            } else {
                $errorRegistro = "Error al crear el usuario: " . $conn->error;
            }
            $sql->close();
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
    <title>Acceder - TiendaPlus</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            max-width: 480px;
            width: 100%;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #2c2c2c;
            margin-bottom: 10px;
        }

        .logo h1 span {
            color: #d4a574;
            font-style: italic;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .tabs button {
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            color: #999;
            padding: 12px 30px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s ease;
        }

        .tabs button.active {
            color: #d4a574;
            border-bottom-color: #d4a574;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c2c2c;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #d4a574;
            box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.1);
        }

        .form-group small {
            color: #999;
            font-size: 12px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #d4a574;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .back-link a:hover {
            gap: 10px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 40px 30px;
            }

            .logo h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>Tienda<span>Plus</span></h1>
                <p>Accede a tu cuenta o regístrate</p>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="<?= $mostrarFormulario === 'login' ? 'active' : '' ?>" onclick="cambiarTab('login')"
                    id="tabLogin">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
                <button class="<?= $mostrarFormulario === 'registro' ? 'active' : '' ?>"
                    onclick="cambiarTab('registro')" id="tabRegistro">
                    <i class="fas fa-user-plus"></i> Registrarse
                </button>
            </div>

            <!-- Formulario de Login -->
            <div id="formLogin" class="form-section <?= $mostrarFormulario === 'login' ? 'active' : '' ?>">
                <?php if (!empty($errorLogin)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $errorLogin ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="correo-login"><i class="fas fa-envelope"></i> Correo electrónico</label>
                        <input type="email" id="correo-login" name="correo" placeholder="tu@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="pass-login"><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" id="pass-login" name="pass" placeholder="Tu contraseña" required>
                    </div>

                    <button type="submit" name="login" class="btn-submit">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </form>
            </div>

            <!-- Formulario de Registro -->
            <div id="formRegistro" class="form-section <?= $mostrarFormulario === 'registro' ? 'active' : '' ?>">
                <?php if (!empty($successRegistro)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $successRegistro ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorRegistro)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $errorRegistro ?>
                    </div>
                <?php endif; ?>

                <form method="POST" autocomplete="off">
                    <div class="form-group">
                        <label for="nombre-registro"><i class="fas fa-user"></i> Nombre completo</label>
                        <input type="text" id="nombre-registro" name="nombre"
                            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" placeholder="Ej: María García"
                            autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="correo-registro"><i class="fas fa-envelope"></i> Correo electrónico</label>
                        <input type="email" id="correo-registro" name="correo"
                            value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" placeholder="tu@email.com"
                            autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label for="pass-registro"><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" id="pass-registro" name="pass" placeholder="Mínimo 6 caracteres"
                            autocomplete="new-password" required>
                        <small>Debe tener al menos 6 caracteres</small>
                    </div>

                    <input type="hidden" name="accion" value="registro">

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> Crear Cuenta
                    </button>
                </form>
            </div>

            <!-- Link para volver -->
            <div class="back-link">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i> Volver a la tienda
                </a>
            </div>
        </div>
    </div>

    <script>
        function cambiarTab(tipo) {
            const formLogin = document.getElementById('formLogin');
            const formRegistro = document.getElementById('formRegistro');
            const tabLogin = document.getElementById('tabLogin');
            const tabRegistro = document.getElementById('tabRegistro');

            if (tipo === 'login') {
                formLogin.classList.add('active');
                formRegistro.classList.remove('active');
                tabLogin.classList.add('active');
                tabRegistro.classList.remove('active');
            } else {
                formLogin.classList.remove('active');
                formRegistro.classList.add('active');
                tabLogin.classList.remove('active');
                tabRegistro.classList.add('active');
            }
        }

        // Contador regresivo para redirección después de registro exitoso
        window.addEventListener('DOMContentLoaded', function () {
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                let seconds = 3;
                const interval = setInterval(function () {
                    seconds--;
                    if (seconds > 0) {
                        countdownElement.textContent = seconds;
                    } else {
                        clearInterval(interval);
                        countdownElement.textContent = '0';
                    }
                }, 1000);
            }
        });
    </script>

</body>

</html>