<?php
// holiday_request.php - Search contracts
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission = 27; /* View your calendar */
require ('db_connect.inc.php');
require ('functions.inc.php');
$title = $strHolidayRequests;

// This page requires authentication
require ('auth.inc.php');

// External variables
$user = cleanvar($_REQUEST['user']);
$sent = cleanvar($_REQUEST['sent']);
$mode = cleanvar($_REQUEST['mode']);
$action = cleanvar($_REQUEST['action']);
$type = cleanvar($_REQUEST['type']);
$memo = cleanvar($_REQUEST['memo']);
$approvaluser = cleanvar($_REQUEST['approvaluser']);

include ('htmlheader.inc.php');

function display_holiday_table($result)
{
    global $CONFIG, $user, $approver, $mode;

    echo "<table align='center'>";
    echo "<tr>";
    if ($user == 'all' && $approver == TRUE)
    {
        echo "<th>{$GLOBALS['strName']}</th>";
    }
    echo "<th>{$GLOBALS['strDate']}</th><th>{$GLOBALS['strLength']}</th><th>{$GLOBALS['strType']}</th>";
    if ($approver AND $mode == 'approval')
    {
        echo "<th>{$GLOBALS['strOperation']}</th><th>{$GLOBALS['strGroupMembersAway']}</th>";
    }
    else
    {
        echo "<th>{$GLOBALS['strStatus']}</th>";
    }

    echo "</tr>";
    while ($holiday = mysql_fetch_object($result))
    {
        echo "<tr class='shade2'>";
        if ($user=='all' && $approver==TRUE)
        {
            echo "<td><a href='{$_SERVER['PHP_SELF']}?user={$holiday->userid}&amp;mode=approval'>";
            echo user_realname($holiday->userid,TRUE);
            echo "</a></td>";
        }
        echo "<td>".ldate($CONFIG['dateformat_longdate'], $holiday->startdate)."</td>";
        echo "<td>";
        if ($holiday->length=='am') echo $GLOBALS['strMorning'];
        if ($holiday->length=='pm') echo $GLOBALS['strAfternoon'];
        if ($holiday->length=='day') echo $GLOBALS['strFullDay'];
        echo "</td>";
        echo "<td>".holiday_type($holiday->type)."</td>";
        if ($approver == TRUE)
        {
            if ($sit[2] != $holiday->userid AND $mode == 'approval')
            {
                echo "<td>";
                $approvetext = $GLOBALS['strApprove'];
                if ($holiday->type == 2) $approvetext = $GLOBALS['strAcknowledge'];
                echo "<a href=\"holiday_approve.php?approve=TRUE&amp;user={$holiday->userid}&amp;view={$user}&amp;startdate={$holiday->startdate}&amp;type={$holiday->type}&amp;length={$holiday->length}\">{$approvetext}</a> | ";
                echo "<a href=\"holiday_approve.php?approve=FALSE&amp;user={$holiday->userid}&amp;view={$user}&amp;startdate={$holiday->startdate}&amp;type={$holiday->type}&amp;length={$holiday->length}\">{$GLOBALS['strDecline']}</a>";
                if ($holiday->type == 1)
                {
                    echo " | <a href=\"holiday_approve.php?approve=FREE&amp;user={$holiday->userid}&amp;view={$user}&amp;startdate={$holiday->startdate}&amp;type={$holiday->type}&amp;length={$holiday->length}\">{$GLOBALS['strFreeLeave']}</a>";
                }
                echo "</td>";
            }
            else
            {
                echo "<td>";

                if ($holiday->approvedby > 0)
                {
                    echo sprintf($GLOBALS['strRequestSentToX'], user_realname($holiday->approvedby,TRUE));
                }
                else
                {
                    echo $GLOBALS['strRequestNotSent'];
                    $waiting = TRUE;
                }
                echo "</td>";
            }
            if ($approver == TRUE AND $mode == 'approval')
            {
                echo "<td>";
                echo check_group_holiday($holiday->userid, $holiday->startdate, $holiday->length);
                echo "</td>";
            }
        }
        echo "</tr>\n";
    }
    echo "</table>\n";
}

if (empty($user)) $user=$sit[2];
if (!$sent)
{
    // check to see if this user has approve permission
    $approver = user_permission($sit[2], 50);

    $waiting = FALSE;
    echo "<h2>".icon('holiday', 32)." ";
    if ($user == 'all')
    {
        echo $strAll;
    }
    else
    {
        echo user_realname($user,TRUE);
    }
    echo " - {$strHolidayRequests}</h2>";

    if ($approver==TRUE AND $mode!='approval' AND $user==$sit[2])
    {
        echo "<p align='center'><a href='holiday_request.php?user=all&amp;mode=approval'>{$strApproveHolidays}</a></p>";
    }

    if ($approver==TRUE AND $mode=='approval' AND $user!='all')
    {
        echo "<p align='center'><a href='holiday_request.php?user=all&amp;mode=approval'>{$strShowAll}</a></p>";
    }

    $sql = "SELECT * FROM `{$dbHolidays}` WHERE approved=0 ";
    if (!empty($type)) $sql .= "AND type='$type' ";
    if ($mode != 'approval' || $user != 'all') $sql.="AND userid='$user' ";
    if ($approver == TRUE && $mode == 'approval') $sql .= "AND approvedby={$sit[2]} ";
    $sql .= "ORDER BY startdate, length";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    if (mysql_num_rows($result) > 0)
    {

        display_holiday_table($result);

        if ($mode == 'approval')
        {
            echo "<p align='center'><a href='holiday_approve.php?approve=TRUE&amp;user=$user&amp;view=$user&amp;startdate=all&amp;type=all'>{$strApproveAll}</a></p>";
        }
        else
        {
            $groupid = user_group_id($sit[2]);
            // extract users (only show users with permission to approve that are not disabled accounts)
            $sql  = "SELECT DISTINCT id, realname, accepting, groupid ";
            $sql .= "FROM `{$dbUsers}` AS u, `{$dbUserPermissions}` AS up, `{$dbRolePermissions}` AS rp ";
            $sql .= "WHERE u.id = up.userid AND u.roleid = rp.roleid ";
            $sql .= "AND (up.permissionid = 50 AND up.granted = 'true' OR ";
            $sql .= "rp.permissionid = 50 AND rp.granted = 'true') ";
            $sql .= "AND u.id != {$sit[2]} AND u.status > 0 ORDER BY realname ASC";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            $numapprovers = mysql_num_rows($result);
            if ($numapprovers > 0)
            {
                echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
                echo "<p align='center'>";
                echo "{$strSendRequestsTo}: ";
                echo "<select name='approvaluser'>";
                echo "<option selected='selected' value='0'></option>\n";
                while ($users = mysql_fetch_array($result))
                {
                    echo "<option";
                    if ($users['groupid'] == $groupid) echo " selected='selected'";
                    echo " value='{$users['id']}'";
                    echo ">{$users['realname']}</option>\n";
                }
                echo "</select>";
                echo "</p>";

                // Force resend if there are no new additions to be requested
                if ($waiting == FALSE AND $action!='resend') $action='resend';
                echo "<input type='hidden' name='action' value='{$action}' />";
                echo "<p align='center'>{$strRequestSendComments}<br />";
                echo "<textarea name='memo' rows='3' cols='40'></textarea>";
                echo "<input type='hidden' name='user' value='$user' />";
                echo "<input type='hidden' name='sent' value='true' /><br /><br />";
                echo "<input type='submit' name='submit' value='{$strSendRequest}' />";
                echo "</p>";
                echo "</form>";
            }
            else
            {
                echo "<p class='error'>{$strRequestNoUsersToApprovePermissions}</p>";
            }
        }
    }
    else
    {
        echo "<p class='info'>{$strRequestNoHolidaysAwaitingYourApproval}</p>";
    }


    if ($approver AND $user == 'all')
    {
        // Show all holidays where requests have not been sent

        $sql = "SELECT * FROM `{$dbHolidays}` WHERE approved = 0 AND userid != 0 ";
        if (!empty($type)) $sql .= "AND type='$type' ";
        if ($mode == 'approval') $sql .= "AND approvedby = 0 ";
        $sql .= "ORDER BY startdate, length";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

        if (mysql_num_rows($result) > 0)
        {
            echo "<h2>{$strDatesNotRequested}</h2>";

            $mode = "notapprove";

            display_holiday_table($result);
        }
    }
}
else
{
    if (empty($approvaluser))
    {
        echo "<p class='error'>{$strErrorNotUserSelectedToSendApproval}</p>";
    }
    else
    {
        $sql = "SELECT * FROM `{$dbHolidays}` WHERE approved=0 ";
        if ($action != 'resend') $sql .= "AND approvedby=0 ";
        if ($user != 'all' || $approver == FALSE) $sql .= "AND userid='{$user}' ";
        $sql .= "ORDER BY startdate, length";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        if (mysql_num_rows($result)>0)
        {
            while ($holiday=mysql_fetch_object($result))
            {
                $holidaylist .= ldate('l j F Y', $holiday->startdate).", ";
                if ($holiday->length == 'am') $holidaylist .= $strMorning;
                if ($holiday->length == 'pm') $holidaylist .= $strAfternoon;
                if ($holiday->length == 'day') $holidaylist .= $strFullDaye;
                $holidaylist .= ", ";
                $holidaylist .= holiday_type($holiday->type)."\n";
            }
            if (strlen($memo) > 3)
            {
                $holidaylist .= "{$SYSLANG['strCommentsSentWithRequest']}:\n\n";
                $holidaylist .= "---\n{$memo}\n---\n\n";
            }
        }
        // Mark the userid of the person who will approve the request so that they can see them
        $sql = "UPDATE `{$dbHolidays}` SET approvedby='{$approvaluser}' WHERE userid='{$user}' AND approved=0";
        mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        $rtnvalue = trigger('TRIGGER_HOLIDAY_REQUESTED', array('userid' => $user, 'approvaluseremail' => user_email($approvaluser), 'listofholidays' => $holidaylist));

        if ($rtnvalue === TRUE)
        {
            echo "<p align='center'>{$strRequestSent}</p>";
            echo "<p align='center'>".nl2br($holidaylist)."</p>";
        }
        else
        {
            echo "<p class='error'>There was a problem sending your request</p>";
        }
    }
    echo "<p align='center'><a href='holidays.php?user={$user}'>{$strMyHolidays}</p></p>";
}
include ('htmlfooter.inc.php');
?>