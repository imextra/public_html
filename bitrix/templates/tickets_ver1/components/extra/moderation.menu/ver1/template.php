<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arAdminMenu = array();
if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У вас нет прав для просмотра данных...');
}
else{
	$arResult['LOG'][] = 'У пользователя есть возможность просматривать информацию об элементах';
	$arAdminMenu[] = array('NAME'=>'Главная','URL'=>'/moderation/');
	if($arParams['ACCESS']['LOCATION_GROUPS']){
		$arAdminMenu[] = array('NAME'=>'Страны','URL'=>'/moderation/country/');
		$arAdminMenu[] = array('NAME'=>'Районы города','URL'=>'/moderation/borough/');
		$arAdminMenu[] = array('NAME'=>'Станции метро','URL'=>'/moderation/metro/');
	}

	if($arParams['ACCESS']['CINEMA_SETTINGS_GROUPS']){
		$arAdminMenu[] = array('NAME'=>'Жанры фильмов','URL'=>'/moderation/genre/');
		$arAdminMenu[] = array('NAME'=>'Дистрибьюторы','URL'=>'/moderation/distribution/');
	}

	if($arParams['ACCESS']['CINEMA_GROUPS']){
		$arAdminMenu[] = array('NAME'=>'Кинотеатры','URL'=>'/moderation/cinema/');
	}



	echo '<div id="adminMenu">';
	if(!empty($arAdminMenu) && count($arAdminMenu)>0){
		echo '<ul>';
		foreach($arAdminMenu as $arTempMenu){
			echo '<li><a href="'.$arTempMenu['URL'].'" title="'.$arTempMenu['NAME'].'">'.$arTempMenu['NAME'].'</a></li>';
		}
		echo '</ul>';
	}
	echo '</div>';

	
}

?>
<?




	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>