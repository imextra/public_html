<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;

CUtil::InitJSCore(array('ajax', 'popup'));

$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/crm.js');

$fieldName = $arParams["arUserField"]["~FIELD_NAME"];
$formName = $arParams["form_name"];
?>
<div id="crm-<?=$fieldName?>-box">
	<div  class="crm-button-open">
		<a id="crm-<?=$fieldName?>-open" href="#open" onclick="obCrm['<?=$formName?>crm-<?=$fieldName?>-open'].Open()"><?=GetMessage('CRM_FF_CHOISE');?></a>
	</div>
</div>
<script type="text/javascript">
	var _BX_CRM_FIELD_INIT<?=$fieldName?> = function()
	{
		if(typeof(CRM) == 'undefined')
		{
			BX.loadCSS('/bitrix/js/crm/css/crm.css');
			BX.loadScript('/bitrix/js/crm/crm.js', _BX_CRM_FIELD_INIT<?=$fieldName?>);
			return;
		}
		
		CRM.Set(
			BX('crm-<?=$fieldName?>-open'),
			'<?=CUtil::JSEscape($fieldName)?>', '',
			<?echo CUtil::PhpToJsObject($arResult['ELEMENT']);?>,
			<?=($arResult["PREFIX"]=='Y'? 'true': 'false')?>,
			<?=($arResult["MULTIPLE"]=='Y'? 'true': 'false')?>,
			<?echo CUtil::PhpToJsObject($arResult['ENTITY_TYPE']);?>,
			{
				'lead': '<?=CUtil::JSEscape(GetMessage('CRM_FF_LEAD'))?>',
				'contact': '<?=CUtil::JSEscape(GetMessage('CRM_FF_CONTACT'))?>',
				'company': '<?=CUtil::JSEscape(GetMessage('CRM_FF_COMPANY'))?>',
				'deal': '<?=CUtil::JSEscape(GetMessage('CRM_FF_DEAL'))?>',
				'ok': '<?=CUtil::JSEscape(GetMessage('CRM_FF_OK'))?>',
				'cancel': '<?=CUtil::JSEscape(GetMessage('CRM_FF_CANCEL'))?>',
				'close': '<?=CUtil::JSEscape(GetMessage('CRM_FF_CLOSE'))?>',
				'wait': '<?=CUtil::JSEscape(GetMessage('CRM_FF_SEARCH'))?>',
				'noresult': '<?=CUtil::JSEscape(GetMessage('CRM_FF_NO_RESULT'))?>',
				'add' : '<?=CUtil::JSEscape(GetMessage('CRM_FF_CHOISE'))?>',
				'edit' : '<?=CUtil::JSEscape(GetMessage('CRM_FF_CHANGE'))?>',
				'search' : '<?=CUtil::JSEscape(GetMessage('CRM_FF_SEARCH'))?>',
				'last' : '<?=CUtil::JSEscape(GetMessage('CRM_FF_LAST'))?>'
			}
		);
	};

	BX.ready(_BX_CRM_FIELD_INIT<?=$fieldName?>);
</script>