

function BlockSwitch(t, id) {
	var o = document.getElementById(id);
	var a = t.getElementsByTagName('span');
	a = a[0];
	if (o.style.display=='none') {
		var buf = 'block';
		a.innerHTML = '&ndash;';
		if (o.innerHTML.length<=1) {
			o.innerHTML = '<img src="/cms/img/cms/load.gif" />';
			eval('var fn = function(text) {document.getElementById("'+id+'").innerHTML = text;}');
			loader.SendRequest('/cms/ajax?template='
				+encodeURIComponent('xml:<t:block xmlns:t="/templates/ns" id="'
				+document.getElementById('v_id').value
				+'" template="'+id+'@cms/pages/cms/index.xml" view_strict="yes" />'), '', 'POST', fn);
                }
        } else {
		var buf = 'none';
		a.innerHTML = '+';
	}
	SetCookie('b_'+id, buf);
	o.style.display = buf;
}

function ShowHelp(o) {
	var oHelp = document.getElementById('oHelp');
	oHelp.innerHTML = '<b>'+o.getAttribute('title')+'</b> - '+o.getAttribute('description');
}


function bActionComplete(sLink) {
	CmsLoadWndHide();
	if (sLink.match(/http\:\/\//)) window.location = sLink;
	else window.location = '/cms/?'+sLink;
}

function blDeleteComplete(sLink) {
	CmsLoadWndHide();
}

function bSaveComplete(sLink) {
	CmsLoadWndHide();
	if (sLink!=window.location && '?'+sLink!=window.location.search) window.location = '/cms/?'+sLink;
}

function PrepareBackendBlocks(o) {
	var imgs = o.getElementsByTagName('img');
	for (var i=0; i<imgs.length; i++) if (imgs[i].className=='cmsBackendBlock') {
		var buf = document.createElement('div');
		buf.innerHTML = imgs[i].getAttribute('title');
		imgs[i].parentNode.replaceChild(buf.firstChild, imgs[i]);
		i--;
	}
}

function bMaximize() {
	var d = window.cmsActiveBlock;
	var to = document.getElementById('ToolBarEditor');
	o = to.getElementsByTagName('a');
	for (var i=0; i<o.length; i++)
		if (o[i].getAttribute('href')=='javascript:bMaximize()') {o = o[i]; break;}
	if (!o.length) {
		o.setAttribute('href', 'javascript:bMinimize()');
		o.setAttribute('title', 'Выключить полноэкранный режим');

		window.cmsMaximizeWidth = d.style.width;
		window.cmsMaximizeHeight = d.style.height;
		d.style.position = 'fixed';
		d.style.left = '0px';
		d.style.top = '56px';
		d.style.width = (document.documentElement.clientWidth - 12) + 'px';
		d.style.height = (document.documentElement.clientHeight - 68) + 'px';
		
		to.style.position = 'fixed';
		to.style.left = '0px';
		to.style.top = '0px';
		to.style.backgroundColor = '#ffffff';
	}
}

function bMinimize() {
	var d = window.cmsActiveBlock;
	var to = document.getElementById('ToolBarEditor');
	o = to.getElementsByTagName('a');
	for (var i=0; i<o.length; i++)
		if (o[i].getAttribute('href')=='javascript:bMinimize()') {o = o[i]; break;}
	if (!o.length) {
		o.setAttribute('href', 'javascript:bMaximize()');
		o.setAttribute('title', 'Включить полноэкранный режим');

		d.style.position = '';
		d.style.width = window.cmsMaximizeWidth;
		d.style.height = window.cmsMaximizeHeight;
		cmsFocus(window.cmsActiveBlock);
		
		to.style.position = '';
		to.style.backgroundColor = '';
	}
}

function bSave(publish) {
	CmsLoadWndShow();
	var id = document.getElementById('v_id').value;
	
	var sBody = '_a[0][action]='+(publish ? 'saveAndPublish' : 'save')
		+'&_a[0][id]='+id;
	
	o = document.getElementById('titleToLink');
	if (o && o.checked) sBody += '&_a[0][titleToLink]=true';

	o = document.getElementById('v_title');
	if (o) sBody += '&_a[0][vars][title]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_description');
	if (o) {
		var o = o.cloneNode(true);
		PrepareBackendBlocks(o);
		PrepareImages(o);
		sBody += '&_a[0][vars][description]='+PrepareSaveVar(o.innerHTML);
        }
	
	o = document.getElementById('v_image');
	if (o) {
		var o = o.cloneNode(true);
		PrepareBackendBlocks(o);
		PrepareImages(o);
		sBody += '&_a[0][vars][image]='+PrepareSaveVar(o.innerHTML);
        }
	
	o = document.getElementById('v_content');
	if (o) {
		var o = o.cloneNode(true);
		PrepareBackendBlocks(o);
		PrepareImages(o);
		sBody += '&_a[0][vars][content]='+PrepareSaveVar(o.innerHTML);
        }
	
	o = document.getElementById('v_create_time');
	if (o) sBody += '&_a[0][vars][create_time]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_n');
	if (o) sBody += '&_a[0][vars][n]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_keywords');
	if (o) sBody += '&_a[0][vars][keywords]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_link');
	if (o) sBody += '&_a[0][vars][link]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('cash_yes');
	if (o) {
		if (document.getElementById('cash_yes').checked) sBody += '&_a[0][vars][cash]=yes';
		else if (document.getElementById('cash_no').checked) sBody += '&_a[0][vars][cash]=no';
        }
	
	o = document.getElementById('data_only_yes');
	if (o) {
		if (document.getElementById('data_only_yes').checked) sBody += '&_a[0][vars][data_only]=yes';
		else if (document.getElementById('data_only_no').checked) sBody += '&_a[0][vars][data_only]=no';
		else if (document.getElementById('data_only_childs').checked) sBody += '&_a[0][vars][data_only]=childs';
	}
	
	o = document.getElementById('use_content_in_head_true');
	if (o) {
		if (document.getElementById('use_content_in_head_true').checked)
			sBody += '&_a[0][vars][use_content_in_head]=true';
		else if (document.getElementById('use_content_in_head_false').checked)
			sBody += '&_a[0][vars][use_content_in_head]=false';
		else if (document.getElementById('use_content_in_head_path').checked)
			sBody += '&_a[0][vars][use_content_in_head]=path';
	}
	
	o = document.getElementById('v_head');
	if (o) sBody += '&_a[0][vars][head]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_template_src');
	if (o) sBody += '&_a[0][vars][template_src]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_childrens_template_src');
	if (o) sBody += '&_a[0][vars][childrens_template_src]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_external_class');
	if (o) sBody += '&_a[0][vars][external_class]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('v_parent_id');
	if (o) sBody += '&_a[0][vars][parent_id]='+PrepareSaveVar(o.value);
	
	o = document.getElementById('external_vars');
	if (o) {
		var a = o.getElementsByTagName('input');
		for (var i=0; i<a.length; i++) sBody+='&_a[0][vars]['+a[i].id.substr(2)+']='+PrepareSaveVar(a[i].value);
		a = o.getElementsByTagName('textarea');
		for (var i=0; i<a.length; i++) sBody+='&_a[0][vars]['+a[i].id.substr(2)+']='+PrepareSaveVar(a[i].value);
	}
	
	if (window._bAddAssocSelector) for (var i=0; i<window._bAddAssocSelector.length; i++) {
		o = document.getElementById('assoc1_'+window._bAddAssocSelector[i]);
		if (o.value) sBody += '&_a[assoc_'+i+'][action]=addAssoc&&_a[assoc_'+i+'][id]='+id
			+'&_a[assoc_'+i+'][foreign_id]='+o.value;
        }
	
	//alert(sBody);
	loader.SendRequest('/cms/action?async=exit', sBody, 'POST', bSaveComplete);
}

function bInsert(child) {
	CmsLoadWndShow();
	var sBody = '_a[0][action]=save&_a[0][titleToLink]=true&_a[0][parent_id]='
		+(child ? document.getElementById('v_id').value : document.getElementById('_parent_id').value)
		+'&_a[0][vars][create_time]=NOW&_a[0][vars][title]='+encodeURIComponent('Новая запись');
	
	loader.SendRequest('/cms/action?async=exit', sBody, 'POST', bActionComplete);
}

function bDelete() {
	if (confirm('Вы уверены что хотите удалить запись '+document.getElementById('v_title').value
		+"?\nВосстановить удаленную запись будет невозможно")) {
		CmsLoadWndShow();
		loader.SendRequest('/cms/action?async=exit', '_a[0][action]=delete&_a[0][id]='
			+document.getElementById('v_id').value, 'POST', bActionComplete);
	}
}

function blDelete(DomId, id, title) {
	if (confirm('Вы уверены что хотите удалить запись '+title
		+"?\nВосстановить удаленную запись будет невозможно")) {
		CmsLoadWndShow();
		var o = document.getElementById(DomId);
		o.parentNode.removeChild(o);
		loader.SendRequest('/cms/action?async=exit', '_a[0][action]=delete&_a[0][id]='+id,
			'POST', blDeleteComplete);
	}
}

function bDeleteChilds() {
	if (confirm('Вы уверены что хотите удалить дочерние записи '+document.getElementById('v_title').value
		+"?\nВосстановить удаленную запись будет невозможно")) {
		CmsLoadWndShow();
		loader.SendRequest('/cms/action?async=exit', '_a[0][action]=deleteChilds&_a[0][id]='
			+document.getElementById('v_id').value, 'POST', bActionComplete);
	}
}

function bSA(action) {
	CmsLoadWndShow();
	loader.SendRequest('/cms/action?async=exit', '_a[0][action]='+action+'&_a[0][id]='
		+document.getElementById('v_id').value, 'POST', bActionComplete);
}

function LoadLeft(sLink) {
	document.getElementById('left').innerHTML = '<img src="/cms/img/cms/load.gif" />';
	loader.SendRequest('/cms/ajax?template='+encodeURIComponent('xml:<t:block xmlns:t="/templates/ns" link="'+sLink
		+'" template="left@cms/pages/cms/index.xml" view_strict="yes" />'), '', 'POST', LoadLeftComplete);
}

function LoadLeftComplete(text) {
	var o = document.getElementById('left').parentNode;
	o.innerHTML = text;
}

function bBackup() {
	CmsLoadWndShow();
	loader.SendRequest('/cms/action?async=exit', '_a[0][action]=backup&_a[0][id]='
		+document.getElementById('v_id').value+'&_a[0][version]='
		+document.getElementById('v_version').value, 'POST', bActionComplete);
}

function bSaveToChilds(name) {
	CmsLoadWndShow();
	switch (name) {
		case 'cash':
			if (document.getElementById('cash_yes').checked) var value = 'yes';
			else if (document.getElementById('cash_no').checked) var value = 'no';
			break;
		case 'data_only':
			if (document.getElementById('data_only_yes').checked) var value = 'yes';
			else if (document.getElementById('data_only_no').checked) var value = 'no';
			else if (document.getElementById('data_only_childs').checked) var value = 'childs';
			break;
		case 'use_content_in_head':
			if (document.getElementById('use_content_in_head_true').checked) var value = 'true';
			else if (document.getElementById('use_content_in_head_false').checked) var value = 'false';
			else if (document.getElementById('use_content_in_head_path').checked) var value = 'path';
			break;
        }
	loader.SendRequest('/cms/action?async=exit', '_a[0][action]=saveToChilds&_a[0][id]='
		+document.getElementById('v_id').value+'&_a[0][vars]['+name+']='+value, 'POST', bActionComplete);
}


function bAddAssocSelector(id) {
	if (!window._bAddAssocSelector) window._bAddAssocSelector = new Array();
	window._bAddAssocSelector.push(id);
}

function Lock(o, sParentId) {
	if (!o.checked) {
		DelCookie('ASSOC['+sParentId+']');
		LoadParentBlock(o);
	} else SetCookie('ASSOC['+sParentId+']', document.getElementById('assoc1_'+sParentId).value);
}
