<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    $rolesPermitidos = [1,4];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }

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

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $user = $_SESSION['user'];

    include('conection.php');
    $conn = conectiondb();
    $result = null;

    $userCorreo = $_SESSION['correoInstitucional'];
    $stmt = $conn->prepare("SELECT	rt.idReporte, 
                                    c.nombre AS carrera, 
                                    p.nombre AS periodo, 
                                    rt.fechaInicioTutoria, 
                                    rt.fechaFinTutoria, 
                                    rt.numTutoria, 
                                    rt.numRiesgo, 
                                    rt.comentario, 
                                    (SELECT COUNT(pa.idProblematicaAcademica) FROM problematica_academica pa WHERE pa.reporte = rt.idReporte) AS tieneProblematica, 
                                    rt.fechaCreacion 
                            FROM 
                                reporte_tutoria rt 
                            INNER JOIN carrera_tutor tc ON tc.idCarreraTutor = rt.carreraTutor 
                            INNER JOIN carrera c ON c.idCarrera = tc.carrera 
                            INNER JOIN tutor t ON t.idTutor = tc.tutor 
                            INNER JOIN periodo p ON p.idPeriodo = rt.periodo 
                            WHERE 
                                t.correoInstitucional = ? 
                            ORDER BY 
                                STR_TO_DATE(p.nombre, '%M %Y - %M %Y') DESC");
    
    $stmt->bind_param("s", $userCorreo);
    $stmt->execute();
    $result = $stmt->get_result();

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

    $stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administración de Reportes de Tutoría</title>
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
        .new-button-container {
            text-align:end;
            margin: 30px auto;
            width: 80%;
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
        .table-container {
            text-align: center;
            margin: 0px 0; 
            padding: 20px 180px; 
        }
        .top-left {
            text-align: left;
        }
        .top-right {
            text-align: right;
        }
        .bottom-left {
            margin-top: 10px;
            text-align: left;
        }
        .bottom-right {
            margin-top: 10px;
            text-align: right;
        }
        footer {
            margin-top: auto;
            text-align: center;
            padding: 10px;
            position: relative;
            background-color: #f1f1f1;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="libs/DataTables/datatables.min.css" rel="stylesheet">
 
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="libs/DataTables/datatables.min.js"></script>
</head>
<body>
    <div class="header-container">
        <div class="header-left">
            <img src="<?= logo_UV ?>" alt="UV Logo">
            <div class="welcome-message">Bienvenid@ <?php echo $_SESSION['user']; ?></div>
            <button class="buttonsHead" onclick="location.href='<?php echo $menu; ?>'"><i class="fas fa-home"></i> Inicio</button>
            <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </div>
        <div class="header-right">
            Universidad Veracruzana   
        </div>
    </div>

    <div class="new-button-container">
        <button class="buttonNew" onclick="location.href = './registroReporteTutoria.php' "><i class="fas fa-plus"></i> Nuevo</button>
    </div>

    <div class="table-container">
        <table id="reportesTable">
            <thead>
                <tr>
                    <th class="autoWidthColumn">Carrera</th>
                    <th class="autoWidthColumn">Periodo</th>
                    <th class="autoWidthColumn">Fecha de inicio de tutoría</th>
                    <th class="autoWidthColumn">Fecha de fin de tutoría</th>
                    <th class="autoWidthColumn">Comentario</th>
                    <th class="autoWidthColumn">Problemática</th>
                    <th class="autoWidthColumn">Fecha de creación</th>
                    <th class="autoWidthColumn">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $idReporte = $row['idReporte'] ?? '';
                            $carrera = $row['carrera'] ?? 'Sin carrera';
                            $periodo = $row['periodo'] ?? 'Sin periodo';
                            $fechaInicioTutoria = $row['fechaInicioTutoria'] ?? 'Sin fecha de inicio';
                            $fechaFinTutoria = $row['fechaFinTutoria'] ?? 'Sin fecha de cierre';
                            $numTutoria = $row['numTutoria'] ?? 'Sin número de tutoría';
                            $numRiesgo = $row['numRiesgo'] ?? 'Sin número de alumnos en riesgo';
                            $comentarioCompleto = htmlspecialchars($row['comentario'] ?? '', ENT_QUOTES, 'UTF-8');
                            $tieneProblematica = $row['tieneProblematica'] ?? 0;
                            $fechaCreacion = $row['fechaCreacion'] ?? 'Sin fecha de creación';

                            $problematica = $row['tieneProblematica'] > 0 ? "Sí existen problemáticas" : "No existen problemáticas";

                            $comentarioCorto = strlen($row['comentario'] ?? '') > 50 
                                ? htmlspecialchars(substr($row['comentario'], 0, 50), ENT_QUOTES, 'UTF-8') . "..."
                                : $comentarioCompleto;
                            echo "<tr>
                                    <td>{$carrera}</td>
                                    <td>{$periodo}</td>
                                    <td>{$fechaInicioTutoria}</td>
                                    <td>{$fechaFinTutoria}</td>
                                    <td title=\"{$comentarioCompleto}\">{$comentarioCorto}</td>
                                    <td>{$problematica}</td>
                                    <td>{$fechaCreacion}</td>
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
    </div>
</body>
<footer>© Universidad Veracruzana</footer>
</html>

<script>
    $(document).ready(function() {
        $('#reportesTable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-MX.json",
                "emptyTable": "<div class='empty-table-message'>No hay reportes de tutorías disponibles</div>",
                "zeroRecords": "No se encontraron coincidencias"
            },
            "paging": true,
            "searching": true,
            "ordering": true,
            "pageLength": 10,
            "autoWidth": true, 
            "responsive": true,
            "dom": '<"top-left"l><"top-right"f><"top-left"B>t<"bottom-left"i><"bottom-right"p>r',
            layout: {
                topStart: 'buttons'
            },
            buttons: [
                {
                    extend: 'collection',
                    className: 'custom-html-collection',
                    buttons: [
                        '<h3> == Exportar</h3>',
                        'pdf',
                        'print',
                        'excel',
                        'copy',
                        '<h3 class="not-top-heading"> == Visibilidad de columnas</h3>',
                        'colvis'
                    ]
                }
            ]
        });

        function confirmDelete(idReporte, csrfToken) {
            Swal.fire({
                title: '¿Estás seguro de eliminar este registro?',
                text: "No podrás revertir esta acción.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "./eliminarReporteTutoria.php",
                        cache: false,
                        data: {
                            idReporte: idReporte,
                            csrf_token: csrfToken
                        },
                        error: function() {
                            Swal.fire({
                                title: '¡Oh no!',
                                text: 'Ha ocurrido un error, intente de nuevo, por favor.',
                                icon: 'error',
                                showConfirmButton: false,
                                timer: 3500
                            });
                        },
                        success: function() {
                            Swal.fire({
                                title: '¡Registro eliminado!',
                                text: 'El registro ha sido eliminado exitosamente.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 3500
                            });
                            location.reload();
                        }
                    });
                }
            });
        }
        function editReporte(idReporte) {
            var form = $('<form>', {
                'method': 'POST',
                'action': './datosReporteTutoria.php'
            }).append($('<input>', {
                'type': 'hidden',
                'name': 'idReporte',
                'value': idReporte
            }));
            $('body').append(form);
            form.submit();
        }

        $(document).on('click', '.delete', function() {
            var idReporte = $(this).data('id-reporte');
            var csrfToken = $(this).data('csrf-token');
            confirmDelete(idReporte, csrfToken);
        });        
        
        $(document).on('click', '.edit', function() {
            var idReporte = $(this).data('id-reporte');
            editReporte(idReporte);
        });
    });
</script>