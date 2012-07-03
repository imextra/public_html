<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');?>
	<script type="text/javascript">
	BX.message({
		BLOG_TAG_ADD : "<?=GetMessage("BLOG_ADD")?>"
	});
	var postFormId = '<?=$arParams["FORM_ID"]?>';
	</script>

	<form action="<?=$arParams["FORM_ACTION_URL"]?>" id="<?=$arParams["FORM_ID"]?>" name="<?=$arParams["FORM_ID"]?>" method="POST" enctype="multipart/form-data"<?if(strlen($arParams["FORM_TARGET"]) > 0) echo " target=\"".$arParams["FORM_TARGET"]."\""?>>
	<?
	if(!empty($arParams["HIDDENS"]))
	{
		foreach($arParams["HIDDENS"] as $val)
		{
			?><input type="hidden" name="<?=$val["NAME"]?>" id="<?=$val["ID"]?>" value="<?=$val["VALUE"]?>" /><?
		}
	}
	?>
	<?=bitrix_sessid_post();?>
	<?=$arParams["ADITIONAL_BEFORE"]?>
	<?
	if(!empty($arParams["TITLE"]))
	{
		?>
		<div class="feed-add-post-title" id="blog-title">
			<input id="POST_TITLE" name="<?=$arParams["TITLE"]["NAME"]?>" class="feed-add-post-inp" type="text" value="<?=(strlen($arParams["TITLE"]["VALUE"]) > 0 ? htmlspecialcharsBack($arParams["TITLE"]["VALUE"]) : $arParams["TITLE"]["VALUE_DEFAULT"])?>" onblur="if (this.value=='') {this.value='<?=$arParams["TITLE"]["VALUE_DEFAULT"]?>'; BX.removeClass(this, 'feed-add-post-inp-active');}" onclick="if (this.value=='<?=$arParams["TITLE"]["VALUE_DEFAULT"]?>') {this.value=''; BX.addClass(this,'feed-add-post-inp-active')}" />
			<div class="feed-add-close-icon" onclick="showPanel_<?=$arParams["FORM_ID"]?>('title', false)"></div>
		</div>
		<?
	}
	?>
	<div id="blog-post-autosave-hidden" style="display:none;"></div>

	<div class="feed-add-post-form feed-add-post-edit-form">
		<div class="feed-add-post-text">
			<div class="feed-add-close-icon" onclick="showPanel_<?=$arParams["FORM_ID"]?>('html', false)" id="bx-panel-close"></div>
			<?include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/lhe.php");?>
			<div style="width:0; height:0; overflow:hidden;"><input type="text" tabindex="3" onFocus="window.oPostFormLHE_<?=$arParams["FORM_ID"]?>.SetFocus()" name="hidden_focus"></div>
		</div>
		<div class="feed-add-post-form-but-wrap" id="blog-buttons-bottom">
			<?if(!empty($arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"]))
			{
				foreach($arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"] as $val)
				{
					switch($val)
					{
						case "File":
							?><span class="feed-add-post-form-but feed-add-file" id="bx-b-file" title="<?=GetMessage("MPF_FILE_TITLE")?>"></span><?
							break;
						case "CreateLink":
							?><span class="feed-add-post-form-but feed-add-link" id="bx-b-link"></span><?
							break;
						case "BlogImage":
							?><span class="feed-add-post-form-but feed-add-img" id="bx-b-img"></span><?
							break;
						case "BlogInputVideo":
							?><span class="feed-add-post-form-but feed-add-video" id="bx-b-video"></span><?
							break;
						case "BlogUser":
							?><span class="feed-add-post-form-but feed-add-mention" id="bx-b-mention" title="<?=GetMessage("MPF_MENTION_TITLE")?>"></span><?
							break;
						case "BlogTag":
							?><span class="feed-add-post-form-but feed-add-tag" id="bx-b-tag"></span><?
							break;
						case "BlogTagInput":
							?><span class="feed-add-post-form-but feed-add-tag" id="bx-b-tag-input" title="<?=GetMessage("MPF_TAG_TITLE")?>"></span><?
							break;
					}
				}
				
				?><?
			}  
			if($arParams["TEXT"]["HTML"]["CAN_HIDE"] == "Y" || $arParams["TITLE"]["CAN_HIDE"] == "Y")
			{
				?>
				<div class="feed-add-post-form-but-more" onclick="BX.PopupMenu.show('menu-more<?=$arParams["FORM_ID"]?>', this, [
						<?if($arParams["TITLE"]["CAN_HIDE"] == "Y")
						{
							?>{ text : '<?=GetMessage("BLOG_P_TITLE")?>', onclick : function() {showPanel_<?=$arParams["FORM_ID"]?>('title'); this.popupWindow.close();}, className: 'blog-post-popup-menu', id: 'bx-title'},<?
						}
						if($arParams["TEXT"]["HTML"]["CAN_HIDE"] == "Y")
						{
							?>{ text : '<?=GetMessage("BLOG_P_HTML")?>', onclick : function() {showPanel_<?=$arParams["FORM_ID"]?>('html'); this.popupWindow.close();}, className: 'blog-post-popup-menu', id: 'bx-html'}<?
						}
						?>
						],
					{
						offsetLeft: 12,
						offsetTop: 3,
						lightShadow: false,
						angle: top
					});"><?=GetMessage("BLOG_P_MORE")?><div class="feed-add-post-form-but-arrow"></div>
				</div>
				<?
			}?>
		</div>
	</div>
	<?
	if(!empty($arParams["DESTINATION"]) && $arParams["DESTINATION"]["SHOW"] != "N")
	{
		?>
		<div class="feed-add-post-destination-block">
			<div class="feed-add-post-destination-title"><?=GetMessage("BLOG_P_DESTINATION")?></div>
			<div class="feed-add-post-destination-wrap" id="feed-add-post-destination-container">
				<span id="feed-add-post-destination-item"></span>
				<span class="feed-add-destination-input-box" id="feed-add-post-destination-input-box"><input type="text" value="" class="feed-add-destination-inp" id="feed-add-post-destination-input"></span>
				<a href="#" class="feed-add-destination-link" id="bx-destination-tag"></a>

				<script type="text/javascript">
					BX.message({'BX_FPD_LINK_1':'<?=GetMessage("BLOG_P_DESTINATION_1")?>','BX_FPD_LINK_2':'<?=GetMessage("BLOG_P_DESTINATION_2")?>'});
					BXSocNetLogDestinationFormName = '<?=randString(6)?>';
					BXSocNetLogDestinationDisableBackspace = null;
					BX.SocNetLogDestination.init({
						'name' : BXSocNetLogDestinationFormName,
						'searchInput' : BX('feed-add-post-destination-input'),
						'extranetUser' :  <?=($arParams["DESTINATION"]["VALUE"]["EXTRANET_USER"] == 'Y'? 'true': 'false')?>,
						'bindMainPopup' : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
						'bindSearchPopup' : { 'node' : BX('feed-add-post-destination-container'), 'offsetTop' : '5px', 'offsetLeft': '15px'},
						'callback' : {
							'select' : BXfpdSelectCallback,
							'unSelect' : BXfpdUnSelectCallback,
							'openDialog' : BXfpdOpenDialogCallback,
							'closeDialog' : BXfpdCloseDialogCallback,	
							'openSearch' : BXfpdOpenDialogCallback,	
							'closeSearch' : BXfpdCloseSearchCallback
						},
						'items' : {
							'users' : <?=(empty($arParams["DESTINATION"]["VALUE"]['USERS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['USERS']))?>,
							'groups' : <?=($arParams["DESTINATION"]["VALUE"]["EXTRANET_USER"] == 'Y'? '{}': "{'UA' : {'id':'UA','name': '".(!empty($arParams["DESTINATION"]["VALUE"]['DEPARTMENT']) ? GetMessage("BLOG_P_DESTINATION_3"): GetMessage("BLOG_P_DESTINATION_4"))."'}}")?>,
							'sonetgroups' : <?=(empty($arParams["DESTINATION"]["VALUE"]['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['SONETGROUPS']))?>,
							'department' : <?=(empty($arParams["DESTINATION"]["VALUE"]['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['DEPARTMENT']))?>,
							'departmentRelation' : <?=(empty($arParams["DESTINATION"]["VALUE"]['DEPARTMENT_RELATION'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['DEPARTMENT_RELATION']))?>
						},
						'itemsLast' : {
							'users' : <?=(empty($arParams["DESTINATION"]["VALUE"]['LAST']['USERS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['LAST']['USERS']))?>,
							'sonetgroups' : <?=(empty($arParams["DESTINATION"]["VALUE"]['LAST']['SONETGROUPS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['LAST']['SONETGROUPS']))?>,
							'department' : <?=(empty($arParams["DESTINATION"]["VALUE"]['LAST']['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['LAST']['DEPARTMENT']))?>,
							'groups' : <?=($arParams["DESTINATION"]["VALUE"]["EXTRANET_USER"] == 'Y'? '{}': "{'UA':true}")?>
						},
						'itemsSelected' : <?=(empty($arParams["DESTINATION"]["VALUE"]['SELECTED'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['SELECTED']))?>
					});
					BX.bind(BX('feed-add-post-destination-input'), 'keyup', BXfpdSearch);
					BX.bind(BX('feed-add-post-destination-input'), 'keydown', BXfpdSearchBefore);
					BX.bind(BX('bx-destination-tag'), 'click', function(e){ BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName); BX.PreventDefault(e) });
					BX.bind(BX('feed-add-post-destination-container'), 'click', function(e){ BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormName); BX.PreventDefault(e) });
				</script>
			</div>
		</div>
		<?
	}
	
	if(!empty($arParams["TAGS"]))
	{
		?>
		<div class="feed-add-post-tags-block">
			<div class="feed-add-post-tags-title"><?=GetMessage("BLOG_BLOG_BLOG_CATEGORY")?></div>
			<div class="feed-add-post-tags-wrap" id="blog-tags-container">
				<?
				if(!empty($arParams["TAGS"]["VALUE"]))
				{
					foreach($arParams["TAGS"]["VALUE"] as $val)
					{
						$val = trim($val);
						if(strlen($val) > 0)
						{
							?><span class="feed-add-post-tags"><?=htmlspecialchars($val)?><span class="feed-add-post-del-but" onclick="deleteTag('<?=CUtil::JSEscape($val)?>', this.parentNode)"></span></span><?
						}
					}
				}?>
				<span class="feed-add-post-tags-add" id="bx-blog-tag"><?=GetMessage("BLOG_ADD")?></span>
				<input type="hidden" name="<?=$arParams["TAGS"]["NAME"]?>" id="tags-hidden" value="<?=implode(",", $arParams["TAGS"]["VALUE"])?>,">
			</div>
		</div>
		<div id="blog-tags-input" style="display:none;">
			<?if($arParams["TAGS"]["USE_SEARCH"] == "Y" && IsModuleInstalled("search"))
			{
				$arSParams = Array(
					"NAME"	=>	"TAG_INPUT",
					"VALUE"	=>	"",
					"arrFILTER"	=>	$arParams["TAGS"]["FILTER"],
					"PAGE_ELEMENTS"	=>	"10",
					"SORT_BY_CNT"	=>	"Y",
					"TEXT" => 'size="30" tabindex="4"',
					"ID" => "TAGS"
					);
				$APPLICATION->IncludeComponent("bitrix:search.tags.input", ".default", $arSParams);
			}
			else
			{
				?><input type="text" tabindex="4" name="TAG_INPUT" size="30" value=""><?
			}?>
		</div>
		<?
	}
	if($arParams["USER_FIELDS"]["SHOW"] == "Y")
	{
		$eventHandlerID = false;
		$eventHandlerID = AddEventHandler('main', 'system.field.edit.file', array('CBlogTools', 'blogUFfileEdit'));

		foreach($arParams["USER_FIELDS"]["VALUE"] as $FIELD_NAME => $arPostField)
		{
			$APPLICATION->IncludeComponent(
					"bitrix:system.field.edit",
					$arPostField["USER_TYPE"]["USER_TYPE_ID"],
					array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));
		}

		if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
			RemoveEventHandler('main', 'system.field.edit.file', $eventHandlerID);
	}
	
	if(in_array("BlogImage", $arParams["TEXT"]["HTML"]["BUTTONS"]) && $arParams["IMAGES"]["SHOW"] != "N")
	{
		?>
		<div class="feed-add-post-files-block">
			<div class="feed-add-post-files-title feed-add-post-p"><?=GetMessage("BLOG_P_PHOTO")?></div>
			<div class="feed-add-post-files-list-wrap">
				<div class="feed-add-photo-block-wrap" id="blog-post-image">
					<?
					if (!empty($arParams["IMAGES"]))
					{
						foreach($arParams["IMAGES"]["VALUE"] as $aImg)
						{
							?><span class="feed-add-photo-block">
									<span class="feed-add-img-wrap"><?=$aImg["fileShow"]?></span>
									<span class="feed-add-img-title"><?=$aImg["fileName"]?></span>
									<span class="feed-add-post-del-but" onclick="DeleteImage('<?=$aImg["ID"]?>', this)"></span>
								</span><?
						}
					}
					?>
				</div>
			</div>
		</div>
		<?
	}
	echo $arParams["ADITIONAL_AFTER"];
	foreach($arParams["BUTTONS"] as $val)
	{
		if($val["CLEAR_CANCEL"] == "Y")
		{
			?><a class="feed-cancel-com" href="javascript:void(0)" id="blog-submit-button-<?=$val["NAME"]?>"><?=$val["TEXT"]?></a><?
		}
		else
		{
			?><a class="feed-add-button<?=" ".$val["ADIT_STYLES"]?>" href="javascript:void(0)" id="blog-submit-button-<?=$val["NAME"]?>" onmousedown="BX.addClass(this, 'feed-add-button-press')" onmouseup="BX.removeClass(this,'feed-add-button-press')"><span class="feed-add-button-left"></span><span class="feed-add-button-text"><?=$val["TEXT"]?></span><span class="feed-add-button-right"></span></a><?
		}
	}
	?>
	</form>
	<script>
	<?
	if(in_array("BlogUser", $arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"]))
	{
		?>
		BX.message({'BX_FPD_LINK_1':'<?=GetMessage("BLOG_P_DESTINATION_1")?>','BX_FPD_LINK_2':'<?=GetMessage("BLOG_P_DESTINATION_2")?>'});
		BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?> = '<?=randString(6)?>';
		BXSocNetLogDestinationDisableBackspace = null;
		var bxBMent = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'bx-b-mention'}}, true, false);
		BX.SocNetLogDestination.init({
			'name' : BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?>,
			'searchInput' : bxBMent,
			'extranetUser' :  <?=($arParams["DESTINATION"]["VALUE"]["EXTRANET_USER"] == 'Y'? 'true': 'false')?>,
			'bindMainPopup' : { 'node' : bxBMent, 'offsetTop' : '1px', 'offsetLeft': '12px'},
			'bindSearchPopup' : { 'node' : bxBMent, 'offsetTop' : '1px', 'offsetLeft': '12px'},
			'callback' : {
				'select' : BXfpdSelectCallbackMent<?=$arParams["FORM_ID"]?>/*,
				'closeDialog' : BXfpdCloseDialogCallbackMent<?=$arParams["FORM_ID"]?>,	
				'closeSearch' : BXfpdCloseDialogCallbackMent<?=$arParams["FORM_ID"]?>*/
			},
			'items' : {
				'users' : <?=(empty($arParams["DESTINATION"]["VALUE"]['USERS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['USERS']))?>,
				'groups' : {},
				'sonetgroups' : {},
				'department' : <?=(empty($arParams["DESTINATION"]["VALUE"]['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['DEPARTMENT']))?>,
				'departmentRelation' : <?=(empty($arParams["DESTINATION"]["VALUE"]['DEPARTMENT_RELATION'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['DEPARTMENT_RELATION']))?>

			},
			'itemsLast' : {
				'users' : <?=(empty($arParams["DESTINATION"]["VALUE"]['LAST']['USERS'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['LAST']['USERS']))?>,
				'sonetgroups' : {},
				'department' : {},
				'groups' : {}
			},
			'itemsSelected' : <?=(empty($arParams["DESTINATION"]["VALUE"]['SELECTED'])? '{}': CUtil::PhpToJSObject($arParams["DESTINATION"]["VALUE"]['SELECTED']))?>,
			'departmentSelectDisable' : true,
			'obWindowClass' : 'bx-lm-mention',
			'obWindowCloseIcon' : false
		});
		<?
	}?>

	if(window.BX)
	{
		BX.ready(function() {	
			<?
			if(empty($arParams["IMAGES"]["VALUE"]) && $arParams["IMAGES"]["SHOW"] != "N")
			{
				?>
				BX.hide(BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'blog-post-image'}}, true, false).parentNode.parentNode);
				<?
			}
			if(!empty($arParams["TITLE"]))
			{
				if(strlen($arParams["TITLE"]["VALUE"]) > 0 || $arParams["TITLE"]["SHOW_DEFAULT"] == "Y")
				{
					?>showPanel_<?=$arParams["FORM_ID"]?>('title', true);<?
					if(strlen($arParams["TITLE"]["VALUE"]) > 0)
					{
						?>BX.addClass(BX('POST_TITLE'),'feed-add-post-inp-active');<?
					}
				}
				else
				{
					?>showPanel_<?=$arParams["FORM_ID"]?>('title', false);<?
				}
			}			
			if(!empty($arParams["TAGS"]))
			{
				?>
				BX.bind(BX("bx-blog-tag"), "click", function(e) {
							if(!e) e = window.event;
							popupTag.setAngle({position:'top'});
							popupTag.show();
							BX(BX.findChild(BX('blog-tags-input'), {'tag': 'input' })).focus();
							BX.PreventDefault(e);
						});
				BX.bind(BX("bx-b-tag-input"), "click", function(e) {
							if(!e) e = window.event;
							
							var el = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'className': /feed-add-post-tags-block/ }, true, false);
							BX.show(el);

							popupTag.setAngle({position:'top'});
							popupTag.show();
							BX(BX.findChild(BX('blog-tags-input'), {'tag': 'input' })).focus();
							BX.PreventDefault(e);
						});
				BX.bind(BX("bx-blog-tag-button"), "click", function(e) {
							if(!e) e = window.event;
							popupTag.setAngle({position:'top'});
							popupTag.show();
							BX(BX.findChild(BX('blog-tags-input'), {'tag': 'input' })).focus();
							BX.PreventDefault(e);
						});

				var popupBindElement = BX('bx-blog-tag');
				popupTag = new BX.PopupWindow('bx-blog-tag-popup', popupBindElement, {
					lightShadow : false,
					offsetTop: 8,
					offsetLeft: 10,
					autoHide: true,
					closeByEsc: true,
					zIndex: -910,
					bindOptions: {position: "bottom"},
					buttons: [
								new BX.PopupWindowButton({
									text : BX.message('BLOG_TAG_ADD'),
									events : {
										click : function() {
												addTag();
										}
									}
								})
							]

				});

				popupTag.setContent(BX('blog-tags-input'));
				tagInput = BX.findChild(BX('blog-tags-input'), {'tag': 'input' });
				BX.bind(tagInput, "keydown", BX.proxy(__onKeyTags, this ));
				BX.bind(tagInput, "keyup", BX.proxy(__onKeyTags, this ));
				<?
				if(!empty($arParams["TAGS"]["VALUE"]) && !empty($arParams["TAGS"]["VALUE"][0]))
				{
					?>
					var el = BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'className': /feed-add-post-tags-block/ }, true, false);
					BX.show(el);<?
				}
			}

			if(in_array("BlogUser", $arParams["TEXT"]["HTML"]["BUTTONS_BOTTOM"]))
			{
				?>
				BX.addCustomEvent(BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'bx-b-mention'}}, true, false), 'mentionClick', function(e){setTimeout(function(){
						if(!BX.SocNetLogDestination.isOpenDialog())
							BX.SocNetLogDestination.openDialog(BXSocNetLogDestinationFormNameMent<?=$arParams["FORM_ID"]?>); 
						pLEditor = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>;
						pLEditor.SetFocus();}, 100);
				});
				//mousedown for IE, that lost focus on button click
				BX.bind(BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'bx-b-mention'}}, true, false), "mousedown", function(e) {
							if(!bMentListen)
							{
								pLEditor = window.oPostFormLHE_<?=$arParams["FORM_ID"]?>;
								if(pLEditor.sEditorMode == 'html') // WYSIWYG
								{
									pLEditor.InsertHTML('@');
									bMentListen = true;
								}
							}
							
							BX.onCustomEvent(BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {'attr': {id: 'bx-b-mention'}}, true, false), 'mentionClick');
						});
				<?
			}
			
			foreach($arParams["BUTTONS"] as $val)
			{
				if(strlen($val["CLICK"]) > 0)
				{
					?>BX.bind(BX("blog-submit-button-<?=$val["NAME"]?>"), "click", function(){<?=$val["CLICK"]?>});<?
				}
				else
				{
					?>BX.bind(BX("blog-submit-button-<?=$val["NAME"]?>"), "click", function(){BX.submit(BX('<?=$arParams["FORM_ID"]?>'), '<?=$val["NAME"]?>')});<?
				}
			}
			?>
			BX.bind(BX.findChild(BX('<?=$arParams["FORM_ID"]?>'), {attr: {id : 'bx-b-file'}}, true, false), "click", showFile_<?=$arParams["FORM_ID"]?>);
		});
	}
	</script>
