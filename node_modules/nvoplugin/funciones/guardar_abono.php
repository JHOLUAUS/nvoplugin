<?php
include('conexion.php');

header('Content-Type: application/json');

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Capturar datos del POST y validarlos
$idGlobalCliente = isset($_POST['idGlobalCliente']) ? intval($_POST['idGlobalCliente']) : null;
$aboprodfolio = isset($_POST['aboprodfolio']) ? intval($_POST['aboprodfolio']) : null;
$id_cobrador = isset($_POST['id_cobrador']) ? intval($_POST['id_cobrador']) : null;
$cantidad_abono = isset($_POST['cantidad_abono']) ? floatval($_POST['cantidad_abono']) : null;
$metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : null;
$liquidar = isset($_POST['liquidar']) && $_POST['liquidar'] === 'true' ? 1 : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($conn && $idGlobalCliente && $cantidad_abono && $metodo_pago && $id_cobrador) {
        $conn->begin_transaction(); // Iniciar la transacción
        try {
            $fecha_abono = date('Y-m-d H:i:s');
            $fecha_truncada = date('Y-m-d H:i');

            // Verificar si el abono ya existe para evitar duplicados
            $sql_check = "SELECT COUNT(*) AS total FROM abonos 
                          WHERE id_cliente = ? AND id_folio = ? 
                          AND cantidad_abono = ? AND fecha_truncada = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param('iids', $idGlobalCliente, $aboprodfolio, $cantidad_abono, $fecha_truncada);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check) {
                $row_check = $result_check->fetch_assoc();
                if ($row_check['total'] > 0) {
                    throw new Exception("Error: Abono duplicado. Intenta de nuevo.");
                }
            } else {
                throw new Exception("Error en la verificación de abono: " . $conn->error);
            }

            $sql_abono = "INSERT INTO abonos (id_cliente, id_folio, id_cobrador, cantidad_abono, fecha_abono, fecha_truncada, forma_pago_abono, tipo_abono) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'Abono')";
            $stmt_abono = $conn->prepare($sql_abono);
            $stmt_abono->bind_param('iiidsss', $idGlobalCliente, $aboprodfolio, $id_cobrador, $cantidad_abono, $fecha_abono, $fecha_truncada, $metodo_pago);

            if (!$stmt_abono->execute()) {
                throw new Exception("Error al insertar el abono: " . $stmt_abono->error);
            }

            // Obtener el saldo actual
            $sql_get_saldo = "SELECT saldo_nuevo FROM folios WHERE id_folio = ?";
            $stmt_get_saldo = $conn->prepare($sql_get_saldo);
            $stmt_get_saldo->bind_param('i', $aboprodfolio);
            $stmt_get_saldo->execute();
            $result_get_saldo = $stmt_get_saldo->get_result();

            if ($result_get_saldo && $result_get_saldo->num_rows > 0) {
                $row_saldo = $result_get_saldo->fetch_assoc();
                $saldo_actual = $row_saldo['saldo_nuevo'];

                // Calcular el nuevo saldo
                $nuevo_saldo = $saldo_actual - $cantidad_abono;

                // Actualizar el saldo en la tabla folios
                $sql_update_saldo = "UPDATE folios SET saldo_nuevo = ? WHERE id_folio = ?";
                $stmt_update_saldo = $conn->prepare($sql_update_saldo);
                $stmt_update_saldo->bind_param('di', $nuevo_saldo, $aboprodfolio);

                if (!$stmt_update_saldo->execute()) {
                    throw new Exception("Error al actualizar el saldo: " . $stmt_update_saldo->error);
                }

                // Si el saldo es 0, actualizar el estado de liquidación
                if ($nuevo_saldo == 0) {
                    $sql_update_estado = "UPDATE folios SET estado_liquidacion = 'Liquidado' WHERE id_folio = ?";
                    $stmt_update_estado = $conn->prepare($sql_update_estado);
                    $stmt_update_estado->bind_param('i', $aboprodfolio);

                    if (!$stmt_update_estado->execute()) {
                        throw new Exception("Error al actualizar el estado de liquidación: " . $conn->error);
                    }
                }

                // Confirmar la transacción si todo salió bien
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Abono registrado y saldo actualizado correctamente."]);
            } else {
                throw new Exception("No se encontró el saldo actual del folio.");
            }
        } catch (Exception $e) {
            $conn->rollback(); // Revertir cambios en caso de error
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Faltan datos o conexión fallida."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>

