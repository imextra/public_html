<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams["IBLOCK_TYPE_CITY"] = trim($arParams["IBLOCK_TYPE_CITY"]);
if(strlen($arParams["IBLOCK_TYPE_CITY"])<=0)
 	$arParams["IBLOCK_TYPE_CITY"] = "news";	

$arParams["IBLOCK_ID_CITY"] = intval($arParams["IBLOCK_ID_CITY"]);

$arResult['LOG'][] = 'Начало скрипта...';
$arResult['ERRORS'] = array();



	

$arResult['LOG'][] = 'Подключаем модуль iblock';
if(!CModule::IncludeModule("iblock"))
{
	$arResult['LOG'][] = GetMessage("IBLOCK_MODULE_NOT_INSTALLED");
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}


$arResult['TF']['FIRST'] = array('ID','NAME','IBLOCK_SECTION_ID');
$arResult['TF']['SECOND'] = array('SHORT_NAME_RUS','NAME_ENG','SHORT_NAME_ENG','KINOAFISHA_URL');
$arResult['TF']['ALL'] = array_merge($arResult['TF']['FIRST'],$arResult['TF']['SECOND']);


$arResult['TF2']['FIRST'] = array('ID','NAME');
$arResult['TF2']['SECOND'] = array();
$arResult['TF2']['ALL'] = array_merge($arResult['TF2']['FIRST'],$arResult['TF2']['SECOND']);

$arResult['LOG'][] = 'Формируем SELECT запрос...';
$arFilter = array(
	"ACTIVE" =>'Y',
	"IBLOCK_ID" => $arParams["IBLOCK_ID_CITY"],
	"CHECK_PERMISSIONS" => "Y",
);
$arSort = array(
	'ID' => 'ASC',
);

$arResult['SECTION'] = array();
$rsElement = CIBlockSection::GetList($arSort, $arFilter, true);
while($arTempSection = $rsElement->GetNext())
{
	unset($arTempData);

	foreach($arResult['TF2']['FIRST'] as $f){
		$arTempData[$f] = $arTempSection[$f];
	}
	
	$arResult['SECTION'][$arTempData['ID']] = $arTempData;
}


if(!emptyArray($arResult['SECTION'])){
	$arResult['LOG'][] = 'Найдено стран: '.count($arResult['SECTION']);
}
else{
	$arResult['LOG'][] = 'Найдено стран: 0';
	$arResult['ERRORS'][] = 'Страны не найдены.';
}



$arResult['LOG'][] = 'Формируем SELECT запрос...';
$arSelect = array_merge($arResult['TF']['FIRST'],array('IBLOCK_ID','PROPERTY_*'));
$arFilter = array(
	"ACTIVE" =>'Y',
	"IBLOCK_ID" => $arParams["IBLOCK_ID_CITY"],
	"CHECK_PERMISSIONS" => "Y",
);
$arNav = false;
$arSort = array(
	'ID' => 'ASC',
);
$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, $arNav, $arSelect);
$arResult['ITEMS'] = array();
while($obElement = $rsElement->GetNextElement())
{
	unset($arTempData);

	$arTemp = $obElement->GetFields();
	$arTemp['PROPERTIES'] = $obElement->GetProperties();
	
	foreach($arResult['TF']['FIRST'] as $f){
		$arTempData[$f] = $arTemp[$f];
	}
	
	foreach($arResult['TF']['SECOND'] as $f){
		$arTempData[$f] = $arTemp['PROPERTIES'][$f]['VALUE'];
	}

	$arTempData['SHORT_NAME_ENG_LOWER'] = mb_strtolower($arTempData['SHORT_NAME_ENG']);
	
	if(!isset($arResult['ITEMS'][$arTempData['SHORT_NAME_ENG_LOWER']])){
		$arResult['ITEMS'][$arTempData['SHORT_NAME_ENG_LOWER']] = $arTempData;
	}
	
	$arResult['SECTION'][$arTempData['IBLOCK_SECTION_ID']]['ITEMS'][$arTempData['ID']] = $arTempData;

}

if(!emptyArray($arResult['ITEMS'])){
	$arResult['LOG'][] = 'Найдено городов: '.count($arResult['ITEMS']);
	$GLOBALS['CITY'] = $arResult['ITEMS'];
}
else{
	$arResult['LOG'][] = 'Найдено городов: 0';
	$arResult['ERRORS'][] = 'Города не найдены.';
}




$arResult['LOG'][] = 'Определяем город пользователя...';
if(!empty($_COOKIE['USER_CITY']) && isset($arResult['ITEMS'][$_COOKIE['USER_CITY']])){
	$arResult['LOG'][] = 'Значение $_COOKIE[\'USER_CITY\'] непустое и есть в базе данных.';
	$arResult['LOG'][] = 'Устанавливаем значение $arResult[\'USER_CITY\'] = '.$_COOKIE['USER_CITY'];
	$arResult['USER_CITY'] = $_COOKIE['USER_CITY'];
}
elseif(empty($_COOKIE['USER_CITY']) && !emptyString($_GET['CITY']) && isset($arResult['ITEMS'][$_GET['CITY']])){
	$arResult['LOG'][] = 'Записываем значение кукис';
	$_GET['ACT'] = 'setcity';
	$_GET['REDIRECT'] = false;
	$_GET['BACK_URL'] = $APPLICATION->GetCurPageParam(false, array("ACT",'REDIRECT','BACK_URL'));;
}
else{
	$arResult['LOG'][] = 'Значение $_COOKIE[\'USER_CITY\'] не найдено.';

	$arResult['LOG'][] = 'Определяем город пользователя через ipgeobase.ru';

	$arResult['TEMP']['defineUserCity2ipgeobase'] = defineUserCity2ipgeobase($arResult['ITEMS']);
	if(!emptyArray($arResult['TEMP']['defineUserCity2ipgeobase']['LOG'])){
		$arResult['LOG'] = array_merge($arResult['LOG'],$arResult['TEMP']['defineUserCity2ipgeobase']['LOG']);
	}
	
	if(!emptyArray($arResult['TEMP']['defineUserCity2ipgeobase']['ERRORS'])){
		$arResult['ERRORS'] = array_merge($arResult['ERRORS'],$arResult['TEMP']['defineUserCity2ipgeobase']['ERRORS']);
	}
	
	if(!empty($arResult['TEMP']['defineUserCity2ipgeobase']['DATA'])){
		$arResult['USER_CITY'] = $arResult['TEMP']['defineUserCity2ipgeobase']['DATA'];
	}
	else{
		$arResult['LOG'][] = 'Определяем город из значений по умолчанию';
		if(!empty($GLOBALS['DEFAULT_CITY']) && isset($arResult['ITEMS'][$GLOBALS['DEFAULT_CITY']])){
			$arResult['LOG'][] = 'Нашли значение: '.$GLOBALS['DEFAULT_CITY'];
			$arResult['USER_CITY'] = $GLOBALS['DEFAULT_CITY'];
		}
		else{
			$arResult['LOG'][] = 'Значение по умолчанию не нашли';
			$arResult['ERRORS'][] = 'Значение по умолчанию не нашли';
			$arResult['USER_CITY'] = false;
		}
	}
	
	if(!empty($arResult['USER_CITY']) && empty($_COOKIE['USER_CITY'])){
		$arResult['LOG'][] = 'Записываем значение кукис';
		$_GET['ACT'] = 'setcity';
		$_GET['CITY'] = $arResult['USER_CITY'];
		$_GET['REDIRECT'] = false;
		$_GET['BACK_URL'] = $APPLICATION->GetCurPageParam(false, array("ACT",'REDIRECT','BACK_URL'));;
	}
}






if(!empty($_GET['ACT']) && $_GET['ACT'] == 'setcity' && !empty($_GET['CITY']) && isset($arResult['ITEMS'][$_GET['CITY']])){
	$arResult['LOG'][] = 'Изменяем город пользователя если это требуется';
	$arResult['USER_CITY'] = $_GET['CITY'];
	$arResult['LOG'][] = 'Можно менять город на указанный пользователем';
	setcookie("USER_CITY", $arResult['USER_CITY'], time()+(3600*24*100),'/'); 
	$arResult['LOG'][] = 'Делаем редирект на город пользователя';

	if(!emptyString($_GET['BACK_URL'])){
		$arResult['LOG'][] = 'Нашли BACK_URL';
		if($_GET['REDIRECT']){
			$arResult['LOG'][] = 'Делаем редирект';			
			LocalRedirect($_GET['BACK_URL']);
		}
		else{
			$arResult['LOG'][] = 'НЕ делаем редирект';
		}
	}else{
		LocalRedirect('/city/?CITY='.$arResult['USER_CITY']);		
	}
}

$GLOBALS['USER_CITY'] = $arResult['USER_CITY'];

if(!empty($_GET['ACT']) && $_GET['ACT'] == 'clearcity'){
	$arResult['LOG'][] = 'Удаляем данные о городе из куки, если требуется...';
	setcookie("USER_CITY", '', time()+(3600*24*100),'/'); 
	$redirectUrl = $APPLICATION->GetCurPageParam(false, array("ACT",'REDIRECT','BACK_URL'));
	// echo $redirectUrl;
	LocalRedirect($redirectUrl);
}

$arResult['LOG'][] = 'Определяем это страница выбора городов или обычная страница';

if(!empty($_GET['CITY']) && isset($arResult['ITEMS'][$_GET['CITY']])){
	$arResult['LOG'][] = 'Найден город пользователя';
	$arResult['CURRENT_CITY'] = $_GET['CITY'];
}else{
	$arResult['CURRENT_CITY'] = false;
}

	
// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
// echo '<pre>',print_r($arParams),'</pre>';
// echo '<pre>',print_r($arResult),'</pre>';
// echo '<pre>',print_r($_COOKIE),'</pre>';
// echo '<pre>',print_r($GLOBALS),'</pre>';
// exit;
}
$this->IncludeComponentTemplate();

?>