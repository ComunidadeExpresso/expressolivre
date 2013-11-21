#!/bin/bash

# Script utilizado para gerar o dump da base de dados padrao do Expresso.
# Este dump e utilizado na instalacao do Expresso via script.

DUMP=expresso.dump

# Variaveis que devem ser substituidas
DOMAIN="prognus.com.br"
LDAP_DN="dc=prognus,dc=com,dc=br"
ORG="ou=prognus"
LDAP_PWD="123pass"
LDAP_DC="dc=br"

# Remove as informacoes temporarias do banco
psql -U postgres expresso <<EOF
DELETE FROM phpgw_access_log *;
DELETE FROM phpgw_expressoadmin_log *;
DELETE FROM phpgw_history_log *;
DELETE FROM phpgw_log *;
DELETE FROM phpgw_log_msg *;
EOF

# Gera o dump da base do Expresso
TDUMP="/tmp/$DUMP"
pg_dump -U postgres -f $TDUMP expresso

sed -e "s/$LDAP_DN/LDAP_DN/g" \
	-e "s/$ORG/ou=ORG/g" \
	-e "s/$DOMAIN/DOMAIN/g"	\
	-e "s/$LDAP_PWD/LDAP_PWD/g" $TDUMP > $DUMP

