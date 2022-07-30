package operateur_strategy;

public class OperateurContext {
	
	//CHAMP
	OperateurStrategy operateur;
	
	
	//CONSTRUCTEUR
	OperateurContext(OperateurStrategy NewOperateur)
	{
		this.operateur=NewOperateur;
	}
		
	
	//MÉTHODES
	public int doOperation(int op1,int op2)
	{
		return operateur.doOperation(op1, op2);
	}	
	 public OperateurStrategy getOperateur() {
		return operateur;
	}
	public void setOperateur(OperateurStrategy NewOperateur) {
		this.operateur = NewOperateur;
	}

}
