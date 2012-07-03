<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Редактирование стран");
?><?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"AREA_FILE_SHOW" => "file",
	"PATH" => "/moderation/include_area.php",
	"EDIT_TEMPLATE" => "standard.php"
	),
	false
);?><?$APPLICATION->IncludeComponent("extra:element.simple.edit", "country_ver1", array(
	"IBLOCK_TYPE" => "location",
	"IBLOCK_ID" => "19",
	"USE_CITY" => "0",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "9",
	),
	"MODER_GROUPS" => array(
		0 => "9",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?$APPLICATION->IncludeComponent("extra:element.simple.add", "country_ver1", array(
	"IBLOCK_TYPE" => "location",
	"IBLOCK_ID" => "19",
	"USE_CITY" => "0",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "9",
	),
	"MODER_GROUPS" => array(
		0 => "9",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>