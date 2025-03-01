<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Contratos</title>
    <link rel="stylesheet" href="../css/asignar_cobrador.css?v=<?php echo(rand()); ?>"> <!-- Vincula tu archivo CSS -->
</head>
<body>
    <div class="container">
        
        <h1>Asignar Contratos a Cobradores</h1>
        <form action="../funciones/asignar_cobrador.php" method="POST" class="form-asignar">
            <label for="cobrador">Selecciona Cobrador:</label>
            <select id="cobrador" name="id_cobrador" required>
                <?php
                // Conexión a la base de datos
                include('../funciones/conexion.php');

                // Consulta para obtener todos los cobradores
                $result = $conn->query("SELECT id_usuario, nombre_usuario FROM usuarios WHERE tipo_usuario = 'Cobrador'");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id_usuario'] . "'>" . $row['nombre_usuario'] . "</option>";
                }
                ?>
            </select>

            <label for="contratos">Selecciona Contratos:</label>
            <select id="contratos" name="contratos[]" multiple required>
                <?php
                // Consulta para obtener contratos no asignados
                $result = $conn->query("SELECT id_cliente, nombre_cliente, alias_cliente 
                FROM clientecontrato 
                WHERE id_cobrador IS NULL 
                AND estado_contrato = 'Liberado'");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id_cliente'] . "'>" . $row['nombre_cliente'] . " (Alias: " . $row['alias_cliente'] . ")</option>";
                }
                ?>
            </select>

            <button type="submit" class="btn-asignar">Asignar Contratos</button>
        </form>
    </div>
</body>
</html>
