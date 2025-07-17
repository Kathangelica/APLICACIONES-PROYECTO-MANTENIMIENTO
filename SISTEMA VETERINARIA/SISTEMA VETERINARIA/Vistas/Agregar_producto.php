<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agregar Producto - Veterinaria</title>
    <link rel="stylesheet" href="../estilos/agregar_producto.css" />
</head>
<?php include '../Layaout/Navbar.php'; ?>
<body>

    <div class="container">
        <h2>Agregar Producto</h2>
        <form action="../Modelos/insertar_productos.php" method="POST" id="formProducto">
            <label for="codigo">Código:</label>
            <input type="text" id="codigo" name="codigo" required />

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required />

            <label for="precio">Precio:</label>
            <input type="number" step="0.01" min="0" id="precio" name="precio" required />

            <label for="stock">Stock:</label>
            <input type="number" min="0" id="stock" name="stock" required />

            <button type="submit">Agregar Producto</button>
        </form>
    </div>
</body>
<?php include '../Layaout/Footer.php'; ?>
</html>
