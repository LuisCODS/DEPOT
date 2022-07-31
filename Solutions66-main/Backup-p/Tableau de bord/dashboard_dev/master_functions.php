<?php

function getDateLastDayMonth($annee,$mois){
	$nb = date("t", strtotime(sprintf("%d-%02d-01",$annee,$mois)) );
	return sprintf("%d-%02d-%02d",$annee,$mois,$nb);
}

function hed($txt){
	return html_entity_decode( $txt, ENT_COMPAT | ENT_HTML401,"UTF-8");
}
function convertAccents($t){
	$normalizeChars = array(
			'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
			'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
			'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
			'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
			'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
			'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
			'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
			'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
	);

	return strtr($t, $normalizeChars);
}
function formatFileName($t){
	//$t = str_replace([" ","\n","\r"],'_',$t);
	$t = preg_replace('#\s+#','_',$t);
	//$t = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $t);
	$t = convertAccents($t);
	//$t = strtolower($t);
	$t = preg_replace('#[^a-zA-Z_\-]+#','',$t);

	if ( $t == "" ) $t = "_";

	return $t;
}

$LIST_CARACT_ESCAPE_RE = [".","\\","+","*","?","[","^","]","$","(",")","{","}","=","!","<",">","|",":","-"];
function is_nfs($v){
	global $LIST_CARACT_ESCAPE_RE;

	$p = in_array(LOCAL_NUM_DECIMAL,$LIST_CARACT_ESCAPE_RE) ? "\\" . LOCAL_NUM_DECIMAL : LOCAL_NUM_DECIMAL;
	$s = in_array(LOCAL_NUM_MILLIER,$LIST_CARACT_ESCAPE_RE) ? "\\" . LOCAL_NUM_MILLIER : LOCAL_NUM_MILLIER;

	return preg_match('#^\-?\d+(' . $s . '\d+)*(' . $p . '\d*)$#',$v);
}

function reverse_nfs($v){
	return str_replace( [LOCAL_NUM_DECIMAL,LOCAL_NUM_MILLIER], [".",""], $v);
}


/*
Formatation du prix: nf avec un espace
@$v:c'est le montant;
@$videIfZero :affiche rien si le nombre égal à zéro.
*/
function nfs($v,$videIfZero=false){
	if ($videIfZero and ( !$v or round($v,2) == 0) ){
		return "";
	}
	return number_format(round($v,2),2,LOCAL_NUM_DECIMAL,LOCAL_NUM_MILLIER);
}
// nf avec espace, sans décimales
function nfsnd($v){
	return number_format($v,0,LOCAL_NUM_DECIMAL,LOCAL_NUM_MILLIER);
}

function zeropad($num, $lim=2){
	return (strlen($num) >= $lim) ? $num : zeropad("0" . $num, $lim);
}


function L($t, $s=""){
	global $L;
	if (isset($L[$t])){
		$t = $L[$t];
	}
	if ( $s == "o" ) //String UpperCase
		return mb_convert_case(mb_substr($t,0,1,'UTF-8'),MB_CASE_TITLE,"UTF-8") . mb_substr($t,1,mb_strlen($t)-1,'UTF-8');
	elseif ( $s == "t" ) //Word UpperCase
		return mb_convert_case($t,MB_CASE_TITLE,"UTF-8");
	elseif ( $s == "u" ) //UpperCase
		return mb_convert_case($t,MB_CASE_UPPER,"UTF-8");
	elseif ( $s == "l" ) //lowerCase
		return mb_convert_case($t,MB_CASE_LOWER,"UTF-8");
	return $t;
}
function admin_text($text){
	return sprintf("<span style='color: rgb(58, 133, 134);font-size: 12px;font-style: italic;font-weight:400;'>%s</span>",$text);
}
function convertApostrophe( $texte ) {
	$newTexte = preg_replace("/(\\\\|)\\'/", "\\'", $texte);
	return $newTexte;
}
function cleanTextHTML($text){
	// enleve les tags inutiles
	$text = strip_tags($text,"<h3><h4><h5><ul><li><p><ol><br><b><i><u><del><sup><sub><a>");
	// enleve les multi-espaces
	$text = preg_replace('#(\r|\n)+#',"\n",$text);
	// enlève les br successifs
	$text = preg_replace('#(\<br ?\/?>)+#',"<br />",$text);
	// enlever les paragrahes vides laissés par CKEditor
	$text = preg_replace('#\<p\>(\s|\n|\r|\t)*\<\/p\>#',"",$text);
	$text = preg_replace('#\ {2,}#'," ",$text);
	return $text;
}
function phoneFormat($phone){
	if( preg_match( '/^[^0-9]*(\d{3})[^0-9]*(\d{3})[^0-9]*(\d{4})[^0-9]*$/', $phone, $matches )){
		$result = $matches[1] . '-' .$matches[2] . '-' . $matches[3];
		return $result;
	}
}

function escapeForSqlLike($t){
	return mb_ereg_replace( '([_%])', '\\\1', $t );
}

function faireInsert_i( $dataArray, $nomTable, $linkIden, $debug=NULL, $valeurvide=true, $doReplace=false ) {
	//Accumuler les nom des table et leur valeurs enfin de créer le
	$cumulNoms = "";
	$cumulValeurs = "";

	foreach ($dataArray as $nomColonne=>$valeur) {
		$cumulNoms .= "`" . $nomColonne . "`, ";

		if ( is_null($valeur) ) { //si valeur NULL; mettre le texte NULL
			$cumulValeurs .= "NULL, ";
		} elseif( is_string($valeur) ) {//si chaine de lettre; mettre des  ''
			if ( $valeurvide and ($valeur == "" or strtolower($valeur) == "n/a" or $valeur == "--" or $valeur == "-") ) {
				$cumulValeurs .= "NULL, ";
			} else {
				$cumulValeurs .= "'".trim($linkIden->real_escape_string($valeur))."', ";
			}
		} elseif( is_float($valeur) ) {//si float
			$valeur = (string)$valeur;
			$cumulValeurs .= str_replace(',','.',$valeur).", ";
		} else {
			$cumulValeurs .= $valeur.", ";
		}
	}

	//Enlever le dernier ", "
	$cumulNoms = substr($cumulNoms, 0, -2);
	$cumulValeurs = substr($cumulValeurs, 0, -2);

	//Creation de l'énoncer
	if ( $doReplace )
		$enonce = "replace into ".$nomTable."\n";
	else
		$enonce = "insert into ".$nomTable."\n";
	$enonce .= "(".$cumulNoms.")\n";
	$enonce .= "values ( ".$cumulValeurs." )\n";

	if ( $debug and $debug > 0 ) {	echo $enonce."<br/><br/>"; }

	if ( !$debug or $debug < 2 ){
		if ( $linkIden->query( $enonce ) ) {

		} else {
			throw new Exception( 'Erreur SQL : faireInsert_i : '.$linkIden->errno." :: ".$linkIden->error );
		}
	}
	//if ( $debug ) {	echo "Row insert into ".$nomTable.".<br/>"; }

}
/*
@$dataArray:tableau associatif de tout les champs a "set";
@$nomTable: c'Est une string, le nom de la table;
@$colonneWhere: est une string ou array, comportant la/les clef pour faire le "where": doit avoir la valeur de la clef dans premier parametre($dataArray);
@$linkIden: l'object ressource de php pour la DB (le connecteur);
@$debug : int : si égal a 2, la function va juste afficher l'enonce. égal a 1, la function va afficher ET éxécuter l'énonce. 
...Et 0, seulement éxécuter(mets a 2 quand test préléminaire,a 1, quand tu test pour écrire et quand tout est beau, tu mets a 0).
*/
function faireUpdate_i( $dataArray, $nomTable, $colonneWhere, $linkIden, $debug=NULL ) {

	if ( is_string($colonneWhere) ){
		$colonneWhere = array($colonneWhere);
	}

	$cumulSet = "";
	$listWhere = array();
	$cumulWhere = "";
	foreach ($dataArray as $nomColonne=>$valeur) {

		if ( in_array($nomColonne,$colonneWhere) ) {
			if ( is_null($valeur) or $valeur === false ) { //si valeur NULL; mettre le texte NULL
				$listWhere[] = "`".$nomColonne."` is NULL";
			} elseif( is_string($valeur) ) {//si chaine de lettre; mettre des  ''
				$listWhere[] = "`".$nomColonne."` = '".trim($linkIden->real_escape_string($valeur))."'";

			} elseif( is_float($valeur) ) {//si float
				$valeur = (string)$valeur;
				$listWhere[] = "`".$nomColonne."` = " . str_replace(',','.',$valeur);
			} else {
				$listWhere[] = "`".$nomColonne."` = " . $valeur;
			}
		} else {
			$cumulSet .= "`".$nomColonne."`=";

			if ( is_null($valeur) or $valeur === false ) { //si valeur NULL; mettre le texte NULL
				$cumulSet .= "NULL, ";
			} elseif( is_string($valeur) ) {//si chaine de lettre; mettre des  ''
				if ($valeur == "") {
					$cumulSet .= "NULL, ";
				} else {
					$cumulSet .= "'".trim($linkIden->real_escape_string($valeur))."', ";
				}
			} elseif( is_float($valeur) ) {//si float
				$valeur = (string)$valeur;
				$cumulSet .= str_replace(',','.',$valeur).", ";
			} else {
				$cumulSet .= $valeur.", ";
			}
		}
	}


	$cumulWhere = implode(' and ',$listWhere);

	if ($cumulSet != "") {
		$cumulSet = substr($cumulSet, 0, -2);

		$enonce = "update ".$nomTable." set ".$cumulSet." where ".$cumulWhere;
		if ( $debug and $debug > 0 ) {	echo $enonce."\n<br/><br/>\n";}
		if ( !$debug or $debug < 2 ){
			if ( $linkIden->query( $enonce ) ) {

			} else {
				throw new Exception( '\nErreur SQL : faireUpdate_i : '.$linkIden->errno." :: ".$linkIden->error );
			}
		}
	} else {
		if ( $debug and $debug > 0 ) {	echo "Aucune valeur a 'SET'\n<br/><br/>\n";}
	}
}

function faireDelete_i( $arrayColValue, $tableName, $linkIden, $debug=NULL ) {
	$cumulWhere = "";
	foreach( $arrayColValue as $colName=>$value ) {
		if ($cumulWhere != "") { $cumulWhere .= " and "; }
		if ( $value === null ){
			$cumulWhere .= $colName." is null";
		} else {
			if( is_string($value) ){
				$cumulWhere .= $colName."='".trim($linkIden->real_escape_string($value))."'";
			} elseif( is_float($value) ) {//si float
				$value = (string)$value;
				$cumulWhere .= str_replace(',','.',$value).", ";
			} else {
				$cumulWhere .= $colName."=".$value;
			}
		}
	}
	$enonce = sprintf( "delete from %s where %s", $tableName, $cumulWhere);
	if ( $debug and $debug > 0 ) {
		echo $enonce."\n<br/><br/>\n";
	}
	if ( !$debug or $debug < 2 ){
		if ( $linkIden->query( $enonce ) ) {

		} else {
			throw new Exception( '\nErreur SQL : faireDelete_i : '.$linkIden->errno." :: ".$linkIden->error );
		}
	}
}

function wisePrintStack($e){
	/*
	@raison: Because "xdebug" sucks to print catched exception !
	@param:
	$e (Exception)	: (REQUIRED) Exception object from "catch"
	 */
	if (INDEV){
		echo '<table class="PrintStack">';

		//header principal
		echo sprintf('<tr><th colspan="4">%s</th></tr>',$e->getMessage() );

		//header column
		echo '<tr><th>File</th><th>Line</th><th>Function</th><th>Args</th></tr>';

		//Traceback
		foreach( $e->getTrace() as $trace ){
			if ( $trace["function"] == "{closure}" ){
				$file = $trace["file"];
				$line = $trace["line"];
				$function = $trace["function"];
				$args = "";
				if ($file and $line){
					echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$file,$line,$function,$args);
				}
			} else {
				$file = $trace["file"];
				$line = $trace["line"];
				$function = $trace["function"];
				$args = json_encode($trace["args"],JSON_UNESCAPED_SLASHES);
				echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$file,$line,$function,$args);
			}
		}
		echo '</table>';
	}
}

class mysqli_stmt_jlt {
	public function __construct ( mysqli_stmt $stmt ){
		$this->stmt = $stmt;
		$this->stmt->store_result();

	}

	public function fetch_row(){
		$this->fields = $this->stmt->result_metadata()->fetch_fields();

		$args = array();
		foreach($this->fields AS $k=>$field) {
			$args[$k] = &$field->name;
		}

		call_user_func_array(array($this->stmt, 'bind_result'), $args);
		if ( $row = $this->stmt->fetch() ) {
			return $args;
		} else {
			return $row;
		}
	}
	public function fetch_assoc(){
		//return $this->fetch_array(MYSQLI_ASSOC);
		$this->fields = $this->stmt->result_metadata()->fetch_fields();


		$args = array();
		foreach($this->fields AS $field) {
			$key = str_replace(' ', '_', $field->name); // space may be valid SQL, but not PHP
			while ( isset($args[$key]) ){
				$key .= "_";
			}
			$args[$key] = &$field->name; // this way the array key is also preserved
		}

		call_user_func_array(array($this->stmt, 'bind_result'), $args);
		if ( $row = $this->stmt->fetch() ) {
			$c = [];
			foreach($args as $key => $val){
				$c[$key] = $val;
			}
			return $c;
		} else {
			return $row;
		}
	}



	public function __isset($name){
		return isset($this->stmt->{$name});
	}

	public function __get($name){
		return $this->stmt->{$name};
	}
	public function __call($name, $arguments){
		call_user_func_array(array($this->stmt, $name), $arguments);
	}
	public static function __callStatic($name, $arguments){
		call_user_func_array(array($this->stmt, $name), $arguments);
	}

}

function Query_AddParam(&$paramBind,$params,&$enonce){
	foreach( $params as $k=>$v ){
		if ( is_null($v) ){
			$paramBind[0] .= "s";
			$paramBind[] = $null;
			continue;
		}
		if ( is_float($v) ){
			$paramBind[0] .= "d";
			$paramBind[] = $v;
			continue;
		}
		if ( is_int($v) ){
			$paramBind[0] .= "i";
			$paramBind[] = $v;
			continue;
		}

		if ( is_array($v) ){
			//Mod $enonce...
			//    find position of '?'
			$lastPos = 0;
			for ( $i=0; $i <= strlen($paramBind[0]) and $lastPos !== false; $i++ ){
				$lastPos = mb_strpos($enonce,"?",$lastPos+1);
			}

			if ( $lastPos !== false ){
				$newStr = implode(",",array_fill(0, sizeof($v), "?"));
				$enonce = mb_substr($enonce,0,$lastPos) . $newStr . mb_substr($enonce,$lastPos+1);
			} else {
				throw new Exception("Miss ? for array param");
			}

			Query_AddParam($paramBind,$v,$enonce);
			continue;
		}

		$paramBind[0] .= "s";
		$paramBind[] = (string)$v;
	}
}

//query('select * from utilisateur where id_utilisateur = ? AND nom = ?',[221,$_GET['nom']],$mysqli);
function query($enonce,$params,$conn,$throwError=true){
	//is_string is_float is_null
	if ( !method_exists($conn,"prepare") ){
		throw new Exception( 'query func has not a connector' );
	}


	$null = null;
	$paramBind = [""];

	Query_AddParam($paramBind,$params,$enonce);

	if ( $stmt = $conn->prepare( $enonce ) ){
		if ( sizeof($paramBind) > 1 ){
			$tmp = array();
			foreach($paramBind as $key => $value) {
				$tmp[$key] = &$paramBind[$key];
			}
			call_user_func_array( array($stmt,"bind_param"), $tmp);
		}
		$stmt->execute();


		return new mysqli_stmt_jlt($stmt);
	} else {
		if ( $throwError ){
			throw new Exception( 'Query SQL error : ' . $conn->error );
		}
		return false;
	}
}

function getAllRowsToArray($enonce,$params,$conn){
	$listRows = [];

	if ( $result = query($enonce,$params,$conn) ){
		while( $uneLigne = $result->fetch_assoc() ){
			$listRows[] = $uneLigne;
		}
		return $listRows;
	} else {
		return null;
	}
}

function vex( $var ){
	echo "<style type='text/css'>.divDebugVar{color:blue;text-align:left;} .divDebugVar p {margin:0;padding:0;} </style>";
	echo "<div class='divDebugVar'><pre>";
	var_export($var);
	echo "</pre></div>";
	echo "<br />";
}
/*
Convertit tous les caractères éligibles en entités HTML.
@parm1: La chaîne d'entrée.
@parm2 ENT_QUOTES: Convertit les guillemets doubles et les guillemets simples.
@parm3 ENT_HTML5: Gère le code comme étant du HTML 5.
*/
function attrEncode($t){
	return htmlentities($t,ENT_QUOTES | ENT_HTML5);
}

function formatDateUTF8nonHTML( $d, $f='%e %B %Y' ){
	if($f == "%e %B %Y" && $_SESSION["lang"] == "en"){
		$f = "%B %e, %Y";
	}
	if ( $d== "" ) return "";
	if($f == '%e %B %Y' && date("d",strtotime($d)) == "01"){
		return trim("1 ". utf8_encode(strftime("%B %Y", strtotime( $d ) )));
	}
	if($f == '%B %e, %Y' && date("d",strtotime($d)) == "01"){
		return trim(utf8_encode(strftime("%B 1st, %Y", strtotime( $d ) )));
	}
	return trim(utf8_encode(strftime($f, strtotime( $d ) )));
}

function formatDateUTF8( $d, $f='%e %B %Y' ){
	if($f == "%e %B %Y" && $_SESSION["lang"] == "en"){
		$f = "%B %e, %Y";
	}
	if ( $d== "" ) return "";
	if($f == '%e %B %Y' && date("d",strtotime($d)) == "01"){
		return "1<sup>er</sup> ". utf8_encode(strftime("%B %Y", strtotime( $d ) ));
	}
	if($f == '%B %e, %Y' && date("d",strtotime($d)) == "01"){
		return utf8_encode(strftime("%B 1st, %Y", strtotime( $d ) ));
	}
	return utf8_encode(strftime($f, strtotime( $d ) ));
}

function formatPrix($f){
	if($_SESSION["lang"] == "en"){
		$s = number_format((float)$f, 2, ".", ",");
		return "$".$s;
	}else{
		$s = number_format((float)$f, 2, ",", " ");
		return $s . "$";
	}
}
function redirect($url,$debugValue=null,$hash=null,$code = 302) {
	if ( $hash )
		$url .= "&hash=".substr($_POST["hash"],1);
		
	if ( $debugValue ){
		die('REDIRECT:DEBUG:<a href="'.$url.'">'.$url."</a>");
	} else {
		if(headers_sent()){
			echo($_SESSION["lang"] == "en" ? "Redirecting, plesse wait..." : "Redirection en cours, veuillez patienter...");
			die('<meta http-equiv="refresh" content="0;URL='.$url.'">');
		} else {
			// EDIT: si les headers ne sont pas encore envoyées utiliser une redirect 302.
			header("Location: $url",false,$code);
			exit;
		}
	}
}
function msg_output($msg, $type = "success", $icon = "check", $padding = 15, $margin = 0){
	// affiche un message à l'utilisateur (ex. Complêté avec succès ou Une erreur s'est produite lors de l'enregistrement).
	/*
	@param:
	$msg (string)	: 	(REQUIS) texte à afficher
	$type (string)	: 	type d'alerte (défaut "success")
	$icon (string)	: 	icône (défaut: "check")
	Si omis mais $type spécifié la fonction choisit une icône appropriée
	Sinon mettre n'importe quel ID d'icône Font Awesome SANS le "fa-"
	$padding (int)	:	Marge intérieure (défaut: 15)

	 */
	if($msg != ""){
		switch($type){
			case "warning":
			case "danger":
			case "info":
			case "success":
				$o_type = $type;
				break;
			default:
				$o_type = "success";
		}
		if($icon != "" && $icon != "check"){
			$o_icon = str_replace("fa-", "",$icon);
		}else{
			switch($o_type){
				case "danger":
				case "warning":
					$o_icon = "warning";
					break;
				case "info":
					$o_icon = "info-circle";
					break;
				case "success":
				default:
					$o_icon = "check";
					break;
			}
		}
		if(preg_match('#^[0-9]+$#', $padding)){
			$o_padding = $padding;
		}else{
			$pt = str_replace('#[^0-9\.]+#', "",$padding);
			if(preg_match('#^[0-9]+$#', $padding)){
				$o_padding = $pt;
			}else{
				$o_padding = 15;
			}
		}
		?>
		<div class="alert bgc-<?= $o_type?>-lightest" style="padding: <?= $padding?>px; margin: <?= $margin ?>px 0; display: table;width: 100%;">
			<div style="display: table-cell;width: 65px;padding: 0 10px;vertical-align: middle;color: #000;">
				<i class="fa fa-3x fa-<?= $o_icon?>"></i>
			</div>
			<div style="display: table-cell;vertical-align: middle;text-align:left;color: #000;">
				<?= $msg?>
			</div>
		</div>
		<?php
	}
}
function timeToHuman($nbSec,$limitUnit=2){
	$listValeur = [ 60*60*24*7*52, 60*60*24*7, 60*60*24, 60*60,    60,        1 ];
	$listLabel =  ["année",        "semaine",  "jour",   "heure",  "minute", "seconde"];
	$listLabel2 = ["années",       "semaines", "jours",  "heures", "minutes","secondes"];

	$listeSec = [];
	$nb = 0;

	for ($i=$limitUnit; $i < sizeof($listValeur); $i++){
		if ( $nbSec >= $listValeur[$i] ){
			$nb = floor($nbSec / $listValeur[$i]);
			if ( $nb >= 2 )
				$listeSec[] = $nb." ".$listLabel2[$i];
			else if( $nb > 0 )
				$listeSec[] = $nb." ".$listLabel[$i];

			$nbSec = $nbSec % $listValeur[$i];
		}
	}

	if ( sizeof(listeSec) == 0 ){
		$listeSec[] = "0 ".$listLabel[5];
	}

	return implode($listeSec," ");
}

function saison(){
	$adate = date('Y/m/d');
	$limits= array('/12/21'=>'hiver',
						'/09/21'=>'automne',
						'/06/21'=>'ete',
						'/03/21'=>'printemps',
						'/01/01'=>'hiver');
	foreach ($limits AS $key => $value) {
		$limit=date("Y").$key;
		if (strtotime($adate)>=strtotime($limit)) {
			return $value;
		}
	}
}

function setActive($pages){
	global $p;
	$pageArray = explode(",", $pages);
	foreach ($pageArray as $value){
		if($p == $value){
			return "active";
		}
	}
}

function setPoid($poid){
	if($poid > 899){
		return round($poid / 1000, 1).'kg';
	}else{
		return round($poid, 1).'g';
	}
}

function arronMillier($num){
	if ($num > 9999) {
		return floor($value / 1000) . ' K';
	}else{
		return $num;
	}
}

function formatNum($val){
	return empty($val)?0:$val;
}

function get_string_between($string, $start, $end){
	$string = " ".$string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string,$end,$ini) - $ini;
	return substr($string,$ini,$len);
}
function convertArrayNumericToDictTrue( $monArray ){
	$newArray = array();
	if ( sizeof($monArray) > 0 )
		foreach( $monArray as $valeur )
			$newArray[$valeur] = true;
	return $newArray;
}
function defined_all(){
	$haystack = [];
	$args = func_get_args();
	if(func_num_args() === 1){
		if(is_array($args[0]) && array_filter($args[0],"is_string")){
			$haystack = $args[0];
		}else if(is_string($args[0])){
			return defined($args[0]);
		}
	}else if(array_filter($args,"is_string")){
		$haystack = $args;
	}else{
		return false;
	}
	foreach($haystack as $constant){
		if(!defined($constant)){
			return false;
		}
	}
	return true;
}
$sftp_conn = null;
function getSFTPConnection(){
    global $sftp_conn;
    if(!$sftp_conn){
        if(!defined_all(array("SFTP_HOST","SFTP_USERNAME","SFTP_PASSWORD"))){
            throw new Exception("Erreur stockFileSFTP: missing credentials");
        }
        
        $sftp = new Net_SFTP(SFTP_HOST);
        
        if (!$sftp->login(SFTP_USERNAME, SFTP_PASSWORD)) {
            throw new Exception("Erreur SFTP: auth failed");
        }
        
        $sftp->chdir('/');
        
        $sftp_conn = $sftp;
    }
         
    return $sftp_conn;
}
function copyFileSFTP($pathSrc, $nomFormat, $path){
	if ( $pathSrc != "" ){
		$ext = "." . strtolower(pathinfo($pathSrc,PATHINFO_EXTENSION));
		$nomBaseOri = pathinfo($pathSrc,PATHINFO_FILENAME);

		//Formater le nom désiré
		$nomFichierBase = preg_replace('#%d#', date("Ymd") ,$nomFormat);
		$nomFichierBase = preg_replace('#%t#', date("His") ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%f#', preg_replace('#[^a-zA-Z0-9_\-]#','_',$nomBaseOri) ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%T#', (string)time() ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%I#', $_SESSION["user"] ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%i#', $_SESSION["agent_id"] ,$nomFichierBase);

		$sftp = getSFTPConnection();
		
		$oldfile = $sftp->get($pathSrc);

		if ( $path )
			$changeReussit = $sftp->chdir($path);

		$listeFichier = $sftp->nlist(".");
		$listeFichier = convertArrayNumericToDictTrue($listeFichier);

		$i = "";
		if ( $listeFichier[$nomFichierBase.$ext] ){
			$i = "0";
			while ( $listeFichier[$nomFichierBase."-".$i.$ext] ) {
				if($i===""){$i=0;}else{$i+=1;}
			}
			$i = "-".$i;
		}

		$nom_ftp = $nomFichierBase.$i.$ext;
		$nomValeurDB = $nomFichierBase.$i.$ext;

		if(!$sftp->put($nom_ftp,$oldfile)){
			throw new Exception("Erreur SFTP: error while sending the file.");
		}
		return $nomValeurDB;
	}
}

function reArrayFiles(&$file_post) {
	$file_ary = array();
	$file_count = count($file_post['name']);
	$file_keys = array_keys($file_post);

	for ($i=0; $i<$file_count; $i++) {
		foreach ($file_keys as $key) {
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}

	return $file_ary;
}

function stockFileSFTP($fichierSend, $nomFormat, $path = null){
	if ( $fichierSend["error"] == 0 and $fichierSend["name"] != "" ){
		$ext = "." . strtolower(pathinfo($fichierSend["name"],PATHINFO_EXTENSION));
		$nomBaseOri = pathinfo($fichierSend["name"],PATHINFO_FILENAME);

		//Formater le nom désiré
		$nomFichierBase = preg_replace('#%d#', date("Ymd") ,$nomFormat);
		$nomFichierBase = preg_replace('#%t#', date("His") ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%f#', preg_replace('#[^a-zA-Z0-9_\-]#','_',$nomBaseOri) ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%T#', (string)time() ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%I#', $_SESSION["mag"] ,$nomFichierBase);
		$nomFichierBase = preg_replace('#%i#', $_SESSION["utilisateur"]["id_utilisateur"] ,$nomFichierBase);

		$sftp = getSFTPConnection();

		if ( $path )
		    $changeReussit = $sftp->chdir($path);

		$listeFichier = $sftp->nlist(".");
		$listeFichier = convertArrayNumericToDictTrue($listeFichier);
		$i = "";
		if ( $listeFichier[$nomFichierBase.$ext] ){
			$i = "0";
			while ( $listeFichier[$nomFichierBase."-".$i.$ext] ) {
				if($i===""){$i=0;}else{$i+=1;}
			}
			$i = "-".$i;
		}

		$nom_ftp = $nomFichierBase.$i.$ext;
		$nomValeurDB = $nomFichierBase.$i.$ext;
		if(!$sftp->put($nom_ftp,$fichierSend["tmp_name"],NET_SFTP_LOCAL_FILE)){
			throw new Exception("Erreur SFTP: error while sending the file.");
		}
	}

	return $nomValeurDB;
}
function deleteFileSFTP($nomFichier, $path, $recursive = false){
   
    $sftp = getSFTPConnection();

	if ( $path )
		$changeReussit = $sftp->chdir($path);

	$listeFichier = $sftp->nlist(".");
	$listeFichier = convertArrayNumericToDictTrue($listeFichier);
	if ( $listeFichier[$nomFichier] ){
		$nom_ftp = $nomFichier;
		return ($sftp->delete($nom_ftp,$recursive));
	}
	return false;
}
function listFilesSFTP($path = ".",$param = "."){
    $sftp = getSFTPConnection();

	if ( $path )
		$sftp->chdir($path);

	$listeFichier = $sftp->nlist($param);
	$listeFichier = convertArrayNumericToDictTrue($listeFichier);
	$list2 = null;
	foreach($listeFichier as $nom => $dummy){
		$list2[$nom] = $sftp->lstat($nom);
	}
	unset($list2["."]);
	unset($list2[".."]);
	return $list2;
}
if(!class_exists("IOException")){
	class IOException extends Exception{}
}
function fileInfoSFTP($file,$path = "."){
    $sftp = getSFTPConnection();

	if ( $path )
		$changeReussit = $sftp->chdir($path);

	$listeFichier = $sftp->nlist(".");
	$listeFichier = convertArrayNumericToDictTrue($listeFichier);
	if ( $listeFichier[$file] ){
		return $sftp->lstat($file);
	}else{
		throw new IOException("Erreur SFTP: $path/$file: No such file or directory");
	}
}
function previewtext($string, $length, $tag, $etc = '...') {
	if($tag==1){ $string = strip_tags($string);}
	return (strlen($string) > $length) ? substr(substr($string, 0, $length - strlen($etc)), 0, strrpos($string, ' ')) . $etc : $text;
}
function get_file_extension($file_name) {
	if(function_exists("pathinfo")){
		return pathinfo($file_name, PATHINFO_EXTENSION);
	}
	return substr(strrchr($file_name,'.'),1);
}

function rebuildQueryString($listAddGetParam=[],$listRemove=[]){
	$listChamp = [];
	foreach ( $listAddGetParam as $k=>$v){
		$listChamp[] = $k . "=" . urlencode($v) ;
	}
	foreach ( $_GET as $k=>$v){
		if ( !array_key_exists($k,$listAddGetParam) and !in_array($k,$listRemove)){
			if( is_string($v) ){
				$listChamp[] = $k . "=" . urlencode($v) ;
			}elseif( is_array($v) ){
				foreach( $v as $vSub ){
					if( is_string($v) ){
						$listChamp[] = $k . "[]=" . urlencode($vSub);
					} else {
						$listChamp[] = $k . "[]=" . $vSub;
					}
				}
			}else{
				$listChamp[] = $k . "=" . $v ;
			}
		}
	}

	if (sizeof($listChamp) > 0 ){
		return implode('&',$listChamp);
	}
	return "";
}
?>