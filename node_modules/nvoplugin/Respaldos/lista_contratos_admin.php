<?php include('header.php'); ?>
<?php
include('../funciones/conexion.php');

// Verificar si el usuario ha iniciado sesión y si es cobrador o administrador
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'Cobrador' && $_SESSION['tipo_usuario'] != 'Administrador')) {
    header('Location: login.php');
    exit();
}

// Obtener el nombre del cobrador desde la sesión
global $id_cobrador;
$id_cobrador = $_SESSION['id_usuario'];

global $nombreCobrador;
$nombreCobrador = $_SESSION['nombre_usuario'];

global $tipoUsuario;
$tipoUsuario = $_SESSION['tipo_usuario'];
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
                    <th>Liberado/No Liberado</th>
                    <th>Cobrador Asignado</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta para obtener todos los contratos con la dirección de cobranza
                $sql = "
                    SELECT 
                        clientecontrato.id_cliente,  /* Incluir el id del contrato */
                        clientecontrato.nombre_cliente, 
                        clientecontrato.alias_cliente, 
                        clientecontrato.estado_contrato,
                        clientecontrato.id_cobrador,
                        lugarcobranza.calle_cobranza, 
                        lugarcobranza.numero_cobranza, 
                        lugarcobranza.departamento_cobranza, 
                        lugarcobranza.asentamiento_cobranza, 
                        lugarcobranza.municipio_cobranza, 
                        lugarcobranza.estado_cobranza,
                        usuarios.id_usuario,
                        usuarios.nombre_usuario
                    FROM 
                        clientecontrato
                    INNER JOIN 
                        lugarcobranza ON clientecontrato.id_lugarCobranza = lugarcobranza.id_lugarcobranza
                    INNER JOIN
                        usuarios ON clientecontrato.id_cobrador = usuarios.id_usuario";
                
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
                            <a href="informacion_de_contrato.php?id_cliente=<?php echo $row['id_cliente']; ?>&nombre_cobrador=<?php echo urlencode($nombreCobrador); ?> &id_cobrador=<?php echo urlencode($id_cobrador);?> &tipo_usuario=<?php echo urlencode($tipoUsuario); ?>">
                                <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                            </a>
                        </td>
                        <td data-label="Alias">
                            <?php echo htmlspecialchars($row['alias_cliente']); ?>
                        </td>
                        <td data-label="Dirección">
                            <?php echo htmlspecialchars($row['calle_cobranza']) . ' No. ' . htmlspecialchars($row['numero_cobranza']) . ', ' . htmlspecialchars($row['departamento_cobranza']) . ', ' . htmlspecialchars($row['asentamiento_cobranza']) . ', ' . htmlspecialchars($row['municipio_cobranza']) . ', ' . htmlspecialchars($row['estado_cobranza']); ?>
                        </td>
                        <td data-label="Liberado/No Liberado">
                            <?php echo htmlspecialchars($row['estado_contrato']); ?>
                        </td>
                        <td data-label="Cobrador Asignado">
                            <?php echo htmlspecialchars($row['nombre_usuario']); ?>
                        </td>
                        <td>
                            <form action="eliminar_contrato.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este contrato?');">
                                <input type="hidden" name="id_cliente" value="<?php echo $row['id_cliente']; ?>">
                                <button type="submit" class="delete-btn">Eliminar</button>
                            </form>
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
