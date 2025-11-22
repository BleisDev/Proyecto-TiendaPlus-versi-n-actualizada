<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
$conexion = new mysqli("localhost", "root", "", "TiendaPlus");

if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para mostrar productos destacados
$query = "SELECT * FROM productos WHERE destacado = 1 LIMIT 8";
$resultado = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TiendaPlus - Moda Plus Size Elegante</title>

  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
    rel="stylesheet">

  <!-- Font Awesome -->
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
      line-height: 1.6;
    }

    /* ============================================
       HEADER
    ============================================ */
    header {
      background: #ffffff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 0;
      z-index: 1000;
      transition: all 0.3s ease;
    }

    .header-top {
      background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
      color: #ffffff;
      padding: 8px 0;
      font-size: 13px;
      text-align: center;
    }

    .header-top a {
      color: #ffffff;
      text-decoration: none;
      margin: 0 15px;
      transition: color 0.3s;
    }

    .header-top a:hover {
      color: #d4a574;
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
      font-size: 32px;
      font-weight: 700;
      color: #2c2c2c;
      letter-spacing: 2px;
      text-transform: uppercase;
      position: relative;
    }

    .logo span {
      color: #d4a574;
      font-style: italic;
    }

    .logo::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 60px;
      height: 2px;
      background: linear-gradient(90deg, #d4a574, transparent);
    }

    nav {
      display: flex;
      gap: 35px;
    }

    nav a {
      text-decoration: none;
      color: #2c2c2c;
      font-weight: 500;
      font-size: 15px;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      position: relative;
    }

    nav a::after {
      content: '';
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 0;
      height: 2px;
      background: #d4a574;
      transition: width 0.3s ease;
    }

    nav a:hover {
      color: #d4a574;
    }

    nav a:hover::after {
      width: 100%;
    }

    .header-icons {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .header-icons a,
    .header-icons .user-info {
      color: #2c2c2c;
      font-size: 20px;
      text-decoration: none;
      transition: all 0.3s ease;
      position: relative;
    }

    .header-icons a:hover {
      color: #d4a574;
      transform: translateY(-2px);
    }

    /* User Avatar with Initials */
    .user-dropdown {
      position: relative;
      display: inline-block;
    }


    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(212, 165, 116, 0.3);
    }

    .user-avatar:hover {
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(212, 165, 116, 0.5);
    }

    /* Avatar link wrapper */
    .user-dropdown>a {
      display: inline-block;
      line-height: 0;
    }

    .user-dropdown>a .user-avatar {
      margin: 0;
    }


    /* Dropdown Menu */
    .dropdown-menu {
      position: absolute;
      top: 50px;
      right: 0;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      min-width: 220px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(-10px);
      transition: all 0.3s ease;
      z-index: 1000;
    }

    .user-dropdown:hover .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    .dropdown-header {
      padding: 15px;
      border-bottom: 1px solid #f0f0f0;
      color: #2c2c2c;
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      color: #2c2c2c;
      text-decoration: none;
      transition: all 0.2s ease;
      font-size: 14px;
    }

    .dropdown-item:hover {
      background: #f8f9fa;
      color: #d4a574;
    }

    .dropdown-item i {
      width: 20px;
      font-size: 16px;
    }

    .logout-item {
      border-top: 1px solid #f0f0f0;
      color: #e74c3c;
    }

    .logout-item:hover {
      background: #fee;
      color: #c0392b;
    }

    /* ============================================
       HERO BANNER
    ============================================ */
    .hero {
      position: relative;
      height: 600px;
      background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
        url('img/banner1.jpg') center/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      overflow: hidden;
    }

    .hero-content {
      max-width: 700px;
      padding: 40px;
      animation: fadeInUp 1s ease;
    }

    .hero-content h1 {
      font-family: 'Playfair Display', serif;
      font-size: 56px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.2;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero-content p {
      font-size: 20px;
      margin-bottom: 30px;
      font-weight: 300;
      letter-spacing: 1px;
    }

    .btn-primary {
      display: inline-block;
      padding: 15px 40px;
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      text-decoration: none;
      border-radius: 30px;
      font-weight: 600;
      letter-spacing: 1px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ============================================
       FEATURES SECTION
    ============================================ */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: -50px auto 80px;
      padding: 0 40px;
      position: relative;
      z-index: 10;
    }

    .feature-card {
      background: white;
      padding: 30px;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }

    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .feature-card i {
      font-size: 40px;
      color: #d4a574;
      margin-bottom: 15px;
    }

    .feature-card h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #2c2c2c;
    }

    .feature-card p {
      font-size: 14px;
      color: #666;
      line-height: 1.6;
    }

    /* ============================================
       PRODUCTOS DESTACADOS
    ============================================ */
    .productos {
      max-width: 1200px;
      margin: 0 auto;
      padding: 80px 40px;
    }

    .section-header {
      text-align: center;
      margin-bottom: 60px;
    }

    .section-header h2 {
      font-family: 'Playfair Display', serif;
      font-size: 42px;
      font-weight: 700;
      color: #2c2c2c;
      margin-bottom: 15px;
    }

    .section-header p {
      font-size: 16px;
      color: #666;
      max-width: 600px;
      margin: 0 auto;
    }

    .productos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 35px;
    }

    .producto-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .producto-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .producto-image {
      position: relative;
      overflow: hidden;
      height: 350px;
    }

    .producto-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .producto-card:hover .producto-image img {
      transform: scale(1.1);
    }

    .producto-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: #d4a574;
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .producto-info {
      padding: 25px;
    }

    .producto-info h3 {
      font-size: 18px;
      font-weight: 600;
      color: #2c2c2c;
      margin-bottom: 10px;
      min-height: 50px;
    }

    .producto-price {
      font-size: 24px;
      font-weight: 700;
      color: #d4a574;
      margin-bottom: 15px;
    }

    .producto-actions {
      display: flex;
      gap: 10px;
    }

    .btn-ver {
      flex: 1;
      padding: 12px;
      background: #2c2c2c;
      color: white;
      text-align: center;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .btn-ver:hover {
      background: #d4a574;
    }

    /* ============================================
       FOOTER
    ============================================ */
    footer {
      background: #2c2c2c;
      color: #ffffff;
      padding: 60px 40px 30px;
      margin-top: 80px;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      margin-bottom: 40px;
    }

    .footer-section h3 {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      margin-bottom: 20px;
      color: #d4a574;
    }

    .footer-section p,
    .footer-section a {
      color: #ccc;
      text-decoration: none;
      font-size: 14px;
      line-height: 2;
      transition: color 0.3s;
    }

    .footer-section a:hover {
      color: #d4a574;
    }

    .footer-social {
      display: flex;
      gap: 15px;
      margin-top: 15px;
    }

    .footer-social a {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;
    }

    .footer-social a:hover {
      background: #d4a574;
      transform: translateY(-3px);
    }

    .footer-bottom {
      text-align: center;
      padding-top: 30px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      color: #999;
      font-size: 14px;
    }

    /* ============================================
       RESPONSIVE
    ============================================ */
    @media (max-width: 768px) {
      .header-main {
        padding: 15px 20px;
        flex-wrap: wrap;
      }

      nav {
        order: 3;
        width: 100%;
        margin-top: 15px;
        justify-content: center;
        gap: 15px;
      }

      .hero-content h1 {
        font-size: 36px;
      }

      .productos {
        padding: 40px 20px;
      }

      .section-header h2 {
        font-size: 32px;
      }
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header>
    <div class="header-top">
      <span><i class="fas fa-truck"></i> Envío gratis en compras superiores a $150.000</span>
      <a href="tel:+573001234567"><i class="fas fa-phone"></i> +57 300 123 4567</a>
    </div>

    <div class="header-main">
      <div class="logo">
        Tienda<span>Plus</span>
      </div>

      <nav>
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="catalogo.php"><i class="fas fa-th"></i> Catálogo</a>
        <a href="nosotros.php"><i class="fas fa-info-circle"></i> Nosotros</a>
        <a href="contactanos.php"><i class="fas fa-envelope"></i> Contacto</a>
        <?php
        // Verificar si el usuario ha iniciado sesión
        if (isset($_SESSION["usuario_id"]) && isset($_SESSION["usuario_nombre"])) {
          $nombre = $_SESSION["usuario_nombre"];
          $rol = $_SESSION["usuario_rol"] ?? 'cliente';

          // Obtener iniciales del nombre
          $palabras = explode(" ", $nombre);
          $iniciales = "";

          // Si hay más de una palabra, tomar la primera letra de cada una
          if (count($palabras) > 1) {
            foreach ($palabras as $palabra) {
              if (!empty($palabra)) {
                $iniciales .= strtoupper(mb_substr($palabra, 0, 1));
              }
            }
          } else {
            // Si solo hay una palabra, tomar las primeras 2 letras
            if (strlen($nombre) > 1) {
              $iniciales = strtoupper(mb_substr($nombre, 0, 2));
            } else {
              $iniciales = strtoupper($nombre);
            }
          }
        }
        ?>
      </nav>

      <div class="header-icons">
        <?php if (isset($_SESSION["usuario_id"])): ?>
          <div class="user-dropdown">
            <?php if ($rol == 'admin' || $rol == 'administrador'): ?>
              <a href="../backend/panel.php" style="text-decoration: none;">
                <div class="user-avatar" title="<?= htmlspecialchars($nombre) ?> - Ir al Panel Admin">
                  <?= $iniciales ?>
                </div>
              </a>
            <?php else: ?>
              <div class="user-avatar" title="<?= htmlspecialchars($nombre) ?>">
                <?= $iniciales ?>
              </div>
            <?php endif; ?>

            <div class="dropdown-menu">
              <div class="dropdown-header">
                <strong><?= htmlspecialchars($nombre) ?></strong>
                <div style="font-size: 12px; color: #888; margin-top: 3px;">
                  <?= ($rol == 'admin' || $rol == 'administrador') ? 'Administrador' : 'Cliente' ?>
                </div>
              </div>

              <?php if ($rol == 'admin' || $rol == 'administrador'): ?>
                <a href="../backend/panel.php" class="dropdown-item">
                  <i class="fas fa-tachometer-alt"></i> Panel Administrador
                </a>
              <?php else: ?>
                <a href="catalogo.php" class="dropdown-item">
                  <i class="fas fa-shopping-bag"></i> Mis Compras
                </a>
                <a href="resenas.php" class="dropdown-item">
                  <i class="fas fa-star"></i> Mis Reseñas
                </a>
                <a href="carrito.php" class="dropdown-item">
                  <i class="fas fa-shopping-cart"></i> Mi Carrito
                </a>
              <?php endif; ?>

              <a href="../backend/logout.php" class="dropdown-item logout-item">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" title="Mi cuenta"><i class="fas fa-user"></i></a>
        <?php endif; ?>
        <a href="resenas.php" title="Favoritos"><i class="fas fa-heart"></i></a>
        <a href="carrito.php" title="Carrito"><i class="fas fa-shopping-bag"></i></a>
      </div>
    </div>
  </header>

  <!-- Hero Banner -->
  <section class="hero">
    <div class="hero-content">
      <h1>Moda Plus Size con Estilo</h1>
      <p>Descubre la elegancia en cada talla. Ropa diseñada para realzar tu belleza natural.</p>
      <a href="catalogo.php" class="btn-primary">Explorar Colección</a>
    </div>
  </section>

  <!-- Features -->
  <section class="features">
    <div class="feature-card">
      <i class="fas fa-shipping-fast"></i>
      <h3>Envío Rápido</h3>
      <p>Entrega en 2-5 días hábiles a todo el país</p>
    </div>
    <div class="feature-card">
      <i class="fas fa-shield-alt"></i>
      <h3>Compra Segura</h3>
      <p>Pagos 100% seguros y protegidos</p>
    </div>
    <div class="feature-card">
      <i class="fas fa-undo-alt"></i>
      <h3>Devoluciones Fáciles</h3>
      <p>30 días para cambios y devoluciones</p>
    </div>
    <div class="feature-card">
      <i class="fas fa-headset"></i>
      <h3>Soporte 24/7</h3>
      <p>Atención al cliente siempre disponible</p>
    </div>
  </section>

  <!-- Productos Destacados -->
  <section class="productos">
    <div class="section-header">
      <h2>Productos Destacados</h2>
      <p>Descubre nuestra selección especial de prendas elegantes y cómodas</p>
    </div>

    <div class="productos-grid">
      <?php while ($fila = $resultado->fetch_assoc()): ?>
        <div class="producto-card">
          <div class="producto-image">
            <img src="img/<?php echo htmlspecialchars($fila['imagen']); ?>"
              alt="<?php echo htmlspecialchars($fila['nombre']); ?>">
            <span class="producto-badge">Destacado</span>
          </div>
          <div class="producto-info">
            <h3><?php echo htmlspecialchars($fila['nombre']); ?></h3>
            <div class="producto-price">
              $<?php echo number_format($fila['precio'], 0, ',', '.'); ?>
            </div>
            <div class="producto-actions">
              <a href="detalle_producto.php?id=<?php echo $fila['id']; ?>" class="btn-ver">
                <i class="fas fa-eye"></i> Ver Detalles
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>TiendaPlus</h3>
        <p>Moda plus size elegante y cómoda para todas las ocasiones. Realzamos tu belleza natural con diseños
          exclusivos.</p>
        <div class="footer-social">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-pinterest"></i></a>
        </div>
      </div>

      <div class="footer-section">
        <h3>Enlaces Rápidos</h3>
        <p><a href="catalogo.php">Catálogo</a></p>
        <p><a href="nosotros.php">Sobre Nosotros</a></p>
        <p><a href="guia_tallas.php">Guía de Tallas</a></p>
        <p><a href="ayuda.php">Ayuda</a></p>
      </div>

      <div class="footer-section">
        <h3>Atención al Cliente</h3>
        <p><a href="contactanos.php">Contacto</a></p>
        <p><a href="#">Términos y Condiciones</a></p>
        <p><a href="#">Política de Privacidad</a></p>
        <p><a href="#">Cambios y Devoluciones</a></p>
      </div>

      <div class="footer-section">
        <h3>Contacto</h3>
        <p><i class="fas fa-map-marker-alt"></i> Medellín, Colombia</p>
        <p><i class="fas fa-phone"></i> +57 300 123 4567</p>
        <p><i class="fas fa-envelope"></i> info@tiendaplus.com</p>
        <p><i class="fas fa-clock"></i> Lun - Sáb: 9:00 - 18:00</p>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2025 TiendaPlus. Todos los derechos reservados. | Diseñado con <i class="fas fa-heart"
          style="color: #d4a574;"></i> para ti</p>
    </div>
  </footer>

</body>

</html>