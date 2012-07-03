<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(CModule::IncludeModule("fileman"))
{
	?>
	<script>
	if(!arImagesId)
		var arImagesId = Array();
	if(!arImagesSrc)
		var arImagesSrc = Array();
	var arWindowImagesFields_LHEPostFormId_<?=$arParams["FORM_ID"]?> = '<?=htmlspecialcharsback($arParams["IMAGES"]["ADIT_FIELDS"])?>';
	<?
	$i = 0;
	foreach($arParams["IMAGES"]["VALUE"] as $aImg)
	{
		?>
		arImagesId.push('<?=$aImg["ID"]?>');
		arImagesSrc.push('<?=CUtil::JSEscape($aImg["SRC"])?>');
		<?
		$i++;
	}
	?>
	
	InsertBlogImage_LHEPostFormId_<?=$arParams["FORM_ID"]?> = function(imageId, src, width)
	{
		pLEditor = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>;
		var strSize = '';
		if (!pLEditor.arBlogImages[imageId])
		{
			pLEditor.arBlogImages[imageId] = {src : src};
		}
		
		if(pLEditor.arBlogImages[imageId].src)
		{
			if(width > 0)
			{
				if(pLEditor.arConfig.width && pLEditor.arConfig.width.indexOf('%') <= 0)
					widthC = parseInt(pLEditor.arConfig.width)*0.8;
				else
					widthC = 800;
				if(width > widthC)
					strSize = ' width="80%"';
			}

			if (pLEditor.sEditorMode == 'code' && pLEditor.bBBCode) // BB Codes
				pLEditor.WrapWith("", "", "[IMG ID=" + imageId + "]");
			else if(pLEditor.sEditorMode == 'html') // WYSIWYG
			{
				pLEditor.InsertHTML('<img id="' + pLEditor.SetBxTag(false, {tag: "blogImage", params: {value : imageId}}) + '" src="' + pLEditor.arBlogImages[imageId].src + '" title=""' + strSize + '>');
				setTimeout('window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.AutoResize();', 500);
			}
		}
	}

	// Submit form by ctrl+enter
	window.postFormCtrlEnterHandler_<?=$arParams["FORM_ID"]?> = function(e)
	{
		oPostFormLHE_<?=$arParams["FORM_ID"]?>.SaveContent();
		<?
		if(strlen($arParams["JS_SUBMIT"]) > 0)
			echo $arParams["JS_SUBMIT"];
		else
		{
			?>BX.submit(BX('<?=$arParams["FORM_ID"]?>'));<?
		}
		?>
	};

	BX('<?=$arParams["FORM_ID"]?>').onsubmit = function()
	{
		oPostFormLHE_<?=$arParams["FORM_ID"]?>.SaveContent();
		<?if(!empty($arParams["TITLE"]))
		{
			?>
			if(BX('blog-title').style.display == "none")
				BX('POST_TITLE').value = "";
			<?
		}
		if(strlen($arParams["FORM_ON_SUBMIT"]) > 0)
			echo $arParams["FORM_ON_SUBMIT"];
		?>
	};

	function showFile_<?=$arParams["FORM_ID"]?>()
	{
		BX.onCustomEvent(BX('<?=$arParams["FORM_ID"]?>'), '<?=(CModule::IncludeModule("webdav") ?  "WDLoadFormController" : "BFileDLoadFormController");?>');
	}
	<?
	if(CModule::IncludeModule("webdav"))
	{
		?>BX.addCustomEvent(BX('<?=$arParams["FORM_ID"]?>'), 'BFileDLoadFormController', function(){BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'className': 'file-label' }, true, false).innerHTML = '<?=GetMessage("BLOG_P_PHOTO")?>';});<?
	}
	?>
	function showImageFile_<?=$arParams["FORM_ID"]?>()
	{
		BX.onCustomEvent(BX('<?=$arParams["FORM_ID"]?>'), 'BFileDLoadFormController');
	}
	</script>
	<?
	if(!function_exists('CustomizeLightEditorForBlog'))
	{
		function CustomizeLightEditorForBlog($id)
		{
			?>
			<script>
			var params  = {
				imageLinkText: '<?=GetMessage("BLOG_P_IMAGE_LINK")?>',
				videoText: '<?=GetMessage("FPF_VIDEO")?>',
				imageText: '<?= GetMessage('BLOG_IMAGE')?>',
				postFormActionUri: '<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
				bitrixSessid: '<?=bitrix_sessid_post()?>',
				imageSizeNotice: '<?= GetMessage('BPC_IMAGE_SIZE_NOTICE', Array('#SIZE#' => DoubleVal(COption::GetOptionString("blog", "image_max_size", 5000000)/1000000)))?>',
				imageUploadText: '<?= GetMessage('BLOG_P_IMAGE_UPLOAD')?>',
				videoUploadText: '<?= GetMessage('BPC_VIDEO_P')?>',
				videoUploadText1: '<?= GetMessage('BPC_VIDEO_PATH_EXAMPLE')?>',
				videoUploadText2: '<?= GetMessage('FPF_VIDEO')?>'
				};
			CustomizeLightEditorForBlog('<?=$id?>', params);
			</script>
			<?
		}
	}
	AddEventHandler("fileman", "OnIncludeLightEditorScript", "CustomizeLightEditorForBlog");
	?>
	<div id="edit-post-text">
	<?
	$arParams["TEXT"]["HTML"]["BUTTONS"][] = "SmileList";
	$LHE = new CLightHTMLEditor;
	$LHE->Show(array(
		'id' => 'LHEPostFormId_'.$arParams["FORM_ID"],
		//'width' => '800', // default 100%
		'height' => $arParams["TEXT"]["HTML"]["HEIGHT"],
		'inputId' => 'POST_MESSAGE',
		'inputName' => $arParams["TEXT"]["NAME"],
		'content' => htmlspecialcharsBack($arParams["TEXT"]["VALUE"]),
		'bUseFileDialogs' => false,
		'bUseMedialib' => false,
		'toolbarConfig' => $arParams["TEXT"]["HTML"]["BUTTONS"],
		'jsObjName' => 'oPostFormLHE_'.$arParams["FORM_ID"],
		'arSmiles' => $arParams["SMILES"]["VALUE"],
		'smileCountInToolbar' => $arParams['SMILES_COUNT'],
		'bSaveOnBlur' => false,
		'BBCode' => true,
		'bConvertContentFromBBCodes' => false, 
		'bQuoteFromSelection' => true, // Make quote from any text in the page
		'ctrlEnterHandler' => 'postFormCtrlEnterHandler_'.$arParams["FORM_ID"], // Ctrl+Enter handler name in global namespace
		'bSetDefaultCodeView' => false, // Set first view to CODE or to WYSIWYG
		'bBBParseImageSize' => true, // [IMG ID=XXX WEIGHT=5 HEIGHT=6],  [IMGWEIGHT=5 HEIGHT=6]/image.gif[/IMG]
		'documentCSS' => $arParams["LHE_STYLES"],
		'bResizable' => true,
		'bAutoResize' => true,
		'autoResizeOffset' => 40,
		//'autoResizeMaxHeight' => 300,
		'controlButtonsHeight' => '34',
		'autoResizeSaveSize' => false,

	));
	?></div><?
}
?>
<script>
var bShow<?=$arParams["FORM_ID"]?> = false;
function PostFormCheckLHE_<?=$arParams["FORM_ID"]?>()
{
	if(window.oPostFormLHE_<?=$arParams["FORM_ID"]?>)
	{
		BX.addCustomEvent(window.oPostFormLHE_<?=$arParams["FORM_ID"]?>, 'OnDocumentKeyDown', function(e){bxPFParser<?=$arParams["FORM_ID"]?>(e)});

		if(!bShow<?=$arParams["FORM_ID"]?>)
			bShow<?=$arParams["FORM_ID"]?> = true;

		<?
		/*
		if(COption::GetOptionString("blog", "use_autosave", "Y") == "Y")
		{
			?>BlogPostAutoSaveIcon();<?
		}
		*/
	
		if($arParams["TEXT"]["HTML"]["SHOW_DEFAULT"] == "Y")
		{
			?>showPanel_<?=$arParams["FORM_ID"]?>('html', true);<?
		}
		else
		{
			?>showPanel_<?=$arParams["FORM_ID"]?>('html', false);<?
		}

		if(in_array("CreateLink", $arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"]))
		{
			?>makeButton_<?=$arParams["FORM_ID"]?>('lhe_btn_createlink', 'bx-b-link');<?
		}
		if(in_array("BlogImage", $arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"]))
		{
			?>makeButton_<?=$arParams["FORM_ID"]?>('lhe_btn_image', 'bx-b-img');<?
		}
		if(in_array("BlogInputVideo", $arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"]))
		{
			?>makeButton_<?=$arParams["FORM_ID"]?>('lhe_btn_bloginputvideo', 'bx-b-video');<?
		}
		if(in_array("BlogUser", $arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"]))
		{
			/*?>makeButton_<?=$arParams["FORM_ID"]?>('lhe_btn_bloguser', 'bx-b-mention');<?*/
			?>
			var el = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'lhe_btn_bloguser'}}, true, false);
			BX.remove(BX.findParent(el), true);
			<?
		}
		
		?>
		if(el = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'lhe_btn_smilelist'}}, true, false))
			BX.remove(BX.findParent(el), true);

		
		var el = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'bx-panel-close'}}, true, false);
		BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'className': /lhe-stat-toolbar-cont/ }, true, false).appendChild(el);
	}
	else
		setTimeout("PostFormCheckLHE_<?=$arParams["FORM_ID"]?>()", 100);
}
setTimeout("PostFormCheckLHE_<?=$arParams["FORM_ID"]?>()", 100);

function DeleteImage(id, el)	
{
	url = '<?=CUtil::JSEscape($arParams["IMAGES"]["DEL_LINK"])?>';
	url1 = url.replace('#del_image_id#', id);
	BX.remove(el.parentNode);
	BX.ajax.get(url1, function(data){});
	return false;
}

function makeButton_<?=$arParams["FORM_ID"]?>(oldb, newb)
{
	var el = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: oldb}}, true, false);
	BX.remove(BX.findParent(el), true);
	BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: newb}}, true, false).appendChild(el);
	
	el.style.backgroundImage = 'url(/bitrix/images/1.gif)';
	el.src = '/bitrix/images/1.gif';
	el.style.width = '25px';
	el.style.height = '25px';
	el.onmouseout = '';
	el.onmouseover = '';
	el.className = '';
}

function deleteTag(val, el)
{
	BX.remove(el, true);
	BX('tags-hidden').value = BX('tags-hidden').value.replace(val+',', '');
	BX('tags-hidden').value = BX('tags-hidden').value.replace('  ', ' ');
}

function addTag()
{
	tagInput = BX.findChild(BX('blog-tags-input'), {'tag': 'input' });
	var tags = tagInput.value.split(",");
	for (var i = 0; i < tags.length; i++ )
	{
		var tag = BX.util.trim(tags[i]);
		if(tag.length > 0)
		{
			var allTags = BX('tags-hidden').value.split(",");
			if(!BX.util.in_array(tag, allTags))
			{
				el = BX.create('SPAN', {html: BX.util.htmlspecialchars(tag) + '<span class="feed-add-post-del-but" onclick="deleteTag(\'' + BX.util.htmlspecialchars(tag) + '\', this.parentNode)"></span>', attrs : {className: 'feed-add-post-tags'}});
				BX('blog-tags-container').insertBefore(el, BX('bx-blog-tag'));
				BX('tags-hidden').value += tag + ',';
			}
		}
	}

	tagInput.value = '';
	popupTag.close();
}

function showPanel_<?=$arParams["FORM_ID"]?>(panel, show)
{
	if(panel == "title")
	{
		if(BX('blog-title').style.display == "none" || show)
			BX.show(BX('blog-title'));
		else
			BX.hide(BX('blog-title'));
	}
	else if(panel == "html")
	{
		formHeaders = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'className': /bxlhe-editor-buttons/ }, true, true);
		if (formHeaders.length >= 1)
			var p = formHeaders[formHeaders.length-1].parentNode;

		if(p.style.display == "none" || show)
		{
			if(p)
				p.style.display = "table-row";
			window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.buttonsHeight = 34;
			window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.ResizeFrame();
		}
		else
		{
			if(p)
				BX.hide(p);
			window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.buttonsHeight = 0;
			window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.ResizeFrame();
		}
	}
}

var bMentListen = false;
var bPlus = false;

function BXfixIERangeObject(range, win) //Only for IE8 and below.
{ 
	win = win || window;

	if(!range) 
		return null;
	if(!range.startContainer && win.document.selection) //IE8 and below
	{
		var _findTextNode = function(parentElement,text) 
		{
			var container=null, 
				offset=-1;
			for(var node = parentElement.firstChild; node; node = node.nextSibling) 
			{
				if(node.nodeType == 3) 
				{
					var find = node.nodeValue,
						pos = text.indexOf(find);
					if(pos == 0 && text != find) 
					{
						text = text.substring(find.length);
					} 
					else
					{
						container = node;
						offset = text.length-1;
						break;
					}
				}
			}
			return {node: container, offset: offset};
		}

		var rangeCopy1 = range.duplicate(), 
			rangeCopy2 = range.duplicate(),
			rangeObj1 = range.duplicate(),
			rangeObj2 = range.duplicate();

		rangeCopy1.collapse(true);
		rangeCopy1.moveEnd('character', 1);
		rangeCopy2.collapse(false);
		rangeCopy2.moveStart('character', -1);

		var parentElement1 = rangeCopy1.parentElement(),
			parentElement2 = rangeCopy2.parentElement();

		rangeObj1.moveToElementText(parentElement1);
		rangeObj1.setEndPoint('EndToEnd', rangeCopy1);
		rangeObj2.moveToElementText(parentElement2);
		rangeObj2.setEndPoint('EndToEnd', rangeCopy2);

		var text1 = rangeObj1.text,
			text2 = rangeObj2.text,
			nodeInfo1 = _findTextNode(parentElement1, text1),
			nodeInfo2 = _findTextNode(parentElement2, text2);

		range.startContainer = nodeInfo1.node;
		range.startOffset = nodeInfo1.offset;
		range.endContainer = nodeInfo2.node;
		range.endOffset = nodeInfo2.offset+1;
	}
	return range;
}

function bxPFParser<?=$arParams["FORM_ID"]?>(e)
{
	if((e.keyCode == 187 || e.keyCode == 50 || e.keyCode == 107 || e.keyCode == 43 || e.keyCode == 61) && (e.shiftKey || e.modifiers > 3))
	{
		bPlus = false;
		setTimeout(function(){
			var r = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.GetSelectionRange();
			
			win = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.pEditorWindow;
			if(win.document.selection) // IE8 and below
			{
				r = BXfixIERangeObject(r, win);
				txt = r.endContainer.nodeValue;
			}
			else
			{
				txt = r.endContainer.textContent;
			}

			if(txt.length > 0 && (txt.slice(r.endOffset-1, r.endOffset) == "@" || txt.slice(r.endOffset-1, r.endOffset) == "+"))
			{
				if(txt.slice(r.endOffset-1, r.endOffset) == "+")
					bPlus = true;
				prevS = txt.slice(r.endOffset-2, r.endOffset-1);
				if(prevS == "+" || prevS == "@" || prevS == "," || (prevS.length == 1 && BX.util.trim(prevS) == 0) || prevS == "" || prevS== "(")
				{
					bMentListen = true;
					if(!BX.SocNetLogDestination.isOpenDialog())
						BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?>);
				}
			}
		}, 10);
	}

	if(bMentListen === true)
	{
		if(e.keyCode == 8) // backspace
		{
			setTimeout(function(){
				r = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.GetSelectionRange();

				win = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.pEditorWindow;
				if(win.document.selection) // IE8 and below
				{
					r = BXfixIERangeObject(r, win);
					if(r.endContainer)
						txt = r.endContainer.nodeValue;
				}
				else
				{
					txt = r.endContainer.textContent;
				}
				if(txt === undefined || txt == null || txt.length == 0 || (txt.lastIndexOf("+", r.endOffset) == -1 && txt.lastIndexOf("@", r.endOffset) == -1))
				{
					bMentListen = false;
					BXfpdStopMent<?=$arParams["FORM_ID"]?>();
				}
			}, 50);
		}
	}
	if(bMentListen === true)
	{
		if(e.keyCode == 27) //ESC
		{
			BXfpdStopMent<?=$arParams["FORM_ID"]?>();
		}
		else if(e.keyCode == 13) // enter
		{
			BX.PreventDefault(e);
			BX.SocNetLogDestination.selectFirstSearchItem(BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?>);
		}
		else
		{
			setTimeout(function(){
				r = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.GetSelectionRange();

				win = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.pEditorWindow;
				if(win.document.selection) // IE8 and below
				{
					r = BXfixIERangeObject(r, win);
					if(r.endContainer)
						txt = r.endContainer.nodeValue;
				}
				else
				{
					txt = r.endContainer.textContent;
				}
				if(txt !== null)
				{
					if(bPlus)
						txtPos = txt.lastIndexOf("+", r.endOffset)+1;
					else
						txtPos = txt.lastIndexOf("@", r.endOffset)+1;
					txt2 = txt.substr(txtPos, (r.endOffset - txtPos));
	/*
	console.log(txt);
	console.log(txt2);
	console.log(txt.length);
	console.log(txtPos);
	console.log(r.endOffset);
	*/

					if(txt2.length == 1 && BX.util.trim(txt2).length == 0)
					{
						BXfpdStopMent<?=$arParams["FORM_ID"]?>();
					}
					else
					{
						BX.SocNetLogDestination.search(txt2, true, BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?>);
						if(BX.util.trim(txt2).length == 0)
						{
							BXfpdStopMent<?=$arParams["FORM_ID"]?>();
							bMentListen = true;
							BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?>); 
						}
					}
				}
			}, 10);
		}
	}
}

function BXfpdSelectCallbackMent<?=$arParams["FORM_ID"]?>(item, type, search)
{ 
	if(type == 'users')
	{
		if(item.entityId > 0)
		{
			pLEditor = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>;
			if(pLEditor)
			{
				if (pLEditor.sEditorMode == 'code' && pLEditor.bBBCode) // BB Codes
				{
					pLEditor.WrapWith("", "", "[USER=" + item.entityId + "]" + item.name + "[/USER]");
				}
				else if(pLEditor.sEditorMode == 'html') // WYSIWYG
				{
		            pLEditor.SetFocus();

					r = pLEditor.GetSelectionRange();

					win = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.pEditorWindow;
					if(win.document.selection) // IE8 and below
					{
						r = BXfixIERangeObject(r, win);
						if(r.endContainer)
							txt = r.endContainer.nodeValue;
					}
					else
					{
						txt = r.endContainer.textContent;
					}


					lastS = r.endOffset+2;
					if(lastS > r.endOffset)
						lastS = r.endOffset;
					if(bPlus)
						txtPos = txt.lastIndexOf("+", lastS);
					else
						txtPos = txt.lastIndexOf("@", lastS);

					txt2 = txt.substr(0, txtPos);
					txtleng = txt2.length;
					
					lastC = txt2.slice(txt2.length-1);
					if(lastC.length == 1 && BX.util.trim(lastC).length == 0)
					{
						txt2 = txt2.substr(0, txt2.length-1);
						txt2 += txt.substr(r.endOffset, txt.length);
						txtPos--;
					}
					else
					{
						if(txtPos > 0)
							txt2 += txt.substr(r.endOffset, txt.length);
					}

					if(win.document.selection) // IE8 and below
					{
						r.endContainer.nodeValue = txt2;
					}
					else
					{
						r.endContainer.textContent = txt2;
					}

					if(!BX.browser.IsIE())
					{
						var selection = pLEditor.GetSelectionRange();

						if(win.document.selection) // IE8 and below
							selection = BXfixIERangeObject(selection, win);

						var rng = pLEditor.pEditorDocument.createRange();
						if(txtPos < 0)
							txtPos = selection.endContainer.length;
//console.log(selection.endContainer);
		                rng.setStart(selection.endContainer, txtPos);
		                rng.setEnd(selection.endContainer, txtPos);
		                pLEditor.SelectRange(rng);
		            }
					adit = '&nbsp;';
					if(txtleng <= 0)
						adit = '';
					
					pLEditor.InsertHTML(adit + '<span id="' + pLEditor.SetBxTag(false, {tag: "bloguser", params: {value : item.entityId}}) + '" style="color: #2067B0; border-bottom: 1px dashed #2067B0;">' + item.name + '</span>&nbsp;');
				}
			}
			BX.SocNetLogDestination.obItemsSelected[BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?>] = {};
			BXfpdStopMent<?=$arParams["FORM_ID"]?>();
		}

	}
}

function BXfpdStopMent<?=$arParams["FORM_ID"]?>()
{
	bMentListen = false;
	clearTimeout(BX.SocNetLogDestination.searchTimeout);
	BX.SocNetLogDestination.closeDialog();
	BX.SocNetLogDestination.closeSearch();
	if(window.oPostFormLHE_<?=$arParams["FORM_ID"]?>)
		window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.SetFocus();
}

function insertBlogImageFile<?=$arParams["FORM_ID"]?>(id)
{
	img = BX.findChild(BX('wd-doc'+id), {'tag': 'img'}, true, false);
	src = img.getAttribute('rel');
	imageId = id+'file';
	pLEditor = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>;
	if (!pLEditor.arBlogImages[imageId])
	{
		pLEditor.arBlogImages[imageId] = {src : src};
	}
	pLEditor.SetFocus();
	InsertBlogImage_LHEPostFormId_<?=$arParams["FORM_ID"]?>(imageId, src);
}

BX.addCustomEvent(BX('<?=$arParams["FORM_ID"]?>'), 'OnFileUploadSuccess', function(result, obj){
	if(result.element_content_type.substr(0,6) == 'image/')
	{
		img = BX.findChild(BX('wd-doc'+result.element_id), {'tag': 'img'}, true, false);
		
		el = BX.findChild(BX('wd-doc'+result.element_id), {'className': 'feed-add-img-wrap'}, true, false);
		BX.bind(el, "click", function(){insertBlogImageFile<?=$arParams["FORM_ID"]?>(result.element_id);});
		el.style.cursor = "pointer";
		el.title = "<?=GetMessage("MPF_IMAGE_TITLE")?>";
		el = BX.findChild(BX('wd-doc'+result.element_id), {'className': 'feed-add-img-title'}, true, false);
		BX.bind(el, "click", function(){insertBlogImageFile<?=$arParams["FORM_ID"]?>(result.element_id);});
		el.style.cursor = "pointer";
		el.title = "<?=GetMessage("MPF_IMAGE_TITLE")?>";
	}
	<?
	if(CModule::IncludeModule("webdav"))
	{
		?>else
		{
			obj.agent.StopUpload(BX('wd-doc'+result.element_id))
		}<?
	}
	?>
});

BX.addCustomEvent(BX('<?=$arParams["FORM_ID"]?>'), 'OnFileUploadRemove', function(result){
	if(BX.findChild(BX('wd-doc'+result), {'tag': 'img'}, true, false))
	{
		pLEditor = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>;
		pLEditor.SaveContent();
		content = pLEditor.GetContent();
		content = content.replace(new RegExp('\\[IMG ID='+result+'file\\]','g'), '');
		pLEditor.SetContent(content);
		pLEditor.SetEditorContent(pLEditor.content);
		pLEditor.SetFocus();
		pLEditor.AutoResize();
	}
});
</script>