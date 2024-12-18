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
    $idCarrera = $_POST['idCarrera'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idCarrera)) {
        $carrera = !empty($_POST['carrera']) ? $_POST['carrera'] : null;
        
        $errors = [];

        if (empty($carrera)) $errors[] = 'El campo "Carrera" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosCarrera.php');
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
            $sql = "UPDATE carrera SET nombre = ? 
                    WHERE idCarrera = ?";
            $carreraModificar = $conn->prepare($sql);

            $carreraModificar->bind_param('si', $carrera, $idCarrera);


            if ($carreraModificar->execute()) {
                $_SESSION['message'] = "Carrera actualizada exitosamente.";
                header("Location: administrarCarreras.php");
                exit();
            } else {
                $_SESSION['message'] = "Error al actualizar la carrera.";
                header("Location: menuAdministrador.php");
                exit();
            }

            $consultaTutor->close();
            $carreraModificar->close();
            $conn->close();
        }else {
            echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para modificar esta carrera.']);
        }
    } else {
        echo "Error: No se recibió el ID de la carrera.";
        exit();
    }
?>