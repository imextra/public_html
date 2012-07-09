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
	"IBLOCK_TYPE_FILM" => "kinoafisha",
	"IBLOCK_ID_FILM" => "17",
	"IBLOCK_TYPE_PERSONS" => "person",
	"IBLOCK_ID_PERSONS" => "21",
	"IBLOCK_TYPE_DISTRIBUTOR" => "kinoafisha",
	"IBLOCK_ID_DISTRIBUTOR" => "18",
	"IBLOCK_TYPE_GENRE" => "kinoafisha",
	"IBLOCK_ID_GENRE" => "16",
	"IBLOCK_TYPE_COUNTRY" => "location",
	"IBLOCK_ID_COUNTRY" => "19",
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