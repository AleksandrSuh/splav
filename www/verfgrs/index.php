<?
function Scan($dir)
{
	$vcount = 0;
	global $my_svdir;
	$arr = array();
    if (!preg_match("/\.$/",$dir)){
		//echo $dir.'<br />';
		//exit;
       if (is_file($dir)) {
		   $arr[] = $dir;
			$trib = substr(strrchr($dir, '.'), 1);	
			if ($trib == 'php' && $dir!='verfgrs//index.php') {
				$mydir = explode('//', $dir);
				$verw_name = str_replace('/','_',$mydir[1]);	
				if(!$my_svdir) {
					//echo 'zapis '.$dir.' v sv/'.$verw_name.'<br />';
					$mycopy = copy($dir, 'verfgrs/sv/'.$verw_name);
				} else {
					if (file_exists('verfgrs/sv/'.$verw_name)) {
						//echo $verw_name.' найден';
						if (md5_file($dir) == md5_file('verfgrs/sv/'.$verw_name)) $vcount++; //echo " - нутро одинаково";
						else {
							//echo " два разных файла";
							copy('verfgrs/sv/'.$verw_name, $dir);							
						}
					}
					else {
						echo $dir.' - такой херни не было!';
						//unlink($dir);
					}
					//echo '<br />';
				}
				//if ($mycopy) echo ' yes'; else echo ' her';
				//echo $dir.'<br />';
			}
			
	   }
        else {
            $d=opendir("$dir");
            while(false !== ($file = readdir($d)))
				//echo $file.'<br />';
                if ($file!='verfgrs') Scan("$dir/$file");
            closedir($d);
        }
    }
/*	foreach ($arr as $file) {
		echo $file.'<br />';
	}*/
}
//echo dirname(__FILE__);
//exit;
if (file_exists('verfgrs/sv')) $my_svdir = true; 
else  $trac = mkdir('verfgrs/sv', 0500);
Scan('./');
?>