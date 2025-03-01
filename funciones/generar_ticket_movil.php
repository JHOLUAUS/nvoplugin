<?php
// Obtener los parámetros de la URL
$idGlobalCliente = $_GET['idGlobalCliente'];
$cantidad_abono = $_GET['cantidad_abono'];
$metodo_pago = $_GET['metodo_pago'];
$nombre_cliente = $_GET['nombre_cliente'];
$nombre_cobrador = $_GET['nombre_cobrador'];
$saldo_anterior = $_GET['saldo_anterior'];
$folio_encode = $_GET['folio_encode'];
$nuevo_saldo = $_GET['nuevo_saldo'];

// Crear el HTML del ticket
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
    <div class='ticket' id='ticket'>
        <img src='../imagenes/LogooNuevaVista.png' alt='Logo de la Óptica'>
        <h2>Óptica Nueva Vista</h2>
        <p class='section-title'>Datos del Cliente</p>
        <p>Cliente: $nombre_cliente</p>
        <p>Folio: $folio_encode</p>
        <p class='section-title'>Detalles del Abono</p>
        <div class='details'>
            <p>Cantidad Abonada: $" . number_format($cantidad_abono, 2) . "</p>
            <p>Método de Pago: " . ucfirst($metodo_pago) . "</p>
            <p>Saldo Anterior: $" . number_format($saldo_anterior, 2) . "</p>
            <p>Nuevo Saldo: $" . number_format($nuevo_saldo, 2) . "</p>
        </div>
        <p class='section-title'>Información de la Transacción</p>
        <p>Fecha: " . date('d-m-Y') . "</p>
        <p>Hora: " . date('g:i A') . "</p>
        <p>Cobrador: $nombre_cobrador</p>
        <p class='total'>Gracias por su pago</p>
        <p class='footer'>Conserve este ticket para cualquier aclaración</p>
    </div>
</body>
</html>
";

// Devolver el HTML como respuesta (sin procesar aún como imagen)
echo $html;
?>
