<?php
include("../Config/conexion.php");

if (isset($_GET['id_producto'])) {
    $id = $_GET['id_producto'];

    $stmt = $conexion->prepare("UPDATE productos SET estado = 'inactivo' WHERE id_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: ../Vistas/Listar_producto.php?mensaje=desactivado");
    } else {
        header("Location: ../Vistas/Listar_producto.php?mensaje=no_encontrado");
    }
    exit;
} else {
    header("Location: ../Vistas/Listar_producto.php?mensaje=invalido");
    exit;
}
