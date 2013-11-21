package br.gov.serpro.cert;

import br.gov.serpro.setup.Setup;
import br.gov.serpro.cert.Token;
import java.awt.Frame;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.MalformedURLException;
import java.net.URL;
import java.security.AuthProvider;
import java.security.GeneralSecurityException;
import java.security.Key;
import java.security.KeyPair;
import java.security.KeyStore;
import java.security.KeyStoreException;
import java.security.PrivateKey;
import java.security.Provider;
import java.security.ProviderException;
import java.security.Security;
import java.security.cert.CertStore;
import java.security.cert.Certificate;
import java.security.cert.CollectionCertStoreParameters;
import java.security.cert.X509Certificate;
import java.util.ArrayList;
import java.util.Enumeration;
import java.util.List;
import java.util.Map;
import java.util.Properties;

import javax.crypto.Cipher;
import javax.mail.Message;
import javax.mail.MessagingException;
import javax.mail.Session;
import javax.mail.internet.MimeBodyPart;
import javax.mail.internet.MimeMessage;
import javax.mail.internet.MimeMultipart;
import javax.net.ssl.SSLHandshakeException;
import javax.security.auth.login.LoginException;

import org.apache.commons.httpclient.HttpClient;
import org.apache.commons.httpclient.HttpException;
import org.apache.commons.httpclient.methods.PostMethod;
import org.apache.commons.httpclient.protocol.Protocol;
import org.apache.commons.httpclient.protocol.ProtocolSocketFactory;
import org.bouncycastle.asn1.ASN1EncodableVector;
import org.bouncycastle.asn1.cms.AttributeTable;
import org.bouncycastle.asn1.smime.SMIMECapability;
import org.bouncycastle.asn1.smime.SMIMECapabilityVector;
import org.bouncycastle.mail.smime.SMIMEException;
import org.bouncycastle.mail.smime.SMIMESignedGenerator;

import br.gov.serpro.ui.DialogBuilder;
import br.gov.serpro.util.Base64Utils;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.security.AlgorithmParameters;
import java.security.NoSuchProviderException;
import java.security.cert.CertificateEncodingException;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import javax.activation.CommandMap;
import javax.activation.MailcapCommandMap;
import javax.mail.internet.ContentType;
import javax.mail.internet.MimeUtility;
import javax.mail.internet.PreencodedMimeBodyPart;
import org.bouncycastle.cms.CMSException;
import org.bouncycastle.cms.RecipientId;
import org.bouncycastle.cms.RecipientInformation;
import org.bouncycastle.cms.RecipientInformationStore;
import org.bouncycastle.mail.smime.SMIMEEnvelopedParser;
import org.bouncycastle.mail.smime.SMIMEUtil;

/**
 * Classe que realiza todo o trabalho realizado com o certificado
 * @author Mário César Kolling - mario.kolling@serpro.gov.br
 */
//TODO: Criar exceções para serem lançadas, entre elas DigitalCertificateNotLoaded
//TODO: Adicionar setup
public class DigitalCertificate {

    private TokenCollection tokens;
    private String selectedCertificateAlias;
    private Certificate cert; // Certificado extraído da KeyStore. Pode ser nulo.
    private KeyStore keyStore; // KeyStore que guarda o certificado do usuário. Pode ser nulo.
    private Frame parentFrame;
    private Setup setup;
    // TODO: Transformar pkcs12Input em uma string ou URL com o caminho para a KeyStore pkcs12
    private FileInputStream pkcs12Input; // stream da KeyStore pkcs12. Pode ser nulo.
    private String providerName; // Nome do SecurityProvider pkcs11 carregado. Pode ser nulo.
    private URL pageAddress; // Endereço do host, onde a página principal do
    private static final String HOME_SUBDIR; // Subdiretório dentro do diretório home do usuário. Dependente de SO.
    private static final String EPASS_2000; // Caminho da biblioteca do token ePass2000. Dependente de SO.
    private static final String CRLF = "\r\n"; // Separa campos na resposta do serviço de verificação de certificados
    private static final String SUBJECT_ALTERNATIVE_NAME = "2.5.29.17"; // Não é mais utilizado.
    private static final URL[] TRUST_STORES_URLS = new URL[3]; // URLs (file:/) das TrustStores, cacerts (jre),
    // trusted.certs e trusted.jssecerts (home do usuário)
    // Utilizadas para validação do certificado do servidor.
    private static final String[] TRUST_STORES_PASSWORDS = null; // Senhas para cada uma das TrustStores,
    // caso seja necessário.
    private int keystoreStatus;
    public static final int KEYSTORE_DETECTED = 0;
    public static final int KEYSTORE_NOT_DETECTED = 1;
    public static final int KEYSTORE_ALREADY_LOADED = 2;

    /*
     * Bloco estático que define os caminhos padrões da instalação da jre,
     * do diretório home do usuário, e da biblioteca de sistema do token ePass2000,
     * de acordo com o sistema operacional.
     */
    static {

	Properties systemProperties = System.getProperties();
	Map<String, String> env = System.getenv();

	/* TODO: Testar a existência de vários drivers de dispositivos. Determinar qual deve ser utilizado
	 * e guardar em uma property no subdiretório home do usuário.
	 */

	if (systemProperties.getProperty("os.name").equalsIgnoreCase("linux")) {
	    HOME_SUBDIR = "/.java/deployment/security";
	    EPASS_2000 = "/usr/lib/libepsng_p11.so";
	} else {
	    HOME_SUBDIR = "\\dados de aplicativos\\sun\\java\\deployment\\security";
	    EPASS_2000 = System.getenv("SystemRoot") + "\\system32\\ngp11v211.dll";
	    //EPASS_2000 = System.getenv("ProgramFiles")+"\\Gemplus\\GemSafe Libraries\\BIN\\gclib.dll";
	}

	try {
	    if (systemProperties.getProperty("os.name").equalsIgnoreCase("linux")) {
		TRUST_STORES_URLS[0] = new File(systemProperties.getProperty("java.home") + "/lib/security/cacerts").toURI().toURL();
		TRUST_STORES_URLS[1] = new File(systemProperties.getProperty("user.home") + HOME_SUBDIR + "/trusted.certs").toURI().toURL();
		TRUST_STORES_URLS[2] = new File(systemProperties.getProperty("user.home") + HOME_SUBDIR + "/trusted.jssecerts").toURI().toURL();
	    } else {

		TRUST_STORES_URLS[0] = new File(systemProperties.getProperty("java.home") +
			"\\lib\\security\\cacerts").toURI().toURL();
		TRUST_STORES_URLS[1] = new File(systemProperties.getProperty("user.home") +
			HOME_SUBDIR + "\\trusted.certs").toURI().toURL();
		TRUST_STORES_URLS[2] = new File(systemProperties.getProperty("user.home") +
			HOME_SUBDIR + "\\trusted.jssecerts").toURI().toURL();
	    }

	    // Define os tipos smime no mailcap
	    MailcapCommandMap mailcap = (MailcapCommandMap) CommandMap.getDefaultCommandMap();

	    mailcap.addMailcap("application/pkcs7-signature;; x-java-content-handler=org.bouncycastle.mail.smime.handlers.pkcs7_signature");
	    mailcap.addMailcap("application/pkcs7-mime;; x-java-content-handler=org.bouncycastle.mail.smime.handlers.pkcs7_mime");
	    mailcap.addMailcap("application/x-pkcs7-signature;; x-java-content-handler=org.bouncycastle.mail.smime.handlers.x_pkcs7_signature");
	    mailcap.addMailcap("application/x-pkcs7-mime;; x-java-content-handler=org.bouncycastle.mail.smime.handlers.x_pkcs7_mime");
	    mailcap.addMailcap("multipart/signed;; x-java-content-handler=org.bouncycastle.mail.smime.handlers.multipart_signed");

	    CommandMap.setDefaultCommandMap(mailcap);



	} catch (MalformedURLException e) {
	    e.printStackTrace();
	}
    }

    /**
     *
     */
    public DigitalCertificate() {
	this.pageAddress = null;
	this.parentFrame = null;
    }

    /**
     * Construtor da classe. Recebe a {@link URL} da página em que a Applet está incluída.
     * @param pageAddress URL da página em que a Applet está incluída
     */
    private DigitalCertificate(URL pageAddress) {
	this.pageAddress = pageAddress;
	this.parentFrame = null;
    }

    private DigitalCertificate(Frame parent) {
	this.pageAddress = null;
	this.parentFrame = parent;
    }

    public DigitalCertificate(Frame parent, Setup setup) {
	this(parent);
	this.setup = setup;
    }

    public DigitalCertificate(URL pageAddress, Setup setup) {
	this(pageAddress);
	this.setup = setup;
    }

    public KeyStore getKeyStore() {
	return keyStore;
    }

    public int getKeystoreStatus() {
	return keystoreStatus;
    }

    public String getProviderName() {
	return providerName;
    }

    /**
     * Destrói a Applet, removendo o security provider inicializado se o atributo providerName
     * for diferente de nulo.
     */
    public void destroy() {

	AuthProvider ap = null;

	if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("logout no provider");
	}
	if (keyStore != null) {
	    ap = (AuthProvider) this.keyStore.getProvider();
	}

	if (ap != null) {
	    try {
		ap.logout();
	    } catch (LoginException e) {
		if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
		    e.printStackTrace();
		}
	    }
	}

	if (providerName != null) {
	    Security.removeProvider(providerName);
	}

	this.cert = null;
        this.selectedCertificateAlias = null;
	this.keyStore = null;
	this.pkcs12Input = null;
	this.providerName = null;

    }

    /**
     * Procura pelo token nos locais padrões (Por enquanto só suporta o token ePass200),
     * senão procura por um certificado A1 em System.getProperties().getProperty("user.home") +
     * HOME_SUBDIR  + "/trusted.clientcerts" e retorna um inteiro de acordo com resultado desta procura.
     *
     * @author	Mário César Kolling
     * @return	Retorna um destes valores inteiros DigitalCertificate.KEYSTORE_DETECTED,
     * 		DigitalCertificate.KEYSTORE_ALREADY_LOADED ou DigitalCertificate.KEYSTORE_NOT_DETECTED
     * @see	DigitalCertificate
     */
    public int init() {

	// TODO: Usar dentro de um "loop" para testar outros modelos de tokens.
	this.tokens = new TokenCollection(setup);

        Provider[] providers = Security.getProviders();
        if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
            for (Provider provider : providers) {
                System.out.println(provider.getInfo());
            }
        }

        int interfaceType = DigitalCertificate.KEYSTORE_DETECTED;

	try {
	    // Tenta abrir o Token padrï¿½o (ePass2000).
	    loadKeyStore();

	} catch (Exception e1) {

	    if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
		// Não conseguiu abrir o token (ePass2000).
		System.out.println("Erro ao ler o token: " + e1.getMessage());
	    }

	    try {
		// Tenta abrir a keyStore padrão
		// USER_HOME/deployment/security/trusted.clientcerts

		Properties props = System.getProperties();
		pkcs12Input = new FileInputStream(props.getProperty("user.home") + HOME_SUBDIR + "/trusted.clientcerts");

		// Se chegar aqui significa que arquivo de KeyStore existe.
		keyStore = KeyStore.getInstance("JKS");

	    } catch (Exception ioe) {
		// Não conseguiu abrir a KeyStore pkcs12
		if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
		    System.out.println(ioe.getMessage());
		}
	    }
	}


	if (keyStore == null) {
	    // Não conseguiu inicializar a KeyStore. Mostra tela de login com usuário e senha.
	    this.keystoreStatus = DigitalCertificate.KEYSTORE_NOT_DETECTED;
	    //} else if (keyStore.getType().equalsIgnoreCase("pkcs11")){
	} else {
	    // Usa certificado digital.
	    try {
		// Testa se uma keystore já foi carregada previamente
		if (keyStore.getType().equalsIgnoreCase("pkcs11")) {
		    keyStore.load(null, null);
		} else {
		    keyStore.load(pkcs12Input, null);
		}

		// Se chegou aqui KeyStore está liberada, mostrar tela de login sem pedir o pin.
		this.keystoreStatus = DigitalCertificate.KEYSTORE_ALREADY_LOADED;

	    } catch (ProviderException e) {
		// Algum erro ocorreu, mostra  tela de login com usuário e senha.
		this.keystoreStatus = DigitalCertificate.KEYSTORE_NOT_DETECTED;
		if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
		    e.printStackTrace();
		}
	    } catch (IOException e) {
		// KeyStore não está liberada, mostra tela de login com o pin.
		if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
		    System.out.println(e.getMessage());
		}
		this.keystoreStatus = DigitalCertificate.KEYSTORE_DETECTED;
	    } catch (GeneralSecurityException e) {
		if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
		    e.printStackTrace();
		}
	    }
	}

	return keystoreStatus;

    }

    /**
     * Usado para assinar digitalmente um e-mail.
     * @param mime
     * @return String vazia
     */
    public String signMail(Map<String, String> data) throws IOException, GeneralSecurityException, SMIMEException, MessagingException {

	Key privateKey = null;
	if (this.keystoreStatus == DigitalCertificate.KEYSTORE_DETECTED) {
	    String pin = DialogBuilder.showPinDialog(this.parentFrame, this.setup);
	    if (pin != null) {
		openKeyStore(pin.toCharArray());
                if (this.selectedCertificateAlias == null){
                    return null;
                }
                privateKey = this.keyStore.getKey(this.selectedCertificateAlias, pin.toCharArray());
	    } else {
		return null;
	    }
	} /*
	else if (this.keystoreStatus == DigitalCertificate.KEYSTORE_ALREADY_LOADED){
	if (DialogBuilder.showPinNotNeededDialog(this.parentFrame)){
	openKeyStore(null);
	privateKey = this.keyStore.getKey(keyStore.aliases().nextElement(), null);
	}
	else {
	return null;
	}
	}
	 */ else {

	    //DialogBuilder.showMessageDialog(this.parentFrame, "Nenhum token/smartcard foi detectado.\nOperação não pôde ser realizada!");
	    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMessages", "DigitalCertificate001"), this.setup);
	    return null;
	}

	Security.addProvider(new org.bouncycastle.jce.provider.BouncyCastleProvider());

	Certificate certificate = getCert();

	KeyPair keypair = new KeyPair(certificate.getPublicKey(), (PrivateKey) privateKey);

	// Cria a cadeia de certificados que a gente vai enviar
	List certList = new ArrayList();

	certList.add(certificate);

	//
	// create the base for our message
	//
	String fullMsg = data.get("body");

	if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("Corpo do e-mail:\n" + fullMsg + "\n");
	}

	//
	// Get a Session object and create the mail message
	//
	Properties props = System.getProperties();
	Session session = Session.getDefaultInstance(props, null);

	InputStream is = new ByteArrayInputStream(fullMsg.getBytes("iso-8859-1"));
	MimeMessage unsignedMessage = new MimeMessage(session, is);

	//
	// create a CertStore containing the certificates we want carried
	// in the signature
	//
	if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("Provider: " + providerName);
	}
	CertStore certsAndcrls = CertStore.getInstance(
		"Collection",
		new CollectionCertStoreParameters(certList), "BC");

	//
	// create some smime capabilities in case someone wants to respond
	//
	ASN1EncodableVector signedAttrs = new ASN1EncodableVector();

	SMIMECapabilityVector caps = new SMIMECapabilityVector();

	caps.addCapability(SMIMECapability.dES_EDE3_CBC);
	caps.addCapability(SMIMECapability.rC2_CBC, 128);
	caps.addCapability(SMIMECapability.dES_CBC);

	SMIMESignedGenerator gen = new SMIMESignedGenerator(unsignedMessage.getEncoding());

	//SMIMESignedGenerator gen = new SMIMESignedGenerator();

	gen.addSigner(keypair.getPrivate(), (X509Certificate) certificate, SMIMESignedGenerator.DIGEST_SHA1, new AttributeTable(signedAttrs), null);

	gen.addCertificatesAndCRLs(certsAndcrls);

	//TODO: Extrair todos os headers de unsignedMessage

	// Gera a assinatura
	Object content = unsignedMessage.getContent();

	//TODO: igualar unsignedMessage a null
	//TODO: Pegar os headers do objeto que guardarï¿½ esses headers quando necessï¿½rio.

	MimeMultipart mimeMultipartContent = null;
	PreencodedMimeBodyPart mimeBodyPartContent = null;

	if (content.getClass().getName().contains("MimeMultipart")) {
	    mimeMultipartContent = (MimeMultipart) content;
	} else {
	    String encoding = MimeUtility.getEncoding(unsignedMessage.getDataHandler());
	    mimeBodyPartContent = new PreencodedMimeBodyPart(encoding);
	    if (encoding.equalsIgnoreCase("quoted-printable")) {
		ByteArrayOutputStream os = new ByteArrayOutputStream();
		OutputStream encode = MimeUtility.encode(os, encoding);
		OutputStreamWriter writer = new OutputStreamWriter(encode, "iso-8859-1");
		writer.write(content.toString());
		writer.flush();
		mimeBodyPartContent.setText(os.toString(), "iso-8859-1");
		os = null;
		encode = null;
		writer = null;
	    } else {
		mimeBodyPartContent.setText(content.toString(), "iso-8859-1");
	    }
	    mimeBodyPartContent.setHeader("Content-Type", unsignedMessage.getHeader("Content-Type", null));
	}
	content = null;

	//
	// extract the multipart object from the SMIMESigned object.
	//
	MimeMultipart mm = null;
	if (mimeMultipartContent == null) {
	    mm = gen.generate(mimeBodyPartContent, providerName);
	    mimeBodyPartContent = null;
	} else {
	    MimeBodyPart multipartMsg = new MimeBodyPart();
	    multipartMsg.setContent(mimeMultipartContent);
	    mm = gen.generate(multipartMsg, providerName);
	    multipartMsg = null;
	    mimeMultipartContent = null;
	}

	gen = null;

	MimeMessage body = new MimeMessage(session);
	body.setFrom(unsignedMessage.getFrom()[0]);
	body.setRecipients(Message.RecipientType.TO, unsignedMessage.getRecipients(Message.RecipientType.TO));
	body.setRecipients(Message.RecipientType.CC, unsignedMessage.getRecipients(Message.RecipientType.CC));
	body.setRecipients(Message.RecipientType.BCC, unsignedMessage.getRecipients(Message.RecipientType.BCC));
	body.setSubject(unsignedMessage.getSubject(), "iso-8859-1");

	// Atrafuia o resto dos headers
	body.setHeader("Return-Path", unsignedMessage.getHeader("Return-Path", null));
	body.setHeader("Message-ID", unsignedMessage.getHeader("Message-ID", null));
	body.setHeader("X-Priority", unsignedMessage.getHeader("X-Priority", null));
	body.setHeader("X-Mailer", unsignedMessage.getHeader("X-Mailer", null));
        body.setHeader("Importance", unsignedMessage.getHeader("Importance", null));
	body.setHeader("Disposition-Notification-To", unsignedMessage.getHeader("Disposition-Notification-To", null));
	body.setHeader("Date", unsignedMessage.getHeader("Date", null));
	body.setContent(mm, mm.getContentType());
	mm = null;

	if (setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("\nHeaders do e-mail original:\n");
	}

	body.saveChanges();

	ByteArrayOutputStream oStream = new ByteArrayOutputStream();

	oStream = new ByteArrayOutputStream();
	body.writeTo(oStream);

	body = null;
	return oStream.toString("iso-8859-1");

    }

    /**
     * Método utilizado para criptografar um e-mail
     * @param source
     * @return
     */
    public String cipherMail(Map<String, String> data) throws IOException, GeneralSecurityException, MessagingException, CMSException, SMIMEException {

	//Pega certificado do usuário.

	Key privateKey = null;
	if (this.keystoreStatus == DigitalCertificate.KEYSTORE_DETECTED) {
	    String pin = DialogBuilder.showPinDialog(this.parentFrame, this.setup);
	    if (pin != null) {
		openKeyStore(pin.toCharArray());
                if (this.selectedCertificateAlias == null){
                    return null;
                }
                privateKey = this.keyStore.getKey(this.selectedCertificateAlias, pin.toCharArray());
	    } else {
		return null;
	    }
	} /*
	else if (this.keystoreStatus == DigitalCertificate.KEYSTORE_ALREADY_LOADED){
	if (DialogBuilder.showPinNotNeededDialog(this.parentFrame)){
	openKeyStore(null);
	privateKey = this.keyStore.getKey(keyStore.aliases().nextElement(), null);
	}
	else {
	return null;
	}
	}
	 */ else {

	    //DialogBuilder.showMessageDialog(this.parentFrame, "Nenhum token/smartcard foi detectado.\nOperação não pôde ser realizada!");
	    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMessages", "DigitalCertificate001"), this.setup);
	    return null;
	}

	Security.addProvider(new org.bouncycastle.jce.provider.BouncyCastleProvider());

	X509Certificate cert = (X509Certificate) getCert();

	RecipientId recId = new RecipientId();
	recId.setSerialNumber(cert.getSerialNumber());
	recId.setIssuer(cert.getIssuerX500Principal());

	Properties props = System.getProperties();
	Session session = Session.getDefaultInstance(props, null);

	String fullMsg = data.get("body");
	InputStream is = new ByteArrayInputStream(fullMsg.getBytes("iso-8859-1"));
	MimeMessage encriptedMsg = new MimeMessage(session, is);

	Provider prov = Security.getProvider(providerName);
	if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("Serviços do provider " + providerName + ":\n" + prov.getInfo());
	    for (Provider.Service service : prov.getServices()) {
		System.out.println(service.toString() + ": " + service.getAlgorithm());
	    }
	}

	if (setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("Email criptografado:\n" + fullMsg);
	}

	SMIMEEnvelopedParser m = new SMIMEEnvelopedParser(encriptedMsg);
	if (setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("Algoritmo de encriptação: " + m.getEncryptionAlgOID());
	}

	AlgorithmParameters algParams = m.getEncryptionAlgorithmParameters("BC");
	if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("Parâmetros do algoritmo: " + algParams.toString());
	}

	RecipientInformationStore recipients = m.getRecipientInfos();
	RecipientInformation recipient = recipients.get(recId);

	if (recipient != null) {
	    String retorno;

	    MimeBodyPart decriptedBodyPart = SMIMEUtil.toMimeBodyPart(recipient.getContent(privateKey, getProviderName()));

	    if ((new ContentType(decriptedBodyPart.getContentType())).getSubType().equalsIgnoreCase("x-pkcs7-mime")) {
		StringBuffer sb = new StringBuffer(encriptedMsg.getSize());

		for (Enumeration e = encriptedMsg.getAllHeaderLines(); e.hasMoreElements();) {
		    String header = (String) e.nextElement();
		    if (!header.contains("Content-Type") &&
			    !header.contains("Content-Transfer-Encoding") &&
			    !header.contains("Content-Disposition")) {
			sb.append(header);
			sb.append("\r\n");
		    }
		}
		ByteArrayOutputStream oStream = new ByteArrayOutputStream();
		decriptedBodyPart.writeTo(oStream);

                decriptedBodyPart = null;
		encriptedMsg = null;

		sb.append(oStream.toString("iso-8859-1"));

		retorno = sb.toString();

	    } else {
                
		encriptedMsg.setContent(decriptedBodyPart.getContent(), decriptedBodyPart.getContentType());
		encriptedMsg.saveChanges();

		ByteArrayOutputStream oStream = new ByteArrayOutputStream();
		encriptedMsg.writeTo(oStream);
		encriptedMsg = null;

		retorno = oStream.toString("iso-8859-1");
	    }

            // Corrige problemas com e-mails vindos do Outlook
            // Corrige linhas que são terminadas por \n (\x0A) e deveriam ser terminadas por \r\n (\x0D\x0A)
            Pattern p = Pattern.compile("(?<!\\r)\\n");
            Matcher matcher = p.matcher(retorno);
            retorno = matcher.replaceAll(CRLF);

	    return retorno;
	} else {
	    //DialogBuilder.showMessageDialog(this.parentFrame, "Não é possível ler este e-mail com o Certificado Digital apresentado!\n" +
	    //        "Motivo: Este e-mail não foi cifrado com a chave pública deste Certificado Digital.");
	    DialogBuilder.showMessageDialog(this.parentFrame, setup.getLang("ExpressoCertMessages", "DigitalCertificate002"), this.setup);
	    return null;
	}
    }

    /**
     * Pega as credenciais de login do dono do certificado do serviço de verificação de certificados
     * @param	pin			pin para acessar o token
     * @param   where                   URL que será acessada para recuperar as credenciais
     * @return	resposta	Array de Strings em que:
     * 						Indice 0: código de retorno;
     * 						Indice 1: username se código de retorno for 0, senão mensagem de erro;
     * 						Indice 2: senha decriptada se código de retorno for 0, senão não existe;
     * @throws SSLHandshakeException
     * @throws HttpException
     * @throws IOException
     * @throws GeneralSecurityException
     */

    public String[] getCredentials(String pin, URL where) throws SSLHandshakeException, HttpException, IOException, GeneralSecurityException {

	String[] resposta = null;

        if (this.selectedCertificateAlias == null){
            return resposta;
        }

	if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
	    System.out.println("Proxy Configurado no browser: " + System.getProperty("http.proxyHost") + ":" + System.getProperty("http.proxyPort"));
	}

	// Registra novo protocolo https, utilizando nova implementação de AuthSSLProtocolSocketFactory
	Protocol.registerProtocol("https", new Protocol("https",
		(ProtocolSocketFactory) new AuthSSLProtocolSocketFactory(TRUST_STORES_URLS, TRUST_STORES_PASSWORDS, this.setup),
		443));

	HttpClient httpclient = new HttpClient();
	// Define um método post para o link do serviço de verificação de certificados
	if (System.getProperty("http.proxyHost") != null && System.getProperty("http.proxyPort") != null) {
	    httpclient.getHostConfiguration().setProxy(System.getProperty("http.proxyHost"),
		    Integer.parseInt(System.getProperty("http.proxyPort")));
	}

        PostMethod httppost = new PostMethod(where.toExternalForm());
	
	try {
	    // Adiciona parâmetro certificado no método post, executa o método, pega a resposta do servidor
	    // como uma string com CRLF de separador entre os campos e gera um array de Strings
	    httppost.addParameter("certificado", Base64Utils.der2pem(cert.getEncoded()));
	    httpclient.executeMethod(httppost);
	    resposta = httppost.getResponseBodyAsString().split(CRLF);

	    if (resposta.length > 2) {
		if (Integer.parseInt(resposta[0].trim()) == 0) {
		    // Se código da resposta for zero, decripta a senha criptografada do usuário
		    resposta[2] = decriptPassword(resposta[2].trim(), pin);
		}
	    }

	} catch (IOException e) {
	    // Se for instância de SSLHandshakeException faz um cast para este tipo e lança a exceção novamente
	    // Isto é usado para diferenciar o tipo de falha, para que a mensagem para o usuário seja mostrada de
	    // acordo.
	    if (e instanceof SSLHandshakeException) {
		throw (SSLHandshakeException) e;
	    }
	    // senão lança novamente a exceção do tipo IOException
	    throw e;
	} finally {
	    // fecha a conexão
	    httppost.releaseConnection();
	}

	return resposta;
    }

    /**
     * Decripta a senha criptografada
     * @param encodedPassword senha criptografada e codificada em base64 para ser decriptada
     * @param pin pin para acessar a KeyStore
     * @return decodedPassword
     * @throws GeneralSecurityException se algum problema ocorrer na decriptação da senha.
     */
    public String decriptPassword(String encodedPassword, String pin) throws GeneralSecurityException {

	String decodedPassword = new String();

	// Pega a chave privada do primeiro certificado armazenado na KeyStore
	Key privateKey = this.keyStore.getKey(selectedCertificateAlias, pin.toCharArray());

	// Inicializa os cipher com os parâmetros corretos para realizar a decriptação
	Cipher dcipher = Cipher.getInstance("RSA/ECB/PKCS1Padding");
	dcipher.init(Cipher.DECRYPT_MODE, privateKey);

	// Decodifica a senha em base64 e a decripta
	decodedPassword = new String(dcipher.doFinal(Base64Utils.base64Decode(encodedPassword)));

	return decodedPassword.trim();

    }

    /**
     * Carrega um novo SecurityProvider
     * @param pkcs11Config Linha de configuração do SmartCard ou Token
     * @throws KeyStoreException Quando não conseguir iniciar a KeyStore, ou a lib do Token
     * 							 ou Smartcard não foi encontrada, ou o usuário não inseriu o Token.
     */
    private void loadKeyStore() throws GeneralSecurityException {

        try{
            if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
                System.out.println("Carregando provider: PKCS11");
            }
            this.keyStore = KeyStore.getInstance("PKCS11");
            this.providerName = keyStore.getProvider().getName();
        }
        catch (GeneralSecurityException kex){
            if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
                System.out.println("Erro ao carregar provider: PKCS11");
                Throwable cause = kex.getCause();
                kex.printStackTrace();
                if (cause != null){
                    cause.printStackTrace();
                }
            }
            throw kex;
        }
    }

    /**
     *  Abre a keystore passando o pin
     *  @param pin pin para acessar o Token
     */
    public void openKeyStore(char[] pin) throws IOException {
        // TODO:  Verify if object DigitalCertificate was initiated
	try {

	    if (this.keyStore.getType().equals("PKCS11")) {
		this.keyStore.load(null, pin);
	    } else {
		this.keyStore.load(this.pkcs12Input, pin);
	    }

            List<String> aliases = new ArrayList<String>();
            for (Enumeration<String> certificateList = keyStore.aliases(); certificateList.hasMoreElements();){
                aliases.add(certificateList.nextElement());
            }

            // selecionador de certificado
            this.selectedCertificateAlias = DialogBuilder.showCertificateSelector(this.parentFrame, this.setup, aliases);
	    if (this.selectedCertificateAlias != null){
                this.cert = this.keyStore.getCertificate(this.selectedCertificateAlias);
            
                if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
                    System.out.println("Aliases (" + this.keyStore.size() + "): ");
                    for (Enumeration alias = this.keyStore.aliases(); alias.hasMoreElements();) {
                        System.out.println(alias.nextElement());
                    }
                }
            }

	} catch (GeneralSecurityException e) {
	    if (this.setup.getParameter("debug").equalsIgnoreCase("true")) {
		e.printStackTrace();
	    }
	}

    }

    /**
     * @return the cert
     */
    Certificate getCert() {
	return this.cert;
    }

    /**
     * Get a PEM encoded instance of the user certificate
     * @return PEM encoded Certificate
     * @throws CertificateEncodingException
     */
    public String getPEMCertificate() throws CertificateEncodingException {
        if (this.cert != null){
            return Base64Utils.der2pem(this.cert.getEncoded());
        }
        return null;

    }

    /**
     * @param cert the cert to set
     */
    void setCert(Certificate cert) {
	this.cert = cert;
    }
}
