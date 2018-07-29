<?php

class CDBCMySQL extends CDBC implements IDBC {

	private $Link;

	public function connect($Host = DB_HOST, $User = DB_USER, $Pass = DB_Pass, $Name = DB_NAME, $File = DB_FILE) {
		$this->Link = mysql_connect($Host, $User, $Pass) or die('1');
		mysql_select_db($Name, $this->Link) or die('2');
		mysql_query('SET NAMES "UTF8"', $this->Link);
		mysql_query('SET TIME_ZONE = "'.preg_replace('/(..)$/', ':$1', date('O')).'"', $this->Link);
		mysql_query('SET TIMESTAMP = '.time(), $this->Link);
		return true;
        }
	
	private function SQLCheckVersion($TableAlias) {
		if ($this->UsePublicVersion) {
			$time = date('Y-m-d H:i:s');
			return '`'.$TableAlias.'`.`version`="0000-00-00 00:00:00" AND `'
				.$TableAlias.'`.`publish_time`<="'.$time.'" AND (`'
				.$TableAlias.'`.`expire_time`="0000-00-00 00:00:00" OR `'
				.$TableAlias.'`.`expire_time`>="'.$time.'")';
		} else return '`'.$TableAlias.'`.`version` IN (SELECT MAX(`version`) FROM `'.TABLE_PAGE.'` WHERE `id`=`'
			.$TableAlias.'`.`id`)';
	}
	
//	protected function _query(&$Query) {
//		return mysql_query($Query, $this->Link);
//	}

	protected function _query($Query) {
//		$t = microtime(true);
		$result = array();
		$r = mysql_query($Query, $this->Link);
		if (is_bool($r)) return $r;
		while ($row = mysql_fetch_assoc($r)) $result[] = $row;
//		echo $Query, '<br />', count($result), ' ', (microtime(true) - $t), '<br />';
//		tstop1();
		return $result;
	}

	private function num_rows(&$Result) {
		return count($Result);
	}
	
	public static function install($AdminName = 'artgk', $AdminPass = '', $StructOnly = false) {
		mysql_query('DROP TABLE IF EXISTS `CmsAssoc`');
		mysql_query("CREATE TABLE `CmsAssoc` (
				`id` int(10) unsigned NOT NULL default '0',
				`foreign_id` int(10) unsigned NOT NULL default '0',
				PRIMARY KEY  (`id`,`foreign_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		mysql_query('DROP TABLE IF EXISTS `CmsGroup`');
		mysql_query("CREATE TABLE `CmsGroup` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`name` varchar(255) NOT NULL default '',
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		mysql_query('DROP TABLE IF EXISTS `CmsPage`');
		mysql_query("CREATE TABLE `CmsPage` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`version` timestamp NOT NULL default CURRENT_TIMESTAMP,
				`create_time` timestamp NOT NULL default '0000-00-00 00:00:00',
				`publish_time` timestamp NOT NULL default '0000-00-00 00:00:00',
				`expire_time` timestamp NOT NULL default '0000-00-00 00:00:00',
				`cash` enum('yes','no') NOT NULL default 'yes',
				`data_only` enum('yes','no','childs') NOT NULL default 'no',
				`owner_id` int(10) unsigned NOT NULL default '0',
				`parent_id` int(10) unsigned default NULL,
				`external_class` varchar(255) default NULL,
				`n` int(11) NOT NULL default '0',
				`template_src` varchar(255) default NULL,
				`childrens_template_src` varchar(255) default  NULL,
				`link` varchar(255) NOT NULL default '',
				`use_content_in_head` enum('true','false','path') NOT NULL default 'path',
				`head` text NOT NULL,
				`title` varchar(255) NOT NULL default '',
				`keywords` varchar(255) NOT NULL default '',
				`description` text NOT NULL, `image` text NOT NULL,
				`content` mediumtext NOT NULL,
				PRIMARY KEY  (`id`,`version`),
				UNIQUE KEY `link` (`version`,`link`),
				FULLTEXT KEY `fulltext` (`title`, `keywords`, `description`, `content`),
				KEY (`parent_id`),
				KEY (`n`),
				KEY (`version`),
				KEY (`create_time`),
				KEY (`publish_time`),
				KEY (`expire_time`),
				KEY (`title`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		mysql_query('DROP TABLE IF EXISTS `CmsRights`');
		mysql_query("CREATE TABLE `CmsRights` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`ug_id` int(10) unsigned NOT NULL default '0',
				`is_group` enum('true','false') NOT NULL default 'false',
				`link` varchar(255) default NULL,
				`allow` smallint(5) unsigned NOT NULL default '0',
				`disallow` smallint(5) unsigned NOT NULL default '0',
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		mysql_query('DROP TABLE IF EXISTS `CmsUser`');
		mysql_query("CREATE TABLE `CmsUser` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`group_id` int(10) unsigned NOT NULL default '1',
				`name` varchar(255) NOT NULL default '',
				`password` varchar(255) NOT NULL default '',
				`expire` timestamp NOT NULL,
				`email` varchar(255) NOT NULL default '',
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		mysql_query('DROP TABLE IF EXISTS `CmsLog`');
		mysql_query('CREATE TABLE `CmsLog` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`time` timestamp NOT NULL default CURRENT_TIMESTAMP,
				`ip` varchar(15) NOT NULL,
				`user` varchar(255) default NULL,
				`link` varchar(255) NOT NULL,
				`post` text NOT NULL,
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		mysql_query('DROP TABLE IF EXISTS `CmsForm`');
		mysql_query("CREATE TABLE `CmsForm` (
				`id` varchar(32) NOT NULL,
				`time` timestamp NOT NULL default CURRENT_TIMESTAMP,
				`code` varchar(255) NOT NULL default '',
				`redirect` varchar(255) NOT NULL default '',
				`checkform` text NOT NULL,
				`code2` varchar(255) NOT NULL default '',
				`post` text NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		mysql_query('DROP TABLE IF EXISTS `CmsStat`');
		mysql_query('CREATE TABLE `CmsStat` (
				  `year` smallint(6) NOT NULL,
				  `month` tinyint(4) NOT NULL,
				  `click` int(10) unsigned NOT NULL,
				  `unique` int(10) unsigned NOT NULL,
				  `external` int(10) unsigned NOT NULL,
				  `google` int(10) unsigned NOT NULL,
				  `yandex` int(10) unsigned NOT NULL,
				  `rambler` int(10) unsigned NOT NULL,
				  `unique_list` mediumtext NOT NULL,
				  PRIMARY KEY  (`year`,`month`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		mysql_query('DROP TABLE IF EXISTS `CmsSearchStat`');
		mysql_query('CREATE TABLE `CmsSearchStat` (
				`year` smallint(6) NOT NULL,
				`month` tinyint(4) NOT NULL,
				`search` varchar(255) NOT NULL,
				`google` int(10) unsigned NOT NULL,
				`yandex` int(10) unsigned NOT NULL,
				`rambler` int(10) unsigned NOT NULL,
				`sum` int(10) unsigned NOT NULL,
				PRIMARY KEY  (`year`,`month`,`search`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		mysql_query('DROP TABLE IF EXISTS `CmsCounter`');
		mysql_query('CREATE TABLE `CmsCounter` (
				`id` varchar(255) NOT NULL,
				`count` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		
		if (!$StructOnly) {
			mysql_query("INSERT INTO `CmsGroup` VALUES (1, 'default')");
			mysql_query("INSERT INTO `CmsGroup` VALUES (2, 'administrators')");
			mysql_query("INSERT INTO `CmsGroup` VALUES (3, 'moderators')");
			mysql_query("INSERT INTO `CmsUser` VALUES (1, 1, 'guest', '', '2030-01-01 00:00:00', '')");
			mysql_query("INSERT INTO `CmsUser` VALUES (2, 2, '$AdminName', "
				."'$AdminPass', '2030-01-01 00:00:00', '')");
			mysql_query("INSERT INTO `CmsRights` VALUES (1, 1, 'false', NULL, 4, 0)");
			mysql_query("INSERT INTO `CmsRights` VALUES (2, 2, 'true', NULL, 1, 0)");
			mysql_query("INSERT INTO `CmsRights` VALUES (3, 3, 'true', NULL, 2, 0)");
			mysql_query("INSERT INTO `CmsRights` VALUES (4, 1, 'false', '/cms/form', 24, 0)");
			mysql_query("INSERT INTO `CmsPage`(`id`,`version`, `link`, `title`) VALUES ('1','"
				.date('Y-m-d H:i:s', time()-1)."', '/', '".$_SERVER['HTTP_HOST']."')");
		}
	}
	
	private static function exportXML1(DOMDocument &$D, DOMElement &$E, $Time, $ParentVars = NULL) {
		$parentCond = is_null($ParentVars) ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$ParentVars['id'].'"';
		$r = mysql_query('SELECT * FROM `'.TABLE_PAGE.'` WHERE '.$parentCond
			.' AND `version`="0000-00-00 00:00:00"');
		while ($row = mysql_fetch_assoc($r)) {
			$p = $D->createElement('page');
			$p->setAttribute('id', $row['id']);
			$p->setAttribute('version', $Time);
			$p->setAttribute('create_time', $row['create_time']);
			$p->setAttribute('publish_time', $row['publish_time']);
			$p->setAttribute('cash', $row['cash']);
			$p->setAttribute('data_only', $row['data_only']);
			$p->setAttribute('owner_id', $row['owner_id']);
			$p->setAttribute('external_class', $row['external_class']);
			$p->setAttribute('n', $row['n']);
			$p->setAttribute('template_src', $row['template_src']);
			$p->setAttribute('childrens_template_src', $row['childrens_template_src']);
			$p->setAttribute('link', $row['link']);
			$p->setAttribute('use_content_in_head', $row['use_content_in_head']);
			$E->appendChild($p);
			$e = $D->createElement('head');
			if (!empty($row['head'])) {
				$p->appendChild($e);
				$f = $D->createDocumentFragment();
				$f->appendXML($row['head']);
				$e->appendChild($f);
			}
			$e = $D->createElement('title');
			if (!empty($row['title'])) {
				$p->appendChild($e);
				$f = $D->createDocumentFragment();
				$f->appendXML($row['title']);
				$e->appendChild($f);
			}
			$e = $D->createElement('keywords');
			if (!empty($row['keywords'])) {
				$p->appendChild($e);
				$f = $D->createDocumentFragment();
				$f->appendXML($row['keywords']);
				$e->appendChild($f);
			}
			$e = $D->createElement('description');
			if (!empty($row['description'])) {
				$p->appendChild($e);
				$f = $D->createDocumentFragment();
				$f->appendXML($row['description']);
				$e->appendChild($f);
			}
			$e = $D->createElement('image');
			if (!empty($row['image'])) {
				$p->appendChild($e);
				$f = $D->createDocumentFragment();
				$f->appendXML($row['image']);
				$e->appendChild($f);
			}
			$e = $D->createElement('content');
			if (!empty($row['content'])) {
				$p->appendChild($e);
				$f = $D->createDocumentFragment();
				$f->appendXML($row['content']);
				$e->appendChild($f);
			}
			if (isset($ParentVars['external_class']) && $ParentVars['external_class']!='') {
				$r2 = mysql_query('SELECT * FROM `'.$ParentVars['external_class'].'` WHERE `id`="'
					.$row['id'].'" AND `version`="0000-00-00 00:00:00"');
				while ($row2 = mysql_fetch_assoc($r2)) 
					foreach ($GLOBALS['EXTERNAL_CLASS'][$ParentVars['external_class']]['property']
						as $property) {
						$e = $D->createElement($property['name']);
						if (!empty($row2[$property['name']])) {
							$f = $D->createDocumentFragment();
							$f->appendXML($row2[$property['name']]);
							$e->appendChild($f);
							$p->appendChild($e);
						}
					}
				mysql_free_result($r2);
			}
			self::exportXML1($D, $p, $Time, $row);
		}
		mysql_free_result($r);
	}
	
	public static function exportXML($DBFile) {
		$time = date('Y-m-d H:i:s');
		$d = new DOMDocument('1.0', 'UTF-8');
		$db = $d->createElement('db');
		$d->appendChild($db);
		$r = mysql_query('SELECT * FROM `'.TABLE_GROUP.'`');
		while ($row = mysql_fetch_assoc($r)) {
			$g = $d->createElement('group');
			$g->setAttribute('id', $row['id']);
			$g->setAttribute('name', $row['name']);
			$db->appendChild($g);
			$r2 = mysql_query('SELECT * FROM `'.TABLE_USER.'` WHERE `group_id`="'.$row['id'].'"');
			while ($row2 = mysql_fetch_assoc($r2)) {
				$u = $d->createElement('user');
				$u->setAttribute('id', $row2['id']);
				$u->setAttribute('group_id', $row2['group_id']);
				$u->setAttribute('name', $row2['name']);
				$u->setAttribute('password', $row2['password']);
				$u->setAttribute('expire', $row2['expire']);
				$u->setAttribute('email', $row2['email']);
				$g->appendChild($u);
			}
			mysql_free_result($r2);
		}
		mysql_free_result($r);
		$rs = $d->createElement('rights');
		$db->appendChild($rs);
		$r = mysql_query('SELECT * FROM `'.TABLE_RIGHTS.'`');
		while ($row = mysql_fetch_assoc($r)) {
			$e = $d->createElement('right');
			$e->setAttribute('id', $row['id']);
			$e->setAttribute('ug_id', $row['ug_id']);
			$e->setAttribute('is_group', $row['is_group']);
			$e->setAttribute('link', $row['link']);
			$e->setAttribute('allow', $row['allow']);
			$e->setAttribute('disallow', $row['disallow']);
			$rs->appendChild($e);
		}
		mysql_free_result($r);
		
		$root = $d->createElement('root');
		$db->appendChild($root);
		self::exportXML1($d, $root, $time);
		
		$xpath = new DOMXPath($d);
		$r = mysql_query('SELECT * FROM `'.TABLE_ASSOC.'`');
		while ($row = mysql_fetch_assoc($r)) {
			$a = $d->createElement('assoc');
			$a->setAttribute('id', $row['foreign_id']);
			$ps = $xpath->query('/db/root//page[@id="'.$row['id'].'"]');
			foreach ($ps as $p) $p->appendChild($a);
		}
		mysql_free_result($r);
		
		return $d->save(DIR_ROOT.'/'.$DBFile);
	}
	
	public static function importXML($DBHost, $DBUser, $DBPass, $DBName) {
		$time = date('Y-m-d H:i:s');

		mysql_connect($DBHost, $DBUser, $DBPass);
		mysql_select_db($DBName);
		mysql_query('SET NAMES "UTF8"');
		mysql_query('SET TIME_ZONE = "'.preg_replace('/(..)$/', ':$1', date('O')).'"');
		mysql_query('SET TIMESTAMP = '.time());

		self::install(null, null, true);

		foreach ($GLOBALS['EXTERNAL_CLASS'] as $className=>$class) {
			$fields = '';
			foreach ($class['property'] as $property) {
				$fields .= '`'.$property['name'].'` ';
				switch ($property['type']) {
					case 'char': $fields .= 'VARCHAR(255)'; break;
					case 'date': $fields .= 'TIMESTAMP'; break;
					case 'int': $fields .= 'INT'; break;
					case 'float': $fields .= 'FLOAT'; break;
					case 'mediumtext': $fields .= 'MEDIUMTEXT'; break;
					case 'text': $fields .= 'TEXT'; break;
                                }
				$fields .= ' NOT NULL,';
                        }
			mysql_query('DROP TABLE IF EXISTS `'.$className.'`');
			mysql_query('CREATE TABLE `'.$className.'` (
					`id` int(10) unsigned NOT NULL auto_increment,
					`version` timestamp NOT NULL default CURRENT_TIMESTAMP,
					'.$fields.'
					PRIMARY KEY  (`id`,`version`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8');
                }

		$d = new DOMDocument('1.0', 'UTF-8');
		$d->load(DIR_ROOT.'/'.DB_FILE);
		$xpath = new DOMXPath($d);

		foreach ($xpath->query('//group') as $g)
			mysql_query('INSERT INTO `'.TABLE_GROUP.'`(`id`,`name`) VALUES("'
				.$g->getAttribute('id').'","'
				.$g->getAttribute('name').'")');

		foreach ($xpath->query('//user') as $u) {
			$group_id = $u->getAttribute('group_id');
			if (empty($group_id))
				foreach ($xpath->query('..', $u) as $e) $group_id = $e->getAttribute('id');
			mysql_query('INSERT INTO `'.TABLE_USER
				.'`(`id`,`group_id`,`name`,`password`,`expire`,`email`) VALUES("'
				.$u->getAttribute('id').'","'
				.$group_id.'","'
				.$u->getAttribute('name').'","'
				.$u->getAttribute('password').'","'
				.$u->getAttribute('expire').'","'
				.$u->getAttribute('email').'")');
		}

		foreach ($xpath->query('//right') as $u)
			mysql_query('INSERT INTO `'.TABLE_RIGHTS
				.'`(`id`,`ug_id`,`is_group`,`link`,`allow`,`disallow`) VALUES("'
				.$u->getAttribute('id').'","'
				.$u->getAttribute('ug_id').'","'
				.$u->getAttribute('is_group').'","'
				.$u->getAttribute('link').'","'
				.$u->getAttribute('allow').'","'
				.$u->getAttribute('disallow').'")');

		$ids = array();
		$r = $xpath->query('//page');
		for ($i=0; $i<$r->length; $i++) $ids[$r->item($i)->getAttribute('id')] = $i;

		foreach ($xpath->query('//back[@version="0000-00-00 00:00:00"]') as $p) {
			$vars=array('head'=>'','title'=>'','keywords'=>'','description'=>'','image'=>'','content'=>'');
			$externals = array();
			$assocs = array();

			$id = $ids[$p->getAttribute('id')];

			foreach ($xpath->query('../..', $p) as $parent) {
				$parent_id = $ids[$parent->getAttribute('id')];
				$external_class = $parent->getAttribute('external_class');
                        }

			foreach ($xpath->query('*', $p) as $child)
				switch ($child->tagName) {
					case 'assoc':
					case 'page':
					case 'back':
						break;
					case 'head':
					case 'title':
					case 'keywords':
					case 'description':
					case 'image':
					case 'content':
						$vars[$child->tagName] = DOMElementInnerXML($child);
						break;
					default:
						$externals[$child->tagName] = DOMElementInnerXML($child);
						break;
				}
			mysql_query('INSERT INTO `'.TABLE_PAGE.'`(`id`,`version`,`create_time`,`publish_time`,`cash`,'
				.'`data_only`,`owner_id`,`parent_id`,`external_class`,`n`,`template_src`,'
				.'`childrens_template_src`,`link`,`use_content_in_head`,`head`,`title`,'
				.'`keywords`,`description`,`image`,`content`) VALUES("'
				.$id.'","'
				.$time.'","'
				.$p->getAttribute('create_time').'","'
				.$p->getAttribute('publish_time').'","'
				.$p->getAttribute('cash').'","'
				.$p->getAttribute('data_only').'","'
				.$p->getAttribute('owner_id').'",'
				.($parent_id ? '"'.$parent_id.'"' : 'NULL').',"'
				.$p->getAttribute('external_class').'","'
				.$p->getAttribute('n').'","'
				.$p->getAttribute('template_src').'","'
				.$p->getAttribute('childrens_template_src').'","'
				.$p->getAttribute('link').'","'
				.$p->getAttribute('use_content_in_head').'","'
				.addslashes($vars['head']).'","'
				.addslashes($vars['title']).'","'
				.addslashes($vars['keywords']).'","'
				.addslashes($vars['description']).'","'
				.addslashes($vars['image']).'","'
				.addslashes($vars['content']).'")');

			if ($external_class!='') {
				$fields = '`id`,`version`';
				$values = '"'.$id.'","'.$time.'"';
				foreach ($GLOBALS['EXTERNAL_CLASS'][$external_class]['property'] as $property) {
					if (!isset($externals[$property['name']])) $externals[$property['name']] = '';
					$fields .= ',`'.$property['name'].'`';
					$values .= ',"'.addslashes($externals[$property['name']]).'"';
				}
				mysql_query('INSERT INTO `'.$external_class.'`('.$fields.') VALUES('.$values.')');
			}
		}

		foreach ($xpath->query('//assoc') as $a) {
			foreach ($xpath->query('..', $a) as $e)
				$id = $ids[$e->getAttribute('id')];
			$foreign_id = $ids[$a->getAttribute('id')];
			mysql_query('INSERT INTO `'.TABLE_ASSOC.'`(`id`,`foreign_id`) VALUES("'
				.$id.'","'.$foreign_id.'")');
		}
	}

	public function authenticate($Name, $Password) {
		$r = $this->_query('SELECT `id` AS `user.id`, `group_id` AS `user.group_id`, `name` AS `user.name`,
`password` AS `user.password`, `expire` AS `user.expire`, `email` AS `user.email` FROM `'.TABLE_USER.'`
WHERE `name`="'.$Name.'" AND `password`="'.$Password.'" AND `expire`>NOW()');
		if (!($userVars = $this->getRow($r))) return null;
		$r = $this->_query('SELECT `id` FROM `'.TABLE_PAGE.'` AS A  WHERE `title`="'.$userVars['user.name']
			.'" AND `parent_id`="'.USER_PARENT_ID.'" AND '.$this->SQLCheckVersion('A'));
		if ($row = $this->getRow($r)) $userVars = $this->getVarsById($row['id'], true) + $userVars;
		$r = $this->_query('SELECT `id` AS `group.id`, `name` AS `group.name` FROM `'.TABLE_GROUP
			.'` WHERE `id`="'.$userVars['user.group_id'].'"');
		if ($groupVars = $this->getRow($r)) $userVars = $groupVars + $userVars;
		return $userVars;
        }

	public function getUserRights() {
		$r = $this->_query('SELECT `owner_id` FROM `'.TABLE_PAGE.'` AS A WHERE `link`="'.$_SERVER['REQUEST_URI']
			.'" AND '.$this->SQLCheckVersion('A'));
		$row = $this->getRow($r);
		$ownerId = $row['owner_id'];
		$r = $this->_query('SELECT `group_id` FROM `'.TABLE_USER.'` WHERE `id`="'.$ownerId.'"');
		$row = $this->getRow($r);
		$ownerGroupId = $row['group_id'];
		$r = $this->_query('SELECT `allow`, `disallow` FROM `'.TABLE_RIGHTS.'` WHERE (
			(	(	(`ug_id`="'.$this->USER->getVar('user.id').'"
					OR (`ug_id`="0" AND "'.$this->USER->getVar('user.id').'"="'.$ownerId.'")
					)
					AND `is_group`="false"
				)
				OR (	(`ug_id`="'.$this->USER->getVar('group.id').'"
					OR (`ug_id`="0" AND "'.$this->USER->getVar('group.id').'"="'.$ownerGroupId.'")
					)
					AND `is_group`="true"
				)
			)
			AND (`link`="'.$_SERVER['REQUEST_URI'].'" OR ISNULL(`link`) OR `link`="")
		)');
		$allow = 0;
		$disallow = 0;
		for ($i=0; $row = $this->getRow($r, $i); $i++) {
			$allow |= (int)$row['allow'];
			$disallow |= (int)$row['disallow'];
		}
		return $allow & ~$disallow;
        }

	public function getVars($Link, $RequiredVars = null, $useId = false) {
		if (is_null($RequiredVars)) {
			$fields = '`'.implode('`,`', $GLOBALS['CMS_REQUIRED_VARS']).'`';
			$parentFields = '';
			$externalFields = '';
        	} else if ($RequiredVars===true)  {
			$fields = '*';
			$parentFields = '*';
			$externalFields = '*';
		} else {
			if (count($RequiredVars['external'])>0) {
				if (!in_array('id', $RequiredVars['vars']))
					$RequiredVars['vars'][] = 'id';
				if (!in_array('version', $RequiredVars['vars']))
					$RequiredVars['vars'][] = 'version';
				if (!in_array('external_class', $RequiredVars['parent']))
					$RequiredVars['parent'][] = 'external_class';
				$externalFields = '`'.implode('`,`', $RequiredVars['external']).'`';
			} else $externalFields = '';
			if (count($RequiredVars['parent'])>0) {
				if (!in_array('parent_id', $RequiredVars['vars']))
					$RequiredVars['vars'][] = 'parent_id';
				if (in_array('id', $RequiredVars['parent'])) unset($RequiredVars['parent']['id']);
				$parentFields = '`'.implode('`,`', $RequiredVars['parent']).'`';
			} else $parentFields = '';
			$fields = '`'.implode('`,`', $RequiredVars['vars']).'`';//var_dump($fields);
		}
		$r = $this->getRow($this->query('SELECT '.$fields.' FROM `'.TABLE_PAGE.'` AS A WHERE `'.
				($useId ? 'id' : 'link').'`="'.$Link.'" AND '.$this->SQLCheckVersion('A')));
		if ($parentFields!='') {
			$parent_r = $this->getRow($this->query('SELECT '.$parentFields.' FROM `'.TABLE_PAGE
				.'` AS A WHERE `id`="'.$r['parent_id'].'" AND '.$this->SQLCheckVersion('A')));
			if ($parent_r) foreach ($parent_r as $i=>$v) $r['parent.'.$i] = $v;
		}
		if ($externalFields!='' && isset($r['parent.external_class']) && $r['parent.external_class']!='') {
			$external_r = $this->getRow($this->query('SELECT '.$externalFields.' FROM `'
				.$r['parent.external_class'].'` AS A WHERE `id`="'.$r['id'].'" AND `version`="'
				.$r['version'].'"'));
			if ($external_r) foreach ($external_r as $i=>$v) $r['external.'.$i] = $v;
		}
		if (isset($r['parent_id'])) $r['parent.id'] = $r['parent_id'];
		return $r;
        }

	public function getVarsById($Id, $RequiredVars = null) {
		return $this->getVars($Id, $RequiredVars, true);
        }
	
//	public function getRow($Result, $I = 0, $F = 0) {
//		$I -= $F;
//		if ($Result && mysql_num_rows($Result)>$I) {
//			mysql_data_seek($Result, $I);
//			return mysql_fetch_assoc($Result);
//		} else return false;
//        }

	public function getRow($Result, $I = 0, $F = 0) {
		$I -= $F;
		if ($Result && count($Result)>$I) {
			return $Result[$I];
		} else return false;
	}
	
	private function checkOrderby($Orderby, $Length) {
		if ($Orderby=='?')
			return '';
		if ($Orderby=='')
		{
			if ($Length==1)
				return '';
			else
				return ' ORDER BY `n` DESC, `create_time` DESC';
		}
		return ' ORDER BY '.$Orderby;
	}
	
	public function getMaxN($ParentId) {
		$r = $this->query('SELECT `n` FROM `'.TABLE_PAGE.'` AS A WHERE '
			.($ParentId=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$ParentId.'"').' AND '
			.$this->SQLCheckVersion("A").' ORDER BY `n` DESC, `create_time` DESC LIMIT 1');
		if ($vars = $this->getRow($r)) return $vars['n'];
		else return 0;
	}

	public function checkLink($Link, $Id) {
		$r = $this->query('SELECT `id` FROM `'.TABLE_PAGE.'` AS A WHERE '
			.(!is_null($Id) ? '`id`!="'.$Id.'" AND ' : '').'`link`="'.$Link.'" AND '
			.$this->SQLCheckVersion('A'));
		//if (mysql_num_rows($r)==0) return true;////////////////////////////////////////////////////////////////
		if ($this->num_rows($r)==0) return true;
		else return false;
	}

	public function checkUserName($UserId, $UserName) {
		$r = $this->query('SELECT `id` FROM `'.TABLE_USER.'` AS A WHERE '
			.($UserId!='' ? '`id`!="'.$UserId.'" AND' : '')
			.' `name`="'.$UserName.'"');
		//if (mysql_num_rows($r)==0) return true;////////////////////////////////////////////////////////////////
		if ($this->num_rows($r)==0) return true;
		else return false;
	}

	public function checkId($Id) {
		$r = $this->query('SELECT `id` FROM `'.TABLE_PAGE.'` WHERE `id`="'.$Id.'" LIMIT 1');
		//return mysql_num_rows($r)==0;////////////////////////////////////////////////////////////////
		return $this->num_rows($r)==0;
	}

	public function getParentTemplateSrc(&$Vars) {
		if (!isset($Vars['id'])) return array('', 0);
		$vars = array('parent_id'=>$Vars['parent_id']);
		for ($k=1; $vars = $this->getRow($this->query('SELECT `parent_id`, `childrens_template_src` FROM `'
			.TABLE_PAGE.'` AS A WHERE `id`="'.$vars['parent_id'].'" AND '
			.$this->SQLCheckVersion('A'))); $k++)
			if ($vars['childrens_template_src']!='') return array($vars['childrens_template_src'], $k);
		return array(isset($Vars['childrens_template_src']) ? $Vars['childrens_template_src'] : '', 0);
	}

	public function getLastPublishTime() {
		$r = $this->getRow($this->query('SELECT MAX(`publish_time`) as "max_publish_time" FROM `CmsPage`'));
		return $r['max_publish_time'];
        }

	private function prepareVarNames(&$RequiredVars, $WithoutExternal = false, $Prefix = 'A') {
		$r = count($RequiredVars['external'])>0;
		foreach ($RequiredVars['vars'] as $i=>$v) $RequiredVars['vars'][$i] = $Prefix.'.`'.$v.'`';
		if (!$WithoutExternal) {
			foreach ($RequiredVars['external'] as $i=>$v)
				$RequiredVars['external'][$i] = 'B.`'.$v.'` AS \'external.'.$v.'\'';
			$RequiredVars = array_merge($RequiredVars['vars'], $RequiredVars['external']);
		} else $RequiredVars = $RequiredVars['vars'];
		return $r;
        }
	
	private function splitVars($Vars) {
		$vars = array();
		$parentVars = array();
		$externalVars = array();
		foreach ($Vars as $i=>$v)
			if (substr($i, 0, 7)=='parent.') $parentVars[substr($i, 7)] = $v;
			else if (substr($i, 0, 9)=='external.') $externalVars[substr($i, 9)] = $v;
			else $vars[$i] = $v;
		return array($vars, $parentVars, $externalVars);
        }
	
	public function deleteOldVersions($Table, $Id, $VersionsNum = DB_VERSIONS_NUM) {
		$r = $this->_query('SELECT `version` FROM `'.$Table.'` WHERE `id`="'.$Id
			.'" AND `version`!="0000-00-00 00:00:00" ORDER BY `version` DESC LIMIT '.$VersionsNum.', 1');
		if ($row = $this->getRow($r)) return $this->_query('DELETE FROM `'.$Table.'` WHERE `id`="'.$Id
			.'" AND `version`!="0000-00-00 00:00:00" AND `version`<="'.$row['version'].'"');
		else return true;
	}

	private function findIdForSetNewPublishTime(array $Ids) {
		$result = array();

		$r = mysql_unbuffered_query('SELECT `parent_id` FROM `'.TABLE_PAGE.'` AS A WHERE `id` IN ("'
			.implode('","', $Ids).'") AND version="0000-00-00 00:00:00"'
			.'UNION SELECT `foreign_id` FROM `'.TABLE_ASSOC
			.'` WHERE `id` IN ("'.implode('","', $Ids).'")'
			.'UNION SELECT `id` FROM `'.TABLE_ASSOC
			.'` WHERE `foreign_id` IN ("'.implode('","', $Ids).'")');

		while ($row = mysql_fetch_row($r)) {
			if (!in_array($row[0], $GLOBALS['SPANPT'])) {
				$GLOBALS['SPANPT'][] = $row[0];
				$result[] = $row[0];
			}
		}
		mysql_free_result($r);
		if (!empty($result)) $result = array_merge($result, $this->findIdForSetNewPublishTime($result));
		return $result;
	}

	private function setNewPublishTime($Id, $First)
	{
		if (empty($GLOBALS['STRIP_SET_NEW_PUBLISH_TIME']))
		{
			if (!isset($GLOBALS['SPANPT'])) $GLOBALS['SPANPT'] = array();
			$GLOBALS['SPANPT'][] = $Id;
			$ids = $this->findIdForSetNewPublishTime(array($Id));
		}
		else
			$ids = array();

		if ($First)
			$ids[] = $Id;

		if (!empty($ids))
			mysql_query('UPDATE `'.TABLE_PAGE.'` SET `publish_time`=NOW() WHERE `id` IN ("'
					.implode('","', $ids).'") AND `publish_time`>=`version` AND `publish_time`<NOW()');
	}





	/*
         * Queries
         */
	
	public function getSiblings(&$Vars, &$RequiredVars, $Orderby, $Length, $Page, $ReturnCount = false) {
		$hasExternals = $this->prepareVarNames($RequiredVars);
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		$parentId = !isset($Vars['parent_id']) || is_null($Vars['parent_id'])
			? 'ISNULL(A.`parent_id`)' : 'A.`parent_id`="'.$Vars['parent_id'].'"';
		$query = ' FROM `'.TABLE_PAGE.'` AS A'
			.($hasExternals ? ' LEFT JOIN `'.$Vars['external_class']
				.'` AS B ON (A.id=B.id AND A.version=B.version)' : '')
			.' WHERE '.$parentId.' AND '.$this->SQLCheckVersion('A');
		if ($Length!='' || $ReturnCount || $Orderby=='?') {
			$r = $this->query('SELECT COUNT(*) AS "count"'.$query);
			$row = $this->getRow($r);
			$c = $row['count'];
			if ($ReturnCount) return $c;
			if ($Orderby=='?')
				$f = mt_rand(0, $c-$Length);
			else
				$f = ($Page-1)*$Length;
			$limit = ' LIMIT '.$f.', '.$Length;
		} else $limit = '';
		$r = $this->query('SELECT '.implode(',', $RequiredVars)
			.$query.$this->checkOrderby($Orderby, $Length).$limit);
		$num = $r ? $this->num_rows($r) : 0;
		if (!isset($f)) $f = 0;
		if (!isset($c)) $c = $num;
		$l = $num < $Length || $Length=='' ? $f+$num-1 : $f+$Length-1;
		return array($r, $f, $l, $c);
	}
	
	public function getChilds(&$Vars, &$RequiredVars, $Orderby, $Length, $Page, $Deep=null, $ReturnCount=false) {
		if (!$Deep) $Deep = '1;'.$Vars['external_class'];
		@list($Deep, $external_class) = explode(';', $Deep);

		$hasExternals = $this->prepareVarNames($RequiredVars, false, 't1');
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		$where = !isset($Vars['id']) || is_null($Vars['id'])
			? 'ISNULL(t'.$Deep.'.`parent_id`)' : 't'.$Deep.'.`parent_id`="'.$Vars['id'].'"';
		$query = ' FROM `'.TABLE_PAGE.'` AS t1'
			.($hasExternals ? ' LEFT JOIN `'.$external_class
			.'` AS B ON (t1.id=B.id AND t1.version=B.version)' : '');
		$where .= ' AND '.$this->SQLCheckVersion('t1');

		for ($i=2; $i<=$Deep; $i++) {
			$query .= ' JOIN `'.TABLE_PAGE.'` AS t'.$i.' ON (t'.$i.'.`id`=t'.($i-1).'.`parent_id`)';
			$where .= ' AND '.$this->SQLCheckVersion('t'.$i);
		}

		$query .= ' WHERE '.$where;
		if ($Length!='' || $ReturnCount || $Orderby=='?') {
			$r = $this->query('SELECT COUNT(*) AS "count"'.$query);
			$row = $this->getRow($r);
			$c = $row['count'];
			if ($ReturnCount) return $c;
			if ($Orderby=='?')
				$f = mt_rand(0, $c-$Length);
			else
				$f = ($Page-1)*$Length;
			$limit = ' LIMIT '.$f.', '.$Length;
		} else $limit = '';
		$r = $this->query('SELECT '.implode(',', $RequiredVars)
			.$query.$this->checkOrderby($Orderby, $Length).$limit);
		$num = $r ? $this->num_rows($r) : 0;
		if (!isset($f)) $f = 0;
		if (!isset($c)) $c = $num;
		$l = $num < $Length || $Length=='' ? $f+$num-1 : $f+$Length-1;
		return array($r, $f, $l, $c);
	}
	
	public function getParent(&$Vars, &$RequiredVars, $Deep = 1) {
		if (!isset($Vars['parent_id']) || $Vars['parent_id']=='') return false;
		$hasExternals = $this->prepareVarNames($RequiredVars, true);
		if ($Deep==1 || $Deep=='') {
			$r = $this->query('SELECT '.implode(',', $RequiredVars).' FROM `'.TABLE_PAGE.'` AS A'
				//.($hasExternals ? ' LEFT JOIN `'.$Vars['external_class']
				//	.'` AS B ON (A.id=B.id AND A.version=B.version)' : '')
				.' WHERE `id`="'.$Vars['parent_id'].'"'
				.' AND '.$this->SQLCheckVersion('A'));
			return array($r, 0, 0, $this->num_rows($r));
		} else {
			$query = 'SELECT t'.$Deep.'.`id` FROM `'.TABLE_PAGE.'` AS t1';
			$where = ' WHERE t1.`id`="'.$Vars['parent_id'].'" AND '.$this->SQLCheckVersion('t'.$Deep);
			for ($i=2; $i<=$Deep; $i++) {
				$query .= ', `'.TABLE_PAGE.'` AS t'.$i;
				$where .= ' AND t'.$i.'.`id`=t'.($i-1).'.`parent_id` AND '
					.$this->SQLCheckVersion('t'.$i);
			}
			$r = $this->query($query.$where);
			if ($row = $this->getRow($r)) {
				$r = $this->query('SELECT '.implode(',', $RequiredVars).' FROM `'.TABLE_PAGE.'` AS A'
					.($hasExternals ? ' LEFT JOIN `'.$Vars['external_class']
						.'` AS B ON (A.id=B.id AND A.version=B.version)' : '')
					.' WHERE `id`="'.$row['id'].'" AND '.$this->SQLCheckVersion('A'));
				return array($r, 0, 0, $this->num_rows($r));
			}
			else return array(null, 0, 0, 0);
		}
	}
	
	public function getAssoc(&$Vars, &$RequiredVars, $Orderby, $Length, $Page) {
		$hasExternals = $this->prepareVarNames($RequiredVars);
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		if (!isset($Vars['assoc.id'])) {
			if (!isset($Vars['parent_id'])) return array(false, 0, 0, 0);
			$queryPre = 'SELECT '.implode(',', $RequiredVars).($Orderby=='`?`' ? ', RAND() AS `?`' : '')
				.', "'.$Vars['id'].'" AS "assoc.id", "'.$Vars['parent_id'].'" AS "assoc.parent_id"';
			$query = ' FROM `'.TABLE_PAGE.'` AS A WHERE EXISTS (SELECT `id` FROM `'.TABLE_ASSOC
				.'` WHERE `id`="'.$Vars['parent_id'].'" AND `foreign_id`=`A`.`id`) AND '
				.$this->SQLCheckVersion('A');
		} else {
			$queryPre = 'SELECT '.implode(',', $RequiredVars).($Orderby=='`?`' ? ', RAND() AS `?`' : '');
			$query = ' FROM `'.TABLE_PAGE.'` AS A WHERE EXISTS (SELECT `id` FROM `'.TABLE_ASSOC
				.'` WHERE `id`="'.$Vars['assoc.id'].'" AND `foreign_id`=`A`.`id`) AND `parent_id`="'
				.$Vars['id'].'" AND '.$this->SQLCheckVersion('A');
		}
		if ($Length!='' || $Orderby=='?') {
			$r = $this->query('SELECT COUNT(*) AS "count"'.$query);
			$row = $this->getRow($r);
			$c = $row['count'];
			if ($Orderby=='?')
				$f = mt_rand(0, $c-$Length);
			else
				$f = ($Page-1)*$Length;
			$limit = ' LIMIT '.$f.', '.$Length;
		} else $limit = '';
		$r = $this->query($queryPre.$query.$this->checkOrderby($Orderby, $Length).$limit);
		$num = $r ? $this->num_rows($r) : 0;
		if (!isset($f)) $f = 0;
		if (!isset($c)) $c = $num;
		$l = $num < $Length || $Length=='' ? $f+$num-1 : $f+$Length-1;
		return array($r, $f, $l, $c);
	}

	public function getRAssoc(&$Vars, &$RequiredVars, $ForeignId, $Orderby, $Length, $Page) {
		$hasExternals = $this->prepareVarNames($RequiredVars);
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		$queryPre = 'SELECT '.implode(',', $RequiredVars).($Orderby=='`?`' ? ', RAND() AS `?`' : '').', "'
			.$ForeignId.'" AS "assoc.foreign_id"';
		$query = ' FROM `'.TABLE_PAGE.'` AS A '
			.($hasExternals ? ' LEFT JOIN `'.$Vars['external_class']
				.'` AS B ON (A.id=B.id AND A.version=B.version)' : '')
			.' WHERE EXISTS (SELECT `id` FROM `'.TABLE_ASSOC
			.'` WHERE `id`=`A`.`id` AND `foreign_id`="'.$ForeignId.'")'
			.(!is_null($Vars) ? ' AND `parent_id`="'.$Vars['id'].'"' : '').' AND '
			.$this->SQLCheckVersion('A');
		if ($Length!='' || $Orderby=='?') {
			$r = $this->query('SELECT COUNT(*) AS "count"'.$query);
			$row = $this->getRow($r);
			$c = $row['count'];
			if ($Orderby=='?')
				$f = mt_rand(0, $c-$Length);
			else
				$f = ($Page-1)*$Length;
			$limit = ' LIMIT '.$f.', '.$Length;
		} else $limit = '';
		$r = $this->query($queryPre.$query.$this->checkOrderby($Orderby, $Length).$limit);
		$num = $r ? $this->num_rows($r) : 0;
		if (!isset($f)) $f = 0;
		if (!isset($c)) $c = $num;
		$l = $num < $Length || $Length=='' ? $f+$num-1 : $f+$Length-1;
		return array($r, $f, $l, $c);
	}

	public function getDAssoc(&$Vars, &$RequiredVars, $Id, $Orderby, $Length, $Page) {
		$hasExternals = $this->prepareVarNames($RequiredVars);
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		$queryPre = 'SELECT '.implode(',', $RequiredVars).($Orderby=='`?`' ? ', RAND() AS `?`' : '').', "'
			.$Id.'" AS "assoc.id"';
		$query = ' FROM `'.TABLE_PAGE.'` AS A '
			.($hasExternals ? ' LEFT JOIN `'.$Vars['external_class']
				.'` AS B ON (A.id=B.id AND A.version=B.version)' : '')
			.' WHERE EXISTS (SELECT `id` FROM `'.TABLE_ASSOC.'` WHERE `id`="'
			.$Id.'" AND `foreign_id`=`A`.`id`)'.
			(!is_null($Vars) ? ' AND `parent_id`="'.$Vars['id'].'"' : '').' AND '
			.$this->SQLCheckVersion('A');
		if ($Length!='' || $Orderby=='?') {
			$r = $this->query('SELECT COUNT(*) AS "count"'.$query);
			$row = $this->getRow($r);
			$c = $row['count'];
			if ($Orderby=='?')
				$f = mt_rand(0, $c-$Length);
			else
				$f = ($Page-1)*$Length;
			$limit = ' LIMIT '.$f.', '.$Length;
		} else $limit = '';
		$r = $this->query($queryPre.$query.$this->checkOrderby($Orderby, $Length).$limit);
		$num = $r ? $this->num_rows($r) : 0;
		if (!isset($f)) $f = 0;
		if (!isset($c)) $c = $num;
		$l = $num < $Length || $Length=='' ? $f+$num-1 : $f+$Length-1;
		return array($r, $f, $l, $c);
	}
	
	public function select($Sql, $XPath, $Orderby, $Length, $Page, $Element) {
		if ($Page=='') $Page = $GLOBALS['REQUEST_PAGE'];
		if ($Element->hasAttribute('checkversion'))
			$Sql .= ' AND '.$this->SQLCheckVersion($Element->getAttribute('checkversion'));
		preg_match('/\sFROM\s.*$/', $Sql, $matches);
		$q = $matches[0];
		if ($Length!='' || $Orderby=='?') {
			$r = $this->query('SELECT'
				.(preg_match('/^SELECT\s+STRAIGHT_JOIN/', $Sql) ? ' STRAIGHT_JOIN' : '').' COUNT(*) AS "count" '.$q);
			$row = $this->getRow($r);
			$c = $row['count'];
			if ($Orderby=='?')
				$f = mt_rand(0, $c-$Length);
			else
				$f = ($Page-1)*$Length;
			$limit = ' LIMIT '.$f.', '.$Length;
		} else $limit = '';
		$r = $this->query($Sql.$this->checkOrderby($Orderby, $Length).$limit);
		$num = $r ? $this->num_rows($r) : 0;
		if (!isset($f)) $f = 0;
		if (!isset($c)) $c = $num;
		$l = $num < $Length || $Length=='' ? $f+$num-1 : $f+$Length-1;
		return array($r, $f, $l, $c);
	}
	
	public function rawQuery($Sql, $XPath, $Orderby) {
		$c = 0;
		if ($r = $this->query($Sql.($Orderby!='' ? ' ORDER BY '.$Orderby : '')))
			$c = $this->num_rows($r);
		return array($r, 0, $c-1, $c);
	}

	public function search(&$RequiredVars, $Query, $Length, $Page, $Element) {
		$hasExternals = $this->prepareVarNames($RequiredVars);
		return $this->select('SELECT '.implode(',', $RequiredVars)
			.', MATCH(`title`, `keywords`, `description`, `content`) AGAINST("'.$Query
			.'") AS `score` FROM `'.TABLE_PAGE.'` AS A'
			.($hasExternals ? ' LEFT JOIN `'.$Vars['external_class']
				.'` AS B ON (A.id=B.id AND A.version=B.version)' : '')
			.' WHERE MATCH(`title`, `keywords`, `description`, `content`) AGAINST("'.$Query
			.'") AND '.$this->SQLCheckVersion('A'), null, '`score` DESC', $Length, $Page, $Element);
	}

	public function getUsers($Id, $GroupId, $Orderby, $Length, $Page, $Element) {
		$Vars = $this->getVarsById(USER_PARENT_ID, array('vars'=>array('external_class'), 
			'parent'=>array(), 'external'=>array()));
		return $this->select('SELECT '.($Vars['external_class']!='' ? 'B.*, ' : '').'A.*'
			.', C.`id` AS `user.id`, C.`group_id` AS `user.group_id`,C.`name` AS
`user.name`, C.`password` AS `user.password`, C.`expire` AS `user.expire`, C.`email` AS `user.email` FROM `'
			.TABLE_USER.'` AS C, `'.TABLE_PAGE.'` AS A'
			.($Vars['external_class']!='' ? ' LEFT JOIN `'.$Vars['external_class']
				.'` AS B ON (A.id=B.id AND A.version=B.version)' : '')
			.' WHERE A.`title`=C.`name` AND A.`parent_id`="'
			.USER_PARENT_ID.'"'.($Id!='' ? ' AND C.id="'.$Id.'"' : '')
			.($GroupId!='' ? ' AND C.group_id="'.$GroupId.'"' : '')
			.' AND '.$this->SQLCheckVersion('A'),
			null, $Orderby, $Length, $Page, $Element);
	}



	/*
         * Actions
         */

	public function save(&$A) {
		if (!isset($A['id']) || $A['id']=='') {
			$A['vars']['parent_id'] = $A['parent_id'];
			$oldVars = array();
			$parentVars = $this->getVarsById($A['parent_id']);
			if ($parentVars) foreach ($parentVars as $i=>$v) $oldVars['parent.'.$i] = $v;
                } else $oldVars = $this->getVarsById($A['id'], true);
		if (!$oldVars) $oldVars = array();
		$newVars = array_merge($oldVars, $A['vars']);
		if ($oldVars==$newVars) return true;
		
		$version = date('Y-m-d H:i:s', time());
		$newVars['version'] = $version;
		
		foreach ($newVars as $key=>$var)
			if ($var=='') unset($newVars[$key]);
			else if (array_key_exists($key, $A['vars']))
				$newVars[$key] = str_replace('&', '&amp;', addslashes($var));
                        else $newVars[$key] = addslashes($var);
		
		if (isset($A['id'])) $this->deleteOldVersions(TABLE_PAGE, $A['id'], DB_VERSIONS_NUM-1);
		
		list($newVars, $parentVars, $newExternalVars) = $this->splitVars($newVars);

		$this->_query('REPLACE INTO `'.TABLE_PAGE.'`(`'.implode('`,`', array_keys($newVars)).'`) VALUES("'
			.implode('","', $newVars).'")');
		
		if (!isset($A['id']) || $A['id']=='') {
			$A['id'] = mysql_insert_id();
			$this->setNewPublishTime($A['id'], false);
		}
		
		if (isset($parentVars['external_class']) && $parentVars['external_class']!='') {
			$newExternalVars['id'] = $A['id'];
			$newExternalVars['version'] = $version;
			$this->deleteOldVersions($parentVars['external_class'], $A['id'], DB_VERSIONS_NUM-1);
			$this->_query('REPLACE INTO `'.$parentVars['external_class'].'`(`'
				.implode('`,`', array_keys($newExternalVars)).'`) VALUES("'
				.implode('","', $newExternalVars).'")');
		}
		
		return true;
        }

	public function publish(&$A) {
		$pubVars = $this->getVarsById($A['id'], true);
		$version = $pubVars['version'];
		$publish_time = max($pubVars['publish_time'], date('Y-m-d H:i:s'), $version);
		
		foreach ($pubVars as $key=>$var) $pubVars[$key] = addslashes($var);
		
		list($pubVars, $parentVars, $pubExternalVars) = $this->splitVars($pubVars);
		
		$pubVars['version'] = '0000-00-00 00:00:00';
		$pubVars['publish_time'] = $publish_time;
		$pubExternalVars['version'] = '0000-00-00 00:00:00';
		
		foreach ($pubVars as $key=>$var) if ($var=='') unset($pubVars[$key]);
		
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `publish_time`="'.$publish_time.'" WHERE `id`="'.$A['id']
			.'" AND `version`="'.$version.'"');
		$this->_query('REPLACE INTO `'.TABLE_PAGE.'`(`'.implode('`,`', array_keys($pubVars)).'`) VALUES("'
			.implode('","', $pubVars).'")');

		if (isset($parentVars['external_class']) && $parentVars['external_class']!='')
			$this->_query('REPLACE INTO `'.$parentVars['external_class'].'`(`'
				.implode('`,`', array_keys($pubExternalVars)).'`) VALUES("'
				.implode('","', $pubExternalVars).'")');
			
		$this->setNewPublishTime($A['id'], false);

		return true;
        }

	public function hide(&$A) {
		$this->setNewPublishTime($A['id'], false);
		$hideVars = $this->getVarsById($A['id'], array(
			'vars'=>array('id', 'version'), 
			'parent'=>array('external_class'), 
			'external'=>array()));
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `publish_time`="0000-00-00 00:00:00" WHERE `id`="'.$A['id']
			.'" AND `version`="'.$hideVars['version'].'"');
		$this->_query('DELETE FROM `'.TABLE_PAGE.'` WHERE `id`="'.$A['id']
			.'" AND `version`="0000-00-00 00:00:00"');
		if (isset($hideVars['parent.external_class']) && $hideVars['parent.external_class']!='')
			$this->_query('DELETE FROM `'.$hideVars['parent.external_class'].'` WHERE `id`="'.$A['id']
				.'" AND `version`="0000-00-00 00:00:00"');
		return true;
	}
	
	public function delete(&$A) {
		$this->setNewPublishTime($A['id'], false);
		$delVars = $this->getVarsById($A['id'], array(
			'vars'=>array('id', 'version'), 
			'parent'=>array('external_class', 'link'), 
			'external'=>array()));
		$this->_query('DELETE FROM `'.TABLE_PAGE.'` WHERE `id`="'.$A['id'].'"');
		if (isset($delVars['parent.external_class']) && $delVars['parent.external_class']!='')
			$this->_query('DELETE FROM `'.$delVars['parent.external_class'].'` WHERE `id`="'.$A['id'].'"');
		$this->_query('DELETE FROM `'.TABLE_ASSOC.'` WHERE `id`="'.$A['id'].'" OR `foreign_id`="'.$A['id'].'"');
		return isset($delVars['parent.link']) ? $delVars['parent.link'] : '/';
	}
	
	public function addAssoc(&$A) {
		if (empty($A['id']) || empty($A['foreign_id'])) return false;
		$this->_query('INSERT INTO `'.TABLE_ASSOC.'` VALUES("'.$A['id'].'", "'.$A['foreign_id'].'")');
		$this->setNewPublishTime($A['id'], true);
		return true;
	}

	public function removeAssoc(&$A) {
		$this->_query('DELETE FROM `'.TABLE_ASSOC.'` WHERE `id`="'.$A['id']
			.'" AND `foreign_id`="'.$A['foreign_id'].'"');
		$this->setNewPublishTime($A['id'], true);
		return true;
	}

	public function backup(&$A) {
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `version`=NOW() WHERE `id`="'.$A['id']
			.'" AND `version`="'.$A['version'].'"');
		$backupVars = $this->getVarsById($A['id'], array(
			'vars'=>array('link'), 'parent'=>array(), 'external'=>array()));
		return $backupVars['link'];
	}

	public function moveTo(&$A) {
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('parent_id', 'n', 'create_time'), 
			'parent'=>array(), 
			'external'=>array()));
		$varsTo = $this->getVarsById($A['moveto_id'], array(
			'vars'=>array('parent_id', 'n', 'create_time'),
			'parent'=>array(), 
			'external'=>array()));
		
		$up = $varsTo['n']>$vars['n'] 
			|| ($varsTo['n']==$vars['n'] && $varsTo['create_time']>$vars['create_time'])
			? 'true' : 'false';
		$parentCond = $varsTo['parent_id']=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$varsTo['parent_id'].'"';
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `n`=`n`+1 WHERE (`n`>"'.$varsTo['n'].'" OR (`n`="'
			.$varsTo['n'].'" AND `create_time`>"'.$varsTo['create_time'].'") OR (`id`="'.$A['moveto_id']
			.'" AND "'.$up.'"="false")) AND '.$parentCond);
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `n`=`n`-1 WHERE (`n`<"'.$varsTo['n'].'" OR (`n`="'
			.$varsTo['n'].'" AND `create_time`<"'.$varsTo['create_time'].'") OR (`id`="'.$A['moveto_id']
			.'" AND "'.$up.'"="true")) AND '.$parentCond);
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `n`="'.$varsTo['n'].'", parent_id='
			.($varsTo['parent_id']=='' ? 'NULL' : '"'.$varsTo['parent_id'].'"').' WHERE `id`="'.$A['id'].'"');
		$this->setNewPublishTime($A['id'], true);
		return true;
	}

	public function moveUp(&$A) {
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('parent_id', 'n', 'create_time'), 
			'parent'=>array(), 
			'external'=>array()));
		$r = $this->_query('SELECT `id` FROM `'.TABLE_PAGE.'` AS A WHERE (`n`>"'.$vars['n'].'" OR (`n`="'
			.$vars['n'].'" AND `create_time`>"'.$vars['create_time'].'")) AND '
			.($vars['parent_id']=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$vars['parent_id'].'"')
			.' AND '.$this->SQLCheckVersion('A').' ORDER BY `n` ASC, `create_time` ASC LIMIT 1');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			return $this->moveTo($A);
                } else return false;
	}

	public function moveDown(&$A) {
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('parent_id', 'n', 'create_time'), 
			'parent'=>array(), 
			'external'=>array()));
		$r = $this->_query('SELECT `id` FROM `'.TABLE_PAGE.'` AS A WHERE (`n`<"'.$vars['n'].'" OR (`n`="'
			.$vars['n'].'" AND `create_time`<"'.$vars['create_time'].'")) AND '
			.($vars['parent_id']=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$vars['parent_id'].'"')
			.' AND '.$this->SQLCheckVersion('A').' ORDER BY `n` DESC, `create_time` DESC LIMIT 1');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			return $this->moveTo($A);
                } else return false;
	}

	public function moveToFirst(&$A) {
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('parent_id'), 
			'parent'=>array(), 
			'external'=>array()));
		/*$r = $this->_query('SELECT `id` FROM `'.TABLE_PAGE.'` AS A WHERE '
			.($vars['parent_id']=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$vars['parent_id'].'"')
			.' AND '.$this->SQLCheckVersion('A').' ORDER BY `n` DESC, `create_time` DESC LIMIT 1');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			return $this->moveTo($A);
                } else return false;*/
		$r = $this->_query('SELECT MAX(`n`) AS "maxN" FROM `'.TABLE_PAGE.'` AS A WHERE '
			.($vars['parent_id']=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$vars['parent_id'].'"')
			.' AND '.$this->SQLCheckVersion('A'));
		$vars = $this->getRow($r);
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `n`="'.($vars['maxN']+1).'" WHERE `id`="'.$A['id'].'"');
		$this->setNewPublishTime($A['id'], true);
		return true;
	}
	
	public function moveToLast(&$A) {
		$vars = $this->getVarsById($A['id'], array(
			'vars'=>array('parent_id'), 
			'parent'=>array(), 
			'external'=>array()));
		/*$r = $this->_query('SELECT `id` FROM `'.TABLE_PAGE.'` AS A WHERE '
			.($vars['parent_id']=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$vars['parent_id'].'"')
			.' AND '.$this->SQLCheckVersion('A').' ORDER BY `n` ASC, `create_time` ASC LIMIT 1');
		if ($vars = $this->getRow($r)) {
			$A['moveto_id'] = $vars['id'];
			return $this->moveTo($A);
                } else return false;*/
		$r = $this->_query('SELECT MIN(`n`) AS "maxN" FROM `'.TABLE_PAGE.'` AS A WHERE '
			.($vars['parent_id']=='' ? 'ISNULL(`parent_id`)' : '`parent_id`="'.$vars['parent_id'].'"')
			.' AND '.$this->SQLCheckVersion('A'));
		$vars = $this->getRow($r);
		$this->_query('UPDATE `'.TABLE_PAGE.'` SET `n`="'.($vars['maxN']-1).'" WHERE `id`="'.$A['id'].'"');
		$this->setNewPublishTime($A['id'], true);
		return true;
	}
	
	public function userSave(&$A) {
		if (isset($A['id']) && $A['id']!='') {
			$query = '';
			if (isset($A['group_id']) && $A['group_id']!='')
				$query .= ($query!='' ? ',' : '').' `group_id`="'.$A['group_id'].'"';
			if (isset($A['name']) && $A['name']!='')
				$query .= ($query!='' ? ',' : '').' `name`="'.$A['name'].'"';
			if (isset($A['password']) && $A['password']!='')
				$query .= ($query!='' ? ',' : '').' `password`="'.md5($A['password']).'"';
			if (isset($A['expire']) && $A['expire']!='')
				$query .= ($query!='' ? ',' : '').' `expire`="'.$A['expire'].'"';
			else if (isset($A['expire']))
				$query .= ($query!='' ? ',' : '').' `expire`=NOW()';
			else
				$query .= ($query!='' ? ',' : '').' `expire`=`expire`';
			if (isset($A['email']))
				$query .= ($query!='' ? ',' : '').' `email`="'.$A['email'].'"';
			$query = 'UPDATE `'.TABLE_USER.'` SET'.$query.' WHERE `id`="'.$A['id'].'"';
		} else {
			$value_names = '`id`';
			$values = (isset($A['id']) && $A['id']!='') ? '"'.$A['id'].'"' : 'NULL';
			if (isset($A['group_id']) && $A['group_id']!='') {
				$value_names .= ',`group_id`';
				$values .= ',"'.$A['group_id'].'"';
			}
			if (isset($A['name']) && $A['name']!='') {
				$value_names .= ',`name`';
				$values .= ',"'.$A['name'].'"';
			}
			if (isset($A['password']) && $A['password']!='') {
				$value_names .= ',`password`';
				$values .= ',"'.md5($A['password']).'"';
			}
			if (isset($A['expire']) && $A['expire']!='') {
				$value_names .= ',`expire`';
				$values .= ',"'.$A['expire'].'"';
			} else {
				$value_names .= ',`expire`';
				$values .= ',NOW()';
			}
			if (isset($A['email']) && $A['email']!='') {
				$value_names .= ',`email`';
				$values .= ',"'.$A['email'].'"';
			}
			$query = 'INSERT INTO `'.TABLE_USER.'`('.$value_names.') VALUES('.$values.')';
		}
		$this->_query($query);
		return true;
	}
	
	public function userDelete(&$A) {
		$d = new DOMDocument();
		$e = $d->createElement('temp');
		list($r, $f, $l, $c) = $this->getUsers($A['id'], '', '', '', '', $e);
		for ($i=$f; $i<=$l && $c>0; $i++) {
			$vars = $this->DBC->getRow($r, $i);
			$A['id'] = $vars['id'];
			$this->delete($A);
		}
		$this->_query('DELETE FROM `'.TABLE_USER.'` WHERE `id`="'.$A['id'].'"');
		return true;
	}
	
	public function groupSave(&$A) {
		if ($A['name']!='') $this->_query('REPLACE INTO `'.TABLE_GROUP.'` VALUES('
			.(isset($A['id']) && $A['id']!='' ? '"'.$A['id'].'"' : 'NULL').', "'.$A['name'].'")');
		return true;
	}
	
	public function groupDelete(&$A) {
		$this->_query('DELETE FROM `'.TABLE_GROUP.'` WHERE `id`="'.$A['id'].'"');
		return true;
	}
	
	public function rightsSave(&$A) {
		$value_names = '`id`';
		$values = (isset($A['id']) && $A['id']!='') ? '"'.$A['id'].'"' : 'NULL';
		if (isset($A['ug_id']) && $A['ug_id']!='') {
			$value_names .= ',`ug_id`';
			$values .= ',"'.$A['ug_id'].'"';
		}
		if (isset($A['is_group']) && $A['is_group']!='') {
			$value_names .= ',`is_group`';
			$values .= ',"'.$A['is_group'].'"';
		}
		if (isset($A['link']) && $A['link']!='') {
			$value_names .= ',`link`';
			$values .= ',"'.$A['link'].'"';
		} else {
			$value_names .= ',`link`';
			$values .= ',NULL';
		}
		if (isset($A['allow']) && $A['allow']!='') {
			$value_names .= ',`allow`';
			$values .= ',"'.$A['allow'].'"';
		}
		if (isset($A['disallow']) && $A['disallow']!='') {
			$value_names .= ',`disallow`';
			$values .= ',"'.$A['disallow'].'"';
		}
		$this->_query('REPLACE INTO `'.TABLE_RIGHTS.'`('.$value_names.') VALUES('.$values.')');
		return true;
	}
	
	public function rightsDelete(&$A) {
		$this->_query('DELETE FROM `'.TABLE_RIGHTS.'` WHERE `id`="'.$A['id'].'"');
		return true;
	}
	
	
	
	/*
         * Form
         */

	public function formAdd($Id, $Code, $Redirect, $CheckForm, $Code2) {
		$this->_query('INSERT INTO `'.TABLE_FORM.'`(`id`, `code`, `redirect`, `checkform`, `code2`) VALUES("'
			.$Id.'", "'.$Code.'", "'.$Redirect.'", "'.addslashes($CheckForm).'", "'.$Code2.'")');
		return mysql_insert_id();
	}
	
	public function formGet($Id) {
		if ($r = $this->_query('SELECT `code`, `redirect`, `checkform`, `code2`, `post` FROM `'.TABLE_FORM
				.'` WHERE `id`="'.$Id.'"')) return $this->getRow($r);
		else return array('code'=>null, 'redirect'=>null, 'checkform'=>null, 'code2'=>null, 'post'=>null);
	}

	public function formClear($Id = null) {
		if (is_null($Id)) $this->_query('DELETE FROM `'.TABLE_FORM
			.'` WHERE `time`<NOW() - INTERVAL '.FORM_EXPIRE.' HOUR');
		else $this->_query('DELETE FROM `'.TABLE_FORM.'` WHERE `id`="'.$Id.'"');
	}

	public function formSavePost($Id, $Post) {
		return $this->_query('UPDATE `'.TABLE_FORM.'` SET `post`="'.addslashes($Post).'" WHERE `id`="'.$Id.'"');
	}
	
	
	
	/*
         * Statistic
         */
	
	public function statUnique() {
		list($r, $f, $l, $c) = $this->DBC->rawQuery('SELECT COUNT(*) AS "count" FROM `'.TABLE_STAT
			.'` WHERE `year`=YEAR(NOW()) AND `month`=MONTH(NOW()) AND `unique_list` LIKE "%'
			.$_SERVER['REMOTE_ADDR'].'%"', '', '');
		$row = $this->DBC->getRow($r);
		return $row['count']=='0';
        }

	public function statAdd($Unique, $External, $Google, $Yandex, $Rambler) {
		list($r, $f, $l, $c) = $this->DBC->rawQuery('SELECT COUNT(*) AS "count" FROM `'.TABLE_STAT
			.'` WHERE `year`=YEAR(NOW()) AND `month`=MONTH(NOW())', '', '');
		$row = $this->DBC->getRow($r);
		
		if ($row['count']=='0') $this->DBC->_query('INSERT INTO `'.TABLE_STAT
			.'` VALUES(YEAR(NOW()), MONTH(NOW()), 1, 1, '
			.($External ? '1' : '0').', '
			.($Google ? '1' : '0').', '.($Yandex ? '1' : '0').', '.($Rambler ? '1' : '0').', "'
			.$_SERVER['REMOTE_ADDR'].'")');
		else $this->DBC->_query('UPDATE `'.TABLE_STAT.'` SET `click`=`click`+1 '
			.($Unique ? ', `unique`=`unique`+1, `unique_list`=CONCAT(`unique_list`, " '
				.$_SERVER['REMOTE_ADDR'].'")' : '')
			.($External ? ', `external`=`external`+1' : '')
			.($Google ? ', `google`=`google`+1' : '')
			.($Yandex ? ', `yandex`=`yandex`+1' : '')
			.($Rambler ? ', `rambler`=`rambler`+1' : '')
			.' WHERE `year`=YEAR(NOW()) AND `month`=MONTH(NOW())');
        }

	public function statSearchAdd($Search, $Google, $Yandex, $Rambler) {
		list($r, $f, $l, $c) = $this->DBC->rawQuery('SELECT COUNT(*) AS "count" FROM `'
			.TABLE_SEARCH_STAT.'` WHERE `year`=YEAR(NOW()) AND `month`=MONTH(NOW()) AND `search`="'
			.addslashes($Search).'"', '', '');
		$row = $this->DBC->getRow($r);

		if ($row['count']=='0') $this->DBC->_query('INSERT INTO `'.TABLE_SEARCH_STAT
			.'` VALUES(YEAR(NOW()), MONTH(NOW()), "'.addslashes($Search).'", '
			.($Google ? '1' : '0').', '.($Yandex ? '1' : '0').', '.($Rambler ? '1' : '0').', 1)');
		else $this->DBC->_query('UPDATE `'.TABLE_SEARCH_STAT.'` SET `sum`=`sum`+1 '
			.($Google ? ', `google`=`google`+1' : '')
			.($Yandex ? ', `yandex`=`yandex`+1' : '')
			.($Rambler ? ', `rambler`=`rambler`+1' : '')
			.' WHERE `year`=YEAR(NOW()) AND `month`=MONTH(NOW()) AND `search`="'
			.addslashes($Search).'"');
        }
	
	public function logAdd($Ip, $User, $Link, $Post) {
		$Link = addslashes($Link);
		$Post = addslashes($Post);
		$this->_query("INSERT DELAYED INTO `CmsLog`(`ip`, `user`, `link`, `post`)
			VALUES('$Ip', '$User', '$Link', '$Post')");
        }

	public function counterAdd($Id) {
		$count = $this->counterGet($Id) + 1;
		$this->_query("REPLACE DELAYED INTO `CmsCounter`(`id`, `count`) VALUES('$Id', '$count')");
	}

	public function counterGet($Id) {
		$r = $this->_query("SELECT `count` FROM `CmsCounter` WHERE `id`='$Id'");
		if ($row = $this->getRow($r)) return (int)$row['count'];
		else return 0;
	}





	/*
	 * External Classes
	 */

	public function classAdd($ClassName) {
		$this->_query('CREATE TABLE IF NOT EXISTS `'.$ClassName.'` (
			`id` int(10) unsigned NOT NULL auto_increment,
			`version` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY  (`id`,`version`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
	}

	public function classSave($ClassName, $NewClassName) {
		$this->_query('ALTER TABLE `'.$ClassName.'` RENAME `'.$NewClassName.'`');
	}

	public function classRemove($ClassName) {
		$this->_query('DROP TABLE `'.$ClassName.'`');
	}

	public function classPropertyAdd($ClassName, $PropertyName, $PropertyType) {
		switch ($PropertyType) {
			case 'char':       $type = 'VARCHAR(255)'; break;
			case 'date':       $type = 'TIMESTAMP'; break;
			case 'int':        $type = 'INT'; break;
			case 'float':      $type = 'FLOAT'; break;
			case 'mediumtext': $type = 'MEDIUMTEXT'; break;
			case 'text':       $type = 'TEXT'; break;
		}
		$this->_query('ALTER TABLE `'.$ClassName.'` ADD `'.$PropertyName.'` '.$type);
	}

	public function classPropertySave($ClassName, $PropertyName, $NewPropertyName, $PropertyType) {
		switch ($PropertyType) {
			case 'char':       $type = 'VARCHAR(255)'; break;
			case 'date':       $type = 'TIMESTAMP'; break;
			case 'int':        $type = 'INT'; break;
			case 'float':      $type = 'FLOAT'; break;
			case 'mediumtext': $type = 'MEDIUMTEXT'; break;
			case 'text':       $type = 'TEXT'; break;
		}
		$this->_query('ALTER TABLE `'.$ClassName.'` CHANGE `'.$PropertyName.'` `'.$NewPropertyName.'` '.$type);
	}

	public function classPropertyRemove($ClassName, $PropertyName) {
		$this->_query('ALTER TABLE `'.$ClassName.'` DROP `'.$PropertyName.'`');
	}

}

?>
