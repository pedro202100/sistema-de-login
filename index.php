<?php
session_start();
    if(count($_POST) > 0) {
        //1.pega os valores do formulario
        $usuario = $_POST['Usuario'];
        $senha = $_POST['senha'];
        try {
            include('conexao.php');
            $query = $conn->prepare("SELECT lgnusuario,lgnsenha FROM tblLogin WHERE lgnusuario=:lgnusuario and lgnsenha=:lgnsenha and lgnstatus = 'True'");
            $query ->bindParam(':lgnusuario',$usuario, PDO::PARAM_STR);
            $query ->bindParam(':lgnsenha',$senha, PDO::PARAM_STR);
            $query->execute();
            
            //3.verificar se usuario e senah esta no banco de dados 
            $result = $query-> fetchAll();
            $qtd_usuarios = count($result);
            $query2 = $conn->prepare("SELECT empid FROM tblLogin WHERE lgnstatus = 'True' AND lgnusuario = '$usuario' AND lgnsenha = '$senha'");
            $query2 -> execute();
            if($qtd_usuarios == 1){
                //TODO substituido pelo redirecionamento   
                $_SESSION['usuario'] = $usuario;
                if($dados = $query2 -> fetch(PDO::FETCH_ASSOC) ){
                    $_SESSION['id2'] = $dados["empid"];  
                    header('Location: consulta.php');
                }
            }else if($qtd_usuarios == 0){
                $resultado["msg"] = "<div align='center' ><h3>Usu&aacute;rio e/ou senha inv&aacute;lido(s)!</h3></div>";
                $resultado["cod"] = 0;
            }  
        } catch(PDOException $e) {
            echo "Conexão falhou: " . $e->getMessage();
            }
    }
?>

<!DOCTYPE html>
<html lang="en" id="html_main">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="sortcut icon" href="img/logo.png" type="image/img" />
    <link rel="stylesheet" type="text/css" href="css/estilo_index.css"/>
    
    <title>Hiscon</title>
</head>
<body id="corpo_index">
<img src="img/logo.png" alt="Logo do login">
        <?php if(isset($resultado) && ($resultado["cod"] == 0)): ?>
            <div class="alert alert-danger">
                <?php echo $resultado["msg"]; ?>
            </div>
        <?php endif;?>
    <div class="containner"> 
       
        <form action="index.php" id="form_login" method="POST">
            
            
            <p>Login:</p>
            <input type="text" id="Usuario" name="Usuario" required autocomplete="off"><br>
            <p>Senha:</p>    
            <input type="password" id="senha" name="senha" required><br>
            <button type="submit" value="Entrar">ENTRAR</button>
        </form>
    </div>
</body>
</html> 

 
