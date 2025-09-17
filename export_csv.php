<?php
require 'conexion.php';
$desde = !empty($_GET['desde']) ? $_GET['desde'] : null;
$hasta = !empty($_GET['hasta']) ? $_GET['hasta'] : null;
$where = ''; $params = [];
if ($desde) { $where .= " AND c.fecha_compra >= ?"; $params[] = $desde . " 00:00:00"; }
if ($hasta) { $where .= " AND c.fecha_compra <= ?"; $params[] = $hasta . " 23:59:59"; }

$sql = "SELECT p.documento AS proveedor_documento, p.nombre AS proveedor_nombre, p.email AS proveedor_email, c.fecha_compra,
pr.codigo AS codigo_producto, pr.nombre AS nombre_producto, pr.marca AS marca, cd.cantidad, cd.valor_unitario, cd.valor_total
FROM compras c
JOIN proveedores p ON c.proveedor_id = p.id
JOIN compras_detalle cd ON cd.compra_id = c.id
JOIN productos pr ON cd.producto_id = pr.id
WHERE 1=1 $where
ORDER BY c.fecha_compra DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reportes_compras.csv');
$out = fopen('php://output', 'w');
fputcsv($out, ['proveedor_documento','proveedor_nombre','proveedor_email','fecha_compra','codigo_producto','nombre_producto','marca','cantidad','valor_unitario','valor_total']);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  fputcsv($out, $row);
}
fclose($out);
exit;
?>