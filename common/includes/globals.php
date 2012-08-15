<?php
// current subversion revision
$svnrevision = '$492 $';
$svnrevision = trim(substr($svnrevision, 10, strlen($svnrevision)-11));

define('SVN_REV', $svnrevision);

define('LATEST_DB_UPDATE',"011");

// current version: major.minor.sub
// unpair numbers for minor = development version
define('KB_VERSION', '2.0.11');
define('KB_RELEASE', '(Dominion)');

// add new corporations here once you've added the logo to img/corps/
$corp_npc = array('Guristas', 'Serpentis Corporation', 'Sansha\'s Nation', 'CONCORD',
	'Mordus Legion', 'Blood Raider', 'Archangels', 'Guardian Angels', 'True Power');

function shorten($shorten, $by = 22)
{
	if (strlen($shorten) > $by)
	{
		$s = substr($shorten, 0, $by) . "...";
	}
	else $s = $shorten;

	return $s;
}

function slashfix($fix)
{
	return addslashes(stripslashes($fix));
}

function roundsec($sec)
{
	if ($sec <= 0)
		$s = 0.0;
	else
		$s = $sec;

	return number_format(round($s, 1), 1);
}
//! Check if a version of this template for the igb exists and return that if so.
function get_tpl($name)
{
	if (IS_IGB)
	{
		if (file_exists('./templates/igb_'.$name.'.tpl'))
		{
			return 'igb_'.$name.'.tpl';
		}
	}
	return $name.'.tpl';
}

// this is currently only a wrapper but might get
// timestamp adjustment options in the future
function kbdate($format, $timestamp = null)
{
	if ($timestamp === null)
	{
		$timestamp = time();
	}

	if (config::get('date_gmtime'))
	{
		return gmdate($format, $timestamp);
	}
	return date($format, $timestamp);
}

function getYear()
{
	$test = kbdate('o');
	if ($test == 'o')
	{
		$test = kbdate('Y');
	}
	return $test;
}

//! Return start date for the given week, month, year or date.

/*!
 * weekno > monthno > startWeek > yearno
 * weekno > monthno > yearno
 * startDate and endDate are used if they restrict the date range further
 * monthno, weekno and startweek are not used if no year is set
 */
function makeStartDate($week = 0, $year = 0, $month = 0, $startweek = 0, $startdate = 0)
{
	$qstartdate=0;
	if(intval($year)>2000)
	{
		if($week)
		{
			if($week < 10) $week = '0'.$week;
			$qstartdate = strtotime($year.'W'.$week.' UTC');
			// PHP 4-ish
			if($qstartdate <= 0)
			{
				$offset = date('w', strtotime($year."-01-01 00:01 UTC")) - 1;
				if($offset > 3) $offset = $offset - 7;
				$qstartdate = strtotime($year."-01-01 00:00 UTC")
					+ (($week-1) * 7 * 24 * 60 * 60)
					- $offset * 24 * 60 * 60;
			}
		}
		elseif($month)
			$qstartdate = strtotime($year.'-'.$month.'-1 00:00 UTC');
		elseif($startweek)
		{
			$qstartdate = strtotime($year.'W'.$startweek.' UTC');
		}
		else
			$qstartdate = strtotime($year.'-1-1 00:00 UTC');
	}
	//If set use the latest startdate and earliest enddate set.
	if($startdate && $qstartdate < strtotime($startdate." UTC")) $qstartdate = strtotime($startdate." UTC");
	return $qstartdate;
}

//! Return end date for the given week, month, year or date.

/*!
 *  Priority order of date filters:
 * weekno > monthno > startWeek > yearno
 * weekno > monthno > yearno
 * startDate and endDate are used if they restrict the date range further
 * monthno, weekno and startweek are not used if no year is set
 */
function makeEndDate($week = 0, $year = 0, $month = 0, $enddate = 0)
{
	if($year)
	{
		if($week)
		{
			if($week < 10) $week = '0'.$week;
			$qenddate = strtotime($year.'W'.$week.' +7days -1second UTC');
			// PHP 4-ish
			if($qenddate <= 0)
			{
				$offset = date('w', strtotime($year."-01-01 00:01 UTC")) - 1;
				if($offset > 3) $offset = $offset - 7;
				$qenddate = strtotime($year."-01-01 00:00 UTC")
					+ ($week * 7 * 24 * 60 * 60)
					- 1 // back 1 second into the previous week
					- $offset * 24 * 60 * 60;
			}
		}
		elseif($month)
		{
			if($month == 12) $qenddate = strtotime(($year).'-12-31 23:59 UTC');
			else $qenddate = strtotime(($year).'-'.($month + 1).'-1 00:00 - 1 minute UTC');
		}
		else
			$qenddate = strtotime(($year).'-12-31 23:59 UTC');
	}
	//If set use the earliest enddate.
	if($enddate && (!$qenddate || ($qenddate && $qenddate > strtotime($enddate." UTC")))) $qenddate = strtotime($enddate." UTC");

	return $qenddate;
}

if (!function_exists('file_put_contents'))
{
	function file_put_contents($filename, $data, $flags=null)
	{
		if(!is_null($flags) && $flags == FILE_APPEND) $f = @fopen($filename, 'ab');
		else $f = @fopen($filename, 'wb');
		if (!$f)
		{
			return false;
		} else
		{
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

if (!function_exists('file_get_contents'))
{
	function file_get_contents($filename, $incpath = false, $resource_context = null)
	{
		if (false === $f = fopen($filename, 'rb', $incpath))
		{
			trigger_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
			return false;
		}

		$data = '';
		if ($fsize = @filesize($filename))
		{
			while (!feof($f)) $data .= fread($f, $fsize);
		}
		else
		{
			while (!feof($f)) $data .= fread($f, 8192);
		}

		fclose($f);
		return $data;
	}
}
if (!function_exists('scandir'))
{
	function scandir($dir, $sorting_order = false, $context = null)
	{
		$dirArray = array();
		if ($handle = opendir($dir))
		{
			while (false !== ($file = readdir($handle)))
				array_push($dirArray,basename($file));
			closedir($handle);
		}
		else $dirArray = false;
		return $dirArray;
	}
}
