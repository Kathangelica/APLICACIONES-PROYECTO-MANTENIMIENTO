<?php
include("../Config/conexion.php");
$query = "SELECT * FROM productos WHERE estado = 'activo' ORDER BY nombre ASC";
$resultado = $conexion->query($query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Lista de Productos</title>
    <link rel="stylesheet" href="../estilos/listar_productos.css" />
</head>

<body>
    <?php include("../Layaout/Navbar.php"); ?>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
        <div class="listaProd_mensaje">Producto eliminado correctamente.</div>
    <?php endif; ?>

    <div class="listaProd_container">
        <h2 class="listaProd_titulo">Listado de Productos</h2>

        <input type="text" id="listaProd_buscador" class="listaProd_buscador" placeholder="Buscar por nombre..." onkeyup="listaProd_filtrarTabla()" />

        <table id="listaProd_tabla" class="listaProd_tabla">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Stock</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['codigo']) ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>
                        <td>$<?= number_format($row['precio'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include("../Layaout/Footer.php"); ?>

    <script>
        function listaProd_filtrarTabla() {
            let input = document.getElementById("listaProd_buscador").value.toLowerCase();
            let filas = document.querySelectorAll("#listaProd_tabla tbody tr");

            filas.forEach(fila => {
                let nombre = fila.cells[1].innerText.toLowerCase();
                fila.style.display = nombre.includes(input) ? "" : "none";
            });
        }
    </script>
</body>

</html>
