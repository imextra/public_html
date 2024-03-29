<?
IncludeModuleLangFile(__FILE__);
class CMedialib
{
	function Init(){}
	function GetOperations($collectionId)
	{
		global $USER;
		static $oCollections;
		static $arOp;

		$userGroups = $USER->GetUserGroupArray();
		$key = $collectionId.'|'.implode('-', $userGroups);

		if (!is_array($arOp[$key]))
		{
			if (!is_array($arOp))
				$arOp = array();

			if (!is_array($oCollections))
			{
				$res = CMedialib::GetCollectionTree();
				$oCollections = $res['Collections'];
			}

			$userGroups = $USER->GetUserGroupArray();
			$res = CMedialib::GetAccessPermissionsArray($collectionId, $oCollections);

			$arOp[$key]  = array();
			foreach ($res as $group_id => $task_id)
			{
				if (in_array($group_id, $userGroups))
					$arOp[$key] = array_merge($arOp[$key], CTask::GetOperations($task_id, true));
			}
		}
		return $arOp[$key];
	}

	function CanDoOperation($operation, $collectionId=0, $userId = false)
	{
		if ($GLOBALS["USER"]->IsAdmin())
			return true;

		$arOp = CMedialib::GetOperations($collectionId);
		return in_array($operation, $arOp);
	}

	function GetAccessPermissionsArray($collectionId = 0, $oCollections = false)
	{
		static $arAllTasks;
		if (is_array($arAllTasks[$collectionId]))
			return $arAllTasks[$collectionId];

		$col = $oCollections[$collectionId];
		$arCols = array();
		$resTask = array();

		if ($col || $collectionId == 0)
		{
			$arCols[] = $collectionId;
			if (intVal($col['PARENT_ID']) > 0)
			{
				$col_ = $col;
				while($col_ && intVal($col_['PARENT_ID']) > 0)
				{
					$arCols[] = $col_['PARENT_ID'];
					$col_ = $oCollections[$col_['PARENT_ID']];
				}
			}
			$arCols[] = 0;
			$arPerm = CMedialib::_GetAccessPermissions($arCols);

			for($i = count($arCols); $i >= 0; $i--)
			{
				$colId = $arCols[$i];
				if (is_array($arPerm[$colId]))
				{
					for ($j = 0, $n = count($arPerm[$colId]); $j < $n; $j++)
						$resTask[$arPerm[$colId][$j]['GROUP_ID']] = $arPerm[$colId][$j]['TASK_ID'];
				}
			}
		}

		if (!is_array($arAllTasks))
			$arAllTasks = array();
		$arAllTasks[$collectionId] = $resTask;

		return $resTask;
	}

	function _GetAccessPermissions($arCols = array())
	{
		global $DB;

		$s = '0';
		for($i=0; $i < count($arCols); $i++)
			$s .= ",".IntVal($arCols[$i]);

		$strSql = 'SELECT *
			FROM b_group_collection_task GCT
			WHERE GCT.COLLECTION_ID in ('.$s.')';

		$res = $DB->Query($strSql , false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$arResult = array();
		while($arRes = $res->Fetch())
		{
			$colid = $arRes['COLLECTION_ID'];
			if (!is_array($arResult[$colid]))
				$arResult[$colid] = array();

			unset($arRes['COLLECTION_ID']);
			$arResult[$colid][] = $arRes;
		}

		return $arResult;
	}

	function ShowDialogScript($arConfig = array())
	{
		global $USER;

		CUtil::InitJSCore(array('ajax'));

		$strWarn = '';
		$arConfig['bReadOnly'] = false;
		$arConfig['lang'] = LANGUAGE_ID;

		$event = '';
		if (isset($arConfig['event']))
			$event = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['event']);
		if (strlen($event) <= 0)
			$strWarn .= GetMessage('ML_BAD_EVENT').'. ';

		$resultDest = "";
		$bDest = is_array($arConfig['arResultDest']);
		if ($bDest)
		{
			if (isset($arConfig['arResultDest']["FUNCTION_NAME"]))
			{
				$arConfig['arResultDest']["FUNCTION_NAME"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["FUNCTION_NAME"]);
				$bDest = strlen($arConfig['arResultDest']["FUNCTION_NAME"]) > 0;
				$resultDest = "FUNCTION";
			}
			elseif (isset($arConfig['arResultDest']["FORM_NAME"], $arConfig['arResultDest']["FORM_ELEMENT_NAME"]))
			{
				$arConfig['arResultDest']["FORM_NAME"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["FORM_NAME"]);
				$arConfig['arResultDest']["FORM_ELEMENT_NAME"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["FORM_ELEMENT_NAME"]);
				$bDest = strlen($arConfig['arResultDest']["FORM_NAME"]) > 0 && strlen($arConfig['arResultDest']["FORM_ELEMENT_NAME"]) > 0;
				$resultDest = "FORM";
			}
			elseif (isset($arConfig['arResultDest']["ELEMENT_ID"]))
			{
				$arConfig['arResultDest']["ELEMENT_ID"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arConfig['arResultDest']["ELEMENT_ID"]);
				$bDest = strlen($arConfig['arResultDest']["ELEMENT_ID"]) > 0;
				$resultDest = "ID";
			}
			else
			{
				$bDest = false;
			}
		}
		if (!$bDest)
			$strWarn .= GetMessage('ML_BAD_RETURN').'. ';

		if (strlen($strWarn) <= 0)
		{
			?>
			<script>
			if (!window.BX && top.BX)
				window.BX = top.BX;

			<?CMedialib::AppendLangMessages();?>

			window.<?= $arConfig['event']?> = function(bLoadJS)
			{
				if (window.oBXMedialib && window.oBXMedialib.bOpened)
					return false;

				<?if(!CMedialib::CanDoOperation('medialib_view_collection', 0)):?>
					return alert(ML_MESS.AccessDenied);
				<?else:?>

				if (!window.BXMediaLib)
				{
					if (bLoadJS !== false)
					{
						// Append CSS
						BX.loadCSS("/bitrix/js/fileman/medialib/medialib.css");

						var arJS = [];
						if (!window.jsAjaxUtil)
							arJS.push("/bitrix/js/main/ajax.js?v=<?= filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/main/ajax.js')?>");
						if (!window.jsUtils)
							arJS.push("/bitrix/js/main/utils.js?v=<?= filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/main/utils.js')?>");
						if (!window.CHttpRequest)
							arJS.push("/bitrix/js/main/admin_tools.js?v=<?= filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/main/admin_tools.js')?>");

						arJS.push("/bitrix/js/fileman/medialib/common.js?v=<?= filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/fileman/medialib/common.js')?>");
						arJS.push("/bitrix/js/fileman/medialib/core.js?v=<?= filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/fileman/medialib/core.js')?>");
						BX.loadScript(arJS);
					}
					return setTimeout(function(){<?=$arConfig['event']?>(false)}, 50);
				}

				<?CMedialib::ShowJS()?>
				<?
					$arSet = explode(',' , CUserOptions::GetOption("fileman", "medialib_user_set", '600,450,0'));
					$width = $arSet[0] ? intVal($arSet[0]) : 600;
					$height = $arSet[1] ? intVal($arSet[1]) : 450;
					$coll_id = $arSet[2] ? intVal($arSet[2]) : 0;
				?>
				window._mlUserSettings = window._mlUserSettings || {width: <?=$width?>, height: <?=$height?>, coll_id: <?=$coll_id?>}

				var oConfig =
				{
					sessid: "<?=bitrix_sessid()?>",
					thumbWidth : <?= COption::GetOptionInt('fileman', "ml_thumb_width", 140)?>,
					thumbHeight : <?= COption::GetOptionInt('fileman', "ml_thumb_height", 105) ?>,
					userSettings : window._mlUserSettings,
					resType: "<?= $resultDest?>",
					Types : <?= CUtil::PhpToJSObject(CMedialib::GetTypes($arConfig['types']))?>,
					arResultDest : <?= CUtil::PhpToJSObject($arConfig['arResultDest'])?>,
					rootAccess: {
						new_col: '<?= CMedialib::CanDoOperation('medialib_new_collection', 0)?>',
						edit: '<?= CMedialib::CanDoOperation('medialib_edit_collection', 0)?>',
						del: '<?= CMedialib::CanDoOperation('medialib_del_collection', 0)?>',
						new_item: '<?= CMedialib::CanDoOperation('medialib_new_item', 0)?>',
						edit_item: '<?= CMedialib::CanDoOperation('medialib_edit_item', 0)?>',
						del_item: '<?= CMedialib::CanDoOperation('medialib_del_item', 0)?>',
						access: '<?= CMedialib::CanDoOperation('medialib_access', 0)?>'
					},
					bCanUpload: <?= $USER->CanDoOperation('fileman_upload_files') ? 'true' : 'false'?>,
					bCanViewStructure: <?= $USER->CanDoOperation('fileman_view_file_structure') ? 'true' : 'false'?>,
					strExt : "<?= CMedialib::GetMediaExtentions()?>",
					lang : "<?= $arConfig['lang']?>",
					description_id : '<?= CUtil::JSEscape($arConfig['description_id'])?>'
				};

				window.oBXMedialib = new BXMediaLib(oConfig);
				oBXMedialib.Open();
				<?endif;?>
			}
			</script>
			<?
		}
		else
		{
			echo '<font color="#FF0000">'.htmlspecialchars($strWarn).'</font>';
		}
	}

	function AttachJSScripts()
	{
		if(!defined("BX_B_MEDIALIB_SCRIPT_LOADED"))
		{
			define("BX_B_MEDIALIB_SCRIPT_LOADED", true);
?>
BX.loadScript("/bitrix/js/main/file_dialog.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/file_dialog.js')?>");
<?
		}
	}

	function AppendLangMessages()
	{
?>
var ML_MESS =
{
	AccessDenied : '<?= addslashes(GetMessage('ML_ACCESS_DENIED'))?>',
	SessExpired : '<?= addslashes(GetMessage('ML_SESS_EXPIRED'))?>',
	DelCollection : '<?= addslashes(GetMessage('ML_DEL_COLLECTION'))?>',
	DelItem : '<?= addslashes(GetMessage('ML_DEL_ITEM'))?>',
	DelCollectionConf : '<?= addslashes(GetMessage('ML_DEL_COLLECTION_CONFIRM'))?>',
	DelItemConf : '<?= addslashes(GetMessage('ML_DEL_ITEM_CONFIRM'))?>',
	EditCollection : '<?= addslashes(GetMessage('ML_EDIT_COLLECTION'))?>',
	EditItem : '<?= addslashes(GetMessage('ML_EDIT_ITEM'))?>',
	NewCollection : '<?= addslashes(GetMessage('ML_NEW_COLLECTION'))?>',
	Collection : '<?= addslashes(GetMessage('ML_COLLECTION'))?>',
	ColLocEr : '<?= addslashes(GetMessage('ML_COL_LOC_ER'))?>',
	ColLocEr2 : '<?= addslashes(GetMessage('ML_COL_LOC_ER2'))?>',
	Item : '<?= addslashes(GetMessage('ML_ITEM'))?>',
	NewItem : '<?= addslashes(GetMessage('ML_NEW_ITEM'))?>',
	DelColFromItem : '<?= addslashes(GetMessage('ML_DEL_COL2ITEM'))?>',
	ItemNoColWarn : '<?= addslashes(GetMessage('ML_COL2ITEM_WARN'))?>',
	DateModified : '<?= addslashes(GetMessage('ML_DATE_MODIFIED'))?>',
	FileSize : '<?= addslashes(GetMessage('ML_FILE_SIZE'))?>',
	ImageSize : '<?= addslashes(GetMessage('ML_IMAGE_SIZE'))?>',
	CheckedColTitle : '<?= addslashes(GetMessage('ML_CHECKED_COL_TITLE'))?>',
	ItSourceError : '<?= addslashes(GetMessage('ML_SOURCE_ERROR'))?>',
	ItNameError : '<?= addslashes(GetMessage('ML_NAME_ERROR'))?>',
	ItCollsError : '<?= addslashes(GetMessage('ML_COLLS_ERROR'))?>',
	ColNameError : '<?= addslashes(GetMessage('ML_COL_NAME_ERROR'))?>',
	DelItConfTxt : '<?= addslashes(GetMessage('ML_DEL_CONF_TEXT'))?>',
	DelItB1 : '<?= addslashes(GetMessage('ML_DEL_IT_B1'))?>',
	DelItB2 : '<?= addslashes(GetMessage('ML_DEL_IT_B2'))?>',
	CollAccessDenied : '<?= addslashes(GetMessage('ML_COLL_ACCESS_DENIED'))?>',
	CollAccessDenied2 : '<?= addslashes(GetMessage('ML_COLL_ACCESS_DENIED2'))?>',
	CollAccessDenied3: '<?= addslashes(GetMessage('ML_COLL_ACCESS_DENIED3'))?>',
	CollAccessDenied4: '<?= addslashes(GetMessage('ML_COLL_ACCESS_DENIED4'))?>',
	BadSubmit: '<?= addslashes(GetMessage('ML_BAD_SUBMIT'))?>',
	ColNameError: '<?= addslashes(GetMessage('ML_COL_NAME_ERROR'))?>',
	ItemExtError: '<?= addslashes(GetMessage('ML_ITEM_EXT_ERROR'))?>',
	EditItemError: '<?= addslashes(GetMessage('ML_EDIT_ITEM_ERROR'))?>',
	SearchResultEx: '<?= addslashes(GetMessage('ML_SEARCH_RESULT_EX'))?>',
	DelElConfirm: '<?= addslashes(GetMessage('ML_DEL_EL_CONFIRM'))?>',
	DelElConfirmYes: '<?= addslashes(GetMessage('ML_DEL_EL_CONFIRM_YES'))?>',
	SearchDef: '<?= addslashes(GetMessage('ML_SEARCH_DEF'))?>',
	NoResult: '<?= addslashes(GetMessage('ML_SEARCH_NO_RESULT'))?>',
	ViewItem : '<?= addslashes(GetMessage('ML_VIEW_ITEM'))?>',
	FileExt : '<?= addslashes(GetMessage('ML_FILE_EXT'))?>',
	CheckExtTypeConf : '<?= addslashes(GetMessage('ML_CHECK_TYPE_EXT_CONF'))?>'
};
<?
	}

	function AppendLangMessagesEx()
	{
?>
ML_MESS.Edit = '<?= addslashes(GetMessage('ML_EDIT'))?>';
ML_MESS.Delete = '<?= addslashes(GetMessage('ML_DELETE'))?>';
ML_MESS.Access = '<?= addslashes(GetMessage('ML_ACCESS'))?>';
ML_MESS.AccessTitle = '<?= addslashes(GetMessage('ML_ACCESS_TITLE'))?>';

ML_MESS.AddElement = '<?= addslashes(GetMessage('ML_ADD_ELEMENT'))?>';
ML_MESS.AddElementTitle = '<?= addslashes(GetMessage('ML_ADD_ELEMENT_TITLE'))?>';
ML_MESS.AddCollection = '<?= addslashes(GetMessage('ML_ADD_COLLECTION'))?>';
ML_MESS.AddCollectionTitle = '<?= addslashes(GetMessage('ML_ADD_COLLECTION_TITLE'))?>';
ML_MESS.MultiDelConfirm = '<?= addslashes(GetMessage('ML_MULTI_DEL_CONFIRM'))?>';
ML_MESS.Decreased = '<?= addslashes(GetMessage('ML_DECREASED'))?>';

ML_MESS.ChangeType = '<?= addslashes(GetMessage('ML_CHANGE_TYPE'))?>';
ML_MESS.ChangeTypeTitle = '<?= addslashes(GetMessage('ML_CHANGE_TYPE_TITLE'))?>';
ML_MESS.ChangeTypeError = '<?= addslashes(GetMessage('ML_CHANGE_TYPE_ERROR'))?>';
ML_MESS.ChangeTypeChildConf = '<?= addslashes(GetMessage('ML_CHANGE_TYPE_CHILD_CONF'))?>';
<?
	}

	function Start($Params)
	{
		$Params['bReadOnly'] = false;
		CMedialib::BuildDialog($Params);

		// TODO: Check access
		?>#ML_SUBDIALOGS_BEGIN#<?
		CMedialib::BuildAddCollectionDialog($Params);
		CMedialib::BuildAddItemDialog($Params);
		CMedialib::BuildConfirmDialog($Params);
		CMedialib::BuildViewItemDialog($Params);
		$exParams = array('types' => $Params['types']);
		?>#ML_SUBDIALOGS_END#

		<script><?CMedialib::GetCollections($exParams);?></script>
		<?
	}

	function BuildDialog($Params)
	{
		?>
		#ML_MAIN_DIALOG_BEGIN#
		<form name="medialib_form"><table  id="ml_frame" class="ml-frame"><tr>
		<td class="ml-title-cell">
			<table onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('bxmedialib'));"><tr><td style="width: 10px; padding-left: 3px;"><img class="ml-iconkit ml-dd-dot" src="/bitrix/images/1.gif" /></td><td class="ml-diad-title" id="ml_diag_title"><?=GetMessage('ML_MEDIALIB')?></td><td id="bxml_close" class="ml-close" title="<?=GetMessage('ML_CLOSE')?>"><img src="/bitrix/images/1.gif"></td></tr></table>
		</td></tr>
		<tr><td class="ml-content-cell">
		<div class="ml-head-cont" id="ml_head_cont">
			<table><tr><td class="ml-left">
			<div class="ml-breadcrumbs" id="ml_breadcrumbs"></div>
			</td><td class="ml-right">
			<input class="ml-search ml-search-empty" id="medialib_search" type="text" value="<?=GetMessage('ML_SEARCH_DEF')?>"/>
			</td></tr></table>
		</div>
		<div class="ml-left-sec" id="ml_left_cont">
			<div id="ml_type_cont" class="ml-type-cont"></div>
			<div class="ml-collect-cont" id="ml_coll_cont"><div class="ml-no-colls"> - <?= GetMessage('ML_NO_COLS')?> - </div></div>
		</div>
		<div class="ml-right-sec" id="ml_right_cont">
			<div class="ml-list-cont" id="ml_list_cont"><div class="ml-list-noitems"> - <?= GetMessage('ML_NO_ITEMS')?> - </div></div>
			<div class="ml-info-cont"  id="ml_info_wnd">
				<div class="ml-info-noinfo"> - <?= GetMessage('ML_NO_ITEM_INFO')?> - </div>
				<table class="ml-info-tbl">
					<tr>
						<td colSpan="2">
							<div class="ml-info-name" id="ml_info_name"></div>
							<div class="ml-info-collections" id="ml_info_colls"></div>
						</td>
					</tr>
					<tr>
						<td style="width: 50%;">
							<span><?= GetMessage('ML_KEYWORDS')?>:</span>
							<span id="ml_info_keys"></span>
						</td>
						<td rowSpan="2" style="width: 50%; vertical-align: top;">
							<span><?= GetMessage('ML_DESC')?>:</span>
							<div class="ml-info-desc" id="ml_info_desc"></div>
						</td>
					</tr>
					<tr>
						<td style="vertical-align: top;">
							<div class="ml-info-details" id="ml_info_details"></div>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="ml-buttons-cont" id="ml_but_cont">
			<table><tr>
				<td class="ml-left">
				<?if (!$Params['bReadOnly']):?>
				<a id="ml_add_collection" href="javascript:void(0)" title="<?=GetMessage('ML_ADD_COLLECTION_TITLE')?>" class="ml-add-el-link"><img src="/bitrix/images/1.gif" /><?=GetMessage('ML_ADD_COLLECTION')?></a>

				<a id="ml_add_item" href="javascript:void(0)" title="<?=GetMessage('ML_ADD_ELEMENT_TITLE')?>" class="ml-add-el-link"><img src="/bitrix/images/1.gif" /><?=GetMessage('ML_ADD_ELEMENT')?></a>
				<?endif;?>
				</td><td class="ml-right">
				<input id="medialib_but_save" type="button" value="<?=GetMessage('ML_SELECT')?>" />
				<input id="medialib_but_cancel" type="button" value="<?=GetMessage('ML_CANCEL')?>" />
			</td></tr></table>
		</div>
		</td></tr>
		</table>
		</form>
		<div id="bxml_resizer" class="ml-resizer"></div>
		#ML_MAIN_DIALOG_END#
		<?
	}

	function BuildAddCollectionDialog($Params)
	{
		?>
		<div id="mlsd_coll" class="mlsd"><table class="mlsd-frame"><tr>
		<td class="ml-title-cell">
			<table onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('mlsd_coll'));"><tr><td style="width: 10px; padding-left: 3px;"><img class="ml-iconkit ml-dd-dot" src="/bitrix/images/1.gif" /></td><td class="ml-diad-title"><span id="mlsd_coll_title"></span></td><td id="mlsd_coll_close" class="ml-close" title="<?=GetMessage('ML_CLOSE')?>"><img src="/bitrix/images/1.gif"></td></tr></table>
		</td></tr>
		<tr><td class="ml-content-cell">
			<table class="mlsd-fields-tbl">
			<tr><td><b><?=GetMessage('ML_NAME')?>:</b></td><td><input type="text" id="mlsd_coll_name" /></td></tr>
			<tr><td style="vertical-align: top;"><?=GetMessage('ML_DESC')?>:</td><td><textarea id="mlsd_coll_desc" rows="2" cols="21"></textarea></td></tr>
			<tr><td><?=GetMessage('ML_KEYWORDS')?>:</td><td><input type="text" id="mlsd_coll_keywords" /></td></tr>
			<tr><td><?=GetMessage('ML_PLACE')?>:</td>
			<td><select id="mlsd_coll_parent" style="width: 190px;"><option value="0"><?= GetMessage('ML_UPPER_LEVEL')?></option></select></td></tr>
			</table>
		</td></tr>
		<tr><td class="ml-buttons-cell">
			<input id="mlsd_coll_save" type="button" value="<?=GetMessage('ML_SAVE')?>">
			<input id="mlsd_coll_cancel" type="button" value="<?=GetMessage('ML_CANCEL')?>">
		</td></tr>
		</table>
		</div>
		<?
	}

	function BuildAddItemDialog($Params)
	{
		?>
		<div id="mlsd_item" class="mlsd"><table class="mlsd-frame"><tr>
		<td class="ml-title-cell">
			<table onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('mlsd_item'));"><tr><td style="width: 10px; padding-left: 3px;"><img class="ml-iconkit ml-dd-dot" src="/bitrix/images/1.gif" /></td><td class="ml-diad-title"><span id="mlsd_item_title"></span></td><td id="mlsd_item_close" class="ml-close" title="<?=GetMessage('ML_CLOSE')?>"><img src="/bitrix/images/1.gif"></td></tr></table>
		</td></tr>
		<tr><td class="ml-content-cell">
			<div id="mlsd_item_upload" style="overflow: hidden;"><iframe class="mlsd-iframe" id="mlsd_iframe_upload" src="javascript:''" frameborder="0"></iframe></div>
		</td></tr>
		<tr><td class="ml-buttons-cell">
			<input id="mlsd_item_save" type="button" value="<?=GetMessage('ML_SAVE')?>">
			<input id="mlsd_item_cancel" type="button" value="<?=GetMessage('ML_CANCEL')?>">
		</td></tr>
		</table>
		</div>
		<?
		CAdminFileDialog::ShowScript(Array
			(
				"event" => "mlOpenFileDialog",
				"arResultDest" => Array("FUNCTION_NAME" => "mlOnFileDialogSave"),
				"arPath" => Array(),
				"select" => 'F',
				"operation" => 'O',// O - open, S - save
				"showUploadTab" => true,
				"showAddToMenuTab" => false,
				"fileFilter" => CMedialib::GetMediaExtentions(),
				"allowAllFiles" => false,
				"SaveConfig" => true
			)
		);
	}

	function ShowUploadForm($Params)
	{
		?>
<HTML>
<HEAD>
<style>
	body {margin:0px !important; overflow: hidden;}
	body *{font-family:Verdana,Arial,Helvetica,sans-serif; font-size: 13px; color: #000;}
	form {margin:0px !important;}
	table.mlsd-ifrm-tbl {width: 400px; height: 265px; margin: 3px;}
	a.mlsd-up-link{text-decoration: none; color: #6E8C9B; font-size: 11px;}
	table.mlsd-ifrm-tbl input{width: 220px;}
	div.mlsd-col-cont{height: 70px;}
	div.mlsd-col-label, div.mlsd-col-sel{font-weight: bold; float: left; padding: 2px; margin: 2px;}
	div.mlsd-col-sel select{width: 90px; display: block; margin-top: -2px;}
	div.mlsd-ch-col{float: left; border: 1px solid #6E8C9B; width: 80px; height: 20px; padding: 0px; overflow: hidden; margin: 2px; position: relative; background: url(/bitrix/images/fileman/medialib/group_bg.gif) repeat-x scroll left top;}
	div.mlsd-ch-col span{white-space: nowrap; font-size: 12px !important; display: block; margin: 2px 0 0 2px;}
	div.mlsd-ch-col img.ml-col-del{width: 17px; height: 18px; background-image: url(/bitrix/images/fileman/medialib/iconkit.gif); position: absolute; display: none; background-position: 0px -60px; top: 1px; right: 1px; cursor: pointer;}
	div.col-over img.ml-col-del{display: block !important;}
	div.mlsd-prev-cont{width: 150px; height: 140px; border: 1px solid #6E8C9B; text-align: center;}
	div.mlsd-prev-cont img{margin: 2px;}
	div.mlsd-prev-cont span{color: #6E8C9B; font-size: 11px; display: block; padding: 2px;}
	select option.opt-checked{color: #808080; font-weight: bold; background-color: #F2F6F8;}
	div.mlsd-size-cont{text-align: center; color: #808080;}
</style>
</HEAD>
<BODY style="margin:0px !important;">
<form name="ml_item_form" action="/bitrix/admin/fileman_medialib.php?action=edit_item&<?=bitrix_sessid_get()?>" onsubmit="return parent.oBXMediaLib.EditItemDialogOnsubmit();" method="post" enctype="multipart/form-data"><table class="mlsd-ifrm-tbl">
		<tr><td colSpan="2">
			<div id="mlsd_fname_cont">
				<b><?=GetMessage('ML_FILE')?>:</b><span style="padding: 0px 15px" id="ml_file_name"></span>
			</div>
			<div id="mlsd_load_cont">
				<b><label for="ml_load_file"><?=GetMessage('ML_FILE')?>:</label></b>
				<input id="ml_load_file" type="file" name="load_file" style="margin-left: 15px; width:180px;">
			</div>
			<div id="mlsd_select_cont" style="display: none;">
				<b><label for="mlsd_item_path"><?=GetMessage('ML_FILE')?>:</label></b>
				<input type="text" size="25" value="" id="mlsd_item_path" style="margin-left: 15px;  width: 280px;" name="item_path">
				<input type="button" id="mlsd_open_fd" value="..." style="width: 30px;">
			</div>
			<div style="text-align: right; padding-right: 20px;">
			<div style="float: left; text-align: left; margin-top: -2px;">
			<a id="mlsd_fname_change" href="javascript:void(0)" class="mlsd-up-link" title="<?=GetMessage('ML_CHANGE_FILE_TITLE')?>">(<?=GetMessage('ML_CHANGE')?>)</a>
			<a id="mlsd_fname_change_back" href="javascript:void(0)" class="mlsd-up-link" title="<?=GetMessage('ML_CHANGE_UNDO_TITLE')?>">(<?=GetMessage('ML_CHANGE_UNDO')?>)</a>
			</div>
			<a id="mlsd_select_fd" href="javascript:void(0)" class="mlsd-up-link" title="<?=GetMessage('ML_SELECT_FILE_TITLE')?>"><?=GetMessage('ML_SELECT_FILE')?></a>
			<a id="mlsd_select_pc" href="javascript:void(0)" class="mlsd-up-link" title="<?=GetMessage('ML_LOAD_FILE_TITLE')?>" style="display: none;"><?=GetMessage('ML_LOAD_FILE')?></a>
			</div>
		</td></tr>
		<tr><td><b><label for="mlsd_item_name"><?=GetMessage('ML_NAME')?>:</label></b><br /><input type="text" id="mlsd_item_name" name="item_name"/></td>
			<td rowSpan="3" style="padding-top: 10px;">
			<div class="mlsd-prev-cont"><span id="mlsd_no_preview"><?= GetMessage('ML_NO_PREVIEW')?></span><img id="mlsd_item_thumb" src="/bitrix/images/1.gif" /></div>
			<div class="mlsd-size-cont" id="mlsd_item_size"  title="<?=GetMessage('ML_SIZE_IN_PX')?>"></div>
			</td></tr>
		<tr>
			<td style="vertical-align: top;"><label for="mlsd_item_desc"><?=GetMessage('ML_DESC')?>:</label><br />
			<textarea id="mlsd_item_desc" rows="2" cols="26" name="item_desc"></textarea></td>
		</tr>
		<tr><td><label for="mlsd_item_keywords"><?=GetMessage('ML_KEYWORDS')?>:<br /></label><input type="text" id="mlsd_item_keywords" name="item_keywords"/></td></tr>
		<tr><td colSpan="2">
		<div class="mlsd-col-cont">
			<div class="mlsd-col-label"><label for="mlsd_coll_sel"><?=GetMessage('ML_COLLECTIONS')?>:</label></div>
			<div class="mlsd-col-sel"><select title="<?= GetMessage('ML_ADD_COL2ITEM')?>" id="mlsd_coll_sel"><option value="0"><?= GetMessage('ML_COL_SELECT')?></option></select></div>
		</div>
		</td></tr>
	</table>

	<? /* <input type="hidden" name="MAX_FILE_SIZE" value="1000000000">*/?>
	<input id="mlsd_item_collections" type="hidden" name="item_collections" value="">
	<input id="mlsd_item_id" type="hidden" name="id" value="">
	<input id="mlsd_source_type" type="hidden" name="source_type" value="PC">
</form>
</BODY>
</HTML>
<?
	}

	function BuildConfirmDialog($Params)
	{
		?>
		<div id="ml_colfirm_dialog" class="mlsd mlsd-confirm">
			<div id="ml_confd_text" class="ml-confd-text"></div>
			<input id="ml_confd_b1" type="button" value="b1" />
			<input id="ml_confd_b2" type="button" value="b2" />
			<input id="ml_confd_cancel" type="button" value="<?=GetMessage('ML_CANCEL')?>" />
		</div>
		<?
	}

	function BuildViewItemDialog($Params)
	{
		?>
		<div id="mlsd_view_item" class="mlsd"><table class="mlsd-frame"><tr>
		<td class="ml-title-cell">
			<table onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('mlsd_view_item'));"><tr><td style="width: 10px; padding-left: 3px;"><img class="ml-iconkit ml-dd-dot" src="/bitrix/images/1.gif" /></td><td class="ml-diad-title"><?= GetMessage('ML_VIEW_ITEM')?></td><td id="mlsd_viewit_close" class="ml-close" title="<?=GetMessage('ML_CLOSE')?>"><img src="/bitrix/images/1.gif"></td></tr></table>
		</td></tr>
		<tr><td class="ml-content-cell">
			<div id="mlsd_info_cont" class="mlvi-info-cnt">
				<table class="mlvi-info-tbl">
					<tr><td><div class="mlvi-info-name" id="mlvi_info_name"></div></td></tr>
					<tr><td><a id="mlvi_info_link" href="javascript: void(0);" title="<?= GetMessage("ML_DOWNLOAD_LINK_TITLE")?>"><?= GetMessage('ML_DOWNLOAD_LINK')?></a></td></tr>
					<tr><td class="mlvi-new-row">
						<div class="mlvi-info-details" id="mlvi_info_details"></div>
					</td></tr>
					<tr><td class="small-grey" id="mlvi_info_colls">
						<span><?= GetMessage('ML_COLLECTIONS')?>: </span>
					</td></tr>
					<tr>
						<td class="small-grey" ><span><?= GetMessage('ML_KEYWORDS')?>:</span>
						<span id="mlvi_info_keys"></span></td>
					</tr>
					<tr><td class="mlvi-new-row">
						<span style="font-size: 11px !important;"><?= GetMessage('ML_DESC')?>:</span>
						<div class="ml-info-desc" id="mlvi_info_desc"></div>
					</td></tr>
				</table>
			</div>
			<div id="mlsd_item_cont" class="mlvi-img-cnt"><?/*<img id="mlsd_viewit_img" src="/bitrix/images/1.gif" />*/?></div>
		</td></tr>
		<tr><td class="ml-buttons-cell">
			<input id="mlsd_viewit_del" type="button" value="<?=GetMessage('ML_DELETE')?>">
			<input id="mlsd_viewit_edit" type="button" value="<?=GetMessage('ML_EDIT')?>">
			<input id="mlsd_viewit_cancel" type="button" value="<?=GetMessage('ML_CANCEL')?>">
		</td></tr>
		</table>
		</div>
		<?
	}

	function BuildChangeType($Params)
	{
		?>
		<div id="mlsd_change_type" class="mlsd"><table class="mlsd-frame"><tr>
		<td class="ml-title-cell">
			<table onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('mlsd_change_type'));"><tr><td style="width: 10px; padding-left: 3px;"><img class="ml-iconkit ml-dd-dot" src="/bitrix/images/1.gif" /></td><td class="ml-diad-title"><?= GetMessage('ML_CHANGE_TYPE_DIALOG')?></td><td id="mlsd_chtype_close" class="ml-close" title="<?=GetMessage('ML_CLOSE')?>"><img src="/bitrix/images/1.gif"></td></tr></table>
		</td></tr>
		<tr><td class="ml-content-cell">
			<table class="mlsd-fields-tbl">
				<tr>
					<td><b><?=GetMessage('ML_CHOOSE_TYPE')?>:</b></td>
					<td><select id="mlsd_chtype_type" style="width: 190px;"><option value="none">- <?= GetMessage('ML_COL_SELECT')?> -</option></select></td>
				</tr>
				<tr>
					<td><?=GetMessage('ML_PLACE')?>:</td>
					<td><select id="mlsd_chtype_parent" style="width: 190px;"><option value="0"><?= GetMessage('ML_UPPER_LEVEL')?></option></select></td>
				</tr>
			</table>
		</td></tr>
		<tr><td class="ml-buttons-cell">
			<input id="mlsd_chtype_save" type="button" value="<?=GetMessage('ML_SAVE')?>">
			<input id="mlsd_chtype_cancel" type="button" value="<?=GetMessage('ML_CANCEL')?>">
		</td></tr>
		</table>
		</div>
		<?
	}

	function ShowJS()
	{
		?>
		BX.loadCSS("/bitrix/js/fileman/medialib/medialib.css");
		if (!window.jsUtils && top.jsUtils)
			window.jsUtils = top.jsUtils;
		if (!window.jsUtils)
			BX.loadScript('/bitrix/js/main/utils.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/utils.js')?>');

		if (!window.CHttpRequest && top.CHttpRequest)
			window.CHttpRequest = top.CHttpRequest;
		if (!window.CHttpRequest)
			BX.loadScript('/bitrix/js/main/admin_tools.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/admin_tools.js')?>');

		if (!window.jsAjaxUtil && top.jsAjaxUtil)
			window.jsAjaxUtil = top.jsAjaxUtil;
		if (!window.jsAjaxUtil)
			BX.loadScript('/bitrix/js/main/ajax.js?v=<?=@filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/main/ajax.js')?>');
		<?
	}

	function GetCollections(&$exParams)
	{
		$bCountPermissions = isset($exParams['bCountPermissions']) && $exParams['bCountPermissions'] === true;
		$exParams['arCountPerm'] = array('new_col' => 0, 'edit' => 0, 'del' => 0, 'new_item' => 0, 'edit_item' => 0, 'del_item' => 0, 'access' => 0);

		?>window.MLCollections = [<?
		$arCol = CMedialibCollection::GetList(array('arFilter' =>
			array(
				'ACTIVE' => 'Y',
				'TYPES' => $exParams['types']
			)
		));
		$commonEdit = false;
		$commonItemEdit = false;
		$arResCol = array();
		for ($i = 0, $l = count($arCol); $i < $l; $i++)
		{
			if (!CMedialibCollection::IsViewable($arCol[$i], $arCol))
				continue;

			$id = $arCol[$i]['ID'];
			$arCol[$i]['PERMISSIONS'] = array(
				'new_col' => CMedialib::CanDoOperation('medialib_new_collection', $arCol[$i]['ID']),
				'edit' => CMedialib::CanDoOperation('medialib_edit_collection', $arCol[$i]['ID']),
				'del' => CMedialib::CanDoOperation('medialib_del_collection', $arCol[$i]['ID']),
				'new_item' => CMedialib::CanDoOperation('medialib_new_item', $arCol[$i]['ID']),
				'edit_item' => CMedialib::CanDoOperation('medialib_edit_item', $arCol[$i]['ID']),
				'del_item' => CMedialib::CanDoOperation('medialib_del_item', $arCol[$i]['ID']),
				'access' => CMedialib::CanDoOperation('medialib_access', $arCol[$i]['ID'])
			);

			$accStr = '';
			foreach($exParams['arCountPerm'] as $key => $el)
			{
				if ($bCountPermissions)
					$exParams['arCountPerm'][$key] += intVal($arCol[$i]['PERMISSIONS'][$key]);
				$accStr .= $key.": '".$arCol[$i]['PERMISSIONS'][$key]."', ";
			}
			$accStr = rtrim($accStr, ' ,');
			?>
{
	id: <?= $arCol[$i]['ID']?>,
	name: '<?= CMedialib::Escape($arCol[$i]['NAME'])?>',
	desc: '<?= CMedialib::Escape($arCol[$i]['DESCRIPTION'])?>',
	date: '<?= $arCol[$i]['DATE_UPDATE']?>',
	keywords: '<?= CMedialib::Escape($arCol[$i]['KEYWORDS'])?>',
	parent: <?= intVal($arCol[$i]['PARENT_ID']) > 0 ? intVal($arCol[$i]['PARENT_ID']) : '0'?>,
	access: {<?= $accStr?>},
	type: '<?= $arCol[$i]['ML_TYPE']?>'
}
			<?
			if ($i != $l - 1)
				echo ',';
			$arResCol[] = $arCol[$i];
		}
		?>];<?
		return $arResCol;
	}

	function DelCollection($id, $arIds = array())
	{
		if (!CMedialib::CanDoOperation('medialib_del_collection', $id))
			return false;

		for($i = 0, $l = count($arIds); $i < $l; $i++)
		{
			if (CMedialib::CanDoOperation('medialib_del_collection', $arIds[$i]))
				CMedialibCollection::Delete($arIds[$i], false);
		}

		return CMedialibCollection::Delete($id);
	}

	function EditCollection($Params)
	{
		if ($Params['id'] && !CMedialib::CanDoOperation('medialib_edit_collection', $Params['id']) ||
			!$Params['id'] && !CMedialib::CanDoOperation('medialib_new_collection', $Params['parent']))
			return;

		return CMedialibCollection::Edit(array(
			'arFields' => array(
				'ID' => $Params['id'],
				'NAME' => $Params['name'],
				'DESCRIPTION' => $Params['desc'],
				'OWNER_ID' => $GLOBALS['USER']->GetId(),
				'PARENT_ID' => $Params['parent'],
				'KEYWORDS' => $Params['keywords'],
				'ACTIVE' => "Y",
				'ML_TYPE' => $Params['type']
			)
		));
	}

	function EditItem($Params)
	{
		$bOpName = $Params['id'] ? 'medialib_edit_item' : 'medialib_new_item';
		$arCols_ = explode(',', $Params['item_collections']);
		$arCols = array();
		for ($i = 0, $l = count($arCols_); $i < $l; $i++)
		{
			if (intVal($arCols_[$i]) > 0 && CMedialib::CanDoOperation($bOpName, $arCols_[$i])) // Check access
				$arCols[] = intVal($arCols_[$i]);
		}

		if (count($arCols) > 0)
		{
			if ($Params['source_type'] == 'PC')
				$Params['path'] = false;
			else if($Params['source_type'] == 'FD')
				$Params['file'] = false;

			$res = CMedialibItem::Edit(array(
				'file' => $Params['file'],
				'path' => $Params['path'],
				'arFields' => array(
					'ID' => $Params['id'],
					'NAME' => $Params['name'],
					'DESCRIPTION' => $Params['desc'],
					'KEYWORDS' => $Params['keywords']
				),
				'arCollections' => $arCols
			));

			if ($res):

			if (!isset($res['DATE_UPDATE']) && isset($res['TIMESTAMP_X']))
				$res['DATE_UPDATE'] = $res['TIMESTAMP_X'];
			?>
			<script>
			top.bx_req_res = {
				id: <?=intVal($res['ID'])?>,
				name: '<?= CMedialib::Escape($res['NAME'])?>',
				desc: '<?= CMedialib::Escape($res['DESCRIPTION'])?>',
				keywords: '<?= CMedialib::Escape($res['KEYWORDS'])?>',
				<?if (isset($res['FILE_NAME'])):?>file_name: '<?= CMedialib::Escape($res['FILE_NAME'])?>',<?endif;?>
				<?if (isset($res['DATE_UPDATE'])):?>date_mod: '<?= CMedialib::GetUsableDate($res['DATE_UPDATE'])?>',<?endif;?>
				<?if (isset($res['FILE_SIZE'])):?>file_size: '<?= CMedialib::GetUsableSize($res['FILE_SIZE'])?>',<?endif;?>
				<?if (isset($res['THUMB_PATH'])):?>thumb_path: '<?= CMedialib::Escape($res['THUMB_PATH'])?>',<?endif;?>
				<?if (isset($res['PATH'])):?>path: '<?= CMedialib::Escape($res['PATH'])?>',<?endif;?>
				<?if (isset($res['TYPE'])):?>type: '<?= $res['TYPE']?>',<?endif;?>
				height: <?= ($res['HEIGHT'] ? $res['HEIGHT'] : '0')?>,
				width: <?= ($res['WIDTH'] ? $res['WIDTH'] : '0')?>
			};

			top._ml_items_colls = [<?
			for ($i = 0, $l = count($arCols); $i < $l; $i++)
				echo $arCols[$i].($i != $l - 1 ? ',' : '');
			?>];
			</script>
			<? else: ?>
			<script>top.bx_req_res = false;</script>
			<?endif;
		}
	}

	function GetCollectionTree($Params = array())
	{
		$arColTree = array();
		$arColTemp = array();
		$Collections = array();
		$arCol = CMedialibCollection::GetList(array('arFilter' => array('ACTIVE' => 'Y')));
		$iter = 0;

		for ($i = 0, $l = count($arCol); $i < $l; $i++)
		{
			if (isset($Params['CheckAccessFunk']) && !call_user_func($Params['CheckAccessFunk'], $arCol[$i]['ID']))
				continue;

			if (!CMedialib::_buildCollection($arCol[$i], $i, $arColTree, $Collections, $Params))
				$arColTemp[] = array($arCol[$i], $i);
		}

		while(count($arColTemp) > 0 && $iter < 50)
		{
			$newAr = array();
			for ($i = 0, $l = count($arColTemp); $i < $l; $i++)
			{
				if (isset($Params['CheckAccessFunk']) && !call_user_func($Params['CheckAccessFunk'], $arCol[$i]['ID']))
					continue;

				if (!CMedialib::_buildCollection($arColTemp[$i][0], $arColTemp[$i][1], $arColTree, $Collections, $Params))
					$newAr[] = $arColTemp[$i];
			}
			$arColTemp = $newAr;
			$iter++;
		}

		if ($Params['checkByType'] && $Params['typeId'] > 0)
		{
			$arType = CMedialib::GetTypeById($Params['typeId']);
			if ($arType)
			{
				foreach ($Collections as $id => $col)
				{
					// Del collection escription if it has another type
					if (!CMedialib::CompareTypesEx($Collections[$id]['ML_TYPE'], $arType))
						unset($Collections[$id]);
				}
			}
		}

		return array('arColTree' => $arColTree, 'Collections' => $Collections);
	}

	function _buildCollection($Col, $ind, &$arColTree, &$Collections, $Params = array())
	{
		if ($Params['CHECK_ACCESS'] === true && !CMedialib::CanDoOperation('medialib_view_collection', $Col['ID']))
			return true;

		if (!$Col['PARENT_ID']) // Root element
			$arColTree[] = array('id' => $Col['ID'], 'child' => array());
		else if ($Collections[$Col['PARENT_ID']])
			CMedialib::_findChildInColTree($arColTree, $Col['PARENT_ID'], $Col['ID']);
		else
			return false;

		$Collections[$Col['ID']] = $Col;
		return true;
	}

	function _findChildInColTree(&$arr, $id, $colId)
	{
		for ($i = 0, $l = count($arr); $i < $l; $i++)
		{
			if ($arr[$i]['id'] == $id)
			{
				$arr[$i]['child'][] = array('id' => $colId, 'child' => array());
				return true;
			}
			else if (count($arr[$i]['child']) > 0)
			{
				if (CMedialib::_findChildInColTree($arr[$i]['child'], $id, $colId))
					return true;
			}
		}
		return false;
	}

	function _BuildCollectionsSelectOptions($Collections = false, $arColTree = false, $level = 0, $selected = false)
	{
		if ($Collections === false && $arColTree === false)
		{
			$res = CMedialib::GetCollectionTree();
			$Collections = $res['Collections'];
			$arColTree = $res['arColTree'];
		}

		$str = '';
		for ($i = 0, $l = count($arColTree); $i < $l; $i++)
		{
			//if ($type !== false && )
			$col = $Collections[$arColTree[$i]['id']];
			if (!is_array($col))
				continue;
			$html = str_repeat(" . ", $level);
			$s = ($selected !== false && $selected == $arColTree[$i]['id']) ? ' selected' : '';
			$str .= '<option value="'.$arColTree[$i]['id'].'"'.$s.'>'.$html.htmlspecialcharsex($col['NAME']).'</option>';

			if (count($arColTree[$i]['child']))
				$str .= CMedialib::_BuildCollectionsSelectOptions($Collections, $arColTree[$i]['child'], $level + 1, $selected);
		}
		return $str;
	}

	function GetItems($Params)
	{
		$arCollections = array();
		if (!CMedialib::CanDoOperation('medialib_view_collection', $Params['collectionId']))
			return false;

		if (isset($Params['collectionId']) && $Params['collectionId'] > 0)
			$arCollections[] = $Params['collectionId'];

		$arItems = CMedialibItem::GetList(array(
			'arCollections' => $arCollections
		));

		?>
		<script>
		window.MLItems[<?=$Params['collectionId']?>] = [<?
		for ($i = 0, $l = count($arItems); $i < $l; $i++)
		{
		?>
		{
			id: <?=intVal($arItems[$i]['ID'])?>,
			name: '<?= CMedialib::Escape($arItems[$i]['NAME'])?>',
			desc: '<?= CMedialib::Escape($arItems[$i]['DESCRIPTION'])?>',
			keywords: '<?= CMedialib::Escape($arItems[$i]['KEYWORDS'])?>',
			file_name: '<?= CMedialib::Escape($arItems[$i]['FILE_NAME'])?>',
			date_mod: '<?= CMedialib::GetUsableDate($arItems[$i]['DATE_UPDATE2'])?>',
			height: <?= ($arItems[$i]['HEIGHT'] ? $arItems[$i]['HEIGHT'] : '0')?>,
			width: <?= ($arItems[$i]['WIDTH'] ? $arItems[$i]['WIDTH'] : '0')?>,
			file_size: '<?= CMedialib::GetUsableSize($arItems[$i]['FILE_SIZE'])?>',
			thumb_path: '<?= CMedialib::Escape($arItems[$i]['THUMB_PATH'])?>',
			path: '<?= CMedialib::Escape($arItems[$i]['PATH'])?>',
			type: '<?= $arItems[$i]['TYPE']?>'
		}
		<?
			if ($i != $l - 1)
				echo ',';
		}
		?>];
		</script>
		<?
	}

	function DelItem($id, $bCurrent = false, $colId = false)
	{
		return CMedialibItem::Delete($id, $bCurrent, $colId);
	}

	function DeleteThumbnails()
	{
		CFileman::DeleteEx(BX_PERSONAL_ROOT."/tmp/medialibrary");
	}

	function GetItemCollectionList($Params)
	{
		if(!CMedialib::CanDoOperation('medialib_view_collection', 0))
			return false;

		$ar = CMedialibItem::GetItemCollections($Params);
		?>
		<script>
		window._ml_items_colls = [<?
		for ($i = 0, $l = count($ar); $i < $l; $i++)
			echo $ar[$i].($i != $l - 1 ? ',' : '');
		?>];
		</script>
		<?
	}

	function SaveUserSettings($Params)
	{
		if ($GLOBALS["USER"]->IsAuthorized())
			CUserOptions::SetOption("fileman", "medialib_user_set", intVal($Params['width']).','.intVal($Params['height']).','.intVal($Params['coll_id']));
	}

	function SaveAccessPermissions($colId, $arTaskPerm)
	{
		global $DB;
		$DB->Query("DELETE FROM b_group_collection_task WHERE COLLECTION_ID=".intVal($colId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		foreach($arTaskPerm as $group_id => $task_id)
		{
			$arInsert = $DB->PrepareInsert("b_group_collection_task", array("GROUP_ID" => $group_id, "TASK_ID" => $task_id, "COLLECTION_ID" => intVal($colId)));
			$strSql = "INSERT INTO b_group_collection_task(".$arInsert[0].") VALUES(".$arInsert[1].")";
			$DB->Query($strSql , false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
	}

	function MultiActionDelete($Params = array())
	{
		global $DB;

		if (count($Params['Cols']) > 0) // Del collections
		{
			$strCols = "0";
			for($i = 0, $l = count($Params['Cols']); $i < $l; $i++)
			{
				$colId = $Params['Cols'][$i];
				if (CMedialib::CanDoOperation('medialib_del_collection', $colId)) // Access
					$strCols .= ",".IntVal($colId);
			}

			if ($strCols != "0")
			{
				$strSql = "DELETE FROM b_medialib_collection WHERE ID in (".$strCols.")";
				$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

				$strSql = "DELETE FROM b_medialib_collection_item WHERE COLLECTION_ID in (".$strCols.")";
				$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}
		}

		if (count($Params['Items']) > 0) // Del items
		{
			foreach($Params['Items'] as $colId => $arItems)
			{
				if (!CMedialib::CanDoOperation('medialib_del_item', $colId)) // Access
					return false;

				$strItems = "0";
				for($i=0; $i < count($arItems); $i++)
					$strItems .= ",".IntVal($arItems[$i]);

				$strSql = "DELETE FROM b_medialib_collection_item WHERE ITEM_ID IN (".$strItems.") AND COLLECTION_ID=".$colId;
				$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}
		}

		CMedialibItem::DeleteEmpty(); // Del all items which are absent in 'b_medialib_collection_item'
		return true;
	}

	function ShowBrowseButton($Params = array())
	{
		$value = isset($Params['value']) ? $Params['value'] : '...';
		$inputId = isset($Params['id']) ? $Params['id'] : '';
		$title = isset($Params['title']) ? $Params['title'] : '';
		$event = $Params['event'];
		$mode = isset($Params['mode']) ? $Params['mode'] : '';

		if (!isset($Params['useMLDefault']))
			$useMLDefault = COption::GetOptionString('fileman', "ml_use_default", true);
		else
			$useMLDefault = $Params['useMLDefault'];

		if ($mode == 'file_dialog' || COption::GetOptionString('fileman', "use_medialib", "Y") == "N" || !CMedialib::CanDoOperation('medialib_view_collection', 0))
			$mode = 'file_dialog';
		else if ($mode == 'medialib' || !$GLOBALS["USER"]->CanDoOperation('fileman_view_file_structure'))
			$mode = 'medialib';
		else
			$mode = 'select';

		if ($Params['bReturnResult'])
			ob_start();

		if ($mode == 'medialib' || $mode == 'select')
		{
			$arMLConfig = $Params['MedialibConfig'];
			if (!isset($arMLConfig['event']))
				$arMLConfig['event'] = 'BXOpenMLEvent';
			CMedialib::ShowDialogScript($arMLConfig);
		}

		if ($mode == 'medialib')
		{
			$title = isset($Params['title']) ? $Params['title'] : GetMessage('ML_BR_BUT_ML_TITLE');
			?>
			<input  id="<?= $inputId?>" style="display: none;"/>
			<input type="button" value="<?= $value?>" title="<?= $title?>" onclick="<?= $arMLConfig['event']?>();"/>
			<?
		}
		elseif ($mode == 'file_dialog')
		{
			$title = isset($Params['title']) ? $Params['title'] : GetMessage('ML_BR_BUT_FD_TITLE');
			?><input type="button" value="<?= $value?>" id="<?= $inputId?>" title="<?= $title?>" /><?
		}
		else
		{
			$cid = 'bxmlbut'.$inputId;
		?>
<style>
div.bx-ml-pnbutton{float:left; cursor: pointer; width: 25px; height: 21px; margin:-1px 0 0 2px;}
div.bx-ml-pnbutton div.bx-pn1{background: url(/bitrix/images/fileman/medialib/browse.gif); width: 14px; height: 21px; float: left;}
div.bx-ml-pnbutton div.bx-pn2{background: url(/bitrix/images/fileman/medialib/browse.gif) -14px 0; width: 10px; height: 21px; float: left;}
 div.bx-ml-pnbutton div.bx-pressed{background: url(/bitrix/images/fileman/medialib/browse.gif) -39px 0;}
 .bxml-empty-icon{height: 22px !important; width: 20px !important;}
</style>

<script>
if (!window.<?= $cid?>_menu)
{
	window.<?= $cid?>_menu = new BX.CMenu();
	function <?= $cid?>_onclick(pEl){
		window.<?= $cid?>_menu.ShowMenu(pEl, [
		{ICONCLASS : 'bxml-empty-icon', DEFAULT: <?= $useMLDefault ? 'true' : 'false'?>, TEXT : '<?= addslashes(GetMessage('ML_BR_BUT_ML'))?>', TITLE: '<?= addslashes(GetMessage('ML_BR_BUT_ML_TITLE'))?>', ONCLICK: '<?= $arMLConfig['event']?>();'},
		{ICONCLASS : 'bxml-empty-icon', DEFAULT: <?= $useMLDefault ? 'false' : 'true'?>, TEXT : '<?= addslashes(GetMessage('ML_BR_BUT_FD'))?>',TITLE: '<?= addslashes(GetMessage('ML_BR_BUT_FD_TITLE'))?>', ONCLICK: '<?= $event?>();'}
	]);}
}
</script>

<div class="bx-ml-pnbutton">
<div class="bx-pn1" title="<?= GetMessage('ML_BR_BUT_ML_TITLE')?>" onclick="<?= $useMLDefault ? $arMLConfig['event'] : $event?>();"></div>
<div class="bx-pn2" title="<?= GetMessage('ML_BR_BUT_SEL')?>" onclick="<?= $cid?>_onclick(this);"></div>
</div>
		<?
		}

		if ($Params['bReturnResult'])
		{
			$s = ob_get_contents();
			ob_end_clean();
			return $s;
		}
	}

	function GetUsableSize($size = 0)
	{
		$size = intVal($size);
		if ($size < 1024)
			return $size." ".GetMessage('ML_BYTE');

		$size = round($size / 1024);
		if ($size < 1024)
			return $size." K".GetMessage('ML_BYTE');

		$size = round($size / 1024);
		if ($size < 1024)
			return $size." M".GetMessage('ML_BYTE');

		return $size;
	}

	function GetUsableDate($date = '')
	{
		return ConvertDateTime($date, "DD.MM.YYYY HH:MI");
	}

	function GetMediaExtentions($bStr = true)
	{
		$strExt = COption::GetOptionString('fileman', "ml_media_available_ext", CMedialib::GetDefaultMediaExtentions());

		$arExt_ = explode(',', $strExt);

		$arTypes = CMedialib::GetTypes();
		for($i = 0, $l = count($arTypes); $i < $l; $i++)
			$arExt_ = array_merge($arExt_, explode(',', $arTypes[$i]["ext"]));

		$arExt = array();
		for ($i = 0, $l = count($arExt_); $i < $l; $i++)
		{
			$ext = strtolower(trim($arExt_[$i], ' .'));
			if (strlen($ext) > 0 && !in_array($ext, $arExt))
				$arExt[] = $ext;
		}

		if ($bStr)
			return implode(",", $arExt);

		return $arExt;
	}

	function GetDefaultMediaExtentions()
	{
		return 'jpg,jpeg,gif,png,flv,mp4,wmv,wma,mp3,ppt';
	}

	function CheckFileExtention($strPath = '', $arExt = false)
	{
		if (!$arExt)
			$arExt = CMedialib::GetMediaExtentions(false);
		$ext = strtolower(CFileman::GetFileExtension($strPath));
		return in_array($ext, $arExt);
	}

	function Escape($str, $bHtmlSpCh = true)
	{
		return CUtil::JSEscape($str);

		if (strlen($str) <= 0)
			return $str;

		if ($bHtmlSpCh)
			$str = htmlspecialcharsex($str);

		$str = str_replace("script>","script_>", $str);
		$str = str_replace("\r","",$str);
		$str = str_replace("\n","\\n",$str);
		$str = str_replace("'","\'",$str);
		$str = str_replace("\"","\\\"",$str);

		return $str;
	}

	function SearchItems($Params)
	{
		if (!CModule::IncludeModule("search"))
			return;

		$arQuery = array_keys(stemming($Params['query'], LANGUAGE_ID));
		$arItems = CMedialibItem::Search($arQuery, $Params['types']);
?>
<script>
window.MLSearchResult = [
<?
		for ($i = 0, $l = count($arItems); $i < $l; $i++)
		{
?>
{
	id: <?=intVal($arItems[$i]['ID'])?>,
	name: '<?= CMedialib::Escape($arItems[$i]['NAME'])?>',
	desc: '<?= CMedialib::Escape($arItems[$i]['DESCRIPTION'])?>',
	keywords: '<?= CMedialib::Escape($arItems[$i]['KEYWORDS'])?>',
	file_name: '<?= CMedialib::Escape($arItems[$i]['FILE_NAME'])?>',
	height: <?= ($arItems[$i]['HEIGHT'] ? $arItems[$i]['HEIGHT'] : '0')?>,
	width: <?= ($arItems[$i]['WIDTH'] ? $arItems[$i]['WIDTH'] : '0')?>,
	file_size: '<?= CMedialib::GetUsableSize($arItems[$i]['FILE_SIZE'])?>',
	date_mod: '<?= CMedialib::GetUsableDate($arItems[$i]['DATE_UPDATE2'])?>',
	thumb_path: '<?= CMedialib::Escape($arItems[$i]['THUMB_PATH'])?>',
	path: '<?= CMedialib::Escape($arItems[$i]['PATH'])?>',
	type: '<?= $arItems[$i]['TYPE']?>',
	perm: {edit: <?= $arItems[$i]['perm']['edit'] ? 'true' : 'false'?>, del: <?= $arItems[$i]['perm']['del'] ? 'true' : 'false'?>},
	collections: <?= count($arItems[$i]['collections']) == 1 ? "['".$arItems[$i]['collections'][0]."']" : CUtil::PhpToJSObject($arItems[$i]['collections'])?>

}<?
			if ($i != $l - 1)
				echo ",\n";
		}
?>
];
</script>
<?
	}

	/*
		$strInputName //��� �������� ����������
		$strImageID // ������������� ����� ��� ���� � ����� �� ����� �����
		$showInfo => array(
			"IMAGE" => "Y",
			"MAX_SIZE" => array("W" => ww, "H" => hh)
			"IMAGE_POPUP" => "Y",
			"PATH" => "Y",
			"DIMENSIONS" => "Y",
			"FILE_SIZE" => "Y",
		),
		$fileInput => array( //���� false, �� �������� � ���������� �� ������������.
			"NAME" => "...", //��� ��� ������ ���� ����. ��� �������� � �����. �� ��������� ����� $strInputName
			["ID" => "...",] //������������� ������. �� ��������� ����� $strInputName."_file" � ������� ���� �������� ����� a-zA-z0-9_ �� �������������.
			["SIZE" => NN,] // �������������� ��������, �� ��������� 35
			["SHOW_INFO" => "Y",] //���������� ���������� � ����������� ��� ���, �� ��������� �� ����������
			["LABEL" => "������� � ������"],
		),
		$servInput => array( //���� false, �� ����� ����� �� ������� �� ������������. �� ��������� ����� $strInputName
			"NAME" => "...", //��� ��� ������ ���� �����. ��� ������ ����� �� �������.
			["ID" => "...",] //������������� ������. �� ��������� ����� $strInputName."_serv" � ������� ���� �������� ����� a-zA-z0-9_ �� �������������.
			["SIZE" => NN,] // �������������� ��������, �� ��������� 35
			["SHOW_INFO" => "Y",] //���������� ���������� � ����������� ��� ���, �� ��������� �� ����������
			["LABEL" => "������� � ������"],
		),
		$pathInput => array( //���� false, �� ����� ���������� �� ������������.
			["NAME" => "NNN",] //��� ������ ���� �����. ��� ������ �� ����� ����������. �� ��������� ����� $strInputName
			["ID" => "...",] //������������� ������. �� ��������� ����� $strInputName."_path" � ������� ���� �������� ����� a-zA-z0-9_ �� �������������.
			["SIZE" => NN,] // �������������� ��������, �� ��������� 35
			["LABEL" => "������� � ������"],
		),
		$descInput => array( //���� false, �� ���� �������� �� ���������.
			["NAME" => "NNN",] //��� ������ ���� �����. �������� �����. �� ��������� ����� $strInputName."_descr"  ��� ����� �������� �������.
			["SIZE" => NN,] // �������������� ��������, �� ��������� 35
			["VALUE" => "...",] // �������� ��� �������� �����. ���� �� ������, �� ����� ����� �� $strImageID
			["LABEL" => "������� � ������"],
		),
		$delInput => array( //���� false, �� ������ �������� �� ���������.
			["NAME" => "NNN",] //��� ������ ���� �������. ������ �������� �����.
					// �� ��������� ����� $strInputName."_del" ��� ����� �������� �������.
			["LABEL" => "������� � ������"],
		),
		$scaleIcon => array( //���� false, �� ������ � ���������� �������� �� �����
			"SCALE" => Y|N // Y - ����� �������� ������ ��������������� N - ������ ����. ��������
			"WIDTH" => xxx // ���������� ��� ����� ��� �������
			"HEIGHT" => yyy // ���������� ��� ����� ��� �������
		),
	*/
	function InputFile(
		$strInputName,
		$strImageID = "",
		$showInfo = false,
		$fileInput = false,
		$servInput = false,
		$pathInput = false,
		$descInput = false,
		$delInput = false,
		$scaleIcon = false,
		$cloudInput = false
	)
	{
		global $USER;
		$io = CBXVirtualIo::GetInstance();

		$arFile = CFile::GetFileArray($strImageID);
		//Check if not ID but file path was given
		if(!is_array($arFile))
		{
			$strFilePath = $_SERVER["DOCUMENT_ROOT"].$strImageID;
			if($io->FileExists($strFilePath))
			{
				$flTmp = $io->GetFile($strFilePath);
				$arFile = array(
					"PATH" => $strImageID,
					"FILE_SIZE" => $flTmp->GetFileSize(),
					"DESCRIPTION" => "",
				);

				$arImageSize = CFile::GetImageSize($io->GetPhysicalName($strFilePath));
				if(is_array($arImageSize))
				{
					$arFile["WIDTH"] = $arImageSize[0];
					$arFile["HEIGHT"] = $arImageSize[1];
				}
			}
		}

		$tabCount = 0;
		$arTabs = array();

		if(is_array($fileInput))
		{
			if(!array_key_exists("NAME", $fileInput))
				$fileInput["NAME"] = $strInputName;
			if(!array_key_exists("ID", $fileInput))
				$fileInput["ID"] = preg_replace("/[^a-z0-9_]/i", "_", $fileInput["NAME"])."_file";
			if(!array_key_exists("SIZE", $fileInput) || intval($fileInput["SIZE"]) <= 0)
				$fileInput["SIZE"] = 35;
			else
				$fileInput["SIZE"] = intval($fileInput["SIZE"]);
			if(!array_key_exists("LABEL", $fileInput))
				$fileInput["LABEL"] = GetMessage("ML_IF_SELECT_FILE");
			$arTabs[] = array(
				"DIV" => "file",
				"ICON" => "file",
				"NAME" => GetMessage("ML_IF_TAB_FILE"),
				"TITLE" => GetMessage("ML_IF_TAB_FILE_TITLE"),
			);
		}

		if(is_array($servInput))
		{
			if(!array_key_exists("NAME", $servInput))
				$servInput["NAME"] = $strInputName;
			if(!array_key_exists("ID", $servInput))
				$servInput["ID"] = preg_replace("/[^a-z0-9_]/i", "_", $servInput["NAME"])."_serv";
			if(!array_key_exists("SIZE", $servInput) || intval($servInput["SIZE"]) <= 0)
				$servInput["SIZE"] = 35;
			else
				$servInput["SIZE"] = intval($servInput["SIZE"]);
			if(!array_key_exists("LABEL", $servInput))
			{
				if(is_array($fileInput))
					$servInput["LABEL"] = $fileInput["LABEL"];
				else
					$servInput["LABEL"] = GetMessage("ML_IF_SELECT_FILE");
			}
			$arTabs[] = array(
				"DIV" => "server",
				"ICON" => "server",
				"NAME" => GetMessage("ML_IF_TAB_SERV"),
				"TITLE" => GetMessage("ML_IF_TAB_SERV_TITLE"),
			);
		}

		if(COption::GetOptionString('fileman', "use_medialib", "Y") != "Y")
			$pathInput = false;

		if(is_array($pathInput))
		{
			if(!array_key_exists("NAME", $pathInput))
				$pathInput["NAME"] = $strInputName;
			if(!array_key_exists("ID", $pathInput))
				$pathInput["ID"] = preg_replace("/[^a-z0-9_]/i", "_", $pathInput["NAME"])."_path";
			if(!array_key_exists("SIZE", $pathInput) || intval($pathInput["SIZE"]) <= 0)
				$pathInput["SIZE"] = 35;
			else
				$pathInput["SIZE"] = intval($pathInput["SIZE"]);
			if(!array_key_exists("LABEL", $pathInput))
			{
				if(is_array($fileInput))
					$pathInput["LABEL"] = $fileInput["LABEL"];
				else
					$pathInput["LABEL"] = GetMessage("ML_IF_SELECT_FILE");
			}
			$arTabs[] = array(
				"DIV" => "media",
				"ICON" => "media",
				"NAME" => GetMessage("ML_IF_TAB_MEDIA"),
				"TITLE" => GetMessage("ML_IF_TAB_MEDIA_TITLE"),
			);
		}

		if(
			is_array($cloudInput)
			&& $USER->CanDoOperation("clouds_browse")
			&& CModule::IncludeModule("clouds")
			&& CCloudStorage::HasActiveBuckets()
		)
		{
			if(!array_key_exists("NAME", $cloudInput))
				$cloudInput["NAME"] = $strInputName;
			if(!array_key_exists("ID", $cloudInput))
				$cloudInput["ID"] = preg_replace("/[^a-z0-9_]/i", "_", $cloudInput["NAME"])."_cloud";
			if(!array_key_exists("SIZE", $cloudInput) || intval($cloudInput["SIZE"]) <= 0)
				$cloudInput["SIZE"] = 35;
			else
				$cloudInput["SIZE"] = intval($cloudInput["SIZE"]);
			if(!array_key_exists("LABEL", $cloudInput))
			{
				if(is_array($fileInput))
					$cloudInput["LABEL"] = $fileInput["LABEL"];
				else
					$cloudInput["LABEL"] = GetMessage("ML_IF_SELECT_FILE");
			}
			$arTabs[] = array(
				"DIV" => "clouds",
				"ICON" => "clouds",
				"NAME" => GetMessage("ML_IF_TAB_CLOUDS"),
				"TITLE" => GetMessage("ML_IF_TAB_CLOUDS_TITLE"),
			);
		}
		else
			$cloudInput = false;

		if(is_array($descInput))
		{
			if(!array_key_exists("NAME", $descInput))
			{
				$p = strpos($strInputName, "[");
				if($p > 0)
					$descInput["NAME"] = substr($strInputName, 0, $p)."_descr".substr($strInputName, $p);
				else
					$descInput["NAME"] = $strInputName."_descr";
			}
			$descInput["ID"] = preg_replace("/[^a-z0-9_]/i", "_", $descInput["NAME"]);
			if(!array_key_exists("SIZE", $descInput) || intval($descInput["SIZE"]) <= 0)
				$descInput["SIZE"] = 35;
			else
				$descInput["SIZE"] = intval($descInput["SIZE"]);
			if(!array_key_exists("LABEL", $descInput))
				$descInput["LABEL"] = GetMessage("ML_IF_DESCRIPTION");

			if($arFile)
				$descInput["VALUE"] = $arFile["DESCRIPTION"];
		}

		if(is_array($delInput))
		{
			if(!array_key_exists("NAME", $delInput))
			{
				$p = strpos($strInputName, "[");
				if($p > 0)
					$delInput["NAME"] = substr($strInputName, 0, $p)."_del".substr($strInputName, $p);
				else
					$delInput["NAME"] = $strInputName."_del";
			}
			if(!array_key_exists("LABEL", $delInput))
				$delInput["LABEL"] = GetMessage("ML_IF_DELETE_FILE");
		}

		$bFileExists = false;
		if($arFile)
		{
			if(isset($arFile["PATH"]))
				$sImagePath = $arFile["PATH"];
			else
				$sImagePath = $arFile["SRC"];

			if(
				$arFile["HANDLER_ID"]
				|| (defined("BX_IMG_SERVER") && substr($sImagePath, 0, strlen(BX_IMG_SERVER)) === BX_IMG_SERVER)
				|| $io->FileExists($_SERVER["DOCUMENT_ROOT"].$sImagePath)
			)
			{
				$bFileExists = true;

				$intWidth = intval($arFile["WIDTH"]);
				$intHeight = intval($arFile["HEIGHT"]);
				$intSize = CFile::FormatSize($arFile["FILE_SIZE"]);
			}
		}


		$strImageHTML = "";

		ob_start();

		if($arFile && (is_array($showInfo) || is_array($descInput) || ($bFileExists && is_array($delInput)))):

			ob_start();

			if(is_array($showInfo))
			{
				$showInfo["ID"] = preg_replace("/[^a-z0-9_]/i", "_", $strInputName)."_info";
				if(array_key_exists("MAX_SIZE", $showInfo) && is_array($showInfo["MAX_SIZE"]))
				{
					$iMaxW = intval($showInfo["MAX_SIZE"]["W"]);
					$iMaxH = intval($showInfo["MAX_SIZE"]["H"]);
				}
				else
				{
					$iMaxW = 0;
					$iMaxH = 0;
				}

				if($bFileExists)
				{
					$imgPath = CUtil::addslashes(htmlspecialcharsEx($sImagePath));
					$bImageShowed = false;
					if($showInfo["IMAGE"] == "Y" && $intWidth > 0 && $intHeight > 0)
					{
						if($iMaxW > 0 && $iMaxH > 0) //need to check scale, maybe show actual size in the popup window
						{
							//check for max dimensions exceeding
							if($intWidth > $iMaxW || $intHeight > $iMaxH)
							{
								$coeff = ($intWidth/$iMaxW > $intHeight/$iMaxH? $intWidth/$iMaxW : $intHeight/$iMaxH);
								if($showInfo["IMAGE_POPUP"] == "Y") //show in JS window
								{
									?><tr><td class="img-control-image">
									<?CFile::OutputJSImgShw();?>
									<a title="<?echo GetMessage("FILE_ENLARGE")?>" onclick="ImgShw('<?= $imgPath?>','<?echo $intWidth?>','<?echo $intHeight?>', ''); return false;" href="<?= $imgPath?>" target="_blank"><img border="0" id="<?echo $showInfo["ID"]?>" src="<?= $imgPath?>" width="<?echo intval(roundEx($intWidth/$coeff))?>" height="<?echo intval(roundEx($intHeight/$coeff))?>" /></a>
									</td></tr><?
									$bImageShowed = true;
								}
							}
							else
							{
								?><tr><td class="img-control-image">
								<img id="<?echo $showInfo["ID"]?>" src="<?= $imgPath?>" width="<?echo $intWidth?>" height="<?echo $intHeight?>">
								</td></tr><?
								$bImageShowed = true;
							}
						}
						else
						{
							?><tr><td class="img-control-image">
							<img id="<?echo $showInfo["ID"]?>" src="<?= $imgPath?>" width="<?= $intWidth?>" height="<?= $intHeight?>">
							</td></tr><?
							$bImageShowed = true;
						}
					}

					if(!$bImageShowed)
					{
						?><tr><td class="img-control-image"><div style="text-align:left"><?
						if($showInfo["PATH"] == "Y")
							echo GetMessage("FILE_TEXT").": <a href=\"".htmlspecialchars($sImagePath)."\">".htmlspecialcharsEx($sImagePath)."</a>";
						if($showInfo["DIMENSIONS"] == "Y" && $intWidth>0 && $intHeight>0)
						{
							echo "<br>".GetMessage("FILE_WIDTH").': '.$intWidth;
							echo "<br>".GetMessage("FILE_HEIGHT").': '.$intHeight;
						}
						if($showInfo["FILE_SIZE"] == "Y")
							echo "<br>".GetMessage("FILE_SIZE").': '.$intSize;
						?></div></td></tr><?
					}
				}
				else
				{
					?><tr><td class="img-control-image"><div style="text-align:left"><?
					echo GetMessage("FILE_NOT_FOUND").": ".htmlspecialcharsEx($sImagePath);
					?></td></tr><?
				}
			}

			if(is_array($descInput))
			{
				?><tr><td class="img-control-descr">
				<label><?echo $descInput["LABEL"]?><br>
				<input type="text" name="<?echo htmlspecialchars($descInput["NAME"])?>" id="<?echo htmlspecialchars($descInput["ID"])?>" size="<?echo $descInput["SIZE"]?>" value="<?echo htmlspecialchars($descInput["VALUE"])?>">
				</label>
				</td></tr><?
			}

			if($bFileExists && is_array($delInput))
			{
				?><tr><td class="img-control-del">
				<label><input type="checkbox" name="<?echo htmlspecialchars($delInput["NAME"])?>" value="Y" id="<?echo htmlspecialchars($delInput["NAME"])?>" /> <?echo $delInput["LABEL"]?></label>
				</td></tr><?
			}

			$strImageHTML = ob_get_contents();
			ob_end_clean();
		endif;

		if(is_array($scaleIcon))
		{
			$sHintRows = "";
			if($scaleIcon["SCALE"]=="Y")
			{
				$sHintRows .= '<tr valign="top"><td colspan="2" class="bx-grey">'.GetMessage("ML_IF_SCALE_HINT").'</td>';
				$sHintRows .= '<tr valign="top"><td class="bx-grey" nowrap>'.GetMessage("ML_IF_SCALE_WIDTH").'</td><td nowrap>'.intval($scaleIcon["WIDTH"]).'</td></tr>';
				$sHintRows .= '<tr valign="top"><td class="bx-grey" nowrap>'.GetMessage("ML_IF_SCALE_HEIGHT").'</td><td nowrap>'.intval($scaleIcon["HEIGHT"]).'</td></tr>';
				if(is_array($fileInput))
					$fileInput["SCALE_HINT_HTML"] = '&nbsp;<img id="'.$fileInput["ID"].'_scale" src="/bitrix/images/fileman/medialib/tabs/icon_resized.gif">';
				if(is_array($servInput))
					$servInput["SCALE_HINT_HTML"] = '&nbsp;<img id="'.$servInput["ID"].'_scale" src="/bitrix/images/fileman/medialib/tabs/icon_resized.gif">';
				if(is_array($pathInput))
					$pathInput["SCALE_HINT_HTML"] = '&nbsp;<img id="'.$pathInput["ID"].'_scale" src="/bitrix/images/fileman/medialib/tabs/icon_resized.gif">';
			}
			else
			{
				$sHintRows .= '<tr valign="top"><td colspan="2" class="bx-grey">'.GetMessage("ML_IF_NO_SCALE_HINT").'</td>';
				if(is_array($fileInput))
					$fileInput["SCALE_HINT_HTML"] = '&nbsp;<img id="'.$fileInput["ID"].'_scale" src="/bitrix/images/fileman/medialib/tabs/icon_original.gif">';
				if(is_array($servInput))
					$servInput["SCALE_HINT_HTML"] = '&nbsp;<img id="'.$servInput["ID"].'_scale" src="/bitrix/images/fileman/medialib/tabs/icon_original.gif">';
				if(is_array($pathInput))
					$pathInput["SCALE_HINT_HTML"] = '&nbsp;<img id="'.$pathInput["ID"].'_scale" src="/bitrix/images/fileman/medialib/tabs/icon_original.gif">';
			}
			$sHint = '<table cellspacing="0" border="0" style="font-size:100%">'.$sHintRows.'</table>';
		}
		else
		{
			if(is_array($fileInput))
				$fileInput["SCALE_HINT_HTML"] = '';
			if(is_array($servInput))
				$servInput["SCALE_HINT_HTML"] = '';
			if(is_array($pathInput))
				$pathInput["SCALE_HINT_HTML"] = '';
		}

		if(count($arTabs))
		{
			$tabControl = new CMedialibTabControl(preg_replace("/[^a-z0-9_]/i", "_", $strInputName)."_tab", $arTabs);
			$bFirst = true;
			if(is_array($fileInput))
			{
				$tabControl->BeginTab();
				?><table width="95%" cellpadding="2" cellspacing="2">
				<tr><td colspan="2"><?echo $fileInput["LABEL"]?></td></tr>
				<tr valign="center"><td><input type="file" id="<?echo htmlspecialchars($fileInput["ID"])?>" name="<?echo htmlspecialchars($fileInput["NAME"])?>" size="<?echo $fileInput["SIZE"]?>" <?if(!$bFirst) echo "disabled"?>></td><td align="left" width="100%"><?echo $fileInput["SCALE_HINT_HTML"]?>&nbsp;</td></tr>
				<?if(is_array($descInput) && !$arFile):?>
					<tr><td colspan="2"><?echo $descInput["LABEL"]?></td></tr>
					<tr><td colspan="2"><input type="text" name="<?echo htmlspecialchars($descInput["NAME"])?>" id="<?echo htmlspecialchars($descInput["ID"])?>_file" size="<?echo $descInput["SIZE"]?>" value="<?echo htmlspecialchars($descInput["VALUE"])?>" <?if(!$bFirst) echo "disabled"?>></td></tr>
				<?endif;?>
				</table><?
				if($fileInput["SCALE_HINT_HTML"]):?>
					<script>
					// TODO: use new BX.CHint instead
					window.structHint<?echo $fileInput["ID"]."_scale"?> = new BXHint(
						'<?= CUtil::JSEscape($sHint)?>',
						document.getElementById('<?echo $fileInput["ID"]."_scale"?>')
					);
					</script>
				<?endif;

				$bFirst = false;
				$tabControl->EndTab();
			}

			if(is_array($servInput))
			{
				$tabControl->BeginTab();
				?><table width="100%" cellpadding="2" cellspacing="2">
				<tr><td colspan="3"><?echo $servInput["LABEL"]?></td></tr>
				<tr valign="center"><td><input type="text" id="<?echo htmlspecialchars($servInput["ID"])?>" size="<?echo $servInput["SIZE"]?>" value="<?echo htmlspecialchars($servInput["VALUE"])?>" name="<?echo htmlspecialchars($servInput["NAME"])?>" <?if(!$bFirst) echo "disabled"?>></td><td>
				<input type="button" id="mlsd_<?echo htmlspecialchars($servInput["ID"])?>_open" value="..." style="width: 30px;" OnClick="<?echo htmlspecialchars("inputId = '".CUtil::JSEscape($servInput["ID"])."';serv_OpenML()")?>"></td><td align="left" width="100%"><?echo $servInput["SCALE_HINT_HTML"]?>&nbsp;</td></tr>
				<?if(is_array($descInput) && !$arFile):?>
					<tr><td colspan="3"><?echo $descInput["LABEL"]?></td></tr>
					<tr><td colspan="3"><input type="text" name="<?echo htmlspecialchars($descInput["NAME"])?>" id="<?echo htmlspecialchars($descInput["ID"])?>_serv" size="<?echo $descInput["SIZE"]?>" value="<?echo htmlspecialchars($descInput["VALUE"])?>" <?if(!$bFirst) echo "disabled"?>></td></tr>
				<?endif;?>
				</table><?
				if($fileInput["SCALE_HINT_HTML"]):?>
					<script>
					// TODO: use new BX.CHint instead
					window.structHint<?echo $servInput["ID"]."_scale"?> = new BXHint(
						'<?= CUtil::JSEscape($sHint)?>',
						document.getElementById('<?echo $servInput["ID"]."_scale"?>')
					);
					</script>
				<?endif;
				CAdminFileDialog::ShowScript
				(
					Array(
						"event" => "serv_OpenML",
						"arResultDest" => array("FUNCTION_NAME" => "setServerInputFromDialog"),
						"arPath" => array("SITE" => SITE_ID, "PATH" =>"/upload"),
						"select" => 'F',// F - file only, D - folder only
						"operation" => 'O',
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"allowAllFiles" => true,
						"SaveConfig" => true,
					)
				);
				?><script>
				function setServerInputFromDialog(filename, path, site, title, menu)
				{
					var inp = document.getElementById(inputId);
					if(inp)
						inp.value = path + (path == '/'? '': '/') + filename;
				}
				</script><?
				$bFirst = false;
				$tabControl->EndTab();
			}

			if(is_array($pathInput))
			{
				$tabControl->BeginTab();
				?><table width="100%" cellpadding="2" cellspacing="2">
				<tr><td colspan="3"><?echo $pathInput["LABEL"]?></td></tr>
				<tr valign="center"><td><input type="text" id="<?echo htmlspecialchars($pathInput["ID"])?>" size="<?echo $pathInput["SIZE"]?>" value="" name="<?echo htmlspecialchars($pathInput["NAME"])?>" <?if(!$bFirst) echo "disabled"?>></td><td>
				<input type="button" id="mlsd_<?echo htmlspecialchars($pathInput["ID"])?>_open" value="..." style="width: 30px;" OnClick="<?echo htmlspecialchars("inputId = '".CUtil::JSEscape($pathInput["ID"])."';descrId = '".CUtil::JSEscape($descInput["ID"]."_path")."';media_OpenML()")?>"></td><td align="left" width="100%"><?echo $pathInput["SCALE_HINT_HTML"]?>&nbsp;</td></tr>
				<?if(is_array($descInput) && !$arFile):?>
					<tr><td colspan="3"><?echo $descInput["LABEL"]?></td></tr>
					<tr><td colspan="3"><input type="text" name="<?echo htmlspecialchars($descInput["NAME"])?>" id="<?echo htmlspecialchars($descInput["ID"])?>_path" size="<?echo $descInput["SIZE"]?>" value="<?echo htmlspecialchars($descInput["VALUE"])?>" <?if(!$bFirst) echo "disabled"?>></td></tr>
				<?endif;?>
				</table>
				<?
				if($fileInput["SCALE_HINT_HTML"]):?>
					<script>
					// TODO: use new BX.CHint instead
					window.structHint<?echo $pathInput["ID"]."_scale"?> = new BXHint(
						'<?= CUtil::JSEscape($sHint)?>',
						document.getElementById('<?echo $pathInput["ID"]."_scale"?>')
					);
					</script>
				<?endif;
				$ar = array(
					"event" => "media_OpenML",
					"arResultDest" => array(
						"FUNCTION_NAME" => "setMediaInputFromDialog",
					),
				);
				if(is_array($descInput))
				{
					if($arFile)
						$ar["description_id"] = $descInput["ID"];
					else
						$ar["description_id"] = $descInput["ID"]."_path";
				}
				CMedialib::ShowDialogScript($ar);
				?><script>
				function setMediaInputFromDialog(oItem)
				{
					var inp = document.getElementById(inputId);
					if(inp)
						inp.value = oItem.src;
					var desc = document.getElementById(descrId);
					if(desc)
						desc.value = oItem.name;
				}
				</script><?
				$bFirst = false;
				$tabControl->EndTab();
			}

			if(is_array($cloudInput))
			{
				$tabControl->BeginTab();
				?><table width="100%" cellpadding="2" cellspacing="2">
				<tr><td colspan="3"><?echo $cloudInput["LABEL"]?></td></tr>
				<tr valign="center"><td><input type="text" id="<?echo htmlspecialchars($cloudInput["ID"])?>" size="<?echo $cloudInput["SIZE"]?>" value="" name="<?echo htmlspecialchars($cloudInput["NAME"])?>" <?if(!$bFirst) echo "disabled"?>></td><td>
				<input type="button" value="..." style="width: 30px;" OnClick="jsUtils.OpenWindow('/bitrix/admin/clouds_file_search.php?lang=<?echo LANGUAGE_ID?>&amp;n=<?echo htmlspecialchars($cloudInput["ID"])?>', 600, 500);"></td><td align="left" width="100%"><?echo $cloudInput["SCALE_HINT_HTML"]?>&nbsp;</td></tr>
				<?if(is_array($descInput) && !$arFile):?>
					<tr><td colspan="3"><?echo $descInput["LABEL"]?></td></tr>
					<tr><td colspan="3"><input type="text" name="<?echo htmlspecialchars($descInput["NAME"])?>" id="<?echo htmlspecialchars($descInput["ID"])?>_cloud" size="<?echo $descInput["SIZE"]?>" value="<?echo htmlspecialchars($descInput["VALUE"])?>" <?if(!$bFirst) echo "disabled"?>></td></tr>
				<?endif;?>
				</table><?
				if($fileInput["SCALE_HINT_HTML"]):?>
					<script>
					// TODO: use new BX.CHint instead
					window.structHint<?echo $cloudInput["ID"]."_scale"?> = new BXHint(
						'<?= CUtil::JSEscape($sHint)?>',
						document.getElementById('<?echo $cloudInput["ID"]."_scale"?>')
					);
					</script>
				<?endif;
				$bFirst = false;
				$tabControl->EndTab();
			}

			if($strImageHTML)
			{
				$tabControl->BeginEpilog();
				?><table class="img-control img-control-tab" width="100%" cellpadding="0" cellspacing="0"><tr><td class="img-control-empty-top"><div class="empty" /></td></tr><?
				echo $strImageHTML;
				?><tr><td class="img-control-empty-bottom"><div class="empty" /></td></tr></table><?
				$tabControl->EndEpilog();
			}

			$tabControl->Show();


		}
		else
		{
			if($strImageHTML)
			{
				?><table class="img-control img-control-alone"><tr><td class="img-control-empty-top"><div class="empty" /></td></tr><?
				echo $strImageHTML;
				?><tr><td class="img-control-empty-bottom"><div class="empty" /></td></tr></table><br><?
			}
		}

		if($bImageShowed)
		{
			$sHintRows = "";
			if($showInfo["PATH"] == "Y")
				$sHintRows .= '<tr valign="top"><td class="bx-grey" nowrap>'.htmlspecialcharsEx(GetMessage("FILE_TEXT")).':</td><td nowrap>'.htmlspecialcharsEx($sImagePath).'</td></tr>';
			if($showInfo["DIMENSIONS"] == "Y" && $intWidth>0 && $intHeight>0)
			{
				$sHintRows .= '<tr valign="top"><td class="bx-grey" nowrap>'.htmlspecialcharsEx(GetMessage("FILE_WIDTH")).':</td><td nowrap>'.htmlspecialcharsEx($intWidth).'</td></tr>';
				$sHintRows .= '<tr valign="top"><td class="bx-grey" nowrap>'.htmlspecialcharsEx(GetMessage("FILE_HEIGHT")).':</td><td nowrap>'.htmlspecialcharsEx($intHeight).'</td></tr>';
			}
			if($showInfo["FILE_SIZE"] == "Y")
				$sHintRows .= '<tr valign="top"><td class="bx-grey" nowrap>'.htmlspecialcharsEx(GetMessage("FILE_SIZE")).':</td><td nowrap>'.htmlspecialcharsEx($intSize).'</td></tr>';
			if($sHintRows <> "")
			{
				$sHint = '<table cellspacing="0" border="0" style="font-size:100%">'.$sHintRows.'</table>';
				?>
				<script>
				// TODO: use new BX.CHint instead
				window.structHint<?echo $showInfo["ID"]?> = new BXHint(
					'<?= CUtil::JSEscape($sHint)?>',
					document.getElementById('<?echo $showInfo["ID"]?>'),
					{width:false}
				);
				</script>
				<?
			}
		}

		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

	function GetTypeById($id, $arMLTypes = false)
	{
		if ($arMLTypes === false)
			$arMLTypes = CMedialib::GetTypes();

		for ($i = 0, $l = count($arMLTypes); $i < $l; $i++)
			if ($arMLTypes[$i]['id'] == $id)
				return $arMLTypes[$i];

		return false;
	}

	function GetTypes($arConfigTypes = array(), $bGetEmpties = false)
	{
		global $DB;
		if ($bGetEmpties)
			$q = "SELECT MT.*, MC.ML_TYPE FROM b_medialib_type MT LEFT JOIN b_medialib_collection MC ON (MT.ID=MC.ML_TYPE)";
		else
			$q = "SELECT * FROM b_medialib_type";


		$err_mess = CMedialibCollection::GetErrorMess()."<br>Function: CMedialib::GetTypes<br>Line: ";
		$res = $DB->Query($q, false, $err_mess);
		$arMLTypes = array();
		$arMLTypesInd = array();

		while($arRes = $res->Fetch())
		{
			if ($arMLTypesInd[$arRes["ID"]])
				continue;

			$typeIcon = "/bitrix/images/fileman/medialib/type_".strtolower($arRes["CODE"]).".gif";
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].$typeIcon))
				$typeIcon = "/bitrix/images/fileman/medialib/type_default.gif";

			if (count($arConfigTypes) > 0 && !in_array(strtolower($arRes["CODE"]), $arConfigTypes))
				continue;

			if ($arRes["SYSTEM"] == "Y")
			{
				$arRes["NAME"] = GetMessage("ML_TYPE_".strtoupper($arRes["NAME"]));
				$arRes["DESCRIPTION"] = GetMessage("ML_TYPE_".strtoupper($arRes["DESCRIPTION"]));
			}

			$arMLTypesInd[$arRes["ID"]] = true;

			$arMLTypes[] = array(
				"id" => $arRes["ID"],
				"code" => $arRes["CODE"],
				"name" => $arRes["NAME"],
				"ext" => $arRes["EXT"],
				"system" => $arRes["SYSTEM"] == "Y",
				"desc" => $arRes["DESCRIPTION"],
				"type_icon" => $typeIcon,
				"empty" => !$arRes['ML_TYPE'] && ($arRes["CODE"] != "image" || $arRes["SYSTEM"] != "Y")
			);
		}
		return $arMLTypes;
	}

	function SetTypes($arTypes = array())
	{
		global $DB;

		for ($i = 0, $l = count($arTypes); $i < $l; $i++)
		{
			$arFields = $arTypes[$i];

			$arFields["CODE"] = preg_replace("/[^a-zA-Z0-9_]/i", "", $arFields["CODE"]);
			$arFields["EXT"] = preg_replace("/[^a-zA-Z0-9_\,]/i", "", $arFields["EXT"]);

			if ($arFields["CODE"] == '' || $arFields["EXT"] == '' || $arFields["NAME"] == '')
				continue;

			$id = IntVal($arFields['ID']);
			unset($arFields['ID']);

			if ($arFields['NEW']) // Add
			{
				unset($arFields['NEW']);
				CDatabase::Add("b_medialib_type", $arFields);
			}
			else // Update
			{
				// Edit only non system types
				if ($arFields['SYSTEM'] == 'Y')
					continue;

				$strSql =
					"UPDATE b_medialib_type SET ".
						$DB->PrepareUpdate("b_medialib_type", $arFields).
					" WHERE SYSTEM<>'Y' AND ID=".$id;

				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
	}

	function DelTypes($arIds = array())
	{
		if (count($arIds) == 0)
			return;

		global $DB;
		$strItems = "0";
		for($i = 0, $l = count($arIds); $i < $l; $i++)
			$strItems .= ",".IntVal($arIds[$i]);

		$res = $DB->Query("DELETE FROM b_medialib_type WHERE ID in (".$strItems.")", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	function GetItemViewHTML($itemId)
	{
		$arItem = CMedialibItem::GetList(array('id' => $itemId));
		if (is_array($arItem) && count($arItem) > 0)
		{
			$events = GetModuleEvents("fileman", "OnMedialibItemView");
			$bHandled = false;
			while($arEvent = $events->Fetch())
			{
				$arRes = ExecuteModuleEventEx($arEvent, array($arItem[0]));
				if (!$arRes || !is_array($arRes))
					continue;
				$bHandled = true;
			}
		}

		if (!$bHandled)
		{
			$item = $arItem[0];

			// Default view
			$ext = strtolower(GetFileExtension($item['PATH']));
			$videoExt = array('flv', 'mp4', 'wmv', 'avi');
			$soundExt = array('aac', 'mp3', 'wma');

			if ($item['TYPE'] == 'image' || strpos($item['CONTENT_TYPE'], 'image') !== false)
			{
				// It's image
				$arRes = array(
					"html" => "<img src=\"".htmlspecialcharsex($item['PATH'])."\" width=\"".intVal($item['WIDTH'])."\" height=\"".intVal($item['HEIGHT'])."\" title=\"".htmlspecialcharsex($item['NAME'])."\" />",
					"width" => intVal($item['WIDTH']),
					"height" => intVal($item['HEIGHT'])
				);
			}
			else if (strpos($item['CONTENT_TYPE'], 'video') !== false || in_array($ext, $videoExt))
			{
				global $APPLICATION;
				$item['WIDTH'] = 400;
				$item['HEIGHT'] = 300;

				ob_start();
				$APPLICATION->IncludeComponent(
					"bitrix:player",
					"",
					array(
						"PLAYER_TYPE" => "auto",
						"PATH" => $item['PATH'],
						"WIDTH" => $item['WIDTH'],
						"HEIGHT" => $item['HEIGHT'],
						"FILE_TITLE" => $item['NAME'],
						"FILE_DESCRIPTION" => "",
						//"SKIN_PATH" => "/bitrix/components/bitrix/player/mediaplayer/skins",
						//"SKIN" => "bitrix.swf",
						"WMODE" => "transparent",
						"WMODE_WMV" => "windowless",
						"SHOW_CONTROLS" => "Y",
						"BUFFER_LENGTH" => "3",
						"ALLOW_SWF" => "N"
					),
					false,
					array('HIDE_ICONS' => 'Y')
				);
				$s = ob_get_contents();
				ob_end_clean();

				$arRes = array(
					"html" => $s,
					"width" => $item['WIDTH'],
					"height" => $item['HEIGHT']
				);
			}
			else if (strpos($item['CONTENT_TYPE'], 'audio') !== false || in_array($ext, $soundExt))
			{
				global $APPLICATION;
				$item['WIDTH'] = 300;
				$item['HEIGHT'] = 24;

				ob_start();
				$APPLICATION->IncludeComponent(
					"bitrix:player",
					"",
					array(
						"PROVIDER" => "sound",
						"PLAYER_TYPE" => "auto",
						"PATH" => $item['PATH'],
						"WIDTH" => $item['WIDTH'],
						"HEIGHT" => $item['HEIGHT'],
						"FILE_TITLE" => $item['NAME'],
						"FILE_DESCRIPTION" => "",
						"WMODE" => "transparent",
						"WMODE_WMV" => "windowless",
						"SHOW_CONTROLS" => "Y",
						"BUFFER_LENGTH" => "3",
						"ALLOW_SWF" => "N"
					),
					false,
					array('HIDE_ICONS' => 'Y')
				);
				$s = "<div style='margin-top: 10px;'>".ob_get_contents()."</div>";
				ob_end_clean();
				$arRes = array(
					"html" => $s,
					"width" => $item['WIDTH'],
					"height" => $item['HEIGHT']
				);
			}
		}
?>
<script>
window.bx_req_res = {
	html: '<?= CUtil::JSEscape($arRes['html'])?>',
	width: '<?= intVal($arRes['width'])?>',
	height: '<?= intVal($arRes['height'])?>',
	bReplaceAll: <?= $arRes['bReplaceAll'] === true ? 'true' : 'false'?>
};
</script>
<?
	}

	function ChangeColType($Params)
	{
		if (
			CMedialib::CanDoOperation('medialib_edit_collection', $Params['col']) &&
			CMedialib::CanDoOperation('medialib_edit_collection', $Params['parent']) &&
			$Params['col'] > 0 && $Params['type'] > 0
		)
		{
			$arChild = array();
			for($i = 0, $l = count($Params['childCols']); $i < $l; $i++)
			{
				if (intVal($Params['childCols'][$i]) > 0 &&
				CMedialib::CanDoOperation('medialib_edit_collection', $Params['childCols'][$i]))
					$arChild[] = intVal($Params['childCols'][$i]);
			}
			$Params['childCols'] = $arChild;

			CMedialibCollection::ChangeType($Params);
			?><script>top.bx_req_res = true;</script><?
		}
		else
		{
			?><script>top.bx_req_res = false;</script><?
		}
	}

	function CompareTypesEx($typeMix, $arType)
	{
		if ($typeMix == $arType['id'] || (!$typeMix && $arType['code'] == 'image' && $arType['system']))
			return true;

		return false;
	}
}

class CMedialibCollection
{
	function GetList($Params = array())
	{
		global $DB, $USER;
		$arFilter = $Params['arFilter'];
		$arOrder = isset($Params['arOrder']) ? $Params['arOrder'] : Array('ID' => 'asc');

		static $arFields = array(
			"ID" => Array("FIELD_NAME" => "MLC.ID", "FIELD_TYPE" => "int"),
			"NAME" => Array("FIELD_NAME" => "MLC.NAME", "FIELD_TYPE" => "string"),
			"ACTIVE" => Array("FIELD_NAME" => "MLC.ACTIVE", "FIELD_TYPE" => "string"),
			"DATE_UPDATE" => Array("FIELD_NAME" => "MLC.DATE_UPDATE", "FIELD_TYPE" => "date"),
			"KEYWORDS" => Array("FIELD_NAME" => "MLC.KEYWORDS", "FIELD_TYPE" => "string"),
			"DESCRIPTION" => Array("FIELD_NAME" => "MLC.DESCRIPTION", "FIELD_TYPE" => "string"),
			"OWNER_ID" => Array("FIELD_NAME" => "MLC.OWNER_ID", "FIELD_TYPE" => "int"),
			"PARENT_ID" => Array("FIELD_NAME" => "MLC.PARENT_ID", "FIELD_TYPE" => "int"),
			"ML_TYPE" => Array("FIELD_NAME" => "MLC.ML_TYPE", "FIELD_TYPE" => "string")
		);

		$err_mess = (CMedialibCollection::GetErrorMess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = array();
		$strSqlSearch = "";
		if(is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for($i=0, $l = count($filter_keys); $i<$l; $i++)
			{
				$n = strtoupper($filter_keys[$i]);
				$val = $arFilter[$filter_keys[$i]];
				if(is_string($val)  && strlen($val) <=0 || strval($val)=="NOT_REF")
					continue;
				if ($n == 'ID')
					$arSqlSearch[] = GetFilterQuery("MLC.ID", $val, 'N');
				elseif(isset($arFields[$n]))
					$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val);
			}
		}

		$strOrderBy = '';
		foreach($arOrder as $by=>$order)
			if(isset($arFields[strtoupper($by)]))
				$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order)=='desc'?'desc'.(strtoupper($DB->type)=="ORACLE"?" NULLS LAST":""):'asc'.(strtoupper($DB->type)=="ORACLE"?" NULLS FIRST":"")).',';

		if(strlen($strOrderBy)>0)
			$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if (isset($arFilter['TYPES']) && is_array($arFilter['TYPES']))
		{
			$strTypes = "";
			for($i=0; $i < count($arFilter['TYPES']); $i++)
				$strTypes .= ",".IntVal($arFilter['TYPES'][$i]);
			$strSqlSearch .= "\n AND ML_TYPE in (".trim($strTypes, ", ").")";
		}

		$strSql = "
			SELECT
				MLC.*
			FROM
				b_medialib_collection MLC
			WHERE
				$strSqlSearch
			$strOrderBy";

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$arResult = Array();
		while($arRes = $res->Fetch())
			$arResult[]=$arRes;

		return $arResult;
	}

	function CheckFields($arFields)
	{
		if (!isset($arFields['NAME']) || strlen($arFields['NAME']) <= 0)
			return false;

		/*
		ID int not null auto_increment,
		NAME varchar(255) not null,
		DESCRIPTION text null,
		ACTIVE char(1) not null default 'Y',
		DATE_UPDATE datetime not null,
		OWNER_ID int null,
		PARENT_ID int null,
		SITE_ID char(2) not null,
		KEYWORDS varchar(255) null
		*/
		return true;
	}

	function Edit($Params)
	{
		global $DB;
		$arFields = $Params['arFields'];

		if (!isset($arFields['~DATE_UPDATE']))
			$arFields['~DATE_UPDATE'] = $DB->CurrentTimeFunction();

		if(!CMedialibCollection::CheckFields($arFields))
			return false;

		if (!isset($arFields['ML_TYPE']))
			$arFields['ML_TYPE'] = '';

		$bNew = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		if ($bNew) // Add
		{
			unset($arFields['ID']);
			$ID = CDatabase::Add("b_medialib_collection", $arFields);
		}
		else // Update
		{
			$ID = $arFields['ID'];
			unset($arFields['ID']);
			$strUpdate = $DB->PrepareUpdate("b_medialib_collection", $arFields);
			$strSql =
				"UPDATE b_medialib_collection SET ".
					$strUpdate.
				" WHERE ID=".IntVal($ID);
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;
	}

	function Delete($ID, $bDelEmpty = true)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "DELETE FROM b_medialib_collection WHERE ID=".$ID;
		$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$strSql = "DELETE FROM b_medialib_collection_item WHERE COLLECTION_ID=".$ID;
		$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if ($bDelEmpty)
			CMedialibItem::DeleteEmpty();

		return $z;
	}

	function GetErrorMess()
	{
		return "<br>Class: CMedialibCollection<br>File: ".__FILE__;
	}

	function IsViewable($oCol, $arCol=false)
	{
		if(!$arCol)
			$arCol = CMedialibCollection::GetList(array('arFilter' => array('ACTIVE' => 'Y')));

		if (!CMedialib::CanDoOperation('medialib_view_collection', $oCol['ID']))
			return false;

		$l = count($arCol);
		if ($oCol['PARENT_ID'])
		{
			$parId = $oCol['PARENT_ID'];
			while(intVal($parId) > 0)
			{
				$bFind = false;
				for($i = 0; $i < $l; $i++) // Find parent
				{
					if ($arCol[$i]['ID'] == $parId)
					{
						if (!CMedialib::CanDoOperation('medialib_view_collection', $arCol[$i]['ID']))
							return false;
						$parId = $arCol[$i]['PARENT_ID'];
						$bFind = true;
						break;
					}
				}
				if (!$bFind)
					return false;
			}
		}
		return true;
	}

	function ChangeType($Params)
	{
		global $DB;
		$arFields = array(
			'ML_TYPE' => $Params['type'],
			'PARENT_ID' => $Params['parent']
		);

		$strUpdate = $DB->PrepareUpdate("b_medialib_collection", $arFields);
		$strSql =
			"UPDATE b_medialib_collection SET ".
				$strUpdate.
			" WHERE ID=".IntVal($Params['col']);

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if (count($Params['childCols']) > 0 && $res)
		{
			$strIds = "0";
			for($i=0; $i < count($Params['childCols']); $i++)
				$strIds .= ",".IntVal($Params['childCols'][$i]);

			$strUpdate = $DB->PrepareUpdate("b_medialib_collection", array('ML_TYPE' => $Params['type']));
			$strSql =
				"UPDATE b_medialib_collection SET ".
					$strUpdate.
				" WHERE ID in (".$strIds.")";

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $res;
	}
}

class CMedialibItem
{
	function CheckFields($arFields)
	{
		if (!isset($arFields['NAME']) || strlen($arFields['NAME']) <= 0)
			return false;

		return true;
	}

	function GetList($Params)
	{
		global $DB;

		$q = '';
		if (is_array($Params['arCollections']))
		{
			if (count($Params['arCollections']) == 1)
			{
				$q = 'WHERE MCI.COLLECTION_ID='.intVal($Params['arCollections'][0]);
			}
			elseif (count($Params['arCollections']) > 1)
			{
				$strCollections = "0";
				for($i=0; $i < count($Params['arCollections']); $i++)
					$strCollections .= ",".IntVal($Params['arCollections'][$i]);
				$q = 'WHERE MCI.COLLECTION_ID in ('.$strCollections.')';
			}
		}

		if (isset($Params['id']) && $Params['id'] > 0)
			$q = 'WHERE MI.ID='.intVal($Params['id']);

		if (isset($Params['minId']) && $Params['minId'] > 0)
		{
			if (strlen($q) > 0)
				$q = trim($q)." AND MI.ID>=".intVal($Params['minId']);
			else
				$q .= "WHERE MI.ID>=".intVal($Params['minId']);
		}

		$err_mess = CMedialibCollection::GetErrorMess()."<br>Function: CMedialibItem::GetList<br>Line: ";
		$strSql = "SELECT
					MI.*,MCI.COLLECTION_ID, F.HEIGHT, F.WIDTH, F.FILE_SIZE, F.CONTENT_TYPE, F.SUBDIR, F.FILE_NAME, F.HANDLER_ID,
					".$DB->DateToCharFunction("MI.DATE_UPDATE")." as DATE_UPDATE2
				FROM b_medialib_collection_item MCI
				INNER JOIN b_medialib_item MI ON (MI.ID=MCI.ITEM_ID)
				INNER JOIN b_file F ON (F.ID=MI.SOURCE_ID) ".$q;

		$res = $DB->Query($strSql, false, $err_mess);
		$arResult = Array();
		$rootPath = CSite::GetSiteDocRoot(false);
		$tmbW = COption::GetOptionInt('fileman', "ml_thumb_width", 140);
		$tmbH = COption::GetOptionInt('fileman', "ml_thumb_height", 105);

		while($arRes = $res->Fetch())
		{
			CMedialibItem::GenerateThumbnail($arRes, array('rootPath' => $rootPath, 'width' => $tmbW, 'height' => $tmbH));
			$arRes['PATH'] = CFile::GetFileSRC($arRes);
			$arResult[]=$arRes;
		}

		return $arResult;
	}

	// Add or edit ITEM
	function Edit($Params)
	{
		global $DB;
		$source_id = false;
		$arFields = $Params['arFields'];
		$bNew = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		$bFile_FD = $Params['path'] && strlen($Params['path']) > 0;
		$bFile_PC = $Params['file'] && strlen($Params['file']['name']) > 0 && $Params['file']['size'] > 0;

		$io = CBXVirtualIo::GetInstance();

		if ($bFile_FD || $bFile_PC)
		{
			if ($bFile_FD)
			{
				$DocRoot = CSite::GetSiteDocRoot(false);
				$tmp_name = $DocRoot.$Params['path'];

				if ($io->FileExists($tmp_name))
				{
					$flTmp = $io->GetFile($tmp_name);
					$file_name = substr($Params['path'], strrpos($Params['path'], '/') + 1);
					$arFile = array(
						"name" => $file_name,
						"size" => $flTmp->GetFileSize(),
						"tmp_name" => $tmp_name,
						"type" => CFile::IsImage($file_name) ? 'image' : 'file'
					);
				}
			}
			else if ($bFile_PC)
			{
				$arFile = $Params['file'];
			}

			if (!CMedialib::CheckFileExtention($arFile["name"]))
				return false;

			if (!$bNew) // Del old file
			{
				$arFile["old_file"] = CMedialibItem::GetSourceId($arFields['ID']);
				$arFile["del"] = "Y";
			}

			// Resizing Image
			if (CFile::IsImage($arFile["name"]))
			{
				$arSize = array(
					'width' => COption::GetOptionInt('fileman', "ml_max_width", 1024),
					'height' => COption::GetOptionInt('fileman', "ml_max_height", 1024)
				);
				$res = CFile::ResizeImage($arFile, $arSize);
			}

			$arFile["MODULE_ID"] = "fileman";
			$source_id = CFile::SaveFile($arFile, "medialibrary");

			if ($source_id) // Get file
			{
				$r = CFile::GetByID($source_id);
				if ($arFile = $r->Fetch())
				{
					if (CFile::IsImage($arFile['FILE_NAME']))
						CMedialibItem::GenerateThumbnail($arFile, array('width' => COption::GetOptionInt('fileman', "ml_thumb_width", 140), 'height' => COption::GetOptionInt('fileman', "ml_thumb_height", 105)));

					$arFile['PATH'] = CMedialibItem::GetFullPath($arFile);
				}
			}
		}

		// TODO: Add error handling
		if ($bNew && !$source_id)
			return false;

		// 2. Add to b_medialib_item
		if (!isset($arFields['~DATE_UPDATE']))
			$arFields['~DATE_UPDATE'] = $DB->CurrentTimeFunction();

		if(!CMedialibItem::CheckFields($arFields))
			return false;

		if (CModule::IncludeModule("search"))
		{
			$arStem = stemming($arFields['NAME'].' '.$arFields['DESCRIPTION'].' '.$arFields['KEYWORDS'], LANGUAGE_ID);
			if (count($arStem) > 0)
				$arFields['SEARCHABLE_CONTENT'] = '{'.implode('}{', array_keys($arStem)).'}';
			else
				$arFields['SEARCHABLE_CONTENT'] = '';
		}

		if ($bNew) // Add
		{
			unset($arFields['ID']);
			$arFields['SOURCE_ID'] = $source_id;
			$arFields['~DATE_CREATE'] = $arFields['~DATE_UPDATE'];
			$ID = CDatabase::Add("b_medialib_item", $arFields);
		}
		else // Update
		{
			if ($source_id)
				$arFields['SOURCE_ID'] = $source_id;
			$ID = $arFields['ID'];
			unset($arFields['ID']);

			$strUpdate = $DB->PrepareUpdate("b_medialib_item", $arFields);
			$strSql =
				"UPDATE b_medialib_item SET ".
					$strUpdate.
				" WHERE ID=".IntVal($ID);

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		// 3. Set fields to b_medialib_collection_item
		if (!$bNew) // Del all rows if
		{
			$strSql = "DELETE FROM b_medialib_collection_item WHERE ITEM_ID=".IntVal($ID);
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		$strCollections = "0";

		for($i=0; $i < count($Params['arCollections']); $i++)
			$strCollections .= ",".IntVal($Params['arCollections'][$i]);

		$strSql =
			"INSERT INTO b_medialib_collection_item(ITEM_ID, COLLECTION_ID) ".
			"SELECT ".intVal($ID).", ID ".
			"FROM b_medialib_collection ".
			"WHERE ID in (".$strCollections.")";

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (!$arFields['ID'])
			$arFields['ID'] = $ID;

		if ($source_id)
			$arFields = array_merge($arFile, $arFields);

		return $arFields;
	}

	function GenerateThumbnail(&$arFile, $Params = array())
	{
		$rootPath = isset($Params['rootPath']) ? $Params['rootPath'] : CSite::GetSiteDocRoot(false);
		if (CFile::IsImage($arFile['FILE_NAME']))
		{
			$arResized = CFile::ResizeImageGet($arFile, array('width' => $Params['width'], 'height' => $Params['height']));
			if($arResized)
				$arFile['THUMB_PATH'] = $arResized['src'];
			$arFile['TYPE'] = 'image';
		}
		else
			$arFile['TYPE'] = 'file';
	}

	function GetItemCollections($Params)
	{
		global $DB;
		$strSql = 'SELECT MCI.COLLECTION_ID
			FROM b_medialib_collection_item MCI
			WHERE MCI.ITEM_ID='.intVal($Params['ID']);
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$arResult = array();
		while($arRes = $res->Fetch())
			$arResult[]=$arRes['COLLECTION_ID'];
		return $arResult;
	}

	function Delete($ID, $bCurrent, $colId)
	{
		global $DB;
		if ($bCurrent) // Del from one collection
		{
			if (!CMedialib::CanDoOperation('medialib_del_item', $colId))
				return false;
			$strSql = "DELETE FROM b_medialib_collection_item WHERE ITEM_ID=".IntVal($ID)." AND COLLECTION_ID=".IntVal($colId);
			$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
		else // Del from all collections
		{
			$arCols = CMedialibItem::GetItemCollections(array('ID' => $ID));
			for ($i = 0, $l = count($arCols); $i < $l; $i++)
			{
				if (!CMedialib::CanDoOperation('medialib_del_item', $arCols[$i])) // Check access
					return false;
			}
			$strSql = "DELETE FROM b_medialib_collection_item WHERE ITEM_ID=".IntVal($ID);
			$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		CMedialibItem::DeleteEmpty();

		return $z;
	}

	function DeleteEmpty()
	{
		global $DB;

		$strSql = 'SELECT MI.*,MCI.COLLECTION_ID
			FROM b_medialib_item MI
			LEFT JOIN b_medialib_collection_item MCI ON (MI.ID=MCI.ITEM_ID)
			WHERE MCI.COLLECTION_ID is null';
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$strItems = "0";
		while($arRes = $res->Fetch())
		{
			$strItems .= ",".IntVal($arRes['ID']);

			if ($arRes['SOURCE_ID'] > 0) // Clean from 'b_file'
				CFile::Delete(IntVal($arRes['SOURCE_ID']));
		}

		// Clean from 'b_medialib_item'
		if ($strItems != "0")
			$DB->Query("DELETE FROM b_medialib_item WHERE ID in (".$strItems.")", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	function GetThumbPath($arImage)
	{
		return BX_PERSONAL_ROOT."/tmp/".$arImage['SUBDIR'].'/'.$arImage['FILE_NAME'];
	}

	function GetFullPath($arImage, $upload_dir = false)
	{
		return CFile::GetFileSRC($arImage, $upload_dir);
	}

	function GetSourceId($id)
	{
		global $DB;
		$strSql = 'SELECT SOURCE_ID
			FROM b_medialib_item
			WHERE ID='.intVal($id);
		$r = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($res = $r->Fetch())
			return $res['SOURCE_ID'];

		return false;
	}

	function Search($arQuery, $arTypes = array())
	{
		global $DB;
		$err_mess = CMedialibCollection::GetErrorMess()."<br>Function: CMedialibItem::Search<br>Line: ";

		$strSql = "SELECT
					MI.*, MI.*,MCI.COLLECTION_ID, F.HEIGHT, F.WIDTH, F.FILE_SIZE, F.CONTENT_TYPE, F.SUBDIR, F.FILE_NAME, F.HANDLER_ID,
					".$DB->DateToCharFunction("MI.DATE_UPDATE")." as DATE_UPDATE2
				FROM b_medialib_item MI
				INNER JOIN b_medialib_collection_item MCI ON (MI.ID=MCI.ITEM_ID)
				INNER JOIN b_file F ON (F.ID=MI.SOURCE_ID)
				WHERE 1=1";

		$l = count($arQuery);
		if ($l == 0)
			return array();

		for ($i = 0; $i < $l; $i++)
			$strSql .= " AND MI.SEARCHABLE_CONTENT LIKE '%{".$DB->ForSql($arQuery[$i])."}%'";

		$strSql .= " ORDER BY MI.ID DESC";

		$res = $DB->Query($strSql, false, $err_mess);
		$arResult = Array();
		$rootPath = CSite::GetSiteDocRoot(false);
		$tmbW = COption::GetOptionInt('fileman', "ml_thumb_width", 140);
		$tmbH = COption::GetOptionInt('fileman', "ml_thumb_height", 105);

		$elId2Index = array();
		$colId2Index = array();
		$arCol = CMedialibCollection::GetList(array('arFilter' => array('ACTIVE' => 'Y', "TYPES" => $arTypes)));

		for ($i = 0, $l = count($arCol); $i < $l; $i++)
			$colId2Index[$arCol[$i]['ID']] = $i;

		while($arRes = $res->Fetch())
		{
			$colId = $arRes['COLLECTION_ID'];
			if (!isset($colId2Index[$colId]) || !CMedialibCollection::IsViewable($arCol[$colId2Index[$colId]], $arCol))
				continue;

			if (isset($elId2Index[$arRes['ID']]))
			{
				$arResult[$elId2Index[$arRes['ID']]]['collections'][] = $colId;
			}
			else
			{
				$elId2Index[$arRes['ID']] = count($arResult);
				$arRes['collections'] = array($colId);
				$arRes['perm'] = array
				(
					'edit' => true,
					'del' => true
				);

				CMedialibItem::GenerateThumbnail($arRes, array('rootPath' => $rootPath, 'width' => $tmbW, 'height' => $tmbH));
				$arRes['PATH'] = CFile::GetFileSRC($arRes);
				$arResult[]=$arRes;
			}
		}

		return $arResult;
	}
}

class CMedialibTabControl
{
	var $_id = "";
	var $_key = "";
	var $_tabs = array();
	var $_current_tab = 0;
	var $sEpilog = "";

	function ShowScript()
	{
		static $bShowed = false;
		if(!$bShowed)
		{
			CUtil::InitJSCore(array('ajax'));
			?>
			<script type="text/javascript" src="/bitrix/js/fileman/medialib/tabs.js?v=<?= filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/fileman/medialib/tabs.js')?>"></script>
			<?
			$bShowed = true;
		}
	}

	function CMedialibTabControl($id, $tabs)
	{
		$this->_id = $id;
		$this->_tabs = $tabs;
		$this->_key = md5(mt_rand());
		$this->_uniq_id = $this->_id."_".$this->_key;
	}

	function BeginEpilog()
	{
		ob_start();
	}

	function EndEpilog()
	{
		$this->sEpilog = ob_get_contents();
		ob_end_clean();
	}

	function BeginTab()
	{
		ob_start();
	}

	function EndTab()
	{
		$this->_tabs[$this->_current_tab]["CONTENT"] = ob_get_contents();
		ob_end_clean();
		$this->_current_tab++;
	}

	function Show()
	{
		$bClosed = strlen($this->sEpilog) <= 0;
		CMedialibTabControl::ShowScript();
		?>
		<table class="bx-ml-tab-cont" cellspacing="0" cellpadding="0"><tr><td>
		<table id="<?= $this->_uniq_id?>" class="imgtab-tabs" cellspacing="0" cellpadding="0" width="100%"><tr valign="top">
		<td class="imgtab-none-sel"><div class="empty"></div></td>
		<?
		$this->__tabs = array();
		for($i = 0; $i < count($this->_tabs); $i++)
		{
			$this->_tabs[$i]["TD_ID"] = $this->_id."_".$this->_tabs[$i]["DIV"];
			$this->_tabs[$i]["DIV_ID"] = "div_".$this->_tabs[$i]["TD_ID"];
			$this->__tabs[$i] = $this->_tabs[$i];
			unset($this->__tabs[$i]['CONTENT']);
		}
		$this->__tabs = CUtil::PHPToJSObject($this->__tabs);

		for($i = 0; $i < count($this->_tabs); $i++)
		{
			if($i == 0):?>

				<td class="imgtab-sel" nowrap id="<?= $this->_tabs[$i]["TD_ID"]?>" onclick="InitMedialibTabControl('<?= $this->_uniq_id?>', <?= $this->__tabs?>);top.<?= $this->_uniq_id?>.SelectTab(this);"><table class="imgtab-tab" cellspacing="0" cellpadding="0"><tr valign="top"><td><img src="<?= "/bitrix/images/fileman/medialib/tabs/".$this->_tabs[$i]["ICON"].".gif"?>" /></td><td>&nbsp;<div title="<?= $this->_tabs[$i]["TITLE"]?>"><?= $this->_tabs[$i]["NAME"]?></div></td></tr></table></td>
				<?if($i == count($this->_tabs)-1){?>
					<td class="imgtab-sel-none" ><div class="empty"></div></td>
				<?}else{?>
					<td class="imgtab-sel-some" ><div class="empty"></div></td>
				<?}?>
			<?else:?>
				<td class="imgtab-some" nowrap id="<?= $this->_tabs[$i]["TD_ID"]?>" onclick="InitMedialibTabControl('<?= $this->_uniq_id?>', <?= $this->__tabs?>); top.<?= $this->_uniq_id?>.SelectTab(this)"><table class="imgtab-tab" cellspacing="0" cellpadding="0"><tr valign="top"><td><img src="<?= "/bitrix/images/fileman/medialib/tabs/".$this->_tabs[$i]["ICON"].".gif"?>" /></td><td>&nbsp;<div title="<?= $this->_tabs[$i]["TITLE"]?>"><?= $this->_tabs[$i]["NAME"]?></div></td></tr></table></td>
				<?if($i == count($this->_tabs)-1){?>
					<td class="imgtab-some-none" ><div class="empty"></div></td>
				<?}else{?>
					<td class="imgtab-some-some" ><div class="empty"></div></td>
				<?}?>
			<?endif;
		}
		?>
		<td class="imgtab-none" width="100%"><div class="empty"></div></td>
		</tr></table>

		</td></tr>
		<tr><td class="imgtab-content <?echo $bClosed? "imgtab-content-closed": "imgtab-content-opened";?>">
			<?
			for($i = 0; $i < count($this->_tabs); $i++)
			{
				if($i == 0):?>
					<div id="<?echo $this->_tabs[$i]["DIV_ID"]?>" >
					<?echo $this->_tabs[$i]["CONTENT"]?>
					</div>
				<?else:?>
					<div id="<?echo $this->_tabs[$i]["DIV_ID"]?>" style="display:none">
					<?echo $this->_tabs[$i]["CONTENT"]?>
					</div>
				<?endif;
				unset($this->_tabs[$i]["CONTENT"]);
			}
			?>
		<?if($this->sEpilog):?>
			<tr><td><?echo $this->sEpilog;?></td></tr>
		<?endif;?>
		</td></tr></table>

		<script>
		if (!window.InitMedialibTabControl)
		{
			function InitMedialibTabControl(uniq_id, tabs)
			{
				if (!top[uniq_id] || (top[uniq_id] && top[uniq_id].nodeType != undefined))
				{
					top[uniq_id] = new MedialibTabControl(uniq_id, tabs, '<?= filemtime($_SERVER["DOCUMENT_ROOT"].'/bitrix/js/fileman/medialib/tabs.css')?>');
					BX.bind(window, "unload", function(){if (top[uniq_id]){top[uniq_id].Destroy();}});
				}
			}
		}

		InitMedialibTabControl('<?= $this->_uniq_id?>', <?= CUtil::PHPToJSObject($this->_tabs)?>);
		BX.ready(function(){top.<?= $this->_uniq_id?>.SelectTab(BX('<?= $this->_tabs[0]["TD_ID"]?>'));});
		</script>
		<?
	}
}

?>