<?php
// noaccess.php - Tell the user access is denied
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

$lib_path = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
require ($lib_path.'db_connect.inc.php');
require ($lib_path.'functions.inc.php');

// External variables
$username = cleanvar($_REQUEST['username']);
$id = cleanvar($_REQUEST['id']);

include ('./inc/htmlheader.inc.php');

echo "<h2>".icon('permissiondenied', 32, $strPermissionDenied);
echo " {$strPermissionDenied}</h2>";
// FIXME 3.35 triggers
if ($username != '')
{
    $errdate = date('M j H:i');
    $errmsg = "$errdate ".permission_name($id)."({$id}) ".sprintf($strPermissionDeniedForX, $username);
    $errmsg .= "\n";
    if (!empty($CONFIG['access_logfile']))
    {
        $errlog = error_log($errmsg, 3, "{$CONFIG['access_logfile']}");
        if (!$errlog) echo "Fatal error logging this problem<br />";
    }
    unset($errdate);
    unset($errmsg);
    unset($errlog);
}

if (strpos($id,',') !== FALSE)
{
    $refused = explode(',', $id);
}
else
{
    $refused = array($id);
}

echo "<p align='center' class='error'>{$strSorryNoPermissionToAreas}:</p>";
echo "<ul>";
foreach ($refused AS $id)
{
    echo "<li>{$id}: ".permission_name($id)."</li>\n";
    journal(CFG_LOGGING_MIN, 'Access Failure', "Access to ".permission_name($id)." ($id) was denied", CFG_JOURNAL_OTHER, $id);
}
echo "</ul>";
echo "<p align='center'>{$strIfYouShouldHaveAccess}</p>";
echo "<p align='center'><a href=\"javascript:history.back();\">{$strPrevious}</a></p>";


include ('./inc/htmlfooter.inc.php');

?>
