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
				$retHtml .= '<input type="button" class="MOVIE_EDIT_BACK_TO_ALL" value="Назад к списку" />';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td></td>';
			( !emptyString($arResult['ITEM']['ACTIVE']) && $arResult['ITEM']['ACTIVE'] == "N" ) ? $checked = '' : $checked = 'checked="checked"';
			$retHtml .= '<td style="width:500px;"><input id="ACTIVE" type="checkbox" name="ACTIVE" '.$checked.' /><label for="ACTIVE">Активность</label></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Название:</td>';
			$retHtml .= '<td><input type="text" class="text" name="NAME" id="NAME" value="'.$arResult['ITEM']['NAME'].'" /><image id="name_link" title="Генерация кода из названия" class="linked" src="/bitrix/themes/.default/icons/iblock/link.gif" onclick="set_linked()" /></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Символьный код:</td>';
			$retHtml .= '<td><input type="text" class="text" name="CODE" id="CODE" value="'.$arResult['ITEM']['CODE'].'" /><image id="code_link" title="Генерация кода из названия" class="linked" src="/bitrix/themes/.default/icons/iblock/link.gif" onclick="set_linked()" /></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Название на английском:</td>';
			$retHtml .= '<td><input type="text" class="text" name="ENG_NAME" value="'.$arResult['ITEM']['ENG_NAME'].'" /></td>';
		$retHtml .= '</tr>';
		
		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Изображение:</td>';
			$retHtml .= '<td>';
				$retHtml .= '<input type="file" name="PREVIEW_PICTURE" id="PREVIEW_PICTURE_MOVIE">';
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
			$retHtml .= '<td>Год выхода:</td>';
			$retHtml .= '<td><input type="text" class="text" name="YEAR" value="'.$arResult['ITEM']['YEAR'].'" /></td>';
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
			$retHtml .= '<td>Страна выхода:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['COUNTRY'])){
					$retHtml .= '<select name="COUNTRY_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['COUNTRY'] as $arMetro){
						if(!emptyArray($arResult['ITEM']['COUNTRY_ID']) && in_array($arMetro['ID'], $arResult['ITEM']['COUNTRY_ID'])){
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
			$retHtml .= '<td>Жанр фильма:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['GENRE'])){
					$retHtml .= '<select name="GENRE_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['GENRE'] as $arMetro){
						if(!emptyArray($arResult['ITEM']['GENRE_ID']) && in_array($arMetro['ID'], $arResult['ITEM']['GENRE_ID'])){
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
			$retHtml .= '<td>Длительность фильма:</td>';
			$retHtml .= '<td><input type="text" class="text" name="DURATION_TIME" value="'.$arResult['ITEM']['DURATION_TIME'].'" /></td>';
		$retHtml .= '</tr>';

		$retHtml .= '<tr class="modT-body">';
			$retHtml .= '<td>Дистрибьютер:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['DISTRIBUTOR'])){
					$retHtml .= '<select name="DISTRIBUTOR_ID" style="width:300px">';
					foreach($arResult['DISTRIBUTOR'] as $arCinemaNetwork){
						if(!empty($arResult['ITEM']['DISTRIBUTOR_ID']) && ($arCinemaNetwork['ID'] == $arResult['ITEM']['DISTRIBUTOR_ID'])){
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
			$retHtml .= '<td>Режиссер:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['PERSONS'])){
					$retHtml .= '<select name="DIRECTOR_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['PERSONS'] as $arMetro){
						if(!emptyArray($arResult['ITEM']['DIRECTOR_ID']) && in_array($arMetro['ID'], $arResult['ITEM']['DIRECTOR_ID'])){
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
			$retHtml .= '<td>Актеры:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['PERSONS'])){
					$retHtml .= '<select name="ACTORS_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['PERSONS'] as $arMetro){
						if(!emptyArray($arResult['ITEM']['ACTORS_ID']) && in_array($arMetro['ID'], $arResult['ITEM']['ACTORS_ID'])){
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
			$retHtml .= '<td>Дублеры:</td>';
			$retHtml .= '<td>';
				if(!emptyArray($arResult['PERSONS'])){
					$retHtml .= '<select name="DOUBLE_ID[]" multiple="multiple" size="10" style="width:300px">';
					foreach($arResult['PERSONS'] as $arMetro){
						if(!emptyArray($arResult['ITEM']['DOUBLE_ID']) && in_array($arMetro['ID'], $arResult['ITEM']['DOUBLE_ID'])){
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
				$retHtml .= '<input type="button" class="MOVIE_EDIT_BACK_TO_ALL" value="Назад к списку" />';
			$retHtml .= '</td>';
		$retHtml .= '</tr>';

		$retHtml .= '</table>';

		$retHtml .= '<input type="hidden" name="ID" value="'.$arResult['ITEM']['ID'].'" />';
		$retHtml .= '<input type="hidden" name="linked_state" id="linked_state" value="Y">';

		$retHtml .= '</form>';
	

	
	}

	
	
	

}
echo $retHtml;

?>
<script type="text/javascript" src="/bitrix/js/main/core/core_translit.js?1341326831"></script>
<script type="text/javascript">BX.message({'BING_KEY':'','TRANS_FROM':'а,б,в,г,д,е,ё,ж,з,и,й,к,л,м,н,о,п,р,с,т,у,ф,х,ц,ч,ш,щ,ъ,ы,ь,э,ю,я,А,Б,В,Г,Д,Е,Ё,Ж,З,И,Й,К,Л,М,Н,О,П,Р,С,Т,У,Ф,Х,Ц,Ч,Ш,Щ,Ъ,Ы,Ь,Э,Ю,Я','TRANS_TO':'a,b,v,g,d,e,ye,zh,z,i,y,k,l,m,n,o,p,r,s,t,u,f,kh,ts,ch,sh,shch,,y,,e,yu,ya,A,B,V,G,D,E,YE,ZH,Z,I,Y,K,L,M,N,O,P,R,S,T,U,F,KH,TS,CH,SH,SHCH,,Y,,E,YU,YA'})</script>
<script type="text/javascript">
var linked=true;
function set_linked()
{
	linked=!linked;

	var name_link = document.getElementById('name_link');
	if(name_link)
	{
		if(linked)
			name_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
		else
			name_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
	}
	var code_link = document.getElementById('code_link');
	if(code_link)
	{
		if(linked)
			code_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
		else
			code_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
	}
	var linked_state = document.getElementById('linked_state');
	if(linked_state)
	{
		if(linked)
			linked_state.value='Y';
		else
			linked_state.value='N';
	}
}
var oldValue = '';
function transliterate()
{
	if(linked)
	{
		var from = document.getElementById('NAME');
		var to = document.getElementById('CODE');
		if(from && to && oldValue != from.value)
		{
			BX.translit(from.value, {
				'max_len' : 100,
				'change_case' : 'L',
				'replace_space' : '-',
				'replace_other' : '-',
				'delete_repeat_replace' : true,
				'use_google' : false,
				'callback' : function(result){to.value = result; setTimeout('transliterate()', 250);}
			});
			oldValue = from.value;
		}
		else
		{
			setTimeout('transliterate()', 250);
		}
	}
	else
	{
		setTimeout('transliterate()', 250);
	}
}
transliterate();
		</script>
<?php

	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($GLOBALS['CITY']),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';

if($USER->IsAdmin()){
	// echo '<pre>',print_r($arParams),'</pre>';
	echo '<pre>',print_r($arResult),'</pre>';
}

?>