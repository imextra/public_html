<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

### Настроки из .parameters.php
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"])<=0)
 	$arParams["IBLOCK_TYPE"] = "news";

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);

$arParams["USE_CITY"] = intval($arParams["USE_CITY"]);
if(!empty($arParams["USE_CITY"]) && $arParams["USE_CITY"] == 1){
	$arParams["USE_CITY"] = true;
}
else{
	$arParams["USE_CITY"] = false;
}

### Настроки из .parameters.php


$arParams['CURRENT_USER_ID'] = $USER->GetID();

### Управление правами пользователей
$arResult['LOG'][] = 'Управление правами пользователей...';
$arGroupsList = array('VIEW_GROUPS','MODER_GROUPS');
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
	
	
	
	$arResult['TF']['FIRST'] = array('ID','NAME','PREVIEW_TEXT','ACTIVE');
	$arResult['TF']['SECOND'] = array();
	if($arParams["USE_CITY"]){
		$arResult['TF']['SECOND'][] = 'CITY';
	}
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
	);
	
	if($arParams["USE_CITY"])
		$arFilter["PROPERTY_CITY"] = $arParams['CITY']['ID'];

	// echo '<pre>',print_r($arFilter),'</pre>';

	$arNav = false;
	$arSort = array(
		'ACTIVE' => 'ASC',
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
		
		$arTempTemp['PREVIEW_TEXT'] = HTMLToTxt($arTemp['~PREVIEW_TEXT'],"",array(),0);


		if(emptyString($arTempTemp['ACTIVE']) || (emptyString($arTempTemp['ACTIVE']) && $arTempTemp['ACTIVE'] != 'Y')){
			$arTempTemp['ACTIVE'] = 'N';
		}

		$arResult['ITEMS']['IDS'][$arTempTemp['ID']] = $arTempTemp['ID'];
		$arResult['ITEMS']['ALL'][$arTempTemp['ID']] = $arTempTemp;
		$arResult['ITEMS']['SORTED'][$arTempTemp['ACTIVE']][$arTempTemp['ID']] = $arTempTemp;

	}	
	
	

	if($arParams['ACCESS']['MODER_GROUPS']){
		$arResult['LOG'][] = 'Можем редактировать данные';

		// echo '<pre>',print_r($_POST),'</pre>';
		
		if(!emptyString($_POST['submitAddData'])){
			$arResult['LOG'][] = 'Отправили форму';
			$arResult['ADD'] = array();
			if(emptyString($_POST['NAMES'])){
				$arResult['ERRORS'][] = 'Не нашли данные...';
			}
			else{
				$arResult['ERRORS'][] = 'Нашли данные...';
				
				$arResult['~NAMES'] = preg_split("/[\n\r]+/s",$_POST['NAMES']);
				
				foreach($arResult['~NAMES'] as $tempName){
					unset($arT);

					$arT['NAME'] = trim($tempName);
					$arT['ACTIVE'] = 'Y';
					$arT['PREVIEW_TEXT'] = '';

					if($arParams["USE_CITY"]){
						$arT['CITY'] = $arParams['CITY']['ID'];
					}

					if(!emptyString($arT['NAME']) && !isset($arResult['~ADD'][$arT['NAME']])){
						$arResult['LOG'][] = 'Добавляем уникальный элемент...';
						$arResult['ADD'][] = $arT;
						$arResult['~ADD'][$arT['NAME']] = $arT;
					}
				}


			}
			
			
			if(!emptyArray($arResult['ADD'])){
				unset($arTempUpd);
				foreach($arResult['ADD'] as $arTempUpd){
					$arResult['CAN_ADD'] = true;

					foreach($arResult['ITEMS']['ALL'] as $arTempItem){
						if($arTempItem['NAME'] == $arTempUpd['NAME']){
							$arResult['LOG'][] = 'Повторный элемент: '.$arTempItem['NAME'];
							$arResult['CAN_ADD'] = false;
							break;
						}
					}
					
					if($arResult['CAN_ADD']){

					
						$arUpdateValues = array();
						$arUpdateValues["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
						$arUpdateValues["SORT"] = 0;
						$arUpdateValues['MODIFIED_BY'] = $arParams['CURRENT_USER_ID'];

						$arUpdateValues['NAME'] = trim($arTempUpd['NAME']);
						$arUpdateValues['ACTIVE'] = trim($arTempUpd['ACTIVE']);
						$arUpdateValues['PREVIEW_TEXT'] = TxtToHTML($arTempUpd['PREVIEW_TEXT']);

						$PROP = array();
						if(!emptyArray($arResult['TF']['SECOND'])){
							foreach($arResult['TF']['SECOND'] as $tempTitle){
								$PROP[$tempTitle] = trim($arTempUpd[$tempTitle]);
							}
							if($arParams["USE_CITY"]){
								$PROP['CITY'] = intval($PROP['CITY']);
							}
						}
						else{
							$arResult['LOG'][] = 'Не найдены города';
						}
						
						if(!emptyArray($PROP)){
							$arUpdateValues["PROPERTY_VALUES"] = $PROP;
						}
						
						if($arParams['EDIT']['UPDATE']){
					
							$oElement = new CIBlockElement();
							$arUpdateValues['ID'] = intval($arTempUpd["ID"]);
							if(empty($arUpdateValues['ID'])){
								$arResult['LOG'][] = 'Обновляем элемент';
							
								if (!$arUpdateValues["ID"] = $oElement->Add($arUpdateValues, $bWorkflowIncluded)){
									$arResult['LOG'][] = 'Ошибка';
									$arResult['ERRORS'][] = $oElement->LAST_ERROR;
									$arParams['ACCESS']['UPDATED'][0] = true;
								}
								else{
									$arParams['ACCESS']['UPDATED'][1] = true;
								}
							}
						}
						else{
							$arResult['LOG'][] = 'Режим тестирование. Обновление элементов отключено...)';
						}
						echo '<pre>',print_r($arUpdateValues),'</pre>';
						// echo '<pre>',print_r($arTempUpd),'</pre>';
					}
					else{
						// $arResult['LOG'][] = 'Повторный элемент...';
					}
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
	// echo '<pre>',print_r($_POST),'</pre>';
}
$this->IncludeComponentTemplate();

?>