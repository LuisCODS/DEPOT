package examenFinal;

//O que � observado implementa essa interface
public interface Subject {

	public void Add(Observer o);	
	public void Delete(Observer o);
	public void Notify(Vol v);
	
}
