<?php
require_once('../req/init.php');

// fix temporaire pour la temporisation de la recherche qui ne marche pas
unset($_SESSION["product_search"]);

$pageNum = 1;
$maxRows = 10;

try{
	$listAND = [];
	$listVALUE= [];
	
	require_once(__DIR__."/../req/print.php");

	if ( $_GET["format"] == "xls" or $_GET["format"] == "xlsx" ){
		$rapport = new RapportXLS( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	} else {
		$rapport = new RapportPDF( RPDF_PAGE_PORTRAIT, RPDF_SHOWCOL_ALLPAGE );
	}

	
	$titre = L("liste de produits","o");

	$listSoustitre = [];
	//$listSoustitre[] = ["En date du", date("Y-m-d") ];
	$listNomMag = [];
	
	//Construction de la recherche
	// CodeProduit
	$_GET['codeproduit'] = trim($_GET['codeproduit']);
	if( $_GET["codeproduit"] != ""){
		$_GET['codeproduit'] = preg_replace('#[^0-9a-zA-Z_\-\+\\\\]+#','',$_GET['codeproduit']);
		//A REFAIRE! TODO: Ajouter champ pour cette recherche et deux trigger(insert/update) pour set la valeur de cette nouveau champ
		$listAND[] = "(REPLACE(link_article_four.num_four, '-','') like ? OR REPLACE(link_article_four.num_four, '/','') like ? OR REPLACE(link_article_four.num_four, '_','') like ?) ";
		$listVALUE[] = "%" . $_GET['codeproduit'] . "%";
		$listVALUE[] = "%" . $_GET['codeproduit'] . "%";
		$listVALUE[] = "%" . $_GET['codeproduit'] . "%";
		$_SESSION["product_search"]["codeproduit"] = $_GET["codeproduit"];
		
		$listSoustitre[] = ["code fournisseur", $_GET["codeproduit"] ];
	}else{
		unset($_SESSION["product_search"]["codeproduit"]);
	}

	if(preg_match('#^\d+$#', $_GET["fournisseur"])){
		$listAND[] = "article.id_distributeur = ?";
		$listVALUE[] = $_GET["fournisseur"];
		$_SESSION["product_search"]["fournisseur"] = $_GET["fournisseur"];
		
		$getFour = query("select * from fournisseur where id_fournisseur = ?", [$_GET["fournisseur"],], $dbAnimoCaisseDefault);
		$listSoustitre[] = ["fournisseur", $getFour->fetch_assoc()["nom"] ];
	}else if($_GET["fournisseur"] == "NO_FOUR"){
		$listAND[] = "article.id_distributeur is null ";
	}else{
		unset($_SESSION["product_search"]["fournisseur"]);
	}

	if(preg_match('#^\d+$#', $_GET["distributeur"])){
		$listAND[] = "link_article_four.id_fournisseur = ? AND link_article_four.discontinued IS NULL ";
		$listVALUE[] = $_GET["distributeur"];
		$_SESSION["product_search"]["distributeur"] = $_GET["distributeur"];
		
		$getFour = query("select * from fournisseur where id_fournisseur = ?", [$_GET["distributeur"],], $dbAnimoCaisseDefault);
		$listSoustitre[] = ["distributeur", $getFour->fetch_assoc()["nom"] ];
	}else if($_GET["distributeur"] == "NO_DIS"){
		$listAND[] = "link_article_four.id_fournisseur is null ";
	}else{
		unset($_SESSION["product_search"]["distributeur"]);
	}

	if(preg_match('#^\d+$#', $_GET["idsearch"])){
		$listAND[] = "(article.id_article = ?)";
		$_SESSION["product_search"]["idsearch"] = $_GET["idsearch"];
		$listVALUE[] = $_GET["idsearch"];
		
		$listSoustitre[] = ["id article", $_GET["idsearch"] ];
	}else{
		unset($_SESSION["product_search"]["idsearch"]);
	}

	if( $_GET["codeupc"] ){
		$_GET["codeupc"] = preg_replace('#[^0-9,]#','',$_GET["codeupc"]);
		$_SESSION["product_search"]["codeupc"] = $_GET["codeupc"];
		
		$listUPC = explode(",",$_GET["codeupc"]);
		if ( count($listUPC) > 1 ){
			$listAND[] = "(article.PLU in (?) or article.boite_PLU in (?) or article.PLU2 in (?) or article.PLU3 in (?))";
			$listVALUE[] = $listUPC;
			$listVALUE[] = $listUPC;
			$listVALUE[] = $listUPC;
			$listVALUE[] = $listUPC;
		} else {
			$listAND[] = "(article.PLU like ? or article.boite_PLU like ? or article.PLU2 like ? or article.PLU3 like ?)";
			$listVALUE[] = '%'.$_GET["codeupc"].'%';
			$listVALUE[] = '%'.$_GET["codeupc"].'%';
			$listVALUE[] = '%'.$_GET["codeupc"].'%';
			$listVALUE[] = '%'.$_GET["codeupc"].'%';
		}
		
		$listSoustitre[] = ["code upc", $_GET["codeupc"] ];
	}else{
		unset($_SESSION["product_search"]["codeupc"]);
	}

	if($_GET["keywrd"]!=''){
		$_SESSION["product_search"]["keywrd"] = $_GET["keywrd"];
		$listKeyword = explode(' ',$_GET["keywrd"]);
		foreach($listKeyword as $keyword){
			$keyword = escapeForSqlLike(trim($keyword));
			if ( $keyword ){
				$keyword = '%'.$keyword.'%';
				$listAND[] = "(article.desc_fr like ? or article.desc_en like ? or article_desc.nom_fr like ? or article_desc.nom_en like ? or article_desc.format like ? or article_desc.couleur like ?) ";
				$listVALUE[] = $keyword;
				$listVALUE[] = $keyword;
				$listVALUE[] = $keyword;
				$listVALUE[] = $keyword;
				$listVALUE[] = $keyword;
				$listVALUE[] = $keyword;
			}
		}
		
		$listSoustitre[] = ["mot clef", $_GET["keywrd"] ];
	}else{
		unset($_SESSION["product_search"]["keywrd"]);
	}

	if( $_GET["poidmin"] != ""){
		$poids = $_GET["poidmin"];
		if ( $_GET["poidmin_unit"] == "kg" ){
			$poids = $poids * 1000;
		} elseif ( $_GET["poidmin_unit"] == "lb" ){
			$poids = $poids * 453.592;
		}
		$_SESSION["product_search"]["poidmin"] = $_GET["poidmin"];
		$listAND[] = "(article_desc.poid >= ?)";
		$listVALUE[] = round($poids,2);
		
		$listSoustitre[] = ["poids min.", $_GET["poidmin"] ];
	}else{
		unset($_SESSION["product_search"]["poidmin"]);
	}

	if( $_GET["poidmax"] != ""){
		$poids = $_GET["poidmax"];
		if ( $_GET["poidmax_unit"] == "kg" ){
			$poids = $poids * 1000;
		} elseif ( $_GET["poidmax_unit"] == "lb" ){
			$poids = $poids * 453.592;
		}
		$_SESSION["product_search"]["poidmax"] = $_GET["poidmax"];
		$listAND[] = "(article_desc.poid <= ?)";
		$listVALUE[] = round($poids,2);
		
		$listSoustitre[] = ["poids max.", $_GET["poidmax"] ];
	}else{
		unset($_SESSION["product_search"]["poidmax"]);
	}

	if($_GET["webvalue"]=="1"){ 
		$listAND[] = 'article_desc.webReady = 1';
		$listSoustitre[] = ["Article web", "" ];
	}
	if($_GET["enVedette"]=="1"){
		$listAND[] = 'article_desc.enVedette = 1';
		$listSoustitre[] = ["En vedette", "" ];
	}
	if($_GET["isIndispensable"]=="1"){
	    $listAND[] = 'article.id_article IN(SELECT id_article FROM animoetc_dashboard.article_indispensable WHERE id_magasin = ?)';
	    $listVALUE[] = $_SESSION['mag'];
		$listSoustitre[] = ["Indispensable", "" ];
	}

	if($_SESSION["mag"] != "5"){
		$mode = "inventaire";
	}else{
		$mode = "list";
	}

	if( $_GET["marque"] != ""){
		$listAND[] = "(article_desc.marque = ?)";
		$listVALUE[] = $_GET["marque"];
		$_SESSION["product_search"]["marque"] = $_GET["marque"];
		$listSoustitre[] = ["marque", $_GET["marque"] ];
	}else{
		unset($_SESSION["product_search"]["marque"]);
	}
	
	$groupby = "article.id_article";
	$having = "";

	$double_query = null;
	if( $_GET["doublons"]=="four"){
		$double_query = "SELECT GROUP_CONCAT(DISTINCT num_four) FROM link_article_four HAVING COUNT(num_four)>1 ORDER BY id_fournisseur";
	}elseif($_GET["doublons"]=="upc"){
		$double_query = "SELECT GROUP_CONCAT(DISTINCT PLU) FROM article HAVING COUNT(plu)>1";
	}
	if($double_query){
		$queryDoublon = query($double_query,[],$dbAnimoCaisseDefault);
		if($queryDoublon->num_rows === 1){
			$listDoublon = $queryDoublon->fetch_row()[0];
			if($listDoublon != ""){
				$listDoublon = "'".implode("','",explode(",",$listDoublon))."'";
				if( $_GET["doublons"]=="four"){
					$listAND[] = "link_article_four.num_four IN($listDoublon)";
				}elseif($_GET["doublons"]=="upc"){
					$listAND[] = "article.plu IN($listDoublon)";
				}
			}
		}
	}
	
	//inactif
	if ( $_GET["showInactif"] != "1" ){
		$listAND[] = "article.inactif is null";
	} else {
		$listSoustitre[] = ["affiché inactif", "" ];
	}
	
	//Default
	$noFilter = false;
	if (sizeof($listAND) == 0){ 
		$listAND[] = "1=1";
		unset($_SESSION["product_search"]);
		$noFilter = true;
	}
		
	if($_GET['order']!=''){ $order = $dbAnimoCaisse->real_escape_string($_GET['order']); }else{$order = 'nomArticle';}
	if($_GET['sens']==''){ $sens = 'desc';}else{ $sens = $dbAnimoCaisse->real_escape_string($_GET['sens']);}

	
	
	
	$listEnteteColonne = [
		[ 
		    ["text"=>L('id',"u"),"width"=>15,"align"=>"R"],
		    ['text'=>L('UPC'),'width'=>25,'align'=>'L'],
		    ['text'=>L('nom',"o"),'width'=>60,'align'=>'L'],
		    ['text'=>L('Distributeurs(s)'),'width'=>45,'align'=>'L'],
		    ['text'=>L('prix'),'width'=>15,'align'=>'R'],
		    ['text'=>L('stock'),'width'=>15,'align'=>'R'],
		    ['text'=>L('stock min.','o'),'width'=>15,'align'=>'R'],
		]
	];
	
	
	
	
	
	

	$caisse_db = getInfoMag('caisse_db');

	$and = implode(' and ', $listAND);
	
	$enonce = "select article.id_article
				from article
					left join link_article_four on link_article_four.id_article = article.id_article
					left join article_desc on (article_desc.id_article = article.id_article)
					left join fournisseur on fournisseur.id_fournisseur = article.id_distributeur
					left join marques ON marques.id_marques = article_desc.marque
				where $and
			group by $groupby $having";
			
	if(preg_match('#^\d+$#',$_GET['limit'])){ $maxRows = $_GET['limit'];}
	if(preg_match('#^\d+$#',$_GET['pageNum'])){ $pageNum = $_GET['pageNum'];}
	
	$query_limit = query($enonce, $listVALUE, $dbAnimoCaisseDefault);
	$nbTotalRows = $query_limit->num_rows;
	$nbTotalPage = ceil($nbTotalRows / $maxRows);
	$startRow = ($pageNum-1) * $maxRows;

	if ($pageNum>$nbTotalPage){$pageNum = $nbTotalPage;}
	
	
	$productQuery = "select article.id_article `id_article`, article.id_distributeur, marques.nom as brand, article.desc_{$_SESSION['lang']} as nomArticle,
							article_desc.nom_{$_SESSION['lang']} as nomArticledesc, article_categorie.label_{$_SESSION['lang']} as nomCategorie, stock,
							article_desc.webReady, article.PLU, article.PLU2, article.PLU3, img, fournisseur.nom as nom_four, poid, couleurs_{$_SESSION['lang']} as couleurs,
							tailles_{$_SESSION['lang']} as tailles, types_{$_SESSION['lang']} as types, recettes_{$_SESSION['lang']} as recettes, produit_qc,
							ifnull((
									select min(prixMag.prix)
									from $caisse_db.prix `prixMag`
									where prixMag.id_article = article.id_article
										and prixMag.qte = 1
										and (prixMag.date_debut is null or prixMag.date_debut <= now())
										and (prixMag.date_fin is null or prixMag.date_fin >= now())
									),0) `min_prix`,
							ifnull(articleMag.hold_min,0) `hold_min`, articleMag.stock `stock`
					   from article
							left join $caisse_db.article `articleMag` on (articleMag.id_article = article.id_article)
							left join link_article_four on link_article_four.id_article = article.id_article
							left join article_desc on (article_desc.id_article = article.id_article)
							left join article_photo on (article_photo.id_article = article.id_article)
							left join fournisseur on fournisseur.id_fournisseur = article.id_distributeur
							left join article_categorie_link on(article_categorie_link.id_article = article.id_article)
							left join article_categorie using(id_categorie)
							left join marques on marques.id_marques = article_desc.marque
							left join couleurs using(id_couleurs)
							left join tailles using(id_tailles)
							left join types using(id_types)
							left join recettes using(id_recettes)
					  where $and
				   group by $groupby
					$having
				   order by $order $sens
					  limit $startRow, $maxRows";
	
	$getProducts = query($productQuery, $listVALUE, $dbAnimoCaisseDefault);

	$listSoustitre[] = ["page", "#" . $pageNum . sprintf(" (Ligne %d à %d sur %d)",$startRow+1,$startRow+$getProducts->num_rows,$nbTotalRows) ];
	
	$rapport->debutSection3($titre,$listSoustitre,$listEnteteColonne);
	$rapport->setInfoCols(-1);
	
	while($rowProducts = $getProducts->fetch_assoc()){
		$getFourCost = query("select fournisseur.*, link_article_four.num_four, link_article_four.prix_coutant, link_article_four.prix_caisse, link_article_four.id_link_article_four, link_article_four.discontinued
										from fournisseur
										join link_article_four using(id_fournisseur)
										where link_article_four.id_article = ?
										and link_article_four.discontinued is null
										order by prix_coutant asc",[$rowProducts["id_article"]],$dbAnimoCaisseDefault);
		$rowFourCost = $getFourCost->fetch_assoc();

		$groupe=[]; $allGroupe=[];
		$getGroupe = query("SELECT groupe.* FROM groupe
									JOIN link_article_groupe USING(id_groupe)
									WHERE id_groupe IN (select lag.id_groupe from link_article_groupe `lag` where lag.id_article = ? AND lag.inactif IS NULL )
									AND groupe.inactif IS NULL
									group by id_groupe",[$rowProducts['id_article']],$dbAnimoCaisseDefault);
		while($rowGroupe = $getGroupe->fetch_assoc()){
			$allGroupe[] = $rowGroupe['type'];
			if($rowGroupe['type']!='' && $rowGroupe['type']!='fidelite'){
				$type = explode(",", $rowGroupe['type']);
				foreach ($type as $typevalue){
					if($typevalue == 'poid'){
						$groupe[] = setPoid($rowProducts[$typevalue]);
					}else{
						$groupe[] = $rowProducts[$typevalue];
					}
				}
			}
		}

		$nomCaisse = $rowProducts['nomArticle'];
		$nomWeb = $rowProducts['nomArticledesc'];
        
		
		//////////////////////////
		$listChamps = [];
		
		//id article
		$listChamps[] = $rowProducts['id_article'];
		
		//UPC
		$listPLU = []; 
		if ($rowProducts["PLU"]) $listPLU[] = $rowProducts["PLU"]; 
		if ($rowProducts["PLU2"]) $listPLU[] = $rowProducts["PLU2"]; 
		if ($rowProducts["PLU3"]) $listPLU[] = $rowProducts["PLU3"];
		$listChamps[] = implode("\n",$listPLU);
		
		//nom
		$listChamps[] = $nomCaisse;
		
		//Distributeur(s)
		$listDistributeur = [];
		$getFourCost->data_seek(0); 
		while($rowFourCost = $getFourCost->fetch_assoc()){
			if($rowProducts["min_prix"] != 0){
				$pourc = round(($rowProducts["min_prix"] - $rowFourCost["prix_coutant"])/$rowProducts["min_prix"]*100,2);
			} else {
				$pourc = 0;
			}
			
			$listDistributeur[] = $rowFourCost["nom"] . " - " . $rowFourCost["num_four"] . " - " . formatPrix($rowFourCost["prix_coutant"]);
		}
		$listChamps[] = implode("\n",$listDistributeur);
		
		//prix
		$listChamps[] = ($rowProducts['min_prix'])?formatPrix($rowProducts['min_prix']):"N/A";
		
		//stock
		$listChamps[] = $rowProducts['stock'];
		
		//stock min
		$listChamps[] = $rowProducts['hold_min'];
		
		
		$rapport->writeLigneRapport3wrap( $listChamps );
	}
	
	$rapport->Output( formatFileName($titre).'.pdf', 'I');
	
	die("");
} catch( Exception $e ){
	if ( INDEV ){
		wisePrintStack($e);
	} else {
		msg_output("Erreur durant l'exécution de votre requête.");
	}
}
?>