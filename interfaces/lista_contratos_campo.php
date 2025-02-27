<?php include('header.php'); ?>
<?php
include('../funciones/conexion.php');

// Verificar si el usuario ha iniciado sesión y si es laboratorista
if (!isset($_SESSION['loggedin']) || ($_SESSION['tipo_usuario'] != 'Campo' && $_SESSION['tipo_usuario'] != 'Administrador')) {
    header('Location: login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Contratos Editables</title>
    <link rel="stylesheet" href="../css/lista_contratos.css?v=<?php echo(rand()); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <style>
        /* Estilos para el botón flotante */
        .floating-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
        }

        .floating-button:hover {
            background-color: #0056b3;
        }

        .floating-button i {
            font-size: 24px;
        }
    </style>
</head>

<body>
    <section class="contract-table-wrapper">
        <h2>Lista de Contratos Editables</h2>
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

        <div class="table-container">
            <table class="contract-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Alias</th>
                        <th>Total</th>
                        <th>Fecha de Creación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
    <?php
 $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

 if ($_SESSION['tipo_usuario'] == 'Campo') {
     $sql = "
         SELECT 
             c.id_cliente, 
             c.id_folio, 
             c.nombre_cliente, 
             c.alias_cliente, 
             c.estado_contrato, 
             f.folios, 
             f.total, 
             f.estado_liquidacion,
             f.fecha_creacion
         FROM 
             clientecontrato c
         JOIN 
             folios f 
         ON 
             c.id_folio = f.id_folio
         WHERE 
             c.id_usuario = ?
             AND f.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 2 DAY)
             AND (
                 c.nombre_cliente LIKE ? OR
                 c.alias_cliente LIKE ? OR
                 f.folios LIKE ? OR
                 f.total LIKE ? OR
                 f.fecha_creacion LIKE ? OR
                 c.estado_contrato LIKE ?
             )
         ORDER BY 
             f.folios DESC;
     ";
 
     $stmt = $conn->prepare($sql);
     if (!$stmt) {
         die("Error en la preparación de la consulta: " . $conn->error);
     }
 
     $stmt->bind_param("issssss", $id_usuario, $search, $search, $search, $search, $search, $search);
 } elseif ($_SESSION['tipo_usuario'] == 'Administrador') {
     $sql = "
         SELECT 
             c.id_cliente, 
             c.id_folio, 
             c.nombre_cliente, 
             c.alias_cliente, 
             c.estado_contrato, 
             f.folios, 
             f.total, 
             f.estado_liquidacion,
             f.fecha_creacion
         FROM 
             clientecontrato c
         JOIN 
             folios f 
         ON 
             c.id_folio = f.id_folio
         WHERE 
             c.nombre_cliente LIKE ? OR
             c.alias_cliente LIKE ? OR
             f.folios LIKE ? OR
             f.total LIKE ? OR
             f.fecha_creacion LIKE ? OR
             c.estado_contrato LIKE ?
         ORDER BY 
             f.folios DESC;
     ";
 
     $stmt = $conn->prepare($sql);
     if (!$stmt) {
         die("Error en la preparación de la consulta: " . $conn->error);
     }
 
     $stmt->bind_param("ssssss", $search, $search, $search, $search, $search, $search);
 }

    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si hay resultados
    if ($result->num_rows > 0) {
        // Mostrar los contratos
        while ($row = $result->fetch_assoc()) {
            ?>
            <tr>
                <td data-label="Folio">
                    <a href="informacion_de_contrato_campo.php?id_cliente=<?php echo $row['id_cliente']; ?>&id_folio=<?php echo $row['id_folio']; ?>">
                        <?php echo htmlspecialchars($row['folios']); ?>
                    </a>
                </td>
                <td data-label="Cliente">
                    <?php echo htmlspecialchars($row['nombre_cliente']); ?>
                </td>
                <td data-label="Alias">
                    <?php echo htmlspecialchars($row['alias_cliente']); ?>
                </td>
                <td data-label="Total">
                    <?php echo htmlspecialchars($row['total']); ?>
                </td>
                <td data-label="Fecha de Creacion">
                    <?php echo htmlspecialchars($row['fecha_creacion']); ?>
                </td>
                <td data-label="Estado">
                    <?php echo htmlspecialchars($row['estado_contrato']); ?>
                </td>
            </tr>
            <?php
        }
    } else {
        // Mostrar mensaje si no hay contratos
        echo '<tr><td colspan="6" style="text-align:center;">No se encontraron contratos.</td></tr>';
    }

    // Cierra la consulta
    $stmt->close();
    ?>
</tbody>
            </table>
        </div>
    </section>

    <!-- Botón flotante -->
    <button class="floating-button" onclick="window.location.href='contrato.php';">
        <i class="fas fa-plus"></i>
    </button>
</body>
</html>
