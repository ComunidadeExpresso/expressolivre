
# Servico Banco de Dados (Postrgres)

# Comum a todas as distros

# Parametro 1: Destino (completo) de onde ficarao os dados do Postges (data)
# Parametro 2: Nome do arquivo modelo de configuração do Postgres (postgres.conf)
# Parametro 3: Nome do arquivo modelo de configuracao de acessos do Postgres (pg_hba.conf)
# Parametro 4: Destino (completo) de onde ficarao os arquivos de configuracao do Postgres
# Exemplo: bd /var/lib/pgsql/data
bd () {
	DIR_PG=$1
	PG_CONF=$2
	PG_HBA=$3
	DIR_CONF=$4
	get_org
	get_pass
	
	rm -rf $DIR_PG*
	su - postgres -c "env LC_ALL=C initdb --encoding=LATIN1 -D $DIR_PG"
	sed -e "s/LDAP_DN/$LDAP_DN/g" -e "s/LDAP_PWD/$LDAP_PWD/g" -e "s/ou=ORG/ou=$ORG/g" -e "s/DOMAIN/$DOMAIN/g" $ARQS/expresso.dump > /tmp/expresso.dump
	

	cp -f $PG_CONF $DIR_CONF/
	cp -f $PG_HBA $DIR_CONF/
}

# Cria a base de dados e importa os SQLs padroes
create_db ()
{
	su - postgres -c 'dropdb expresso'  || { echo "Banco expresso nao existia. [OK]"; }
	su - postgres -c 'createdb -E LATIN1 expresso'  || { echo "Falha na criacao do banco expresso."; exit 1; }
	su - postgres -c 'psql -f /tmp/expresso.dump expresso'
}

bd_debian ()
{
	DIR_PG="$1"
	PG_CONF="$2"
	PG_HBA="$3"
	DIR_CONF="$4"
	# Instala os pacotes do servico
	apt-get install -y postgresql postgresql-common postgresql-client postgresql-client-common
	/etc/init.d/postgresql stop
	# Gera um link do initdb, para padronizar com o Centos
	ln -sf /usr/lib/postgresql/8.4/bin/initdb /usr/bin/
	# Faz backup da base do Postgres
	mv $DIR_PG $DIR_PG.`date +"%s"`
	mkdir -p $DIR_PG/main

	chown -R postgres:postgres $DIR_PG
	chmod -R 770 $DIR_PG

	bd $DIR_PG $PG_CONF $PG_HBA $DIR_CONF
	rm -f $DIR_PG/postgresql.conf
	rm -f $DIR_PG/pg_hba.conf

	/etc/init.d/postgresql start

	# Inicializa a base de dados do Expresso
	create_db
}

bd_debian_6 ()
{
	DIR_PG="/var/lib/postgresql/8.4/main"
	DIR_CONF="/etc/postgresql/8.4/main"
	SQUEEZE=debian/squeeze/$DIR_CONF
	bd_debian $DIR_PG $SQUEEZE/postgresql.conf $SQUEEZE/pg_hba.conf $DIR_CONF
}

bd_debian_7 ()
{
	DIR_PG="/var/lib/postgresql/9.1/main"
	DIR_CONF="/etc/postgresql/9.1/main"
	WHEEZY=debian/wheezy/$DIR_CONF
	PG_CONF="$WHEEZY/postgresql.conf"
	PG_HBA="$WHEEZY/pg_hba.conf"

	# Instala os pacotes do servico
	apt-get install -y postgresql postgresql-common postgresql-client postgresql-client-common
	/etc/init.d/postgresql stop
	
	# Faz backup da base do Postgres
	rm -rf $DIR_PG
	mkdir -p $DIR_PG

	# Muda as permissoes
	chown -R postgres:postgres $DIR_PG
	chmod -R 700 $DIR_PG

	# Recria a base do Postgres
	su - postgres -c "env LC_ALL=C /usr/lib/postgresql/9.1/bin/initdb --encoding=LATIN1 -D $DIR_PG"
	sed -e "s/LDAP_DN/$LDAP_DN/g" -e "s/LDAP_PWD/$LDAP_PWD/g" -e "s/ou=ORG/ou=$ORG/g" -e "s/DOMAIN/$DOMAIN/g" $ARQS/expresso.dump > /tmp/expresso.dump
	
	cp -f $PG_CONF $DIR_CONF/
	cp -f $PG_HBA $DIR_CONF/
	
	# Cria o link simbolico para os certificados
	ln -sf /etc/ssl/certs/ssl-cert-snakeoil.pem $DIR_PG/server.crt
	ln -sf /etc/ssl/private/ssl-cert-snakeoil.key $DIR_PG/server.key

	/etc/init.d/postgresql start

	# Inicializa a base de dados do Expresso
	create_db
}

bd_ubuntu ()
{
        DIR_PG="$1"
        PG_CONF="$2"
        PG_HBA="$3"
        DIR_CONF="$4"
        # Instala os pacotes do servico
        apt-get install -y postgresql postgresql-common postgresql-client postgresql-client-common
        /etc/init.d/postgresql stop
        # Gera um link do initdb, para padronizar com o Centos
        ln -sf /usr/lib/postgresql/9.1/bin/initdb /usr/bin/
        # Faz backup da base do Postgres
        mv $DIR_PG $DIR_PG.`date +"%s"`

        mkdir -p $DIR_PG/main
        chown -R postgres:postgres $DIR_PG
        chmod -R 770 $DIR_PG

        bd $DIR_PG $PG_CONF $PG_HBA $DIR_CONF
        #rm -f $DIR_PG/{postgresql.conf,pg_hba.conf}
        /etc/init.d/postgresql start

        # Inicializa a base de dados do Expresso
        create_db
}

bd_ubuntu_1204 ()
{
        DIR_PG="/var/lib/postgresql/9.1/main"
        DIR_CONF="/etc/postgresql/9.1/main"
        UBUNTU=ubuntu/12.04/$DIR_CONF
        bd_ubuntu $DIR_PG $UBUNTU/postgresql.conf $UBUNTU/pg_hba.conf $DIR_CONF
}

# Parametros: Iguais a funcao bd;
bd_rhel ()
{
	DIR_PG="$1"
	PG_CONF="$2"
	PG_HBA="$3"
	DIR_CONF="$4"
	# Instala os pacotes do servico
	yum -y install postgresql postgresql-server
	service postgresql stop
	# Faz backup da base do Postgres
	mv $DIR_PG $DIR_PG.`date +"%s"`
	mkdir -p $DIR_PG/data
        chown -R postgres:postgres $DIR_PG

	bd $DIR_PG/data $PG_CONF $PG_HBA $DIR_CONF

	chown -R postgres:postgres $DIR_PG
	chmod 700 $DIR_PG

	service postgresql start
	chkconfig postgresql on

	# Inicializa a base de dados do Expresso
	create_db
}

bd_rhel_6 ()
{
	DIR_PG="/var/lib/pgsql"
	DIR_CONF="$DIR_PG/data"
	RHEL6=rhel/6/var/lib/pgsql/data
	bd_rhel $DIR_PG $RHEL6/postgresql.conf $RHEL6/pg_hba.conf $DIR_CONF
}
