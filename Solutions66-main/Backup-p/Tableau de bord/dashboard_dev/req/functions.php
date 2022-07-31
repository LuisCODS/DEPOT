<?php
class ExceptionExitError extends Exception { }
class ExceptionMessage extends Exception { }

function getInfoMag($param,$id=null){
	if(!$id){$id=$_SESSION['mag'];}
	return $_SESSION["magasins"][$id][$param];
}

function getInfoMagDB($id=null){
    global $mysqli;
    if(!$id){
        $id = $_SESSION['mag'];
    }
    $getMagasin = query("SELECT * FROM MAGASIN WHERE ID_MAGASIN = ? LIMIT 1",[$id],$mysqli);
    return $getMagasin->fetch_assoc();
}

function get_current_user_info($fields = "*"){
	return get_user_info($_SESSION["utilisateur"]["id_utilisateur"],$fields);
}

function getInfoMagCaisseDB($param,$caisse = null){
	global $mysqli;
	if(!$caisse){
		$caisse = getInfoMag("caisse_db");
	}
	$getMagasin = query("SELECT $param FROM MAGASIN WHERE caisse_db = ? LIMIT 1",[$caisse],$mysqli);
	return $getMagasin->fetch_row()[0];
}

function get_all_caisse_db(){
	global $mysqli,$dbAnimoCaisse;
	$getAllCaisse = query("SELECT GROUP_CONCAT(MAGASIN.caisse_db) FROM MAGASIN",[],$mysqli);
	return explode(",",$getAllCaisse->fetch_row()[0]);
}

function getContrastColor($hexColor) {
	//////////// hexColor RGB
	$R1 = hexdec(substr($hexColor, 1, 2));
	$G1 = hexdec(substr($hexColor, 3, 2));
	$B1 = hexdec(substr($hexColor, 5, 2));

	//////////// Black RGB
	$blackColor = "#000000";
	$R2BlackColor = hexdec(substr($blackColor, 1, 2));
	$G2BlackColor = hexdec(substr($blackColor, 3, 2));
	$B2BlackColor = hexdec(substr($blackColor, 5, 2));

	//////////// Calc contrast ratio
	$L1 = 0.2126 * pow($R1 / 255, 2.2) +
	0.7152 * pow($G1 / 255, 2.2) +
	0.0722 * pow($B1 / 255, 2.2);

	$L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
	0.7152 * pow($G2BlackColor / 255, 2.2) +
	0.0722 * pow($B2BlackColor / 255, 2.2);

	$contrastRatio = 0;
	if ($L1 > $L2) {
		$contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
	} else {
		$contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
	}

	//////////// If contrast is more than 5, return black color
	if ($contrastRatio > 5) {
		return 'black';
	} else { //////////// if not, return white color.
		return 'white';
	}
}

//convert range min/prix
//  ex : "60-70" à "[60,70]"
//  ex : "60" à "[60,60]"
//  ex : "asdasd-adad" à "[-1,-1]"
//  ex : "985-adad" à "[-1,-1]"
//  ex : "" à "[-1,-1]"
function convertRangeDBtoJS( $t ){
	$min = "-1";
	$max = "-1";
	if ( preg_match('#^(\d+)-(\d+)$#',$t,$matches) ){
		$min = $matches[1];
		$max = $matches[2];
	} else if ( preg_match('#^(\d+)$#',$t,$matches) ){
		$min = $matches[1];
		$max = $matches[1];
	}
	return sprintf("[%s,%s]",$min,$max);
}

function get_user_info($id_utilisateur,$fields = "*"){
	global $mysqli;
	if(!preg_match('#^\d+$#',$id_utilisateur)){
		throw new Exception("Erreur get_user_info: first argument must be numeric");
	}
	if(is_string($fields)){
		$fields = array($fields);
	}else if(!is_array($fields)){
		throw new Exception("Erreur get_user_info: second parameter must be a string or an array");
	}
	$fields = array_filter($fields,"is_string");
	if(count($fields) === 0){
		throw new Exception("Erreur get_user_info: second parameter must be array(string) or string, array(mixed) given instead");
	}
	$list_fields = implode(", ",$fields);
	$enonce = "SELECT $list_fields FROM utilisateur WHERE id_utilisateur = ? LIMIT 1";
	$arraystmt = [];
	$arraystmt["id_utilisateur"] = $id_utilisateur;
	$resultUser = query($enonce,$arraystmt,$mysqli) or trigger_error("Erreur SQL: {$mysqli->error}",E_USER_ERROR);
	if($resultUser->num_rows === 1){
		return $resultUser->fetch_assoc();
	}else{
		throw new Exception("Erreur get_user_info: invalid user or requested fields");
	}
}

function check_username_availability($username){
	global $mysqli;
	$enonce = "SELECT username FROM utilisateur WHERE username = ?";
	$resultUsername = query($enonce,[$username],$mysqli) or false;
	if(!$resultUsername) return false;
	return ($resultUsername->num_rows === 0);
}

function check_user_email_availability($email){
	global $mysqli;
	if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return false;
	$enonce = "SELECT email FROM utilisateur WHERE email = ?";
	$resultEmail = query($enonce,[$email],$mysqli) or false;
	if(!$resultEmail) return false;
	return ($resultEmail->num_rows === 0);
}

function save_user_info($id_utilisateur){
	global $mysqli;
	if(has_rights("utilisateur_edit_all")){
		$admin_mode = true;
	}else if($id_utilisateur != get_current_user_info("id_utilisateur")){
		return false; // tutt tutt tutttttt
	}
	$arrayDB = array();
	if(preg_match('#^\d+$#',$id_utilisateur)){
		$is_update = true;
		$arrayDB["id_utilisateur"] = $id_utilisateur;
	}
	$errors = [];
	if(check_username_availability($_POST["username"])){
		$arrayDB["username"] = $_POST["username"];
	}else{
		$errors[] = "Le nom d'utilisateur est déjà utilisé par un autre utilisateur du réseau.";
	}
}

$LIST_DROIT_CACHE = [];
$SECURITY_CACHE = null;
define("DROIT_AUTORISER","A");
define("DROIT_REFUSER","F");

function get_current_security_level(){
	global $mysqli,$SECURITY_CACHE;

	if ( $SECURITY_CACHE === null ){

		$querySecurity = query("SELECT security FROM utilisateur WHERE id_utilisateur = ? LIMIT 1",[$_SESSION["utilisateur"]["id_utilisateur"]],$mysqli);
		if($querySecurity->num_rows === 1){
			$SECURITY_CACHE = (int)$querySecurity->fetch_row()[0];
			return $SECURITY_CACHE;
		}else{
			// user have been deleted so get it out of there!
			unset($_SESSION["magasins"]);
			unset($_SESSION["mag"]);
			unset($_SESSION["utilisateur"]);
			unset($_SESSION["dashboard"]);
		}
		return false;
	} else {
		return $SECURITY_CACHE;
	}
}

function generateRandomString($length = 32, $characters=null) {
	if ($characters===null){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

function has_rights($right){
	global $mysqli,$LIST_DROIT_CACHE;

	if(is_string($right)){
		$rights = array($right);
	}else if(is_array($right)){
		$rights = $right;
	}else{
		throw new Exception("Erreur has_rights: first argument must be array(string) or string, ".gettype($right)." given instead");
	}
	$rights = array_filter($rights,"is_string");
	if(count($rights) === 0){
		throw new Exception("Erreur has_rights: first argument must be array(string), array(mixed) given instead");
	}
	// constantes
	$USER = $_SESSION["utilisateur"]["id_utilisateur"];
	$MAGASIN = $_SESSION["mag"];

	// verifier si sécurité a changé
	$SESSION_SECURITY = $_SESSION["utilisateur"]["security"];
	$SECURITY = get_current_security_level();
	
	if($SECURITY != $SESSION_SECURITY){
		$_SESSION["utilisateur"]["security"] = $SECURITY;
	}
	
	$DEFAULT_SECURITY = 1;
	// peut valider plusieurs pages à la fois
	// une seule a besoin d'être invalide pour refuser l'accès
	foreach($rights as $unright){
		// voir si déjà en cache
		if(!empty($LIST_DROIT_CACHE[$unright])){
			if($LIST_DROIT_CACHE[$unright] == DROIT_REFUSER){
				return false;
			}else if($LIST_DROIT_CACHE[$unright] == DROIT_AUTORISER){
				continue;
			}
		}
		
		// vérifier en premier le droit du brand
		$rowRightPage = null;
		$enonce = "SELECT * 
					 FROM DASH_PAGE_NEW
					WHERE P = ? LIMIT 1";
		$resultRightPage = query($enonce,[$unright],$mysqli);
		if($resultRightPage->num_rows === 1){
			$rowRightPage = $resultRightPage->fetch_assoc();
			$listBrandOk = explode(",",$rowRightPage["LIST_BRAND_AUTHORISED"]);
			//si la page est dans un brand
			if( !in_array($_SESSION["brand"],$listBrandOk) ){
				$LIST_DROIT_CACHE[$unright] = DROIT_REFUSER;
				return false;
			}
		}
		
		// verifier en deuxieme si l'utilisateur a un droit défini pour cette page
		$enonce = "SELECT * 
					 FROM LINK_DASH_utilisateur
						  JOIN DASH_PAGE_NEW USING(ID_PAGE)
						  JOIN utilisateur_magasin USING(id_utilisateur_magasin)
					WHERE utilisateur_magasin.id_utilisateur = ? AND utilisateur_magasin.id_magasin = ? AND P = ?";
		$resultRightUser = query($enonce,[$USER,$MAGASIN,$unright],$mysqli) or trigger_error("Erreur SQL: {$mysqli->error}");
		if($resultRightUser->num_rows === 1){
			$rowRight = $resultRightUser->fetch_assoc();
			// si l'utilisateur n'a pas été banni accorder l'accès
			if($rowRight["FORBIDDEN"] == "1"){
				$LIST_DROIT_CACHE[$unright] = DROIT_REFUSER;
				return false;
			}else{
				$LIST_DROIT_CACHE[$unright] = DROIT_AUTORISER;
				continue;
			}
		}
		
		// si aucun droit défini vérifier les droits par défaut
		if($rowRightPage){
			// parcourir les différents droits
			if($SECURITY <= $rowRightPage["DEFAULT_SECURITY"]){
				$LIST_DROIT_CACHE[$unright] = DROIT_AUTORISER;
				continue;
			}else{
				$LIST_DROIT_CACHE[$unright] = DROIT_REFUSER;
				return false;
			}
		}
		
		
		// rien de défini, bloquer aux SS seulement
		if($SECURITY <= $DEFAULT_SECURITY){
			$LIST_DROIT_CACHE[$unright] = DROIT_AUTORISER;
			continue;
		}else{
			$LIST_DROIT_CACHE[$unright] = DROIT_REFUSER;
			return false;
		}
		
		//euh... AL, on arrive quand ici ? (lol!)
		$LIST_DROIT_CACHE[$unright] = DROIT_AUTORISER;
	}
	return true;
}

$Gender = array( "f" => "Mme. ", "h" => "M. ");

function displayName($p1, $n1, $s1, $p2, $n2, $s2){
	global $Gender;
	$disp1 =''; $disp2 =''; $displayname='';
	//Même nom de famille
	if($n1 !='' && $n1 == $n2){
		if($p1 !=''){ $dis1 = $p1; }else{ $dis1 = $Gender[$s1]; }
		if($p2 !=''){ $dis2 = $p2; }else{ $dis2 = $Gender[$s2]; }
		return $dis1.' & '.$dis2.' '.$n1;
	}
	//2 Noms
	if($n2 !='' || $p2 !=''){
		if($p1 !=''){ $dis1 = $p1; }else{ $dis1 = $Gender[$s1]; }
		if($p2 !=''){ $dis2 = $p2; }else{ $dis2 = $Gender[$s2]; }
		return $dis1.' '.$n1.' & '.$dis2.' '.$n2;
	}else{
		if($p1 !=''){ $dis1 = $p1; }else{ $dis1 = $Gender[$s1]; }
		return $dis1.' '.$n1;
	}
}

function displayNameByLigne($uneLigneClient){
	return displayName($uneLigneClient["PRENOM"],$uneLigneClient["NOM"],$uneLigneClient["sexe"],$uneLigneClient["PRENOM2"],$uneLigneClient["NOM2"],$uneLigneClient["sexe2"]);
}

function assign_store_to_client($id_client){
	global $mysqli;
	try{
		$arrayDB = array();
		$arrayDB["ID_CLIENT"] = $id_client;
		$arrayDB["ID_MAGASIN"] = $_SESSION["mag"];
		// voir si déja assigné
		$checkifMag = query("SELECT CL_MAG_ID FROM CLIENT_MAGASIN
							WHERE ID_CLIENT = ? AND ID_MAGASIN = ? LIMIT 1",[$id_client,$mag],$mysqli);
		if($checkifMag->num_rows === 1){
			// existe déjà
			// remonter la priorité vu que c'est le dernier magasin qui y a touché
			faireDelete_i($arrayDB,"CLIENT_MAGASIN",$mysqli);
			faireInsert_i($arrayDB,"CLIENT_MAGASIN",$mysqli);
		}else{
			// existe pas, créer le lien
			faireInsert_i($arrayDB,"CLIENT_MAGASIN",$mysqli);
		}
		return $mysqli->insert_id; // retourne le CL_MAG_ID, pour usage futur
	}catch(Exception $e){
		return false;
	}
}

class MailBuilderException extends Exception{}

class MailBuilder{
	const DEFAULT_DELIMITER = '%%';
	const DEFAULT_TEMPLATE_DIR = __DIR__."/../inc/template_mail_builder/";
	private function error($error){
		throw new MailBuilderException($error);
	}
	public function joinPaths(){
		$paths = array();

		foreach (func_get_args() as $arg) {
			if ($arg !== '') { $paths[] = $arg; }
		}

		return preg_replace('#/+#','/',join('/', $paths));
	}
	public function parseData($fc, $data, $delimiter = self::DEFAULT_DELIMITER){
		if(!is_array($data)){
			self::error("Erreur build_mail: Second argument must be an array");
		}
		if(!is_string($fc)){
			self::error("Erreur build_mail: First argument must be a string");
		}
		if(count($data) < 1){
			return $fc;
		}else{
			// replace keys with values
			foreach($data as $key => $key_value){
				// remove delimiter from key before adding them (for backward compatibility with manual technique)
				$key = str_replace($delimiter, "", $key);
				// add delimiters for key
				$key_r = $delimiter . $key . $delimiter;
				// replace value
				$fc = str_replace($key_r, $key_value, $fc);
			}
			// return modified file contents
			return $fc;
		}
	}
	public function build($template_file, $data, $template_root = self::DEFAULT_TEMPLATE_DIR, $delimiter = self::DEFAULT_DELIMITER){
		/*
		 * Take a template email file and replace values indicated by delimiters in original file using an array of values
		 *
		 * @args:
		 * $template_file		string						Filename of template. File must exists
		 * $data				array[string => string]		Data to replace and keys. Array can be empty
		 * $template_root		string						Optional: specify folder where template file is
		 * $delimiter			string						Optional: specify delimiter for keys in original file (default is '%%')
		 *
		 * @returns string: Template file contents with replaced data
		 *
		 * @throws Exception
		 *
		 * */
		$tf = self::joinPaths($template_root, $template_file);
		// do some verification stuff
		if(!file_exists($tf)){
			self::error("Erreur build_mail: '".$template_root . $template_file."': No such file or directory");
		}
		if(is_dir($tf)){
			self::error("Erreur build_mail: '".$template_root . $template_file."' is a directory");
		}
		if(!is_array($data)){
			self::error("Erreur build_mail: Second argument must be an array");
		}
		if(!is_dir($template_root)){
			self::error("Erreur build_mail: '$template_root' is not a directory");
		}
		// all is OK, start building email
		if($fc = file_get_contents($tf)){
			return self::parseData($fc, $data, $delimiter);
		}else{
			self::error("Erreur build_mail: Unable to read '".$template_root . $template_file."': Permission denied");
		}

	}
}

define("SFTP_USERNAME","animoetc");
define("SFTP_PASSWORD",'jklu$as0d9634!');
define("SFTP_HOST","animoetc.com");

?>
