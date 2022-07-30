using System;
using System.IO;
using System.Linq;
using System.Web.UI;

public partial class _Default : Page
{
    //Notre contrôleur personnel  MAIN
    protected void Page_Load(Object sender, EventArgs e)
    {
        // the attribute name of the input form gives the route value
        string action = Request.Form["action"];

        //ROUTEUR
        switch (action)
        {
            case "Enregistrer":
                string num;
                string titre;
                string res;
                int duree;
                //retrieve values from Form  
                num = Request.Form["num"];
                titre = Request.Form["titre"];
                duree = int.Parse(Request.Form["duree"]);
                res = Request.Form["res"];

                //Send date and file name to be recorded
                enregistrerMembres("films.txt", num, titre, duree, res);
                //Response.Write("<h3>Film  bien enregistré</h3>");
                break;

            case "Lister":
                ListerMembres("films.txt");//Send the file to be read
                break;

            case "Modifier":
                string code = Request.Form["code"];//Routage

                //On cherche d'abord le Film à editer
                if (String.Equals(code, "Chercher"))//code == Chercher
                {                    
                    string numf = Request.Form["numf"];//Get item's Id to be found                    
                    obtenirFiche("films.txt", numf); //Send item to be shown
                }
                //Le Film a été trouvé deja
                else //code == Maj
                    majFilm("films.txt");
                break;

            default:
                Response.Write("<h1>Oups!!!</h1>");
                break;
        }
    }


    //************************************ MÉTHODES *************************************************************

    void enregistrerMembres(string leFichier, string num, string titre, int duree, string res)
    {
        /* 
         * chemin : retrieve the physical file of a relative virtual path(inside of the Web application)
         * Server.MapPath : gives the way of the file and  the parameter gives the file name to be found
         */
        string chemin = Server.MapPath("./") + leFichier;
        try {
            // Create or open file and write line to it.
            // ... If file exists, it contents are erased before writing.
            using (StreamWriter fichier = new StreamWriter(chemin, true))
            {
                string ligne = num + ";" + titre + ";" + duree + ";" + res;
                //write a line in the file 
                fichier.WriteLine(ligne);
                Response.Write("<h3>Film  bien enregistré</h3>");

            }//using ferme deja le fichier
        } catch (IOException e){
            //Code exécuté en cas d'exception
             Response.Write(e.Message);
          }
     }

    void ListerMembres(string leFichier)
    {
        
        string chemin = Server.MapPath("./") + leFichier;
        try {
            // Read every line in the file.
            using (StreamReader fichier = new StreamReader(chemin, true))
            {
                string ligne;
                string[] elems;
                char delimiteur = ';';

                //tant qu'il y a de quoi à lire
                //ReadLine:It returns null if no further data is available in the file.
                while ((ligne = fichier.ReadLine()) != null)
                {
                    /*
                     Split :sépare apartir d'un parametre fournit (delimiteur) chaque mot provenant de string input (ligne)
                     elems: chaque indice du Array recoit la decomposition des mot defagrimenté.
                     SOURCE:https://www.dotnetperls.com/split
                     */
                    elems = ligne.Split(delimiteur);

                    //Display on browser line-by-line
                    Response.Write("NUMERO = "      + elems[0] + "<br>");
                    Response.Write("TITRE = "       + elems[1] + "<br>");
                    Response.Write("DURÉE = "       + elems[2] + "<br>");
                    Response.Write("RÉALISATEUR = " + elems[3] + "<br>");
                    Response.Write("**********************************<br><br>");
                }
                //Close(); pas necessaire, car using le ferme deja


            }
        } catch (IOException e){
            //Code exécuté en cas d'exception
            Response.Write("Une erreur est survenue au cours de la lecture !");
            Response.Write("</BR>");
            Response.Write(e.Message);
          }
     }

    //Affiche les contenus (tableau Form modifier) du Film trouvé au client au format HTML -  1:00
    void EnvoyerFiche(string[] elems)
    {
        Response.Write("<div id=\"divFilm\" style=\"visibility:show;position:absolute;top:20%;left:40%\">");
        Response.Write("<h3>Fiche du film a modifier</h3><br/><br/>");
        Response.Write("<form name=\"formMajFilm\" action=\"gestionMembres.aspx\" method=\"post\">");
        Response.Write("Numéro : <input id=\"num\" name=\"num\" type=\"text\" value='"+elems[0]+"' readonly/><br/>");
        Response.Write("Titre : <input id=\"titre\" name=\"titre\" type=\"text\" value='"+elems[1]+"'/><br/>");
        Response.Write("Durée : <input id=\"duree\" name=\"duree\" type=\"text\" value='"+elems[2]+"'/><br/>");
        Response.Write("Réalisateur : <input id=\"res\" name=\"res\" type=\"text\" value='"+elems[3]+"'/><br/>");
        Response.Write("<input type=\"hidden\" name=\"action\" value=\"Modifier\"/>");
        Response.Write("<input type=\"hidden\" name=\"code\" value=\"Maj\"/>");       
        Response.Write("<input type=\"button\" value=\"Enregistrer\" onclick=\"mettreAJour()\"/>"); //Call javaScript
        Response.Write("<input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('divFilm').style.visibility='hidden'\"/>");
        Response.Write("</form>");
        Response.Write("</div>");
    }

    /*
     * La methode cherche dans le fichier un element par son ID. 
     * Elle l'envoit par la suite une autre methode pour l'afficher 1:02
    */
    void obtenirFiche(string leFichier, string numf)
    {   //path + name of the filme to be found
        string chemin = Server.MapPath("./") + leFichier;
        try
        {   //Open de file 
            using (StreamReader fichier = new StreamReader(chemin, true))
            {
                string ligne;
                bool trouve = false;
                string[] elems = null;
                char delimiteur = ';';

                //ReadLine(): get the first ligne on the file
                //while the file has something to be read (text) and the item wasn't found yet ...
                while ((ligne = fichier.ReadLine()) != null && !trouve)
                {
                    //Remplit chaque index avec les respectives mots qui ont été separéés par Split
                    elems = ligne.Split(delimiteur);   
                    //Compare chaque index qui contien le numero du film(ID) contre celui fournit en parametre
                    if (String.Equals(elems[0], numf))
                        trouve = true;//element trouvé
                }                
                if (trouve)
                    EnvoyerFiche(elems);//Send the array to display 
                else
                    Response.Write("Film de numéro " + numf + " introuvable  !");
            }//using ferme deja le fichier
        }
        catch (IOException e)
        {
            //Code exécuté en cas d'exception
            Response.Write("Une erreur est survenue au cours de la lecture !");
            Response.Write("</BR>");
            Response.Write(e.Message);
        }
    }

//************************************ EXERCICES EN PÉRIODE DE LABO ********************************
    //A COMPLETER EN EXERCICE
    void majFilm(string leFichier)
    {
        //Response.Write("<h3>Fim a modifier " + num + " mais les étudiants vont s'en occuper</h3>");

        //Get the news dates to be add...
        string num;
        string titre;
        string res;
        int duree;

        num = Request.Form["num"];
        titre = Request.Form["titre"];
        duree = int.Parse(Request.Form["duree"]);
        res = Request.Form["res"];

        //Call effacerFilm...
        effacerFilm(leFichier, num);

        //Call enregistrer...
        enregistrerMembres("films.txt", num, titre, duree, res);

    }

    //A COMPLETER EN EXERCICE
    void effacerFilm(string leFichier, string numf)
    {
        //File.Copy("leFichier", "destFileCopy");
        //File.Move("leFichier", "destFileToeMove");
        //File.Delete("leFichier");
        string chemin = Server.MapPath("./") + leFichier;
        string fichierTemp = Server.MapPath("./") + "example.txt";

        bool exists = (from line in File.ReadAllLines(chemin)
                       where line == numf
                       select line).Count() > 0;

        string[] lines = File.ReadAllLines(chemin);

            foreach (string line in lines)
            {

            }
        

    }//fin methode



   
   
}
