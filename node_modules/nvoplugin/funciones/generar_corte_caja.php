<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Conectar a la base de datos
include '../funciones/conexion.php';

$id_cobrador = $_SESSION['id_usuario']; // Obtener el id del cobrador de la sesión
$fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d'); // Fecha de inicio (o actual)
$fecha_fin = $_POST['fecha_fin'] ?? date('Y-m-d'); // Fecha de fin (o actual)

// Consulta para obtener abonos en el rango de fechas
// Consulta para obtener abonos en el rango de fechas y del usuario seleccionado
$query_abonos = "SELECT cantidad_abono, forma_pago_abono 
                 FROM abonos 
                 WHERE DATE(fecha_abono) BETWEEN ? AND ? 
                 AND id_cobrador = ? 
                 AND tipo_abono = 'Abono'";

$stmt_abonos = $conn->prepare($query_abonos);
$stmt_abonos->bind_param("ssi", $fecha_inicio, $fecha_fin, $id_cobrador);
$stmt_abonos->execute();
$result_abonos = $stmt_abonos->get_result();

$abonos = [];
$total_abonos = 0;
$total_efectivo = 0;
$total_tarjeta = 0;
$total_transferencia = 0;

// Agrupar los abonos por cantidad y sumar los totales
while ($row = $result_abonos->fetch_assoc()) {
    $cantidad = $row['cantidad_abono'];
    $forma_pago = $row['forma_pago_abono'];

    // Contabilizar abonos por cantidad
    if (!isset($abonos[$cantidad])) {
        $abonos[$cantidad] = 0;
    }
    $abonos[$cantidad]++;

    // Sumar totales por forma de pago
    $total_abonos += $cantidad;
    if ($forma_pago == 'Efectivo') {
        $total_efectivo += $cantidad;
    } elseif ($forma_pago == 'Tarjeta') {
        $total_tarjeta += $cantidad;
    } elseif ($forma_pago == 'Transferencia') {
        $total_transferencia += $cantidad;
    }
}

// Precios de los productos (esto lo puedes ajustar según tus necesidades)
$precios_productos = [
    'Spray' => 100,
    'Gotas' => 250,
    'Póliza' => 250,
    'Enganche 100+100' => 200
];

$query_productos = "SELECT cantidad_abono, tipo_abono, forma_pago_abono 
                    FROM abonos 
                    WHERE DATE(fecha_abono) BETWEEN ? AND ? 
                    AND id_cobrador = ? 
                    AND tipo_abono IN ('Spray', 'Gotas', 'Póliza', 'Enganche 100+100')";

$stmt_productos = $conn->prepare($query_productos);
$stmt_productos->bind_param("ssi", $fecha_inicio, $fecha_fin, $id_cobrador);
$stmt_productos->execute();
$result_productos = $stmt_productos->get_result();

$productos = [];
$total_prod = 0;
$total_efectivo_prod = 0;
$total_tarjeta_prod = 0;
$total_transferencia_prod = 0;

while ($row = $result_productos->fetch_assoc()) {
    $cantidad = $row['cantidad_abono'];
    $tipo_producto = $row['tipo_abono'];
    $forma_pago = $row['forma_pago_abono'];

    // Si el producto no está en el arreglo, lo agregamos
    if (!isset($productos[$tipo_producto])) {
        $productos[$tipo_producto] = ["cantidad" => 0, "num_productos" => 0, "precio" => $precios_productos[$tipo_producto]];
    }

    // Agregar la cantidad a ese producto
    $productos[$tipo_producto]["cantidad"] += $cantidad;
    $productos[$tipo_producto]["num_productos"]++;

    // Sumar totales por forma de pago para productos
    $total_prod += $cantidad;
    if ($forma_pago == 'Efectivo') {
        $total_efectivo_prod += $cantidad;
    } elseif ($forma_pago == 'Tarjeta') {
        $total_tarjeta_prod += $cantidad;
    } elseif ($forma_pago == 'Transferencia') {
        $total_transferencia_prod += $cantidad;
    }
}

// Cerrar la conexión
$stmt_abonos->close();
$stmt_productos->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corte de Caja</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            width: 80mm;
        }

        .receipt {
            padding: 10px;
            text-align: center;
            width: 100%;
        }

        .receipt h4 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .receipt .table {
            width: 100%;
            margin-bottom: 15px;
        }

        .receipt .table th, .receipt .table td {
            text-align: left;
            font-size: 14px;
            padding: 4px 0;
        }

        .receipt .table th {
            border-bottom: 1px solid #000;
        }

        .receipt .table td {
            padding-left: 5px;
        }

        .receipt .total-row {
            font-weight: bold;
            border-top: 1px solid #000;
            margin-top: 10px;
        }

        .receipt .total-row td {
            padding-top: 10px;
        }

        .receipt .total-row td:last-child {
            text-align: right;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #555;
        }
    </style>

    <script>
        // Función para imprimir automáticamente al cargar la página
        window.onload = function() {
            window.print(); // Imprime el contenido de la página
            setTimeout(function(){
                window.close(); // Cierra la pestaña o ventana después de imprimir
            }, 2000); // Ajusta el tiempo de espera si es necesario
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="ticket">
            <h2>Corte de Caja: <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>
            <h4>Fecha: <?php echo $fecha_inicio;?> al <?php echo $fecha_fin;?></h4>
            
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Número de Abonos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($abonos as $cantidad => $num_abonos): ?>
                        <tr>
                            <td><?php echo "$" . number_format($cantidad, 2); ?></td>
                            <td><?php echo $num_abonos; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td>Total Abonos</td>
                        <td><?php echo array_sum($abonos); ?></td>
                        <td>$<?php echo number_format($total_abonos, 2); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <h4>Resumen de Abonos</h4>
            <ul>
            <b><li>Total Efectivo:</b> $<?php echo number_format($total_efectivo, 2); ?></li>
                <b><li>Total Tarjeta:</b> $<?php echo number_format($total_tarjeta, 2); ?></li>
                <b><li>Total Transferencia:</b> $<?php echo number_format($total_transferencia, 2); ?></li>
                <b><li>Total General:</b> $<?php echo number_format($total_abonos, 2); ?></li>
            </ul>

            <h4>Productos Vendidos</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Número de Abonos</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto => $datos): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto); ?></td>
                            <td><?php echo "$" . number_format($datos['precio'], 2); ?></td>
                            <td><?php echo $datos['num_productos']; ?></td>
                            <td><?php echo "$" . number_format($datos['num_productos'] * $datos['precio'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td>Total Productos</td>
                        <td></td>
                        <td>
                            <?php 
                            $total_productos = 0;
                            foreach ($productos as $producto => $datos) {
                                $total_productos += $datos['num_productos']; // Sumar la cantidad de productos
                            }
                            echo $total_productos; 
                            ?>
                        </td>
                        <td>
                            <?php 
                            $total_valor_productos = 0;
                            foreach ($productos as $producto => $datos) {
                                $total_valor_productos += $datos['cantidad']; // Sumar el valor total de los productos
                            }
                            echo "$" . number_format($total_valor_productos, 2); 
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <h4>Resumen de Productos</h4>
            <ul>
            <b><li>Total Efectivo:</b> $<?php echo number_format($total_efectivo_prod, 2); ?></li>
                <b><li>Total Tarjeta:</b> $<?php echo number_format($total_tarjeta_prod, 2); ?></li>
                <b><li>Total Transferencia:</b> $<?php echo number_format($total_transferencia_prod, 2); ?></li>
                <b><li>Total General:</b> $<?php echo number_format($total_prod, 2); ?></li>
            </ul>


        </div>
    </div>
</body>
</html>
