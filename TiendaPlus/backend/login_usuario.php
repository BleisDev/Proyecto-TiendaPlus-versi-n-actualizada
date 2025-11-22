<?php
include("conexion.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id']  = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];

            echo "✅ Bienvenido, " . $usuario['nombre'];
            // Redirigir al home o perfil
            // header("Location: ../web/index.php");
        } else {
            echo "❌ Contraseña incorrecta";
        }
    } else {
        echo "❌ No existe un usuario con ese correo";
    }

    $stmt->close();
    $conn->close();
}
?>
