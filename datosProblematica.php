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
    
    $correoInstitucional = $_SESSION['correoInstitucional'];

    $idProblematica = isset($_POST['idProblematica']) ? $_POST['idProblematica'] : '';

    if($idProblematica){
        include ('./conection.php');
        $conn = conectiondb();

        $tipoProblematicaConsulta = $conn->prepare("SELECT tp.idTipoProblematica, 
                                                    tp.nombre  
                                            FROM tipo_problematica tp");
        $tipoProblematicaConsulta->execute();
        $arrayTipoProblematica = $tipoProblematicaConsulta->get_result();
        $tipoProblematicaConsulta->close();
        
        $problematicaModificar = $conn->prepare("SELECT p.idProblematica, 
                                                        p.descripcion,  
                                                        p.tipoProblematica 
                                                 FROM problematica p 
                                                 WHERE p.idProblematica = ?");
        $problematicaModificar->bind_param("i", $idProblematica);
        $problematicaModificar->execute();
        $result = $problematicaModificar->get_result();
        $problematicaAcademica = $result->fetch_assoc();
        $problematicaModificar->close();

        $descripcion = isset($problematicaAcademica['descripcion']) ? htmlspecialchars($problematicaAcademica['descripcion']) : '';
        $tipoProblematica = isset($problematicaAcademica['tipoProblematica']) ? htmlspecialchars($problematicaAcademica['tipoProblematica']) : '';

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
        <title>Formulario de Actualización de Problemáticas Académicas</title>
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
                <button class="buttonsHead" onclick="location.href='./administrarProblematica.php'"><i class="fas fa-arrow-left"></i> Regresar</button>
                <button class="buttonsHead" onclick="location.href='./menuAdministrador.php'"><i class="fas fa-home"></i> Inicio</button>
                <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
            </div>
            <div class="header-right">
                Universidad Veracruzana   
            </div>
        </div>

        <div class="container mt-5">
            <form action="modificarProblematica.php" method="POST" enctype="multipart/form-data" id="form">
                
                <input type="hidden" name="idProblematica" value="<?php echo htmlspecialchars($idProblematica); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="descripcion">Descripción de problemática: <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Descripción de la problemática máximo 500 caracteres" maxlength="500"><?php echo $descripcion?></textarea>
                </div>

                <div class="form-group">
                    <label for="tipoProblematica">Tipo de problemática: <span class="text-danger">*</span></label>
                    <select class="form-control" id="tipoProblematica" name="tipoProblematica" required>
                        <option value="" disabled selected>-----Selecciona un tipo de problemática asignada a la problemática-----</option>
                        <?php while($tiposProblematicas = $arrayTipoProblematica->fetch_assoc()) { 
                            $selected = ($tipoProblematica == $tiposProblematicas['idTipoProblematica']) ? 'selected' : '';
                            echo "<option value='{$tiposProblematicas['idTipoProblematica']}' $selected> {$tiposProblematicas['nombre']}</option>";
                        } ?>
                    </select>
                </div>

                <div class="form-group row">
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-success buttonGreen" id="enviar">Guardar Experiencia Educativa</button>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>

<script>
    $(document).ready(function() 
    {
        $("#tipoProblematica").select2();

        $('#descripcion').on('input', function() {
            if ($(this).val().length >= 500) {
                $(this).val($(this).val().substring(0, 500));
                Swal.fire({
                    title: 'Límite alcanzado',
                    text: 'Has alcanzado el máximo de 500 caracteres para el campo Descripción.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        $("#enviar").on("click", function(e) {
            e.preventDefault();
            validarFormulario();
        });
    });
    
    function validarFormulario() 
    {
        var descripcion = $('#descripcion').val();
        var tipoProblematica = $('#tipoProblematica').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!descripcion || !tipoProblematica) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!descripcion) $('#descripcion').addClass("borderRed");
            if (!tipoProblematica) $('#tipoProblematica').addClass("borderRed");
            
            error = true;
        }

        if (error) {
            return; 
        }

        $('#descripcion, #tipoProblematica').removeClass("borderRed").addClass("borderGreen");

        $("#form").submit();
    }
</script>