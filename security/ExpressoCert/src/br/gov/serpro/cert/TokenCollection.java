/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

package br.gov.serpro.cert;

import br.gov.serpro.setup.Setup;
import java.io.IOException;
import java.util.HashMap;
import java.util.logging.Level;
import java.util.logging.Logger;
import sun.security.pkcs11.wrapper.CK_C_INITIALIZE_ARGS;
import sun.security.pkcs11.wrapper.CK_TOKEN_INFO;
import sun.security.pkcs11.wrapper.PKCS11;
import sun.security.pkcs11.wrapper.PKCS11Exception;
import static sun.security.pkcs11.wrapper.PKCS11Constants.*;

/**
 *
 * @author esa
 */
class TokenCollection extends HashMap<String, Token>{
    
    private String preferedTokenKey;
    private final Setup setup;

    public TokenCollection(Setup setup){
       
        this.setup = setup;
        this.addTokens(setup.getParameter("token"));

    }

    public void setPreferedToken(java.lang.String preferedTokenKey){
        
    }

    public String getPreferedToken(){
        return preferedTokenKey;
    }

    private void addTokens(String tokens){
        
        String[] tokensArray = tokens.split(",");
        for (int i = 0; i < tokensArray.length; i++){
            if (tokensArray[i] != null && tokensArray[i].length() > 0){
                String[] tokenArray = tokensArray[i].split(";");
                Token token = new Token(tokenArray[0], tokenArray[1], this.setup);

                // Aqui testar se existe token inserido em algum slot para determinado driver.
                // Pega sempre o primeiro slot registrado com a lib tokenArray[1]
                // TODO: Deixar o usuário escolher o token que vai usar;
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    System.out.println("Getting slots from " + tokenArray[1]);
                }
                try {
                    long[] slots = getSlotsWithTokens(tokenArray[1]);
                    if (slots.length > 0){
                        token.registerToken(slots[0]);
                        if (token.isRegistered()){
                            this.put(token.getName(), token);
                        }
                    }
                } catch (IOException iex){
                    continue;
                }
            }
        }
    }

    public long[] getSlotsWithTokens(String libraryPath) throws IOException{
        CK_C_INITIALIZE_ARGS initArgs = new CK_C_INITIALIZE_ARGS();
        String functionList = "C_GetFunctionList";

        initArgs.flags = CKF_OS_LOCKING_OK;
        PKCS11 tmpPKCS11 = null;
        long[] slotList = null;
        try {
            try {
                tmpPKCS11 = PKCS11.getInstance(libraryPath, functionList, initArgs, false);
            } catch (IOException ex) {
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    Logger.getLogger(TokenCollection.class.getName()).log(Level.SEVERE, null, ex);
                }
                throw ex;
            }
        } catch (PKCS11Exception e) {
            try {
                initArgs = null;
                tmpPKCS11 = PKCS11.getInstance(libraryPath, functionList, initArgs, true);
            } catch (IOException ex) {
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    Logger.getLogger(TokenCollection.class.getName()).log(Level.SEVERE, null, ex);
                }
            } catch (PKCS11Exception ex) {
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    Logger.getLogger(TokenCollection.class.getName()).log(Level.SEVERE, null, ex);
                }
            }
        }

        try {
            slotList = tmpPKCS11.C_GetSlotList(true);

            for (long slot : slotList){
                CK_TOKEN_INFO tokenInfo = tmpPKCS11.C_GetTokenInfo(slot);
                if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                    System.out.println("slot: "+slot+"\nmanufacturerID: "
                            + String.valueOf(tokenInfo.manufacturerID) + "\nmodel: "
                            + String.valueOf(tokenInfo.model));
                }
            }
        } catch (PKCS11Exception ex) {
            if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                Logger.getLogger(TokenCollection.class.getName()).log(Level.SEVERE, null, ex);
            }
        } catch (Throwable t) {
            if (setup.getParameter("debug").equalsIgnoreCase("true")) {
                Logger.getLogger(TokenCollection.class.getName()).log(Level.SEVERE, null, t);
            }
        }

        return slotList;

    }
}
