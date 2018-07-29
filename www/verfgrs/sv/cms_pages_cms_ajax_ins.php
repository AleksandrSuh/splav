<?php

header('Content-Type: application/xml; charset=UTF-8');

switch ($_GET['i']) {

case 'template_opts':
	$selected = isset($_GET['s']) ? $_GET['s'] : '';
	foreach (glob(DIR_ROOT.'/templates/*.xml') as $filename) {
		$template = basename($filename);
		echo '<option'.($template==$selected ? ' selected="selected"' : '')
			.' value="'.$template.'">'.$template.'</option>';
	}
	foreach (glob(DIR_ROOT.'/templates/*', GLOB_ONLYDIR) as $dirname) {
		$template_dir = basename($dirname);
		echo '<optgroup label="'.$template_dir.'">';
		foreach (glob(DIR_ROOT.'/templates/'.$template_dir.'/*.xml') as $filename) {
			$template = $template_dir.'/'.basename($filename);
			echo '<option'.($template==$selected ? ' selected="selected"' : '').' value="'.$template.'">'
				.$template.'</option>';
		}
		echo '</optgroup>';
	}
break;

case 'templates':
	$src = isset($_GET['src']) ? $_GET['src'] : '';
	$selected = isset($_GET['s']) ? $_GET['s'] : 'index';
	$Templates = new CTemplates;
	$src = $Templates->GetRealSrc($src);
	$d = $Templates->GetTemplateDocment($src);
	foreach ($d->getElementsByTagNameNS('/templates/ns', 'template') as $t) {
		$template = $t->getAttribute('id');
		echo '<option'.($template==$selected ? ' selected="selected"' : '').' value="'.$template.'">'
			.$template.'</option>';
	}
break;

default: break;

}

exit;

?>