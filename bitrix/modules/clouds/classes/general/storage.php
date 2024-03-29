<?
/*.
	require_module 'standard';
	require_module 'pcre';
	require_module 'hash';
	require_module 'bitrix_main';
	require_module 'bitrix_clouds_classes_storage_bucket';
	require_module 'bitrix_clouds_classes_storage_service';
.*/
IncludeModuleLangFile(__FILE__);

class CCloudStorage
{
	const FILE_SKIPPED = 0;
	const FILE_MOVED = 1;
	const FILE_PARTLY_UPLOADED = 2;
	const FILE_UPLOAD_ERROR = 3;

	public static $part_size = 0;
	public static $part_count = 0;

	private static $_services = /*.(array[string]CCloudStorageService).*/null;

	/**
	 * @return void
	*/
	function _init()
	{
		if(!isset(self::$_services))
		{
			$obService = /*.(CCloudStorageService).*/null;
			self::$_services = /*.(array[string]CCloudStorageService).*/array();
			foreach(GetModuleEvents("clouds", "OnGetStorageService", true) as $arEvent)
			{
				$obService = ExecuteModuleEventEx($arEvent);
				if(is_object($obService))
					self::$_services[$obService->GetID()] = $obService;
			}
		}
	}

	/**
	 * @param string $ID
	 * @return CCloudStorageService
	*/
	function GetServiceByID($ID)
	{
		self::_init();
		if(array_key_exists($ID, self::$_services))
			return self::$_services[$ID];
		else
			return null;
	}

	/**
	 * @return array[string]CCloudStorageService
	*/
	function GetServiceList()
	{
		self::_init();
		return self::$_services;
	}

	/**
	 * @param string $ID
	 * @return array[string]string
	*/
	function GetServiceLocationList($ID)
	{
		$obService = CCloudStorage::GetServiceByID($ID);
		if(is_object($obService))
			return $obService->GetLocationList();
		else
			return /*.(array[string]string).*/array();
	}

	/**
	 * @param string $ID
	 * @return string
	*/
	function GetServiceDescription($ID)
	{
		$obService = CCloudStorage::GetServiceByID($ID);
		if(is_object($obService))
			return $obService->GetName();
		else
			return "";
	}

	/**
	 * @param array[string]string $arFile
	 * @param string $strFileName
	 * @return CCloudStorageBucket
	*/
	function FindBucketForFile($arFile, $strFileName)
	{
		if(array_key_exists("size", $arFile))
			$file_size = intval($arFile["size"]);
		else
			$file_size = intval($arFile["FILE_SIZE"]);

		foreach(CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			if($bucket["ACTIVE"] === "Y" && $bucket["READ_ONLY"] !== "Y")
			{
				foreach($bucket["FILE_RULES_COMPILED"] as $rule)
				{
					if(strlen($rule["MODULE_MASK"]))
						$bMatch = preg_match($rule["MODULE_MASK"], $arFile["MODULE_ID"]) > 0;
					else
						$bMatch = true;

					if(strlen($rule["EXTENTION_MASK"]))
						$bMatch = $bMatch && (preg_match($rule["EXTENTION_MASK"], $strFileName) > 0);

					if(empty($rule["SIZE_ARRAY"]))
					{
						$bMatchSize = true;
					}
					else
					{
						$bMatchSize = false;
						foreach($rule["SIZE_ARRAY"] as $size)
						{
							if(
								($size[0] === false || $file_size >= $size[0])
								&&  ($size[1] === false || $file_size <= $size[1])
							)
								$bMatchSize = true;
						}
					}

					$bMatch = $bMatch && $bMatchSize;

					if($bMatch)
						return new CCloudStorageBucket(intval($bucket["ID"]));
				}
			}
		}
		return null;
	}

	/**
	 * @param array[string]string $arFile
	 * @param array[string]string $arResizeParams
	 * @param array[string]mixed $callbackData
	 * @param bool $bNeedResize
	 * @param array[string]string $sourceImageFile
	 * @param array[string]string $cacheImageFileTmp
	 * @return bool
	*/
	function OnBeforeResizeImage($arFile, $arResizeParams, &$callbackData, &$bNeedResize, &$sourceImageFile, &$cacheImageFileTmp)
	{
		$callbackData = null;

		if(intval($arFile["HANDLER_ID"]) <= 0)
			return false;

		$obSourceBucket = new CCloudStorageBucket(intval($arFile["HANDLER_ID"]));
		if(!$obSourceBucket->Init())
			return false;

		$callbackData = /*.(array[string]mixed).*/array();
		$callbackData["obSourceBucket"] = $obSourceBucket;

		//Assume target bucket same as source
		$callbackData["obTargetBucket"] = $obTargetBucket = $obSourceBucket;

		//if original file bucket is read only
		if($obSourceBucket->READ_ONLY === "Y") //Try to find bucket with write rights
		{
			$bucket = CCloudStorage::FindBucketForFile($arFile, $arFile["FILE_NAME"]);
			if(!is_object($bucket))
				return false;
			if($bucket->Init())
			{
				$callbackData["obTargetBucket"] = $obTargetBucket = $bucket;
			}
		}

		$callbackData["cacheID"] = $arFile["ID"]."/".md5(serialize($arResizeParams));
		$callbackData["cacheOBJ"] = new CPHPCache;
		$callbackData["fileDIR"] = "/"."resize_cache/".$callbackData["cacheID"]."/".$arFile["SUBDIR"];
		$callbackData["fileNAME"] = $arFile["FILE_NAME"];
		$callbackData["fileURL"] = $callbackData["fileDIR"]."/".$callbackData["fileNAME"];

		if($callbackData["cacheOBJ"]->StartDataCache(3600, $callbackData["cacheID"], "clouds"))
		{
			//Check if it is cache file was deleted, but not the file in the cloud
			if($obTargetBucket->FileExists($callbackData["fileURL"]))
			{
				$callbackData["cacheSTARTED"] = true;
				$bNeedResize = false;
				return true;
			}
			else
			{
				$callbackData["tmpFile"] = CFile::GetTempName('', $arFile["FILE_NAME"]);
				$callbackData["tmpFile"] = preg_replace("#[\\\\\\/]+#", "/", $callbackData["tmpFile"]);
				if($obSourceBucket->DownloadToFile($arFile, $callbackData["tmpFile"]))
				{
					$callbackData["cacheSTARTED"] = true;
					$bNeedResize = true;
					$sourceImageFile = $callbackData["tmpFile"];
					$cacheImageFileTmp = CFile::GetTempName('', $arFile["FILE_NAME"]);
					return true;
				}
				else
				{
					$callbackData["cacheSTARTED"] = false;
					$bNeedResize = false;
					$callbackData["cacheOBJ"]->AbortDataCache();
					return false;
				}
			}
		}
		else
		{
			$callbackData["cacheSTARTED"] = false;
			$callbackData["cacheVARS"] = $callbackData["cacheOBJ"]->GetVars();
			$bNeedResize = false;
			return true;
		}
	}

	function OnAfterResizeImage($arFile, $arResizeParams, &$callbackData, &$cacheImageFile, &$cacheImageFileTmp, &$arImageSize)
	{
		global $arCloudImageSizeCache;

		if(!is_array($callbackData))
			return false;

		if($callbackData["cacheSTARTED"])
		{
			if(isset($callbackData["tmpFile"])) //have to upload to the cloud
			{
				$arFileToStore = CFile::MakeFileArray($cacheImageFileTmp);
				if($callbackData["obTargetBucket"]->SaveFile($callbackData["fileURL"], $arFileToStore))
				{
					$cacheImageFile = $callbackData["obTargetBucket"]->GetFileSRC($callbackData["fileURL"]);

					$arImageSize = CFile::GetImageSize($cacheImageFileTmp);
					$arImageSize[2] = filesize($cacheImageFileTmp);
					$iFileSize = filesize($arFileToStore["tmp_name"]);

					if(!is_array($arImageSize))
						$arImageSize = array(0, 0);
					$callbackData["cacheOBJ"]->EndDataCache(array(
						"cacheImageFile"=>$cacheImageFile,
						"width"=>$arImageSize[0],
						"height"=>$arImageSize[1],
						"size"=>$arImageSize[2],
					));

					unlink($callbackData["tmpFile"]);
					@rmdir(substr($callbackData["tmpFile"], 0, -strlen(bx_basename($callbackData["tmpFile"]))));

					$arCloudImageSizeCache[$cacheImageFile] = $arImageSize;

					$callbackData["obTargetBucket"]->IncFileCounter($iFileSize);

					return true;
				}
				else
				{
					unlink($callbackData["tmpFile"]);
					@rmdir(substr($callbackData["tmpFile"], 0, -strlen(bx_basename($callbackData["tmpFile"]))));

					unlink($cacheImageFileTmp);
					@rmdir(substr($cacheImageFileTmp, 0, -strlen(bx_basename($cacheImageFileTmp))));

					// $cacheImageFile not clear what to do
					return false;
				}

			}
			else //the file is already in the cloud
			{
				CFile::ScaleImage($arFile["WIDTH"], $arFile["HEIGHT"], $arResizeParams[0], $arResizeParams[1], $bNeedCreatePicture, $arSourceSize, $arDestinationSize);

				$cacheImageFile = $callbackData["obTargetBucket"]->GetFileSRC($callbackData["fileURL"]);
				$arImageSize = array(
					$arDestinationSize["width"],
					$arDestinationSize["height"],
					$callbackData["obTargetBucket"]->GetFileSize($callbackData["fileURL"]),
				);
				$callbackData["cacheOBJ"]->EndDataCache(array(
					"cacheImageFile"=>$cacheImageFile,
					"width"=>$arImageSize[0],
					"height"=>$arImageSize[1],
					"size"=>$arImageSize[2],
				));

				$arCloudImageSizeCache[$cacheImageFile] = $arImageSize;

				return true;
			}
		}
		elseif(is_array($callbackData["cacheVARS"]))
		{
			$cacheImageFile = $callbackData["cacheVARS"]["cacheImageFile"];
			$arImageSize = array(
				$callbackData["cacheVARS"]["width"],
				$callbackData["cacheVARS"]["height"],
				$callbackData["cacheVARS"]["size"],
			);
			$arCloudImageSizeCache[$cacheImageFile] = $arImageSize;
			return true;
		}

		return false;
	}

	function OnMakeFileArray($arSourceFile, &$arDestination)
	{
		if(!is_array($arSourceFile))
		{
			$file = $arSourceFile;
			if(substr($file, 0, strlen($_SERVER["DOCUMENT_ROOT"])) == $_SERVER["DOCUMENT_ROOT"])
				$file = ltrim(substr($file, strlen($_SERVER["DOCUMENT_ROOT"])), "/");

			if(!preg_match("/^http:\\/\\//", $file))
				return false;

			$bucket = CCloudStorage::FindBucketByFile($file);
			if(!is_object($bucket))
				return false;

			$filePath = substr($file, strlen($bucket->GetFileSRC("/"))-1);
			$filePath = urldecode($filePath);

			$target = CFile::GetTempName('', bx_basename($filePath));
			$target = preg_replace("#[\\\\\\/]+#", "/", $target);

			if($bucket->DownloadToFile($filePath, $target))
			{
				$arDestination = $target;
			}

			return true;
		}
		else
		{
			if($arSourceFile["HANDLER_ID"] <= 0)
				return false;

			$bucket = new CCloudStorageBucket($arSourceFile["HANDLER_ID"]);
			if(!$bucket->Init())
				return false;

			$target = CFile::GetTempName('', $arSourceFile["FILE_NAME"]);
			$target = preg_replace("#[\\\\\\/]+#", "/", $target);

			if($bucket->DownloadToFile($arSourceFile, $target))
			{
				$arDestination["name"] = (strlen($arSourceFile['ORIGINAL_NAME'])>0? $arSourceFile['ORIGINAL_NAME']: $arSourceFile['FILE_NAME']);
				$arDestination["size"] = $arSourceFile['FILE_SIZE'];
				$arDestination["type"] = $arSourceFile['CONTENT_TYPE'];
				$arDestination["description"] = $arSourceFile['DESCRIPTION'];
				$arDestination["tmp_name"] = $target;
			}

			return true;
		}
	}

	function OnFileDelete($arFile)
	{
		if($arFile["HANDLER_ID"] <= 0)
			return false;

		$bucket = new CCloudStorageBucket($arFile["HANDLER_ID"]);
		if((!$bucket->Init()) || ($bucket->READ_ONLY === "Y"))
			return false;

		$result = $bucket->DeleteFile("/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"]);
		if($result)
			$bucket->DecFileCounter($arFile["FILE_SIZE"]);

		$path = '/resize_cache/'.$arFile["ID"]."/";
		$arCloudFiles = $bucket->ListFiles($path, true);
		if(is_array($arCloudFiles["file"]))
		{
			foreach($arCloudFiles["file"] as $i => $file_name)
			{
				$tmp = $bucket->DeleteFile($path.$file_name);
				if($tmp)
					$bucket->DecFileCounter($arCloudFiles["file_size"][$i]);
			}
		}

		return $result;
	}

	function DeleteDirFilesEx($path)
	{
		$path = rtrim($path, "/")."/";
		foreach(CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			$obBucket = new CCloudStorageBucket($bucket["ID"]);
			if($obBucket->Init())
			{
				$arCloudFiles = $obBucket->ListFiles($path, true);
				if(is_array($arCloudFiles["file"]))
				{
					foreach($arCloudFiles["file"] as $i => $file_name)
					{
						$tmp = $obBucket->DeleteFile($path.$file_name);
						if($tmp)
							$obBucket->DecFileCounter($arCloudFiles["file_size"][$i]);
					}
				}
			}
		}
	}

	function OnFileCopy(&$arFile, $newPath = "")
	{
		if($arFile["HANDLER_ID"] <= 0)
			return false;

		$bucket = new CCloudStorageBucket($arFile["HANDLER_ID"]);
		if(!$bucket->Init())
			return false;

		if($bucket->READ_ONLY == "Y")
			return false;

		if(strlen($newPath))
		{
			$filePath = "/".trim(str_replace("//", "/", $newPath), "/");
		}
		else
		{
			$strFileExt = strrchr($arFile["FILE_NAME"], ".");
			while(true)
			{
				$newName = md5(uniqid(mt_rand(), true)).$strFileExt;
				$filePath = "/".$arFile["SUBDIR"]."/".$newName;
				if(!$bucket->FileExists($filePath))
					break;
			}
		}

		$result = $bucket->FileCopy($arFile, $filePath);

		if($result)
		{
			$bucket->IncFileCounter($arFile["FILE_SIZE"]);

			if(strlen($newPath))
			{
				$arFile["FILE_NAME"] = bx_basename($filePath);
				$arFile["SUBDIR"] = substr($filePath, 1, -(strlen(bx_basename($filePath)) + 1));
			}
			else
			{
				$arFile["FILE_NAME"] = $newName;
			}
		}

		return $result;
	}

	function OnGetFileSRC($arFile)
	{
		if($arFile["HANDLER_ID"] <= 0)
			return false;

		$bucket = new CCloudStorageBucket($arFile["HANDLER_ID"]);
		if($bucket->Init())
			return $bucket->GetFileSRC($arFile);
		else
			return false;

	}

	function MoveFile($arFile, $obTargetBucket)
	{
		//Try to find suitable bucket for the file
		$bucket = CCloudStorage::FindBucketForFile($arFile, $arFile["FILE_NAME"]);
		if(!is_object($bucket))
			return CCloudStorage::FILE_SKIPPED;

		if(!$bucket->Init())
			return CCloudStorage::FILE_SKIPPED;

		//Check if this is same bucket as the target
		if($bucket->ID != $obTargetBucket->ID)
			return CCloudStorage::FILE_SKIPPED;

		if($bucket->FileExists($bucket->GetFileSRC($arFile))) //TODO rename file
			return CCloudStorage::FILE_SKIPPED;

		$filePath = "/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"];
		$filePath = preg_replace("#[\\\\\\/]+#", "/", $filePath);

		if($arFile["FILE_SIZE"] > $bucket->GetService()->GetMinUploadPartSize())
		{
			$temp_file = CTempFile::GetDirectoryName(2, "clouds").bx_basename($arFile["FILE_NAME"]);
			CheckDirPath($temp_file);

			$obUpload = new CCloudStorageUpload($filePath);
			if(!$obUpload->isStarted())
			{
				if($arFile["HANDLER_ID"])
				{
					$ar = array();
					if(!CCloudStorage::OnMakeFileArray($arFile, $ar))
						return CCloudStorage::FILE_SKIPPED;
					if(!isset($ar["tmp_name"]))
						return CCloudStorage::FILE_SKIPPED;
				}
				else
				{
					$ar = CFile::MakeFileArray($arFile["ID"]);
					if(!isset($ar["tmp_name"]))
						return CCloudStorage::FILE_SKIPPED;
				}

				if(!copy($ar["tmp_name"], $temp_file))
					return CCloudStorage::FILE_SKIPPED;

				if($obUpload->Start($bucket->ID, $arFile["FILE_SIZE"], $arFile["CONTENT_TYPE"]))
					return CCloudStorage::FILE_PARTLY_UPLOADED;
				else
					return CCloudStorage::FILE_SKIPPED;
			}
			else
			{
				$fp = fopen($temp_file, "rb");
				if(!is_resource($fp))
					return CCloudStorage::FILE_SKIPPED;

				$pos = $obUpload->getPos();
				if($pos > filesize($temp_file))
				{
					if($obUpload->Finish())
					{
						$bucket->IncFileCounter(filesize($temp_file));

						if($arFile["HANDLER_ID"])
							CCloudStorage::OnFileDelete($arFile);
						else
						{
							$ar = CFile::MakeFileArray($arFile["ID"]);
							unlink($ar["tmp_name"]);
							@rmdir(substr($ar["tmp_name"], 0, -strlen(bx_basename($ar["tmp_name"]))));
						}

						return CCloudStorage::FILE_MOVED;
					}
					else
						return CCloudStorage::FILE_SKIPPED;
				}

				fseek($fp, $pos);
				self::$part_count = $obUpload->GetPartCount();
				self::$part_size = $obUpload->getPartSize();
				$part = fread($fp, self::$part_size);
				while($obUpload->hasRetries())
				{
					if($obUpload->Next($part))
						return CCloudStorage::FILE_PARTLY_UPLOADED;
				}
				return CCloudStorage::FILE_SKIPPED;
			}
		}
		else
		{
			if($arFile["HANDLER_ID"])
			{
				$ar = array();
				if(!CCloudStorage::OnMakeFileArray($arFile, $ar))
					return CCloudStorage::FILE_SKIPPED;
				if(!isset($ar["tmp_name"]))
					return CCloudStorage::FILE_SKIPPED;
			}
			else
			{
				$ar = CFile::MakeFileArray($arFile["ID"]);
				if(!isset($ar["tmp_name"]))
					return CCloudStorage::FILE_SKIPPED;
			}

			$res = $bucket->SaveFile($filePath, $ar);
			if($res)
			{
				$bucket->IncFileCounter(filesize($ar["tmp_name"]));

				if(file_exists($ar["tmp_name"]))
				{
					unlink($ar["tmp_name"]);
					@rmdir(substr($ar["tmp_name"], 0, -strlen(bx_basename($ar["tmp_name"]))));
				}

				if($arFile["HANDLER_ID"])
					CCloudStorage::OnFileDelete($arFile);
			}
			else
			{	//delete temporary copy
				if($arFile["HANDLER_ID"])
				{
					unlink($ar["tmp_name"]);
					@rmdir(substr($ar["tmp_name"], 0, -strlen(bx_basename($ar["tmp_name"]))));
				}
			}

			return $res? CCloudStorage::FILE_MOVED: CCloudStorage::FILE_SKIPPED;
		}
	}

	function OnFileSave(&$arFile, $strFileName, $strSavePath, $bForceMD5 = false, $bSkipExt = false)
	{
		if(!$arFile["tmp_name"])
			return false;

		if(array_key_exists("bucket", $arFile))
			$bucket = $arFile["bucket"];
		else
			$bucket = CCloudStorage::FindBucketForFile($arFile, $strFileName);

		if(!is_object($bucket))
			return false;

		if(!$bucket->Init())
			return false;

		if(array_key_exists("bucket", $arFile))
		{
			$newName = bx_basename($arFile["tmp_name"]);

			$prefix = $bucket->GetFileSRC("/");
			$subDir = substr($arFile["tmp_name"], strlen($prefix));
			$subDir = substr($subDir, 0, -strlen($newName)-1);
		}
		else
		{
			if(
				$bForceMD5 != true
				&& COption::GetOptionString("main", "save_original_file_name", "N")=="Y"
			)
			{
				if(COption::GetOptionString("main", "convert_original_file_name", "Y")=="Y")
					$newName = CCloudStorage::translit($strFileName);
				else
					$newName = $strFileName;
			}
			else
			{
				$strFileExt = ($bSkipExt == true? '' : strrchr($strFileName, "."));
				$newName = md5(uniqid(mt_rand(), true)).$strFileExt;
			}

			while(true)
			{
				$strRand = md5(mt_rand());
				$strRand = substr($strRand, 0, 3)."/".$strRand;

				if(substr($strSavePath, -1) == "/")
					$subDir = $strSavePath.$strRand;
				else
					$subDir = $strSavePath."/".$strRand;
				$subDir = ltrim($subDir, "/");

				$filePath = "/".$subDir."/".$newName;

				if(!$bucket->FileExists($filePath))
					break;
			}

			if(!$bucket->SaveFile($filePath, $arFile))
				return false;
		}

		$arFile["HANDLER_ID"] = $bucket->ID;
		$arFile["SUBDIR"] = $subDir;
		$arFile["FILE_NAME"] = $newName;

		$arFile["WIDTH"] = 0;
		$arFile["HEIGHT"] = 0;
		if(array_key_exists("bucket", $arFile))
		{
			$arFile["WIDTH"] = $arFile["width"];
			$arFile["HEIGHT"] = $arFile["height"];
			$arFile["size"] = $arFile["file_size"];
		}
		elseif(array_key_exists("content", $arFile))
		{
			$tmp_name = tempnam();
			$fp = fopen($tmp_name, "ab");
			if($fp)
			{
				if(fwrite($fp, $arFile["content"]))
				{
					$bucket->IncFileCounter(filesize($tmp_name));
					$imgArray = CFile::GetImageSize($tmp_name);
					if(is_array($imgArray))
					{
						$arFile["WIDTH"] = $imgArray[0];
						$arFile["HEIGHT"] = $imgArray[1];
					}
				}
				fclose($fp);
				unlink($tmp_name);
			}
		}
		else
		{
			$bucket->IncFileCounter(filesize($arFile["tmp_name"]));
			$imgArray = CFile::GetImageSize($arFile["tmp_name"]);
			if(is_array($imgArray))
			{
				$arFile["WIDTH"] = $imgArray[0];
				$arFile["HEIGHT"] = $imgArray[1];
			}
		}

		if(isset($arFile["old_file"]))
			CFile::DoDelete($arFile["old_file"]);

		return true;
	}

	function FindBucketByFile($file_name)
	{
		foreach(CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			if($bucket["ACTIVE"] == "Y")
			{
				$obBucket = new CCloudStorageBucket($bucket["ID"]);
				if($obBucket->Init())
				{
					$prefix = $obBucket->GetFileSRC("/");
					if(substr($file_name, 0, strlen($prefix)) === $prefix)
						return $obBucket;
				}
			}
		}
		return false;
	}

	function FindFileURIByURN($urn, $log_descr="")
	{
		foreach(CCloudStorageBucket::GetAllBuckets() as $bucket)
		{
			if($bucket["ACTIVE"] == "Y")
			{
				$obBucket = new CCloudStorageBucket($bucket["ID"]);
				if($obBucket->Init() && $obBucket->FileExists($urn))
				{
					$uri = $obBucket->GetFileSRC($urn);

					if($log_descr && COption::GetOptionString("clouds", "log_404_errors") === "Y")
						CEventLog::Log("WARNING", "CLOUDS_404", "clouds", $uri, $log_descr);

					return $uri;
				}
			}
		}
		return "";
	}

	function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
	{
		global $USER;
		if(!$USER->CanDoOperation("clouds_browse"))
			return;

		//When UnRegisterModuleDependences is called from module uninstall
		//cached EventHandlers may be called
		if(defined("BX_CLOUDS_UNINSTALLED"))
			return;

		$aMenu = array(
			"parent_menu" => "global_menu_content",
			"section" => "clouds",
			"sort" => 150,
			"text" => GetMessage("CLO_STORAGE_MENU"),
			"title" => GetMessage("CLO_STORAGE_TITLE"),
			"url" => "clouds_index.php?lang=".LANGUAGE_ID,
			"icon" => "clouds_menu_icon",
			"page_icon" => "clouds_page_icon",
			"items_id" => "menu_clouds",
			"more_url" => array(
				"clouds_index.php",
			),
			"items" => array()
		);

		$rsBuckets = CCloudStorageBucket::GetList(array("SORT"=>"DESC", "ID"=>"ASC"));
		while($arBucket = $rsBuckets->Fetch())
			$aMenu["items"][] = array(
				"text" => $arBucket["BUCKET"],
				"url" => "clouds_file_list.php?lang=".LANGUAGE_ID."&bucket=".$arBucket["ID"]."&path=/",
				"more_url" => array(
					"clouds_file_list.php?bucket=".$arBucket["ID"],
				),
				"title" => "",
				"page_icon" => "clouds_page_icon",
				"items_id" => "menu_clouds_bucket_".$arBucket["ID"],
				"module_id" => "clouds",
				"items" => array()
			);

		if(!empty($aMenu["items"]))
			$aModuleMenu[] = $aMenu;
	}

	function OnAdminListDisplay(&$obList)
	{
		global $USER;

		if($obList->table_id !== "tbl_fileman_admin")
			return;

		if(!is_object($USER) || !$USER->CanDoOperation("clouds_upload"))
			return;

		static $clouds = null;
		static $sTableID = "tbl_clouds_storage_list";
		static $lAdmin = null;

		$upload_dir = "/".COption::GetOptionString("main", "upload_dir", "")."/";
		foreach($obList->aRows as $row_num => $obRow)
		{
			if($obRow->arRes["TYPE"] === "F")
			{
				if(!isset($clouds))
				{
					$lAdmin = new CAdminList($sTableID);
					$clouds = array();
					$rsClouds = CCloudStorageBucket::GetList(array("SORT"=>"DESC", "ID"=>"ASC"));
					while($arStorage = $rsClouds->Fetch())
						if($arStorage["READ_ONLY"] == "N" && $arStorage["ACTIVE"] == "Y")
							$clouds[$arStorage["ID"]] = $arStorage["BUCKET"];
				}

				if(!empty($clouds))
				{
					$ID = "F".$obRow->arRes["NAME"];
					$file = $obRow->arRes["NAME"];
					$path = substr($obRow->arRes["ABS_PATH"], 0, -strlen($file));

					$arSubMenu = array();
					foreach($clouds as $id => $bucket)
						$arSubMenu[] = array(
							"TEXT" => $bucket,
							"ACTION" => $s = "if(confirm('".GetMessage("CLO_STORAGE_UPLOAD_CONF")."')) jsUtils.Redirect([], '".CUtil::AddSlashes("/bitrix/admin/clouds_file_list.php?lang=".LANGUAGE_ID."&bucket=".urlencode($id)."&path=".urlencode($path)."&ID=".urlencode($ID)."&action=upload&".bitrix_sessid_get())."');"
						);

					$obRow->aActions[] = array(
						"TEXT" => GetMessage("CLO_STORAGE_UPLOAD_MENU"),
						"MENU" => $arSubMenu,
					);
				}
			}
		}
	}

	function HasActiveBuckets()
	{
		foreach(CCloudStorageBucket::GetAllBuckets() as $bucket)
			if($bucket["ACTIVE"] === "Y")
				return true;
		return false;
	}

	function OnBeforeProlog()
	{
		if(defined("BX_CHECK_SHORT_URI") && BX_CHECK_SHORT_URI)
		{
			$upload_dir = "/".COption::GetOptionString("main", "upload_dir", "")."/";
			if(substr($_SERVER["REQUEST_URI"], 0, strlen($upload_dir)) === $upload_dir)
			{
				$check_uri1 = substr($_SERVER["REQUEST_URI"], strlen($upload_dir)-1);
				$url = CCloudStorage::FindFileURIByURN($check_uri1);
				if($url != "")
				{
					if(COption::GetOptionString("clouds", "log_404_errors") === "Y")
						CEventLog::Log("WARNING", "CLOUDS_404", "clouds", $_SERVER["HTTP_REFERER"]);
					LocalRedirect($obBucket->GetFileSRC($check_uri1), true);
				}
			}
		}
	}

	function GetAuditTypes()
	{
		return array(
			"CLOUDS_404" => "[CLOUDS_404] ".GetMessage("CLO_404_ON_MOVED_FILE"),
		);
	}

	function translit($file_name)
	{
		return CUtil::translit($file_name, LANGUAGE_ID, array(
			"safe_chars"=>". ",
			"change_case"=>false,
			"max_len"=>255,
		));
	}
}
?>
