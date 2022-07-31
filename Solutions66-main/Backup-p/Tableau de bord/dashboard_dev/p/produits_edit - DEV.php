<?php 
$DEBUG_DB = 0; 

/*
Ce fichier fait l'ajout et aussi l'edition d'un article.
LE SCRIPT js POUR CE FICHIER : dashboard_dev/p/produits_edit_footer.php
*/

/*
================================== CAS (ARTICLE DUPLIQUÉ) ======================================
Cette requette va dupliquer un article en affectant only 2 champs (date_insert et date_update) 
================================================================================================
*/
if($_GET["duplicate"]=="1" and preg_match('#^[0-9]+$#',$_GET["id_article"]) ){
    
	$dreteLa = date("Y-m-d H:i:s");
	$dbAnimoCaisseDefault->autocommit(false);
	try{
	    // _______________ Create un article dupliqué ____________________
	    
		$resultArticle = query("select *
								from article
							    where id_article = ?",[$_GET["id_article"]],$dbAnimoCaisseDefault);
		$rowArticle = $resultArticle->fetch_assoc();
		// On profite des champs existent d'un article en excluant son ID et en changent que 2 champs
		unset($rowArticle["id_article"]);
		$rowArticle["date_insert"] = $dreteLa;
		$rowArticle["date_update"] = $dreteLa;
		faireInsert_i($rowArticle,"article",$dbAnimoCaisseDefault,$DEBUG_DB);
		/*
        Garde sa cle pour faire les relations avec les autres tables:
        (article_desc,article_categorie_link,prix_change, link_article_besoin,link_article_four,article_photo )
		*/
		$new_id_article = $dbAnimoCaisseDefault->insert_id;
		
		 // _______________ Mets l'article créé en relations avec les  autres tables ____________________
		 
		//TABLE article_desc
		$resultDesc = query("SELECT *
							   from article_desc
							  where id_article = ?",[$_GET['id_article']],$dbAnimoCaisseDefault);
		if ( $rowDesc = $resultDesc->fetch_assoc() ){
		    // Set  PK
			$rowDesc["id_article"] = $new_id_article;
			faireInsert_i($rowDesc,"article_desc",$dbAnimoCaisseDefault,$DEBUG_DB);
		}
		//TABLE article_categorie_link 	
		$resultCat = query("select *
                              from article_categorie_link
                             where id_article = ?",[$_GET["id_article"]],$dbAnimoCaisseDefault);
		while ( $uneLigneCat = $resultCat->fetch_assoc() ){
		    // Set  FK
			$uneLigneCat["id_article"] = $new_id_article;
			faireInsert_i($uneLigneCat,"article_categorie_link",$dbAnimoCaisseDefault,$DEBUG_DB);
		}
		// TABLE prix_change
		$resultPrix = query("select *
						   from prix_change
						   where id_article = ?
					       order by id_prix_change desc 
						   limit 1",[$_GET["id_article"]],$dbAnimoCaisseDefault);
		if ( $uneLignePrix = $resultPrix->fetch_assoc() ){
			$arrayPrix =[];
			$arrayPrix["prix"] = $uneLignePrix["prix"];
			$arrayPrix["change_date_exp"] = date("Y-m-d");
			$arrayPrix["id_article"] = $new_id_article;
			$arrayPrix["qte"] = "1";
			$arrayPrix["date_update"] = date("Y-m-d H:i:s");
			$arrayPrix["date_insert"] = date("Y-m-d H:i:s");
			$arrayPrix["id_staff"] = $_SESSION["utilisateur"]["id_utilisateur"];
			$arrayPrix["date_insert"] = $dreteLa;
			$arrayPrix["date_update"] = $dreteLa;
			
			faireInsert_i($arrayPrix,"prix_change",$dbAnimoCaisseDefault,$DEBUG_DB);
		}
		// TABLE link_article_besoin 
		$resulT_link_article_besoin = query("select *
							   from link_article_besoin
							  where id_article = ?",[$_GET["id_article"]],$dbAnimoCaisseDefault);
		while ( $uneLigne_link_article_besoin = $resulT_link_article_besoin->fetch_assoc() ){ //$uneLignePrix  nom remplaced!
			$uneLigne_link_article_besoin["id_article"] = $new_id_article;
			faireInsert_i($uneLigne_link_article_besoin,"link_article_besoin",$dbAnimoCaisseDefault,$DEBUG_DB);
		}
		// TABLE link_article_four
		$resultFour = query("select *
                               from link_article_four
                              where id_article = ?",[$_GET["id_article"]],$dbAnimoCaisseDefault);
		while ( $uneLigneFour = $resultFour->fetch_assoc() ){
			unset($uneLigneFour["id_link_article_four"]);
			$uneLigneFour["id_article"] = $new_id_article;
			$uneLigneFour["date_insert"] = $dreteLa;
			$uneLigneFour["date_update"] = $dreteLa;
			faireInsert_i($uneLigneFour,"link_article_four",$dbAnimoCaisseDefault,$DEBUG_DB);
		}
		// TABLE article_photo
		$resultPhoto = query("SELECT * FROM article_photo WHERE id_article = ?",[$_GET['id_article']],$dbAnimoCaisseDefault);
		if ( $resultPhoto->num_rows > 0 ){
			$sftp = getSFTPConnection();
			$sftp->chdir("public_html/upimg/produits");
	
			$listeFichier = $sftp->nlist(".");
			$listeFichier = convertArrayNumericToDictTrue($listeFichier);
			
			while ( $uneLignPhoto = $resultPhoto->fetch_assoc() ){
				if ( $listeFichier[$uneLignPhoto["img"]] ){
					
					$ext = "." . strtolower(pathinfo($uneLignPhoto["img"],PATHINFO_EXTENSION));
					$nomFichierBase = $new_id_article;
					
					$i = "";
					if ( $listeFichier[$nomFichierBase.$ext] ){
						$i = "0";
						while ( $listeFichier[$nomFichierBase."-".$i.$ext] ) {
							if($i===""){$i=0;}else{$i+=1;}
						}
						$i = "-".$i;
					}
					
					if ( $DEBUG_DB < 2 ){
						$sftp->put( $nomFichierBase.$i.$ext, $sftp->get($uneLignPhoto["img"]) ); 
					} else {
						echo "SFTP : COPY " . $uneLignPhoto["img"] . " -> " . $nomFichierBase.$i.$ext . "<br />";
					}
					
					$arrayDB2 = array();
					$arrayDB2["id_article"] = $new_id_article;      
					$arrayDB2["img"] = $nomFichierBase.$i.$ext;
					faireInsert_i($arrayDB2,"article_photo",$dbAnimoCaisseDefault,$DEBUG_DB);
					
					$listeFichier[$nomFichierBase.$i.$ext] = true;
				}
			}
		}
		
		// ______________________________ GESTION DEBUG ____________________________
		//COMMIT!
		if ( $DEBUG_DB > 1 ){
		    // Pour le teste
			$dbAnimoCaisseDefault->rollback();
		} else {
		    //Fait persister les données 
			$dbAnimoCaisseDefault->commit();
		}
		
		redirect("index.php?p=produits_edit&id_article=".$new_id_article,$DEBUG_DB);
		
	}catch(Exception $e){
		$dbAnimoCaisseDefault->rollback();
		$errors = $e->getMessage();
	}
}

/*
================================== CAS (CREATE et UPDATE) ======================================================
                        Cette requette traite 2 action: create et update 
=================================================================================================================
*/
//SI le fom a été soumis
if(isset($_POST["edit_produit"])){    
    
    $errorsValidation = [];
	$dbAnimoCaisseDefault->autocommit(false);
	try{
		if ( $DEBUG_DB ){
			vex($_POST);
			vex($_FILES);
		}
		// CAS UPDATE: recupere l'article à éditer. 
		if(preg_match('#^\d+$#',$_REQUEST["id_article"])){
			$is_update = true;
			$id_article = $_REQUEST["id_article"];//Article to edit
			$resultArticle = query("select * from article where id_article = ?",[$_REQUEST["id_article"]],$dbAnimoCaisseDefault);
			$rowArticle = $resultArticle->fetch_assoc();
		}
		// ________________________ GESTION UPC _________________________
	
		$_POST["PLU"]   = preg_replace('#[^0-9]+#','',$_POST["PLU"]);
		$_POST["PLU2"]  = preg_replace('#[^0-9]+#','',$_POST["PLU2"]);
		$_POST["PLU3"]  = preg_replace('#[^0-9]+#','',$_POST["PLU3"]);
		$listPLU = [];
		if ( $_POST["PLU"] != "" ){
			$listPLU[] = trim($_POST["PLU"]);
    	}
		if ( $_POST["PLU2"] != "" ){
			$listPLU[] = trim($_POST["PLU2"]);
		}
		if ( $_POST["PLU3"] != "" ){
			$listPLU[] = trim($_POST["PLU3"]);
		}
        //Au moins un UPC est envoyé
		if ( sizeof($listPLU) > 0 ){ 
		    //Cas UPDATE
			if ( $rowArticle["id_article"] ){
			    //CAS UPDATE: Check all UPC sauf l'article lui meme
				$resultTest = query("select * from article where (PLU in (?) or PLU2 in (?) or PLU3 in (?)) and id_article != ?",
				                    [$listPLU,$listPLU,$listPLU,$rowArticle["id_article"]],$dbAnimoCaisseDefault);
		    //cas CREATE
			} else {
			    //Check si les UPC envoyés  existent deja dans la BD
				$resultTest = query("select * from article where PLU in (?) or PLU2 in (?) or PLU3 in (?)",[$listPLU,$listPLU,$listPLU],$dbAnimoCaisseDefault);
			}
			//SI  erreur sur la query OU que la query return un résultat : le UPC existe déjà
			if ( !$resultTest or $resultTest->num_rows > 0 ){
				throw new Exception("Un article disposant du même code CUP existe déjà.");
			}
		}
		
		// ARTICLE		
		$arrayArticle = [];
		$arrayArticle["id_departement"] = trim($_POST["id_departement"]); // validation côté JS
		$arrayArticle["desc_fr"]        = trim($_POST["nom_fr"]); // validation côté HTML
		$arrayArticle["desc_en"]        = trim($_POST["nom_en"]);
		$arrayArticle["PLU"]            = trim($_POST["PLU"]); // validation côté HTML et PHP
		$arrayArticle["PLU2"]           = trim($_POST["PLU2"]);
		$arrayArticle["PLU3"]           = trim($_POST["PLU3"]);
		$arrayArticle["boite_nb"]       = trim($_POST["boite_nb"]); 
		$arrayArticle["boite_PLU"]      = trim($_POST["boite_PLU"]);
		$arrayArticle["notes"]          = trim($_POST["notes"]);
		$arrayArticle["inactif"]        = ($_POST["inactif"]=="1")?"1":null;
		$arrayArticle["id_distributeur"]= $_POST["id_fournisseur"];
		$arrayArticle["taxe1"]          = ($_POST["taxe1"]=="1")?"1":null;
		$arrayArticle["taxe2"]          = ($_POST["taxe2"]=="1")?"1":null;
		$arrayArticle["taxe3"]          = ($_POST["taxe3"]=="1")?"1":null;
		$arrayArticle["taxe4"]          = ($_POST["taxe4"]=="1")?"1":null;
		//$arrayArticle["disc"] = ($_POST["disc"]=="1")?"1":null;
		
		//Compare si pareil
		$skip = false;
		//Dans le CAS UPDATE
		if ( $rowArticle ){
			$skip = true;
			//Parcours les donnés envoyés
			foreach( $arrayArticle as $champ=>$valeur ){
				// ...compare avec ceux qui sont deja dans la BD 
				if ( (string)$valeur !== (string)$rowArticle[$champ] ){
					$skip = false;
					break;
				}
			}
		}
		
		//Dans le CAS UPDATE
		if ( $skip && $is_update){
			if ( $DEBUG_DB ) vex("skip");
		} else {
			if ($is_update) {
			    //Update article
				$arrayArticle["id_article"] = $id_article;
				$arrayArticle["date_update"] = date("Y-m-d H:i:s");
				faireUpdate_i($arrayArticle,"article","id_article",$dbAnimoCaisseDefault,$DEBUG_DB);
				
				if($arrayArticle["inactif"] == '1'){
				    //Update article_desc: don't show in web
					$arrayArticle2 = [];
					$arrayArticle2["webReady"] = "";
					$arrayArticle2["id_article"] = $id_article;
					$arrayArticle2["date_update"] = date("Y-m-d H:i:s");
					faireUpdate_i($arrayArticle2,"article_desc","id_article",$dbAnimoCaisseDefault,$DEBUG_DB);
				}
			} else {
			    //Cas INSERT
				$arrayArticle["date_update"] = date("Y-m-d H:i:s");
				$arrayArticle["date_insert"] = date("Y-m-d H:i:s");
				faireInsert_i($arrayArticle,"article",$dbAnimoCaisseDefault,$DEBUG_DB);
				$id_article = $dbAnimoCaisseDefault->insert_id;
			}
		}

		//_________________________________ Table: prix_change ___________________________________________________________________
		
		//SI Nouveau prix est posté: insert into prix_change
		if ( $_POST["prix_change_prix"] != ""  && preg_match('#^\s*[0-9\s]{1,8}([.,][0-9]{0,2})?\s*?$#', $_POST["prix_change_prix"]) ){
			$arrayPrix =[];
			$arrayPrix["prix"] = trim(str_replace( ',','.', $_POST["prix_change_prix"] ));
			
			if ( preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$#',$_POST["prix_chang_change_date_exp"]) ){
				$arrayPrix["change_date_exp"] = $_POST["prix_chang_change_date_exp"];
			} else {
				$arrayPrix["change_date_exp"] = date("Y-m-d");
			}
			$arrayPrix["locked"] = $_POST["prix_change:locked"];
			$arrayPrix["id_article"] = $id_article; //Liaison entre la table prix_change et l'article crée précédemment($id_article)
			$arrayPrix["qte"] = "1";
			$arrayPrix["date_update"] = date("Y-m-d H:i:s");
			$arrayPrix["date_insert"] = date("Y-m-d H:i:s");
			$arrayPrix["id_staff"] = $_SESSION["utilisateur"]["id_utilisateur"];
			faireInsert_i($arrayPrix,"prix_change",$dbAnimoCaisseDefault,$DEBUG_DB);
		}
		
		// _______________________ TABLE article_desc ($arrayDB) _______________________ 
		
		// récolte de données pour la TABLE article_desc 
		$arrayDB = array();
		$arrayDB["id_article"]  = $id_article; //liée au article crée précédemment($id_article) 
		$arrayDB["nom_fr"]      = $_POST['nom_web_fr'] ? : $_POST['nom_fr'];
		$arrayDB["nom_en"]      = $_POST['nom_web_en'] ? : $_POST['nom_en'];
		$arrayDB["desc_fr"]     = $_POST['desc_fr_web'];
		$arrayDB["desc_en"]     = $_POST['desc_en_web'];
		$arrayDB["webReady"]    = isset($_POST['webReady']) ? 1 : null;
		$arrayDB["format"]      = str_replace('.',',',$_POST['format']);
		$arrayDB["id_stades"]   = $_POST['id_stades'];
		$arrayDB["id_races"]    = $_POST['id_races'];
		$arrayDB["id_recettes"] = $_POST['id_recettes'];
		$arrayDB["id_couleurs"] = $_POST['id_couleurs'];
		$arrayDB["id_tailles"]  = $_POST['id_tailles'];
		$arrayDB["id_types"]    = $_POST['id_types'];
		$arrayDB["marque"]      = $_POST['marque'];
		$arrayDB["produit_qc"]  = isset($_POST['produit_qc']) ? 1 : null;
		$arrayDB["enVedette"]   = isset($_POST['enVedette']) ? 1 : null;
		$_POST['poid']          = floatval(str_replace('.',',',trim($_POST['poid'])));
		if ( $_POST['poid'] != "" ){
			if ( $_POST["poid_unit"] == "kg" ){
				$arrayDB["poid"] = $_POST['poid'] * 1000;
			} else if ( $_POST["poid_unit"] == "kg" ){
				$arrayDB["poid"] = $_POST['poid'] * 453.592;
			} else {
				$arrayDB["poid"] = $_POST['poid'];
			}
		} else {
			$arrayDB['poid'] = null;
		}
		//SI pas de date
		if ( empty($_POST['date_enVedette_expiration']) ) {
		    //Set à NUll
		   $arrayDB["date_enVedette_expiration"] = NULL;
		}else{
    	    //Verifie si le format est valide
    	    if ( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_POST['date_enVedette_expiration']) !='' )  {
                // Stock la date
                $arrayDB["date_enVedette_expiration"] = $_POST['date_enVedette_expiration'];
    	    }else{
    	        $errorsValidation[] = "Format de date invalide!";
    	    }			    
		}
		
	    faireDelete_i( ["id_article"=>$id_article],"link_article_besoin", $dbAnimoCaisseDefault, $DEBUG_DB );
        
		$resultTest = query("SELECT id_article FROM article_desc WHERE id_article = ?",[$_REQUEST["id_article"]],$dbAnimoCaisseDefault);
		//CAS UPDATE
		if ($resultTest->num_rows === 1) {
	   		$arrayDB["date_update"] = date('Y-m-d H:i:s');
		    faireUpdate_i($arrayDB, "article_desc", "id_article", $dbAnimoCaisseDefault, $DEBUG_DB); 
		} else {
		    // AJOUTER
			$arrayDB["id_article"] = $id_article;//La reference 
			$arrayDB["date_insert"] = date('Y-m-d H:i:s');
    		faireInsert_i($arrayDB, "article_desc", $dbAnimoCaisseDefault, $DEBUG_DB);	
		}
		
		//_______________________  TABLE link_article_besoin ($arrayDB3) _______________________ 
		//Traite le select besoin
		if(isset($_POST['id_besoins']) && !empty($_POST['id_besoins'])){
			$arrayDB3 = array();
			$arrayDB3["id_article"] = $id_article;//La reference 
			// Filtre les éléments envoyées 
			$arrayBesoins = array_filter($_POST['id_besoins'],"is_numeric");
			foreach($arrayBesoins as $Value){
				$arrayDB3["id_besoins"] = $Value;
			 	faireInsert_i($arrayDB3, "link_article_besoin", $dbAnimoCaisseDefault, $DEBUG_DB); 
			}
		}
	    
	    //_______________________  TABLE article_categorie_link _______________________ 
		
		//Traite le select categorie
		$listCategorie = array_filter(explode(',',$_POST["id_categorie"]),"is_numeric");
		if ( sizeof($listCategorie) > 0 ){
			if ( $DEBUG_DB ){
				vex("delete from article_categorie_link where id_article = ? and id_categorie not in (?)");
				vex($id_article,$listCategorie);
			}
			if ( $DEBUG_DB < 2 ){
				query("delete from article_categorie_link where id_article = ? and id_categorie not in (?)",[$id_article,$listCategorie],$dbAnimoCaisseDefault);
			}
			foreach( $listCategorie as $id_categorie ){
				$testExists = query("select *
									 from article_categorie_link
									 where id_article = ?
									 and id_categorie = ?",[$id_article,$id_categorie],$dbAnimoCaisseDefault);
				if ( $testExists->num_rows == 0){
					faireInsert_i(["id_article"=>$id_article,"id_categorie"=>$id_categorie],"article_categorie_link",$dbAnimoCaisseDefault,$DEBUG_DB);
				}
			}
		}else {
		        faireDelete_i(["id_article"=>$id_article],"article_categorie_link",$dbAnimoCaisseDefault,$DEBUG_DB);
		}
		
        //_______________________  TABLE article_photo  _______________________ 
		//DELETE
		if(is_array($_POST["delete_img"])){
			foreach($_POST["delete_img"] as $id_article_photo){
				$queryPhoto = "SELECT * FROM article_photo WHERE id_article_photo = ?";
				$resultPhoto = query($queryPhoto,[$id_article_photo],$dbAnimoCaisseDefault);
				if($resultPhoto->num_rows === 1){
					$unePhotoDel = $resultPhoto->fetch_assoc();
					if( $DEBUG_DB < 2 and deleteFileSFTP($unePhotoDel["img"],"public_html/upimg/produits")){
						faireDelete_i(["id_article_photo"=>$id_article_photo],"article_photo",$dbAnimoCaisseDefault,$DEBUG_DB);
					}
				}
			}
		}		
		
	    //_______________________  TABLE id_link_article_four  _______________________ 
		//Fournisseur
		if ($_POST["listFournisseur"] != ""){
			$listFournisseur = explode("\n",str_replace("\r","",$_POST["listFournisseur"]));
			foreach($listFournisseur as $strFour){
				$champs = explode("\t",$strFour);
				
				$arrayLinkFour = [];
				$arrayLinkFour["id_fournisseur"] = $champs[1];
				$arrayLinkFour["num_four"] = $champs[2];
				$arrayLinkFour["prix_coutant"] = str_replace(",",".",$champs[3]);
				$arrayLinkFour["prix_caisse"] = str_replace(",",".",$champs[4]);
				$arrayLinkFour["discontinued"] = $champs[5];
				$arrayLinkFour["id_article"] = $id_article;
				
				$row_link_article_four = null;
				if ( $champs[0] != "" ){
					$enonce = sprintf("select * from link_article_four where id_link_article_four = %s",$champs[0]);
					$result_link_article_four = $dbAnimoCaisseDefault->query($enonce) or die($dbAnimoCaisseDefault->error);
					$row_link_article_four = $result_link_article_four->fetch_assoc();
				}
				
				$skip = false;
				if ( $row_link_article_four ){
					$skip = true;
					foreach( $arrayLinkFour as $champ=>$valeur ){
						if ( $valeur != $row_link_article_four[$champ] ){
							$skip = false;
							break;
						}
					}
				}
				
				if ( $skip ){
					if ($DEBUG_DB)
						echo "SKIP LINK_FOUR : ".$champs[0]."<br />";
				} else {
					if ( preg_match('#^\d+$#',$champs[0]) ) {
						$arrayLinkFour["id_link_article_four"] = $champs[0];
						$arrayLinkFour["date_update"] = date("Y-m-d H:i:s");
						faireUpdate_i($arrayLinkFour,"link_article_four","id_link_article_four",$dbAnimoCaisseDefault,$DEBUG_DB);
				    	$id_link_article_four = $_POST["id_article"];					    
						
					} else {
						$arrayLinkFour["date_update"] = date("Y-m-d H:i:s");
						$arrayLinkFour["date_insert"] = date("Y-m-d H:i:s");
				    	faireInsert_i($arrayLinkFour,"link_article_four",$dbAnimoCaisseDefault,$DEBUG_DB);
			    		$id_link_article_four = $dbAnimoCaisseDefault->insert_id;
					}
				}
				
			}
		}
	    
		if(isset($_FILES["article_img"])){
			$_FILES["article_img"] = reArrayFiles($_FILES["article_img"]);
		}
		//INSERT
		if(count($_FILES["article_img"]) > 0){
			ini_set("memory_limit","128M");
			foreach($_FILES["article_img"] as $file){
				if ($file["error"] == 0 and $file["name"] != "") {
				    
					$arrayDB2 = array();
					$arrayDB2["id_article"] = $id_article;      
					$arrayDB2["img"] = stockFileSFTP($file,"%f","public_html/upimg/produits");
					faireInsert_i($arrayDB2,"article_photo",$dbAnimoCaisseDefault); 
				}
			}
		}
		
		if ($DEBUG_DB > 1){
			throw new Exception("DEBUG");
		}
		$dbAnimoCaisseDefault->autocommit(true);
		$success = true;//Show message utilisateur
		
		//redirect("index.php?p=produits", $DEBUG_DB);
		
		/*
		if(count($errorsValidation) > 0){
		    ?>
			<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4 class="alert-heading">Erreur</h4>
			<p>
			<b>Certaines informations requises sont manquantes</b>:<br />
			<?= implode('<br />',$errorsValidation)?>
			</p>
			</div>
			<?php
		}else{
			?>
			<div class="alert alert-success">
			<button type="button" class="close" data-dismiss="alert"></button>
			<h4 class="alert-heading">Merci!</h4>
			<p>
			Produit enregistré avec succès!
			</p>
			</div>
			<?php
		}		
		*/
			
		
	}catch(Exception $e){
		$dbAnimoCaisseDefault->rollback();
		$errors = $e->getMessage();
	}
}//fin if

// Pour nourrir le select "Distributeur"
$listeFournisseur = [];
$enonce = sprintf("select fournisseur.nom, fournisseur.id_fournisseur, fournisseur.inactif, 
				     fournisseur.est_fournisseur, fournisseur.est_distributeur
					 from fournisseur
					order by nom asc" );
					
$resultFour = $dbAnimoCaisseDefault->query($enonce) or die($dbAnimoCaisseDefault->error);
while ( $uneLigneFour = $resultFour->fetch_assoc()){
	$listeFournisseur[] = $uneLigneFour;
}//fin while


/*================================== querys ======================================================*/

if ( preg_match('#^\d+$#',$_GET['id_article']) ){
    
	$resultArticle = query("select *, desc_fr `nom_fr`, desc_en `nom_en` from article where id_article = ?",[$_GET["id_article"]],$dbAnimoCaisseDefault);
	$rowArticle = $resultArticle->fetch_assoc();
	
	$resultDesc = query("SELECT * FROM article_desc WHERE id_article = ?",[$rowArticle['id_article']],$dbAnimoCaisseDefault);
	$rowDesc = $resultDesc->fetch_assoc();
	
	$resultPhoto = query("SELECT * FROM article_photo WHERE id_article = ?",[$rowArticle['id_article']],$dbAnimoCaisseDefault);
	
	$resultMarques = query("SELECT marques.* FROM marques order by marques.nom",[],$dbAnimoCaisseDefault);
}


//echo '<pre>' , print_r($_REQUEST) , '</pre>'; die();
?>

<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-barcode icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?= isset($_GET["id_article"]) ? L('produitedit',"o") : 'Ajouter produit' ?></span>
		</h1>
	</div>
    <div id="alertErreurAjax" class="alert alert-danger" style="display: none;" role="alert">
    </div>
	<!-- End SubHeader-->
	<!-- Start Content-->
	<?php if($success){
	    msg_output("Les informations ont été enregistrées avec succès.");
	    echo "<script>setTimeout(function(){location = 'index.php?p=produits' }, 3000 ); </script>";
	    
	}else if($errors != ""){
		msg_output($errors,"danger");
	}?>
	<div class="row pl-3 pr-3 mb-3 mt-3">   
		<section class="col-sm-12 col-md-12 col-lg-12 panel-wrap panel-grid-item">
			<!--Start Panel-->
			<div class="panel c-white-dark pb-0">
				<div class="panel-body">
					<div class="panel bgc-white-dark transition visible pb-2">
						<div class="panel-body panel-body-p">
							<div class="page-size-table">
								<div class="bootstrap-table">
									<div class="fixed-table-toolbar">
									    <!-- ######################################################  BEGIN FORM  ###################################################### -->
										<form method="post" enctype="multipart/form-data" action="" onsubmit="return onSubmitAddArticle(this)" id="formEditProduit">
											<!-- ==========================================================  Nom du Produit ========================================================= --> 
											<div class="row">
												<div class="col-md-6 form-group">
											    	<label for="nom_fr"><?= L("nom produit","o") ?> <i>Fr</i></label>
													<input  type="text" class="form-control mb-1 mr-sm-2 " required  name="nom_fr" id="nom_fr" 
													        value="<?= $rowArticle['nom_fr'] ==  "" && isset($_POST["nom_fr"]) && $_POST["nom_fr"] != "" ? $_POST["nom_fr"] : attrEncode($rowArticle['nom_fr']) ?>" >
												</div>
												
												<div class="col-md-6 form-group">
													<label for="nom_en"><?= L("nom produit","o") ?> <i>EN</i></label>
													<input type="text" class="form-control mb-1 mr-sm-2"  name="nom_en" id="nom_en" value="<?php echo attrEncode($rowArticle['nom_en']); ?>">
												</div>
												
												<div class="col-md-6 form-group">
													<label for="nom_fr"><?= L("nom produit","o"); ?> web <i>FR</i></label>
													<input type="text" class="form-control mb-1 mr-sm-2" name="nom_web_fr" id="nom_web_fr" value="<?php echo attrEncode($rowDesc['nom_fr']);?>">
												</div>
												<div class="col-md-6 form-group">
													<label for="nom_en"><?= L("nom produit","o") ?> web <i>EN</i></label>
													<input type="text" class="form-control mb-1 mr-sm-2" name="nom_web_en" id="nom_web_en" value="<?php echo attrEncode($rowDesc['nom_en']);?>">
												</div>
											</div>
											<hr/>
											<!-- ========================================================== Fournisseur =========================================================-->
											<div class="row">
												<div class="col-md-3">
													<label for="id_fournisseur"><?php echo L('fournisseur',"o"); ?></label>
													<?php $getFournisseur = query('select nom, id_fournisseur from FOURNISSEURS where est_distributeur = 1 AND inactif IS NULL order by nom asc',[],$mysqli);?>
													<select class="ui search simple-select select-dropdown fluid" name="id_fournisseur" >
														<option value="" disabled selected><?php echo L('fournisseur',"o"); ?></option>
														<?php while ( $rowFournisseur = $getFournisseur->fetch_assoc()){?>
														<option value="<?php echo $rowFournisseur['id_fournisseur']?>" <?php if( $rowArticle['id_distributeur'] == $rowFournisseur['id_fournisseur']){ echo 'selected';}?>><?php echo $rowFournisseur['nom']?></option>
														<?php }?>
													</select>
												</div>
												<!-- ========================================================== marques =========================================================-->
												<div class="col-md-3">
													<label for="marque"><?php echo L('marques',"o"); ?></label>
													<select class="ui search simple-select select-dropdown fluid" name="marque">
														<option value="" disabled selected><?php echo L('marque',"o"); ?></option>
														<?php 
														$getBrand = query('select * from marques order by marques.nom',[],$dbAnimoCaisseDefault);
														while ( $rowBrand = $getBrand->fetch_assoc()){
															?>
															<option value="<?php echo $rowBrand['id_marques']?>" <?php if( $rowDesc["marque"] == $rowBrand['id_marques']){ echo 'selected';}?>><?php echo $rowBrand['nom']?> (<?php echo L($rowBrand['categorie'],"o"); ?>)</option>
															<?php 
														}?>
													</select>
												</div>
												<!-- ========================================================== departement =========================================================-->  <!--required-->
												<div class="col-md-3">
													<label for="id_departement"><?php echo L('departement',"o"); ?></label>
													<?php $getDepartement = query('select * from departement order by id_departement asc',[],$dbAnimoCaisseDefault);?>
													<select class="ui search simple-select select-dropdown fluid" name="id_departement" >
														<option value="" disabled selected><?php echo L('departement',"o"); ?></option>
														<?php while ( $rowDepartement = $getDepartement->fetch_assoc()){?>
														<option value="<?php echo $rowDepartement['id_departement']?>" 
														    <?php if( $rowArticle['id_departement'] == $rowDepartement['id_departement']){ echo 'selected';}?>>
														    <?php echo $rowDepartement['nom']?>
														</option>
														<?php }?>
													</select>
													<span class="id_departement"  style="color: #FF0000; display: none;">Champ obligatoire !</span>
												</div>
											</div>
											<hr/>
											<!-- ========================================================== Catégories =========================================================--> <!--required-->
											<h4 class="lh-0 pb-4"><?php echo L('categorie',"o"); ?>(s)</h4>
											<div class="row">
											    <?php //Btn ajouter catégorie : js dans produits_edit_footer.php ?> 
												<div class="col-md-1 right" style="padding-top:4px;">
												    <a href="javascript:;" onclick="createCatDiv()"><span class="fa fa-plus-circle fa-2x"></span></a>
											    </div>
												<div class="col-md-10">
													<div class="row" id="catDiv">
														<?php
														$list_id_cat = [];
														//SI l'article existe deja
														if ( $rowArticle ){
															$i = 0;
															$resultCat = query("SELECT * FROM article_categorie_link WHERE id_article = ?",[$rowArticle["id_article"],],$dbAnimoCaisseDefault);
															while( $rowCat = $resultCat->fetch_assoc() ){
																?>
																<div class="col-md-3">
																	<div class="<?= $i > 0 ? "added ":""?>selectCat" data-value="<?php echo $rowCat["id_categorie"] ?>" >
																	    
																	</div>
																</div>
																<?php
																$list_id_cat[] = $rowCat["id_categorie"];
																$i++;
															}
														}
														if ( sizeof($list_id_cat) == 0 ){
															?>
															<div class="col-md-3">
															    <div class="selectCat" data-value="" ></div>
														    </div>
															<?php
														}
														?>
													</div>
												</div>
											</div>
											 <span class="id_categorie"  style="color: #FF0000; display: none;">Champ obligatoire !</span>
											<input type="hidden" id="id_categorie" name="id_categorie" value="<?php echo (sizeof($list_id_cat)>0)?implode(',',$list_id_cat):"" ?>" />
											<hr/>
											<!-- ========================================================== UPC =========================================================--> 
											<h4 class="lh-0 pb-4"><?php echo L('codeupc',"o"); ?></h4>
											<div class="row">
												<div class="col-md-4">
													<input type="text"
													        class="form-control mb-1 mr-sm-2" placeholder="<?php echo L('upc',"o"); ?> 1 " 
													        name="PLU" minlength="12" maxlength="14" pattern="\d{1,14}" required
													        value="<?php echo attrEncode($rowArticle['PLU']);?>" > 
													        <i>Seulement chiffres (max 14 caractères).</i><br><br>
													        <span id="msnErreurUPC"  style="color: #FF0000; display: none;"></span>
												</div>
												
												<div class="col-md-4">
													<input type="text" class="form-control mb-1 mr-sm-2" placeholder="<?php echo L('upc',"o"); ?> 2" name="PLU2" minlength="12" maxlength="12" pattern="\d{1,12}" value="<?php echo attrEncode($rowArticle['PLU2']);?>">
												</div>
												<div class="col-md-4">
													<input type="text" class="form-control mb-1 mr-sm-2" placeholder="<?php echo L('upc',"o"); ?> 3" name="PLU3" minlength="12" maxlength="12" pattern="\d{1,12}" value="<?php echo attrEncode($rowArticle['PLU3']);?>">
												</div>
											</div>
											<div class="row">
												<div class="col-md-3 form-group">
													<label for="boite_PLU"><?php echo L('upcbox',"o"); ?></label>
													<input type="text" class="form-control mb-1 mr-sm-2" placeholder="" name="boite_PLU" id="boite_PLU" value="<?php echo attrEncode($rowArticle['boite_PLU']);?>">
												</div>
												<div class="col-md-2 form-group">
													<label for="boite_nb"><?php echo L('nbboite',"o"); ?></label>
													<input type="text" class="form-control mb-1 mr-sm-2" placeholder="" name="boite_nb" id="boite_nb" value="<?php echo attrEncode($rowArticle['boite_nb']);?>">
												</div>
												<div class="col-md-7 form-group">
													<label><?php echo L('taxes',"o"); ?></label>
													<br />
													<div class="ui dynamic checkbox mr-4 mb-1">
														<input type="checkbox" name="taxe1" <?= ($rowArticle && $rowArticle["taxe1"]!="1")?"":"checked" ?> value="1" />
														<label><?php echo L('taxe 1',"o"); ?></label>
													</div>
													<div class="ui dynamic checkbox mr-4 mb-1">
														<input type="checkbox" name="taxe2" <?= ($rowArticle && $rowArticle["taxe2"]!="1")?"":"checked" ?> value="1" />
														<label><?php echo L('taxe 2',"o"); ?></label>
													</div>
													<div class="ui dynamic checkbox mr-4 mb-1">
														<input type="checkbox" name="taxe3" <?= ($rowArticle["taxe3"]=="1")?"checked":"" ?> value="1" />
														<label><?php echo L('taxe 3',"o"); ?></label>
													</div>
													<div class="ui dynamic checkbox mr-4 mb-1">
														<input type="checkbox" name="taxe4" <?= ($rowArticle["taxe4"]=="1")?"checked":"" ?> value="1" />
														<label><?php echo L('taxe 4',"o"); ?></label>
													</div>
												</div>
											</div>
											<hr/>
											<!-- ========================================================== Prix =========================================================--> 
											<h4 class="lh-0 pb-4"><?php echo L('Prix',"o"); ?></h4>
											<div class="row ">
												<?php
											    	if (preg_match('#^\d+$#',$_GET["id_article"]) ){
    													$enonce = sprintf("select * from article where id_article = %s",$_GET["id_article"]);
    													$resultArticle = $dbAnimoCaisseDefault->query($enonce) or die($dbAnimoCaisseDefault->error);
													if ( $rowArticle = $resultArticle->fetch_assoc() ){
														$enonce = sprintf("select * from prix where id_article = %s and qte = 1",$_GET["id_article"]);
														$resultPrix = $dbAnimoCaisseDefault->query($enonce) or die($dbAnimoCaisseDefault->error);
														$rowPrix = $resultPrix->fetch_assoc();
													}
												}
												?>
												<?php // Prix actuel ?>
												<div class="col-md-3 form-group">
													<label><b>Prix actuel</b></label>
													<input type="text" class="form-control" value="<?php echo ($rowPrix["prix"] and $_GET["faireCopie"]!="1")?formatPrix($rowPrix["prix"]):"* aucun *" ?>" READONLY  />
												</div>
												<?php 
												if (preg_match('#^\d+$#',$_GET["id_article"]) ){
													$enoncepc = sprintf("select * from prix_change where id_article = %s order by change_date_exp asc",$_GET["id_article"]);
													$resultPrixChange = $dbAnimoCaisseDefault->query($enoncepc) or die($dbAnimoCaisseDefault->error);
													if($resultPrixChange->num_rows >0){
														?>
														<div class="form-group col-md-9">
															<label>Historique de prix</label>
															<div>
																<table class="table table-condensed">
																	<thead>
																		<tr>
																			<th><?php echo $L['prix'];?></th>
																			<th><?php echo $L['date'];?></th>
																			<th style="text-align:center">Locked</th>
																			<th>Staff</th>
																		</tr>
																	</thead>
																	<tbody>
																		<?php while($rowPrixChange = $resultPrixChange->fetch_assoc()){?>
																		<tr>
																			<td><?php echo formatPrix($rowPrixChange['prix']);?></td>
																			<td><?php echo $rowPrixChange['change_date_exp']?></td>
																			<td style="text-align:center"><?php if($rowPrixChange['locked'] == '1'){?><i class="fa fa-lock"></i><?php }?></td>
																			<td><?php echo $rowPrixChange['id_staff']?></td>
																		</tr>
																		<?php }?>
																	</tbody>
																</table>
															</div>
														</div>
														<?php 
													} 
												}?>
											</div>
											<!--CAS CREATE -->
											<div style="border:1px solid #ccc; padding-left:5px;">
												<div class="row" >
											    	<?php //NOUVEAU PRIX ?>
													<div class="form-group col-md-3">
														<label><?php echo $L['newprice'];?></label>
														<input  type="text" 
        														class="form-control" 
        														name="prix_change_prix" 
        														value="<?php echo ($_GET["faireCopie"]=="1" and $rowPrix["prix"])?$rowPrix["prix"]:"" ?>"  
        														placeholder="00,00" 
        														pattern="\s*[0-9\s]{1,8}([.,][0-9]{0,2})?\s*?" 	
        														<?php echo ($_GET["id_article"] == "") ? "required" : "" ?> />  
													</div>
												    <!--DATE CHANGEMENT -->
													<div class="form-group col-md-3">
														<label><?php echo $L['Datechangement'];?></label>
														<div>
															<input 
															class="form-control datepicker"
															type="text" 
															value="<?php echo ($_GET["faireCopie"]=="1")?date("Y-m-d"):"" ?>" 
															name="prix_chang_change_date_exp" 	>
														</div>
													</div>
													<!--PRIX VERROUILLÉ -->
													<div class="form-group col-md-3">
														<div class="ui dynamic checkbox mb-1 mt-4">
															<input type="checkbox" name="prix_change:locked" value="1" />
															<label><?php echo L('vérouiller le prix',"o"); ?></label>
														</div>
													</div>
												</div>
											</div>
											<hr />
											<!-- =========================================== Distributeur(ID du Fournisseur (mix-up)) ========================--> 
											<h4 class="lh-0 pb-4"><?php echo L('distributeur',"o"); ?></h4>
											<div class="row">
											    <!--CAS AJOUTER -->
												<script>
    												//Pour nourrir le select Distributeur
    												var listeFournisseur = <?php echo json_encode($listeFournisseur) ?>;
    												
    												var ajouterFourToArticle = function (){
    													var maTable = getEl("tableFournisseur");
    													var row,cell,i,input,a;
    													
    													//Add ligne
    													row = maTable.insertRow(-1);
    													row.className = 'ligneFour';
    													//Add col 1
    													cell = row.insertCell(-1);
    													input = createEx("input",{className:"ligneFourI0","type":"hidden","value":"", required:"required"}); 
    													cell.appendChild(input);
    													var s = createEx("select",{className:"ligneFourI1 form-control"}); 
    													for (i in listeFournisseur){
    														if ( listeFournisseur[i]["inactif"] != "1" && listeFournisseur[i]["est_fournisseur"] == "1" )
    															createAndAddOptionToSelect(listeFournisseur[i]["id_fournisseur"],listeFournisseur[i]["nom"],s);
    													}
    													cell.appendChild(s);
    													//Add ligne
    													cell = row.insertCell(-1);
    													input = createEx("input",{className:"ligneFourI2 form-control","type":"text", required:"required" });
    													cell.appendChild(input);
    													//Add ligne
    													cell = row.insertCell(-1);
    													input = createEx("input",{className:"ligneFourI3 form-control","type":"text", required:"required"});
    													cell.appendChild(input);
    													
    													cell = row.insertCell(-1);
    													input = createEx("input",{className:"ligneFourI4 form-control","type":"text"});
    													cell.appendChild(input);
    													
    													cell = row.insertCell(-1);
    													input = createEx("input",{className:"ligneFourI5 form-control","type":"checkbox"});
    													cell.appendChild(input);
    													
    													cell = row.insertCell(-1);
    													a = createEx("a",{"href":"javascript:;"});
    													$(a).click(function(){
    														removeFour(this);
    													});
    													a.appendChild( createText("Del") );
    													cell.appendChild(a);
    												}
    												var removeFour = function(srcOb){
    													$(srcOb).parents('tr.ligneFour').remove();
    												}
												</script>
												<!-- pour recevoir la list(listStrFour) provenant du script  -->
												<input type="hidden" name="listFournisseur" value="" />
												<div class="col-md-12">
													<div style="text-align:right"><a href="javascript:;" onclick="ajouterFourToArticle()">Ajouter un distributeur</a></div>
													<table class="table" id="tableFournisseur">
														<thead>
															<tr>
																<th style="padding:10px 1rem">Distributeur</th>
																<th style="padding:10px 1rem"><?php echo $L['num_four'];?></th>
																<th style="padding:10px 1rem"><?php echo $L['cost'];?></th>
																<th style="padding:10px 1rem"><?php echo $L['costcaisse'];?></th>
																<th style="padding:10px 1rem; text-align:center"><i class="fa fa-ban"></th>
															</tr>
														</thead>
														<?php 
														//CAS UPDATE
														if ( $rowArticle ){
															$enonce = "select fournisseur.*, link_article_four.num_four, link_article_four.id_link_article_four, 
																			  link_article_four.prix_coutant, link_article_four.prix_caisse, link_article_four.discontinued
																		 from fournisseur
																			  join link_article_four using(id_fournisseur)
																		where link_article_four.id_article = ?";
															$resultFour = query($enonce, [$rowArticle["id_article"],],$dbAnimoCaisseDefault);
															while ( $uneLigneFour = $resultFour->fetch_assoc() ){
																?>
																<tr class="ligneFour ligneFourDB">
																	<td>
																		<input class="form-control ligneFourI0" type="hidden" value="<?php echo ($_GET["faireCopie"]!="1")?$uneLigneFour["id_link_article_four"]:"" ?>" required/>
																		<select class="form-control ligneFourI1" <?php if($uneLigneFour["discontinued"]=="1"){ echo 'disabled'; }?>>
																			<?php 
																			foreach($listeFournisseur as $four){
																				if (  $uneLigneFour["id_fournisseur"] == $four["id_fournisseur"] or  ($four["inactif"] != "1" and $four["est_fournisseur"] == "1")  ){
																					?>
																					<option value="<?php echo $four["id_fournisseur"] ?>" <?php echo ($uneLigneFour["id_fournisseur"]==$four["id_fournisseur"])?"selected":"" ?> ><?php echo $four["nom"] ?></option>
																					<?php
																				}
																			}
																			?>
																		</select>
																	</td>
																	<td>
																		<input class="form-control ligneFourI2" type="text" value="<?php echo ($_GET["faireCopie"]!="1")?$uneLigneFour["num_four"]:"" ?>" <?php if($uneLigneFour["discontinued"]=="1"){ echo 'disabled'; }?> required />
																	</td>
																	<td>
																		<input class="form-control ligneFourI3" type="text" value="<?php echo $uneLigneFour["prix_coutant"] ?>" <?php if($uneLigneFour["discontinued"]=="1"){ echo 'disabled'; }?> required/>
																	</td>
																	<td>
																		<input class="form-control ligneFourI4" type="text" value="<?php echo $uneLigneFour["prix_caisse"] ?>" <?php if($uneLigneFour["discontinued"]=="1"){ echo 'disabled'; }?>/>
																	</td>
																	<td>
																		<input class="form-control ligneFourI5" type="checkbox" value="1" <?php echo ($uneLigneFour["discontinued"]=="1")?"checked":""; ?> />
																	</td>
																</tr>
																<?php 
															}
														} ?>
													</table>
													
												</div>
											</div>
											<hr />
											<!-- ========================================================== ZONE Description =========================================================-->
											<h4 class="lh-0 pb-4"><?php echo L('description',"o"); ?></h4>
											<div class="row">
											    <!-- Poid en grammes  -->
												<div class="col-md-4">
													<label for="poid"><?php echo L('poidg',"o"); ?></label>
													<div class="input-group mb-1 mr-sm-2">
														<input type="text" class="form-control" placeholder="" name="poid" id="poid" value="<?php echo attrEncode($rowDesc['poid']);?>">
														<div class="input-group-addon p-0" id="poid_unit"></div>
													</div>
												</div>
												<!-- id_tailles  -->
												<div class="col-md-4">
													<label for="id_tailles"><?php echo L('tailles',"o"); ?></label>
													<?php $getTailles = query('select * from tailles order by tailles_fr',[],$dbAnimoCaisseDefault);?>
													<select class="ui search simple-select select-dropdown fluid" name="id_tailles" id="id_tailles">
														<option value="" disabled selected></option>
														<?php while ( $rowTailles = $getTailles->fetch_assoc()){?>
														<option value="<?php echo $rowTailles['id_tailles']?>" <?php if( $rowDesc['id_tailles'] == $rowTailles['id_tailles']){ echo 'selected';}?>><?php echo $rowTailles['tailles_fr']?></option>
														<?php }?>
													</select>
												</div>
												<!-- id_couleurs  -->
												<div class="col-md-4">
													<label for="id_couleurs"><?php echo L('couleurs',"o"); ?></label>
													<?php $getCouleurs = query('select * from couleurs order by couleurs_fr',[],$dbAnimoCaisseDefault);?>
													<select class="ui search simple-select select-dropdown fluid" name="id_couleurs" id="id_couleurs">
														<option value="" disabled selected></option>
														<?php while ( $rowCouleurs = $getCouleurs->fetch_assoc()){?>
														<option value="<?php echo $rowCouleurs['id_couleurs']?>" <?php if($rowDesc['id_couleurs'] == $rowCouleurs['id_couleurs']){ echo 'selected';}?>><?php echo $rowCouleurs['couleurs_fr']?></option>
														<?php }?>
													</select>
												</div>
											</div>
											<!-- id_races  -->
											<div class="row">	
												<div class="col-md-4">
													<label for="id_races"><?php echo L('races',"o"); ?></label>
													<?php $getRaces = query('select * from races order by races_fr',[],$dbAnimoCaisseDefault);?>
													<select class="ui fluid search simple-select select-dropdown mb-4" name="id_races" id="id_races">
														<option value="" disabled selected></option>
														<?php while ( $rowRaces = $getRaces->fetch_assoc()){?>
														<option value="<?php echo $rowRaces['id_races']?>" <?php if( $rowDesc['id_races'] == $rowRaces['id_races']){ echo 'selected';}?>><?php echo $rowRaces['races_fr']?></option>
														<?php }?>
													</select>
												</div>
												<!-- id_stades  -->
												<div class="col-md-4">
													<label for="id_stades"><?php echo L('stades',"o"); ?></label>
													<?php $getStades = query('select * from stades order by stades_fr',[],$dbAnimoCaisseDefault);?>
													<select class="ui fluid search simple-select select-dropdown mb-4" name="id_stades" id="id_stades">
														<option value="" disabled selected></option>
														<?php while ( $rowStades = $getStades->fetch_assoc()){?>
														<option value="<?php echo $rowStades['id_stades']?>" <?php if( $rowDesc['id_stades'] == $rowStades['id_stades']){ echo 'selected';}?>><?php echo $rowStades['stades_fr']?></option>
														<?php }?>
													</select>
												</div>
												<!-- id_recettes  -->
												<div class="col-md-4">
													<label for="id_recettes"><?php echo L('recettes',"o"); ?></label>
													<?php $getRecettes = query('select * from recettes order by recettes_fr',[],$dbAnimoCaisseDefault);?>
													<select class="ui fluid search simple-select select-dropdown mb-4" name="id_recettes" id="id_recettes">
														<option value="" disabled selected></option>
														<?php while ( $rowRecettes = $getRecettes->fetch_assoc()){?>
														<option value="<?php echo $rowRecettes['id_recettes']?>" <?php if( $rowDesc['id_recettes'] == $rowRecettes['id_recettes']){ echo 'selected';}?>><?php echo $rowRecettes['recettes_fr']?></option>
														<?php }?>
													</select>
												</div>
											</div>
											<div class="row">
												<div class="col-md-4">
												    <!-- id_besoins  -->
													<?php 
													$getBesoinsArticle = query('select GROUP_CONCAT(id_besoins) as besoins FROM link_article_besoin WHERE id_article = ? GROUP BY id_article',[$rowArticle['id_article'],],$dbAnimoCaisseDefault);
													$rowBesoinsArticle = $getBesoinsArticle->fetch_assoc();
													$besoin = explode(',',$rowBesoinsArticle['besoins']);
													?>
													<label for="id_besoins"><?php echo L('besoins',"o"); ?></label>
													<?php $getBesoins = query('select * from besoins order by besoins_fr',[],$dbAnimoCaisseDefault);?>
													<select class="ui fluid search simple-select select-dropdown mb-4" multiple="" name="id_besoins[]" id="id_besoins">
														<option value="" disabled selected></option>
														<?php while ( $rowBesoins = $getBesoins->fetch_assoc()){?>
														<option value="<?php echo $rowBesoins['id_besoins']?>" <?php if(in_array($rowBesoins['id_besoins'],$besoin)){ echo 'selected'; }?>><?php echo $rowBesoins['besoins_fr']?></option>
														<?php }?>
													</select>
												</div>
												<div class="col-md-4">
												    <!-- id_types  -->   <!-- required  -->
													<?php 
													$queryTypes = "select * FROM types ORDER BY types_fr";
													$resultTypes = $dbAnimoCaisseDefault->query($queryTypes) or die(mysqli_error($dbAnimoCaisseDefault));
													?>
													<label class="control-label">Types</label>
													<div class="form-group">
														<select id="id_types" name="id_types" class="ui fluid search simple-select select-dropdown mb-4">
															<option value="">&nbsp;</option>
															<?php while($rowTypes = $resultTypes->fetch_assoc()){?>
																<option value="<?php echo $rowTypes['id_types'];?>" <?php if($rowDesc['id_types'] == $rowTypes['id_types']){ echo 'selected'; }?>><?php echo $rowTypes['types_fr'];?></option>
															<?php }?>
														</select>
													</div>
												</div>
												<!-- produit_qc  -->
												<div class="col-md-4">
													<div class="ui dynamic checkbox mr-4">
														<input type="checkbox" name="produit_qc" <?php if( $rowDesc['produit_qc'] == 1){ echo 'checked';}?> value="1" />
														<label><?php echo L('madeincanada',"o"); ?></label>
													</div>
												</div>
											</div>
											<!-- ========================================================== desc_fr_web =========================================================-->
											<div class="row">
												<div class="col-md-12 pb-3">
													<label>Description Web FR</label>
													<textarea name="desc_fr_web" class="form-control editor" style="height:200px;"><?= $rowDesc["desc_fr"]?></textarea>
												</div>
											</div>
											<!-- ========================================================== desc_en_web =========================================================-->
											<div class="row">
												<div class="col-md-12 pb-3">
													<label>Description Web EN</label>
													<textarea name="desc_en_web" class="form-control editor" style="height:200px;"><?= $rowDesc["desc_en"]?></textarea>
												</div>
											</div>
											<hr/>
											<!-- ========================================================== Images =========================================================-->
											<h4 class="lh-0 pb-4"><?php echo L('image',"o"); ?>(s)</h4>
											<div>
												<div class="row">
													<?php if($resultPhoto->num_rows > 0){while($unePhoto = $resultPhoto->fetch_assoc()){?>
														<div class="pb-3 col-xs-12 col-sm-6 col-md-3 col-lg-1">
															<img src="//animoetc.com/upimg/produits/<?= $unePhoto["img"] ?>?t=1&h=100"><br />
															<label><input name="delete_img[]" value="<?= $unePhoto["id_article_photo"]?>" type="checkbox"> Supprimer</label>
														</div>
													<?php }}?>
												</div>
												<div class="row">
													<div class="col-md-1">
														<a href="javascript:;" onclick="addArticleFile()"><span class="fa fa-plus-circle fa-2x"></span></a>														
													</div>
													<div class="col-md-11" id="articleFiles">
														<div class="row first">
															<div class="col-md-11">
																<input type="file" style="right: auto !important;" class="form-control-file" name="article_img[]">
															</div>
															<div class="col-md-1">
																<a href="javascript:;" onclick="deleteArticleFile(this,event)"><span class="fa fa-trash fa-2x"></span></a>
															</div>
														</div>
													</div>
												</div>
											</div>
											<hr/>
											<!-- ========================================================== ZONE  Options d'édition ============================================= -->
											<h4 class="lh-0 pb-4"><?php echo L('editoption',"o"); ?></h4>
											<div class="row">
												<?php
												/*
												<div class="col-4">
													<p class="fs-6-plus fw-bold"><?php echo L('duplicateweb',"o"); ?></p>
													<?php 
													$pattern = '#[0-9]{1,2}\!\s#';
													$i = 0;
													$enonce1 = sprintf( "SELECT groupe.* FROM groupe
																	JOIN link_article_groupe USING(id_groupe)
																	WHERE id_groupe IN (select id_groupe from link_article_groupe where id_article = ?)
																	AND groupe.type !='fidelite'
																	AND groupe.inactif IS NULL
																	group by id_groupe");
													$resultGroupeOrder = query($enonce1,[$_REQUEST['id_article']],$dbAnimoCaisseDefault);
													$rowGroupeOrder = $resultGroupeOrder->fetch_assoc();
													
													if($resultGroupeOrder->num_rows > 0){
														$order = "ORDER BY ".$rowGroupeOrder['order1']." ASC";
														if($rowGroupeOrder['order2']!=''){ $order .= ", ".$rowGroupeOrder['order2']." ASC";}
														if($rowGroupeOrder['order3']!=''){ $order .= ", ".$rowGroupeOrder['order3']." ASC";}
														
														$enonce = sprintf( "SELECT article_desc.id_article, article_desc.nom_fr, poid, couleurs.couleurs_%s as couleurs, tailles.tailles_%s as tailles, types.types_%s as types, recettes.recettes_%s as recettes
																			FROM groupe
																			JOIN link_article_groupe USING(id_groupe)
																			JOIN article_desc USING(id_article)
																			LEFT JOIN couleurs USING(id_couleurs)
																			LEFT JOIN tailles USING(id_tailles)
																			LEFT JOIN types USING(id_types)
																			LEFT JOIN recettes USING(id_recettes)
																			WHERE id_groupe = %s
																			AND link_article_groupe.inactif IS NULL
																			AND groupe.type !='fidelite'
																			%s",$_SESSION['lang'],$_SESSION['lang'],$_SESSION['lang'],$_SESSION['lang'],$rowGroupeOrder['id_groupe'],$order);
														$resultGroupe = $dbAnimoCaisseDefault->query($enonce) or die(mysqli_error($dbAnimoCaisseDefault));
														while($rowGroupe = $resultGroupe->fetch_assoc()){
															
															if($rowGroupeOrder['order1'] == 'poid'){
																echo '<div class="ui dynamic checkbox mb-1"><input type="checkbox" name="group_'.$i.'" value="'.$rowGroupe['id_article'].'"> <label>'.$rowGroupe['nom_fr'].' ('.setPoid($rowGroupe[$rowGroupeOrder['order1']]).')</label></div>';
															}else{
																echo '<div class="ui dynamic checkbox mb-1"><input type="checkbox" name="group_'.$i.'" value="'.$rowGroupe['id_article'].'"> <label>'.$rowGroupe['nom_fr'].' ('.$rowGroupe[$rowGroupeOrder['order1']].')</label></div>';
															}
															echo "<br />";
															
															$i++;
														}
													}
													?>
													
												</div>
												*/
												?>
												<!-- ========================================================== Affichage sur le web ============================================-->
												<div class="col-4">
													<p class="fs-6-plus fw-bold"><?php echo L('displayweb',"o"); ?></p>
													<div class="ui dynamic checkbox mr-4 mb-1">
														<input type="checkbox" name="webReady" value="1" <?= $rowDesc["webReady"] == "1" ? "checked ":""?>>
														<label><?php echo L('webready',"o"); ?></label>
													</div>
													<br> 
												</div>
												<!-- ========================================================== Produits en vedette =========================================================-->
												<div class="col-4">
												    <p class="fs-6-plus fw-bold"><?php echo "Produits en vedette" ?></p>
													<div class="ui dynamic checkbox mb-1">
														<input 
    														type="checkbox" 
    														id="enVedette"
    														name="enVedette" 
    														value="1" <?= $rowDesc["enVedette"] == "1" ? "checked ":""?> >
														<label><?php echo L('mention `en vedette`',"o"); ?></label>
													</div>
													<div  id="hideDivDateEnVedette" style="display:none" >
														<p><?php echo "Date d'expiration" ?></p>
    													<div class="input-group bs-datepicker input-daterange picker-range">
    														<input  type="text" 
    														        class="form-control" 
    														        id="date_enVedette_expiration"
    														        name="date_enVedette_expiration"
    														        pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
    														        id="date_enVedette_expiration" 
    														        value="<?= empty(htmlentities($_GET["date_enVedette_expiration"])) ? "" : htmlentities($_GET["date_enVedette_expiration"])?>">
    													</div>		
    													<span class="date_enVedette_expiration"  style="color: #FF0000; display: none;"></span>
													</div>
											    </div>
												<!-- ========================================================== inactif =========================================================-->
												<div class="col-4">
													<p class="fs-6-plus fw-bold">État de l'article</p>
													<div class="ui dynamic checkbox mb-1">
														<input type="checkbox" name="inactif" value="1" <?php echo ($rowArticle["inactif"]=="1")?"checked":""; ?> />
														<label>Inactif</label>
													</div>
												</div>
											</div>
											<hr/>
											<!-- ========================================================== button =========================================================-->
											<div class="columns columns-right btn-group pull-right">
												<button class="btn btn-default" type="reset" name="refresh" aria-label="refresh" title="<?php echo L('refresh',"o"); ?>" onclick="$(this).closest('form').find('.ui.select-dropdown').dropdown('clear');" ><i class="icon-refresh"></i></button>
												<button type="submit" class="btn btn-primary"><?php echo L("soumettre","o") ?></button>
											</div>
											<input type="hidden" name="id_article" value="<?= attrEncode($rowArticle["id_article"])?>" />
											<input type="hidden" name="edit_produit" value="1">
										</form>
										<!-- ######################################################  FIN FORM  ###################################################### -->
									</div>
									
                                    <script>
                                        //__________________________ script 1 __________________________________________
                                        //Gestion hide et show calendrier pour la date 
                                        
                                         const checkboxEnVedette = document.querySelector('#enVedette');
                                         //Add checkbox event
                                         checkboxEnVedette.addEventListener('change',function(e){
                                            //Get element hideDivDateEnVedette
                                            const eleDiv = document.querySelector('#hideDivDateEnVedette');
                                            if (checkboxEnVedette.checked){
                                                 //Show input date
                                                eleDiv.style.display= 'block';      
                                             }else{
                                                 //Hide input date
                                                 eleDiv.style.display= 'none';  
                                             }
                                         })
                                         
                                        //_______________________ script 2 _____________________________________________
                                        var onSubmitAddArticle = function(monForm){
                                            
                                            try{
                                                /*recuperer tous les inputs rentrés dans la zone "Ajouter un distributeur" et les envoie étant que valeur du champs listFournisseur */
                                                var listStrFour = [];
                                                $("#tableFournisseur .ligneFour").map(function(index,element){
                                                    
                                                    listStrFour.push( $(this).find('.ligneFourI0')[0].value + "\t" + $(this).find('.ligneFourI1')[0].value + "\t" +
                                                					  $(this).find('.ligneFourI2')[0].value + "\t" + $(this).find('.ligneFourI3')[0].value + "\t" + 
                                                					  $(this).find('.ligneFourI4')[0].value + "\t" + (($(this).find('.ligneFourI5')[0].checked)?"1":"") );
                                                });
                                                monForm.listFournisseur.value = listStrFour.join("\n");
                                                
                                                //id_departement
                                                if(monForm.id_departement.value == ""){
                                                    document.querySelector("span.id_departement").style.display = "block";
                                                    monForm.id_departement.focus();
                                                    document.querySelector("span.id_departement").scrollIntoView(false);
                                                    return false;
                                                }else{
                                                    document.querySelector("span.id_departement").style.display = "none"; 
                                                }                                        
                                             
                                                
                                                if( monForm.id_departement.value != "" ) {
                                                    //validation supplémentaire pour verifier si les UPC choisies existe deja  
                                                    var formDict = {};
                                                    formDict["PLU"] = monForm.PLU.value;
                                                    formDict["PLU2"] = monForm.PLU2.value;                                     	
    	                                        	formDict["PLU3"] = monForm.PLU3.value;
    	                                            formDict["id_article"] = monForm.id_article.value;
    	                                        	formDict["action"] = "checkPluIfExists";    
    	                                        	
                                        			$.ajax({
                                            			type:"POST",
                                            			url: "ajax/produit_edit.php",  
                                            			data: formDict,
                                            			dataType:'json',
                                            			success:function(reponse){
                                            			    
                                            				if ( reponse.status == "success" ){
                                            				    if ( reponse.isValid ){
                                            				        monForm.submit();
                                            				    } else {
                                            				       // alert("UPC existe deja!");
                                            				       document.getElementById("msnErreurUPC").style.display = "block";
                                            				       document.getElementById("msnErreurUPC").scrollIntoView(false);
                                            				       document.getElementById("msnErreurUPC").innerHTML = "Le UPC (" + reponse.upcExiste + ") existe déjà!";  
                                            				       //setInterval(function(){ document.getElementById("msnErreurUPC").style.display = "none"; }, 7000);
                                            				    }
                                            				} else {
                                            				     //alert("Traitement d'erreur du ajax lui meme");   
                                            				     document.getElementById("alertErreurAjax").innerHTML =  "Un erreur est survenu, veuillez essayer à nouveau."; 
                                            				     document.getElementById("alertErreurAjax").style.display = "block";
                                            				     document.getElementById("alertErreurAjax").scrollIntoView(false);
                                            				     setInterval(function(){ document.getElementById("alertErreurAjax").style.display = "none"; }, 5000);
                                            				}
                                            			},
                                            			error:function(jqXHR){
                                            				 //Traitement d'erreur:  la demande a échoué!
                                            				 document.getElementById("alertErreurAjax").innerHTML =  "Un erreur de type (HTTP " +jqXHR.status+ ") est survenu au niveau de la réponse. Veuillez essayer à nouveau!"; 
                                        				     document.getElementById("alertErreurAjax").style.display = "block";
                                        				     document.getElementById("alertErreurAjax").scrollIntoView(false);
                                        				     setInterval(function(){ document.getElementById("alertErreurAjax").style.display = "none"; }, 5000);
                                            			},
    	                                            }); 
                                                }
                                                /*
                                                 //id_categorie 
                                                if(monForm.id_categorie.value == ""){
                                                    document.querySelector("span.id_categorie").style.display = "block";
                                                    monForm.id_categorie.focus();
                                                    document.querySelector("span.id_categorie").scrollIntoView(false);
                                                    return false;
                                                }else{
                                                    document.querySelector("span.id_categorie").style.display = "none"; 
                                                }                          
                                                */
                                               
                                            }catch (e){
                                            	log(e);
                                            }
                                            return false;
                                        }
                                    </script>
                                    
								</div>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	<!-- End Content-->
</section>