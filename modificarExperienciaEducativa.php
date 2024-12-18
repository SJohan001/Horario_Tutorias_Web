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

    $correoActual = $_SESSION['correoInstitucional'];
    $idExperiencia = $_POST['idExperiencia'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idExperiencia)) {
        $nombre = $_POST['nombre'];
        $nrc = $_POST['nrc'];
        $profesor = $_POST['profesor'];
        $programa = $_POST['programa'];
        
        $errors = [];

        if (empty($nombre)) $errors[] = 'El campo "Nombre" es obligatorio.';
        if (!isset($nrc) || $nrc === '') $errors[] = 'El campo "NRC" es obligatorio.';
        if (empty($profesor)) $errors[] = 'El campo "Profesor" es obligatorio.';
        if (empty($programa)) $errors[] = 'El campo "Programa" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosExperienciaEducativa.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $verificarCorreo = $conn->prepare("SELECT a.correoInstitucional  
                                       FROM administrador a 
                                       WHERE a.correoInstitucional = ?");
        $verificarCorreo->bind_param("s", $correoActual);
        $verificarCorreo->execute();
        $verificarCorreo->bind_result($correoAdministrador);
        $verificarCorreo->fetch();
        $verificarCorreo->close();

        if ($correoAdministrador === $correoActual) {
            $sql = "UPDATE experiencia_educativa SET nombre = ?, nrc = ?, profesor = ?, programaEducativo = ? 
                    WHERE idExperienciaEducativa = ?";
            $experienciaEducativaModificar = $conn->prepare($sql);

            $experienciaEducativaModificar->bind_param('ssssi', $nombre, $nrc, $profesor, $programa, $idExperiencia);


            if ($experienciaEducativaModificar->execute()) {
                $_SESSION['message'] = "Experiencia educativa actualizada exitosamente.";
                header("Location: administrarExperienciaEducativa.php");
                exit();
            } else {
                $_SESSION['message'] = "Error al actualizar la experiencia educativa.";
                header("Location: menuAdministrador.php");
                exit();
            }

            $experienciaEducativaModificar->close();
            $conn->close();
        }else {
            echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para modificar esta experiencia educativa.']);
        }
    } else {
        echo "Error: No se recibió el ID de la experiencia educativa.";
        exit();
    }
?>