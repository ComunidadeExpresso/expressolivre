<?php
/***********************************************
* File      :   itemoperations.php
* Project   :   Z-Push
* Descr     :   Provides the ItemOperations command
*
* Created   :   16.02.2012
*
* Copyright 2007 - 2013 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation with the following additional
* term according to sec. 7:
*
* According to sec. 7 of the GNU Affero General Public License, version 3,
* the terms of the AGPL are supplemented with the following terms:
*
* "Zarafa" is a registered trademark of Zarafa B.V.
* "Z-Push" is a registered trademark of Zarafa Deutschland GmbH
* The licensing of the Program under the AGPL does not imply a trademark license.
* Therefore any rights, title and interest in our trademarks remain entirely with us.
*
* However, if you propagate an unmodified version of the Program you are
* allowed to use the term "Z-Push" to indicate that you distribute the Program.
* Furthermore you may use our trademarks where it is necessary to indicate
* the intended purpose of a product or service provided you use it in accordance
* with honest practices in industrial or commercial matters.
* If you want to propagate modified versions of the Program under the name "Z-Push",
* you may only do so if you have a written permission by Zarafa Deutschland GmbH
* (to acquire a permission please contact Zarafa at trademark@zarafa.com).
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

class ItemOperations extends RequestProcessor {

    /**
     * Handles the ItemOperations command
     * Provides batched online handling for Fetch, EmptyFolderContents and Move
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        // Parse input
        if(!self::$decoder->getElementStartTag(SYNC_ITEMOPERATIONS_ITEMOPERATIONS))
            return false;

        $itemoperations = array();
        //ItemOperations can either be Fetch, EmptyFolderContents or Move
        while (1) {
            //TODO check if multiple item operations are possible in one request
            $el = self::$decoder->getElement();

            if($el[EN_TYPE] != EN_TYPE_STARTTAG)
                return false;

            $fetch = $efc = $move = false;
            $operation = array();
            if($el[EN_TAG] == SYNC_ITEMOPERATIONS_FETCH) {
                $fetch = true;
                $operation['operation'] = SYNC_ITEMOPERATIONS_FETCH;
                self::$topCollector->AnnounceInformation("Fetch", true);
            }
            else if($el[EN_TAG] == SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS) {
                $efc = true;
                $operation['operation'] = SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS;
                self::$topCollector->AnnounceInformation("Empty Folder", true);
            }
            else if($el[EN_TAG] == SYNC_ITEMOPERATIONS_MOVE) {
                $move = true;
                $operation['operation'] = SYNC_ITEMOPERATIONS_MOVE;
                self::$topCollector->AnnounceInformation("Move", true);
            }

            if(!$fetch && !$efc && !$move) {
                ZLog::Write(LOGLEVEL_DEBUG, "Unknown item operation:".print_r($el, 1));
                self::$topCollector->AnnounceInformation("Unknown operation", true);
                return false;
            }

            if ($fetch) {
                if(!self::$decoder->getElementStartTag(SYNC_ITEMOPERATIONS_STORE))
                    return false;
                $operation['store'] = self::$decoder->getElementContent();
                if(!self::$decoder->getElementEndTag())
                    return false;//SYNC_ITEMOPERATIONS_STORE

                if(self::$decoder->getElementStartTag(SYNC_SEARCH_LONGID)) {
                    $operation['longid'] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;//SYNC_SEARCH_LONGID
                }

                if(self::$decoder->getElementStartTag(SYNC_FOLDERID)) {
                    $operation['folderid'] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;//SYNC_FOLDERID
                }

                if(self::$decoder->getElementStartTag(SYNC_SERVERENTRYID)) {
                    $operation['serverid'] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;//SYNC_SERVERENTRYID
                }

                if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_FILEREFERENCE)) {
                    $operation['filereference'] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;//SYNC_AIRSYNCBASE_FILEREFERENCE
                }

                if(self::$decoder->getElementStartTag(SYNC_ITEMOPERATIONS_OPTIONS)) {
                    //TODO other options
                    //schema
                    //range
                    //username
                    //password
                    //bodypartpreference
                    //rm:RightsManagementSupport

                    // Save all OPTIONS into a ContentParameters object
                    $operation["cpo"] = new ContentParameters();
                    while(1) {
                        // Android 4.3 sends empty options tag, so we don't have to look further
                        $e = self::$decoder->peek();
                        if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                            break;
                        }

                        while (self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_BODYPREFERENCE)) {
                            if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TYPE)) {
                                $bptype = self::$decoder->getElementContent();
                                $operation["cpo"]->BodyPreference($bptype);
                                if(!self::$decoder->getElementEndTag()) {
                                    return false;
                                }
                            }

                            if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_TRUNCATIONSIZE)) {
                                $operation["cpo"]->BodyPreference($bptype)->SetTruncationSize(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_ALLORNONE)) {
                                $operation["cpo"]->BodyPreference($bptype)->SetAllOrNone(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if(self::$decoder->getElementStartTag(SYNC_AIRSYNCBASE_PREVIEW)) {
                                $operation["cpo"]->BodyPreference($bptype)->SetPreview(self::$decoder->getElementContent());
                                if(!self::$decoder->getElementEndTag())
                                    return false;
                            }

                            if(!self::$decoder->getElementEndTag())
                                return false;//SYNC_AIRSYNCBASE_BODYPREFERENCE
                        }

                        if(self::$decoder->getElementStartTag(SYNC_MIMESUPPORT)) {
                            $operation["cpo"]->SetMimeSupport(self::$decoder->getElementContent());
                            if(!self::$decoder->getElementEndTag())
                                return false;
                        }

                        if(self::$decoder->getElementStartTag(SYNC_ITEMOPERATIONS_RANGE)) {
                            $operation["range"] = self::$decoder->getElementContent();
                            if(!self::$decoder->getElementEndTag())
                                return false;
                        }

                        if(self::$decoder->getElementStartTag(SYNC_ITEMOPERATIONS_SCHEMA)) {
                            // read schema tags
                            while (1) {
                                // TODO save elements
                                $el = self::$decoder->getElement();
                                $e = self::$decoder->peek();
                                if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                                    self::$decoder->getElementEndTag();
                                    break;
                                }
                            }
                        }

                        //break if it reached the endtag
                        $e = self::$decoder->peek();
                        if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                            self::$decoder->getElementEndTag();
                            break;
                        }
                    }
                }
            }

            if ($efc) {
                if(self::$decoder->getElementStartTag(SYNC_FOLDERID)) {
                    $operation['folderid'] = self::$decoder->getElementContent();
                    if(!self::$decoder->getElementEndTag())
                        return false;//SYNC_FOLDERID
                }
                if(self::$decoder->getElementStartTag(SYNC_ITEMOPERATIONS_OPTIONS)) {
                    if(self::$decoder->getElementStartTag(SYNC_ITEMOPERATIONS_DELETESUBFOLDERS)) {
                        $operation['deletesubfolders'] = true;
                        if (($dsf = self::$decoder->getElementContent()) !== false) {
                            $operation['deletesubfolders'] = (boolean)$dsf;
                            if(!self::$decoder->getElementEndTag())
                                return false;
                        }
                    }
                    self::$decoder->getElementEndTag();
                }
            }

            //TODO move

            if(!self::$decoder->getElementEndTag())
                return false; //SYNC_ITEMOPERATIONS_FETCH or SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS or SYNC_ITEMOPERATIONS_MOVE

            $itemoperations[] = $operation;
            //break if it reached the endtag
            $e = self::$decoder->peek();
            if($e[EN_TYPE] == EN_TYPE_ENDTAG) {
                self::$decoder->getElementEndTag(); //SYNC_ITEMOPERATIONS_ITEMOPERATIONS
                break;
            }

        }

//         if(!self::$decoder->getElementEndTag())
//             return false;//SYNC_ITEMOPERATIONS_ITEMOPERATIONS

        $status = SYNC_ITEMOPERATIONSSTATUS_SUCCESS;
        //TODO status handling

        self::$encoder->startWBXML();

        self::$encoder->startTag(SYNC_ITEMOPERATIONS_ITEMOPERATIONS);

        self::$encoder->startTag(SYNC_ITEMOPERATIONS_STATUS);
        self::$encoder->content($status);
        self::$encoder->endTag();//SYNC_ITEMOPERATIONS_STATUS

        self::$encoder->startTag(SYNC_ITEMOPERATIONS_RESPONSE);

        foreach ($itemoperations as $operation) {
            // fetch response
            if ($operation['operation'] == SYNC_ITEMOPERATIONS_FETCH) {
                self::$encoder->startTag(SYNC_ITEMOPERATIONS_FETCH);

                    self::$encoder->startTag(SYNC_ITEMOPERATIONS_STATUS);
                    self::$encoder->content($status);
                    self::$encoder->endTag();//SYNC_ITEMOPERATIONS_STATUS

                    if (isset($operation['folderid']) && isset($operation['serverid'])) {
                        self::$encoder->startTag(SYNC_FOLDERID);
                        self::$encoder->content($operation['folderid']);
                        self::$encoder->endTag(); // end SYNC_FOLDERID

                        self::$encoder->startTag(SYNC_SERVERENTRYID);
                        self::$encoder->content($operation['serverid']);
                        self::$encoder->endTag(); // end SYNC_SERVERENTRYID

                        self::$encoder->startTag(SYNC_FOLDERTYPE);
                        self::$encoder->content("Email");
                        self::$encoder->endTag();

                        self::$topCollector->AnnounceInformation("Fetching data from backend with item and folder id");

                        $data = self::$backend->Fetch($operation['folderid'], $operation['serverid'], $operation["cpo"]);
                    }

                    if (isset($operation['longid'])) {
                        self::$encoder->startTag(SYNC_SEARCH_LONGID);
                        self::$encoder->content($operation['longid']);
                        self::$encoder->endTag(); // end SYNC_FOLDERID

                        self::$encoder->startTag(SYNC_FOLDERTYPE);
                        self::$encoder->content("Email");
                        self::$encoder->endTag();

                        $tmp = explode(":", $operation['longid']);

                        self::$topCollector->AnnounceInformation("Fetching data from backend with long id");

                        $data = self::$backend->Fetch($tmp[0], $tmp[1], $operation["cpo"]);
                    }

                    if (isset($operation['filereference'])) {
                        self::$encoder->startTag(SYNC_AIRSYNCBASE_FILEREFERENCE);
                        self::$encoder->content($operation['filereference']);
                        self::$encoder->endTag(); // end SYNC_AIRSYNCBASE_FILEREFERENCE

                        self::$topCollector->AnnounceInformation("Get attachment data from backend with file reference");

                        $data = self::$backend->GetAttachmentData($operation['filereference']);
                    }

                    //TODO put it in try catch block

                    if (isset($data)) {
                        self::$topCollector->AnnounceInformation("Streaming data");

                        self::$encoder->startTag(SYNC_ITEMOPERATIONS_PROPERTIES);
                        $data->Encode(self::$encoder);
                        self::$encoder->endTag(); //SYNC_ITEMOPERATIONS_PROPERTIES
                    }

                self::$encoder->endTag();//SYNC_ITEMOPERATIONS_FETCH
            }
            // empty folder contents operation
            else if ($operation['operation'] == SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS) {
                try {
                    self::$topCollector->AnnounceInformation("Emptying folder");

                    // send request to backend
                    self::$backend->EmptyFolder($operation['folderid'], $operation['deletesubfolders']);
                }
                catch (StatusException $stex) {
                   $status = $stex->getCode();
                }

                self::$encoder->startTag(SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS);

                    self::$encoder->startTag(SYNC_ITEMOPERATIONS_STATUS);
                    self::$encoder->content($status);
                    self::$encoder->endTag();//SYNC_ITEMOPERATIONS_STATUS

                    if (isset($operation['folderid'])) {
                        self::$encoder->startTag(SYNC_FOLDERID);
                        self::$encoder->content($operation['folderid']);
                        self::$encoder->endTag(); // end SYNC_FOLDERID
                    }
                self::$encoder->endTag();//SYNC_ITEMOPERATIONS_EMPTYFOLDERCONTENTS
            }
            // TODO implement ItemOperations Move
            // move operation
            else {
                self::$topCollector->AnnounceInformation("not implemented", true);

                self::$encoder->startTag(SYNC_ITEMOPERATIONS_MOVE);
                    self::$encoder->startTag(SYNC_ITEMOPERATIONS_STATUS);
                    self::$encoder->content($status);
                    self::$encoder->endTag();//SYNC_ITEMOPERATIONS_STATUS
                self::$encoder->endTag();//SYNC_ITEMOPERATIONS_MOVE
            }

        }
        self::$encoder->endTag();//SYNC_ITEMOPERATIONS_RESPONSE
        self::$encoder->endTag();//SYNC_ITEMOPERATIONS_ITEMOPERATIONS

        return true;
    }
}
?>