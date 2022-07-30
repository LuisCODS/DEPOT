package operateur_strategy;

public class Strategy_multiply extends OperateurStrategy
{

	@Override
	public int doOperation(int op1, int op2)
	{
		return op1 * op2;
	}	
	
}
