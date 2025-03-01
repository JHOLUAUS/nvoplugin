<?php
include('../config.php');
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Fechas - Corte de Caja</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
        <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
        }
        .btn-back {
            margin-left: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Seleccionar Rango de Fechas para Corte de Caja</h2>
    <br>

    <?php if ($_SESSION['tipo_usuario'] == 'Cobrador') : ?>
    <form action="../funciones/generar_corte_caja.php" method="POST">
        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
        </div>
        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
        </div>
        <button type="submit" class="btn btn-primary">Generar Corte</button>
        <button type="button" class="btn btn-secondary btn-back" onclick="window.location.href='panel_administrador.php';">Volver</button>

    </form>
    <?php endif; ?>

    <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
    <form action="../funciones/generar_corte_caja_admin.php" method="POST">
        <div class="mb-2">
            <label for="user" class="form-label">Usuario</label>
            <select id="user" name="user" required>
            <option value="General">General</option>
            <?php
            // Incluir la conexión a la base de datos
            include('../funciones/conexion.php');
            // Consulta para obtener los usuarios que son "Optometristas" o "Administradores"
            $query = "SELECT id_usuario, nombre_usuario FROM usuarios WHERE tipo_usuario IN ('Cobrador', 'Campo', 'Administrador')";
            $result = $conn->query($query);
            // Verificar si hay resultados
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // El valor del option es el ID, pero se muestra el nombre
                    echo '<option value="' . $row['id_usuario'] . '">' . $row['nombre_usuario'] . '</option>';
                }
            } else {
                echo '<option value="">No hay usuarios disponibles</option>';
            }

            // Cerrar la conexión
            $conn->close();
            ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
        </div>
        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
        </div>
        <button type="submit" class="btn btn-primary">Generar Corte</button>
        <button type="button" class="btn btn-secondary btn-back" onclick="window.location.href='panel_administrador.php';">Volver</button>

    </form>
    <?php endif; ?>
</div>
</body>
</html>
