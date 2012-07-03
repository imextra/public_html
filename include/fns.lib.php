<?php
function rCopyRights($start){
	$arResult['PROJECT_START_DATE'] = intval($start);

	if(empty($arResult['PROJECT_START_DATE'])){
		$arResult['PROJECT_START_DATE'] = date('Y');
	}

	$arResult['CURRENT_DATE'] = date('Y');
	$arResult['COPY_RIGHTS'] = '&copy '.$arResult['PROJECT_START_DATE'];

	if($arResult['PROJECT_START_DATE'] != $arResult['CURRENT_DATE']){
		$arResult['COPY_RIGHTS'] .= ' - '.$arResult['CURRENT_DATE'];
	}

	return $arResult['COPY_RIGHTS'];
}


function ShowErrorMessage($errorMessage){
	echo returnErrorMessage($errorMessage);
}

function returnErrorMessage($errorMessage, $width = 540){
	$retHtml .= '<div class="ui-widget">';
	$retHtml .= '<div class="ui-state-error ui-corner-all" style="padding: 1.3em .7em;width:'.$width.'px;margin:20px auto; font-size:.8em;">';
	$retHtml .= '<p>';
		$retHtml .= '<table><tr>';
		$retHtml .= '<td nowrap="nowrap" style="width:70px"><span class="ui-icon ui-icon-alert" style="float:left;margin-right: .3em;"></span><strong>Alert:</strong></td>';
		$retHtml .= '<td>';
			$retHtml .= $errorMessage;
			$retHtml .= '<br />';
			$retHtml .= '<br />';
			$retHtml .= 'По всем вопросам ошибок пишите на <a href="mailto:abc@valcom.ru">abc@valcom.ru</a>';
		$retHtml .= '</td>';
		$retHtml .= '</tr></table>';
	$retHtml .= '</p>';
	$retHtml .= '</div>';
	$retHtml .= '</div>';	
	return $retHtml;
}

function ShowInfoMessage($infoMessage){
	echo returnInfoMessage($infoMessage);
}

function returnInfoMessage($infoMessage, $width = 540){
	$retHtml .= '<div class="ui-widget">';
	$retHtml .= '<div class="ui-state-highlight ui-corner-all" style="padding: 1.3em .7em;width:'.$width.'px;margin:20px auto; font-size:.8em;">';
	$retHtml .= '<p>';
		$retHtml .= '<table><tr>';
		$retHtml .= '<td nowrap="nowrap" style="width:70px"><span class="ui-icon ui-icon-info" style="float:left;margin-right: .3em;"></span><strong>Alert:</strong></td>';
		$retHtml .= '<td>';
			$retHtml .= $infoMessage;
		$retHtml .= '</td>';
		$retHtml .= '</tr></table>';
	$retHtml .= '</p>';
	$retHtml .= '</div>';
	$retHtml .= '</div>';	
	return $retHtml;
}

function returnUrlLink($url = '#', $title = ""){
	$retHtml .= '<a href="'.$url.'" title="'.$title.'">'.$url.'</a>';	
	return $retHtml;	
}

function showUrlLink($url = '#', $title = ""){
	echo returnUrlLink($url, $title);
}

function emptyString($str){
	if(!empty($str) && strlen($str)>0){
		return false;
	}else{
		return true;
	}
}

function emptyArray($arr){
	if(!empty($arr) && count($arr)>0){
		return false;
	}else{
		return true;
	}
}

function getArray($node) 
{ 
    $array = false; 

    if ($node->hasAttributes()) 
    { 
        foreach ($node->attributes as $attr) 
        { 
            $array[$attr->nodeName] = $attr->nodeValue; 
        } 
    } 

    if ($node->hasChildNodes()) 
    { 
        if ($node->childNodes->length == 1) 
        { 
            $array[$node->firstChild->nodeName] = $node->firstChild->nodeValue; 
        } 
        else 
        { 
            foreach ($node->childNodes as $childNode) 
            { 
                if ($childNode->nodeType != XML_TEXT_NODE) 
                { 
                    $array[$childNode->nodeName][] = $this->getArray($childNode); 
                } 
            } 
        } 
    } 

    return $array; 
} 

function dom_to_array($root) 
{ 
    $result = array(); 

    if ($root->hasAttributes()) 
    { 
        $attrs = $root->attributes; 

        foreach ($attrs as $i => $attr) 
            $result[$attr->name] = $attr->value; 
    } 

    $children = $root->childNodes; 

    if ($children->length == 1) 
    { 
        $child = $children->item(0); 

        if ($child->nodeType == XML_TEXT_NODE) 
        { 
            $result['_value'] = $child->nodeValue; 

            if (count($result) == 1) 
                return $result['_value']; 
            else 
                return $result; 
        } 
    } 

    $group = array(); 

    for($i = 0; $i < $children->length; $i++) 
    { 
        $child = $children->item($i); 

        if (!isset($result[$child->nodeName])) 
            $result[$child->nodeName] = dom_to_array($child); 
        else 
        { 
            if (!isset($group[$child->nodeName])) 
            { 
                $tmp = $result[$child->nodeName]; 
                $result[$child->nodeName] = array($tmp); 
                $group[$child->nodeName] = 1; 
            } 

            $result[$child->nodeName][] = dom_to_array($child); 
        } 
    } 

    return $result; 
} 

function detect_encoding($string) {  
  static $list = array('utf-8', 'windows-1251');
  
  foreach ($list as $item) {
    $sample = iconv($item, $item, $string);
    if (md5($sample) == md5($string))
      return $item;
  }
  return null;
}


function defineUserCity2ipgeobase($arCities){

	$ret['DATA'] = false;
	$ret['LOG'] = array();
	$ret['ERRORS'] = array();

	$ret['LOG'][] = 'Проверяем наличие списка городов...';
	if(emptyArray($arCities)){
		$ret['LOG'][] = 'Не нашли список городов...function defineUserCity2ipgeobase()';
		$ret['ERRORS'][] = 'Не нашли список городов... function defineUserCity2ipgeobase()';
		return $ret;
	}
	else{
		$ret['LOG'][] = 'Определяем город пользователя через сервис ipgeobase.ru';
		$ret['LOG'][] = 'Класс http://faniska.ru/php-kusochki/geotargeting-novyj-php-klass-dlya-raboty-s-bazoj-ipgeobase-ru.html';
		$ret['LOG'][] = 'Списки других классов http://ipgeobase.ru/cgi-bin/Software.cgi';

		$o = array(); // опции. необзятательно.
		$o['charset'] = 'utf-8'; // нужно указать требуемую кодировку, если она отличается от windows-1251
		// $o['ip'] = '127.0.0.1'; // можно указать ip пользователя, если его знаем.
		$geo = new Geo($o); // запускаем класс
		$userCity = $geo->get_value('city', false);  // получаем название города пользователя

		// $userCity = 'Санкт-Петербург';
		if(empty($userCity)){
			$ret['LOG'][] = 'Не смогли определить город пользователя function defineUserCity2ipgeobase()';
			$ret['ERRORS'][] = 'Не смогли определить город пользователя function defineUserCity2ipgeobase()';
			return $ret;
		}

		$ret['LOG'][] = 'Сравниваем его с текущими. При нахождении сразу обрываем цикл и возвращаем результат или false';
		foreach($arCities as $arCity){
			$ret['LOG'][] = 'Город: '.$arCity['NAME'];
			if($arCity['NAME'] == $userCity){
				$ret['LOG'][] = 'Подошел.';
				$ret['DATA'] = $arCity['SHORT_NAME_ENG_LOWER'];
				break;
			}
			else{
				$ret['LOG'][] = 'НЕ подошел.';
			}
		}
		if(!$ret['DATA']){
			$ret['LOG'][] = 'Ни один город не подошел пользователю. function defineUserCity2ipgeobase()';
			$ret['ERRORS'][] = 'Ни один город не подошел пользователю. function defineUserCity2ipgeobase()';
		}

		return $ret;
	}
}

?>