<?php
// dashboard_tags.php - Show tags
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

$dashboard_tags_version = 1;

function dashboard_tags($dashletid)
{
    global $CONFIG, $iconset;
    echo "<div class='windowbox' id='$row-$dashboardid'>";
    echo "<div class='windowtitle'><a href='view_tags.php'>".icon('tag', 16);
    echo " {$GLOBALS['strTags']}</a></div>";
    echo "<div class='window'>";
    echo show_tag_cloud();
    echo "</div>";
    echo "</div>";
}

?>
