<?php
// portalauth.inc.php - Checks whether the portal user is allowed to access the page
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// This file is to be included on any portal page that requires authentication
// This file must be included before any page output

// Prevent script from being run directly (ie. it must always be included
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
    exit;
}


session_name($CONFIG['session_name']);
session_start();

if (!isset($accesslevel))
{
    include 'portalheader.inc.php';
    echo "<p class='error'>{$strPermissionDenied}: \$accesslevel not set</p>";
    include 'htmlfooter.inc.php';
    exit;
}
elseif ($accesslevel == 'admin' AND $_SESSION['usertype'] != 'admin' AND $_SESSION['portalauth'] == TRUE)
{
    include 'portalheader.inc.php';
    echo "<p class='error'>{$strPermissionDenied}</p>";
    include 'htmlfooter.inc.php';
    exit;
}

if ($CONFIG['portal'] == FALSE)
{
    // portal disabled
    $_SESSION['portalauth'] = FALSE;
    $page = $_SERVER['PHP_SELF'];
    if (!empty($_SERVER['QUERY_STRING'])) $page .= '?'.$_SERVER['QUERY_STRING'];
    $page = urlencode($page);
    header("Location: {$CONFIG['application_webpath']}index.php?id=2&page=$page");
    exit;
}

// Check session is authenticated, if not redirect to login page
if (!isset($_SESSION['portalauth']) OR $_SESSION['portalauth'] == FALSE)
{
    $_SESSION['portalauth'] = FALSE;
    // Invalid user
    $page = $_SERVER['PHP_SELF'];
    if (!empty($_SERVER['QUERY_STRING'])) $page .= '?'.$_SERVER['QUERY_STRING'];
    $page = urlencode($page);
    header("Location: {$CONFIG['application_webpath']}index.php?id=2&page=$page");
    exit;
}
else
{
    // Attempt to prevent session fixation attacks
    session_regenerate();
    setcookie(session_name(), session_id(),ini_get("session.cookie_lifetime"), "/");
}

?>
