

<div class="modal fade ModalCadastro" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
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

        <!--  MODAL BODY-->
        <div class="modal-body">
             <!--  FORM -->
             <form id="formAjouter">
                  <input type="hidden" id="Post_ID" name="Post_ID" readonly="true" >
                  <div class="form-group">
                      <label for="Title" Title>Title</label>
                      <input type="text" class="form-control estVide" id="Title" name="Title" onkeypress="isItEmpty(this)" required>
                       <!--  SHOW WHEN INPUT IS EMPTY  -->
                      <div class="invalid-feedback"> Champs obligatoire!</div>
                  </div>
                  <div class="form-group">
                       <label for="Contenu">Resume</label>
                       <textarea class="form-control estVide" id="Resume" name="Resume" rows="3" onkeypress="isItEmpty(this)" required></textarea>
                       <div class="invalid-feedback"> Champs obligatoire!</div>
                  </div>
                  <div class="form-group">
                       <label for="Contenu">Contenu</label>
                       <textarea class="form-control estVide" id="Contenu" name="Contenu" rows="3" onkeypress="isItEmpty(this)" required></textarea>
                       <div class="invalid-feedback">Champs obligatoire!</div> 
                  </div>           
                  <div class="row">
                        <div class="col-md-6">
                            <div class="form-group ">
                                 <label for="DateDebut">Date debut</label>
                                 <input  class="form-control estVide " id="DateDebut" name="DateDebut" placeholder="yyyy-mm-dd">
                                 <div class="invalid-feedback">Champs obligatoire!</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group  ">
                                 <label for="DateFin">Date fin</label>
                                <input  class="form-control estVide" id="DateFin" name="DateFin" placeholder="yyyy-mm-dd" >
                             </div> 
                        </div>
                  </div>               
                  <div class="form-group">
                      <label for="Categorie_ID">Categorie</label>
                      <select class="form-control estVide" id="Categorie_ID" name="Categorie_ID" required>
                         <!--  RENDER PAGE(TAMPLATE) HERE -->
                      </select>
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
