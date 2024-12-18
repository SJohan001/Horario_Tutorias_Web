<?php
    session_start();

    $rolesPermitidos = [3];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Error: solicitud inválida o CSRF token no válido.');
    }

    include('./conection.php');
    $conn = conectiondb();

    $correoActual = $_SESSION['correoInstitucional'];
    $idAdministrador = isset($_POST['idAdministrador']) ? $_POST['idAdministrador'] : '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idAdministrador)) {
        $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $apellidoPaterno = !empty($_POST['paterno']) ? $_POST['paterno'] : null;
        $apellidoMaterno = !empty($_POST['materno']) ? $_POST['materno'] : null;
        $noPersonal = !empty($_POST['noPersonal']) ? $_POST['noPersonal'] : null;
        $correoInstitucional = !empty($_POST['correoInstitucional']) ? $_POST['correoInstitucional'] : null;
        
        $errors = [];

        if (empty($nombre)) $errors[] = 'El campo "Nombre" es obligatorio.';
        if (empty($correoInstitucional)) $errors[] = 'El campo "Correo institucional" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosAdministrador.php');
            exit();
        }

        $administradorModificar = $conn->prepare("UPDATE administrador SET nombre = ?, apellidoPaterno = ?, apellidoMaterno = ?,
                                                                           noPersonal = ?, correoInstitucional = ? 
                                                  WHERE idAdministrador = ?");
        $administradorModificar->bind_param('sssssi', $nombre, $apellidoPaterno, $apellidoMaterno, $noPersonal, $correoInstitucional, $idAdministrador);

        if ($administradorModificar->execute()) {
            $verificarSesion = $conn->prepare("SELECT a.sesion 
                                                FROM administrador a 
                                                WHERE a.idAdministrador = ?");
            $verificarSesion->bind_param("s", $idAdministrador);
            $verificarSesion->execute();
            $verificarSesion->bind_result($sesion);
            $verificarSesion->fetch();
            $verificarSesion->close();

            if($sesion){
                $sesionModificar = $conn->prepare("UPDATE sesion 
                                                    SET correoInstitucional = ? 
                                                    WHERE idSesion = ?");
                $sesionModificar->bind_param('si', $correoInstitucional, $sesion);
                if($sesionModificar->execute()){    
                    $_SESSION['message'] = "Administrador actualizado exitosamente.";
                    header("Location: administrarAdministrador.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Error al actualizar el administrador.";
                    header("Location: menuAdministrador.php");
                    exit();
                }

                $administradorModificar->close();
                echo json_encode(['status' => 'success', 'message' => 'Datos del administrador actualizados correctamente.']);
            } else {
                $_SESSION['message'] = "Error: No se recibió el ID de la sesión.";
                header("Location: registroAdministrador.php");
                exit();
            }
        }
        $conn->close();
    } else {
        echo "Error: No se recibió el ID del administrador.";
        exit();
    }
?>