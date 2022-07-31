<?php
// Ce fichier traite la requête ajax pour verifier si le UPC choisit par le user existe deja.
require_once('../req/init.php');

ob_start();

$DEBUG_DB = 0;

try{
	$reponse = ["status"=>"unknow"];
	
    if(isset($_POST["action"]) && $_POST["action"] == "checkPluIfExists" ){  
        
    	// ________________________ GESTION UPC _________________________
    
    	$_POST["PLU"] = preg_replace('#[^0-9]+#','',$_POST["PLU"]);
    	$_POST["PLU2"] = preg_replace('#[^0-9]+#','',$_POST["PLU2"]);
    	$_POST["PLU3"] = preg_replace('#[^0-9]+#','',$_POST["PLU3"]);
    
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
        
    	if ( sizeof($listPLU) > 0 ){
    		if ( preg_match('#^[0-9]+$#',$_POST["id_article"]) ){
    		    //CAS UPDATE: Check all UPC sauf l'article lui meme
    			$resultTest = query("select * from article where (PLU in (?) or PLU2 in (?) or PLU3 in (?)) and id_article != ?",
    			[$listPLU,$listPLU,$listPLU,$_POST["id_article"]],$dbAnimoCaisseDefault);
    		} else {
    		    //CAS CREATE: Check all UPC
    			$resultTest = query("select * from article where PLU in (?) or PLU2 in (?) or PLU3 in (?)",
    			[$listPLU,$listPLU,$listPLU],$dbAnimoCaisseDefault);
    		}
    		//SI  erreur sur la query ou que la query return un résultat (UPC existe déjà)
    		if ( !$resultTest or $resultTest->num_rows > 0 ){
		    	$resultRow = $resultTest->fetch_assoc();
                if (in_array( $resultRow["PLU"], $listPLU))
                    $reponse["upcExiste"] = $reponse["upcExiste"]." ".$resultRow["PLU"];
                if (in_array( $resultRow["PLU2"], $listPLU))
                    $reponse["upcExiste"] = $reponse["upcExiste"]." ".$resultRow["PLU2"];
                if (in_array( $resultRow["PLU3"], $listPLU))
                    $reponse["upcExiste"] = $reponse["upcExiste"]." ".$resultRow["PLU3"];
                    
		    	$reponse["status"] = "success";
		    	$reponse["isValid"]  = false; 
    		} else {
		    	$reponse["status"] = "success";
    		    $reponse["isValid"]  = true;
    		}
    	} else {
		    $reponse["status"] = "success";
    	    $reponse["isValid"]  = true;
    	}
    }
    
	echo json_encode($reponse);

	
} catch( Exception $e ){
	if ( INDEV ){
		wisePrintStack($e);
	} else {
		msg_output("Erreur durant l'exécution de votre requête.");
	}
	$reponse = ["status"=>"error","html"=>ob_get_clean(),"message"=>"Erreur durant l'exécution de votre requête.","txt_error"=>$e->getMessage()];
	echo json_encode($reponse);
}
?>

