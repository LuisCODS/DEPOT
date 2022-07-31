
<?php
    extract($_POST);
    foreach( json_decode($obj) as $film)  
    {
?>          

<!-- TEMPLATE CARD FILM -->
<div class="card flex-container" style="width: 20rem;  ">
        <a href="#" target="_blank">
            <img class="card-img-top"  src="../../img/<?php echo $film->pochette; ?>"width="200" height="300">
        </a>
         <div class="card-body">
            <h5 class="card-title">Titre: <?php echo $film->titre; ?></h5>
             <p class="card-text" >Realisateur: <?php echo $film->realisateur; ?></p>
             <p class="card-text">Prix: <?php echo $film->prix; ?>$</p>
             <p class="card-text">Categorie: <?php echo $film->categorie; ?></p>
             <p class="card-text">Description: <?php echo $film->description; ?></p>
             <a href="#" class="btn btn-primary">Ajouter Panier</a>
        </div>
</div>
<!--  FIN TEMPLATE CARD FILM -->

<?php  } ?>   
