<?php

class CCoreObject
{
	protected $CORE;
	protected $DBC;
	protected $LOG;
	protected $TEMPLATES;
	protected $USER;
	protected $OUTPUT;
	
	public function init()
	{
		global $CORE;
		global $DBC;
		global $TEMPLATES;
		global $USER;
		global $OUTPUT;
		$this->CORE =& $CORE;
		$this->DBC =& $DBC;
		$this->OUTPUT =& $OUTPUT;
		$this->TEMPLATES =& $TEMPLATES;
		$this->USER =& $USER;
	}
}









class CDBC extends CCoreObject {
	
	private $Connected = false;
	private $QueryNum = 0;
	private $QueryLog = '';
	protected $UsePublicVersion = true;
	
	public function isConnected() {return $this->Connected;}
	public function getQueryNum() {return $this->QueryNum;}
	public function getQueryLog() {return $this->QueryLog;}
	public function usePublicVersion($b) {
		$buf = $this->UsePublicVersion;
		$this->UsePublicVersion = $b;
		return $buf;
	}

	public function connect($Type = DB_TYPE,
				$Host = DB_HOST, $User = DB_USER, $Pass = DB_PASS, $Name = DB_NAME,
				$File = DB_FILE) {
		switch ($Type) {
			case 'mysql': $this->DBC = new CDBCMySQL(); break;
			case 'xml': $this->DBC = new CDBCXML(); break;
                }
		$this->DBC->init();
		$this->DBC->Connected = $this->DBC->connect($Host, $User, $Pass, $Name, $File);
        }
	
	protected function _query($Query) {
		return null;
        }

	protected function query($Query, $a1 = null, $a2 = null) {
		$key = md5($Query);
		$result = $this->CORE->cache_get($key);
		if ($result===FALSE) {
			if (DEBUG_MODE=='true') {
				$this->QueryNum++;
				$this->QueryLog .= $Query."<br />\n";
			}
			$result = $this->_query($Query, $a1, $a2);
			$this->CORE->cache_set($key, $result);
		}
		return $result;
	}

	protected function dquery($Query, $a1 = null, $a2 = null) {
		var_dump($Query);
		return $this->query($Query, $a1, $a2);
        }

	protected function _dquery($Query, $a1 = null, $a2 = null) {
		var_dump($Query);
		return $this->_query($Query, $a1, $a2);
        }
}









class CTemplates extends CCoreObject
{

	private $TemplateDocuments = array();
	private $LoadedDocumentsNum = 0;

	public function getLoadedDocumentsNum()
	{
		return $this->LoadedDocumentsNum;
	}

	public function getRealSrc($Src)
	{
		if (file_exists($Src))
			return $Src;
		else if (file_exists(DIR_ROOT.'/'.$Src))
			$Src = DIR_ROOT.'/'.$Src;
		else if (file_exists(DIR_ROOT.'/templates/'.$Src))
			$Src = DIR_ROOT.'/templates/'.$Src;
		else if (file_exists(DIR_ROOT.'/cms/templates/'.$Src))
			$Src = DIR_ROOT.'/cms/templates/'.$Src;
		else
		{
			trigger_error(sprintf('file "%s" not found', $Src), E_USER_NOTICE);
			$Src = DIR_ROOT.'/cms/templates/default.xml';
		}
		return $Src;
	}

	public function getRootRelativeSrc($Src)
	{
		return str_replace(DIR_ROOT.'/', '', $Src);
	}

	public function getTemplateDocment($Src)
	{
		if (!array_key_exists($Src, $this->TemplateDocuments))
		{
			$d = new DOMDocument('1.0', 'UTF-8');

/*			if (TEMPLATES_PHP_CACHE=='true')
			{
				$phpSrc = substr($Src, 0, -4).'.php';
				if (!file_exists($phpSrc))
					file_put_contents($phpSrc, "<?php\n\$template_string = <<<END\n"
						.str_replace('$', '\$', file_get_contents($Src))
						."\nEND;\n?>");
				include $phpSrc;
				$d->loadXML($template_string);
				$d->documentURI = $Src;
			}
			else*/
			{
				$d->load($Src) || trigger_error(sprintf('file "%s" not found', $Src), E_USER_ERROR);
			}

			if (!is_null($this->CORE))
				$this->CORE->templateDocumentOnload($d);
			$this->TemplateDocuments[$Src] = $d;
			$this->LoadedDocumentsNum++;
		}
		else
			$d = $this->TemplateDocuments[$Src];
		return $d;
	}
	
	public function templateInfo($Template)
	{
		if (substr($Template, 0, 4)=='xml:')
			return array(null, null, true);
		$r = explode('@', $Template);
		if (count($r)>1)
			return array($r[0], $r[1], false);
		else if (substr($r[0], -4)=='.xml')
			return array('index', $r[0], false);
		else
			return array($r[0], null, false);
	}

	public function getTemplateElement(&$Template, $BaseTemplate = 'index@cms/templates/default.xml')
	{
		list($name, $src, $xml) = $this->templateInfo($Template);
		if ($xml)
		{
			$template = substr($Template, 4);
			$d = new DOMDocument('1.0', 'UTF-8');
			$d->loadXML('<t:template xmlns:t="/templates/ns">'.$template.'</t:template>');
			return $d->documentElement;
		} else if (is_null($src))
			list($name2, $src, $xml) = $this->templateInfo($BaseTemplate);

		if ($name=='')
			$name = 'index';
		$src = $this->getRealSrc($src);
		$Template = $name.'@'.$src;
		$d = $this->getTemplateDocment($src);
		foreach ($d->getElementsByTagNameNS('/templates/ns', 'template') as $t)
			if ($t->getAttribute('id')==$name)
				return $t->cloneNode(true);
		trigger_error(sprintf('template "%s" not found', $Template), E_USER_ERROR);
	}

}









class CUser extends CCoreObject {
	
	const NONE		= 0;
	const ADMINISTRATOR	= 1;
	const MODERATOR		= 2;
	const GUEST		= 4;
	const GUEST_SAVE	= 8;
	const GUEST_PUBLISH	= 16;
	const GUEST_DELETE	= 32;
	const GUEST_ASSOC	= 64;
	
	public $Vars = array();
	private $Rights = self::GUEST;

	public function setRights($Rights) {
		if ($this->DBC->isConnected()) return false;
		else $this->Rights = $Rights;
        }

	public function checkRights($Rights) {
		if ($this->Rights==self::NONE) return false;
		else if (($this->Rights & self::ADMINISTRATOR)==self::ADMINISTRATOR) return true;
		else if ($this->Rights<=self::GUEST && $this->Rights<=$Rights) return true;
		else return ($this->Rights & $Rights)==$Rights;
	}

	public function getVar($Name) {
		return isset($this->Vars[$Name]) ? $this->Vars[$Name] : null;
        }

	public function authenticate() {
		if (!empty($_SESSION['userdata']) && $_SESSION['userdata']['domain']==getenv('HTTP_HOST'))
		{
			$this->Vars = $_SESSION['userdata']['vars'];
//			$this->Rights = $_SESSION['userdata']['rights'];
			$this->Rights = $this->DBC->getUserRights();
		}
		else
		{
			if (!isset($_SESSION['user']) || !($this->Vars = $this->DBC->authenticate($_SESSION['user'],
				$_SESSION['password']=='d41d8cd98f00b204e9800998ecf8427e' ? '' : $_SESSION['password']))
				)
			{
				if (empty($_COOKIE['user']) || empty($_COOKIE['password'])
						|| !($this->Vars = $this->DBC->authenticate($_COOKIE['user'],
						$_COOKIE['password']=='d41d8cd98f00b204e9800998ecf8427e' ? '' : $_COOKIE['password'])))
					$this->Vars = $this->DBC->authenticate('guest', '');
				else
				{
					if (!isset($_COOKIE[session_name()]))
						session_start();
					$_SESSION['userdata'] = NULL;
					$_SESSION['user'] = $_COOKIE['user'];
					$_SESSION['password'] = $_COOKIE['password'];
				}
			}
			$this->Rights = $this->DBC->getUserRights();
			if ($this->Vars['user.name']!='guest')
			{
				$_SESSION['userdata'] = array('vars'=>$this->Vars, 'rights'=>$this->Rights,
					'domain'=>getenv('HTTP_HOST'));
			}
			else
				$_SESSION['userdata'] = NULL;
		}

		if ($this->checkRights(self::MODERATOR))
		{
			$this->DBC->usePublicVersion(false);
			if (CMS_LOG=='true' && $_SERVER['REQUEST_URI']=='/cms/action' && !empty($_POST))
				$this->DBC->logAdd($_SERVER['REMOTE_ADDR'], $this->Vars['user.name'],
					$_SERVER['REQUEST_URI'].'?'.$_SERVER['QUERY_STRING'], serialize($_POST));
		}
	}
	
}

?>
