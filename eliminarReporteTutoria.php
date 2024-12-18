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

    $idReporte = isset($_POST['idReporte']) ? $_POST['idReporte'] : '';

    $correoActual = $_SESSION['correoInstitucional'];

    $verificarCorreo = $conn->prepare("SELECT t.correoInstitucional  
                                       FROM tutor t 
                                       WHERE t.correoInstitucional = ?");
    $verificarCorreo->bind_param("s", $correoActual);
    $verificarCorreo->execute();
    $verificarCorreo->bind_result($correoAdministrador);
    $verificarCorreo->fetch();
    $verificarCorreo->close();

    if ($correoAdministrador === $correoActual) {
        $carreraTutorBusqueda = $conn->prepare("SELECT rt.carreraTutor 
                                          FROM reporte_tutoria rt
                                          WHERE rt.idReporte = ?");
        $carreraTutorBusqueda->bind_param("i", $idReporte);
        $carreraTutorBusqueda->execute();
        $result = $carreraTutorBusqueda->get_result();
        $objetoCarreraTutor = $result->fetch_assoc();
        $idCarreraTutor = $objetoCarreraTutor['carreraTutor'];
        $carreraTutorBusqueda->close();
        
        if($idCarreraTutor){
            $problematicaEliminar = $conn->prepare("DELETE FROM problematica_academica pa WHERE pa.reporte = ?");
            $problematicaEliminar->bind_param("i", $idReporte);
            
            if($problematicaEliminar->execute()){
                $reporteEliminar = $conn->prepare("DELETE FROM reporte_tutoria rt WHERE rt.idReporte = ?");
                $reporteEliminar->bind_param("i", $idReporte);

                if($reporteEliminar->execute()){
                    $carreraTutorEliminar = $conn->prepare("DELETE FROM carrera_tutor ct WHERE ct.idCarreraTutor = ?");
                    $carreraTutorEliminar->bind_param("i", $idCarreraTutor);

                    if ($carreraTutorEliminar->execute()) {
                        echo json_encode(['status' => 'success', 'message' => 'Reporte de tutoría eliminado con éxito.']);

                        $carreraTutorEliminar->close();
                        $reporteEliminar->close();
                        $problematicaEliminar->close();
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el reporte de tutoría.']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el reporte.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar las problemáticas.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar la relación carrera-tutor.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar este reporte.']);
    }

    mysqli_close($conn);
    exit();
?>