<?php
/* 
  w2box: web 2.0 File Repository v4.0 Beta5
  
  (c) 2005-2007, Cl�ment Beffa
  http://labs.beffa.org/w2box/
 
  Licence:
  w2box is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 3.0
  http://creativecommons.org/licenses/by-nc-sa/3.0/
 
  You are free:
    * to Share � to copy, distribute and transmit the work
    * to Remix � to adapt the work

  Under the following conditions:
    * Attribution. You must attribute the work in the manner specified by the author or
      licensor (but not in any way that suggests that they endorse you or your use of the work).
    * Noncommercial. You may not use this work for commercial purposes.
    * Share Alike. If you alter, transform, or build upon this work, you may distribute
      the resulting work only under the same or similar license to this one.

  For any reuse or distribution, you must make clear to others the license terms of this work.
  Any of the above conditions can be waived if you get permission from the copyright holder.
  Nothing in this license impairs or restricts the author's moral rights.
*/

         ini_set("display_errors",1);
    //   error_reporting(E_ALL);
  //    set log_errors=1 
  //    error_log=error.log 
//
// you should edit config.php (and upload.cgi) in order to configure this script.
require("config.php");
$dir = (isset($_GET['d']))?$_GET['d']:isset($_POST['d']);
sizeof([$dir]);
if ($dir && $config['enable_folder_maxdepth']){
	$dir = split("/",$dir);
	foreach ($dir as $k => $v) {
		if (($v == "..") || ($v == ".") || ($v == "")) unset($dir[$k]);
	}
	$path = $config['storage_path'];
	foreach ($dir as $k => $v) {
		if (!file_exists($path."/$v"))
			unset($dir[$k]);
		else
			$path .= "/$v";
	}
	$config['storage_path'] .= "/".join("/",$dir);
	
	if (isset($dir)) $dirlevel = sizeof($dir); else $dirlevel = 0;
} else
	unset($dir);


//authentication
$auth = !$config['admin_actived'];
authorize(true); //silent authorize first
if (isset($_GET["admin"])) {
	authorize();
	Header("Location: ".rooturl());
}

//bruteforce php ini, almost never work except on old php..
ini_set('post_max_size','500M') ;
ini_set('upload_max_filesize','500M');
ini_set('memory_limit','500M');
//find real max_filesize
$max_filesize = $config['max_filesize'] * pow(1024,2);

if (!$config['upload_progressbar']) //doesn't apply with the perl script
	$max_filesize = max(return_bytes(ini_get('post_max_size')),return_bytes(ini_get('upload_max_filesize')),return_bytes(ini_get('memory_limit')),$max_filesize);


	
// deleting
if (isset($_POST["delete"])) {
	if ($config['protect_delete']) authorize();
	deletefile($_POST["delete"]);
}

function deletefile($cell){
	global $config, $lang;

	//decode str
	$str=stripslashes(urldecode($cell));
	$str=substr($str, strpos($str,'href="')+6);
	$str=substr($str,0, strpos($str,'"'));
	$file = $config['storage_path'].'/'.basename($str);

	if (!file_exists($file))
	echo $lang['delete_error_notfound']." ($file)";
	else {
		if (is_dir($file))
			$return = @rmdir($file);
		else 
			$return = @unlink($file);
		
		if ($config['log_delete']) logadm($lang['DELETE']." ".$file);
		if ($return) 
			echo "successful"; 
		else { 
			if (is_dir($file))
				echo $lang['delete_error_cant_dir'];
			else
				echo $lang['delete_error_cant'];
		}
	}
	exit;
}

//progress bar notification
if (isset ($_POST['progress'])) {
	//define filenames
	$sessionid = basename($_POST['progress']);
	$tmp_dir = $config['upload_tmpdir'];
	$error_file = $tmp_dir."/$sessionid"."_err";
	$signal_file = $tmp_dir."/$sessionid"."_signal";
	$info_file = $tmp_dir."/$sessionid"."_flength";
	$data_file = $tmp_dir."/$sessionid"."_postdata";

	if(!file_exists($tmp_dir)) {
		header("HTTP/1.1 500 Internal Server Error");
		echo "tmp directory is invalid!";
	} else if(file_exists($error_file)) {
		# Send error code if error file exists
		header("HTTP/1.1 500 Internal Server Error");
		echo file_get_contents($error_file);
		//clean the shit
		$files = array($info_file,$data_file,$error_file,$signal_file);
		foreach($files as $file) {
			if(file_exists($file)) {
				unlink($file);
			}
		}
	} else if (file_exists($signal_file)) {
		echo "FINISHED";
	} else if (file_exists($info_file)){
		$total = file_get_contents($info_file);
		$current = @filesize($data_file);
		echo intval(($current / $total) * 100);
	}
	else echo '0';
	exit;
}

//progressbar upload
if ($config['upload_progressbar']){
	if (isset($_GET['sid'])) {
		$sid = $_GET['sid'];
		$tmp_dir = $config['upload_tmpdir'];
		$sid = ereg_replace("[^a-zA-Z0-9]","",$sid);//clean sid
		$file = $tmp_dir.'/'.$sid.'_qstring';
		if(!file_exists($file)) {
			$errormsg = $lang['upload_error_sid'];
		} else {
			$qstr = join("",file($file));
			//parse_str($qstr);
			parse_str($qstr, $_POST);			

			//cleaning shit
			$exts = array("_flength","_postdata","_err","_signal","_qstring");
			foreach($exts as $ext) {
				if(file_exists("$tmp_dir/$sid$ext")) {
					@unlink("$tmp_dir/$sid$ext");
				}
			}
			
			//setting vars like without progressbar
			$_FILES['file']['name']=basename($_POST['file']['name']['0']);
			$_FILES['file']['size']=$_POST['file']['size']['0'];
			$_FILES['file']['tmp_name']=$_POST['file']['tmp_name']['0'];
		}
	} else if (isset($_POST['errormsg'])) {
		$errormsg = urldecode($_POST['errormsg']);
		if ($errormsg =="The maximum upload size has been exceeded")
		$errormsg = $lang['upload_error_sizelimit'].' ('.getfilesize($max_filesize).').';
	}
}

//uploading
if (isset($_FILES['file'])) {
	if ($config['protect_upload']) authorize();
	uploadfile($_FILES['file']);
}

function uploadfile($file) {
	global $config, $lang, $max_filesize, $errormsg,$dir;

	if ($file['error']!=0) {
		$errormsg = $lang['upload_error'][$file['error']];
		return;
	}

	//determine filename
	$filename=$file['name'];
	if (isset($_POST['filename']) && $_POST['filename']!="") $filename=$_POST['filename'];
	$filename=basename($filename);
	$filename=explode(".",basename($filename));
	$ext = $filename[count($filename)-1];
	unset($filename[count($filename)-1]);
	$filename=join('_',$filename).'.'.$ext;
	
	if (!in_array(strtolower(extname($filename)), $config['allowed_ext'])) {
		$errormsg = $lang['upload_badext'];
		return;
	}

	$filesize=$file['size'];
	if ($filesize > $max_filesize) {
		@unlink($file['tmp_name']);
		$errormsg = $lang['upload_error_sizelimit'].' ('.getfilesize($max_filesize).').';
		return;
	}

	$filedest = $config['storage_path'].'/'.$filename;
	if (file_exists($filedest) && !$config['allow_overwrite']) {
		@unlink($file['tmp_name']);
		$errormsg = "$filename ".$lang['upload_error_fileexist'];
		return;
	}

	$filesource=$file['tmp_name'];
	if (!file_exists($filesource)) {
		$errormsg = "$filesource do no exist!";
		return;
	} else if (!move_uploaded_file($filesource,$filedest)) {
		if (!rename($filesource,$filedest)) {
			$errormsg = $lang['upload_error_nocopy'];
			return;
		}
	}

	if  ($errormsg=="") {
		chmod ($filedest, 0755);
		if ($config['log_upload']) logadm($lang['UPLOAD'].' '.$filedest);
		$loc = returnurl();
		if (filesize($dir)>0) $loc .= join("/",$dir)."/";
		Header("Location: ".$loc);
		exit;
	}
}

//make dir
if (isset ($_POST['dir'])) {
	if ($config['protect_makedir']) authorize();
	if ($dirlevel < $config['enable_folder_maxdepth']) {
		$newdir = preg_replace("/[^0-9a-zA-Z\(\)\s]/i",'', $_POST['dir']);
		if ($newdir <> "") {
			$newdir = $config['storage_path']."/".$newdir;
			if (file_exists($newdir))
				$errormsg = $lang['make_error_exist'];
			else {
				if (mkdir($newdir)) {
					$loc = rooturl();
					if (filesize($dir)>0) $loc .= join("/",$dir)."/";
					Header("Location: ".$loc);
					exit;
				} else
					$errormsg = $lang['make_error_cant'];
			}
		}
	} else {
		$errormsg = $lang['make_error_maxdepth'];
	}
}




///////////////////////////////////////////////////////////////////////////////////

if (isset($_GET['storage']))
{
setstorage($_GET['storage']);
}else{
$url="data";
}

function setstorage($filed) {

	global $config;
	if ( $config['storage_path'] < "data" )	{
		$config['storage_path'] = "data/".$filed;
		echo $config['storage_path'];
	} else {
		$config['storage_path'] .= "/".$filed;
	}
}

///////////////////////////////////////////////////////////////////////////////////


//downloading
if (isset($_GET['download']))
downloadfile($_GET['download']);


function downloadfile($file){
	global $config, $lang;
	$file = $config['storage_path'].'/'.basename($file);
	if (!is_file($file)) { return; }
	header("Content-Type: application/octet-stream");
	header("Content-Size: ".filesize($file));
	header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
	header("Content-Length: ".filesize($file));
	header("Content-transfer-encoding: binary");
	@readfile($file);
	if ($config['log_download']) logadm($lang['DOWNLOAD'].' '.$file);
	exit;
}

function authorize($silent=false){
	global $config, $lang, $auth;
	//authentication
	if (!$auth){
		if ((empty($_SERVER['PHP_AUTH_USER']) or empty($_SERVER['PHP_AUTH_PW'])) and isset($_REQUEST['BAD_HOSTING']) and preg_match('/Basic\s+(.*)$/i', $_REQUEST['BAD_HOSTING'], $matc))
          list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode($matc[1]));
	
		if ((isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) &&
		($_SERVER['PHP_AUTH_USER'] == $config['admin_username'] && $_SERVER['PHP_AUTH_PW']==$config['admin_password'])) {
			$auth = true; // user is authenticated
		} else {
			if (!$silent) {
				header( 'WWW-Authenticate: Basic realm="w2box admin"' );
				header( 'HTTP/1.0 401 Unauthorized' );
				echo 'Your are not allowed to access this function!';
				exit;
			}
		}
	}

}


function extname($file) {
	$file = explode(".",basename($file));
	return $file[count($file)-1];
}

function getfilesize($size) {
	//if ($size < 2) return "$size byte";
	$units = array(' B', ' KiB', ' MiB', ' GiB', ' TiB');
	for ($i = 0; $size > 1024; $i++) { $size /= 1024; }
	return round($size, 2).$units[$i];
}

function return_bytes($val) {
	$val = trim($val);
	$intval = intval(trim($val));
	if (empty($intval)) return pow(1024,3);
	$last = strtolower($val[(strlen($intval)-1)]);
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
		$intval *= 1024;
		case 'm':
		$intval *= 1024;
		case 'k':
		$intval *= 1024;
	}
	return $intval;
}

function returnurl(){
	$dir = dirname($_SERVER['PHP_SELF']);
	if (strlen($dir) > 1) $dir.="/";

	return "http://".$_SERVER['HTTP_HOST'].$dir."?page=downloads";
	//return "http://".$_SERVER['HTTP_HOST'].$dir."/w2box/index.php";
}

function rooturl(){
	$dir = dirname($_SERVER['PHP_SELF']);
	if (strlen($dir) > 1) $dir.="/";

	return "http://".$_SERVER['HTTP_HOST'].$dir;
}

function logadm($str) {
	global $config, $lang;
	if (!$config['log']) return;

	//$file_handle = fopen($config['log_filename'],"a+");
	//fwrite($file_handle, date("Y-m-d\TH:i:s").' '.sprintf("%15s",$_SERVER["REMOTE_ADDR"]).' '. $str."\n");
	//fclose($file_handle);
}

function ls($dir) {
	global $config, $lang, $auth, $demo;
	if ($demo){
		// demo code -- deleteme file
		$file = "data/deleteme.txt";
		if (!$file_handle = fopen($file,"a")) { echo "Cannot open file"; }
		if (!fwrite($file_handle, "Delete me or I'll become fat!!!\n")) { echo "Cannot write to file"; }
		fclose($file_handle);
	}

	$files = Array();
	echo $dir;
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (substr($file,0,1) != "." && $file != "index.html") {
				$size=filesize($dir."/".$file);
				$date=filemtime($dir."/".$file);
				$ext=strtolower(extname($file));
				if (is_dir($dir."/".$file)) $ext="directory";
				if ($config['delete_after'] && ($date < mktime(0, 0, 0, date("m"), date("d")-$config['delete_after'], date("Y")))){
					if (is_dir($dir."/".$file))
						@rmdir($dir."/".$file);
					else 
						@unlink($dir."/".$file);
				} 
				if(file_exists($dir."/".$file))
					$files[] = Array('file'=>$file,'date'=>$date, 'size'=>$size, 'ext'=>$ext);
			}
		}
		closedir($handle);

	}
	if (is_array($files) && !empty($files)) {
		foreach ($files as $key => $row) {
   			$fn[$key]  = strtolower($row['file']);
		}
		array_multisort($fn, SORT_ASC, SORT_STRING, $files);
	}
	return $files;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=iso-8859-1" />
  <title><?php echo $config['w2box_title']; ?> | powered by w2box</title>
  <link rel="stylesheet" type="text/css" href="w2box/w2box.css" />
  <script type="text/javascript">
  //<![CDATA[
  var ROOT_URL = '<?php echo rooturl(); ?>';
  var ALLOWED_TYPES = '.<?php echo join(".",$config['allowed_ext']); ?>.';
  var MAX_FILESIZE = <?php echo $max_filesize; ?>;
  var UPLOAD_SCRIPT = '<?php echo $config['upload_cgiscript']; ?>';
  //]]>-->
  </script>
  <script type="text/javascript" src="w2box/pt.ajax.js"></script>
  <script type="text/javascript" src="w2box/sorttable.js"></script>  
  <script type="text/javascript" src="w2box/w2box.js"></script>
</head>

<div id="page" class="mainbox">

<div id="header">
 <h1><a href="."><?php echo $config['w2box_title']; ?></a></h1>
</div>

<div id="content">
<?php 
if ($config['show_warning']) echo '<div id="warningmsg"><p>'.$lang['warning_msg'].'</p></div>'."\n";
if (isset($errormsg)) echo '<div id="errormsg"><p>'.$errormsg.'</p></div>'."\n";

if ($config['enable_folder_maxdepth'] && (!($config['hide_makedir']) || $auth)) { ?>
<div id="makedirform" class="formdiv">
<?php if ($config['protect_delete'] && $config['admin_actived'] ) echo "<h3>admin can only delete files</h3>" ?> 
<form method="post" action="">
 <p><label for="dir"><?php echo $lang['dir'] ?> :</label><input type="text" id="dir" name="dir" size="50" /><input id="make" type="submit" value="<?php echo $lang['make'] ?>" class="button" /></p>
 </form>
</div>
<?php
}

if (!($config['hide_upload']) || $auth) { ?>
<div id="uploadform" class="formdiv">
<?php $sid = md5(uniqid(rand())); //unique file id ?>


<!-- UPLOAD FORM FILES MY COMMENT -->
 <form method="post" enctype="multipart/form-data">
  <p><label for="file"><?php echo $lang['file'] ?> :</label><input type="file" id="file" name="file" size="50" onchange="renameSync();" /><input id="upload" type="submit" value="<?php echo $lang['upload'] ?>" class="button" <?php if ($config['upload_progressbar']) echo 'onclick="beginUpload(\''.$sid.'\');return false;" '; ?>/></p>
  <p><label for="filename"><?php echo $lang['renameto'] ?> :</label><input type="text" id="filename" name="filename" onkeyup="filetypeCheck();" size="50" /></p>
  <p class="small"><span id="allowed"><?php echo $lang['filetypesallowed'] ?> : <?php echo join(",",$config['allowed_ext']); ?></span>
  <br /><?php echo $lang['filesizelimit'] ?> : <?php echo getfilesize($max_filesize); ?>
  <?php if ($config['delete_after']) echo   '<br />'.str_replace("{D}",$config['delete_after'],$lang['filedeleteafter']); ?>
  </p>
 </form>



<?php if ($config['upload_progressbar']){ ?>
<div id="upload_pb" style="display: none;">
  <p>Uploading <span id="upload_filename"></span> ...</p>
  <div id="upload_border"><div id="upload_progress"></div></div>
</div>
<iframe name="upload_iframe" style="border:0;width:0px;height:0px;visibility:hidden;"></iframe>
<?php } ?>
</div>
<?php } ?>

<?php 
global $dir;
if ($dir>0) {
	echo '<div id="dirpath"><p>';
	$path = rooturl();
	echo '<a href="'.$path.'">w2box</a>';
	foreach ($dir as $k => $v) {
		$path .= "$v/";
		if (sizeof($dir) == $k+1)
			echo ' &raquo; '.$v.' ';
		else 
			echo ' &raquo; <a href="'.$path.'">'.$v.'</a> ';
			
	}
	echo '<a href="..">(go up)</a></p></div>';
}
?>
<div id="filelisting">
 <img src="images/arrow-up.gif" alt="" style="display:none;" /><img src="images/arrow-down.gif" alt="" style="display:none;" />
 <table id="t1" class="sortable">
  <tr>
  <th id="th1" class="lefted"><?php echo $lang['filename']; ?></th>
  <th id="th2"><?php echo $lang['date']; ?></th>
  <th id="th3"><?php echo $lang['size']; ?></th>
  <th id="th4"><?php echo $lang['type']; ?></th>
  <?php if (!$config['hide_delete'] || $auth) echo '<th id="th5" class="unsortable">'.$lang['delete'].'</th>'; ?>
  </tr>
<?php
	$files = ls($config['storage_path']);
	if (empty($files)){
		echo '  <tr><td class="lefted">'.$lang['nofiles'].'</td></tr>';
	} 
	else 
	{
	   foreach ($files as $file) {
		echo '  <tr class="off" onmouseover="if (this.className!=\'delete\') {this.className=\'on\'};" onmouseout="if 				(this.className!=\'delete\') {this.className=\'off\'};">';
		echo '<td class="lefted">';
		echo '<img src="'.rooturl().'images/icons/'.$file['ext'].'.gif" alt="" /> ';

		if ($config['disable_directlink'])
			echo $file['file'];
		else {
			$dlink = $file['file'];
			if ($config['utf8encode_directlink'])
			$dlink = utf8_encode($file['file']);

			if ($file['ext']!="directory")
				$url = rooturl().$config['storage_path'].'/'.rawurlencode($dlink);
			else
///////////////////////////////////////////////////////////////////////////////////
				//if (isset($_GET['storage']))
				//$url.=rawurlencode($dlink);//.rawurlencode($dlink);		//FOLDER URL
				//else
				$url = "?storage=".rawurlencode($dlink);//.rawurlencode($dlink);
///////////////////////////////////////////////////////////////////////////////////


			echo '<a href="'.$url.'">';
			$maxlen=29;

			if ($maxlen>0 && strlen($file['file'])>$maxlen)
				echo substr($file['file'],0,$maxlen-3)."...";
			else 
				echo $file['file'];
			echo '</a>';
		}

		echo '&nbsp;';
		if ($file['ext']!="directory") echo '<a href="?download='.urlencode($file['file']).'" download><img src="'.rooturl().'images/download_arrow.gif" alt="('.$lang['download'].')" title="'.$lang['download_link'].'" /></a></td>';
		echo '<td>'.date ($lang['date_format'], $file['date']).'</td>';
		echo '<td>';
		if ($file['ext']!="directory") echo getfilesize($file['size']);
		echo '</td>';
		echo '<td>';
		if ($file['ext']!="directory") echo '<img src="'.rooturl().'images/icons/'.$file['ext'].'.gif" alt="" /> <span>'.$file['ext'].'</span>';
		echo '</td>';
		if (!$config['hide_delete'] || $auth) {
			echo '<td>';
			//if ($file['ext']!="directory") {
				echo '<a onclick="';

				if ($config['confirm_delete'])
				echo 'if(confirm(\''.$lang['delete_confirm_msg'].'\')) ';
				echo 'deletefile(this.parentNode.parentNode); return false;" ';
				echo 'href=""><img src="'.rooturl().'images/delete.gif" alt="'.$lang['delete'].'" title="'.$lang['delete_link'].'" /></a>';
			//}
			echo '</td>';
		}
		echo '</tr>'."\n";
	}
}
?>
 </table>
</div>
</div>

<div id="footer">
  <p><a id="hiddenlink" href="w2box/index.php?admin" onmouseover="return true;">Powered</a>.</p>
</div>
</div>
</body>
</html>
