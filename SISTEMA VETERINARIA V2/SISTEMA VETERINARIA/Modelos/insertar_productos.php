<?php
include '../Config/conexion.php'; // Archivo con conexión a base de datos

// Obtener datos del formulario
$codigo = $_POST['codigo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio = $_POST['precio'] ?? 0;
$stock = $_POST['stock'] ?? 0;

// Validar que no estén vacíos
if(empty($codigo) || empty($nombre) || $precio <= 0 || $stock < 0){
    echo "<script>alert('Por favor completa todos los campos correctamente.'); window.history.back();</script>";
    exit;
}
$estado = 'activo'; // Estado por defecto al insertar

// Preparar y ejecutar inserción segura con prepared statement
$stmt = $conexion->prepare("INSERT INTO productos (codigo, nombre, precio, stock, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdiss", $codigo, $nombre, $precio, $stock, $descripcion, $estado);

if($stmt->execute()){
    // Inserción exitosa, redirigir al formulario con alerta (usamos JS)
    echo "<script>alert('Producto agregado correctamente.'); window.location.href = '../Vistas/Agregar_producto.php';</script>";
} else {
    // Error (posible código duplicado)
    echo "<script>alert('Error al agregar producto: ".$stmt->error."'); window.history.back();</script>";
}

$stmt->close();
$conexion->close();
?>
