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

    $idPeriodo = $_POST['idPeriodo'];
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
        $periodoEliminar    = $conn->prepare("DELETE FROM periodo p WHERE p.idPeriodo = ?");
        $periodoEliminar->bind_param("i", $idPeriodo);
        if ($periodoEliminar->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Periodo escolar eliminado con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el periodo escolar.']);
        }
        $periodoEliminar->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar este periodo.']);
    }

    mysqli_close($conn);
    exit();
?>