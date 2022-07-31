<!DOCTYPE html>
<!--source: https://www.youtube.com/watch?v=fy0zj2wv0Wc&list=PLHz_AreHm4dmGuLII3tsvryMMD7VgcT7x&index=29-->

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
    <pre>
    <?php
        require_once "video.php";                
        require_once "Gafanhoto.php";
        require_once "Visualizacao.php";
        
        //VIDEOS...      
        $videos[0] = new Video("Aula 1 de POO");
        $videos[1] = new Video("Aula 12 de PHP"); 
        $videos[2] = new Video("Aula 15 de HTML5");   

        //ESPECTADORES...
        $gafs[0] = new Gafanhoto("Jubileu", 22, "M","Juba");
        $gafs[1] = new Gafanhoto("Creuza", 12, "F","creuzita"); 
        
        //VISUALIZACOES...    
        
        //agregando gafanhoto ao video
        $vis[0] = new Visualizacao( $gafs[0], $videos[2] );
        $vis[1] = new Visualizacao( $gafs[0], $videos[1] );
        
        $vis[0]->avaliar();
        $vis[1]->avaliarPorPorcentage(85);
        print_r($vis);
        print_r("<hr>");   
        
        ?>
    </pre>
    </body>
</html>
