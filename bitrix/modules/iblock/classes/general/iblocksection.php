<?
IncludeModuleLangFile(__FILE__);

class CAllIBlockSection
{
	var $LAST_ERROR;
	function GetFilter($arFilter=Array())
	{
		global $DB;
		$arIBlockFilter = Array();
		$arSqlSearch = Array();
		$bSite = false;
		foreach($arFilter as $key => $val)
		{
			$res = CIBlock::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			$key = strtoupper($key);
			switch($key)
			{
			case "ACTIVE":
			case "GLOBAL_ACTIVE":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "string_equal", $cOperationType);
				break;
			case "IBLOCK_ACTIVE":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.ACTIVE", $val, "string_equal", $cOperationType);
				break;
			case "LID":
			case "SITE_ID":
				$str = CIBlock::FilterCreate("BS.SITE_ID", $val, "string_equal", $cOperationType);
				if(strlen($str) > 0)
				{
					$arIBlockFilter[] = $str;
					$bSite = true;
				}
				break;
			case "IBLOCK_NAME":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.NAME", $val, "string", $cOperationType);
				break;
			case "IBLOCK_EXTERNAL_ID":
			case "IBLOCK_XML_ID":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.XML_ID", $val, "string", $cOperationType);
				break;
			case "IBLOCK_TYPE":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.IBLOCK_TYPE_ID", $val, "string", $cOperationType);
				break;
			case "TIMESTAMP_X":
			case "DATE_CREATE":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "date", $cOperationType);
				break;
			case "IBLOCK_CODE":
				$arIBlockFilter[] = CIBlock::FilterCreate("B.CODE", $val, "string", $cOperationType);
				break;
			case "IBLOCK_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "number", $cOperationType);
				$arIBlockFilter[] = CIBlock::FilterCreate("B.ID", $val, "number", $cOperationType);
				break;
			case "NAME":
			case "XML_ID":
			case "TMP_ID":
			case "CODE":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "string", $cOperationType);
				break;
			case "EXTERNAL_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.XML_ID", $val, "string", $cOperationType);
				break;
			case "ID":
			case "DEPTH_LEVEL":
			case "MODIFIED_BY":
			case "CREATED_BY":
			case "SOCNET_GROUP_ID":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.".$key, $val, "number", $cOperationType);
				break;
			case "SECTION_ID":
				if(!is_array($val) && IntVal($val)<=0)
					$arSqlSearch[] = CIBlock::FilterCreate("BS.IBLOCK_SECTION_ID", "", "number", $cOperationType, false);
				else
					$arSqlSearch[] = CIBlock::FilterCreate("BS.IBLOCK_SECTION_ID", $val, "number", $cOperationType);
				break;
			case "RIGHT_MARGIN":
				$arSqlSearch[] = "BS.RIGHT_MARGIN ".($cOperationType=="N"?">":"<=").IntVal($val);
				break;
			case "LEFT_MARGIN":
				$arSqlSearch[] = "BS.LEFT_MARGIN ".($cOperationType=="N"?"<":">=").IntVal($val);
				break;
			case "LEFT_BORDER":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.LEFT_MARGIN", $val, "number", $cOperationType);
				break;
			case "RIGHT_BORDER":
				$arSqlSearch[] = CIBlock::FilterCreate("BS.RIGHT_MARGIN", $val, "number", $cOperationType);
				break;
			}
		}

		static $IBlockFilter_cache = array();
		if($bSite)
		{
			if(is_array($arIBlockFilter) && count($arIBlockFilter)>0)
			{
				$sIBlockFilter = "";
				foreach($arIBlockFilter as $val)
					if(strlen($val)>0)
						$sIBlockFilter .= "  AND ".$val;

				if(!array_key_exists($sIBlockFilter, $IBlockFilter_cache))
				{
					$strSql =
						"SELECT DISTINCT B.ID ".
						"FROM b_iblock B, b_iblock_site BS ".
						"WHERE B.ID = BS.IBLOCK_ID ".
							$sIBlockFilter;

					$arIBLOCKFilter = array();
					$dbRes = $DB->Query($strSql);
					while($arRes = $dbRes->Fetch())
						$arIBLOCKFilter[] = $arRes["ID"];
					$IBlockFilter_cache[$sIBlockFilter] = $arIBLOCKFilter;
				}
				else
				{
					$arIBLOCKFilter = $IBlockFilter_cache[$sIBlockFilter];
				}

				if(count($arIBLOCKFilter) > 0)
					$arSqlSearch[] = "B.ID IN (".implode(", ", $arIBLOCKFilter).") ";
			}
		}
		else
		{
			foreach($arIBlockFilter as $val)
				if(strlen($val) > 0)
					$arSqlSearch[] = $val;
		}

		return $arSqlSearch;
	}

	function GetTreeList($arFilter=Array())
	{
		return CIBlockSection::GetList(Array("left_margin"=>"asc"), $arFilter);
	}

	function GetNavChain($IBLOCK_ID, $SECTION_ID)
	{
		global $DB;

		$res = new CIBlockResult(
			$DB->Query("
				SELECT
					BS.*,
					B.LIST_PAGE_URL,
					B.SECTION_PAGE_URL,
					B.IBLOCK_TYPE_ID,
					B.CODE as IBLOCK_CODE,
					B.XML_ID as IBLOCK_EXTERNAL_ID,
					BS.XML_ID as EXTERNAL_ID
				FROM
					b_iblock_section M,
					b_iblock_section BS,
					b_iblock B
				WHERE M.ID=".IntVal($SECTION_ID)."
					".($IBLOCK_ID>0? "AND M.IBLOCK_ID=".IntVal($IBLOCK_ID): "")."
					AND M.IBLOCK_ID=BS.IBLOCK_ID
					AND B.ID=BS.IBLOCK_ID
					AND M.LEFT_MARGIN>=BS.LEFT_MARGIN
					AND M.RIGHT_MARGIN<=BS.RIGHT_MARGIN
				ORDER BY BS.LEFT_MARGIN
			")
		);
		$res->bIBlockSection = true;
		return $res;
	}


	///////////////////////////////////////////////////////////////////
	// Function returns section by ID
	///////////////////////////////////////////////////////////////////
	function GetByID($ID)
	{
		return CIBlockSection::GetList(Array(), Array("ID"=>IntVal($ID)));
	}

	///////////////////////////////////////////////////////////////////
	// New section
	///////////////////////////////////////////////////////////////////
	function Add($arFields, $bResort=true, $bUpdateSearch=true, $bResizePictures=false)
	{
		global $USER, $DB, $APPLICATION;

		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];
		Unset($arFields["GLOBAL_ACTIVE"]);
		Unset($arFields["DEPTH_LEVEL"]);
		Unset($arFields["LEFT_MARGIN"]);
		Unset($arFields["RIGHT_MARGIN"]);

		$arIBlock = CIBlock::GetArrayByID($arFields["IBLOCK_ID"]);
		if($bResizePictures && is_array($arIBlock))
		{
			if(
				$arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["FROM_DETAIL"] === "Y"
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arFields["DETAIL_PICTURE"]["size"] > 0
				&& (
					$arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"] === "Y"
					|| $arFields["PICTURE"]["size"] <= 0
				)
			)
			{
				$arNewPreview = $arFields["DETAIL_PICTURE"];
				$arNewPreview["COPY_FILE"] = "Y";
				$arNewPreview["description"] = $arFields["PICTURE"]["description"];
				$arFields["PICTURE"] = $arNewPreview;
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["PICTURE"], $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["PICTURE"]["description"];
					$arFields["PICTURE"] = $arNewPicture;
				}
				elseif($arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"]["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["DETAIL_PICTURE"], $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"]);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["DETAIL_PICTURE"]["description"];
					$arFields["DETAIL_PICTURE"] = $arNewPicture;
				}
				elseif($arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["DETAIL_PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").": ".$arNewPicture."<br>";
				}
			}
		}

		if(is_set($arFields, "PICTURE"))
		{
			if(
				strlen($arFields["PICTURE"]["name"]) <= 0
				&& strlen($arFields["PICTURE"]["del"]) <= 0
			)
				unset($arFields["PICTURE"]);
			else
				$arFields["PICTURE"]["MODULE_ID"] = "iblock";
		}

		if(is_set($arFields, "DETAIL_PICTURE"))
		{
			if(
				strlen($arFields["DETAIL_PICTURE"]["name"]) <= 0
				&& strlen($arFields["DETAIL_PICTURE"]["del"]) <=0
			)
				unset($arFields["DETAIL_PICTURE"]);
			else
				$arFields["DETAIL_PICTURE"]["MODULE_ID"] = "iblock";
		}

		$arFields["IBLOCK_SECTION_ID"] = isset($arFields["IBLOCK_SECTION_ID"])? intval($arFields["IBLOCK_SECTION_ID"]): 0;
		if($arFields["IBLOCK_SECTION_ID"] == 0)
			$arFields["IBLOCK_SECTION_ID"] = false;

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";
		else
			$arFields["ACTIVE"] = "Y";

		if(!array_key_exists("DESCRIPTION_TYPE", $arFields) || $arFields["DESCRIPTION_TYPE"]!="html")
			$arFields["DESCRIPTION_TYPE"]="text";

		if(!isset($arFields["DESCRIPTION"]))
			$arFields["DESCRIPTION"] = false;

		$arFields["SEARCHABLE_CONTENT"] =
			ToUpper(
				$arFields["NAME"]."\r\n".
				($arFields["DESCRIPTION_TYPE"]=="html" ?
					HTMLToTxt($arFields["DESCRIPTION"]) :
					$arFields["DESCRIPTION"]
				)
			);

		unset($arFields["DATE_CREATE"]);
		$arFields["~DATE_CREATE"] = $DB->CurrentTimeFunction();
		if(is_object($USER))
		{
			$user_id = intval(@$USER->GetID());
			if(!isset($arFields["CREATED_BY"]) || intval($arFields["CREATED_BY"]) <= 0)
				$arFields["CREATED_BY"] = $user_id;
			if(!isset($arFields["MODIFIED_BY"]) || intval($arFields["MODIFIED_BY"]) <= 0)
				$arFields["MODIFIED_BY"] = $user_id;
		}

		$IBLOCK_ID = intval($arFields["IBLOCK_ID"]);

		if(!$this->CheckFields($arFields))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		elseif($IBLOCK_ID && !$GLOBALS["USER_FIELD_MANAGER"]->CheckFields("IBLOCK_".$IBLOCK_ID."_SECTION", 0, $arFields))
		{
			$Result = false;
			$err = $APPLICATION->GetException();
			if(is_object($err))
				$this->LAST_ERROR .= str_replace("<br><br>", "<br>", $err->GetString()."<br>");
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			if(array_key_exists("PICTURE", $arFields))
			{
				$SAVED_PICTURE = $arFields["PICTURE"];
				CFile::SaveForDB($arFields, "PICTURE", "iblock");
			}

			if(array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$SAVED_DETAIL_PICTURE = $arFields["DETAIL_PICTURE"];
				CFile::SaveForDB($arFields, "DETAIL_PICTURE", "iblock");
			}

			unset($arFields["ID"]);
			$ID = intval($DB->Add("b_iblock_section", $arFields, Array("DESCRIPTION","SEARCHABLE_CONTENT"), "iblock"));

			if(array_key_exists("PICTURE", $arFields))
				$arFields["PICTURE"] = $SAVED_PICTURE;
			if(array_key_exists("DETAIL_PICTURE", $arFields))
				$arFields["DETAIL_PICTURE"] = $SAVED_DETAIL_PICTURE;

			if($bResort)
			{
				if(!array_key_exists("SORT", $arFields))
					$arFields["SORT"] = 500;

				$arParent = false;
				if($arFields["IBLOCK_SECTION_ID"] !== false)
				{
					$strSql = "
						SELECT BS.ID, BS.ACTIVE, BS.GLOBAL_ACTIVE, BS.DEPTH_LEVEL, BS.LEFT_MARGIN, BS.RIGHT_MARGIN
						FROM b_iblock_section BS
						WHERE BS.IBLOCK_ID = ".$IBLOCK_ID."
						AND BS.ID = ".$arFields["IBLOCK_SECTION_ID"]."
					";
					$rsParent = $DB->Query($strSql);
					$arParent = $rsParent->Fetch();
				}

				$NAME = $arFields["NAME"];
				$SORT = intval($arFields["SORT"]);

				//Find rightmost child of the parent
				$strSql = "
					SELECT BS.ID, BS.RIGHT_MARGIN, BS.GLOBAL_ACTIVE, BS.DEPTH_LEVEL
					FROM b_iblock_section BS
					WHERE BS.IBLOCK_ID = ".$IBLOCK_ID."
					AND ".($arFields["IBLOCK_SECTION_ID"] !== false? "BS.IBLOCK_SECTION_ID=".$arFields["IBLOCK_SECTION_ID"]: "BS.IBLOCK_SECTION_ID IS NULL")."
					AND (
						(BS.SORT < ".$SORT.")
						OR (BS.SORT = ".$SORT." AND BS.NAME < '".$DB->ForSQL($NAME)."')
					)
					AND BS.ID <> ".$ID."
					ORDER BY BS.SORT DESC, BS.NAME DESC
				";
				$rsChild = $DB->Query($strSql);
				if($arChild = $rsChild->Fetch())
				{
					//We found the left neighbour
					$arUpdate = array(
						"LEFT_MARGIN" => intval($arChild["RIGHT_MARGIN"])+1,
						"RIGHT_MARGIN" => intval($arChild["RIGHT_MARGIN"])+2,
						"DEPTH_LEVEL" => intval($arChild["DEPTH_LEVEL"]),
					);
					//in case we adding active section
					if($arFields["ACTIVE"] != "N")
					{
						//Look up GLOBAL_ACTIVE of the parent
						//if none then take our own
						if($arParent)//We must inherit active from the parent
							$arUpdate["GLOBAL_ACTIVE"] = $arParent["ACTIVE"] == "Y"? "Y": "N";
						else //No parent was found take our own
							$arUpdate["GLOBAL_ACTIVE"] = "Y";
					}
					else
					{
						$arUpdate["GLOBAL_ACTIVE"] = "N";
					}
				}
				else
				{
					//If we have parent, when take its left_margin
					if($arParent)
					{
						$arUpdate = array(
							"LEFT_MARGIN" => intval($arParent["LEFT_MARGIN"])+1,
							"RIGHT_MARGIN" => intval($arParent["LEFT_MARGIN"])+2,
							"GLOBAL_ACTIVE" => ($arParent["GLOBAL_ACTIVE"] == "Y") && ($arFields["ACTIVE"] != "N")? "Y": "N",
							"DEPTH_LEVEL" => intval($arParent["DEPTH_LEVEL"])+1,
						);
					}
					else
					{
						//We are only one/leftmost section in the iblock.
						$arUpdate = array(
							"LEFT_MARGIN" => 1,
							"RIGHT_MARGIN" => 2,
							"GLOBAL_ACTIVE" => $arFields["ACTIVE"] != "N"? "Y": "N",
							"DEPTH_LEVEL" => 1,
						);
					}
				}
				$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
						,LEFT_MARGIN = ".$arUpdate["LEFT_MARGIN"]."
						,RIGHT_MARGIN = ".$arUpdate["RIGHT_MARGIN"]."
						,DEPTH_LEVEL = ".$arUpdate["DEPTH_LEVEL"]."
						,GLOBAL_ACTIVE = '".$arUpdate["GLOBAL_ACTIVE"]."'
					WHERE
						ID = ".$ID."
				");
				$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
						,LEFT_MARGIN = LEFT_MARGIN + 2
						,RIGHT_MARGIN = RIGHT_MARGIN + 2
					WHERE
						IBLOCK_ID = ".$IBLOCK_ID."
						AND LEFT_MARGIN >= ".$arUpdate["LEFT_MARGIN"]."
						AND ID <> ".$ID."
				");
				if($arParent)
					$DB->Query("
						UPDATE b_iblock_section SET
							TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
							,RIGHT_MARGIN = RIGHT_MARGIN + 2
						WHERE
							IBLOCK_ID = ".$IBLOCK_ID."
							AND LEFT_MARGIN <= ".$arParent["LEFT_MARGIN"]."
							AND RIGHT_MARGIN >= ".$arParent["RIGHT_MARGIN"]."
					");
			}

			$GLOBALS["USER_FIELD_MANAGER"]->Update("IBLOCK_".$IBLOCK_ID."_SECTION", $ID, $arFields);

			if($bUpdateSearch)
				CIBlockSection::UpdateSearch($ID);

			if($arIBlock["FIELDS"]["LOG_SECTION_ADD"]["IS_REQUIRED"] == "Y")
			{
				$db_events = GetModuleEvents("main", "OnBeforeEventLog");
				$arEvent = $db_events->Fetch();
				if(!$arEvent || ExecuteModuleEventEx($arEvent, array($USER->GetID()))===false)
				{
					$rsSection = CIBlockSection::GetList(array(), array("=ID"=>$ID), false,  array("LIST_PAGE_URL", "NAME", "CODE"));
					$arSection = $rsSection->GetNext();
					$res = array(
							"ID" => $ID,
							"CODE" => $arSection["CODE"],
							"NAME" => $arSection["NAME"],
							"SECTION_NAME" => $arIBlock["SECTION_NAME"],
							"IBLOCK_PAGE_URL" => $arSection["LIST_PAGE_URL"]
							);
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_SECTION_ADD",
						"iblock",
						$arIBlock["ID"],
						serialize($res)
					);
				}
			}

			if($arIBlock["RIGHTS_MODE"] === "E")
			{
				$obSectionRights = new CIBlockSectionRights($arIBlock["ID"], $ID);
				$obSectionRights->ChangeParents(array(), array($arFields["IBLOCK_SECTION_ID"]));

				if(array_key_exists("RIGHTS", $arFields) && is_array($arFields["RIGHTS"]))
					$obSectionRights->SetRights($arFields["RIGHTS"]);
			}

			$Result = $ID;
			$arFields["ID"] = &$ID;

			/************* QUOTA *************/
			$_SESSION["SESS_RECOUNT_DB"] = "Y";
			/************* QUOTA *************/
		}

		$arFields["RESULT"] = &$Result;

		$events = GetModuleEvents("iblock", "OnAfterIBlockSectionAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		if(defined("BX_COMP_MANAGED_CACHE"))
			$GLOBALS["CACHE_MANAGER"]->ClearByTag("iblock_id_".$arIBlock["ID"]);

		return $Result;
	}

	///////////////////////////////////////////////////////////////////
	// Update section properties
	///////////////////////////////////////////////////////////////////
	function Update($ID, $arFields, $bResort=true, $bUpdateSearch=true, $bResizePictures=false)
	{
		global $USER, $DB, $APPLICATION;

		$ID = intval($ID);

		$db_record = CIBlockSection::GetList(Array(), Array("ID"=>$ID, "CHECK_PERMISSIONS"=>"N"));
		if(!($db_record = $db_record->Fetch()))
			return false;

		if(is_set($arFields, "EXTERNAL_ID"))
			$arFields["XML_ID"] = $arFields["EXTERNAL_ID"];

		Unset($arFields["GLOBAL_ACTIVE"]);
		Unset($arFields["DEPTH_LEVEL"]);
		Unset($arFields["LEFT_MARGIN"]);
		Unset($arFields["RIGHT_MARGIN"]);
		unset($arFields["IBLOCK_ID"]);
		unset($arFields["DATE_CREATE"]);
		unset($arFields["CREATED_BY"]);

		$arIBlock = CIBlock::GetArrayByID($db_record["IBLOCK_ID"]);
		if($bResizePictures)
		{
			if(
				$arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["DELETE_WITH_DETAIL"] === "Y"
				&& $arFields["DETAIL_PICTURE"]["del"] === "Y"
			)
			{
				$arFields["PICTURE"]["del"] = "Y";
			}

			if(
				$arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["FROM_DETAIL"] === "Y"
				&& (
					$arFields["PICTURE"]["size"] <= 0
					|| $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"] === "Y"
				)
				&& $arFields["DETAIL_PICTURE"]["size"] > 0
			)
			{
				if(
					$arFields["PICTURE"]["del"] !== "Y"
					&& $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["UPDATE_WITH_DETAIL"] !== "Y"
				)
				{
					$arOldSection = $db_record;
				}
				else
				{
					$arOldSection = false;
				}

				if(!$arOldSection || !$arOldSection["PICTURE"])
				{
					$arNewPreview = $arFields["DETAIL_PICTURE"];
					$arNewPreview["COPY_FILE"] = "Y";
					$arNewPreview["description"] = $arFields["PICTURE"]["description"];
					$arFields["PICTURE"] = $arNewPreview;
				}
			}

			if(
				array_key_exists("PICTURE", $arFields)
				&& is_array($arFields["PICTURE"])
				&& $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["PICTURE"], $arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["PICTURE"]["description"];
					$arFields["PICTURE"] = $arNewPicture;
				}
				elseif($arIBlock["FIELDS"]["SECTION_PICTURE"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
				}
			}

			if(
				array_key_exists("DETAIL_PICTURE", $arFields)
				&& is_array($arFields["DETAIL_PICTURE"])
				&& $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"]["SCALE"] === "Y"
			)
			{
				$arNewPicture = CIBlock::ResizePicture($arFields["DETAIL_PICTURE"], $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"]);
				if(is_array($arNewPicture))
				{
					$arNewPicture["description"] = $arFields["DETAIL_PICTURE"]["description"];
					$arFields["DETAIL_PICTURE"] = $arNewPicture;
				}
				elseif($arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["DEFAULT_VALUE"]["IGNORE_ERRORS"] !== "Y")
				{
					unset($arFields["DETAIL_PICTURE"]);
					$strWarning .= GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").": ".$arNewPicture."<br>";
				}
			}
		}

		if(is_set($arFields, "PICTURE"))
		{
			if(strlen($arFields["PICTURE"]["name"])<=0 && strlen($arFields["PICTURE"]["del"])<=0)
				unset($arFields["PICTURE"]);
			else
			{
				$arFields["PICTURE"]["old_file"] = $db_record["PICTURE"];
				$arFields["PICTURE"]["MODULE_ID"] = "iblock";
			}
		}

		if(is_set($arFields, "DETAIL_PICTURE"))
		{
			if(strlen($arFields["DETAIL_PICTURE"]["name"])<=0 && strlen($arFields["DETAIL_PICTURE"]["del"])<=0)
				unset($arFields["DETAIL_PICTURE"]);
			else
			{
				$arFields["DETAIL_PICTURE"]["old_file"] = $db_record["DETAIL_PICTURE"];
				$arFields["DETAIL_PICTURE"]["MODULE_ID"] = "iblock";
			}
		}

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		if(is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"]!="html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if(isset($arFields["IBLOCK_SECTION_ID"]))
		{
			$arFields["IBLOCK_SECTION_ID"] = intval($arFields["IBLOCK_SECTION_ID"]);
			if($arFields["IBLOCK_SECTION_ID"] <= 0)
				$arFields["IBLOCK_SECTION_ID"] = false;
		}

		$DESC_tmp = is_set($arFields, "DESCRIPTION")? $arFields["DESCRIPTION"]: $db_record["DESCRIPTION"];
		$DESC_TYPE_tmp = is_set($arFields, "DESCRIPTION_TYPE")? $arFields["DESCRIPTION_TYPE"]: $db_record["DESCRIPTION_TYPE"];

		$arFields["SEARCHABLE_CONTENT"] = ToUpper(
			(is_set($arFields, "NAME")? $arFields["NAME"]: $db_record["NAME"])."\r\n".
			($DESC_TYPE_tmp=="html"? HTMLToTxt($DESC_tmp): $DESC_tmp)
		);

		if(is_object($USER))
		{
			if(!isset($arFields["MODIFIED_BY"]) || intval($arFields["MODIFIED_BY"]) <= 0)
				$arFields["MODIFIED_BY"] = intval(@$USER->GetID());
		}

		if(!$this->CheckFields($arFields, $ID))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		elseif(!$GLOBALS["USER_FIELD_MANAGER"]->CheckFields("IBLOCK_".$db_record["IBLOCK_ID"]."_SECTION", $ID, $arFields))
		{
			$Result = false;
			$err = $APPLICATION->GetException();
			if(is_object($err))
				$this->LAST_ERROR .= str_replace("<br><br>", "<br>", $err->GetString()."<br>");
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			if(array_key_exists("PICTURE", $arFields))
			{
				$SAVED_PICTURE = $arFields["PICTURE"];
				CFile::SaveForDB($arFields, "PICTURE", "iblock");
			}

			if(array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$SAVED_DETAIL_PICTURE = $arFields["DETAIL_PICTURE"];
				CFile::SaveForDB($arFields, "DETAIL_PICTURE", "iblock");
			}

			unset($arFields["ID"]);
			$strUpdate = $DB->PrepareUpdate("b_iblock_section", $arFields, "iblock");

			if(array_key_exists("PICTURE", $arFields))
				$arFields["PICTURE"] = $SAVED_PICTURE;
			if(array_key_exists("DETAIL_PICTURE", $arFields))
				$arFields["DETAIL_PICTURE"] = $SAVED_DETAIL_PICTURE;

			if(strlen($strUpdate) > 0)
			{
				$strSql = "UPDATE b_iblock_section SET ".$strUpdate." WHERE ID = ".$ID;
				$arBinds=Array();
				if(array_key_exists("DESCRIPTION", $arFields))
					$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];
				if(array_key_exists("SEARCHABLE_CONTENT", $arFields))
					$arBinds["SEARCHABLE_CONTENT"] = $arFields["SEARCHABLE_CONTENT"];
				$DB->QueryBind($strSql, $arBinds);
			}

			if($bResort)
			{
				//Move inside the tree
				if((isset($arFields["SORT"]) && $arFields["SORT"]!=$db_record["SORT"])
					|| (isset($arFields["NAME"]) && $arFields["NAME"]!=$db_record["NAME"])
					|| (isset($arFields["IBLOCK_SECTION_ID"]) && $arFields["IBLOCK_SECTION_ID"]!=$db_record["IBLOCK_SECTION_ID"]))
				{
					//First "delete" from the tree
					$distance = intval($db_record["RIGHT_MARGIN"]) - intval($db_record["LEFT_MARGIN"]) + 1;
					$DB->Query("
						UPDATE b_iblock_section SET
							TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
							,LEFT_MARGIN = -LEFT_MARGIN
							,RIGHT_MARGIN = -RIGHT_MARGIN
						WHERE
							IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
							AND LEFT_MARGIN >= ".intval($db_record["LEFT_MARGIN"])."
							AND LEFT_MARGIN <= ".intval($db_record["RIGHT_MARGIN"])."
					");
					$DB->Query("
						UPDATE b_iblock_section SET
							TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
							,RIGHT_MARGIN = RIGHT_MARGIN - ".$distance."
						WHERE
							IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
							AND RIGHT_MARGIN > ".$db_record["RIGHT_MARGIN"]."
					");
					$DB->Query("
						UPDATE b_iblock_section SET
							TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
							,LEFT_MARGIN = LEFT_MARGIN - ".$distance."
						WHERE
							IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
							AND LEFT_MARGIN > ".$db_record["LEFT_MARGIN"]."
					");

					//Next insert into the the tree almost as we do when inserting the new one

					$PARENT_ID = isset($arFields["IBLOCK_SECTION_ID"])? intval($arFields["IBLOCK_SECTION_ID"]): intval($db_record["IBLOCK_SECTION_ID"]);
					$NAME = isset($arFields["NAME"])? $arFields["NAME"]: $db_record["NAME"];
					$SORT = isset($arFields["SORT"])? intval($arFields["SORT"]): intval($db_record["SORT"]);

					$arParents = array();
					$strSql = "
						SELECT BS.ID, BS.ACTIVE, BS.GLOBAL_ACTIVE, BS.DEPTH_LEVEL, BS.LEFT_MARGIN, BS.RIGHT_MARGIN
						FROM b_iblock_section BS
						WHERE BS.IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
						AND BS.ID in (".intval($db_record["IBLOCK_SECTION_ID"]).", ".$PARENT_ID.")
					";
					$rsParents = $DB->Query($strSql);
					while($arParent = $rsParents->Fetch())
					{
						$arParents[$arParent["ID"]] = $arParent;
					}

					//Find rightmost child of the parent
					$strSql = "
						SELECT BS.ID, BS.RIGHT_MARGIN, BS.DEPTH_LEVEL
						FROM b_iblock_section BS
						WHERE BS.IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
						AND ".($PARENT_ID > 0? "BS.IBLOCK_SECTION_ID=".$PARENT_ID: "BS.IBLOCK_SECTION_ID IS NULL")."
						AND (
							(BS.SORT < ".$SORT.")
							OR (BS.SORT = ".$SORT." AND BS.NAME < '".$DB->ForSQL($NAME)."')
						)
						AND BS.ID <> ".$ID."
						ORDER BY BS.SORT DESC, BS.NAME DESC
					";
					$rsChild = $DB->Query($strSql);
					if($arChild = $rsChild->Fetch())
					{
						//We found the left neighbour
						$arUpdate = array(
							"LEFT_MARGIN" => intval($arChild["RIGHT_MARGIN"])+1,
							"DEPTH_LEVEL" => intval($arChild["DEPTH_LEVEL"]),
						);
					}
					else
					{
						//If we have parent, when take its left_margin
						if(isset($arParents[$PARENT_ID]) && $arParents[$PARENT_ID])
						{
							$arUpdate = array(
								"LEFT_MARGIN" => intval($arParents[$PARENT_ID]["LEFT_MARGIN"])+1,
								"DEPTH_LEVEL" => intval($arParents[$PARENT_ID]["DEPTH_LEVEL"])+1,
							);
						}
						else
						{
							//We are only one/leftmost section in the iblock.
							$arUpdate = array(
								"LEFT_MARGIN" => 1,
								"DEPTH_LEVEL" => 1,
							);
						}
					}

					$move_distance = intval($db_record["LEFT_MARGIN"]) - $arUpdate["LEFT_MARGIN"];
					$DB->Query("
						UPDATE b_iblock_section SET
							TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
							,LEFT_MARGIN = LEFT_MARGIN + ".$distance."
							,RIGHT_MARGIN = RIGHT_MARGIN + ".$distance."
						WHERE
							IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
							AND LEFT_MARGIN >= ".$arUpdate["LEFT_MARGIN"]."
					");
					$DB->Query("
						UPDATE b_iblock_section SET
							TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
							,LEFT_MARGIN = -LEFT_MARGIN - ".$move_distance."
							,RIGHT_MARGIN = -RIGHT_MARGIN - ".$move_distance."
							".($arUpdate["DEPTH_LEVEL"] != intval($db_record["DEPTH_LEVEL"])? ",DEPTH_LEVEL = DEPTH_LEVEL - ".($db_record["DEPTH_LEVEL"] - $arUpdate["DEPTH_LEVEL"]): "")."
						WHERE
							IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
							AND LEFT_MARGIN <= ".(-intval($db_record["LEFT_MARGIN"]))."
							AND LEFT_MARGIN >= ".(-intval($db_record["RIGHT_MARGIN"]))."
					");

					if(isset($arParents[$PARENT_ID]))
					{
						$DB->Query("
							UPDATE b_iblock_section SET
								TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
								,RIGHT_MARGIN = RIGHT_MARGIN + ".$distance."
							WHERE
								IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
								AND LEFT_MARGIN <= ".$arParents[$PARENT_ID]["LEFT_MARGIN"]."
								AND RIGHT_MARGIN >= ".$arParents[$PARENT_ID]["RIGHT_MARGIN"]."
						");
					}
				}

				//Check if parent was changed
				if(isset($arFields["IBLOCK_SECTION_ID"]) && $arFields["IBLOCK_SECTION_ID"]!=$db_record["IBLOCK_SECTION_ID"])
				{
					$rsSection = CIBlockSection::GetByID($ID);
					$arSection = $rsSection->Fetch();

					$strSql = "
						SELECT ID, GLOBAL_ACTIVE
						FROM b_iblock_section
						WHERE IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
						AND ID = ".intval($arFields["IBLOCK_SECTION_ID"])."
					";
					$rsParent = $DB->Query($strSql);
					$arParent = $rsParent->Fetch();
					//If new parent is not globally active
					//or we are not active either
					//we must be not globally active too
					if(($arParent && $arParent["GLOBAL_ACTIVE"] == "N") || ($arFields["ACTIVE"] == "N"))
					{
						$DB->Query("
							UPDATE b_iblock_section SET
								TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
								,GLOBAL_ACTIVE = 'N'
							WHERE
								IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
								AND LEFT_MARGIN >= ".intval($arSection["LEFT_MARGIN"])."
								AND RIGHT_MARGIN <= ".intval($arSection["RIGHT_MARGIN"])."
						");
					}
					//New parent is globally active
					//And we WAS NOT active
					//But is going to be
					elseif($arSection["ACTIVE"] == "N" && $arFields["ACTIVE"] == "Y")
					{
						$this->RecalcGlobalActiveFlag($arSection);
					}
					//Otherwise we may not to change anything
				}
				//Parent not changed
				//but we are going to change activity flag
				elseif(isset($arFields["ACTIVE"]) && $arFields["ACTIVE"] != $db_record["ACTIVE"])
				{
					//Make all children globally inactive
					if($arFields["ACTIVE"] == "N")
					{
						$DB->Query("
							UPDATE b_iblock_section SET
								TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
								,GLOBAL_ACTIVE = 'N'
							WHERE
								IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
								AND LEFT_MARGIN >= ".intval($db_record["LEFT_MARGIN"])."
								AND RIGHT_MARGIN <= ".intval($db_record["RIGHT_MARGIN"])."
						");
					}
					else
					{
						//Check for parent activity
						$strSql = "
							SELECT ID, GLOBAL_ACTIVE
							FROM b_iblock_section
							WHERE IBLOCK_ID = ".$db_record["IBLOCK_ID"]."
							AND ID = ".intval($db_record["IBLOCK_SECTION_ID"])."
						";
						$rsParent = $DB->Query($strSql);
						$arParent = $rsParent->Fetch();
						//Parent is active
						//and we changed
						//so need to recalc
						if(!$arParent || $arParent["GLOBAL_ACTIVE"] == "Y")
							$this->RecalcGlobalActiveFlag($db_record);
					}
				}
			}

			if($arIBlock["RIGHTS_MODE"] === "E")
			{
				$obSectionRights = new CIBlockSectionRights($arIBlock["ID"], $ID);
				//Check if parent changed with extended rights mode
				if(
					isset($arFields["IBLOCK_SECTION_ID"])
					&& $arFields["IBLOCK_SECTION_ID"] != $db_record["IBLOCK_SECTION_ID"]
				)
				{
					$obSectionRights->ChangeParents(array($db_record["IBLOCK_SECTION_ID"]), array($arFields["IBLOCK_SECTION_ID"]));
				}

				if(array_key_exists("RIGHTS", $arFields) && is_array($arFields["RIGHTS"]))
					$obSectionRights->SetRights($arFields["RIGHTS"]);
			}

			$uf_updated = $GLOBALS["USER_FIELD_MANAGER"]->Update("IBLOCK_".$db_record["IBLOCK_ID"]."_SECTION", $ID, $arFields);
			if($uf_updated)
			{
				$DB->Query("UPDATE b_iblock_section SET TIMESTAMP_X = ".$DB->CurrentTimeFunction()." WHERE ID = ".$ID);
			}

			if($bUpdateSearch)
				CIBlockSection::UpdateSearch($ID);

			if($arIBlock["FIELDS"]["LOG_SECTION_EDIT"]["IS_REQUIRED"] == "Y")
			{
				$db_events = GetModuleEvents("main", "OnBeforeEventLog");
				$arEvent = $db_events->Fetch();
				if(!$arEvent || ExecuteModuleEventEx($arEvent, array($USER->GetID()))===false)
				{
					$rsSection = CIBlockSection::GetList(array(), array("=ID"=>$ID), false,  array("LIST_PAGE_URL", "NAME", "CODE"));
					$arSection = $rsSection->GetNext();
					$res = array(
							"ID" => $ID,
							"CODE" => $arSection["CODE"],
							"NAME" => $arSection["NAME"],
							"SECTION_NAME" => $arIBlock["SECTION_NAME"],
							"IBLOCK_PAGE_URL" => $arSection["LIST_PAGE_URL"]
							);
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_SECTION_EDIT",
						"iblock",
						$arIBlock["ID"],
						serialize($res)
					);
				}
			}

			$Result = true;

			/*********** QUOTA ***************/
			$_SESSION["SESS_RECOUNT_DB"] = "Y";
			/*********** QUOTA ***************/
		}

		$arFields["ID"] = $ID;
		$arFields["IBLOCK_ID"] = $db_record["IBLOCK_ID"];
		$arFields["RESULT"] = &$Result;

		$events = GetModuleEvents("iblock", "OnAfterIBlockSectionUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		if(defined("BX_COMP_MANAGED_CACHE"))
			$GLOBALS["CACHE_MANAGER"]->ClearByTag("iblock_id_".$arIBlock["ID"]);

		return $Result;
	}

	///////////////////////////////////////////////////////////////////
	// Function delete section by its ID
	///////////////////////////////////////////////////////////////////
	function Delete($ID, $bCheckPermissions = true)
	{
		$err_mess = "FILE: ".__FILE__."<br>LINE: ";
		global $DB, $APPLICATION;
		$ID = IntVal($ID);

		$APPLICATION->ResetException();
		$db_events = GetModuleEvents("iblock", "OnBeforeIBlockSectionDelete");
		while($arEvent = $db_events->Fetch())
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}

		$s = CIBlockSection::GetList(Array(), Array("ID"=>$ID, "CHECK_PERMISSIONS"=>($bCheckPermissions? "Y": "N")));
		if($s = $s->Fetch())
		{
			$iblockelements = CIBlockElement::GetList(Array(), Array("SECTION_ID"=>$ID, "SHOW_HISTORY"=>"Y", "IBLOCK_ID"=>$s["IBLOCK_ID"]), false, false, array("ID", "IBLOCK_ID", "WF_PARENT_ELEMENT_ID"));
			while($iblockelement = $iblockelements->Fetch())
			{
				$strSql = "
					SELECT IBLOCK_SECTION_ID
					FROM b_iblock_section_element
					WHERE
						IBLOCK_ELEMENT_ID = ".$iblockelement["ID"]."
						AND IBLOCK_SECTION_ID<>".$ID."
						AND ADDITIONAL_PROPERTY_ID IS NULL
					ORDER BY
						IBLOCK_SECTION_ID
				";
				$db_section_element = $DB->Query($strSql);
				if($ar_section_element = $db_section_element->Fetch())
				{
					$DB->Query("
						UPDATE b_iblock_element
						SET IBLOCK_SECTION_ID=".$ar_section_element["IBLOCK_SECTION_ID"]."
						WHERE ID=".IntVal($iblockelement["ID"])."
					", false, $err_mess.__LINE__);
				}
				elseif(IntVal($iblockelement["WF_PARENT_ELEMENT_ID"])<=0)
				{
					if(!CIBlockElement::Delete($iblockelement["ID"]))
						return false;
				}
				else
				{
					$DB->Query("
						UPDATE b_iblock_element
						SET IBLOCK_SECTION_ID=NULL, IN_SECTIONS='N'
						WHERE ID=".IntVal($iblockelement["ID"])."
					", false, $err_mess.__LINE__);
				}
			}

			$iblocksections = CIBlockSection::GetList(
				array(),
				array("SECTION_ID"=>$ID, "CHECK_PERMISSIONS"=>($bCheckPermissions? "Y": "N")),
				false,
				array("ID")
			);
			while($iblocksection = $iblocksections->Fetch())
			{
				if(!CIBlockSection::Delete($iblocksection["ID"], $bCheckPermissions))
					return false;
			}

			CFile::Delete($s["PICTURE"]);
			CFile::Delete($s["DETAIL_PICTURE"]);

			static $arDelCache;
			if(!is_array($arDelCache))
				$arDelCache = Array();
			if(!is_set($arDelCache, $s["IBLOCK_ID"]))
			{
				$arDelCache[$s["IBLOCK_ID"]] = false;
				$db_ps = $DB->Query("SELECT ID,IBLOCK_ID,VERSION,MULTIPLE FROM b_iblock_property WHERE PROPERTY_TYPE='G' AND (LINK_IBLOCK_ID=".$s["IBLOCK_ID"]." OR LINK_IBLOCK_ID=0 OR LINK_IBLOCK_ID IS NULL)", false, $err_mess.__LINE__);
				while($ar_ps = $db_ps->Fetch())
				{
					if($ar_ps["VERSION"]==2)
					{
						if($ar_ps["MULTIPLE"]=="Y")
							$strTable = "b_iblock_element_prop_m".$ar_ps["IBLOCK_ID"];
						else
							$strTable = "b_iblock_element_prop_s".$ar_ps["IBLOCK_ID"];
					}
					else
					{
						$strTable = "b_iblock_element_property";
					}
					$arDelCache[$s["IBLOCK_ID"]][$strTable][] = $ar_ps["ID"];
				}
			}

			if($arDelCache[$s["IBLOCK_ID"]])
			{
				foreach($arDelCache[$s["IBLOCK_ID"]] as $strTable=>$arProps)
				{
					if(strncmp("b_iblock_element_prop_s", $strTable, 23)==0)
					{
						foreach($arProps as $prop_id)
						{
							$strSql = "UPDATE ".$strTable." SET PROPERTY_".$prop_id."=null,DESCRIPTION_".$prop_id."=null WHERE PROPERTY_".$prop_id."=".$s["ID"];
							if(!$DB->Query($strSql, false, $err_mess.__LINE__))
								return false;
						}
					}
					elseif(strncmp("b_iblock_element_prop_m", $strTable, 23)==0)
					{
						$strSql = "SELECT IBLOCK_PROPERTY_ID, IBLOCK_ELEMENT_ID FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$s["ID"];
						$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
						while($ar = $rs->Fetch())
						{
							$strSql = "
								UPDATE ".str_replace("prop_m", "prop_s", $strTable)."
								SET	PROPERTY_".$ar["IBLOCK_PROPERTY_ID"]."=null,
									DESCRIPTION_".$ar["IBLOCK_PROPERTY_ID"]."=null
								WHERE IBLOCK_ELEMENT_ID = ".$ar["IBLOCK_ELEMENT_ID"]."
							";
							if(!$DB->Query($strSql, false, $err_mess.__LINE__))
								return false;
						}
						$strSql = "DELETE FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$s["ID"];
						if(!$DB->Query($strSql, false, $err_mess.__LINE__))
							return false;
					}
					else
					{
						$strSql = "DELETE FROM ".$strTable." WHERE IBLOCK_PROPERTY_ID IN (".implode(", ", $arProps).") AND VALUE_NUM=".$s["ID"];
						if(!$DB->Query($strSql, false, $err_mess.__LINE__))
							return false;
					}
				}
			}

			$DB->Query("DELETE FROM b_iblock_section_element WHERE IBLOCK_SECTION_ID=".IntVal($ID), false, $err_mess.__LINE__);

			if(CModule::IncludeModule("search"))
				CSearch::DeleteIndex("iblock", "S".$ID);

			$GLOBALS["USER_FIELD_MANAGER"]->Delete("IBLOCK_".$s["IBLOCK_ID"]."_SECTION", $ID);

			//Delete the hole in the tree
			$ss = $DB->Query("
				SELECT
					IBLOCK_ID,
					LEFT_MARGIN,
					RIGHT_MARGIN
				FROM
					b_iblock_section
				WHERE
					ID = ".$s["ID"]."
			");
			$ss = $ss->Fetch();
			if(($ss["RIGHT_MARGIN"] > 0) && ($ss["LEFT_MARGIN"] > 0))
			{
				$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
						,RIGHT_MARGIN = RIGHT_MARGIN - 2
					WHERE
						IBLOCK_ID = ".$ss["IBLOCK_ID"]."
						AND RIGHT_MARGIN > ".$ss["RIGHT_MARGIN"]."
				");

				$DB->Query("
					UPDATE b_iblock_section SET
						TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
						,LEFT_MARGIN = LEFT_MARGIN - 2
					WHERE
						IBLOCK_ID = ".$ss["IBLOCK_ID"]."
						AND LEFT_MARGIN > ".$ss["LEFT_MARGIN"]."
				");
			}

			$obSectionRights = new CIBlockSectionRights($s["IBLOCK_ID"], $ID);
			$obSectionRights->DeleteAllRights();

			/************* QUOTA *************/
			$_SESSION["SESS_RECOUNT_DB"] = "Y";
			/************* QUOTA *************/

			$arIBlockFields = CIBlock::GetArrayByID($s["IBLOCK_ID"], "FIELDS");
			if($arIBlockFields["LOG_SECTION_DELETE"]["IS_REQUIRED"] == "Y")
			{
				$db_events = GetModuleEvents("main", "OnBeforeEventLog");
				$arEvent = $db_events->Fetch();
				global $USER;
				if(!$arEvent || ExecuteModuleEventEx($arEvent, array($USER->GetID()))===false)
				{
					$rsSection = CIBlockSection::GetList(
						array(),
						array("=ID"=>$ID, "CHECK_PERMISSIONS"=>($bCheckPermissions? "Y": "N")),
						false,
						array("LIST_PAGE_URL", "NAME", "CODE")
					);
					$arSection = $rsSection->GetNext();
					$res = array(
							"ID" => $ID,
							"CODE" => $arSection["CODE"],
							"NAME" => $arSection["NAME"],
							"SECTION_NAME" => CIBlock::GetArrayByID($s["IBLOCK_ID"], "SECTION_NAME"),
							"IBLOCK_PAGE_URL" => $arSection["LIST_PAGE_URL"]
							);
					CEventLog::Log(
						"IBLOCK",
						"IBLOCK_SECTION_DELETE",
						"iblock",
						$s["IBLOCK_ID"],
						serialize($res)
					);
				}
			}

			$res = $DB->Query("DELETE FROM b_iblock_section WHERE ID=".IntVal($ID), false, $err_mess.__LINE__);

			if($res)
			{
				$db_events = GetModuleEvents("iblock", "OnAfterIBlockSectionDelete");
				while($arEvent = $db_events->Fetch())
					ExecuteModuleEventEx($arEvent, array($s));

				if(defined("BX_COMP_MANAGED_CACHE"))
					$GLOBALS["CACHE_MANAGER"]->ClearByTag("iblock_id_".$s["IBLOCK_ID"]);
			}

			return $res;
		}

		return true;
	}

	///////////////////////////////////////////////////////////////////
	// Check function called from Add and Update
	///////////////////////////////////////////////////////////////////
	function CheckFields(&$arFields, $ID=false)
	{
		global $DB, $APPLICATION;
		$this->LAST_ERROR = "";

		if(($ID===false || is_set($arFields, "NAME")) && strlen($arFields["NAME"])<=0)
			$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION")."<br>";

		if(
			is_array($arFields["PICTURE"])
			&& array_key_exists("bucket", $arFields["PICTURE"])
			&& is_object($arFields["PICTURE"]["bucket"])
		)
		{
			//This is trusted image from xml import
		}
		elseif(
			isset($arFields["PICTURE"])
			&& is_array($arFields["PICTURE"])
			&& isset($arFields["PICTURE"]["name"])
		)
		{
			$error = CFile::CheckImageFile($arFields["PICTURE"]);
			if (strlen($error) > 0)
				$this->LAST_ERROR .= $error."<br>";
		}

		if(
			is_array($arFields["DETAIL_PICTURE"])
			&& array_key_exists("bucket", $arFields["DETAIL_PICTURE"])
			&& is_object($arFields["DETAIL_PICTURE"]["bucket"])
		)
		{
			//This is trusted image from xml import
		}
		elseif(
			isset($arFields["DETAIL_PICTURE"])
			&& is_array($arFields["DETAIL_PICTURE"])
			&& isset($arFields["DETAIL_PICTURE"]["name"])
		)
		{
			$error = CFile::CheckImageFile($arFields["DETAIL_PICTURE"]);
			if (strlen($error) > 0)
				$this->LAST_ERROR .= $error."<br>";
		}

		$arIBlock = false;
		$arThis = false;

		if($ID === false)
		{
			if(!array_key_exists("IBLOCK_ID", $arFields))
			{
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
			}
			else
			{
				$arIBlock = CIBlock::GetArrayByID($arFields["IBLOCK_ID"]);
				if(!$arIBlock)
					$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
			}
		}
		else
		{
			$rsThis = $DB->Query("SELECT ID, IBLOCK_ID, DETAIL_PICTURE, PICTURE FROM b_iblock_section WHERE ID = ".intval($ID));
			$arThis = $rsThis->Fetch();
			if(!$arThis)
			{
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_ID", array("#ID#" => intval($ID)))."<br>";
			}
			else
			{
				$arIBlock = CIBlock::GetArrayByID($arThis["IBLOCK_ID"]);
				if(!$arIBlock)
					$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_ID")."<br>";
			}
		}

		$arParent = false;
		$IBLOCK_SECTION_ID = isset($arFields["IBLOCK_SECTION_ID"])? intval($arFields["IBLOCK_SECTION_ID"]): 0;

		if(($IBLOCK_SECTION_ID > 0) && (strlen($this->LAST_ERROR) <= 0))
		{
			$rsParent = $DB->Query("SELECT ID, IBLOCK_ID FROM b_iblock_section WHERE ID = ".$IBLOCK_SECTION_ID);
			$arParent = $rsParent->Fetch();
			if(!$arParent)
				$this->LAST_ERROR = GetMessage("IBLOCK_BAD_BLOCK_SECTION_PARENT")."<br>";
		}

		if($arParent && $arIBlock)
		{
			if($arParent["IBLOCK_ID"] != $arIBlock["ID"])
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_SECTION_ID_PARENT")."<br>";
		}

		if($arParent && (strlen($this->LAST_ERROR) <= 0))
		{
			$rch = $DB->Query("
				SELECT 'x'
				FROM
					b_iblock_section bsto
					,b_iblock_section bsfrom
				WHERE
					bsto.ID = ".$arParent["ID"]."
					AND bsfrom.ID = ".intval($ID)."
					AND bsto.LEFT_MARGIN >= bsfrom.LEFT_MARGIN
					AND bsto.LEFT_MARGIN <= bsfrom.RIGHT_MARGIN
			");
			if($rch->Fetch())
				$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_BLOCK_SECTION_RECURSE")."<br>";
		}

		if($arIBlock)
		{
			if(
				array_key_exists("CODE", $arFields)
				&& strlen($arFields["CODE"])
				&& is_array($arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"])
				&& $arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"]["UNIQUE"] == "Y"
			)
			{
				$res = $DB->Query("
					SELECT ID
					FROM b_iblock_section
					WHERE IBLOCK_ID = ".$arIBlock["ID"]."
					AND CODE = '".$DB->ForSQL($arFields["CODE"])."'
					AND ID <> ".intval($ID)
				);
				if($res->Fetch())
					$this->LAST_ERROR .= GetMessage("IBLOCK_DUP_SECTION_CODE")."<br>";
			}

			foreach($arIBlock["FIELDS"] as $FIELD_ID => $field)
			{
				if(!preg_match("/^SECTION_(.+)$/", $FIELD_ID, $match))
					continue;

				$FIELD_ID = $match[1];

				if($field["IS_REQUIRED"] === "Y")
				{
					switch($FIELD_ID)
					{
					case "NAME":
					case "DESCRIPTION_TYPE":
						//We should never check for this fields
						break;
					case "PICTURE":
						$field["NAME"] = GetMessage("IBLOCK_FIELD_PICTURE");
					case "DETAIL_PICTURE":
						if($arThis && $arThis[$FIELD_ID] > 0)
						{//There was an picture so just check that it is not deleted
							if(
								array_key_exists($FIELD_ID, $arFields)
								&& is_array($arFields[$FIELD_ID])
								&& $arFields[$FIELD_ID]["del"] === "Y"
							)
								$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
						}
						else
						{//There was NO picture so it MUST be present
							if(!array_key_exists($FIELD_ID, $arFields))
							{
								$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							elseif(is_array($arFields[$FIELD_ID]))
							{
								if(
									$arFields[$FIELD_ID]["del"] === "Y"
									|| (array_key_exists("error", $arFields[$FIELD_ID]) && $arFields[$FIELD_ID]["error"] !== 0)
									|| $arFields[$FIELD_ID]["size"] <= 0
								)
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
							else
							{
								if(intval($arFields[$FIELD_ID]) <= 0)
									$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
							}
						}
						break;
					default:
						if($ID===false || array_key_exists($FIELD_ID, $arFields))
						{
							if(is_array($arFields[$FIELD_ID]))
								$val = implode("", $arFields[$FIELD_ID]);
							else
								$val = $arFields[$FIELD_ID];
							if(strlen($val) <= 0)
								$this->LAST_ERROR .= GetMessage("IBLOCK_BAD_SECTION_FIELD", array("#FIELD_NAME#" => $field["NAME"]))."<br>";
						}
						break;
					}
				}
			}
		}

		$APPLICATION->ResetException();
		if($ID===false)
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockSectionAdd");
		else
		{
			$arFields["ID"] = $ID;
			$arFields["IBLOCK_ID"] = $arIBlock["ID"];
			$db_events = GetModuleEvents("iblock", "OnBeforeIBlockSectionUpdate");
		}

		/****************************** QUOTA ******************************/
		if(empty($this->LAST_ERROR) && (COption::GetOptionInt("main", "disk_space") > 0))
		{
			$quota = new CDiskQuota();
			if(!$quota->checkDiskQuota($arFields))
				$this->LAST_ERROR = $quota->LAST_ERROR;
		}
		/****************************** QUOTA ******************************/

		while($arEvent = $db_events->Fetch())
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
				{
					$APPLICATION->ThrowException("Unknown error");
					$this->LAST_ERROR .= "Unknown error.<br>";
				}
				break;
			}
		}

		if(strlen($this->LAST_ERROR)>0)
			return false;

		return true;
	}


	function ReSort($IBLOCK_ID, $ID=0, $cnt=0, $depth=0, $ACTIVE="Y")
	{
		global $DB;
		$IBLOCK_ID = IntVal($IBLOCK_ID);

		if($ID > 0)
		{
			$DB->Query("
				UPDATE
					b_iblock_section
				SET
					TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
					,RIGHT_MARGIN=".IntVal($cnt)."
					,LEFT_MARGIN=".IntVal($cnt)."
				WHERE
					ID=".IntVal($ID)
			);
		}

		$strSql = "
			SELECT BS.ID, BS.ACTIVE
			FROM b_iblock_section BS
			WHERE BS.IBLOCK_ID = ".$IBLOCK_ID."
			AND ".($ID>0? "BS.IBLOCK_SECTION_ID=".IntVal($ID): "BS.IBLOCK_SECTION_ID IS NULL")."
			ORDER BY BS.SORT, BS.NAME
		";

		$cnt++;
		$res = $DB->Query($strSql);
		while($arr = $res->Fetch())
			$cnt = CIBlockSection::ReSort($IBLOCK_ID, $arr["ID"], $cnt, $depth+1, ($ACTIVE=="Y" && $arr["ACTIVE"]=="Y" ? "Y" : "N"));

		if($ID==0)
		{
			$obIBlockRights = new CIBlockRights($IBLOCK_ID);
			$obIBlockRights->Recalculate();
			return true;
		}

		$DB->Query("
			UPDATE
				b_iblock_section
			SET
				TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
				,RIGHT_MARGIN=".IntVal($cnt)."
				,DEPTH_LEVEL=".IntVal($depth)."
				,GLOBAL_ACTIVE='".$ACTIVE."'
			WHERE
				ID=".IntVal($ID)
		);

		return $cnt+1;
	}

	function UpdateSearch($ID, $bOverWrite=false)
	{
		if(!CModule::IncludeModule("search")) return;

		global $DB;
		$ID = Intval($ID);

		static $arGroups = array();
		static $arSITE = array();

		$strSql = "
			SELECT BS.ID, BS.NAME, BS.DESCRIPTION_TYPE, BS.DESCRIPTION, BS.XML_ID as EXTERNAL_ID,
				BS.CODE, BS.IBLOCK_ID, B.IBLOCK_TYPE_ID,
				".$DB->DateToCharFunction("BS.TIMESTAMP_X")." as LAST_MODIFIED,
				B.CODE as IBLOCK_CODE, B.XML_ID as IBLOCK_EXTERNAL_ID, B.SECTION_PAGE_URL,
				B.ACTIVE as ACTIVE1,
				BS.GLOBAL_ACTIVE as ACTIVE2,
				B.INDEX_SECTION, B.RIGHTS_MODE
			FROM b_iblock_section BS, b_iblock B
			WHERE BS.IBLOCK_ID=B.ID
				AND BS.ID=".$ID;

		$dbrIBlockSection = $DB->Query($strSql);

		if($arIBlockSection = $dbrIBlockSection->Fetch())
		{
			$IBLOCK_ID = $arIBlockSection["IBLOCK_ID"];
			$SECTION_URL =
					"=ID=".$arIBlockSection["ID"].
					"&EXTERNAL_ID=".$arIBlockSection["EXTERNAL_ID"].
					"&IBLOCK_TYPE_ID=".$arIBlockSection["IBLOCK_TYPE_ID"].
					"&IBLOCK_ID=".$arIBlockSection["IBLOCK_ID"].
					"&IBLOCK_CODE=".$arIBlockSection["IBLOCK_CODE"].
					"&IBLOCK_EXTERNAL_ID=".$arIBlockSection["IBLOCK_EXTERNAL_ID"].
					"&CODE=".$arIBlockSection["CODE"];

			if($arIBlockSection["ACTIVE1"]!="Y" || $arIBlockSection["ACTIVE2"]!="Y" || $arIBlockSection["INDEX_SECTION"]!="Y")
			{
				CSearch::DeleteIndex("iblock", "S".$arIBlockSection["ID"]);
				return;
			}

			if(!array_key_exists($IBLOCK_ID, $arGroups))
			{
				$arGroups[$IBLOCK_ID] = array();
				$strSql =
					"SELECT GROUP_ID ".
					"FROM b_iblock_group ".
					"WHERE IBLOCK_ID= ".$IBLOCK_ID." ".
					"	AND PERMISSION>='R' ".
					"ORDER BY GROUP_ID";

				$dbrIBlockGroup = $DB->Query($strSql);
				while($arIBlockGroup = $dbrIBlockGroup->Fetch())
				{
					$arGroups[$IBLOCK_ID][] = $arIBlockGroup["GROUP_ID"];
					if($arIBlockGroup["GROUP_ID"]==2) break;
				}
			}

			if(!array_key_exists($IBLOCK_ID, $arSITE))
			{
				$arSITE[$IBLOCK_ID] = array();
				$strSql =
					"SELECT SITE_ID ".
					"FROM b_iblock_site ".
					"WHERE IBLOCK_ID= ".$IBLOCK_ID;

				$dbrIBlockSite = $DB->Query($strSql);
				while($arIBlockSite = $dbrIBlockSite->Fetch())
					$arSITE[$IBLOCK_ID][] = $arIBlockSite["SITE_ID"];
			}

			$BODY =
				($arIBlockSection["DESCRIPTION_TYPE"]=="html" ?
					CSearch::KillTags($arIBlockSection["DESCRIPTION"])
				:
					$arIBlockSection["DESCRIPTION"]
				);

			$BODY .= $GLOBALS["USER_FIELD_MANAGER"]->OnSearchIndex("IBLOCK_".$arIBlockSection["IBLOCK_ID"]."_SECTION", $arIBlockSection["ID"]);

			if($arIBlockSection["RIGHTS_MODE"] !== "E")
				$arPermissions = $arGroups[$IBLOCK_ID];
			else
			{
				$obSectionRights = new CIBlockSectionRights($IBLOCK_ID, $arIBlockSection["ID"]);
				$arPermissions = $obSectionRights->GetGroups(array("section_read"));
			}

			CSearch::Index("iblock", "S".$ID, array(
				"LAST_MODIFIED" => $arIBlockSection["LAST_MODIFIED"],
				"TITLE" => $arIBlockSection["NAME"],
				"PARAM1" => $arIBlockSection["IBLOCK_TYPE_ID"],
				"PARAM2" => $IBLOCK_ID,
				"SITE_ID" => $arSITE[$IBLOCK_ID],
				"PERMISSIONS" => $arPermissions,
				"URL" => $SECTION_URL,
				"BODY" => $BODY,
			), $bOverWrite);
		}
	}

	function GetMixedList($arOrder=Array("SORT"=>"ASC"), $arFilter=Array(), $bIncCnt = false, $arSelectedFields = false)
	{
		global $DB;

		$arResult = array();

		$arSectionFilter = array (
			"IBLOCK_ID"		=>$arFilter["IBLOCK_ID"],
			"?NAME"			=>$arFilter["NAME"],
			">=ID"			=>$arFilter["ID_1"],
			"<=ID"			=>$arFilter["ID_2"],
			">=TIMESTAMP_X"		=>$arFilter["TIMESTAMP_X_1"],
			"<=TIMESTAMP_X"		=>$arFilter["TIMESTAMP_X_2"],
			"MODIFIED_BY"		=>$arFilter["MODIFIED_USER_ID"]? $arFilter["MODIFIED_USER_ID"]: $arFilter["MODIFIED_BY"],
			">=DATE_CREATE"		=>$arFilter["DATE_CREATE_1"],
			"<=DATE_CREATE"		=>$arFilter["DATE_CREATE_2"],
			"CREATED_BY"		=>$arFilter["CREATED_USER_ID"]? $arFilter["CREATED_USER_ID"]: $arFilter["CREATED_BY"],
			"CODE"			=>$arFilter["CODE"],
			"EXTERNAL_ID"		=>$arFilter["EXTERNAL_ID"],
			"ACTIVE"		=>$arFilter["ACTIVE"],

			"CNT_ALL"		=>$arFilter["CNT_ALL"],
			"ELEMENT_SUBSECTIONS"	=>$arFilter["ELEMENT_SUBSECTIONS"],
		);
		if (isset($arFilter["CHECK_PERMISSIONS"]))
		{
			$arSectionFilter['CHECK_PERMISSIONS'] = $arFilter["CHECK_PERMISSIONS"];
			$arSectionFilter['MIN_PERMISSION'] = (isset($arFilter['MIN_PERMISSION']) ? $arFilter['MIN_PERMISSION'] : 'R');
		}
		if(array_key_exists("SECTION_ID", $arFilter))
			$arSectionFilter["SECTION_ID"] = $arFilter["SECTION_ID"];

		$obSection = new CIBlockSection;
		$rsSection = $obSection->GetList($arOrder, $arSectionFilter, $bIncCnt);
		while($arSection = $rsSection->Fetch())
		{
			$arSection["TYPE"]="S";
			$arResult[]=$arSection;
		}

		$arElementFilter = array (
			"IBLOCK_ID"		=>$arFilter["IBLOCK_ID"],
			"?NAME"			=>$arFilter["NAME"],
			"SECTION_ID"		=>$arFilter["SECTION_ID"],
			">=ID"			=>$arFilter["ID_1"],
			"<=ID"			=>$arFilter["ID_2"],
			"=ID"			=> $arFilter["ID"],
			">=TIMESTAMP_X"		=>$arFilter["TIMESTAMP_X_1"],
			"<=TIMESTAMP_X"		=>$arFilter["TIMESTAMP_X_2"],
			"CODE"			=>$arFilter["CODE"],
			"EXTERNAL_ID"		=>$arFilter["EXTERNAL_ID"],
			"MODIFIED_USER_ID"	=>$arFilter["MODIFIED_USER_ID"],
			"MODIFIED_BY"		=>$arFilter["MODIFIED_BY"],
			">=DATE_CREATE"		=>$arFilter["DATE_CREATE_1"],
			"<=DATE_CREATE"		=>$arFilter["DATE_CREATE_2"],
			"CREATED_BY"		=>$arFilter["CREATED_BY"],
			"CREATED_USER_ID"	=>$arFilter["CREATED_USER_ID"],
			">=DATE_ACTIVE_FROM"	=>$arFilter["DATE_ACTIVE_FROM_1"],
			"<=DATE_ACTIVE_FROM"	=>$arFilter["DATE_ACTIVE_FROM_2"],
			">=DATE_ACTIVE_TO"	=>$arFilter["DATE_ACTIVE_TO_1"],
			"<=DATE_ACTIVE_TO"	=>$arFilter["DATE_ACTIVE_TO_2"],
			"ACTIVE"		=>$arFilter["ACTIVE"],
			"?SEARCHABLE_CONTENT"	=>$arFilter["DESCRIPTION"],
			"?TAGS"			=>$arFilter["?TAGS"],
			"WF_STATUS"		=>$arFilter["WF_STATUS"],

			"SHOW_NEW"		=> ($arFilter["SHOW_NEW"] !== "N"? "Y": "N"),
			"SHOW_BP_NEW"		=> $arFilter["SHOW_BP_NEW"]
		);
		if (isset($arFilter["CHECK_PERMISSIONS"]))
		{
			$arElementFilter['CHECK_PERMISSIONS'] = $arFilter["CHECK_PERMISSIONS"];
			$arElementFilter['MIN_PERMISSION'] = (isset($arFilter['MIN_PERMISSION']) ? $arFilter['MIN_PERMISSION'] : 'R');
		}

		foreach($arFilter as $key=>$value)
		{
			$op = CIBlock::MkOperationFilter($key);
			$newkey = strtoupper($op["FIELD"]);
			if(
				substr($newkey, 0, 9) == "PROPERTY_"
				|| substr($newkey, 0, 8) == "CATALOG_"
			)
			{
				$arElementFilter[$key] = $value;
			}
		}

		if(strlen($arFilter["SECTION_ID"])<= 0)
			unset($arElementFilter["SECTION_ID"]);

		if(!is_array($arSelectedFields))
			$arSelectedFields = Array("*");

		if(isset($arFilter["CHECK_BP_PERMISSIONS"]))
			$arElementFilter["CHECK_BP_PERMISSIONS"] = $arFilter["CHECK_BP_PERMISSIONS"];

		$obElement = new CIBlockElement;

		$rsElement = $obElement->GetList($arOrder, $arElementFilter, false, false, $arSelectedFields);
		while($arElement = $rsElement->Fetch())
		{
			$arElement["TYPE"]="E";
			$arResult[]=$arElement;
		}

		$rsResult = new CDBResult;
		$rsResult->InitFromArray($arResult);

		return $rsResult;
	}

	///////////////////////////////////////////////////////////////////
	// GetSectionElementsCount($ID, $arFilter=Array())
	///////////////////////////////////////////////////////////////////
	function GetSectionElementsCount($ID, $arFilter=Array())
	{
		global $DB, $USER;

		$arJoinProps = array();
		$bJoinFlatProp = false;
		$arSqlSearch = array();

		if(array_key_exists("PROPERTY", $arFilter))
		{
			$val = $arFilter["PROPERTY"];
			foreach($val as $propID=>$propVAL)
			{
				$res = CIBlock::MkOperationFilter($propID);
				$propID = $res["FIELD"];
				$cOperationType = $res["OPERATION"];
				if($db_prop = CIBlockProperty::GetPropertyArray($propID, CIBlock::_MergeIBArrays($arFilter["IBLOCK_ID"], $arFilter["IBLOCK_CODE"])))
				{

					$bSave = false;
					if(array_key_exists($db_prop["ID"], $arJoinProps))
						$iPropCnt = $arJoinProps[$db_prop["ID"]];
					elseif($db_prop["VERSION"]!=2 || $db_prop["MULTIPLE"]=="Y")
					{
						$bSave = true;
						$iPropCnt=count($arJoinProps);
					}

					if(!is_array($propVAL))
						$propVAL = Array($propVAL);

					if($db_prop["PROPERTY_TYPE"]=="N" || $db_prop["PROPERTY_TYPE"]=="G" || $db_prop["PROPERTY_TYPE"]=="E")
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "number", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE_NUM", $propVAL, "number", $cOperationType);
					}
					else
					{
						if($db_prop["VERSION"]==2 && $db_prop["MULTIPLE"]=="N")
						{
							$r = CIBlock::FilterCreate("FPS.PROPERTY_".$db_prop["ORIG_ID"], $propVAL, "string", $cOperationType);
							$bJoinFlatProp = $db_prop["IBLOCK_ID"];
						}
						else
							$r = CIBlock::FilterCreate("FPV".$iPropCnt.".VALUE", $propVAL, "string", $cOperationType);
					}

					if(strlen($r)>0)
					{
						if($bSave)
						{
							$db_prop["iPropCnt"] = $iPropCnt;
							$arJoinProps[$db_prop["ID"]] = $db_prop;
						}
						$arSqlSearch[] = $r;
					}
				}
			}
		}

		$strSqlSearch = "";
		foreach($arSqlSearch as $r)
			if(strlen($r)>0)
				$strSqlSearch .= "\n\t\t\t\tAND  (".$r.") ";

		$strSqlSearchProp = "";
		foreach($arJoinProps as $propID=>$db_prop)
		{
			if($db_prop["VERSION"]==2)
				$strTable = "b_iblock_element_prop_m".$db_prop["IBLOCK_ID"];
			else
				$strTable = "b_iblock_element_property";
			$i = $db_prop["iPropCnt"];
			$strSqlSearchProp .= "
				INNER JOIN b_iblock_property FP".$i." ON FP".$i.".IBLOCK_ID=BS.IBLOCK_ID AND
				".(IntVal($propID)>0?" FP".$i.".ID=".IntVal($propID)." ":" FP".$i.".CODE='".$DB->ForSQL($propID, 200)."' ")."
				INNER JOIN ".$strTable." FPV".$i." ON FP".$i.".ID=FPV".$i.".IBLOCK_PROPERTY_ID AND FPV".$i.".IBLOCK_ELEMENT_ID=BE.ID
			";
		}
		if($bJoinFlatProp)
			$strSqlSearchProp .= "
				INNER JOIN b_iblock_element_prop_s".$bJoinFlatProp." FPS ON FPS.IBLOCK_ELEMENT_ID = BE.ID
			";

		$strHint = $DB->type=="MYSQL"?"STRAIGHT_JOIN":"";
		$strSql = "
			SELECT ".$strHint." COUNT(DISTINCT BE.ID) as CNT
			FROM b_iblock_section BS
				INNER JOIN b_iblock_section BSTEMP ON (BSTEMP.IBLOCK_ID=BS.IBLOCK_ID
					AND BSTEMP.LEFT_MARGIN >= BS.LEFT_MARGIN
					AND BSTEMP.RIGHT_MARGIN <= BS.RIGHT_MARGIN)
				INNER JOIN b_iblock_section_element BSE ON BSE.IBLOCK_SECTION_ID=BSTEMP.ID
				INNER JOIN b_iblock_element BE ON BE.ID=BSE.IBLOCK_ELEMENT_ID AND BE.IBLOCK_ID=BS.IBLOCK_ID
			".$strSqlSearchProp."
			WHERE BS.ID=".IntVal($ID)."
				AND ((BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
				".($arFilter["CNT_ALL"]=="Y"?" OR BE.WF_NEW='Y' ":"").")
				".($arFilter["CNT_ACTIVE"]=="Y"?
					" AND BE.ACTIVE='Y'
					AND (BE.ACTIVE_TO >= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_TO IS NULL)
					AND (BE.ACTIVE_FROM <= ".$DB->CurrentTimeFunction()." OR BE.ACTIVE_FROM IS NULL)"
				:"")."
				".$strSqlSearch;
		//echo "<pre>",htmlspecialchars($strSql),"</pre>";
		$res = $DB->Query($strSql);
		$res = $res->Fetch();
		return $res["CNT"];
	}

	function _check_rights_sql($min_permission)
	{
		global $DB, $USER;
		$min_permission = (strlen($min_permission)==1) ? $min_permission : "R";

		if(is_object($USER))
		{
			$iUserID = intval($USER->GetID());
			$strGroups = $USER->GetGroups();
			$bAuthorized = $USER->IsAuthorized();
		}
		else
		{
			$iUserID = 0;
			$strGroups = "2";
			$bAuthorized = false;
		}

		$stdPermissions = "
			SELECT IBLOCK_ID
			FROM b_iblock_group IBG
			WHERE IBG.GROUP_ID IN (".$strGroups.")
			AND IBG.PERMISSION >= '".$DB->ForSQL($min_permission)."'
		";
		if(!defined("ADMIN_SECTION"))
			$stdPermissions .= "
				AND (IBG.PERMISSION='X' OR B.ACTIVE='Y')
			";

		if($min_permission >= "X")
			$operation = 'section_rights_edit';
		elseif($min_permission >= "W")
			$operation = 'section_edit';
		elseif($min_permission >= "R")
			$operation = 'section_read';
		else
			$operation = '';

		if($operation)
		{
			$acc = new CAccess;
			$acc->UpdateCodes();
		}

		if($operation == "section_read")
		{
			$extPermissions = "
				SELECT SR.SECTION_ID
				FROM b_iblock_section_right SR
				INNER JOIN b_iblock_right IBR ON IBR.ID = SR.RIGHT_ID
				".($iUserID > 0? "LEFT": "INNER")." JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = ".$iUserID."
				WHERE SR.SECTION_ID = BS.ID
				AND IBR.OP_SREAD = 'Y'
				".($bAuthorized || $iUserID > 0? "
					AND (UA.USER_ID IS NOT NULL
					".($bAuthorized? "OR IBR.GROUP_CODE = 'AU'": "")."
					".($iUserID > 0? "OR (IBR.GROUP_CODE = 'CR' AND BS.CREATED_BY = ".$iUserID.")": "")."
				)": "")."
			";

			$strResult = "(
				B.ID IN ($stdPermissions)
				OR (B.RIGHTS_MODE = 'E' AND EXISTS ($extPermissions))
			)";
		}
		elseif($operation)
		{
			$extPermissions = "
				SELECT SR.SECTION_ID
				FROM b_iblock_section_right SR
				INNER JOIN b_iblock_right IBR ON IBR.ID = SR.RIGHT_ID
				INNER JOIN b_task_operation T ON T.TASK_ID = IBR.TASK_ID
				INNER JOIN b_operation O ON O.ID = T.OPERATION_ID
				".($iUserID > 0? "LEFT": "INNER")." JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = ".$iUserID."
				WHERE SR.SECTION_ID = BS.ID
				AND O.NAME = '".$operation."'
				".($bAuthorized || $iUserID > 0? "
					AND (UA.USER_ID IS NOT NULL
					".($bAuthorized? "OR IBR.GROUP_CODE = 'AU'": "")."
					".($iUserID > 0? "OR (IBR.GROUP_CODE = 'CR' AND BS.CREATED_BY = ".$iUserID.")": "")."
				)": "")."
			";

			$strResult = "(
				B.ID IN ($stdPermissions)
				OR (B.RIGHTS_MODE = 'E' AND EXISTS ($extPermissions))
			)";
		}
		else
		{
			$strResult = "(
				B.ID IN ($stdPermissions)
			)";
		}

		return $strResult;
	}

	function GetCount($arFilter=Array())
	{
		global $DB, $USER;

		$arSqlSearch = CIBlockSection::GetFilter($arFilter);

		$bCheckPermissions = !array_key_exists("CHECK_PERMISSIONS", $arFilter) || $arFilter["CHECK_PERMISSIONS"]!=="N";
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		if($bCheckPermissions && !$bIsAdmin)
		{
			$min_permission = (strlen($arFilter["MIN_PERMISSION"])==1) ? $arFilter["MIN_PERMISSION"] : "R";
			$arSqlSearch[] = CIBlockSection::_check_rights_sql($min_permission);
		}

		$strSqlSearch = "";
		foreach($arSqlSearch as $i=>$strSearch)
			if(strlen($strSearch)>0)
				$strSqlSearch .= "\n\t\t\tAND  (".$strSearch.") ";

		$strSql = "
			SELECT COUNT(DISTINCT BS.ID) as C
			FROM b_iblock_section BS
				INNER JOIN b_iblock B ON BS.IBLOCK_ID = B.ID
			WHERE 1=1
			".$strSqlSearch."
		";

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$res_cnt = $res->Fetch();
		return IntVal($res_cnt["C"]);
	}

	function UserTypeRightsCheck($entity_id)
	{
		if(preg_match("/^IBLOCK_(\d+)_SECTION$/", $entity_id, $match))
		{
			return CIBlock::GetPermission($match[1]);
		}
		else
			return "D";
	}

	function RecalcGlobalActiveFlag($arSection)
	{
		global $DB;

		//Make all children globally active
		$DB->Query("
			UPDATE b_iblock_section SET
				TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
				,GLOBAL_ACTIVE = 'Y'
			WHERE
				IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
				AND LEFT_MARGIN >= ".intval($arSection["LEFT_MARGIN"])."
				AND RIGHT_MARGIN <= ".intval($arSection["RIGHT_MARGIN"])."
		");
		//Select those who is not active
		$strSql = "
			SELECT ID, LEFT_MARGIN, RIGHT_MARGIN
			FROM b_iblock_section
			WHERE IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
			AND LEFT_MARGIN >= ".intval($arSection["LEFT_MARGIN"])."
			AND RIGHT_MARGIN <= ".intval($arSection["RIGHT_MARGIN"])."
			AND ACTIVE = 'N'
			ORDER BY LEFT_MARGIN
		";
		$arUpdate = array();
		$prev_right = 0;
		$rsChildren = $DB->Query($strSql);
		while($arChild = $rsChildren->Fetch())
		{
			if($arChild["RIGHT_MARGIN"] > $prev_right)
			{
				$prev_right = $arChild["RIGHT_MARGIN"];
				$arUpdate[] = "(LEFT_MARGIN >= ".$arChild["LEFT_MARGIN"]." AND RIGHT_MARGIN <= ".$arChild["LEFT_MARGIN"].")\n";
			}
		}
		if(count($arUpdate) > 0)
		{
			$DB->Query("
				UPDATE b_iblock_section SET
					TIMESTAMP_X=".($DB->type=="ORACLE"?"NULL":"TIMESTAMP_X")."
					,GLOBAL_ACTIVE = 'N'
				WHERE
					IBLOCK_ID = ".$arSection["IBLOCK_ID"]."
					AND (".implode(" OR ", $arUpdate).")
			");
		}
	}
}

?>