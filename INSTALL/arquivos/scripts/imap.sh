# Servico IMAP (Cyrus-IMAP)

imap () {
	get_org

	cp $ARQS/usr/local/bin/cyradm_expresso /usr/local/bin
	sed -e "s/LDAP_DN/$LDAP_DN/g" $ARQS/saslauthd.conf > /etc/saslauthd.conf
}

create_mbox ()
{
	get_pass
	service slapd stop
	sleep 5
	service slapd start
	./$ARQS/scripts/cyrus.pl $LDAP_PWD
}

imap_debian ()
{
	imap
}

imap_debian_6 ()
{
	apt-get -y install cyrus-admin-2.2 cyrus-clients-2.2 cyrus-common-2.2 cyrus-doc-2.2 cyrus-imapd-2.2 \
			libcyrus-imap-perl22 libsasl2-modules sasl2-bin libmail-imapclient-perl \
			libparse-recdescent-perl libterm-readkey-perl libterm-readline-perl-perl

	SQUEEZE=debian/squeeze/etc
	cp -f $SQUEEZE/imapd.conf /etc/
	cp -f $SQUEEZE/cyrus.conf /etc/
	cp -f $SQUEEZE/default/saslauthd /etc/default/

	imap_debian

	/etc/init.d/saslauthd restart
	# TODO: e necessario colocar para inicializar automaticamente?
	sleep 4
	/etc/init.d/cyrus2.2 restart

	create_mbox
}

imap_ubuntu_1204 ()
{
	apt-get -y install cyrus-admin-2.4 cyrus-clients-2.4 cyrus-common-2.4 cyrus-doc-2.4 cyrus-imapd-2.4 \
			libcyrus-imap-perl24 libsasl2-modules sasl2-bin libmail-imapclient-perl \
			libparse-recdescent-perl libterm-readkey-perl libterm-readline-perl-perl

	UBUNTU=ubuntu/12.04/etc
	cp -f $UBUNTU/imapd.conf /etc/
	cp -f $UBUNTU/cyrus.conf /etc/
	cp -f $UBUNTU/default/saslauthd /etc/default/

	imap_debian

	/etc/init.d/saslauthd restart
	# TODO: e necessario colocar para inicializar automaticamente?
	sleep 4
	/etc/init.d/cyrus-imapd restart

	create_mbox
}

imap_rhel ()
{
	yum -y install cyrus-imapd cyrus-imapd-utils cyrus-sasl cyrus-sasl-ldap cyrus-sasl-plain perl-IO-Socket-SSL	
	
	imap
}

imap_rhel_6 ()
{
	imap_rhel

	RHEL6=rhel/6/etc
	cp $RHEL6/imapd.conf /etc/
	cp $RHEL6/cyrus.conf /etc/
	cp $RHEL6/sysconfig/saslauthd /etc/sysconfig/

	service saslauthd restart
	chkconfig saslauthd on
	sleep 4
	service cyrus-imapd restart
	chkconfig cyrus-imapd on

	create_mbox
}
