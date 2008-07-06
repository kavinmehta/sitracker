<?php
// timesheet_approve.php - Show and approve timesheets
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Tom Gerrard <tom.gerrard[at]salfordsoftware.co.uk>

@include ('set_include_path.inc.php');
$permission = 50; /* Approve holidays */
require ('db_connect.inc.php');
require ('functions.inc.php');
$title = $strApproveTimesheets;

// This page requires authentication
require ('auth.inc.php');

foreach (array('user', 'date', 'approve' ) as $var)
    eval("\$$var=cleanvar(\$_REQUEST['$var']);");

if ($user == '')
{
    include ('htmlheader.inc.php');
    echo "<h2>".icon('holiday', 32)." ";
    echo $strTimesheets;
    echo "</h2>";
    $usql = "SELECT groupid FROM `{$dbUsers}` WHERE id = {$sit[2]}";
    $uresult = mysql_query($usql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $mygroup = mysql_fetch_array($uresult);
    $sql = "SELECT DISTINCT owner FROM `{$dbTasks}` AS t, `{$dbUsers}` AS u, `{$dbGroups}` AS g ";
    $sql .= "WHERE completion = 1 AND distribution='event' AND u.groupid = {$mygroup['groupid']} AND ";
    $sql .= "u.id = t.owner ORDER BY owner";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    if (mysql_num_rows($result) > 0)
    {
        echo "<table align='center'>";
        echo "<tr>";
        echo "<th>{$strName}</th>";
        echo "<th>{$strDate}</th>";
        echo "</tr>";
        while ($owner = mysql_fetch_object($result))
        {
            echo "<tr class='shade2'>";
            echo "<td>";
            echo user_realname($owner->owner, TRUE);
            echo "</td>";
            $ssql = "SELECT startdate FROM `{$dbTasks}` WHERE completion = 1 AND distribution = 'event' AND owner = {$owner->owner} ORDER BY startdate LIMIT 1";
            $sresult = mysql_query($ssql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
            $startdate = mysql_fetch_object($sresult);
            $sd = strtotime($startdate->startdate);
            if (date('w', $sd) != 1)
            {
                $sd = strtotime('last monday', $sd);
            }
            else
            {
                $sd = strtotime('midnight', $sd);
            }
            echo "<td>".date($CONFIG['dateformat_date'], $sd) ."</td>";
            echo "<td>";
            echo "<a href=\"timesheet_approve.php?user={$owner->owner}&amp;date=$sd\">{$strView}</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    else
    {
        echo "<p class='info'>There are currently no timesheets waiting for your approval</p>";
    }
    include ('htmlfooter.inc.php');
}
else if ($approve == '')
{
    include ('calendar.inc.php');
    include ('htmlheader.inc.php');
    echo "<h2>$strTimesheet - " . user_realname($user) . "</h2>";
    echo "<p align='center'>" . date($CONFIG['dateformat_date'], $date) . " - " . date($CONFIG['dateformat_date'], $date + 86400 * 6) . "</p>";
    echo "<table align='center'>";
    echo "<tr>";
    echo "<th>{$strDate}</th>";
    echo "<th>{$strActivity}</th>";
    echo "<th>{$strTotal}</th>";
    echo "</tr>";
    foreach (array($strMonday, $strTuesday, $strWednesday, $strThursday, $strFriday, $strSaturday, $strSunday) as $day)
    {
        $daytime = 0;
        $items = get_users_appointments($user, $date, $date + 86400);
        echo "<tr class='shade2'><th>$day</th>";
        echo "<td style='width: 250px;'>";
        $times = array();
        foreach ($items as $item)
        {
            $timediff = strtotime($item['eventEndDate']) - strtotime($item['eventStartDate']);
            $times[$item['description']] += $timediff;
            $daytime += $timediff;
        }
        ksort($times);
        $html = array();

        foreach ($times as $description => $time)
            $html[] = "<strong>$description</strong>: " . format_seconds($time);
        echo implode('<br />', $html);
        echo "</td>";

        echo "<td>";
        if ($daytime > 0) echo format_seconds($daytime);
        echo "</td>";
        $date += 86400;
    }
    echo "</table>";
    echo "<p align = 'center'><a href='{$_SERVER['PHP_SELF']}?user=$user&amp;date=$date&amp;approve=1'>$strApprove</a></p>";
    include ('htmlfooter.inc.php');
}
else
{
    $sql = "UPDATE `{$dbTasks}` SET completion = 2 WHERE distribution = 'event' AND owner = $user ";
    $sql.= "AND UNIX_TIMESTAMP(startdate) >= ($date - 86400 * 7) AND UNIX_TIMESTAMP(startdate) < $date";
    mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    html_redirect($_SERVER['PHP_SELF']);
}

?>