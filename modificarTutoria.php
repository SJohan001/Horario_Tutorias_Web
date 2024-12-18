<?php
    session_start();

    $rolesPermitidos = [1,4];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Error: solicitud inválida o CSRF token no válido.');
    }

    $userTutor = $_SESSION['correoInstitucional'];
    $idTutoria = $_POST['idTutoria'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idTutoria)) {
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

        $errors = [];

        if (empty($carrera)) $errors[] = 'El campo "Carrera" es obligatorio.';
        if (empty($numTutoria)) $errors[] = 'El campo "Tutoría" es obligatorio.';
        if (empty($periodo)) $errors[] = 'El campo "Periodo Escolar" es obligatorio.';
        if (empty($modalidad)) $errors[] = 'El campo "Modalidad" es obligatorio.';
        if (empty($periodoAtencion)) $errors[] = 'El campo "Período Atención" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosTutoria.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $verificarCorreo = $conn->prepare("SELECT tt.correoInstitucional  
                                        FROM tutoria t 
                                        INNER JOIN tutor tt ON tt.idTutor = t.tutor
                                        WHERE idTutoria = ?");
        $verificarCorreo->bind_param("i", $idTutoria);
        $verificarCorreo->execute();
        $verificarCorreo->bind_result($creadorCorreo);
        $verificarCorreo->fetch();
        $verificarCorreo->close();

        $sql = "SELECT t.archivo FROM tutoria t WHERE t.idTutoria = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $idTutoria);
        $stmt->execute();
        $result = $stmt->get_result();
        $archivoExistente = null;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $archivoExistente = $row['archivo'];
        }
        $stmt->close();

        $archivo = $archivoExistente;

        if (isset($_FILES['archivo_horario']) && $_FILES['archivo_horario']['error'] == 0) {
            $archivoNombre = $_FILES['archivo_horario']['name'];
            $archivoTmp = $_FILES['archivo_horario']['tmp_name'];
            $archivoDestino = './uploads/' . $archivoNombre;

            if (move_uploaded_file($archivoTmp, $archivoDestino)) {
                $archivo = $archivoNombre;

                if ($archivoExistente && file_exists('./uploads/' . $archivoExistente)) {
                    if (unlink('./uploads/' . $archivoExistente)) {
                        echo "Archivo eliminado correctamente.";
                    } else {
                        echo "No se pudo eliminar el archivo.";
                    }
                }
            } else {
                echo "Error al subir el archivo.";
                exit();
            }
        }
        
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

        if ($creadorCorreo === $userTutor) {
            $sql = "UPDATE tutoria SET modalidad = ?, periodoAtencion = ?, lugar = ?, fecha = ?, 
                                    horaInicio = ?, horaFin = ?, nota = ?, archivo = ?, carrera = ?, 
                                    periodo = ?, numTutoria = ? 
                    WHERE idTutoria = ? AND tutor = ?";
            $tutoriaModificar = $conn->prepare($sql);

            $tutoriaModificar->bind_param('sssssssssiiii', $modalidad, $periodoAtencion, $lugar, $fecha, 
                                                        $horaInicio, $horaFinal, $notas, $archivo, $carrera, 
                                                        $periodo, $numTutoria, $idTutoria, $idTutor);


            if ($tutoriaModificar->execute()) {
                $_SESSION['message'] = "Tutoría actualizada exitosamente.";
                header("Location: tutorTutorias.php");
                exit();
            } else {
                $_SESSION['message'] = "Error al actualizar la tutoría.";
                header("Location: tutorTutorias.php");
                exit();
            }

            $consultaTutor->close();
            $tutoriaModificar->close();
            $conn->close();
        }else {
            echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para modificar esta tutoría.']);
        }
    } else {
        echo "Error: No se recibió el ID de la tutoría.";
        exit();
    }
?>