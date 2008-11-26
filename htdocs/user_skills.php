<?php
// user_skills.php - Display a list of users skills
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
// This Page Is Valid XHTML 1.0 Transitional!  31Oct05

@include ('set_include_path.inc.php');
$permission = 14; // View Users
$title = "User Skills";
require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

// External Variables
$sort = cleanvar($_REQUEST['sort']);

include ('htmlheader.inc.php');

$sql  = "SELECT * FROM `{$dbUsers}` WHERE status!=0";  // status=0 means account disabled

// sort users by realname by default
if (empty($sort) || $sort == "realname")  $sql .= " ORDER BY realname ASC";

$result = mysql_query($sql);
if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

echo "<h2>".icon('user', 32)." {$strListSkills} ";
echo icon('skill', 32)."</h2>";

echo "<table align='center' style='width:95%;'>";
echo "<tr>";
echo "<th width='15%'><a href='{$_SERVER['PHP_SELF']}?sort=realname'>{$strName}</a></th>";
echo "<th>{$strQualifications} / {$strSkills}</th>";
echo "</tr>";

// show results
$shade = 0;
while ($users = mysql_fetch_array($result))
{
    // define class for table row shading
    if ($shade) $class = "shade1";
    else $class = "shade2";

    echo "<tr>";
    echo "<td rowspan='2' class='{$class}'><a href=\"mailto:{$users['email']}\">{$users['realname']}</a><br />";
    echo "{$users['title']}</td>";
    echo "<td class='{$class}'>";
    if (!empty($users['qualifications'])) echo "<strong>{$users['qualifications']}</strong>";
    else echo "&nbsp;";
    echo "</td></tr>\n";
    echo "<tr>";
    echo "<td class='$class'>";
    $ssql = "SELECT * FROM `{$dbUserSoftware}` AS us, `{$dbSoftware}` AS s WHERE us.softwareid = s.id AND us.userid='{$users['id']}' ORDER BY s.name ";
    $sresult = mysql_query($ssql);
    $countskills=mysql_num_rows($sresult);
    $nobackup=0;
    if ($countskills >= 1)
    {
        $c=1;
        while ($software = mysql_fetch_object($sresult))
        {
             //echo "<em>{$software->name}</em>";
            //echo "<span class='info' title='{$strSubstitute}: ".user_realname($software->backupid,TRUE)."'>{$software->name}</span>";
            echo "{$software->name}";
            if ($software->backupid > 0) echo " <em style='color: #555;'>(".user_realname($software->backupid,TRUE).")</em>";
            if ($software->backupid==0) $nobackup++;
            if ($c < $countskills) echo ", ";
            else
            {
                echo "<br /><br />&bull; $countskills ".strtolower($strSkills);
                if (($nobackup+1) >= $countskills) echo ", <strong>{$strNoSubstitutes}</strong>.";
                elseif ($nobackup > 0) echo ", <strong>".sprintf($strNeedsSubstitueEngineers, $nobackup)."</strong>.";
            }
            $c++;
        }
    }
    else echo "&nbsp;";

    if ($users['id']==$sit[2]) echo " <a href='edit_user_skills.php'>{$strMySkills}</a>";

    echo "</td>";
    echo "</tr>\n";
    // invert shade
    if ($shade == 1) $shade = 0;
    else $shade = 1;
}
echo "</table>\n";

// free result and disconnect
mysql_free_result($result);

include ('htmlfooter.inc.php');
?>