package midterm;

import java.time.LocalDate;
import java.time.ZoneId;
import java.util.*;

 public class CreditCardnew {
	 long number ;
	 Date experitation_dtae;
	 public String cvv;
	 public String type;
	 
//Contructeur	 
	 
public CreditCardnew(long number, Date experitation_dtae, String cvv, String type) {
		
		this.number = number;
		this.experitation_dtae = experitation_dtae;
		this.cvv = cvv;
		this.type = type;
	}


 public boolean  IsvalidCardNumber(){
	// cette ligne convertit le "number" to "string"
	String numberstring=String.valueOf(number);
	boolean isValid=false;
    if (numberstring.startsWith("4")&& numberstring.length()==15)
          {type= "Visa";
           LocalDate localDate = LocalDate.now();
           Date today = new Date(localDate.atStartOfDay(ZoneId.of("America/New_York")).toEpochSecond() * 1000);
            		if(experitation_dtae.after(today)&&LIHM(number))
            		{   
            			isValid=true;
            		}
            }
                        
      else if (numberstring.startsWith("5")&& numberstring.length()==16)
      {
    	  type= "MasterCard";
          LocalDate localDate = LocalDate.now();
          Date today = new Date(localDate.atStartOfDay(ZoneId.of("America/New_York")).toEpochSecond() * 1000);
           		if(experitation_dtae.after(today)&&LIHM(number))
           		{   
           			isValid=true;
           		}
           }
    return isValid;
      }
                        
              
  //tout ce qui suit concerne l'algorithme LIHM, vous les laissez joints        
            
            public boolean LIHM(long number)

            {long total = sumOfEvenPlaces(number) + (sumOfOddPlaces(number)*2);
            boolean isvalid=isValid(total);

            return isvalid;
        }
 
            


        public static boolean isValid(long total) {
            if (total % 10 != 0) {
            } else {
                        return true;
                }
            return false;
        }

        public static int sumOfEvenPlaces(long number) {
            int sum = 0;
            int remainder;
            while (number % 10 != 0 || number / 10 != 0) {
                remainder = (int) (number % 10);

                sum = sum + getDigit(remainder * 2);
                number /= 100;
            }
            return sum;
        }

        public static int getDigit(int number) {
            if (number > 9) {
                return (number % 10 + number / 10);
            } 
            return number;
        }

        public static int sumOfOddPlaces(long number) {
            int sum = 0;
            int remainder;
            number /= 10;
            while (number % 10 != 0 || number / 10 != 0) {
                remainder = (int) (number % 10);
                sum = sum + getDigit(remainder * 2);
                number /= 100;
            }
            return sum;
        }
    }


