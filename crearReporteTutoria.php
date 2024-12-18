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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idTutor = $_POST['tutor'];
        $carrera = $_POST['carrera'];
        $numTutoria = $_POST['numTutoria'];
        $fechaInicio = $_POST['fechaInicio'];
        $fechaFin = $_POST['fechaFin'];
        $numAsistencias = $_POST['numAsistencias'];
        $numRiesgo = $_POST['numRiesgo'];
        $comentario = !empty($_POST['comentario']) ? $_POST['comentario'] : null;
        $fechaActual = date('Y-m-d');       

        if (empty($carrera)) $errors[] = 'El campo "Carrera" es obligatorio.';
        if (empty($numTutoria)) $errors[] = 'El campo "Número de tutoría" es obligatorio.';
        if (empty($fechaInicio)) $errors[] = 'El campo "Fecha de inicio" es obligatorio.';
        if (empty($fechaFin)) $errors[] = 'El campo "Fecha de fin" es obligatorio.';
        if (!isset($numAsistencias) || $numAsistencias === '') $errors[] = 'El campo "Número de asistencias" es obligatorio.';
        if (!isset($numRiesgo) || $numRiesgo === '') $errors[] = 'El campo "Número de tutorados en riesgo" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: registroReporteTutoria.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $periodoActual = $_SESSION['periodoActual'];

        $periodoConsulta = $conn->prepare("SELECT p.idPeriodo 
                                            FROM periodo p
                                            wHERE p.nombre = ?");
        $periodoConsulta->bind_param("s", $periodoActual);
        $periodoConsulta->execute();
        $periodoConsulta->bind_result($periodo);
        $periodoConsulta->fetch();
        $periodoConsulta->close();

        $sqlCarreraTutor = "INSERT INTO carrera_tutor (carrera, tutor) 
                VALUES (?, ?)";
        $carreraTutorRegistro = $conn->prepare($sqlCarreraTutor);
        $carreraTutorRegistro->bind_param('ss', $carrera, $idTutor);
        
        if ($carreraTutorRegistro->execute()) {
            $idCarreraTutor = $conn->insert_id;
            $sqlReporteTutoria = "INSERT INTO reporte_tutoria (carreraTutor, periodo, numTutoria, fechaInicioTutoria, fechaFinTutoria, numAsistencia, numRiesgo, comentario, fechaCreacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $reporteTutoriaRegistro = $conn->prepare($sqlReporteTutoria);
            $reporteTutoriaRegistro->bind_param('sssssssss', $idCarreraTutor, $periodo, $numTutoria, $fechaInicio, $fechaFin, $numAsistencias, $numRiesgo, $comentario, $fechaActual);
            
            if ($reporteTutoriaRegistro->execute()) {
                if ($_POST['tipo'] === 'problematica') {
                    $idReporte = $conn->insert_id;
                    $experiencias = $_POST['experienciaE'] ?? [];
                    $profesores = $_POST['profesor'] ?? [];
                    $problematicas = $_POST['problematica'] ?? [];
                    $otros = $_POST['otro'] ?? [];
                    $numAfectados = $_POST['numAlumnos'] ?? [];
                    $estado = 'En revisión';

                    for ($i = 0; $i < count($experiencias); $i++) {
                        if (empty($experiencias)) $errors[] = 'El campo "Experiencia Educativa" es obligatorio y debe tener al menos un valor.';
                        else {
                            foreach ($experiencias as $index => $experiencia) {
                                if (empty($experiencia)) {
                                    $errors[] = "El valor en 'Experiencia Educativa' en la posición " . ($index + 1) . " es obligatorio.";
                                }
                            }
                        }
                        
                        if (empty($profesores)) $errors[] = 'El campo "Profesor" es obligatorio y debe tener al menos un valor.';
                        else {
                            foreach ($profesores as $index => $profesor) {
                                if (empty($profesor)) {
                                    $errors[] = "El valor en 'Profesor' en la posición " . ($index + 1) . " es obligatorio.";
                                }
                            }
                        }

                        for ($i = 0; $i < max(count($problematicas), count($otros)); $i++) {
                            $problematica = $problematicas[$i] ?? '';
                            $otro = $otros[$i] ?? '';
                    
                            if (empty($problematica) && empty($otro)) {
                                $errors[] = "Debe rellenarse una opción en la posición " . ($i + 1) . ": 'Problemática' o 'Otro'.";
                            }
                        }
                        
                        if (empty($numAfectados)) $errors[] = 'El campo "Número de Afectados" es obligatorio y debe tener al menos un valor.';
                        else {
                            foreach ($numAfectados as $index => $afectado) {
                                if (empty($afectado)) {
                                    $errors[] = "El valor en 'Número de Afectados' en la posición " . ($index + 1) . " es obligatorio.";
                                }
                            }
                        }
                    
                        if (!empty($errors)) {
                            $_SESSION['errors'] = $errors;
                            header('Location: registroReporteTutoria.php');
                            exit();
                        }
                    }

                    for ($i = 0; $i < count($experiencias); $i++) {
                        $problematica = isset($problematicas[$i]) ? $problematicas[$i] : null;
                        $otro = isset($otros[$i]) ? $otros[$i] : null;

                        if ($problematica === 'otro') {
                            $problematica = null;
                        }

                        if (empty($problematica) && empty($otro)) {
                            $errors[] = "Debe rellenarse una opción en la posición " . ($i + 1) . ": 'Problemática' o 'Otro'.";
                            continue;
                        }
                        
                        $sqlProblematicaAcademica = "INSERT INTO problematica_academica (experienciaEducativa, problematica, otro, numAlumnos, estado, reporte) 
                                              VALUES (?, ?, ?, ?, ?, ?)";
                        $problematicaAcademicaRegistro = $conn->prepare($sqlProblematicaAcademica);
                        $problematicaAcademicaRegistro->bind_param("sssssi", $experiencias[$i], $problematica, $otro, $numAfectados[$i], $estado ,$idReporte);
                        if ($problematicaAcademicaRegistro->execute()) $successful[] = "Reporte de tutoría registrada exitosamente.";
                        else $errors[] = 'Problemática académica falló al ser registrada.';
                    }
                    
                    if (!empty($successful)) {
                        $problematicaAcademicaRegistro->close();
                        $carreraTutorRegistro->close();
                        $reporteTutoriaRegistro->close();
                        $conn->close();
                        $_SESSION['message'] = $successful;
                        header("Location: administrarReporteTutoria.php");
                        exit();
                    } else if (!empty($errors)) {
                        $_SESSION['errors'] = $errors;
                        header('Location: registroReporteTutoria.php');
                        exit();
                    }
                } else {
                    $_SESSION['message'] = "Reporte de tutoría registrada exitosamente.";
                    header("Location: administrarReporteTutoria.php");
                    exit();
                }
            } else {
                $_SESSION['message'] = "Error al registrar el reporte de tutoría.";
                header("Location: administrarReporteTutoria.php");
                exit();
            } 
        } else {
            $_SESSION['message'] = "Error al registrar la carrera y tutor.";
            header("Location: administrarReporteTutoria.php");
            exit();
        }
        
        $carreraTutorRegistro->close();
        $reporteTutoriaRegistro->close();
        $conn->close();
    }
?>