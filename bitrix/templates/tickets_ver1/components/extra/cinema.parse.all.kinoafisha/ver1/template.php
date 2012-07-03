<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();



$retHtml = '';
if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У Вас нет прав для просмотра информации...');
}else{

	$retHtml .= '';

	
	if(isset($_GET['s'])){
		$ret = intval($_GET['s']);
		if($ret){
			$title = 'Изменения внесли в базу';
			$retHtml .= returnInfoMessage($title);
		}
		else{
			$title = 'Ошибка при внесении изменений';
			$retHtml .= returnErrorMessage($title);
		}
	}
	
	if(!emptyArray($arResult['ERRORS'])){
		$srtOut = '';
		foreach($arResult['ERRORS'] as $err){
			$srtOut .= $err.'<br />';
		}
		$retHtml .= returnErrorMessage($srtOut);
	}
	
	
	$retHtml .= '<form action="" method="post" >';
	$retHtml .= '<table class="modT">';
	$retHtml .= '<tr class="modT-body">';
		$retHtml .= '<td>';
		(!emptyString($arParams['CITY']['KINOAFISHA_URL'])) ? $PARSE_URL_VALUE = $arParams['CITY']['KINOAFISHA_URL'] : $PARSE_URL_VALUE = '';
		$retHtml .= '<input type="text" style="width: 300px;" value="'.$PARSE_URL_VALUE.'" name="PARSE_URL" />';
		$retHtml .= '</td>';
	$retHtml .= '</tr>';

	$retHtml .= '<tr class="modT-body">';
		$retHtml .= '<td>';
		$retHtml .= '<input type="submit" value="Парсить" name="submitStep1" />';
		$retHtml .= '</td>';
	$retHtml .= '</tr>';

	$retHtml .= '</table>';
	$retHtml .= '</form>';
	$retHtml .= '';
	$retHtml .= '';
	$retHtml .= '';

	
	if(!emptyArray($arResult['PARSE']['~ARRAY'])){
		$retHtml .= '<form action="" method="post" >';
		$retHtml .= '<table class="modT">';
		$retHtml .= '<tr class="modT-head">';
			$retHtml .= '<td>';
				$retHtml .= 'Название';
			$retHtml .= '</td>';
			$retHtml .= '<td>';
				$retHtml .= 'ID';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';
		foreach($arResult['PARSE']['~ARRAY'] as $arTempData){
			$retHtml .= '<tr class="modT-body">';
				$retHtml .= '<td>';
					$retHtml .= '<input type="text" style="width: 300px;" value="'.$arTempData['KINOAFISHA_NAME'].'" name="CINEMA_NAME['.$arTempData['KINOAFISHA_ID'].']" />';
				$retHtml .= '</td>';
				$retHtml .= '<td>';
				$retHtml .= $arTempData['KINOAFISHA_ID'];
				$retHtml .= '</td>';
			$retHtml .= '</tr>';
		}
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td colspan="2">';
				$retHtml .= '<input type="submit" value="Добавить на сайт" name="submitStep2" />';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '</table>';
		$retHtml .= '</form>';
	}
	
	
	
	
	echo $retHtml;
	
	

}
	






	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>