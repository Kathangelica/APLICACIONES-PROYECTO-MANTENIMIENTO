<?php
include("../Config/conexion.php");

// Buscar productos (opcional)
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : "";
$sql = "SELECT * FROM productos WHERE nombre LIKE ? AND estado = 'activo' ORDER BY nombre ASC";
$stmt = $conexion->prepare($sql);
$like = "%$busqueda%";
$stmt->bind_param("s", $like);
$stmt->execute();
$resultado = $stmt->get_result();
?>
<link rel="stylesheet" href="../estilos/modificar_producto.css" />
<?php include("../Layaout/Navbar.php"); ?>
<br><br>
<form method="GET" action="">
    <input type="text" name="buscar" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>">
    <button type="submit">Buscar</button>
</form>

<table border="1">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($producto = $resultado->fetch_assoc()) { ?>
            <tr>
                <td><?= $producto['codigo'] ?></td>
                <td><?= $producto['nombre'] ?></td>
                <td><?= $producto['precio'] ?></td>
                <td><?= $producto['stock'] ?></td>
                <td><?= $producto['descripcion'] ?></td>
                <td>
                    <a href="editar_producto.php?id=<?= $producto['id_producto'] ?>">Editar</a> |
                    <a href="../Modelos/eliminar_productos.php?id_producto=<?= $producto['id_producto'] ?>" onclick="return confirm('¿Seguro de eliminar este producto?')">Eliminar</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php include("../Layaout/Footer.php"); ?>