<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $csrf_token = $_SESSION['csrf_token'];

    $rolesPermitidos = [1,4];
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

    $idTutoria = isset($_POST['idTutoria']) ? $_POST['idTutoria'] : '';

    if($idTutoria){
        include ('./conection.php');
        $conn = conectiondb();
    
        $carrerasConsulta = $conn->prepare("SELECT c.idCarrera, c.nombre 
                                                FROM carrera c");
        $carrerasConsulta->execute();
        $arrayCarreras = $carrerasConsulta->get_result();
        $carrerasConsulta->close();


        $periodosConsulta = $conn->prepare("SELECT p.idPeriodo, p.nombre 
                                                FROM periodo p");
        $periodosConsulta->execute();
        $arrayPeriodo = $periodosConsulta->get_result();
        $periodosConsulta->close();

        $tutorConsulta = $conn->prepare("SELECT t.idTutor 
                                         FROM tutor t 
                                         WHERE t.correoInstitucional = ?");
        if (!$tutorConsulta) {
            die('Error en la preparación de la consulta de tutor: ' . $conn->error);
        }
        $tutorConsulta->bind_param("s", $correoInstitucional);
        $tutorConsulta->execute();
        $tutorResult = $tutorConsulta->get_result();
        $tutorConsulta->close();

        $objetoTutor = $tutorResult->fetch_assoc();
        $idTutor = $objetoTutor['idTutor'];

        $tutoriaModificar = $conn->prepare("SELECT t.numTutoria, t.modalidad, t.periodoAtencion, t.lugar, t.fecha, t.horaInicio, t.horaFin, t.nota, t.carrera, t.periodo, t.archivo 
                                            FROM tutoria t 
                                            WHERE t.idTutoria = ? AND t.tutor = ?");
        $tutoriaModificar->bind_param("ii", $idTutoria, $idTutor);
        $tutoriaModificar->execute();
        $result = $tutoriaModificar->get_result();
        $tutoria = $result->fetch_assoc();
        $tutoriaModificar->close();

        $lugar = isset($tutoria['lugar']) ? htmlspecialchars($tutoria['lugar']) : '';
        $fecha = isset($tutoria['fecha']) ? htmlspecialchars($tutoria['fecha']) : '';
        $horaInicio = isset($tutoria['horaInicio']) ? htmlspecialchars($tutoria['horaInicio']) : '';
        $horaFin = isset($tutoria['horaFin']) ? htmlspecialchars($tutoria['horaFin']) : '';
        $notas = isset($tutoria['nota']) ? htmlspecialchars($tutoria['nota']) : '';

        $conn->close();
    }

    if (isset($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo "<p class='error'>$error</p>";
        }
        unset($_SESSION['errors']);
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
        <title>Formulario de Actualización de Tutorías</title>
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
                <button class="buttonsHead" onclick="location.href='./tutorTutorias.php'"><i class="fas fa-arrow-left"></i> Regresar</button>
                <button class="buttonsHead" onclick="location.href='.<?php echo $menu; ?>'"><i class="fas fa-home"></i> Inicio</button>
                <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
            </div>
            <div class="header-right">
                Universidad Veracruzana   
            </div>
        </div>

        <div class="container mt-5">
            <form action="modificarTutoria.php" method="POST" enctype="multipart/form-data" id="form">
                
                <input type="hidden" name="idTutoria" value="<?php echo htmlspecialchars($idTutoria); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label for="carrera">Carrera: <span class="text-danger">*</span></label>
                    <select class="form-control" id="carrera" name="carrera" required>
                        <option value="" disabled selected>-----Selecciona una carrera-----</option>
                        <?php 
                            while($carreras = $arrayCarreras->fetch_assoc()) { 
                                $selected = ($tutoria['carrera'] == $carreras['idCarrera']) ? 'selected' : '';
                                echo "<option value='{$carreras['idCarrera']}' $selected>{$carreras['nombre']}</option>";
                            } 
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numTutoria">Tutoría: <span class="text-danger">*</span></label>
                    <select class="form-control numTutoria" name="numTutoria" id="numTutoria" required>
                        <option value="" disabled selected>-----Selecciona el numero de tutoría-----</option>
                        <?php 
                            $tutoriaOptions = [1, 2, 3];
                            foreach ($tutoriaOptions as $option) {
                                $selected = ($tutoria['numTutoria'] == $option) ? 'selected' : '';
                                echo "<option value='$option' $selected>$option</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="periodo">Periodo Escolar: <span class="text-danger">*</span></label>
                    <select class="form-control periodo" name="periodo" id="periodo" required>
                        <option value="" disabled selected>-----Selecciona el periodo escolar-----</option>
                        <?php 
                            while($periodos = $arrayPeriodo->fetch_assoc()) { 
                                $selected = ($tutoria['periodo'] == $periodos['idPeriodo']) ? 'selected' : '';
                                echo "<option value='{$periodos['idPeriodo']}' $selected>{$periodos['nombre']}</option>";
                            } 
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <span>Modalidad: <span class="text-danger">*</span></span><br>
                    <?php 
                        $modalidades = ['Presencial', 'Virtual', 'Mixta'];
                        foreach ($modalidades as $modalidad) {
                            $checked = ($tutoria['modalidad'] == $modalidad) ? 'checked' : '';
                            echo "<div class='form-check form-check-inline'>
                                    <input class='form-check-input' type='radio' name='modalidad' id='$modalidad' value='$modalidad' $checked required>
                                    <label class='form-check-label' for='$modalidad'>$modalidad</label>
                                </div>";
                        }
                    ?>
                </div>

                <div class="form-group">
                    <span>Período Atención: <span class="text-danger">*</span></span><br>
                    <?php 
                        $periodosAtencion = ['Un solo día', 'Más de un día'];
                        foreach ($periodosAtencion as $periodoAtencion) {
                            $checked = ($tutoria['periodoAtencion'] == $periodoAtencion) ? 'checked' : '';
                            echo "<div class='form-check'>
                                    <input class='form-check-input' type='radio' name='periodoAtencion' id='$periodoAtencion' value='$periodoAtencion' $checked required>
                                    <label class='form-check-label' for='$periodoAtencion'>$periodoAtencion</label>
                                </div>";
                        }
                    ?>
                </div>

                <div class="form-group">
                    <label for="lugar">Lugar:</label>
                    <input type="text" class="form-control" id="lugar" name="lugar" placeholder="Lugar de tutoría máximo 300 caracteres" maxlength="300" value="<?php echo $lugar?>">
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" placeholder="dd/mm/aaaa" value="<?php echo $fecha?>">
                </div>

                <div class="form-group">
                    <label for="hora_inicio">Hora Inicio:</label>
                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="<?php echo $horaInicio?>">
                </div>

                <div class="form-group">
                    <label for="hora_final">Hora Final:</label>
                    <input type="time" class="form-control" id="hora_final" name="hora_final" value="<?php echo $horaFin ?>">
                </div>

                <div class="form-group">
                    <label for="notas">Notas:</label>
                    <textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Máximo 500 caracteres" maxlength="500"><?php echo $notas ?></textarea>
                </div>
                <div class="form-group">
                    <label for="archivo_horario">Archivo:</label>
                    <?php if (!empty($tutoria['archivo'])): ?>
                        <div class="mb-2">
                            <strong>Archivo Actual:</strong> <?php echo htmlspecialchars($tutoria['archivo']); ?>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control-file widthInput" id="archivo_horario" name="archivo_horario" accept=".pdf,.doc,.docx,.xls,.xlsx">
                </div>

                <div class="form-group row">
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-success buttonGreen" id="enviar">Guardar Horario</button>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>

<script>
    $(document).ready(function() {
        $("#carrera").select2();
        $("#periodo").select2();

        $('#lugar').on('input', function() {
            if ($(this).val().length >= 300) {
                $(this).val($(this).val().substring(0, 300));
                Swal.fire({
                    title: 'Límite alcanzado',
                    text: 'Has alcanzado el máximo de 300 caracteres para el campo Lugar.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        $('#notas').on('input', function() {
            if ($(this).val().length >= 500) {
                $(this).val($(this).val().substring(0, 500));
                Swal.fire({
                    title: 'Límite alcanzado',
                    text: 'Has alcanzado el máximo de 500 caracteres para el campo Notas.',
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
    
    function validarFormulario() {
        var carreraSeleccionada = $('#carrera').val();
        var tutoriaSeleccionada = $('#numTutoria').val();
        var periodoSeleccionado = $('#periodo').val();
        var modalidadSeleccionada = $('input[name="modalidad"]:checked').val();
        var periodoAtencionSeleccionado = $('input[name="periodoAtencion"]:checked').val();
        var fecha = $('#fecha').val();
        var horaInicio = $('#hora_inicio').val();
        var horaFinal = $('#hora_final').val();
        var notas = $('#notas').val();
        var lugar = $('#lugar').val();
        var archivo = $('#archivo_horario').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!carreraSeleccionada || !tutoriaSeleccionada || !periodoSeleccionado || !modalidadSeleccionada || !periodoAtencionSeleccionado) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!carreraSeleccionada) $('#carrera').addClass("borderRed");
            if (!tutoriaSeleccionada) $('#numTutoria').addClass("borderRed");
            if (!periodoSeleccionado) $('#periodo').addClass("borderRed");
            if (!modalidadSeleccionada) $('input[name="modalidad"]').closest('.form-check').addClass("borderRed");
            if (!periodoAtencionSeleccionado) $('input[name="periodoAtencion"]').closest('.form-check').addClass("borderRed");
            
            error = true;
        }

        if (horaInicio && horaFinal && horaInicio > horaFinal) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>La hora final no puede ser anterior a la hora de inicio.</p>',
                showConfirmButton: false,
                timer: 3500
            });
            error = true;
        }

        if (error) {
            return false;
        }

        $('#carrera, #numTutoria, #periodo, #fecha, #hora_inicio, #hora_final, #notas, #lugar').removeClass("borderRed").addClass("borderGreen");
        
        $("#form").submit();
    }
</script>