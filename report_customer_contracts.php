<?php
// supportbycontract.php - Shows sites and their contracts
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author:   Ivan Lucas
// Email:    ivanlucas[at]users.sourceforge.net
// Comments: List supported contacts by contract


$permission = 19; /* View Maintenance Contracts */
$title = $strSupportedContactsbySite;

require ('core.php');
require (APPLICATION_LIBPATH . 'functions.inc.php');

// This page requires authentication
require (APPLICATION_LIBPATH . 'auth.inc.php');

$siteid = cleanvar($_REQUEST['siteid']);

if ($_REQUEST['mode'] == 'csv')
{
    // --- CSV File HTTP Header
    header("Content-type: text/csv\r\n");
    header("Content-disposition-type: attachment\r\n");
    header("Content-disposition: filename=supported_contacts_by_contract.csv");
}
else
{
    include (APPLICATION_INCPATH . 'htmlheader.inc.php');
}

$sql = "SELECT *, s.name AS sitename FROM `{$dbSites}` AS s ";
if (!empty($_REQUEST['siteid'])) $sql .= "WHERE id='{$siteid}'";
else $sql .= "ORDER BY s.name";
$result = mysql_query($sql);
if (mysql_error()) trigger_error(mysql_error(), E_USER_WARNING);
while ($site = mysql_fetch_object($result))
{
    $msql  = "SELECT m.id AS maintid, m.term AS term, p.name AS product, r.name AS reseller, ";
    $msql .= "licence_quantity, l.name AS licence_type, expirydate, admincontact, c.forenames AS admincontactsforenames, ";
    $msql .= "c.surname AS admincontactssurname, m.notes AS maintnotes ";
    $msql .= "FROM `{$dbMaintenance}` AS m, `{$dbContacts}` AS c, `{$dbProducts}` AS p, `{$dbLicenceTypes}` AS l, `{$dbResellers}` AS r ";
    $msql .= "WHERE m.product=p.id ";
    $msql .= "AND m.reseller=r.id AND licence_type=l.id AND admincontact=c.id ";
    $msql .= "AND m.site = '{$site->id}' ";
//     $msql .= "AND p.vendorid=2 ";    // novell products only
    $msql .= "AND m.term!='yes' ";
    $msql .= "AND m.expirydate > '$now' ";     $msql .= "ORDER BY expirydate DESC";

    echo "\n<!-- $msql -->\n";
    $mresult = mysql_query($msql);
    if (mysql_num_rows($mresult)>=1)
    {
        if ($_REQUEST['mode'] == 'csv')
        {
            echo "{$site->sitename}\n";
            echo "Product,Licence,Expiry Date,Engineer 1, Engineer 2, Engineer 3, Engineer 4\n";
            while ($maint = mysql_fetch_object($mresult))
            {
                if ($maint->expirydate > $now AND $maint->term!='yes')
                {
                    echo "{$maint->product},";
                    echo "{$maint->licence_quantity} {$maint->licence_type},";
                    echo ldate($CONFIG['dateformat_date'], $maint->expirydate).",";
                    $csql  = "SELECT * FROM `{$dbSupportContacts}` ";
                    $csql .= "WHERE maintenanceid='{$maint->maintid}' ";
                    $csql .= "ORDER BY contactid LIMIT 4";
                    ## echo "<!-- ($csql) -->";
                    $cresult = mysql_query($csql);
                    if (mysql_error()) trigger_error(mysql_error(), E_USER_WARNING);
                    while ($contact = mysql_fetch_object($cresult))
                    {
                        echo contact_realname($contact->contactid).",";
                    }
                    echo "\n";
                    $a++;
                }
            }
        }
        else
        {
            echo "<h2>{$site->sitename}</h2>";
            echo "<table width='100%'>";
            echo "<tr><th style='text-align: left;'>{$strProduct}</th><th style='text-align: left;'>{$strLicense}</th><th style='text-align: left;'>{$strExpiryDate}</th><th style='text-align: left;'>Engineer 1</th><th style='text-align: left;'>Engineer 2</th><th style='text-align: left;'>Engineer 3</th><th style='text-align: left;'>Engineer 4</th></tr>\n";
            while ($maint = mysql_fetch_object($mresult))
            {
                if ($maint->expirydate > $now AND $maint->term!='no')
                {
                    echo "<tr>";
                    echo "<td width='20%'>{$maint->product}</td>";
                    echo "<td>{$maint->licence_quantity} {$maint->licence_type}</td>";
                    echo "<td>".ldate($CONFIG['dateformat_date'], $maint->expirydate)."</td>";

                    $csql  = "SELECT * FROM `{$dbSupportContacts}` ";
                    $csql .= "WHERE maintenanceid='{$maint->maintid}' ";
                    $csql .= "ORDER BY contactid LIMIT 4";
                    ## echo "<!-- ($csql) -->";
                    $cresult = mysql_query($csql);
                    if (mysql_error()) trigger_error(mysql_error(), E_USER_WARNING);
                    while ($contact = mysql_fetch_object($cresult))
                    {
                        echo "<td>".contact_realname($contact->contactid)."</td>";
                    }
                    echo "</tr>\n";
                    $a++;
                }
            }
            echo "</table>";
            echo "<hr />";
        }
    }
}
if ($_REQUEST['mode']!='csv')
{
    echo "<p align='center'><a href='{$_SERVER['PHP_SELF']}?siteid={$siteid}&amp;mode=csv'>Download as <abbr title='Comma Seperated Values'>CSV</abbr> File</a></p>";
    include (APPLICATION_INCPATH . 'htmlfooter.inc.php');
}
?>
