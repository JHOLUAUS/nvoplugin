<?php
// Conectar a la base de datos
include('conexion.php');

// Obtener los datos enviados por AJAX
date_default_timezone_set('America/Mexico_City');

$id_folio = isset($_POST['id_folio']) ? intval($_POST['id_folio']) : null;
$id_cobrador = isset($_POST['id_cobrador']) ? intval($_POST['id_cobrador']) : null;
$cantidad_abono = isset($_POST['cantidad_abono']) ? floatval($_POST['cantidad_abono']) : null;
$metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : null;
$liquidar = isset($_POST['liquidar']) ? ($_POST['liquidar'] === 'true' ? 1 : 0) : 0; // Convertir a entero
$nuevo_saldo = isset($_POST['nuevo_saldo']) ? floatval($_POST['nuevo_saldo']) : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar los datos
    if (!empty($id_folio) && !empty($cantidad_abono) && !empty($metodo_pago) && !empty($id_cobrador)) {
        $fecha_abono = date('Y-m-d H:i:s');

        // Insertar el abono en la tabla
        $sql_abono = "INSERT INTO abonos (id_cliente, id_folio, id_cobrador, cantidad_abono, fecha_abono, forma_pago_abono, tipo_abono) 
                      VALUES ('$id_folio', '$id_folio', '$id_cobrador', '$cantidad_abono', '$fecha_abono', '$metodo_pago', 'Abono')";
        
        if (mysqli_query($conn, $sql_abono)) {
            // Actualizar el saldo en la tabla folios
            $sql_update_saldo = "UPDATE folios SET saldo_nuevo = '$nuevo_saldo' WHERE id_folio = '$id_folio'";
            if (mysqli_query($conn, $sql_update_saldo)) {
                // Comprobar si el saldo es cero
                if ($nuevo_saldo == 0) {
                    $sql_update_estado = "UPDATE folios SET estado_liquidacion = 'Liquidado' WHERE id_folio = '$id_folio'";
                    if (mysqli_query($conn, $sql_update_estado)) {
                        echo "Abono registrado, saldo actualizado y estado marcado como Liquidado.";
                    } else {
                        echo "Error al actualizar el estado de liquidaci܇n: " . mysqli_error($conn);
                    }
                } else {
                    echo "Abono registrado y saldo actualizado correctamente.";
                }
            } else {
                echo "Error al actualizar el saldo: " . mysqli_error($conn);
            }
        } else {
            echo "Error al insertar el abono: " . mysqli_error($conn);
        }
    } else {
        echo "Faltan datos.";
    }
}
?>