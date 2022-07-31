<?php
$L["Mode copie activée"] = "Mode copie activée";



$LIMITED_ACCESS = true;
if(has_rights("horaire_staf_edit")){
	$LIMITED_ACCESS = false;
}

if ( $LIMITED_ACCESS and isset($_POST["modecopie"]) ){
	unset($_POST["modecopie"]);
}


if (!$LIMITED_ACCESS){
	try{
		if ((isset($_POST["form_horaire"])) && ($_POST["form_horaire"] == "sendok")) {
			$arrayDB = array();
			$arrayDB["ID_STAFF"] = $_POST['ID_STAFF'];
			$arrayDB["DATE_DEBUT"] = $_POST['DATE_DEBUT'];
			$arrayDB["DATE_FIN"] = $_POST['DATE_FIN'];
			$arrayDB["DISPO_TOIL"] = $_POST['DISPO_TOIL'];
			if($arrayDB["DISPO_TOIL"] == "1"){
				$arrayDB["DISPO_TOIL_WEB"] = isset($_POST["DISPO_TOIL_WEB"]) ? "1" : null;
			} else {
				$arrayDB["DISPO_TOIL_WEB"] = null;
			}
			$arrayDB['PASDECHAT'] = !empty($_POST['PASDECHAT']) ? 1 : 0;
			$arrayDB["ID_MAG"] = $_SESSION["mag"];
			// end patch
			if (preg_match('#^\d+$#', $_POST['ID_STAFF_HORAIRE'])) {
				$arrayDB["ID_STAFF_HORAIRE"] = $_POST['ID_STAFF_HORAIRE'];
				faireUpdate_i($arrayDB, "STAFF_HORAIRE", "ID_STAFF_HORAIRE", $mysqli, 0);
				$reponse_message = "La plage horaire a été modifiée avec succès.";
			} else {
				faireInsert_i($arrayDB, "STAFF_HORAIRE", $mysqli, 0);
				$reponse_message = "La plage horaire a été ajoutée avec succès.";
			}
			$reponse = "success";
		}

		/* * *************************************************************************************DELETE HORAIRE */
		if ($_GET["delete"] == "horaire" && preg_match('#^\d+$#', $_GET['ID_STAFF_HORAIRE'])) {
			$resultStaff = query("SELECT * FROM STAFF_HORAIRE WHERE ID_STAFF_HORAIRE = ?",[$_GET['ID_STAFF_HORAIRE'],],$mysqli);
			if ($uneLigneStaff = $resultStaff->fetch_assoc() and $uneLigneStaff["ID_MAG"] == $_SESSION["mag"]) {
				$timeDebut = strtotime($uneLigneStaff["DATE_DEBUT"]);
				$mois = date("m",$timeDebut);
				$annee = date("Y",$timeDebut);
				faireDelete_i(array("ID_STAFF_HORAIRE" => $_GET['ID_STAFF_HORAIRE']), "STAFF_HORAIRE", $mysqli, 0);
			}
			redirect("index.php?p=horaire_staff&mois=$mois&annee=$annee");
			//redirect("?p=horaire_staff&ds=1");  ?!?!?
		}
	}catch(Exception $e){
		$reponse = "error";
		$reponse_message = "Une erreur est survenue pendant la modification de la plage horaire.";
	}
	
	
	if ( $_POST["mode"] == "casehoraireEmp" and preg_match('#^\d+$#',$_POST["ID_STAFF"]) and preg_match('#^\d{4}-\d{2}-\d{2}$#',$_POST["DEBUT_DATE"]) and preg_match('#^\d{2}:\d{2}:\d{2}$#',$_POST["DEBUT_HEURE"]) and preg_match('#^\d{4}-\d{2}-\d{2}$#',$_POST["FIN_DATE"]) and preg_match('#^\d{2}:\d{2}:\d{2}$#',$_POST["FIN_HEURE"])        ){
		if ( preg_match('#^\d+$#',$_POST["ID_STAFF_HORAIRE"]) ){
			$enonce = sprintf("select STAFF_HORAIRE.*
								from STAFF_HORAIRE
								join utilisateur_magasin    ON(STAFF_HORAIRE.ID_STAFF = utilisateur_magasin.id_utilisateur
															AND STAFF_HORAIRE.ID_MAG = ?)
								join utilisateur USING(id_utilisateur)
								WHERE STAFF_HORAIRE.ID_STAFF_HORAIRE = ?");
			$resultStaffHor = query($enonce,[$_SESSION["mag"],$_SESSION["mag"],$_POST["ID_STAFF_HORAIRE"]],$mysqli);
			$uneLigneTestProp = $resultStaffHor->fetch_assoc();
		}

		//Voir si employé du magasin
		$resultstaff = query("select *
							from utilisateur
							join utilisateur_magasin ON(utilisateur.id_utilisateur = utilisateur_magasin.id_utilisateur)
							where utilisateur_magasin.id_magasin = ?
								AND utilisateur.id_utilisateur = ?
							group by utilisateur.id_utilisateur",[$_SESSION["mag"],$_POST["ID_STAFF"]],$mysqli);
		if( $rowstaff = $resultstaff->fetch_assoc() ){
			$arrayDB = array();
			$arrayDB["ID_STAFF"] = $rowstaff["id_utilisateur"];
			$arrayDB["DATE_DEBUT"] = $_POST["DEBUT_DATE"] . " " . $_POST["DEBUT_HEURE"];
			$arrayDB["DATE_FIN"] = $_POST["FIN_DATE"] . " " . $_POST["FIN_HEURE"];
			$arrayDB["DISPO_TOIL"] = ($_POST["DISPO_TOIL"]=="1")?"1":null;
			if($arrayDB["DISPO_TOIL"] == "1"){
			    $arrayDB["DISPO_TOIL_WEB"] = isset($_POST["DISPO_TOIL_WEB"]) ? "1" : null;
			} else {
			    $arrayDB["DISPO_TOIL_WEB"] = null;
			}
			$arrayDB["ID_MAG"] = $_SESSION["mag"];
			$arrayDB['PASDECHAT'] = !empty($_POST['PASDECHAT']) ? 1 : 0;
			if ( $uneLigneTestProp ){
				$arrayDB["ID_STAFF_HORAIRE"] = $uneLigneTestProp["ID_STAFF_HORAIRE"];
				faireupdate_i($arrayDB,"STAFF_HORAIRE","ID_STAFF_HORAIRE",$mysqli,0);
			} else {
				faireInsert_i($arrayDB,"STAFF_HORAIRE",$mysqli,0);
			}
		}
	}


	if ( $_POST["mode"] == "FAIRE_COPIE" ){
		$timeStartCopie = strtotime($_POST["COPIE_FROM"] . " 00:00:00");
		$timeEndCopie = strtotime("+7 day",$timeStartCopie) - 1;


		$resultHoraireStaff = query(" select STAFF_HORAIRE.*, utilisateur.id_utilisateur
										from STAFF_HORAIRE
											 join utilisateur_magasin ON(STAFF_HORAIRE.ID_STAFF = utilisateur_magasin.id_utilisateur
																	AND STAFF_HORAIRE.ID_MAG = utilisateur_magasin.id_magasin)
											 join utilisateur USING(id_utilisateur)
									   where ? <= STAFF_HORAIRE.DATE_DEBUT and STAFF_HORAIRE.DATE_DEBUT <= ?
										 and STAFF_HORAIRE.ID_MAG = ?
									order by STAFF_HORAIRE.DATE_DEBUT asc", [date("Y-m-d H:i:s",$timeStartCopie),date("Y-m-d H:i:s",$timeEndCopie),$_SESSION["mag"]],$mysqli);
		$listLignesHoraireStaff = array();
		while( $uneLigneHoraireStaff = $resultHoraireStaff->fetch_assoc() ){
			foreach($_POST as $key=>$value){
				$matches = null;
				if ( preg_match('#^COPIE_TO_(\d{4}-\d{2}-\d{2})$#',$key,$matches) ){
					$diff = strtotime($matches[1] . " 00:00:00") - strtotime($_POST["COPIE_FROM"]." 00:00:00");
					$nbSemaine = round($diff / (60*60*24*7));

					$arrayDB = array();
					$arrayDB["DATE_DEBUT"] = date( "Y-m-d H:i:s", strtotime($nbSemaine." week",strtotime($uneLigneHoraireStaff["DATE_DEBUT"]))  );
					$arrayDB["DATE_FIN"] = date( "Y-m-d H:i:s", strtotime($nbSemaine." week",strtotime($uneLigneHoraireStaff["DATE_FIN"])) );
					$arrayDB["DISPO_TOIL"] = $uneLigneHoraireStaff["DISPO_TOIL"];
					$arrayDB["DISPO_TOIL_WEB"] = $uneLigneHoraireStaff["DISPO_TOIL_WEB"];
					$arrayDB["ID_STAFF"] = $uneLigneHoraireStaff["id_utilisateur"];
					$arrayDB["ID_MAG"] = $_SESSION["mag"];
					$arrayDB['PASDECHAT'] = $uneLigneHoraireStaff['PASDECHAT'];
					faireInsert_i($arrayDB,"STAFF_HORAIRE",$mysqli,0);
				}
			}
		}

		redirect("index.php?p=horaire_staff&mois={$_GET["mois"]}&annee={$_GET["annee"]}");
		return;
	}
}

$jourAff = ($_GET["jour"]!="")?$_GET["jour"]:"01";
$moisAff = ($_GET["mois"]!="")?$_GET["mois"]:date("m");
$anneeAff = ($_GET["annee"]!="")?$_GET["annee"]:date("Y");
$nbJourMois = cal_days_in_month(CAL_GREGORIAN,$moisAff,$anneeAff);
$timeDemander = strtotime(sprintf("%d-%02d-01 00:00:00",$anneeAff,$moisAff));

$time_premierJourMois = strtotime(sprintf("%d-%02d-01 00:00:00",$anneeAff,$moisAff));
//Déterminé le premier lundi du calendrier
if ( date( "w", $time_premierJourMois ) != "1" ){
	$time_premierJourMois = strtotime("last Monday", $time_premierJourMois);
}

$time_dernierJourMois = strtotime(sprintf("%d-%02d-%02d 00:00:00",$anneeAff,$moisAff,$nbJourMois));
//Déterminé le premier lundi du calendrier
if ( date( "w", $time_dernierJourMois ) != "0" ){
	$time_dernierJourMois = strtotime("next Sunday", $time_dernierJourMois);
}
//$time_dernierJourMois = strtotime("+7 day",$time_dernierJourMois);
$listDay = [0,1,2,3,4,5,6];
?>
<section id="main" class="main-wrap bgc-white-darkest" role="main">
	<!-- Start SubHeader-->
	<div id="page-title-wrap" class="page-title-wrap clearfix" style="background: #f4f9fc">
		<h1 class="page-title pull-left fs-4 fw-light">
			<i class="fa fa-calendar icon-mr fs-4"></i>
			<span class="hidden-xs-down"> Horaires de travail</span>
		</h1>
		<?php if (!$LIMITED_ACCESS){ ?>
		<div class="smart-links">
			<ul class="nav" role="tablist">
				<li class="nav-item">
					<?php if ($_POST["modecopie"]=="1"){ ?>
						<a class="nav-link clear-style" href="javascript:activeCopy(0)"><i class="fa fa-copy"></i> <?php echo $L['Dactivecopie'];?></a>
					<?php } else { ?>
						<a class="nav-link clear-style" href="javascript:activeCopy(1)"><i class="fa fa-copy"></i> <?php echo $L['Activemodecopie'];?></a>
					<?php } ?>
				</li>
			</ul>
		</div>
		<?php } ?>
	</div>
	<style>
	   .tableHoraire td *{
             -webkit-transform: translate3d(0,0,0);
         }
	</style>
	<div id="listGrille" class="bgc-white table-responsive">
		<script>
		function onChangeRadio(monIradio){
			$('.COPIE_TO_CHECK').attr('disabled', false);
			$('.COPIE_TO_CHECK').css('visibility', 'visible');

			var monCheck = getEl( "COPIE_TO_" + monIradio.value );
			monCheck.disabled = true;
			monCheck.style.visibility = 'hidden';
		}
		function activeCopy(v){
			$("#formHoraireStaff").find("input[name=modecopie]").val(v);
			$("#formHoraireStaff").submit();
		}

		function submitGet(aSrc,event){
			event.preventDefault();
			$("#formHoraireStaff").attr("action",aSrc.href).submit();
		}
		</script>
		<div class="outer-container" style="margin:auto; text-align:left;">
			<form method="post" id="formHoraireStaff">
			<input type="hidden" name="modecopie" value="<?= $_POST["modecopie"] ?>" />
			<table class="tableHoraire table fs-7">
				<tr border="0" style="">
					<?php
					if ($_POST["modecopie"]=="1"){
						?>
						<th valign="middle" rowspan="2" style="border-top:0;vertical-align:middle;width:1%;min-width:auto;"><?php echo $L['copier'];?></th>
						<th valign="middle" rowspan="2" style="border-top:0;vertical-align:middle;width:1%;min-width:auto;"><?php echo $L['coller'];?></th>
						<?php
					}
					?>
					<td valign="middle" class="p-1 pl-3" border="0" style="border:0;background-color: #f4f9fc !important;vertical-align:middle;text-align:left">
						<?php $timeMoisAvant = strtotime("last month",$timeDemander);?>
						<a href="index.php?p=horaire_staff&mois=<?php echo date("m",$timeMoisAvant) ?>&annee=<?php echo date("Y",$timeMoisAvant) ?>" onclick="submitGet(this,event)" title="<?php echo formatDateUTF8($timeMoisAvant,'%B') ?>"><span class="fa fa-2x fa-chevron-left"></span></a>
					</td>
					<td valign="middle" border="0" colspan="5" style="border:0;background-color: #f4f9fc !important;vertical-align:middle;text-align:center">
							<select class="form-control form-control-lg fs-4 text-center" style="padding: 4px 10px;width: 200px;margin:auto;" onchange="onchangeMoisHoraireEmp(this)">
								<?php 
								for($i=-6; $i <= 24; $i++){
									?>
									<option value="<?php echo strftime('%Y-%m', strtotime($i." month",$timeDemander)) ?>" <?php if($i == 0 ){ echo 'selected'; }?>><?php echo utf8_encode(strftime('%B %Y', strtotime($i." month",$timeDemander)));?></option>
									<?php 
								}?>
							</select>
							<script>
							function onchangeMoisHoraireEmp(srcOb){
								var dateAAMM = srcOb.value.split("-");
								var href = "index.php?p=horaire_staff&mois="+dateAAMM[1]+"&annee="+dateAAMM[0];
								$("#formHoraireStaff").attr("action",href).submit();
							}
							</script>
							<?php /* echo formatDateUTF8(sprintf("%s-%s-01 00:00:01",$anneeAff,$moisAff),"%B") ?> <?php echo $anneeAff*/ ?>
						<h4>
							<?php if ($_POST["modecopie"]=="1"){ echo $L["Mode copie activée"]; } ?>
						</h4>
					</td>
					<td valign="middle" class="p-1 pr-3" border="0" style="border:0;background-color: #f4f9fc !important;vertical-align:middle;text-align:right">
						<?php $timeMoisApres = strtotime("next month",$timeDemander);?>
						<a href="index.php?p=horaire_staff&mois=<?php echo date("m",$timeMoisApres) ?>&annee=<?php echo date("Y",$timeMoisApres) ?>" onclick="submitGet(this,event)" title="<?php echo formatDateUTF8($timeMoisApres,'%B') ?>"><span class="fa fa-2x fa-chevron-right"></span></a>
					</td>
				</tr>
				<tr>
					<?php
					foreach ($listDay as $iDayWeek){
						$timeCase = strtotime("+".$iDayWeek." day",$time_premierJourMois);
						?>
						<th><?php echo utf8_encode( strftime('%A', $timeCase) ) ?></th>
						<?php
					}
					?>
				</tr>
				<?php
				
				//Si mode copie
				if ( $_POST["modecopie"] == "1" ){
					if ( $_POST["COPIE_FROM"] != "" ){
						$fromTime = strtotime($_POST["COPIE_FROM"]);
						if ( $fromTime < $time_premierJourMois ){
							?>
							<tr>
								<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center radio dynamic checkbox"><input type="radio" onchange="onChangeRadio(this)" name="COPIE_FROM" value="<?php echo date("Y-m-d",$fromTime); ?>" checked /></span></td>
								<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center dynamic checkbox"><input type="checkbox" class="COPIE_TO_CHECK" name="COPIE_TO_<?php echo date("Y-m-d",$fromTime); ?>" id="COPIE_TO_<?php echo date("Y-m-d",$fromTime); ?>" value="1" /></span></td>
								<td colspan="7">
									<div class="alert alert-success center mb-0">
									<?php 
									echo sprintf( L( 'Semaine du %1$s au %2$s') , date("Y-m-d",$fromTime), date("Y-m-d",strtotime("+6 days",$fromTime)) );
									?>
									</div>
								</td>
							</tr>
							<?php
						}
					}
					
					foreach($_POST as $key=>$value){
						$matches = null;
						if ( preg_match('#^COPIE_TO_(\d{4}-\d{2}-\d{2})$#',$key,$matches) ){
							$copietoTime = strtotime($matches[1]);
							if ( $copietoTime < $time_premierJourMois ){
								?>
								<tr>
									<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center radio dynamic checkbox"><input type="radio" onchange="onChangeRadio(this)" name="COPIE_FROM" value="<?php echo date("Y-m-d",$copietoTime); ?>" /></span></td>
									<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center dynamic checkbox"><input type="checkbox" class="COPIE_TO_CHECK" name="COPIE_TO_<?php echo date("Y-m-d",$copietoTime); ?>" id="COPIE_TO_<?php echo date("Y-m-d",$copietoTime); ?>" value="1" checked /></span></td>
									<td colspan="7">
										<div class="alert alert-success center mb-0">
										<?php 
										echo sprintf( L( 'Semaine du %1$s au %2$s') , date("Y-m-d",$copietoTime), date("Y-m-d",strtotime("+6 days",$copietoTime)) );
										?>
										</div>
									</td>
								</tr>
								<?php
							}
						}
					}
				}
				
				
				
				
				$nbWeek = 0;
				$lundiTime = $time_premierJourMois;
				while ($lundiTime < $time_dernierJourMois){
					$nbWeek++;
					?>
					<tr>
						<?php
						if ($_POST["modecopie"]=="1"){
							?>
							<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center radio dynamic checkbox"><input type="radio" onchange="onChangeRadio(this)" name="COPIE_FROM" value="<?php echo date("Y-m-d",$lundiTime); ?>" <?= ($_POST["COPIE_FROM"]==date("Y-m-d",$lundiTime))?"checked":"" ?> /></span></td>
							<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center dynamic checkbox"><input type="checkbox" class="COPIE_TO_CHECK" name="COPIE_TO_<?php echo date("Y-m-d",$lundiTime); ?>" id="COPIE_TO_<?php echo date("Y-m-d",$lundiTime); ?>" value="1" <?= ($_POST["COPIE_TO_".date("Y-m-d",$lundiTime)]=="1")?"checked":"" ?> /></span></td>
							<?php
						}

						for ($iDayWeek=0;$iDayWeek<7;$iDayWeek++){

							$timeCase = strtotime("+".$iDayWeek." day",$lundiTime);
							?>
							<td class="caseCal <?php if(date("m",$timeCase)==$moisAff){echo "caseCalCeMois";} ?>">
								<div style="position:relative">
									<div class="caseCalTitle">
										<?php
										$format = '%e %b %Y';
										if ( date("m",$timeCase)==$moisAff ){
											$format = '%e';
										} elseif ( date("Y",$timeCase)==$anneeAff ){
											$format = '%e %b';
										}
										echo utf8_encode(  strftime( $format, $timeCase)  );

										if ($_POST["modecopie"]!="1" and !$LIMITED_ACCESS){?>
										<div style="float:left; margin-left:5px">
											<a data-modal-url="ajax/modals/horaire_edit.php?date=<?php echo date("Y-m-d",$timeCase) ?>" href="javascript:;" class="ajaxPopup">
												<span class="fa fa-plus-square fa-lg"></span>
											</a>
										</div>
										<?php }?>

									</div>

									<div class="caseContHoraireEmpCont">
										<?php
										$enonce = sprintf("select STAFF_HORAIRE.*, utilisateur.prenom as PRENOM, utilisateur.nom as NOM, utilisateur.couleur as COULEUR
															from STAFF_HORAIRE
																join utilisateur_magasin ON(utilisateur_magasin.id_utilisateur = STAFF_HORAIRE.ID_STAFF and utilisateur_magasin.id_magasin = STAFF_HORAIRE.ID_MAG)
																join utilisateur using(id_utilisateur)
															where (   ('%s' <= STAFF_HORAIRE.DATE_DEBUT and STAFF_HORAIRE.DATE_DEBUT <= '%s')
																or ('%s' <= STAFF_HORAIRE.DATE_FIN and STAFF_HORAIRE.DATE_FIN   <= '%s')
																or (STAFF_HORAIRE.DATE_DEBUT <= '%s' and '%s' <= STAFF_HORAIRE.DATE_FIN)  )
															and utilisateur_magasin.id_magasin = %s
														order by STAFF_HORAIRE.DATE_DEBUT asc",
														date("Y-m-d",$timeCase)." 00:00:00",
														date("Y-m-d",$timeCase)." 23:59:59",
														date("Y-m-d",$timeCase)." 00:00:00",
														date("Y-m-d",$timeCase)." 23:59:59",
														date("Y-m-d",$timeCase)." 00:00:00",
														date("Y-m-d",$timeCase)." 23:59:59",
														$_SESSION["mag"] );
										$resultHoraireStaff = $mysqli->query($enonce) or die("MYSQL_ERROR:".__LINE__);
										$listLignesHoraireStaff = array();
										while( $uneLigneHoraireStaff = $resultHoraireStaff->fetch_assoc() ){
											$colorStaff = ($uneLigneHoraireStaff['COULEUR']=="")?"000000":$uneLigneHoraireStaff['COULEUR'];

											if ($_POST["modecopie"]!="1" and !$LIMITED_ACCESS){ ?>
												<a data-modal-url="ajax/modals/horaire_edit.php?ID_STAFF_HORAIRE=<?php echo $uneLigneHoraireStaff["ID_STAFF_HORAIRE"] ?>" href="javascript:;" class="ajaxPopup caseContHoraireEmpA">
											<?php }?>

												<div class="caseContHoraireEmp" style="background-color:#<?php echo $colorStaff ?>; ">
													<div class="caseContHoraireEmpName">
														<?php
														echo $uneLigneHoraireStaff["PRENOM"]." ".$uneLigneHoraireStaff["NOM"];
														?>
													</div>
													<div class="caseContHoraireEmpHeure">
														<?php

														$timeDebutCase = strtotime( date("Y-m-d",$timeCase)." 00:00:00" );
														$timeFinCase = strtotime( date("Y-m-d",$timeCase)." 23:59:59" );

														$timeDebutCaseStaff = strtotime($uneLigneHoraireStaff["DATE_DEBUT"]);
														$timeFinCaseStaff = strtotime($uneLigneHoraireStaff["DATE_FIN"]);
                                                        $text2show = '';
														if ( $timeDebutCase < $timeDebutCaseStaff and $timeFinCaseStaff < $timeFinCase ){
														    $text2show .= date("H:i",$timeDebutCaseStaff) . " - " . date("H:i",$timeFinCaseStaff);
														} elseif ( $timeDebutCase < $timeDebutCaseStaff ){
														    $text2show .= date("H:i",$timeDebutCaseStaff) . " - ";
														} elseif ( $timeFinCaseStaff < $timeFinCase ){
														    $text2show .= " - " . date("H:i",$timeFinCaseStaff);
														} else {
														    $text2show .= " - ";
														}
														echo $text2show;
														?>
													</div>
													<?php if($uneLigneHoraireStaff["DISPO_TOIL"]=="1"){ ?>
														<div style="position:absolute; right:5px; top:8px;">
															<span class="fa fa-scissors fa-lg"></span>
															<?php 
															if($uneLigneHoraireStaff['DISPO_TOIL_WEB'] == '1'){
															    ?>
															    <span class="fa fa-globe fa-lg"></span>
															    <?php 
															}
															if($uneLigneHoraireStaff['PASDECHAT'] == '1'){
															    echo '<del style="border-radius: 50%;" class="fa fa-github fa-lg text-white bg-danger"></del>';
															    /*?> 
															    <style>
															        .nocat-svg path{
															            fill: #ffffff;
															        }
															    </style>
															    <svg class='nocat-svg' style="display:inline;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1000 1000" width="20" height="20" enable-background="new 0 0 1000 1000" xml:space="preserve">
                                                                <g><g><g><path d="M671.6,744.8l-39.3-39.3c-39.1,13.9-83.8,21.9-131.2,21.9c-71.8,0-137.1-18.2-186.5-47.9l53.9-14.1c13.8-3.6,22.1-17.8,18.5-31.6c-3.6-13.8-17.8-22.1-31.6-18.5L264,639.2c-15.5-16.8-27.2-35.2-34-55H364c14.3,0,25.9-11.6,25.9-25.9c0-14.3-11.6-25.9-25.9-25.9H223.7c1.7-18.1,7.2-35.9,17-53l5.2-9.2l-33.2-127.8l76.2,19.8l-71.8-71.9l-3.9-0.5c-29.2,0-53,23.8-53,53l30.4,120.3c-11.2,22.2-17.3,45.4-18.8,69.1h-17.2c-14.3,0-25.9,11.6-25.9,25.9c0,14.3,11.6,25.9,25.9,25.9h21.8c6,24.6,17.2,47.9,33.1,69.3l-35.5,9.3c-13.8,3.6-22.1,17.8-18.5,31.6c3.1,11.6,13.6,19.3,25,19.3c2.2,0,4.4-0.3,6.6-0.8l63.2-16.6c60.5,50.6,150.4,82.9,250.7,82.9C563.5,779.2,621.7,766.5,671.6,744.8z"/><path d="M501,359.6c44.2,0,86.5,6.8,125.8,20.2l7.4,2.5l153-39.8L754.6,468l5.5,9.3c10.5,17.8,16.5,36.3,18.2,55.1H638.2c-8.8,0-16.6,4.4-21.3,11.2l40.6,40.6H772c-6.8,19.7-18.4,38.2-34,54.9l-34.8-9.1l21.9,21.9l36,36l14.7,14.7l39.1,10.3c2.2,0.6,4.4,0.8,6.6,0.8c11.5,0,22-7.7,25-19.3c3.6-13.8-4.7-28-18.5-31.6l-35.5-9.3c15.8-21.3,27-44.7,33.1-69.2h21.9c14.3,0,25.9-11.6,25.9-25.9c0-14.3-11.6-25.9-25.9-25.9h-17.3c-1.6-24.7-8.1-48.9-20.2-71.9l28.9-111.1l0.8-6.5c0-29.2-23.8-53-53-53l-150.7,38.3c-42.5-13.6-87.9-20.4-135.1-20.4c-37,0-72.9,4.4-107.2,12.8l43.9,43.9C458.3,361.2,479.4,359.6,501,359.6z"/><path d="M990,500c0-270.2-219.8-490-490-490C229.8,10,10,229.8,10,500c0,270.2,219.8,490,490,490C770.2,990,990,770.2,990,500z M500,61.8c241.6,0,438.2,196.6,438.2,438.2c0,111.5-41.9,213.4-110.8,290.8l-26.3-26.3l-59.9-59.9l-15-15l-21.4-21.4L653.6,617L407.2,370.6l-42.1-42.1L235.5,198.9l-26.3-26.3C286.6,103.7,388.5,61.8,500,61.8z M61.8,500c0-111.5,41.9-213.4,110.8-290.8l26.3,26.3l67.3,67.3l72.3,72.3L658.3,695l38,38l68.1,68.1l26.3,26.3C713.4,896.3,611.5,938.2,500,938.2C258.4,938.2,61.8,741.6,61.8,500z"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></g>
                                                                </svg>*/?>
															    <?php
														    }?>
														</div>
													<?php } ?>
												</div>
											<?php if ($_POST["modecopie"]!="1" and !$LIMITED_ACCESS){ echo '</a>'; }
										} ?>
									</div>

								</div>
							</td>
							<?php
						}

						?>
					</tr>
					<?php
					$lundiTime = strtotime("next monday",$lundiTime);
				}
				
				
				
				//Si mode copie
				if ( $_POST["modecopie"] == "1" ){
					if ( $_POST["COPIE_FROM"] != "" ){
						$fromTime = strtotime($_POST["COPIE_FROM"]);
						if ( $fromTime >= $lundiTime){
							?>
							<tr>
								<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center radio dynamic checkbox"><input type="radio" onchange="onChangeRadio(this)" name="COPIE_FROM" value="<?php echo date("Y-m-d",$fromTime); ?>" checked /></span></td>
								<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center dynamic checkbox"><input type="checkbox" class="COPIE_TO_CHECK" name="COPIE_TO_<?php echo date("Y-m-d",$fromTime); ?>" id="COPIE_TO_<?php echo date("Y-m-d",$fromTime); ?>" value="1" /></span></td>
								<td colspan="7">
									<div class="alert alert-success center mb-0">
									<?php 
									echo sprintf( L( 'Semaine du %1$s au %2$s') , date("Y-m-d",$fromTime), date("Y-m-d",strtotime("+6 days",$fromTime)) );
									?>
									</div>
								</td>
							</tr>
							<?php
						}
					}
					
					foreach($_POST as $key=>$value){
						$matches = null;
						if ( preg_match('#^COPIE_TO_(\d{4}-\d{2}-\d{2})$#',$key,$matches) ){
							$copietoTime = strtotime($matches[1]);
							if ( $copietoTime >= $lundiTime){
								?>
								<tr>
									<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center radio dynamic checkbox"><input type="radio" onchange="onChangeRadio(this)" name="COPIE_FROM" value="<?php echo date("Y-m-d",$copietoTime); ?>" /></span></td>
									<td style="vertical-align:middle;text-align:center;width:1%;min-width:auto;"><span class="ui text-center dynamic checkbox"><input type="checkbox" class="COPIE_TO_CHECK" name="COPIE_TO_<?php echo date("Y-m-d",$copietoTime); ?>" id="COPIE_TO_<?php echo date("Y-m-d",$copietoTime); ?>" value="1" checked /></span></td>
									<td colspan="7">
										<div class="alert alert-success center mb-0">
										<?php 
										echo sprintf( L( 'Semaine du %1$s au %2$s') , date("Y-m-d",$copietoTime), date("Y-m-d",strtotime("+6 days",$copietoTime)) );
										?>
										</div>
									</td>
								</tr>
								<?php
							}
						}
					}
				}
				
				
				?>
			</table>
			<?php if ($_POST["modecopie"]=="1"){ 
				/* FAIRE_COPIE */
				?>
				<input type="hidden" name="mode" value="" />
				<div style="text-align:right;" class="mb-1 mt-1">
					<button type="button" class="btn btn-success" onclick="$('#formHoraireStaff').find('input[name=mode]').val('FAIRE_COPIE');$('#formHoraireStaff').submit();"><i class="fa fa-copy"></i> <?php echo $L['docpoy'];?></button>
				</div>
				<?php
			} ?>
			</form>
		</div>
	</div>
</section>