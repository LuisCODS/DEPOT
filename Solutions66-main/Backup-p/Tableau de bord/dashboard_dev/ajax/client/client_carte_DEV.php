<?php
require_once(__DIR__.'/../../req/init.php');

$ID_CLIENT = $_POST['ID_CLIENT'] ?: $_REQUEST['id'];

if ( $_SESSION["brand"] == "animo" ){
	$queryclient = "SELECT CLIENT.*
					  FROM CLIENT
						   JOIN CLIENT_MAGASIN using(ID_CLIENT)
					 WHERE CLIENT.ID_CLIENT = ? and CLIENT_MAGASIN.ID_MAGASIN in (select mag.ID_MAGASIN from MAGASIN as mag where BRAND = 'animo')";
	$resultclient = query($queryclient,[$ID_CLIENT],$mysqli);
} else {
	$queryclient = "SELECT CLIENT.*
					  FROM CLIENT
						   JOIN CLIENT_MAGASIN using(ID_CLIENT)
					 WHERE CLIENT.ID_CLIENT = ? and CLIENT_MAGASIN.ID_MAGASIN = ?";
	$resultclient = query($queryclient,[$ID_CLIENT,$_SESSION["mag"]],$mysqli);
}
$rowclient = $resultclient->fetch_assoc();

if(preg_match('#^\d+$#',$rowclient["cartedepoint"])){
	// a déjà une carte valide
	$mode = "edit";
}else{
	$mode = "add";
}
if ((isset($_POST["form_carte"])) && ($_POST["form_carte"] == "sendok")) {
	ob_start();
	try{
		$arrayDB = array();
		$arrayDB["cartedepoint"] = preg_replace('#\s+#', '', $_POST['cartedepoint']);
		if($mode == "add"){
			if(preg_match('#^\d+#',$arrayDB["cartedepoint"]) && in_array(strlen($arrayDB["cartedepoint"]),[8,9,12])){
				// ok!
			}else{
				throw new Exception("Numéro de carte Avantages invalide. Le numéro doit correspondre à celui d'une carte Avantages Animo etc, Voyage Vasco ou Jack Steel le Barbier d'une longueur de 8, 9 ou 12 chiffres.");
			}
		}
		if (preg_match('#^\d+$#', $_POST['ID_CLIENT'])) {
			$arrayDB["ID_CLIENT"] = $_POST['ID_CLIENT'];

			//Test si existe deja
			$enonce = "select * from CLIENT where ID_CLIENT != ? and cartedepoint = ?";
			$resultTestExists = query($enonce,[$arrayDB['ID_CLIENT'],$arrayDB["cartedepoint"]],$mysqli);
			if ( $uneLigneExists = $resultTestExists->fetch_assoc() ){
				$erreurCarteExists = true;
				$reponse = ["status"=>"error","cause"=>"card_exists", "html"=>ob_get_clean()];
			} else {
				faireUpdate_i($arrayDB, "CLIENT", "ID_CLIENT", $mysqli, 0);
				$reponse = ["status"=>"success","html"=>ob_get_clean()];
				assign_store_to_client($arrayDB["ID_CLIENT"]);
			}
		}else{
			$reponse = ["status"=>"error","cause"=>"no_client","html"=>ob_get_clean()];
		}
	}catch(Exception $e){
		$reponse = ["status"=>"error","txt_error"=>$e->getMessage(),"html"=>ob_get_clean()];
	}
	echo json_encode($reponse);
	die();
}
if ((isset($_POST["del_achat_fidele"])) && ($_POST["del_achat_fidele"] == "sendok")) {
	ob_start();
	try{
		$arrayDB = array();
		$arrayDB["id_achat_fidele"] = $_POST['id_achat_fidele'];
		$arrayDB["id_achat_fidele_gratuit"] = "-1";
		$arrayDB["note"] = "Deleted by user";
		faireUpdate_i($arrayDB, "achat_fidele", "id_achat_fidele", $mysqli, 0);
		$reponse = ["status"=>"success","html"=>ob_get_clean()];
	}catch(Exception $e){
		$reponse = ["status"=>"error","txt_error"=>$e->getMessage(),"html"=>ob_get_clean()];
	}
	echo json_encode($reponse);
	die();
}
?>
<div class="row">
<?php if($mode != "add"){?>
<div class="col-lg-8">
	<div class="portlet sale-summary mb-2">
	    <!-- ================================= Historique de la carte ================================= -->
		<div class="portlet-title">
			<div class="caption">
				Historique de la carte
			</div>
			<div class="tools">
				<a class="reload" href="javascript:;"></a>
			</div>
		</div>
		<!-- ================== TABLE ================== -->
		<div class="portlet-body" style="height:600px;overflow:auto;">
			<div class="table-responsive slimscroll">
				<div class="rTable">
					<div class="rTableHeading">
						<div class="rTableRow">
							<div class="rTableHead"><?php echo $L["date"];?></div>
							<div class="rTableHead centre"><?php echo $L["facture"];?></div>
							<div class="rTableHead centre">Magasin</div>
							<div class="rTableHead text-right"><?php echo $L["points"];?></div>
						</div>
					</div>
					<div class="rTableBody">
						<div class="rTableRow rTableHeadingClone">
							<div class="rTableHead"><?php echo $L["date"];?></div>
							<div class="rTableHead centre"><?php echo $L["facture"];?></div>
							<div class="rTableHead centre">Magasin</div>
							<div class="rTableHead text-right"><?php echo $L["points"];?></div>
						</div>
						<?php
						if ( $_SESSION["brand"] == "animo" ){
							$query = "SELECT POINTS.*, MAGASIN.M_NOM
										FROM POINTS
											 left join MAGASIN using(ID_MAGASIN)
										WHERE num_avantages = ? and ID_MAGASIN in (select mag.ID_MAGASIN from MAGASIN as mag where BRAND = 'animo')
										ORDER BY DATE_INSERT DESC";
							$resultpoints = query($query,[$rowclient['cartedepoint']],$mysqli);
						} else {
							$query = "SELECT POINTS.*, MAGASIN.M_NOM
										FROM POINTS
											 left join MAGASIN using(ID_MAGASIN)
										WHERE num_avantages = ?
											and ID_MAGASIN = ?
										ORDER BY DATE_INSERT DESC";
							$resultpoints = query($query,[$rowclient['cartedepoint'],$_SESSION["mag"]],$mysqli);
						}
						while($rowpoints = $resultpoints->fetch_assoc()){
							?>
							<div class="rTableRow">
								<div class="rTableCell"><?php echo $rowpoints['DATE_INSERT'];?></div>
								<div class="rTableCell">
									<a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?php echo $rowpoints['id_facture'];?>&ID_MAGASIN=<?php echo $rowpoints['ID_MAGASIN'];?>"><?php echo $rowpoints['id_facture'];?></a>
								</div>
								<div class="rTableCell">
									<?php echo $rowpoints["M_NOM"]?>
								</div>
								<div class="rTableCell text-right"><?php echo nfs($rowpoints['points']);?></div>
							</div>
							<?php
						}?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
	<?php
}?>
<div class="col-lg-4">
    <!-- ==================  FORM  ================== -->
	<form method="POST" onsubmit="updateCarte(this,event)">
		<div class="portlet sale-summary mb-1">
		    <!-- ==================  Numéro de carte  ================== -->
			<div class="portlet-title">
				<div class="caption">
					Numéro de carte
				</div>
				<?php if($mode == "edit" || filter_var($rowclient["EMAIL"],FILTER_VALIDATE_EMAIL)){?>
				<div class="actions" style="margin-top:-3px;position:relative;">
					<button type="submit" class="btn bgc-success"><i class="fa fa-check"></i> <?php echo $L['savechange'];?></button>
				</div>
				<?php }?>
			</div>
			<div class="portlet-body form">
				<div class="form-body">
					<div class="form-group">
						<?php 
						if($mode == "edit" || filter_var($rowclient["EMAIL"],FILTER_VALIDATE_EMAIL)){
						    // afficher un message comme quoi la carte peut être invalide.
						    if(!empty($rowclient['cartedepoint']) && !in_array(strlen($rowclient['cartedepoint']),[8,9,12])){
						        ?>
						        <div class="py-1">
						        	<?php msg_output("La carte Avantages actuellement utilisée est invalide. Cette carte ne doit pas être utilisée.","warning")?>
						        </div>
						        <?php
						    }
						    ?>
							<input name="cartedepoint" placeholder="30XXXXXXXXXX" class="form-control" type="text" value="<?php echo $rowclient['cartedepoint']?>" />
							<?php if($mode == "add" && filter_var($rowclient["EMAIL"],FILTER_VALIDATE_EMAIL)){?>
								<div class="pt-1"><small class="lh-0 fs-7">Ajouter un numéro de carte Avantages valide débloquera automatiquement l'accès aux sections Sommaire des ventes et Liste d'achats de la fiche client.</small></div>
							<?php }
							
							if ($mode == "edit" && filter_var($rowclient["EMAIL"],FILTER_VALIDATE_EMAIL)){
								?><button type="button" class="btn bgc-info mt-1" onclick="sendEmailRecuperationClient(this)">Envoyer courriel récupération</button>
								<div id="messageEmailRecup"></div>
								<script>
								function sendEmailRecuperationClient(srcBtn){
									if (srcBtn.locked){return}
									srcBtn.locked = true;
									srcBtn.htmlOri = $(srcBtn).html();
									$(srcBtn).html("Envoie en cours...");
									$.post("ajax/client/sendEmailRecup.php",{"action":"sendEmailRecup","ID_CLIENT":<?= json_encode($rowclient['ID_CLIENT']) ?>},function(r){
										if ( r ){
											if ( r.status == "success" ){
												var divConteneur = createEx("div",{className:"alert alert-primary"});
												divConteneur.innerHTML = "Courriel envoyé avec succès.";
											} else {
												var divConteneur = createEx("div",{className:"alert alert-danger"});
												divConteneur.innerHTML = "Une erreur s'est produite durant l'envoie du courriel.";
											}
											$("#messageEmailRecup").empty().append(divConteneur);
										}
										srcBtn.locked = false;
										$(srcBtn).html(srcBtn.htmlOri);
									});
								}
								</script>
								<?php 
							}
						} else {
							msg_output("Le client doit disposer d'une adresse de courriel valide pour disposer d'une carte Avantages","warning");
						}?>
					</div>
				</div>
				<div class="form-actions right">
					<input name="form_carte" type="hidden" value="sendok" />
					<input name="ID_CLIENT" type="hidden" value="<?php echo $rowclient['ID_CLIENT']?>" />
				</div>
			</div>
		</div>
	</form>
	<?php
	if($rowclient['cartedepoint'] !=''){
		$query = "SELECT COALESCE(SUM(points),0) as pointTotal 
	              FROM POINTS
	              WHERE num_avantages = '".str_replace(' ', '',$rowclient['cartedepoint'])."'";
		$resultpoints = $mysqli->query($query);
		$rowpoints = $resultpoints->fetch_assoc();
		?>
		<!-- ================== Programme Avantages ================== -->
		<div class="portlet sale-summary mb-1">
			<div class="portlet-title">
				<div class="caption">
					<?php echo $L['ProgrammeAvantages'];?>
				</div>
				<div class="tools">
					<a class="reload" href="javascript:;"></a>
				</div>
			</div>
			<div class="portlet-body">
				<ul class="list-unstyled" style="margin: 0 10px;">
					<li>
						<span class="sale-info"><?php echo $L['DollarsAnimo'];?></span>
						<span class="sale-num"><?php echo money_format('%n', $rowpoints['pointTotal']) ?></span>
					</li>
				</ul>
			</div>
		</div>
        <!-- ================== Programme Fidélité  ================== -->
		<div class="portlet sale-summary mb-1">
			<div class="portlet-title">
				<div class="caption">
					<?php echo $L['ProgrammeFidelite'];?>
				</div>
				<div class="tools">
					<a class="reload" href="javascript:;"></a>
				</div>
			</div>
			<div class="portlet-body">
				<div class="panel-group accordion" id="accordion1">

					<!-- Groupe FIDÉLITÉ-->
					<?php
					$queryfidele = "SELECT groupe_fidele.id_groupe_fidele, groupe.*, groupe_fidele.nbAvantGratuit,
										COUNT(achat_fidele.id_achat_fidele) AS nbre
									FROM achat_fidele
										LEFT JOIN animoetc_caisse_default.groupe_fidele USING(id_groupe_fidele)
										LEFT JOIN animoetc_caisse_default.groupe USING(id_groupe)
									WHERE achat_fidele.num_avantages = '".$mysqli->real_escape_string($rowclient['cartedepoint'])."'
									AND achat_fidele.id_achat_fidele_gratuit IS NULL
									AND groupe_fidele.inactif IS NULL
									GROUP BY achat_fidele.id_groupe_fidele";
					$resultfidele = $mysqli->query($queryfidele);
					while($rowfidele = $resultfidele->fetch_assoc()){
						if($rowfidele["id_groupe_fidele"] < 0 || !$rowfidele["id_groupe_fidele"]){
							continue;
						}else{
							?>
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion1" href="#collapse_<?php echo $rowfidele["id_groupe"] ?>">
											<ul class="list-unstyled" style="margin: 0 10px;">
												<li>
													<span class="sale-info" data-id-groupe-fid="<?php echo $rowfidele["id_groupe_fidele"] ?>" data-id-groupe="<?php echo $rowfidele["id_groupe"] ?>"><?php echo $rowfidele['nom'];?></span>
													<span class="sale-num"><?php echo $rowfidele['nbre'].'/'.$rowfidele['nbAvantGratuit'];; ?></span>
												</li>
											</ul>
										</a>
									</h4>
								</div>
								<div id="collapse_<?php echo $rowfidele["id_groupe"] ?>" class="panel-collapse collapse">
									<div class="panel-body">
										<?php
										$queryProduitfidele = "SELECT * 
																 FROM achat_fidele
																	  LEFT JOIN animoetc_caisse_default.article USING(id_article)
																WHERE id_groupe_fidele = '".$rowfidele["id_groupe_fidele"]."'
																  AND num_avantages = '".$mysqli->real_escape_string($rowclient['cartedepoint'])."'
																  AND id_achat_fidele_gratuit IS NULL";
										$resultProduitfidele = $mysqli->query($queryProduitfidele);
										while($rowProduitfidele = $resultProduitfidele->fetch_assoc()){
											echo '<p style="margin-left:20px">'.$rowProduitfidele['desc_fr'].' <a href="javascript:;" onclick="deleteAchatFidele('.$rowProduitfidele['id_achat_fidele'].')"><span class="fa fa-trash"></a></p>';
										}
										
										
										if(get_current_security_level() <= 2){
											?>
											<p style="margin-left:20px">
												<a data-modal-url="ajax/modals/client_carte_addCodeBar.php?ajouterCodeBarFid=1&id_client=<?= $rowclient["ID_CLIENT"] ?>&id_groupe_fidele=<?= $rowfidele["id_groupe_fidele"] ?>&cartedepoint=<?= $rowclient["cartedepoint"] ?>" 
													href="javascript:;" class="ajaxPopup" >Ajouter un code barre
												</a>
											</p>
											<?php 
										}?>
										
									</div>
								</div>
							</div>
							<?php
						}
					}?>
				</div>
			</div>
		</div>
        <!-- ==================  Gratuité reçue   ================== -->
		<div class="portlet sale-summary mb-1">
			<div class="portlet-title">
				<div class="caption">
					<?php echo $L['gratuiterecu'];?>
				</div>
				<div class="tools">
					<a class="reload" href="javascript:;"></a>
				</div>
			</div>
			<div class="portlet-body">
				<table class="table table-condensed">
					<thead>
						<tr>
							<th><?php echo $L["Date"];?></th>
							<th class="centre"><?php echo $L["facture"];?></th>
						</tr>
					</thead>
					<tbody>
					<!--  achat_fidele_gratuit-->
					<?php
					$querygratuit = "SELECT achat_fidele_gratuit.*, groupe.nom
									FROM achat_fidele_gratuit
									LEFT JOIN animoetc_caisse_default.groupe_fidele USING(id_groupe_fidele)
									LEFT JOIN animoetc_caisse_default.groupe USING(id_groupe)
									WHERE achat_fidele_gratuit.num_avantages = '".$mysqli->real_escape_string($rowclient['cartedepoint'])."' 
									ORDER BY achat_fidele_gratuit.id_groupe_fidele < 0  ASC, `achat_fidele_gratuit`.`date_insert` DESC";
					$resultgratuit = $mysqli->query($querygratuit);
					
					while($rowgratuit = $resultgratuit->fetch_assoc()){
					   //Groupe FIDÉLITÉ maison 
						if($rowgratuit["id_groupe_fidele"] < 0){
						    
 						    //Requête pour aller chercher le nom des articles: si id_groupe_fidele est négatif, cela correspond  l'id de l'article.
 						    //...faut juste enlever le signe negative pour trouver les articles.
							$queryArticle = "SELECT desc_fr, PLU,id_article
    									  	 FROM animoetc_caisse_default.article 
    										 WHERE id_article  = ? ";
					    	$resultArticle = query($queryArticle,[ Abs($rowgratuit["id_groupe_fidele"]) ],$mysqli );
					    	
					    	while($rowArticle = $resultArticle->fetch_assoc()){
					    	    
					    	$varTOConcat = "Fidélité AnimoEtc 9+1 \n ". formatDateUTF8($rowgratuit['date_insert']) ."\n";
					    	?>
  							<tr>
								<td>
								    <?= nl2br($varTOConcat); ?> 
								    <a href="?p=produits&id=<?= urlencode($rowArticle['id_article'])?>"><?= $rowArticle['desc_fr'];?></a>
								</td>
								<td class="centre">
								    <a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?php echo $rowgratuit['id_facture']; ?>&ID_MAGASIN=<?php echo $rowgratuit['ID_MAGASIN'];?>"><?php echo $rowgratuit['id_facture']; ?>
								    </a>
							    </td>
							</tr>  					    	   
					        <?php } ?>

							<?php
						}else{
							?>
							<tr>
								<td>
								    <?= $rowgratuit['nom'];?><br/>
								    <a href="?p=rap_fidelite_details&id_achat_fidele_gratuit=<?= $rowgratuit['id_achat_fidele_gratuit'];?>&id_groupe_fidele=<?= $rowgratuit['id_groupe_fidele'];?>"><?= formatDateUTF8($rowgratuit['date_insert']);?>
							    </td>
								<td class="centre"><a class="ajaxModal" href="javascript:;" data-modal-url="ajax/modals/viewFacture.php?id_facture=<?= $rowgratuit['id_facture']; ?>&ID_MAGASIN=<?= $rowgratuit['ID_MAGASIN'];?>"><?php echo $rowgratuit['id_facture']; ?></a></td>
							</tr>
						<?php
						}
					}?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}?>
</div>
</div>