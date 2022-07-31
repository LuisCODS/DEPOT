<?php
if ((isset($_POST["form_pub"])) && ($_POST["form_pub"] == "sendok")) {
    
    //Stock tous les champs de la table PUBLICITE avec les  respectives clés et valeurs
	$arrayDB = array();
	
	$arrayDB["id_fournisseur"] = ($_POST['id_fournisseur']) ? $_POST['id_fournisseur'] : "0";
	$arrayDB["pub_name"] = $_POST['pub_name'];
	$arrayDB["pub_desc"] = $_POST['pub_desc'];
	$arrayDB["date_debut"] = $_POST['date_debut'];
	$arrayDB["date_exp"] = $_POST['date_exp'];
	$arrayDB["WEB"] = isset($_POST['WEB']) ? "1" : null;
	$arrayDB["PRINT"] = isset($_POST['PRINT']) ? "1" : null;
	$arrayDB["ILOT"] = isset($_POST['ILOT']) ? "1" : null;
	$arrayDB["CONCOURS"] = isset($_POST['CONCOURS']) ? "1" : null;
	$arrayDB["RADIO"] =isset($_POST['RADIO']) ? "1" : null;
	$arrayDB["MISC"] =isset( $_POST['MISC']) ? "1" : null;
	$arrayDB["pub_langue"] = $_POST["pub_langue"] == "en" ? "en" : "fr";

    /*SI le fichier ne contient pas d'erreur et qu'il ne pas vide
	if ($_FILES["pub_file"]["error"] == 0 and $_FILES["pub_file"]["name"] != "") {
		try{
		    $nomFichier = stockFileSFTP($_FILES["pub_file"],"%f","private/mkt");
			$arrayDB["pub_file"] = $nomFichier;
		}catch(Exception $e){
            die($e->getMessage());
		}
	}
	*/
	
	 /* ======================= GESTION FILE =======================  */
	 
	 /*Recuperer tous les inputs files envoyés */
	$files_inputs = array();
	/*recupere chaque nom original du fichier, tel que sur la machine du client web.*/
	foreach ($_FILES['pub_file']['name'] as $num_key => $dummy) {
	    //pour chaque fichier
	    foreach ($_FILES['pub_file'] as $txt_key => $dummy) {
	        $files_inputs[$num_key][$txt_key] = $_FILES['pub_file'][$txt_key][$num_key];
	    }
	}
	//Recupere les fichiers enregistrés au serveur
	$listFilesNames = [];
	foreach($files_inputs as $file){
	    /*SI le fichier n'a pas d'erreur et n'est pas vide*/
	    if($file['error'] == 0 and $file["name"] != ""){
	        /*Stock le fichier dans l'emplacement fournie au 3ieme paramètre et retourne le nom final du fichier qui doit etre  stocké*/
	        $new_filename = stockFileSFTP($file,"%f","private/mkt");
	        $listFilesNames[] = $new_filename;
	    }
	}

    /* ======================= GESTION IMAGE =======================  */
 
    /*SI l'image n'a pas d'erreur et ell est envoyée*/
	if ($_FILES["pub_img"]["error"] == 0 and $_FILES["pub_img"]["name"] != "") {
		try{
		    /*Stock l'image dans l'emplacement fournie au 3ieme paramètre et retourne le nom final du fichier qui doit etre  stocké*/
			$nomFichier = stockFileSFTP($_FILES["pub_img"],"%f","private/mkt");
			$arrayDB["pub_img"] = $nomFichier;
			
		}catch(Exception $e){
		    die($e->getMessage());
		}
	}
	
	 /* ============= GESTION UPDATE & INSERT- table PUBLICITE ==================*/
	try{
	    //Si on a une ID...
		if (preg_match('#^\d+$#', $_POST['id_pub'])) {
		    //Fait le UPDATE
			$arrayDB["id_pub"] = $_POST['id_pub'];
			faireUpdate_i($arrayDB, "PUBLICITE", "id_pub", $mysqli, 0);
			$message = "Publicité mise à jour avec succès";
		} else {
		    //Fait le INSERT
			$arrayDB["date_ajout"] = date("Y-m-d H:i:s");
			/*
			La methode enregistre les donnes à la table PUBLICITE
			@parm: les données, la table, le connecteur et le type de debug			
			*/
			faireInsert_i($arrayDB, "PUBLICITE", $mysqli, 0);
			//Recupere et Set la derniere ID créé pour la table PUBLICITE
			$arrayDB["id_pub"] = $mysqli->insert_id;
			$message = "Publicité ajoutée avec succès.";
		}
		
		 /* ================================ GESTION INSERT - table PUBLICITE_FILE  =============================*/
		 
		//Si la liste  des  noms des fichiers enregistrés au serveur ne sont pas vides
		if(!empty($listFilesNames)){
		    //Pour chaque nom de fichier
    	    foreach($listFilesNames as $fileName){
    	        //Pour stocker les donnes de la table PUBLICITE_FILE
    	        $array2DB = [];
    	        $array2DB['pub_file'] = $fileName;
    	        //Recupere l'ID de la table PUBLICITE pour l'associer avec la table PUBLICITE_FILE 
    	        $array2DB['id_pub'] = $arrayDB['id_pub'];
    	        $array2DB['date_insert'] = date('Y-m-d H:i:s');
    			/*
    			La methode enregistre les donnes à la table PUBLICITE_FILE
    			@parm: les données, la table et le connecteur			
    			*/
    	        faireInsert_i($array2DB,'PUBLICITE_FILE',$mysqli);
    	    }
    	}
    	$success = true;
	}catch(Exception $e){
		$error = true;
		$message = "Une erreur s'est produite";
	}
}

 /* ================================ GESTION DELETE - PUB =============================*/

if ($_POST["delete"] == "publicite" && preg_match('#^\d+$#', $_POST['id_pub'])) {
	faireDelete_i(array("id_pub" => $_POST['id_pub']), "PUBLICITE", $mysqli, 0, 1);
	$success = true;
	$message = "Publicité supprimée avec succès.";
}
?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-eye icon-mr fs-4"></i>
			<span class="hidden-xs-down">Publicités </span>
		</h1>
		<div class="smart-links">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a class="nav-link clear-style<?= !empty($_GET['show_expired']) ? ' text-primary' : ''?>" href="index.php?p=publicite<?= !empty($_GET['show_expired']) ? '' : '&show_expired=1'?>">
						<i class="fa fa-eye"></i>
					</a>
				</li>
				<?php if(has_rights("publicite_add")){?>
					<li class="nav-item">
						<a class="nav-link clear-style" href="?p=publicite_add">
							<i class="fa fa-plus-circle"></i>
						</a>
					</li>
				<?php }?>
			</ul>
		</div>
	</div>
	<div class="nav-scroll-container perfectscroll">
		<ul class="nav nav-pills nav-scroll" role="tablist">
			<li class="nav-item">
				<a href="#publicite_fr" data-toggle="tab" class="nav-link active">Français et billingue</a>
			</li>
			<li class="nav-item">
				<a href="#publicite_en" data-toggle="tab" class="nav-link">English</a>
			</li>
		</ul>
	</div>
	<!-- End SubHeader-->
	<!-- Start Content-->
	<div class="row">
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<?php if($success){
				msg_output($message);
			}else if($error){
				msg_output($message,"danger");
			}?>
			<div class="panel bgc-white-darkest pb-0" style="box-shadow:none;">
				<div class="panel-body panel-body-p" id="listPromo">
					<div class="tab-content">
						<div class="tab-pane active bgc-white-dark" id="publicite_fr">
							<div class="table-responsive">
								<table class="table table-condensed table-hover">
									<thead>
										<tr>
										<th>
											Fournisseur
										</th>
										<th>
											Nom pub.
										</th>
										<th class="centre">
											Image pub
										</th>
										<th class="centre">
											Types
										</th>
										<th class="centre">
											En vigueur du / au
										</th>
										<?/*<th class="centre">
											Fichier
										</th>*/?>
										<?php
											if(has_rights("publicite_add")){
											?>
											<th class="centre">
											Modifier
											</th>
										<?php }?>
										</tr>
									</thead>
									<tbody>
										<?php
										$daterange = !empty($_GET['show_expired']) ? '' : '(date_exp >= now() OR date_exp is null) and';
										$queryPub = "SELECT * FROM PUBLICITE
													LEFT JOIN FOURNISSEURS using(id_fournisseur)
													WHERE $daterange pub_langue in('fr','bi')
													ORDER BY date_debut is not null AND date_exp is not null DESC,date_exp ASC,
															date_debut is null AND date_exp is not null DESC, date_exp ASC,
															date_debut is not null and date_exp is null DESC, date_debut ASC,
															date_debut is null and date_exp is null DESC, pub_name ASC
															";
										$resultPub= $mysqli->query($queryPub);
										if($resultPub->num_rows > 0){
											while($rowPub = $resultPub->fetch_assoc()){
												?>
												<tr>
													<td><?php if($rowPub["id_fournisseur"] != "" && $rowPub["id_fournisseur"] != 999){ echo $rowPub["nom"]; }else{ echo 'Animo etc'; }?></td>
													<td><b><a href="?p=publicite_single&id_pub=<?php echo $rowPub['id_pub']?>"><?php echo $rowPub['pub_name'];?></a></b></td>
													<td class="centre">
														<?php if($rowPub['pub_img'] !=''){?>
															<a data-featherlight="image" href="private/mkt/<?php echo $rowPub['pub_img'];?>?maxW=800"><i class="fa fa-image"></i></a>
														<?php }else{?>
															Pas d'image
														<?php }?>
													</td>
													<td class="centre">
														<?php
														$types = [
															"WEB" => "Web",
																"PRINT" => "Imprimé",
																"ILOT" => "Îlot central",
																"CONCOURS" => "Concours",
																"RADIO" => "Radio/TV",
																"MISC" => "Autre"
														];
														$i = 0;
														foreach($types as $key => $type){
															if($rowPub[$key] == 1){
																if($i !== 0){
																	echo ", ";
																}
																echo $type;
																$i++;
															}
														}
														?>
													</td>
													<td class="centre">
														<?php
														if($rowPub['date_debut'] != ""){
															if($rowPub["date_exp"] != ""){

																printf("%s%s - %s%s",
																		(strtotime($rowPub["date_exp"]) < time() ? "<span style='color: red;font-weight:bold'>" : ""),
																		formatDateUTF8($rowPub['date_debut'],'%e %B'),
																		formatDateUTF8($rowPub['date_exp'],'%e %B'),
																		(strtotime($rowPub["date_exp"]) < time() ? "</span>" : ""));
															}else{
																echo "À partir du ".formatDateUTF8($rowPub['date_debut'],'%e %B');
															}
														}else if($rowPub["date_exp"] != ""){
															printf("%sJusqu'au %s%s",
																	(strtotime($rowPub["date_exp"]) < time() ? "<span style='color: red;font-weight:bold'>" : ""),
																	formatDateUTF8($rowPub['date_exp'],'%e %B'),
																	(strtotime($rowPub["date_exp"]) < time() ? "</span>": ""));
														}
														?>
														<?php ?>
													</td>
													<?/*
													<td class="centre">
														<?php if($rowPub['pub_file'] !=''){?><a class="ajaxModal mix-link" href="private/mkt/<?php echo $rowPub['pub_file'];?>?download=1" target="_blank"><i class="fa fa-file-pdf-o"></i></a><?php }?>
													</td>													
													*/?>

													
													<?php
													if(has_rights("publicite_add")){?>

													<td class="centre" style="width:1%;">
														<a href="?p=publicite_add&id_pub=<?php echo $rowPub['id_pub'];?>" title="<?php echo $rowPub['pub_name'];?>" rel="pub" data-rel="fancybox-button"><i class="fa fa-gear"></i></a>
													</td>
													<?php }?>
												</tr>
												<?php
											}
										}else{
											?>
											<tr>
												<td colspan="<?= $_SESSION["mag"] == "5" ? 7 : 6?>">
													Aucune publicité à afficher
												</td>
											</tr>
											<?php
										}

										?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane bgc-white-dark" id="publicite_en">
							<div class="table-responsive">
								<table class="table table-condensed table-hover">
									<thead>
										<tr>
										<th>
											Fournisseur
										</th>
										<th>
											Nom pub.
										</th>
										<th class="centre">
											Image pub
										</th>
										<th class="centre">
											Types
										</th>
										<th class="centre">
											En vigueur du / au
										</th>
										<th class="centre">
											Fichier
										</th>
										<?php
										if(has_rights("publicite_add")){
											?>
											<th class="centre">
											Modifier
											</th>
										<?php }?>
										</tr>
									</thead>
									<tbody>
										<?php
										$queryPub = "SELECT * FROM PUBLICITE
													LEFT JOIN FOURNISSEURS using(id_fournisseur)
													WHERE $daterange pub_langue in('en')
													ORDER BY date_debut is not null AND date_exp is not null DESC,date_exp ASC,
															date_debut is null AND date_exp is not null DESC, date_exp ASC,
															date_debut is not null and date_exp is null DESC, date_debut ASC,
															date_debut is null and date_exp is null DESC, pub_name ASC
															";
										$resultPub= $mysqli->query($queryPub);
										if($resultPub->num_rows > 0){
											while($rowPub = $resultPub->fetch_assoc()){
												?>
												<tr>
													<td><?php if($rowPub["id_fournisseur"] != "" && $rowPub["id_fournisseur"] != 999){ echo $rowPub["nom"]; }else{ echo 'Animo etc'; }?></td>
													<td><b><a href="?p=publicite_single&id_pub=<?php echo $rowPub['id_pub']?>"><?php echo $rowPub['pub_name'];?></a></b></td>
													<td class="centre">
														<?php if($rowPub['pub_img'] !=''){?>
															<a data-featherlight="image" href="<?= OLD_DASHBOARD?>private/mkt/<?php echo $rowPub['pub_img'];?>?maxW=800"><i class="fa fa-image"></i></a>
														<?php }else{?>
															Pas d'image
														<?php }?>
													</td>
													<td class="centre">
														<?php
														$types = [
															"WEB" => "Web",
																"PRINT" => "Imprimé",
																"ILOT" => "Îlot central",
																"CONCOURS" => "Concours",
																"RADIO" => "Radio/TV",
																"MISC" => "Autre"
														];
														$i = 0;
														foreach($types as $key => $type){
															if($rowPub[$key] == 1){
																if($i !== 0){
																	echo ", ";
																}
																echo $type;
																$i++;
															}
														}
														?>
													</td>
													<td class="centre">
														<?php
														if($rowPub['date_debut'] != ""){
															if($rowPub["date_exp"] != ""){

																printf("%s%s - %s%s",
																		(strtotime($rowPub["date_exp"]) < time() ? "<span style='color: red;font-weight:bold'>" : ""),
																		formatDateUTF8($rowPub['date_debut'],'%e %B'),
																		formatDateUTF8($rowPub['date_exp'],'%e %B'),
																		(strtotime($rowPub["date_exp"]) < time() ? "</span>" : ""));
															}else{
																echo "À partir du ".formatDateUTF8($rowPub['date_debut'],'%e %B');
															}
														}else if($rowPub["date_exp"] != ""){
															printf("%sJusqu'au %s%s",
																	(strtotime($rowPub["date_exp"]) < time() ? "<span style='color: red;font-weight:bold'>" : ""),
																	formatDateUTF8($rowPub['date_exp'],'%e %B'),
																	(strtotime($rowPub["date_exp"]) < time() ? "</span>": ""));
														}
														?>
														<?php ?>
													</td>
													<td class="centre">
														<?php if($rowPub['pub_file'] !=''){?><a class="ajaxModal mix-link" href="private/mkt/<?php echo $rowPub['pub_file'];?>?download=1" target="_blank"><i class="fa fa-file-pdf-o"></i></a><?php }?>
													</td>
													<?php
													if(has_rights("publicite_add")){?>

													<td class="centre" style="width:1%;">
														<a href="?p=publicite_add&id_pub=<?php echo $rowPub['id_pub'];?>" title="<?php echo $rowPub['pub_name'];?>" rel="pub" data-rel="fancybox-button"><i class="fa fa-gear"></i></a>
													</td>
													<?php }?>
												</tr>
												<?php
											}
										}else{
											?>
											<tr>
												<td colspan="<?= $_SESSION["ID_MAGASIN"] == "5" ? 7 : 6?>">
													Aucune publicité à afficher
												</td>
											</tr>
											<?php
										}

										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<!-- End Content-->
</section>