
<!-- _______________  BEGIN INCLUDE MODAL-AJOUTER   _________________-->
<div class="modal fade ModalCadastro" tabindex="-1" role="dialog" 
     aria-labelledby="myLargeModalLabel" aria-hidden="true" id='ModalCadastro'>
  <div class="modal-dialog modal-lg" role="document">
    <!--  MODAL CONTENT -->
    <div class="modal-content">  

       <!--  MODAL HEAD -->
        <div class="modal-header">
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

                <input type="hidden" id="Utilisateur_ID" name="Utilisateur_ID" readonly="true" >

                <div class="form-group">
                        <label for="">Nom</label>
                        <input type="text" class="form-control estVide" id="UtilisateurName" name="UtilisateurName" onkeypress="isItEmpty(this)" required>
                       <!--  SHOW WHEN INPUT IS EMPTY  -->
                        <div class="invalid-feedback"> Champs obligatoire!</div>
                </div>

                <div class="form-group">
                        <label for="UtilisateurNickName">Utilisateur</label>
                        <input type="text" class="form-control estVide" id="UtilisateurNickName" 
                               name="UtilisateurNickName" onkeypress="isItEmpty(this)" required>
                       <!--  SHOW WHEN INPUT IS EMPTY  -->
                        <div class="invalid-feedback">Champs obligatoire!</div>                           
                </div>

                <div class="form-group">
                        <label for="UtilisateurMDP">Mot de passe</label>
                        <input type="password" autocomplete="on" class="form-control estVide" 
                               id="UtilisateurMDP" name="UtilisateurMDP" onkeypress="isItEmpty(this)" required>
                       <!--  SHOW WHEN INPUT IS EMPTY  -->
                       <div class="invalid-feedback">Champs obligatoire!</div>                        
                </div>

                <div class="form-group">
                        <label for="UtilisateurEmail">Email</label>
                        <input type="email" class="form-control estVide" 
                               id="UtilisateurEmail" name="UtilisateurEmail" onkeypress="isItEmpty(this)" required>
                        <!--  SHOW WHEN INPUT IS EMPTY  -->
                        <div class="invalid-feedback">Champs obligatoire!</div>  
                </div>

                <div class="form-group">
                    <label for="Profil_ID">Profil</label>
                    <select class="form-control estVide" id="Profil_ID" name="Profil_ID" required>
                       <!--  RENDER PAGE(TAMPLATE) HERE -->
                    </select>
                        <!--  SHOW WHEN INPUT IS EMPTY  -->
                        <div class="invalid-feedback">Champs obligatoire!</div>  
                </div>

            </form>            
        </div>

        <!--  MODAL FOOTER -->
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
    <!--  FIN MODAL CONTENT -->
    
  </div>
</div>
<!-- ______________  END INCLUDE MODAL-AJOUTER   ______________-->
