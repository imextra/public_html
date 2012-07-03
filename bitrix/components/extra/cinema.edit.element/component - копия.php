<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

### Настроки из .parameters.php
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if(strlen($arParams["IBLOCK_TYPE"])<=0)
 	$arParams["IBLOCK_TYPE"] = "news";

$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);


$arParams["ID"] = intval($arParams["ID"]);
!empty($_POST['ID']) ? $_POST['ID'] = intval($_POST['ID']) : $_POST['ID'] = 0;
if(!empty($_POST['ID'])){
	if($arParams["ID"] != $_POST['ID']){
		$arParams["ID"] = $_POST['ID'];
	}
}
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
	$arResult['LOG'][] = 'У пользователя есть возможность просматривать информацию о компаниях';

	$arResult['LOG'][] = 'Подключаем модуль iblock';
	if(!CModule::IncludeModule("iblock"))
	{
		$arResult['LOG'][] = GetMessage("IBLOCK_MODULE_NOT_INSTALLED");
		ShowErrorMessage(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	$arResult['LOG'][] = 'Подключаем модуль fileman... Для работы с редактором';
	CModule::IncludeModule("fileman");
	
	
	
	$arResult['LOG'][] = 'Задаем настройки по умолчанию';
	$form['ID'] = 0;
	$form['ACTIVE'] = 'Y';
	
	$arResult['TF']['FIRST'] = array('ID','NAME','PREVIEW_PICTURE','PREVIEW_TEXT','ACTIVE');
	$arResult['TF']['SECOND'] = array('ADDRESS','CITY','KINOAFISHA_ID','KINOAFISHA_NAME','PHONE','SITE','BOROUGH_ID','METRO_ID');
	$arDataTemp['ALL'] = array_merge($arDataTemp['FIRST_LEVEL'],$arDataTemp['SECOND_LEVEL']);
	foreach($arDataTemp['ALL'] as $tempTitle){
		(isset($_POST[$tempTitle])) ? $form[$tempTitle] = $_POST[$tempTitle] : $form[$tempTitle] = '';
	}

	$arResult['TF2']['FIRST'] = array('ID','NAME');
	$arResult['TF2']['SECOND'] = array();
	$arResult['TF2']['ALL'] = array_merge($arResult['TF2']['FIRST'],$arResult['TF2']['SECOND']);
	
	if(empty($arParams["ID"])){
		$arResult['LOG'][] = 'ID не нашли... Оставляем настройки по умолчанию';
		
	}
	else{
		$arResult['LOG'][] = 'Нашли ID: '.$arParams["ID"];
		$arSelect = array(
			'IBLOCK_ID',
			'PROPERTY_*',
		);
		$arSelect = array_merge($arSelect,$arResult['TF']['FIRST']);

		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ID" => $arParams["ID"],
			"CHECK_PERMISSIONS" => "Y",
		);

		$rsEventElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
		
		if($obEventElement = $rsEventElement->GetNextElement()){
			$arTemp = $obEventElement->GetFields();
			$arTemp['PROPERTIES'] = $obEventElement->GetProperties();
	// echo '<pre>',print_r($arTemp),'</pre>';
			
			foreach($arResult['TF']['FIRST'] as $tempTitle){
				$form['~'.$tempTitle] = $arTemp[$tempTitle];
			}

			foreach($arResult['TF']['SECOND'] as $tempTitle){
				$form['~'.$tempTitle] = $arTemp['PROPERTIES'][$tempTitle]['VALUE'];
			}

			if(!emptyString($form['PREVIEW_PICTURE'])){
				$form['PREVIEW_PICTURE'] = CFile::GetFileArray($form['PREVIEW_PICTURE']);
			}
			
			
		}
		else{
			$arResult['ERRORS'][] = 'Информация о элементе с ID: '.$arParams["ID"].' не найдена!';
		}
		
	}
	

	$arResult['LOG'][] = 'Проверяем, данные получены из формы или из базы...';
	foreach($arResult['TF']['ALL'] as $tempTitle){
		if(empty($_POST['submitUpdateData'])){
			$arResult['LOG'][] = 'Данные из базы или новая компания...';
			if(empty($form[$tempTitle]) && !empty($form['~'.$tempTitle])){
				$arResult['LOG'][] = 'Обновлено поле: '.$tempTitle;
				$form[$tempTitle] = $form['~'.$tempTitle];
			}
		}
		else{
			$arResult['LOG'][] = 'Данные из формы...';
		}

	}
	

	$arResult['FORM'] = $form;



	
	$arResult['LOG'][] = 'Определяем, может ли пользователь вносить изменения в текущие данные, перед отправкой формы.';

	if($arParams['ACCESS']['ADMIN_GROUPS']){
		$arResult['LOG'][] = 'У пользователя супер права';
		$arParams['ACCESS']['UPDATE_DATA'] = true;
	}elseif($arParams['ACCESS']['EDIT_GROUPS']){
		$arResult['LOG'][] = 'У пользователя супер права';
		$arParams['ACCESS']['UPDATE_DATA'] = true;
	}
	else{
		$arParams['ACCESS']['UPDATE_DATA'] = false;
		$arResult['ERRORS'][] = 'У Вас нет прав для редактирования данной информации. Вы должны быть в специальной группе.';
	}

	
	
	
	
	if(!empty($_POST['submitUpdateData']) && !empty($arParams['ACCESS']['UPDATE_DATA'])){
		$arResult['LOG'][] = 'Обрабатываем данные формы...';
		

		
		$arUpdateValues = array();
		$arUpdateValues['NAME'] = trim($_POST['NAME']);
		$arUpdateValues["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
		$arUpdateValues["SORT"] = 0;
		$arUpdateValues['MODIFIED_BY'] = $arParams['CURRENT_USER_ID'];
		$arUpdateValues['PREVIEW_TEXT'] = TxtToHTML($_POST['PREVIEW_TEXT']);
				
		$PROP = array();
		foreach($arDataTemp['SECOND_LEVEL'] as $tempTitle){
			$PROP[$tempTitle] = trim($_POST[$tempTitle]);
		}
		if(empty($PROP['NAME_SHORT'])){
			$PROP['NAME_SHORT'] = $arUpdateValues['NAME'];
		}
		
		$arUpdateValues["PROPERTY_VALUES"] = $PROP;
		

		$arResult['LOG'][] = 'Проверяем символьный код...';
		if(empty($arResult['FORM']['~CODE'])){
			$arResult['LOG'][] = 'Код не найден, формируем новый код...';
			!empty($PROP['NAME_SHORT']) ? $arUpdateValues["CODE"] = $PROP['NAME_SHORT'] : $arUpdateValues["CODE"] = $arUpdateValues['NAME'];
			
			if(!empty($PROP['COMPANY_TYPE']))
				$arUpdateValues["CODE"] .= '_'.$PROP['COMPANY_TYPE'];
			
			
			$arUpdateValues["CODE"] = CUtil::translit($arUpdateValues["CODE"], 'ru', array('change_case'=>'U')).'_'.RandString(3);
			$arResult['FORM']['CODE'] = $arUpdateValues["CODE"];
			
			$arResult['LOG'][] = 'Установлен код: '.$arResult['FORM']['CODE'];
		}
		else{
			$arResult['FORM']['CODE'] = $arResult['FORM']['~CODE'];
		}

		
		$oElement = new CIBlockElement();
		$arUpdateValues['ID'] = intval($arParams["ID"]);
		if(!empty($arUpdateValues['ID'])){
			$arResult['LOG'][] = 'Обновляем элемент';
		
			if (!$res = $oElement->Update($arUpdateValues['ID'], $arUpdateValues, $bWorkflowIncluded))
			{
				$arResult['LOG'][] = 'Ошибка';
				$arResult['ERRORS'][] = $oElement->LAST_ERROR;
				$arParams['ACCESS']['UPDATED'] = false;
			}
			else{
				$arResult['LOG'][] = 'Успешно';
				$arParams['ACCESS']['UPDATED'] = true;
			}
		}
		else{
			$arResult['LOG'][] = 'Добавляем элемент';
			if (!$arUpdateValues["ID"] = $oElement->Add($arUpdateValues, $bWorkflowIncluded))
			{
				$arResult['LOG'][] = 'Ошибка';
				$arResult['ERRORS'][] = $oElement->LAST_ERROR;
				$arParams['ACCESS']['ADDED'] = false;
			}
			else{
				$arResult['LOG'][] = 'Успешно';
				$arParams['ACCESS']['ADDED'] = true;
			}
		}
		
		
		if($arParams['ACCESS']['ADDED'] || $arParams['ACCESS']['UPDATED']){
		
			if(empty($arResult['ERRORS'])){
				$arResult['LOG'][] = 'Делаем редирект, если нет ошибок!';
				LocalRedirect('?ID='.$arUpdateValues["ID"].'&s=1');	
			}
		}
	}
	
	
	



	
}
else{
	$arResult['ERRORS'][] = 'У вас нет доступа для просмотра данной информации.';
}


	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';

	// echo '<pre>',print_r($arResult),'</pre>';
//	echo '<pre>',print_r($arUpdateValues),'</pre>';
	// echo '<pre>',print_r($arTemp),'</pre>';
	// echo '<pre>',print_r($_POST),'</pre>';
}
$this->IncludeComponentTemplate();

?>