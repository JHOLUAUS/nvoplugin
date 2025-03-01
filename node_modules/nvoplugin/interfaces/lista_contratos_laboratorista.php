<?php include('header.php'); ?>
<?php
include('../funciones/conexion.php');

// Verificar si el usuario ha iniciado sesión y si es laboratorista
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'Laboratorista' && $_SESSION['tipo_usuario'] != 'Administrador')) {
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Contratos</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
</head>
<body>


    <section class="contract-table-wrapper">
    <div class="table-container">
        <table class="contract-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Alias</th>
                    <th>Dirección</th>
                    <th>Estado Contrato</th>
                    
                    <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                        <th>Cobrador Asignado</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta para obtener todos los contratos, junto con la dirección de cobranza
                $sql = "
                    SELECT 
                        clientecontrato.id_cliente,  /* Incluir el id del contrato */
                        clientecontrato.nombre_cliente, 
                        clientecontrato.alias_cliente, 
                        clientecontrato.estado_contrato,
                        lugarcobranza.calle_cobranza, 
                        lugarcobranza.numero_cobranza, 
                        lugarcobranza.departamento_cobranza, 
                        lugarcobranza.asentamiento_cobranza, 
                        lugarcobranza.municipio_cobranza, 
                        lugarcobranza.estado_cobranza,
                        usuarios.nombre_usuario AS cobrador_nombre  /* Nombre del cobrador */
                    FROM 
                        clientecontrato
                    INNER JOIN 
                        lugarcobranza ON clientecontrato.id_lugarCobranza = lugarcobranza.id_lugarcobranza
                    LEFT JOIN
                        usuarios ON clientecontrato.id_cobrador = usuarios.id_usuario
                    WHERE estado_contrato = 'No Liberado'";

                // Prepara la consulta
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Error en la preparación de la consulta: " . $conn->error);
                }

                $stmt->execute();
                $result = $stmt->get_result();

                // Mostrar los contratos
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td data-label="Cliente">
                            <a href="informacion_de_contrato_lab.php?id_cliente=<?php echo $row['id_cliente']; ?>">
                                <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                            </a>
                        </td>
                        <td data-label="Alias">
                            <?php echo htmlspecialchars($row['alias_cliente']); ?>
                        </td>
                        <td data-label="Dirección">
                            <?php echo htmlspecialchars($row['calle_cobranza']) . ' No. ' . htmlspecialchars($row['numero_cobranza']) . ', ' . htmlspecialchars($row['departamento_cobranza']) . ', ' . htmlspecialchars($row['asentamiento_cobranza']) . ', ' . htmlspecialchars($row['municipio_cobranza']) . ', ' . htmlspecialchars($row['estado_cobranza']); ?>
                        </td>
                        <td data-label="Estado Contrato">
                            <?php echo htmlspecialchars($row['estado_contrato']); ?>
                        </td>



                        <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                            <td data-label="Cobrador Asignado">
                                <?php echo htmlspecialchars($row['cobrador_nombre']); ?>
                            </td>
                        <?php endif; ?>

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
