<?php
include '../Config/conexion.php';

// Recibir id_factura o código por GET
$id_factura = $_GET['id'] ?? null;
if (!$id_factura) {
    die("Factura no especificada.");
}

// Consultar datos de la factura
$sql_factura = "SELECT codigo_factura, nombre_cliente, email, cedula, direccion, total 
                FROM factura WHERE id_factura = ?";
$stmt = $conexion->prepare($sql_factura);
$stmt->bind_param("i", $id_factura);
$stmt->execute();
$result_factura = $stmt->get_result();
if ($result_factura->num_rows === 0) {
    die("Factura no encontrada.");
}
$factura = $result_factura->fetch_assoc();
$stmt->close();

// Consultar detalles (productos)
$sql_detalles = "SELECT p.codigo, p.nombre, df.cantidad, df.precio_unitario 
                 FROM detalle_factura df
                 JOIN productos p ON df.id_producto = p.id_producto
                 WHERE df.id_factura = ?";
$stmt_detalle = $conexion->prepare($sql_detalles);
$stmt_detalle->bind_param("i", $id_factura);
$stmt_detalle->execute();
$result_detalles = $stmt_detalle->get_result();

$productos = [];
while ($row = $result_detalles->fetch_assoc()) {
    $productos[] = $row;
}
$stmt_detalle->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Comprobante de Factura <?= htmlspecialchars($factura['codigo_factura']) ?></title>
    <style>
        /* Tamaño compacto para comprobante */
        body {
            font-family: Arial, sans-serif;
            max-width: 480px;
            margin: auto;
            padding: 15px;
            background: white;
            color: #000;
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .datos-cliente,
        .productos,
        .totales {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .totales p {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
            text-align: right;
        }

        /* Botón imprimir fijo arriba */
        .btn-imprimir {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-regresar {
            position: fixed;
            top: 50px;
            right: 10px;
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-imprimir:hover {
            background: #2980b9;
        }

        @media print {
            .btn-imprimir {
                display: none;
            }

            body {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir</button>
   <button class="btn-regresar" onclick="location.href='factura_electronica.php'">⟵ Regresar</button>

    <h1>Veterinaria Patitas</h1>
    <h2>Comprobante de Factura</h2>

    <div class="datos-cliente">
        <p><strong>Código:</strong> <?= htmlspecialchars($factura['codigo_factura']) ?></p>
        <p><strong>Cliente:</strong> <?= htmlspecialchars($factura['nombre_cliente']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($factura['email']) ?></p>
        <p><strong>Cédula:</strong> <?= htmlspecialchars($factura['cedula']) ?></p>
        <p><strong>Dirección:</strong> <?= nl2br(htmlspecialchars($factura['direccion'])) ?></p>
    </div>

    <div class="productos">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = 0;
                foreach ($productos as $prod):
                    $subtotal_item = $prod['precio_unitario'] * $prod['cantidad'];
                    $subtotal += $subtotal_item;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['codigo'] . " - " . $prod['nombre']) ?></td>
                        <td><?= $prod['cantidad'] ?></td>
                        <td>$<?= number_format($prod['precio_unitario'], 2) ?></td>
                        <td>$<?= number_format($subtotal_item, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="totales">
        <?php
        $iva = $subtotal * 0.15; // 15% de IVA
        $total = $subtotal + $iva;
        ?>
        <p>Subtotal: $<?= number_format($subtotal, 2) ?></p>
        <p>IVA (15%): $<?= number_format($iva, 2) ?></p>
        <p>Total: $<?= number_format($total, 2) ?></p>
    </div>

</body>

</html>