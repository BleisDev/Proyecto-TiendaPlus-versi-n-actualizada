<?php
session_start();
include_once("../backend/conexion.php");

if (!isset($_GET['id'])) {
    header("Location: catalogo.php");
    exit;
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM productos WHERE id = $id");
if (!$result || $result->num_rows == 0) {
    echo "Producto no encontrado";
    exit;
}
$producto = $result->fetch_assoc();

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad = intval($_POST['cantidad']);
    if ($cantidad < 1)
        $cantidad = 1;

    $id = $producto['id'];
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id]['cantidad'] += $cantidad;
    } else {
        $_SESSION['carrito'][$id] = [
            'id' => $producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'imagen' => $producto['imagen'],
            'cantidad' => $cantidad
        ];
    }
    header("Location: detalle_producto.php?id=$id&added=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto['nombre']) ?> - TiendaPlus</title>

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

        nav a:hover {
            color: #d4a574;
        }

        .header-icons {
            display: flex;
            gap: 20px;
            font-size: 20px;
        }

        .header-icons a {
            color: #2c2c2c;
            transition: color 0.3s;
        }

        .header-icons a:hover {
            color: #d4a574;
        }

        /* Breadcrumb */
        .breadcrumb {
            max-width: 1200px;
            margin: 30px auto 0;
            padding: 0 40px;
            font-size: 14px;
            color: #666;
        }

        .breadcrumb a {
            color: #d4a574;
            text-decoration: none;
        }

        /* Product Detail */
        .product-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px;
        }

        .product-detail {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .product-image {
            position: relative;
        }

        .product-image img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .product-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            color: #2c2c2c;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 32px;
            font-weight: 700;
            color: #d4a574;
            margin-bottom: 25px;
        }

        .product-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .product-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #fafafa;
            border-radius: 10px;
        }

        .meta-item {
            flex: 1;
            text-align: center;
        }

        .meta-item i {
            font-size: 24px;
            color: #d4a574;
            margin-bottom: 8px;
        }

        .meta-item .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .meta-item .value {
            font-size: 16px;
            font-weight: 600;
            color: #2c2c2c;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .quantity-selector label {
            font-weight: 600;
            color: #2c2c2c;
        }

        .quantity-selector input {
            width: 80px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .quantity-selector input:focus {
            outline: none;
            border-color: #d4a574;
        }

        .product-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 165, 116, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #2c2c2c;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .modal {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            animation: slideUp 0.3s ease;
        }

        .modal i {
            font-size: 60px;
            color: #d4a574;
            margin-bottom: 20px;
        }

        .modal h3 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #2c2c2c;
            margin-bottom: 15px;
        }

        .modal p {
            color: #666;
            margin-bottom: 30px;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        @media (max-width: 768px) {
            .header-main {
                padding: 15px 20px;
            }

            .product-detail {
                grid-template-columns: 1fr;
                padding: 30px 20px;
            }

            .product-info h1 {
                font-size: 28px;
            }

            .product-actions {
                flex-direction: column;
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
                <a href="nosotros.php"><i class="fas fa-info-circle"></i> Nosotros</a>
                <a href="contactanos.php"><i class="fas fa-envelope"></i> Contacto</a>
            </nav>

            <div class="header-icons">
                <a href="login.php" title="Mi cuenta"><i class="fas fa-user"></i></a>
                <a href="resenas.php" title="Favoritos"><i class="fas fa-heart"></i></a>
                <a href="carrito.php" title="Carrito"><i class="fas fa-shopping-bag"></i></a>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">Inicio</a> / <a href="catalogo.php">Catálogo</a> /
        <?= htmlspecialchars($producto['nombre']) ?>
    </div>

    <!-- Product Detail -->
    <div class="product-container">
        <div class="product-detail">
            <div class="product-image">
                <img src="img/<?= htmlspecialchars($producto['imagen']) ?>"
                    alt="<?= htmlspecialchars($producto['nombre']) ?>">
            </div>

            <div class="product-info">
                <h1><?= htmlspecialchars($producto['nombre']) ?></h1>

                <div class="product-price">
                    $<?= number_format($producto['precio'], 0, ',', '.') ?>
                </div>

                <div class="product-description">
                    <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
                </div>

                <div class="product-meta">
                    <div class="meta-item">
                        <i class="fas fa-box"></i>
                        <div class="label">Stock</div>
                        <div class="value"><?= $producto['stock'] ?> unidades</div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-truck"></i>
                        <div class="label">Envío</div>
                        <div class="value">2-5 días</div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-shield-alt"></i>
                        <div class="label">Garantía</div>
                        <div class="value">30 días</div>
                    </div>
                </div>

                <div class="quantity-selector">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" value="1" min="1"
                        max="<?= $producto['stock'] ?>">
                </div>

                <div class="product-actions">
                    <button type="button" class="btn btn-primary"
                        onclick="agregarAlCarritoDetalle(<?= $producto['id'] ?>, '<?= htmlspecialchars($producto['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($producto['tallas'] ?? '', ENT_QUOTES) ?>')">
                        <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                    </button>
                    <a href="catalogo.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modal-agregado">
        <div class="modal">
            <i class="fas fa-check-circle"></i>
            <h3>¡Producto Agregado!</h3>
            <p>El producto se añadió correctamente a tu carrito</p>
            <div class="modal-buttons">
                <a href="carrito.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Ver Carrito
                </a>
                <a href="#" class="btn btn-secondary" id="cerrar-modal">
                    Seguir Comprando
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 TiendaPlus. Todos los derechos reservados.</p>
    </footer>

    <?php if (isset($_GET['added'])): ?>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const modal = document.getElementById("modal-agregado");
                const closeBtn = document.getElementById("cerrar-modal");

                modal.style.display = "flex";
                closeBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    modal.style.display = "none";
                });
            });
        </script>
    <?php endif; ?>

    <script>
        // SISTEMA DE TALLAS - Detalle de Producto
        function agregarAlCarritoDetalle(productoId, productoNombre, tallasDisponibles) {
            const cantidad = document.getElementById('cantidad').value;

            // Verificar si el usuario ha iniciado sesión
            <?php if (!isset($_SESSION['usuario_id'])): ?>
                alert('⚠️ Debes iniciar sesión para agregar productos al carrito');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            // Si el producto tiene tallas, mostrar modal
            if (tallasDisponibles && tallasDisponibles.trim() !== '') {
                mostrarModalTallas(productoId, productoNombre, tallasDisponibles, cantidad);
            } else {
                // Sin tallas, agregar directamente
                agregarAlCarritoConTalla(productoId, productoNombre, null, cantidad);
            }
        }

        function mostrarModalTallas(productoId, productoNombre, tallasDisponibles, cantidad) {
            const modal = document.createElement('div');
            modal.id = 'modal-tallas';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeIn 0.3s ease;
            `;

            const tallasArray = tallasDisponibles.split(',').map(t => t.trim());
            let tallasHTML = '';
            tallasArray.forEach(talla => {
                tallasHTML += `
                    <button class="talla-btn" data-talla="${talla}">
                        ${talla}
                    </button>
                `;
            });

            modal.innerHTML = `
                <div style="
                    background: white;
                    border-radius: 20px;
                    padding: 40px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                    animation: slideUp 0.3s ease;
                ">
                    <h3 style="
                        font-family: 'Playfair Display', serif;
                        font-size: 24px;
                        color: #2c2c2c;
                        margin-bottom: 10px;
                        text-align: center;
                    ">Selecciona tu talla</h3>
                    <p style="
                        color: #666;
                        text-align: center;
                        margin-bottom: 30px;
                        font-size: 14px;
                    ">${productoNombre}</p>
                    
                    <div style="
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
                        gap: 15px;
                        margin-bottom: 30px;
                    ">
                        ${tallasHTML}
                    </div>

                    <button onclick="cerrarModalTallas()" style="
                        width: 100%;
                        padding: 12px;
                        background: #e5e7eb;
                        color: #2c2c2c;
                        border: none;
                        border-radius: 10px;
                        font-weight: 500;
                        cursor: pointer;
                    ">
                        Cancelar
                    </button>
                </div>
            `;

            document.body.appendChild(modal);

            const tallaBtns = modal.querySelectorAll('.talla-btn');
            tallaBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const tallaSeleccionada = this.getAttribute('data-talla');
                    cerrarModalTallas();
                    agregarAlCarritoConTalla(productoId, productoNombre, tallaSeleccionada, cantidad);
                });
            });
        }

        function cerrarModalTallas() {
            const modal = document.getElementById('modal-tallas');
            if (modal) {
                modal.remove();
            }
        }

        function agregarAlCarritoConTalla(productoId, productoNombre, talla, cantidad) {
            const formData = new FormData();
            formData.append('producto_id', productoId);
            formData.append('cantidad', cantidad);
            if (talla) {
                formData.append('talla', talla);
            }

            fetch('../backend/carrito_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=agregar&id_producto=${productoId}&cantidad=${cantidad || 1}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        const mensaje = document.createElement('div');
                        mensaje.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white;
                        padding: 20px 30px;
                        border-radius: 12px;
                        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
                        z-index: 10000;
                        font-weight: 500;
                        animation: slideIn 0.3s ease;
                    `;
                        let mensajeTexto = `<i class="fas fa-check-circle"></i> <strong>${productoNombre}</strong>`;
                        if (talla) {
                            mensajeTexto += ` (Talla: ${talla})`;
                        }
                        mensajeTexto += ` agregado al carrito (${cantidad} unidad${cantidad > 1 ? 'es' : ''})`;
                        mensaje.innerHTML = mensajeTexto;
                        document.body.appendChild(mensaje);

                        setTimeout(() => {
                            mensaje.style.animation = 'slideOut 0.3s ease';
                            setTimeout(() => mensaje.remove(), 300);
                        }, 3000);

                        setTimeout(() => {
                            if (confirm('¿Deseas ir al carrito para finalizar tu compra?')) {
                                window.location.href = 'carrito.php';
                            }
                        }, 500);
                    } else {
                        alert('❌ Error: ' + (data.mensaje || 'No se pudo agregar el producto al carrito'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error al agregar el producto al carrito');
                });
        }

        // Estilos
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from {
                    transform: translateY(50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
            .talla-btn {
                padding: 15px;
                background: white;
                border: 2px solid #d4a574;
                border-radius: 10px;
                font-weight: 600;
                font-size: 16px;
                color: #2c2c2c;
                cursor: pointer;
                transition: all 0.3s;
            }
            .talla-btn:hover {
                background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
                color: white;
                transform: scale(1.05);
                box-shadow: 0 5px 15px rgba(212, 165, 116, 0.4);
            }
        `;
        document.head.appendChild(style);
    </script>

</body>

</html>