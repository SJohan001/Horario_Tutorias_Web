<?php
    define('logo_UV','.\img\UV.png');

    session_start();
    
    $rolesPermitidos = [3];
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

    include('conection.php');
    $conn = conectiondb();
    $result = null;

    $stmt = $conn->prepare("SELECT t.idTutor, 
                                   CONCAT(t.nombre, ' ', COALESCE(t.apellidoPaterno, ''), ' ', COALESCE(t.apellidoMaterno, '')) AS tutorNombre, 
                                   t.noPersonal, 
                                   t.correoInstitucional, 
                                   t.sesion 
                            FROM tutor t");

    $stmt->execute();
    $result = $stmt->get_result();

    $stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administración de profesores</title>
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
            text-align: right;
            margin: 20px auto;
            width: 82%;
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
            <button class="buttonsHead" onclick="location.href='./menuAdministrador.php'"><i class="fas fa-home"></i> Inicio</button>
            <button class="buttonsHead" onclick="location.href='./cerrarSesion.php'"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button>
        </div>
        <div class="header-right">
            Universidad Veracruzana   
        </div>
    </div>

    <div class="new-button-container">
        <button class="buttonNew" onclick="location.href = './registroProfesor.php' "><i class="fas fa-plus"></i> Nuevo</button>
    </div>

    <div class="table-container">
        <table id="profesoresTable">
            <thead>
                <tr>
                    <th class="autoWidthColumn">Nombre de profesor</th>
                    <th class="autoWidthColumn">Numero de personal</th>
                    <th class="autoWidthColumn">Correo institucional</th>
                    <th class="autoWidthColumn">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $idTutor = $row['idTutor'];
                            echo "<tr>
                                    <td>{$row['tutorNombre']}</td>
                                    <td>{$row['noPersonal']}</td>
                                    <td>{$row['correoInstitucional']}</td>
                                    <td class='action-buttons autoTable'>
                                        <button class='edit' data-id-tutor='{$idTutor}'><i class='fas fa-edit'></i></button>
                                        <button class='delete' data-id-tutor='{$idTutor}' data-csrf-token='{$_SESSION['csrf_token']}'><i class='fas fa-trash-alt'></i></button>
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
        $('#profesoresTable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.5/i18n/es-MX.json",
                "emptyTable": "<div class='empty-table-message'>No hay profesores disponibles</div>",
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

        function confirmDelete(idTutor, csrfToken) {
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
                        url: "./eliminarProfesor.php",
                        cache: false,
                        data: {
                            idTutor: idTutor,
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
        function editTutor(idTutor) {
            var form = $('<form>', {
                'method': 'POST',
                'action': './datosProfesor.php'
            }).append($('<input>', {
                'type': 'hidden',
                'name': 'idTutor',
                'value': idTutor
            }));
            $('body').append(form);
            form.submit();
        }

        $('#searchProfesor').on('input', function() {
            var searchValue = $(this).val().toLowerCase();
            $('table tr').each(function(index) {
                if (index !== 0) { 
                    var tutorNombre = $(this).find('td:first').text().toLowerCase();
                    if (tutorNombre.includes(searchValue)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                }
            });
        });
        
        $(document).on('click', '.delete', function() {
            var idTutor = $(this).data('id-tutor');
            var csrfToken = $(this).data('csrf-token');
            confirmDelete(idTutor, csrfToken);
        });
        
        $(document).on('click', '.edit', function() {
            var idTutor = $(this).data('id-tutor');
            editTutor(idTutor);
        });
    });
</script>