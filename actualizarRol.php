<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    $rolesPermitidos = [3];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }
    if(isset($_SESSION['message']))
    {   
        $message = $_SESSION['message'];
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $csrf_token = $_SESSION['csrf_token'];

    $user = $_SESSION['user'];

    include('conection.php');
    $conn = conectiondb();

    $tutoresConsulta = $conn->prepare("SELECT t.idTutor, 
                                           CONCAT(t.nombre, ' ', COALESCE(t.apellidoPaterno, ''), ' ', COALESCE(t.apellidoMaterno, '')) AS tutorNombre, 
                                           t.sesion 
                                       FROM tutor t 
                                       ORDER BY tutorNombre ASC");
    $tutoresConsulta->execute();
    $arrayTutores = $tutoresConsulta->get_result();
    $tutoresConsulta->close();

    $conn->close();
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Actualizacion de rol</title>
        <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        h1 {
            font-size: 40px;
            padding: 10px;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f0f0f0;
        }
        
        .header-left {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .header-left img {
            width: 150px;
        }
        .welcome-message {
            background-color: #bbb;
            padding: 50px 30px;
            width: 360px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border-radius: 5px 30px;
            font-weight: bold;
        }
        .header-left button {
            background-color: #f1f1f1;
            color: black;
            border: none;
            padding: 50px 20px;
            cursor: pointer;
            font-size: 20px;
        }
        .header-left button:hover {
            background-color: #7DCE94;
        }
        .header-right {
            font-weight: bold;
            font-size: 30px;
        }
        .new-button-container {
            text-align: right;
            margin: 20px auto;
            width: 60%;
        }
        .buttonAction {
            background-color: #28AD56;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .buttonAction:hover {
            opacity: 0.8;
        }
        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin: 20px auto;
            width: 100%;
        }

        form {
            background-color: #f7f7f7;
            padding: 30px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 60%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        .form-group label {
            display: block;
            font-size: 18px;
            margin-bottom: 10px;
            text-align: start;
            padding: 5px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .submit-button {
            background-color: #28AD56;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .submit-button:hover {
            opacity: 0.8;
        }

        footer {
            margin-top: auto;
            text-align: center;
            padding: 10px;
            background-color: #f1f1f1;
        }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-left">
            <img src="<?= logo_UV ?>" alt="UV Logo">
            <div class="welcome-message">Bienvenid@ <?php echo $_SESSION['user']; ?></div>
            <button class="buttonsHead" onclick="location.href='./menuAdministrador.php'"><i class="fas fa-home"></i> Inicio</button>
            <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </div>
        <div class="header-right">
            Universidad Veracruzana   
        </div>
    </div>

    <div class="form-container">
        <form action="modificarRol.php" method="POST" id="form">
            <div class="form-group">

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <h1>Actualización de rol</h1>
                <label for="idTutor">Profesores: <span class="text-danger">*</span></label>
                <select class="form-control" id="tutor" name="tutor">
                    <option value="" disabled selected>-----Selecciona un profesor-----</option>
                    <?php while($tutor = $arrayTutores->fetch_assoc()): ?>
                        <option value="<?= $tutor['sesion'] ?>"><?= $tutor['tutorNombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="rol">Roles: <span class="text-danger">*</span></label>
                <select class="form-control" id="rol" name="rol">
                    <option value="" disabled selected>-----Selecciona el nuevo rol para el profesor-----</option>
                    <option value="1">Tutor</option>
                    <option value="4">Coordinador de Tutorías</option>
                    <option value="5">Jefe de Carrera</option>
                </select>
            </div>

            <button id="enviar" name="enviar" type="submit" class="button buttonAction">Actualizar Rol</button>
        </form>
    </div>
    <footer>
        @ Universidad Veracruzana
    </footer>
</body>
</html>

<script>
    $(document).ready(function() {
        $("#enviar").on("click", function(e) {
            e.preventDefault(); 
            validarFormulario();
        });
    });
    
    function validarFormulario() {
        var tutor = $('#tutor').val();
        var rol = $('#rol').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!tutor || !rol) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!tutor) $('#tutor').addClass("borderRed");
            if (!rol) $('#rol').addClass("borderRed");
            
            error = true;
        }

        if (error) {
            return false;
        }

        $('#tutor, #rol').removeClass("borderRed").addClass("borderGreen");

        $("#form").submit();
    }
</script>