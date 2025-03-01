<?php
session_start(); // Iniciar la sesión
include('../funciones/conexion.php'); // Asegúrate de que la ruta sea correcta
date_default_timezone_set('America/Mexico_City');


if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $conn->begin_transaction();
        try {
            $id_usuario =$_POST['usuario'];
            $id_cliente =$_POST['id_cliente'];
            $id_folio = $_POST['id_folio'];
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
            $campos_fotos = ['ident_frente', 'ident_reversa', 'ident_pagare', 'ident_comprobante', 'ident_casa', 'extra_casa']; // Campos de imágenes en el formulario
            // Función para registrar mensajes en el archivo de log
            function registrarMensaje($mensaje, $log_file) {
                $timestamp = date("Y-m-d H:i:s"); // Fecha y hora actual
                file_put_contents($log_file, "[$timestamp] $mensaje\n", FILE_APPEND); // Agregar el mensaje al archivo
            }

            registrarMensaje("Inicio del registro de imágenes", $log_file);

            $stmt = $conn->prepare("SELECT * FROM fotos WHERE id_folio = ?");
            $stmt->bind_param("i", $id_folio);
            $stmt->execute();
            $result = $stmt->get_result();
            $rutas_existentes = $result->fetch_assoc();
            
            $rutas_fotos = []; // Arreglo para almacenar las rutas de imágenes subidas
            
            foreach ($campos_fotos as $campo) {
                if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] == 0) {
                    // Si ya existe una imagen para este campo, eliminarla
                    if (!empty($rutas_existentes[$campo]) && file_exists($rutas_existentes[$campo])) {
                        if (unlink($rutas_existentes[$campo])) {
                            registrarMensaje("Imagen anterior eliminada para el campo $campo", $log_file);
                        } else {
                            registrarMensaje("Error al intentar eliminar la imagen anterior de $campo", $log_file);
                        }
                    }
                    
                    // Procesar nueva imagen
                    $imageFileType = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png'];
            
                    if (!in_array($imageFileType, $allowed_types)) {
                        registrarMensaje("Formato no permitido en $campo", $log_file);
                        $rutas_fotos[$campo] = $rutas_existentes[$campo];
                        continue;
                    }
            
                    $nombre_archivo = uniqid() . '.' . $imageFileType;
                    $target_file = $target_dir . $nombre_archivo;
            
                    if (move_uploaded_file($_FILES[$campo]["tmp_name"], $target_file)) {
                        $rutas_fotos[$campo] = $target_file;
                        registrarMensaje("Imagen actualizada en $campo", $log_file);
                    } else {
                        registrarMensaje("Error al subir $campo", $log_file);
                        $rutas_fotos[$campo] = $rutas_existentes[$campo];
                    }
                } else {
                    // Mantener la ruta existente
                    $rutas_fotos[$campo] = $rutas_existentes[$campo];
                }
            }
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
            $query = "SELECT * FROM folios WHERE id_folio = ?";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $stmt->bind_param("i", $id_folio); // Asegúrate de que $id_folio tenga el ID correcto
                $stmt->execute();
                $result = $stmt->get_result();
                $ultimoFolio = $result->fetch_assoc();
                $stmt->close();

                // Ahora actualizamos los campos
                $stmt_update = $conn->prepare("
                    UPDATE folios 
                    SET total_original = ?, total = ?, saldo_nuevo = ?, forma_pago = ?, promociones = ?
                    WHERE id_folio = ?
                ");

                if ($stmt_update) {
                    $stmt_update->bind_param("dddssi", $total, $total, $total, $formaPago, $promo, $id_folio);

                    if ($stmt_update->execute()) {
                    } else {
                        echo "Error al actualizar los campos: " . $stmt_update->error . "<br>";
                    }
                    $stmt_update->close();
                } else {
                    echo "Error al preparar la consulta UPDATE: " . $conn->error . "<br>";
                }
            } else {
                echo "Error al preparar la consulta SELECT: " . $conn->error . "<br>";
            }
//FIN FOLIO--------------------

//PREPARAR Y EJECUTAR CONSULTA PARA lugarcobranza--------

            $stmt = $conn->prepare("
                UPDATE lugarcobranza 
                SET calle_cobranza = ?, numero_cobranza = ?, departamento_cobranza = ?, al_lado_cobranza = ?, frente_a_cobranza = ?, 
                    entre_calles_cobranza = ?, asentamiento_cobranza = ?, tipo_asent_cobranza = ?, municipio_cobranza = ?, estado_cobranza = ?, 
                    zona_cobranza = ?, tipo_casa_cobranza = ?, color_casa_cobranza = ?, lugar_entrega_cobranza = ?, cp = ?
                WHERE id_folio = ?
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "sisssssssssssssi",
                    $calleCobranza,
                    $numeroCobranza,
                    $departamentoCobranza,
                    $alLadoCobranza,
                    $frenteACobranza,
                    $entreCallesCobranza,
                    $asentamientoCobranza,
                    $tipo_asentCobranza,
                    $municipioCobranza,
                    $estadoCobranza,
                    $zonaCobranza,
                    $tipoCasaCobranza,
                    $colorCasaCobranza,
                    $lugarEntrega,
                    $cp,
                    $id_folio // ID que permanece igual
                );

                if ($stmt->execute()) {

                } else {
                    echo "Error al actualizar los campos: " . $stmt->error . "<br>";
                }

                $stmt->close();
            } else {
                echo "Error al preparar la consulta UPDATE: " . $conn->error . "<br>";
            }

//FIN LC----------------

// fotos--------------
            $set_clause = [];
            $params = [];

            foreach ($campos_fotos as $campo) {
                if (isset($rutas_fotos[$campo])) {
                    $set_clause[] = "$campo = ?";
                    $params[] = $rutas_fotos[$campo];
                }
            }

            if (count($set_clause) > 0) {
                // Construir la consulta dinámica con los campos actualizados
                $sql = "UPDATE fotos SET " . implode(", ", $set_clause) . " WHERE id_folio = ?";
                $stmt = $conn->prepare($sql);

                // Añadir el id_folio al final de los parámetros
                $params[] = $id_folio;

                // Unir los parámetros y ejecutar la consulta
                $stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

                // Ejecutar la consulta de actualización
                if ($stmt->execute()) {
                    registrarMensaje("Edición de imágenes completada", $log_file);
                } else {
                    registrarMensaje("Error al actualizar la base de datos", $log_file);
                }
            } else {
                registrarMensaje("No se han realizado cambios en las imágenes. No se actualizaron registros.", $log_file);
            }

            // Registrar el fin del proceso
            registrarMensaje("Fin del registro de imágenes", $log_file);
//FIN FOTOS

//PREPARAR Y EJECUTAR CONSULTA PARA historialclinico

            $stmt = $conn->prepare("
                UPDATE historialclinico 
                SET edad_HC = ?, departamento_HC = ?, ocupacion_HC = ?, diabetes_HC = ?, hipertension_HC = ?, 
                    embarazo_HC = ?, horas_HC = ?, actividad_HC = ?, principalproblema_HC = ?, molestias_HC = ?, 
                    otramolestia_HC = ?, ultimoexamen_HC = ?
                WHERE id_folio = ?
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "isssssisssssi", 
                    $edadHC, 
                    $diagnosticoHC, 
                    $ocupacionHC, 
                    $diabetesHC, 
                    $hipertensionHC, 
                    $embarazoHC, 
                    $dormirHC, 
                    $actividadHC, 
                    $problemaHC, 
                    $molestiasHC, 
                    $otraMolestiaTexto, 
                    $ultimoexamenHC, 
                    $id_folio // ID que permanece igual
                );

                if ($stmt->execute()) {

                } else {
                    echo "Error al actualizar el historial clínico: " . $stmt->error . "<br>";
                }

                $stmt->close();
            } else {
                echo "Error al preparar la consulta UPDATE: " . $conn->error . "<br>";
            }

//FIN HC----------------------


//PREPARAR Y EJECUTAR CONSULTA PARA armazon

            $stmt = $conn->prepare("
                UPDATE armazon 
                SET tipo_armazon = ?, paquete_armazon = ?, esferico_AOD = ?, cilindro_AOD = ?, eje_AOD = ?, add_AOD = ?, alt_AOD = ?, 
                    esferico_AOI = ?, cilindro_AOI = ?, eje_AOI = ?, add_AOI = ?, alt_AOI = ?, material = ?, matOtroTexto = ?, 
                    matPrecio = ?, tratamiento = ?, tratOtroTexto = ?, tratPrecio = ?, bifocal = ?, biOtroTexto = ?, biPrecio = ?, 
                    observacion_int = ?
                WHERE id_folio = ?
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "ssssssssssssssdssdssdsi",
                    $armazonA,
                    $paquetesA,
                    $esfericoAOD,
                    $cilindroAOD,
                    $ejeAOD,
                    $addAOD,
                    $altAOD,
                    $esfericoAOI,
                    $cilindroAOI,
                    $ejeAOI,
                    $addAOI,
                    $altAOI,
                    $material,
                    $matOtroTexo,
                    $matPrecio,
                    $tratamiento,
                    $tratOtroTexo,
                    $tratPrecio,
                    $bifocal,
                    $biOtroTexo,
                    $biPrecio,
                    $obsInterno,
                    $id_folio // ID utilizado para identificar el registro
                );

                if ($stmt->execute()) {

                } else {
                    echo "Error al actualizar el armazón: " . $stmt->error . "<br>";
                }

                $stmt->close();
            } else {
                echo "Error al preparar la consulta UPDATE: " . $conn->error . "<br>";
            }

//FIN ARMAZON-----------------------------




//PREPARAR Y EJECUTAR CONSULTA PARA clientecontrato----------

            $stmt = $conn->prepare("
                UPDATE clientecontrato 
                SET nombre_cliente = ?, alias_cliente = ?, calle_cliente = ?, numero_cliente = ?, departamento_cliente = ?, al_lado_cliente = ?, 
                    frente_a_cliente = ?, entre_calles_cliente = ?, asentamiento_cliente = ?, tipo_asent = ?, municipio_cliente = ?, estado_cliente = ?, 
                    telefono_cliente = ?, referencia_cliente = ?, tel_ref_cliente = ?, tipo_casa_cliente = ?, color_casa_cliente = ?, id_optometrista = ? 
                WHERE id_folio = ?
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "sssssssssssssssssii",
                    $clientname,
                    $clientalias,
                    $calle,
                    $numero,
                    $departamento,
                    $allado,
                    $frentea,
                    $entrecalles,
                    $asentamiento,
                    $tipo_asent,
                    $municipio,
                    $estado,
                    $telefono,
                    $nombrereferencia,
                    $telreferencia,
                    $tipocasa,
                    $colorcasa,
                    $id_optometrista,
                    $id_folio // ID utilizado para identificar el registro
                );

                if ($stmt->execute()) {

                } else {
                    echo "Error al actualizar el cliente: " . $stmt->error . "<br>";
                }

                $stmt->close();
            } else {
                echo "Error al preparar la consulta UPDATE: " . $conn->error . "<br>";
            }

//FIN CLIENTECONTRATO--------------------------

//INSERTAR EN ABONOS LAS PROMOCIONES-----------------------------------
// Obtener las promociones seleccionadas actualmente desde el formulario
$promocionesSeleccionadas = isset($_POST['promo']) ? $_POST['promo'] : [];
$metodo_pago_promo = $_POST['metodo_pago_promo'];
$tipo_abono = "Producto";

// Obtener las promociones existentes en la base de datos para este contrato
$promocionesActuales = [];
$stmt = $conn->prepare("SELECT tipo_abono FROM abonos WHERE id_folio = ? AND tipo_abono IN ('Spray', 'Gotas', 'Póliza', 'Enganche 100+100')");
$stmt->bind_param("i", $id_folio);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $promocionesActuales[] = $row['tipo_abono'];
}
$stmt->close();

// Determinar promociones nuevas y promociones eliminadas
$promocionesNuevas = array_diff($promocionesSeleccionadas, $promocionesActuales);
$promocionesEliminadas = array_diff($promocionesActuales, $promocionesSeleccionadas);

// Manejar promociones nuevas
foreach ($promocionesNuevas as $promocion) {
    // Determinar el monto basado en la promoción seleccionada
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

    // Insertar la promoción en la tabla `abonos`
        $stmt = $conn->prepare("INSERT INTO abonos (id_cliente, id_folio, id_cobrador, fecha_abono, cantidad_abono, forma_pago_abono, tipo_abono) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
        $stmt->bind_param("iisiss", $id_cliente, $id_folio, $id_usuario, $cantidad_abono, $metodo_pago_promo, $promocion);
        $stmt->execute();
        $stmt->close();

        // Si es "Enganche 100+100", actualizar la tabla `folios`
        if ($promocion === 'Enganche 100+100') {
            $stmtUpdate = $conn->prepare("
                UPDATE folios 
                SET 
                    total_original = total_original + ? 
                WHERE id_folio = ?
            ");
            $stmtUpdate->bind_param("di", $cantidad_abono, $id_folio);
            $stmtUpdate->execute();
            $stmtUpdate->close();
        }
    }

        // Manejar promociones eliminadas
        foreach ($promocionesEliminadas as $promocion) {
            // Eliminar la promoción de la tabla `abonos`
            $stmt = $conn->prepare("DELETE FROM abonos WHERE id_folio = ? AND tipo_abono = ?");
            $stmt->bind_param("is", $id_folio, $promocion);
            $stmt->execute();
            $stmt->close();
        }


            $stmt = $conn->prepare("SELECT SUM(cantidad_abono) AS suma_abonos FROM abonos WHERE id_folio = ? AND tipo_abono = 'Abono'");
            $stmt->bind_param("i", $id_folio);
            $stmt->execute();
            $result = $stmt->get_result();
            $suma_abonos = 0;

            if ($row = $result->fetch_assoc()) {
                $suma_abonos = $row['suma_abonos'] ?? 0; // Asignar 0 si el resultado es null
            }
            $stmt->close();

            // Si hay abonos, actualizar el saldo en la tabla `folios`
            if ($suma_abonos > 0) {
                $stmtUpdateSaldo = $conn->prepare("UPDATE folios SET saldo_nuevo = saldo_nuevo - ? WHERE id_folio = ?");
                $stmtUpdateSaldo->bind_param("di", $suma_abonos, $id_folio);
                $stmtUpdateSaldo->execute();
                $stmtUpdateSaldo->close();
            }
    //FIN ABONOS Y PROMOCIONES---------------------

            // Confirmar la transacción
            $conn->commit();
            // Redirección exitosa
            header("Location: ../interfaces/lista_contratos_campo.php?msg=Registro exitoso");
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
    $conn->close();
}
?>