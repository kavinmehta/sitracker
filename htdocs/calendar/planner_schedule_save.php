<?php

// planner_schedule_save.php - create or update tasks based on calendar
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Tom Gerrard <tom.gerrard[at]salfordsoftware.co.uk>

$permission=27; // View your calendar
require('db_connect.inc.php');
require('functions.inc.php');
require('auth.inc.php');
include('calendar.inc.php');

header('Content-Type: text/plain');

foreach(array(
			  'saveAnItem',
			  'description',
			  'newItem',
			  'eventStartDate',
			  'eventEndDate',
              'droptarget',
              'week',
			  'id',
              'name',
              'user'
			  ) as $var)
{
	eval("\$$var=cleanvar(\$_REQUEST['$var']);");
}

$startDate = strtotime($eventStartDate);
$endDate = strtotime($eventEndDate);

if (isset($_GET['saveAnItem']))
{
    switch($newItem)
    {

        case 2:
            $day = substr($droptarget,-1) - 1;
            $startDate = $week / 1000 + 86400 * $day + $CONFIG['start_working_day'] - 3600;
            $endDate = $week / 1000 + 86400 * $day + $CONFIG['end_working_day'] - 3600;

        case 1:
            echo book_appointment($name, $description, $user, $startDate, $endDate);
        break;

        case 0:
            $sql = "update tasks set description='" . mysql_escape_string($description) .
                    "',name='". mysql_escape_string($name) .
                    "',startdate='".date("Y-m-d H:i:s",strtotime($eventStartDate)) .
                    "',enddate='".date("Y-m-d H:i:s",strtotime($eventEndDate)) .
                    "' where id='" . $id . "' and completion < '1'";
            mysql_query($sql);
            echo $sql;
        break;
	}
    echo mysql_error();
}

?>