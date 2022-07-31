<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc;">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-address-book-o icon-mr fs-4"></i>
			<span class="hidden-xs-down"><?php echo L('',o); ?></span>
		</h1>
	</div>
	<!-- End SubHeader-->
	<!-- Start Content-->
	<div class="row pl-3 pr-3 pb-3 mt-3">
		<section class="panel-wrap panel-grid-item col-xl-12">
			<!--Start Panel-->
			<div class="panel bgc-white-dark">
				<div class="panel-body panel-body-p pt-5">
				<?php 
				$DEBUG_DB = 2;
				set_time_limit(300);
				$mysqli->autocommit(false);
				$resultRace = query("select ID_RACE, COUPE_PRIX, TONTE_PRIX, LS_PRIX 
				                     from RACE  
				                     where COUPE_PRIX is not null and COUPE_PRIX != ''
				                     and TONTE_PRIX is not null  and TONTE_PRIX != ''
				                     and LS_PRIX is not null  and LS_PRIX != ''
				                      ",[],$mysqli);
				/*
                    select ID_RACE, COUPE_PRIX, TONTE_PRIX, LS_PRIX from RACE where COUPE_PRIX is not null and COUPE_PRIX != '' and TONTE_PRIX is not null and TONTE_PRIX != '' and LS_PRIX is not null and LS_PRIX != '' 
				*/
				//"ID_RACE 52: 85"
				while ( $uneLigneRace = $resultRace->fetch_assoc() )
				{
					$array_race = [];  
					$array_race["ID_RACE"] = $uneLigneRace["ID_RACE"];
					$array_race["race_date_update"] = date("Y-m-d H:i:s");
					
					//============================== COUPE_PRIX ===========================================
					
					if ( $uneLigneRace["COUPE_PRIX"]) 
					{
					    $array_race["COUPE_PRIX"] = explode("-",$uneLigneRace["COUPE_PRIX"]);
					    //Cas Un seul prix
    					if (count( $array_race["COUPE_PRIX"]  ) == 1 ) 
    					{
    						$nouveauPrix =  5 + (int)$array_race["COUPE_PRIX"][0];
    						$val_final = (string)$nouveauPrix;
    						$array_race["COUPE_PRIX"] = $val_final;
    					//Cas deux prix
    					}else if (count( $array_race["COUPE_PRIX"]  ) == 2)
    					{
    					   $val1 = 5 + (int)$array_race["COUPE_PRIX"][0];
    					   $val2 = 5 + (int)$array_race["COUPE_PRIX"][1];
    					   $val_final = $val1."-".$val2;   
    					   $array_race["COUPE_PRIX"] = $val_final;
    					}
					}
					//============================ TONTE_PRIX =============================================
					
					if ( $uneLigneRace["TONTE_PRIX"]) 
					{
					    $array_race["TONTE_PRIX"] = explode("-",$uneLigneRace["TONTE_PRIX"]);
				
    					if (count( $array_race["TONTE_PRIX"]  ) == 1 ) 
    					{
    						$nouveauPrix =  5 + (int)$array_race["TONTE_PRIX"][0];
    						$val_final = (string)$nouveauPrix;
    						$array_race["TONTE_PRIX"] = $val_final;
    				
    					}else if (count( $array_race["TONTE_PRIX"]  ) == 2)
    					{
    					   $val1 = 5 + (int)$array_race["TONTE_PRIX"][0];
    					   $val2 = 5 + (int)$array_race["TONTE_PRIX"][1];
    					   $val_final = $val1."-".$val2;   
    					   $array_race["TONTE_PRIX"] = $val_final;
    					}
					}
                    //============================ LS_PRIX =============================================
					if ( $uneLigneRace["LS_PRIX"]) 
					{
					    $array_race["LS_PRIX"] = explode("-",$uneLigneRace["LS_PRIX"]);
					   
    					if (count( $array_race["LS_PRIX"]  ) == 1 ) 
    					{
    						$nouveauPrix =  5 + (int)$array_race["LS_PRIX"][0];
    						$val_final = (string)$nouveauPrix;
    						$array_race["LS_PRIX"] = $val_final;
    				
    					}else if (count( $array_race["LS_PRIX"]  ) == 2)
    					{
    					   $val1 = 5 + (int)$array_race["LS_PRIX"][0];
    					   $val2 = 5 + (int)$array_race["LS_PRIX"][1];
    					   $val_final = $val1."-".$val2;   
    					   $array_race["LS_PRIX"] = $val_final;
    					}
					}
					//echo '<pre>', print_r( "ID_RACE ". $uneLigneRace["ID_RACE"]."<br>") , '</pre>';
					echo '<pre>', print_r( $array_race) , '</pre>';
					//die();
				    faireUpdate_i($array_race,"RACE","ID_RACE",$mysqli,2);
				}
				//$mysqli->commit();
				?>
				</div>
			</div>
		</section>
	</div>
</section>