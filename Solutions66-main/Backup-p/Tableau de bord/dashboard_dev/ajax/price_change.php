<?php
require_once('../req/init.php');

/*
if ( $_SESSION["brand"] != "animo" ){
	echo json_encode( ["status"=>"error","html"=>"403","message"=>"403","txt_error"=>"403"] );
	die();
}
*/

ob_start();

ini_set('memory_limit','256M');

$pageNum = 1;
$maxRows = 10;
$DEBUG_DB = 0;

try{
	if ((isset($_POST["form_PC"])) && ($_POST["form_PC"] == "sendok")) {
		if(count($_POST['PCcheck']) > 0){
			$mode = "accept";
			if ( $_SESSION["brand"] != "animo" and $_POST["refuse"] == "1" ){
				$mode = "refuse";
			}
			
			$repeat_i = array_fill(0,count($_POST['PCcheck']),"?");
			$enoncePC = sprintf("select * from prix_change where id_prix_change IN (".implode(",",$repeat_i).") order by prix_change.date_update asc");
			$result = query($enoncePC,array_values($_POST['PCcheck']),$dbAnimoCaisse);
			
			if($result->num_rows > 0){
				while ($rowPrixChange = $result->fetch_assoc()) {
					$arrayDB = array();
					if ($rowPrixChange['change_done'] == '') {
						if ($mode != "refuse"){
							$arrayDBprix = [];
							$arrayDBprix["qte"] = $rowPrixChange["qte"];
							$arrayDBprix["date_debut"] = $rowPrixChange["date_debut"];
							$arrayDBprix["date_fin"] = $rowPrixChange["date_fin"];
							$arrayDBprix["id_article"] = $rowPrixChange["id_article"];
							$arrayDBprix["prix"] = $rowPrixChange["prix"];
							$arrayDBprix["date_update"] = date("Y-m-d H:i:s");
	
							$enonceP = sprintf("select * from prix 
												 where qte = ? and id_article = ? 
												   and date_debut %s 
												   and date_fin %s order by date_update asc",
									(($arrayDBprix["date_debut"] != "") ? " = '" . $arrayDBprix["date_debut"] . "'" : "is null"), 
									(($arrayDBprix["date_fin"] != "") ? " = '" . $arrayDBprix["date_fin"] . "'" : "is null")
								);
							$resultPrix = query($enonceP,[$rowPrixChange["qte"],$rowPrixChange["id_article"]],$dbAnimoCaisse);
							if ($rowPrix = $resultPrix->fetch_assoc()) {
								//update
								$arrayDBprix["id_prix"] = $rowPrix["id_prix"];
								faireUpdate_i($arrayDBprix, "prix", "id_prix", $dbAnimoCaisse, $DEBUG_DB);
							} else {
								//insert
								$arrayDBprix["date_insert"] = date("Y-m-d H:i:s");
								faireInsert_i($arrayDBprix, "prix", $dbAnimoCaisse, $DEBUG_DB);
							}
						}
						$arrayDB["change_done"] = date('Y-m-d H:i:s');
					}
					$arrayDB["id_prix_change"] = $rowPrixChange['id_prix_change'];
					
					if ($mode == "refuse"){
						$arrayDB["change_refused"] = date('Y-m-d H:i:s');
					} else {
						$arrayDB["change_accepted"] = date('Y-m-d H:i:s');
					}
					faireUpdate_i($arrayDB, "prix_change", "id_prix_change", $dbAnimoCaisse, $DEBUG_DB);
				}
			}
			$json = ["status"=>"success"];
			die(json_encode($json));
		}else{
			$json = ["status"=>"no_input"];
			die(json_encode($json));
		}
	}

	/* * ******************************************************************************** ACCEPT/refuse PRICE CHANGE NO STOCK */
	if ((isset($_POST["form_PCNS"])) && ($_POST["form_PCNS"] == "sendok")) {
		
		$mode = "accept";
		if ( $_SESSION["brand"] != "animo" and $_POST["refuse"] == "1" ){
			$mode = "refuse";
		}
		
	    set_time_limit(300);
	    $enoncePC = "select * from prix_change INNER JOIN article USING(id_article) where article.stock <= 0 and change_accepted is null order by prix_change.date_update asc";
		$result = query($enoncePC,[],$dbAnimoCaisse);

		while ($rowPrixChange = $result->fetch_assoc()) {
			$arrayDB = array();
			if ($rowPrixChange['change_done'] == '') {
				if ( $mode != "refuse" ){
					$arrayDBprix = [];
					$arrayDBprix["qte"] = $rowPrixChange["qte"];
					$arrayDBprix["date_debut"] = $rowPrixChange["date_debut"];
					$arrayDBprix["date_fin"] = $rowPrixChange["date_fin"];
					$arrayDBprix["id_article"] = $rowPrixChange["id_article"];
					$arrayDBprix["prix"] = $rowPrixChange["prix"];
					$arrayDBprix["date_update"] = date("Y-m-d H:i:s");
	
					$enonceP = sprintf("select * from prix where qte = ? and id_article = ? and date_debut %s and date_fin %s order by date_update asc",
							(($arrayDBprix["date_debut"] != "") ? " = '" . $arrayDBprix["date_debut"] . "'" : "is null"), 
							(($arrayDBprix["date_fin"] != "") ? " = '" . $arrayDBprix["date_fin"] . "'" : "is null")
						);
					$resultPrix = query($enonceP,[$rowPrixChange["qte"],$rowPrixChange["id_article"]],$dbAnimoCaisse);
					if ($rowPrix = $resultPrix->fetch_assoc()) {
						//update
						$arrayDBprix["id_prix"] = $rowPrix["id_prix"];
						faireUpdate_i($arrayDBprix, "prix", "id_prix", $dbAnimoCaisse, $DEBUG_DB);
					} else {
						//insert
						$arrayDBprix["date_insert"] = date("Y-m-d H:i:s");
						faireInsert_i($arrayDBprix, "prix", $dbAnimoCaisse, $DEBUG_DB);
					}
				}
				$arrayDB["change_done"] = date('Y-m-d H:i:s');
			}
			$arrayDB["id_prix_change"] = $rowPrixChange['id_prix_change'];
			if ($mode == "refuse"){
				$arrayDB["change_refused"] = date('Y-m-d H:i:s');
			} else {
				$arrayDB["change_accepted"] = date('Y-m-d H:i:s');
			}
			faireUpdate_i($arrayDB, "prix_change", "id_prix_change", $dbAnimoCaisse, $DEBUG_DB);
		}
		$json = ["status"=>"success"];
		die(json_encode($json));
	}
	
	
	
	////////////////////////////////////
	//Default call
	$listColonneTri = ['prix_change.change_date_exp,prix_change.id_article,prix_change.id_prix_change','prix_change.change_date_exp',
						'article.PLU','article.desc_fr','prix_change.id_prix_change','prix_change.id_article'];
	if ( !in_array($_POST["order"],$listColonneTri) ){
		$_POST["order"] = $listColonneTri[0];
	}
	
	if($_POST['sens']==''){ $sens = 'asc';}else{ $sens = $_POST['sens'];}
	
	$orderByList = [];
	foreach( explode(',',$_POST['order']) as $champ ){
		$orderByList[] = $champ . " " . $sens;
	}
	$orderBy = implode(', ',array_filter($orderByList));
	
	
	$listAND = [];
	$listVALUE= [];
	if(preg_match('#^\d+$#',$_POST['limit'])){ $maxRows = $_POST['limit'];}
	if(preg_match('#^\d+$#',$_POST['pageNum'])){ $pageNum = $_POST['pageNum'];}

	if($_REQUEST["nostock"] == "1"){

	}else{
		$listAND[] = "article.stock > 0";
	}

	if (sizeof($listAND) == 0){ $listAND[] = "1=1";unset($_SESSION["product_search"]);}

	$and = implode(' and ', $listAND);
	
	$enonce = "SELECT prix_change.*, article.stock, article.PLU, article.desc_fr as ARTICLE_NOM, article_desc.poid, 
					  prix.prix as OLDPRICE, prix_change.prix as NEWPRICE 
				 FROM prix_change
					  INNER JOIN article USING(id_article)
					  left JOIN prix USING(id_article)
					  left JOIN animoetc_caisse_default.article_desc USING(id_article)
				WHERE prix_change.change_accepted IS NULL AND prix_change.change_refused is null AND $and
			 ORDER BY $orderBy";
	$query_limit = query($enonce,$listVALUE,$dbAnimoCaisse);
	$nbTotalRows = $query_limit->num_rows;
	$nbTotalPage = ceil($nbTotalRows / $maxRows);

	if ($pageNum>$nbTotalPage){$pageNum = $nbTotalPage;}
	if ( $pageNum < 1 ){
		//Aucun résultats !
		$startRow = 0;
		?>
		<tr>
			<td colspan="8" class="noresult"><?php echo L("aucun résultat") ?></td>
		</tr>
		<?php
	} else {
		$startRow = ($pageNum-1) * $maxRows;
		$resulPChange = query("$enonce LIMIT $startRow,$maxRows",$listVALUE,$dbAnimoCaisse);
		while ($rowPChange = $resulPChange->fetch_assoc()) {
			$today = new DateTime();
			$label = "";

			if($rowPChange['change_done'] !=''){
				$label = 'c-danger';
			}else{
				if(new DateTime($rowPChange['change_date_exp']) > $today->modify('+3 days')){
					$label = 'c-primary';
				}else{
					$label = 'c-warning';
				}
			}
			?>
			<tr>
				<td valign="middle" align="center" class="chk_boxes no-print">
					<div class="ui dynamic checkbox">
						<input type="checkbox" name="PCcheck[]" value="<?php echo $rowPChange['id_prix_change'];?>" />
					</div>
				</td>
				<td class="no-print"><h6 style="margin:2px 0 0 0;"><span class="label <?php echo $label; ?>"><?php echo formatDateUTF8($rowPChange['change_date_exp']);?></span></h6></td>
				<td><a href="?p=produits&mode=inventaire&upc=<?php echo $rowPChange['PLU'];?>" ><?php echo $rowPChange['PLU'];?></a></td>
				<td><?php echo (mb_strlen($rowPChange['ARTICLE_NOM']) > 95) ? mb_substr($rowPChange['ARTICLE_NOM'],0,90).'...' : $rowPChange['ARTICLE_NOM'];?></td>
				<td><?php if($rowPChange['poid'] !=''){ echo setPoid($rowPChange['poid']);}?></td>
				<td style="text-align:center"><?php echo $rowPChange['stock'];?></td>
				<td style="text-align:right"><?php echo $rowPChange['OLDPRICE']?>$</td>
				<td style="text-align:right"><?php echo $rowPChange['NEWPRICE'];?>$</td>
			</tr>
			<?php
		}
	}
	
	$reponse = ["status"=>"success","html"=>ob_get_clean()];
	$reponse["nbPage"] = $nbTotalPage;
	$reponse["currentPage"] = $pageNum;
	$reponse["nbRowsByPage"] = $maxRows;
	$reponse["nbRows"] = $getProducts->num_rows;
	$reponse["nbTotalRows"] = $nbTotalRows;
	$reponse["nbOffsetRows"] = $startRow;

	$jsonData = json_encode($reponse);
	if ( $jsonData === false ){
		echo json_last_error_msg();
	} else {
		echo $jsonData;
	}
	die();
} catch( Exception $e ){
	if ( INDEV ){
		wisePrintStack($e);
	} else {
		msg_output("Erreur durant l'exécution de votre requête.");
	}

	$reponse = ["status"=>"error","html"=>ob_get_clean(),"message"=>"Erreur durant l'exécution de votre requête.","txt_error"=>$e->getMessage()];
	echo json_encode($reponse);
	die();
}
?>