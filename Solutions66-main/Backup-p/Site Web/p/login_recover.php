<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(filter_var($_POST["EMAIL"],FILTER_VALIDATE_EMAIL) && checkReCaptcha()){
	$enonce = sprintf("SELECT * FROM CLIENT 
				JOIN CLIENT_MAGASIN USING(ID_CLIENT)
				JOIN MAGASIN USING(ID_MAGASIN)
				WHERE EMAIL = '%s' AND EMAIL NOT LIKE '%%animoetc.com'
				GROUP BY CLIENT.ID_CLIENT
				ORDER BY CL_MAG_ID DESC LIMIT 1",$mysqli->real_escape_string($_POST["EMAIL"]));
	$resultEmail = $mysqli->query($enonce) or die($mysqli->error);
	if($resultEmail->num_rows === 1){
		$rowClient = $resultEmail->fetch_assoc();
		
		$query = sprintf("SELECT CLIENT.*
							FROM CLIENT
						   WHERE EMAIL NOT LIKE '%%animoetc.com' AND EMAIL = ? LIMIT 1");
		$resultclient = query($query,[$rowClient["EMAIL"],],$mysqli) or die("SQL" . __LINE__);
		$rowclient = $resultclient->fetch_assoc();
		$langue = ($rowclient["LANGUE"] == "en") ? "en" : "fr";
		
		if (preg_match('#^[0-9]+$#', $rowClient["ID_MAGASIN"])) {
		    //Check si magasin choisi
            $query = sprintf("SELECT *
							FROM MAGASIN
						   WHERE ID_MAGASIN = %s", $rowClient["ID_MAGASIN"]);
            $resultmag = $mysqli->query($query) or die("SQL" . __LINE__);
            $uneLigneMag = $resultmag->fetch_assoc();
		}
		try{
		    $mysqli->autocommit(false);
    	    //Créer le token
    	    $mysqli->query("lock tables CLIENT_TOKEN_LOGIN write;");
    	    do {
    	        $token = generateRandomString(64);
    	        $result = query("select * from CLIENT_TOKEN_LOGIN where `TOKEN` = ?",[$token,],$mysqli);
    	    } while( $result->num_rows > 0 );
    	    
    	    $arrayDB = [];
    	    $arrayDB["ID_CLIENT"] = $rowclient["ID_CLIENT"];
    	    $arrayDB["token"] = $token;
    	    $arrayDB["DATE_INSERT"] = $dreteLa;
    	    $arrayDB["DATE_EXPIRE"] = date('Y-m-d H:i:s',strtotime("+48 hours",strtotime($dreteLa)));
    	    $arrayDB["EMAIL"] = $rowclient["EMAIL"];
    	    
    	    faireInsert($arrayDB,"CLIENT_TOKEN_LOGIN",$mysqli,$DEBUG_DB);
    	    
    	    $mysqli->commit();
    	    $mysqli->query("UNLOCK TABLES");
    	    
    	    //Envoie courriel
    	    $arrayBuilder = array();
    	    $arrayBuilder["M_NOM"] = $uneLigneMag["M_NOM"];
    	    $arrayBuilder["M_TEL"] = $uneLigneMag["M_TEL"];
    	    $arrayBuilder["M_FAX"] = $uneLigneMag["M_FAX"];
    	    $arrayBuilder["M_ADRESSE"] = $uneLigneMag["M_ADRESSE"];
    	    $arrayBuilder["M_VILLE"] = $uneLigneMag["M_VILLE"];
    	    $arrayBuilder["M_PROVINCE"] = $uneLigneMag["M_PROVINCE"];
    	    $arrayBuilder["M_CP"] = $uneLigneMag["M_CP"];
    	    $arrayBuilder["M_LATT"] = $uneLigneMag["M_LATT"];
    	    $arrayBuilder["M_LON"] = $uneLigneMag["M_LON"];
    	    $arrayBuilder["TOKEN"] = $token;
    	    
    	    $builder = new MailBuilder(); // voir functions.php
    	    $email_message = $builder->build("sendEmailRecup_" . $langue . ".html", $arrayBuilder,__DIR__ . "/../../dashboard/inc/template_mail_builder");
    	    
    	    $mailin = new AnimoMailin($rowclient['ID_CLIENT']);
    	    
    	    $data = array(
    	        "to" => array($rowclient["EMAIL"]=>($rowclient["PRENOM"] . " " . $rowclient["NOM"])),
    	        "replyto"=> array($uneLigneMag['M_EMAIL'],"Animo etc ".$uneLigneMag['M_NOM']),
    	        "subject" => ($langue == "en" ? "Recovery account Animo etc" : "Récupération de compte Animo etc"),
    	        "html" => $email_message,
    	    );
    	    $result = $mailin->send_email($data);
    		?>
    		<div class="well-04">
    			<div class="container">
    				<div class="row">
    					<div class="col-md-3">
    						
    					</div>
    					<div class="col-md-6 information-entry">
    						<?php 
    						msg_output($L["motdepassereset"]);
    						?>
    					</div>
    					<div class="col-md-3">
    					</div>
    				</div>
    			</div>
    		</div>
    		<?php
    		return;
		} catch(Exception $e) {
		    $mysqli->query("UNLOCK TABLES");
		    $mysqli->rollback();
		    $error = L('erreur inconnue');
		}
	}else{
		$error = $L["courrielaucunclient"];
	}
}
?>
<div class="well-04">
	<div class="container">
		<div class="row">
			<div class="col-md-3">
				
			</div>
			<div class="col-md-6 information-entry">
				<?php 
				if($error){
					msg_output($error,"danger");
				}
				?>
				<h3 class="block-title size-1"><?php echo $L['recuperationmdp'];?></h3>
				<p class="article-container style-1">
					<?php echo $L['recuperationmdpemail'];?>.
				</p>
				<form method="post" action="/login/oublie">
					<div class="form-group">
						<input type="email" name="EMAIL" autocomplete="off" class="form-control" placeholder="<?php echo $L["votrecourrielici"]; ?>"/>
					</div>
					<div class="form-group">
						<script src='https://www.google.com/recaptcha/api.js?hl=<?= $_SESSION["lang"]?>'></script>
						<div class="g-recaptcha" data-sitekey="<?= CAPTCHA_PUBLIC_KEY?>"></div>
					</div>
					<div class="form-group text-right">
						<button type="submit" class="btn btn-danger"><?php echo $L['envoyer_message'];?></button>
					</div>
				</form>
			</div>
			<div class="col-md-3">
			</div>
		</div>
	</div>
</div>