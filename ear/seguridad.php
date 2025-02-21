<?php
include 'parametros.php';
date_default_timezone_set("America/Lima");

session_start();
if (!isset($_SESSION['ldap_user'])){    
    header("location:".$urlLogin);
}elseif (!isset($_SESSION['rec_usu_id'])){  
    header("location:index.php");     
}

$var_modulo_ident = "ADMI";
?>
