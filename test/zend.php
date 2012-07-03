<?php
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

function detect_encoding($string) {  
  static $list = array('utf-8', 'windows-1251');
  
  foreach ($list as $item) {
    $sample = iconv($item, $item, $string);
    if (md5($sample) == md5($string))
      return $item;
  }
  return null;
}


$html = file_get_contents('http://kinoafisha.info/');

$textCharset = detect_encoding($html);
echo $textCharset;
if($textCharset != 'utf-8'){
	// $str = $APPLICATION->ConvertCharset($str, "windows-1251", "UTF-8")); # bitrix
	$html = mb_convert_encoding($html, "UTF-8", $textCharset);
}

$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'utf-8');

$textCharset = detect_encoding($html);
echo $textCharset;
// $html = file_get_contents('http://habrahabr.ru/');

$tstart = microtime(true); 
/* Далее код библиотеки */

include('Zend/Dom/Query.php');

$znd = new Zend_Dom_Query($html);

$to_echo = '';

foreach ($znd->query('#Main div span a') as $topic){
    $to_echo .= $topic->nodeValue.'<br />';
	$ret[] = getArray($topic);
}

/* Конец кода библиотеки */
$tend = microtime(true);
$totaltime = ($tend - $tstart);

$textCharset = detect_encoding($to_echo);
echo $textCharset;
echo $to_echo;
print_r($ret);
// file_put_contents('results/'.$file_to_result.'.txt', "done in: $totaltime sec\r\n", FILE_APPEND);
?>