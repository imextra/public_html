<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();



if(!empty($arResult['USER_CITY'])){
	LocalRedirect('/city/?CITY='.$arResult['USER_CITY']);		
}else{
	LocalRedirect('/setcity/?ACT=setcity&CITY='.$GLOBALS['DEFAULT_CITY']);		
}


	// echo '<pre>',print_r($arParams),'</pre>';
	// echo '<pre>',print_r($arResult),'</pre>';
if($USER->IsAdmin()){
//	echo '<pre>',print_r($arParams),'</pre>';
//	echo '<pre>',print_r($arResult),'</pre>';
//	echo '<pre>',print_r($_GET),'</pre>';
}
?>