package hdr;

public class testEmploye {

	public static void main(String[] args) {
		Employe e1= new Employe("jean", 1,new Salary(145));
		Employe e2= new Employe("marc", 1,new Salary(15));
		Employe e3= new Employe("hu", 1,new Salary(35));
		Employe e4= new Employe("carlos", 1,new Salary(90));
		EmployeeManagement empmgt=new EmployeeManagement();
		
		
		PayementEmployee payementEmploye= new PayementEmployee();
		
		Hdr hdr =new Hdr();
		
		empmgt.Subscribe(payementEmploye);
		empmgt.Subscribe(hdr);
		
		
		
		
		
		empmgt.addEmploye(e1);
		empmgt.addEmploye(e2);
		empmgt.addEmploye(e3);
		empmgt.addEmploye(e4);
		

	}

}
