<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Редактирование");
?><?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"AREA_FILE_SHOW" => "file",
	"PATH" => "/moderation/include_area.php",
	"EDIT_TEMPLATE" => "standard.php"
	),
	false
);?><?$APPLICATION->IncludeComponent("extra:movie.edit.element", "ver1", array(
	"IBLOCK_TYPE" => "kinoafisha",
	"IBLOCK_ID" => "13",
	"IBLOCK_TYPE_METRO" => "location",
	"IBLOCK_ID_METRO" => "15",
	"IBLOCK_TYPE_BOROUGH" => "location",
	"IBLOCK_ID_BOROUGH" => "14",
	"IBLOCK_TYPE_CINEMA_NETWORK" => "kinoafisha",
	"IBLOCK_ID_CINEMA_NETWORK" => "20",
	"ID" => $_REQUEST["ID"],
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "7",
	),
	"MODER_GROUPS" => array(
		0 => "7",
	),
	"ADMIN_GROUPS" => array(
		0 => "7",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>