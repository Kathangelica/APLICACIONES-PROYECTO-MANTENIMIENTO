<?php
include 'conexion.php';

if ($conexion) {
    echo "<h2 style='color: green; font-family: Arial;'>✅ Conexión exitosa a la base de datos.</h2>";
} else {
    echo "<h2 style='color: red; font-family: Arial;'>❌ Error de conexión.</h2>";
}
?>
