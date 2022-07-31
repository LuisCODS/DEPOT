
<!-- ______________________________________    MODAL - CREATE FILM    ______________________________________-->

<div class="modal fade modalEditer" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
              <!-- HEAD -->
              <div class="modal-header">
                  <h5 class="modal-title" id="TituloModalCentralizado">Nouveau Film</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <!-- BODY -->
              <div class="modal-body">
                    <!-- FORM -->
                    <form id="formAjouterFilm" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="PK_ID_Film"></label>
                                <input type="hidden" class="form-control" id="PK_ID_Film" name="PK_ID_Film" >
                            </div>
                            <div class="form-group">
                                <label for="titre">Titre</label>
                                <input type="text" class="form-control" id="titre" name="titre"
                                 placeholder="" size="40">
                            </div>
                            <div class="form-group">
                                <label for="prix">Prix</label>
                                <input type="text" class="form-control" id="prix" name="prix" placeholder="" size="40">
                            </div>
                            <div class="form-group">
                                <label for="categorie">Categorie</label>
                                <select class="form-control" id="categorie" name="categorie">
                                    <option selected>Choose...</option>
                                    <option value="Romance">Romance</option>
                                    <option value="Horreur">Horreur</option>
                                    <option value="Comedie">Comedie</option>
                                    <option value="Action">Action</option>
                                    <option value="Pour la famille">Pour la famille</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for=realisateur"">Realisateur</label>
                                <input type="text" class="form-control" id="realisateur" name="realisateur" placeholder="">
                            </div>
                            <div class="form-group">
                                <label for=description"">Description</label>
                                <textarea type="textarea" class="form-control" id="description" name="description" ></textarea>
                            </div>
                            <div class="form-group" >                                                             
                                <label for="pochette">Pochette</label>
                                <input type="file" class="form-control" id="pochette" name="pochette" >
                            </div>
                    </form>                     
              </div>
              <!-- FOOTER -->
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                  <button type="button" id="btnEnregistrerFilm"  
                  class="btn btn-success"><i class="fas fa-save"></i> Enregistrers</button>
              </div>

        </div>
    </div>
</div>