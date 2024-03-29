<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

//color schemes
$arThemes = CGridOptions::GetThemes($this->GetFolder());
?>

<div class="bx-interface-form">

<script type="text/javascript">
var bxForm_<?=$arParams["FORM_ID"]?> = null;
</script>

<?if($arParams["SHOW_FORM_TAG"]):?>
<form name="form_<?=$arParams["FORM_ID"]?>" id="form_<?=$arParams["FORM_ID"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">

<?=bitrix_sessid_post();?>
<input type="hidden" id="<?=$arParams["FORM_ID"]?>_active_tab" name="<?=$arParams["FORM_ID"]?>_active_tab" value="<?=htmlspecialchars($arResult["SELECTED_TAB"])?>">
<?endif?>
			<table cellspacing="0" class="bx-edit-tabs" width="100%">
				<tr>
					<td class="bx-tab-indent"><div class="empty"></div></td>
<?
$nTabs = count($arResult["TABS"]);
foreach($arResult["TABS"] as $tab):
	$bSelected = ($tab["id"] == $arResult["SELECTED_TAB"]);
?>
					<td title="<?=htmlspecialchars($tab["title"])?>" id="tab_cont_<?=$tab["id"]?>" class="bx-tab-container<?=($bSelected? "-selected":"")?>" onclick="bxForm_<?=$arParams["FORM_ID"]?>.SelectTab('<?=$tab["id"]?>');" onmouseover="if(bxForm_<?=$arParams["FORM_ID"]?>){bxForm_<?=$arParams["FORM_ID"]?>.HoverTab('<?=$tab["id"]?>', true);}" onmouseout="if(bxForm_<?=$arParams["FORM_ID"]?>){bxForm_<?=$arParams["FORM_ID"]?>.HoverTab('<?=$tab["id"]?>', false);}">
						<table cellspacing="0">
							<tr>
								<td class="bx-tab-left<?=($bSelected? "-selected":"")?>" id="tab_left_<?=$tab["id"]?>"><div class="empty"></div></td>
								<td class="bx-tab<?=($bSelected? "-selected":"")?>" id="tab_<?=$tab["id"]?>"><?=htmlspecialchars($tab["name"])?></td>
								<td class="bx-tab-right<?=($bSelected? "-selected":"")?>" id="tab_right_<?=$tab["id"]?>"><div class="empty"></div></td>
							</tr>
						</table>
					</td>
<?
endforeach;
?>
					<td width="100%"<?if($USER->IsAuthorized() && $arParams["SHOW_SETTINGS"] == true):?> ondblclick="bxForm_<?=$arParams["FORM_ID"]?>.ShowSettings()"<?endif?> style="white-space:nowrap; text-align:right">
<?
if(count($arResult["TABS"]) > 1 && $arParams["CAN_EXPAND_TABS"] == true):
?>
<a href="javascript:void(0)" onclick="bxForm_<?=$arParams["FORM_ID"]?>.ToggleTabs();" title="<?echo GetMessage("interface_form_show_all")?>" id="bxForm_<?=$arParams["FORM_ID"]?>_expand_link" class="bx-context-button bx-down"><span></span></a>
<?endif?>
<a href="javascript:void(0)" onclick="bxForm_<?=$arParams["FORM_ID"]?>.menu.ShowMenu(this, bxForm_<?=$arParams["FORM_ID"]?>.settingsMenu);" title="<?echo GetMessage("interface_form_settings")?>" class="bx-context-button bx-form-menu"><span></span></a>
					</td>
				</tr>
			</table>
			<table cellspacing="0" class="bx-edit-tab">
				<tr>
					<td>
<?
$bWasRequired = false;
foreach($arResult["TABS"] as $tab):
?>
<div id="inner_tab_<?=$tab["id"]?>" class="bx-edit-tab-inner"<?if($tab["id"] <> $arResult["SELECTED_TAB"]) echo ' style="display:none;"'?>>
<div style="height: 100%;">
<?if($tab["title"] <> ''):?>
	<div class="bx-edit-tab-title">
	<table cellpadding="0" cellspacing="0" border="0" class="bx-edit-tab-title">
		<tr>
	<?
		if($tab["icon"] <> ""):
	?>
			<td class="bx-icon"><div class="<?=htmlspecialchars($tab["icon"])?>"></div></td>
	<?
		endif
	?>
			<td class="bx-form-title"><?=htmlspecialchars($tab["title"])?></td>
		</tr>
	</table>
	</div>
<?endif;?>

<div class="bx-edit-table">
<table cellpadding="0" cellspacing="0" border="0" class="bx-edit-table <?=(isset($tab["class"]) ? $tab['class'] : '')?>" id="<?=$tab["id"]?>_edit_table">
<?
$i = 0;
$cnt = count($tab["fields"]);
$prevType = '';
foreach($tab["fields"] as $field):
	$i++;
	if(!is_array($field))
		continue;

	$className = '';
	if($i == 1)
		$className .= ' bx-top';
	if($i == $cnt)
		$className .= ' bx-bottom';
	if($prevType == 'section')
		$className .= ' bx-after-heading';
?>
	<tr<?if($className <> ''):?> class="<?=$className?>"<?endif?>>
<?
if($field["type"] == 'section'):
?>
		<td colspan="2" class="bx-heading"><?=htmlspecialchars($field["name"])?></td>
<?
else:
	$val = (isset($field["value"])? $field["value"] : $arParams["~DATA"][$field["id"]]);

	//default attributes
	if(!is_array($field["params"]))
		$field["params"] = array();
	if($field["type"] == '' || $field["type"] == 'text')
	{
		if($field["params"]["size"] == '')
			$field["params"]["size"] = "30";
	}
	elseif($field["type"] == 'textarea')
	{
		if($field["params"]["cols"] == '')
			$field["params"]["cols"] = "40";
		if($field["params"]["rows"] == '')
			$field["params"]["rows"] = "3";
	}
	elseif($field["type"] == 'date')
	{
		if($field["params"]["size"] == '')
			$field["params"]["size"] = "10";
	}
	
	$params = '';
	if(is_array($field["params"]) && $field["type"] <> 'file')
	{
		foreach($field["params"] as $p=>$v)
			$params .= ' '.$p.'="'.$v.'"';
	}

	if($field["colspan"] <> true):
		if($field["required"])
			$bWasRequired = true;
?>
		<td class="bx-field-name<?if($field["type"] <> 'label') echo' bx-padding'?>"<?if($field["title"] <> '') echo ' title="'.htmlspecialcharsEx($field["title"]).'"'?>><?=($field["required"]? '<span class="required">*</span>':'')?><?=htmlspecialcharsEx($field["name"])?>:</td>
<?
	endif
?>
		<td class="bx-field-value"<?=($field["colspan"]? ' colspan="2"':'')?>>
<?
	switch($field["type"]):
		case 'label':
		case 'custom':
			echo $val;
			break;
		case 'checkbox':
?>
<input type="hidden" name="<?=$field["id"]?>" value="N">
<input type="checkbox" name="<?=$field["id"]?>" value="Y"<?=($val == "Y"? ' checked':'')?><?=$params?>>
<?
			break;
		case 'textarea':
?>
<textarea name="<?=$field["id"]?>"<?=$params?>><?=$val?></textarea>
<?
			break;
		case 'list':
?>
<select name="<?=$field["id"]?>"<?=$params?>>
<?
			if(is_array($field["items"])):
				if(!is_array($val))
					$val = array($val);
				foreach($field["items"] as $k=>$v):
?>
	<option value="<?=htmlspecialchars($k)?>"<?=(in_array($k, $val)? ' selected':'')?>><?=htmlspecialchars($v)?></option>
<?
				endforeach;
?>
</select>
<?
			endif;
			break;
		case 'file':
			$arDefParams = array("iMaxW"=>150, "iMaxH"=>150, "sParams"=>"border=0", "strImageUrl"=>"", "bPopup"=>true, "sPopupTitle"=>false, "size"=>20);
			foreach($arDefParams as $k=>$v)
				if(!array_key_exists($k, $field["params"]))
					$field["params"][$k] = $v;
	
			echo CFile::InputFile($field["id"], $field["params"]["size"], $val);
			if($val <> '')
				echo '<br>'.CFile::ShowImage($val, $field["params"]["iMaxW"], $field["params"]["iMaxH"], $field["params"]["sParams"], $field["params"]["strImageUrl"], $field["params"]["bPopup"], $field["params"]["sPopupTitle"]);

			break;
		case 'date':
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.calendar",
	"",
	array(
		"SHOW_INPUT"=>"Y",
		"INPUT_NAME"=>$field["id"],
		"INPUT_VALUE"=>$val,
		"INPUT_ADDITIONAL_ATTR"=>$params,
	),
	$component,
	array("HIDE_ICONS"=>true)
);?>
<?
			break;
		default:
?>
<input type="text" name="<?=$field["id"]?>" value="<?=$val?>"<?=$params?>>
<?
			break;
	endswitch;
?>
		</td>
<?endif?>
	</tr>
<?
	$prevType = $field["type"];
endforeach;
?>
</table>
</div>
</div>
</div>
<?
endforeach;
?>
					</td>
				</tr>
			</table>
<?
if(isset($arParams["BUTTONS"])):
?>
			<div class="bx-buttons">
<?if($arParams["~BUTTONS"]["standard_buttons"] !== false):?>
	<?if($arParams["BUTTONS"]["back_url"] <> ''):?>
	<input type="submit" name="save" value="<?echo GetMessage("interface_form_save")?>" title="<?echo GetMessage("interface_form_save_title")?>" />
	<?endif?>
	<input type="submit" name="apply" value="<?echo GetMessage("interface_form_apply")?>" title="<?echo GetMessage("interface_form_apply_title")?>" />
	<?if($arParams["BUTTONS"]["back_url"] <> ''):?>
	<input type="button" value="<?echo GetMessage("interface_form_cancel")?>" name="cancel" onclick="window.location='<?=htmlspecialchars(CUtil::addslashes($arParams["~BUTTONS"]["back_url"]))?>'" title="<?echo GetMessage("interface_form_cancel_title")?>" />
	<?endif?>
<?endif?>
<?=$arParams["~BUTTONS"]["custom_html"]?>
			</div>
<?endif?>
<?if($arParams["SHOW_FORM_TAG"]):?>
</form>
<?endif?>

<?if($GLOBALS['USER']->IsAuthorized() && $arParams["SHOW_SETTINGS"] == true):?>
<div style="display:none">

<div id="form_settings_<?=$arParams["FORM_ID"]?>">
<table width="100%">
	<tr class="section">
		<td colspan="2"><?echo GetMessage("interface_form_tabs")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table>
				<tr>
					<td style="background-image:none" nowrap>
						<select style="min-width:150px;" name="tabs" size="10" ondblclick="this.form.tab_edit_btn.onclick()" onchange="bxForm_<?=$arParams["FORM_ID"]?>.OnSettingsChangeTab()">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="tab_up_btn" value="<?echo GetMessage("intarface_form_up")?>" title="<?echo GetMessage("intarface_form_up_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabMoveUp()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_down_btn" value="<?echo GetMessage("intarface_form_up_down")?>" title="<?echo GetMessage("intarface_form_down_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabMoveDown()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_add_btn" value="<?echo GetMessage("intarface_form_add")?>" title="<?echo GetMessage("intarface_form_add_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabAdd()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_edit_btn" value="<?echo GetMessage("intarface_form_edit")?>" title="<?echo GetMessage("intarface_form_edit_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabEdit()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_del_btn" value="<?echo GetMessage("intarface_form_del")?>" title="<?echo GetMessage("intarface_form_del_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabDelete()"></div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="section">
		<td colspan="2"><?echo GetMessage("intarface_form_fields")?></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
			<table>
				<tr>
					<td style="background-image:none" nowrap>
						<div style="margin-bottom:5px"><?echo GetMessage("intarface_form_fields_available")?></div>
						<select style="min-width:150px;" name="all_fields" multiple size="12" ondblclick="this.form.add_btn.onclick()" onchange="bxForm_<?=$arParams["FORM_ID"]?>.ProcessButtons()">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="add_btn" value="&gt;" title="<?echo GetMessage("intarface_form_add_field")?>" style="width:30px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsAdd()"></div>
						<div style="margin-bottom:5px"><input type="button" name="del_btn" value="&lt;" title="<?echo GetMessage("intarface_form_del_field")?>" style="width:30px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsDelete()"></div>
					</td>
					<td style="background-image:none" nowrap>
						<div style="margin-bottom:5px"><?echo GetMessage("intarface_form_fields_on_tab")?></div>
						<select style="min-width:150px;" name="fields" multiple size="12" ondblclick="this.form.del_btn.onclick()" onchange="bxForm_<?=$arParams["FORM_ID"]?>.ProcessButtons()">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="up_btn" value="<?echo GetMessage("intarface_form_up")?>" title="<?echo GetMessage("intarface_form_up_title")?>" style="width:80px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsMoveUp()"></div>
						<div style="margin-bottom:5px"><input type="button" name="down_btn" value="<?echo GetMessage("intarface_form_up_down")?>" title="<?echo GetMessage("intarface_form_down_title")?>" style="width:80px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsMoveDown()"></div>
						<div style="margin-bottom:5px"><input type="button" name="field_add_btn" value="<?echo GetMessage("intarface_form_add")?>" title="<?echo GetMessage("intarface_form_add_sect")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldAdd()"></div>
						<div style="margin-bottom:5px"><input type="button" name="field_edit_btn" value="<?echo GetMessage("intarface_form_edit")?>" title="<?echo GetMessage("intarface_form_edit_field")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldEdit()"></div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</div>

</div>
<?
endif //$GLOBALS['USER']->IsAuthorized()
?>

<?
$variables = array(
	"mess"=>array(
		"collapseTabs"=>GetMessage("interface_form_close_all"),
		"expandTabs"=>GetMessage("interface_form_show_all"),
		"settingsTitle"=>GetMessage("intarface_form_settings"),
		"settingsSave"=>GetMessage("interface_form_save"),
		"tabSettingsTitle"=>GetMessage("intarface_form_tab"),
		"tabSettingsSave"=>"OK",
		"tabSettingsName"=>GetMessage("intarface_form_tab_name"),
		"tabSettingsCaption"=>GetMessage("intarface_form_tab_title"),
		"fieldSettingsTitle"=>GetMessage("intarface_form_field"),
		"fieldSettingsName"=>GetMessage("intarface_form_field_name"),
		"sectSettingsTitle"=>GetMessage("intarface_form_sect"),
		"sectSettingsName"=>GetMessage("intarface_form_sect_name"),
	),
	"ajax"=>array(
		"AJAX_ID"=>$arParams["AJAX_ID"],
		"AJAX_OPTION_SHADOW"=>($arParams["AJAX_OPTION_SHADOW"] == "Y"),
	),
	"settingWndSize"=>CUtil::GetPopupSize("InterfaceFormSettingWnd"),
	"tabSettingWndSize"=>CUtil::GetPopupSize("InterfaceFormTabSettingWnd", array('width'=>400, 'height'=>200)),
	"fieldSettingWndSize"=>CUtil::GetPopupSize("InterfaceFormFieldSettingWnd", array('width'=>400, 'height'=>150)),
	"component_path"=>$component->GetRelativePath(),
	"template_path"=>$this->GetFolder(),
	"sessid"=>bitrix_sessid(),
	"current_url"=>$APPLICATION->GetCurPageParam("", array("bxajaxid", "AJAX_CALL")),
	"GRID_ID"=>$arParams["THEME_GRID_ID"],
);
?>
<script type="text/javascript">
var formSettingsDialog<?=$arParams["FORM_ID"]?>;

bxForm_<?=$arParams["FORM_ID"]?> = new BxInterfaceForm('<?=$arParams["FORM_ID"]?>', <?=CUtil::PhpToJsObject(array_keys($arResult["TABS"]))?>);
bxForm_<?=$arParams["FORM_ID"]?>.vars = <?=CUtil::PhpToJsObject($variables)?>;
<?if($arParams["SHOW_SETTINGS"] == true):?>
bxForm_<?=$arParams["FORM_ID"]?>.oTabsMeta = <?=CUtil::PhpToJsObject($arResult["TABS_META"])?>;
bxForm_<?=$arParams["FORM_ID"]?>.oFields = <?=CUtil::PhpToJsObject($arResult["AVAILABLE_FIELDS"])?>;
<?endif?>
bxForm_<?=$arParams["FORM_ID"]?>.settingsMenu = [
<?if($arParams["SHOW_SETTINGS"] == true):?>
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("intarface_form_mnu_settings"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("intarface_form_mnu_settings_title"))?>', 'ONCLICK': 'bxForm_<?=$arParams["FORM_ID"]?>.ShowSettings()', 'DEFAULT':true, 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'form-settings'},
<?if(!empty($arResult["OPTIONS"]["tabs"])):?>
<?if($arResult["OPTIONS"]["settings_disabled"] == "Y"):?>
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("intarface_form_mnu_on"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("intarface_form_mnu_on_title"))?>', 'ONCLICK': 'bxForm_<?=$arParams["FORM_ID"]?>.EnableSettings(true)', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'form-settings-on'},
<?else:?>
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("intarface_form_mnu_off"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("intarface_form_mnu_off_title"))?>', 'ONCLICK': 'bxForm_<?=$arParams["FORM_ID"]?>.EnableSettings(false)', 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'form-settings-off'},
<?endif;?>
<?endif?>
<?endif?>
	{'TEXT': '<?=CUtil::JSEscape(GetMessage("interface_form_colors"))?>', 'TITLE': '<?=CUtil::JSEscape(GetMessage("interface_form_colors_title"))?>', 'CLASS': 'bx-grid-themes-menu-item', 'MENU':[
<?
$i = 0;
foreach($arThemes as $theme):
?>
		<?if($i > 0) echo ','?>{'TEXT': '<?=CUtil::JSEscape($theme["name"])?><?if($theme["theme"] == $arResult["GLOBAL_OPTIONS"]["theme"]) echo ' '.CUtil::JSEscape(GetMessage("interface_form_default"))?>', 'ONCLICK': 'bxForm_<?=$arParams["FORM_ID"]?>.SetTheme(this, \'<?=CUtil::JSEscape($theme["theme"])?>\')'<?if($theme["theme"] == $arResult["OPTIONS"]["theme"] || $theme["theme"] == "grey" && $arResult["OPTIONS"]["theme"] == ''):?>, 'ICONCLASS':'checked'<?endif?>}
<?
	$i++;
endforeach;
?>
	], 'DISABLED':<?=($USER->IsAuthorized()? 'false':'true')?>, 'ICONCLASS':'form-themes'}
];

<?if($arResult["OPTIONS"]["expand_tabs"] == "Y"):?>
BX.ready(function(){bxForm_<?=$arParams["FORM_ID"]?>.ToggleTabs(true);});
<?endif?>
</script>

</div>

<?if($bWasRequired):?>
<div class="bx-form-notes"><span class="required">*</span><?echo GetMessage("interface_form_required")?></div>
<?endif?>
