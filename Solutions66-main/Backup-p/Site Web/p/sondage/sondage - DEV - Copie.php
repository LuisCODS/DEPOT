<?php 
//Lien pour tester le sondage: https://dev.animoetc.com/sondage?token=D8denXYigXE4EPm0Y8bbg4zR263K3Gi7
require_once(__DIR__.'/../req/init.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$arrayDB_Q1aQ3          = []; //Stocke la réponse  Q1 à Q3
$arrayDB_Q4             = []; //Stocke la réponse Q4
$arrayDB_Q5             = []; //Stocke la réponse Q5
$questionsByVersion     = []; //Stock sondage questions
$tabErreur              = []; //Get erros from all inputs
$debug                  = 2;
$arrayEmailReponse      = []; //Stocke la réponse pour l'email
$isSuccess              = false; //tout est bien passé avec les validations

// VALIDATION (token): Si le clien a clické sur le lien de sondage envoyé par email
if( isset($_GET["token"]) && !empty($_GET["token"]) && preg_match('#^[A-Za-z0-9]+$#', $_GET["token"]) ) {
	$dateNow = date('Y-m-d H:i:s'); 
	// ===================== GET ID VERSION SONDAGE =======================
	if ( INDEV ){
		//Chercher la version de sondage en dev (id_sondage_version: 2)
		$enonceVersion = "SELECT * 
						  FROM sondage_version
						  WHERE brand = 'animo' AND date_insert <= ? 
						  ORDER BY date_insert  DESC
						  LIMIT 1 ";   
		$resultVersion = query($enonceVersion, [$dateNow], $mysqli);
	 } else {
		//Chercher la version en production (id_sondage_version: 1)
		$enonceVersion = "SELECT * 
						  FROM sondage_version
						  WHERE brand = 'animo' AND date_live <= ? 
						  ORDER BY date_live  DESC
						  LIMIT 1 ";   
		$resultVersion = query($enonceVersion, [$dateNow], $mysqli);
	}      
	// =========================== GET SONDAGE QUESTIONS ============================
	if($resultVersion->num_rows === 1) {
		$rowVersion = $resultVersion->fetch_assoc();  
		// Va chercher les questions reliées à la version du sondage
		$enonceQuestion = "SELECT * 
						   FROM sondage_question
						   WHERE id_sondage_version = ?  ";
		$resultQuestion = query($enonceQuestion, [$rowVersion['id_sondage_version']], $mysqli);
		while( $uneLigneQuestion = $resultQuestion->fetch_assoc() )  {   
			$questionsByVersion[] =  $uneLigneQuestion;
		}
	} 
	$enonce = sprintf("SELECT
    	                    CLIENT_SONDAGE.TOKEN AS TOKEN, CLIENT_SONDAGE.TOKEN_USED AS TOKEN_USED,CLIENT_SONDAGE.ID_SONDAGE AS ID_SONDAGE,CLIENT_SONDAGE.ID_CLIENT AS ID_CLIENT ,
    	                    CLIENT.EMAIL AS C_EMAIL, CLIENT.NOM AS C_NOM, CLIENT.PRENOM AS C_PRENOM, CLIENT.TEL_MAISON AS C_TEL_MAISON, CLIENT.CELL AS C_CELL, 
    	                    utilisateur.nom AS EMPLOYE_NOM, utilisateur.prenom AS EMPLOYE_PRENOM,
    	                    ANIMAL.A_NOM AS ANIMAL_NOM,
    	                    TOILETTAGE.SERVICE AS TOIL_SERVICE, TOILETTAGE.DATE AS TOIL_DATE,
    	                    MAGASIN.M_EMAIL AS M_EMAIL, MAGASIN.M_NOM AS M_NOM    
						FROM CLIENT_SONDAGE
    						JOIN CLIENT USING(ID_CLIENT)
    						JOIN TOILETTAGE USING(ID_TOIL)
    						JOIN utilisateur_magasin ON(TOILETTAGE.ID_STAFF = utilisateur_magasin.id_utilisateur AND TOILETTAGE.ID_MAG = utilisateur_magasin.id_magasin)
    						JOIN utilisateur USING(id_utilisateur)
    				    	JOIN ANIMAL USING(ID_ANIMAL)
    						JOIN RACE USING(ID_RACE)
    						JOIN MAGASIN USING(ID_MAGASIN)
						WHERE TOKEN_USED IS NULL AND TOKEN = '%s'
						LIMIT 1", $_GET["token"]);
	$result = $mysqli->query($enonce) or die("Erreur SQL: ".$mysqli->error);
	
	if($result->num_rows === 1) {
	    
		$mysqli->autocommit(false);
		$resultRow  = $result->fetch_assoc();
		
		//Si le client a envoyé le form
		if( isset($_POST["form_Sondage"]) ) 
		{ 
			//va cherche le fichier et changer son contenu pour l'envois d'email
		    $email_message = file_get_contents(__DIR__ . "/../../dev/inc/template/sondage_result.html");
			//Pour chanque question...
			foreach ($questionsByVersion as $rowQuestion)
			{  
				// Récupère et clean les inputs
				$commentaire      = trim($_POST["commentaireEmoji_".$rowQuestion["id_sondage_question"]]);
				$emoji_inputValue = trim($_POST["choixEmoji_".$rowQuestion["id_sondage_question"]]);
				
				// =================== VALIDATION EMOJI & COMMENTAIRE  ===================  
				if( $rowQuestion["input_type"] == "radioemoji"){
					//Si un emoji a été séléctionné 
					if( !empty($emoji_inputValue) ) {
						$matchedValue = false;
						foreach (json_decode($rowQuestion["input_choices"]) as $emojiRow){ 
							//Check s'il y a fait un choix parmis les valeurs possibles
							if($emoji_inputValue == $emojiRow->value ){
								$matchedValue = true;
								// Check si l'emoji choisit oblige un commenatire et que ce dernier est vide
								if( $emojiRow->require_comment && empty($commentaire) ){
									$tabErreur["commentaireEmoji_".$rowQuestion["id_sondage_question"]]  = L('champsObligatoire');
								}else{
									//Stokage de données pour la BD
									$arrayDB_Q1aQ3[$rowQuestion["id_sondage_question"]]["ID_SONDAGE"]          = $resultRow["ID_SONDAGE"];
									$arrayDB_Q1aQ3[$rowQuestion["id_sondage_question"]]["id_sondage_question"] = $rowQuestion["id_sondage_question"];
									$arrayDB_Q1aQ3[$rowQuestion["id_sondage_question"]]["reponse"]             = $emoji_inputValue; 
									$arrayDB_Q1aQ3[$rowQuestion["id_sondage_question"]]["commentaire"]         = htmlentities($commentaire); 
									//Stokage de données pour l'envoi d'email
									$arrayEmailReponse["Q1aQ3"][$rowQuestion["id_sondage_question"]] = [
									                                                            "question_fr"=>$rowQuestion["question_fr"],
									                                                            "id_sondage_question"=>$rowQuestion["id_sondage_question"],
									                                                            "emoji"=>$emojiRow->label_fr,
									                                                            "reponse"=>htmlentities($commentaire)
							                                                                  ]; 
								} 
							}
						}
						// Check si l'envoie de la valeur emoji choisi ne match pas avec les valeurs possibles
						if (!$matchedValue) {
							//Show error
							msg_output('Ups, mauvais donnée envoyé comme paramètre!','warning');
							$tabErreur["choixEmoji_".$rowQuestion["id_sondage_question"]] = L('msnErreurEmojiChoix');
							break;  
						} 
					}else{
						$tabErreur["choixEmoji_".$rowQuestion["id_sondage_question"]] = L('msnErreurEmojiChoix');
					}
				}   
				
				// ======================= VALIDATION COMMENTAIRE SANS EMOJI- QUESTION 4  =========================== 
			    if( $rowQuestion["input_type"] == "textarea"){
					 $commentaireSansEmoji = trim($_POST["commentaireSansEmoji_".$rowQuestion["id_sondage_question"]]);
					if( !empty($commentaireSansEmoji) ){
					    //Stokage de données pour la BD
						$arrayDB_Q4["ID_SONDAGE"]          = $resultRow["ID_SONDAGE"];
						$arrayDB_Q4["id_sondage_question"] = $rowQuestion["id_sondage_question"];
						$arrayDB_Q4["reponse"]             = htmlentities($commentaireSansEmoji); 
						$arrayDB_Q4["commentaire"]         = NULL; 
						//Stokage de données pour l'envoi d'email
						$arrayEmailReponse["Q4"] = [
                                                    "question_fr"=>$rowQuestion["question_fr"],
                                                    "id_sondage_question"=>$rowQuestion["id_sondage_question"],
                                                    "reponse"=>htmlentities($commentaireSansEmoji)
                                                   ]; 
					}else{                 
						$tabErreur["commentaireSansEmoji_".$rowQuestion["id_sondage_question"]]  = L('champsObligatoire');
					}                  
			   }
			   
				// =================== VALIDATION SELECT & INPUT TEXT  ===================
				if( $rowQuestion["input_type"] == "select"){
					$selectInputValue = $_POST["select_".$rowQuestion["id_sondage_question"]]; 
					//Saisie les valeurs possibles des options select  
					$nbChoixOfSelection = count(json_decode($rowQuestion["input_choices"]));//5  
					//S'il y a fait une sélection et parmis les choix possibles
					if(!empty($selectInputValue) && preg_match('#^[1-'.$nbChoixOfSelection.']$#', $selectInputValue) ){
						// si l'option "autres" n'a pas été choisie
						if( $selectInputValue != "5" ){
						    //Stokage de données pour la BD
							$arrayDB_Q5["ID_SONDAGE"]          = $resultRow["ID_SONDAGE"];
							$arrayDB_Q5["id_sondage_question"] = $rowQuestion["id_sondage_question"];
							//$arrayDB_Q5["reponse"]             = $selectInputValue;
							$arrayDB_Q5["commentaire"]         = NULL; 
	    					//Stokage de données pour l'envoi d'email
        					foreach (json_decode($rowQuestion["input_choices"]) as $selectValue){ 
        					    if ($selectValue->value == $selectInputValue) {
        					        $arrayDB_Q5["reponse"] = $selectValue->label_fr;
        					        break;
        					    }
    					    }
    						$arrayEmailReponse["Q5"] = [
                                                        "question_fr"=>$rowQuestion["question_fr"],
                                                        "id_sondage_question"=>$rowQuestion["id_sondage_question"],
                                                        "reponse"=>$arrayDB_Q5["reponse"]
                                                       ]; 
						}else{  
							$autreReseau = trim($_POST["autreReseau_".$rowQuestion["id_sondage_question"]]);
							// check s'il a choisi l'option 5 sans préciser la source
							if(empty($autreReseau) && $selectInputValue == "5" ){
								$tabErreur["autreReseau_".$rowQuestion["id_sondage_question"]] =  L('champsObligatoire'); 
							}else{
							    //Stokage de données pour la BD
								$arrayDB_Q5["ID_SONDAGE"]          = $resultRow["ID_SONDAGE"];
								$arrayDB_Q5["id_sondage_question"] = $rowQuestion["id_sondage_question"];
								$arrayDB_Q5["reponse"]             = $selectInputValue;
								$arrayDB_Q5["commentaire"]         = htmlentities($autreReseau);
                                //Stokage de données pour l'envoi d'email
        						$arrayEmailReponse["Q5"] = [
                                                            "question_fr"=>$rowQuestion["question_fr"],
                                                            "id_sondage_question"=>$rowQuestion["id_sondage_question"],
                                                            "reponse"=>$arrayDB_Q5["commentaire"]
                                                           ]; 
							}
						} 
					}else{  
						$tabErreur["select_".$rowQuestion["id_sondage_question"]] = L('champsObligatoire');
					}  
				}
			} //fin foreach
			
			if( count($tabErreur) == 0){
			    try {
    			    // ====================================== QUESTION DE 1 À 3 ===============================================================
    				//BD
    				foreach ($arrayDB_Q1aQ3 as $unArrayQuestion){ 
    					faireInsert($unArrayQuestion, "sondage_reponse", $mysqli, $debug);
    				}
                    //Email : Réponses au sondage
                    foreach ($arrayEmailReponse["Q1aQ3"] as $emoji){ 
                        //Nourrie le fichier pour l'email
                        $email_message = str_replace("%%QUESTION_".$emoji["id_sondage_question"]."%%",  $emoji["question_fr"], $email_message);
                        $email_message = str_replace("%%EMOJI_".$emoji["id_sondage_question"]."%%",     $emoji["emoji"], $email_message);
                        $email_message = str_replace("%%REPONSE_".$emoji["id_sondage_question"]."%%",   $emoji["reponse"], $email_message);
                    }
    				// ======================================= QUESTION 4 =======================================================================
    				//BD
    				faireInsert($arrayDB_Q4, "sondage_reponse", $mysqli, $debug);
                    //Email : Réponses au sondage
                    $email_message = str_replace("%%QUESTION_".$arrayEmailReponse["Q4"]["id_sondage_question"]."%%", $arrayEmailReponse["Q4"]["question_fr"], $email_message);
                    $email_message = str_replace("%%REPONSE_".$arrayEmailReponse["Q4"]["id_sondage_question"]."%%",  $arrayEmailReponse["Q4"]["reponse"], $email_message);
                    
                    // ======================================= QUESTION 5 =======================================================================
    				//BD
    				faireInsert($arrayDB_Q5, "sondage_reponse", $mysqli, $debug);
                    //Email : Réponses au sondage
                    $email_message = str_replace("%%QUESTION_".$arrayEmailReponse["Q5"]["id_sondage_question"]."%%", $arrayEmailReponse["Q5"]["question_fr"], $email_message);
                    $email_message = str_replace("%%REPONSE_".$arrayEmailReponse["Q5"]["id_sondage_question"]."%%",  $arrayEmailReponse["Q5"]["reponse"], $email_message);
                    
                     // ========================== update la Table: CLIENT_SONDAGE pour défaussez le jeton================================================
                     
    				$arrayDB_CLIENT_SONDAGE["ID_SONDAGE"] = $resultRow["ID_SONDAGE"];
    				$arrayDB_CLIENT_SONDAGE["DATE_COMPLETED"] = date("Y-m-d H:i:s");
    				$arrayDB_CLIENT_SONDAGE["TOKEN_USED"] = "1";
    				faireUpdate($arrayDB_CLIENT_SONDAGE, "CLIENT_SONDAGE", "ID_SONDAGE", $mysqli, $debug);
    				
    				// ============================ EMAIL CONTENT ========================================================================================
    				
    				// Email : Informations sur le client 
    				$email_message = str_replace("%%CLIENT%%", $resultRow["C_PRENOM"]. " " .$resultRow["C_NOM"],$email_message);
    				$email_message = str_replace("%%EMAIL%%",  $resultRow["C_EMAIL"], $email_message);
    				$email_message = str_replace("%%TEL_1%%",  $resultRow["C_TEL_MAISON"], $email_message);
    				$email_message = str_replace("%%TEL_2%%",  $resultRow["C_CELL"], $email_message);
    				// Email : Informations du le service 
    				$email_message = str_replace("%%A_NOM%%", $resultRow["ANIMAL_NOM"],$email_message);
    				$services = array(
                						"BRS"=>L("Brossage"),
                				        "ECSS"=>L("eclcoussins"),
                				        "ORL"=>L("nettoyagedoreille"),
                				        "RG"=>L("recgriffes"),
                				        "TM"=>L("tmoufette"),
                				        "TAP"=>L("tantipuces"),
                				        "TRM"=>L("trimme"),
                				        "VGA"=>L("glanal"),
                				        "GRIFFE" =>L("cat_10"),
                				        "LS"=>L("laversecher"),
                				        "TONTE"=>L("tonte"),
                				        "COUPE"=>L("rv_type_toil3"),
                				        "autre"=>L("diversservice"),
                				        "DIVERS" => L("diversservice")
            				        );
    				foreach($services as $service => $value){
    					if($service == $resultRow["TOIL_SERVICE"]){
    						$email_message = str_replace("%%SERVICE%%", $value, $email_message);
    					}
    				}					
    				$email_message = str_replace("%%DATE_T%%", formatDateUTF8($resultRow['TOIL_DATE']),$email_message);
    				$email_message = str_replace("%%TOILETTEUR%%",  $resultRow["EMPLOYE_PRENOM"]. " " .$resultRow["EMPLOYE_NOM"], $email_message);
    				// Email : Détails techniques 
    				$email_message = str_replace("%%IP%%", $_SERVER["REMOTE_ADDR"],     $email_message);
    				$email_message = str_replace("%%UA%%", $_SERVER["HTTP_USER_AGENT"], $email_message);
    				$email_message = str_replace("%%DATE_S%%", date("Y-m-d H:i:s"),     $email_message);
    				
    				//Send email
					try{
    				    $mailin = new AnimoMailin($resultRow['ID_CLIENT']);
    				    $data = array(
                				        "to" => array($resultRow["M_EMAIL"]=>("Animo etc ".$resultRow["M_NOM"])),
                				        "replyto"=> array($resultRow["C_EMAIL"],($resultRow['C_PRENOM'].' '.$resultRow['C_NOM'])),
                				        "subject" => ("Nouvelle évaluation client"),
                				        "html" => $email_message,
                				        'bcc'=> array('isabelle@animoetc.com'=>("Animo etc")),
                				    );
    				    $result = $mailin->send_email($data);
    					$mysqli->autocommit(true);
    					//die("success"); //isabelle@animoetc.com
    					//Rendu ici, tout est beau : on afficher le message success au client
    					$isSuccess = true;
    				}catch(phpmailerException $e){
    					$mysqli->rollback();
    					die("emailerror");
    				}
    			}catch(Exception $e){
    				$mysqli->rollback();
    				die($e->getMessage());
    			}
			}
            //vex($email_message);die();
		}
	}else{
		die("Jeton d'identification invalide!");
	}
//fin validation token    
}else{ 
	die("Jeton d'identification invalide!"); 
}    
//echo '<pre>' , print_r($POST) , '</pre>';
?>
<div class="container blog-container article-container style-1">
	<?php /*================= Msn success  =================*/?>
	<?php if ( $isSuccess ){?>  
    	<div class='row'>
    	    <?php msg_output($L['sondageMSNsuccess'],"success"); ?>
        	<script>
    			setTimeout(function(){window.location = "/"},7000);
    		</script>
    	</div>
	<?php
	} else {
	?>
		<?php /*================= DESCRIPTION jumbotron  =================*/?>
		<div class="information-blocks mt-5" >
			<div class="row">
				<div class="col-md-12 information-entry">
					<div class="article-container style-1">
						<div class="jumbotron">
							<h2><?php echo $L['sondage_titre'];?></h2>
							<p style="font-size:20px; font-weight:300; margin:40px 0;"><?php echo $L['sondage_desc'];?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php /* ____________________________  FORM ____________________________ */ ?>
		<div class="row  mb-5" style="border-style: groove; border-color:#ffffff ;">
			<div class="col-md-12 ">
				<form method="POST" class="pt-5" action="">
					<input type="hidden" name="token" value="<?= attrEncode($_POST["token"])?>">
					<input type="hidden" name="form_Sondage" value="envoye">
					<!-- ============ LOOP ALL INPUTS ==========-->
					<?php foreach ($questionsByVersion as $rowQuestion){
						// ============  RADIO & TEXTAREA ==========
						if($rowQuestion["input_type"] == "radioemoji"){ ?>
							<div class="row mb-5">
								<!--QUESTION -->
								<div class="col-md-12">
									<label class="control-label mt-2"><?=  ( $_SESSION["lang"] == "fr" ) ?  $rowQuestion["question_fr"] :  $rowQuestion["question_en"];   ?></label>
								</div>
								<!--  ========================================================================== RADIOS ================================================================================= -->
								<div class="col-md-4 mt-2 ">
									<p><?php echo $L['emojiQuestion']; ?></p>
									<?php foreach (json_decode($rowQuestion["input_choices"]) as $emojiRow){ ?>
										<div class="form-check-inline">
											<label class="form-check-label emoji_face" >
												<input class="form-check-input chache_radio" 
													   type="radio"
													   name="choixEmoji_<?php echo $rowQuestion["id_sondage_question"] ?>"
													   value="<?php echo $emojiRow->value ?>" <?= $_POST["choixEmoji_".$rowQuestion["id_sondage_question"]] ==  $emojiRow->value ? 'checked="checked"':""; ?> >
												<span style="font-size:40px;" class="emoji_NoirBlanc mb-3" ><?php echo $emojiRow->label_fr ?></span>
											</label>
										</div>
									<?php } ?>
									<br>
									<span class="errorSondage"><?php if ( isset($tabErreur["choixEmoji_".$rowQuestion["id_sondage_question"]]) ) echo $tabErreur["choixEmoji_".$rowQuestion["id_sondage_question"]]; ?></span>
								</div>
								<!--  ========================================================================== TEXTEAREA ================================================================================= -->
								<div class="col-md-8">
									<textarea  class="form-control" 
											   rows="3"
											   name="commentaireEmoji_<?= $rowQuestion["id_sondage_question"]?>"
											   placeholder="<?php echo $L['Commentaires'];?>"><?= htmlspecialchars($_POST["commentaireEmoji_".$rowQuestion["id_sondage_question"]] ) ?></textarea>
									<span class="errorSondage"><?php if (isset($tabErreur["commentaireEmoji_".$rowQuestion["id_sondage_question"]] ) ) echo $tabErreur["commentaireEmoji_".$rowQuestion["id_sondage_question"]]; ?></span>
								</div>
							</div>
						<?php } ?>
						<!-- =================================================================================   SELECT =================================================================================  -->
						<?php if($rowQuestion["input_type"] == "select"){  ?>
							<div class="row mb-5">
								<div class="col-md-4">
								</div>
								<div class="col-md-8 mt-2">
									<label for="select_<?= $rowQuestion["id_sondage_question"]?>" class="control-label"><?php echo $L['commententenduparler'];?> ?</label>
									<select name="select_<?= $rowQuestion["id_sondage_question"]?>"  class="form-control" id="selectMedia" onchange="addInput()">
										<option selected="selected">Fait une choix</option>
										<?php foreach (json_decode($rowQuestion["input_choices"]) as $selectOption){  ?>
											<option value="<?= $selectOption->value ?>"  <?= $_POST["select_".$rowQuestion["id_sondage_question"]] == $selectOption->value ? 'selected="selected"' : '' ;?>  ><?= ( $_SESSION["lang"] == "fr" ) ? $selectOption->label_fr : $selectOption->label_en ?></option>
										<?php } ?>
									</select>
									<span class="errorSondage"><?php if (isset($tabErreur["select_".$rowQuestion["id_sondage_question"]])) echo $tabErreur["select_".$rowQuestion["id_sondage_question"]]; ?></span>
								</div>
								<div class="col-md-4">
								</div>
								<!-- ============================================================================ INPUT TEXTT =================================================================================  -->
								<div class="col-md-8" style="visibility:hidden" id="inputToShow">
									<input type="text" 
											name="autreReseau_<?= $rowQuestion["id_sondage_question"] ?>" 
											class="form-control" 
											placeholder="<?php echo $L['precisezLeReseau'];?>"  
											value="<?= htmlspecialchars($_POST["autreReseau_".$rowQuestion["id_sondage_question"]]); ?>">
									<span class="errorSondage"><?php if (isset($tabErreur["autreReseau_".$rowQuestion["id_sondage_question"]])) echo $tabErreur["autreReseau_".$rowQuestion["id_sondage_question"]]; ?></span>
								</div>
							</div>
						<?php } ?>
						<!--=================================================================================   TEXTAREA ================================================================================= = -->
						<?php if($rowQuestion["input_type"] == "textarea"){ ?>
							<div class="row mb-4">
								<div class="col-md-4"></div>
								<div class="col-md-8">
									<label class="control-label mt-2"><?=  ( $_SESSION["lang"] == "fr" ) ?  $rowQuestion["question_fr"] :  $rowQuestion["question_en"];   ?></label>
									<textarea rows="3" 
											  class="form-control" 
											  name="commentaireSansEmoji_<?= $rowQuestion["id_sondage_question"]?>"
											  placeholder="<?php echo $L['Commentaires'];?>"><?= htmlspecialchars($_POST["commentaireSansEmoji_".$rowQuestion["id_sondage_question"]]); ?></textarea>
									<span class="errorSondage"><?php if ( isset($tabErreur["commentaireSansEmoji_".$rowQuestion["id_sondage_question"]]) ) echo $tabErreur["commentaireSansEmoji_".$rowQuestion["id_sondage_question"]]; ?></span>
								</div>
							</div>
						<?php } ?>
					<?php } ?>
					<!--BUTTON-->
					<div class="form-group col-md-12  mb-5">
						<button class="btn btn btn-danger  btn-lg btn-block mt-3" type="submit" onclick=""><?php echo $L['envoyer_message'];?></button>
					</div>
				</form>
			</div>
		</div>
		
		<script>
		//Ajoute un champ input supplémentaire à la selection de l'option "Autres" dans select
		function addInput()
		{
			var select = document.getElementById('selectMedia');
			var selectedValue = select.options[select.selectedIndex].value;
			
			if(selectedValue == 5){
				document.getElementById('inputToShow').style.visibility = 'visible';
			}else{
			   document.getElementById('inputToShow').style.visibility = 'hidden';
			}
		}
		
		addInput();
		</script>
		<?php 
	}
?>
</div>

