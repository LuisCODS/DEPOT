package calculeSalaireNetEmployee;

public abstract class Salary {
	float salaryParHeure;
	

	public  double calculSalaireNet(Employee employee)
	{
	return calculsalirebrute(employee)+ calculheuresSupplementaire(employee) +
			calculNotesDeFrais(employee) - calculheuresAbscences(employee);
	}
	
	
	private double calculNotesDeFrais(Employee employee) {
	
		return employee.getNoteDeFrais();
	}


	public double calculsalirebrute(Employee employee) {
		
		return (this.salaryParHeure)*(employee.getHeureTravaillee());
	}

	
	public double calculheuresSupplementaire(Employee employee) {
		
		return (this.salaryParHeure)*(employee.getHeuresSupplementaires());
	}

	public double calculheuresAbscences(Employee employee) {
		
		return (this.salaryParHeure)*(employee.getHeuresAbscences());
	}

	

	

}
