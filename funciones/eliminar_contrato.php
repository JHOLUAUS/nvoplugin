<?php
include('../funciones/conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener ID del folio desde la URL
    $id_folio = $_GET['id_folio'];

    // Consultar el contrato y datos relacionados
    $sql = "SELECT * FROM clientecontrato WHERE id_folio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_folio);
    $stmt->execute();
    $result = $stmt->get_result();
    $contrato = $result->fetch_assoc();
    $stmt->close();
    
    // Consultar los datos de la tabla 'folios'
    $sql = "SELECT * FROM folios WHERE id_folio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_folio);
    $stmt->execute();
    $result = $stmt->get_result();
    $folio_data = $result->fetch_assoc(); // Suponiendo que solo hay un registro
    $stmt->close();

    $datos = [];
    $tablas = ['abonos','armazon', 'historialclinico', 'lugarcobranza'];
    foreach ($tablas as $tabla) {
        $sql = "SELECT * FROM $tabla WHERE id_folio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_folio);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos[$tabla] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener ID del folio desde el formulario
    $id_folio = $_POST['id_folio'];

    try {
        // Deshabilitar restricciones de claves foráneas
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Eliminar registros de las tablas relacionadas
        $tablas = ['abonos','armazon', 'fotos', 'historialclinico', 'lugarcobranza', 'clientecontrato', 'folios'];
        foreach ($tablas as $tabla) {
            $sql = "DELETE FROM $tabla WHERE id_folio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_folio);
            $stmt->execute();
            $stmt->close();
        }

        // Reactivar restricciones de claves foráneas
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        header("Location: ../interfaces/lista_contratos_admin.php?mensaje=contrato_eliminado");
        exit();
    } catch (Exception $e) {
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        header("Location: pagina_error.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Eliminación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            padding: 20px;
            margin: 10px;
            text-align: center;
        }
        h1 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        h2, h3 {
            color: blue;
            font-size: 1.2rem;
            margin: 10px 0;
        }
        p, ul, li {
            color: #666;
            font-size: 1rem;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background: #f9f9f9;
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            font-size: 1rem;
            color: #fff;
            background: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn.cancel {
            background: #dc3545;
        }
        .btn.cancel:hover {
            background: #a71d2a;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .table-section {
            text-align: left;
            margin-top: 15px;
        }
        .table-section h3 {
            margin-top: 20px;
            text-align: center;
        }
        .red-text h2{
            color: red;
        }

    </style>
</head>
<body>
    <div class="container">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'GET'): ?>
            <h1>Confirmar Eliminación del contrato:</h1>
            <div class="red-text">
                <h2><?= $contrato['nombre_cliente'] ?></h2>
            </div>
            <h2>Datos del Contrato</h2>
            <div class="red-text">
                <h4><strong>Folio:</strong> <?= $folio_data['folios'] ?></h4>
                <h4><strong>Alias del Cliente:</strong> <?= $contrato['alias_cliente'] ?></h4>
            </div>

            <div class="table-section">
                <h2>Datos Relacionados</h2>
                <?php foreach ($datos as $tabla => $registros): ?>
                    <h3><?= ucfirst($tabla) ?></h3>
                    <?php if (count($registros) > 0): ?>
                        <ul>
                            <?php foreach ($registros as $registro): ?>
                                <li><?= implode(', ', $registro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No hay registros relacionados en <?= $tabla ?>.</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <form action="eliminar_contrato.php" method="POST">
                <input type="hidden" name="id_folio" value="<?= $id_folio ?>">
                <button type="submit" class="btn">Confirmar Eliminación</button>
                <a href="../interfaces/lista_contratos_admin.php" class="btn cancel">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
