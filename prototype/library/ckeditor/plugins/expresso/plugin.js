

CKEDITOR.plugins.add('expresso',
{
    
    init: function (editor) {
        var pluginName = 'expresso';
        editor.ui.addButton('expAddImage',
            {
                label: 'Adicionar Imagem',
                command: 'imgDialog',
                icon: CKEDITOR.plugins.getPath('expresso') + 'img/Image.gif'
            });
                        
        editor.ui.addRichCombo('expSignature', 
        {
                    label: 'Assinaturas',
                    voiceLabel : "Assinaturas",
                    title: 'Assinaturas',
                    className : 'cke_format',
                    panel :
                    {
                           css : [ CKEDITOR.config.contentsCss, CKEDITOR.getUrl( editor.skinPath + 'editor.css' ) ],
                           voiceLabel : 'xala'
                    },

                    init : function()
                    {
                         var options = RichTextEditor.getSignaturesOptions();
                         for( var key in options )
                             this.add(  options[key],key,key);    
                                              
                    },
                   
                    onClick : function( value )
                    {
                        editor.focus();
                        editor.fire( 'saveSnapshot' );
                        var fontSize = '';
                        var fontFamily = '';
                        if(typeof(preferences.font_size_editor) !== 'undefined')
                            fontSize = 'font-size:' + preferences.font_size_editor;
                        if(fontSize != '') 
                            fontFamily = ';'
                        if(typeof(preferences.font_family_editor) !== 'undefined')
                            fontFamily += 'font-family:' + preferences.font_family_editor + ';';                         
                        var divBr = '<div style="'+fontSize+fontFamily+'"><br type="_moz"></div>';
                        editor.insertHtml(divBr + unescape(value));
                        editor.fire( 'saveSnapshot' );
                        var selection = editor.getSelection();
                        if(selection !== undefined && selection !== null){
                            var selectionRanges = selection.getRanges(); 
                        }
                        if(selection !== null){ 
                            if(selectionRanges[selectionRanges.length-1] !== undefined){
                                selectionRanges[selectionRanges.length-1].setStart(selectionRanges[selectionRanges.length-1].getTouchedStartNode().getParents()[1].getChild(0), 0);
                                selectionRanges[selectionRanges.length-1].setEnd(selectionRanges[selectionRanges.length-1].getTouchedStartNode().getParents()[1].getChild(0), 0);
                            }
                            selection.selectRanges(selectionRanges);
                        }
                        if (CKEDITOR.env.ie){
                            var body = editor.document.getBody();
                            var range = new CKEDITOR.dom.range(body);
                            range.selectNodeContents(body);
                            range.collapse(true);
                            var selection = editor.getSelection();
                            selection.selectRanges([range]);
                        }                       
                    }

        });
        
        
        editor.addCommand( 'imgDialog',new CKEDITOR.dialogCommand( 'imgDialog' ) );

		if ( editor.contextMenu )
		{
			editor.addMenuGroup( 'mygroup', 10 );
			editor.addMenuItem( 'My Dialog',
			{
				label : 'Adicionar imagem',
				command : 'imgDialog',
				group : 'mygroup',
                icon: CKEDITOR.plugins.getPath('expresso') + 'img/Image.gif'
			});
			editor.contextMenu.addListener( function( element )
			{
 				return {'My Dialog' : CKEDITOR.TRISTATE_OFF};
			});
		}
                
		CKEDITOR.dialog.add( 'imgDialog', function( api )
		{
            var ID = currentTab;
			// CKEDITOR.dialog.definition
			var dialogDefinition =
			{
				
                title : 'Inserir Imagem',
				minWidth : 400,
				minHeight : 70,
				contents : [
					{
						id : 'tab1',
						label : 'Label',
						title : 'Title',
						expand : true,
						padding : 0,
						elements :
						[
							{
								type : 'html',
								html :  '<form id="fileupload_img'+ID+'" class="fileupload" action="mailAttachment:img" method="POST">    <input type="file" name="files[]"  onclick="bindFileUpload(this);" style="margin-left:10px"></form>' 
							}
						]
					}
				],
				buttons : [ CKEDITOR.dialog.cancelButton]
				
			};
                        	
			return dialogDefinition;
		} );
	
         

    }
});
function bindFileUpload(e) {
	var ID = currentTab;
	var fileUploadIMG = $(e).parents('form');
	var fileUploadMSG = $('#fileupload_msg'+ID);
	var maxAttachmentSize = (preferences.max_attachment_size !== "" && preferences.max_attachment_size != 0) ? (parseInt(preferences.max_attachment_size.replace('M', '')) * 1048576 ) : false;
	fileUploadIMG.fileupload({
		type: 'post',
		dataType : 'json',
		url: "../prototype/post.php",
		forceIframeTransport: true,
		formData: function(form) {
			return [
				{
					name : "mailAttachment[0][source]",
					value : "files0"
				},
				{
					name : "mailAttachment[0][disposition]",
					value : $('#attDisposition'+ID).val()
				},
				{
					name: "MAX_FILE_SIZE",
					value : maxAttachmentSize
				}
			];
		},
		add: function (e, data) {
            var iterator = idattachbycontent;
			if(!maxAttachmentSize || data.files[0].size < maxAttachmentSize || is_ie) {
				setTimeout(function() {
                                        $('#attDisposition'+ID).val('embedded');
					jqXHR[iterator] = data.submit();
				}, 100);
			}
            fileUploadMSG.find(' .attachments-list').show();
			$.each(data.files, function (index, file) {	
				var attach = {};
				attach.fullFileName = file.name;
				attach.fileName = file.name;
				if(file.name.length > 20)
					attach.fileName = file.name.substr(0, 17) + "..." + file.name.substr(file.name.length-9, file.name.length);
				attach.fileSize = formatBytes(file.size);
				if(maxAttachmentSize && file.size > maxAttachmentSize)
					attach.error = 'Tamanho de arquivo nao permitido!!'
				else
                    attach.error = true;
				var upload = $(DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist.ejs", {file : attach}));	

				upload.find('.att-box-delete').click(function(){
					var idAttach = $(this).parent().find('input[name="fileId[]"]').val();
			
                    var content_body = RichTextEditor.getData('body_'+ID);
                    var imagens = content_body.match(/<img[^>]*>/g);
       
                    if(imagens != null)
                        for (var x = 0; x < imagens.length; x++)
                            if(imagens[x].indexOf('src="../prototype/getArchive.php?mailAttachment='+idAttach+'"') !== -1)
                                content_body = content_body.replace(imagens[x],'');
         
                    RichTextEditor.setData('body_'+ID,content_body);   
                                       	
                    $('.attachments-list').find('input[value="'+idAttach+'"]').remove();
                    delAttachment(ID, idAttach);
					$(this).parent().qtip("destroy");
                    $(this).parent().remove();
                    if(!fileUploadMSG.find(' .attachments-list').find(".att-box").length){
                        fileUploadMSG.find(' .attachments-list').hide();
                    }
                    if(jqXHR){
                        jqXHR[iterator].abort();
                    }
				});
                                
                fileUploadMSG.find('.attachments-list').append(upload);
                fileUploadMSG.find('.attachments-list .att-box:last').qtip({
                    content: DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist_tooltip.ejs", {attach : attach}),
                    position: {
                        corner: {
                            tooltip: 'bottomMiddle',
                            target: 'topMiddle'
                        },
                        adjust: {
                           resize: true,
                           scroll: true,
                           screen: true
                        }
                    },
                    show: {
                        when: 'mouseover', // Don't specify a show event
                        ready: false // Show the tooltip when ready
                    },
                    hide: 'mouseout', // Don't specify a hide event
                    style: {
                        border: {
                            width: 1,
                            radius: 5
                        },
                        width: {
                             min: 75,
                             max : 1000
                        },
                        padding: 3, 
                        textAlign: 'left',
                        tip: true, // Give it a speech bubble tip with automatic corner detection
                        name: (typeof(attach.error) == 'boolean' ? 'light' : 'red') // Style it according to the preset 'cream' style
                    }
                });
    			if(!maxAttachmentSize || file.size < maxAttachmentSize){
    				if(data.fileInput){
    					fileUploadMSG.find('.fileinput-button.new').append(data.fileInput[0]).removeClass('new');
    					fileUploadMSG.find('.attachments-list').find('[type=file]').addClass('hidden');	
    				}
    			}else{
    				fileUploadMSG.find(' .fileinput-button.new').removeClass('new');
    			}	                                
                CKEDITOR.instances['body_'+ID].insertHtml('<img src=""/>');
                idattachbycontent++; 
			});
                
            CKEDITOR.dialog.getCurrent().hide();	
                       
		},
		done: function(e, data){
            var attach_box = fileUploadMSG.find('.att-box-loading:first').parents('.att-box');
            var attach = {
                fullFileName : attach_box.find(".att-box-fullfilename").text(),
                fileSize : attach_box.find(".att-box-filesize").text(),
                OK : true,
                error : false
            };
            if(!!data.result && data.result != "[]" ){
                var newAttach = data.result;                             
                if(!newAttach.mailAttachment.error || newAttach.rollback !== false){
					attach_box.append('<input type="hidden" name="fileId[]" value="'+newAttach['mailAttachment'][0][0].id+'"/>');
					addAttachment(ID,newAttach['mailAttachment'][0][0].id);
					var content_body  = RichTextEditor.getData('body_'+ID);
					var rex = new RegExp('<img src="" [^\/>]*\/>', 'i'); 
					var newImg = '<img src="../prototype/getArchive.php?mailAttachment='+newAttach['mailAttachment'][0][0].id+'" />'; 
					content_body = content_body.replace(rex,newImg); 
					RichTextEditor.setData('body_'+ID,content_body); 
				}else{
					attach_box.addClass('invalid-email-box');
                    attach.error = newAttach.mailAttachment.error ? newAttach.mailAttachment.error : 'Erro ao anexar...';
                }
			}else {
                attach_box.addClass('invalid-email-box');
                attach.error = 'Erro ao anexar...';
            }
            attach_box.qtip("destroy").qtip({
                content: DataLayer.render("../prototype/modules/mail/templates/attachment_add_itemlist_tooltip.ejs", {attach : attach}),
                position: {
                    corner: {
                        tooltip: 'bottomMiddle',
                        target: 'topMiddle'
                    },
                    adjust: {
                       resize: true,
                       scroll: true,
                       screen: true
                    }
                },
                show: {
                    when: 'mouseover', // Don't specify a show event
                    ready: false // Show the tooltip when ready
                },
                hide: 'mouseout', // Don't specify a hide event
                style: {
                    border: {
                        width: 1,
                        radius: 5
                    },
                    width: {
                         min: 75,
                         max : 1000
                    },
                    padding: 3, 
                    textAlign: 'left',
                    tip: true, // Give it a speech bubble tip with automatic corner detection
                    name: (attach.error == false ? 'blue' : 'red')// Style it according to the preset 'cream' style
                }
            });
            fileUploadMSG.find(' .att-box-loading:first').remove();
		}
	});
}
