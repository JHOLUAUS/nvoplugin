<?php include('header.php'); ?>

<?php
    // Incluir la conexión a la base de datos
    include('../funciones/conexion.php');

    // Obtener el último folio registrado
    $query = "SELECT folios FROM folios ORDER BY id_folio DESC LIMIT 1";
    $result = $conn->query($query);
    $ultimoFolio = $result->fetch_assoc();

    // Si existe un folio, se incrementa, si no, empieza desde 1
    if ($ultimoFolio) {
        // Extraer solo el número del folio (asumiendo que está en el formato "Fo. 000000001")
        $numeroFolio = intval(substr($ultimoFolio['folios'], 4));
        $nuevoFolio = $numeroFolio + 1;
    } else {
        $nuevoFolio = 1;
    }

    // Formatear el nuevo folio como "Fo. 000000001"
    $folioFormateado = 'Fo. ' . str_pad($nuevoFolio, 9, '0', STR_PAD_LEFT);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente</title>
    <link rel="stylesheet" href="../css/registro_cliente.css?v=<?php echo(rand()); ?>">
</head>
<body>
<div class="container">
    <h2>Registro de Nuevo Cliente</h2>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">
    <br>

    
<form action="../funciones/registrar_contrato.php" method="post" class="form" enctype="multipart/form-data">

    <div class="field-container">
            <label for="folio">Folio:</label>
            <input type="text" id="folio" name="folio" value="<?php echo $folioFormateado; ?>" readonly>

        </div>

    <!-- Optometrista y Nombre -->
        <div class="field-container">
            <label for="optometrista">Optometrista:</label>
            <select id="optometrista" name="optometrista" required>
            <?php
            // Incluir la conexión a la base de datos
            include('../funciones/conexion.php');

            // Consulta para obtener los usuarios que son "Optometristas" o "Administradores"
            $query = "SELECT id_usuario, nombre_usuario FROM usuarios WHERE tipo_usuario IN ('Optometrista', 'Administrador')";
            $result = $conn->query($query);
            // Verificar si hay resultados
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // El valor del option es el ID, pero se muestra el nombre
                    echo '<option value="' . $row['id_usuario'] . '">' . $row['nombre_usuario'] . '</option>';
                }
            } else {
                echo '<option value="">No hay usuarios disponibles</option>';
            }

            // Cerrar la conexión
            $conn->close();
            ?>
            </select>
        </div>

        <div class="field-container">
            <label for="client_name">Nombre del Cliente:</label>
            <input type="text" id="client_name" name="client_name" required>
        </div>

        <div class="field-container">
            <label for="client_alias">Alias del Cliente:</label>
            <input type="text" id="client_alias" name="client_alias" required>
        </div>

<!-- Sección Lugar de Venta -->
    <h3>Lugar de Venta</h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">

        <div class="form-row">
            
            <div class="field-container">
                <label for="codigo_postal">Código Postal:</label>
                <input type="text" id="cp" name="cp" placeholder="Código Postal" onblur="cargarDatosDireccion('cp', 'asentamiento', 'tipo_asent', 'municipio', 'estado')" required>
            </div>  
            <div class="form-row">
                <button type="button" id="buscar_datos">Buscar Datos</button>
                <label for="BD" style="font-style: italic; color:#00796b;">Nota: Ingresar el CP y presionar El botón para llenar datos de dirección automáticamente</label>
            </div>    
        </div>   

            <div class="field-container">
                <label for="calle">Calle:</label>
                <input type="text" id="calle" name="calle" required>
            </div> 

            <div class="form-row">
                <div class="form-group">
                    <label for="numero">Número:</label>
                    <input type="text" id="numero" name="numero" required>
                </div>

                <div class="form-group">
                    <label for="numero">Departamento:</label>
                    <input type="text" id="departamento" name="departamento" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="al_lado">Al lado de:</label>
                    <input type="text" id="al_lado" name="al_lado" required>
                </div>

                <div class="form-group">
                    <label for="frente_a">Frente a:</label>
                    <input type="text" id="frente_a" name="frente_a" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="entre_calles">Entre calles:</label>
                    <input type="text" id="entre_calles" name="entre_calles" required>
                </div>

                <div class="form-group">
                    <label for="asentamiento">Asentamiento:</label>
                    <!-- Select dinámico -->
                    <select id="asentamiento" name="asentamiento" onchange="sincronizarCampoTexto()">
                        <option value="">Seleccione</option>
                        <!-- Opciones dinámicas -->
                    </select>
                    <br>
                    <!-- Campo de texto habilitado desde el principio -->
                    <input type="text" id="asentamientoEditable" name="asentamientoEditable" 
                        placeholder="Escribe el asentamiento" oninput="sincronizarCombobox()">
                    <small style="font-style: italic;">Si no encuentras el asentamiento, escríbelo aquí.</small>
                </div>
            </div>

            <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_asent">Tipo:</label>
                        <input type="text" id="tipo_asent" name="tipo_asent" >
                    </div>

                    <div class="form-group">
                        <label for="municipio">Municipio:</label>
                        <input type="text" id="municipio" name="municipio" >
                    </div>
                </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <input type="text" id="estado" name="estado">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono del Paciente:</label>
                    <input type="tel" id="telefono" name="telefono" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre_referencia">Nombre Referencia:</label>
                    <input type="text" id="nombre_referencia" name="nombre_referencia" required>
                </div>

                <div class="form-group">
                    <label for="telefono_referencia">Teléfono Referencia:</label>
                    <input type="tel" id="telefono_referencia" name="telefono_referencia" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_casa">Tipo de Casa:</label>
                    <input type="text" id="tipo_casa" name="tipo_casa" required>
                </div>

                <div class="form-group">
                    <label for="color_casa">Color de Casa:</label>
                    <input type="text" id="color_casa" name="color_casa" required>
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
                <input type="text" id="cpCobranza" placeholder="Código Postal" onblur="cargarDatosCobranza('cpCobranza', 'asentamientoCobranza', 'tipo_asentCobranza', 'municipioCobranza', 'estadoCobranza')" required>
            </div>  
            <div class="field-container">
                <button type="button" id="buscar_datosCobranza">Buscar Datos</button>
            </div>    
        </div>   
    
            <div class="field-container">
                <label for="calleCobranza">Calle:</label>
                <input type="text" id="calleCobranza" name="calleCobranza" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="numeroCobranza">Número:</label>
                    <input type="text" id="numeroCobranza" name="numeroCobranza" required>
                </div>
                <div class="form-group">
                    <label for="departamentoCobranza">Departamento:</label>
                    <input type="text" id="departamentoCobranza" name="departamentoCobranza" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="alLadoCobranza">Al lado de:</label>
                    <input type="text" id="alLadoCobranza" name="alLadoCobranza" required>
                </div>
                <div class="form-group">
                    <label for="frenteACobranza">Frente a:</label>
                    <input type="text" id="frenteACobranza" name="frenteACobranza" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="entreCallesCobranza">Entre calles:</label>
                    <input type="text" id="entreCallesCobranza" name="entreCallesCobranza" required>
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
                    <!-- Select dinámico -->
                    <select id="asentamientoCobranza" name="asentamientoCobranza" onchange="sincronizarCampoTextoCobranza()">
                        <option value="">Seleccione</option>
                        <!-- Opciones dinámicas -->
                    </select>
                    <br>
                    <!-- Campo de texto habilitado desde el principio -->
                    <input type="text" id="asentamientoEditableCobranza" name="asentamientoEditableCobranza" 
                        placeholder="Escribe el asentamiento" oninput="sincronizarComboboxCobranza()">
                    <small style="font-style: italic;">Si no encuentras el asentamiento, escríbelo aquí.</small>
                </div>




            </div>

            <div class="form-row">
                    <div class="form-group">
                        <label for="tipo_asentCobranza">Tipo:</label>
                        <input type="text" id="tipo_asentCobranza" name="tipo_asentCobranza">
                    </div>

                    <div class="form-group">
                        <label for="municipioCobranza">Municipio:</label>
                        <input type="text" id="municipioCobranza" name="municipioCobranza">
                    </div>
                </div>

            <div class="form-row">
            <div class="form-group">
                    <label for="estadoCobranza">Estado:</label>
                    <input type="text" id="estadoCobranza" name="estadoCobranza" >
                </div>
                <div class="form-group">
                    <label for="zonaCobranza">Zona:</label>
                    <select id="zonaCobranza" name="zonaCobranza" required>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Zona 1">Zona 1</option>
                        <!-- Opciones dinámicas a llenar después -->
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipoCasaCobranza">Tipo de casa:</label>
                    <input type="text" id="tipoCasaCobranza" name="tipoCasaCobranza" required>
                </div>
                <div class="form-group">
                    <label for="colorCasaCobranza">Color de casa:</label>
                    <input type="text" id="colorCasaCobranza" name="colorCasaCobranza" required>
                </div>
            </div>

            <div class="form-row">
            <div class="field-container">
                    <label for="lugarEntrega">Seleccionar lugar de entrega:</label>
                    <select id="lugarEntrega" name="lugarEntrega" required>
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
    <label for="notaFotos" style="color:#00796b; font-style:italic;"><b>Nota:</b> Subir todas las fotos. Si no cuenta con algún documento, subir una foto repetida.</label>
    <br>

    <div class="icon-row">
        <!-- Campo Identificación Frente -->
        <div class="form-group">
            <label for="id_frente">Ident. Frente</label>
            <label for="id_frente" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir identificación frente" title="Subir Identificación Frente">
            </label>
            <input type="file" id="id_frente" name="id_frente" accept="image/*" capture="environment" style="display: none;" onchange="previewImage(this, 'preview_id_frente')">
            <img id="preview_id_frente" style="display:none; max-width: 100px; margin-top: 10px;">
        </div>

        <!-- Campo Identificación Reversa -->
        <div class="form-group">
            <label for="id_reversa">Ident. Reversa</label>
            <label for="id_reversa" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir identificación reversa" title="Subir Identificación Reversa">
            </label>
            <input type="file" id="id_reversa" name="id_reversa" accept="image/*" capture="environment" style="display: none;" onchange="previewImage(this, 'preview_id_reversa')">
            <img id="preview_id_reversa" style="display:none; max-width: 100px; margin-top: 10px;">
        </div>
    </div>

    <div class="icon-row">
        <!-- Campo Pagaré firmado -->
        <div class="form-group">
            <label for="pagare">Pagaré</label>
            <label for="pagare" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir pagaré firmado" title="Subir Pagaré Firmado">
            </label>
            <input type="file" id="pagare" name="pagare" accept="image/*" capture="environment" style="display: none;" onchange="previewImage(this, 'preview_pagare')">
            <img id="preview_pagare" style="display:none; max-width: 100px; margin-top: 10px;">
        </div>

        <!-- Campo Comprobante de domicilio -->
        <div class="form-group">
            <label for="comprobante_domicilio">Comprobante Domicilio</label>
            <label for="comprobante_domicilio" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir comprobante de domicilio" title="Subir Comprobante de Domicilio">
            </label>
            <input type="file" id="comprobante_domicilio" name="comprobante_domicilio" accept="image/*" capture="environment" style="display: none;" onchange="previewImage(this, 'preview_comprobante_domicilio')">
            <img id="preview_comprobante_domicilio" style="display:none; max-width: 100px; margin-top: 10px;">
        </div>
    </div>

    <!-- Campo Casa -->
    <div class="icon-row">
        <div class="form-group">
            <label for="casa">Casa</label>
            <label for="casa" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir fotos de la casa" title="Subir Fotos de la Casa">
            </label>
            <input type="file" id="casa" name="casa" accept="image/*" capture="environment" multiple style="display: none;" onchange="previewImage(this, 'preview_casa')">
            <img id="preview_casa" style="display:none; max-width: 100px; margin-top: 10px;">
        </div>
        <div class="form-group">
            <label for="casa2">Foto extra para casa</label>
            <label for="casa2" class="icon-upload">
                <img src="../imagenes/id.png" alt="Subir fotos de la casa" title="Subir Fotos de la Casa">
            </label>
            <input type="file" id="casa2" name="casa2" accept="image/*" capture="environment" multiple style="display: none;" onchange="previewImage(this, 'preview_casa2')">
            <img id="preview_casa2" style="display:none; max-width: 100px; margin-top: 10px;">
        </div>
    </div>
</div>

    <script>
        function previewImage(input, previewId) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = "none";
            }
        }
    </script>

    <!-- Sección Historial Clínico -->
    <h3>Historial Clínico</h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">
    <br>
    
    <div class="form-row">
        <div class="form-group">
            <label for="edadHC">Edad:</label>
            <input type="text" id="edadHC" name="edadHC" required>
        </div>
        <div class="form-group">
            <label for="diagnosticoHC">Diagnostico:</label>
            <input type="text" id="diagnosticoHC" name="diagnosticoHC" required>
        </div>
    </div> 
    <div class="form-row">
        <div class="form-group">
            <label for="ocupacionHC">Ocupación:</label>
            <input type="text" id="ocupacionHC" name="ocupacionHC" required>
        </div>
        <div class="form-group">
            <label for="diabetesHC">¿Diabetes?:</label>
            <select id="diabetesHC" name="diabetesHC" required>
            <option value="">Seleccionar</option>
            <option value="Si">Si</option>
            <option value="No">No</option>
            </select>
        </div>
    </div> 
    <div class="form-row">
        <div class="form-group">
                <label for="hipertensionHC">¿Hipertension?:</label>
                <select id="hipertensionHC" name="hipertensionHC" required>
                <option value="">Seleccionar</option>
                <option value="Si">Si</option>
                <option value="No">No</option>
                </select>
            </div>

        <div class="form-group">
            <label for="embarazoHC">¿Está Embarazada?:</label>
            <select id="embarazoHC" name="embarazoHC" required>
            <option value="">Seleccionar</option>
            <option value="Si">Si</option>
            <option value="No">No</option>
            </select>
        </div>
    </div> 
    
    <div class="form-row">
        <div class="form-group">
            <label for="dormirHC">¿Cuántas horas durmió?:</label>
            <input type="number" id="dormirHC" name="dormirHC" placeholder="Ej: 8" required>
        </div>
        <div class="form-group">
            <label for="actividadHC">Principal actividad en el día:</label>
            <input type="text" id="actividadHC" name="actividadHC" required>
        </div>
    </div> 

    <div class="field-container">
        <label for="problemaHC">Principal Problema que padece en sus Ojos:</label>
        <input type="text" id="problemaHC" name="problemaHC" required>
    </div>

    <br>

    <div class="form-row">
    <!-- Columna de Molestias -->
        <div class="form-group" style="flex: 1;">
            <label>Molestia:</label>
            <div class="checkbox-group">
                <div>
                    <input type="checkbox" id="dolorCabeza" name="molestias_HC[]" value="Dolor de cabeza">
                    <label for="dolorCabeza">Dolor de cabeza</label>
                </div>
                <div>
                    <input type="checkbox" id="ardorOjos" name="molestias_HC[]" value="Ardor en los ojos">
                    <label for="ardorOjos">Ardor en los ojos</label>
                </div>
                <div>
                    <input type="checkbox" id="golpeCabeza" name="molestias_HC[]" value="Golpe en cabeza">
                    <label for="golpeCabeza">Golpe en cabeza</label>
                </div>
                <div>
                    <input type="checkbox" id="otraMolestia" name="molestias_HC[]" value="Otra">
                    <label for="otraMolestia">Otro</label>
                </div>
                <br>
                <input type="text" id="otraMolestiaTexto" name="otraMolestiaTexto" placeholder="Especificar otra molestia">
            </div>
        </div>

        <!-- Columna de Último Examen -->
        <div class="form-group" style="flex: 1;">
            <label for="ultimoExamenHC">Último examen:</label>
            <input type="date" id="ultimoExamenHC" name="ultimoExamenHC" required>
        </div>
    </div>

        <!-- Sección Armazón -->
    <h3>Armazón </h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">
    <label for="notaArmazon" style="color:#00796b; font-style:italic;"><b>Nota:</b> Armazones pendientes por agregar</label>
    <div class="field-container">
            <label for="armazonA">Armazón:</label>
            <select id="armazonA" name="armazonA">
            <option value="">Seleccionar</option>
            </select>
        </div>
        <div class="field-container">
            <label for="paquetesA">Paquetes:</label>
            <select id="paquetesA" name="paquetesA" required>
                <option value="">Seleccionar</option>
                <option value="Lectura" data-precio="1100">Lectura</option>
                <option value="Lente Proteccion" data-precio="1690">Lente Protección</option>
                <option value="Lectura Eco Jr" data-precio="1690">Lectura (Eco Junior)</option>
                <option value="Eco Junio" data-precio="1690">ECO Junior</option>
                <option value="Junior" data-precio="1990">Junior</option>
                <option value="Dorado 1" data-precio="2090">Dorado I</option>
                <option value="Dorado 2" data-precio="2490">Dorado II</option>
                <option value="Platino" data-precio="2790">Platino</option>
                <option value="Premium" data-precio="2890">Premium</option>
            </select>
        </div>
        
        <!-- Sub-sección Ojo derecho -->
         <br>
        <b><label style="color: #00796b;"> Ojo Derecho:</label></b>
        <br>
        <div class="form-row"> 
            <div class="form-group">
                <label for="esfericoAOD">Esférico:</label>
                <input type="text" id="esfericoAOD" name="esfericoAOD" placeholder="Esférico">
            </div>
            <div class="form-group">
                <label for="cilindroAOD">Cilindro:</label>
                <input type="text" id="cilindroAOD" name="cilindroAOD" placeholder="Cilindro">
            </div>
        </div> 
        <div class="form-row">
            <div class="form-group">
                <label for="ejeAOD">Eje:</label>
                <input type="text" id="ejeAOD" name="ejeAOD" placeholder="Eje">
            </div>
            <div class="form-group">
                <label for="addAOD">Add:</label>
                <input type="text" id="addAOD" name="addAOD" placeholder="Add">
            </div>
        </div> 
        <div class="form-group">
                <label for="altAOD">ALT:</label>
                <input type="text" id="altAOD" name="altAOD" placeholder="Alt">
        </div>
        <!-- Sub-sección Ojo Izquierdo -->
         <br>
        <b><label style="color: #00796b;"> Ojo Izquierdo:</label></b>
        <br>
        <div class="form-row">
            <div class="form-group">
                <label for="esfericoAOI">Esférico:</label>
                <input type="text" id="esfericoAOI" name="esfericoAOI" placeholder="Esférico">
            </div>
            <div class="form-group">
                <label for="cilindroAOI">Cilindro:</label>
                <input type="text" id="cilindroAOI" name="cilindroAOI" placeholder="Cilindro">
            </div>
        </div> 
        <div class="form-row">
            <div class="form-group">
                <label for="ejeAOI">Eje:</label>
                <input type="text" id="ejeAOI" name="ejeAOI" placeholder="Eje">
            </div>
            <div class="form-group">
                <label for="addAOI">Add:</label>
                <input type="text" id="addAOI" name="addAOI" placeholder="Add">
            </div>
        </div> 

        <div class="form-row">
            <div class="field-container">
                    <label for="altAOI">ALT:</label>
                    <input type="text" id="altAOI" name="altAOI" placeholder="Alt">
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
                        <input type="checkbox" id="matCR" name="material[]" value="CR" data-precio="300">
                        <label for="matCR">CR</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matHiIndex" name="material[]" value="Hi Index">
                        <label for="matHiIndex">Hi Index</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matPolicarbonato" name="material[]" value="Policarbonato" data-precio="300">
                        <label for="matPolicarbonato">Policarbonato</label>
                    </div>
                    <div>
                        <input type="checkbox" id="matOtro" name="material[]" value="Otro">
                        <label for="matOtro">Otro</label>
                    </div>
                    <input type="text" id="matOtroTexto" name="matOtroTexto" placeholder="Especificar otro">
                    <input type="text" id="matPrecio" name="matPrecio" placeholder="Precio/Costo" readonly>
                </div>
        </div>

        <!-- Columna tratamiento -->
        <div class="form-group" style="flex: 1;">
            <b><label style="color:#00796b">Tratamiento:</label></b>
                <div class="checkbox-group">
                    <div>
                        <input type="checkbox" id="tratAR" name="tratamiento[]" value="AR" data-precio="0">
                        <label for="tratAR">A/R</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratBlueRay" name="tratamiento[]" value="BlueRay" data-precio="700">
                        <label for="tratBlueRay">Blu-Ray</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratEspejo" name="tratamiento[]" value="Espejo" data-precio="">
                        <label for="tratEspejo">Espejo</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratFotocromatico" name="tratamiento[]" value="Fotocromatico" data-precio="700">
                        <label for="tratFotocromatico">Fotocromático</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratTinte" name="tratamiento[]" value="Tinte" data-precio="300">
                        <label for="tratTinte">Tinte</label>
                    </div>
                    <div>
                        <input type="checkbox" id="tratOtro" name="tratamiento[]" value="Otro" data-precio="700">
                        <label for="tratOtro">Otro</label>
                    </div>


                    <input type="text" id="tratOtroTexto" name="tratOtroTexto" placeholder="Otro" data-precio="700">
                    <input type="text" id="tratPrecio" name="tratPrecio" placeholder="Precio/Costo">
                </div>
        </div>
        <!-- Columna tipo bifocal -->
        <div class="form-group" style="flex: 1;">
            <b><label style="color:#00796b">Tipo de Bifocal:</label></b>
                <div class="checkbox-group">
                    <div>
                        <input type="checkbox" id="biBlend" name="bifocal[]" value="Blend">
                        <label for="biBlend">Blend</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biFT" name="bifocal[]" value="FT">
                        <label for="biFT">FT</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biNA" name="bifocal[]" value="NA">
                        <label for="biNA">N/A</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biProgresivo" name="bifocal[]" value="Progresivo">
                        <label for="biProgresivo">Progresivo</label>
                    </div>
                    <div>
                        <input type="checkbox" id="biOtro" name="bifocal[]" value="Otro">
                        <label for="biOtro">Otro</label>
                    </div>

                    
                <div class="form-row">
                    <div class="field-container">
                        <input type="text" id="biOtroTexto" name="biOtroTexto" placeholder="Otro">
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
                <input type="text" id="obsInterno" name="obsInterno" placeholder="Observaciones Interno">
            </div> 
        </div>

        </div>


        <h3>Total:</h3>
        <hr style="border: 2px solid #00796b; margin: 5px 0;">
        <div class="form-row">
            <div class="field-container">
                    <label for="forma_pago">Forma de Pago:</label>
                    <select id="forma_pago" name="forma_pago" required>
                        <option value="">Seleccionar</option>
                        <option value="Semanal" >Semanal</option>
                        <option value="Quincenal" >Quincenal</option>
                        <option value="Mensual">Mensual</option>
                    </select>
            </div>

            <div class="form-row">
                <div class="field-container">
                    <label for="total">Total:</label>
                    <input type="number" id="total" name="total" placeholder="0.0">
                </div>
                <label for="notaTotal" style="color:red; font-style:italic;"><b>Nota:</b> Por ahora puede modificar el campo total en caso de que surga error en la suma de totales.</label>
            </div>

        </div>


            <div class="form-row">
            <button type="submit">Registrar Cliente</button>
            <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
            <button id="btnCancelar" style="background-color: red;" onclick="cancelar()">Cancelar</button>
            <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        function cancelar() {
            // Redirige a otra página sin reemplazarla en el historial
            window.location.assign('../interfaces/panel_administrador.php'); 
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

    <script>
        function sincronizarCampoTexto() {
            const asentamientoSelect = document.getElementById('asentamiento');
            const asentamientoEditable = document.getElementById('asentamientoEditable');

            // Cuando seleccionas algo del combobox, actualiza el campo de texto
            asentamientoEditable.value = asentamientoSelect.value;
        }

        function sincronizarCombobox() {
            const asentamientoSelect = document.getElementById('asentamiento');
            const asentamientoEditable = document.getElementById('asentamientoEditable');

            // Si escribes algo en el campo de texto, limpia la selección del combobox
            asentamientoSelect.value = ''; 
        }

        function sincronizarCampoTextoCobranza() {
            const asentamientoSelectCobranza = document.getElementById('asentamientoCobranza');
            const asentamientoEditableCobranza = document.getElementById('asentamientoEditableCobranza');

            // Cuando seleccionas algo del combobox, actualiza el campo de texto
            asentamientoEditableCobranza.value = asentamientoSelectCobranza.value;
        }

        function sincronizarComboboxCobranza() {
            const asentamientoSelectCobranza = document.getElementById('asentamientoCobranza');
            const asentamientoEditableCobranza = document.getElementById('asentamientoEditableCobranza');

            // Si escribes algo en el campo de texto, limpia la selección del combobox
            asentamientoSelectCobranza.value = ''; 
        }
    </script>


</body>
</html>