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
        $carrera = !empty($_POST['carrera']) ? $_POST['carrera'] : null;

        if (empty($carrera)) $errors[] = 'El campo "Carrera" es obligatorio.';
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: registroCarrera.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $sql = "INSERT INTO carrera (nombre) 
                VALUES (?)";
        $carreraRegistro = $conn->prepare($sql);
        $carreraRegistro->bind_param('s', $carrera);
        
        if ($carreraRegistro->execute()) {
            $_SESSION['message'] = "Carrera registrada exitosamente.";
            header("Location: administrarCarreras.php");
            exit();
        } else {
            $_SESSION['message'] = "Error al registrar la carrera.";
            header("Location: administrarCarreras.php");
            exit();
        }

        $carreraRegistro->close();
        $conn->close();
    }
?>