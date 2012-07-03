<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"])<=0)
 	$arParams["IBLOCK_TYPE"] = "news";
	
$arParams['CURRENT_USER_ID'] = $USER->GetID();

### Управление правами пользователей
$arResult['LOG'][] = 'Управление правами пользователей...';
$arGroupsList = array('VIEW_GROUPS','ADMIN_GROUPS');
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


$arResult['LOG'][] = 'Начало скрипта...';


if($arParams['ACCESS']['VIEW_GROUPS']){

	$arResult['LOG'][] = 'У пользователя есть возможность просматривать информацию о компаниях';

	$arResult['LOG'][] = 'Подключаем модуль iblock';
	if(!CModule::IncludeModule("iblock"))
	{
		$arResult['LOG'][] = GetMessage("IBLOCK_MODULE_NOT_INSTALLED");
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	$arResult['TF']['FIRST'] = array('ID','NAME','ACTIVE');
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
		// "ACTIVE" =>'Y',
		"PROPERTY_CITY" => $arParams['CITY']['ID'],
	);

	// echo '<pre>',print_r($arFilter),'</pre>';

	$arNav = false;
	
	$arSort = array(
		'NAME' => 'ASC',
	);

	//print_r($arrFilter);

	$rsElement = CIBlockElement::GetList($arSort,$arFilter, false, $arNav, $arSelect);
	
	$arResult['ITEMS'] = array();
	while($obElement = $rsElement->GetNextElement())
	{
		$arTemp = $obElement->GetFields();
		$arTemp['PROPERTIES'] = $obElement->GetProperties();

		unset($arTempTemp);
		foreach($arResult['TF']['FIRST'] as $tempTitle){
			$arTempTemp[$tempTitle] = $arTemp[$tempTitle];
		}
		
		if ($arTempTemp['ACTIVE'] == "Y"){
			$arResult['ITEMS'][$arTempTemp['ID']] = $arTempTemp;
		}
		else{
			$arResult['ITEMS_NOT_ACTIVE'][$arTempTemp['ID']] = $arTempTemp;
		}
	
	}	
}


if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
	// echo '<pre>',print_r($_GET),'</pre>';
}
$this->IncludeComponentTemplate();

?>