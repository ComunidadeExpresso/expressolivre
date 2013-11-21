 <?php

//Definindo Constantes
require_once ROOTPATH . '/modules/catalog/constants.php';

use prototype\api\Config as Config;

    /**
     * Métodos que são chamados conforme definições no arquivo contact.ini & contactGroup.ini
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
     * @sponsor    Caixa Econômica Federal
     * @author     José Vicente Tezza Jr. e Gustavo Pereira dos Santos Stabelini
     * @return     Métodos que são chamados conforme definições no arquivo contact.ini & contactGroup.ini
     * @access     public
     * */
	 
class CatalogDBMapping {


    public function findConnections(&$uri, &$params, &$criteria, $original) {

		if(count($params)>0 && isset($params[0]['id'])){
            $params_count = count($params);
			for($i=0; $i < $params_count; ++$i){
				//Retorna o telefone e o e-mail padrao de um determinado contato
				$sql = ' SELECT phpgw_cc_contact_conns.id_typeof_contact_connection as type, phpgw_cc_connections.connection_value as value '
					.'FROM phpgw_cc_contact_conns '
					.'JOIN phpgw_cc_connections '
					.'ON (phpgw_cc_connections.id_connection = phpgw_cc_contact_conns.id_connection) '
					.'WHERE phpgw_cc_contact_conns.id_contact = ' . $params[$i]['id'] . ' AND '
					.'phpgw_cc_connections.connection_is_default = TRUE ';

				$array = Controller::service('PostgreSQL')->execResultSql($sql);
				if(count($array)>0){
					foreach($array as $connection){
						switch($connection['type']){
							case TYPE_EMAIL 	: $params[$i][INDEX_EMAIL] 	= $connection['value']; break;
							case TYPE_TELEPHONE 	: $params[$i][INDEX_TELEPHONE] 	= $connection['value']; break;
							default			: $params[$i][INDEX_EMAIL] = $params[$i][INDEX_TELEPHONE] = '';
						}

					}
				}
				else{
					$params[$i][INDEX_EMAIL] = $params[$i][INDEX_TELEPHONE] = '';
				}
			}
		}
    }

	public function findGroupConnections(&$uri, &$params, &$criteria, $original) {

		if(count($params)>0 && isset($params[0]['id'])){
		$z = 0;
		$count = count($params);
		for($i=0; $i < $count; ++$i){
				//Retorna o telefone e o e-mail padrao de um determinado contato
				$sql = 'SELECT contato.names_ordered as name, contato.id_contact as id, conexao.connection_value as value '
					.'FROM phpgw_cc_groups grupo '
					.'JOIN phpgw_cc_contact_grps grupo_contato '
					.'ON (grupo.id_group = grupo_contato.id_group) '
					.'JOIN phpgw_cc_connections conexao '
					.'ON (grupo_contato.id_connection = conexao.id_connection) '
					.'JOIN phpgw_cc_contact_conns conexaoCon '
					.'ON (conexao.id_connection = conexaoCon.id_connection) '
					.'JOIN phpgw_cc_contact contato '
					.'ON (contato.id_contact = conexaoCon.id_contact) '
					.'WHERE grupo.id_group = ' . $params[$i]['id'] . ' AND '
					.'conexao.connection_is_default = TRUE';

				$array = Controller::service('PostgreSQL')->execResultSql($sql);

				if(count($array)>0){
					$params[$i]['contacts'][$z] = array();
					foreach($array as $connection){
						$params[$i]['contacts'][$z]['id'] = $connection['id'];		
						$params[$i]['contacts'][$z]['name'] = $connection['name'];							
						$params[$i]['contacts'][$z][INDEX_EMAIL] = $connection['value'];							
						++$z;
					}
				}
				else{					
					$params[$i]['contacts'] = null;
				}
			}
		}
    }
	
}

?>
