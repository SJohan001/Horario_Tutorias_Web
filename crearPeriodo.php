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
        $periodo = !empty($_POST['periodo']) ? $_POST['periodo'] : null;
        $actual = isset($_POST['actual']) ? $_POST['actual'] : null;
        
        if (empty($periodo)) $errors[] = 'El campo "Periodo" es obligatorio.';
        if ($actual === null) $errors[] = 'El campo "Actual" es obligatorio.'; 
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: registroPeriodo.php');
            exit();
        }

        include('./conection.php');
        $conn = conectiondb();

        $sql = "INSERT INTO periodo (nombre, actual) 
                VALUES (?, ?)";
        $periodoRegistro = $conn->prepare($sql);
        $periodoRegistro->bind_param('ss', $periodo, $actual);

        if ($periodoRegistro->execute()) {
            if($actual == 1){
                $idPeriodo = $conn->insert_id;
                $sqlActual = "UPDATE periodo SET actual = 0 
                        WHERE idPeriodo != ?";
                $periodoActual = $conn->prepare($sqlActual);
                $periodoActual->bind_param('s', $idPeriodo);
    
                if ($periodoActual->execute()) {
                    $_SESSION['message'] = "Periodo actual registrado exitosamente.";
                    header("Location: administrarPeriodoEscolar.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Error al registrar el periodo actual.";
                    header("Location: administrarPeriodoEscolar.php");
                    exit();
                }
    
                $periodoActual->close();
            } else {
                $_SESSION['message'] = "Periodo registrado exitosamente.";
                header("Location: administrarPeriodoEscolar.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Error al registrar la periodo.";
            header("Location: administrarPeriodoEscolar.php");
            exit();
        }

        $periodoRegistro->close();
        $conn->close();
    }
?>