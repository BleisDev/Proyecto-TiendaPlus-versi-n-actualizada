<?php
session_start();
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ayuda - TiendaPlus</title>

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

    /* Container */
    .container {
      max-width: 1000px;
      margin: 60px auto;
      padding: 0 40px;
    }

    .tabs {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-bottom: 40px;
    }

    .tab-btn {
      padding: 12px 30px;
      background: white;
      border: 2px solid #e0e0e0;
      border-radius: 30px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      color: #2c2c2c;
    }

    .tab-btn.active {
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      border-color: #d4a574;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
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

    /* FAQ */
    .faq-list {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    }

    .faq-item {
      border-bottom: 1px solid #f0f0f0;
      padding: 20px 0;
      cursor: pointer;
      transition: all 0.3s;
    }

    .faq-item:last-child {
      border-bottom: none;
    }

    .faq-item:hover {
      background: #fafafa;
      padding-left: 10px;
    }

    .faq-question {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
      color: #2c2c2c;
    }

    .faq-question i {
      color: #d4a574;
      transition: transform 0.3s;
    }

    .faq-item.open .faq-question i {
      transform: rotate(180deg);
    }

    .faq-answer {
      display: none;
      padding-top: 15px;
      color: #666;
      line-height: 1.6;
    }

    .faq-item.open .faq-answer {
      display: block;
    }

    /* Form */
    .help-form {
      background: white;
      border-radius: 15px;
      padding: 40px;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    }

    .help-form h3 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      margin-bottom: 20px;
      color: #2c2c2c;
    }

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

    /* WhatsApp */
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

      .tabs {
        flex-direction: column;
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
      <span><i class="fas fa-question-circle"></i> ¿Necesitas ayuda? Estamos aquí para ti</span>
    </div>

    <div class="header-main">
      <a href="index.php" class="logo">
        Tienda<span>Plus</span>
      </a>

      <nav>
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="catalogo.php"><i class="fas fa-th"></i> Catálogo</a>
        <a href="nosotros.php"><i class="fas fa-info-circle"></i> Nosotros</a>
        <a href="ayuda.php" class="active"><i class="fas fa-question-circle"></i> Ayuda</a>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero">
    <h1><i class="fas fa-life-ring"></i> Centro de Ayuda</h1>
    <p>Encuentra respuestas a tus preguntas o contáctanos</p>
  </section>

  <!-- Container -->
  <div class="container">

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn active" onclick="showTab('faq')">
        <i class="fas fa-question-circle"></i> Preguntas Frecuentes
      </button>
      <button class="tab-btn" onclick="showTab('form')">
        <i class="fas fa-envelope"></i> Formulario de Ayuda
      </button>
    </div>

    <!-- FAQ Tab -->
    <div id="faq" class="tab-content active">
      <div class="faq-list">
        <div class="faq-item" onclick="toggleFaq(this)">
          <div class="faq-question">
            <span>¿Qué tallas manejan?</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Ofrecemos tallas desde L hasta 5XL. Cada producto tiene una guía de tallas específica
            con medidas exactas para que encuentres tu talla perfecta.
          </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
          <div class="faq-question">
            <span>¿Puedo cambiar o devolver un producto?</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Sí, aceptamos cambios y devoluciones dentro de los 30 días posteriores a la entrega.
            El producto debe estar en perfecto estado, sin uso y con todas sus etiquetas.
          </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
          <div class="faq-question">
            <span>¿Cuánto tarda el envío?</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Los envíos tardan entre 2 y 5 días hábiles según la ciudad de destino.
            Recibirás un código de seguimiento cuando tu pedido sea despachado.
          </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
          <div class="faq-question">
            <span>¿Cómo puedo rastrear mi pedido?</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Una vez despachado tu pedido, recibirás un email con el número de guía.
            Puedes rastrearlo en la página de la transportadora o contactarnos para ayudarte.
          </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
          <div class="faq-question">
            <span>¿Qué métodos de pago aceptan?</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Aceptamos tarjetas de crédito, débito, PSE y Mercado Pago.
            Todos los pagos son procesados de forma segura y encriptada.
          </div>
        </div>

        <div class="faq-item" onclick="toggleFaq(this)">
          <div class="faq-question">
            <span>¿Tienen tienda física?</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="faq-answer">
            Actualmente somos una tienda 100% en línea, lo que nos permite ofrecerte
            mejores precios y una experiencia de compra cómoda desde tu hogar.
          </div>
        </div>
      </div>
    </div>

    <!-- Form Tab -->
    <div id="form" class="tab-content">
      <div class="help-form">
        <h3>Formulario de Ayuda</h3>
        <p style="color: #666; margin-bottom: 30px;">
          ¿No encontraste la respuesta que buscabas? Envíanos tu consulta y te responderemos pronto.
        </p>

        <form method="post" action="#">
          <div class="form-group">
            <label for="nombre">Nombre Completo *</label>
            <input type="text" id="nombre" name="nombre" required>
          </div>

          <div class="form-group">
            <label for="correo">Correo Electrónico *</label>
            <input type="email" id="correo" name="correo" required>
          </div>

          <div class="form-group">
            <label for="pedido">Número de Pedido (opcional)</label>
            <input type="text" id="pedido" name="pedido" placeholder="Ej: #12345">
          </div>

          <div class="form-group">
            <label for="consulta">Tu Consulta *</label>
            <textarea id="consulta" name="consulta" required></textarea>
          </div>

          <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane"></i> Enviar Consulta
          </button>
        </form>
      </div>
    </div>

  </div>

  <!-- WhatsApp Float -->
  <a href="https://wa.me/573001234567" target="_blank" class="whatsapp-float" title="WhatsApp">
    <i class="fab fa-whatsapp"></i>
  </a>

  <!-- Footer -->
  <footer>
    <p>© 2025 TiendaPlus. Todos los derechos reservados.</p>
  </footer>

  <script>
    function showTab(tabId) {
      // Hide all tabs
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
      });

      // Remove active from all buttons
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });

      // Show selected tab
      document.getElementById(tabId).classList.add('active');
      event.target.classList.add('active');
    }

    function toggleFaq(element) {
      element.classList.toggle('open');
    }
  </script>

</body>

</html>