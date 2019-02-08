
var racineDOM = null; 
var tblJsonSize = tblEmployesJSON.length;

function chargerXML(){
	$.ajax({
		url:"donnes/employe.xml",
		type:"GET",
		dataType:"xml"
	}).done (function(xmlDocument){
			//alert(racineDOM); si dataType:"text"
			racineDOM = xmlDocument; //racineDOM desormais pointe vers la racine du DOM
			console.log(racineDOM);
	}).fail (function(){
			alert("ERREUR");
	});
}

// <!-- ================================= XML =====================================-->

function listerXML()
{
	var numEmploye, nom, prenom, sexe;
	var tabEmployes = racineDOM.getElementsByTagName("unEmploye");
	var taille = tabEmployes.length;
		
	var reponseHTML = "<table table border=1 class="+"table"+">";
		reponseHTML+="<caption>Liste tout en XML</caption>";
		reponseHTML+="<tr><th>NUMERO EMPLOYE</th><th>NOM</th><th>PRENOM</th><th>SEXE</th></tr>";
		
	for (var i = 0; i < taille; i++)
	{
		numEmploye=tabEmployes[i].getElementsByTagName("numEmp")[0].innerHTML;
		nom=tabEmployes[i].getElementsByTagName("Nom")[0].firstChild.nodeValue;
		prenom=tabEmployes[i].getElementsByTagName("Prenom")[0].firstChild.nodeValue;
		sexe=tabEmployes[i].getElementsByTagName("sexe")[0].firstChild.nodeValue;
		
		reponseHTML+="<tr>";
		reponseHTML+="<td>"+numEmploye+"</td><td>"+nom+"</td><td>"+prenom+"</td><td>"+sexe+"</td>";
		reponseHTML+="</tr>";
	}
	$('#contenu').html(reponseHTML);
	$('#fenetre').toggle();
}

function listerParSexeXML()
{
	var numEmploye, nom, prenom, sexe;
	var tabEmployes = racineDOM.getElementsByTagName("unEmploye");
	var taille = tabEmployes.length;
	var rep="";
	var formValue = document.getElementById('sexEmploye').value;
	var sexComp="";

	var reponseHTML = "<table table border=1 class="+"table"+">";
		reponseHTML+="<caption>lister Par Sexe XML</caption>";
		reponseHTML+="<tr><th>NUMERO EMPLOYE</th><th>NOM</th><th>PRENOM</th><th>SEXE</th></tr>";
		
	for (var i = 0; i < taille; i++)
	{
		sexComp = tabEmployes[i].getElementsByTagName("sexe")[0].firstChild.nodeValue
		if(formValue == sexComp )
		{
			numEmploye=tabEmployes[i].getElementsByTagName("numEmp")[0].innerHTML;
			nom=tabEmployes[i].getElementsByTagName("Nom")[0].firstChild.nodeValue;
			prenom=tabEmployes[i].getElementsByTagName("Prenom")[0].firstChild.nodeValue;
			sexe=tabEmployes[i].getElementsByTagName("sexe")[0].firstChild.nodeValue;
			
			reponseHTML+="<tr>";
			reponseHTML+="<td>"+numEmploye+"</td><td>"+nom+"</td><td>"+prenom+"</td><td>"+sexe+"</td>";
			reponseHTML+="</tr>";

		}
	}
	$('#contenu').html(reponseHTML);
	$('#fenetre').toggle();
}

function Rechercher()
{
	var numEmploye, nom, prenom, sexe;
	var tabEmployes = racineDOM.getElementsByTagName("unEmploye");
	var taille = tabEmployes.length;
	var rep="";
	var formValue = document.getElementById('rechercher').value;
	var codeEmp="";
	
	var reponseHTML = "<table table border=1 class="+"table"+">";
		reponseHTML+="<caption>Employes trouve en XML</caption>";
		reponseHTML+="<tr><th>NUMERO EMPLOYE</th><th>NOM</th><th>PRENOM</th><th>SEXE</th></tr>";
		
	for (var i = 0; i < taille; i++)
	{
		codeEmp = tabEmployes[i].getElementsByTagName("numEmp")[0].firstChild.nodeValue
		if(formValue == codeEmp )
		{
			numEmploye=tabEmployes[i].getElementsByTagName("numEmp")[0].innerHTML;
			nom=tabEmployes[i].getElementsByTagName("Nom")[0].firstChild.nodeValue;
			prenom=tabEmployes[i].getElementsByTagName("Prenom")[0].firstChild.nodeValue;
			sexe=tabEmployes[i].getElementsByTagName("sexe")[0].firstChild.nodeValue;
			
			reponseHTML+="<tr>";
			reponseHTML+="<td>"+numEmploye+"</td><td>"+nom+"</td><td>"+prenom+"</td><td>"+sexe+"</td>";
			reponseHTML+="</tr>";
			
		}
	}
	$('#contenu').html(reponseHTML);
	$('#fenetre').toggle();
}









// <!-- ================================= JSON =====================================-->

function listerJSON()
{	
	var reponseHTML = "<table table border=1 class="+"table"+">";
		reponseHTML+="<caption>Liste tout en JSON</caption>";
		reponseHTML+="<tr><th>numEmploye</th><th>nom</th><th>prenom</th><th>Sexe</th></tr>";
		
	for (var i = 0; i < tblJsonSize; i++)
	{			
		reponseHTML+="<tr>";
		reponseHTML+="<td>"+tblEmployesJSON[i].numEmploye+"</td><td>"+tblEmployesJSON[i].nom+"</td><td>"+tblEmployesJSON[i].prenom+"</td><td>"+tblEmployesJSON[i].sexe+"</td>";
		reponseHTML+="</tr>";
	}
	// on utilise .html à la place de .text car il doit interpreter les tags html
	$('#contenu').html(reponseHTML);
	$('#fenetre').toggle();
}

function listerParSexe()
{	
	var reponseHTML = "<table table border=1 class="+"table"+">";
		reponseHTML+="<caption>Lister par sexe</caption>";
		reponseHTML+="<tr><th>numEmploye</th><th>nom</th><th>prenom</th><th>Sexe</th></tr>";
	var genre = document.getElementById('sexe').value;
	// var res = genre.toLowerCase();
	
	// rattrapage des données par les indices...
	for (var i = 0; i < tblJsonSize; i++)
	for (var i = 0; i < tblJsonSize; i++)
	{	
		if(tblEmployesJSON[i].sexe == genre)
		{
			reponseHTML+="<tr>";
			reponseHTML+="<td>"+tblEmployesJSON[i].numEmploye+"</td><td>"+tblEmployesJSON[i].nom+"</td><td>"+tblEmployesJSON[i].prenom+"</td><td>"+tblEmployesJSON[i].sexe+"</td>";
			reponseHTML+="</tr>";
		}
		// else{
			// document.getElementById('sexe').value = ''"
			// alert("Entrée invalide! Entrez soit M ou F ");
		// }

	}
	// on utilise .html à la place de .text car il doit interpreter les tags html
	$('#contenu').html(reponseHTML);
	$('#fenetre').toggle();
}

function listerParcode()
{	
	var reponseHTML = "<table table border=1 class="+"table"+">";
		reponseHTML+="<caption>Employes trouve</caption>";
		reponseHTML+="<tr><th>numEmploye</th><th>nom</th><th>prenom</th><th>Sexe</th></tr>";
	var code = document.getElementById('code').value;
	
	for (var i = 0; i < tblJsonSize; i++)
	for (var i = 0; i < tblJsonSize; i++)
	{	
		if(tblEmployesJSON[i].numEmploye == code)
		{
			reponseHTML+="<tr>";
			reponseHTML+="<td>"+tblEmployesJSON[i].numEmploye+"</td><td>"+tblEmployesJSON[i].nom+"</td><td>"+tblEmployesJSON[i].prenom+"</td><td>"+tblEmployesJSON[i].sexe+"</td>";
			reponseHTML+="</tr>";
		}

	}
	$('#contenu').html(reponseHTML);
	$('#fenetre').toggle();
}







