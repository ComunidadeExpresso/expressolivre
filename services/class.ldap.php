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
 * @package    LdapService
 * @license    http://www.gnu.org/copyleft/gpl.html GPL
 * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
 * @sponsor    Caixa Econômica Federal
 * @version    1.0
 * @since      2.4.0
 */
class LdapService {

    var $limit = 11;
    var $allTargetTypes = array('i', 'g', 'l', 'u', 's');
    var $connection;

    function LdapService() {
        if (isset($GLOBALS['phpgw_info']['server']['ldap_context']))
            $this->context = $GLOBALS['phpgw_info']['server']['ldap_context'];
    }

    public function setContext($pContext) {
        $this->context = $pContext;
    }

    function connect($host='', $dn='', $passwd='', $ldapreferral=false) {
        if (!$host || $host == $GLOBALS['phpgw_info']['server']['ldap_host']) {
            $dn = $dn ? $dn : $GLOBALS['phpgw_info']['server']['ldap_root_dn'];
            $passwd = $passwd ? $passwd : $GLOBALS['phpgw_info']['server']['ldap_root_pw'];
            $host = $host ? $host : $GLOBALS['phpgw_info']['server']['ldap_host'];
        }

        $conection = ldap_connect($host);

        if (!ldap_set_option($conection, LDAP_OPT_PROTOCOL_VERSION, 3))
            $GLOBALS['phpgw_info']['server']['ldap_version3'] = False;

        ldap_set_option($conection, LDAP_OPT_REFERRALS, $ldapreferral);

        if ($ldapreferral) {
            $GLOBALS['phpgw_info']['server']['user_ldap_referral'] = $dn;
            $GLOBALS['phpgw_info']['server']['password_ldap_referral'] = $passwd;
            ldap_set_rebind_proc($conection, ldap_rebind);
        }

        if ($dn && $passwd && !@ldap_bind($conection, $dn, $passwd))
            @ldap_bind($conection, $dn, $passwd);

        $this->connection = $conection;

        return( $conection );
    }

    function _or($toWrap) {
        return (!is_array($toWrap) && count($toWrap) > 0 ) ? $toWrap : $this->wrap($toWrap, '|');
    }

    function _and($toWrap) {
        return (!is_array($toWrap) && count($toWrap) > 0 ) ? $toWrap : $this->wrap($toWrap, '&');
    }

    function _not($toWrap) {
        return (!is_array($toWrap) && count($toWrap) > 0 ) ? $toWrap : $this->wrap($toWrap, '!');
    }

    function wrap($toWrap, $conditional = "") {
        if (!$toWrap || ( is_array($toWrap) && count($toWrap) < 1))
            return '';

        if (!is_array($toWrap))
            $toWrap = array($toWrap);

        $toWrap = array_unique($toWrap);

        return '(' . $conditional . implode('', $toWrap) . ")";
    }

    function getSearchFilter($search, $targetTypes = false, $customFilter = '', $exact = false) {
        $search = utf8_encode($search);

        if (!$targetTypes)
            $targetTypes = $this->allTargetTypes;

        if (!is_array($targetTypes))
            $targetTypes = array($targetTypes);

        $searchFilter = '';

        foreach ($targetTypes as $targetType) {
            switch ($targetType) {
                case 'g':
                    $searchFilter = $this->stemFilter($search, 'cn');
                    break;

                default :
                    $searchFilter = $this->stemFilter($search, array('cn', 'givenName', 'uid', 'sn', 'displayName', 'mail', 'mailAlternateAddress'));
                    break;
            }
        }

        $filter = array();

        if ($customFilter)
            $filter[] = $customFilter;
        if ($search)
            $filter[] = $searchFilter;

        return $this->_and(array(
                    // Somente objetos relacionados com o Expresso
                    $this->accountFilter($targetTypes),
                    // Objetos ocultados e/ou desativados pelo Administrador nao podem ser exibidos nas consultas do Expresso
                    $this->securityFilter($targetTypes),
                    //foco da busca
                    ( $exact ? $this->_and($filter) : $this->_or($filter) )
                ));
    }

    function securityFilter($targetTypes) {
        if (!$targetTypes)
            $targetTypes = $this->allTargetTypes;

        if (!is_array($targetTypes))
            $targetTypes = array($targetTypes);

        $typeFilter = array();

        foreach ($targetTypes as $targetType) {
            switch ($targetType) {
                case 'g': $typeFilter[] = "(objectClass=posixGroup)";
                    break;

                default : $typeFilter[] = "(phpgwAccountStatus=A)(accountStatus=active)";
                    break;
            }
        }

        return $this->_and(array('(!(phpgwAccountVisible=-1))', $this->_or($typeFilter)));
    }

    function accountFilter($targetTypes) {
        if (!$targetTypes)
            $targetTypes = $this->allTargetTypes;

        if (!is_array($targetTypes))
            $targetTypes = array($targetTypes);

        $typeFilter = array();

        foreach ($targetTypes as $targetType)
            $typeFilter[] = '(phpgwAccountType=' . $targetType . ')';

        return $this->_and(array('(objectClass=phpgwAccount)', $this->_or($typeFilter)));
    }

    function stemFilter($search, $params) {
        $search = str_replace(' ', '*', $search);

        if (!is_array($params))
            $params = array($params);

        foreach ($params as $i => $param)
            $params[$i] = "($param=*$search*)";

        return $this->_or($params);
    }

    function phoneticFilter($search, $params) {
        if (preg_match('/\d/i', $search))
            return( "" );

        if (!is_array($params))
            $params = array($params);

        foreach ($params as $i => $param)
            $params[$i] = "($param~=$search)";

        return $this->_or($params);
    }

    function approxFilter($search, $params) {
        return $this->_or(array($this->stemFilter($search, $params),
                    $this->phoneticFilter($search, $params)));
    }

    public function accountSearch($search, $justthese = "*", $context = false, $accountType = false, $sort = false) {
        if (!$this->connection)
            $this->connect();

        $filter = $this->getSearchFilter($search, $accountType);

        if (!$context)
            $context = $this->context;

        $sr = ldap_search($this->connection, utf8_encode($context), $filter, $justthese, 0, $this->limit);

        if (!$sr)
            return false;

        if ($sort)
            ldap_sort($this->connection, $sr, $sort);

        return $this->formatEntries(ldap_get_entries($this->connection, $sr));
    }

    private function formatEntries($pEntries) {

        if (!$pEntries)
            return( false );

        $return = array();

        for ($i = 0; $i < $pEntries["count"]; ++$i) {
            $entrieTmp = array();
            foreach ($pEntries[$i] as $index => $value) {
                if (!is_numeric($index) && $index != 'count') {
                    if (is_array($value)) {
                        if (count($value) == 2)
                            $entrieTmp[$index] = utf8_decode($value['0']);
                        else {
                            foreach ($value as $index2 => $value2) {
                                if ($index != 'count')
                                    $entrieTmp[$index][$index2] = utf8_decode($value2);
                            }
                        }
                    }
                    else
                        $entrieTmp[$index] = utf8_decode($value);
                }
            }

            $return[] = $entrieTmp;
        }

        return( $return );
    }

    /**
     * Retorna o endereço de e-mail da conta pelo uidNumber 
     * 
     * @license    http://www.gnu.org/copyleft/gpl.html GPL 
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br) 
     * @sponsor    Caixa Econômica Federal 
     * @author     Cristiano Corrêa Schmidt 
     * @param      int $pUidNumber uidNumber da conta 
     * @return     string 
     * @access     public 
     */
    public function getMailByUidNumber($pUidNumber) {
        if (!$this->connection)
            $this->connect();
        $sr = ldap_search($this->connection, $this->context, '(uidNumber=' . $pUidNumber . ')', array('mail'));
        if (!$sr)
            return false;

        $return = ldap_get_entries($this->connection, $sr);
        return $return[0]['mail'][0];
    }

    /**
     * Retorna em um array os endereços de e-mails alternativos da conta pelo uidNumber 
     * 
     * @license    http://www.gnu.org/copyleft/gpl.html GPL 
     * @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br) 
     * @sponsor    Caixa Econômica Federal 
     * @author     Cristiano Corrêa Schmidt 
     * @param      int $pUidNumber uidNumber da conta 
     * @return     Array 
     * @access     public 
     */
    public function getMailAlternateByUidNumber($pUidNumber) {
        if (!$this->connection)
            $this->connect();

        $sr = ldap_search($this->connection, $this->context, '(uidNumber=' . $pUidNumber . ')', array('mailAlternateAddress'));
        if (!$sr)
            return false;

        $returnL = ldap_get_entries($this->connection, $sr);
        $return = array();
        if ( isset($returnL[0]['mailalternateaddress']) )
            foreach ($returnL[0]['mailalternateaddress'] as $i => $v) {
                if ($i === 'count')
                    continue;
                $return[] = $v;
            }
        return $return;
    }

}

ServiceLocator::register('ldap', new LdapService());

