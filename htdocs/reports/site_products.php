<?php
// site_products.php - List products that sites have under contract
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

@include ('../set_include_path.inc.php');
$permission = 37; // Run Reports
$title = $strSiteProducts;
require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

if (empty($_REQUEST['mode']))
{
    include ('htmlheader.inc.php');
    echo "<h2>{$title}</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<table align='center'>";

    echo "<tr><th>{$strSiteType}:</td>";
    echo "<td>";
    echo sitetype_drop_down('type', 0);
    echo "</td></tr>";

    echo "<tr><th>{$strOutput}:</th>";
    echo "<td>";
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

    echo "<table align='center'><tr><td>";
    echo "<h4>{$strCSVFileFormatAsFollows}:</h4>";
    echo "<strong>{$strField1}:</strong> {$strSite}<br />";
    echo "<strong>{$strField1}:</strong> {$strAddress1}<br />";
    echo "<strong>{$strField1}:</strong> {$strAddress2}<br />";
    echo "<strong>{$strField1}:</strong> {$strCity}<br />";
    echo "<strong>{$strField1}:</strong> {$strCounty}<br />";
    echo "<strong>{$strField1}:</strong> {$strCountry}<br />";
    echo "<strong>{$strField1}:</strong> {$strPostcode}<br />";
    echo "<strong>{$strField1}:</strong> {$strProducts}<br />";
    echo "</td></tr></table>";
    include ('htmlfooter.inc.php');
}
elseif ($_REQUEST['mode'] == 'report')
{
    $type = cleanvar($_REQUEST['type']);
    $sql = "SELECT * FROM `{$dbSites}` WHERE typeid='$type' ORDER BY name";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $numrows = mysql_num_rows($result);

    // FIXME i18n
    $html .= "<p align='center'>This report is a list of sites that you selected and the products they have (or have had) maintenance for.</p>";
    $html .= "<table width='99%' align='center'>";
    $html .= "<tr><th>{$strSite}</th><th>{$strAddress1}</th>";
    $html .= "<th>{$strAddress2}</th><th>{$strCity}</th>";
    $html .= "<th>{$strCounty}</th><th>{$strCountry}</th>";
    $html .= "<th>{$strPostcode}</th><th>{$strProducts}</th></tr>";
    $csvfieldheaders .= "site,address1,address2,city,county,country,postcode,products\r\n";
    $rowcount=0;
    while ($row = mysql_fetch_object($result))
    {
        // FIXME strip slashes
        $product= '';
        $nicedate = ldate('d/m/Y',$row->opened);
        $html .= "<tr class='shade2'><td>{$row->name}</td>";
        $html .= "<td>{$row->address1}</td><td>{$row->address2}</td>";
        $html .= "<td>{$row->city}</td><td>{$row->county}</td>";
        $html .= "<td>{$row->country}</td><td>{$row->postcode}</td>";
        $html .= "<td>";
        $psql  = "SELECT m.id AS maintid, m.term AS term, p.name AS product, ";
        $psql .= "m.admincontact AS admincontact, ";
        $psql .= "r.name AS reseller, licence_quantity, lt.name AS licence_type, expirydate, admincontact, c.forenames AS admincontactsforenames, c.surname AS admincontactssurname, m.notes AS maintnotes ";
        $psql .= "FROM `{$dbMaintenance}` AS m, `{$dbContacts}` AS c, `{$dbProducts}` AS p, `{$dbLicenceTypes}` AS lt, `{$dbResellers}` AS r ";
        $psql .= "WHERE m.product = p.id AND m.reseller = r.id AND licence_type = lt.id AND admincontact = c.id ";
        $psql .= "AND m.site = '{$row->id}' ";
        $psql .= "ORDER BY p.name ASC";
        $presult = mysql_query($psql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        while ($prod = mysql_fetch_object($presult))
        {
            $product.= "{$prod->product}\n";
        }
        $html .= nl2br($product)."</td>";
        $html .= "</tr>";
        $csv .="'{$row->name}', '{$row->address1}','{$row->address2}','{$row->city}','{$row->county}','{$row->country}','{$row->postcode}',";
        $csv .= ''.str_replace("\n", ",", $product)."\n";
        // flush();
    }
    $html .= "</table>";

    //  $html .= "<p align='center'>SQL Query used to produce this report:<br /><code>$sql</code></p>\n";

    if ($_POST['output'] == 'screen')
    {
        include ('htmlheader.inc.php');
        echo $html;
        include ('htmlfooter.inc.php');
    }
    elseif ($_POST['output'] == 'csv')
    {
        // --- CSV File HTTP Header
        header("Content-type: text/csv\r\n");
        header("Content-disposition-type: attachment\r\n");
        header("Content-disposition: filename=site_products.csv");
        echo $csvfieldheaders;
        echo $csv;
    }
}
?>
