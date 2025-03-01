<?php include('header.php'); ?>

<?php
// Conectar a la base de datos
include '../funciones/conexion.php'; // Asegúrate de que la ruta sea correcta

// Obtener el ID del contrato desde la URL
if (isset($_GET['id_cliente'])) {
    $id_cliente = intval($_GET['id_cliente']); // Asegúrate de convertir a entero para mayor seguridad
} else {
    // Manejar el caso en que no se pasa el ID
    die("Error: ID de contrato no especificado.");
}

// Consultar los datos del contrato y los detalles relacionados, incluyendo las fotos
$query_contrato = "
    SELECT 
        clientecontrato.*,
        historialclinico.*,
        folios.*,
        armazon.*,
        usuarios.nombre_usuario AS cobrador_nombre
    FROM 
        clientecontrato
    LEFT JOIN 
        folios ON folios.id_cliente = clientecontrato.id_cliente
    LEFT JOIN
        historialclinico ON clientecontrato.id_HC = historialclinico.id_HC
    LEFT JOIN 
        armazon ON clientecontrato.id_armazon = armazon.id_armazon  
    LEFT JOIN
        usuarios ON clientecontrato.id_cobrador = usuarios.id_usuario
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


//CADENA MATERIALES
$id_clientecontrato=$contrato['id_cliente'];


$material=$contrato['material'];
$material_array = explode(',', $material);
//CADENA TRATAMIENTO
$tratamiento=$contrato['tratamiento'];
$tratamiento_array = explode(',', $tratamiento);
//CADENA BIFOCAL
$bifocal=$contrato['bifocal'];
$bifocal_array = explode(',', $bifocal);


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
</head>

<body>

    <div class="container">
        <h2>Detalle del Contrato</h2>
        <input type="hidden" id="id_cliente" value="<?php echo $id_cliente; ?>">


        <div class="form-section">
            <h3>Detalles Paciente</h3>
            <hr style="border: 2px solid #00796b; margin: 10px 0;">
        
                <div class="field-container">
                    <label for="nombrePaciente">Nombre del Paciente</label>
                    <input type="text" id="nombrePaciente" value="<?php echo $contrato['nombre_cliente']; ?>" readonly>
                </div>
                <?php if ($_SESSION['tipo_usuario'] == 'Administrador') : ?>
                <div class="field-container">
                    <label for="nombreCobrador">Cobrador asignado</label>
                    <input type="text" id="nombreCobrador" value="<?php echo $contrato['cobrador_nombre']; ?>" readonly>
                </div>
                <?php endif; ?>

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


         <br>
 

         <div class="form-section">
    <h3>Otros Detalles</h3>
    <hr style="border: 2px solid #00796b; margin: 10px 0;">
    <p><strong>Descripción Interna:</strong> <?php echo $contrato['observacion_int']; ?></p>
    <br>
    <div class="row">
        <p><strong><label for="observaciones">Observaciones Laboratorio:</label></strong></p>
        <textarea id="observaciones" name="observaciones" rows="3" cols="50"></textarea>
    </div>
    <div class="row">
    <button id="btnEnviarObservacion">Enviar Observación</button>
    </div>
</div>

<div class="form-section">
    <div class="form-row">
        <div class="liberar-contrato">
            <button id="btnLiberarContrato" onclick="liberarContrato()">Liberar Contrato</button>
        </div>
        <a href="lista_contratos_laboratorista.php" class="btn" style="background-color: red;">Volver</a>
    </div>

</div>


<script>
// Definir la variable PHP en JavaScript
const idClienteContrato = <?php echo json_encode($id_clientecontrato); ?>;
</script>
<script src="../funciones/scriptsN/enviarcomentariolab.js" defer></script>
<script src="../funciones/scriptsN/liberar_contrato.js" defer></script>
    


</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
