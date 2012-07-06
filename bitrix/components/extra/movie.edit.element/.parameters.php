<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arTypes = Array();
$db_iblock_type = CIBlockType::GetList(Array("SORT"=>"ASC"));
while($arRes = $db_iblock_type->Fetch())
	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
		$arTypes[$arRes["ID"]] = $arIBType["NAME"];

$arIBlocksFilm=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_FILM"]!="-"?$arCurrentValues["IBLOCK_TYPE_FILM"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksFilm[$arRes["ID"]] = $arRes["NAME"];

$arIBlocksPersons=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_PERSONS"]!="-"?$arCurrentValues["IBLOCK_TYPE_PERSONS"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksPersons[$arRes["ID"]] = $arRes["NAME"];

$arIBlocksDistribution=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_DISTRIBUION"]!="-"?$arCurrentValues["IBLOCK_TYPE_DISTRIBUION"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksDistribution[$arRes["ID"]] = $arRes["NAME"];

$arIBlocksGenre=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_GENRE"]!="-"?$arCurrentValues["IBLOCK_TYPE_GENRE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksGenre[$arRes["ID"]] = $arRes["NAME"];

$arIBlocksCountry=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_COUNTRY"]!="-"?$arCurrentValues["IBLOCK_TYPE_COUNTRY"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksCountry[$arRes["ID"]] = $arRes["NAME"];

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
		"IBLOCK_TYPE_FILM" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ FILM',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_FILM" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ FILM',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksFilm,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE_PERSONS" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ PERSONS',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_PERSONS" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ PERSONS',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksPersons,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE_DISTRIBUION" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ DISTRIBUION',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_DISTRIBUION" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ DISTRIBUION',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksDistribution,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE_GENRE" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ GENRE',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_GENRE" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ GENRE',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksGenre,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE_COUNTRY" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ COUNTRY',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_COUNTRY" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ COUNTRY',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksCountry,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"ID" => array(
			"PARENT" => "BASE",
			"NAME" => 'ID',
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ID"]}',
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
