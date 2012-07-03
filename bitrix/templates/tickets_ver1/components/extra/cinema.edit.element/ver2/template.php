<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();


// echo '<pre>
// 0. Когда добавляем новый элемент - необходимо добавить принудительно город!!! Иначе будет без города кинотеатр.
// 1. При загрузке файла предусмотреть, чтобы поьзователь не смог загрузить другие типы файлов кроме изображений. Ограничить разрешение, размер файла.
// 2. Проверки все работают, осталось исходя из всех данных сделать обновление базы данных. Предусмотреть загрузку изображения и правильное удаление изображения.
// А так же чтобы свойства, которые не выводим, они не удалялись при обновлении.
// </pre>';

$retHtml = '';
if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У Вас нет прав для просмотра информации...');
}else{

	$retHtml .= '';
	$retHtml .= '';
	$retHtml .= '';
	
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

	if(!empty($_GET['s'])){
		showInfoMessage('Информация успешно сохранена!');
	}	
	
	if(!emptyArray($arResult['ITEM'])){
		$retHtml .= '<form action="" method="post" enctype="multipart/form-data">';
		$retHtml .= '<table class="modT">';

		$retHtml .= '<tr class="modT-bottom">';
			$retHtml .= '<td style="border-right:0px;">';
				if($arParams['ACCESS']['UPDATE_DATA']){
					$retHtml .= '<input type="submit" name="submitUpdateData" value="Сохранить" />';
				}
				else{
					$retHtml .= '<input type="button" value="Хрен вам, не сохраните!" />';
				}
			$retHtml .= '</td>';
			$retHtml .= '<td style="text-align:right; border-left:0px;">';
				$retHtml .= '<input type="button" class="CINEMA_EDIT_BACK_TO_ALL" value="Назад к списку" />';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td></td>';
			( !emptyString($arResult['ITEM']['ACTIVE']) && $arResult['ITEM']['ACTIVE'] == "N" ) ? $checked = '' : $checked = 'checked="checked"';
			$retHtml .= '<td style="width:500px;"><input id="ACTIVE" type="checkbox" name="ACTIVE" '.$checked.' /><label for="ACTIVE">Активность</label></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Название:</td>';
			$retHtml .= '<td><input type="text" class="text" name="NAME" value="'.$arResult['ITEM']['NAME'].'" /></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Название на английском:</td>';
			$retHtml .= '<td><input type="text" class="text" name="NAME_ENG" value="'.$arResult['ITEM']['NAME_ENG'].'" /></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Изображение:</td>';
			$retHtml .= '<td>';
				$retHtml .= '<input type="file" name="PREVIEW_PICTURE" id="PREVIEW_PICTURE_CINEMA">';
				$retHtml .= '<br />';
				if(!emptyArray($arResult['ITEM']['PREVIEW_PICTURE'])){
					$retHtml .= '<div style="margin:5px 0">';
						(!emptyString($arResult['ITEM']['PREVIEW_PICTURE_DEL']) && $arResult['ITEM']['PREVIEW_PICTURE_DEL'] == 'Y') ? $checked = 'checked="checked"' : $checked = '';
						$retHtml .= '<input type="checkbox" '.$checked.' name="PREVIEW_PICTURE_DEL" id="PREVIEW_PICTURE_DEL" /> <label for="PREVIEW_PICTURE_DEL">удалить</label>';
						$retHtml .= '<br />';
						$retHtml .= '<br />';
						$retHtml .= 'Размеры: '.$arResult['ITEM']['PREVIEW_PICTURE']['WIDTH'].'x'.$arResult['ITEM']['PREVIEW_PICTURE']['HEIGHT'].'';
						$retHtml .= '<br />';
						$retHtml .= '<img src="'.$arResult['ITEM']['PREVIEW_PICTURE']['SRC'].'" width="200" />';
					$retHtml .= '</div>';
				}

			$retHtml .= '</td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Адрес:</td>';
			$retHtml .= '<td><input type="text" class="text" name="ADDRESS" value="'.$arResult['ITEM']['ADDRESS'].'" /></td>';
		$retHtml .= '</tr>';
	
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Телефон:</td>';
			$retHtml .= '<td><input type="text" class="text" name="PHONE" value="'.$arResult['ITEM']['PHONE'].'" /></td>';
		$retHtml .= '</tr>';
	
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Ссылка на URL кинотеатра:</td>';
			$retHtml .= '<td><input type="text" class="text" name="SITE" value="'.$arResult['ITEM']['SITE'].'" /></td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Описание:</td>';
			$retHtml .= '<td>';
				echo $retHtml;
				
				$LHE = new CLightHTMLEditor;
				$LHE->Show(array(
					'id' => "preview_text",
					'content' => $arResult['ITEM']['PREVIEW_TEXT'],
					'inputName' => "PREVIEW_TEXT",
					'inputId' => "PREVIEW_TEXT",
					'width' => "100%",
					'height' => "300px",
					'bUseFileDialogs' => "N",
					'bFloatingToolbar' => "N",
					'bArisingToolbar' => "N",
					'jsObjName' => "",
					'toolbarConfig' => array(
						'Bold', 'Italic', 'Underline', 'RemoveFormat',
						// 'CreateLink', 'DeleteLink','Image', 'Video','BackColor','ForeColor','JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull','InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent','StyleList', 'HeaderList','FontList', 'FontSizeList',
					),
				   'videoSettings' => "N"
				));
					


			$retHtml = '';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Информация о скидках:</td>';
			$retHtml .= '<td>';
				echo $retHtml;
				
				$LHE = new CLightHTMLEditor;
				$LHE->Show(array(
					'id' => "detail_text",
					'content' => $arResult['ITEM']['DETAIL_TEXT'],
					'inputName' => "DETAIL_TEXT",
					'inputId' => "DETAIL_TEXT",
					'width' => "100%",
					'height' => "300px",
					'bUseFileDialogs' => "N",
					'bFloatingToolbar' => "N",
					'bArisingToolbar' => "N",
					'jsObjName' => "",
					'toolbarConfig' => array(
						'Bold', 'Italic', 'Underline', 'RemoveFormat',
						// 'CreateLink', 'DeleteLink','Image', 'Video','BackColor','ForeColor','JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull','InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent','StyleList', 'HeaderList','FontList', 'FontSizeList',
					),
				   'videoSettings' => "N"
				));
					


			$retHtml = '';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Сеть Кинотеатров:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['CINEMA_NETWORK'])){
					$retHtml .= '<select name="CINEMA_NETWORK_ID" style="width:300px">';
					foreach($arResult['CINEMA_NETWORK'] as $arCinemaNetwork){
						if(!empty($arResult['ITEM']['CINEMA_NETWORK_ID']) && ($arCinemaNetwork['ID'] == $arResult['ITEM']['CINEMA_NETWORK_ID'])){
							$selected = ' selected="selected"';
						}else{
							$selected = '';
						}
						$retHtml .= '<option '.$selected.' value="'.$arCinemaNetwork['ID'].'">';
							$retHtml .= $arCinemaNetwork['NAME'];
						$retHtml .= '</option>';
					}
					$retHtml .= '</select>';
				}
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Метро:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['METRO'])){
					$retHtml .= '<select name="METRO_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['METRO'] as $arMetro){
						if(!emptyArray($arResult['ITEM']['METRO_ID']) && in_array($arMetro['ID'], $arResult['ITEM']['METRO_ID'])){
							$selected = ' selected="selected"';
						}else{
							$selected = '';
						}
						$retHtml .= '<option '.$selected.' value="'.$arMetro['ID'].'">';
							$retHtml .= $arMetro['NAME'];
						$retHtml .= '</option>';
					}
					$retHtml .= '</select>';
				}
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Район:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['BOROUGH'])){
					$retHtml .= '<select name="BOROUGH_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['BOROUGH'] as $arMetro){
						if(!emptyArray($arResult['ITEM']['BOROUGH_ID']) && in_array($arMetro['ID'], $arResult['ITEM']['BOROUGH_ID'])){
							$selected = ' selected="selected"';
						}else{
							$selected = '';
						}
						$retHtml .= '<option '.$selected.' value="'.$arMetro['ID'].'">';
							$retHtml .= $arMetro['NAME'];
						$retHtml .= '</option>';
					}
					$retHtml .= '</select>';
				}
			$retHtml .= '</td>';
		$retHtml .= '</tr>';


		// $retHtml .= '<tr class="modT-body">';
			// $retHtml .= '<td>Город:</td>';
			// $retHtml .= '<td>';
				// if(!emptyArray($GLOBALS['CITY'])){
					// $retHtml .= '<select name="CITY" style="width:300px">';
					// foreach($GLOBALS['CITY'] as $arMetro){
						// if($arMetro['ID'] == $arResult['ITEM']['CITY']){
							// $selected = ' selected="selected"';
						// }else{
							// $selected = '';
						// }
						// $retHtml .= '<option '.$selected.' value="'.$arMetro['ID'].'">';
							// $retHtml .= $arMetro['NAME'];
						// $retHtml .= '</option>';
					// }
					// $retHtml .= '</select>';
				// }
			// $retHtml .= '</td>';
		// $retHtml .= '</tr>';
	
		$retHtml .= '<tr class="modT-head">';
			$retHtml .= '<td colspan="2"><b>Граббинг:</b></td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>ID на сайте <br/><a alt="www.kinoafisha.info" href="http://www.kinoafisha.info">www.kinoafisha.info</a>:</td>';
			$retHtml .= '<td><input type="text" class="text" name="KINOAFISHA_ID" value="'.$arResult['ITEM']['KINOAFISHA_ID'].'" /></td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Название кинотеатра на сайте <br/><a alt="www.kinoafisha.info" href="http://www.kinoafisha.info">www.kinoafisha.info</a>:</td>';
			$retHtml .= '<td><input type="text" class="text" name="KINOAFISHA_NAME" value="'.$arResult['ITEM']['KINOAFISHA_NAME'].'" /></td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-bottom">';
			$retHtml .= '<td style="border-right:0px;">';
				if($arParams['ACCESS']['UPDATE_DATA']){
					$retHtml .= '<input type="submit" name="submitUpdateData" value="Сохранить" />';
				}
				else{
					$retHtml .= '<input type="button" value="Хрен вам, не сохраните!" />';
				}
			$retHtml .= '</td>';
			$retHtml .= '<td style="text-align:right; border-left:0px;">';
				$retHtml .= '<input type="button" class="CINEMA_EDIT_BACK_TO_ALL" value="Назад к списку" />';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '</table>';

		$retHtml .= '<input type="hidden" name="ID" value="'.$arResult['ITEM']['ID'].'" />';

		$retHtml .= '</form>';
	

	
	}

	
	
	

}
echo $retHtml;



	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($GLOBALS['CITY']),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>