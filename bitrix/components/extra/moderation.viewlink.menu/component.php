<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();



$arParams['CURRENT_USER_ID'] = $USER->GetID();

### Управление правами пользователей
$arResult['LOG'][] = 'Управление правами пользователей...';
$arGroupsList = array('MODER_GROUPS');
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


$arResult['ERRORS'] = array();
$arResult['LOG'] = array();


$arResult['LOG'][] = 'Начало скрипта...';

if(!$arParams['ACCESS']['MODER_GROUPS']){
	$arResult['ERRORS'][] = 'У вас нет прав для просмотра данных...';
}
else{
	$arResult['LOG'][] = 'У пользователя есть возможность просматривать информацию об элементах';
	echo '<br />';
	echo '<a href="/moderation/">Администрирование</a>';
}


if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
	// echo '<pre>',print_r($_GET),'</pre>';
}
// $this->IncludeComponentTemplate();

?>