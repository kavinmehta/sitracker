<?php
// control_panel.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

@include ('set_include_path.inc.php');
$permission = 4; // Edit your profile

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

include ('htmlheader.inc.php');
echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/settings.png' alt='{$strControlPanel}' /> {$CONFIG['application_shortname']} {$strControlPanel}</h2>";
echo "<table align='center'>";
echo "<thead>";
echo "<tr><th>{$strUserSettings}</th></tr>";
echo "</thead>";
echo "<tbody>";
echo "<tr><td><a href='edit_profile.php'>{$strMyProfile}</a></td></tr>";
echo "<tr><td><a href='edit_user_skills.php'>{$strMySkills}</a></td></tr>";
echo "<tr><td><a href='edit_backup_users.php'>{$strMySubstitutes}</a></td></tr>";
echo "<tr><td><a href='holidays.php'>{$strMyHolidays}</a></td></tr>";
echo "</tbody>\n";
if (user_permission($sit[2],42)) // Review/Delete Incident Updates
{
    echo "<thead><tr><th>{$strTechnicalSupportAdmin}</th></tr></thead>";
    echo "<tbody><tr><td><a href='review_incoming_updates.php'>{$strHoldingQueue}</a></td></tr></tbody>";
}

if (user_permission($sit[2],44)) // FTP Publishing
{
    echo "<thead><tr><th>{$strFiles}</th></tr></thead>";
    echo "<tbody><tr><td><a href='ftp_list_files.php'>{$strManageFTPFiles}</a></td></tr></tbody>";
}
if (user_permission($sit[2],50)) // Approve holidays
{
    echo "<thead><tr><th>{$strManageUsers}</th></tr></thead>";
    echo "<tbody><tr><td><a href='holiday_request.php?user=all&mode=approval'>{$strApproveHolidays}</a></td></tr></tbody>";
}
if (user_permission($sit[2],22)) // Administrate
{
    echo "<thead><tr><th>{$strAdministratorsOnly}</th></tr></thead>";
    echo "<tbody>";
    echo "<tr><td><a href='manage_users.php'>{$strManageUsers}</a></td></tr>";
    echo "<tr><td><a href='templates.php'>{$strManageEmailTemplates}</a></td></tr>";
    echo "<tr><td><a href='browse_journal.php'>{$strBrowse} {$CONFIG['application_shortname']} {$strJournal}</a></td></tr>";
    echo "<tr><td><a href='service_levels.php'>{$strServiceLevels}</a></td></tr>";
    echo "<tr><td><a href='add_productinfo.php?action=showform'>{$strAddProductInformation}</a></td></tr>";
    echo "<tr><td><a href='holiday_calendar.php?type=10'>{$strSetPublicHolidays}</a></td></tr>";
    echo "<tr><td><a href='show_orphaned_contacts.php'>{$strShowOrphandedContacts}</a></td></tr>";
    echo "</tbody>";
}

plugin_do('cp_menu');
echo "</table>\n";
include ('htmlfooter.inc.php');
?>