<?php include '../../includes/head.php'; ?>
    <div class="container-fluid">

        <!--  ICONE + TITLE -->
        <div class="row mb-3">
            <div class="col-md-8">
               <h2><i class="far fa-address-card"></i> Post</h2> 
            </div>   

            <!--  ZONE RECHERCHE -->         
            <div class="col-md-3">
                   <input type="text" 
                          id="txtInput" 
                          name="" 
                          value="" 
                          class="form-control" 
                          placeholder="Qui cherchez vous?"> 
            </div> 

            <!--  BOUTTON + -->
            <div class="col-md-1">
                <button type="button"
                        class="btn btn-primary float-right" 
                        id="btnPlus" ><i class="fas fa-plus"></i> 
                </button>
            </div>        
        </div>  

        <!--  RENDER PAGE --> 
        <div class="row">
            <div class="col-md-12" id="listTemplate">
               <!-- TEMPLATE CONTENT ICI !-->
            </div>
        </div>

    </div>     
<?php include 'includes/ajouter.php'; ?>
<?php include '../../includes/footer.php'; ?>
