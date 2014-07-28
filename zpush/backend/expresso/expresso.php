<?php
require_once __DIR__ . '/../../lib/default/diffbackend/diffbackend.php';
require_once __DIR__ . '/providers/imapProvider.php';
require_once __DIR__ . '/providers/contactProvider.php';
require_once __DIR__ . '/providers/calendarProvider.php';

class BackendExpresso extends BackendDiff
{
    var $providers = array('Contact','Imap','Calendar');
    var $providerInstances;
    var $providersFolderMap;
    var $sendMailProvider = 'Imap';
    var $cacheFolders = array();

    function __construct()
    {
        foreach($this->providers as $provider)
        {
            $providerClass = 'Expresso'.$provider.'Provider';
            $this->providerInstances[$provider] = new $providerClass();
        }
    }

    private function getProvider( $folderId )
    {
        foreach($this->providers as $provider)
        {
            if(!isset($this->cacheFolders[$provider]))
                $this->cacheFolders[$provider] =  $this->providerInstances[$provider]->GetFolderList();

            foreach($this->cacheFolders[$provider] as $folder)
            {
                if($folder['id'] == $folderId && is_object($this->providerInstances[$provider]))
                    return $this->providerInstances[$provider];
            }
        }

        throw new FatalException("BackendExpresso->getProvider(): Provide not found", 0, null, LOGLEVEL_FATAL);
    }

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
        $return = array();

        foreach($this->providers as $provider)
             $return = array_merge($return , $this->providerInstances[$provider]->GetFolderList());


        return $return;
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
       return $this->getProvider($id)->GetFolder($id);
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
        return $this->getProvider($id)->StatFolder($id);
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
        return $this->getProvider($oldid ? $oldid: $folderid)->ChangeFolder($folderid, $oldid, $displayname, $type);
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
        return $this->getProvider($id)->DeleteFolder($id, $parentid);
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
     * @param string        $folderid       id of the parent folder
     * @param long          $cutoffdate     timestamp in the past from which on messages should be returned
     *
     * @access public
     * @return array/false                  array with messages or false if folder is not available
     */
    public function GetMessageList($folderid, $cutoffdate)
    {
        return $this->getProvider($folderid)->GetMessageList($folderid, $cutoffdate);
    }

    /**
     * Returns the actual SyncXXX object type. The '$folderid' of parent folder can be used.
     * Mixing item types returned is illegal and will be blocked by the engine; ie returning an Email object in a
     * Tasks folder will not do anything. The SyncXXX objects should be filled with as much information as possible,
     * but at least the subject, body, to, from, etc.
     *
     * @param string            $folderid           id of the parent folder
     * @param string            $id                 id of the message
     * @param ContentParameters $contentparameters  parameters of the requested message (truncation, mimesupport etc)
     *
     * @access public
     * @return object/false                 false if the message could not be retrieved
     */
    public function GetMessage($folderid, $id, $contentparameters)
    {
        return $this->getProvider($folderid)->GetMessage($folderid, $id, $contentparameters);
    }

    /**
     * Returns message stats, analogous to the folder stats from StatFolder().
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     *
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
        return $this->getProvider($folderid)->StatMessage($folderid, $id);
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
        return $this->getProvider($folderid)->ChangeMessage($folderid, $id, $message, $contentParameters);
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
        return $this->getProvider($folderid)->SetReadFlag($folderid,$id, $flags, $contentParameters);
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
        return $this->getProvider($folderid)->DeleteMessage($folderid, $id, $contentParameters);
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
        if( $this->getProvider($folderid) instanceof ExpressoContactProvider ) //Contatos não tem lixeria, mensagem deve ser removida imediatamente
            return $this->DeleteMessage($folderid , $id , $contentParameters);

        if( $this->getProvider($folderid) instanceof ExpressoCalendarProvider && !($this->getProvider($newfolderid) instanceof ExpressoCalendarProvider ) )
            return $this->DeleteMessage($folderid , $id , $contentParameters);

        return $this->getProvider($folderid)->MoveMessage($folderid, $id , $newfolderid, $contentParameters);
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
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ExpressoBackend->Logon(): Trying to authenticate user '%s'..", $username));

        foreach($this->providers as $provider)
        {
            if( !$this->providerInstances[$provider]->Logon($username, $domain, $password) )
            {
                ZLog::Write(LOGLEVEL_ERROR, 'ExpressoBackend->Logon(): login failed provide :'.$provider);
                return false;
            }
        }

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
        foreach($this->providers as $provider)
           $this->providerInstances[$provider]->Logoff();

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
        return $this->providerInstances[$this->sendMailProvider]->SendMail($sm);
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
        return $this->providerInstances['Imap']->GetWasteBasket();
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
        list($folderid, $id, $part) = explode(":", $attname);
        return $this->getProvider($folderid)->GetAttachmentData($attname);
    }

}
