<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $csrf_token = $_SESSION['csrf_token'];

    $rolesPermitidos = [1,4];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }

    $user = $_SESSION['user'];
    $perfilActual = $_SESSION['correoInstitucional'];

    if (isset($_GET['idReporte'])) {
        $idReporte = $_GET['idReporte'];
    } elseif (isset($_POST['idReporte'])) {
        $idReporte = $_POST['idReporte'];
    } else {
        echo "Error: No se recibió el ID del reporte.";
        exit();
    }

    if($idReporte){
        include ('./conection.php');
        $conn = conectiondb();
        
        $carreraConsulta = $conn->prepare("SELECT c.idCarrera, 
                                                c.nombre 
                                            FROM carrera c");
        $carreraConsulta->execute();
        $arrayCarreras = $carreraConsulta->get_result();
        $carreraConsulta->close();

        $periodoConsulta = $conn->prepare("SELECT p.idPeriodo, 
                                                p.nombre 
                                            FROM periodo p");
        $periodoConsulta->execute();
        $arrayPeriodos = $periodoConsulta->get_result();
        $periodoConsulta->close();

        $reporteModificar = $conn->prepare("SELECT 
                                                    rt.periodo, 
                                                    tc.carrera, 
                                                    tc.tutor, 
                                                    rt.fechaInicioTutoria, 
                                                    rt.fechaFinTutoria, 
                                                    rt.numTutoria, 
                                                    rt.numAsistencia, 
                                                    rt.numRiesgo, 
                                                    rt.comentario 
                                            FROM reporte_tutoria rt 
                                            INNER JOIN carrera_tutor tc ON tc.idCarreraTutor = rt.carreraTutor 
                                            WHERE rt.idReporte = ?");
        $reporteModificar->bind_param("i", $idReporte);
        $reporteModificar->execute();
        $result = $reporteModificar->get_result();
        $reporte = $result->fetch_assoc();
        $reporteModificar->close();

        $problematicasModificar = $conn->prepare("  SELECT 
                                                            pa.idProblematicaAcademica, 
                                                            pa.experienciaEducativa, 
                                                            pa.problematica, 
                                                            pa.numAlumnos, 
                                                            pa.estado, 
                                                            pa.otro 
                                                    FROM problematica_academica pa 
                                                    WHERE pa.reporte = ?");
        $problematicasModificar->bind_param("i", $idReporte);
        $problematicasModificar->execute();
        $resultProblematica = $problematicasModificar->get_result();
        $problematicasModificar->close();

        $periodo = isset($reporte['periodo']) ? htmlspecialchars($reporte['periodo']) : '';
        $carrera = isset($reporte['carrera']) ? htmlspecialchars($reporte['carrera']) : '';
        $tutor = isset($reporte['tutor']) ? htmlspecialchars($reporte['tutor']) : '';
        $fechaInicioTutoria = isset($reporte['fechaInicioTutoria']) ? htmlspecialchars($reporte['fechaInicioTutoria']) : '';
        $fechaFinTutoria = isset($reporte['fechaFinTutoria']) ? htmlspecialchars($reporte['fechaFinTutoria']) : '';
        $numTutoria = isset($reporte['numTutoria']) ? htmlspecialchars($reporte['numTutoria']) : '';
        $numAsistencia = isset($reporte['numAsistencia']) ? htmlspecialchars($reporte['numAsistencia']) : '';
        $numRiesgo = isset($reporte['numRiesgo']) ? htmlspecialchars($reporte['numRiesgo']) : '';
        $comentario = isset($reporte['comentario']) ? htmlspecialchars($reporte['comentario']) : '';
        
        $conn->close();
    }

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

    $menu = './cerrarSesion.php';

    switch ($_SESSION['rol']) {
        case 1:
            $menu = './menuTutor.php';
            break;
        case 4:
            $menu = './menuCoordinador.php';
            break;
        default:
            $menu = './cerrarSesion.php';
            break;
    }
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Formulario de Actualización de Reportes de Tutoría</title>
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

            .buttonNew {
                background-color: #28AD56;
                color: white;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
            }
            .buttonNew:hover {
                opacity: 0.8;
            }
            table {
                width: 60%;
                margin: 20px auto;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
            }
            th {
                background-color: #18529D;
                color: white;
            }
            .autoWidthColumn {
                white-space: nowrap;
                width: auto;
                text-align: center;
            }
            .action-buttons {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                width: auto;
            }
            .action-buttons button {
                background-color: #28AD56;
                color: white;
                border: none;
                padding: 5px 10px;
                cursor: pointer;
                flex-grow: 1;
            }
            .action-buttons button.delete {
                background-color: #dc3545;
            }
            .action-buttons button:hover {
                opacity: 0.8;
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
        </style>
    </head>
    <body>

        <div class="header-container">
            <div class="header-left">
                <img src="<?= logo_UV ?>" alt="UV Logo">
                <div class="welcome-message">Bienvenid@ <?php echo $_SESSION['user']; ?></div>
                <button class="buttonsHead" onclick="location.href='./administrarReporteTutoria.php'"><i class="fas fa-arrow-left"></i> Regresar</button>
                <button class="buttonsHead" onclick="location.href='<?php echo $menu; ?>'"><i class="fas fa-home"></i> Inicio</button>
                <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
            </div>
            <div class="header-right">
                Universidad Veracruzana   
            </div>
        </div>

        <div class="container mt-5">
            <form action="modificarReporteTutoria.php" method="post" enctype="multipart/form-data" id="form">

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="idReporte" value="<?php echo htmlspecialchars($idReporte); ?>">
                <input type="hidden" name="carreraConsulta" value="<?php echo htmlspecialchars($carrera); ?>">
                <input type="hidden" name="idTutor" value="<?php echo htmlspecialchars($tutor); ?>">

                <div class="form-group">
                    <label for="carrera">Carrera: <span class="text-danger">*</span></label>
                    <select class="form-control" id="carrera" name="carrera" required>
                        <option value="" disabled selected>-----Selecciona la carrera-----</option>
                        <?php while($carreras = $arrayCarreras->fetch_assoc()) { 
                                $selected = ($carrera == $carreras['idCarrera']) ? 'selected' : '';
                                echo "<option value='{$carreras['idCarrera']}' $selected>{$carreras['nombre']}</option>";
                              } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="periodo">Periodo: <span class="text-danger">*</span></label>
                    <select class="form-control" id="periodo" name="periodo" required>
                        <option value="" disabled selected>-----Selecciona el periodo-----</option>
                        <?php while($periodos = $arrayPeriodos->fetch_assoc()) {
                                $selected = ($periodo == $periodos['idPeriodo']) ? 'selected' : '';
                                echo "<option value='{$periodos['idPeriodo']}' $selected>{$periodos['nombre']}</option>";
                              } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numTutoria">Número de Tutoría: <span class="text-danger">*</span></label>
                    <select class="form-control numTutoria" name="numTutoria" id="numTutoria" required>
                        <option value="" disabled selected>-----Selecciona el numero de tutoría-----</option>
                        <option value="1" <?= ($numTutoria == 1) ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= ($numTutoria == 2) ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= ($numTutoria == 3) ? 'selected' : '' ?>>3</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fechaInicio">Fecha de Inicio: <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" placeholder="dd/mm/aaaa" value="<?php echo $fechaInicioTutoria?>">
                </div>

                <div class="form-group">
                    <label for="fechaFin">Fecha de Fin: <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" placeholder="dd/mm/aaaa" value="<?php echo $fechaFinTutoria?>">
                </div>

                <div class="form-group">
                    <label for="numAsistencias">Número de alumnos que asistieron: <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="numAsistencias" name="numAsistencias" placeholder="Número total de asistencias de tutorados" min="0" step="1" value="<?php echo $numAsistencia?>">
                </div>

                <div class="form-group">
                    <label for="numRiesgo">Número de alumnos en riesgo: <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="numRiesgo" name="numRiesgo" placeholder="Número total de tutorados en riesgo" min="0" step="1" value="<?php echo $numRiesgo?>">
                </div>

                <div class="form-group">
                    <label>Problemática académica:
                        <div>
                            <label>
                                <input type="radio" id="problematica" name="tipo" value="problematica"> Agregar 
                            </label>
                        </div>
                        <div>
                            <label>
                                <input type="radio" id="ninguno" name="tipo" value="ninguno" checked> Ninguno
                            </label>
                        </div>
                    </label>
                </div>
                
                <div class="problematica-table">
                    <table id="problematicaTable">
                        <thead>
                            <tr>
                                <th class="autoWidthColumn">Experiencia educativa <span class="text-danger" style="font-size: 25px;"> *</span></th>
                                <th class="autoWidthColumn">Profesor <span class="text-danger" style="font-size: 25px;">*</span></th>
                                <th class="autoWidthColumn">Problemática <span class="text-danger" style="font-size: 25px;">*</span></th>
                                <th class="autoWidthColumn">Número de alumnos <span class="text-danger" style="font-size: 25px;">*</span></th>
                                <th class="autoWidthColumn">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if ($resultProblematica->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $idProblematicaAcademica = $row['idProblematicaAcademica'] ?? '';
                                        $experienciaEducativa = $row['experienciaEducativa'] ?? 'Sin experiencia educativa';
                                        $problematica = $row['problematica'] ?? 'Sin problemática';
                                        $numAlumnos = $row['numAlumnos'] ?? 'Sin número de alumnos';
                                        $otro = $row['otro'] ?? 'Sin otra problematica';

                                        echo "<tr>
                                                <td>{$experienciaEducativa}</td>
                                                <td>{$problematica}</td>
                                                <td>{$numAlumnos}</td>
                                                <td>{$numAlumnos}</td>
                                                <td class='action-buttons autoTable'>
                                                    <button class='edit' data-id-reporte='{$idReporte}'><i class='fas fa-edit'></i></button>
                                                    <button class='delete' data-id-reporte='{$idReporte}' data-csrf-token='{$_SESSION['csrf_token']}'><i class='fas fa-trash-alt'></i></button>
                                                </td>
                                            </tr>";
                                    }
                                } 
                            ?>
                        </tbody>
                    </table>
                    <button type="button" id="agregarFilaBtn" class="btn btn-primary">Agregar problematica</button>
                </div>

                <div class="form-group">
                    <label for="comentario">Comentarios:</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3" placeholder="Máximo 500 caracteres" maxlength="500" value="<?php echo $comentario?>"></textarea>
                </div>

                <div class="form-group row">
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-success buttonGreen" id="enviar">Guardar Reporte de Tutoría</button>
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
        $("#enviar").on("click", function(e) 
        {
            e.preventDefault();
            validarFormulario();
        });
    });
    
    function validarFormulario() 
    {
        var carrera = $('#carrera').val();
        var periodo = $('#periodo').val();
        var numTutoria = $('#numTutoria').val();
        var fechaInicio = $('#fechaInicio').val();
        var fechaFin = $('#fechaFin').val();
        var numAsistencias = $('#numAsistencias').val();
        var numRiesgo = $('#numRiesgo').val();
        var comentario = $('#comentario').val();
        var tutor = $('#tutor').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!carrera || !numTutoria || !periodo || !fechaInicio || !fechaFin || !numAsistencias || !numRiesgo) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!carrera) $('#carrera').addClass("borderRed");
            if (!periodo) $('#periodo').addClass("borderRed");
            if (!numTutoria) $('#numTutoria').addClass("borderRed");
            if (!fechaInicio) $('#fechaInicio').addClass("borderRed");
            if (!fechaFin) $('#fechaFin').addClass("borderRed");
            if (!numAsistencias) $('#numAsistencias').addClass("borderRed");
            if (!numRiesgo) $('#numRiesgo').addClass("borderRed");
            
            error = true;
        }

        if (error) {
            return; 
        }

        if (new Date(fechaFin) < new Date(fechaInicio)) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>La fecha de fin no puede ser menor que la fecha de inicio.</p>',
                showConfirmButton: false,
                timer: 3500
            });
            $('#fechaFin').addClass("borderRed");
            return;
        }

        $('#carrera, #periodo, #numTutoria, #fechaInicio, #fechaFin, #numAsistencias, #numRiesgo').removeClass("borderRed").addClass("borderGreen");

        $("#form").submit();
    }
</script>