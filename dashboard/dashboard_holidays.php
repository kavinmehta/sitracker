<?php
// dashboard_holidays.php - List of who's away today
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.

$dashboard_incoming_version = 1;

function dashboard_holidays($dashletid)
{
    global $sit, $CONFIG, $iconset;
    global $dbUsers;
    $user = $sit[2];
    echo "<div class='windowbox' style='width: 95%;' id='$dashletid'>";
    echo "<div class='windowtitle'>".icon('holiday', 16)." {$GLOBALS['strWhosAwayToday']}</div>";
    echo "<div class='window'>";
    $sql  = "SELECT * FROM `{$dbUsers}` WHERE status!=0 AND status!=1 ";  // status=0 means left company
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    if (mysql_num_rows($result) >=1)
    {
        while ($users = mysql_fetch_array($result))
        {
            $title = userstatus_name($users['status']);
            $title.=" - ";
            if ($users['accepting'] == 'Yes') $title .= "{$GLOBALS['strAcceptingIncidents']}";
            else $title .= "{$GLOBALS['strNotAcceptingIncidents']}";
            if (!empty($users['message'])) $title.= "\n(".$users['message'].")";

            echo "<strong>{$users['realname']}</strong>, $title";
            echo "<br />";
        }
    }
    else echo "<p align='center'>{$GLOBALS['strNobody']}</p>\n";
    echo "</div></div></div>";
}

?>
