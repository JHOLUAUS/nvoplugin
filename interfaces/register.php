<?php include('header.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../css/registro_cliente.css?v=<?php echo(rand()); ?>">
</head>
<body>
    <div class="container">
        

        <form action="../funciones/registrar_usuario.php?v=<?php echo(rand()); ?>" method="post" class="form">

            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="ape_pat">Apellido Paterno:</label>
            <input type="text" id="ape_pat" name="ape_pat">

            <label for="ape_mat">Apellido Materno:</label>
            <input type="text" id="ape_mat" name="ape_mat">

            <label for="tipo_usuario">Optometrista:</label>
            <select id="tipo_usuario" name="tipo_usuario" required>
                <option value="">Seleccionar:</option>
                <option value="Administrador">Administrador</option>
                <option value="Optometrista">Optometrista</option>
                <option value="Cobrador">Cobrador</option>
                <option value="Laboratorista">Laboratorio</option>
                <option value="Campo">Campo</option>                
                <!-- Opciones del combobox -->
            </select>

            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required>
            <option value="">Seleccionar:</option>
                <option value="Habilitado">Habilitado</option>
                <option value="Deshabilitado">Deshabilitado</option>
            </select>

            <label for="hora_inicio">Hora Inicio:</label>
            <input type="time" id="hora_inicio" name="hora_inicio" required>

            <label for="hora_fin">Hora Fin:</label>
            <input type="time" id="hora_fin" name="hora_fin" required>

            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>
