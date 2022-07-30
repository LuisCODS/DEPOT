package transport_Strategy;

/**
 * CLASSE POUR MONTRER LE MAUVAISE EXEMPLE DE NON RESPECTE DU PATRON (open/close), car
 * l'implementantion des types de transports se faire à l'interieure de cette derniere plutot qu'ailleurs. 
 */
public class Transport_BadExemple {

	// CHAMP
	TypeTransport typeTransport;

	//CONSTRUCTEUR
	Transport_BadExemple() {
		typeTransport = TypeTransport.VOITURE;
	}

	
	
	//MÉTHODES
	public TypeTransport getTypeTransport() {
		return typeTransport;
	}

	public void setTypeTransport(TypeTransport typeTransport) {
		this.typeTransport = typeTransport;
	}
	/**
	 * cette méthode devrait se faire ailleur de la classe.
	 */
	public void voyager() {

		switch (typeTransport) {
		case CAR:
			System.out.println("Transport par car");
			break;
		case VOITURE:
			System.out.println("Transport par Voiture");
			break;
		case AVION:
			System.out.println("Transport par Avion");
			break;
		default:
			break;
		}
	}
}//FIN CLASS
