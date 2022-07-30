
public class Base_Singleton {
	private static Base_Singleton instance = new Base_Singleton();
	private Base_Singleton(){
		System.out.println("test singleton");
	}
	public static Base_Singleton getinstance()
	{
		return instance;
	}
	
	public void test(){ System.out.println("ABCDEFG");}
	

}
