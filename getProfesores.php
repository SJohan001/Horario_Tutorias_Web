<?php
    include ('./conection.php');
    $conn = conectiondb();

    if (isset($_POST['idExperienciaEducativa'])) {
        $idExperienciaEducativa = $_POST['idExperienciaEducativa'];

        $profesoresConsulta = $conn->prepare("SELECT t.idTutor, 
                                                     CONCAT(t.nombre, ' ', COALESCE(t.apellidoPaterno, ''), ' ', COALESCE(t.apellidoMaterno, '')) AS nombreProfesor
                                            FROM tutor t
                                            INNER JOIN experiencia_educativa ee ON ee.profesor = t.idTutor
                                            WHERE ee.idExperienciaEducativa = ?");
        $profesoresConsulta->bind_param("i", $idExperienciaEducativa);
        $profesoresConsulta->execute();
        $result = $profesoresConsulta->get_result();

        $profesores = [];
        while ($row = $result->fetch_assoc()) {
            $profesores[] = ['idProfesor' => $row['idTutor'], 'nombreProfesor' => $row['nombreProfesor']];
        }
        
        echo json_encode($profesores);
        $profesoresConsulta->close();
        $conn->close();
    }
?>