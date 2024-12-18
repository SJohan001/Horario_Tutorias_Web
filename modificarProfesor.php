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
    $idTutor = isset($_POST['idTutor']) ? $_POST['idTutor'] : '';
    $rol = isset($_POST['rol']) ? $_POST['rol'] : '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idTutor)) {
        $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $apellidoPaterno = !empty($_POST['paterno']) ? $_POST['paterno'] : null;
        $apellidoMaterno = !empty($_POST['materno']) ? $_POST['materno'] : null;
        $noPersonal = !empty($_POST['noPersonal']) ? $_POST['noPersonal'] : null;
        $correoInstitucional = !empty($_POST['correoInstitucional']) ? $_POST['correoInstitucional'] : null;
        
        $errors = [];

        if (empty($nombre)) $errors[] = 'El campo "Nombre" es obligatorio.';
        if (empty($noPersonal)) $errors[] = 'El campo "Número personal" es obligatorio.';
        if (empty($correoInstitucional)) $errors[] = 'El campo "Correo institucional" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosProfesor.php');
            exit();
        }

        $menu = './cerrarSesion.php';
        switch ($rol) {
            case 1:
                $menu = './administrarProfesor.php';
                break;
            case 4:
                $menu = './administrarCoordinador.php';
                break;
            case 5:
                $menu = './administrarJefeCarrera.php';
                break;
            default:
                $menu = './cerrarSesion.php';
                break;
        }

        $tutorModificar = $conn->prepare("UPDATE tutor SET nombre = ?, apellidoPaterno = ?, apellidoMaterno = ?,
                                                 noPersonal = ?, correoInstitucional = ? 
                                                 WHERE idTutor = ?");
        $tutorModificar->bind_param('sssssi', $nombre, $apellidoPaterno, $apellidoMaterno, $noPersonal, $correoInstitucional, $idTutor);

        if ($tutorModificar->execute()) {
            $verificarSesion = $conn->prepare("SELECT t.sesion 
                                                FROM tutor t 
                                                WHERE t.idTutor = ?");
            $verificarSesion->bind_param("s", $idTutor);
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
                    $_SESSION['message'] = "Profesor actualizado exitosamente.";
                    header("Location: $menu");
                    exit();
                } else {
                    $_SESSION['message'] = "Error al actualizar el profesor.";
                    header("Location: menuAdministrador.php");
                    exit();
                }

                $tutorModificar->close();
                echo json_encode(['status' => 'success', 'message' => 'Datos del profesor actualizados correctamente.']);
            } else {
                $_SESSION['message'] = "Error: No se recibió el ID de la sesión.";
                header("Location: $menu");
                exit();
            }
        }
        $conn->close();
    } else {
        echo "Error: No se recibió el ID del profesor.";
        exit();
    }
?>