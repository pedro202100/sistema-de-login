<?php
session_cache_expire(180000);
session_start();
//se nao existir uma sessao aberta leva para o login
if(!isset($_SESSION['usuario'])){
    header('Location: index.php');
    exit;
}//deixa um tempo de expiraçao

//recebo o meu ususario
$usuario = $_SESSION['usuario'];
//incluo minha pagina conxao com minha DB
include('conexao.php');
//começo meu HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="sortcut icon" href="img/logo.png" type="image/img"/>
    <link rel="stylesheet" type="text/css" href="css/estilo_pg.css"/>
    
    <title>Document</title>
</head>
<body>
    <header> 
        <nav>
            <ul id="nav-bar">
                <li><a href="solicitacao.php">Novo pedido</a></li>
                <li id="usuario"><p>Usuario: <?php echo $usuario?> </p></li>
                <li><a href="logout.php">Logoff</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div id="botoes"> 
            <a href="solicitacao.php"><button class="botoes_lista">NOVO PEDIDO</button></a>
            <form action="consulta.php" method="POST" id="form_pesquisa">
                <a><button class="botoes_lista" onClick="window.location.reload();">ATUALIZAR</button></a>
                <input type="text" name="pesquisa" maxlength="14" placeholder="Pesquisar CPF" autocomplete="off">
                <button type="submit" value="pesquisar" id="botao_post"><img src="img/searchmagnifierinterfacesymbol1_79893.png"></button>
            </form> 
        </div>  
        <table border="1">
            <?php
            $sql = $conn ->prepare("SELECT lgnId,lgmaster FROM tblLogin where lgnusuario = '$usuario'");
            $sql -> execute();
            if($dados3 = $sql -> fetch(PDO::FETCH_ASSOC)){
                $master =  $dados3["lgmaster"];
                $idUser = $dados3["lgnId"];
            }
            
            ?>
            <tr id="barra_table_consulta">
                
                <td>CPF</td>
                <?
                if($master == 1):?>
                    <td>USUARIO</td>
                <?php endif; ?>
                <td>DATA</td>
                <td>NOME</td>
                <td>Situação</td>
                <td>Senha</td>
                <td>Hiscon</td>
                <td>Atualizar</td>
                <td>Verificado</td>
            </tr>
            <?php
                $id_empresa = $_SESSION['id2'];
                $limiteLinha = 25;
                $pesquisa = $_POST["pesquisa"];
                // Extrai somente os números
                $pesquisa = preg_replace( '/[^0-9]/is', '', $pesquisa);
                // faço um select para paginaçao no php
                $sql = $conn -> prepare("SELECT cliCPF from tblCliente cli (nolock)
                inner JOIN  tblClienteEmpresa emp (nolock) 
                ON emp.cliid = cli.cliid 
                WHERE emp.empid = '$id_empresa'");
                $sql -> execute();
                $result = $sql -> fetchall();
                $total = count($result); 
                // arredondo o numero da paginaçao ex: 2.5 para 3
                $total_n = ceil($total/$limiteLinha);
                //se nao tiver nenhumaa pg ele deixa setado em 1
                if(isset($_GET["page"])){
                    $page = $_GET["page"];
                }else{
                    $page = 1;
                }
            ?>
            <?php 
            $this_page = ($page-1)*$limiteLinha;       
            $valor_2 = $this_page + $limiteLinha;
            $hoje = date('Y/m/d');
            $data = str_replace('/','',$hoje);
            $maxlinks = 0;
            //$sql2 = $conn ->prepare("SELECT lgUsuario FROM tblClienteEmpresa where ");
            
            if($master == 1){
                $query =  $conn -> prepare("
                DECLARE @datetimeoffset datetimeoffset(4) = '$data';
                DECLARE @datetime datetime = @datetimeoffset;
                with CTE_R as
                (
                    SELECT  ROW_NUMBER() OVER(ORDER BY  cemverificado,isnull(clidataatualizacao,@datetime) DESC)as rownum ,lgnusuario,clidataatualizacao,cemsenha,cliCPF,lgUsuario,cemverificado,cli.cliid,cliNome,clisituacao,clisenha,cliErro,isnull(clidataatualizacao,@datetime) as dta
                    FROM tblCliente cli (nolock)
                    inner JOIN  tblClienteEmpresa emp (nolock)
                    ON emp.cliid = cli.cliid
                    left JOIN  tblLogin lng (nolock)
                    ON lng.lgnId = emp.lgUsuario
                    WHERE emp.empid = '$id_empresa'
                )
                select * from CTE_R
                where RowNum >=$this_page and RowNum <= $valor_2");
                $query -> execute();

                
            }elseif($master == 0){
                
                $query =  $conn -> prepare("
                DECLARE @datetimeoffset datetimeoffset(4) = '$data';
                DECLARE @datetime datetime = @datetimeoffset;
                with CTE_R as
                (
                    SELECT  ROW_NUMBER() OVER(ORDER BY  cemverificado,isnull(clidataatualizacao,@datetime) DESC)as rownum ,clidataatualizacao,cemsenha,cliCPF,cemverificado,cli.cliid,cliNome,clisituacao,clisenha,cliErro,isnull(clidataatualizacao,@datetime) as dta
                    FROM tblCliente cli (nolock)
                    inner JOIN  tblClienteEmpresa emp (nolock) 
                    ON emp.cliid = cli.cliid
                    WHERE emp.lgUsuario = '$idUser'
                )
                select * from CTE_R
                where RowNum >=$this_page and RowNum <= $valor_2");
                
                $query -> execute();
                
            }
            ?>
            <?php if($pesquisa == ''):?>
                <?php while($dados = $query -> fetch(PDO::FETCH_ASSOC)): ?>
                    <tr id="main_table_consulta">
                        <td><?php echo $dados["cliCPF"]?></td>
                        <?php if($master == 1):?>
                        <td><?php echo $dados["lgnusuario"]?></td>
                        <? endif;?>
                        <?php
                        $data = $dados["clidataatualizacao"];
                        $date = strtotime($data);
                        ?>
                        <td><?php echo date('d/m/Y', $date);?></td>
                        <?php if($dados["clisituacao"] == 1): ?>
                            <td><?php echo $dados["cliNome"]?></td>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
                        <?php if($dados["clisituacao"] == 1): ?>
                            <td><?php echo 'concluido'?></td>
                            <td><?php echo $dados["clisenha"]?></td>
                        <?php elseif($dados["clisituacao"] == 2):?>
                            <td><?php echo 'Erro'?></td>
                            <td></td>
                        <?php elseif($dados["cemsenha"] == 1):?>
                            <td><?php echo 'Em Analise<br><h4>(Senha requerida)</h4>'?></td>
                            <td></td>
                        <?php else:?>
                            <td><?php echo 'Em Analise'?></td>
                            <td></td>
                        <?php endif;?>
                        <?php if($dados["clisituacao"] == 1):?>
                            <td id="hiscon"><a href="hiscon.php?id=<?php echo  $dados["cliid"] ?>" target="_blank"><img src="img/pdf.jpeg"></a></td>
                            <td class="refresh"><a href="refresh.php?id=<?php echo  $dados["cliid"]?>" onClick="return confirm('Tem certeza que deseja atualizar este hiscon?')"><img src="img/refresh.jpeg"></a></td>
                        <?php elseif($dados["clisituacao"] == 2): ?>
                            <td class="imagem"><img src="img/block.jpeg" title="<?php echo $dados["cliErro"]?>" alt="Imagem de erro"></td>
                            <td></td>    
                        <?php else:?>   
                            <td></td>
                            <td></td>
                        <?php endif;?>
                        
                        <?php if($dados["cemverificado"] == 1 and $dados["clisituacao"] == 1):?>
                            <?php $valoCheck = 0?>
                           <td><a href="verificar.php?check=<?php echo $valoCheck?>&id=<?php echo  $dados["cliid"]?>" class="check"><img src="img/checkBramco.jpeg" ></a></td>     
                        <?php elseif ($dados["cemverificado"] == 0 and $dados["clisituacao"] == 1):?>
                            <?php $valoCheck = 1?>
                           <td><a href="verificar.php?check=<?php echo $valoCheck?>&id=<?php echo  $dados["cliid"]?>" class="check"><img src="img/checkCheck.jpeg" ></a></td>         
                        <?php elseif ($dados["cemverificado"] == 0 and $dados["clisituacao"] == 2):?>
                            <?php $valoCheck = 1?>
                           <td><a href="verificar.php?check=<?php echo $valoCheck?>&id=<?php echo  $dados["cliid"]?>" class="check"><img src="img/checkCheck.jpeg" ></a></td>         
                        <?php elseif($dados["cemverificado"] == 1 and $dados["clisituacao"] == 2):?>
                            <?php $valoCheck = 0?>
                           <td><a href="verificar.php?check=<?php echo $valoCheck?>&id=<?php echo  $dados["cliid"]?>" class="check"><img src="img/checkBramco.jpeg" ></a></td>     
                        <?php else:?>
                            <td></td>
                        <?php endif;?>
                    </tr>
                <?php
                endwhile;
                ?>
            <?php else:?>
                <?php include_once('pesquisa.php') ?>
            <?php endif;?>      
        </table>
        <ul class="pagination">
            <li>
            <?php 
                $ant_pag = 1;
                $volt_page = $page - $ant_pag;
                if($page == 1){
                    echo '<p><<</p>';
                    echo '<p><</p>';
                }else{
                    echo '<a href="consulta.php?page=1"><<</a>';
                    echo '<a href="consulta.php?page='. $volt_page  .'"><</a>';
                }
                for($i = $page - $maxlinks; $i <= $page - 1; $i++){ 
                    if($i >= 1){
                        echo '<a href="consulta.php?page='.$i.'" id="link">'.$i.'</a>';
                    }
                }
                for($i = $page; $i <= $page + $maxlinks; $i++){
                    if($i <=$total_n){
                        echo '<a href="consulta.php?page='.$i.'"id="link">'.$i.'</a>';
                    }
                }
                $avanc_pag = 1;
                $avan_page = $page + $avanc_pag;
                if($page == $total_n){
                    echo '<p>></p>';
                    echo '<p>>></p>';
                }
                else{
                    echo '<a href="consulta.php?page='. $avan_page  .'">></a>';
                    echo '<a href="consulta.php?page='.$total_n.'">>></a>';
                }    
            ?> 
            </li>
        </ul>



        <div id="pagination">
        
        </div>
        
    </main>
    <footer style="
    bottom:0;
    width:100%;
    color: #5a5a5a;" id="footer">
            &copy;Jmarcel. Todos os direitos reservados.
    </footer>

</body>
</html>