<?php
include '../funciones/conexion.php';

// Obtén los datos enviados por el formulario
$idUsuario = $_POST['id_usuario'];
$nuevoEstado = $_POST['estado_usuario'];
$horaInicio = $_POST['hora_inicio_usuario'] ?: null;
$horaFin = $_POST['hora_fin_usuario'] ?: null;

// Actualiza el estado y horario en la base de datos
$query = "UPDATE usuarios SET estado_usuario = ?, hora_inicio_usuario = ?, hora_fin_usuario = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('sssi', $nuevoEstado, $horaInicio, $horaFin, $idUsuario);

if ($stmt->execute()) {
    echo "Estado y horario actualizado correctamente";
} else {
    echo "Error al actualizar los datos";
}

// Redirigir de vuelta a la página de administración de usuarios
header("Location: ../interfaces/lista_usuarios.php");
exit();
?>
