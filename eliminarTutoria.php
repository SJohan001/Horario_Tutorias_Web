<?php
    session_start();
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF inválido.']);
        exit();
    }

    $rolesPermitidos = [1,4];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }
    
    include('./conection.php');
    $conn = conectiondb();

    $idTutoria = $_POST['idTutoria'];
    $correoActual = $_SESSION['correoInstitucional'];

    $verificarCorreo = $conn->prepare("SELECT tt.correoInstitucional  
                                       FROM tutoria t 
                                       INNER JOIN tutor tt ON tt.idTutor = t.tutor
                                       WHERE idTutoria = ?");
    $verificarCorreo->bind_param("i", $idTutoria);
    $verificarCorreo->execute();
    $verificarCorreo->bind_result($creadorCorreo);
    $verificarCorreo->fetch();
    $verificarCorreo->close();

    if ($creadorCorreo === $correoActual) {
        $tutoriaEliminar    = $conn->prepare("DELETE FROM tutoria t WHERE t.idTutoria = ?");
        $tutoriaEliminar->bind_param("i", $idTutoria);
        if ($tutoriaEliminar->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Tutoría eliminada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la tutoría.']);
        }
        $tutoriaEliminar->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar esta tutoría.']);
    }

    mysqli_close($conn);
    exit();
?>