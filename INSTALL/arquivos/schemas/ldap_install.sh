#!/bin/bash

# ref.: http://www.zytrax.com/books/ldap/ch6/slapd-config.html

# Default installation, only asks for a password
apt-get install slapd
# > Senha do administrador:
# > Confirme a senha:

# Complete configuration of ldap (recommended)
dpkg-reconfigure slapd || exit
# > Omitir a configuração do servidor OpenLDAP? <Não>
# > Nome do domínio DNS: expressolab.celepar.parana
# > Nome da organização: expressolab.celepar.parana
# > Senha do administrador: senha
# > Confirme a senha: senha
# > "Backend" de base de dados a ser usado: HDB
# > Você deseja que a base de dados seja removida quando o pacote slapd for expurgado ("purge")? <Sim>
# > Mover a base de dados antiga? <Não>
# > Permitir o protocolo LDAPv2? <Sim>

# Creating schemas in ldif format
LDAPDIR="/etc/ldap"
SYSDIR="${LDAPDIR}/schema"
CFGDIR="${LDAPDIR}/slapd.d"
TMPDIR="/tmp/ldap-schema"
OUTDIR="${TMPDIR}/slapd.d"
EXPRESSO_PATH="."
DEPs="core cosine nis inetorgperson"
SCHEMAs="samba radius qmailuser expressolivre phpgwaccount phpgwcontact phpgwquotacontrolled"
BACKEND=$(ldapsearch -LLL -Y EXTERNAL -H ldapi:/// -b "cn=config" "(objectClass=olcBackendConfig)" "olcBackend" 2> /dev/null | sed -n "/^olcBackend:/{s#olcBackend: {[0-9]*}##g;p}")
ROOTPW=$(find ${CFGDIR} -name "olcDatabase*${BACKEND}.ldif" -exec grep olcRootPW {} \;)

# Create a temporary directory
mkdir -p ${OUTDIR} || exit

# Copy schemas expressed for temporary
cp ${EXPRESSO_PATH}/*.schema ${TMPDIR} || exit

# Standardize permissions
chmod 644 ${TMPDIR}/*.schema || exit

# Adds dependencies of schemas in the configuration file
for SCHEMA in ${DEPs}; do echo "include ${SYSDIR}/${SCHEMA}.schema"; done > ${TMPDIR}/test.conf || exit

# Adds schemas in the configuration file
for SCHEMA in ${SCHEMAs}; do echo "include ${TMPDIR}/${SCHEMA}.schema"; done >> ${TMPDIR}/test.conf || exit

# Run the program that generates the ldif from the schemes
slaptest -f ${TMPDIR}/test.conf -F ${OUTDIR} || exit

# Standardize the ldif files created previously
for SCHEMA in ${SCHEMAs}; do
	find ${OUTDIR} -name "*${SCHEMA}.ldif" -exec sed '/structuralObjectClass/,$d;s#^dn: cn={[0-9]*}\(.*\)#dn: cn=\1,cn=schema,cn=config#g;s#^cn: {[0-9]*}#cn: #g' {} \; > ${TMPDIR}/${SCHEMA}.ldif || exit
done

# Copy and ldifs schemas to the system directory
cp ${TMPDIR}/*.{ldif,schema} ${SYSDIR}

# Dependence of schemas
# | inetorgperson.schema <- core.schema, cosine.schema
# | samba.schema <- core.schema, cosine.schema, inetorgperson.schema
# | radius.schema <-
# | qmailuser.schema <- core.schema, cosine.schema, nis.schema
# | expressolivre.schema <-
# | phpgwaccount.schema <- core.schema, cosine.schema, nis.schema, qmailuser.schema
# | phpgwcontact.schema <- core.schema, cosine.schema, inetorgperson.schema
# | phpgwquotacontrolled.schema <-

# Loads schemas in cn = config
for SCHEMA in ${SCHEMAs}; do
	ldapadd -Y EXTERNAL -H ldapi:/// -f ${SYSDIR}/${SCHEMA}.ldif;
done

# Loads other settings in cn=config
ldapmodify -Y EXTERNAL -H ldapi:/// <<EOF
dn: cn=config
changetype: modify
replace: olcAllows
olcAllows: bind_v2
-
replace: olcLogLevel
olcLogLevel: 256
-
replace: olcSizeLimit
olcSizeLimit: -1

dn: olcDatabase={-1}frontend,cn=config
changetype: modify
delete: olcSizeLimit

dn: olcDatabase={0}config,cn=config
changetype: modify
replace: olcRootPW
${ROOTPW}

dn: olcDatabase={1}${BACKEND},cn=config
changetype: modify
replace: olcDbCheckpoint
olcDbCheckpoint: 1024 10
-
replace: olcDbIndex
olcDbIndex: default sub
olcDbIndex: entryCSN,accountStatus eq
olcDbIndex: objectClass,uidNumber,gidNumber,entryUUID eq,pres
olcDbIndex: sambaSID,sambaPrimaryGroupSID,sambaAcctFlags eq,pres
olcDbIndex: sambaDomainName,sambaSIDList,sambaGroupType eq,pres
olcDbIndex: cn,displayName eq,pres,subany,approx
olcDbIndex: sn,ou,givenName,uid,employeeNumber eq,pres,subany
olcDbIndex: memberUid,mail,mailAlternateAddress,mailForwardingAddress eq,pres,subany
olcDbIndex: phpgwContactOwner,phpgwAccountType,phpgwAccountStatus eq,pres
olcDbIndex: uniqueMember pres
-
EOF















