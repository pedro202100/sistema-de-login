<?php
session_start();
if(!isset($_SESSION['usuario'])){
    header('Location: index.php');
    exit;
}
//$base64 = $_SESSION["base64"];
$id = $_GET["id"];

include('conexao.php');
$query =  $conn -> prepare("SELECT clihiscon FROM tblClienteEmpresa emp (nolock)
inner join 
tblCliente cli (nolock)
ON emp.cliid = cli.cliid WHERE emp.cliid = '$id'");
$query -> execute();

if ($dados = $query -> fetch(PDO::FETCH_ASSOC)){
    $base64 =  $dados["clihiscon"];
}

$base64_decode =  base64_decode($base64);

$pdf = fopen('hiscon.pdf', 'w+');
fwrite($pdf, $base64_decode);
fclose($pdf);

header('Location: hiscon.pdf');

?>
