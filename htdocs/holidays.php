<?php
// holidays.php -
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// This Page Is Valid XHTML 1.0 Transitional!  13Sep06
@include ('set_include_path.inc.php');
$permission=4; // Edit your profile

require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

$approver = user_permission($sit[2],50); // Approve holidays

if (!empty($_REQUEST['user'])) $user = cleanvar($_REQUEST['user']);
else $user = $sit[2];

if ($user==$sit[2]) $title= sprintf($strUsersHolidays, $_SESSION['realname']);
else $title = user_realname($user)."'s Holidays";

include ('htmlheader.inc.php');
echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/holiday.png' width='32' height='32' alt='' /> ";
echo "$title</h2>";

echo "<p align='center'>";
echo "<a href='book_holidays.php?user={$user}'>{$strBookHoliday}</a>";
echo " | <a href='holiday_calendar.php'>{$strHolidayPlanner}</a>";
if ($approver)
{
    echo " | <a href='holiday_request.php?user=";
    if (user==$sit[2]) echo "all";
    else echo $user;
    echo "&amp;mode=approval'>{$strApproveHolidays}</a>";
}
echo "</p>\n";

// Entitlement
if ($user==$sit[2] OR $approver==TRUE)
{
    // Only shown when viewing your own holidays or when you're an approver
    echo "<table align='center' width='450'>\n";
    echo "<tr><th class='subhead'>{$strHolidays}</th></tr>\n";
    echo "<tr class='shade1'><td><strong>{$strHolidayEntitlement}</strong>:</td></tr>\n";
    echo "<tr class='shade2'><td>";
    $entitlement=user_holiday_entitlement($user);
    $holidaystaken=user_count_holidays($user, 1);
    echo "$entitlement {$strDays}, ";
    echo "$holidaystaken {$strtaken}, ";
    printf ($strRemaining, $entitlement-$holidaystaken);
    echo "</td></tr>\n";
    echo "<tr class='shade1'><td ><strong>{$strOtherLeave}</strong>:</td></tr>\n";
    echo "<tr class='shade2'><td>";
    echo user_count_holidays($user, 2)." {$strdayssick}, ";
    echo user_count_holidays($user, 3)." {$strdaysworkingaway}, ";
    echo user_count_holidays($user, 4)." {$strdaystraining}";
    echo "<br />";
    echo user_count_holidays($user, 5)." {$strdaysother}";
    echo "</td></tr>\n";
    echo "</table>\n";
}

// Holiday List
echo "<table align='center' width='450'>\n";
echo "<tr><th colspan='4' class='subhead'>{$strHolidayList}</th></tr>\n";
$sql = "SELECT * FROM holidays WHERE userid='{$user}' AND approved=0 ORDER BY startdate ASC";
$result = mysql_query($sql);
if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
$numwaiting=mysql_num_rows($result);
if ($numwaiting > 0)
{
    if ($user==$sit[2])
    {
        // Show dates waiting approval, but only to owner
        echo "<tr class='shade2'><td colspan='4'><strong>{$strDatesNotYetApproved}</strong>:</td></tr>";
        while ($dates = mysql_fetch_array($result))
        {
            echo "<tr class='shade1'><td>{$dates['name']}</td>";
            echo "<td>".date('l', $dates['startdate'])." ";
            if ($dates['length']=='am') echo "<u>{$strMorning}</u> ";
            if ($dates['length']=='pm') echo "<u>{$strAfternoon}</u> ";
            echo date('jS F Y', $dates['startdate']);
            echo "</td>";
            echo "<td>";
            echo holiday_approval_status($dates['approved'], $dates['approvedby']);
            echo "</td>";
            echo "<td>";
            if ($dates['length']=='pm' OR $dates['length']=='day') echo "<a href='add_holiday.php?type={$dates['type']}&amp;user=$user&amp;year=".date('Y',$dates['startdate'])."&amp;month=".date('m',$dates['startdate'])."&amp;day=".date('d',$dates['startdate'])."&amp;length=am' onclick=\"return window.confirm('".date('l jS F Y', $dates['startdate']).": {$strHolidayMorningOnlyConfirm}');\" title='{$strHolidayMorningOnly}'>{$strAM}</a> | ";
            if ($dates['length']=='am' OR $dates['length']=='day') echo "<a href='add_holiday.php?type={$dates['type']}&amp;user=$user&amp;year=".date('Y',$dates['startdate'])."&amp;month=".date('m',$dates['startdate'])."&amp;day=".date('d',$dates['startdate'])."&amp;length=pm' onclick=\"return window.confirm('".date('l jS F Y', $dates['startdate']).": {$strHolidayAfternoonOnlyConfirm}');\" title='{$strHolidayAfternoonOnly}'>{$strPM}</a> | ";
            if ($dates['length']=='am' OR $dates['length']=='pm') echo "<a href='add_holiday.php?type={$dates['type']}&amp;user=$user&amp;year=".date('Y',$dates['startdate'])."&amp;month=".date('m',$dates['startdate'])."&amp;day=".date('d',$dates['startdate'])."&amp;length=day' onclick=\"return window.confirm('".date('l jS F Y', $dates['startdate']).": {$strHolidayFullDayConfirm}');\" title='{$strHolidayFullDay}'>{$strAllDay}</a> | ";
            if ($sit[2]==$user) echo "<a href='add_holiday.php?year=".date('Y',$dates['startdate'])."&amp;month=".date('m',$dates['startdate'])."&amp;day=".date('d',$dates['startdate'])."&amp;user={$sit[2]}&amp;type={$dates['type']}&amp;length=0&amp;return=holidays' onclick=\"return window.confirm('".date('l jS F Y', $dates['startdate']).": {$strHolidayCancelConfirm}');\" title='{$strHolidayCancel}'>cancel</a>";
            echo "</td></tr>\n";
        }
        echo "<tr class='shade1'><td colspan='4'><a href='holiday_request.php?action=resend'>{$strSendReminderRequest}</a></td></tr>";
    }
}
mysql_free_result($result);

// Get list of holiday types
$holidaytype[1] = $GLOBALS['strHoliday'];
$holidaytype[2] = $GLOBALS['strAbsentSick'];
$holidaytype[3] = $GLOBALS['strWorkingAway'];
$holidaytype[4] = $GLOBALS['strTraining'];
$holidaytype[5] = $GLOBALS['strCompassionateLeave'];
foreach ($holidaytype AS $htypeid => $htype)
{
    $sql = "SELECT *, from_unixtime(startdate) AS start FROM holidays WHERE userid='{$user}' AND type={$htypeid} ";
    $sql.= "AND (approved=1 OR (approved=11 AND startdate >= $now)) ORDER BY startdate ASC ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $numtaken = mysql_num_rows($result);
    if ($numtaken > 0)
    {
        echo "<tr class='shade2'><td colspan='4'><strong>{$htype}</strong>:</td></tr>";
        while ($dates = mysql_fetch_array($result))
        {
            echo "<tr class='shade1'>";
            echo "<td colspan='2'>".date('l', $dates['startdate'])." ";
            if ($dates['length']=='am') echo "<u>{$strMorning}</u> ";
            if ($dates['length']=='pm') echo "<u>{$strAfternoon}</u> ";
            echo date('jS F Y', $dates['startdate']);
            echo "</td>";
            echo "<td colspan='2'>";
            echo holiday_approval_status($dates['approved'], $dates['approvedby']);
            echo "</td></tr>\n";
        }
    }
    mysql_free_result($result);
}

if ($numtaken < 1 AND $numwaiting < 1) echo "<tr class='shade2'><td colspan='4'><em>{$strNone}</em</td></tr>\n";
echo "</table>\n";


// AWAY TODAY
if ($user==$sit[2])
{
    // Only show when viewing your own holiday page
    $sql  = "SELECT * FROM users WHERE status!=0 AND status!=1 ";  // status=0 means left company
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    echo "<table align='center' width='450'>";
    echo "<tr><th align='right'>{$strWhosAwayToday}</th></tr>\n";
    if (mysql_num_rows($result) >=1)
    {
        while ($users = mysql_fetch_array($result))
        {
            echo "<tr><td class='shade2'>";
            $title=userstatus_name($users["status"]);
            $title.=" - ";
            if ($users['accepting']=='Yes') $title .= "{$strAccepting}";
            else $title .= "{$strNotAccepting}";
            $title .= " {$strIncidents}";
            if (!empty($users['message'])) $title.="\n".$users['message'];

            echo "<strong>{$users['realname']}</strong>, $title";
            echo "</td></tr>\n";
        }
    }
    else echo "<tr class='shade2'><td><em>{$strNobody}</em></td></tr>\n";
    echo "</table>";
}
include ('htmlfooter.inc.php');
?>
