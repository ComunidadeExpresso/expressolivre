
# Especifico para instalacao do PHP de pacotes de terceiros, nao oficiais da distribuicao
pacotes_php () {
	dialog --yesno 'Os pacotes do PHP disponíveis nesta distribuição possuem versões inferiores às requisitadas pelo Expresso. O Expresso necessita do PHP-5.2.1 ou superior.\nPorém, este script poderá configurar automaticamente um repositório externo, de terceiros e sem suporte (neste caso, o REMI-Enterprise), para instalar versões mais recentes do PHP.\n\nA equipe do Expresso Livre recomenda o uso deste repositório e a instalação dos novos pacotes, porém, não nos responsabilizamos por qualquer dano. Caso você não utilize os pacotes mais recentes do PHP, o Expresso não irá funcionar corretamente!!\n\nVocê deseja prosseguir, por sua conta e risco, a instalação do PHP mais recente?' 20 70
	# yes
	if [ $? = 0 ]
	then
		# Utiliza o repositorio do Remi, que possui (em 10/09/09) o php-5.3.0
		# http://blog.famillecollet.com/pages/Config-en
		rpm -Uvh http://download.fedoraproject.com/pub/epel/6/x86_64/epel-release-5-3.noarch.rpm
		wget http://rpms.famillecollet.com/remi-enterprise.repo -O /etc/yum.repos.d/remi-enterprise.repo
		# Atualiza os pacotes...
		yum --enablerepo=remi install php php-cli php-ldap php-pgsql php-imap php-mbstring php-gd libtool
	else
			
		dialog --infobox 'Ok! A versão mais recente do PHP não foi instalada!' 0 0
	fi
}


