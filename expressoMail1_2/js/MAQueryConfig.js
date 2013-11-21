<!--
/**
 *MailArchiver Query Config API
 *
 *This api is intended to be used with MailArquiver JavaScript API.
 *Basicly, it is responsable to provide a standard data format conversion
 *to handle queryes under MailArquiver web services pooling. *
 *
 */


//MailArchiver Query Config data structure
var MAQueryConfig = function(){
  this.lowerIndex = null;
  this.upperIndex = null;
  this.from = null;
  this.to = null;
  this.cc = null;
  this.subject = null;
  this.body = null;
  this.date = null;
  this.dateLower = null;
  this.dateUpper = null;  
  this.tag = null;
  this.folder = null;
  this.folder_recursive = null;
  this.order = null;
  this.preview_message = null;
  this.preview_tooltip = null;
  this.defaults = null;
}

//Set up all criteria list received
MAQueryConfig.prototype.setCriteriaList = function(cl){
  //window.alert('dumpando setCriteriaList:\n' + print_r(cl));
  for(var p in cl){
    //window.alert('p = ' + p + '\ncl = ' + cl + '\ncl[p] = ' + cl[p] + '\np[cl] = ' + p[cl]);
    //Decode the criteria for search.
    cl[p] = url_decode_s(cl[p]);
    if(p.toLowerCase() == 'all')
      this.setSubject(cl[p]);
    if(p.toLowerCase() == 'lowerindex')
      this.setLowerIndex(cl[p]);    
    if(p.toLowerCase() == 'upperindex')
      this.setUpperIndex(cl[p]);      
    if(p.toLowerCase() == 'from')
      this.setFrom(cl[p]);
    if(p.toLowerCase() == 'to')
      this.setTo(cl[p]);
    if(p.toLowerCase() == 'cc')
      this.setCc(cl[p]);
    if(p.toLowerCase() == 'subject')
      this.setSubject(cl[p]);
    if(p.toLowerCase() == 'body')
      this.setBody(cl[p]);
    if(p.toLowerCase() == 'on')
      this.setDate('on',cl[p]);
    if(p.toLowerCase() == 'since')
      this.setDate('lower',cl[p]);  
    if(p.toLowerCase() == 'before')
      this.setDate('upper',cl[p]);  
    if(p.toLowerCase() == 'flags')
      this.setTag(cl[p]);
    if(p.toLowerCase() == 'folder')
      this.setFolder(cl[p]);
    if(p.toLowerCase() == 'order')
      this.setOrder(cl[p]);

    //search tags window criteria mapping
    if(p.toLowerCase() == 'flagged'){
        var oexptag = eval('({"contains":"flagged"})'); 
        this.setTags(oexptag);        
    }
    if(p.toLowerCase() == 'unflagged'){
        var oexptag = eval('({"contains":"unflagged"})'); 
        this.setTags(oexptag);        
    }    
    if(p.toLowerCase() == 'seen'){        
        var oexptag = eval('({"contains":"seen"})'); 
        this.setTags(oexptag);                
    }    
    if(p.toLowerCase() == 'unseen'){
        var oexptag = eval('({"contains":"unseen"})'); 
        this.setTags(oexptag);                
    }
    if(p.toLowerCase() == 'answered'){
        var oexptag = eval('({"contains":"answered"})'); 
        this.setTags(oexptag);                
    }        
    if(p.toLowerCase() == 'unanswered'){
        var oexptag = eval('({"contains":"unanswered"})'); 
        this.setTags(oexptag);                
    }            
  }
}

MAQueryConfig.prototype.setDefaults = function(defaults){
    this.defaults = defaults;
}

MAQueryConfig.prototype.setExpressoDefaults = function(data_default){
    //Expresso defaults came from "messages_controller.js": here, we maps
    //each array entry from messages_list intended to run by our own behavior
    if(data_default.length != 8)
        return;
   //expresso default argument list => new Array(baseFolder,msg_range_begin,emails_per_page,sort_box_type,search_box_type, sort_box_reverse,preview_msg_subject,preview_msg_tip);
   this.setFolder(data_default[0]);
   this.setLowerIndex(data_default[1]);
   this.setUpperIndex(data_default[2]);
   
   if (parseInt(data_default[5]) == 0)
       var oexpor = 'asc';
   else
       var oexpor = 'desc';
   
   //window.alert('NO MAQueryconfig setExpressoDefaults\n\nbase_folder = ' + data_default[0] + '\nmsg_range_begin = ' + data_default[1] + '\nemails_per_page = ' + data_default[2] + '\nsort_box_type =' + data_default[3].toLowerCase() + '\nsearch_box_type= ' + data_default[4] + '\nsort_box_reverse = ' + data_default[5] + '\npreview_msg_subject = ' + data_default[6] + '\npreview_msg_tip = ' + data_default[7]);
   
   //sets tag data filter
   switch(data_default[4].toLowerCase()){
    case 'all':
        var tagtolist_criteria = 'contains';
        var tagtolist_value = 'all';
        break;
    case 'unseen':
        var tagtolist_criteria = 'contains';
        var tagtolist_value = 'unseen';
        break;
    case 'seen':
        var tagtolist_criteria = 'contains';
        var tagtolist_value = 'seen';        
        break;
    case 'answered':
        var tagtolist_criteria = 'contains';
        var tagtolist_value = 'answered';        
        break;
    case 'unanswered':
        var tagtolist_criteria = 'contains';
        var tagtolist_value = 'unanswered';        
        break;        
    case 'flagged':
        var tagtolist_criteria = 'contains';
        var tagtolist_value = 'flagged';        
        break;  
    case 'unflagged':
        var tagtolist_criteria = 'contains';
        var tagtolist_value = 'unflagged';        
        break;        
    default:
        var tagtolist_criteria = null;
        var tagtolist_value = null;        
   }

   //sets TagConfig
   var oexptag = eval('({"' + tagtolist_criteria + '":"' + tagtolist_value + '"})'); 
   this.setTags(oexptag);


   //sets order criteria
   switch(data_default[3].toLowerCase()){
       case 'sortfrom':
           var oexpcrt = 'from';
           break;
       case 'sortsubject':
           var oexpcrt = 'subject';
           break;
       case 'sortsize':
           var oexpcrt = 'size';
           break;
       default:
           var oexpcrt = 'date';
           break;
   }   

   var oexporder = eval('({"' + oexpcrt + '":"' + oexpor + '"})');
   this.setOrder(oexporder);
}

//PharseCriteria receives data to pharse structure format fields from, to, cc and subject
MAQueryConfig.prototype.pharseCriteria = function(field, data, criteria){
  //window.alert('pharseCriteria input data:\n\n-> field = ' + field + '\ndata = ' + data + '\ncriteria = ' + criteria);
  if(!criteria)
      criteria = 'none';
  
  var stdout = '"'+field+'":[';
  switch (criteria.toLowerCase()){
      case "equals":
          stdout += '{"@equals":"'+data+'"},';
          break;
      case "equalsic":
          stdout += '{"@equalsIgnoreCase":"'+data+'"},';
          break;
      case "like":
          stdout += '{"@like":"'+data.like+'"},';
          break;
      default:
          stdout+= '{"@likeIgnoreCase":"'+data+'"},';
  }
  stdout = stdout.substr(0,stdout.length-1) + '],';
  return(stdout);
}

//setbounds up and down
MAQueryConfig.prototype.setLowerIndex = function(low){
  //window.alert('setbounds low = ' + low + ' high = ' + high);
  this.lowerIndex = '"@lowerIndex":"'+(parseInt(low)-1)+'", ';
}

MAQueryConfig.prototype.setUpperIndex = function(high){
  //window.alert('setbounds low = ' + low + ' high = ' + high);
  this.upperIndex = '"@upperIndex":"'+(parseInt(high))+'", ';
}
 
//From receives a array of structutred data, by the model
MAQueryConfig.prototype.setFrom = function(data){
  if(data.length <= 0){
    this.from = null;
    return;
  }
  
  //subdata = this.pharseCriteria('from', data);
  this.from = '"@from":"' + data + '",';
}

//To receives a array of structutred data, by the model
MAQueryConfig.prototype.setTo = function(data){
  if(data.length <= 0){
    this.to = null;
    return;
  }

  //subdata = this.pharseCriteria('to', data);
  this.to = '"@to":"' + data + '",';
}

//Cc data
MAQueryConfig.prototype.setCc = function(data){
  if(data.length <= 0){
    this.cc = null;
    return;
  }

  //subdata = this.pharseCriteria('cc', data);
  this.cc = '"@cc":"' + data + '",';
}

//Subject data
MAQueryConfig.prototype.setSubject = function(sub){
  if(sub.length <= 0){
    this.subject = null;
    return;
  }

  //subdata = this.pharseCriteria('subject', sub);
  this.subject = '"@subject":"' + sub + '",';
}

//PREGMatch date format
MAQueryConfig.prototype.validateDate = function (dat){
 var dateregex = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/;
 if (dateregex.test(dat))
   return true;
 else
   return false;
}

//PREGMatch time format
MAQueryConfig.prototype.validateTime = function (tim){
  //var timeregex = /^([1-9]|1[0-2]):[0-5]\d(:[0-5]\d(\.\d{1,3})?)?$/;
  //var timeregex = /^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/;
  var timeregex = /^\d{1,2}[:]\d{2}([:]\d{2})?$/;
  if (timeregex.test(tim))
    return true;
  else
    return false;
}

//toStdDate returns date time as long. Format input:'DD/MM/YYYY HH:MM:SS'
MAQueryConfig.prototype.toStdDate = function(dt){ 
  if (dt.length <= 0)
    return;

  var date_time_picker = dt.split(" ");
  var date_part = date_time_picker[0];
  var time_part = date_time_picker[1];

  //window.alert('date_part = ' + date_time_picker[0] + '\ntime_part = ' + date_time_picker[1]);

  if ((this.validateDate(date_part)) /*&& (this.validateTime(time_part))*/){
    var date_split = date_part.split("/");
    var ddd = date_split[1]+'/'+date_split[0]+'/'+date_split[2] + ' ' + (time_part ? time_part : '00:00');
    var ndate = new Date(ddd);   
    
    //window.alert('Date.parse (' + ddd + ') = ' + Date.parse(ndate));
    return Math.round(ndate.getTime());//(Date.parse());
  }
  else
    return("");
}

//Date data
MAQueryConfig.prototype.setDate = function(date_field, date_data){
  if((!date_field) || (!date_data)){
    this.date = null;
    this.dateLower = null;
    this.dateUppder = null;
    return;
  }
    
  if(date_field == 'lower'){
    if(date_data.indexOf('%') != -1)
        var dt1b = this.pharseStdDate(date_data);
    else
        var dt1b = this.toStdDate(date_data);
    this.dateLower = '"@lowerDate":"'+dt1b+'",';
  }
  if(date_field == 'upper'){
    if(date_data.indexOf('%') != -1)
        var dt2b = this.pharseStdDate(date_data);
    else
        var dt2b = this.toStdDate(date_data);
    this.dateUpper = '"@upperDate":"'+dt2b+'",';
  }
  if(date_field == 'on'){
    if(date_data.indexOf('%') != -1)
        var dt3b = this.pharseStdDate(date_data);
    else
        var dt3b = this.toStdDate(date_data);
    this.date = '"@date":"'+dt3b+'",';
  }
}

//flags data
MAQueryConfig.prototype.setTag = function(flaglist){
  if(flaglist.flags.length <= 0){
    this.tag = null;
    return;
  }

  this.tag = '"flag":[';
  var flag_data = flaglist.flags.split(',');
  if(flag_data.length > 0){
    for(var k=0; k<flag_data.length; k++){
      this.tag += '{"@value":"'+flag_data[k]+'"},'
    }
    this.tag = this.tag.substr(0,this.tag.length-1);
  }
  else{
    this.tag += '{"@value":"'+flaglist.flags+'"},';
  }
  this.tag += '],';

}

//Folder data
MAQueryConfig.prototype.setFolder = function(folderslist){
  if(folderslist.length <= 0){
    this.folder = null;
    return;
  }
  this.folder = '"folder":[';
  if(this.folder_recursive){
      var pfrec = ', "@recursive":"true"';
  }
  else
      var pfrec = '';
  
  if (folderslist.indexOf(',') != -1){
    var folder_data = folderslist.split(',');
    if(folder_data.length > 0){
        for(var k=0; k<folder_data.length; k++){
            this.folder += '{"@id":"'+folder_data[k]+'"' + pfrec + '},'
        }
        this.folder = this.folder.substr(0,this.folder.length-1);
    }
    else{
        this.folder += '{"@id":"'+folderslist+'"' + pfrec + '"}';
    }
  }
  else{
      this.folder += '{"@id":"' + folderslist +'"' + pfrec + '}';
  }
  this.folder += '],';
}

//Order criteria
MAQueryConfig.prototype.setOrder = function(order){
  if(typeof(order) != 'object'){
    this.order = null;
    return;
  }

  this.order = '"order":[';
  for(var k in order){
    if(k.toLowerCase().indexOf("date") != -1)
      this.order += '{"@date":"'+order[k]+'"},';
    if(k.toLowerCase().indexOf("from") != -1)
      this.order += '{"@from":"'+order[k]+'"},';
    if(k.toLowerCase().indexOf("subject") != -1)
      this.order += '{"@subject":"'+order[k]+'"},';
    if(k.toLowerCase().indexOf("size") != -1)
      this.order += '{"@size":"'+order[k]+'"},';
  }
  this.order = this.order.substr(0,this.order.length-1) + ']';
}

//Tags criteria
MAQueryConfig.prototype.setTags = function(taglist){
  if(typeof(taglist) != 'object'){
    this.tag = null;
    return;
  }
  //Adding tag criteria
  if(taglist.contains == "all"){
    this.tag = null;
  } else {
      this.tag = '"tags":[';
      //if(get_current_folder().indexOf("local") != "-1"){
          for(var k in taglist){
            if(k != null){
                    this.tag += '{"@contains":"'+taglist[k]+'"}],';
            }
          }
      //} 
  }
/*
  if(this.tag != null)
    this.tag = this.tag.substr(0, this.tag.length-2) + ', ';
  //Creating tag criteria
  else
    this.tag = '"tags":[';
  
  for(var k in taglist){
    if(k != null){
        if(k.toLowerCase() == "contains"){
            this.tag += '{"@contains":"'+taglist[k]+'"},';
        }
    }
  }
  //if no tag data was parametrizided, no one tag data will be returned
  if(this.tag.length > 8)
    this.tag = this.tag.substr(0,this.tag.length-1) + '],';
  else
    this.tag = null;
  //if no tag data was parametrizided, no one tag data will be returned
  */
}

//Body structrure
MAQueryConfig.prototype.setBody = function(data){
/*  if(body_data.length <= 0){
    this.body = null;
    return;
  }
  this.body = '"body":[';
  for(var k in body_data){
    if(k.toLowerCase() == "like")
      this.body += '{"@like":"'+body_data[k]+'"},';
    if(k.toLowerCase() == "likeic")
      this.body += '{"@likeIgnoreCase":"'+body_data[k]+'"},';
  }
  this.body = this.body.substr(0,this.body.length-1) + '],';
  */
  if(data.length <= 0){
    this.body = null;
    return;
  }
  
  //subdata = this.pharseCriteria('from', data);
  this.body = '"@body":"' + data + '",';  
}

MAQueryConfig.prototype.pharseSearchFields = function(fields){
   if(fields.length <= 0) 
       return;
   
   var filters = fields.replace(/^##|##$/g,"").split('##');
   if(filters.length > 0){
     var filter_object = '{' ;
     for(var k=0; k<filters.length; k++){
       var filter_entry = filters[k].split('<=>');
       filter_object += filter_entry[0].toLowerCase() + ':"' + filter_entry[1] + '", ';
     }
     filter_object = filter_object.substr(0, filter_object.length-2);
     filter_object += '}';
     return(filter_object);
   }
   else
       return;
}

MAQueryConfig.prototype.pharseFolders = function(folderslist){
    var folder_string_list = "";
    if (folderslist.length > 1){
        for(var k=0; k<folderslist.length; k++){
            var f_criteria = folderslist[k].split("#");
            var f_name = f_criteria[0];
            var f_recursive = f_criteria[1];
            folder_string_list += f_name + ',';
        }
        folder_string_list = folder_string_list.substr(0, folder_string_list.length-1) + '#' + f_recursive + ',';
        folder_string_list = folder_string_list.substr(0, folder_string_list.length-1);
    }
    else{
        folder_string_list = folderslist[0];
    }
    //window.alert('pharsefolders return = ' + folder_string_list);
    return(folder_string_list);
}

MAQueryConfig.prototype.getLowerIndex = function(){
  //  (this.lowerIndex != null) ? retdata = this.lowerIndex : retdata = '"@lowerIndex":"0", ';
    (this.lowerIndex != null) ? retdata = this.lowerIndex : retdata = '';
    return(retdata);
}

MAQueryConfig.prototype.getUpperIndex = function(){
//    (this.upperIndex != null) ? retdata = this.upperIndex : retdata = '"@upperIndex":"' + preferences.max_email_per_page+ '", ';
    (this.upperIndex != null) ? retdata = this.upperIndex : retdata = '';
    return(retdata);
}

MAQueryConfig.prototype.getFrom = function(){
    (this.from != null) ? retdata = this.from : retdata = "";
    return(retdata);
}

MAQueryConfig.prototype.getTo = function(){
    (this.to != null) ? retdata = this.to : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getCc = function(){
    (this.cc != null) ? retdata = this.cc : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getSubject = function(){
    (this.subject != null) ? retdata = this.subject : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getBody = function(){
    (this.body != null) ? retdata = this.body : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getDate = function(){
    (this.date != null) ? retdata = this.date : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getDateLower = function(){
    (this.dateLower != null) ? retdata = this.dateLower : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getDateUpper = function(){
    (this.dateUpper != null) ? retdata = this.dateUpper : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getTag = function(){
    (this.tag != null) ? retdata = this.tag : retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getFolder = function(){
    if (this.folder != null){
        if (this.folder_recursive){
            var tmp_root_id = this.folder.substr(this.folder.indexOf("@id")+6, 4);
            if(tmp_root_id == 'root')
                retdata = "";
            else
                retdata = this.folder;
        }
        else
            retdata = this.folder;
    }
    else retdata = "";
    return(retdata);    
}

MAQueryConfig.prototype.getOrder = function(){
    //defaults order criteria is date asc
    (this.order != null) ? retdata = this.order : retdata = '"order":[{"@date":"desc"}]';
    return(retdata);    
}

//Query criteria (core api)
MAQueryConfig.prototype.query = function(criterialist){
  //window.alert('MAQueryConfig.query\n\n - > CriteriaList:\n ' + criterialist + '[' + typeof(criterialist) + ']\ndump:\n' + print_r(criterialist));

  if (criterialist){
    this.setCriteriaList(criterialist);
  }
  
  //window.alert('em qc.query com lower = ' + this.lowerIndex + ' e upper = ' + this.upperIndex);
  var querystring = '{"query":{';
  querystring += this.getLowerIndex();
  querystring += this.getUpperIndex();
  querystring += this.getDateLower();
  querystring += this.getDateUpper();
  querystring += this.getDate();  
  querystring += this.getFrom();
  querystring += this.getTo();
  querystring += this.getCc();
  querystring += this.getSubject();
  querystring += this.getBody();
  querystring += this.getTag();
  querystring += this.getFolder();
  querystring += this.getOrder();
  
  var lastchar = querystring.substr(querystring.length-1,querystring.length);
  if(lastchar == ',')
      querystring = querystring.substr(0, querystring.length-1);
  
  querystring += '}}';
  //window.alert('queryconfig output\n\n' + querystring);
  return(querystring);
}

MAQueryConfig.prototype.reset = function(){
  this.lowerIndex = null;
  this.upperIndex = null;
  this.from = null;
  this.to = null;
  this.cc = null;
  this.subject = null;
  this.body = null;
  this.date = null;
  this.dateLower = null;  
  this.dateUpper = null;  
  this.tag = null;
  this.folder = null;
  this.order = null;
  this.preview_message = null;
  this.preview_tooltip = null;
  this.defaults = null;    
}

MAQueryConfig.prototype.pharseStdDate = function(date){
    var data_decoded = decodeURIComponent(date);
    var data_array = data_decoded.split("/");
    var data_day = data_array[0];
    var data_month = data_array[1];
    var data_year = data_array[2];

    var date_object = new Date();
    date_object.setDate(data_day);
    date_object.setMonth(data_month-1);
    date_object.setFullYear(data_year);
    
    var date_number = date_object.getTime();
    return(date_number);
    //return(new String(date_number)+'L');
}
-->