<?php 
include('conexao.php');
$id_empresa = $_SESSION['id2'];
$sql = $conn ->prepare("SELECT lgnId,lgmaster FROM tblLogin where lgnusuario = '$usuario'");
$sql -> execute();
if($dados3 = $sql -> fetch(PDO::FETCH_ASSOC)){
    $master =  $dados3["lgmaster"];
    $idUser = $dados3["lgnId"];
}

    if($master == 1){
        $query =  $conn -> prepare("SELECT  *
        FROM tblCliente cli (nolock)
        inner JOIN  tblClienteEmpresa emp (nolock) 
        ON emp.cliid = cli.cliid
        left JOIN  tblLogin lng (nolock)
        ON lng.lgnId = emp.lgUsuario
        WHERE emp.empid = '$id_empresa'  and cliCPF = '$pesquisa'");
        $query -> execute();

    }else{

        $query =  $conn -> prepare("SELECT  *
        FROM tblCliente cli (nolock)
        inner JOIN  tblClienteEmpresa emp (nolock) 
        ON emp.cliid = cli.cliid
        left JOIN  tblLogin lng (nolock)
        ON lng.lgnId = emp.lgUsuario
        WHERE lng.lgnId = $idUser  and cliCPF = '$pesquisa'");
        $query -> execute();
    }
    
?>
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
        <?php elseif($dados["clisituacao"] == 0 and $dados["cemsenha"] == 1):?>
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
        
        <?php if($dados["cemVerificado"] == 1 and $dados["clisituacao"] == 1):?>
            <?php $valoCheck = 0?>
            <td><a href="verificar.php?check=<?php echo $valoCheck?>&id=<?php echo  $dados["cliid"]?>" class="check"><img src="img/checkBramco.jpeg" ></a></td>     
        <?php elseif ($dados["cemVerificado"] == 0 and $dados["clisituacao"] == 1):?>
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