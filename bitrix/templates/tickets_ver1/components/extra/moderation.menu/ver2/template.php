<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arAdminMenu = array();
if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У вас нет прав для просмотра данных...');
}
else{
	$arResult['LOG'][] = 'У пользователя есть возможность просматривать информацию об элементах';

	
	$arAdminMenu = array(
		array(
			'NAME'=>'Юзеры',
			'CODE'=>'USERS',
			'ACCESS'=>array('ROOT_ADMIN_GROUPS'),
			'SUB_MENU'=>array(
				array(
					'NAME'=>'Редактирование юзеров',
					'URL'=>'/bitrix/admin/user_admin.php?lang=ru',
				),
				array(
					'NAME'=>'Редактирование групп',
					'URL'=>'/bitrix/admin/group_admin.php?lang=ru',
				),
			),
		),
		array(
			'NAME'=>'Месторасположение',
			'CODE'=>'LOCATION',
			'ACCESS'=>array('LOCATION_GROUPS'),
			'SUB_MENU'=>array(
				array(
					'NAME'=>'location.setcity',
					'URL'=>'/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=12&type=location',
				),
				array(
					'NAME'=>'Районы города',
					'URL'=>'/moderation/borough/',
				),
				array(
					'NAME'=>'Станции метро',
					'URL'=>'/moderation/metro/',
				),
			),
		),
		array(
			'NAME'=>'Кинотеатры',
			'CODE'=>'CINEMAS',
			'ACCESS'=>array('CINEMA_GROUPS'),
			'SUB_MENU'=>array(
				array(
					'NAME'=>'Кинотеатры',
					'URL'=>'/moderation/cinema/',
				),
				array(
					'NAME'=>'Сети кинотеатров',
					'URL'=>'/moderation/cinemanetwork/',
				),
			),
		),
		array(
			'NAME'=>'#Фильмы',
			'CODE'=>'FILMS',
			'ACCESS'=>array('CINEMA_SETTINGS_GROUPS'),
			'SUB_MENU'=>array(
				array(
					'NAME'=>'Жанры фильмов',
					'URL'=>'/moderation/genre/',
				),
				array(
					'NAME'=>'Дистрибьюторы фильмов',
					'URL'=>'/moderation/distribution/',
				),
				array(
					'NAME'=>'#Фильмы',
					'URL'=>'/moderation/movies/',
				),
				array(
					'NAME'=>'Персоны',
					'URL'=>'/moderation/persons/',
				),
			),
		),
		array(
			'NAME'=>'#Новости',
			'CODE'=>'NEWS',
			'ACCESS'=>array('NEWS_GROUPS'),
			'SUB_MENU'=>array(
				array(
					'NAME'=>'#Лента новостей',
					'URL'=>'/moderation/news/',
				),
			),
		),
	);

// echo '<pre>',print_r($arAdminMenu),'</pre>';

if(!emptyArray($arAdminMenu)){

?><script>
$(function() {
	$( "#adminMenuContent" ).tabs({
		event: "click",
		// event: "mouseover",
		cookie: {
			expires: 7, path: '/moderation/'
		}
	});
	
	
});


</script>
<div id="adminMenuContent"><?
	echo '<ul>';
	foreach($arAdminMenu as $arMenu){
		if(!empty($arParams['ACCESS'][$arMenu['ACCESS'][0]])){
			$arMenu['URL'] = '#adminMenu-'.$arMenu['CODE'];
			echo '<li><a href="'.$arMenu['URL'].'">'.$arMenu['NAME'].'</a></li>';
		}
	}
	echo '</ul>';

	foreach($arAdminMenu as $arMenu){
		if(!emptyArray($arMenu['SUB_MENU']) && !empty($arParams['ACCESS'][$arMenu['ACCESS'][0]])){
			echo '<div id="adminMenu-'.$arMenu['CODE'].'">';
				echo '<div class="adminMenu">';
					echo '<ul>';
					foreach($arMenu['SUB_MENU'] as $arSubMenu){
						echo '<li><a href="'.$arSubMenu['URL'].'" title="'.$arSubMenu['NAME'].'">'.$arSubMenu['NAME'].'</a></li>';
					}
					echo '</ul>';
				echo '</div>';

			echo '</div>';
		}
	}
	
?>
</div>
<?
	
}

	
	
}





	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>