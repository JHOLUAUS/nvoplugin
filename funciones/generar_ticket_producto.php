<?php
// Obtener los datos enviados a través de la URL
date_default_timezone_set('America/Mexico_City');
$cantidad_abonoP = $_GET['cantidad_abonoP'];
$metodo_pagoP = $_GET['metodo_pagoP'];
$nombre_cliente= $_GET['nombre_cliente'];
$nombre_cobrador= $_GET['nombre_cobrador'];
$folio_encode= $_GET['folio_encode'];
$producto= $_GET['producto'];

$fecha_actual = date('d-m-Y');
$hora_actual = date('g:i A'); // Hora en formato de 12 horas
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Producto</title>
    <style>
        /* Estilos ajustados para ticket de 48 mm */
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
    <div class="ticket">
        <!-- Logo (actualiza la ruta a la de tu imagen) -->
        <img src="../imagenes/LogooNuevaVista.png" alt="Logo de la Óptica">

        <!-- Título -->
        <h2>Óptica Nueva Vista</h2>
        
        <!-- Datos del cliente y folio -->
        <p class="section-title">Datos del Cliente</p>
        <p>Cliente: <?php echo $nombre_cliente; ?></p>
        <p>Folio: <?php echo $folio_encode; ?></p>

        <!-- Detalles de la compra del producto -->
        <p class="section-title">Producto</p>
        <div class="details">
            <p>Producto: <?php echo $producto; ?></p>
            <p>Cantidad Abonada: $<?php echo number_format($cantidad_abonoP, 2); ?></p>
            <p>Método de Pago: <?php echo ucfirst($metodo_pagoP); ?></p>
        </div>

        <!-- Fecha y cobrador -->
        <p class="section-title">Información de la Transacción</p>
        <p>Fecha: <?php echo $fecha_actual; ?></p>
        <p>Hora: <?php echo $hora_actual; ?></p>
        <p>Cobrador: <?php echo $nombre_cobrador; ?></p>

        <!-- Mensaje de agradecimiento GT PROD-->
        <p class="total">Gracias por su pago</p>
        <p class="footer">Conserve este ticket para cualquier aclaración</p>
    </div>
</body>
</html>
