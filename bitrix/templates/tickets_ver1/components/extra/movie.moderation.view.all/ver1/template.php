<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<?php
$retHtml = '';
if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У Вас нет прав для просмотра информации...');
}else{


	// if(!emptyArray($GLOBALS['CITY'])){
		// $retHtml .= '<br />';
		// $retHtml .= '<br />';
		// $retHtml .= '<form action="/setcity/" method="get">';
		// $retHtml .= 'Выбрать город: ';
		// $retHtml .= '<select name="CITY" onchange="this.form.submit()">';
		// foreach($GLOBALS['CITY'] as $arCity){
			// $GLOBALS['USER_CITY'] == $arCity['SHORT_NAME_ENG_LOWER'] ? $selected = ' selected="selected"' : $selected = '';
			// $retHtml .= '<option '.$selected.' value="'.$arCity['SHORT_NAME_ENG_LOWER'].'">'.$arCity['NAME'].'</option>';
		// }
		// $retHtml .= '</select>';
		// $retHtml .= '<input type="hidden" value="setcity" name="ACT" />';
		// $retHtml .= '<input type="hidden" value="1" name="REDIRECT" />';
		// $retHtml .= '<input type="hidden" value="'.$APPLICATION->GetCurUri().'" name="BACK_URL" />';
		// $retHtml .= '</form>';
	// }

	
	$retHtml .= '';
	$retHtml .= '<br />';
	$retHtml .= 'Фильмы:';
	$retHtml .= '<br />';
	$retHtml .= '<br />';
	$retHtml .= '<a id="addCinemaButton" href="/moderation/movies/edit/">Добавить фильм</a>';

	if(!emptyArray($arResult['ITEMS'])){
		$retHtml .= '<table class="modT">';
		$retHtml .= '<tr class="modT-head">';
			$retHtml .= '<td width="20">';
				$retHtml .= '#';
			$retHtml .= '</td>';
			$retHtml .= '<td width="400">';
				$retHtml .= 'Название';
			$retHtml .= '</td>';
			$retHtml .= '<td>';
				$retHtml .= 'Год выпуска';
			$retHtml .= '</td>';
			$retHtml .= '<td colspan="2">';
				$retHtml .= 'Добавить';
			// $retHtml .= '</td>';
			// $retHtml .= '<td>';
				// $retHtml .= '';
			$retHtml .= '</td>';
			// $retHtml .= '<td>';
				// $retHtml .= '';
			// $retHtml .= '</td>';
		$retHtml .= '</tr>';

		$i = $arResult['NAV']["START_ELEMENT"]-1;
		foreach($arResult['ITEMS'] as $arItem){
			$i++;
			($arItem['ACTIVE']) ? $classTR = '' : $classTR = 'modT-attention';
			$retHtml .= '<tr class="modT-body '.$classTR.'">';
				$retHtml .= '<td style="text-align:center;">';
					$retHtml .= $i;				
				$retHtml .= '</td>';
				$retHtml .= '<td>';
					$retHtml .= '<a href="/moderation/movies/edit/?ID='.$arItem['ID'].'" title="'.$arItem['NAME'].'">';
						$retHtml .= $arItem['NAME'];
					$retHtml .= '</a>';
					$retHtml .= ' ';
					$retHtml .= '['.$arItem['ID'].']';
				$retHtml .= '</td>';
				$retHtml .= '<td align="center">';
					$retHtml .= ''.$arItem['YEAR'].'';
				$retHtml .= '</td>';
				$retHtml .= '<td>';
					$retHtml .= '<a href="#/moderation/movies/edit/?ID='.$arItem['ID'].'" title="'.$arItem['NAME'].'">';
					$retHtml .= 'постер';
					$retHtml .= '</a>';
				$retHtml .= '</td>';
				$retHtml .= '<td>';
					$retHtml .= '<a href="#/moderation/movies/edit/?ID='.$arItem['ID'].'" title="'.$arItem['NAME'].'">';
					$retHtml .= 'трейлер';
					$retHtml .= '</a>';
				$retHtml .= '</td>';
				// $retHtml .= '<td>';
				
				// $retHtml .= '</td>';
			$retHtml .= '</tr>';
		}
		$retHtml .= '</table>';
		if(!emptyString($arResult['NAV']["NAV_STRING"])){
			$retHtml .= $arResult['NAV']["NAV_STRING"];
		}

	}
	
	
	
/* 	if(!emptyArray($arResult['ITEMS_NOT_ACTIVE'])){
		$retHtml .= '<b>Не активные:</b>';
		$retHtml .= '<table class="modT">';
		$retHtml .= '<tr class="modT-head">';
			$retHtml .= '<td width="20">';
				$retHtml .= '#';
			$retHtml .= '</td>';
			$retHtml .= '<td width="400">';
				$retHtml .= 'Название';
			$retHtml .= '</td>';
			// $retHtml .= '<td>';
				// $retHtml .= '';
			// $retHtml .= '</td>';
		$retHtml .= '</tr>';

		$i = 0;
		foreach($arResult['ITEMS_NOT_ACTIVE'] as $arItem){
			$i++;
			$retHtml .= '<tr class="modT-body">';
				$retHtml .= '<td style="text-align:center;">';
					$retHtml .= $i;				
				$retHtml .= '</td>';
				$retHtml .= '<td>';
					$retHtml .= '<a href="/moderation/cinema/edit/?ID='.$arItem['ID'].'" title="'.$arItem['NAME'].'">';
						$retHtml .= $arItem['NAME'];
					$retHtml .= '</a>';
					$retHtml .= ' ';
					$retHtml .= '['.$arItem['ID'].']';
				$retHtml .= '</td>';
				// $retHtml .= '<td>';
				
				// $retHtml .= '</td>';
			$retHtml .= '</tr>';
		}
		$retHtml .= '</table>';

	}
 */	
	
	

}
echo $retHtml;







	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
	// echo '<pre>',print_r($GLOBALS),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>