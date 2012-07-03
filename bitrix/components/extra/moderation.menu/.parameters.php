<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;


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
		"ROOT_ADMIN_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'ROOT_ADMIN_GROUPS (admin groups only)',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"VIEW_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'VIEW_GROUPS (Special groups only)',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"LOCATION_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'LOCATION_GROUPS (borough, country, metro)',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"CINEMA_SETTINGS_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'CINEMA_SETTINGS_GROUPS (genre, distributions, films, persons)',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"CINEMA_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'CINEMA_GROUPS (can edit cinemas)',
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"NEWS_GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => 'NEWS_GROUPS (news)',
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
