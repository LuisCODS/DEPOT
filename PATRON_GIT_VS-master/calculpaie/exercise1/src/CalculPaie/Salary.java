package CalculPaie;

public abstract class Salary {
	float salaryParHeure;
	

	public  double calculSalaireEmployee(Emplyee employee)
	{
	return calculsalirebrute(employee)+ calculheuresSupplementaire(employee) +
			calculNotesDeFrais(employee) - calculheuresAbscences(employee);
	}
	
	
	private double calculNotesDeFrais(Emplyee employee) {
	
		return employee.getNoteDeFrais();
	}


	public double calculsalirebrute(Emplyee employee) {
		
		return (this.salaryParHeure)*(employee.getHeureTravaillee());
	}

	
	public double calculheuresSupplementaire(Emplyee employee) {
		
		return (this.salaryParHeure)*(employee.getHeuresSupplementaires());
	}

	public double calculheuresAbscences(Emplyee employee) {
		
		return (this.salaryParHeure)*(employee.getHeuresAbscences());
	}

	

	

}
