/*
 * Tool Menu 
 */



function ToolMenuClick(sSourceId, sTargetId) {
	var oTarget = document.getElementById(sTargetId);
	if (oTarget.style.visibility=='visible') ToolMenuCloseNow(sTargetId);
	else ToolMenuOpen(sSourceId, sTargetId);
}

function ToolMenuOpen(sSourceId, sTargetId) {
	if (window.iToolMenuTimer) {
		clearTimeout(window.iToolMenuTimer);
		window.iToolMenuTimer = null;
		if (window.sToolMenuTargetId!=sTargetId) ToolMenuCloseNow(window.sToolMenuTargetId);
	}
	var oSource = document.getElementById(sSourceId);
	var oTarget = document.getElementById(sTargetId);
	oTarget.style.left=oSource.offsetLeft+'px';
	oTarget.style.top=oSource.offsetTop+oSource.offsetHeight+'px';
	oTarget.style.visibility='visible';
}

function ToolMenuClose(sTargetId) {
	window.sToolMenuTargetId = sTargetId;
	window.iToolMenuTimer = setTimeout('ToolMenuCloseNow("'+sTargetId+'")', 500);
}

function ToolMenuCloseNow(sTargetId) {
	var oTarget = document.getElementById(sTargetId);
	oTarget.style.visibility='hidden';
}

function ToolMenuStayTop() {// IE6
	var o = document.getElementById('oToolBarDiv');
	o.style.left = document.documentElement.scrollLeft;
	o.style.top = document.documentElement.scrollTop;

        o = document.getElementById('__overlay');
	if (o) {
		o.style.left = document.documentElement.scrollLeft;
		o.style.top = document.documentElement.scrollTop;
	}

	o = document.getElementById('__modalbox');
	if (o) {
		var o2 = o.parentNode.parentNode.parentNode.parentNode.parentNode;
		o2.style.left = document.documentElement.scrollLeft;
		o2.style.top = document.documentElement.scrollTop;
	}
}

function CallMenuFunction(menuChoice) {
	var url = location.pathname.replace(/\/page\/\d+$/, '');
	switch (menuChoice) {
		case 'SavePage': SavePage(); break;
		case 'PublishPage': PublishPage(); break;
		case 'PublishAll': PublishAll(); break;
		case 'BackEnd': window.open('/cms/?'+url, '_blank'); break;
		case 'About': window.open('/cms/about', '_blank', 
			'menubar=0,status=0,resizable=0,scrollbars=0,width=640,height=520'); break;
		case 'Update': window.open('/cms/update', '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=0,width=350,height=200'); break;
		case 'EditHead': window.open('/cms/head?'+url, '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=0,width=750,height=650'); break;
		case 'Config': window.open('/cms/config', '_blank'); break;
		case 'Rights': window.open('/cms/rights', '_blank'); break;
		case 'StyleCss': window.open('/cms/txt?file=/style.css', '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=0,width=350,height=200'); break;
		case 'Htaccess': window.open('/cms/txt?file=/.htaccess', '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=0,width=350,height=200'); break;
		case 'RobotsTxt': window.open('/cms/txt?file=/robots.txt', '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=0,width=350,height=200'); break;
		case 'PageMaster': window.open('/cms/includes/PageMaster.html', '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=0,width=350,height=250'); break;
		case 'RootTemplateProperties': TPDlg('root.xml'); break;
		case 'SiteStat': window.open('/cms/stat', '_blank'); break;
		case 'Import': window.open('/cms/import?'+url, '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=1,width=600,height=600'); break;
		case 'Export': window.open('/cms/export?'+url, '_blank'); break;
		case 'Email': window.open('/cms/email?'+url, '_blank',
			'menubar=0,status=0,resizable=1,scrollbars=1,width=600,height=600'); break;
		default: break;
	}
}










/*
 * Editor
 */



function cmsSetCookie(sName, sValue, dDate) {
	document.cookie = sName + "=" + sValue + ";" + (dDate!=null ? " expires=" + dDate + ";" : "") + " path=/;";
}

function cmsDelCookie(sName) {
	document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT; path=/;";
}

function cmsGetCookie(sName) {
	var aCookie = document.cookie.split('; ');
	for (var i=0; i < aCookie.length; i++) {
		var aCrumb = aCookie[i].split('=');
		if (sName == aCrumb[0]) return unescape(aCrumb[1]);
	}
	return null;
}

function cmsGetLeft(o) {
	var result = o.offsetLeft;
	while (o = o.offsetParent) result += o.offsetLeft;
	return result;
}

function cmsGetTop(o) {
	var result = o.offsetTop;
	while (o = o.offsetParent) result += o.offsetTop;
	return result;
}
















function cmsAddEvent(o, e, f) {
	if (o.attachEvent) {// IE Opera
		o.attachEvent('on'+e, f);
        } else {// FF
		o.addEventListener(e, f, false);
	}
}

function cmsRemoveEvent(o, e, f) {
	if (o.detachEvent) {// IE Opera
		o.detachEvent('on'+e, f);
        } else {// FF
		o.removeEventListener(e, f, false);
	}
}

function getSelectedText() {
	if (window.getSelection) {// FF Opera
		return window.getSelection();
	} else {// IE
		if (document.selection.type=='Control') return false;
		var r = document.selection.createRange();
		return r.text;
	}
}

function getSelectedImage() {
	var img = null;
	if (document.selection) {// IE
		if (document.selection.type=='Control') {
			var o = document.selection.createRange().item(0);
			if (o.tagName.toLowerCase()=='img') img = o;
                }
	} else {// FF
		var sel = window.getSelection();
		var imgs = window.cmsActiveBlock.getElementsByTagName('img');
		for (var i=0; i<imgs.length; i++) if (sel.containsNode(imgs[i], false)) img = imgs[i];
	}
	return img;
}

function cmsPasteHTML(sHTML) {
	var b = false;
	try {b = document.queryCommandEnabled('InsertHTML');} catch (e) {}
	if (!b) {// IE
		if (document.selection.type=='Control') return false;
		var r = document.selection.createRange();
		r.pasteHTML(sHTML);
	} else {// FF Opera
		try {document.execCommand('InsertHTML', false, sHTML);} catch (e) {}
	}
}

function stopPropagation(event) {
	if (!event) return;
	if (event.stopPropagation) {// FF
		event.stopPropagation();
        } else {// IE
		event.cancelBubble = true;
	}
}

function getTarget(event) {
	if (event.srcElement) {// IE
		return event.srcElement;
	} else {// FF Opera
		return event.target;
	}
}

function cmsStoreSelection() {
	if (window.getSelection) {// FF Opera
		var s = window.getSelection();
		window.cmsStoredSelection = s.getRangeAt(0);
	} else {// IE
		window.cmsStoredSelection = document.selection.createRange();
	}
}

function cmsRestoreSelection() {
	if (window.getSelection) {// FF Opera
		var s = window.getSelection();
		s.removeAllRanges();
		if (window.cmsStoredSelection) s.addRange(window.cmsStoredSelection);
	} else {// IE
		document.selection.empty();
		if (window.cmsStoredSelection) window.cmsStoredSelection.select();
	}
}












function cmsFocus(o, bStrict) {
	if (o.setActive) {// IE
		o.setActive();
	} else if (bStrict) {// FF Opera
		var left = document.documentElement.scrollLeft;
		var top = document.documentElement.scrollTop;
		o.focus();
		document.documentElement.scrollLeft = left;
		document.documentElement.scrollTop = top;
	}
}

function cmsOnFocus(e) {
	if (!e && window.event) e = event;
	window.cmsActiveBlock = this;
	if (this.contentEditable.toString()=='true' && this.innerHTML==this.getAttribute('var_default_text'))
		this.innerHTML = '';
	if (e) stopPropagation(e);
}

function cmsIsEmpty(o) {
	if (o.innerHTML.match(/^\s*$/)) return true;
	if (o.innerHTML==o.getAttribute('var_default_text')) return true;
	if (!o.innerHTML.match(/^\s*\<img/i)) {
		if (window.getSelection) {// FF
			if (o.textContent.match(/^\s*$/)) return true;
		} else {// IE
			if (o.innerText.match(/^\s*$/)) return true;
		}
	}
	return false;
}

function cmsOnBlur() {
	if (this.contentEditable.toString()=='true' && cmsIsEmpty(this))
		this.innerHTML = this.getAttribute('var_default_text');
}

function cmsWindowChange() {
	var o = document.getElementById('ToolBarEditor');
	if (window.cmsActiveBlock && window.cmsActiveBlock.id && o && o.style.visibility=='visible')
		cmsShowEditor(window.cmsActiveBlock.id);
}

function cmsInit(isFrontend) {
	eval('var f = function () {_cmsInit("'+isFrontend+'");}');
	cmsAddEvent(window, 'load', f);
	//cmsAddEvent(window, 'resize', cmsWindowChange);
	//cmsAddEvent(window, 'scroll', cmsWindowChange);
}

function _cmsInit(isFrontend) {
	var frontendBtns = new Array(
		{t: 'Сохранить" id="cmsSaveBtn',	a: 'SavePage()',			s: 'save.gif'},
		{t: 'Сохранить и опубликовать" id="cmsSaveBtn2',a: 'SavePage(true)',s: 'save-and-publish.gif'});
	var btns = new Array(
		{t: 'Вырезать',				a: 'cmsFormat(\'Cut\')',		s: 'cut.gif'},
		{t: 'Копировать',			a: 'cmsFormat(\'Copy\')',		s: 'copy.gif'},
		{t: 'Вставить',				a: 'cmsFormat(\'Paste\')',		s: 'paste.gif'},
		{t: 'Вставить текст',			a: 'cmsFormat(\'PasteText\')',		s: 'pastetext.gif'},
		{t: 'Отменить',				a: 'cmsFormat(\'Undo\')',		s: 'undo.gif'},
		{t: 'Повторить',			a: 'cmsFormat(\'Redo\')',		s: 'redo.gif'},
		{t: 'Удалить ссылку',			a: 'cmsFormat(\'Unlink\')',		s: 'unlink.gif'},
		{t: 'Создать ссылку',			a: 'cmsFormat(\'CreateLink\')',		s: 'createlink.gif'},
		{t: 'Вставить изображение',		a: 'cmsFormat(\'InsertImage\')',	s: 'insertimage.gif'},
		{t: 'Вставить таблицу',			a: 'cmsFormat(\'InsertTable\')',	s: 'table.gif'},
		{t: 'Дополнительное форматирование',	a: 'cmsFormat(\'BlockFormat\')',	s: 'blockformat.gif'},
		{t: 'Включить полноэкранный режим',	a: 'cmsFormat(\'Maximize\')',		s: 'maximize.gif'},
		{t: 'Закрыть редактор',			a: 'cmsFormat(\'Close\', null, true)',	s: 'close.gif'},
		{t: 'Исходный текст',			a: 'cmsFormat(\'Html\')',		s: 'html.gif'},
		{t: 'Полужирный',			a: 'cmsFormat(\'Bold\')',		s: 'bold.gif'},
		{t: 'Курсив',				a: 'cmsFormat(\'Italic\')',		s: 'italic.gif'},
		{t: 'Подчеркнутый',			a: 'cmsFormat(\'Underline\')',		s: 'underline.gif'},
		{t: 'Перечеркнутый',			a: 'cmsFormat(\'StrikeThrough\')',	s: 'strikethrough.gif'},
		{t: 'Нижний индекс',			a: 'cmsFormat(\'SubScript\')',		s: 'sub.gif'},
		{t: 'Верхний индекс',			a: 'cmsFormat(\'SuperScript\')',	s: 'sup.gif'},
		{t: 'Выравнивание по левому краю',	a: 'cmsFormat(\'JustifyLeft\')',	s: 'justifyleft.gif'},
		{t: 'Выравнивание по центру',		a: 'cmsFormat(\'JustifyCenter\')',	s: 'justifycenter.gif'},
		{t: 'Выравнивание по правому краю',	a: 'cmsFormat(\'JustifyRight\')',	s: 'justifyright.gif'},
		{t: 'Выравнивание по ширине',		a: 'cmsFormat(\'JustifyFull\')',	s: 'justifyfull.gif'},
		{t: 'Ненумерованый список',		a: 'cmsFormat(\'InsertUnorderedList\')',s: 'bulletlist.gif'},
		{t: 'Нумерованый список',		a: 'cmsFormat(\'InsertOrderedList\')',	s: 'numberlist.gif'},
		{t: 'Увеличить отступ',			a: 'cmsFormat(\'Indent\')',		s: 'indent.gif'},
		{t: 'Уменьшить отступ',			a: 'cmsFormat(\'Outdent\')',		s: 'outdent.gif'},
		{t: 'Очистить формат',			a: 'cmsFormat(\'RemoveFormat\')',	s: 'removeformat.gif'});
	var buf = '';
	if (isFrontend=='true') for (var i=0; i<frontendBtns.length; i++)
		buf += '<a title="'+frontendBtns[i].t+'" href="javascript:'+frontendBtns[i].a
			+'"><img src="/cms/img/toolicons/'+frontendBtns[i].s+'" width="20" height="20" /></a>';
	for (var i=0; i<btns.length; i++) {
		if (i==9) buf += '<a title="Быстрая загрузка изображения" href="#">\
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" \
	codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" \
	width="20" height="20" id="fu" align="middle">\
<param name="allowScriptAccess" value="sameDomain" />\
<param name="allowFullScreen" value="false" />\
<param name="movie" value="/cms/img/toolicons/fu14.swf?PHPSESSID='+cmsGetCookie('PHPSESSID')+'" />\
<param name="quality" value="high" />\
<param name="bgcolor" value="#ffffff" />\
<param name="wmode" value="transparent" />\
<embed src="/cms/img/toolicons/fu14.swf?PHPSESSID='+cmsGetCookie('PHPSESSID')+'" quality="high" wmode="transparent" \
	bgcolor="#ffffff" width="20" height="20" name="fu" align="middle" allowScriptAccess="sameDomain" \
	allowFullScreen="false" type="application/x-shockwave-flash" \
	pluginspage="http://www.macromedia.com/go/getflashplayer" />\
</object></a>';
		if (i==13) buf += '<br />';
		buf += '<a title="'+btns[i].t+'" href="javascript:'+btns[i].a
			+'"><img src="/cms/img/toolicons/'+btns[i].s+'" width="20" height="20" /></a>';
	}
	
	var eb = document.getElementById('ToolBarEditor');
	eb.innerHTML = buf;
	//eb.style.position = 'fixed';
	//eb.style.left = '0px';
	//eb.style.top = '28px';
	eb.style.display = 'none';
	
	if (!window.XMLHttpRequest) {// IE6
		var o = document.getElementById('oToolBarDiv');
		o.style.position = 'absolute';
		o.style.left = '0px';
		o.style.top = '0px';
		window.onscroll = ToolMenuStayTop;
		window.onresize = ToolMenuStayTop;
	}
}

function cmsAddEditor(id) {
        if (!window.cmsEditBlocks) window.cmsEditBlocks = new Array();
        window.cmsEditBlocks.push(id);
	eval('var f = function () {_cmsAddEditor("'+id+'");}');
	cmsAddEvent(window, 'load', f);
}

function cmsAddEditor2(id) {
        if (!window.cmsEditBlocks) window.cmsEditBlocks = new Array();
        window.cmsEditBlocks.push(id);
	_cmsAddEditor(id);
}

function _cmsAddEditor(id) {
	var o = document.getElementById(id);
        if (!o) return false;
        o._initValue = o.innerHTML;
	o.onfocus = cmsOnFocus;
	o.onclick = cmsOnFocus;
	o.onblur = cmsOnBlur;
	o.oncontextmenu = ContextMenuOpen;
	o.onmouseover = AltContextMenuOpenDelayed;
	o.onmouseout = AltContextMenuCloseDelayed;
	//if (window.navigator.appName=='Opera') o.onclick = OperaClick;

        if (o.innerHTML=='' && o.getAttribute('cms_default')) o.innerHTML = o.getAttribute('cms_default');
	if (!Published(o)) o.setAttribute('pubmode', 'edited');
	if (Hidden(o)) o.setAttribute('pubmode', 'hidden');
	o.setAttribute('inittext', o.innerHTML);
}

function cmsShowEditor(id) {
	var d = document.getElementById(id);
	var o = document.getElementById('ToolBarEditor');
	window.cmsActiveBlock = d;
	o.style.display = '';
	var oToolBarDivBlock = document.getElementById('oToolBarDivBlock');
	oToolBarDivBlock.style.height = '84px';
}

function cmsHideEditor() {
	var o = document.getElementById('ToolBarEditor');
	if (o) o.style.display = 'none';
	var oToolBarDivBlock = document.getElementById('oToolBarDivBlock');
	oToolBarDivBlock.style.height = '28px';
	if (window.cmsActiveBlock) {
		if (!window.cmsActiveBlock.getAttribute('pubmode') && Changed(window.cmsActiveBlock))
			window.cmsActiveBlock.setAttribute('pubmode', 'edited');
		window.cmsActiveBlock = null;
	}
}

function cmsFlashUploadReturn(src) {
	cmsFocus(window.cmsActiveBlock);
	
	var width = '';
	var height = '';

	var sParamImg = window.cmsActiveBlock.getAttribute('param_img');
	if (sParamImg) {
		var width = parseInt(sParamImg.replace(/^([\d\*]+)\x([\d\*]+)(\,\s*.*)?(\;.*)?$/, '$1'));
		var height = parseInt(sParamImg.replace(/^([\d\*]+)\x([\d\*]+)(\,\s*.*)?(\;.*)?$/, '$2'));
		if (sParamImg.match(/cut/)) {
			window.cutimage_url = src;
			window.cutimage_width = width;
			window.cutimage_height = height;
			modalDlg.open('/cms/includes/cutimage.html', null, 300, 300);
//			showModalDialog('/cms/includes/cutimage.html',
//				{sUrl: src, iWidth: width, iHeight: height},
//				'dialogwidth: 300px; dialogheight: 300px;');
		}
	}

	var buf = '';

	if (src.match(/\.swf$/i)) {
		buf = '<object';
		if (width!='') buf += ' width="'+width+'"';
		if (height!='') buf += ' height="'+height+'"';
		buf += ' classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"><param name="movie" value="'
			+src+'" /><param name="quality" value="high" /><param name="allowScriptAccess" value="sameDomain" /><param name="wmode" value="transparent"/><embed src="'
			+src+'"';
		if (width!='') buf += ' width="'+width+'"';
		if (height!='') buf += ' height="'+height+'"';
		buf += ' quality="high" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object>';
	} else {
		buf = '<img src="'+src+'" alt="" style="';
		if (width!='') buf += ' width: '+width+'px;';
		if (height!='') buf += ' height: '+height+'px;';
		buf += '" />';
	}
	
	window.cmsActiveBlock.onfocus();
	cmsPasteHTML(buf);
	if (!sParamImg || !sParamImg.match(/cut/)) CmsLoadWndHide();
}

function cmsFormat(s, o, notFocus) {
	if (o) window.cmsActiveBlock = o;
	if (!notFocus) cmsFocus(window.cmsActiveBlock);

	var img = getSelectedImage();
	if (img) {
		switch (s) {
			case 'JustifyLeft': img.setAttribute('align', 'left'); img = true; break;
			case 'JustifyRight': img.setAttribute('align', 'right'); img = true; break;
			case 'JustifyCenter': img.setAttribute('align', 'center'); img = true; break;
			case 'JustifyFull': img.removeAttribute('align'); img = true; break;
			case 'Indent':
				img.style.margin = (parseInt(img.style.margin ? img.style.margin : 0) + 10) + 'px';
				img = true;
				break;
			case 'Outdent':
				img.style.margin = (parseInt(img.style.margin ? img.style.margin : 0) - 10) + 'px';
				img = true;
				break;
			default: img = false; break;
		}
	}
	if (!img) switch (s) {
		case 'Close':
			EditableSwitch(window.cmsActiveBlock.id);
			break;
		case 'Html':
			var d = window.cmsActiveBlock;
			var o = document.getElementById('ToolBarEditor');
			o = o.getElementsByTagName('a');
			for (var i=0; i<o.length; i++)
				if (o[i].getAttribute('href')=='javascript:cmsFormat(\'Html\')') {o = o[i]; break;}
			if (o.length) break;
			o.setAttribute('href', 'javascript:cmsFormat(\'Text\')');
			o.setAttribute('title', 'Форматирование');
			o.firstChild.setAttribute('src', '/cms/img/toolicons/text.gif');
			var text = document.createTextNode(d.innerHTML);
			d.innerHTML = '';
			d.appendChild(text);
			break;
		case 'Text':
			var d = window.cmsActiveBlock;
			var o = document.getElementById('ToolBarEditor');
			o = o.getElementsByTagName('a');
			for (var i=0; i<o.length; i++)
				if (o[i].getAttribute('href')=='javascript:cmsFormat(\'Text\')') {o = o[i]; break;}
			if (o.length) break;
			o.setAttribute('href', 'javascript:cmsFormat(\'Html\')');
			o.setAttribute('title', 'Исходный код');
			o.firstChild.setAttribute('src', '/cms/img/toolicons/html.gif');
			if (document.createRange) {//FF
				var text = document.createRange();
				text.selectNodeContents(d);
				d.innerHTML = text;
			} else d.innerHTML = d.innerText;
			break;
		case 'CreateLink': cmsStoreSelection(); modalDlg.open('/cms/includes/createlink.html', null, 340, 130); break;
		case 'InsertImage': cmsStoreSelection(); modalDlg.open('/cms/includes/insertimage.html', null, 330, 200); break;
		case 'InsertTable': cmsStoreSelection(); modalDlg.open('/cms/includes/inserttable.html', null, 250, 250); break;
		case 'BlockFormat': cmsStoreSelection(); modalDlg.open('/cms/includes/blockformat.html', null, 300, 370); break;
		case 'Maximize':
			var d = window.cmsActiveBlock;
			var o = document.getElementById('ToolBarEditor');
			o = o.getElementsByTagName('a');
			for (var i=0; i<o.length; i++)
				if (o[i].getAttribute('href')=='javascript:cmsFormat(\'Maximize\')') {o = o[i]; break;}
			if (o.length) break;
			o.setAttribute('href', 'javascript:cmsFormat(\'Minimize\')');
			o.setAttribute('title', 'Выключить полноэкранный режим');

			window.cmsMaximizeWidth = d.style.width;
			window.cmsMaximizeHeight = d.style.height;
			d.className = 'cmsEditableMaximize';
			d.style.width = (document.documentElement.clientWidth - 12) + 'px';
			d.style.height = (document.documentElement.clientHeight - 68) + 'px';
			cmsShowEditor(d.id);
			break;
		case 'Minimize':
			var d = window.cmsActiveBlock;
			var o = document.getElementById('ToolBarEditor');
			o = o.getElementsByTagName('a');
			for (var i=0; i<o.length; i++)
				if (o[i].getAttribute('href')=='javascript:cmsFormat(\'Minimize\')') {o = o[i]; break;}
			if (o.length) break;
			o.setAttribute('href', 'javascript:cmsFormat(\'Maximize\')');
			o.setAttribute('title', 'Включить полноэкранный режим');

			d.className = 'cmsEditable';
			d.style.width = window.cmsMaximizeWidth;
			d.style.height = window.cmsMaximizeHeight;
			cmsFocus(window.cmsActiveBlock);
			cmsShowEditor(d.id);
			break;
		case 'PasteText':
			document.execCommand('Paste', false, window.clipboardData.getData('Text'));
			break;
		default:
			try {document.execCommand(s, false, null);} catch (e) {}
			break;
	}
}


function fb() {
	window.open('/cms/fb', '_blank', 'menubar=0,status=0,resizable=1,scrollbars=1,width=640,height=480');
}
function fbReturn(sUrl) {
	document.getElementById('cmsSrc').value = sUrl;
	cmsChangeIcon();
}
function cmsChangeIcon() {
	var oType = document.getElementById('cmsIcon');
	if (!oType) return;
	else var type = oType.value;
	if (type=='auto') {
		var ext = document.getElementById('cmsSrc').value.replace(/^.*\.([^\.]+)$/, '$1');
		switch (ext) {
			case 'zip': case 'rar': type = 'archive'; break;
			case 'txt': case 'rtf': case 'doc': case 'pdf': type = 'text'; break;
			case 'jpg': case 'jpeg': case 'gif': case 'png': type = 'image'; break;
			case 'mpg': case 'mpeg': case 'wmv': case 'avi': case 'mp4': type = 'movie'; break;
			case 'mp3': case 'wma': case 'ogg': type = 'sound'; break;
			default: type = 'none'; break;
		}
	}
	if (type=='none') document.getElementById('cmsIconImg').removeAttribute('src');
	else document.getElementById('cmsIconImg').setAttribute('src', '/cms/img/filetypes/'+type+'.gif');
}
function cmsCreateLink() {
	cmsRestoreSelection();
	var result = getSelectedText();
	var src = document.getElementById('cmsSrc').value;
	var icon = document.getElementById('cmsIconImg').getAttribute('src');
	if (icon && icon!='') {
		if (result=='') result = src;
		result = '<a href="'+src+'">'+result+'</a>';
		result = '<img src="'+icon+'" style="vertical-align: middle;" />&nbsp;'+result;
		cmsFocus(window.cmsActiveBlock);
		cmsPasteHTML(result);
        } else document.execCommand('CreateLink', false, src);

	modalDlg.close();
}
function cmsInsertTable(oForm) {
	cmsRestoreSelection();
	var cols = parseInt(oForm.elements['cols'].value);
	var rows = parseInt(oForm.elements['rows'].value);
	if (cols!=0 && rows!=0) {
		var result = '<table';
		var cellspacing = oForm.elements['cellspacing'].value;
		var cellpadding = oForm.elements['cellpadding'].value;
		var width = oForm.elements['width'].value;
		var border = oForm.elements['border'].value;
		if (cellspacing!='') result += ' cellspacing="'+cellspacing+'"';
		if (cellpadding!='') result += ' cellpadding="'+cellpadding+'"';
		if (width!='') result += ' width="'+width+'"';
		if (border!='') result += ' border="'+border+'"';
		result += '>';
		for (var i=0; i<rows; i++) {
			result += '<tr>';
			for (var j=0; j<cols; j++) result += '<td>&nbsp;</td>';
			result += '</tr>';
		}
		result += '</table>';
		cmsFocus(window.cmsActiveBlock, true);
		cmsPasteHTML(result);
	}
	modalDlg.close();
}
function cmsInsertImage(oForm) {
	cmsRestoreSelection();
	var src = document.getElementById('cmsSrc').value;
	if (src!='') {
		var width = oForm.elements['width'].value;
		var height = oForm.elements['height'].value;

		var sParamImg = window.cmsActiveBlock.getAttribute('param_img');
		if (sParamImg) {
			width = parseInt(sParamImg.replace(/^([\d\*]+)\x([\d\*]+)(\,\s*.*)?(\;.*)?$/, '$1'));
			height = parseInt(sParamImg.replace(/^([\d\*]+)\x([\d\*]+)(\,\s*.*)?(\;.*)?$/, '$2'));
			if (sParamImg.match(/cut/)) {
				window.cutimage_url = src;
				window.cutimage_width = width;
				window.cutimage_height = height;
				modalDlg.open('/cms/includes/cutimage.html', null, 300, 300);
//				showModalDialog('/cms/includes/cutimage.html',
//					{sUrl: src, iWidth: width, iHeight: height},
//					'dialogwidth: 300px; dialogheight: 300px;');
			}
		}

		var alt = oForm.elements['alt'].value;
		var result = '';

		if (src.match(/\.swf$/i)) {
			var d = new Date();
			var oid = 'f'+d.getTime();
			result = '<object id="'+oid+'"';
			if (width!='') result += ' width="'+width+'"';
			if (height!='') result += ' height="'+height+'"';
			result += ' classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.adobe.com/pub/shockwave/cabs/flash/"><param name="movie" value="'
				+src+'" /><param name="quality" value="high" /><param name="allowScriptAccess" value="sameDomain" /><param name="wmode" value="transparent" /><embed src="'
				+src+'"';
			if (width!='') result += ' width="'+width+'"';
			if (height!='') result += ' height="'+height+'"';
			result += ' name="'+oid+'" quality="high" allowScriptAccess="sameDomain" wmode="transparent" pluginspage="http://www.adobe.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" /></object>';
		} else {
			result = '<img src="'+src+'" alt="'+alt+'" style="';
			if (width!='') result += ' width: '+width+'px;';
			if (height!='') result += ' height: '+height+'px;';
			result += '" />';
		}


		cmsFocus(window.cmsActiveBlock, true);
		cmsPasteHTML(result);
	}
	if (!sParamImg || !sParamImg.match(/cut/)) modalDlg.close();
}

function cmsBlockFormat(oForm) {
	cmsRestoreSelection();

	var tag = oForm.elements['tag'].value;
	var result = '<'+tag+' style="'

	if (oForm.elements['bold'].checked) result += 'font-weight: bold;';
	if (oForm.elements['italic'].checked) result += 'font-style: italic;';
	if (oForm.elements['underline'].checked) result += 'text-decoration: underline;';

	var value = oForm.elements['color'].value;
	if (value!='') result += 'color: '+value+';';

	var value = oForm.elements['fontFamily'].value;
	if (value!='') result += 'font-family: '+value+';';

	var value = oForm.elements['fontSize'].value;
	if (value!='') result += 'font-size: '+value+';';

	result += '">'+getSelectedText()+'</'+tag+'>';

	cmsFocus(window.cmsActiveBlock);
	cmsPasteHTML(result);

	modalDlg.close();
}
















function cmsSaveBtnDisable() {
	var o = document.getElementById('cmsSaveBtn');
	o.setAttribute('href', 'javascript:cmsAction(\'\')');
	o.firstChild.style.opacity = '0.3';
	o.firstChild.style.filter = 'alpha(opacity=30)';
	
	var o = document.getElementById('cmsSaveBtn2');
	o.setAttribute('href', 'javascript:cmsAction(\'\')');
	o.firstChild.style.opacity = '0.3';
	o.firstChild.style.filter = 'alpha(opacity=30)';
}

function cmsSaveBtnEnable() {
	var o = document.getElementById('cmsSaveBtn');
	o.setAttribute('href', 'javascript:SavePage()');
	o.firstChild.style.opacity = '';
	o.firstChild.style.filter = '';
	
	var o = document.getElementById('cmsSaveBtn2');
	o.setAttribute('href', 'javascript:SavePage(true)');
	o.firstChild.style.opacity = '';
	o.firstChild.style.filter = '';
}

function PrepareSaveVar(text) {
	text = text.replace(/\<\?xml[^\>]*\>/gi, '');
	text = text.replace(new RegExp('src="http://'+location.hostname, 'gi'), 'src="');
	text = text.replace(new RegExp('href="http://'+location.hostname, 'gi'), 'href="');
	return encodeURIComponent(text);
}

function PrepareImages(o) {
	var imgs = o.getElementsByTagName('img');
	for (var i=0; i<imgs.length; i++) {
		var img = imgs[i];
		var url = img.getAttribute('src').replace(new RegExp('http://'+location.hostname, 'gi'), '');
		var matches = url.match(/^\/cms\/fb\/vi\?w\=[^\&]*\&h\=[^\&]*(\&m\=[^\&]*)?\&url\=([^\&]+)$/i);
		if (matches) url = matches[2];
		if (!o.getAttribute('param_img')) {
			var w = parseInt(img.getAttribute('width'));
			if (img.style.width!='') w = parseInt(img.style.width);
			var h = parseInt(img.getAttribute('height'));
			if (img.style.height!='') h = parseInt(img.style.height);
			if (!w && !h) continue;
			if (!w) w = '*';
			if (!h) h = '*';
			url = '/cms/fb/vi?w='+w+'&h='+h+'&url='+url;
		}
		img.removeAttribute('width');
		img.removeAttribute('height');
		img.setAttribute('src', url);
	}
}

/*function PrepareFlashes(o) {;
	var embeds = o.getElementsByTagName('embed');
	for (var i=0; i<embeds.length; i++) {
		var embed = embeds[i];
		
		var object = document.createElement('object');
		object.setAttribute('classid', 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000');
		object.setAttribute('codebase', 'http://fpdownload.adobe.com/pub/shockwave/cabs/flash/');
		object.setAttribute('id', embed.getAttribute('name'))
		object.setAttribute('width', embed.getAttribute('width'));
		object.setAttribute('height', embed.getAttribute('height'));

		var param = document.createElement('param');
		param.setAttribute('name', 'movie');
		param.setAttribute('value', embed.getAttribute('src'));
		object.appendChild(param);

		param = document.createElement('param');
		param.setAttribute('name', 'quality');
		param.setAttribute('value', 'high');
		object.appendChild(param);

		param = document.createElement('param');
		param.setAttribute('name', 'wmode');
		param.setAttribute('value', 'transparent');
		object.appendChild(param);

		object.appendChild(embed);

		embed.parentNode.parentNode.replaceChild(object, embed.parentNode);
	}
}*/

function SavePage(publish) {
	cmsSaveBtnDisable();
	CmsLoadWndShow();
	var sBody = '';
	var noParent = false;
	window.aSavePageBlocks = Object();
	for (var j=0; j<window.cmsEditBlocks.length; j++) {
		o = document.getElementById(window.cmsEditBlocks[j]);
		if (!o) continue;
		if (!Changed(o) || o.getAttribute('pubmode')=='saving') continue;	

		sBody += '&_a['+j+'][action]='+(publish ? 'saveAndPublish' : 'save');
		if (o.getAttribute('var_id') && o.getAttribute('var_id')!='') {
			sBody += '&_a['+j+'][id]='+o.getAttribute('var_id');
			if (CanInsert(o)) sBody += '&_a['+j+'][titleToLink]=true';
		} else {
			if (o.getAttribute('var_parent_id') && o.getAttribute('var_parent_id')!='') {
				sBody += '&_a['+j+'][parent_id]='+o.getAttribute('var_parent_id')
					+'&_a['+j+'][titleToLink]=true';
			} else {
				noParent = true;
				sBody += '&_a['+j+'][parent_id]=DEFAULT'
					+'&_a['+j+'][vars][link]='+location.pathname;
			}
			sBody += '&_a['+j+'][vars][create_time]=NOW'
				+'&_a['+j+'][vars][title]='+encodeURIComponent('Новая страница');
		}
		
		var pb = GetParentBlock(o);
		if (!pb) continue;
		window.aSavePageBlocks[pb.id] = pb;
		var t = pb.getAttribute('template');
		if (t.match(/^.*save_params\=[\"\']([^\"\']+)[\"\'].*$/i)) sBody += '&_a['+j+'][params]='
				+encodeURIComponent(t.replace(/^.*save_params\=[\"\']([^\"\']+)[\"\'].*$/i, '$1'));
		
		var aVar = GetVarsByVar(o);
		for (var i=0; i<aVar.length; i++) if (Changed(aVar[i]) && aVar[i].getAttribute('pubmode')!='saving') {
			o = aVar[i];
			o.setAttribute('pubmode', 'saving');
			cmsFormat('Text', o, true);
			
			var o = aVar[i].cloneNode(true);
			if (cmsIsEmpty(o)) o.innerHTML = '';
			PrepareImages(o);
			//PrepareFlashes(o);
			var aSpan = o.getElementsByTagName('div');
			for (var k=0; k<aSpan.length; k++) if (aSpan[k].getAttribute('template')) {
				var buf = document.createElement('div');
				buf.innerHTML = aSpan[k].getAttribute('template').substr(4);
				aSpan[k].parentNode.replaceChild(buf.firstChild, aSpan[k]);
				k--;
			}
			sBody += '&_a['+j+'][vars]['+o.getAttribute('var_name')+']='+PrepareSaveVar(o.innerHTML);
                }
	}
	if (sBody!='') {
		//alert(sBody);
		if (noParent) loader.SendRequest('/cms/action?async=exit', sBody, 'POST', ReloadPage, SavePageFailed);
		else loader.SendRequest('/cms/action?async=exit', sBody, 'POST', SavePageComplete, SavePageFailed);
	} else {
		cmsSaveBtnEnable();
		CmsLoadWndHide();
	}
}
function SavePageComplete() {
	cmsSaveBtnEnable();
	CmsLoadWndHide();
	cmsFormat('Close');
	cmsHideEditor();
	for (var id in window.aSavePageBlocks) Load(id);
	window.aSavePageBlocks = null;
}
function SavePageFailed(sReadyState, sStatus, sHeaders) {
	cmsSaveBtnEnable();
	CmsLoadWndHide();
	window.aSavePageBlocks = null;
	alert("Не удалось передать данные на сервер.\nПроверте состояние подключения и повторите попытку.\n\n"
		+'ReadyState: '+sReadyState+'; Status: '+sStatus+'; Headers:\n\n'+sHeaders);
}

function Insert(id) {
	CmsLoadWndShow();
	var o = document.getElementById(id);
	var pb = GetParentBlock(o);
	var t = pb.getAttribute('template');
	if (t.match(/^.*save_params\=[\"\']([^\"\']+)[\"\'].*$/i)) {
		var saveParams = t.replace(/^.*save_params\=[\"\']([^\"\']+)[\"\'].*$/i, '$1');
		saveParams = encodeURIComponent(saveParams);
	} else var saveParams = '';
	var d = new Date();
	var sBody = '_a[0][action]=save&_a[0][params]='+saveParams
		+'&_a[0][titleToLink]=true'
		+'&_a[0][parent_id]='+o.getAttribute('var_parent_id')
		+'&_a[0][vars][create_time]=NOW&_a[0][vars][title]='+encodeURIComponent('Новая запись');
	eval('var f = function() {Load("'+pb.id+'"); CmsLoadWndHide();}');
	loader.SendRequest('/cms/action?async=exit', sBody, 'POST', f);
}
function Delete(id) {
	var o = document.getElementById(id);
	var pb = GetParentBlock(o);
	var aVar = GetVarsByVar(o);
	var sTitle = '';
	for (var i=0; i<aVar.length; i++) if (aVar[i].getAttribute('var_name')=='title') {
		if (aVar[i].textContent) sTitle = aVar[i].textContent;// FF
		else if (aVar[i].innerText) sTitle = aVar[i].innerText;// IE
        }
	if (sTitle!='') sTitle = ' "'+sTitle+'"';
	if (confirm('Вы уверены что хотите удалить запись '+sTitle
		+"?\nВосстановить удаленную запись будет невозможно")) {
		var bHasChildBlocks = false;
		var aSpan = pb.getElementsByTagName('div');
		for (var j=0; j<aSpan.length; j++) if (aSpan[j].getAttribute('template')) {
			bHasChildBlocks = true;
			break;
		}
		if (!bHasChildBlocks 
			|| confirm("Эта запись содержит вложенные записи\nВы точно хотите удалить эту запись?")) {
			CmsLoadWndShow();
			eval('var f = function() {Load("'+pb.id+'"); CmsLoadWndHide();}');
			loader.SendRequest('/cms/action?async=exit',
				'_a[0][action]=delete&_a[0][id]='+o.getAttribute('var_id'), 'POST', f);
		}
	}
}


function ReloadPage() {
	window.location.reload();
}
function PublishPage() {
	var sBody = '';
	for (var j=0; j<window.cmsEditBlocks.length; j++) {
		o = document.getElementById(window.cmsEditBlocks[j]);
		if (!o) continue;
		if (o.getAttribute('var_id') && o.getAttribute('var_id')!='')
			sBody += '&_a['+j+'][action]=publish'
				+'&_a['+j+'][id]='+o.getAttribute('var_id');
        }
	if (sBody!='') {
		CmsLoadWndShow();
		loader.SendRequest('/cms/action?async=exit', sBody, 'POST', ReloadPage);
	}
}

function PublishAll() {
	CmsLoadWndShow();
	loader.SendRequest('/cms/action?async=exit', '_a[0][action]=publishAll', 'POST', ReloadPage);
}

function cmsSimpleAction(action, id) {
	CmsLoadWndShow();
	var o = document.getElementById(id);
	eval('var f = function() {Load("'+GetParentBlock(o).id+'"); CmsLoadWndHide();}');
	loader.SendRequest('/cms/action?async=exit',
		'_a[0][action]='+action+'&_a[0][id]='+o.getAttribute('var_id'), 'POST', f);
}
function EditAssoc(id) {
	var o = document.getElementById(id);
	window.open('/cms/assoc/?'+o.getAttribute('var_link'), '_blank', 
		'menubar=0,status=0,resizable=1,scrollbars=1,width=600,height=400');
}
function EditPage(id) {
	var o = document.getElementById(id);
	window.open('/cms/?'+o.getAttribute('var_link'), '_blank');
}

function EditTemplateProperties(id) {
	var o = document.getElementById(id);
	var oBlock = GetParentBlock(o);
	TPDlg(oBlock.getAttribute('template').replace(/^.*template=[\"\']([^\"\']*)[\"\'].*$/, '$1'));
}






function BlockBufClear() {
	var aCookie = document.cookie.split('; ');
	for (var i=0; i < aCookie.length; i++) {
		var aCrumb = aCookie[i].split('=');
		if (aCrumb[0].match(/^BLOCK_BUF/)) DelCookie(aCrumb[0]);
	}
}
function BlockBufCut(id) {
	var o = document.getElementById(id);
	var Id = o.getAttribute('var_id');
	SetCookie('BLOCK_BUF['+Id+']', Id);
}
function BlockBufPaste(id) {
	var o = document.getElementById(id);
	var aCookie = document.cookie.split('; ');
	var sBody = '';
	var re = /^BLOCK_BUF\[([\d\w]+)\]$/;
	for (var i=0; i < aCookie.length; i++) {
		var aCrumb = aCookie[i].split('=');
		if (aCrumb[0].match(re))
			sBody += '&_a['+i+'][action]=moveTo'
				+'&_a['+i+'][id]='+aCrumb[1]
				+'&_a['+i+'][moveto_id]='+o.getAttribute('var_id');
	}
	BlockBufClear();
	eval('var f = function() {Load("'+GetParentBlock(o).id+'"); CmsLoadWndHide();}');
	loader.SendRequest('/cms/action?async=exit', sBody, 'POST', f);
}
function BlockBufGetNum() {
	var result = 0;
	var aCookie = document.cookie.split('; ');
	for (var i=0; i < aCookie.length; i++) {
		var aCrumb = aCookie[i].split('=');
		if (aCrumb[0].match(/^BLOCK_BUF\[([\d\w]+)\]$/)) result++;
	}
	return result;
}

















/*
 * Context menu
 */

function GetParentVar(o) {
	do {
		try {if (o.getAttribute('var_id')!=null) return o;} catch (e) {;}
	} while (o = o.parentNode);
	return null;
}
function GetParentBlock(o) {
	do {
		try {if (o.getAttribute('template')) return o;} catch (e) {;}
	} while (o = o.parentNode);
	return null;
}
function GetVarsByVar(o) {
	var pb = GetParentBlock(o);
	if (!pb) return null;
	var result = new Array();
	var var_id = o.getAttribute('var_id');
	var aS = pb.getElementsByTagName('div');
	for (var i=0; i<aS.length; i++) if (aS[i].getAttribute('var_id')==var_id) result.push(aS[i]);
	return result;
}

function Changed(o) {return o.getAttribute('inittext')!=o.innerHTML;
	/*var result = false;
	var aVar = GetVarsByVar(o);
	for (var i=0; i<aVar.length; i++) if (aVar[i].getAttribute('inittext')!=aVar[i].innerHTML)
		{result = true; break;}
	return result;*/
}
function Published(o) {
	var version = o.getAttribute('var_version');
	var pt = o.getAttribute('var_publish_time');
	if (pt=='') pt = '0000-00-00 00:00:00';
	var st = o.getAttribute('server_time');
	if (pt>=version && pt<=st) return true;
	else return false;
}
function Hidden(o) {
	return o.getAttribute('var_publish_time')=='0000-00-00 00:00:00' || o.getAttribute('var_publish_time')=='';
}
function CanMove(o) {return o.getAttribute('param_orderby')=='';}
function CanInsert(o) {
	var t = GetParentBlock(o).getAttribute('template');
	return t.match(/\<t\:childrens/i) || t.match(/\<t\:siblings/i) || t.match(/can_insert\=[\"\']yes[\"\']/i);
}
function BlockCutted(o) {return GetCookie('BLOCK_BUF['+o.getAttribute('var_id')+']')!=null;}
function ParentBlockEditable(o) {return GetParentVar(GetParentBlock(o));}
function EditableSwitch(id) {
	if (window.cmsActiveBlock && id!=window.cmsActiveBlock.id && window.cmsActiveBlock.contentEditable.toString()=='true')
		EditableSwitch(window.cmsActiveBlock.id);
	o = document.getElementById(id);
	if (o) {
		var aVar = GetVarsByVar(o);
		for (var i=0; i<aVar.length; i++) {
			if (aVar[i].contentEditable.toString()!='true') {
				aVar[i].contentEditable = true;
				aVar[i].setAttribute('edit', 'yes');
			} else {
				aVar[i].contentEditable = false;
				aVar[i].setAttribute('edit', 'no');
			}
		}
		var p = GetParentVar(aVar[0].parentNode);
		if (aVar[0].contentEditable.toString()=='true') {
			if (p) p.setAttribute('edit', 'yes');
			cmsShowEditor(o.id);
			cmsFocus(o);
		} else {
			if (p) p.setAttribute('edit', 'no');
			cmsHideEditor();
		}
	}
}



function ContextSubMenuShow(iSourceId, iTargetId) {document.getElementById(iTargetId).style.visibility = 'visible';}
function ContextSubMenuHide(iTargetId) {document.getElementById(iTargetId).style.visibility = 'hidden';}

function ContextMenuCreate() {
	var cm = document.getElementById('CmsContextMenu');
	if (!cm) {
		cm = document.createElement('div');
		document.body.appendChild(cm);
		cm.id = 'CmsContextMenu';
		cm.style.position = 'absolute';
		cm.style.visibility = 'hidden';
		cm.style.zIndex = 99;
		cm.Show = function(o, x, y) {
			buf = '<div class="ToolMenu" style="padding: 3px; width: 170px;">\
<a style="border-bottom: 1px solid threedshadow;" \
href="javascript: EditableSwitch(\''+o.id+'\')">Редактировать</a>';
			
			buf += '<img src="/cms/img/toolicons/save.gif" />';
			if (!Changed(o)) buf += '<a>Сохранить</a>';
			else buf += '<a href="javascript: SavePage();">Сохранить</a>';
			
			
			buf += '<img src="/cms/img/cms/insert.gif" />';
			if (!CanInsert(o)) buf += '<a>Вставить</a>';
			else buf += '<a href="javascript: Insert(\''+o.id+'\');">Вставить</a>';

			buf += '<img src="/cms/img/cms/delete.gif" />';
			buf += '<a href="javascript: Delete(\''+o.id+'\');">Удалить</a>';

			buf += '<img src="/cms/img/cms/publish.gif" />';
			if (Published(o)) buf += '<a>Опубликовать</a>';
			else buf +='<a href="javascript: cmsSimpleAction(\'publish\', \''+o.id+'\');">Опубликовать</a>';

			buf += '<img src="/cms/img/cms/hide.gif" />';
			if (Hidden(o)) buf += '<a>Скрыть</a>';
			else buf += '<a href="javascript: cmsSimpleAction(\'hide\', \''+o.id+'\');">Скрыть</a>';

			if (CanMove(o)) {
				buf += '<a id="CmsContextMenuMoveSource"\
 style="padding-left: 24px; border-bottom: 1px solid threedshadow; width: 148px;" href="javascript:"\
 onmouseover="ContextSubMenuShow(\'CmsContextMenuMoveSource\', \'CmsContextMenuMoveTarget\')"\
 onmouseout="ContextSubMenuHide(\'CmsContextMenuMoveTarget\')">Переместить &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size: 8px">&#9658;</span></a>';
				buf += '<div id="CmsContextMenuMoveTarget" class="ToolMenu" \
style="position: absolute; visibility: hidden; width: 170px; left: 170px; top: 145px" \
onmouseover="ContextSubMenuShow(\'CmsContextMenuMoveSource\', \'CmsContextMenuMoveTarget\')" \
onmouseout="ContextSubMenuHide(\'CmsContextMenuMoveTarget\')">\
<a href="javascript: cmsSimpleAction(\'moveToFirst\', \''+o.id+'\');">Переместить в начало</a>\
<a href="javascript: cmsSimpleAction(\'moveUp\', \''+o.id+'\');">Переместить вверх</a>\
<a href="javascript: cmsSimpleAction(\'moveDown\', \''+o.id+'\');">Переместить вниз</a>\
<a href="javascript: cmsSimpleAction(\'moveToLast\', \''+o.id+'\');">Переместить в конец</a>\
</div>';
				if (BlockCutted(o)) buf += '<a>Вырезать</a>';
				else buf += '<a href="javascript: BlockBufCut(\''+o.id+'\');">Вырезать</a>';
				var bbn = BlockBufGetNum();
				if (bbn>0) buf += '<a href="javascript: BlockBufClear(\''
					+o.id+'\');">Очистить буфер ['+bbn+']</a><a href="javascript: BlockBufPaste(\''
					+o.id+'\');">Вставить из буфера</a>';
				else buf += '<a>Очистить буфер</a><a>Вставить из буфера</a>';
			} else buf += '';
			buf += '<a style=" border-top: 1px solid threedshadow;" href="javascript: EditPage(\''
				+o.id+'\');">Перейти в Backend</a>';
			buf += '<a href="javascript: EditAssoc(\''+o.id+'\');">Редактировать связки</a>';
			if (ParentBlockEditable(o))
				buf += '<a href="javascript: BlockProperties(\''+o.id+'\');">Свойства блока</a>';
			buf += '<a href="javascript: EditTemplateProperties(\''+o.id+'\');">Свойства шаблона</a>';
			buf += '</div>';
			this.innerHTML = buf;
			
			this.style.visibility = 'visible';
			this.style.left = parseInt(x) + document.documentElement.scrollLeft + 'px';
			this.style.top = parseInt(y) + document.documentElement.scrollTop + 'px';
			var MaxLeft = document.documentElement.scrollLeft+document.documentElement.clientWidth
				-this.offsetWidth;
			var MaxTop = document.documentElement.scrollTop+document.documentElement.clientHeight
				-this.offsetHeight;//this.firstChild.childNodes.length*26;
			var MinLeft = document.documentElement.scrollLeft;
			var MinTop = document.documentElement.scrollTop;
			if (parseInt(this.style.left) > MaxLeft) this.style.left = MaxLeft + 'px';
			if (parseInt(this.style.top) > MaxTop) this.style.top = MaxTop + 'px';
			if (parseInt(this.style.left) < MinLeft) this.style.left = MinLeft + 'px';
			if (parseInt(this.style.top) < MinTop) this.style.top = MinTop + 'px';
		}
		cm.Visible = function() {return this.style.visibility=='visible';}
		cm.Hide = function() {
			this.style.visibility = 'hidden';
			var CmsContextMenuMoveTarget = document.getElementById('CmsContextMenuMoveTarget');
			if (CmsContextMenuMoveTarget) CmsContextMenuMoveTarget.style.visibility = 'hidden';
		}
	}
	return cm;
}
function ContextMenuOpen(e, o) {
	if (!e) e = event;
	if (!o) o = this;
	if (o.getAttribute('edit')=='yes') return true;
	var cm = ContextMenuCreate();
	cm.Show(o, e.clientX, e.clientY);
	stopPropagation(e);
	return false;
}
function ContextMenuClose() {
	var cm = ContextMenuCreate();
	cm.Hide();
}
cmsAddEvent(document, 'mouseup', ContextMenuClose);

/*function OperaClick(e) {
	if (e.ctrlKey) ContextMenuOpen(e, this);
}*/


function AltContextMenuOpenDelayed(e) {
	if (!e) e = event;
	if (this.getAttribute('edit')=='yes') return true;
	window.oAltContextMenuTarget = this;
	if (window.iAltContextMenuTimer) clearTimeout(window.iAltContextMenuTimer);
	window.iAltContextMenuTimer = setTimeout('AltContextMenuOpen(null, window.oAltContextMenuTarget)', 200);
	stopPropagation(e);
	return false;
}
function AltContextMenuOpen(e, o) {
	//if (!e) e = event;
	if (!o) o = this;
	var acm = document.getElementById('CmsAltContextMenu');
	if (!acm) {
		acm = document.createElement('div');
		document.body.appendChild(acm);
		acm.id = 'CmsAltContextMenu';
		acm.className = 'AltContextMenu'
		acm.style.position = 'absolute';
		acm.style.visibility = 'hidden';
		acm.style.zIndex = 98;
		acm.onclick = function(e) {
			if (!e) e = event;
			ContextMenuOpen(e, this._target);
		}
		acm.onmouseover = function(e) {
			if (!e) e = event;
			AltContextMenuOpen(e, this._target);
		}
		acm.onmouseout = AltContextMenuCloseDelayed;
		acm.innerHTML = '&#9660;';
	}
	if (window.iAltContextMenuTimer) {
		clearTimeout(window.iAltContextMenuTimer);
		window.iAltContextMenuTimer = null;
	}
	acm._target = o;
	acm.style.visibility = 'visible';
	acm.style.left = cmsGetLeft(o) + 'px';
	acm.style.top = (cmsGetTop(o) - acm.offsetHeight) + 'px';
}
function AltContextMenuCloseDelayed() {
	if (window.iAltContextMenuTimer) clearTimeout(window.iAltContextMenuTimer);
	window.iAltContextMenuTimer = setTimeout(AltContextMenuClose, 1000);
}
function AltContextMenuClose() {
	var acm = document.getElementById('CmsAltContextMenu');
	if (acm) acm.style.visibility = 'hidden';
}










function BlockProperties(id, bInsert) {
	if (!bInsert) {
		bInsert = false;
		var oBlock = GetParentBlock(document.getElementById(id));
	}
	var oWnd = window.open('about:blank', '_blank', 'menubar=0,status=0,resizable=1,scrollbars=1,width=600,height=500');
	oWnd.document.writeln('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
	oWnd.document.writeln('<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><script type="text/javascript" src="/cms/js/ajax.js"></script><script type="text/javascript" src="/cms/js/cms.js"></script><link type="text/css" rel="stylesheet" href="/cms/css/cms.css" /><title>Свойства блока</title></head><body><span id="oEditedBlock">'
		+(bInsert ? '<temp></temp>' : oBlock.getAttribute('template').substr(4))
		+'</span><form id="FormBlockProperties"><table cellpadding="0" cellspacing="3" width="100%"><tr><th colspan="2">Основные свойства блока</th></tr>');
	var temp = document.createElement('temp');
	temp.innerHTML = bInsert ? '<temp></temp>' : oBlock.getAttribute('template').substr(4);
	var oEditedBlock = temp.firstChild;
	//var oEditedBlock = oWnd.document.getElementById('oEditedBlock').firstChild;
	if (bInsert) oWnd.document.writeln('<tr><td>Тип блока</td><td><select name="blockType"><option value="block">Блок</option><option value="childrens">Дочерние</option></select></td></tr>');
	oWnd.document.writeln('<tr><td>Источник шаблона</td><td><select name="src" id="SelectTemplateSrc" onchange="LoadTemplateList(\'SelectTemplate\', this.options[this.selectedIndex].value, \''+(oEditedBlock.getAttribute('template') ? oEditedBlock.getAttribute('template') : '')+'\');"><option value="">По умолчанию</option>');
	var req = loader.initXMLHTTPRequest();
	req.open('GET', '/cms/ajax/ins?i=template_opts&s='+encodeURIComponent(oEditedBlock.getAttribute('src') ? oEditedBlock.getAttribute('src') : ''), false);
	req.send(null);
	oWnd.document.writeln(req.responseText);
	oWnd.document.writeln('</select><br /><a href="/cms/tp" target="_blank" onclick="TPDlg(document.getElementById(\'SelectTemplateSrc\').value); return false;">[свойства шаблона]</a></td></tr>');
	if (bInsert) {
		oWnd.document.writeln('<tr><td>Установить корневой шаблон</td><td><input type="checkbox" name="SetTemplateSrc" /></td></tr>');
		oWnd.document.writeln('<tr><td>Установить шаблон дочерних</td><td><input type="checkbox" name="SetChildrensTemplateSrc" /></td></tr>');
	}
	var req = loader.initXMLHTTPRequest();
	req.open('GET', '/cms/ajax/ins?i=templates&src='+encodeURIComponent(oEditedBlock.getAttribute('src') ? oEditedBlock.getAttribute('src') : '')+'&s='+(oEditedBlock.getAttribute('template') ? oEditedBlock.getAttribute('template') : ''), false);
	req.send(null);
	oWnd.document.writeln('<tr><td>Название шаблона</td><td><select name="template" id="SelectTemplate">'+req.responseText+'</select></td></tr>');
	oWnd.document.writeln('<tr><td>Количество записей на странице</td><td><input name="_length" type="text" value="'+(oEditedBlock.getAttribute('length') ? oEditedBlock.getAttribute('length') : '')+'" /></td></tr>');
	oWnd.document.writeln('<tr><td>Фиксированный номер страницы</td><td><input name="page" type="text" value="'+(oEditedBlock.getAttribute('page') ? oEditedBlock.getAttribute('page') : '')+'" /></td></tr>');
	oWnd.document.writeln('<tr><td>Сортировка</td><td><input name="orderby" type="text" value="'+(oEditedBlock.getAttribute('orderby') ? oEditedBlock.getAttribute('orderby') : '')+'" title="Используйте SQL-синтаксис:\n`имя_поля` [ASC|DESC], ...\nПо умолчанию:\n`n` DESC, `create_time` DESC" /></td></tr>');
	oWnd.document.writeln('<tr><th colspan="2">Дополнительные свойства блока</th></tr>');
	oWnd.document.writeln('<tr><td>id</td><td><input id="id" name="id" type="text" value="'+(oEditedBlock.getAttribute('id') ? oEditedBlock.getAttribute('id') : '')+'" /> <a href="/cms/map" target="_blank" onclick="Select(\'FormBlockProperties\', \'id\', \'id\'); return false;"><button>Выбрать</button></a></td></tr>');
	oWnd.document.writeln('<tr><td>link</td><td><input id="link" name="link" type="text" value="'+(oEditedBlock.getAttribute('link') ? oEditedBlock.getAttribute('link') : '')+'" /> <a href="/cms/map" target="_blank" onclick="Select(\'FormBlockProperties\', \'link\', \'link\'); return false;"><button>Выбрать</button></a></td></tr>');
	oWnd.document.writeln('<tr><td>parent_id</td><td><input id="parent_id" name="parent_id" type="text" value="'+(oEditedBlock.getAttribute('parent_id') ? oEditedBlock.getAttribute('parent_id') : '')+'" /> <a href="/cms/map" target="_blank" onclick="Select(\'FormBlockProperties\', \'parent_id\', \'id\'); return false;"><button>Выбрать</button></a></td></tr>');
	oWnd.document.writeln('<tr><td>parent_link</td><td><input id="parent_link" name="parent_link" type="text" value="'+(oEditedBlock.getAttribute('parent_link') ? oEditedBlock.getAttribute('parent_link') : '')+'" /> <a href="/cms/map" target="_blank" onclick="Select(\'FormBlockProperties\', \'parent_link\', \'link\'); return false;"><button>Выбрать</button></a></td></tr>');
	oWnd.document.writeln('<tr><td>foreign_id</td><td><input id="foreign_id" name="foreign_id" type="text" value="'+(oEditedBlock.getAttribute('foreign_id') ? oEditedBlock.getAttribute('foreign_id') : '')+'" /> <a href="/cms/map" target="_blank" onclick="Select(\'FormBlockProperties\', \'foreign_id\', \'id\'); return false;"><button>Выбрать</button></a></td></tr>');
	oWnd.document.writeln('<tr><td>foreign_link</td><td><input id="foreign_link" name="foreign_link" type="text" value="'+(oEditedBlock.getAttribute('foreign_link') ? oEditedBlock.getAttribute('foreign_link') : '')+'" /> <a href="/cms/map" target="_blank" onclick="Select(\'FormBlockProperties\', \'foreign_link\', \'link\'); return false;"><button>Выбрать</button></a></td></tr>');
	oWnd.document.writeln('<tr><td>sql</td><td><input name="sql" type="text" value="'+(oEditedBlock.getAttribute('sql') ? oEditedBlock.getAttribute('sql') : '')+'" /></td></tr>');
	oWnd.document.writeln('<tr><td>xpath</td><td><input name="xpath" type="text" value="'+(oEditedBlock.getAttribute('xpath') ? oEditedBlock.getAttribute('xpath') : '')+'" /></td></tr>');
	oWnd.document.writeln('<tr><td>checkVersion</td><td><input name="checkVersion" type="text" value="'+(oEditedBlock.getAttribute('checkVersion') ? oEditedBlock.getAttribute('checkVersion') : '')+'" /></td></tr>');
	if (bInsert) oWnd.document.writeln('<tr><td><button onclick="window.opener.InsertBlock(window, this.form); window.close();">&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;</button></td></tr>');
	else oWnd.document.writeln('<tr><td><button onclick="window.opener.BlockPropertiesSave(\''+oBlock.id+'\', window, this.form); window.close();">&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;</button></td></tr>');
	oWnd.document.writeln('</table></form></body></html>');
}
function BlockPropertiesSave(sId, oWnd, oForm) {
	var oBlock = document.getElementById(sId);
	var oEditedBlock = oWnd.document.getElementById('oEditedBlock').firstChild;
	var aName = new Array('src', 'template', '_length', 'page', 'orderby', 'id', 'link', 'parent_id',
		'parent_link', 'foreign_id', 'foreign_link', 'sql', 'xpath', 'checkVersion');
	for (var i=0; i<aName.length; i++) {
		var e = oForm.elements[aName[i]];
		if (e.value!='') oEditedBlock.setAttribute(e.name=='_length' ? 'length' : e.name, e.value);
		else oEditedBlock.removeAttribute(e.name);
	}
	oBlock.setAttribute('template', 'xml:'+oEditedBlock.parentNode.innerHTML);
	var pv = GetParentVar(oBlock);
	if (pv)	SavePage();
}
/*function InsertBlock(oWnd, oForm) {
	var o = GetParentVar(window.CmsActiveElement); window.CmsActiveElement.setActive();
	var oBlock = document.createElement('T:'+oForm.elements['blockType'].value);
	oBlock.setAttribute('xmlns:T', '/templates/ns');
	var aName = new Array('src', 'template', 'length', 'page', 'orderby', 'id', 'link', 'parent_id', 'parent_link', 'foreign_id', 'foreign_link', 'sql', 'xpath', 'checkVersion');
	var sBody = '';
	if (oForm.elements['SetTemplateSrc'].checked || oForm.elements['SetChildrensTemplateSrc'].checked) {
		sBody = 'Save=Save&Vars[id]='+o.getAttribute('var_id');
		if (oForm.elements['SetTemplateSrc'].checked) sBody += '&Vars[template_src]='+encodeURIComponent(oForm.elements['src'].value);
		if (oForm.elements['SetChildrensTemplateSrc'].checked) sBody += '&Vars[childrens_template_src]='+encodeURIComponent(oForm.elements['src'].value);
	}
	oBlock.setAttribute('id', o.getAttribute('var_id'));
	for (var i=0; i<aName.length; i++) {
		var e = oForm.elements[aName[i]];
		if (e.value!='') oBlock.setAttribute(e.name, e.value);
	}
	var date = new Date();
	var sDomId = "CmsNew" + date.getTime();
	var text = '<div id="'+sDomId+'"></div>';
	var oRng = document.selection.createRange();
	oRng.pasteHTML(text);
	oRng.collapse(true);
	oRng.select();
	document.getElementById(sDomId).setAttribute('template', oBlock.outerHTML.replace(/id=([\w\d]+)/, 'id="$1"')+'</'+oBlock.tagName+'>');
	Load(sDomId, null, sBody, null, null, 'var o = GetParentVar(window.CmsActiveElement); var_onkeydown(o); var_onkeyup(o);');
	var o = GetParentVar(window.CmsActiveElement); var_onkeydown(o); var_onkeyup(o);
}*/
function PageMasterReturn(sSrc, bSys) {
	var aSpan = document.getElementsByTagName('div');
	for (var i=0; i<aSpan.length; i++) if (aSpan[i].getAttribute('template')
		&& aSpan[i].getAttribute('template').match(/^xml\:\<t\:main/i)) {
		var sLink = aSpan[i].getAttribute('link')==''
			? window.location.pathname : aSpan[i].getAttribute('link');
		var sBody = '_a[0][action]=save';
		if (aSpan[i].getAttribute('block_var_id')!='')
			sBody += '&_a[0][id]='+aSpan[i].getAttribute('block_var_id');
		else {
			sBody += '&_a[0][vars][create_time]=NOW&&_a[0][parent_id]=DEFAULT'
			sBody += '&_a[0][vars][link]='+encodeURIComponent(sLink);
		}
		if (bSys) sBody += '&_a[0][vars][template_src]='+encodeURIComponent(sSrc);
		sBody += '&_a[0][vars][childrens_template_src]='+encodeURIComponent(sSrc);
		sBody += '&_a[0][vars][content]='
			+encodeURIComponent('<t:block xmlns:t="/templates/ns" src="'+sSrc+'" />');
		
		CmsLoadWndShow();
		eval('var f = function() {Load("'+aSpan[i].id+'"); CmsLoadWndHide();}');
		loader.SendRequest('/cms/action?async=exit', sBody, 'POST', f);
		break;
	}
}



















/*
 * Color picker
 */

function cmsColorPickerCreate() {
	var cp = document.createElement('div');
	document.body.appendChild(cp);
	cp.id = 'cmsColorPicker';
	cp.style.position = 'fixed';
	cp.style.visibility = 'hidden';
	cp.style.zIndex = 102;

	var colors = new Array(
'#000','#111','#222','#333','#444','#555',   '#666','#777','#888','#999','#aaa','#bbb',   '#ccc','#ddd','#eee','#fff','transparent','',

'#f00','#f33','#f66','#f99','#fcc','#fff',   '#0f0','#3f3','#6f6','#9f9','#cfc','#fff',   '#00f','#33f','#66f','#99f','#ccf','#fff',
'#c00','#c33','#c66','#c99','#ccc','#cff',   '#0c0','#3c3','#6c6','#9c9','#ccc','#fcf',   '#00c','#33c','#66c','#99c','#ccc','#ffc',
'#900','#933','#966','#999','#9cc','#9ff',   '#090','#393','#696','#999','#c9c','#f9f',   '#009','#339','#669','#999','#cc9','#ff9',
'#600','#633','#666','#699','#6cc','#6ff',   '#060','#363','#666','#969','#c6c','#f6f',   '#006','#336','#666','#996','#cc6','#ff6',
'#300','#333','#366','#399','#3cc','#3ff',   '#030','#333','#636','#939','#c3c','#f3f',   '#003','#333','#663','#993','#cc3','#ff3',
'#000','#033','#066','#099','#0cc','#0ff',   '#000','#303','#606','#909','#c0c','#f0f',   '#000','#330','#660','#990','#cc0','#ff0'
	);

	var table = document.createElement('table');
	table.cellSpacing = 0;
	table.cellPaddinf = 0;
	for (var i=0; i<colors.length; i++) {
		if (i % 18 == 0) var tr = table.insertRow(-1);
		var td = tr.insertCell(-1);
		td.style.backgroundColor = colors[i];
		td.style.cursor = 'pointer';
		td.onmouseover = function() {
			var cp = this.parentNode.parentNode.parentNode.parentNode
			cp._target.value = this.style.backgroundColor;
			cp._target.style.backgroundColor = this.style.backgroundColor;
		};
		td.onmousedown = function() {
			cmsColorPickerHide();
			return false;
		}
	}
	cp.appendChild(table);

	return cp;
}

function cmsColorPickerShow(o) {
	var cp = document.getElementById('cmsColorPicker');
	if (!cp) cp = cmsColorPickerCreate();
	if (cp.style.visibility=='hidden') {
		cp._target = o;
		cp.style.left = cmsGetLeft(o) + 'px';
		cp.style.top = cmsGetTop(o) + o.offsetHeight - 1 + 'px';
		cp.style.visibility = 'visible';

		if (!window.XMLHttpRequest) {// IE6
			cp.style.position = 'absolute';
		}
	}
}

function cmsColorPickerHide() {
	var cp = document.getElementById('cmsColorPicker');
	if (cp) cp.style.visibility = 'hidden';
}








/*
 * Cut image
 */

function cutimage_onload() {
	var o = document.getElementById('cutimage_imgtd');
	o.innerHTML = '<img src="/cms/fb/vi/?w=200&amp;h=200&amp;m=&amp;url='+window.cutimage_url+'" />';
	o.firstChild.onload = cutimage_img_onload;

	document.onselectstart = function () {return false;}
}

function cutimage_img_onload() {
	var o = document.getElementById('cutimage_imgtd');
	var img = o.firstChild;
	var width = img.width;
	var height = img.height;
	if (width>=img.width) {
		var width = img.width;
		var height = parseInt((img.width / window.cutimage_width) * window.cutimage_height);
        }
	if (height>=img.height) {
		var height = img.height;
		var width = parseInt((img.height / window.cutimage_height) * window.cutimage_width);
        }
	var o = document.getElementById('cutimage_div');
	if (!o) {
		o = document.createElement('cutimage_div');
		o.id = 'cutimage_div';
		o.onmousedown = cutimage_div_mousedown;
		document.onmouseup = cutimage_div_mouseup;
		o.onmousemove = cutimage_div_mousemove;
		if (!window.XMLHttpRequest) {// IE6
			o.style.position = 'absolute';
		} else {// FF
			o.style.position = 'fixed';
		}
		o.style.zIndex = '102';
		o.style.border = '2px solid Red';
		o.style.backgroundImage = 'url("/cms/img/1x1transparent.gif")';
		document.body.appendChild(o);
	}
	o.style.visibility = 'visible';
	o.style.width = width+'px';
	o.style.height = height+'px';
	o.style.left = cmsGetLeft(img)-2+'px';
	o.style.top = cmsGetTop(img)-2+'px';

	o.innerHTML = '<div style="background-color: Red; width: 5px; height: 5px; float: right; margin-top:'+
		(height-5)+'px; cursor: se-resize;" onmousedown="cutimage_div_mousedown2(event)"></div>';
}

function cutimage_div_mousedown(e) {
	if (!e) var e = event;
	var o = document.getElementById('cutimage_div');
	window.cutimage__div_left = e.clientX - cmsGetLeft(o);
	window.cutimage__div_top = e.clientY - cmsGetTop(o);
	window.cutimage__div_can_move = true;
	window.cutimage__div_can_resize = false;
}

function cutimage_div_mousedown2(e) {
	if (!e) var e = event;
	stopPropagation(e);
	var o = document.getElementById('cutimage_div');
	window.cutimage__div_left = cmsGetLeft(o);
	window.cutimage__div_top = cmsGetTop(o);
	window.cutimage__div_can_move = false;
	window.cutimage__div_can_resize = true;
}

function cutimage_div_mouseup() {
	window.cutimage__div_can_move = false;
	window.cutimage__div_can_resize = false;
}

function cutimage_div_mousemove(e) {
	if (!e) var e = event;
	if (window.cutimage__div_can_move) {
		var o = document.getElementById('cutimage_imgtd');
		var img = o.firstChild;
		var o = document.getElementById('cutimage_div');

		var left = e.clientX - window.cutimage__div_left;
		var top = e.clientY - window.cutimage__div_top;

		var min_left = cmsGetLeft(img) - 2;
		var min_top = cmsGetTop(img) - 2;

		var max_left = min_left + img.width - parseInt(o.style.width);
		var max_top = min_top + img.height - parseInt(o.style.height);

		if (left>max_left) left = max_left;
		if (top>max_top) top = max_top;

		if (left<min_left) left = min_left;
		if (top<min_top) top = min_top;

		o.style.left = left+'px';
		o.style.top = top+'px';
        }

	if (window.cutimage__div_can_resize) {
		var o = document.getElementById('cutimage_imgtd');
		var img = o.firstChild;
		var o = document.getElementById('cutimage_div');

		var width = e.clientX - window.cutimage__div_left;
		//var height = e.clientY - window.cutimage__div_top;
		var height = parseInt((width / window.cutimage_width) * window.cutimage_height);

		var min_width = 10;
		var min_height = 10;

		var max_width = img.width - window.cutimage__div_left + cmsGetLeft(img) - 2;
		var max_height = img.height - window.cutimage__div_top + cmsGetTop(img) - 2;

		if (width>max_width) {
			width = max_width;
			height = parseInt((width / window.cutimage_width) * window.cutimage_height);
                }
		if (height>max_height) {
			height = max_height;
			width = parseInt((img.height / window.cutimage_height) * window.cutimage_width);
                }

		if (width<min_width) {
			width = min_width;
			height = parseInt((width / window.cutimage_width) * window.cutimage_height);
                }
		if (height<min_height) {
			height = min_height;
			width = parseInt((img.height / window.cutimage_height) * window.cutimage_width);
                }

		o.style.width = width+'px';
		o.style.height = height+'px';

		o.firstChild.style.marginTop = height - 5 + 'px';
        }
}

function cutimage_OK() {
	var o = document.getElementById('cutimage_imgtd');
	var img = o.firstChild;
	var div = document.getElementById('cutimage_div');

	var sBody = 'url='+window.cutimage_url
		+'&src_width='+img.width
		+'&src_height='+img.height
		+'&cut_width='+parseInt(div.style.width)
		+'&cut_height='+parseInt(div.style.height)
		+'&cut_left='+(div.offsetLeft - cmsGetLeft(img) + 2)
		+'&cut_top='+(div.offsetTop - cmsGetTop(img) + 2)
		+'&iWidth='+window.cutimage_width
		+'&iHeight='+window.cutimage_height;

	loader.SendRequest('/cms/fb/ci', sBody, 'POST', cutimage_fOnLoad);
}

function cutimage_fOnLoad() {
	var o = document.getElementById('cutimage_div');
	o.style.visibility = 'hidden';
	document.onselectstart = null;
	modalDlg.close();
}
