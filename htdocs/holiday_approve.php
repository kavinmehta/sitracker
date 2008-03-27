<?php
// holiday_approve.php -
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission = 50; // Approve Holiday
require ('db_connect.inc.php');
require ('functions.inc.php');
$title = "Holiday Approval";

// This page requires authentication
require ('auth.inc.php');

// External variables
$approve = $_REQUEST['approve'];
$startdate = cleanvar($_REQUEST['startdate']);
$type = cleanvar($_REQUEST['type']);
$user = cleanvar($_REQUEST['user']);
$length = cleanvar($_REQUEST['length']);
$view = cleanvar($_REQUEST['view']);

// there is an existing booking so alter it
switch (strtolower($approve))
{
    case 'true':
        $sql = "UPDATE `{$dbHolidays}` SET approved='1' ";
    break;
    case 'false':
        $sql = "UPDATE `{$dbHolidays}` SET approved='2' ";
    break;
    case 'free':
        $sql = "UPDATE `{$dbHolidays}` SET approved='1', type='5' ";
    break;
}

$sql .= "WHERE approvedby='$sit[2]' AND approved=0 ";

if ($user != 'all')
    $sql .= "AND userid='$user' ";

if ($startdate != 'all')
    $sql.="AND startdate='$startdate' AND type='$type' AND length='$length'";

$result = mysql_query($sql);
if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

$bodytext = "Message from {$CONFIG['application_shortname']}: ".user_realname($sit[2])." has ";
if ($approve=='FALSE') $bodytext.="rejected";
else $bodytext.="approved";
$bodytext.=" your request for ";
if ($startdate=='all') $bodytext .= "all days requested\n\n";
else
{
    $bodytext .= "the ";
    $bodytext .= date('l j F Y',$startdate);
    $bodytext .= "\n";
}
$email_from = user_email($sit[2]);
$email_to = user_email($user);
$email_subject = "Re: {$CONFIG['application_shortname']}: Holiday Approval Request";
$extra_headers  = "From: $email_from\nReply-To: $email_from\nErrors-To: {$CONFIG['support_email']}\n";
$extra_headers .= "X-Mailer: {$CONFIG['application_shortname']} {$application_version_string}/PHP " . phpversion()."\n";
$extra_headers .= "X-Originating-IP: {$_SERVER['REMOTE_ADDR']}\n";
$rtnvalue = mail($email_to, $email_subject, $bodytext, $extra_headers);

//if ($rtnvalue===TRUE) echo "<p align='center'>".user_realname($user)." has been notified of your decision</p>";
//else echo "<p class='error'>There was a problem sending your notification</p>";

plugin_do('holiday_ack');

if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
header("Location: holiday_request.php?user=$view&mode=approval");
exit;
?>
