<?
	IncludeModuleLangFile(__FILE__);

	//Sanitize html text from xss etc. code
	class CBXSanitizer
	{
		private static $htmlTags = array();
		private static $secLevel = "";
		private static $noClose = array('br','hr','img');
		private static $localAlph;

		protected static function SetLevel($secLevel)
		{
			if($secLevel!='HIGH' && $secLevel!='MID' && $secLevel!='LOW')
				$secLevel='HIGH';

			switch ($secLevel)
			{
				case 'HIGH':
					$arTags = array(
						'b'			=> array(),
						'br'		=> array(),
						'big'		=> array(),
						'code'		=> array(),
						'del'		=> array(),
						'dt'		=> array(),
						'dd'		=> array(),
						'font'		=> array(),
						'h1'		=> array(),
						'h2'		=> array(),
						'h3'		=> array(),
						'h4'		=> array(),
						'h5'		=> array(),
						'h6'		=> array(),
						'hr'		=> array(),
						'i'			=> array(),
						'ins'		=> array(),
						'li'		=> array(),
						'ol'		=> array(),
						'p'			=> array(),
						'small'		=> array(),
						's'			=> array(),
						'sub'		=> array(),
						'sup'		=> array(),
						'strong'	=> array(),
						'pre'		=> array(),
						'u'			=> array(),
						'ul'		=> array()
					);
					self::$secLevel = 'HIGH';
					break;

				case 'MID':
					$arTags = array(
						'a'			=> array('href', 'title','name','alt'),
						'b'			=> array(),
						'br'		=> array(),
						'big'		=> array(),
						'code'		=> array(),
						'caption'	=> array(),
						'del'		=> array('title'),
						'dt'		=> array(),
						'dd'		=> array(),
						'font'		=> array('color','size'),
						'color'		=> array(),
						'h1'		=> array(),
						'h2'		=> array(),
						'h3'		=> array(),
						'h4'		=> array(),
						'h5'		=> array(),
						'h6'		=> array(),
						'hr'		=> array(),
						'i'			=> array(),
						'img'		=> array('src','alt','height','width','title'),
						'ins'		=> array('title'),
						'li'		=> array(),
						'ol'		=> array(),
						'p'			=> array(),
						'pre'		=> array(),
						's'			=> array(),
						'small'		=> array(),
						'strong'	=> array(),
						'sub'		=> array(),
						'sup'		=> array(),
						'table'		=> array('border','width'),
						'tbody'		=> array('align','valign'),
						'td'		=> array('width','height','align','valign'),
						'tfoot'		=> array('align','valign'),
						'th'		=> array('width','height'),
						'thead'		=> array('align','valign'),
						'tr'		=> array('align','valign'),
						'ul'		=> array()
					);
					self::$secLevel = 'MID';
					break;

				case 'LOW':
					$arTags = array(
						'a'			=> array('href', 'title','name','style','id','class','shape','coords','alt','target'),
						'b'			=> array('style','id','class'),
						'br'		=> array('style','id','class'),
						'big'		=> array('style','id','class'),
						'caption'	=> array('style','id','class'),
						'code'		=> array('style','id','class'),
						'del'		=> array('title','style','id','class'),
						'div'		=> array('title','style','id','class','align'),
						'dt'		=> array('style','id','class'),
						'dd'		=> array('style','id','class'),
						'font'		=> array('color','size','face','style','id','class'),
						'h1'		=> array('style','id','class','align'),
						'h2'		=> array('style','id','class','align'),
						'h3'		=> array('style','id','class','align'),
						'h4'		=> array('style','id','class','align'),
						'h5'		=> array('style','id','class','align'),
						'h6'		=> array('style','id','class','align'),
						'hr'		=> array('style','id','class'),
						'i'			=> array('style','id','class'),
						'img'		=> array('src','alt','height','width','title'),
						'ins'		=> array('title','style','id','class'),
						'li'		=> array('style','id','class'),
						'map'		=> array('shape','coords','href','alt','title','style','id','class','name'),
						'ol'		=> array('style','id','class'),
						'p'			=> array('style','id','class','align'),
						'pre'		=> array('style','id','class'),
						's'			=> array('style','id','class'),
						'small'		=> array('style','id','class'),
						'strong'	=> array('style','id','class'),
						'span'		=> array('title','style','id','class','align'),
						'sub'		=>array('style','id','class'),
						'sup'		=>array('style','id','class'),
						'table'		=> array('border','width','style','id','class','cellspacing','cellpadding'),
						'tbody'		=> array('align','valign','style','id','class'),
						'td'		=> array('width','height','style','id','class','align','valign','colspan','rowspan'),
						'tfoot'		=> array('align','valign','style','id','class','align','valign'),
						'th'		=> array('width','height','style','id','class','colspan','rowspan'),
						'thead'		=> array('align','valign','style','id','class'),
						'tr'		=> array('align','valign','style','id','class'),
						'ul'		=> array('style','id','class')
					);
					self::$secLevel = 'LOW';
					break;
			}

			self::SetTags($arTags);
		}

		protected static function IsValidAttr($arAttr)
		{
			if(!isset($arAttr[1]) || !isset($arAttr[3]))
				return false;

			switch ($arAttr[1])
			{
				case 'href':
					$valid = preg_match("#^(http://|https://|ftp://|mailto:|callto:|\#|/|)+#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) &&  !preg_match("#javascript:|data:|[^a-z0-9".self::$localAlph."_:/\.=\&\#\?\-]#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) ? true : false;
					break;

				case 'height':
				case 'width':
				case 'cellpadding':
				case 'cellspacing':
					$valid = !preg_match("#^[^0-9\-]+(px|%)*#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) ? true : false;
					break;

				case 'src':
					$valid = !preg_match("#javascript:|data:|[^a-z0-9".self::$localAlph."_:/\.=\&\?\-]#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) ? true : false;
					break;

				case 'title':
				case 'alt':
					$valid = !preg_match("#[^a-z0-9\.\?!,".self::$localAlph."_\s\-]#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) ? true : false;
					break;

				case 'style':
					$valid = !preg_match("#[^a-z0-9_\s:;\-]#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) ? true : false;
					break;

				case 'coords':
					$valid = !preg_match("#[^0-9\s,\-]#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) ? true : false;
					break;

				default:
					$valid = !preg_match("#[^a-z".self::$localAlph."0-9_]#i".BX_UTF_PCRE_MODIFIER,$arAttr[3]) ? true : false;
					break;
			}

			return $valid;
		}

		//returns string with allowed tags and attributies
		public static function GetTags()
		{
			if(!is_array(self::$htmlTags))
				return false;

			$confStr="";

			foreach (self::$htmlTags as $tag => $arAttrs)
			{
				$confStr.=$tag." (";
				foreach ($arAttrs as $attr)
					if($attr)
						$confStr.=" ".$attr." ";
				$confStr.=")<br>";
			}

			return $confStr;
		}

		//sets allowed tags from array
		public static function SetTags($arTags)
		{
			if(!is_array($arTags))
				return false;

			foreach($arTags as $arTag)
				for($i=0; $i<count($arTag); $i++)
					$arTag[$i] = strtolower($arTag[$i]);

			self::$htmlTags = array_change_key_case($arTags);
			return true;
		}

		//Sanitaze starts&ends here
		public static function Sanitize($html, $secLevel='HIGH', $htmlspecialchars=true, $delTags=true)
		{

			//CUSTOM mean that user defined white list tags earlier by call CBXSanitizer::SetTags($arWhiteListTags=array(...)) manualy
			if(($secLevel!='CUSTOM' && self::$secLevel!=$secLevel) || empty(self::$htmlTags))
				self::SetLevel($secLevel);

			$openTagsStack = array();
			$isCode = false;

			if(LANGUAGE_ID!="en")
				self::$localAlph=GetMessage("SNT_SYMB");
			else
				self::$localAlph="";

			//split html to tag and simple text
			$seg = array();
			while(preg_match('/<[^<>]+>/si'.BX_UTF_PCRE_MODIFIER, $html, $matches, PREG_OFFSET_CAPTURE))
			{
				if($matches[0][1])
					$seg[] = array('segType'=>'text', 'value'=>substr($html, 0, $matches[0][1]));

				$seg[] = array('segType'=>'tag', 'value'=>$matches[0][0]);
				$html = substr($html, $matches[0][1]+strlen($matches[0][0]));
			}

			if($html != '')
				$seg[] = array('segType'=>'text', 'value'=>$html);

			//process segments
			for($i=0; $i<count($seg); $i++)
			{
				if($seg[$i]['segType'] == 'text' && $htmlspecialchars)
					$seg[$i]['value'] = htmlspecialchars($seg[$i]['value'],ENT_QUOTES,LANG_CHARSET);
				elseif($seg[$i]['segType'] == 'tag')
				{
					//find tag type (open/close), tag name, attributies
					preg_match('#^<\s*(/)?\s*([a-z0-9]+)(.*?)>$#si'.BX_UTF_PCRE_MODIFIER, $seg[$i]['value'], $matches);
					$seg[$i]['tagType'] = ( $matches[1] ? 'close' : 'open' );
					$seg[$i]['tagName'] = strtolower($matches[2]);

					if(($seg[$i]['tagName']=='code') && ($seg[$i]['tagType']=='close'))
						$isCode = false;

					//if tag founded inside  <code></code>  it is simple text
					if($isCode)
					{
						$seg[$i]['segType'] = 'text';
						$i--;
						continue;
					}

					if($seg[$i]['tagType'] == 'open')
					{
						// if tag unallowed screen it, or erase
						if(!array_key_exists($seg[$i]['tagName'], self::$htmlTags))
						{
							if($delTags)
								$seg[$i]['action'] = 'del';
							else
							{
								$seg[$i]['segType'] = 'text';
								$i--;
								continue;
							}
						}
						//if allowed
						else
						{
							//find attributies an erase unallowed
							preg_match_all('#([a-z_]+)\s*=\s*([\'\"])\s*(.*?)\s*\2#i'.BX_UTF_PCRE_MODIFIER, $matches[3], $arTagAttrs, PREG_SET_ORDER);
							$attr = array();
							foreach($arTagAttrs as $arTagAttr)
								if(in_array(strtolower($arTagAttr[1]), self::$htmlTags[$seg[$i]['tagName']]))
									if(self::IsValidAttr($arTagAttr))
										if($htmlspecialchars)
											$attr[strtolower($arTagAttr[1])] = htmlspecialchars($arTagAttr[3], ENT_QUOTES,LANG_CHARSET);
										else
											$attr[strtolower($arTagAttr[1])] = $arTagAttr[3];

							$seg[$i]['attr'] = $attr;
							if($seg[$i]['tagName'] == 'code')
								$isCode = true;

							//if tag need close tag add it to stack opened tags
							if(!count(self::$htmlTags[$seg[$i]['tagName']]) || !in_array($seg[$i]['tagName'], self::$noClose))
								array_push($openTagsStack, $seg[$i]['tagName']);
						}
					}
					//if closing tag
					else
					{	//if tag allowed
						if(array_key_exists($seg[$i]['tagName'], self::$htmlTags) && (!count(self::$htmlTags[$seg[$i]['tagName']]) || (self::$htmlTags[$seg[$i]['tagName']][count(self::$htmlTags[$seg[$i]['tagName']])-1] != false)))
						{
							if($seg[$i]['tagName'] == 'code')
								$isCode = false;
							//if open tags stack is empty, or not include it's name lets screen/erase it
							if((count($openTagsStack) == 0) || (!in_array($seg[$i]['tagName'], $openTagsStack)))
							{
								if($delTags)
									$seg[$i]['action'] = 'del';
								else
								{
									$seg[$i]['segType'] = 'text';
									$i--;
									continue;
								}
							}
							else
							{
								//if this tag don't match last from open tags stack , adding right close tag
								$tagName = array_pop($openTagsStack);
								if($seg[$i]['tagName'] != $tagName)
									array_splice($seg, $i, 0, array(array('segType'=>'tag', 'tagType'=>'close', 'tagName'=>$tagName, 'action'=>'add')));
							}
						}
						//if tag unallowed erase it
						else
						{
							if($delTags) $seg[$i]['action'] = 'del';
							else
							{
								$seg[$i]['segType'] = 'text';
								$i--;
								continue;
							}
						}
					}
				}
			}

			//close tags stayed in stack
			foreach(array_reverse($openTagsStack) as $val)
				array_push($seg, array('segType'=>'tag', 'tagType'=>'close', 'tagName'=>$val, 'action'=>'add'));

			//build filtered code and return it
			$filteredHTML = '';
			foreach($seg as $segt)
			{
				if($segt['segType'] == 'text')
					$filteredHTML .= $segt['value'];
				elseif(($segt['segType'] == 'tag') && ($segt['action'] != 'del'))
				{
					if($segt['tagType'] == 'open')
					{
						$filteredHTML .= '<'.$segt['tagName'];

						if(is_array($segt['attr']))
							foreach($segt['attr'] as $attr_key=>$attr_val)
								$filteredHTML .= ' '.$attr_key.'="'.$attr_val.'"';

						if (count(self::$htmlTags[$segt['tagName']]) && (self::$htmlTags[$segt['tagName']][count(self::$htmlTags[$segt['tagName']])-1] == false))
							$filteredHTML .= " /";

						$filteredHTML .= '>';
					}
					elseif($segt['tagType'] == 'close')
						$filteredHTML .= '</'.$segt['tagName'].'>';
				}
			}
			return $filteredHTML;
		}
	};
?>
