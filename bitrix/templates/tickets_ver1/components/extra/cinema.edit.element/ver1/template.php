<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();



$retHtml = '';
if(!$arParams['ACCESS']['VIEW_GROUPS']){
	ShowErrorMessage('У Вас нет прав для просмотра информации...');
}else{

	$retHtml .= '';
	$retHtml .= '';
	$retHtml .= '';
	if(!emptyArray($arResult['ITEM'])){
		$retHtml .= '<form action="" method="post" enctype="multipart/form-data">';
		$retHtml .= '<table class="modT">';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Активность</td>';
			( !empty($arResult['ITEM']['ACTIVE']) && $arResult['ITEM']['ACTIVE'] == "Y" ) ? $checked = 'checked="checked"' : $checked = '';
			$retHtml .= '<td style="width:500px;"><input type="checkbox" name="ACTIVE" '.$checked.' /></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Название:</td>';
			$retHtml .= '<td><input type="text" class="text" name="NAME" value="'.$arResult['ITEM']['NAME'].'" /></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Изображение:</td>';
			$retHtml .= '<td>';
				$retHtml .= '<input type="file" name="PREVIEW_PICTURE">';
				$retHtml .= '<br />';
				if(!emptyArray($arResult['ITEM']['PREVIEW_PICTURE'])){
					$retHtml .= '<div style="margin:5px 0">';
						$retHtml .= '<input type="checkbox" name="PREVIEW_PICTURE_DEL" id="PREVIEW_PICTURE_DEL" /> <label for="PREVIEW_PICTURE_DEL">удалить</label>';
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
			$retHtml .= '<td>Сайт:</td>';
			$retHtml .= '<td><input type="text" class="text" name="SITE" value="'.$arResult['ITEM']['SITE'].'" /></td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Описание:</td>';
			$retHtml .= '<td>';
				echo $retHtml;
				
				$LHE = new CLightHTMLEditor;
				$LHE->Show(array(
					'id' => "detail_text",
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
						'CreateLink', 'DeleteLink', 'Image', 
						// 'Video',
						'BackColor', 'ForeColor',
						// 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
						// 'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent',
						// 'StyleList', 'HeaderList',
						// 'FontList', 'FontSizeList',
					),
				   'videoSettings' => "N"
				));
					


			$retHtml = '';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Метро:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['METRO'])){
					$retHtml .= '<select name="METRO_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['METRO'] as $arMetro){
						if(in_array($arMetro['ID'], $arResult['ITEM']['METRO_ID'])){
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
					$retHtml .= '<select name="BOTOUGH_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['BOROUGH'] as $arMetro){
						if(in_array($arMetro['ID'], $arResult['ITEM']['BOROUGH_ID'])){
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
			$retHtml .= '<td>Город:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($GLOBALS['CITY'])){
					$retHtml .= '<select name="CITY" style="width:300px">';
					foreach($GLOBALS['CITY'] as $arMetro){
						if($arMetro['ID'] == $arResult['ITEM']['CITY']){
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
		$retHtml .= '<tr class="modT-bottom"><td colspan="2">';
			if($arParams['ACCESS']['MODER_GROUPS']){
				$retHtml .= '<input type="submit" name="submitUpdateData" value="Сохранить" />';
			}
			else{
				$retHtml .= '<input type="button" value="Хрен вам!" />';
			}
		$retHtml .= '</td></tr>';
		$retHtml .= '</table>';

		$retHtml .= '<input type="hidden" name="ID" value="'.$arResult['ITEM']['ID'].'" />';

		$retHtml .= '</form>';
	

	
	}

	
	
	

}
echo $retHtml;



	// echo '<pre>',print_r($arParams),'</pre>';
	echo '<pre>',print_r($GLOBALS['CITY']),'</pre>';
	echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
}

?>