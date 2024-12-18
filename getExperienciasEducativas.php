<?php
    include ('./conection.php');
    $conn = conectiondb();

    if (isset($_POST['idProfesor'])) {
        $idProfesor = $_POST['idProfesor'];

        $experienciaEducativaConsulta = $conn->prepare("SELECT e.idExperienciaEducativa, 
                                                               CONCAT(e.nombre, ' - ', COALESCE(e.nrc, '')) AS nombre 
                                                        FROM experiencia_educativa e 
                                                        WHERE e.profesor = ?");
        $experienciaEducativaConsulta->bind_param("i", $idProfesor);
        $experienciaEducativaConsulta->execute();
        $result = $experienciaEducativaConsulta->get_result();

        $experiencias = [];
        while ($row = $result->fetch_assoc()) {
            $experiencias[] = ['idExperienciaEducativa' => $row['idExperienciaEducativa'], 'nombre' => $row['nombre']];
        }
        
        echo json_encode($experiencias);
        $experienciaEducativaConsulta->close();
        $conn->close();
    }
?>