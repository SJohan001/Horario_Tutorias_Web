<?php
    session_start();

    $rolesPermitidos = [1,4];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "Error: Solicitud no válida.";
        exit();
    }

    $userTutor = $_SESSION['correoInstitucional'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $carrera = $_POST['carrera'];
        $numTutoria = $_POST['numTutoria'];
        $periodo = $_POST['periodo'];
        $modalidad = $_POST['modalidad'];
        $periodoAtencion = $_POST['periodoAtencion'];
        $lugar = !empty($_POST['lugar']) ? $_POST['lugar'] : null;
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : null; 
        $horaInicio = !empty($_POST['hora_inicio']) ? $_POST['hora_inicio'] : null; 
        $horaFinal = !empty($_POST['hora_final']) ? $_POST['hora_final'] : null; 
        $notas = !empty($_POST['notas']) ? $_POST['notas'] : null;
        $archivo = '';

        if (isset($_FILES['archivo_horario']) && $_FILES['archivo_horario']['error'] == 0) {
            $archivoNombre = $_FILES['archivo_horario']['name'];
            $archivoTmp = $_FILES['archivo_horario']['tmp_name'];
            $archivoDestino = './uploads/' . $archivoNombre;
            
            if (move_uploaded_file($archivoTmp, $archivoDestino)) {
                $archivo = $archivoNombre; 
            } else {
                echo "Error al subir el archivo.";
                exit();
            }
        }

        if (empty($carrera)) $errors[] = 'El campo "Carrera" es obligatorio.';
        if (empty($numTutoria)) $errors[] = 'El campo "Tutoría" es obligatorio.';
        if (empty($periodo)) $errors[] = 'El campo "Periodo Escolar" es obligatorio.';
        if (empty($modalidad)) $errors[] = 'El campo "Modalidad" es obligatorio.';
        if (empty($periodoAtencion)) $errors[] = 'El campo "Período Atención" es obligatorio.';
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: registroTutoria.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();
        
        $tutor = "SELECT t.idTutor FROM tutor t WHERE t.correoInstitucional = ?";
        $consultaTutor = $conn->prepare($tutor);
        $consultaTutor->bind_param('s', $userTutor);
        $consultaTutor->execute();
        $resultadoTutor = $consultaTutor->get_result();

        if ($resultadoTutor->num_rows > 0) {
            $tutorRow = $resultadoTutor->fetch_assoc();
            $idTutor = $tutorRow['idTutor'];
        } else {
            echo "No se encontró el tutor.";
            $consultaTutor->close();
            $conn->close();
            exit();
        }

        $sql = "INSERT INTO tutoria (numTutoria, modalidad, periodoAtencion, lugar, fecha, horaInicio, horaFin, nota, carrera, periodo, tutor, archivo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $tutoriaRegistro = $conn->prepare($sql);
        $tutoriaRegistro->bind_param('ssssssssssss', $numTutoria, $modalidad, $periodoAtencion, $lugar, $fecha, $horaInicio, $horaFinal, $notas, $carrera, $periodo, $idTutor, $archivo);
        
        if ($tutoriaRegistro->execute()) {
            $_SESSION['message'] = "Tutoría registrada exitosamente.";
            header("Location: tutorTutorias.php");
            exit();
        } else {
            $_SESSION['message'] = "Error al registrar la tutoría.";
            header("Location: tutorTutorias.php");
            exit();
        }

        $consultaTutor->close();
        $tutoriaRegistro->close();
        $conn->close();
    }
?>