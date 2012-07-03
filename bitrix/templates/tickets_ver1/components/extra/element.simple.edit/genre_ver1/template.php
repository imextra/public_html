<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if($arParams['ACCESS']['VIEW_GROUPS']){

	$retHtml = '';
	$retHtml .= '<form action="" method="post">';
	$arResult['COLSPAN'] = 4;
	$retHtml .= '<table class="modT">';
	$retHtml .= '<tr class="modT-head">';
		$retHtml .= '<td>#</td>';
		$retHtml .= '<td>Название</td>';
		$retHtml .= '<td>Описание</td>';
		$retHtml .= '<td></td>';
	$retHtml .= '</tr>';
	
	$arTempFields = array(
		'Y'=>array('ID'=>'Y', 'NAME'=>'Активные', 'VAR'=>'DELETE', 'CHECK_TITLE'=>'скрыть'),
		'N'=>array('ID'=>'N', 'NAME'=>'Скрытые', 'VAR'=>'ACTIVE', 'CHECK_TITLE'=>'показать')
	);

	foreach($arTempFields as $arTeF){
		$retHtml .= '<tr class="modT-head" style="background-color: #EFEFEF"><td colspan="'.$arResult['COLSPAN'].'">';
			$retHtml .= $arTeF['NAME'];
		$retHtml .= '</td></tr>';
		if(!emptyArray($arResult['ITEMS']['SORTED'][$arTeF['ID']])){
			$i = 0;
			foreach($arResult['ITEMS']['SORTED'][$arTeF['ID']] as $arCountry){
				$i++;
				(!emptyString($arCountry['ACTIVE']) && $arCountry['ACTIVE'] == $arTeF['ID']) ? $arCountry['ACTIVE_CHECK'] = '' : $arCountry['ACTIVE_CHECK'] = ' checked="checked"';
				$retHtml .= '<tr class="modT-body">';
					$retHtml .= '<td><input type="hidden" name="ID['.$arCountry['ID'].']" value="'.$arCountry['ID'].'" />'.$i.'</td>';
					$retHtml .= '<td><input class="text" type="text" name="NAME['.$arCountry['ID'].']" value="'.$arCountry['NAME'].'" /></td>';
					$retHtml .= '<td><textarea class="text" name="PREVIEW_TEXT['.$arCountry['ID'].']">'.$arCountry['PREVIEW_TEXT'].'</textarea></td>';
					$retHtml .= '<td><input type="checkbox" name="'.$arTeF['VAR'].'['.$arCountry['ID'].']" id="ID_'.$arCountry['ID'].'" '.$arCountry['ACTIVE_CHECK'].' /> <label for="ID_'.$arCountry['ID'].'">'.$arTeF['CHECK_TITLE'].'</label></td>';
				$retHtml .= '</tr>';
			}
		}
		else{
			$retHtml .= '<tr class="modT-body"><td colspan="'.$arResult['COLSPAN'].'">';
				$retHtml .= 'Ничего не найдено...';
			$retHtml .= '</td></tr>';
		}
	}
	
	$retHtml .= '';
	$retHtml .= '';
	$retHtml .= '';
	$retHtml .= '';
	$retHtml .= '';
	$retHtml .= '<tr class="modT-bottom"><td colspan="'.$arResult['COLSPAN'].'">';
		if($arParams['ACCESS']['MODER_GROUPS']){
			$retHtml .= '<input type="submit" name="submitUpdateData" value="Сохранить" />';
		}
		else{
			$retHtml .= '<input type="button" value="Хрен вам!" />';
		}
	$retHtml .= '</td></tr>';
	$retHtml .= '</table>';
	$retHtml .= '</form>';

	echo $retHtml;

}

	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>