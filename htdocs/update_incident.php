<?php
// update_incident.php - For for logging updates to an incident
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

@include('set_include_path.inc.php');
$permission=8; // Update Incident
require('db_connect.inc.php');
require('functions.inc.php');

$disable_priority=TRUE;

// 19 Nov 04 - Fixed bug where currentstatus wasn't updated or inserted - ilucas

// This page requires authentication
require('auth.inc.php');

// External Variables
// $bodytext = cleanvar($_REQUEST['bodytext'],FALSE,FALSE);
$bodytext = cleanvar($_REQUEST['bodytext'], FALSE, TRUE);
$id = cleanvar($_REQUEST['id']);
$incidentid=$id;
$action = cleanvar($_REQUEST['action']);

include('incident/update.inc.php');

include('incident_html_bottom.inc.php');
exit;

?>
