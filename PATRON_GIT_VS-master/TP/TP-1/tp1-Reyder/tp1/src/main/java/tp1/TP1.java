package tp1;

import java.net.URL;

import javafx.application.Application;
import javafx.beans.InvalidationListener;
import javafx.beans.Observable;
import javafx.event.ActionEvent;
import javafx.geometry.Insets;
import javafx.geometry.Pos;
import javafx.geometry.Rectangle2D;
import javafx.scene.Scene;
import javafx.scene.control.Button;
import javafx.scene.control.Slider;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.input.MouseEvent;
import javafx.scene.layout.BorderPane;
import javafx.scene.layout.HBox;
import javafx.scene.layout.Priority;
import javafx.scene.layout.VBox;
import javafx.scene.media.Media;
import javafx.scene.media.MediaPlayer;
import javafx.scene.media.MediaPlayer.Status;
import javafx.scene.media.MediaView;
import javafx.stage.Screen;
import javafx.stage.Stage;
import javafx.util.Duration;

public class TP1 extends Application {

    public static void main(String[] args) {

        Application.launch(args);
    }

    @Override
    public void start(Stage stage) {
    	
    	

        // Locate the media content in the CLASSPATH
        URL mediaUrl = getClass().getResource("Test.mp4");
        String mediaStringUrl = mediaUrl.toExternalForm();

        // Create a Media
        Media media = new Media(mediaStringUrl);

        // Create a Media Player
        final MediaPlayer player = new MediaPlayer(media);
        //Automatically begin the playback
        player.setAutoPlay(true);

        MediaView mediaView = new MediaView(player);

        Rectangle2D primaryScreenBounds = Screen.getPrimary().getVisualBounds();
        mediaView.setFitHeight(primaryScreenBounds.getHeight() - 230);

        BorderPane borderPane = new BorderPane();
        borderPane.setCenter(mediaView);
       
       
        // Create the Scene
        Scene scene = new Scene(borderPane, primaryScreenBounds.getWidth() - 50, primaryScreenBounds.getHeight() - 50);

        // Add the scene to the Stage
        stage.setScene(scene);
        // Set the title of the Stage
        stage.setTitle("Un Lecteur de Vid??o Simple");
        // Display the Stage
        
        
       
 

        // Add time slider
        timeSlider = new Slider();
        HBox.setHgrow(timeSlider, Priority.ALWAYS);
        timeSlider.setMinWidth(50);
        timeSlider.setMaxWidth(Double.MAX_VALUE);
        
        
        VBox controls = new VBox();
        controls.setPadding(new Insets(20));
        controls.setAlignment(Pos.CENTER);
        controls.alignmentProperty().isBound();
        controls.setSpacing(5);
        controls.setStyle("-fx-background-color: Black");
        
        controls.getChildren().addAll(timeSlider,addToolBar());
     
        
        borderPane.setBottom(controls);
       
        

        player.currentTimeProperty().addListener(new InvalidationListener() {
            public void invalidated(Observable ov) {
                timeSlider.setValue(100 * player.getCurrentTime().toMillis() / player.getMedia().getDuration().toMillis());

            }
        });

        timeSlider.valueProperty().addListener(new InvalidationListener() {
            public void invalidated(Observable ov) {
                if (timeSlider.isValueChanging()) {
                    player.seek(new Duration(player.getMedia().getDuration().toMillis() * timeSlider.getValue() / 100));
                }
            }
        });

        playButton.addEventFilter(MouseEvent.MOUSE_CLICKED, (MouseEvent e) -> {
            if (time != null) {
                player.seek(time);
            } else {
                player.seek(player.getStartTime());
            }
            
            //player.play();
            lecteurVideo.play();

        });

        stopButton.addEventFilter(MouseEvent.MOUSE_CLICKED, (MouseEvent e) -> {
            time = null;
            player.stop();
        });

        pauseButton.addEventFilter(MouseEvent.MOUSE_CLICKED, (MouseEvent e) -> {
            Status status = player.getStatus();
            if (status == Status.UNKNOWN || status == Status.HALTED) {
                return;
            }
            if (status == Status.PLAYING) {
                //player.pause();
            	lecteurVideo.pause();
                time = player.getCurrentTime();
            }

        });

        forwardButton.addEventFilter(MouseEvent.MOUSE_CLICKED, (MouseEvent e) -> {
            Status status = player.getStatus();
            if (status == Status.UNKNOWN || status == Status.HALTED) {
                return;
            }
            if (status == Status.PLAYING) {
                //time = player.getCurrentTime();
                //player.seek(time.add(new Duration(10000)));
                //time = null;
            	lecteurVideo.enAvant();
            }

        });

        backwardButton.addEventFilter(MouseEvent.MOUSE_CLICKED, (MouseEvent e) -> {
            Status status = player.getStatus();
            if (status == Status.UNKNOWN || status == Status.HALTED) {
                return;
            }
            if (status == Status.PLAYING) {
                //time = player.getCurrentTime();
                //player.seek(time.subtract(new Duration(10000)));
                //time = null;
            	lecteurVideo.enArriere();
            }

        });

        stage.show();
        
        lecteurVideo=new LecteurVideo(player);

    }
    
    LecteurVideo lecteurVideo=null;

    Duration time;

    Button playButton;

    Button stopButton;

    Button pauseButton;

    Button forwardButton;

    Button backwardButton;

    Slider timeSlider;

    private HBox addToolBar() {
        HBox toolBar = new HBox();
        toolBar.setPadding(new Insets(20));
        toolBar.setAlignment(Pos.CENTER);
        toolBar.alignmentProperty().isBound();
        toolBar.setSpacing(5);
        toolBar.setStyle("-fx-background-color: Black");

        Image playButtonImage = new Image(getClass().getResourceAsStream("play.png"));
        playButton = new Button();
        playButton.setGraphic(new ImageView(playButtonImage));
        playButton.setStyle("-fx-background-color: Black");

        playButton.setOnAction((ActionEvent e) -> {

        });
        playButton.addEventHandler(MouseEvent.MOUSE_ENTERED, (MouseEvent e) -> {
            playButton.setStyle("-fx-background-color: Black");
            playButton.setStyle("-fx-body-color: Black");
        });
        playButton.addEventHandler(MouseEvent.MOUSE_EXITED, (MouseEvent e) -> {
            playButton.setStyle("-fx-background-color: Black");
        });

        Image pausedButtonImage = new Image(getClass().getResourceAsStream("pause.png"));
        pauseButton = new Button();
        pauseButton.setGraphic(new ImageView(pausedButtonImage));
        pauseButton.setStyle("-fx-background-color: Black");

        pauseButton.setOnAction((ActionEvent e) -> {
        });

        pauseButton.addEventHandler(MouseEvent.MOUSE_ENTERED, (MouseEvent e) -> {
            pauseButton.setStyle("-fx-background-color: Black");
            pauseButton.setStyle("-fx-body-color: Black");
        });
        pauseButton.addEventHandler(MouseEvent.MOUSE_EXITED, (MouseEvent e) -> {
            pauseButton.setStyle("-fx-background-color: Black");
        });

        Image stopButtonImage = new Image(getClass().getResourceAsStream("stop.png"));
        stopButton = new Button();
        stopButton.setGraphic(new ImageView(stopButtonImage));
        stopButton.setStyle("-fx-background-color: Black");

        stopButton.setOnAction((ActionEvent e) -> {
        });

        stopButton.addEventHandler(MouseEvent.MOUSE_ENTERED, (MouseEvent e) -> {
            stopButton.setStyle("-fx-background-color: Black");
            stopButton.setStyle("-fx-body-color: Black");
        });
        stopButton.addEventHandler(MouseEvent.MOUSE_EXITED, (MouseEvent e) -> {
            stopButton.setStyle("-fx-background-color: Black");
        });

        Image forwardButtonImage = new Image(getClass().getResourceAsStream("forward.png"));
        forwardButton = new Button();
        forwardButton.setGraphic(new ImageView(forwardButtonImage));
        forwardButton.setStyle("-fx-background-color: Black");

        forwardButton.setOnAction((ActionEvent e) -> {
        });

        forwardButton.addEventHandler(MouseEvent.MOUSE_ENTERED, (MouseEvent e) -> {
            forwardButton.setStyle("-fx-background-color: Black");
            forwardButton.setStyle("-fx-body-color: Black");
        });
        forwardButton.addEventHandler(MouseEvent.MOUSE_EXITED, (MouseEvent e) -> {
            forwardButton.setStyle("-fx-background-color: Black");
        });

        Image backwardButtonImage = new Image(getClass().getResourceAsStream("backward.png"));
        backwardButton = new Button();
        backwardButton.setGraphic(new ImageView(backwardButtonImage));
        backwardButton.setStyle("-fx-background-color: Black");

        backwardButton.setOnAction((ActionEvent e) -> {
        });

        backwardButton.addEventHandler(MouseEvent.MOUSE_ENTERED, (MouseEvent e) -> {
            backwardButton.setStyle("-fx-background-color: Black");
            backwardButton.setStyle("-fx-body-color: Black");
        });
        backwardButton.addEventHandler(MouseEvent.MOUSE_EXITED, (MouseEvent e) -> {
            backwardButton.setStyle("-fx-background-color: Black");
        });

        toolBar.getChildren().addAll(playButton, pauseButton, stopButton, forwardButton, backwardButton);

        return toolBar;
    }
}

//LA IMPLEMENTATION DES PATRONS COMMENCE ICI



//Strategy Pattern utilis?? pour implementer les modes Lecture et Capture
//Ce qui les buttons play,stop,pause,avant et arriere font d??pend du mode actif
//Ceci est une classe qui founit une instance d'une des modes disponibles ?? date
class SelecteurMode{
	public static  ModeStrategy getModeLecture(LecteurVideo lecteurVideo) {
		return new ModeLecture(lecteurVideo);		
	}
	public static  ModeStrategy getModeCapture(LecteurVideo lecteurVideo) {
		return new ModeCapture(lecteurVideo);		
	}
}

//Classe qui fait abtraction d'un lecteur video
//Classe Contexte qui utilise la strategy Lecture ou Capture
//Classe dont l'??tat (State Pattern: Interface Etat) peut etre chang?? par
//la strategy du mode en utilisation ce qui se soit  
//C'est le mode qui decide l'instance concrete d'etat et 
//selon l'etat choisie par chaque strategy du mode le comportement varie.
class LecteurVideo {
	//attributs	
	private Etat state;//??tat	
	private ModeStrategy mode;//Strategy pour le mode: Lecture ou Capture	
	private boolean enregistrer = false;//Atribut qui decide si l'utilisateur de la classe souhaite enregistrer la saveChk en cours
	private MediaPlayer player;
	
	//Constructeur
	public LecteurVideo(MediaPlayer player) {
		this.player=player;
		mode = new ModeLecture(this);
	}
	
	//M??thodes plubliques
	public void play() {
		System.out.println(mode.play());
	}

	public void pause() {
		System.out.println(mode.pause());
	}

	public void stop() {
		System.out.println(mode.stop());
	}

	public void enAvant() {
		System.out.println(mode.enAvant());
	}

	public void enArriere() {
		System.out.println(mode.enArriere());		
	}
	
	//Accesseurs
	public void setState(Etat state) {
		this.state = state;
	}

	public Etat getState() {
		return state;
	}

	public ModeStrategy getMode() {
		return mode;
	}

	public void setMode(ModeStrategy mode) {
		this.mode = mode;
	}

	public boolean isEnregistrer() {
		return enregistrer;
	}

	public void setEnregistrer(boolean enregistrer) {
		this.enregistrer = enregistrer;
	}

	public MediaPlayer getPlayer() {
		return player;
	}

	public void setPlayer(MediaPlayer player) {
		this.player = player;
	}
	
	
	
}

//Classe Abstracte ?? la base de la hi??rarchie des Modes
abstract class ModeStrategy {
	protected LecteurVideo lecteurVideo;
	
	public ModeStrategy(LecteurVideo lecteurVideo) {
		super();
		this.lecteurVideo = lecteurVideo;
	}

	//Ces m??thodes ne doivent pas ??tre  r??crites 
	//car le graphe d?????tats est complet et  correctement impl??ment?? par la hi??rarchie des ??tats ci-bas.   
	//L?????tat initial d??fini par le constructeur garanti d??j?? que juste le bon ??tat la bonne m??thode seront appel??s par la suite.

	public String play() {
		return lecteurVideo.getState().play(lecteurVideo);
	}

	public String pause() {
		return lecteurVideo.getState().pause(lecteurVideo);
	}

	public String stop() {
		return lecteurVideo.getState().stop(lecteurVideo);
	}

	public String enAvant() {
		return lecteurVideo.getState().enAvant(lecteurVideo);
	}

	public String enArriere() {
		return lecteurVideo.getState().enArriere(lecteurVideo);
	}
	
    
}

//Strategy Lecture
class ModeLecture extends ModeStrategy {
	//Strategy Lecture qui commence dans l'etat stop et
	//et puis d??clanche la lecture
	//Les r??gles de navigation entre ??tats garantissent que 
	//juste des ??tats compatibles avec le graphe d?????tats de la strategy du mode courante seront appel??s 
	public ModeLecture(LecteurVideo lecteurVideo) {
		super(lecteurVideo);
		lecteurVideo.setState(new Play());
	}
}

//Strategy Capture
class ModeCapture extends ModeStrategy {
	//Strategy Capture qui commence dans l'??tat Recording
	//et ??tablie que le client ne souhaite pas enregistrer par d??faut
	//Les r??gles de navigation entre ??tats garantissent que 
	//juste des ??tats compatibles avec le graphe d?????tats de la strategy du mode courante seront appel??s 
	public ModeCapture(LecteurVideo lecteurVideo) {
		super(lecteurVideo);	
		lecteurVideo.setState(new Recording());
		lecteurVideo.setEnregistrer(false);
	}	
}

//State Pattern. Interface ?? la base de la hi??rarchie
abstract class Etat {
	abstract public String play(LecteurVideo lecteurVideo);

	abstract public String pause(LecteurVideo lecteurVideo);

	abstract public String stop(LecteurVideo lecteurVideo);

	abstract public String enAvant(LecteurVideo lecteurVideo);

	abstract public String enArriere(LecteurVideo lecteurVideo);
	
	public Duration time=null;
}

//??tat play (utilis?? juste par la strategy Lecture)
class Play extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {  
		lecteurVideo.getPlayer().play();
		return "On lit la video d??j??"+System.lineSeparator();
	}

	@Override
	public String  pause(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().pause();
		time = lecteurVideo.getPlayer().getCurrentTime();
		lecteurVideo.setState(new PauseLecture());
		return "Paused"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().stop();
		lecteurVideo.setState(new StopLecture());
		return "Stopped"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
				
		
        time = lecteurVideo.getPlayer().getCurrentTime();
        lecteurVideo.getPlayer().seek(time.add(new Duration(10000)));
        time = null;        
        
       return "On avance la video"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {		
        time = lecteurVideo.getPlayer().getCurrentTime();
        lecteurVideo.getPlayer().seek(time.subtract(new Duration(10000)));
        time = null;
        
       return "On recule la video"+System.lineSeparator();
	}

}

//??tat stop (utilis?? juste par la strategy Lecture)
class StopLecture extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {		
		lecteurVideo.getPlayer().play();
		lecteurVideo.setState(new Play());
        return "On commence ?? lire la video"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		return "La video est arr??t??e. Rien ?? faire!"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {
		return "La video est d??j?? arr??t??e. Rien ?? faire!"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		return "Stopped"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {
		return "Stopped"+System.lineSeparator();
	}
}

//??tat recording (utilis?? juste par la strategy Capture)
class Recording extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {
		return "On est en train d'enregistrer. Utiliser 'pause' pour arreter ou 'stop' pour terminer"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		lecteurVideo.setState(new PauseRecording());
              return "On va mettre l'enregistrement en pause"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {
		if (lecteurVideo.isEnregistrer()) {
			lecteurVideo.setState(new Enregistre());
			return "La capture a ??t?? enregistr??e"+System.lineSeparator();
		} else {
			lecteurVideo.setState(new Annulee());
			return "La capture a ??t?? annul??e"+System.lineSeparator();
		}
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		return "Invalide!"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {
		return "Invalide!"+System.lineSeparator();
	}
}

//??tat pause de la lecture (utilis?? juste par la strategy Lecture)
class PauseLecture extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {		
		lecteurVideo.getPlayer().play();
		lecteurVideo.setState(new Play());
        return "On reprend la lecture de la video"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		return "La video est d??j?? en pause. Rien ?? faire!"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {		
		lecteurVideo.getPlayer().stop();
		lecteurVideo.setState(new StopLecture());
		return "Stopped"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		return "Paused"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {
		return "Paused"+System.lineSeparator();
	}
}

//??tat pause de l'enregistrement (utilis?? juste par la strategy Capture)
class PauseRecording extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {
          lecteurVideo.setState(new Recording());
	    return "L'enregistrement va continuer"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		return "L'enregistrement est d??j?? en pause"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {		
		lecteurVideo.setState(new Recording());
		lecteurVideo.getState().stop(lecteurVideo);
              return "L'enregistrement sera arr??t??"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		return "Invalide!"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {
		return "Invalide!"+System.lineSeparator();
	}
}

//??tat en avant (utilis?? juste par la strategy Lecture)
/*
class EnAvant extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().play();
		lecteurVideo.setState(new Play());
              return "On arr??te l'avancement et on lit la video"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().pause();
		lecteurVideo.setState(new PauseLecture());
              return "On arr??te l'avancement et on met la video en pause"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().stop();
		lecteurVideo.setState(new StopLecture());
              return "On arr??te l'avancement et la video aussi"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		lecteurVideo.setState(new Play());
		
		return "On avance d??j??. Rien ?? faire. Avancer plus vite, peut-etre?"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {	
		lecteurVideo.setState(new Play());
		return "On avance presentement"+System.lineSeparator();
	}
}

//??tat en arri??re (utilis?? juste par la strategy Lecture)
class EnArriere extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().play();
		lecteurVideo.setState(new Play());
              return "On arr??te le recul et on lit la video"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().pause();
		lecteurVideo.setState(new PauseLecture());
              return "On arr??te le recul et on met la video en pause"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {
		lecteurVideo.getPlayer().stop();
		lecteurVideo.setState(new StopLecture());
              return "On arr??te le recul et la video aussi"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		return "Invalide"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {
		return "On recule d??j??. Rien ?? faire. Reculer plus vite, peut-etre?"+System.lineSeparator();
	}
}
*/
//??tat enregistr?? (utilis?? juste par la strategy Capture)
class Enregistre extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {
		lecteurVideo.setMode(SelecteurMode.getModeLecture(lecteurVideo));
              return "Enregistrement Fini et Enregistr??. On passe au mode Lecture et on lit la video"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

}

//??tat annul??e (utilis?? juste par la strategy Capture)
class Annulee extends Etat {

	@Override
	public String play(LecteurVideo lecteurVideo) {
		lecteurVideo.setMode(SelecteurMode.getModeLecture(lecteurVideo));
              return "Enregistrement Annul??. On passe au mode Lecture et on lit la video"+System.lineSeparator();
	}

	@Override
	public String pause(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

	@Override
	public String stop(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

	@Override
	public String enAvant(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

	@Override
	public String enArriere(LecteurVideo lecteurVideo) {
		return "Invalide! Utiliser 'play' pour rependre la lecture"+System.lineSeparator();
	}

}

