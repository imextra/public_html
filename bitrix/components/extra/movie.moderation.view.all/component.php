<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

### Настроки из .parameters.php
$arParams["IBLOCK_TYPE_MOVIE"] = trim($arParams["IBLOCK_TYPE_MOVIE"]);
if(strlen($arParams["IBLOCK_TYPE_MOVIE"])<=0)
 	$arParams["IBLOCK_TYPE_MOVIE"] = "news";

$arParams["IBLOCK_ID_MOVIE"] = intval($arParams["IBLOCK_ID_MOVIE"]);

$arParams["COUNT_ELEMENTS"] = intval($arParams["COUNT_ELEMENTS"]);
if(empty($arParams["COUNT_ELEMENTS"]))
	$arParams["COUNT_ELEMENTS"] = 30;


$arParams["FILTER_NAME"] = 'arrFilter';
global $$arParams["FILTER_NAME"];
$arrFilter = ${$arParams["FILTER_NAME"]};
if(!is_array($arrFilter))
	$arrFilter = array();
	
### Настроки из .parameters.php


$arParams['CURRENT_USER_ID'] = $USER->GetID();

### Управление правами пользователей
$arResult['LOG'][] = 'Управление правами пользователей...';
$arGroupsList = array('VIEW_GROUPS','MODER_GROUPS','ADMIN_GROUPS');
$arGroups = $USER->GetUserGroupArray();
if(!empty($arGroupsList) && count($arGroupsList)>0){
	foreach($arGroupsList as $tGroup){
		if(!is_array($arParams[$tGroup])){
			$arParams[$tGroup] = array();
		}

		$bAllowAccess = count(array_intersect($arGroups, $arParams[$tGroup])) > 0 || $USER->IsAdmin();
		$arParams['ACCESS'][$tGroup] = $bAllowAccess;
		$arResult['LOG'][] = 'Права '.$tGroup. ': '.$bAllowAccess;
	}
}
### Управление правами пользователей

### Определяем город пользователя
if(!empty($_GET['CITY']) && !empty($GLOBALS['CITY'][$_GET['CITY']])){
	$arParams['CITY'] = $GLOBALS['CITY'][$_GET['CITY']];
}
else{
	if(!empty($GLOBALS['USER_CITY']) && !empty($GLOBALS['CITY'][$GLOBALS['USER_CITY']])){
		$arParams['CITY'] = $GLOBALS['CITY'][$GLOBALS['USER_CITY']];
	}else{
		$arParams['CITY'] = $GLOBALS['CITY'][$GLOBALS['DEFAULT_CITY']];
	}
}
$arParams['CITY']['ID'] = intval($arParams['CITY']['ID']);
### Определяем город пользователя


$arResult['ERRORS'] = array();
$arResult['LOG'] = array();


$arResult['LOG'][] = 'Начало скрипта...';

if(!$arParams['ACCESS']['VIEW_GROUPS']){
	$arResult['ERRORS'][] = 'У вас нет прав для просмотра данных...';
}
else{
	$arResult['LOG'][] = 'У пользователя есть возможность просматривать информацию об элементах';

	$arResult['LOG'][] = 'Подключаем модуль iblock';
	if(!CModule::IncludeModule("iblock"))
	{
		$arResult['LOG'][] = GetMessage("IBLOCK_MODULE_NOT_INSTALLED");
		ShowErrorMessage(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	
	$arResult['TF']['FIRST'] = array('ID','NAME','ACTIVE');
	$arResult['TF']['SECOND'] = array('YEAR');
	$arResult['TF']['ALL'] = array_merge($arResult['TF']['FIRST'],$arResult['TF']['SECOND']);

/*
	$arResult['TF2']['FIRST'] = array('ID','NAME');
	$arResult['TF2']['SECOND'] = array();
	$arResult['TF2']['ALL'] = array_merge($arResult['TF2']['FIRST'],$arResult['TF2']['SECOND']);
 */	
	$arResult['LOG'][] = 'Формируем SELECT запрос...';
	$arSelect = array(
		'IBLOCK_ID',
		'PROPERTY_*',
	);
	$arSelect = array_merge($arSelect,$arResult['TF']['FIRST']);
	

	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID_MOVIE"],
		"CHECK_PERMISSIONS" => "Y",
		// "ACTIVE" =>'Y',
		// "PROPERTY_CITY" => $arParams['CITY']['ID'],
	);

	### фильтры дополнительные
	if(false && !empty($_GET['arrFilter_ff']['NAME'])){
		$arFilterTemp = array(
				"LOGIC" => "OR",
				array("?NAME" => $_GET['arrFilter_ff']['NAME']),
				array("?PROPERTY_NAME_SHORT" => $_GET['arrFilter_ff']['NAME']),
			);
		if(!empty($arResult['ITEMS_IDS']) && count($arResult['ITEMS_IDS'])>0){
			$arFilterTemp[] = array("ID" => $arResult['ITEMS_IDS']);
		}
		$arFilter[] = $arFilterTemp;
	}

	// echo '<pre>',print_r($arFilter),'</pre>';

 	$arNav = array(
		'bShowAll' => false,
		'nPageSize' => $arParams["COUNT_ELEMENTS"],
	);

	$arSort = array(
		'NAME' => 'ASC',
	);

	//print_r($arrFilter);

	$rsElement = CIBlockElement::GetList($arSort,$arFilter, false, $arNav, $arSelect);

 	$arResult['LOG'][] = 'Настройки постраничной навигации...';
	$arResult['NAV']["LIST_CNT"] = $rsElement->SelectedRowsCount();
	$rsElement->NavStart($arParams["COUNT_ELEMENTS"],false);
	$rsElement->nPageWindow = 5;
	$arResult['NAV']["NAV_STRING"] = $rsElement->GetPageNavString(GetMessage("IBLOCK_LIST_PAGES_TITLE"), "", true);
	### $GLOBALS['NavFirstRecordShow'] - данную переменную добавил сам в компоненте components\bitrix\system.pagenavigation\component.php
	(!empty($GLOBALS['NavFirstRecordShow'])) ? $arResult['NAV']["START_ELEMENT"] = $GLOBALS['NavFirstRecordShow'] : $arResult['NAV']["START_ELEMENT"] = 0;
	
	$arResult['ITEMS'] = array();
	while($obElement = $rsElement->GetNextElement())
	{
		$arTemp = $obElement->GetFields();
		$arTemp['PROPERTIES'] = $obElement->GetProperties();

		unset($arTempTemp);

		foreach($arResult['TF']['FIRST'] as $tempTitle){
			$arTempTemp[$tempTitle] = $arTemp[$tempTitle];
		}
		
		foreach($arResult['TF']['SECOND'] as $tempTitle){
			$arTempTemp[$tempTitle] = $arTemp['PROPERTIES'][$tempTitle]['VALUE'];
		}

		
		# Дополнительные настройки
		(!emptyString($arTempTemp['ACTIVE']) && $arTempTemp['ACTIVE'] == 'Y') ? $arTempTemp['ACTIVE'] = true : $arTempTemp['ACTIVE'] = false;
		
		// if(!emptyString($arTempTemp['PREVIEW_PICTURE'])){
			// $arTempTemp['PREVIEW_PICTURE'] = CFile::GetFileArray($arTempTemp['PREVIEW_PICTURE']);
		// }
		
		$arResult['ITEMS'][$arTempTemp['ID']] = $arTempTemp;
		// echo '<pre>',print_r($arTempTemp),'</pre>';
	}	
}


if($USER->IsAdmin()){
/*
<table>
	<tr>
		<td><?php  echo '<pre>',print_r($_POST),'</pre>'; ?></td>
		<td><?php  echo '<pre>',print_r($arResult['ITEM']),'</pre>'; ?></td>
	</tr>
</table>
*/
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
	// echo '<pre>',print_r($_GET),'</pre>';
}
$this->IncludeComponentTemplate();

?>