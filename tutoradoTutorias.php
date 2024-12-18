<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    $rolesPermitidos = [2];
    if(!isset($_SESSION['user']) || !in_array($_SESSION["rol"], $rolesPermitidos)) {
        header('Location: ./cerrarSesion.php');
        exit();
    }
    if(isset($_SESSION['message']))
    {   
        $message = $_SESSION['message'];
    }
    $user = $_SESSION['user'];

    
    include('conection.php');
    $conn = conectiondb();
    $result = null;

    $userCorreo = $_SESSION['correoInstitucional'] ?? '';

    $carreraTutorado = $conn->prepare("SELECT c.nombre AS carreraNombre
                                       FROM tutorado t 
                                       INNER JOIN carrera c ON c.idCarrera = t.carrera
                                       WHERE t.correoInstitucional = ?");
    $carreraTutorado->bind_param("s", $userCorreo);
    $carreraTutorado->execute();
    $carreraResult = $carreraTutorado->get_result();
    $carrera = $carreraResult->fetch_assoc()['carreraNombre'];
    $carreraTutorado->close();

    $user = $_SESSION['correoInstitucional'];
    $tutoriaConsulta = $conn->prepare("SELECT ttt.idTutoria, 
                                              CONCAT(tt.nombre, ' ', COALESCE(tt.apellidoPaterno, ''), ' ', COALESCE(tt.apellidoMaterno, '')) AS tutorNombre, 
                                              c.nombre AS carrera, 
                                              ttt.numTutoria AS tutoria, 
                                              ttt.fecha, 
                                              CONCAT(COALESCE(TIME_FORMAT(ttt.horaInicio, '%H:%i'), ''), ' - ', COALESCE(TIME_FORMAT(ttt.horaFin, '%H:%i'), '')) AS horario, 
                                              ttt.lugar, 
                                              ttt.nota, 
                                              ttt.archivo
                                       FROM tutoria ttt
                                       INNER JOIN tutor tt ON tt.idTutor = ttt.tutor 
                                       INNER JOIN periodo p ON p.idPeriodo = ttt.periodo
                                       INNER JOIN carrera c ON c.idCarrera = ttt.carrera
                                       WHERE c.nombre = ?");
    
    $tutoriaConsulta->bind_param("s", $carrera);
    $tutoriaConsulta->execute();
    $result = $tutoriaConsulta->get_result();

    $tutoriaConsulta->close();
    $conn->close();
?>


<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro de Tutorías UV</title>
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
        .search-container {
            text-align: right;
            margin: 20px auto;
            width: 60%;
            font-size: 18px;
            font-family: Arial, sans-serif;
            font-weight: bold;
        }
        .search-input {
            width: 50%;
            padding: 10px; 
            font-size: 18px;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            box-sizing: border-box;
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
            <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </div>
        <div class="header-right">
            Universidad Veracruzana   
        </div>
    </div>

    <div class="table-container">
        <table id="tutoriasTable">
            <thead>
                <tr>
                    <th class="autoWidthColumn">Tutor</th>
                    <th class="autoWidthColumn">Carrera</th>
                    <th class="autoWidthColumn">Tutoría</th>
                    <th class="autoWidthColumn">Fecha</th>
                    <th class="autoWidthColumn">Horario</th>
                    <th class="autoWidthColumn">Lugar</th>
                    <th class="autoWidthColumn">Nota</th>
                    <th class="autoWidthColumn">Archivo</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $idTutoria = $row['idTutoria'];
                            $archivo = $row['archivo']; 

                            $lugarCompleto = htmlspecialchars($row['lugar'] ?? '', ENT_QUOTES, 'UTF-8');
                            $lugarCorto = strlen($row['lugar'] ?? '') > 50 
                                ? htmlspecialchars(substr($row['lugar'], 0, 50), ENT_QUOTES, 'UTF-8') . "..."
                                : $lugarCompleto;

                            $notaCompleto = htmlspecialchars($row['nota'] ?? '', ENT_QUOTES, 'UTF-8');
                            $notaCorto = strlen($row['nota'] ?? '') > 50 
                                ? htmlspecialchars(substr($row['nota'], 0, 50), ENT_QUOTES, 'UTF-8') . "..."
                                : $notaCompleto;

                            $archivoRuta = './uploads/' . $archivo;
                            echo "<tr>
                                    <td>{$row['tutorNombre']}</td>
                                    <td>{$row['carrera']}</td>
                                    <td>{$row['tutoria']}</td>
                                    <td>{$row['fecha']}</td>
                                    <td>{$row['horario']}</td>
                                    <td title=\"{$lugarCompleto}\">{$lugarCorto}</td>
                                    <td title=\"{$notaCompleto}\">{$notaCorto}</td>";
                                    if (!empty($archivo) && file_exists($archivoRuta)) {
                                        echo "<td><a href='{$archivoRuta}' download>Descargar</a></td>";
                                    } else {
                                        echo "<td>No disponible</td>";
                                    }
                                    ?>
                        <?php
                                    echo "</tr>";
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
        $('#tutoriasTable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-MX.json",
                "emptyTable": "<div class='empty-table-message'>No hay sesiones de tutoría disponibles</div>",
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
                        '<h3 class="not-top-heading"> == Visibilidad de columnas</h3>',
                        'colvis'
                    ]
                }
            ]
        });
        
        $('#searchTutor').on('input', function() {
            var searchQuery = $(this).val();

            $.ajax({
                url: './buscarTutor.php',
                type: 'POST',
                data: { query: searchQuery },
                success: function(response) {
                    var tableBody = $('table').find('tbody');
                    tableBody.html(response);
                },
                error: function() {
                    alert('Error al realizar la búsqueda');
                }
            });
        });
    });
</script>
