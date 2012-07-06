<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
	
$arParams['CURRENT_USER_ID'] = $USER->GetID();

### ���������� ������� �������������
$arResult['LOG'][] = '���������� ������� �������������...';
$arGroupsList = array('VIEW_GROUPS');
$arGroups = $USER->GetUserGroupArray();
if(!empty($arGroupsList) && count($arGroupsList)>0){
	foreach($arGroupsList as $tGroup){
		if(!is_array($arParams[$tGroup])){
			$arParams[$tGroup] = array();
		}

		$bAllowAccess = count(array_intersect($arGroups, $arParams[$tGroup])) > 0 || $USER->IsAdmin();
		$arParams['ACCESS'][$tGroup] = $bAllowAccess;
		$arResult['LOG'][] = '����� '.$tGroup. ': '.$bAllowAccess;
	}
}
### ���������� ������� �������������

$arResult['LOG'][] = '������ �������...';


if($arParams['ACCESS']['VIEW_GROUPS']){
	$arResult['LOG'][] = '� ������������ ���� ����������� ������������� ����������';

	
	global $arrFilter;
	$arrFilter = array();

	if(!empty($_GET['send'])){
		$arResult['LOG'][] = '������������ ������ �����...';

		if(!emptyString($_GET['FILM_NAME'])){
			$_GET['FILM_NAME'] = htmlspecialchars(trim($_GET['FILM_NAME']));
			$arrFilter['?NAME'] = $_GET['FILM_NAME'];
		}
		
		$_GET['FILM_YEAR'] = intval($_GET['FILM_YEAR']);
		if(!empty($_GET['FILM_YEAR'])){
			$arrFilter['PROPERTY_YEAR'] = $_GET['FILM_YEAR'];
		}
		
		// echo '<pre>',print_r($arrFilter),'</pre>';
		// $arrFilter = array();
	}
	
	
	
	
	
	
	

	$arResult['LOG'][] = '������ ��������� �����...';

	$form = array();
	$arFields = array('FILM_NAME','FILM_YEAR');
	foreach($arFields as $f){
		if(!empty($_GET[$f])){
		//echo '---!';
			$form[$f] = $_GET[$f];
		}
		else{
			if(!isset($form[$f])){
				if($f == 'FILM_YEAR'){
					$form[$f] = '';
					// $form[$f] = date('Y');
					// $arrFilter['PROPERTY_YEAR'] = $form[$f];
				}else{
					$form[$f] = '';
				}
			}
		}
	}
	
	$arResult['FORM'] = $form;

	
}
else{
	$arResult['ERRORS'][] = '� ��� ��� ������� ��� ��������� ������ ����������.';
}



$this->IncludeComponentTemplate();
	


if($USER->IsAdmin()){
//	echo '<pre>',print_r($arTemp),'</pre>';
//	echo '<pre>',print_r($_GET),'</pre>';
//	echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>