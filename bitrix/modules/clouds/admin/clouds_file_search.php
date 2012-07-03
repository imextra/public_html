<?
define("ADMIN_MODULE_NAME", "clouds");

/*.require_module 'standard';.*/
/*.require_module 'pcre';.*/
/*.require_module 'bitrix_main_include_prolog_admin_before';.*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!$USER->CanDoOperation("clouds_browse"))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

/*.require_module 'bitrix_clouds_include';.*/
if(!CModule::IncludeModule('clouds'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$bucket_id = 0;
$arBuckets = array();
foreach(CCloudStorageBucket::GetAllBuckets() as $arBucket)
{
	if($arBucket["ACTIVE"] == "Y")
	{
		$bucket_id = $arBucket["ID"];
		$arBuckets[$bucket_id] = $arBucket["BUCKET"];
	}
}

$message = /*.(CAdminMessage).*/null;
$sTableID = "tbl_clouds_file_search";
$lAdmin = new CAdminList($sTableID);

$lAdmin->InitFilter(array("bucket", "path"));
$path = isset($_GET["path"])? $_GET["path"]: $path;
$path = preg_replace("#[\\\\\\/]+#", "/", "/".$path."/");
$n = preg_replace("/[^a-zA-Z0-9_:\\[\\]]/", "", $_GET["n"]);
if(intval($bucket) <= 0 && count($arBuckets) == 1)
	$bucket = $bucket_id;

//TODO when there is only one cloud storage there is no need for filter or at least we can preset it
$arHeaders = array(
	array(
		"id" => "FILE_NAME",
		"content" => GetMessage("CLO_STORAGE_SEARCH_NAME"),
		"default" => true,
	),
	array(
		"id" => "FILE_SIZE",
		"content" => GetMessage("CLO_STORAGE_SEARCH_SIZE"),
		"align" => "right",
		"default" => true,
	),
);

$lAdmin->AddHeaders($arHeaders);

$arData = /*.(array[int][string]string).*/array();

$obBucket = new CCloudStorageBucket($bucket);
if($obBucket->Init() && $_GET["file"]!=="y")
{
	$arFiles = $obBucket->ListFiles($path);

	if($path != "/")
		$arData[] = array("ID" => "D..", "TYPE" => "dir", "NAME" => "..", "SIZE" => "");
	if(is_array($arFiles))
	{
		foreach($arFiles["dir"] as $i => $dir)
			$arData[] = array("ID" => "D".$dir, "TYPE" => "dir", "NAME" => $dir, "SIZE" => '');
		foreach($arFiles["file"] as $i => $file)
			$arData[] = array("ID" => "F".$file, "TYPE" => "file", "NAME" => $file, "SIZE" => $arFiles["file_size"][$i]);
	}
	else
	{
		$e = $APPLICATION->GetException();
		if(is_object($e))
			$message = new CAdminMessage(GetMessage("CLO_STORAGE_FILE_LIST_ERROR"), $e);
	}
}

$rsData = new CDBResult;
$rsData->InitFromArray($arData);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(''));

while(is_array($arRes = $rsData->NavNext()))
{
	$row =& $lAdmin->AddRow($arRes["ID"], $arRes);

	if($arRes["TYPE"] === "dir")
	{
		if($arRes["NAME"] === "..")
			$row->link = 'clouds_file_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($n).'&bucket='.$obBucket->ID.'&path='.urlencode(preg_replace('#([^/]+)/$#', '', $path));
		else
			$row->link = 'clouds_file_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($n).'&bucket='.$obBucket->ID.'&path='.urlencode($path.$arRes["NAME"].'/');

		$row->AddViewField("FILE_NAME", '<div class="clouds_menu_icon_folder'.($arRes["NAME"] === ".."? "_up": "").'"></div><a href="'.htmlspecialchars($row->link).'">'.htmlspecialcharsex($arRes["NAME"]).'</a>');
		$row->AddViewField("FILE_SIZE", '&nbsp;');
	}
	else
	{
		$row->link = 'clouds_file_search.php?lang='.LANGUAGE_ID.'&n='.urlencode($n).'&file=y&bucket='.$obBucket->ID.'&path='.urlencode($path.$arRes["NAME"]);
		$row->AddViewField("FILE_NAME", '<a href="'.htmlspecialchars($row->link).'">'.htmlspecialcharsex($arRes["NAME"]).'</a>');
		$row->AddViewField("FILE_SIZE", CFile::FormatSize((float)$arRes["SIZE"]));
	}
}

$arFooter = array(
	array(
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $path === "/"? $rsData->SelectedRowsCount(): $rsData->SelectedRowsCount()-1, // W/O ..
	),
);
$lAdmin->AddFooter($arFooter);

/*
if($obBucket->Init())
{
	$chain = $lAdmin->CreateChain();
	$arPath = explode("/", $path);
	$curPath = "/";
	foreach($arPath as $dir)
	{
		if($dir != "")
		{
			$curPath .= $dir."/";
			$url = "clouds_file_search.php?lang=".LANGUAGE_ID."&n=".urlencode($n)."&bucket=".$obBucket->ID."&path=".urlencode($curPath);
			$chain->AddItem(array(
				"TEXT" => htmlspecialcharsex($dir),
				"LINK" => htmlspecialchars($url),
				"ONCLICK" => $lAdmin->ActionAjaxReload($url).';return false;',
			));
		}
	}
	$lAdmin->ShowChain($chain);
}
*/

$lAdmin->BeginPrologContent();

if(is_object($message))
	echo $message->Show();

if($obBucket->Init() && $_GET["file"] === "y")
	echo "<script>SelFile('".CUtil::JSEscape(urldecode($obBucket->GetFileSRC(rtrim($path, "/"))))."');</script>";

$lAdmin->EndPrologContent();

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CLO_STORAGE_SEARCH_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>
<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = Array(
	"bucket"=>GetMessage("CLO_STORAGE_SEARCH_BUCKET"),
	"path"=>GetMessage("CLO_STORAGE_SEARCH_PATH"),
);
$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields);
$oFilter->Begin();
?>
<script language="JavaScript">
function SelFile(name)
{
	el = window.opener.document.getElementById('<?echo CUtil::JSEscape($n)?>');
	if(el)
	{
		el.value = name;
		if (window.opener.BX)
			window.opener.BX.fireEvent(el, 'change');
	}
	window.close();
}
</script>
	<tr>
		<td><b><?echo GetMessage("CLO_STORAGE_SEARCH_BUCKET")?></b></td>
		<td><select name="bucket">
			<option value=""><?echo GetMessage("CLO_STORAGE_SEARCH_CHOOSE_BUCKET")?></option>
			<?foreach($arBuckets as $id => $name):?>
					<option value="<?echo htmlspecialchars($id)?>" <?if($id == $bucket) echo "selected"?>><?echo htmlspecialcharsex($name)?></option>
			<?endforeach?>
		</select></td>
	</tr>

	<tr>
		<td><?echo GetMessage("CLO_STORAGE_SEARCH_PATH")?></td>
		<td><input type="text" name="path" size="45" value="<?echo htmlspecialchars($path)?>"></td>
	</tr>
<?
$oFilter->Buttons(array(
	"url" => "/bitrix/admin/clouds_file_search.php?lang=".LANGUAGE_ID."&n=".urlencode($n),
	"table_id" => $sTableID,
));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
?>