//  _____________________________________________________________________
//  PAGE QUI FOURNIE LES METHODES DE SUPPORT AU FICHIER moduleScript.jS
// _____________________________________________________________________




// Methode qui retourne l'ensemble des entités.
function lister(txtInput)
{
	//Set la valeur à recuperer par(extract($_POST);) au controlleur
	var actionType = 'action=getUtilisateur';
	//La valeur contenant dans txtInput sera recuperée  
	//...par (extract($_POST);) dans le controlleur .
	var champs  = "txtInput="+txtInput;

	//REQUISITION asynchrone 
	$.ajax({
		method:'POST', 
		url: UserController,
		data: actionType+'&'+champs
		//CALLBACK: un array d'entité en json.
	}).done((jsonData)=>{			
		//  REQUISITION asynchrone
		$.ajax({
			method:'POST', 
			url: 'template/table-utilisateur.php',
			//le callback jsonData est envoyée par la variable obj
			data: "obj="+jsonData
		//CALLBACK: tout le contenu du fichier table-utilisateur.php	
		}).done((template)=>{
			//Charge le template, provenant du callback, dans la div 
			//... listTemplate avec son id,  dans (index.php) du utilisateur.
			$("#listTemplate").html(template);
			// declenche dès que le button btnEditer(table-utilisateur.php) est appuyé
			//...ele é pego pela class.
			$('.btnEditer').click(function() 
			{
				//Open the modal windows
				$('.ModalCadastro').modal("show");				 	
				//convert en json l'objet du button
				var obj = JSON.parse($(this).attr("obj") );
				//Show object propertys on form input
				$("#Utilisateur_ID").val(obj.Utilisateur_ID);
				$("#UtilisateurName").val(obj.UtilisateurName);	
				$("#UtilisateurNickName").val(obj.UtilisateurNickName);	
				$("#UtilisateurMDP").val(obj.UtilisateurMDP);	
				$("#UtilisateurEmail").val(obj.UtilisateurEmail);	
				$("#Profil_ID").val(obj.Profil_ID);
				//Show le boutton Supprimer par son ID: btnSupprimer
				//on javascript sintax:  document.getElementById("btnSupprimer").hidden = false;
				$("#btnSupprimer").css("display", "block");	
				//Ajoute la valeur du title h5 du modal
				$("#ModalTitle").html("Editer Utilisateur");		
			});

		})
	});
}

//========================================================================
// Methode qui valide if textbox input is empty.Return true/false.
// If false, set a new class for textbox input.
//========================================================================
function validerEntreeVide()
{
	var reponse = "";

	//pour chaque INPUT qui a la class "estVide"
	$(".estVide").each(function()
	{
		//If input isen't empty
		if ($(this).val() != "" )
		{
	    	reponse = true;
		}else{
			$(this).addClass("is-invalid");
			reponse = false;
		}	
	});
	// Prevent registration form if textbox is empty
	if ($(".is-invalid").length>0){
		reponse = false;
	}
	return reponse;
}

//========================================================================
// Methode qui valide les textbox. Si le champs est vide, la couleur 
// ...autours du textbox est à rouge. Outrement, il est à vert.
//========================================================================
function validerFormInputs()
{
	//For eatch INPUT TEXTBOX with class = estVide
	$(".estVide").each(function()
	{
		//lorsque l'utilisateur relâche une clé
		$(this).keyup(function()
		{
			//Si textbox is empty
			if ($(this).val() == "" )
			 {
			 	//switch class...
				$(this).removeClass("is-valid");
				$(this).addClass("is-invalid");								
			}	
		});
		//When un option is selected
		$( ".estVide" ).change(function() {
			$(this).removeClass("is-invalid");
		  	$(this).addClass("is-valid");	
		});
	});


}

//========================================================================
// Method that changes color of form input.
// It's Called in each input field of form:
// When input field is empty the red color is shown, 
//... once the field is fill, green color appear.
//========================================================================
function isItEmpty(texte)
{
	if($(texte).val().length >= 0)
	{
		 //console.log($(texte).val().length);//to test
		 $(texte).removeClass("is-invalid"); 
		 $(texte).addClass("is-valid");
	}
}


//Methode qui charge les Profil dans la zone select dans la page Utilisateur.
function listerProfil()
{
	var action = 'action=getProfil';
	var champs  = "txtInput=";

	//REQUISITION asynchrone 
	$.ajax({
		method:'POST', 
		url: profilController,
		data: action+'&'+champs
		//CALLBACK: un array de profil en format json.
	}).done((jsonData)=>{			
		//  REQUISITION asynchrone
		$.ajax({
			method:'POST', 
			url: 'template/list-profil.php',
			//le callback jsonData est envoyée par la variable obj
			data: "obj="+jsonData
		//CALLBACK: tout le contenu du fichier table-profil.php	
		}).done((template)=>{
			//Charge le template, provenant du callback, dans la div 
			//... listTemplate avec son id,  dans (index.php) du profil.
			$("#Profil_ID").html(template);
		});

	});	
}