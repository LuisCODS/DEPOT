<?php

// Fait la formatation de la date pour l'afficher correctement.
function format($date)
{
	if ($date != "") {
		$aux = str_replace('/', '-', $date);
		$newDate = date('Y-m-d', strtotime($aux));
	}else{
		$newDate = null;
	}
	return $newDate;
}