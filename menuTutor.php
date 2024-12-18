<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    $rolesPermitidos = [1];
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

    $user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Menú de Administrador UV</title>
        <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
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
            width: 300px;
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

        .button-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px; 
            justify-items: center; 
            align-items:center; 
            margin-top: 40px;
            font-size: 20px;
        }

        .button-style {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 400px; 
            height: 150px; 
            font-size: 25px; 
            font-weight: bold;
            color: white;
            border: 1px solid #28AD56;
            border-radius: 5px;
            cursor: pointer;
            background: #28AD56;
            background-position: 20px center;
            background-size: 80px; 
            background-repeat: no-repeat;
            text-align: right;
            padding-left: 120px; 
            position: relative;
            transition: background 0.3s linear;
        }

        .button-style img {
            opacity: 0.3;
            position: absolute;
            top: 50%;
            left: 20px;
            transform: translateY(-50%);
            z-index: 1;
            width: 80px;
            height: auto;
            transition: transform 0.3s linear;
        }

        .button-style:hover img {
            transform: translateY(-50%) scale(0.8); 
        }

        .button-style:hover {
            background: #45a049;
        }

        footer {
            margin-top: auto;
            text-align: center;
            padding: 10px;
            background-color: #f1f1f1;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-left">
            <img src="<?= logo_UV ?>" alt="UV Logo">
            <div class="welcome-message">Bienvenid@ <?php echo $_SESSION['user']; ?></div>
            <button class="buttonsHead" onclick="location.href='./menuTutor.php'"><i class="fas fa-home"></i> Inicio</button>
            <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </div>
        <div class="header-right">
            Universidad Veracruzana   
        </div>
    </div>
    <div class="button-container">
        <button class="button-style" style="background-image: url('./img/importar-icon.png');" onclick="location.href='./tutorTutorias.php'"> Registro de Sesión de Tutorías</button>
        <button class="button-style" style="background-image: url('./img/carrera-icon.png');" onclick="location.href='./administrarReporteTutoria.php'"> Registro de Reporte de Tutorías</button>
    </div>
</body>
<footer>© Universidad Veracruzana</footer>
</html>