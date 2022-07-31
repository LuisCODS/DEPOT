<?php 
//Ce fichier fait la gestion MATERIEL pour CREATE et UPDATE en même  temps!

// Recupere all data from BD
$rowMProduit = null;
// Stock all inputs  errors
$tabErreur = array();
// Stock all new inputs data
$tabInputs = array();

//CAS UPDATE: pour nourrir les champs du form
if(isset($_GET["produitID"]) && preg_match('#^[0-9]+$#', $_GET["produitID"]) ){
    $requette = "SELECT * FROM MATERIAL WHERE id_mat = ?";
    $resultMaterial = query($requette,[$_GET["produitID"]],$mysqli);
    $rowMProduit = $resultMaterial->fetch_assoc();
}

// =================== SOUMISSION DU FORM =======================
if( isset($_POST["form_approvisionnement"]) && $_POST["form_approvisionnement"] == "envoye" ){        
   
    // _____________ VALIDATION DES CHAMPS _____________
    
    //ID PRODUIT
    if(!empty($_POST["id_mat"]) && trim($_POST["id_mat"]) != "" ){
        $tabInputs["id_mat"] = trim($_POST["id_mat"]);
    }       
    //NOM DU PRODUIT
	if(!empty(trim($_POST["mat_name"]) )){
		$tabInputs["mat_name"] = trim($_POST["mat_name"]);
	}else{
		$tabErreur["mat_name"] = "Champ obligatoire!";
	}
    //DESCRIPTION 
	if(!empty($_POST["mat_desc"]) && trim($_POST["mat_desc"]) != "" ){
		$tabInputs["mat_desc"] = trim($_POST["mat_desc"]);
	}else{
		$tabErreur["mat_desc"] = "Champ obligatoire!";
	}
    //PRIX 
	if( !empty($_POST["mat_price"]) && trim($_POST["mat_price"]) != ""  && preg_match('#^\s*[0-9\s]{1,8}([.,][0-9]{0,2})?\s*?$#', $_POST["mat_price"]) ){
	    //@preg_replace: Enleve les whitespace du prix et remplace par ''
	    //@str_replace : Remplace les virgules par les points 
    	$tabInputs["mat_price"] = str_replace(',','.',preg_replace('#\s+#','',$_POST["mat_price"]));
	}else{
	    $tabErreur["mat_price"] =  "Champ obligatoire!";
	}		
    //PRICE SPÉCIAL 
	if( empty($_POST["mat_price_spec"]) ){
	    $tabInputs["mat_price_spec"] = "";
	}else if(!empty($_POST["mat_price_spec"]) && trim($_POST["mat_price_spec"]) != "" && preg_match('#^\s*[0-9\s]{1,8}([.,][0-9]{0,2})?\s*?$#', $_POST["mat_price_spec"]) ){
	    $tabInputs["mat_price_spec"] = str_replace(',','.',preg_replace('#\s+#','',$_POST["mat_price_spec"]));
	}		
	//QUANTITÉ
	if(!empty($_POST["mat_qty"]) && trim($_POST["mat_qty"]) != "" ){
		$tabInputs["mat_qty"] = trim($_POST["mat_qty"]);
	}else{
		$tabErreur["mat_qty"] = "Champ obligatoire!";
	}		
    //CATEGORIE
	if(!empty($_POST["ID_MATERIAL_CAT"]) && trim($_POST["ID_MATERIAL_CAT"]) != "" ){
		$tabInputs["ID_MATERIAL_CAT"] = trim($_POST["ID_MATERIAL_CAT"]);
	}else{
		$tabErreur["ID_MATERIAL_CAT"] = "Champ obligatoire!";
	}		
    //STOCK 
	if(!empty($_POST["mat_stock"]) && preg_match('#^[0-9]+$#', $_POST["mat_stock"]) ){
		$tabInputs["mat_stock"] = trim($_POST["mat_stock"]);
	}else{
		//$tabInputs["mat_stock"] = 0;
		$tabErreur["mat_stock"] = "Champ obligatoire!";
	}		
    //show_animo: si coché, on mets 1 dans la DB, et si pas coché on mets 0
	if(isset($_POST["show_animo"])){
		$tabInputs["show_animo"] = 1;
	}else{
		$tabInputs["show_animo"] = 0;
	}	
    //show_groupea: si coché, on mets 1 dans la DB, et si pas coché on mets 0
	if(isset($_POST["show_groupea"]) ){
		$tabInputs["show_groupea"] = 1;
	}else{
		$tabInputs["show_groupea"] =  0;
	}	
	// DELETED
	if(isset($_POST["deleted"])){
		$tabInputs["deleted"] = 0;
	}	
	// CAS CREATE sans envoie de photo
	if($_FILES['mat_pic']['tmp_name'] == "" && $rowMProduit['mat_pic'] == ""){
	     $tabErreur["mat_pic"] = "Champ obligatoire!";
	}
    // CAS PHOTO envoyée
    if($_FILES['mat_pic']['tmp_name'] !== "" && $_FILES['mat_pic']['error'] == 0){
        //Get Nom fichier coté client
        $nomFichierClient = trim(strtolower($_FILES['mat_pic']['name']));
        // Get extension du fichier
        $extension = strrchr($nomFichierClient,'.');
        //Valide l'extension
        if(in_array($extension, [".jpg",".png",".jpeg",".gif",".bmp"]) ){
            //Nouveau nom du fichier à stocker
            $tabInputs["mat_pic"] = sha1($nomFichierClient.time()).$extension; 
            //Destination du fichier
            $dossier = __DIR__ ."/../private/img/materiel/"; 
            //Recupere le nom temporaire
            $fichier_Temp = $_FILES['mat_pic']['tmp_name'];
            //Stock le fichier
            move_uploaded_file($fichier_Temp,$dossier.$tabInputs["mat_pic"]);
        }else{
            $tabErreur["mat_pic"] = "Extension d'image invalide!";
        }
        //CAS UPDATE- il veut changer de photo
        if($rowMProduit['mat_pic'] != ""){
            //Recupere la photo courante
            $photoToDelete = $rowMProduit['mat_pic'];
            $cheminPhoto = __DIR__ .'/../private/img/materiel/'.$photoToDelete;
            if(is_file($cheminPhoto)) {
                //Supprime ancienne 
                unlink($cheminPhoto);               
            }
        }
    }
    //CAS UPDATE sans envoie de photo 
    if($_FILES['mat_pic']['tmp_name'] == "" && $rowMProduit['mat_pic'] != "" ){
        //Garde la courante
        $tabInputs["mat_pic"] = $rowMProduit['mat_pic'];
    }
    //SI pas d'erreur dans l'ensemble des champs
    if(count($tabErreur) == 0){
        if(isset($rowMProduit["id_mat"]) && preg_match('#^[0-9]+$#', $rowMProduit["id_mat"]) ){
            // UPDATE
            faireUpdate_i($tabInputs,"MATERIAL","id_mat",$mysqli,0);
            $_SESSION["message"] = ["content"=>"Produit édité avec succès!","type"=>"success"];
        }else{
            // CREATE
            faireInsert_i($tabInputs,"MATERIAL",$mysqli,0); 
            $_SESSION["message"] = ["content"=>"Produit enregistré avec succès!","type"=>"success"];
        }     
        redirect("index.php?p=appr_materiel",0);
    }

}else if ($rowMProduit) {
    //CAS UPDATE: nourrir les champs du form
    $tabInputs = $rowMProduit;
}

?>

<!-- ===================================== ZONE HTML ========================================  -->
<section id="main" class="main-wrap bgc-white-darkest" role="main">
  <!-- Start SubHeader-->
  <div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
    <h1 class="page-title pull-left fs-4 fw-light">
         <?= isset($_GET["produitID"])  ? 'Édition produit' : 'Ajouter produit' ?>
    </h1>
		<div class="smart-links">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<a class="nav-link clear-style" href="?p=appr_materiel">
						<i class="fa fa-arrow-left" style="font-size: 15px;" aria-hidden="true">  Retourner</i>
					</a>
				</li>
			</ul>
		</div>    			
			
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
                <div class="container-fluid">
                  <?php /* ========== FORM ==========*/ ?>
                  <form  action="" method="POST" enctype="multipart/form-data">
                    <?php /*ROW*/ ?>
                    <div class="row">
                      <?php /* _________ COL ___________*/ ?>
                      <div class="col-md-6">
                        <input type="hidden" name="form_approvisionnement" value="envoye">
                        <input type="hidden" name="id_mat" value="<?=  isset($rowMProduit["id_mat"]) ?  htmlspecialchars($rowMProduit["id_mat"]) : '' ?>" >
                        <input type="hidden" name="deleted" value="<?=  isset($rowMProduit["deleted"]) ?  htmlspecialchars($rowMProduit["deleted"]) : '' ?>" >
                        <?php /* _____________________________________________ mat_name _____________________________________________ */ ?>
                        <div class="form-group">
                          <label for="mat_name" class="">
                            Nom produit
                            <input type="text" class="form-control" name="mat_name" size="80" 
                                   value="<?=  isset($tabInputs["mat_name"]) || isset($tabInputs["mat_name"]) ? htmlspecialchars($tabInputs["mat_name"]) : '' ?>" required>
                          </label>
                          <span class="errorMessageInput" style="color: #FF0000"><?php if (isset($tabErreur["mat_name"] )) echo $tabErreur["mat_name"] ;?></span>
                        </div>
                        <?php /* _____________________________________________ mat_desc _____________________________________________ */ ?>
                        <div class="form-group">
                          <label for="mat_desc" class="">
                            Description
                                <input type="text" class="form-control" name="mat_desc" size="80" 
                                       value="<?=  isset($tabInputs["mat_desc"]) ?  htmlspecialchars($tabInputs["mat_desc"]) : '' ?>" required>
                          </label>
                          <span class="errorMessageInput" style="color: #FF0000"><?php if (isset($tabErreur["mat_desc"] )) echo $tabErreur["mat_desc"] ;?></span>
                        </div>
                        <?php /* _____________________________________________ mat_price _____________________________________________ */ ?>
                        <div class="form-group">
                          <label for="mat_price" class="">
                            Prix
                            <input type="text" class="form-control" name="mat_price" size="80"  placeholder="00,00" pattern="\s*[0-9\s]{1,8}([.,][0-9]{0,2})?\s*?"
                                    value="<?= isset($tabInputs["mat_price"]) ? htmlspecialchars($tabInputs["mat_price"]) : '' ?>" required>
                          </label>
                          <span class="errorMessageInput" style="color: #FF0000"><?php if (isset($tabErreur["mat_price"] )) echo $tabErreur["mat_price"] ;?></span>
                        </div>
                        <?php /* _____________________________________________ mat_price_spec _____________________________________________ */ ?>
                        <div class="form-group">
                          <label for="mat_price_spec" class="">
                            Prix spécial
                            <input type="text" class="form-control" name="mat_price_spec" size="80"  placeholder="00,00" pattern="\s*[0-9\s]{1,8}([.,][0-9]{0,2})?\s*?"
                                    value="<?= isset($tabInputs["mat_price_spec"]) ? htmlspecialchars($tabInputs["mat_price_spec"]) : '' ?>" >
                          </label>
                          <span class="errorMessageInput" style="color: #FF0000"><?= isset($tabErreur["mat_price_spec"]) ? $tabErreur["mat_price_spec"] : '' ?></span>
                        </div>
                        <?php /* _____________________________________________ mat_qty _____________________________________________ */ ?>
                        <div class="form-group">
                          <label for="mat_qty" class="">
                            Quantité
                            <input type="text" class="form-control" name="mat_qty" size="80"
                                   value="<?= isset($tabInputs["mat_qty"]) ?  htmlspecialchars($tabInputs["mat_qty"]) : '' ?>" required >
                          </label>
                          <span class="errorMessageInput" style="color: #FF0000"><?= isset($tabErreur["mat_qty"]) ? $tabErreur["mat_qty"] : '' ?></span>
                        </div>
                        <?php /* _____________________________________________ ID_MATERIAL_CAT _____________________________________________ */ ?>
                        <div class="form-group">
                          <label for="ID_MATERIAL_CAT" class="control-label">
                          Catégorie
                          </label>
                          <select name="ID_MATERIAL_CAT"  class="form-control" id="catID"  style="width: 300px;">
                            <?php
                            // SELECT seulement les catégories actifs
                            $queryCategories = "SELECT * FROM MATERIAL_CAT WHERE inactif = 0";
                            $resultCat = query($queryCategories,[],$mysqli);
                            
                            while($rowCat = $resultCat->fetch_assoc()){?>
                            
                                <option value="<?= $rowCat["ID_MATERIAL_CAT"] ?>" 
                                <?= $rowCat["ID_MATERIAL_CAT"] == $tabInputs["ID_MATERIAL_CAT"] ? 'selected' : '';?> >
                                <?= $rowCat["CAT_FR"] ?>
                                </option> <?php
                            }
                            ?>
                          </select>
                          <span class="errorMessageInput" style="color: #FF0000"><?= isset($tabErreur["ID_MATERIAL_CAT"]) ? $tabErreur["ID_MATERIAL_CAT"] : '' ?></span>
                        </div>
                        <?php /* _____________________________________________ mat_stock _____________________________________________ */ ?>
                        <div class="form-group">
                          <label for="mat_stock" class="">
                            Stock
                            <input type="number" class="form-control" name="mat_stock" size="80" min="1"
                                   value="<?=  isset($tabInputs["mat_stock"]) ?  htmlspecialchars($tabInputs["mat_stock"]) : '' ?>">
                          </label>
                        <span class="errorMessageInput" style="color: #FF0000"><?= isset($tabErreur["mat_stock"]) ? $tabErreur["mat_stock"] : '' ?></span>
                        </div>
                        <?php /* _____________________________________________ show_animo _____________________________________________ */ ?>
                        <div class="form-group">
                          <label class="checkbox-inline">
                            Show animo
                            <input type="checkbox"
                                    name="show_animo"
                                    <?= count($tabInputs) == 0 || $tabInputs["show_animo"] == 1  ? 'checked' : '';?>
                                    value="1">
                          </label>
                        </div>
                        <?php /* _____________________________________________ show_groupea _____________________________________________ */ ?>
                        <div class="form-group">
                          <label  class="checkbox-inline">
                            Show groupe
                            <input type="checkbox"
                                   name="show_groupea"
                                   <?= count($tabInputs) == 0 || $tabInputs["show_groupea"] == 1  ? 'checked' : '';?>
                                   value="1">
                          </label>
                        </div>
                      </div>
                      <?php /* _________ COL ___________*/ ?>
                      <div class="col-md-6">
                        <?php /* _____________________________________________ mat_pic _____________________________________________ */ ?>  
                        <?php if(!empty($tabInputs['mat_pic'])) { ?>
                            <img class="card-img-top"
                                style="width:50%; max-width:100%;"
                                data-featherlight="/private/img/materiel/<?= $tabInputs['mat_pic']?>"
                                title="<?php echo $tabInputs['mat_name'];?>"
                                src="/private/img/materiel/<?= $tabInputs['mat_pic']?>">
                        <?php } ?>
                        <div class="form-group">
                              <label for="mat_pic">
                                <input type="file" class="form-control" name="mat_pic"  <?= isset($tabInputs["mat_pic"])  ? '' : '' ?>
                                        accept="image/png,image/jpeg,image/jpg,image/gif,image/bmp">
                              </label>
                              <br>
                              <span class="errorMessageInput" style="color: #FF0000"><?= isset($tabErreur["mat_pic"]) ? $tabErreur["mat_pic"] : '' ?></span>
                        </div>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</section>
