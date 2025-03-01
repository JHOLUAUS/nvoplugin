<?php include('header.php'); ?>
<?php
include('../funciones/conexion.php');

// Verificar si el usuario ha iniciado sesión y si es cobrador o administrador
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'Cobrador' && $_SESSION['tipo_usuario'] != 'Administrador')) {
    header('Location: login.php');
    exit();
}

// Obtener el nombre del cobrador desde la sesión
global $id_cobrador;
$id_cobrador = $_SESSION['id_usuario'];

global $nombreCobrador;
$nombreCobrador = $_SESSION['nombre_usuario'];

global $tipoUsuario;
$tipoUsuario = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Contratos</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
    <!-- Incluir la librería de SheetJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
</head>
<body>

<section class="contract-table-wrapper">
    <div class="table-container">
        <div class="form-row">
            <select id="download-type">
                <option value="all">Descargar todos</option>
                <option value="liquidados">Descargar liquidados</option>
                <option value="no_liquidados">Descargar no liquidados</option>
                <option value="manual">Seleccionar manualmente</option>
            </select>
            <div id="manual-selection" class="hidden">
                <h6>¡Selecciona los contratos!</h6>
                <form id="contract-selection-form">
                    <div id="contracts-list"></div>
                </form>
            </div>
            <button id="generate-excel">Generar Excel</button>
        </div>
        <div class="search-container">
            <form method="GET" action="">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Buscar contratos..." 
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <table class="contract-table">
            <thead>
                <tr>
                    <th>Sel</th> <!-- Nueva columna para selección manual -->
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Alias</th>
                    <th>Dirección</th>
                    <th>Liberado/No Liberado</th>
                    <th>Estado de Liquidación</th>
                    <th>Cobrador Asignado</th>
                    <th>Ultimo Abono</th>
                    <th>Total</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody id="contracts-list-body">
                <?php
                // Consulta para obtener todos los contratos con la dirección de cobranza
                $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

                $sql = "
                SELECT 
                    clientecontrato.id_cliente,
                    clientecontrato.nombre_cliente,
                    clientecontrato.alias_cliente,
                    clientecontrato.estado_contrato,
                    clientecontrato.id_cobrador,
                    clientecontrato.id_folio,
                    lugarcobranza.calle_cobranza,
                    lugarcobranza.numero_cobranza,
                    lugarcobranza.departamento_cobranza,
                    lugarcobranza.asentamiento_cobranza,
                    lugarcobranza.municipio_cobranza,
                    lugarcobranza.estado_cobranza,
                    usuarios.nombre_usuario AS cobrador,
                    folios.estado_liquidacion,
                    folios.folios,
                    folios.saldo_nuevo,
                    DATE(MAX(abonos.fecha_abono)) AS fecha_abono
                FROM 
                    clientecontrato
                LEFT JOIN 
                    lugarcobranza ON clientecontrato.id_lugarCobranza = lugarcobranza.id_lugarcobranza
                LEFT JOIN 
                    usuarios ON clientecontrato.id_cobrador = usuarios.id_usuario
                LEFT JOIN 
                    folios ON clientecontrato.id_cliente = folios.id_cliente
                LEFT JOIN 
                    abonos ON clientecontrato.id_cliente = abonos.id_cliente 
                             AND clientecontrato.id_folio = abonos.id_folio 
                             AND abonos.tipo_abono = 'Abono'
                WHERE 
                    clientecontrato.nombre_cliente LIKE ? OR
                    clientecontrato.alias_cliente LIKE ? OR
                    clientecontrato.estado_contrato LIKE ? OR
                    lugarcobranza.calle_cobranza LIKE ? OR
                    lugarcobranza.municipio_cobranza LIKE ? OR
                    lugarcobranza.estado_cobranza LIKE ? OR
                    usuarios.nombre_usuario LIKE ? OR
                    folios.estado_liquidacion LIKE ? OR
                    folios.folios LIKE ?
                GROUP BY 
                    clientecontrato.id_cliente,
                    clientecontrato.nombre_cliente,
                    clientecontrato.alias_cliente,
                    clientecontrato.estado_contrato,
                    clientecontrato.id_cobrador,
                    clientecontrato.id_folio,
                    lugarcobranza.calle_cobranza,
                    lugarcobranza.numero_cobranza,
                    lugarcobranza.departamento_cobranza,
                    lugarcobranza.asentamiento_cobranza,
                    lugarcobranza.municipio_cobranza,
                    lugarcobranza.estado_cobranza,
                    usuarios.nombre_usuario,
                    folios.estado_liquidacion,
                    folios.folios,
                    folios.saldo_nuevo
                ORDER BY 
                    folios.estado_liquidacion ASC, folios.folios ASC;
            ";
                
                // Prepara la consulta
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Error en la preparación de la consulta: " . $conn->error);
                }
                
                // Vincula los parámetros
                $stmt->bind_param(
                    "sssssssss",
                    $search,
                    $search,
                    $search,
                    $search,
                    $search,
                    $search,
                    $search,
                    $search,
                    $search
                );
                
                $stmt->execute();
                $result = $stmt->get_result();

                // Mostrar los contratos
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <tr class="contract-row" data-id="<?php echo $row['id_cliente']; ?>">

                        <td>
                            <!-- Checkbox para seleccionar el contrato -->
                            <input type="checkbox" class="contract-checkbox" />
                        </td>
                        <td data-label="Folio">
                            <?php echo htmlspecialchars($row['folios']); ?>
                        </td>
                        <td data-label="Cliente">
                            <a href="informacion_de_contrato.php?id_cliente=<?php echo $row['id_cliente']; ?>&nombre_cobrador=<?php echo urlencode($nombreCobrador); ?> &id_cobrador=<?php echo urlencode($id_cobrador);?> &tipo_usuario=<?php echo urlencode($tipoUsuario); ?> &folioContrato=<?php echo $row['id_folio'];?>">
                                <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                            </a>
                        </td>
                        <td data-label="Alias">
                            <?php echo htmlspecialchars($row['alias_cliente']); ?>
                        </td>
                        <td data-label="Dirección">
                            <?php echo htmlspecialchars($row['calle_cobranza']) . ' No. ' . htmlspecialchars($row['numero_cobranza']) . ', ' . htmlspecialchars($row['departamento_cobranza']) . ', ' . htmlspecialchars($row['asentamiento_cobranza']) . ', ' . htmlspecialchars($row['municipio_cobranza']) . ', ' . htmlspecialchars($row['estado_cobranza']); ?>
                        </td>
                        <td data-label="Liberado/No Liberado">
                            <?php echo htmlspecialchars($row['estado_contrato']); ?>
                        </td>
                        <td data-label="¿Liquidado?">
                            <?php echo htmlspecialchars($row['estado_liquidacion']); ?>
                        </td>
                        <td data-label="Cobrador Asignado">
                            <?php echo htmlspecialchars($row['cobrador']); ?>
                        </td>
                        <td data-label="Ultimo Abono">
                            <?php echo htmlspecialchars($row['fecha_abono'] ?: 'Sin abonos'); ?>
                        </td>
                        <td data-label="Total">
                            <?php echo htmlspecialchars($row['saldo_nuevo']); ?>
                        </td>
                        <td>
                            <div class="options-container">
                                <button class="options-btn">⋮</button>
                                <div class="options-menu">
                                    <!-- <a href="enviar_a_lista_negra.php?id_cliente=<?php echo $row['id_cliente']; ?>">Enviar a lista negra</a> -->
                                    <a href="../funciones/eliminar_contrato.php?id_folio=<?php echo $row['id_folio']; ?>">Eliminar</a>
                                </div>
                            </div>
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
    </section>


    <script>
document.getElementById('generate-excel').addEventListener('click', function() {
    const downloadType = document.getElementById('download-type').value;
    const contracts = document.querySelectorAll('.contract-table tbody tr');
    const selectedContracts = [];

    // Limpiar selección manual
    document.getElementById('manual-selection').classList.add('hidden');
    
    // Filtrar según la opción seleccionada
    if (downloadType === 'all') {
        // Seleccionar todos los contratos
        contracts.forEach(contract => selectedContracts.push(contract));
    } else if (downloadType === 'liquidados') {
        // Seleccionar solo los contratos liquidados
        contracts.forEach(contract => {
            const estadoLiquidacion = contract.querySelector('td[data-label="¿Liquidado?"]').textContent.trim();
            if (estadoLiquidacion === 'Liquidado') {
                selectedContracts.push(contract);
            }
        });
    } else if (downloadType === 'no_liquidados') {
        // Seleccionar solo los contratos no liquidados
        contracts.forEach(contract => {
            const estadoLiquidacion = contract.querySelector('td[data-label="¿Liquidado?"]').textContent.trim();
            if (estadoLiquidacion === 'No liquidados' || estadoLiquidacion === '') {
                selectedContracts.push(contract);
            }
        });
    } else if (downloadType === 'manual') {
        // Mostrar la opción de selección manual si es necesario
        document.getElementById('manual-selection').classList.remove('hidden');
        const selectedCheckboxes = document.querySelectorAll('.contract-checkbox:checked');
            selectedCheckboxes.forEach(checkbox => {
                const row = checkbox.closest('tr');
                selectedContracts.push(row);
            });
        
    }

    // Verificar si se han seleccionado contratos para descargar
    if (selectedContracts.length > 0) {
        generateExcel(selectedContracts);
    } else {
        alert('Selecciona al menos un contrato para descargar');
    }
});

function generateExcel(contracts) {
    const headers = ["Folio", "Cliente", "Alias", "Dirección", "Liberado/No Liberado", "Estado de Liquidación","Cobrador Asignado", "Ultimo Abono", "Total"];
    let excelData = [headers];

    contracts.forEach(contract => {
        const rowData = [
            contract.querySelector('td[data-label="Folio"]').textContent.trim(),
            contract.querySelector('td[data-label="Cliente"]').textContent.trim(),
            contract.querySelector('td[data-label="Alias"]').textContent.trim(),
            contract.querySelector('td[data-label="Dirección"]').textContent.trim(),
            contract.querySelector('td[data-label="Liberado/No Liberado"]').textContent.trim(),
            contract.querySelector('td[data-label="¿Liquidado?"]').textContent.trim(),
            contract.querySelector('td[data-label="Cobrador Asignado"]').textContent.trim(),
            contract.querySelector('td[data-label="Ultimo Abono"]').textContent.trim(),
            contract.querySelector('td[data-label="Total"]').textContent.trim()

        ];
        excelData.push(rowData);
    });

    // Crear la hoja de trabajo
    const ws = XLSX.utils.aoa_to_sheet(excelData);

    // Aplicar estilo a las celdas
    for (let row = 0; row < excelData.length; row++) {
        for (let col = 0; col < headers.length; col++) {
            const cell = ws[XLSX.utils.encode_cell({ r: row, c: col })];
            if (!cell.s) cell.s = {}; // Asegurarse de que el objeto de estilo exista
            cell.s.font = { sz: 7 }; // Tamaño de letra 7 puntos
            if (row === 0) {
                // Estilo especial para el encabezado
                cell.s.font.bold = true; // Negrita
                cell.s.font.color = { rgb: "FFFFFF" }; // Texto blanco
                cell.s.fill = { fgColor: { rgb: "4F81BD" } }; // Fondo azul
                cell.s.alignment = { horizontal: "center", vertical: "center" }; // Alineación centrada
                cell.s.border = { // Bordes
                    top: { style: "thin", color: { rgb: "000000" } },
                    left: { style: "thin", color: { rgb: "000000" } },
                    bottom: { style: "thin", color: { rgb: "000000" } },
                    right: { style: "thin", color: { rgb: "000000" } }
                };
            }
        }
    }

    // Ajustar el ancho de las columnas
    const colWidth = excelData[0].map((col, index) => {
        const maxLength = Math.max(...excelData.map(row => (row[index] ? row[index].length : 0)));
        return { wpx: maxLength * 10 };
    });
    ws["!cols"] = colWidth;

    // Crear el libro de trabajo y agregar la hoja
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Contratos");

    // Descargar el archivo
    XLSX.writeFile(wb, "contratos.xlsx");
}

</script>

<script>
    // Obtener todos los botones de opciones
    const optionButtons = document.querySelectorAll('.options-btn');

    optionButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            // Obtener el contenedor de opciones asociado
            const optionsContainer = button.parentElement;

            // Alternar la visibilidad del menú
            optionsContainer.classList.toggle('show-menu');

            // Cerrar otros menús si ya están abiertos
            document.querySelectorAll('.options-container').forEach(container => {
                if (container !== optionsContainer) {
                    container.classList.remove('show-menu');
                }
            });

            // Evitar que el clic en el botón cierre inmediatamente el menú
            event.stopPropagation();
        });
    });

    // Cerrar los menús si se hace clic fuera de ellos
    document.addEventListener('click', () => {
        document.querySelectorAll('.options-container').forEach(container => {
            container.classList.remove('show-menu');
        });
    });
</script>

</body>
</html>

/// ACA CON LA FUNCION ANTERIOR DE ESTILOS A LA ULTIMA:

<script>
document.getElementById('generate-excel').addEventListener('click', async function() {
    const downloadType = document.getElementById('download-type').value;
    const contracts = document.querySelectorAll('.contract-table tbody tr');
    const selectedContracts = [];

    document.getElementById('manual-selection').classList.add('hidden');

    if (downloadType === 'all') {
        contracts.forEach(contract => selectedContracts.push(contract));
    } else if (downloadType === 'liquidados') {
        contracts.forEach(contract => {
            const estadoLiquidacion = contract.querySelector('td[data-label="¿Liquidado?"]').textContent.trim();
            if (estadoLiquidacion === 'Liquidado') {
                selectedContracts.push(contract);
            }
        });
    } else if (downloadType === 'no_liquidados') {
        contracts.forEach(contract => {
            const estadoLiquidacion = contract.querySelector('td[data-label="¿Liquidado?"]').textContent.trim();
            if (estadoLiquidacion === 'No liquidados' || estadoLiquidacion === '') {
                selectedContracts.push(contract);
            }
        });
    } else if (downloadType === 'manual') {
        document.getElementById('manual-selection').classList.remove('hidden');
        const selectedCheckboxes = document.querySelectorAll('.contract-checkbox:checked');
        selectedCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            selectedContracts.push(row);
        });
    }

    if (selectedContracts.length > 0) {
        await generateExcelFromTemplate(selectedContracts);
    } else {
        alert('Selecciona al menos un contrato para descargar');
    }
});

async function generateExcelFromTemplate(contracts) {
    const response = await fetch('template.xlsx');
    const arrayBuffer = await response.arrayBuffer();
    const workbook = XLSX.read(arrayBuffer, { type: 'array' });
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];

    let rowIndex = 2;
    contracts.forEach(contract => {
        const rowData = [
            contract.querySelector('td[data-label="Folio"]').textContent.trim(),
            contract.querySelector('td[data-label="Cliente"]').textContent.trim(),
            contract.querySelector('td[data-label="Alias"]').textContent.trim(),
            contract.querySelector('td[data-label="Calle"]').textContent.trim(),
            contract.querySelector('td[data-label="Numero"]').textContent.trim(),
            contract.querySelector('td[data-label="Departamento"]').textContent.trim(),
            contract.querySelector('td[data-label="Asentamiento"]').textContent.trim(),
            contract.querySelector('td[data-label="Municipio"]').textContent.trim(),
            contract.querySelector('td[data-label="Estado"]').textContent.trim(),
            contract.querySelector('td[data-label="Liberado/No Liberado"]').textContent.trim(),
            contract.querySelector('td[data-label="¿Liquidado?"]').textContent.trim(),
            contract.querySelector('td[data-label="Cobrador Asignado"]').textContent.trim(),
            contract.querySelector('td[data-label="Ultimo Abono"]').textContent.trim(),
            contract.querySelector('td[data-label="Total"]').textContent.trim()
        ];
        XLSX.utils.sheet_add_aoa(worksheet, [rowData], { origin: `A${rowIndex}` });
        rowIndex++;
    });

    const today = new Date().toISOString().split('T')[0];
    const fileName = `contratos_${today}.xlsx`;
    XLSX.writeFile(workbook, fileName);
}
</script>





/*  SCRIPT PARA EL MODAL DE ABONO

  <!-- SCRIPT Y MODAL PARA ABONO -------------------------------------------------------------------------------------------------------------------SCRIPT Y MODAL PARA ABONO----- -->
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
    var nombreCobrador = <?php echo json_encode($nombredelCobrador);?>;
    var idCobrador = <?php echo json_encode($id_global_cobrador);?>;
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

                    // Generar e imprimir el ticket
                    var ticketWindow = window.open(
                        '../funciones/generar_ticket.php?idGlobalCliente=' + idGlobalCliente +
                        '&folio_encode=' + folioEncode +
                        '&nombre_cliente=' + nombreCliente +
                        '&cantidad_abono=' + cantidadAbono +
                        '&metodo_pago=' + metodoPago +
                        '&nuevo_saldo=' + nuevoSaldo +
                        '&aboprodfolio='+ aboprodfolio +
                        '&nombre_cobrador=' + nombreCobrador +
                        '&saldo_anterior=' + saldoAnterior,
                        '_blank'
                    );

                    ticketWindow.onload = function () {
                        ticketWindow.print();
                        ticketWindow.onafterprint = function () {
                            // Reiniciar campos del formulario
                            document.getElementById('cantidadAbono').value = '';
                            document.getElementById('metodoPago').value = 'Efectivo'; // Valor por defecto
                            liquidarCheckbox.checked = false;

                            // Refrescar la página después de cerrar el ticket
                            location.reload();
                        };
                    };
                } else if (response.status === "error") {
                    // Mostrar mensaje de error
                    alert(response.message);
                }
            } else {
                // Error de red o servidor
                alert("Ocurrió un error al registrar el abono. Inténtalo más tarde.");
            }

            // Rehabilitar el botón después de completar la solicitud
            btnImprimirAbono.disabled = false;
            btnImprimirAbono.style.backgroundColor = ""; // Vuelve al color por defecto
            btnImprimirAbono.style.cursor = "";
        }
    };

    xhr.send("idGlobalCliente=" + idGlobalCliente + "&aboprodfolio=" + aboprodfolio + "&cantidad_abono=" + cantidadAbono + "&metodo_pago=" + metodoPago + "&liquidar=" + liquidar + "&nuevo_saldo=" + nuevoSaldo + "&id_cobrador=" + idCobrador);
};
});
</script>


*/


//ACA EL ULTIMO GENERAR_TICKET_MOVIL QUE HICE:
<?php
require '../funciones/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('America/Mexico_City');

// Obtener los datos enviados a través de la URL
$idGlobalCliente = $_GET['idGlobalCliente'] ?? '';
$cantidad_abono = $_GET['cantidad_abono'] ?? 0;
$metodo_pago = $_GET['metodo_pago'] ?? '';
$nuevo_saldo = $_GET['nuevo_saldo'] ?? 0;
$nombre_cliente = $_GET['nombre_cliente'] ?? '';
$nombre_cobrador = $_GET['nombre_cobrador'] ?? '';
$saldo_anterior = $_GET['saldo_anterior'] ?? 0;
$folio_encode = $_GET['folio_encode'] ?? '';

$fecha_actual = date('d-m-Y');
$hora_actual = date('g:i A');

// Configurar Dompdf
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

$html = "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .ticket { width: 250px; padding: 8px; border: 1px dashed black; }
        img { width: 100px; margin-bottom: 5px; }
        h2 { font-size: 14px; text-transform: uppercase; }
        p { font-size: 12px; margin: 2px 0; }
        a { text-align: center; margin-top: 20px; }
        .details { text-align: left; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class='ticket'>
        <img src='../imagenes/LogooNuevaVista.png' alt='Logo Óptica'>
        <h2>Óptica Nueva Vista</h2>
        <p><strong>Cliente:</strong> $nombre_cliente</p>
        <p><strong>Folio:</strong> $folio_encode</p>
        <p><strong>Cantidad Abonada:</strong> $$cantidad_abono</p>
        <p><strong>Método de Pago:</strong> $metodo_pago</p>
        <p><strong>Saldo Anterior:</strong> $$saldo_anterior</p>
        <p><strong>Nuevo Saldo:</strong> $$nuevo_saldo</p>
        <p><strong>Fecha:</strong> $fecha_actual</p>
        <p><strong>Hora:</strong> $hora_actual</p>
        <p><strong>Cobrador:</strong> $nombre_cobrador</p>
        <p class='total'>Gracias por su pago</p>
        <p>Conserve este ticket para cualquier aclaración</p>
    </div>
</body>
</html>
";

$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 250, 500]);
$dompdf->render();

$pdfOutput = $dompdf->output();
$filePath = 'tickets/ticket_' . time() . '.pdf';
file_put_contents($filePath, $pdfOutput);

// Asegúrate de que la ruta del archivo sea accesible públicamente
$fileUrl = 'http://192.168.1.2/funciones/tickets/ticket_' . time() . '.pdf';

// Mostrar el botón para imprimir con Capacitor
echo "<a href='#' onclick='return sendToPrinter(\"$fileUrl\");' class='print-file' style='display: block; width: 200px; margin: 50px auto; text-align: center; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; font-size: 16px; border-radius: 5px;'>Imprimir Ticket</a>";
echo "<a href='../interfaces/lista_contratos_ocultos.php' class='print-file' style='display: block; width: 200px; margin: 50px auto; text-align: center; padding: 10px 20px; background-color: red; color: white; text-decoration: none; font-size: 16px; border-radius: 5px;'>Volver</a>";
?>

<div id="print-status" style="display: none; text-align: center; margin-top: 20px; font-size: 14px; padding: 10px; border-radius: 5px;"></div>



<script src="https://unpkg.com/@capacitor/core@latest/dist/capacitor.js"></script>
<script>
            console.log("Capacitor cargado:", window.Capacitor);
            console.log("Plugins disponibles:", window.Capacitor.Plugins);
</script>

<script>
function sendToPrinter(pdfUrl) {
  var statusDiv = document.getElementById("print-status");

  // Muestra un mensaje de "Procesando"
  statusDiv.style.display = "block";
  statusDiv.innerHTML = "⏳ Enviando a la impresora...";
  statusDiv.style.backgroundColor = "#ffcc00";
  statusDiv.style.color = "#000";

  if (window.Capacitor && window.Capacitor.Plugins && window.Capacitor.Plugins.Printer) {
    window.Capacitor.Plugins.Printer.print({ uri: pdfUrl })
      .then(() => {
        statusDiv.innerHTML = "✅ Ticket enviado correctamente.";
        statusDiv.style.backgroundColor = "#4CAF50";
        statusDiv.style.color = "#fff";
        console.log('Ticket enviado a la impresora');
      })
      .catch((error) => {
        statusDiv.innerHTML = "❌ Error al imprimir: " + error.message;
        statusDiv.style.backgroundColor = "#ff0000";
        statusDiv.style.color = "#fff";
        console.error('Error al imprimir:', error);
      });
  } else {
    statusDiv.innerHTML = "⚠️ El plugin de impresión no está Disponible.";
    statusDiv.style.backgroundColor = "#ff0000";
    statusDiv.style.color = "#fff";
    console.error('El plugin de impresión no está disponible.');
  }
  
  return false;
}
</script>


