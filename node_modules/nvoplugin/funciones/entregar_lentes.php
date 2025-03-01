<?php
// Conexión a la base de datos
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el ID del cliente enviado desde JavaScript
    $id_clientecontrato = $_POST['id_cliente'];

    // Validar que el ID del cliente no esté vacío
    if (!empty($id_clientecontrato)) {
        // Realizar la consulta para actualizar el estado del contrato
        $query = "UPDATE clientecontrato 
        SET estado_entrega = 'Entregado' 
        WHERE id_cliente = ?";

        // Preparar la declaración
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $id_clientecontrato);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo "El contrato ha sido entregado correctamente.";
            } else {
                echo "Error al modificar el contrato.";
            }

            // Cerrar la declaración
            $stmt->close();
        } else {
            echo "Error al preparar la consulta.";
        }
    } else {
        echo "ID de cliente no proporcionado.";
    }
} else {
    echo "Método no permitido.";
}

// Cerrar la conexión
$conn->close();
?>
