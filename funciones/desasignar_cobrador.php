<?php
include('../funciones/conexion.php');

if (isset($_GET['id_folio'])) {
    $id_folio = $_GET['id_folio'];

    // Preparamos la consulta para actualizar el id_cobrador a NULL
    $sql = "UPDATE clientecontrato SET id_cobrador = NULL WHERE id_folio = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_folio); // 'i' indica que es un número entero
        
        if ($stmt->execute()) {
            echo "<script>
                alert('Cobrador desasignado correctamente.');
                window.location.href = '../interfaces/lista_contratos_admin.php'; // Ajusta la redirección según tu estructura
            </script>";
        } else {
            echo "<script>
                alert('Error al desasignar cobrador.');
                window.history.back();
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            alert('Error en la preparación de la consulta.');
            window.history.back();
        </script>";
    }

    $conn->close();
} else {
    echo "<script>
        alert('ID de folio no recibido.');
        window.history.back();
    </script>";
}
?>
