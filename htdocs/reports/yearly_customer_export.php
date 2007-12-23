<?php
// yearly_customer_export.php - List the numbers and titles of incidents logged by each site in the past year.
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

// This Page Is Valid XHTML 1.0 Transitional!   15Mar06

@include ('set_include_path.inc.php');
$permission=37; // Run Reports

require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

$title = $strIncidentsBySite;

if (empty($_REQUEST['mode']))
{
    include ('htmlheader.inc.php');
    echo "<h2>$title</h2>";
    echo "<p align='center'>This report lists the incidents that each site has logged over the past twelve months.</p>";
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<table summary='Site Selection Table' align='center'>";
    echo "<tr><th colspan='2' align='center'>Include</th></tr>";
    echo "<tr><td align='center' colspan='2'>";
    $sql = "SELECT * FROM `{$dbSites}` ORDER BY name";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    echo "<select name='inc[]' multiple='multiple' size='20'>";
    while ($site = mysql_fetch_object($result))
    {
        echo "<option value='{$site->id}'>{$site->name}</option>\n";
    }
    echo "</select>";
    echo "</td>";
    echo "</tr>\n";
    echo "<tr><th align='right'>{$strOutput}:</th>";
    echo "<td width='400'>";
    echo "<input type='checkbox' name='showsitetotals' value='yes' /> Add a line after each site showing totals<br />";
    echo "<input type='checkbox' name='showtotals' value='yes' /> Add a line to the bottom of the report showing totals<br /><br />";
    echo "<select name='output'>";
    echo "<option value='screen'>{$strScreen}</option>";
    echo "<option value='csv'>{$strCSVfile}</option>";
    echo "</select>";
    echo "</td></tr>";
    echo "</table>";
    echo "<p align='center'>";
    echo "<input type='hidden' name='table1' value='{$_POST['table1']}' />";
    echo "<input type='hidden' name='mode' value='report' />";
    echo "<input type='submit' value=\"{$strRunReport}\" />";
    echo "</p>";
    echo "</form>";
    include ('htmlfooter.inc.php');
}
elseif ($_REQUEST['mode']=='report')
{
    if (is_array($_POST['exc']) && is_array($_POST['exc'])) $_POST['inc']=array_values(array_diff($_POST['inc'],$_POST['exc']));  // don't include anything excluded

    $includecount=count($_POST['inc']);
    if ($_POST['showsitetotals']=='yes') $showsitetotals = TRUE;
    else $showsitetotals = FALSE;

    if ($_POST['showtotals']=='yes') $showtotals = TRUE;
    else $showtotals = FALSE;

    if ($_POST['showgrandtotals']=='yes') $showgrandtotals = TRUE;
    else $showgrandtotals = FALSE;

    if ($includecount >= 1)
    {
        // $html .= "<strong>Include:</strong><br />";
        $incsql .= "(";
        for ($i = 0; $i < $includecount; $i++)
        {
            // $html .= "{$_POST['inc'][$i]} <br />";
            $incsql .= "siteid={$_POST['inc'][$i]}";
            if ($i < ($includecount-1)) $incsql .= " OR ";
        }
        $incsql .= ")";
    }
    $sql = "SELECT i.id AS incid, i.title AS title, c.id AS contactid, s.name AS site, c.email AS cemail, ";
    $sql .= "CONCAT(c.forenames,' ',c.surname) AS cname, i.opened as opened, st.typename, i.externalid AS externalid, ";
    $sql .= "s.id AS siteid ";
    $sql .= "FROM `{$dbContacts}` AS c, `{$dbSites}` AS s, `{$dbSiteTypes}` AS st, `{$dbIncidents}` AS i ";
    $sql .= "WHERE c.siteid = s.id AND s.typeid = st.typeid AND i.opened > ($now-60*60*24*365.25) ";
    $sql .= "AND i.contact=c.id";

    if (empty($incsql)==FALSE OR empty($excsql)==FALSE) $sql .= " AND ";
    if (!empty($incsql)) $sql .= "$incsql";
    if (empty($incsql)==FALSE AND empty($excsql)==FALSE) $sql .= " AND ";
    if (!empty($excsql)) $sql .= "$excsql";

    $sql .= " ORDER BY site, incid ASC ";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $numrows = mysql_num_rows($result);

    // FIXME i18n

    $html .= "<p align='center'>This report is a list of ($numrows) incidents for all sites that you selected</p>";
    $html .= "<table width='99%' align='center'>";
    $html .= "<tr><th>{$strOpened}</th><th>{$strIncident}</th><th>{$strExternalID}</th><th>{$strTitle}</th><th>{$strContact}</th><th>{$strSite}</th><th>{$strType}</th></tr>";
    $csvfieldheaders .= "{$strOpened},{$strIncident},{$strExternalID},{$strTitle},{$strContact},{$strSite},{$strType}\r\n";
    $rowcount=0;
    $externalincidents=0;
    while ($row = mysql_fetch_object($result))
    {
        $nicedate=date('d/m/Y',$row->opened);
        $html .= "<tr class='shade2'><td>$nicedate</td><td>{$row->incid}</td><td>{$row->externalid}</td><td>{$row->title}</td><td>{$row->cname}</td><td>{$row->site}</td><td>{$row->typename}</td></tr>\n";
        $csv .="'".$nicedate."', '{$row->incid}','{$row->externalid}', '{$row->title}','{$row->cname}','{$row->site}','{$row->typename}'\n";
        if (!empty($row->externalid))
        {
            $externalincidents++;
            $sitetotals[$row->siteid]['extincidents']++;
        }
        $sitetotals[$row->siteid]['incidents']++;
        if ($sitetotals[$row->siteid]['name']=='') $sitetotals[$row->siteid]['name']=$row->site;

    }

    if ($showsitetotals)
    {
        foreach ($sitetotals AS $sitetotal)
        {
            if ($sitetotal['incidents'] >= 1) $externalpercent = number_format(($sitetotal['extincidents'] / $sitetotal['incidents'] * 100),1);
            $html .= "<tr class='shade1'><td colspan='0'>Number of incidents logged by {$sitetotal['name']}: {$sitetotal['incidents']}, Logged externally: {$sitetotal['extincidents']} ({$externalpercent}%)</td></tr>\n";
        }
    }

    if ($numrows >= 1) $externalpercent = number_format(($externalincidents / $numrows * 100),1);
    if ($showtotals)
    {
        $html .= "<tfoot><tr><td colspan='0'>Total Number of incidents logged: {$numrows}, Logged externally: {$externalincidents} ({$externalpercent}%)</td></tr></tfoot>\n";
    }

    $html .= "</table>";

    // $html .= "<p align='center'>SQL Query used to produce this report:<br /><code>$sql</code></p>\n";

    if ($_POST['output']=='screen')
    {
        include ('htmlheader.inc.php');
        echo $html;
        include ('htmlfooter.inc.php');
    }
    elseif ($_POST['output']=='csv')
    {
        // --- CSV File HTTP Header
        header("Content-type: text/csv\r\n");
        header("Content-disposition-type: attachment\r\n");
        header("Content-disposition: filename=yearly_incidents.csv");
        echo $csvfieldheaders;
        echo $csv;
    }
}
?>
