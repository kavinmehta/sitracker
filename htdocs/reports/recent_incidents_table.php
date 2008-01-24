<?php
// recent_incidents_table.php - Report showing a list of incidents logged in the past month
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
// Comments: Shows a list of incidents that each site has logged

@include('../set_include_path.inc.php');
require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');
include('htmlheader.inc.php');

$sites=array();

$monthago = time()-(60 * 60 * 24 * 30.5);

echo "<h2>Incidents opened since ".ldate($CONFIG['dateformat_date'], $monthago)."</h2>";

$sql  = "SELECT *,sites.id AS siteid FROM sites, maintenance, supportcontacts, incidents ";
$sql .= "WHERE sites.id = maintenance.site ";
$sql .= "AND maintenance.id = supportcontacts.maintenanceid ";
$sql .= "AND supportcontacts.contactid = incidents.contact ";
$sql .= "AND incidents.opened > '$monthago' ";
$sql .= "ORDER BY sites.id, incidents.id";

$result = mysql_query($sql);
if (mysql_error()) trigger_error("MySQL Query Error: ".mysql_error(), E_USER_ERROR);

if (mysql_num_rows($result) > 0)
{
    $prvincid=0;
    while ($row = mysql_fetch_object($result))
    {
        if ($prvincid!=$row->id)
        {
            echo "<b>[{$row->siteid}] {$row->name}</b> Incident: <a href='{$CONFIG['application_uriprefix']}{$CONFIG['application_webpath']}incident_details.php?id={$row->id}'>{$row->id}</a>  ";
            echo "Date: ".ldate('d M Y', $row->opened)." ";
            echo "Product: ".product_name($row->product);
            $site=$row->siteid;
            $$site++;
            $sites[]=$row->siteid;
            echo "<br />\n";
        }
        $prvincid=$row->id;
        // print_r($row);
    }
}
else
{
    echo "<p class='warning'>{$strNoRecords}</p>";
}

$sites=array_unique($sites);

/*
foreach($sites AS $site => $val)
{
  $tot[$val] = $$val;
}

rsort($tot);

foreach($tot AS $total => $val)
{
  echo "total: $total   value: $val <br />";
}
*/

$totals=array();

foreach($sites AS $site => $val)
{
    if ($prev > $$val) array_push($totals, $val);
    else array_unshift($totals, $val);
    $prev=$$val;
}


// was sites
/*
foreach($totals AS $site => $val)
{
  echo "[{$val}] ".site_name($val);
  echo "= {$$val} <br />";
}
*/

include('htmlfooter.inc.php');
?>
