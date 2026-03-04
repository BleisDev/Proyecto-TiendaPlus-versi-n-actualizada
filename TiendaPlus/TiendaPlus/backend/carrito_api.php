<?php
if(session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php'; // Asegúrate de tener $conn

$usuario_id = $_SESSION['usuario_id'] ?? null;
if(!$usuario_id){
    echo json_encode(['exito'=>false,'mensaje'=>'Debes iniciar sesión']);
    exit;
}

$accion = $_POST['accion'] ?? '';

try {
    switch($accion){

        // 🟢 AGREGAR PRODUCTO
        case 'agregar':
            $producto_id = intval($_POST['id_producto'] ?? 0);
            $cantidad = max(1,intval($_POST['cantidad'] ?? 1));
            if($producto_id<=0){
                echo json_encode(['exito'=>false,'mensaje'=>'ID de producto inválido']);
                exit;
            }
            $stmt=$conn->prepare("
                INSERT INTO carrito (usuario_id, producto_id, cantidad)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE cantidad=VALUES(cantidad)
            ");
            $stmt->bind_param("iii", $usuario_id, $producto_id, $cantidad);
            $ok=$stmt->execute();
            $stmt->close();

            // Contador actualizado
            $stmt=$conn->prepare("SELECT SUM(cantidad) AS total FROM carrito WHERE usuario_id=?");
            $stmt->bind_param("i",$usuario_id);
            $stmt->execute();
            $res=$stmt->get_result()->fetch_assoc();
            $count = intval($res['total']);
            $stmt->close();

            echo json_encode(['exito'=>$ok,'mensaje'=>$ok?'Producto agregado':'Error al agregar','count'=>$count]);
        break;

        // 🟢 ACTUALIZAR CANTIDADES
        case 'actualizar':
            $cantidades = $_POST['cant'] ?? [];
            if(!is_array($cantidades)){
                echo json_encode(['exito'=>false,'mensaje'=>'Datos inválidos']);
                exit;
            }
            foreach($cantidades as $id => $cant){
                $id=intval($id);
                $cant=max(1,intval($cant));
                $stmt=$conn->prepare("UPDATE carrito SET cantidad=? WHERE usuario_id=? AND producto_id=?");
                $stmt->bind_param("iii",$cant,$usuario_id,$id);
                $stmt->execute();
                $stmt->close();
            }
            echo json_encode(['exito'=>true,'mensaje'=>'Cantidades actualizadas']);
        break;

        // 🟢 ELIMINAR PRODUCTO
        case 'eliminar':
            $id_producto=intval($_POST['id_producto'] ?? 0);
            if($id_producto<=0){
                echo json_encode(['exito'=>false,'mensaje'=>'ID de producto inválido']);
                exit;
            }
            $stmt=$conn->prepare("DELETE FROM carrito WHERE usuario_id=? AND producto_id=?");
            $stmt->bind_param("ii",$usuario_id,$id_producto);
            $ok=$stmt->execute();
            $stmt->close();

            // Contador actualizado
            $stmt=$conn->prepare("SELECT SUM(cantidad) AS total FROM carrito WHERE usuario_id=?");
            $stmt->bind_param("i",$usuario_id);
            $stmt->execute();
            $res=$stmt->get_result()->fetch_assoc();
            $count = intval($res['total']);
            $stmt->close();

            echo json_encode(['exito'=>$ok,'mensaje'=>$ok?'Producto eliminado':'No se pudo eliminar','count'=>$count]);
        break;

        // 🟢 LISTAR ITEMS
        case 'listar':
            $stmt=$conn->prepare("
                SELECT c.producto_id, c.cantidad, p.nombre, p.precio, p.imagen
                FROM carrito c
                JOIN productos p ON p.id=c.producto_id
                WHERE c.usuario_id=?
            ");
            $stmt->bind_param("i",$usuario_id);
            $stmt->execute();
            $res=$stmt->get_result();
            $items=[];
            while($row=$res->fetch_assoc()){
                $items[]=[
                    'id'=>$row['producto_id'],
                    'nombre'=>$row['nombre'],
                    'precio'=>$row['precio'],
                    'cantidad'=>$row['cantidad'],
                    'imagen'=>!empty($row['imagen']) && file_exists($row['imagen'])?$row['imagen']:'https://via.placeholder.com/80?text=Producto'
                ];
            }
            $stmt->close();
            echo json_encode(['exito'=>true,'items'=>$items]);
        break;

        default:
            echo json_encode(['exito'=>false,'mensaje'=>'Acción no reconocida']);
    }

} catch(Exception $e){
    echo json_encode(['exito'=>false,'mensaje'=>'Error: '.$e->getMessage()]);
}
