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
    $periodoActual = $_SESSION['periodoActual'];

    include ('./conection.php');
    $conn = conectiondb();
    
    $carreraConsulta = $conn->prepare("SELECT c.idCarrera, 
                                              c.nombre 
                                        FROM carrera c");
    $carreraConsulta->execute();
    $arrayCarreras = $carreraConsulta->get_result();
    $carreraConsulta->close();

    $profesorActual = $conn->prepare("SELECT t.idTutor 
                                        FROM tutor t 
                                        WHERE t.correoInstitucional = ?");
    $profesorActual->bind_param("s", $perfilActual);
    $profesorActual->execute();
    $profesorActual->bind_result($tutor);
    $profesorActual->fetch();
    $profesorActual->close();

    $periodoConsulta = $conn->prepare("SELECT p.idPeriodo 
                                        FROM periodo p
                                        wHERE p.nombre = ?");
    $periodoConsulta->bind_param("s", $periodoActual);
    $periodoConsulta->execute();
    $periodoConsulta->bind_result($periodo);
    $periodoConsulta->fetch();
    $periodoConsulta->close();

    $problematicaConsulta = $conn->prepare("SELECT p.idProblematica, 
                                                   p.descripcion 
                                            FROM problematica p");
    $problematicaConsulta->execute();
    $arrayProblematicas = $problematicaConsulta->get_result();
    $problematicaConsulta->close();

    if (isset($_SESSION['errors'])) {
        if (is_array($_SESSION['erros'])) {
            foreach ($_SESSION['erros'] as $error) {
                echo "<p class='error'>$error</p>";
            }
        } else {
            echo "<p class='error'>{$_SESSION['errors']}</p>";
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
        <title>Formulario de Registro de Problemáticas</title>
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
                width: 100%;
                margin: auto auto;
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
            .problematica-table {
                display: none;
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
            <form action="crearReporteTutoria.php" method="post" enctype="multipart/form-data" id="form">

                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="tutor" value="<?= $tutor ?>">

                <div class="form-group">
                    <label for="periodo">Periodo escolar: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="periodo" value="<?= $periodoActual ?>" readonly>
                </div>

                <div class="form-group">
                    <div>
                        <label for="carrera">Carrera: <span class="text-danger">*</span></label>
                    </div>
                    <select class="form-control carreras" id="carrera" name="carrera" required>
                        <option value="" disabled selected>-----Selecciona la carrera-----</option>
                        <?php while($carreras = $arrayCarreras->fetch_assoc()) { ?>
                            <option value="<?php echo $carreras['idCarrera']; ?>"><?php echo $carreras['nombre']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numTutoria">Número de Tutoría: <span class="text-danger">*</span></label>
                    <select class="form-control numTutoria" name="numTutoria" id="numTutoria" required>
                        <option value="" disabled selected>-----Selecciona el numero de tutoría-----</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fechaInicio">Fecha de Inicio: <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" placeholder="dd/mm/aaaa">
                </div>

                <div class="form-group">
                    <label for="fechaFin">Fecha de Fin: <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" placeholder="dd/mm/aaaa">
                </div>

                <div class="form-group">
                    <label for="numAsistencias">Número de alumnos que asistieron: <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="numAsistencias" name="numAsistencias" placeholder="Número total de asistencias de tutorados" min="0" step="1">
                </div>

                <div class="form-group">
                    <label for="numRiesgo">Número de alumnos en riesgo: <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="numRiesgo" name="numRiesgo" placeholder="Número total de tutorados en riesgo" min="0" step="1">
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
                        <tr>
                            <th class="autoWidthColumn">Experiencia educativa <span class="text-danger" style="font-size: 25px;"> *</span></th>
                            <th class="autoWidthColumn">Profesor <span class="text-danger" style="font-size: 25px;">*</span></th>
                            <th class="autoWidthColumn">Problemática <span class="text-danger" style="font-size: 25px;">*</span></th>
                            <th class="autoWidthColumn">Número de alumnos <span class="text-danger" style="font-size: 25px;">*</span></th>
                            <th class="autoWidthColumn">Acción</th>
                        </tr>
                    </table>
                    <button type="button" id="agregarFilaBtn" class="btn btn-primary">Agregar problematica</button>
                </div>

                <div class="form-group">
                    <label for="comentario">Comentarios:</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3" placeholder="Máximo 500 caracteres" maxlength="500"></textarea>
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
        $("input[name='experienciaE[]']").select2();
        $("input[name='profesor[]']").select2();

        $('input[name="tipo"]').change(function() {
            if ($(this).val() === 'ninguno') {
                $('.problematica-table').hide();
                $('#problematicaTable tr:not(:first)').remove();
            } else {
                $('.problematica-table').show();
            }
        });

        function actualizarSelectsEnFila(row, response) {
            var $experienciaSelect = $(row).find('select[name="experienciaE[]"]');
            var $profesorSelect = $(row).find('select[name="profesor[]"]');

            $experienciaSelect.empty().append('<option value="" disabled selected>-----Selecciona una experiencia educativa-----</option>');
            $profesorSelect.empty().append('<option value="" disabled selected>-----Selecciona un profesor-----</option>');

            response.experiencias.forEach(function(exp) {
                $experienciaSelect.append($('<option>', { value: exp.idExperienciaEducativa, text: exp.nombre }));
            });

            response.profesores.forEach(function(prof) {
                $profesorSelect.append($('<option>', { value: prof.idProfesor, text: prof.nombreProfesor }));
            });
        }
        
        $(document).on('change', 'select[name="problematica[]"]', function() {
            var $fila = $(this).closest('tr'); 
            var $textarea = $fila.find('textarea[name="otro[]"]'); 

            if ($(this).val() === 'otro') {
                $textarea.show();
                $textarea.prop('required', true); 
            } else {
                $textarea.hide(); 
                $textarea.prop('required', false); 
                $textarea.val(''); 
            }
        });

        $('#carrera').change(function() {
            var idCarrera = $(this).val();
            $.ajax({
                url: 'getCarreraDatos.php',
                type: 'POST',
                data: { idCarrera: idCarrera },
                dataType: 'json',
                success: function(response) {
                    if (response && response.experiencias && response.profesores) {
                        $('#problematicaTable tbody tr').each(function(index, row) {
                            if (index === 0) return; 
                            actualizarSelectsEnFila(row, response);
                        });
                    } else {
                        alert('No se encontraron datos relacionados.');
                    }
                },
                error: function() {
                    alert('Hubo un error al obtener los datos.');
                }
            });
        });

        $(document).on('click', '#agregarFilaBtn', function() {
            var idCarrera = $('#carrera').val();
            if (!idCarrera) {
                alert('Por favor, selecciona una carrera antes de agregar una problemática.');
                return;
            }

            $.ajax({
                url: 'getCarreraDatos.php',
                type: 'POST',
                data: { idCarrera: idCarrera },
                dataType: 'json',
                success: function(response) {
                    if (response && response.experiencias && response.profesores) {
                        var nuevaFila = `
                            <tr>
                                <td>
                                    <select name="experienciaE[]" class="form-control experiencia-educativa" required>
                                        <option value="" disabled selected>-----Selecciona una experiencia educativa-----</option>
                                        ${response.experiencias.map(exp => `<option value="${exp.idExperienciaEducativa}">${exp.nombre}</option>`).join('')}
                                    </select>
                                </td>
                                <td>
                                    <select name="profesor[]" class="form-control profesor-problematica" required>
                                        <option value="" disabled selected>-----Selecciona un profesor-----</option>
                                        ${response.profesores.map(prof => `<option value="${prof.idProfesor}">${prof.nombreProfesor}</option>`).join('')}
                                    </select>
                                </td>
                                <td>
                                    <div class="form-group">
                                        <select class="form-control" name="problematica[]">
                                            <option value="" disabled selected>-----Problemática-----</option>
                                            <?php
                                                include_once ('./conection.php');
                                                $conn = conectiondb();

                                                $consulta = $conn->query("SELECT p.idProblematica, p.descripcion FROM problematica p");
                                                $problematicas = $consulta->fetch_all(MYSQLI_ASSOC);

                                                foreach ($problematicas as $problematica) {
                                                    echo "<option value='{$problematica['idProblematica']}'>{$problematica['descripcion']}</option>";
                                                }
                                            ?>
                                            <option value="otro">Otro</option>
                                        </select>
                                        <textarea id="otro" name="otro[]" class="form-control mt-2" placeholder="Describe la problemática, máximo 500 caracteres" maxlength="500" style="display: none;"></textarea>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="numAlumnos[]" class="form-control" placeholder="Número de alumnos" min="1" step="1" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger eliminarFila">Eliminar</button>
                                </td>
                            </tr>
                        `;
                        $('#problematicaTable').append(nuevaFila); 
                    } else {
                        alert('No se encontraron datos relacionados para la nueva fila.');
                    }
                },
                error: function() {
                    alert('Hubo un error al obtener los datos para la nueva fila.');
                }
            });
        });

        $(document).on('click', '.eliminarFila', function() {
            $(this).closest('tr').remove(); 
        });

        $(document).on('change', '.experiencia-educativa', function() {
            var idExperienciaEducativa = $(this).val();
            var $profesorSelect = $(this).closest('tr').find('select[name="profesor[]"]');

            $.ajax({
                url: 'getProfesores.php',
                type: 'POST',
                data: { idExperienciaEducativa: idExperienciaEducativa },
                dataType: 'json',
                success: function(response) {
                    $profesorSelect.empty();
                    $profesorSelect.append('<option value="" disabled selected>-----Selecciona un profesor-----</option>');
                    $.each(response, function(index, profesor) {
                        $profesorSelect.append('<option value="' + profesor.idProfesor + '" selected>' + profesor.nombreProfesor + '</option>');
                    });
                },
                error: function() {
                    alert('Hubo un error al obtener los profesores.');
                }
            });
        });

        $(document).on('change', '.profesor-problematica', function() {
            var idProfesor = $(this).val();
            var $experienciaEducativaSelect = $(this).closest('tr').find('select[name="experienciaE[]"]');

            $.ajax({
                url: 'getExperienciasEducativas.php',
                type: 'POST',
                data: { idProfesor: idProfesor },
                dataType: 'json',
                success: function(response) {
                    $experienciaEducativaSelect.empty();
                    $experienciaEducativaSelect.append('<option value="" disabled selected>-----Selecciona una experiencia educativa-----</option>');
                    $.each(response, function(index, experiencia) {
                        $experienciaEducativaSelect.append('<option value="' + experiencia.idExperienciaEducativa + '">' + experiencia.nombre + '</option>');
                    });
                },
                error: function() {
                    alert('Hubo un error al obtener las experiencias educativas.');
                }
            });
        });

        $('#comentario').on('input', function() {
            if ($(this).val().length >= 500) {
                $(this).val($(this).val().substring(0, 500));
                Swal.fire({
                    title: 'Límite alcanzado',
                    text: 'Has alcanzado el máximo de 500 caracteres para el campo Comentario.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });

        $(document).on('input', 'textarea[name="otro[]"]', function() {
            var maxLength = 500;
            var currentLength = $(this).val().length;

            if (currentLength > maxLength) {
                alert('Has alcanzado el límite de 500 caracteres.');
                $(this).val($(this).val().substring(0, maxLength));
            }
        });

        $("#enviar").on("click", function(e) 
        {
            e.preventDefault();
            validarFormulario();
        });
    });
    
    function validarFormulario() 
    {
        var carrera = $('#carrera').val();
        var numTutoria = $('#numTutoria').val();
        var fechaInicio = $('#fechaInicio').val();
        var fechaFin = $('#fechaFin').val();
        var numAsistencias = $('#numAsistencias').val();
        var numRiesgo = $('#numRiesgo').val();
        var comentario = $('#comentario').val();
        var tutor = $('#tutor').val();

        
        var experienciaE = $('#experienciaE').val();

        $('.form-control').removeClass("borderRed borderGreen");

        var error = false;
        if (!carrera || !numTutoria || !fechaInicio || !fechaFin || !numAsistencias || !numRiesgo) {
            Swal.fire({
                title: '¡Error!',
                icon: 'error',
                html: '<p>Todos los campos obligatorios dentro del reporte deben ser completados.</p>',
                showConfirmButton: false,
                timer: 3500
            });

            if (!carrera) $('#carrera').addClass("borderRed");
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

        if (!validateTable()) {
            return;
        }

        $('#carrera, #numTutoria, #fechaInicio, #fechaFin, #numAsistencias, #numRiesgo').removeClass("borderRed").addClass("borderGreen");

        $("#form").submit();
    }

    function validateTable() {
        const errores = [];
        let camposInvalidos = false;

        const carrera = $('#carrera').val();
        if (!carrera) {
            $('#carrera').css('border', '2px solid red');
            camposInvalidos = true;
        } else {
            $('#carrera').css('border', '1px solid #ccc');
        }

        const tipo = $('input[name="tipo"]:checked').val();
        
        if (tipo === 'problematica') {
            const filas = $('#problematicaTable tr:not(:first)'); 
            if (filas.length === 0) {
                alert('Debe agregar al menos una problemática si seleccionó "Agregar".');
                return false;
            }

            filas.each(function(index, fila) {
                const experiencia = $(fila).find('select[name="experienciaE[]"]').val();
                const profesor = $(fila).find('select[name="profesor[]"]').val();
                const problematica = $(fila).find('select[name="problematica[]"]').val();
                const numAlumnos = $(fila).find('input[name="numAlumnos[]"]').val();

                if (!experiencia) {
                    $(fila).find('select[name="experienciaE[]"]').css('border', '2px solid red');
                    camposInvalidos = true;
                } else {
                    $(fila).find('select[name="experienciaE[]"]').css('border', '1px solid #ccc');
                }

                if (!profesor) {
                    $(fila).find('select[name="profesor[]"]').css('border', '2px solid red');
                    camposInvalidos = true;
                } else {
                    $(fila).find('select[name="profesor[]"]').css('border', '1px solid #ccc');
                }

                if (!problematica) {
                    $(fila).find('select[name="problematica[]"]').css('border', '2px solid red');
                    camposInvalidos = true;
                } else {
                    $(fila).find('select[name="problematica[]"]').css('border', '1px solid #ccc');
                }

                if (!numAlumnos || numAlumnos <= 0) {
                    $(fila).find('input[name="numAlumnos[]"]').css('border', '2px solid red');
                    camposInvalidos = true;
                } else {
                    $(fila).find('input[name="numAlumnos[]"]').css('border', '1px solid #ccc');
                }

                if (problematica === 'otro') {
                    const descripcionOtro = $(fila).find('textarea[name="otro[]"]').val();
                    if (!descripcionOtro || descripcionOtro.length === 0) {
                        $(fila).find('textarea[name="otro[]"]').css('border', '2px solid red');
                        camposInvalidos = true;
                    } else {
                        $(fila).find('textarea[name="otro[]"]').css('border', '1px solid #ccc');
                    }
                }
            });
        }

        if (camposInvalidos) {
            Swal.fire({
                title: 'Se deben rellenar los campos obligatorios',
                icon: 'error',
                confirmButtonText: 'Corregir'
            });
            return false;
        }

        return true;
    }
</script>