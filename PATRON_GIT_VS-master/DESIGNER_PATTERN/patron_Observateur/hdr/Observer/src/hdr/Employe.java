package hdr;

public class Employe {
	String name;
	int id;
	String adresse;
	Salary salaire;
	public Employe(String name, int id, Salary salaire) {
		this.salaire=salaire;
		this.name = name;
		this.id = id;
	}
	public String getName() {
		return name;
	}
	public Salary getSalaire() {
		return salaire;
	}
	public void setSalaire(Salary salaire) {
		this.salaire = salaire;
	}
	
	
	
	

}
