package posteCanada;

public class Mail {
	
	Person person;
	String email;
	String Content;
	
	
	public Mail(Person person, String email, String content) {
		
		this.person = person;
		this.email = email;
		Content = content;
	}
	public Person getPerson() {
		return person;
	}
	public void setPerson(Person person) {
		this.person = person;
	}
	public String getEmail() {
		return email;
	}
	public void setEmail(String email) {
		this.email = email;
	}
	public String getContent() {
		return Content;
	}
	public void setContent(String content) {
		Content = content;
	}
	
	
	


}
