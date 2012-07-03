<?
/*
You can place here your functions and event handlers

AddEventHandler("module", "EventName", "FunctionName");
function FunctionName(params)
{
	//code
}
*/




include $_SERVER["DOCUMENT_ROOT"]."/include/config.php";
include $_SERVER["DOCUMENT_ROOT"]."/include/fns.lib.php";
include $_SERVER["DOCUMENT_ROOT"]."/include/geo.class.php";

# Нужна для отладки http://dev.1c-bitrix.ru/api_help/main/general/constants.php#error_email
define("ERROR_EMAIL", "abc@valcom.ru");

?>