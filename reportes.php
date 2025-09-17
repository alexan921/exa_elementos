<?php
require 'conexion.php';

$query = "
    SELECT 
        pr.documento,
        pr.nombre AS proveedor,
        pr.email,
        c.fecha_compra,
        p.codigo,
        p.nombre AS producto,
        p.marca,
        dc.cantidad,
        p.valor_unitario,
        dc.valor_total
    FROM detalle_compras dc
    INNER JOIN compras c ON dc.id_compra = c.id_compra
    INNER JOIN proveedores pr ON c.id_proveedor = pr.id_proveedor
    INNER JOIN productos p ON dc.id_producto = p.id_producto
";

$stmt = $PDO->query($query);
$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Compras</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  
</head>
<body class="p-4">
  <div class="container">
    <h2 class="mb-4"><i class="bi bi-table"></i> Reporte de Compras</h2>
    <div class="table-responsive shadow-lg">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Documento</th>
            <th>Proveedor</th>
            <th>Email</th>
            <th>Fecha Compra</th>
            <th>Código</th>
            <th>Producto</th>
            <th>Marca</th>
            <th>Cantidad</th>
            <th>Valor Unitario</th>
            <th>Valor Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($datos as $row): ?>
          <tr>
            <td><?= $row['documento'] ?></td>
            <td><?= $row['proveedor'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['fecha_compra'] ?></td>
            <td><span class="badge bg-secondary"><?= $row['codigo'] ?></span></td>
            <td><?= $row['producto'] ?></td>
            <td><?= $row['marca'] ?></td>
            <td><span class="badge bg-info text-dark"><?= $row['cantidad'] ?></span></td>
            <td>$<?= number_format($row['valor_unitario'], 2) ?></td>
            <td class="fw-bold text-success">$<?= number_format($row['valor_total'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
