<!-- _______________  BEGIN INCLUDE MODAL-AJOUTER   _________________-->
<div class="modal fade ModalDetails" tabindex="-1" role="dialog" 
     aria-labelledby="myLargeModalLabel" aria-hidden="true" id='ModalDetails'>
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">  

       <!--  MODAL HEAD -->
        <div class="modal-header">
            <!--  MODAL TITLE -->
            <h5 class="modal-title" id="ModalTitle"></h5>
                 <!--  X to close modal windows -->
                <button type="button" 
                        class="close"
                        data-dismiss="modal" 
                        aria-label="Close"
                        onclick="location.reload(true);">
                        <span aria-hidden="true">&times;</span>
                </button>
        </div>

        <!--  MODAL BODY -->
        <div class="modal-body">
           <form id="formAjouter">

                <input type="hidden" id="PK_ID_Membre" name="PK_ID_Membre" readonly="true" >
                <input type="hidden" id="profil" name="profil" readonly="true" value="membre" >
                    <div class="form-group">
                        <label for="nom">Nom</label>
                         <input type="text" minlength="1" maxlength="50" 
                                class="form-control estVide" id="nom" name="nom" 
                                onkeypress="isItEmpty(this)"
                                required> 
                               <!--  SHOW WHEN INPUT IS EMPTY  -->
                                <div class="invalid-feedback">
                                    Champs obligatoire!
                               </div>                 
                    </div>                    

                    <div class="form-group">
                        <label for="prenom">Prenom</label>
                         <input type="text" minlength="1" maxlength="50" 
                                class="form-control estVide" id="prenom" name="prenom" 
                                onkeypress="isItEmpty(this)"
                                required> 
                               <!--  SHOW WHEN INPUT IS EMPTY  -->
                                <div class="invalid-feedback">
                                    Champs obligatoire!
                               </div>                 
                    </div>


                    <div class="form-group">
                        <label for="profil">Profil</label>
                         <input type="password" minlength="1" maxlength="50" 
                                class="form-control estVide" id="profil" name="profil" 
                                onkeypress="isItEmpty(this)"
                                required> 
                               <!--  SHOW WHEN INPUT IS EMPTY  -->
                                <div class="invalid-feedback">
                                    Champs obligatoire!
                               </div>                 
                    </div>

        </form>  




        <!--  MODAL FOOTER  -->
        <div class="modal-footer">
             <button class="btn-success"                   
                    data-toggle="modal" 
                    type="button" 
                    id="btnAjouter">
                    <i class="far fa-save"></i> Ajouter
            </button>  
            <button class="btn-danger" 
                    data-dismiss="modal"  
                    type="button" 
                    id="btnSupprimer">
                    <i class="far fa-trash-alt"></i> Supprimer
            </button>
        </div>  

    </div>
  </div>
</div>
<!-- ______________  END INCLUDE MODAL-AJOUTER   ______________-->
