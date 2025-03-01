<?php
// Conectar a la base de datos
include('conexion.php');

// Establecer el encabezado de respuesta a JSON
header('Content-Type: application/json');

// Verificar que se haya recibido el ID del abono
if (!isset($_POST['id_abono'])) {
    echo json_encode(["error" => "No se proporcionó el ID del abono"]);
    exit;
}

$id_abono = $_POST['id_abono'];

// Iniciar una transacción para asegurar que ambas operaciones se realicen juntas
// Iniciar transacción
$conn->begin_transaction();

try {
    // Obtener la cantidad y el id_folio del abono antes de eliminarlo
    $query = "SELECT cantidad_abono, id_folio, tipo_abono FROM abonos WHERE id_abono = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_abono);
    $stmt->execute();
    $stmt->bind_result($cantidad_abono, $id_folio, $tipo_abono);
    $stmt->fetch();
    $stmt->close();

    // Si el tipo de abono es uno de los productos "Spray", "Gotas", o "Póliza"
    if (in_array($tipo_abono, ["Spray", "Gotas", "Póliza"])) {
        // Solo eliminamos el abono, no actualizamos la tabla folios
        $query = "DELETE FROM abonos WHERE id_abono = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_abono);
        $stmt->execute();
        $stmt->close();
        
        // Confirmar la transacción
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Abono eliminado correctamente."]);
    } elseif ($tipo_abono === "Enganche 100+100") {
        // Si el tipo de abono es "100y100" (la promoción), actualizamos el total y saldo_nuevo
        $monto_a_sumar = 200; // Ajuste para la promoción 100y100

        // Actualizar el total y saldo_nuevo en la tabla folios
        $query = "UPDATE folios SET total = total + ?, saldo_nuevo = saldo_nuevo + ? WHERE id_folio = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ddi", $monto_a_sumar, $monto_a_sumar, $id_folio);
        $stmt->execute();
        $stmt->close();

        // Obtener las promociones actuales del folio
        $query = "SELECT promociones FROM folios WHERE id_folio = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_folio);
        $stmt->execute();
        $stmt->bind_result($promociones);
        $stmt->fetch();
        $stmt->close();

        // Si las promociones existen, eliminar "Enganche 100+100"
        if ($promociones) {
            $promociones_array = explode(",", $promociones);
            $promociones_array = array_filter($promociones_array, function($promo) {
                return $promo !== "Enganche 100+100"; // Eliminar "Enganche 100+100"
            });
            $promociones = implode(",", $promociones_array); // Volver a convertir el array a string
        }

        // Actualizar el campo "promociones" en la tabla folios
        $query = "UPDATE folios SET promociones = ? WHERE id_folio = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $promociones, $id_folio);
        $stmt->execute();
        $stmt->close();

        // Eliminar el abono
        $query = "DELETE FROM abonos WHERE id_abono = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_abono);
        $stmt->execute();
        $stmt->close();

        // Confirmar la transacción
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Abono eliminado correctamente con ajuste de promoción."]);
    } else {
        // Para otros tipos de abono, actualizamos solo el saldo_nuevo
        $query = "UPDATE folios SET saldo_nuevo = saldo_nuevo + ? WHERE id_folio = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("di", $cantidad_abono, $id_folio);
        $stmt->execute();
        $stmt->close();

        // Eliminar el abono
        $query = "DELETE FROM abonos WHERE id_abono = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_abono);
        $stmt->execute();
        $stmt->close();

        // Confirmar la transacción
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Abono eliminado correctamente, saldo actualizado."]);
    }

} catch (Exception $e) {
    // En caso de error, revertir la transacción
    $conn->rollback();
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}

// Cerrar la conexión
//eliminar abono
$conn->close();
?>
