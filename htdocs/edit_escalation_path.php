<?php
// edit_escalation_path - Ability to edit escalation path
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Paul Heaney <paulheaney[at]users.sourceforge.net>

//// This Page Is Valid XHTML 1.0 Transitional!  (7 Oct 2006)

// FIXME i18n whole page
@include('set_include_path.inc.php');
$permission=64; // Manage escalation paths
require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

if(empty($_REQUEST['mode']))
{
    $title = $strEditEscalationPath;
    //show page
    $id = $_REQUEST['id'];
    $sql = "SELECT * FROM escalationpaths WHERE id = {$id}";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

    include('htmlheader.inc.php');

    echo "<h2>{$title}</h2>";

    while($details = mysql_fetch_object($result))
    {
        echo "<form action='".$_SERVER['PHP_SELF']."' method='post' onsubmit='return confirm_submit(\"{$strAreYouSureEditEscalationPath}\")'>";
        echo "<table class='vertical'>";
        echo "<tr><th>{$strName}:</th><td><input name='name' value='{$details->name}'/></td></tr>";
        echo "<tr><th>Track URL:</th><td><input name='trackurl' value='{$details->track_url}' /><br />Note: insert '%externalid%' for automatic incident number insertion</td></tr>";
        echo "<tr><th>Home URL:</th><td><input name='homeurl' value='{$details->home_url}' /></td></tr>";
        echo "<tr><th>{$strTitle}:</th><td><input name='title' value='{$details->url_title}' /></td></tr>";
        echo "<tr><th>Email domain:</th><td><input name='emaildomain' value='{$details->email_domain}' /></td></tr>";

        echo "</table>";
        echo "<input type='hidden' value='{$id}' name='id' />";
        echo "<input type='hidden' value='edit' name='mode' />";
        echo "<p align='center'><input type='submit' name='submit' value=\"{$strSave}\" /></p>";

        echo "</form>";
    }
    include('htmlfooter.inc.php');
}
else
{
    //make changes
    $id = cleanvar($_REQUEST['id']);
    $name = cleanvar($_REQUEST['name']);
    $trackurl = cleanvar($_REQUEST['trackurl']);
    $homeurl = cleanvar($_REQUEST['homeurl']);
    $title = cleanvar($_REQUEST['title']);
    $emaildomain = cleanvar($_REQUEST['emaildomain']);

    $errors = 0;
    if(empty($name))
    {
        $errors++;
        echo "<p class='error'>You must enter a name for the escalation path</p>\n";
    }

    if($errors == 0)
    {
        $sql = "UPDATE escalationpaths SET name = '{$name}', track_url = '{$trackurl}', ";
        $sql .= " home_url = '{$homeurl}', url_title = '{$title}', email_domain = '{$emaildomain}' ";
        $sql .= " WHERE id = '{$id}'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        if(!$result) echo "<p class='error'>Edit of escalation path failed</p>";
        else
        {
            html_redirect("escalation_paths.php");
        }
    }
}

?>
