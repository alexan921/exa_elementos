<?php
require 'conexion.php';

if (!isset($_FILES['csvfile'])) {
  die("No se subió archivo.");
}
if ($_FILES['csvfile']['error'] !== UPLOAD_ERR_OK) {
  die("Error al subir.");
}

$tmp = $_FILES['csvfile']['tmp_name'];
if (($handle = fopen($tmp, 'r')) === false) {
  die("No se pudo abrir archivo.");
}

// Usamos $PDO (de conexion.php)
$PDO->beginTransaction();

try {
  $row = 0;
  while (($data = fgetcsv($handle, 0, ',')) !== false) {
    $row++;
    if ($row == 1) continue; // saltar encabezado CSV
    if (count($data) < 9) continue;

    list($prov_doc,$prov_nombre,$prov_email,$fecha_compra,$codigo,$nombre_producto,$marca,$cantidad,$valor_unitario) = array_map('trim', $data);

    if (!$prov_doc || !$codigo) continue;

    // Proveedor (insertar si no existe)
    $stmt = $PDO->prepare("SELECT id_proveedor FROM proveedores WHERE documento = ?");
    $stmt->execute([$prov_doc]);
    $prov_id = $stmt->fetchColumn();

    if (!$prov_id) {
      $stmt = $PDO->prepare("INSERT INTO proveedores (documento, nombre, email) VALUES (?, ?, ?)");
      $stmt->execute([$prov_doc, $prov_nombre, $prov_email]);
      $prov_id = $PDO->lastInsertId();
    }

    // Compra
    $stmt = $PDO->prepare("INSERT INTO compras (fecha_compra, id_proveedor) VALUES (?, ?)");
    $stmt->execute([$fecha_compra, $prov_id]);
    $compra_id = $PDO->lastInsertId();

    // Producto (insertar si no existe)
    $stmt = $PDO->prepare("SELECT id_producto FROM productos WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $prod_id = $stmt->fetchColumn();

    if (!$prod_id) {
      $stmt = $PDO->prepare("INSERT INTO productos (codigo, nombre, marca, valor_unitario) VALUES (?, ?, ?, ?)");
      $stmt->execute([$codigo, $nombre_producto, $marca, floatval($valor_unitario)]);
      $prod_id = $PDO->lastInsertId();
    }

    // Detalle de la compra
    $vt = round(floatval($cantidad) * floatval($valor_unitario), 2);
    $stmt = $PDO->prepare("INSERT INTO detalle_compras (id_compra, id_producto, cantidad, valor_total) VALUES (?, ?, ?, ?)");
    $stmt->execute([$compra_id, $prod_id, floatval($cantidad), $vt]);
  }

  $PDO->commit();
  fclose($handle);
  header('Location:index.html?msg=import_ok');
  exit;

} catch (Exception $e) {
  $PDO->rollBack();
  fclose($handle);
  die('Error importando CSV: ' . $e->getMessage());
}
?>
