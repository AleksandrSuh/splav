<?php

class CCore extends CCoreObject {

	const URI_REQ_NONE = 1;
	const URI_REQ_AUTH = 2;
	const URI_REQ_CONN = 3;

	const CHECK_POST_EXIT = 0;
	const CHECK_POST_REDIRECT = 1;
	const CHECK_POST_RETURN = 2;
	const CHECK_POST_NONE = 3;

	private $InstallMode = false;
	private $cache;
	private $cache_modified = false;

	public function cache_set($Name, $Value) {
		$this->cache[$Name] =& $Value;
		$this->cache_modified = TRUE;
	}

	public function cache_get($Name) {
		if (isset($this->cache[$Name])) return $this->cache[$Name];
		else return FALSE;
	}

	public function cache_clear() {
		$this->cache = array();
	}

	public function init() {
		parent::init();
		$this->DBC->init();
		$this->TEMPLATES->init();
		$this->USER->init();
		$this->prepareRequestURI();

		if (isset($_COOKIE[session_name()]) || isset($_GET[session_name()])) session_start();
		ob_start('ob_saveCookieAfter');
		ob_start('ob_gzhandler', 9);
		ob_start('ob_saveCookieBefore');
        }

	private function getTime() {
		return microtime(true) - $GLOBALS['start'];
        }

	public function showStat() {
		return $stat = "<hr />\n"
			.'time: '.sprintf('%.3f', $this->getTime())."<br />\n"
			.'memory: '.memory_get_peak_usage()."<br />\n"
			.'templates: '.$this->TEMPLATES->getLoadedDocumentsNum()."<br />\n"
			.'query num: '.$this->DBC->getQueryNum()."<br />\n"
			."query log: <br />\n<br />\n".$this->DBC->getQueryLog();
        }
	
	public function output($Str = '') {
		if ($Str!='') {
			$this->OUTPUT .= $Str;
			if (DEBUG_MODE=='true') $this->OUTPUT .= $this->showStat();
                }
		if ($this->OUTPUT!='') $this->HTTPReturn(200);
		else $this->HTTPReturn(404);
        }

	public function HTTPReturn($Code) {
		switch ($Code) {
			case 200: echo $this->OUTPUT; exit;
			case 304: header('HTTP/1.1 304 Not Modified'); exit;
			case 403: header('HTTP/1.1 403 Forbidden'); exit;
			case 404:
				header('HTTP/1.1 404 Not Found');
				if (file_exists('cms/pages/eu404.html'))
					echo file_get_contents('cms/pages/eu404.html');
				exit;
			case 500: header('HTTP/1.1 500 Internal Server Error'); exit;
		}
        }

	private function prepareRequestURI($Uri = null) {
		if (is_null($Uri)) $Uri = $_SERVER['REQUEST_URI'];
		preg_match('/(\?(.*))?(\#.*)?$/', $Uri, $matches);
		if (count($matches)>1) $_SERVER['QUERY_STRING'] = $matches[2];
		else $_SERVER['QUERY_STRING'] = '';
		$Uri = preg_replace('/(\/)?([\?\#].*)?$/', '', $Uri);
		preg_match('/\/page\/(\d+)$/', $Uri, $matches);
		if (count($matches)>1) {
			if ((int)$matches[1]==1) $this->HTTPReturn(404);
			$GLOBALS['REQUEST_PAGE'] = (int)$matches[1];
			$Uri = str_replace('/page/'.$GLOBALS['REQUEST_PAGE'], '', $Uri);
		} else $GLOBALS['REQUEST_PAGE'] = 1;
		if ($Uri=='') $Uri = '/';
		$_SERVER['REQUEST_URI'] = $Uri;
		parse_str($_SERVER['QUERY_STRING'], $_GET);
        }

	private function defineConfigParam($ConfigElement, $Name, $ConfigName, $Default) {
		define($Name,$ConfigElement->hasAttribute($ConfigName)
			? $ConfigElement->getAttribute($ConfigName)
			: $Default);
	}

	public function loadConfig() {
		$d = new DOMDocument();
		$this->InstallMode = false;
		if (!@$d->load(DIR_ROOT.'/config.xml')) {
			$this->InstallMode = true;
/* Загрузка пустого файла конфигурации, для установки значений по умолчанию */
			$d->appendChild($d->createElement('config'));
                }
		
		$de = $d->documentElement;

		$this->defineConfigParam($de, 'DIR_FB',			'pathFb',		'/files');
		$this->defineConfigParam($de, 'DB_VERSIONS_NUM',	'dbVersionsNum',	3);
		$this->defineConfigParam($de, 'DB_TYPE',		'dbType',		'mysql');
		$this->defineConfigParam($de, 'DB_HOST',		'dbHost',		'localhost');
		$this->defineConfigParam($de, 'DB_USER',		'dbUser',		'root');
		$this->defineConfigParam($de, 'DB_PASS',		'dbPass',		'');
		$this->defineConfigParam($de, 'DB_NAME',		'dbName',		'');
		$this->defineConfigParam($de, 'DB_FILE',		'dbFile',		'.xml');
		$this->defineConfigParam($de, 'DEFAULT_TEXT',		'defaultText',		'Текст');
		$this->defineConfigParam($de, 'TIME_ZONE',		'timeZone',		'Asia/Yekaterinburg');
		$this->defineConfigParam($de, 'DEFAULT_PARENT_ID',	'defaultParentId',	'');
		$this->defineConfigParam($de, 'FB_SHOW_IMG_INFO',	'fbShowImgInfo',	'true');
		$this->defineConfigParam($de, 'USER_PARENT_ID',		'userParentId',		'');
		$this->defineConfigParam($de, 'CMS_LOG',		'cmsLog',		'false');
		$this->defineConfigParam($de, 'AUTH_SERIAL',		'serial',		'');
		$this->defineConfigParam($de, 'UPDATE_CHECK_BETA',	'updateCheckBeta',	'false');
		$this->defineConfigParam($de, 'UPDATE_CHECK_ALPHA',	'updateCheckAlpha',	'false');
		$this->defineConfigParam($de, 'CMS_MAP_LENGTH',		'cmsMapLength',		50);
		$this->defineConfigParam($de, 'CMS_CACHE',		'cmsCache',		'none');
		$this->defineConfigParam($de, 'IMG_WATERMARK',		'imgWatermark',		'');
		$this->defineConfigParam($de, 'COMPATIBILITY_MODE',	'compatibilityMode',	'true');
		$this->defineConfigParam($de, 'DEBUG_MODE',		'debugMode',		'false');
		$this->defineConfigParam($de, 'FORM_EXPIRE',		'formExpire',		24);
		$this->defineConfigParam($de, 'JPEG_QUALITY',		'jpegQuality',		75);
//		$this->defineConfigParam($de, 'TEMPLATES_PHP_CACHE',		'templatesPhpCache',	'false');

		$GLOBALS['EXTERNAL_CLASS'] = array();
		foreach ($d->getElementsByTagName('class') as $class) {
			$name = $class->getAttribute('name');
			$GLOBALS['EXTERNAL_CLASS'][$name] = array();
			$properties = $class->getElementsByTagName('property');
			for ($i=0; $i<$properties->length; $i++) {
				$GLOBALS['EXTERNAL_CLASS'][$name]['property'][$i]['name'] =
					$properties->item($i)->getAttribute('name');
				$GLOBALS['EXTERNAL_CLASS'][$name]['property'][$i]['type'] =
					$properties->item($i)->getAttribute('type');
			}
		}
		foreach ($d->getElementsByTagName('key') as $key) {
			define('AUTH_KEY', trim($key->textContent));
			break;
		}
		
		if (!$this->InstallMode) $this->siteAuthenticate($d);

		if (DEBUG_MODE=='true') error_reporting(E_ALL);
		else error_reporting(0);
		
		date_default_timezone_set(TIME_ZONE);

		return !$this->InstallMode;
        }

	final public static function checkSiteKey($Key) {
		$r = array();
		$host = preg_replace('/^www\./', '', getenv('HTTP_HOST'));
		$host2 = preg_replace('/^(.*\.)?(\w+\.\w+)$/', '$2', getenv('HTTP_HOST'));
		$rows = explode("\n", $Key);
		$r[0] = isset($rows[0]) && $rows[0]==md5($host."\n".'tipa eto sekretnyi kod');//domain
		$r[1] = isset($rows[1]) && $rows[1]==md5($host2."\n".'tipa eto sekretnyi kod2');//subdomains
		$r[2] = isset($rows[2]) && $rows[2]==md5($host2."\n".'tipa eto sekretnyi kod3');//hidelink
		return $r;
        }

	final private function siteAuthenticate($d, $retry = false) {
		define('ARTGK_BANNER_TEXT', '<p class="ARTGK-CMS" style="text-align: center;">
Сайт работает на <a title="Система управления сайтом" href="http://artgk-cms.ru/">ArtGK CMS</a></p>');
		return true;
		
		if (getenv('REMOTE_ADDR')=='127.0.0.1') {
			define('ARTGK_BANNER_TEXT', '<p class="ARTGK-CMS" style="text-align: center;">
Сайт работает на <a title="Система управления сайтом" href="http://artgk-cms.ru/">ArtGK CMS</a>
(локальная версия)</p>');
			return true;
                }

	/*	if (defined('AUTH_KEY') && !$retry) $key = AUTH_KEY;
		else {
			$key = @file_get_contents('http://auth43.artgk-cms.ru/?'.AUTH_SERIAL);
			$d->load(DIR_ROOT.'/config.xml');
			foreach ($d->documentElement->getElementsByTagName('key') as $e)
				$d->documentElement->removeChild($e);
			$e = $d->createElement('key');
			$d->documentElement->appendChild($e);
			$e->appendChild(new DOMText($key));
			$d->save(DIR_ROOT.'/config.xml');
                }
		*/
		list($db, $subdomains, $hidelink) = $this->checkSiteKey($key);

		if (!$retry && !defined('ARTGK_BANNER_TEXT'))
			define('ARTGK_BANNER_TEXT', $hidelink ? '' : '<p class="ARTGK-CMS" style="text-align: center;">
Сайт работает на <a title="Система управления сайтом" href="http://artgk-cms.ru/">ArtGK CMS</a></p>');

		if (DB_TYPE=='xml' || $db || $subdomains) return true;

		if (!$retry && $this->siteAuthenticate($d, true)) return true;
		
		header('HTTP/1.1 500 Internal Server Error');
		echo 'Не верный серийный номер';
		exit;
        }

	public function checkRequestURI($UriReq, $Install = false) {
		$r = false;
		switch ($UriReq) {
			case self::URI_REQ_NONE:
switch ($_SERVER['REQUEST_URI']) {
case '/cms/about':		$r = array('rights'=>CUser::GUEST,		'include'=>'cms/about.php'); break;
case '/cms/fb/vi':		$r = array('rights'=>CUser::GUEST,		'include'=>'cms/fb/vi.php'); break;
case '/cms/mail':		$r = array('rights'=>CUser::GUEST,		'include'=>'cms/mail.php'); break;
case '/login':			$r = array('rights'=>CUser::GUEST,		'include'=>'login.php'); break;
case '/logout':			$r = array('rights'=>CUser::GUEST,		'include'=>'logout.php'); break;
}
				break;
			case self::URI_REQ_AUTH:
switch ($_SERVER['REQUEST_URI']) {
case '/cms/cache/clear':	$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/cache/clear.php'); break;
//case '/cms/cache/clear/img':	$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/cache/clear_img.php'); break;
case '/cms/config':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/config.php'); break;
case '/cms/css/style':		$r = array('rights'=>CUser::MODERATOR,		'include'=>'cms/css_style.php'); break;
case '/cms/fb':			$r = array('rights'=>CUser::MODERATOR,		'include'=>'cms/fb/index.php'); break;
case '/cms/fb/action':		$r = array('rights'=>CUser::MODERATOR,		'include'=>'cms/fb/action.php'); break;
case '/cms/fb/ci':		$r = array('rights'=>CUser::MODERATOR,		'include'=>'cms/fb/ci.php'); break;
case '/cms/install':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/install.php'); break;
case '/cms/phpinfo':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/phpinfo.php'); break;
case '/cms/templates/gallery':	$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/templates/gallery.php'); break;
case '/cms/templates/properties':$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/templates/properties.php'); break;
case '/cms/templates/upload':	$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/templates/upload.php'); break;
case '/cms/update':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/update.php'); break;
case '/cms/txt':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/txt.php'); break;
}
				break;
			case self::URI_REQ_CONN:
switch ($_SERVER['REQUEST_URI']) {
case '/cms':			$r = array('rights'=>CUser::MODERATOR,		'template'=>'cms/index.xml'); break;
case '/cms/list':		$r = array('rights'=>CUser::MODERATOR,		'template'=>'cms/list.xml'); break;
case '/cms/action':		$r = array('rights'=>CUser::MODERATOR,		'eval'=>'CCore::checkPost(CCore::CHECK_POST_REDIRECT, isset($_POST[\'redirect\']) ? $_POST[\'redirect\'] : \'\');'); break;
case '/cms/ajax':		$r = array('rights'=>CUser::GUEST,		'eval'=>'$this->buildAjax(); exit;'); break;
case '/cms/ajax/ins':		$r = array('rights'=>CUser::MODERATOR,		'include'=>'cms/ajax/ins.php'); break;
case '/cms/converter':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/converter.php'); break;
case '/cms/assoc':		$r = array('rights'=>CUser::MODERATOR,		'template'=>'cms/assoc.xml'); break;
case '/cms/forget':		$r = array('rights'=>CUser::GUEST,		'include'=>'cms/forget.php'); break;
case '/cms/form':		$r = array('rights'=>CUser::GUEST,		'eval'=>'$f = new CForm(); $f->init(); $f->check();'); break;
case '/cms/form/image':		$r = array('rights'=>CUser::GUEST,		'eval'=>'$f = new CForm(); $f->init(); $f->image();'); break;
case '/cms/head':		$r = array('rights'=>CUser::MODERATOR,		'template'=>'cms/head.xml'); break;
case '/cms/import':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/import.php'); break;
case '/cms/map':		$r = array('rights'=>CUser::MODERATOR,		'template'=>'cms/map.xml'); break;
case '/cms/page':		$r = array('rights'=>CUser::MODERATOR,		'template'=>'cms/page.xml'); break;
case '/cms/rights':		$r = array('rights'=>CUser::ADMINISTRATOR,	'template'=>'cms/rights.xml'); break;
case '/cms/stat':		$r = array('rights'=>CUser::ADMINISTRATOR,	'template'=>'cms/stat/index.xml'); break;
case '/cms/stat/search':	$r = array('rights'=>CUser::ADMINISTRATOR,	'template'=>'cms/stat/search.xml'); break;
case '/sitemap.xml':		$r = array('rights'=>CUser::GUEST,		'template'=>'sitemap.xml'); break;
case '/cms/email':		$r = array('rights'=>CUser::ADMINISTRATOR,	'template'=>'cms/email.xml'); break;
case '/cms/export':		$r = array('rights'=>CUser::ADMINISTRATOR,	'include'=>'cms/export.php'); break;
}
if ($Install) $r = array('rights'=>1, 'include'=>'cms/install.php');
				break;
                }
		if ($r) {
			if (!$this->USER->checkRights($r['rights'])) $this->HTTPReturn(403);
			if (isset($r['eval'])) eval($r['eval']);
			else if (isset($r['include'])) require_once DIR_ROOT.'/cms/pages/'.$r['include'];
			else if (isset($r['template'])) {
				$GLOBALS['BACKEND'] = true;
				$this->buildPage('/cms/pages/'.$r['template']);
			}
		} else if ($UriReq==self::URI_REQ_CONN) {
			if (!$this->USER->checkRights(CUser::GUEST)) $this->HTTPReturn(403);
			if (CMS_LOG=='true' && !$this->USER->checkRights(CUser::MODERATOR)) $this->addStat();
			$this->buildPage();
		}
        }

	private function addStat() {
		$unique = $this->DBC->statUnique();
		$host = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
		$external = isset($_SERVER['HTTP_REFERER'])
			&& !(bool)@preg_match('/^(http\:\/\/)?(www\.)?'.$host.'/', $_SERVER['HTTP_REFERER']);
		$google = (bool)@preg_match('/^(http\:\/\/)?(www\.)?google.ru/', $_SERVER['HTTP_REFERER']);
		$yandex = (bool)@preg_match('/^(http\:\/\/)?(www\.)?yandex.ru/', $_SERVER['HTTP_REFERER']);
		$rambler = (bool)@preg_match('/^(http\:\/\/)?(www\.)?nova.rambler.ru/', $_SERVER['HTTP_REFERER']);

		$this->DBC->statAdd($unique, $external, $google, $yandex, $rambler);
		
		if ($google || $yandex || $rambler) {
			parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $params);
			if ($google) $search = $params['q'];
                        else if ($yandex) $search = $params['text'];
			else if ($rambler) $search = $params['query'];
			
			if (extension_loaded('mbstring')) $search = mb_convert_case(mb_convert_encoding($search,
				'UTF-8', 'UTF-8, Windows-1251'), MB_CASE_LOWER, 'UTF-8');
			
			if ($yandex && isset($params['rstr'])) {
				$search .= ' [Область поиска: ';
				switch ($params['rstr']) {
					case '-54': $search .= 'регион - Екатеринбург'; break;
					case '-52': $search .= 'регион - Урал'; break;
					default: $search .= $params['rstr']; break;
				}
				$search .= ']';
			}

			$this->DBC->statSearchAdd($search, $google, $yandex, $rambler);
                }
        }
	
	public static function checkPost($Result, $Redirect = '') {
		if (isset($_GET['async'])) switch ($_GET['async']) {
			case 'exit': $Result = self::CHECK_POST_EXIT; break;
			case 'yes': $Result = self::CHECK_POST_RETURN; break;
                }

		if (isset($_POST['_a_reverse']) && $_POST['_a_reverse']) $_POST['_a'] = array_reverse($_POST['_a']);

		if (isset($_POST['_a']))
			foreach ($_POST['_a'] as $i=>$a)
				if (isset($a['action'])) {
					$action = new CAction($_POST['_a'][$i]);
					$action->init();
					if ($action->check()) $action->exec();
				}
		
		if ($Redirect!='') $_SERVER['HTTP_REFERER'] = $Redirect;
		else if (isset($_POST['redirect'])) $_SERVER['HTTP_REFERER'] = $_POST['redirect'];
//die();
		switch ($Result) {
			case self::CHECK_POST_EXIT:
				header('Content-Type: application/xml; charset=UTF-8');
				echo $_SERVER['HTTP_REFERER'];
				exit;
			case self::CHECK_POST_REDIRECT:
				if (empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'][0]=='?')
					$GLOBALS['CORE']->HTTPReturn(500);
				header('location: '.$_SERVER['HTTP_REFERER']);
				exit;
			case self::CHECK_POST_RETURN:
				header('Content-Encoding: gzip');
				$fp = fopen('http://'.$_SERVER['HTTP_HOST'].$_SERVER['HTTP_REFERER'], 'rb');
				fpassthru($fp);
				fclose($fp);
				exit;
			case self::CHECK_POST_NONE: break;
		}
        }
	
	public function templateDocumentOnload(DOMDocument $D) {
		$elements = $D->documentElement->getElementsByTagNameNS('/templates/ns', 'info');
		if ($elements->length>0) {
			$template = $elements->item(0)->cloneNode(true);
			$vars = array('content'=>'');
			$params = array();
			$this->build($template, $vars, $params);
                }
	}

	public function getRequiredVars(DOMElement $TemplateElement, array $ExistVars = array()) {
		$r = array('vars'=>array(), 'parent'=>array(), 'external'=>array());
		foreach ($TemplateElement->getElementsByTagNameNS('/templates/ns', 'var') as $e) {
			$var = $e->getAttribute('name');
			if ($var!='' && !in_array($var, $ExistVars)) {
				if (substr($var, 0, 7)=='parent.') {
					$var = substr($var, 7);
					if (!in_array($var, $r['parent'])) $r['parent'][] = $var;
				} else if (substr($var, 0, 9)=='external.') {
					$var = substr($var, 9);
					if (!in_array($var, $r['external'])) $r['external'][] = $var;
				} else if (in_array($var, $GLOBALS['CMS_VARS']) && !in_array($var, $r['vars']))
					$r['vars'][] = $var;
			}
                }
		foreach ($TemplateElement->getElementsByTagNameNS('/templates/ns', 'eval') as $e) {
			preg_match_all('/[^\_]VARS\[[\'\"]([^\'\"]+)[\'\"]\]/u', $e->textContent, $matches);
			for ($i=0; $i<count($matches[0]); $i++) {
				$var =& $matches[1][$i];
				if ($var!='' && !in_array($var, $ExistVars)) {
					if (substr($var, 0, 7)=='parent.') {
						$var = substr($var, 7);
						if (!in_array($var, $r['parent'])) $r['parent'][] = $var;
					} else if (substr($var, 0, 9)=='external.') {
						$var = substr($var, 9);
						if (!in_array($var, $r['external'])) $r['external'][] = $var;
					} else if (in_array($var, $GLOBALS['CMS_VARS']) && !in_array($var, $r['vars']))
						$r['vars'][] = $var;
				}
                        }
                }
		if (DB_TYPE=='xml') {
			$r['vars'] = array();
			if (!in_array('parent.id', $ExistVars) && !in_array('id', $r['parent'])) $r['parent'][] = 'id';
			$r['external'] = array();
                }
		if (empty($r['vars']) && empty($r['parent']) && empty($r['external'])) return false;
		else return $r;
        }
	
	private function insertEditor(&$Str) {
        	$Str = str_ireplace('<head>',
			'<head>
	<link rel="stylesheet" type="text/css" href="/cms/css/editor.css" />
	<script type="text/javascript" src="/cms/js/cms.js"></script>
	<script type="text/javascript" src="/cms/js/editor.js"></script>
	<script type="text/javascript" src="/cms/js/modaldlg.js"></script>
	<script type="text/javascript">
		window.CmsUserName="'.$this->USER->getVar('user.name').'";
		window.CmsGenerateTime="'.sprintf('%.3f', $this->getTime()).'";
	</script>',
			$Str);
		$Str = preg_replace('/(\<body[^\>]*\>)/iu', '$1'
			.file_get_contents(DIR_ROOT.'/cms/includes/editor.html'), $Str);
		$Str = str_replace('</body>', '<script type="text/javascript">_cmsInit("true");</script></body>', $Str);
	}
	
	public function stripTempTags(&$Str) {
		$Str = str_replace(array('<temp xmlns:t="/templates/ns">', '<temp>', '</temp>',
			'<temp xmlns:t="/templates/ns"/>', '<temp/>'), '', $Str);
        }
	
	public static function innerXML(DOMElement $Element) {
		return '<temp xmlns:t="/templates/ns">'.DOMElementInnerXML($Element).'</temp>';
        }
	
	public function checkCache($Type, &$Vars) {
		if (
			CMS_CACHE!=$Type || (isset($Vars['cash']) && $Vars['cash']=='no') || $GLOBALS['VIEW_STRICT']
			|| ($this->USER->checkRights(CUser::MODERATOR) && CMS_CACHE!='sql')
			) {
			if ($Type=='page') header('Cache-Control: no-cache');
			return false;
		}
		$filename = DIR_ROOT.'/_cache'.($Vars['link']=='/' ? '' : $Vars['link']);
		if (!preg_match('/\.\w+$/u', $filename)) $filename .= '/index.html.gz';
		$buf = '';
		if ($_SERVER['QUERY_STRING']!='') $buf .= '%3F'.$_SERVER['QUERY_STRING'];
		if ($GLOBALS['REQUEST_PAGE']>1) $buf .= ($buf=='' ? '%3F' : '&').'page='.$GLOBALS['REQUEST_PAGE'];
		$filename .= $buf;
		
		if (file_exists($filename) && date('Y-m-d H:i:s', filemtime($filename))>=$Vars['publish_time']) {
			switch (CMS_CACHE) {
				case 'page':
					//$this->output(file_get_contents($filename));
					$zp = gzopen($filename, 'rb');
					gzpassthru($zp);
					gzclose($zp);
					exit;
				case 'main':
					//return file_get_contents($filename);
					$buf = '';
					$zp = gzopen($filename, 'rb');
					while (!gzeof($zp)) $buf .= gzread($zp, 8192);
					gzclose($zp);
					return $buf;
				case 'sql':
					$buf = '';
					$zp = gzopen($filename, 'rb');
					while (!gzeof($zp)) $buf .= gzread($zp, 8192);
					gzclose($zp);
					$this->cache = unserialize($buf);
					$this->cache_modified = false;
					return true;
			}
		} else return true;
	}
	
	public function saveCache(&$Vars, &$Result) {
		$filename = '_cache'.($Vars['link']=='/' ? '' : $Vars['link']);
		if (!preg_match('/\.\w+$/u', $filename)) $filename .= '/index.html.gz';
		$dirs = explode('/', dirname($filename));
		$dir = '';
		foreach ($dirs as $d) {$dir .= '/'.$d; if (!file_exists(DIR_ROOT.$dir)) mkdir(DIR_ROOT.$dir);}
		$buf = '';
		if ($_SERVER['QUERY_STRING']!='') $buf .= '%3F'.$_SERVER['QUERY_STRING'];
		if ($GLOBALS['REQUEST_PAGE']>1) $buf .= ($buf=='' ? '%3F' : '&').'page='.$GLOBALS['REQUEST_PAGE'];
		$filename .= $buf;
		switch (CMS_CACHE) {
			case 'page': case 'main':
				//file_put_contents(DIR_ROOT.'/'.$filename, $Result);
				$zp = gzopen(DIR_ROOT.'/'.$filename, 'wb');
				gzwrite($zp, $Result);
				gzclose($zp);
				break;
			case 'sql':
				if ($this->cache_modified) {
					$zp = gzopen(DIR_ROOT.'/'.$filename, 'wb');
					gzwrite($zp, serialize($this->cache));
					gzclose($zp);
				}
				break;
		}
	}

	public function buildAjax() {
		if (isset($_POST['template'])) $template = stripslashes($_POST['template']);
		else if (isset($_GET['template'])) $template = stripslashes($_GET['template']);
		else return false;

		if (CMS_CACHE=='sql' && preg_match('/id\=[\'\"]([^\'\"]+)[\'\"]/i', $template, $matches)) {
			$vars = $this->DBC->getVarsById($matches[1]);
			$saveCache = $this->checkCache(CMS_CACHE, $vars);
		} else $saveCache = false;

		echo $result = Build($template);

		if ($saveCache) $this->saveCache($vars, $result);
        }

	private function buildPage($Template = null) {
		$baseRequiredVars = array('vars'=>array_merge($GLOBALS['CMS_REQUIRED_VARS'],
			array('cash', 'data_only', 'publish_time', 'use_content_in_head', 'template_src', 
				'childrens_template_src', 'head', 'keywords', 'description', 'content')),
			'parent'=>array(), 'external'=>array());
		$vars = $this->DBC->getVars($_SERVER['REQUEST_URI'], $baseRequiredVars);
		if (!$vars) {
			if ($GLOBALS['BACKEND']) {
				$vars = array('link'=>$_SERVER['REQUEST_URI']);
				switch ($_SERVER['REQUEST_URI']) {
					case '/sitemap.xml':
						$vars['cash'] = 'yes';
						$vars['publish_time'] = $this->DBC->getLastPublishTime();
						break;
					default:
						$vars['id'] = '';
						$vars['cash'] = 'no';
						break;
                                }
                        }
			else if ($this->USER->checkRights(CUser::MODERATOR)) $vars = array(
				'id'=>'',
				'parent_id'=>'',
				'link'=>$_SERVER['REQUEST_URI'],
				'content'=>@file_get_contents(DIR_ROOT.'/cms/pages/e404.html'));
			else $this->HTTPReturn(404);
                }
		
		if (isset($vars['data_only']) && $vars['data_only']!='no'
			&& !$this->USER->checkRights(CUser::MODERATOR)) $this->HTTPReturn(403);

		if (is_null($Template)) {
			if (isset($vars['template_src']) && $vars['template_src']!='')
				$Template = $vars['template_src'];
			else $Template = DIR_ROOT.'/templates/root.xml';
		}
		$template = $this->TEMPLATES->getTemplateElement($Template);
		if ($templateRequiredVars = $this->getRequiredVars($template, array_keys($vars)))
			$vars = (array)$this->DBC->getVarsById($vars['id'], $templateRequiredVars) + $vars;
			//$vars = array_merge_recursive($this->DBC->getVarsById($vars['id'], $templateRequiredVars),
			//	$vars);

		switch (CMS_CACHE) {
			case 'page': case 'sql':
				$saveCache = $this->checkCache(CMS_CACHE, $vars);
				break;
			default: $saveCache = false; break;
		}

		$params = array('template'=>$Template);
		$result = $this->build($template, $vars, $params);
		$result = str_replace('</head>', $GLOBALS['HEAD_ADD'].'</head>', $result);
		$result = str_replace(array('<![CDATA[', ']]>'), '', $result);
		$this->stripTempTags($result);
		if (!$GLOBALS['VIEW_STRICT'] && !$GLOBALS['BACKEND'] && $this->USER->checkRights(CUser::MODERATOR))
			$this->insertEditor($result);
		if ($_SERVER['REQUEST_URI']=='/' && !$GLOBALS['BACKEND'])
			$result = str_ireplace('</body>', ARTGK_BANNER_TEXT.'</body>', $result);

		if ($saveCache) $this->saveCache($vars, $result);
		
		$this->OUTPUT .= $result;
		if (DEBUG_MODE=='true') $this->OUTPUT .= $this->showStat();
        }

	public function build(DOMElement &$Template, array &$Vars, array &$Params) {
		if (empty($Vars)) {
			$elements = $Template->getElementsByTagNameNS('/templates/ns', 'empty');
			if ($elements->length>0) $Template = $elements->item(0)->cloneNode(true);
			else return '';
		}

		if (isset($GLOBALS['Vars'])) $oldVars = $GLOBALS['Vars'];
		$GLOBALS['Vars'] = $Vars;

		if (isset($GLOBALS['Params'])) $oldParams = $GLOBALS['Params'];
		$GLOBALS['Params'] = $Params;

		while (($elements = $Template->getElementsByTagNameNS('/templates/ns', '*')) && $elements->length>0)
			CTag::replace($elements->item(0), $Vars, $Params);

		if (isset($oldVars)) $GLOBALS['Vars'] = $oldVars;
		else unset($GLOBALS['Vars']);

		if (isset($oldParams)) $GLOBALS['Params'] = $oldParams;
		else unset($GLOBALS['Params']);

		$result = DOMElementInnerXML($Template);
//		$result = htmlspecialchars_decode($result);
		$result = str_replace(array('&lt;', '&gt;', '&quot;', '&amp;'), array('<', '>', '"', '&'), $result);
		return $result;
        }

}

?>