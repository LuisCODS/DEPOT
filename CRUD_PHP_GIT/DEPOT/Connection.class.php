

<?php class Connection{
    
    //ATTRIBUTS
    private $user;
    private $pass;
    private $bd;
    private $serveur;
    private $pdo;
    
    //CONSTRUCTEUR
    public function _construct($usuario,$senha,$banco,$servidor) 
    {
        $this->user    = $usuario;
        $this->serveur = $servidor;
        $this->pass    = $senha;
        $this->bd      = $banco;
    }    
    //METHODES
    public function connecter() 
    {
        try
        {   
            //si pas encore instancie
            if (is_null(self::$pdo)) 
            {
                self::$pdo = new PDO("mysql:host=".$this->serveur.";bdname=".$this->bd, $this->user, $this->pass);
            }
            //
            return self::pdo;
        }catch(PDOException $ex){
            
        }
    }  
    
}//END CLASS
?>