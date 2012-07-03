<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

### Настроки из .parameters.php
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"])<=0)
 	$arParams["IBLOCK_TYPE"] = "news";

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);


$arParams["ID"] = intval($arParams["ID"]);
/*
Если есть редактирование
!empty($_POST['ID']) ? $_POST['ID'] = intval($_POST['ID']) : $_POST['ID'] = 0;
if(!empty($_POST['ID'])){
	if($arParams["ID"] != $_POST['ID']){
		$arParams["ID"] = $_POST['ID'];
	}
}
*/

$arParams["COUNT_ELEMENTS"] = intval($arParams["COUNT_ELEMENTS"]);
if(empty($arParams["COUNT_ELEMENTS"]))
	$arParams["COUNT_ELEMENTS"] = 30;
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
### Редактирование элементов
$arParams['EDIT']['REDIRECT'] = false; # Разрешить redirect страницы 
$arParams['EDIT']['UPDATE'] = false; # Разрешить обновлять элементы на странице
$arParams['EDIT']['REDIRECT'] = true; # Разрешить redirect страницы 
$arParams['EDIT']['UPDATE'] = true; # Разрешить обновлять элементы на странице
### Редактирование элементов


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

	
	$arResult['TF']['FIRST'] = array('ID','NAME');
	$arResult['TF']['SECOND'] = array();
	$arResult['TF']['ALL'] = array_merge($arResult['TF']['FIRST'],$arResult['TF']['SECOND']);

	$arResult['LOG'][] = 'Формируем SELECT запрос для компаний...';
	$arSelect = array(
		'IBLOCK_ID',
		'PROPERTY_*',
	);
	$arSelect = array_merge($arSelect,$arResult['TF']['FIRST']);
	

	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CHECK_PERMISSIONS" => "Y",
		"ACTIVE" =>'Y',
		"PROPERTY_CITY" => $arParams['CITY']['ID'],
	);

	// echo '<pre>',print_r($arFilter),'</pre>';

	$arNav = false;
/* 	$arNav = array(
		'bShowAll' => false,
		'nPageSize' => $arParams["COUNT_ELEMENTS"],
	);
 */	
	$arSort = array(
		'NAME' => 'ASC',
	);

	//print_r($arrFilter);

	$rsElement = CIBlockElement::GetList($arSort,$arFilter, false, $arNav, $arSelect);

/* 	$arResult['LOG'][] = 'Настройки постраничной навигации...';
	$arResult["LIST_CNT"] = $rsElement->SelectedRowsCount();
	$arParams["NAV_ON_PAGE"] = intval($arParams["COUNT_ELEMENTS"]);
	$rsElement->NavStart($arParams["NAV_ON_PAGE"],false);
	$rsElement->nPageWindow = 5;
	$arResult["NAV_STRING"] = $rsElement->GetPageNavString(GetMessage("IBLOCK_LIST_PAGES_TITLE"), "", true);
 */	
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
		
		$arResult['ITEMS'][$arTempTemp['ID']] = $arTempTemp;
	
	}	
}


if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
	// echo '<pre>',print_r($_GET),'</pre>';
}
$this->IncludeComponentTemplate();

?>