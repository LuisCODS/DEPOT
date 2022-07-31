<?php
session_start(); 
// require_once("../../includes/ConnectionPDO.php");
include_once '../../model/Film.class.php';
include_once '../../model/Membre.class.php';
include_once '../../model/Film_Location.class.php';
include_once '../../dao/Film_LocationDAO.class.php';


extract($_POST);



?>