<?php
if (isset($_POST['image']) && isset($_POST['fileName'])) {
    $imageData = $_POST['image'];
    $fileName = $_POST['fileName'];
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);

    $filePath = '../funciones/tickets/' . $fileName;

    // Guardar la imagen en el servidor
    if (file_put_contents($filePath, base64_decode($imageData))) {
        echo json_encode(["status" => "success", "fileUrl" => $filePath]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se pudo guardar la imagen"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Datos insuficientes"]);
}
