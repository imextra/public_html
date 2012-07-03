<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if($arParams['ACCESS']['VIEW_GROUPS']){

	$retHtml = '';
	$retHtml .= '<form action="" method="post">';
	$retHtml .= '<div>Добавить элементы:</div>';
	$retHtml .= '<textarea name="NAMES" style="margin:5px 0; width:500px; height:100px;"></textarea>';
	$retHtml .= '';
	$retHtml .= '<div style="margin: 10px 0;">';
		if($arParams['ACCESS']['MODER_GROUPS']){
			$retHtml .= '<input type="submit" name="submitAddData" value="Добавить" />';
		}
		else{
			$retHtml .= '<input type="button" value="Хрен вам!" />';
		}
	$retHtml .= '</div>';
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