<?php
include('../funciones/conexion.php');
include('../interfaces/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger datos del formulario
    $usuario = $_POST['usuario'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $fecha_fin = date('Y-m-d 23:59:59', strtotime($fecha_fin));


    // Consulta base
    $query = "
        SELECT 
            f.folios, 
            a.cantidad_abono, 
            a.tipo_abono, 
            a.fecha_abono, 
            a.forma_pago_abono,
            u.nombre_usuario
        FROM abonos a
        INNER JOIN folios f ON a.id_folio = f.id_folio
        INNER JOIN usuarios u ON a.id_cobrador = u.id_usuario

    ";

    // Condiciones de la consulta
    $conditions = [];
    if ($usuario !== 'todos') {
        $conditions[] = "a.id_cobrador = " . intval($usuario);
    }
    $conditions[] = "a.fecha_abono BETWEEN '$fecha_inicio' AND '$fecha_fin'";

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(' AND ', $conditions);
    }

    $query .= " ORDER BY a.fecha_abono ASC"; // Cambia ASC a DESC si quieres el orden inverso


    // Ejecutar la consulta
    $result = $conn->query($query);

    $totales_query = "
        SELECT 
            -- Totales por tipo de abono
            SUM(CASE WHEN a.tipo_abono = 'Abono' AND a.forma_pago_abono = 'Efectivo' THEN a.cantidad_abono ELSE 0 END) AS abono_efectivo,
            SUM(CASE WHEN a.tipo_abono = 'Abono' AND a.forma_pago_abono = 'Transferencia' THEN a.cantidad_abono ELSE 0 END) AS abono_transferencia,
            SUM(CASE WHEN a.tipo_abono = 'Abono' AND a.forma_pago_abono = 'Tarjeta' THEN a.cantidad_abono ELSE 0 END) AS abono_tarjeta,
            SUM(CASE WHEN a.tipo_abono = 'Abono' THEN a.cantidad_abono ELSE 0 END) AS abono_total,

            -- Totales por Spray, Gotas, Póliza, Enganche 100+100
            SUM(CASE WHEN a.tipo_abono IN ('Spray', 'Gotas', 'Póliza', 'Enganche 100+100') AND a.forma_pago_abono = 'Efectivo' THEN a.cantidad_abono ELSE 0 END) AS otros_efectivo,
            SUM(CASE WHEN a.tipo_abono IN ('Spray', 'Gotas', 'Póliza', 'Enganche 100+100') AND a.forma_pago_abono = 'Transferencia' THEN a.cantidad_abono ELSE 0 END) AS otros_transferencia,
            SUM(CASE WHEN a.tipo_abono IN ('Spray', 'Gotas', 'Póliza', 'Enganche 100+100') AND a.forma_pago_abono = 'Tarjeta' THEN a.cantidad_abono ELSE 0 END) AS otros_tarjeta,
            SUM(CASE WHEN a.tipo_abono IN ('Spray', 'Gotas', 'Póliza', 'Enganche 100+100') THEN a.cantidad_abono ELSE 0 END) AS otros_total
        FROM abonos a
        WHERE a.fecha_abono BETWEEN '$fecha_inicio' AND '$fecha_fin'
    ";

    if ($usuario !== 'todos') {
        $totales_query .= " AND a.id_cobrador = " . intval($usuario);
    }

    $totales_result = $conn->query($totales_query);

    if (!$totales_result) {
        die("Error en consulta de totales: " . $conn->error);
    }

    $totales = $totales_result->fetch_assoc();

    $nombreUsuario = '';
    if ($usuario !== 'todos') {
        $consultaUsuario = "SELECT nombre_usuario FROM usuarios WHERE id_usuario = " . intval($usuario);
        $resultadoUsuario = $conn->query($consultaUsuario);

        if ($resultadoUsuario && $resultadoUsuario->num_rows > 0) {
            $filaUsuario = $resultadoUsuario->fetch_assoc();
            $nombreUsuario = $filaUsuario['nombre_usuario'];
        } else {
            $nombreUsuario = 'Usuario no encontrado';
        }
    } else {
        $nombreUsuario = 'Corte de caja General';
    }


}
//YA JALA
?>

<!DOCTYPE html>
<html>
<head>
    <title>Corte de Caja</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
        color: #333;
    }
    main {
        margin: 30px auto;
        padding: 30px;
        max-width: 900px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .menu-icon{
        margin-left: auto;
    }
    
    .header-container {
    display: flex; /* Esto habilita el uso de Flexbox */
    justify-content: space-between; /* Asegura que el contenido se distribuya a los extremos */
    align-items: center; /* Alinea los elementos verticalmente */
    padding: 10px 20px;
    background-color: mediumaquamarine; /* Color de fondo */
}
    h1, h2, h3 {
        text-align: center;
        color:rgb(24, 160, 133);
        margin-bottom: 20px;
    }
    
    form {
        background-color: #ffffff;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    label {
        font-weight: bold;
        font-size: 1rem;
        margin-bottom: 5px;
        color: #333;
    }
    
    select, input[type="date"], button {
        padding: 12px;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 8px;
        width: 100%;
        box-sizing: border-box;
        transition: border-color 0.3s, background-color 0.3s;
    }
    
    select:focus, input[type="date"]:focus {
        border-color: #4CAF50;
        background-color: #f9f9f9;
    }
    
    .botoncito {
        background-color: #1abc9c;
        color: white;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        padding: 15px;
        font-size: 1.1rem;
        border-radius: 8px;
        grid-column: span 2; /* Makes the button span across both columns */
    }
    
    .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin-top: 30px;
}

/* Estilos de la tabla */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
    table-layout: fixed; /* Hace que las celdas se ajusten al espacio disponible */
}

/* Asegura que las celdas de la tabla sean responsivas */
table, th, td {
    border: 1px solid #ddd;
}

th {
    background-color: #1abc9c;
    color: white;
    padding: 12px;
    text-align: center;
    font-size: 1.1rem;
    word-wrap: break-word; /* Permite que el texto largo se ajuste y no se desborde */
}

td {
    padding: 10px;
    text-align: center;
    font-size: 1rem;
    word-wrap: break-word; /* Permite que el texto largo se ajuste */
    overflow-wrap: break-word; /* Asegura que el texto largo se divida si es necesario */
}

/* Para pantallas pequeñas (máximo 600px de ancho) */
@media screen and (max-width: 600px) {
    table {
        font-size: 0.9rem; /* Reduce el tamaño de la fuente en pantallas pequeñas */
    }
    
    th, td {
        padding: 8px; /* Reduce el padding para que las celdas no se vean tan grandes */
    }
}
    
    .totals {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
    }
    
    .totals p {
        font-size: 1.2rem;
        margin: 10px 0;
        color: #333;
    }
</style>

</head>
<body>
    <header>
        <h1>Corte de Caja</h1>
    </header>
    <main>
        <form method="POST" action="">
            <label for="usuario">Selecciona el Usuario:</label>
            <select name="usuario" id="usuario">
                <option value="todos">Todos los usuarios</option>
                <?php
                $query = "SELECT id_usuario, nombre_usuario FROM usuarios WHERE tipo_usuario IN ('Cobrador', 'Campo', 'Administrador')";
                $usuarios = $conn->query($query);
                while ($row = $usuarios->fetch_assoc()) {
                    echo "<option value='{$row['id_usuario']}'>{$row['nombre_usuario']}</option>";
                }
                ?>
            </select>

            <label for="fecha_inicio">Fecha de inicio:</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" required>

            <label for="fecha_fin">Fecha de fin:</label>
            <input type="date" name="fecha_fin" id="fecha_fin" required>

            <button type="submit" class="botoncito">Generar Corte</button>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <h2>Resultados</h2>
            <h3>Del: <span style="color: red;"><?= htmlspecialchars($fecha_inicio) ?></span> a: <span style="color: red;"><?= htmlspecialchars($fecha_fin) ?></span></h3>
            <h4>Para: 
                <p class="redd" style="color: red;">
                    <?= $usuario === 'todos' ? 'Corte de caja General' : htmlspecialchars($nombreUsuario) ?>
                </p>
            </h4>
            <table>
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cantidad</th>
                        <th>Tipo de Abono</th>
                        <th>Fecha</th>
                        <th>Forma de Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['folios']) ?></td>
                            <td>$<?= number_format($row['cantidad_abono'], 2) ?></td>
                            <td><?= htmlspecialchars($row['tipo_abono']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_abono']) ?></td>
                            <td><?= htmlspecialchars($row['forma_pago_abono']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <br>
            <h2>Totales por Tipo de Abono y Forma de Pago</h2>
            <div class="totals">
                <h3>Abonos</h3>
                <p><strong>Total en Efectivo:</strong> $<?= number_format($totales['abono_efectivo'], 2) ?></p>
                <p><strong>Total en Transferencia:</strong> $<?= number_format($totales['abono_transferencia'], 2) ?></p>
                <p><strong>Total en Tarjeta:</strong> $<?= number_format($totales['abono_tarjeta'], 2) ?></p>
                <p><strong>Total General:</strong> $<?= number_format($totales['abono_total'], 2) ?></p>
            </div>

            <div class="totals">
                <h3>Spray, Gotas, Póliza y Enganche 100+100</h3>
                <p><strong>Total en Efectivo:</strong> $<?= number_format($totales['otros_efectivo'], 2) ?></p>
                <p><strong>Total en Transferencia:</strong> $<?= number_format($totales['otros_transferencia'], 2) ?></p>
                <p><strong>Total en Tarjeta:</strong> $<?= number_format($totales['otros_tarjeta'], 2) ?></p>
                <p><strong>Total General:</strong> $<?= number_format($totales['otros_total'], 2) ?></p>
            </div>
            <button onclick="window.location.href='corte_caja_formulario.php';" class="botoncito">¿Impresión Resumida?</button>
        <?php endif; ?>
    </main>
</body>
</html>
