
<?php
session_start();
if(!isset($_SESSION['usuario'])){
    header('Location: index.php');
    exit;
}
session_cache_expire(60);
$hoje = date('Y/m/d');
$data = str_replace('/','',$hoje);

$id = $_GET["id"];

include('conexao.php');
$query =  $conn -> prepare("update tblCliente set clisituacao = '0', clidataatualizacao = '$data' where cliid = '$id'");
$query -> execute();
$query2 =  $conn -> prepare("update tblClienteEmpresa set cemVerificado = 0 where cliid = '$id'");
$query2 -> execute();

header('Location: consulta.php');
?>

