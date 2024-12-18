<?php 
    function conectiondb()
    {
        $host="localhost";
        $port=3306;
        $socket="";
        $user="root";
        $password="root";
        $dbname="sistema_registro_tutorias";
        $conn = new mysqli($host, $user, $password, $dbname, $port, $socket);
        return $conn;        
    }
?>