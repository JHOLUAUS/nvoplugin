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
    <div class="tablitascroll">
        <table class="contract-table">
            <thead>
                <tr>
                    <th>Sel</th> <!-- Nueva columna para selección manual -->
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Alias</th>
                    <th>Calle</th>
                    <th>Numero</th>
                    <th>Departamento</th>
                    <th>Asentamiento</th>
                    <th>Municipio</th>
                    <th>Estado</th>
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
                        <td data-label="Calle">
                            <?php echo htmlspecialchars($row['calle_cobranza']); ?>
                        </td>
                        <td data-label="Numero">
                            <?php echo htmlspecialchars($row['numero_cobranza']); ?>
                        </td>
                        <td data-label="Departamento">
                            <?php echo htmlspecialchars($row['departamento_cobranza']); ?>
                        </td>
                        <td data-label="Asentamiento">
                            <?php echo htmlspecialchars($row['asentamiento_cobranza']); ?>
                        </td>
                        <td data-label="Municipio">
                            <?php echo htmlspecialchars($row['municipio_cobranza']); ?>
                        </td>
                        <td data-label="Estado">
                            <?php echo htmlspecialchars($row['estado_cobranza']); ?>
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
                                    <a href="#" onclick="confirmarEliminacion('<?php echo htmlspecialchars($row['folios']); ?>', '../funciones/eliminar_contrato.php?id_folio=<?php echo $row['id_folio']; ?>')">Eliminar</a>
                                    <a href="#" onclick="desasignarCobrador('<?php echo htmlspecialchars($row['folios']); ?>', '../funciones/desasignar_cobrador.php?id_folio=<?php echo $row['id_folio']; ?>')">Desasignar Cobrador</a>
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
    </div>
    </section>

    <script>
        function confirmarEliminacion(alias, url) {
            if (confirm("¿Deseas eliminar el contrato de " + alias + "?")) {
                window.location.href = url;
            }
        }
        function desasignarCobrador(alias, url) {
            if (confirm("¿Deseas desasignar cobrador a " + alias + "?")) {
                window.location.href = url;
            }
        }
    </script>

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
    try {
        const response = await fetch('template.xlsx');
        const arrayBuffer = await response.arrayBuffer();
        const workbook = XLSX.read(arrayBuffer, { type: 'array', cellStyles: true }); // cellStyles para conservar estilos
        const sheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[sheetName];

        let excelData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

        // Empezar en la fila 2 (ajusta según tu plantilla)
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
            excelData.push(rowData);
        });

        // Crear una nueva hoja con los datos actualizados
        const updatedWorksheet = XLSX.utils.aoa_to_sheet(excelData);
        workbook.Sheets[sheetName] = updatedWorksheet;

        // Generar el nombre del archivo con la fecha de hoy
        const today = new Date().toISOString().split('T')[0];
        const fileName = `contratos_${today}.xlsx`;

        // Descargar el archivo con los estilos intactos
        XLSX.writeFile(workbook, fileName);

    } catch (error) {
        console.error('Error al generar el archivo Excel:', error);
    }
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