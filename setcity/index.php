<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Билеты Через Интернет.рф - Сделай жизнь легче! - Выбор города");
?>
<?$APPLICATION->IncludeComponent("extra:location.setcity", "setcity.page_ver1", array(
	"IBLOCK_TYPE_CITY" => "location",
	"IBLOCK_ID_CITY" => "12",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>