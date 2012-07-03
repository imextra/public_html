// v0.91
function CBXHotKeys()
{
	var _this = this;
	var idxKS = 0;
	var idxCode = 1;
	var idxCodeId = 2;
	var idxName = 3;
	var idxHKId = 4;
	var arServSymb = { 8: 'Back Space',9: 'Tab',13: 'Enter',16: 'Shift',17: 'Ctrl',18: 'Alt',19: 'Pause',
					20: 'Caps Lock',27: 'ESC',32: 'Space bar',33: 'Page Up',34: 'Page Down',35: 'End',36: 'Home',
					37: 'Left',38: 'Up',39: 'Right',40: 'Down',45: 'Insert',46: 'Delete',96: '0 (ext)',97: '1 (ext)',
					98: '2 (ext)',99: '3 (ext)',100: '4 (ext)',101: '5 (ext)',102: '6 (ext)',105: '9 (ext)',106: '* (ext)',
					107: '+ (ext)',104: '8 (ext)',103: '7 (ext)',110: '. (ext)',111: '/ (ext)',112: 'F1',113: 'F2',114: 'F3',
					115: 'F4',116: 'F5',117: 'F6',118: 'F7',119: 'F8',120: 'F9',121: 'F10',122: 'F11',123: 'F12',144: 'Num Lock',
					186: ';',188: ',',190: '.',191: '/',192: '`',219: '[',220: '|',221: ']',222: "'",189: '-',187: '+',145: 'Scrol Lock' };
	var bxHotKeyCode=0;
	var inputKeyCode=0;
	var inputDopString="";

	this.ArrHKCode=[];
	this.MesNotAssign="";
	this.MesClToChange="";
	this.MesClean="";
	this.MesBusy="";
	this.MesClose="";
	this.MesSettings="";
	this.MesDefault="";
	this.MesDelAll="";
	this.uid="";
	this.deleting = false;



	this.Init = function()
	{
		this.Register();
	};

	// keysString: Ctrl+Alt+Shift+KeyCode
	this.UpdateKS = function(codeId, keysString)
	{
		for(var i=0; i<this.ArrHKCode.length; i++)
  			if(this.ArrHKCode[i][idxCodeId]==codeId)
  			{
  				this.ArrHKCode[i][idxKS]=keysString;
  				return true;
  			}
	};

	this.UpdateHk = function(codeId, hkId)
	{
		for(var i=0; i<this.ArrHKCode.length; i++)
  			if(this.ArrHKCode[i][idxCodeId]==codeId)
  			{
  				this.ArrHKCode[i][idxHKId]=hkId;
  				return i;
  			}

  		return (-1);
	};

	this.Add = function(keysString, execCode, codeId, name, hkId)
	{
		for(var i=0; i<this.ArrHKCode.length; i++)
  			if(this.ArrHKCode[i][idxCodeId]==codeId)
  				return false;

		return this.ArrHKCode.push([String(keysString),String(execCode),codeId,String(name),hkId]);
	};

	// keysString: Ctrl+Alt+Shift+KeyCode
	this.GetExCode = function(keysString)
	{
		var ret="";
		if(keysString)
			for(var i=0; i<this.ArrHKCode.length; i++)
	  			if (this.ArrHKCode[i][idxKS]==keysString)
	  			{
	  				if(ret)
	  					ret+=" ";

	  				ret+=this.ArrHKCode[i][idxCode];
	  			}

		return ret;
	};

	this.MakeKeyString = function(Event)
	{
		this.inputDopString = (Event.ctrlKey ? 'Ctrl+':'') + (Event.altKey ? 'Alt+':'') + (Event.shiftKey ? 'Shift+':'');
		this.inputKeyCode = Event.keyCode;

		if(!this.inputKeyCode)
			this.inputKeyCode = Event.charCode;

		return this.inputDopString + this.inputKeyCode;
	};

	this.ShowSettings = function()
	{
		var formText = '<table width="100%" id="tbl_hk_settings">';
		var keyStr="";
		var editStr="";

		for(var i=0; i<this.ArrHKCode.length; i++)
		{
			if(this.ArrHKCode[i][idxKS])
				keyStr=this.PrintKSAsChar(this.ArrHKCode[i][idxKS]);
			else
				keyStr=this.MesNotAssign;

			if(this.ArrHKCode[i][idxCode])
				editStr = "<td width='30%' id='hotkeys-float-form-"+this.ArrHKCode[i][idxCodeId]+"'><a href='javascript:void(0)' onclick='BXHotKeys.SubstInput("+this.ArrHKCode[i][idxCodeId]+", "+
  						this.ArrHKCode[i][idxHKId]+", \""+this.ArrHKCode[i][idxKS]+"\");' title='"+this.MesClToChange+"'>"+keyStr+"</a></td><td width='10%' align='right' id='hotkeys-float-form-del-"+this.ArrHKCode[i][idxCodeId]+"'><a href='javascript:void(0)' onclick='BXHotKeys.DeleteBase("+
  						this.ArrHKCode[i][idxCodeId]+","+this.ArrHKCode[i][idxHKId]+");' class='hk-delete-icon'></a></td>";
			else
				editStr ="<td width='30%'>&nbsp;</td><td width='10%'>&nbsp</td>";

  			formText+="<tr><td width='60%'>"+this.ArrHKCode[i][idxName]+"</td>"+editStr+"</tr>";
  		}

  		formText+='</table>';

		var btnClose = new BX.CWindowButton({
			'title': this.MesClose,
			'action': function() { this.parentWindow.Close(); }
		});

		var btnDefault = new BX.CWindowButton({
			'title': this.MesDefault,
			'action': function() { _this.DelAll(); _this.SetDefault(); }
		});

		var btnDelAll = new BX.CWindowButton({
			'title': this.MesDelAll,
			'action': function() { _this.DelAll(); }
		});



		var obWnd = new BX.CDialog({
						title: this.MesSettings,
						content: formText,
						buttons: [btnClose,btnDefault,btnDelAll],
						width: 500,
						height: 500
					});

		this.tblSettParent=BX("tbl_hk_settings").parentNode;
		BX.addCustomEvent(obWnd, 'onWindowClose', function(obWnd) {
																	_this.tblSettParent.removeChild(BX("tbl_hk_settings"));
																	_this.Register();
															  	});

		obWnd.Show();

		this.Unregister();
	};

	this.DelAll = function()
	{
		_this.deleting = true;

		for(var i=0; i<this.ArrHKCode.length; i++)
		{
			_this.UpdateKS(this.ArrHKCode[i][idxCodeId],"");
			_this.UpdateHk(this.ArrHKCode[i][idxCodeId],0);
			_this.SubstAnch(this.ArrHKCode[i][idxCodeId], 0,"");
			_this.SubstDel(this.ArrHKCode[i][idxCodeId],0);
		}

		var request = new JCHttpRequest;
		var options_url = '/bitrix/admin/hot_keys_act.php?hkaction=delete_all';
		var sParam = "&sessid="+phpVars.bitrix_sessid;
		request.Action = function (result)
		{
			_this.deleting = false;
		}

		request.Post(options_url, sParam);
	}

	this.Register = function()
	{
		jsUtils.addEvent(document, 'keypress', _this.KeyPressHandler);
		jsUtils.addEvent(document, 'keydown', _this.KeyDownHandler);
	}

	this.Unregister = function()
	{
		jsUtils.removeEvent(document, 'keypress', _this.KeyPressHandler);
		jsUtils.removeEvent(document, 'keydown', _this.KeyDownHandler);
	}

	this.SetDefault = function()
	{
		var request = new JCHttpRequest;
		var options_url = '/bitrix/admin/hot_keys_act.php?hkaction=set_default';
		var sParam = "&sessid="+phpVars.bitrix_sessid;

		request.Action = function (strDefHK)
		{
			if(strDefHK)
			{
				if(!strDefHK)
					return false;

				var arHK=[];
				var row="";
				var arStrHK=strDefHK.split(";;");

				for(var i=0; i<arStrHK.length; i++)
				{
					arHK=arStrHK[i].split("::");
					row=_this.UpdateHk(arHK[0],arHK[1]);
					if (row>=0)
					{
						_this.UpdateKS(arHK[0],arHK[2]);
						_this.SubstAnch(arHK[0],arHK[1],arHK[2]);
						_this.SubstDel(arHK[0],arHK[1]);
					}
				}
			}
		}

		//waiting while deleting hot-keys
		waiter =
			{
	   			func: function()
	   			{
	   				if (!(this.deleting))
	   				{
	   					request.Post(options_url, sParam);
	   					clearInterval(intervalID);
	   				}
	   			}
			}
		intervalID = window.setInterval(function(){ waiter.func.call(waiter) }, 1000);
	}

	this.IsKeysBusy = function(strKeyString,code_id)
	{
		for(var i=0; i<this.ArrHKCode.length; i++)
			if (this.ArrHKCode[i][idxKS]==strKeyString && this.ArrHKCode[i][idxCodeId]!=code_id)
		  		return true;

		return false;
	}

	this.SubstInput = function(code_id, hk_id, keysString)
	{

		var td = document.getElementById('hotkeys-float-form-'+code_id);

		if(!td)
			return false;

		td.innerHTML='';
		td.innerHTML = '<input type="text" name="HUMAN_KEYS_STRING" size="10" maxlength="30" value="'+this.PrintKSAsChar(keysString)+'" id="HKeysString" autocomplete="off">'+
						'<input type="hidden" name="KEYS_STRING" value="'+keysString+'" id="KeysString">';

		var inpHKString = document.getElementById("HKeysString");
		var inpKString = document.getElementById("KeysString");

		inpHKString .onkeydown  = _this.SetInput;
		inpHKString .onkeypress = _this.SetInput;
		inpHKString .onkeyup = function ()
		{
			ShowWaitWindow();

			inpHKString .onblur ="";

			if(_this.IsKeysBusy(inpKString.value,code_id))
				if(!confirm(_this.MesBusy))
				{
					_this.SubstAnch(code_id, hk_id, keysString);
					return false;
				}


			_this.bxHotKeyCode=0;

			_this.UpdateKS(code_id,inpKString.value);

			if(hk_id)
			{
				_this.UpdateHk(code_id,hk_id);
				_this.UpdateBase(hk_id,inpKString.value);
			}
			else
				_this.AddBase(code_id,inpKString.value);

			_this.SubstAnch(code_id, hk_id, inpKString.value);

			CloseWaitWindow();
		}

		inpHKString.focus();

		inpHKString.onblur = function ()
		{
			_this.SubstAnch(code_id, hk_id, keysString);
		}
	}

	this.SubstAnch = function(code_id, hk_id, keysString)
	{
		var td = document.getElementById('hotkeys-float-form-'+code_id);
		if(td)
			td.innerHTML = "<a href='javascript:void(0)' onclick='BXHotKeys.SubstInput("+code_id+", "+hk_id+", \""+keysString+"\");' title='"+this.MesClToChange+"'>"+(keysString ? this.PrintKSAsChar(keysString) : this.MesNotAssign)+"</a>";
	}

	this.SubstDel = function(code_id, hk_id)
	{
		var td = document.getElementById('hotkeys-float-form-del-'+code_id);
		if (td)
			td.innerHTML = "<a href='javascript:void(0)' onclick='BXHotKeys.DeleteBase("+code_id+","+hk_id+");' class='hk-delete-icon'></a>";
	}


	this.AddBase = function(code_id,keysString)
	{
		var request = new JCHttpRequest;
		var options_url = '/bitrix/admin/hot_keys_act.php?sessid='+phpVars.bitrix_sessid+'&hkaction=add';
		var sParam = "&KEYS_STRING="+encodeURIComponent(keysString)+"&CODE_ID="+code_id+"&USER_ID="+_this.uid;
		request.Action = function (hk_id)
		{
			if(hk_id)
			{
				var row =_this.UpdateHk(code_id,hk_id);
				if (row>=0)
				{
					_this.SubstAnch(code_id, hk_id,keysString);
					_this.SubstDel(code_id, hk_id);
				}
			}
		}
		request.Post(options_url, sParam);
	}

	this.UpdateBase = function(hk_id, keysString)
	{
		var request = new JCHttpRequest;
		var options_url = '/bitrix/admin/hot_keys_act.php?sessid='+phpVars.bitrix_sessid+'&hkaction=update';
		var sParam = "&KEYS_STRING="+encodeURIComponent(keysString)+"&ID="+hk_id;
		request.Post(options_url, sParam);
	}

	this.DeleteBase = function(code_id, hk_id)
	{
		if(hk_id)
		{
			var request = new JCHttpRequest;
			var options_url = '/bitrix/admin/hot_keys_act.php?sessid='+phpVars.bitrix_sessid+'&hkaction=delete';
			var sParam = "&ID="+hk_id;
			request.Post(options_url, sParam);
			_this.UpdateKS(code_id,"");
			_this.UpdateHk(code_id,0);
			_this.SubstAnch(code_id, 0,"");
			_this.SubstDel(code_id,0);
		}
	}

	this.PrintKSAsChar = function(strKeysString)
	{
		if(!strKeysString)
			return "";

		var lastPlus = strKeysString.lastIndexOf("+");
		if(lastPlus)
		{
			var charCode = strKeysString.substr(lastPlus+1,strKeysString.length - (lastPlus+1));
			var preChar = strKeysString.substr(0,lastPlus+1);
			if(charCode==16 || charCode==17 || charCode==18)
				return preChar.substr(0,preChar.length-1);
		}
		else
		{
			var charCode = strKeysString;
			var preChar = "";
		}

		var codeSymb=arServSymb[charCode];
		if(!codeSymb)
			codeSymb = String.fromCharCode(charCode);

		return preChar+codeSymb;
	}

	this.SetInput = function(e)
	{
		e = e || event;

		var inputDopString = (e.ctrlKey ? 'Ctrl+':'') + (e.altKey ? 'Alt+':'') + (e.shiftKey ? 'Shift+':'');

		if(e.keyCode && e.type!="keypress")
			_this.bxHotKeyCode = e.keyCode;

		var charCode;
		if(e.charCode==undefined)
			charCode = e.which;
		else
			charCode = e.charCode;

		if (charCode && (!_this.bxHotKeyCode || _this.bxHotKeyCode==17 || _this.bxHotKeyCode==18 || _this.bxHotKeyCode==16 || _this.bxHotKeyCode==224))
			_this.bxHotKeyCode = charCode;

		document.getElementById("KeysString").value = inputDopString + _this.bxHotKeyCode;
		document.getElementById("HKeysString").value = _this.PrintKSAsChar(document.getElementById("KeysString").value);
		return false;
	}

	//Key-handlers
	this.KeyPressHandler = function(e)
	{
		e = e || event;

		if(e.charCode > 256)
		{
	   		var ExCode=_this.GetExCode(_this.MakeKeyString(e));

	   		if (ExCode)
				eval(ExCode);
	   	}
	}

	this.KeyDownHandler = function(e)
	{
		e = e || event;

	   	var ExCode=_this.GetExCode(_this.MakeKeyString(e));

	   	if (ExCode)
			eval(ExCode);
	}
}

var BXHotKeys = new CBXHotKeys;

BXHotKeys.Init();
