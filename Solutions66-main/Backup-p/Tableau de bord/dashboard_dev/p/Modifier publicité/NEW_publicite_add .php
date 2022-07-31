<?php
    /*
    echo '<pre>';
    echo htmlspecialchars(print_r($_REQUEST, true)); 
    echo'<br>';
    echo 'PUBLICITE_FILE($listPubliciteFiles): '.htmlspecialchars(print_r($listPubliciteFiles, true));
    echo'<br>';
    echo 'PUBLICITE($rowPublicite): '.htmlspecialchars(print_r($rowPublicite, true));
    echo'<br>';
    echo '</pre>';
    */

    if (!has_rights("publicite_add") ){ 
        require(__DIR__."/403.php");
        return; 
    }
    
    /*Tous les fichiers associés à la table PUBLICITE*/
    $listPubliciteFiles = [];
    
    if ( preg_match('#^\d+$#',$_GET['id_pub']) ){
    	$queryPub = "SELECT * FROM PUBLICITE WHERE id_pub = ?";
    	$resultPub = query($queryPub,[$_GET['id_pub']],$mysqli);
    	$rowPublicite = $resultPub->fetch_assoc();
    }
    
	if($rowPublicite){
	    $getFiles = query('SELECT * FROM PUBLICITE_FILE WHERE id_pub = ?',[$rowPublicite['id_pub']],$mysqli);
	    if($getFiles->num_rows > 0){
	        while($rowFilePublicite = $getFiles->fetch_assoc()){
	           $listPubliciteFiles[] = $rowFilePublicite;
	        }
	    }
	}
	
?>

<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa icon-people icon-mr fs-4"></i>
			<span class="hidden-xs-down"> Modifier publicité</span>
		</h1>
	</div>
	<!-- END PAGE HEADER-->
	<!-- BEGIN PAGE CONTENT-->
	<div class="row pl-3 pr-3 mb-3 mt-3">   
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<!--Start Panel-->
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible pb-2">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
                            	<div class="row">
                            		<div class="col-md-12">
                            		    <? /*Input hidden to get  publicite  id  */?>
                            			 <form role="form" method="POST" enctype="multipart/form-data" action="?p=publicite" id="form_delpublicite">
                            				<input name="id_pub" type="hidden" value="<?php echo $rowPublicite['id_pub'];?>" />
                            				<input name="delete" type="hidden" value="publicite" />
                            			 </form>
                            			 <? /* FORM  */?>
                            			 <form role="form" method="POST" enctype="multipart/form-data" action="?p=publicite" >
                            				<?php 
                            				/* =======  Select fournisseurs  ======= */
                            				$enonce = sprintf("select FOURNISSEURS.nom, FOURNISSEURS.id_fournisseur from FOURNISSEURS order by nom asc" );
                            				$resultFour = $mysqli->query($enonce) or die($mysqli->error);
                            				?>
                        					<?/* ================================================== Fournisseur   ================================================== */?>
                            				<div class="row">
                            					<div class="col-md-12">
                            						<label class="control-label">Fournisseur</label>
                            						<div class="form-group">
                            							<select class="ui search fluid simple-select select-dropdown ligneFourI1" name="id_fournisseur">
                            								<option value="">Choisir un fournisseur</option>
                            								<option value="999" <?php echo ($rowPublicite["id_fournisseur"]==999 ? " selected" : "")?>>Animo Etc (promotion maison)</option>
                            								<?php while ( $row_fournisseur = $resultFour->fetch_assoc()){?>
                            									<option 
                            									    value="<?php echo $row_fournisseur["id_fournisseur"] ?>" 
                            									           <?php echo ( $rowPublicite["id_fournisseur"] == $row_fournisseur["id_fournisseur"] ) ? "selected":"" ?> >
                            									           <?php echo $row_fournisseur["nom"] ?>
                            									</option>
                            								<?php }?>
                            							</select>
                            						</div>
                            					</div>				
                            				</div>
                            				<?/* ================================================== Titre   ================================================== */?>
                            				<div class="row">
                            					<div class="col-md-12">
                            						<label class="control-label">Nom de la publicité</label>
                            						<div class="form-group">
                            							<input name="pub_name" class="form-control" type="text" value="<?= $rowPublicite["pub_name"]?>" />
                            						</div>
                            					</div>				
                            				</div>
                            				<?/* ================================================== textarea   ================================================== */?>
                            				<div class="row">	
                            					<div class="col-md-12">
                            						<div class="form-group">
                            							<textarea class="editor form-control" name="pub_desc" rows="6" placeholder="Description de la publicité"><?php echo $rowPublicite['pub_desc'];?></textarea>
                            						</div>
                            					</div>
                            				</div>
                            				<?/* ================================================== Image   ================================================== */?>
                            				<?/*
                            				<div class="row">
                            					<div class="col-md-6" style="margin-bottom:25px;">
                            						<label class="control-label">Image <small><?php if($rowPub['pub_img']!=''){?>(<?php echo $rowPub['pub_img'];?>)<?php }?></small></label>
                            						<input type="file" name="pub_img">
                            					</div>
                            					<div class="col-md-6" style="margin-bottom:25px;">
                            						<label class="control-label">Fichier <small><?php if($rowPub['pub_file']!=''){?>(<?php echo $rowPub['pub_file'];?>)<?php }?></small></label>
                            						<input type="file" name="pub_file">
                            					</div>
                            				</div>                            				
                            				*/?>

                            				<?/*ce code remplace le code precedent pour ajouter plus d'input files!)*/?>
                         					<div class="row">
                         					    <?/*get image*/?>
                             					<div class="col-md-6" style="margin-bottom:25px;">
                            						<label class="control-label">
                        						    Image 
                            						    <small>
                            						        <?php if($rowPublicite['pub_img']!=''){ ?>
                            						            (<?php echo $rowPublicite['pub_img']; ?>)
                            						        <?php } ?>
                            						    </small>
                        						    </label>
                            						<input type="file" name="pub_img">
                            					</div>
                            					<?/* ================================================== Gestion files  ================================================== */?>
                        						<div class="col-md-6">
                        							<div id="listPubFichiers" class="fichiers">
                        							    <?/*$listPubliciteFiles : les fichiers associés à la table PUBLICITE*/?> 
                                						<?php foreach($listPubliciteFiles as $file){?>
                                							<div class="fichier mb-1 form-control w-100 p-1">
                                								<i class="fa fa-file"></i> <?= $file['pub_file']?>
                                							</div>
                                						<?php }?>
                                						<div class="fichier mb-1 form-control w-100 p-1">
                                							<input type="file" class="w-100" name="pub_file[]">
                                						</div>
                                					</div>
                            						<div class="">
                            							<button class="btn btn-primary mb-5" type="button" onclick="ajouterPubFile()">+</button>
                            						</div>
                        						</div>
                        					</div>
                        					<script>
                        						function ajouterPubFile(){
                        							$("#listPubFichiers").append('<div class="fichier mb-1 form-control w-100 p-1"><input type="file" class="w-100" name="pub_file[]"></div>');
                        						}
                        					</script>  
                            				<div class="row">
                            				    <?/* ================================================== Date Début ================================================== */?>
                            					<div class="col-md-3">
                            						<label class="control-label">Date Début</label>
                            						<div class="form-group">
                            							<div class="input-group date date-picker">
                            								<input type="text" size="16" class="form-control datepicker" 
                            								       value="<?php echo $rowPublicite['date_debut']; ?>" name="date_debut">
                            							</div>
                            						</div>
                            					</div>
                            					<?/* ================================================== Expiration ================================================== */?>
                            					<div class="col-md-3">
                            						<label class="control-label">Expiration</label>
                            						<div class="form-group">
                            							<div class="input-group date date-picker">
                            								<input type="text" size="16" class="form-control datepicker" 
                            								       value="<?php echo $rowPublicite['date_exp'];?>" name="date_exp">
                            							</div>
                            						</div>
                            					</div>
                            					<?/* ================================================== Langue ================================================== */?>
                            					<div class="col-md-3">
                            						<label class="control-label">Langue</label>
                            						<div class="form-group">
                            							<select class="form-control" name="pub_langue">
                            								<?php 
                            								$langues = [
                            								            "fr" => "Français",
                                        								"en" => "Anglais", 
                                        								"bi" => "Billingue"
                                        							   ];
                            								foreach($langues as $langue => $lngstr){
                            									?>  <option value="<?= $langue ?>"<?= $langue == $rowPublicite["pub_langue"] ? " selected" : ""?>><?= $lngstr?></option>  <?php
                            								}
                            								?>
                            							</select>
                            						</div>
                            					</div>
                            					<?/* ================================================== Type de publicité ================================================== */?>
                            					<div class="col-md-3">
                            						<label class="control-label">Type de publicité</label>
                            						<div class="form-group">
                            							<div class="form-check form-group">
                                                            <label class="custom-control custom-checkbox ui checkbox d-block" for="HAS_BORDER">
                                         						<input class="form-control custom-control-input" type="checkbox" <?= $rowPublicite["WEB"] == "1" ? "checked " : "" ?>name="WEB" value="1">
                                         						<span class="custom-control-indicator"></span>
                                                				<span class="custom-control-description ml-2">&nbsp; Ensemble WEB (TV, FB, NL, WEB)</span>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-group">
                                                            <label class="custom-control custom-checkbox ui checkbox d-block" for="HAS_BORDER">
                                         						<input class="form-control custom-control-input" type="checkbox" <?= $rowPublicite["PRINT"] == "1" ? "checked " : "" ?>name="PRINT" value="1">
                                         						<span class="custom-control-indicator"></span>
                                                				<span class="custom-control-description ml-2">&nbsp; Imprimé</span>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-group">
                                                            <label class="custom-control custom-checkbox ui checkbox d-block" for="HAS_BORDER">
                                         						<input class="form-control custom-control-input" type="checkbox"  <?= $rowPublicite["ILOT"] == "1" ? "checked " : "" ?>name="ILOT" value="1">
                                         						<span class="custom-control-indicator"></span>
                                                				<span class="custom-control-description ml-2">&nbsp; Ilôt central</span>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-group">
                                                            <label class="custom-control custom-checkbox ui checkbox d-block" for="HAS_BORDER">
                                         						<input class="form-control custom-control-input" type="checkbox" <?= $rowPublicite["CONCOURS"] == "1" ? "checked " : "" ?>name="CONCOURS" value="1">
                                         						<span class="custom-control-indicator"></span>
                                                				<span class="custom-control-description ml-2">&nbsp; Concours</span>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-group">
                                                            <label class="custom-control custom-checkbox ui checkbox d-block" for="HAS_BORDER">
                                         						<input class="form-control custom-control-input" type="checkbox" <?= $rowPublicite["RADIO"] == "1" ? "checked " : "" ?>name="RADIO" value="1">
                                         						<span class="custom-control-indicator"></span>
                                                				<span class="custom-control-description ml-2">&nbsp; Radio/TV</span>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-group">
                                                            <label class="custom-control custom-checkbox ui checkbox d-block" for="HAS_BORDER">
                                         						<input class="form-control custom-control-input" type="checkbox" <?= $rowPublicite["MISC"] == "1" ? "checked " : "" ?>name="MISC" value="1">
                                         						<span class="custom-control-indicator"></span>
                                                				<span class="custom-control-description ml-2">&nbsp; Autre</span>
                                                            </label>
                                                        </div>
                            						</div>
                            					</div>
                            				</div>
                            				<div class="form-actions right">
                            					<input name="id_pub" type="hidden" value="<?php echo $rowPublicite['id_pub'];?>" />
                            					<input name="date_ajout" type="hidden" value="<?php echo date('Y-m-d H:i:s');?>" />
                            					<input name="form_pub" type="hidden" value="sendok" />
                            					<button type="submit" form="form_delpublicite" class="btn btn-danger">Effacer</button>
                            					<button type="submit" class="btn btn-success">Sauvegarder</button>
                            				</div>
                            			</form>
                            		</div>
                            	</div>
                            </div>
                    	</div>
      				</div>
    			</div>
    		</div>
    	</section>
	</div>
</section>
<!-- END CONTENT -->