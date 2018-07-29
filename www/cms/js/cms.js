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

function Select(sFormId, sId, sMode) {
	window.sSelectFormId = sFormId;
	window.sSelectId = sId;
	window.sSelectMode = sMode;
	window.open('/cms/map', 'SelectWnd', 'menubar=0,status=0,resizable=1,scrollbars=1,width=300,height=400');
}

function SelectReturn(sId, sLink) {
	switch (window.sSelectMode) {
		case 'id':		 
		document.getElementById(window.sSelectFormId).elements[window.sSelectId].value = sId; 
		break;
		case 'link': document.getElementById(window.sSelectFormId).elements[window.sSelectId].value = sLink; break;
	}
}

function FBDlg(sFormId, sId) {
	window.sSelectFormId = sFormId;
	window.sSelectId = sId;
	window.open('/cms/fb', 'SelectWnd', 'menubar=0,status=0,resizable=1,scrollbars=1,width=600,height=400');
}

function FBReturn(sUrl) {document.getElementById(window.sSelectFormId).elements[window.sSelectId].value = sUrl;}

function CheckPassword(f) {return f.elements['_a[0][password]'].value==f.elements['password2'].value;}

function LoadTemplateSrcList(sSelectId, sSelected) {
	eval('var fn = function(text) {var s = document.getElementById("'+sSelectId+'"); s.innerHTML += text;}');
	loader.SendRequest('/cms/ajax/ins?i=template_opts&s='+encodeURIComponent(sSelected), null, null, fn);
}

function LoadTemplateList(sSelectId, sSrc, sSelected) {
	eval('var fn = function(text) {var s = document.getElementById("'+sSelectId+'"); s.parentNode.innerHTML = \'<select name="\'+s.name+\'" id="'+sSelectId+'">\'+text+"</select>";}');
	loader.SendRequest('/cms/ajax/ins?i=templates&src='+encodeURIComponent(sSrc)+'&s='+encodeURIComponent(sSelected), null, null, fn);
}

function TPDlg(sSrc) {
	window.open('/cms/templates/properties?src='+sSrc, '_blank', 'menubar=0,status=0,resizable=1,scrollbars=1,width=450,height=400');
}

function cmsConfigCangeDbType(v) {
	var tb1 = document.getElementById('mysqlParams');
	var tb2 = document.getElementById('xmlParams');
	switch (v) {
		case 'mysql':
			tb1.style.display = '';
			tb2.style.display = 'none';
			break;
		case 'xml':
			tb1.style.display = 'none';
			tb2.style.display = '';
			break;
        }
}