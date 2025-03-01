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
    <title>Lista de Contratos Asignados</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /*ESTILOS ESTADO ENTREGA*/
        .estado-entrega { 
            background-color: #fbb2b2; /* Un color de fondo suave y cálido */
            color: #000000; /* Color de texto naranja */
            padding: 3px 10px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            margin-left: 10px;
            font-size: 0.9em;
        }

        .estado-entrega i {
            margin-right: 5px; /* Espacio entre el icono y el texto */
        }

        .contract-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .contract-table td:nth-child(1) {
            text-align: center;
            background-color:rgb(248, 237, 237);
            font-weight: bold;
        }
        
            .contract-table th,
    .contract-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .contract-table th:nth-child(1),
    .contract-table td:nth-child(1) {
        min-width: 120px; /* Ancho mínimo para la columna del folio */
        width: 15%; /* Proporción del ancho de la tabla */
    }
    </style>
</head>
<body>

<section class="contract-table-wrapper">
    <div class="table-container">
        <div class="search-container">
            <form method="GET" action="">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Buscar contratos..." 
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <table class="contract-table" id="tabla-colorcitos">
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
            // Consulta SQL optimizada
            $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

            $sql = "
            SELECT 
                clientecontrato.id_cliente,
                clientecontrato.nombre_cliente,
                clientecontrato.alias_cliente,
                clientecontrato.estado_entrega,
                CONCAT_WS(', ', lugarcobranza.calle_cobranza, CONCAT('No. ', lugarcobranza.numero_cobranza), lugarcobranza.departamento_cobranza, lugarcobranza.asentamiento_cobranza, lugarcobranza.municipio_cobranza, lugarcobranza.estado_cobranza) AS direccion,
                folios.forma_pago,
                folios.folios,
                folios.id_folio,
                MAX(abonos.fecha_abono) AS ultimo_abono,
                CASE 
                    -- Mostrar siempre si está 'Por entregar'
                    WHEN clientecontrato.estado_entrega = 'Por entregar' THEN 'Mostrar'
                    
                    -- Mostrar si no hay abonos registrados
                    WHEN MAX(abonos.fecha_abono) IS NULL THEN 'Mostrar'
                    
                    -- Mostrar si el último abono no fue esta semana
                    WHEN WEEK(MAX(abonos.fecha_abono), 1) != WEEK(CURDATE(), 1) THEN 'Mostrar'
                    
                    -- Evaluar dependiendo de la forma de pago
                    ELSE
                        CASE
                            WHEN folios.forma_pago = 'semanal' AND WEEK(MAX(abonos.fecha_abono), 1) = WEEK(CURDATE(), 1) THEN 'No mostrar'
                            WHEN folios.forma_pago = 'quincenal' AND DATEDIFF(CURDATE(), MAX(abonos.fecha_abono)) < 14 THEN 'No mostrar'
                            WHEN folios.forma_pago = 'mensual' AND DATEDIFF(CURDATE(), MAX(abonos.fecha_abono)) < 30 THEN 'No mostrar'
                            ELSE 'Mostrar'
                        END
                END AS mostrar_contrato
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
                AND (clientecontrato.nombre_cliente LIKE ? OR
                    clientecontrato.alias_cliente LIKE ? OR
                    clientecontrato.estado_contrato LIKE ? OR
                    lugarcobranza.calle_cobranza LIKE ? OR
                    lugarcobranza.municipio_cobranza LIKE ? OR
                    lugarcobranza.estado_cobranza LIKE ? OR
                    folios.estado_liquidacion LIKE ? OR
                    folios.forma_pago LIKE ? OR
                    folios.folios LIKE ?
                    )
            GROUP BY 
                clientecontrato.id_cliente
            HAVING 
                mostrar_contrato = 'Mostrar'
            ORDER BY 
                CASE WHEN clientecontrato.estado_entrega = 'Por entregar' THEN 0 ELSE 1 END, 
                folios.folios ASC;
            ";
            
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("isssssssss", $id_cobrador,$search,$search,$search,$search,$search,$search,$search,$search,$search);
        $stmt->execute();
        $result = $stmt->get_result();

            // Mostrar resultados
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['folios']); ?></td>
                        <td>
                            <a href="informacion_de_contrato.php?id_cliente=<?php echo $row['id_cliente']; ?>&nombre_cobrador=<?php echo urlencode($nombreCobrador); ?> &id_cobrador=<?php echo urlencode($id_cobrador);?>&tipo_usuario=<?php echo urlencode($tipo_usuario);?> &folioContrato=<?php echo $row['id_folio'];?>">
                                <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                            </a>
                            <?php if ($row['estado_entrega'] == 'Por entregar') { ?>
                                <span class="estado-entrega">
                                    <i class="fas fa-glasses"></i> <!-- Icono de reloj -->
                                    <strong>Por entregar</strong>
                                </span>
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['alias_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                        <td><?php echo $row['ultimo_abono'] ? date('Y-m-d', strtotime($row['ultimo_abono'])) : 'Sin abonos'; ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($row['forma_pago'])); ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="6">No hay contratos asignados.</td></tr>';
            }
            $stmt->close();
            ?>
            </tbody>
        </table>
    </div>
</section>

</body>
</html>
