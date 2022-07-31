// //  ____________________________________________________________________
// //  SCRIPT MEMBRE
// // ____________________________________________________________________



function validerForm( )
{
	//GET FORM INPUTS
	var motUm   = formCreate.MDP_membre.value;
	var motDeux = formCreate.MDP_membreConfirm.value;
	var nom     = formCreate.nom.value;
	var courriel= formCreate.courriel.value;
	var prenom  = formCreate.prenom.value;
	//var nomRegExp = new RegExp("/^[a-zA-Z-' ]*$/");
	//var nomRegExp = new RegExp("/^[a-zA-Z\s]+$/");
	//$regex = "/^[a-zA-Z\s]+$/";


	// USER NAME VALIDATION

	// if (!nomRegExp.test(nom) ) 
	// {	 
	//  	//$output = "<span style='color:red'> x </span> ";
	// 	//document.getElementById("alertMsg").style.display='block';
	// 	document.getElementById("erreurNom").innerHTML="Champs (nom): seuls les lettres et les espaces sont autorisés!";
	// 	return false;
	// }

	//PASSWORD LIMITE

	if ( motUm.length != 4   &&  motUm != "" ) 
	{
		document.getElementById("erreurPassword").innerHTML="Le mot de passe doit contenir 4 chiffres!";
		return false;
	}else{
		document.getElementById("erreurPassword").innerHTML="";
	}	

	//PASSWORD COMPARE

	if (motUm != motDeux &&  motDeux != "" ) 
	{
		document.getElementById("erreurPasswordConfirm").innerHTML="Mot de passe differents!";
		return false;

	}else{
		document.getElementById("erreurPasswordConfirm").innerHTML="";
	}	



}//fin if





// function validerEmail( )
// {
// 	var email = document.getElementById("courriel").value;

// 	if(!preg_match('/^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$/', $email))
//     {
// 		//$mess_erreur .= "Courriel: Doit être comme l'example -> monadresseemail@service.com.br />";
// 		document.getElementById("alertMsg").style.display='block';
// 		document.getElementById("msnErreur").innerHTML="Courriel doit être valide!";
// 		return false;
// 	}else{
// 		document.getElementById("alertMsg").style.display='none';
// 	}	

// }

// function validerEmail(email){


// 	//pega valor do id fornecido no form
// 	var courriel = document.getElementById('email').value;
// 	alert(courriel);
// 	// var courrielRegExp=new RegExp("/^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$/ ");
// 	// if(courriel!="" && courrielRegExp.test(courriel))
// 	// 	return true;
// 	// return false;
// }

