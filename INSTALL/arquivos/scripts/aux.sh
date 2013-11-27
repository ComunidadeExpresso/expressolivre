# Funcoes auxiliares de todo o instalador

# Monta um dialog para que o usuario insira a senha.
# Depois grava a informacao em uma variavel global "LDAP_PWD".
get_pass ()
{
	# Se a senha ainda não foi requisitada
	if [ "x$LDAP_PWD" == "x" ]
	then
		LDAP_PWD=$( dialog --backtitle "$BACKTITLE" --stdout --inputbox 'Digite uma senha:' 0 0 )
		LDAP_PWD_CONF=$( dialog --backtitle "$BACKTITLE" --stdout --inputbox 'Confirme a senha:' 0 0 )
		if [ "x$LDAP_PWD" != "x$LDAP_PWD_CONF" ] 
		then
			dialog --backtitle "$BACKTITLE" --title 'As senhas não conferem' \
				--msgbox 'Favor digitar as duas senhas iguais!!!' 6 45
			# Zera a senha e chama novamente a tela de senha
			unset LDAP_PWD
			get_pass
		fi
		# Gera a senha para o arquivo de configuração do LDAP
		LDAP_PWD_MD5=$(perl $ARQS/scripts/md5pass.pl $LDAP_PWD)
	fi
}

# Monta um dialog para que o usuario insira a organizacao e dominio
# Depois grava as informacoes em variaveis globais
get_org ()
{
	if [ "x$ORG" == "x" ] || [ "x$DOMAIN" == "x" ]
	then
		ORG=$( dialog --backtitle "$BACKTITLE" --stdout --inputbox 'Digite uma organização:(ex.: celepar)' 0 55 )
		DOMAIN=$( dialog --backtitle "$BACKTITLE" --stdout --inputbox 'Digite seu domínio:(ex.: '$ORG'.com.br)' 0 55 )
		if [ "x$ORG" == "x" ] || [ "x$DOMAIN" == "x" ]
		then
			dialog --backtitle "$BACKTITLE" --title 'Organização ou domínio inválido!' \
				--msgbox 'Favor preencher a organização E o domínio!!' 6 50
			# Zera as variaveis e chama o dialog novamente
			unset ORG
			unset DOMAIN
			get_org
		fi
		# Substitui o '.' por ',dc='
		LDAP_DN=`echo "dc=$DOMAIN" | sed 's/\./,dc=/g'`
		# Substitui qualquer coisa depois do primerio '.' por 'nada'
		LDAP_DC=`echo $DOMAIN | sed 's/\..*//g'`
		# Substitui qualquer coisa antes do ultimo '.' por 'nada'
		#LDAP_DC=`echo $DOMAIN | sed 's/.*\.//g'`
	fi
}

# Detects which OS and if it is Linux then it will detect which Linux
# Distribution.
# http://linuxmafia.com/faq/Admin/release-files.html
msg_erro ()
{
	echo "Sistema operacional ($1) não suportado!"
	exit 1
}

log_erro ()
{
	echo "$1"
}

qualSO ()
{
	OS=`uname -s`
	REV=`uname -r`
	MACH=`uname -m`

	DIST="Desconhecido"
	PSEUDONAME="Desconhecido"
	REV="Desconhecido"

	if [ "${OS}" = "SunOS" ] ; then
		OS=Solaris
		ARCH=`uname -p`	
		OSSTR="${OS} ${REV}(${ARCH} `uname -v`)"
		msg_erro $OSSTR
	elif [ "${OS}" = "AIX" ] ; then
		OSSTR="${OS} `oslevel` (`oslevel -r`)"
		msg_erro $OSSTR
	elif [ "${OS}" = "Linux" ] ; then
		KERNEL=`uname -r`
		# RedHat ou Centos
		if [ -f /etc/redhat-release ] ; then
			DIST=`cat /etc/redhat-release | cut -d' ' -f1`
			PSUEDONAME=`cat /etc/redhat-release | sed s/.*\(// | sed s/\)//`
			REV=`cat /etc/redhat-release | sed s/.*release\ // | sed s/\ .*//`
		# Debian, Ubuntu e variantes
		elif [ -f /etc/debian_version ] ; then
			# Verifica se o LSB esta instalado
			LSB=`which lsb_release`
			# Se nao estiver, configura no braco
			if [ -z "$LSB" ] ; then
				DIST="Debian"
				REV=`cat /etc/debian_version`
			# Se nao, usa o LSB. Ubuntu eh reconhecido aqui
			else
				DIST=`lsb_release -si`
				PSEUDONAME=`lsb_release -sc`
				REV=`lsb_release -sr`
			fi
		# TODO: Validar do suse
		elif [ -f /etc/SuSE-release ] ; then
			DIST=`cat /etc/SuSE-release | tr "\n" ' '| sed s/VERSION.*//`
			REV=`cat /etc/SuSE-release | tr "\n" ' ' | sed s/.*=\ //`
		fi

		OSSTR="${OS} ${DIST} ${REV} (${PSEUDONAME} ${KERNEL} ${MACH})"
	fi
}

validaSO ()
{
	R=1
	#if [ "$DIST" == "$1" ] && [ `echo $REV | grep -q "$2"` ]
	echo $REV | grep -q "$2"
	if [ $? -eq 0 ] && [ "$DIST" == "$1" ]
	then
		R=0
	fi

	return $R
}
