<?php
// Incluir la conexión a la base de datos
include('conexion.php');

// Verificar si se recibieron los datos por POST
if (isset($_POST['id_clienteContrato']) && isset($_POST['observacion'])) {
    $id_clienteContrato = $_POST['id_clienteContrato'];
    $observacion = $_POST['observacion'];

    // Consultar el id_armazon desde la tabla clientecontrato basado en id_cliente
    $query = "SELECT id_armazon FROM clientecontrato WHERE id_cliente = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_clienteContrato);
    $stmt->execute();
    $result = $stmt->get_result();
    $contrato = $result->fetch_assoc();
    $stmt->close();

    // Si se encuentra un armazón asociado al cliente, actualizar las observaciones
    if ($contrato && $contrato['id_armazon']) {
        $id_armazon = $contrato['id_armazon'];

        // Actualizar la observación en la tabla armazon
        $updateQuery = "UPDATE armazon SET observacion_lab = ? WHERE id_armazon = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $observacion, $id_armazon);

        if ($stmt->execute()) {
            echo "Observación actualizada correctamente";
        } else {
            echo "Error al actualizar la observación";
        }

        $stmt->close();
    } else {
        echo "No se encontró el armazón asociado al cliente.";
    }
} else {
    echo "Datos incompletos.";
}
?>
