<?
IncludeModuleLangFile(__FILE__);

function _ShowStringPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false)
{
	global $bCopy;
	$start = 0;

	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';

	$rows = $property_fields["ROW_COUNT"];
	$cols = $property_fields["COL_COUNT"];

	if($property_fields["WITH_DESCRIPTION"]=="Y")
		$strAddDesc = "[VALUE]";
	else
		$strAddDesc = "";

	if(!is_array($values)) $values = array();
	foreach($values as $key=>$val)
	{
		if($bCopy)
		{
			$key = "n".$start;
			$start++;
		}
		echo '<tr><td>';
		$val_description = "";
		if(is_array($val) && is_set($val, "VALUE"))
		{
			$val_description = $val["DESCRIPTION"];
			$val = $val["VALUE"];
		}
		if($rows>1)
			echo '<textarea name="'.$name.'['.$key.']'.$strAddDesc.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialcharsex($val).'</textarea>';
		else
			echo '<input name="'.$name.'['.$key.']'.$strAddDesc.'" value="'.htmlspecialcharsex($val).'" size="'.$cols.'" type="text">';

		if($property_fields["WITH_DESCRIPTION"]=="Y")
			echo ' <span title="'.GetMessage("IBLOCK_AT_PROP_DESC").'">'.GetMessage("IBLOCK_AT_PROP_DESC_1").'<input name="'.$name.'['.$key.'][DESCRIPTION]" value="'.htmlspecialcharsex($val_description).'" size="18" type="text" id="'.$name.'['.$key.'][DESCRIPTION]"></span>';

		echo "<br>";
		echo '</td></tr>';

		if($property_fields["MULTIPLE"]!="Y")
		{
			$bVarsFromForm = true;
			break;
		}
	}

	if(!$bVarsFromForm)
	{
		$val_description = "";
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) + ($bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0?1:0) : 1);
		for($i=0; $i<$cnt;$i++)
		{
			echo '<tr><td>';
			if($i==0 && $bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0)
				$val = $property_fields["DEFAULT_VALUE"];
			else
				$val = "";

			if($rows>1)
				echo '<textarea name="'.$name.'[n'.($start + $i).']'.$strAddDesc.'" cols="'.$cols.'" rows="'.$rows.'">'.htmlspecialcharsex($val).'</textarea>';
			else
				echo '<input name="'.$name.'[n'.($start + $i).']'.$strAddDesc.'" value="'.htmlspecialcharsex($val).'" size="'.$cols.'" type="text">';

			if($property_fields["WITH_DESCRIPTION"]=="Y")
				echo ' <span title="'.GetMessage("IBLOCK_AT_PROP_DESC").'">'.GetMessage("IBLOCK_AT_PROP_DESC_1").'<input name="'.$name.'[n'.($start + $i).'][DESCRIPTION]" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';

			echo "<br>";
			echo '</td></tr>';
		}
	}
	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td><input type="button" value="'.GetMessage("IBLOCK_AT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
		echo "<script type=\"text/javascript\">BX.addCustomEvent('onAutoSaveRestore', function(ob, data) {for (var i in data){if (i.substring(0,".(strlen($name)+1).")=='".CUtil::JSEscape($name)."['){addNewRow('tb".md5($name)."')}}})</script>";
	}

	echo '</table>';
}

function _ShowGroupPropertyField($name, $property_fields, $values)
{
	if(!is_array($values)) $values = array();
	foreach($values as $key => $value)
	{
		if(is_array($value) && array_key_exists("VALUE", $value))
			$values[$key] = $value["VALUE"];
	}

	$res = "";
	$bWas = false;
	$sections = CIBlockSection::GetList(
		array("left_margin"=>"asc"),
		array("IBLOCK_ID"=>$property_fields["LINK_IBLOCK_ID"]),
		false,
		array("ID", "DEPTH_LEVEL", "NAME")
	);
	while($ar = $sections->GetNext())
	{
		$res .= '<option value="'.$ar["ID"].'"';
		if(in_array($ar["ID"], $values))
		{
			$bWas = true;
			$res .= ' selected';
		}
		$res .= '>'.str_repeat(" . ", $ar["DEPTH_LEVEL"]-1).$ar["NAME"].'</option>';
	}
	echo '<select name="'.$name.'[]" size="'.$property_fields["MULTIPLE_CNT"].'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>';
	echo '<option value=""'.(!$bWas?' selected':'').'>'.GetMessage("IBLOCK_AT_NOT_SET").'</option>';
	echo $res;
	echo '</select>';
}

function _ShowElementPropertyField($name, $property_fields, $values, $bVarsFromForm = false)
{
	global $bCopy;
	$index = 0;

	if(!is_array($values))
		$values = array();

	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	foreach($values as $key=>$val)
	{
		if($bCopy)
		{
			$key = "n".$index;
			$index++;
		}
		if(is_array($val) && is_set($val, "VALUE"))
			$val = $val["VALUE"];
		$db_res = CIBlockElement::GetByID($val);
		$ar_res = $db_res->GetNext();
		echo '<tr><td>'.
		'<input name="'.$name.'['.$key.']" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
		'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.'\', 600, 500);">'.
		'&nbsp;<span id="sp_'.md5($name).'_'.$key.'" >'.$ar_res['NAME'].'</span>'.
		'</td></tr>';

		if($property_fields["MULTIPLE"]!="Y")
		{
			$bVarsFromForm = true;
			break;
		}
	}

	if(!$bVarsFromForm)
	{
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) : 1);
		for($i = 0; $i < $cnt; $i++)
		{
			$val = "";
			$key = "n".$index;
			$index++;

			echo '<tr><td>'.
			'<input name="'.$name.'['.$key.']" id="'.$name.'['.$key.']" value="'.htmlspecialcharsex($val).'" size="5" type="text">'.
			'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;k='.$key.'\', 600, 500);">'.
			'&nbsp;<span id="sp_'.md5($name).'_'.$key.'"></span>'.
			'</td></tr>';
		}
	}

	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td>'.
			'<input type="button" value="'.GetMessage("IBLOCK_AT_PROP_ADD").'..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$property_fields["LINK_IBLOCK_ID"].'&amp;n='.$name.'&amp;m=y&amp;k='.$key.'\', 600, 500);">'.
			'<span id="sp_'.md5($name).'_'.$key.'" ></span>'.
			'</td></tr>';
	}

	echo '</table>';
	echo "<script type=\"text/javascript\">\r\n";
	echo "var MV_".md5($name)." = ".$index.";\r\n";
	echo "function InS".md5($name)."(id, name){ \r\n";
	echo "	oTbl=document.getElementById('tb".md5($name)."');\r\n";
	echo "	oRow=oTbl.insertRow(oTbl.rows.length-1); \r\n";
	echo "	oCell=oRow.insertCell(-1); \r\n";
	echo "	oCell.innerHTML=".
		"'<input name=\"".$name."[n'+MV_".md5($name)."+']\" value=\"'+id+'\" size=\"5\" type=\"text\">'+\r\n".
		"'<input type=\"button\" value=\"...\" '+\r\n".
		"'onClick=\"jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang=".LANG."&amp;IBLOCK_ID=".$property_fields["LINK_IBLOCK_ID"]."&amp;n=".$name."&amp;k='+MV_".md5($name)."+'\', '+\r\n".
		"' 600, 500);\">'+".
		"'&nbsp;<span id=\"sp_".md5($name)."_'+MV_".md5($name)."+'\" >'+name+'</span>".
		"';";
	echo 'MV_'.md5($name).'++;';
	echo '}';
	echo "\r\n</script>";
}

function _ShowFilePropertyField($name, $property_fields, $values, $max_file_size_show=50000, $bVarsFromForm = false)
{
	global $bCopy, $historyId;

	$bFileman = CModule::IncludeModule('fileman');
	$bVarsFromForm = false;
	echo '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	if(!is_array($values)) $values = array();
	$cols = $property_fields["COL_COUNT"];

	if(!$bCopy)
	{
		foreach($values as $key=>$val)
		{
			echo '<tr><td>';
			$val_description = "";
			if(is_array($val) && is_set($val, "VALUE"))
			{
				$val_description = $val["DESCRIPTION"];
				$val = $val["VALUE"];
			}

			if($bFileman)
			{
				if ($historyId > 0)
					echo CMedialib::InputFile(
						$name."[".$key."]", $val,
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)) //info
					);
				else
					echo CMedialib::InputFile(
						$name."[".$key."]", $val,
						array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
						"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
						array("SIZE"=>$cols), //file
						array(), //server
						array(), //media lib
						($property_fields["WITH_DESCRIPTION"]=="Y"?
							array("NAME" => "DESCRIPTION_".$name."[".$key."]"):
							false
						), //descr
						array(), //delete
						false,
						array() //clouds
					);
			}
			else
			{
				echo CFile::InputFile($name."[".$key."]", $cols, $val, false, 0, "");
				echo "<br>";
				if ($historyId > 0)
					echo CFile::ShowImage($val, 200, 200, "border=0", "", true);
				else
					echo CFile::ShowFile($val, $max_file_size_show, 400, 400, true)."<br>";
				if($property_fields["WITH_DESCRIPTION"]=="Y")
					echo ' <span title="'.GetMessage("IBLOCK_AT_PROP_DESC").'">'.GetMessage("IBLOCK_AT_PROP_DESC_1").'<input name="DESCRIPTION_'.$name.'['.$key.']" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';
			}
			echo '<br></td></tr>';
			if($property_fields["MULTIPLE"]!="Y")
			{
				$bVarsFromForm = true;
				break;
			}
		}
	}

	if(!$bVarsFromForm)
	{
		$MULTIPLE_CNT = IntVal($property_fields["MULTIPLE_CNT"]);
		$cnt = ($property_fields["MULTIPLE"]=="Y"? ($MULTIPLE_CNT>0 && $MULTIPLE_CNT<=30 ? $MULTIPLE_CNT : 5) + ($bInitDef && strlen($property_fields["DEFAULT_VALUE"])>0?1:0) : 1);
		for($i=0; $i<$cnt;$i++)
		{
			echo '<tr><td>';
			$val_description = "";

			if($bFileman)
			{
				echo CMedialib::InputFile(
					$name."[n".$i."]", 0,
					array("IMAGE" => "Y", "PATH" => "Y", "FILE_SIZE" => "Y", "DIMENSIONS" => "Y",
					"IMAGE_POPUP"=>"Y", "MAX_SIZE" => array("W" => 200, "H"=>200)), //info
					array("SIZE"=>$cols), //file
					array(), //server
					array(), //media lib
					($property_fields["WITH_DESCRIPTION"]=="Y"?
						array("NAME" => "DESCRIPTION_".$name."[n".$i."]"):
						false
					), //descr
					array(), //delete
					false,
					array() //clouds
				);
			}
			else
			{
				echo CFile::InputFile($name."[n".$i."]", $cols, "", false, 0, "");
				if($property_fields["WITH_DESCRIPTION"]=="Y")
					echo ' <span title="'.GetMessage("IBLOCK_AT_PROP_DESC").'">'.GetMessage("IBLOCK_AT_PROP_DESC_1").'<input name="DESCRIPTION_'.$name.'[n'.$i.']" value="'.htmlspecialcharsex($val_description).'" size="18" type="text"></span>';
			}
			echo '<br></td></tr>';
		}
	}

	if($property_fields["MULTIPLE"]=="Y")
	{
		echo '<tr><td><input type="button" value="'.GetMessage("IBLOCK_AT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}

	echo '</table>';
}

function _ShowListPropertyField($name, $property_fields, $values, $bInitDef = false, $def_text = false)
{
	if(!is_array($values)) $values = array();
	foreach($values as $key => $value)
	{
		if(is_array($value) && array_key_exists("VALUE", $value))
			$values[$key] = $value["VALUE"];
	}

	$id = $property_fields["ID"];
	$multiple = $property_fields["MULTIPLE"];
	$res = "";
	if($property_fields["LIST_TYPE"]=="C") //list property as checkboxes
	{
		$cnt = 0;
		$wSel = false;
		$prop_enums = CIBlockProperty::GetPropertyEnum($id);
		while($ar_enum = $prop_enums->Fetch())
		{
			$cnt++;
			if($bInitDef)
				$sel = ($ar_enum["DEF"]=="Y");
			else
				$sel = in_array($ar_enum["ID"], $values);
			if($sel)
				$wSel = true;

			$uniq = md5(uniqid(rand(), true));
			if($multiple=="Y") //multiple
				$res .= '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").' id="'.$uniq.'"><label for="'.$uniq.'">'.htmlspecialcharsex($ar_enum["VALUE"]).'</label><br>';
			else //if(MULTIPLE=="Y")
				$res .= '<input type="radio" name="'.$name.'[]" id="'.$uniq.'" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").'><label for="'.$uniq.'">'.htmlspecialcharsex($ar_enum["VALUE"]).'</label><br>';

			if($cnt==1)
				$res_tmp = '<input type="checkbox" name="'.$name.'[]" value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" checked":"").' id="'.$uniq.'"><br>';
		}


		$uniq = md5(uniqid(rand(), true));

		if($cnt==1)
			$res = $res_tmp;
		elseif($multiple!="Y")
			$res = '<input type="radio" name="'.$name.'[]" value=""'.(!$wSel?" checked":"").' id="'.$uniq.'"><label for="'.$uniq.'">'.htmlspecialcharsex(($def_text ? $def_text : GetMessage("IBLOCK_AT_PROP_NO") )).'</label><br>'.$res;

		if($multiple=="Y" || $cnt==1)
			$res = '<input type="hidden" name="'.$name.'" value="">'.$res;
	}
	else //list property as list
	{
		$bNoValue = true;
		$prop_enums = CIBlockProperty::GetPropertyEnum($id);
		while($ar_enum = $prop_enums->Fetch())
		{
			if($bInitDef)
				$sel = ($ar_enum["DEF"]=="Y");
			else
				$sel = in_array($ar_enum["ID"], $values);
			if($sel)
				$bNoValue = false;
			$res .= '<option value="'.htmlspecialchars($ar_enum["ID"]).'"'.($sel?" selected":"").'>'.htmlspecialcharsex($ar_enum["VALUE"]).'</option>';
		}

		if($property_fields["MULTIPLE"]=="Y" && IntVal($property_fields["ROW_COUNT"])<2)
			$property_fields["ROW_COUNT"] = 5;
		if($property_fields["MULTIPLE"]=="Y")
			$property_fields["ROW_COUNT"]++;
		$res = '<select name="'.$name.'[]" size="'.$property_fields["ROW_COUNT"].'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>'.
				'<option value=""'.($bNoValue?' selected':'').'>'.htmlspecialcharsex(($def_text ? $def_text : GetMessage("IBLOCK_AT_PROP_NA") )).'</option>'.
				$res.
				'</select>';
	}
	echo $res;
}

function _ShowUserPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false, $max_file_size_show=50000, $form_name = "form_element", $bCopy = false)
{
	global $bCopy;
	$start = 0;

	if(!is_array($property_fields["~VALUE"]))
		$values = array();
	else
		$values = $property_fields["~VALUE"];
	unset($property_fields["VALUE"]);
	unset($property_fields["~VALUE"]);

	$html = '<table cellpadding="0" cellspacing="0" border="0" class="nopadding" width="100%" id="tb'.md5($name).'">';
	$arUserType = CIBlockProperty::GetUserType($property_fields["USER_TYPE"]);
	$bMultiple = $property_fields["MULTIPLE"] == "Y" && array_key_exists("GetPropertyFieldHtmlMulty", $arUserType);
	$max_val = -1;

	if(($arUserType["PROPERTY_TYPE"] !== "F") || (!$bCopy))
	{
		if($bMultiple)
		{
			$html .= '<tr><td>';
			$html .= call_user_func_array($arUserType["GetPropertyFieldHtmlMulty"],
				array(
					$property_fields,
					$values,
					array(
						"VALUE"=>'PROP['.$property_fields["ID"].']',
						"FORM_NAME"=>$form_name,
						"MODE"=>"FORM_FILL",
						"COPY" => $bCopy,
					),
				));
			$html .= '</td></tr>';
		}
		else
		{
		foreach($values as $key=>$val)
		{
			if($bCopy)
			{
				$key = "n".$start;
				$start++;
			}

			if(!is_array($val) || !array_key_exists("VALUE",$val))
				$val = array("VALUE"=>$val, "DESCRIPTION"=>"");

			$html .= '<tr><td>';
			if(array_key_exists("GetPropertyFieldHtml", $arUserType))
				$html .= call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$property_fields,
						$val,
						array(
							"VALUE"=>'PROP['.$property_fields["ID"].']['.$key.'][VALUE]',
							"DESCRIPTION"=>'PROP['.$property_fields["ID"].']['.$key.'][DESCRIPTION]',
							"FORM_NAME"=>$form_name,
							"MODE"=>"FORM_FILL",
							"COPY"=>$bCopy,
						),
					));
			else
				$html .= '&nbsp;';
			$html .= '</td></tr>';

			if(substr($key, -1, 1)=='n' && $max_val < intval(substr($key, 1)))
				$max_val = intval(substr($key, 1));
			if($property_fields["MULTIPLE"] != "Y")
			{
				$bVarsFromForm = true;
				break;
			}
		}
		}
	}

	if(!$bVarsFromForm && !$bMultiple)
	{
		$bDefaultValue = is_array($property_fields["DEFAULT_VALUE"]) || strlen($property_fields["DEFAULT_VALUE"]);

		if($property_fields["MULTIPLE"]=="Y")
		{
			$cnt = IntVal($property_fields["MULTIPLE_CNT"]);
			if($cnt <= 0 || $cnt > 30)
				$cnt = 5;

			if($bInitDef && $bDefaultValue)
				$cnt++;
		}
		else
		{
			$cnt = 1;
		}

		for($i=$max_val+1; $i<$max_val+1+$cnt; $i++)
		{
			if($i==0 && $bInitDef && $bDefaultValue)
				$val = array(
					"VALUE"=>$property_fields["DEFAULT_VALUE"],
					"DESCRIPTION"=>"",
				);
			else
				$val = array(
					"VALUE"=>"",
					"DESCRIPTION"=>"",
				);

			$key = "n".($start + $i);

			$html .= '<tr><td>';
			if(array_key_exists("GetPropertyFieldHtml", $arUserType))
				$html .= call_user_func_array($arUserType["GetPropertyFieldHtml"],
					array(
						$property_fields,
						$val,
						array(
							"VALUE"=>'PROP['.$property_fields["ID"].']['.$key.'][VALUE]',
							"DESCRIPTION"=>'PROP['.$property_fields["ID"].']['.$key.'][DESCRIPTION]',
							"FORM_NAME"=>$form_name,
							"MODE"=>"FORM_FILL",
							"COPY"=>$bCopy,
						),
					));
			else
				$html .= '&nbsp;';
			$html .= '</td></tr>';
		}
		$max_val += $cnt;
	}
	if($property_fields["MULTIPLE"]=="Y" && $arUserType["USER_TYPE"] !== "HTML" && !$bMultiple)
	{
		$html .= '<tr><td><input type="button" value="'.GetMessage("IBLOCK_AT_PROP_ADD").'" onClick="addNewRow(\'tb'.md5($name).'\')"></td></tr>';
	}
	$html .= '</table>';
	echo $html;
}

function _ShowPropertyField($name, $property_fields, $values, $bInitDef = false, $bVarsFromForm = false, $max_file_size_show = 50000, $form_name = "form_element", $bCopy = false)
{
	$type = $property_fields["PROPERTY_TYPE"];
	if($property_fields["USER_TYPE"]!="")
		_ShowUserPropertyField($name, $property_fields, $values, $bInitDef, $bVarsFromForm, $max_file_size_show, $form_name, $bCopy);
	elseif($type=="L") //list property
		_ShowListPropertyField($name, $property_fields, $values, $bInitDef);
	elseif($type=="F") //file property
		_ShowFilePropertyField($name, $property_fields, $values, $max_file_size_show, $bVarsFromForm);
	elseif($type=="G") //section link
	{
		if(function_exists("_ShowGroupPropertyField_custom"))
			_ShowGroupPropertyField_custom($name, $property_fields, $values, $bVarsFromForm);
		else
			_ShowGroupPropertyField($name, $property_fields, $values, $bVarsFromForm);
	}
	elseif($type=="E") //element link
		_ShowElementPropertyField($name, $property_fields, $values, $bVarsFromForm);
	else
		_ShowStringPropertyField($name, $property_fields, $values, $bInitDef, $bVarsFromForm);
}

function _ShowHiddenValue($name, $value)
{
	$res = "";

	if(is_array($value))
	{
		foreach($value as $k => $v)
			$res .= _ShowHiddenValue($name.'['.htmlspecialchars($k).']', $v);
	}
	else
	{
		$res .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'">'."\n";
	}

	return $res;
}

class _CIBlockError
{
	var $err_type, $err_text, $err_level;

	function _CIBlockError($err_level = false, $err_type = "", $err_text = "")
	{
		$this->err_type = $err_type;
		$this->err_text = preg_replace("#<br>$#i", "", $err_text);
		$this->err_level = $err_level;
		_CIBlockError::GetErrorText($this);
	}

	function GetErrorText($error = false)
	{
		static $errors = array();
		$str = "";
		if(is_object($error))
		{
			$errors[] = $error;
		}
		else
		{
			foreach($errors as $error)
			{
				if($str)
					$str .= "<br>";
				$str .= $error->err_text;
			}
		}
		return $str;
	}
}

if(
	$_SERVER["REQUEST_METHOD"] === "GET"
	&& isset($_GET["ajax"]) && $_GET["ajax"] === "y"
	&& isset($_GET["entity_type"])
	&& isset($_GET["iblock_id"])
	&& isset($_GET["id"])
	&& check_bitrix_sessid()
)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

	if($_GET["entity_type"] == "element")
		$obRights = new CIBlockElementRights($_GET["iblock_id"], $_GET["id"]);
	elseif($_GET["entity_type"] == "section")
		$obRights = new CIBlockSectionRights($_GET["iblock_id"], $_GET["id"]);
	else
		$obRights = new CIBlockRights($_GET["id"]);

	$obStorage = $obRights->_storage_object();

	$arOverwrited = array();
	if(isset($_REQUEST["added"]) && is_array($_REQUEST["added"]))
	{
		foreach($_REQUEST["added"] as $provider => $arCodes)
		{
			if(is_array($arCodes))
			{
				foreach($arCodes as $id => $arCode)
					$arOverwrited[$id] = $obStorage->CountOverWrited($id);
			}
		}
	}

	if(isset($_REQUEST["info"]) && $_REQUEST["info"] > 0)
	{
		$arOverwrited = $obRights->GetUserOperations($_GET["id"], $_REQUEST["info"]);
	}

	echo CUtil::PHPToJSObject($arOverwrited);

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");
}

function IBlockShowRights($entity_type, $iblock_id, $id, $section_title, $variable_name, $arPossibleRights, $arActualRights, $bDefault = false, $bForceInherited = false, $arSelected = array(), $arHighLight = array())
{
	$js_var_name = preg_replace("/[^a-zA-Z0-9_]/", "_", $variable_name);
	$html_var_name = htmlspecialchars($variable_name);

	$sSelect = '<select name="'.$html_var_name.'[][TASK_ID]" style="vertical-align:middle">';
	foreach($arPossibleRights as $value => $title)
		$sSelect .= '<option value="'.htmlspecialchars($value).'">'.htmlspecialcharsex($title).'</option>';
	$sSelect .= '</select>';

	if($bForceInherited != true)
	{
		foreach($arActualRights as $RIGHT_ID => $arRightSet)
			if($arRightSet["IS_INHERITED"] <> "Y")
				$arSelected[$arRightSet["GROUP_CODE"]] = true;
	}

	$table_id = $variable_name."_table";
	$href_id = $variable_name."_href";

	CJSCore::Init(array('access'));
	if($section_title != ""):?>
	<tr id="<?echo $html_var_name?>_heading" class="heading">
		<td colspan="2">
			<?echo $section_title?>
		</td>
	</tr>
	<?endif?>
	<tr>
		<td colspan="2" align="center">
			<script type="text/javascript">
				BX.message({
						langApplyTitle: '<?=CUtil::JSEscape(GetMessage("IBLOCK_AT_OVERWRITE_TIP"))?>',
						langApply1Title: '<?=CUtil::JSEscape(GetMessage("IBLOCK_AT_OVERWRITE_1"))?>',
						langApply2Title: '<?=CUtil::JSEscape(GetMessage("IBLOCK_AT_OVERWRITE_2"))?>',
						langApply3Title: '<?=CUtil::JSEscape(GetMessage("IBLOCK_AT_OVERWRITE_3"))?>'
				});
				var obIBlockAccess_<?=$js_var_name?> = new JCIBlockAccess(
					'<?=CUtil::JSEscape($entity_type)?>',
					<?=intval($iblock_id)?>,
					<?=intval($id)?>,
					<?=CUtil::PhpToJsObject($arSelected)?>,
					'<?=CUtil::JSEscape($variable_name)?>',
					'<?=CUtil::JSEscape($table_id)?>',
					'<?=CUtil::JSEscape($href_id)?>',
					'<?=CUtil::JSEscape($sSelect)?>',
					<?=CUtil::PhpToJsObject($arHighLight)?>
				);
			</script>
			<table width="100%" cellpadding="0" cellspacing="10" border="0" id="<?echo htmlspecialchars($table_id)?>" align="center">
			<?
			$arNames = array();
			foreach($arActualRights as $arRightSet)
				$arNames[] = $arRightSet["GROUP_CODE"];

			$access = new CAccess();
			$arNames = $access->GetNames($arNames);

			foreach($arActualRights as $RIGHT_ID => $arRightSet)
			{
				if($bForceInherited || $arRightSet["IS_INHERITED"] == "Y")
				{
					?>
					<tr class="<?echo $html_var_name?>_row_for_<?echo htmlspecialchars($arRightSet["GROUP_CODE"])?><?if($arRightSet["IS_OVERWRITED"] == "Y") echo " iblock-strike-out";?>">
						<td align="right"><?echo htmlspecialcharsex($arNames[$arRightSet["GROUP_CODE"]]["provider"]." ".$arNames[$arRightSet["GROUP_CODE"]]["name"])?>:</td>
						<td align="left">
							<?if($arRightSet["IS_OVERWRITED"] != "Y"):?>
							<input type="hidden" name="<?echo $html_var_name?>[][RIGHT_ID]" value="<?echo htmlspecialchars($RIGHT_ID)?>">
							<input type="hidden" name="<?echo $html_var_name?>[][GROUP_CODE]" value="<?echo htmlspecialchars($arRightSet["GROUP_CODE"])?>">
							<input type="hidden" name="<?echo $html_var_name?>[][TASK_ID]" value="<?echo htmlspecialchars($arRightSet["TASK_ID"])?>">
							<?endif;?>
							<?echo htmlspecialcharsex($arPossibleRights[$arRightSet["TASK_ID"]])?>
						</td>
					</tr>
					<?
				}
			}

			if($bForceInherited != true)
			{
				foreach($arActualRights as $RIGHT_ID => $arRightSet)
				{
					if($arRightSet["IS_INHERITED"] <> "Y")
					{
					?>
					<tr valign="top">
						<td align="right"><?echo htmlspecialcharsex($arNames[$arRightSet["GROUP_CODE"]]["provider"]." ".$arNames[$arRightSet["GROUP_CODE"]]["name"])?>:</td>
						<td align="left">
							<input type="hidden" name="<?echo $html_var_name?>[][RIGHT_ID]" value="<?echo htmlspecialchars($RIGHT_ID)?>">
							<input type="hidden" name="<?echo $html_var_name?>[][GROUP_CODE]" value="<?echo htmlspecialchars($arRightSet["GROUP_CODE"])?>">
							<select name="<?echo $html_var_name?>[][TASK_ID]" style="vertical-align:middle">
							<?foreach($arPossibleRights as $value => $title):?>
								<option value="<?echo htmlspecialchars($value)?>" <?if($value == $arRightSet["TASK_ID"]) echo "selected"?>><?echo htmlspecialcharsex($title)?></option>
							<?endforeach?>
							</select>
							<a href="javascript:void(0);" onclick="JCIBlockAccess.DeleteRow(this, '<?=htmlspecialchars(CUtil::addslashes($arRightSet["GROUP_CODE"]))?>', '<?=CUtil::JSEscape($variable_name)?>')" class="access-delete"></a>
							<?if($bDefault):?>
								<span title="<?echo GetMessage("IBLOCK_AT_OVERWRITE_TIP")?>"><?
								if(
									is_array($arRightSet["OVERWRITED"])
									&& $arRightSet["OVERWRITED"][0] > 0
									&& $arRightSet["OVERWRITED"][1] > 0
								)
								{
									?>
									<br><input name="<?echo $html_var_name?>[][DO_CLEAN]" value="Y" type="checkbox"><?echo GetMessage("IBLOCK_AT_OVERWRITE_1")?> (<?echo intval($arRightSet["OVERWRITED"][0]+$arRightSet["OVERWRITED"][1])?>)
									<?
								}
								elseif(
									is_array($arRightSet["OVERWRITED"])
									&& $arRightSet["OVERWRITED"][0] > 0
								)
								{
									?>
									<br><input name="<?echo $html_var_name?>[][DO_CLEAN]" value="Y" type="checkbox"><?echo GetMessage("IBLOCK_AT_OVERWRITE_2")?> (<?echo intval($arRightSet["OVERWRITED"][0])?>)
									<?
								}
								elseif(
									is_array($arRightSet["OVERWRITED"])
									&& $arRightSet["OVERWRITED"][1] > 0
								)
								{
									?>
									<br><input name="<?echo $html_var_name?>[][DO_CLEAN]" value="Y" type="checkbox"><?echo GetMessage("IBLOCK_AT_OVERWRITE_3")?> (<?echo intval($arRightSet["OVERWRITED"][1])?>)
									<?
								}?></span>
							<?endif;?>
						</td>
					</tr>
					<?
					}
				}
			}
			?>
				<tr>
					<td width="40%" align="right">&nbsp;</td>
					<td width="60%" align="left">
						<a href="javascript:void(0)"  id="<?echo htmlspecialchars($href_id)?>" class="bx-action-href"><?echo GetMessage("IBLOCK_AT_PROP_ADD")?></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?
}

function GetUserProfileLink($user_id, $title)
{
	static $arUsersCache = array();
	$user_id = intval($user_id);

	if($user_id > 0)
	{
		if(!isset($arUsersCache[$user_id]))
		{
			$rsUser = CUser::GetByID($user_id);
			$arUsersCache[$user_id] = $rsUser->Fetch();
		}

		if($arUsersCache[$user_id])
			return '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$user_id.'" title="'.$title.'">'.$user_id."</a>]&nbsp;(".$arUsersCache[$user_id]["LOGIN"].") ".$arUsersCache[$user_id]["NAME"]." ".$arUsersCache[$user_id]["LAST_NAME"];
	}
	return '';
}

function IBlockGetHiddenHTML($name, $value)
{
	$result = "";
	if(is_array($value))
	{
		$i = 0;
		foreach($value as $k => $v)
		{
			if($k === $i)
				$result .= IBlockGetHiddenHTML($name."[]", $v);
			else
				$result .= IBlockGetHiddenHTML($name."[".$k."]", $v);
			$i++;
		}
	}
	else
	{
		$result = '<input type="hidden" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" />'."\n";
	}
	return $result;
}
?>