package revistaAssinatura;

public class Assinante1 implements Observer{

	//observa a revista
	Subject revistaInformatica= null;
	int edicaoNovaRevista;

	public Assinante1(RevistaInformatica revInfo)
	{
		this.revistaInformatica = revInfo;
		revInfo.Add(this);
	}
	
	@Override
	public void UpDate(Subject revistaInfo)
	{
		if (revistaInfo instanceof RevistaInformatica)
		{
			RevistaInformatica revistaInformatica = (RevistaInformatica) revistaInfo;
			edicaoNovaRevista = revistaInformatica.getEdicao();
			System.out.println("Atenção, já chegou a mais uma edição da Revista Informatica. " +
					"Esta é a sua edição número: " + edicaoNovaRevista);
		}
		
	}


}
