<?php
    define('logo_UV','.\img\UV-fondoObscuro.png');

    session_start();
    if(isset($_SESSION['message']))
    {
        $message = $_SESSION['message'];
    }

    $mantenimiento = false;

    if ($mantenimiento) {
        header("Location: mantenimiento.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .full-height {
                height: 100vh;
            }

            .image-container {
                position: relative;
                text-align: center;
            }

            .large-image {
                width: 90%;
                max-width: 100%;
                height: auto;
            }

            .gradient-background {
                backdrop-filter: blur(10px);
            }

            .left-section, .right-section {
                padding: 2rem;
                margin: 1rem;
            }

            .form-control {
                font-size: 1.25rem; 
                padding: 0.75rem; 
            }

            .btn {
                font-size: 1rem; 
                padding: 0.5rem 1rem; 
            }

            .btn:hover {
                background-color: #18529D;
                color: #FFFFFF;
            }

            .smaller-btn {
                font-size: 1rem;
                padding: 0.5rem 1rem; 
                background-color: #8a8985;
                margin: 30px 0px 0px 0px;
            }

            .small-input {
                width: 100%; 
                height: auto;
            }

            .label-medium{
                font-size: 1.5rem;
            }

            .loginBig{
                font-size: 3rem;
                font-style: Aileron;
                font-weight: bold;
                color: #18529D;
            }

            .text-custom{
                font-size: 18px;
                font-weight: bold;
                color: #28AD56;
            }

            .input-white::placeholder {
                color: #ffff;
            }

            .input-dark::placeholder {
                color: #18529D;
            }

            .labelDark {
                color: #000;
                font-size: 26px;
                font-weight: bold;
            }
            
            .alert {
                margin-top: 20px;
            }

            .left-section {
                background-color: #18529D;
                color: white;
            }

            .right-section {
                background-color: #e2e0da;
                color: white;
            }

            .right-section input::placeholder {
                color: #fff; 
                opacity: 1;
            }

            .right-section input:not(:focus){
                background-color: #18529D;
                color: #fff;
            }

            .right-section input:focus{
                background-color: #8a8985;
                color: #fff;
            }

            .alert {
                margin-top: 20px;
            }

            body {
                background-color: #18529D;
            }
            footer {
                margin-top: auto;
                text-align: center;
                padding: 10px;
                background-color: #f1f1f1;
            }
            @media only screen and (max-width: 576px) {
                .form 
                {
                    padding-top: 10rem;
                }
            }
            @media (max-width: 576px) {
                .left-section, .right-section {
                    padding: 1rem;
                }

                .loginBig {
                    font-size: 2rem;
                }
            }
        </style>
    </head>
    <body class="container-fluid text-white full-height d-flex justify-content-center align-items-center">
        <div class="row w-100">
            <div class="col-md-5 d-flex justify-content-center left-section">
                <div class="image-container">
                    <img src="<?= logo_UV ?>" alt="UV Logo" class="img-fluid large-image">
                </div>
            </div>
            <div class="col-md-6 d-flex justify-content-center align-items-center right-section">
                <div class="w-100 px-3">
                    <h2 class="text-center loginBig">Registro de Tutorías</h2>
                    <h2 class="text-center loginBig">UV</h2>
                    <br></br>

                    <form action="userSearch.php" method="post" id="form">
                        <div class="mb-3">
                            <label class="labelDark">Usuario:</label>
                            <input type="text" id="user" name="user" class="form-control small-input" placeholder="Ingrese su usuario/matrícula" required>
                            <p class="text-secondary">Ejem: abcgarcia / zS12345678 / GS12345678</p>
                        </div>
                        <div class="mb-3">
                            <label class="labelDark">Contraseña:</label>
                            <input type="password" id="password" name="password" class="form-control text-white small-input input-white" placeholder="Ingrese su contraseña" required>
                        </div>

                        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="aceptarTerminos" class="mr-2" style="transform: scale(1.5);">
                            <label for="aceptarTerminos" class="text-secondary" style="font-size: 1.2rem;">
                                He leído el <a href="https://www.uv.mx/fei/files/2018/10/2023_Aviso-de-privacidad-Integral-TA_ET_FEI.pdf" target="_blank">aviso de privacidad</a>
                            </label>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary btn-block smaller-btn" name="enviar" id="enviar" disabled>Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
    <?php
        if (isset($message)) 
        {
            $text_error = "";
            if($message == "no_exist")
            {
                $text_error = "La cuenta institucional no pertenece a la Facultad de Estadística e Infomática.";
            }

            if($message == "no_login")
            {
                $text_error = "Correo/cuenta o contraseña incorrecta, favor de verificar.";
            }

            if($message == "error")
            {
                $text_error = "Ha ocurrido un problema, intente de nuevo, si el problema persiste contacte a soporte.";
            }
            
            if($text_error !="")
            {
                echo "<script> Swal.fire({
                        icon: 'error',
                        html: '<p>".$text_error."</p>',
                        showConfirmButton: false,
                        timer: 3500
                    }); </script>";
            }
            session_destroy();
        }
    ?>
</html>

<script>
    $(document).ready(function() 
    {
        $('#enviar').prop('disabled', true);

        $('#aceptarTerminos').on('change', function() {
            if ($(this).is(':checked')) {
                $('#enviar').prop('disabled', false);
            } else {
                $('#enviar').prop('disabled', true);
            }
        });

        $("#enviar").on("click", function(e) 
        {
            e.preventDefault();
            validarFormulario();
        })
    
        $('#user').on("change", function() 
        {
            $(this).removeClass("borderGreen borderRed")
        });
    
        $('#password').on("change", function() 
        {
            $(this).removeClass("borderGreen borderRed")
        });
    });

    function validarFormulario() 
    {
        var usuario = $('#user').val();
        var clave = $('#password').val();
        if(usuario.length == 0) 
        {
            Swal.fire({
                icon: 'error',
                html: '<p>Es necesario ingresar su correo/cuenta institucional.</p>',
                showConfirmButton: false,
                timer: 3500
            });
            $('#user').addClass("borderRed");
            return false;
        }
        else 
        {
            $('#user').addClass("borderGreen");   
        }
        if (clave.length == 0) 
        {
            Swal.fire({
                icon: 'error',
                html: '<p>Es necesario ingresar su contraseña.</p>',
                showConfirmButton: false,
                timer: 3500
            });
            $('#password').addClass("borderRed");
            return false;
        }
        else 
        {
            $('#password').addClass("borderGreen");   
        }
        $("#form").submit();
    }
</script>