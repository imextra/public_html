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
if (empty($_POST['ID'])){
	$_POST['ID'] = $arParams["ID"];
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
$arParams['EDIT']['REDIRECT'] = true; # Разрешить redirect страницы 
$arParams['EDIT']['REDIRECT'] = false; # Разрешить redirect страницы 

$arParams['EDIT']['UPDATE'] = true; # Разрешить обновлять элементы на странице
$arParams['EDIT']['UPDATE'] = false; # Разрешить обновлять элементы на странице
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

	$arResult['LOG'][] = 'Подключаем модуль fileman... Для работы с редактором';
	CModule::IncludeModule("fileman");
	
	$arResult['TF']['FIRST'] = array('ID','NAME','PREVIEW_PICTURE','PREVIEW_TEXT','ACTIVE','CODE');
	$arResult['TF']['SECOND'] = array('DOUBLE_ID','ACTORS_ID','DIRECTOR_ID','DISTRIBUTOR_ID','DURATION_TIME','GENRE_ID','COUNTRY_ID','ENG_NAME','YEAR');
	$arResult['TF']['ALL'] = array_merge($arResult['TF']['FIRST'],$arResult['TF']['SECOND']);

	$arResult['TF2']['FIRST'] = array('ID','NAME');
	$arResult['TF2']['SECOND'] = array();
	$arResult['TF2']['ALL'] = array_merge($arResult['TF2']['FIRST'],$arResult['TF2']['SECOND']);




	$form['ID'] = 0;
	$form['ACTIVE'] = 'Y';
	


	if(empty($arParams["ID"])){
		$arResult['LOG'][] = 'ID не нашли... Оставляем настройки по умолчанию';
		
	}
	else{

		$arResult['LOG'][] = 'Формируем SELECT запрос для компаний...';
		$arSelect = array(
			'IBLOCK_ID',
			'PROPERTY_*',
			// 'PREVIEW_TEXT_TYPE','DETAIL_TEXT_TYPE', # Для корректного отображения данных в HTML
		);
		$arSelect = array_merge($arSelect,$arResult['TF']['FIRST']);
				
		
		
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID_FILM"],
			"ID" => $arParams["ID"],
			"CHECK_PERMISSIONS" => "Y",
		);

		// echo '<pre>',print_r($arFilter),'</pre>';

		$arNav = false;
		$arSort = array(
			'NAME' => 'ASC',
		);

		//print_r($arrFilter);

		$rsElement = CIBlockElement::GetList($arSort,$arFilter, false, $arNav, $arSelect);
		$arResult['ITEM'] = array();
		if($obElement = $rsElement->GetNextElement())
		{
			$arTemp = $obElement->GetFields();
			$arTemp['PROPERTIES'] = $obElement->GetProperties();
		// echo '<pre>',print_r($arTemp),'</pre>';

			unset($arTempTemp);

			foreach($arResult['TF']['FIRST'] as $tempTitle){
				$arTempTemp[$tempTitle] = $arTemp[$tempTitle];
			}
			
			foreach($arResult['TF']['SECOND'] as $tempTitle){
				$arTempTemp[$tempTitle] = $arTemp['PROPERTIES'][$tempTitle]['VALUE'];
			}
			
			if(!emptyString($arTempTemp['PREVIEW_PICTURE'])){
				$arTempTemp['PREVIEW_PICTURE'] = CFile::GetFileArray($arTempTemp['PREVIEW_PICTURE']);
			}
			
			if(!emptyArray($arTemp['PROPERTIES'])){
				foreach($arTemp['PROPERTIES'] as $arTempProp){
					$arTempTemp['~PROPERTIES'][$arTempProp['CODE']] = $arTempProp['VALUE'];
				}
			}

			
			$arResult['~ITEM'] = $form = $arTempTemp;
		
		}	
		else{
			$arResult['LOG'][] = 'Не нашли ';
			$arResult['ERRORS'][] = 'Не нашли ID = '.$arParams["ID"].'. Создавать будем новый.';
			$arParams["ID"] = $_POST['ID'] = 0;
		}
		
		



	}					

	
	
	$arResult['PERSONS'] = array();
	if(!empty($arParams["IBLOCK_ID_PERSONS"])){
	
		$arResult['LOG'][] = 'Формируем SELECT запрос...';
		$arSelect = array(
			'IBLOCK_ID',
			// 'PROPERTY_*',
			
		);
		$arSelect = array_merge($arSelect,$arResult['TF2']['FIRST']);
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID_PERSONS"],
			"CHECK_PERMISSIONS" => "Y",
			"ACTIVE" =>'Y',
		);
		$arNav = false;
		$arSort = array(
			'NAME' => 'ASC',
		);

		//print_r($arrFilter);

		$rsElement = CIBlockElement::GetList($arSort,$arFilter, false, $arNav, $arSelect);
		$arResult['PERSONS'] = array();
		while($obElement = $rsElement->GetNextElement())
		{
			$arTemp = $obElement->GetFields();
			// $arTemp['PROPERTIES'] = $obElement->GetProperties();

			unset($arTempTemp);

			foreach($arResult['TF2']['FIRST'] as $tempTitle){
				$arTempTemp[$tempTitle] = $arTemp[$tempTitle];
			}
			
			// foreach($arResult['TF']['SECOND'] as $tempTitle){
				// $arTempTemp[$tempTitle] = $arTemp['PROPERTIES'][$tempTitle]['VALUE'];
			// }
			
			$arResult['PERSONS'][$arTempTemp['ID']] = $arTempTemp;
		
		}	
	}
	

	
	$arResult['DISTRIBUION'] = array();
	if(!empty($arParams["IBLOCK_ID_DISTRIBUION"])){
	
		$arResult['LOG'][] = 'Формируем SELECT запрос...';
		$arSelect = array(
			'IBLOCK_ID',
			// 'PROPERTY_*',
		);
		$arSelect = array_merge($arSelect,$arResult['TF2']['FIRST']);
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID_DISTRIBUION"],
			"CHECK_PERMISSIONS" => "Y",
			"ACTIVE" =>'Y',
		);
		$arNav = false;
		$arSort = array(
			'NAME' => 'ASC',
		);

		//print_r($arrFilter);

		$rsElement = CIBlockElement::GetList($arSort,$arFilter, false, $arNav, $arSelect);
		$arResult['DISTRIBUION'] = array();
		while($obElement = $rsElement->GetNextElement())
		{
			$arTemp = $obElement->GetFields();
			// $arTemp['PROPERTIES'] = $obElement->GetProperties();

			unset($arTempTemp);

			foreach($arResult['TF2']['FIRST'] as $tempTitle){
				$arTempTemp[$tempTitle] = $arTemp[$tempTitle];
			}
			
			// foreach($arResult['TF']['SECOND'] as $tempTitle){
				// $arTempTemp[$tempTitle] = $arTemp['PROPERTIES'][$tempTitle]['VALUE'];
			// }
			
			$arResult['DISTRIBUION'][$arTempTemp['ID']] = $arTempTemp;
		
		}	
	}
	
	
	
	
	
	
	$arResult['GENRE'] = array();
	if(!empty($arParams["IBLOCK_ID_GENRE"])){
		$arResult['LOG'][] = 'Формируем SELECT запрос...';
		$arSelect = array(
			'IBLOCK_ID',
			// 'PROPERTY_*',
		);
		$arSelect = array_merge($arSelect,$arResult['TF2']['FIRST']);
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID_GENRE"],
			"CHECK_PERMISSIONS" => "Y",
			"ACTIVE" =>'Y',
		);
		$arNav = false;
		$arSort = array(
			'NAME' => 'ASC',
		);

		//print_r($arrFilter);

		$rsElement = CIBlockElement::GetList($arSort,$arFilter, false, $arNav, $arSelect);
		$arResult['GENRE'] = array();
		while($obElement = $rsElement->GetNextElement())
		{
			$arTemp = $obElement->GetFields();
			// $arTemp['PROPERTIES'] = $obElement->GetProperties();

			unset($arTempTemp);

			foreach($arResult['TF2']['FIRST'] as $tempTitle){
				$arTempTemp[$tempTitle] = $arTemp[$tempTitle];
			}
			
			// foreach($arResult['TF']['SECOND'] as $tempTitle){
				// $arTempTemp[$tempTitle] = $arTemp['PROPERTIES'][$tempTitle]['VALUE'];
			// }
			
			$arResult['GENRE'][$arTempTemp['ID']] = $arTempTemp;
		
		}	
	}
	
	
	

	echo 'данный блок требует проверки. Строка 322.';
	$arResult['LOG'][] = 'Проверяем и обновляем данные если это требуется.';
	foreach($arResult['TF']['ALL'] as $tempTitle){
	
		if($tempTitle == 'ACTIVE' && !empty($_POST["submitUpdateData"])){
			$arResult['LOG'][] = 'Особая проверка должна быть у свойства ACTIVE.';
			(!empty($_POST[$tempTitle]) && $_POST[$tempTitle] == 'on') ? $_POST[$tempTitle] = 'Y' : $_POST[$tempTitle] = 'N';
		}
	
		if($tempTitle == 'PREVIEW_PICTURE' && !empty($_FILES["PREVIEW_PICTURE"]) && !empty($_POST["submitUpdateData"])){
			$arResult['LOG'][] = 'Особая проверка должна быть у свойства PREVIEW_PICTURE.';
			if (is_uploaded_file($_FILES['PREVIEW_PICTURE']['tmp_name'])) {
				$arResult['LOG'][] = 'Была закачена фотография и добавлено свойство PREVIEW_PICTURE_NEW.';
				$form['PREVIEW_PICTURE_NEW'] = $_FILES["PREVIEW_PICTURE"];
			}
		}
		
		// Если никакой пункт не выбрать то его не будет в _POST, поэтому делаем проверку
		if($tempTitle == 'PERSONS_ID' && !empty($_POST["submitUpdateData"])){
			
			// $_POST[$tempTitle] = array(339,'asdf',22);
			if(emptyArray($_POST[$tempTitle])){
				$_POST[$tempTitle] = array();
			}else{
				# Необходимо сравнить со значениями в базе, чтобы лишнее не добавлять
				unset($tt);
				unset($arTempRet);
				foreach($_POST[$tempTitle] as $tt){
					$tt = intval($tt);
					if(!emptyArray($arResult['PERSONS']) && !empty($tt) && isset($arResult['PERSONS'][$tt])){
						$arTempRet[] = $tt;
					}
				}
				!emptyArray($arTempRet) ? $_POST[$tempTitle] = $arTempRet : $_POST[$tempTitle] = array();
			}
		}
	
		// Если никакой пункт не выбрать то его не будет в _POST, поэтому делаем проверку
		if($tempTitle == 'DISTRIBUION_ID' && !empty($_POST["submitUpdateData"])){

			// $_POST[$tempTitle] = array(339,'asdf',22);
			
			if(emptyArray($_POST[$tempTitle])){
				$_POST[$tempTitle] = array();
			}else{
				# Необходимо сравнить со значениями в базе, чтобы лишнее не добавлять
				unset($tt);
				unset($arTempRet);
				foreach($_POST[$tempTitle] as $tt){
					$tt = intval($tt);
					if(!emptyArray($arResult['DISTRIBUION']) && !empty($tt) && isset($arResult['DISTRIBUION'][$tt])){
						$arTempRet[] = $tt;
					}
				}
				!emptyArray($arTempRet) ? $_POST[$tempTitle] = $arTempRet : $_POST[$tempTitle] = array();
			}
		}
	
		if(isset($_POST[$tempTitle])){
			$arResult['LOG'][] = 'POST: '.$tempTitle.'='.$_POST[$tempTitle];
			(!is_array($_POST[$tempTitle])) ? $form[$tempTitle] = trim($_POST[$tempTitle]) : $form[$tempTitle] = $_POST[$tempTitle];
		}
		else{
			if (empty($arParams["ID"])){
				$arResult['LOG'][] = 'Новое значение: '.$tempTitle;
				$form[$tempTitle] = '';
			}
			else{
				$arResult['LOG'][] = 'Значение из базы: '.$tempTitle;
			}
		}
	}

	$arResult['LOG'][] = 'Указываем доп. настройки';

	// Если удаляем изображение
	if (!emptyString($_POST['PREVIEW_PICTURE_DEL']) && $_POST['PREVIEW_PICTURE_DEL'] == 'on'){
		$form['PREVIEW_PICTURE_DEL'] = 'Y';
	}else{
		$form['PREVIEW_PICTURE_DEL'] = 'N';
	}
	
	$arResult['ITEM'] = $form;
	




	$arResult['LOG'][] = 'Определяем, может ли пользователь вносить изменения в текущие данные, перед отправкой формы.';

	// $arParams['ACCESS']['ADMIN_GROUPS'] = false;
	// $arParams['ACCESS']['MODER_GROUPS'] = false;
	if($arParams['ACCESS']['ADMIN_GROUPS']){
		$arResult['LOG'][] = 'У пользователя супер права';
		$arParams['ACCESS']['UPDATE_DATA'] = true;
	}elseif($arParams['ACCESS']['MODER_GROUPS']){
		$arResult['LOG'][] = 'Пользователь в группе MODER_GROUPS';
		$arParams['ACCESS']['UPDATE_DATA'] = true;
	}
	else{
		$arParams['ACCESS']['UPDATE_DATA'] = false;
		$arResult['ERRORS'][] = 'У Вас нет прав для редактирования данной информации. Вы должны быть в специальной группе.';
	}
	
	if(!empty($_POST['submitUpdateData']) && $arParams['ACCESS']['UPDATE_DATA']){
		$arResult['LOG'][] = 'Проверяем данные на заполнение.';
		if(empty($arResult['ITEM']['NAME'])){
			$arResult['ERRORS'][] = 'Обязательно заполните поле "Название"';
			$arResult['LOG'][] = 'Ошибка: Обязательно заполните поле "Название"';
		}
	}

	
	if(!emptyArray($arResult['ERRORS'])){
		$arResult['LOG'][] = 'Нашли ошибки, поэтому не обновляем данные.';
		unset($_POST['submitUpdateData']);
	}

	
	if(!empty($_POST['submitUpdateData']) && $arParams['ACCESS']['UPDATE_DATA']){
		$arResult['LOG'][] = 'Обрабатываем данные формы...';
		
		$arUpdateValues = array();
		$arUpdateValues["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
		
		foreach($arResult['TF']['FIRST'] as $tempTitle){
			$arUpdateValues[$tempTitle] = $arResult['ITEM'][$tempTitle];
		}
		
		(!emptyArray($arResult['ITEM']['~PROPERTIES'])) ? $arUpdateValues['PROPERTY_VALUES'] = $arResult['ITEM']['~PROPERTIES'] : $arUpdateValues['PROPERTY_VALUES'] = array();
		
		foreach($arResult['TF']['SECOND'] as $tempTitle){
			$arUpdateValues['PROPERTY_VALUES'][$tempTitle] = $arResult['ITEM'][$tempTitle];
		}
		
		$arResult['LOG'][] = 'Специальные настройки';
			
			# Если закачиваем новую картинку.
			if(!emptyArray($arResult['ITEM']['PREVIEW_PICTURE_NEW'])){
				$arUpdateValues['PREVIEW_PICTURE'] = $arResult['ITEM']['PREVIEW_PICTURE_NEW'];
			}else{
				unset($arUpdateValues['PREVIEW_PICTURE']);
			}

			# Если удаляем картинку.
			if(!emptyString($arResult['ITEM']['PREVIEW_PICTURE_DEL']) && $arResult['ITEM']['PREVIEW_PICTURE_DEL'] == "Y"){
				$arUpdateValues['PREVIEW_PICTURE']['del'] = $arResult['ITEM']['PREVIEW_PICTURE_DEL'];
			}
			
			$arResult['LOG'][] = 'Специальные настройки для PREVIEW_TEXT и DETAIL_TEXT_TYPE. Иначе будет геммор с выбодом данных в html ';
			if(!empty($arUpdateValues['PREVIEW_TEXT'])){
				$arUpdateValues['PREVIEW_TEXT_TYPE'] = 'html';
			}
			if(!empty($arUpdateValues['DETAIL_TEXT'])){
				$arUpdateValues['DETAIL_TEXT_TYPE'] = 'html';
			}
			
		
		
		$arResult['UPDATE_VALUES'] = $arUpdateValues;
	
		if(!$arParams['EDIT']['UPDATE']){
			$arResult['LOG'][] = 'Не обновляем, так как режим отладки';
		}else{
			$arResult['LOG'][] = 'Начинаем обновлять информацию.';
			
			$oElement = new CIBlockElement();
			$arUpdateValues['ID'] = intval($arParams["ID"]);
			if(!empty($arUpdateValues['ID'])){
				$arResult['LOG'][] = 'Обновляем элемент';
			
				if (!$res = $oElement->Update($arUpdateValues['ID'], $arUpdateValues, $bWorkflowIncluded, true, true))
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
				if (!$arUpdateValues["ID"] = $oElement->Add($arUpdateValues, $bWorkflowIncluded, true, true))
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
				if(emptyArray($arResult['ERRORS'])){
					$arResult['LOG'][] = 'Делаем редирект, если нет ошибок!';
					if($arParams['EDIT']['REDIRECT']){
						LocalRedirect('?ID='.$arUpdateValues["ID"].'&s=1');	
					}
				}
			}



		}
	
	
	}
	




	
}



/*
<table>
	<tr>
		<td><?php  echo '<pre>',print_r($_POST),'</pre>'; ?></td>
		<td><?php  echo '<pre>',print_r($arResult['ITEM']),'</pre>'; ?></td>
	</tr>
</table>
*/
?>
<?php
	
	// echo '<pre>',print_r($_FILES),'</pre>';
	// $arResult['asdf'] = CFile::MakeFileArray($_FILES['PREVIEW_PICTURE']);
	// echo '<pre>',print_r($arParams),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arResult),'</pre>';
	// echo '<pre>',print_r($_GET),'</pre>';
}
$this->IncludeComponentTemplate();

?>