<?php
include('../config.php');
include('../funciones/conexion.php'); // Asegúrate de que la ruta sea correcta
date_default_timezone_set('America/Mexico_City');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['form_token']) && $_POST['form_token'] === $_SESSION['form_token']) {
        //PASO 1. OBTENER TODAS LAS VARIABLES DEL FORMULARIO
        //usuarios
        $conn->begin_transaction();
        try {
            $id_usuario =$_POST['usuario'];
            $id_optometrista =$_POST['optometrista'];
            $formaPago =$_POST['forma_pago'];
            $optometrista = $_POST['optometrista'];
            //cliente-contrato-lugar de venta
            $clientname = $_POST['client_name'];
            $clientalias = $_POST['client_alias'];
            $calle = $_POST['calle'];
            $numero = $_POST['numero'];
            $departamento = $_POST['departamento'];
            $allado = $_POST['al_lado'];
            $frentea = $_POST['frente_a'];
            $entrecalles = $_POST['entre_calles'];
            $asentamiento = !empty($_POST['asentamientoEditable']) ? $_POST['asentamientoEditable'] : $_POST['asentamiento'];
            $tipo_asent = $_POST['tipo_asent'];
            $municipio = $_POST['municipio'];
            $estado = $_POST['estado'];
            $telefono = $_POST['telefono'];
            $nombrereferencia = $_POST['nombre_referencia'];
            $telreferencia = $_POST['telefono_referencia'];
            $tipocasa = $_POST['tipo_casa'];
            $colorcasa = $_POST['color_casa'];   
            $estadoContrato = 'No Liberado';

            //lugarcobranza    
            $calleCobranza = $_POST['calleCobranza'];
            $numeroCobranza = $_POST['numeroCobranza'];
            $departamentoCobranza = $_POST['departamentoCobranza'];
            $alLadoCobranza = $_POST['alLadoCobranza'];
            $frenteACobranza = $_POST['frenteACobranza'];
            $entreCallesCobranza = $_POST['entreCallesCobranza'];
            $asentamientoCobranza = $_POST['asentamientoCobranza'];
            $asentamientoCobranza = !empty($_POST['asentamientoEditableCobranza']) ? $_POST['asentamientoEditableCobranza'] : $_POST['asentamientoCobranza'];
            $tipo_asentCobranza = $_POST['tipo_asentCobranza'];
            $municipioCobranza = $_POST['municipioCobranza'];
            $estadoCobranza = $_POST['estadoCobranza'];
            $zonaCobranza = $_POST['zonaCobranza'];
            $tipoCasaCobranza = $_POST['tipoCasaCobranza'];
            $colorCasaCobranza = $_POST['colorCasaCobranza'];
            $lugarEntrega = $_POST['lugarEntrega'];
            $cp=$_POST['cp'];

            //fotos
            $log_file = "../logs/log_registro.txt"; // Archivo donde se guardarán los mensajes
            $target_dir = "../fotosClientes/";  // Directorio donde se guardarán las imágenes
            $campos_fotos = ['id_frente', 'id_reversa', 'pagare', 'comprobante_domicilio', 'casa', 'casa2']; // Campos de imágenes en el formulario
            $rutas_fotos = []; // Arreglo para almacenar las rutas de imágenes subidas
            
            // Función para escribir mensajes en el archivo de log
            function registrarMensaje($mensaje, $log_file) {
                $timestamp = date("Y-m-d H:i:s"); // Fecha y hora actual
                file_put_contents($log_file, "[$timestamp] $mensaje\n", FILE_APPEND); // Agregar el mensaje al archivo
            }
            
            // Registrar el inicio del proceso
            registrarMensaje("Inicio del registro de imágenes", $log_file);
            
            foreach ($campos_fotos as $campo) {
                if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] == 0) {
                    // Comprobar la extensión del archivo
                    $imageFileType = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png'];
                    
                    if (!in_array($imageFileType, $allowed_types)) {
                        $mensaje = "El archivo en $campo no es un formato permitido (JPG, JPEG, PNG).";
                        registrarMensaje($mensaje, $log_file);
                        continue; // Salta al siguiente campo
                    }
                    
                    // Generar un nombre único para la imagen
                    $nombre_archivo = uniqid() . '.' . $imageFileType;
                    $target_file = $target_dir . $nombre_archivo;
            
                    // Verificar si el archivo realmente es una imagen
                    $check = getimagesize($_FILES[$campo]["tmp_name"]);
                    if ($check !== false) {
                        // Subir la imagen al servidor
                        if (move_uploaded_file($_FILES[$campo]["tmp_name"], $target_file)) {
                            // Verificar si el archivo se subió correctamente
                            if (file_exists($target_file)) {
                                $rutas_fotos[$campo] = $target_file; // Guardar la ruta subida
                                $mensaje = "La imagen $campo fue subida correctamente al servidor.";
                            } else {
                                $mensaje = "La imagen $campo no se encontró en el servidor después de subirla.";
                            }
                        } else {
                            $mensaje = "Error al subir la imagen en el campo $campo.";
                        }
                    } else {
                        $mensaje = "El archivo en $campo no es una imagen válida.";
                    }
            
                    // Registrar el mensaje
                    registrarMensaje($mensaje, $log_file);
                }
            }
            
            // Registrar el fin del proceso
            registrarMensaje("Fin del registro de imágenes", $log_file);
            
            

            //-------------------------------------------
            //historialClinico

            $edadHC = $_POST['edadHC'];
            $diagnosticoHC = $_POST['diagnosticoHC'];
            $ocupacionHC = $_POST['ocupacionHC'];
            $diabetesHC = $_POST['diabetesHC'];
            $hipertensionHC = $_POST['hipertensionHC'];
            $embarazoHC = $_POST['embarazoHC'];
            $dormirHC = $_POST['dormirHC'];
            $actividadHC = $_POST['actividadHC'];
            $problemaHC = $_POST['problemaHC'];

            if (isset($_POST['molestias_HC'])) {
                $molestiasHC = implode(',', array_map('htmlspecialchars', $_POST['molestias_HC']));
            } else {
                $molestiasHC = '';
            }

            // Manejo del campo "Otra molestia"
            if (isset($_POST['otraMolestiaTexto']) && !empty($_POST['otraMolestiaTexto'])) {
                $otraMolestiaTexto = htmlspecialchars($_POST['otraMolestiaTexto']);
                // Agregar "Otra" a las molestias
                if (strpos($molestiasHC, 'Otra') === false) {
                    $molestiasHC .= ', Otra: ' . $otraMolestiaTexto;
                }
            }

            $otraMolestiaTexto = $_POST['otraMolestiaTexto'];
            $ultimoexamenHC = $_POST['ultimoExamenHC'];

            //ARMAZON
            $armazonA = $_POST['armazonA'];
            $paquetesA = $_POST['paquetesA'];
            $esfericoAOD = $_POST['esfericoAOD'];
            $cilindroAOD = $_POST['cilindroAOD'];
            $ejeAOD = $_POST['ejeAOD'];
            $addAOD = $_POST['addAOD'];
            $altAOD = $_POST['altAOD'];
            $esfericoAOI = $_POST['esfericoAOI'];
            $cilindroAOI = $_POST['cilindroAOI'];
            $ejeAOI = $_POST['ejeAOI'];
            $addAOI = $_POST['addAOI'];
            $altAOI = $_POST['altAOI'];

            if (isset($_POST['material'])) {
                $material = implode(',', $_POST['material']);  // Convierte el array en una cadena separada por comas
            } else {
                $material = '';
            }

            $matOtroTexo = $_POST['matOtroTexto'];
            $matPrecio = $_POST['matPrecio'];

            if (isset($_POST['tratamiento'])) {
                $tratamiento = implode(',', $_POST['tratamiento']);  // Convierte el array en una cadena separada por comas
            } else {
                $tratamiento = '';
            }

            $tratOtroTexo = $_POST['tratOtroTexto'];
            $tratPrecio = $_POST['tratPrecio'];

            if (isset($_POST['bifocal'])) {
                $bifocal = implode(',', $_POST['bifocal']);  // Convierte el array en una cadena separada por comas
            } else {
                $bifocal = '';
            }

            $biOtroTexo = $_POST['biOtroTexto'];
            $biPrecio = $_POST['biPrecio'];

            //OBSERVACIONES
            $obsInterno = $_POST['obsInterno'];
            $total = $_POST['total'];

            if (isset($_POST['promo'])) {
                 $promo= implode(',', $_POST['promo']);  // Convierte el array en una cadena separada por comas
            } else {
                $promo = '';
            }

//INICIO INSERCIONES
//FOLIO----------------------
            $query = "SELECT MAX(id_folio) AS ultimo_folio FROM folios";
            $result = $conn->query($query);
            $ultimoFolio = $result->fetch_assoc();

            // Si no hay folios previos, comenzamos desde 1, de lo contrario, incrementamos el último
            if ($ultimoFolio && $ultimoFolio['ultimo_folio']) {
                $nuevoFolio = $ultimoFolio['ultimo_folio'] + 1;
            } else {
                $nuevoFolio = 1;
            }

            // Formatear el folio como 'Fo. 000000001'
            $folio = 'Fo. ' . str_pad($nuevoFolio, 9, '0', STR_PAD_LEFT);


            //Insertar en folio

            $stmt = $conn->prepare("INSERT INTO folios(folios,total_original, total, saldo_nuevo, forma_pago, promociones, fecha_creacion) VALUES(?, ?, ?, ?, ?, ?, CURDATE())");
            $stmt->bind_param("sdddss",$folio,$total,$total,$total,$formaPago, $promo);
            $stmt->execute();
            $id_folio = $conn->insert_id;
            $stmt->close();
//FIN FOLIO--------------------

//PREPARAR Y EJECUTAR CONSULTA PARA lugarcobranza--------

            $stmt = $conn->prepare("INSERT INTO lugarcobranza (id_folio, calle_cobranza, numero_cobranza, departamento_cobranza, al_lado_cobranza, 
            frente_a_cobranza, entre_calles_cobranza, asentamiento_cobranza, tipo_asent_cobranza,municipio_cobranza,estado_cobranza, zona_cobranza, tipo_casa_cobranza, color_casa_cobranza,
            lugar_entrega_cobranza,cp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isissssssssssssi",$id_folio, $calleCobranza, $numeroCobranza, $departamentoCobranza, $alLadoCobranza, $frenteACobranza,$entreCallesCobranza,
            $asentamientoCobranza,$tipo_asentCobranza,$municipioCobranza,$estadoCobranza,$zonaCobranza,$tipoCasaCobranza,$colorCasaCobranza,$lugarEntrega,$cp);
            $stmt->execute();
            $id_lugarcobranza = $conn->insert_id;  // Obtener el ID insertado
            $stmt->close();
//FIN LC----------------

//PREPARAR Y EJECUTAR CONSULTA PARA fotos--------------

            // Asignar valores predeterminados a los campos que no tienen imágenes subidas
            foreach ($campos_fotos as $campo) {
                if (!isset($rutas_fotos[$campo])) {
                    $rutas_fotos[$campo] = "";
                }
            }

            // Inserción de las rutas de las imágenes en la tabla correspondiente
            $stmt = $conn->prepare("
                INSERT INTO fotos (id_folio, ident_frente, ident_reversa, ident_pagare, ident_comprobante, ident_casa, extra_casa) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt) {
                // Vincular parámetros
                $stmt->bind_param(
                    "issssss",
                    $id_folio,
                    $rutas_fotos['id_frente'],
                    $rutas_fotos['id_reversa'],
                    $rutas_fotos['pagare'],
                    $rutas_fotos['comprobante_domicilio'],
                    $rutas_fotos['casa'],
                    $rutas_fotos['casa2']
                );

                // Ejecutar la consulta
                if ($stmt->execute()) {
                    $id_foto = $conn->insert_id; // Obtener el ID insertado
                } else {
                    echo "Error al insertar en la tabla fotos: " . $stmt->error . "<br>";
                }

                $stmt->close();
            } else {
                echo "Error al preparar la consulta: " . $conn->error . "<br>";
            }
//FIN FOTOS

//PREPARAR Y EJECUTAR CONSULTA PARA historialclinico

            $stmt = $conn->prepare("INSERT INTO historialclinico (id_folio, edad_HC, departamento_HC, ocupacion_HC, diabetes_HC, hipertension_HC, embarazo_HC, horas_HC, actividad_HC, 
            principalproblema_HC, molestias_HC, otramolestia_HC, ultimoexamen_HC) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssssisssss",$id_folio,$edadHC,$diagnosticoHC,$ocupacionHC,$diabetesHC,$hipertensionHC,$embarazoHC,$dormirHC,$actividadHC,$problemaHC,
            $molestiasHC,$otraMolestiaTexto,$ultimoexamenHC);
            $stmt->execute();
            $id_HC = $conn->insert_id;
            $stmt->close();
//FIN HC----------------------


//PREPARAR Y EJECUTAR CONSULTA PARA armazon

            $stmt = $conn->prepare("INSERT INTO armazon (id_folio, tipo_armazon, paquete_armazon, esferico_AOD, cilindro_AOD, eje_AOD, add_AOD, alt_AOD, esferico_AOI, cilindro_AOI, eje_AOI, add_AOI, alt_AOI, 
            material, matOtroTexto, matPrecio, tratamiento, tratOtroTexto, tratPrecio, bifocal, biOtroTexto, biPrecio, observacion_int) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssssssssssssdssdssds",$id_folio,$armazonA,$paquetesA,$esfericoAOD,$cilindroAOD,$ejeAOD,$addAOD,$altAOD,$esfericoAOI,$cilindroAOI,
            $ejeAOI,$addAOI,$altAOI,$material,$matOtroTexo,$matPrecio,$tratamiento,$tratOtroTexo,$tratPrecio,$bifocal,$biOtroTexo,$biPrecio,$obsInterno);
            $stmt->execute();
            $id_armazon = $conn->insert_id;
            $stmt->close();
//FIN ARMAZON-----------------------------




//PREPARAR Y EJECUTAR CONSULTA PARA clientecontrato----------

            $stmt = $conn->prepare("INSERT INTO clientecontrato(id_folio,nombre_cliente, alias_cliente, calle_cliente, numero_cliente, departamento_cliente, al_lado_cliente, frente_a_cliente, 
            entre_calles_cliente, asentamiento_cliente, tipo_asent,municipio_cliente,estado_cliente, telefono_cliente, referencia_cliente, tel_ref_cliente, tipo_casa_cliente, color_casa_cliente, id_foto, id_lugarCobranza, id_HC, id_armazon, id_usuario, id_optometrista, estado_contrato) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssisssssssssssssiiiiiis",$id_folio,$clientname,$clientalias,$calle,$numero,$departamento,$allado,$frentea,$entrecalles,$asentamiento,
            $tipo_asent,$municipio,$estado,$telefono,$nombrereferencia,$telreferencia,$tipocasa,$colorcasa,$id_foto,$id_lugarcobranza,$id_HC,$id_armazon,$id_usuario, $id_optometrista, $estadoContrato);
            $stmt->execute();
            $id_cliente = $conn->insert_id;
            $stmt->close();
//FIN CLIENTECONTRATO--------------------------

// Actualizar la tabla folios con el id_cliente
            $stmt = $conn->prepare("UPDATE folios SET id_cliente = ? WHERE id_folio = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $id_cliente, $id_folio);

                if ($stmt->execute()) {
                } else {
                    echo "Error al actualizar la tabla 'folios': " . $stmt->error . "<br>";
                }

                $stmt->close();
            } else {
                echo "Error al preparar la consulta de actualización: " . $conn->error . "<br>";
            }



//INSERTAR EN ABONOS LAS PROMOCIONES-----------------------------------
            $promociones = isset($_POST['promo']) ? $_POST['promo'] : [];
            $metodo_pago_promo =$_POST['metodo_pago_promo'];
            $tipo_abono = "Producto";

            foreach ($promociones as $promocion) {
                // Asignar monto basado en la promoción seleccionada
                $cantidad_abono = 0;
                switch ($promocion) {
                    case 'Spray':
                        $cantidad_abono = 100;
                        break;
                    case 'Gotas':
                        $cantidad_abono = 250;
                        break;
                    case 'Póliza':
                        $cantidad_abono = 250;
                        break;
                    case 'Enganche 100+100':
                        $cantidad_abono = 200; // Enganche total
                        break;
                }
            
                // Inserción en la tabla `abonos`
                $stmt = $conn->prepare("INSERT INTO abonos (id_cliente, id_folio, id_cobrador, fecha_abono, cantidad_abono, forma_pago_abono, tipo_abono) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
                $stmt->bind_param("iisiss", $id_cliente, $id_folio, $id_usuario, $cantidad_abono, $metodo_pago_promo, $promocion);
                $stmt->execute();

                // Actualizar el saldo en la tabla `folios` si la promoción es "Enganche 100+100"
                if ($promocion === 'Enganche 100+100') {
                    $stmtUpdate = $conn->prepare("
                        UPDATE folios 
                        SET 
                            total = total - ?, 
                            saldo_nuevo = saldo_nuevo - ? 
                        WHERE id_folio = ?
                    ");
                    $stmtUpdate->bind_param("ddi", $cantidad_abono, $cantidad_abono, $id_folio);
                    $stmtUpdate->execute();
                    $stmtUpdate->close();
                }
            }
//FIN ABONOS Y PROMOCIONES---------------------
            



            // Confirmar la transacción
            $conn->commit();
            unset($_SESSION['form_token']);
            // Redirección exitosa
            header("Location: ../interfaces/lista_contratos_campo.php");
            exit();
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $error_message = date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . PHP_EOL;
            $error_message .= "Archivo: " . $e->getFile() . " en la línea " . $e->getLine() . PHP_EOL;
            $error_message .= "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
            file_put_contents('errors.log', $error_message, FILE_APPEND);            
            echo "Error: " . $e->getMessage();
            $conn->rollback();
            
            // Redirección a página de error
            header("Location: pagina_error.php");
            exit();
        }
    } else {
        // El token no coincide o no se envió, posible ataque CSRF
        header("Location: pagina_errorT.php");
        exit();
    }
    $conn->close();
}
?>