import java.awt.Frame;

import javax.swing.JApplet;
import javax.swing.SwingUtilities;

import netscape.javascript.JSObject;

import br.gov.serpro.js.Javascript2AppletPassingData;
import br.gov.serpro.js.ParamReaderThread;
import br.gov.serpro.setup.Setup;

//TODO: Logs de depuração e interface para usuário reportar problemas.

public class ExpressoSmimeApplet extends JApplet {

	/**
	 * Variáveis de instância
	 */
	private Setup setup;
	private Javascript2AppletPassingData dataReader;
	private Thread paramReaderThread;

	/**
	 * Variáveis de classe
	 */
	private static final long serialVersionUID = 4797603392324194391L;

	@Override
	public void init() {
		super.init();
		this.setSize(1, 1);
		this.setup = new Setup(this);
        this.setup.addLanguageResource("ExpressoCertMailMessages");
		this.dataReader = new Javascript2AppletPassingData();
		// this.paramReaderThread = new ParamReaderThread(JSObject.getWindow(this), dataReader, setup);
		this.paramReaderThread = new ParamReaderThread(JSObject.getWindow(this), dataReader,
				setup, (Frame) SwingUtilities.getAncestorOfClass(Frame.class, this));
		this.paramReaderThread.start();
	}

	@Override
    /**
     * Retorna Informações sobre os parâmetros que essa applet aceita
     * @author Mário César Kolling <mario.kolling@serpro.gov.br>
     * @return String[][] Uma matriz de Strings relacionando cada parâmetro à sua descrição
     */
	public String[][] getParameterInfo() {
		return setup.getParameterInfo();
	}

	@Override
	public void start() {
		super.start();

	}

	@Override
	public void stop() {
		super.stop();
		//dataReader.unlock();

        if (this.paramReaderThread.isAlive()){
            if (setup.getParameter("debug").equalsIgnoreCase("true")){
                System.out.println("Interrompendo Applet paramReaderThread");
            }
            this.paramReaderThread.interrupt();
        }
	}

	/**
     * Método da Applet chamado pela página (js) ao assinar ou decifrar um e-mail
	 * @param resultado Dados serializados passados pela Applet
	 */
	public void doButtonClickAction(String resultado){
		dataReader.setData(resultado);
		dataReader.unlock();
		//return "cert";
	}

    public void doButtonClickAction(String operation, String id, String body){
        dataReader.setData(operation, id, body);
        //dataReader.unlock();
    }

    public void doButtonClickAction(String operation, String id, String body, String folder){
        dataReader.setData(operation, id, body, folder);
        //dataReader.unlock();
    }

}
