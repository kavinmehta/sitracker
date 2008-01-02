<?php
// edit_user_skills.php - Form to set users skills
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
if (empty($_REQUEST['user'])
    OR $_REQUEST['user']=='current'
    OR $_REQUEST['userid']==$_SESSION['userid']) $permission=58; // Edit your software skills
else $permission=59; // Manage users software skills

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

// External Variables
$submit=$_REQUEST['submit'];
if (empty($_REQUEST['user']) || $_REQUEST['user']=='current') $user=$sit[2];
else $user = cleanvar($_REQUEST['user']);

if (empty($submit))
{
    include ('htmlheader.inc.php');
    $sql = "SELECT * FROM `{$dbUserSoftware}` AS us, `{$dbSoftware}` AS s WHERE us.softwareid = s.id AND userid = '$user' ORDER BY name";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) >= 1)
    {
        while ($software = mysql_fetch_object($result))
        {
            $expertise[]=$software->id;
        }
    }
    echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/skill.png' width='32' height='32' alt='' /> ";
    echo sprintf($strSkillsFor, user_realname($user,TRUE))."</h2>";
    echo "<p align='center'>{$strSelectYourSkills}</p>";
    echo "<form name='softwareform' action='{$_SERVER['PHP_SELF']}' method='post' onsubmit=\"populateHidden(document.softwareform.elements['expertise[]'],document.softwareform.choices)\">";
    echo "<table align='center'>";
    echo "<tr><th>{$strNOSkills}</th><th>&nbsp;</th><th>{$strHAVESkills}</th></tr>";
    echo "<tr><td align='center' width='300' class='shade1'>";
    $sql = "SELECT * FROM `{$dbSoftware}` ORDER BY name";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) >= 1)
    {
        echo "<select name='noskills[]' multiple='multiple' size='20' style='width: 100%; min-width: 200px;'>";
        while ($software = mysql_fetch_object($result))
        {
            if (is_array($expertise)) { if (!in_array($software->id,$expertise)) echo "<option value='{$software->id}'>$software->name</option>\n";  }
            else  echo "<option value='{$software->id}'>$software->name</option>\n";
        }
        echo "</select>";
    }
    else echo "<p class='error'>No software defined</p>";
    echo "</td>";
    echo "<td class='shade2'>";
    echo "<input type='button' value='&gt;' title='Add Selected' onclick=\"copySelected(this.form.elements['noskills[]'],this.form.elements['expertise[]'])\" /><br />";
    echo "<input type='button' value='&lt;' title='Remove Selected' onclick=\"copySelected(this.form.elements['expertise[]'],this.form.elements['noskills[]'])\" /><br />";
    echo "<input type='button' value='&gt;&gt;' title='Add All' onclick=\"copyAll(this.form.elements['noskills[]'],this.form.elements['expertise[]'])\" /><br />";
    echo "<input type='button' value='&lt;&lt;' title='Remove All' onclick=\"copyAll(this.form.elements['expertise[]'],this.form.elements['noskills[]'])\" /><br />";
    echo "</td>";
    echo "<td class='shade1'>";
    $sql = "SELECT * FROM `{$dbUserSoftware}` AS us, `{$dbSoftware}` AS s WHERE us.softwareid = s.id AND userid = '{$user}' ORDER BY name";
    $result = mysql_query($sql);
    echo "<select name='expertise[]' multiple='multiple' size='20' style='width: 100%;  min-width: 200px;'>";
    while ($software = mysql_fetch_object($result))
    {
        echo "<option value='{$software->id}'>$software->name</option>\n";
    }
    // echo "<option value='0'>---</option>\n";
    echo "</select>";
    echo "<input type='hidden' name='userid' value='{$user}' />";
    echo "</td></tr>\n";
    ?>
    </table>
    <input type="hidden" name="choices" />
    <?php
    echo "<p align='center'><input name='submit' type='submit' value='{$strSave}' /></p>";
    echo "</form>\n";

    include ('htmlfooter.inc.php');
}
else
{
    // Update user profile
    $selections=urldecode($_POST['choices']);
    parse_str($selections);

    $expertise = $_POST['expertise'];
    $noskills = $_POST['noskills'];

    // remove existing selections first

    // FIXME: This is going to wipe the backup/substitute engineer settings, need to fix this
    // INL 21Dec04

    // FIXME: whatabout cases where the user is a backup for one of the products
    // he removes? or if the backup user leaves the company?

    //$sql = "DELETE FROM `{$dbUserSoftware}` WHERE userid='{$_POST['userid']}'";
    //mysql_query($sql);
    //if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    if (is_array($expertise))
    {
        $expertise=array_unique($expertise);
        foreach ($expertise AS $value)
        {
            $checksql = "SELECT userid FROM `{$dbUserSoftware}` WHERE userid='{$_POST['userid']}' AND softwareid='$value' LIMIT 1";
            $checkresult=mysql_query($checksql);
            if (mysql_num_rows($checkresult)< 1)
            {
                $sql = "INSERT DELAYED INTO `{$dbUserSoftware}` (userid, softwareid) VALUES ('{$_POST['userid']}', '$value')";
                // echo "$sql <br />";
                mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
            }
            $softlist[]=$value;
        }
    }

    // Make sure we're not being backup support for all the software we have no skills in.
    if (is_array($noskills))
    {
        $noskills=array_unique($noskills);
        foreach ($noskills AS $value)
        {
            // Remove the software listed that we don't support
            $sql = "DELETE FROM `{$dbUserSoftware}` WHERE userid='{$_POST['userid']}' AND softwareid='$value' LIMIT 1";
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            // If we are providing backup for a skill we don't have - reset that back to nobody providing backup
            $sql = "UPDATE `{$dbUserSoftware}` SET backupid='0' WHERE backupid='{$_POST['userid']}' AND softwareid='$value' LIMIT 1";
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        }
    }

    journal(CFG_LOGGING_MAX,'Skillset Updated',"Users Skillset was Changed",CFG_JOURNAL_USER,0);

    // Have a look to see if any of the software we support is lacking a backup/substitute engineer
    $sql = "SELECT userid FROM `{$dbUserSoftware}` WHERE userid='{$_POST['userid']}' AND backupid='0' LIMIT 1";
    $result=mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $lacking=mysql_num_rows($result);
    if ($lacking >= 1)
    {
        // FIXME i18n
        html_redirect("edit_backup_users.php?user={$_POST['userid']}", TRUE, "You should now define a substitute engineer for each skill");
    }
    else
    {
        if ($_POST['userid']==$_COOKIE['sit'][2]) html_redirect("edit_user_skills.php?user={$_POST['userid']}");
        else html_redirect("manage_users.php");
    }
}
?>
