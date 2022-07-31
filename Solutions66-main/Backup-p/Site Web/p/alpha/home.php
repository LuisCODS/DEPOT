<!-- ==================  SESSION CARROUSEL======================== -->
<?php
$enonce = "SELECT * FROM PARALLAX 
            WHERE IMAGE IS NOT NULL AND IMAGE <> '' 
            AND (DATE_EXP > now() OR DATE_EXP IS NULL)
            AND (DATE_DEBUT < now() OR DATE_DEBUT IS NULL) 
            AND LANGUE = ?
            AND `HIDE` IS NULL
            ORDER BY WEB_FIRST = 1 DESC, RAND()	";
$resultParallax = query($enonce,[$_SESSION['lang']],$mysqli_db);
if($resultParallax->num_rows > 0){
	?>
	<div class="container-fluid pl-0 pr-0">
	<div class="home-parallax parallax-slide fullwidth-block small-slide no-select">
		<div class="swiper-container" style="height: 424px;" data-autoplay="10000" data-speed="1000"  data-loop="1"  data-center="0" data-slides-per-view="1" >
			<div class="swiper-wrapper" >
				<?php 
				while($rowParallax = $resultParallax->fetch_assoc()){
					?>
					<div class="swiper-slide" data-val="0" data-slide-bg="/upimg/parallax/<?= $rowParallax["IMAGE"]?>?t=1&c=1&w=1200&h=600&zip=9" > 
						<div class="swiper-backdrop" >
							<div class="swiper-slide-content" style="height: 424px;">
								<div class="swiper-slide-content-sub" >
									<?php if($rowParallax["SOUSTITRE_TOP"] != "" and $rowParallax["SOUSTITRE_TOP"] != null){?>
										<h2 class="subtitle"><?= $rowParallax["SOUSTITRE_TOP"]?></h2>
									<?php }?>
									<?php if($rowParallax["TITRE"] != "" and $rowParallax["TITRE"] != null){?>
										<h2 class="title"><?= $rowParallax["TITRE"]?></h2>
									<?php }?>
									<?php if($rowParallax["SOUSTITRE_BOTTOM"] != "" and $rowParallax["SOUSTITRE_BOTTOM"] != null){?>
										<h2 class="subtitle"><?= $rowParallax["SOUSTITRE_BOTTOM"]?></h2>
									<?php }?>
									<?php if($rowParallax["DESCRIPTION"] != "" and $rowParallax["DESCRIPTION"] != null){?>
										<div class="description"><?= $rowParallax["DESCRIPTION"]?></div>
									<?php }?>
									<?php if($rowParallax["LIEN"] != "" and $rowParallax["LIEN"] != null){?>
										<div class="info">
											<a href="<?= $rowParallax["LIEN"]?>"><?= $rowParallax["BUTTON_TEXT"]?></a>
										</div>
									<?php }?>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<div class="swiper-pagination"></div>
			<div class="swiper-button-prev"><i class="fa fa-chevron-left"></i></div>
			<div class="swiper-button-next"><i class="fa fa-chevron-right"></i></div>
		</div>
	</div>
	</div>
	<?php
}
?>
<?php 
/*
<!-- =============================  ZONE  CONCOURS ============================= 
<div class="bg-black animo-special-banner">
    <div class="container">
    	<div class="row align-items-center">
    		<div class="col-lg-5 p-3">
    			<div class="subtitle pb-1"><b><?= L('concours_titre')?>!</b></div>
    			<div class="description pb-1"><?= L('concours_sousTitre1')?></div>
    			<div class="title">
    				<div class="text-animo"><b><?= L('gagner_titre')?>*</b></div>
    				<div><small><?= L('gagner_sousTitre1')?></small></div>
    			</div>
    			<div class="text-right px-5">
    				<div class="info">
						<a class="text-animo" href="/contact"><?= L('btn_participer')?></a>
					</div>
    			</div>
    		</div>
    			<img class="lazyload" data-src="images/royalcanin-bk-noir.jpg">
    		</div>
    	</div>
    </div>
</div>
-->*/ 
?>

 <!-- ========================= SESSION  DIVERS ========================== -->
 <session class="divers">
    <div class="container-fluid mt-5 mb-5">
        <div class="row">
            <div class="divers_bloc  col-12 col-md-4 ">
                <a href="/rv">
                    <img class="lazyload loaded center_element" data-src="/images/Toilettage_SiteWeb.jpg" src="/images/Toilettage_SiteWeb.jpg" data-was-processed="true">
                </a>
            </div>
            <div class="divers_bloc  col-12 col-md-4 ">
    			<?php 
    			$lien_circulaire = null;
    			$getIfPublisac = $mysqli_db->query("SELECT LIEN FROM PARALLAX WHERE TYPE = 'circulaire' AND DATE_DEBUT < NOW() AND DATE_EXP > NOW() ORDER BY DATE_INSERT DESC LIMIT 1");
                //$getIfPublisac = $mysqli_db->query("SELECT LIEN FROM PARALLAX WHERE TYPE = 'circulaire' AND DATE_DEBUT ='2021-12-23 00:00:00'");//To test
                
                if($getIfPublisac->num_rows === 1){
                	$lien_circulaire = $getIfPublisac->fetch_row()[0];
                }           
    			// ______________  AFFICHE  CIRCULAIRE ______________________
    			//$lien_circulaire = null; //to test sans circulaire disponible!
    			if($lien_circulaire){
    		    ?>
    		    	<a href="<?= $lien_circulaire ?>" class="linktToMagazine" target="_blank">
		    	        <img class="h-100 lazyload loaded  center_element" data-src="/images/Circulaire-SiteWeb.jpg" src="/images/Circulaire-SiteWeb.jpg" data-was-processed="true">
		    	    </a>
		    	<?php
    			}else{
			    // ______________  AFFICHE  MAGAZINE ______________________
			    ?>
                    <a href="https://issuu.com/animoetc/docs/magazineanimovol11" target="_blank">
                        <img class="lazyload loaded img_magazine" data-src="/images/Magazine_SiteWeb.jpg" src="/images/Magazine_SiteWeb.jpg" data-was-processed="true">
                    </a>
			    <?php } ?>
            </div>
            <div class="divers_bloc  col-12 col-md-4 ">
                <a href="/produits">
                    <img class="lazyload loaded  center_element" data-src="/images/BoutiqueEnLigne_SiteWeb.jpg" src="/images/BoutiqueEnLigne_SiteWeb.jpg" data-was-processed="true">
                </a>
            </div>
        </div>
        <div class="row">
            <div class="divers_bloc  col-12 col-md-4 ">
                <a href="/avantages">
                    <img class="lazyload loaded carte_fanimo center_element" data-src="/images/carte_fanimo_650x400.png" src="/images/carte_fanimo_650x400.png" data-was-processed="true">
                </a>
            </div>
            <div class="divers_bloc  col-12 col-md-4 ">
              <div>
                <h4 class="animo-title text-black m-0">Recherchez notre logo </h4>
                <h4 class="animo-title text-animo m-0">dans nos boutiques</h4>
              </div>
              <div class="center_element mt-4">
                <a href="/achetezlocal" class="submenu-item">						
                  <img class="lazyload loaded" data-src="/images/achet_local200x200.jpg" src="/images/achet_local200x200.jpg" data-was-processed="true">
                </a>
              </div>
            </div>
            <div class="divers_bloc  col-12 col-md-4 ">
                <img class="lazyload loaded  center_element" data-src="/images/CarteCadeau_SiteWeb.jpg" src="/images/CarteCadeau_SiteWeb.jpg" data-was-processed="true">
            </div>
        </div>
    </div> 
 </session>
 
<!-- ==========  SESSION EN VEDETTE & NOS INDISPENSABLES  ================ -->
<div class="container">
    <div class="section-produits py-3">
        <!-- ====== SESSION VEDETTE ====== -->
    	<div class="container pt-3 pb-1">
    		<h2 class="animo-title"><?= L('en_vedette_titre')?></h2>
    	</div>
    	<div class="container text-center pl-0 pr-0">
    	    <!-- menu -->
    		<ul class="nav justify-content-center animo-product-tabs" id="tabsEV" role="tablist">
            	<li class="nav-item">
                	<a class="nav-link active" id="gttabsEVTous" data-toggle="tab" href="#tabsEVTous" role="tab" aria-controls="tabsEVTous" aria-selected="true"><?= L('menu1_tous','o')?></a>
            	</li>
            	<li class="nav-item">
            		<a class="nav-link" id="gttabsEVChiens" data-toggle="tab" href="#tabsEVChiens" role="tab" aria-controls="tabsEVChiens" aria-selected="false"><?= L('cat3','o')?></a>
            	</li>
            	<li class="nav-item">
            		<a class="nav-link" id="gttabsEVChats" data-toggle="tab" href="#tabsEVChats" role="tab" aria-controls="tabsEVChats" aria-selected="false"><?= L('cat2','o')?></a>
            	</li>
            	<li class="nav-item">
            		<a class="nav-link" id="gttabsEVRongeurs" data-toggle="tab" href="#tabsEVRongeurs" role="tab" aria-controls="tabsEVRongeurs" aria-selected="false"><?= L('cat6','o')?></a>
            	</li>
            	<li class="nav-item">
            		<a class="nav-link" id="gttabsEVOiseaux" data-toggle="tab" href="#tabsEVOiseaux" role="tab" aria-controls="tabsEVOiseaux" aria-selected="false"><?= L('cat5','o')?></a>
            	</li>
            	<li class="nav-item">
            		<a class="nav-link" id="gttabsEVOther" data-toggle="tab" href="#tabsEVOther" role="tab" aria-controls="tabsEVOther" aria-selected="false"><?= L('autres_animaux','o')?></a>
            	</li>
            </ul>
            <!-- les articles -->
            <div class="tab-content" id="tabsEVContent">
            	<div class="tab-pane fade show active" id="tabsEVTous" role="tabpanel" aria-labelledby="gttabsEVTous">
    				<div class="owl-carousel owl-theme">
                		<?php get_products(['where'=>['enVedette = ?'=>'1'],'orderby'=>'article.date_update desc','limit'=>50,'css_class'=>'item m-1'])?>
                	</div>
            	</div>
            	<div class="tab-pane fade" id="tabsEVChiens" role="tabpanel" aria-labelledby="gttabsEVChiens">
            		<div class="owl-carousel owl-theme">
                		<?php 
                		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
    		              FROM article_categorie
    		              WHERE id_categorie = 3", $_SESSION['lang']);
                		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                		if($rowCategorie = $resultCategorie->fetch_assoc()){
                		}
                		get_products(['where'=>['enVedette = ?'=>'1','id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'orderby'=>'article.date_update desc','limit'=>50,'css_class'=>'item m-1'])?>
                	</div>
            	</div>
            	<div class="tab-pane fade" id="tabsEVChats" role="tabpanel" aria-labelledby="gttabsEVChats">
            		<div class="owl-carousel owl-theme">
                		<?php 
                		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
    		              FROM article_categorie
    		              WHERE id_categorie = 2", $_SESSION['lang']);
                		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                		if($rowCategorie = $resultCategorie->fetch_assoc()){
                		}
                		get_products(['where'=>['enVedette = ?'=>'1','id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'orderby'=>'article.date_update desc','limit'=>50,'css_class'=>'item m-1'])?>
                	</div>
            	</div>
            	<div class="tab-pane fade" id="tabsEVRongeurs" role="tabpanel" aria-labelledby="gttabsEVRongeurs">
            		<div class="owl-carousel owl-theme">
                		<?php 
                		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
    		              FROM article_categorie
    		              WHERE id_categorie = 6", $_SESSION['lang']);
                		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                		if($rowCategorie = $resultCategorie->fetch_assoc()){
                		}
                		get_products(['where'=>['enVedette = ?'=>'1','id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'orderby'=>'article.date_update desc','limit'=>50,'css_class'=>'item m-1'])?>
                	</div>
            	</div>
            	<div class="tab-pane fade" id="tabsEVOiseaux" role="tabpanel" aria-labelledby="gttabsEVOiseaux">
            		<div class="owl-carousel owl-theme">
                		<?php 
                		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
        	              FROM article_categorie
        	              WHERE id_categorie = 5", $_SESSION['lang']);
                		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                		if($rowCategorie = $resultCategorie->fetch_assoc()){
                		}
                		get_products(['where'=>['enVedette = ?'=>'1','id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'orderby'=>'article.date_update desc','limit'=>50,'css_class'=>'item m-1'])?>
                	</div>
            	</div>
            	<div class="tab-pane fade" id="tabsEVOther" role="tabpanel" aria-labelledby="gttabsEVOther">
            		<div class="owl-carousel owl-theme">
                		<?php 
                		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
    		              FROM article_categorie
    		              WHERE id_categorie IN(1,4)", $_SESSION['lang']);
                		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                		$cache_list_enfants = [];
                		while($rowCategorie = $resultCategorie->fetch_assoc()){
                		    $cache_list_enfants = array_merge($cache_list_enfants,explode(',',$rowCategorie['cache_list_enfants']));
                		}
                		get_products(['where'=>['enVedette = ?'=>'1','id_categorie IN(?)'=>$cache_list_enfants],'orderby'=>'article.date_update desc','limit'=>50,'css_class'=>'item m-1'])?>
                	</div>
            	</div>
            </div>
        </div>
        
        <!-- ====== SESSION NOS INDISPENSABLES  ====== -->
        <?php 
        $id_mag_ind = $_SESSION['MAGASIN']['ID_MAGASIN'] ?: 5;
        $getProduitIndisp = query('select group_concat(id_article) from article_indispensable where id_magasin IN(?,5) order by id_magasin = ? desc',[$id_mag_ind,$id_mag_ind],$mysqli);
        if($getProduitIndisp->num_rows > 0){
            $list_id_article = explode(',',$getProduitIndisp->fetch_row()[0]);
        ?>
            <div class="py-3">
            	<div class="container pt-3 pb-1">
            		<h2 class="animo-title"><?= L('nos_indispensables_titre')?></h2>
            	</div>
            	<div class="container text-center pl-0 pr-0">
            	    <!-- menu -->
            		<ul class="nav justify-content-center animo-product-tabs" id="tabsNI" role="tablist">
                    	<li class="nav-item">
                        	<a class="nav-link active" id="gttabsNITous" data-toggle="tab" href="#tabsNITous" role="tab" aria-controls="tabsNITous" aria-selected="true"><?= L('menu1_tous','o')?></a>
                    	</li>
                    	<li class="nav-item">
                    		<a class="nav-link" id="gttabsNIChiens" data-toggle="tab" href="#tabsNIChiens" role="tab" aria-controls="tabsNIChiens" aria-selected="false"><?= L('cat3','o')?></a>
                    	</li>
                    	<li class="nav-item">
                    		<a class="nav-link" id="gttabsNIChats" data-toggle="tab" href="#tabsNIChats" role="tab" aria-controls="tabsNIChats" aria-selected="false"><?= L('cat2','o')?></a>
                    	</li>
                    	<li class="nav-item">
                    		<a class="nav-link" id="gttabsNIRongeurs" data-toggle="tab" href="#tabsNIRongeurs" role="tab" aria-controls="tabsNIRongeurs" aria-selected="false"><?= L('cat6','o')?></a>
                    	</li>
                       	<li class="nav-item">
                    		<a class="nav-link" id="gttabsNIOiseaux" data-toggle="tab" href="#tabsNIOiseaux" role="tab" aria-controls="tabsNIOiseaux" aria-selected="false"><?= L('cat5','o')?></a>
                    	</li>
                    	<li class="nav-item">
                    		<a class="nav-link" id="gttabsNIOther" data-toggle="tab" href="#tabsNIOther" role="tab" aria-controls="tabsNIOther" aria-selected="false"><?= L('autres_animaux','o')?></a>
                    	</li>  
                    </ul>
                    <!-- les articles -->
                    <div class="tab-content" id="tabsNIContent">
                    	<div class="tab-pane fade show active" id="tabsNITous" role="tabpanel" aria-labelledby="gttabsNITous">
            				<div class="owl-carousel owl-theme" >
                        		 <?php get_products(['where'=>['article.id_article IN(?)'=>$list_id_article],'css_class'=>'item m-1','orderby'=>'article.date_update desc','limit'=>100]); ?>
                        	</div>
                    	</div>
                    	<div class="tab-pane fade" id="tabsNIChiens" role="tabpanel" aria-labelledby="gttabsNIChiens">
                			<?php 
                    		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
            	              FROM article_categorie
            	              WHERE id_categorie = 3", $_SESSION['lang'], $sqlcaisse->real_escape_string($_GET['categorie']));
                    		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                    		if($rowCategorie = $resultCategorie->fetch_assoc()){
                    		}
                    		
                    		?>
                    		<div class="owl-carousel owl-theme" >
                        		 <?php get_products(['where'=>['article.id_article IN(?)'=>$list_id_article,'id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'css_class'=>'item m-1','orderby'=>'article.date_update desc','limit'=>100]); ?>
                        	</div>
                    	</div>
                    	<div class="tab-pane fade" id="tabsNIChats" role="tabpanel" aria-labelledby="gttabsNIChats">
                    		<?php
                    		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
        		              FROM article_categorie
        		              WHERE id_categorie = 2", $_SESSION['lang']);
                    		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                    		if($rowCategorie = $resultCategorie->fetch_assoc()){
                    		}
                    		?>
                    		<div class="owl-carousel owl-theme" >
                        		 <?php get_products(['where'=>['article.id_article IN(?)'=>$list_id_article,'id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'css_class'=>'item m-1','orderby'=>'article.date_update desc','limit'=>100]); ?>
                        	</div>
                    	</div>
                    	<div class="tab-pane fade" id="tabsNIRongeurs" role="tabpanel" aria-labelledby="gttabsNIRongeurs">
                    		<?php 
                    		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
        		              FROM article_categorie
        		              WHERE id_categorie = 6", $_SESSION['lang']);
                    		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                    		if($rowCategorie = $resultCategorie->fetch_assoc()){
                    		}
                    		?>
                    		<div class="owl-carousel owl-theme" >
                        		 <?php get_products(['where'=>['article.id_article IN(?)'=>$list_id_article,'id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'css_class'=>'item m-1','orderby'=>'article.date_update desc','limit'=>100]); ?>
                        	</div>
                    	</div>
                     	<div class="tab-pane fade" id="tabsNIOiseaux" role="tabpanel" aria-labelledby="gttabsNIOiseaux">
                    		<?php 
                    		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
        		              FROM article_categorie
        		              WHERE id_categorie = 5", $_SESSION['lang']);
                    		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                    		if($rowCategorie = $resultCategorie->fetch_assoc()){
                    		}
                    		?>
                    		<div class="owl-carousel owl-theme" >
                        		 <?php get_products(['where'=>['article.id_article IN(?)'=>$list_id_article,'id_categorie IN(?)'=>explode(',',$rowCategorie['cache_list_enfants'])],'css_class'=>'item m-1','orderby'=>'article.date_update desc','limit'=>100]); ?>
                        	</div>
                    	</div>
                    	<div class="tab-pane fade" id="tabsNIOther" role="tabpanel" aria-labelledby="gttabsNIOther">
                    		<?php 
                    		$queryCategorie = sprintf("select cache_list_enfants, label_%s AS catLabel
        		              FROM article_categorie
        		              WHERE id_categorie IN(1,4)", $_SESSION['lang']);
                    		$resultCategorie = $sqlcaisse->query($queryCategorie) or die();
                    		$cache_list_enfants = [];
                    		while($rowCategorie = $resultCategorie->fetch_assoc()){
                    		    $cache_list_enfants = array_merge($cache_list_enfants,explode(',',$rowCategorie['cache_list_enfants']));
                    		}
                    		?>
                    		<div class="owl-carousel owl-theme" >
                        		 <?php get_products(['where'=>['article.id_article IN(?)'=>$list_id_article,'id_categorie IN(?)'=>$cache_list_enfants],'css_class'=>'item m-1','orderby'=>'article.date_update desc','limit'=>100]); ?>
                        	</div>
                    	</div>       
                    </div>
                </div>
            </div>
        <?php 
        }
        ?>
    </div>
</div>

<!-- ========================= SESSION BLOG ============================= -->
<div class="container">
	<div class="row"> 
		<?php 
		$listAND = [];
		$listPARAM = [];
		$listAND[] = 'LANGUE = ?';
		$listPARAM[] = $_SESSION["lang"];
		//$listAND[] = 'DATE_INSERT = ?';
		//$listPARAM[] = date('Y-m-d');//date('Y-m-d H:i:s');
	   
		$and = implode(' and ',$listAND);
		$lng_cat_main = $_SESSION["en"] ? "label_en" : "label_fr";
        $blog_query_all = "SELECT BLOG.*, label_fr, GROUP_CONCAT(CATEGORIES.$lng_cat_main SEPARATOR ', ') AS NOM_CATEGORIE
                            FROM BLOG
                            LEFT JOIN BLOG_CATEGORIES_LINK USING (ID_ARTICLE)
                            LEFT JOIN CATEGORIES USING (id_categorie)
                            WHERE $and
                            GROUP BY ID_ARTICLE
                            ORDER BY DATE_ARTICLE DESC LIMIT 3 ";
		$result_blog1 = query($blog_query_all,$listPARAM,$mysqli_db);
		if($result_blog1->num_rows > 0){
		    //$i = 0;
		    while ($rowBlog = $result_blog1->fetch_assoc()) {  
                //echo '<pre>' , print_r($rowBlog["ID_ARTICLE"]) , '</pre>'; die();
		        //$image = (!empty($rowBlog["IMAGE"]) ? ("/upimg/blog/" . $rowBlog["IMAGE"]."?t=1&c=1&w=433&h=250") : "//placehold.it/400x400"); //?t=1&w=331&h=331
		        $image = ( !empty($rowBlog["IMAGE"]) ? ("/upimg/blog/" . $rowBlog["IMAGE"]."?t=1&c=1&w=400&h=200") : "//placehold.it/400x200" ); ?> 
    
                <?php if($varControle != 1){ ?>  
                
                    <!-- Affichage modele 1  -->
                    <a class="col-12 col-md-12 col-lg-4 col-xl-4 blog_box text-decoration-none" href="/blog/<?= strtogoogle($rowBlog["SUJET"]) ?>/<?= $rowBlog["ID_ARTICLE"] ?>" style="background:black;">
                        <h4 class="pt-5" style="color:white;"><?= $rowBlog["SUJET"]?></h4>
                        <i class="pt-3 fa fa-long-arrow-right fa-3x" style="color:white;"  aria-hidden="true" ></i>
                    </a>  
                    <a class="col-12 col-md-12 col-lg-4 col-xl-4 blog_box text-center text-decoration-none"  href="/blog/<?= strtogoogle($rowBlog["SUJET"]) ?>/<?= $rowBlog["ID_ARTICLE"] ?>" style="color: black;">
                        <i class="text-justify pt-4 fa fa-heart-o fa-5x" aria-hidden="true" style="line-height:40px;" ></i>
                        <p class="blog_texte">
                            <?php
                            $textCleanTagFromBd = strip_tags($rowBlog["TEXTE"]);
                            echo mb_substr($textCleanTagFromBd, 0, 180)." ...";
                            ?>
                        </p>
                    </a>
                    <a class="col-12 col-md-12 col-lg-4 col-xl-4 blog_box blog_image pl-0 pr-0" href="/blog/<?= strtogoogle($rowBlog["SUJET"]) ?>/<?= $rowBlog["ID_ARTICLE"] ?>" style="background: url(<?= $image ?>) no-repeat center center; background-size: cover;"></a>  
                    <?php $varControle = 1;  ?>
                    
		        <?php }elseif ($varControle == 1) {  ?>
		        
                    <!-- Affichage modele 2-->
                    <a class="col-12 col-md-12 col-lg-4 col-xl-4 blog_box text-center text-decoration-none" href="/blog/<?= strtogoogle($rowBlog["SUJET"]) ?>/<?= $rowBlog["ID_ARTICLE"] ?>" style="color: black;">
                        <i class="fa fa-paw fa-5x pt-5" aria-hidden="true" ></i> 
                        <p class="blog_texte">
                            <?php
                            $textCleanTagFromBd = strip_tags($rowBlog["TEXTE"]);
                            echo mb_substr($textCleanTagFromBd, 0, 180)." ...";
                            ?>
                        </p>
                    </a>
                    <a class="col-12 col-md-12 col-lg-4 col-xl-4 blog_box blog_image pl-0 pr-0" href="/blog/<?= strtogoogle($rowBlog["SUJET"]) ?>/<?= $rowBlog["ID_ARTICLE"] ?>" style="background-image: url(<?= $image ?>); background-repeat: no-repeat; background-position: center; background-size: cover;"></a> 
                    <a class="col-12 col-md-12 col-lg-4 col-xl-4 blog_box text-center text-decoration-none " href="/blog/<?= strtogoogle($rowBlog["SUJET"]) ?>/<?= $rowBlog["ID_ARTICLE"] ?>" style="color: black;">
                        <i class="pt-5 fa fa-comment-o fa-5x" aria-hidden="true" ></i> 
                        <p class="blog_texte">
                            <?php
                            $textCleanTagFromBd = strip_tags($rowBlog["TEXTE"]);
                            echo mb_substr($textCleanTagFromBd, 0, 180)." ...";
                            ?>
                        </p>
                    </a>	  
                    <?php $varControle = 2; ?>
                        
                <?php } ?>
    	<?php 
		    }//fin while
		    
		}//fin if
		?>
	</div>
    <div class="row mt-2">
        <div class="col-12 alert blog_lien" role="alert">
            <a href="/blog" class="blog_lien">
                <h4>
                    <i class="fal fa-info-circle  fa-lg"></i>  
                    <i>Voir davantage!</i> 
                </h4>
            </a>
        </div>         
    </div>
</div>

<!--========================= SESSION ZARABELLA ======================== -->
<session class="zarabella">
     <div class="container">
    	<div class="row">
            <div class="col-12 contente">
                <h2 class="zarabella_texte pt-3">Animo etc est fier partenaire de...</h2>
                <h3 class="zarabella_texte"> Fondation Zarabella</h3> 
                <p class="zarabella_texte"> Pour en savoir plus ou pour faire un don:<br>
                    <a href="//zarabella.ca/accueil" class="btn btn-success">
                        Cliquez ici
                    </a>
                </p>
            </div>
            <div class="col-12 contente">
                <img class="lazyload loaded" data-src="images/sponsor/zarabella_500x143.png" alt="image Zarabella" src="images/sponsor/zarabella_500x143.png" data-was-processed="true">
            </div>
		</div>
    </div>
</session>
