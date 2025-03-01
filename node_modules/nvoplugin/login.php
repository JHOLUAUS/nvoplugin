<?php
include('config.php');
include('funciones/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id_usuario, username, nombre_usuario, pass, ape_pat_usuario, ape_mat_usuario, tipo_usuario, estado_usuario, hora_inicio_usuario, hora_fin_usuario FROM usuarios WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_usuario, $username, $nombre_usuario, $hashed_password, $ape_pat_usuario, $ape_mat_usuario, $tipo_usuario, $estado_usuario, $hora_inicio_usuario, $hora_fin_usuario);
        $stmt->fetch();

        if ($estado_usuario != 'Habilitado') {
            header("Location: index.php?error=El acceso ha sido deshabilitado para este usuario.");
            exit();
        } else {
            date_default_timezone_set('America/Mexico_City');
            $hora_actual = date("H:i:s");

            if ($hora_actual < $hora_inicio_usuario || $hora_actual > $hora_fin_usuario) {
                header("Location: index.php?error=Acceso no permitido fuera del horario establecido de $hora_inicio_usuario a $hora_fin_usuario.");
                exit();
            } else {
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id_usuario'] = $id_usuario;
                    $_SESSION['username'] = $username;
                    $_SESSION['nombre_usuario'] = $nombre_usuario;
                    $_SESSION['ape_pat_usuario'] = $ape_pat_usuario;
                    $_SESSION['ape_mat_usuario'] = $ape_mat_usuario;
                    $_SESSION['tipo_usuario'] = $tipo_usuario;

                    if ($tipo_usuario == 'Administrador') {
                        header('Location: interfaces/panel_administrador.php');
                    } elseif ($tipo_usuario == 'Cobrador') {
                        header('Location: interfaces/panel_administrador.php');
                    } elseif ($tipo_usuario == 'Optometrista') {
                        header('Location: interfaces/contrato.php');
                    } elseif ($tipo_usuario == 'Laboratorista') {
                        header('Location: interfaces/lista_contratos_laboratorista.php');
                    } elseif ($tipo_usuario == 'Campo') {
                        header('Location: ../interfaces/lista_contratos_campo.php');
                    }
                    exit();
                } else {
                    header("Location: index.php?error=Contrase���a incorrecta.");
                    exit();
                }
            }
        }
    } else {
        header("Location: index.php?error=Nombre de usuario incorrecto.");
        exit();
    }
}
?>
