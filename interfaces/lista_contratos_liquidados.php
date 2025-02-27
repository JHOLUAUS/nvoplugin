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
    <title>Lista de Contratos Liquidados</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
</head>
<body>

<section class="contract-table-wrapper">
    <div class="table-container">
    <div class="indicadores">
        <h2> Contratos Liquidados</h2>
    </div>
    <table class="contract-table">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Alias</th>
                <th>Dirección</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $sql = "
            SELECT 
                clientecontrato.id_cliente,  
                clientecontrato.nombre_cliente, 
                clientecontrato.alias_cliente,
                folios.estado_liquidacion,
                CONCAT(
                    lugarcobranza.calle_cobranza, ' No. ', lugarcobranza.numero_cobranza, ', ',
                    IFNULL(lugarcobranza.departamento_cobranza, ''), ', ',
                    lugarcobranza.asentamiento_cobranza, ', ',
                    lugarcobranza.municipio_cobranza, ', ',
                    lugarcobranza.estado_cobranza
                ) AS direccion_completa
            FROM 
                clientecontrato
            INNER JOIN 
                lugarcobranza ON clientecontrato.id_lugarCobranza = lugarcobranza.id_lugarcobranza
            LEFT JOIN 
                folios ON clientecontrato.id_cliente = folios.id_cliente
            WHERE 
                folios.estado_liquidacion = 'Liquidado'
            GROUP BY 
                clientecontrato.id_cliente
            ORDER BY 
                clientecontrato.nombre_cliente ASC"; // Ordenar por nombre del cliente

            // Prepara la consulta
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("Error en la preparación de la consulta: " . $conn->error);
            }

            // Ejecutar sin parámetros, ya que se eliminó el filtro por cobrador
            $stmt->execute();
            $result = $stmt->get_result();

            // Mostrar los contratos
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td data-label="Cliente">
                        <a href="informacion_de_contrato.php?id_cliente=<?php echo $row['id_cliente']; ?>&nombre_cobrador=<?php echo urlencode($nombreCobrador); ?>&id_cobrador=<?php echo urlencode($id_cobrador); ?>&tipo_usuario=<?php echo urlencode($tipo_usuario); ?>">
                            <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                        </a>
                    </td>
                    <td data-label="Alias">
                        <?php echo htmlspecialchars($row['alias_cliente']); ?>
                    </td>
                    <td data-label="Dirección">
                        <?php echo htmlspecialchars($row['direccion_completa']); ?>
                    </td>
                    <td data-label="Estado">
                        <?php echo htmlspecialchars($row['estado_liquidacion']); ?>
                    </td>
                </tr>
                <?php
            }
            $stmt->close();
            ?>

        </tbody>
    </table>
</div>
</section>

</body>
</html>
