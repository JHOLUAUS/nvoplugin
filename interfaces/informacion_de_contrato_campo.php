<?php include('header.php'); ?>
<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<?php
// Conectar a la base de datos
include '../funciones/conexion.php';

if (isset($_GET['id_cliente']) && isset($_GET['id_folio'])) {
    $id_cliente = intval($_GET['id_cliente']); // Convertir a entero para mayor seguridad
    $id_folio = intval($_GET['id_folio']);     // Convertir a entero para mayor seguridad
    $id_usuario = $_SESSION['id_usuario'];
} else {
    // Manejar el caso en que no se pasen los parámetros
    die("Error: Parámetros faltantes.");
}

// Consultar los datos del contrato y los detalles relacionados
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
        clientecontrato.id_cliente = ? AND folios.id_folio = ?";

$stmt = $conn->prepare($query_contrato);

// Asociar los parámetros id_cliente e id_folio
$stmt->bind_param("ii", $id_cliente, $id_folio);
$stmt->execute();
$result = $stmt->get_result();

// Obtener los datos del contrato
$contrato = $result->fetch_assoc();

if (!$contrato) {
    die("Error: No se encontró el contrato.");
}

$molestiasSeleccionadas = explode(',', $contrato['molestias_HC']); // Convertir string a array
$materialSeleccionado = explode(',', $contrato['material']); // Convertir string a array
$tratamientoSeleccionado = explode(',', $contrato['tratamiento']); // Convertir string a array
$bifocalSeleccionado = explode(',', $contrato['bifocal']); // Convertir string a array
$promocionesSeleccionadas = explode(',', $contrato['promociones']); // Convertir string a array


$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Contrato</title>
    <link rel="stylesheet" href="../css/registro_cliente.css?v=<?php echo(rand()); ?>">
</head>
<body>
 <!-- REVISO A PARTIR DE AQUI-->

<div class="container">
    <h2>Detalle del Contrato por Actualizar</h2>


<form action="../funciones/actualizar_contrato.php" method="POST" class="form" enctype="multipart/form-data" onsubmit="return confirmarEnvio();">
        <!-- Campo oculto para el ID del cliente -->
        <input type="hidden" name="id_cliente" value="<?php echo $id_cliente ?>">
        <input type="hidden" name="id_folio" value="<?php echo $id_folio ?>">
        <input type="hidden" id="usuario" name="usuario" value="<?php echo $id_usuario; ?>">

        <div class="field-container">
            <label for="folio">Folio:</label>
            <input type="text" id="folio" name="folio" value="<?php echo $contrato['folios']; ?>" readonly>

    </div>

    <!-- Optometrista y Nombre -->
        <div class="field-container">
            <label for="optometrista">Optometrista:</label>
            <select id="optometrista" name="optometrista" required>
            <?php
            // Incluir la conexión a la base de datos
            include('../funciones/conexion.php');
            $usuarioSeleccionado = $contrato['id_optometrista']; // Asegúrate de que este valor venga del registro que estás editando

            // Consulta para obtener los usuarios que son "Optometristas" o "Administradores"
            $query = "SELECT id_usuario, nombre_usuario FROM usuarios WHERE tipo_usuario IN ('Optometrista', 'Administrador')";
            $result = $conn->query($query);
            // Verificar si hay resultados
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // El valor del option es el ID, pero se muestra el nombre
                    $selected = ($row['id_usuario'] == (int)$usuarioSeleccionado) ? 'selected' : '';
                    echo '<option value="' . $row['id_usuario'] . '" ' . $selected . '>' . $row['nombre_usuario'] . '</option>';
                }
            } else {
                echo '<option value="">No hay usuarios disponibles</option>';
            }

            // Cerrar la conexión
            ?>
            </select>
        </div>

        <div class="field-container">
            <label for="client_name">Nombre del Cliente:</label>
            <input type="text" id="client_name" name="client_name" value="<?php echo $contrato['nombre_cliente']; ?>" required>
        </div>

        <div class="field-container">
            <label for="client_alias">Alias del Cliente:</label>
            <input type="text" id="client_alias" name="client_alias" value="<?php echo $contrato['alias_cliente']; ?>" required>
        </div>

<!-- Sección Lugar de Venta -->
    <h3>Lugar de Venta</h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">

        <div class="form-row">
            
            <div class="field-container">
                <label for="cp">Código Postal:</label>
                <input type="text" id="cp" name="cp" placeholder="Código Postal" onblur="cargarDatosDireccion('cp', 'asentamiento', 'tipo_asent', 'municipio', 'estado')">
            </div>  
            <div class="form-row">
                <button type="button" id="buscar_datos">Buscar Datos</button>
                <label for="BD" style="font-style: italic; color:#00796b;">Nota: Ingresar el CP y presionar El botón para llenar datos de dirección automáticamente</label>
            </div>    
        </div>   

            <div class="field-container">
                <label for="calle">Calle:</label>
                <input type="text" id="calle" name="calle" value="<?php echo $contrato['calle_cliente']; ?>">
            </div> 

            <div class="form-row">
                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="text" id="numero" name="numero" value="<?php echo $contrato['numero_cliente']; ?>">
                </div>

                <div class="form-group">
                    <label for="numero">Departamento:</label>
                    <input type="text" id="departamento" name="departamento" value="<?php echo $contrato['departamento_cliente']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="al_lado">Al lado de:</label>
                    <input type="text" id="al_lado" name="al_lado" value="<?php echo $contrato['al_lado_cliente']; ?>">
                </div>

                <div class="form-group">
                    <label for="frente_a">Frente a:</label>
                    <input type="text" id="frente_a" name="frente_a" value="<?php echo $contrato['frente_a_cliente']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="entre_calles">Entre calles:</label>
                    <input type="text" id="entre_calles" name="entre_calles" value="<?php echo $contrato['entre_calles_cliente']; ?>">
                </div>

                <div class="form-group">
                    <label for="asentamiento">Asentamiento:</label>
                    <input type="text" id="asentamiento" name="asentamiento" value="<?php echo $contrato['asentamiento_cliente']; ?>">
                </div>
            </div>

            <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_asent">Tipo:</label>
                        <input type="text" id="tipo_asent" name="tipo_asent" value="<?php echo $contrato['tipo_asent']; ?>" >
                    </div>

                    <div class="form-group">
                        <label for="municipio">Municipio:</label>
                        <input type="text" id="municipio" name="municipio" value="<?php echo $contrato['municipio_cliente']; ?>">
                    </div>
                </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado" value="<?php echo $contrato['estado_cliente']; ?>">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono del Paciente:</label>
                    <input type="tel" id="telefono" name="telefono" value="<?php echo $contrato['telefono_cliente']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre_referencia">Nombre Referencia:</label>
                    <input type="text" id="nombre_referencia" name="nombre_referencia" value="<?php echo $contrato['referencia_cliente']; ?>">
                </div>

                <div class="form-group">
                    <label for="telefono_referencia">Teléfono Referencia:</label>
                    <input type="tel" id="telefono_referencia" name="telefono_referencia" value="<?php echo $contrato['tel_ref_cliente']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_casa">Tipo de Casa:</label>
                    <input type="text" id="tipo_casa" name="tipo_casa" value="<?php echo $contrato['tipo_casa_cliente']; ?>">
                </div>

                <div class="form-group">
                    <label for="color_casa">Color de Casa:</label>
                    <input type="text" id="color_casa" name="color_casa" value="<?php echo $contrato['color_casa_cliente']; ?>">
                </div>
            </div>

            <!-- Sección Lugar de Cobranza -->
            <div class="form-row">
            <h3>Lugar de Cobranza</h3>
                <button type="button" onclick="copiarDatosLugar()">Copiar Datos</button>
            </div>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">

        <div class="form-row">  
            <div class="field-container">
                <label for="codigo_postalCobranza">Código Postal:</label>
                <input type="text" id="cpCobranza" placeholder="Código Postal" onblur="cargarDatosCobranza('cpCobranza', 'asentamientoCobranza', 'tipo_asentCobranza', 'municipioCobranza', 'estadoCobranza')">
            </div>  
            <div class="field-container">
                <button type="button" id="buscar_datosCobranza">Buscar Datos</button>
            </div>    
        </div>   
    
            <div class="field-container">
                <label for="calleCobranza">Calle:</label>
                <input type="text" id="calleCobranza" name="calleCobranza" value="<?php echo $contrato['calle_cobranza']; ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="numeroCobranza">Número:</label>
                    <input type="text" id="numeroCobranza" name="numeroCobranza" value="<?php echo $contrato['numero_cobranza']; ?>">
                </div>
                <div class="form-group">
                    <label for="departamentoCobranza">Departamento:</label>
                    <input type="text" id="departamentoCobranza" name="departamentoCobranza" value="<?php echo $contrato['departamento_cobranza']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="alLadoCobranza">Al lado de:</label>
                    <input type="text" id="alLadoCobranza" name="alLadoCobranza" value="<?php echo $contrato['al_lado_cobranza']; ?>">
                </div>
                <div class="form-group">
                    <label for="frenteACobranza">Frente a:</label>
                    <input type="text" id="frenteACobranza" name="frenteACobranza" value="<?php echo $contrato['frente_a_cobranza']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="entreCallesCobranza">Entre calles:</label>
                    <input type="text" id="entreCallesCobranza" name="entreCallesCobranza" value="<?php echo $contrato['entre_calles_cobranza']; ?>">
                </div>

                <!-- Select dinámico 
                    <div class="form-group">
                    <label for="asentamientoCobranza">Asentamiento:</label>
                    <select id="asentamientoCobranza" name="asentamientoCobranza" required>
                        <option value="">Seleccione</option>
                    </select>
                </div>
                -->
                <div class="form-group">
                    <label for="asentamientoCobranza">Asentamiento:</label>
                    <input type="text" id="asentamientoCobranza" name="asentamientoCobranza" value="<?php echo $contrato['asentamiento_cobranza']; ?>">
                </div>
            </div>

            <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_asentCobranza">Tipo:</label>
                        <input type="text" id="tipo_asentCobranza" name="tipo_asentCobranza" value="<?php echo $contrato['tipo_asent_cobranza']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="municipioCobranza">Municipio:</label>
                        <input type="text" id="municipioCobranza" name="municipioCobranza" value="<?php echo $contrato['municipio_cobranza']; ?>">
                    </div>
                </div>

            <div class="form-row">
            <div class="form-group">
                    <label for="estadoCobranza">Estado:</label>
                    <input type="text" id="estadoCobranza" name="estadoCobranza" value="<?php echo $contrato['estado_cobranza']; ?>">
                </div>
                <div class="form-group">
                    <label for="zonaCobranza">Zona:</label>
                    <select id="zonaCobranza" name="zonaCobranza">
                        <option value="<?php echo $contrato['zona_cobranza']; ?>"><?php echo $contrato['zona_cobranza']; ?></option>
                        <option value="Pendiente">Pendiente</option>
                        <!-- Opciones dinámicas a llenar después -->
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipoCasaCobranza">Tipo de casa:</label>
                    <input type="text" id="tipoCasaCobranza" name="tipoCasaCobranza" value="<?php echo $contrato['tipo_casa_cobranza']; ?>">
                </div>
                <div class="form-group">
                    <label for="colorCasaCobranza">Color de casa:</label>
                    <input type="text" id="colorCasaCobranza" name="colorCasaCobranza" value="<?php echo $contrato['color_casa_cobranza']; ?>">
                </div>
            </div>

            <div class="form-row">
            <div class="field-container">
                    <label for="lugarEntrega">Seleccionar lugar de entrega:</label>
                    <select id="lugarEntrega" name="lugarEntrega">
                        <option value="Pendiente">Seleccionar</option>
                        <option value="Lugar de Venta">Lugar de Venta</option>
                        <option value="Lugar de Cobranza">Lugar de Cobranza</option>
                        <option value="Pendiente">Pendiente</option>
                        <!-- Opciones dinámicas a llenar después -->
                    </select>
                </div>
            </div>


    <div class="form-group">
    <h3>Sección para fotos</h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">
    <br>

    <div class="icon-row">
        <!-- Campo Identificación Frente -->
        <div class="form-group">
            <label for="ident_frente">Ident. Frente</label>
            <label for="ident_frente" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir identificación frente" title="Subir Identificación Frente">
            </label>
            <input type="file" id="ident_frente" name="ident_frente" accept="image/*" style="display: none;">
            <div class="img-preview">
                <img id="preview_ident_frente" style="display: none;">
            </div>
        </div>

        <!-- Campo Identificación Reversa -->
        <div class="form-group">
            <label for="ident_reversa">Ident. Reversa</label>
            <label for="ident_reversa" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir identificación reversa" title="Subir Identificación Reversa">
            </label>
            <input type="file" id="ident_reversa" name="ident_reversa" accept="image/*" style="display: none;">
            <div class="img-preview">
                <img id="preview_ident_reversa" style="display: none;">
            </div>
        </div>
    </div>

    <div class="icon-row">
        <!-- Campo Pagaré firmado -->
        <div class="form-group">
            <label for="ident_pagare">Pagaré</label>
            <label for="ident_pagare" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir pagaré firmado" title="Subir Pagaré Firmado">
            </label>
            <input type="file" id="ident_pagare" name="ident_pagare" accept="image/*" style="display: none;">
            <div class="img-preview">
                <img id="preview_ident_pagare" style="display: none;">
            </div>
        </div>

        <!-- Campo Comprobante de domicilio -->
        <div class="form-group">
            <label for="ident_comprobante">Comprobante Domicilio</label>
            <label for="ident_comprobante" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir comprobante de domicilio" title="Subir Comprobante de Domicilio">
            </label>
            <input type="file" id="ident_comprobante" name="ident_comprobante" accept="image/*" style="display: none;">
            <div class="img-preview">
                <img id="preview_ident_comprobante" style="display: none;">
            </div>
        </div>
    </div>

    <!-- Campo Casa -->
    <div class="icon-row">
        <div class="form-group">
            <label for="ident_casa">Casa</label>
            <label for="ident_casa" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir fotos de la casa" title="Subir Fotos de la Casa">
            </label>
            <input type="file" id="ident_casa" name="ident_casa" accept="image/*" multiple style="display: none;">
            <div class="img-preview">
                <img id="preview_ident_casa" style="display: none;">
            </div>
        </div>
        <div class="form-group">
            <label for="extra_casa">Armazón</label>
            <label for="extra_casa" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir fotos de la casa" title="Subir Fotos de la Casa">
            </label>
            <input type="file" id="extra_casa" name="extra_casa" accept="image/*" multiple style="display: none;">
            <div class="img-preview">
                <img id="preview_extra_casa" style="display: none;">
            </div>
        </div>
    </div>
</div>


 <!-- AQUI VOY -->


    <!-- Sección Historial Clínico -->
    <h3>Historial Clínico</h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">
    
    <div class="form-row">
        <div class="form-group">
            <label for="edadHC">Edad:</label>
            <input type="text" id="edadHC" name="edadHC" value="<?php echo $contrato['edad_HC']; ?>">
        </div>
        <div class="form-group">
            <label for="diagnosticoHC">Diagnostico:</label>
            <input type="text" id="diagnosticoHC" name="diagnosticoHC" value="<?php echo $contrato['departamento_HC']; ?>">
        </div>
    </div> 
    <div class="form-row">
        <div class="form-group">
            <label for="ocupacionHC">Ocupación:</label>
            <input type="text" id="ocupacionHC" name="ocupacionHC" value="<?php echo $contrato['ocupacion_HC']; ?>">
        </div>
        <div class="form-group">
            <label for="diabetesHC">¿Diabetes?:</label>
            <input type="text" id="diabetesHC" name="diabetesHC" value="<?php echo $contrato['diabetes_HC']; ?>">
        </div>
    </div> 
    <div class="form-row">
        <div class="form-group">
                <label for="hipertensionHC">¿Hipertension?:</label>
                <input type="text" id="hipertensionHC" name="hipertensionHC" value="<?php echo $contrato['hipertension_HC']; ?>">
            </div>

        <div class="form-group">
            <label for="embarazoHC">¿Está Embarazada?:</label>
            <input type="text" id="embarazoHC" name="embarazoHC" value="<?php echo $contrato['embarazo_HC']; ?>">
        </div>
    </div> 
    
    <div class="form-row">
        <div class="form-group">
            <label for="dormirHC">¿Cuántas horas durmió?:</label>
            <input type="number" id="dormirHC" name="dormirHC" value="<?php echo $contrato['horas_HC']; ?>">
        </div>
        <div class="form-group">
            <label for="actividadHC">Principal actividad en el día:</label>
            <input type="text" id="actividadHC" name="actividadHC" value="<?php echo $contrato['actividad_HC']; ?>">
        </div>
    </div> 

    <div class="field-container">
        <label for="problemaHC">Principal Problema que padece en sus Ojos:</label>
        <input type="text" id="problemaHC" name="problemaHC" value="<?php echo $contrato['principalproblema_HC']; ?>">
    </div>

    <br>

    <div class="form-row">
    <!-- Columna de Molestias -->
        <div class="form-group" style="flex: 1;">
            <label>Molestia:</label>
            <div class="checkbox-group">
                <div>
                    <input type="checkbox" id="dolorCabeza" name="molestias_HC[]" value="Dolor de cabeza" <?php if (in_array('Dolor de cabeza', $molestiasSeleccionadas)) echo 'checked'; ?>>
                    <label for="dolorCabeza">Dolor de cabeza</label>
                </div>
                <div>
                    <input type="checkbox" id="ardorOjos" name="molestias_HC[]" value="Ardor en los ojos" <?php if (in_array('Ardor en los ojos', $molestiasSeleccionadas)) echo 'checked'; ?>>
                    <label for="ardorOjos">Ardor en los ojos</label>
                </div>
                <div>
                    <input type="checkbox" id="golpeCabeza" name="molestias_HC[]" value="Golpe en cabeza" <?php if (in_array('Golpe en cabeza', $molestiasSeleccionadas)) echo 'checked'; ?>>
                    <label for="golpeCabeza">Golpe en cabeza</label>
                </div>
                <div>
                    <input type="checkbox" id="otraMolestia" name="molestias_HC[]" value="Otra" <?php if (in_array('Otra', $molestiasSeleccionadas)) echo 'checked'; ?>>
                    <label for="otraMolestia">Otra</label>
                </div>
                <br>
                <input type="text" id="otraMolestiaTexto" name="otraMolestiaTexto" placeholder="Especificar otra molestia" value="<?php echo $contrato['otramolestia_HC']; ?>">
            </div>
        </div>

        <!-- Columna de Último Examen -->
        <div class="form-group" style="flex: 1;">
            <label for="ultimoExamenHC">Último examen:</label>
            <input type="date" id="ultimoExamenHC" name="ultimoExamenHC" value="<?php echo $contrato['ultimoexamen_HC']; ?>">
        </div>
    </div>

        <!-- Sección Armazón -->
    <h3>Armazón </h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">
    <label for="notaArmazon" style="color:#00796b; font-style:italic;"><b>Nota:</b> Armazones pendientes por agregar</label>
    <div class="field-container">
            <label for="armazonA">Armazón:</label>
            <input type="text" id="armazonA" name="armazonA" value="<?php echo $contrato['tipo_armazon']; ?>">
        </div>
        <div class="field-container">
            <label for="paquetesA">Paquetes:</label>
            <select id="paquetesA" name="paquetesA">
                <option value="">Seleccionar</option>
                <?php
                // Array de paquetes con sus precios
                $paquetes = [
                    "Lectura" => 1100,
                    "Lente Proteccion" => 1690,
                    "Lectura Eco Jr" => 1690,
                    "Eco Junio" => 1690,
                    "Junior" => 1990,
                    "Dorado 1" => 2090,
                    "Dorado 2" => 2490,
                    "Platino" => 2790,
                    "Premium" => 2890,
                ];

                $paqueteSeleccionado = $contrato['paquete_armazon']; // Valor del paquete guardado en la BD

                // Generar las opciones dinámicamente
                foreach ($paquetes as $paquete => $precio) {
                    $selected = ($paquete == $paqueteSeleccionado) ? 'selected' : '';
                    echo '<option value="' . $paquete . '" data-precio="' . $precio . '" ' . $selected . '>' . $paquete . '</option>';
                }
                ?>
            </select>
        </div>

        
        <!-- Sub-sección Ojo derecho -->
         <br>
        <b><label style="color: #00796b;"> Ojo Derecho:</label></b>
        <br>
        <div class="form-row"> 
            <div class="form-group">
                <label for="esfericoAOD">Esférico:</label>
                <input type="text" id="esfericoAOD" name="esfericoAOD" placeholder="Esférico" value="<?php echo $contrato['esferico_AOD']; ?>">
            </div>
            <div class="form-group">
                <label for="cilindroAOD">Cilindro:</label>
                <input type="text" id="cilindroAOD" name="cilindroAOD" placeholder="Cilindro" value="<?php echo $contrato['cilindro_AOD']; ?>">
            </div>
        </div> 
        <div class="form-row">
            <div class="form-group">
                <label for="ejeAOD">Eje:</label>
                <input type="text" id="ejeAOD" name="ejeAOD" placeholder="Eje" value="<?php echo $contrato['eje_AOD']; ?>">
            </div>
            <div class="form-group">
                <label for="addAOD">Add:</label>
                <input type="text" id="addAOD" name="addAOD" placeholder="Add" value="<?php echo $contrato['add_AOD']; ?>">
            </div>
        </div> 
        <div class="form-group">
                <label for="altAOD">ALT:</label>
                <input type="text" id="altAOD" name="altAOD" placeholder="Alt" value="<?php echo $contrato['alt_AOD']; ?>">
        </div>
        <!-- Sub-sección Ojo Izquierdo -->
         <br>
        <b><label style="color: #00796b;"> Ojo Izquierdo:</label></b>
        <br>
        <div class="form-row">
            <div class="form-group">
                <label for="esfericoAOI">Esférico:</label>
                <input type="text" id="esfericoAOI" name="esfericoAOI" placeholder="Esférico" value="<?php echo $contrato['esferico_AOI']; ?>">
            </div>
            <div class="form-group">
                <label for="cilindroAOI">Cilindro:</label>
                <input type="text" id="cilindroAOI" name="cilindroAOI" placeholder="Cilindro" value="<?php echo $contrato['cilindro_AOI']; ?>">
            </div>
        </div> 
        <div class="form-row">
            <div class="form-group">
                <label for="ejeAOI">Eje:</label>
                <input type="text" id="ejeAOI" name="ejeAOI" placeholder="Eje" value="<?php echo $contrato['eje_AOI']; ?>">
            </div>
            <div class="form-group">
                <label for="addAOI">Add:</label>
                <input type="text" id="addAOI" name="addAOI" placeholder="Add" value="<?php echo $contrato['add_AOI']; ?>">
            </div>
        </div> 
        <div class="form-row">
            <div class="field-container">
                    <label for="altAOI">ALT:</label>
                    <input type="text" id="altAOI" name="altAOI" placeholder="Alt" value="<?php echo $contrato['alt_AOI']; ?>">
            </div>
        </div>
        <br>

        <!-- sub sección material y tratamiento -->

     <div class="form-row">

             <!-- Columna Material -->
        <div class="form-group" style="flex: 1;">
               <b><label style="color: #00796b;"> Material:</label></b> 
                <div class="checkbox-groupMat">
                    <div>
                        <input type="checkbox" id="matCR" name="material[]" value="CR" data-precio="0" <?php if (in_array('CR', $materialSeleccionado)) echo 'checked'; ?>>
                        <label for="matCR">CR</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matHiIndex" name="material[]" value="Hi Index" <?php if (in_array('Hi Index', $materialSeleccionado)) echo 'checked'; ?>>
                        <label for="matHiIndex">Hi Index</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matPolicarbonato" name="material[]" value="Policarbonato" data-precio="300" <?php if (in_array('Policarbonato', $materialSeleccionado)) echo 'checked'; ?>>
                        <label for="matPolicarbonato">Policarbonato</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matOtro" name="material[]" value="Otro" <?php if (in_array('Otro', $materialSeleccionado)) echo 'checked'; ?>>
                        <label for="matOtro">Otro</label>
                    </div>
                    <input type="text" id="matOtroTexto" name="matOtroTexto" placeholder="Especificar otro" value="<?php echo $contrato['matOtroTexto']; ?>">
<!-- VOY ACA -->                    
                    <input type="text" id="matPrecio" name="matPrecio" placeholder="Precio/Costo" readonly>
                </div>
        </div>

        <!-- Columna tratamiento -->
        <div class="form-group" style="flex: 1;">
            <b><label style="color:#00796b">Tratamiento:</label></b>
                <div class="checkbox-group">
                    <div>
                        <input type="checkbox" id="tratAR" name="tratamiento[]" value="AR" data-precio="0" <?php if (in_array('AR', $tratamientoSeleccionado)) echo 'checked'; ?>>
                        <label for="tratAR">A/R</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratBlueRay" name="tratamiento[]" value="BlueRay" data-precio="700" <?php if (in_array('BlueRay', $tratamientoSeleccionado)) echo 'checked'; ?>>
                        <label for="tratBlueRay">Blu-Ray</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratEspejo" name="tratamiento[]" value="Espejo" data-precio="" <?php if (in_array('Espejo', $tratamientoSeleccionado)) echo 'checked'; ?>>
                        <label for="tratEspejo">Espejo</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratFotocromatico" name="tratamiento[]" value="Fotocromatico" data-precio="700" <?php if (in_array('Fotocromatico', $tratamientoSeleccionado)) echo 'checked'; ?>>
                        <label for="tratFotocromatico">Fotocromático</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratTinte" name="tratamiento[]" value="Tinte" data-precio="300" <?php if (in_array('Tinte', $tratamientoSeleccionado)) echo 'checked'; ?>>
                        <label for="tratTinte">Tinte</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratOtro" name="tratamiento[]" value="Otro" data-precio="700" <?php if (in_array('Otro', $tratamientoSeleccionado)) echo 'checked'; ?>>
                        <label for="tratOtro">Otro</label>
                    </div>


                    <input type="text" id="tratOtroTexto" name="tratOtroTexto" placeholder="Otro" value="<?php echo $contrato['tratOtroTexto']; ?>">
                    <input type="text" id="tratPrecio" name="tratPrecio" placeholder="Precio/Costo">
                </div>
        </div>
        <!-- Columna tipo bifocal -->
        <div class="form-group" style="flex: 1;">
            <b><label style="color:#00796b">Tipo de Bifocal:</label></b>
                <div class="checkbox-groupBi">
                    <div>
                        <input type="checkbox" id="biBlend" name="bifocal[]" value="Blend" <?php if (in_array('Otro', $bifocalSeleccionado)) echo 'checked'; ?>>
                        <label for="biBlend">Blend</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biFT" name="bifocal[]" value="FT" <?php if (in_array('Otro', $bifocalSeleccionado)) echo 'checked'; ?>>
                        <label for="biFT">FT</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biNA" name="bifocal[]" value="NA" <?php if (in_array('Otro', $bifocalSeleccionado)) echo 'checked'; ?>>
                        <label for="biNA">N/A</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biProgresivo" name="bifocal[]" value="Progresivo" <?php if (in_array('Otro', $bifocalSeleccionado)) echo 'checked'; ?>>
                        <label for="biProgresivo">Progresivo</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biOtro" name="bifocal[]" value="Otro" <?php if (in_array('Otro', $bifocalSeleccionado)) echo 'checked'; ?>>
                        <label for="biOtro">Otro</label>
                    </div>

                    
                <div class="form-row">
                    <div class="field-container">
                        <input type="text" id="biOtroTexto" name="biOtroTexto" placeholder="Otro" value="<?php echo $contrato['biOtroTexto']; ?>">
                        <input type="text" id="biPrecio" name="biPrecio" placeholder="Precio/Costo">
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <div class="form-group" style="flex: 1;">
        <h5>Observaciones:</h5>
        <div class="form-row">
        <div class="field-container">
                <label for="obsInterno">Observaciones Interno:</label>
                <input type="text" id="obsInterno" name="obsInterno" placeholder="Observaciones Interno" value="<?php echo $contrato['observacion_int']; ?>">
            </div> 
        </div>

        </div>


        <h3>Forma de pago y Promoción:</h3>
        <hr style="border: 2px solid #00796b; margin: 5px 0;">
        <div class="form-row">
            <div class="field-container">
                    <label for="forma_pago">Forma de Pago:</label>
                    <select id="forma_pago" name="forma_pago" required>
                        <option value="<?php echo $contrato['forma_pago']; ?>"><?php echo $contrato['forma_pago']; ?></option>
                        <option value="Semanal" >Semanal</option>
                        <option value="Quincenal" >Quincenal</option>
                        <option value="Mensual">Mensual</option>
                    </select>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <b><label style="color:#00796b">Promoción</label></b>
                        <div class="checkbox-groupPromo">
                            <div>
                                <input type="checkbox" id="promoSpray" name="promo[]" value="Spray" <?php if (in_array('Spray', $promocionesSeleccionadas)) echo 'checked'; ?>>
                                <label for="promoSpray">Spray - $100</label>
                            </div>
                            <div>
                                <input type="checkbox" id="promoGotas" name="promo[]" value="Gotas" <?php if (in_array('Gotas', $promocionesSeleccionadas)) echo 'checked'; ?>>
                                <label for="promoGotas">Gotas - $250</label>
                            </div>
                            <div>
                                <input type="checkbox" id="promoPoliza" name="promo[]" value="Póliza" <?php if (in_array('Póliza', $promocionesSeleccionadas)) echo 'checked'; ?>>
                                <label for="promoPoliza">Póliza - $250</label>
                            </div>
                            <div>
                                <input type="checkbox" id="promoEnganche" name="promo[]" value="Enganche 100+100" data-precio="200" <?php if (in_array('Enganche 100+100', $promocionesSeleccionadas)) echo 'checked'; ?>>
                                <label for="promoEnganche">Enganche 100 + 100</label>
                            </div>
                        </div>
                </div>
        </div>
                <div class="form-row">
                    <div class="field-container">
                            <label for="metodo_pago_promo">Método de pago promoción:</label>
                            <select id="metodo_pago_promo" name="metodo_pago_promo">
                                <option value="Efectivo" >Efectivo</option>
                                <option value="Transferencia" >Transferencia</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select>
                    </div>
            </div>
        </div>

        <h3>Total:</h3>
        <hr style="border: 2px solid #00796b; margin: 5px 0;">
            <div class="form-row">
                <div class="field-container">
                    <label for="total">Total:</label>
                    <input type="number" id="total" name="total" placeholder="0.0">
                </div>
                <label for="notaTotal" style="color:red; font-style:italic;"><b>Nota:</b> Por ahora puede modificar el campo total en caso de que surga error en la suma de totales.</label>
            </div>
            

        <div class="form-section">
                <button type="submit" class="btn-submit">Guardar Cambios</button>
                <button id="btnCancelar" type="button" style="background-color: red;" onclick="cancelar()">Cancelar</button>
        </div>



    </form>
    </div>


<script>
    // Pasar las rutas de las imágenes a JavaScript
    const imagenesExistentes = {
        ident_frente: "<?php echo $contrato['ident_frente']; ?>",
        ident_reversa: "<?php echo $contrato['ident_reversa']; ?>",
        ident_pagare: "<?php echo $contrato['ident_pagare']; ?>",
        ident_comprobante: "<?php echo $contrato['ident_comprobante']; ?>",
        ident_casa: "<?php echo $contrato['ident_casa']; ?>",
        extra_casa: "<?php echo $contrato['extra_casa']; ?>"
    };
</script>
<script>
//ESTE SCRIPT ES PARA MANEJAR LOS CAMBIOS EN LAS IMAGENES
// Función para redimensionar imágenes
function resizeImage(input, maxWidth, maxHeight, callback) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function (event) {
            const img = new Image();
            img.onload = function () {
                // Crear canvas para redimensionar
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");

                let width = img.width;
                let height = img.height;

                // Ajustar dimensiones manteniendo la proporción
                if (width > maxWidth || height > maxHeight) {
                    if (width > height) {
                        height = Math.round((height / width) * maxWidth);
                        width = maxWidth;
                    } else {
                        width = Math.round((width / height) * maxHeight);
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                // Generar imagen redimensionada en base64
                const resizedImage = canvas.toDataURL("image/jpeg", 0.6); // Calidad 80%
                callback(resizedImage);
            };
            img.src = event.target.result;
        };

        reader.readAsDataURL(file);
    } else {
        alert("No se pudo cargar la imagen.");
    }
}

// Función para manejar eventos de cambio
function handleFileInput(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    input.addEventListener("change", function () {
        const maxWidth = 800; // Ancho máximo
        const maxHeight = 800; // Alto máximo

        resizeImage(input, maxWidth, maxHeight, function (resizedImage) {
            preview.src = resizedImage;
            preview.style.display = "block";
        });
    });
}


function cargarImagenesExistentes(imagenes) {
    Object.keys(imagenes).forEach((key) => {
        if (imagenes[key]) {
            const preview = document.getElementById(`preview_${key}`);
            preview.src = "../fotosClientes/" + imagenes[key]; // Asumiendo que la ruta es relativa a la carpeta 'uploads'
            preview.style.display = "block";
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    ["ident_frente", "ident_reversa", "ident_pagare", "ident_comprobante", "ident_casa", "extra_casa"].forEach((id) => {
        handleFileInput(id, `preview_${id}`);
    });

    if (typeof imagenesExistentes !== "undefined") {
        cargarImagenesExistentes(imagenesExistentes);
    }
});
</script>

    <script>
        function confirmarEnvio() {
        return confirm("¿Estás seguro de que deseas enviar este formulario?");
    }
        function cancelar() {
            // Redirige a otra página sin reemplazarla en el historial
            window.location.assign('../interfaces/lista_contratos_campo.php'); 
        }
    </script>

<script src="../funciones/codigo_postal.js"></script>
    <script src="../funciones/actualizartotales.js"></script>
    <script>
        function copiarDatosLugar() {
             // Campos relacionados entre Lugar de Venta y Lugar de Cobranza
            const campos = [
                { venta: "cp", cobranza: "cpCobranza" },
                { venta: "calle", cobranza: "calleCobranza" },
                { venta: "numero", cobranza: "numeroCobranza" },
                { venta: "departamento", cobranza: "departamentoCobranza" },
                { venta: "al_lado", cobranza: "alLadoCobranza" },
                { venta: "frente_a", cobranza: "frenteACobranza" },
                { venta: "entre_calles", cobranza: "entreCallesCobranza" },
                { venta: "asentamiento", cobranza: "asentamientoCobranza" },
                { venta: "tipo_asent", cobranza: "tipo_asentCobranza" },
                { venta: "municipio", cobranza: "municipioCobranza" },
                { venta: "estado", cobranza: "estadoCobranza" },
                { venta: "tipo_casa", cobranza: "tipoCasaCobranza" },
                { venta: "color_casa", cobranza: "colorCasaCobranza" },
                { venta: "asentamientoEditable", cobranza: "asentamientoEditableCobranza" }
            ];

            // Iterar sobre los campos y copiar valores
            campos.forEach(campo => {
                const valor = document.getElementById(campo.venta).value;
                document.getElementById(campo.cobranza).value = valor;
            });

            alert("Datos copiados de Lugar de Venta a Lugar de Cobranza.");
        }
    </script>
  
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>