<?php
    session_start();
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF inválido.']);
        exit();
    }

    $rolesPermitidos = [3];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }
    
    include('./conection.php');
    $conn = conectiondb();

    $idProblematica = $_POST['idProblematica'];
    $correoActual = $_SESSION['correoInstitucional'];

    $verificarCorreo = $conn->prepare("SELECT a.correoInstitucional  
                                       FROM administrador a 
                                       WHERE a.correoInstitucional = ?");
    $verificarCorreo->bind_param("s", $correoActual);
    $verificarCorreo->execute();
    $verificarCorreo->bind_result($correoAdministrador);
    $verificarCorreo->fetch();
    $verificarCorreo->close();

    if ($correoAdministrador === $correoActual) {
        $problematicaEliminar    = $conn->prepare("DELETE FROM problematica p WHERE p.idProblematica = ?");
        $problematicaEliminar->bind_param("i", $idProblematica);
        if ($problematicaEliminar->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Problemática eliminada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la problemática.']);
        }
        $periodoEliminar->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar esta problemática.']);
    }

    mysqli_close($conn);
    exit();
?>