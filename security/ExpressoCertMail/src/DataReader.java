
public class DataReader {

	private boolean flag = false;
	private String resultado;

	public synchronized String getResultado(){
		lock();
		//notifyAll();
		return this.resultado;
	}

	public synchronized void setResultado(String resultado){
		//flag = true;
		this.resultado = resultado;
	}

	public synchronized void unlock(){
		flag = true;
		notifyAll();
	}

	private synchronized void lock(){
		while (!flag){
			try{
				wait();
			}
			catch (InterruptedException e){}
		}
		flag = false;
	}

}
