<!-- =================  VISUALIZAR USUARIO ================= -->
<?php    
    $id = $_GET['id'];
    $result = $connection->query("SELECT * FROM produtos WHERE id = '$id' LIMIT 1")
    or  die($connection->error()); 
    $resultado = mysqli_fetch_assoc($result);    
?> 
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>Detalhes do Produto</h2>
    </div>    
   
    <div class="row">
        <div class="col-md-12"> 
            <!--IMAGEM-->
            <div class="col-md-2">
                <b>Imagem:</b>             
            </div>
             <div class="col-md-10"  >
                 <img src="<?php echo "images/".$resultado['imagem']; ?>" width="100" height="100">
            </div> 
            <!--ID-->
            <div class="col-md-2">
                <b>Id:</b>             
            </div>
             <div class="col-md-10">
                <?php echo $resultado['id']; ?>
            </div>
            <!--NOM-->
             <div class="col-md-2">
                <b>Nome:</b>             
            </div>
            <div class="col-md-10">
                <?php echo $resultado['nome']; ?>
            </div>
             <!--DESCICAO CURTA-->
            <div class="col-md-2">
                <b>Descricao curta:</b>             
            </div>
             <div class="col-md-10">
                <?php echo $resultado['descricao_curta']; ?>
            </div>
            <!--PRECO-->
               <div class="col-md-2" >
                <b>Preco:</b>             
            </div>
             <div class="col-md-10" >
                <?php echo $resultado['preco']; ?>
            </div>             
            <!--TAG-->
            <div class="col-md-2">
                <b>Tag:</b>             
            </div>
             <div class="col-md-10">
                <?php echo $resultado['tag']; ?>
            </div>
            <!--description-->
          <div class="col-md-2">
                <b>Descricao:</b>             
            </div>             
            <div class="col-md-10">
                <?php echo $resultado['description']; ?>
            </div>       
            <!--SITUACAO ID-->
            <div class="col-md-2">
                <b>Situacao:</b>             
            </div>
              <div class="col-md-10">
                 <?php echo $resultado['situacao_id']; ?>
            </div>  
            <!--CATEGORIA-->
            <div class="col-md-2">
                <b>Categoria:</b>             
            </div>
             <div class="col-md-10">
                 <?php echo $resultado['categoria_id']; ?>
            </div>  
            <!--DESCICAO LONGA-->
           <div class="col-md-2">
                <b>Descricao longa:</b>             
            </div>
             <div class="col-md-10">
                <?php echo $resultado['descricao_longa']; ?>
            </div>
        </div>
    </div>  
    <br/>
        <!--  BUTTONS-->
    <div class="row">
        <div>
<!--            <a href='administrativo.php?link=10'>
               <button type='button' class='btn btn-sm btn-info'>Listar</button> 
           </a>            -->
              <a href='administrativo.php?link=13&id=<?php echo $resultado['id']; ?> '> 
               <button type='button' class='btn btn-sm btn-warning'>Editar</button> 
           </a>
             <a href='proc_apagar_produto.php?id=<?php echo $resultado['id']; ?> '>
               <button type='button' class='btn btn-sm btn-danger'>Apagar</button> 
           </a>
        </div>
    </div>  
    <hr>
     <a href="administrativo.php?link=10">Voltar</a>
</div> 
<br/>

