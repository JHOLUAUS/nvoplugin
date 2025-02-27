<?php include('header.php'); ?>
<?php
include('../funciones/conexion.php');

// Verificar si el usuario ha iniciado sesión y si es cobrador o admin
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'Cobrador' && $_SESSION['tipo_usuario'] != 'Administrador')) {
    header('Location: login.php');
    exit();
}

// Obtener el ID del cobrador desde la sesión
global $id_cobrador;
$id_cobrador = $_SESSION['id_usuario'];
global $nombreCobrador;
$nombreCobrador = $_SESSION['nombre_usuario'];
global $tipo_usuario;
$tipo_usuario = $_SESSION['tipo_usuario'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Contratos Asignados</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
</head>
<body>

<section class="contract-table-wrapper">
    <div class="table-container">
    <div class="indicadores">
        <div class="indicador">
            <span class="icono azul"></span>
            <span>Por entregar</span>
        </div>
        <div class="indicador">
            <span class="icono verde"></span>
            <span>Disponible para cobrar</span>
        </div>
        <div class="indicador">
            <span class="icono rojo"></span>
            <span>Cobrado</span>
        </div>
    </div>
    <table class="contract-table">
        <thead>
            <tr>
                <th> </th> <!-- Nueva columna para el estado de cobro -->
                <th>Cliente</th>
                <th>Alias</th>
                <th>Dirección</th>
                <th>Ultimo Abono</th>
                <th>Forma de Pago</th>
                
            </tr>
        </thead>
        <tbody>
        <?php
            // Consulta para obtener los contratos asignados al cobrador junto con la dirección y la fecha del último abono
            $sql = "
            SELECT 
                clientecontrato.id_cliente,  
                clientecontrato.nombre_cliente, 
                clientecontrato.alias_cliente, 
                lugarcobranza.calle_cobranza, 
                lugarcobranza.numero_cobranza, 
                lugarcobranza.departamento_cobranza, 
                lugarcobranza.asentamiento_cobranza, 
                lugarcobranza.municipio_cobranza, 
                lugarcobranza.estado_cobranza,
                folios.forma_pago,
                folios.estado_liquidacion, -- Agregar este campo para filtrar
                clientecontrato.estado_entrega,
                MAX(abonos.fecha_abono) AS ultimo_abono,
                CASE 
                    WHEN clientecontrato.estado_entrega = 'Por entregar' THEN 1
                    WHEN MAX(abonos.fecha_abono) IS NULL OR DATEDIFF(NOW(), MAX(abonos.fecha_abono)) >= 
                        CASE 
                            WHEN folios.forma_pago = 'semanal' THEN 7 
                            WHEN folios.forma_pago = 'quincenal' THEN 15 
                            WHEN folios.forma_pago = 'mensual' THEN 30 
                            ELSE 999 
                        END THEN 2
                    ELSE 3
                END AS orden_estado
            FROM 
                clientecontrato
            INNER JOIN 
                lugarcobranza ON clientecontrato.id_lugarCobranza = lugarcobranza.id_lugarcobranza
            LEFT JOIN 
                folios ON clientecontrato.id_cliente = folios.id_cliente
            LEFT JOIN 
                abonos ON clientecontrato.id_cliente = abonos.id_cliente AND clientecontrato.id_cobrador = abonos.id_cobrador
            WHERE 
                clientecontrato.id_cobrador = ? 
                AND (folios.estado_liquidacion IS NULL OR folios.estado_liquidacion != 'Liquidado')
            GROUP BY 
                clientecontrato.id_cliente
            ORDER BY 
                orden_estado ASC";      

            // Prepara la consulta
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error en la preparación de la consulta: " . $conn->error);
            }

            $stmt->bind_param('i', $id_cobrador); // Pasa el ID del cobrador
            $stmt->execute();
            $result = $stmt->get_result();

            // Mostrar los contratos
            while ($row = $result->fetch_assoc()) {

                $ultimoAbono = null;
                $estadoEntrega = $row['estado_entrega'];

                // Verificar el estado de entrega primero
                if ($estadoEntrega === 'Por entregar') {
                    $estadoCobro = ' ';
                    $estadoClase = 'estado-entrega-por-entregar'; // Clase azul
                } else {
                    // Obtener la forma de pago
                    $formaPago = strtolower($row['forma_pago']); // semanal, quincenal, mensual

                    // Obtener la fecha del último abono o establecer una fecha por defecto si no hay abonos
                    $ultimoAbono = $row['ultimo_abono'] ? new DateTime($row['ultimo_abono']) : null;

                    // Calcular el estado de cobro
                    $estadoCobro = ' '; // Valor por defecto
                    $estadoClase = 'estado-cobro-disponible'; // Clase por defecto (verde)

                    if ($ultimoAbono) {
                        $fechaActual = new DateTime(); // Fecha actual

                        // Calculamos la diferencia en días
                        $diferenciaDias = $fechaActual->diff($ultimoAbono)->days;

                        // Establecer el límite de días según la forma de pago
                        $limiteDias = 0;
                        switch ($formaPago) {
                            case 'semanal':
                                $limiteDias = 7;
                                break;
                            case 'quincenal':
                                $limiteDias = 15;
                                break;
                            case 'mensual':
                                $limiteDias = 30;
                                break;
                            default:
                                $limiteDias = PHP_INT_MAX;
                                break;
                        }

                        // Comparar la diferencia de días con el límite
                        if ($diferenciaDias >= $limiteDias) {
                            $estadoCobro = ' ';
                            $estadoClase = 'estado-cobro-disponible'; // Verde
                        } else {
                            $estadoCobro = ' ';
                            $estadoClase = 'estado-cobro-pendiente'; // Rojo
                        }
                    }
                }
            ?>
                <tr>
                    <td data-label="Estado de Cobro" class="<?php echo $estadoClase; ?>">
                        <?php echo $estadoCobro; ?>
                    </td>
                    <td data-label="Cliente">
                        <a href="informacion_de_contrato.php?id_cliente=<?php echo $row['id_cliente']; ?>&nombre_cobrador=<?php echo urlencode($nombreCobrador); ?> &id_cobrador=<?php echo urlencode($id_cobrador);?>&tipo_usuario=<?php echo urlencode($tipo_usuario);?>">
                            <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                        </a>
                    </td>
                    <td data-label="Alias">
                        <?php echo htmlspecialchars($row['alias_cliente']); ?>
                    </td>
                    <td data-label="Dirección">
                        <?php echo htmlspecialchars($row['calle_cobranza']) . ' No. ' . htmlspecialchars($row['numero_cobranza']) . ', ' . htmlspecialchars($row['departamento_cobranza']) . ', ' . htmlspecialchars($row['asentamiento_cobranza']) . ', ' . htmlspecialchars($row['municipio_cobranza']) . ', ' . htmlspecialchars($row['estado_cobranza']); ?>
                    </td>

                    <td data-label="Último Abono">
                        <?php echo $ultimoAbono ? $ultimoAbono->format('Y-m-d') : 'Sin abonos'; ?>
                    </td>
                    <td data-label="Forma de Pago">
                        <?php echo htmlspecialchars($row['forma_pago']);?>
                    </td>
                </tr>
            <?php
            }

            // Cierra la consulta
            $stmt->close();
            ?>
        </tbody>
    </table>
</div>
</section>

</body>
</html>