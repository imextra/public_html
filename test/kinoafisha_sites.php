<?php

include $_SERVER['DOCUMENT_ROOT'].'/include/fns.lib.php';


$html = file_get_contents('http://kinoafisha.info/');

$textCharset = detect_encoding($html);
if($textCharset != 'utf-8'){
	// $str = $APPLICATION->ConvertCharset($str, "windows-1251", "UTF-8")); # bitrix
	$html = mb_convert_encoding($html, "UTF-8", $textCharset);
}
$html = mb_convert_encoding( $html, 'HTML-ENTITIES', 'utf-8');

include('Zend/Dom/Query.php');

$znd = new Zend_Dom_Query($html);

foreach ($znd->query('#Main div span a') as $topic){
	$ret[] = dom_to_array($topic);
}

print_r($ret);
?>