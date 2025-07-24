<?php
include("../Config/conexion.php");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['editProd_codigo'];
    $nombre = $_POST['editProd_nombre'];
    $precio = $_POST['editProd_precio'];
    $stock = $_POST['editProd_stock'];
    $descripcion = $_POST['editProd_descripcion'];

    $stmt = $conexion->prepare("UPDATE productos SET codigo=?, nombre=?, precio=?, stock=?, descripcion=? WHERE id_producto=?");
    $stmt->bind_param("ssdssi", $codigo, $nombre, $precio, $stock, $descripcion, $id);
    $stmt->execute();
    header("Location: Listar_producto.php");
    exit;
}
?>
<?php include("../Layaout/Navbar.php"); ?>
<link rel="stylesheet" href="../estilos/editar_producto.css" />
<div class="editProd_container">
  <h3 class="editProd_titulo">Editar Producto</h3>
  <form method="POST" class="editProd_form">
      <label for="editProd_codigo">Código:</label>
      <input type="text" id="editProd_codigo" name="editProd_codigo" value="<?= htmlspecialchars($producto['codigo']) ?>" required>

      <label for="editProd_nombre">Nombre:</label>
      <input type="text" id="editProd_nombre" name="editProd_nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>

      <label for="editProd_precio">Precio:</label>
      <input type="number" step="0.01" id="editProd_precio" name="editProd_precio" value="<?= htmlspecialchars($producto['precio']) ?>" required>

      <label for="editProd_stock">Stock:</label>
      <input type="number" id="editProd_stock" name="editProd_stock" value="<?= htmlspecialchars($producto['stock']) ?>" required>

      <label for="editProd_descripcion">Descripción:</label>
      <textarea id="editProd_descripcion" name="editProd_descripcion"><?= htmlspecialchars($producto['descripcion']) ?></textarea>

      <button type="submit" class="editProd_boton">Guardar Cambios</button>
  </form>
</div>

<?php include("../Layaout/Footer.php"); ?>
