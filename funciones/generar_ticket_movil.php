<?php
// Datos del ticket
$idGlobalCliente = $_GET['idGlobalCliente'];
$cantidad_abono = $_GET['cantidad_abono'];
$metodo_pago = $_GET['metodo_pago'];
$nuevo_saldo = $_GET['nuevo_saldo'];
$nombre_cliente= $_GET['nombre_cliente'];
$nombre_cobrador= $_GET['nombre_cobrador'];
$saldo_anterior= $_GET['saldo_anterior'];
$folio_encode= $_GET['folio_encode'];

$fecha_actual = date('d-m-Y');
$hora_actual = date('g:i A');

// Genera el HTML del ticket
$html = "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Ticket de Abono</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .ticket {
            width: 48mm;
            padding: 8px;
            text-align: center;
            border: 1px dashed black;
        }
        .ticket img {
            width: 100px;
            height: auto;
            margin-bottom: 3px;
        }
        .ticket h2 {
            margin-bottom: 6px;
            font-size: 1.1em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .ticket .section-title {
            font-size: 0.85em;
            font-weight: bold;
            margin-top: 6px;
            border-top: 1px dashed black;
            padding-top: 4px;
        }
        .ticket p {
            margin: 2px 0;
            font-size: 0.75em;
            line-height: 1.1;
        }
        .ticket .details {
            text-align: left;
            margin-top: 8px;
        }
        .ticket .total {
            font-weight: bold;
            font-size: 0.9em;
            margin-top: 6px;
        }
        .footer {
            font-size: 0.7em;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class='ticket'>
        <img src='../imagenes/LogooNuevaVista.png' alt='Logo de la Óptica'>
        <h2>Óptica Nueva Vista</h2>
        <p class='section-title'>Datos del Cliente</p>
        <p>Cliente: $nombre_cliente</p>
        <p>Folio: $folio_encode</p>
        <p class='section-title'>Detalles del Abono</p>
        <div class='details'>
            <p>Cantidad Abonada: $$cantidad_abono</p>
            <p>Método de Pago: $metodo_pago</p>
            <p>Saldo Anterior: $$saldo_anterior</p>
            <p>Nuevo Saldo: $$nuevo_saldo</p>
        </div>
        <p class='section-title'>Información de la Transacción</p>
        <p>Fecha: $fecha_actual</p>
        <p>Hora: $hora_actual</p>
        <p>Cobrador: $nombre_cobrador</p>
        <p class='total'>Gracias por su pago</p>
        <p class='footer'>Conserve este ticket para cualquier aclaración</p>
    </div>
</body>
</html>
";

// Definir la URL de la API y los parámetros
$api_url = 'https://api.apiflash.com/v1/urltoimage';
$access_key = 'cc58625979c947dfaaafe199f75faab3'; // Reemplaza con tu API Key
$api_request_url = $api_url . '?access_key=' . $access_key . '&html=' . urlencode($html);

// Hacer la solicitud a la API de ApiFlash
$serverPath = '../funciones/tickets/ticket_abono.pdf';
$image_data = file_get_contents($api_request_url);

// Guardar la imagen generada en el servidor
if ($image_data) {
    file_put_contents($serverPath, $image_data);
    echo "Imagen generada con éxito.";
} else {
    echo "Error al generar la imagen.";
}

/* 
Definir la ruta del archivo en el servidor
$serverPath = '../funciones/tickets/ticket_abono.pdf';
//$webPath = 'https://tudominio.com/funciones/tickets/ticket_abono.pdf'; // Cambia por tu dominio real
$webPath = 'http://192.168.1.32/funciones/tickets/ticket_abono.pdf';


// Guardar el PDF en el servidor
file_put_contents($serverPath, $dompdf->output());

// Enviar la URL del archivo generado como respuesta
echo json_encode(['fileUrl' => $webPath]);
*/
?>



