<?php
require ('verfgrs/index.php');

$GLOBALS['start'] = microtime(true);

define('CMS_VERSION',	'2009-08-28 16:00:00');
define('DIR_ROOT',	str_replace('\\', '/', dirname(__FILE__)));


/*
 * Constants
 */

define('TABLE_PAGE',		'CmsPage');
define('TABLE_USER',		'CmsUser');
define('TABLE_GROUP',		'CmsGroup');
define('TABLE_RIGHTS',		'CmsRights');
define('TABLE_ASSOC',		'CmsAssoc');
define('TABLE_LOG',		'CmsLog');
define('TABLE_FORM',		'CmsForm');
define('TABLE_STAT',		'CmsStat');
define('TABLE_SEARCH_STAT',	'CmsSearchStat');

define('LNG_TITLE',		'Заголовок');
define('LNG_IMAGE',		'Изображение');
define('LNG_DESCRIPTION',	'Описание');
define('LNG_CONTENT',		'Содержимое');

$GLOBALS['VIEW_STRICT'] = false;
$GLOBALS['BACKEND'] = false;
$GLOBALS['CMS_REQUIRED_VARS'] = array('id', 'link', 'version', 'parent_id', 'external_class', 'owner_id', 'title', 'create_time', 'publish_time', 'expire_time', 'n', 'childrens_template_src');
$GLOBALS['CMS_VARS'] = array('id', 'version', 'create_time', 'publish_time', 'expire_time', 'cash', 'data_only', 'owner_id', 'parent_id', 'external_class', 'n', 'template_src', 'childrens_template_src', 'link', 'use_content_in_head', 'head', 'title', 'keywords', 'description', 'image', 'content');
$GLOBALS['TAG_FUNCTION'] = array('head', 'main', 'first', 'inner', 'last', 'empty', 'even', 'odd', 'var', 'eval', 'setvar', 'if', 'rule', 'tohead', 'form', 'a', 'counteradd', 'counterget', 'counter');
$GLOBALS['TAG_SELECT'] = array('block', 'siblings', 'childrens', 'childrenscount', 'assoc', 'rassoc', 'dassoc', 'parent', 'select', 'rawquery', 'logquery', 'search', 'user', 'users', 'basket');
$GLOBALS['HEAD_ADD'] = '';






/*
 * Functions
 */

function __autoload($ClassName) {
	require_once DIR_ROOT.'/cms/classes/'.$ClassName.'.php';
}

function ob_saveCookieAfter($s) {
    setcookie('psa', strlen($s));
    return $s;
}

function ob_saveCookieBefore($s) {
    setcookie('psb', strlen($s));
    return $s;
}

function DOMElementInnerXML(DOMNode $Element) {
	if (is_a($Element, 'DOMText')) return $Element->data;
	$r = '';
	for ($i=0; $i<$Element->childNodes->length; $i++) {
		$r .= $Element->ownerDocument->saveXML($Element->childNodes->item($i));}
	return $r;
}

function EvalCode($Code, &$output = '') {//var_dump($Code); echo '<br />';
	ob_start();
	$result = eval($Code);
	$output = ob_get_clean();
	return $result;
}

function autoreplace($Str) {
    $Str = preg_replace('/([^\s\<\>](\<[^\>]*\>)*)\"(?![^\<\>]*\>)/',   '$1&raquo;',            $Str);
    $Str = preg_replace('/\"((\<[^\>]*\>)*[^\s\<\>])(?![^\<\>]*\>)/',   '&laquo;$1',            $Str);
    $Str = preg_replace('/(\s)\-\-?\-?(\s+)|&nbsp;\-\-?\-?(\s)/',       '&nbsp;&mdash;$2$3',    $Str);
    $Str = preg_replace('/(\d+)\-(\d+)/',                               '$1&minus;$2',          $Str);
    $Str = preg_replace('/\-\-\-/',                                     '&mdash;',              $Str);
    $Str = preg_replace('/\-\-/',                                       '&ndash;',              $Str);
    $Str = preg_replace('/\<\<|&lt;&lt;/',                              '&laquo;',              $Str);
    $Str = preg_replace('/\>\>|&gt;&gt;/',                              '&raquo;',              $Str);
    $Str = preg_replace('/\(c\)/i',                                     '&copy;',               $Str);
    return $Str;
}

function HtmlToXml($Str) {
	$Str = str_replace(array("\r\n", "\r"), "\n", $Str);
	$Str = preg_replace('/\<(\w+)\:(\w+)([^\>]*)\>/u', '<$1__$2$3>', $Str);
	$Str = preg_replace('/\<\/(\w+)\:(\w+)\s*\>/u', '</$1__$2>', $Str);
	$Str = preg_replace('/xmlns\:(\w+)\=/u', 'xmlns__$1=', $Str);
	$d = new DOMDocument('1.0', 'UTF-8');
	@$d->loadHTML(
"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /></head><body>$Str</body></html>");
	$result = DOMElementInnerXML($d->documentElement->lastChild);
	$result = preg_replace('/\<(\w+)__(\w+)([^\>]*)\>/u', '<$1:$2$3>', $result);
	$result = preg_replace('/\<\/(\w+)\__(\w+)\s*\>/u', '</$1:$2>', $result);
	$result = preg_replace('/xmlns__(\w+)\=/u', 'xmlns:$1=', $result);
	return $result;
}

function GeneratePassword($size = 6, $possible = '0123456789qwertyuioplkjhgfdsazxcvbnm') {
	$len = strlen($possible);
	if ($size>$len) $size = $len;
	return substr(str_shuffle($possible), mt_rand(0, 36-$size), $size);
}

function MailTo($To, $From, $Subject, $Message) {
	$Message = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" /></head>
<body>$Message</body></html>";
	$headers = "From: $From\r\nContent-Type: text/html; charset=UTF-8\r\n";
	mail($To, $Subject, $Message, $headers);
}

function BasketClear() {
	foreach ($_COOKIE['B'] as $i=>$v) setcookie('B['.$i.']', '', 0, '/');
}

function CheckTime() {
	global $start;
	echo microtime(true) - $start;
	exit;
}

function Build($Template, $Vars = null) {
	if (is_null($Vars)) $Vars = array('content'=>'');
	global $CORE;
	global $TEMPLATES;
	$template = $TEMPLATES->getTemplateElement($Template);
	$params = array('template'=>$Template);
	$r = $CORE->build($template, $Vars, $params);
	$r = str_replace(array('<![CDATA[', ']]>'), '', $r);
	$CORE->stripTempTags($r);
	return $r;
}

function isMSIE() {
	return (bool)preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']);
}

function tstart() {
	//if (!isset($GLOBALS['T_TIME1'])) $GLOBALS['T_TIME1'] = 0;
	//if (!isset($GLOBALS['T_TIMES'])) $GLOBALS['T_TIMES'] = 0;
	$GLOBALS['T_TIME'] = microtime(true);
}

function tstop1() {
	printf('<pre>%.5f</pre>', microtime(true) - $GLOBALS['T_TIME']);
	//$GLOBALS['T_TIME1'] += microtime(true) - $GLOBALS['T_TIME'];
	//$GLOBALS['T_TIMES']++;
}

function tstop2() {
	die(sprintf('%.5f', microtime(true) - $GLOBALS['T_TIME']));
}

function tstop() {
	die(sprintf('%d %.5f %.5f', $GLOBALS['T_TIMES'], $GLOBALS['T_TIME1'], $GLOBALS['T_TIME1']/$GLOBALS['T_TIMES']));
}

function preImportXML($Id, $Filename, $entityDecode, array $Fields1, array $Fields2,
	$Publish, $TitleToLink, $CreateTimeNow, $Params) {
	$d = new DOMDocument();
	$d->load($Filename);
	for ($j=0; $j<$d->documentElement->childNodes->length; $j++) {
		$e = $d->documentElement->childNodes->item($j);
		if ($e->nodeType!=XML_ELEMENT_NODE) continue;
		$_POST['_a'][$j] = array();
		if (!empty($Publish)) $_POST['_a'][$j]['action'] = 'saveAndPublish';
		else $_POST['_a'][$j]['action'] = 'save';
		if (!empty($TitleToLink)) $_POST['_a'][$j]['titleToLink'] = 'true';
		if (!empty($Params)) $_POST['_a'][$j]['params'] = $Params;
		$_POST['_a'][$j]['vars'] = array();
		if (!empty($CreateTimeNow)) $_POST['_a'][$j]['vars']['create_time'] = 'NOW';
		foreach ($Fields1 as $i=>$v) if (!empty($v) && !empty($Fields2[$i])) {
			$tags = $e->getElementsByTagName($v);
			if ($tags->length>0)
				$_POST['_a'][$j]['vars'][$Fields2[$i]] = ($entityDecode
					? $tags->item(0)->textContent
					: DOMElementInnerXML($tags->item(0)));
			else $_POST['_a'][$j]['vars'][$Fields2[$i]] = $e->getAttribute($v);
		}

		if (empty($_POST['_a'][$j]['vars']['parent_id'])) $_POST['_a'][$j]['parent_id'] = $Id;
		else $_POST['_a'][$j]['parent_id'] = $_POST['_a'][$j]['vars']['parent_id'];
		unset($_POST['_a'][$j]['vars']['parent_id']);
		
		if (isset($_POST['_a'][$j]['vars']['id']) 
			&& !$GLOBALS['DBC']->checkId($_POST['_a'][$j]['vars']['id'])) {
			$_POST['_a'][$j]['id'] = $_POST['_a'][$j]['vars']['id'];
			unset($_POST['_a'][$j]['vars']['id']);
		}
	}
	return $j;
}






/*
 * Interfaces
 */

interface IDBC {

	public function connect();
	public static function install();
	public function authenticate($Name, $Password);
	public function getUserRights();
	public function getVars($Link, $RequiredVars = array());
	public function getVarsById($Id, $RequiredVars = array());
	public function getRow($Result, $I = 0, $F = 0);
	public function getMaxN($ParentId);
	public function checkLink($Link, $Id);
	public function checkUserName($UserId, $UserName);
	public function getParentTemplateSrc(&$Vars);
	public function getLastPublishTime();

	/*
         * Queries
         *
         * MUST return array($result, $first, $last, $count)
         */

	public function getSiblings(&$Vars, &$RequiredVars, $Orderby, $Length, $Page, $ReturnCount = false);
	public function getChilds(&$Vars, &$RequiredVars, $Orderby, $Length, $Page, $Deep = 1, $ReturnCount = false);
	public function getParent(&$Vars, &$RequiredVars, $Deep = 1);
	public function getAssoc(&$Vars, &$RequiredVars, $Orderby, $Length, $Page);
	public function getRAssoc(&$Vars, &$RequiredVars, $ForeignId, $Orderby, $Length, $Page);
	public function getDAssoc(&$Vars, &$RequiredVars, $Id, $Orderby, $Length, $Page);
	public function select($Sql, $XPath, $Orderby, $Length, $Page, $Element);
	public function rawQuery($Sql, $XPath, $Orderby);
	public function search(&$RequiredVars, $Query, $Length, $Page, $Element);
	public function getUsers($Id, $GroupId, $Orderby, $Length, $Page, $Element);

	/*
         * Actions
         */
	
	public function save(&$A);
	public function publish(&$A);
	public function hide(&$A);
	public function delete(&$A);
	public function addAssoc(&$A);
	public function removeAssoc(&$A);
	public function backup(&$A);
	public function moveTo(&$A);
	public function moveUp(&$A);
	public function moveDown(&$A);
	public function moveToFirst(&$A);
	public function moveToLast(&$A);
	public function userSave(&$A);
	public function userDelete(&$A);
	public function groupSave(&$A);
	public function groupDelete(&$A);
	public function rightsSave(&$A);
	public function rightsDelete(&$A);
	
	/*
         * Form
         */
	
	public function formAdd($Id, $Code, $Redirect, $CheckForm, $Code2);
	public function formGet($Id);
	public function formClear($Id = null);
	public function formSavePost($Id, $Post);

	/*
         * Statistic
         */

	public function statUnique();
	public function statAdd($Unique, $External, $Google, $Yandex, $Rambler);
	public function statSearchAdd($Search, $Google, $Yandex, $Rambler);
	public function logAdd($Ip, $User, $Link, $Post);

}









$CORE		= new CCore();
$DBC		= new CDBC();
$TEMPLATES	= new CTemplates();
$USER		= new CUser();
$OUTPUT		= '';
$CORE->init();

if (!$CORE->loadConfig()) {
	$USER->setRights(CUser::ADMINISTRATOR);
	$CORE->checkRequestURI(CCore::URI_REQ_NONE);
	$CORE->checkRequestURI(CCore::URI_REQ_AUTH);
	$CORE->checkRequestURI(CCore::URI_REQ_CONN, true);
} else {
	$CORE->checkRequestURI(CCore::URI_REQ_NONE);

	$DBC->connect();
	$USER->authenticate();
	$CORE->checkRequestURI(CCore::URI_REQ_AUTH);

	$CORE->checkRequestURI(CCore::URI_REQ_CONN);
}
//tstop();
$CORE->output();

?>