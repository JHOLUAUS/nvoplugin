<?php
// Conexión a la base de datos
include('../funciones/conexion.php'); 

// Verificar si se ha enviado el parámetro 'cp'
if (isset($_GET['cp'])) {
    $cp = $_GET['cp'];

    // Consulta para obtener los datos del código postal
    $sql = "SELECT d_asenta AS asentamiento, d_tipo_asenta AS tipo, D_mnpio AS municipio, d_estado AS estado
            FROM c_cp
            WHERE d_codigo = ?";
    
    // Preparar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cp);  // 's' para cadena
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    $asentamientos = [];

    // Si se encontraron resultados
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Añadir el asentamiento a la lista
            $asentamientos[] = $row['asentamiento'];
            
            // Si es el primer resultado, guardar otros datos
            if (empty($data)) {
                $data['tipo'] = $row['tipo'];
                $data['municipio'] = $row['municipio'];
                $data['estado'] = $row['estado'];
            }
        }
        // Añadir la lista de asentamientos al array de datos
        $data['asentamientos'] = $asentamientos;

        // Devolver los datos en formato JSON
        echo json_encode($data);
    } else {
        // Si no se encontró el código postal, devolver un array vacío
        echo json_encode(['error' => 'No se encontraron datos para el código postal ingresado.']);
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'No se proporcionó un código postal.']);
}
?>
