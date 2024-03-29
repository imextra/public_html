<?
IncludeModuleLangFile(__FILE__);

class CIBlockPropertyDateTime
{
	function AddFilterFields($arProperty, $strHTMLControlName, &$arFilter, &$filtered)
	{
		$filtered = false;
		$from_name = $strHTMLControlName["VALUE"].'_from';
		$from = isset($_REQUEST[$from_name])? $_REQUEST[$from_name]: "";
		if($from)
		{
			if(CheckDateTime($from))
			{
				$from = CIBlockPropertyDateTime::ConvertToDB($arProperty, array("VALUE"=>$from));
				$arFilter[">=PROPERTY_".$arProperty["ID"]] = $from["VALUE"];
				$filtered = true;
			}
			else
			{
				$arFilter[">=PROPERTY_".$arProperty["ID"]] = $from;
				$filtered = true;
			}
		}

		$to_name = $strHTMLControlName["VALUE"].'_to';
		$to = isset($_REQUEST[$to_name])? $_REQUEST[$to_name]: "";
		if($to)
		{
			if(CheckDateTime($to))
			{
				$to = CIBlockPropertyDateTime::ConvertToDB($arProperty, array("VALUE"=>$to));
				$arFilter["<=PROPERTY_".$arProperty["ID"]] = $to["VALUE"];
				$filtered = true;
			}
			else
			{
				$arFilter["<=PROPERTY_".$arProperty["ID"]] = $to;
				$filtered = true;
			}
		}
	}

	function GetAdminFilterHTML($arProperty, $strHTMLControlName)
	{
		$from_name = $strHTMLControlName["VALUE"].'_from';
		$to_name = $strHTMLControlName["VALUE"].'_to';
		$from = isset($_REQUEST[$from_name])? $_REQUEST[$from_name]: "";
		$to = isset($_REQUEST[$to_name])? $_REQUEST[$to_name]: "";

		return  CAdminCalendar::CalendarPeriod($from_name, $to_name, $from, $to);
	}

	function GetPublicFilterHTML($arProperty, $strHTMLControlName)
	{
		$from_name = $strHTMLControlName["VALUE"].'_from';
		$to_name = $strHTMLControlName["VALUE"].'_to';
		$from = isset($_REQUEST[$from_name])? $_REQUEST[$from_name]: "";
		$to = isset($_REQUEST[$to_name])? $_REQUEST[$to_name]: "";

		ob_start();

		$GLOBALS["APPLICATION"]->IncludeComponent(
			'bitrix:main.calendar',
			'',
			array(
				'FORM_NAME' => $strHTMLControlName["FORM_NAME"],
				'SHOW_INPUT' => 'Y',
				'INPUT_NAME' => $from_name,
				'INPUT_VALUE' => $from,
				'INPUT_NAME_FINISH' => $to_name,
				'INPUT_VALUE_FINISH' => $to,
				'INPUT_ADDITIONAL_ATTR' => 'size="10"',
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);

		$s = ob_get_contents();
		ob_end_clean();
		return  $s;
	}

	function GetPublicViewHTML($arProperty, $value, $strHTMLControlName)
	{
		if(strlen($value["VALUE"])>0)
		{
			if(!CheckDateTime($value["VALUE"]))
				$value = CIBlockPropertyDateTime::ConvertFromDB($arProperty, $value);

			if($strHTMLControlName["MODE"] == "CSV_EXPORT")
				return $value["VALUE"];
			else
				return str_replace(" ", "&nbsp;", htmlspecialcharsex($value["VALUE"]));
		}
		else
			return '';
	}

	function GetPublicEditHTML($arProperty, $value, $strHTMLControlName)
	{
		$s = '<input type="text" name="'.htmlspecialchars($strHTMLControlName["VALUE"]).'" size="25" value="'.htmlspecialchars($value["VALUE"]).'" />';
		ob_start();
		$GLOBALS["APPLICATION"]->IncludeComponent(
			'bitrix:main.calendar',
			'',
			array(
				'FORM_NAME' => $strHTMLControlName["FORM_NAME"],
				'INPUT_NAME' => $strHTMLControlName["VALUE"],
				'INPUT_VALUE' => $value["VALUE"],
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
		$s .= ob_get_contents();
		ob_end_clean();
		return  $s;
	}

	function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
	{
		if(strlen($value["VALUE"])>0)
		{
			if(!CheckDateTime($value["VALUE"]))
				$value = CIBlockPropertyDateTime::ConvertFromDB($arProperty, $value);
			return str_replace(" ", "&nbsp;", htmlspecialcharsex($value["VALUE"]));
		}
		else
			return '&nbsp;';
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE","DESCRIPTION") -- here comes HTML form value
	//strHTMLControlName - array("VALUE","DESCRIPTION")
	//return:
	//safe html
	function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		return  CAdminCalendar::CalendarDate($strHTMLControlName["VALUE"], $value["VALUE"], 20).
			($arProperty["WITH_DESCRIPTION"]=="Y" && '' != trim($strHTMLControlName["DESCRIPTION"]) ?
				'&nbsp;<input type="text" size="20" name="'.$strHTMLControlName["DESCRIPTION"].'" value="'.htmlspecialchars($value["DESCRIPTION"]).'">'
				:''
			);
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//array of error messages
	function CheckFields($arProperty, $value)
	{
		$arResult = array();
		if(strlen($value["VALUE"])>0 && !CheckDateTime($value["VALUE"]))
			$arResult[] = GetMessage("IBLOCK_PROP_DATETIME_ERROR");
		return $arResult;
	}

	//PARAMETERS:
	//$arProperty - b_iblock_property.*
	//$value - array("VALUE",["DESCRIPTION"]) -- here comes HTML form value
	//return:
	//DB form of the value
	function ConvertToDB($arProperty, $value)
	{
		if(strlen($value["VALUE"])>0)
			$value["VALUE"] = CDatabase::FormatDate($value["VALUE"], CLang::GetDateFormat("FULL"), "YYYY-MM-DD HH:MI:SS");
		return $value;
	}

	function ConvertFromDB($arProperty, $value)
	{
		if(strlen($value["VALUE"])>0)
		{
			$value["VALUE"] = CDatabase::FormatDate($value["VALUE"], "YYYY-MM-DD HH:MI:SS", CLang::GetDateFormat("FULL"));
			$value["VALUE"] = str_replace(" 00:00:00", "", $value["VALUE"]);
		}
		return $value;
	}

	function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$arPropertyFields = array(
			"HIDE" => array("ROW_COUNT", "COL_COUNT"),
		);

		return '';
	}

}
?>
