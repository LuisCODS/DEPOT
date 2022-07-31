//  ____________________________________________________________________
//                      FILM SCRIPT
// ____________________________________________________________________
//  PORTÉE GLOBAL 
	//var filmController ='../../controller/film.php';


// var polystar = $('#btlOpenPreview');

// $polystar.on('mouseenter', function(){
// 	$polystar.get(0).play();
// })
//========================================================================
// EDITER: prend le click du bouton Enregistrer dans form formEditer au edite.php
// ========================================================================
// $('#btnEnregistrer').click(()=>    
// {		
// 		//alert("Teste");
// 		//get all form modal inputs  
// 		var champs   = $("#formEditer").serialize();
// 		var action = 'action=update';
// 		//alert(champs);

// 		$.ajax({
// 			method: 'POST', 
// 			url:filmController,
// 			data: action+'&'+champs
// 			//CALLBACK: Si update a été fait, msg = 1
// 			}).done((msg)=>	{
// 			//var reponse = (msg == 1) ? "Edité avec sucess!" : msg;
// 			//alert(msg);
   
// 		});
	
// }); 

//========================================================================
// Things that should be done every time a page loads
//========================================================================
//$(()=>{

	//alert("TEste");
	//insert();


//});

//========================================================================
// BOUTON EDITER: open a modal windos
// Ce bouton est recuperé par la classe (btnEditer), dans le button Editer
//de la page lister.php.
//========================================================================
/*$('.btnEditer').click(function()
{    
	//alert("Tetse");
	$('.modalEditer').modal("show");	

	//Recuperer l'objet qui est attaché au attribut du bouton edit, dans lister.php
	var obj = JSON.parse($(this).attr("obj") );

	//Remplis les champs du form modal avec les infos du film à editer
	$("#titre").val(obj.titre);
	$("#prix").val(obj.prix);		
	$("#categorie").val(obj.categorie);	
	$("#realisateur").val(obj.realisateur);
	$("#description").val(obj.description);
	//$("#pochette").val(obj.pochette);
});*/


//========================================================================
// Cache les elements
//==================================================================
// function hideAll()
// {
// 	document.getElementById("FormSup").style.visibility = "hidden";
// 	document.getElementById("FormEnr").style.visibility = "hidden";
// 	document.getElementById("FormModi").style.visibility = "hidden";
// 	document.getElementById("nouvMembre").style.visibility = "hidden";
// 	document.getElementById("membreActif").style.visibility = "hidden";
// }

//========================================================================
// CREATE
// ========================================================================
// $('#btnEnregistrerFormCreate').click(()=>    
// {		

// 		//get all form modal inputs  
// 		var champs   = $("#formModalEdit").serialize();
// 		var action = 'action=update';
// 		//console.log(action); //to test!

// 		$.ajax({
// 			method: "POST", 
// 			url:"../../controller/film.php",
// 			data: action+'&'+champs
// 			//CALLBACK: Si l'insertion ou update a été fait, msg = 1
// 			}).done((msg)=>	{
// 			var reponse = (msg == 1) ? "Enregistré avec sucess!" : msg;
// 			//console.log(msg); 

// 			//Windos showup	
// 			$.confirm({
// 				title: 'Attention!',
// 				content: reponse,
// 				buttons: {
// 					Ok: ()=>{					
// 						 $('.modalEditer').modal('toggle');//close modal 
// 					}				
// 				}
// 			});		   
// 		});
	
// }); 




