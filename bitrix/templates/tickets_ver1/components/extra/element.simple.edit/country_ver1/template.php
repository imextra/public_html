<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if($arParams['ACCESS']['VIEW_GROUPS']){

	$retHtml = '';
	$retHtml .= '<form action="" method="post">';
	$arResult['COLSPAN'] = 3;
	$retHtml .= '<table class="modT">';
	$retHtml .= '<tr class="modT-head">';
		$retHtml .= '<td>#</td>';
		$retHtml .= '<td>Название</td>';
		$retHtml .= '<td></td>';
	$retHtml .= '</tr>';
	$retHtml .= '<tr class="modT-head"><td colspan="'.$arResult['COLSPAN'].'">';
		$retHtml .= 'Активные';
	$retHtml .= '</td></tr>';
	if(!emptyArray($arResult['ITEMS']['SORTED']['Y'])){
		$i = 0;
		foreach($arResult['ITEMS']['SORTED']['Y'] as $arCountry){
			$i++;
			(!emptyString($arCountry['ACTIVE']) && $arCountry['ACTIVE'] == 'N') ? $arCountry['ACTIVE_CHECK'] = ' checked="checked"' : $arCountry['ACTIVE_CHECK'] = '';
			$retHtml .= '<tr class="modT-body">';
				$retHtml .= '<td><input type="hidden" name="ID['.$arCountry['ID'].']" value="'.$arCountry['ID'].'" />'.$i.'</td>';
				$retHtml .= '<td><input class="text" type="text" name="NAME['.$arCountry['ID'].']" value="'.$arCountry['NAME'].'" /></td>';
				$retHtml .= '<td><input type="checkbox" name="DELETE['.$arCountry['ID'].']" id="ID_'.$arCountry['ID'].'" '.$arCountry['ACTIVE_CHECK'].' /> <label for="ID_'.$arCountry['ID'].'">скрыть</label></td>';
			$retHtml .= '</tr>';
		}
	}
	else{
		$retHtml .= '<tr class="modT-body"><td colspan="'.$arResult['COLSPAN'].'">';
			$retHtml .= 'Ничего не найдено...';
		$retHtml .= '</td></tr>';
	}
	$retHtml .= '<tr class="modT-head"><td colspan="'.$arResult['COLSPAN'].'">';
		$retHtml .= 'Скрытые';
	$retHtml .= '</td></tr>';
	if(!emptyArray($arResult['ITEMS']['SORTED']['N'])){
		$i = 0;
		foreach($arResult['ITEMS']['SORTED']['N'] as $arCountry){
			$i++;
			(!emptyString($arCountry['ACTIVE']) && $arCountry['ACTIVE'] == 'Y') ? $arCountry['ACTIVE_CHECK'] = ' checked="checked"' : $arCountry['ACTIVE_CHECK'] = '';
			$retHtml .= '<tr class="modT-body">';
				$retHtml .= '<td><input type="hidden" name="ID['.$arCountry['ID'].']" value="'.$arCountry['ID'].'" />'.$i.'</td>';
				$retHtml .= '<td><input class="text" type="text" name="NAME['.$arCountry['ID'].']" value="'.$arCountry['NAME'].'" /></td>';
				$retHtml .= '<td><input type="checkbox" name="ACTIVE['.$arCountry['ID'].']" id="ID_'.$arCountry['ID'].'" '.$arCountry['ACTIVE_CHECK'].' /> <label for="ID_'.$arCountry['ID'].'">показать</label></td>';
			$retHtml .= '</tr>';
		}
	}
	else{
		$retHtml .= '<tr class="modT-body"><td colspan="'.$arResult['COLSPAN'].'">';
			$retHtml .= 'Ничего не найдено...';
		$retHtml .= '</td></tr>';
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