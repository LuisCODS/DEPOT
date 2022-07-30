package hdr;

public class Hdr implements Iobserver{
	public void update(Employe e, String message)
	{	System.out.println("le departement hr est notifie");
		System.out.println(message +" : " +e.getName());
	}
}
