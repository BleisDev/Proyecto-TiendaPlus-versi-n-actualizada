<?php
session_start();
require_once('../backend/conexion.php');

// Obtener categorías
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre");

// Filtrar productos por categoría
$filtro = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$categoria_nombre = "Todos los Productos";

if ($filtro > 0) {
    // Obtener nombre de la categoría seleccionada
    $stmt_cat = $conn->prepare("SELECT nombre FROM categorias WHERE id = ?");
    $stmt_cat->bind_param("i", $filtro);
    $stmt_cat->execute();
    $result_cat = $stmt_cat->get_result();
    if ($result_cat->num_rows > 0) {
        $cat_data = $result_cat->fetch_assoc();
        $categoria_nombre = $cat_data['nombre'];
    }
    $stmt_cat->close();

    // Obtener productos de la categoría seleccionada
    $stmt = $conn->prepare("SELECT * FROM productos WHERE categoria_id = ? ORDER BY nombre");
    $stmt->bind_param("i", $filtro);
    $stmt->execute();
    $productos = $stmt->get_result();
} else {
    // Obtener todos los productos
    $productos = $conn->query("SELECT * FROM productos ORDER BY nombre");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - TiendaPlus</title>

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

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 16px;
            color: #ccc;
        }

        /* Container */
        .container {
            display: flex;
            max-width: 1200px;
            margin: 40px auto;
            gap: 30px;
            padding: 0 40px;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .sidebar h3 {
            font-family: 'Playfair Display', serif;
            color: #2c2c2c;
            margin-bottom: 25px;
            font-size: 22px;
            padding-bottom: 15px;
            border-bottom: 2px solid #d4a574;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 12px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: #fafafa;
            border-radius: 10px;
            color: #2c2c2c;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            transform: translateX(5px);
        }

        .sidebar ul li a i {
            font-size: 16px;
        }

        /* Productos Grid */
        .productos {
            flex: 1;
        }

        .productos-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .productos-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #2c2c2c;
        }

        .productos-count {
            color: #666;
            font-size: 14px;
        }

        .productos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
        }

        .producto-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            cursor: pointer;
        }

        .producto-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .producto-image {
            position: relative;
            overflow: hidden;
            height: 320px;
        }

        .producto-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .producto-card:hover .producto-image img {
            transform: scale(1.1);
        }

        .producto-info {
            padding: 20px;
        }

        .producto-info h3 {
            font-size: 17px;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 10px;
            min-height: 45px;
        }

        .producto-price {
            font-size: 22px;
            font-weight: 700;
            color: #d4a574;
            margin-bottom: 15px;
        }

        .producto-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-detalle {
            display: block;
            text-align: center;
            padding: 12px;
            background: #2c2c2c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-detalle:hover {
            background: #d4a574;
        }

        .btn-carrito {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: linear-gradient(135deg, #d4a574 0%, #c49563 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-carrito:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 165, 116, 0.4);
        }

        .btn-carrito i {
            font-size: 16px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 80px;
            color: #d4a574;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2c2c2c;
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
                flex-wrap: wrap;
            }

            .container {
                flex-direction: column;
                padding: 0 20px;
            }

            .sidebar {
                width: 100%;
                position: static;
            }

            .page-header h1 {
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
        </div>

        <div class="header-main">
            <a href="index.php" class="logo">
                Tienda<span>Plus</span>
            </a>

            <nav>
                <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
                <a href="catalogo.php" style="color: #d4a574;"><i class="fas fa-th"></i> Catálogo</a>
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

    <!-- Page Header -->
    <section class="page-header">
        <h1><?= $filtro > 0 ? htmlspecialchars($categoria_nombre) : 'Nuestro Catálogo' ?></h1>
        <p><?= $filtro > 0 ? 'Explora nuestra colección de ' . strtolower(htmlspecialchars($categoria_nombre)) : 'Descubre toda nuestra colección de moda plus size' ?>
        </p>
    </section>

    <!-- Main Container -->
    <div class="container">

        <!-- Sidebar de Categorías -->
        <aside class="sidebar">
            <h3><i class="fas fa-filter"></i> Categorías</h3>
            <ul>
                <li>
                    <a href="catalogo.php" class="<?= ($filtro == 0) ? 'active' : '' ?>">
                        <i class="fas fa-th-large"></i> Todas las Categorías
                    </a>
                </li>

                <?php
                // Resetear el puntero de categorías para poder iterar de nuevo
                $categorias->data_seek(0);
                while ($cat = $categorias->fetch_assoc()):
                    ?>
                    <li>
                        <a href="catalogo.php?categoria=<?= $cat['id'] ?>"
                            class="<?= ($filtro == $cat['id']) ? 'active' : '' ?>">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </aside>

        <!-- Productos -->
        <section class="productos">
            <div class="productos-header">
                <h2><?= htmlspecialchars($categoria_nombre) ?></h2>
                <span class="productos-count">
                    <i class="fas fa-box"></i> <?= $productos->num_rows ?>
                    producto<?= $productos->num_rows != 1 ? 's' : '' ?>
                    encontrado<?= $productos->num_rows != 1 ? 's' : '' ?>
                </span>
            </div>

            <?php if ($productos->num_rows > 0): ?>
                <div class="productos-grid">
                    <?php while ($p = $productos->fetch_assoc()): ?>
                        <?php
                        // Obtener la primera imagen
                        $imagenes = explode(",", $p['imagen']);
                        $primera_imagen = trim($imagenes[0]);
                        ?>

                        <div class="producto-card">
                            <div class="producto-image">
                                <img src="img/<?= $primera_imagen ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                            </div>
                            <div class="producto-info">
                                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                                <div class="producto-price">
                                    $<?= number_format($p['precio'], 0, ',', '.') ?>
                                </div>
                                <div class="producto-buttons">
                                    <a href="detalle_producto.php?id=<?= $p['id'] ?>" class="btn-detalle">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                    <button class="btn-carrito"
                                        onclick="agregarAlCarrito(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['tallas'] ?? '', ENT_QUOTES) ?>')">
                                        <i class="fas fa-shopping-cart"></i> Agregar al Carrito
                                    </button>
                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No hay productos en esta categoría</h3>
                    <p>Intenta seleccionar otra categoría</p>
                </div>
            <?php endif; ?>
        </section>

    </div>

    <!-- Footer -->
    <footer>
        <p>© 2025 TiendaPlus. Todos los derechos reservados.</p>
    </footer>

    <script>
        // SISTEMA DE TALLAS v2.0 - Actualizado
        function agregarAlCarrito(productoId, productoNombre, tallasDisponibles = null) {
            // DEBUG: Ver qué valores llegan
            console.log('=== AGREGAR AL CARRITO ===');
            console.log('Producto ID:', productoId);
            console.log('Producto Nombre:', productoNombre);
            console.log('Tallas Disponibles:', tallasDisponibles);
            console.log('Tallas vacías?', tallasDisponibles ? tallasDisponibles.trim() === '' : 'null');

            // Verificar si el usuario ha iniciado sesión
            <?php if (!isset($_SESSION['usuario_id'])): ?>
                alert('⚠️ Debes iniciar sesión para agregar productos al carrito');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>

            // Si el producto tiene tallas, mostrar modal de selección
            if (tallasDisponibles && tallasDisponibles.trim() !== '') {
                console.log('✅ Mostrando modal de tallas');
                mostrarModalTallas(productoId, productoNombre, tallasDisponibles);
                return;
            }

            // Si no tiene tallas, agregar directamente
            console.log('⚠️ Producto sin tallas, agregando directamente');
            agregarAlCarritoConTalla(productoId, productoNombre, null);
        }

        function mostrarModalTallas(productoId, productoNombre, tallasDisponibles) {
            // Crear modal
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
                        transition: all 0.3s;
                    " onmouseover="this.style.background='#d1d5db'" onmouseout="this.style.background='#e5e7eb'">
                        Cancelar
                    </button>
                </div>
            `;

            document.body.appendChild(modal);

            // Agregar event listeners a los botones de talla
            const tallaBtns = modal.querySelectorAll('.talla-btn');
            tallaBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    const tallaSeleccionada = this.getAttribute('data-talla');
                    cerrarModalTallas();
                    agregarAlCarritoConTalla(productoId, productoNombre, tallaSeleccionada);
                });
            });
        }

        function cerrarModalTallas() {
            const modal = document.getElementById('modal-tallas');
            if (modal) {
                modal.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => modal.remove(), 300);
            }
        }

        function agregarAlCarritoConTalla(productoId, productoNombre, talla) {
            // Crear parámetros para enviar
            let params = `accion=agregar&id_producto=${productoId}&cantidad=1`;
            if (talla) {
                params += `&talla=${encodeURIComponent(talla)}`;
            }

            // Enviar solicitud AJAX
            fetch('../backend/carrito_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params
            })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        // Mostrar mensaje de éxito
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
                        mensajeTexto += ' agregado al carrito';
                        mensaje.innerHTML = mensajeTexto;
                        document.body.appendChild(mensaje);

                        // Remover mensaje después de 3 segundos
                        setTimeout(() => {
                            mensaje.style.animation = 'slideOut 0.3s ease';
                            setTimeout(() => mensaje.remove(), 300);
                        }, 3000);

                        // Preguntar si quiere ir al carrito
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

        // Agregar animaciones CSS
        const style = document.createElement('style');
        style.textContent = `
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
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
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