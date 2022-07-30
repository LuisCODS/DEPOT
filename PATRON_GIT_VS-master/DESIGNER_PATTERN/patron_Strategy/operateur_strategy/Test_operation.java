package operateur_strategy;

public class Test_operation {

	public static void main(String[] args) {
		
		OperateurContext operateur1=new OperateurContext(new Strategy_soustraction());
		OperateurContext operateur2=new OperateurContext(new Strategy_add());
		OperateurContext operateur3=new OperateurContext(new Strategy_multiply());
		OperateurContext operateur4=new OperateurContext(new Strategy_divide());
		
		System.out.println(operateur1.doOperation(operateur2.doOperation(15, (operateur3.doOperation(34, 23))), 12)+operateur4.doOperation(7, 2));
		
		
		
		

	}

}
