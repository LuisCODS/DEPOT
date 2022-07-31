<?php 
// vérification du token de sondage.
if(isset($_GET["token"]) && preg_match('#^[A-Za-z0-9]+$#', $_GET["token"])){
    $enonce = sprintf("SELECT *,
							CLIENT.EMAIL AS C_EMAIL,
							CLIENT.PASSWORD AS C_PASSWORD
							FROM CLIENT_SONDAGE
							INNER JOIN CLIENT USING(ID_CLIENT)
							INNER JOIN TOILETTAGE USING(ID_TOIL)
							INNER JOIN utilisateur_magasin ON(TOILETTAGE.ID_STAFF = utilisateur_magasin.id_utilisateur AND TOILETTAGE.ID_MAG = utilisateur_magasin.id_magasin)
							JOIN utilisateur USING(id_utilisateur)
							INNER JOIN MAGASIN USING(ID_MAGASIN)
							WHERE TOKEN_USED IS NULL AND TOKEN = '%s'
							LIMIT 1", $_GET["token"]);
    $resultToken = $mysqli->query($enonce) or die("Erreur SQL: ".$mysqli->error);
    if($resultToken->num_rows === 1){
        $rowToken = $resultToken->fetch_assoc();
        $email = $rowToken["C_EMAIL"];
        $password = $rowToken["C_PASSWORD"];
        $rowClient = $rowToken;
    }else{
        die("Jeton d'identification invalide!");
    }
}else{
    die("Jeton d'identification invalide!");
}
?>
<div class="container blog-container article-container style-1">
	<div class="information-blocks">
		<div class="row">
			<div class="col-md-12 information-entry">
				<div class="article-container style-1">
					<p style="font-size:20px; font-weight:300; margin:40px 0;"><?php echo $L['sondage_desc'];?></p>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="submiterror">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						<?php echo $L['erreur'];?>
					</h4>
				</div>
				<div class="modal-body">
					<p><?php echo $L['certainesinformationsnontfournies'];?>.</p>
				</div>
				<div class="modal-footer">
					<button data-dismiss="modal" class="button style-12">OK</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="submitsuccess" data-backdrop="static">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						<?php echo $L['merci'];?> !
					</h4>
				</div>
				<div class="modal-body">
					<p><?php echo $L['mercirepondresondage'];?>.</p>
				</div>
				<div class="modal-footer">
					<button data-dismiss="modal" onclick="location = '/'" class="button style-12">OK</button>
				</div>
			</div>
		</div>
	</div>
	<div class="information-blocks">
		<form id="formSondage" onsubmit="submitSondage(this);return false;">
			<input type="hidden" name="token" value="<?= attrEncode($_GET["token"])?>">
			
			<div class="row">
				<div class="form-group col-md-6" style="margin-bottom:40px;">
					<label class="control-label"><?php echo $L['SQ1'];?></label>
					<fieldset class="rating">
					    <input type="radio" id="star5" name="Q1" value="5" /><label class = "full" for="star5" title="5"></label>
					    <input type="radio" id="star4" name="Q1" value="4" /><label class = "full" for="star4" title="4"></label>
					    <input type="radio" id="star3" name="Q1" value="3" /><label class = "full" for="star3" title="3"></label>
					    <input type="radio" id="star2" name="Q1" value="2" /><label class = "full" for="star2" title="2"></label>
					    <input type="radio" id="star1" name="Q1" value="1" /><label class = "full" for="star1" title="1"></label>
					</fieldset>
					<input type="text" name="COM_Q1" class="form-control" placeholder="<?php echo $L['Commentaires'];?>">
				</div>
			</div>
			
			<div class="row">
				<div class="form-group col-md-6" style="margin-bottom:40px;">
					<label class="control-label"><?php echo $L['SQ2'];?></label>
					<fieldset class="rating">
						<input type="radio" id="star52" name="Q2" value="5" /><label class = "full" for="star52" title="5"></label>
					    <input type="radio" id="star42" name="Q2" value="4" /><label class = "full" for="star42" title="4"></label>
					    <input type="radio" id="star32" name="Q2" value="3" /><label class = "full" for="star32" title="3"></label>
					    <input type="radio" id="star22" name="Q2" value="2" /><label class = "full" for="star22" title="2"></label>
					    <input type="radio" id="star12" name="Q2" value="1" /><label class = "full" for="star12" title="1"></label>
					</fieldset>
					<input type="text" name="COM_Q2" class="form-control" placeholder="<?php echo $L['Commentaires'];?>">
				</div>
			</div>
			
			<div class="row">
				<div class="form-group col-md-6" style="margin-bottom:40px;">
					<label class="control-label"><?php echo $L['SQ3'];?></label>
					<fieldset class="rating">
					    <input type="radio" id="star53" name="Q3" value="5" /><label class = "full" for="star53" title="5"></label>
					    <input type="radio" id="star43" name="Q3" value="4" /><label class = "full" for="star43" title="4"></label>
					    <input type="radio" id="star33" name="Q3" value="3" /><label class = "full" for="star33" title="3"></label>
					    <input type="radio" id="star23" name="Q3" value="2" /><label class = "full" for="star23" title="2"></label>
					    <input type="radio" id="star13" name="Q3" value="1" /><label class = "full" for="star13" title="1"></label>
					</fieldset>
					<input type="text" name="COM_Q3" class="form-control" placeholder="<?php echo $L['Commentaires'];?>">
				</div>
			</div>
			
			<div class="row">
				<div class="form-group col-md-6" style="margin-bottom:40px;">
					<label class="control-label"><?php echo $L['SQ4'];?></label>
					<fieldset class="rating">
					    <input type="radio" id="star54" name="Q4" value="5" /><label class = "full" for="star54" title="5"></label>
					    <input type="radio" id="star44" name="Q4" value="4" /><label class = "full" for="star44" title="4"></label>
					    <input type="radio" id="star34" name="Q4" value="3" /><label class = "full" for="star34" title="3"></label>
					    <input type="radio" id="star24" name="Q4" value="2" /><label class = "full" for="star24" title="2"></label>
					    <input type="radio" id="star14" name="Q4" value="1" /><label class = "full" for="star14" title="1"></label>
					</fieldset>
					<input type="text" name="COM_Q4" class="form-control" placeholder="<?php echo $L['Commentaires'];?>">
				</div>
			</div>
			
			<div class="row">
				<div class="form-group col-md-6" style="margin-bottom:40px;">
					<label class="control-label"><?php echo $L['SQ5'];?></label>
					<fieldset class="rating">
					    <input type="radio" id="star55" name="Q5" value="5" /><label class = "full" for="star55" title="5"></label>
					    <input type="radio" id="star45" name="Q5" value="4" /><label class = "full" for="star45" title="4"></label>
					    <input type="radio" id="star35" name="Q5" value="3" /><label class = "full" for="star35" title="3"></label>
					    <input type="radio" id="star25" name="Q5" value="2" /><label class = "full" for="star25" title="2"></label>
					    <input type="radio" id="star15" name="Q5" value="1" /><label class = "full" for="star15" title="1"></label>
					</fieldset>
					<input type="text" name="COM_Q5" class="form-control" placeholder="<?php echo $L['Commentaires'];?>">
				</div>
			</div>
			
			<div class="row">
				<div class="form-group col-md-6" style="margin-bottom:40px;">
					<label class="control-label"><?php echo $L['commententenduparler'];?> ?</label>
					<select name="REFERENCE" class="form-control">
						<?php 
							$referer_choices = [
								1 => $L["viaamifamille"],
								2 => $L["viareseauxsociaux"],
							    3 => $L["viatelevision"],
								4 => $L["Site_internet"]
							];
							foreach($referer_choices as $id => $choice){
								?>
								<option value="<?= $id?>"><?= $choice?></option>
								<?php
							}
						?>
					</select>
				</div>
				<div class="form-group col-md-12">
					<button class="btn btn-danger" type="submit"><?php echo $L['envoyer_message'];?></button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
function submitSondage(form){
	var jf = $(form);
	jf.find("button[type=submit]").attr("disabled",true);
	$.ajax({
		type: "POST",
		url: "ajax/ajx_sondage.php",
		cache: false,
		data: jf.serialize(),
		async: false,
		success: function(data, status, xhr){
			if(data == "success"){
				$("#submitsuccess").modal();
			}else{
				$("#submiterror").modal();
				jf.find("button[type=submit]").attr("disabled",false);
				console.log(data);
			}
		},
		error: function(data, status, xhr){
			jf.find("button[type=submit]").attr("disabled",false);
		}
	})
}
</script>
