<?php
    session_start();

    $rolesPermitidos = [3];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "Error: Solicitud no válida.";
        exit();
    }

    $userAdministrador = $_SESSION['correoInstitucional'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $apellidoPaterno = !empty($_POST['paterno']) ? $_POST['paterno'] : null;
        $apellidoMaterno = !empty($_POST['materno']) ? $_POST['materno'] : null;
        $matricula = !empty($_POST['matricula']) ? $_POST['matricula'] : null;
        $carrera = !empty($_POST['carrera']) ? $_POST['carrera'] : null;
        $correoInstitucional = !empty($_POST['correoInstitucional']) ? $_POST['correoInstitucional'] : null;
        $tutor = !empty($_POST['tutor']) ? $_POST['tutor'] : null;
        $rol = 2;

        if (empty($nombre)) $errors[] = 'El campo "Nombre" es obligatorio.';
        if (empty($matricula)) $errors[] = 'El campo "Matrícula" es obligatorio.';
        if (empty($carrera)) $errors[] = 'El campo "Carrera" es obligatorio.';
        if (empty($correoInstitucional)) $errors[] = 'El campo "Correo institucional" es obligatorio.';
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: registroEstudiante.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $sesionNueva = $conn->prepare("INSERT INTO sesion (correoInstitucional, rol) VALUES (?, ?)");
        $sesionNueva->bind_param("ss", $correoInstitucional, $rol);

        if ($sesionNueva->execute()) {
            $consultaIdSesionUltima = $conn->prepare("SELECT MAX(s.idSesion) AS sesion FROM sesion s WHERE s.correoInstitucional = ?");
            $consultaIdSesionUltima->bind_param("s", $correoInstitucional);
            $consultaIdSesionUltima->execute();
            $result = $consultaIdSesionUltima->get_result();
            $objetoSesion = $result->fetch_assoc();
            $idSesion = $objetoSesion['sesion'];
            $consultaIdSesionUltima->close();

            if($rol == 2){
                $estudianteNuevo = $conn->prepare("INSERT INTO tutorado (nombre, apellidoPaterno, apellidoMaterno, matricula, carrera, correoInstitucional, sesion, tutor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $estudianteNuevo->bind_param("ssssssss", $nombre, $apellidoPaterno, $apellidoMaterno, $matricula, $carrera, $correoInstitucional, $idSesion, $tutor);

                if ($estudianteNuevo->execute()) {
                    $_SESSION['message'] = "Estudiante registrado exitosamente.";
                    header("Location: administrarEstudiante.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Error al registrar el estudiante.";
                    header("Location: administrarEstudiante.php");
                    exit();
                }

                $profesorNuevo->close();
            } else {
                $_SESSION['message'] = "Error al designar rol.";
                header("Location: administrarEstudiante.php");
                exit();
            }

            $conn->close();
        } else {
            $_SESSION['message'] = "Error al registrar la sesión.";
            header("Location: administrarEstudiante.php");
            exit();
        }
    }
?>