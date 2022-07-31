//  _____________________________________________________________________
//  PAGE  RESPONSABLE POUR ELABORER LES REQUISITIONS AJAX ASYNCHRONES.
// _____________________________________________________________________

//  PORTÉE GLOBAL 
var UserController ='../../controller/utilisateur.php';
//necessaire pour charger le select dans la page Utilisateur.
var profilController ='../../controller/profil.php';
var strRecherchee = "";







//========================================================================
// Things that should be done every time a page loads
//========================================================================
$(()=>{
	lister(strRecherchee); //(moduleFunction.js)
	validerFormInputs();//(moduleFunction.js)
	listerProfil(); //Charge les option dans la selection de la page Utilisateur(moduleFunction.js)
});

//========================================================================
//  ZONE DE RECHERCHE: declenchée dès qu'il y a une entrée par l'user.
//========================================================================
$('#txtInput').keyup(()=>    
{
	//Get textBox value by ID.
	strRecherchee = $('#txtInput').val();
	// alert(strRecherchee); //To test
	lister(strRecherchee);
});

//========================================================================
// BOUTON (+) : One a window to add a new Profil member.
//========================================================================
$('#btnPlus').click(()=>    
{
	//Open the modal windows
	$('.ModalCadastro').modal("show");	

	//Cache le boutton Supprimer par son ID: btnSupprimer
	//on javascript sintax:  document.getElementById("btnSupprimer").hidden = true;
	$("#btnSupprimer").css("display", "none");
	//Set title h5 au modal
	$("#ModalTitle").html("Nouveau Utilisateur");

	//Clean all input form
	$("#Utilisateur_ID").val("");
	$("#UtilisateurName").val("");
	$("#UtilisateurNickName").val("");
	$("#UtilisateurMDP").val("");
	$("#UtilisateurEmail").val("");
	$("#ProfilID").val("");
	
});

//========================================================================
// BOUTON AJOUTER: Cette fonction est declenchée dès que le button 
//  ... btnAjouter du fichier (ajouter.php) du modal est appuyé.
// ========================================================================
$('#btnAjouter').click(()=>    
{		
	// Si true
	if( validerEntreeVide() )
	{
		//console.log(validerEntreeVide()); //to test

		//get all form inputs  
		var champs   = $("#formAjouter").serialize();
		//Get ID from profil
		var Utilisateur_ID = $("#Utilisateur_ID").val();	
		//Si le champ est vide, action = insert, sinon action = update
		var actionType = (Utilisateur_ID == "") ?'action=insert' : 'action=update';
		//console.log(actionType); //to test!

		// REQUISITION asynchrone 
		$.ajax({
			method: "POST", 
			url:UserController,
			data: actionType+'&'+champs
			//CALLBACK: Si l'insertion ou update a été fait, msg = 1
			}).done((msg)=>	{
			var reponse = (msg == 1) ? "Enregistré avec sucess!" : msg;
			//console.log(msg); //to test

			//Windos showup	
			$.confirm({
				title: 'Attention!',
				content: reponse,
				buttons: {
					Ok: ()=>{					
						$('#ModalCadastro').modal('toggle');//close modal
				         // Recharge la page actuelle à partir du 
				         //... serveur, sans utiliser le cache.		
						location.reload(true);
						// lister(strRecherchee);						 
					}				
				}
			});		   
		});
	}
	
}); 

//========================================================================
//   Cette fonction est declenchée dès que le button btnSupprimer
//   ...( from ajouter.php) du modal est appuyé.
//========================================================================
$('#btnSupprimer').click(()=>    
{	
	//get all inputs from form (Utilisateur_ID et ProfilNom )
	var champs   = $("#formAjouter").serialize();
	var actionType = 'action=delete';

	// REQUISITION asynchrone 
	$.ajax({
		method: "POST", 
		url:UserController,
		data: actionType+'&'+champs
		
		}).done((callBack)=>
		{		
			var reponse = (callBack == 1) ? "Supprimé avec sucess!" : callBack;
			//Windos popup du plugin	
			$.confirm({
				title: 'Attention!',
				content: reponse,
				buttons: {
					Ok: ()=>{
				         // Recharge la page actuelle à partir du 
				         //... serveur, sans utiliser le cache.
						 location.reload(true);
					}				
				}
			});
		});
}); 


