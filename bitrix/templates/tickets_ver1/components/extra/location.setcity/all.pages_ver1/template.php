<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

	if(!empty($arResult['CURRENT_CITY'])){
		echo 'Город: ';
		echo '';
		echo '<a href="/setcity/">';
			echo $arResult['ITEMS'][$arResult['CURRENT_CITY']]['NAME'];
		echo '</a>';
		if(!empty($arResult['USER_CITY']) && $arResult['USER_CITY'] != $arResult['CURRENT_CITY']){
			echo ' ';
			echo 'Вы просматриваете не свой город... ';
			echo 'Ваш город: ';
			echo '<a href="'.$APPLICATION->GetCurPageParam("CITY=".$arResult['USER_CITY'], array('CITY')).'">';
				echo $arResult['ITEMS'][$arResult['USER_CITY']]['NAME'];
			echo '</a>';
		}
	}elseif(!empty($arResult['USER_CITY'])){
		echo 'Город: ';
		echo '';
		echo '<a href="/setcity/">';
			echo $arResult['ITEMS'][$arResult['USER_CITY']]['NAME'];
		echo '</a>';
	}
	else{
		echo '<a href="/setcity/">';
			echo $arResult['ITEMS'][$GLOBALS['DEFAULT_CITY']]['NAME'];
		echo '</a>';
	}
	
	$forall = false;
	if($forall || $USER->IsAdmin()){
		echo ' [';
		echo '<a href="'.$APPLICATION->GetCurPageParam("ACT=clearcity", array("ACT",'REDIRECT','BACK_URL')).'">Сбросить город...</a>';
		echo ']';
	}
	
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
if($USER->IsAdmin()){
//	echo '<pre>',print_r($arParams),'</pre>';
//	echo '<pre>',print_r($arResult),'</pre>';
//	echo '<pre>',print_r($_GET),'</pre>';
}
?>