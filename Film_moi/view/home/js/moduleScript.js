// //  PORTÉE GLOBAL 
// var membreController ='../../controller/membre.php';


// //SEND TO CONTROLLER :Route, data, action
// $('#btnEnregistrerMembre').click(()=> {

//     //get all form filds  
//     var donnes   = $("#formAjouter").serialize();
//     var action = 'action=insert';

//    $.ajax({
//        method: 'POST',
//        url:membreController,
//        data:action+'&'+donnes
//    }).done((msg)=>{
//        //plugin confirm
//        $.confirm({
//            title:'Bienvenue !',
//             content: msg,
//             buttons:{
//                 OK:()=>{
//                     $('#modal_DevenirMembre').modal('toggle');//close modal
//                     // Recharge la page actuelle à partir du serveur, sans utiliser le cache.		
//                     location.reload(true);                  
//                 }
//             }
//        });    
//    });
// });