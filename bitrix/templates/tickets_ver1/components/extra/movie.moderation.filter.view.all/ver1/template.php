<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<?php
if(!emptyArray($arResult['ERRORS'])){
	$arResult['ERRORS_HTML'] = '';
	foreach($arResult['ERRORS'] as $arTempError){
		$arResult['ERRORS_HTML'] .= $arTempError;
		$arResult['ERRORS_HTML'] .= '<br />';
	}
	if(!emptyString($arResult['ERRORS_HTML'])){
		ShowErrorMessage($arResult['ERRORS_HTML']);
	}
}

if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У Вас нет прав для просмотра информации...');
}else{
?>
	
<form method="get" action="<?=$APPLICATION->GetCurPageParam('',array("FILM_NAME", "FILM_YEAR"))?>" name="FILM_FILTER_SUBMIT_FORM" id="FILM_FILTER_SUBMIT_FORM">	
<table class="data-table">
<tr>
	<td style="vertical-align:middle"><div>Название фильма:</div><input style="width:234px;" type="text" id="FILM_NAME" name="FILM_NAME" value="<?=$arResult['FORM']['FILM_NAME']?>" title="Название фильма" /></td>
	<td style="vertical-align:middle">
		<div>Дата фильма:</div>
		<select name="FILM_YEAR" id="FILM_YEAR"><?php 
				echo '<option value="0">Выбрать</option>';
				for($i = 2000; $i<=date('Y'); $i++){
					(!empty($arResult['FORM']['FILM_YEAR']) && ($i == $arResult['FORM']['FILM_YEAR'])) ? $checked = 'selected="selected"' : $checked = '';
					echo '<option value="'.$i.'" '.$checked.'>'.$i.'</option>';
				}
		?></select>
	</td>
	<td>
		<div>&nbsp;</div>
		&nbsp;<input type="submit" name="send" value="Фильтровать" />
		&nbsp;
		<input type="button" onClick="location.href='./'" value="Сбросить" />
	</td>
</tr>
</table>


</form>
	<?php
}


	
if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>