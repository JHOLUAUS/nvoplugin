<?php
include('../funciones/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cobrador = $_POST['id_cobrador']; // Obtener el ID del cobrador seleccionado
    $contratos = $_POST['contratos']; // Obtener los contratos seleccionados

    // Preparar la consulta SQL para actualizar cada contrato
    $sql = "UPDATE clientecontrato SET id_cobrador = ? WHERE id_cliente = ?";

    // Preparar la declaración
    $stmt = $conn->prepare($sql);

    $success = true; // Bandera para comprobar si hubo éxito

    foreach ($contratos as $id_cliente) {
        // Vincular parámetros
        $stmt->bind_param("ii", $id_cobrador, $id_cliente); // id_cobrador, id_cliente
        // Ejecutar la declaración
        if (!$stmt->execute()) {
            $success = false; // Si hay un error, cambiar la bandera a false
            break;
        }
    }

    // Cerrar la declaración
    $stmt->close();

    // Mostrar mensaje emergente
    if ($success) {
        echo '<div id="success-message" style="display:none;">Contratos asignados exitosamente.</div>';
        echo '<script>
            document.getElementById("success-message").style.display = "block";
            setTimeout(function() {
                document.getElementById("success-message").style.display = "none";
                window.location.href = "../interfaces/interfaz_asignar_contratos.php"; // Redirigir después de 3 segundos
            }, 2000); // Cambiar el tiempo según lo desees
        </script>';
    } else {
        // Si hubo un error, redirigir o mostrar un mensaje de error
        echo '<script>alert("Error al asignar contratos. Inténtalo de nuevo.");</script>';
    }
}
?>
