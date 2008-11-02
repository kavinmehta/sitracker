<?php
// review_incoming_updates.php - Review/Delete Incident Updates
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Authors: Tom Gerrard, Ivan Lucas <ivanlucas[at]users.sourceforge.net>
//                       Paul Heaney <paulheaney[at]users.sourceforge.net>

// This Page Is Valid XHTML 1.0 Transitional! 31Oct05

@include ('set_include_path.inc.php');
$permission = 42;
require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');


/**
    * @author Tom Gerrard
*/
function generate_row($update)
{
    global $CONFIG, $sit;
    if (strlen($update['bodytext']) > 1003)
    {
        $updatebodytext = substr($update['bodytext'],0,1000).'...';
    }
    else
    {
        $updatebodytext = $update['bodytext'];
    }

    $search = array( '<b>',  '</b>',  '<i>',  '</i>',  '<u>',  '</u>',  '&lt;',  '&gt;');
    $replace = '';
    $updatebodytext = htmlspecialchars(str_replace($search, $replace, $updatebodytext));
    if ($updatebodytext == '') $updatebodytext = '&nbsp;';

    $shade = 'shade1';
    if (!empty($update['fromaddr']))
    {
        // Have a look if we've got a customer or user with this email address
        $sql = "SELECT COUNT(id) FROM `{$GLOBALS['dbContacts']}` WHERE email LIKE '%{$update['fromaddr']}%'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        list($contactmatches) = mysql_fetch_row($result);
        if ($contactmatches > 0) $shade = 'idle';
    }
    $pluginshade = plugin_do('holdingqueue_rowshade',$update);
    $shade = $pluginshade ? $pluginshade : $shade;
    $html_row = "<tr class='$shade'>";
    $html_row .= "<td style='text-align: center'>";
    if (($update['locked'] == $sit[2]) OR empty($update['locked']))
    {
        $html_row .= "<input type='checkbox' name='selected[]' value='".$update['id']."' />";
    }
    $html_row .= "</td>";
    $html_row .= "<td align='center' width='20%'>".date($CONFIG['dateformat_datetime'],$update['timestamp']).'</td>';
    $html_row .= "<td width='20%'>";
    if (!empty($update['contactid'])
        AND strtolower($update['fromaddr']) == strtolower(contact_email($update['contactid'])))
    {
        $contact_realname = contact_realname($update['contactid']);
        $html_row .= $contact_realname;
        $html_row .= " of ".contact_site($update['contactid']);
        if ($update['emailfrom'] != $contact_realname)
        {
            $html_row .= "<br />\n";
            $html_row .= htmlentities($update['emailfrom'],ENT_QUOTES, $GLOBALS['i18ncharset']);
        }
    }
    else
    {
        $html_row .= "{$update['fromaddr']}<br />\n";
        $html_row .= htmlentities($update['emailfrom'],ENT_QUOTES, $GLOBALS['i18ncharset']);
    }
    $html_row .= "</td>";

    $html_row.="<td width='20%'><a href=\"javascript:incident_details_window('{$update['tempid']}','incomingview');\" id='update{$update['id']}' class='info'>";
//     $html_row.="<td width='20%'><a href=\"javascript:void(0);\" id='update{$update['id']}' class='info' style='cursor:help;'>";
    if (empty($update['subject'])) $update['subject'] = $GLOBALS['strUntitled'];
    $html_row .= htmlentities($update['subject'],ENT_QUOTES, $GLOBALS['i18ncharset']);
    $html_row .= '<span>'.parse_updatebody($updatebodytext).'</span></a></td>';

    $span = sprintf($GLOBALS['strByX'], user_realname($update['reason_user']))."<br />";
    $span .= sprintf($GLOBALS['strOnxAtY'], ldate($CONFIG['dateformat_date'], mysql2date($update['reason_time'])), ldate($CONFIG['dateformat_time'], mysql2date($update['reason_time'])));
    
    $html_row .= "<td align='center' width='20%'><a class='info'>{$update['reason']}<span>{$span}</span></a></td>";
    $html_row .= "<td align='center' width='20%'>";
    if (($update['locked'] != $sit[2]) && ($update['locked'] > 0))
    {
        $html_row .= "Locked by ".user_realname($update['locked'],TRUE);
    }
    else
    {
        if ($update['locked'] == $sit[2])
        {
            $html_row.="<a href='{$_SERVER['PHP_SELF']}?unlock={$update['tempid']}' title='Unlock this update so it can be modified by someone else'> {$GLOBALS['strUnlock']}</a> | ";
        }
        else
        {
            $html_row.= "<a href=\"javascript:incident_details_window('{$update['tempid']}','incomingview');\" id='update{$update['id']}' class='info' title='View and lock this held e-mail'>{$GLOBALS['strView']}</a> | ";
        }
        $html_row.= "<a href='delete_update.php?updateid=".$update['id']."&amp;tempid=".$update['tempid']."&amp;timestamp=".$update['timestamp']."' title='Remove this item permanently' onclick=\"return confirm_action('{$GLOBALS['strAreYouSureDelete']}');\"> {$GLOBALS['strDelete']}</a>";
    }
    $html_row .= "</td></tr>\n";
    return $html_row;
}


function deldir($location)
{
    if (substr($location,-1) <> "/")
    $location = $location."/";
    if (is_dir($location))
    {
        $all = opendir($location);
        while ($file = readdir($all))
        {
            if (is_dir($location.$file) && $file <> ".." && $file <> ".")
            {
                deldir($location.$file);
                rmdir($location.$file);
                unset($file);
            }
            elseif (!is_dir($location.$file))
            {
                unlink($location.$file);
                unset($file);
            }
        }
        rmdir($location);
    }
}

$title = $strReviewHeldUpdates;
$refresh = $_SESSION['incident_refresh'];
$selected = $_POST['selected'];
include ('htmlheader.inc.php');

if ($lock = $_REQUEST['lock'])
{
    $lockeduntil = date('Y-m-d H:i:s',$now+$CONFIG['record_lock_delay']);
    $sql = "UPDATE `{$dbTempIncoming}` SET locked='{$sit[2]}', lockeduntil='{$lockeduntil}' ";
    $sql .= "WHERE id='{$lock}' AND (locked = 0 OR locked IS NULL)";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
}
elseif ($unlock = $_REQUEST['unlock'])
{
    $sql = "UPDATE `{$dbTempIncoming}` AS t SET locked=NULL, lockeduntil=NULL ";
    $sql .= "WHERE id='{$unlock}' AND locked = '{$sit[2]}'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
}
else
{
    // Unlock any expired locks
    $nowdatel = date('Y-m-d H:i:s');
    $sql = "UPDATE `{$dbTempIncoming}` SET locked=NULL, lockeduntil=NULL ";
    $sql .= "WHERE UNIX_TIMESTAMP(lockeduntil) < '$now' ";
    mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
}

if ($spam_string == $_REQUEST['delete_all_spam'])
{
    $spam_array = explode(',',$spam_string);
    foreach ($spam_array as $spam)
    {
        $ids = explode('_',$spam);

        $sql = "DELETE FROM `{$dbTempIncoming}` WHERE id='".$ids[1]."' AND SUBJECT LIKE '%SPAMASSASSIN%' AND updateid='".$ids[0]."' LIMIT 1";
        mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        if (mysql_affected_rows() == 1)
        {
            $sql = "DELETE FROM `{$dbUpdates}` WHERE id='".$ids[0]."'";
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
            $path = $CONFIG['attachment_fspath'].'updates/'.$ids[0];
            if (file_exists($path)) deldir($path);
        }
    }
    unset($spam_array);
}

if (!empty($selected))
{
    foreach ($selected as $updateid)
    {
        $sql = "DELETE FROM `{$dbUpdates}` WHERE id='$updateid'";
        mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        $sql = "DELETE FROM `{$dbTempIncoming}` WHERE updateid='$updateid'";
        mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $path = $CONFIG['attachment_fspath'].'updates/'.$updateid;

        deldir($path);

        journal(CFG_LOGGING_NORMAL, 'Incident Log Entry Deleted', "Incident Log Entry $updateid was deleted", CFG_JOURNAL_INCIDENTS, $updateid);
    }
}


    ?>
    <script type="text/javascript">
    <!--
        function submitform()
        {
            document.held_emails.submit();
        }

        function checkAll(checkStatus)
        {
            var frm = document.held_emails.elements;
            for(i = 0; i < frm.length; i++)
            {
                if (frm[i].type == 'checkbox')
                {
                    if (checkStatus)
                    {
                        frm[i].checked = true;
                    }
                    else
                    {
                        frm[i].checked = false;
                    }
                }
            }
        }
        -->
    </script>

<?php

// extract updates
$sql  = "SELECT u.id AS id, u.bodytext AS bodytext, ti.emailfrom AS emailfrom, ti.subject AS subject, ";
$sql .= "u.timestamp AS timestamp, ti.incidentid AS incidentid, ti.id AS tempid, ti.locked AS locked, ";
$sql .= "ti.reason AS reason, ti.reason_user AS reason_user, ti.reason_time AS reason_time, ti.contactid AS contactid, ti.`from` AS fromaddr ";
$sql .= "FROM `{$dbUpdates}` AS u, `{$dbTempIncoming}` AS ti ";
$sql .= "WHERE u.incidentid = 0 AND ti.updateid = u.id ";
$sql .= "ORDER BY timestamp ASC, id ASC";
$result = mysql_query($sql);
if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
$countresults = mysql_num_rows($result);

$spamcount = 0;
if ($countresults > 0)
{
    if ($countresults) mysql_data_seek($result, 0);

    while ($updates = mysql_fetch_array($result))
    {
        if (!stristr($updates['subject'], $CONFIG['spam_email_subject']))
        {
            $queuerows[$updates['id']] = generate_row($updates);
        }
        else
        {
            $spamcount++;
        }
    }
}

$sql = "SELECT * FROM `{$dbIncidents}` WHERE owner='0' AND status!='2'";
$resultnew = mysql_query($sql);
if (mysql_num_rows($resultnew) >= 1)
{
    while ($new = mysql_fetch_object($resultnew))
    {
        // Get Last Update
        list($update_userid, $update_type, $update_currentowner, $update_currentstatus, $update_body, $update_timestamp, $update_nextaction, $update_id) = incident_lastupdate($new->id);
        $update_body = parse_updatebody($update_body);
        $html = "<tr class='shade1'>";
        $html .= "<td align='center'>".ldate($CONFIG['dateformat_datetime'], $new->opened)."</td>";
        $html .= "<td>".contact_realname($new->contact)."</td>";
        $html .= "<td>".product_name($new->product)." / ".software_name($new->softwareid)."<br />";
        $html .= "[{$new->id}] <a href=\"javascript:incident_details_window('{$new->id}','holdingview');\" class='info'>{$new->title}<span>{$update_body}</span></a></td>";
        $html .= "<td style='text-align:center;'>Unassigned</td>";
        $html .= "<td style='text-align:center;'>";
        $html .= "<a href= \"javascript:incident_details_window('{$new->id}','holdingview');\" title='View this incident'>View</a> | ";
        $html .= "<a href= \"javascript:wt_winpopup('reassign_incident.php?id={$new->id}&amp;reason=Initial%20assignment%20to%20engineer&amp;popup=yes','mini');\" title='Assign this incident'>Assign</a></td>";
        $html .= "</tr>";
        $incidentqueuerows[$update_timestamp] = $html;
    }
}

$realemails = $countresults - $spamcount;
//$totalheld = $countresults + mysql_num_rows($resultnew) - $spamcount;
if (is_array($queuerows))
{
    echo "<h2>".icon('email', 32)." {$strIncomingEmail}</h2>";

    echo "<p align='center'>{$strIncomingEmailText}</p>";
    echo "<form action='{$_SERVER['PHP_SELF']}' name='held_emails'  method='post'>";
    echo "<table align='center' style='width: 95%'>";
    echo "<tr>";
    echo "<th>";
    if ($realemails > 0)
    {
        echo "<input type='checkbox' name='selectAll' value='CheckAll' onclick=\"checkAll(this.checked);\" />";
    }

    echo "</th>";
    echo "<th>{$strDate}</th>";
    echo "<th>{$strFrom}</th>";
    echo "<th>{$strSubject}</th>";
    echo "<th>{$strMessage}</th>";
    echo "<th>{$strOperation}</th>";
    echo "</tr>";
    sort($queuerows);
    foreach ($queuerows AS $row)
    {
        echo $row;
    }

    if ($realemails > 0)
    {
        echo "<tr><td>";
        echo "<a href=\"javascript: submitform()\" onclick=\"return confirm_action('{$strAreYouSureDelete}');\">{$strDelete}</a>";
        echo "</td></tr>";
    }
    echo "</table>\n";
    echo "</form>";
}
else if ($spamcount == 0)
{
    echo "<h2>".icon('email', 32)." {$strHoldingQueue}</h2>";
    echo "<p align='center'>{$strNoRecords}</p>";
}

if (is_array($incidentqueuerows))
{
    if (sizeof($incidentqueuerows) > 0)
    {
        echo "<h2>".icon('support', 32)." Unassigned Incidents</h2>";

        echo "<table align='center' style='width: 95%'>";
        echo "<th>{$strDate}</th>";
        echo "<th>{$strFrom}</th>";
        echo "<th>{$strSubject}</th>";
        echo "<th>{$strMessage}</th>";
        echo "<th>{$strOperation}</th>";
        echo "</tr>";
        sort($incidentqueuerows);
        foreach ($incidentqueuerows AS $row)
        {
            echo $row;
        }
        echo "</table>";
    }
}

if ($spamcount > 0)
{
    echo "<h2>{$strSpamEmail}";
    if ($spamcount > 1) echo "s"; // FIXME i18n cant we do this ?
    echo " ({$spamcount} {$strTotal})</h2>\n";
    echo "<p align='center'>{$strIncomingEmailSpam}</p>";

    // Reset back for 'nasty' emails
    if ($countresults) mysql_data_seek($result, 0);

    echo "<table align='center' style='width: 95%;'>";
    echo "<tr><th /><th>{$strDate}</th><th>{$strFrom}</th>";
    echo "<th>{$strSubject}</th><th>{$strMessage}</th>";
    echo "<th>{$strOperation}</th></tr>\n";

    while ($updates = mysql_fetch_array($result))
    {
        if (stristr($updates['subject'],$CONFIG['spam_email_subject']))
        {
            echo generate_row($updates);
            $spam_array[] = $updates['id'].'_'.$updates['tempid'];
        }
    }
    echo "</table>";

    if (is_array($spam_array))
    {
        echo "<p align='center'><a href={$_SERVER['PHP_SELF']}?delete_all_spam=".implode(',',$spam_array).'>{$strDeleteAllSpam}</a></p>';
    }

    echo "<br /><br />"; //gap
}


$sql = "SELECT i.id, i.title, c.forenames, c.surname, s.name ";
$sql .= "FROM `{$dbIncidents}` AS i,`{$dbContacts}` AS c, `{$dbSites}` AS s ";
$sql .= "WHERE i.status = 8 AND i.contact = c.id AND c.siteid = s.id ";
$sql .= "ORDER BY s.id, i.contact"; //awaiting customer action
$resultchase = mysql_query($sql);
if (mysql_num_rows($resultchase) >= 1)
{
    $shade = 'shade1';
    while ($chase = mysql_fetch_object($resultchase))
    {
        $sql_update = "SELECT * FROM `{$dbUpdates}` WHERE incidentid = {$chase->id} ORDER BY timestamp DESC LIMIT 1";
        $result_update = mysql_query($sql_update);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

        $obj_update = mysql_fetch_object($result_update);

        if ($obj_update->type == 'auto_chase_phone' OR $obj_update->type == 'auto_chase_manager')
        {
            if (empty($html_chase))
            {
                $html_chase .= "<br />";
                $html_chase .= "<h2>{$strIncidentsRequiringChaseByPhone}</h2>";
                $html_chase .= "<table align='center' style='width: 95%'>";
                $html_chase .= "<tr><th>{$strIncident} {$strID}</th>";
                $html_chase .= "<th>{$strIncidentTitle}</th><th>{$strContact}</th>";
                $html_chase .= "<th>{$strSite}</th><th>{$strType}</th></tr>";
            }

            if ($obj_update->type == "auto_chase_phone")
            {
                $type = $strChasePhone;
            }
            else
            {
                $type = $strChaseManager;
            }

            // show
            $html_chase .= "<tr class='{$shade}'><td>";
            $html_chase .= "<a href=\"javascript:incident_details_window('{$obj_update->incidentid}','incident{$obj_update->incidentid}')\" class='info'>{$obj_update->incidentid}</a></td>";
            $html_chase .= "<td>{$chase->title}</td><td>{$chase->forenames} {$chase->surname}</td>";
            $html_chase .= "<td>{$chase->name}</td><td>{$type}</td></tr>";

            if ($shade == 'shade1') $shade = 'shade2';
            else $shade = 'shade1';
        }
    }
}

if (!empty($html_chase))
{
    echo $html_chase;
    echo "</table>";
}

$sql = "SELECT * FROM `{$dbTempAssigns}` AS t, `{$dbIncidents}` AS i ";
$sql .= "WHERE t.incidentid = i.id AND assigned='no' ";
$result = mysql_query($sql);

if (mysql_num_rows($result) >= 1)
{
    $show = FALSE;
    $rhtml = "<br />\n";

    $rhtml .= "<h2>".icon('reassign', 32, $strPendingReassignments);
    $rhtml .= " {$strPendingReassignments}</h2>";
    $rhtml .= "<p align='center'>{$strAutoReassignmentsThatCouldntBeMade}</p>";
    $rhtml .= "<table align='center' style='width: 95%;'>";
    $rhtml .= "<tr><th title='{$strLastUpdated}'>{$strDate}</th><th title='{$strCurrentOwner}'>{$strFrom}</th>";
    $rhtml .= "<th title='{$strIncidentTitle}'>{$strSubject}</th><th>{$strMessage}</th>";
    $rhtml .= "<th>{$strOperation}</th></tr>\n";

    while ($assign = mysql_fetch_object($result))
    {
        // $originalownerstatus=user_status($assign->originalowner);
        $useraccepting = strtolower(user_accepting($assign->originalowner));
        if (($assign->owner == $assign->originalowner || $assign->towner == $assign->originalowner) AND $useraccepting == 'no')
        {
            $show = TRUE;
            $rhtml .= "<tr class='shade1'>";
            $rhtml .= "<td align='center'>".ldate($CONFIG['dateformat_datetime'], $assign->lastupdated)."</td>";
            $rhtml .= "<td>".user_realname($assign->originalowner,TRUE)."</td>";
            $rhtml .= "<td>".software_name($assign->softwareid)."<br />[<a href=\"javascript:wt_winpopup('incident_details.php?id={$assign->id}&amp;popup=yes', 'mini')\">{$assign->id}</a>] ".$assign->title."</td>";
            $userstatus = userstatus_name($assign->userstatus);
            $usermessage = user_message($assign->originalowner);
            $username = user_realname($assign->originalowner,TRUE);
            $rhtml .= "<td>".sprintf($strOwnerXAndNotAccepting, $userstatus)."<br />{$usermessage}</td>";
            $backupid = software_backup_userid($assign->originalowner, $assign->softwareid);
            $backupname = user_realname($backupid,TRUE);
            $reason = urlencode(trim("{$strPreviousIncidentOwner} ($username) {$userstatus}  {$usermessage}"));
            $rhtml .= "<td>";
            if ($backupid >= 1)
            {
                $rhtml .= "<a href=\"javascript:wt_winpopup('reassign_incident.php?id={$assign->id}&amp;reason={$reason}&amp;backupid={$backupid}&amp;asktemp=temporary&amp;popup=yes','mini');\" title='Re-assign this incident to {$backupname}'>{$strAssignToBackup}</a> | ";
            }

            $rhtml .= "<a href=\"javascript:wt_winpopup('reassign_incident.php?id={$assign->id}&amp;reason={$reason}&amp;asktemp=temporary&amp;popup=yes','mini');\" title='Re-assign this incident to another engineer'>Assign to other</a> | <a href='set_user_status.php?mode=deleteassign&amp;incidentid={$assign->incidentid}&amp;originalowner={$assign->originalowner}' title='Ignore this reassignment and delete this notice'>Ignore</a></td>";
            $rhtml .= "</tr>\n";
        }
        elseif ($assign->owner != $assign->originalowner AND $useraccepting == 'yes')
        {
            $show = TRUE;
            // display a row to assign the incident back to the original owner
            $rhtml .= "<tr class='shade2'>";
            $rhtml .= "<td>".ldate($CONFIG['dateformat_datetime'], $assign->lastupdated)."</td>";
            $rhtml .= "<td>".user_realname($assign->owner,TRUE)."</td>";
            $rhtml .= "<td>[<a href=\"javascript:wt_winpopup('incident_details.php?id={$assign->id}&amp;popup=yes', 'mini')\">{$assign->id}</a>] {$assign->title}</td>";
            $userstatus = user_status($assign->originalowner);
            $userstatusname = userstatus_name($userstatus);
            $origstatus = userstatus_name($assign->userstatus);
            $usermessage = user_message($assign->originalowner);
            $username = user_realname($assign->owner,TRUE);
            $rhtml .= "<td>".sprintf($strOwnerXAcctingAgain, $userstatusname)."<br />{$usermessage}</td>";
            $originalname = user_realname($assign->originalowner,TRUE);
            $reason = urlencode(trim("{$originalname} is now accepting incidents again. Previous status {$origstatus} and not accepting."));  // FIXME i18n
            $rhtml .= "<td>";
            $rhtml .= "<a href=\"javascript:wt_winpopup('reassign_incident.php?id={$assign->id}&amp;reason={$reason}&amp;originalid={$assign->originalowner}&amp;popup=yes','mini');\" title='Re-assign this incident to {$originalname}'>Return to original owner</a> | ";

            $rhtml .= "<a href=\"javascript:wt_winpopup('reassign_incident.php?id={$assign->id}&amp;reason={$reason}&amp;asktemp=temporary&amp;popup=yes','mini');\" title='Re-assign this incident to another engineer'>{$strAssignToOther}</a> | <a href='set_user_status.php?mode=deleteassign&amp;incidentid={$assign->incidentid}&amp;originalowner={$assign->originalowner}' title='Ignore this reassignment and delete this notice'>{$strIgnore}</a></td>";
            $rhtml .= "</tr>\n";
        }
    }
    $rhtml .= "</table>\n";
}

if ($show)
{
    echo $rhtml;
}

include ('htmlfooter.inc.php');
?>
