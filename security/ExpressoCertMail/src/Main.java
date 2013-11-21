import java.applet.Applet;

import netscape.javascript.JSObject;

import br.gov.serpro.cert.DigitalCertificate;
import br.gov.serpro.setup.Setup;


public class Main extends Applet{

	//private DigitalCertificate teste;
	private Thread executaTeste;
	private DataReader dataReader;
	private JSObject page;
	private Setup setup;

	/**
	 *
	 */
	private static final long serialVersionUID = 1726731542858100340L;

	/**
	 *
	 */
	public void start() {
		this.setup = new Setup(this);
		//this.teste = new DigitalCertificate();
		this.dataReader = new DataReader();
		//this.data.setResultado(this.teste.init());
		this.executaTeste = new Leitor(dataReader, JSObject.getWindow(this), setup);
		this.executaTeste.start();

		//this.page = JSObject.getWindow(this);
		/*try{
			SwingUtilities.invokeAndWait(new Runnable(){

				public void run() {
					// TODO Auto-generated method stub
					int resultado = teste.init();
					System.out.println("Event Dispatching Thread: Resultado após leitura do token" + resultado);
					dataReader.setResultado(resultado);
				}

			});
		}
		catch (InterruptedException e){
			e.printStackTrace();
		}
		catch (InvocationTargetException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		*/
	}

	//TODO: Testar a passagem de parâmetros do javascript para a applet
	/**
	 * @param args
	 */
	public void doButtonClickAction(String resultado){
		dataReader.setResultado(resultado);
		dataReader.unlock();
		//return "cert";
	}

	//public class Executor extends Thread {
		//
	//	@Override
	//	public void run() {
	//		// TODO Auto-generated method stub
	//		super.run();
//
	//		// chamar função no javascript
	//		System.out.println("Classe executor: pegando resultado.");
	//		int resultado = dataReader.getResultado();
	//		page.call("mostraResultado", new String[] {Integer.toString(resultado)});

	//	}

	//}

}
