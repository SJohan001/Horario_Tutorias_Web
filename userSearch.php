<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
        if ($_POST['user'] != '' && $_POST['password'] != '') 
        {
            // define('SIMPLELDAP_DOMAIN_CONTROLLER','148.226.12.10'); // este es el LDAP de la UV

            // // Datos de autenticación
            // $ldaprdn  = $_POST['user'];
            // $ldappass = $_POST['password'];

            // // Conexión al servidor LDAP
            // $ldapconn = ldap_connect(SIMPLELDAP_DOMAIN_CONTROLLER) or die("No se puede conectar al servidor LDAP.");

            // if ( $ldapconn ) 
            // {
            //     // Set some ldap options for talking to AD
            //     ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            //     ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
                
            //     // Realizando la autenticación
            //     $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);

            //     // Verificación del enlace
            //     if ($ldapbind) 
            //     {
                    include ('./conection.php');
                    $conn = conectiondb();
                    global $login;
                    global $result;
                    
                    $user = $conn->real_escape_string($_POST['user']);
            
                    if (strpos($user, '@') === false) {
                        $correo_uv = $user . '@uv.mx';
                        $correo_estudiante = $user . '@estudiantes.uv.mx';
                    } else {
                        $correo_uv = $user;
                        $correo_estudiante = $user;
                    }

                    $stmt = $conn->prepare("SELECT s.idSesion, s.correoInstitucional, s.rol  
                                            FROM sesion s 
                                            WHERE s.correoInstitucional = ? OR s.correoInstitucional = ?");
                    $stmt->bind_param("ss", $correo_uv, $correo_estudiante);
                    $stmt->execute();
                    $login = $stmt->get_result();

                    $periodoConsulta = $conn->prepare("SELECT p.nombre 
                                                       FROM periodo p 
                                                       WHERE p.actual = 1 
                                                       LIMIT 1");
                    $periodoConsulta->execute();
                    $periodoConsulta->bind_result($periodoActual);
                    $periodoConsulta->fetch();
                    $periodoConsulta->close();

                    $stmt->close();

                    if($login->num_rows > 0){
                        $rowLogin = $login->fetch_assoc();
                        $rol = $rowLogin['rol'];

                        if($rol == 1){
                            $stmt = $conn->prepare("SELECT t.idTutor, 
                                                           t.nombre, 
                                                           IFNULL(t.apellidoPaterno, '') AS apellidoPaterno, 
                                                           IFNULL(t.apellidoMaterno, '') AS apellidoMaterno,
                                                           IFNULL(t.noPersonal, '') AS noPersonal,  
                                                           t.correoInstitucional, s.rol 
                                                    FROM sesion s 
                                                    INNER JOIN tutor t ON t.correoInstitucional = s.correoInstitucional 
                                                    WHERE s.correoInstitucional = ? OR s.correoInstitucional = ?");
                            $stmt->bind_param("ss", $correo_uv, $correo_estudiante);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            $stmt->close();
                        }elseif($rol == 2){
                            $stmt = $conn->prepare("SELECT t.idTutorado, 
                                                           t.nombre, 
                                                           IFNULL(t.apellidoPaterno, '') AS apellidoPaterno, 
                                                           IFNULL(t.apellidoMaterno, '') AS apellidoMaterno,
                                                           t.matricula, 
                                                           t.correoInstitucional, 
                                                           t.carrera, 
                                                           IFNULL(t.tutor, '') AS tutor, 
                                                           s.rol 
                                                    FROM sesion s 
                                                    INNER JOIN tutorado t ON t.correoInstitucional = s.correoInstitucional 
                                                    WHERE s.correoInstitucional = ?");
                            $stmt->bind_param("s", $correo_estudiante);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            $stmt->close();
                        }elseif($rol == 3){
                            $stmt = $conn->prepare("SELECT a.idAdministrador, 
                                                           a.nombre, 
                                                           IFNULL(a.apellidoPaterno, '') AS apellidoPaterno, 
                                                           IFNULL(a.apellidoMaterno, '') AS apellidoMaterno, 
                                                           a.correoInstitucional, 
                                                           s.rol 
                                                    FROM sesion s 
                                                    INNER JOIN administrador a ON a.correoInstitucional = s.correoInstitucional 
                                                    WHERE s.correoInstitucional = ? OR s.correoInstitucional = ?");
                            $stmt->bind_param("ss", $correo_uv, $correo_estudiante);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            $stmt->close();
                        }elseif($rol == 4){
                            $stmt = $conn->prepare("SELECT t.idTutor, 
                                                           t.nombre, 
                                                           IFNULL(t.apellidoPaterno, '') AS apellidoPaterno, 
                                                           IFNULL(t.apellidoMaterno, '') AS apellidoMaterno,
                                                           IFNULL(t.noPersonal, '') AS noPersonal,  
                                                           t.correoInstitucional, s.rol 
                                                    FROM sesion s 
                                                    INNER JOIN tutor t ON t.correoInstitucional = s.correoInstitucional 
                                                    WHERE s.correoInstitucional = ? OR s.correoInstitucional = ?");
                            $stmt->bind_param("ss", $correo_uv, $correo_estudiante);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            $stmt->close();
                        }
                        
                    }

                    mysqli_close($conn);

                    if($result)
                    {
                        while($row = $result->fetch_array())
                        {
                            $name               = $row['nombre'];
                            $usersuccesful      = $_POST['user'];
                            $passwordsuccesful  = $_POST['password']; 
                            $user_rol           = $row['rol'];
                            $correoInstitucional= $row['correoInstitucional'];
                        }
                    }
                    
                    if(isset($usersuccesful) && isset($passwordsuccesful) && isset($user_rol))
                    {
                        
                        if($user_rol == 1)
                        {
                            session_start();
                            $_SESSION["user"]=$name;
                            $_SESSION["rol"]=1;
                            $_SESSION["correoInstitucional"]=$correoInstitucional;
                            $_SESSION["periodoActual"]=$periodoActual;
                            header("Location: ./menuTutor.php");
                            die();
                        }
                        else if($user_rol == 2)
                        {
                            session_start();
                            $_SESSION["user"]=$name;
                            $_SESSION["rol"]=2;
                            $_SESSION["correoInstitucional"]=$correoInstitucional;
                            $_SESSION["periodoActual"]=$periodoActual;
                            header("Location: ./tutoradoTutorias.php");
                            die();
                        }
                        else if($user_rol == 3)
                        {
                            session_start();
                            $_SESSION["user"]=$name;
                            $_SESSION["rol"]=3;
                            $_SESSION["correoInstitucional"]=$correoInstitucional;
                            $_SESSION["periodoActual"]=$periodoActual;
                            header("Location: ./menuAdministrador.php");
                            die();
                        }
                        else if($user_rol == 4)
                        {
                            session_start();
                            $_SESSION["user"]=$name;
                            $_SESSION["rol"]=4;
                            $_SESSION["correoInstitucional"]=$correoInstitucional;
                            $_SESSION["periodoActual"]=$periodoActual;
                            header("Location: ./menuCoordinador.php");
                            die();
                        }
                        else
                        {
                            session_start();
                            $_SESSION["message"]="error";
                            header("Location: ./index.php");
                            die();
                        }
                    }
                    else
                    {
                        session_start();
                        $_SESSION["message"]="no_exist";
                        header("Location: ./index.php");
                        die(); 
                    }
            //     } 
            //     else 
            //     {
            //         session_start();
            //         $_SESSION["message"]="no_login";
            //         header("Location: ./index.php");
            //         die();
            //     }
            // }
            // else
            // {
            //     session_start();
            //     $_SESSION["message"]="error";
            //     header("Location: ./index.php");
            //     die();
            // }
        }
        else
        {
            session_start();
            $_SESSION["message"]="error";
            header("Location: ./index.php");
            die();
        }
    }
    else
    {
        session_start();
        $_SESSION["message"]="error";
        header("Location: ./index.php");
        die();
    }
?>