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
        $descripcion = $_POST['descripcion'];
        $tipoProblematica = $_POST['tipoProblematica'];

        if (empty($descripcion)) $errors[] = 'El campo "Descripcion" es obligatorio.';
        if (empty($tipoProblematica)) $errors[] = 'El campo "Tipo de Problematica" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: registroProblematica.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $sql = "INSERT INTO problematica (descripcion, tipoProblematica) 
                VALUES (?, ?)";
        $problematicaRegistro = $conn->prepare($sql);
        $problematicaRegistro->bind_param('si', $descripcion, $tipoProblematica);
        
        if ($problematicaRegistro->execute()) {
            $_SESSION['message'] = "Problemática académica registrada exitosamente.";
            header("Location: administrarProblematica.php");
            exit();
        } else {
            $_SESSION['message'] = "Error al registrar la problemática académica.";
            header("Location: menuAdministrador.php");
            exit();
        }

        $problematicaRegistro->close();
        $conn->close();
    }
?>