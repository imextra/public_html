<?
define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/getdata.php");

if (!check_bitrix_sessid())
	return;

$rnd = $_REQUEST["rnd"];

include_once(dirname(__FILE__).'/include.php');

if (
	!array_key_exists("GD_RSS_PARAMS", $_SESSION) 
	|| !array_key_exists($rnd, $_SESSION["GD_RSS_PARAMS"]) 
	|| !is_array($_SESSION["GD_RSS_PARAMS"][$rnd])
)
	return;

$arGadgetParams = $_SESSION["GD_RSS_PARAMS"][$rnd];

$arGadgetParams["CNT"] = IntVal($arGadgetParams["CNT"]);
if($arGadgetParams["CNT"]>50)
	$arGadgetParams["CNT"] = 0;

$cache = new CPageCache();
if(
	$arGadgetParams["CACHE_TIME"] > 0 
	&& !$cache->StartDataCache($arGadgetParams["CACHE_TIME"], 'c'.$arGadgetParams["RSS_URL"].'-'.$arGadgetParams["CNT"], "gdrss")
)
	return;
	
?>
<?
if($arGadgetParams["RSS_URL"]=="")
{
	?>
	<div class="gdrsserror">
		<?echo GetMessage("GD_RSS_READER_NEW_RSS")?>
	</div>
	<?
	$cache->EndDataCache();
	return;
}

session_write_close();

$rss = gdGetRss($arGadgetParams["RSS_URL"]);
if($rss):
	$rss->title = strip_tags($rss->title);
?>
<script>
function ShowHide<?=htmlspecialchars(CUtil::JSEscape($rnd))?>(id)
{
	var d = document.getElementById(id);
	if(d.style.display == 'none')
		d.style.display = 'block';
	else
		d.style.display = 'none';
}
</script>
<div class="gdrsstitle">
<?if($arGadgetParams["SHOW_URL"]=="Y" && preg_match("'^(http://|https://|ftp://)'i", $rss->link)):?>
	<a href="<?=htmlspecialchars($rss->link)?>"><?=htmlspecialcharsEx($rss->title)?></a>
<?else:?>
	<?=htmlspecialcharsEx($rss->title)?>
<?endif?>
</div>
<div class="gdrssitems">
<?
$cnt = 0;
foreach($rss->items as $item):
	$cnt++;
	if($arGadgetParams["CNT"]>0 && $arGadgetParams["CNT"]<$cnt)
		break;
	$item["DESCRIPTION"] = strip_tags($item["DESCRIPTION"]);
	$item["TITLE"] = strip_tags($item["TITLE"]);
?>
<div class="gdrssitem">
	<div class="gdrssitemtitle">&raquo; <a href="javascript:void(0)" onclick="ShowHide<?=htmlspecialchars(CUtil::JSEscape($rnd))?>('z<?=$cnt.md5($item["TITLE"])?><?=htmlspecialchars(CUtil::JSEscape($rnd))?>')"><?=htmlspecialcharsEx($item["TITLE"])?></a></div>
	<div class="gdrssitemdetail" id="z<?=$cnt.md5($item["TITLE"])?><?=htmlspecialchars(CUtil::JSEscape($rnd))?>" style="display:none">
		<div class="gdrssitemdate"><?=htmlspecialcharsEx($item["PUBDATE"])?></div>
		<div class="gdrssitemdesc"><?=htmlspecialcharsEx($item["DESCRIPTION"])?> <?if($arGadgetParams["SHOW_URL"]=="Y" && preg_match("'^(http://|https://|ftp://)'i", $item["LINK"])):?><a href="<?=htmlspecialchars($item["LINK"])?>"><?echo GetMessage("GD_RSS_READER_RSS_MORE")?></a><?endif?></div>
	</div>
</div>
<?endforeach;?>
</div>
<?else:?>
<div class="gdrsserror">
<?echo GetMessage("GD_RSS_READER_RSS_ERROR")?>
</div>
<?endif?>
<?$cache->EndDataCache();?>