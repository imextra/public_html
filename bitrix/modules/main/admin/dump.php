<?php
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2010 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "utilities/dump.php");

if(!$USER->CanDoOperation('edit_php'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", microtime(true));

if (!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0777);

if (!defined("BX_FILE_PERMISSIONS"))
	define("BX_FILE_PERMISSIONS", 0666);

IncludeModuleLangFile(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/fileman.php");

if (function_exists('mb_internal_encoding'))
	mb_internal_encoding('ISO-8859-1');

define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));

$com_marker = "--";
$filr_id = "";
$sTableID = "tbl_dump";

$oSort = new CAdminSorting($sTableID, "timestamp", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$path = BX_ROOT."/backup";
if($_REQUEST['process'] == "Y" && check_bitrix_sessid())
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	if($_REQUEST['action'] == 'start')
	{
		$NS = Array();

		if(!file_exists(DOCUMENT_ROOT.BX_ROOT."/backup"))
			mkdir(DOCUMENT_ROOT.BX_ROOT."/backup", BX_DIR_PERMISSIONS);

		if(!file_exists(DOCUMENT_ROOT.BX_ROOT."/backup/index.php"))
		{
			$f = fopen(DOCUMENT_ROOT.BX_ROOT."/backup/index.php","w");
			fwrite($f,"<head><meta http-equiv=\"REFRESH\" content=\"0;URL=/bitrix/admin/index.php\"></head>");
			fclose($f);
		}

		if(!is_dir(DOCUMENT_ROOT.BX_ROOT."/backup") || !is_writable(DOCUMENT_ROOT.BX_ROOT."/backup"))
			RaiseErrorAndDie(GetMessage("MAIN_DUMP_FOLDER_ERR",array('#FOLDER#' => DOCUMENT_ROOT.BX_ROOT.'/backup')));

		DeleteDirFilesEx(BX_ROOT.'/backup/clouds');

		$bUseCompression = $_REQUEST['dump_disable_gzip'] != 'Y';
		if(!extension_loaded('zlib') || !function_exists("gzcompress"))
			$bUseCompression = False;

		$arc_name = DOCUMENT_ROOT.BX_ROOT."/backup/".date("Y-m-d.H-i-s.");
		if ($_REQUEST['dump_site_id'])
			$arc_name.= ($NS['dump_site_id'] = $_REQUEST['dump_site_id']).'.';
		$arc_name .= substr(md5(uniqid(rand(), true)), 0, 8);
		$NS['dump_name'] = $arc_name.".sql";
		$NS['st_row'] = -1;
		$arc_name .= ".tar";
		if ($bUseCompression)
			$arc_name .=".gz";
		$NS['arc_name'] = $arc_name;

		COption::SetOptionInt("main", "dump_use_compression", $bUseCompression);
		COption::SetOptionInt("main", "dump_max_exec_time", intval($_REQUEST['dump_max_exec_time']) < 5 ? 5 : $_REQUEST['dump_max_exec_time']);
		COption::SetOptionInt("main", "dump_max_exec_time_sleep", $_REQUEST['dump_max_exec_time_sleep']);
		COption::SetOptionInt("main", "dump_integrity_check", $_REQUEST['dump_integrity_check'] == 'Y');

		$NS['BUCKET_ID'] = intval($_REQUEST['dump_bucket_id']);
		COption::SetOptionInt("main", "dump_bucket_id", $_REQUEST['dump_bucket_id']);

		COption::SetOptionInt("main", "dump_max_file_size", $_REQUEST['max_file_size']);
		COption::SetOptionInt("main", "dump_file_public", $_REQUEST['dump_file_public'] == 'Y');
		COption::SetOptionInt("main", "dump_file_kernel", $_REQUEST['dump_file_kernel'] == 'Y');
		COption::SetOptionInt("main", "dump_base", $DB->type == 'MYSQL' ? ($_REQUEST['dump_base'] == 'Y') : 0);
		COption::SetOptionInt("main", "dump_base_skip_stat", $_REQUEST['dump_base_skip_stat'] == 'Y');
		COption::SetOptionInt("main", "dump_base_skip_search", $_REQUEST['dump_base_skip_search'] == 'Y');
		COption::SetOptionInt("main", "dump_base_skip_log", $_REQUEST['dump_base_skip_log'] == 'Y');
		COption::SetOptionInt("main", "skip_symlinks", $_REQUEST['skip_symlinks'] == 'Y');

		if ($arAllBucket = GetBucketList())
			foreach($arAllBucket as $arBucket)
				COption::SetOptionInt('main', 'dump_cloud_'.$arBucket['ID'], $_REQUEST['dump_cloud'][$arBucket['ID']] == 'Y');

		$skip_mask = $_REQUEST['skip_mask'] == 'Y';
		COption::SetOptionInt("main", "skip_mask", $skip_mask);

		$skip_mask_array = array();
		if ($skip_mask && is_array($_REQUEST['arMask']))
		{
			$arMask = array_unique($_REQUEST['arMask']);
			foreach($arMask as $mask)
				if (trim($mask))
				{
					$mask = rtrim(str_replace('\\','/',trim($mask)),'/');
					$skip_mask_array[] = $mask;
				}
			COption::SetOptionString("main", "skip_mask_array", serialize($skip_mask_array));
		}
		$NS["step"] = 1;
	}
	elseif($_REQUEST['action'] == 'cloud_send')
	{
		$NS = Array();
		$NS['cloud_send'] = 1;
		$NS['arc_name'] = $name = DOCUMENT_ROOT.BX_ROOT.'/backup/'.str_replace(array('..','/','\\'),'',$_REQUEST['f_id']);
		$NS['arc_size'] = filesize($NS['arc_name']);
		$NS['BUCKET_ID'] = intval($_REQUEST['dump_bucket_id']);
		while(file_exists($name = CTar::getNextName($name)))
			$NS['arc_size'] += filesize($name);
		$NS['step'] = 6;
	}
	else
	{
		$NS = $_SESSION['BX_DUMP_STATE'];
		$ar = unserialize(COption::GetOptionString("main","skip_mask_array"));
		$skip_mask_array = is_array($ar) ? $ar : array();
	}

	$after_file = str_replace('.sql','_after_connect.sql',$NS['dump_name']);

	// Step 1: Dump
	if($NS["step"] == 1)
	{
		if (IntOption('dump_base'))
		{
			$bres = BaseDump($NS["dump_name"], intval($NS["num"]), intval($NS["st_row"]));
			$NS["ptab"] = $bres["ptab"];
			$NS["num"] = $bres["num"];
			$NS["st_row"] = $bres["st_row"];

			if ($bres['end'])
			{
				$rs = $DB->Query('SHOW VARIABLES LIKE "character_set_results"');
				if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
				{
					$charset = $f['Value'];
					$rs = fopen($after_file,'wb');
					fwrite($rs,"SET NAMES '$charset';\n");
					fclose($rs);
				}

				$NS["step"]++;

			}
		}
		else
			$NS["step"]++;
	}

	// Step 2: pack dump 
	if($NS["step"] == 2)
	{
		if (IntOption('dump_base'))
		{
			if (haveTime())
			{
				$tar = new CTar;
				$tar->ArchiveSizeMax = COption::GetOptionString('main', 'dump_archive_size_max', 1024 * 1024 * 1024);
				$tar->gzip = IntOption('dump_use_compression');
				$tar->path = DOCUMENT_ROOT;
				$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);

				if (!$tar->openWrite($NS["arc_name"]))
					RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'));

				if (!$tar->ReadBlockCurrent && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php'))
					$tar->addFile($f);

				$Block = $tar->Block;
				while(haveTime() && ($r = $tar->addFile($NS['dump_name'])) && $tar->ReadBlockCurrent > 0);
				$NS["data_size"] += 512 * ($tar->Block - $Block);
				
				if ($r === false)
					RaiseErrorAndDie(implode('<br>',$tar->err));

				$NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;

				if($tar->ReadBlockCurrent == 0)
				{
					$tar->addFile($after_file);
					unlink($NS["dump_name"]) && (!file_exists($after_file) || unlink($after_file));

					$NS['arc_size'] = 0;
					$name = $NS["arc_name"];
					while(file_exists($name))
					{
						$size = filesize($name);
						$NS['arc_size'] += $size;
						if (IntOption("disk_space") > 0)
							CDiskQuota::updateDiskQuota("file", $size, "add");
						$name = CTar::getNextName($name);
					}

					$NS["step"]++;
				}
				$tar->close();
			}
		}
		else
			$NS["step"]++;
	}

	// Step 3: Download Cloud Files 
	if($NS["step"] == 3)
	{
		if ($arDumpClouds = CheckDumpClouds())
		{
			if (haveTime())
			{
				foreach($arDumpClouds as $arBucket)
				{
					$id = $arBucket['ID'];
					if ($NS['bucket_finished_'.$id])
						continue;

					$obCloud = new CloudDownload($id);
					$obCloud->last_bucket_path = $NS['last_bucket_path'];
					if ($res = $obCloud->Scan(''))
						$NS['bucket_finished_'.$id] = true;
					else // partial
					{
						$NS['last_bucket_path'] = $obCloud->path;
						$NS['download_cnt'] += $obCloud->download_cnt;
						$NS['download_size'] += $obCloud->download_size;
						if ($c = count($NS->arSkipped))
							$NS['download_skipped'] += $c;
						break;
					}
				}

				if ($res) // finish
					$NS['step']++;
			}
		}
		else
			$NS["step"]++;
	}
	
	// Step 4: Tar Files
	if($NS["step"] == 4)
	{
		if (CheckDumpFiles() || CheckDumpClouds())
		{
			if (haveTime())
			{
				$DOCUMENT_ROOT_SITE = DOCUMENT_ROOT;
				if ($NS['dump_site_id'])
				{
					$rs = CSite::GetList($by='sort', $order='asc', array('ID' => $NS['dump_site_id']));
					if ($f = $rs->Fetch())
					{
						$DOCUMENT_ROOT_SITE = rtrim(str_replace('\\','/',$f['ABS_DOC_ROOT']),'/');
						$DIR = rtrim(str_replace('\\','/',$f['DIR']),'/');
					}
				}
				if (!defined('DOCUMENT_ROOT_SITE'))
					define('DOCUMENT_ROOT_SITE', $DOCUMENT_ROOT_SITE);

				$tar = new CTar;
				$tar->ArchiveSizeMax = COption::GetOptionString('main', 'dump_archive_size_max', 1024 * 1024 * 1024);
				$tar->gzip = IntOption('dump_use_compression');
				$tar->path = DOCUMENT_ROOT_SITE;
				$tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);

				if (!$tar->openWrite($NS["arc_name"]))
					RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS'));

				$Block = $tar->Block;
				$DirScan = new CDirRealScan;

				if (!$NS['startPath'])
				{
					if (!IntOption('dump_base') && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php'))
						$tar->addFile($f);
				}
				else
					$DirScan->startPath = $NS['startPath'];

				$r = $DirScan->Scan(DOCUMENT_ROOT_SITE.$DIR);
				$NS["data_size"] += 512 * ($tar->Block - $Block);
				$tar->close();

				if ($r === false)
					RaiseErrorAndDie(implode('<br>',array_merge($DirScan->err,$tar->err)));

				$NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;
				$NS["startPath"] = $DirScan->nextPath;
				$NS["cnt"] += $DirScan->FileCount;

				if ($r !== 'BREAK') // finish
				{
					$NS['arc_size'] = 0;
					$name = $NS["arc_name"];
					while(file_exists($name))
					{
						$size = filesize($name);
						$NS['arc_size'] += $size;
						if (IntOption("disk_space") > 0)
							CDiskQuota::updateDiskQuota("file", $size, "add");
						$name = CTar::getNextName($name);
					}
					DeleteDirFilesEx(BX_ROOT.'/backup/clouds');
					$NS["step"]++;
				}
			}
		}
		else
			$NS["step"]++;
	}

	// Step 5: Integrity check
	if($NS["step"] == 5)
	{
		if (IntOption('dump_integrity_check'))
		{
			if (haveTime())
			{
				$tar = new CTarCheck;

				if (!$tar->openRead($NS["arc_name"]))
					RaiseErrorAndDie(GetMessage('DUMP_NO_PERMS_READ'));
				else
				{
					if($Block = intval($NS['Block']))
						$tar->Skip($Block);

					while(($r = $tar->extractFile()) && haveTime());

					$NS["Block"] = $tar->Block;

					if ($r === false)
						RaiseErrorAndDie(implode('<br>',$tar->err));
					if ($r === 0)
						$NS["step"]++;
				}
				$tar->close();
			}
		}
		else
			$NS["step"]++;
	}

	// Step 6: Send to the cloud 
	if($NS["step"] == 6)
	{
		if ($NS['BUCKET_ID'])
		{
			if (haveTime())
			{
				if (!CModule::IncludeModule('clouds'))
					RaiseErrorAndDie(GetMessage("MAIN_DUMP_NO_CLOUDS_MODULE"));

				$file_size = filesize($NS["arc_name"]);
				$obUpload = new CCloudStorageUpload(substr($NS['arc_name'],strlen(DOCUMENT_ROOT)));
				if (!$obUpload->isStarted())
				{
					if (!$obUpload->Start(intval($NS['BUCKET_ID']), $file_size))
					{
						if ($e = $APPLICATION->GetException())
							$strError = $e->GetString();
						else
							$strError = GetMessage('MAIN_DUMP_INT_CLOUD_ERR');
						RaiseErrorAndDie($strError);
					}
				}

				if ($fp = fopen($NS['arc_name'],'rb'))
				{
					fseek($fp, $obUpload->getPos());
					$part = fread($fp, $obUpload->getPartSize());
					fclose($fp);
					while($obUpload->hasRetries())
						if($res = $obUpload->Next($part))
							break;
					if ($res)
					{
						$pos = $obUpload->getPos();
						if ($pos >= $file_size) // file ended
						{
							if($obUpload->Finish())
							{
								$NS['pos'] += $file_size;
								$ar = $obUpload->GetArray();
								$oBucket = new CCloudStorageBucket($ar['BUCKET_ID']);
								$oBucket->IncFileCounter($file_size);

								if (file_exists($arc_name = CTar::getNextName($NS['arc_name'])))
									$NS['arc_name'] = $arc_name;
								else
								{
									$name = preg_replace('#\.[0-9]+$#','',$NS['arc_name']);
									while(file_exists($name))
									{
										$size = filesize($name);
										if (unlink($name) && IntOption("disk_space") > 0)
											CDiskQuota::updateDiskQuota("file",$size , "del");
										$name = CTar::getNextName($name);
									}

									$NS["step"]++;
								}
							}
							else
							{
								$obUpload->Delete();
								RaiseErrorAndDie(GetMessage("MAIN_DUMP_ERR_FILE_SEND").basename($NS['arc_name']));
							}
						} // partial
						else
							$pos += $NS['pos'];
					}
					else
					{
						$obUpload->Delete();
						RaiseErrorAndDie(GetMessage("MAIN_DUMP_ERR_FILE_SEND").basename($NS['arc_name']));
					}
				}
				else
					RaiseErrorAndDie(GetMessage("MAIN_DUMP_ERR_OPEN_FILE").$NS['arc_name']);
			}
		}
		else
			$NS["step"]++;
	}


	$NS["time"] += workTime();
	$_SESSION['BX_DUMP_STATE'] = $NS;

	if ($NS["step"] > 6) // Finish
	{
		$title = $NS['cloud_send'] ? GetMessage("MAIN_DUMP_SUCCESS_SENT") : GetMessage("MAIN_DUMP_FILE_FINISH");
		$status_msg = '';
		
		if ($NS["num"])
			$status_msg .= GetMessage("MAIN_DUMP_TABLE_FINISH")." <b>".$NS["num"]."</b><br>";
		if ($NS["cnt"])
			$status_msg .= GetMessage("MAIN_DUMP_FILE_CNT")." <b>".$NS["cnt"]."</b><br>";
		if ($NS["data_size"])
			$status_msg .= GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".HumanSize($NS["data_size"])."</b><br>";
		if ($NS["arc_size"])
			$status_msg .= GetMessage("MAIN_DUMP_ARC_SIZE")." <b>".HumanSize($NS["arc_size"])."</b><br>";

		$status_msg .= GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';

		CAdminMessage::ShowMessage(array(
			"MESSAGE" => $title,
			"DETAILS" => $status_msg ,
			"TYPE" => "OK",
			"HTML" => true));

?>
		<?echo bitrix_sessid_post()?>
		<script>
			EndDump();
			RefreshList();
		</script>
<?
	}
	else // Partial
	{
		switch ($NS['step'])
		{
			case 1:
				$status_title = GetMessage('DUMP_DB_CREATE');
				$status_msg = GetMessage("MAIN_DUMP_TABLE_FINISH")." <b>".($NS["num"])."</b>"
					.'<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';
			break;
			case 2:
				$arc_size = file_exists($NS['arc_name']) ? filesize($NS['arc_name']) : 0;
				$status_title = GetMessage("MAIN_DUMP_DB_PROC");
				$status_msg = 
				GetMessage('CURRENT_POS').' <b>'.HumanSize($arc_size) .'</b>  (<b>'.round(100 * $arc_size / filesize($NS['dump_name'])).'%</b>) '
					.'<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';
			break;
			case 3:
				$status_title = GetMessage("MAIN_DUMP_CLOUDS_DOWNLOAD");
				$status_msg = GetMessage("MAIN_DUMP_FILES_DOWNLOADED").": <b>".intval($NS["download_cnt"])."</b><br>".
				GetMessage("MAIN_DUMP_FILES_SIZE").": <b>".HumanSize($NS["download_size"])."</b><br>";
				if ($NS['download_skipped'])
					$status_msg .= GetMessage("MAIN_DUMP_DOWN_ERR_CNT").': <b>'.$NS['download_skipped'].'</b><br>';
				$status_msg .= GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';
			break;
			case 4:
				$status_title = GetMessage("MAIN_DUMP_SITE_PROC");
				$status_msg = GetMessage("MAIN_DUMP_FILE_CNT")." <b>".intval($NS["cnt"])."</b><br>".
				GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".HumanSize($NS["data_size"])."</b> ";

				if (is_object($DirScan))
					$status_msg.= '<br>'.GetMessage('DUMP_CUR_PATH').' <b>'.substr($DirScan->nextPath,strlen(DOCUMENT_ROOT_SITE)).'</b>';

				$status_msg .= '<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';
			break;
			case 5:
				$status_title = GetMessage('INTEGRITY_CHECK');
				$status_msg = GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".HumanSize($NS["arc_size"])."</b><br>".
				GetMessage('CURRENT_POS').' <b>'.HumanSize($NS['Block'] * 512).'</b>  (<b>'.round(100 * $NS['Block'] * 512 / $NS['data_size']).'%</b>) '
					.'<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';
			break;
			case 6:
				$status_title = GetMessage("MAIN_DUMP_FILE_SENDING");
				$status_msg = GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".HumanSize($NS["arc_size"])."</b><br>".
				GetMessage('CURRENT_POS').' <b>'.HumanSize($pos).'</b>  (<b>'.round(100 * $pos / $NS['arc_size']).'%</b>) '
					.'<br>'.GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';
		}

		CAdminMessage::ShowMessage(array(
			"MESSAGE" => $status_title,
			"DETAILS" =>  $status_msg,
			"TYPE" => "OK",
			"HTML" => true));
			
//		echo '<input type=button onclick="AjaxSend(\'?process=Y&'.bitrix_sessid_get().'\')" value="Next">';
		?>
		<script>
			window.setTimeout("if(!stop)AjaxSend('?process=Y&<?=bitrix_sessid_get()?>')",<?=1000 * IntOption("dump_max_exec_time_sleep")?>);
		</script>
		<?
	}
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_js.php");
	die();
}

if ($_REQUEST['action'] && check_bitrix_sessid())
{
	if ($_REQUEST['action'] == 'download')
	{
		$name = $path.'/'.$_REQUEST['f_id'];
		echo '<script>';

		if ($BUCKET_ID = intval($_REQUEST['BUCKET_ID']))
		{
			if (CModule::IncludeModule('clouds'))
			{
				$obBucket = new CCloudStorageBucket($BUCKET_ID);
				if ($obBucket->Init())
				{
					while($obBucket->FileExists($name))
					{
						echo 'window.open("'.htmlspecialchars($obBucket->GetFileSRC(array("URN" => $name))).'");'."\n";
						$name = CTar::getNextName($name);
					}
				}
			}
		}
		else
		{
			while(file_exists(DOCUMENT_ROOT.$name))
			{
				echo 'window.open("'.htmlspecialchars($name).'");'."\n";
				$name = CTar::getNextName($name);
			}
		}
		echo '
			EndDump();
		</script>';
		die();
	}
	elseif ($_REQUEST['action'] == 'copy')
	{
		$name = $path.'/'.$_REQUEST['f_id'];
		echo '
		<script>
		';

		$url = '';
		if ($BUCKET_ID = intval($_REQUEST['BUCKET_ID']))
		{
			if (CModule::IncludeModule('clouds'))
			{
				$obBucket = new CCloudStorageBucket($BUCKET_ID);
				if ($obBucket->Init())
					$url = htmlspecialchars($obBucket->GetFileSRC(array("URN" => $name)));
			}
		}
		else
		{
			$host = COption::GetOptionString('main', 'server_name', $_SERVER['HTTP_HOST']);
			$url = 'http://'.htmlspecialchars($host.$name);
		}
		if ($url)
			echo 'window.prompt("'.GetMessage("MAIN_DUMP_USE_THIS_LINK").' restore.php", "'.htmlspecialchars($url).'");'."\n";
		echo '
			EndDump();
		</script>';
		die();
	}
	elseif ($_REQUEST['action'] == 'restore')
	{
		if (!copy($f = DOCUMENT_ROOT.BX_ROOT.'/modules/main/admin/restore.php', DOCUMENT_ROOT.'/restore.php'))
			RaiseErrorAndDie(GetMessage("MAIN_DUMP_ERR_COPY_FILE").htmlspecialchars($f));

		$url = '';
		$name = $path.'/'.$_REQUEST['f_id'];
		if ($BUCKET_ID = intval($_REQUEST['BUCKET_ID']))
		{
			if (CModule::IncludeModule('clouds'))
			{
				$obBucket = new CCloudStorageBucket($BUCKET_ID);
				if ($obBucket->Init())
					$url = 'arc_down_url='.htmlspecialchars($obBucket->GetFileSRC(array("URN" => $name)));
			}
		}
		else
			$url = 'local_arc_name='.htmlspecialchars($name);
		if ($url)
			echo '<script>document.location = "/restore.php?Step=1&'.$url.'";</script>';
		echo '<script>EndDump();</script>';
		die();
	}
}
$DB->Query("UNLOCK TABLES",true);

######### Admin list #######
$arFilterFields = array();
$lAdmin->InitFilter($arFilterFields);
$lAdmin->BeginPrologContent();

$site = CSite::GetSiteByFullPath(DOCUMENT_ROOT);
if ($arID = $lAdmin->GroupAction())
{
	foreach ($arID as $ID)
	{

		if (strlen($ID) <= 0)
			continue;

		if ($APPLICATION->GetFileAccessPermission(array($site, $path."/".$ID))< "W")
			continue;
		switch ($_REQUEST['action'])
		{
			case "delete":
				$BUCKET_ID = intval($_REQUEST['BUCKET_ID']);
				if (!$BUCKET_ID)
					$BUCKET_ID = $_SESSION['BX_DUMP_BUCKET'][$ID];
				if ($BUCKET_ID)
				{
					if (CModule::IncludeModule('clouds'))
					{
						$obBucket = new CCloudStorageBucket($BUCKET_ID);
						if ($obBucket->Init())
						{
							$name = $path.'/'.$ID;
							while($obBucket->FileExists($name))
							{
								$file_size = $obBucket->GetFileSize($name);
								if ($obBucket->DeleteFile($name))
									$obBucket->DecFileCounter($file_size);
								$name = CTar::getNextName($name);
							}

							$e = $APPLICATION->GetException();
							if(is_object($e))
								$lAdmin->AddGroupError($e->GetString(), $ID);
						}
						else
							$lAdmin->AddGroupError(GetMessage("MAIN_DUMP_ERR_INIT_CLOUD"), $ID);
					}
				}
				else
				{
					while(file_exists(DOCUMENT_ROOT.$path.'/'.$ID))
					{
						$strWarning_tmp = CFileMan::DeleteEx(Array($site, CFileMan::NormalizePath($path."/".$ID)));

						if (strlen($strWarning_tmp) > 0)
							$lAdmin->AddGroupError($strWarning_tmp, $ID);

						$ID = CTar::getNextName($ID);
					}
				}
			break;
			case "rename":
				if (preg_match('#^[a-z0-9\-\.]+$#i',$_REQUEST['name']))
				{
					$arName = ParseFileName($_REQUEST['ID']);
					$new_name = $_REQUEST['name'].'.'.$arName['ext'];

					if ($BUCKET_ID = intval($_REQUEST['BUCKET_ID']))
					{
						// Not realized 'cos no cloud API
					}
					else
					{
						while(file_exists(DOCUMENT_ROOT.$path.'/'.$ID))
						{
							if (!rename(DOCUMENT_ROOT.$path.'/'.$ID, DOCUMENT_ROOT.$path.'/'.$new_name))
							{
								$lAdmin->AddGroupError(GetMessage("MAIN_DUMP_ERR_FILE_RENAME").htmlspecialchars($ID), $ID);
								break;
							}

							$ID = CTar::getNextName($ID);
							$new_name = CTar::getNextName($new_name);
						}
					}
				}
				else
					$lAdmin->AddGroupError(GetMessage("MAIN_DUMP_ERR_NAME"), $ID);
			break;
		}
	}
}
InitSorting();

$arDirs = array();
$arFiles = array();
$arTmpFiles = array();
$arFilter = array();

GetDirList(Array($site, $path), $arDir, $arTmpFiles, $arFilter, Array($by=>$order), "F");

// Clouds
if (($arAllBucket = GetBucketList()) && $_REQUEST['read_clouds'])
{
	foreach($arAllBucket as $arBucket)
	{
		if ($arCloudFiles = GetBucketFileList($arBucket['ID'], BX_ROOT.'/backup/'))
		{
			foreach($arCloudFiles['file'] as $k=>$v)
			{
				$arTmpFiles[] = array(
					'NAME' => $v,
					'SIZE' => $arCloudFiles['file_size'][$k],
					'DATE' => '',
					'PERMISSION' => 'X',
					'BUCKET_ID' => $arBucket['ID'],
					'PLACE' => htmlspecialchars($arBucket['BUCKET'].' ('.$arBucket['SERVICE_ID'].')')

				);
			}
		}
	}
}
$arWriteBucket = GetBucketList($arFilter = array('READ_ONLY' => 'Y'));

$arParts = array();
$arSize = array();
foreach($arTmpFiles as $k=>$ar)
{
	if (preg_match('#^(.*\.(tar|gz))(\.[0-9]+)?$#',$ar['NAME'],$regs))
	{
		$arParts[$regs[1]]++;
		$arSize[$regs[1]] += $ar['SIZE'];
		if (!$regs[3])
		{
			$arFiles[] = $ar;
			$_SESSION['BX_DUMP_BUCKET'][$ar['NAME']] = intval($ar['BUCKET_ID']);
		}
	}
}

$rsDirContent = new CDBResult;
$rsDirContent->InitFromArray($arFiles);
$rsDirContent->sSessInitAdd = $path;
$rsDirContent = new CAdminResult($rsDirContent, $sTableID);
$rsDirContent->NavStart(20);

// установка строки навигации
$lAdmin->NavText($rsDirContent->GetNavPrint(GetMessage("MAIN_DUMP_FILE_PAGES")));
$lAdmin->AddHeaders(array(
		array("id"=>"NAME", "content"=>GetMessage("MAIN_DUMP_FILE_NAME"), "sort"=>"name", "default"=>true),
		array("id"=>"SIZE","content"=>GetMessage("MAIN_DUMP_FILE_SIZE1"), "sort"=>"size", "default"=>true),
		$arAllBucket ? array("id"=>"PLACE","content"=>GetMessage("MAIN_DUMP_LOCATION"), "default"=>true) : null,
		array("id"=>"DATE", "content"=>GetMessage('MAIN_DUMP_FILE_TIMESTAMP'), "sort"=>"timestamp", "default"=>true)
));

while($Elem = $rsDirContent->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_NAME, $Elem);

	$c = $arParts[$f_NAME];
	if ($c > 1)
	{
		$parts = ' ('.GetMessage("MAIN_DUMP_PARTS").$c.')';
		$size = $arSize[$f_NAME];
	}
	else
	{
		$parts = '';
		$size = $f_SIZE;
	}

	$row->AddField("NAME", '<img src="/bitrix/images/fileman/types/'.CFileMan::GetFileTypeEx($f_NAME).'.gif" width="16" height="16" border=0 alt="">&nbsp;'.$f_NAME.$parts);
	$row->AddField("SIZE", HumanSize($size));
	$row->AddField("PLACE", $f_PLACE ? $f_PLACE : GetMessage("MAIN_DUMP_LOCAL"));
	$row->AddField("DATE", $f_DATE);

	$arActions = Array();

	if ($f_PERMISSION >= "R")
	{
		$arActions[] = array(
			"ICON" => "download",
			"DEFAULT" => true,
			"TEXT" => GetMessage("MAIN_DUMP_ACTION_DOWNLOAD"),
			"ACTION" => "AjaxSend('?action=download&f_id=".$f_NAME."&BUCKET_ID=".intval($f_BUCKET_ID)."&".bitrix_sessid_get()."')"
		);
		$arActions[] = array(
			"ICON" => "link",
			"TEXT" => GetMessage("MAIN_DUMP_GET_LINK"),
			"ACTION" => "AjaxSend('?action=copy&f_id=".$f_NAME."&BUCKET_ID=".intval($f_BUCKET_ID)."&".bitrix_sessid_get()."')"
		);
		$arActions[] = array(
			"ICON" => "restore",
			"TEXT" => GetMessage("MAIN_DUMP_RESTORE"),
			"ACTION" => "if(confirm('".GetMessage("MAIN_RIGHT_CONFIRM_EXECUTE")."')) AjaxSend('?action=restore&f_id=".$f_NAME."&BUCKET_ID=".intval($f_BUCKET_ID)."&".bitrix_sessid_get()."')"
		);

		if ($f_PERMISSION >= 'W')
		{
			if ($arWriteBucket && !$f_BUCKET_ID)
			{
				$arActions[] = array("SEPARATOR" => true);

				foreach($arWriteBucket as $f)
					$arActions[] = array(
						"ICON" => "clouds",
						"TEXT" => GetMessage("MAIN_DUMP_SEND_CLOUD").htmlspecialchars('"'.$f['BUCKET'].'"'),
						"ACTION" => "if(confirm('".GetMessage("MAIN_DUMP_SEND_FILE_CLOUD")."?')) AjaxSend('?f_id=".Urlencode($f_NAME)."&process=Y&action=cloud_send&dump_bucket_id=".$f['ID']."&".bitrix_sessid_get()."')"
					);
			}

			$arActions[] = array("SEPARATOR" => true);
			if (!$f_BUCKET_ID)
			{
				$arName = ParseFileName($f_NAME);
				$arActions[] = array(
					"ICON" => "rename",
					"TEXT" => GetMessage("MAIN_DUMP_RENAME"),
					"ACTION" => "if(name=prompt('".GetMessage("MAIN_DUMP_ARC_NAME_W_O_EXT")."','".htmlspecialchars($arName['name'])."')) tbl_dump.GetAdminList('/bitrix/admin/dump.php?ID=".Urlencode($f_NAME)."&action=rename&lang=".LANG."&".bitrix_sessid_get()."&BUCKET_ID=".intval($f_BUCKET_ID)."&name='+name);"
				);
			}
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("MAIN_DUMP_DELETE"),
				"ACTION" => "if(confirm('".GetMessage('MAIN_DUMP_ALERT_DELETE')."')) ".$lAdmin->ActionDoGroup($f_NAME, "delete", '&BUCKET_ID='.intval($f_BUCKET_ID))
			);
		}
	}
	$row->AddActions($arActions);
}

// "подвал" списка
$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $rsDirContent->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

$lAdmin->CheckListMode();


$APPLICATION->SetTitle(GetMessage("MAIN_DUMP_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$aTabs = array(
	array("DIV"=>"tab1", "TAB"=>GetMessage("TAB_STANDARD"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("TAB_STANDARD_DESC")),
	array("DIV"=>"tab2", "TAB"=>GetMessage("TAB_ADVANCED"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("TAB_ADVANCED_DESC")),
);
$editTab = new CAdminTabControl("editTab", $aTabs, true, true);

?>

<div id="dump_result_div"></div>
<? 
echo BeginNote();
echo GetMessage("MAIN_DUMP_HEADER_MSG");
echo EndNote();

CAdminFileDialog::ShowScript(Array
    (
	"event" => "__bx_select_dir",
	"arResultDest" => Array("FUNCTION_NAME" => "mnu_SelectValue"),
	"arPath" => Array('PATH'=>"/"),
	"select" => 'D',
	"operation" => 'O',
	"showUploadTab" => false,
	"showAddToMenuTab" => false,
	"allowAllFiles" => true,
	"SaveConfig" => true 
    )
);		
?>
<script language="JavaScript">
var numRows=0;
function AddTableRow()
{
	oTable = document.getElementById('skip_mask_table');
	numRows = oTable.rows.length;
	oRow = oTable.insertRow(-1);
	oCell = oRow.insertCell(0);
	oCell.innerHTML = '<input name="arMask[]" id="mnu_FILES_' + numRows  +'" size=30><input type="button" id="mnu_FILES_btn_' + numRows  + '" value="..." onclick="showMenu(this, '+ numRows  +')">';
}

var currentID;

function showMenu(div, id)
{
	currentID = id;
	__bx_select_dir();
}

function mnu_SelectValue(filename, path, site, title, menu)
{
	document.getElementById('mnu_FILES_' + currentID).value = path + (path == '/' ? '' : '/') + filename;
}

function CheckActiveStart()
{
	if (ob = document.fd1.dump_base)
	{
		document.fd1.dump_base_skip_stat.disabled = !ob.checked;
		document.fd1.dump_base_skip_search.disabled = !ob.checked;
		document.fd1.dump_base_skip_log.disabled = !ob.checked;
	}

	noFiles = !document.fd1.dump_file_public.checked && !document.fd1.dump_file_kernel.checked;
	document.fd1.max_file_size.disabled = noFiles;
	document.fd1.skip_symlinks.disabled = noFiles;
	document.fd1.skip_mask.disabled = noFiles;

	if (ob = document.fd1.dump_site_id)
		ob.disabled = !document.fd1.dump_file_public.checked;

	noMask = noFiles || !document.fd1.skip_mask.checked;

	oTable = document.getElementById('skip_mask_table');
	numRows = oTable.rows.length;
	for(i=0;i<numRows;i++)
	{
		document.getElementById('mnu_FILES_'+i).disabled = noMask;
		document.getElementById('mnu_FILES_btn_'+i).disabled = noMask;
	}

	<?
	if ($arAllBucket)
	{
		foreach($arAllBucket as $arBucket)
			echo 'noFiles = noFiles && !document.getElementById("dump_cloud_'.$arBucket['ID'].'").checked'."\n";
	}
	?>

	document.getElementById('more_button').disabled = noMask;
	document.getElementById('start_button').disabled = noFiles && !document.fd1.dump_base.checked;
}

function CheckActiveMode()
{
	standard = document.fd1.dump_file_public.checked && document.fd1.dump_file_kernel.checked && (document.fd1.max_file_size.value == 0) && !document.fd1.skip_symlinks.checked && !document.fd1.skip_mask.checked && document.fd1.dump_integrity_check.checked;

	if (standard && (ob_dump = document.fd1.dump_base))
		standard = ob_dump.checked && !document.fd1.dump_base_skip_stat.checked && !document.fd1.dump_base_skip_search.checked && !document.fd1.dump_base_skip_log.checked;

	if (!standard)
		return;

	if (document.fd1.dump_max_exec_time.value == 20 && document.fd1.dump_max_exec_time_sleep.value == 3 && !document.fd1.dump_disable_gzip.checked)
		document.getElementById('shared_profile').checked = true;
	else if (document.fd1.dump_max_exec_time.value == 45 && document.fd1.dump_max_exec_time_sleep.value == 0 && !document.fd1.dump_disable_gzip.checked)
		document.getElementById('vps_profile').checked = true;
	else if (document.fd1.dump_max_exec_time.value == 15 && document.fd1.dump_max_exec_time_sleep.value == 10 && document.fd1.dump_disable_gzip.checked)
		document.getElementById('slow_profile').checked = true;

	CheckActiveStart();
}

BX.ready(function()
	{
		CheckActiveMode();
		<?=$arAllBucket ? 'RefreshList();' : ''?>
	}
);

function SetMode(ob)
{
	document.fd1.dump_file_public.checked = true;
	document.fd1.dump_file_kernel.checked = true;
	document.fd1.max_file_size.value = 0;
	document.fd1.skip_symlinks.checked = false;
	document.fd1.skip_mask.checked = false;

	if (ob_dump = document.fd1.dump_base)
	{
		ob_dump.checked = true;
		document.fd1.dump_base_skip_stat.checked = false;
		document.fd1.dump_base_skip_search.checked = false;
		document.fd1.dump_base_skip_log.checked = false;
	}


	switch (ob.value)
	{
		case 'shared':
			document.fd1.dump_max_exec_time.value = 20;
			document.fd1.dump_max_exec_time_sleep.value = 3;
			document.fd1.dump_disable_gzip.checked = false;
			document.fd1.dump_integrity_check.checked = true;
		break;
		case 'vps':
			document.fd1.dump_max_exec_time.value = 45;
			document.fd1.dump_max_exec_time_sleep.value = 0;
			document.fd1.dump_disable_gzip.checked = false;
			document.fd1.dump_integrity_check.checked = true;
		break;
		case 'slow':
		default:
			document.fd1.dump_max_exec_time.value = 15;
			document.fd1.dump_max_exec_time_sleep.value = 10;
			document.fd1.dump_disable_gzip.checked = true;
			document.fd1.dump_integrity_check.checked = true;
		break;
	}
	CheckActiveStart();
}

var stop;
function StartDump()
{
	queryString='?lang=<?echo htmlspecialchars(LANG)?>';
	queryString+='&process=Y';
	queryString+='&action=start';

	queryString+='&dump_max_exec_time=' + document.fd1.dump_max_exec_time.value;
	queryString+='&dump_max_exec_time_sleep=' + document.fd1.dump_max_exec_time_sleep.value;

	if (document.fd1.dump_disable_gzip.checked)
		queryString += '&dump_disable_gzip=Y';

	if (document.fd1.dump_integrity_check.checked)
		queryString += '&dump_integrity_check=Y';
	
	if (ob = document.fd1.dump_bucket_id)
		queryString += '&dump_bucket_id=' + ob.value;

	if(document.fd1.dump_file_public.checked)
		queryString +='&dump_file_public=Y';

	if(document.fd1.dump_file_kernel.checked)
			queryString+='&dump_file_kernel=Y';

	if(document.fd1.skip_symlinks.checked)
			queryString+='&skip_symlinks=Y';

	if(document.fd1.skip_mask.checked)
	{
		queryString+='&skip_mask=Y';

		oTable = document.getElementById('skip_mask_table');
		numRows = oTable.rows.length;

		for(i=0;i<numRows;i++)
			queryString+='&arMask[]=' + document.getElementById('mnu_FILES_'+i).value;
	}

	if(document.fd1.dump_file_public.checked || document.fd1.dump_file_kernel.checked)
		queryString+='&max_file_size=' + document.fd1.max_file_size.value;

	if(document.fd1.dump_base.checked)
	{
		queryString +='&dump_base=Y';

		if(document.fd1.dump_base_skip_stat.checked)
			queryString +='&dump_base_skip_stat=Y';
		if(document.fd1.dump_base_skip_search.checked)
			queryString +='&dump_base_skip_search=Y';
		if(document.fd1.dump_base_skip_log.checked)
			queryString +='&dump_base_skip_log=Y';
	}

	if (ob = document.fd1.dump_site_id)
		queryString += '&dump_site_id=' + ob.value;

	<?
	if ($arAllBucket)
	{
		foreach($arAllBucket as $arBucket)
		{
		?>
			if (document.getElementById('dump_cloud_<?=$arBucket['ID']?>').checked)
				queryString += '&dump_cloud[<?=$arBucket['ID']?>]=Y';
		<?
		}
	}
	?>

	queryString += '&<?=bitrix_sessid_get()?>';

	document.getElementById('dump_result_div').innerHTML='';
	AjaxSend(queryString);
}

function EndDump()
{
	stop = true;
	document.getElementById('stop_button').disabled = true;
	document.getElementById('start_button').disabled = false;
}

function AjaxSend(url)
{
	stop = false;
	document.getElementById('stop_button').disabled=false;
	document.getElementById('start_button').disabled=true;

	ShowWaitWindow();
	CHttpRequest.Action = function(result)
	{
		CloseWaitWindow();
		if (stop)
			RefreshList();
		else
			document.getElementById('dump_result_div').innerHTML = result;
	}
	CHttpRequest.Send(url);
}

function RefreshList()
{
	tbl_dump.GetAdminList('/bitrix/admin/dump.php?lang=<?=LANG?>&read_clouds=Y');
}
</script>


	<form name="fd1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANG?>" method="GET">
	<?
	$editTab->Begin();
	$editTab->BeginNextTab();
	?>

	<tr>
		<td colspan=2 align=center><? 
		echo BeginNote();
		echo GetMessage('MODE_DESC');
		echo EndNote();
		?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="radio" name=arc_profile value=shared id='shared_profile' onclick="SetMode(this)"> <?=GetMessage('MODE_SHARED')?></label></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="radio" name=arc_profile value=vps id='vps_profile' onclick="SetMode(this)"> <?=GetMessage('MODE_VPS')?></label></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="radio" name=arc_profile value=slow id='slow_profile' onclick="SetMode(this)"> <?=GetMessage('MODE_SLOW')?></label></td>
	</tr>
	<?
	$editTab->BeginNextTab();

	if ($arWriteBucket)
	{
	?>
		<tr class="heading">
			<td colspan="2"><?=GetMessage("MAIN_DUMP_CLOUD_STORAGES")?></td>
		</tr>
		<tr>
			<td><?=GetMessage("MAIN_DUMP_ARC_LOCATION")?></td>
			<td>
				<select name=dump_bucket_id>
				<?
					echo '<option value="0">'.GetMessage("MAIN_DUMP_LOCAL_DISK").'</option>';
					foreach($arWriteBucket as $f)
						echo '<option value="'.$f['ID'].'" '.(IntOption("dump_bucket_id") == $f['ID'] ? "selected" : "").'>'.htmlspecialchars($f['BUCKET'].' ('.$f['SERVICE_ID'].')').'</option>';
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td valign=top><?=GetMessage("MAIN_DUMP_ARC_FROM_CLOUD")?></td>
			<td>
				<?
					foreach($arAllBucket as $arBucket)
						echo '<div><label><input type="checkbox" id="dump_cloud_'.$arBucket['ID'].'" OnClick="CheckActiveStart()" '.(IntOption("dump_cloud_".$arBucket['ID']) ? "checked" : "").'> '.htmlspecialchars($arBucket['BUCKET'].' ('.$arBucket['SERVICE_ID'].')').'</label></div>';
				?>
			</td>
		</tr>
	<?
	}
	?>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("MAIN_DUMP_FILE_TITLE")?></td>
	</tr>
	<tr>
		<td style="width:50%"><?echo GetMessage("MAIN_DUMP_FILE_KERNEL")?></td>
		<td><input type="checkbox" name="dump_file_kernel" value="Y" OnClick="CheckActiveStart()" <?=IntOption("dump_file_kernel") ? "checked" : ''?>></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_FILE_PUBLIC")?></td>
		<td><input type="checkbox" name="dump_file_public" value="Y" OnClick="CheckActiveStart()" <?=IntOption("dump_file_public") ? "checked" : ''?>></td>
	</tr>
	<?
	$arSitePath = array();
	$res = CSite::GetList($by='sort', $order='asc', array('ACTIVE'=>'Y'));
	while($f = $res->Fetch())
		$arSitePath[$f['ABS_DOC_ROOT'].rtrim($f['DIR'],'/')] = array($f['ID'] => '['.$f['ID'].'] '.$f['NAME']);

	if (count($arSitePath) > 1)
	{
	?>
	<tr>
		<td><?=GetMessage('PUBLIC_PART')?></td>
		<td>
			<select name=dump_site_id>
			<?
				foreach($arSitePath as $path => $val)
				{
					$path = rtrim(str_replace('\\','/',$path),'/');
					list($k,$v) = each($val);
					echo '<option value="'.htmlspecialchars($k).'"'.($path == DOCUMENT_ROOT ? ' selected' : '').'>'.htmlspecialchars($v).'</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<?
	}
	?>
	<?
		$bNoFiles = !CheckDumpFiles();
	?>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE")?></td>
		<td><input type="text" name="max_file_size" size="10" value="<?=IntOption("dump_max_file_size")?>" <?=CheckDumpFiles() ? '' : "disabled"?>>
		<?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE_kb")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_SKIP_SYMLINKS")?></td>
		<td><input type="checkbox" name="skip_symlinks" <?=$bNoFiles?'disabled':''?> value="Y" <?=IntOption("skip_symlinks") ? "checked" : ''?>></td>
	</tr>
	<? $bMask = IntOption("skip_mask"); ?>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_MASK")?><span class="required"><sup>1</sup></span></td>
		<td><input type="checkbox" name="skip_mask" <?=$bNoFiles?'disabled':''?> value="Y" <?=$bMask?" checked":'';?> onclick="CheckActiveStart()">
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<table id="skip_mask_table" cellspacing=0 cellpadding=0>
			<?
			$i=-1;

			$res = unserialize(COption::GetOptionString("main","skip_mask_array"));
			$skip_mask_array = is_array($res)?$res:array();

			foreach($skip_mask_array as $mask)
			{
				$i++;
				echo
				'<tr><td>
					<input name="arMask[]" id="mnu_FILES_'.$i.'" value="'.htmlspecialchars($mask).'" '.(!$bMask||$bNoFiles?'disabled':'').' size=30>'.
					'<input type="button" id="mnu_FILES_btn_'.$i.'" '.(!$bMask||$bNoFiles?'disabled':'').' value="..." onclick="showMenu(this, \''.$i.'\')">'.
				'</tr>';
			}
			$i++;
			?>
				<tr><td><input name="arMask[]" id="mnu_FILES_<?=$i?>" size=30 <?=!$bMask||$bNoFiles?'disabled':'';?>><input type="button" id="mnu_FILES_btn_<?=$i?>" value="..." onclick="showMenu(this, '<?=$i?>')" <?=!$bMask||$bNoFiles?'disabled':'';?>></tr>
			</table>
			<input type=button id="more_button" value="<?=GetMessage('MAIN_DUMP_MORE')?>" onclick="AddTableRow()" <?=(!$bMask||$bNoFiles?'disabled':'')?>>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?echo GetMessage("MAIN_DUMP_BASE_TITLE")?></td>
	</tr>
	<?
	if ($DB->type != 'MYSQL') 
	{
		$strDisableNotMysql = 'disabled';
	?>
	<tr>
		<td colspan=2 align=center><? 
		echo BeginNote();
		echo GetMessage('MAIN_DUMP_MYSQL_ONLY');
		echo EndNote();
		?></td>
	</tr>
	<? 
	} else 
		$strDisableNotMysql = '';
	?>
	<tr>
		<td><?echo GetMessage("MAIN_DUMP_BASE_TRUE")?></td>
		<td><input type="checkbox" <?=$strDisableNotMysql?> name="dump_base" OnClick="CheckActiveStart()" <?=IntOption("dump_base") ? "checked" : "" ?>><?= " (".getTableSize("")." ".GetMessage("MAIN_DUMP_BASE_SIZE").") " ;?>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("MAIN_DUMP_BASE_IGNORE")?></td>
		<td><label><input type="checkbox" <?=$strDisableNotMysql?> name="dump_base_skip_stat" <?=IntOption("dump_base_skip_stat") ? "checked" : "" ?>> <? echo GetMessage("MAIN_DUMP_BASE_STAT")." (".getTableSize("b_stat")." ".GetMessage("MAIN_DUMP_BASE_SIZE").")" ?></label>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="checkbox" <?=$strDisableNotMysql?> name="dump_base_skip_search" value="Y" <?=IntOption("dump_base_skip_search") ? "checked" : "" ?>> <? echo GetMessage("MAIN_DUMP_BASE_SINDEX")." (".getTableSize("b_search")." ".GetMessage("MAIN_DUMP_BASE_SIZE").")" ?></label>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input type="checkbox" <?=$strDisableNotMysql?> name="dump_base_skip_log" value="Y"<?=IntOption("dump_base_skip_log") ? "checked" : "" ?>> <? echo GetMessage("MAIN_DUMP_EVENT_LOG")." (".getTableSize("b_event_log")." ".GetMessage("MAIN_DUMP_BASE_SIZE").")" ?></label>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage('SERVER_LIMIT')?></td>
	</tr>
	<tr>
		<td><?=GetMessage('STEP_LIMIT')?></td>
		<td>
			<input name="dump_max_exec_time" value="<?=IntOption("dump_max_exec_time")?>" size=2>
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>,
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_SLEEP")?>
			<input name="dump_max_exec_time_sleep" value="<?=IntOption("dump_max_exec_time_sleep")?>" size=2>
			<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage('DISABLE_GZIP')?></td>
		<td><input type="checkbox" name="dump_disable_gzip" <?=IntOption('dump_use_compression') ? '' : 'checked' ?>>
	</tr>
	<tr>
		<td><?=GetMessage('INTEGRITY_CHECK_OPTION')?></td>
		<td><input type="checkbox" name="dump_integrity_check" <?=IntOption('dump_integrity_check') ? 'checked' : '' ?>>
	</tr>

	<?$editTab->Buttons();
	?>
	<input type="button" id="start_button" value="<?=GetMessage("MAIN_DUMP_FILE_DUMP_BUTTON")?>" <?=!CheckDumpFiles() && !CheckDumpClouds() && !IntOption("dump_base") ? "disabled" : ''?> OnClick="StartDump();">
	<input type="button" id="stop_button" value="<?=GetMessage("MAIN_DUMP_FILE_STOP_BUTTON")?>" OnClick="EndDump();" disabled>

	<?
	$editTab->End();
	?>
	</form>

<?
$lAdmin->DisplayList();

echo BeginNote();
echo '<span class=required><sup>1</sup></span> '.GetMessage("MAIN_DUMP_FOOTER_MASK");
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");





#################################################
################## FUNCTIONS
function skipMask($abs_path)
{
	if (!IntOption('skip_mask'))
		return false;

	global $skip_mask_array;
	
	$path = substr($abs_path,strlen(DOCUMENT_ROOT_SITE));
	$path = str_replace('\\','/',$path);
	
	static $preg_mask_array;
	if (!$preg_mask_array)
		$preg_mask_array = prepare_preg_escape($skip_mask_array);

	reset($skip_mask_array);
	foreach($skip_mask_array as $k => $mask)
	{
		if (strpos($mask,'/')===0) // absolute path
		{
			if (strpos($mask,'*') === false) // нет звездочки 
			{
				if (strpos($path.'/',$mask.'/') === 0)
					return true;
			}
			elseif (preg_match('#^'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
				return true;
		}
		elseif (strpos($mask, '/')===false)
		{
			if (strpos($mask,'*')===false)
			{
				if (substr($path,-strlen($mask)) == $mask)
					return true;
			}
			elseif (preg_match('#/[^/]*'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
				return true;
		}
	}
}

function prepare_preg_escape($skip_mask_array)
{
	static $res;
	if (!isset($res))
		foreach($skip_mask_array as $a)
			$res[] = _preg_escape($a); 
	return $res;
}

function _preg_escape($str)
{
	$search = array('#','[',']','.','?','(',')','^','$','|','{','}');
	$replace = array('\#','\[','\]','\.','\?','\(','\)','\^','\$','\|','\{','\}');
	return str_replace($search, $replace, $str);
}

function createTable($table_name, $drop = true)
{
	global $DB, $com_marker;
	$sql = "SHOW CREATE TABLE `".$table_name."`";

	$res = $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	$row = $res->Fetch();

	$com = "\n\n";
	$com .= $com_marker. " --------------------------------------------------------" ."\n";
	$com .= $com_marker. " \n";
	$com .= $com_marker. " Table structure for table `".$table_name."`\n";
	$com .= $com_marker. " \n";
	$com .= "\n";

	$string = $row['Create Table'];
//	$string = preg_replace('#collate [a-z0-9_]+#i','',$string);

	return $com."\n\n\n".($drop ? "DROP TABLE IF EXISTS `".$table_name."`;\n".$string : str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$string)).';';
}

function getData($table, $file, $row_count, $last_row = 0, $mem)
{
	global $DB, $com_marker;
	$dump = "";
	$step = "";

	$com = "\n" .$com_marker. " \n";
	$com .= $com_marker. " Dumping data for table  `".$table."`\n";
	$com .= $com_marker. " \n";
	$com .= "\n";

	fwrite($file, $com."\n");

	$sql = "SHOW COLUMNS FROM `$table`";
	$res = $DB->Query($sql);
	$num = Array();
	$i = 0;

	//Определяем тип поля
	while($row = $res->Fetch())
	{
		if(preg_match("/^(\w*int|year|float|double|decimal)/", $row["Type"]))
			$meta[$i] = 0;
		elseif(preg_match("/^(\w*binary)/", $row["Type"]))
		{
			$meta[$i] = 1;
		} else
			$meta[$i] = 2;
		$i++;
	}

	$sql = "SHOW TABLE STATUS LIKE '$table'";
	$res = $DB->Query($sql);
	$tbl_info = $res->Fetch();
	$step = 1+round($mem * 1048576 * 0.5 / ($tbl_info["Avg_row_length"] + 1));

	$DB->Query("LOCK TABLE `$table` WRITE",true);
	while(($last_row <= ($row_count-1)) && haveTime())
	{
		$sql = "SELECT * FROM `$table` LIMIT $last_row, $step";
		$res = $DB->Query($sql);

		while($row = $res->Fetch())
		{
			$i = 0;
			foreach($row as $key => $val)
			{
				if (!isset($val) || is_null($val))
						$row[$key] = 'NULL';
				else
					switch($meta[$i])
					{
						case 0:
							$row[$key] = $val;
						break;
						case 1:
							if (empty($val) && $val != '0')
								$row[$key] = '\'\'';
							else
								$row[$key] = '0x' . bin2hex($val);
						break;
						case 2:
							$row[$key] = "'".$DB->ForSql($val)."'";
						break;
					}
				$i++;
			}
			fwrite($file, "INSERT INTO `".$table."` VALUES (".implode(",", $row).");\n");
		}
		$last_row += $step;
	}
	$DB->Query("UNLOCK TABLES",true);

	if($last_row >= ($row_count-1))
		return -1;
	else
		return $last_row;
}

function ignorePath($path)
{
	## Ignore paths
	$ignore_path = array(
		BX_PERSONAL_ROOT."/cache",
		BX_PERSONAL_ROOT."/cache_image",
		BX_PERSONAL_ROOT."/managed_cache",
		BX_PERSONAL_ROOT."/managed_flags",
		BX_PERSONAL_ROOT."/stack_cache",
		BX_PERSONAL_ROOT."/html_pages",
		BX_PERSONAL_ROOT."/tmp",
		BX_ROOT."/tmp",
		BX_ROOT."/help",
		BX_ROOT."/updates",
	);

	foreach($ignore_path as $value)
		if(DOCUMENT_ROOT_SITE.$value == $path)
			return true;

	## Clouds
	$clouds = DOCUMENT_ROOT_SITE.BX_ROOT.'/backup/clouds/';
	if (strpos($path, $clouds) === 0 || strpos($clouds, $path) === 0)
		return false;
	
	## Backups
	if (strpos($path, DOCUMENT_ROOT_SITE.BX_ROOT.'/backup/') === 0)
		return true;

	## Symlinks
	if (IntOption("skip_symlinks") && is_dir($path) && is_link($path))
		return true;

	## File size
	if (IntOption("dump_max_file_size") > 0 && filesize($path) > $max_file_size * 1024)
		return true;

	## Skip mask	
	if (skipMask($path))
		return true;

	## Kernel vs Public
	$dump_file_public = IntOption('dump_file_public');
	$dump_file_kernel = IntOption('dump_file_kernel');

	if ($dump_file_public == $dump_file_kernel)
		return !$dump_file_public;

	$path_kernel = array(
		"/bitrix/admin",
		"/bitrix/activities/bitrix",
		"/bitrix/components/bitrix",
		"/bitrix/gadgets/bitrix",
		"/bitrix/js",
		"/bitrix/images",
		"/bitrix/image_uploader",
		"/bitrix/license_key.php",
		"/bitrix/modules",
		"/bitrix/php_interface",
		"/bitrix/sounds",
		"/bitrix/themes/.default",
		"/bitrix/tools",
		"/bitrix/wizards/bitrix",
	);

	// если публичка и админка 
	foreach($path_kernel as $value)
	{
		if (strpos($path, DOCUMENT_ROOT_SITE.$value) === 0) // мы в ядре
			return !$dump_file_kernel;
		elseif ($dump_file_kernel && strpos(DOCUMENT_ROOT_SITE.$value, $path) === 0) // нужно ядро и мы на пути к ядру
			return false;
	}
	return !$dump_file_public; // мы в публичке
}

function BaseDump($arc_name="", $tbl_num, $start_row)
{
	global $DB;

	$ret = array();
	$last_row = $start_row;
	$mem = 32; // Minimum required value

	$sql = "SHOW TABLES;";
	$res = $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	$ptab = Array();
	while($row = $res->Fetch())
	{
		$ar = each($row);
		$table = $ar[1];
		$ptab[] = $table;
	}

	$f = fopen($arc_name,"a");
	$i = $tbl_num;

	$dump = "";

	while($i <= (count($ptab) - 1) && haveTime())
	{
		if (strlen($ptab[$i]))
		{
			if($last_row == -1)
			{
				$table = $ptab[$i];
				$drop = !IntOption('dump_base_skip_stat') || !preg_match("#^b_stat#i",$table); // если не переносим статистику, то не удаляем старую статистику при восстановлении 
				$dump = createTable($ptab[$i], $drop);
				fwrite($f, $dump."\n");
				$next = false;
				$ret["num"] = $i;
				$ret["st_row"] = 0;
				$last_row = 0;
			}

			$res = $DB->Query("SELECT count(*) as count FROM `$ptab[$i]`");
			$row_count = $res->Fetch();

			if($row_count["count"] > 0)
			{
				if($ptab[$i] == 'b_xml_tree')
					$row_next = -1;
				elseif(IntOption('dump_base_skip_stat') && preg_match('#^b_stat#i',$ptab[$i]))
					$row_next = -1;
				elseif(IntOption('dump_base_skip_search') && preg_match("#^(b_search_content_site|b_search_content_group|b_search_content_stem|b_search_content_title|b_search_tags|b_search_content_freq|b_search_content|b_search_suggest)$#i",$ptab[$i]))
					$row_next = -1;
				elseif(IntOption('dump_base_skip_log') && $ptab[$i] == 'b_event_log')
					$row_next = -1;
				else
					$row_next = getData($ptab[$i], $f, $row_count["count"], $last_row, $mem);
			}
			else
				$row_next = -1;

			if($row_next == -1)
			{
				$ret["num"] = ++$i;
				$ret["st_row"] = -1;
				$last_row = -1;
			}
			else
			{
				$last_row = $row_next;
				$ret["num"] = $i;
				$ret["st_row"] = $last_row;
			}
		}
	}

	fclose($f);

	if(!($i <= (count($ptab) - 1)))
		$ret["end"] = true;

	return $ret;
}

function getTableSize($prefix)
{
	global $DB;
	if ($DB->type != 'MYSQL')
		return 0;
	$size = 0;

	$sql = "SHOW TABLE STATUS LIKE '".$DB->ForSql($prefix)."%'";
	$res = $DB->Query($sql);

	while($row = $res->Fetch())
		$size += $row["Data_length"];

	return round($size/(1048576), 2);
}

class CDirScan
{
	var $DirCount = 0;
	var $FileCount = 0;
	var $err= array();

	var $bFound = false;
	var $nextPath = '';
	var $startPath = '';
	var $arIncludeDir = false;

	function __construct()
	{
	}

	function ProcessDirBefore($f)
	{
		return true;
	}

	function ProcessDirAfter($f)
	{
		return true;
	}

	function ProcessFile($f)
	{
		return true;
	}

	function Skip($f)
	{
		if ($this->startPath)
		{
			if (strpos($this->startPath.'/', $f.'/') === 0)
			{
				if ($this->startPath == $f)
					unset($this->startPath);
				return false;
			}
			else
				return true;
		}
		return false;
	}

	function Scan($dir)
	{
		$dir = str_replace('\\','/',$dir);

		if ($this->Skip($dir))
			return;

		$this->nextPath = $dir;

		if (is_dir($dir))
		{
		#############################
		# DIR
		#############################
			if (!$this->startPath) // если начальный путь найден или не задан
			{
				$r = $this->ProcessDirBefore($dir);
				if ($r === false)
				{
					$this->err[] = GetMessage('CDIR_FOLDER_ERROR').$dir;
					return false;
				}
			}

			if (!($handle = opendir($dir)))
			{
				$this->err[] = GetMessage('CDIR_FOLDER_OPEN_ERROR').$dir;
				return false;
			}

			while (($item = readdir($handle)) !== false)
			{
				if ($item == '.' || $item == '..')
					continue;

				$f = $dir."/".$item;
				$r = $this->Scan($f);
				if ($r === false || $r === 'BREAK')
				{
					closedir($handle);
					return $r;
				}
			}
			closedir($handle);

			if (!$this->startPath) // если начальный путь найден или не задан
			{
				if ($this->ProcessDirAfter($dir) === false)
				{
					$this->err[] = GetMessage('CDIR_FOLDER_ERROR').$dir;
					return false;
				}
				$this->DirCount++;
			}
		}
		else 
		{
		#############################
		# FILE
		#############################
			$r = $this->ProcessFile($dir);
			if ($r === false)
			{
				$this->err[] = GetMessage('CDIR_FILE_ERROR').$dir;
				return false;
			}
			elseif ($r === 'BREAK') // если файл обработан частично
				return $r;
			$this->FileCount++;
		}
		return true;
	}
}

class CDirRealScan extends CDirScan
{
	function ProcessFile($f)
	{
		global $tar;
		while(haveTime())
		{
			if ($tar->addFile($f) === false)
				return false; // error
			if ($tar->ReadBlockCurrent == 0)
				return true; // finished
		}
		return 'BREAK';
	}

	function ProcessDirBefore($f)
	{
		global $tar;
		return $tar->addFile($f);
	}

	function Skip($f)
	{
		if ($this->startPath)
		{
			if (strpos($this->startPath.'/', $f.'/') === 0)
			{
				if ($this->startPath == $f)
					unset($this->startPath);
				return false;
			}
			else
				return true;
		}
		
		$res = ignorePath($f);
//		echo $f.' <font color=red>'.$res.'</font> <br>';
		return $res;
	}

}

class CTar
{
	var $gzip;
	var $file;
	var $err = array();
	var $res;
	var $Block = 0;
	var $BlockHeader;
	var $path;
	var $FileCount = 0;
	var $DirCount = 0;
	var $ReadBlockMax = 2000;
	var $ReadBlockCurrent = 0;
	var $header = null;
	var $ArchiveSizeMax;
	const BX_EXTRA = 'BX0000';

	##############
	# READ
	# {
	function openRead($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		return $this->open($file, 'r');
	}

	function readBlock()
	{
		$str = $this->gzip ? gzread($this->res,512) : fread($this->res,512);
		if (!$str && $this->openNext())
			$str = $this->gzip ? gzread($this->res,512) : fread($this->res,512);

		if ($str)
			$this->Block++;

		return $str;
	}

	function SkipFile()
	{
		$this->Skip(ceil($this->header['size']/512));
		$this->header = null;
	}

	function Skip($Block = 0)
	{
		if (!$Block)
			return false;
		$pos = $this->gzip ? gztell($this->res) : ftell($this->res);
		if (file_exists($this->getNextName()))
		{
			while(($BlockLeft = ($this->getArchiveSize($this->file) - $pos)/512) < $Block)
			{
				if ($BlockLeft != floor($BlockLeft))
					return false; // invalid file size
				$this->Block += $BlockLeft;
				$Block -= $BlockLeft;
				if (!$this->openNext())
					return false;
				$pos = 0;
			}
		}

		$this->Block += $Block;
		return 0 === ($this->gzip ? gzseek($this->res,$pos + $Block*512) : fseek($this->res,$pos + $Block*512));
	}

	function readHeader($Long = false)
	{
		$str = '';
		while(trim($str) == '')
			if (!strlen($str = $this->readBlock()))
				return 0; // finish
		if (!$Long)
			$this->BlockHeader = $this->Block - 1;

		if (strlen($str)!=512)
			return $this->Error('TAR_WRONG_BLOCK_SIZE',$this->Block.' ('.strlen($str).')');


		$data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix", $str);
		$chk = $data['devmajor'].$data['devminor'];

		if (!is_numeric(trim($data['checksum'])) || $chk!='' && $chk!=0)
			return $this->Error('TAR_ERR_FORMAT',($this->Block-1).'<hr>Header: <br>'.htmlspecialchars($str)); // быстрая проверка

		$header['filename'] = trim($data['prefix'].'/'.$data['filename'],'/');
		$header['mode'] = OctDec($data['mode']);
		$header['uid'] = OctDec($data['uid']);
		$header['gid'] = OctDec($data['gid']);
		$header['size'] = OctDec($data['size']);
		$header['mtime'] = OctDec($data['mtime']);
		$header['type'] = $data['type'];
//		$header['link'] = $data['link'];

		if (strpos($header['filename'],'./')===0)
			$header['filename'] = substr($header['filename'],2);

		if ($header['type']=='L') // Long header
		{
			$n = ceil($header['size']/512);
			for ($i = 0; $i < $n; $i++)
				$filename .= $this->readBlock();

			$header = $this->readHeader($Long = true);
			$header['filename'] = substr($filename,0,strpos($filename,chr(0)));
		}
		
		if (substr($header['filename'],-1)=='/') // trailing slash
			$header['type'] = 5; // Directory

		if ($header['type']=='5')
			$header['size'] = '';

		if ($header['filename']=='')
			return $this->Error('TAR_EMPTY_FILE',($this->Block-1));

		if (!$this->checkCRC($str, $data))
			return $this->Error('TAR_ERR_CRC',htmlspecialchars($header['filename']));

		$this->header = $header;

		return $header;
	}

	function checkCRC($str, $data)
	{
		$checksum = $this->checksum($str);
		$res = octdec($data['checksum']) == $checksum || $data['checksum']===0 && $checksum==256;
#		if (!$res)
#			var_dump(octdec($data['checksum']) .'=='. $checksum);
		return $res;
	}

	function extractFile()
	{
		if ($this->header === null)
		{
			if(($header = $this->readHeader()) === false || $header === 0 || $header === true)
			{
				if ($header === true)
					$this->SkipFile();
				return $header;
			}

			$this->lastPath = $f = $this->path.'/'.$header['filename'];
		
			if ($this->ReadBlockCurrent == 0)
			{
				if ($header['type']==5) // dir
				{
					if(!file_exists($f) && !self::xmkdir($f))
						return $this->Error('TAR_ERR_FOLDER_CREATE',htmlspecialchars($f));
					//chmod($f, $header['mode']);
				}
				else // file
				{
					if (!self::xmkdir($dirname = dirname($f)))
						return $this->Error('TAR_ERR_FOLDER_CREATE'.htmlspecialchars($dirname));
					elseif (($rs = fopen($f, 'wb'))===false)
						return $this->Error('TAR_ERR_FILE_CREATE',htmlspecialchars($f));
				}
			}
			else
				$this->Skip($this->ReadBlockCurrent);
		}
		else // файл уже частично распакован, продолжаем на том же хите
		{
			$header = $this->header;
			$this->lastPath = $f = $this->path.'/'.$header['filename'];
		}

		if ($header['type'] != 5) // пишем контент в файл 
		{
			if (!$rs)
			{
				if (($rs = fopen($f, 'ab'))===false)
					return $this->Error('TAR_ERR_FILE_OPEN',htmlspecialchars($f));
			}

			$i = 0;
			$FileBlockCount = ceil($header['size'] / 512);
			while(++$this->ReadBlockCurrent <= $FileBlockCount && ($contents = $this->readBlock()))
			{
				if ($this->ReadBlockCurrent == $FileBlockCount && ($chunk = $header['size'] % 512))
					$contents = substr($contents, 0, $chunk);

				fwrite($rs,$contents);

				if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
				{
					fclose($rs);
					return true; // Break
				}
			}
			fclose($rs);

			//chmod($f, $header['mode']);
			if (($s=filesize($f)) != $header['size'])
				return $this->Error('TAR_ERR_FILE_SIZE',htmlspecialchars($header['filename']).' (actual: '.$s.'  expected: '.$header['size'].')');
		}

		if ($this->header['type']==5)
			$this->DirCount++;
		else
			$this->FileCount++;

		$this->debug_header = $this->header;
		$this->BlockHeader = $this->Block;
		$this->ReadBlockCurrent = 0;
		$this->header = null;

		return true;
	}

	function extract()
	{
		while ($r = $this->extractFile());
		return $r === 0;
	}

	function openNext()
	{
		if (file_exists($file = $this->getNextName()))
		{
			$this->close();
			return $this->open($file,$this->mode);
		}
		else
			return false;
	}

	# }
	##############

	##############
	# WRITE 
	# {
	function openWrite($file)
	{
		if (!isset($this->gzip) && (substr($file,-3)=='.gz' || substr($file,-4)=='.tgz'))
			$this->gzip = true;

		if ($this->ArchiveSizeMax > 0)
		{
			while(file_exists($file1 = $this->getNextName($file)))
				$file = $file1;

			$size = 0;
			if (($size = $this->getArchiveSize($file)) >= $this->ArchiveSizeMax)
			{
				$file = $file1;
				$size = 0;
			}
			$this->ArchiveSizeCurrent = $size;
		}
		return $this->open($file, 'a');
	}

	// создадим пустой gzip с экстра полем
	function createEmptyGzipExtra($file)
	{
		if (file_exists($file))
			return false;

		if (!($f = gzopen($file,'wb')))
			return false;
		gzwrite($f,'');
		gzclose($f);

		$data = file_get_contents($file);

		if (!($f = fopen($file, 'w')))
			return false;

		$ar = unpack('A3bin0/A1FLG/A6bin1',substr($data,0,10));
		if ($ar['FLG'] != 0)
			return $this->Error('Error writing extra field: already exists');

		$EXTRA = chr(0).chr(0).chr(strlen(self::BX_EXTRA)).chr(0).self::BX_EXTRA;
		fwrite($f,$ar['bin0'].chr(4).$ar['bin1'].chr(strlen($EXTRA)).chr(0).$EXTRA.substr($data,10));
		fclose($f);
		return true;
	}

	function writeBlock($str)
	{
		$l = strlen($str);
		if ($l!=512)
			return $this->Error('TAR_WRONG_BLOCK_SIZE'.$l);

		if ($this->ArchiveSizeMax && $this->ArchiveSizeCurrent >= $this->ArchiveSizeMax)
		{
			$file = $this->getNextName();
			$this->close();

			if (!$this->open($file,$this->mode))
				return false;

			$this->ArchiveSizeCurrent = 0;
		}

		if ($res = $this->gzip ? gzwrite($this->res, $str) : fwrite($this->res,$str))
		{
			$this->Block++;
			$this->ArchiveSizeCurrent+=512;
		}

		return $res;
	}

	function writeHeader($ar)
	{
		$header0 = pack("a100a8a8a8a12a12", $ar['filename'], decoct($ar['mode']), decoct($ar['uid']), decoct($ar['gid']), decoct($ar['size']), decoct($ar['mtime']));
		$header1 = pack("a1a100a6a2a32a32a8a8a155", $ar['type'],'','','','','','', '', $ar['prefix']);

		$checksum = pack("a8",decoct($this->checksum($header0.'        '.$header1)));
		$header = pack("a512", $header0.$checksum.$header1);
		return $this->writeBlock($header) || $this->Error('TAR_ERR_WRITE_HEADER');
	}

	function addFile($f)
	{
		$f = str_replace('\\', '/', $f);
		$path = substr($f,strlen($this->path) + 1);
		if ($path == '')
			return true;
		if (strlen($path)>512)
			return $this->Error('TAR_PATH_TOO_LONG',htmlspecialchars($path));

		$ar = array();

		if (is_dir($f))
		{
			$ar['type'] = 5;
			$path .= '/';
		}
		else
			$ar['type'] = 0;

		$info = stat($f);
		if ($info)
		{
			if ($this->ReadBlockCurrent == 0) // read from start
			{
				$ar['mode'] = 0777 & $info['mode'];
				$ar['uid'] = $info['uid'];
				$ar['gid'] = $info['gid'];
				$ar['size'] = $ar['type']==5 ? 0 : $info['size'];
				$ar['mtime'] = $info['mtime'];


				if (strlen($path)>100) // Long header
				{
					$ar0 = $ar;
					$ar0['type'] = 'L';
					$ar0['filename'] = '././@LongLink';
					$ar0['size'] = strlen($path);
					if (!$this->writeHeader($ar0))
						return false;
					$path .= str_repeat(chr(0),512 - strlen($path));

					if (!$this->writeBlock($path))
						return false;
					$ar['filename'] = substr($path,0,100);
				}
				else
					$ar['filename'] = $path;

				if (!$this->writeHeader($ar))
					return false;
			}

			if ($ar['type']==0 && $info['size']>0) // File
			{
				if (!($rs = fopen($f, 'rb')))
					return $this->Error('TAR_ERR_FILE_READ',htmlspecialchars($f));

				if ($this->ReadBlockCurrent)
					fseek($rs, $this->ReadBlockCurrent * 512);

				$i = 0;
				while(!feof($rs) && ('' !== $str = fread($rs,512)))
				{
					$this->ReadBlockCurrent++;
					if (feof($rs) && ($l = strlen($str)) && $l < 512)
						$str .= str_repeat(chr(0),512 - $l);

					if (!$this->writeBlock($str))
					{
						fclose($rs);
						return $this->Error('TAR_ERR_FILE_WRITE',htmlspecialchars($f));
					}

					if ($this->ReadBlockMax && ++$i >= $this->ReadBlockMax)
					{
						fclose($rs);
						return true;
					}
				}
				fclose($rs);
				$this->ReadBlockCurrent = 0;
			}
			return true;
		}
		else
			return $this->Error('TAR_ERR_FILE_NO_ACCESS',htmlspecialchars($f));
	}

	# }
	##############

	##############
	# BASE 
	# {
	function open($file, $mode='r')
	{
		$this->file = $file;
		$this->mode = $mode;

		if ($this->gzip) 
		{
			if(!function_exists('gzopen'))
				return $this->Error('TAR_NO_GZIP');
			else
			{
				if ($mode == 'a' && !file_exists($file) && !$this->createEmptyGzipExtra($file))
					return false;
				$this->res = gzopen($file,$mode."b");
			}
		}
		else
			$this->res = fopen($file,$mode."b");

		return $this->res;
	}

	function close()
	{
		if ($this->gzip)
		{
			gzclose($this->res);

			// добавим фактический размер всех несжатых данных в extra поле
			if ($this->mode == 'a')
			{
				$f = fopen($this->file, 'rb+');
#				fseek($f, -4, SEEK_END);
				fseek($f, 18);
				fwrite($f, pack("V", $this->ArchiveSizeCurrent));
				fclose($f);
			}
		}
		else
			fclose($this->res);
	}

	function getNextName($file = '')
	{
		if (!$file)
			$file = $this->file;
		static $CACHE;
		$c = &$CACHE[$file];

		if (!$c)
		{
			$l = strrpos($file, '.');
			$num = substr($file,$l+1);
			if (is_numeric($num))
				$file = substr($file,0,$l+1).++$num;
			else
				$file .= '.1';
			$c = $file;
		}
		return $c;
	}

	function checksum($str)
	{
		static $CACHE;
		$checksum = &$CACHE[md5($str)];
		if (!$checksum)
		{
//			$str = pack("a512",$str);
			for ($i = 0; $i < 512; $i++)
				if ($i>=148 && $i<156)
					$checksum += 32; // ord(' ')
				else
					$checksum += ord($str[$i]);
		}
		return $checksum;
	}

	function getArchiveSize($file = '')
	{
		if (!$file)
			$file = $this->file;

		if (!file_exists($file))
			$size = 0;
		else
		{
			if ($this->gzip)
			{
				$f = fopen($file, "rb");
	#			fseek($f, -4, SEEK_END);
				fseek($f, 18);
				$size = end(unpack("V", fread($f, 4)));
				fclose($f);
			}
			else
				$size = filesize($file);
		}

		return $size;
	}

	function Error($err_code, $str = '')
	{
		$this->err[] = self::GetMessage($err_code).' '.$str;
		return false;
	}

	function xmkdir($dir)
	{
		if (!file_exists($dir))
		{
			$upper_dir = dirname($dir);
			if (!file_exists($upper_dir) && !self::xmkdir($upper_dir))
				return false;

			return mkdir($dir);
		}

		return is_dir($dir);
	}

	function GetMessage($code)
	{
		static $arLang;

		if (!$arLang)
		{
				$arLang = array(
					'TAR_WRONG_BLOCK_SIZE' => 'Wrong block size: ',
					'TAR_ERR_FORMAT' => 'Archive is corrupted, wrong block: ',
					'TAR_EMPTY_FILE' => 'Filename is empty, wrong block: ',
					'TAR_ERR_CRC' => 'Checksum error on file: ',
					'TAR_ERR_FOLDER_CREATE' => 'Can\'t create folder: ',
					'TAR_ERR_FILE_CREATE' => 'Can\'t create file: ',
					'TAR_ERR_FILE_OPEN' => 'Can\'t open file: ',
					'TAR_ERR_FILE_SIZE' => 'File size is wrong: ',
					'TAR_ERR_WRITE_HEADER' => 'Error writing header',
					'TAR_PATH_TOO_LONG' => 'Path is too long: ',
					'TAR_ERR_FILE_READ' => 'Error reading file: ',
					'TAR_ERR_FILE_WRITE' => 'Error writing file: ',
					'TAR_ERR_FILE_NO_ACCESS' => 'No access to file: ',
					'TAR_NO_GZIP' => 'Function &quot;gzopen&quot; is not available',
				);
		}
		return $arLang[$code];
	}

	# }
	##############
}

class CTarCheck extends CTar
{
	function extractFile()
	{
		if(($header = $this->readHeader()) === false || $header === 0)
			return $header;

		$this->SkipFile();
		return true;
	}
}

function haveTime()
{
	return microtime(true) - START_EXEC_TIME < IntOption("dump_max_exec_time");
}

function workTime()
{
	return microtime(true) - START_EXEC_TIME;
}

function HumanSize($num0, $show0 = false)
{
	$num = $num0;
	$i=0;
	$ar = array(GetMessage('MAIN_DUMP_FILE_MAX_SIZE_b'),GetMessage('MAIN_DUMP_FILE_MAX_SIZE_kb'),GetMessage('MAIN_DUMP_FILE_MAX_SIZE_mb'),GetMessage('MAIN_DUMP_FILE_MAX_SIZE_gb'));
	while($num > 1024)
	{
		$num /= 1024;
		$i++;
	}
	$num = round($num,1);
	return $num." ".$ar[$i].($show0 ? ' ('.$num0.')' : '');
}

function HumanTime($t)
{
	$ar = array(GetMessage('TIME_S'),GetMessage('TIME_M'),GetMessage('TIME_H'));
	if ($t < 60)
		return sprintf('%d '.$ar[0], $t);
	if ($t < 3600)
		return sprintf('%d '.$ar[1].' %d '.$ar[0], floor($t/60), $t%60);
	return sprintf('%d '.$ar[2].'%d '.$ar[1].' %d '.$ar[0], floor($t/3600), floor($t%3600/60), $t%60);
}

function GetBucketList($arFilter = array())
{
	if (CModule::IncludeModule('clouds'))
	{
		$arBucket = array();
		$rsData = CCloudStorageBucket::GetList(
			array("SORT"=>"DESC", "ID"=>"ASC")
//			array('ACTIVE'=>'Y','READ_ONLY'=>'N')
		);
		while($f = $rsData->Fetch())
		{
			if ($f['ACTIVE'] != 'Y' || ($f['READ_ONLY'] == 'Y' && $arFilter['READ_ONLY'] == 'Y'))
				continue; // sql filter currently is not supported TODO: remove in future

			$arBucket[] = $f;
		}
		return count($arBucket) ? $arBucket : false;
	}
	return false;
}

function RaiseErrorAndDie($strError)
{
	CAdminMessage::ShowMessage(array(
		"MESSAGE" => GetMessage("MAIN_DUMP_ERROR"),
		"DETAILS" =>  $strError,
		"TYPE" => "ERROR",
		"HTML" => true));
	echo '<script>EndDump();</script>';
	die();
}

function CheckDumpFiles()
{
	return IntOption("dump_file_public") || IntOption("dump_file_kernel");
}

function CheckDumpClouds()
{
	$arRes = array();
	if ($arAllBucket = GetBucketList())
	{
		foreach($arAllBucket as $arBucket)
			if (IntOption('dump_cloud_'.$arBucket['ID']))
				$arRes[] = $arBucket['ID'];
		if (count($arRes))
			return $arRes;
	}
	return false;
}

function IntOption($name)
{
	static $CACHE;
	if (!$CACHE[$name])
		$CACHE[$name] = COption::GetOptionInt("main", $name, 0);
	return $CACHE[$name];
}

function GetBucketFileList($BUCKET_ID, $path)
{
	static $CACHE;

	if ($CACHE[$BUCKET_ID])
		$obBucket = $CACHE[$BUCKET_ID];
	else
		$CACHE[$BUCKET_ID] = $obBucket = new CCloudStorageBucket($BUCKET_ID);

	if ($obBucket->Init())
		return $obBucket->ListFiles($path);
	return false;
}

class CloudDownload
{
	function __construct($id)
	{
		$this->id = $id;
		$this->last_bucket_path = '';
		$this->arSkipped = array();
		$this->path = '';
		$this->download_cnt = 0;
		$this->download_size = 0;

		$this->obBucket = new CCloudStorageBucket($id);
		if (!$this->obBucket->Init())
			return;
	}

	function Scan($path)
	{
		$this->path = $path;

		if ($arCloudFiles = GetBucketFileList($this->id, $path))
		{
			foreach($arCloudFiles['file'] as $k=>$file)
			{
				if ($this->last_bucket_path)
				{
					if ($path.'/'.$file == $this->last_bucket_path)
						$this->last_bucket_path = '';
					else
						continue;
				}

				$name = $this->path = $path.'/'.$file;
				if (!haveTime()) // Сохраняется путь файла, который еще предстоит сохранить, TODO: пошаговое скачивание больших файлов
					return false;

				$HTTP = new CHTTP;
				if ($HTTP->Download($this->obBucket->GetFileSRC(array("URN" => $name)), DOCUMENT_ROOT.BX_ROOT.'/backup/clouds/'.$this->id.$name))
				{
					$this->download_size += $arCloudFiles['file_size'][$k];
					$this->download_cnt++;
				}
				else
					$this->arSkipped[] = $name;
			}
		}

		foreach($arCloudFiles['dir'] as $dir)
		{
			if ($this->last_bucket_path)
			{
				if ($path.'/'.$dir == $this->last_bucket_path)
					$this->last_bucket_path = '';
				elseif (strpos($this->last_bucket_path, $path.'/'.$dir) !== 0)
					continue;
			}

			if ($path.'/'.$dir == '/bitrix/backup')
				continue;

			if ($path.'/'.$dir == '/tmp')
				continue;

			if (!$this->Scan($path.'/'.$dir)) // partial
				return false;
		}

		return true;
	}
}

function ParseFileName($name)
{
	if (preg_match('#^(.+)\.(tar.*)$#', $name, $regs))
		return array('name' => $regs[1], 'ext' => $regs[2]);
	elseif (preg_match('#^(.+)\.([^\.]+)$#', $name, $regs))
		return array('name' => $regs[1], 'ext' => $regs[2]);
	return array('name' => $name, 'ext' => '');
}
?>
