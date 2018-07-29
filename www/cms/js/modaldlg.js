
document.writeln('<style type="text/css">\
#__overlay {background-color: #aaaaaa;}\
#__modalbox {padding: 0px; background-color: #ffffff; border: 1px solid #999999; color: #333333; font: 12px Arial;}\
#__modalbox a:link, #__modalbox a:visited, #__modalbox a:active {color: #333399; text-decoration: none;}\
#__modalbox a:hover {color: #6666cc; text-decoration: underline;}\
</style>');

modalDlg = {

    width:  300,
    height: 400,
    img:    '/cms/img/modaldlg.gif',
    error:  "Ошибка передачи данных.\nВозможно сервер временно недоступен.\nПовторите попытку позже.",

    loadingurl: null,
    loadedurl:  null,

    createCenteredBlock: function(o, bOnlyVerticalCenter) {
        var table = document.createElement('table');
        o.appendChild(table);
        var tr = table.insertRow(0);
        var td = tr.insertCell(0);
        table.setAttribute('cellspacing', '0');
        table.setAttribute('cellpadding', '0');
        table.setAttribute('width',       '100%');
        table.setAttribute('height',      '100%');
        td.setAttribute('valign',         'middle');
        if (!bOnlyVerticalCenter) td.setAttribute('align', 'center');
        return td;
    },

    showOverlay: function() {
        var o = document.getElementById('__overlay');
        if (!o) {
            o = document.createElement('div');
            document.body.appendChild(o);
            o.id = '__overlay';
            o.style.position        = 'fixed';
            o.style.visibility      = 'hidden';
            o.style.zIndex          = '100';
            o.style.left            = '0px';
            o.style.top             = '0px';
            o.style.width           = '100%';
            o.style.height          = '100%';
            o.style.opacity         = '0.50';
            o.style.filter          = 'alpha(opacity=50)';
            var cb = this.createCenteredBlock(o);
            var img = document.createElement('img');
            img.setAttribute('src', this.img);
            cb.appendChild(img);

	    if (!window.XMLHttpRequest) {// IE6
		    o.style.position = 'absolute';
		    o.style.backgroundColor = '#aaaaaa';
	    }
        }
        o.style.visibility = 'visible';
	
	if (!window.XMLHttpRequest) {// IE6
		o.style.left = document.documentElement.scrollLeft + 'px';
		o.style.top = document.documentElement.scrollTop + 'px';
	}
    },

    hideOverlay: function() {
        var o = document.getElementById('__overlay');
        if (o) o.style.visibility = 'hidden';
    },

    showModalDlgBox: function(sContents) {
        var o = document.getElementById('__modalbox');
        if (!o) {
            var o2 = document.createElement('div');
            document.body.appendChild(o2);
            o2.style.position       = 'fixed';
            o2.style.visibility     = 'hidden';
            o2.style.zIndex         = '101';
            o2.style.left           = '0px';
            o2.style.top            = '0px';
            o2.style.width          = '100%';
            o2.style.height         = '100%';

            var cb = this.createCenteredBlock(o2, true);
            o = document.createElement('div');
            cb.appendChild(o);
            o.id = '__modalbox';
            o.style.margin          = 'auto';

	    if (!window.XMLHttpRequest) {// IE6
		    o2.style.position = 'absolute';
		    o.style.backgroundColor = '#ffffff';
		    o.style.border = '1px solid #999999';
	    }
        }
        o.style.width = this.width+'px';
        o.style.height = this.height+'px'
        o.style.visibility = 'visible';
	o.parentNode.parentNode.parentNode.parentNode.parentNode.style.visibility = 'visible';
        if (sContents) o.innerHTML = sContents
            .replace(/href\=\"([^\"]+)\"/ig, 'href="javascript:modalDlg.open(\'$1\')"')
            .replace(/\<form/ig, '<form onsubmit="modalDlg.submit(this); return false;"');

	if (!window.XMLHttpRequest) {// IE6
		var o2 = o.parentNode.parentNode.parentNode.parentNode.parentNode;
		o2.style.left = document.documentElement.scrollLeft + 'px';
		o2.style.top = document.documentElement.scrollTop + 'px';
	}

	var scripts = o.getElementsByTagName('script');
	for (var i=0; i<scripts.length; i++) eval(scripts[i].innerHTML);
    },

    hideModalDlgBox: function(bClear) {
        var o = document.getElementById('__modalbox');
        if (o) {
		o.style.visibility = 'hidden';
		o.parentNode.parentNode.parentNode.parentNode.parentNode.style.visibility = 'hidden';
		if (bClear) o.innerHTML = '';
	}
    },

    open: function(sUrl, sBody, iWidth, iHeight) {
        this.hideModalDlgBox();
        this.showOverlay();
        this.loadingurl = sUrl;
        if (iWidth)  this.width  = iWidth;
        if (iHeight) this.height = iHeight;
        var sMethod = sBody ? 'POST' : 'GET';
	var fOnLoad = function(responseText) {
		modalDlg.loadedurl = modalDlg.loadingurl;
		modalDlg.showModalDlgBox(responseText);
	}
	var fOnError = function() {
		alert(modalDlg.error);
                if (modalDlg.loadedurl) modalDlg.showModalDlgBox();
                else modalDlg.hideOverlay();
	}
	loader.SendRequest(sUrl, sBody, sMethod, fOnLoad, fOnError)
    },

    close: function() {
        this.loadedurl = null;
        this.hideModalDlgBox(true);
        this.hideOverlay();
    },

    submit: function(oForm) {
        var sUrl = oForm.action ? oForm.action : this.loadedurl;
        var sBody = '';
        for (var i=0; i<oForm.elements.length; i++) {
		if ((oForm.elements[i].type=='checkbox' || oForm.elements[i].type=='radio')
			&& !oForm.elements[i].checked) continue;
		sBody += oForm.elements[i].name+'='+encodeURIComponent(oForm.elements[i].value)+'&';
	}
        this.open(sUrl, sBody);
    }

}
