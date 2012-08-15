<?php
require_once('common/includes/class.corp.php');
require_once('common/includes/class.alliance.php');
require_once('common/includes/class.killlist.php');
require_once('common/includes/class.killlisttable.php');
require_once('common/includes/class.killsummarytable.php');
require_once('common/includes/class.toplist.php');
require_once("common/includes/class.eveapi.php");

$all_id = intval($_GET['all_id']);
$all_external_id = intval($_GET['all_external_id']);
if (!$all_id && !$all_external_id)
{
    if (ALLIANCE_ID)
    {
        $all_id = ALLIANCE_ID;
    }
    else
    {
        echo 'no valid alliance id specified<br/>';
        return;
    }
}
$scl_id = intval($_GET['scl_id']);

if(!$all_id && $all_external_id)
{
	$qry = new DBQuery();
	$qry->execute("SELECT all_id FROM kb3_alliances WHERE all_external_id = ".$all_external_id);
	if($qry->recordCount())
	{
		$row = $qry->getRow();
		$all_id = $row['all_id'];
	}
}

$month = $_GET['m'];
$year = $_GET['y'];

if ($month == '')
    $month = kbdate('m');

if ($year == '')
    $year = kbdate('Y');

if ($month == 12)
{
    $nmonth = 1;
    $nyear = $year + 1;
}
else
{
    $nmonth = $month + 1;
    $nyear = $year;
}
if ($month == 1)
{
    $pmonth = 12;
    $pyear = $year - 1;
}
else
{
    $pmonth = $month - 1;
    $pyear = $year;
}
$monthname = kbdate("F", strtotime("2000-".$month."-2"));

$alliance = new Alliance($all_id);
$tempMyCorp = new Corporation();

$myAlliName = $alliance->getName();

$myAlliAPI = new AllianceAPI();
$myAlliAPI->fetchalliances();
$myAlliAPI->UpdateAlliances();

$myAlliance = $myAlliAPI->LocateAlliance( $myAlliName );

$myCorpAPI = new API_CorporationSheet();

if ($myAlliance)
{
	if($alliance->isFaction()) $page = new Page('Faction details - '.$alliance->getName() . " [" . $myAlliance["shortName"] . "]");
	else $page = new Page('Alliance details - '.$alliance->getName() . " [" . $myAlliance["shortName"] . "]");
	
	foreach ( (array)$myAlliance["memberCorps"] as $tempcorp)
	{
		$myCorpAPI->setCorpID($tempcorp["corporationID"]);
		$result .= $myCorpAPI->fetchXML();
	
		if ($tempcorp["corporationID"] == $myAlliance["executorCorpID"])
		{
			$ExecutorCorp = $myCorpAPI->getCorporationName();
		}
		// Build Data array
		$membercorp["corpName"] = $myCorpAPI->getCorporationName();
		$membercorp["ticker"] = $myCorpAPI->getTicker();
		$membercorp["members"] = $myCorpAPI->getMemberCount();
		$membercorp["joinDate"] = $tempcorp["startDate"];
		$membercorp["taxRate"] = $myCorpAPI->getTaxRate() . "%";
		$membercorp["url"] = $myCorpAPI->getUrl();
	
		$AllianceCorps[] = $membercorp;

		// Check if corp is known to EDK DB, if not, add it.
		$tempMyCorp->Corporation();
		$tempMyCorp->lookup($myCorpAPI->getCorporationName());
		if ($tempMyCorp->getID() == 0)
		{
			$tempMyCorp->add($myCorpAPI->getCorporationName(), $alliance , substr($tempcorp["startDate"], 0, 16));
		}
	
		$membercorp = array();
		unset($membercorp);
	}
	
	$html .= "<table class=kb-table width=\"100%\" border=\"0\" cellspacing=1><tr class=kb-table-row-even><td rowspan=8 width=128 align=center bgcolor=black>";

	if (file_exists("img/alliances/".$alliance->getUnique().".png"))
	{
    	$html .= "<img src=\"".IMG_URL."/alliances/".$alliance->getUnique().".png\" border=\"0\"></td>";
	}
	else
	{
    	$html .= "<img src=\"".IMG_URL."/alliances/default.gif\" border=\"0\"></td>";
	}
	$kill_summary = new KillSummaryTable();
	$kill_summary->addInvolvedAlliance($alliance);
	$kill_summary->setBreak(config::get('summarytable_rowcount'));
	$summary_html = $kill_summary->generate();

	$html .= "<td class=kb-table-cell width=150><b>Kills:</b></td><td class=kl-kill>".$kill_summary->getTotalKills()."</td>";
	$html .= "<td class=kb-table-cell width=65><b>Executor:</b></td><td class=kb-table-cell><a href=\"?a=search&searchtype=corp&searchphrase=" . $ExecutorCorp . "\">" . $ExecutorCorp . "</a></td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$kill_summary->getTotalLosses()."</td>";
	$html .= "<td class=kb-table-cell><b>Members:</b></td><td class=kb-table-cell>" . $myAlliance["memberCount"] . "</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".round($kill_summary->getTotalKillISK()/1000000000, 2)."B</td>";
	$html .= "<td class=kb-table-cell><b>Start Date:</b></td><td class=kb-table-cell>" . $myAlliance["startDate"] . "</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".round($kill_summary->getTotalLossISK()/1000000000, 2)."B</td>";
	$html .= "<td class=kb-table-cell><b>Number of Corps:</b></td><td class=kb-table-cell>" . count($myAlliance["memberCorps"]) . "</td></tr>";
	if ($kill_summary->getTotalKillISK())
	{
		 $efficiency = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
	}
	else
	{
    	$efficiency = 0;
	}

	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>" . $efficiency . "%</b></td>";
	$html .= "<td class=kb-table-cell></td><td class=kb-table-cell></td></tr>";

	$html .= "</table>";
	$html .= "<br/>";

	$html .= "<table class=kb-table width=\"100%\" border=\"0\" cellspacing=1><tr class=kb-table-header>";
	$html .= "<td class=kb-table-cell><b>Corporation Name</b></td><td class=kb-table-cell align=center><b>Ticker</b></td><td class=kb-table-cell align=center><b>Members</b></td><td class=kb-table-cell align=center><b>Join Date</b></td><td class=kb-table-cell align=center><b>Tax Rate</b></td><td class=kb-table-cell><b>Website</b></td></tr>";
	foreach ( (array)$AllianceCorps as $tempcorp )
	{
		$html .= "<tr class=kb-table-row-even>";
		$html .= "<td class=kb-table-cell><a href=\"?a=search&searchtype=corp&searchphrase=" . $tempcorp["corpName"] . "\">" . $tempcorp["corpName"] . "</a></td>";
		$html .= "<td class=kb-table-cell align=center>" . $tempcorp["ticker"] . "</td>";
		$html .= "<td class=kb-table-cell align=center>" . $tempcorp["members"] . "</td>";
		$html .= "<td class=kb-table-cell align=center>" . $tempcorp["joinDate"] . "</td>";
		$html .= "<td class=kb-table-cell align=center>" . $tempcorp["taxRate"] . "</td>";
		$html .= "<td class=kb-table-cell><a href=\"" . $tempcorp["url"] . "\">" . $tempcorp["url"] . "</a></td>";
		$html .= "</tr>";
	}
	$html .= "</table>";
	$html .= "<br/>";            
} else {
	if($alliance->isFaction()) $page = new Page('Faction details - '.$alliance->getName() . " [" . $myAlliance["shortName"] . "]");
	else $page = new Page('Alliance details - '.$alliance->getName() . " [" . $myAlliance["shortName"] . "]");
	
	$html .= "<table class=kb-table width=\"100%\" border=\"0\" cellspacing=1><tr class=kb-table-row-even><td rowspan=8 width=128 align=center bgcolor=black>";

	if (file_exists("img/alliances/".$alliance->getUnique().".png"))
	{
    $html .= "<img src=\"".IMG_URL."/alliances/".$alliance->getUnique().".png\" border=\"0\"></td>";
	}
	else
	{
    	$html .= "<img src=\"".IMG_URL."/alliances/default.gif\" border=\"0\"></td>";
	}
	$kill_summary = new KillSummaryTable();
	$kill_summary->addInvolvedAlliance($alliance);
	$kill_summary->setBreak(config::get('summarytable_rowcount'));
	$summary_html = $kill_summary->generate();

	$html .= "<td class=kb-table-cell width=180><b>Kills:</b></td><td class=kl-kill>".$kill_summary->getTotalKills()."</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Losses:</b></td><td class=kl-loss>".$kill_summary->getTotalLosses()."</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage done (ISK):</b></td><td class=kl-kill>".round($kill_summary->getTotalKillISK()/1000000000, 2)."B</td></tr>";
	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Damage received (ISK):</b></td><td class=kl-loss>".round($kill_summary->getTotalLossISK()/1000000000, 2)."B</td></tr>";
	if ($kill_summary->getTotalKillISK())
	{
    	$efficiency = round($kill_summary->getTotalKillISK() / ($kill_summary->getTotalKillISK() + $kill_summary->getTotalLossISK()) * 100, 2);
	}
	else
	{
    	$efficiency = 0;
	}

	$html .= "<tr class=kb-table-row-even><td class=kb-table-cell><b>Efficiency:</b></td><td class=kb-table-cell><b>" . $efficiency . "%</b></td></tr>";

	$html .= "</table>";
	$html .= "<br/>";
}

if ($_GET['view'] == "" || $_GET['view'] == "kills" || $_GET['view'] == "losses")
{
    $html .= $summary_html;
}

switch ($_GET['view'])
{
    case "":
        $html .= "<div class=kb-kills-header>10 Most recent kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addInvolvedAlliance($alliance);
        if ($scl_id)
            $list->addVictimShipClass($scl_id);

        $ktab = new KillListTable($list);
        $ktab->setLimit(10);
        $ktab->setDayBreak(false);
        $html .= $ktab->generate();

        $html .= "<div class=kb-losses-header>10 Most recent losses</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setLimit(10);
        $list->setPodsNoobships(true);
        $list->addVictimAlliance($alliance);
        if ($scl_id)
            $list->addVictimShipClass($scl_id);

        $ltab = new KillListTable($list);
        $ltab->setLimit(10);
        $ltab->setDayBreak(false);
        $html .= $ltab->generate();

        break;
    case "kills":
        $html .= "<div class=kb-kills-header>All kills</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->addInvolvedAlliance($alliance);
        if ($scl_id)
            $list->addVictimShipClass($scl_id);
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);
        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        break;
    case "losses":
        $html .= "<div class=kb-losses-header>All losses</div>";

        $list = new KillList();
        $list->setOrdered(true);
        $list->setPodsNoobships(true);
        $list->addVictimAlliance($alliance);
        if ($scl_id)
            $list->addVictimShipClass($scl_id);
        $pagesplitter = new PageSplitter($list->getCount(), 30);
        $list->setPageSplitter($pagesplitter);

        $table = new KillListTable($list);
        $table->setDayBreak(false);
        $html .= $table->generate();
        $html .= $pagesplitter->generate();

        break;
    case "corp_kills":
        $html .= "<div class=block-header2>Top killers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopCorpKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopCorpTable($list, "Kills");
        $html .= $table->generate();
        
		$html .= "<table width=300 cellspacing=1><tr><td><a href='?a=alliance_detail&view=corp_kills&m=$pmonth&all_id=$all_id&y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=alliance_detail&view=corp_kills&all_id=$all_id&m=$nmonth&y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopCorpKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopCorpTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "corp_kills_class":
        $html .= "<div class=block-header2>Destroyed ships</div>";

        // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopCorpKillsList();
            $list->addInvolvedAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopCorpTable($list, "Kills");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center>#</td><td class=kb-table-cell align=center>Corporation</td><td class=kb-table-cell align=center width=60>Kills</td></tr></table>'){
            $html .= "<td valign=top width=440>";
            $html .= "<div class=block-header>".$shp->getName()."</div>";
            $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }

        }
        $html .= "</tr></table>";        
        break;
    case "kills_class":
        $html .= "<div class=block-header2>Destroyed ships</div>";

        // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopKillsList();
            $list->addInvolvedAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopPilotTable($list, "Kills");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center colspan=2>Pilot</td><td class=kb-table-cell align=center width=60>Kills</td></tr></table>'){
            $html .= "<td valign=top width=440>";
            $html .= "<div class=block-header>".$shp->getName()."</div>";
            $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }

        }
        $html .= "</tr></table>";

        break;
    case "corp_losses_class":
        $html .= "<div class=block-header2>Lost ships</div>";

            // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopCorpLossesList();
                $list->addVictimAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopCorpTable($list, "Losses");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center>#</td><td class=kb-table-cell align=center>Corporation</td><td class=kb-table-cell align=center width=60>Losses</td></tr></table>'){
            $html .= "<td valign=top width=440>";
                $html .= "<div class=block-header>".$shp->getName()."</div>";
                $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }
        }
        $html .= "</tr></table>";

        break;
    case "losses_class":
        $html .= "<div class=block-header2>Lost ships</div>";

            // Get all ShipClasses
        $sql = "select scl_id, scl_class from kb3_ship_classes
            where scl_class not in ('Drone','Unknown') order by scl_class";

        $qry = new DBQuery();
        $qry->execute($sql);
        while ($row = $qry->getRow())
        {
            $shipclass[] = new Shipclass($row['scl_id']);
        }
        $html .= "<table class=kb-subtable>";
        $html .= "<tr>";
        $newrow = true;

        foreach ($shipclass as $shp){
            if ($newrow){
            $html .= '</tr><tr>';
            }
            $list = new TopLossesList();
                $list->addVictimAlliance($alliance);
            $list->addVictimShipClass($shp);
            $table = new TopPilotTable($list, "Losses");
            $content = $table->generate();
            if ($content != '<table class=kb-table cellspacing=1><tr class=kb-table-header><td class=kb-table-cell align=center colspan=2>Pilot</td><td class=kb-table-cell align=center width=60>Losses</td></tr></table>'){
            $html .= "<td valign=top width=440>";
                $html .= "<div class=block-header>".$shp->getName()."</div>";
                $html .= $content;
            $html .= "</td>";
            $newrow = !$newrow;
            }
        }
        $html .= "</tr></table>";

        break;
    case "corp_losses":
        $html .= "<div class=block-header2>Top losers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopCorpLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopCorpTable($list, "Losses");
        $html .= $table->generate();

		$html .= "<table width=300 cellspacing=1><tr><td><a href='?a=alliance_detail&view=corp_losses&m=$pmonth&all_id=$all_id&y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=alliance_detail&view=corp_losses&all_id=$all_id&m=$nmonth&y=$nyear'>next</a></p></td></tr></table>";
         
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopCorpLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopCorpTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_kills":
        $html .= "<div class=block-header2>Top killers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

		$html .= "<table width=300 cellspacing=1><tr><td><a href='?a=alliance_detail&view=pilot_kills&m=$pmonth&all_id=$all_id&y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=alliance_detail&view=pilot_kills&all_id=$all_id&m=$nmonth&y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopKillsList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Kills");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_scores":
        $html .= "<div class=block-header2>Top scorers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopScoreList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

		$html .= "<table width=300 cellspacing=1><tr><td><a href='?a=alliance_detail&view=pilot_scores&m=$pmonth&all_id=$all_id&y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=alliance_detail&view=pilot_scores&all_id=$all_id&m=$nmonth&y=$nyear'>next</a></p></td></tr></table>";
          
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopScoreList();
        $list->addInvolvedAlliance($alliance);
        $list->setPodsNoobShips(true);
        $table = new TopPilotTable($list, "Points");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "pilot_losses":
        $html .= "<div class=block-header2>Top losers</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=440>";
        $html .= "<div class=block-header>$monthname $year</div>";

        $list = new TopLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $list->setMonth($month);
        $list->setYear($year);
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

		$html .= "<table width=300 cellspacing=1><tr><td><a href='?a=alliance_detail&view=pilot_losses&m=$pmonth&all_id=$all_id&y=$pyear'>previous</a></td>";
        $html .= "<td align='right'><a href='?a=alliance_detail&view=pilot_losses&all_id=$all_id&m=$nmonth&y=$nyear'>next</a></p></td></tr></table>";
        
        $html .= "</td><td valign=top width=400>";
        $html .= "<div class=block-header>All time</div>";

        $list = new TopLossesList();
        $list->addVictimAlliance($alliance);
        $list->setPodsNoobShips(false);
        $table = new TopPilotTable($list, "Losses");
        $html .= $table->generate();

        $html .= "</td></tr></table>";

        break;
    case "ships_weapons":
        $html .= "<div class=block-header2>Ships & weapons used</div>";

        $html .= "<table class=kb-subtable><tr><td valign=top width=400>";
        $shiplist = new TopShipList();
        $shiplist->addInvolvedAlliance($alliance);
        $shiplisttable = new TopShipListTable($shiplist);
        $html .= $shiplisttable->generate();
        $html .= "</td><td valign=top align=right width=400>";

        $weaponlist = new TopWeaponList();
        $weaponlist->addInvolvedAlliance($alliance);
        $weaponlisttable = new TopWeaponListTable($weaponlist);
        $html .= $weaponlisttable->generate();
        $html .= "</td></tr></table>";

        break;
    case 'violent_systems':
        $html .= "<div class=block-header2>Most violent systems</div>";
        $html .= "<table width=\"99%\"><tr><td align=center valign=top>";

        $html .= "<div class=block-header>This month</div>";
        $html .= "<table class=kb-table>";
        $html .= "<tr class=kb-table-header><td>#</td><td width=180>System</td><td width=40 align=center >Kills</td></tr>";

        $sql = "select sys.sys_name, sys.sys_sec, sys.sys_id, count(distinct kll.kll_id) as kills
                    from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
                    where kll.kll_system_id = sys.sys_id
                    and inv.ind_kll_id = kll.kll_id";

        if ($crp_id)
            $sql .= " and inv.ind_crp_id in (".$crp_id.")";
        if ($all_id)
            $sql .= " and inv.ind_all_id = ".$all_id;

        $sql .= "   and date_format( kll.kll_timestamp, \"%c\" ) = ".kbdate("m")."
                    and date_format( kll.kll_timestamp, \"%Y\" ) = ".kbdate("Y")."
                    group by sys.sys_name
                    order by kills desc
                    limit 25";

        $qry = new DBQuery();
        $qry->execute($sql);
        $odd = false;
        $counter = 1;
        while ($row = $qry->getRow())
        {
            if (!$odd)
            {
                $odd = true;
                $rowclass = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $rowclass = 'kb-table-row-even';
            }

            $html .= "<tr class=".$rowclass."><td><b>".$counter.".</b></td><td class=kb-table-cell width=180><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=center>".$row['kills']."</td></tr>";
            $counter++;
        }

        $html .= "</table>";

        $html .= "</td><td align=center valign=top>";
        $html .= "<div class=block-header>All-Time</div>";
        $html .= "<table class=kb-table>";
        $html .= "<tr class=kb-table-header><td>#</td><td width=180>System</td><td width=40 align=center>Kills</td></tr>";

        $sql = "select sys.sys_name, sys.sys_id, sys.sys_sec, count(distinct kll.kll_id) as kills
                    from kb3_systems sys, kb3_kills kll, kb3_inv_detail inv
                    where kll.kll_system_id = sys.sys_id
                    and inv.ind_kll_id = kll.kll_id";

        if ($crp_id)
            $sql .= " and inv.ind_crp_id in (".$crp_id.")";
        if ($all_id)
            $sql .= " and inv.ind_all_id = ".$all_id;

        $sql .= " group by sys.sys_name
                    order by kills desc
                    limit 25";

        $qry = new DBQuery();
        $qry->execute($sql);
        $odd = false;
        $counter = 1;
        while ($row = $qry->getRow())
        {
            if (!$odd)
            {
                $odd = true;
                $rowclass = 'kb-table-row-odd';
            }
            else
            {
                $odd = false;
                $rowclass = 'kb-table-row-even';
            }

            $html .= "<tr class=".$rowclass."><td><b>".$counter.".</b></td><td class=kb-table-cell><b><a href=\"?a=system_detail&amp;sys_id=".$row['sys_id']."\">".$row['sys_name']."</a></b> (".roundsec($row['sys_sec']).")</td><td align=center>".$row['kills']."</td></tr>";
            $counter++;
        }
        $html .= "</table>";
        $html .= "</td></tr></table>";
    break;
}

$html .= "<hr><b>Extended Alliance Detail by " . FindThunk() . ".<b/></br>";

$menubox = new Box("Menu");
$menubox->setIcon("menu-item.gif");
$menubox->addOption("caption","Kills & losses");
$menubox->addOption("link","Recent activity", "?a=alliance_detail&all_id=" . $alliance->getID());
$menubox->addOption("link","Kills", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=kills");
$menubox->addOption("link","Losses", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=losses");
$menubox->addOption("caption","Corp statistics");
$menubox->addOption("link","Top killers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_kills");
$menubox->addOption("link","Top losers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_losses");
$menubox->addOption("link","Destroyed ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_kills_class");
$menubox->addOption("link","Lost ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=corp_losses_class");
$menubox->addOption("caption","Pilot statistics");
$menubox->addOption("link","Top killers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_kills");
if (config::get('kill_points'))
{
    $menubox->addOption('link', "Top scorers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_scores");
}
$menubox->addOption("link","Top losers", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=pilot_losses");
$menubox->addOption("link","Destroyed ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=kills_class");
$menubox->addOption("link","Lost ships", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=losses_class");
$menubox->addOption("caption","Global statistics");
$menubox->addOption("link","Ships & weapons", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=ships_weapons");
$menubox->addOption("link","Most violent systems", "?a=alliance_detail&all_id=" . $alliance->getID() . "&view=violent_systems");
$page->addContext($menubox->generate());

$page->setContent($html);
$page->generate();
?>