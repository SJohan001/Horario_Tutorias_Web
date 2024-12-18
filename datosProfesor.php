<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $csrf_token = $_SESSION['csrf_token'];

    $rolesPermitidos = [3];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }
    if(isset($_SESSION['message']))
    {   
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
    }

    $userActual = $_SESSION['correoInstitucional'];

    $idTutor = isset($_POST['idTutor']) ? $_POST['idTutor'] : '';


    if($idTutor){
        include ('./conection.php');
        $conn = conectiondb();

        $tutorModificar = $conn->prepare("SELECT t.idTutor, 
                                                 t.nombre, 
                                                 t.apellidoPaterno, 
                                                 t.apellidoMaterno, 
                                                 t.noPersonal, 
                                                 t.correoInstitucional, 
                                                 t.sesion, 
                                                 s.rol 
                                         FROM tutor t
                                         INNER JOIN sesion s ON s.idSesion = t.sesion
                                         WHERE t.idTutor = ?");
        $tutorModificar->bind_param("i", $idTutor);
        $tutorModificar->execute();
        $result = $tutorModificar->get_result();
        $tutor = $result->fetch_assoc();
        $tutorModificar->close();

        $nombre = isset($tutor['nombre']) ? htmlspecialchars($tutor['nombre']) : '';
        $apellidoPaterno = isset($tutor['apellidoPaterno']) ? htmlspecialchars($tutor['apellidoPaterno']) : '';
        $apellidoMaterno = isset($tutor['apellidoMaterno']) ? htmlspecialchars($tutor['apellidoMaterno']) : '';
        $noPersonal = isset($tutor['noPersonal']) ? htmlspecialchars($tutor['noPersonal']) : '';
        $correoInstitucional = isset($tutor['correoInstitucional']) ? htmlspecialchars($tutor['correoInstitucional']) : '';
        $rol = isset($tutor['rol']) ? htmlspecialchars($tutor['rol']) : '';

        if (!$rol) {
            echo "No se encontró el rol del usuario actual.";
            $conn->close();
            exit();
        }

        $regresar = './cerrarSesion.php';
        $academico = 'Académico';
        switch ($rol) {
            case 1:
                $regresar = './administrarProfesor.php';
                $academico = 'Profesor';
                break;
            case 4:
                $regresar = './administrarCoordinador.php';
                $academico = 'Coordinador';
                break;
            case 5:
                $regresar = './administrarJefeCarrera.php';
                $academico = 'Jefe de Carrera';
                break;
            default:
                $regresar = './cerrarSesion.php';
                break;
        }

        $conn->close();
    }

    if (isset($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo "<p class='error'>$error</p>";
        }
        unset($_SESSION['errors']);
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Formulario de Actualización de Profesores</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                margin: 0;
            }
            .buttonGreen {
                background-color: #28AD56;
                color: #fff;
                border: none;
                cursor: pointer;
                padding: auto;
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
                padding: 46px 30px;
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
            
            .widthInput {
                padding: 4px ;
            }
        </style>
    </head>
    <body>
        <div class="header-container">
            <div class="header-left">
                <img src="<?= logo_UV ?>" alt="UV Logo">
                <div class="welcome-message">Bienvenid@ <?php echo $_SESSION['user']; ?></div>
                <button class="buttonsHead" onclick="location.href='<?php echo $regresar; ?>'"><i class="fas fa-arrow-left"></i> Regresar</button>
                <button class="buttonsHead" onclick="location.href='./menuAdministrador.php'"><i class="fas fa-home"></i> Inicio</button>
                <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
            </div>
            <div class="header-right">
                Universidad Veracruzana   
            </div>
        </div>

        <div class="container mt-5">
            <form action="modificarProfesor.php" method="POST" enctype="multipart/form-data" id="form">
                
                <input type="hidden" name="idTutor" value="<?php echo htmlspecialchars($idTutor); ?>">
                <input type="hidden" name="rol" value="<?php echo htmlspecialchars($rol); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="nombre">Nombre de <?php echo $academico; ?>: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de profesor máximo 70 caracteres" maxlength="70" value="<?php echo $nombre?>">
                </div>
                
                <div class="form-group">
                    <label for="paterno">Apellido paterno:</label>
                    <input type="text" class="form-control" id="paterno" name="paterno" placeholder="Apellido paterno máximo 70 caracteres" maxlength="70" value="<?php echo $apellidoPaterno?>">
                </div>

                <div class="form-group">
                    <label for="materno">Apellido materno:</label>
                    <input type="text" class="form-control" id="materno" name="materno" placeholder="Apellido materno máximo 70 caracteres" maxlength="70" value="<?php echo $apellidoMaterno?>">
                </div>

                <div class="form-group">
                    <label for="noPersonal">Número de personal:</label>
                    <input type="text" class="form-control" id="noPersonal" name="noPersonal" placeholder="Número de personal máximo 15 caracteres" maxlength="15" value="<?php echo $noPersonal?>">
                </div>

                <div class="form-group">
                    <label for="correoInstitucional">Correo institucional: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="correoInstitucional" name="correoInstitucional" placeholder="Correo institucional máximo 50 caracteres" maxlength="50" value="<?php echo $correoInstitucional?>">
                    <p class="text-secondary">Ejem: zs12345678@estudiantes.uv.mx | asdw5678@uv.mx</p>
                </div>

                <div class="form-group row">
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-success buttonGreen" id="enviar">Guardar <?php echo $academico; ?></button>
                    </div>
                </div>
            </form>
        </div>
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
        var nombre = $('#nombre').val();
        var paterno = $('#paterno').val();
        var materno = $('#materno').val();
        var noPersonal = $('#noPersonal').val();
        var correoInstitucional = $('#correoInstitucional').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!nombre || !correoInstitucional) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!nombre) $('#nombre').addClass("borderRed");
            if (!correoInstitucional) $('#correoInstitucional').addClass("borderRed");
            
            error = true;
        }

        if (error) {
            return false;
        }

        if (!correoInstitucional.endsWith('@uv.mx') && !correoInstitucional.endsWith('@estudiantes.uv.mx')) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>El correo institucional debe terminar en @uv.mx o @estudiantes.uv.mx.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            $('#correoInstitucional').addClass("borderRed");
            return; 
        }

        $('#nombre, #paterno, #materno, #noPersonal, #correoInstitucional').removeClass("borderRed").addClass("borderGreen");
        
        $("#form").submit();
    }
</script>