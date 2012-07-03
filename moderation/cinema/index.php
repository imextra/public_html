<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Кинотеатры");
?><?$APPLICATION->IncludeComponent("bitrix:main.include", ".default", array(
	"AREA_FILE_SHOW" => "file",
	"PATH" => "/moderation/include_area.php",
	"EDIT_TEMPLATE" => "standard.php"
	),
	false
);?><?$APPLICATION->IncludeComponent("extra:cinema.view.all", "mod_ver1", array(
	"IBLOCK_TYPE" => "kinoafisha",
	"IBLOCK_ID" => "13",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "2",
	),
	"ADMIN_GROUPS" => array(
		0 => "7",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>
<?$APPLICATION->IncludeComponent("extra:cinema.parse.all.kinoafisha", "ver1", array(
	"IBLOCK_TYPE" => "kinoafisha",
	"IBLOCK_ID" => "13",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "11",
	),
	"MODER_GROUPS" => array(
		0 => "11",
	),
	"ADMIN_GROUPS" => array(
		0 => "11",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>