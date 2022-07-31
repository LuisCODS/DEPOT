<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa icon-people icon-mr fs-4"></i>
			<span class="hidden-xs-down"> Erreurs de prix</span>
		</h1>
	</div>
	<!-- End SubHeader-->
	<!-- Start Content-->
	<div class="row pl-3 pr-3 mb-3 mt-3">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<!--Start Panel-->
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible pb-2">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
								<?php
								if($_POST['form_erreurPrix'] == 'ok'){

									$arrayDB = array();
									$errors = [];
									if(preg_match('#^\d+$#',$_POST['UPC'])){
									    $arrayDB["upc"] = $_POST['UPC'];
									}else{
									    $errors[] = 'Le code UPC ne peut être vide.';
									}
									if(!empty($_POST['num_four'])){
									    $arrayDB["code_four"] = $_POST['num_four'];
									}else{
									    $errors[] = 'Le code fournisseur ne peut être vide.';
									}
									if(!empty($_POST['nom_four'])){
									    $arrayDB["fournisseur"] = $_POST['nom_four'];
									}else{
									    $errors[] = 'Veuillez spécifier un fournisseur';
									}
									if(!empty($_POST['error_type'])){
									    $arrayDB["error_type"] = $_POST['error_type'];
									}else{
									    $errors[] = "Veuillez spécifier un type d'erreur";
									}
									$arrayDB["description"] = $_POST['desc'];
									$arrayDB["cost"] = $_POST['cost'];
									$arrayDB["date_sent"] = date('Y-m-d H:i:s');
									$arrayDB["ID_MAGASIN"] = $_SESSION["mag"];
									$arrayDB["ID_STAFF"] = $_SESSION["utilisateur"]["id_utilisateur"];
									
									if(count($errors) > 0){
									    ?>
    										<div class="alert alert-danger">
    										<button type="button" class="close" data-dismiss="alert"></button>
    										<h4 class="alert-heading">Erreur</h4>
    										<p>
    										<b>Certaines informations requises sont manquantes</b>:<br />
    										<?= implode('<br />',$errors)?>
    										</p>
    										</div>
    										<?php
									}else{
    									try{
    										faireInsert_i($arrayDB,"ERREURPRIX",$mysqli,0);
    										?>
    										<div class="alert alert-success">
    										<button type="button" class="close" data-dismiss="alert"></button>
    										<h4 class="alert-heading">Merci!</h4>
    										<p>
    										Reçue avec succès.
    										</p>
    										</div>
    										<?php
    									}catch(Exception $e){
    										?>
    										<div class="alert alert-danger">
    										<button type="button" class="close" data-dismiss="alert"></button>
    										<h4 class="alert-heading">Erreur</h4>
    										<p>
    										Une erreur est survenue.
    										</p>
    										</div>
    										<?php
    									}
									}
									/*$email_to = "sys_caisse@animoetc.com";
									$email_subject = "Animo-Caisse - Erreur produit";
									$email_from = $MAG['M_EMAIL'];

									$email_message .= "Magasin : ".$_POST['magasin']."(".$_POST['staff'].")\n \n";
									$email_message .= "Code UPC: ".$_POST['UPC']."\n";
									$email_message .= "Code Fournisseur: ".$_POST['num_four']."\n";
									$email_message .= "Fournisseur: ".$_POST['nom_four']."\n";
									$email_message .= "Description: ".$_POST['desc']."\n";
									$email_message .= "Coutant: ".$_POST['cost']."\n";

									$headers = 'From: '.$email_from."\r\n".
									'Reply-To: '.$email_from."\r\n" .
									'X-Mailer: PHP/' . phpversion();
									@mail($email_to, $email_subject, $email_message, $headers);*/
								}?>
								<form method="POST">
									<div class="form-body">

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="control-label">Code UPC</label>
													<input onkeydown="handleenter(this,event)" value="<?= attrEncode($_POST["UPC"])?>" type="text" class="form-control" placeholder="000000000000" name="UPC">
													<span class="help-block">Inscrire le code CUP tel qu'indiqué sur le code-barre du produit. Ne pas oublier le premier et dernier chiffre de la séquence.</span>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="control-label">Code fournisseur</label>
													<input value="<?= attrEncode($_POST["num_four"])?>" type="text" class="form-control" placeholder="" name="num_four">
													<span class="help-block">Inscrire le code du fournisseur tel qu'indiqué sur la facture lors de la réception du produit.</span>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="control-label">Distributeur</label>
													<select class="form-control select2" id="nom_four" name="nom_four">
														<option selected value="">Choisir un distributeur</option>
														<?php
														$enonce = sprintf("select fournisseur.*
																					from fournisseur
																					where est_fournisseur is not null
																					order by nom asc" );
														$resultFour = query($enonce,[],$dbAnimoCaisseDefault);
														while ( $uneLigneFour = $resultFour->fetch_assoc()){?>
															<option <?php if( $_POST["nom_four"] == $uneLigneFour['id_fournisseur']){echo "selected ";}?>value="<?php echo $uneLigneFour['nom'];?>"><?php echo $uneLigneFour['nom'];?></option>
														<?php }?>
													</select>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="control-label">Coût d'achat</label>
													<input value="<?= attrEncode($_POST["cost"])?>" type="text" class="form-control" placeholder="0.00$" name="cost">
													<span class="help-block">Inscrire le coût d'achat tel qu'indiqué sur la facture lors de la réception du produit.</span>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="control-label">Description</label>
													<textarea class="form-control" name="desc"><?= htmlentities($_POST["desc"])?></textarea>
													<span class="help-block"><small>Description du produit, incluant la marque et la couleur s'il y a lieu.</small></span>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="control-label">Type d'erreur</label>
													<select class="form-control select2" id="error_type" name="error_type">
														<option selected value="">Choisir un type d'erreur</option>
														<option value="Produit introuvable">Produit introuvable (ne scan pas)</option>
														<option value="Erreur prix">Le prix est incorrect</option>
														<option value="Erreur description">La description du produit est erronée</option>
														<option value="Autre">Autre (SVP précisez dans la Description)</option>
													</select>
												</div>
											</div>
										</div>

									</div>
									<div class="form-actions right">
										<input type="hidden" name="staff" value="<?php echo $_SESSION["utilisateur"]["fullname"] ?>">
										<input type="hidden" name="magasin" value="<?php echo getInfoMag($_SESSION["mag"])["M_NOM"] ?>">
										<input type="hidden" name="form_erreurPrix" value="ok">
										<button type="submit" class="btn btn-success">Envoyer</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<!-- End Content-->
</section>