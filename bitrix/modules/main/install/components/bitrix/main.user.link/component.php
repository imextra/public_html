<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$bSocialNetwork = IsModuleInstalled('socialnetwork');
$bIntranet = IsModuleInstalled('intranet');

if ($bSocialNetwork)
{
	$bUseTooltip = COption::GetOptionString("socialnetwork", "allow_tooltip", "Y") == "Y";

	if(!defined("BXMAINUSERLINK"))
	{
		define("BXMAINUSERLINK", true);
		$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
		CAjax::Init();
		if ($bUseTooltip)
			CUtil::InitJSCore(array("ajax", "tooltip"));
		else
			CUtil::InitJSCore(array("ajax"));
	}
}
else
	$_GET["MUL_MODE"] = "";

$arParams['AJAX_CALL'] = $_GET["MUL_MODE"];

if ($bSocialNetwork)
{
	if ($bIntranet)
	{
		$arTooltipFieldsDefault	= serialize(array(
			"EMAIL",
			"WORK_PHONE",
			"PERSONAL_PHOTO",
			"PERSONAL_CITY",
			"WORK_COMPANY",
			"WORK_POSITION",
			"MANAGERS",
		));
		$arTooltipPropertiesDefault = serialize(array(
			"UF_DEPARTMENT",
			"UF_PHONE_INNER",
			"UF_SKYPE",
		));
	}
	else
	{
		$arTooltipFieldsDefault = serialize(array(
			"PERSONAL_ICQ",
			"PERSONAL_BIRTHDAY",
			"PERSONAL_PHOTO",
			"PERSONAL_CITY",
			"WORK_COMPANY",
			"WORK_POSITION"
		));
		$arTooltipPropertiesDefault = serialize(array());
	}

	if (!array_key_exists("SHOW_FIELDS", $arParams) || !$arParams["SHOW_FIELDS"])
		$arParams["SHOW_FIELDS"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_fields", $arTooltipFieldsDefault));
	if (!array_key_exists("USER_PROPERTY", $arParams) || !$arParams["USER_PROPERTY"])
		$arParams["USER_PROPERTY"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_properties", $arTooltipPropertiesDefault));

	if (COption::GetOptionString("socialnetwork", "tooltip_show_rating", "N") == "Y")
		$arParams["USER_RATING"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_rating_id", serialize(array())));

}

if (!function_exists('MULChangeOnlineStatus') && $bSocialNetwork && !array_key_exists("IS_ONLINE", $arParams))
{

	function MULChangeOnlineStatus($USER_ID = false, $HTML_ID = false)
	{
		static $arUserList, $arUserListID, $arUserListHTML_ID, $arUserListOnlineHTML_ID;
		static $bNotFirstCall;

		if (!$bNotFirstCall)
		{
			AddEventHandler("main", "OnEpilog", "MULChangeOnlineStatus");
			$bNotFirstCall = true;
		}

		if (intval($USER_ID) > 0)
		{
			if (!$HTML_ID)
				$HTML_ID = "main_".$USER_ID;

			$arUserListID[] = $USER_ID;
			$arUserListHTML_ID[] = "'".$HTML_ID."'";
			$arUserList[] = array("USER_ID" => $USER_ID, "HTML_ID" => $HTML_ID);
		}
		else
		{
			$arUserListIDUnique = array_unique($arUserListID);
			$strUserListID = implode("|", $arUserListIDUnique);
			$rsUser = CUser::GetList(($by="id"), ($order="desc"), array("ID" => $strUserListID));

			$arUserListOnlineHTML_ID = array();
			while($arUser = $rsUser->Fetch())
			{
				if ((time() - intval(MakeTimeStamp($arUser["LAST_ACTIVITY_DATE"], "YYYY-MM-DD HH-MI-SS"))) < 120)
				{
					foreach($arUserList as $arTmp)
						if ($arUser["ID"] == $arTmp["USER_ID"])
							$arUserListOnlineHTML_ID[] = "'".$arTmp["HTML_ID"]."'";
				}
			}

			if (!function_exists("__bx_mul_apos"))
			{
				function __bx_mul_apos(&$item, $key)
				{
					$item = "'".$item."'";
				}
			}

			?><script type="text/javascript">
				top.jsUtils.addEvent(window, "load", function() {

				var arMULUserList = new Array(<?=implode(",", $arUserListHTML_ID)?>);
				var arMULUserListOnline = new Array(<?=implode(",", $arUserListOnlineHTML_ID)?>);

				for(var i=0; i<arMULUserList.length; i++)
				{
					elOnline = document.getElementById(arMULUserList[i]);
					if(elOnline)
					{
						if (BX.util.in_array(arMULUserList[i], arMULUserListOnline))
						{
							elOnline.className = "bx-user-info-online";
							elOnline.title = "<?=GetMessage("MAIN_UL_ONLINE")?>";
						}
						else
						{
							elOnline.className = "bx-user-info-offline";
							elOnline.title = "";
						}

					}

				}
				});
			</script><?
		}
	}
}

if (intval($_GET["USER_ID"]) > 0)
	$arParams["ID"] = $_GET["USER_ID"];

$arParams["ID"] = IntVal($arParams["ID"]);

if ($arParams["ID"] <= 0 && $arParams["AJAX_ONLY"] != "Y")
	$arResult["FatalError"] = GetMessage("MAIN_UL_NO_ID").". ";
elseif (strlen(trim($arParams["HTML_ID"])) <= 0)
	$arParams["HTML_ID"] = "mul_".RandString(8);

if ($arParams['USE_THUMBNAIL_LIST'] != "N")
{
	$arParams['USE_THUMBNAIL_LIST'] = "Y";
	if (intval($arParams['THUMBNAIL_LIST_SIZE']) <= 0)
		$arParams['THUMBNAIL_LIST_SIZE'] = 30;
}

if (array_key_exists("SHOW_FIELDS", $arParams) && in_array("PERSONAL_PHOTO", $arParams['SHOW_FIELDS']) && intval($arParams['THUMBNAIL_DETAIL_SIZE']) <= 0)
	$arParams['THUMBNAIL_DETAIL_SIZE'] = 100;

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;

$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : GetMessage('MAIN_UL_NAME_TEMPLATE_DEFAULT');
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

if (!array_key_exists("DO_RETURN", $arParams))
	$arParams["DO_RETURN"] = "N";

if ($arParams["DO_RETURN"] != "Y")
	$arParams["DO_RETURN"] = "N";

$bNeedGetUser = false;

if (strlen($arResult["FatalError"]) <= 0)
{
	if ($bSocialNetwork && !array_key_exists("IS_ONLINE", $arParams) && $arParams["AJAX_ONLY"] != "Y" && (!array_key_exists("INLINE", $arParams) || $arParams["INLINE"] != "Y"))
		MULChangeOnlineStatus($arParams["ID"], $arParams["HTML_ID"]);

	if ($arParams['AJAX_CALL'] == 'INFO')
		$bNeedGetUser = true;
	elseif(intval($arParams["ID"]) > 0)
	{
		if (!array_key_exists("NAME", $arParams) || !array_key_exists("LAST_NAME", $arParams) || !array_key_exists("SECOND_NAME", $arParams)  || !array_key_exists("LOGIN", $arParams))
			$bNeedGetUser = true;
		if ($arParams['USE_THUMBNAIL_LIST'] == "Y" && !array_key_exists("PERSONAL_PHOTO_IMG", $arParams))
			$bNeedGetUser = true;
	}

	if ($bSocialNetwork && CModule::IncludeModule('socialnetwork'))
	{
		if ($arParams['AJAX_CALL'] == 'INFO')
			$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arParams["ID"], CSocNetUser::IsCurrentUserModuleAdmin());
		else
			$arResult["CurrentUserPerms"] = array(
				"Operations" => array(
					"viewprofile" => true,
					"videocall" => true,
					"message" => true
				)
			);

		if (!$bUseTooltip)
			$arResult["USE_TOOLTIP"] = false;

		if(!CModule::IncludeModule("video"))
			$arResult["CurrentUserPerms"]["Operations"]["videocall"] = false;
		elseif(!CVideo::CanUserMakeCall())
			$arResult["CurrentUserPerms"]["Operations"]["videocall"] = false;

		if ($arParams['AJAX_CALL'] != 'INFO' && strlen($arParams["PROFILE_URL_LIST"]) > 0) // don't use PROFILE_URL in ajax call because it could be another component inclusion
			$arResult["Urls"]["SonetProfile"] = $arParams["~PROFILE_URL_LIST"];
		elseif ($arParams['AJAX_CALL'] != 'INFO' && strlen($arParams["PROFILE_URL"]) > 0)
			$arResult["Urls"]["SonetProfile"] = $arParams["~PROFILE_URL"];
		elseif(strlen($arParams["PATH_TO_SONET_USER_PROFILE"]) > 0)
			$arResult["Urls"]["SonetProfile"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_SONET_USER_PROFILE"], array("user_id" => $arParams["ID"], "USER_ID" => $arParams["ID"], "ID" => $arParams["ID"]));

		if (strlen($arResult["Urls"]["SonetProfile"]) <= 0 && $bIntranet)
		{
			$arParams['DETAIL_URL'] = COption::GetOptionString('intranet', 'search_user_url', '/user/#ID#/');
			$arParams['DETAIL_URL'] = str_replace(array('#ID#', '#USER_ID#'), array($arParams["ID"], $arParams["ID"]), $arParams['DETAIL_URL']);
		}
		else
			$arParams['DETAIL_URL'] = $arResult["Urls"]["SonetProfile"];
	}
	else
	{
		if (strlen($arParams["PROFILE_URL_LIST"]) > 0)
			$arParams['DETAIL_URL'] = $arParams["~PROFILE_URL_LIST"];
		elseif (strlen($arParams["PROFILE_URL"]) > 0)
			$arParams['DETAIL_URL'] = $arParams["~PROFILE_URL"];
	}

	$arResult["User"]["DETAIL_URL"] = $tmpUserDetailUrl = $arParams['DETAIL_URL'];

	if ($bNeedGetUser)
	{
		$obCache = new CPHPCache;
		$strCacheID = $arParams["ID"]."_".$arParams["USE_THUMBNAIL_LIST"]."_".$arParams["THUMBNAIL_LIST_SIZE"]."_".$GLOBALS["USER"]->GetID()."_".$bSocialNetwork;

		$path = "/user_card_".intval($arParams["ID"] / 100);

		if($arParams['AJAX_CALL'] == 'INFO' || $obCache->StartDataCache($arParams["CACHE_TIME"], $strCacheID, $path))
		{
			if ($arParams['AJAX_CALL'] != 'INFO' && defined("BX_COMP_MANAGED_CACHE"))
			{
				$GLOBALS["CACHE_MANAGER"]->StartTagCache($path);
				$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_CARD_".intval($arParams["ID"] / 100));
			}

			$dbUser = CUser::GetByID($arParams["ID"]);
			$arResult["User"] = $dbUser->Fetch();

			if (!$arResult["User"])
				$arResult["FatalError"] = GetMessage("MAIN_UL_NO_ID").". ";

			if (strlen($arResult["FatalError"]) <= 0 && $arParams["USE_THUMBNAIL_LIST"] == "Y" && $arParams['AJAX_CALL'] != 'INFO')
			{
				$iSize = $arParams["THUMBNAIL_LIST_SIZE"];
				$imageFile = false;
				$imageImg = false;

				$bThumbnailFound = false;

				if (intval($arResult["User"]["PERSONAL_PHOTO"]) <= 0 && $bSocialNetwork)
				{
					switch ($arResult["User"]["PERSONAL_GENDER"])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
							break;
						default:
							$suffix = "unknown";
					}
					$arResult["User"]["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
				}

				if (intval($arResult["User"]["PERSONAL_PHOTO"]) > 0)
				{
					$imageFile = CFile::GetFileArray($arResult["User"]["PERSONAL_PHOTO"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $iSize, "height" => $iSize),
							BX_RESIZE_IMAGE_PROPORTIONAL,
							false
						);
						$imageImg = CFile::ShowImage($arFileTmp["src"], $iSize, $iSize, "border='0'", "");
						$bThumbnailFound = true;
					}
				}

				if (!$bThumbnailFound)
					$imageImg = "<img src='/bitrix/components/bitrix/main.user.link/templates/.default/images/nopic_30x30.gif' width='30' height='30' border='0'>";

				if ($bSocialNetwork && CModule::IncludeModule('socialnetwork') && $arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && strlen($arParams["HREF"]) > 0)
					$arResult["User"]["PersonalPhotoImgThumbnail"] = '<a href="'.$arParams["HREF"].'">'.$imageImg.'</a>';
				elseif ($bSocialNetwork && CModule::IncludeModule('socialnetwork') && $arResult["CurrentUserPerms"]["Operations"]["viewprofile"] && strlen($arResult["User"]["DETAIL_URL"]) > 0)
					$arResult["User"]["PersonalPhotoImgThumbnail"] = '<a href="'.$arResult["User"]["DETAIL_URL"].'">'.$imageImg.'</a>';
				else
					$arResult["User"]["PersonalPhotoImgThumbnail"] = $imageImg;

			}
			$arResult["User"]["DETAIL_URL"] = $tmpUserDetailUrl;

			if (CModule::IncludeModule('intranet'))
			{
				$arResult["User"]['MANAGERS'] = CIntranetUtils::GetDepartmentManager($arResult["User"]["UF_DEPARTMENT"], $arResult["User"]["ID"], true);
				foreach($arResult["User"]['MANAGERS'] as $key=>$manager)
				{
					$arResult["User"]['MANAGERS'][$key]["NAME_FORMATTED"] = $manager['LAST_NAME'].' '.$manager['NAME']; //CUser::FormatName($arParams['NAME_TEMPLATE'], $manager, $bUseLogin);
					$arResult["User"]['MANAGERS'][$key]["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SONET_USER_PROFILE"], array("user_id" => $manager["ID"], "USER_ID" => $manager["ID"], "ID" => $manager["ID"]));
				}
			}

			if ($arParams['AJAX_CALL'] != 'INFO')
			{
				$obCache->EndDataCache($arResult);
				if(defined("BX_COMP_MANAGED_CACHE"))
					$GLOBALS["CACHE_MANAGER"]->EndTagCache();
			}
		}
		else
			$arResult = $obCache->GetVars();

		$arResult["ajax_page"] = $APPLICATION->GetCurPageParam("", array("bxajaxid", "logout"));

		if ($bSocialNetwork && CModule::IncludeModule('socialnetwork'))
			$arResult["Urls"]["SonetMessageChat"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_SONET_MESSAGES_CHAT"], array("user_id" => $arParams["ID"], "USER_ID" => $arParams["ID"], "ID" => $arParams["ID"]));

		if(CModule::IncludeModule("video"))
			$arResult["Urls"]["VideoCall"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_VIDEO_CALL"], array("user_id" => $arParams["ID"], "USER_ID" => $arParams["ID"], "ID" => $arParams["ID"]));

		if (strlen($arResult["FatalError"]) <= 0 && $arParams['AJAX_CALL'] == 'INFO')
		{
			$arResult["User"]["PERSONAL_LOCATION"] = GetCountryByID($arResult["User"]["PERSONAL_COUNTRY"]);
			if (strlen($arResult["User"]["PERSONAL_LOCATION"])>0 && strlen($arResult["User"]["PERSONAL_CITY"])>0)
				$arResult["User"]["PERSONAL_LOCATION"] .= ", ";
			$arResult["User"]["PERSONAL_LOCATION"] .= $arResult["User"]["PERSONAL_CITY"];

			$arResult["User"]["WORK_LOCATION"] = GetCountryByID($arResult["User"]["WORK_COUNTRY"]);
			if (strlen($arResult["User"]["WORK_LOCATION"])>0 && strlen($arResult["User"]["WORK_CITY"])>0)
				$arResult["User"]["WORK_LOCATION"] .= ", ";
			$arResult["User"]["WORK_LOCATION"] .= $arResult["User"]["WORK_CITY"];

			$arResult["Sex"] = array(
				"M" => GetMessage("MAIN_UL_SEX_M"),
				"F" => GetMessage("MAIN_UL_SEX_F"),
			);

			if (strlen($arResult["User"]["PERSONAL_WWW"]) > 0)
				$arResult["User"]["PERSONAL_WWW"] = ((strpos($arResult["User"]["PERSONAL_WWW"], "http") === false) ? "http://" : "").$arResult["User"]["PERSONAL_WWW"];

			$arMonths_r = array();
			for ($i = 1; $i <= 12; $i++)
				$arMonths_r[$i] = ToLower(GetMessage('MONTH_'.$i.'_S'));

			$arTmpUser = array(
				"ID" => $arResult["User"]["ID"],
				"NAME" => $arResult["User"]["NAME"],
				"LAST_NAME" => $arResult["User"]["LAST_NAME"],
				"SECOND_NAME" => $arResult["User"]["SECOND_NAME"],
				"LOGIN" => $arResult["User"]["LOGIN"],
				"DETAIL_URL" => $arResult["User"]["DETAIL_URL"],
			);

			if($this->InitComponentTemplate())
			{
				$template = &$this->GetTemplate();
				$arResult["FOLDER_PATH"] = $folderPath = $template->GetFolder();
				include($_SERVER["DOCUMENT_ROOT"].$folderPath."/card.php");
			}

			if (CModule::IncludeModule('intranet'))
			{
				$arResult['IS_HONOURED'] = CIntranetUtils::IsUserHonoured($arResult["User"]["ID"]);
				$arResult['IS_ABSENT'] = CIntranetUtils::IsUserAbsent($arResult["User"]["ID"]);
			}

			if ($arResult["User"]['PERSONAL_BIRTHDAY'] <> '')
			{
				$arBirthDate = ParseDateTime($arResult["User"]['PERSONAL_BIRTHDAY'], CSite::GetDateFormat('SHORT'));
				$arResult['IS_BIRTHDAY'] = (intval($arBirthDate['MM']) == date('n') && intval($arBirthDate['DD']) == date('j'));
			}

			$strToolbar = "";
			$strToolbar2 = "";
			$intToolbarItems = 0;

			if (
				$GLOBALS["USER"]->IsAuthorized()
				&& $arResult["User"]["ID"] != $GLOBALS["USER"]->GetID()
				&& $arResult["CurrentUserPerms"]["Operations"]["message"]
			)
			{
				$strOnclick = "if (typeof(BX) != 'undefined' && BX.IM) { BXIM.openMessenger(".$arResult["User"]["ID"]."); return false; } else { window.open('".$arResult["Urls"]["SonetMessageChat"]."', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false; }";			
				$strToolbar2 .= '<li class="bx-icon bx-icon-message"><a href="'.$arResult["Urls"]["SonetMessageChat"].'" onclick="'.$strOnclick.'">'.GetMessage("MAIN_UL_TOOLBAR_MESSAGES_CHAT").'</a></li>';
			}

			if (
				$GLOBALS["USER"]->IsAuthorized()
				&& $arResult["User"]["ID"] != $GLOBALS["USER"]->GetID()
				&& $arResult["CurrentUserPerms"]["Operations"]["videocall"]
				&& strlen($arResult["Urls"]["VideoCall"]) > 0
			)
			{
				$strOnclick = "window.open('".$arResult["Urls"]["VideoCall"]."', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=1000,height=600,top='+Math.floor((screen.height - 600)/2-14)+',left='+Math.floor((screen.width - 1000)/2-5)); return false;";
				$strToolbar2 .= '<li class="bx-icon bx-icon-video"><a href="'.$arResult["Urls"]["VideoCall"].'" onclick="'.$strOnclick.'">'.GetMessage("MAIN_UL_TOOLBAR_VIDEO_CALL").'</a></li>';
			}

			if ($arResult['IS_BIRTHDAY'])
			{
				$strToolbar .= '<li class="bx-icon bx-icon-birth">'.GetMessage("MAIN_UL_TOOLBAR_BIRTHDAY").'</li>';
				$intToolbarItems++;
			}

			if ($arResult['IS_HONOURED'])
			{
				$strToolbar .= '<li class="bx-icon bx-icon-featured">'.GetMessage("MAIN_UL_TOOLBAR_HONORED").'</li>';
				$intToolbarItems++;
			}

			if ($arResult['IS_ABSENT'])
			{
				$strToolbar .= '<li class="bx-icon bx-icon-away">'.GetMessage("MAIN_UL_TOOLBAR_ABSENT").'</li>';
				$intToolbarItems++;
			}

			if (strlen($strToolbar) > 0)
				$strToolbar = "<ul>".$strToolbar."</ul>";

			if (strlen($strToolbar2) > 0)
				$strToolbar2 = "<div class='bx-user-info-data-separator'></div><ul>".$strToolbar2."</ul>";

			$arResult = array(
				"Toolbar" => $strToolbar,
				"ToolbarItems" => $intToolbarItems,
				"Toolbar2" => $strToolbar2,
				"Card" => $strCard,
				"Photo" => $strPhoto,
			);

			$APPLICATION->RestartBuffer();
			while (@ob_end_clean());

			Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

			echo CUtil::PhpToJsObject(array('RESULT' => $arResult));
			die();

		}

	}
	else
	{
		$arResult["User"]["ID"] = $arParams["ID"];
		$arResult["User"]["NAME"] = $arParams["~NAME"];
		$arResult["User"]["LAST_NAME"] = $arParams["~LAST_NAME"];
		$arResult["User"]["SECOND_NAME"] = $arParams["~SECOND_NAME"];
		$arResult["User"]["LOGIN"] = $arParams["LOGIN"];
		if ($arParams["USE_THUMBNAIL_LIST"] == "Y" && strlen($arParams["HREF"]) <= 0)
			$arResult["User"]["PersonalPhotoImgThumbnail"] = $arParams["~PERSONAL_PHOTO_IMG"];
		elseif ($arParams["USE_THUMBNAIL_LIST"] == "Y" && intval($arParams["PERSONAL_PHOTO_FILE"]["ID"]) > 0)
		{
			$arImage = CSocNetTools::InitImage($arParams["PERSONAL_PHOTO_FILE"]["ID"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/1.gif", 1, $arParams["~HREF"], $canViewProfile);
			$arResult["User"]["PersonalPhotoImgThumbnail"] = '<a href="'.$arParams["~HREF"].'">'.$arImage["IMG"].'</a>';
		}
	}

	if (array_key_exists("NAME_LIST_FORMATTED", $arParams) && strlen(trim($arParams['NAME_LIST_FORMATTED'])) > 0)
		$arResult["User"]["NAME_FORMATTED"] = trim($arParams['NAME_LIST_FORMATTED']);
	else
		$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arResult["User"], $bUseLogin);

	if ($bSocialNetwork)
		$arResult["User"]["HTML_ID"] = $arParams["HTML_ID"];

	if (strlen($arParams["HREF"]) > 0)
		$arResult["User"]["HREF"] = $arParams["~HREF"];

	$arResult["bSocialNetwork"] = $bSocialNetwork;

	if (strlen($arParams["DESCRIPTION"]) > 0)
		$arResult["User"]["NAME_DESCRIPTION"] = $arParams["~DESCRIPTION"];

}
elseif($arParams['AJAX_CALL'] == 'INFO') // fatal error for ajax page
{
	$APPLICATION->RestartBuffer();
	while (@ob_end_clean());

	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

	echo CUtil::PhpToJsObject(array('RESULT' => $arResult));
	die();
}

if ($arParams["AJAX_ONLY"] != "Y")
{
	ob_start();
	$this->IncludeComponentTemplate();
	$sReturn = ob_get_contents();

	if ($arParams["DO_RETURN"] == "Y")
		ob_end_clean();
	else
		ob_end_flush();

	return $sReturn;
}
?>