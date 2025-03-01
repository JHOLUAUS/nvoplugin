<?php include('header.php'); ?>
<?php
// Incluye el archivo de conexión a la base de datos
include '../funciones/conexion.php';

// Consulta para obtener los datos de los usuarios
$query = "SELECT id_usuario, nombre_usuario,tipo_usuario, estado_usuario, hora_inicio_usuario, hora_fin_usuario FROM usuarios";
$resultado = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Usuarios</title>
    <link rel="stylesheet" href="../css/admin_usuarios.css">
</head>
<body>

    <table>
        <tr>
            <th>Nombre</th>
            <th>Tipo de Usuario</th>
            <th>Estado</th>
            <th>Horario de Acceso</th>
            <th>Acciones</th>
        </tr>
        <?php while ($usuario = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= $usuario['nombre_usuario'] ?></td>
            <td><?= $usuario['tipo_usuario'] ?></td>
            <td><?= $usuario['estado_usuario'] ?></td>
            <td>
                <?= $usuario['hora_inicio_usuario'] ? $usuario['hora_inicio_usuario'] : 'No especificado' ?> - 
                <?= $usuario['hora_fin_usuario'] ? $usuario['hora_fin_usuario'] : 'No especificado' ?>
            </td>
            <td>
                <form action="../funciones/cambiar_estado_usuarios.php" method="post">
                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                    
                    <select name="estado_usuario">
                        <option value="Habilitado" <?= $usuario['estado_usuario'] == 'Habilitado' ? 'selected' : '' ?>>Habilitado</option>
                        <option value="Deshabilitado" <?= $usuario['estado_usuario'] == 'Deshabilitado' ? 'selected' : '' ?>>Deshabilitado</option>
                    </select>
                    
                    <label>Inicio: <input type="time" name="hora_inicio_usuario" value="<?= $usuario['hora_inicio_usuario'] ?>"></label>
                    <label>Fin: <input type="time" name="hora_fin_usuario" value="<?= $usuario['hora_fin_usuario'] ?>"></label>
                    
                    <button type="submit">Actualizar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
