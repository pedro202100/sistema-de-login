<?php
session_start();

if(!isset($_SESSION['usuario'])){
    header('Location: index.php');
    exit;
}
session_cache_expire(60);


include('conexao.php');


$verificar = $_GET["check"];
$id = $_GET["id"];

$id_empresa = $_SESSION['id2'];
$query =  $conn -> prepare("update tblClienteEmpresa set cemVerificado = '$verificar' where cliid = '$id' and empid = '$id_empresa' ");
$query -> execute();

header('Location: consulta.php');

?>