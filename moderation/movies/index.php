<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Фильмы");
?><?$APPLICATION->IncludeComponent(
	"bitrix:main.include",
	".default",
	Array(
		"AREA_FILE_SHOW" => "file",
		"PATH" => "/moderation/include_area.php",
		"EDIT_TEMPLATE" => "standard.php"
	)
);?>
<?$APPLICATION->IncludeComponent("extra:movie.moderation.filter.view.all", "ver1", array(
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "10",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>

<?$APPLICATION->IncludeComponent("extra:movie.moderation.view.all", "ver1", array(
	"IBLOCK_TYPE_MOVIE" => "kinoafisha",
	"IBLOCK_ID_MOVIE" => "17",
	"FILTER_NAME" => "arrFilter",
	"COUNT_ELEMENTS" => "30",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"VIEW_GROUPS" => array(
		0 => "8",
	),
	"MODER_GROUPS" => array(
		0 => "10",
	),
	"ADMIN_GROUPS" => array(
		0 => "10",
	),
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>






<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>