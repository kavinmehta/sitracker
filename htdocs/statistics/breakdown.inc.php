<?php
// breakdown.inc.php - Displays the incidents for a particular day and condition (i.e. closed, opened etc)
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Paul Heaney <paulheaney[at]users.sourceforge.net>


// Included by ../statistics.php

// Prevent script from being run directly (ie. it must always be included
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
    exit;
}

$sql = get_sql_statement($startdate,$enddate,$query,false);
$result= mysql_query($sql);
if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

$start_str=date("Y-m-d",$startdate);
$end_str=date("Y-m-d",$enddate);

switch($query)
{
    case 0:
        $type='opened';
        break;
    case 1:
        $type='closed';
        break;
    case 2:
        $type='updated';
        break;
}

if($start_str==$end_str) echo "<h2>Incidents {$type} on {$start_str}</h2>";
else echo "<h2>Incidents {$type} between {$start_str} and {$end_str}</h2>";

echo "<table align='center'>";

echo "<tr><th>ID</th><th>Title</th><th>Opened</th><th>Closed</th><th>Owner</th><th>Customer</th><th>Site</th></tr>";

while($row = mysql_fetch_array($result))
{
    echo "<tr>";
    echo "<td><a href=\"javascript:incident_details_window('{$row['id']}','incident{$row['id']}')\" class='info'>{$row['id']}</a></td>";
    echo "<td><a href=\"javascript:incident_details_window('{$row['id']}','incident{$row['id']}')\" class='info'>{$row['title']}</a></td>";
    echo "<td>".date($CONFIG['dateformat_datetime'],$row['opened'])."</td>";
    echo "<td>".date($CONFIG['dateformat_datetime'],$row['closed'])."</td>";
    echo "<td>".user_realname($row['owner'])."</td>";
    $sql = "SELECT contacts.forenames,contacts.surname, sites.name FROM contacts,sites WHERE sites.id = contacts.siteid AND contacts.id = {$row['contact']}";
    $contactResult = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $contact = mysql_fetch_array($contactResult);
    echo "<td>{$contact['forenames']} {$contact['surname']}</td>";
    echo "<td>{$contact['name']}</td>";
    echo "</tr>";
}

echo "</table>";

?>