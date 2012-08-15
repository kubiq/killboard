<?php
require_once("class.ship.php");
require_once("class.summary.php");

class KillSummaryTable
{
	function KillSummaryTable($klist = null, $llist = null)
	{
		$this->klist_ = $klist;
		$this->llist_ = $llist;
		$this->verbose_ = false;
		$this->filter_ = true;
		$this->inv_plt_ = array();
		$this->inv_crp_ = array();
		$this->inv_all_ = array();
	}

	function setBreak($break)
	{
		$this->break_ = $break;
	}

	function setVerbose($verbose)
	{
		$this->verbose_ = $verbose;
	}

	function setFilter($filter)
	{
		$this->filter_ = $filter;
	}

	function setWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->weekno_ = 1;
		if($weekno >53) $this->weekno_ = 53;
		else $this->weekno_ = $weekno;
	}

	function setMonth($monthno)
	{
		$monthno = intval($monthno);
		if($monthno < 1) $this->monthno_ = 1;
		if($monthno > 12) $this->monthno_ = 12;
		else $this->monthno_ = $monthno;
	}

	function setYear($yearno)
	{
	// 1970-2038 is the allowable range for the timestamp code used
	// Needs to be revisited in the next 30 years
		$yearno = intval($yearno);
		if($yearno < 1970) $this->yearno_ = 1970;
		if($yearno > 2038) $this->yearno_ = 2038;
		else $this->yearno_ = $yearno;
	}

	function setStartWeek($weekno)
	{
		$weekno=intval($weekno);
		if($weekno <1)  $this->startweekno_ = 1;
		if($weekno >53) $this->startweekno_ = 53;
		else $this->startweekno_ = $weekno;
	}

	function setStartDate($timestamp)
	{
	// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->startDate_ = $timestamp;
	}

	function setEndDate($timestamp)
	{
	// Check timestamp is valid before adding
		if(strtotime($timestamp)) $this->endDate_ = $timestamp;
	}

	// Return SQL for date filter using currently set date limits
	function setDateFilter()
	{
		$qstartdate = makeStartDate($this->weekno_, $this->yearno_, $this->monthno_, $this->startweekno_, $this->startDate_);
		$qenddate = makeEndDate($this->weekno_, $this->yearno_, $this->monthno_, $this->endDate_);
		if($qstartdate) $sql .= " kll.kll_timestamp >= '".gmdate('Y-m-d H:i',$qstartdate)."' ";
		if($qstartdate && $qenddate) $sql .= " AND ";
		if($qenddate) $sql .= " kll.kll_timestamp <= '".gmdate('Y-m-d H:i',$qenddate)."' ";
		return $sql;
	}

	function getTotalKills()
	{
		return $this->tkcount_;
	}

	function getTotalLosses()
	{
		return $this->tlcount_;
	}

	function getTotalKillPoints()
	{
		return $this->tkpoints_;
	}

	function getTotalLossPoints()
	{
		return $this->tlpoints_;
	}

	function getTotalKillISK()
	{
		return $this->tkisk_;
	}

	function getTotalLossISK()
	{
		return $this->tlisk_;
	}

	function setView($string)
	{
		$this->view_ = $string;
	}

	function addInvolvedPilot($pilot)
	{
		$this->inv_plt_[] = $pilot->getID();
		if ($this->inv_crp_ || $this->inv_all_)
		{
			$this->mixedinvolved_ = true;
		}
	}

	function addInvolvedCorp($corp)
	{
		$this->inv_crp_[] = $corp->getID();
		if ($this->inv_plt_ || $this->inv_all_)
		{
			$this->mixedinvolved_ = true;
		}
	}

	function addInvolvedAlliance($alliance)
	{
		$this->inv_all_[] = $alliance->getID();
		if ($this->inv_plt_ || $this->inv_crp_)
		{
			$this->mixedinvolved_ = true;
		}
	}

	// do it faster, baby!
	function getkills()
	{
		if ($this->mixedinvolved_)
		{
			echo 'mode not supported<br>';
			exit;
		}
		if( count($this->inv_all_) == 1 && !$this->inv_crp_ && !$this->inv_plt_ && $this->setDateFilter() == "")
		{
			$allsum = new allianceSummary($this->inv_all_[0]);
			$summary = $allsum->getSummary();
			foreach($summary as $key => $row)
			{
				$this->entry_[$row['class_name']] = array('id' => $key,
					'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
					'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

				$this->tkcount_ += $row['killcount'];
				$this->tkisk_ += $row['killisk'];
				$this->tlcount_ += $row['losscount'];
				$this->tlisk_ += $row['lossisk'];
			}
			return;
		}
		elseif( count($this->inv_crp_) == 1 && !$this->inv_all_ && !$this->inv_plt_ && $this->setDateFilter() == "")
		{
			$crpsum = new corpSummary($this->inv_crp_[0]);
			$summary = $crpsum->getSummary();
			foreach($summary as $key => $row)
			{
				$this->entry_[$row['class_name']] = array('id' => $key,
					'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
					'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

				$this->tkcount_ += $row['killcount'];
				$this->tkisk_ += $row['killisk'];
				$this->tlcount_ += $row['losscount'];
				$this->tlisk_ += $row['lossisk'];
			}
			return;
		}
		elseif( count($this->inv_plt_) == 1 && !$this->inv_all_ && !$this->inv_crp_ && $this->setDateFilter() == "")
		{
			$pltsum = new pilotSummary($this->inv_plt_[0]);
			$summary = $pltsum->getSummary();
			foreach($summary as $key => $row)
			{
				$this->entry_[$row['class_name']] = array('id' => $key,
					'kills' => $row['killcount'], 'kills_isk' => $row['killisk'],
					'losses' => $row['losscount'], 'losses_isk' => $row['lossisk']);

				$this->tkcount_ += $row['killcount'];
				$this->tkisk_ += $row['killisk'];
				$this->tlcount_ += $row['losscount'];
				$this->tlisk_ += $row['lossisk'];
			}
			return;
		}

		$this->entry_ = array();
		// as there is no way to do this elegantly in sql
		// i'll keep it in php
		$sql = "select scl_id, scl_class from kb3_ship_classes
               where scl_class not in ('Drone','Unknown') order by scl_class";

		$qry = new DBQuery();
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry_[$row['scl_class']] = array('id' => $row['scl_id'],
				'kills' => 0, 'kills_isk' => 0,
				'losses' => 0, 'losses_isk' => 0);
		}

		$sql = 'SELECT count(distinct kll.kll_id) AS knb, scl_id, scl_class,';
		$sql .= ' sum(kll_isk_loss) AS kisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
		$sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

		// Force MySQL to first filter by date if a date range is given.
		if($this->setDateFilter())
		{
			$sql.= 'WHERE '.$this->setDateFilter();
			if ($this->inv_plt_)
			{
				$sql .= ' AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind.ind_kll_id AND ind.ind_plt_id in ( '.implode(',', $this->inv_plt_).' ) LIMIT 1)';
			}
			if ($this->inv_crp_)
			{
				$sql .= ' AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind.ind_kll_id AND ind.ind_crp_id in ( '.implode(',', $this->inv_crp_).' ) AND ind.ind_crp_id != kll.kll_crp_id LIMIT 1)';
			}
			if ($this->inv_all_)
			{
				$sql .= ' AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind.ind_kll_id and ind.ind_all_id in ( '.implode(',', $this->inv_all_).' ) and ind.ind_all_id != kll.kll_all_id LIMIT 1) ';
			}
		}
		else
		{
			if ($this->inv_plt_)
			{
				$sql .= ' INNER JOIN (SELECT distinct a.ind_kll_id FROM kb3_inv_detail a WHERE a.ind_plt_id in ( '.implode(',', $this->inv_plt_).' ) ) ind ON (ind.ind_kll_id = kll.kll_id) ';
			}
			elseif ($this->inv_crp_)
			{
				$sql .= ' INNER JOIN (SELECT distinct b.ind_kll_id, b.ind_crp_id FROM kb3_inv_detail b WHERE b.ind_crp_id in ( '.implode(',', $this->inv_crp_).' ) ) ind  ON (ind.ind_kll_id = kll.kll_id AND ind.ind_crp_id != kll.kll_crp_id) ';
			}
			elseif ($this->inv_all_)
			{
				$sql .= ' INNER JOIN (SELECT distinct c.ind_kll_id, c.ind_all_id FROM kb3_inv_detail c WHERE c.ind_all_id in ( '.implode(',', $this->inv_all_).' ) ) ind  ON (ind.ind_kll_id = kll.kll_id AND ind.ind_all_id != kll.kll_all_id) ';
			}
		}

		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = new DBQuery();
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry_[$row['scl_class']]['kills'] = $row['knb'];
			$this->entry_[$row['scl_class']]['kills_isk'] = $row['kisk'];
			$this->tkcount_ += $row['knb'];
			$this->tkisk_ += $row['kisk'];
		}


		$sql = 'SELECT count(distinct kll_id) AS lnb, scl_id, scl_class,';
		$sql .= ' sum(kll_isk_loss) AS lisk FROM kb3_kills kll
                    INNER JOIN kb3_ships shp ON ( shp.shp_id = kll.kll_ship_id )';
		$sql .= ' INNER JOIN kb3_ship_classes scl ON ( scl.scl_id = shp.shp_class )';

		$sqlop = ' WHERE ';
		if($this->setDateFilter())
		{
			$sql.= $sqlop.$this->setDateFilter();
			$sqlop = " AND ";
		}

		if ($this->inv_plt_)
		{
			$sql .= $sqlop.' kll.kll_victim_id IN ( '.implode(',', $this->inv_plt_).' ) ';
			$sql .= ' AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind_kll_id AND ind.ind_plt_id NOT IN ( '.implode(',', $this->inv_plt_).' ) limit 0,1) ';
		}
		elseif ($this->inv_crp_)
		{
			$sql .= $sqlop.' kll.kll_crp_id IN ( '.implode(',', $this->inv_crp_).' ) ';
			$sql .= 'AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind_kll_id AND ind.ind_crp_id NOT IN ( '.implode(',', $this->inv_crp_).' ) limit 0,1) ';
		}
		elseif ($this->inv_all_)
		{
			$sql .= $sqlop.' kll.kll_all_id IN ( '.implode(',', $this->inv_all_).' ) ';
			$sql .= 'AND EXISTS (SELECT 1 FROM kb3_inv_detail ind WHERE kll.kll_id = ind_kll_id AND ind.ind_all_id NOT IN ( '.implode(',', $this->inv_all_).' ) limit 0,1) ';
		}
		$sql .= 'GROUP BY scl_class order by scl_class';

		$qry = new DBQuery();
		$qry->execute($sql);
		while ($row = $qry->getRow())
		{
			$this->entry_[$row['scl_class']]['losses'] = $row['lnb'];
			$this->entry_[$row['scl_class']]['losses_isk'] =  $row['lisk'];

			$this->tlcount_ += $row['lnb'];
			$this->tlisk_ += $row['lisk'];
		}
	}

	function generate()
	{
		if ($this->klist_)
		{
			$entry = array();
			// build array
			$sql = "select scl_id, scl_class
                    from kb3_ship_classes
                   where scl_class not in ( 'Drone', 'Unknown' )
                  order by scl_class";

			$qry = new DBQuery();
			$qry->execute($sql) or die($qry->getErrorMsg());
			while ($row = $qry->getRow())
			{
				if (!$row['scl_id'])
					continue;

				$shipclass = new ShipClass($row['scl_id']);
				$shipclass->setName($row['scl_class']);

				$entry[$shipclass->getName()]['id'] = $row['scl_id'];
				$entry[$shipclass->getName()]['kills'] = 0;
				$entry[$shipclass->getName()]['kills_isk'] = 0;
				$entry[$shipclass->getName()]['losses'] = 0;
				$entry[$shipclass->getName()]['losses_isk'] = 0;
			}
			// kills
			while ($kill = $this->klist_->getKill())
			{
				$classname = $kill->getVictimShipClassName();
				$entry[$classname]['kills']++;
				$entry[$classname]['kills_isk'] += $kill->getISKLoss();
				$this->tkcount_++;
				$this->tkisk_ += $kill->getISKLoss();
			}
			// losses
			while ($kill = $this->llist_->getKill())
			{
				$classname = $kill->getVictimShipClassName();
				$entry[$classname]['losses']++;
				$entry[$classname]['losses_isk'] += $kill->getISKLoss();
				$this->tlcount_++;
				$this->tlisk_ += $kill->getISKLoss();
			}
		}
		else
		{
			$this->getkills();
			$entry = &$this->entry_;
		}

		$odd = false;
		$prevdate = "";
		// Don't count noobships.
		$num = count($entry) - 1;
		if($this->break_) $columns = ceil($num/$this->break_);
		else $columns = 2;
		if(!$columns) $columns = 1;
        $width_mod = 1/$columns;
        $width = round($width_mod*100);
		if($this->verbose_) $width_abs = round($width_mod*(760-240*$columns));
		else $width_abs = round($width_mod*(760-60*$columns));
		$summary = array();
		$count = 0;
		foreach ($entry as $k => $v)
		{
			if($v['id'] == 3) continue;
			if($count && $this->break_ && $count%$this->break_ == 0) $v['break'] = 1;
			else $v['break'] = 0;
			if($_GET['scl_id'] && $_GET['scl_id'] == $v['id']) $v['hl'] = 1;
			else $v['hl'] = 0;
			$qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", $_SERVER['QUERY_STRING']);
			$qrystring = preg_replace("/&page=([0-9]?[0-9])/", "", $qrystring);
			$qrystring = preg_replace("/&/", "&amp;", $qrystring);
			if ($this->view_)
			{
				$qrystring .= '&view='.$this->view_;
			}
			$v['qry'] = $qrystring;
			$v['kisk'] = round($v['kills_isk']/1000000, 2);
			$v['lisk'] = round($v['losses_isk']/1000000, 2);
			$v['name'] = $k;

			$summary[] = $v;
			$count++;

		}
		global $smarty;
		$smarty->assign('summary', $summary);
		$smarty->assign('count', count($entry));
		$smarty->assign('break', $this->break_);
		$smarty->assign('width', $width);
		$smarty->assign('width_abs', $width_abs);
		$smarty->assign('columns', $columns);
		$smarty->assign('verbose', $this->verbose_);
		$smarty->assign('filter', $this->filter_);
		$smarty->assign('losses', 1);

		if (config::get('summarytable_summary'))
		{
			$smarty->assign('summarysummary', 1);
			if (config::get('summarytable_efficiency'))
				$smarty->assign('efficiency', round($this->tkisk_ / (($this->tkisk_ + $this->tlisk_) == 0 ? 1 : ($this->tkisk_ + $this->tlisk_)) * 100, 2));
			else $smarty->assign('efficiency', 0);
			$smarty->assign('kiskB', round($this->tkisk_/1000000000, 2));
			$smarty->assign('liskB', round($this->tlisk_/1000000000, 2));
			$smarty->assign('kiskM', round($this->tkisk_/1000000, 2));
			$smarty->assign('liskM', round($this->tlisk_/1000000, 2));
			$smarty->assign('kcount', $this->tkcount_);
			$smarty->assign('lcount', $this->tlcount_);
		}

		if ($_GET['scl_id'] != "")
		{
			$qrystring = preg_replace("/&scl_id=([0-9]?[0-9])/", "", '?'.$_SERVER['QUERY_STRING']);
			$qrystring = preg_replace("/&/", "&amp;", $qrystring);
			$smarty->assign('clearfilter',$qrystring);
		}

		$html .= $smarty->fetch(get_tpl('summarytable'));

		return $html;
	}
}
?>