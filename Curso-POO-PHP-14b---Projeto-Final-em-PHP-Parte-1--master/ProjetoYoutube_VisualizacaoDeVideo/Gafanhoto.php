<?php
require_once 'Pessoa.php';
class Gafanhoto  extends Pessoa{
    
 // ==================== ATTRIBUTS ====================
    private $login;
    private $totAssistido;

 // ==================== CONSTRUCT ====================
    public function __construct($nome, $idade, $sexo, $login) 
    {
        //call  super-class construct
        parent::__construct($nome, $idade, $sexo); 
        $this->login = $login;
        $this->totAssistido = 0;
    } 
    
// ==================== METHODE ====================
    public function assistirMaisUm() {
        $this -> totAssistido ++;
    }
    
// ==================== GETS & SETS ====================
    function getLogin() {
        return $this->login;
    }

    function getTotAssistido() {
        return $this->totAssistido;
    }

    function setLogin($login) {
        $this->login = $login;
    }

    function setTotAssistido($totAssistido) {
        $this->totAssistido = $totAssistido;
    }




}//END CLASS