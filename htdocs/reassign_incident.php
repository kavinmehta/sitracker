<?php
// reassign_incident.php - Form for re-assigning an incident to another user
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
// FIXME i18n

$permission=13; // Reassign Incident
require('db_connect.inc.php');
require('functions.inc.php');
// This page requires authentication
require('auth.inc.php');

$forcepermission = user_permission($sit[2],40);

// External variables
$bodytext = cleanvar($_REQUEST['bodytext']);
$id = cleanvar($_REQUEST['id']);
$incidentid=$id;
$backupid = cleanvar($_REQUEST['backupid']);
$originalid = cleanvar($_REQUEST['originalid']);
$reason = cleanvar($_REQUEST['reason']);
$action = cleanvar($_REQUEST['action']);

switch ($action)
{
    case 'save':
        // External variables
        $tempnewowner = cleanvar($_REQUEST['tempnewowner']);
        $permnewowner = cleanvar($_REQUEST['permnewowner']);
        $newstatus = cleanvar($_REQUEST['newstatus']);
        $userid = cleanvar($_REQUEST['userid']);
        $temporary  = cleanvar($_REQUEST['temporary']);
        $id = cleanvar($_REQUEST['id']);

        // Retrieve current incident details
        $sql = "SELECT * FROM incidents WHERE id='$id' LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $incident = mysql_fetch_object($result);

        if ($newstatus != $incident->status)
        $bodytext = "Status: ".incidentstatus_name($incident->status)." -&gt; <b>" . incidentstatus_name($newstatus) . "</b>\n\n" . $bodytext;

        // Update incident
        $sql = "UPDATE incidents SET ";
        if ($temporary=='yes') $sql .= "towner='{$userid}', ";
        elseif ($temporary != 'yes' AND $sit[2]==$incident->owner) $sql .= "owner='{$sit[2]}', towner=0, "; // make current user = owner
        elseif ($temporary == 'yes' AND $userid=$incident->owner) $sql .= "owner='{$userid}', towner=0, ";
        else  $sql .= "owner='{$userid}', ";
        $sql .= "status='$newstatus', lastupdated='$now' WHERE id='$id' LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        // add update
        if (strtolower(user_accepting($userid)) != "yes")
        {
            $bodytext = "(Incident assignment was forced because the user was not accepting)<hr>\n" . $bodytext;   // FIXME i18n
        }

        if ($temporary=='yes') $assigntype = 'tempassigning';
        else $assigntype = 'reassigning';

        if ($_REQUEST['cust_vis']=='yes') $customervisibility='show';
        else $customervisibility='hide';

        $sql  = "INSERT INTO updates (incidentid, userid, bodytext, type, timestamp, currentowner, currentstatus, customervisibility) ";
        $sql .= "VALUES ($id, $sit[2], '$bodytext', '$assigntype', '$now', ";
        if ($temporary=='yes') $sql .= "'{$userid}', ";
        elseif ($temporary != 'yes' AND $sit[2]==$incident->owner) $sql .= "'{$sit[2]}', ";
        else $sql .= "'{$userid}', ";
        $sql .= "'$newstatus', '$customervisibility')";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
//         echo "<p>$sql</p>";

        // Remove any tempassigns that are pending for this incident
        $sql = "DELETE FROM tempassigns WHERE incidentid='$id'";
        mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        if (user_notification_on_reassign($userid)=='true') send_template_email('INCIDENT_REASSIGNED_USER_NOTIFY', $id);

        journal(CFG_LOGGING_FULL,'Incident Reassigned', "Incident $id reassigned to user id $newowner", CFG_JOURNAL_SUPPORT, $id);

        confirmation_page("2", "incident_details.php?id=" . $id, "<h2>Reassignment Successful</h2><h5>{$strPleaseWaitRedirect}...</h5>");
        break;

    default:
        // No submit detected show reassign form
        $title = $strReassign;
        include('incident_html_top.inc.php');


        $sql = "SELECT * FROM incidents WHERE id='$id' LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $incident = mysql_fetch_object($result);

        if ($incident->towner >0 AND $incident->owner == $sit[2]) $suggested = suggest_reassign_userid($id);
        else $suggested = suggest_reassign_userid($id, $incident->owner);

        echo "<form name='assignform' action='{$_SERVER['PHP_SELF']}?id={$id}' method='post'>";

        $sql = "SELECT * FROM users WHERE status!=0 ";
        $sql .= "AND NOT id=$incident->owner ";
        if ($suggested) $sql .= "AND NOT id='$suggested' ";
        if (!$forcepermission) $sql .= "AND accepting='Yes' ";
        $sql .= "ORDER BY realname";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        echo "<p>{$strOwner}: <strong>";
        if ($sit[2]==$incident->owner) echo "{$strYou} (".user_realname($incident->owner,TRUE).")";
        else echo user_realname($incident->owner,TRUE);
        echo "</strong>";

        if ($incident->towner > 0)
        {
            echo " (Temp: "; // FIXME i18n
            if ($sit[2]==$incident->towner) echo $strYou;
            else echo user_realname($incident->towner,TRUE);
            echo ")";
        }
        echo "</p>";

        echo "<div id='reassignlist'>";
        echo "<table align='center'>";
        echo "<tr>
              <th colspan='2'>{$strReassignTo}:</th>
              <th colspan='5'>{$strIncidentsinQueue}</th>
              <th>{$strAccepting}</th>
              </tr>";
        echo "<tr>
              <th>{$strName}</th>
              <th>{$strStatus}</th>
              <th align='center'>{$strActionNeeded} / {$strOther}</th>";
        echo "<th align='center'>".priority_icon(4)."</th>";
        echo "<th align='center'>".priority_icon(3)."</th>";
        echo "<th align='center'>".priority_icon(2)."</th>";
        echo "<th align='center'>".priority_icon(1)."</th>";
        echo "<th></th></tr>\n";

        if ($suggested)
        {
            // Suggested user is shown as the first row
            $sugsql = "SELECT * FROM users WHERE id='$suggested' LIMIT 1";
            $sugresult = mysql_query($sugsql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
            $suguser = mysql_fetch_object($sugresult);
            echo "<tr class='idle'>";
            echo "<td><label><input type='radio' name='userid' checked='checked' value='{$suguser->id}' /> ";
            // Have a look if this user has skills with this software
            $ssql = "SELECT softwareid FROM usersoftware WHERE userid={$suguser->id} AND softwareid={$incident->softwareid} ";
            $sresult = mysql_query($ssql);
            if (mysql_error()) trigger_error("MySQL Query Error".mysql_error(), E_USER_ERROR);
            if (mysql_num_rows($sresult) >=1 ) echo "<strong>".stripslashes($suguser->realname)."</strong>";
            else echo stripslashes($suguser->realname);
            echo "</label></td>";
            echo "<td>".user_online($suguser->id).userstatus_name($suguser->status)."</td>";
            $incpriority = user_incidents($suguser->id);
            $countincidents = ($incpriority['1']+$incpriority['2']+$incpriority['3']+$incpriority['4']);

            if ($countincidents >= 1) $countactive=user_activeincidents($suguser->id);
            else $countactive=0;
            $countdiff=$countincidents-$countactive;
            echo "<td align='center'>$countactive / {$countdiff}</td>";
            echo "<td align='center'>".$incpriority['4']."</td>";
            echo "<td align='center'>".$incpriority['3']."</td>";
            echo "<td align='center'>".$incpriority['2']."</td>";
            echo "<td align='center'>".$incpriority['1']."</td>";
            echo "<td align='center'>";
            echo $suguser->accepting=='Yes' ? $strYes : "<span class='error'>{$strNo}</span>";
            echo "</td>";
            echo "</tr>\n";
        }
        $countusers = mysql_num_rows($result);
        if ($countusers >= 1)
        {
            // Other users are shown in a optional section
            if ($suggested) echo "<tbody id='moreusers' style='display:none;'>";
            $shade='shade1';

            while ($users = mysql_fetch_object($result))
            {
                echo "<tr class='$shade'>";
                echo "<td><label><input type='radio' name='userid' value='{$users->id}' /> ";
                // Have a look if this user has skills with this software
                $ssql = "SELECT softwareid FROM usersoftware WHERE userid={$users->id} AND softwareid={$incident->softwareid} ";
                $sresult = mysql_query($ssql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                if (mysql_num_rows($sresult) >=1 ) echo "<strong>".stripslashes($users->realname)."</strong>";
                else echo stripslashes($users->realname);
                echo "</label></td>";
                echo "<td>".user_online($users->id).userstatus_name($users->status)."</td>";
                $incpriority = user_incidents($users->id);
                $countincidents = ($incpriority['1']+$incpriority['2']+$incpriority['3']+$incpriority['4']);

                if ($countincidents >= 1) $countactive=user_activeincidents($users->id);
                else $countactive=0;
                $countdiff=$countincidents-$countactive;
                echo "<td align='center'>$countactive / {$countdiff}</td>";
                echo "<td align='center'>".$incpriority['4']."</td>";
                echo "<td align='center'>".$incpriority['3']."</td>";
                echo "<td align='center'>".$incpriority['2']."</td>";
                echo "<td align='center'>".$incpriority['1']."</td>";
                echo "<td align='center'>";
                echo $users->accepting=='Yes' ? $strYes : "<span class='error'>{$strNo}</span>";
                echo "</td>";
                echo "</tr>\n";
                if ($shade=='shade1') $shade='shade2';
                else $shade='shade1';
            }
            if ($suggested) echo "</tbody>";
            echo "</table><br />";
            if ($suggested) echo "<p id='morelink'><a href=\"#\" onclick=\"$('moreusers').toggle();$('morelink').toggle();\">{$countusers} {$strMore}</a></p>";
        }
        echo "</div>\n"; // reassignlist

        echo "<table class='vertical'>";


//         if (empty($_REQUEST['backupid']) AND empty($_REQUEST['originalid']))
//         {
//         }
//         elseif (!empty($originalid))
//         {
//             echo "<tr><th>{$strReassign}:</th>";
//             echo "<td>Reassign to original engineer (".user_realname($originalid,TRUE).")";
//             echo "<input type='hidden' name='permnewowner' value='{$originalid}' />";
//             echo "<input type='hidden' name='permassign' value='{$originalid}' />";
//             echo "</td></tr>\n";
//         }
//         elseif (!empty($backupid))
//         {
//             echo "<tr><th>{$strReassign}:</strong>:</th>";
//             echo "<td>To Substitute Engineer (".user_realname($backupid,TRUE).")";
//             echo "<input type='hidden' name='tempnewowner' value='{$backupid}' />";
//             echo "<input type='hidden' name='tempassign' value='{$originalid}' />";
//             echo "</td></tr>\n";
//         }

        echo "<tr><td colspan='2'><br />{$strReassignText}</td></tr>\n";
        echo "<tr><th>{$strUpdate}:</th>";
        echo "</th><td>";
        echo "<textarea name='bodytext' wrap='soft' rows='10' cols='65'>";
        if (!empty($reason)) echo $reason;
        echo "</textarea>";
        echo "</td></tr>\n";
        // FIXME i18n
        if ($incident->towner > 0 AND ($sit[2] == $incident->owner OR $sit[2] == $incident->towner))
        {
            echo "<tr><th>Temporary:</th><td>";
            echo "<label><input type='radio' name='temporary' value='yes' checked='checked' onchange=\"$('reassignlist').show();\" /> Change temporary ownership</label>";
            echo "<label><input type='radio' name='temporary' value='no' onchange=\"$('reassignlist').hide();\" /> Remove temporary ownership</label> ";
            echo "</td></tr>\n";
        }
        else
        {
            echo "<tr><th>Temporary:</th><td><label><input type='checkbox' name='temporary' value='yes' ";
            if ($sit[2] != $incident->owner AND $sit[2] != $incident->towner) echo "disabled='disabled' ";
            echo "/> ";
            if ($incident->towner > 0) echo "Change temporary ownership";
            else echo "Assign Temporarily";
            echo "</label></td></tr>\n";
        }
        echo "<tr><th>{$strVisibility}:</th><td><label><input type='checkbox' name='cust_vis' value='yes' /> {$strVisibleToCustomer}</label></td></tr>\n";

        echo "<tr><th>{$strNewIncidentStatus}:</th>";
        echo "<td>".incidentstatus_drop_down("newstatus", $incident->status)."</td></tr>\n";
        echo "</table>\n\n";
        echo "<input type='hidden' name='action' value='save' />";
        echo "<p align='center'><input name='submit' type='submit' value=\"{$strReassign}\" /></p>";
        echo "</form>\n";
        include('incident_html_bottom.inc.php');
}

?>