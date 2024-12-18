<?php
    session_start();
    
    include 'conection.php';
    $conn = conectiondb();
    $query = isset($_POST['query']) ? $_POST['query'] : '';
    $userCorreo = $_SESSION['correoInstitucional'] ?? '';

    if (empty($userCorreo)) {
        echo "<tr><td colspan='8'>Usuario no autenticado</td></tr>";
        exit();
    }

    $carreraTutorado = $conn->prepare("SELECT c.nombre AS carreraNombre
                                       FROM tutorado t 
                                       INNER JOIN carrera c ON c.idCarrera = t.carrera
                                       WHERE t.correoInstitucional = ?");
    $carreraTutorado->bind_param("s", $userCorreo);
    $carreraTutorado->execute();
    $carreraResult = $carreraTutorado->get_result();
    $carrera = $carreraResult->fetch_assoc()['carreraNombre'];
    $carreraTutorado->close();

    if ($query != '') {
        $sql = "SELECT t.idTutoria, 
                    CONCAT(tt.nombre, ' ', COALESCE(tt.apellidoPaterno, ''), ' ', COALESCE(tt.apellidoMaterno, '')) AS tutorNombre, 
                    c.nombre AS carrera, 
                    t.numTutoria AS tutoria, 
                    t.fecha, 
                    CONCAT(COALESCE(TIME_FORMAT(t.horaInicio, '%H:%i'), ''), ' - ', COALESCE(TIME_FORMAT(t.horaFin, '%H:%i'), '')) AS horario,  
                    t.lugar, 
                    t.nota, 
                    t.archivo 
                FROM tutoria t 
                INNER JOIN tutor tt ON tt.idTutor = t.tutor
                INNER JOIN periodo p ON p.idPeriodo = t.periodo
                INNER JOIN carrera c ON c.idCarrera = t.carrera
                WHERE (tt.nombre LIKE ? 
                OR COALESCE(tt.apellidoPaterno, '') LIKE ? 
                OR COALESCE(tt.apellidoMaterno, '') LIKE ?)
                AND c.nombre = ?";

        $tutoriaBusqueda = $conn->prepare($sql);

        $searchTerm = "%{$query}%";
        $tutoriaBusqueda->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $carrera);
    } else {
        $sql = "SELECT t.idTutoria, 
                    concat(tt.nombre,' ',tt.apellidoPaterno,' ',tt.apellidoMaterno) AS tutorNombre, 
                    c.nombre AS carrera, 
                    t.numTutoria AS tutoria, 
                    t.fecha, 
                    concat(t.horaInicio,' - ', t.horaFin) AS horario, 
                    t.lugar, 
                    t.nota, 
                    t.archivo 
                FROM tutoria t 
                INNER JOIN tutor tt ON tt.idTutor = t.tutor
                INNER JOIN periodo p ON p.idPeriodo = t.periodo
                INNER JOIN carrera c ON c.idCarrera = t.carrera
                WHERE c.nombre = ?";

        $tutoriaBusqueda = $conn->prepare($sql);
        $tutoriaBusqueda->bind_param("s", $carrera);
    }

    if ($tutoriaBusqueda) {
        $tutoriaBusqueda->execute();
        $resultSearch = $tutoriaBusqueda->get_result();

        echo "<tr>
            <th class='autoWidthColumn'>Tutor</th>
            <th class='autoWidthColumn'>Carrera</th>
            <th class='autoWidthColumn'>Tutoría</th>
            <th class='autoWidthColumn'>Fecha</th>
            <th class='autoWidthColumn'>Horario</th>
            <th class='autoWidthColumn'>Lugar</th>
            <th class='autoWidthColumn'>Nota</th>
            <th class='autoWidthColumn'>Archivo</th>
        </tr>";
        if ($resultSearch->num_rows > 0) {
            while ($row = $resultSearch->fetch_assoc()) {
                $idTutoria = $row['idTutoria'];
                $archivo = $row['archivo']; 
                $archivoRuta = './uploads/' . $archivo;
                echo "<tr>
                            <td>{$row['tutorNombre']}</td>
                            <td>{$row['carrera']}</td>
                            <td>{$row['tutoria']}</td>
                            <td>{$row['fecha']}</td>
                            <td>{$row['horario']}</td>
                            <td>{$row['lugar']}</td>
                            <td>{$row['nota']}</td>";
                            if (!empty($archivo) && file_exists($archivoRuta)) {
                                echo "<td><a href='{$archivoRuta}' download>Descargar</a></td>";
                            } else {
                                echo "<td>No disponible</td>";
                            }
                            ?>
                <?php
                            echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No hay tutorías disponibles</td></tr>";
        }

        $resultSearch->free();
        $tutoriaBusqueda->close(); 
    } else {
        echo "<tr><td colspan='8'>Error en la consulta</td></tr>";
    }

    $conn->close();
?>

<style>
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
</style>