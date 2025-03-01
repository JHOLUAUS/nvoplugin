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
$query = "SELECT cantidad_abono, forma_pago_abono 
          FROM abonos 
          WHERE id_cobrador = ? AND DATE(fecha_abono) BETWEEN ? AND ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $id_cobrador, $fecha_inicio, $fecha_fin);
$stmt->execute();
$result = $stmt->get_result();

$abonos = [];
$total_abonos = 0;
$total_efectivo = 0;
$total_tarjeta = 0;
$total_transferencia = 0;

// Agrupar los abonos por cantidad y sumar los totales
while ($row = $result->fetch_assoc()) {
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

// Cerrar la conexión
$stmt->close();
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
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
        }
        .container {
            margin-top: 50px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .total-row {
            font-weight: bold;
        }
        .ticket {
            border: 1px solid black;
            padding: 20px;
            background-color: #fff;
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
            <h2>Corte de Caja - Cobrador: <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></h2>
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
                    </tr>
                </tbody>
            </table>
            
            <h4>Resumen de Formas de Pago</h4>
            <ul>
                <li>Total Efectivo: $<?php echo number_format($total_efectivo, 2); ?></li>
                <li>Total Tarjeta: $<?php echo number_format($total_tarjeta, 2); ?></li>
                <li>Total Transferencia: $<?php echo number_format($total_transferencia, 2); ?></li>
                <li>Total General: $<?php echo number_format($total_abonos, 2); ?></li>
            </ul>
        </div>
    </div>
</body>
</html>
