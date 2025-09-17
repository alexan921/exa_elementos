<?php
require 'conexion.php';

header('Content-Type: application/json');

try {
    $documento = $_POST['prov_documento'];
    $nombre    = $_POST['prov_nombre'];
    $email     = $_POST['prov_email'];
    $fecha     = $_POST['fecha_compra'];

    // 1. Verificar si proveedor ya existe
    $stmt = $PDO->prepare("SELECT id_proveedor FROM proveedores WHERE documento = ?");
    $stmt->execute([$documento]);
    $prov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prov) {
        $stmt = $PDO->prepare("INSERT INTO proveedores (documento, nombre, email) VALUES (?, ?, ?)");
        $stmt->execute([$documento, $nombre, $email]);
        $provId = $PDO->lastInsertId();
    } else {
        $provId = $prov['id_proveedor'];
    }

    // 2. Insertar compra
    $stmt = $PDO->prepare("INSERT INTO compras (fecha_compra, id_proveedor) VALUES (?, ?)");
    $stmt->execute([$fecha, $provId]);
    $compraId = $PDO->lastInsertId();

    // 3. Insertar productos y detalles
    $codigos  = $_POST['codigo'];
    $nombres  = $_POST['nombre_producto'];
    $marcas   = $_POST['marca'];
    $cantidades = $_POST['cantidad'];
    $valores  = $_POST['valor_unitario'];

    for ($i=0; $i<count($codigos); $i++) {
        // Insertar producto si no existe
        $stmt = $PDO->prepare("SELECT id_producto FROM productos WHERE codigo = ?");
        $stmt->execute([$codigos[$i]]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prod) {
            $stmt = $PDO->prepare("INSERT INTO productos (codigo, nombre, marca, valor_unitario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$codigos[$i], $nombres[$i], $marcas[$i], $valores[$i]]);
            $prodId = $PDO->lastInsertId();
        } else {
            $prodId = $prod['id_producto'];
        }

        // Insertar detalle
        $valor_total = $cantidades[$i] * $valores[$i];
        $stmt = $PDO->prepare("INSERT INTO detalle_compras (id_compra, id_producto, cantidad, valor_total) VALUES (?, ?, ?, ?)");
        $stmt->execute([$compraId, $prodId, $cantidades[$i], $valor_total]);
    }

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
