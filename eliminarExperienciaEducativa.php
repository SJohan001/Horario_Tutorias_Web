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

    $idExperiencia = isset($_POST['idExperiencia']) ? $_POST['idExperiencia'] : '';
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
        $experienciaEducativaEliminar    = $conn->prepare("DELETE FROM experiencia_educativa ee WHERE ee.idExperienciaEducativa = ?");
        $experienciaEducativaEliminar->bind_param("i", $idExperiencia);
        if ($experienciaEducativaEliminar->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Experiencia educativa eliminada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la experiencia educativa.']);
        }
        $experienciaEducativaEliminar->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar esta experiencia educativa.']);
    }

    mysqli_close($conn);
    exit();
?>