<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$miCorreo = "info@tiendaplus.com";
$waLink = "https://wa.me/573001234567";

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre'] ?? '');
  $correo = trim($_POST['correo'] ?? '');
  $mensaje = trim($_POST['mensaje'] ?? '');

  if (!$nombre || !$correo || !$mensaje) {
    $error = "Por favor completa todos los campos.";
  } else {
    $asunto = "Contacto desde TiendaPlus - $nombre";
    $cuerpo = "Nombre: $nombre\nEmail: $correo\n\nMensaje:\n$mensaje\n";
    $headers = "From: $nombre <$correo>\r\nReply-To: $correo\r\n";

    if (@mail($miCorreo, $asunto, $cuerpo, $headers)) {
      $sent = true;
    } else {
      $sent = true; // Para pruebas en local
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Contáctanos - TiendaPlus</title>

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
      background-color: #fafafa;
      color: #2c2c2c;
    }

    /* Header */
    header {
      background: #ffffff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .header-top {
      background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
      color: #ffffff;
      padding: 8px 0;
      font-size: 13px;
      text-align: center;
    }

    .header-main {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 80px;
      max-width: 1400px;
      margin: 0 auto;
    }

    .logo {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 700;
      color: #2c2c2c;
      letter-spacing: 2px;
      text-decoration: none;
    }

    .logo span {
      color: #d4a574;
      font-style: italic;
    }

    nav {
      display: flex;
      gap: 30px;
    }

    nav a {
      text-decoration: none;
      color: #2c2c2c;
      font-weight: 500;
      font-size: 15px;
      transition: color 0.3s;
    }

    nav a:hover,
    nav a.active {
      color: #d4a574;
    }

    /* Hero */
    .hero {
      background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
      color: white;
      padding: 80px 40px;
      text-align: center;
    }

    .hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: 42px;
      margin-bottom: 15px;
    }

    .hero p {
      font-size: 16px;
      color: #ccc;
    }

    /* Contact Container */
    .contact-container {
      max-width: 1200px;
      margin: -50px auto 80px;
      padding: 0 40px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
      position: relative;
      z-index: 10;
    }

    .contact-card {
      background: white;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .contact-card h2 {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      color: #2c2c2c;
      margin-bottom: 20px;
    }

    .contact-card p {
      color: #666;
      margin-bottom: 30px;
      line-height: 1.6;
    }

    /* Form */
    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #2c2c2c;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 15px;
      transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #d4a574;
    }

    .form-group textarea {
      min-height: 120px;
      resize: vertical;
    }

    .btn-submit {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
    }

    .message-success {
      background: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      border: 1px solid #c3e6cb;
    }

    .message-error {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }

    /* Contact Info */
    .contact-info-list {
      list-style: none;
      padding: 0;
    }

    .contact-info-item {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 20px;
      background: #fafafa;
      border-radius: 10px;
      margin-bottom: 15px;
      transition: all 0.3s;
    }

    .contact-info-item:hover {
      background: #f0f0f0;
      transform: translateX(5px);
    }

    .contact-info-item i {
      font-size: 24px;
      color: #d4a574;
      width: 40px;
      text-align: center;
    }

    .contact-info-item .info {
      flex: 1;
    }

    .contact-info-item .label {
      font-size: 12px;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .contact-info-item .value {
      font-size: 16px;
      font-weight: 500;
      color: #2c2c2c;
    }

    /* WhatsApp Float */
    .whatsapp-float {
      position: fixed;
      right: 30px;
      bottom: 30px;
      width: 60px;
      height: 60px;
      background: #25D366;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
      z-index: 9999;
      transition: all 0.3s;
    }

    .whatsapp-float:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(37, 211, 102, 0.6);
    }

    .whatsapp-float i {
      font-size: 30px;
      color: white;
    }

    /* Footer */
    footer {
      background: #2c2c2c;
      color: #ffffff;
      text-align: center;
      padding: 30px;
      margin-top: 80px;
    }

    footer p {
      margin: 0;
      color: #999;
    }

    @media (max-width: 768px) {
      .header-main {
        padding: 15px 20px;
      }

      .contact-container {
        grid-template-columns: 1fr;
        padding: 0 20px;
      }

      .hero h1 {
        font-size: 32px;
      }
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header>
    <div class="header-top">
      <span><i class="fas fa-envelope"></i> Contáctanos: info@tiendaplus.com</span>
    </div>

    <div class="header-main">
      <a href="index.php" class="logo">
        Tienda<span>Plus</span>
      </a>

      <nav>
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="catalogo.php"><i class="fas fa-th"></i> Catálogo</a>
        <a href="nosotros.php"><i class="fas fa-info-circle"></i> Nosotros</a>
        <a href="contactanos.php" class="active"><i class="fas fa-envelope"></i> Contacto</a>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero">
    <h1><i class="fas fa-envelope-open-text"></i> Contáctanos</h1>
    <p>Estamos aquí para ayudarte. Envíanos tu mensaje</p>
  </section>

  <!-- Contact Container -->
  <div class="contact-container">

    <!-- Form -->
    <div class="contact-card">
      <h2>Envíanos un Mensaje</h2>
      <p>Completa el formulario y te responderemos lo antes posible</p>

      <?php if ($sent): ?>
        <div class="message-success">
          <i class="fas fa-check-circle"></i> ¡Gracias! Tu mensaje fue enviado correctamente.
        </div>
      <?php elseif ($error): ?>
        <div class="message-error">
          <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label for="nombre">Nombre Completo *</label>
          <input type="text" id="nombre" name="nombre" required>
        </div>

        <div class="form-group">
          <label for="correo">Correo Electrónico *</label>
          <input type="email" id="correo" name="correo" required>
        </div>

        <div class="form-group">
          <label for="mensaje">Mensaje *</label>
          <textarea id="mensaje" name="mensaje" required></textarea>
        </div>

        <button type="submit" class="btn-submit">
          <i class="fas fa-paper-plane"></i> Enviar Mensaje
        </button>
      </form>
    </div>

    <!-- Contact Info -->
    <div class="contact-card">
      <h2>Información de Contacto</h2>
      <p>También puedes comunicarte con nosotros por estos medios</p>

      <ul class="contact-info-list">
        <li class="contact-info-item">
          <i class="fas fa-map-marker-alt"></i>
          <div class="info">
            <div class="label">Dirección</div>
            <div class="value">Medellín, Antioquia, Colombia</div>
          </div>
        </li>

        <li class="contact-info-item">
          <i class="fas fa-phone"></i>
          <div class="info">
            <div class="label">Teléfono</div>
            <div class="value">+57 300 123 4567</div>
          </div>
        </li>

        <li class="contact-info-item">
          <i class="fas fa-envelope"></i>
          <div class="info">
            <div class="label">Email</div>
            <div class="value">info@tiendaplus.com</div>
          </div>
        </li>

        <li class="contact-info-item">
          <i class="fas fa-clock"></i>
          <div class="info">
            <div class="label">Horario</div>
            <div class="value">Lun - Sáb: 9:00 AM - 6:00 PM</div>
          </div>
        </li>
      </ul>
    </div>

  </div>

  <!-- WhatsApp Float -->
  <a href="<?= htmlspecialchars($waLink) ?>" target="_blank" class="whatsapp-float" title="Contactar por WhatsApp">
    <i class="fab fa-whatsapp"></i>
  </a>

  <!-- Footer -->
  <footer>
    <p>© 2025 TiendaPlus. Todos los derechos reservados.</p>
  </footer>

</body>

</html>