<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<script>
<?php
if(empty($arResult['FORM']['ID']) || $arResult['FORM']['ACCOUNT_TYPE'] == 'Y'){
	echo 'var flag = 1;';
}else{
	echo 'var flag = 0;';
}
?>

$(function() {
	$("input:submit").button();
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
/*
	$( "#ACCOUNT_NUMBER" ).tooltip();  // Не работает.
	$("input:text").css('width','400px');
	$("textarea").css('width','400px');
	$("textarea").css('height','100px');
	$("textarea").css('padding','5px');
*/
	
});

</script>

<?php
if(!empty($arResult['ERRORS']) && count($arResult['ERRORS'])>0){
?>
<div class="ui-widget" style="margin-top:20px;">
<div class="ui-state-error ui-corner-all" style="padding: 1.3em .7em;width:400px"> 
	<p>
		<table><tr>
		<td nowrap="nowrap" style="width:70px"><span class="ui-icon ui-icon-alert" style="float:left;margin-right: .3em;"></span><strong>Alert:</strong></td> 
		<td><?php
		$first = true;
		foreach($arResult['ERRORS'] as $tempError){
			if($first)
				$first = false;
			else{
				echo '<br /><br />';			
			}
			echo $tempError;
		}
		?></td> 
		</tr></table>
	 </p>
</div>
</div>
<?
}

if($arParams['ACCESS']['VIEW_GROUPS']){
?>
	
<form method="get" action="<?=$APPLICATION->GetCurPageParam('',array("CUSTOMER", "CUSTOMER_MANAGER"))?>" name="ACCOUNT_SUBMIT_FORM" id="ACCOUNT_SUBMIT_FORM">	
<table class="data-table">
<tr>
	<td style="vertical-align:top"><div>Тип:</div><select name="ACCOUNT_TYPE" style="width:80px;height:30px;vertical-align:middle"><option value="0" >Выбрать:</option><!--<option value="-1" >Значение не определено</option>--><?php
		if(!empty($arResult['ACCOUNT_TYPE_TITLE']) && count($arResult['ACCOUNT_TYPE_TITLE'])>0){
			foreach($arResult['ACCOUNT_TYPE_TITLE'] as $arShipmentDescription){
				$arShipmentDescription['ID'] == $arResult['FORM']['ACCOUNT_TYPE'] ? $selected = ' selected="selected"' : $selected = '';
				echo '<option value="'.$arShipmentDescription['ID'].'"'.$selected.'>'.$arShipmentDescription['NAME'].'</option>';
			}	
		}	
	?></select></td>
	<td style="vertical-align:middle"><div>Номер:</div><input style="width:34px;" maxlength="4" type="text" id="ACCOUNT_NUMBER" name="ACCOUNT_NUMBER" value="<?=$arResult['FORM']['ACCOUNT_NUMBER']?>" title="Укажите номер счета" /></td>
	<td style="vertical-align:top"><div>Тип:</div><select name="VP" style="width:45px;height:30px;vertical-align:middle"><option value="0" >Выбрать:</option><?php
		if(!empty($arResult['ACCOUNT_CODE_TITLE']) && count($arResult['ACCOUNT_CODE_TITLE'])>0){
			foreach($arResult['ACCOUNT_CODE_TITLE'] as $arStatusDescription){
				$arStatusDescription['ID'] == $arResult['FORM']['VP'] ? $selected = ' selected="selected"' : $selected = '';
				echo '<option value="'.$arStatusDescription['ID'].'"'.$selected.'>'.$arStatusDescription['NAME'].' - '.$arStatusDescription['DESCRIPTION'].'</option>';
			}	
		}	
	?></select></td>
	<td style="vertical-align:middle">
		<div>Дата выставления:</div>
		<input style="width:65px;" type="text" name="ACCOUNT_DATE" id="ACCOUNT_DATE" maxlength="10" value="<?=$arResult['FORM']['ACCOUNT_DATE']?>" class="datepicker"  title="Укажите дату счета" />
		по 
		<input style="width:65px;" type="text" name="ACCOUNT_DATE_TO" id="ACCOUNT_DATE_TO" maxlength="10" value="<?=$arResult['FORM']['ACCOUNT_DATE_TO']?>" class="datepicker"  title="Укажите дату счета" />
	</td>
	<td style="vertical-align:middle"><div>Заказчик:</div><input style="width:85px;" type="text" name="CUSTOMER" value="<?=htmlspecialchars($arResult['FORM']['CUSTOMER'])?>"   title="Укажите заказчика счета" /></td>
	<td style="vertical-align:top"><div>Статус:</div><select name="STATUS_DESCRIPTION" style="width:80px;height:30px;vertical-align:middle"><option value="0" >Выбрать:</option><option value="-1" >Значение не определено</option><?php
		if(!empty($arResult['ACCOUNT_STATUS_TITLE']) && count($arResult['ACCOUNT_STATUS_TITLE'])>0){
			foreach($arResult['ACCOUNT_STATUS_TITLE'] as $arStatusDescription){
				$arStatusDescription['ID'] == $arResult['FORM']['STATUS_DESCRIPTION'] ? $selected = ' selected="selected"' : $selected = '';
				echo '<option value="'.$arStatusDescription['ID'].'"'.$selected.'>'.$arStatusDescription['NAME'].'</option>';
			}	
		}	
	?></select></td>
	<td style="vertical-align:top"><div>Оплата:</div><select name="PAYMENT_DESCRIPTION" style="width:80px;height:30px;vertical-align:middle"><option value="0" >Выбрать:</option><!--<option value="-1" >Значение не определено</option>--><?php
		if(!empty($arResult['ACCOUNT_PAYMENT_DESCRIPTION_TITLE']) && count($arResult['ACCOUNT_PAYMENT_DESCRIPTION_TITLE'])>0){
			foreach($arResult['ACCOUNT_PAYMENT_DESCRIPTION_TITLE'] as $arPaymentDescription){
				$arPaymentDescription['ID'] == $arResult['FORM']['PAYMENT_DESCRIPTION'] ? $selected = ' selected="selected"' : $selected = '';
				echo '<option value="'.$arPaymentDescription['ID'].'"'.$selected.'>'.$arPaymentDescription['NAME'].'</option>';
			}	
		}	
	?></select></td>
	<td style="vertical-align:top"><div>Реализация:</div><select name="SHIPMENT_DESCRIPTION" style="width:80px;height:30px;vertical-align:middle"><option value="0" >Выбрать:</option><!--<option value="-1" >Значение не определено</option>--><?php
		if(!empty($arResult['ACCOUNT_SHIPMENT_DESCRIPTION_TITLE']) && count($arResult['ACCOUNT_SHIPMENT_DESCRIPTION_TITLE'])>0){
			foreach($arResult['ACCOUNT_SHIPMENT_DESCRIPTION_TITLE'] as $arShipmentDescription){
				$arShipmentDescription['ID'] == $arResult['FORM']['SHIPMENT_DESCRIPTION'] ? $selected = ' selected="selected"' : $selected = '';
				echo '<option value="'.$arShipmentDescription['ID'].'"'.$selected.'>'.$arShipmentDescription['NAME'].'</option>';
			}	
		}	
	?></select></td>
	<td style="vertical-align:top"><div>Кто создал:</div><select name="CREATED_BY" style="width:80px;height:30px;vertical-align:middle"><option value="0" >Выбрать:</option><!--<option value="-1" >Значение не определено</option>--><?php
		if(!empty($arResult['USERS_FULL']) && count($arResult['USERS_FULL'])>0){
			foreach($arResult['USERS_FULL'] as $arUser){
				$arUser['ID'] == $arResult['FORM']['CREATED_BY'] ? $selected = ' selected="selected"' : $selected = '';
				echo '<option value="'.$arUser['ID'].'"'.$selected.'>'.$arUser['FULL_NAME'].'</option>';
			}	
		}	
	?></select></td>
	<td>
		<div>&nbsp;</div>
		<input type="submit" name="send" value="Фильтровать" />
		&nbsp;
		<input type="button" onClick="location.href='./'" value="Сбросить" />
	</td>
</tr>
</table>


</form>
	<?php
}
else{
	ShowErrorMessage('У вас нет прав для просмотра данных!');
}


	
if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>