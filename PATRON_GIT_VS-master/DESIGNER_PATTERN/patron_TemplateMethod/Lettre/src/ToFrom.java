package Lettre.src;


public class ToFrom {

	String to ="";
	String from = "";

	public ToFrom(String to, String from)
	{	
		setFrom(from);
		setTo(to);
	}

	
	public String getTo() {
		return to;
	}
	public void setTo(String to) {
		this.to = to;
	}
	public String getFrom() {
		return from;
	}
	public void setFrom(String from) {
		this.from = from;
	}
}
