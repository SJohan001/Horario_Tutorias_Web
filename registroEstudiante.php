<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    if (empty($_SESSION['csrf_token'])) {
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

    $user = $_SESSION['user'];

    include ('./conection.php');
    $conn = conectiondb();
    
    $carrerasConsulta = $conn->prepare("SELECT c.idCarrera, c.nombre 
                                        FROM carrera c");
    $carrerasConsulta->execute();
    $arrayCarreras = $carrerasConsulta->get_result();
    $carrerasConsulta->close();

    $tutoresConsulta = $conn->prepare("SELECT t.idTutor, 
                                              CONCAT(t.nombre, ' ', COALESCE(t.apellidoPaterno, ''), ' ', COALESCE(t.apellidoMaterno, '')) AS tutorNombre 
                                        FROM tutor t");
    $tutoresConsulta->execute();
    $arrayTutores = $tutoresConsulta->get_result();
    $tutoresConsulta->close();

    if (isset($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo "<p class='error'>$error</p>";
        }
        unset($_SESSION['errors']);
    }
    
    if (isset($_SESSION['message'])) {
        echo "<p class='success'>{$_SESSION['message']}</p>";
        unset($_SESSION['message']);
    }    
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Formulario de Registro de Estudiantes</title>
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

            footer {
                margin-top: auto;
                text-align: center;
                padding: 10px;
                background-color: #f1f1f1;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
            }
        </style>
    </head>
    <body>

        <div class="header-container">
            <div class="header-left">
                <img src="<?= logo_UV ?>" alt="UV Logo">
                <div class="welcome-message">Bienvenid@ <?php echo $_SESSION['user']; ?></div>
                <button class="buttonsHead" onclick="location.href='./administrarEstudiante.php'"><i class="fas fa-arrow-left"></i> Regresar</button>
                <button class="buttonsHead" onclick="location.href='./menuAdministrador.php'"><i class="fas fa-home"></i> Inicio</button>
                <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
            </div>
            <div class="header-right">
                Universidad Veracruzana   
            </div>
        </div>

        <div class="container mt-5">
            <form action="crearEstudiante.php" method="post" enctype="multipart/form-data" id="form">

                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label for="nombre">Nombre de estudiante: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de estudiante máximo 70 caracteres" maxlength="70">
                </div>
                
                <div class="form-group">
                    <label for="paterno">Apellido paterno:</label>
                    <input type="text" class="form-control" id="paterno" name="paterno" placeholder="Apellido paterno máximo 70 caracteres" maxlength="70">
                </div>

                <div class="form-group">
                    <label for="materno">Apellido materno:</label>
                    <input type="text" class="form-control" id="materno" name="materno" placeholder="Apellido materno máximo 70 caracteres" maxlength="70">
                </div>

                <div class="form-group">
                    <label for="noPersonal">Matrícula: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matricula" name="matricula" placeholder="Matrícula máximo 9 caracteres" maxlength="9">
                    <p class="text-secondary">Ejem: S12345678 </p>
                </div>

                <div class="form-group">
                    <label for="carrera">Carrera: <span class="text-danger">*</span></label>
                    <select class="form-control" id="carrera" name="carrera" required>
                        <option value="" disabled selected>-----Selecciona una carrera-----</option>
                        <?php while($carreras = $arrayCarreras->fetch_assoc()) { ?>
                            <option value="<?php echo $carreras['idCarrera']; ?>"><?php echo $carreras['nombre']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="correoInstitucional">Correo institucional: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="correoInstitucional" name="correoInstitucional" placeholder="Correo institucional máximo 45 caracteres" maxlength="45">
                    <p class="text-secondary">Ejem: zs12345678@estudiantes.uv.mx</p>
                </div>

                <div class="form-group">
                    <label for="tutor">Tutor asignado:</label>
                    <select class="form-control" id="tutor" name="tutor" required>
                        <option value="" disabled selected>-----Selecciona un tutor asignado al estudiante-----</option>
                        <?php while($tutores = $arrayTutores->fetch_assoc()) { ?>
                            <option value="<?php echo $tutores['idTutor']; ?>"><?php echo $tutores['tutorNombre']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group row">
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-success buttonGreen" id="enviar">Guardar Estudiante</button>
                    </div>
                </div>
            </form>
        </div>
        <footer>
            @ Universidad Veracruzana
        </footer>
    </body>
</html>


<script>
    $(document).ready(function() 
    {   
        $("#carrera").select2();
        $("#tutor").select2();
        $("#enviar").on("click", function(e) 
        {
            e.preventDefault();
            validarFormulario();
        });
    });
    
    function validarFormulario() 
    {
        var nombre = $('#nombre').val();
        var paterno = $('#paterno').val();
        var materno = $('#materno').val();
        var matricula = $('#matricula').val();
        var carrera = $('#carrera').val();
        var correoInstitucional = $('#correoInstitucional').val();
        var tutor = $('#tutor').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!nombre || !matricula || !carrera || !correoInstitucional) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!nombre) $('#nombre').addClass("borderRed");
            if (!matricula) $('#matricula').addClass("borderRed");
            if (!carrera) $('#carrera').addClass("borderRed");
            if (!correoInstitucional) $('#correoInstitucional').addClass("borderRed");
            
            error = true;
        }

        if (error) {
            return; 
        }

        if (!matricula.startsWith('S')) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>La matrícula debe comenzar con S.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            $('#matricula').addClass("borderRed"); 
            return; 
        }

        if (matricula.length !== 9) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>La matrícula debe tener exactamente 9 caracteres.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            $('#matricula').addClass("borderRed"); 
            return; 
        }

        if (!correoInstitucional.startsWith('z')) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>El correo debe comenzar con z.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            $('#correoInstitucional').addClass("borderRed");
            return; 
        }

        if (!correoInstitucional.startsWith('z' + matricula.toLowerCase())) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>El correo debe comenzar con la matrícula en minúscula.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            $('#correoInstitucional').addClass("borderRed");
            return; 
        }

        if (!correoInstitucional.endsWith('@estudiantes.uv.mx')) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>El correo institucional debe terminar en @estudiantes.uv.mx.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            $('#correoInstitucional').addClass("borderRed");
            return; 
        }

        $('#nombre, #paterno, #materno, #matricula, #carrera, #correoInstitucional, #tutor').removeClass("borderRed").addClass("borderGreen");

        $("#form").submit();
    }
</script>