/*
 * $HeadURL$
 * $Revision$
 * $Date$
 *
 * ====================================================================
 *
 *  Licensed to the Apache Software Foundation (ASF) under one or more
 *  contributor license agreements.  See the NOTICE file distributed with
 *  this work for additional information regarding copyright ownership.
 *  The ASF licenses this file to You under the Apache License, Version 2.0
 *  (the "License"); you may not use this file except in compliance with
 *  the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 * ====================================================================
 *
 * This software consists of voluntary contributions made by many
 * individuals on behalf of the Apache Software Foundation.  For more
 * information on the Apache Software Foundation, please see
 * <http://www.apache.org/>.
 *
 */

package br.gov.serpro.cert;

import java.security.cert.X509Certificate;

import javax.net.ssl.X509TrustManager;
import javax.swing.JOptionPane;

import java.security.cert.CertificateException;
import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

/**
 * <p>
 * AuthSSLX509TrustManager can be used to extend the default {@link X509TrustManager}
 * with additional trust decisions.
 * </p>
 *
 * @author <a href="mailto:oleg@ural.ru">Oleg Kalnichevski</a>
 *
 * <p>
 * DISCLAIMER: HttpClient developers DO NOT actively support this component.
 * The component is provided as a reference material, which may be inappropriate
 * for use without additional customization.
 * </p>
 */

public class AuthSSLX509TrustManager implements X509TrustManager {

    private X509TrustManager defaultTrustManager = null;

    /** Log object for this class. */
    private static final Log LOG = LogFactory.getLog(AuthSSLX509TrustManager.class);

    /**
     * Constructor for AuthSSLX509TrustManager.
     */
    public AuthSSLX509TrustManager(final X509TrustManager defaultTrustManager) {
        super();
        if (defaultTrustManager == null) {
            throw new IllegalArgumentException("Trust manager may not be null");
        }
        this.defaultTrustManager = defaultTrustManager;
    }

    /**
     * @see javax.net.ssl.X509TrustManager#checkClientTrusted(X509Certificate[],String authType)
     */
    public void checkClientTrusted(X509Certificate[] certificates,String authType) throws CertificateException {
        if (LOG.isInfoEnabled() && certificates != null) {
            for (int c = 0; c < certificates.length; c++) {
                X509Certificate cert = certificates[c];
                LOG.info(" Client certificate " + (c + 1) + ":");
                LOG.info("  Subject DN: " + cert.getSubjectDN());
                LOG.info("  Signature Algorithm: " + cert.getSigAlgName());
                LOG.info("  Valid from: " + cert.getNotBefore() );
                LOG.info("  Valid until: " + cert.getNotAfter());
                LOG.info("  Issuer: " + cert.getIssuerDN());
            }
        }
        defaultTrustManager.checkClientTrusted(certificates,authType);
    }

    /**
     * @see javax.net.ssl.X509TrustManager#checkServerTrusted(X509Certificate[],String authType)
     */
    public void checkServerTrusted(X509Certificate[] certificates,String authType) throws CertificateException {
        if (LOG.isInfoEnabled() && certificates != null) {
            for (int c = 0; c < certificates.length; c++) {
                X509Certificate cert = certificates[c];
                LOG.info(" Server certificate " + (c + 1) + ":");
                LOG.info("  Subject DN: " + cert.getSubjectDN());
                LOG.info("  Signature Algorithm: " + cert.getSigAlgName());
                LOG.info("  Valid from: " + cert.getNotBefore() );
                LOG.info("  Valid until: " + cert.getNotAfter());
                LOG.info("  Issuer: " + cert.getIssuerDN());
            }
        }
    	// TODO: Implementar uma caixa de di�logo que pergunta para o usu�rio se ele quer aceitar o certificado do site
        // Implementado com try/catch usando JOptionPanel

        try {
        	defaultTrustManager.checkServerTrusted(certificates,authType);
        } catch (CertificateException e){
        	//Object[] options = {"Aceitar Certificado", "Aceitar Permanentemente", "Cancelar"};
            Object[] options = {"Aceitar Certificado", "Cancelar"};
        	switch (JOptionPane.showOptionDialog(null, "Falha na valida��o do seguinte certificado:\n" +
        			certificates[0].getSubjectX500Principal().getName(),
        			"\nO que voc� quer fazer?",
        			JOptionPane.YES_NO_CANCEL_OPTION, JOptionPane.WARNING_MESSAGE, null,
        			options, options[0]))
        	{
        		case 2:
        			// Rejeita certificado!
        			throw e;
        		case 1:
        			// Aceita certificado permanentemente
        			// TODO: Adicionar c�digo para inserir o certificado como um certificado confi�vel
        			break;
        			// Aceita certificado para esta sess�o

        	}
        }
    }

    /**
     * @see javax.net.ssl.X509TrustManager#getAcceptedIssuers()
     */
    public X509Certificate[] getAcceptedIssuers() {
        return this.defaultTrustManager.getAcceptedIssuers();
    }

}
