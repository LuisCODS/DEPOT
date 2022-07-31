<?php
require_once "video.php";
require_once "Gafanhoto.php";
class Visualizacao {

    // ==================== ATTRIBUTS ====================
    //agregation...
    private $espectador;
    private $filme;

     // ==================== CONSTRUCT ====================
     public function __construct($espectador, $filme) 
     {
         $this -> espectador = $espectador;
         $this -> filme = $filme;
         //update the objets states...
         $this -> filme -> setViews($this -> filme -> getViews() + 1);
         $this -> espectador -> setTotAssistido($this->espectador->getTotAssistido()+1);                
     }
     

 // ==================== METHODE ====================
    public function avaliar() {
        $this->filme-> setAvaliacao(5);
    }
    public function avaliarPorNota($nota) {
        $this->filme-> setAvaliacao($nota);
    }   
    public function avaliarPorPorcentage($porc) 
    {
       $novaNota  = 0;
       if ($porc <= 20) {
           $novaNota = 3;
       }elseif ($porc <= 50) {
           $novaNota = 5;
       } elseif ($porc <= 90){
            $novaNota = 8;
       }else{
            $novaNota = 10;           
       }
       $this->filme-> setAvaliacao($novaNota);
    }
    
    
    // ==================== GETS & SETS ====================
    public function getEspectador() {
        return $this->espectador;
    }

    public function getFilme() {
        return $this->filme;
    }

    public function setEspectador($espectador) {
        $this->espectador = $espectador;
    }

    public function setFilme($filme) {
        $this->filme = $filme;
    }


    
}//end class
