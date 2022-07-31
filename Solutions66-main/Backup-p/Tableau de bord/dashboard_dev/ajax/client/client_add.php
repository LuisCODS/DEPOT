<?php
require_once __DIR__."/../../req/init.php";
?>
<form method="POST" onsubmit="clientHandler(this,event)" class="horizontal-form">
	<div class="ajax_response">

	</div>
	<div class="form-body">
		<h4 class="form-section"><?php echo $L["infoperso"];?></h4>
		<div class="row">
			<div class="col-md-5">
				<div class="form-group">
					<label class="control-label"><?php echo $L["prenom"];?></label>
					<input name="PRENOM" class="form-control" type="text" value="<?php echo ucwords($rowclient['PRENOM'])?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-5">
				<div class="form-group">
					<label class="control-label"><?php echo $L["Nom"];?></label>
					<input name="NOM" class="form-control" type="text"  value="<?php echo ucwords($rowclient['NOM'])?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label"><?php echo $L["sexe"];?></label>
					<select class="form-control" name="sexe">
						<option selected disabled><?php echo $L["choisir"] ?></option>
						<option <?php if($rowclient["sexe"]=="f"){echo "selected ";} ?> value="f"><?php echo $L["femme"] ?></option>
						<option <?php if($rowclient["sexe"]=="h"){echo "selected ";} ?> value="h"><?php echo $L["homme"] ?></option>
					</select>
				</div>
			</div>
			<!--/span-->
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?php echo $L["telephone"];?></label>
					<input name="TEL_MAISON" id="TEL_MAISON" class="form-control" type="text" placeholder="XXX-XXX-XXXX" value="<?php echo $rowclient['TEL_MAISON']?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?php echo $L["Cellulaire"];?></label>
					<input name="CELL" class="form-control" type="text" placeholder="XXX-XXX-XXXX" value="<?php echo $rowclient['CELL']?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?php echo $L["courriel"];?></label>
					<input style="display:none" type="text" name="usernameremembered" />
					<input style="display:none" type="password" name="passwordremebmered" />
					<input autocomplete='new-password' name="EMAIL" class="form-control" type="text" value="<?php echo $rowclient['EMAIL']?>" />
					<small style="line-height: 1.1;">Le courriel est obligatoire pour assigner une carte Avantages au client.</small>
				</div>
			</div>
			<!--/span-->
		</div>
		<hr />
		<h4 class="form-section"><?php echo $L["infoperso"];?> 2</h4>
		<div class="row">
			<div class="col-md-5">
				<div class="form-group">
					<label class="control-label"><?php echo $L["prenom"];?></label>
					<input name="PRENOM2" class="form-control" type="text" value="<?php echo ucwords($rowclient['PRENOM2'])?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-5">
				<div class="form-group">
					<label class="control-label"><?php echo $L["Nom"];?></label>
					<input name="NOM2" class="form-control" type="text" value="<?php echo ucwords($rowclient['NOM2'])?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-2">
				<div class="form-group">
					<label class="control-label"><?php echo $L["sexe"];?></label>
					<select class="form-control" name="sexe2">
						<option selected disabled><?php echo $L["choisir"] ?></option>
						<option <?php if($rowclient["sexe2"]=="f"){echo "selected ";} ?> value="f"><?php echo $L["femme"] ?></option>
						<option <?php if($rowclient["sexe2"]=="h"){echo "selected ";} ?> value="h"><?php echo $L["homme"] ?></option>
					</select>
				</div>
			</div>
			<!--/span-->
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?php echo $L["telephone"];?></label>
					<input name="TEL_2" id="TEL_2" class="form-control" type="text" placeholder="XXX-XXX-XXXX" value="<?php echo $rowclient['TEL_2']?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?php echo $L["Cellulaire"];?></label>
					<input style="display:none;" type="text" name="usernameremembered3"/>
					<input autocomplete='new-password' name="CELL_2" class="form-control" type="text" placeholder="XXX-XXX-XXXX" value="<?php echo $rowclient['CELL_2']?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?php echo $L["courriel"];?></label>
					<input style="display:none;" type="text" name="usernameremembered2"/>
					<input style="display:none" type="password" name="passwordremebmered2" />
					<input autocomplete='new-password' name="EMAIL2" class="form-control" type="text" value="<?php echo $rowclient['EMAIL2']?>" />
				</div>
			</div>
		</div>
		<hr />
		
		<h4 class="form-section">Mot de passe d'accès Web</h4>
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<input style="display:none" type="password" name="passwordfak2"/>
					<label class="control-label">Modifier le mot de passe d'accès Web</label>
					<?php
					$password = "";
					for($i = 0;$i < strlen($rowclient["PASSWORD"]);$i++){
						$password .= "*";
					}
					?>
					<style>
						/* Change the white to any color ;) */
						input:-webkit-autofill,
						input:-webkit-autofill:hover,
						input:-webkit-autofill:active,
						input:-webkit-autofill:focus {
							-webkit-box-shadow: 0 0 0px 1000px white inset !important;
						}
					</style>
					<div id="clientpass">
						<button type="button" class="btn btn-danger" onclick="getClientPassInput();">Définir le mot de passe...</button>
					</div>
					<p><small>Ne rien mettre pour garder le mot de passe intact. Si le champ n'a pas un texte indicatif composé d'étoiles et est vide, cela signifie que le client n'a pas d'accès Web.</small></p>
					<script>
						function getClientPassInput(){
							// workaround to disable Chrome and Safari autocompletin.
							// not needed for IE & Firefox as autocomplete='off' does the job
							setTimeout(function(){
								// input is temporarly a text field but we'll change later
								// this prevents LastPass from converting field to password one
								$("#clientpass").html("<input webkitautocomplete='off' mozautocomplete='off' autocomplete='new-password' name='PASSWORD' class='form-control' type='text' placeholder='<?= $password ?>' value='' />");
							}, 100);
							// define a variable to set if user has entered field
							var userInput = false;
							setTimeout(function(){
								// define field as password one then listen to the input event Webkit
								// triggers when completing field
								$("#clientpass input").prop("type","password").on("input",function(e){
									// if no user input on the field Chrome did his shit so erase field
									if(!userInput){
										$(this).val("");
									}
								// then listen to keyboard for user input and unlocks the field.
								// We also listen the click event so user can choose his saved passwords from natuve Webkit dropdown.
								// But iOS Safari doesn't fire keydown event for text input in UIWebView
								// but does in WKWebView, only workaround is to use a touch event as reference
								}).on("keydown click touchstart",function(e){
									userInput = true;
								});
							}, 200);
						}
					</script>
				</div>
			</div>
			<!--/span-->
		</div>
		<!--/row-->
		<hr />
		<h4 class="form-section"><?php echo $L["adresse"];?></h4>
		<div class="row">
			<div class="col-md-10 ">
				<label class="control-label"><?php echo $L["adresse"];?></label>
				<div class="input-group">
					<input name="ADRESSE" id="ADRESSE" class="form-control" type="text" value="<?php echo $rowclient['ADRESSE']?>" />
					<span class="input-group-addon"><a href="javascript:;" onclick="getAddrFromTel(this)" title="<?php echo $L["AutoFill"] ?>"><i class="fa fa-map-marker"></i></a></span>
				</div>
			</div>
			<div class="col-md-2">
				<div class="form-group">
					<label>App.</label>
					<input name="APP" class="form-control" type="text" value="<?php echo $rowclient['APP']?>" />
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label><?php echo $L["ville"];?></label>
					<input name="VILLE" id="VILLE" class="form-control" type="text" value="<?php echo ucwords($rowclient['VILLE'])?>" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-4">
				<div class="form-group">
					<label><?php echo $L["cp"];?></label>
					<input name="CP" id="CP" class="form-control" type="text" value="<?php echo $rowclient['CP'];?>" style="text-transform:uppercase" />
				</div>
			</div>
			<!--/span-->
			<div class="col-md-4">
				<div class="form-group">
					<label class="control-label"><?php echo $L["province"];?></label>
					<select name="PROVINCE" id="PROVINCE" class="form-control">
						<?php
						foreach($LIST_PROVINCE as $key => $value){
							?>
							<option value="<?php echo $key ?>" <?php if($key==$rowclient['PROVINCE']){echo "selected ";} ?>><?php echo $value ?></option>
							<?php
						} ?>
					</select>
				</div>
			</div>
			<!--/span-->
		</div>
		<!--/row-->
		<div class="row">
			<div class="col-md-6">
				<label class="control-label"><?php echo $L["Latitude"];?></label>
				<div class="input-group">
					<input name="LATITUDE" class="form-control" type="text" value="<?php echo $rowclient['LATITUDE']?>" />
					<span class="input-group-addon"><a href="javascript:" onclick="getLatLngFromAdresse(this)" title="<?php echo $L["Auto-géolocalisation"] ?>"><i class="fa fa-globe"></i></a></span>
				</div>
			</div>
			<!--/span-->
			<div class="col-md-6">
				<label class="control-label"><?php echo $L["Longitude"];?></label>
				<div class="input-group">
					<input name="LONGITUDE" class="form-control" type="text" value="<?php echo $rowclient['LONGITUDE']?>" />
					<span class="input-group-addon"><a href="javascript:" onclick="getLatLngFromAdresse(this)" title="<?php echo $L["Auto-géolocalisation"] ?>"><i class="fa fa-globe"></i></a></span>
				</div>
			</div>
		</div>
		<hr />
		<h4 class="form-section"><?php echo $L["langue de correspondance"];?></h4>
		<div class="row">
			<div class="col-md-12 pt-1">
					<div class="radio-list">
						<label class="radio-inline"><input checked name="LANGUE" class="" type="radio" value="fr" <?php if($rowclient["LANGUE"]=="fr"){echo "checked ";} ?>/> <?php echo $L["lang:fr"] ?></label>
						<label class="radio-inline"><input name="LANGUE" class="" type="radio" value="en" <?php if($rowclient["LANGUE"]=="en"){echo "checked ";} ?>/> <?php echo $L["lang:en"] ?></label>
					</div>
				</div>
			<!--/span-->
		</div>
	</div>
	<div class="form-actions text-right pt-2">
		<input name="form_client" type="hidden" value="sendok" />
		<input name="ID_CLIENT" type="hidden" value="<?php echo $rowclient['ID_CLIENT']?>" />

		<a href="<?= OLD_DASHBOARD?>private/files/Ouverture de dossier.pdf" target="_blank" class="btn btn-info"><i class="fa fa-print"></i> <?php echo $L['imprimerfiche'];?> </a>
		<a href="<?= OLD_DASHBOARD?>private/files/Ouverture de dossier_bilingue.pdf" target="_blank" class="btn btn-info"><i class="fa fa-print"></i> <?php echo $L['imprimerfichebilingue'];?> </a>
		<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo $L['savechange'];?></button>
	</div>
</form>