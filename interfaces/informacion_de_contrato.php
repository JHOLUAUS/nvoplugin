<?php include('header.php'); ?>
<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<?php
// Conectar a la base de datos
include '../funciones/conexion.php'; // Asegúrate de que la ruta sea correcta

// Obtener el ID del contrato desde la URL
if (isset($_GET['id_cliente'])) {
    $id_cliente = intval($_GET['id_cliente']); // Asegúrate de convertir a entero para mayor seguridad
    $id_cobrador = intval($_GET['id_cobrador']); // Asegúrate de convertir a entero para mayor seguridad
    $nombre_cobrador = $_GET['nombre_cobrador'];
    $tipo_usuario = $_GET['tipo_usuario'];
    $folioContrato = intval($_GET['folioContrato']);

} else {
    // Manejar el caso en que no se pasa el ID
    die("Error: ID de contrato no especificado.");
}

global $id_global_cliente;
$id_global_cliente = $id_cliente;

global $aboprodfolio;
$aboprodfolio=$folioContrato;

global $id_global_cobrador;
$id_global_cobrador=$id_cobrador;

global $tipo_usuario_global;
$tipo_usuario_global;

global $nombredelCobrador;
$nombredelCobrador=$nombre_cobrador;

// Consultar los datos del contrato y los detalles relacionados, incluyendo las fotos
$query_contrato = "
    SELECT 
        clientecontrato.*, 
        fotos.*, 
        historialclinico.*, 
        folios.*, 
        armazon.*, 
        lugarcobranza.*, 
        abonos.*
    FROM 
        clientecontrato
    LEFT JOIN 
        fotos ON clientecontrato.id_foto = fotos.id_foto
    LEFT JOIN 
        historialclinico ON clientecontrato.id_HC = historialclinico.id_HC
    LEFT JOIN 
        folios ON folios.id_cliente = clientecontrato.id_cliente
    LEFT JOIN 
        armazon ON clientecontrato.id_armazon = armazon.id_armazon
    LEFT JOIN 
        lugarcobranza ON clientecontrato.id_lugarCobranza = lugarcobranza.id_lugarCobranza
    LEFT JOIN 
        abonos ON abonos.id_folio = folios.id_folio
    WHERE 
        clientecontrato.id_cliente = ?";


$stmt = $conn->prepare($query_contrato);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
$contrato = $result->fetch_assoc();
// Verifica si se obtuvieron datos del contrato
if (!$contrato) {
    die("Error: No se encontró el contrato.");
}

//. $contrato['asentamiento_cliente']. ' ' .
global $direccionCobranza; 
$direccionCobranza= $contrato['calle_cliente'] . ' '. $contrato['municipio_cliente']. ' '.$contrato['cp']; // Ajusta según tu tabla
$id_folio = $contrato['id_folio'];

global $nombreClienteglobal;
$nombreClienteglobal=$contrato['nombre_cliente'];


global $telefonoClienteGB;
$telefonoClienteGB=$contrato['telefono_cliente'];


$material=$contrato['material'];
$material_array = explode(',', $material);
//CADENA TRATAMIENTO
$tratamiento=$contrato['tratamiento'];
$tratamiento_array = explode(',', $tratamiento);
//CADENA BIFOCAL
$bifocal=$contrato['bifocal'];
$bifocal_array = explode(',', $bifocal);

$id_clientecontrato=$contrato['id_cliente'];

// Cierra la consulta
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Contrato</title>
    <link rel="stylesheet" href="../css/registro_cliente.css?v=<?php echo(rand()); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Leaflet Routing Machine CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- Leaflet Routing Machine JavaScript -->
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <script type="text/javascript" src="https://printjs-4de6.kxcdn.com/print.min.js"></script>



</head>
<body>

<div class="container">
        <h2>Detalle del Contrato</h2>
       
    <!-- Botón para abrir el modal -->
                <div class="field-container">
                    <label for="nombrePaciente">Cobrador asignado</label>
                    <input type="text" id="nombrePaciente" value="<?php echo $nombredelCobrador ?>" readonly>
                </div>
    <button id="btnOpcionesContrato">Ver opciones de contrato</button>



        <!-- Primera Sección: Historiales Clínicos -->
        <div class="form-section">
            <h3>Historiales Clínicos</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
            <table class="contract-table">
                <thead>
                    <tr>
                        <th>Fecha de entrega del producto</th>
                        <th> | </th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí puedes agregar filas dinámicamente con datos de la base -->
                    <tr>
                        <td><?php echo $contrato['ultimoexamen_HC']; // Asegúrate de que este campo exista ?></td>
                        <td></td>
                        <td><?php echo $contrato['observacion_int'];?></td>
                    </tr>
                    <!-- Más filas -->
                </tbody>
            </table>
        </div>

        <!-- Segunda Sección: Información del Contrato -->
         <br>
        <div class="form-section">
            <h3>Contrato</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
            <div class="form-row">
                <!-- Columna 1                                 -->
                <div class="form-group">
                    <label for="folio">Folio:</label>
                    <input type="text" id="folio" value="<?php echo $contrato['folios']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="fecha_creacion">Fecha de Creación:</label>
                    <input type="text" id="fecha_creacion" value="<?php echo $contrato['ultimoexamen_HC']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="total">Total:</label>
                    <input type="text" id="total" value="<?php echo $contrato['total']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="saldo">Saldo:</label>
                    <input type="text" id="saldo" value="<?php echo $contrato['saldo_nuevo']; ?>" readonly >
                </div>
                <div class="form-group">
                    <label for="paquete">Paquete:</label>
                    <input type="text" id="paquete" value="<?php echo $contrato['paquete_armazon']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="periodo">Periodo:</label>
                    <input type="text" id="periodo" value="Revisar" readonly> <!-- <?php echo $contrato['periodo']; ?>-->
                </div>
            </div>

            <div class="form-row">
                <!-- Columna 2 -->
                <div class="form-group">
                    <label for="cliente">Cliente:</label>
                    <input type="text" id="cliente" value="<?php echo $contrato['nombre_cliente']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="alias">Alias:</label>
                    <input type="text" id="alias" value="<?php echo $contrato['alias_cliente']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <?php
                    // Obtener el número de teléfono del cliente
                    $telefono_cliente = $contrato['telefono_cliente'];

                    // Limpiar el número de teléfono para asegurarse de que contiene solo dígitos
                    $telefono_limpio = preg_replace('/\D/', '', $telefono_cliente);

                    // Verificar si ya tiene el prefijo +52, si no lo tiene, agregarlo
                    if (substr($telefono_limpio, 0, 2) !== "52") {
                        $telefono_limpio = "+52" . $telefono_limpio;
                    } else {
                        $telefono_limpio = "+" . $telefono_limpio;
                    }
                    ?>
                    <!-- Enlace de llamada -->
                    <a href="tel:<?php echo $telefono_limpio; ?>" id="telefono">
                        <?php echo $telefono_limpio; ?>
                    </a>
                </div>

                <div class="form-group">
                    <label for="nombre_ref">Nombre de Referencia:</label>
                    <input type="text" id="nombre_ref" value="<?php echo $contrato['referencia_cliente']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="telefono_ref">Teléfono de Referencia:</label>
                    <?php
                    // Obtener el número de teléfono del cliente
                    $telefono_cliente_ref = $contrato['tel_ref_cliente'];

                    // Limpiar el número de teléfono para asegurarse de que contiene solo dígitos
                    $telefono_limpio_ref = preg_replace('/\D/', '', $telefono_cliente_ref);

                    // Verificar si ya tiene el prefijo +52, si no lo tiene, agregarlo
                    if (substr($telefono_limpio_ref, 0, 2) !== "52") {
                        $telefono_limpio_ref = "+52" . $telefono_limpio_ref;
                    } else {
                        $telefono_limpio_ref = "+" . $telefono_limpio_ref;
                    }
                    ?>
                    <!-- Enlace de llamada -->
                    <a href="tel:<?php echo $telefono_limpio_ref; ?>" id="telefono_ref">
                        <?php echo $telefono_limpio_ref; ?>
                    </a>
                </div>
                <div class="form-group">
                    <label for="promocion">Promoción:</label>
                    <input type="text" id="promocion" value="" readonly> <!-- <?php echo $contrato['promocion']; ?>-->
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Lugar de Venta</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
            <div class="field-container">
                <label for="calleLV">Calle:</label>
                <input type="text" id="calleLV" value="<?php echo $contrato['calle_cliente']; ?>" readonly>
            </div>  <!--Field-container -->
            <div class="field-container">
                <label for="entrecallesLV">Entre Calles:</label>
                <input type="text" id="entrecallesLV" value="<?php echo $contrato['entre_calles_cliente']; ?>" readonly>
            </div>  <!--Field-container -->
            
            <div class="form-row">
                <div class="form-group">
                    <label for="numeroLV">Número:</label>
                    <input type="text" id="numeroLV" value="<?php echo $contrato['numero_cliente']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="departamentoLV">Departamento:</label>
                    <input type="text" id="departamentoLV" value="<?php echo $contrato['departamento_cliente']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="coloniaLV">Colonia:</label>
                    <input type="text" id="coloniaLV" value="<?php echo $contrato['asentamiento_cliente']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="localidadLV">Localidad:</label>
                    <input type="text" id="localidadLV" value="<?php echo $contrato['municipio_cliente']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="colorLV">Color de Casa:</label>
                    <input type="text" id="colorLV" value="<?php echo $contrato['color_casa_cliente']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="alladodeLV">Al lado de:</label>
                    <input type="text" id="alladodeLV" value="<?php echo $contrato['al_lado_cliente']; ?>" readonly>
                </div>  <!--Form-group -->
            </div>  <!--Form-Row -->
            <div class="field-container">
                <label for="frenteaLV">Frente a:</label>
                <input type="text" id="frenteaLV" value="<?php echo $contrato['frente_a_cliente']; ?>" readonly>
            </div>  <!--Field-container -->
        </div> <!-- Form Section Lugar de VENTA -->

        <div class="form-section">
            <h3>Lugar de Cobranza</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">

            <button id="btnTrazarRuta" title="Trazar ruta hacia lugar de cobranza">
            <i class="fas fa-map-marked-alt"></i> Trazar Ruta
            </button>
            <div id="map" style="height: 400px; display: none;"></div>
            <br>
            <div class="field-container">

                <label for="calleLC">Calle:</label>
                <input type="text" id="calleLC" value="<?php echo $contrato['calle_cobranza']; ?>" readonly>
            </div>  <!--Field-container -->
            <div class="field-container">
                <label for="entrecallesLC">Entre Calles:</label>
                <input type="text" id="entrecallesLC" value="<?php echo $contrato['entre_calles_cobranza']; ?>" readonly>
            </div>  <!--Field-container -->
            
            <div class="form-row">
                <div class="form-group">
                    <label for="numeroLC">Número:</label>
                    <input type="text" id="numeroLC" value="<?php echo $contrato['numero_cobranza']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="departamentoLC">Departamento:</label>
                    <input type="text" id="departamentoLC" value="<?php echo $contrato['departamento_cobranza']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="coloniaLC">Colonia:</label>
                    <input type="text" id="coloniaLC" value="<?php echo $contrato['asentamiento_cobranza']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="localidadLC">Localidad:</label>
                    <input type="text" id="localidadLC" value="<?php echo $contrato['municipio_cobranza']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="colorLC">Color de Casa:</label>
                    <input type="text" id="colorLC" value="<?php echo $contrato['color_casa_cobranza']; ?>" readonly>
                </div>  <!--Form-group -->
                <div class="form-group">
                    <label for="alladodeLC">Al lado de:</label>
                    <input type="text" id="alladodeLC" value="<?php echo $contrato['al_lado_cobranza']; ?>" readonly>
                </div>  <!--Form-group -->
            </div>  <!--Form-Row -->
            <div class="field-container">
                <label for="frenteaLC">Frente a:</label>
                <input type="text" id="frenteaLC" value="<?php echo $contrato['frente_a_cobranza']; ?>" readonly>
            </div>  <!--Field-container -->
        </div> <!-- Form Section Lugar de Cobranza -->



    <div class="form-section">
    <h3>Imágenes Asociadas</h3>
        <div class="imagenes-contrato">

        <div class="form-row">
        <div class="icon-upload">
            <?php if (!empty($contrato['ident_frente'])): ?>
                <a href="#" onclick="openModal('<?php echo htmlspecialchars($contrato['ident_frente']); ?>')">
                    <img src="../imagenes/id.png" alt="Ícono Identificación Frontal" />
                </a>
                <p>INE Frente</p>
            <?php endif; ?>
            </div>

        <div class="icon-upload">
            <?php if (!empty($contrato['ident_reversa'])): ?>
                <a href="#" onclick="openModal('<?php echo htmlspecialchars($contrato['ident_reversa']); ?>')">
                    <img src="../imagenes/id.png" alt="Ícono Identificación Reversa" />
                </a>
                <p>INE Reversa</p>
            <?php endif; ?>
        </div>

            <div class="icon-upload">
            <?php if (!empty($contrato['ident_pagare'])): ?>
                <a href="#" onclick="openModal('<?php echo htmlspecialchars($contrato['ident_pagare']); ?>')">
                    <img src="../imagenes/id.png" alt="Ícono Pagaré" />
                </a>
                <p>Pagaré</p>
            <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
        <div class="icon-upload">
            <?php if (!empty($contrato['ident_comprobante'])): ?>
                <a href="#" onclick="openModal('<?php echo htmlspecialchars($contrato['ident_comprobante']); ?>')">
                    <img src="../imagenes/id.png" alt="Ícono Comprobante" />
                </a>
                <p>Comprobante</p>
            <?php endif; ?>
            </div>

        <div class="icon-upload">
            <?php if (!empty($contrato['ident_casa'])): ?>
                <a href="#" onclick="openModal('<?php echo htmlspecialchars($contrato['ident_casa']); ?>')">
                    <img src="../imagenes/id.png" alt="Ícono Casa" />
                </a>
                <p>Foto Casa</p>
            <?php endif; ?>
        </div>


        <div class="icon-upload">
            <?php if (!empty($contrato['extra_casa'])): ?>
                <a href="#" onclick="openModal('<?php echo htmlspecialchars($contrato['extra_casa']); ?>')">
                    <img src="../imagenes/id.png" alt="Ícono Casa Extra" />
                </a>
                <p>Armazón</p>
            <?php endif; ?>
            </div>
        </div>
    </div>
    </div>

    <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>

        <div class="container">
        <h2>Detalle del Contrato</h2>

        <div class="form-section">
            <h3>Detalles Paciente</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
        
                <div class="field-container">
                    <label for="nombrePaciente">Nombre del Paciente</label>
                    <input type="text" id="nombrePaciente" value="<?php echo $contrato['nombre_cliente']; ?>" readonly>
                </div>

        </div>
        <!-- Primera Sección: Historiales Clínicos -->
        <div class="form-section">
            <h3>Historiales Clínicos</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
            <table class="contract-table">
                <thead>
                    <tr>
                        <th>Fecha de entrega del producto</th>
                        <th>|</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí puedes agregar filas dinámicamente con datos de la base -->
                    <tr>
                        <td><?php echo $contrato['ultimoexamen_HC']; // Asegúrate de que este campo exista ?></td>
                        <td> |</td>
                        <td>  HOLA  <!--<?php echo $contrato['observaciones']; // Asegúrate de que este campo exista ?> --></td>
                    </tr>
                    <!-- Más filas -->
                </tbody>
            </table>
        </div>

        <div class="form-section">
            <h3>Detalles de Armazon</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">

        <div class="field-container">
            <label for="armazonA">Armazón:</label>
            <input type="text" id="armazonA" value="<?php echo $contrato['tipo_armazon']; ?>" readonly>
        </div>

         <!-- Sub-sección Ojo derecho 
        <div class="field-container">
            <label for="paquetesA">Paquetes:</label>
            <input type="text" id="paquetesA" value="<?php echo $contrato['paquete_armazon']; ?>" readonly>
        </div>
        -->
        
        <!-- Sub-sección Ojo derecho -->
         <br>
        <b><label style="color: #00796b;"> Ojo Derecho:</label></b>
        <br>
        <div class="form-row"> 
            <div class="form-group">
                <label for="esfericoAOD">Esférico:</label>
                <input type="text" id="esfericoAOD" value="<?php echo $contrato['esferico_AOD']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="cilindroAOD">Cilindro:</label>
                <input type="text" id="cilindroAOD" value="<?php echo $contrato['cilindro_AOD']; ?>" readonly>
            </div>
        </div> 
        <div class="form-row">
            <div class="form-group">
                <label for="ejeAOD">Eje:</label>
                <input type="text" id="ejeAOD" value="<?php echo $contrato['eje_AOD']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="addAOD">Add:</label>
                <input type="text" id="addAOD" value="<?php echo $contrato['add_AOD']; ?>" readonly>
            </div>
        </div> 
        <div class="form-group">
                <label for="altAOD">ALT:</label>
                <input type="text" id="altAOD" value="<?php echo $contrato['alt_AOD']; ?>" readonly>
        </div>
        <!-- Sub-sección Ojo Izquierdo -->
         <br>
        <b><label style="color: #00796b;"> Ojo Izquierdo:</label></b>
        <br>
        <div class="form-row"> 
            <div class="form-group">
                <label for="esfericoAOI">Esférico:</label>
                <input type="text" id="esfericoAOI" value="<?php echo $contrato['esferico_AOI']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="cilindroAOI">Cilindro:</label>
                <input type="text" id="cilindroAOI" value="<?php echo $contrato['cilindro_AOI']; ?>" readonly>
            </div>
        </div> 
        <div class="form-row">
            <div class="form-group">
                <label for="ejeAOI">Eje:</label>
                <input type="text" id="ejeAOI" value="<?php echo $contrato['eje_AOI']; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="addAOI">Add:</label>
                <input type="text" id="addAOI" value="<?php echo $contrato['add_AOI']; ?>" readonly>
            </div>
        </div> 
        <div class="form-row">
            <div class="form-group">
                    <label for="altAOI">ALT:</label>
                    <input type="text" id="altAOI" value="<?php echo $contrato['alt_AOI']; ?>" readonly>
            </div>
        </div>
        <br>

        <!-- sub sección material y tratamiento -->

     <div class="form-row">

             <!-- Columna Material -->
             <div class="form-group" style="flex: 1;">
            <b><label style="color: #00796b;"> Material:</label></b> 
                <div class="checkbox-group">
                    <div>
                        <input type="checkbox" id="matCR" name="material[]" value="CR" <?php if (in_array('CR', $material_array)) echo 'checked'; ?> disabled>
                        <label for="matCR">CR</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matHiIndex" name="material[]" value="Hi Index" <?php if (in_array('Hi Index', $material_array)) echo 'checked'; ?> disabled>
                        <label for="matHiIndex">Hi Index</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matPolicarbonato" name="material[]" value="Policarbonato" <?php if (in_array('Policarbonato', $material_array)) echo 'checked'; ?> disabled>
                        <label for="matPolicarbonato">Policarbonato</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matOtro" name="material[]" value="Otro" <?php if (in_array('Otro', $material_array)) echo 'checked'; ?> disabled>
                        <label for="matOtro">Otro</label>
                    </div>
                    <input type="text" id="matOtroTexto" name="matOtroTexto" value="<?php echo $contrato['matOtroTexto']; ?>" readonly>
                </div>
            </div>

        <!-- Columna tratamiento -->
        <div class="form-group" style="flex: 1;">
            <b><label style="color:#00796b">Tratamiento:</label></b>
                <div class="checkbox-group">
                    <div>
                        <input type="checkbox" id="tratAR" name="tratamiento[]" value="AR" <?php if (in_array('AR', $tratamiento_array)) echo 'checked'; ?> disabled>
                        <label for="tratAR">A/R</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratBlueRay" name="tratamiento[]" value="BlueRay"<?php if (in_array('BlueRay', $tratamiento_array)) echo 'checked'; ?> disabled>
                        <label for="tratBlueRay">Blu-Ray</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratEspejo" name="tratamiento[]" value="Espejo" <?php if (in_array('Espejo', $tratamiento_array)) echo 'checked'; ?> disabled>
                        <label for="tratEspejo">Espejo</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratFotocromatico" name="tratamiento[]" value="Fotocromatico"<?php if (in_array('Fotocromatico', $tratamiento_array)) echo 'checked'; ?> disabled>
                        <label for="tratFotocromatico">Fotocromático</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratTinte" name="tratamiento[]" value="Tinte" <?php if (in_array('Tinte', $tratamiento_array)) echo 'checked'; ?> disabled>
                        <label for="tratTinte">Tinte</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratOtro" name="tratamiento[]" value="Otro" <?php if (in_array('Otro', $tratamiento_array)) echo 'checked'; ?> disabled>
                        <label for="tratOtro">Otro</label>
                    </div>


                    <input type="text" id="tratOtroTexto" name="tratOtroTexto" value="<?php echo $contrato['tratOtroTexto']; ?>" readonly>
                </div>
        </div>
        <!-- Columna tipo bifocal -->
        <div class="form-group" style="flex: 1;">
            <b><label style="color:#00796b">Tipo de Bifocal:</label></b>
                <div class="checkbox-group">
                    <div>
                        <input type="checkbox" id="biBlend" name="bifocal[]" value="Blend" <?php if (in_array('Blend', $bifocal_array)) echo 'checked'; ?> disabled>
                        <label for="biBlend">Blend</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biFT" name="bifocal[]" value="FT" <?php if (in_array('FT', $bifocal_array)) echo 'checked'; ?> disabled>
                        <label for="biFT">FT</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biNA" name="bifocal[]" value="NA" <?php if (in_array('NA', $bifocal_array)) echo 'checked'; ?> disabled>
                        <label for="biNA">N/A</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biProgresivo" name="bifocal[]" value="Progresivo" <?php if (in_array('Progresivo', $bifocal_array)) echo 'checked'; ?> disabled>
                        <label for="biProgresivo">Progresivo</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biOtro" name="bifocal[]" value="Otro" <?php if (in_array('Otro', $bifocal_array)) echo 'checked'; ?> disabled>
                        <label for="biOtro">Otro</label>
                    </div>

                    
                <div class="form-row">
                    <div class="field-container">
                        <input type="text" id="biOtroTexto" name="biOtroTexto" value="<?php echo $contrato['tratOtroTexto']; ?>" readonly>
                        </div>
                    </div>
                </div>
        </div>
        </div>


    <?php endif; ?>




    <!-- Modal para mostrar la imagen -->
    <div id="imageModal" class="modal" style="display:none;">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage" src="">
        <div id="caption"></div>
    </div>

        <div class="form-section">
            <h3>Otros Detalles</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
            <p><strong>Descripción:</strong> <?php echo $contrato['observacion_int']; ?></p>
        </div>

        <div class="form-section">
                <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                <!-- Botón para Administradores -->
                    <a href="lista_contratos_admin.php" class="btn">Volver</a>
                    <?php elseif ($_SESSION['tipo_usuario'] == 'Cobrador') : ?>
                    <!-- Botón para Cobradores -->
                    <a href="lista_contratos.php" class="btn">Volver</a>
                <?php endif; ?>

                <?php if ($contrato['estado_entrega'] == 'Por entregar') : ?>
                    <button id="btnEntregar" class="btnEntregar" onclick="entregarLentes(idClienteContrato)">Entregar</button>
                <?php endif; ?>
        </div>
    </div>

    <!--ZONA DE SCRIPTS --------------------------------------------------------------------------------------------------------------------------------- -->
  
     <!--SCRIPT PARA EL MAPA -->
  <script>
        const direccionCobranza = "<?php echo $direccionCobranza; ?>";
    </script>
    <script src="../funciones/scriptsN/trazodeRuta.js">
    </script>

    <script>
        function openModal(imageSrc) {
            document.getElementById("modalImage").src = imageSrc;
            document.getElementById("imageModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("imageModal").style.display = "none";
        }
    </script>






  <!--SCRIPT Y MODAL PARA OPCIONES DE CONTRATO --------------------------------------------------------------------------------------------------------------------------------- -->
<script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("JavaScript cargado y listo");
        // Modal principal de opciones de contrato
        var btnOpcionesContrato = document.getElementById('btnOpcionesContrato');
        var modalOpcionesContrato = document.getElementById('modalOpcionesContrato');
        var closeModalOpciones = document.getElementById('closeModalOpciones');

        // Mostrar el modal al hacer clic en el botón
        btnOpcionesContrato.onclick = function() {
            modalOpcionesContrato.style.display = "flex";
            document.body.classList.add('modal-open');
        };

        // Cerrar el modal al hacer clic en la "X"
        closeModalOpciones.onclick = function() {
            modalOpcionesContrato.style.display = "none";
            document.body.classList.add('modal-closed');
        };

        // Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target == modalOpcionesContrato) {
            modalOpcionesContrato.style.display = "none";
            document.body.classList.add('modal-closed');
            }
        };
        });
</script>

  
  <!-- Modal de opciones de contrato -->
<div id="modalOpcionesContrato" class="modal-opciones-contrato" style="display: none;">
        <div class="modal-opciones-contrato-content">
            <span id="closeModalOpciones" class="close-opciones-contrato">&times;</span>
            <h3>Opciones de Contrato</h3>

            <div class="form-section">
                <div class="form-row">
                    <label for="saldoContrato">Saldo Total</label>
                    <input type="text" id="saldoContrato" value="<?php echo $contrato['total']; ?>" placeholder="0.0" readonly>
                    <label for="saldoContrato">Saldo Actual</label>
                    <input type="text" id="saldoContrato" value="<?php echo $contrato['saldo_nuevo']; ?>" placeholder="0.0" readonly>
                </div>
            </div>

<div class="form-section">
                <div class="form-row">
                    <h3>Abonos</h3>
                </div>
                <div class="form-row">
                    <button id="opcionAbono" class="modal-opciones-contrato-button">Nuevo</button>
                    <button id="btnEliminarAbono" class="modal-btn-eliminar">Eliminar Abono</button>

                </div>

                <div class="form-row">

                <table class="contract-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>|</th>
                            <th>Abono</th>
                            <th>|</th>
                            <th>Método Pago</th>
                            <th>|</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta para obtener los contratos asignados al cobrador, junto con la dirección de cobranza
                        $sql = "
                                SELECT 
                                    a.cantidad_abono, 
                                    f.forma_pago, 
                                    a.fecha_abono,
                                    a.forma_pago_abono,
                                    a.tipo_abono
                                FROM 
                                    abonos a
                                JOIN 
                                    folios f ON a.id_folio = f.id_folio
                                JOIN 
                                    clientecontrato c ON a.id_cliente = c.id_cliente
                                WHERE 
                                    c.id_cliente = ?
                                ORDER BY
                                    fecha_abono ASC";
                        
                        // Prepara la consulta
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Error en la preparación de la consulta: " . $conn->error);
                        }

                        $stmt->bind_param('i', $id_cliente); // Pasa el ID
                        $stmt->execute();
                        $result = $stmt->get_result();


                        // Mostrar los contratos
                        while ($row = $result->fetch_assoc()) {
                            $fecha_abono_12hrs = date('d/m/Y g:i A', strtotime($row['fecha_abono']));
                            ?>
                            <tr>
                                <td data-label="Tipo">
                                        <?php echo htmlspecialchars($row['tipo_abono']); ?>
                                    </a>
                                </td>
                                <td>|</td>
                                <td data-label="Abono">
                                        <?php echo htmlspecialchars($row['cantidad_abono']); ?>
                                    </a>
                                </td>
                                <td>|</td>
                                <td data-label="Método Pago">
                                    <?php echo htmlspecialchars($row['forma_pago_abono']); ?>
                                </td>
                                <td>|</td>
                                <td data-label="Fecha">
                                    <?php echo htmlspecialchars($fecha_abono_12hrs); ?>
                                </td>
                            </tr>
                            <?php
                        }

                        // Cierra la consulta
                        $stmt->close();
                        ?>
                    </tbody>
                </table>
                <button id="btnEnviarWSP" class="ticketWSP">Enviar Ticket Por Whatsapp</button>
                </div>

            </div>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">

            <!-- Seccion de Abono -->
        <div class="form-section">
            <div class="form-row">
                <h3>Productos</h3>
                <button id="opcionProductos" class="modal-opciones-contrato-button">Nuevo Producto</button>
            </div>
        </div>
        <div class="form-section">
            <div class="form-row">
                <h3>Periodo</h3>
                <button id="opcionPeriodos" class="modal-opciones-contrato-button">Nuevo Periodo</button>
            </div>
        </div>

        </div>
    </div>

<script src="../funciones/scriptsN/ticketHandler.js"></script>


<script type="module">

//import { Share } from '@capacitor/share';
document.addEventListener("DOMContentLoaded", function() {
    // MODAL DE ABONO----------------------------------------------------------
    var opcionAbono = document.getElementById('opcionAbono');
    var modalAbono = document.getElementById('modalAbono');
    var closeModalAbono = document.getElementById('closeModalAbono');
    var btnImprimirAbono = document.getElementById('btnImprimirAbono');
    var liquidarCheckbox = document.getElementById('liquidar');

    // Mostrar el modal de Abono al hacer clic en la opción de Abono
    opcionAbono.onclick = function() {
        modalAbono.style.display = "flex";
    };

    // Cerrar el modal de Abono al hacer clic en la "X"
    closeModalAbono.onclick = function() {
        modalAbono.style.display = "none";
    };

    // Cerrar el modal de Abono al hacer clic fuera de él
    window.onclick = function(event) {
        if (event.target == modalAbono) {
            modalAbono.style.display = "none";
        }
    };

    

    // Función al hacer clic en el botón "Aceptar"
    btnImprimirAbono.onclick = function() {
        // Deshabilitar el botón inmediatamente para prevenir clics adicionales
        btnImprimirAbono.disabled = true;
        btnImprimirAbono.style.backgroundColor = "red"; // Color gris deshabilitado
        btnImprimirAbono.style.cursor = "not-allowed";

        var cantidadAbono = parseFloat(document.getElementById('cantidadAbono').value);
        var metodoPago = document.getElementById('metodoPago').value;
        var liquidar = liquidarCheckbox.checked;
        var idGlobalCliente = <?php echo json_encode($id_global_cliente); ?>;
        var aboprodfolio = <?php echo json_encode($aboprodfolio); ?>;
        var saldoNuevo = <?php echo json_encode($contrato['saldo_nuevo']); ?>;
        var nombreCobrador = <?php echo json_encode($nombredelCobrador); ?>;
        var idCobrador = <?php echo json_encode($id_global_cobrador); ?>;
        var nombreCliente = <?php echo json_encode($contrato['nombre_cliente']); ?>;
        var folioEncode = <?php echo json_encode($contrato['folios']); ?>;

        if (isNaN(cantidadAbono) || cantidadAbono <= 0) {
            alert("Por favor, ingrese una cantidad válida.");
            btnImprimirAbono.disabled = false; // Rehabilitar el botón si hay error
            return;
        }

        // Verificar si el cliente decide liquidar
        if (liquidar) {
            // Aplicar un descuento
            var descuento = 300;
            cantidadAbono = saldoNuevo - descuento;
        }

        var saldoAnterior = saldoNuevo;
        var nuevoSaldo = saldoNuevo - cantidadAbono;

        // Mostrar confirmación antes de proceder
        var confirmMessage = "Ingresarás una cantidad de: $" + cantidadAbono.toFixed(2) + ", ¿continuar?";
        if (!confirm(confirmMessage)) {
            // Si el usuario cancela, habilitar el botón y salir
            btnImprimirAbono.disabled = false;
            btnImprimirAbono.style.backgroundColor = "";
            btnImprimirAbono.style.cursor = "";
            return;
        }

        // Enviar la información al servidor mediante AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../funciones/guardar_abono.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Procesar la respuesta JSON
                    const response = JSON.parse(xhr.responseText);

                    if (response.status === "success") {
                        // Abono registrado correctamente
                        alert(response.message);
                        modalAbono.style.display = "none";

                        // Detectar si es móvil o web
                        var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                        var urlGenerarTicket = isMobile 
                            ? '../funciones/generar_ticket_movil.php'
                            : '../funciones/generar_ticket.php';

                            if (!isMobile) {
                                // 💻 En Web: Generar ticket e imprimir
                                var ticketWindow = window.open(
                                    urlGenerarTicket + 
                                    '?idGlobalCliente=' + encodeURIComponent(idGlobalCliente) +
                                    '&folio_encode=' + encodeURIComponent(folioEncode) +
                                    '&nombre_cliente=' + encodeURIComponent(nombreCliente) +
                                    '&cantidad_abono=' + encodeURIComponent(cantidadAbono) +
                                    '&metodo_pago=' + encodeURIComponent(metodoPago) +
                                    '&nuevo_saldo=' + encodeURIComponent(nuevoSaldo) +
                                    '&aboprodfolio=' + encodeURIComponent(aboprodfolio) +
                                    '&nombre_cobrador=' + encodeURIComponent(nombreCobrador) +
                                    '&saldo_anterior=' + encodeURIComponent(saldoAnterior),
                                    '_blank'
                                );

                                ticketWindow.onload = function () {
                                    ticketWindow.print();  // Imprimir solo en web
                                    ticketWindow.onafterprint = function () {
                                        location.reload(); // Recargar la página después de imprimir
                                    };
                                };
                            } else {
                                // 📱 En Móvil: Obtener URL del ticket y abrir/compartir
                                let urlParams = new URLSearchParams({
                                    idGlobalCliente: idGlobalCliente,
                                    folio_encode: folioEncode,
                                    nombre_cliente: nombreCliente,
                                    cantidad_abono: cantidadAbono,
                                    metodo_pago: metodoPago,
                                    nuevo_saldo: nuevoSaldo,
                                    aboprodfolio: aboprodfolio,
                                    nombre_cobrador: nombreCobrador,
                                    saldo_anterior: saldoAnterior
                                });

                                fetch(urlGenerarTicket + '?' + urlParams.toString())
                                    .then(response => response.json()) // Convertir la respuesta en JSON
                                    .then(data => {
                                        if (data.fileUrl) {
                                            let ticketUrl = data.fileUrl;
                                            console.log("url del ticket: ",ticketUrl);
                                    nvoBluetoothPrinter.isAvailable().then(function() {
                                        nvoBluetoothPrinter.printBase64(ticketUrl).then(function(result) {
                                            console.log("Impresión exitosa:", result);
                                            alert("Ticket impreso exitosamente.");
                                        }).catch(function(error) {
                                            console.error("Error al imprimir:", error);
                                            alert("No se pudo imprimir el ticket.");
                                        });
                                    }).catch(function(error) {
                                        alert("No se pudo conectar con la impresora Bluetooth. Asegúrate de que esté encendida y conectada.");
                                    });
                                    
                                    // También abrimos el archivo si es necesario
                                    //window.open(ticketUrl, '_blank');
                                } else {
                                    alert("No se pudo generar el ticket.");
                                }
                            })
                                    .catch(error => {
                                        console.error("Error en fetch:", error);
                                        alert("No se pudo generar el ticket. Inténtalo nuevamente.");
                                    });
                            }
                        } else if (response.status === "error") {
                            alert(response.message);
                        }
                    } else {
                        alert("Ocurrió un error al registrar el abono. Inténtalo más tarde.");
                    }
                    // Rehabilitar el botón después de completar la solicitud
                    btnImprimirAbono.disabled = false;
                    btnImprimirAbono.style.backgroundColor = ""; 
                    btnImprimirAbono.style.cursor = "";
                }
            };

        xhr.send("idGlobalCliente=" + idGlobalCliente + "&aboprodfolio=" + aboprodfolio + "&cantidad_abono=" + cantidadAbono + "&metodo_pago=" + metodoPago + "&liquidar=" + liquidar + "&nuevo_saldo=" + nuevoSaldo + "&id_cobrador=" + idCobrador);
    };
});
</script>


<!-- Modal de Abono -->
        <div id="modalAbono" class="modal-abono" style="display: none;">
            <div class="modal-content-abono">
                <span id="closeModalAbono" class="close">&times;</span>
                <h2>Registrar Abono</h2>

                <!-- Campo para la cantidad a abonar -->
                <label for="cantidadAbono" class="modal-label-abono">Cantidad a abonar:</label>
                <input type="number" id="cantidadAbono" class="modal-input-abono" placeholder="Introduce la cantidad" step="0.01" min="0" required>


                <!-- Campo para el método de pago -->
                <label for="metodoPago" class="modal-label-abono">Método de pago:</label>
                <select id="metodoPago" class="modal-input-abono">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Otro">Otro</option>
                </select>
                <br>
                <div class="form-row">
                    <p><b><label for="liquidar">¿Liquidar?</label></b></p>
                    <input type="checkbox" id="liquidar" name="liquidar">
                </div>
                <!-- Botón para aceptar -->
                <button id="btnImprimirAbono" class="modal-btn-abono">Aceptar</button>
            </div>
        </div>

  <!-- SCRIPT Y MODAL PARA ELIMINAR ABONO ------------------------------------------------------------------------------------------------------SCRIPT Y MODAL PARA ELIMINAR ABONO----- -->

  <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modalEliminarAbono = document.getElementById('modalEliminarAbono');
            var btnEliminarAbono = document.getElementById('btnEliminarAbono');
            var closeModalEliminarAbono = document.getElementById('closeModalEliminarAbono');
            var btnConfirmarEliminarAbono = document.getElementById('btnConfirmarEliminarAbono');
            var abonosList = document.getElementById('abonosList');
            var id_Cliente = <?php echo json_encode($id_global_cliente); ?>;
            var aboprodfolio = <?php echo json_encode($aboprodfolio); ?>;

            // Función para abrir el modal
            btnEliminarAbono.onclick = function() {
                cargarAbonos(); // Llamamos la función para cargar abonos
                modalEliminarAbono.style.display = "flex";
            };

            // Función para cerrar el modal al hacer clic en la "X"
            closeModalEliminarAbono.onclick = function() {
                modalEliminarAbono.style.display = "none";
            };

            // Cerrar el modal al hacer clic fuera de él
            window.onclick = function(event) {
                if (event.target == modalEliminarAbono) {
                    modalEliminarAbono.style.display = "none";
                }
            };

            // Función para cargar los abonos dinámicamente
            function cargarAbonos() {
                var xhr = new XMLHttpRequest();
                var idCliente = id_Cliente; // Asegúrate de que id_Cliente tiene el valor correcto

                xhr.open("GET", "../funciones/obtener_abonos.php?id_folio=" + aboprodfolio, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            try {
                                var responseText = xhr.responseText.trim(); // Remover espacios innecesarios
                                console.log("Respuesta del servidor:", responseText); // Para verificar la salida del servidor
                                var abonos = JSON.parse(responseText); // Intentar parsear el JSON
                                abonosList.innerHTML = '<option value="">Selecciona un abono</option>';
                                
                                if (abonos.length > 0) {
                                    abonos.forEach(function(abono) {
                                        var option = document.createElement('option');
                                        option.value = abono.id_abono;
                                        option.textContent = `${abono.tipo_abono} de $${abono.cantidad_abono} (${abono.forma_pago_abono}) - ${abono.fecha_abono}`;
                                        abonosList.appendChild(option);
                                    });
                                } else {
                                    abonosList.innerHTML = '<option value="">No hay abonos disponibles</option>';
                                }
                            } catch (e) {
                                console.error("Error al procesar JSON:", e);
                                console.error("Respuesta recibida no es un JSON válido:", responseText);
                                alert("Error al cargar los abonos. La respuesta no es un JSON válido.");
                            }
                        } else {
                            console.error("Error de conexión:", xhr.status, xhr.statusText);
                            alert("Error de conexión al cargar los abonos.");
                        }
                    }
                };
                xhr.send();
            }


            // Función para confirmar la eliminación
            btnConfirmarEliminarAbono.onclick = function() {
                var abonoId = abonosList.value;

                if (!abonoId) {
                    alert("Por favor, selecciona un abono para eliminar.");
                    return;
                }

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "../funciones/eliminar_abono.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        console.log(response); // Agrega esto para ver la respuesta del servidor

                        if (response.success) {
                            alert(response.message);
                            modalEliminarAbono.style.display = "none";
                            cargarAbonos(); // Recargar la lista de abonos después de eliminar
                            location.reload(); // Refresca la página
                        } else {
                            alert("Error al eliminar el abono: " + (response.error || "Error desconocido"));
                        }
                    } else {
                        alert("Hubo un problema con la solicitud.");
                    }
                }
            };

                xhr.send("id_abono=" + abonoId);
            };
        });
    </script>

<!-- Modal para eliminar abono -->
    <!-- Modal para eliminar abono -->
    <div id="modalEliminarAbono" class="modal-eliminar">
        <div class="modal-content-eliminar">
            <!-- Botón de cierre (X) -->
            <span id="closeModalEliminarAbono" class="close-modal-eliminar">&times;</span>
            <h2>Eliminar Abono</h2>
            <p>Selecciona el abono que deseas eliminar:</p>
            <!-- Lista de abonos -->
            <select id="abonosList">
                <!-- Opciones se llenarán dinámicamente -->
            </select>
            <!-- Botón de confirmación -->
            <button id="btnConfirmarEliminarAbono">Confirmar Eliminación</button>
        </div>
    </div>




  <!-- SCRIPT Y MODAL PARA PRODUCTOS ----------------------------------------------------------------------------------------------------------------SCRIPT Y MODAL PARA PRODUCTOS----- -->
  <script>
document.addEventListener("DOMContentLoaded", function() {
    // MODAL DE PRODUCTOS----------------------------------------------------------
    var opcionProductos = document.getElementById('opcionProductos');
    var modalProductos = document.getElementById('modalProductos');
    var closeModalProductos = document.getElementById('closeModalProductos');
    var btnImprimirProductos = document.getElementById('btnImprimirProductos');

    // Mostrar el modal de Abono al hacer clic en la opción de Abono
    opcionProductos.onclick = function() {
        modalProductos.style.display = "flex";
    };

    // Cerrar el modal de Abono al hacer clic en la "X"
    closeModalProductos.onclick = function() {
        modalProductos.style.display = "none";
    };

    // Cerrar el modal de Abono al hacer clic fuera de él
    window.onclick = function(event) {
        if (event.target == modalProductos) {
            modalProductos.style.display = "none";
        }
    };

    // Función al hacer clic en el botón "Aceptar" XD
    btnImprimirProductos.onclick = function() {
        var cantidadAbonoP = parseFloat(document.getElementById('cantidadAbonoP').value);
        var metodoPagoP = document.getElementById('metodoPagoP').value;
        var producto = encodeURIComponent(document.getElementById('producto').value);
        var idFolio = <?php echo json_encode($id_global_cliente); ?>;
        var idGlobalCliente = <?php echo json_encode($id_global_cliente); ?>;
        var aboprodfolio = <?php echo json_encode($aboprodfolio); ?>;
        var nombreCobrador = <?php echo json_encode($nombredelCobrador);?>;
        var idCobrador = <?php echo json_encode($id_global_cobrador);?>;
        var nombreCliente = <?php echo json_encode($contrato['nombre_cliente']); ?>;
        var folioEncode = <?php echo json_encode($contrato['folios']); ?>;

        if (isNaN(cantidadAbonoP) || cantidadAbonoP <= 0) {
            alert("Por favor, ingrese una cantidad válida.");
            return;
        }
        // Enviar la información al servidor mediante AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../funciones/guardar_producto.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Analiza la respuesta del servidor
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === "success") {
                        alert(response.message);
                        modalProductos.style.display = "none";

                        // Reiniciar campos
                        document.getElementById('cantidadAbonoP').value = '';
                        document.getElementById('metodoPagoP').value = 'Efectivo';

                        // Abrir una nueva ventana con el ticket y enviarlo a impresión
                        var ticketWindow = window.open('../funciones/generar_ticket_producto.php?idGlobalCliente=' + idGlobalCliente + '&folio_encode=' + folioEncode + '&nombre_cliente=' + nombreCliente + '&cantidad_abonoP=' + cantidadAbonoP + '&metodo_pagoP=' + metodoPagoP + '&nombre_cobrador=' + nombreCobrador + '&producto=' + producto, '_blank');
                        ticketWindow.onload = function() {
                            ticketWindow.print();
                        };

                        // Refrescar la página
                        location.reload();
                    } else {
                        alert("Error: " + response.message);
                    }
                } else {
                    // Manejo de errores HTTP
                    alert("Error de servidor: " + xhr.status);
                }
            }
        };


        xhr.send("idGlobalCliente=" + idGlobalCliente + "&aboprodfolio=" + aboprodfolio + "&cantidad_abonoP=" + cantidadAbonoP + "&metodo_pagoP=" + metodoPagoP + "&producto=" + producto + "&id_cobrador=" + idCobrador);
        console.log("Producto seleccionado:", producto);
    };
});
</script>

<!-- Modal de PRODUCTO -->
<div id="modalProductos" class="modal-abono" style="display: none;">
            <div class="modal-content-abono">
                <span id="closeModalProductos" class="close">&times;</span>
                <h2>Registrar Producto</h2>

                <!-- Campo para la cantidad a abonar -->
                <label for="cantidadAbonoP" class="modal-label-abono">Cantidad a abonar:</label>
                <input type="number" id="cantidadAbonoP" class="modal-input-abono" placeholder="Introduce la cantidad" required>

                <!-- Campo para el método de pago -->
                <label for="metodoPagoP" class="modal-label-abono">Método de pago:</label>
                <select id="metodoPagoP" class="modal-input-abono">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Otro">Otro</option>
                </select>
                <br>

                <label for="producto" class="modal-label-abono">Producto:</label>
                <select id="producto" class="modal-input-abono">
                    <option value="Spray">Spray - $100</option>
                    <option value="Gotas">Gotas - $250</option>
                    <option value="Póliza">Póliza $250</option>
                    <option value="Enganche 100+100">Enganche 100+100</option>
                </select>
                <!-- Botón para aceptar -->
                <button id="btnImprimirProductos" class="modal-btn-abono">Aceptar</button>
            </div>
</div>


<!--- AQUI MANEJARÉ ENVIAR EL TICKET POR WHATSAPP ------------------------------------------------->
<script>
document.getElementById('btnEnviarWSP').onclick = function() {
  var folioId = <?php echo json_encode($id_global_cliente); ?>;
  var modalWSP = document.getElementById('modalWSP');
  var listaAbonosWSP = document.getElementById('listaAbonosWSP');
  var modalWSP = document.getElementById('modalWSP');
  var closeModalWSP = document.getElementById('closeModalWSP');
  var nombreCliente = <?php echo json_encode($nombreClienteglobal); ?>;
  var nombreCobrador = <?php echo json_encode($nombredelCobrador); ?>;

  modalWSP.style.display = "flex";

  
        // Cerrar el modal al hacer clic en la "X"
        closeModalWSP.onclick = function() {
            modalWSP.style.display = "none";
            document.body.classList.add('modal-closed');
        };

        window.onclick = function(event) {
            if (event.target == modalWSP) {
            modalWSP.style.display = "none";
            document.body.classList.add('modal-closed');
            }
        };

  // Limpiar el contenido previo
  listaAbonosWSP.innerHTML = "<li>Cargando abonos...</li>";

  // Hacer la solicitud al servidor
  fetch('../funciones/obtener_abonos.php?id_folio=' + folioId)
    .then(response => response.json())
    .then(abonos => {
      listaAbonosWSP.innerHTML = ""; // Limpiar la lista

      // Verificar si hay abonos disponibles
      if (abonos.length === 0) {
        listaAbonosWSP.innerHTML = "<li>No hay abonos disponibles.</li>";
        return;
      }

      // Crear un elemento <li> para cada abono
      abonos.forEach(abono => {
        var li = document.createElement('li');
        li.innerHTML = `
            ${abono.folios}, 
            <b>Cantidad:</b> $${abono.cantidad_abono}, 
            <b>Fecha:</b> ${abono.fecha_abono}, 
            <b>Mp:</b> ${abono.forma_pago_abono}
        `;
          li.onclick = function() {
          generateAndDownloadTicketImage(
            abono.folios,
            nombreCliente,
            abono.cantidad_abono,
            abono.forma_pago_abono,
            abono.saldo_nuevo,
            nombreCobrador,
            abono.total
          );
          modalWSP.style.display = "none"; // Cerrar modal
        };
        listaAbonosWSP.appendChild(li);
      });
    })
    .catch(error => {
      console.error("Error al cargar los abonos:", error);
      listaAbonosWSP.innerHTML = "<li>Error al cargar los abonos.</li>";
    });
};


function generateAndDownloadTicketImage(folio, nombreCliente, cantidadAbono, metodoPago, nuevoSaldo, nombreCobrador, saldoAnterior) {  
    var telefonoCliente = <?php echo json_encode($telefonoClienteGB); ?>;
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');

    // Establecer tamaño del canvas
    canvas.width = 400;
    canvas.height = 700;

    // Establecer fondo
    ctx.fillStyle = "#FFFFFF"; // Fondo blanco
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Cargar la imagen del logo (ajusta la ruta de la imagen)
    var logo = new Image();
    logo.src = '../imagenes/LogooNuevaVista.png'; // Reemplaza con la ruta de tu logo

    logo.onload = function() {
    // Dibujar el logo en la parte superior del ticket
    ctx.drawImage(logo, 20, 20, 100, 40); // Ajusta las coordenadas y tamaño según necesites

    // Establecer el estilo para los textos
    ctx.fillStyle = "#333333"; // Color del texto (gris oscuro)
    ctx.font = "18px Arial";
    ctx.textAlign = "left"; // Alineación del texto

    // Dibujar título
    ctx.font = "24px Arial";
    ctx.fillText("Óptica Nueva Vista", 140, 40);

    // Dibujar línea de separación
    ctx.beginPath();
    ctx.moveTo(20, 80);
    ctx.lineTo(580, 80);
    ctx.strokeStyle = "#000000";
    ctx.lineWidth = 2;
    ctx.stroke();

    // *** Sección 1: Datos del Cliente ***
    ctx.font = "20px Arial";
    ctx.fillStyle = "#000000";
    ctx.fillText("Datos del Cliente", 20, 110);
    
    ctx.font = "16px Arial";
    ctx.fillText("Cliente: " + nombreCliente, 20, 140);
    ctx.fillText("Folio: " + folio, 20, 170);

    // Dibujar línea de separación
    ctx.beginPath();
    ctx.moveTo(20, 190);
    ctx.lineTo(580, 190);
    ctx.stroke();

    // *** Sección 2: Detalles del Abono ***
    ctx.font = "20px Arial";
    ctx.fillStyle = "#000000";
    ctx.fillText("Detalles del Abono", 20, 210);

    ctx.font = "16px Arial";
    ctx.fillText("Cantidad Abonada: $ " + cantidadAbono, 20, 240);
    ctx.fillText("Método de Pago: " + metodoPago, 20, 270);
    ctx.fillText("Saldo Anterior: $ " + saldoAnterior, 20, 300);
    ctx.fillText("Nuevo Saldo: $ " + nuevoSaldo, 20, 330);

    // Dibujar línea de separación
    ctx.beginPath();
    ctx.moveTo(20, 350);
    ctx.lineTo(580, 350);
    ctx.stroke();

    // *** Sección 3: Información de la Transacción ***
    ctx.font = "20px Arial";
    ctx.fillStyle = "#000000";
    ctx.fillText("Información de la Transacción", 20, 370);

    ctx.font = "16px Arial";
    var now = new Date();
    var fecha = now.toLocaleDateString(); // Formato: "DD/MM/YYYY"
    var hora = now.toLocaleTimeString(); // Formato: "HH:MM:SS"

    ctx.fillText("Fecha: " + fecha, 20, 400);
    ctx.fillText("Hora: " + hora, 20, 430);
    ctx.fillText("Cobrador: " + nombreCobrador, 20, 460);

    // *** Sección 4: Agradecimiento ***
    ctx.font = "18px Arial";
    ctx.fillStyle = "#008000"; // Verde para agradecimiento
    ctx.fillText("¡Gracias por su pago!", 20, 490);

    // Mensaje de conservar el ticket
    ctx.font = "14px Arial";
    ctx.fillStyle = "#000000"; // Texto negro para el mensaje
    ctx.fillText("Conserve este ticket para cualquier aclaración.", 20, 520);

    // Generar la imagen del canvas en formato base64
    var imgData = canvas.toDataURL("image/png");
        // Crear un enlace de descarga para la imagen generada
        var randomFileName = "ticket_abono_" + Date.now() + ".png";

        var downloadLink = document.createElement('a');
        downloadLink.href = imgData;
        downloadLink.download = randomFileName; // Nombre aleatorio del archivo
        downloadLink.click(); // Simula el clic para descargar
        };
            // Crear el mensaje de WhatsApp
        var numeroCliente = "521" + telefonoCliente; // Incluye el código de país (e.g., 52 para México)    
        var mensaje = "¡Aquí está tu Ticket! Folio: " + folio + " Cliente: " + nombreCliente + " Cantidad: " + cantidadAbono + " Saldo Nuevo: " + nuevoSaldo;
        var whatsappUrl = "https://wa.me/"+ numeroCliente+"?text=" + encodeURIComponent(mensaje);
        // Redirigir al enlace de WhatsApp
         window.open(whatsappUrl, '_blank');
}

</script>

<!-- MODAL PARA ENVIAR TICKET WSP -->
<div id="modalWSP" class="modal-wsp" style="display: none;">
  <div class="modal-wsp-content">
    <span id="closeModalWSP" class="close-wsp">&times;</span>
    <h3>Seleccionar Abono</h3>
    <ul id="listaAbonosWSP">
      <!-- Los abonos se cargarán dinámicamente aquí -->
    </ul>
  </div>
</div>


<script>
// Definir la variable PHP en JavaScript
const idClienteContrato = <?php echo json_encode($id_global_cliente); ?>;
</script>
<script src="../funciones/scriptsN/entregar_lentes.js" defer></script>
   
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>