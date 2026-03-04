<?php
session_start();
require_once('../backend/conexion.php');

// Validar sesión
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nombre = $_SESSION['nombre'] ?? 'Invitado';

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$usuario_id) {
    $mensaje = "Debes iniciar sesión para dejar una reseña.";
    $tipo_mensaje = 'error';
  } else {
    $producto_id = intval($_POST['producto_id']);
    $comentario = trim($_POST['comentario']);
    $calificacion = intval($_POST['calificacion']);

    // Validar existencia del producto
    $check = $conn->prepare("SELECT id FROM productos WHERE id = ?");
    $check->bind_param("i", $producto_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
      $stmt = $conn->prepare("INSERT INTO resenas (usuario_id, producto_id, comentario, calificacion) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("iisi", $usuario_id, $producto_id, $comentario, $calificacion);
      $stmt->execute();
      $stmt->close();
      $mensaje = "¡Gracias por tu reseña! Ha sido publicada correctamente.";
      $tipo_mensaje = 'success';
    } else {
      $mensaje = "El producto seleccionado no existe.";
      $tipo_mensaje = 'error';
    }
    $check->close();
  }
}

// Obtener productos para el menú desplegable
$productos = $conn->query("SELECT id, nombre FROM productos ORDER BY nombre ASC");

// Obtener reseñas con información del usuario
$resenas = $conn->query("
    SELECT r.*, p.nombre AS producto, p.imagen, u.nombre AS usuario
    FROM resenas r
    INNER JOIN productos p ON r.producto_id = p.id
    LEFT JOIN usuarios u ON r.usuario_id = u.id
    ORDER BY r.fecha DESC
    LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reseñas - TiendaPlus</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap"
    rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
      color: #2c2c2c;
      line-height: 1.6;
    }

    /* ============================================
       HEADER
    ============================================ */
    .header {
      background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
      padding: 20px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 700;
      color: white;
      text-decoration: none;
    }

    .logo span {
      color: #d4a574;
    }

    nav a {
      color: white;
      text-decoration: none;
      margin-left: 30px;
      font-size: 14px;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #d4a574;
    }

    /* ============================================
       HERO SECTION
    ============================================ */
    .hero {
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      text-align: center;
      padding: 80px 20px;
    }

    .hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: 48px;
      font-weight: 700;
      margin-bottom: 15px;
    }

    .hero p {
      font-size: 18px;
      opacity: 0.95;
      max-width: 600px;
      margin: 0 auto;
    }

    /* ============================================
       CONTAINER
    ============================================ */
    .container {
      max-width: 1200px;
      margin: -50px auto 60px;
      padding: 0 20px;
    }

    .content-grid {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 30px;
      margin-top: 30px;
    }

    /* ============================================
       FORM SECTION
    ============================================ */
    .form-card {
      background: white;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      height: fit-content;
      position: sticky;
      top: 20px;
    }

    .form-card h2 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      color: #2c2c2c;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .form-card h2 i {
      color: #d4a574;
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

    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 14px;
      transition: all 0.3s;
    }

    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #d4a574;
      box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.1);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 120px;
    }

    .rating-input {
      display: flex;
      gap: 10px;
      flex-direction: row-reverse;
      justify-content: flex-end;
      font-size: 32px;
    }

    .rating-input input {
      display: none;
    }

    .rating-input label {
      cursor: pointer;
      color: #ddd;
      transition: all 0.2s;
    }

    .rating-input label:hover,
    .rating-input label:hover~label,
    .rating-input input:checked~label {
      color: #d4a574;
    }

    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      border: none;
      padding: 15px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
    }

    .btn-submit:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    /* ============================================
       ALERTS
    ============================================ */
    .alert {
      padding: 15px 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: slideDown 0.3s ease;
    }

    .alert-success {
      background: #d4edda;
      border-left: 4px solid #28a745;
      color: #155724;
    }

    .alert-error {
      background: #f8d7da;
      border-left: 4px solid #dc3545;
      color: #721c24;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ============================================
       REVIEWS SECTION
    ============================================ */
    .reviews-section {
      background: white;
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .reviews-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #f0f0f0;
    }

    .reviews-header h2 {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      color: #2c2c2c;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .reviews-header h2 i {
      color: #d4a574;
    }

    .reviews-count {
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
    }

    .review-card {
      border-bottom: 1px solid #f0f0f0;
      padding: 25px 0;
      animation: fadeIn 0.5s ease;
    }

    .review-card:last-child {
      border-bottom: none;
    }

    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
    }

    .review-product {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .product-thumb {
      width: 60px;
      height: 60px;
      border-radius: 8px;
      object-fit: cover;
      border: 2px solid #f0f0f0;
    }

    .product-info h4 {
      font-size: 16px;
      color: #2c2c2c;
      margin-bottom: 5px;
    }

    .review-meta {
      font-size: 13px;
      color: #888;
    }

    .review-meta i {
      color: #d4a574;
      margin-right: 5px;
    }

    .review-rating {
      display: flex;
      gap: 3px;
      font-size: 18px;
    }

    .star-filled {
      color: #d4a574;
    }

    .star-empty {
      color: #ddd;
    }

    .review-content {
      color: #555;
      line-height: 1.8;
      font-size: 15px;
      margin-top: 12px;
    }

    .no-reviews {
      text-align: center;
      padding: 60px 20px;
      color: #888;
    }

    .no-reviews i {
      font-size: 64px;
      color: #ddd;
      margin-bottom: 20px;
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

    /* ============================================
       RESPONSIVE
    ============================================ */
    @media (max-width: 768px) {
      .content-grid {
        grid-template-columns: 1fr;
      }

      .form-card {
        position: static;
      }

      .hero h1 {
        font-size: 32px;
      }

      .reviews-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
    }
  </style>
</head>

<body>

  <!-- HEADER -->
  <div class="header">
    <div class="header-content">
      <a href="index.php" class="logo">Tienda<span>Plus</span></a>
      <nav>
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="catalogo.php"><i class="fas fa-th"></i> Catálogo</a>
        <a href="carrito.php"><i class="fas fa-shopping-bag"></i> Carrito</a>
      </nav>
    </div>
  </div>

  <!-- HERO -->
  <div class="hero">
    <h1><i class="fas fa-star"></i> Reseñas de Clientes</h1>
    <p>Comparte tu experiencia y ayuda a otros clientes a tomar la mejor decisión</p>
  </div>

  <!-- MAIN CONTENT -->
  <div class="container">
    <?php if ($mensaje): ?>
      <div class="alert alert-<?= $tipo_mensaje ?>">
        <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= htmlspecialchars($mensaje) ?>
      </div>
    <?php endif; ?>

    <div class="content-grid">
      <!-- FORM SECTION -->
      <div class="form-card">
        <h2><i class="fas fa-edit"></i> Deja tu Reseña</h2>

        <?php if (!$usuario_id): ?>
          <div class="alert alert-error">
            <i class="fas fa-lock"></i>
            <div>
              <strong>Inicia sesión</strong> para dejar una reseña.
              <br><a href="login.php" style="color: #d4a574;">Ir a Login</a>
            </div>
          </div>
        <?php else: ?>
          <form method="POST">
            <div class="form-group">
              <label><i class="fas fa-box"></i> Producto</label>
              <select name="producto_id" required>
                <option value="">Selecciona un producto</option>
                <?php while ($p = $productos->fetch_assoc()): ?>
                  <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group">
              <label><i class="fas fa-comment"></i> Tu Comentario</label>
              <textarea name="comentario" placeholder="Cuéntanos tu experiencia con este producto..." required></textarea>
            </div>

            <div class="form-group">
              <label><i class="fas fa-star"></i> Calificación</label>
              <div class="rating-input">
                <input type="radio" name="calificacion" value="5" id="star5" required>
                <label for="star5"><i class="fas fa-star"></i></label>
                <input type="radio" name="calificacion" value="4" id="star4">
                <label for="star4"><i class="fas fa-star"></i></label>
                <input type="radio" name="calificacion" value="3" id="star3">
                <label for="star3"><i class="fas fa-star"></i></label>
                <input type="radio" name="calificacion" value="2" id="star2">
                <label for="star2"><i class="fas fa-star"></i></label>
                <input type="radio" name="calificacion" value="1" id="star1">
                <label for="star1"><i class="fas fa-star"></i></label>
              </div>
            </div>

            <button type="submit" class="btn-submit">
              <i class="fas fa-paper-plane"></i>
              Publicar Reseña
            </button>
          </form>
        <?php endif; ?>
      </div>

      <!-- REVIEWS SECTION -->
      <div class="reviews-section">
        <div class="reviews-header">
          <h2><i class="fas fa-comments"></i> Reseñas Recientes</h2>
          <span class="reviews-count"><?= $resenas->num_rows ?> reseñas</span>
        </div>

        <?php if ($resenas->num_rows > 0): ?>
          <?php while ($r = $resenas->fetch_assoc()): ?>
            <div class="review-card">
              <div class="review-header">
                <div class="review-product">
                  <img src="../uploads/<?= htmlspecialchars($r['imagen']) ?>" alt="<?= htmlspecialchars($r['producto']) ?>"
                    class="product-thumb" onerror="this.src='../uploads/sin_imagen.png'">
                  <div class="product-info">
                    <h4><?= htmlspecialchars($r['producto']) ?></h4>
                    <div class="review-meta">
                      <i class="fas fa-user"></i><?= htmlspecialchars($r['usuario'] ?? 'Usuario') ?>
                      <span style="margin: 0 8px;">•</span>
                      <i class="fas fa-calendar"></i><?= date('d/m/Y', strtotime($r['fecha'])) ?>
                    </div>
                  </div>
                </div>
                <div class="review-rating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= $r['calificacion'] ? 'star-filled' : 'star-empty' ?>"></i>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="review-content">
                "<?= htmlspecialchars($r['comentario']) ?>"
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-reviews">
            <i class="fas fa-inbox"></i>
            <h3>No hay reseñas todavía</h3>
            <p>Sé el primero en compartir tu experiencia</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</body>

</html>