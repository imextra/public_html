<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<script>
<?php
// if(empty($arResult['FORM']['ID']) || $arResult['FORM']['ACCOUNT_TYPE'] == 'Y'){
	// echo 'var flag = 1;';
// }else{
	// echo 'var flag = 0;';
// }
?>

$(function() {
/* 	$("input:submit").button();
	$("input:button").button();
	$(".btn").button();
	$( ".datepicker" ).datepicker({
		dateFormat: 'dd.mm.yy'
	});
	$("input:text").css('padding','5px');
	
	$("#ACCOUNT_DATE").change(function (){
		if(!$("#ACCOUNT_DATE_TO").val()){
			$("#ACCOUNT_DATE_TO").val($("#ACCOUNT_DATE").val());
		}
	});
 *//*
	$( "#ACCOUNT_NUMBER" ).tooltip();  // Не работает.
	$("input:text").css('width','400px');
	$("textarea").css('width','400px');
	$("textarea").css('height','100px');
	$("textarea").css('padding','5px');
*/
	
});

</script>

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
	
<form method="get" action="<?=$APPLICATION->GetCurPageParam('',array("CUSTOMER", "CUSTOMER_MANAGER"))?>" name="FILM_FILTER_SUBMIT_FORM" id="FILM_FILTER_SUBMIT_FORM">	
<table class="data-table">
<tr>
	<td style="vertical-align:middle"><div>Название фильма:</div><input style="width:234px;" type="text" id="FILM_NAME" name="FILM_NAME" value="<?=$arResult['FORM']['FILM_NAME']?>" title="Название фильма" /></td>
	<td style="vertical-align:middle">
		<div>Дата фильма:</div>
		<select name="FILM_YEAR" id="FILM_YEAR"><?php 
				for($i = 2000; $i++; $i<date('Y')){
					echo '<option value="'.$i.'">'.$i.'</option>';
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