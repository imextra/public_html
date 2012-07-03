<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$hkInstance = CHotKeys::GetInstance();
$uid=$USER->GetID();

if($USER->IsAuthorized() && check_bitrix_sessid())
{
	$res = false;

	switch ($_REQUEST["hkaction"])
	{
		case  'add':

				$arFields = array(
								"KEYS_STRING"=>rawurldecode($_REQUEST["KEYS_STRING"]),
								"CODE_ID"=>$_REQUEST["CODE_ID"],
								"USER_ID"=>$uid
								);

				$res = $hkInstance->Add($arFields);
				break;

		case  'update':

				if($hkInstance->GetUIDbyHID($_REQUEST["ID"])==$uid)
					$res = $hkInstance->Update($_REQUEST["ID"],array( "KEYS_STRING"=>rawurldecode($_REQUEST["KEYS_STRING"]) ));

				break;

		case  'delete':

				if($hkInstance->GetUIDbyHID($_REQUEST["ID"])==$uid)
					$res = $hkInstance->Delete($_REQUEST["ID"]);

				break;

		case  'delete_all':

				$res=0;
				$listRes=$hkInstance->GetList(array(),array( "USER_ID" => $uid ));
				while($arHK=$listRes->Fetch())
					$res += $hkInstance->Delete($arHK["ID"]);

				break;

		case  'set_default':

				$sdRes = $hkInstance->SetDefault($uid);
				if($sdRes)
				{
					$res="";
					$listRes=$hkInstance->GetList(array(),array( "USER_ID" => $uid ));
					while($arHK=$listRes->Fetch())
						$res.=$arHK["CODE_ID"]."::".$arHK["ID"]."::".$arHK["KEYS_STRING"].";;";
				}

				break;
	}

	echo $res;
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
?>
