
// ======================== GLOBAL ============================

var Default = {
    id: 0,
    src: '/Img/imgDefault.jpg',
    prix: 0.00,
    nome: 'Choisir...'
};

var MixSalade = {
    id:1,
    src: '/Img/image1.jpg',
    prix: 8.00,
    nome: 'Mix Salade'
    //fullPrice: function () {
    //return this.prix * 1.15;
    //}
};

var Soupe = {
    id: 2,
    src: '/Img/image2.jpg',
    prix: 6.00,
    nome: 'Soupe'
};

var Cesar = {
    id: 3,
    src: '/Img/image3.jpg',
    prix: 5.00,
    nome: 'Salade Cesar'
};

var tableauSalade = [Default, MixSalade, Soupe, Cesar];//Array


// ======================== FUNCTIONS ============================

//Initialise(dinamiquement) une list deroulande aves des données provenant d'un array
function initializeListe(){
    var tblSalade = document.getElementById('listOfSalade'); 
    var i;
    for (i in tableauSalade) {
        for (i = 0; i < tableauSalade.length; i++) {
            tblSalade.options[i] = new Option(tableauSalade[i].nome);  
        } 
    } 
}

//Change l'image à chaque selection sur la list
function changeImage(formName) {
    var imageSalade = document.getElementById('imageSalade');
    var i;
    for (i = 0; i < tableauSalade.length; i++) {

        if (formName.listOfSalade.options[i].selected) {
            //Si l'indice 0 est selectionné: on renitialise
            if (tableauSalade[i].id === 0) {
                imageSalade.src = tableauSalade[i].src;
                tpsTvq(tableauSalade[i].prix);
            } else {
                //...on change les images et les prix
                imageSalade.src = tableauSalade[i].src;
                tpsTvq(tableauSalade[i].prix);
            }
        }
    }       
}

function tpsTvq(nb1, nb2=0) {

    var taux = 1.13;
    var sousTotal = nb1 + nb2;
    var total = sousTotal * taux;
    var taxes = total-sousTotal;

    document.getElementById('sousTotal').innerHTML = "Sous-Total:   " + sousTotal + " $";
    document.getElementById('taxes').innerHTML = "Taxes:    " + taxes + " $";
    document.getElementById('total').innerHTML = "Total:    " +total + " $";
}
















    //ToTest:
    //document.getElementById('optionValue').innerHTML = "listItem.options[0].value :" + listItem.options[0].value;
    //document.getElementById('tableauValue').innerHTML = "tableauSalade[0].id:" + tableauSalade[0].id;
    //alert(tableauSalade.length);//3
    //listItem.options[0].value;//vaux 1
    //tableauSalade[0].id; // vaux 1

