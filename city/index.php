<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Билеты Через Интернет.рф - Сделай жизнь легче!");
?>
<?$APPLICATION->IncludeComponent("extra:cinema.view.all", "ver1", array(
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
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>