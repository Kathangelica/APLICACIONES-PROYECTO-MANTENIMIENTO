<?php
$host = "localhost";
$usuario = "root";
$contrasena = ""; // Por defecto en XAMPP no hay contraseña
$base_datos = "sistema_veterinaria";

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Opcional: establecer codificación
$conexion->set_charset("utf8");
?>
