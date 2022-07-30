package revistaAssinatura;
import java.util.ArrayList;

public class RevistaInformatica implements Subject{

	private int edicao;
	ArrayList<Observer> observers = new ArrayList<Observer>();

		
	public void setEdicao(int edicao) {
		this.edicao = edicao;
		this.Notify();
	}
	public int getEdicao() {
		return this.edicao;
	}
	
	@Override
	public void Add(Observer o) {
		observers.add(o);		
	}
	@Override
	public void Delete(Observer o) {
		observers.remove(o);
	}
	@Override
	public void Notify() {
		for (Observer o : observers) {			
			o.UpDate(this);	
		}	
	}

}
