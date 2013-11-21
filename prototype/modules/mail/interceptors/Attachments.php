<?php
/**
*
* Copyright (C) 2012 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
*
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU Affero General Public License version 3 as published by
* the Free Software Foundation with the addition of the following permission
* added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
* WORK IN WHICH THE COPYRIGHT IS OWNED BY FUNAMBOL, FUNAMBOL DISCLAIMS THE
* WARRANTY OF NON INFRINGEMENT  OF THIRD PARTY RIGHTS.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
* details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program; if not, see www.gnu.org/licenses or write to
* the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301 USA.
*
* This code is based on the OpenXchange Connector and on the Prognus pSync
* Connector both developed by the community and licensed under the GPL
* version 2 or above as published by the Free Software Foundation.
*
* You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
* 6731, PTI, Edifício do Saber, 3º floor, room 306, Foz do Iguaçu - PR - Brasil
* or at e-mail address prognus@prognus.com.br.
*
*
* @package    Prototype (Mail)
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @version    2.4
* @sponsor    Caixa Econômica Federal
* @since      Arquivo disponibilizado na versão 2.4
*/

/**
*
* @package    Prototype (Mail)
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @sponsor    Caixa Econômica Federal
* @version    2.4
* @since      Classe disponibilizada na versão 2.4
*/

use prototype\api\Config as Config;

class Attachments {	
	
	public function encodeCreateAttachment( &$uri , &$params , &$criteria , $original ){            
            if(!isset($params['source'])) return false;
			

            if(isset($_FILES[$params['source']]))
                $params =  $_FILES[$params['source']];
				
				if($params['error'] !== 0){
					switch ($params['error']){
						case 1:
							throw  new Exception('Tamanho de arquivo nao permitido!!! (php.ini)'); 
						case 2:
							throw  new Exception('Tamanho de arquivo nao permitido!!!'); 
						case 3:
							throw  new Exception('Ocorreu um erro durante o upload'); 
						case 4:
							throw  new Exception('Nao e um arquivo valido'); 
					}
				}
				
                $params['owner'] = Config::me('uidNumber'); 

                $params['disposition'] = $original['properties']['disposition'];
                $params['dtstamp'] = time();
	}
        
        public function securityOwner(  &$uri , &$params , &$criteria , $original )
        {
            $criteria['filter'] = isset( $criteria['filter'] ) ? array('AND', $criteria['filter'] , array('=' , 'owner', Config::me('uidNumber')) ) :  array('=' , 'owner', Config::me('uidNumber'));   
        }

}

?>
