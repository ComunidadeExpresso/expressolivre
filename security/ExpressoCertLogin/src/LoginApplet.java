
import java.awt.Frame;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.security.GeneralSecurityException;
import java.security.ProviderException;

import javax.net.ssl.SSLHandshakeException;
import javax.swing.JApplet;
import javax.swing.JOptionPane;
import javax.swing.SwingUtilities;
import org.apache.commons.httpclient.HttpException;

import netscape.javascript.JSObject;

import br.gov.serpro.cert.DigitalCertificate;
import br.gov.serpro.setup.Setup;
import br.gov.serpro.ui.DialogBuilder;

/**
 * GUI que realiza o login com certificados A1 e A3 ou login com usu�rio e senha no expresso.
 * Esta classe estende a classe JApplet
 * @author M�rio C�sar Kolling - mario.kolling@serpro.gov.br
 */
public class LoginApplet extends JApplet {

    /**
     * Valor gerado aleatoriamente
     */
    //TODO: Alterar a cor e fonte dos labels e das caixas de texto e senha.
    private static final long serialVersionUID = -6204158613173951516L;
    private DigitalCertificate dc;
    private Setup setup;

    /* (non-Javadoc)
     * @see java.applet.Applet#init()
     */
    public void init() {
        super.init();
        this.setSize(0, 0);
        this.setup = new Setup(this);
        this.setup.addLanguageResource("ExpressoCertLoginMessages");
    }
    
    private boolean parseVercert(String[] answer, String certificate){
        boolean tryAgain = false;
        // Faz o login
        if (setup.getParameter("debug").equalsIgnoreCase("true") && answer != null) {
            System.out.println("C�digo de retorno: " + answer[0].trim());
        }

        if (answer == null){ // A��o cancelada
            tryAgain = false;
            String redirect = this.getCodeBase().getProtocol() + "://" + this.getCodeBase().getHost()
                    + ":" + this.getCodeBase().getPort() + "/login.php";
            try {
                this.getAppletContext().showDocument(new URL(redirect));
            } catch (MalformedURLException e) {
                // TODO Bloco catch gerado automaticamente
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    e.printStackTrace();
                }
            }
        }
        else if (Integer.parseInt(answer[0].trim()) == 0) {

            tryAgain = false;
            // Pega usu�rio e senha de credentials[1] e credentials[2], respectivamente
            // adiciona na p�gina e faz o submit.
            JSObject document = (JSObject) JSObject.getWindow(this).getMember("document");
            JSObject loginForm = (JSObject) document.getMember("flogin");
            JSObject loginField = (JSObject) loginForm.getMember("user");
            loginField.setMember("value", answer[1].trim());

            JSObject passwdField = (JSObject) loginForm.getMember("passwd");
            passwdField.setMember("value", answer[2].trim());

            JSObject certificateField = (JSObject) loginForm.getMember("certificado");
            certificateField.setMember("value", certificate.trim());

            loginForm.call("submit", null);
            Thread.yield();

        } else if (Integer.parseInt(answer[0].trim()) == 6) {

            tryAgain = false;

            if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                System.out.println("Mensagem de retorno: " + answer[1].trim());
            }

            DialogBuilder.showMessageDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this), answer[1].trim(), this.setup);

            String redirect = this.getCodeBase().getProtocol() + "://" + this.getCodeBase().getHost()
                    + ":" + this.getCodeBase().getPort() + "/login.php?cd=98&ts=202";
            try {
                this.getAppletContext().showDocument(new URL(redirect));
            } catch (MalformedURLException e) {
                // TODO Bloco catch gerado automaticamente
                if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
                    e.printStackTrace();
                }
            }
        } else {
            tryAgain = true;
            dc.destroy();
            System.gc();

            if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                System.out.println("Mensagem de retorno: " + answer[1].trim());
            }

            // Mostra mensagem de erro para o usu�rio
            DialogBuilder.showMessageDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this), answer[1].trim(), this.setup);
            Thread.yield();
        }

        return tryAgain;
    }

    private boolean parseHandleCertificateResponse(String certificate){
        
        // Envia certificado
        
        JSObject document = (JSObject) JSObject.getWindow(this).getMember("document");
        JSObject certificateForm = (JSObject) document.getMember("certificateForm");
        JSObject certificateField = (JSObject) certificateForm.getMember("certificado");
        certificateField.setMember("value", certificate);

        // submit e cai fora
        certificateForm.call("submit", null);
       
        dc.destroy();
        System.gc();
        Thread.yield();

        return false;
    }

    /* (non-Javadoc)
     * @see java.applet.Applet#start()
     */
    @Override
    public void start() {
        super.start();

        int useCertificate = DigitalCertificate.KEYSTORE_NOT_DETECTED;
        boolean tryAgain = true;

        do {

            // Cria uma inst�ncia de DigitalCertificate e a inicializa
            // Aqui pega document base e verifica em que aplica��o estamos.

            this.dc = new DigitalCertificate(this.getDocumentBase(), setup);
            useCertificate = dc.init();

            try {

                String redirect = "";

                // Testa em qual aplica��o estamos.
                URL documentURL = this.getDocumentBase();

                if (documentURL.getPath().matches(".*login.php$")){
                    redirect = this.getCodeBase().getProtocol() + "://" + this.getCodeBase().getHost()
                        + ":" + this.getCodeBase().getPort() + "/login.php";
                }
                else {
                    redirect = this.getCodeBase().getProtocol() + "://" + this.getCodeBase().getHost()
                        + ":" + this.getCodeBase().getPort() + "/preferences/index.php";
                }

                switch (useCertificate) {
                    case DigitalCertificate.KEYSTORE_DETECTED:
                        // Mostra PinNeedeDialog.
                        String pin = DialogBuilder.showPinDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this), this.setup);
                        
                        if (pin != null) {
                            dc.openKeyStore(pin.toCharArray());
                            if (documentURL.getPath().matches(".*login.php$")){
                                tryAgain = parseVercert(dc.getCredentials(pin, new URL(this.getCodeBase().getProtocol()+"://" +
                                    this.getCodeBase().getHost() + ":" + this.getCodeBase().getPort() +
                                    "/security/vercert.php")), dc.getPEMCertificate());
                            }
                            else {
                                tryAgain = parseHandleCertificateResponse(dc.getPEMCertificate());
                            }

                        } else {

                            // TODO: Notifica usu�rio
                            tryAgain = false;
                            try {
                                this.getAppletContext().showDocument(new URL(redirect));
                            } catch (MalformedURLException e) {
                                // TODO Bloco catch gerado automaticamente
                                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                                    e.printStackTrace();
                                }
                            }
                        }

                        break;
                    default:

                        // TODO: notifica usu�rio que token n�o foi encontrado
                        // ou reposit�rio de chaves p�blicas n�o foi configurado.
                        // Tentar carregar token/keystore novamente? / Logon sem certificado digital?

                        tryAgain = false;
                        if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                            System.out.println("n�o achou token");
                        }

                        dc.destroy();
                        System.gc();

                        try {
                            this.getAppletContext().showDocument(new URL(redirect));
                        } catch (MalformedURLException e) {
                            // TODO Bloco catch gerado automaticamente
                            if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                                e.printStackTrace();
                            }
                        }
                }

            } catch (SSLHandshakeException e) {
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    e.printStackTrace();
                }
                dc.destroy();
                System.gc();
                DialogBuilder.showMessageDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this),
                        this.setup.getLang("ExpressoCertLoginMessages", "LoginApplet001"), this.setup);

                Thread.yield();

                tryAgain = true;
            } catch (HttpException e) {
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    e.printStackTrace();
                }

                tryAgain = true;
                Thread.yield();
            } catch (GeneralSecurityException e) {
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    e.printStackTrace();
                }

                DialogBuilder.showMessageDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this),
                        this.setup.getLang("ExpressoCertLoginMessages", "LoginApplet002"), this.setup);

                Thread.yield();
                tryAgain = true;
            } catch (IOException e) {
                dc.destroy();
                System.gc();

                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    e.printStackTrace();
                }

                Throwable cause = null;
                if ((cause = e.getCause()) != null) {
                    if (cause instanceof javax.security.auth.login.LoginException) {
                        DialogBuilder.showMessageDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this), this.setup.getLang("ExpressoCertLoginMessages", "LoginApplet003"), this.setup);
                    } else {
                        if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                            System.out.println("Exception lan�ada: " + cause.getClass().getCanonicalName());
                        }
                    }
                } else {
                    if (e instanceof java.net.ConnectException) {
                        DialogBuilder.showMessageDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this),
                                this.setup.getLang("ExpressoCertLoginMessages", "LoginApplet004"), this.setup);
                    } else {
                        if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                            System.out.println("Exception lan�ada: " + e.getClass().getCanonicalName());
                        }
                    }
                }

                Thread.yield();
                tryAgain = true;
            } catch (ProviderException e) {

                dc.destroy();
                System.gc();

                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    e.printStackTrace();
                }

                int resultado = DialogBuilder.showConfirmDialog((Frame) SwingUtilities.getAncestorOfClass(Frame.class, this),
                        //"Deseja tentar carreg�-lo novamente?",
                        this.setup.getLang("ExpressoCertLoginMessages", "LoginApplet005"),
                        JOptionPane.QUESTION_MESSAGE,
                        JOptionPane.OK_CANCEL_OPTION, this.setup);

                if (resultado == JOptionPane.OK_OPTION) {
                    tryAgain = true;
                } else {
                    tryAgain = false;
                }

                Thread.yield();
            }

        } while (tryAgain);

    }

    /**
     * Destr�i a Applet, executando c�digos para desregistrar tokens, keystores, etc.
     */
    @Override
    public void stop() {
        //super.destroy();
        if (setup.getParameter("debug").equalsIgnoreCase("true")) {
            System.out.println("Finalizando Applet de Login!");
        }

        this.dc.destroy();
        this.dc = null;
        System.gc();
    }
}
