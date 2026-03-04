<?php
session_start();
require_once('../backend/conexion.php');

// Comprobaciones iniciales
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
  header("Location: carrito.php");
  exit;
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc() ?: ['nombre' => 'Usuario', 'email' => ''];
$stmt->close();

// Limpiar y normalizar carrito
$raw = $_SESSION['carrito'];
$carrito = [];
$total = 0.0;

foreach ($raw as $key => $item) {
  $id = null;
  if (isset($item['id'])) {
    $id = (int) $item['id'];
  } elseif (is_numeric($key)) {
    $id = (int) $key;
  }

  if ($id && (!isset($item['nombre']) || !isset($item['precio']))) {
    $stmt = $conn->prepare("SELECT nombre, precio, imagen FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($r) {
      $item['nombre'] = $item['nombre'] ?? $r['nombre'];
      $item['precio'] = $item['precio'] ?? $r['precio'];
      $item['imagen'] = $item['imagen'] ?? $r['imagen'];
    }
  }

  $nombre = isset($item['nombre']) ? (string) $item['nombre'] : 'Producto sin nombre';
  $precio = isset($item['precio']) ? floatval($item['precio']) : 0.0;
  $cantidad = isset($item['cantidad']) ? max(1, intval($item['cantidad'])) : 1;
  $imagen = isset($item['imagen']) ? (string) $item['imagen'] : '';

  if (!$id) {
    $id = uniqid('i_');
  }

  $carrito[$id] = [
    'id' => $id,
    'nombre' => $nombre,
    'precio' => $precio,
    'cantidad' => $cantidad,
    'imagen' => $imagen
  ];

  $total += $precio * $cantidad;
}

$_SESSION['carrito'] = $carrito;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Checkout - TiendaPlus</title>

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

    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
      color: white;
      padding: 40px;
      text-align: center;
    }

    .page-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 36px;
      margin-bottom: 10px;
    }

    .page-header p {
      font-size: 14px;
      color: #ccc;
    }

    /* Container */
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 40px;
      display: grid;
      grid-template-columns: 1fr 420px;
      gap: 30px;
      align-items: start;
    }

    /* Card */
    .card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
    }

    .section-title {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: #2c2c2c;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #d4a574;
    }

    /* Form */
    .form-row {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }

    .field {
      flex: 1;
    }

    .field label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 8px;
      color: #2c2c2c;
    }

    .field input,
    .field select {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 15px;
      transition: border-color 0.3s;
    }

    .field input:focus,
    .field select:focus {
      outline: none;
      border-color: #d4a574;
    }

    /* Payment Methods */
    .payment-methods {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .pay-card {
      flex: 1;
      min-width: 150px;
      border: 2px solid #e0e0e0;
      padding: 15px;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s;
      text-align: center;
      font-weight: 500;
    }

    .pay-card:hover {
      border-color: #d4a574;
      background: #fafafa;
    }

    .pay-card.selected {
      border-color: #d4a574;
      background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
      color: white;
    }

    /* Summary */
    .summary-list {
      list-style: none;
      padding: 0;
      margin: 0 0 20px 0;
    }

    .summary-item {
      display: flex;
      gap: 15px;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .summary-item img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 10px;
    }

    .summary-item-info {
      flex: 1;
    }

    .summary-item-info h4 {
      font-size: 15px;
      margin-bottom: 5px;
    }

    .summary-item-info .qty {
      font-size: 13px;
      color: #666;
    }

    .summary-item-price {
      font-size: 18px;
      font-weight: 700;
      color: #d4a574;
    }

    .summary-line {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      font-size: 15px;
    }

    .summary-line.total {
      font-size: 22px;
      font-weight: 700;
      padding-top: 20px;
      border-top: 2px solid #f0f0f0;
      color: #2c2c2c;
    }

    .summary-line.total span:last-child {
      color: #d4a574;
    }

    /* Button */
    .btn-primary {
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

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
    }

    .user-info-box {
      background: #fafafa;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 25px;
    }

    .user-info-box strong {
      font-size: 16px;
      color: #2c2c2c;
    }

    .user-info-box .email {
      font-size: 14px;
      color: #666;
    }

    .small-text {
      font-size: 13px;
      color: #666;
      margin-top: 10px;
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

    /* Responsive */
    @media (max-width: 980px) {
      .container {
        grid-template-columns: 1fr;
        padding: 0 20px;
      }

      .header-main {
        padding: 15px 20px;
      }

      .page-header h1 {
        font-size: 28px;
      }
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header>
    <div class="header-top">
      <span><i class="fas fa-lock"></i> Pago seguro y encriptado</span>
    </div>

    <div class="header-main">
      <a href="index.php" class="logo">
        Tienda<span>Plus</span>
      </a>
    </div>
  </header>

  <!-- Page Header -->
  <section class="page-header">
    <h1><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
    <p>Completa tu información para procesar el pedido</p>
  </section>

  <!-- Container -->
  <div class="container">

    <!-- Left: Form -->
    <div class="card">
      <h3 class="section-title"><i class="fas fa-user"></i> Información de Contacto</h3>

      <div class="user-info-box">
        <strong><?= htmlspecialchars($usuario['nombre'] ?: 'Usuario') ?></strong><br>
        <span class="email"><?= htmlspecialchars($usuario['email'] ?: '') ?></span>
      </div>

      <form id="checkoutForm" method="post" action="../backend/finalizar_compra.php">

        <h3 class="section-title"><i class="fas fa-shipping-fast"></i> Información de Envío</h3>

        <div class="form-row">
          <div class="field">
            <label for="nombre">Nombre Completo *</label>
            <input id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
          </div>
          <div class="field">
            <label for="telefono">Teléfono *</label>
            <input id="telefono" name="telefono" placeholder="Ej: 3123456789" required>
          </div>
        </div>

        <div class="form-row">
          <div class="field">
            <label for="direccion">Dirección *</label>
            <input id="direccion" name="direccion" placeholder="Calle, número, apartamento" required>
          </div>
          <div class="field">
            <label for="ciudad">Ciudad *</label>
            <input id="ciudad" name="ciudad" placeholder="Ej: Medellín" required>
          </div>
        </div>

        <div class="form-row">
          <div class="field">
            <label for="pais">País</label>
            <select id="pais" name="pais">
              <option value="CO" selected>Colombia</option>
            </select>
          </div>
          <div class="field">
            <label for="region">Departamento</label>
            <select id="region" name="region">
              <option value="ANT">Antioquia</option>
              <option value="CUN">Cundinamarca</option>
              <option value="VAL">Valle del Cauca</option>
            </select>
          </div>
        </div>

        <h3 class="section-title"><i class="fas fa-truck"></i> Método de Envío</h3>

        <div class="payment-methods" id="envios">
          <label class="pay-card selected" data-price="10000">
            <input type="radio" name="envio" value="medellin" checked style="display:none">
            <div><strong>Medellín</strong><br><small>$10.000</small></div>
          </label>

          <label class="pay-card" data-price="15000">
            <input type="radio" name="envio" value="otras_antioquia" style="display:none">
            <div><strong>Otras ciudades</strong><br><small>$15.000</small></div>
          </label>
        </div>

        <h3 class="section-title"><i class="fas fa-credit-card"></i> Método de Pago</h3>

        <div class="payment-methods" id="pagos">
          <label class="pay-card selected" data-type="card">
            <input type="radio" name="pago" value="tarjeta" checked style="display:none">
            <div><i class="fas fa-credit-card"></i><br>Tarjeta</div>
          </label>

          <label class="pay-card" data-type="mercadopago">
            <input type="radio" name="pago" value="mercadopago" style="display:none">
            <div><i class="fab fa-cc-mastercard"></i><br>Mercado Pago</div>
          </label>

          <label class="pay-card" data-type="pse">
            <input type="radio" name="pago" value="pse" style="display:none">
            <div><i class="fas fa-university"></i><br>PSE</div>
          </label>
        </div>

        <div id="tarjetaFields" style="margin-top:20px">
          <div class="form-row">
            <div class="field">
              <label>Número de Tarjeta</label>
              <input name="card_number" placeholder="1234 1234 1234 1234">
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Nombre en la Tarjeta</label>
              <input name="card_name" placeholder="Como aparece en la tarjeta">
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label>Expiración (MM/AA)</label>
              <input name="card_exp" placeholder="MM/AA">
            </div>
            <div class="field">
              <label>CVC</label>
              <input name="card_cvc" placeholder="123">
            </div>
          </div>
        </div>

      </form>
    </div>

    <!-- Right: Summary -->
    <aside class="card">
      <h3 class="section-title"><i class="fas fa-shopping-bag"></i> Resumen del Pedido</h3>

      <ul class="summary-list">
        <?php foreach ($_SESSION['carrito'] as $id => $item):
          $nombre = htmlspecialchars($item['nombre'] ?? 'Producto');
          $cantidad = intval($item['cantidad'] ?? 1);
          $precio = floatval($item['precio'] ?? 0);
          $imagen = !empty($item['imagen']) ? "img/" . htmlspecialchars($item['imagen']) : null;
          ?>
          <li class="summary-item">
            <?php if ($imagen): ?>
              <img src="<?= $imagen ?>" alt="<?= $nombre ?>">
            <?php endif; ?>
            <div class="summary-item-info">
              <h4><?= $nombre ?></h4>
              <div class="qty">Cantidad: <?= $cantidad ?></div>
            </div>
            <div class="summary-item-price">
              $<?= number_format($precio * $cantidad, 0, ',', '.') ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="summary-line">
        <span>Subtotal</span>
        <span>$<?= number_format($total, 0, ',', '.') ?></span>
      </div>

      <div class="summary-line">
        <span>Envío</span>
        <span id="envioPrecio">$10.000</span>
      </div>

      <div class="summary-line total">
        <span>Total</span>
        <span id="granTotal">$<?= number_format($total + 10000, 0, ',', '.') ?></span>
      </div>

      <div style="margin-top:25px">
        <button class="btn-primary" id="btnPagar">
          <i class="fas fa-lock"></i> Procesar Pago Seguro
        </button>
      </div>

      <div class="small-text" style="text-align:center">
        <i class="fas fa-shield-alt"></i> Pago 100% seguro y encriptado
      </div>
    </aside>

  </div>

  <!-- Footer -->
  <footer>
    <p>© 2025 TiendaPlus. Todos los derechos reservados.</p>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Seleccionar envíos
      document.querySelectorAll('#envios .pay-card').forEach(card => {
        card.addEventListener('click', () => {
          document.querySelectorAll('#envios .pay-card').forEach(c => c.classList.remove('selected'));
          card.classList.add('selected');

          const price = parseInt(card.getAttribute('data-price') || '0', 10);
          document.getElementById('envioPrecio').textContent = '$' + price.toLocaleString('de-DE');
          const subtotal = <?= json_encode(floatval($total)) ?>;
          document.getElementById('granTotal').textContent = '$' + (subtotal + price).toLocaleString('de-DE');
        });
      });

      // Seleccionar método de pago
      document.querySelectorAll('#pagos .pay-card').forEach(card => {
        card.addEventListener('click', () => {
          document.querySelectorAll('#pagos .pay-card').forEach(c => c.classList.remove('selected'));
          card.classList.add('selected');

          const type = card.getAttribute('data-type');
          document.getElementById('tarjetaFields').style.display = (type === 'card') ? 'block' : 'none';
        });
      });

      // Botón pagar
      document.getElementById('btnPagar').addEventListener('click', () => {
        const selectedEnvio = document.querySelector('#envios .pay-card.selected');
        const envioValue = selectedEnvio ? (selectedEnvio.querySelector('input') ? selectedEnvio.querySelector('input').value : '') : '';

        const f = document.getElementById('checkoutForm');
        let h = document.getElementById('envio_hidden');
        if (!h) {
          h = document.createElement('input');
          h.type = 'hidden';
          h.name = 'envio_seleccionado';
          h.id = 'envio_hidden';
          f.appendChild(h);
        }
        h.value = envioValue;

        f.submit();
      });
    });
  </script>

</body>

</html>