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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estiloheader.css?v=<?php echo rand(); ?>">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
        }

        .header-container {
            background-color:mediumaquamarine;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .header-logo {
            height: 50px;
        }

        .menu-icon {
            cursor: pointer;
            font-size: 24px;
            background: none;
            border: none;
            color: white;
        }

        .side-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background-color: #34495e;
            padding-top: 60px;
            transition: 0.3s;
            z-index: 1000;
        }

        .side-menu ul {
            list-style-type: none;
            padding: 0;
        }

        .side-menu ul li {
            text-align: center;
        }

        .side-menu ul li a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
        }

        .side-menu ul li a:hover {
            background-color: #1abc9c;
        }

        /* Estilo de botón */
        .logout-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .logout-button:hover {
            background-color: #c0392b;
        }
        
    </style>
</head>
<body>
    <header class="header-container">
        <div class="header-content d-flex align-items-center">
            <img src="../imagenes/NVL.png" alt="Logo" class="header-logo">
            <span class="ms-3" style="color:white"><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span>
        </div>
        <button id="menu-toggle" class="menu-icon">☰</button> <!-- Icono del menú -->
    </header>

    <!-- Menú desplegable -->
    <nav id="side-menu" class="side-menu">
        <ul>
            <!-- Opciones según el tipo de usuario -->
            <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                <li><a href="panel_administrador.php">Panel de Administrador</a></li>
                <li><a href="lista_usuarios.php">Gestionar Usuarios</a></li>
                <li><a href="../apks/Installer-NVO_1.0.apk" download>Descargar Instalador</a></li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['tipo_usuario'] == 'Cobrador') : ?>
                <li><a href="panel_administrador.php">Panel Principal</a></li>
                <li><a href="../interfaces/lista_contratos.php">Contratos</a></li>
                <li><a href="../interfaces/corte_caja_formulario.php">Imprimir Corte de Caja</a></li>
                <li><a href="../apks/Installer-NVO_1.0.apk" download>Descargar Instalador</a></li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['tipo_usuario'] == 'Optometrista') : ?>
                <li><a href="historiales_clinicos.php">Historiales Clínicos</a></li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
            <?php endif; ?>

            <?php if ($_SESSION['tipo_usuario'] == 'Laboratorista') : ?>
                <li><a href="lista_contratos_laboratorista.php">Lista de Contratos</a></li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
                <li><a href="../apks/Installer-NVO_1.0.apk" download>Descargar Instalador</a></li>

            <?php endif; ?>

            <?php if ($_SESSION['tipo_usuario'] == 'Campo') : ?>
                <li><a href="../interfaces/lista_contratos_campo.php">Lista de Contratos</a></li>
                <li><a href="../apks/Installer-NVO_1.0_signed.apk" download>Descargar Instalador</a></li>
                <li><a href="../logout.php">Cerrar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <script>
        // Función para abrir/cerrar el menú
        document.getElementById('menu-toggle').onclick = function() {
            var menu = document.getElementById('side-menu');
            if (menu.style.display === 'block') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'block';
            }
        }
    </script>
</body>
</html>
