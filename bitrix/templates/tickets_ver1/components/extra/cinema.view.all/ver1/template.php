<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>

Формат вывода кинотеатров:<br />
ВСЕ А Б В Г Д Е ..... Э Ю Я
<br />
Нажимаешь на все, выводятся все кинотераты в 2, или три столбика, если выбираешь букву, другие буквы затемряются, и эта становиться активной, и выводятся только кинотеатры этой буквы.
<br />
<br />
<?php

echo 'Кинотеатры: ';
echo '<br />';
if(!empty($arResult['ITEMS']) && count($arResult['ITEMS'])>0){
	foreach($arResult['ITEMS'] as $arItem){
		echo '';
		echo '<a href="/city/cinema/?CITY='.$arParams['CITY']['SHORT_NAME_ENG_LOWER'].'&CINEMA_ID='.$arItem['ID'].'" title="'.$arItem['NAME'].'">';
		echo $arItem['NAME'];
		echo '</a>';
		echo '';
		echo '<br />';
	}
}





	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>