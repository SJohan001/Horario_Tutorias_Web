<?php
    include ('./conection.php');
    $conn = conectiondb();

    if (isset($_POST['idCarrera'])) {
        $idCarrera = $_POST['idCarrera'];

        $experienciaEducativaConsulta = $conn->prepare("SELECT e.idExperienciaEducativa, 
                                                               CONCAT(e.nombre, ' - ', COALESCE(e.nrc, '')) AS nombre 
                                                        FROM experiencia_educativa e 
                                                        WHERE e.programaEducativo = ?");
        $experienciaEducativaConsulta->bind_param("i", $idCarrera);
        $experienciaEducativaConsulta->execute();
        $resultEE = $experienciaEducativaConsulta->get_result();

        $experiencias = [];
        while ($rowEE = $resultEE->fetch_assoc()) {
            $experiencias[] = ['idExperienciaEducativa' => $rowEE['idExperienciaEducativa'], 'nombre' => $rowEE['nombre']];
        }

        $profesoresConsulta = $conn->prepare("SELECT t.idTutor, 
                                                     CONCAT(t.nombre, ' ', COALESCE(t.apellidoPaterno, ''), ' ', COALESCE(t.apellidoMaterno, '')) AS nombreProfesor
                                            FROM tutor t
                                            INNER JOIN experiencia_educativa ee ON ee.profesor = t.idTutor
                                            WHERE ee.programaEducativo = ?");
        $profesoresConsulta->bind_param("i", $idCarrera);
        $profesoresConsulta->execute();
        $resultProfesor = $profesoresConsulta->get_result();

        $profesores = [];
        while ($rowProfesor = $resultProfesor->fetch_assoc()) {
            $profesores[] = ['idProfesor' => $rowProfesor['idTutor'], 'nombreProfesor' => $rowProfesor['nombreProfesor']];
        }
        
        $response = [
            'profesores' => $profesores,
            'experiencias' => $experiencias
        ];
        echo json_encode($response);
        $experienciaEducativaConsulta->close();
        $profesoresConsulta->close();
        $conn->close();
    }
?>