<?php 
<<<<<<< HEAD
//echo '<pre>' , print_r($_REQUEST) , '</pre>';
=======
>>>>>>> 02661005a422649804a221a452910f5265930706

ini_set('memory_limit', '256M');
$dbAnimoCaisseDefault = new mysqli("localhost", $username_animo, $password_animo, "animoetc_caisse_default" );
if ($dbAnimoCaisseDefault->connect_errno) {
	printf("Erreur de connection SQL : %s\n", $dbAnimoCaisseDefault->connect_error);
	exit();
}
$dbAnimoCaisseDefault->set_charset("utf8");

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

?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa icon-people icon-mr fs-4"></i>
			<span class="hidden-xs-down"> Import Excel <small>(ATTENTION : MODIFICATION DE MASSE DE LA BASE DE DONNÉES!)</small></span>
		</h1>
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
						    <!-- Table -->
							<div class="page-size-table">
<<<<<<< HEAD
								<?php 
=======
							<?php 
>>>>>>> 02661005a422649804a221a452910f5265930706
								function calculCheckSumEAN12($code){
									echo "calcul UPC checksum:".$code.":";
									$code = substr($code,0,11);
									$impair = substr($code,0,1) + substr($code,2,1) + substr($code,4,1) + substr($code,6,1) + substr($code,8,1) + substr($code,10,1);
									$pair = substr($code,1,1) + substr($code,3,1) + substr($code,5,1) + substr($code,7,1) + substr($code,9,1);
									$total = $impair * 3 + $pair;
									$checksum = 10-$total%10;
									if ($checksum == 10)
										$checksum = 0;
									echo $impair . ":" . $pair . ":" . $total . ":" . $checksum;
									return $checksum;
								}
								function convertLigneExcelEnArray($l){
									global $maSheet;
									
									$champs = [];
									$nbCol = columnIndexFromString( $maSheet->getHighestColumn() );
									for ($c=0;$c < $nbCol  and $c<=26 ;$c++){
										$champs[] = (string)$maSheet->getCell(stringFromColumnIndex($c).$l)->getCalculatedValue();
									}
									//$listNewArticle[] = $champs;
									return $champs;
								}
								
<<<<<<< HEAD
								//SI le fichier est envoté
								if ($_POST["startImport"]=="1"){
									//SI format fichier valide
									if ( substr($_FILES["fichier"]["name"],-4) == ".xls" or substr($_FILES["fichier"]["name"],-5) == ".xlsx"  ){
									    //Enregistre le fichier....
										$nomFichierTemp = session_id() . "_" . time() . "." . pathinfo( $_FILES["fichier"]["name"], PATHINFO_EXTENSION);
										$pathFichierTemp = __DIR__ . "/../../temp/tmp_excel_import/".$nomFichierTemp;
										move_uploaded_file($_FILES["fichier"]["tmp_name"], $pathFichierTemp);
									}
									//SI pas de fichier
									if ( !is_file($pathFichierTemp) ){
										die("missing file...");
									}
									//Gestion chache
									require_once __DIR__ . "/../req/PHPExcel-1.8/PHPExcel.php";
=======
								//SI le form est soumis 
								if ($_POST["startImport"]=="1"){
								    
								    //_________________________ Gestion fichier _____________________________
								    
									//SI format fichier valide
									if ( substr($_FILES["fichier"]["name"],-4) == ".xls" or substr($_FILES["fichier"]["name"],-5) == ".xlsx"  ){
									    //Create name
										$nomFichierTemp = session_id() . "_" . time() . "." . pathinfo( $_FILES["fichier"]["name"], PATHINFO_EXTENSION);
										//Create pathdestination file
										$pathFichierTemp = __DIR__ . "/../../temp/tmp_excel_import/".$nomFichierTemp;
										// temporary location
										move_uploaded_file($_FILES["fichier"]["tmp_name"], $pathFichierTemp);
									}
									if ( !is_file($pathFichierTemp) ){
										die("missing file...");
									}
									//_________________________ Gestion chache _____________________________
									
									require_once __DIR__ . "/../req/PHPExcel-1.8/PHPExcel.php";
									
>>>>>>> 02661005a422649804a221a452910f5265930706
									$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
									$cacheSettings = array( 'memoryCacheSize' => '32MB');
									PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
									
<<<<<<< HEAD
=======
									//_________________________ Instace objet Excel lecture _____________________________
									
>>>>>>> 02661005a422649804a221a452910f5265930706
									if ( substr($pathFichierTemp,-4) == ".xls" ){
										$objReader = new PHPExcel_Reader_Excel5();
									} elseif ( substr($pathFichierTemp,-5) == ".xlsx" ){
										$objReader = new PHPExcel_Reader_Excel2007();
									}
									$objReader->setReadDataOnly(true);
<<<<<<< HEAD
									$objPHPExcel = $objReader->load($pathFichierTemp);
									$listSheetName = $objPHPExcel->getSheetNames();
									?>
									<h3>Partie 2 - Paramètres d'entrée</h3>
									
									<!--====================== FORM ========================== -->
=======
									//charge le fichier
									$objPHPExcel = $objReader->load($pathFichierTemp);
									//Récupère les noms des pages
									$listSheetName = $objPHPExcel->getSheetNames();  
                                 
									?>
									<h3>Partie 2 - Paramètres d'entrée</h3>
									
									<!-- _____________________________ FORM  _____________________________ -->
									
									<form id="formParametresForExcel" method="post" target="_blank" action="ajax/admin_caisseimport.php?test=1&getFile=1"></form>
									<script>
									    /*
									    Cette fonction va copier tous les inputs du form formParametres
									    vers le form formParametresForExcel pour partir avec le post Ajax. 
									    */
										function getExcelApercu(){
											var f = $("#formParametresForExcel");
											    f.empty();
											var dataArray = $("#formParametres").serializeArray();
											console.log(dataArray);
											dataArray.forEach( function(field){
										        f.append( '<input type="hidden" name="'+field["name"]+'" value="'+field["value"]+'" />' );
											} );
											f.submit();
										}
									</script>
									
									<!-- _____________________________ FORM  _____________________________ -->
>>>>>>> 02661005a422649804a221a452910f5265930706
									<form id="formParametres" method="" onsubmit="return byPassFormSubmit(this)">
										<input type="hidden" name="fichier" value="<?php echo $nomFichierTemp ?>" />
										<!--get inputs Nom de la feuille-->
										<div id="choixNomFeuille" style="margin-bottom:20px">
											Nom de la feuille : 
											<select name="sheetName" onchange="onChangeSheetName(this)">
												<option value="">&nbsp;</option>
												<?php
<<<<<<< HEAD
												foreach( $listSheetName as $nameSheet){
=======
												foreach( $listSheetName as $nameSheet){ 
>>>>>>> 02661005a422649804a221a452910f5265930706
													?>
													<option value="<?php echo htmlentities($nameSheet) ?>"><?php echo $nameSheet ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<?php 
										?>
										<br />
										<!--get inputs colonnes names-->
										<ul>
											<li id="choixColUPC"        class="choicecol">Colonne UPC : <input type="text" size="1"                 name="col_UPC" /></li>
											<li id="choixColDescFr"     class="choicecol">Colonne DescriptionFR : <input type="text" size="1"       name="col_descFR" /></li>
											<li id="choixColDescEn"     class="choicecol">Colonne DescriptionEN : <input type="text" size="1"       name="col_descEN" /></li>
											<li id="choixColPrix"       class="choicecol">Colonne Prix : <input type="text" size="1"                name="col_prix" /></li>
											<li id="choixColCost"       class="choicecol">Colonne Cost : <input type="text" size="1"                name="col_cost" /></li>
											<li id="choixColCode"       class="choicecol" onclick="">Colonne Code Distributeur : <input type="text" size="1" name="col_code" /></li>
											<li id="choixColPrix"       class="choicecol">Colonne Departement : <input type="text" size="1"         name="col_dep" /></li>
											<li id="choixColSousDep"    class="choicecol">Colonne Sous-Departement : <input type="text" size="1"    name="col_SousDep" /></li>
											<li id="choixColPrix"       class="choicecol">Colonne Unité par caisse : <input type="text" size="1"    name="col_boite_nb" /></li>
											<li id="choixColUPCcaisse"  class="choicecol">Colonne UPC caisse : <input type="text" size="1"          name="col_UPC_caisse" /></li>
										</ul>
										<!--get inputs ligne-->
										<div>
											Première ligne article : <input type="text" size="2" name="first_line" id="first_line" />
										</div>
										<!--Zone to display l'aperçu Excel -->
										<div id="apercuChoixCol"></div>
										<?php 
										?>
										<br />
										<div id="choixFournisseur" style="margin-bottom:20px">
										    <!--get inputs Fournisseur-->
											Fournisseur : 
											<select name="id_distributeur">
												<option value="">&nbsp;</option>
												<?php
												$enonce = sprintf("select * from fournisseur where est_distributeur = 1 order by nom asc");
												$resultFour = $dbAnimoCaisseDefault->query($enonce) or die( "SQL".$dbAnimoCaisse->error );
												while( $uneLigneFour = $resultFour->fetch_assoc() ){
													?>
													<option value="<?php echo $uneLigneFour["id_fournisseur"] ?>"><?php echo $uneLigneFour["nom"] ?></option>
													<?php
												}
												?>
											</select>
											<!--get inputs Distributeur-->
											Distributeur : 
											<select name="id_fournisseur">
												<option value="">&nbsp;</option>
												<?php
												$enonce = sprintf("select * from fournisseur where est_fournisseur = 1 order by nom asc");
												$resultFour = $dbAnimoCaisseDefault->query($enonce) or die( "SQL".$dbAnimoCaisse->error );
												while( $uneLigneFour = $resultFour->fetch_assoc() ){
													?>
													<option value="<?php echo $uneLigneFour["id_fournisseur"] ?>"><?php echo $uneLigneFour["nom"] ?></option>
													<?php
												}
												?>
											</select>
										</div>
										<?php 
										//Étape de choix Update (écrasement)
										?>
										<!--get inputs checkbox -->
										<h3>Options quand mise à jour de produit</h3>
										<ul>
											<li><label><input type="checkbox" value="1" name="overwrite_descFR" /> Description FR</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_descEN" /> Description EN</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_codefour" /> Code fournisseur</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_prix" /> Prix</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_cost" /> Cost</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_dep" /> Département</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_sousdep" /> Sous-Département</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_nombreparboite" /> Nombre par boite</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_four" /> Fournisseur</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_dist" /> Distributeur</label></li>
											<li><label><input type="checkbox" value="1" name="overwrite_upccaisse" /> UPC caisse</label></li>
										</ul>
										<label><input type="checkbox" value="1" name="overwrite_disabledIfMinusCost" /> Ne pas chagner le prix vendant s'il y a un autre distributeur (actif) avec un cost moins cher</label><br />
										<label><input type="checkbox" value="1" name="overwrite_onlyidOldIsNone" /> Mettre à jour seulement si l'ancienne valeur est vide/absente</label>
										<h3>Prix</h3>
										<ul>
											<li>
												<label><input type="checkbox" value="1" name="prix_costpourcaisse" /> La colonne cost du fichier représente le cost pour la caisse</label>
											</li>
											<li>
												<label><input type="checkbox" value="1" name="prix_forcerounddown99" /> Arrondir le prix vendant au .99 plus bas</label>
												<span>(prix minimum : <input type="text" name="prix_forcerounddown99_minprix" value="4.99" style="width:100px" />)</span>
											</li>
											<li>
												<label><input type="checkbox" value="1" name="prix_dontchangepriceifsamecost" /> Ne pas changer le prix vendant si même prix coûtant</label>
											</li>
											<li>
												Date limite des changements de prix : 
												<div style="width:165px;display:inline-block">
													<input type="text" size="16" class="form-control bs-datepicker picker-simple" value="<?php echo date("Y-m-d"); ?>" name="change_date_exp">
												</div>
												<label><input type="checkbox" value="1" name="prix_changeRightNow" /> Changement immédiat</label>
											</li>
										</ul>
										<h3>Divers</h3>
										<ul>
											<li>
												<label><input type="checkbox" value="1" name="options_updateonly" /> Seulement faire les mises à jour de produits</label>
											</li>
											<li>
												<label><input type="checkbox" value="1" name="options_discontOtherDist" /> Discontinuer les autres distributeurs</label>
											</li>
										</ul>
<<<<<<< HEAD
										<br />
										<br />
										<input id="btnSubmit" class="redbutton" type="submit" value="Aperçu" />
									</form>
									
									<h3>Résultats de l'aperçu</h3>
									<div id="resultApercuTest"></div>
=======
										<input id="btnSubmit" class="redbutton" type="submit" value="Aperçu" />
									</form>

                            	    <div id="divShowButton"  style="display:none" class="mt-2">
                            	        <button onclick="getExcelApercu()">Ouvrir en Excel</button> 
                            	    </div>
									<br>
									
									<h3>Résultats de l'aperçu</h3>
									<div id="resultApercuTest"></div>
									
>>>>>>> 02661005a422649804a221a452910f5265930706
									<!--Boutton Apply -->
									<h3>Appliquer les changement</h3>
									<div>
										<input class="" type="button" value="Apply" onclick="onClickApply()" />
									</div>
									<!--Zone to display le resultat final-->
									<div id="resultatApply"></div>
									<style>
										#formParametres ul {
											list-style: none;
											padding: 0;
											margin: 0;
										}
									
										.choicecol{
											display:inline-block;
											padding:5px 10px;
										}
										.tableExcel{
											
										}
											.tableExcel td{
												border:1px solid #cccccc;
												padding:2px 5px;
											}
											.excelNomCol{
												vertical-align:middle;
												text-align:center;
												background:#aaa;
												font-weight:bold;
											}
											.caseExcelOver {
												background:#eeeeee;
											}
										
										.divChoixEnCours {
											background:#cccccc;
										}
									</style>
									<script>
										var onloadingapply = false;
										function onClickApply(){
											if ( onloadingapply ){
												alert("Déjà en chargement");
												return false;
											}
											if ( confirm("Êtes-vous sûre ?") ){
												onloadingapply = true;
												//Display text
												$("#resultatApply").empty().html("Chargement...");
												var listAFaire = [];
												$("#resultApercuTest .aFaire").map(function(i,tr){
													var data = JSON.parse( $(tr).attr("data-info") );
													listAFaire.push(data);
												});
												var param = $("#formParametres").serializeArray();
												param.push( { value:JSON.stringify(listAFaire), name:"listAFaire" });
												$.post("ajax/admin_caisseimport.php?apply=1",param,function(rep){
													onloadingapply = false;
													if ( rep && rep.status == "success" ){
														$("#resultatApply").empty().html(rep.data);
													}
												}, "json").fail(function(data) {
													log(data);
													onloadingapply = false;
													$("#resultatApply").empty().html("error inconnue");
													alert( "error inconnue" );
												});
											}
										}
										var faireChoix = false;
										var divChoixEnCours = null;
										var clickColonne = function(c){
											log(c)
										}
										var onloading = false;
										//Traite l'envoie du form
										function byPassFormSubmit(monForm){
											if ( onloading ){
												alert("Déjà en chargement");
												return false;
											}
											onloading = true;
<<<<<<< HEAD
											//Show text 
											$("#resultApercuTest").empty().html("Chargement...");
											
											try{
											    //Stock form data
=======
											$("#resultApercuTest").empty().html("Chargement...");
											
											try{
											    //get form data
>>>>>>> 02661005a422649804a221a452910f5265930706
												var param = $(monForm).serialize();
												//Send form data to ajax/admin_caisseimport.php by POST  and get the result back
												$.post("ajax/admin_caisseimport.php?test=1",param,function(rep){
													onloading = false;
													if ( rep && rep.status == "success" ){
<<<<<<< HEAD
													    
=======
													    //Rendre visible le boutton pour ouvrir le fichier
													    $("#divShowButton").css("display", "block")
>>>>>>> 02661005a422649804a221a452910f5265930706
														$("#resultApercuTest").empty().html(rep.data);
														$("#nbItemError").empty().html(rep.nbItemError);
														$("#nbItemIgnore").empty().html(rep.nbItemIgnore);
														$("#nbItemAdded").empty().html(rep.nbItemAdded);
														$("#nbItemUpdated").empty().html(rep.nbItemUpdated);
														
													} else if ( rep && rep.message != "" ){
														alert( rep.message );
													}
												}, "json").fail(function() {
													onloading = false;
													$("#resultApercuTest").empty().html("error inconnue");
													alert( "error inconnue" );
												});
											} catch (e){
												console.log(e)
											}
											return false;
										}
<<<<<<< HEAD
										
										$('.choicecol').click(function(){
											$('.choicecol').removeClass("divChoixEnCours");
											
											faireChoix = true;
											divChoixEnCours = this;
											$(this).addClass("divChoixEnCours");
											
											clickColonne = function(c){
												$(divChoixEnCours).find('input').val( c );
												
												faireChoix = false;
												divChoixEnCours = null;
												$('.choicecol').removeClass("divChoixEnCours");
												$("#apercu .tableExcel .excel_case").removeClass("caseExcelOver");
											}
										}); 
										
										function onChangeSheetName(obSrc){
											$("#apercuChoixCol").empty().html("Chargement en cours...");
											
=======
										//
										window.addEventListener("load", function(event) {
											$('.choicecol').click(function(){
												$('.choicecol').removeClass("divChoixEnCours");
												
												faireChoix = true;
												divChoixEnCours = this;
												$(this).addClass("divChoixEnCours");
												
												clickColonne = function(c){
													$(divChoixEnCours).find('input').val( c );
													
													faireChoix = false;
													divChoixEnCours = null;
													$('.choicecol').removeClass("divChoixEnCours");
													$("#apercu .tableExcel .excel_case").removeClass("caseExcelOver");
												}
											}); 
										});
										//Quand on select une page
										function onChangeSheetName(obSrc){
											$("#apercuChoixCol").empty().html("Chargement en cours...");
											//Post le nom et la feuille du fichier
>>>>>>> 02661005a422649804a221a452910f5265930706
											$.post("ajax/admin_caisseimport.php?apercu=1",
												{"fichier":$('#formParametres input[name=fichier]').val(),"sheetName":$('#formParametres select[name=sheetName]').val()},
												function(reponse,e){
													if (reponse.substr(0,3)=="ok:"){
<<<<<<< HEAD
=======
													    
>>>>>>> 02661005a422649804a221a452910f5265930706
														reponse = reponse.substr(3);
														$("#apercuChoixCol").html(reponse);
														
														$("#apercuChoixCol .tableExcel .excel_case").mouseover( function(){
															//log(["over",this])
															if ( faireChoix ){
																var c = $(this).attr("data-excel-c");
																$("#apercuChoixCol .tableExcel .excel_case[data-excel-c="+c+"]").map(function(i,e){
																	$(e).addClass("caseExcelOver");
																})
															}
														}).mouseout( function(){
															//log(["out",this])
															if ( faireChoix ){
																var c = $(this).attr("data-excel-c");
																$("#apercuChoixCol .tableExcel .excel_case[data-excel-c="+c+"]").map(function(i,e){
																	$(e).removeClass("caseExcelOver");
																})
															}
														}).click( function(){
															//log(["click",this])
															if ( faireChoix ){
																var c = $(this).attr("data-excel-c");
																$("#apercuChoixCol .tableExcel .excel_case[data-excel-c="+c+"]").map(function(i,e){
																	$(e).removeClass("caseExcelOver");
																})
																
																clickColonne(c);
															}
														});
														//evalBaliseScript(reponse);
														//$('#loadingDiv').hide();
														//$('#choixFirstLigne').slideDown();
													}
												})
										}
									</script>
<<<<<<< HEAD
=======
									
>>>>>>> 02661005a422649804a221a452910f5265930706
									<?php 
								} else {
									?>
									<!--====================== STEP 1 ============== -->
									<h3>Partie 1 - Envoie du fichier</h3>
									<div>
										<form action="" method="post" enctype="multipart/form-data">
											<input type="hidden" name="startImport" value="1" />
<<<<<<< HEAD
											<input type="file" name="fichier" />
=======
											<input type="file"   name="fichier" />
>>>>>>> 02661005a422649804a221a452910f5265930706
											<input type="submit" />
										</form>
									</div>
									<br />
									<?php 
<<<<<<< HEAD
								} ?>
=======
								} 
							?>
>>>>>>> 02661005a422649804a221a452910f5265930706
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</section>
