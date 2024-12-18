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
        $nombre = $_POST['nombre'];
        $nrc = $_POST['nrc'];
        $profesor = $_POST['profesor'];
        $programa = $_POST['programa'];

        if (empty($nombre)) $errors[] = 'El campo "Nombre" es obligatorio.';
        if (!isset($nrc) || $nrc === '') $errors[] = 'El campo "NRC" es obligatorio.';
        if (empty($profesor)) $errors[] = 'El campo "Profesor" es obligatorio.';
        if (empty($programa)) $errors[] = 'El campo "Programa" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: registroExperienciaEducativa.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $sql = "INSERT INTO experiencia_educativa (nombre, nrc, profesor, programaEducativo) 
                VALUES (?, ?, ?, ?)";
        $experienciaEducativaRegistro = $conn->prepare($sql);
        $experienciaEducativaRegistro->bind_param('ssss', $nombre, $nrc, $profesor, $programa);
        
        if ($experienciaEducativaRegistro->execute()) {
            $_SESSION['message'] = "Experiencia educativa registrada exitosamente.";
            header("Location: administrarExperienciaEducativa.php");
            exit();
        } else {
            $_SESSION['message'] = "Error al registrar la experiencia educativa.";
            header("Location: administrarExperienciaEducativa.php");
            exit();
        }

        $experienciaEducativaRegistro->close();
        $conn->close();
    }
?>