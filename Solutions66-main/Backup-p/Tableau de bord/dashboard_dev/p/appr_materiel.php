<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Traite l'envoie du form provenant du Modal pour supprimer un materiel
if(isset($_POST["btnFormDelete"]) && preg_match('#^[0-9]+$#', $_POST["idPorduit"]) ){
    
    $tabMaterial = array();
    $tabMaterial["deleted"] = 1;
    $tabMaterial["id_mat"] = $_POST["idPorduit"];
    
    faireUpdate_i($tabMaterial,"MATERIAL", "id_mat" ,$mysqli,0);
    
    $_SESSION["message"] = ["content"=>"Produit supprimé avec succès!","type"=>"success"];
    redirect("index.php?p=appr_materiel",0);
}

/*
 * // script temporaire pour migrer le tableau de produits HTML présent dans MATERIAL_COMMANDE.commande
 * // vers le format dans la table MATERIAL_COMMANDE_ARTICLE
if(INDEV && $_GET['do'] == 'papa'){
	$getq = query('select * from MATERIAL_COMMANDE',[],$mysqli);
	while($row = $getq->fetch_assoc()){
		$xml = '<?xml version="1.0" encoding="UTF-8" ?><root>';
		$xml .= $row['commande'] . '</root>';
		$commande = simplexml_load_string($xml);
		foreach($commande->li as $unArticle){
			preg_match('#(.+)\ +\(#',$unArticle,$matches0);
			$nomA = trim($matches0[1]);
			preg_match('#\((.+)\)#',$unArticle,$matches);
			$desc = $matches[1];
			preg_match('#\)\ +\:\ (\d+)#',$unArticle,$matches2);
			$qty = $matches2[1];
			vex($desc);
			vex($nomA);
			vex($qty);
			if(!$desc){
				$desc = ', ';
			}
			vex($desc);
			if(!empty($nomA) && $qty > 0){
				// vérifier si le produit existe
				$query2 = query("select id_mat from MATERIAL where mat_name = ? and (concat(ifnull(mat_desc,''),', ',ifnull(mat_qty,'')) = ? OR ', ' = ?)",[$nomA,$desc,$desc],$mysqli);
				if($query2->num_rows === 1){
					$id_mat = $query2->fetch_row()[0];
					$arrayDB = array();
					$arrayDB['QTY'] = $qty;
					$arrayDB['id_mat'] = $id_mat;
					$arrayDB['id_commande'] = $row['id_commande'];
					$arrayDB['DATE_INSERT'] = $row['date'];
					faireInsert_i($arrayDB,'MATERIAL_COMMANDE_ARTICLE',$mysqli,1);
				}
			}
		}
	}
}*/

//Traite le post du form modal des informations de la commande 
if ((isset($_POST["form_matpromo"])) && ($_POST["form_matpromo"] == "sendok")) {
	$mail = new PHPMailer(true);
	$mysqli->autocommit(false);
	try{
		$error = false;
		$email_message .= "Magasin : " . getInfoMag("succursale") . " (" . $_SESSION["utilisateur"]["prenom"] . " " . $_SESSION["utilisateur"]["nom"] . ")<br/><br/>";
		
		foreach ($_SESSION['itemSession'] as $key => $value) {
		    //Si le panier contient item
			if ($value > 0) {
				$queryMaterial = "SELECT * FROM MATERIAL WHERE id_mat = ?";
				$resultMaterial = query($queryMaterial,[$key],$mysqli);
				$rowMaterial = $resultMaterial->fetch_assoc();
				//Si le produit est en stock
				if($rowMaterial['mat_stock'] !== null){
				    //Si la quantités voulu est inferieur au stock
					if($rowMaterial['mat_stock'] < $value){
						// produit n'est plus en stock, clean le panier
						unset($_SESSION['itemSession'][$key]);
						$error = true;
						break;
					}else{
					    //Update le stock dans la BD
						query('UPDATE MATERIAL SET mat_stock = ? WHERE id_mat = ?',[$rowMaterial['mat_stock'] - $value,$rowMaterial['id_mat']],$mysqli);
					}
				}
				$comm .= "<li>" . $rowMaterial['mat_name'] . " (" . $rowMaterial['mat_desc'] . ", ".$rowMaterial['mat_qty'].") " . " : " . $value . "</li>";
			}
		}
		if(!$error){
			
			$arrayDB = array();
			$arrayDB["date"] = date('Y-m-d H:i:s');
			$arrayDB["id_magasin"] = $_SESSION["mag"];
			$arrayDB["commande"] = $comm;
			$arrayDB["shipping"] = $_POST['shipping'];
			$arrayDB["paiement"] = $_POST['paiement'];
			if ($_POST['paiement']=="cheque"){
				 $arrayDB["numero_cheque"] = $_POST['numero_cheque'];
			}
			$arrayDB["cc"] = null;
			$arrayDB["exp"] = null;
			$arrayDB["ccv"] = null;
			$arrayDB["commentaires"] = $_POST['commentaires'];
			faireInsert_i($arrayDB, "MATERIAL_COMMANDE", $mysqli, 0);
			$id_mat = $mysqli->insert_id;
			
			foreach ($_SESSION['itemSession'] as $key => $value) {
				if ($value > 0) {
					$arrayMatAr = array();
					$arrayMatAr['id_mat'] = $key;
					$arrayMatAr['id_commande'] = $id_mat;
					$arrayMatAr['QTY'] = $value;
					$arrayMatAr['DATE_INSERT'] = date('Y-m-d H:i:s');
					faireInsert_i($arrayMatAr,'MATERIAL_COMMANDE_ARTICLE',$mysqli);
				}
			}
			
			
			$email_message .= $comm . "<br/>";
			$email_message .= "Livraison : " . $_POST['shipping'] . "<br/><br/>";
			if ( $_POST['paiement'] == "cheque" ){
				$email_message .= "Paiement: Chèque #" . $_POST['numero_cheque'] . "<br/><br/>";
			} else if ( $_POST['paiement'] == "credit" ){
				$email_message .= "Paiement : Crédit<br/><br/>";
			}
			$email_message .= "Commentaires : " . $_POST['commentaires'];
			
			$mail->IsSMTP();
			$mail->CharSet="UTF-8";
			$mail->SMTPSecure = 'tls';
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = 587;
			$mail->Username = EMAIL_LOGIN_USER;
			$mail->Password = EMAIL_LOGIN_PASS;
			$mail->SMTPAuth = true;
			$mail->SetFrom("noreply@animoetc.com", "Animo etc");
			
			$mail->AddReplyTo(getInfoMag("email"));
			//$mail->SetFrom(getInfoMag("email"));
			//$mail->AddAddress("alh@solution66.com");
			//$mail->AddAddress("jeanlouis@solution66.com");  
			//$mail->AddAddress("pamela@animoetc.com");
			$mail->AddAddress("guillaume@animoetc.com");
			$mail->Subject = "Animo etc - Commande Promo";
			$mail->MsgHTML($email_message);
			$mail->Send();
			
			unset($_SESSION['itemSession']);
			$mysqli->autocommit(true);
		}else{
			throw new Exception('error');
		}
	}catch(Exception $e){
		$mysqli->rollback();
		if(count($_SESSION['itemSession']) < 1){
			unset($_SESSION['itemSession']);
		}
	}
}

/* Affiche la liste des produits dans un card 
@parm $uneCat:
*/
function printListProdFromCat($uneCat,$i){

	global $mysqli;
	?>
	<? /*Set l'onglet par default*/?>
	<div class="tab-pane<?= $uneCat['ID_MATERIAL_CAT'] === 0 ? ' active' : ''?>" id="appr_<?= $uneCat['ID_MATERIAL_CAT']?>">
		<div class="row">
			<?php 
			//type de categorie
			if($uneCat['ID_MATERIAL_CAT'] !== 0){
		    	$queryMaterial = "SELECT * FROM MATERIAL WHERE ID_MATERIAL_CAT = ? and MATERIAL.show_{$_SESSION["brand"]} = 1 AND MATERIAL.deleted = 0  ORDER BY mat_name ASC";
				$resultMaterial = query($queryMaterial,[$uneCat['ID_MATERIAL_CAT']],$mysqli);
			}else{
				$queryMaterial = "SELECT * FROM MATERIAL where MATERIAL.show_{$_SESSION["brand"]} = 1  AND MATERIAL.deleted = 0 ORDER BY mat_name ASC";
				$resultMaterial = query($queryMaterial,[],$mysqli);
			}
			while($rowMaterial = $resultMaterial ->fetch_assoc()){
			    
			    //Recupere les prix  dans un tableau
				//$pieces = explode(".", $rowMaterial['mat_price']);
				?>
				<?php /*CARD*/?>
				<div class="col-12 p-1 col-md-6 col-lg-4 col-xl-3 col-xxl-2">
					<div class="card bgc-white-dark">
					    <?php /*EN-TÊTE */?>
						<div class="card-heading p-1" style="text-align:center">
							<h4 class="m-0 text-center card-title"><?= $rowMaterial['mat_name'] ?></h4>
								<?php /*PRIX SPÉCIALE 
								<?php if($rowMaterial['mat_price_spec']){ ?>
									<span style="text-decoration:line-through;">
									    <?= formatPrix(floatval($pieces[0] . '.' . $pieces[1]))?>
								    </span> <b style="color:red"><?= $rowMaterial['mat_price_spec'] ?>$</b>
								<?php }else{ ?>
									<span><?= formatPrix(floatval($pieces[0] . '.' . $pieces[1]))?></span>
								<?php }?>								
								*/?>
                                <? /*Prix speciale VS prix regulier*/?>
								<?php if($rowMaterial['mat_price_spec']){ ?>
									<span style="text-decoration:line-through;">
									    <?= formatPrix($rowMaterial['mat_price'])?>
								    </span> <b style="color:red"><?= $rowMaterial['mat_price_spec'] ?>$</b>
								<?php }else{ ?>
									<span><?= formatPrix($rowMaterial['mat_price'])?></span>
								<?php }?>
								<p><?php echo $rowMaterial['mat_qty'];?></p>
						</div>
						
						<?php /* _____________________________________________ IMAGE  _____________________________________________ */ ?>
						
						<div style="position:relative;">
							<?php if($rowMaterial['mat_stock'] !== NULL && $rowMaterial['mat_stock'] > 0){?>
								<span class="badge badge-primary fs-4 p-1" style="position:absolute;top: 4%;right:4%;background: rgba(0,0,0,0.6);">
								    <?= nfsnd($rowMaterial['mat_stock'])?> en stock</span>
							<?php }?>
							<img class="card-img-top" 
							     data-featherlight="<?= OLD_DASHBOARD?>private/img/materiel/<?php echo $rowMaterial['mat_pic'];?>?maxW=800" 
						    	 title="<?php echo $rowMaterial['mat_name'];?>" 
    							 src="<?= OLD_DASHBOARD?>private/img/materiel/<?php echo $rowMaterial['mat_pic'];?>?maxW=500&maxH=500&c=1&z=1" 
    							 alt="Card image cap">
						</div>
						
						<?php /* _____________________________________________ DESCRIPTION  _____________________________________________ */ ?>
						
						<div class="card-body text-center p-1" style="min-height: 80px;">
							<?php echo $rowMaterial['mat_desc']?>
						</div>
						
						<?php 
						/* _____________________________________________ AJOUTER AU PANIE  _____________________________________________ */ 
						// Si stock == NULL OU qu'il est > panier: show bouton
						if( $rowMaterial['mat_stock'] === NULL || $rowMaterial['mat_stock'] > $_SESSION['itemSession'][$rowMaterial['id_mat']]) { ?>
    						<a 
                                href="?p=appr_materiel_cart&articles=<?php echo $rowMaterial['id_mat'];?>" 
                                class="card-footer text-center bgc-<?= $_SESSION['itemSession'][$rowMaterial['id_mat']] > 0 ? 'primary' : 'primary'?>">
    						   <?php /* Si panier > 0  */?> 
                                <?= $_SESSION['itemSession'][$rowMaterial['id_mat']] > 0 ? 'Ajouter davantage <i class="fa fa-plus"></i>' : 'Ajouter au panier <i class="fa fa-shopping-cart"></i>'?>
    						</a>	
			        	<?php }else { ?>
							<span class="text-center card-footer bgc-warning">Rupture de stock</span>
						<?php } ?>
						
						<?php /* _____________________________________________ BTN - MODIFIER  _____________________________________________ */ ?>
						
					   <?php if(has_rights("appr_materiel_add")){?>
    						<a href="?p=appr_materiel_add&produitID=<?php echo $rowMaterial['id_mat'];?>" 
    					       class="card-footer text-center bgc-primary">
    						   <?php echo $_SESSION['itemSession'][$rowMaterial['id_mat']] > 0 ? 'Modifier' : 'Modifier'?>
    					   </a>					        
					   <? }	?>
					   
					   <?php /* _____________________________________________ BTN - SUPPRIMER  _____________________________________________ */ ?>
					   
					   <?php if(has_rights("appr_materiel_supprimer")){?>
    						<a  href="" role="button" class="card-footer text-center bgc-danger" onclick="displayModal(<?= json_encode($rowMaterial['id_mat']) ?>)">
    						   <?php echo $_SESSION['itemSession'][$rowMaterial['id_mat']] > 0 ? 'Supprimer' : 'Supprimer'?>
    					   </a>	
					   <? }	?>
					   
					</div>
				</div>
				<?php 

			}?>
		</div>
	</div>
	<?php
}

?>

<!-- =========================== ZONE HTML ======================================== --->
<section id="main" class="main-wrap bgc-white-darkest" role="main">
    <!-- Message alerte sucess -->
    <?php 
    if (isset($_SESSION["message"])) { ?>
    	<div  class="alert alert-<?= $_SESSION["message"]['type']?> text-center" style="height:50px" >
    		<?php 
    			echo '<strong>'.htmlspecialchars($_SESSION["message"]['content']).'</strong>';
    			unset($_SESSION["message"]);
    		?>
    	</div>
    <?php }  ?>
	<!-- Start SubHeader -->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<span class="hidden-xs-down">Approvisionnement</span>
		</h1>
		<div class="smart-links no-print pd-l-10px">
			<ul class="nav" role="tablist">
                <?php /*BTN AJOUTER PRODUIT*/ ?>
				<?php if(has_rights("admin_article")){?>
				<li class="nav-item">
					<a href="?p=appr_materiel_add" class="nav-link clear-style">
						<i class="fa fa-plus-circle" style="font-size: 30px;"></i>
					</a>
				</li><?php }?>
			</ul>
		</div>
	</div>	
    <!-- LISTE DES ONGLETS -->
	<div class="nav-scroll-container perfectscroll">
		<ul class="nav nav-pills nav-scroll" role="tablist">
			<li class="nav-item">
			    <a href="#appr_0" data-toggle="tab" class="nav-link active">Tous les articles</a>
		    </li>
			<?php 
			$listCategories = getAllRowsToArray(sprintf("SELECT ID_MATERIAL_CAT,CAT_%s AS NOM FROM MATERIAL_CAT WHERE INACTIF = 0 ORDER BY ORDRE ASC",strtoupper($_SESSION['lang'])),[],$mysqli);
            //echo '<pre>' , var_dump($listCategories) , '</pre>'; // to test
			foreach($listCategories as $uneCat){
				?>
				<li class="nav-item">
				    <a href="#appr_<?= $uneCat['ID_MATERIAL_CAT']?>" data-toggle="tab" class="nav-link"><?= $uneCat['NOM']?></a>
			    </li>
			<?php }	?>
		</ul>
	</div>
	<!--  -->
	<div class="row p-0 m-0">
		<section class="col-12 p-2">
			<div class="row p-0 m-0">
				<?php
				/*========================== DISPLAY MESSAGE SUCCES COMMANDE ================================= */
				
				// Si pas d'erreur et que la commande a été envoyée 
				if(!$error && $_GET['shop'] == 'done'){
					?>
					<div class="col-md-12">
						<?php msg_output("Votre commande a bien été effectuée.");?>
					</div>
					<?php
				}else if($error){
					?>
					<div class="col-md-12">
						<?php msg_output("Impossible de compléter la commande car un des produits n'est plus en stock","warning");?><br />
					</div>
					<?php
				}
				
				/*========================== DISPLAY MESSAGE  TOTAL PANIER ================================= */
				//Si panier existe
				if(isset($_SESSION['itemSession'])){
					foreach ($_SESSION['itemSession'] as $key => $value){
					    //Get total items
						$nbitem += $value;
					}
				?>
					<div class="col-md-12">
						<div class="alert alert-info droit">
							 Vous avez <strong><?php echo $nbitem; ?></strong> items dans <a href="?p=appr_materiel_cart"><strong>votre panier</strong></a>
						</div>
					</div>
				<?php
				}
				?>
				
				<?php //========================== TABLE DE PRODUITS =================================  ?>
				<div class="col-12">
					<div class="tab-content">
						<?php
						$i = 0;
						printListProdFromCat(['ID_MATERIAL_CAT'=>0],$i);
						$i++;
						foreach($listCategories as $uneCat){
							printListProdFromCat($uneCat,$i);
							$i++;
						}
						?>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>
<!-- End HTML-->

<!-- =========================== ZONE SCRIPT ======================================== --->
<script>
    function displayModal(idToDelete){
       ajaxModal("ajax/modals/appr_materiel_supprimer.php", {id_mat:idToDelete}, true)
    }
</script>
<!-- End script-->





