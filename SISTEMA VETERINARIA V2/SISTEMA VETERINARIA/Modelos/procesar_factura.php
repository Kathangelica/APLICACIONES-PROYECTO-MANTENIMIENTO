<?php
include '../Config/conexion.php'; // Archivo con conexión a base de datos

// Recibir datos del formulario
$nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
$email_cliente = trim($_POST['email_cliente'] ?? '');
$cedula_cliente = trim($_POST['cedula_cliente'] ?? '');
$direccion_cliente = trim($_POST['direccion_cliente'] ?? '');
$productos_json = $_POST['productos'] ?? '';

// Validar campos obligatorios
if (!$nombre_cliente || !$email_cliente || !$cedula_cliente || !$direccion_cliente || !$productos_json) {
    echo "<script>
        alert('Error: Todos los campos son obligatorios.');
        window.history.back();
    </script>";
    exit;
}

// Decodificar productos
$productos = json_decode($productos_json, true);
if (!$productos || !is_array($productos) || count($productos) == 0) {
    echo "<script>
        alert('Error: No hay productos para facturar.');
        window.history.back();
    </script>";
    exit;
}

// Generar código de factura único
$codigo_factura = "VET-" . date('Y') . str_pad(rand(1, 99999), 5, "0", STR_PAD_LEFT);

// Verificar que el código no exista (opcional, para mayor seguridad)
$check_codigo = $conexion->prepare("SELECT id_factura FROM factura WHERE codigo_factura = ?");
$check_codigo->bind_param("s", $codigo_factura);
$check_codigo->execute();
if ($check_codigo->get_result()->num_rows > 0) {
    // Si existe, generar otro
    $codigo_factura = "VET-" . date('Y') . str_pad(rand(1, 99999), 5, "0", STR_PAD_LEFT);
}
$check_codigo->close();

// Calcular totales
$subtotal = 0;
$productos_validados = [];

// Validar productos y calcular subtotal
foreach ($productos as $p) {
    $id_producto = $p['id'];
    $cantidad = intval($p['cantidad']);
    $precio_enviado = floatval($p['precio']);
    
    // Verificar que el producto existe y obtener datos actuales
    $stmt_producto = $conexion->prepare("SELECT nombre, precio, stock FROM productos WHERE id_producto = ?");
    $stmt_producto->bind_param("i", $id_producto);
    $stmt_producto->execute();
    $result_producto = $stmt_producto->get_result();
    
    if ($result_producto->num_rows == 0) {
        echo "<script>
            alert('Error: El producto con ID $id_producto no existe.');
            window.history.back();
        </script>";
        exit;
    }
    
    $producto_db = $result_producto->fetch_assoc();
    $stmt_producto->close();
    
    // Verificar stock disponible
    if ($producto_db['stock'] < $cantidad) {
        echo "<script>
            alert('Error: No hay suficiente stock para el producto " . addslashes($producto_db['nombre']) . ". Stock disponible: " . $producto_db['stock'] . "');
            window.history.back();
        </script>";
        exit;
    }
    
    // Usar el precio actual de la base de datos por seguridad
    $precio_actual = floatval($producto_db['precio']);
    $total_producto = $precio_actual * $cantidad;
    $subtotal += $total_producto;
    
    $productos_validados[] = [
        'id' => $id_producto,
        'nombre' => $producto_db['nombre'],
        'cantidad' => $cantidad,
        'precio' => $precio_actual,
        'total' => $total_producto
    ];
}

// Calcular IVA (15%) y total
$iva = $subtotal * 0.15;
$total_con_iva = $subtotal + $iva;

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Insertar factura
    $stmt_factura = $conexion->prepare("INSERT INTO factura (codigo_factura, nombre_cliente, email, cedula, direccion, subtotal, iva, total, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt_factura->bind_param("sssssddd", $codigo_factura, $nombre_cliente, $email_cliente, $cedula_cliente, $direccion_cliente, $subtotal, $iva, $total_con_iva);
    
    if (!$stmt_factura->execute()) {
        throw new Exception("Error al insertar factura: " . $stmt_factura->error);
    }
    
    $id_factura = $stmt_factura->insert_id;
    $stmt_factura->close();

    // Insertar detalles y actualizar stock
    $stmt_detalle = $conexion->prepare("INSERT INTO detalle_factura (id_factura, id_producto, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)");
    $stmt_update_stock = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");

    foreach ($productos_validados as $p) {
        // Insertar detalle
        $stmt_detalle->bind_param("iiidd", $id_factura, $p['id'], $p['cantidad'], $p['precio'], $p['total']);
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al insertar detalle: " . $stmt_detalle->error);
        }

        // Actualizar stock
        $stmt_update_stock->bind_param("ii", $p['cantidad'], $p['id']);
        if (!$stmt_update_stock->execute()) {
            throw new Exception("Error al actualizar stock: " . $stmt_update_stock->error);
        }
    }

    $stmt_detalle->close();
    $stmt_update_stock->close();

    // Confirmar transacción
    $conexion->commit();
    
    // Redirigir a página de éxito
    echo "<script>
        alert('✅ Factura generada correctamente');
        window.location.href='../Vistas/comprobante.php?id=$id_factura';
    </script>";
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conexion->rollback();
    
    echo "<script>
        alert('Error al procesar la factura: " . addslashes($e->getMessage()) . "');
        window.history.back();
    </script>";
}

$conexion->close();
exit;
?>