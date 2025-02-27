<?php
// Conexión a la base de datos
include '../funciones/conexion.php';
date_default_timezone_set('America/Mexico_City');

// Función para obtener la lista de cobradores con su primer y último cobro y el total cobrado
function obtenerListaCobradores($conn) {
    // Establecer la fecha de hoy
    $hoy = date('Y-m-d');

    $sql = "SELECT 
                u.id_usuario AS id_cobrador,
                CONCAT(u.nombre_usuario, ' ', u.ape_pat_usuario, ' ', u.ape_mat_usuario) AS nombre_completo,
                MIN(a.fecha_abono) AS primer_cobro,
                MAX(a.fecha_abono) AS ultimo_cobro,
                SUM(a.cantidad_abono) AS total_cobrado
            FROM 
                usuarios u
            JOIN 
                abonos a ON u.id_usuario = a.id_cobrador
            WHERE 
                u.tipo_usuario = 'Cobrador' 
                AND DATE(a.fecha_abono) = ?
            GROUP BY 
                u.id_usuario";

    // Preparar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $hoy); // Enlazar la fecha actual
    $stmt->execute();

    return $stmt->get_result(); // Devolver el resultado
}

// Función para contar contratos no asignados
function contarContratosNoAsignados($conn) {
    $sql = "SELECT COUNT(*) as total_no_asignados FROM clientecontrato WHERE id_cobrador IS NULL";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total_no_asignados'];
}

function contarCobrosHoy($conn) {
    $sql = "SELECT COUNT(*) as cobros_hoy 
            FROM abonos 
            WHERE DATE(fecha_abono) = CURDATE()";  // Filtra los abonos por la fecha actual
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['cobros_hoy'];
}


$id_cobrador = $_SESSION['id_usuario']; // Obt��n el ID del usuario logueado
function contarCobrosHoyCobrador($conn, $id_cobrador) {
    $sql = "SELECT COUNT(*) as cobros_hoy 
            FROM abonos 
            WHERE DATE(fecha_abono) = CURDATE() 
              AND id_cobrador = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cobrador); // Vincula el id del cobrador a la consulta
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['cobros_hoy'];
}

$nombreCobrador = $_SESSION['nombre_usuario'];
function obtenerListaUnicoCobrador($conn) {
    // Establecer la fecha de hoy
    $hoy = date('Y-m-d');

    $sql = "SELECT 
                a.id_cobrador,
                c.nombre_cliente,
                a.fecha_abono,
                a.cantidad_abono
            FROM 
                clientecontrato c
            JOIN 
                abonos a ON c.id_cliente = a.id_cliente
            WHERE 
                c.id_cliente = a.id_cliente 
                AND DATE(a.fecha_abono) = ?";

    // Preparar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $hoy); // Enlazar la fecha actual
    $stmt->execute();

    return $stmt->get_result(); // Devolver el resultado
}
?>
