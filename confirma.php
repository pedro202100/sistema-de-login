<?php
session_start();
//verifica se a sessao ta aberta
if(!isset($_SESSION['usuario'])){
    header('Location: index.php');
    exit;
}
include('conexao.php');


$query5 = $conn -> prepare("UPDATE tblCliente set clidataatualizacao='$data',clisituacao=0,clisenha='' where cliid='$cli_id'");
$query5->execute();
header('Location: consulta.php');

?>