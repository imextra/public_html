<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?$APPLICATION->ShowTitle()?></title>
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/dot-luv/jquery-ui-1.8.21.custom.css">
<?$APPLICATION->ShowHead()?>
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/jquery/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/jquery-ui/jquery-ui-1.8.21.custom.min.js"></script>
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/jquery.cookie.js"></script>
<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/jquery.dump.js"></script>

<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/fns.lib.js"></script>

<script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/readybox.all.js"></script>


</head>
<body>
<?$APPLICATION->ShowPanel();?>


<div id="dialog-message" class="hide" title="Диалоговое окно">
	<table style="margin: 20px">
		<tr>
			<td><span id="dialog-message-icon" class="ui-icon" style="margin:0 7px 50px 0;"></span></td>
			<td style="color:white" id="dialog-message-text"></td>
		</tr>
	</table>
</div>


<div id="content">

	<div style="margin:10px 0 10px 0; background-color:#DDDDDD; padding:10px; border:1px solid #EEE">
	<table style="width:100%;" >
	<tr valign="middle">
		<td align="left"><a href="/"><img src="<?=SITE_TEMPLATE_PATH?>/img/logo_all_pages.png" width="200" /></a></td>
		<td align="right"><?$APPLICATION->IncludeComponent("extra:location.setcity", "all.pages_ver1", array(
			"IBLOCK_TYPE_CITY" => "location",
			"IBLOCK_ID_CITY" => "12",
			"AJAX_MODE" => "N",
			"AJAX_OPTION_JUMP" => "N",
			"AJAX_OPTION_STYLE" => "Y",
			"AJAX_OPTION_HISTORY" => "N",
			"AJAX_OPTION_ADDITIONAL" => ""
			),
			false
		);?><?$APPLICATION->IncludeComponent("extra:moderation.viewlink.menu", ".default", array(
	"MODER_GROUPS" => array(
		0 => "8",
	)
	),
	false
);?></td>
	</tr>
	</table>
	</div>


	<div style="padding:0 10px;">
	<table style="width:100%;"><tr>
		<td style="border:0px solid red;vertical-align:top; padding:0 10px 0 0px;">
<?php
// echo '<pre>',print_r($GLOBALS),'</pre>';		
?>