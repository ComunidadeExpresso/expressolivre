<?php
include_once(__DIR__.'/../../../lib/default/diffbackend/diffbackend.php');


class ExpressoContactProvider extends BackendDiff
{
    var $db;
    var $_uidnumber;

    /**
     * Returns a list (array) of folders, each entry being an associative array
     * with the same entries as StatFolder(). This method should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * @access protected
     * @return array/boolean        false if the list could not be retrieved
     */
    public function GetFolderList()
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->GetFolderList()");
        return array($this->StatFolder("contacts"));
    }

    /**
     * Returns an actual SyncFolder object with all the properties set. Folders
     * are pretty simple, having only a type, a name, a parent and a server ID.
     *
     * @param string        $id           id of the folder
     *
     * @access public
     * @return object   SyncFolder with information
     */
    public function GetFolder($id)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->GetFolder()");
        if($id == "contacts") {
            $folder = new SyncFolder();
            $folder->serverid = $id;
            $folder->parentid = "0";
            $folder->displayname = "Contatos";
            $folder->type = SYNC_FOLDER_TYPE_CONTACT;

            return $folder;
        }
        else return false;
    }

    /**
     * Returns folder stats. An associative array with properties is expected.
     *
     * @param string        $id             id of the folder
     *
     * @access public
     * @return array
     *          Associative array(
     *              string  "id"            The server ID that will be used to identify the folder. It must be unique, and not too long
     *                                      How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
     *              string  "parent"        The server ID of the parent of the folder. Same restrictions as 'id' apply.
     *              long    "mod"           This is the modification signature. It is any arbitrary string which is constant as long as
     *                                      the folder has not changed. In practice this means that 'mod' can be equal to the folder name
     *                                      as this is the only thing that ever changes in folders. (the type is normally constant)
     *          )
     */
    public function StatFolder($id)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->StatFolder()");

        $folder = $this->GetFolder($id);

        $stat = array();
        $stat["id"] = $id;
        $stat["parent"] = $folder->parentid;
        $stat["mod"] = $folder->displayname;

        return $stat;
    }

    /**
     * Creates or modifies a folder
     *
     * @param string        $folderid       id of the parent folder
     * @param string        $oldid          if empty -> new folder created, else folder is to be renamed
     * @param string        $displayname    new folder name (to be created, or to be renamed to)
     * @param int           $type           folder type
     *
     * @access public
     * @return boolean                      status
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     *
     */
    public function ChangeFolder($folderid, $oldid, $displayname, $type)
    {
        // TODO: Implement ChangeFolder() method.
    }

    /**
     * Deletes a folder
     *
     * @param string $id
     * @param $parentid
     * @internal param string $parent is normally false
     *
     * @access public
     * @return boolean                      status - false if e.g. does not exist
     */
    public function DeleteFolder($id, $parentid)
    {
        // TODO: Implement DeleteFolder() method.
    }

    /**
     * Returns a list (array) of messages, each entry being an associative array
     * with the same entries as StatMessage(). This method should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * The $cutoffdate is a date in the past, representing the date since which items should be shown.
     * This cutoffdate is determined by the user's setting of getting 'Last 3 days' of e-mail, etc. If
     * the cutoffdate is ignored, the user will not be able to select their own cutoffdate, but all
     * will work OK apart from that.
     *
     * @param string $folderid id of the parent folder
     * @param long $cutoffdate timestamp in the past from which on messages should be returned
     *
     * @throws Exception
     * @access public
     * @return array/false                  array with messages or false if folder is not available
     */
    public function GetMessageList($folderid, $cutoffdate)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->GetMessageList()");

        $messages = array();
        $ids = array();

        $result = pg_query($this->db, "select given_names, family_names, last_update, id_contact from phpgw_cc_contact where id_owner = " . $this->_uidnumber . ";");
        if ($result == FALSE) throw new Exception(pg_last_error($this->db));
        while ($row = pg_fetch_row($result)) {
          $message = array();
          $message["id"] = $row[3];
          $message["mod"] = substr($row[2], 0, strlen($row[2])-3);
          $message["flags"] = 1; // always 'read'
          $messages[] = $message;
        }

        return $messages;
    }

    /**
     * Returns the actual SyncXXX object type. The '$folderid' of parent folder can be used.
     * Mixing item types returned is illegal and will be blocked by the engine; ie returning an Email object in a
     * Tasks folder will not do anything. The SyncXXX objects should be filled with as much information as possible,
     * but at least the subject, body, to, from, etc.
     *
     * @param string $folderid id of the parent folder
     * @param string $id id of the message
     * @param ContentParameters $contentparameters parameters of the requested message (truncation, mimesupport etc)
     *
     * @throws Exception
     * @access public
     * @return object/false                 false if the message could not be retrieved
     */
    public function GetMessage($folderid, $id, $contentparameters)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->GetMessage()");

        // Parse the database into object
        $message = new SyncContact();
        $result_contact = pg_query($this->db, "select id_contact, id_owner, id_status, photo, alias, id_prefix, given_names, family_names, names_ordered, id_suffix, birthdate, sex, pgp_key, notes, is_global, last_status, last_update, category, web_page, corporate_name, job_title, department from phpgw_cc_contact where id_contact = " . $id . ";");
            if ($result_contact == FALSE) throw new Exception(pg_last_error($this->db));
            while ($row_contact = pg_fetch_row($result_contact)) {
                if(isset($row_contact[3]) && $row_contact[3] ) {
                    $message->picture = base64_encode(pg_unescape_bytea( $row_contact[3]) );
                }
                if(isset($row_contact[4])) {
                    $message->nickname = utf8_encode($row_contact[4]);
                }
                if(isset($row_contact[7])) {
                    $arrayFamilyName = explode(' ',trim($row_contact[7]));
                    if (sizeof($arrayFamilyName) > 1) {
                        $message->lastname = utf8_encode($arrayFamilyName[sizeof($arrayFamilyName) - 1]);
                        for ($i = 0; $i < (sizeof($arrayFamilyName) - 1); $i++) {
                            $message->middlename .= " " . utf8_encode($arrayFamilyName[$i]);
                        }
                        $message->middlename = trim($message->middlename);
                    } else {
                        $message->lastname = utf8_encode(trim($row_contact[7]));
                    }
                    $message->fileas = $message->lastname;
                }
                if(isset($row_contact[6])) {
                    $message->firstname = utf8_encode(trim($row_contact[6]));
                    if(isset($row_contact[7])) $message->fileas .= ', ' . $message->firstname;
                    else $message->fileas = $message->firstname;
                }
                if(isset($row_contact[10])) {
                    $dataParte = explode('-',$row_contact[10]);
                    $data = $dataParte[2] . '-' . $dataParte[1] . '-' . $dataParte[0];
                    $tz = date_default_timezone_get();
                    date_default_timezone_set('UTC');
                    $message->birthday = strtotime($data);
                    $message->birthday += 3600 * 10; // Soma 10 horas para nao alterar a data quando mudar o Timezone
                    date_default_timezone_set($tz);
                }
                // TODO:Incluir campo de Aniversario na sincronizacao. O BD do Expresso ainda nao tem esse campo :-(
                if(isset($row_contact[13])) {
                    $message->body = utf8_encode(str_replace('\\n', chr(13) . chr(10), $this->escape($row_contact[13])));
                    $message->bodysize = strlen($message->body);
                    $message->bodytruncated = 0;
                }
                //TODO:Tratar o conteudo do campo de categorias
                //if(isset($row_contact[17])) {
                //	$message->categories = utf8_encode($row_contact[17]);
                //}
                if(isset($row_contact[18])) {
                    $message->webpage = utf8_encode($row_contact[18]);
                }
                if(isset($row_contact[19])) {
                    $message->companyname = utf8_encode($row_contact[19]);
                    if (!isset($row_contact[6]) and !isset($row_contact[7])) $message->fileas = $message->companyname;
                }
                if(isset($row_contact[20])) {
                    $message->jobtitle = utf8_encode($row_contact[20]);
                }
                if(isset($row_contact[21])) {
                    $message->department = utf8_encode($row_contact[21]);
                }

                // Endere�o Comercial
                $result_addresses_comercial = pg_query($this->db,"select co.id_address, co.id_city, city_name, co.id_state, state_symbol, co.id_country, address1, address2, complement, address_other, postal_code, po_box, address_is_default from phpgw_cc_addresses co join phpgw_cc_contact_addrs ca on (co.id_address = ca.id_address) join phpgw_cc_typeof_ct_addrs tca on (ca.id_typeof_contact_address = tca.id_typeof_contact_address) left outer join phpgw_cc_city ci on (ci.id_city = co.id_city) left outer join phpgw_cc_state cs on (cs.id_state = co.id_state) where tca.contact_address_type_name = 'Comercial' and ca.id_contact = " . $row_contact[0] . ";");
                if ($result_addresses_comercial == FALSE) throw new Exception(pg_last_error($this->db));
                while ($row_addresses_comercial = pg_fetch_row($result_addresses_comercial)) {
                    if (isset($row_addresses_comercial[2])) {
                        $message->businesscity = utf8_encode($row_addresses_comercial[2]);
                    }
                    if (isset($row_addresses_comercial[5])) {
                        $message->businesscountry = utf8_encode($row_addresses_comercial[5]);
                    }
                    if (isset($row_addresses_comercial[10])) {
                        $message->businesspostalcode = utf8_encode($row_addresses_comercial[10]);
                    }
                    if (isset($row_addresses_comercial[4])) {
                        $message->businessstate = utf8_encode($row_addresses_comercial[4]);
                    }
                    if (isset($row_addresses_comercial[6])) {
                        $message->businessstreet = utf8_encode($row_addresses_comercial[6]);
                    }
                    if (isset($row_addresses_comercial[8])) {
                        if (isset($message->businessstreet)) {
                            $message->businessstreet .= ":";
                        }
                        $message->businessstreet .= utf8_encode($row_addresses_comercial[8]);
                    }
                    if (isset($row_addresses_comercial[7])) {
                        if (isset($message->businessstreet)) {
                            $message->businessstreet .= ":";
                        }
                        $message->businessstreet .= utf8_encode($row_addresses_comercial[7]);
                    }
                    if (isset($row_addresses_comercial[9])) {
                        if (isset($message->businessstreet)) {
                            $message->businessstreet .= ":";
                        }
                        $message->businessstreet .= utf8_encode($row_addresses_comercial[9]);
                    }
                    if (isset($row_addresses_comercial[11])) {
                        if (isset($message->businessstreet)) {
                            $message->businessstreet .= ":";
                        }
                        $message->businessstreet .= utf8_encode($row_addresses_comercial[11]);
                    }
                }
                // Endere�o Residencial
                $result_addresses_residencial = pg_query($this->db,"select co.id_address, co.id_city, city_name, co.id_state, state_name, co.id_country, address1, address2, complement, address_other, postal_code, po_box, address_is_default from phpgw_cc_addresses co join phpgw_cc_contact_addrs ca on (co.id_address = ca.id_address) join phpgw_cc_typeof_ct_addrs tca on (ca.id_typeof_contact_address = tca.id_typeof_contact_address) left outer join phpgw_cc_city ci on (ci.id_city = co.id_city) left outer join phpgw_cc_state cs on (cs.id_state = co.id_state) where tca.contact_address_type_name = 'Residencial' and ca.id_contact = " . $row_contact[0] . ";");
                if ($result_addresses_residencial == FALSE) throw new Exception(pg_last_error($this->db));
                while ($row_addresses_residencial = pg_fetch_row($result_addresses_residencial)) {
                    if (isset($row_addresses_residencial[2])) {
                        $message->homecity = utf8_encode($row_addresses_residencial[2]);
                    }
                    if (isset($row_addresses_residencial[5])) {
                        $message->homecountry = utf8_encode($row_addresses_residencial[5]);
                    }
                    if (isset($row_addresses_residencial[10])) {
                        $message->homepostalcode = utf8_encode($row_addresses_residencial[10]);
                    }
                    if (isset($row_addresses_residencial[4])) {
                        $message->homestate = utf8_encode($row_addresses_residencial[4]);
                    }
                    if (isset($row_addresses_residencial[6])) {
                        $message->homestreet = utf8_encode($row_addresses_residencial[6]);
                    }
                    if (isset($row_addresses_residencial[8])) {
                        if (isset($message->homestreet)) {
                            $message->homestreet .= ":";
                        }
                        $message->homestreet .= utf8_encode($row_addresses_residencial[8]);
                    }
                    if (isset($row_addresses_residencial[7])) {
                        if (isset($message->homestreet)) {
                            $message->homestreet .= ":";
                        }
                        $message->homestreet .= utf8_encode($row_addresses_residencial[7]);
                    }
                    if (isset($row_addresses_residencial[9])) {
                        if (isset($message->homestreet)) {
                            $message->homestreet .= ":";
                        }
                        $message->homestreet .= utf8_encode($row_addresses_residencial[9]);
                    }
                    if (isset($row_addresses_residencial[11])) {
                        if (isset($message->homestreet)) {
                            $message->homestreet .= ":";
                        }
                        $message->homestreet .= utf8_encode($row_addresses_residencial[11]);
                    }
                }
                // Emails
                $result_emails = pg_query($this->db,"select cn.id_connection, connection_name, connection_value, connection_is_default from phpgw_cc_connections cn join phpgw_cc_contact_conns cc on (cn.id_connection = cc.id_connection) join phpgw_cc_typeof_ct_conns ct on (ct.id_typeof_contact_connection = cc.id_typeof_contact_connection) where ct.contact_connection_type_name = 'Email' and cc.id_contact = "  . $row_contact[0] . ";");
                if ($result_emails == FALSE) throw new Exception(pg_last_error($this->db));
                while ($row_emails = pg_fetch_row($result_emails)) {
                    if ($row_emails[1] == "Principal") {
                        $message->email1address = utf8_encode($row_emails[2]);
                    }
                    if ($row_emails[1] == "Alternativo") {
                        $message->email2address = utf8_encode($row_emails[2]);
                    }
                    //TODO : Atribuir o email3address. O Expresso ainda n�o tem campo para um terceiro email :(
                }
                // Telefones
                $result_tel = pg_query($this->db,"select cn.id_connection, connection_name, connection_value, connection_is_default from phpgw_cc_connections cn join phpgw_cc_contact_conns cc on (cn.id_connection = cc.id_connection) join phpgw_cc_typeof_ct_conns ct on (ct.id_typeof_contact_connection = cc.id_typeof_contact_connection) where ct.contact_connection_type_name = 'Telefone' and cc.id_contact = "  . $row_contact[0] . ";");
                if ($result_tel == FALSE) throw new Exception(pg_last_error($this->db));
                while ($row_tel = pg_fetch_row($result_tel)) {
                    if ($row_tel[1] == "Trabalho") {
                        $message->businessphonenumber = utf8_encode($row_tel[2]);
                    }
                    if ($row_tel[1] == "Casa") {
                        $message->homephonenumber = utf8_encode($row_tel[2]);
                    }
                    if ($row_tel[1] == "Celular") {
                        $message->mobilephonenumber = utf8_encode($row_tel[2]);
                    }
                    if ($row_tel[1] == "Fax") {
                        $message->businessfaxnumber = utf8_encode($row_tel[2]);
                    }
                    if ($row_tel[1] == "Principal") {
                        $message->business2phonenumber = utf8_encode($row_tel[2]);
                    }
                    if ($row_tel[1] == "Alternativo") {
                        $message->home2phonenumber = utf8_encode($row_tel[2]);
                    }
                    //TODO : Permitir mais de um n�mero de telefone para Trabalho, Casa e Celular. O Expresso ainda n�o suporta isso :(
                }
            }

        return $message;
    }

    /**
     * Returns message stats, analogous to the folder stats from StatFolder().
     *
     * @param string $folderid id of the folder
     * @param string $id id of the message
     *
     * @throws Exception
     * @access public
     * @return array or boolean if fails
     *          Associative array(
     *              string  "id"            Server unique identifier for the message. Again, try to keep this short (under 20 chars)
     *              int     "flags"         simply '0' for unread, '1' for read
     *              long    "mod"           This is the modification signature. It is any arbitrary string which is constant as long as
     *                                      the message has not changed. As soon as this signature changes, the item is assumed to be completely
     *                                      changed, and will be sent to the PDA as a whole. Normally you can use something like the modification
     *                                      time for this field, which will change as soon as the contents have changed.
     *          )
     */
    public function StatMessage($folderid, $id)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->StatMessage()");

            $result_contact = pg_query($this->db, "select last_update from phpgw_cc_contact where id_contact = " . $id . ";");
            if ($result_contact == FALSE) throw new Exception(pg_last_error($this->db));
            while ($row_contact = pg_fetch_row($result_contact)) {
                if(isset($row_contact[0])) {
                    $message = array();
                    $message["mod"] = substr($row_contact[0], 0, strlen($row_contact[0])-3);
                    $message["id"] = $id;
                    $message["flags"] = 1;
                    return $message;
                }
            }
        return false;
    }

    /**
     * Called when a message has been changed on the mobile. The new message must be saved to disk.
     * The return value must be whatever would be returned from StatMessage() after the message has been saved.
     * This way, the 'flags' and the 'mod' properties of the StatMessage() item may change via ChangeMessage().
     * This method will never be called on E-mail items as it's not 'possible' to change e-mail items. It's only
     * possible to set them as 'read' or 'unread'.
     *
     * @param string $folderid id of the folder
     * @param string $id id of the message
     * @param SyncXXX $message the SyncObject containing a message
     *
     * @param ContentParameters $contentParameters
     * @access public
     * @return array                        same return value as StatMessage()
     */
    public function ChangeMessage($folderid, $id, $message, $contentParameters)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->ChangeMessage()");

        try {
            $result = pg_query($this->db,"BEGIN;");
            if ($result == FALSE) throw new Exception(pg_last_error($this->db));

            // Obtem o id_contact
            $found_id_contact = false;
            if (trim($id) !== "") {
                $result_contact = pg_query($this->db, "select id_contact from phpgw_cc_contact where id_contact = " . $id . ";");
                if ($result_contact == FALSE) throw new Exception(pg_last_error($this->db));
                // tenta localizar id_contact para fazer Update
                while ($row_contact = pg_fetch_row($result_contact)) {
                    if(isset($row_contact[0])) {
                        $id_contact = $row_contact[0];
                        $found_id_contact = true;
                    }
                }
            }
            // se n�o encontrou id_contact para fazer Update, define o pr�ximo id_contact para fazer Insert
            if (!isset($id_contact)) {
                $result = pg_query($this->db,"LOCK TABLE phpgw_cc_contact IN ACCESS EXCLUSIVE MODE;");
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result_contact_max_id = pg_query($this->db, "select max(id_contact) from phpgw_cc_contact;");
                if ($result_contact_max_id == FALSE) throw new Exception(pg_last_error($this->db));
                $row_contact_max_id = pg_fetch_row($result_contact_max_id);
                if (isset($row_contact_max_id[0])) {
                    $id_contact = $row_contact_max_id[0] + 1;
                } else $id_contact = 1;
            }

            // Procura o id_address comercial e residencial
            $result_address = pg_query($this->db, "select co.id_address, tca.contact_address_type_name from phpgw_cc_addresses co join phpgw_cc_contact_addrs ca on (co.id_address = ca.id_address) join phpgw_cc_typeof_ct_addrs tca on (ca.id_typeof_contact_address = tca.id_typeof_contact_address) where ca.id_contact = " . $id_contact . ";");
            if ($result_address == FALSE) throw new Exception(pg_last_error($this->db));
            $found_id_address_comercial = false;
            $found_id_address_residencial = false;
            while ($row_address = pg_fetch_row($result_address)) {
                if(isset($row_address[0])) {
                    if ($row_address[1] == "Comercial") {
                        $id_address_comercial = $row_address[0];
                        $found_id_address_comercial = true;
                    }
                    if ($row_address[1] == "Residencial") {
                        $id_address_residencial = $row_address[0];
                        $found_id_address_residencial = true;
                    }
                }
            }
            // Verifica se os campos de endereco estao preenchidos na mensagem recebida do dispositivo movel
            $isset_business_address_fields = false;
            if(isset($message->businessstate) or isset($message->businesspostalcode) or isset($message->businessstreet)) {
                $isset_business_address_fields = true;
            }
            $isset_home_address_fields = false;
            if(isset($message->homestate) or isset($message->homepostalcode) or isset($message->homestreet)) {
                $isset_home_address_fields = true;
            }
            // Obtem o ultimo id_address
            if (!($found_id_address_comercial and $found_id_address_residencial) and ($isset_business_address_fields or $isset_home_address_fields)) {
                $result = pg_query($this->db,"LOCK TABLE phpgw_cc_addresses IN ACCESS EXCLUSIVE MODE;");
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result_address_max_id = pg_query($this->db, "select max(id_address) from phpgw_cc_addresses;");
                if ($result_address_max_id == FALSE) throw new Exception(pg_last_error($this->db));
                $array_row_address_max_id = pg_fetch_row($result_address_max_id);
                if (isset($array_row_address_max_id)) $row_address_max_id = $array_row_address_max_id[0];
                if (!isset($row_address_max_id)) $row_address_max_id = 0;
            }
            $next_offset_address_to_insert = 0;
            // se n�o encontrou id_address_comercial para fazer Update, define o pr�ximo id_address_comercial para fazer Insert
            if (!$found_id_address_comercial and $isset_business_address_fields) {
                $next_offset_address_to_insert += 1;
                $id_address_comercial = $row_address_max_id + $next_offset_address_to_insert;
            }
            // se n�o encontrou id_address_residencial para fazer Update, define o pr�ximo id_address_residencial para fazer Insert
            if (!$found_id_address_residencial and $isset_home_address_fields) {
                $next_offset_address_to_insert += 1;//            if(isset($message->rtf)) {
//                $rtf_to_ascii = new rtf();
//                $rtf_to_ascii->output("ascii");
//                $result_loadrtf = $rtf_to_ascii->loadrtf(base64_decode($message->rtf));
//                if ($result_loadrtf == true) $rtf_to_ascii->parse();
//                $array
                $id_address_residencial = $row_address_max_id + $next_offset_address_to_insert;
            }

            // Procura o id_connection de Emails e Telefones
            $result_connection = pg_query($this->db, "select cn.id_connection, connection_name, connection_is_default, cc.id_typeof_contact_connection from phpgw_cc_connections cn join phpgw_cc_contact_conns cc on (cn.id_connection = cc.id_connection) join phpgw_cc_typeof_ct_conns ct on (ct.id_typeof_contact_connection = cc.id_typeof_contact_connection) where cc.id_contact = " . $id_contact . ";");
            if ($result_connection == FALSE) throw new Exception(pg_last_error($this->db));
            $found_id_connection_email_principal = false;
            $found_id_connection_email_alternativo = false;
            $found_id_connection_tel_trabalho = false;
            $found_id_connection_tel_casa = false;
            $found_id_connection_tel_celular = false;
            $found_id_connection_tel_fax = false;
            $found_id_connection_tel_principal = false;
            $found_id_connection_tel_alternativo = false;
            $tel_default = "";
            $email_default = "";



            while ($row_connection = pg_fetch_row($result_connection)) {
                if(isset($row_connection[0])) {
                    if ($row_connection[1] == "Principal" and $row_connection[3] == 1) {
                        $id_connection_email_principal = $row_connection[0];
                        $found_id_connection_email_principal = true;
                        if ($row_connection[2] == 't') $email_default = "Principal";
                    }
                    if ($row_connection[1] == "Alternativo" and $row_connection[3] == 1) {
                        $id_connection_email_alternativo = $row_connection[0];
                        $found_id_connection_email_alternativo = true;
                        if ($row_connection[2] == 't') $email_default = "Alternativo";
                    }
                    if ($row_connection[1] == "Trabalho") {
                        $id_connection_tel_trabalho = $row_connection[0];
                        $found_id_connection_tel_trabalho = true;
                        if ($row_connection[2] == 't' && isset($message->businessphonenumber)) $tel_default = "Trabalho";
                    }
                    if ($row_connection[1] == "Casa") {
                        $id_connection_tel_casa = $row_connection[0];
                        $found_id_connection_tel_casa = true;
                        if ($row_connection[2] == 't' & isset($message->homephonenumber)) $tel_default = "Casa";
                    }
                    if ($row_connection[1] == "Celular") {
                        $id_connection_tel_celular = $row_connection[0];
                        $found_id_connection_tel_celular = true;
                        if ($row_connection[2] == 't' & isset($message->mobilephonenumber)) $tel_default = "Celular";
                    }
                    if ($row_connection[1] == "Fax") {
                        $id_connection_tel_fax = $row_connection[0];
                        $found_id_connection_tel_fax = true;
                        if ($row_connection[2] == 't' & isset($message->businessfaxnumber)) $tel_default = "Fax";
                    }
                    if ($row_connection[1] == "Principal" and $row_connection[3] == 2) {
                        $id_connection_tel_principal = $row_connection[0];
                        $found_id_connection_tel_principal = true;
                        if ($row_connection[2] == 't' & isset($message->business2phonenumber)) $tel_default = "Principal";
                    }
                    if ($row_connection[1] == "Alternativo" and $row_connection[3] == 2) {
                        $id_connection_tel_alternativo = $row_connection[0];
                        $found_id_connection_tel_alternativo = true;
                        if ($row_connection[2] == 't' & isset($message->home2phonenumber)) $tel_default = "Alternativo";
                    }
                }
            }

            // Obtem o ultimo id_connection
            if (!($found_id_connection_email_principal and $found_id_connection_email_alternativo and $found_id_connection_tel_trabalho and $found_id_connection_tel_celular and $found_id_connection_tel_casa and $found_id_connection_tel_fax and $found_id_connection_tel_principal and $found_id_connection_tel_alternativo) and (isset($message->email1address) or isset($message->email2address) or isset($message->businessphonenumber) or isset($message->homephonenumber) or isset($message->mobilephonenumber) or isset($message->businessfaxnumber) or isset($message->business2phonenumber) or isset($message->home2phonenumber))){
                $result = pg_query($this->db,"LOCK TABLE phpgw_cc_connections IN ACCESS EXCLUSIVE MODE;");
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result_connection_max_id = pg_query($this->db, "select max(id_connection) from phpgw_cc_connections;");
                if ($result_connection_max_id == FALSE) throw new Exception(pg_last_error($this->db));
                $array_row_connection_max_id = pg_fetch_row($result_connection_max_id);
                if (isset($array_row_connection_max_id)) $row_connection_max_id = $array_row_connection_max_id[0];
                if (!isset($row_connection_max_id)) $row_connection_max_id = 0;
            }
            $next_offset_connection_to_insert = 0;
            // se n�o encontrou id_connection_email_principal para fazer Update, define o pr�ximo id_connection_email_principal para fazer Insert
            if (!$found_id_connection_email_principal and isset($message->email1address)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_email_principal = $row_connection_max_id + $next_offset_connection_to_insert;
            }
            // se n�o encontrou id_connection_email_alternativo para fazer Update, define o pr�ximo id_connection_email_alternativo para fazer Insert
            if (!$found_id_connection_email_alternativo and isset($message->email2address)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_email_alternativo = $row_connection_max_id + $next_offset_connection_to_insert;
            }
            // se n�o encontrou $id_connection_tel_trabalho para fazer Update, define o pr�ximo $id_connection_tel_trabalho para fazer Insert
            if (!$found_id_connection_tel_trabalho and isset($message->businessphonenumber)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_tel_trabalho = $row_connection_max_id + $next_offset_connection_to_insert;
            }
            // se n�o encontrou $id_connection_tel_casa para fazer Update, define o pr�ximo $id_connection_tel_casa para fazer Insert
            if (!$found_id_connection_tel_casa and isset($message->homephonenumber)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_tel_casa = $row_connection_max_id + $next_offset_connection_to_insert;
            }
            // se n�o encontrou $id_connection_tel_celular para fazer Update, define o pr�ximo $id_connection_tel_celular para fazer Insert
            if (!$found_id_connection_tel_celular and isset($message->mobilephonenumber)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_tel_celular = $row_connection_max_id + $next_offset_connection_to_insert;
            }
            // se n�o encontrou $id_connection_tel_fax para fazer Update, define o pr�ximo $id_connection_tel_fax para fazer Insert
            if (!$found_id_connection_tel_fax and isset($message->businessfaxnumber)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_tel_fax = $row_connection_max_id + $next_offset_connection_to_insert;
            }
            // se n�o encontrou $id_connection_tel_principal para fazer Update, define o pr�ximo $id_connection_tel_principal para fazer Insert
            if (!$found_id_connection_tel_principal and isset($message->business2phonenumber)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_tel_principal = $row_connection_max_id + $next_offset_connection_to_insert;
            }
            // se n�o encontrou $id_connection_tel_alternativo para fazer Update, define o pr�ximo $id_connection_tel_alternativo para fazer Insert
            if (!$found_id_connection_tel_alternativo and isset($message->home2phonenumber)) {
                $next_offset_connection_to_insert += 1;
                $id_connection_tel_alternativo = $row_connection_max_id + $next_offset_connection_to_insert;
            }

            // Incluir/Alterar registro na tabela phpgw_cc_contact no Banco de Dados
            if(isset($message->picture) && $message->picture) {
            	$arrayContact["photo"] = base64_decode($message->picture);
            }
            if(isset($message->nickname)) {
                $arrayContact["alias"] = utf8_decode($message->nickname);
            }
            if(isset($message->firstname)) {
                $arrayContact["given_names"] = utf8_decode($message->firstname);
                $arrayContact["names_ordered"] = utf8_decode($message->firstname);
            }
            if (isset($message->middlename)) {
                $arrayContact["family_names"] = utf8_decode($message->middlename);
                if(isset($message->firstname)) {
                    $arrayContact["names_ordered"] .= " ";
                }
                $arrayContact["names_ordered"] .= utf8_decode($message->middlename);
            }
            if(isset($message->lastname)) {
                if(isset($message->middlename)) {
                    $arrayContact["family_names"] .= " ";
                }
                if (isset($arrayContact["family_names"])) $arrayContact["family_names"] .= utf8_decode($message->lastname);
                else $arrayContact["family_names"] = utf8_decode($message->lastname);
                if(isset($message->firstname) or isset($message->middlename)) {
                    $arrayContact["names_ordered"] .= " ";
                }
                $arrayContact["names_ordered"] .= utf8_decode($message->lastname);
            }
            if (isset($message->birthday)) {
                $tz = date_default_timezone_get();
                date_default_timezone_set('UTC');
                $arrayContact["birthdate"] = date("Y-m-d",$message->birthday);
                date_default_timezone_set($tz);
            }
            //TODO: Incluir o campo de Aniversario na Sincronizacao. O DB do Expresso nao tem esse campo :-(


            if(isset($message->body)) {
                $arrayContact["notes"] = utf8_decode($message->body);
            }
//            if(isset($message->rtf)) {
//                $rtf_to_ascii = new rtf();
//                $rtf_to_ascii->output("ascii");
//                $result_loadrtf = $rtf_to_ascii->loadrtf(base64_decode($message->rtf));
//                if ($result_loadrtf == true) $rtf_to_ascii->parse();
//                $arrayContact["notes"] = $rtf_to_ascii->out;
//            }
            //TODO: Tratar o conteudo do campo de categorias
            //if(isset($message->categories)) {
            //	$arrayContact["category"] = $this->truncateString(utf8_decode($message->categories),20);
            //}
            if(isset($message->webpage)) {
                $arrayContact["web_page"] = $this->truncateString(utf8_decode($message->webpage),100);
            }
            if(isset($message->companyname)) {
                $arrayContact["corporate_name"] = $this->truncateString(utf8_decode($message->companyname),100);
            }
            if(isset($message->jobtitle)) {
                $arrayContact["job_title"] = $this->truncateString(utf8_decode($message->jobtitle),40);
            }
            if(isset($message->department)) {
                $arrayContact["department"] = $this->truncateString(utf8_decode($message->department),30);
            }
            if (!$found_id_contact){
                $arrayContact["id_contact"] = $id_contact;
                $arrayContact["id_owner"] = $this->_uidnumber;
                $result = pg_insert($this->db, 'phpgw_cc_contact', $arrayContact);
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            } else {
                $result = pg_update($this->db, 'phpgw_cc_contact', $arrayContact, array('id_contact' => $id_contact));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db)); }

            // Incluir/Alterar Endereco Comercial na tabela phpgw_cc_addresses no Banco de Dados
            if(isset($message->businessstate)) {
                $result = pg_query($this->db, "select id_state, id_country from phpgw_cc_state where LOWER(to_ASCII(state_name)) = '" . trim(strtolower($this->removeAccents(utf8_decode($message->businessstate)))) . "' or LOWER(to_ASCII(state_symbol)) = '" . trim(strtolower($this->removeAccents(utf8_decode($message->businessstate)))) . "';");
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $row = pg_fetch_row($result);
                if(isset($row[0])) {
                    $arrayAddressComercial["id_state"] = $row[0];
                }
                if(isset($row[1])) {
                    $arrayAddressComercial["id_country"] = $row[1];
                }
                if(isset($message->businesscity) and isset($arrayAddressComercial["id_state"])) {
                    $result = pg_query($this->db, "select id_city from phpgw_cc_city where id_state = " . $arrayAddressComercial["id_state"] . " and LOWER(to_ASCII(city_name)) = '" . trim(strtolower($this->removeAccents(utf8_decode($message->businesscity)))) . "';");
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $row = pg_fetch_row($result);
                    if(isset($row[0])) {
                        $arrayAddressComercial["id_city"] = $row[0];
                    }
                }
            }
            if(isset($message->businesspostalcode)) {
                $arrayAddressComercial["postal_code"] = $this->truncateString(utf8_decode($message->businesspostalcode),15);
            }
            if(isset($message->businessstreet)) {
                $arrayAddressComercial["address1"] = $this->truncateString(utf8_decode($message->businessstreet),60);
            }
            if($isset_business_address_fields) {
                if (!$found_id_address_comercial) {
                    $arrayAddressComercial["id_address"] = $id_address_comercial;
                    $result = pg_insert($this->db, 'phpgw_cc_addresses', $arrayAddressComercial);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactAddressComercial["id_contact"] = $id_contact;
                    $arrayContactAddressComercial["id_address"] = $id_address_comercial;
                    $arrayContactAddressComercial["id_typeof_contact_address"] = 1; //comercial
                    $result = pg_insert($this->db, 'phpgw_cc_contact_addrs', $arrayContactAddressComercial);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_addresses', $arrayAddressComercial, array('id_address' => $id_address_comercial));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }
            } elseif ($found_id_address_comercial) {
                $result = pg_delete($this->db, "phpgw_cc_contact_addrs", array('id_contact' => $id_contact, 'id_address' => $id_address_comercial));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_addresses", array('id_address' => $id_address_comercial));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Incluir/Alterar Endereco Residencial na tabela phpgw_cc_addresses no Banco de Dados
            if(isset($message->homestate)) {
                $result = pg_query($this->db, "select id_state, id_country from phpgw_cc_state where LOWER(to_ASCII(state_name)) = '" . trim(strtolower($this->removeAccents(utf8_decode($message->homestate)))) . "' or LOWER(to_ASCII(state_symbol)) = '" . trim(strtolower($this->removeAccents(utf8_decode($message->homestate)))) . "';");
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $row = pg_fetch_row($result);
                if(isset($row[0])) {
                    $arrayAddressResidencial["id_state"] = $row[0];
                }
                if(isset($row[1])) {
                    $arrayAddressResidencial["id_country"] = $row[1];
                }
                if(isset($message->homecity) and isset($arrayAddressResidencial["id_state"])) {
                    $result = pg_query($this->db, "select id_city from phpgw_cc_city where id_state = " . $arrayAddressResidencial["id_state"] . " and LOWER(to_ASCII(city_name)) = '" . trim(strtolower($this->removeAccents(utf8_decode($message->homecity)))) . "';");
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $row = pg_fetch_row($result);
                    if(isset($row[0])) {
                        $arrayAddressResidencial["id_city"] = $row[0];
                    }
                }
            }
            if(isset($message->homepostalcode)) {
                $arrayAddressResidencial["postal_code"] = $this->truncateString(utf8_decode($message->homepostalcode),15);
            }
            if(isset($message->homestreet)) {
                $arrayAddressResidencial["address1"] = $this->truncateString(utf8_decode($message->homestreet),60);
            }
            if($isset_home_address_fields) {
                if (!$found_id_address_residencial) {
                    $arrayAddressResidencial["id_address"] = $id_address_residencial;
                    $result = pg_insert($this->db, 'phpgw_cc_addresses', $arrayAddressResidencial);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactAddressResidencial["id_contact"] = $id_contact;
                    $arrayContactAddressResidencial["id_address"] = $id_address_residencial;
                    $arrayContactAddressResidencial["id_typeof_contact_address"] = 2; //residencial
                    $result = pg_insert($this->db, 'phpgw_cc_contact_addrs', $arrayContactAddressResidencial);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_addresses', $arrayAddressResidencial, array('id_address' => $id_address_residencial));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }
            } elseif ($found_id_address_residencial) {
                $result = pg_delete($this->db, "phpgw_cc_contact_addrs", array('id_contact' => $id_contact, 'id_address' => $id_address_residencial));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_addresses", array('id_address' => $id_address_residencial));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Email Principal
            if(isset($message->email1address)) {
                $arrayConnectionEmailPrincipal["connection_value"] = $this->truncateString($this->formatMail(utf8_decode($message->email1address)),100);
                if (!$found_id_connection_email_principal){
                    $arrayConnectionEmailPrincipal["id_connection"] = $id_connection_email_principal;
                    $arrayConnectionEmailPrincipal["connection_name"] = "Principal";
                    if ($email_default != "Alternativo"){
                        $arrayConnectionEmailPrincipal["connection_is_default"] = 't';
                        $email_default = "Principal";
                    } else {
                        $arrayConnectionEmailPrincipal["connection_is_default"] = 'f';
                    }
                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionEmailPrincipal);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_email_principal;
                    $arrayContactConnection["id_typeof_contact_connection"] = 1;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionEmailPrincipal, array('id_connection' => $id_connection_email_principal));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_email_principal) {

                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_email_principal));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_email_principal));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_email_principal));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Email Alternativo
            if(isset($message->email2address)) {
                $arrayConnectionEmailAlternativo["connection_value"] = $this->truncateString($this->formatMail(utf8_decode($message->email2address)),100);
                if (!$found_id_connection_email_alternativo){
                    $arrayConnectionEmailAlternativo["id_connection"] = $id_connection_email_alternativo;
                    $arrayConnectionEmailAlternativo["connection_name"] = "Alternativo";
                    if ($email_default != "Principal"){
                        $arrayConnectionEmailAlternativo["connection_is_default"] = 't';
                        $email_default = "Alternativo";
                    } else {
                        $arrayConnectionEmailAlternativo["connection_is_default"] = 'f';
                    }
                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionEmailAlternativo);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_email_alternativo;
                    $arrayContactConnection["id_typeof_contact_connection"] = 1;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionEmailAlternativo, array('id_connection' => $id_connection_email_alternativo));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_email_alternativo) {
                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_email_alternativo));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_email_alternativo));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_email_alternativo));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }
            //TODO : Atribuir o email3address. O Expresso ainda n�o tem campo para um terceiro email :(

            // Telefone Trabalho
            if(isset($message->businessphonenumber)) {
                $arrayConnectionTelTrabalho["connection_value"] = $this->truncateString(utf8_decode($message->businessphonenumber),100);

                if ($tel_default != "Celular" and $tel_default != "Casa" and $tel_default != "Fax"  and $tel_default != "Principal"  and $tel_default != "Alternativo"){
                    $arrayConnectionTelTrabalho["connection_is_default"] = 't';
                    $tel_default = "Trabalho";
                } else {
                    $arrayConnectionTelTrabalho["connection_is_default"] = 'f';
                }

                if (!$found_id_connection_tel_trabalho){
                    $arrayConnectionTelTrabalho["id_connection"] = $id_connection_tel_trabalho;
                    $arrayConnectionTelTrabalho["connection_name"] = "Trabalho";
                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionTelTrabalho);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_tel_trabalho;
                    $arrayContactConnection["id_typeof_contact_connection"] = 2;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionTelTrabalho, array('id_connection' => $id_connection_tel_trabalho));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_tel_trabalho) {

                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_tel_trabalho));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_tel_trabalho));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_tel_trabalho));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Telefone Celular
            if(isset($message->mobilephonenumber)) {
                $arrayConnectionTelCelular["connection_value"] = $this->truncateString(utf8_decode($message->mobilephonenumber),100);

                if ($tel_default != "Trabalho" and $tel_default != "Casa" and $tel_default != "Fax"   and $tel_default != "Principal"  and $tel_default != "Alternativo"){
                    $arrayConnectionTelCelular["connection_is_default"] = 't';
                    $tel_default = "Celular";
                } else {
                    $arrayConnectionTelCelular["connection_is_default"] = 'f';
                }

                if (!$found_id_connection_tel_celular){
                    $arrayConnectionTelCelular["id_connection"] = $id_connection_tel_celular;
                    $arrayConnectionTelCelular["connection_name"] = "Celular";

                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionTelCelular);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_tel_celular;
                    $arrayContactConnection["id_typeof_contact_connection"] = 2;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionTelCelular, array('id_connection' => $id_connection_tel_celular));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_tel_celular) {
                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_tel_celular));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_tel_celular));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_tel_celular));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Telefone Casa
            if(isset($message->homephonenumber)) {
                $arrayConnectionTelCasa["connection_value"] = $this->truncateString(utf8_decode($message->homephonenumber),100);

                if ($tel_default != "Trabalho" and $tel_default != "Celular" and $tel_default != "Fax" and $tel_default != "Principal"  and $tel_default != "Alternativo"){
                    $arrayConnectionTelCasa["connection_is_default"] = 't';
                    $tel_default = "Casa";
                } else {
                    $arrayConnectionTelCasa["connection_is_default"] = 'f';
                }

                if (!$found_id_connection_tel_casa){
                    $arrayConnectionTelCasa["id_connection"] = $id_connection_tel_casa;
                    $arrayConnectionTelCasa["connection_name"] = "Casa";

                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionTelCasa);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_tel_casa;
                    $arrayContactConnection["id_typeof_contact_connection"] = 2;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionTelCasa, array('id_connection' => $id_connection_tel_casa));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_tel_casa) {

                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_tel_casa));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_tel_casa));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_tel_casa));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Fax
            if(isset($message->businessfaxnumber)) {
                $arrayConnectionFax["connection_value"] = $this->truncateString(utf8_decode($message->businessfaxnumber),100);

                if ($tel_default != "Trabalho" and $tel_default != "Celular" and $tel_default != "Casa" and $tel_default != "Principal"  and $tel_default != "Alternativo"){
                    $arrayConnectionFax["connection_is_default"] = 't';
                    $tel_default = "Fax";
                } else {
                    $arrayConnectionFax["connection_is_default"] = 'f';
                }

                if (!$found_id_connection_tel_fax){
                    $arrayConnectionFax["id_connection"] = $id_connection_tel_fax;
                    $arrayConnectionFax["connection_name"] = "Fax";

                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionFax);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_tel_fax;
                    $arrayContactConnection["id_typeof_contact_connection"] = 2;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionFax, array('id_connection' => $id_connection_tel_fax));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_tel_fax) {

                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_tel_fax));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_tel_fax));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_tel_fax));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Telefone Principal
            if(isset($message->business2phonenumber)) {
                $arrayConnectionTelPrincipal["connection_value"] = $this->truncateString(utf8_decode($message->business2phonenumber),100);
                if ($tel_default != "Celular" and $tel_default != "Casa" and $tel_default != "Fax"  and $tel_default != "Trabalho"  and $tel_default != "Alternativo"){
                    $arrayConnectionTelPrincipal["connection_is_default"] = 't';
                    $tel_default = "Principal";
                } else {
                    $arrayConnectionTelPrincipal["connection_is_default"] = 'f';
                }

                if (!$found_id_connection_tel_principal){
                    $arrayConnectionTelPrincipal["id_connection"] = $id_connection_tel_principal;
                    $arrayConnectionTelPrincipal["connection_name"] = "Principal";

                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionTelPrincipal);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_tel_principal;
                    $arrayContactConnection["id_typeof_contact_connection"] = 2;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionTelPrincipal, array('id_connection' => $id_connection_tel_principal));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_tel_principal) {

                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_tel_principal));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_tel_principal));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_tel_principal));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            // Telefone Alternativo
            if(isset($message->home2phonenumber)) {
                $arrayConnectionTelAlternativo["connection_value"] = $this->truncateString(utf8_decode($message->home2phonenumber),100);

                if ($tel_default != "Trabalho" and $tel_default != "Celular" and $tel_default != "Fax" and $tel_default != "Principal"  and $tel_default != "Casa"){
                    $arrayConnectionTelAlternativo["connection_is_default"] = 't';
                    $tel_default = "Alternativo";
                } else {
                    $arrayConnectionTelAlternativo["connection_is_default"] = 'f';
                }

                if (!$found_id_connection_tel_alternativo){
                    $arrayConnectionTelAlternativo["id_connection"] = $id_connection_tel_alternativo;
                    $arrayConnectionTelAlternativo["connection_name"] = "Alternativo";

                    $result = pg_insert($this->db, 'phpgw_cc_connections', $arrayConnectionTelAlternativo);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                    $arrayContactConnection["id_contact"] = $id_contact;
                    $arrayContactConnection["id_connection"] = $id_connection_tel_alternativo;
                    $arrayContactConnection["id_typeof_contact_connection"] = 2;
                    $result = pg_insert($this->db, 'phpgw_cc_contact_conns', $arrayContactConnection);
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                } else {
                    $result = pg_update($this->db, 'phpgw_cc_connections', $arrayConnectionTelAlternativo, array('id_connection' => $id_connection_tel_alternativo));
                    if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                }

            } elseif ($found_id_connection_tel_alternativo) {
                $result = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact, 'id_connection' => $id_connection_tel_alternativo));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_contact_grps", array('id_connection' => $id_connection_tel_alternativo));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
                $result = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection_tel_alternativo));
                if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            }

            //TODO : Permitir mais de um n�mero de telefone para Trabalho, Celular e Casa. O Expresso ainda n�o suporta isso :(

            $result = pg_query($this->db,"COMMIT;");
            if ($result == FALSE) throw new Exception(pg_last_error($this->db));
            if (!$id) {
                $id = $id_contact;
            }
        } catch (Exception $e) {

            debugLog("exception -> " . $e->getMessage() . " - ARQUIVO: " . $e->getFile() . " - LINHA: " . $e->getLine());
            pg_query($this->db,"ROLLBACK;");
            return false;
        }
        return $this->StatMessage($folderid, $id);
    }

    /**
     * Changes the 'read' flag of a message on disk. The $flags
     * parameter can only be '1' (read) or '0' (unread). After a call to
     * SetReadFlag(), GetMessageList() should return the message with the
     * new 'flags' but should not modify the 'mod' parameter. If you do
     * change 'mod', simply setting the message to 'read' on the mobile will trigger
     * a full resync of the item from the server.
     *
     * @param string $folderid id of the folder
     * @param string $id id of the message
     * @param int $flags read flag of the message
     *
     * @param ContentParameters $contentParameters
     * @access public
     * @return boolean                      status of the operation
     */
    public function SetReadFlag($folderid, $id, $flags, $contentParameters)
    {

        // TODO: Implement SetReadFlag() method.
    }

    /**
     * Called when the user has requested to delete (really delete) a message. Usually
     * this means just unlinking the file its in or somesuch. After this call has succeeded, a call to
     * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the mobile
     * as it will be seen as a 'new' item. This means that if this method is not implemented, it's possible to
     * delete messages on the PDA, but as soon as a sync is done, the item will be resynched to the mobile
     *
     * @param string $folderid id of the folder
     * @param string $id id of the message
     *
     * @param ContentParameters $contentParameters
     * @access public
     * @return boolean                      status of the operation
     */
    public function DeleteMessage($folderid, $id, $contentParameters)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->DeleteMessage()");
        $result = pg_query($this->db,"BEGIN;");
        try {
            $result_contact = pg_query($this->db, "select id_contact from phpgw_cc_contact where id_contact = " . $id . ";");
            if ($result_contact == FALSE) throw new Exception(pg_last_error($this->db));
            while ($row_contact = pg_fetch_row($result_contact)) {
                if(isset($row_contact[0])) {
                    $id_contact = $row_contact[0];
                }
            }
            if(!isset($id_contact)){
                return true;
            }

            $result_contact_addrs = pg_query($this->db, "select id_address from phpgw_cc_contact_addrs where id_contact = " . $id_contact . ";");
            if ($result_contact_addrs == FALSE) throw new Exception(pg_last_error($this->db));
            while ($row_contact_addrs = pg_fetch_row($result_contact_addrs)) {
                if(isset($row_contact_addrs[0])) {
                    $id_address = $row_contact_addrs[0];
                    $result_delete_address = pg_delete($this->db, "phpgw_cc_addresses", array('id_address' => $id_address));
                    if ($result_delete_address == FALSE) throw new Exception(pg_last_error($this->db));
                }
            }

            $result_delete_contact_addrs = pg_delete($this->db, "phpgw_cc_contact_addrs", array('id_contact' => $id_contact));
            if ($result_delete_contact_addrs == FALSE) throw new Exception(pg_last_error($this->db));

            $result_contact_conns = pg_query($this->db, "select id_connection from phpgw_cc_contact_conns where id_contact = " . $id_contact . ";");
            if ($result_contact_conns == FALSE) throw new Exception(pg_last_error($this->db));
            while ($row_contact_conns = pg_fetch_row($result_contact_conns)) {
                if(isset($row_contact_conns[0])) {
                    $id_connection = $row_contact_conns[0];
                    $result_delete_connections = pg_delete($this->db, "phpgw_cc_connections", array('id_connection' => $id_connection));
                    if ($result_delete_connections == FALSE) throw new Exception(pg_last_error($this->db));
                    $result_delete_contact_grps = pg_delete($this->db,"phpgw_cc_contact_grps", array('id_connection' => $id_connection));
                    if ($result_delete_contact_grps == FALSE) throw new Exception(pg_last_error($this->db));
                }
            }

            $result_delete_contact_conns = pg_delete($this->db, "phpgw_cc_contact_conns", array('id_contact' => $id_contact));
            if ($result_delete_contact_conns == FALSE) throw new Exception(pg_last_error($this->db));
            $result_delete_contact = pg_delete($this->db, "phpgw_cc_contact", array('id_contact' => $id_contact));
            if ($result_delete_contact == FALSE) throw new Exception(pg_last_error($this->db));
            $result = pg_query($this->db,"COMMIT;");
            if ($result == FALSE) throw new Exception(pg_last_error($this->db));
        } catch (Exception $e) {

            pg_query($this->db,"ROLLBACK;");
            debugLog("exception -> " . $e->getMessage() . " - ARQUIVO: " . $e->getFile() . " - LINHA: " . $e->getLine());
            return false;
        }
        return true;
    }

    /**
     * Called when the user moves an item on the PDA from one folder to another. Whatever is needed
     * to move the message on disk has to be done here. After this call, StatMessage() and GetMessageList()
     * should show the items to have a new parent. This means that it will disappear from GetMessageList()
     * of the sourcefolder and the destination folder will show the new message
     *
     * @param string $folderid id of the source folder
     * @param string $id id of the message
     * @param string $newfolderid id of the destination folder
     *
     * @param ContentParameters $contentParameters
     * @access public
     * @return boolean                      status of the operation
     */
    public function MoveMessage($folderid, $id, $newfolderid, $contentParameters)
    {
        // TODO: Implement MoveMessage() method.
    }

    /**
     * Authenticates the user
     *
     * @param string        $username
     * @param string        $domain
     * @param string        $password
     *
     * @access public
     * @return boolean
     * @throws FatalException   e.g. some required libraries are unavailable
     */
    public function Logon($username, $domain, $password)
    {
        ZLog::Write(LOGLEVEL_DEBUG, "ExpressoContactProvider->Logon()");

        $ldapConfig = parse_ini_file(EXPRESSO_PATH . '/prototype/config/OpenLDAP.srv' , true );
        $ldapConfig =  $ldapConfig['config'];
        $sr = ldap_search( $GLOBALS['connections']['ldap'] , $ldapConfig['context'] , "(uid=$username)" , array('uidNumber'), 0 , 1 );
        if(!$sr) return false;

        $entries = ldap_get_entries( $GLOBALS['connections']['ldap'] , $sr );
        $this->_uidnumber = $entries[0]['uidnumber'][0];
        $this->db =  $GLOBALS['connections']['db'] ;

        return true;
    }

    /**
     * Logs off
     * non critical operations closing the session should be done here
     *
     * @access public
     * @return boolean
     */
    public function Logoff()
    {
       return true;
    }

    /**
     * Sends an e-mail
     * This messages needs to be saved into the 'sent items' folder
     *
     * Basically two things can be done
     *      1) Send the message to an SMTP server as-is
     *      2) Parse the message, and send it some other way
     *
     * @param SyncSendMail        $sm         SyncSendMail object
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function SendMail($sm)
    {
        // TODO: Implement SendMail() method.
    }

    /**
     * Returns the waste basket
     *
     * The waste basked is used when deleting items; if this function returns a valid folder ID,
     * then all deletes are handled as moves and are sent to the backend as a move.
     * If it returns FALSE, then deletes are handled as real deletes
     *
     * @access public
     * @return string
     */
    public function GetWasteBasket()
    {
        // TODO: Implement GetWasteBasket() method.
    }

    /**
     * Returns the content of the named attachment as stream. The passed attachment identifier is
     * the exact string that is returned in the 'AttName' property of an SyncAttachment.
     * Any information necessary to locate the attachment must be encoded in that 'attname' property.
     * Data is written directly - 'print $data;'
     *
     * @param string        $attname
     *
     * @access public
     * @return SyncItemOperationsAttachment
     * @throws StatusException
     */
    public function GetAttachmentData($attname)
    {
        // TODO: Implement GetAttachmentData() method.
    }

    function escape($data){
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->escape($val);
            }
            return $data;
        }
        $data = str_replace("\r\n", "\n", $data);
        $data = str_replace("\r", "\n", $data);
        $data = str_replace(array('\\', ';', ',', "\n"), array('\\\\', '\\;', '\\,', '\\n'), $data);
        return $data;
    }

    function truncateString($string, $size)
    {
        if(strlen($string) <= $size) return $string;
        else return substr($string, 0, $size - 1);
    }

    function formatMail($mail)
    {
        if (preg_match('/[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}/', $mail , $mat ))
            return $mat[0];
        else
            return '';
    }

    function  removeAccents($data){
        $data = strtr($data,"������������������������������������������������������������","aaaaaaaceeeeiiii noooooxouuutbaaaaaaaceeeeiiii nooooo/ouuuty");
        return $data;
    }

}