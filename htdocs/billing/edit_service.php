<?php
// billing/edit_service.php - Allows balances to be edited or transfered
// TODO description
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author:  Paul Heaney Paul Heaney <paulheaney[at]users.sourceforge.net>

@include('set_include_path.inc.php');
$permission =  80;

require_once('db_connect.inc.php');
require_once('functions.inc.php');
// This page requires authentication
require_once('auth.inc.php');

$mode = cleanvar($_REQUEST['mode']);
$amount = cleanvar($_REQUEST['amount']);
$contractid = cleanvar($_REQUEST['contractid']);
$sourceservice = cleanvar($_REQUEST['sourceservice']);
$destinationservice = cleanvar($_REQUEST['destinationservice']);
$reason = cleanvar($_REQUEST['reason']);
$serviceid = cleanvar($_REQUEST['serviceid']);
if (empty($mode)) $mode = 'showform';

switch ($mode)
{
    case 'editservice':
        if (user_permission($sit[2], 80) == FALSE)
        {
            header("Location: {$CONFIG['application_webpath']}noaccess.php?id=80");
            exit;
        }
        else
        {
            $sql = "SELECT * FROM `{$dbService}` WHERE serviceid = {$serviceid}";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            
            include('htmlheader.inc.php');
            
            if (mysql_numrows($result) != 1)
            {
                echo "<h2>No service with ID {$servicid} found</h2>";
            }
            else
            {
                $obj = mysql_fetch_object($result);
                
                echo "<h5>".sprintf($strMandatoryMarked, "<sup class='red'>*</sup>")."</h5>";
                echo "<form name='serviceform' action='{$_SERVER['PHP_SELF']}' method='post' onsubmit='return confirm_submit(\"{$strAreYouSureMakeTheseChanges}\");'>";
                echo "<table align='center' class='vertical'>";
            
                echo "<tr><th>{$strStartDate}</th>";
                echo "<td><input type='text' name='startdate' id='startdate' size='10'";
                echo "value='{$obj->startdate}' />";
                echo date_picker('serviceform.startdate');
                echo "</td></tr>";
            
                echo "<tr><th>{$strEndDate}<sup class='red'>*</sup></th>";
                echo "<td><input type='text' name='enddate' id='enddate' size='10'";
                echo "value='{$obj->enddate}' />";
                echo date_picker('serviceform.enddate');
                echo " <input type='checkbox' name='noexpiry' ";
                if($_SESSION['formdata']['add_contract']['noexpiry'] == "on")
                {
                    echo "checked='checked' ";
                }
                echo "onclick=\"$('enddate').value='';\" /> {$strUnlimited}</td></tr>\n";
            
                echo "<tr><th>{$strNotes}</th><td>";
                echo "<textarea rows='5' cols='20' name='notes'>{$obj->notes}</textarea></td></tr>";
            
                if ($obj->balance == $obj->creditamount)
                {
                    echo "<input type='hidden' name='editbilling' id='editbilling' value='true' />";
                    echo "<input type='hidden' name='originalcredit' id='originalcredit' value='{$obj->creditamount}' />";
                    echo "<tr><th>{$strBilling}</th>";
                    echo "<td>";
                    echo "<label>";
                    echo "<input type='checkbox' id='billperunit' name='billperunit' value='yes' onchange=\"addservice_showbilling();\" /> ";
                    echo "{$strPerUnit}</label>";
                    echo "<label>";
                    echo "<input type='checkbox' id='billperincident' name='billperincident' value='yes' onchange=\"addservice_showbilling();\" /> ";
                    echo "{$strPerIncident}</label>";
                    echo "</td></tr>\n";
                
                    echo "<tbody id='billingsection' style='display:none'>"; //FIXME not XHTML
                
                    echo "<tr><th>{$strCreditAmount}</th>";
                    echo "<td>{$CONFIG['currency_symbol']} <input type='text' name='amount' size='5' value='{$obj->creditamount}' />";
                    echo "</td></tr>";
                
                    echo "<tr id='unitratesection' style='display:none'><th>{$strUnitRate}</th>";
                    echo "<td>{$CONFIG['currency_symbol']} <input type='text' name='unitrate' size='5' value='{$obj->unitrate}' />";
                    echo "</td></tr>";
                
                    echo "<tr id='incidentratesection' style='display:none'><th>{$strIncidentRate}</th>";
                    echo "<td>{$CONFIG['currency_symbol']} <input type='text' name='incidentrate' size='5' value='{$obj->incidentrate}' />";
                    echo "</td></tr>";
                
                    echo "</tbody>"; //FIXME not XHTML
                }
                else
                {
                    echo "<input type='hidden' name='editbilling' id='editbilling' value='false' />";
                    echo "<tr><th colspan='2'>Unable to change amounts or rates as the service has been used.</th></tr>";
                }
            //  Not sure how applicable daily rate is, INL 4Apr08
            //     echo "<tr><th>{$strDailyRate}</th>";
            //     echo "<td>{$CONFIG['currency_symbol']} <input type='text' name='dailyrate' size='5' />";
            //     echo "</td></tr>";
            
                echo "</table>\n\n";
                echo "<input type='hidden' name='contractid' value='{$contractid}' />";
                echo "<p><input name='submit' type='submit' value=\"{$strUpdate}\" /></p>";
                echo "<input type='hidden' name='serviceid' id='serviceid' value='{$serviceid}' />";
                echo "<input type='hidden' name='mode' id='mode' value='doupdate' />";
                echo "</form>\n";
            
                echo "<p align='center'><a href='../contract_details.php?id={$contractid}'>{$strReturnWithoutSaving}</a></p>";
            }
            include('htmlfooter.inc.php');
        }
        
        break;
    case 'doupdate':
        $sucess = true;
        if (user_permission($sit[2], 80) == FALSE)
        {
            header("Location: {$CONFIG['application_webpath']}noaccess.php?id=80");
            exit;
        }
        else
        {
            $originalcredit = cleanvar($_REQUEST['originalcredit']);
            
            $startdate = strtotime($_REQUEST['startdate']);
            if ($startdate > 0) $startdate = date('Y-m-d',$startdate);
            else $startdate = date('Y-m-d',$now);
            $enddate = strtotime($_REQUEST['enddate']);
            if ($enddate > 0) $enddate = date('Y-m-d',$enddate);
            else $enddate = date('Y-m-d',$now);

            $notes = cleanvar($_REQUEST['notes']);

            $editbilling = cleanvar($_REQUEST['editbilling']);
            
            if ($editbilling == "true")
            {
                $amount =  cleanvar($_POST['amount']);
                if ($amount == '') $amount = 0;
                $unitrate =  cleanvar($_POST['unitrate']);
                if ($unitrate == '') $unitrate = 0;
                $incidentrate =  cleanvar($_POST['incidentrate']);
                if ($incidentrate == '') $incidentrate = 0;
                
                $updateBillingSQL = ", creditamount = '{$amount}', balance = '{$amount}', unitrate = '{$unitrate}', incidentrate = '{$incidentrate}' ";
            }
        
            if ($amount != $originalcredit)
            {
                $adjust = $amount - $originalcredit;
                
                update_contract_balance($contractid, "Credit adjusted to", $adjust, $serviceid);
            }

            $sql = "UPDATE `{$dbService}` SET startdate = '{$startdate}', enddate = '{$enddate}' {$updateBillingSQL}";
            $sql .= ", notes = '{$notes}' WHERE serviceid = {$serviceid}";
            echo $sql;
            
            mysql_query($sql);
            if (mysql_error())
            {
                trigger_error(mysql_error(),E_USER_ERROR);
                $sucess = false;
            }
            
            if (mysql_affected_rows() < 1)
            {
                trigger_error("Insert failed",E_USER_ERROR);
                $sucess = false;
            }
        
            $sql = "SELECT expirydate FROM `{$dbMaintenance}` WHERE id = {$contractid}";

            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            
            if (mysql_num_rows($result) > 0)
            {
                $obj = mysql_fetch_object($result);
                if ($obj->expirydate < strtotime($enddate))
                {
                    $update = "UPDATE `$dbMaintenance` ";
                    $update .= "SET expirydate = '".strtotime($enddate)."' ";
                    $update .= "WHERE id = {$contractid}";
                    mysql_query($update);
                    if (mysql_error())
                    {
                        trigger_error(mysql_error(),E_USER_ERROR);
                        $sucess = false;
                    }
                    
                    if (mysql_affected_rows() < 1)
                    {
                        trigger_error("Expiry of contract update failed",E_USER_ERROR);
                        $sucess = false;
                    }
                }
            }
            
            if ($sucess)
            {
                html_redirect("{$CONFIG['application_webpath']}contract_details.php?id={$contractid}", TRUE, 'Sucessfully udpated');
            }
            else
            {
                html_redirect("{$CONFIG['application_webpath']}contract_details.php?id={$contractid}", FALSE, 'NOT udpated');
            }

            
        }
        break;
    case 'showform':
        // Will be passed a $sourceservice to modify
        if (user_permission($sit[2], 79) == FALSE)
        {
            header("Location: {$CONFIG['application_webpath']}noaccess.php?id=79");
            exit;
        }
        else
        {
            include('htmlheader.inc.php');
            echo "<h2>One time balance editor</h2>";
            
            echo "<form name='serviceform' action='{$_SERVER['PHP_SELF']}' method='post' onsubmit='return confirm_submit(\"{$strAreYouSureMakeTheseChanges}\");'>";
    
            echo "<table align='center' class='vertical'>";
            echo "<tr><th>{$strEdit}</th><td>{$sourceservice}</td></tr>";
            echo "<tr><th></th><td>";
            echo "<input type='radio' name='mode' id='edit' value='edit' checked='checked' onclick=\"setDivState('transfersection', 'false');\" /> {$strEdit} ";
            echo "<input type='radio' name='mode' id='transfer' value='transfer' onclick=\"setDivState('transfersection', 'true');\" /> Transfer ";
            echo "</td></tr>";
            echo "<tbody  style='display:none' id='transfersection' ><tr><th>Destination Account:</th>";
            echo "<td>";
            
            
            // Only allow transfers on the same contractid
            $sql = "SELECT * FROM `{$dbService}` WHERE contractid = '{$contractid}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            
            if (mysql_numrows($result) > 0)
            {
                echo "<select name='destinationservice'>\n";
                
                while ($obj = mysql_fetch_object($result))
                {
                    echo "<option value='{$obj->serviceid}'>{$obj->serviceid} - {$obj->enddate} {$CONFIG['currency_symbol']}{$obj->balance}</option>\n";
                }
                
                echo "</select>\n";
            }
            
            echo "</td></tr></tbody>\n";
            
            echo "<tr><th>{$strAmount}</th><td><input type='textbox' name='amount' id='amount' /></td></tr>";
            echo "<tr><th>{$strReason}</th><td><input type='textbox' name='reason' id='reason' /></td></tr>";
            
            echo "</table>";
            echo "<p align='center'><input type='submit' name='runreport' value='Do' /></p>";
        
            echo "<input type='hidden' name='sourceservice' value='{$sourceservice}' />";
            echo "<input type='hidden' name='contractid' value='{$contractid}' />";
        
            echo "</form>";
        }
        include('htmlfooter.inc.php');
        break;
    case 'edit':
        if (user_permission($sit[2], 79) == FALSE)
        {
            header("Location: {$CONFIG['application_webpath']}noaccess.php?id=79");
            exit;
        }
        else
        {
            //function update_contract_balance($contractid, $description, $amount, $serviceid='')
            $status = update_contract_balance($contractid, $reason, $amount, $sourceservice);
            if ($status)
            {
                html_redirect("{$CONFIG['application_webpath']}contract_details.php?id={$contractid}", TRUE, 'Balance sucessfully udpated');
            }
            else
            {
                html_redirect("{$CONFIG['application_webpath']}contract_details.php?id={$contractid}", FALSE, 'Balance NOT udpated');
            }
        }
        break;
    case 'transfer': // TODO check this logic is what people expect
        if (user_permission($sit[2], 79) == FALSE)
        {
            header("Location: {$CONFIG['application_webpath']}noaccess.php?id=79");
            exit;
        }
        else
        {
            $status = update_contract_balance($contractid, $reason, ($amount*-1), $sourceservice);
            if ($status)
            {
                $status = update_contract_balance($contractid, $reason, $amount, $destinationservice);
                if ($status) html_redirect("{$CONFIG['application_webpath']}contract_details.php?id={$contractid}", TRUE, 'Transfer sucessful');
                else html_redirect("{$CONFIG['application_webpath']}contract_details.php?id={$contractid}", FALSE, 'Transfer failed');
            }
            html_redirect('main.php', FALSE, 'Transfer failed');            
        }
        break;
    
}

?>
