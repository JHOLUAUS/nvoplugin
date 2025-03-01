<?php
// Conectar a la base de datos
include('../config.php');
include('conexion.php');

// Validar que el par��metro id_folio est�� presente
if (!isset($_GET['id_folio'])) {
    echo json_encode(["error" => "No se proporcion�� el ID del folio"]);
    exit;
}

$id_folio = $_GET['id_folio']; // id_folio debe ser pasado por la URL

// Consultar los abonos realizados para ese folio
$query = "
    SELECT 
        ab.id_abono, 
        ab.cantidad_abono, 
        ab.fecha_abono,
        ab.forma_pago_abono,
        ab.tipo_abono,
        f.folios, 
        f.total,
        f.saldo_nuevo
    FROM abonos ab
    JOIN folios f ON ab.id_folio = f.id_folio
    WHERE f.id_folio = ?";  // Filtramos por el id_folio que representa el contrato

// Preparar y ejecutar la consulta con MySQLi
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_folio); // "i" indica que es un entero
$stmt->execute();
$result = $stmt->get_result();

$abonos = [];
while ($row = $result->fetch_assoc()) {
    $abonos[] = $row;
}

// Cerrar la consulta y la conexi��n
$stmt->close();
$conn->close();

// Devolver los abonos en formato JSON
echo json_encode($abonos);
?>
