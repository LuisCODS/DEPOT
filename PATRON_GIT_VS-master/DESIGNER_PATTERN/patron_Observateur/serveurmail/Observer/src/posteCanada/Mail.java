package posteCanada;

public class Mail {
	
	
	Person sender;
	Person Receiver;
	String Content;
	public Mail(Person sender, Person receiver, String content) {
		this.sender = sender;
		Receiver = receiver;
		Content = content;
	}
	public Person getSender() {
		return sender;
	}
	public void setSender(Person sender) {
		this.sender = sender;
	}
	public Person getReceiver() {
		return Receiver;
	}
	public void setReceiver(Person receiver) {
		Receiver = receiver;
	}
	public String getContent() {
		return Content;
	}
	public void setContent(String content) {
		Content = content;
	}
	
	

}
