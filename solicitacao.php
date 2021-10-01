<?php
//inicio uma sessao
session_cache_expire(180000);
session_start();
//verifica se a sessao ta aberta
if(!isset($_SESSION['usuario'])){
    header('Location: index.php');
    exit;
}


// pego o nome do usuario da pag Index
$usuario = $_SESSION['usuario'];
// incluoo a pag conexao com o DB
include('conexao.php');

if(count($_POST) > 0) {
    //1.pega os valores do formulario
    $cpf = $_POST['CPF'];

    if(isset($_POST['senha'])){
        $senha = 1;
        $_SESSION["senha"] = $senha;
    }else{
        $senha = 0;
        $_SESSION["senha"] = $senha;
    }
    function validaCPF($cpf) {
        // Extrai somente os números
        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
    // pego o id da empresa logada
    $id_empresa = $_SESSION['id2'];
    // extraio somente os numero do cpf ex: 111.111.111-11 == 11111111111
    $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
    // valido pra ver se o cpf esta certo 
    if(validaCPF($cpf) == true){
            // preparo um select chamando o CPF do cliente
            $query3 = $conn -> prepare("SELECT cliCPF FROM tblCliente WHERE tblCliente.cliCPF = '$cpf'");
            $query3->execute();
            $result = $query3-> fetchAll();
            // verifio se me retorno algo
            $valida_cpf_banco = count($result);

            #################
            
            // preparo um select  chamando o id do usuario
            $sql = $conn -> prepare("SELECT lgnId FROM tblLogin where lgnusuario = '$usuario'");
            $sql->execute();
            // retorno da minha query uma unica linha e os dados da consulta
            if($dados5 = $sql ->fetch(PDO::FETCH_ASSOC)){
                $id_User = $dados5["lgnId"];
            }

            global $validar_situacao;
            $validar_situacao = '';

            global $cli_id;
            $cli_id = '';

            $query5 = $conn -> prepare("SELECT clisenha,clisituacao,cliid FROM tblCliente WHERE cliCPF = '$cpf'");
            $query5->execute();

            if ($dados = $query5 ->fetch(PDO::FETCH_ASSOC)){

                if ($dados["clisituacao"] == '0'){
                    $validar_situacao = 'Em Analise';
                    $btSituacao = $dados["clisituacao"];
                    $situacao = $dados["clisituacao"];
                    $cli_id = $dados["cliid"];
                }
                elseif($dados["clisituacao"] == '1'){
                    $validar_situacao = 'Concluido';
                    $btSituacao = $dados["clisituacao"];
                    $situacao = $dados["clisituacao"];
                    $cli_id = $dados["cliid"];
                    
                }
                else{
                    $validar_situacao = 'Error';
                    $btSituacao = $dados["clisituacao"];
                    $situacao = $dados["clisituacao"];
                    $cli_id = $dados["cliid"];
                }
                 
            }
            $query7 = $conn -> prepare("SELECT cemsenha from tblClienteEmpresa where  cliid = '$cli_id'");
            $query7->execute();
            if ($dados1 = $query7 ->fetch(PDO::FETCH_ASSOC)){
                $check_senha1 = $dados1["cemsenha"];
                if($check_senha1 == 0){
                    $check_senha = 0; 
                }else{
                    $check_senha = 1;
                }       
            }
            $sql = $conn ->prepare("SELECT lgnId,lgmaster FROM tblLogin where lgnusuario = '$usuario'");
            $sql -> execute();
            if($dados3 = $sql -> fetch(PDO::FETCH_ASSOC)){
                $master =  $dados3["lgmaster"];
            }
            $query8 = $conn -> prepare("SELECT * FROM tblClienteEmpresa WHERE cliid = '$cli_id' and empid = '$id_empresa'");
            $query8->execute();
            $result = $query8-> fetchAll();
            $legth = count($result);

            $query9 = $conn -> prepare("SELECT * FROM tblClienteEmpresa WHERE  lgUsuario = '$id_User' and cliid = '$cli_id' ");
            $query9->execute();
            $result2 = $query9-> fetchAll();
            $legth2 = count($result2);
            $hoje = date('Y/m/d');
            $data = str_replace('/','',$hoje);

            if($master == 1){
                if($valida_cpf_banco == 1){
                    if($validar_situacao == 'Concluido'){
                        if($legth == 1){
                            if($senha == 0  and $check_senha == 0){
                                $resultado["msg"] = 'vc ja tem uma solicitaçao concluida Consulte nos seus pedidos 1';
                            }elseif($senha == 1  and $check_senha == 1){
                                $query5 = $conn -> prepare("UPDATE tblCliente set clisituacao = 0,clisenha='',clidataatualizacao='$data' where cliid='$cli_id'");
                                $query5->execute();
                                $query3 = $conn -> prepare("UPDATE tblClienteEmpresa set cemsenha=1,lgUsuario='$id_User',cemVerificado = 0 where cliid='$cli_id' and empid='$id_empresa'");
                                $query3->execute();
                                header('Location: consulta.php');
                            }elseif($senha == 1  and $check_senha == 0){
                                $query3 = $conn -> prepare("UPDATE tblClienteEmpresa set cemsenha=1,cemVerificado = 0 where cliid='$cli_id' and empid='$id_empresa'");
                                $query3->execute();
                                $query5 = $conn -> prepare("UPDATE tblCliente set clisituacao = 0,clisenha='',clidataatualizacao='$data' where cliid='$cli_id'"); 
                                $query5->execute();
                                header('Location: consulta.php');
                            }elseif($senha == 0  and $check_senha == 1){
                                $resultado["msg"] = 'vc ja tem uma solicitaçao concluida Consulte nos seus pedidos 1';
                            }
                        }elseif($legth == 0){
                            $query3 = $conn -> prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                            $query3->execute();
                            header('Location: consulta.php'); 
                        }
                    }elseif($validar_situacao == 'Em Analise'){
                        if($legth == 1){
                            $resultado["msg"] = 'vc ja tem uma solicitaçao em Analise Consulte nos seus pedidos';
                        }elseif($legth == 0){
                            $query4 = $conn -> prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                            $query4->execute();
                            $query5 = $conn -> prepare("UPDATE tblCliente set clidataatualizacao='$data' where cliid='$cli_id'");
                            $query5->execute();
                            header('Location: consulta.php');
                        }
                    }elseif($validar_situacao == 'Error'){
                        if($legth == 1){
                            $resultado["msg"] = 'Cliente ja consultado';
                        }elseif($tamanhoBanco == 0){
                            $query4 = $conn -> prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                            $query4->execute();
                            $query5 = $conn -> prepare("UPDATE tblCliente set clidataatualizacao='$data' where cliid='$cli_id'");
                            $query5->execute();
                            header('Location: consulta.php');
                        }
                    }
                }elseif($valida_cpf_banco == 0){
                    try{
                        $query = $conn->prepare("INSERT INTO tblCliente(cliCPF,clidataatualizacao,clisituacao) VALUES ('$cpf','$data',0)");
                        $query->execute();
                        $query9 = $conn->prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                        $query9->execute();
                        header('Location: consulta.php'); 
                    }catch(PDOException $e){
                        echo "Conexão falhou3: " . $e->getMessage();
                    }
                }else{
                    $resultado["msg"] = 'Contate o desenvolvedor do sistema!';
                }
            }elseif($master == 0){
                if($valida_cpf_banco == 1){
                    if($validar_situacao == 'Concluido'){
                        if($legth2 == 1){
                            if($senha == 0  and $check_senha == 0){
                                $resultado["msg"] = 'vc ja tem uma solicitaçao concluida Consulte nos seus pedidos 1';
                            }elseif($senha == 1  and $check_senha == 1){
                                $query5 = $conn -> prepare("UPDATE tblCliente set clisituacao = 0,clisenha='',clidataatualizacao='$data' where cliid='$cli_id'");
                                $query5->execute();
                                $query3 = $conn -> prepare("UPDATE tblClienteEmpresa set cemsenha=1,lgUsuario='$id_User',cemVerificado = 0 where cliid='$cli_id' and empid='$id_empresa'");
                                $query3->execute();
                                header('Location: consulta.php');
                            }elseif($senha == 1  and $check_senha == 0){
                                $query3 = $conn -> prepare("UPDATE tblClienteEmpresa set cemsenha=1,cemVerificado = 0 where cliid='$cli_id' and empid='$id_empresa'");
                                $query5 = $conn -> prepare("UPDATE tblCliente set clisituacao = 0,clisenha='',clidataatualizacao='$data' where cliid='$cli_id'");
                                $query3->execute();
                                $query5->execute();
                                header('Location: consulta.php');
                            }elseif($senha == 0  and $check_senha == 1){
                                $resultado["msg"] = 'vc ja tem uma solicitaçao concluida Consulte nos seus pedidos 1';
                            }
                        }elseif($legth2 == 0){
                            $query3 = $conn -> prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                            $query3->execute();
                            header('Location: consulta.php'); 
                        }
                    }elseif($validar_situacao == 'Em Analise'){
                        if($legth2 == 1){
                            $resultado["msg"] = 'vc ja tem uma solicitaçao em Analise Consulte nos seus pedidos';
                        }elseif($legth2 == 0){
                            $query4 = $conn -> prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                            $query4->execute();
                            $query5 = $conn -> prepare("UPDATE tblCliente set clidataatualizacao='$data' where cliid='$cli_id'");
                            $query5->execute();
                            header('Location: consulta.php');
                        }
                    }elseif($validar_situacao == 'Error'){
                        if($legth2 == 1){
                            $resultado["msg"] = 'Cliente ja consultado';
                        }elseif($legth2 == 0){
                            $query4 = $conn -> prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                            $query4->execute();
                            $query5 = $conn -> prepare("UPDATE tblCliente set clidataatualizacao='$data' where cliid='$cli_id'");
                            $query5->execute();
                            header('Location: consulta.php');
                        }
                    }
                }elseif($valida_cpf_banco == 0){
                    try{
                        $query = $conn->prepare("INSERT INTO tblCliente(cliCPF,clidataatualizacao,clisituacao) VALUES ('$cpf','$data',0)");
                        $query->execute();
                        $query9 = $conn->prepare("INSERT INTO tblClienteEmpresa (cliid,empid,cemsenha,lgUsuario) SELECT cliid,$id_empresa,$senha,$id_User from tblCliente where cliCPF = '$cpf'");
                        $query9->execute();
                        header('Location: consulta.php'); 
                    }catch(PDOException $e){
                        echo "Conexão falhou3: " . $e->getMessage();
                    }
                }else{
                    $resultado["msg"] = 'Contate o desenvolvedor do sistema!';
                }
            } 
            
    }else{
        $resultado["msg"] = 'CPF invalido';
    }
} 



?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="sortcut icon" href="img/logo.png" type="image/img" />
    <link rel="stylesheet" type="text/css" href="css/estilo_pg.css"/>
    <title>solicitacão</title>
</head>
<body>
    <header>
        <nav id="nav-bar">
            <ul>
                <li><a href="consulta.php">Consultas</a></li>
                <li><a href="logout.php">Logoff</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php
        $valor_2 = $this_page + $limiteLinha;
        ?>
        <form action="solicitacao.php" method="POST" id="formulario" name="form">
            <div id="CPF" class="form_dados">
                <label>CPF:</label>
                <input type="text" name="CPF" id="cpf"maxlength="14" required autocomplete="off"><br><br>
            </div> 
            <div class="senha">
                <label>quer senha? Sim:</label>
                <input type="checkbox" name="senha" value="senha_marcada"><br><br>
            </div>
            <button type="submit" value="enviar" name="enviar" class="botoes_lista">Enviar Pedido</button>
            <?php if(isset($resultado["msg"])):?>
                <div>
                    <?php echo $resultado["msg"]; ?>
                </div>
            <?php endif;?>
        </form>

    </main>
    <footer style="
    position: absolute;
    bottom: 0;
    width: 100%;
    color: #5a5a5a;">
            &copy;Jmarcel. Todos os direitos reservados.
    </footer>
</body>
</html>

