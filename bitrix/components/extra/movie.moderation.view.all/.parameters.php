<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arTypes = Array();
$db_iblock_type = CIBlockType::GetList(Array("SORT"=>"ASC"));
while($arRes = $db_iblock_type->Fetch())
	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
		$arTypes[$arRes["ID"]] = $arIBType["NAME"];

$arIBlocksMovies=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_MOVIE"]!="-"?$arCurrentValues["IBLOCK_TYPE_MOVIE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksMovies[$arRes["ID"]] = $arRes["NAME"];

$arGroups = array();
$rsGroups = CGroup::GetList($by="c_sort", $order="asc", Array("ACTIVE" => "Y"));
while ($arGroup = $rsGroups->Fetch())
{
	$arGroups[$arGroup["ID"]] = $arGroup["NAME"];
}


$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"AJAX_MODE" => array(),
		"IBLOCK_TYPE_MOVIE" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ MOVIE',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_MOVIE" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ MOVIE',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksMovies,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"FILTER_NAME" => array(
			"PARENT" => "BASE",
			"NAME" => 'FILTER_NAME',
			"TYPE" => "STRING",
			"DEFAULT" => 'arrFilter',
		),
		"COUNT_ELEMENTS" => Array(
			"PARENT" => "BASE",
			"NAME" => 'COUNT_ELEMENTS',
			"TYPE" => "STRING",
			"DEFAULT" => '30',
		),
		"VIEW_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'VIEW_GROUPS',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"MODER_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'MODER_GROUPS',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"ADMIN_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'ADMIN_GROUPS',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
	),
);
if($arCurrentValues["USE_PERMISSIONS"]!="Y")
	unset($arComponentParameters["PARAMETERS"]["GROUP_PERMISSIONS"]);
?>
