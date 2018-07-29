function GET(name) {
    var p = name + "=";
    var si = document.URL.indexOf(p);
    if (si == -1)
        return null;
    var ei = document.URL.indexOf("&", si + p.length);
    if (ei == -1)
        ei = document.URL.length;
    return unescape(document.URL.substring(si + p.length, ei));
}

function SetCookie(sName, sValue, dDate) {document.cookie = sName + "=" + sValue + ";" + (dDate!=null ? " expires=" + dDate + ";" : "") + " path=/;";}
function DelCookie(sName) {document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT; path=/;";}
function GetCookie(sName) {
	var aCookie = document.cookie.split('; ');
	for (var i=0; i < aCookie.length; i++) {
		var aCrumb = aCookie[i].split('=');
		if (sName == aCrumb[0]) return unescape(aCrumb[1]);
	}
	return null;
}

function GetDate(date) {
	if (!date) date = new Date();
	var y = date.getFullYear();
	var m = (date.getMonth()+1).toString(); if (m.length==1) m = '0'+m;
	var d = date.getDate().toString(); if (d.length==1) d = '0'+d;
	var h = date.getHours().toString(); if (h.length==1) h = '0'+h;
	var i = date.getMinutes().toString(); if (i.length==1) i = '0'+i;
	var s = date.getSeconds().toString(); if (s.length==1) s = '0'+s;
	return y+'-'+m+'-'+d+' '+h+'-'+i+'-'+s;
}

function Refresh() {window.location.reload();}

function Up() {
	var d = GET('d');
	window.location = '?d='+d.replace(/\/[^\/]+$/, '');
}

function SelectAll() {
	var aInput = document.getElementsByTagName('input');
	var AllSelected = true;
	for (var i=0; i<aInput.length; i++)	if (aInput[i].className=='select') if (aInput[i].checked==false) AllSelected = false;
	for (var i=0; i<aInput.length; i++)	if (aInput[i].className=='select') aInput[i].checked = !AllSelected;
}

function View(sUrl) {
	var ext = sUrl.replace(/.*\.(\w+)$/, '$1').toLowerCase();
	oView = document.getElementById('View');
	oView.innerHTML = '';
	switch (ext) {
		case 'gif': case 'jpg': case 'jpeg': case 'png': oView.innerHTML += '<table id="ViewImg" class="ViewImg" cellspacing="0" cellpadding="0"><tr><td valign="middle" align="center"><img src="'+sUrl+'" alt="" /></td></tr></table>'; break;
		case 'swf': oView.innerHTML += '<table class="ViewImg" cellspacing="0" cellpadding="0"><tr><td valign="middle" align="center"><object align="middle" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"><param name="allowScriptAccess" value="sameDomain" /><param name="movie" value="'+sUrl+'" /><param name="quality" value="high" /><param name="wmode" value="transparent"/><embed src="'+sUrl+'" align="middle" quality="high" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" /></object></td></tr></table>'; break;
	}
}

function Click(sUrl) {
	if (window.opener && window.opener.fbReturn) {
		window.opener.fbReturn(sUrl);
		window.close();
        }
}

function Upload() {
	var oWnd = window.open('about:blank', '_blank', 'menubar=0,status=0,resizable=1,scrollbars=0,width=200,height=200');
	oWnd.document.write('<html><head><title>Загрузить файлы</title><link type="text/css" rel="stylesheet" href="/cms/css/cms.css" /></head><body><form enctype="multipart/form-data" action="/cms/fb/action?a=upload" method="post"><input type="hidden" name="MAX_FILE_SIZE" value="10000000" /><input type="hidden" name="d" value="'+GET('d')+'" /><input name="userfile" type="file" /><button onclick="if (!window._i) window._i = 1; else window._i++; var o = document.createElement(\'input\'); o.type = \'file\'; o.name = \'userfile\'+window._i; this.form.insertBefore(o, this);">Добавить</button> <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;" /></form></body>');
}

function NewDir() {
	var oWnd = window.open('about:blank', '_blank', 'menubar=0,status=0,resizable=1,scrollbars=0,width=200,height=50');
	oWnd.document.write('<html><head><title>Создать Папку</title><link type="text/css" rel="stylesheet" href="/cms/css/cms.css" /></head><body><form action="/cms/fb/action?a=newdir" method="post"><input type="hidden" name="d" value="'+GET('d')+'" /><input name="name" type="text" /><input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;" /></form></body>');
}

function GetSelectedFileNames() {
	var result = new Array();
	var aInput = document.getElementsByTagName('input');
	for (var i=0; i<aInput.length; i++) if (aInput[i].type=='checkbox' && aInput[i].checked) result.push(aInput[i].name);
	return result;
}

function Delete() {
	var aFileName = GetSelectedFileNames();
	var sBody = '';
	for (var i=0; i<aFileName.length; i++) sBody += '&names[]='+encodeURIComponent(aFileName[i]);
	if (sBody=='') alert('Файлы не выбраны');
	else if (confirm("Вы уверены что хотите удалить выбранные папки и файлы?\nВсе вложеные файлы и папки также будут удалены.")) loader.SendRequest('/cms/fb/action?a=delete', 'd='+GET('d')+sBody, 'POST', ActionResult);
}

function ActionResult(text) {if (text!='') alert(text); Refresh();}

function FbBufClear() {
	var aCookie = document.cookie.split('; ');
	for (var i=0; i < aCookie.length; i++) {
		var aCrumb = aCookie[i].split('=');
		if (aCrumb[0].match(/^FB_BUF(_CUT)?(_COPY)?\[(\d+)\]$/)) DelCookie(aCrumb[0]);
	}
}

function Cut() {
	FbBufClear();
	var aFileName = GetSelectedFileNames();
	for (var i=0; i<aFileName.length; i++) SetCookie('FB_BUF_CUT['+i+']', aFileName[i]);
}

function Copy() {
	FbBufClear();
	var aFileName = GetSelectedFileNames();
	for (var i=0; i<aFileName.length; i++) SetCookie('FB_BUF_COPY['+i+']', aFileName[i]);
}

function Paste() {
	loader.SendRequest('/cms/fb/action?a=paste', 'd='+GET('d'), 'POST', ActionResult);
}

function Zip() {
	var oWnd = window.open('about:blank', '_blank', 'menubar=0,status=0,resizable=1,scrollbars=0,width=200,height=50');
	var aFileName = GetSelectedFileNames();
	var names = '';
	for (var i=0; i<aFileName.length; i++) names += '<input type="hidden" name="names[]" value="'+aFileName[i]+'" />';
	oWnd.document.write('<html><head><title>Создать Архив</title><link type="text/css" rel="stylesheet" href="/cms/css/cms.css" /></head><body><form action="/cms/fb/action?a=zip" method="post"><input type="hidden" name="d" value="'+GET('d')+'" />'+names+'<input name="name" type="text" value="'+GetDate()+'.zip" /><input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;" /></form></body>');
}

function Unzip() {
	var aFileName = GetSelectedFileNames();
	if (aFileName.length!=1) alert('Нужно отметить только один файл');
	else if (confirm("При совпадении имен файлы будут заменены. Подтверждаете разархивацию?")) loader.SendRequest('/cms/fb/action?a=unzip', 'd='+GET('d')+'&name='+encodeURIComponent(aFileName[0]), 'POST', ActionResult);
}

function Download() {
	var aFileName = GetSelectedFileNames();
	if (aFileName.length!=1) alert('Нужно отметить только один файл');
	else window.open(aFileName[0], '_self');
}

function Props() {
	var aFileName = GetSelectedFileNames();
	var sBody = '';
	for (var i=0; i<aFileName.length; i++) sBody += '&names[]='+encodeURIComponent(aFileName[i]);
	if (sBody=='') alert('Файлы не выбраны');
	else loader.SendRequest('/cms/fb/action?a=getprops', 'd='+GET('d')+sBody, 'POST', PropsReturn);
}

function PropsReturn(text, xml) {
	var filename = xml.documentElement.getAttribute('filename');
	var filesize = xml.documentElement.getAttribute('filesize');
	var chmod = xml.documentElement.getAttribute('chmod');
	var aFileName = GetSelectedFileNames();
	var oWnd = window.open('about:blank', '_blank', 'menubar=0,status=0,resizable=1,scrollbars=0,width=200,height=50');
	oWnd.document.write('<html><head><title>Свойства</title><link type="text/css" rel="stylesheet" href="/cms/css/cms.css" /></head><body><form action="/cms/fb/action?a=setprops" method="post"><input type="hidden" name="d" value="'+GET('d')+'" />');
	if (filename!='') oWnd.document.write('<input type="hidden" name="name" value="'+aFileName[0]+'" />Имя файла: <input type="text" name="newname" value="'+filename+'" /><br />');
	else for (var i=0; i<aFileName.length; i++) oWnd.document.write('<input type="hidden" name="names[]" value="'+aFileName[i]+'" />');
	oWnd.document.write('Размер: '+filesize+' б<br />Chmod: <input type="text" name="chmod" value="'+chmod+'" /><input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;" /></form></body>');
}