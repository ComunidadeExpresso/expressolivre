package br.gov.serpro.setup;

import java.applet.Applet;
import java.util.HashMap;
import java.util.Locale;
import java.util.Map;
import java.util.MissingResourceException;
import java.util.Properties;
import java.util.ResourceBundle;

public class Setup {

    /**
     *
     */
    private static final long serialVersionUID = -8164125429139606589L;
    private Properties parameters;
    private Properties preferences;
    private Map<String, ResourceBundle> langResources;
    Locale currentLocale;
    private Applet currentApplet;
    private static final String PREFERENCES_PATH;
    private static final String EPASS_2000;

    static {

        if (System.getProperty("os.name").equalsIgnoreCase("linux")) {
            EPASS_2000 = "/usr/lib/libepsng_p11.so";
        } else {
            EPASS_2000 = System.getenv("SystemRoot").replaceAll("\\\\", "/") + "/system32/ngp11v211.dll";
        }


        PREFERENCES_PATH = "TESTE";

    }

    public Setup(Applet applet) {

        this.currentApplet = applet;
        parameters = new Properties();
        //preferences = Do arquivo apontado por preferences_path

        // Pega os parâmetros da applet
        for (String[] parameter : getParameterInfo()) {
            String parameterName = parameter[0];
            String parameterValue;

            parameterValue = this.currentApplet.getParameter(parameterName);

            System.out.println("parameter: "+parameterName+" value: "+parameterValue);

            if (parameterValue != null && !parameterValue.equals("")) {
                parameters.setProperty(parameterName.toLowerCase(), parameterValue);
                if (parameterName.equalsIgnoreCase("locale")) {
                    System.out.println("Locale recebido.");
                }
            } else {
                //Defaults
                if (parameterName.equalsIgnoreCase("debug")) {
                    parameters.setProperty(parameterName.toLowerCase(), "false");
                }
                if (parameterName.equalsIgnoreCase("token")) {
                    parameters.setProperty(parameterName.toLowerCase(), "Epass2000;" + EPASS_2000);
                }
                if (parameterName.equalsIgnoreCase("locale")) {
                    System.out.println("Locale não recebido, definindo valor default.");
                    parameters.setProperty(parameterName.toLowerCase(), "pt_BR");
                }
            }
        }

        //TODO: Pegar as preferências do arquivo de preferências se encontrado;

        // Lang Resources
        currentLocale = this.buildLocale(parameters.getProperty("locale"));
        langResources = new HashMap<String, ResourceBundle>(2);
        langResources.put("ExpressoCertMessages", ResourceBundle.getBundle("ExpressoCertMessages", currentLocale));

    }

    public String[][] getParameterInfo() {

        String[][] info = {
            {"debug", "boolean", "Habilita mensagens de debug"},
            {"token", "string", "Lista de tokens suportados. Formato: nome1;caminho1,nome2;caminho2"},
            {"locale", "string", "Locale do sistema"}
        };

        return info;
    }

    public String[][] getPreferencesInfo() {

        String[][] info = {
            {"preferedToken", "string", "Token preferencial do usuário. Formato: nome;caminho"}
        };

        return info;

    }

    public String getParameter(String key) {
        return parameters.getProperty(key);
    }

    public String getPreference(String key) {
        return getPreference(key);
    }

    //TODO: implementar PreferenceNotRegisteredException
    public void setPreference(String key, String value) {

        boolean exists = false;
        while (!exists) {
            for (String[] preference : getPreferencesInfo()) {
                if (key.equalsIgnoreCase(preference[1])) {
                    exists = true;
                    preferences.setProperty(key, value);
                }
            }
        }

        if (!exists) {
//			 throws PreferenceNotRegisteredException();
            System.out.println("Preferência não existe!");
        }
    }

    Locale buildLocale(String localeCode) {

        String[] localeItems = localeCode.split("_");
        Locale locale;

        switch (localeItems.length) {
            case 1:
                locale = new Locale(localeItems[0]);
                break;
            case 2:
                locale = new Locale(localeItems[0], localeItems[1]);
                break;
            case 3:
                locale = new Locale(localeItems[0], localeItems[1], localeItems[2]);
                break;
            default:
                locale = new Locale("pt", "BR");
                System.out.println("Locale code error, setting default locale: " + locale.toString());
        }

        return locale;
    }

    public void addLanguageResource(String langResource) {
        System.out.println("registrando recurso de linguagem " + langResource);
        langResources.put(langResource, ResourceBundle.getBundle(langResource, currentLocale));
    }

    public String getLang(String langResource, String message) {

        ResourceBundle resource = langResources.get(langResource);

        String i18nText = new String("????");
        try {
            i18nText = resource.getString(message);
        } catch (MissingResourceException e) {
            e.printStackTrace();
        }

        return i18nText;

        //return message;
    }

    // TODO: Not Implemented Yet
    public boolean savePreferences() {
        return false;
    }
}
