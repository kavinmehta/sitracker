<?php
// add_contract.php - Add a new maintenance contract
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// This Page fails XHTML validation because of collapsable tbody in the table - INL 12/12/07
// FIXME make XHTML complient - PH 13/12/07

@include ('set_include_path.inc.php');
$permission = 39; // Add Maintenance Contract

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

$title = $strAddContract;

// External variables
$action = $_REQUEST['action'];
$siteid = cleanvar($_REQUEST['siteid']);

// Show add maintenance form
if ($action == "showform" OR $action=='')
{
    include ('htmlheader.inc.php');

    echo show_form_errors('add_contract');
    clear_form_errors('add_contract');
    echo "<h2>".icon('contract', 32)." ";
    echo "{$strAddContract}</h2>";
    echo "<form name='addcontract' action='{$_SERVER['PHP_SELF']}?action=add' method='post' onsubmit='return confirm_action(\"{$strAreYouSureAddContract}\");'>";
    echo "<table align='center' class='vertical'>";

    echo "<tr><th>{$strSite}</th><td>";
    if ($_SESSION['formdata']['add_contract']['site'] != '')
    {
        echo site_drop_down("site", $_SESSION['formdata']['add_contract']['site'], TRUE);
    }
    else
    {
        echo site_drop_down("site", $siteid, TRUE);
    }
    echo " <span class='required'>{$strRequired}</span></td></tr>\n";
    echo "<tr><th>{$strContacts}</th><td>";
    echo "<input value='amount' type='radio' name='contacts' checked='checked' />";

    echo "{$strLimitTo} <input size='2' name='amount' ";
    if ($_SESSION['formdata']['add_contract']['contacts'] != '')
    {
        echo "value='{$_SESSION['formdata']['add_contract']['amount']}'";
    }
    else
    {
        echo "value='0'";
    }
    echo " /> {$strSupportedContacts} ({$str0MeansUnlimited})<br />";
    echo "<input type='radio' value='all' name='contacts' />";
    echo "{$strAllSiteContactsSupported}";
    echo "</td></tr>";
    echo "<tr><th>{$strProduct} <sup class='red'>*</sup></th><td>";
    if ($_SESSION['formdata']['add_contract']['product'] != '')
    {
        echo product_drop_down("product", $_SESSION['formdata']['add_contract']['product'])."</td></tr>\n";
    }
    else
    {
        echo product_drop_down("product", 0)."</td></tr>\n";
    }

    // TODO if service level is timed, we need to ask for unit rate (and daily rate?)
    // servicelevel_timed($sltag)
    echo "<tr><th>{$strServiceLevel}</th><td>";
    if ($_SESSION['formdata']['add_contract']['servicelevelid'] != '')
    {
        $slid = $_SESSION['formdata']['add_contract']['servicelevelid'];
    }
    else
    {
        $slid = 0;  // Default to first service level
    }
    echo servicelevel_drop_down('servicelevelid', $slid, TRUE, "onchange=\"addcontract_sltimed(\$F('servicelevelid'));\"")."</td></tr>\n";
    // check the initially selected service level to decide whether to show the extra hiddentimed section
    $sltag = servicelevel_id2tag($slid);
    $timed = servicelevel_timed($sltag);

    echo "<tbody id='hiddentimed'";
    if (!$timed) echo " style='display:none'";
    echo ">"; //FIXME not XHTML
    echo "<tr><th>{$strUnitRate}<sup class='red'>*</sup></th><td>{$CONFIG['currency_symbol']}";
    echo "<input name='unitrate' size='5' /></td></tr>";
    echo "</tbody>";

    echo "<tr><th colspan='2' style='text-align: left;'><br />Service Period</th></tr>";
    echo "<tr><th>{$strStartDate}</th>";
    echo "<td><input type='text' name='startdate' id='startdate' size='10' value='".date('Y-m-d', $now)."' /> ";
    echo date_picker('addcontract.startdate');
    echo "</td></tr>";

    echo "<tr><th>{$strExpiryDate}</th>";
    echo "<td><input class='required' name='expiry' size='10' ";
    if ($_SESSION['formdata']['add_contract']['expiry'] != '')
    {
        echo "value='{$_SESSION['formdata']['add_contract']['expiry']}'";
    }
    echo "/> ".date_picker('addcontract.expiry');
    echo "<input type='checkbox' name='noexpiry' ";
    if ($_SESSION['formdata']['add_contract']['noexpiry'] == "on")
    {
        echo "checked='checked' ";
    }
    echo "onclick=\"this.form.expiry.value=''\" /> {$strUnlimited}";
    echo " <span class='required'>{$strRequired}</span></td></tr>\n";

    echo "<tr><th>{$strAdminContact}</th>";
    echo "<td>".contact_drop_down("admincontact", 0, TRUE, TRUE);
    echo " <span class='required'>{$strRequired}</span></td></tr>\n";

    echo "<tr><th>{$strNotes}</th><td><textarea cols='40' name='notes' rows='5'>{$_SESSION['formdata']['add_contract']['notes']}</textarea></td></tr>\n";

    echo "<tr><th></th><td><a href=\"javascript:void(0);\" onclick=\"$('hidden').toggle();\">{$strMore}</a></td></tr>\n";

    echo "<tbody id='hidden' style='display:none'>"; //FIXME not XHTML

    echo "<tr><th>{$strReseller}</th><td>";
    reseller_drop_down("reseller", 1);
    echo "</td></tr>\n";

    echo "<tr><th>{$strLicenseQuantity}</th><td><input value='0' maxlength='7' name='licence_quantity' size='5' />";
    echo " ({$str0MeansUnlimited})</td></tr>\n";

    echo "<tr><th>{$strLicenseType}</th><td>";
    licence_type_drop_down("licence_type", 0);
    echo "</td></tr>\n";

    echo "<tr><th>{$strAmount}</th><td>{$CONFIG['currency_symbol']}";
    echo "<input value='0' maxlength='7' name='amount' size='5' /></td></tr>\n";


    echo "<tr><th>{$strIncidentPool}</th>";
    $incident_pools = explode(',', "Unlimited,{$CONFIG['incident_pools']}");
    echo "<td>".array_drop_down($incident_pools,'incident_poolid',$maint['incident_quantity'])."</td></tr>";

    echo "<tr><th>{$strProductOnly}</th><td><input name='productonly' type='checkbox' value='yes' /></td></tr></tbody>\n"; //FIXME XHTML

    echo "</table>\n";
    if ($timed) $timed = 'yes';
    else $timed = 'no';
    echo "<input type='hidden' id='timed' name='timed' value='no' />";
    echo "<p align='center'><input name='submit' type='submit' value=\"{$strAddContract}\" /></p>";
    echo "</form>";
    include ('htmlfooter.inc.php');

    clear_form_data('add_contract');

}
elseif ($action == "add")
{
    // External Variables
    $site = cleanvar($_REQUEST['site']);
    $product = cleanvar($_REQUEST['product']);
    $reseller = cleanvar($_REQUEST['reseller']);
    $licence_quantity = cleanvar($_REQUEST['licence_quantity']);
    $licence_type = cleanvar($_REQUEST['licence_type']);
    $admincontact = cleanvar($_REQUEST['admincontact']);
    $notes = cleanvar($_REQUEST['notes']);
    $servicelevelid = cleanvar($_REQUEST['servicelevelid']);
    $incidentpoolid = cleanvar($_REQUEST['incidentpoolid']);
    $productonly = cleanvar($_REQUEST['productonly']);
    $term = cleanvar($_REQUEST['term']);
    $contacts = cleanvar($_REQUEST['contacts']);
    $timed = cleanvar($_REQUEST['timed']);
    $startdate = strtotime($_REQUEST['startdate']);
    if ($startdate > 0) $startdate = date('Y-m-d',$startdate);
    else $startdate = date('Y-m-d',$now);
    $enddate = strtotime($_REQUEST['expiry']);
    if ($enddate > 0) $enddate = date('Y-m-d',$enddate);
    else $enddate = date('Y-m-d',$now);
    
    if ($_REQUEST['noexpiry'] == 'on')
    {
        $expirydate = '-1';        
    }
    else $expirydate = strtotime($_REQUEST['expiry']);
    $amount =  cleanvar($_POST['amount']);
    if ($amount == '') $amount = 0;
    $unitrate =  cleanvar($_POST['unitrate']);
    if ($unitrate == '') $unitrate = 0;

    $allcontacts = 'no';
    if ($contacts == 'amount') $amount = cleanvar($_REQUEST['amount']);
    elseif ($contacts == 'all') $allcontacts = 'yes';

    $incident_pools = explode(',', "0,{$CONFIG['incident_pools']}");
    $incident_quantity = $incident_pools[$_POST['incident_poolid']];

    $_SESSION['formdata']['add_contract'] = cleanvar($_POST, TRUE, FALSE, FALSE,
                                                     array("@"), array("'" => '"'));

    // Add maintenance to database
    $errors = 0;
    // check for blank site
    if ($site == 0)
    {
        $errors++;
        $_SESSION['formerrors']['add_contract']['site'] = "You must select a site\n";
    }
    // check for blank product
    if ($product == 0)
    {
        $errors++;
        $_SESSION['formerrors']['add_contract']['product'] = "You must select a product\n";
    }
    // check for blank admin contact
    if ($admincontact == 0)
    {
        $errors++;
        $_SESSION['formerrors']['add_contract']['admincontact'] = "You must select an admin contact\n";
    }
    // check for blank expiry day
    if (!isset($expirydate))
    {
        $errors++;
        $_SESSION['formerrors']['add_contract']['expirydate'] = "You must enter an expiry date\n";
    }
    elseif ($expirydate < $now AND $expirydate != -1)
    {
        $errors++;
        $_SESSION['formerrors']['add_contract']['expirydate2'] = "Expiry date cannot be in the past\n";
    }
    // check timed sla data and store it
    if ($timed == 'yes' AND trim($unitrate) == '')
    {
        $errors++;
        $_SESSION['formerrors']['add_contract']['unitrate'] = "Unit rate must not be blank\n";
    }

    // add maintenance if no errors
    if ($errors == 0)
    {
        $addition_errors = 0;

        if (empty($productonly))
        {
            $productonly = 'no';
        }

        if ($productonly=='yes')
        {
            $term = 'yes';
        }
        else
        {
            $term = 'no';
        }

        if (empty($reseller) OR $reseller == 0)
        {
            $reseller = "NULL";
        }
        else
        {
            $reseller = "'{$reseller}'";
        }

        if (empty($licence_type) OR $licence_type == 0)
        {
            $licence_type = "NULL";
        }
        else
        {
            $licence_type = "'{$licence_type}'";
        }

        // NOTE above is so we can insert null so browse_contacts etc can see the contract rather than inserting 0
        $sql  = "INSERT INTO `{$dbMaintenance}` (site, product, reseller, expirydate, licence_quantity, licence_type, notes, ";
        $sql .= "admincontact, servicelevelid, incidentpoolid, incident_quantity, productonly, term, supportedcontacts, allcontactssupported) ";
        $sql .= "VALUES ('$site', '$product', $reseller, '$expirydate', '$licence_quantity', $licence_type, '$notes', ";
        $sql .= "'$admincontact', '$servicelevelid', '$incidentpoolid', '$incident_quantity', '$productonly', '$term', '$numcontacts', '$allcontacts')";

        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $maintid = mysql_insert_id();

        if (!$result)
        {
            $addition_errors = 1;
            $addition_errors_string .= "<p class='error'>Addition of contract failed</p>\n";
        }

        // Add service
        $sql = "INSERT INTO `{$dbService}` (contractid, startdate, enddate, creditamount, unitrate) ";
        $sql .= "VALUES ('{$maintid}', '{$startdate}', '{$enddate}', '{$amount}', '{$unitrate}')";
        mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        if (mysql_affected_rows() < 1) trigger_error("Insert failed",E_USER_ERROR);

        $serviceid = mysql_insert_id();
        update_contract_balance($maintid, "New contract", $amount, $serviceid);



        if ($addition_errors == 1)
        {
            // show addition error message
            include ('htmlheader.inc.php');
            echo $addition_errors_string;
            include ('htmlfooter.inc.php');
        }
        else
        {
            // show success message
            trigger('TRIGGER_NEW_CONTRACT', array('contractid' => $maintid, 'userid' => $sit[2]));
            html_redirect("contract_details.php?id=$maintid");
        }
        clear_form_data('add_contract');
    }
    else
    {
        // show error message if errors
        include ('htmlheader.inc.php');
        html_redirect("add_contract.php", FALSE);
    }
}
?>
