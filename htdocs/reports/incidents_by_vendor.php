<?php
// incidents_by_vendor.php - List the number of incidents for each vendor
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Authors: Paul Heaney <paulheaney[at]users.sourceforge.net>

@include('set_include_path.inc.php');
$permission=37; // Run Reports

require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

$title = $strIncidentsByVendor;

if (empty($_REQUEST['mode']))
{
    include('htmlheader.inc.php');

    echo "<h2>$title</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}' id='incidentsbyvendor' method='post'>";
    echo "<table class='vertical'>";
    echo "<tr><td class='shade2'>{$strStartDate}:</td>";
    echo "<td class='shade2'><input type='text' name='startdate' id='startdate' size='10' /> ";
    echo date_picker('incidentsbyvendor.startdate');
    echo "</td></tr>";
    echo "<tr><td class='shade2'>{$strEndDate}:</td>";
    echo "<td class='shade2'><input type='text' name='enddate' id='enddate' size='10' /> ";
    echo date_picker('incidentsbyvendor.enddate');
    echo "</td></tr>";
    echo "</table>";
    echo "<p align='center'>";
    echo "<input type='hidden' name='mode' value='report' />";
    echo "<input type='submit' value=\"{$strRunReport}\" />";
    echo "</p>";
    echo "</form>";

    include('htmlfooter.inc.php');
}
else
{
/*
SELECT COUNT( incidents.id ) , products.vendorid, vendors.name
FROM incidents, products, vendors
WHERE incidents.product = products.id
AND products.vendorid = vendors.id
GROUP BY products.vendorid
LIMIT 0 , 30
*/
    $startdate = strtotime($_REQUEST['startdate']);
    $enddate = strtotime($_REQUEST['enddate']);

    $sql = "SELECT COUNT(incidents.id) AS volume, products.vendorid, vendors.name  FROM incidents, products, vendors WHERE incidents.product = products.id AND incidents.opened >= '{$startdate}' AND incidents.opened <= '{$enddate}' ";
    $sql .= "AND products.vendorid = vendors.id GROUP BY products.vendorid";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    include('htmlheader.inc.php');

    echo "<h2>$title</h2>";

    // FIXME i18n for the period N to N
    echo "<p align='center'>For the period {$_REQUEST['startdate']} to {$_REQUEST['enddate']}</p>";

    if(mysql_num_rows($result) > 0)
    {
        echo "<p>";
        echo "<table class='vertical' align='center'>";
        echo "<tr><th>{$strVendor}</th><th>{$strIncidents}</th></tr>";
        while($row = mysql_fetch_array($result))
        {
            echo "<tr><td class='shade1'>".$row['name']."</td><td class='shade1'>".$row['volume']."</td></tr>";
        }
        echo "</table>";
        echo "</p>";
    }

    include('htmlfooter.inc.php');

}


?>