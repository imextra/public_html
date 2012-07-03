<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();




$retHtml = '';
if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У Вас нет прав для просмотра информации...');
}else{
	if(!$arResult['ITEM']['ID']){
		ShowErrorMessage('Искомый элемент не найден...');
	}else{

		if($arParams['ACCESS']['MODER_GROUPS'] || $arParams['ACCESS']['ADMIN_GROUPS']){
			$retHtml .= '<div style="margin:10px 0">';
				$retHtml .= '<a href="#">редактировать информацию</a>';
			$retHtml .= '</div>';
		}
		$retHtml .= '<table>';
		$retHtml .= '<tr>';
			$retHtml .= '<td>';
				if(!emptyString($arResult['ITEM']['PREVIEW_PICTURE']['SRC'])){
					$retHtml .= '<div style="width:250px; max-height: 150px; overflow: hidden">';
						$retHtml .= '<img src="'.$arResult['ITEM']['PREVIEW_PICTURE']['SRC'].'" style="width:250px;" />';
					$retHtml .= '</div>';
				}
				else{
					$retHtml .= '<div style="width:100px; height:100px; background-color:#DDD"></div>';
				}
			$retHtml .= '</td>';
			$retHtml .= '<td style="padding:0 0 0 10px;">';
				$retHtml .= '<div style="font-size:1.3em;">'.$arResult['ITEM']['NAME'].'</div>';
				if(!emptyString($arResult['ITEM']['ADDRESS'])){
					$retHtml .= '<div style="">Адрес: '.$arResult['ITEM']['ADDRESS'].'</div>';
				}
				if(!emptyString($arResult['ITEM']['PHONE'])){
					$retHtml .= '<div style="">Телефоны: '.$arResult['ITEM']['PHONE'].'</div>';
				}
				if(!emptyString($arResult['ITEM']['SITE'])){
					$retHtml .= '<div style="">Сайт: '.returnUrlLink($arResult['ITEM']['SITE']).'</div>';
				}
					$retHtml .= '<div style="" class="notwork">Район города: Приморский и т.д.</div>';
					$retHtml .= '<div style="" class="notwork">Метро: Старая деревня</div>';
					$retHtml .= '<div style="" class="notwork">Город: '.$arResult['ITEM']['CITY'].'</div>';
					$retHtml .= '<div style="" class="notwork">Принадлежит к сети кинотеатров:</div>';

				$retHtml .= '';
				$retHtml .= '';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';
		$retHtml .= '</table>';
		$retHtml .= '';
		if(!emptyString($arResult['ITEM']['PREVIEW_TEXT'])){
			$retHtml .= '<div style="margin:10px 0;border:1px solid #CCC">';
				$retHtml .= '<b>Информация:</b>';
				$retHtml .= '<div style="margin:5px 0;">'.$arResult['ITEM']['PREVIEW_TEXT'].'</div>';
			$retHtml .= '</div>';
		}
		$retHtml .= '';
		$retHtml .= '';
		$retHtml .= '';

		echo $retHtml;
	
	}
}



// echo '';




	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>