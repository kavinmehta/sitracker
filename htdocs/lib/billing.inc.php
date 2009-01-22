<?php
// billing.inc.php - functions relating to billing
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.

// Prevent script from being run directly (ie. it must always be included
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
    exit;
}

/**
 * Adds a closed incident to the transactions table awaiting approval
 * @author Paul Heaney
 * @param  int $incidentid The incident ID to add
 */
function add_billable_incident($incidentid)
{

}

?>
