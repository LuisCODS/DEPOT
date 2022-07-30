

function valider() 
{
    var titre=document.getElementById('titre').value;
    var duree=document.getElementById('duree').value;
    var res = document.getElementById('res').value;

    if (titre == "" || duree == "" || res == "")
    {
	    alert("Vous n'avez pas saisie toutes les données !!!");
	    return false;
    }
    formFilm.submit();// (submit)envoit les données au serveur  
}

function lister(){
  formLister.submit();//envoyer les données au serveur
}
function modifier() {
    formModifier.submit();//envoyer les données au serveur
}
function mettreAJour() {

    var titre = document.getElementById('titre').value;
    var duree = document.getElementById('duree').value;
    var res = document.getElementById('res').value;

    if (titre == "" || duree == "" || res == "") {
        alert("Vous n'avez pas saisie toutes les données !!!");
        return false;
    }
    formMajFilm.submit();//envoyer les données au serveur
}