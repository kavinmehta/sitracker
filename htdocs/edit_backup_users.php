<?php
// edit_backup_users.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//


// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

// This Page Is Valid XHTML 1.0 Transitional!   3Nov05

require('db_connect.inc.php');
require('functions.inc.php');

if (empty($_REQUEST['user'])
    OR $_REQUEST['user']=='current'
    OR $_REQUEST['user']==$_SESSION['userid']
    OR $_REQUEST['userid']==$_SESSION['userid']) $permission = 58; // Edit your software skills
else $permission = 59; // Manage users software skills

// This page requires authentication
require('auth.inc.php');

// Valid user with Permission
// External variables
$save = $_REQUEST['save'];

if (empty($save))
{
    // External variables
    if (empty($_REQUEST['user']) OR $_REQUEST['user']=='current') $user = mysql_escape_string($sit[2]);
    else $user = mysql_escape_string($_REQUEST['user']);
    $default = cleanvar($_REQUEST['default']);
    $softlist = $_REQUEST['softlist'];

    include('htmlheader.inc.php');
    echo "<h2>Define Substitute Engineers for ".user_realname($user,TRUE)."</h2>\n";
    echo "<form name='def' action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<input type='hidden' name='user' value='{$user}' />";
    echo "<p align='center'>Default Substitute Engineer: ";
    user_drop_down('default', $default, FALSE, $user, "onchange='javascript:this.form.submit();'");
    echo "</p>";
    echo "</form>";

    $sql = "SELECT * FROM usersoftware, software WHERE usersoftware.softwareid=software.id AND userid='{$user}' ORDER BY name";
    $result = mysql_query($sql);
    $countsw=mysql_num_rows($result);
    if ($countsw >= 1)
    {
        echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>\n";
        echo "<table align='center'>\n";
        echo "<tr><th>{$strSkill}</th><th>Substitute</th></tr>";
        $class='shade1';
        while ($software = mysql_fetch_object($result))
        {
            echo "<tr class='$class'>";
            echo "<td><strong>{$software->id}</strong>: {$software->name}</td>";
            if ($software->backupid==0) $software->backupid=$default;
            echo "<td>".software_backup_dropdown('backup[]', $user, $software->id, $software->backupid)."</td>";
            echo "</tr>\n";
            if ($class=='shade2') $class = "shade1";
            else $class = "shade2";
            flush();
            $softarr[]=$software->id;
        }
        $softlist=implode(',',$softarr);
        echo "</table>\n";
        echo "<input type='hidden' name='user' value='$user' />";
        echo "<input type='hidden' name='softlist' value='$softlist' />";
        echo "<input type='hidden' name='save' value='vqvbgf' />";
        echo "<p align='center'><input type='submit' value='{$strSave}' /></p>";
        echo "</form>";
    }
    else
    {
        echo "<h5 class='error'>No software skills defined</h5>";
    }
    include('htmlfooter.inc.php');
}
else
{
    // External variables
    $softlist=explode(',',$_REQUEST['softlist']);
    $backup=$_REQUEST['backup'];
    $user=cleanvar($_REQUEST['user']);
    foreach ($backup AS $key=>$backupid)
    {
        $sql = "UPDATE usersoftware SET backupid='$backupid' WHERE userid='$user' AND softwareid='{$softlist[$key]}' LIMIT 1 ";
        // echo "{$softlist[$key]} -- $key -- $value<br />";
        //echo "$sql <br />";
        mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    }
    confirmation_page("2", "control_panel.php", "<h2>Update Successful</h2><h5>{$strPleaseWaitRedirect}...</h5>");
}

?>