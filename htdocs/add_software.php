<?php
// add_software.php - Form for adding software
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission = 56; // Add Skills

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

$title = $strAddSkill;

// External variables
$submit = $_REQUEST['submit'];

if (empty($submit))
{
    // Show add product form
    include ('htmlheader.inc.php');

    $_SESSION['formerrors']['add_software'] = NULL;
    echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/skill.png' width='32' height='32' alt='' /> ";
    echo "{$strNewSkill}</h2>";
    echo "<h5>".sprintf($strMandatoryMarked, "<sup class='red'>*</sup></h5>");
    echo "<form name='addsoftware' action='{$_SERVER['PHP_SELF']}' method='post' onsubmit='return confirm_submit(\"{$strAreYouSureAddSkill}\");'>";
    echo "<table class='vertical'>";
    echo "<tr><th>{$strVendor}:</th><td>";
    if ($_SESSION['formdata']['add_software']['vendor'] != "")
    {
        echo vendor_drop_down('vendor',$_SESSION['formdata']['add_software']['vendor'])."</td></tr>\n";
    }
    else
    {
        echo vendor_drop_down('vendor',$software->vendorid)."</td></tr>\n";
    }
    echo "<tr><th>{$strSkill}: <sup class='red'>*</sup></th><td><input maxlength='50' name='name' size='30' /></td></tr>\n";
    echo "<tr><th>{$strLifetime}:</th><td>";
    echo "<input type='text' name='lifetime_start' id='lifetime_start' size='10' ";
    if ($_SESSION['formdata']['add_software']['lifetime_start'] != "")
    {
        echo "value='{$_SESSION['formdata']['add_software']['lifetime_start']}'";
    }
    echo " /> ";
    echo date_picker('addsoftware.lifetime_start');
    echo " {$strTo}: ";
    echo "<input type='text' name='lifetime_end' id='lifetime_end' size='10'";
    if ($_SESSION['formdata']['add_software']['lifetime_end'] != "")
    {
        echo "value='{$_SESSION['formdata']['add_software']['lifetime_end']}'";
    }
    echo "/> ";
    echo date_picker('addsoftware.lifetime_end');
    echo "</td></tr>\n";
    echo "<tr><th>{$strTags}:</th>";
    echo "<td><textarea rows='2' cols='30' name='tags'></textarea></td></tr>\n";
    echo "</table>";
    echo "<p align='center'><input name='submit' type='submit' value='{$strAddSkill}' /></p>";
    echo "<p class='warning'>{$strAvoidDupes}</p>";
    echo "</form>\n";
    echo "<p align='center'><a href='products.php'>{$strReturnWithoutSaving}</a></p>";
    include ('htmlfooter.inc.php');

    $_SESSION['formdata']['add_software'] = NULL;
}
else
{
    // External variables
    $name = cleanvar($_REQUEST['name']);
    $tags = cleanvar($_REQUEST['tags']);
    $vendor = cleanvar($_REQUEST['vendor']);
    if (!empty($_REQUEST['lifetime_start']))
    {
        $lifetime_start = date('Y-m-d',strtotime($_REQUEST['lifetime_start']));
    }
    else
    {
        $lifetime_start = '';
    }
    
    if (!empty($_REQUEST['lifetime_end']))
    {
        $lifetime_end = date('Y-m-d',strtotime($_REQUEST['lifetime_end']));
    }    
    else
    {
        $lifetime_end = '';
    }

    $_SESSION['formdata']['add_software'] = $_REQUEST;

    // Add new
    $errors = 0;

    // check for blank name
    if ($name == "")
    {
        $errors++;
        $_SESSION['formerrors']['add_software']['name'] = $strMustEnterSkillName;
    }
    // Check this is not a duplicate
    $sql = "SELECT id FROM `{$dbSoftware}` WHERE LCASE(name)=LCASE('$name') LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) >= 1)
    {
        $errors++;
        $_SESSION['formerrors']['add_software']['duplicate'] .= "A record already exists with that skill name"; // FIXME i18n
    }

    // add product if no errors
    if ($errors == 0)
    {
        $sql = "INSERT INTO `{$dbSoftware}` (name, vendorid, lifetime_start, lifetime_end) VALUES ('$name','$vendor','$lifetime_start','$lifetime_end')";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        if (!$result)
        {
            echo "<p class='error'>Addition of Skill Failed\n"; // FIXME i18n
        }
        else
        {
            $id = mysql_insert_id();
            replace_tags(TAG_SKILL, $id, $tags);

            journal(CFG_LOGGING_DEBUG, 'Skill Added', "Skill {$id} was added", CFG_JOURNAL_DEBUG, $id);
            html_redirect("products.php");
            //clear form data
            $_SESSION['formdata']['add_software'] = NULL;
        }
    }
    else
    {
        include ('htmlheader.inc.php');
        html_redirect("add_software.php", FALSE);
    }
}
?>
