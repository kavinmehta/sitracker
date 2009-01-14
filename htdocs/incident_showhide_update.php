<?php
// incident_showhide_update.php - Toggle visibility of an incident
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

// External variables
$mode = cleanvar($_REQUEST['mode']);
$updateid = cleanvar($_REQUEST['updateid']);
$incidentid = cleanvar($_REQUEST['incidentid']);
$expand = cleanvar($_REQUEST['expand']);
$view = cleanvar($_REQUEST['view']);

switch ($mode)
{
  case 'show':
    //echo "Showing update: $updateid for incident $incidentid";
    $vsql = "UPDATE `{$dbUpdates}` SET customervisibility='show' WHERE id='$updateid' LIMIT 1";
  break;

  case 'hide':
    //echo "Hiding update: $updateid for incident $incidentid";
    $vsql = "UPDATE `{$dbUpdates}` SET customervisibility='hide' WHERE id='$updateid' LIMIT 1";
  break;

  default:
    trigger_error("Error showing/hiding update {$updateid}: invalid mode", E_USER_WARNING);
}

$temp_result = mysql_query($vsql);
if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

header("Location: incident_details.php?id=$incidentid&expand={$expand}&view={$view}#$updateid");
exit;
?>
