<?php include('header.php'); ?>

<?php
// Conectar a la base de datos
include '../funciones/conexion.php'; // Asegúrate de que la ruta sea correcta

// Obtener el ID del contrato desde la URL
if (isset($_GET['id_cliente'])) {
    $id_cliente = intval($_GET['id_cliente']); // Asegúrate de convertir a entero para mayor seguridad
    $nombre_cobrador = $_GET['nombre_cobrador'];
} else {
    // Manejar el caso en que no se pasa el ID
    die("Error: ID de contrato no especificado.");
}

global $id_global_cliente;
$id_global_cliente = $id_cliente;

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
$direccionCobranza = $contrato['calle_cliente'] . ' '. $contrato['municipio_cliente']. ' '.$contrato['cp']; // Ajusta según tu tabla
$id_folio = $contrato['id_folio'];
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



</head>
<body>

    <div class="container">
        <h2>Detalle del Contrato</h2>
       
    <!-- Botón para abrir el modal -->
    <button id="btnOpcionesContrato">Ver opciones de contrato</button>



        <!-- Primera Sección: Historiales Clínicos -->
        <div class="form-section">
            <h3>Historiales Clínicos</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
            <table class="contract-table">
                <thead>
                    <tr>
                        <th>Fecha de entrega del producto</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí puedes agregar filas dinámicamente con datos de la base -->
                    <tr>
                        <td><?php echo $contrato['ultimoexamen_HC']; // Asegúrate de que este campo exista ?></td>
                        <td>  HOLA  <!--<?php echo $contrato['observaciones']; // Asegúrate de que este campo exista ?> --></td>
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
                    <input type="text" id="telefono" value="<?php echo $contrato['telefono_cliente']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nombre_ref">Nombre de Referencia:</label>
                    <input type="text" id="nombre_ref" value="<?php echo $contrato['referencia_cliente']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="telefono_ref">Teléfono de Referencia:</label>
                    <input type="text" id="telefono_ref" value="<?php echo $contrato['tel_ref_cliente']; ?>" readonly>
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
                <p>Otra</p>
            <?php endif; ?>
            </div>
        </div>
    </div>
    </div>

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
            <a href="lista_contratos.php" class="btn">Volver</a>
        </div>
    </div>

    <script>
        function openModal(imageSrc) {
            document.getElementById("modalImage").src = imageSrc;
            document.getElementById("imageModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("imageModal").style.display = "none";
        }
    </script>





    <!-- Modal de opciones de contrato -->
    <div id="modalOpcionesContrato" class="modal-opciones-contrato" style="display: none;">
        <div class="modal-opciones-contrato-content">
            <span id="closeModalOpciones" class="close-opciones-contrato">&times;</span>
            <h2>Opciones de Contrato</h2>

            <div class="form-section">
                <div class="form-row">
                    <label for="saldoContrato">Saldo</label>
                    <input type="text" id="saldoContrato" value="<?php echo $contrato['saldo_nuevo']; ?>" placeholder="0.0" readonly>
                </div>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <h3>Abonos</h3>
                    <button id="opcionAbono" class="modal-opciones-contrato-button">Nuevo</button>
                </div>

                <div class="form-row">

                <table class="contract-table">
                    <thead>
                        <tr>
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
                                    a.forma_pago_abono
                                FROM 
                                    abonos a
                                JOIN 
                                    folios f ON a.id_folio = f.id_folio
                                JOIN 
                                    clientecontrato c ON a.id_cliente = c.id_cliente
                                WHERE 
                                    c.id_cliente = ?";
                        
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
                            ?>
                            <tr>
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
                                    <?php echo htmlspecialchars($row['fecha_abono']); ?>
                                </td>
                            </tr>
                            <?php
                        }

                        // Cierra la consulta
                        $stmt->close();
                        ?>
                    </tbody>
                </table>

                </div>

            </div>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">

            <!-- Seccion de Abono -->
        <div class="form-section">
            <div class="form-row">
                <h3>Productos</h3>
                <button id="opcionPeriodos" class="modal-opciones-contrato-button">Nuevo</button>
            </div>
        </div>
            <button id="opcionProductos" class="modal-opciones-contrato-button">Productos</button>
        </div>
    </div>

        <!-- Modal de Abono -->
        <div id="modalAbono" class="modal-abono" style="display: none;">
            <div class="modal-content-abono">
                <span id="closeModalAbono" class="close">&times;</span>
                <h2>Registrar Abono</h2>

                <!-- Campo para la cantidad a abonar -->
                <label for="cantidadAbono" class="modal-label-abono">Cantidad a abonar:</label>
                <input type="number" id="cantidadAbono" class="modal-input-abono" placeholder="Introduce la cantidad" required>

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




    <script>
        document.addEventListener("DOMContentLoaded", function() {
        // Modal principal de opciones de contrato
        var btnOpcionesContrato = document.getElementById('btnOpcionesContrato');
        var modalOpcionesContrato = document.getElementById('modalOpcionesContrato');
        var closeModalOpciones = document.getElementById('closeModalOpciones');

        // Mostrar el modal al hacer clic en el botón
        btnOpcionesContrato.onclick = function() {
            modalOpcionesContrato.style.display = "flex";
        };

        // Cerrar el modal al hacer clic en la "X"
        closeModalOpciones.onclick = function() {
            modalOpcionesContrato.style.display = "none";
        };

        // Cerrar el modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target == modalOpcionesContrato) {
            modalOpcionesContrato.style.display = "none";
            }
        };
        });
    </script>


<script>
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
        var cantidadAbono = parseFloat(document.getElementById('cantidadAbono').value);
        var metodoPago = document.getElementById('metodoPago').value;
        var liquidar = liquidarCheckbox.checked;
        var idFolio = <?php echo json_encode($id_global_cliente); ?>;
        var saldoNuevo = <?php echo json_encode($contrato['saldo_nuevo']); ?>;
        var nombreCobrador = <?php echo json_encode($nombredelCobrador);?>;
        var nombreCliente = <?php echo json_encode($contrato['nombre_cliente']); ?>;
        var folioEncode = <?php echo json_encode($contrato['folios']); ?>;

        if (isNaN(cantidadAbono) || cantidadAbono <= 0) {
            alert("Por favor, ingrese una cantidad válida.");
            return;
        }

        // Verificar si el cliente decide liquidar
        if (liquidar) {
            // Aplicar un descuento del 20%
            var descuento = 300;
            cantidadAbono = saldoNuevo - descuento;
        }

        var SaldoConDescuento = saldoNuevo-descuento; //Se va a utilizar para cuando liquidan
        var saldoAnterior = saldoNuevo;
        // Actualizar el saldo nuevo restando el abono
        var nuevoSaldo = saldoNuevo - cantidadAbono;

        // Enviar la información al servidor mediante AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../funciones/guardar_abono.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                // Actualización exitosa, cerrar el modal
                alert("Abono registrado correctamente.");
                modalAbono.style.display = "none";
                // Reiniciar campos
                document.getElementById('cantidadAbono').value = '';
                document.getElementById('metodoPago').value = 'efectivo'; // Valor por defecto
                liquidarCheckbox.checked = false; // Desmarcar el checkbox

                // Abrir una nueva ventana con el ticket y enviarlo a impresión
                var ticketWindow = window.open('../funciones/generar_ticket.php?id_folio=' + idFolio + '&folio_encode='+ folioEncode + '&nombre_cliente='+ nombreCliente + '&cantidad_abono=' + cantidadAbono + '&metodo_pago=' + metodoPago + '&nuevo_saldo=' + nuevoSaldo + '&nombre_cobrador=' + nombreCobrador + '&saldo_anterior='+ saldoAnterior, '_blank');
                //Espero a que cargue antes de imprimir
                ticketWindow.onload = function() {
                    ticketWindow.print();
                };

                // Refrescar la página o hacer lo necesario
                location.reload();
            }
        };

        xhr.send("id_folio=" + idFolio + "&cantidad_abono=" + cantidadAbono + "&metodo_pago=" + metodoPago + "&liquidar=" + liquidar + "&nuevo_saldo=" + nuevoSaldo);
    };
});
</script>




    <!--SCRIPT PARA MANEJAR EL CLIC DEL ÍCONO DE TRAZAR RUTAS -->
    <script>
// Variables globales
let map;
let cobradorMarker;
let lugarCobranzaMarker;

// Función para inicializar el mapa
function initMap(cobradorLat, cobradorLon) {
    // Crear el mapa centrado en la ubicación del cobrador
    map = L.map('map').setView([cobradorLat, cobradorLon], 13); // Cambiar el zoom a 13

    // Capa de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Marcador para la ubicación del cobrador
    cobradorMarker = L.marker([cobradorLat, cobradorLon]).addTo(map)
        .bindPopup('Ubicación del Cobrador')
        .openPopup();
}

// Función para mostrar el mapa y trazar la ruta
function mostrarRuta() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            const cobradorLat = position.coords.latitude;
            const cobradorLon = position.coords.longitude;

            // Mostrar el mapa
            document.getElementById('map').style.display = 'block';

            // Inicializa el mapa con la ubicación del cobrador
            initMap(cobradorLat, cobradorLon);

            // Obtener la dirección de cobranza
            const address = "<?php echo $direccionCobranza; ?>"; // Reemplaza con la dirección del lugar de cobranza
            console.log("Dirección de cobranza: " + address);

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const lugarCobranzaLat = data[0].lat; // Latitud del lugar de cobranza
                        const lugarCobranzaLon = data[0].lon; // Longitud del lugar de cobranza

                        // Actualiza el marcador del lugar de cobranza
                        lugarCobranzaMarker = L.marker([lugarCobranzaLat, lugarCobranzaLon]).addTo(map)
                            .bindPopup('Lugar de Cobranza')
                            .openPopup();

                        // Trazar la ruta
                        L.Routing.control({
                            waypoints: [
                                L.latLng(cobradorLat, cobradorLon),
                                L.latLng(lugarCobranzaLat, lugarCobranzaLon)
                            ],
                            routeWhileDragging: true
                        }).addTo(map);

                        // Iniciar el seguimiento de la ubicación en tiempo real
                        navigator.geolocation.watchPosition((position) => {
                            const newCobradorLat = position.coords.latitude;
                            const newCobradorLon = position.coords.longitude;

                            // Actualiza el marcador del cobrador
                            cobradorMarker.setLatLng([newCobradorLat, newCobradorLon]);
                            map.setView([newCobradorLat, newCobradorLon]); // Centrar el mapa en la nueva posición
                        }, (error) => {
                            console.error('Error al obtener la ubicación: ', error);
                        });
                    } else {
                        alert('No se pudo encontrar la dirección de cobranza.');
                    }
                })
                .catch(error => console.error('Error al obtener las coordenadas: ', error));
        }, (error) => {
            console.error('Error al obtener la ubicación: ', error);
            alert("No se pudo obtener la ubicación. Asegúrate de tener habilitada la geolocalización.");
        });
    } else {
        alert("La geolocalización no es soportada por este navegador.");
    }
}

// Manejo del clic en el botón
document.getElementById('btnTrazarRuta').onclick = mostrarRuta;
</script>




    
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>

