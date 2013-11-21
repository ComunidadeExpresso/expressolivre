package br.gov.serpro.ui;

import br.gov.serpro.setup.Setup;
import java.awt.BorderLayout;
import java.awt.Color;
import java.awt.Dimension;
import java.awt.FlowLayout;
import java.awt.Frame;
import java.awt.Toolkit;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.event.KeyEvent;
import java.awt.event.KeyListener;
import java.awt.event.WindowEvent;
import java.awt.event.WindowListener;
import java.beans.PropertyChangeEvent;
import java.beans.PropertyChangeListener;
import java.lang.reflect.InvocationTargetException;

import java.util.List;
import javax.swing.BorderFactory;
import javax.swing.JButton;
import javax.swing.JDialog;
import javax.swing.JLabel;
import javax.swing.JList;
import javax.swing.JOptionPane;
import javax.swing.JPanel;
import javax.swing.JPasswordField;
import javax.swing.JScrollPane;
import javax.swing.ListSelectionModel;
import javax.swing.SwingUtilities;
import javax.swing.WindowConstants;

public final class DialogBuilder extends JDialog implements PropertyChangeListener {

    private Setup setup;
    private static Object lock = new Object();
    private String pin = null;
    private String certificateSubject = null;
    //private List<String> certificatelist = new ArrayList<String>();
    private boolean ok = false;
    private boolean locked = true;
    private JOptionPane optionPane;
//	private DialogBuilder myself;
    private EventManager em = new EventManager();
    /**
     * Gerado automaticamente
     */
    private static final long serialVersionUID = 1003857725229120014L;
    private int dialogType = -1;
    //JPanel pNorte, pCentro, pSul;
    //JButton btOk, btCancel;
    //JLabel lTitle, lTitle2, lPin;
    JPasswordField pfPin;
    JList lCertificatesList;
    public static final int PIN_NEEDED_DIALOG = 0;
    public static final int CERTIFICATE_SELECTOR_DIALOG = 1;

    private DialogBuilder(Frame parent, Setup setup) {

        super(parent, true);
        this.setup = setup;
        this.setResizable(false);
//		this.myself = this;

        //this.setVisible(true);

    }

    private void buildPinDialog() {

        this.dialogType = DialogBuilder.PIN_NEEDED_DIALOG;

        this.setContentPane(new JPanel());
        this.setLayout(new BorderLayout());
        this.setTitle(this.setup.getLang("ExpressoCertMessages", "pin"));
        this.setDefaultCloseOperation(WindowConstants.DISPOSE_ON_CLOSE);
        this.setModal(true);

        JPanel pNorte = new JPanel(new FlowLayout(FlowLayout.CENTER));
        this.add(pNorte, BorderLayout.NORTH);
        JPanel pCentro = new JPanel(new FlowLayout(FlowLayout.CENTER));
        this.add(pCentro, BorderLayout.CENTER);
        JPanel pSul = new JPanel(new FlowLayout(FlowLayout.CENTER));
        this.add(pSul, BorderLayout.SOUTH);

        //JLabel lTitle = new JLabel("Entre com o seu PIN:");
        JLabel lTitle = new JLabel(this.setup.getLang("ExpressoCertMessages", "DialogBuilder001"));
        pNorte.add(lTitle);

        JLabel lPin = new JLabel(this.setup.getLang("ExpressoCertMessages", "pin") + ":");
        this.pfPin = new JPasswordField(30);
        this.pfPin.requestFocusInWindow();
        this.pfPin.addKeyListener(this.em);
        pCentro.add(lPin);
        pCentro.add(this.pfPin);

        JButton btOk = new JButton(this.setup.getLang("ExpressoCertMessages", "ok"));
        btOk.setMnemonic(KeyEvent.VK_ENTER);
        btOk.setActionCommand("ok");

        btOk.addActionListener(this.em);

        JButton btCancel = new JButton(this.setup.getLang("ExpressoCertMessages", "cancel"));
        btCancel.setMnemonic(KeyEvent.VK_ESCAPE);
        btCancel.setActionCommand("cancel");
        btCancel.addActionListener(this.em);

        pSul.add(btOk);
        pSul.add(btCancel);

        this.addWindowListener(this.em);
        this.pack();

        //Posicionando no centro da tela.
        Dimension mySize = this.getSize();
        Dimension screenSize = Toolkit.getDefaultToolkit().getScreenSize();
        //System.out.println("ScreenSize: " + screenSize.toString()+"\nMySize: " +mySize.toString());
        this.setLocation(screenSize.width / 2 - (mySize.width / 2), screenSize.height / 2 - (mySize.height / 2));

        setVisible(true);
        //this.repaint();

    }

    private void buildCertificateSelector(List<String> subjectList) {

        dialogType = DialogBuilder.CERTIFICATE_SELECTOR_DIALOG;

        this.setContentPane(new JPanel());
        this.setLayout(new BorderLayout());
        this.setTitle(this.setup.getLang("ExpressoCertMessages", "certificate"));
        this.setDefaultCloseOperation(WindowConstants.DISPOSE_ON_CLOSE);
        this.setModal(true);

        JPanel pNorte = new JPanel(new FlowLayout(FlowLayout.CENTER));
        this.add(pNorte, BorderLayout.NORTH);
        JPanel pCentro = new JPanel(new FlowLayout(FlowLayout.CENTER));
        this.add(pCentro, BorderLayout.CENTER);
        JPanel pSul = new JPanel(new FlowLayout(FlowLayout.CENTER));
        this.add(pSul, BorderLayout.SOUTH);

        JLabel lTitle = new JLabel(this.setup.getLang("ExpressoCertMessages", "DialogBuilder002"));
        pNorte.add(lTitle);

        lCertificatesList = new JList(subjectList.toArray());
        lCertificatesList.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        lCertificatesList.setLayoutOrientation(JList.HORIZONTAL_WRAP);
        lCertificatesList.setBorder(BorderFactory.createLoweredBevelBorder());
        JScrollPane listScroller = new JScrollPane(lCertificatesList);
        listScroller.setPreferredSize(new Dimension(500, 80));
        listScroller.setAlignmentX(LEFT_ALIGNMENT);

        pCentro.add(listScroller);
        //pCentro.setBorder(BorderFactory.createLineBorder(Color.BLACK, 2));

        JButton bSelect = new JButton(this.setup.getLang("ExpressoCertMessages", "select"));
        bSelect.setMnemonic(KeyEvent.VK_ENTER);
        bSelect.setActionCommand("select");
        bSelect.addActionListener(em);
        pSul.add(bSelect);

        JButton btCancel = new JButton(this.setup.getLang("ExpressoCertMessages", "cancel"));
        btCancel.setMnemonic(KeyEvent.VK_ESCAPE);
        btCancel.setActionCommand("cancel");
        btCancel.addActionListener(this.em);
        pSul.add(btCancel);

        this.addWindowListener(em);
        this.pack();

        //Posicionando no centro da tela.
        Dimension mySize = this.getSize();
        Dimension screenSize = Toolkit.getDefaultToolkit().getScreenSize();
        //System.out.println("ScreenSize: " + screenSize.toString()+"\nMySize: " +mySize.toString());
        this.setLocation(screenSize.width / 2 - (mySize.width / 2), screenSize.height / 2 - (mySize.height / 2));

        this.setVisible(true);

    }

    // TODO: Lançar e implementar exceção ActionCanceled
    private void cancelButtonActionPerformed() {
        this.setVisible(false);
        this.pin = null; // hack para saber que diálogo foi cancelado.
        this.certificateSubject = null;
        this.unlock();
        //this.dispose();
    }

    private void okButtonActionPerformed() {
        this.setVisible(false);
        this.unlock();
        //this.dispose();
    }

    protected String getPin() {
        return this.pin;
    }

    protected void setPin(String pin) {
        this.pin = pin;
    }

    protected String getCertificateSubject() {
        return certificateSubject;
    }

    protected void setCertificateSubject(String certificateSubject) {
        this.certificateSubject = certificateSubject;
    }

    protected boolean isLocked() {
        synchronized (DialogBuilder.lock) {
            return this.locked;
        }
    }

    private void setLocked(boolean locked) {
        synchronized (DialogBuilder.lock) {
            this.locked = locked;
        }

    }

    private void unlock() {
        synchronized (DialogBuilder.lock) {
            setLocked(false);
            DialogBuilder.lock.notifyAll();
        }
    }

    static public int showMessageDialog(Frame parent, Object message, Setup setup) {

        return DialogBuilder.showDialog(parent, message, JOptionPane.INFORMATION_MESSAGE, JOptionPane.DEFAULT_OPTION, setup);

    }

    static public int showMessageDialog(Frame parent, Object message, int messageType, Setup setup) {

        return DialogBuilder.showDialog(parent, message, messageType, JOptionPane.DEFAULT_OPTION, setup);

    }

    static public int showConfirmDialog(Frame parent, Object message, int messageType, int optionType, Setup setup) {

        return DialogBuilder.showDialog(parent, message, messageType, optionType, setup);

    }

    static public int showDialog(Frame parent, Object message, int messageType, int optionType, Setup setup) {

        DialogBuilder dialog = new DialogBuilder(parent, setup);
        int valor = dialog.buildDialog(message, messageType, optionType);

        dialog.dispose();

        return valor;

    }

    private int buildDialog(Object message, int messageType, int optionType) {

        this.optionPane = new JOptionPane(message, messageType, optionType);
        this.setModal(true);
        this.setContentPane(this.optionPane);
        this.optionPane.addPropertyChangeListener(this);
        this.pack();

        //Posicionando no centro da tela.
        Dimension mySize = this.getSize();
        Dimension screenSize = Toolkit.getDefaultToolkit().getScreenSize();
        this.setLocation(screenSize.width / 2 - (mySize.width / 2), screenSize.height / 2 - (mySize.height / 2));
        this.setVisible(true);

        Object selectedValue = this.optionPane.getValue();
        int resultado = JOptionPane.CLOSED_OPTION;
        if (selectedValue != null) {
            resultado = JOptionPane.CLOSED_OPTION;
        } else if (selectedValue instanceof Integer) {
            resultado = ((Integer) selectedValue).intValue();
        }

        //this.dispose();

        return resultado;

    }

    public void propertyChange(PropertyChangeEvent evt) {
        // TODO Stub de método gerado automaticamente
        String property = evt.getPropertyName();

        if (this.isVisible() &&
                evt.getSource() == optionPane &&
                JOptionPane.VALUE_PROPERTY.equals(property)) {

            this.setVisible(false);
        }

    }

    static public String showPinDialog(Frame parent, Setup setup) {
        DialogBuilder pinCodeDialog = new DialogBuilder(parent, setup);

        try {
            SwingUtilities.invokeAndWait(pinCodeDialog.new PinCodeNeededBuilder());
        } catch (InterruptedException e1) {
            // TODO Bloco catch gerado automaticamente
            e1.printStackTrace();
        } catch (InvocationTargetException e1) {
            // TODO Bloco catch gerado automaticamente
            e1.printStackTrace();
        }

        synchronized (DialogBuilder.lock) {

            while (pinCodeDialog.isLocked()) {
                try {
                    DialogBuilder.lock.wait();
                } catch (InterruptedException e) {
                    // TODO Bloco catch gerado automaticamente
                }
            }

            String pin = pinCodeDialog.getPin();
            pinCodeDialog.dispose();
            pinCodeDialog = null;

            return pin;
        }
    }

    static public String showCertificateSelector(Frame parent, Setup setup, List<String> certificateList) {
        DialogBuilder certificateSelectorDialog = new DialogBuilder(parent, setup);

        try {
            SwingUtilities.invokeAndWait(certificateSelectorDialog.new CertificateSelectorBuilder(certificateList));
        } catch (InterruptedException e1) {
            // TODO Bloco catch gerado automaticamente
            e1.printStackTrace();
        } catch (InvocationTargetException e1) {
            // TODO Bloco catch gerado automaticamente
            e1.printStackTrace();
        }

        synchronized (DialogBuilder.lock) {

            while (certificateSelectorDialog.isLocked()) {
                try {
                    DialogBuilder.lock.wait();
                } catch (InterruptedException e) {
                    // TODO Bloco catch gerado automaticamente
                }
            }

            String subject = certificateSelectorDialog.getCertificateSubject();
            certificateSelectorDialog.dispose();
            certificateSelectorDialog = null;

            return subject;
        }
    }

    synchronized private boolean isOk() {
        return ok;
    }

    synchronized private void setOk(boolean ok) {
        this.ok = ok;
    }

    private class PinCodeNeededBuilder implements Runnable {

        public void run() {
            buildPinDialog();
        }
    }

    private class CertificateSelectorBuilder implements Runnable{

        private List<String> certificateList;

        public CertificateSelectorBuilder(List<String> certificateList){
            this.certificateList = certificateList;
        }

        public void run() {
            buildCertificateSelector(this.certificateList);
        }
    }

    private final class EventManager implements ActionListener, KeyListener, WindowListener {

        public void keyPressed(KeyEvent keyEvt) {
            int keyPressed = keyEvt.getKeyCode();

            if (keyPressed == KeyEvent.VK_ENTER) {
                //System.out.println("Tecla ENTER pressionada");
                if (dialogType == CERTIFICATE_SELECTOR_DIALOG) {
                    this.actionPerformed(new ActionEvent(keyEvt.getSource(), keyEvt.getID(), "select"));
                } else {
                    this.actionPerformed(new ActionEvent(keyEvt.getSource(), keyEvt.getID(), "ok"));
                }
            } else if (keyPressed == KeyEvent.VK_ESCAPE) {
                //System.out.println("Tecla ESC pressionada");
                this.actionPerformed(new ActionEvent(keyEvt.getSource(), keyEvt.getID(), "cancel"));
            }
        }

        public void keyReleased(KeyEvent arg0) {
        }

        public void keyTyped(KeyEvent keyEvt) {
        }

        public void windowActivated(WindowEvent arg0) {
        }

        public void windowClosed(WindowEvent arg0) {
            //System.out.println("Window Closed: Fechando diálogo!");
        }

        public void windowClosing(WindowEvent arg0) {
            //System.out.println("Window Closing: Fechando diálogo!");
            cancelButtonActionPerformed();
        }

        public void windowDeactivated(WindowEvent arg0) {
        }

        public void windowDeiconified(WindowEvent arg0) {
        }

        public void windowIconified(WindowEvent arg0) {
        }

        public void windowOpened(WindowEvent arg0) {
        }

        public void actionPerformed(ActionEvent evt) {

            String command = evt.getActionCommand();

            // Atribui o valor digitado para o campo
            // senha e esconde a interface e assina o certificado
            if (command.equals("ok")) {

                //if (pfPin != null && pfPin.getPassword().length <= 0){
                // Mostra um aviso de que o campo PIN não foi preenchido
                //JOptionPane.showMessageDialog(null, "Campo PIN não foi preenchido!",
                //		"Aviso", JOptionPane.INFORMATION_MESSAGE);
                //}
                //else {
                setPin(String.valueOf(pfPin.getPassword()));
                okButtonActionPerformed();
                //}

            } else if (command.equals("select")){
                setCertificateSubject((String) lCertificatesList.getSelectedValue());
                okButtonActionPerformed();
            }
            else if (command.equals("cancel")) {
                cancelButtonActionPerformed();
            } else if (command.equals("prosseguir")) {
                setOk(true);
                okButtonActionPerformed();
            }
        }
    }
}
