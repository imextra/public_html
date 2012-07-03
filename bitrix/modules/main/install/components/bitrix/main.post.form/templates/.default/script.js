function __onKeyTags(event)
{
	if (!event)
		event = window.event;
	var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null));
    if (key == 13)
        addTag();
}

function BXfpdSetLinkName(name)
{
	if (BX.SocNetLogDestination.getSelectedCount(name) <= 0)
		BX('bx-destination-tag').innerHTML = BX.message("BX_FPD_LINK_1");
	else
		BX('bx-destination-tag').innerHTML = BX.message("BX_FPD_LINK_2");
}

function BXfpdSelectCallback(item, type, search)
{ 
	var type1 = type;
	prefix = 'S';
	if (type == 'sonetgroups')
		prefix = 'SG';
	else if (type == 'groups')
	{
		prefix = 'UA';
		type1 = 'all-users';
	}
	else if (type == 'users')
		prefix = 'U';	
	else if (type == 'department')
		prefix = 'DR';

	BX('feed-add-post-destination-item').appendChild(
		BX.create("span", { attrs : { 'data-id' : item.id }, props : { className : "feed-add-post-destination feed-add-post-destination-"+type1 }, children: [
			BX.create("input", { attrs : { 'type' : 'hidden', 'name' : 'SPERM['+prefix+'][]', 'value' : item.id }}),
			BX.create("span", { props : { className : "feed-add-post-destination-text" }, html : item.name}),
			BX.create("span", { props : { className : "feed-add-post-del-but"}, events : {'click' : function(e){BX.SocNetLogDestination.deleteItem(item.id, type, BXSocNetLogDestinationFormName);BX.PreventDefault(e)}, 'mouseover' : function(){BX.addClass(this.parentNode, 'feed-add-post-destination-hover')}, 'mouseout' : function(){BX.removeClass(this.parentNode, 'feed-add-post-destination-hover')}}})
		]})
	);
	
	BX('feed-add-post-destination-input').value = '';
	BXfpdSetLinkName(BXSocNetLogDestinationFormName);
}

// remove block
function BXfpdUnSelectCallback(item, type, search)
{ 
	var elements = BX.findChildren(BX('feed-add-post-destination-item'), {attribute: {'data-id': ''+item.id+''}}, true);
	if (elements != null)
	{
		for (var j = 0; j < elements.length; j++) 
			BX.remove(elements[j]);
	}
	BX('feed-add-post-destination-input').value = '';
	BXfpdSetLinkName(BXSocNetLogDestinationFormName);
}
function BXfpdOpenDialogCallback()
{ 
	BX.style(BX('feed-add-post-destination-input-box'), 'display', 'inline-block');
	BX.style(BX('bx-destination-tag'), 'display', 'none');
	BX.focus(BX('feed-add-post-destination-input'));
}

function BXfpdCloseDialogCallback()
{ 
	if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-add-post-destination-input').value.length <= 0)
	{
		BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
		BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
		BXfpdDisableBackspace();
	}
}

function BXfpdCloseSearchCallback()
{ 
	if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-add-post-destination-input').value.length > 0)
	{
		BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
		BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
		BX('feed-add-post-destination-input').value = '';
		BXfpdDisableBackspace();
	}
	
}
function BXfpdDisableBackspace(event)
{
	if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null)
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
		
	BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(event){
		if (event.keyCode == 8)
		{
			BX.PreventDefault(event);
			return false;
		}
	});
	setTimeout(function(){
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
		BX.SocNetLogDestination.backspaceDisable = null;
	}, 5000);
}

function BXfpdSearchBefore(event)
{
	if (event.keyCode == 8 && BX('feed-add-post-destination-input').value.length <= 0)
	{
		BX.SocNetLogDestination.sendEvent = false;
		BX.SocNetLogDestination.deleteLastItem(BXSocNetLogDestinationFormName);
	}

	return true;	
}
function BXfpdSearch(event)
{ 
	if(event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18)
		return false;

	if (event.keyCode == 13)
	{
		BX.SocNetLogDestination.selectFirstSearchItem(BXSocNetLogDestinationFormName);
		return true;
	}
	if (event.keyCode == 27)
	{
		BX('feed-add-post-destination-input').value = '';
		BX.style(BX('bx-destination-tag'), 'display', 'inline');
	}
	else
	{
		BX.SocNetLogDestination.search(BX('feed-add-post-destination-input').value, true, BXSocNetLogDestinationFormName);
	}

	if (!BX.SocNetLogDestination.isOpenDialog() && BX('feed-add-post-destination-input').value.length <= 0)
	{
		BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName);
	}
	else
	{
		if (BX.SocNetLogDestination.sendEvent && BX.SocNetLogDestination.isOpenDialog())
			BX.SocNetLogDestination.closeDialog();
	}
	if (event.keyCode == 8)
	{
		BX.SocNetLogDestination.sendEvent = true;
	}
	return true;
}

function CustomizeLightEditorForBlog(editorId, params)
{
	LHEButtons['BlogImage'] ={
		id : 'Image', // Standart image icon from editor-s CSS
		name : LHE_MESS.Image,
		handler: function(pBut)
		{
			//pBut.pLEditor.OpenDialog({id : 'BlogImage'+editorId, obj: false});
			edId = editorId.replace('LHEPostFormId_', '');
			eval('showImageFile_'+edId+'();');
		},
		OnBeforeCreate: function(pLEditor, pBut)
			{
				// Disable in non BBCode mode in html
				pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
				return pBut;
			},
		parser: {
			name: 'blogimage',
			obj: {
				Parse: function(sName, sContent, pLEditor)
				{
					var i, cnt = arImagesId.length, j;
					if (!pLEditor.arBlogImages)
						pLEditor.arBlogImages = {};
					//if (!pLEditor.pBlogPostImage)
						//pLEditor.pBlogPostImage = BX('blog-post-image');

					for(i = 0; i < cnt; i++)
					{
						if (!pLEditor.arBlogImages[arImagesId[i]])
						{
							pLEditor.arBlogImages[arImagesId[i]] = {src : arImagesSrc[i]};
						}
					}

					sContent = sContent.replace(/\[IMG ID=((?:\s|\S)*?)(?:\s*?WIDTH=(\d+)\s*?HEIGHT=(\d+))?\]/ig, function(str, id, width, height)
					{
						if (!pLEditor.arBlogImages[id])
							return str;

						width = parseInt(width);
						height = parseInt(height);

						var
							strSize = "",
							imageSrc = pLEditor.arBlogImages[id].src;

						if (width && height && pLEditor.bBBParseImageSize)
							strSize = " width=\"" + width + "\" height=\"" + height + "\"";

						return '<img id="' + pLEditor.SetBxTag(false, {tag: "blogimage", params: {value : id}}) + '" src="' + imageSrc + '" title="" ' + strSize +'>';
					});
					return sContent;
				},
				UnParse: function(bxTag, pNode, pLEditor)
				{	
					if (bxTag.tag == 'blogimage')
					{
						var
							width = parseInt(pNode.arAttributes['width']),
							height = parseInt(pNode.arAttributes['height']),
							strSize = "";

						if (width && height  && pLEditor.bBBParseImageSize)
							strSize = ' WIDTH=' + width + ' HEIGHT=' + height;

						return '[IMG ID=' + bxTag.params.value + strSize + ']';
					}
					return "";
				}
			}
		}
	};

	// Rename image button and change Icon
	LHEButtons['Image'].id = 'ImageLink';
	LHEButtons['Image'].src = '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_image_upload.gif';
	LHEButtons['Image'].name = params.imageLinkText;

	LHEButtons['BlogInputVideo'] = {
		id : 'BlogInputVideo',
		src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_video.gif',
		name : params.videoText,
		handler: function(pBut)
		{
			pBut.pLEditor.OpenDialog({id : 'BlogVideo', obj: false});
		},
		OnBeforeCreate: function(pLEditor, pBut)
			{
				// Disable in non BBCode mode in html
				pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
				return pBut;
			},
		parser: {
			name: 'blogvideo',
			obj: {
				Parse: function(sName, sContent, pLEditor)
				{
					sContent = sContent.replace(/\[VIDEO\s*?width=(\d+)\s*?height=(\d+)\s*\]((?:\s|\S)*?)\[\/VIDEO\]/ig, function(str, w, h, src)
					{
						var
							w = parseInt(w) || 400,
							h = parseInt(h) || 300,
							src = BX.util.trim(src);

						return '<img id="' + pLEditor.SetBxTag(false, {tag: "blogvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + LHE_MESS.Video + ": " + src + '" />';
					});
					return sContent;
				},
				UnParse: function(bxTag, pNode, pLEditor)
				{
					if (bxTag.tag == 'blogvideo')
					{
						return "[VIDEO WIDTH=" + pNode.arAttributes["width"] + " HEIGHT=" + pNode.arAttributes["height"] + "]" + bxTag.params.value + "[/VIDEO]";
					}
					return "";
				}
			}
		}
	};

	LHEButtons['BlogTag'] = {
		id : 'BlogTag',
		src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_video.gif',
		name : params.videoText,
		handler: function(){},
		parser: {
			name: 'blogtag',
			obj: {
				Parse: function(sName, sContent, pLEditor)
				{
					sContent = sContent.replace(/\[TAG\s*\]((?:\s|\S)*?)\[\/TAG\]/ig, function(str, name)
					{
						var
							name = BX.util.trim(name);

						return '<span id="' + pLEditor.SetBxTag(false, {tag: "blogtag", params: {value : name}}) + '" style="background-color:#f0f0f0;">#' + name + '</span>';
					});
					return sContent;
				},
				UnParse: function(bxTag, pNode, pLEditor)
				{
					if (bxTag.tag == 'blogtag')
					{
						return "[TAG]" + bxTag.params.value + "[/TAG]";
					}
					return "";
				}
			}
		}
	};

	LHEButtons['BlogUser'] = {
		id : 'BlogUser',
		src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_video.gif',
		name : params.videoText,
		handler: function (){},
		parser: {
			name: 'bloguser',
			obj: {
				Parse: function(sName, sContent, pLEditor)
				{
					sContent = sContent.replace(/\[USER\s*=\s*(\d+)\]((?:\s|\S)*?)\[\/USER\]/ig, function(str, id, name)
					{
						var
							id = parseInt(id),
							name = BX.util.trim(name);

						return '<span id="' + pLEditor.SetBxTag(false, {tag: "bloguser", params: {value : id}}) + '" style="color: #2067B0; border-bottom: 1px dashed #2067B0;">' + name + '</span>';
					});
					return sContent;
				},
				UnParse: function(bxTag, pNode, pLEditor)
				{
					if (bxTag.tag == 'bloguser')
					{
						var name = '';
						for (var i = 0; i < pNode.arNodes.length; i++)
							name += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);
						name = BX.util.trim(name);
						return "[USER=" + bxTag.params.value + "]" + name +"[/USER]";
					}
					return "";
				}
			}
		}
	};

	window.LHEDailogs['BlogImage'+editorId] = function(pObj)
	{
		var str = 
			'<span class="errortext" id="lhed_blog_image_error" style="display:none;"></span>' +
			'<table width="100%"><tr>' +
			'<td class="lhe-dialog-label lhe-label-imp">' + params.imageText + ':</td>' +
			'<td class="lhe-dialog-param">' +
			'<form id="' + pObj.pLEditor.id + 'img_upload_form" action="' + params.postFormActionUri + '" method="post" enctype="multipart/form-data" style="margin: 0!important; padding: 0!important;">' +
			params.bitrixSessid +
			'<input type="file" size="30" name="BLOG_UPLOAD_FILE" id="bx_lhed_blog_img_input" />' +
			window['arWindowImagesFields_'+editorId] +
			'</form>'+
			'</td>' +
			'</tr><tr id="' + pObj.pLEditor.id + 'lhed_blog_notice">' +
			'<td colSpan="2" style="padding: 0 0 20px 25px !important; font-size: 11px!important;">' + params.imageSizeNotice + '</td>' +
		'</tr></table>';

		return {
			title: params.imageUploadText,
			innerHTML : str,
			width: 500,
			OnLoad: function()
			{
				pObj.pForm = false;
				pObj.pInput = false;

				pObj.pInput = BX('bx_lhed_blog_img_input');
				pObj.pForm = BX(pObj.pLEditor.id + 'img_upload_form');
				pObj.pLEditor.focus(pObj.pInput);
				if(BX('postId') && BX('igm_comment_post_id'))
					BX('igm_comment_post_id').value = BX('postId').value;
				
				window.obLHEDialog.adjustSizeEx();
			},
			OnSave: function()
			{
				if (pObj.pInput && pObj.pForm && pObj.pInput.value != "")
				{
					BX.showWait('bx_lhed_blog_img_input');
					BX.hide(BX('lhed_blog_image_error'));
					BX('lhed_blog_image_error').innerHTML = '';
					BX.ajax.submit(pObj.pForm, function(){
						BX.closeWait();
						if (window.bxBlogImageId)
						{
							
							window['InsertBlogImage_'+editorId](window.bxBlogImageId, window.bxBlogImageIdSrc, window.bxBlogImageIdWidth);
							window.obLHEDialog.Close();
							window.bxBlogImageId = false;
							window.bxBlogImageIdWidth = false;
							window.bxBlogImageIdSrc = false;
						}
						else if(window.bxBlogImageError)
						{
							BX('lhed_blog_image_error').innerHTML = window.bxBlogImageError;
							BX.show(BX('lhed_blog_image_error'));
							window.obLHEDialog.adjustSizeEx();
						}
					});

					return false;
				}
			}
		};
	};

	window.LHEDailogs['BlogVideo'] = function(pObj)
	{
		var str = '<table width="100%"><tr>' +
			'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_path"><b>' + params.videoUploadText + ':</b></label></td>' +
			'<td class="lhe-dialog-param">' +
			'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_path" value="" size="30"/>' +
			'</td>' +
		'</tr><tr>' +
			'<td></td>' +
			'<td style="padding: 0!important; font-size: 11px!important;">' + params.videoUploadText1 + '</td>' +
		'</tr><tr>' +
			'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_width">' + LHE_MESS.ImageSizing + ':</label></td>' +
			'<td class="lhe-dialog-param">' +
				'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_width" value="" size="4"/>' +
				' x ' +
				'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_height" value="" size="4" />' +
			'</td>' +
		'</tr></table>';

		return {
			title: params.videoUploadText2,
			innerHTML : str,
			width: 480,
			OnLoad: function()
			{
				pObj.pPath = BX(pObj.pLEditor.id + "lhed_blog_video_path");
				pObj.pWidth = BX(pObj.pLEditor.id + "lhed_blog_video_width");
				pObj.pHeight = BX(pObj.pLEditor.id + "lhed_blog_video_height");

				pObj.pLEditor.focus(pObj.pPath);
			},
			OnSave: function()
			{
				var
					src = BX.util.trim(pObj.pPath.value),
					w = parseInt(pObj.pWidth.value) || 400,
					h = parseInt(pObj.pHeight.value) || 300;

				if (src == "")
					return;

				if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode) // BB Codes
				{
					pObj.pLEditor.WrapWith("", "", "[VIDEO WIDTH=" + w + " HEIGHT=" + h + "]" + src + "[/VIDEO]");
				}
				else if(pObj.pLEditor.sEditorMode == 'html') // WYSIWYG
				{
					pObj.pLEditor.InsertHTML('<img id="' + pObj.pLEditor.SetBxTag(false, {tag: "blogvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + LHE_MESS.Video + ": " + src + '" />');
					pObj.pLEditor.AutoResize();
				}
			}
		};
	};
}