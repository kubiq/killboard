<?php
require_once('common/includes/class.item.php');
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.ship.php');
require_once('common/includes/class.system.php');
require_once('common/includes/class.pilot.php');
//require_once('common/includes/class.killlist.php');
require_once('common/includes/class.itemlist.php');
require_once('common/includes/class.cache.php');
require_once('common/includes/class.summary.php');

class Kill
{
	function Kill($id = 0, $external = false)
	{
		if($id && $external)
		{
			$qry = new DBQuery();
			$qry->execute("SELECT kll_id FROM kb3_kills WHERE kll_external_id = ".$id);
			if($qry->recordCount())
			{
				$result = $qry->getRow();
				$this->id_ = $result['kll_id'];
				$this->externalid_ = $id;
			}
			else
			{
				$this->id_ = 0;
				$this->external_id = 0;
			}
		}
		else
		{
			$this->id_ = $id;
			$this->externalid_ = 0;
		}
		$this->involvedparties_ = array();
		$this->destroyeditems_ = array();
		$this->droppeditems_ = array();
		$this->fullinvolved_ = false;
	}

	function set($var, $value)
	{
		$this->$var = $value;
	}
	//! Get the internal ID of this kill.

	//! \return integer value for the internal kill ID.
	function getID()
	{
		return $this->id_;
	}

	//! Get the external ID of this kill.

	//! \return integer value for the external kill ID.
	function getExternalID()
	{
		if($this->externalid_) return $this->externalid_;
		$this->execQuery();
		return $this->externalid_;
	}

	function getTimeStamp()
	{
		$this->execQuery();
		return $this->timestamp_;
	}

	function getVictimName()
	{
		$this->execQuery();
		return $this->victimname_;
	}

	function getVictimID()
	{
		$this->execQuery();
		return $this->victimid_;
	}

	function getVictimPortrait($size = 32)
	{
		$this->execQuery();
		$plt = new Pilot($this->victimid_);
		return $plt->getPortraitURL($size);
	}

	function getVictimCorpID()
	{
		$this->execQuery();
		return $this->victimcorpid_;
	}

	function getVictimCorpName()
	{
		$this->execQuery();
		return $this->victimcorpname_;
	}

	function getVictimAllianceName()
	{
		$this->execQuery();
		return $this->victimalliancename_;
	}

	function getVictimFactionName()
	{
        $this->execQuery();
        if ($this->getIsVictimFaction())
		{
			return $this->victimalliancename_;
		}
		else
		{
			return "NONE";
		}
	}

	function getVictimAllianceID()
	{
		$this->execQuery();
		return $this->victimallianceid_;
	}

	function getVictimShip()
	{
		$this->execQuery();
		return $this->victimship_;
	}

	function getSystem()
	{
		$this->execQuery();
		return $this->solarsystem_;
	}

	function getFBPilotID()
	{
		$this->execQuery();
		if (!$this->fbpilotid_) return "null";
		else return $this->fbpilotid_;
	}

	function getFBPilotName()
	{
		$this->execQuery();
		return $this->fbpilotname_;
	}

	function getFBCorpID()
	{
		$this->execQuery();
		if (!$this->fbcorpid_) return "null";
		else return $this->fbcorpid_;
	}

	function getFBCorpName()
	{
		$this->execQuery();
		return $this->fbcorpname_;
	}

	function getFBAllianceID()
	{
		$this->execQuery();
		if (!$this->fballianceid_) return "null";
		else return $this->fballianceid_;
	}

	function getFBAllianceName()
	{
		$this->execQuery();
		return $this->fballiancename_;
	}

	function getISKLoss()
	{
		$this->execQuery();
		return $this->iskloss_;
	}
	function getKillPoints()
	{
		$this->execQuery();
		return $this->killpoints_;
	}

	function getSolarSystemName()
	{
		return $this->solarsystemname_;
	}

	function getSolarSystemSecurity()
	{
		return $this->solarsystemsecurity_;
	}

	function getVictimShipName()
	{
		return $this->victimshipname_;
	}

	function getVictimShipExternalID()
	{
		return $this->victimshipexternalid_;
	}

	function getVictimShipClassName()
	{
		return $this->victimshipclassname_;
	}

	function getVictimShipValue()
	{
		return $this->victimshipvalue_;
	}

	function getVictimShipImage($size)
	{
		return IMG_URL."/ships/".$size."_".$size."/".$this->victimshipexternalid_.".png";
	}

	function getVictimShipValueIndicator()
	{
	// value is now raw
		$value = $this->getVictimShipValue()/1000000;

		if ($value >= 0 && $value <= 1)
			$color = 'gray';
		elseif ($value > 1 && $value <= 15)
			$color = 'blue';
		elseif ($value > 15 && $value <= 25)
			$color = 'green';
		elseif ($value > 25 && $value <= 40)
			$color = 'yellow';
		elseif ($value > 40 && $value <= 80)
			$color = 'red';
		elseif ($value > 80 && $value <= 250)
			$color = 'orange';
		elseif ($value > 250)
			$color = 'purple';

		return IMG_URL.'/ships/ship-'.$color.'.gif';
	}
	//! Check if the victim is in a Faction.
	function getIsVictimFaction()
	{
        $this->execQuery();
		$factions = array("Amarr Empire", "Minmatar Republic", "Caldari State", "Gallente Federation");

		return (in_array($this->victimalliancename_, $factions));
	}


	function getRawMail()
	{
		if (config::get('km_cache_enabled') && file_exists(config::get('km_cache_dir')."/".$this->getID().".txt"))
		{
			return file_get_contents(config::get('km_cache_dir')."/".$this->getID().".txt");
		}

		$this->execQuery();
		if ($this->isClassified())
		{
			return 'Killmail not yet available, try again in '.round($this->getClassifiedTime()/3600, 2).' hrs.';
		}

		$ship = $this->getVictimShip();
		$shipclass = $ship->getClass();

		$mail .= substr(str_replace('-', '.' , $this->getTimeStamp()), 0, 16)."\r\n\r\n";
		if ( $shipclass->getID() == 35 ) // Starbase (so this is a POS mail)
		{
			$mail .= "Corp: ".$this->getVictimCorpName()."\r\n";
			if($this->getIsVictimFaction()) $mail .= "Alliance: NONE\r\n";
			else $mail .= "Alliance: ".$this->getVictimAllianceName()."\r\n";
			$mail .= "Faction: ".$this->getVictimFactionName()."\r\n";
			//$ship = $this->getVictimShip();
			$mail .= "Destroyed: ".$ship->getName()."\r\n";
			$mail .= "Moon: ".$this->getVictimName()."\r\n";
			$system = $this->getSystem();
			$mail .= "System: ".$system->getName()."\r\n";
			$mail .= "Security: ".$system->getSecurity(true)."\r\n";
			$mail .= "Damage Taken: ".$this->VictimDamageTaken."\r\n\r\n";
			$mail .= "Involved parties:\r\n\r\n";
		}
		else
		{
			$mail .= "Victim: ".$this->getVictimName()."\r\n";
			$mail .= "Corp: ".$this->getVictimCorpName()."\r\n";
			if($this->getIsVictimFaction()) $mail .= "Alliance: NONE\r\n";
			else $mail .= "Alliance: ".$this->getVictimAllianceName()."\r\n";
			$mail .= "Faction: ".$this->getVictimFactionName()."\r\n";
			//$ship = $this->getVictimShip();
			$mail .= "Destroyed: ".$ship->getName()."\r\n";
			$system = $this->getSystem();
			$mail .= "System: ".$system->getName()."\r\n";
			$mail .= "Security: ".$system->getSecurity(true)."\r\n";
			$mail .= "Damage Taken: ".$this->VictimDamageTaken."\r\n\r\n";
			$mail .= "Involved parties:\r\n\r\n";
		}

		foreach ($this->involvedparties_ as $inv)
		{
			$pilot = new Pilot($inv->getPilotID());
			$corp = new Corporation($inv->getCorpID());
			$alliance = new Alliance($inv->getAllianceID());

			$weapon = $inv->getWeapon();
			$ship = $inv->getShip();
			if ($pilot->getName() == $weapon->getName())
			{
				$name = $pilot->getName()." / ".$corp->getName();
			}
			else
			{
				$name = $pilot->getName();
			}

			$mail .= "Name: ".$name;
			if ($pilot->getID() == $this->getFBPilotID())
			{
				$mail .= " (laid the final blow)";
			}
			$mail .= "\r\n";

			if ($pilot->getName() != $weapon->getName())
			{
				$mail .= "Security: ".$inv->getSecStatus()."\r\n";
				$mail .= "Corp: ".$corp->getName()."\r\n";
				if ($alliance->isFaction())
				{
					$mail .= "Alliance: NONE\r\n";
					$mail .= "Faction: ".$alliance->getName()."\r\n";
				}
				else
				{
					$mail .= "Alliance: ".$alliance->getName()."\r\n";
					$mail .= "Faction: NONE\r\n";
				}
				$mail .= "Ship: ".$ship->getName()."\r\n";
				$mail .= "Weapon: ".$weapon->getName()."\r\n";
				$mail .= "Damage Done: ".$inv->dmgdone_."\r\n";
			}
			else
			{
				$mail .= "Damage Done: ".$inv->dmgdone_."\r\n";
			}
			$mail .= "\r\n";
		}

		if (count($this->destroyeditems_) > 0)
		{
			$mail .= "\r\nDestroyed items:\r\n\r\n";

			foreach($this->destroyeditems_ as $destroyed)
			{
				$item = $destroyed->getItem();
				$mail .= $item->getName();
				if ($destroyed->getQuantity() > 1)
				{
				// if the option is enabled and the item is fitted then split it up
				// this is aworkaround for the lazy parser of griefwatch
					if (config::get('kill_splitfit') && $destroyed->getQuantity() < 9 && ($destroyed->getLocationID() == 1
						|| $destroyed->getLocationID() == 2 || $destroyed->getLocationID() == 3))
					{
						for ($i = $destroyed->getQuantity(); $i > 1; $i--)
						{
							$mail .= "\r\n".$item->getName();
						}

					}
					else
					{
						$mail .= ", Qty: ".$destroyed->getQuantity();
					}
				}
				if ($destroyed->getLocationID() == 4) // cargo
					$mail .= " (Cargo)";
				if ($destroyed->getLocationID() == 6) // drone
					$mail .= " (Drone Bay)";
				$mail .= "\r\n";
			}
		}

		if (count($this->droppeditems_) > 0)
		{
			$mail .= "\r\nDropped items:\r\n\r\n";

			foreach($this->droppeditems_ as $dropped)
			{
				$item = $dropped->getItem();
				$mail .= $item->getName();
				if ($dropped->getQuantity() > 1)
				{
				// if the option is enabled and the item is fitted then split it up
				// this is aworkaround for the lazy parser of griefwatch
					if (config::get('kill_splitfit') && $dropped->getQuantity() < 9 && ($dropped->getLocationID() == 1
						|| $dropped->getLocationID() == 2 || $dropped->getLocationID() == 3))
					{
						for ($i = $dropped->getQuantity(); $i > 1; $i--)
						{
							$mail .= "\r\n".$item->getName();
						}

					}
					else
					{
						$mail .= ", Qty: ".$dropped->getQuantity();
					}
				}
				if ($dropped->getLocationID() == 4) // cargo
					$mail .= " (Cargo)";
				if ($dropped->getLocationID() == 6) // drone
					$mail .= " (Drone Bay)";
				$mail .= "\r\n";
			}
		}

		if (config::get('km_cache_enabled')) file_put_contents(config::get('km_cache_dir')."/".$this->getID().".txt", $mail);

		return $mail;
	}

	function getDupe($checkonly = false)
	{
		if (!$checkonly)
		{
			$this->execQuery();
		}
		$dupe = 0;
		$qry = new DBQuery(true);
		if (!$this->getFBPilotID() || !$this->victimid_)
			return 0;
		$qry->execute("select kll_id
                    from kb3_kills
                    where kll_timestamp ='".$this->timestamp_."'
                    and kll_victim_id = ".$this->victimid_."
                    and kll_ship_id = ".$this->victimship_->getID()."
                    and kll_system_id = ".$this->solarsystem_->getID()."
                    and kll_fb_plt_id = ".$this->getFBPilotID()."
                    and kll_id != ".$this->id_);

		$row = $qry->getRow();
		if ($row)
			return $row['kll_id'];
		else
			return 0;
	}

	function execQuery()
	{
		if (!$this->timestamp_)
		{
			$qry = new DBQuery();

			$this->qry_ = new DBQuery();
			$this->sql_ = "select kll.kll_id, kll.kll_timestamp, plt.plt_name,
                          crp.crp_name, ali.all_name, ali.all_id, kll.kll_ship_id,
                          kll.kll_system_id, kll.kll_ship_id, kll.kll_external_id,
                          kll.kll_victim_id, plt.plt_externalid, kll.kll_isk_loss,
                          kll.kll_crp_id, kll.kll_points, 
                          fbplt.plt_id as fbplt_id,
                          fbplt.plt_externalid as fbplt_externalid,
                          fbcrp.crp_id as fbcrp_id,
                          fbali.all_id as fbali_id,
                          fbplt.plt_name as fbplt_name,
                          fbcrp.crp_name as fbcrp_name,
                          fbali.all_name as fbali_name,
                          kll_dmgtaken
                     from kb3_kills kll, kb3_pilots plt, kb3_corps crp,
                          kb3_alliances ali, kb3_alliances fbali, kb3_corps fbcrp,
                          kb3_pilots fbplt, kb3_inv_detail fb
                    where kll.kll_id = '".$this->id_."'
                      and plt.plt_id = kll.kll_victim_id
                      and crp.crp_id = kll.kll_crp_id
                      and ali.all_id = kll.kll_all_id
					  and fb.ind_kll_id = kll.kll_id
					  and fb.ind_plt_id = kll.kll_fb_plt_id
                      and fbali.all_id = fb.ind_all_id
                      and fbcrp.crp_id = fb.ind_crp_id
                      and fbplt.plt_id = kll.kll_fb_plt_id";

			$this->qry_->execute($this->sql_);
			$row = $this->qry_->getRow();
			if (!$row)
			{
				$this->valid_ = false;
				return false;
			}
			else
			{
				$this->valid_ = true;
			}

			$this->setTimeStamp($row['kll_timestamp']);
			$this->setSolarSystem(new SolarSystem($row['kll_system_id']));
			$this->setVictimID($row['kll_victim_id']);
			$this->setVictimName($row['plt_name']);
			$this->setVictimCorpID($row['kll_crp_id']);
			$this->setVictimCorpName($row['crp_name']);
			$this->setVictimAllianceID($row['all_id']);
			$this->setVictimAllianceName($row['all_name']);
			$this->setVictimShip(new Ship($row['kll_ship_id']));
			$this->setFBPilotID($row['fbplt_id']);
			$this->setFBPilotName($row['fbplt_name']);
			$this->setFBCorpID($row['fbcrp_id']);
			$this->setFBCorpName($row['fbcrp_name']);
			$this->setFBAllianceID($row['fbali_id']);
			$this->setFBAllianceName($row['fbali_name']);
			$this->setKillPoints($row['kll_points']);
			$this->setExternalID($row['kll_external_id']);
			$this->setISKLoss($row['kll_isk_loss']);
			$this->plt_ext_ = $row['plt_externalid'];
			$this->fbplt_ext_ = $row['fbplt_externalid'];
			$this->VictimDamageTaken = $row['kll_dmgtaken'];

			// involved
			if($this->fullinvolved_)
			{
				$sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status,
					ind_shp_id, ind_wep_id, typeName, ind_dmgdone,
					shp_id, shp_name, shp_externalid, shp_class, scl_class,
					plt_name, plt_externalid, crp_name, all_name
					from kb3_inv_detail
					join kb3_pilots on ind_plt_id = plt_id
					join kb3_corps on ind_crp_id = crp_id
					join kb3_alliances on ind_all_id = all_id
					join kb3_ships on ind_shp_id = shp_id
					join kb3_ship_classes on shp_class = scl_id
					join kb3_invtypes on ind_wep_id = typeID
					where ind_kll_id = ".$this->getID()."
					order by ind_order";

				$qry->execute($sql) or die($qry->getErrorMsg());
				while ($row = $qry->getRow())
				{
					$pilot = new Pilot($row['ind_plt_id']);
					$pilot->externalid_ = $row['plt_externalid'];
					$pilot->name_ = $row['plt_name'];
					$pilot->corp_ = $row['ind_crp_id'];

					$corp = new Corporation($row['ind_crp_id']);
					$corp->name_ = $row['crp_name'];
					$corp->alliance_ = $row['ind_all_id'];

					$alliance = new Alliance($row['ind_all_id']);
					$alliance->name_ = $row['all_name'];

					$ship = new Ship($row['shp_id']);
					$ship->externalid_ = $row['shp_externalid'];
					$ship->shipname_ = $row['shp_name'];

					$ship->shipclass_ = new ShipClass($row['shp_class']);
					$ship->shipclass_->name_ = $row['scl_class'];
					
					$weapon = new Item($row['ind_wep_id']);
					$weapon->row_['typeName'] = $row['typeName'];
					$weapon->row_['typeID'] = $row['ind_wep_id'];
					$weapon->row_['itm_externalid'] = $row['ind_wep_id'];
					
					$involved = new DetailedInv($pilot,
						$row['ind_sec_status'],
						$corp,
						$alliance,
						$ship,
						$weapon);
					$involved->dmgdone_ = $row['ind_dmgdone'];
					array_push($this->involvedparties_, $involved);
				}
			}
			else
			{
				$sql = "select ind_plt_id, ind_crp_id, ind_all_id, ind_sec_status,
					ind_shp_id, ind_wep_id, ind_dmgdone
					from kb3_inv_detail
					where ind_kll_id = ".$this->getID()."
					order by ind_order";

				$qry->execute($sql) or die($qry->getErrorMsg());
				while ($row = $qry->getRow())
				{
					$involved = new InvolvedParty($row['ind_plt_id'],
						$row['ind_crp_id'],
						$row['ind_all_id'],
						$row['ind_sec_status'],
						new Ship($row['ind_shp_id']),
						new Item($row['ind_wep_id']));
					$involved->dmgdone_ = $row['ind_dmgdone'];
					array_push($this->involvedparties_, $involved);
				}
			}
			$destroyedlist = new ItemList(null, true);
			$destroyedlist->addKillDestroyed($this->id_);
			while($item = $destroyedlist->getItem())
			{
				$destroyed = new DestroyedItem($item,
					$item->row_['itd_quantity'],
					$item->row_['itl_location'],
					$item->row_['itd_itl_id']);
				array_push($this->destroyeditems_, $destroyed);
			}
			$droppedlist = new ItemList(null, true);
			$droppedlist->addKillDropped($this->id_);
			while($item = $droppedlist->getItem())
			{
				$dropped = new DroppedItem($item,
					$item->row_['itd_quantity'],
					$item->row_['itl_location'],
					$item->row_['itd_itl_id']);
				array_push($this->droppeditems_, $dropped);
			}
		}
	}

	function isClassified()
	{
		if(!$this->timestamp_) $this->execQuery();
		if (config::get('kill_classified'))
		{
			if (user::role('classified_see'))
			{
				return false;
			}

			$offset = config::get('kill_classified')*3600;
			if (config::get('date_gmtime'))
			{
				$time = time()-date('Z');
			}
			else
			{
				$time = time();
			}
			if (strtotime($this->timestamp_) > $time-$offset)
			{
				return true;
			}
		}
		return false;
	}

	function getClassifiedTime()
	{
		if (config::get('kill_classified'))
		{
			$offset = config::get('kill_classified')*3600;
			if (config::get('date_gmtime'))
			{
				$time = time()-date('Z');
			}
			else
			{
				$time = time();
			}
			if (strtotime($this->timestamp_) > $time-$offset)
			{
				return ($offset-$time+strtotime($this->timestamp_));
			}
		}
		return 0;
	}

	function getInvolvedPartyCount()
	{
		if(isset($this->involvedcount_)) return $this->involvedcount_;
		$qry = new DBQuery();
		$qry->execute("select max(ind_order)+1 inv from kb3_inv_detail where ind_kll_id = ". $this->id_);
		$result = $qry->getRow();
		$this->involvedcount_ = $result['inv'];
		return $result['inv'];
	}

	// Set the number of involved parties - used by killlist
	function setInvolvedPartyCount($invcount = 0)
	{
		$this->involvedcount_ = $invcount;
	}

	function setDetailedInvolved()
	{
		$this->fullinvolved_ = true;
	}
	function exists()
	{
		$this->execQuery();
		return $this->valid_;
	}

	//Count all kills by board owner related to this kill
	function relatedKillCount()
	{
		// No details for classified kills.
		if($this->isClassified()) return 0;
		if($this->relatedkillcount_) return $this->relatedkillcount_;
		$sql="SELECT COUNT(kll.kll_id) AS kills FROM kb3_kills kll WHERE ";
		$sql.="kll.kll_timestamp <= '".(date('Y-m-d H:i:s',strtotime($this->timestamp_) + 60 * 60))."'
                                                   AND kll.kll_timestamp >=
                                                           '".(date('Y-m-d H:i:s',strtotime($this->timestamp_) - 60 * 60))."'
                                                   AND kll.kll_system_id = ".$this->solarsystem_->getID();
		if(ALLIANCE_ID <>0)
		{
			$sql .=" AND EXISTS (SELECT 1 FROM kb3_inv_detail ind".
				" WHERE ( ind.ind_all_id = ".ALLIANCE_ID." AND kll.kll_id = ind.ind_kll_id".
				" AND ind.ind_all_id != kll.kll_all_id ) LIMIT 1)";
		}
		else if(CORP_ID <>0)
		{
			$sql .=" AND EXISTS (SELECT 1 FROM kb3_inv_detail".
				" WHERE ( ind_crp_id = ".CORP_ID."  AND kll.kll_id = ind_kll_id".
				" AND ind_crp_id != kll.kll_crp_id ) LIMIT 1) ";
		}
		else if(PILOT_ID <>0)
		{
			$sql .=" AND EXISTS (SELECT 1 FROM kb3_inv_detail".
				" WHERE ( ind_plt_id = ".PILOT_ID."  AND kll.kll_id = ind_kll_id".
				" AND ind_plt_id != kll.kll_victim_id ) LIMIT 1) ";
		}
		$sql .= "/* related kill count */";
		$qry = new DBQuery();
		if(!$qry->execute($sql)) return 0;
		$res=$qry->getRow();
		$this->relatedkillcount_ = $res['kills'];
		return $this->relatedkillcount_;
	}

	//Count all losses by board owner related to this kill
	function relatedLossCount()
	{
		// No details for classified kills.
		if($this->isClassified()) return 0;
		if($this->relatedlosscount_) return $this->relatedlosscount_;
		$sql="SELECT count(kll.kll_id) as losses FROM kb3_kills kll ";
		$sql.="WHERE kll.kll_system_id = ".$this->solarsystem_->getID().
			" AND kll.kll_timestamp <= '".
				(date('Y-m-d H:i:s',strtotime($this->timestamp_) + 60 * 60)).
			"' AND kll.kll_timestamp >= '".
				(date('Y-m-d H:i:s',strtotime($this->timestamp_) - 60 * 60))."'";
		if(ALLIANCE_ID <>0)
		{
			$sql .="AND kll.kll_all_id = ".ALLIANCE_ID.
				" AND EXISTS (SELECT 1 FROM kb3_inv_detail ind".
				" WHERE ( kll.kll_id = ind.ind_kll_id".
				" AND ind.ind_all_id != ".ALLIANCE_ID." ) ) ";
		}
		else if(CORP_ID <>0)
		{
			$sql .="AND kll_crp_id = ".CORP_ID."  AND".
				" EXISTS (SELECT 1 FROM kb3_inv_detail".
				" WHERE ( kll.kll_id = ind_kll_id".
				" AND ind_crp_id != ".CORP_ID." ) ) ";
		}
		else if(PILOT_ID <>0)
		{
			$sql .="AND kll_victim_id = ".PILOT_ID." AND".
				" EXISTS (SELECT 1 FROM kb3_inv_detail".
				" WHERE ( kll.kll_id = ind_kll_id".
				" AND ind_plt_id != ".PILOT_ID." ) ) ";
		}
		$sql .= "/* related loss count */";
		$qry = new DBQuery();
		if(!$qry->execute($sql)) return 0;
		$res=$qry->getRow();
		$this->relatedlosscount_ = $res['losses'];
		return $this->relatedlosscount_;
	}

	function countComment($kll_id)
	{
		if(isset($this->commentcount_)) return $this->commentcount_;
		$qry = new DBQuery();
		$sql = "SELECT count(id) as comments FROM kb3_comments WHERE kll_id = '$kll_id'";
		// return 0 if query fails. May be incorrect but is harmless here
		if(!$qry->execute($sql)) return 0;
		$result = $qry->getRow();
		$this->commentcount_ = $result['comments'];
		return $result['comments'];
	}

	// Set the number of comments - used by killlist
	function setCommentCount($comcount = 0)
	{
		$this->commentcount_ = $comcount;
	}

	function setID($id)
	{
		$this->id_ = $id;
	}

	function setTimeStamp($timestamp)
	{
		$this->timestamp_ = $timestamp;
	}

	function setSolarSystem($solarsystem)
	{
		$this->solarsystem_ = $solarsystem;
	}

	function setSolarSystemName($solarsystemname)
	{
		$this->solarsystemname_ = $solarsystemname;
	}

	function setSolarSystemSecurity($solarsystemsecurity)
	{
		$this->solarsystemsecurity_ = $solarsystemsecurity;
	}

	function setExternalID($externalid)
	{
		if($externalid) $this->externalid_ = $externalid;
		else $this->externalid_ = 0;
	}
	
	function setVictim($victim)
	{
		$this->victim_ = $victim;
	}

	function setVictimID($victimid)
	{
		$this->victimid_ = $victimid;
	}

	function setVictimName($victimname)
	{
		$this->victimname_ = $victimname;
	}

	function setVictimCorpID($victimcorpid)
	{
		$this->victimcorpid_ = $victimcorpid;
	}

	function setVictimCorpName($victimcorpname)
	{
		$this->victimcorpname_ = $victimcorpname;
	}

	function setVictimAllianceID($victimallianceid)
	{
		$this->victimallianceid_ = $victimallianceid;
	}

	function setVictimAllianceName($victimalliancename)
	{
		$this->victimalliancename_ = $victimalliancename;
	}

	function setVictimShip($victimship)
	{
		$this->victimship_ = $victimship;
	}

	function setVictimShipName($victimshipname)
	{
		$this->victimshipname_ = $victimshipname;
	}

	function setVictimShipExternalID($victimshipexternalid)
	{
		$this->victimshipexternalid_ = $victimshipexternalid;
	}

	function setVictimShipClassName($victimshipclassname)
	{
		$this->victimshipclassname_ = $victimshipclassname;
	}

	function setVictimShipValue($victimshipvalue)
	{
		$this->victimshipvalue_ = $victimshipvalue;
	}

	function setFBPilotID($fbpilotid)
	{
		$this->fbpilotid_ = $fbpilotid;
	}

	function setFBPilotName($fbpilotname)
	{
		$npc = strpos($fbpilotname, "#");
		if ($npc === false)
		{
			$this->fbpilotname_ = $fbpilotname;
		}
		else
		{
			$name = explode("#", $fbpilotname);
			$plt = new Item($name[2]);
			$this->fbpilotname_ = $plt->getName();
		}
	}

	function setFBCorpID($fbcorpid)
	{
		$this->fbcorpid_ = $fbcorpid;
	}

	function setFBCorpName($fbcorpname)
	{
		$this->fbcorpname_ = $fbcorpname;
	}

	function setFBAllianceID($fballianceid)
	{
		$this->fballianceid_ = $fballianceid;
	}

	function setFBAllianceName($fballiancename)
	{
		$this->fballiancename_ = $fballiancename;
	}
	function setKillPoints($killpoints)
	{
		$this->killpoints_ = $killpoints;
	}
	//! Set the ISK loss value for this kill.
	function setISKLoss($isk)
	{
		$this->iskloss_ = $isk;
	}
	//! Calculate the current cost of a ship loss excluding blueprints.

	//! \param $update set true to update all-time summaries.
	function calculateISKLoss($update = true)
	{
		$value = 0;
		foreach($this->destroyeditems_ as $itd)
		{
			$item = $itd->getItem();
			if(strpos($item->getName(), "Blueprint") === FALSE) $value += $itd->getValue() * $itd->getQuantity();
		}
		if(config::get('kd_droptototal'))
		{
			foreach($this->droppeditems_ as $itd)
			{
				$item = $itd->getItem();
				if(strpos($item->getName(), "Blueprint") === FALSE) $value += $itd->getValue() * $itd->getQuantity();
			}
		}
		$value += $this->victimship_->getPrice();
		if($this->iskloss_ && $update) summaryCache::update($this, $value - $this->iskloss_);
		$this->iskloss_ = $value;
		return $value;
	}

	function calculateKillPoints()
	{
		$ship = $this->getVictimShip();
		$shipclass = $ship->getClass();
		$vicpoints = $shipclass->getPoints();
		$maxpoints = round($vicpoints * 1.2);

		foreach ($this->involvedparties_ as $inv)
		{
			$shipinv = $inv->getShip();
			$shipclassinv = $shipinv->getClass();
			$invpoints += $shipclassinv->getPoints();
		}

		$gankfactor = $vicpoints / ($vicpoints + $invpoints);
		$points = ceil($vicpoints * ($gankfactor / 0.75));

		if ($points > $maxpoints) $points = $maxpoints;

		$points = round($points, 0);
		return $points;
	}

	function add($id = null)
	{
		if (!$this->solarsystem_->getID())
		{
			echo 'INTERNAL ERROR; SOLARSYSTEM NOT FOUND; PLEASE CONTACT A DEV WITH THIS MESSAGE<br/>';
			var_dump($this->solarsystem_);
			var_dump($this->solarsystemname_);
			return 0;
		}
		// If value isn't already calculated then do so now. Don't update the
		// stored value since at this point it does not exist.
		if(!is_numeric($this->iskloss_)) $this->calculateISKLoss(false);

		$dupe = $this->getDupe(true);
		if ($dupe == 0)
		{
			$this->realadd();
		}
		elseif (config::get('readd_dupes'))
		{
			$this->dupeid_ = $dupe;
			$this->id_ = $dupe;
			$this->remove(false);
			$this->realadd($dupe);
			$this->id_ = -1;
		}
		else
		{
			$this->dupeid_ = $dupe;
			$this->id_ = -1;
		}
		return $this->id_;
	}

	function realadd($id = null)
	{
		if ( $this->timestamp_ == "" || !$this->victimid_ || !$this->victimship_->getID() || !$this->solarsystem_->getID() ||
		!$this->victimallianceid_ || !$this->victimcorpid_ || !$this->getFBAllianceID() || !$this->getFBCorpID() ||
		!$this->getFBPilotID() ) return 0;
		if ($id == null)
		{
			$qid = 'null';
		}
		else
		{
			$qid = $id;
		}
		if (!$this->dmgtaken)
		{
			$this->dmgtaken = 0;
		}

		$qry = new DBQuery();
		$sql = "INSERT INTO kb3_kills
            (kll_id , kll_timestamp , kll_victim_id , kll_all_id , kll_crp_id , kll_ship_id , kll_system_id , kll_fb_plt_id , kll_points , kll_dmgtaken, kll_external_id, kll_isk_loss)
            VALUES (".$qid.",
                    date_format('".$this->timestamp_."', '%Y.%m.%d %H:%i:%s'),
            ".$this->victimid_.",
            ".$this->victimallianceid_.",
            ".$this->victimcorpid_.",
            ".$this->victimship_->getID().",
            ".$this->solarsystem_->getID().",
            ".$this->getFBPilotID().",
            ".$this->calculateKillPoints().",
            ".$this->dmgtaken.", ";
		if($this->externalid_) $sql .= $this->externalid_.", ";
		else $sql .= "NULL, ";
        $sql .= $this->getISKLoss()." )";
		$qry->autocommit(false);
		if(!$qry->execute($sql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			//If the query is causing errors here there's no point going on
			return false;
		}

		if ($id)
		{
			$this->id_ = $id;
		}
		else
		{
			$this->id_ = $qry->getInsertID();
		}
		if(!$this->id_)
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}
		// involved
		$order = 0;
		$invall = array();
		$invcrp = array();
		$invplt = array();
		$involveddsql = 'insert into kb3_inv_detail
                    (ind_kll_id, ind_plt_id, ind_sec_status, ind_all_id, ind_crp_id, ind_shp_id, ind_wep_id, ind_order, ind_dmgdone )
                    values ';
		$notfirstd = false;
		foreach ($this->involvedparties_ as $inv)
		{
			$ship = $inv->getShip();
			$weapon = $inv->getWeapon();
			if (!$inv->getPilotID() || $inv->getSecStatus() == "" || !$inv->getAllianceID() || !$inv->getCorpID() || !$ship->getID() || !$weapon->getID())
			{
				$this->remove();
				return 0;
			}

			if (!$inv->dmgdone_)
			{
				$inv->dmgdone_ = 0;
			}
			if($notfirstd) $involveddsql .= ", ";
			$involveddsql .= "( ".$this->getID().", ".$inv->getPilotID().", '".$inv->getSecStatus()."', "
				.$inv->getAllianceID().", ".$inv->getCorpID().", ".$ship->getID().", "
				.$weapon->getID().", ".$order++.", ".$inv->dmgdone_.")";
			$notfirstd = true;

		}
		if($notfirstd && !$qry->execute($involveddsql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}

		// destroyed
		$notfirstitd=false;
		$itdsql = "insert into kb3_items_destroyed (itd_kll_id, itd_itm_id, itd_quantity, itd_itl_id) values ";
		foreach ($this->destroyeditems_ as $dest)
		{
			$item = $dest->getItem();
			$loc_id = $dest->getLocationID();
			if (!is_numeric($this->getID()) || !is_numeric($item->getID()) || !is_numeric($dest->getQuantity()) || !is_numeric($loc_id))
			{
				trigger_error('error with destroyed item.', E_USER_WARNING);
				var_dump($dest);exit;
				continue;
			}

			if($notfirstitd) $itdsql .= ", ";
			$itdsql .= "( ".$this->getID().", ".$item->getID().", ".$dest->getQuantity().", ".$loc_id." )";
			$notfirstitd = true;
		}
		if($notfirstitd &&!$qry->execute($itdsql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}

		// dropped
		$notfirstitd=false;
		$itdsql = "insert into kb3_items_dropped (itd_kll_id, itd_itm_id, itd_quantity, itd_itl_id) values ";
		foreach ($this->droppeditems_ as $dest)
		{
			$item = $dest->getItem();
			$loc_id = $dest->getLocationID();
			if (!is_numeric($this->getID()) || !is_numeric($item->getID()) || !is_numeric($dest->getQuantity()) || !is_numeric($loc_id))
			{
				trigger_error('error with dropped item.', E_USER_WARNING);
				var_dump($dest);exit;
				continue;
			}

			if($notfirstitd) $itdsql .= ", ";
			$itdsql .= "( ".$this->getID().", ".$item->getID().", ".$dest->getQuantity().", ".$loc_id." )";
			$notfirstitd = true;
		}
		if($notfirstitd &&!$qry->execute($itdsql))
		{
			$qry->rollback();
			$qry->autocommit(true);
			return false;
		}
		//Update cache tables.
		summaryCache::addKill($this);
		$qry->autocommit(true);
		// call the event that we added this mail
		event::call('killmail_added', $this);
		cache::notifyKillAdded();
		return $this->id_;
	}

	function remove($delcomments = true)
	{
		if (!$this->id_)
			return;
		$qry = new DBQuery();
		$qry->autocommit(false);
		summaryCache::delKill($this);

		event::call('killmail_delete', $this);

		$qry->execute("delete from kb3_kills where kll_id = ".$this->id_);
		$qry->execute("delete from kb3_inv_detail where ind_kll_id = ".$this->id_);
//		$qry->execute("delete from kb3_inv_all where ina_kll_id = ".$this->id_);
//		$qry->execute("delete from kb3_inv_crp where inc_kll_id = ".$this->id_);
//		$qry->execute("delete from kb3_inv_plt where inp_kll_id = ".$this->id_);
		$qry->execute("delete from kb3_items_destroyed where itd_kll_id = ".$this->id_);
		$qry->execute("delete from kb3_items_dropped where itd_kll_id = ".$this->id_);
		// Don't remove comments when readding a kill
		if ($delcomments)
		{
			$qry->execute("delete from kb3_comments where kll_id = ".$this->id_);
		}
		$qry->autocommit(true);
	}

	function addInvolvedParty($involved)
	{
		array_push($this->involvedparties_, $involved);
	}

	function addDestroyedItem($destroyed)
	{
		array_push($this->destroyeditems_, $destroyed);
	}

	function addDroppedItem($dropped)
	{
		array_push($this->droppeditems_, $dropped);
	}
}

class InvolvedParty
{
	function InvolvedParty($pilotid, $corpid, $allianceid, $secstatus, $ship, $weapon)
	{
		$this->pilotid_ = $pilotid;
		$this->corpid_ = $corpid;
		$this->allianceid_ = $allianceid;
		$this->secstatus_ = $secstatus;
		$this->ship_ = $ship;
		$this->weapon_ = $weapon;
	}

	function getPilotID()
	{
		return $this->pilotid_;
	}

	function getCorpID()
	{
		return $this->corpid_;
	}

	function getAllianceID()
	{
		return $this->allianceid_;
	}

	function getSecStatus()
	{
		return $this->secstatus_;
	}

	function getShip()
	{
		return $this->ship_;
	}

	function getWeapon()
	{
		return $this->weapon_;
	}
}

class DestroyedItem
{
	function DestroyedItem($item, $quantity, $location, $locationID = null)
	{
		$this->item_ = $item;
		$this->quantity_ = $quantity;
		$this->location_ = $location;
		$this->locationID_ = $locationID;
	}

	function getItem()
	{
		return $this->item_;
	}

	function getQuantity()
	{
		if ($this->quantity_ == "") $this->quantity = 1;
		return $this->quantity_;
	}
	//! Deprecated function to support old mods.
	function getFormatttedValue()
	{
		return $this->getFormattedValue();
	}
	//! Return value formatted into millions or thousands.
	function getFormattedValue()
	{
		if (!isset($this->value))
		{
			$this->getValue();
		}
		if ($this->value > 0)
		{
			$value = $this->value * $this->getQuantity();
			// Value Manipulation for prettyness.
			if (strlen($value) > 6) // Is this value in the millions?
			{
				$formatted = round($value / 1000000, 2);
				$formatted = number_format($formatted, 2);
				$formatted = $formatted." M";
			}
			elseif (strlen($value) > 3) // 1000's ?
			{
				$formatted = round($value / 1000, 2);

				$formatted = number_format($formatted, 2);
				$formatted = $formatted." K";
			}
			else
			{
				$formatted = number_format($value, 2);
				$formatted = $formatted." isk";
			}
		}
		else
		{
			$formatted = "0 isk";
		}
		return $formatted;
	}

	function getValue()
	{
		if ($this->value)
		{
			return $this->value;
		}
		if ($this->item_->row_['itm_value'])
		{
			$this->value = $this->item_->row_['itm_value'];
			return $this->item_->row_['itm_value'];
		}
		elseif ($this->item_->row_['baseprice'])
		{
			$this->value = $this->item_->row_['baseprice'];
			return $this->item_->row_['baseprice'];
		}
		$this->value = 0;
		$qry = new DBQuery();
		$qry->execute("select basePrice, price
					from kb3_invtypes
					left join kb3_item_price on kb3_invtypes.typeID=kb3_item_price.typeID
					where kb3_invtypes.typeID='".$this->item_->getID()."'");
		if ($row = $qry->getRow())
		{
			if ($row['price'])
			{
				$this->value = $row['price'];
			}
			else
			{
				$this->value = $row['basePrice'];
			}
		}
		return $this->value;

		//returns the value of an item
		$value = 0; 				// Set 0 value incase nothing comes back
		$id = $this->item_->getID(); // get Item ID
		$qry = new DBQuery();
		$qry->execute("select itm_value from kb3_items where itm_id= '".$id."'");
		$row = $qry->getRow();
		$value = $row['itm_value'];
		if ($value == '')
		{
			$value = 0;
		}
		return $value;
	}

	function getLocationID()
	{
		if(!is_null($this->locationID_)) return $this->locationID_;
		$id = false;
		if (strlen($this->location_) < 2)
		{
			$id = $this->item_->getSlot();
		}
		else
		{
			$qry = new DBQuery();
			$qry->execute("select itl_id from kb3_item_locations where itl_location = '".$this->location_."'");
			$row = $qry->getRow();
			$id = $row['itl_id'];
		}
		return $id;
	}
}

class DroppedItem extends DestroyedItem
{
	function DroppedItem($item, $quantity, $location, $locationID = null)
	{
		$this->item_ = $item;
		$this->quantity_ = $quantity;
		$this->location_ = $location;
		$this->locationID_ = $locationID;
	}
}

class DetailedInv extends InvolvedParty
{
	function DetailedInv($pilot, $secstatus, $corp, $alliance, $ship, $weapon)
	{
		$this->pilot_ = $pilot;
		$this->secstatus_ = $secstatus;
		$this->corp_ = $corp;
		$this->alliance_ = $alliance;
		$this->ship_ = $ship;
		$this->weapon_ = $weapon;
	}

	function getPilot()
	{
		return $this->pilot_;
	}

	function getPilotID()
	{
		return $this->pilot_->getID();
	}

	function getCorp()
	{
		return $this->corp_;
	}

	function getCorpID()
	{
		return $this->corp_->getID();
	}

	function getAlliance()
	{
		return $this->alliance_;
	}

	function getAllianceID()
	{
		return $this->alliance_->getID();
	}

}
?>
