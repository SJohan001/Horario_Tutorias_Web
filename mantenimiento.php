<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitio en Mantenimiento</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            overflow: hidden;
        }

        .image-background {
            position: absolute;
            width: 50vw; 
            height: auto;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            filter: blur(8px);
            opacity: 0.7; 
        }

        .maintenance-message {
            position: relative;
            z-index: 1;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
        }

        h1 {
            color: #18529D;
        }

        p {
            color: #333;
        }
    </style>
</head>
<body>
    <img src="./img/UV.png" alt="Mantenimiento" class="image-background">
    
    <div class="maintenance-message">
        <h1>Estamos en mantenimiento</h1>
        <p>Volveremos pronto. Gracias por su paciencia.</p>
    </div>
</body>
</html>
