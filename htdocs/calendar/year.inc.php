<?php
// year.inc.php - Displays a year view of the calendar
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Included by ../calendar.php

// Prevent script from being run directly (ie. it must always be included
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
    exit;
}

// Display year calendar
if ($type < 10)
{
    echo "<h2>";
    // FIXME i18n apostrophe in name and Everybodys calendar
    if ($user=='all' && $approver==TRUE) echo "Everybody";
    else echo user_realname($user,TRUE);
    echo "'s Calendar</h2>";
    if ($type==1) echo "<p align='center'>Used ".user_count_holidays($user, $type)." of ".user_holiday_entitlement($user)." days entitlement.<br />";

    echo appointment_type_dropdown($type, 'year');

    $sql = "SELECT * from holidays WHERE userid='{$user}' AND approved=0 AND type='$type'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    if (mysql_num_rows($result))
    {
        echo "<table align='center'>";
        echo "<tr class='shade2'><td><strong>Dates waiting for approval</strong>:</td></tr>"; // FIXME i18n waiting
        echo "<tr class='shade1'><td>";
        while ($dates = mysql_fetch_array($result))
        {
            echo date('l ', $dates['startdate']);
            if ($dates['length']=='am') echo "{$strMorning} ";
            if ($dates['length']=='pm') echo "{$strAfternoon} ";
            echo date('jS F Y', $dates['startdate']);
            echo "<br/>\n";
        }
        echo "</td></tr>\n";
        // FIXME i18n send holiday request
        echo "<tr class='shade1'><td><a href='holiday_request.php?type=$type'>Send holiday request</a></td></tr>";
        echo "</table>";
    }
    mysql_free_result($result);

}
else
{
    // Public Holidays are a special type = 10
    echo "<h2>{$strSetPublicHolidays}</h2>";
}

echo "<p align='center'>";
if (!empty($selectedday))
{
    echo "$selectedday/$selectedmonth/$selectedyear is ";
    switch ($length)
    {
        case 'am':
        echo "selected for the <strong>morning";
        break;

        case 'pm':
        echo "selected for the <strong>afternoon";
        break;

        case 'day':
        echo "selected for the <strong>full day";
        break;

        default:
        echo "<strong>not selected";
    }
    echo "</strong> ";
    echo " as ".holiday_type($type).".  ";

    if ($approved==0)
    {
        switch ($length)
        {
            case 'am':
                echo "You can make it <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=pm'>the afternoon instead</a>, or select the <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=day'>full day</a>. ";
            break;

            case 'pm':
                echo "You can make it <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=am'>the morning</a> instead, or select the <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=day'>full day</a>. ";
            break;

            case 'day':
                echo "You can make it <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=am'>the morning</a>, or <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=pm'>the afternoon</a> instead. ";
        }
        if ($length!='0')
        {
            echo "Or you can <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=0'>deselect</a> it. ";
            echo "<a href='holiday_calendar.php?type=$type&amp;user=$user' title='Clear this message'>Okay</a>.";
        }
    }
    elseif ($approved==1)
    {
        list($xtype, $xlength, $xapproved, $xapprovedby)=user_holiday($user, $type, $selectedyear, $selectedmonth, $selectedday, FALSE);
        echo "Approved by ".user_realname($xapprovedby).".";
        if ($length!='0' && $approver==TRUE && $sit[2]==$xapprovedby) echo "&nbsp;As approver for this holiday you can <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=0'>deselect</a> it.";
    }
    else
    {
        echo "<span class='error'>Declined</span>.  You should <a href='add_holiday.php?type=$type&amp;user=$user&amp;year=$selectedyear&amp;month=$selectedmonth&amp;day=$selectedday&amp;length=0'>deselect</a> it.";
    }
}
else
{
    echo "Click on a day to select it"; // FIXME i18n click on a day
}
echo "</p>\n";


echo "<h2>{$strYear} View</h2>"; // FIXME i18n Year View
$pdate=mktime(0,0,0,$month,$day,$year-1);
$ndate=mktime(0,0,0,$month,$day,$year+1);
echo "<p align='center'>";
echo "<a href='{$_SERVER['PHP_SELF']}?display=year&amp;year=".date('Y',$pdate)."&amp;month=".date('m',$pdate)."&amp;day=".date('d',$pdate)."&amp;type={$type}'>&lt;</a> ";
echo date('Y',mktime(0,0,0,$month,$day,$year));
echo " <a href='{$_SERVER['PHP_SELF']}?display=year&amp;year=".date('Y',$ndate)."&amp;month=".date('m',$ndate)."&amp;day=".date('d',$ndate)."&amp;type={$type}'>&gt;</a>";
echo "</p>";


echo "<table align='center' border='1' cellpadding='0' cellspacing='0' style='border-collapse:collapse; border-color: #AAA; width: 80%;'>";
$displaymonth=1;
$displayyear=$year;
for ($r==1;$r<3;$r++)
{
    echo "<tr>";
    for ($c=1;$c<=4;$c++)
    {
        echo "<td valign='top' align='center' class='shade1'>";
        draw_calendar($displaymonth,$displayyear);
        echo "</td>";
        if ($displaymonth==12) { $displayyear++; $displaymonth=0; }
        $displaymonth++;
    }
    echo "</tr>";
}
echo "</table>";


?>