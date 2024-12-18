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

    include ('./conection.php');
    $conn = conectiondb();

    $profesoresConsulta = $conn->prepare("SELECT t.idTutor, 
                                                 CONCAT(t.nombre, ' ', COALESCE(t.apellidoPaterno, ''), ' ', COALESCE(t.apellidoMaterno, '')) AS profesorNombre 
                                          FROM tutor t");
    $profesoresConsulta->execute();
    $arrayProfesores = $profesoresConsulta->get_result();
    $profesoresConsulta->close();

    $programasConsulta = $conn->prepare("SELECT c.idCarrera, 
                                              c.nombre 
                                        FROM carrera c");
    $programasConsulta->execute();
    $arrayProgramas = $programasConsulta->get_result();
    $programasConsulta->close();

    $conn->close();
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Formulario de Registro de Experiencias Educativas</title>
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
                <button class="buttonsHead" onclick="location.href='./administrarExperienciaEducativa.php'"><i class="fas fa-arrow-left"></i> Regresar</button>
                <button class="buttonsHead" onclick="location.href='./menuAdministrador.php'"><i class="fas fa-home"></i> Inicio</button>
                <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
            </div>
            <div class="header-right">
                Universidad Veracruzana   
            </div>
        </div>

        <div class="container mt-5">
            <form action="crearExperienciaEducativa.php" method="post" enctype="multipart/form-data" id="form">

                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <div class="form-group">
                    <label for="nombre">Nombre de experiencia educativa: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre de experiencia educativa máximo 200 caracteres" maxlength="200">
                </div>

                <div class="form-group">
                    <label for="nrc">NRC de la experiencia educativa: <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="nrc" name="nrc" placeholder="NRC de la experiencia educativa" min="0" step="1">
                </div>

                <div class="form-group">
                    <label for="profesor">Profesor: <span class="text-danger">*</span></label>
                    <select class="form-control" id="profesor" name="profesor" required>
                        <option value="" disabled selected>-----Selecciona el profesor asignado a la experiencia educativa-----</option>
                        <?php while($profesor = $arrayProfesores->fetch_assoc()) { ?>
                            <option value="<?php echo $profesor['idTutor']; ?>"><?php echo $profesor['profesorNombre']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="programa">Programa educativo: <span class="text-danger">*</span></label>
                    <select class="form-control" id="programa" name="programa" required>
                        <option value="" disabled selected>-----Selecciona el programa educativo-----</option>
                        <?php while($programas = $arrayProgramas->fetch_assoc()) { ?>
                            <option value="<?php echo $programas['idCarrera']; ?>"><?php echo $programas['nombre']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group row">
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-success buttonGreen" id="enviar">Guardar Experiencia Educativa</button>
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
        $("#profesor").select2();
        $("#programa").select2();
        $("#enviar").on("click", function(e) 
        {
            e.preventDefault();
            validarFormulario();
        });
    });
    
    function validarFormulario() 
    {
        var nombre = $('#nombre').val();
        var nrc = $('#nrc').val();
        var profesor = $('#profesor').val();
        var programa = $('#profesor').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!nombre || !nrc || !profesor || !programa) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!nombre) $('#nombre').addClass("borderRed");
            if (!nrc) $('#nrc').addClass("borderRed");
            if (!profesor) $('#profesor').addClass("borderRed");
            if (!programa) $('#programa').addClass("borderRed");
            
            
            error = true;
        }

        if (error) {
            return; 
        }

        $('#nombre, #nrc, #profesor, #programa').removeClass("borderRed").addClass("borderGreen");

        $("#form").submit();
    }
</script>