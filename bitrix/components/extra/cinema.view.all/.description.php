<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("NAME"), //�������� ����������
	"DESCRIPTION" => GetMessage("DESCR"), //�������� ����������
	"ICON" => "/images/system.empty.png", //������ ����������
	"VERSION" => "1.00",
    "PATH" => array(
				"ID" => "valcom",
				"NAME" => GetMessage("VALCOM_GROUP_NAME"),
                "CHILD" => array(
                         "ID" => "system",
                         "NAME" => GetMessage("SYSTEM_GROUP_NAME"),
                      ),
		),	
   //"CACHE_PATH" => "Y",
   //"COMPLEX" => "Y"
);

?>