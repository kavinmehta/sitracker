<?php
// maintenance_details.php - Show contract details
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
// Created: 20th August 2001
// Purpose: Show All Maintenance Contract Details
// This Page Is Valid XHTML 1.0 Transitional! 27Oct05

@include ('set_include_path.inc.php');
$permission=19;  // view Maintenance contracts

require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

$id = cleanvar($_REQUEST['id']);

include ('htmlheader.inc.php');

// Display Maintenance



echo contract_details($id);

include ('htmlfooter.inc.php');
?>
