<?

IncludeModuleLangFile(__FILE__);

class CHotKeysCode
{
    private $arList;
    protected $hkCacheTtl = 3600;

	public function GetByID($ID)
	{
		return $this->GetList(array(),array("ID"=>$ID));
	}

	public function GetCodeByClassName($className)
	{
		$this->LoadToCache();

		$arRet = false;

		if(!is_array($this->arList))
			return false;

		foreach ($this->arList as $arCode)
			if($arCode["CLASS_NAME"]==$className)
					$arRet[]=$arCode;

		return $arRet;
	}

	protected function CleanUrl($url)
	{
		//removes host & proto from url
		if($hostPos=strpos($url, $_SERVER["HTTP_HOST"]))
			$cleanUrl=substr($url,$hostPos+strlen($_SERVER["HTTP_HOST"]));
		else
			$cleanUrl = $url;

		//removes all after "?"
		if($qMarkPos = strpos($cleanUrl,"?"))
			$cleanUrl=substr($cleanUrl,0,$qMarkPos);

		return $cleanUrl;
	}

	public function GetByUrl($url)
	{
		$this->LoadToCache();

		if(!is_array($this->arList))
			return false;

		$arRet = false;

		foreach ($this->arList as $arCode)
		{
			$codeUrl = $this->CleanUrl($arCode["URL"]);

			if(($codeUrl==substr($this->CleanUrl($url),0,strlen($codeUrl)) && $codeUrl) || (!$arCode["CLASS_NAME"] && (!$arCode["URL"] || $arCode["URL"]=="*")))
				$arRet[]=$arCode;
		}

		return $arRet;
	}

	public function GetIDByTitleObj($strTitleObj)
	{
		if($strTitleObj)
		{
			$this->LoadToCache();

			if(!is_array($this->arList))
				return false;

			foreach ($this->arList as $arCode)
				if($arCode["TITLE_OBJ"]==$strTitleObj)
					return $arCode["ID"];
		}

		return false;
	}

	protected function CheckFields($arFields)
	{
		$aMsg = array();

		if(isset($arFields["CODE"]))
		{
			if(!$arFields["CODE"])
				$aMsg[] = array("id"=>"CODE", "text"=>GetMessage("HK_CF_CODE"));

			if(stripos($arFields["CODE"], "</script>")!==false || stripos($arFields["CODE"], "script")!==false)
				$aMsg[] = array("id"=>"CODE", "text"=>GetMessage("HK_CF_SCRIPT"));
		}

		if(isset($arFields["NAME"]))
			if(!$arFields["NAME"])
				$aMsg[] = array("id"=>"NAME", "text"=>GetMessage("HK_CF_NAME"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	protected function LoadToCache()
	{
		global $CACHE_MANAGER;

		if(!is_array($this->arList))
		{
			if($CACHE_MANAGER->Read($this->hkCacheTtl, "b_hot_keys_code".LANGUAGE_ID))
				$this->arList = $CACHE_MANAGER->Get("b_hot_keys_code".LANGUAGE_ID);
			else
			{
				$res = $this->GetList();
				while($arTemp=$res->Fetch())
				{
					if(!$arTemp["IS_CUSTOM"])
					{
						if(isset($arTemp["NAME"]) && $arTemp["NAME"]!="-=AUTONAME=-")
							$arTemp["NAME"]=GetMessage($arTemp["NAME"]);

						if(isset($arTemp["COMMENTS"]))
							$arTemp["COMMENTS"]=GetMessage($arTemp["COMMENTS"]);
					}

					$this->arList[$arTemp["ID"]]=$arTemp;
				}
				$CACHE_MANAGER->Set("b_hot_keys_code".LANGUAGE_ID, $this->arList);
			}
		}
	}

	protected function CleanCache()
	{
		global $CACHE_MANAGER;
		$arLangs = CLanguage::GetLangSwitcherArray();

		foreach($arLangs as $adminLang)
			$CACHE_MANAGER->Clean("b_hot_keys_code".$adminLang["LID"]);
	}

	protected function ErrOrig()
	{
		return "<br>Class: CHotKeysCode File: ".__FILE__."<br>";
	}

	public function Delete($ID)
	{
		global $DB;

		$this->CleanCache();

		$strSql = "SELECT ID FROM b_hot_keys WHERE CODE_ID=".intval($ID);
		$res=$DB->Query($strSql,false, $this->ErrOrig()." Line: ".__LINE__);

		while($arHK=$res->Fetch())
			CHotKeys::GetInstance()->Delete($arHK["ID"]);

		$sql = "DELETE FROM b_hot_keys_code WHERE ID=".intval($ID);

		return $DB->Query($sql, false, $this->ErrOrig()." Line: ".__LINE__);
	}

	public function Update($ID,$arFields)
	{
		if(!$this->CheckFields($arFields))
			return false;

		global $DB;

		$this->CleanCache();

		$strUpdate = $DB->PrepareUpdate("b_hot_keys_code", $arFields);

		if($strUpdate!="")
		{
			$strSql = "UPDATE b_hot_keys_code SET ".$strUpdate." WHERE ID=".intval($ID)." AND IS_CUSTOM <> 0";
			if(!$DB->Query($strSql))
				return false;
		}
		return true;
	}

	public function Add($arFields)
	{
		if(!$this->CheckFields($arFields))
			return false;

		global $DB;

		$this->CleanCache();

		return $DB->Add("b_hot_keys_code",$arFields);
	}

	public function GetList($aSort=array(), $arFilter=Array(), $showEmptyName=true)
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0; $i<count($filter_keys); $i++)
			{
				$val = $arFilter[$filter_keys[$i]];
				if (strlen($val)<=0 || $val=="NOT_REF") continue;
				switch(strtoupper($filter_keys[$i]))
				{
				case "ID":
					$arSqlSearch[] = "C.ID=".intval($val);
					break;
				case "CLASS_NAME":
					$arSqlSearch[] = GetFilterQuery("C.CLASS_NAME", $val);
					break;
				case "CODE":
					$arSqlSearch[] = GetFilterQuery("C.CODE", $val);
					break;
				case "NAME":
					$arSqlSearch[] = GetFilterQuery("C.NAME", $val);
					break;
				case "COMMENTS":
					$arSqlSearch[] = GetFilterQuery("C.COMMENTS", $val);
					break;
				case "TITLE_OBJ":
					$arSqlSearch[] = GetFilterQuery("C.TITLE_OBJ", $val);
					break;
				case "URL":
					$arSqlSearch[] = GetFilterQuery("C.URL", $val);
					break;
				case "IS_CUSTOM":
					$arSqlSearch[] = GetFilterQuery("C.IS_CUSTOM", $val);
					break;
				}
			}
		}

		if(!$showEmptyName)
			$arSqlSearch[] = "C.NAME IS NOT NULL AND C.NAME<>'-=AUTONAME=-'";

		$sOrder = "";
		foreach($aSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch (strtoupper($key))
			{
				case "ID":	$sOrder .= ", C.ID ".$ord; break;
				case "CLASS_NAME": $sOrder .= ", C.CLASS_NAME ".$ord; break;
				case "CODE": $sOrder .= ", C.CODE ".$ord; break;
				case "NAME": $sOrder .= ", C.NAME ".$ord; break;
				case "COMMENTS": $sOrder .= ", C.COMMENTS ".$ord; break;
				case "TITLE_OBJ": $sOrder .= ", C.TITLE_OBJ ".$ord; break;
				case "URL": $sOrder .= ", C.URL ".$ord; break;
				case "IS_CUSTOM": $sOrder .= ", C.IS_CUSTOM ".$ord; break;
			}
		}
		if (strlen($sOrder)<=0)
			$sOrder = "NAME ASC";

		$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				C.*
			FROM
				b_hot_keys_code C
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;

		$res = $DB->Query($strSql, false, $this->ErrOrig()." Line: ".__LINE__);
		return $res;
	}
}

class CHotKeys
{
	protected static $codes;
	protected static $instance;
	protected static $optUse; //Global settings option
    protected $arList; //For Cache //private
	protected $arServSymb = array(
							8=>"Back Space",	9=>"Tab",	13=>"Enter",	16=>"Shift",	17=>"Ctrl",	18=>"Alt",	19=>"Pause",
							20=>"Caps Lock",	27=>"ESC",	32=>"Space bar",	33=>"Page Up",	34=>"Page Down",	35=>"End",
							36=>"Home",	37=>"Left",	38=>"Up",	39=>"Right",	40=>"Down",	45=>"Insert",	46=>"Delete",	96=>"0 (ext)",
							97=>"1 (ext)",	98=>"2 (ext)",	99=>"3 (ext)",	100=>"4 (ext)",	101=>"5 (ext)",	102=>"6 (ext)",	105=>"9 (ext)",
							106=>"* (ext)",	107=>"+ (ext)",	104=>"8 (ext)",	103=>"7 (ext)",	110=>". (ext)",	111=>"/ (ext)",	112=>"F1",
							113=>"F2",	114=>"F3",	115=>"F4",	116=>"F5",	117=>"F6",	118=>"F7",	119=>"F8",	120=>"F9",	121=>"F10",
							122=>"F11",	123=>"F12",	144=>"Num Lock",	186=>";",	188=>",",	190=>".",	191=>"/",	192=>"`",	219=>"[",
							220=>"|",	221=>"]",	222=>"'",	189=>"-",	187=>"+",145=>"Scrol Lock",
							);

    private function __construct() { }
    private function __clone()    { }
    private function __wakeup()   { }

	public static function GetInstance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
			self::$codes = new CHotKeysCode;
			self::$optUse = COption::GetOptionString('main', "use_hot_keys", "Y") == "Y";
		}

		return self::$instance;
	}

	protected function ErrOrig()
	{
		return "<br>Class: CHotKeys File: ".__FILE__."<br>";
	}

	protected function LoadToCache()
	{
		global $USER,$_SESSION;

		if(is_array($this->arList) || !self::$optUse)
			return false;

		if(isset($_SESSION["hasHotKeys"]) && $_SESSION["hasHotKeys"]==false)
			return false;

		$uid = $USER->GetID();
		$res = $this->GetList(array(),array("USER_ID"=>$uid));

		$this->CheckStickers();

		while($arTemp=$res->Fetch())
			$this->arList[$arTemp["ID"]]=$arTemp;

		if(is_array($this->arList))
			$_SESSION["hasHotKeys"]=true;
		else
		{
			if(!$this->IsDefaultOpt())
			{
				$_SESSION["hasHotKeys"]=false;
				return false;
			}

			$setDef=$this->SetDefault($uid);
			$setNoDef=$this->SetNotDefaultOpt();

			if(!$setDef || !$setNoDef)
			{
				$_SESSION["hasHotKeys"]=false;
				return false;
			}

			return $this->LoadToCache();
		}

		return true;
	}

	protected function GetByCodeID($codeID)
	{
		if(!$codeID)
			return false;

		$this->LoadToCache();

		if(!is_array($this->arList))
			return false;

		foreach ($this->arList as $arHK)
			if($arHK["CODE_ID"]==$codeID)
				return $arHK;

		return false;
	}

	public function GetUIDbyHID($hkID) //user id by hot-key id
	{
		$this->LoadToCache();

		if(!is_array($this->arList))
			return false;

		foreach ($this->arList as $arHK)
			if($arHK["ID"]==$hkID)
				return $arHK["USER_ID"];

		return false;
	}

	protected function GlueSelfToCode(&$arCode)
	{
		$found=false;

		if(!is_array($arCode)) 	//|| !is_array($this->arList)
			return false;

		if (is_array($this->arList))
			foreach ($this->arList as $arHK)
				if($arCode["ID"]==$arHK["CODE_ID"])
				{
					$arCode["KEYS_STRING"]=$arHK["KEYS_STRING"];
					$arCode["CODE_ID"]=$arHK["CODE_ID"];
					$arCode["HK_ID"]=$arHK["ID"];
					$found = true;
					break;
				}

		if(!$found)
		{
			$arCode["KEYS_STRING"]="";
			$arCode["CODE_ID"]=$arCode["ID"];
			$arCode["HK_ID"]=0;
		}

		return $found;
	}

	public function GetCodeByClassName($className, $name="",$code="")
	{
		if(!self::$optUse)
			return false;

		$this->LoadToCache();

		$arCodes = self::$codes->GetCodeByClassName($className);

		if(!is_array($arCodes))
			return false;

		foreach ($arCodes as &$arCode)
		{
			$this->GlueSelfToCode($arCode);

			if($code)									//TODO: work only for single code in each class
				$arCode["CODE"] = $code;

			if($name)
				$arCode["NAME"] = $name;
		}

		return $arCodes;
	}

	public function GetCodeByUrl($url)
	{
		if(!self::$optUse)
			return false;

		$this->LoadToCache();
		$arCodes = self::$codes->GetByUrl($url);

		if(!is_array($arCodes))
			return false;

		foreach ($arCodes as &$arCode)
			$this->GlueSelfToCode($arCode);

		return $arCodes;
	}

	public function GetTitle($strTitleObj, $forHint=false)
	{
		if(!self::$optUse)
			return false;

		$this->LoadToCache();
		$codeID = self::$codes->GetIDByTitleObj($strTitleObj);
		$arHK = $this->GetByCodeID($codeID);
		$space=($forHint ? "&nbsp;" : " ");

		if(is_array($arHK))
			return " (".$space.htmlspecialchars($this->ShowHKAsChar($arHK["KEYS_STRING"]),ENT_QUOTES).$space.") ";

		return "";
	}

	//if obj has unique id = b_hot-keys_code.TITLE_OBJ
	public function SetTitle($className, $forHint=false)
	{
		$this->LoadToCache();
		$arCodes = self::$codes->GetCodeByClassName($className);

		if(!is_array($arCodes))
			return false;

		$retHtml="";

		foreach ($arCodes as $arCode)
		{
			if(!$arCode["TITLE_OBJ"])
				continue;

			$arHK = $this->GetByCodeID($arCode["ID"]);
			$space=($forHint ? "&nbsp;" : " ");

			if(!is_array($arHK))
				continue;

			$retHtml .= "<script type='text/javascript'> var d = BX('".$arCode["TITLE_OBJ"]."'); if (!d) d=BX.findChild(document, {attribute: {'name': '".$arCode["TITLE_OBJ"]."'}}, true ); if(d) d.title+=' (".$space.Cutil::JSEscape($this->ShowHKAsChar($arHK["KEYS_STRING"])).$space.") ';</script>";
		}

		return $retHtml;
	}

	public function PrintJSExecs($execs, $controlName="", $scriptTags=true, $checkHK=false)
	{
		$retStr = "";

		if(!is_array($execs))
			return false;

		$printName= ($controlName) ? $controlName."." : "";

		foreach ($execs as $arExec)
		{
			if($arExec["CODE"])
				$code = $printName.addslashes($arExec["CODE"]);
			else
				$code ="";

			$retStr.=' BXHotKeys.Add("'.htmlspecialchars($arExec["KEYS_STRING"]).'","'.$code.'",'.intval($arExec["CODE_ID"]).", '".strip_tags(addslashes($arExec["NAME"]),"<b>")."', ".intval($arExec["HK_ID"])."); ";
		}

		if($checkHK==true)
			$retStr = ' if(window.BXHotKeys!==undefined)  { '.$retStr.' } ';

		if($scriptTags==true)
			$retStr = '<script type="text/javascript">'.$retStr.'</script>';

		return $retStr;
	}

	//On Error Throws exception+message and return false
	protected function CheckFields($arFields)
	{
		$aMsg = array();

		if(isset($arFields["CODE_ID"]))
			if(!$arFields["CODE_ID"])
				$aMsg[] = array("id"=>"CODE_ID", "text"=>GetMessage("HK_CF_CODE_ID"));

		if(isset($arFields["KEYS_STRING"]))
			if(!$arFields["KEYS_STRING"] || !$this->CheckKeysString($arFields["KEYS_STRING"]))
				$aMsg[] = array("id"=>"KEYS_STRING", "text"=>GetMessage("HK_CF_KEYS_STRING"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}

	public function GetList($aSort=array(), $arFilter=Array())
	{
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0; $i<count($filter_keys); $i++)
			{
				$val = $arFilter[$filter_keys[$i]];
				if (strlen($val)<=0 || $val=="NOT_REF") continue;
				switch(strtoupper($filter_keys[$i]))
				{
				case "ID":
					$arSqlSearch[] = "ID=".intval($val);
					break;
				case "KEYS_STRING":
					$arSqlSearch[] = GetFilterQuery("KEYS_STRING", $val);
					break;
				case "CODE_ID":
					$arSqlSearch[] = "CODE_ID=".intval($val);
					break;
				case "USER_ID":
					$arSqlSearch[] = "USER_ID=".intval($val);
					break;
				}
			}
		}

		$sOrder = "";
		foreach($aSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> "ASC"? "DESC":"ASC");
			switch (strtoupper($key))
			{
				case "ID":	$sOrder .= ", ID ".$ord; break;
				case "KEYS_STRING":	$sOrder .= ", KEYS_STRING ".$ord; break;
				case "CODE_ID":	$sOrder .= ", CODE_ID ".$ord; break;
				case "USER_ID":	$sOrder .= ", USER_ID ".$ord; break;
			}
		}

		if (strlen($sOrder)>0)
			$strSqlOrder = " ORDER BY ".TrimEx($sOrder,",");
		else
			$strSqlOrder="";

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				*
			FROM
				b_hot_keys
			WHERE
			".$strSqlSearch."
			".$strSqlOrder;
		$res = $DB->Query($strSql, false, $this->ErrOrig()." Line: ".__LINE__);
		return $res;
	}

	public function Add($arFields)
	{

		if(!$this->CheckFields($arFields))
			return false;

		global $DB;

		unset($_SESSION["hasHotKeys"]);

		$arPrepFields = array(
			        	"KEYS_STRING"=>$arFields["KEYS_STRING"],
			        	"CODE_ID"=>intval($arFields["CODE_ID"]),
			        	"USER_ID"=>intval($arFields["USER_ID"]),
					);

		return $DB->Add("b_hot_keys",$arPrepFields);
	}

	public function Update($ID,$arFields)
	{
		if(!$this->CheckFields($arFields))
			return false;

		global $DB;

		unset($_SESSION["hasHotKeys"]);

		$strUpdate = $DB->PrepareUpdate("b_hot_keys", $arFields);

		if($strUpdate!="")
		{
			$strSql = "UPDATE b_hot_keys SET ".$strUpdate." WHERE ID=".intval($ID);
			if(!$DB->Query($strSql))
				return false;
		}
		return true;
	}

	public function Delete($ID)
	{
		global $DB;

		unset($_SESSION["hasHotKeys"]);

		$sql = "DELETE FROM b_hot_keys WHERE ID=".intval($ID);
		$res = $DB->Query($sql, false, $this->ErrOrig()." Line: ".__LINE__);

		return $res->AffectedRowsCount();
	}

	//sets (copy) keys_strings from user with id=0 to userID
	public function SetDefault($userID)
	{
		global $DB;

		$uid= intval($userID);

		unset($_SESSION["hasHotKeys"]);

		$sql = "DELETE FROM b_hot_keys WHERE USER_ID=".$uid;
		$delRes = $DB->Query($sql, false, $this->ErrOrig()." Line: ".__LINE__);

		$listRes=$this->GetList(array(),array("USER_ID"=>"0"));

		$insErr=false;
		while($arHK=$listRes->Fetch())
		{
			$arPrepFields = array(
	        	"KEYS_STRING"=>$arHK["KEYS_STRING"],
	        	"CODE_ID"=>$arHK["CODE_ID"],
	        	"USER_ID"=>$uid
			);

			$insRes = $DB->Add("b_hot_keys",$arPrepFields);

			if(!$insRes)
				$insErr=true;
		}

		return ($delRes && !$insErr);
	}

	public function ShowHKAsChar($hotKeysString)
	{
		if(!$this->CheckKeysString($hotKeysString))
			return GetMessage("HK_WRONG_KS");

		$lastPlusPos=strrpos($hotKeysString,"+");

		if($lastPlusPos)
		{
			$charCode = substr($hotKeysString, $lastPlusPos+1, strlen($hotKeysString));
			$preChar = substr($hotKeysString, 0, $lastPlusPos+1);
			if($charCode==16 || $charCode==17 || $charCode==18)
				return substr($preChar,0,strlen($preChar)-1);
		}
		else
		{
			$charCode=$hotKeysString;
			$preChar="";
		}

		if(intval($charCode)<256)
		{
			if(!($codeSymb=$this->arServSymb[intval($charCode)]))
		 		$codeSymb=Chr($charCode);
		}
		else
			$codeSymb = html_entity_decode("&#".$charCode.";", ENT_NOQUOTES,LANG_CHARSET);

		return $preChar.$codeSymb;
	}

	protected function CheckKeysString($keysString)
	{
		$keyCode=str_replace(array("ctrl","alt","shift","+"), "", strtolower($keysString));
		return !(strcmp(intval($keyCode),$keyCode));
	}

	public function PrintPhpToJSVars()
	{
		if(!self::$optUse)
			return false;

		global $USER;
		$htmlOut= 	"<script type='text/javascript'>
					BXHotKeys.MesNotAssign = '".GetMessage("HK_NOT_ASSIGN")."';
					BXHotKeys.MesClToChange = '".GetMessage("HK_CLICK_TO_CHANGE")."';
					BXHotKeys.MesClean = '".GetMessage("HK_CLEAN")."';
					BXHotKeys.MesBusy = '".GetMessage("HK_BUSY")."';
					BXHotKeys.MesClose = '".GetMessage("HK_CLOSE")."';
					BXHotKeys.MesSave = '".GetMessage("HK_SAVE")."';
					BXHotKeys.MesSettings = '".GetMessage("HK_SETTINGS")."';
					BXHotKeys.MesDefault = '".GetMessage("HK_DEFAULT")."';
					BXHotKeys.MesDelAll = '".GetMessage("HK_DELALL")."';
					BXHotKeys.uid = ".$USER->GetID().";
					</script>";
		return $htmlOut;
	}

	public function IsActive()
	{
		return self::$optUse;
	}

	// Top panel buttons
	public function PrintTPButton(&$arPanelButton,$parent="")
	{
		if(!self::$optUse)
			return false;

		$retJS="";
		$hkCode="";

		if(isset($arPanelButton["LINK"]))
			$hkCode="location.href='".$arPanelButton["LINK"]."'";

		if(isset($arPanelButton["ACTION"]))
			$hkCode=$arPanelButton["ACTION"];

		if(isset($arPanelButton["ALT"]))
			$arPanelButton["ALT"] = $arPanelButton["ALT"].$this->GetTitle($arPanelButton["HK_ID"]);

		if(isset($arPanelButton["TITLE"]))
			$arPanelButton["TITLE"] = $arPanelButton["TITLE"].$this->GetTitle($arPanelButton["HK_ID"]);

		if(isset($arPanelButton["HK_ID"]))
		{
			$name = str_replace(array("#BR#","&nbsp;"),array(" ",""), $arPanelButton["TEXT"]);

			if($parent!="")
				$name=$parent.$name;
			else
				if($arPanelButton["MENU"])
					$name="<b>".$name."</b>";

			$Execs=$this->GetCodeByClassName($arPanelButton["HK_ID"], $name, $hkCode);
			$retJS.=$this->PrintJSExecs($Execs);
		}

		if(is_array($arPanelButton["MENU"]) && !empty($arPanelButton["MENU"]))
			foreach ($arPanelButton["MENU"] as &$menu)
				$retJS.=$this->PrintTPButton($menu,$parent."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");

		return $retJS;
	}

	protected function IsDefaultOpt()
	{
		global $DB, $USER, $__USER_OPTIONS_CACHE;

		$userId = $USER->GetID();
		$cacheKey = "hot_keys.user_defined";

		if(!isset($__USER_OPTIONS_CACHE[$userId][$cacheKey]))
		{
			$strSql =
				"SELECT CATEGORY, NAME, VALUE, COMMON FROM b_user_option ".
				"WHERE (USER_ID=".$userId." OR USER_ID IS NULL AND COMMON='Y') ";

			$res = $DB->Query($strSql,false, $this->ErrOrig()." Line: ".__LINE__);

			while($res_array = $res->Fetch())
			{
				$row_cache_key = $res_array["CATEGORY"].".".$res_array["NAME"];
				if(!isset($__USER_OPTIONS_CACHE[$userId][$row_cache_key]) || $res_array["COMMON"] <> 'Y')
					$__USER_OPTIONS_CACHE[$userId][$row_cache_key] = unserialize($res_array["VALUE"]);
			}
		}

		if(!isset($__USER_OPTIONS_CACHE[$userId][$cacheKey]))
			return true;
		else
			return false;
	}

	protected function SetNotDefaultOpt()
	{
		global $DB, $USER, $__USER_OPTIONS_CACHE;

		$userId = $USER->GetID();

		$arFields = array(
			"USER_ID"=>$userId,
			"CATEGORY"=>"hot_keys",
			"NAME"=>"user_defined",
			"VALUE"=>serialize(true),
			"COMMON"=>("N"),
		);

		if(!$DB->Add("b_user_option", $arFields, array("VALUE")))
				return false;

		$__USER_OPTIONS_CACHE = array();

		return true;
	}

	//for old stickers hotkeys compability
	protected function CheckStickers()
	{
		global $DB;

		if(COption::GetOptionString("fileman", "stickers_use_hotkeys","Y") != "Y")
			return false;

		$strSql = "SELECT ID FROM b_hot_keys WHERE USER_ID=0 AND ( CODE_ID=87 OR CODE_ID=88 OR CODE_ID=89)";
		$res=$DB->Query($strSql,false, $this->ErrOrig()." Line: ".__LINE__);

		if($res->SelectedRowsCount()<=0)
		{
			$this->Add (array("KEYS_STRING"=>"Ctrl+Shift+83", "CODE_ID"=>87, "USER_ID"=>0));
			$this->Add (array("KEYS_STRING"=>"Ctrl+Shift+88", "CODE_ID"=>88, "USER_ID"=>0));
			$this->Add (array("KEYS_STRING"=>"Ctrl+Shift+76", "CODE_ID"=>89, "USER_ID"=>0));
		}

		return COption::SetOptionString("fileman", "stickers_use_hotkeys", "N");
	}

	public function PrintGlobalUrlVar()
	{

		if(!$GLOBALS["APPLICATION"]->PanelShowed)
			return "";

		$Execs=$this->GetCodeByClassName("Global");
		$out=$this->PrintJSExecs($Execs);

		$Execs=$this->GetCodeByUrl($_SERVER["REQUEST_URI"]);
		$out.=$this->PrintJSExecs($Execs);

		$out.=$this->PrintPhpToJSVars();

		return $out;
	}
}
?>
