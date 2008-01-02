<?php
// email_incident.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

@include('set_include_path.inc.php');
$permission=33; // Send Emails
require('db_connect.inc.php');
require('functions.inc.php');
include('mime.inc.php');

// This page requires authentication
require('auth.inc.php');

// External variables
$step = cleanvar($_REQUEST['step']);
$id = cleanvar($_REQUEST['id']);
$incidentid=$id;
$draftid = cleanvar($_REQUEST['draftid']);
if(empty($draftid)) $draftid = -1;

$title = $strEmail;

include('incident/email.inc.php');

?>
