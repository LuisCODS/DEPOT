<?php 
//Valider token
$uneLigneToken = null;
if ( preg_match('#^[0-9a-zA-Z]{64}$#',$_GET["token"]) ){
	$resultToken = query("select * from CLIENT_TOKEN_LOGIN 
						   where TOKEN = ?
							 and DELETED = 0 and DATE_USED is null and DATE_EXPIRE > ? ",[$_GET["token"],date("Y-m-d H:i:s")],$mysqli);
	$uneLigneToken = $resultToken->fetch_assoc();
}

$uneLigneClient= null;
if ( $uneLigneToken ){
	$resultClient = query("select * from CLIENT where ID_CLIENT = ?",[$uneLigneToken["ID_CLIENT"],],$mysqli);
	$uneLigneClient = $resultClient->fetch_assoc();
}

$DEBUG_DB = 0;

if ( $uneLigneToken or $uneLigneClient ){
	$ERROR_MESSAGE = "";
	if ( $_POST["action"] == "changePassword" ){
		if ( forcePassword($_POST["PASSWORD"]) < 25 ){
			$ERROR_MESSAGE = L("Votre mot de passe n'est pas suffisement fort. Essayer d'ajouter des minuscules, majuscules, chiffres et caractères spéciaux.");
		} else {
			try{
				$mysqli->autocommit(false);
				
				$arrayDB = [];
				$arrayDB["ID_CLIENT"] = $uneLigneClient["ID_CLIENT"];
				$arrayDB["PASSWORD"] = hash("sha512",$_POST["PASSWORD"]);
				faireUpdate($arrayDB,"CLIENT","ID_CLIENT",$mysqli,$DEBUG_DB);
				
				$arrayDB = [];
				$arrayDB["ID_CLIENT_TOKEN_LOGIN"] = $uneLigneToken["ID_CLIENT_TOKEN_LOGIN"];
				$arrayDB["DATE_USED"] = date("Y-m-d H:i:s");
				faireUpdate($arrayDB,"CLIENT_TOKEN_LOGIN","ID_CLIENT_TOKEN_LOGIN",$mysqli,$DEBUG_DB);
				
				$mysqli->commit();
			} catch( Exception $e ){
				$mysqli->rollback();
			}
			
			$mysqli->autocommit(true);
			if ($uneLigneClient["EMAIL"] != "" and $_POST["PASSWORD"] != ""){
			    
			    $formLogin = query("select * from CLIENT where EMAIL = ? and EMAIL NOT LIKE '%animoetc.com' and PASSWORD = ? LIMIT 1",[$uneLigneClient["EMAIL"] ,hash('sha512', $_POST['PASSWORD'])],$mysqli);
			    //Form Login OK
			    if($formLogin->num_rows === 1){
			        $rowformLogin = $formLogin->fetch_assoc();
			        unset($rowformLogin['PASSWORD']);
			        $_SESSION['CLIENT'] = $rowformLogin;
			        if(isset($_REQUEST["from"]) && preg_match('#[a-zA-Z0-9_-]+#',$_REQUEST["from"])){
			            switch($_REQUEST["fromtype"]){
			                case "url":
			                    if($_REQUEST["from"] == "subscr_mag"){
			                        $arrayDB = array();
			                        $arrayDB["ID_CLIENT"] = $_SESSION["CLIENT"]['ID_CLIENT'];
			                        $arrayDB["MAGAZINE"] = "1";
			                        faireUpdate($arrayDB,"CLIENT","ID_CLIENT",$mysqli);
			                        redirect("/?subscr_mag=1");
			                    }else{
			                        redirect(rawurldecode($_REQUEST["from"]));
			                    }
			                    break;
			                default:
			                    redirect(sprintf("/%s", $_REQUEST["from"]));
			            }
			        }else{
			            redirect("/");
			        }
			    } else {
			        redirect('/login', $DEBUG_DB );
			    }
			} else {
				redirect('/login', $DEBUG_DB );
			}
		}
	}
	
	if ( $ERROR_MESSAGE ){
		?>
		<div class="alert alert-danger">
			<?= $ERROR_MESSAGE ?>
		</div>
		<?php 
	}
	?>
	<form onsubmit="return recoveryPasswordForm(this)" method="post">
		<div class="container py-4">
			<h2 class="animo-title">Changement de mot de passe de votre profil : </h2>
			<div class="row">
				<div class="form-group col-md-6">
					<label class="control-label">Mot de passe : </label>
					<input class="form-control" type="password" name="PASSWORD" value="" autocomplete="new-password" />
				</div>
				<div class="form-group col-md-6">
					<label class="control-label">Mot de passe (confirmation) : </label>
					<input class="form-control" type="password" name="PASSWORD2" value="" autocomplete="new-password" />
				</div>
				
				<div class="col-md-12" style="margin-bottom:15px;">
					<div class="errorMess alert alert-danger" style="display:none"></div>
				</div>
				
				<div class="form-group col-md-12">
					<button type="submit" class="btn btn-animo">Modifier le mot de passe</button></div>
					<input type="hidden" name="action" value="changePassword" />
				</div>
			</div>
			
		</div>
	</form>
	<script>
	function forcePassword(p){var intScore=0;if(p.length>0&&p.length<=4){intScore+=p.length}else if(p.length>=5&&p.length<=7){intScore+=6}else if(p.length>=8&&p.length<=15){intScore+=12}else if(p.length>=16){intScore+=18}if(p.match(/[a-z]/)){intScore+=1}if(p.match(/[A-Z]/)){intScore+=5}if(p.match(/\d/)){intScore+=5}if(p.match(/.*\d.*\d.*\d/)){intScore+=5}if(p.match(/[!,@,#,$,%,^,&,*,?,_,~]/)){intScore+=5}if(p.match(/.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~]/)){intScore+=5}if(p.match(/(?=.*[a-z])(?=.*[A-Z])/)){intScore+=2}if(p.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])/)){intScore+=2}if(p.match(/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!,@,#,$,%,^,&,*,?,_,~])/)){intScore+=2}return intScore}
	
	function recoveryPasswordForm(form){
		try{
			var errors = [];
			$(form).find(".errorMess").empty().slideUp();
			if ( form.PASSWORD.value != form.PASSWORD2.value ){
				errors.push(<?= json_encode(L("Les mots de passe ne sont pas identiques")) ?>);
			}
			if ( forcePassword(form.PASSWORD.value) < 25 ){
				errors.push(<?= json_encode(L("Votre mot de passe n'est pas suffisement fort. Essayer d'ajouter des minuscules, majuscules, chiffres et caractères spéciaux.")) ?>);
			}
	
			if ( errors.length == 0 ){
				return true;
			} else {
				$(form).find(".errorMess").html(errors.join("<br /><br />")).slideDown();
			}
		} catch( e ) {
			console.log(e);
		}
		return false;
	}
	</script>
	<?php 
} else {
	?>
	<div class="py-4">
		<div class="alert alert-danger">
			Votre lien n'est pas valide ou il a expiré.
		</div>
	</div>
	<?php 
}
