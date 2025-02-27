<?php

// Ruta: funciones/registrar_usuario.php
include('../funciones/conexion.php'); // Asegúrate de que la ruta sea correcta

//var_dump($_FILES);

//1. OBTENER TODAS LAS VARIABLES DEL FORMULARIO
//2. HACER LA INSERCION EN LAS TABLAS EXTERNAS (LugarCobranza,Fotos,historialClinico,armazon)
//3. OBTENER LAS ID DE LAS TABLAS EXTERNAS PARA PODER HACER INSERCION EN LA TABLA PRINCIPAL "CLIENTECONTRATO"


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //PASO 1. OBTENER TODAS LAS VARIABLES DEL FORMULARIO
    //usuarios
    $conn->begin_transaction();
    try {
        $id_usuario =$_POST['usuario'];
        $id_optometrista =$_POST['optometrista'];
        $folio =$_POST['folio'];
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

        $target_dir = "../fotosClientes/";  // Directorio donde se guardarán las imágenes
        // Arreglo de nombres de los campos de imagen en tu formulario
        $campos_fotos = ['id_frente', 'id_reversa', 'pagare', 'comprobante_domicilio', 'casa','casa2'];
        // Crear un arreglo para almacenar las rutas de las imágenes subidas
        $rutas_fotos = [];
        foreach ($campos_fotos as $campo) {
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] == 0) {
                // Comprobar la extensión del archivo
                $imageFileType = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png'];
        
                if (!in_array($imageFileType, $allowed_types)) {
                    echo "Solo se permiten imágenes de tipo JPG, JPEG y PNG en el campo $campo.<br>";
                    continue; // Saltar a la siguiente iteración
                }
                
                $nombre_archivo = uniqid() . '.' . $imageFileType; // o usar basename para obtener el nombre original
                $target_file = $target_dir . $nombre_archivo;

                $check = getimagesize($_FILES[$campo]["tmp_name"]);
                if ($check !== false) {
                    // Subir la imagen al servidor
                    if (move_uploaded_file($_FILES[$campo]["tmp_name"], $target_file)) {
                        // Guardar la ruta de la imagen en el arreglo
                        $rutas_fotos[$campo] = $target_file;
                        echo "La imagen " . htmlspecialchars(basename($_FILES[$campo]["name"])) . " ha sido subida.<br>";
                    } else {
                        echo "Hubo un error subiendo la imagen para el campo $campo.<br>";
                    }
                } else {
                    echo "El archivo cargado en $campo no es una imagen.<br>";
                }
            } else {
                echo "No se cargó ninguna imagen en el campo $campo. Error: " . $_FILES[$campo]['error'] . "<br>";
            }
        }

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
        $addAOI = $_POST['cilindroAOI'];
        $altAOI = $_POST['cilindroAOI'];

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


    //______________________________________________________________________________________________________________________________________________
    //______________________________________________________________________________________________________________________________________________
    //______________________________________________________________________________________________________________________________________________
    //______________________________________________________________________________________________________________________________________________
    //______________________________________________________________________________________________________________________________________________
    //PREPARAR Y EJECUTAR CONSULTA PARA lugarcobranza

        $stmt = $conn->prepare("INSERT INTO lugarcobranza (calle_cobranza, numero_cobranza, departamento_cobranza, al_lado_cobranza, 
        frente_a_cobranza, entre_calles_cobranza, asentamiento_cobranza, tipo_asent_cobranza,municipio_cobranza,estado_cobranza, zona_cobranza, tipo_casa_cobranza, color_casa_cobranza,
        lugar_entrega_cobranza,cp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssssssssssi", $calleCobranza, $numeroCobranza, $departamentoCobranza, $alLadoCobranza, $frenteACobranza,$entreCallesCobranza,
        $asentamientoCobranza,$tipo_asentCobranza,$municipioCobranza,$estadoCobranza,$zonaCobranza,$tipoCasaCobranza,$colorCasaCobranza,$lugarEntrega,$cp);
        $stmt->execute();
        $id_lugarcobranza = $conn->insert_id;  // Obtener el ID insertado
        $stmt->close();

    //PREPARAR Y EJECUTAR CONSULTA PARA fotos

        if (!empty($rutas_fotos)) {
            $stmt = $conn->prepare("INSERT INTO fotos (ident_frente, ident_reversa, ident_pagare, ident_comprobante, ident_casa, extra_casa) 
                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", 
                $rutas_fotos['id_frente'], 
                $rutas_fotos['id_reversa'], 
                $rutas_fotos['pagare'], 
                $rutas_fotos['comprobante_domicilio'], 
                $rutas_fotos['casa'], 
                $rutas_fotos['casa2']);
            $stmt->execute();
            $id_foto = $conn->insert_id;  // Obtener el ID insertado
            $stmt->close();
        } else {
            echo "No se subieron fotos, no se puede realizar la inserción en la tabla fotos.<br>";
        }


    //PREPARAR Y EJECUTAR CONSULTA PARA historialclinico

        $stmt = $conn->prepare("INSERT INTO historialclinico (edad_HC, departamento_HC, ocupacion_HC, diabetes_HC, hipertension_HC, embarazo_HC, horas_HC, actividad_HC, 
        principalproblema_HC, molestias_HC, otramolestia_HC, ultimoexamen_HC) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssisssss",$edadHC,$diagnosticoHC,$ocupacionHC,$diabetesHC,$hipertensionHC,$embarazoHC,$dormirHC,$actividadHC,$problemaHC,
        $molestiasHC,$otraMolestiaTexto,$ultimoexamenHC);
        $stmt->execute();
        $id_HC = $conn->insert_id;
        $stmt->close();

    //PREPARAR Y EJECUTAR CONSULTA PARA armazon

        $stmt = $conn->prepare("INSERT INTO armazon (tipo_armazon, paquete_armazon, esferico_AOD, cilindro_AOD, eje_AOD, add_AOD, alt_AOD, esferico_AOI, cilindro_AOI, eje_AOI, add_AOI, alt_AOI, 
        material, matOtroTexto, matPrecio, tratamiento, tratOtroTexto, tratPrecio, bifocal, biOtroTexto, biPrecio, observacion_int) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddddddddddssdssdssds",$armazonA,$paquetesA,$esfericoAOD,$cilindroAOD,$ejeAOD,$addAOD,$altAOD,$esfericoAOI,$cilindroAOI,
        $ejeAOI,$addAOI,$altAOI,$material,$matOtroTexo,$matPrecio,$tratamiento,$tratOtroTexo,$tratPrecio,$bifocal,$biOtroTexo,$biPrecio,$obsInterno);
        $stmt->execute();
        $id_armazon = $conn->insert_id;
        $stmt->close();





    //PREPARAR Y EJECUTAR CONSULTA PARA clientecontrato

        $stmt = $conn->prepare("INSERT INTO clientecontrato(nombre_cliente, alias_cliente, calle_cliente, numero_cliente, departamento_cliente, al_lado_cliente, frente_a_cliente, 
        entre_calles_cliente, asentamiento_cliente, tipo_asent,municipio_cliente,estado_cliente, telefono_cliente, referencia_cliente, tel_ref_cliente, tipo_casa_cliente, color_casa_cliente, id_foto, id_lugarCobranza, id_HC, id_armazon, id_usuario, id_optometrista, estado_contrato) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisssssssssssssiiiiiis",$clientname,$clientalias,$calle,$numero,$departamento,$allado,$frentea,$entrecalles,$asentamiento,
        $tipo_asent,$municipio,$estado,$telefono,$nombrereferencia,$telreferencia,$tipocasa,$colorcasa,$id_foto,$id_lugarcobranza,$id_HC,$id_armazon,$id_usuario, $id_optometrista, $estadoContrato);
        $stmt->execute();
        $id_cliente = $conn->insert_id;
        $stmt->close();


        //Insertar en folio

        $stmt = $conn->prepare("INSERT INTO folios(id_cliente, folios, total, saldo_nuevo, forma_pago) VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param("isdds",$id_cliente,$folio,$total,$total,$formaPago);
        $stmt->execute();
        $id_folio = $conn->insert_id;
        $stmt->close();


        //INSERTAR EN ABONOS LAS PROMOCIONES
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
        



        // Confirmar la transacción
        $conn->commit();

        // Redirección exitosa
        header("Location: ../interfaces/contrato.php?msg=Registro exitoso");
        exit();

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        echo "Error: " . $e->getMessage();
        $conn->rollback();
        
        // Redirección a página de error
        header("Location: pagina_error.php");
        exit();
    }
    $conn->close();
}

?>