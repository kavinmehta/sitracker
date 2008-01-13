<?php
// add_escalation_path.php - Display a form for adding an escalation path
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Paul Heaney <paulheaney[at]users.sourceforge.net>

//// This Page Is Valid XHTML 1.0 Transitional!  (1 Oct 2006)

@include ('set_include_path.inc.php');
$permission = 64; // Manage escalation paths

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

$submit = $_REQUEST['submit'];

$title = $strNewEscalationPath;

if (empty($submit))
{
    include ('htmlheader.inc.php');

    echo show_form_errors('add_escalation_path');
    clear_form_errors('add_escalation_path');

    echo "<h2>{$title}</h2>";

    echo "<form action='".$_SERVER['PHP_SELF']."' method='post' onsubmit='return confirm_submit(\"{$strAreYouSureAddEscalationPath}\")'>";
    echo "<table class='vertical'>";

    echo "<tr><th>{$strName}<sup class='red'>*</sup></th><td><input name='name'";
    if ($_SESSION['formdata']['add_escalation_path']['name'] != "")
    {
        echo "value='{$_SESSION['formdata']['add_escalation_path']['name']}'";
    }
    echo "/></td></tr>";

    echo "<tr><th>{$strTrackURL}<br /></th><td><input name='trackurl'";
    if ($_SESSION['formdata']['add_escalation_path']['trackurl'] != "")
    {
        echo "value='{$_SESSION['formdata']['add_escalation_path']['trackurl']}'";
    }
    echo "/><br />{$strNoteInsertEscalationID}</td></tr>";

    echo "<tr><th>{$strHomeURL}</th><td><input name='homeurl'";
    if ($_SESSION['formdata']['add_escalation_path']['homeurl'] != "")
    {
        echo "value='{$_SESSION['formdata']['add_escalation_path']['homeurl']}'";
    }
    echo "/></td></tr>";

    echo "<tr><th>{$strTitle}</th><td><input name='title'";
    if ($_SESSION['formdata']['add_escalation_path']['title'] != "")
    {
        echo "value='{$_SESSION['formdata']['add_escalation_path']['title']}'";
    }
    echo "/></td></tr>";

    echo "<tr><th>{$strEmailDomain}</th><td><input name='emaildomain'";
    if ($_SESSION['formdata']['add_escalation_path']['emaildomain'] != "")
    {
        echo "value='{$_SESSION['formdata']['add_escalation_path']['emaildomain']}'";
    }
    echo "/></td></tr>";

    echo "</table>";

    echo "<p align='center'><input type='submit' name='submit' value='{$strAdd}' /></p>";

    echo "</form>";

    include ('htmlfooter.inc.php');
    clear_form_data('add_escalation_path');

}
else
{
    $name = cleanvar($_REQUEST['name']);
    $trackurl = cleanvar($_REQUEST['trackurl']);
    $homeurl = cleanvar($_REQUEST['homeurl']);
    $title = cleanvar($_REQUEST['title']);
    $emaildomain = cleanvar($_REQUEST['emaildomain']);

    $_SESSION['formdata']['add_escalation_path'] = $_REQUEST;

    $errors = 0;
    if (empty($name))
    {
        $errors++;
        $_SESSION['formerrors']['add_escalation_path']['name'] = $strMustEnterNameEscalationPath;
    }

    if ($errors == 0)
    {
        $sql = "INSERT INTO `{$dbEscalationPaths}` (name,track_url,home_url,url_title,email_domain) VALUES ";
        $sql .= " ('{$name}','{$trackurl}','{$homeurl}','{$title}','{$emaildomain}')";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        if(!$result)
        {
            $_SESSION['formerrors']['add_escalation_path']['error'] = "Addition of escalation path failed";   // FIXME i18n error
        }
        else
        {
            html_redirect("escalation_paths.php");
        }
        clear_form_errors('add_escalation_path');
        clear_form_data('add_escalation_path');
    }
    else
    {
        include 'htmlheader.inc.php';
        html_redirect("add_escalation_path.php", FALSE);
    }
}


?>
