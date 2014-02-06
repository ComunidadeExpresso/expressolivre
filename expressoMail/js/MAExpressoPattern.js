<!--
/* 
 * MailArchiver Expresso Pattern serialized format
 * ---
 * This resources intended to be used on converting data formats, to make
 * it work on ExpressoMail pattern.
 * 
 * Basicly, it gets all the service return data (almost enveloped at SOAP 
 * webservices spec i.e XML data), and then transform it to the serialized
 * pattern used by Expresso (somethink almost close or near JSON Javascript 
 * objects).
 * 
 * @author Fernando Wendt [fernando-alberto.wendt@serpro.gov.br]
 *
 * @status under development
 * 
 */
var MAPattern = function(){
    
}
// -----------------------------------SERIALIZED DATA TEMPLATES ----------------
MAPattern.prototype.toExpressoAddress = function(data){   
    if((typeof(data) != 'undefined') && (data.length > 0)){

        if((typeof(data) != 'string') || (data.indexOf("{") == -1)){
            var objMsg = expresso_mail_archive.getMessageServiceObject(data);
            var attData = objMsg.getTo();
            var Fromdata = attData ? eval("(" + attData + ")") : null;
            if (Fromdata == null)
                return('N;');
        }
        else{
            var Fromdata = data ?  eval("(" + data + ")") : null;
            if (Fromdata == null){
                return('N;');     
            }
            else{
                if((!Fromdata.mailbox) && (!Fromdata.groupbox)){
                    var nofrom = get_lang('Sender not identified');
                    return('a:2{s:4:"name";s:' + nofrom.length + ':"' + nofrom + '";s:5:"email";s:'+nofrom.length +':"'+nofrom +'";}');
                }
            }
        }       
        
        //var Fromdata = eval("(" + data + ")");
        if(Fromdata.mailbox){
            //just one address to pharse: length is undefined
            if(typeof(Fromdata.mailbox.length) == 'undefined'){
                var objfrom = Fromdata.mailbox;
                flag1 = 1;
            }
            else{
                var objfrom = Fromdata.mailbox[0];
                flag1 = 2;
            }
            
            if(objfrom["@name"] != 'null')
                var dataName = (objfrom["@name"]);
            else
                var dataName = "";
                
            if(objfrom["@localPart"] != 'null')
                var dataEmailPart = (objfrom["@localPart"]);
            else 
                var dataEmailpart = "";
                
            if(objfrom["@domain"] != 'null')
                var dataEmailDomain = (objfrom["@domain"]);
            else
                var dataEmailDomain = "";
                
            if(dataEmailDomain != "")
                var dataEmail =  dataEmailPart + '@' + dataEmailDomain;
            else
                var dataEmail =  dataEmailPart;
                                
            if(dataName == '')
                dataName = dataEmail;
                
            var dataTmp = '\"'+dataName+'\"&lt;'+dataEmail+'&gt;'
            var tplFrom = 'a:3:{'
+                         's:4:"name";'
+                         's:'+dataName.length+':"'+dataName+'";'
+                         's:5:"email";'
+                         's:'+dataEmail.length+':"'+dataEmail+'";'
+                         's:4:"full";'
+                         's:'+(dataTmp.length)+':"'+dataTmp+'";'
+                         '}';                
        }
        else{
            if(Fromdata.group){
                //just one address to pharse: length is undefined
                if(typeof(Fromdata.group.length) == 'undefined'){
                    if(Fromdata.group["@name"]){
                        var dataName = Fromdata.group["@name"];
                    }
                    else
                        var dataName = null;
                    
                    if(Fromdata.group["@localPart"]){
                        var dataEmail = Fromdata.group["@localPart"] + '@' + Fromdata.group["@domain"];
                    }
                    else
                        var dataEmail = null;
                    
                    if(dataName == 'null')
                        dataName = dataEmail;
                }
                //address list to pharse, but to header(folder list messages action), only first metters
                else{
                    var dataName = Fromdata.group[0]["@name"];
                    var dataEmail = Fromdata.group[0]["@localPart"] + '@' + Fromdata.group[0]["@domain"];
                    if(dataName == 'null')
                        dataName = dataEmail;
                }
            
            if((dataName != null) && (dataEmail != null)){
                var dataTmp = '\"'+dataName+'\"&lt;'+dataEmail+'&gt;'
                var tplFrom = 'a:3:{'
+                         's:4:"name";'
+                         's:'+dataName.length+':"'+dataName+'";'
+                         's:5:"email";'
+                         's:'+dataEmail.length+':"'+dataEmail+'";'
+                         's:4:"full";'
+                         's:'+(dataTmp.length)+':"'+dataTmp+'";'
+                         '}';               
            }
            else{
                if(dataName!= null){
                var tplFrom = 'a:2:{'
+                         's:4:"name";'
+                         's:'+dataName.length+':"'+dataName+'";'
+                         's:4:"full";'
+                         's:'+(dataName.length)+':"'+dataName+'";'
+                         '}';                     
                }
                else{
                    if(dataEmail != null){
                    var tplFrom = 'a:2:{'
+                         's:4:"email";'
+                         's:'+dataEmail.length+':"'+dataEmail+'";'
+                         's:4:"full";'
+                         's:'+(dataEmail.length)+':"'+dataEmail+'";'
+                         '}';                         
                    }
                    else tplFrom = 'a:2{s:4:"name";s:14:"Unknow mailbox";s:5:"email";s:15:"Unknow_mailbox";}';
                }
            }
           }   
           //Data adrress from mailbox is empty, null, or canot be correctly returned by service invocation (will return 'unknow mailbox' string)
           else {
                tplFrom = 'N;';
           }       
        }
    }
    else{
        tplFrom = 'N;';
    }
    return(tplFrom);
}

MAPattern.prototype.toExpressoCc = function(data){
    if((typeof(data) != 'undefined') && (data.length > 0)){
        var Fromdata = eval("(" + data + ")");
        if(Fromdata.mailbox){
        //just one address to pharse: length is undefined
            if(typeof(Fromdata.mailbox.length) == 'undefined'){
                var dataName = Fromdata.mailbox["@name"];
                var dataEmail = Fromdata.mailbox["@localPart"] + '@' + Fromdata.mailbox["@domain"];
                if (dataName == 'null')
                    var dataFull = 's:'+(dataEmail.length)+':"'+dataEmail+'";';
                else{
                    var dataTmp = '\"'+dataName+'\"&lt;'+dataEmail+'&gt;'
                    var dataFull = 's:'+(dataTmp.length)+':"'+dataTmp+'";'
                }
            }
            //address list to pharse, but to header(folder list messages action), only first metters
            else{
                    var dataTmp = '';
                    for( i in Fromdata.mailbox){
                        var dataName = Fromdata.mailbox[i]["@name"];
                        var dataEmail = Fromdata.mailbox[i]["@localPart"] + '@' + Fromdata.mailbox[i]["@domain"];
                        if (dataName == 'null')
                             dataTmp = dataTmp + dataEmail+',';
                        else{
                             dataTmp = dataTmp + '\"'+dataName+'\"&lt;'+dataEmail+'&gt;,';
                            }
                }
                dataTmp = dataTmp.substring(0,(dataTmp.length - 1));
                 var dataFull = 's:'+(dataTmp.length)+':"'+dataTmp+'";'

            }

            var tplCc = dataFull;

        }
        else {
            tplCc = 'N;';
        }
    }
    //Data adrress from mailbox is empty, null, or canot be correctly returned by service invocation (will return 'unknow mailbox' string)
    else {
        tplCc = 'N;';
    }
    
    return(tplCc);
    
}

MAPattern.prototype.toExpressoDispositionNotificationTo = function(data){
    var objMsg = expresso_mail_archive.getMessageServiceObject(data);
    var dntData = objMsg.getDispositionNotificationTo();

    if((typeof(dntData) != 'undefined') && (dntData.length > 0)){
        var notificationData = eval("(" + dntData + ")");
        if(notificationData.mailbox){
            //just one address to pharse: length is undefined
            if(typeof(notificationData.mailbox.length) == 'undefined'){
                var dataEmail = notificationData.mailbox["@localPart"] + '@' + notificationData.mailbox["@domain"];
            }
            //address list to parse, but to header(folder list messages action), only first matters
            else{
                var dataEmail = notificationData.mailbox[0]["@localPart"] + '@' + notificationData.mailbox[0]["@domain"];
            }

            var tplNotification = 's:25:"DispositionNotificationTo";s:' + dataEmail.length + ':"' + dataEmail + '";'
        }
        //Data adrress from mailbox is empty, null, or canot be correctly returned by service invocation (will return 'unknow mailbox' string)
        else {
            tplNotification = 's:25:"DispositionNotificationTo";N;';
        }
    }
    else {
        tplNotification = 's:25:"DispositionNotificationTo";N;';
    }

    return tplNotification;
}

MAPattern.prototype.toExpressoAddress2 = function(data){
    if((typeof(data) != 'undefined') && (data.length > 0)){
        if((typeof(data) != 'string') || (data.indexOf("{") == -1)){
            var objMsg = expresso_mail_archive.getMessageServiceObject(data);
            var attData = objMsg.getTo();
            var Fromdata = attData ? eval("(" + attData + ")") : null;
            if (Fromdata == null){                
                var noto = get_lang("without destination");
                return('s:10:"toaddress2";s:' + noto.length + ':"'+noto+'";');
            }
        }
        else{
            var Fromdata = data ?  eval("(" + data + ")") : null;
            if (Fromdata == null)
                return('s:10:"toaddress2";N;');                        
        }       
       
        if(Fromdata && Fromdata.mailbox){
            //just one address to pharse: length is undefined
            if(typeof(Fromdata.mailbox.length) == 'undefined'){
                var dataEmail = Fromdata.mailbox["@localPart"] + '@' + Fromdata.mailbox["@domain"];
            }
            //address list to pharse, but to header(folder list messages action), only first metters
            else{
                var datatmp = "";
                for(var k=0; k<Fromdata.mailbox.length; k++){                    
                    if(Fromdata.mailbox[k]["@name"] != 'null'){
                        datatmp += '"' + Fromdata.mailbox[k]["@name"] + '" ';
                        if(Fromdata.mailbox[k]["@localPart"] != 'null')
                            datatmp += '&lt;' + Fromdata.mailbox[k]["@localPart"];
                        if(Fromdata.mailbox[k]["@domain"] != 'null')
                            datatmp += '@' + Fromdata.mailbox[k]["@domain"] + '&gt;';                    
                        else
                            datatmp += '&gt;';
                    }
                    else{
                        if(Fromdata.mailbox[k]["@localPart"] != 'null')
                            datatmp += Fromdata.mailbox[k]["@localPart"];
                        if(Fromdata.mailbox[k]["@domain"] != 'null')
                            datatmp += '@' + Fromdata.mailbox[k]["@domain"];
                    }
                    if(datatmp != '')
                        datatmp += ', ';
                }
                datatmp = datatmp.substr(0, datatmp.length -2);
                var dataEmail = datatmp;
                
                //var dataEmail = Fromdata.mailbox[0]["@localPart"] + '@' + Fromdata.mailbox[0]["@domain"];
            }

            //var tplAddr2 = 's:10:"toaddress2";s:' + dataEmail.length + ':"' + dataEmail + '";'
            var tplAddr2 = 's:10:"toaddress2";s:' + dataEmail.length + ':"' + dataEmail + '";'
        }
        else{
            if(Fromdata && Fromdata.group){
                //just one address group to pharse
                if(typeof(Fromdata.group.length) == 'undefined'){   
                    //there is a group email identifier
                    if(Fromdata.group['@localPart']){
                        var dataEmail = Fromdata.group["@localPart"] + '@' + Fromdata.group["@domain"];
                    }
                    //there is no email identifier, will try by name
                    else{
                        if(Fromdata.group['@name']){
                            var dataEmail = '"' + Fromdata.group['@name'] + '"';                        
                        }
                        else{
                            dataEmail = 'N';
                        }
                    } 

                }
                //group address list to pharse, but to header(folder list messages action), only first metters
                else{
                    var dataEmail = Fromdata.group[0]["@localPart"] + '@' + Fromdata.group[0]["@domain"];
                }

                var tplAddr2 = 's:10:"toaddress2";s:' + dataEmail.length + ':"' + dataEmail + '";'                
            }
            //Data adrress from mailbox is empty, null, or canot be correctly returned by service invocation (will return 'unknow mailbox' string)
            else {
                tplAddr2 = 's:10:"toaddress2";s:1:"N";';
            }
        }

    }
    else{
        //tplAddr2 = 's:10:"toaddress2";s:1:"";';
        var noto = get_lang("without destination");
        tplAddr2 = 's:10:"toaddress2";s:' + noto.length + ':"'+noto+'";';
    }
    return(tplAddr2);
}


MAPattern.prototype.toExpressoSender = function(data){
    if((typeof(data) != 'undefined') && (data.length > 0)){
        if((typeof(data) != 'string') || (data.indexOf("{") == -1)){
            var objMsg = expresso_mail_archive.getMessageServiceObject(data);
            var attData = objMsg.getSender();
            var Fromdata = attData ? eval("(" + attData + ")") : null;
            if (Fromdata == null)
                return('s:6:"sender";N;');
        }
        else{
            var Fromdata = data ?  eval("(" + data + ")") : null;
            if (Fromdata == null)
                return('s:6:"sender";N;');                        
        }       
        
        //var Fromdata = eval("(" + data + ")");
        if(Fromdata.mailbox){
            //just one address to pharse: length is undefined
            if(typeof(Fromdata.mailbox.length) == 'undefined'){
                var objfrom = Fromdata.mailbox;
                flag1 = 1;
            }
            else{
                var objfrom = Fromdata.mailbox[0];
                flag1 = 2;
            }
            
            if(objfrom["@name"] != 'null')
                var dataName = (objfrom["@name"]);
            else
                var dataName = "";
                
            if(objfrom["@localPart"] != 'null')
                var dataEmailPart = (objfrom["@localPart"]);
            else 
                var dataEmailpart = "";
                
            if(objfrom["@domain"] != 'null')
                var dataEmailDomain = (objfrom["@domain"]);
            else
                var dataEmailDomain = "";
                
            if(dataEmailDomain != "")
                var dataEmail =  dataEmailPart + '@' + dataEmailDomain;
            else
                var dataEmail =  dataEmailPart;
                
            if(dataName == '')
                dataName = dataEmail;
                
            var dataTmp = '\"'+dataName+'\"&lt;'+dataEmail+'&gt;'
            
            var tplFrom = 'a:3:{'
+                         's:4:"name";'
+                         's:'+dataName.length+':"'+dataName+'";'
+                         's:5:"email";'
+                         's:'+dataEmail.length+':"'+dataEmail+'";'
+                         's:4:"full";'
+                         's:'+(dataTmp.length)+':"'+dataTmp+'";'
+                         '}';                
            }
            else{
                if(Fromdata.group){
                    //just one address to pharse: length is undefined
                    if(typeof(Fromdata.group.length) == 'undefined'){
                        if(Fromdata.group["@name"]){
                            var dataName = Fromdata.group["@name"];
                        }
                        else
                            var dataName = null;
                    
                        if(Fromdata.group["@localPart"]){
                            var dataEmail = Fromdata.group["@localPart"] + '@' + Fromdata.group["@domain"];
                        }
                        else
                            var dataEmail = null;
                    
                        if(dataName == 'null')
                            dataName = dataEmail;
                    }
                    //address list to pharse, but to header(folder list messages action), only first metters
                    else{
                        var dataName = Fromdata.group[0]["@name"];
                        var dataEmail = Fromdata.group[0]["@localPart"] + '@' + Fromdata.group[0]["@domain"];
                        if(dataName == 'null')
                            dataName = dataEmail;
                    }
            
                if((dataName != null) && (dataEmail != null)){
                    var dataTmp = '\"'+dataName+'\"&lt;'+dataEmail+'&gt;'
                    var tplFrom = 'a:3:{'
+                         's:4:"name";'
+                         's:'+dataName.length+':"'+dataName+'";'
+                         's:5:"email";'
+                         's:'+dataEmail.length+':"'+dataEmail+'";'
+                         's:4:"full";'
+                         's:'+(dataTmp.length)+':"'+dataTmp+'";'
+                         '}';               
                }
                else{
                    if(dataName!= null){
                    var tplFrom = 'a:2:{'
+                         's:4:"name";'
+                         's:'+dataName.length+':"'+dataName+'";'
+                         's:4:"full";'
+                         's:'+(dataName.length)+':"'+dataName+'";'
+                         '}';                     
                    }
                    else{
                        if(dataEmail != null){
                        var tplFrom = 'a:2:{'
+                         's:4:"email";'
+                         's:'+dataEmail.length+':"'+dataEmail+'";'
+                         's:4:"full";'
+                         's:'+(dataEmail.length)+':"'+dataEmail+'";'
+                         '}';                         
                        }
                        else tplFrom = 'a:2{s:4:"name";s:14:"Unknow mailbox";s:5:"email";s:15:"Unknow_mailbox";}';
                    }
                }
            }   
           else {
                tplFrom = 'N;';
           }       
        }
    }
    else{
        tplFrom = 'N;';
    }
    return('s:6:"sender";' + tplFrom);
}

MAPattern.prototype.toExpressoReplyTo = function(data){
    if((typeof(data) != 'undefined') && (data.length > 0)){

        if((typeof(data) != 'string') || (data.indexOf("{") == -1)){
            var objMsg = expresso_mail_archive.getMessageServiceObject(data);
            var attData = objMsg.getReplyTo();
            var Fromdata = attData ? eval("(" + attData + ")") : null;
            if (Fromdata == null)
                return('s:8:"reply_to";N;');            
        }
        else{
            var Fromdata = data ?  eval("(" + data + ")") : null;
            if (Fromdata == null)
                return('s:8:"reply_to";N;');                        
        }       
        
        //var Fromdata = eval("(" + data + ")");        
        if(Fromdata.mailbox){
            //just one address to pharse: length is undefined
            if(typeof(Fromdata.mailbox.length) == 'undefined'){
                var objfrom = Fromdata.mailbox;
                flag1 = 1;
            }
            else{
                var objfrom = Fromdata.mailbox[0];
                flag1 = 2;
            }
            
            if(objfrom["@name"] != 'null')
                var dataName = (objfrom["@name"]);
            else
                var dataName = "";
                
            if(objfrom["@localPart"] != 'null')
                var dataEmailPart = (objfrom["@localPart"]);
            else 
                var dataEmailpart = "";
                
            if(objfrom["@domain"] != 'null')
                var dataEmailDomain = (objfrom["@domain"]);
            else
                var dataEmailDomain = "";
                
            if(dataEmailDomain != "")
                var dataEmail =  dataEmailPart + '@' + dataEmailDomain;
            else
                var dataEmail =  dataEmailPart;
                
            if(dataName == '')
                tplFrom = dataEmail;
            else
                tplFrom = '\"'+dataName+'\" &lt;'+dataEmail+'&gt;'                
            
           
            //tplFrom = dataEmail;
            
            }
            else{
                if(Fromdata.group){
                    //just one address to pharse: length is undefined
                    if(typeof(Fromdata.group.length) == 'undefined'){
                        if(Fromdata.group["@name"]){
                            var dataName = Fromdata.group["@name"];
                        }
                        else
                            var dataName = null;
                    
                        if(Fromdata.group["@localPart"]){
                            var dataEmail = Fromdata.group["@localPart"] + '@' + Fromdata.group["@domain"];
                        }
                        else
                            var dataEmail = null;
                    
                        if(dataName == 'null')
                            tplFrom = dataEmail;
                        else
                            tplFrom = '\"'+dataName+'\" &lt;'+dataEmail+'&gt;'    
                        
                    }
                    //address list to pharse, but to header(folder list messages action), only first metters
                    else{
                        var dataName = Fromdata.group[0]["@name"];
                        var dataEmail = Fromdata.group[0]["@localPart"] + '@' + Fromdata.group[0]["@domain"];
                        
                        if(dataName == 'null')
                            tplFrom = dataEmail;
                        else
                            tplFrom = '\"'+dataName+'\" &lt;'+dataEmail+'&gt;'    
                    }
                }   
                //Data adrress from mailbox is empty, null, or canot be correctly returned by service invocation (will return 'unknow mailbox' string)
                else {
                    tplFrom = 'N;';
                    return('');
                }       
            }
        }
        else{
            tplFrom = 'N;';
            return('')
        }
        return('s:8:"reply_to";s:' + tplFrom.length + ':"' + tplFrom + '";');
}

MAPattern.prototype.toExpressoSearchFrom = function(data){
    if((typeof(data) != 'undefined') && (data.length > 0)){

        if((typeof(data) != 'string') || (data.indexOf("{") == -1)){
            var objMsg = expresso_mail_archive.getMessageServiceObject(data);
            var attData = objMsg.getFrom();
            var Fromdata = attData ? eval("(" + attData + ")") : null;
            if (Fromdata == null)
                return('N;');
        }
        else{
            var Fromdata = data ?  eval("(" + data + ")") : null;
            if (Fromdata == null)
                return('N;');            
        }       
        
        //var Fromdata = eval("(" + data + ")");
        if(Fromdata.mailbox){
            //just one address to pharse: length is undefined
            if(typeof(Fromdata.mailbox.length) == 'undefined'){
                var objfrom = Fromdata.mailbox;
            }
            else{
                var objfrom = Fromdata.mailbox[0];
            }
            
            if(objfrom["@name"] != 'null')
                var tplSearchFrom = (objfrom["@name"]);
            else{                
                if(objfrom["@localPart"] != 'null')
                   var dataEmailPart = (objfrom["@localPart"]);
                else 
                   var dataEmailpart = "";
                
                if(objfrom["@domain"] != 'null')
                    var dataEmailDomain = (objfrom["@domain"]);
                else
                    var dataEmailDomain = "";
                
                if(dataEmailDomain != "")
                    var tplSearchFrom =  dataEmailPart + '@' + dataEmailDomain;
                else
                    var tplSearchFrom =  dataEmailPart;
            }
        }
        else{
            if(Fromdata.group){
                //just one address to pharse: length is undefined
                if(typeof(Fromdata.group.length) == 'undefined'){
                    if(Fromdata.group["@name"]){
                        var tplSearchFrom = Fromdata.group["@name"];
                    }
                    else{
                    
                        if(Fromdata.group["@localPart"]){
                            var tplSearchFrom = Fromdata.group["@localPart"] + '@' + Fromdata.group["@domain"];
                        }
                        else
                            var tplSearchFrom = null;
                    }
                    
                }
                //address list to pharse, but to header(folder list messages action), only first metters
                else{
                    var dataName = Fromdata.group[0]["@name"];
                    var dataEmail = Fromdata.group[0]["@localPart"] + '@' + Fromdata.group[0]["@domain"];
                    if(dataName == 'null')
                        var tplSearchFrom = dataEmail;
                    else
                        var tplSearchFrom = dataName;
                }
            
        }
    }
    }
    else{
        var tplSearchFrom = 'N;';
    }
    //window.alert('from search = ' + tplSearchFrom);
    return(tplSearchFrom);
}


MAPattern.prototype.toExpressoSubject = function(data){
    if((typeof(data) != 'undefined') && (data.length > 0)){
        var tplSbj = 's:'+data.length+':"'+data+'";'
    }
    //Subject is empty, null, or canot be correctly returned by service invocation (will return 'Message without subject' string)
    else {
        tplSbj = 's:25:"[Message without subject]";';
    }
    return(tplSbj);
}

MAPattern.prototype.toExpressoPreview = function(data){
    //var objMsg = expresso_mail_archive.getMessageServiceObject(data);
    var prwData = data;
    
    
    if(prwData){
        if((typeof(prwData) != 'undefined') && (prwData.length > 0)){
            var tplSbj = 's:'+prwData.length+':"'+prwData+'";'
        }
        else {
            tplSbj = 's:1:" ";';
        }
    }
    else
        tplSbj = 's:1:" ";';
    return(tplSbj);
}

MAPattern.prototype.toExpressoAttachmentsHeader = function(data){
    if((typeof(data) != 'undefined') && (data.length > 2)){
        var Attdata = eval("(" + data + ")");
        if(typeof(Attdata.attachment) != 'undefined'){                    
            var tplAttatch = 'a:2:{'
+               's:5:"names";';
            //More than 1 attachment - because length will be 'undefined' if just one exists
            if(Attdata.attachment.length > 0){
                var strnamesatt = "";
                for(var x=0; x<Attdata.attachment.length; x++){
                  var attId = Attdata.attachment[x]["@id"];
                  var attName = Attdata.attachment[x]["@name"];
                  var attSize = Attdata.attachment[x]["@size"];
                  var attMediaType = Attdata.attachment[x]["@mediaType"];
                  var attSubType = Attdata.attachment[x]["@subType"];
                  var attEncoding = Attdata.attachment[x]["@encoding"];
                  
                  strnamesatt += attName + ', ';
                }
                strnamesatt = strnamesatt.substr(0, strnamesatt.length-2);
                var intnumberatt = Attdata.attachment.length;
            }
            //Just one attachment
            else{
                var attId = Attdata.attachment["@id"];
                var attName = Attdata.attachment["@name"];
                var attSize = Attdata.attachment["@size"];
                var attMediaType = Attdata.attachment["@mediaType"];
                var attSubType = Attdata.attachment["@subType"];
                var attEncoding = Attdata.attachment["@encoding"];
                strnamesatt = attName;
                var intnumberatt = 1;
            }
            
            tplAttatch += 's:'+strnamesatt.length+':"'+strnamesatt+'";'
+               's:18:"number_attachments";i:' +intnumberatt+';'
+           '}';
        }
        else {
            tplAttatch = 'a:2:{s:5:"names";b:0;s:18:"number_attachments";i:0;}';    
        }
    }
    //No attatch data
    else {
        tplAttatch = 'a:2:{s:5:"names";b:0;s:18:"number_attachments";i:0;}';
    }
    return(tplAttatch);
}

MAPattern.prototype.toExpressoDate = function(data){
    var ndate = new Date(data);
    var odate = zeroFill(ndate.getDate()) + '/' + zeroFill(ndate.getMonth()+1) + '/' + zeroFill(ndate.getFullYear() + ' ' + zeroFill(ndate.getHours()) + ':' + zeroFill(ndate.getMinutes()) + ':' + zeroFill(ndate.getSeconds()));
    return(odate);
}

MAPattern.prototype.toExpressoFlags = function(data){
    if((data.length > 0) && (typeof(data) != 'undefined')){
        var Tagdata = eval("(" + data + ")");
        if(typeof(Tagdata.tag) != 'undefined'){ 
        //More than 1 attachment: length will be 'undefined' if just one exists
        if(Tagdata.tag.length >= 0){
            var strnamestag = "";
            for(var x=0; x<Tagdata.tag.length; x++){
                var tagvalue = Tagdata.tag[x]["@value"];
                if(tagvalue.toLowerCase() == 'recent')
                    strnamestag += 's:6:"Recent";s:1:"N";';
                if(tagvalue.toLowerCase() == 'unseen')
                    strnamestag += 's:6:"Unseen";s:1:"U";';
                if(tagvalue.toLowerCase() == 'seen')
                    strnamestag += 's:6:"Unseen";s:1:" ";';                
                if(tagvalue.toLowerCase() == 'deleted')
                    strnamestag += 's:7:"Deleted";s:1:"D";';
                if(tagvalue.toLowerCase() == 'answered')
                    strnamestag += 's:8:"Answered";s:1:"A";';
                if(tagvalue.toLowerCase() == 'unanswered')
                    strnamestag += 's:8:"Answered";s:1:" ";';                
                if(tagvalue.toLowerCase() == 'forwarded')
                    strnamestag += 's:9:"Forwarded";s:1:"F";';
                if(tagvalue.toLowerCase() == 'draft')
                    strnamestag += 's:5:"Draft";s:1:"X";';
                if(tagvalue.toLowerCase() == 'importance_high')
                    strnamestag += 's:10:"Importance";s:4:"High";';
                if(tagvalue.toLowerCase() == 'flagged')
                    strnamestag += 's:7:"Flagged";s:1:"F";';
                if(tagvalue.toLowerCase() == 'unflagged')
                    strnamestag += 's:7:"Flagged";b:0;';                
            } 
            var tplTags = strnamestag; 
          }
          
          else{
            var strnamestag = "";
            var tagvalue = Tagdata.tag["@value"];
                if(tagvalue.toLowerCase() == 'recent')
                    strnamestag += 's:6:"Recent";s:1:"N";';
                if(tagvalue.toLowerCase() == 'unseen')
                    strnamestag += 's:6:"Unseen";s:1:"U";';
                if(tagvalue.toLowerCase() == 'seen')
                    strnamestag += 's:6:"Unseen";s:1:" ";';                
                if(tagvalue.toLowerCase() == 'deleted')
                    strnamestag += 's:7:"Deleted";s:1:"D";';
                if(tagvalue.toLowerCase() == 'answered')
                    strnamestag += 's:8:"Answered";s:1:"A";';
                if(tagvalue.toLowerCase() == 'unanswered')
                    strnamestag += 's:8:"Answered";s:1:" ";';                
                if(tagvalue.toLowerCase() == 'forwarded')
                    strnamestag += 's:9:"Forwarded";s:1:"F";';
                if(tagvalue.toLowerCase() == 'draft')
                    strnamestag += 's:5:"Draft";s:1:"X";';
                if(tagvalue.toLowerCase() == 'importance_high')
                    strnamestag += 's:10:"Importance";s:4:"High";';
                if(tagvalue.toLowerCase() == 'flagged')
                    strnamestag += 's:7:"Flagged";s:1:"F";';
                if(tagvalue.toLowerCase() == 'unflagged')
                    strnamestag += 's:7:"Flagged";b:0;';   
            var tplTags = strnamestag;
          }
      }
      //Something is wrong with tags: return defaults is no one tag
      else{
        tplTags = 's:6:"Recent";s:1:" ";s:6:"Unseen";s:1:" ";s:7:"Deleted";s:1:" ";s:8:"Answered";s:1:" ";s:9:"Forwarded";s:1:" ";s:5:"Draft";s:1:" ";s:10:"Importance";s:6:"Normal";s:7:"Flagged";b:0;';
      }
      return(tplTags);
    }
}

MAPattern.prototype.toExpressoSearchFlags = function(data){
    if((data.length > 0) && (typeof(data) != 'undefined')){
        var Tagdata = eval("(" + data + ")");
        if(typeof(Tagdata.tag) != 'undefined'){ 
        //More than 1 attachment: length will be 'undefined' if just one exists
        var tflagged = false;
        if(Tagdata.tag.length >= 0){
            var strnamestag = '';
            for(var x=0; x<Tagdata.tag.length; x++){
                var tagvalue = Tagdata.tag[x]["@value"];
                if(tagvalue.toLowerCase() == 'recent')
                    strnamestag += 'N';
                if(tagvalue.toLowerCase() == 'unseen')
                    strnamestag += 'U';
                if(tagvalue.toLowerCase() == 'draft')
                    strnamestag += 'X';
                if(tagvalue.toLowerCase() == 'answered')
                    strnamestag += 'A';
                if(tagvalue.toLowerCase() == 'flagged')
                    tflagged = true;
                if((tagvalue.toLowerCase() == 'importance_high') || tflagged)
                        strnamestag += 'F';
            } 
            var tplTags = strnamestag; 
          }
          
          else{
            var strnamestag = "";
            var tflagged = false;
            var tagvalue = Tagdata.tag["@value"];
                if(tagvalue.toLowerCase() == 'recent')
                    strnamestag += 'N';
                if(tagvalue.toLowerCase() == 'unseen')
                    strnamestag += 'U';
                if(tagvalue.toLowerCase() == 'draft')
                    strnamestag += 'X';
                /*if(tagvalue.toLowerCase() == 'importance_high')
                    strnamestag += 'F';
                if(tagvalue.toLowerCase().indexOf('flagged')!= -1)
                    strnamestag += 'F';*/
                if(tagvalue.toLowerCase() == 'flagged')
                    tflagged = true;
                if((tagvalue.toLowerCase() == 'importance_high') && tflagged)
                        strnamestag += 'F';
            var tplTags = strnamestag;
          }
          var tplSearchTags = 's:4:"flag";s:' + tplTags.length + ':"' + tplTags + '";'
      }
      //Something is wrong with tags: return defaults is no one tag
      else{
        var tplSearchTags = 's:4:"flag";s:1:" ";';
      }
      //window.alert('tags to search data\n' + tplSearchTags);
      return(tplSearchTags);
    }
}

MAPattern.prototype.toExpressoHeader = function(data){   
    var tplHeader = 'a:40:{'
+           's:11:"ContentType";'
+           's:' + data.getContentType().length + ':"' + data.getContentType() + '";'
+           expresso_mail_archive.pattern.toExpressoFlags(data.getTags())
+           's:10:"msg_number";'
+           's:'+data.getId().length+':"'+data.getId()+'";'
+           's:5:"udate";'
+           'i:' + parseInt(data.getDate()/1000) + ';'
+           's:9:"timestamp";'
+           'i:' + parseInt(data.getDate()/1000) + ';'
+           's:4:"from";'
+           expresso_mail_archive.pattern.toExpressoAddress(data.getFrom())
//+           's:6:"sender":' xpresso_mail_archive.pattern.toExpressoSender(expresso_mail_archive.currentmessage)
+           's:2:"to";'
+           expresso_mail_archive.pattern.toExpressoAddress(data.getTo())
+           expresso_mail_archive.pattern.toExpressoAddress2(data.getTo())
+           's:2:"cc";'
+           expresso_mail_archive.pattern.toExpressoCc(data.getCc())
+           's:3:"bcc";'
+           expresso_mail_archive.pattern.toExpressoCc(data.getBcc())
+           's:7:"subject";'
+           expresso_mail_archive.pattern.toExpressoSubject(data.getSubject())
+           's:4:"Size";'
+           'i:'+data.getSize()+';'
+           's:10:"msg_sample";' + expresso_mail_archive.pattern.toExpressoPreview(data.getPreview())
+           's:10:"attachment";'
+           expresso_mail_archive.pattern.toExpressoAttachmentsHeader(data.getAttachments())
+           '}';
    return(tplHeader);
}

MAPattern.prototype.toExpressoSearchHeader = function(data){   
    //var accid = new String(account_id);
    var accid = new String(data.getId());
    //var fdata = data.getFolderId();
    var fdata = data.getFolderPath();
    var fname = fdata;
       
    var tplHeader = 'a:16:{'
+           's:11:"ContentType";'
+           's:' + data.getContentType().length + ':"' + data.getContentType() + '";'
+           expresso_mail_archive.pattern.toExpressoSearchFlags(data.getTags())
+           's:10:"msg_number";'
+           's:'+data.getId().length+':"'+data.getId()+'";'
+           's:5:"udate";'
+           'i:' + parseInt(data.getDate()/1000) + ';'
+           's:9:"timestamp";'
+           'i:' + parseInt(data.getDate()/1000) + ';'
+           's:4:"from";'
+           's:' + expresso_mail_archive.pattern.toExpressoSearchFrom(data.getFrom()).length + ':"' + expresso_mail_archive.pattern.toExpressoSearchFrom(data.getFrom()) + '";'
//+           's:6:"sender":' expresso_mail_archive.pattern.toExpressoSender(expresso_mail_archive.currentmessage)
+           's:2:"to";'
+           expresso_mail_archive.pattern.toExpressoAddress(data.getTo())
+           expresso_mail_archive.pattern.toExpressoAddress2(data.getTo())
+           's:2:"cc";'
+           expresso_mail_archive.pattern.toExpressoCc(data.getCc())
+           's:3:"bcc";'
+           expresso_mail_archive.pattern.toExpressoCc(data.getBcc())
+           's:7:"subject";'
+           expresso_mail_archive.pattern.toExpressoSubject(data.getSubject())
+           's:4:"size";'
+           'i:'+data.getSize()+';'
+           's:3:"uid";'
+           's:'+ accid.length + ':"' + accid + '";'
+           's:7:"boxname";'
+           's:'+(fname.length + 6)+':"local_' + fname + '";'
+           's:10:"msg_sample";' + expresso_mail_archive.pattern.toExpressoPreview(data.getPreview())
+           's:10:"attachment";'
+           expresso_mail_archive.pattern.toExpressoAttachmentsHeader(data.getAttachments())
+           '}';

    //window.alert('return from TplSearchHeader:\n' + tplHeader);
    
    return(tplHeader);
}

/*
MAPattern.prototype.toExpressoBody = function(mheader){
    var tplBody = 'a:33:{'
+                   's:4:"body";N;'
+                   's:11:"attachments";N;'
+                   's:6:"thumbs";N;'
+                   's:9:"signature";N;'
+                   's:10:"Importance";s:6:"Normal";'
+                   's:6:"Recent";s:1:" ";'
+                   's:6:"Unseen";s:1:"N";'
+                   's:7:"Deleted";s:1:" ";'
+                   's:7:"Flagged";b:0;'
+                   's:8:"Answered";s:1:" ";'
+                   's:5:"Draft";s:1:" ";'
+                   's:10:"msg_number";s:4:"2241";'
+                   's:10:"msg_folder";s:5:"INBOX";'
+                   's:5:"udate";s:5:"10:01";'
+                   's:7:"msg_day";s:10:"10/05/2011";'
+                   's:8:"msg_hour";s:5:"10:02";'
+                   's:8:"fulldate";s:36:"10/05/2011 10:01 (05:35 horas atras)";'
+                   's:9:"smalldate";s:5:"10:04";'
+                   's:4:"from";N;'
+                   's:6:"sender";a:3:{'
+                       's:4:"name";N;'
+                       's:5:"email";s:46:"listaregional-pae-bounces@grupos.serpro.gov.br";'
+                       's:4:"full";s:46:"listaregional-pae-bounces@grupos.serpro.gov.br";'
+                   '}'
+                   's:10:"toaddress2";s:32:"comunicacao.social@serpro.gov.br";'
+                   's:2:"cc";N;'
+                   's:3:"bcc";N;'
+                   's:8:"reply_to";N;'
+                   's:7:"subject";N;'
+                   's:4:"Size";s:4:"1234";'
+                   's:15:"reply_toaddress";s:45:"com.social <comunicacao.social@serpro.gov.br>";'
+                   's:9:"timestamp";i:1305032476;'
+                   's:5:"login";i:944889240;'
+                   's:6:"header";a:16:{'
+                      's:11:"ContentType";s:6:"normal";'
+                      's:10:"Importance";s:6:"Normal";'
+                      's:6:"Recent";s:1:" ";'
+                      's:6:"Unseen";s:1:"N";'
+                      's:8:"Answered";s:1:" ";'
+                      's:5:"Draft";s:1:" ";'
+                      's:7:"Deleted";s:1:" ";'
+                      's:7:"Flagged";b:0;'
+                      's:10:"msg_number";s:4:"2251";'
+                      's:5:"udate";s:10:"10/05/2011";'
+                      's:11:"offsetToGMT";i:-10800;'
+                      's:8:"aux_date";N;'
+                      's:4:"from";a:2:{'
+                         's:4:"name";s:10:"com.social";'
+                         's:5:"email";s:32:"comunicacao.social@serpro.gov.br";'
+                      '}'
+                      's:2:"to";a:2:{'
+                         's:4:"name";s:32:"comunicacao.social@serpro.gov.br";'
+                         's:5:"email";s:32:"comunicacao.social@serpro.gov.br";'
+                      '}'
+                      's:7:"subject";s:28:"Primeira leitura - 10/5/2011";'
+                      's:4:"Size";N;'
+                      's:10:"attachment";a:2:{'
+                         's:5:"names";b:0;'
+                         's:18:"number_attachments";i:0;'
+                      '}'
+                  '}'
+                  's:12:"array_attach";N;'
+                  's:15:"url_export_file";s:160:"inc/gotodownload.php?idx_file=/var/www/expressov2/expressoMail/inc/../tmpLclAtt/source_38a3e960115351578aa182ae1d6c2b7b.txt&newfilename=fonte_da_mensagem.txt";'
+                  's:2:"to";N;'
+              '}';
    return(tplBody);
}
*/

MAPattern.prototype.toExpressoMailDataHeader = function(msgid){
    var tplHMail = "";
    var tmpHead = expresso_mail_archive.getMessageHeaders(msgid);
    return tplHMail;    
}

MAPattern.prototype.toExpressoMailData = function(msg){
    var tplBMail = "";
    
    return(tplBMail);
}

MAPattern.prototype.toExpressoMailAttachment = function(att){    
    var objMsg = expresso_mail_archive.getMessageServiceObject(att);
    var attData = objMsg.getAttachments();
    var objAtt =  eval("(" + attData + ")");
    var srlData = "";

    //There are attachments
    if(typeof(objAtt.attachment) != 'undefined'){
        if(typeof(objAtt.attachment.length) != 'undefined'){
            srlData = 's:11:"attachments";a:' + objAtt.attachment.length + ':{';
            for(var m=0; m<objAtt.attachment.length; m++){
                srlData += 's:1:"' + m + '";'
                         + 'a:4:{'
                         + 's:3:"pid";s:' + objAtt.attachment[m]["@id"].length + ':"' + objAtt.attachment[m]["@id"] + '";'
                         + 's:4:"name";s:' + objAtt.attachment[m]["@name"].length + ':"' + objAtt.attachment[m]["@name"] + '";'
                         + 's:8:"encoding";s:' + objAtt.attachment[m]["@encoding"].length + ':"' + objAtt.attachment[m]["@encoding"] + '";'
                         + 's:5:"fsize";i:' + objAtt.attachment[m]["@size"] + ';'
                         + '}';
            }
        }
        //just one att
        else{
            srlData = 's:11:"attachments";a:1:{';
            srlData += 's:1:"0";'
                     + 'a:4:{'
                     + 's:3:"pid";s:' + objAtt.attachment["@id"].length + ':"' + objAtt.attachment["@id"] + '";'
                     + 's:4:"name";s:' + objAtt.attachment["@name"].length + ':"' + objAtt.attachment["@name"] + '";'
                     + 's:8:"encoding";s:' + objAtt.attachment["@encoding"].length + ':"' + objAtt.attachment["@encoding"] + '";'
                     + 's:5:"fsize";i:' + objAtt.attachment["@size"] + ';'
                     + '}';
        }
        srlData += '}';            
    }    
    //No attachments data
    else{
        srlData = 's:11:"attachments";N;';
    }
    return(srlData);
}

MAPattern.prototype.toExpressoMailThumbs = function(thb){
    var objMsg = expresso_mail_archive.getMessageServiceObject(thb);
    var attData = objMsg.getAttachments();
    var objAtt =  eval("(" + attData + ")");
    var srlData = "";
    var arrThumbs = new Array();
    if(typeof(objAtt.attachment) != 'undefined'){       
        //more than 1 att
        if(typeof(objAtt.attachment.length) != 'undefined'){
            for(var m=0; m<objAtt.attachment.length; m++){
                //Only images has thums
                if(objAtt.attachment[m]["@mediaType"].toLowerCase() == 'image')
                    arrThumbs.push(objAtt.attachment[m]);
            }
        }
        //just one att data
        else{
            if(objAtt.attachment["@mediaType"].toLowerCase() == 'image')
                arrThumbs.push(objAtt.attachment);
        }
        //There are thumbs
        if(arrThumbs.length > 0){
            srlData = 's:6:"thumbs";a:'+arrThumbs.length+':{'
            for(var n=0; n<arrThumbs.length; n++){
                var thumblink = '<a onMouseDown=\'save_image(event,this,"image/'+arrThumbs[n]["@subType"]+'")\' href=\'#'+mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/temp/'+arrThumbs[n]["@id"]+'/'+arrThumbs[n]["@name"]+'\' onClick="window.open(\''+mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/temp/'+arrThumbs[n]["@id"]+'/' + arrThumbs[n]["@name"] + '\',\'mywindow\',\'width=700,height=600,scrollbars=yes\');"><IMG id=\''+arrThumbs[n]["@id"]+'\' style=\'border:2px solid #fde7bc;padding:5px\' title=\'Clique na imagem para Ampliar.\' src="'+mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/temp/'+arrThumbs[n]["@id"]+'/thumb/120/' + arrThumbs[n]["@name"] +'"></a>';
                srlData += 's:1:"'+n+'";s:'+thumblink.length+':"'+thumblink+'";'
            }
            srlData += '}';    
        }
        //No thumbs
        else
            srlData = 's:6:"thumbs";N;';        
    }   
    //Something is wrong with attachments thumb behavior: return no thumbs
    else{
        srlData = 's:6:"thumbs";N;';
    }
    return(srlData);
}

MAPattern.prototype.toExpressoMailSignature = function(sgn){
    return('s:9:"signature";N;');
}

MAPattern.prototype.toExpressoMailBaseHeaders = function(hdr){
    var defMailHeaders = {'recent':' ','unseen': ' ','deleted': ' ','answered': ' ','draft': ' ','flagged': ' '};
    var objMsg = expresso_mail_archive.getMessageServiceObject(hdr);
    var tagData = objMsg.getTags();
    var objTag = eval('(' + tagData + ')');
    var tplHMail = "";
    
    if(objTag.tag){
    //More than 1 attachment - because length will be 'undefined' if just one exists
    if(objTag.tag.length >= 0){

        //flaggs the tags
        for(var k=0; k<objTag.tag.length; k++){
            for(var j in defMailHeaders){
                var tagvalue = objTag.tag[k]["@value"].toLowerCase();
                if((tagvalue == j) && (defMailHeaders[j] != true))
                    defMailHeaders[j] = true;
                else
                    defMailHeaders[j] = false;
            }
        }
        
        //serialize all needed headers       
        if(defMailHeaders["recent"] == true)
            tplHMail += 's:6:"Recent";s:1:"N";';
        else
            tplHMail += 's:6:"Recent";s:1:" ";';
        if(defMailHeaders["unseen"] == true)
            tplHMail += 's:6:"Unseen";s:1:"U";';
        else
            tplHMail += 's:6:"Unseen";s:1:" ";';
        if(defMailHeaders["deleted"] == true)
            tplHMail += 's:7:"Deleted";s:1:"D";';
        else
            tplHMail += 's:7:"Deleted";s:1:" ";';
        if(defMailHeaders["answered"] == true)
            tplHMail += 's:8:"Answered";s:1:"A";';
        else
            tplHMail += 's:8:"Answered";s:1:" ";';
        if(defMailHeaders["draft"] == true)
            tplHMail += 's:5:"Draft";s:1:"X";';
        else
            tplHMail += 's:5:"Draft";s:1:" ";';        
        if(defMailHeaders["flagged"] == true)
            tplHMail += 's:7:"Flagged";s:1:"F";';
        else
            tplHMail += 's:7:"Flagged";s:1:" ";';                      
    }
    }
    
    //No header to tag
    else{
        tplHMail += 's:6:"Recent";s:1:" ";';
        tplHMail += 's:6:"Unseen";s:1:" ";';
        tplHMail += 's:7:"Deleted";s:1:" ";';
        tplHMail += 's:8:"Answered";s:1:" ";';
        tplHMail += 's:5:"Draft";s:1:" ";';        
        tplHMail += 's:7:"Flagged";s:1:" ";';              
    }
    return tplHMail;        
}

MAPattern.prototype.toExpressoMailArrayAttach = function(att){
    var objMsg = expresso_mail_archive.getMessageServiceObject(att);
    var attData = objMsg.getAttachments();
    var objAtt =  eval("(" + attData + ")");
    var srlData = "";

    //There are attacments, map all them
    if(typeof(objAtt.attachment) != 'undefined'){
        srlData = 's:12:"array_attach";a:' + objAtt.attachment.length + ':{';
        for(var m=0; m<objAtt.attachment.length; m++){
            var urllink = mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/temp/download/'+objAtt.attachment[m]["@id"]+'/'+objAtt.attachment[m]["@name"];
            srlData += 's:1:"' + m + '";'
                     + 'a:3:{'
                     + 's:4:"name";s:' + objAtt.attachment[m]["@name"].length + ':"' + objAtt.attachment[m]["@name"] + '";'
                     + 's:3:"url";s:' + urllink.length + ':"' + urllink + '";'
                     + 's:3:"pid";s:' + objAtt.attachment[m]["@id"].length + ':"' + objAtt.attachment[m]["@id"] + '";'
                     + '}';
        }
        srlData += '}';
    }    
    //No one attachment
    else{
        srlData = 's:12:"array_attach";N;';
    }
    return(srlData);
}

MAPattern.prototype.toExpressoMailURLExportFile = function(path, file){
    var srlData = 's:15:"url_export_file";';
    var urllink = mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/mail/'+path+'/'+file;
    srlData += 's:' + urllink.length + ':"' + urllink + '";'
    return(srlData);
}

MAPattern.prototype.toExpressoMail = function(mheader, mbody){
    var tplMessage = null;
    var tplMessageData = null;
    var tplMessageHeaderData = null;  
    
    //Decodes message headers first
    var hdata = mheader;
    var h_date = expresso_mail_archive.pattern.toExpressoDate(mheader["udate"]*1000);
           
    //Init message template
    tplMessage = 'a:49{s:4:"body";N;'
+   expresso_mail_archive.pattern.toExpressoMailAttachment(expresso_mail_archive.currentmessage)
+   expresso_mail_archive.pattern.toExpressoMailThumbs(expresso_mail_archive.currentmessage)    
+   expresso_mail_archive.pattern.toExpressoMailSignature(expresso_mail_archive.currentmessage)
+   expresso_mail_archive.pattern.toExpressoMailBaseHeaders(expresso_mail_archive.currentmessage)
+   's:5:"udate";s:8:"' + h_date.split(" ")[1] +'";' //hours
+   's:7:"msg_day";s:10:"'+ h_date.split(" ")[0] +'";' //date
+   's:8:"msg_hour";s:8:"' + h_date.split(" ")[1] +'";' //hours
+   's:8:"fulldate";s:19:"' + h_date +'";' //full date + hours
+   's:9:"smalldate";s:8:"' + h_date.split(" ")[1] +'";' //hours
+   's:10:"msg_number";s:'+expresso_mail_archive.currentmessage.length+':"'+expresso_mail_archive.currentmessage+'";'
+   's:10:"msg_folder";s:'+(expresso_mail_archive.currentfolder.length+6)+':"local_'+expresso_mail_archive.currentfolder+'";'
+   's:4:"from";N;'
+    expresso_mail_archive.pattern.toExpressoSender(expresso_mail_archive.currentmessage)
+    expresso_mail_archive.pattern.toExpressoReplyTo(expresso_mail_archive.currentmessage)
+   's:10:"msg_sample";' + expresso_mail_archive.pattern.toExpressoPreview(expresso_mail_archive.currentmessage)
+    expresso_mail_archive.pattern.toExpressoAddress2(expresso_mail_archive.currentmessage)
+   's:9:"timestamp";i:'+ mheader["udate"]*1000 +';' //getback the lost miliseconds...
+   expresso_mail_archive.pattern.toExpressoDispositionNotificationTo(expresso_mail_archive.currentmessage)
+   's:5:"login";i:' + account_id
+   's:6:"header";' + expresso_mail_archive.pattern.toExpressoHeader(expresso_mail_archive.getMessageServiceObject(expresso_mail_archive.currentmessage)) 
+   expresso_mail_archive.pattern.toExpressoMailArrayAttach(expresso_mail_archive.currentmessage)
+   expresso_mail_archive.pattern.toExpressoMailURLExportFile(expresso_mail_archive.currentfolder, expresso_mail_archive.currentmessage)
+   's:2:"to";N;'
+   '}';

    var strMailMessage = connector.unserializeArchiver(tplMessage);
//    strMessage['from'] = mheader["from"];
    //strMessage['from'] = expresso_mail_archive.toExpressoBody(mheader, mbody);
//    strMessage['subject'] = mheader["subject"];
    strMailMessage['body'] = mbody;
//    strMessage['to'] = mheader["to"];
//    strMessage['cc'] = mheader["cc"];
    strMailMessage['local_message'] = true;
    strMailMessage['folder'] = 'local_' + expresso_mail_archive.currentfolder;
    //strMessage['msg_number'] = mheader["msg_number"];
    //strMailMessage['msg_number'] = mheader["msg_number"];
    return(strMailMessage);
}

MAPattern.prototype.toExpressoMessage = function(mheader, mbody){
    var strMessage = null;
    var strMessage1 = expresso_mail_archive.pattern.toExpressoBody(mheader);
    strMessage = connector.unserialize(strMessage1);
        
    strMessage['from'] = mheader["from"];
    //strMessage['from'] = expresso_mail_archive.toExpressoBody(mheader, mbody);
    strMessage['subject'] = mheader["subject"];
    strMessage['body'] = mbody;
    strMessage['to'] = mheader["to"];
    strMessage['cc'] = mheader["cc"];
    strMessage['local_message'] = true;
    strMessage['folder'] = 'local_' + expresso_mail_archive.currentfolder;
    strMessage['msg_number'] = mheader["msg_number"];
    return(strMessage);


/*
 *	retorno['from'] = connector.unserialize(rs.field(2));
	retorno['subject'] = rs.field(3);
	retorno['body'] = rs.field(4);
	//Codigo que as imagens embutidas em emails (com multipart/related ou multipart/mixed) sejam corretamente mostradas em emails arquivados. Os links do
	//tipo "./inc/show_embedded_attach.php?msg_folder=[folder]&msg_num=[msg_num]&msg_part=[part]"
	//são substituidos pelos links dos anexos capturados pelo gears.

	var thumbs= retorno.thumbs;
	var anexos= retorno.array_attach;
	for (i in anexos)
	{
	    if(anexos[i]['url'] && anexos[i]['url'].match(/((jpg)|(jpeg)|(png)|(gif)|(bmp))/gi))
	    {
		var er_imagens = new RegExp("\\.\\/inc\\/show_embedded_attach.php\\?msg_folder=[\\w/]+\\&msg_num=[0-9]+\\&msg_part="+anexos[i]['pid']);
		var Result_imagens = er_imagens.exec(retorno['body']);
		retorno['body'] = retorno['body'].replace(Result_imagens,anexos[i]['url']);
		if(thumbs && thumbs[i]){
		    er_imagens = new RegExp("\\.\\/inc\\/show_thumbs.php\\?file_type=image\\/[\\w]+\\&msg_num=[0-9]+\\&msg_folder=[\\w/%]+\\&msg_part="+anexos[i]['pid']);
		    Result_imagens = er_imagens.exec(thumbs[i]);
		    thumbs[i] = thumbs[i].replace(Result_imagens,"'"+anexos[i]['url']+"'");
		    er_imagens = new RegExp("\\.\\/inc\\/show_img.php\\?msg_num=[0-9]+\\&msg_folder=[\\w/%]+\\&msg_part="+anexos[i]['pid']);
		    Result_imagens = er_imagens.exec(thumbs[i]);
		    thumbs[i] = thumbs[i].replace(Result_imagens,anexos[i]['url']);
		    thumbs[i] = thumbs[i].replace(/<IMG/i,'<img width="120"'); 
		}
	    }
	}

	retorno['to'] = connector.unserialize(rs.field(5));
	retorno['cc'] = connector.unserialize(rs.field(6));

	retorno['local_message'] = true;
	retorno['msg_folder'] = "local_"+rs.field(7); //Now it's a local folder
	retorno['msg_number'] = rs.field(0)+plus_id; //the message number is the rowid
 **/ 
}

MAPattern.prototype.tagConfig = function(taglist, arcidmsg, op){
    //window.alert('no maexpressopattern.tagconfig:\n' + print_r(taglist));
    if(taglist != null){
        var tagdata = "";
        //Código comentado para evitar inconsistência de flags quando mensagens passam pela pasta rascunhos
        //var flag_draft = false;
        
        //first message to tag data
        if(expresso_mail_archive.taglist == ""){

            //Código comentado para evitar inconsistência de flags quando mensagens passam pela pasta rascunhos
            /*
            Draft folder, are "draft"
            if(expresso_mail_archive.folder_destination == 'drafts'){
                flag_draft = true;
                tagdata += '<add value="draft"/>';
                tagdata += '<remove value="answered"/>';
                tagdata += '<remove value="forwarded"/>';
                if (typeof(taglist) == 'object'){
                    for(var x in taglist[0]){
                        if (x != 'importance'){
                            if(( x != 'unseen') && ( x != 'answered')){
                                if((taglist[0][x] != '') && (taglist[0][x] != ' ') && (taglist[0][x] != null) && (x != 'msgid'))
                                    tagdata += '<remove value="'+x+'"/>';
                            }
                            else{
                                if( x == 'unseen'){
                                    if (taglist[0][x] == ' ')
                                        tagdata += '<remove value="seen"/>';
                                    else
                                        if((taglist[0][x] != '') && (taglist[0][x] != null))
                                            tagdata += '<remove value="unseen"/>';                                
                                }
                                if( x == 'answered'){
                                    if (taglist[0][x] == ' ')
                                        tagdata += '<remove value="unanswered"/>';
                                    else
                                        if((taglist[0][x] != '') && (taglist[0][x] != null))
                                            tagdata += '<remove value="answered"/>';                                                                    
                                }                              
                            }
                        }
                        else{
                            if((taglist[0][x] != '') && (taglist[0][x] != ' ')){
                                switch (taglist[0][x].toLowerCase()){
                                    case 'high':
                                        tagdata += '<remove value="importance_high"/>';
                                        tagdata += '<remove value="flagged"/>';
                                        break;
                                    default:
                                        tagdata += '<remove value="importance_normal"/>';
                                        tagdata += '<remove value="unflagged"/>';
                                        break;                                        
                                }
                            }
                        }
                    }
                }
                else{
                     if(!flag_draft)
                        tagdata += '<remove value="'+taglist+'"/>';
                }
            }
            else
               tagdata += '<remove value="draft"/>';
            */

            //Trash folder, are "deleted""
            if(expresso_mail_archive.folder_destination == 'trash'){
                tagdata += '<add value="deleted"/>';
            }
            else
                tagdata += '<remove value="deleted"/>';
        
            //Código comentado para evitar inconsistência de flags quando mensagens passam pela pasta rascunhos
            //if(!flag_draft){

            var action;
            if (op == 0)
                action = 'add';
            else
                action = 'remove';
    
            //object data -used at archieve op
            if (typeof(taglist) == 'object'){
                for(var x in taglist[0]){
                    if (x != 'importance'){
                        if(( x != 'unseen') && ( x != 'answered')){
                            if((taglist[0][x] != '') && (taglist[0][x] != ' ') && (taglist[0][x] != null) && (x != 'msgid')) {
                                tagdata += '<'+action+' value="'+x+'"/>';
                            } else {
                                if(x == 'flagged') 
                                    tagdata += '<'+action+' value="unflagged"/>';
                            }
                        }
                        else{
                            if( x == 'unseen'){
                                if (taglist[0][x] == ' ')
                                    tagdata += '<'+action+' value="seen"/>';
                                else
                                    if((taglist[0][x] != '') && (taglist[0][x] != null))
                                        tagdata += '<'+action+' value="unseen"/>';                                
                            }
                            if( x == 'answered'){
                                if (taglist[0][x] == ' ')
                                    tagdata += '<'+action+' value="unanswered"/>';
                                else
                                    if((taglist[0][x] != '') && (taglist[0][x] != null))
                                        tagdata += '<'+action+' value="answered"/>';                                                                    
                            }                            
                        }
                    }
                    else{
                        if((taglist[0][x] != '') && (taglist[0][x] != ' ')){
                            switch (taglist[0][x].toLowerCase()){
                                case 'high':
                                    tagdata += '<'+action+' value="importance_high"/>';
                                    tagdata += '<'+action+' value="flagged"/>';
                                    break;
                                default:
                                    tagdata += '<'+action+' value="importance_normal"/>';
                                    tagdata += '<'+action+' value="unflagged"/>';
                                    break;                                        
                            }
                           
                       }
                       //if no importance data received, defaults is "Normal"
                       else{
                           tagdata += '<add value="importance_normal"/>';
                           tagdata += '<add value="unflagged"/>';
                       }
                    }
                }
            }
            //string data - used to tag a message when reading it (unseen and important, by now)
            else{
                if(taglist.indexOf(",") != -1){
                    var vet_tags = taglist.split(",");
                    for(var k=0; k<vet_tags.length; k++){
                        tagdata += '<'+action+' value="'+vet_tags[k]+'"/>';
                    }
                }
                else
                    tagdata += '<'+action+' value="'+taglist+'"/>';
            }

            //Código comentado para evitar inconsistência de flags quando mensagens passam pela pasta rascunhos
            //}

            if(tagdata != ""){    
                var tobject = '<tag>'
                +              '<message id="'+arcidmsg+'">'
                +              tagdata
                +              '</message>'
                +             '</tag>';
                //window.alert('tagdata ret1 = ' + tobject);
                return(tobject);
            }
        }
        //more messages to add at tag block
        else{
            var endpoint = expresso_mail_archive.taglist.lastIndexOf("<");
           
            //Código comentado para evitar inconsistência de flags quando mensagens passam pela pasta rascunhos
            /*
            Draft folder, are "draft"
            if(expresso_mail_archive.folder_destination == 'drafts'){
                flag_draft = true;
                tagdata += '<add value="draft"/>';
                if (typeof(taglist) == 'object'){
                    for(var x in taglist[0]){
                        if (x != 'importance'){
                            if(( x != 'unseen') && ( x != 'answered')){
                                if((taglist[0][x] != '') && (taglist[0][x] != ' ') && (taglist[0][x] != null) && (x != 'msgid'))
                                    tagdata += '<remove value="'+x+'"/>';
                            }
                            else{
                                if( x == 'unseen'){
                                    if (taglist[0][x] == ' ')
                                        tagdata += '<remove value="seen"/>';
                                    else
                                        if((taglist[0][x] != '') && (taglist[0][x] != null))
                                            tagdata += '<remove value="unseen"/>';                                
                                }
                                if( x == 'answered'){
                                    if (taglist[0][x] == ' ')
                                        tagdata += '<remove value="unanswered"/>';
                                    else
                                        if((taglist[0][x] != '') && (taglist[0][x] != null))
                                            tagdata += '<remove value="answered"/>';                                                                    
                                }                              
                            }
                        }
                        else{
                            if((taglist[0][x] != '') && (taglist[0][x] != ' ')){
                                switch (taglist[0][x].toLowerCase()){
                                    case 'high':
                                        tagdata += '<'+action+' value="importance_high"/>';
                                        tagdata += '<'+action+' value="flagged"/>';
                                        break;
                                    default:
                                        tagdata += '<'+action+' value="importance_normal"/>';
                                        tagdata += '<'+action+' value="unflagged"/>';
                                        break;                                        
                                }
                               
                            }
                           //if no importance data received, defaults is "Normal"
                           else{
                               tagdata += '<add value="importance_normal"/>'; 
                               tagdata += '<add value="unflagged"/>';
                           }
                        }
                    }
                }
                else{
                     if(!flag_draft)
                        tagdata += '<remove value="'+taglist+'"/>';
                }
            }
            else
                tagdata += '<remove value="draft"/>';
            */

            //Trash folder, are "deleted""
            if(expresso_mail_archive.folder_destination == 'trash'){
                tagdata += '<add value="deleted"/>';
            }
            else
                tagdata += '<remove value="deleted"/>';
            
            //Código comentado para evitar inconsistência de flags quando mensagens passam pela pasta rascunhos
            //if(!flag_draft){

            var action;
            if (op == 0)
                action = 'add';
            else
                action = 'remove';
    
            //object data -used at archieve op
            if (typeof(taglist) == 'object'){
                for(var x in taglist[0]){
                    if (x != 'importance'){
                        if(( x != 'unseen') && ( x != 'answered')){
                            if((taglist[0][x] != '') && (taglist[0][x] != ' ') && (taglist[0][x] != null) && (x != 'msgid'))
                                tagdata += '<'+action+' value="'+x+'"/>';
                        }

                        else{
                            if( x == 'unseen'){
                                if (taglist[0][x] == ' ')
                                    tagdata += '<'+action+' value="seen"/>';
                                else
                                    if((taglist[0][x] != '') && (taglist[0][x] != null))
                                        tagdata += '<'+action+' value="unseen"/>';                                
                            }
                            if( x == 'answered'){
                                if (taglist[0][x] == ' ')
                                    tagdata += '<'+action+' value="unanswered"/>';
                                else
                                    if((taglist[0][x] != '') && (taglist[0][x] != null))
                                        tagdata += '<'+action+' value="answered"/>';                                                                    
                            }                             
                        }
                    }
                    else{
                        if((taglist[0][x] != '') && (taglist[0][x] != ' ')){
                            switch (taglist[0][x].toLowerCase()){
                                case 'important':
                                    tagdata += '<'+action+' value="importance_high"/>';
                                    tagdata += '<'+action+' value="flagged"/>';
                                    break;
                                default:
                                    tagdata += '<'+action+' value="importance_normal"/>';
                                    tagdata += '<'+action+' value="unflagged"/>';
                                    break;                                        
                            }
                            
                        }
                       //if no importance data received, defaults is "Normal"
                       else{
                           tagdata += '<add value="importance_normal"/>'; 
                           tagdata += '<add value="unflagged"/>';
                       }
                    }
                }
            }
            //string data - used to tag a message
            else{
                if(taglist.indexOf(",") != -1){
                    var vet_tags = taglist.split(",");
                    for(var k=0; k<vet_tags.length; k++){
                        tagdata += '<'+action+' value="'+vet_tags[k]+'"/>';
                    }
                }
                else
                    tagdata += '<'+action+' value="'+taglist+'"/>';
            }

            //Código comentado para evitar inconsistência de flags quando mensagens passam pela pasta rascunhos
            //}
            
            if(tagdata != ""){        
                var tobject = expresso_mail_archive.taglist.substr(0, endpoint)
                +              '<message id="'+arcidmsg+'">'
                +              tagdata
                +              '</message>'
                +             '</tag>';
                //window.alert('tagdata ret2 = ' + tobject);
                return(tobject);
            }            
            
        }
        return(null);
    }
    return(null);
}

MAPattern.prototype.zipConfig = function(array_messages){
    var tagdata ="";
    if(typeof(array_messages) == 'object'){
        if(!array_messages["format"])
            var def_format = 'zip';
        else
            var def_format = array_messages["format"].toLowerCase();
        var tobject = '<zip format="' + def_format + '">';
        //Export from selected messages grid
        if(array_messages["type"] == 'messages'){
            for(var k=0; k<array_messages["messages"].length;k++){
                tobject += '<message id="' + array_messages["messages"][k] + '"/>';
            }   
        }
        //Export from folder management
        else{
            var folderid = array_messages["messages"];
            if (!array_messages["recursive"])
                var rec = false;
            else
                var rec = array_messages["recursive"];
            tobject += '<folder id="' + array_messages["messages"] + '" recursive="' + rec + '"/>';
        }
        tobject += '</zip>';        
        return(tobject);
    }
    return(null);
}

MAPattern.prototype.download_compressed_attachments = function(msgid,format){
    var urldownload = mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/temp/download/parts_'+msgid+'.'+format;
    return(urldownload);
}

MAPattern.prototype.message_source = function(msgid){
    var urldownload = mail_archive_protocol+'://'+mail_archive_host+':'+mail_archive_port+'/mail/'+folder+'/'+msgid+'.eml';
    return(urldownload);
}
-->