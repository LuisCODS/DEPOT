<!DOCTYPE html>
<head>
    <title>PHP/MySQLi CRUD Operation using Bootstrap/Modal</title>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" ></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" >
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"> </script>
<!--    <script src="js/monJS.js"></script>-->
</head>
<body>    
    <?php require_once 'serveur/process.php'; ?>
    
    <!-- 
        Display Save and Delete message with $_SESSION
        ...at the top of the page using Bootstrap 4
    -->
    <?php if (isset($_SESSION["message"])):  ?>
    <div class="alert alert-<?=$_SESSION['msg_type']?> ">       
        <?php
            echo $_SESSION['message'];
            unset ($_SESSION['message']);
        ?>
    </div>
    <?php endif ?>
    
    <div class="container">
        <?php 
            $mysqli = new mysqli('localhost','root','','crud') or die(mysqli_error($mysqli));
            $result = $mysqli->query("SELECT * FROM data")or die($mysqli->error); 
           /*test only*/
           //pre_r($result);
         ?>
            <div class="row justify-content-center" style= "margin-top:100px" >
                <table class="table">
                    <thead>
                        <tr>
                            <th>NAME</th>
                            <th>LOCATION</th>
                            <th colspan="2">ACTIONS</th>
                        </tr>
                    </thead>
                    <?php 
                        /*
                         * @Valeurs de retour: Retourne un tableau associatif de chaînes représentant
                         *  la prochaine ligne dans le jeu de résultats représenté par le
                         *  paramètre result, où chaque clé du tableau représente le nom d'une 
                         * colonne du résultat ou NULL s'il n'y a plus de ligne dans le jeu de 
                         * résultats. 
                         */
                       while($row = $result->fetch_assoc()): 
                    ?>                                         
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['location']; ?></td>
                        <td>
                            <a href="index.php?edit=<?php echo $row['id'];?>"
                               class="btn btn-info">Edit</a>
                            <a href="serveur/process.php?delete=<?php echo $row['id'];?>" 
                               class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                    <?php 
                        endwhile
                     ?>
                </table>       
            </div>
            <!--test only -->
            <?php 
                function pre_r($array){
                    echo '<pre>';
                    print_r($array);
                    echo '</pre>';
                }
            ?>
                
        <!-- =============================== FORM ================================= --> 
        <hr>
        <div class="row justify-content-left"   >             
            <form  action="serveur/process.php"  method="POST">     
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="form-group"> 
                    <label for="name">Name</label>
                    <input type="text"  name="name" 
                           id="name" 
                           class="form-control" 
                           value="<?php echo $name;?>" 
                          placeholder="Enter your location"  required autofocus >       
                </div>
                <div class="form-group"> 
                    <label for="location">Location</label>
                    <input type="text"  name="location" 
                           id="location" 
                           class="form-control" 
                           value="<?php echo $location; ?>" 
                           placeholder="Enter your location"  required  >     
                </div>
                <div class="form-group"> 
                   <?php if ($update == true): ?>
                        <button type="submit" class="btn btn-info" name="update" >Update</button>  
                    <?php else: ?>                   
                        <button type="submit" name="save" class="btn btn-primary">Save</button>
                    <?php endif ?> 
                </div>
             </form>                
        </div> 
        <a href=javascript:history.back()>Go Back</a>
    </div>        


</body>
