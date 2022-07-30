package solid;



public abstract class  IMessage {
  
	        public String ToAddresses ;
	        public String MessageBody ;
	        public String Subject ;
	        public String BccAddresses;
	        public abstract boolean Send();
	        
	        
			public String getToAddresses() {
				return ToAddresses;
			}
			public void setToAddresses(String toAddresses) {
				ToAddresses = toAddresses;
			}
			public String getMessageBody() {
				return MessageBody;
			}
			public void setMessageBody(String messageBody) {
				MessageBody = messageBody;
			}
			public String getSubject() {
				return Subject;
			}
			public void setSubject(String subject) {
				Subject = subject;
			}
			public String getBccAddresses() {
				return BccAddresses;
			}
			public void setBccAddresses(String bccAddresses) {
				BccAddresses = bccAddresses;
			}
	        
	   

}

