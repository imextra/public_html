<?
define("START_EXEC_EPILOG_BEFORE_1", microtime());
$GLOBALS["BX_STATE"] = "EB";

if($USER->IsAuthorized())
{
	$hkInstance = CHotKeys::getInstance();

	$Execs=$hkInstance->GetCodeByClassName("Global");
	echo $hkInstance->PrintJSExecs($Execs);
	echo $hkInstance->SetTitle("Global");

	$Execs=$hkInstance->GetCodeByUrl($_SERVER["REQUEST_URI"]);

	echo $hkInstance->PrintJSExecs($Execs);
	echo $hkInstance->PrintPhpToJSVars();
}

if(method_exists($APPLICATION, "ShowSpreadCookieHTML")) //for BitrixUpdate to 4.0
	$APPLICATION->ShowSpreadCookieHTML();

if (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1)
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/epilog_main_admin.php");
else
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/epilog_jspopup_admin.php");

?>
