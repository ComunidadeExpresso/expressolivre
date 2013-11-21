package br.gov.serpro.js;

import java.awt.Frame;
import java.io.IOException;
import java.security.GeneralSecurityException;
import java.security.ProviderException;
import java.util.Map;

import javax.mail.MessagingException;

import org.bouncycastle.mail.smime.SMIMEException;
import br.gov.serpro.cert.DigitalCertificate;
import br.gov.serpro.setup.Setup;
import br.gov.serpro.ui.DialogBuilder;
import br.gov.serpro.util.Base64Utils;

import netscape.javascript.JSObject;
import org.bouncycastle.cms.CMSException;

public class ParamReaderThread extends Thread {

	JSObject page;
	Javascript2AppletPassingData data;
	Setup setup;
	Frame parentFrame;

	public ParamReaderThread(JSObject page, Javascript2AppletPassingData data, Setup setup, Frame parent) {
	//public ParamReaderThread(JSObject page, Javascript2AppletPassingData data, Setup setup) {
		super();
		this.page = page;
		this.data = data;
		this.setup = setup;
		this.parentFrame = parent;
	}

	@Override
	public void run() {
		// TODO Stub de método gerado automaticamente
		super.run();

            while (true){
                            if (setup.getParameter("debug").equalsIgnoreCase("true")){
                                    System.out.println("Classe ParamReaderThread: pegando resultado.");
                            }

                // processa o smime. Método sign implementado na classe DigitalCertificate
                String smime = null;
                DigitalCertificate dc = null;
                Map<String, String> parsedData = null;

                try {

                    //Map<String, String> parsedData = parseData(resultado);
                    parsedData = data.getMap();

                    dc = new DigitalCertificate(this.parentFrame, this.setup);
                    dc.init();

                    // Testa a operação e se for
                    if (parsedData.get("operation").equals("sign")){

                        smime = dc.signMail(parsedData);
                        if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                            System.out.println("\nMensagem assinada: " + smime);
                        }

                    }
                  else if (parsedData.get("operation").equals("decript")){
                        String decryptedMsg = dc.cipherMail(parsedData);
                        if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                            System.out.println("Mensagem decifrada: " + decryptedMsg);
                        }
                        if (decryptedMsg == null){
                            smime = null;
                        } else {
                            smime = Base64Utils.base64Encode(decryptedMsg.getBytes());
                        }

                    }
                    else {
                        throw new UnsupportedOperationException("Operation not supported: " + parsedData.get("operation"));
                        // Lança
                    }

                    // Retorna para a página
                    // se smime = null, a assinatura não funcionou

                } catch (IOException e) {
                    //DialogBuilder.showMessageDialog(this.parentFrame, "Não foi possível carregar Token/SmartCard: senha incorreta", this.setup);
                    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMailMessages", "ParamReaderThread001"), this.setup);
                    //JOptionPane.showMessageDialog(this.parentFrame, "Não foi possível carregar Token/SmartCard: senha incorreta",
                    //        "Aviso", JOptionPane.INFORMATION_MESSAGE);
                    if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                        e.printStackTrace();
                    }
                } catch (GeneralSecurityException e) {
                    if (e.getCause() != null){
                        DialogBuilder.showMessageDialog(this.parentFrame, "GeneralSecurityException: " + e.getCause().getMessage(), this.setup);
                        //JOptionPane.showMessageDialog(this.parentFrame, "GeneralSecurityException: " + e.getCause().getMessage(),
                        //        "Aviso", JOptionPane.INFORMATION_MESSAGE);
                    }
                    else {
                        DialogBuilder.showMessageDialog(this.parentFrame, "GeneralSecurityException: " + e.getMessage(), this.setup);
                        //JOptionPane.showMessageDialog(this.parentFrame, "GeneralSecurityException: " + e.getMessage(),
                        //        "Aviso", JOptionPane.INFORMATION_MESSAGE);
                    }
                    if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                        e.printStackTrace();
                    }
                } catch (SMIMEException e) {
                    //DialogBuilder.showMessageDialog(this.parentFrame, "Erro no processamento da assinatura: " + e.getMessage(), this.setup);
                    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMailMessages", "ParamReaderThread002"), this.setup);
                    //JOptionPane.showMessageDialog(this.parentFrame, "Erro no processamento da assinatura: " + e.getMessage(),
                    //        "Aviso", JOptionPane.INFORMATION_MESSAGE);
                    if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                        e.printStackTrace();
                    }
                } catch (MessagingException e) {
                    //DialogBuilder.showMessageDialog(this.parentFrame, "Erro no processamento da mensagem: " + e.getMessage(), this.setup);
                    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMailMessages", "ParamReaderThread003"), this.setup);
                    //JOptionPane.showMessageDialog(this.parentFrame, "Erro no processamento da mensagem: " + e.getMessage(),
                    //        "Aviso", JOptionPane.INFORMATION_MESSAGE);
                    if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                        e.printStackTrace();
                    }
                }
                catch (CMSException e){
                    //DialogBuilder.showMessageDialog(this.parentFrame, "Erro ao decifrar mensagem: Detectado problema na integridade da mensagem cifrada!", this.setup);
                    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMailMessages", "ParamReaderThread004"), this.setup);
                    //if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    Throwable cause = e.getCause();
                    System.out.println(e.getClass().getCanonicalName() + ": " + e.getMessage());
                    if (cause != null){
                        System.out.println(cause.getClass().getCanonicalName() + ": " + cause.getMessage());
                    }
                    e.printStackTrace();
                    //}
                }
                catch (ProviderException e){
                    //DialogBuilder.showMessageDialog(this.parentFrame, "Problema no acesso às informações do Token: " + e.getMessage(), this.setup);
                    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMailMessages", "ParamReaderThread005"), this.setup);
                    //JOptionPane.showMessageDialog(this.parentFrame, "Problema no acesso às informações do Token: " + e.getMessage(),
                    //        "Aviso", JOptionPane.INFORMATION_MESSAGE);
                    if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                        e.printStackTrace();
                    }
                }
                catch (UnsupportedOperationException e){
                    // DialogBuilder.showMessageDialog(this.parentFrame, e.getMessage());
                    if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                        e.printStackTrace();
                    }
                }
                catch (IllegalArgumentException e){
                    //DialogBuilder.showMessageDialog(this.parentFrame, e.getMessage());
                    if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                        e.printStackTrace();
                    }
                }
                catch (InterruptedException e){
                    if (setup.getParameter("debug").equalsIgnoreCase("true")){
                        System.out.println("Classe ParamReaderThread: Thread has been interrupted! Break.");
                        e.printStackTrace();
                    }
                    break;
                }
                finally {

                    page.call("appletReturn", new String[]{smime, parsedData.get("ID"), parsedData.get("operation"), parsedData.get("folder")});

                }

                dc.destroy();
                dc = null;
                System.gc();
                Thread.yield();

            }
	}
	//TODO: Documentar o que recebe!!!
	/*private Map<String, String> parseData(String expressoMailData){

        if ((expressoMailData == null) || (expressoMailData.length() == 0)){
            throw new IllegalArgumentException("Can't unserialize NULL or empty value!");
        }

		Map<String, String> parsedData = new HashMap<String, String> ();
		//Map<String, String> headersData = new HashMap<String, String>();

		for (String paramsArray : expressoMailData.split(";")){

			if (this.setup.getParameter("debug").equalsIgnoreCase("true")){
				System.out.println("sendo parseado: " + paramsArray);
			}
			String[] param = paramsArray.split(":");
			//if (temp[0].equals("headers")){
			//	String[] headersArray = new String(Base64Utils.base64Decode(temp[1])).split(";");
			//	for (String header: headersArray){
			//		String[] keyValueType = header.split(":");
			//		headersData.put(keyValueType[0], keyValueType[1]);
			//	}
			//}
			//else{
			parsedData.put(param[0], new String(Base64Utils.base64Decode(param[1])));
			//}

		}

		return parsedData;
	}

	static private String processReturn(Map smimeData){

		return new String();
	}
*/
}
