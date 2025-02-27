<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Incluir la conexión y funciones de consultas
include '../funciones/conexion.php';
include '../funciones/funcionesPanelAdministrador.php';

// Obtener la lista de cobradores
$result = obtenerListaCobradores($conn);
// Obtener la lista de contratos cobrados
$result2 = obtenerListaUnicoCobrador($conn);

// Obtener la cantidad de contratos no asignados
$total_no_asignados = contarContratosNoAsignados($conn);
$cobros_hoy = contarCobrosHoy($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/panel_administrador.css?v=<?php echo(rand()); ?>">
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('main-content');
            sidebar.classList.toggle('active');
            content.classList.toggle('active');
        }

    </script>
</head>
<body>

    <!-- Barra lateral -->
    <div id="sidebar">
        <div class="sidebar-header">
            <h2><?php echo htmlspecialchars($_SESSION['nombre_usuario'] . ' ' . $_SESSION['ape_pat_usuario'] . ' ' . $_SESSION['ape_mat_usuario']); ?></h2>
            <img src="../imagenes/NVL.png" alt="No se pudo cargar" class="logo">
            <hr style="border: 2px solid #ffffff; margin: 10px 0;">
        </div>
        <ul class="nav">
        <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
            <li><a class="active" href="#"><i class="fas fa-users"></i> Lista de Cobradores</a></li>
            <li><a href="../interfaces/contrato.php"><i class="fas fa-file-contract"></i> Realizar Contrato</a></li>
            <li><a href="../interfaces/interfaz_asignar_contratos.php"><i class="fas fa-file-contract"></i> Asignar Contratos</a></li>
            <li><a href="../interfaces/register.php"><i class="fas fa-user-plus"></i> Registrar Usuarios</a></li>
            <li><a href="../interfaces/lista_contratos_admin.php"><i class="fas fa-list"></i> Lista de Contratos</a></li>
            <li><a href="../interfaces/lista_contratos_laboratorista.php"><i class="fas fa-list"></i> Lista de Contratos Lab</a></li>
            <li><a href="../interfaces/lista_usuarios.php"><i class="fas fa-list"></i> Lista de Usarios</a></li>
        <?php endif; ?>
        <?php if ($_SESSION['tipo_usuario'] == 'Cobrador') : ?>
            <li><a href="../interfaces/lista_contratos.php"><i class="fas fa-list"></i> Lista de Contratos</a></li>

            <li><a href="#"><i class="fas fa-chart-pie"></i> Estadísticas</a></li>
            <li><a href="#"><i class="fas fa-cogs"></i> Configuración</a></li>
        <?php endif; ?>
        </ul>
    </div>

    <!-- Contenido principal -->
    <main id="main-content">
        <div class="navbar">
            <div class="user-info"> 
                <a href="../logout.php" class="btn">Cerrar Sesión</a>
            </div>
            <div>
                <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
            </div>
        </div>

        <div class="main-content">
            <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                <h1>Dashboard</h1>
            <div class="cards">
                <div class="card bg-info">
                    <div class="card-body">Lista Cobradores</div>
                </div>
                <div class="card bg-warning">
                    <div class="card-body">
                        Contratos por asignar: <?php echo $total_no_asignados; ?>
                    </div>
                </div>
                <div class="card bg-success">
                    <div class="card-body">Cobros realizados hoy: <?php echo $cobros_hoy; ?></div>
                </div>
                <div class="card bg-danger">
                    <div class="card-body">Usuarios Nuevos</div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($_SESSION['tipo_usuario'] == 'Cobrador') : ?>
                <h1>Cobrador</h1>
            <div class="cards">
                <div class="card bg-info">
                    <div class="card-body">Funcion pendiente</div>
                </div>
                <div class="card bg-warning">
                    <div class="card-body">
                        Contratos por asignar: <?php echo $total_no_asignados; ?>
                    </div>
                </div>
                <div class="card bg-success">
                    <div class="card-body">Cobros realizados hoy: <?php echo $cobros_hoy; ?></div>
                </div>
            </div>
            <?php endif; ?>

<!-- Zona de Tablas -->
            <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
            <h2>Lista de Cobradores</h2>
            <table>
                <thead>
                    <tr>
                        <th>Cobrador</th>
                        <th>Primer Cobro</th>
                        <th>Último Cobro</th>
                        <th>Total Cobrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
                            echo "<td>" . htmlspecialchars(date('g:i A', strtotime($row['primer_cobro']))) . "</td>";
                            echo "<td>" . htmlspecialchars(date('g:i A', strtotime($row['ultimo_cobro']))) . "</td>";
                            echo "<td>$" . htmlspecialchars(number_format($row['total_cobrado'], 2)) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay cobradores con abonos registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <?php if ($_SESSION['tipo_usuario'] == 'Cobrador') : ?>
            <h2>Lista de Cobros</h2>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Fecha de Abono</th>
                        <th>Cantidad de Abono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result2->num_rows > 0) {
                        while ($row2 = $result2->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row2['nombre_cliente']) . "</td>";
                            echo "<td>" . htmlspecialchars($row2['fecha_abono']) . "</td>";
                            echo "<td>" . htmlspecialchars($row2['cantidad_abono']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No hay abonos registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
