<?php
// core.php - Set up paths, Initiate a database connection, grab config
//            Leave this file in the sit root directory for ease of
//            including.
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Prevent script from being run directly (ie. it must always be included
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
    exit;
}

// Define path constants, done here because this file is the first we include
define ('APPLICATION_FSPATH', realpath(dirname( __FILE__ ) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define ('APPLICATION_LIBPATH', realpath(dirname( __FILE__ ).DIRECTORY_SEPARATOR . 'lib') . DIRECTORY_SEPARATOR);
define ('APPLICATION_HELPPATH', realpath(dirname( __FILE__ ).DIRECTORY_SEPARATOR . 'help') . DIRECTORY_SEPARATOR);
define ('APPLICATION_INCPATH', realpath(dirname( __FILE__ ).DIRECTORY_SEPARATOR . 'inc') . DIRECTORY_SEPARATOR);
define ('APPLICATION_I18NPATH', realpath(dirname( __FILE__ ).DIRECTORY_SEPARATOR . 'i18n') . DIRECTORY_SEPARATOR);
define ('APPLICATION_PORTALPATH', realpath(dirname( __FILE__ ).DIRECTORY_SEPARATOR . 'portal') . DIRECTORY_SEPARATOR);
define ('APPLICATION_PLUGINPATH', realpath(dirname( __FILE__ ).DIRECTORY_SEPARATOR . 'plugins') . DIRECTORY_SEPARATOR);

// Load config defaults
include (APPLICATION_LIBPATH.'defaults.inc.php');
// Server Configuration
@include ('/etc/webtrack.conf');  // Legacy, for compatibility
@include ('/etc/sit.conf');
// Load config file with customisations
@include (APPLICATION_FSPATH . "config.inc.php");

// TODO determine which language to use, for now we're hardcoded to English (British)
// i18n
@include (APPLICATION_I18NPATH . 'en-gb.inc.php');

if (!function_exists("getmicrotime"))
{
    // Set Start Time for Execution Timing
    function getmicrotime()
    {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
    $exec_time_start = getmicrotime();
}

if ($CONFIG['db_username'] == '' OR $CONFIG['db_database'] == '')
{
    $msg = urlencode(base64_encode("Could not connect to the database because the database configuration is missing. Have you configured database settings?  Can your config file be read?"));
    header("Location: setup.php?msg={$msg}&new=1");
    exit;
}

// Connect to Database server
$db = @mysql_connect($CONFIG['db_hostname'], $CONFIG['db_username'], $CONFIG['db_password']);
if (mysql_error())
{
    $msg = urlencode(base64_encode("Could not connect to database server '{$CONFIG['db_hostname']}'"));
    header("Location: {$CONFIG['application_webpath']}setup.php?msg={$msg}");
    exit;
}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET utf8");

// mysql_query("SET time_zone = {$CONFIG['timezone']}");

// Select database
mysql_select_db($CONFIG['db_database'], $db);
if (mysql_error())
{
    // TODO add some detection for missing database
    if (strpos(mysql_error(), 'Unknown database') !== FALSE)
    {
        $msg = urlencode(base64_encode("Unknown Database '{$CONFIG['db_database']}'"));
        header("Location: {$CONFIG['application_webpath']}setup.php?msg={$msg}");
        exit;
    }
    // Attempt socket connection to database to check if server is alive
    if (!fsockopen($CONFIG['db_hostname'], 3306, $errno, $errstr, 5))
    {
        trigger_error("!Error: No response from database server within 5 seconds, Database Server ({$CONFIG['db_hostname']}) is probably down - contact a Systems Administrator",E_USER_ERROR);
    }
    else
    {
        $msg = urlencode(base64_encode("Unknown Database '{$CONFIG['db_database']}' / Failed to connect"));
        header("Location: {$CONFIG['application_webpath']}setup.php?msg={$msg}");
        exit;
    }
}
// Soft table names
require (APPLICATION_LIBPATH . 'tablenames.inc.php');

// Read config from database (this overrides any config in the config files
$sql = "SELECT * FROM `{$dbConfig}`";
$result = @mysql_query($sql);
if ($result AND mysql_num_rows($result) > 0)
{
    while ($conf = mysql_fetch_object($result))
    {
        if ($conf->value==='TRUE') $conf->value = TRUE;
        if ($conf->value==='FALSE') $conf->value = FALSE;
        if (substr($conf->value, 0, 6)=='array(')
        {
                eval("\$val = {$conf->value};");
                $conf->value = $val;
        }
        $CONFIG[$conf->config] = $conf->value;
    }
}

?>