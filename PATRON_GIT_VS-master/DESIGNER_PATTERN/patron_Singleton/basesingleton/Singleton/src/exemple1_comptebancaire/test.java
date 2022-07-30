package exemple1_comptebancaire;

public class test {

	public static void main(String[] args) {
		 // Création et utilisation du CompteBancaire cb1.
        CompteBancaire cb1 = new CompteBancaire(123456789);
        log journal= log.getInstance();
        cb1.Subscribe(journal);
        cb1.deposerArgent(100);
        cb1.retirerArgent(80);
        // Création et utilisation du CompteBancaire cb2.
        CompteBancaire cb2 = new CompteBancaire(987654321);
        log journal1= log.getInstance();
        cb2.Subscribe(journal1);
        cb2.retirerArgent(10);
        // Affichage des logs en console.
        String s = journal1.afficherLog();
        System.out.println(s);

	}

}
