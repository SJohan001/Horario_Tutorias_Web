<?php
    define('logo_UV','.\img\UV.png');

    session_start();

    $rolesPermitidos = [3];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }

    if(isset($_SESSION['message'])) {   
        $message = $_SESSION['message'];
    }

    $user = $_SESSION['user'];

    require './libs/phpspreadsheet/vendor/autoload.php';
    require_once __DIR__ . '/libs/phpspreadsheet/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/IOFactory.php';

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
    use PhpOffice\PhpSpreadsheet\IOFactory;


    try {
        include ('./conection.php');
        $conn = conectiondb();

        
    } catch (\Throwable $th) {
        $message = "error";
        session_start();
        $_SESSION['message'] = $message;
        header("Location: ./tutorTutorias.php");
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Importación de Datos</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
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
            footer {
                margin-top: auto;
                text-align: center;
                padding: 10px;
                background-color: #f1f1f1;
                position: relative;
                bottom: 0;
                left: 0;
                right: 0;
            }

            h2 {
                border: 1px solid black;
                color: white;
                text-align: center;
                margin-top: 20px;
                padding: 15px;
                margin-bottom: 30px;
                background-color: #28AD56;
            }

            .form-section {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 30px;
                background-color: #18529D;
                color: white;
            }

            .form-section div {
                width: 100%;
            }

            .button-container {
                text-align: center;
                margin-top: 40px;
                font-size: 20px;
            }

            .button-import {
                font-size: 18px;
                padding: 15px 30px;
                background-color: #28AD56; 
                font-weight: bold;
                color: white; 
                border: none; 
                border-radius: 5px; 
                cursor: pointer; 
            }

            .button-import:hover {
                background-color: #45a049; 
            }

            body {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }

            .container {
                margin-top: 20px;
            }

            .div-border {
                border: 1px solid black;
                padding: 60px;
            }
        </style>
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

        
        <div class="container mt-5">
            <h2>Importar Datos</h2>

            <form action="importarDatos.php" method="post" enctype="multipart/form-data">
                <div class="form-section">
                    <div class="div-border">
                        <h4>Importar Datos de Tutor</h4>
                        <div class="form-group">
                            <label for="archivo_tutor">Archivo XLSX para Tutores:</label>
                            <input type="file" class="form-control-file" id="archivo_tutor" name="archivo_tutor" accept=".csv, .xlsx">
                            <button type="button" class="btn btn-link mt-2" style="color:#000;" onclick="location.href='./uploads/importe_ejemplo/ejemplo_tutor.xlsx'">Descargar Ejemplo</button>
                        </div>
                    </div>

                    <div class="div-border">
                        <h4>Importar Datos de Tutorados</h4>
                        <div class="form-group">
                            <label for="archivo_tutorado">Archivo XLSX para Tutorados:</label>
                            <input type="file" class="form-control-file" id="archivo_tutorado" name="archivo_tutorado" accept=".csv, .xlsx">
                            <button type="button" class="btn btn-link mt-2" style="color:#000;" onclick="location.href='./uploads/importe_ejemplo/ejemplo_tutorado.xlsx'">Descargar Ejemplo</button>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="div-border">
                        <h4>Importar Datos de Carreras</h4>
                        <div class="form-group">
                            <label for="archivo_carrera">Archivo XLSX para Carreras:</label>
                            <input type="file" class="form-control-file" id="archivo_carrera" name="archivo_carrera" accept=".csv, .xlsx">
                            <button type="button" class="btn btn-link mt-2" style="color:#000;" onclick="location.href='./uploads/importe_ejemplo/ejemplo_carrera.xlsx'">Descargar Ejemplo</button>
                        </div>
                    </div>

                    <div class="div-border">
                        <h4>Importar Datos de Periodos Escolares</h4>
                        <div class="form-group">
                            <label for="archivo_periodo">Archivo XLSX para Periodos Escolares:</label>
                            <input type="file" class="form-control-file" id="archivo_periodo" name="archivo_periodo" accept=".csv, .xlsx">
                            <button type="button" class="btn btn-link mt-2" style="color:#000;" onclick="location.href='./uploads/importe_ejemplo/ejemplo_periodo.xlsx'">Descargar Ejemplo</button>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="div-border">
                        <h4>Importar Datos de Experiencias Educativas</h4>
                        <div class="form-group">
                            <label for="archivo_experienciaE">Archivo XLSX para Experiencias Educativas:</label>
                            <input type="file" class="form-control-file" id="archivo_experienciaE" name="archivo_experienciaE" accept=".csv, .xlsx">
                            <button type="button" class="btn btn-link mt-2" style="color:#000;" onclick="location.href='./uploads/importe_ejemplo/ejemplo_experiencia_educativa.xlsx'">Descargar Ejemplo</button>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="submit" class="btn button-import">Importar Datos</button>
                </div>
            </form>
        </div>

        <?php
            $mensajeImportacion = "";
            $importacionExitosaTutor = false;
            $importacionExitosaTutorado = false;
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if (isset($_FILES['archivo_tutor']) && $_FILES['archivo_tutor']['error'] == 0) {
                    $fileType = IOFactory::identify($_FILES['archivo_tutor']['tmp_name']);
                    $reader = IOFactory::createReader($fileType);
                    $spreadsheet = $reader->load($_FILES['archivo_tutor']['tmp_name']);
                    $sheet = $spreadsheet->getActiveSheet();

                    // Seleccion del numero de fila en que comenzará la lectura
                    $startRow = 2;
                    $highestRow = $sheet->getHighestRow();

                    // Contador de filas vacías
                    $filasVacias = 0; 
                    $maximoFilasVacias = 5;

                    for ($rowIndex = $startRow; $rowIndex <= $highestRow + 1; $rowIndex++) {

                        $nombre = !is_null($sheet->getCell("A" . $rowIndex)->getValue()) ? trim($sheet->getCell("A" . $rowIndex)->getValue()) : '';  //A2
                        $apellidoPaterno = !is_null($sheet->getCell("B" . $rowIndex)->getValue()) ? trim($sheet->getCell("B" . $rowIndex)->getValue()) : '';  //B2
                        $apellidoMaterno = !is_null($sheet->getCell("C" . $rowIndex)->getValue()) ? trim($sheet->getCell("C" . $rowIndex)->getValue()) : '';  //C2
                        $noPersonal = !is_null($sheet->getCell("D" . $rowIndex)->getValue()) ? trim($sheet->getCell("D" . $rowIndex)->getValue()) : '';  //D2 
                        $correoInstitucional = !is_null($sheet->getCell("E" . $rowIndex)->getValue()) ? trim($sheet->getCell("E" . $rowIndex)->getValue()) : '';  //E2
                        $idRol = 1; // Rol de tutor

                        $apellidoPaterno = !empty($apellidoPaterno) ? trim($apellidoPaterno) : null;
                        $apellidoMaterno = !empty($apellidoMaterno) ? trim($apellidoMaterno) : null;
                        $noPersonal = !empty($noPersonal) ? trim($noPersonal) : null;
                        
                        if ($nombre === '' || $correoInstitucional === '') {
                            $filasVacias++;
                                                        
                            if ($filasVacias >= $maximoFilasVacias) {
                                break;
                            }
                        } else {
                            $filasVacias = 0;

                            try {
                                $sesionNueva = $conn->prepare("INSERT INTO sesion (correoInstitucional, rol) VALUES (?, ?)");
                                $sesionNueva->bind_param("ss", $correoInstitucional, $idRol);
                                if ($sesionNueva->execute()) {
                                    $consultaIdSesionUltima = $conn->prepare("SELECT MAX(s.idSesion) AS sesion FROM sesion s WHERE s.correoInstitucional = ?");
                                    $consultaIdSesionUltima->bind_param("s", $correoInstitucional);
                                    $consultaIdSesionUltima->execute();
                                    $result = $consultaIdSesionUltima->get_result();
                                    $objetoSesion = $result->fetch_assoc();
                                    $idSesion = $objetoSesion['sesion'];
                                    $consultaIdSesionUltima->close();
                
                                    $tutorNuevo = $conn->prepare("INSERT INTO tutor (nombre, apellidoPaterno, apellidoMaterno, noPersonal, correoInstitucional, sesion) VALUES (?, ?, ?, ?, ?, ?)");
                                    $tutorNuevo->bind_param("ssssss", $nombre, $apellidoPaterno, $apellidoMaterno, $noPersonal, $correoInstitucional, $idSesion);
                                    $tutorNuevo->execute();
                                }
                            } catch (mysqli_sql_exception $ex) {
                                error_log("Error en la base de datos: " . $ex->getMessage());
                            }
                        }
                    }
                    $importacionExitosaTutor = true;
                }
            
                if (isset($_FILES['archivo_tutorado']) && $_FILES['archivo_tutorado']['error'] == 0) {
                    $fileType = IOFactory::identify($_FILES['archivo_tutorado']['tmp_name']);
                    $reader = IOFactory::createReader($fileType);
                    $spreadsheet = $reader->load($_FILES['archivo_tutorado']['tmp_name']);
                    $sheet = $spreadsheet->getActiveSheet();

                    // Seleccion del numero de fila en que comenzará la lectura
                    $startRow = 2;
                    $highestRow = $sheet->getHighestRow();

                    // Contador de filas vacías
                    $filasVacias = 0; 
                    $maximoFilasVacias = 5;

                    for ($rowIndex = $startRow; $rowIndex <= $highestRow + 1; $rowIndex++) {

                        $nombre = !is_null($sheet->getCell("A" . $rowIndex)->getValue()) ? trim($sheet->getCell("A" . $rowIndex)->getValue()) : '';  //A2
                        $apellidoPaterno = !is_null($sheet->getCell("B" . $rowIndex)->getValue()) ? trim($sheet->getCell("B" . $rowIndex)->getValue()) : '';  //B2
                        $apellidoMaterno = !is_null($sheet->getCell("C" . $rowIndex)->getValue()) ? trim($sheet->getCell("C" . $rowIndex)->getValue()) : '';  //C2
                        $matricula = !is_null($sheet->getCell("D" . $rowIndex)->getValue()) ? trim($sheet->getCell("D" . $rowIndex)->getValue()) : '';  //D2
                        $correoInstitucional = !is_null($sheet->getCell("E" . $rowIndex)->getValue()) ? trim($sheet->getCell("E" . $rowIndex)->getValue()) : '';  //E2
                        $idCarrera = !is_null($sheet->getCell("F" . $rowIndex)->getValue()) ? trim($sheet->getCell("F" . $rowIndex)->getValue()) : '';  //F2
                        $idTutor = !is_null($sheet->getCell("G" . $rowIndex)->getValue()) ? trim($sheet->getCell("G" . $rowIndex)->getValue()) : '';  //G2
                        $idRol = 2;  // Rol de tutorado

                        $apellidoPaterno = !empty($apellidoPaterno) ? trim($apellidoPaterno) : null;
                        $apellidoMaterno = !empty($apellidoMaterno) ? trim($apellidoMaterno) : null;
                        $idTutor = !empty($idTutor) ? trim($idTutor) : null;

                        if ($nombre === '' || $correoInstitucional === '' || $idCarrera === '') {
                            $filasVacias++;
                                                        
                            if ($filasVacias >= $maximoFilasVacias) {
                                break;
                            }
                        } else {
                            $filasVacias = 0;

                            try {
                                $sesionNueva = $conn->prepare("INSERT INTO sesion (correoInstitucional, rol) VALUES (?, ?)");
                                $sesionNueva->bind_param("ss", $correoInstitucional, $idRol);
                                if ($sesionNueva->execute()) {
                                    $consultaIdSesionUltima = $conn->prepare("SELECT MAX(s.idSesion) AS sesion FROM sesion s WHERE s.correoInstitucional = ?");
                                    $consultaIdSesionUltima->bind_param("s", $correoInstitucional);
                                    $consultaIdSesionUltima->execute();
                                    $result = $consultaIdSesionUltima->get_result();
                                    $objetoSesion = $result->fetch_assoc();
                                    $idSesion = $objetoSesion['sesion'];
                                    $consultaIdSesionUltima->close();
                        
                                    $tutoradoNuevo = $conn->prepare("INSERT INTO tutorado (nombre, apellidoPaterno, apellidoMaterno, matricula, correoInstitucional, carrera, tutor, sesion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                    $tutoradoNuevo->bind_param("ssssssss", $nombre, $apellidoPaterno, $apellidoMaterno, $matricula, $correoInstitucional, $idCarrera, $idTutor, $idSesion);
                                    $tutoradoNuevo->execute();
                                }
                            } catch (mysqli_sql_exception $ex) {
                                error_log("Error en la base de datos: " . $ex->getMessage());
                            }
                        }
                    }
                    $importacionExitosaTutorado = true;
                }

                if (isset($_FILES['archivo_carrera']) && $_FILES['archivo_carrera']['error'] == 0) {
                    $fileType = IOFactory::identify($_FILES['archivo_carrera']['tmp_name']);
                    $reader = IOFactory::createReader($fileType);
                    $spreadsheet = $reader->load($_FILES['archivo_carrera']['tmp_name']);
                    $sheet = $spreadsheet->getActiveSheet();

                    // Seleccion del numero de fila en que comenzará la lectura
                    $startRow = 2;
                    $highestRow = $sheet->getHighestRow();

                    // Contador de filas vacías
                    $filasVacias = 0; 
                    $maximoFilasVacias = 5;

                    for ($rowIndex = $startRow; $rowIndex <= $highestRow + 1; $rowIndex++) {

                        $nombre = !is_null($sheet->getCell("A" . $rowIndex)->getValue()) ? trim($sheet->getCell("A" . $rowIndex)->getValue()) : '';  //A2

                        if ($nombre === '') {
                            $filasVacias++;
                                                        
                            if ($filasVacias >= $maximoFilasVacias) {
                                break;
                            }
                        } else {
                            $filasVacias = 0;

                            try {
                                $carreraNueva = $conn->prepare("INSERT INTO carrera (nombre) VALUES (?)");
                                $carreraNueva->bind_param("s", $nombre);
                                $carreraNueva->execute();
                            } catch (mysqli_sql_exception $ex) {
                                error_log("Error en la base de datos: " . $ex->getMessage());
                            }
                        }
                    }
                    $importacionExitosaCarrera = true;
                }

                if (isset($_FILES['archivo_periodo']) && $_FILES['archivo_periodo']['error'] == 0) {
                    $fileType = IOFactory::identify($_FILES['archivo_periodo']['tmp_name']);
                    $reader = IOFactory::createReader($fileType);
                    $spreadsheet = $reader->load($_FILES['archivo_periodo']['tmp_name']);
                    $sheet = $spreadsheet->getActiveSheet();

                    // Seleccion del numero de fila en que comenzará la lectura
                    $startRow = 2;
                    $highestRow = $sheet->getHighestRow();

                    // Contador de filas vacías
                    $filasVacias = 0; 
                    $maximoFilasVacias = 5;

                    for ($rowIndex = $startRow; $rowIndex <= $highestRow + 1; $rowIndex++) {

                        $nombre = !is_null($sheet->getCell("A" . $rowIndex)->getValue()) ? trim($sheet->getCell("A" . $rowIndex)->getValue()) : '';  //A2
                        $actual = !is_null($sheet->getCell("B" . $rowIndex)->getValue()) ? trim($sheet->getCell("B" . $rowIndex)->getValue()) : '';  //B2

                        if ($nombre === '') {
                            $filasVacias++;
                                                        
                            if ($filasVacias >= $maximoFilasVacias) {
                                break;
                            }
                        } else {
                            $filasVacias = 0;

                            try {
                                $periodoNuevo = $conn->prepare("INSERT INTO periodo (nombre, actual) VALUES (?, ?)");
                                $periodoNuevo->bind_param("ss", $nombre, $actual);
                                if ($periodoNuevo->execute()) {
                                    if($actual == 1){
                                        $idPeriodo = $conn->insert_id;
                                        $sqlActual = "UPDATE periodo SET actual = 0 
                                                WHERE idPeriodo != ?";
                                        $periodoActual = $conn->prepare($sqlActual);
                                        $periodoActual->bind_param('s', $idPeriodo);
                                        $periodoActual->execute();
                                    }
                                }
                            } catch (mysqli_sql_exception $ex) {
                                error_log("Error en la base de datos: " . $ex->getMessage());
                            }
                        }
                    }
                    $importacionExitosaPeriodo = true;
                }

                if (isset($_FILES['archivo_experienciaE']) && $_FILES['archivo_experienciaE']['error'] == 0) {
                    $fileType = IOFactory::identify($_FILES['archivo_experienciaE']['tmp_name']);
                    $reader = IOFactory::createReader($fileType);
                    $spreadsheet = $reader->load($_FILES['archivo_experienciaE']['tmp_name']);
                    $sheet = $spreadsheet->getActiveSheet();

                    // Seleccion del numero de fila en que comenzará la lectura
                    $startRow = 2;
                    $highestRow = $sheet->getHighestRow();

                    // Contador de filas vacías
                    $filasVacias = 0; 
                    $maximoFilasVacias = 5;

                    for ($rowIndex = $startRow; $rowIndex <= $highestRow + 1; $rowIndex++) {

                        $nombre = !is_null($sheet->getCell("A" . $rowIndex)->getValue()) ? trim($sheet->getCell("A" . $rowIndex)->getValue()) : '';  //A2
                        $nrc = !is_null($sheet->getCell("B" . $rowIndex)->getValue()) ? trim($sheet->getCell("B" . $rowIndex)->getValue()) : '';  //B2
                        $profesor = !is_null($sheet->getCell("B" . $rowIndex)->getValue()) ? trim($sheet->getCell("B" . $rowIndex)->getValue()) : '';  //C2
                        $programaEducativo = !is_null($sheet->getCell("B" . $rowIndex)->getValue()) ? trim($sheet->getCell("B" . $rowIndex)->getValue()) : '';  //D2

                        if ($nombre === '' || $nrc === '' || $profesor === '' || $programaEducativo === '') {
                            $filasVacias++;
                                                        
                            if ($filasVacias >= $maximoFilasVacias) {
                                break;
                            }
                        } else {
                            $filasVacias = 0;

                            try {
                                $experienciaEducativaNueva = $conn->prepare("INSERT INTO experiencia_educativa (nombre, nrc, profesor, programaEducativo) VALUES (?, ?, ?, ?)");
                                $experienciaEducativaNueva->bind_param("ssii", $nombre, $nrc, $profesor, $programaEducativo);
                                $experienciaEducativaNueva->execute();
                            } catch (mysqli_sql_exception $ex) {
                                error_log("Error en la base de datos: " . $ex->getMessage());
                            }
                        }
                    }
                    $importacionExitosaExperienciaEducativa = true;
                }
                
                if ($importacionExitosaTutor && $importacionExitosaTutorado && $importacionExitosaCarrera && $importacionExitosaPeriodo && $importacionExitosaExperienciaEducativa) {
                    $mensajeImportacion = "<div class='alert alert-success'>Importación de datos completada con éxito.</div>";
                } elseif ($importacionExitosaTutor) {
                    $mensajeImportacion = "<div class='alert alert-success'>Importación de tutores completada con éxito.</div>";
                } elseif ($importacionExitosaTutorado) {
                    $mensajeImportacion = "<div class='alert alert-success'>Importación de tutorados completada con éxito.</div>";
                } elseif ($importacionExitosaCarrera){
                    $mensajeImportacion = "<div class='alert alert-success'>Importación de carreras completada con éxito.</div>";
                } elseif ($importacionExitosaPeriodo){
                    $mensajeImportacion = "<div class='alert alert-success'>Importación de periodos completada con éxito.</div>";
                } elseif ($importacionExitosaExperienciaEducativa){
                    $mensajeImportacion = "<div class='alert alert-success'>Importación de experiencias educativas completada con éxito.</div>";
                } else {
                    $mensajeImportacion = "<div class='alert alert-danger'>No se procesó ningún archivo.</div>";
                }
            }
       ?>

        <footer>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo $mensajeImportacion;
            }
            ?>
            @ Universidad Veracruzana
        </footer>
                
        <?php $conn->close(); ?>
    </body>
</html>