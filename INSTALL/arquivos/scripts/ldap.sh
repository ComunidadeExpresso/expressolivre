# Servico LDAP (OpenLDAP)

# Comum a todas as distros
DB_CONFIG=$ARQS/DB_CONFIG
SCHEMAS=$ARQS/schemas

# Parametro 1: Arquivo modelo de configuração do LDAP
# Parametro 2: Destino (completo) do arquivo de configuração do servidor LDAP
# Parametro 3: Diretório, do instalador, contendo os schemas do servidor LDAP
# Parametro 4: Diretório, do servidor, para onde os schemas do servidor LDAP serao copiados
# Exemplo: ldap $RHEL6/slapd.conf /etc/openldap/slapd.conf $SCHEMAS/* /etc/openldap/schema/
ldap ()
{
	get_org
	get_pass

	# As novas distros necessitam de schemas em UTF-8. Entao estou deixando essa copia para cada distro.
	cp -a $3/* $4/
	
	sed -e "s/LDAP_DN/$LDAP_DN/g" -e "s|LDAP_PWD_MD5|$LDAP_PWD_MD5|g" $1 > $2
	sed -e "s/LDAP_DN/$LDAP_DN/g" -e "s|LDAP_PWD_MD5|$LDAP_PWD_MD5|g" -e "s/ORG/$ORG/g" -e "s/DOMAIN/$DOMAIN/g" -e "s/LDAP_DC/$LDAP_DC/g" $ARQS/expresso.ldif > /tmp/expresso.ldif
	slapadd -v -f $2 -l /tmp/expresso.ldif
	# TODO: E mesmo necessario rodar o slapindex ou o slapadd ja indexa?
	#slapindex
}

# Parametros: Iguais a funcao ldap;
ldap_debian ()
{
	apt-get -y install slapd ldap-utils 
	/etc/init.d/slapd stop
	# Move a atual base LDAP
	# TODO: Executar varias a vezes este instalador ira gerar muitos arquivos de backup! Melhor do que nao ter nenhum...
	mv /var/lib/ldap /var/lib/ldap.`date +"%s"`
	mkdir /var/lib/ldap
	cp -a $DB_CONFIG /var/lib/ldap/

	# Cria o arquivo de configuração do LDAP e sua árvore conforme a distro
	ldap $1 $2 $3 $4
	# Altera as permissões para o usuario padrao do Debian
	chown -R openldap:openldap /var/lib/ldap
	# Apaga os arquivos do slapd.d, para que o LDAP funcione com o 
	# slapd.conf
	rm -rf /etc/ldap/slapd.d
	/etc/init.d/slapd start
}

ldap_debian_6 ()
{
	# BDB do Squeeze
	apt-get -y install db4.8-util

	SQUEEZE=debian/squeeze/etc/ldap
	ldap_debian $SQUEEZE/slapd.conf /etc/ldap/slapd.conf $SCHEMAS /etc/ldap/schema
}

ldap_debian_7 ()
{
	# BDB do Wheezy
	apt-get -y install db5.1-util

	WHEEZY=debian/wheezy/etc/ldap
	ldap_debian $WHEEZY/slapd.conf /etc/ldap/slapd.conf $SCHEMAS /etc/ldap/schema
}

ldap_ubuntu_1204 ()
{
	ldap_debian_6
}

# Parametros: Iguais a funcao ldap;
ldap_rhel ()
{
	yum -y install openldap openldap-clients openldap-servers
	service ldap stop
	# Faz backup das bases do LDAP
	mv /var/lib/ldap /var/lib/ldap.`date +"%s"`
	mkdir /var/lib/ldap
	cp -a $DB_CONFIG /var/lib/ldap/

	# Cria o arquivo de configuração do LDAP e sua árvore conforme a distro
	ldap $1 $2 $3 $4
	chown -R ldap:ldap /var/lib/ldap
	chkconfig slapd on
	rm -rf /etc/openldap/slapd.d
	service slapd start
}

ldap_rhel_6 ()
{
	RHEL6=rhel/6/etc/openldap
	ldap_rhel $RHEL6/slapd.conf /etc/openldap/slapd.conf $SCHEMAS /etc/openldap/schema/
}

