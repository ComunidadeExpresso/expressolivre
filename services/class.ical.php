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
*  You can contact Prognus Software Livre headquarters at Av. Tancredo Neves,
*  6731, PTI, Bl. 05, Esp. 02, Sl. 10, Foz do Iguaçu - PR - Brasil or at
*  e-mail address prognus@prognus.com.br.
*
*
* Serviço ical
*
* Classe responsavel por gerar, e interpretar arquivos ical/vcalendar.
*
* @package    ICalService
* @license    http://www.gnu.org/copyleft/gpl.html GPL
* @author     Consórcio Expresso Livre - 4Linux (www.4linux.com.br) e Prognus Software Livre (www.prognus.com.br)
* @sponsor    Caixa Econômica Federal
* @version    1.0
* @since      2.4.0
*/
class ICalService
{
    private $ical;
    
    function ICalService()
    {
        require_once ( LIBRARY.'/iCalcreator/iCalcreator.class.php' );
    }

     /**
     * Cria um novo Ical
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Prognus Software Livre (http://www.prognus.com.br)
     * @author     Cristiano Corrêa Schmidt
     * @param string $method
     * @access     private
     */
    public function createICal($method)
    {

        $this->ical = new vcalendar();

        /*
         * Seta propiedades obrigatorias para alguns softwares (Outlook)
         */
        $this->ical->setProperty( 'method' , $method );
        $this->ical->setProperty( 'x-wr-calname', 'Calendar Expresso' );
        $this->ical->setProperty( 'X-WR-CALDESC', 'Calendar Expresso' );
        $this->ical->setProperty( 'X-WR-TIMEZONE', date('e') );
    }

     /**
     * Adiciona um novo Evento
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Prognus Software Livre (http://www.prognus.com.br)
     * @author     Cristiano Corrêa Schmidt
     * @param miexed $dtStart 0000-00-00 00:00:00 | array('0000-00-00 00:00:00' => array('XXXX' => 'XXXX'))
     * @param miexed $dtEnd 0000-00-00 00:00:00 | array('0000-00-00 00:00:00' => array('XXXX' => 'XXXX'))
     * @param miexed $organizer array('xxx@xxx.com.br' => array('cn' => 'XXXXXXXX'))
     * @param miexed $summary xxxxx | array('XXXX' =>  array('XXXX' => 'XXXX'))
     * @param miexed $description xxxxx | array( 'XXXX'=> array('XXXX' => 'XXXX'))
     * @param miexed $location xxxxx | array( 'XXXX'=> array('XXXX' => 'XXXX'))
     * @param array $attendee array('xxx@xxx.com.br' => array('cn') => 'XXXX XXXX'))
     * @param array $other array('xxxx' => array('xxx' => array('xxx' =>'xxx'))
     * @param array $components 
     * @param string $uid 
     * @access public
     */
    public function addEvent($dtStart,$dtEnd,$organizer,$summary,$description,$location,$attendee = false,$other = false,$components = false,$uid = false)
    {
       $vevent = &$this->ical->newComponent( 'vevent' );

       $this->setP($vevent, 'dtstart', $dtStart);
       $this->setP($vevent, 'dtend', $dtEnd);
       $this->setP($vevent, 'organizer', $organizer);
       $this->setP($vevent, 'summary', $summary);
       $this->setP($vevent, 'description', $description);
       $this->setP($vevent, 'location', $location);

       if($attendee)
            $this->setP($vevent, 'attendee', $attendee);

       if($other)
       {
           foreach ($other as $property => $value)
               $this->setP($vevent, 'attendee', $value);      
       }

       if($components)
       {
           foreach ($components as $component)
               foreach ($component as $name => $value)
                    $this->setC($vevent, $name, $value);
       }

       if($uid === false)
            $uid = time().'@Expresso';

       $vevent->setProperty( 'uid' , $uid );
    }
    
     /**
     * Seta uma propiedade ao componente
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Prognus Software Livre (http://www.prognus.com.br)
     * @author     Cristiano Corrêa Schmidt
     * @param mixed $component
     * @param string $property Tipo de propiedade
     * @param mixed $value
     * @access     private
     */
    private function setP($component,$property,$value)
    {   
        if(is_array($value))
        {
            foreach ($value as $value2 => $params)
                $component->setProperty( $property , $value2 , $params );
        }
        else
            $component->setProperty( $property , $value );
    }

     /**
     * Seta um componente ao componente
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Prognus Software Livre (http://www.prognus.com.br)
     * @author     Cristiano Corrêa Schmidt
     * @param mixed $component
     * @param string $name Tipo do componente
     * @param mixed $value
     * @access     private
     */
    private function setC($component,$name,$value)
    {
       $comp = & $component->newComponent( $name );
       foreach ($value as $key => $value2)
            $this->setP($comp,$key,$value2);
    }

    /**
     * Importa ical via string ou caminho do arquivo ics
     *
     * @license    http://www.gnu.org/copyleft/gpl.html GPL
     * @author     Prognus Software Livre (http://www.prognus.com.br)
     * @author     Cristiano Corrêa Schmidt
     * @param string $iCal
     * @access     public
     */
    public function setIcal($iCal)
    {
        $this->ical = new vcalendar();
        return $this->ical->parse($iCal);
    }

    /**
    * Retorna todos os componentes do ical parseados
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @return     array
    * @access     public
    */
    public function getComponents()
    {
       
        $return = array();

        $componentes = $this->ical->getConfig('compsinfo');
        foreach ($componentes as $key => $value)
        {
            $return[] = $this->parseComponent($this->ical->getComponent( $key ), $value['type']);
        }
        return $return;
    }

    /**
    * Retorna componente especificado, tipo ou o numero do componente
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @param mixed $component
    * @return     array
    * @access     public
    */
    public function getComponent($component)
    {
        if(is_int($component))
        {
           $componentes = $this->ical->getConfig('compsinfo');
           return $this->parseComponent($this->ical->getComponent($component),$componentes[$component]['type']);
        }
        else
           return $this->parseComponent($this->ical->getComponent($component),$component);
    }

    /**
    * Retorna metodo usado no ical
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @return     string
    * @access     public
    */
    public function getMethod()
    {
        return strtoupper($this->ical->getProperty( 'method' ));
    }

    /**
    * Retorna um array com as informações dos componentes
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @return     array
    * @access     public
    */
    public function getComponentInfo()
    {
        return $this->ical->getConfig('compsinfo');
    }
    

    /**
    * Parseia o componente em um array
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @param  mixed $component
    * @param  string $type
    * @return     array
    * @access     private
    */
    private function parseComponent($component,$type)
    {
        $return = array();

        switch (strtoupper($type)) {
            case 'VEVENT':
                
                $return['type'] = 'VEVENT';
                $return['summary'] = $component->getProperty( 'summary' , FALSE , TRUE );
                $return['description'] = $component->getProperty( 'description' ,FALSE,TRUE);
                $return['organizer'] = $component->getProperty( 'organizer' , FALSE , TRUE );
                $return['organizer']['value'] = str_replace('MAILTO:', '', $return['organizer']['value']);
                $return['location'] = $component->getProperty( 'location', FALSE , TRUE );
                $return['dtstart'] = $component->getProperty( 'dtstart', FALSE , TRUE );
                $return['dtend'] = $component->getProperty( 'dtend', FALSE , TRUE );
                $return['status'] = $component->getProperty( 'status' , FALSE , TRUE );
                $return['class'] = $component->getProperty( 'class' , FALSE , TRUE );
                $return['priority'] = $component->getProperty( 'priority' , FALSE , TRUE );
                $return['uid'] = $component->getProperty( 'uid' , FALSE , TRUE );
                $return['dtstamp'] = $component->getProperty( 'dtstamp' , FALSE , TRUE );
                $return['sequence'] = $component->getProperty( 'sequence' , FALSE , TRUE );
                $return['request-status'] = $component->getProperty( 'request-status' , propOrderNo/FALSE, TRUE );
                $return['rrule'] = $component->getProperty( 'rrule' , propOrderNo/FALSE, TRUE );
                $return['transp'] = $component->getProperty( 'transp' , FALSE , TRUE );
                $return['url'] = $component->getProperty( 'url' , FALSE , TRUE );
                $return['recurrence-id'] = $component->getProperty( 'recurrence-id' , FALSE , TRUE );
                $return['attach'] = $component->getProperty( 'attach' , FALSE , TRUE );
                $return['comment'] = $component->getProperty( 'comment' , FALSE , TRUE );
                $return['created'] = $component->getProperty( 'created' , FALSE , TRUE );
                $return['duration'] = $component->getProperty( 'duration' , FALSE , TRUE );
                $return['geo'] = $component->getProperty( 'geo' , FALSE , TRUE );
                $return['last-modified'] = $component->getProperty( 'last-modified', FALSE , TRUE );
                $return['rdate'] = $component->getProperty( 'rdate' , propOrderNo/FALSE , TRUE );
                $return['related-to'] = $component->getProperty( 'related-to' , propOrderNo/FALSE , TRUE );
                $return['resources'] = $component->getProperty( 'resources' , propOrderNo/FALSE, TRUE );

                while($property = $component->getProperty( FALSE, propOrderNo/FALSE, TRUE )){$return['x-property'][] = array('name' => $property[0], 'value' => $property[1]['value'],'params' => $property[1]['params']);};
               
                while($property = $component->getProperty('attendee',propOrderNo/FALSE , TRUE))
                {
                    $ateendee = $property;
                    $ateendee['value'] = str_replace('MAILTO:', '', $ateendee['value']);
                    $return['attendee'][] = $ateendee;
                };

                while($property = $component->getProperty('categories',propOrderNo/FALSE , TRUE)){$return['categories'][] = $property;};
                while($component->getProperty('contact',propOrderNo/FALSE , TRUE)){$return['contact'][] = $property;};
                while($component->getProperty('exdate',propOrderNo/FALSE , TRUE)){$return['exdate'][] = $property;};
                while($component->getProperty('exrule',propOrderNo/FALSE , TRUE)){$return['exrule'][] = $property;};

                $return['sub'] = array();

                $componentes = $component->getConfig('compsinfo');
                foreach ($componentes as $key => $value)
                    $return['sub'][] = $this->parseComponent($component->getComponent( $key ), $value['type']);
                
                break;

            case 'VALARM':
                $return['type'] = 'VALARM';
                $return['action'] = $component->getProperty( 'action' , FALSE , TRUE );
                $return['attach'] = $component->getProperty( 'attach' , FALSE , TRUE );
                $return['description'] = $component->getProperty( 'description' ,FALSE,TRUE);
                $return['duration'] = $component->getProperty( 'duration' , FALSE , TRUE );
                $return['repeat'] = $component->getProperty( 'repeat', FALSE , TRUE );
                $return['summary'] = $component->getProperty( 'summary' , FALSE , TRUE );
                $return['trigger'] = $component->getProperty( 'trigger' , FALSE , TRUE );
                while($property = $component->getProperty( FALSE, propOrderNo/FALSE, TRUE )){$return['x-property'][] = array('name' => $property[0], 'value' => $property[1]['value'],'params' => $property[1]['params']);};

                break;
            default:
                break;
        }
        

        return $return;
    }

    /**
    * Retorna o ical em uma string
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @return     string
    * @access     prublic
    */
    public function getICal()
    {
       return $this->ical->createCalendar();
    }

    /**
    * Retorna o ical para download direto
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @return     string
    * @access     prublic
    */
    public function downloadICal()
    {
       return $this->ical->returnCalendar();
    }

    /**
    * Salva o ical em disco
    *
    * @license    http://www.gnu.org/copyleft/gpl.html GPL
    * @author     Prognus Software Livre (http://www.prognus.com.br)
    * @author     Cristiano Corrêa Schmidt
    * @param  string $directory
    * @param  string $filename
    * @return     string
    * @access     prublic
    */
    public function saveICal($directory,$filename)
    {
        $config = array( 'directory' => $directory, 'filename' => $filename);
        $this->ical->setConfig( $config );
        $this->ical->saveCalendar();
    }

}

ServiceLocator::register( 'ical', new ICalService() );

?>
