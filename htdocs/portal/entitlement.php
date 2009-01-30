<?php
// portal/entitlement.inc.php - Lists contacts entitlments in the portal included by ../portal.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author Kieran Hogg <kieran[at]sitracker.org>

@include ('../set_include_path.inc.php');
require 'db_connect.inc.php';
require 'functions.inc.php';

$accesslevel = 'any';

include 'portalauth.inc.php';
include 'portalheader.inc.php';

echo "<h2>".icon('support', 32, $strYourSupportEntitlement);
echo " {$strYourSupportEntitlement}</h2>";

if (sizeof($_SESSION['entitlement']) >= 1)
{
    echo "<table align='center'>";
    echo "<tr>";
    echo colheader('id',$strContractID);
    echo colheader('name',$strProduct);
    echo colheader('availableincidents',$strIncidentsAvailable);
    echo colheader('usedincidents',$strIncidentsUsed);
    echo colheader('expirydate', $strExpiryDate);
    echo colheader('actions', $strOperation);
    echo "</tr>";
    $shade = 'shade1';
    foreach ($_SESSION['entitlement'] AS $contract)
    {
        echo "<tr class='$shade'>";
        echo "<td>";
        // Only show link to contract details if the contract belongs to our site
        // Since we can be supported by contracts that aren't our site
        if ($contract->site == $_SESSION['siteid'])
        {
            echo "<a href='contracts.php?id={$contract->id}'>{$contract->id}</a>";
        }
        else echo $contract->id;
        echo "</td>";
        echo "<td>{$contract->name}</td>";
        echo "<td>";
        if ($contract->incident_quantity == 0)
        {
            echo "&#8734; {$strUnlimited}";
        }
        else
        {
            echo "{$contract->availableincidents}";
        }
        echo "</td>";
        echo "<td>{$contract->incidents_used}</td>";
        echo "<td>";
        if ($contract->expirydate == -1)
        {
            echo $strUnlimited;
        }
        else
        {
            echo ldate($CONFIG['dateformat_date'],$contract->expirydate);
        }
        echo "</td>";
        echo "<td>";
        if ($contract->expirydate > $now OR $contract->expirydate == -1)
        {
            echo "<a href='add.php?contractid={$contract->id}&amp;product={$contract->product}'>{$strAddIncident}</a>";
        }
        else
        {
            echo $strExpired;
        }
        echo "</td></tr>\n";
        if ($shade == 'shade1') $shade = 'shade2';
        else $shade = 'shade1';
    }
    echo "</table>";
}
else
{
    echo "<p class='info'>{$strNone}</p>";
}

include './inc/htmlfooter.inc.php';
?>
