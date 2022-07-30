package technicienCegep;

public class Test {

	public static void main(String[] args) {

		IObservateur t1 = new Technicien("Luis");
		IObservateur t2 = new Technicien("Mike");
		IObservateur t3 = new Technicien("Bob");				
				
		Observable ip = new Imprimante();		
				   ip.Subscribe(t1);
				   ip.Subscribe(t2);
				   ip.Subscribe(t3);
				   
				   
	}

}
