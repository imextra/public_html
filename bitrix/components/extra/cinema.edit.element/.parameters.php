<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arTypes = Array();
$db_iblock_type = CIBlockType::GetList(Array("SORT"=>"ASC"));
while($arRes = $db_iblock_type->Fetch())
	if($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG))
		$arTypes[$arRes["ID"]] = $arIBType["NAME"];

$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

$arIBlocksMetro=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_METRO"]!="-"?$arCurrentValues["IBLOCK_TYPE_METRO"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksMetro[$arRes["ID"]] = $arRes["NAME"];

$arIBlocksBorough=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_BOROUGH"]!="-"?$arCurrentValues["IBLOCK_TYPE_BOROUGH"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksBorough[$arRes["ID"]] = $arRes["NAME"];

$arIBlocksCinemaNetwork=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE_CINEMA_NETWORK"]!="-"?$arCurrentValues["IBLOCK_TYPE_CINEMA_NETWORK"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocksCinemaNetwork[$arRes["ID"]] = $arRes["NAME"];

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
		"IBLOCK_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ CINEMA',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ CINEMA',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE_METRO" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ METRO',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_METRO" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ METRO',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksMetro,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE_BOROUGH" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ BOROUGH',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_BOROUGH" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ BOROUGH',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksBorough,
			"DEFAULT" => '',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE_CINEMA_NETWORK" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_TYPE _ CINEMA_NETWORK',
			"TYPE" => "LIST",
			"VALUES" => $arTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID_CINEMA_NETWORK" => Array(
			"PARENT" => "BASE",
			"NAME" => 'IBLOCK_ID _ CINEMA_NETWORK',
			"TYPE" => "LIST",
			"VALUES" => $arIBlocksCinemaNetwork,
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
