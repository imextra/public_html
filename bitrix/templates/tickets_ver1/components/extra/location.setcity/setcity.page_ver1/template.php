<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!empty($arResult['SECTION']) && count($arResult['SECTION'])>0){
	foreach($arResult['SECTION'] as $arSection){
		echo '';
		echo '<b>';
		echo $arSection['NAME'];
		echo '</b>';
		echo '<br />';
		echo '';
		if(!empty($arSection['ITEMS']) && count($arSection['ITEMS'])>0){
			foreach($arSection['ITEMS'] as $arItem){
				echo '';
				echo '';
				echo '';
				echo '<a href="/setcity/?ACT=setcity&CITY='.$arItem['SHORT_NAME_ENG_LOWER'].'">';
					echo $arItem['NAME'];
				echo '</a>';
				echo '<br />';
				echo '';
			}
		}
		else{
			echo 'Города не найдены...:(';
		}
	}
}


	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
if($USER->IsAdmin()){
//	echo '<pre>',print_r($arParams),'</pre>';
//	echo '<pre>',print_r($arResult),'</pre>';
//	echo '<pre>',print_r($_GET),'</pre>';
}
?>