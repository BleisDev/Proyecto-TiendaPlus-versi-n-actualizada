<?php
session_start();
include_once('conexion.php');

// Verificar que el usuario sea admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../web/login.php");
    exit;
}

// Función para valores por defecto
function valor_por_defecto($tabla, $columna) {
    $defaults = [
        'productos' => ['imagen' => 'sin_imagen.png'],
        'resenas' => ['calificacion' => 1],
        'pedidos' => ['estado' => 'pendiente']
    ];
    return $defaults[$tabla][$columna] ?? '';
}

// CRUD
$tabla = $_GET['tabla'] ?? '';
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_post = $_POST['id'] ?? '';
    if ($id_post != '') {
        // Actualizar
        $sets = [];
        foreach ($_POST as $campo => $valor) {
            if ($campo != 'id') $sets[] = "$campo='" . $conn->real_escape_string($valor) . "'";
        }
        $conn->query("UPDATE $tabla SET ".implode(",", $sets)." WHERE id='$id_post'");
    } else {
        // Insertar nuevo
        $campos_insert = [];
        $valores_insert = [];
        $cols = $conn->query("SHOW COLUMNS FROM $tabla");
        while ($col = $cols->fetch_assoc()) {
            $campo = $col['Field'];
            if ($campo == 'id') continue;
            $valor = $_POST[$campo] ?? valor_por_defecto($tabla,$campo);
            $campos_insert[] = $campo;
            $valores_insert[] = "'" . $conn->real_escape_string($valor) . "'";
        }
        $conn->query("INSERT INTO $tabla (".implode(",",$campos_insert).") VALUES (".implode(",",$valores_insert).")");
    }
    header("Location: dashboard.php?tabla=$tabla");
    exit;
}

// Eliminar registro
if ($accion == 'eliminar' && $id) {
    $conn->query("DELETE FROM $tabla WHERE id='$id'");
    header("Location: dashboard.php?tabla=$tabla");
    exit;
}

// Totales para dashboard
$totalUsuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$totalProductos = $conn->query("SELECT COUNT(*) AS total FROM productos")->fetch_assoc()['total'];
$totalPedidos = $conn->query("SELECT COUNT(*) AS total FROM pedidos")->fetch_assoc()['total'];
$totalResenas = $conn->query("SELECT COUNT(*) AS total FROM resenas")->fetch_assoc()['total'];

// Total ventas y top productos
$totalVentas = $conn->query("SELECT SUM(precio*cantidad) AS total FROM detalle_pedido")->fetch_assoc()['total'] ?? 0;
$topProductosRes = $conn->query("
    SELECT p.nombre, SUM(dp.cantidad) AS vendidos
    FROM detalle_pedido dp
    JOIN productos p ON dp.producto_id = p.id
    GROUP BY dp.producto_id
    ORDER BY vendidos DESC
    LIMIT 5
");
$topProductos = [];
while($row = $topProductosRes->fetch_assoc()) $topProductos[] = $row;

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Admin | Tienda Plus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background:#f9fafb; }
.sidebar { position: fixed; left:0; top:0; width:260px; height:100%; background:#111; padding:30px 20px; color:#fff;}
.sidebar h4 { color:#ff69b4; text-align:center; margin-bottom:25px;}
.sidebar a { display:flex; align-items:center; color:#ddd; padding:10px 15px; margin-bottom:10px; text-decoration:none; border-radius:8px; transition:.3s;}
.sidebar a i { margin-right:10px; }
.sidebar a:hover, .sidebar a.active { background:#ff69b4; color:#fff; }
.content { margin-left:280px; padding:30px; }
header { display:flex; justify-content:space-between; align-items:center; background:white; border-radius:12px; padding:15px 25px; box-shadow:0 3px 10px rgba(0,0,0,0.05); margin-bottom:25px;}
header h1 { font-size:1.6em; color:#ff69b4;}
.stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:20px; margin-bottom:25px; }
.card-stat { background:white; padding:20px; border-radius:15px; box-shadow:0 3px 10px rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:space-between;}
.card-stat i { font-size:30px; color:#ff69b4;}
.table { background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05);}
.table th { background:#ff69b4; color:white;}
.btn-primary { background:#ff69b4; border:none;}
.btn-primary:hover { background:#ff4fa4;}
.btn { border-radius:8px;}
.chart-box { background:white; border-radius:15px; box-shadow:0 3px 10px rgba(0,0,0,0.08); padding:20px; margin-top:30px;}
</style>
</head>
<body>

<div class="sidebar">
<h4><i class="bi bi-shop"></i> TIENDA PLUS ADMIN</h4>
<a href="dashboard.php" class="<?= $tabla==''?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
<a href="dashboard.php?tabla=usuarios" class="<?= $tabla=='usuarios'?'active':'' ?>"><i class="bi bi-people"></i> Usuarios</a>
<a href="dashboard.php?tabla=productos" class="<?= $tabla=='productos'?'active':'' ?>"><i class="bi bi-bag-check"></i> Productos</a>
<a href="dashboard.php?tabla=categorias" class="<?= $tabla=='categorias'?'active':'' ?>"><i class="bi bi-tags"></i> Categorías</a>
<a href="dashboard.php?tabla=pedidos" class="<?= $tabla=='pedidos'?'active':'' ?>"><i class="bi bi-cart-check"></i> Pedidos</a>
<a href="dashboard.php?tabla=detalle_pedido" class="<?= $tabla=='detalle_pedido'?'active':'' ?>"><i class="bi bi-card-list"></i> Detalle Pedido</a>
<a href="dashboard.php?tabla=resenas" class="<?= $tabla=='resenas'?'active':'' ?>"><i class="bi bi-chat-left-heart"></i> Reseñas</a>
<a href="dashboard.php?tabla=carrito" class="<?= $tabla=='carrito'?'active':'' ?>"><i class="bi bi-basket"></i> Carrito</a>
<hr>
<a href="../web/index.php" class="btn btn-light w-100 mb-2"><i class="bi bi-house"></i> Volver a Tienda</a>
<a href="logout.php" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
</div>

<div class="content">
<header>
<h1>Panel de Administración</h1>
<div>👋 Bienvenido, <strong><?= $_SESSION['nombre'] ?? 'Administrador' ?></strong></div>
</header>

<?php if($tabla==''): ?>
    <!-- DASHBOARD METRICAS -->
    <div class="stats">
        <div class="card-stat"><div><h5>Usuarios</h5><p><?= $totalUsuarios ?></p></div><i class="bi bi-people"></i></div>
        <div class="card-stat"><div><h5>Productos</h5><p><?= $totalProductos ?></p></div><i class="bi bi-bag-check"></i></div>
        <div class="card-stat"><div><h5>Pedidos</h5><p><?= $totalPedidos ?></p></div><i class="bi bi-cart-check"></i></div>
        <div class="card-stat"><div><h5>Reseñas</h5><p><?= $totalResenas ?></p></div><i class="bi bi-chat-left-heart"></i></div>
        <div class="card-stat"><div><h5>Total Ventas</h5><p>$<?= number_format($totalVentas,2) ?></p></div><i class="bi bi-cash-stack"></i></div>
    </div>

    <div class="row">
        <div class="col-md-6 chart-box">
            <h5 class="text-center mb-3">Usuarios registrados por mes</h5>
            <canvas id="usuariosChart"></canvas>
        </div>
        <div class="col-md-6 chart-box">
            <h5 class="text-center mb-3">Pedidos por estado</h5>
            <canvas id="pedidosChart"></canvas>
        </div>
        <div class="col-md-6 chart-box mt-3">
            <h5 class="text-center mb-3">Top 5 Productos más vendidos</h5>
            <ul>
            <?php foreach($topProductos as $p): ?>
                <li><?= $p['nombre'] ?> - <?= $p['vendidos'] ?> vendidos</li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>

<?php else: ?>
    <?php
    // CRUD de tablas
    if($accion=='editar'||$accion=='nuevo'){
        $fila=[];
        if($accion=='editar'){
            $res=$conn->query("SELECT * FROM $tabla WHERE id='$id'");
            $fila=$res->fetch_assoc();
        }
        $cols=$conn->query("SHOW COLUMNS FROM $tabla");
        echo "<form method='POST' class='card p-4'>";
        if($accion=='editar') echo "<input type='hidden' name='id' value='$id'>";
        while($col=$cols->fetch_assoc()){
            $campo=$col['Field'];
            $valor=$fila[$campo] ?? valor_por_defecto($tabla,$campo);
            $readonly=($campo=='id')?'readonly':'';
            // Campos especiales
            if($tabla=='pedidos' && $campo=='estado'){
                echo "<div class='mb-3'><label class='form-label'>Estado</label>
                <select name='estado' class='form-select'>
                  <option value='pendiente' ".($valor=='pendiente'?'selected':'').">Pendiente</option>
                  <option value='enviado' ".($valor=='enviado'?'selected':'').">Enviado</option>
                  <option value='completado' ".($valor=='completado'?'selected':'').">Completado</option>
                  <option value='cancelado' ".($valor=='cancelado'?'selected':'').">Cancelado</option>
                </select></div>";
            } elseif($tabla=='resenas' && $campo=='calificacion'){
                echo "<div class='mb-3'><label class='form-label'>Calificación</label>
                <select name='calificacion' class='form-select'>";
                for($i=1;$i<=5;$i++){
                    $sel = ($valor==$i)?'selected':'';
                    echo "<option value='$i' $sel>$i</option>";
                }
                echo "</select></div>";
            } else {
                echo "<div class='mb-3'><label class='form-label'>".ucfirst($campo)."</label>
                <input type='text' name='$campo' class='form-control' value='$valor' $readonly></div>";
            }
        }
        echo "<button class='btn btn-success'>Guardar</button>
        <a href='dashboard.php?tabla=$tabla' class='btn btn-secondary'>Cancelar</a></form>";
    } else {
        // Mostrar tabla
        // Consulta especial para detalle_pedido
        if($tabla=='detalle_pedido'){
            $res = $conn->query("
                SELECT dp.id, u.nombre AS usuario, p.nombre AS producto, dp.cantidad, dp.precio
                FROM detalle_pedido dp
                JOIN pedidos pe ON dp.pedido_id = pe.id
                JOIN usuarios u ON pe.usuario_id = u.id
                JOIN productos p ON dp.producto_id = p.id
            ");
        } elseif($tabla=='carrito'){
            $res = $conn->query("
                SELECT c.id, u.nombre AS usuario, p.nombre AS producto, c.cantidad, p.precio, (c.cantidad*p.precio) AS subtotal
                FROM carrito c
                JOIN usuarios u ON c.usuario_id = u.id
                JOIN productos p ON c.producto_id = p.id
            ");
        } else {
            $res=$conn->query("SELECT * FROM $tabla");
        }

        echo "<a href='dashboard.php?tabla=$tabla&accion=nuevo' class='btn btn-primary mb-3'>+ Crear nuevo</a>";
        echo "<div class='table-responsive'><table class='table table-striped'>";
        if($res->num_rows>0){
            echo "<tr>";
            foreach($res->fetch_fields() as $f) echo "<th>{$f->name}</th>";
            echo "<th>Acciones</th></tr>";
            $res->data_seek(0);
            while($row=$res->fetch_assoc()){
                echo "<tr>";
                foreach($row as $v) echo "<td>$v</td>";
                echo "<td>
                    <a href='dashboard.php?tabla=$tabla&accion=editar&id={$row['id']}' class='btn btn-warning btn-sm'>Editar</a>
                    <a href='dashboard.php?tabla=$tabla&accion=eliminar&id={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"¿Eliminar este registro?\")'>Eliminar</a>
                </td></tr>";
            }
        } else {
            echo "<tr><td colspan='99' class='text-center'>No hay registros</td></tr>";
        }
        echo "</table></div>";
    }
    ?>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Gráfico usuarios
const ctx1 = document.getElementById('usuariosChart')?.getContext('2d');
if(ctx1){ new Chart(ctx1,{
    type:'bar',
    data:{labels:['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
    datasets:[{label:'Usuarios registrados', data:[3,4,5,7,2,6,8,10,12,15,18,20], backgroundColor:'#f78fb3'}]},
    options:{responsive:true}
});}

// Gráfico pedidos
const ctx2 = document.getElementById('pedidosChart')?.getContext('2d');
if(ctx2){ new Chart(ctx2,{
    type:'doughnut',
    data:{labels:['Pendiente','Enviado','Completado','Cancelado'], datasets:[{data:[25,15,40,5], backgroundColor:['#f78fb3','#f5cd79','#63cdda','#778beb']}]},
    options:{responsive:true}
});}
</script>
</body>
</html>
