//  _____________________________________________________________________
//  PAGE  RESPONSABLE POUR ELABORER LES REQUISITIONS AJAX ASYNCHRONES.
// _____________________________________________________________________

//  PORTÉE GLOBAL 
var postController ='../../controller/post.php';
//necessaire pour charger le select dans la page Post.
var categorieController ='../../controller/categorie.php';
var strRecherchee = "";


//========================================================================
// Things that should be done every time a page loads
//========================================================================
$(()=>{
	lister(strRecherchee); //(moduleFunction.js)
	validerFormInputs();//(moduleFunction.js)
	listerCategorie(); //Charge les option dans la selection de la page Categorie(moduleFunction.js)
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
	//Cache le boutton Supprimer
	$("#btnSupprimer").css("display", "none");
	//Set title h5 au modal
	$("#ModalTitle").html("Nouveau Post");

	//Clean all input form when modal open
	$("#Post_ID").val("");
	$("#Title").val("");
	$("#Resume").val("");
	$("#Contenu").val("");
	$("#DateDebut").val("");
	$("#DateFin").val("");
	$("#Categorie_ID").val("");	
});

//========================================================================
// BOUTON AJOUTER: Cette fonction est declenchée dès que le button 
//  ... btnAjouter du fichier (ajouter.php) du modal est appuyé.
// ========================================================================
$('#btnAjouter').click(()=>    
{		
	//alert("Working");
	// Si true
	if( validerEntreeVide() )
	{
		//console.log(validerEntreeVide()); //to test

		//get all form inputs  
		var champs   = $("#formAjouter").serialize();
		//Get ID from profil
		var post_ID = $("#Post_ID").val();	
		//Si le champ est vide, action = insert, sinon action = update
		var actionType = (post_ID == "") ?'action=insert' : 'action=update';

		//console.log(actionType); //to test!

		// REQUISITION asynchrone 
		$.ajax({
			method: "POST", 
			url:postController,
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
	//get all inputs from form (post_ID et ProfilNom )
	var champs   = $("#formAjouter").serialize();
	var actionType = 'action=delete';

	// REQUISITION asynchrone 
	$.ajax({
		method: "POST", 
		url:postController,
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


