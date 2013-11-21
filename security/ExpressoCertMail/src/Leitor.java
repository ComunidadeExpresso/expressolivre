import br.gov.serpro.setup.Setup;
import netscape.javascript.JSObject;


class Leitor extends Thread {

	private DataReader dataReader;
	private JSObject page;
	private Setup setup;

	public Leitor(DataReader dataReader, JSObject page, Setup setup){
		this.dataReader = dataReader;
		this.page = page;
		this.setup = setup;
	}

	public void run() {
		// TODO Auto-generated method stub
		super.run();
		// chamar função no javascript
		while (true){
			if (this.setup.getParameter("debug").equalsIgnoreCase("true")){
				System.out.println("Classe executor: pegando resultado.");
			}

			String resultado = dataReader.getResultado();
			if (this.setup.getParameter("debug").equalsIgnoreCase("true")){
				System.out.println("Classe Executor: chamando função appletReturn() no javascript");
				System.out.println("Classe Executor: valor de retorno: " + resultado);
			}

			page.call("appletReturn", new String[] {resultado});

		}
	}

}
