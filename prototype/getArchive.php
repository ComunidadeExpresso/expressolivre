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
 * 6731, PTI, Edifício do Saber, 3º floor, room 306, Foz do Iguaçu - PR - Brasil or at
 * e-mail address prognus@prognus.com.br.
 *
 * Arquivo responsável por extrair do banco de dados informações sobre um 
 * determinado arquivo para que o download possa ser feito.
 *
 * @package    Prototype (Mail)
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 * @version    2.4
 * @sponsor    Caixa Econômica Federal
 * @since      Arquivo disponibilizado na versão 2.4
 */

$data = $_GET;
//TODO implementar suporte a download de varios arquivos!
if( isset($data) )
{
	require_once "api/controller.php";

	foreach($data as $concept => $value){
                              
		$arquive = Controller::find( array( 'concept' => $concept ) , false ,array( 'filter' => array('=', 'id' , $data[$concept]) )); 
				
		foreach($arquive as $key => $arq){		
			header("Content-type: ".$arq['type']);
			header("Expires: 0");
			header("Content-length: ".$arq['size']);
			header("Content-Disposition: attachment; filename=".$arq['name']);
			header("Content-Description: Downlaod de anexos:");
			echo base64_decode($arq['source']);
		}
	}
}else return"false";