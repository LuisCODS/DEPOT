// =========================  EXERCICE 1 ========================= -->

function valider(form){
	
	var input = form.nombre.value;
	if(input!= "")
	{
		if(input > 1 && input < 21 && input != ""){
			buildTable(input);
		}
		else{			
			alert("Veuillez entre un numero entre 2 et 20!");
			form.nombre.focus();
		}
	}else{
		alert("Champs vide!");
		form.nombre.focus();
	}	
	
}//fin methode
	
function buildTable(input)
{			
	//Permet de netoyer à chaque click pour une nouvelle table de multiplication.
	document.getElementById('content').innerHTML = null;
	var tbody = document.createElement("TBODY");
	var table   = document.createElement("TABLE");
		table.setAttribute("border",1);
		table.appendChild(tbody);
		
	for(var lig = 1; lig <= 12; lig++)
	{
		var resultat = input*lig;
		var tr = document.createElement("TR");	//row 1
		
		for (var col = 1; col <= 5; col++)
		{	
			var td = document.createElement("TD"); //col 1	
			
			//pour sauter chaque collonne...
			switch(col) 
			{
			  case 1:					
					var td_text = document.createTextNode(input);
					td.appendChild(td_text);
					tr.appendChild(td);
				break;
			  case 2:
					var td_text = document.createTextNode("x");
					td.appendChild(td_text);
					tr.appendChild(td);
				break;
			  case 3:
					var td_text = document.createTextNode(lig);
					td.appendChild(td_text);
					tr.appendChild(td);
				break;
			  case 4:
					var td_text = document.createTextNode("=");
					td.appendChild(td_text);
					tr.appendChild(td);
				break;
			  case 5:
					var td_text = document.createTextNode(resultat);
					td.appendChild(td_text);
					tr.appendChild(td);
				break;
			}			
		}
		tbody.appendChild(tr);
	}	
	var thead   = document.createElement("THEAD");
		
		//ajouter la table à l'élement «content»
		document.getElementById("content").appendChild(table);
}//fin methode




// =========================  EXERCICE 3 ========================= -->
//Objet JASON
var automobiles = {
    "Acura": ["MDX", "RDX", "TL", "RL"],
    "Audi": ["A4", "A6", "A8", "S4", "S6"],
    "BMW": ["M3", "M5", "M6", "Z4"],
    "Chevrolet": ["Aveo", "Cobalt", "Colorado", "Corvette", "Equinox"],
    "Chrysler": ["300", "Aspen", "PT Cruser", "Sebring"],
    "Ford": ["Edge", "Escape", "Fusion", "Focus"],
    "Honda": ["Accord", "Civic", "Element", "Fit"],
    "Hyundai": ["Accent", "Elantra", "Tiburon", "Sonata", "Tucson"]
};

var marques = document.getElementById("marque");
var modeles = document.getElementById("modele");


for (var propre in automobiles){
    var option = document.createElement("option");
    option.textContent = propre;
    marques.add(option);
}

marques.addEventListener("change", function () {
    modeles.classList.remove('invisible');
    var tab = automobiles[marques.options[marques.selectedIndex].value];
    modeles.innerHTML = null;
    for (var i = 0; i < tab.length; i++) {
        var option = document.createElement("option");
        option.textContent = tab[i];
        modeles.add(option);
    }
})