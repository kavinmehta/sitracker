<?php
// view_task.php - Display existing task
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Authors: Kieran Hogg <kieran_hogg[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission=0; // Allow all auth users

require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

$title = $strViewTask;

// External variables
$action = $_REQUEST['action'];
$id = cleanvar($_REQUEST['incident']);
$taskid = cleanvar($_REQUEST['id']);
$mode = cleanvar($_REQUEST['mode']);

if ($mode == 'incident')
{
    include ('incident_html_top.inc.php');
}
else
{
    include ('htmlheader.inc.php');
}

require ('view_task.inc.php');
include ('htmlfooter.inc.php');

?>