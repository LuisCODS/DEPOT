<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<title>Bootstrap 101 Template</title>
		<!-- Bootstrap -->
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<!--MY CSS-->
		<link href="css/style.css" rel="stylesheet" />
	</head>
	<body>
		<?php
		echo '<pre>';
		echo htmlspecialchars(print_r($_REQUEST, true));
		isset($tabErreur) ?print_r( $tabErreur) : '';
		echo '</pre>';

		$tabErreur[] = "";
		
		if( isset($_POST["form"]) AND $_POST["form"] == "envoye" )
		{ 								
			//Valide NAME
			if( isset($_POST["fname"]) AND !empty($_POST["fname"]) ){
				// "Données valide!";				 
			}else{				
				$tabErreur["fname"] = "Données invalide!";				
			}
			
			//Valide AGE
			if( isset($_POST["age"]) AND $_POST["age"] > 0  AND  filter_var($_POST["age"], FILTER_VALIDATE_INT) !== false ){
				// "Données valide!";				 
			}else{				
				$tabErreur["age"] = "Données invalide!";				
			} 

			//Valide AGE
			if( isset($_POST["email"])  AND  filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) !== false ){
				// "Données valide!";				 
			}else{				
				$tabErreur["email"] = "Données invalide!";				
			} 
			if(count($tabErreur) == 0){
				echo "Les données ont été filtrés...";
			}
		}			
		?>
		<div class="container">
			<div class="row">
				<div class="col-md-12" id="menu">
					<H2>Zone test</H2>
					<form action="" method="POST">
					  <label for="fname">First name:</label><br>
					  
					  <input type="text" id="fname" name="fname" 
							 value="<?= isset($_POST["fname"]) ? htmlspecialchars($_POST["fname"]) : '' ?>" ><br>
							 <span class="errorMessageInput" style="color: #FF0000">
							 <?= isset($tabErreur["fname"]) ? $tabErreur["fname"] : "" ?></span><br><br>
					  
					  <label for="lname">Age:</label><br>					  
					  <input type="text" id="age" name="age"  
							 value="<?= isset($_POST["age"]) ? htmlspecialchars($_POST["age"]) : '' ?>" ><br>
							 <span class="errorMessageInput" style="color: #FF0000">
							 <?= isset($tabErreur["age"]) ? $tabErreur["age"] : '' ?></span><br><br>

					  <label for="lname">email:</label><br>
					  <input type="text" id="email" name="email"  
							 value="<?= isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : '' ?>" ><br>
							 <span class="errorMessageInput" style="color: #FF0000">
							 <?= isset($tabErreur["email"]) ? $tabErreur["email"] : '' ?></span><br><br>

					  <input type="submit" name="form" value="envoye">
					</form> 
				</div>
		</div>		
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="js/bootstrap.min.js"></script>
		<!--MY JS-->
		<script type="text/javascript" src="js/script.js"></script>
	</body>
</html>