<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nosotros - TiendaPlus</title>

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
      padding: 100px 40px;
      text-align: center;
    }

    .hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: 48px;
      margin-bottom: 20px;
    }

    .hero p {
      font-size: 18px;
      color: #ccc;
    }

    /* Content */
    .container {
      max-width: 1200px;
      margin: 80px auto;
      padding: 0 40px;
    }

    .about-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: center;
      margin-bottom: 80px;
    }

    .about-section img {
      width: 100%;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .about-content h2 {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      color: #2c2c2c;
      margin-bottom: 20px;
    }

    .about-content p {
      color: #666;
      line-height: 1.8;
      margin-bottom: 15px;
    }

    /* Values */
    .values {
      background: white;
      padding: 80px 40px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .values h2 {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      text-align: center;
      margin-bottom: 60px;
      color: #2c2c2c;
    }

    .values-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
    }

    .value-card {
      text-align: center;
      padding: 30px;
      border-radius: 15px;
      background: #fafafa;
      transition: all 0.3s;
    }

    .value-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .value-card i {
      font-size: 50px;
      color: #d4a574;
      margin-bottom: 20px;
    }

    .value-card h3 {
      font-size: 22px;
      margin-bottom: 15px;
      color: #2c2c2c;
    }

    .value-card p {
      color: #666;
      line-height: 1.6;
    }

    /* CTA */
    .cta {
      text-align: center;
      padding: 80px 40px;
    }

    .cta h3 {
      font-family: 'Playfair Display', serif;
      font-size: 32px;
      margin-bottom: 20px;
      color: #2c2c2c;
    }

    .cta p {
      color: #666;
      margin-bottom: 30px;
    }

    .btn-primary {
      display: inline-block;
      padding: 15px 40px;
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      text-decoration: none;
      border-radius: 30px;
      font-weight: 600;
      transition: all 0.3s;
      box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
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
        flex-wrap: wrap;
      }

      .about-section {
        grid-template-columns: 1fr;
      }

      .hero h1 {
        font-size: 36px;
      }
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header>
    <div class="header-top">
      <span><i class="fas fa-truck"></i> Envío gratis en compras superiores a $150.000</span>
    </div>

    <div class="header-main">
      <a href="index.php" class="logo">
        Tienda<span>Plus</span>
      </a>

      <nav>
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="catalogo.php"><i class="fas fa-th"></i> Catálogo</a>
        <a href="nosotros.php" class="active"><i class="fas fa-info-circle"></i> Nosotros</a>
        <a href="contactanos.php"><i class="fas fa-envelope"></i> Contacto</a>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero">
    <h1>Conoce TiendaPlus</h1>
    <p>Tu tienda de moda plus size con estilo y elegancia</p>
  </section>

  <!-- About Section -->
  <div class="container">
    <div class="about-section">
      <img src="img/nosotros.jpg" alt="Sobre Nosotros"
        onerror="this.src='https://via.placeholder.com/600x400/d4a574/ffffff?text=TiendaPlus'">

      <div class="about-content">
        <h2>Nuestra Historia</h2>
        <p>
          En <strong>TiendaPlus</strong> comenzamos con una idea simple: ofrecer moda plus size de alta calidad
          que realce la belleza natural de cada mujer. Desde nuestros inicios, nos hemos enfocado en brindar
          una experiencia de compra única, moderna y cercana a nuestros clientes.
        </p>
        <p>
          Hoy somos más que una tienda: somos una comunidad que celebra la autenticidad, el diseño y la pasión
          por la moda inclusiva. Cada prenda está cuidadosamente seleccionada para ofrecerte comodidad, estilo
          y confianza.
        </p>
      </div>
    </div>

    <!-- Values -->
    <div class="values">
      <h2>Nuestros Valores</h2>

      <div class="values-grid">
        <div class="value-card">
          <i class="fas fa-bullseye"></i>
          <h3>Misión</h3>
          <p>Brindar a nuestras clientas productos de alta calidad con una atención cercana y personalizada,
            impulsando la confianza y el estilo propio en cada talla.</p>
        </div>

        <div class="value-card">
          <i class="fas fa-eye"></i>
          <h3>Visión</h3>
          <p>Ser la tienda en línea líder en moda plus size en Colombia, reconocida por su innovación,
            autenticidad y compromiso con la satisfacción del cliente.</p>
        </div>

        <div class="value-card">
          <i class="fas fa-heart"></i>
          <h3>Valores</h3>
          <p>Pasión, confianza, respeto y compromiso. Cada producto y cada cliente son parte fundamental
            de nuestra historia y crecimiento.</p>
        </div>
      </div>
    </div>

    <!-- CTA -->
    <div class="cta">
      <h3>¿Tienes preguntas o sugerencias?</h3>
      <p>Nos encanta escucharte y mejorar cada día</p>
      <a href="contactanos.php" class="btn-primary">
        <i class="fas fa-envelope"></i> Contáctanos
      </a>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p>© 2025 TiendaPlus. Todos los derechos reservados.</p>
  </footer>

</body>

</html>