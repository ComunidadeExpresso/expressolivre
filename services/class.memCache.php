<?php
/**
*
* Copyright (C) 2011 Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. 
*
* You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
* 6731, PTI, Bl. 05, Esp. 02, Sl. 10, Foz do Iguaçu - PR - Brasil or at
* e-mail address prognus@prognus.com.br.
*
*
* @package    MemCacheService
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @author     Cristiano Corrêa Schmidt
* @sponsor    Caixa Econômica Federal
* @version    1.0
* @since      2.4.0
*/
class MemCacheService extends Memcache
{
    var $expiration = 86400;

    /**
    * Adiciona objeto ao cache, com tratamento de erros no compress.
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
    * @sponsor    Caixa Econômica Federal
    * @author     Cristiano Corrêa Schmidt
    * @return     bool
    * @access     public
    */
    public function set( $key, $value, $expiration = false)
    {
        return  parent::set($key, 
                            $value,
                            ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) ? false : MEMCACHE_COMPRESSED,
                            ( $expiration !== false ) ? $expiration : $this->expiration );
    }
    
}

ServiceLocator::register( 'memCache', new MemCacheService() );

