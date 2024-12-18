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

    $idTutorado = isset($_POST['idTutorado']) ? $_POST['idTutorado'] : '';

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
        $sesionBusqueda = $conn->prepare("SELECT s.idSesion as sesion
                                          FROM sesion s
                                          INNER JOIN tutorado t ON t.correoInstitucional = s.correoInstitucional
                                          WHERE t.idTutorado = ?");
        $sesionBusqueda->bind_param("i", $idTutorado);
        $sesionBusqueda->execute();
        $result = $sesionBusqueda->get_result();
        $objetoSesion = $result->fetch_assoc();
        $idSesion = $objetoSesion['sesion'];
        $sesionBusqueda->close();
        
        if($idSesion){
            $sesionEliminar = $conn->prepare("DELETE FROM sesion s WHERE s.idSesion = ?");
            $sesionEliminar->bind_param("i", $idSesion);

            if($sesionEliminar->execute()){
                $profesorEliminar = $conn->prepare("DELETE FROM tutorado t WHERE t.idTutorado = ?");
                $profesorEliminar->bind_param("i", $idTutorado);

                if ($profesorEliminar->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Profesor eliminado con éxito.']);

                    $profesorEliminar->close();
                    $sesionEliminar->close();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el profesor.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la sesión.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar la sesión.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar este profesor.']);
    }

    mysqli_close($conn);
    exit();
?>