
// <-- ============================== PATIENTS ============================== -->

//OBJETS...
var Patient1  = {Dossier: 1,   Nom:"Leger",     Prenom:"Emile", 	Naissence: "26 mars 1917",     Sexe:"M"};
var Patient2  = {Dossier: 2,   Nom:"Bernard",   Prenom:"Marie", 	Naissence: "3 fevrier 1946",   Sexe:"F"};
var Patient3  = {Dossier: 3,   Nom:"Chartrand", Prenom:"Guy", 	    Naissence: "5 avril 1959",     Sexe:"M"};
var Patient4  =	{Dossier: 4,   Nom:"Cote",      Prenom:"Andre", 	Naissence: "2 septembre 1978", Sexe:"M"};
var Patient5  =	{Dossier: 5,   Nom:"Lavoie",    Prenom:"Carole", 	Naissence: "4 novembre 1973",  Sexe:"F"};
var Patient6  =	{Dossier: 6,   Nom:"Martin",    Prenom:"Diane", 	Naissence: "2 decembre 1965",  Sexe:"F"};
var Patient7  =	{Dossier: 7,   Nom:"Lacroix",   Prenom:"Pauline",  Naissence: "25 janvier 1956",  Sexe:"F"};
var Patient8  =	{Dossier: 8,   Nom:"St-Jean",   Prenom:"Arthur", 	Naissence: "7 octobre 1912",   Sexe:"M"};
var Patient9  =	{Dossier: 9,   Nom:"Bechard",   Prenom:"Marc", 	Naissence: "8 aout 1980",      Sexe:"M"};
var Patient10 =	{Dossier: 10,  Nom:"Chartrand", Prenom:"Mario",    Naissence: "23 juillet 1947",  Sexe:"M"};

//TABLEAU 
var tbl_Patients = [Patient1,
					Patient2,
					Patient3,
					Patient4,
					Patient5,
					Patient6,
					Patient7,
					Patient8,
					Patient9,
					Patient10];

// <-- =============================== ETABLISSEMENTS ============================== -->

//OBJETS...

var Etablissement1={
    Etablissement:1234,Nom:"Centre hospitalier Sud",Adresse:"1234 boul. Sud, Montreal, Qc",CodePostal:"H2M 3Y6",Telephone:"514-323-1234"};

var Etablissement2={
    Etablissement:2346,Nom:"Hopital Nord",Adresse:"7562 rue Souvenir, Noreville, Qc",CodePostal:"J4R 2Z5",Telephone:"450-222-3333"};

var Etablissement3={
    Etablissement:3980,Nom:"Hopital centre",Adresse:"4637, boul. de l'Eglise, Montreal, Qc",CodePostal:"H3J 4K8",Telephone:"514-123-5678"};

var Etablissement4={
    Etablissement:4177,Nom:"Centre hospitalier est",Adresse:"12 rue Bernard, repentigny, Qc",CodePostal:"JBR 3K5",Telephone:"450-589-2345"};

var Etablissement5={
    Etablissement:7306,Nom:"Hopital du salut",Adresse:"11 rue de la Redemtion, St-Basilie, Qc",CodePostal:"JBH 2D4",Telephone:"450-345-6789"};

var Etablissement6={
    Etablissement:8895,Nom:"Dernier recours",Adresse:"999 rue St-Pierre, Longueuil, Qc",CodePostal:"J7B 3J5",Telephone:"450-555-6741"};

//TABLEAU
var tbl_Etablissements =[Etablissement1,
						Etablissement2,
						Etablissement3,
						Etablissement4,
						Etablissement5,
						Etablissement6];

// <-- =============================== HOSPITALISATIONS ============================== -->

//OBJETS...
var Hospitalisation1={CodeEtablissement:1234, NoDossierPatient:05,DateAdmission:"14-aout-00",DateSortie:"14-aout-01",Specialite:"medicine"};
var Hospitalisation2={CodeEtablissement:1234, NoDossierPatient:10,DateAdmission:"12-dec.92",DateSortie:"12-dec.92",Specialite:"chirurgie"};
var Hospitalisation3={CodeEtablissement:2346, NoDossierPatient:08,DateAdmission:"03-mars-03",DateSortie:"05-mars-03",Specialite:"medicine"};
var Hospitalisation4={CodeEtablissement:2346, NoDossierPatient:01,DateAdmission:"11-nov.-97",DateSortie:"12-nov.-97",Specialite:"orthopedie"};
var Hospitalisation5={CodeEtablissement:2346, NoDossierPatient:03,DateAdmission:"12-avr.-95",DateSortie:"30-avr.-95",Specialite:"medicine"};
var Hospitalisation6={CodeEtablissement:3980, NoDossierPatient:05,DateAdmission:"14-fev.-00",DateSortie:"14-mars-00",Specialite:"medicine"};
var Hospitalisation7={CodeEtablissement:3980, NoDossierPatient:05,DateAdmission:"01-jan.-01",DateSortie:"01-fev.-01",Specialite:"medicine"};
var Hospitalisation8={CodeEtablissement:3980, NoDossierPatient:09,DateAdmission:"03-avr.-95",DateSortie:"08-avr.-95",Specialite:"orthopedie"};
var Hospitalisation9={CodeEtablissement:3980, NoDossierPatient:07,DateAdmission:"27-nov.-99",DateSortie:"04-dec.-99",Specialite:"chirurgie"};
var Hospitalisation10={CodeEtablissement:3980,NoDossierPatient:10,DateAdmission:"28-avr.-97",DateSortie:"05-mai.-97",Specialite:"chirurgie"};
var Hospitalisation11={CodeEtablissement:4177,NoDossierPatient:03,DateAdmission:"03-aout-01",DateSortie:"05-dec.-01",Specialite:"medicine"};
var Hospitalisation12={CodeEtablissement:4177,NoDossierPatient:03,DateAdmission:"02-fev.-02",DateSortie:"23-fev.-02",Specialite:"orthopedie"};
var Hospitalisation13={CodeEtablissement:7306,NoDossierPatient:02,DateAdmission:"23-mai-98",DateSortie:"27-mai-98",Specialite:"orthopedie"};

//TABLEAU
var tbl_Hospitalisations =[	Hospitalisation1,
							Hospitalisation2,
							Hospitalisation3,
							Hospitalisation4,
							Hospitalisation5,
							Hospitalisation6,
							Hospitalisation7,
							Hospitalisation8,
							Hospitalisation9,
							Hospitalisation10,
							Hospitalisation11,
							Hospitalisation12,
							Hospitalisation13];
	
					
	var tbl_Hopital = new Array();
        tbl_Hopital["Patiens"]          = tbl_Patients;
        tbl_Hopital["Etablissements"]   = tbl_Etablissements;
        tbl_Hopital["Hospitalisations"] = tbl_Hospitalisations;				
							
							
// <-- =================================  (FUNCTIONS) ================================= -->

function initialize()
{
	hiddenDiv()
}

//Cache certains elements pour chaque click sur le Menu
 function  hiddenDiv()
 {
	document.getElementById("divColAffiche").style.visibility = "hidden";
	document.getElementById("divColAfficheLeft").style.visibility = "hidden";
	document.getElementById("selectByDossier").style.visibility = "hidden";
	document.getElementById("selectByEtablissement").style.visibility = "hidden";
	document.getElementById("tag_p_Title").style.visibility = "hidden";
	document.getElementById("specialite").style.visibility = "hidden";
	document.getElementById("msn-Alert").style.visibility = "hidden";
 }

// <-- _______________________________________  ONGLET - PATIENT _______________________________________ -->

/*  Affichera dans la «Zone d’affichage» la liste des patients. 
	La liste sera affichée dans un tableau HTML dont la 1ère ligne contiendra les attributs de la table en question.
*/
function showPatients(){   
	hiddenDiv(); //clean before
    var size = tbl_Patients.length;
	//var table="<table>";
   var table="<table class=table>";
       table+="<tr class=success>"+
					"<td>"+"<strong>Dossier</strong>"+"</td>"+
					"<td>"+"<strong>Nom</strong>"+"</td>"+
					"<td>"+"<strong>Prenom</strong>"+"</td>"+
					"<td>"+"<strong>Naissence</strong>"+"</td>"+
					"<td>"+"<strong>Sexe</strong>"+"</td>"+
               "</tr>";    
	for(i=0; i < size; i++)	
	{
      table+=  "<tr>"+
					 "<td>"+tbl_Patients[i].Dossier+"</td>"+
					 "<td>"+tbl_Patients[i].Nom+"</td>"+
					 "<td>"+tbl_Patients[i].Prenom+"</td>"+
					 "<td>"+tbl_Patients[i].Naissence+"</td>"+
					 "<td>"+tbl_Patients[i].Sexe+"</td>"+
                "</tr>";                   
	}
    table+= "</table>";    
	document.getElementById("divColAffiche").style.visibility = "visible";
    document.getElementById('divColAffiche').innerHTML=table;	
}

// <-- _______________________________________  ONGLET - HOSPITALISATION _______________________________________ -->

/*  Affichera dans la «Zone d’affichage» la liste des établissements. 
	La liste sera affichée dans un tableau HTML dont la 1ère ligne contiendra les attributs de la table en question.
*/
function showEtablissements(){     
    hiddenDiv();
    var size = tbl_Etablissements.length;
    
    var table="<table class=table>";
    //1 LIGNE and 5 COLONNES...
       table+="<tr class=success>"+
	   			"<td>"+"<strong>Etablissement</strong>"+"</td>"+
				"<td>"+"<strong>Nom</strong>"+"</td>"+
				"<td>"+"<strong>Adresse</strong>"+"</td>"+
				"<td>"+"<strong>Code Postal</strong>"+"</td>"+
				"<td>"+"<strong>Telephone</strong>"+"</td>"+
               "</tr>";
    
	for(i=0; i < size; i++)	
	{
      table+=  "<tr>"+
                 "<td>"+tbl_Etablissements[i].Etablissement+"</td>"+
                 "<td>"+tbl_Etablissements[i].Nom+"</td>"+
                 "<td>"+tbl_Etablissements[i].Adresse+"</td>"+
                 "<td>"+tbl_Etablissements[i].CodePostal+"</td>"+
                 "<td>"+tbl_Etablissements[i].Telephone+"</td>"+
                "</tr>";                   
	}
    table+= "</table>";    
    document.getElementById("divColAffiche").style.visibility = "visible";
    document.getElementById('divColAffiche').innerHTML=table;
}

/*  Affichera dans la «Zone d’affichage» la liste des hospitalisations. 
	La liste sera affichée dans un tableau HTML dont la 1ère ligne contiendra les attributs de la table en question.
*/
function showHospitalisations(){     
    hiddenDiv();
    var size = tbl_Hospitalisations.length;
    
    var table="<table class=table>";
    //1 LIGNE and 5 COLONNES
       table+="<tr class=success>"+
				"<td>"+"<strong>Code etablissement</strong>"+"</td>"+
				"<td>"+"<strong>No dossier patient</strong>"+"</td>"+
				"<td>"+"<strong>Date admission</strong>"+"</td>"+
				"<td>"+"<strong>Date sortie</strong>"+"</td>"+
				"<td>"+"<strong>Specialite</strong>"+"</td>"+
               "</tr>";
    
	for(i=0; i < size; i++)	
	{
      table+=  "<tr>"+
                 "<td>"+tbl_Hospitalisations[i].CodeEtablissement+"</td>"+
                 "<td>"+tbl_Hospitalisations[i].NoDossierPatient+"</td>"+
                 "<td>"+tbl_Hospitalisations[i].DateAdmission+"</td>"+
                 "<td>"+tbl_Hospitalisations[i].DateSortie+"</td>"+
                 "<td>"+tbl_Hospitalisations[i].Specialite+"</td>"+
                "</tr>";                   
	}
    table+= "</table>";    
    document.getElementById("divColAffiche").style.visibility = "visible";
    document.getElementById('divColAffiche').innerHTML=table;
}

// <-- _______________________________________  ONGLET - HOSPITALISATION PAR PATIENT _______________________________________ -->

/* Affichera un champ SELECT avec le numéro de dossier de tous les patients (pris directement du tableau JSON).
   Lorsqu’on choisira le dossier du patient, un tableau HTML sera affiché selon le même 
   ...format que les listes précédentes.
*/
function chargerDossier()
{   
	//pour chaque click, on cache les elements
	hiddenDiv()
    //Rendre visible la premiere dropdowlist
	document.getElementById("selectByDossier").style.visibility = "visible";
	//get the tag select
    var selecDossier = document.getElementById('selectByDossier');
    //clean the select
    selecDossier.options.length=0;
    //Give the first element : a text to display
    selecDossier.options[selecDossier.options.length]=new Option("Choisir  un dossier...");

    //Cree une array et y placer tous les numeros de dossier des patients...
    var tblSort = new Array();
	var taille = 1;    
	for(var prop in tbl_Hopital["Patiens"] )
	{
		tblSort[taille] = parseInt(tbl_Hopital["Patiens"][prop].Dossier);
		taille++;
	}	
    //On trie avant de filtrer
	tblSort.sort()
    //On filtre les doublons
	for(var i = 0; i < tblSort.length;i++)	{
		//remove each element equal to the preceding 
		if( tblSort[i] != tblSort[i+1] ){
            selecDossier.options[selecDossier.options.length] = new Option(tblSort[i]);
		}
	}
	//On disponibilise la div que contient le select
	document.getElementById("divColAfficheLeft").style.visibility = "visible";
}//end function

//Affiche le tableau des patients
function showTableHospByPatient(select){
	
        var table="<table class=table>";
		//1 LIGNE and 5 COLONNES
		table+="<tr class=success>"+
					"<td>"+"<strong>CodeEtablissement</strong>"+"</td>"+
					"<td>"+"<strong>NoDossierPatient</strong>"+"</td>"+
					"<td>"+"<strong>DataAdimission</strong>"+"</td>"+
					"<td>"+"<strong>DataSortie</strong>"+"</td>"+
					"<td>"+"<strong>Specialite</strong>"+"</td>"+			
				"</tr>";
						  
	for(var i in  select.options)//Pour chaque option
	{
		//...si l'item a été selectionné
		if(select.options[i].selected)
		{
			//...recupere sa valeur 
			var vl = select.options[i].text;
			//alert(vl);
			for(var prop in tbl_Hospitalisations)
			{
				if(tbl_Hospitalisations[prop].NoDossierPatient == vl)
				{
					table+="<tr>"+
								 "<td>"+tbl_Hospitalisations[prop].CodeEtablissement+"</td>"+
								 "<td>"+tbl_Hospitalisations[prop].NoDossierPatient	+"</td>"+
								 "<td>"+tbl_Hospitalisations[prop].DateAdmission	+"</td>"+
								 "<td>"+tbl_Hospitalisations[prop].DateSortie		+"</td>"+	
								"<td>"+tbl_Hospitalisations[prop].Specialite		+"</td>"+
						"</tr>"; 
				}
			}
		}		
	}
	table+= "</table>";    
	document.getElementById("divColAffiche").style.visibility = "visible";
	document.getElementById('divColAffiche').innerHTML=table;	
}

// <-- _______________________________________  ONGLET - HOSPITALISATION PAR ÉTABLISSEMENT ET SPECIALITÉ ________________________________ -->

/*Affiche un champ SELECT pour la liste des codes d’établissement .
 Les champs SELECT sont générés directement à partir des données des tableaux JSON*/
function chargerEtablissiment(){
	
	//pour chaque click...
	hiddenDiv()
	document.getElementById("selectByEtablissement").style.visibility = "visible";
	document.getElementById("tag_p_Title").style.visibility = "visible";
		
	var selecCode = document.getElementById('selectByEtablissement');
		selecCode.options.length=0;//on vide	
		selecCode.options[selecCode.options.length]=new Option("Choisir...");

	var tblSort = new Array();
	var taille = 1;    
	for(var prop in tbl_Hopital["Etablissements"] ){
		tblSort[taille] = tbl_Hopital["Etablissements"][prop].Etablissement +" ("+
						  tbl_Hopital["Etablissements"][prop].Nom+")";
		taille++;
	}	    
	tblSort.sort()//sort table

	//...fill select  with informations from table.
	for(var i = 0; i < tblSort.length;i++)	{
		//remove each element equal to the preceding 
		if( tblSort[i] != tblSort[i+1] ){
			selecCode.options[selecCode.options.length] = new Option(tblSort[i]);
		}
	}
	//On disponibilise la div que contient le select
	document.getElementById("divColAfficheLeft").style.visibility = "visible";	
}
/*Affiche un champ SELECT pour la liste des spécialités*/
function chargerSpecialite(selecCode)
{	
	//Cache la table pour chaque (onChange) sur la select (selectByEtablissement)
	document.getElementById("divColAffiche").style.visibility = "hidden";
	//OnChange, cache l'avertissement pour l'etablissement que n'a aucun dossier
	document.getElementById("msn-Alert").style.visibility = "hidden";
	
	var codeChoisie = selecCode.options[selecCode.selectedIndex].text;//Get the text(value) from the selected index
		codeChoisie = codeChoisie.substring(0,4);//get only the 4 string
	var selSpecialite  = document.getElementById("specialite");
	var obj ;	
	// var specialiteToCompare = "";
		selSpecialite.options.length = 0;//vider le select 
        selSpecialite.options[selSpecialite.options.length]=new Option("Choisir la spécialité...");
		
    var tblSort = new Array();
	var taille = 1;
	for(var prop in tbl_Hopital["Hospitalisations"] )
	{				
		obj = tbl_Hopital["Hospitalisations"][prop];//get the [objet] of the array		
			if(obj.CodeEtablissement == codeChoisie)
			{			
				tblSort[taille] = obj.Specialite;
				taille++;			
			}		
	}	
	
	if(tblSort.length == 0)	{
		document.getElementById("msn-Alert").style.visibility = "visible";
		document.getElementById("specialite").style.visibility = "hidden";
	}
	else{
		tblSort.sort()//On trie avant de filtrer
		for(var i = 0; i < tblSort.length;i++)	
		{
			//remove each element equal to the preceding 
			if( tblSort[i] != tblSort[i+1] )
			{
				selSpecialite.options[selSpecialite.options.length] = new Option(tblSort[i]);
			}
		}
		// On disponibilise la div que contient le select
		document.getElementById("specialite").style.visibility = "visible";
	}
}



//Affiche le tableau des hospitalisations filtré par specialité
function showTableBySpecialite(select)
{	
	var selCode = document.getElementById("selectByEtablissement");
	var code = selCode.options[selCode.selectedIndex].text;
		code = code.substring(0,4);
		
	var table="<table class=table>";
		//1 LIGNE and 5 COLONNES
		table+="<tr class=success>"+
					"<td>"+"<strong>Code</strong>"+"</td>"+
					"<td>"+"<strong>Dossier</strong>"+"</td>"+
					"<td>"+"<strong>Admission</strong>"+"</td>"+
					"<td>"+"<strong>Sortie</strong>"+"</td>"+
					"<td>"+"<strong>Spécialité</strong>"+"</td>"+
				"</tr>";
						  
	for(var i in  select.options)
	{
		//...si l'item a été selectionné
		if(select.options[i].selected)
		{
			//...recupere sa valeur 
			var vl = select.options[i].text;
			//alert(vl);
			for(var prop in tbl_Hospitalisations)
			{
				if(tbl_Hospitalisations[prop].Specialite == vl && tbl_Hospitalisations[prop].CodeEtablissement == code)
				{
					table+="<tr>"+
								 "<td>"+tbl_Hospitalisations[prop].CodeEtablissement+"</td>"+
								 "<td>"+tbl_Hospitalisations[prop].NoDossierPatient	+"</td>"+
								 "<td>"+tbl_Hospitalisations[prop].DateAdmission	+"</td>"+
								 "<td>"+tbl_Hospitalisations[prop].DateSortie		+"</td>"+
								 "<td>"+tbl_Hospitalisations[prop].Specialite		+"</td>"+
						  "</tr>"; 
				}
			}
		}		
	}
	table+= "</table>";    
	document.getElementById("divColAffiche").style.visibility = "visible";
	document.getElementById('divColAffiche').innerHTML=table;	
}

 
  