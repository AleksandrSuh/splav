function BasketSetCookie(sName, sValue, dDate) {document.cookie = sName + "=" + sValue + "; " + (dDate!=null ? "expires=" + dDate + "; " : "") + "path=/;";}
function BasketDelCookie(sName) {document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT; path=/;";}
function BasketGetCookie(sName) {var aCookie = document.cookie.split('; '); for (var i=0; i<aCookie.length; i++) {var aCrumb = aCookie[i].split('='); if (sName==aCrumb[0]) return unescape(aCrumb[1]);} return null;}
/*function BasketAdd(iBasketId, sId, sPrice, sNum, sParam) {
	if (!sNum) sNum = 1;
	sNum = parseInt(sNum);
	if (n = BasketGetCookie('BASKET['+sId+'][num]')) BasketSetCookie('BASKET['+sId+'][num]', parseInt(n)+sNum);
	else {
		BasketSetCookie('BASKET['+sId+'][price]', encodeURIComponent(sPrice));
		BasketSetCookie('BASKET['+sId+'][num]', sNum);
		BasketSetCookie('BASKET['+sId+'][param]', encodeURIComponent(sParam));
	}
	LoadParentBlock(document.getElementById(iBasketId));
}
function BasketDel(iBasketId, sId) {
	BasketDelCookie('BASKET['+sId+'][price]');
	BasketDelCookie('BASKET['+sId+'][num]');
	BasketDelCookie('BASKET['+sId+'][param]');
	LoadParentBlock(document.getElementById(iBasketId));
}
function BasketSet(iBasketId, sId, sPrice, sNum, sParam) {
	sNum = sNum!='' ? sNum : 0;
	if (sNum!=BasketGetCookie('BASKET['+sId+'][num]')) {
		BasketSetCookie('BASKET['+sId+'][price]', encodeURIComponent(sPrice));
		BasketSetCookie('BASKET['+sId+'][num]', sNum);
		BasketSetCookie('BASKET['+sId+'][param]', encodeURIComponent(sParam));
		LoadParentBlock(document.getElementById(iBasketId));
	}
}*/
function BasketAdd(iBasketId, sId, sPrice, sNum, sParam) {
	if (!sNum) sNum = 1;
	sNum = parseInt(sNum);
	var b = BasketGetCookie('B['+sId+']');
	if (b) {
		var a = b.split(';');
		BasketSetCookie('B['+sId+']', encodeURIComponent(a[0]+';'+(parseInt(a[1])+sNum)+';'+a[2]));
	} else BasketSetCookie('B['+sId+']', encodeURIComponent(sPrice+';'+sNum+';'+sParam));
	LoadParentBlock(document.getElementById(iBasketId));
}
function BasketDel(iBasketId, sId) {
	BasketDelCookie('B['+sId+']');
	LoadParentBlock(document.getElementById(iBasketId));
}
function BasketSet(iBasketId, sId, sPrice, sNum, sParam) {
	sNum = sNum!='' ? sNum : 0;
	var b = BasketGetCookie('B['+sId+']');
	if (b) {var a = b.split(';'); if (sNum==a[1]) return false;}
	BasketSetCookie('B['+sId+']', encodeURIComponent(sPrice+';'+sNum+';'+sParam));
	LoadParentBlock(document.getElementById(iBasketId));
}