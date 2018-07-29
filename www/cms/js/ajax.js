var loader = {
	iCompletedReq: 0,
	aReq: Array(),
	SendRequest: function(sUrl, oParams, sMethod, fOnLoad, fOnError) {
		if (!sMethod) sMethod = 'GET';
		var i = this.aReq.length;
		this.aReq[i] = new Object();
		this.aReq[i].req = this.initXMLHTTPRequest();
		if (this.aReq[i]) {
			eval('var fn = function() {var oReq = loader.aReq['+i+']; if (oReq.req.readyState==4) {\
loader.iCompletedReq++; if (oReq.req.status==200 || oReq.req.status==0) \
oReq.onload(oReq.req.responseText, oReq.req.responseXML); \
else oReq.onerror(oReq.req.readyState, oReq.req.status, oReq.req.getAllResponseHeaders());}}');
			this.aReq[i].req.onreadystatechange = fn;
			this.aReq[i].onload = (fOnLoad) ? fOnLoad : this.defaultOnLoad;
			this.aReq[i].onerror = (fOnError) ? fOnError : this.defaultOnError;
			this.aReq[i].req.open(sMethod, sUrl, true);
			this.aReq[i].req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			if (document.cookie) this.aReq[i].req.setRequestHeader('Cookie', document.cookie);
			this.aReq[i].req.send(oParams);
		}
	},
	initXMLHTTPRequest: function() {
		if (window.XMLHttpRequest) {
			try {return new XMLHttpRequest();} catch (e) {;}
		} else if (window.ActiveXObject) {
			try {return new ActiveXObject('Msxml2.XMLHTTP');} catch (e) {;}
			try {return new ActiveXObject('Microsoft.XMLHTTP');} catch (e) {;}
		}
		return null;
	},
	defaultOnLoad: function(sResponseText, oResponseXML) {
		alert(sResponseText);
	},
	defaultOnError: function(sReadyState, sStatus, sHeaders) {
		alert("error fetching data"+"\n\nreadyState:"+sReadyState+"\nstatus: "+sStatus+"\nheaders:\n"+sHeaders);
	}
}

function CmsExecScripts(id) {
	var o = document.getElementById(id);
	var scripts = o.getElementsByTagName('script');
	for (var i=0; i<scripts.length; i++) eval(scripts[i].innerHTML.replace(/cmsAddEditor/, 'cmsAddEditor2'));
}

function CmsLoadWndShow() {
	if (window.modalDlg) modalDlg.open('/cms/includes/CmsActWnd.html', null, 250, 150);
}
function CmsLoadWndHide() {
	if (window.modalDlg) modalDlg.close();
}
function Load(sId, sUrl, sBody, sEval, bNoReload, sEvalPost) {
	//if (!sUrl) sUrl = '/cms/ajax';
	if (!sUrl) sUrl = document.getElementById(sId).getAttribute('link')
		? document.getElementById(sId).getAttribute('link') : '/';
	if (!sBody) sBody = '';
	if (!sEval) sEval = '';
	if (!bNoReload) bNoReload = null;
	if (!sEvalPost) sEvalPost = '';
	if (window.modalDlg) document.getElementById(sId).innerHTML = '<img src="/cms/img/cms/load.gif" />';
	var buf = "var fn = function(text) {"+sEval;
	if (!bNoReload) buf += "document.getElementById('"+sId+"').innerHTML = text;";
	buf += " CmsExecScripts('"+sId+"');";
	buf += " if (loader.iCompletedReq==loader.aReq.length) CmsLoadWndHide();";
	buf += sEvalPost+"}";
	eval(buf);
	var template = document.getElementById(sId).getAttribute('template');
	document.getElementById(sId).setAttribute('link', sUrl);
	if (sBody) CmsLoadWndShow();
	loader.SendRequest(sUrl, 'DomId='+sId+'&template='+encodeURIComponent(template)+'&'+sBody, 'POST', fn, null);
}

function LoadParentBlock(o, sBody) {
	oBlock = o;
	while (oBlock = oBlock.parentNode) if (oBlock.getAttribute('template')) break;
	sLink = oBlock.getAttribute('link');
	Load(oBlock.id, sLink, sBody);
}

function AsyncSubmitForm(oForm) {
	oBlock = oForm;
	while (oBlock = oBlock.parentNode) if (oBlock.getAttribute('template')) break;
	eval("var fn = function(text) {document.getElementById('"+oBlock.id+"').innerHTML = text;}");
	var sBody = '';
	for (var i=0; i<oForm.elements.length; i++) {
		if (sBody!='') sBody += '&';
		sBody += oForm.elements[i].name+'='+oForm.elements[i].value;
	}
	loader.SendRequest('/cms/form?async=yes&id='+oForm.id, sBody, 'POST', fn, null);
}