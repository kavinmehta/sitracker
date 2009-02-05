<?php
// calendar.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
//         Tom Gerrard <tom.gerrard[at]salfordsoftware.co.uk>

$lib_path = dirname( __FILE__ ).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR;
$permission = 27; // View your calendar
require ($lib_path.'db_connect.inc.php');
require ($lib_path.'functions.inc.php');

// This page requires authentication
require ($lib_path.'auth.inc.php');
include ('calendar/calendar.inc.php');

$groupid = cleanvar($_REQUEST['gid']);
if (empty($groupid)) $groupid = $_SESSION['groupid'];

// External variables
foreach (array(
            'user', 'nmonth', 'nyear', 'type', 'selectedday', 'selectedmonth',
            'selectedyear', 'selectedtype', 'approved', 'length', 'display',
            'weeknumber'
            ) as $var)
{
    eval("\$$var=cleanvar(\$_REQUEST['$var']);");
}
if (empty($length)) $length='day';
$title = $strCalendar;
$pagecss = array('calendar/planner.css.php');
include ('./inc/htmlheader.inc.php');

if (empty($user) || $user=='current') $user = $sit[2];
elseif ($user == 'all') $user = '';
if (empty($type)) $type = HOL_HOLIDAY;
if (user_permission($sit[2],50)) $approver = TRUE;
else $approver = FALSE;

// Force user to 0 (SiT) when setting public holidays
if ($type == HOL_PUBLIC) $user = 0;

$gidurl = '';
if (!empty($groupid)) $gidurl = "&amp;gid={$groupid}";

// Defaults
if (empty($_REQUEST['year'])) $year = date('Y');
else $year = $_REQUEST['year'];

if (empty($_REQUEST['month'])) $month = date('m');
else $month = $_REQUEST['month'];

if (empty($_REQUEST['day'])) $day = date('d');
else $day = $_REQUEST['day'];

$calendarTypes = array('list','year','month','week','day');

if ($CONFIG['timesheets_enabled'])
{
    $calendarTypes[] = 'timesheet';
}

// Prevent people from including any old file - this also handles any cases
// where $display == 'chart'
if (!in_array($display, $calendarTypes)) $display = 'month';

// Navigation (Don't show for public holidays)
if ($type != HOL_PUBLIC)
{
    echo "<p>{$strDisplay}: ";
    foreach ($calendarTypes as $navType)
    {
        $navHtml[$navType]  = "<a href='{$_SERVER['PHP_SELF']}?display={$navType}";
        $navHtml[$navType] .= "&amp;year={$year}&amp;month={$month}&amp;day={$day}";
        $navHtml[$navType] .= "&amp;type={$type}{$gidurl}'>";
        $navi18n = eval('return $str' . ucfirst($navType) . ';');
        if ($display == $navType) $navHtml[$navType] .= '<em>' . $navi18n . '</em>';
        else $navHtml[$navType] .= $navi18n;
        $navHtml[$navType] .= "</a>";
    }
    echo implode(' | ', $navHtml);
    echo "</p>";
}
include ("calendar/{$display}.inc.php");

include ('./inc/htmlfooter.inc.php');
?>
