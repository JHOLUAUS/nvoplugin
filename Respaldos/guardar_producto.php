<?php
// Conectar a la base de datos
include('conexion.php');

// Deshabilitar la visualización de errores en producción
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../funciones/errores/php-error.log');
error_reporting(E_ERROR | E_WARNING | E_PARSE);

header('Content-Type: application/json');

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Obtener los datos enviados por AJAX
$id_folio = isset($_POST['id_folio']) ? intval($_POST['id_folio']) : null;
$idGlobalCliente = isset($_POST['idGlobalCliente']) ? intval($_POST['idGlobalCliente']) : null;
$aboprodfolio = isset($_POST['aboprodfolio']) ? intval($_POST['aboprodfolio']) : null;
$id_cobrador = isset($_POST['id_cobrador']) ? intval($_POST['id_cobrador']) : null;
$cantidad_abonoP = isset($_POST['cantidad_abonoP']) ? floatval($_POST['cantidad_abonoP']) : null;
$metodo_pagoP = isset($_POST['metodo_pagoP']) ? $_POST['metodo_pagoP'] : null;
$producto = isset($_POST['producto']) ? $_POST['producto'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($idGlobalCliente) && !empty($cantidad_abonoP) && !empty($metodo_pagoP) && !empty($producto)) {
        // Iniciar transacción
        mysqli_begin_transaction($conn);

        try {
            $fecha_abono = date('Y-m-d H:i:s');
            $fecha_abono_trunk = date('Y-m-d H:i');
            
            // Insertar el abono en la tabla abonos
            $sql_abono = "INSERT INTO abonos (id_cliente, id_folio, id_cobrador, cantidad_abono, fecha_abono, forma_pago_abono, tipo_abono, fecha_truncada)
                          VALUES ('$idGlobalCliente', '$aboprodfolio', '$id_cobrador', '$cantidad_abonoP', '$fecha_abono', '$metodo_pagoP', '$producto','$fecha_abono_trunk')";

            if (!mysqli_query($conn, $sql_abono)) {
                throw new Exception("Error al insertar el abono: " . mysqli_error($conn));
            }

            // Si el producto es "Enganche", actualizar la tabla folios
            if ($producto === "100y100") {
                $monto_a_descontar = 200; // Ajustar según lógica
                $saldo_a_descontar = 200;

                // Verificar si la consulta de actualización está bien estructurada
                $sql_update_folios = "UPDATE folios SET total = total - ?, saldo_nuevo = saldo_nuevo - ? WHERE id_folio = ?";
                $stmt = mysqli_prepare($conn, $sql_update_folios);
                if (!$stmt) {
                    throw new Exception("Error al preparar la consulta de actualización: " . mysqli_error($conn));
                }

                // Verificar que los valores están bien vinculados a la consulta
                mysqli_stmt_bind_param($stmt, "ddi", $monto_a_descontar, $saldo_a_descontar, $aboprodfolio);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error al ejecutar la consulta de actualización: " . mysqli_stmt_error($stmt));
                }

                mysqli_stmt_close($stmt);
            }

            // Si todo es exitoso, confirmar la transacción
            mysqli_commit($conn);
            $response = array('status' => 'success', 'message' => 'Operación realizada correctamente.');
        } catch (Exception $e) {
            // Si hay algún error, revertir la transacción
            mysqli_rollback($conn);
            $response = array('status' => 'error', 'message' => 'Transacción fallida: ' . $e->getMessage());
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Faltan datos.');
    }
} else {
    $response = array('status' => 'error', 'message' => 'Método de solicitud no válido.');
}

// Enviar la respuesta como JSON
echo json_encode($response);

// Cerrar conexión
mysqli_close($conn);
?>