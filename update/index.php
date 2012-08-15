<?php
// Update an existing installation.

/*
Each update is placed in a subfolder and subfolder/update.php is included then
function [subfoldername] is called. Official updates are numbered sequentially.
e.g. update/012/
*/
if(function_exists("set_time_limit"))
	@set_time_limit(0);
@error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

define('LATEST_DB_UPDATE', "013");
define('DB_HALTONERROR', true);
chdir("..");
require_once('kbconfig.php');
require_once('common/includes/db.php');
require_once('common/includes/class.config.php');
require_once('common/includes/class.session.php');
require_once('common/includes/globals.php');
require_once('common/smarty/Smarty.class.php');

$config = new Config(KB_SITE);
session::init();
$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
if($_SERVER['QUERY_STRING'] != "") $url .= '?'.$_SERVER['QUERY_STRING'];


if(isset($_GET['do']) && $_GET['do'] == 'force')
{
	$url=preg_replace('/(\?|&)do=force/','',$url);
	config::set('DBUpdate', '001');
}
$smarty = new Smarty();
$smarty->compile_dir = getcwd().'/cache/templates_c';
$smarty->cache_dir = getcwd().'/cache/data';
$smarty->template_dir = getcwd().'/update/';
$smarty->assign('url',$url);

if (!session::isAdmin())
{
	if (isset($_POST['usrpass']) && (crypt($_POST['usrpass'],ADMIN_PASSWORD) == ADMIN_PASSWORD || $_POST['usrpass'] == ADMIN_PASSWORD))
	{
		session::create(true);

		header('Location: '.$url);
		die;
	}
	else
	{
		$smarty->assign('content', $smarty->fetch('update_login.tpl'));
		$smarty->display('update.tpl');
		die;
	}
}

if(isset($_GET['package']))
{
	$package = preg_replace('/[^\w]/','',$_GET['package']);
	if(is_dir('update/'.$package)) require('update/'.$package.'/update.php');
	else
	{
		$smarty->assign('content', "Specified package does not exist.");
		$smarty->display('update.tpl');
	}
	die;
}
$qry=new DBQuery(true);
define('CURRENT_DB_UPDATE', config::get("DBUpdate"));
if (CURRENT_DB_UPDATE >= LATEST_DB_UPDATE )
{
		$smarty->assign('content', "Board is up to date.<br><a href='".config::get('cfg_kbhost')."/'>Return to your board</a>");
		$smarty->display('update.tpl');
	die();
}
updateDB();
@touch ('install/install.lock');
		$smarty->assign('content', "Update complete.<br><a href='".config::get('cfg_kbhost')."/'>Return to your board</a>");
		$smarty->display('update.tpl');
die();

function updateDB()
{
// if update nesseary run updates
	killCache();
	removeOld(0,'cache/templates_c', false);
	chdir('update');
	$dir = opendir('.');
	$updatedirs = array();
	while ($file = readdir($dir))
    {
		
        if ($file[0] == '.' || !is_dir($file))
        {
            continue;
        }
		else $updatedirs[] = $file;
	}
	asort($updatedirs);
	foreach($updatedirs as $curdir)
	{
		if(!preg_match("/[0-9]+/",$curdir)) continue;
		if(CURRENT_DB_UPDATE >= $curdir) continue;
		require_once($curdir.'/update.php');
		$func = 'update'.$curdir;
		$func();
	}
}

function update_slot_of_group($id,$oldSlot = 0 ,$newSlot)
{
	$qry  = new DBQuery();
	$query = "UPDATE kb3_item_types
				SET itt_slot = $newSlot WHERE itt_id = $id and itt_slot = $oldSlot;";
	$qry->execute($query);
	$query = "UPDATE kb3_items_destroyed
				INNER JOIN kb3_invtypes ON groupID = $id AND itd_itm_id = typeID
				SET itd_itl_id = $newSlot
				WHERE itd_itl_id = $oldSlot;";
	$qry->execute($query);

	$query = "UPDATE kb3_items_dropped
				INNER JOIN kb3_invtypes ON groupID = $id AND itd_itm_id = typeID
				SET itd_itl_id = $newSlot
				WHERE itd_itl_id = $oldSlot;";
	$qry->execute($query);
}

function move_item_to_group($id,$oldGroup ,$newGroup)
{
	$qry  = new DBQuery();
	$query = "UPDATE kb3_invtypes
				SET groupID = $newGroup
				WHERE typeID = $id AND groupID = $oldGroup;";
	$qry->execute($query);
}

function killCache()
{
	if(!is_dir(KB_CACHEDIR)) return;
	$dir = opendir(KB_CACHEDIR);
	while ($line = readdir($dir))
	{
		if (strstr($line, 'qcache_qry') !== false)
		{
			@unlink(KB_CACHEDIR.'/'.$line);
		}
		elseif (strstr($line, 'qcache_tbl') !== false)
		{
			@unlink(KB_CACHEDIR.'/'.$line);
		}
	}
}

function removeOld($hours, $dir, $recurse = false)
{
	if(!session::isAdmin()) return false;
	if(strpos($dir, '.') !== false) return false;
	//$dir = KB_CACHEDIR.'/'.$dir;
	if(!is_dir($dir)) return false;
	if(substr($dir,-1) != '/') $dir = $dir.'/';
	$seconds = $hours*60*60;
	$files = scandir($dir);

	foreach ($files as $num => $fname)
	{
		$del = 0;
		if (file_exists("{$dir}{$fname}") && !is_dir("{$dir}{$fname}") && substr($fname,0,1) != "." && ((time() - filemtime("{$dir}{$fname}")) > $seconds))
		{
			$mod_time = filemtime("{$dir}{$fname}");
			if (unlink("{$dir}{$fname}")) $del = $del + 1;
		}
		if ($recurse && file_exists("{$dir}{$fname}") && is_dir("{$dir}{$fname}")
			 && substr($fname,0,1) != "." && $fname !== ".." )
		{
			$del = $del + admin_acache::remove_old($hours, $dir.$fname."/");
		}
	}
	return $del;
}