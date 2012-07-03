<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

### Настроки из .parameters.php
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"])<=0)
 	$arParams["IBLOCK_TYPE"] = "news";

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

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
	$arResult['TF']['SECOND'] = array('KINOAFISHA_NAME','KINOAFISHA_ID','CITY','PHONE','ADDRESS','SITE');
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
		
		foreach($arResult['TF']['SECOND'] as $tempTitle){
			$arTempTemp[$tempTitle] = $arTemp['PROPERTIES'][$tempTitle]['VALUE'];
		}
		
		$arResult['ITEMS'][$arTempTemp['ID']] = $arTempTemp;
	
	}	
	
	
	if(!emptyString($_POST['submitStep1'])){
		if(emptyString($_POST['PARSE_URL'])){
			$arResult['ERRORS'][] = 'Отправили данные, а URL не указали';
		}
		else{
			$arResult['LOG'][] = 'Отправили данные успешно';

			$html = file_get_contents($_POST['PARSE_URL']);
			
			if(emptyString($html)){
				$arResult['ERRORS'][] = 'URL ничего не содержит';
			}else{
				$arResult['LOG'][] = 'Загрузили информацию для парсинга';
			

				$textCharset = detect_encoding($html);
				if($textCharset != 'utf-8'){
					// $html = $APPLICATION->ConvertCharset($html, $textCharset, "UTF-8"); # bitrix
					$html = mb_convert_encoding($html, "UTF-8", $textCharset);
				}
				// $html = $APPLICATION->ConvertCharset($html, 'HTML-ENTITIES', "UTF-8"); # bitrix
				$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'utf-8');

				include('Zend/Dom/Query.php');

				$znd = new Zend_Dom_Query($html);
				foreach ($znd->query('.standart-cols .main .cinemas a') as $topic){
					
					unset($t);
					$temp = dom_to_array($topic);
					
					$t['HREF'] = trim($temp['href']);
					$t['KINOAFISHA_NAME'] = htmlspecialchars(trim($temp['_value']),ENT_QUOTES);

					$preg = '/\d{1,20}/i';
					preg_match($preg, $t['HREF'],$parseData);
					(!empty($parseData[0])) ? $t['KINOAFISHA_ID'] = $parseData[0] : $t['KINOAFISHA_ID'] = '0';
					
					$arResult['PARSE']['~ARRAY'][] = $t;
					
				}

			}
		}
	}
	
	
	if(!emptyString($_POST['submitStep2'])){
		if(emptyArray($_POST['CINEMA_NAME'])){
			$arResult['ERRORS'][] = 'Отправили данные, а CINEMA_NAME не указали/';
		}
		else{
			$arResult['LOG'][] = 'Отправили данные успешно';
		
			// echo '<pre>',print_r($_POST),'</pre>';
			foreach($_POST['CINEMA_NAME'] as $k => $v ){
				
				unset($t);
				
				$t['KINOAFISHA_ID'] = intval($k);
				$t['NAME'] = $t['KINOAFISHA_NAME'] = trim($v);

				$t['ID'] = 0;
				$addUpdate = true;
				if(!emptyArray($arResult['ITEMS'])){
					foreach($arResult['ITEMS'] as $arItem){
						if($t['KINOAFISHA_ID'] == $arItem['KINOAFISHA_ID']){
							if($t['KINOAFISHA_NAME'] != $arItem['KINOAFISHA_NAME']){
								
								if(!emptyArray($arResult['TF']['FIRST'])){
									foreach($arResult['TF']['FIRST'] as $tempTitle){
										$t[$tempTitle] = $arItem[$tempTitle];
									}
								}
								if(!emptyArray($arResult['TF']['SECOND'])){
									foreach($arResult['TF']['SECOND'] as $tempTitle){
										$t[$tempTitle] = $arItem[$tempTitle];
									}
								}
								
								$t['NAME'] = $t['KINOAFISHA_NAME'] = trim($v);
								
							}
							else{
								$addUpdate = false;
							}
						}
					
					}
				}

				
				
				if($addUpdate){
					$arResult['PARSE']['UPDATE'][] = $t;
				}
				
				
			}
			
			if(!emptyArray($arResult['PARSE']['UPDATE'])){
				foreach($arResult['PARSE']['UPDATE'] as $arTempUpd){

					$arUpdateValues = array();
					$arUpdateValues['ID'] = intval($arTempUpd["ID"]);
					$arUpdateValues["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
					$arUpdateValues["SORT"] = 0;
					$arUpdateValues['MODIFIED_BY'] = $arParams['CURRENT_USER_ID'];
					$arUpdateValues['NAME'] = $arTempUpd['NAME'];

					$PROP = array();
					if(!emptyArray($arResult['TF']['SECOND'])){
						foreach($arResult['TF']['SECOND'] as $tempTitle){
							$PROP[$tempTitle] = trim($arTempUpd[$tempTitle]);
						}
						$PROP['CITY'] = $arParams['CITY']['ID'];
					}
					else{
						$arResult['LOG'][] = 'Не найдены города';
					}
					
					if(!emptyArray($PROP)){
						$arUpdateValues["PROPERTY_VALUES"] = $PROP;
					}
					

					if($arParams['EDIT']['UPDATE']){
				
						$oElement = new CIBlockElement();
						if(!empty($arUpdateValues['ID'])){
							$arResult['LOG'][] = 'Обновляем элемент';
						
							if (!$res = $oElement->Update($arUpdateValues['ID'], $arUpdateValues, $bWorkflowIncluded))
							{
								$arResult['LOG'][] = 'Ошибка';
								$arResult["ERRORS"][] = $oElement->LAST_ERROR;
								$arParams['ACCESS']['UPDATED'][0] = false;
							}
							else{
								$arResult['LOG'][] = 'Успешно';
								$arParams['ACCESS']['UPDATED'][1] = true;
							}
						}
						else{
							
							$arResult['LOG'][] = 'Добавляем элемент';
							if (!$arUpdateValues["ID"] = $oElement->Add($arUpdateValues, $bWorkflowIncluded))
							{
								$arResult['LOG'][] = 'Ошибка';
								$arResult["ERRORS"][] = $oElement->LAST_ERROR;
								$arParams['ACCESS']['ADDED'][0] = false;
							}
							else{
								$arResult['LOG'][] = 'Успешно';
								$arParams['ACCESS']['ADDED'][1] = true;
							}
						
						}

					}
					else{
						$arResult['LOG'][] = 'Режим тестирование. Обновление элементов отключено...)';
					}
					echo '<pre>',print_r($arUpdateValues),'</pre>';
					
					// break;
				}

				if(empty($arParams['ACCESS']['UPDATED'][0])){
					$arResult['LOG'][] = 'Успешно';
					if($arParams['EDIT']['REDIRECT']){
						LocalRedirect('?s=1');	
					}
				}
				else{
					$arResult['LOG'][] = 'Были ошибки';
					if($arParams['EDIT']['REDIRECT']){
						LocalRedirect('?s=0');	
					}				
				}
			
			}
			
		
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