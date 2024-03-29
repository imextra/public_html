<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/xml.php');

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/probki/styles.css');

if($arGadgetParams["CITY"]!='')
	$url = 'yasoft=barff&region='.substr($arGadgetParams["CITY"], 1).'&ts='.mktime();
else
	$url = 'ts='.mktime();

$cache = new CPageCache();
if($arGadgetParams["CACHE_TIME"]>0 && !$cache->StartDataCache($arGadgetParams["CACHE_TIME"], 'c'.$arGadgetParams["CITY"], "gdprobki"))
	return;


$ob = new CHTTP();
$ob->http_timeout = 10;
$ob->Query(
	"GET",
	"export.yandex.ru",
	80,
	"/bar/reginfo.xml?".$url,
	false,
	"",
	"N"
	);


$errno = $ob->errno;
$errstr = $ob->errstr;

$res = $ob->result;

$res = str_replace("\xE2\x88\x92", "-", $res);

$xml = new CDataXML();
$xml->LoadString($APPLICATION->ConvertCharset($res, 'UTF-8', SITE_CHARSET));

$node = $xml->SelectNodes('/info/traffic/title');
?>
<h3><?=$node->content?></h3>
<table width="90%"><tr>
<td width="80%" nowrap>
<?$node = $xml->SelectNodes('/info/traffic/hint');?>
<span class="gdtrafic"><?=$node->content?></span><br>
<span class="gdtrafinfo">
<?$node = $xml->SelectNodes('/info/traffic/length');?>
Протяженность: <?=$node->content?> м.<br>
<?$node = $xml->SelectNodes('/info/traffic/time');?>
Последнее обновление: <?=$node->content?>

</span>
</td>
<?
$node = $xml->SelectNodes('/info/traffic/level');
$t = Intval($node->content);
?>
<td nowrap="yes" width="20%"><span class="traf<?=intval(($t+1)/2)?>"><?=$t?></span></td>
</tr>
</table>
<?if($arGadgetParams["SHOW_URL"]=="Y"):?>
<br />
<?$node = $xml->SelectNodes('/info/traffic/url');?>
<a href="<?=htmlspecialchars($node->content)?>">Подробнее</a> <a href="<?=htmlspecialchars($node->content)?>"><img width="7" height="7" border="0" src="/bitrix/components/bitrix/desktop/images/arrows.gif" /></a>
<br />
<?endif?>
<?$cache->EndDataCache();?>
