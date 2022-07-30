
public class test_singleton {

	public static void main(String[] args) {
		//Base_Singleton singleton=new Base_Singleton();
		Base_Singleton s=Base_Singleton.getinstance();
		Base_Singleton s1=Base_Singleton.getinstance();
		
		
		s.test();
		s1.test();
		
	}

}
