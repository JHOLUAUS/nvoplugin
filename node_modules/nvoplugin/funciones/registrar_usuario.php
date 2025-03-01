<?php
// Ruta: funciones/registrar_usuario.php
include('../config.php');
include('../funciones/conexion.php'); // Asegúrate de que la ruta sea correcta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nombre = $_POST['nombre'];
    $ape_pat = $_POST['ape_pat'];
    $ape_mat = $_POST['ape_mat'];
    $tipo_usuario =$_POST['tipo_usuario'];
    $estado =$_POST['estado'];
    $hora_inicio =$_POST['hora_inicio'];
    $hora_fin =$_POST['hora_fin'];

    // Encriptar la contraseña
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Preparar y ejecutar la consulta para insertar el nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (username, pass, nombre_usuario, ape_pat_usuario, ape_mat_usuario,tipo_usuario, estado_usuario, hora_inicio_usuario, hora_fin_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $username, $hashed_password, $nombre, $ape_pat, $ape_mat,$tipo_usuario, $estado, $hora_inicio,$hora_fin);

    if ($stmt->execute()) {
        // Registro exitoso, redirigir al login
        header("Location: ../interfaces/panel_administrador.php?v=<?php echo(rand()); ?>");
        exit(); // Detener la ejecución del script después de redirigir
    } else {
        echo "Error al registrar el usuario.";
    }

    $stmt->close();
    $conn->close();
}
?>
