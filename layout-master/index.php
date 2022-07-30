    <!DOCTYPE html>
    <html lang="fr">
            <!-- _______________  HEADE _______________  -->           
            <?php include __DIR__.'/includes/head.php'; ?>
    <body>

        <div class="container-fluid">

            <!-- _______________  MENU _______________  -->           
            <?php include __DIR__.'/includes/menu.php'; ?>

            <!--   HERO  -->
            <div class="row">
                <div class="col">
                    <div class="owl-carousel owl-theme">
                        <div class="item"><img src="images/carrousel_01_1251x714.jpg" alt="logo menu" /></div>
                        <div class="item"><img src="images/carrousel_02_1251x714.jpg" alt="logo menu" /></div>
                        <div class="item"><img src="images/carrousel_03_1251x714.jpg" alt="logo menu" /></div>
                        <div class="item"><img src="images/carrousel_04_1251x714.jpg" alt="logo menu" /></div>
                    </div>
                </div>
            </div>

            <!--   CARDS   -->
            <div class="container">
                <div class="row">
                    <div class="col-md-12 col-lg-4">
                        <div class="card text-center">
                            <img class="card-img-top" src="images/BoutiqueEnLigne_SiteWeb.jpg" alt="logo card">
                            <div class="card-body">
                                <h5 class="card-title">Titre de la carte</h5>
                                <p class="card-text">Contenu textuel de la carte</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-4">
                        <div class="card text-center">
                            <img class="card-img-top" src="images/Circulaire-SiteWeb.jpg" alt="logo card">
                            <div class="card-body">
                                <h5 class="card-title">Titre de la carte</h5>
                                <p class="card-text">Contenu textuel de la carte</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-4">
                        <div class="card text-center">
                            <img class="card-img-top" src="images/Toilettage_SiteWeb.jpg" alt="logo card">
                            <div class="card-body">
                                <h5 class="card-title">Titre de la carte</h5>
                                <p class="card-text">Contenu textuel de la carte</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--   CONTENT   -->
            <div class="container">
                <div class="row">
                    <div class="col">
                        <h3>SECTION CONTENT</h3>
                        <p>
                            Dans le dernier chapitre, nous avons créé la mise en page à trois colonnes pour les
                            cartes d'information sur la
                            page d'accueil. Comme nous n'avons pas encore utilisé les modificateurs de classes, le
                            contenu de la ligne avec
                            les cartes est divisé en trois colonnes .col sur toutes les tailles d'écran. Ce n’est
                            pas une bonne façon de
                            faire concernant les utilisateurs de smartphone. Le contenu apparaîtra de manière trop
                            condensée à l’écran,
                            et cela rendra la lecture bien plus difficile.
                        </p>
                    </div>
                </div>
            </div>

            <!-- _______________  FOOTER _______________ -->
             <?php include __DIR__.'/includes/footer.php'; ?>
        </div>

        <!-- Bootstrap : must be the last-->
        <script src="js/bootstrap/jquery-3.6.0.min.js"></script>
        <script src="js/bootstrap/popper.min.js"></script>
        <script src="js/bootstrap/bootstrap.min.js"></script>
        <!-- Custom JS-->
        <script src="js/custom_script.js"></script>
        <!-- owl carousel 2-->
        <script src="js/owlcarousel/owl.carousel.min.js"></script>
    </body>

    </html>