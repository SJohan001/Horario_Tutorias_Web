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
    $idTutorado = isset($_POST['idTutorado']) ? $_POST['idTutorado'] : '';
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idTutorado)) {
        $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $apellidoPaterno = !empty($_POST['paterno']) ? $_POST['paterno'] : null;
        $apellidoMaterno = !empty($_POST['materno']) ? $_POST['materno'] : null;
        $matricula = !empty($_POST['matricula']) ? $_POST['matricula'] : null;
        $carrera = !empty($_POST['carrera']) ? $_POST['carrera'] : null;
        $correoInstitucional = !empty($_POST['correoInstitucional']) ? $_POST['correoInstitucional'] : null;
        $tutor = !empty($_POST['tutor']) ? $_POST['tutor'] : null;

        $errors = [];

        if (empty($nombre)) $errors[] = 'El campo "Nombre" es obligatorio.';
        if (empty($matricula)) $errors[] = 'El campo "Matrícula" es obligatorio.';
        if (empty($carrera)) $errors[] = 'El campo "Carrera" es obligatorio.';
        if (empty($correoInstitucional)) $errors[] = 'El campo "Correo institucional" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosEstudiante.php');
            exit();
        }

        $tutoradoModificar = $conn->prepare("UPDATE tutorado SET nombre = ?, apellidoPaterno = ?, apellidoMaterno = ?,
                                                 matricula = ?, carrera = ?, correoInstitucional = ?, tutor = ?  
                                                 WHERE idTutorado = ?");
        $tutoradoModificar->bind_param('sssssssi', $nombre, $apellidoPaterno, $apellidoMaterno, $matricula, $carrera, $correoInstitucional, $tutor, $idTutorado);

        if ($tutoradoModificar->execute()) {
            $verificarSesion = $conn->prepare("SELECT t.sesion 
                                                FROM tutorado t 
                                                WHERE t.idTutorado = ?");
            $verificarSesion->bind_param("s", $idTutorado);
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
                    $_SESSION['message'] = "Estudiante actualizado exitosamente.";
                    header("Location: administrarEstudiante.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Error al actualizar el estudiante.";
                    header("Location: menuAdministrador.php");
                    exit();
                }

                $tutoradoModificar->close();
                echo json_encode(['status' => 'success', 'message' => 'Datos del estudiante actualizados correctamente.']);
            } else {
                $_SESSION['message'] = "Error: No se recibió el ID de la sesión.";
                header("Location: registroEstudiante.php");
                exit();
            }
        }
        $conn->close();
    } else {
        echo "Error: No se recibió el ID del estudiante.";
        exit();
    }
?>