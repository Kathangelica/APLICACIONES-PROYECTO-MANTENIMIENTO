<?php
include '../Config/conexion.php';

// Obtener productos para mostrar en el select
$query = "SELECT id_producto, codigo, nombre, precio, stock FROM productos WHERE stock > 0";
$result = $conexion->query($query);
$productos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Facturación Electrónica - Veterinaria</title>
    <link rel="stylesheet" href="../estilos/factura_electronica.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        let productosSeleccionados = [];
        let contadorProductos = 0;

        function agregarProductoClick(id, nombre, precio, stock, codigo) {
            // Verificar si el producto ya está en la lista
            let productoExistente = productosSeleccionados.find(p => p.id === id);
            
            if (productoExistente) {
                // Si existe, incrementar cantidad
                if (productoExistente.cantidad < stock) {
                    productoExistente.cantidad++;
                    mostrarNotificacion(`Cantidad aumentada para ${nombre}`, 'success');
                } else {
                    mostrarNotificacion(`No hay más stock disponible para ${nombre}`, 'warning');
                    return;
                }
            } else {
                // Si no existe, agregarlo con cantidad 1
                productosSeleccionados.push({
                    id: id,
                    nombre: nombre,
                    codigo: codigo,
                    precio: parseFloat(precio),
                    cantidad: 1,
                    stock: stock
                });
                mostrarNotificacion(`${nombre} agregado a la factura`, 'success');
            }
            
            actualizarTabla();
        }

        function cambiarCantidad(id, operacion) {
            let producto = productosSeleccionados.find(p => p.id === id);
            if (!producto) return;

            if (operacion === 'aumentar') {
                if (producto.cantidad < producto.stock) {
                    producto.cantidad++;
                } else {
                    mostrarNotificacion('No hay más stock disponible', 'warning');
                    return;
                }
            } else if (operacion === 'disminuir') {
                if (producto.cantidad > 1) {
                    producto.cantidad--;
                } else {
                    // Si la cantidad es 1, eliminar el producto
                    eliminarProducto(id);
                    return;
                }
            }
            
            actualizarTabla();
        }

        function eliminarProducto(id) {
            productosSeleccionados = productosSeleccionados.filter(p => p.id !== id);
            actualizarTabla();
            mostrarNotificacion('Producto eliminado de la factura', 'info');
        }

        function actualizarTabla() {
            const tbody = document.getElementById('productosAgregados');
            tbody.innerHTML = '';

            let subtotal = 0;
            
            if (productosSeleccionados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-message">No hay productos agregados</td></tr>';
            } else {
                productosSeleccionados.forEach((p, index) => {
                    const totalProducto = p.precio * p.cantidad;
                    subtotal += totalProducto;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <div class="producto-info">
                                <strong>${p.nombre}</strong>
                                <small>Código: ${p.codigo}</small>
                            </div>
                        </td>
                        <td>
                            <div class="cantidad-controls">
                                <button type="button" class="btn-cantidad" onclick="cambiarCantidad('${p.id}', 'disminuir')">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="cantidad-display">${p.cantidad}</span>
                                <button type="button" class="btn-cantidad" onclick="cambiarCantidad('${p.id}', 'aumentar')">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </td>
                        <td class="precio">$${p.precio.toFixed(2)}</td>
                        <td class="stock-info">${p.stock} disponibles</td>
                        <td class="total-producto">$${totalProducto.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn-eliminar" onclick="eliminarProducto('${p.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Calcular IVA 15%
            const iva = subtotal * 0.15;
            const total = subtotal + iva;

            document.getElementById('subtotal').innerText = `$${subtotal.toFixed(2)}`;
            document.getElementById('iva').innerText = `$${iva.toFixed(2)}`;
            document.getElementById('total').innerText = `$${total.toFixed(2)}`;

            // Actualizar contador de productos
            document.getElementById('contador-productos').innerText = productosSeleccionados.length;
            
            // Guardar datos en campos ocultos para enviar
            document.getElementById('productos_json').value = JSON.stringify(productosSeleccionados);
        }

        function mostrarNotificacion(mensaje, tipo) {
            const notification = document.createElement('div');
            notification.className = `notification ${tipo}`;
            notification.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                ${mensaje}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        function buscarProductos() {
            const search = document.getElementById('buscar-producto').value.toLowerCase();
            const productos = document.querySelectorAll('.producto-card');
            
            productos.forEach(producto => {
                const nombre = producto.querySelector('.producto-nombre').textContent.toLowerCase();
                const codigo = producto.querySelector('.producto-codigo').textContent.toLowerCase();
                
                if (nombre.includes(search) || codigo.includes(search)) {
                    producto.style.display = 'block';
                } else {
                    producto.style.display = 'none';
                }
            });
        }

        function validarYEnviar() {
            const cliente = document.getElementById('nombre_cliente').value.trim();
            const email = document.getElementById('email_cliente').value.trim();
            const cedula = document.getElementById('cedula_cliente').value.trim();
            const direccion = document.getElementById('direccion_cliente').value.trim();
            
            if (!cliente) {
                mostrarNotificacion('Ingrese el nombre del cliente', 'warning');
                return false;
            }
            if (!email) {
                mostrarNotificacion('Ingrese el email del cliente', 'warning');
                return false;
            }
            if (!cedula) {
                mostrarNotificacion('Ingrese la cédula del cliente', 'warning');
                return false;
            }
            if (!direccion) {
                mostrarNotificacion('Ingrese la dirección del cliente', 'warning');
                return false;
            }
            if (productosSeleccionados.length === 0) {
                mostrarNotificacion('Debe agregar al menos un producto', 'warning');
                return false;
            }
            
            mostrarNotificacion('Generando factura...', 'info');
            return true;
        }

        // Inicializar la tabla cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            actualizarTabla();
        });
    </script>
</head>

<body>
    <?php include '../Layaout/Navbar.php'; ?>

    <div class="container">
        <h2>Facturación Electrónica</h2>
        <p>Complete los datos del cliente y agregue productos a la factura.</p>
        <form action="../Modelos/procesar_factura.php" method="POST" onsubmit="return validarYEnviar()">
            
            <!-- Datos del Cliente -->
            <section class="card">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    <h2>Datos del Cliente</h2>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre_cliente">
                                <i class="fas fa-user"></i> Nombre completo
                            </label>
                            <input type="text" name="nombre_cliente" id="nombre_cliente" required>
                        </div>
                        <div class="form-group">
                            <label for="email_cliente">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" name="email_cliente" id="email_cliente" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cedula_cliente">
                                <i class="fas fa-id-card"></i> Cédula
                            </label>
                            <input type="text" name="cedula_cliente" id="cedula_cliente" required>
                        </div>
                        <div class="form-group">
                            <label for="direccion_cliente">
                                <i class="fas fa-map-marker-alt"></i> Dirección
                            </label>
                            <textarea name="direccion_cliente" id="direccion_cliente" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Selección de Productos -->
            <section class="card">
                <div class="card-header">
                    <i class="fas fa-shopping-cart"></i>
                    <h2>Seleccionar Productos</h2>
                    <div class="search-container">
                        <input type="text" id="buscar-producto" placeholder="Buscar productos..." onkeyup="buscarProductos()">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="productos-grid">
                        <?php foreach ($productos as $prod): ?>
                            <div class="producto-card" onclick="agregarProductoClick('<?= $prod['id_producto'] ?>', '<?= htmlspecialchars($prod['nombre']) ?>', '<?= $prod['precio'] ?>', '<?= $prod['stock'] ?>', '<?= htmlspecialchars($prod['codigo']) ?>')">
                                <div class="producto-info">
                                    <h3 class="producto-nombre"><?= htmlspecialchars($prod['nombre']) ?></h3>
                                    <p class="producto-codigo">Código: <?= htmlspecialchars($prod['codigo']) ?></p>
                                    <div class="producto-details">
                                        <span class="precio">$<?= number_format($prod['precio'], 2) ?></span>
                                        <span class="stock <?= $prod['stock'] < 10 ? 'stock-bajo' : '' ?>">
                                            <i class="fas fa-boxes"></i> <?= $prod['stock'] ?> disponibles
                                        </span>
                                    </div>
                                </div>
                                <div class="producto-action">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Productos Seleccionados -->
            <section class="card">
                <div class="card-header">
                    <i class="fas fa-list"></i>
                    <h2>Productos Seleccionados</h2>
                    <span class="counter">
                        <i class="fas fa-shopping-basket"></i>
                        <span id="contador-productos">0</span> productos
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Stock</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="productosAgregados">
                                <!-- Los productos se agregarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <div class="totales-container">
                        <div class="totales">
                            <div class="total-line">
                                <span>Total:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Campos ocultos -->
            <input type="hidden" name="productos" id="productos_json" />
            <!-- Botón de envío -->
            <div class="submit-container">
                <button type="submit" class="btn-generar">
                    <i class="fas fa-file-invoice"></i>
                    Generar Factura
                </button>
            </div>
        </form>
    </div>

    <?php include '../Layaout/Footer.php'; ?>
</body>

</html>