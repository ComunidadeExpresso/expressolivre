
# Servico SMTP (Postfix)

# Parametro 1: Diretorio onde estao os arquivos de configuracao (modelo)
smtp () {
	get_org
	
	DIR_POSTFIX="$1"
	sed -e "s/LDAP_DN/$LDAP_DN/g" -e "s/DOMAIN/$DOMAIN/g" $DIR_POSTFIX/main.cf > /etc/postfix/main.cf
	sed -e "s/DOMAIN/$DOMAIN/g" $DIR_POSTFIX/expresso-dominios > /etc/postfix/expresso-dominios
	sed -e "s/DOMAIN/$DOMAIN/g" $DIR_POSTFIX/transport > /etc/postfix/transport
	cp $DIR_POSTFIX/master.cf /etc/postfix/
	sed -e "s/LDAP_DN/$LDAP_DN/g" $ARQS/scl.pl > /etc/postfix/scl.pl
    chmod a+x /etc/postfix/scl.pl

	postmap /etc/postfix/expresso-dominios
	postmap /etc/postfix/transport
	# Adiciona o grupo mail ao usuarios postfix
	usermod -a -G mail postfix
}

smtp_rhel ()
{
	yum -y install postfix perl-LDAP

	smtp $1 

	service postfix restart
	chkconfig postfix on
}

smtp_rhel_6 ()
{
	RHEL6="rhel/6/etc/postfix"
	smtp_rhel $RHEL6
}

smtp_debian ()
{
	apt-get -y install postfix postfix-ldap libnet-ldap-perl

	smtp $1

	/etc/init.d/postfix restart
}

smtp_debian_6 ()
{
	SQUEEZE="debian/squeeze/etc/postfix"
	smtp_debian $SQUEEZE
}

smtp_debian_7 ()
{
	WHEEZY="debian/wheezy/etc/postfix"
	smtp_debian $WHEEZY
}

smtp_ubuntu_1204 ()
{
	smtp_debian_6
}

