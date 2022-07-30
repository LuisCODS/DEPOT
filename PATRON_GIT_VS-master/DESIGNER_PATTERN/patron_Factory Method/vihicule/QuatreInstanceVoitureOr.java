package vihicule;

public class QuatreInstanceVoitureOr extends VoitureEssence{
	
	    private static final int LIMIT = 4; 
	    private static int count = 0;
	    private QuatreInstanceVoitureOr() {}
	    public static synchronized QuatreInstanceVoitureOr getInstance() {
	        if (count < LIMIT) {
	        	QuatreInstanceVoitureOr myClass = new QuatreInstanceVoitureOr();
	            count++;
	            return myClass;
	        } 
	        return null;
	    }
	

}
