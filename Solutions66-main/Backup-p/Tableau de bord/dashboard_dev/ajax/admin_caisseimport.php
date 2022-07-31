<?php
ini_set('memory_limit', '256M');
<<<<<<< HEAD

=======
>>>>>>> 02661005a422649804a221a452910f5265930706
require_once('../req/init.php');

//Fixe
foreach( $_POST as $k => $v ){
	if ( preg_match('#^col_[0-9A-Za-z_]+$#',$k) ){
		$_POST[$k] = strtoupper($v);
	}
}

function callback_formatNomProduit( $matches ){
	return $matches[1] . strtolower($matches[2]);
}
function callback_formatNomProduit2( $matches ){
	return strtoupper($matches[2]);
}
function formatNomProduit($t){
	//Title
	//$t = mb_convert_case($t,MB_CASE_TITLE, "UTF-8");
	
	//Si qte avec unité
	//$t = preg_replace_callback( '#(\d+)\s{0,1}([a-zA-Z]{1,3})#', callback_formatNomProduit, $t );
	
	return $t;
}
function formatPrixEx($p){
	if ( !$p and $p !== 0 ){
		return "";
	}
	return number_format($p,2,","," ") . " $";
}
function formatCaseResultat( $data, $showOldValue = false ){
	global $listFournisseurLabel;
	
	if ( $data["error"] ){
		?>
		<td class="statuserror" colspan="<?= ($showOldValue)?"2":"1" ?>">
			<?php 
			echo $data["error"] 
			?>
		</td>
		<?php
	} else {
		if ( $data["showFournisseurLabel"] == "1" ){
			if ($showOldValue){
				?>
				<td class="status<?php echo $data["status"] ?>" >
					<?php echo ($listFournisseurLabel[$data["oldvalue"]]) ? htmlspecialchars($listFournisseurLabel[$data["oldvalue"]]) : "" ?>
				</td>
				<?php
			}
			?>
			<td class="status<?php echo $data["status"] ?>" 
				data-oldvalue="<?php echo ($data["status"] != "new") ? htmlspecialchars($listFournisseurLabel[$data["oldvalue"]]) : "* nouveau *" ?>" 
				title="<?php echo ($data["status"] != "new") ? htmlspecialchars($listFournisseurLabel[$data["oldvalue"]]) : "* nouveau *" ?>">
				<div class="newvalue"><?php echo htmlspecialchars($listFournisseurLabel[$data["newvalue"]]) ?></div>
				<?php if ( isset($data["info"]) ){ ?>
				<div class="infodata"><?php echo htmlspecialchars($data["info"]) ?></div>
				<?php } ?>
			</td>
			<?php
		} else if ( $data["newPrixLabel"] == "1" ){
			if ($showOldValue){
				?>
				<td class="status<?php echo $data["status"] ?>" style="text-align:right;">
					<?php echo ($data["oldvalue"]) ? formatPrixEx($data["oldvalue"]) : "" ?>
				</td>
				<?php
			}
			?>
			<td class="status<?php echo $data["status"] ?>"  style="text-align:right;"
				data-oldvalue="<?php echo ($data["status"] != "new") ? formatPrixEx($data["oldvalue"]) : "* nouveau *" ?>" 
				title="<?php echo ($data["status"] != "new") ? formatPrixEx($data["oldvalue"]) : "* nouveau *" ?>">
				<div class="newvalue"><?php echo formatPrixEx($data["newvalue"]) ?></div>
				<?php if ( isset($data["info"]) ){ ?>
				<div class="infodata"><?php echo htmlspecialchars($data["info"]) ?></div>
				<?php } ?>
			</td>
			<?php
		} else {
			if ($showOldValue){
				?>
				<td class="status<?php echo $data["status"] ?>" >
					<?php echo ($data["oldvalue"]) ? htmlspecialchars($data["oldvalue"]) : "" ?>
				</td>
				<?php
			}
			?>
			<td class="status<?php echo $data["status"] ?>" 
				data-oldvalue="<?php echo ($data["status"] != "new") ? htmlspecialchars($data["oldvalue"]) : "* nouveau *" ?>" 
				title="<?php echo ($data["status"] != "new") ? htmlspecialchars($data["oldvalue"]) : "* nouveau *" ?>">
				<div class="newvalue"><?php echo htmlspecialchars($data["newvalue"]) ?></div>
				<?php if ( isset($data["info"]) ){ ?>
				<div class="infodata"><?php echo htmlspecialchars($data["info"]) ?></div>
				<?php } ?>
			</td>
			<?php
		}
	}
}
function columnIndexFromString($pString){
	 static $_columnLookup = array(
			 'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'M' => 13,
			 'N' => 14, 'O' => 15, 'P' => 16, 'Q' => 17, 'R' => 18, 'S' => 19, 'T' => 20, 'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25, 'Z' => 26,
			 'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8, 'i' => 9, 'j' => 10, 'k' => 11, 'l' => 12, 'm' => 13,
			 'n' => 14, 'o' => 15, 'p' => 16, 'q' => 17, 'r' => 18, 's' => 19, 't' => 20, 'u' => 21, 'v' => 22, 'w' => 23, 'x' => 24, 'y' => 25, 'z' => 26
	 );
 
	 //      We also use the language construct isset() rather than the more costly strlen() function to match the length of $pString
	 //              for improved performance
	if (isset($pString{0})) {
		if (!isset($pString{1})) {
			return $_columnLookup[$pString];
		} elseif(!isset($pString{2})) {
			return $_columnLookup[$pString{0}] * 26 + $_columnLookup[$pString{1}];
		} elseif(!isset($pString{3})) {
			return $_columnLookup[$pString{0}] * 676 + $_columnLookup[$pString{1}] * 26 + $_columnLookup[$pString{2}];
		}
	}
	throw new Exception("Column string index can not be " . ((isset($pString{0})) ? "longer than 3 characters" : "empty") . ".");
}
function stringFromColumnIndex($pColumnIndex = 0){
	// Determine column string
	if ($pColumnIndex < 26) {
		return chr(65 + $pColumnIndex);
	} elseif ($pColumnIndex < 702) {
		return chr(64 + ($pColumnIndex / 26)).chr(65 + $pColumnIndex % 26);
	}
	return chr(64 + (($pColumnIndex - 26) / 676)).chr(65 + ((($pColumnIndex - 26) % 676) / 26)).chr(65 + $pColumnIndex % 26);
}
function calculCheckSumEAN12($code){
	//echo "calcul UPC checksum:".$code.":";
	$code = substr($code,0,11);
	$impair = substr($code,0,1) + substr($code,2,1) + substr($code,4,1) + substr($code,6,1) + substr($code,8,1) + substr($code,10,1);
	$pair = substr($code,1,1) + substr($code,3,1) + substr($code,5,1) + substr($code,7,1) + substr($code,9,1);
	$total = $impair * 3 + $pair;
	$checksum = 10-$total%10;
	if ($checksum == 10){
		$checksum = 0;
	}
	//echo $impair . ":" . $pair . ":" . $total . ":" . $checksum;
	return $checksum;
}

<<<<<<< HEAD
=======
$infoResultatCopie = [];
>>>>>>> 02661005a422649804a221a452910f5265930706

if ( $_GET["apply"] == "1" and $_POST["sheetName"] != "" and $_POST["fichier"] != "" and $_POST["col_UPC"] != "" ){
	$DEBUG_DB = ( isset($_POST["DEBUG"]) )?$_POST["DEBUG"]:1;
	ob_start();
	$reponse = [];
	
	$dbAnimoCaisseDefault->autocommit(false);
	$dbAnimoCaisse->autocommit(false);
	
	
	$dbAnimoCaisseDefault = new mysqli("localhost", $username_animo, $password_animo, "animoetc_caisse_default" );
	if ($dbAnimoCaisseDefault->connect_errno) {
		printf("Erreur de connection SQL : %s\n", $dbAnimoCaisseDefault->connect_error);
		exit();
	}
	$dbAnimoCaisseDefault->set_charset("utf8");
	
<<<<<<< HEAD
	
	
=======
>>>>>>> 02661005a422649804a221a452910f5265930706
	$dbAnimoCaisse->select_db("animoetc_dashboard");
	$enonce = sprintf("select *
						 from MAGASIN
						where caisse_db is not null");
	$resultMagasin = $dbAnimoCaisse->query($enonce) or die("MYSQL_ERROR:".__LINE__);
	$listLigneMag = [];
	while( $uneLigneMag = $resultMagasin->fetch_assoc() ){
		$listLigneMag[] = $uneLigneMag;
	}
	
<<<<<<< HEAD
	
=======
>>>>>>> 02661005a422649804a221a452910f5265930706
	$nbMiseAJour = 0;
	$nbInsert = 0;
	$nbIgnore = 0;
	
<<<<<<< HEAD
	
	$dreteLaStr = date("Y-m-d H:i:s");
	$listAFaire = json_decode($_POST["listAFaire"],true);
	
	//var_dump($listAFaire); die();
	
	foreach ( $listAFaire as $infoResultat){
		$estMiseAJour = false;
		$estInsert = false;
		//vex($infoResultat);
=======
	$dreteLaStr = date("Y-m-d H:i:s");
	$listAFaire = json_decode($_POST["listAFaire"],true);
	
	foreach ( $listAFaire as $infoResultat){
		$estMiseAJour = false;
		$estInsert = false;
		
>>>>>>> 02661005a422649804a221a452910f5265930706
		/////////////////////////////
		//Article
		$arrayDB = [];
		if ( $infoResultat["col_distributeur"] ){
			$arrayDB["id_distributeur"] = $infoResultat["id_distributeur"];
		}
		if($infoResultat["col_descFR"] and $infoResultat["col_descFR"]["status"] != "unchange"){
			$arrayDB["desc_fr"] = $infoResultat["col_descFR"]["newvalue"];  
			//$arrayDB["desc_fr"] = mb_convert_case($infoResultat["col_descFR"]["newvalue"], MB_CASE_UPPER, "UTF-8");   
		}
		if($infoResultat["col_descEN"] and $infoResultat["col_descEN"]["status"] != "unchange"){
			$arrayDB["desc_en"] = $infoResultat["col_descEN"]["newvalue"];
		}
		if($infoResultat["col_dep"] and $infoResultat["col_dep"]["status"] != "unchange"){
			$arrayDB["id_departement"] = $infoResultat["col_dep"]["newvalue"];
		}
		if($infoResultat["col_UPC"] and $infoResultat["col_UPC"]["status"] != "unchange"){
			$arrayDB["PLU"] = $infoResultat["col_UPC"]["newvalue"];
		}
		if($infoResultat["col_boite_nb"] and $infoResultat["col_boite_nb"]["status"] != "unchange"){
			$arrayDB["boite_nb"] = $infoResultat["col_boite_nb"]["newvalue"];
		}
		if($infoResultat["col_UPC_caisse"] and $infoResultat["col_UPC_caisse"]["status"] != "unchange"){
			$arrayDB["boite_PLU"] = $infoResultat["col_UPC_caisse"]["newvalue"];
		}

		if ( sizeof($arrayDB) > 0 ){
			$arrayDB["date_update"] = $dreteLaStr;
			if ( $infoResultat["id_article"] ){
				$arrayDB["id_article"] = $infoResultat["id_article"];
				faireUpdate_i( $arrayDB, "article", "id_article", $dbAnimoCaisseDefault, $DEBUG_DB );
				$estMiseAJour = true;
			} else {
				$arrayDB["date_insert"] = $dreteLaStr;
				faireInsert_i( $arrayDB, "article", $dbAnimoCaisseDefault, $DEBUG_DB );
				$infoResultat["id_article"] = $dbAnimoCaisseDefault->insert_id;
				$estInsert = true;
			}
		}
		
		////////////////////////
		// link_article_four
		if ( $infoResultat["id_article"] and $infoResultat["id_fournisseur"] ){
			$arrayDBlinkfour = [];
			
			if($infoResultat["col_code"] and $infoResultat["col_code"]["status"] != "unchange" ){
				$arrayDBlinkfour["num_four"] = $infoResultat["col_code"]["newvalue"];
			}
			if($infoResultat["col_cost"] and $infoResultat["col_cost"]["status"] != "unchange"){
				$arrayDBlinkfour["prix_coutant"] = $infoResultat["col_cost"]["newvalue"];
				if ( $arrayDB["boite_nb"] and $arrayDB["boite_nb"] > 1 ){
					$arrayDBlinkfour["prix_caisse"] = $infoResultat["col_cost"]["newvalue"] * $arrayDB["boite_nb"];
				}
			}
			
			if ( sizeof($arrayDBlinkfour) > 0  ){
				$arrayDBlinkfour["date_update"] = $dreteLaStr;
				$arrayDBlinkfour["discontinued"] = null;
				$arrayDBlinkfour["id_article"] = $infoResultat["id_article"];
				$arrayDBlinkfour["id_fournisseur"] = $infoResultat["id_fournisseur"];
				
				if ( $infoResultat["id_link_article_four"] ){
					$arrayDBlinkfour["id_link_article_four"] = $infoResultat["id_link_article_four"];
					faireUpdate_i( $arrayDBlinkfour, "link_article_four", "id_link_article_four", $dbAnimoCaisseDefault, $DEBUG_DB );
				} else {
					$arrayDBlinkfour["date_insert"] = $dreteLaStr;
					faireInsert_i( $arrayDBlinkfour, "link_article_four", $dbAnimoCaisseDefault, $DEBUG_DB );
					$infoResultat["id_link_article_four"] = $dbAnimoCaisseDefault->insert_id;
				}
				
				if ( $_POST["options_discontOtherDist"] == "1" ){
					$enonce = sprintf("update link_article_four set discontinued = '1' and date_update = '%s' where id_article = %s and id_fournisseur != %s",
										$dreteLaStr,$infoResultat["id_article"],$infoResultat["id_fournisseur"]);
					if ( $DEBUG_DB > 0 ){
						echo $enonce . "<br /><br />\n\n";
					}
					if ( $DEBUG_DB < 2 ){
						$dbAnimoCaisseDefault->query($enonce) or die("SQL:".__LINE__);
					}
				}
				$estMiseAJour = true;
			}
		}
		
<<<<<<< HEAD
		
=======
>>>>>>> 02661005a422649804a221a452910f5265930706
		//////////////////////////
		//   prix_change
		if ( $infoResultat["id_article"] and $infoResultat["col_prix"] and $infoResultat["col_prix"]["status"] != "unchange" ){
			$arrayDBprixchange = [];
			$arrayDBprixchange["id_article"] = $infoResultat["id_article"];
			$arrayDBprixchange["id_staff"] = $_SESSION["utilisateur"]["id_utilisateur"];
			$arrayDBprixchange["prix"] = $infoResultat["col_prix"]["newvalue"];
			$arrayDBprixchange["date_update"] = $dreteLaStr;
			
			if ( preg_match('#^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$#',$_POST["change_date_exp"]) ){
				$arrayDBprixchange["change_date_exp"] = $_POST["change_date_exp"];
			}elseif ( preg_match('#^\d{4}\-\d{2}\-\d{2}$#',$_POST["change_date_exp"]) ){
				$arrayDBprixchange["change_date_exp"] = $_POST["change_date_exp"] . " 23:59:59";
			}else{
				$arrayDBprixchange["change_date_exp"] = $dreteLaStr;
			}
<<<<<<< HEAD
			
			
			
=======
>>>>>>> 02661005a422649804a221a452910f5265930706
			if ( $_POST["prix_changeRightNow"] == "1" ){
				$arrayDBprixchange["change_hasbeenBC"] = 1;
				$arrayDBprixchange["change_done"] = $dreteLaStr;
				if ( $infoResultat["id_prix_change"] ){
					$arrayDBprixchange["id_prix_change"] = $infoResultat["id_prix_change"];
					faireUpdate_i( $arrayDBprixchange, "prix_change", "id_prix_change", $dbAnimoCaisseDefault, $DEBUG_DB );
				} else {
					$arrayDBprixchange["date_insert"] = $dreteLaStr;
					$arrayDBprixchange["qte"] = "1";
					faireInsert_i( $arrayDBprixchange, "prix_change", $dbAnimoCaisseDefault, $DEBUG_DB );
					$arrayDBprixchange["id_prix_change"] = $dbAnimoCaisseDefault->insert_id;
				}
				
				foreach($listLigneMag as $uneLigneMag){
					if ( $uneLigneMag["caisse_db"] != "" ){
						$dbAnimoCaisse->select_db($uneLigneMag["caisse_db"]);
						//echo "-=".$uneLigneMag["caisse_db"] . "=-";
						$arrayDBprix = [];
						$arrayDBprix["qte"] = $arrayDBprixchange["qte"];
						$arrayDBprix["date_debut"] = $arrayDBprixchange["date_debut"];
						$arrayDBprix["date_fin"] = $arrayDBprixchange["date_fin"];
						$arrayDBprix["id_article"] = $arrayDBprixchange["id_article"];
						$arrayDBprix["prix"] = $arrayDBprixchange["prix"];
						$arrayDBprix["date_update"] = date("Y-m-d H:i:s");
						
						$enonce = sprintf("select * from prix where qte = %s and id_article = %s and date_debut %s and date_fin %s",
											$arrayDBprixchange["qte"],
											$arrayDBprixchange["id_article"],
											(($arrayDBprix["date_debut"]!="")?" = '".$arrayDBprix["date_debut"]."'":"is null"),
											(($arrayDBprix["date_fin"]!="")?" = '".$arrayDBprix["date_fin"]."'":"is null")
										 );
						$resultTestPrix = $dbAnimoCaisse->query($enonce) or die($dbAnimoCaisse->error);
						if ( $uneLigneTestPrix = $resultTestPrix->fetch_assoc() ){
							//update
							$arrayDBprix["id_prix"] = $uneLigneTestPrix["id_prix"];
							faireUpdate_i( $arrayDBprix, "prix", "id_prix", $dbAnimoCaisse, $DEBUG_DB );
						} else {
							//insert
							$arrayDBprix["date_insert"] = date("Y-m-d H:i:s");
							faireInsert_i( $arrayDBprix, "prix", $dbAnimoCaisse, $DEBUG_DB );
						}
							
						$arrayDB = array("id_prix_change"=>$arrayDBprixchange["id_prix_change"],"date_update"=>date("Y-m-d H:i:s"),"change_done"=>date("Y-m-d H:i:s"));
						faireUpdate_i( $arrayDB, "prix_change", "id_prix_change", $dbAnimoCaisse, $DEBUG_DB );
					}
				}
				
			} else {
				$arrayDBprixchange["change_hasbeenBC"] = null;
				$arrayDBprixchange["change_done"] = null;
				
				if ( $infoResultat["id_prix_change"] ){
					$arrayDBprixchange["id_prix_change"] = $infoResultat["id_prix_change"];
					faireUpdate_i( $arrayDBprixchange, "prix_change", "id_prix_change", $dbAnimoCaisseDefault, $DEBUG_DB );
				} else {
					$arrayDBprixchange["date_insert"] = $dreteLaStr;
					$arrayDBprixchange["qte"] = "1";
					faireInsert_i( $arrayDBprixchange, "prix_change", $dbAnimoCaisseDefault, $DEBUG_DB );
				}
			}
<<<<<<< HEAD
			
			
			$estMiseAJour = true;
			
=======
			$estMiseAJour = true;
>>>>>>> 02661005a422649804a221a452910f5265930706
		}
		
		if ( $DEBUG_DB ){
			echo "<br /><br /><br />\n\n\n";
		}
		
		if ( $estInsert ){
			$nbInsert++;
		} elseif($estMiseAJour){
			$nbMiseAJour++;
		} else {
			$nbIgnore++;
		}
		
	}
	
	$dbAnimoCaisseDefault->commit();
	$dbAnimoCaisse->commit();
	
	?>
	<h4>Résumé des demandes</h4>
	<div>Nombre de produit mise à jour effectué : <?php echo $nbMiseAJour ?></div>
	<div>Nombre de produit ajouté effectué : <?php echo $nbInsert ?></div>
	<div>Nombre de produit erroré : <?php echo $nbIgnore ?></div>
	<?php 
	
	$reponse["status"] = "success";
	$reponse["nbInsert"] = $nbInsert;
	$reponse["nbMiseAJour"] = $nbMiseAJour;
	$reponse["nbIgnore"] = $nbIgnore;
<<<<<<< HEAD
	$reponse["data"] = ob_get_contents();//Stockez le contenu d'un tampon de sortie
=======
	$reponse["data"] = ob_get_contents();//Stockez le contenu d'un tampon
>>>>>>> 02661005a422649804a221a452910f5265930706
	ob_end_clean();//Libere le contenu d'un tampo to the browser
	echo json_encode($reponse);
	die();
}

/*
<<<<<<< HEAD
Traite le POST Ajax (p/admin_caisseimport.php).
Si on a un POST Ajax + le nom de la feuile Excel + le fichier +  UPC.
*/
if ( $_GET["test"] == "1" and $_POST["sheetName"] != "" and $_POST["fichier"] != "" and $_POST["col_UPC"] != "" ){
    
    //crée un tampon de sortie(content will not be sent to the browser)
	ob_start();
	$reponse = [];
	$DEBUG_DB = ( isset($_POST["DEBUG"]) )?$_POST["DEBUG"]:2;
	
	try{
	    //Open une connexion 
=======
Traite le POST Ajax from byPassFormSubmit() et aussi pour getExcelApercu()
Si on a un POST Ajax + le nom de la feuile Excel + le fichier +  UPC.
*/
if ( $_GET["test"] == "1" and $_POST["sheetName"] != "" and $_POST["fichier"] != "" and $_POST["col_UPC"] != "" ){
    //Debut du buffer
	ob_start(); 
	$reponse = [];
	$DEBUG_DB = ( isset($_POST["DEBUG"]) )?$_POST["DEBUG"]:2;
	try{
	    // _____________________________ Open  connexion  _____________________________
	    
>>>>>>> 02661005a422649804a221a452910f5265930706
		$dbAnimoCaisseDefault = new mysqli("localhost", $username_animo, $password_animo, "animoetc_caisse_default" );
		if ($dbAnimoCaisseDefault->connect_errno) {
			printf("Erreur de connection SQL : %s\n", $dbAnimoCaisseDefault->connect_error);
			exit();
		}
		$dbAnimoCaisseDefault->set_charset("utf8");
<<<<<<< HEAD
		
		require_once __DIR__ . "/../req/PHPExcel-1.8/PHPExcel.php";
		
		//SETUP cache
=======
		require_once __DIR__ . "/../req/PHPExcel-1.8/PHPExcel.php";
		
		//_________________________ Gestion chache _____________________________
		
>>>>>>> 02661005a422649804a221a452910f5265930706
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array( 'memoryCacheSize' => '32MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		
<<<<<<< HEAD
		//Va chercher le fichier
		$pathFichierTemp = __DIR__ . "/../../temp/tmp_excel_import/".$_POST["fichier"];
		//Si son extension  est valide, crée les objets selon le type
=======
		//_________________________ Instace objt Excel en mode lecture  _____________________________
		
		// '/home/animoetc/dashboard_dev/ajax/../../temp/tmp_excel_import/b7s98f0ev0evrhlsigbu6ednn7_1643384512.xlsx'
		$pathFichierTemp = __DIR__ . "/../../temp/tmp_excel_import/".$_POST["fichier"];   
		//Si  extension  valide, crée l'objet qui va lire le fichier
>>>>>>> 02661005a422649804a221a452910f5265930706
		if ( substr($pathFichierTemp,-4) == ".xls" ){
			$objReader = new PHPExcel_Reader_Excel5();
		} elseif ( substr($pathFichierTemp,-5) == ".xlsx" ){
			$objReader = new PHPExcel_Reader_Excel2007();
		}
<<<<<<< HEAD
		//Met l'objet en mode lecture seulement
		$objReader->setReadDataOnly(true);
		//l'objet charge le fichier
		$objPHPExcel = $objReader->load($pathFichierTemp);
		//Get sheet name
		$maSheet = $objPHPExcel->getSheetByName($_POST["sheetName"]);
		//Get max number of rows
		$nbLigne = $maSheet->getHighestRow();

=======
		// Set mode lecture seulement
		$objReader->setReadDataOnly(true);
		// Charge le fichier
		$objPHPExcel = $objReader->load($pathFichierTemp);
		// Get sheet by name
		$maSheet = $objPHPExcel->getSheetByName($_POST["sheetName"]);
		// Get max number of rows
		$nbLigne = $maSheet->getHighestRow();
		
    	//_________________________ Gestion Distributeur and fournisseur  _____________________________
        	
>>>>>>> 02661005a422649804a221a452910f5265930706
		//Get all champs from Distributeur and fournisseur 
		$listFournisseurLabel = [];
		$enonce = sprintf("select * from fournisseur");
		$resultFour = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
		while ( $uneLigneFour = $resultFour->fetch_assoc() ){
			$listFournisseurLabel[$uneLigneFour["id_fournisseur"]] = $uneLigneFour["nom"];
		}
		
		//Get one  Distributeur 
		$uneLigneFournisseur = null;
		if ( preg_match('#^\d+$#',$_POST['id_fournisseur']) ){
			$enonce = sprintf("select * from fournisseur where est_fournisseur = 1 and id_fournisseur = %s",$_POST['id_fournisseur']);
			$resultFournisseur = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
			$uneLigneFournisseur = $resultFournisseur->fetch_assoc();
		}
		
		//Get one fournisseur
		$uneLigneDistributeur = null;
		if ( preg_match('#^\d+$#',$_POST['id_distributeur']) ){
			$enonce = sprintf("select * from fournisseur where est_distributeur = 1 and id_fournisseur = %s",$_POST['id_distributeur']);
			$resultDistributeur = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
			$uneLigneDistributeur = $resultDistributeur->fetch_assoc();
		}
		
		$nbItemUpdated = 0;
		$nbItemAdded = 0;
		$nbItemIgnore = 0;
		$nbItemError = 0;
		?>
<<<<<<< HEAD
=======
		
>>>>>>> 02661005a422649804a221a452910f5265930706
		<div>Nombre de produit mise à jour : <span id="nbItemUpdated"></span></div>
		<div>Nombre de produit ajouté : <span id="nbItemAdded"></span></div>
		<div>Nombre de produit ignoré : <span id="nbItemIgnore"></span></div>
		<div><a href="#debutApercuErrors" >Nombre de produit erroné</a> : <span id="nbItemError"></span></div>
<<<<<<< HEAD
		
		<!--On remplie la table avec les inputs $_POST du Ajax   -->
=======
		<!--On remplie la table avec les inputs $_POST  Ajax   -->
>>>>>>> 02661005a422649804a221a452910f5265930706
		<table class="table table-hover tableprint printsml">
			<thead>
				<tr>
					<th>UPC</th><?php  $nbCol=1; ?>
					<?php if($uneLigneFournisseur){?><th>Distributeur</th><?php $nbCol++; } ?>
					<?php if($uneLigneDistributeur){?><th>Fournisseur</th><?php $nbCol++; } ?>
					<?php if($_POST["col_code"]!=""){?><th>Code fournisseur</th><?php $nbCol++; } ?>
					<?php if($_POST["col_descFR"]!=""){?><th>Description fr</th><?php $nbCol++; } ?>
					<?php if($_POST["col_descEN"]!=""){?><th>Description en</th><?php $nbCol++; } ?>
					<?php if($_POST["col_prix"]!=""){?><th>Ancien Prix coutant</th><?php $nbCol++; }  ?>
					<?php if($_POST["col_prix"]!=""){?><th>Prix coutant</th><?php $nbCol++; } ?>
					<?php if($_POST["col_cost"]!=""){?><th>Ancien Prix vendant</th><?php $nbCol++; }  ?>
					<?php if($_POST["col_cost"]!=""){?><th>Prix vendant</th><?php $nbCol++; } ?>
					<?php if($_POST["col_dep"]!=""){?><th>Département</th><?php $nbCol++; } ?>
					<?php if($_POST["col_SousDep"]!=""){?><th>Sous-département</th><?php $nbCol++; } ?>
					<?php if($_POST["col_boite_nb"]!=""){?><th>Nb par boite</th><?php $nbCol++; } ?>
					<?php if($_POST["col_UPC_caisse"]!=""){?><th>UPC caisse</th><?php $nbCol++; } ?>
				</tr>
			<thead>
			<tbody>
				<?php 
<<<<<<< HEAD
				$strCumulLigneError = "<tr><td colspan='".$nbCol."'><a name='debutApercuErrors'></a></td></tr>";
				$upcLimitError = 0;
				$first_line = intval($_POST["first_line"],10);
				for( $iLigne=$first_line; $iLigne <= $nbLigne; $iLigne++ ){
					if ( $upcLimitError >= 100 ){
						break;
					}
					$infoResultat = ["iLigne"=>$iLigne];
					$alertUPC_calcullastdigit = false;
					$uneLigneArticle = null;
					//Check up UPC
					$infoResultat["col_UPC"] = [];
=======
				$strCumulLigneError = "<tr><td colspan='".$nbCol."'><a name='debutApercuErrors'></a></td></tr>";  
				$upcLimitError = 0;
				$first_line = intval($_POST["first_line"],10);
				
				$dataExcelCopie =[];
				// Pour chaque ligne du fichier
				for( $iLigne = $first_line; $iLigne <= $nbLigne; $iLigne++ ){
				    
					if ( $upcLimitError >= 100 ){
						break;
					}
					$infoResultat = ["iLigne"=>$iLigne];  
					$alertUPC_calcullastdigit = false;
					$uneLigneArticle = null;
					
					//_________________________ Gestion UPC _____________________________
					
					$infoResultat["col_UPC"] = []; 
>>>>>>> 02661005a422649804a221a452910f5265930706
					$UPC = $maSheet->getCell($_POST["col_UPC"].$iLigne)->getCalculatedValue();
					$UPC = preg_replace('/[^0-9]/','',$UPC);
					if ( strlen($UPC) >= 9 and strlen($UPC) <= 16 ){
						
						//if ( strlen($UPC) >= 12 ){
						if ( strlen($UPC) == 11 ){
							$UPC .= calculCheckSumEAN12($UPC);
							$alertUPC_calcullastdigit = true;
						}
						
						while (strlen($UPC) < 12){
							$UPC = "0" . $UPC;
						}
					}
<<<<<<< HEAD
                    //On va chercher les articles
					$enonce = sprintf("select * from article where PLU = '%s' or PLU2 = '%s' or PLU3 = '%s'",$UPC,$UPC,$UPC);
					$resultTestArticle = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
					if ( $uneLigneArticle = $resultTestArticle->fetch_assoc() ){
						$infoResultat["id_article"] = $uneLigneArticle["id_article"];
=======
                    //Va chercher un  article
					$enonce = sprintf("select * from article where PLU = '%s' or PLU2 = '%s' or PLU3 = '%s'",$UPC,$UPC,$UPC);
					$resultTestArticle = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
					
					if ( $uneLigneArticle = $resultTestArticle->fetch_assoc() ){
						$infoResultat["id_article"] = $uneLigneArticle["id_article"]; 
>>>>>>> 02661005a422649804a221a452910f5265930706
						$infoResultat["col_UPC"]["status"] = "unchange";
						$infoResultat["col_UPC"]["oldvalue"] = $UPC;
					} else {
						$infoResultat["col_UPC"]["status"] = "new";
						$infoResultat["col_UPC"]["oldvalue"] = "";
					}
<<<<<<< HEAD
					$infoResultat["col_UPC"]["newvalue"] = $UPC;
=======
					$infoResultat["col_UPC"]["newvalue"] = $UPC;    
>>>>>>> 02661005a422649804a221a452910f5265930706
					if ( strlen($UPC) == 0 ) $upcLimitError++;
					if ( strlen($UPC) < 8 or strlen($UPC) > 14 ){
						$infoResultat["error"] = "UPC non-valide";
					} else {
<<<<<<< HEAD
						$upcLimitError = 0;
=======
						$upcLimitError = 0; 
>>>>>>> 02661005a422649804a221a452910f5265930706
						
						if( $uneLigneDistributeur ){
							$infoResultat["col_distributeur"] = [];
							$distributeur = $uneLigneDistributeur["id_fournisseur"];
							$infoResultat["id_distributeur"] = $uneLigneDistributeur["id_fournisseur"];
							
							if ( $uneLigneArticle ){
								$infoResultat["col_distributeur"]["status"] = ($uneLigneArticle["id_distributeur"]==$distributeur) ? "unchange" : "update";
								$infoResultat["col_distributeur"]["oldvalue"] = $uneLigneArticle["id_distributeur"];
							} else {
								$infoResultat["col_distributeur"]["status"] = "new";
								$infoResultat["col_distributeur"]["oldvalue"] = "";
							}
							$infoResultat["col_distributeur"]["newvalue"] = $distributeur;
							$infoResultat["col_distributeur"]["showFournisseurLabel"] = 1;
<<<<<<< HEAD
						}
	
=======
						} 
>>>>>>> 02661005a422649804a221a452910f5265930706
						if( $uneLigneFournisseur ){
							$infoResultat["col_fournisseur"] = [];
							$fournisseur = $uneLigneFournisseur["id_fournisseur"];
							$infoResultat["id_fournisseur"] = $uneLigneFournisseur["id_fournisseur"];
							
							if ( $uneLigneArticle ){
								$enonce = sprintf("select *
													 from link_article_four
													where id_article = %s
													  and id_fournisseur = %s", $uneLigneArticle["id_article"], $uneLigneFournisseur["id_fournisseur"] );
								$resultArtFour = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
								if ( $uneLigneArtFour = $resultArtFour->fetch_assoc() ){
									$infoResultat["id_link_article_four"] = $uneLigneArtFour["id_link_article_four"];
									$infoResultat["col_fournisseur"]["status"] = "unchange";
									$infoResultat["col_fournisseur"]["oldvalue"] = $uneLigneArtFour["id_fournisseur"];
								} else {
									$infoResultat["col_fournisseur"]["status"] = "new";
									$infoResultat["col_fournisseur"]["oldvalue"] = "";
								}
							} else {
								$infoResultat["col_fournisseur"]["status"] = "new";
								$infoResultat["col_fournisseur"]["oldvalue"] = "";
							}
							$infoResultat["col_fournisseur"]["newvalue"] = $fournisseur;
							$infoResultat["col_fournisseur"]["showFournisseurLabel"] = 1;
							
						}
<<<<<<< HEAD
						
=======
>>>>>>> 02661005a422649804a221a452910f5265930706
						//col_descFR
						if( $_POST["col_descFR"] != "" ){
							$infoResultat["col_descFR"] = [];
							$descFR = $maSheet->getCell($_POST["col_descFR"].$iLigne)->getCalculatedValue();
							$descFR = formatNomProduit($descFR);
							
							if ( $uneLigneArticle ){
								$infoResultat["col_descFR"]["status"] = ($uneLigneArticle["desc_fr"]==$descFR) ? "unchange" : "update";
								$infoResultat["col_descFR"]["oldvalue"] = $uneLigneArticle["desc_fr"];
							} else {
								$infoResultat["col_descFR"]["status"] = "new";
								$infoResultat["col_descFR"]["oldvalue"] = "";
							}
							$infoResultat["col_descFR"]["newvalue"] = $descFR;
						}
						//col_descEN
						if($_POST["col_descEN"]!=""){
							$infoResultat["col_descEN"] = [];
							$descEN = $maSheet->getCell($_POST["col_descEN"].$iLigne)->getCalculatedValue();
							$descEN = formatNomProduit($descEN);
							
							if ( $uneLigneArticle ){
								$infoResultat["col_descEN"]["status"] = ($uneLigneArticle["desc_en"]==$descEN) ? "unchange" : "update";
								$infoResultat["col_descEN"]["oldvalue"] = $uneLigneArticle["desc_en"];
							} else {
								$infoResultat["col_descEN"]["status"] = "new";
								$infoResultat["col_descEN"]["oldvalue"] = "";
							}
							$infoResultat["col_descEN"]["newvalue"] = $descEN;
						}
						//col_prix
						if($_POST["col_prix"]!=""){
							$infoResultat["col_prix"] = [];
							$prix = $maSheet->getCell($_POST["col_prix"].$iLigne)->getCalculatedValue();
							$prix = str_replace(',','.',$prix);
							$prix = floatval( preg_replace('#[^0-9\,\.]#','',$prix) );
							
							
							if ( $_POST["prix_forcerounddown99"] == "1" ){
								$minprix = str_replace(',','.',$_POST["prix_forcerounddown99_minprix"]);
								$minprix = max( floatval( preg_replace('#[^0-9\,\.]#','',$minprix) ), 1 );
								if ( $prix >= $minprix and ceil($prix) != $prix + 0.01 ){
									$prix = floor($prix) - 0.01;
								}
							}
							$prix = round($prix,2);
							
							if ( $uneLigneArticle ){
								//Aller chercher le prix
								$enonce = sprintf("select *
													 from prix_change
													where id_article = %s
													  and qte = 1 order by change_date_exp desc, id_prix_change desc", $uneLigneArticle["id_article"] );
													  // and change_hasbeenBC is null and change_done is null
								$resultTestPrix = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
								if ( $uneLignePrixChange = $resultTestPrix->fetch_assoc() ){
									$infoResultat["col_prix"]["oldvalue"] = $uneLignePrix["prix"];
										
									if ( $uneLignePrixChange["change_hasbeenBC"] == "" ){
										$infoResultat["id_prix_change"] = $uneLignePrixChange["id_prix_change"];
										$infoResultat["col_prix"]["status"] = ($uneLignePrixChange["prix"]==$prix) ? "unchange" : "update";
									} else {
										$infoResultat["col_prix"]["status"] = ($uneLignePrixChange["prix"]==$prix) ? "unchange" : "new";
									}
									
									if ( $uneLignePrixChange["change_done"] == "" ){
										$infoResultat["col_prix"]["info"] = "Prix déjà en vu d'être changé";
									}
									
									$infoResultat["col_prix"]["oldvalue"] = $uneLignePrixChange["prix"];
									
									/*
									//Chercher old value
									$enonce = sprintf("select *
														 from prix
														where id_article = %s
														  and qte = 1", $uneLigneArticle["id_article"] );
									$resultTestPrix2 = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
									$uneLignePrix = $resultTestPrix2->fetch_assoc();
									$infoResultat["col_prix"]["oldvalue"] = $uneLignePrix["prix"];
									
									$infoResultat["col_prix"]["status"] = "update";
									*/
									
								} else {
									//Voir si le prix maintenant
									$enonce = sprintf("select *
														 from prix
														where id_article = %s
														  and qte = 1", $uneLigneArticle["id_article"] );
									$resultTestPrix2 = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
									if ( $uneLignePrix = $resultTestPrix2->fetch_assoc() ){
										$infoResultat["col_prix"]["status"] = ($uneLignePrix["prix"]==$prix) ? "unchange" : "update";
										$infoResultat["col_prix"]["oldvalue"] = $uneLignePrix["prix"];
									} else {
										$infoResultat["col_prix"]["status"] = "new";
										$infoResultat["col_prix"]["oldvalue"] = "";
									}
								}
							} else {
								$infoResultat["col_prix"]["status"] = "new";
								$infoResultat["col_prix"]["oldvalue"] = "";
							}
							
							$infoResultat["col_prix"]["newvalue"] = $prix;
							$infoResultat["col_prix"]["newPrixLabel"] = 1;
						}
						//col_cost 
						if( $_POST["col_cost"]!="" ){
							$infoResultat["col_cost"] = [];
							$cost = $maSheet->getCell($_POST["col_cost"].$iLigne)->getCalculatedValue();
							$cost = str_replace(',','.',$cost);
							$cost = floatval( preg_replace('#[^0-9\,\.]#','',$cost) );
							
							if ( $_POST["prix_costpourcaisse"] == "1" and $_POST["col_boite_nb"] != "" ){
								$boite_nb = $maSheet->getCell($_POST["col_boite_nb"].$iLigne)->getCalculatedValue();
								if ( $boite_nb == "" ) $boite_nb = "1";
								$boite_nb = intval( preg_replace('#[^0-9\,\.]#','',$boite_nb) );
								$cost = round( $cost/$boite_nb, 2 );
							}
							
							
							if ( $uneLigneFournisseur ){
								
								if ( $uneLigneArticle ){
									$enonce = sprintf("select *
														 from link_article_four
														where id_article = %s
														  and id_fournisseur = %s", $uneLigneArticle["id_article"], $uneLigneFournisseur["id_fournisseur"] );
									$resultArtFour = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
									if ( $uneLigneArtFour = $resultArtFour->fetch_assoc() ){
										$infoResultat["col_cost"]["status"] = ($uneLigneArtFour["prix_coutant"]==$cost) ? "unchange" : "update";
										$infoResultat["col_cost"]["oldvalue"] = $uneLigneArtFour["prix_coutant"];
									} else {
										$infoResultat["col_cost"]["status"] = "new";
										$infoResultat["col_cost"]["oldvalue"] = "";
									}
								} else {
									$infoResultat["col_cost"]["status"] = "new";
									$infoResultat["col_cost"]["oldvalue"] = "";
								}
								
								$infoResultat["col_cost"]["newvalue"] = $cost;
								$infoResultat["col_cost"]["newPrixLabel"] = 1;
								
							} else {
								$infoResultat["col_cost"]["error"] = "Aucun distributeur sélectionné";
							}
						}
						// col_code
						if($_POST["col_code"]!=""){
							$infoResultat["col_code"] = [];
							$code = $maSheet->getCell($_POST["col_code"].$iLigne)->getCalculatedValue();
							
							if ( $uneLigneFournisseur ){
								if ( $uneLigneArticle ){
									$enonce = sprintf("select *
														 from link_article_four
														where id_article = %s
														  and id_fournisseur = %s", $uneLigneArticle["id_article"], $uneLigneFournisseur["id_fournisseur"] );
									$resultArtFour = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
									if ( $uneLigneArtFour = $resultArtFour->fetch_assoc() ){
										$infoResultat["col_code"]["status"] = ($uneLigneArtFour["num_four"]==$code) ? "unchange" : "update";
										$infoResultat["col_code"]["oldvalue"] = $uneLigneArtFour["num_four"];
									} else {
										$infoResultat["col_code"]["status"] = "new";
										$infoResultat["col_code"]["oldvalue"] = "";
									}
								} else {
									$infoResultat["col_code"]["status"] = "new";
									$infoResultat["col_code"]["oldvalue"] = "";
								}
								$infoResultat["col_code"]["newvalue"] = $code;
									
							} else {
								$infoResultat["col_code"]["error"] = "Aucun distributeur sélectionné";
							}
						}
						// col_dep
						if($_POST["col_dep"]!=""){
							$infoResultat["col_dep"] = [];
							$dep = $maSheet->getCell($_POST["col_dep"].$iLigne)->getCalculatedValue();
							$dep = intval( preg_replace('#[^0-9]#','',$dep) );
								
							if ( $uneLigneArticle ){
								$infoResultat["col_dep"]["status"] = ($uneLigneArticle["id_departement"]==$dep) ? "unchange" : "update";
								$infoResultat["col_dep"]["oldvalue"] = $uneLigneArticle["id_departement"];
							} else {
								$infoResultat["col_dep"]["status"] = "new";
								$infoResultat["col_dep"]["oldvalue"] = "";
							}
							$infoResultat["col_dep"]["newvalue"] = $dep;
						}
						//col_SousDep
						if($_POST["col_SousDep"]!=""){
							$SousDep = $maSheet->getCell($_POST["col_SousDep"].$iLigne)->getCalculatedValue();
							$SousDep = preg_replace('#[^0-9,]#','',$SousDep);
							
							if ( $uneLigneArticle ){
								$enonce = sprintf("select group_concat(id_categorie order by id_categorie asc) `list_id_categorie` 
													 from article_categorie_link 
													where id_article = %s 
												 group by id_article",$uneLigneArticle["id_article"]);
								$resultTestArticleDesc = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
								$uneLigneArticleDesc = $resultTestArticleDesc->fetch_assoc();
								if ( $uneLigneArticleDesc and $uneLigneArticleDesc["list_id_categorie"] != "" ){
									$listTemp = ($SousDep!="") ? explode(",",$SousDep) : [];
									sort( $listTemp );
									
									$infoResultat["col_SousDep"]["status"] = ($uneLigneArticleDesc["list_id_categorie"]==implode(",",$listTemp)) ? "unchange" : "update";
									$infoResultat["col_SousDep"]["oldvalue"] = $uneLigneArticleDesc["list_id_categorie"];
								} else {
									$infoResultat["col_SousDep"]["status"] = "new";
									$infoResultat["col_SousDep"]["oldvalue"] = "";
								}
							} else {
								$infoResultat["col_SousDep"]["status"] = "new";
								$infoResultat["col_SousDep"]["oldvalue"] = "";
							}
							
							$infoResultat["col_SousDep"]["newvalue"] = $SousDep;
							
						}
						// col_boite_nb
						if($_POST["col_boite_nb"]!=""){
							$infoResultat["col_boite_nb"] = [];
							$boite_nb = $maSheet->getCell($_POST["col_boite_nb"].$iLigne)->getCalculatedValue();
							$boite_nb = intval( preg_replace('#[^0-9\,\.]#','',$boite_nb) );
							$boite_nb = max( $boite_nb, 1 );
							
							if ( $uneLigneArticle ){
								$infoResultat["col_boite_nb"]["status"] = ($uneLigneArticle["boite_nb"]==$boite_nb) ? "unchange" : "update";
								$infoResultat["col_boite_nb"]["oldvalue"] = $uneLigneArticle["boite_nb"];
							} else {
								$infoResultat["col_boite_nb"]["status"] = "new";
								$infoResultat["col_boite_nb"]["oldvalue"] = "";
							}
							$infoResultat["col_boite_nb"]["newvalue"] = $boite_nb;
						}
						// col_UPC_caisse
						if($_POST["col_UPC_caisse"]!=""){
							$infoResultat["col_UPC_caisse"] = [];
							$UPC_caisse = $maSheet->getCell($_POST["col_UPC_caisse"].$iLigne)->getCalculatedValue();
							$UPC_caisse = preg_replace('#[^0-9]#','',$UPC_caisse);
								
							if ( $uneLigneArticle ){
								$infoResultat["col_UPC_caisse"]["status"] = ($uneLigneArticle["boite_PLU"]==$UPC_caisse) ? "unchange" : "update";
								$infoResultat["col_UPC_caisse"]["oldvalue"] = $uneLigneArticle["boite_PLU"];
							} else {
								$infoResultat["col_UPC_caisse"]["status"] = "new";
								$infoResultat["col_UPC_caisse"]["oldvalue"] = "";
							}
							$infoResultat["col_UPC_caisse"]["newvalue"] = $UPC_caisse;
						}
						
						// Le faire avant les filtres
						if ( $_POST["prix_dontchangepriceifsamecost"] == "1" and $infoResultat["col_cost"] and $infoResultat["col_cost"]["status"] == "unchange" ){
							if ( $infoResultat["col_prix"]["newvalue"] and $infoResultat["col_prix"]["oldvalue"] and $infoResultat["col_prix"]["newvalue"] > $infoResultat["col_prix"]["oldvalue"] ){
								$infoResultat["col_prix"]["error"] = "Ne pas changer le prix de vente plus haut si le cost est le même.";
								$infoResultat["col_prix"]["status"] = "unchange";
							}
						}
						
						//////////////////////////////
						/// Filtrer avec les options quand update
						if ( $uneLigneArticle ){
<<<<<<< HEAD
							
							
=======
>>>>>>> 02661005a422649804a221a452910f5265930706
							if ( ($_POST["overwrite_codefour"] != "1" and isset($infoResultat["col_code"])
																	 and $infoResultat["col_code"]["newvalue"] != $infoResultat["col_code"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_code"]["oldvalue"] != "") ){
								$infoResultat["col_code"]["newvalue"] = $infoResultat["col_code"]["oldvalue"];
								$infoResultat["col_code"]["status"] = "unchange";
								$infoResultat["col_code"]["info"] = "Mise à jour ignorée";
							}
							if ( ($_POST["overwrite_descFR"] != "1" and isset($infoResultat["col_descFR"])
																   and $infoResultat["col_descFR"]["newvalue"] != $infoResultat["col_descFR"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_descFR"]["oldvalue"] != "") ){
								$infoResultat["col_descFR"]["newvalue"] = $infoResultat["col_descFR"]["oldvalue"];
								$infoResultat["col_descFR"]["status"] = "unchange";
								$infoResultat["col_descFR"]["info"] = "Mise à jour ignorée";
							}
							if ( ($_POST["overwrite_descEN"] != "1" and isset($infoResultat["col_descEN"])
																   and $infoResultat["col_descEN"]["newvalue"] != $infoResultat["col_descEN"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_descEN"]["oldvalue"] != "") ){
								$infoResultat["col_descEN"]["newvalue"] = $infoResultat["col_descEN"]["oldvalue"];
								$infoResultat["col_descEN"]["status"] = "unchange";
								$infoResultat["col_descEN"]["info"] = "Mise à jour ignorée";
							}
							
							
							if ( ($_POST["overwrite_prix"] != "1" and isset($infoResultat["col_prix"])
																   and $infoResultat["col_prix"]["newvalue"] != $infoResultat["col_prix"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_prix"]["oldvalue"] != "") ){
								$infoResultat["col_prix"]["newvalue"] = $infoResultat["col_prix"]["oldvalue"];
								$infoResultat["col_prix"]["status"] = "unchange";
								$infoResultat["col_prix"]["info"] = "Mise à jour ignorée";
							} else if ( $uneLigneFournisseur and $_POST["overwrite_disabledIfMinusCost"] == "1" and $infoResultat["col_cost"]["newvalue"] ){
								//Si cost, voir si le plus pas, si non, ignoré le changement de prix vendant
								$enonce = sprintf("select *
													 from link_article_four
													where id_article = %s
													  and id_fournisseur != %s
													  and discontinued is not null
													  and id_fournisseur != 15
													  and prix_coutant < %F", $uneLigneArticle["id_article"], $uneLigneFournisseur["id_fournisseur"], $infoResultat["col_cost"]["newvalue"] );
								//echo $enonce;
								$resultTestCost = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
								if( $resultTestCost->num_rows > 0 ){
									$infoResultat["col_prix"]["newvalue"] = $infoResultat["col_prix"]["oldvalue"];
									$infoResultat["col_prix"]["status"] = "unchange";
									$infoResultat["col_prix"]["info"] = "Mise à jour ignorée (cost moins chère)";
								}
							} 
							
							if ( isset($infoResultat["col_prix"]) and isset($infoResultat["col_prix"]["status"]) and $infoResultat["col_prix"]["status"] != "unchange" ){
								$enonce = sprintf("select *
													 from prix_change
													where id_article = %s
													  and qte = 1
													  and prix = %F
													  and change_done is null", $uneLigneArticle["id_article"], $infoResultat["col_prix"]["newvalue"] );
								
								$resultTestPrix = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
								if ( $uneLignePrixChange = $resultTestPrix->fetch_assoc() ){
									$infoResultat["col_prix"]["status"] = "unchange";
									$infoResultat["col_prix"]["info"] = "Même prix qu'un changement de prix en cours";
									$infoResultat["col_prix"]["oldvalue"] = $uneLignePrixChange["prix"];
								}
							}
							
							if ( isset($infoResultat["col_prix"]) and $infoResultat["col_prix"]["status"] != "unchange" ){
								//Voir si dernier change prix est locked   40555
								$enonce = sprintf("select *
													 from prix_change
													where id_article = %s
												 order by date_update desc", $uneLigneArticle["id_article"]);
								//echo $enonce;
								$resultTestCostLock = $dbAnimoCaisseDefault->query($enonce) or die(__LINE__."::".$dbAnimoCaisseDefault->error);
								$uneLigneTestArticleLock = $resultTestCostLock->fetch_assoc();
								if( $uneLigneTestArticleLock and $uneLigneTestArticleLock["locked"] == "1" ){
									$infoResultat["col_prix"]["newvalue"] = $infoResultat["col_prix"]["oldvalue"];
									$infoResultat["col_prix"]["status"] = "unchange";
									$infoResultat["col_prix"]["info"] = "Mise à jour ignorée (prix locked)";
								}
							}
							
							if ( ($_POST["overwrite_cost"] != "1" and isset($infoResultat["col_cost"])
																   and $infoResultat["col_cost"]["newvalue"] != $infoResultat["col_cost"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_cost"]["oldvalue"] != "") ){
								$infoResultat["col_cost"]["newvalue"] = $infoResultat["col_cost"]["oldvalue"];
								$infoResultat["col_cost"]["status"] = "unchange";
								$infoResultat["col_cost"]["info"] = "Mise à jour ignorée";
							}
							
							if ( ($_POST["overwrite_dep"] != "1" and isset($infoResultat["col_dep"])
																   and $infoResultat["col_dep"]["newvalue"] != $infoResultat["col_dep"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_dep"]["oldvalue"] != "") ){
								$infoResultat["col_dep"]["newvalue"] = $infoResultat["col_dep"]["oldvalue"];
								$infoResultat["col_dep"]["status"] = "unchange";
								$infoResultat["col_dep"]["info"] = "Mise à jour ignorée";
							}
							if ( ($_POST["overwrite_sousdep"] != "1" and isset($infoResultat["col_SousDep"])
																   and $infoResultat["col_SousDep"]["newvalue"] != $infoResultat["col_SousDep"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_SousDep"]["oldvalue"] != "") ){
								$infoResultat["col_SousDep"]["newvalue"] = $infoResultat["col_SousDep"]["oldvalue"];
								$infoResultat["col_SousDep"]["status"] = "unchange";
								$infoResultat["col_SousDep"]["info"] = "Mise à jour ignorée";
							}
							if ( ($_POST["overwrite_nombreparboite"] != "1" and isset($infoResultat["col_boite_nb"])
																   and $infoResultat["col_boite_nb"]["newvalue"] != $infoResultat["col_boite_nb"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_boite_nb"]["oldvalue"] != "") ){
								$infoResultat["col_boite_nb"]["newvalue"] = $infoResultat["col_boite_nb"]["oldvalue"];
								$infoResultat["col_boite_nb"]["status"] = "unchange";
								$infoResultat["col_boite_nb"]["info"] = "Mise à jour ignorée";
							}
							if ( ($_POST["overwrite_dist"] != "1" and isset($infoResultat["col_fournisseur"])
																   and $infoResultat["col_fournisseur"]["newvalue"] != $infoResultat["col_fournisseur"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_fournisseur"]["oldvalue"] != "") ){
								$infoResultat["col_fournisseur"]["newvalue"] = $infoResultat["col_fournisseur"]["oldvalue"];
								$infoResultat["col_fournisseur"]["status"] = "unchange";
								$infoResultat["col_fournisseur"]["info"] = "Mise à jour ignorée";
							}
							if ( ($_POST["overwrite_four"] != "1" and isset($infoResultat["col_distributeur"])
																   and $infoResultat["col_distributeur"]["newvalue"] != $infoResultat["col_distributeur"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_distributeur"]["oldvalue"] != "") ){
								$infoResultat["col_distributeur"]["newvalue"] = $infoResultat["col_distributeur"]["oldvalue"];
								$infoResultat["col_distributeur"]["status"] = "unchange";
								$infoResultat["col_distributeur"]["info"] = "Mise à jour ignorée";
							}
							if ( ($_POST["overwrite_upccaisse"] != "1" and isset($infoResultat["col_UPC_caisse"])
																and $infoResultat["col_UPC_caisse"]["newvalue"] != $infoResultat["col_UPC_caisse"]["oldvalue"])
								  or ($_POST["overwrite_onlyidOldIsNone"] == "1" and $infoResultat["col_UPC_caisse"]["oldvalue"] != "") ){
								$infoResultat["col_UPC_caisse"]["newvalue"] = $infoResultat["col_UPC_caisse"]["oldvalue"];
								$infoResultat["col_UPC_caisse"]["status"] = "unchange";
								$infoResultat["col_UPC_caisse"]["info"] = "Mise à jour ignorée";
							}
						} elseif ( $_POST["options_updateonly"] == "1" ) {
							$infoResultat["error"] = "Ajout abandonné; seulement faire les mise à jour.";
						}
                        
					}
					//si cost audessus du prix
					if($_POST["col_cost"]!="" and $_POST["col_prix"]!="" and $prix <= $cost){
						$infoResultat["error"] = "Refusé, prix en dessous du cost.";
					}
					if( $_POST["col_prix"] != "" and $prix < 0.01){
						$infoResultat["error"] = "Refusé, prix à zéro.";
					}
					////////////////////////////////
					//  Show ligne table
					if ( $infoResultat["error"] ){
						$strCumulLigneError = "";
						$nbItemError++;
						$strCumulLigneError .= '<tr><td class="statuserror" colspan="'.$nbCol.'" >';
						$strCumulLigneError .= 'Ligne #' . $infoResultat["iLigne"] . " : ";
						
						if ( $infoResultat["col_UPC"]["newvalue"] != "" ){
							$strCumulLigneError .= $infoResultat["error"]. " : " . $infoResultat["col_UPC"]["newvalue"];
							if ( $infoResultat["col_code"] and $infoResultat["col_code"]["newvalue"] ){
								$strCumulLigneError .= " : " . $infoResultat["col_code"]["newvalue"];
							}
							if ( $infoResultat["col_descFR"] and $infoResultat["col_descFR"]["newvalue"] ){
								$strCumulLigneError .= " : " . $infoResultat["col_descFR"]["newvalue"];
							}
							$strCumulLigneError .= '</td></tr>';
						} else {
							$strCumulLigneError .= $infoResultat["error"];
						}
						$strCumulLigneError .= '</td></tr>';
						echo $strCumulLigneError;
						
					} else {
						$aFaire = false;
						if ( $uneLigneArticle ){
							$isModified = false;
							foreach( $infoResultat as $champ=>$data ){
								if ( substr($champ,0,4) == "col_" and $data["status"] != "unchange" ){
									$isModified = true;
									$nbItemUpdated++;
									$aFaire = true;
									break;
								}
							}
							if ( !$isModified ){
								$nbItemIgnore++;
							}
						} else {
							$aFaire = true;
							$nbItemAdded++;
						}
						
						if ( $aFaire ){
							?>
							<tr class="aFaire" data-info="<?php echo htmlentities(json_encode($infoResultat)) ?>">
							<?php
						} else {
							?>
							<tr>
							<?php
						}
						?>
<<<<<<< HEAD
						
						
=======
>>>>>>> 02661005a422649804a221a452910f5265930706
						<td class="status<?php echo $infoResultat["col_UPC"]["status"] ?>" 
							data-oldvalue="<?php echo htmlspecialchars($infoResultat["col_UPC"]["oldvalue"]) ?>" 
							title="<?php echo htmlspecialchars($infoResultat["col_UPC"]["oldvalue"]) ?>">
							<div class="newvalue"><?php echo $infoResultat["col_UPC"]["newvalue"] ?></div>
							<?php if ( isset($infoResultat["col_UPC"]["info"]) ){ ?>
							<div class="infodata"><?php echo $infoResultat["col_UPC"]["info"] ?></div>
							<?php } ?>
						</td>
						<?php
						if($uneLigneFournisseur){
<<<<<<< HEAD
							formatCaseResultat($infoResultat["col_fournisseur"]);
=======
							formatCaseResultat($infoResultat["col_fournisseur"]);//
>>>>>>> 02661005a422649804a221a452910f5265930706
						}
						if($uneLigneDistributeur){
							formatCaseResultat($infoResultat["col_distributeur"]);
						}
						if($_POST["col_code"]!=""){
							formatCaseResultat($infoResultat["col_code"]);
						}
						if($_POST["col_descFR"]!=""){
							formatCaseResultat($infoResultat["col_descFR"]);
						}
						if($_POST["col_descEN"]!=""){
							formatCaseResultat($infoResultat["col_descEN"]);
						}
						if($_POST["col_cost"]!=""){
							formatCaseResultat($infoResultat["col_cost"],true);
						}
						if($_POST["col_prix"]!=""){
							formatCaseResultat($infoResultat["col_prix"],true);
						}
						if($_POST["col_dep"]!=""){
							formatCaseResultat($infoResultat["col_dep"]);
						}
						if($_POST["col_SousDep"]!=""){
							formatCaseResultat($infoResultat["col_SousDep"]);
						}
						if($_POST["col_boite_nb"]!=""){
							formatCaseResultat($infoResultat["col_boite_nb"]);
						}
						if($_POST["col_UPC_caisse"]!=""){
							formatCaseResultat($infoResultat["col_UPC_caisse"]);
						}
						?>
						</tr>
						<?php
<<<<<<< HEAD
=======
						
>>>>>>> 02661005a422649804a221a452910f5265930706
					}
					/*
					 'change_date_exp' => '2017-01-19',
					 'options_discontOtherDist' => '1',
					 'prix_changeRightNow' => '1',
					 */
<<<<<<< HEAD
				}
				echo $strCumulLigneError;
				//echo '<pre>' , print_r($infoResultat) , '</pre>';
				?>
			</tbody>
		</table>
		
=======
					 //Content pour l'Excel
					 $infoResultatCopie[$iLigne] = $infoResultat;
				}//Fin for
				echo $strCumulLigneError;
				?>
			</tbody>
		</table>
>>>>>>> 02661005a422649804a221a452910f5265930706
		<style>
		.statuserror{
			color:#ff0000;
		}
		.statusnew{
			font-weight:bold;
			color:#316bb7;/*00ff00*/
		}
		.statusupdate{
			color:#316bb7; /*00ff00*/
		}
		.statusunchange{
			color:#316bb7;/*00ff00*/
		}
		.infodata{
			font-size:8px;
			color:#316bb7;/*00ff00*/
		}
		</style>
		<?php
<<<<<<< HEAD
		$reponse["nbItemIgnore"] = $nbItemIgnore;
		$reponse["nbItemAdded"] = $nbItemAdded;
		$reponse["nbItemUpdated"] = $nbItemUpdated;
		$reponse["nbItemError"] = $nbItemError;
		$reponse["status"] = "success";

		
=======
		$reponse["nbItemIgnore"]    = $nbItemIgnore;
		$reponse["nbItemAdded"]     = $nbItemAdded;
		$reponse["nbItemUpdated"]   = $nbItemUpdated;
		$reponse["nbItemError"]     = $nbItemError;
		$reponse["status"]          = "success";
>>>>>>> 02661005a422649804a221a452910f5265930706
	} catch (Exception $e){
		$reponse["status"] = "error";
		$reponse["message"] = $e->getMessage();
	}
<<<<<<< HEAD
	//echo '<pre>' , print_r($reponse["data"] = ob_get_contents()) , '</pre>';
	
	$reponse["data"] = ob_get_contents();//renvoie le contenu du tampon de sortie le plus haut.
	ob_end_clean();//Libere le tampon de sorti(display content to browser)
	echo json_encode($reponse);//Reponse Ajax
	die();
}

if ( $_GET["apercu"] == "1" ){
	$pathFichierTemp = __DIR__ . "/../../temp/tmp_excel_import/".$_POST["fichier"];
	require_once __DIR__ . "/../req/PHPExcel-1.8/PHPExcel.php";
	
=======
   //Demande pour generer l'Excel au client
   if ($_GET["getFile"] == "1") {
        
        ob_clean(); // Clean all content from up
        require_once __DIR__ . "/../req/PHPExcel-1.8/PHPExcel/Writer/Excel2007.php";
        // Cree l'excel
        $workbook = new PHPExcel();   
        // cree la feuille: "0" correspond à la première feuille
        $workbook->setActiveSheetIndex(0);
        // Prend l'onglet courant et lui donne un nom
        $workbook->getActiveSheet()->setTitle("Aperçu ".date("d-m-Y"));

        //  ================== intégrer les  entêtes Excel ==================================
        
        if($_POST["col_UPC"] != "" ){  
            // parm: colonne commence à (0) et ligne à (1) 
            $nbCol=0;
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'UPC');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(20);
            $nbCol++;
        }
        if($uneLigneFournisseur ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'Distributeur');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(25);
            $nbCol++;
        }        
        if($uneLigneDistributeur ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'Fournisseur');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(25);
            $nbCol++;
        } 
        if($_POST["col_code"]   != "" ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'Code fournisseur');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(17);
            $nbCol++;
        }           
         if($_POST["col_descFR"] != "" ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'Description fr');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(60);
            $nbCol++;
        }         
        if($_POST["col_descEN"] != "" ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'Description en');
            $nbCol++;
        }    
         if($_POST["col_cost"]   != "" ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'Ancien Prix coutant');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(17);
            $nbCol++;
        }          
         if($_POST["col_cost"]   != "" ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,' Prix coutant');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(12);
            $nbCol++;
        }         
          if($_POST["col_prix"]   != "" ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,'Ancien Prix vendant');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(17);
            $nbCol++;
        }   
         if($_POST["col_prix"]   != "" ){  
            $workbook->getActiveSheet()->setCellValueByColumnAndRow($nbCol,1,' Prix vendant');
            $workbook->getActiveSheet()->getColumnDimensionByColumn($nbCol,1)->setWidth(12);
            $nbCol++;
        }      

        //  ================== intégrer contenue des lignes ==================================

        for( $iLigne = $_POST["first_line"]; $iLigne <= $nbLigne; $iLigne++ ){
            if($_POST["col_UPC"] != ""  ){    
                //$workbook->getActiveSheet()->setCellValue($_POST["col_UPC"].''.$iLigne, $infoResultatCopie[$iLigne]["col_UPC"]["newvalue"]); 
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col=0,$iLigne,$infoResultatCopie[$iLigne]["col_UPC"]["newvalue"]);
                $col++;
            } 	 
            if($_POST["id_fournisseur"] != ""){  
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$listFournisseurLabel[$_POST["id_fournisseur"]]);
                $col++;
            }   
             if($_POST["id_distributeur"] != ""){   
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$listFournisseurLabel[$_POST["id_distributeur"]]);
                $col++;
            }               
            if($_POST["col_code"] != ""  ){
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$infoResultatCopie[$iLigne]["col_code"]["newvalue"]);
                $col++;
            }       
            if($_POST["col_descFR"] != "" ){    
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$infoResultatCopie[$iLigne]["col_descFR"]["newvalue"]); 
                $col++;
            }     
            if($_POST["col_descEN"] != "" ){    
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$infoResultatCopie[$iLigne]["col_descEN"]["newvalue"]); 
                $col++;
            }                
            if($_POST["col_cost"] != ""  ){    
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$infoResultatCopie[$iLigne]["col_cost"]["oldvalue"]);
                $workbook->getActiveSheet()->getStyleByColumnAndRow ($col,$iLigne)->getNumberFormat()->setFormatCode('#,##0.00');
                $col++;
            } 
            if($_POST["col_cost"] != ""  ){    
               $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$infoResultatCopie[$iLigne]["col_cost"]["newvalue"]);
               $workbook->getActiveSheet()->getStyleByColumnAndRow ($col,$iLigne)->getNumberFormat()->setFormatCode('#,##0.00');
               $col++;
            }    
            if($_POST["col_prix"] != ""  ){    
               $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$infoResultatCopie[$iLigne]["col_prix"]["oldvalue"]);
               $workbook->getActiveSheet()->getStyleByColumnAndRow ($col,$iLigne)->getNumberFormat()->setFormatCode('#,##0.00');
               $col++;
            }   
            if($_POST["col_prix"] != ""  ){ 
                $workbook->getActiveSheet()->setCellValueByColumnAndRow($col,$iLigne,$infoResultatCopie[$iLigne]["col_prix"]["newvalue"]);
                $workbook->getActiveSheet()->getStyleByColumnAndRow ($col,$iLigne)->getNumberFormat()->setFormatCode('#,##0.00');
                $col++; 
            }
        } 	        
        
        //  ========================= SETTING HEADER  =========================================
        
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//for xlsx
		header('Content-Disposition: attachment; filename="'.$_POST["fichier"].'"');
		header('Cache-Control: max-age=0');     
		
		// Crée l'objet qui va écrire sur le fichier
		$writer = new PHPExcel_Writer_Excel2007($workbook);
		
	    // Force le download  au client
        $writer->save('php://output');
        ob_end_flush();
        die();
   }else{
       $reponse["data"] = ob_get_contents();//renvoie le contenu du haut. 
       ob_end_clean();//Fermeture du buffer
       echo json_encode($reponse);//Reponse Ajax
       die();
   }
   
}

//Traite le post Ajax  from onChangeSheetName() pour afficher l'apercu du fichier au client
if ( $_GET["apercu"] == "1"){
    
    //Va cherche le fichier
	$pathFichierTemp = __DIR__ . "/../../temp/tmp_excel_import/".$_POST["fichier"];
	
	//_________________________ Gestion chache _____________________________
	require_once __DIR__ . "/../req/PHPExcel-1.8/PHPExcel.php";
>>>>>>> 02661005a422649804a221a452910f5265930706
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
	$cacheSettings = array( 'memoryCacheSize' => '32MB');
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
	
<<<<<<< HEAD
=======
	//_________________________ Instace objet Excel en mode lecture _____________________________
>>>>>>> 02661005a422649804a221a452910f5265930706
	if ( substr($pathFichierTemp,-4) == ".xls" ){
		$objReader = new PHPExcel_Reader_Excel5();
	} elseif ( substr($pathFichierTemp,-5) == ".xlsx" ){
		$objReader = new PHPExcel_Reader_Excel2007();
	}
	$objReader->setReadDataOnly(true);
<<<<<<< HEAD

	$objPHPExcel = $objReader->load($pathFichierTemp);
	
=======
	//charge le fichier
	$objPHPExcel = $objReader->load($pathFichierTemp);   
	//va cherche la feuille 
>>>>>>> 02661005a422649804a221a452910f5265930706
	$maSheet = $objPHPExcel->getSheetByName($_POST["sheetName"]);
	
	$nbCol = columnIndexFromString( $maSheet->getHighestColumn() );
	$nbLigne = $maSheet->getHighestRow();
	
	echo "ok:";
	?>
	<div style="width:100%;overflow:scroll;max-height:300px;">
		<table class="tableExcel">
			<tr class="excelNomCol">
				<td></td>
				<?php
				for ($c=0;$c < $nbCol  and $c<=26 ;$c++){
					?><td class="excel_case" data-excel-c="<?php echo stringFromColumnIndex($c) ?>" ><?php echo stringFromColumnIndex($c) ?></td><?php
				}
				?>
			</tr>
			<?php
			for ($l=1;$l<$nbLigne and $l<=20;$l++){
				?>
				<tr>
					<td class="excelNomCol" data-excel-l="<?php echo $l ?>"><?php echo $l; ?></td>
				<?php
				for ($c=0;$c < $nbCol;$c++){
					?>
					<td class="excel_case" data-excel-c="<?php echo stringFromColumnIndex($c) ?>" data-excel-l="<?php echo $l ?>" ><?php echo $maSheet->getCellByColumnAndRow($c,$l)->getCalculatedValue() ?></td>
					<?php
				}
				?>
				</tr>
				<?php
			}
			?>
		</table>
	</div>
	<?php
<<<<<<< HEAD

}
	
=======
}
>>>>>>> 02661005a422649804a221a452910f5265930706
?>