<?php
// holiday_add.php - Adds a holiday to the database
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

$lib_path = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
$permission = 27; // View your calendar
require ($lib_path.'db_connect.inc.php');
require ($lib_path.'functions.inc.php');
$title = "Holiday Calendar";
// This page requires authentication
require ($lib_path.'auth.inc.php');

// Valid user

// External Variables
$day = cleanvar($_REQUEST['day']);
$month = cleanvar($_REQUEST['month']);
$year = cleanvar($_REQUEST['year']);
$user = cleanvar($_REQUEST['user']);
$type = cleanvar($_REQUEST['type']);
$length = cleanvar($_REQUEST['length']);
$return = cleanvar($_REQUEST['return']);

// startdate in unix format
$startdate = mktime(0,0,0,$month,$day,$year);
$enddate = mktime(23,59,59,$month,$day,$year);
if ($length=='') $length = 'day';

if (user_permission($sit[2],50)) $approver = TRUE;
else $approver = FALSE;
if (user_permission($sit[2],22)) $adminuser = TRUE;
else $adminuser = FALSE;

// Holiday types (for reference)
// 1 = Holiday
// 2 = Sickness
// 3 = Working Away
// 4 = Training
// 5 - Compassionate/Free

// check to see if there is a holiday on this day already, if there is retrieve it
list($dtype, $dlength, $dapproved, $dapprovedby) = user_holiday($user, 0, $year, $month, $day, FALSE);

// allow approver (or admin) to unbook holidays already approved
if ($length == '0' AND ($approver == TRUE
                      AND ($dapprovedby = $sit[2] OR $adminuser == TRUE)))
{
    // Delete the holiday
    $sql = "DELETE FROM `{$dbHolidays}` ";
    $sql .= "WHERE userid='$user' AND `date` = '{$year}-{$month}-{$day}' ";
    $sql .= "AND type='$type' ";
    if (!$adminuser) $sql .= "AND (approvedby='{$sit[2]}' OR userid={$sit[2]}) ";
    $result = mysql_query($sql);
    // echo $sql;
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    $dlength=0;
    $dapproved=0;
}
else
{
    if (empty($dapproved))
    {
        // Only allow these types to be modified
        if ($dtype == HOL_HOLIDAY || $dtype == HOL_WORKING_AWAY || $dtype == HOL_TRAINING)
        {
            if ($length == '0')
            {
                // FIXME: doesn't check permission or anything
                $sql = "DELETE FROM `{$dbHolidays}` ";
                $sql .= "WHERE userid='$user' AND `date` = '{$year}-{$month}-{$day}' AND type='$type' ";
                $result = mysql_query($sql);
                // echo $sql;
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                $dlength=0;
                $dapproved=0;
            }
            else
            {
                // there is an existing booking so alter it
                $sql = "UPDATE `{$dbHolidays}` SET length='$length' ";
                $sql .= "WHERE userid='$user' AND `date` = '{$year}-{$month}-{$day}' AND type='$type' AND length='$dlength'";
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                $dlength=$length;
            }
        }
        else
        {
            // there is no holiday on this day, so make one
            $sql = "INSERT INTO `{$dbHolidays}` ";
            $sql .= "SET userid='$user', type='$type', `date` = '{$year}-{$month}-{$day}', length='$length' ";
            $result = mysql_query($sql);
            $dlength = $length;
            $approved = 0;
        }
    }
}

if ($return=='list')
{
    header("Location: calendar.php?display=list&type=$type&user=$user");
    exit;
}
else
{
    $url = $_SERVER['HTTP_REFERER'];
    header("Location: $url");
    exit;
}
?>
