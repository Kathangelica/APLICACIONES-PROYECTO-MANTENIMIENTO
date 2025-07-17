<?php
include '../Config/conexion.php'; // Archivo con conexión a base de datos

// Obtener datos del formulario
$codigo = $_POST['codigo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$precio = $_POST['precio'] ?? 0;
$stock = $_POST['stock'] ?? 0;

// Validar que no estén vacíos
if(empty($codigo) || empty($nombre) || $precio <= 0 || $stock < 0){
    echo "<script>alert('Por favor completa todos los campos correctamente.'); window.history.back();</script>";
    exit;
}

// Preparar y ejecutar inserción segura con prepared statement
$stmt = $conexion->prepare("INSERT INTO productos (codigo, nombre, precio, stock) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssdi", $codigo, $nombre, $precio, $stock);

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
