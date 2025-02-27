<?php include('header.php'); ?>
<?php
include('../funciones/conexion.php');

// Verificar si el usuario está autenticado y tiene permisos
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'Cobrador' && $_SESSION['tipo_usuario'] != 'Administrador')) {
    header('Location: login.php');
    exit();
}

// Variables de sesión
$id_cobrador = $_SESSION['id_usuario'];
$nombreCobrador = $_SESSION['nombre_usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos Excluidos</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        .contract-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .contract-table th,
        .contract-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
    </style>
</head>
<body>

<section class="contract-table-wrapper">
    <div class="table-container">
                <div class="indicadores">
            <div class="indicador">
                <h2>Lista de Contratos Cobrados</h2>
            </div>
        </div>
        <table class="contract-table" id="tabla-excluidos">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Alias</th>
                    <th>Dirección</th>
                    <th>Último Abono</th>
                    <th>Forma de Pago</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Consulta SQL optimizada para contratos excluidos
            $sql_excluidos = "
            SELECT 
                clientecontrato.id_cliente,
                clientecontrato.nombre_cliente,
                clientecontrato.alias_cliente,
                clientecontrato.estado_entrega,
                CONCAT_WS(', ', lugarcobranza.calle_cobranza, CONCAT('No. ', lugarcobranza.numero_cobranza), lugarcobranza.departamento_cobranza, lugarcobranza.asentamiento_cobranza, lugarcobranza.municipio_cobranza, lugarcobranza.estado_cobranza) AS direccion,
                folios.forma_pago,
                folios.folios,
                folios.id_folio,
                MAX(abonos.fecha_abono) AS ultimo_abono
            FROM 
                clientecontrato
            INNER JOIN 
                lugarcobranza ON clientecontrato.id_lugarCobranza = lugarcobranza.id_lugarcobranza
            LEFT JOIN 
                folios ON clientecontrato.id_cliente = folios.id_cliente
            LEFT JOIN 
                abonos ON clientecontrato.id_cliente = abonos.id_cliente 
                AND abonos.tipo_abono = 'Abono'
            WHERE 
                clientecontrato.id_cobrador = ? 
                AND folios.estado_liquidacion != 'Liquidado'
            GROUP BY 
                clientecontrato.id_cliente
            HAVING 
                NOT (CASE 
                    WHEN clientecontrato.estado_entrega = 'Por entregar' THEN 'Mostrar'
                    WHEN MAX(abonos.fecha_abono) IS NULL THEN 'Mostrar'
                    WHEN WEEK(MAX(abonos.fecha_abono), 1) != WEEK(CURDATE(), 1) THEN 'Mostrar'
                    ELSE
                        CASE
                            WHEN folios.forma_pago = 'semanal' AND WEEK(MAX(abonos.fecha_abono), 1) = WEEK(CURDATE(), 1) THEN 'No mostrar'
                            WHEN folios.forma_pago = 'quincenal' AND DATEDIFF(CURDATE(), MAX(abonos.fecha_abono)) < 14 THEN 'No mostrar'
                            WHEN folios.forma_pago = 'mensual' AND DATEDIFF(CURDATE(), MAX(abonos.fecha_abono)) < 30 THEN 'No mostrar'
                            ELSE 'Mostrar'
                        END
                END = 'Mostrar')
            ORDER BY 
                folios.folios ASC;
            ";

            $stmt_excluidos = $conn->prepare($sql_excluidos);
            if (!$stmt_excluidos) {
                die("Error en la preparación de la consulta: " . $conn->error);
            }

            $stmt_excluidos->bind_param("i", $id_cobrador);
            $stmt_excluidos->execute();
            $result_excluidos = $stmt_excluidos->get_result();

            // Mostrar resultados
            if ($result_excluidos->num_rows > 0) {
                while ($row = $result_excluidos->fetch_assoc()) {
                    ?>
                    <tr>
                    <td>
                            <a href="informacion_de_contrato.php?id_cliente=<?php echo $row['id_cliente']; ?>&nombre_cobrador=<?php echo urlencode($nombreCobrador); ?> &id_cobrador=<?php echo urlencode($id_cobrador);?>&tipo_usuario=<?php echo urlencode($tipo_usuario);?> &folioContrato=<?php echo $row['id_folio'];?>">
                                <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                            </a>
                        </td>  
                        <td style="text-align: center;">
                            <?php echo htmlspecialchars($row['folios']); ?></td>
                        <td>
                            <a href="informacion_de_contrato.php?id_cliente=<?php echo $row['id_cliente']; ?>&nombre_cobrador=<?php echo urlencode($nombreCobrador); ?> &id_cobrador=<?php echo urlencode($id_cobrador);?>&tipo_usuario=<?php echo urlencode($tipo_usuario);?> &folioContrato=<?php echo $row['id_folio'];?>">
                                <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['alias_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                        <td><?php echo $row['ultimo_abono'] ? date('Y-m-d', strtotime($row['ultimo_abono'])) : 'Sin abonos'; ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($row['forma_pago'])); ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="6">No hay contratos excluidos.</td></tr>';
            }
            $stmt_excluidos->close();
            ?>
            </tbody>
        </table>
    </div>
</section>

</body>
</html>