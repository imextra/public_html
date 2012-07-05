<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();



	//echo '<pre>',print_r($_GET),'</pre>';exit;

// $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
// if(strlen($arParams["IBLOCK_TYPE"])<=0)
 	// $arParams["IBLOCK_TYPE"] = "news";
	
$arParams['CURRENT_USER_ID'] = $USER->GetID();

### Управление правами пользователей
$arResult['LOG'][] = 'Управление правами пользователей...';
$arGroupsList = array('VIEW_GROUPS');
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

$arResult['LOG'][] = 'Начало скрипта...';


	// echo '<pre>',print_r($arResult),'</pre>';


if($arParams['ACCESS']['VIEW_GROUPS']){
	$arResult['LOG'][] = 'У пользователя есть возможность просматривать информацию';

	$arResult['LOG'][] = 'Подключаем модуль iblock';
	if(!CModule::IncludeModule("iblock"))
	{
		$arResult['LOG'][] = GetMessage("IBLOCK_MODULE_NOT_INSTALLED");
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}

	
	if(!empty($_GET['send'])){
		$arResult['LOG'][] = 'Обрабатываем данные формы...';

//		echo '<pre>',print_r($arResult),'</pre>';
	//	echo '<pre>',print_r($arUpdateValues),'</pre>';
//		echo '<pre>',print_r($_GET),'</pre>';
		
		global $arrFilter;
		$arrFilter = array();

		if(!empty($_GET['ACCOUNT_NUMBER'])){
			$arrFilter['?NAME'] = $_GET['ACCOUNT_NUMBER'];
		}
		
		if(!empty($_GET['ACCOUNT_DATE']) && !empty($_GET['ACCOUNT_DATE_TO'])){
			if(MakeTimeStamp($_GET['ACCOUNT_DATE']) > MakeTimeStamp($_GET['ACCOUNT_DATE_TO'])){
				$_GET['ACCOUNT_DATE_TO'] = $_GET['ACCOUNT_DATE'];
			}
			
			$arrFilter['>=DATE_ACTIVE_FROM'] = $_GET['ACCOUNT_DATE'];
			$arrFilter['<=DATE_ACTIVE_FROM'] = $_GET['ACCOUNT_DATE_TO'];
		}
		
		
/*
 		if(!empty($_GET['CUSTOMER'])){
		
			$arResult['LOG'][] = 'Формируем SELECT запрос для компаний...';
			$arSelect = array(
				'ID',
				'NAME',
				'IBLOCK_ID',
			);

			$arFilter = array(
				"ACTIVE" =>'Y',
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"CHECK_PERMISSIONS" => "Y",
				array(
					"LOGIC" => "OR",
					array("?NAME" => $_GET['CUSTOMER']),
					array("?PROPERTY_NAME_SHORT" => $_GET['CUSTOMER']),
				),
			);

					
			$arSort = array(
				'NAME' => 'ASC',
			);
			$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, $arNav, $arSelect);

			
			$arResult['ITEM_IDS'] = array();
			while($obElement = $rsElement->GetNextElement())
			{
				$arTemp = $obElement->GetFields();
				
				$arResult['ITEM_IDS'][] = $arTemp['ID'];
			
			}
			if(count($arResult['ITEM_IDS'])>0){
				$arrFilter['PROPERTY_CUSTOMER'] = $arResult['ITEM_IDS'];
			}
			else{
				$arrFilter['PROPERTY_CUSTOMER'] = '0';
			}
		}
 */		
		
		
		
	}
	
	
	
	
	
	
	

	$arResult['LOG'][] = 'Задаем настройки формы...';

	$form = array();
	$arFields = array('ACCOUNT_NUMBER','ACCOUNT_DATE','ACCOUNT_DATE_TO');
	foreach($arFields as $f){
		if(!empty($_GET[$f])){
		//echo '---!';
			$form[$f] = $_GET[$f];
		}
		else{
			if(!isset($form[$f])){
				$form[$f] = '';
			}
		}
	}
	
	$arResult['FORM'] = $form;

	// $arResult['LOG'][] = 'Вытаскиваем пользователей из группы SELECTED';
	// if(!empty($arParams['SELECTED_GROUPS'])){

		// $arFilter = array(
			// "GROUPS_ID" => $arParams['SELECTED_GROUPS'],
		// );
		// $rsUsers = CUser::GetList(($by="LAST_NAME"), ($order="asc"), $arFilter); // выбираем пользователей 
		// while($arUsers = $rsUsers->GetNext()){
			// $arTemp = $obUsers->GetFields();
			// unset($arTemp);
			// $arFields = array('ID','LOGIN','NAME','LAST_NAME','EMAIL');
			// foreach($arFields as $f){
				// $arTemp[$f] = $arUsers[$f];
			// }
			// $arTemp['FULL_NAME'] = $arTemp['LAST_NAME'].' '.$arTemp['NAME'];
			// $arResult['USERS_FULL'][$arTemp['ID']] = $arTemp;
		// }


	// }

	
	
}
else{
	$arResult['ERRORS'][] = 'У вас нет доступа для просмотра данной информации.';
}



$this->IncludeComponentTemplate();
	


if($USER->IsAdmin()){
//	echo '<pre>',print_r($arTemp),'</pre>';
//	echo '<pre>',print_r($_GET),'</pre>';
//	echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>