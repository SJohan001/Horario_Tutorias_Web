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
    $idProblematica = $_POST['idProblematica'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idProblematica)) {
        $descripcion = $_POST['descripcion'];
        $tipoProblematica = $_POST['tipoProblematica'];
        
        $errors = [];

        if (empty($descripcion)) $errors[] = 'El campo "Descripcion" es obligatorio.';
        if (empty($tipoProblematica)) $errors[] = 'El campo "Tipo de Problematica" es obligatorio.';

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosProblematica.php');
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
            $sql = "UPDATE problematica SET descripcion = ?, tipoProblematica = ? 
                    WHERE idProblematica = ?";
            $problematicaModificar = $conn->prepare($sql);

            $problematicaModificar->bind_param('ssi', $descripcion, $tipoProblematica, $idProblematica);

            if ($problematicaModificar->execute()) {
                $_SESSION['message'] = "Problemática académica actualizada exitosamente.";
                header("Location: administrarProblematica.php");
                exit();
            } else {
                $_SESSION['message'] = "Error al actualizar la problemática académica.";
                header("Location: menuAdministrador.php");
                exit();
            }

            $problematicaModificar->close();
            $conn->close();
        }else {
            echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para modificar esta problemática académica.']);
        }
    } else {
        echo "Error: No se recibió el ID de la problemática académica.";
        exit();
    }
?>