<?php
include('config.php');
// Verificar si ya está logueado
if (isset($_SESSION['id_usuario'])) {
    // Redirigir según el tipo de usuario
    if ($_SESSION['tipo_usuario'] == 'Administrador') {
        header("Location: interfaces/panel_administrador.php");
        exit();
    } elseif ($_SESSION['tipo_usuario'] == 'Campo') {
        header("Location: interfaces/lista_contratos_campo.php");
        exit();
    } elseif ($_SESSION['tipo_usuario'] == 'Cobrador') {
        header("Location: interfaces/panel_administrador.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/iniciosesion.css?v=<?php echo(rand()); ?>">
    <script src="https://unpkg.com/@capacitor/core@latest/dist/capacitor.js"></script>
</head>
<body>
    <div class="login-container">
        <img src="imagenes/NVL.png" alt="Encabezado" class="header-image">
        <form action="login.php" method="post" class="login-form">
            <label for="username">Usuario</label>
            <input type="text" id="username" name="username" placeholder="Ingresa tu usuario" required>
            
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
            
            <button type="submit">Iniciar sesión</button>
        </form>

                <!-- Mostrar mensaje de error si está presente en la URL -->

        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
    </div>


    <script>
        console.log("Capacitor cargado:", window.Capacitor);
        console.log("Plugins disponibles:", window.Capacitor.Plugins);
    </script>

</body>
</html>
