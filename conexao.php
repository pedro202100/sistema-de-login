<?php 
try {
    $hostname = "seuHost";
    $dbname = "NomeDaSuaTabela";
    $username = "userName";
    $pw = "SuaSenha";
    $conn= new PDO ("sqlsrv:server=$hostname;Database=$dbname",$username, $pw);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo 'conexao realizada com sucesso!';
  } catch (PDOException $e) {
        echo "Erro de Conexão " . $e->getMessage() . "\n";
        exit;
  }
?>

