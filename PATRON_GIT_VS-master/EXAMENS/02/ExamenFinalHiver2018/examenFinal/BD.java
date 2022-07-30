package examenFinal;

import java.util.ArrayList;

public class BD {

	private BD(){ 	}
	
  	public static BD INSTANCE = null;
  	ArrayList<Vol> vols = new ArrayList<Vol>();  
  	
  	
  	public void Add(Vol  v)
  	{
  		vols.add(v);
  	}  	
  	public void Remove(Vol v) 
  	{
  		vols.remove(v);
  	}  	
  	public static BD getInstance() 
  	{
  		if ( INSTANCE == null) 		
  			INSTANCE = new BD();		
  		return INSTANCE;
  	}

	public ArrayList<Vol> getVols() {
		return vols;
	}
}
