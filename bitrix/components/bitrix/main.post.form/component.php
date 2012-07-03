<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(strlen($arParams["FORM_ID"]) <= 0)
	$arParams["FORM_ID"] = "POST_FORM_".RandString(3);
if(strlen($arParams["FORM_ACTION_URL"]) <= 0)
	$arParams["FORM_ACTION_URL"] = POST_FORM_ACTION_URI;
$this->IncludeComponentTemplate();
?>