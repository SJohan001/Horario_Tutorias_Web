<?php
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include('conection.php');
        $conn = conectiondb();

        $idSesion = $_POST['tutor'];
        $idRol = $_POST['rol'];

        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Token CSRF inválido.");
        }

        $stmt = $conn->prepare("UPDATE sesion 
                                SET rol = ? 
                                WHERE idSesion = ?");
        $stmt->bind_param("ii", $idRol, $idSesion);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Rol actualizado correctamente.";
        } else {
            $_SESSION['message'] = "Error al actualizar el rol.";
        }

        $stmt->close();
        $conn->close();
        header('Location: ./menuAdministrador.php');
    }
?>
