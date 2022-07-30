var input = document.querySelector('#nombre');

input.addEventListener("keypress", function (event) {
    var i;
    var table = document.getElementById('table-multiplication');
    if (event.keyCode === 13) {
        table.innerHTML = null;
        for (i = 1; i <= 12; i++) {
            var resultat = input.value * i;
            table.innerHTML += '<tr>' +
                '<td>' + input.value + '</td>' +
                '<td>X</td>' +
                '<td>' + i + '</td>' +
                '<td>=</td>' +
                '<td>' + resultat + '</td>' +
                '</tr>'
        }
    }
})

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

var marques = document.querySelector("#marque");
var modeles = document.querySelector("#modele");

//console.log(Object.keys(automobiles));

Object.keys(automobiles).forEach((m) => {
    var option = document.createElement("option");
    option.textContent = m;
    marques.add(option);
});


marques.addEventListener("change", function () {
    modeles.classList.remove('invisible');
    var tab = automobiles[marques.options[marques.selectedIndex].value];
    modeles.innerHTML = null;
    for (var i = 0; i < tab.length; i++) {
        var option = document.createElement("option");
        option.textContent = tab[i];
        modeles.add(option);
    }
    //    console.log(marques.options[marques.selectedIndex].value);
})

//var propre, i, tab;
//
//for (propre in automobiles){
//    tab = automobiles[propre];
//    for(i in tab){
//        console.log(tab[i]);
//    }
//}
