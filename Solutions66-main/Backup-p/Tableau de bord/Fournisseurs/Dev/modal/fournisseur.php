<?php
require_once('../../req/init.php');
ob_start();
if ( preg_match('#^\d+$#',$_GET['id_fournisseur']) ){
    $getFour = query('SELECT * FROM FOURNISSEURS WHERE inactif IS NULL AND id_fournisseur = ?',[$_GET['id_fournisseur']],$mysqli);
    if($getFour->num_rows === 1){
        $rowFour = $getFour->fetch_assoc();
        $rowFourDesc = null;
        $getDescFour = query('SELECT * FROM FOURNISSEURS_DESC WHERE id_fournisseur = ?',[$rowFour['id_fournisseur']],$mysqli);
        if($getDescFour->num_rows === 1){
            $rowFourDesc = $getDescFour->fetch_assoc();
        }
        ?>
        <style>
        .documentBox{
	background-color:#eee;
	text-align:center;
	padding:5px;
	min-height:110px;
	}
	.documentoption{
	position: absolute;
	right: 10px;
	top: 0px;
	}
	.documentoption:hover{
		background-color:#428bca;
		color:#fff;
	}
	.documentBox:hover{
		background-color:#428bca;
		color:#fff;
	}
        </style>
		<div style="width:100%;max-width: 1400px;">
        	<div class="pb-2">
        		<h4 class="pb-2"><?= $rowFour['nom']?></h4>
        		<ul class="nav nav-tabs" role="tablist">
        			<li class="nav-item">
        			    <a href="#four_info" data-toggle="tab" class="nav-link active">Informations</a>
        		    </li>
        			<li class="nav-item">
        			    <a href="#four_document" data-toggle="tab" class="nav-link">Documents</a>
        		    </li>
        		</ul>
        	</div>
        	<div class="">
        		<div class="tab-content">
        			<div class="tab-pane active" id="four_info">
        				<div class="row">
            				<div class="col-12">
            					<?php if($rowFourDesc){
            					   echo $rowFourDesc['comm_desc_fr'];
            					}else{
            					   echo 'Aucune information sur ce fournisseur ou distributeur.';
            					}?>
            				</div>
            				<div class="col-12 pt-4">
            					<small>
            					<?php if($rowFourDesc){
            					   echo 'Dernière modification le '.formatDateUTF8($rowFourDesc['date_update']);
            					}?>
            					</small>
            				</div>
            			</div>
        			</div>
        			<div class="tab-pane" id="four_document">
        				<div class="row" style="width: 1200px;" id="divDoc"> </div>
        			</div>
        		</div>
        	</div>
        </div>
        <script>
        divAjax('ajax/document','divDoc',{'inc':'four','type':'four','id_four':<?= $rowFour['id_fournisseur']?>});
        </script>
        <?php
    }
}
die(json_encode(['status'=>'success','html'=>ob_get_clean()]));
?>