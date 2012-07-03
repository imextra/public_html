<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;
    
$arComponentParameters = array(
   "GROUPS" => array(
      "GROUP1" => array(
         "NAME" => GetMessage("GROUP1_NAME")
      ),
      "GROUP2" => array(
         "NAME" => GetMessage("GROUP2_NAME")
      ),
   ),
   "PARAMETERS" => array(
      "PARAM1" => array(
         "PARENT" => "GROUP1",
         "NAME" => GetMessage("PARAM1_NAME"),
         "TYPE" => "STRING",
         "DEFAULT" => '',
      ),
      "PARAM2" => array(
         "PARENT" => "GROUP2",
         "NAME" => GetMessage("PARAM2_NAME"),
         "TYPE" => "LIST",
         "VALUES" => array('1' => '1000', '2' => '2000', '3' => '3000'),
         "DEFAULT" => '2',
      ),
  
      "SET_TITLE" => array(),
      "CACHE_TIME" => array(),
   )
);
    
?>
