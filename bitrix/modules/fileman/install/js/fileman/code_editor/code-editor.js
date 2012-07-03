(function(window) {
/*
	arConfig.pTA
*/

window.JCCodeEditor = function(arConfig, MESS)
{
	this.arConfig = arConfig;
	this.MESS = MESS;

	this.pTA = BX(this.arConfig.textareaId);

	this.pTA.removeAttribute("cols");
	this.pTA.removeAttribute("rows");
	this.saveSettings = !!arConfig.saveSettings;

	this.lineNums = 0; // Count of built line nums. Not lines in documet, just built lines
	this.bWrap = true;
	this.bHighlight = true;
	this.arCachedFrags = {};

	this.curLineHighlight = !!arConfig.curLineHighlight;
	this.bitrixHighlight = !!arConfig.bitrixHighlight;

	this.highlightMode = !!arConfig.highlightMode;

	if (this.IsInvalidBrowser())
		this.highlightMode = false;

	this.theme = arConfig.theme;
	this.curSel = {};

	this.lineHeight = 20;
	this.carretWidth = 9;
	this.carretOffset = 55;
	this.maxLineSize = 90;
	this.tabSym = "        "; // 8 spaces;

	this.ss = String.fromCharCode(0x0AF5);
	this.stsym = this.ss + '__';
	this.ensym = '__' + this.ss;
	this.en2sym = this.ss + '_END_' + this.ss;

	this.Syntax = {};
	this.BuildSceleton();
	this.InitEngine();

	if (!this.highlightMode)
	{
		this.highlightMode = true;
		this.SwitchHightlightMode();
	}

	if (this.theme != 'dark')
	{
		this.theme = 'dark';
		this.SwitchTheme();
	}
};

window.JCCodeEditor.prototype = {
	BuildSceleton: function()
	{
		this.pDiv = BX.create("DIV", {props:{className: 'bxce bxce-hls'}});
		//this.pTopCont = this.pDiv.appendChild(BX.create("DIV", {props:{className: 'bxce-top-cont'}}));
		this.pBaseCont = this.pDiv.appendChild(BX.create("DIV", {props:{className: 'bxce-base-cont'}}));

		//this.pTaskBarCont = BX.adjust(this.pBaseRow.insertCell(-1), {props: {className: 'bxce-task-cell'}, style: {display: 'none'}});
		// Foot cont
		this.pFootCont = this.pDiv.appendChild(BX.create("DIV", {props:{className: 'bxce-foot-cont'}}));
		this.pFastLineInp = this.pFootCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-go-to-line-cont'}})).appendChild(BX.create("INPUT", {props:{className: 'bxce-go-to-line', title: this.MESS.GoToLine}}));
		this.pCurPosCont = this.pFootCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-cur-cont'}, html: this.MESS.Line + ":"}));
		this.pInfoCurLine = this.pCurPosCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-cur-line', title: this.MESS.LineTitle}}));
		this.pCurPosCont.appendChild(document.createTextNode(this.MESS.Char + ":"));
		this.pInfoCurChar = this.pCurPosCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-cur-char', title: this.MESS.CharTitle}}));

		this.pTotalCont = this.pFootCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-total-cont'}, html: this.MESS.Total + "  " + this.MESS.Lines + ":"}));
		this.pInfoTotLines = this.pTotalCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-total-line'}}));
		this.pTotalCont.appendChild(document.createTextNode(', ' + this.MESS.Chars + ":"));
		this.pInfoTotChars = this.pTotalCont.appendChild(BX.create("SPAN", {props:{className: 'bxce-info-total-char'}}));

		// Mode toggle
		this.pModeToggle = this.pFootCont.appendChild(BX.create("A", {props:{href: 'javascript:void(0)',className: 'bxce-mode-link' + (this.highlightMode ? ' bxce-mode-link-on' : ''), title: this.MESS.EnableHighlightTitle}, html: '<span class="bxce-mode-txt">' + this.MESS.EnableHighlight + '</span><i></i>'}));
		this.pModeToggle.onclick = BX.proxy(this.SwitchHightlightMode, this);

		// Theme toggle
		this.pThemeToggle = this.pFootCont.appendChild(BX.create("A", {props:{href: 'javascript:void(0)',className: 'bxce-theme-toggle'}, html: '<i></i><span class="bxce-theme-toggle-text">' + this.MESS.LightTheme + '</span>'}));
		this.pThemeToggle.onclick = BX.proxy(this.SwitchTheme, this);
		this.pThemeToggleText = this.pThemeToggle.childNodes[1];

		// Relative div - contains textarea, line numbers, hightlight sections
		this.pCont = this.pBaseCont.appendChild(BX.create("DIV", {props: {className: 'bxce-cont'}}));

		this.pLineNumBg = this.pBaseCont.appendChild(BX.create("DIV", {props: {className: 'bxce-line-num-bg'}}));

		// Line numbers
		this.pLineNum = this.pCont.appendChild(BX.create("DIV", {props: {className: 'bxce-line-num bxce-font'}}));

		// Selection div
		this.pSel = this.pCont.appendChild(BX.create("DIV", {props: {className: 'bxce-sel bxce-font'}}));

		// Hightlight container
		this.pHightlight = this.pCont.appendChild(BX.create("DIV", {props: {className: 'bxce-hightlight bxce-font'}}));
		//this.pScreenH = this.pCont.appendChild(BX.create("DIV", {props: {className: 'bxce-hightlight bxce-scrn-h bxce-font'}}));

		this.pCarretLine = this.pCont.appendChild(BX.create("DIV", {props: {className: 'bxce-cur-line bxce-font'}}));
		this.pCarret = this.pCont.appendChild(BX.create("DIV", {props: {className: 'bxce-cursor bxce-font'}}));
		this.pBogus = this.pCont.appendChild(BX.create("DIV", {props: {className: 'bxce-bogus-text'}}));

		// Adjust textarea
		this.pTA.className = 'bxce-ta bxce-font';
		this.pTA.setAttribute("spellcheck", false);
		//this.pTA.removeAttribute("owerflow");
		//this.pTA.setAttribute("resize", false);
		this.pTA.setAttribute("autocomplete", "off");
		this.pTA.style.overflow = '';

		this.pTA.parentNode.appendChild(this.pDiv);
		this.pCont.appendChild(this.pTA);

		var w, h;
		if (this.arConfig.width)
			w = parseInt(this.arConfig.width);
		if (this.arConfig.height)
			h = parseInt(this.arConfig.height);
		if (this.pCont.parentNode && !w)
			w = parseInt(this.pCont.parentNode.offsetWidth) - 2;
		if (this.pCont.parentNode && !h)
			h = parseInt(this.pCont.parentNode.offsetHeight) - 2;

		if (!w || isNaN(w) || h < 100)
			w = 1000;
		if (!h || isNaN(h) || h < 100)
			h = 400;

		if (BX.browser.IsIE9())
		{
			this.pCarretLine.style.paddingTop = '1px';
		}

		if (BX.browser.IsIE() && !BX.browser.IsIE9())
		{
			if (BX.browser.IsDoctype())
			{
				this.pHightlight.style.left = '54px';
				this.pHightlight.style.top = '-1px';
			}
			else
			{
				this.pHightlight.style.left = '54px';
				this.pHightlight.style.top = '0px';
			}
		}

		this.AdjustSceletonSize(w, h);
	},

	InitEngine: function()
	{
		// Clean content from \r
		this.SetValue();

		this.displayed = true;

		if (this.bWrap)
			this.Wrap(this.bWrap);

		// Bind events
		this.pTA.onmousedown = BX.proxy(this.TextOnMousedown, this);
		this.pTA.onkeyup = BX.proxy(this.TextOnKeyup, this);
		this.pTA.onkeydown = BX.proxy(this.TextOnKeydown, this);

		this.Focus();
		this.pFastLineInp.onfocus = function(){this.select();};
		this.pFastLineInp.onkeydown = BX.proxy(this.FastGoToLine, this);

		this.ManageSize(true);
		setTimeout(BX.proxy(this.ManageSize, this), 100);
	},

	AdjustSceletonSize: function(w, h)
	{
		w = parseInt(w);
		h = parseInt(h);

		var
			topH = 0,
			//numW = 80,
			botH = 20,
			taskW = 0,
			baseH = h - topH - botH,
			baseW = w - taskW;

		this.pBaseCont.style.height = baseH + 'px';
		this.pBaseCont.style.width = baseW + 'px';
		this.pLineNumBg.style.height = baseH + 'px';

		// Number of full lines showed on the screen
		this.baseContHeight = this.pBaseCont.offsetHeight;
		//this.screenLines = Math.floor(this.baseContHeight / this.lineHeight);

		this.pDiv.style.width = w + 'px';
		this.pDiv.style.height = h + 'px';
	},

	ManageSize: function(bCheckLines)
	{
		if(!this.displayed || !this.focused)
			return false;

		var taW, taH;

		//Handle height ! it's important to handle height first
		taH = this.pTA.scrollHeight;

		this.curSel = this.GetSelectionInfo();

		if(this.savedScrollHeight != taH)
			this.ResizeContainerHeight(taH);

		// Handle width
		if(this.bWrap)
		{
			var newW = this.pTA.offsetWidth - 2;
			if(BX.browser.IsSafari())
				newW -= 4;

			this.pHightlight.style.width = newW + "px";
			//this.pScreenH.style.width = newW + "px";
			this.pBogus.style.width = newW + "px";
			this.pCarretLine.style.width = (newW + 6)+ "px";
			//this.selection_field.style.width =
		}
		else
		{
			taW = this.pTA.scrollWidth;

			this.savedScrollWidth = '';
			this.savedScrollHeight = '';

			if(this.savedScrollWidth != taW)
			{
				this.pTA.style.width = taW +"px";
				this.pCont.style.width = taW +"px";
				//this.pScreenH.style.width = taW +"px";
				this.pHightlight.style.width = taW +"px";
				this.savedScrollWidth = taW;
			}
		}

		if (bCheckLines !== false)
			this.CheckLineSelection();

		if(this.curSel.nbLine >= 0)
		{
			var _lines = this.lineNums;
			this.BuildLineNum(this.curSel.nbLine);

			if(this.bWrap)
				this.CheckLinesHeight(this.GetValue(), _lines, false);

			var lastLine = this.GetLinePos(this.curSel.nbLine); // Last line position
			if (lastLine.top + lastLine.height < taH && this.curSel.nbLine < this.lineNums)
				this.ResizeContainerHeight(lastLine.top + lastLine.height);
		}

		//4) be sure the text is well displayed
		this.pTA.scrollTop = "0px";
		this.pTA.scrollLeft = "0px";
		//if(resized)
		//	this.CheckScrollView();

		//if(this.bWrap)
		//	this.CheckLinesHeight(this.pTA.value, this.lineNums, false);
		// Check line selection
		//setTimeout(BX.proxy(this.CheckLineSelection, this), 100);
	},

	ResizeContainerHeight: function(h)
	{
		h = parseInt(h);
		if (!isNaN(h) && h >= 0)
		{
			this.pCont.style.height = (h + 2) + "px";
			this.pTA.style.height = h + "px";
			this.pHightlight.style.height = h + "px";
			this.savedScrollHeight = h;
		}
	},

	Resize: function(w, h)
	{
		w = parseInt(w);
		h = parseInt(h);
		this.AdjustSceletonSize(w, h);
		this.ManageSize(true);
	},

	CheckLinesHeight: function(textValue, lineStart, lineEnd)
	{
		var arStr = textValue.split("\n"), i, s, h, result = false, h0;
		//if(lineEnd === false)
			lineEnd = arStr.length - 1;
		//if(lineStart < 0)
			lineStart = 0;

		for(i = lineStart; i <= lineEnd; i++)
		{
			if(s = this.pLineNum.childNodes[i])
			{
				h = this.GetLineHeight(arStr[i]);
				h0 = parseInt(s.style.height);
				if (h > this.lineHeight || s.style.height != "")
				{
					s.style.height = arStr[i] ? h + "px" : "";
					if (h0 != h) // Now we know that at least one line was changed
						result = true;
				}
			}
		}

		return result;
	},

	// return the height of string (check if it's very long string - correct line height)
	GetLineHeight: function(str)
	{
		var h = this.lineHeight;
		if (str.length < this.maxLineSize && str.indexOf('\t') == -1)
			return this.lineHeight;

		str = str.replace(/&/g,"&amp;").replace(/</g,"&lt;");
		str = this.ReplaceTabSpan(str);

		this.pBogus.innerHTML = str;

		var cnt = Math.floor(this.pBogus.offsetHeight / this.lineHeight);
		if (cnt > 0)
			h = cnt * this.lineHeight;
		return h;
	},

	GetLinePos: function(ind)
	{
		var
			pLine = this.pLineNum.childNodes[ind - 1],
			top = pLine ? pLine.offsetTop : this.lineHeight * (ind - 1),
			height = pLine ? pLine.offsetHeight : this.lineHeight;

		return {
			top: parseInt(top) || 0,
			height: parseInt(height) || 0
		};
	},

	// Add new line nums or hide if count more than needed
	BuildLineNum: function(count)
	{
		count = parseInt(count);
		if (count !== this.lineNums)
		{
			if (count < 1)
				count = 1;

			if (this.lineNums < count)
			{
				for (var i = this.lineNums + 1; i <= count; i++)
					this.pLineNum.appendChild(BX.create("DIV", {html: i}));

				this.lineNums = count;
			}
		}
	},

	Focus: function()
	{
		this.pTA.focus();
		this.focused = true;
	},

	MoveLineSelection: function(bTimeout)
	{
		if (this.checkLineTimeout)
			this.checkLineTimeout = clearTimeout(this.checkLineTimeout);

		if (bTimeout === true)
			this.checkLineTimeout = setTimeout(BX.proxy(this.MoveLineSelection, this), 5);
		else
			this.CheckLineSelection(false);
	},

	CheckLineSelection: function(bChanges)
	{
		if (!this.focused)
			return;

		var
			sel = this.GetSelectionInfo(),
			Diff,
			lp;

		// Content changed
		bChanges = bChanges !== false || this.reload_highlight || sel.source != this.curSel.source;

		if (bChanges)
			Diff = this.GetDiff(typeof this.curSel.source == 'undefined' ? '' : this.curSel.source, sel.source);

		// if selection change
		if(bChanges || this.curSel.linePos != sel.linePos || this.curSel.lineNb != sel.lineNb || this.curSel.selectionStart != sel.selectionStart || this.curSel.selectionEnd != sel.selectionEnd)
		{
			// move and adjust text selection elements
			lp = this.GetLinePos(sel.linePos);
			if (BX.browser.IsIE())
				lp.top--;

			this.pCarretLine.style.top = lp.top + "px";
			this.pCarretLine.style.height = lp.height + "px";

			if (!BX.browser.IsIE() && this.highlightMode)
			{
				var _this = this;
				if (this.checkCarretTimeout)
					this.checkCarretTimeout = clearTimeout(this.checkCarretTimeout);
				this.checkCarretTimeout = setTimeout(function(){_this.CheckCursor(sel, lp);},50);
			}

			if(!this.bWrap)
				this.pCarretLine.style.width = Math.max(this.pTA.scrollWidth, this.pCont.clientWidth - 50) + "px";

			if(this.bHighlight)
			{
				var line = BX.util.htmlspecialchars(sel.curLine);
				if (BX.browser.IsIE())
				{
					line = line.replace(/ /ig, '<tt> </tt>');
					line = this.ReplaceTabSpan(line);
				}

				this.pCarretLine.innerHTML = line;

				if(this.reload_highlight || (sel.source != this.last_text_to_highlight && (this.curSel.linePos != sel.linePos || this.show_line_colors || this.bWrap || this.curSel.lineNb != sel.lineNb || this.curSel.nbLine != sel.nbLine)))				{
					this.Hightlight(sel, Diff);				}
			}
		}

		var bLinesChanged = false;
		if(this.bWrap && bChanges)
		{
			// check only one line if text was changed only in one line and total line count is the same
			if(sel.nbLine === this.curSel.nbLine && Diff.newText.indexOf("\n") == -1)
				bLinesChanged = this.CheckLinesHeight(sel.source, Diff.lineStart, Diff.lineStart);
			else
				bLinesChanged = this.CheckLinesHeight(sel.source, Diff.lineStart, false);
		}

		//if (bLinesChanged || (bChanges && sel.nbLine != this.lineNums))
		if (bLinesChanged || bChanges)
		{
			this.ManageSize(false);
			// move and adjust text selection elements
			lp = this.GetLinePos(sel.linePos);

			if (BX.browser.IsIE9())
			{
			}
			else if (BX.browser.IsIE())
				lp.top--;

			this.pCarretLine.style.top = lp.top + "px";
			this.pCarretLine.style.height = lp.height + "px";
		}

		this.curSel = sel;
	},

	// Sel - current selection, lp - line position
	CheckCursor: function(sel, lp)
	{
		var top = 0, left = 0;
		// Do nothing - IE has bright cursor and we don't need to hightlight it
		if (BX.browser.IsIE())
			return;

		var html = this.pCarretLine.innerHTML;
		// 1. New line
		if (html == '')
		{
			top = lp.top;
			left = this.carretOffset;
		}
		else
		{
			html = this.pCarretLine.innerHTML.replace('<span id="__bx_bogus_span__"></span>', '');
			html = BX.util.htmlspecialcharsback(html);
			html = html.replace('&nbsp;', ' ');

			var bogusChar = String.fromCharCode(0x0AF6);
			html = BX.util.htmlspecialchars(html.substr(0, sel.carretPos - 1)) + bogusChar + BX.util.htmlspecialchars(html.substr(sel.carretPos - 1));
			html = this.ReplaceTabSpan(html, bogusChar);
			html = html.replace(bogusChar, '<span id="__bx_bogus_span__"></span>');

			this.pCarretLine.innerHTML = html;
			var bogusSpan = BX('__bx_bogus_span__');
			if (bogusSpan)
			{
				if (BX.browser.IsSafari())
					top = parseInt(bogusSpan.offsetTop) + lp.top;
				else
					top = parseInt(bogusSpan.offsetTop) + lp.top - 16;
				left = this.carretOffset + parseInt(bogusSpan.offsetLeft);
			}
		}

		this.pCarret.style.top = top + "px";
		this.pCarret.style.left = left + "px";
	},

	GetSelectionInfo: function()
	{
		var
			taSel = this.GetTASelection(),
			start = taSel.start,
			end = taSel.end,
			str;

		var
			sel = {
				selectionStart: start,
				selectionEnd: end,
				source: this.GetValue(),
				linePos: 1,
				lineNb: 1,
				carretPos: 0,
				curLine: "",
				cursorIndex: 0,
				direction: this.curSel.direction
			},
			splitTab = sel.source.split("\n"),
			nbLine = splitTab.length,
			nbChar = sel.source.length - (nbLine - 1);

		if (nbChar < 0)
			nbChar = 0;

		sel.nbLine = nbLine;
		sel.nbChar = nbChar;

		if(start > 0)
		{
			str = sel.source.substr(0,start);
			sel.carretPos = start - str.lastIndexOf("\n");
			sel.linePos = str.split("\n").length;
		}
		else
		{
			sel.carretPos = 1;
		}

		if (sel.linePos < 1)
			sel.linePos = 1;

		if(end > start)
			sel.lineNb = sel.source.substring(start, end).split("\n").length;

		sel.cursorIndex = start;
		sel.curLine = splitTab[sel.linePos - 1];

		// Check selection direction
		if(sel.selectionStart == this.curSel.selectionStart)
		{
			if(sel.selectionEnd > this.curSel.selectionEnd)
				sel.direction = "down";
			else //if(sel.selectionEnd == this.curSel.selectionStart)
				sel.direction = this.curSel.direction;
		}
		else if(sel.selectionStart == this.curSel.selectionEnd && sel.selectionEnd > this.curSel.selectionEnd)
		{
			sel.direction = "down";
		}
		else
		{
			sel.direction = "up";
		}


		this.SetStatusBarInfo(sel);

		return sel;
	},

	SetStatusBarInfo: function(sel)
	{
		this.pInfoCurLine.innerHTML = sel.linePos;
		this.pInfoCurChar.innerHTML = sel.carretPos;
		this.pInfoTotLines.innerHTML = sel.nbLine;
		this.pInfoTotChars.innerHTML = sel.nbChar;
		this.pFastLineInp.value = sel.linePos;
	},

	GetDiff: function(lastText, newText)
	{
		// ch will contain changes data
		var
			ch = {},
			baseStep = 200,
			cpt = 0,
			end = newText.length,
			step  = baseStep;

		if (end > lastText.length)
			end = lastText.length;

		// find how many chars are similar at the begin of the text
		while(cpt < end && step >= 1)
		{
			if(lastText.substr(cpt, step) == newText.substr(cpt, step))
				cpt += step;
			else
				step = Math.floor(step / 2);
		}

		ch.posStart = cpt;
		ch.lineStart = newText.substr(0, ch.posStart).split("\n").length -1;

		var cpt_last = lastText.length;
		cpt = newText.length;
		step = baseStep;

		// find how many chars are similar at the end of the text
		while(cpt >= 0 && cpt_last >= 0 && step>=1)
		{
			if(lastText.substr(cpt_last-step, step) == newText.substr(cpt-step, step))
			{
				cpt -= step;
				cpt_last -= step;
			}
			else
			{
				step= Math.floor(step/2);
			}
		}

		ch.posNewEnd = cpt;
		ch.posLastEnd = cpt_last;
		if(ch.posNewEnd <= ch.posStart)
		{
			if(lastText.length < newText.length)
			{
				ch.posNewEnd = ch.posStart + newText.length - lastText.length;
				ch.posLastEnd = ch.posStart;
			}
			else
			{
				ch.posLastEnd = ch.posStart + lastText.length - newText.length;
				ch.posNewEnd = ch.posStart;
			}
		}
		ch.newText = newText.substring(ch.posStart, ch.posNewEnd);
		ch.lastText = lastText.substring(ch.posStart, ch.posLastEnd);

		ch.lineNewEnd = newText.substr(0, ch.posNewEnd).split("\n").length -1;
		ch.lineLastEnd = lastText.substr(0, ch.posLastEnd).split("\n").length -1;

		ch.newTextLine = newText.split("\n").slice(ch.lineStart, ch.lineNewEnd + 1).join("\n");
		ch.lastTextLine = lastText.split("\n").slice(ch.lineStart, ch.lineLastEnd + 1).join("\n");

		return ch;
	},

	Wrap: function(bWrap)
	{
		var mode;

		if(bWrap)
		{
			mode = 'soft';
			BX.addClass(this.pCont, 'bxce-wrap');
			this.pCont.style.width = "";
			this.pHightlight.style.width = "";
			this.pTA.style.width = "100%";
		}
		else
		{
			mode = 'off';
			BX.removeClass(this.pCont, 'bxce-wrap');
		}

		this.pTA.wrap = mode;
		this.pTA.setAttribute('wrap', mode);

		// For all browsers exept IE we have to rebuild textarea
		if(!BX.browser.IsIE())
		{
			var
				start = this.pTA.selectionStart,
				end= this.pTA.selectionEnd;

			this.pCont.removeChild(this.pTA);
			this.pCont.appendChild(this.pTA);
			this.Select(start, end - start);
		}

		this.bWrap = bWrap;
		this.Focus();

		this.savedScrollWidth = '';
		this.savedScrollHeight = '';
	},

	Select: function(start, len)
	{
		this.pTA.focus();
		var l = this.pTA.value.length;
		if (start < 0)
			start = 0;
		if (start > l)
			start = l;
		var end = start + len;
		if (end > l)
			end = l;

		if(BX.browser.IsIE())
			this.SetIESelection(start, end);
		else
			this.pTA.setSelectionRange(start, end);
	},

	GetSelection: function()
	{
		var text = "";
		if(document.selection)
			text = document.selection.createRange().text;
		else
			text = this.pTA.value.substring(this.pTA.selectionStart, this.pTA.selectionEnd);
		return text;
	},

	GetTASelection: function()
	{
		var start = 0, end = 0;

		if (BX.browser.IsIE9())
		{
			start = this.pTA.selectionStart;
			end = this.pTA.selectionEnd;
		}
		else if (BX.browser.IsIE())
		{
			var ch = "\001";
			var range = document.selection.createRange();
			var savedText = range.text.replace(/\r/g, "");
			var dubRange = range.duplicate();
			if (savedText != '')
				range.collapse();
			dubRange.moveToElementText(this.pTA);
			range.text = ch;
			end = start = dubRange.text.replace(/\r/g, "").indexOf(ch);
			range.moveStart('character', -1);
			range.text = "";

			if (savedText != '')
				end += savedText.length;
		}
		else
		{
			start = this.pTA.selectionStart;
			end = this.pTA.selectionEnd;
		}

		return {start: start, end: end};
	},

	SetIESelection: function(start, end)
	{
		var
			val = this.GetValue(),
			nbLineStart = val.substr(0, start).split("\n").length - 1,
			nbLineEnd = val.substr(0, end).split("\n").length - 1,
			range = document.selection.createRange();

		start += nbLineStart;
		end += nbLineEnd;

		range.moveToElementText(this.pTA);
		range.setEndPoint('EndToStart', range);

		range.moveStart('character', start - nbLineStart);
		range.moveEnd('character', end - nbLineEnd - (start - nbLineStart));
		range.select();
	},

	GoToLine: function(line)
	{
		var
			start = 0,
			lines = this.GetValue().split("\n"),
			linesCount = lines.length;

		if(line === 'last')
			line = lines.length + 1;

		line = parseInt(line);
		if (isNaN(line))
			return;

		if(line > lines.length)
		{
			start = this.GetValue().length;
		}
		else
		{
			var i;
			if (line < linesCount / 2) // Calculate position from top
			{
				for(i = 0; i < line - 1; i++)
					start += lines[i].length + 1;
			}
			else // Calculate position from bottom
			{
				for(i = linesCount - 1; i > line - 1; i--)
					start += lines[i].length + 1;
				start = this.GetValue().length - start;
			}
		}
		this.Select(start, 0);
		this.MoveLineSelection();
		return start;
	},

	TextOnKeydown: function(e)
	{
		if(!e)
			e = window.event;

		var key = e.which || e.keyCode;

		if (key == 37 || key == 38 || key == 39 || key == 40) // Left, Up, Right, Down
		{
			return this.MoveLineSelection(true);
		}
		else if (key == 33 || key == 34) // 33 - PageUp, 34 - PageDown
		{
			this.OnPageScroll(e, key == 33);
			return BX.PreventDefault(e);
		}
		else if ((key == 35 || key == 36) && this.CtrlPressed(e)) // 35 - End, 36 - Home
		{
			var line = this.GoToLine(key == 36 ? 0 : 'last');
			// Strange browser's behavior while pressed Ctrl+End/Ctrl+Home
			this.pBaseCont.scrollTop = key == 36 ? 0 : this.GetLinePos(line).top;
			return BX.PreventDefault(e);
		}
		else if(key == 9) // Tab
		{
			this.OnTab(e, this.ShiftPressed(e));
			return BX.PreventDefault(e);
		}
		else if(key == 13) // Enter
		{
			//this.OnEnter(e);
		}
		else if(key == 27) // Esc
		{

		}
	},

	ShiftPressed: function(e)
	{
		if (window.event)
			return !!window.event.shiftKey;
		return !!(e.shiftKey || e.modifiers > 3);
	},

	CtrlPressed: function(e)
	{
		if (window.event)
			return !!window.event.ctrlKey;
		return !!e.ctrlKey;
	},

	TextOnKeyup: function(e)
	{
		if(!e)
			e = window.event;

		var key = e.which || e.keyCode;

		var k1 = {37: true, 38 : true, 39: true, 40: true};
		if (k1[key]) // Left, Up, Right, Down
			return this.MoveLineSelection(false);

		var k2 = {
			17: true, //Ctrl
			18 : true, // Alt
			9: true, // tab
			27: true // esc
		};

		if (!k2[key])
			this.CheckLineSelection();
	},

	TextOnMousedown: function(e)
	{
		// if(!e)
			// e = window.event;
		this.MoveLineSelection(true);
	},

	OnEnter: function()
	{
		return false;
	},

	// Scroll page Up or Down
	OnPageScroll: function(e, bUp)
	{
		var
			sel = this.GetSelectionInfo(),
			k = bUp ? -1 : 1,
			curLinePos = this.GetLinePos(sel.linePos),
			scrollTop = this.pBaseCont.scrollTop,
			ssT = curLinePos.top - scrollTop, // Start screen top
			esT = ssT, // End screen top
			maxH = this.baseContHeight - this.lineHeight * 2,
			line = sel.linePos,
			h = ssT - esT,
			pos;

		while (true)
		{
			pos = this.GetLinePos(line);
			h += pos.height;
			line = line + k;

			if (h > maxH || line <= 0 || line > this.lineNums)
				break;
		}
		this.pBaseCont.scrollTop = this.pBaseCont.scrollTop + h * k;
		this.GoToLine(line);
	},

	OnTab: function(e, bShift)
	{
		if(this.bDisableTab)
			return;

		if (BX.browser.IsIE())
			this.bDisableTab = true;

		var
			tab = "\t",
			taSel = this.GetTASelection(),
			from = taSel.start,
			to = taSel.end,
			source = this.GetValue(),
			txt = source.substring(from, to),
			posFrom = from,
			posTo = to;

		if (!bShift) // Insert TABulation
		{
			if (txt == "") // One line
			{
				source = source.substr(0, from) + tab + source.substr(to);
				posFrom = from + 1;
				posTo = posFrom;
			}
			else
			{
				from = source.substr(0, from).lastIndexOf("\n") + 1;
				if (from <= 0)
					from = 0;
				endText = source.substr(to);
				startText = source.substr(0, from);
				tmp = source.substring(from, to).split("\n");
				txt = tab + tmp.join("\n" + tab);
				source = startText + txt + endText;
				posFrom = from;
				posTo= source.indexOf("\n", startText.length + txt.length);

				if(posTo == -1)
					posTo = source.length;
			}
		}
		else // Remove TABulation
		{
			if (txt == "") // One line
			{
				if (from <= 0)
					from = 1;
				if(source.substring(from - 1, from) == tab)
				{
					source = source.substr(0, from - 1) + source.substr(to);
					posFrom = posTo = from - 1;
				}
			}
			else
			{
				from = source.substr(0, from).lastIndexOf("\n") + 1;
				endText = source.substr(to);
				startText = source.substr(0, from);
				tmp = source.substring(from, to).split("\n");
				txt = "";
				for(i = 0; i < tmp.length; i++)
				{
					for(j = 0, l = this.tabSym.length; j < l; j++)
					{
						if(tmp[i].charAt(0) == tab)
						{
							tmp[i] = tmp[i].substr(1);
							j = l;
						}
					}
					txt += tmp[i];
					if(i < tmp.length - 1)
						txt += "\n";
				}

				source = startText + txt + endText;
				posFrom = from;
				posTo = source.indexOf("\n", startText.length + txt.length);
				if(posTo == -1)
					posTo = source.length;
			}
		}

		this.SetValue(source);

		if (BX.browser.IsIE())
		{
			var _this = this;
			this.SetIESelection(posFrom, posTo);
			setTimeout(function(){_this.bDisableTab = false;}, 80);
		}
		else
		{
			this.pTA.selectionStart = posFrom;
			this.pTA.selectionEnd = posTo;
		}

		this.CheckLineSelection(true);
	},

	Hightlight: function(sel, diff, bTimeout)
	{
		if (!this.highlightMode)
			return;

		//BX.addClass(this.pDiv, "bxce-fast-editing");

		//bTimeout = false;
		bTimeout = bTimeout !== false;

		if (this.hightlightTimeout)
			this.hightlightTimeout = clearTimeout(this.hightlightTimeout);

		if (this.checkHightlightTimeout)
			this.checkHightlightTimeout = clearTimeout(this.checkHightlightTimeout);

		var _this = this;
		if (bTimeout)
		{
			this.hightlightTimeout = setTimeout(function(){_this.Hightlight(sel, diff, false);}, 20);
			return;
		}

		if (!sel)
			sel = this.GetSelectionInfo();
		if (!diff)
			diff = this.GetDiff(typeof this.curSel.source == 'undefined' ? '' : this.curSel.source, sel.source);

		var t1 = new Date().getTime();
		var
			textToHighlight = sel.source,
			doSyntaxOpti = false,
			doHtmlOpti = false,
			beforeSrc = "",
			afterSrc = "",
			trace_new ,
			trace_last;

		if(this.last_text_to_highlight == sel.source)
			return;

		//OPTIMISATION: will search to update only changed lines
		// if(this.reload_highlight === true)
		// {
			// this.reload_highlight = false;
		// }
		// else if(textToHighlight == "")
		// {
			// textToHighlight = "\n ";
		// }

		//var doSyntaxOpti = true;
		//if (!this.last_hightlighted_text || this.bReloadHightlight === true)
			doSyntaxOpti = false;

		// if (this.bPrintToScreen)
		// {
			// var
				// bound1 = diff.lineStart - this.screenLines,
				// bound2 = diff.lineLastEnd + this.screenLines;
			// if (bound1 < 0)
				// bound1 = 0;
			// if (bound2 > this.linesCount)
				// bound2 = this.linesCount;

			// var beforeSrc1 = this.last_hightlighted_text.split("\n").slice(bound1, diff.lineStart).join("\n");
			// var afterSrc1 = this.last_hightlighted_text.split("\n").slice(diff.lineLastEnd, bound2).join("\n");
		// }

		if (doSyntaxOpti && false)
		{
			var trace_new = this.get_syntax_trace(diff.newTextLine).replace(/\r/g, '');
			var trace_last = this.get_syntax_trace(diff.lastTextLine).replace(/\r/g, '');
			doSyntaxOpti = (trace_new == trace_last);

			// check if the difference comes only from a new line created
			// => we have to remember that the editor can automaticaly add tabulation or space after the new line)
			//if(!doSyntaxOpti && trace_new == "\n" + trace_last && /^[ \t\s]*\n[ \t\s]*$/.test(diff.newText.replace(/\r/g, '')) && diff.lastText == "" )
			// {
				// doSyntaxOpti = true;
			// }

			if (!doSyntaxOpti && trace_new == "\n" + trace_last)
				doSyntaxOpti = true;

			if (!doSyntaxOpti && "\n" + trace_new == trace_last)
				doSyntaxOpti = true;

			if(doSyntaxOpti)
			{
				beforeSrc = this.last_hightlighted_text.split("\n").slice(0, diff.lineStart).join("\n");
				if(diff.lineStart > 0)
					beforeSrc += "\n";

				afterSrc = this.last_hightlighted_text.split("\n").slice(diff.lineLastEnd + 1).join("\n");

				if(afterSrc.length>0)
					afterSrc = "\n" + afterSrc;

				// Final check to see that we're not in the middle of span tags
				if( beforeSrc.split('<span').length != beforeSrc.split('</span').length || afterSrc.split('<span').length != afterSrc.split('</span').length )
				{
					doSyntaxOpti = false;
					afterSrc	 = '';
					beforeSrc	 = '';
				}
				else
				{
					if(beforeSrc.length == 0 && diff.posLastEnd == -1)
						diff.newTextLine += "\n";
					textToHighlight = diff.newTextLine;
				}
			}
		}
		var t2 = new Date().getTime();

		// apply highlight
		textToHighlight = " " + textToHighlight;
		//res = res.replace(/ /ig, '&nbsp;');

		// Split text to HTML, PHP and JS
		var arFrag = this.ParseToFragments(textToHighlight);

		var res = '', i, str, parsed, cachedFrag;
		for (i = 0; i < arFrag.length; i++)
		{
			str = arFrag[i].str;

			cachedFrag = this.CheckCachedFragment(arFrag[i], i);
			if (cachedFrag !== false)
			{
				parsed = cachedFrag;
			}
			else
			{
				if (arFrag[i].type == 'text')
				{
					parsed = str;
				}
				else
				{
					// 1. Check if syntax inited for this type
					if (!this.Syntax[arFrag[i].type])
						this.InitSyntax(arFrag[i].type);

					// 2. Apply syntax for text
					parsed = this.ApplySyntax(str, this.Syntax[arFrag[i].type]);
				}
			}

			res += parsed;
			this.CacheFragment(arFrag[i], i, parsed);
		}

		res = res.substr(1);

		res = res.replace(/&/g,"&amp;");
		res = res.replace(/</g,"&lt;");
		res = res.replace(/>/g,"&gt;");

		res = res.replace(new RegExp(this.en2sym, 'g'),"</tt>");
		res = res.replace(new RegExp(this.stsym + '([a-zA-Z0-9]+)' + this.ensym, 'g'),"<tt class='$1'>");



		if (BX.browser.IsIE())
		{
			//res = res.replace(/ /ig, '&nbsp;');

			res = this.ReplaceTabSpan(res);
			//res = res.replace(/\t/ig, '<tt class="bxce-ie-tabspan">&#9;</tt>');
			res = res.replace(/\n/ig, '<br />');
		}
		else
		{
			res = this.ReplaceTabSpan(res);
			//res = res.replace(/\t/ig, '<tt class="bxce-tabspan"></tt>');
		}

		//if (this.bPrintToScreen)
		//	res = beforeSrc + res + afterSrc1;
		//else
		//	res = beforeSrc + res + afterSrc;

		//this.bPrintToScreen = false;
		//if (this.bPrintToScreen)
		//	var _pScreenH = this.pScreenH.cloneNode(false);

		var _pHightlight = this.pHightlight.cloneNode(false);

//		if(BX.browser.IsIE() && !BX.browser.IsIE9())
//		{
//			//if (this.bPrintToScreen)
//			//	_pScreenH.innerHTML= "<pre>" + res + "</pre>";
//			//else
//			//	_pHightlight.innerHTML= "<pre>" + res + "</pre>";
//				_pHightlight.innerHTML= res;
//		}
//		else
//		{
//		}
		// if (this.bPrintToScreen)
		// _pScreenH.innerHTML = res;
		// else
		_pHightlight.innerHTML = res;

		// if (this.bPrintToScreen)
		// {
			// this.pScreenH.parentNode.replaceChild(_pScreenH, this.pScreenH);
			// this.pScreenH = _pScreenH;

			// this.pHightlight.style.display = "none";
		// }
		// else
		{
			this.pHightlight.parentNode.replaceChild(_pHightlight, this.pHightlight);
			this.pHightlight = _pHightlight;
		}

		//BX.removeClass(this.pDiv, "bxce-fast-editing");

		this.last_text_to_highlight = sel.source;
		this.last_hightlighted_text = res;

		// if (doSyntaxOpti || this.bPrintToScreen)
		// {
			// this.checkHightlightTimeout = setTimeout(function()
			// {
				// this.checkHightlightTimeout = setTimeout(function()
				// {
					// _this.bPrintToScreen = false;
					// _this.bReloadHightlight = true;
					// _this.Hightlight(false, false, false);
				// }, 1000);
			// }, 5);
		// }

		//this.bPrintToScreen = true;
		//this.bReloadHightlight = false;
	},

	CheckCachedFragment: function(frag, ind)
	{
		if (this.arCachedFrags[ind] && this.arCachedFrags[ind].type == frag.type && this.arCachedFrags[ind].str == frag.str)
			return this.arCachedFrags[ind].parsed;
		return false;
	},

	CacheFragment: function(frag, ind, parsed)
	{
		this.arCachedFrags[ind] = frag;
		this.arCachedFrags[ind].parsed = parsed;
	},

	ParseToFragments: function(text)
	{
		var result = [], arPHP = [], arJS = [];
		// 1. Replace PHP by ~~BX`PHP~
		text = text.replace(/<\?((?:\s|\S)*?)\?>/ig, function(s){arPHP.push(s);return "~~BX`PHP~";});

		// 2. Replace Javascript by ~~BX`JS~
		text = text.replace(/<script[^>]*?>((?:\s|\S)*?)<\/script>/ig, function(s){arJS.push(s);return "~~BX`JS~";});

		var phpInd = 0, jsInd = 0, i, str, start = 0, prefLen = 5;//'~~BX`'.length

		if (this.arConfig.forceSyntax)
		{
			result.push({type: this.arConfig.forceSyntax, str: text});
		}
		else
		{
			while(true)
			{
				i = text.indexOf('~~BX`', start);
				if (i == -1) // text does'n contain any php or js
				{
					str = text.substr(start);
					if (this.arConfig.forceSyntax)
						result.push({type: this.arConfig.forceSyntax, str: str});
					else
						result.push({type: this._NeedToCheckHtml(str) ? 'html' : 'text', str: str});

					break;
				}
				else
				{
					if (i != start)
					{
						str = text.substr(start, i - start);
						if (this.arConfig.forceSyntax)
							result.push({type: this.arConfig.forceSyntax, str: str});
						else
							result.push({type: this._NeedToCheckHtml(str) ? 'html' : 'text', str: str});
					}

					if (text.substr(i + prefLen, 3) === 'PHP') // PHP
					{
						result.push({type: 'php', str: arPHP[phpInd]});
						phpInd++;
						start = i + prefLen + 4; // PHP~ + 1 char
					}
					else // JS
					{
						result.push({type: 'js',str: arJS[jsInd]});
						jsInd++;
						start = i + prefLen + 3; // JS~ + 1 char
					}
				}
			}
		}

		return result;
	},

	ApplySyntax: function(text, oSynt)
	{
		if (text == undefined || text == '')
			return '';

		var _this = this, i, convert;

		if(oSynt.custom_regexp && oSynt.custom_regexp.before)
		{
			for(i in oSynt.custom_regexp.before)
			{
				if (oSynt.custom_regexp.before[i] && oSynt.custom_regexp.before[i].className)
				{
					convert = "$1" + this.stsym + oSynt.custom_regexp.before[i].className + this.ensym + "$2" + this.en2sym + "$3";
					text = text.replace(oSynt.custom_regexp.before[i]['regexp'], convert);
				}
			}
		}

		if(oSynt.comment_or_quote_reg_exp)
			text = text.replace(oSynt.comment_or_quote_reg_exp, function(str){return _this.ParseString(str, oSynt);});

		if(oSynt.keywords_reg_exp)
		{
			for(i in oSynt.keywords_reg_exp)
				text= text.replace(oSynt.keywords_reg_exp[i], this.stsym + i + this.ensym + '$2' + this.en2sym);
		}

		if(oSynt.operators_reg_exp)
			text= text.replace(oSynt.operators_reg_exp, this.stsym + 'operators' + this.ensym + '$1' + this.en2sym);

		if (oSynt.custom_regexp && oSynt.custom_regexp.after)
		{
			for(i in oSynt.custom_regexp.after)
			{
				if (oSynt.custom_regexp.after[i] && oSynt.custom_regexp.after[i].className)
				{
					convert="$1" + this.stsym + oSynt.custom_regexp.after[i].className + this.ensym + "$2" + this.en2sym + "$3";
					text = text.replace(oSynt.custom_regexp.after[i]['regexp'], convert);
				}
			}
		}

		return text;
	},

	BuildRegExp: function(text_array)
	{
		var res="(\\b)(", i;
		for(i = 0; i < text_array.length; i++)
		{
			if(i > 0)
				res += "|";
			res += BX.util.preg_quote(text_array[i]);
		}
		res += ")(\\b)";
		return res;
	},

	InitSyntax: function(syntaxType)
	{
		var
			param, i,
			syntaxDesc = this.GetSyntaxDescription(syntaxType),
			oSynt = {};

		oSynt.keywords_reg_exp = {};
		this.keywords_reg_exp_nb = 0;

		if(syntaxDesc['KEYWORDS'])
		{
			param = "ig";
			for(i in syntaxDesc['KEYWORDS'])
			{
				if(typeof(syntaxDesc['KEYWORDS'][i]) == "function")
					continue;
				oSynt["keywords_reg_exp"][i]= new RegExp(this.BuildRegExp(syntaxDesc['KEYWORDS'][i]), param);
				this.keywords_reg_exp_nb++;
			}
		}

		if(syntaxDesc['OPERATORS'])
		{
			var str = "", nb = 0;
			for(i in syntaxDesc['OPERATORS'])
			{
				if(nb > 0)
					str += "|";
				str += BX.util.preg_quote(syntaxDesc['OPERATORS'][i]);
				nb++;
			}
			if(str.length>0)
				oSynt["operators_reg_exp"]= new RegExp("("+str+")","g");
		}

//		/(("(\\"|[^"])*"?)|('(\\'|[^'])*'?)|(//(.|\r|\t)*\n)|(/\*(.|\n|\r|\t)*\*/)|(<!--(.|\n|\r|\t)*-->))/gi
		var syntax_trace = [];

//		/("(?:[^"\\]*(\\\\)*(\\"?)?)*("|$))/g

		oSynt._regexp = {};
		oSynt.quotes = {};
		var quote_tab = [], x;
		if(syntaxDesc['QUOTEMARKS'])
		{
			for(i in syntaxDesc['QUOTEMARKS'])
			{
				if(typeof(syntaxDesc['QUOTEMARKS'][i])=="function")
					continue;
				x = BX.util.preg_quote(syntaxDesc['QUOTEMARKS'][i]);
				oSynt.quotes[x] = x;
				oSynt._regexp[x] = new RegExp(BX.util.preg_quote(x) + "$", "m");
				//quote_tab.push("(" + x + "(\\\\.|[^" + x + "])*(?:" + x + "|$))");
				quote_tab.push("(?:" + x + "(?:\\\\.|[^" + x + "])*(?:" + x + "|$))");
				syntax_trace.push(x);
			}
		}

		oSynt.comments = {};
		if(syntaxDesc['COMMENT_SINGLE'])
		{
			for(i in syntaxDesc['COMMENT_SINGLE'])
			{
				if(typeof(syntaxDesc['COMMENT_SINGLE'][i])=="function")
					continue;

				x = BX.util.preg_quote(syntaxDesc['COMMENT_SINGLE'][i]);
				quote_tab.push("(?:" + x + "(?:.|\\r|\\t)*(?:\\n|$))");
				syntax_trace.push(x);
				oSynt.comments[syntaxDesc['COMMENT_SINGLE'][i]] = "\n";
			}
		}
		// (/\*(.|[\r\n])*?\*/)
		if(syntaxDesc['COMMENT_MULTI'])
		{
			for(i in syntaxDesc['COMMENT_MULTI'])
			{
				if(typeof(syntaxDesc['COMMENT_MULTI'][i])=="function")
					continue;
				var start = BX.util.preg_quote(i);
				var end = BX.util.preg_quote(syntaxDesc['COMMENT_MULTI'][i]);
				//quote_tab.push("(" + start + "(.|\\n|\\r)*?(" + end + "|$))");
				quote_tab.push("(?:" + start + "(?:.|\\n|\\r)*?(?:" + end + "|$))");
				syntax_trace.push(start);
				syntax_trace.push(end);
				oSynt.comments[i] = syntaxDesc['COMMENT_MULTI'][i];
				oSynt._regexp[i] = new RegExp(BX.util.preg_quote(oSynt.comments[i]) + "$", "m");
			}
		}

		if(quote_tab.length > 0)
			oSynt.comment_or_quote_reg_exp = new RegExp("(" + quote_tab.join("|")+")", "gi");

		if(syntax_trace.length > 0) //   /((.|\n)*?)(\\*("|'|\/\*|\*\/|\/\/|$))/g
			oSynt.syntax_trace_regexp = new RegExp("(?:(?:.|\n)*?)(\\\\*(?:" + syntax_trace.join("|") +"|$))", "gmi");

		if(syntaxDesc['SCRIPT_DELIMITERS'])
		{
			oSynt["script_delimiters"] = {};
			for(i in syntaxDesc['SCRIPT_DELIMITERS'])
			{
				if(typeof(syntaxDesc['SCRIPT_DELIMITERS'][i]) == "function")
					continue;
				oSynt["script_delimiters"][i]= syntaxDesc['SCRIPT_DELIMITERS'];
			}
		}

		oSynt["custom_regexp"]= {};
		if(syntaxDesc['REGEXPS'])
		{
			for(i in syntaxDesc['REGEXPS'])
			{
				if(typeof(syntaxDesc['REGEXPS'][i])=="function")
					continue;

				var val= syntaxDesc['REGEXPS'][i];
				if(!oSynt["custom_regexp"][val['execute']])
					oSynt["custom_regexp"][val['execute']]= {};

				oSynt["custom_regexp"][val['execute']][i]=
				{
					'regexp' : new RegExp(val['search'], val['modifiers']),
					'className' : val.className
				};
			}
		}

		this.Syntax[syntaxType] = oSynt;
	},

	// determine if the selected text if a comment or a quoted text
	ParseString : function(s, oSynt)
	{
		var
			reg,
			new_class = '',
			close_tag = '',
			i;

		for(i in oSynt.quotes)
		{
			//if(s.indexOf(i) == 0)
			if(s.substr(0, 1) === i)
			{
				new_class = 'quotesmarks';
				close_tag = oSynt.quotes[i];
				reg = oSynt._regexp[i];
				break;
			}
		}

		if(new_class === '')
		{
			for(i in oSynt.comments)
			{
				if(s.indexOf(i) == 0)
				{
					new_class = "comments";
					close_tag = oSynt.comments[i];
					reg = oSynt._regexp[i];
					break;
				}
			}
		}

		// for single line comment the \n must not be included in the span tags
		if(close_tag == "\n")
			return this.stsym + new_class + this.ensym + s.replace(/(\n)?$/m, this.en2sym + "$1");

		if(s.search(reg) === -1)
			return this.stsym + new_class + this.ensym + s;

		return this.stsym + new_class + this.ensym + s + this.en2sym;
	},

	FastGoToLine: function(e)
	{
		if(!e)
			e = window.event;

		var key = e.which || e.keyCode;
		if (key == 13)
		{
			this.GoToLine(this.pFastLineInp.value);
			return BX.PreventDefault(e);
		}
	},

	GetTab: function()
	{
		return "\t";
	},

	GetValue: function()
	{
		return this.pTA.value.replace(/\r/g, "");
	},

	SetValue: function(value)
	{
		if (value == undefined)
			value = this.GetValue();
		else
			value = value.replace(/\r/g, "");

		this.pTA.value = value;
	},

	SwitchHightlightMode: function()
	{
		this.highlightMode = !this.highlightMode;
		if (this.highlightMode)
		{
			BX.addClass(this.pModeToggle, 'bxce-mode-link-on');
			BX.addClass(this.pDiv, 'bxce-hls');

			if (this.IsInvalidBrowser())
				alert(this.MESS.HighlightWrongwarning);
		}
		else
		{
			BX.removeClass(this.pModeToggle, 'bxce-mode-link-on');
			BX.removeClass(this.pDiv, 'bxce-hls');
		}

		this.SaveUserOption('highlight', this.highlightMode ? 1 : 0);
		BX.focus(this.pTA);
	},

	SwitchTheme: function()
	{
		if (this.theme == 'dark')
		{
			this.theme = 'light';
			BX.addClass(this.pDiv, 'bxce--light');
			this.pThemeToggleText.innerHTML = this.MESS.DarkTheme;
		}
		else
		{
			BX.removeClass(this.pDiv, 'bxce--light');
			this.theme = 'dark';
			this.pThemeToggleText.innerHTML = this.MESS.LightTheme;
		}

		this.SaveUserOption('theme', this.theme);
		BX.focus(this.pTA);
	},

	_NeedToCheckHtml: function(str)
	{
		return str.indexOf('<') != -1 || str.indexOf('<') != -1;
	},

	SaveUserOption: function(option, value)
	{
		if (this.saveSettings)
			BX.userOptions.save('fileman', 'code_editor', option, value);
	},

	GetSyntaxDescription: function(syntaxType)
	{
		return bxce_syntax[syntaxType] || {};
	},

	IsInvalidBrowser: function()
	{
		return BX.browser.IsOpera() || (BX.browser.IsIE() && !BX.browser.IsIE9());
	},

	ReplaceTabSpan: function(line, bugusChar)
	{
		var
			cn = "bxce-ie-tabspan",
			tabSize = 7,
			carWidth = 9.14,
			tabWidth = 63;

		if (!BX.browser.IsIE())
		{
			cn = "bxce-tabspan";
			tabSize = 8;
			carWidth = 9.375;
			tabWidth = 75;
		}

		line = line.replace(/((?:\s|\S)*?)\t/ig, function(s, s1)
			{
				var
					res = s1,
					w = tabWidth;
					l = s1,
					s = s1.lastIndexOf('\n');

				if (s != -1)
					l = l.substr(s + 1);

				if (bugusChar != undefined)
					l = l.replace(bugusChar, '');

				var l1 = l.length % tabSize;
				if (l1 > 0)
				{
					w -= carWidth * l1;
					res += '<tt class="' + cn + '" style="width: ' + w + 'px;"></tt>';
				}
				else
				{
					res += '<tt class="' + cn + '"></tt>';
				}

				return res;
			});


//		if (BX.browser.IsIE())
//			line = line.replace(/\t/ig, '<tt class="bxce-ie-tabspan"></tt>');
//		else
//			line = line.replace(/\t/ig, '<tt class="bxce-tabspan"></tt>');
		return line;
	}
};

var bxce_syntax = {
	php: {
		COMMENT_SINGLE : ['//', '#'],
		COMMENT_MULTI : {'/*' : '*/'},
		QUOTEMARKS : ["'", '"'],
		KEYWORDS :
		{
			statements : ['echo', 'include', 'require', 'include_once', 'require_once','for', 'foreach', 'as', 'if', 'elseif', 'else', 'while', 'do', 'endwhile', 'endif', 'switch', 'case', 'endswitch', 'return', 'break', 'continue'],
			reserved : ['null', '__LINE__', '__FILE__', 'false', '&lt;?php', '?&gt;', '&lt;?', '&lt;script language', '&lt;/script&gt;', 'true', 'var', 'default', 'function', 'class', 'new', '&amp;new', 'this', '__FUNCTION__', '__CLASS__', '__METHOD__', 'PHP_VERSION', 'E_ERROR', 'E_WARNING','E_PARSE', 'E_NOTICE', 'E_CORE_ERROR', 'E_CORE_WARNING', 'E_COMPILE_ERROR', 'E_COMPILE_WARNING', 'E_USER_ERROR', 'E_USER_WARNING', 'E_USER_NOTICE', 'E_ALL'],
			// Php functions which used in Bitrix more than 10 times. Order by count of using
			functions : ['strlen', 'intval', 'htmlspecialchars', 'count', 'is_array', 'substr', 'str_replace', 'array_key_exists', 'trim', 'defined', 'in_array', 'define', 'strtoupper', 'file_exists', 'header', 'preg_replace', 'fwrite', 'urlencode', 'strpos', 'date', 'implode', 'explode', 'strtolower', 'fclose', 'gzwrite', 'fopen', 'addslashes', 'doubleval', 'log', 'round', 'preg_match', 'array_merge', 'set_time_limit', 'array_keys', 'is_dir', 'end', 'print_r', 'closedir', 'readdir', 'opendir', 'unserialize', 'filesize', 'ini_set', 'function_exists', 'file_get_contents', 'ignore_user_abort', 'mktime', 'is_file', 'serialize', 'ob_start', 'time', 'unlink', 'fread', 'reset', 'ob_end_clean', 'ob_get_contents', 'max', 'is_null', 'sizeof', 'call_user_func_array', 'array_unique', 'rtrim', 'is_object', 'strrpos', 'gzopen', 'gzclose', 'abs', 'str_repeat', 'preg_match_all', 'array_search', 'date_format', 'mail', 'floor', 'flush', 'class_exists', 'copy', 'array_walk', 'min', 'gethostbyaddr', 'strtok', 'stripslashes', 'number_format', 'chr', 'mt_rand', 'strval', 'str_pad', 'chmod', 'split', 'rand', 'uniqid', 'fputs', 'flock', 'sprintf', 'urldecode', 'ord', 'call_user_func', 'array_values', 'array_diff', 'ob_get_clean', 'fseek', 'uasort', 'is_readable', 'fgets', 'preg_split', 'strip_tags', 'filemtime', 'array_pop', 'array_push', 'is_numeric', 'method_exists', 'feof', 'fsockopen', 'array_shift', 'gzcompress', 'dechex', 'ini_get', 'is_writable', 'getimagesize', 'sort', 'gzread', 'clearstatcache', 'pack', 'getdate', 'floatval', 'file', 'ceil', 'strtotime', 'preg_quote', 'fflush', 'basename', 'parse_str', 'usort', 'hexdec', 'parse_url', 'mysql_query', 'date_sub', 'ltrim', 'dirname', 'is_string', 'srand', 'pathinfo', 'mkdir', 'array_flip', 'xml_parser_set_option', 'preg_replace_callback', 'mt_srand', 'imagecolorallocate', 'array_reverse', 'imageline', 'microtime', 'array_splice', 'unpack', 'octdec', 'rename', 'extension_loaded', 'array_intersect', 'strstr', 'rmdir', 'ob_end_flush', 'date_add', 'array_unshift', 'array_filter', 'pow', 'ksort', 'stristr', 'error_reporting', 'eregi', 'uksort', 'imagedestroy', 'extract', 'array_slice', 'next', 'is_callable', 'fgetcsv', 'strncmp', 'exec', 'imagecreatetruecolor', 'xml_parser_free', 'xml_parse_into_struct', 'imagecopyresampled', 'asort', 'xml_parser_create', 'session_id', 'is_resource', 'debug_backtrace', 'localtime', 'is_a', 'imagecreate', 'imagesetpixel', 'array_map', 'strcmp', 'session_start', 'imagejpeg', 'mb_substr', 'key', 'imagecopyresized', 'array_sum', 'setcookie', 'mb_strlen', 'is_writeable', 'imagecolorat', 'gettype', 'file_put_contents', 'zip_close', 'mb_convert_encoding', 'is_bool', 'imagecreatefromgif', 'get_class', 'wordwrap', 'strrchr', 'session_cache_limiter', 'phpversion', 'move_uploaded_file', 'mb_strpos', 'is_int', 'imagegif', 'imagecreatefromjpeg', 'gzuncompress', 'func_num_args', 'current', 'substr_count', 'session_write_close', 'realpath', 'readfile', 'mysql_fetch_array', 'mysql_error', 'mysql_data_seek', 'imagestring', 'imagepng', 'imagefill', 'imagecreatefrompng', 'func_get_args', 'func_get_arg', 'each', 'join'	],
			bxfunc: ['SetTitle', 'GetFileContent']// TODO - add more functions?
		},
		OPERATORS: ['+', '-', '/', '*', '=', '<', '>', '%', '!', '&&', '||'],
		REGEXPS : {
			php: {
				search : '()((?:<\\?(?:php)?)|(?:\\?>))()',
				className : 'php',
				modifiers : 'g',
				execute : 'before' // before or after
			},
			bxvars: {
				search : '()(\\$(?:APPLICATION|USER|DB|CacheManager))()',
				className : 'bxvars',
				modifiers : 'ig',
				execute : 'before' // before or after
			},
			// highlight all variables ($...)
			variables : {
				search : '()(\\$\\w+)()',
				className : 'variables',
				modifiers : 'ig',
				execute : 'before' // before or after
			}
		}
	},
	js:
	{
		COMMENT_SINGLE : ['//'],
		COMMENT_MULTI : {'/*' : '*/'},
		QUOTEMARKS : ["'", '"'],
		KEYWORDS : {
			statements : [
				'as', 'break', 'case', 'catch', 'continue', 'decodeURI', 'delete', 'do',
				'else', 'encodeURI', 'eval', 'finally', 'for', 'if', 'in', 'is', 'item',
				'instanceof', 'return', 'switch', 'this', 'throw', 'try', 'typeof', 'void',
				'while', 'write', 'with'
			],
	 		keywords : [
				'class', 'const', 'default', 'debugger', 'export', 'extends', 'false',
				'function', 'import', 'namespace', 'new', 'null', 'package', 'private',
				'true', 'use', 'var', 'window', 'document','location','Math', 'NaN','Array','prototype','Number', 'Object', 'RegExp', 'length'
			],
			functions : [
				'alert','prompt', 'confirm', 'blur', 'close', 'confirm', 'eval ', 'focus', 'name', 'navigate', 'print', 'setInterval', 'setTimeout', 'clearInterval', 'clearTimeout', 'isNan'
			]
		},
		OPERATORS :[
			'+', '-', '/', '*', '=', '<', '>', '%', '!'
		],
		REGEXPS : {
			js: {
				search : '()((?:<script[^>]*?>)|(?:<\\/script>))()',
				className : 'js',
				modifiers : 'ig',
				execute : 'before' // before or after
			}
		}
	},
	html:
	{
		COMMENT_MULTI : {'<!--' : '-->'},
		QUOTEMARKS : ["'", '"'],
		REGEXPS: {
			tags : {
				search : '()(</?[a-z][^ \r\n\t>]*[^>]*>)()',
				className : 'tags',
				modifiers : 'gi',
				execute : 'before'
			},
			attributes : {
				search : '( |\n|\r|\t)([^ \r\n\t=]+)(=)',
				className : 'attributes',
				modifiers : 'g',
				execute : 'before'
			}
		}
	},
	sql: {
		COMMENT_SINGLE : ['--'],
		COMMENT_MULTI : {'/*' : '*/'},
		QUOTEMARKS : ["'", '"'],
		KEYWORDS :
		{
			statements : [
				'create','drop', 'select', 'where', 'order', 'by', 'insert', 'from', 'update', 'grant', 'join', 'left join', 'right join', 'union', 'group', 'having', 'limit', 'alter', 'like','in','case', 'add', 'index'
			],
			reserved : [
				'null', 'not null', 'enum', 'int', 'boolean', 'varchar', 'char', 'date', 'datetime', 'auto_increment', 'default', 'primary key'
			]
		},
		operators :['and','&&','between','binary','&','|','^','/','div','<=>','=','>=','>','<<','>>','null','<=','<','-','%','!=','<>','!','||','+','regexp','rlike','xor','~','*'],
		REGEXPS : {
			// highlight all variables (@...)
			variables : {
				search : '()(\\@\\w+)()',
				className : 'variables',
				modifiers : 'g',
				execute : 'before' // before or after
			}
		}
	}
}
})(window);
