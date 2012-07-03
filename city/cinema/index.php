<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Кинотеатры");
?><?$APPLICATION->IncludeComponent(
	"extra:cinema.view.element",
	"ver1",
	Array(
		"IBLOCK_TYPE" => "kinoafisha",
		"IBLOCK_ID" => "13",
		"CINEMA_ID" => $_REQUEST["CINEMA_ID"],
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"VIEW_GROUPS" => array(0=>"2",),
		"MODER_GROUPS" => array(0=>"7",),
		"ADMIN_GROUPS" => array(0=>"7",),
		"AJAX_OPTION_ADDITIONAL" => ""
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>