
# Incializacao do script, com as especificidades de cada distro

ini_debian ()
{
	# Para caso de erro
	set -e

	# Realiza o update do APT
	apt-get update || { echo "Falha ao atualizar o APT!"; exit 1; }

	# Instala o dialog e debconf-utils
	apt-get -y install debconf-utils openssl ssl-cert vim dialog rsync || { echo "Falha ao instalar alguns pacotes!" ; exit 1; }

	# Verifica se o dialog foi instalado corretamente
	`/usr/bin/which dialog | grep -q "^/usr/bin/dialog$"` || { echo "Dialog não está instalado!!\nPara continuar, por favor, instale o dialog!"; exit 1; }

	# Mudamos o debconf para critical, para que não sejam feitas perguntas desnecessárias.
	debconf-set-selections debian/debconf.critical
}

ini_debian_6 ()
{
	ini_debian
}

ini_debian_7 ()
{
	ini_debian
}

ini_ubuntu_1204 ()
{
	ini_debian_6	
}

ini_rhel ()
{
	LANG=pt_BR.UTF-8
	LC_TELEPHONE=pt_BR.UTF-8
	LC_CTYPE=pt_BR.UTF-8
	LANGUAGE=pt_BR:pt_PT:pt
	LC_MONETARY=pt_BR.UTF-8
	LC_ADDRESS=pt_BR.UTF-8
	LC_COLLATE=pt_BR.UTF-8
	LC_PAPER=pt_BR.UTF-8
	LC_NAME=pt_BR.UTF-8
	LC_NUMERIC=pt_BR.UTF-8
	SYSFONT=lat1-16
	LC_MEASUREMENT=pt_BR.UTF-8
	LC_TIME=pt_BR.UTF-8
	LC_IDENTIFICATION=pt_BR.UTF-8
	LC_MESSAGES=pt_BR.UTF-8

	export LC_TELEPHONE LC_CTYPE LANGUAGE LC_MONETARY LC_ADDRESS LC_COLLATE LC_PAPER LC_NAME LC_NUMERIC SYSFONT LC_MEASUREMENT LC_TIME LANG LC_IDENTIFICATION LC_MESSAGES

	# Desativa o sendmail
	service sendmail stop
	chkconfig --del sendmail
	yum -y erase sendmail
	# Desativa o iptables
	service iptables stop
	chkconfig --del iptables

	# Realiza o update do YUM
	#yum check-update || { echo "Falha ao atualizar o YUM!"; exit 1; }

	yum -y install openssl vim dialog rsync || { echo "Falha ao instalar alguns pacotes!!" ; exit 1; }

	# Verifica se o dialog foi instalado corretamente
	`/usr/bin/which dialog | grep -q "^/usr/bin/dialog$"` || { echo "Dialog não está instalado!!\nPara continuar, por favor, instale o dialog!"; exit 1; }
}

ini_rhel_6 ()
{
	ini_rhel
	# Deixa o SELinux permissivo
	cp -f rhel/6/etc/selinux/config /etc/selinux/config
	setenforce 0
}

