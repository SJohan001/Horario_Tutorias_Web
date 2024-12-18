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
    $idPeriodo = $_POST['idPeriodo'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($idPeriodo)) {
        $periodo = !empty($_POST['periodo']) ? $_POST['periodo'] : null;
        $actual = isset($_POST['actual']) ? $_POST['actual'] : null;
        
        $errors = [];

        if (empty($periodo)) $errors[] = 'El campo "Periodo" es obligatorio.';
        if ($actual === null) $errors[] = 'El campo "Actual" es obligatorio.'; 

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: datosPeriodo.php');
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
            $sql = "UPDATE periodo SET nombre = ?, actual = ? 
                    WHERE idPeriodo = ?";
            $periodoModificar = $conn->prepare($sql);

            $periodoModificar->bind_param('ssi', $periodo, $actual, $idPeriodo);


            if ($periodoModificar->execute()) {
                if($actual == 1){
                    $sqlActual = "UPDATE periodo SET actual = 0 
                                  WHERE idPeriodo != ?";
                    $periodoActual = $conn->prepare($sqlActual);
                    $periodoActual->bind_param('s', $idPeriodo);
        
                    if ($periodoActual->execute()) {
                        $_SESSION['message'] = "Periodo actual actualizado exitosamente.";
                        header("Location: administrarPeriodoEscolar.php");
                        exit();
                    } else {
                        $_SESSION['message'] = "Error al actualizar el periodo actual.";
                        header("Location: administrarPeriodoEscolar.php");
                        exit();
                    }
        
                    $periodoActual->close();
                } else {
                    $_SESSION['message'] = "Periodo actualizado exitosamente.";
                    header("Location: administrarPeriodoEscolar.php");
                    exit();
                }
            } else {
                $_SESSION['message'] = "Error al actualizar el periodo escolar.";
                header("Location: menuAdministrador.php");
                exit();
            }

            $consultaTutor->close();
            $periodoModificar->close();
            $conn->close();
        }else {
            echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para modificar este periodo.']);
        }
    } else {
        echo "Error: No se recibió el ID del periodo.";
        exit();
    }
?>