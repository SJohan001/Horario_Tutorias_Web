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
        $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
        $apellidoPaterno = !empty($_POST['paterno']) ? $_POST['paterno'] : null;
        $apellidoMaterno = !empty($_POST['materno']) ? $_POST['materno'] : null;
        $noPersonal = !empty($_POST['noPersonal']) ? $_POST['noPersonal'] : null;
        $correoInstitucional = !empty($_POST['correoInstitucional']) ? $_POST['correoInstitucional'] : null;
        $rol = !empty($_POST['rol']) ? $_POST['rol'] : null;

        if (empty($nombre)) $errors[] = 'El campo "Nombre" es obligatorio.';
        if (empty($correoInstitucional)) $errors[] = 'El campo "Correo institucional" es obligatorio.';
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: menuAdministrador.php');
            exit();
        }

        $menu = './cerrarSesion.php';
        switch ($rol) {
            case 1:
                $menu = './administrarProfesor.php';
                break;
            case 3:
                $menu = './administrarAdministrador.php';
                break;    
            case 4:
                $menu = './administrarCoordinador.php';
                break;
            case 5:
                $menu = './administrarJefeCarrera.php';
                break;
            default:
                $menu = './cerrarSesion.php';
                break;
        }

        include('./conection.php');
        $conn = conectiondb();

        $sesionNueva = $conn->prepare("INSERT INTO sesion (correoInstitucional, rol) VALUES (?, ?)");
        $sesionNueva->bind_param("ss", $correoInstitucional, $rol);

        if ($sesionNueva->execute()) {
            $consultaIdSesionUltima = $conn->prepare("SELECT MAX(s.idSesion) AS sesion FROM sesion s WHERE s.correoInstitucional = ?");
            $consultaIdSesionUltima->bind_param("s", $correoInstitucional);
            $consultaIdSesionUltima->execute();
            $result = $consultaIdSesionUltima->get_result();
            $objetoSesion = $result->fetch_assoc();
            $idSesion = $objetoSesion['sesion'];
            $consultaIdSesionUltima->close();

            if($rol == 1 || $rol == 4 || $rol == 5){
                $profesorNuevo = $conn->prepare("INSERT INTO tutor (nombre, apellidoPaterno, apellidoMaterno, noPersonal, correoInstitucional, sesion) VALUES (?, ?, ?, ?, ?, ?)");
                $profesorNuevo->bind_param("ssssss", $nombre, $apellidoPaterno, $apellidoMaterno, $noPersonal, $correoInstitucional, $idSesion);

                if ($profesorNuevo->execute()) {
                    $_SESSION['message'] = "Profesor registrado exitosamente.";
                    header("Location: $menu");
                    exit();
                } else {
                    $_SESSION['message'] = "Error al registrar el profesor.";
                    header("Location: menuAdministrador.php");
                    exit();
                }

                $profesorNuevo->close();
            } elseif($rol == 3) {
                $administradorNuevo = $conn->prepare("INSERT INTO administrador (nombre, apellidoPaterno, apellidoMaterno, noPersonal, correoInstitucional, sesion) VALUES (?, ?, ?, ?, ?, ?)");
                $administradorNuevo->bind_param("ssssss", $nombre, $apellidoPaterno, $apellidoMaterno, $noPersonal, $correoInstitucional, $idSesion);

                if ($administradorNuevo->execute()) {
                    $_SESSION['message'] = "Administrador registrado exitosamente.";
                    header("Location: $menu");
                    exit();
                } else {
                    $_SESSION['message'] = "Error al registrar el administrador.";
                    header("Location: menuAdministrador.php");
                    exit();
                }

                $administradorNuevo->close();
            } else {
                $_SESSION['message'] = "Error al designar rol.";
                header("Location: $menu");
                exit();
            }

            $conn->close();
        } else {
            $_SESSION['message'] = "Error al registrar la sesión.";
            header("Location: $menu");
            exit();
        }
    }
?>