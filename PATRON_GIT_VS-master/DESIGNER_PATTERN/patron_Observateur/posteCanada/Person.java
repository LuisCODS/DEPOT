package posteCanada;

public class Person{
	String Fname;
	String Lname;
	String emailadress;
	public Person(String fname, String lname, String emailadress) {
		Fname = fname;
		Lname = lname;
		this.emailadress = emailadress;
	}
	public String getFname() {
		return Fname;
	}
	public String getLname() {
		return Lname;
	}
	public void setLname(String lname) {
		Lname = lname;
	}
	public String getEmailadress() {
		return emailadress;
	}
	public void setEmailadress(String emailadress) {
		this.emailadress = emailadress;
	}
	public void setFname(String fname) {
		Fname = fname;
	}
	
	
	
	
	

}
