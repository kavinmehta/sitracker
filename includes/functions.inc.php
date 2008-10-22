<?php
// functions.inc.php - Function library and defines for SiT -Support Incident Tracker
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Authors: Ivan Lucas, <ivanlucas[at]users.sourceforge.net
//          Tom Gerrard, <tomgerrard[at]users.sourceforge.net> - 2001 onwards
//          Martin Kilcoyne - 2000
//          Paul Heaney, <paulheaney[at]users.sourceforge.net>
//          Kieran Hogg, <kieran_hogg[at]users.sourceforge.net>

// Many functions here simply extract various snippets of information from
// Most are legacy and can replaced by improving the pages that call them to
// use SQL joins.

include ('classes.inc.php');

// Version number of the application, (numbers only)
$application_version = '3.40';
// Revision string, e.g. 'beta2' or 'svn' or ''
$application_revision = 'svn';

// Append SVN data for svn versions
if ($application_revision == 'svn')
{
    // Add the svn revision number
    preg_match('/([0-9]+)/','$LastChangedRevision$',$revision);
    $application_revision .= $revision[0];
}

// Clean PHP_SELF server variable to avoid potential XSS security issue
$_SERVER['PHP_SELF'] = substr($_SERVER['PHP_SELF'], 0,
                              (strlen($_SERVER['PHP_SELF'])
                              - @strlen($_SERVER['PATH_INFO'])));
// Report all PHP errors
error_reporting(E_ALL);
$oldeh = set_error_handler("sit_error_handler");

if (version_compare(PHP_VERSION, "5.1.0", ">="))
{
    date_default_timezone_set($CONFIG['timezone']);
}

// try to figure out what path delimeter is being used (for windows or unix)...
$fsdelim = (strstr($_SERVER['SCRIPT_FILENAME'],"/")) ? "/" : "\\";

// Journal Logging
// 0 = No logging
// 1 = Minimal Logging
// 2 = Normal Logging
// 3 = Full Logging
// 4 = Maximum/Debug Logging
define ('CFG_LOGGING_OFF',0);
define ('CFG_LOGGING_MIN',1);
define ('CFG_LOGGING_NORMAL',2);
define ('CFG_LOGGING_FULL',3);
define ('CFG_LOGGING_MAX',4);

define ('CFG_JOURNAL_DEBUG', 0);     // 0 = for internal debugging use
define ('CFG_JOURNAL_LOGIN', 1);     // 1 = Logon/Logoff
define ('CFG_JOURNAL_SUPPORT', 2);   // 2 = Support Incidents
define ('CFG_JOURNAL_SALES', 3);     // 3 = Sales Incidents
define ('CFG_JOURNAL_SITES', 4);     // 4 = Sites
define ('CFG_JOURNAL_CONTACTS', 5);  // 5 = Contacts
define ('CFG_JOURNAL_ADMIN', 6);     // 6 = Admin
define ('CFG_JOURNAL_USER', 7);       // 7 = User Management
define ('CFG_JOURNAL_MAINTENANCE', 8);  // 8 = Maintenance Contracts
define ('CFG_JOURNAL_PRODUCTS', 9);
define ('CFG_JOURNAL_OTHER', 10);
define ('CFG_JOURNAL_KB', 11);    // Knowledge Base

define ('TAG_CONTACT', 1);
define ('TAG_INCIDENT', 2);
define ('TAG_SITE', 3);
define ('TAG_TASK', 4);
define ('TAG_PRODUCT', 5);
define ('TAG_SKILL', 6);
define ('TAG_KB_ARTICLE', 7);
define ('TAG_REPORT', 8);

define ('NOTE_TASK', 10);

define ('HOL_HOLIDAY', 1); // Holiday/Leave
define ('HOL_SICKNESS', 2);
define ('HOL_WORKING_AWAY', 3);
define ('HOL_TRAINING', 4);
define ('HOL_FREE', 5); // Compassionate/Maternity/Paterity/etc/free
define ('HOL_PUBLIC', 10);  // Public Holiday (eg. Bank Holiday)

//default notice types
define ('NORMAL_NOTICE_TYPE', 0);
define ('WARNING_NOTICE_TYPE', 1);
define ('CRITICAL_NOTICE_TYPE', 2);
define ('TRIGGER_NOTICE_TYPE', 3);

// Incident statuses
define ("STATUS_ACTIVE",1);
define ("STATUS_CLOSED",2);
define ("STATUS_RESEARCH",3);
define ("STATUS_LEFTMESSAGE",4);
define ("STATUS_COLLEAGUE",5);
define ("STATUS_SUPPORT",6);
define ("STATUS_CLOSING",7);
define ("STATUS_CUSTOMER",8);
define ("STATUS_UNSUPPORTED",9);
define ("STATUS_UNASSIGNED",10);

// BILLING
define ('NO_BILLABLE_CONTRACT', 0);
define ('CONTACT_HAS_BILLABLE_CONTRACT', 1);
define ('SITE_HAS_BILLABLE_CONTRACT', 2);

// Decide which language to use and setup internationalisation
require ('i18n/en-GB.inc.php');
if ($CONFIG['default_i18n'] != 'en-GB')
{
    @include ("i18n/{$CONFIG['default_i18n']}.inc.php");
}
if (!empty($_SESSION['lang'])
    AND $_SESSION['lang'] != $CONFIG['default_i18n'])
{
    include ("i18n/{$_SESSION['lang']}.inc.php");
}
ini_set('default_charset', $i18ncharset);

// Time settings
$now = time();
$today = $now+(16*3600);  // next 16 hours, based on reminders being run at midnight this is today
$lastweek = $now - (7 * 86400); // the previous seven days
$todayrecent = $now-(16*3600);  // past 16 hours

$CONFIG['upload_max_filesize'] = return_bytes($CONFIG['upload_max_filesize']);

// Set a string to be the full version number and revision of the application
$application_version_string = trim("v{$application_version} {$application_revision}");


//Prevent Magic Quotes from affecting scripts, regardless of server settings
//Make sure when reading file data,
//PHP doesn't "magically" mangle backslashes!
set_magic_quotes_runtime(FALSE);

if (get_magic_quotes_gpc())
{

//     All these global variables are slash-encoded by default,
//     because    magic_quotes_gpc is set by default!
//     (And magic_quotes_gpc affects more than just $_GET, $_POST, and $_COOKIE)
//     We don't strip slashes from $_FILES as of 3.32 as this should be safe without
//     doing and it will break windows file paths if we do
    $_SERVER = stripslashes_array($_SERVER);
    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);
    $_COOKIE = stripslashes_array($_COOKIE);
    $_ENV = stripslashes_array($_ENV);
    $_REQUEST = stripslashes_array($_REQUEST);
    $HTTP_SERVER_VARS = stripslashes_array($HTTP_SERVER_VARS);
    $HTTP_GET_VARS = stripslashes_array($HTTP_GET_VARS);
    $HTTP_POST_VARS = stripslashes_array($HTTP_POST_VARS);
    $HTTP_COOKIE_VARS = stripslashes_array($HTTP_COOKIE_VARS);
    $HTTP_POST_FILES = stripslashes_array($HTTP_POST_FILES);
    $HTTP_ENV_VARS = stripslashes_array($HTTP_ENV_VARS);
    if (isset($_SESSION))
    {
        #These are unconfirmed (?)
        $_SESSION = stripslashes_array($_SESSION, '');
        $HTTP_SESSION_VARS = stripslashes_array($HTTP_SESSION_VARS, '');
    }
//     The $GLOBALS array is also slash-encoded, but when all the above are
//     changed, $GLOBALS is updated to reflect those changes.  (Therefore
//     $GLOBALS should never be modified directly).  $GLOBALS also contains
//     infinite recursion, so it's dangerous...
}


require ('triggers.inc.php');


/**
    * Strip slashes from an array
    * @param $data an array
    * @return An array with slashes stripped
*/
function stripslashes_array($data)
{
    if (is_array($data))
    {
        foreach ($data as $key => $value)
        {
            $data[$key] = stripslashes_array($value);
        }
        return $data;
    }
    else
    {
        return stripslashes($data);
    }
}


/**
    * Authenticate a user with a username/password pair
    * @author Ivan Lucas
    * @param $username string. A username
    * @param $password string. An MD5 password
    * @return an integer to indicate whether the user should be allowed to continue
    * @retval 0 the credentials were wrong or the user was not found. the user should not be allowed to continue
    * @retval 1 to indicate user is authenticated and allowed to continue.
*/
function authenticate($username, $password)
{
    global $dbUsers;
    if ($_SESSION['auth'] == TRUE)
    {
        // Already logged in
        return 1;
    }

    // extract user
    $sql  = "SELECT id FROM `{$dbUsers}` ";
    $sql .= "WHERE username = '$username' AND password = '$password' AND status!= 0 ";
    // a status of 0 means the user account is disabled
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    // return appropriate value
    if (mysql_num_rows($result) == 0)
    {
        mysql_free_result($result);
        return 0;
    }
    else
    {
        journal(4,'User Authenticated',"$username authenticated from ".getenv('REMOTE_ADDR'),1,0);
        return 1;
    }
}


/**
    * Returns a specified column from a specified table in the database given an ID primary key
    * @author Ivan Lucas
    * @param $column a database column as a string
    * @param $table a database table as a string
    * @param $id the primary key / id column
    * @return A column from the database
    * @note it's not always efficient to read a single column at a time, but when you only need
    *  one column, this is handy
*/
function db_read_column($column, $table, $id)
{
    $sql = "SELECT `$column` FROM `{$table}` WHERE id ='$id' LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    list($column) = mysql_fetch_row($result);

    return $column;
}


/**
    * Returns TRUE or FALSE to indicate whether a given user has a given permission
    * @author Ivan Lucas
    * @param $userid integer. The userid to check
    * @param $permission integer or array. The permission id to check, or an array of id's to check
    * @return boolean. TRUE if the user has the permission (or all the permissions in the array), otherwise FALSE
*/
function user_permission($userid,$permission)
{
    // Default is no access
    $accessgranted = FALSE;

    if (!is_array($permission))
    {
        $permission = array($permission);
    }

    foreach ($permission AS $perm)
    {
        if (@in_array($perm, $_SESSION['permissions']) == TRUE) $accessgranted = TRUE;
        else $accessgranted = FALSE;
        // Permission 0 is always TRUE (general acess)
        if ($perm == 0) $accessgranted = TRUE;
    }
    return $accessgranted;
}


/**
    * @author Ivan Lucas
*/
function permission_name($permissionid)
{
    global $dbPermissions;
    $name = db_read_column('name', $dbPermissions, $permissionid);
    if (empty($name)) $name = $GLOBALS['strUnknown'];
    return $name;
}


/**
    * Get the name associated with software ID / skill ID
    * @author Ivan Lucas
    * @param $softwareid integer
    * @returns string. Skill/Software Name
    * @note Software was renamed skills for v3.30
    * @todo FIXME i18n
*/
function software_name($softwareid)
{
    global $now, $dbSoftware, $strEOL, $strEndOfLife;

    $sql = "SELECT * FROM `{$dbSoftware}` WHERE id = '{$softwareid}'";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) >= 1)
    {
        $software = mysql_fetch_object($result);
        $lifetime_end = mysql2date($software->lifetime_end);
        if ($lifetime_end > 0 AND $lifetime_end < $now)
        {
            $name = "<span class='deleted'>{$software->name}</span> (<abbr title='{$strEndOfLife}'>{$strEOL}</abbr>)";
        }
        else
        {
            $name = $software->name;
        }
    }
    else
    {
        $name = $GLOBALS['StrUnknown'];
    }

    return $name;
}


/**
    * Returns an integer representing the id of the user identified by his/her username and password
    * @author Ivan Lucas
    * @param $username string. A username
    * @param $password string. An MD5 hashed password
    * @return integer. the users ID or 0 if the user does not exist (username/password did not match)
    * @retval 0 The user did not exist
    * @retval >=1 The userid of the matching user
    * @note Returns 0 if the given user does not exist
*/
function user_id($username, $password)
{
    global $dbUsers;
    $sql  = "SELECT id FROM `{$dbUsers}` ";
    $sql .= "WHERE username='$username' AND password='$password'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    if (mysql_num_rows($result) == 0)
    {
        $userid= 0;
    }
    else
    {
        $user = mysql_fetch_array($result);
        $userid=$user['id'];
    }
    return $userid;
}


/**
    * Return a users password
    * @author Ivan Lucas
    * @param id int. User ID
    * @note this is an MD5 hash
*/
function user_password($id)
{
    global $dbUsers;
    return db_read_column('password', $dbUsers, $id);
}


/**
    * Return a users real name
    * @author Ivan Lucas
    * @param $id integer. A user ID
    * @param $allowhtml boolean. may return HTML if TRUE, only ever returns plain text if FALSE
    * @note If $allowhtml is TRUE disabled user accounts are returned as HTML with span class 'deleted'
*/
function user_realname($id, $allowhtml = FALSE)
{
    global $update_body;
    global $incidents;
    global $CONFIG;
    global $dbUsers, $dbEscalationPaths;
    if ($id >= 1)
    {
        if ($id == $_SESSION['userid'])
        {
            return $_SESSION['realname'];
        }
        else
        {
            $sql = "SELECT realname, status FROM `{$dbUsers}` WHERE id='$id' LIMIT 1";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            list($realname, $status) = mysql_fetch_row($result);
            if ($allowhtml == FALSE OR $status > 0)
            {
                return $realname;
            }
            else
            {
                return "<span class='deleted'>{$realname}</span>";
            }
        }
    }
    elseif (!empty($incidents['email']))
    {
        // TODO this code does not belong here
        // The SQL is also looking at all escalation paths not just the relevant
        // one.
        //an an incident
        preg_match('/From:[ A-Za-z@\.]*/', $update_body, $from);
        if (!empty($from))
        {
            $frommail = strtolower(substr(strstr($from[0], '@'), 1));
            $customerdomain = strtolower(substr(strstr($incidents['email'], '@'), 1));

            if ($frommail == $customerdomain) return $GLOBALS['strCustomer'];

            $sql = "SELECT name, email_domain FROM `{$dbEscalationPaths}`";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            while ($escpath = mysql_fetch_object($result))
            {
                if (!empty($escpath->email_domain))
                {
                    if (strstr(strtolower($frommail), strtolower($escpath->email_domain)))
                    {
                        return $escpath->name;
                    }
                }
            }
        }
    }

    //Got this far not returned anything so
    return $CONFIG['application_shortname']; // No from email address
}


/**
    * Return a users email address
    * @author Ivan Lucas
    * @param id int. User ID
    * @note Obtained from session if possible
*/
function user_email($id)
{
    global $dbUsers;
    if ($id == $_SESSION['userid'])
    {
        return $_SESSION['email'];
    }
    else
    {
        return db_read_column('email', $dbUsers, $id);
    }
}


/**
    * Return a users phone number
    * @author Ivan Lucas
    * @param id int. User ID
*/
function user_phone($id)
{
    return db_read_column('phone', $GLOBALS['dbUsers'], $id);
}


/**
    * Return a users mobile phone number
    * @author Ivan Lucas
    * @param id int. User ID
*/
function user_mobile($id)
{
    return db_read_column('mobile', $GLOBALS['dbUsers'], $id);
}


/**
    * Return a users email signature
    * @author Ivan Lucas
    * @param id int. User ID
*/
function user_signature($id)
{
    return db_read_column('signature', $GLOBALS['dbUsers'], $id);
}


/**
    * Return a users away message
    * @author Ivan Lucas
    * @param id int. User ID
*/
function user_message($id)
{
    return db_read_column('message', $GLOBALS['dbUsers'], $id);
}


/**
    * Return a users current away status
    * @author Ivan Lucas
    * @param id int. User ID
    * @note 0 means user account disabled
*/
function user_status($id)
{
    return db_read_column('status', $GLOBALS['dbUsers'], $id);
}


/**
    * Check whether the given user is accepting
    * @author Ivan Lucas
    * @param $id The userid of the user to check
    * @returns string
    * @retval 'Yes' User is accepting
    * @retval 'No' User is not accepting
    * @retval 'NoSuchUser' The given user does not exist
*/
function user_accepting($id)
{
    $accepting = db_read_column('accepting', $GLOBALS['dbUsers'], $id);
    if ($accepting == '')  $accepting = "NoSuchUser";

    return $accepting;
}


/**
    * Count the number of active incidents for a given user
    * @author Ivan Lucas
    * @param $id The userid of the user to check
    * @returns int
*/
function user_activeincidents($userid)
{
    global $CONFIG, $now, $dbIncidents, $dbContacts, $dbPriority;
    $count = 0;

    // This SQL must match the SQL in incidents.php
    $sql = "SELECT COUNT(i.id)  ";
    $sql .= "FROM `{$dbIncidents}` AS i, `{$dbContacts}` AS c, `{$dbPriority}` AS pr WHERE contact = c.id AND i.priority = pr.id ";
    $sql .= "AND (owner='{$userid}' OR towner='{$userid}') ";
    $sql .= "AND (status!='2') ";  // not closed
    // the "1=2" obviously false else expression is to prevent records from showing unless the IF condition is true
    $sql .= "AND ((timeofnextaction > 0 AND timeofnextaction < $now) OR ";
    $sql .= "(IF ((status >= 5 AND status <=8), ($now - lastupdated) > ({$CONFIG['regular_contact_days']} * 86400), 1=2 ) ";  // awaiting
    $sql .= "OR IF (status='1' OR status='3' OR status='4', 1=1 , 1=2) ";  // active, research, left message - show all
    $sql .= ") AND timeofnextaction < $now ) ";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    else list($count) = mysql_fetch_row($result);

    return ($count);
}


/**
    * Counts a users open incidents
    * @author Ivan Lucas
    * @param $id The userid of the user to check
    * @returns int
    * @note This number will never match the number shown in the active queue and is not meant to
*/
function user_countincidents($id)
{
    global $dbIncidents;
    $count = 0;

    $sql = "SELECT COUNT(id) FROM `{$dbIncidents}` WHERE (owner='{$id}' OR towner='{$id}') AND (status!=2)";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    else list($count) = mysql_fetch_row($result);

    return ($count);
}


/**
    * Counts number of incidents and priorty for a given user
    * @author Ivan Lucas
    * @param $id The userid of the user to check
    * @returns array
*/
function user_incidents($id)
{
    global $dbIncidents;
    $sql = "SELECT priority, count(priority) AS num FROM `{$dbIncidents}` ";
    $sql .= "WHERE (owner = $id OR towner = $id) AND status != 2 ";
    $sql .= "GROUP BY priority";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $arr = array('1' => '0', '2' => '0', '3' => '0', '4' => '0');

    if (mysql_num_rows($result) > 0)
    {
        while ($count = mysql_fetch_array($result))
        {
            $arr[$count['priority']] = $count['num'];
        }
    }
    return $arr;
}


/**
    * gets users holiday information for a certain day given an optional type
    * and optional length returns both type and length and approved as an array
    * @author Ivan Lucas
    * @param $userid integer. The userid of the holiday to retrieve
    * @param $type integer. The holiday type. e.g. sickness
    * @param $year integer. Year. eg. 2008
    * @param $month integer. Month. eg. 11 = November
    * @param $day integer. Day
    * @param $length string. 'am', 'pm', 'day' or FALSE to list all
    * @returns array
*/
function user_holiday($userid, $type= 0, $year, $month, $day, $length = FALSE)
{
    global $dbHolidays;
    $startdate = mktime(0,0,0,$month,$day,$year);
    $enddate = mktime(23,59,59,$month,$day,$year);
    $sql = "SELECT * FROM `{$dbHolidays}` WHERE startdate >= '$startdate' AND startdate < '$enddate' ";
    if ($type !=0 )
    {
        $sql .= "AND (type='$type' OR type='10' OR type='5') ";
        $sql .= "AND IF(type!=10, userid='$userid', 1=1) ";
    }
    else
    {
        $sql .= " AND userid='$userid' ";
    }

    if ($length != FALSE)
    {
        $sql .= "AND length='$length' ";
    }

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 0)
    {
        return FALSE;
    }
    else
    {
        $totallength = 0;
        while ($holiday = mysql_fetch_object($result))
        {
            $type = $holiday->type;
            $length = $holiday->length;
            $approved = $holiday->approved;
            $approvedby = $holiday->approvedby;
            // hmm... not sure these next lines are required.
            if ($length=='am' && $totallength == 0) $totallength = 'am';
            if ($length=='pm' && $totallength == 0) $totallength = 'pm';
            if ($length=='am' && $totallength == 'pm') $totallength = 'day';
            if ($length=='pm' && $totallength == 'am') $totallength = 'day';
            if ($length=='day') $totallength = 'day';
        }
        return array($type, $totallength, $approved, $approvedby);
    }
}


/**
    * Count a users holidays of specified type
    * @author Ivan Lucas
    * @param $userid integer. User ID
    * @param $type integer. Holiday type
    * @param $date integer. (optional) UNIX timestamp. Only counts holidays before this date
    * @returns integer. Number of days holiday
*/
function user_count_holidays($userid, $type, $date=0, $approved=array(0,1,2))
{
    global $dbHolidays;
    $sql = "SELECT id FROM `{$dbHolidays}` WHERE userid='$userid' AND type='$type' AND length='day' AND approved >= 0 AND approved < 2 ";
    if ($date > 0) $sql .= "AND startdate < {$date}";
    if (is_array($approved))
    {
        $sql .= "AND (";

        for ($i = 0; $i < sizeof($approved); $i++)
        {
            $sql .= "approved = {$approved[$i]} ";
            if ($i < sizeof($approved)-1) $sql .= "OR ";
        }

        $sql .= ") ";
    }
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $full_days = mysql_num_rows($result);


    $sql = "SELECT id FROM `{$dbHolidays}` ";
    $sql .= "WHERE userid='{$userid}' AND type='{$type}' AND (length='pm' OR length='am') AND approved >= 0 AND approved < 2 ";

    if ($date > 0)
    {
        $sql .= "AND startdate < $date";
    }

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $half_days = mysql_num_rows($result);

    $days_holiday = $full_days + ($half_days / 2);
    return $days_holiday;
}


/**
    * Return the users holiday entitlement
    * @author Ivan Lucas
    * @param $userid integer. User ID
    * @returns integer. Number of days holiday a user is entitled to
*/
function user_holiday_entitlement($userid)
{
    return db_read_column('holiday_entitlement', $GLOBALS['dbUsers'], $userid);
}


/**
    * Find a contacts real name
    * @author Ivan Lucas
    * @param $id integer. Contact ID
    * @returns string. Full name or 'Unknown'
*/
function contact_realname($id)
{
    global $dbContacts;
    $sql = "SELECT forenames, surname FROM `{$dbContacts}` WHERE id='$id'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 0)
    {
        mysql_free_result($result);
        return ($GLOBALS['strUnknown']);
    }
    else
    {
        $contact = mysql_fetch_array($result);
        $realname = $contact['forenames'].' '.$contact['surname'];
        mysql_free_result($result);
        return $realname;
    }
}


/**
    * Return a contacts site name
    * @author Ivan Lucas
    * @param $id integer. Contact ID
    * @returns string. Full site name or 'Unknown'
    * @note this returns the site _NAME_ not the siteid for the site id use contact_siteid()
*/
function contact_site($id)
{
    global $dbContacts, $dbSites;
    //
    $sql = "SELECT s.name FROM `{$dbContacts}` AS c, `{$dbSites}` AS s WHERE c.siteid = s.id AND c.id = '$id'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 0)
    {
        mysql_free_result($result);
        return $GLOBALS['strUnknown'];
    }
    else
    {
        list($contactsite) = mysql_fetch_row($result);
        mysql_free_result($result);
        $contactsite = $contactsite;
        return $contactsite;
    }
}


/**
    * Return a contacts site ID
    * @author Ivan Lucas
    * @param $id integer. Contact ID
    * @returns integer. Site ID
*/
function contact_siteid($id)
{
    return db_read_column('siteid', $GLOBALS['dbContacts'], $id);
}


/**
    * Return a contacts email address
    * @author Ivan Lucas
    * @param $id integer. Contact ID
    * @returns string. Email address
*/
function contact_email($id)
{
    return db_read_column('email', $GLOBALS['dbContacts'], $id);
}


/**
    * Return a contacts phone number
    * @author Ivan Lucas
    * @param $id integer. Contact ID
    * @returns string. Phone number
*/
function contact_phone($id)
{
    return db_read_column('phone', $GLOBALS['dbContacts'], $id);
}


/**
    * Return a contacts fax number
    * @author Ivan Lucas
    * @param $id integer. Contact ID
    * @returns string. Fax number
*/
function contact_fax($id)
{
    return db_read_column('fax', $GLOBALS['dbContacts'], $id);
}


/**
    * Return the number of incidents ever logged against a contact
    * @author Ivan Lucas
    * @param $id integer. Contact ID
    * @returns int.
*/
function contact_count_incidents($id)
{
    global $dbIncidents;
    $count = 0;

    $sql = "SELECT COUNT(id) FROM `{$dbIncidents}` WHERE contact='$id'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    else list($count) = mysql_fetch_row($result);
    mysql_free_result($result);

    return $count;
}

/**
    * Return the number of incidents ever logged against a site
    * @author Kieran
    * @param $id integer. Site ID
    * @returns int.
*/
function site_count_incidents($id)
{
    global $dbIncidents, $dbContacts;
    $id = intval($id);
    $count = 0;

    $sql = "SELECT COUNT(i.id) FROM `{$dbIncidents}` AS i, `{$dbContacts}` as c ";
    $sql .= "WHERE i.contact = c.id ";
    $sql .= "AND c.siteid='$id'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    else list($count) = mysql_fetch_row($result);
    mysql_free_result($result);

    return $count;
}


/**
    * Return the number of inventory items for a site
    * @author Kieran
    * @param $id integer. Site ID
    * @returns int.
*/
function site_count_inventory_items($id)
{
    global $dbInventory;
    $count = 0;

    $sql = "SELECT COUNT(id) FROM `{$dbInventory}` WHERE siteid='$id'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    else list($count) = mysql_fetch_row($result);
    mysql_free_result($result);

    return $count;
}


/**
    * Return the number of inventory items for a contact
    * @author Kieran
    * @param $id integer. Contact ID
    * @returns int.
*/
function contact_count_inventory_items($id)
{
    global $dbInventory;
    $count = 0;

    $sql = "SELECT COUNT(id) FROM `{$dbInventory}` WHERE contactid='$id'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    else list($count) = mysql_fetch_row($result);
    mysql_free_result($result);

    return $count;
}



/**
    * The number representing the total number of currently OPEN incidents submitted by a given contact.
    * @author Ivan Lucas
    * @param $id The Contact ID to check
    * @returns integer. The number of currently OPEN incidents for the given contact
*/
function contact_count_open_incidents($id)
{
    global $dbIncidents;
    $sql = "SELECT COUNT(id) FROM `{$dbIncidents}` WHERE contact=$id AND status<>2";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    list($count) = mysql_fetch_row($result);
    mysql_free_result($result);

    return $count;
}


/**
    * Creates a vcard electronic business card for the given contact
    * @author Ivan Lucas
    * @param $id integer Contact ID
    * @returns string vcard
*/
function contact_vcard($id)
{
    global $dbContacts, $dbSites;
    $sql = "SELECT *, s.name AS sitename, s.address1 AS siteaddress1, s.address2 AS siteaddress2, ";
    $sql .= "s.city AS sitecity, s.county AS sitecounty, s.country AS sitecountry, s.postcode AS sitepostcode ";
    $sql .= "FROM `{$dbContacts}` AS c, `{$dbSites}` AS s ";
    $sql .= "WHERE c.siteid = s.id AND c.id = '$id' LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $contact = mysql_fetch_object($result);
    $vcard = "BEGIN:VCARD\r\n";
    $vcard .= "N:{$contact->surname};{$contact->forenames};{$contact->courtesytitle}\r\n";
    $vcard .= "FN:{$contact->forenames} {$contact->surname}\r\n";
    if (!empty($contact->jobtitle)) $vcard .= "TITLE:{$contact->jobtitle}\r\n";
    if (!empty($contact->sitename)) $vcard .= "ORG:{$contact->sitename}\r\n";
    if ($contact->dataprotection_phone != 'Yes') $vcard .= "TEL;TYPE=WORK:{$contact->phone}\r\n";
    if ($contact->dataprotection_phone != 'Yes' && !empty($contact->fax))
    {
        $vcard .= "TEL;TYPE=WORK;TYPE=FAX:{$contact->fax}\r\n";
    }

    if ($contact->dataprotection_phone != 'Yes' && !empty($contact->mobile))
    {
        $vcard .= "TEL;TYPE=WORK;TYPE=CELL:{$contact->mobile}\r\n";
    }

    if ($contact->dataprotection_email != 'Yes' && !empty($contact->email))
    {
        $vcard .= "EMAIL;TYPE=INTERNET:{$contact->email}\r\n";
    }

    if ($contact->dataprotection_address != 'Yes')
    {
        if ($contact->address1 != '')
        {
            $vcard .= "ADR;WORK:{$contact->address1};{$contact->address2};{$contact->city};{$contact->county};{$contact->postcode};{$contact->country}\r\n";
        }
        else
        {
            $vcard .= "ADR;WORK:{$contact->siteaddress1};{$contact->siteaddress2};{$contact->sitecity};{$contact->sitecounty};{$contact->sitepostcode};{$contact->sitecountry}\r\n";
        }
    }
    if (!empty($contact->notes))
    {
        $vcard .= "NOTE:{$contact->notes}\r\n";
    }

    $vcard .= "REV:".iso_8601_date($contact->timestamp_modified)."\r\n";
    $vcard .= "END:VCARD\r\n";
    return $vcard;
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns integer. UserID of the user that currently owns the incident
*/
function incident_owner($id)
{
    return db_read_column('owner', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns integer. UserID of the user that currently temporarily owns the incident
*/
function incident_towner($id)
{
    return db_read_column('towner', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns integer. ContactID of the contact this incident is logged against
*/
function incident_contact($id)
{
    return db_read_column('contact', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns integer. Contract ID of the maintenance contract this incident is logged against
*/
function incident_maintid($id)
{
    $maintid = db_read_column('maintenanceid', $GLOBALS['dbIncidents'], $id);
    if ($maintid == '')
    {
        trigger_error("!Error: No matching record while reading in incident_maintid() Incident ID: {$id}", E_USER_WARNING);
    }
    else
    {
        return ($maintid);
    }
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns string. Title of the incident
*/
function incident_title($id)
{
    return db_read_column('title', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns id. Current incident status ID
*/
function incident_status($id)
{
    return db_read_column('status', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns id. Current incident Priority ID
*/
function incident_priority($id)
{
    return db_read_column('priority', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns id. Current incident external ID
*/
function incident_externalid($id)
{
    return db_read_column('externalid', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns string. Current incident external engineer
*/
function incident_externalengineer($id)
{
    return db_read_column('externalengineer', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns string. Current incident external email address
*/
function incident_externalemail($id)
{
    return db_read_column('externalemail', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns string. Current incident CC email address
*/
function incident_ccemail($id)
{
    return db_read_column('ccemail', $GLOBALS['dbIncidents'], $id);
}


/**
    * @author Ivan Lucas
    * @param $id Incident ID
    * @returns int. UNIX Timestamp of the time of the next action for this incident
*/
function incident_timeofnextaction($id)
{
    return db_read_column('timeofnextaction', $GLOBALS['dbIncidents'], $id);
}


/**
    * Returns a string of HTML nicely formatted for the incident details page containing any additional
    * product info for the given incident.
    * @author Ivan Lucas
    * @param $incidentid The incident ID
    * @returns string HTML
*/
function incident_productinfo_html($incidentid)
{
    global $dbProductInfo, $dbIncidentProductInfo, $strNoProductInfo;

    // extract appropriate product info
    $sql  = "SELECT *, TRIM(incidentproductinfo.information) AS info FROM `{$dbProductInfo}` AS p, {$dbIncidentProductInfo}` ipi ";
    $sql .= "WHERE incidentid = $incidentid AND productinfoid = p.id AND TRIM(p.information) !='' ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 0)
    {
        return ('<tr><td>{$strNoProductInfo}</td><td>{$strNoProductInfo}</td></tr>');
    }
    else
    {
        // generate HTML
        while ($productinfo = mysql_fetch_array($result))
        {
            if (!empty($productinfo['info']))
            {
                $html = "<tr><th>{$productinfo['moreinformation']}:</th><td>";
                $html .= urlencode($productinfo['info']);
                $html .= "</td></tr>\n";
            }
        }
        echo $html;
    }
}


/**
    * Create an array containing the service level history
    * @author Ivan Lucas, Tom Gerrard
    * @returns array
*/
function incident_sla_history($incidentid)
{
    global $CONFIG, $dbIncidents, $dbServiceLevels, $dbUpdates;
    $working_day_mins = ($CONFIG['end_working_day'] - $CONFIG['start_working_day']) / 60;

    // Not the most efficient but..
    $sql = "SELECT * FROM `{$dbIncidents}` WHERE id='{$incidentid}'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    $incident = mysql_fetch_object($result);

    // Get service levels
    $sql = "SELECT * FROM `{$dbServiceLevels}` WHERE tag='{$incident->servicelevel}' AND priority='{$incident->priority}' ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    $level = mysql_fetch_object($result);

    // Loop through the updates in ascending order looking for service level events
    $sql = "SELECT * FROM `{$dbUpdates}` WHERE type='slamet' AND incidentid='{$incidentid}' ORDER BY id ASC, timestamp ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    $prevtime = 0;
    $idx = 0;
    while ($history = mysql_fetch_object($result))
    {
        $slahistory[$idx]['targetsla'] = $history->sla;
        switch ($history->sla)
        {
            case 'initialresponse':
                $slahistory[$idx]['targettime'] = $level->initial_response_mins;
                break;
            case 'probdef':
                $slahistory[$idx]['targettime'] = $level->prob_determ_mins;
                break;
            case 'actionplan':
                $slahistory[$idx]['targettime'] = $level->action_plan_mins;
                break;
            case 'solution':
                $slahistory[$idx]['targettime'] = ($level->resolution_days * $working_day_mins);
                break;
            default:
                $slahistory[$idx]['targettime'] = 0;
        }
        if ($prevtime > 0)
        {
            $slahistory[$idx]['actualtime'] = calculate_incident_working_time($incidentid, $prevtime, $history->timestamp);
        }
        else
        {
            $slahistory[$idx]['actualtime'] = 0;
        }

        $slahistory[$idx]['timestamp'] = $history->timestamp;
        $slahistory[$idx]['userid'] = $history->userid;
        if ($slahistory[$idx]['actualtime'] <= $slahistory[$idx]['targettime'])
        {
            $slahistory[$idx]['targetmet'] = TRUE;
        }
        else
        {
            $slahistory[$idx]['targetmet'] = FALSE;
        }

        $prevtime = $history->timestamp;
        $idx++;
    }
    // Get next target, but only if incident is still open
    if ($incident->status != 2 AND $incident->status != 7)
    {
        $target = incident_get_next_target($incidentid);
        $slahistory[$idx]['targetsla'] = $target->type;
        switch ($target->type)
        {
            case 'initialresponse':
                $slahistory[$idx]['targettime'] = $level->initial_response_mins;
                break;
            case 'probdef':
                $slahistory[$idx]['targettime'] = $level->prob_determ_mins;
                break;
            case 'actionplan':
                $slahistory[$idx]['targettime'] = $level->action_plan_mins;
                break;
            case 'solution':
                $slahistory[$idx]['targettime'] = ($level->resolution_days * $working_day_mins);
                break;
            default:
                $slahistory[$idx]['targettime'] = 0;
        }
        $slahistory[$idx]['actualtime'] = $target->since;
        if ($slahistory[$idx]['actualtime'] <= $slahistory[$idx]['targettime'])
        {
            $slahistory[$idx]['targetmet'] = TRUE;
        }
        else
        {
            $slahistory[$idx]['targetmet'] = FALSE;
        }

        $slahistory[$idx]['timestamp'] = 0;
    }
    return $slahistory;
}


/**
    * Takes an array and makes an HTML selection box
    * @author Ivan Lucas
*/
function array_drop_down($array, $name, $setting='', $enablefield='', $usekey = FALSE)
{
    $html = "<select name='$name' id='$name' $enablefield>\n";

    if ((array_key_exists($setting, $array) AND
        in_array((string)$setting, $array) == FALSE) OR
        $usekey == TRUE)
    {
        $usekey = TRUE;
    }
    else
    {
        $usekey = FALSE;
    }

    foreach ($array AS $key => $value)
    {
        $value = htmlentities($value, ENT_COMPAT, $GLOBALS['i18ncharset']);
        if ($usekey)
        {
            $html .= "<option value='$key'";
            if ($key == $setting)
            {
                $html .= " selected='selected'";
            }
            
        }
        else
        {
            $html .= "<option value='$value'";
            if ($value == $setting)
            {
                $html .= " selected='selected'";
            }
        }

        $html .= ">{$value}</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}


/**
    * prints the HTML for a drop down list of contacts, with the given name
    * and with the given id  selected.
    * @author Ivan Lucas
*/
function contact_drop_down($name, $id, $showsite = FALSE, $required = FALSE)
{
    global $dbContacts, $dbSites;
    if ($showsite)
    {
        $sql  = "SELECT c.id AS contactid, s.id AS siteid, surname, forenames, ";
        $sql .= "s.name AS sitename, s.department AS department ";
        $sql .= "FROM `{$dbContacts}` AS c, `{$dbSites}` AS s WHERE c.siteid = s.id AND c.active = 'true' ";
        $sql .= "AND s.active = 'true' ";
        $sql .= "ORDER BY s.name, s.department, surname ASC, forenames ASC";
    }
    else
    {
        $sql  = "SELECT c.id AS contactid, surname, forenames FROM `{$dbContacts}` AS c, `{$dbSites}` AS s ";
        $sql .= "WHERE c.siteid = s.id AND s.active = 'true' AND c.active = 'true' ";
        $sql .= "ORDER BY forenames ASC, surname ASC";
    }

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html = "<select name='$name' id='$name'";
    if ($required)
    {
        $html .= " class='required' ";
    }
    $html .= ">\n";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    $prevsite=0;
    while ($contacts = mysql_fetch_array($result))
    {
        if ($showsite AND $prevsite != $contacts['siteid'] AND $prevsite != 0)
        {
            $html .= "</optgroup>\n";
        }

        if ($showsite AND $prevsite != $contacts['siteid'])
        {
            $html .= "<optgroup label='".htmlentities($contacts['sitename'], ENT_COMPAT, 'UTF-8').", ".htmlentities($contacts['department'], ENT_COMPAT, $GLOBALS['i18ncharset'])."'>";
        }

        $realname=$contacts['forenames'].' '.$contacts['surname'];
        $html .= "<option ";
        if ($contacts['contactid'] == $id)
        {
            $html .= "selected='selected' ";
        }
        $html .= "value='{$contacts['contactid']}'>{$realname}";
        $html .= "</option>\n";

        $prevsite = $contacts['siteid'];
    }
    if ($showsite)
    {
        $html.= "</optgroup>";
    }

    $html .= "</select>\n";
    return $html;
}


/**
    * prints the HTML for a drop down list of contacts along with their site, with the given name and
    * and with the given id selected.
    * @author Ivan Lucas
    * @param $name string. The name of the field
    * @param $id int. Select this contactID by default
    * @param $siteid int. (optional) Filter list to show contacts from this siteID only
    * @param $exclude int|array (optional) Do not show this contactID in the list, accepts an int or array of ints
    * @param $showsite bool (optional) Suffix the name with the site name
    * @returns string.  HTML select
*/
function contact_site_drop_down($name, $id, $siteid='', $exclude='', $showsite=TRUE)
{
    global $dbContacts, $dbSites;
    $sql  = "SELECT c.id AS contactid, forenames, surname, siteid, s.name AS sitename ";
    $sql .= "FROM `{$dbContacts}` AS c, `{$dbSites}` AS s ";
    $sql .= "WHERE c.siteid = s.id AND c.active = 'true' AND s.active = 'true' ";
    if (!empty($siteid)) $sql .= "AND s.id='$siteid' ";
    if (!empty($exclude))
    {
        if (is_array($exclude))
        {
            foreach ($exclude AS $contactid)
            {
                $sql .= "AND c.id != $contactid ";
            }
        }
        else
        {
            $sql .= "AND c.id != $exclude ";
        }
    }
    $sql .= "ORDER BY surname ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $html = "<select name='$name'>";
    if (mysql_num_rows($result) > 0)
    {
        while ($contacts = mysql_fetch_object($result))
        {
            $html .= "<option ";
            if ($contacts->contactid == $id)
            {
                $html .= "selected='selected' ";
            }

            $html .= "value='{$contacts->contactid}'>";
            if ($showsite)
            {
                $html .= htmlspecialchars("{$contacts->surname}, {$contacts->forenames} - {$contacts->sitename}");
            }
            else
            {
                $html .= htmlspecialchars("{$contacts->surname}, {$contacts->forenames}");
            }
            $html .= "</option>\n";
        }
    }
    else $html .= "<option value=''>{$GLOBALS['strNone']}</option>";

    $html .= "</select>\n";
    return $html;
}


/**
    * HTML for a drop down list of products
    * @author Ivan Lucas
    * @param $name string. name/id to use for the select element
    * @param $id int. Product ID
    * @param $required bool.
    * @returns string. HTML select
    * @note With the given name and with the given id selected.
*/
function product_drop_down($name, $id, $required = FALSE)
{
    global $dbProducts;
    // extract products
    $sql  = "SELECT id, name FROM `{$dbProducts}` ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html = "<select name='{$name}' id='{$name}'";
    if ($required)
    {
        $html .= " class='required' ";
    }
    $html .= ">";


    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($products = mysql_fetch_array($result))
    {
        $html .= "<option value='{$products['id']}'";
        if ($products['id'] == $id)
        {
            $html .= " selected='selected'";
        }
        $html .= ">{$products['name']}</option>\n";
    }
    $html .= "</select>\n";
    return $html;

}


/**
    * HTML for a drop down list of skills (was called software)
    * @author Ivan Lucas
    * @param $name string. name/id to use for the select element
    * @param $id int. Software ID
    * @returns HTML select
*/
function software_drop_down($name, $id)
{
    global $now, $dbSoftware, $strEOL;

    // extract software
    $sql  = "SELECT id, name, lifetime_end FROM `{$dbSoftware}` ";
    $sql .= "ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html = "<select name='{$name}' id='{$name}' >";

    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($software = mysql_fetch_array($result))
    {
        $html .= "<option value='{$software['id']}'";
        if ($software['id'] == $id)
        {
            $html .= " selected='selected'";
        }

        $html .= ">{$software['name']}";
        $lifetime_start = mysql2date($software->lifetime_start);
        $lifetime_end = mysql2date($software->lifetime_end);
        if ($lifetime_end > 0 AND $lifetime_end < $now)
        {
            $html .= " ({$strEOL})";
        }
        $html .= "</option>\n";
    }
    $html .= "</select>\n";

    return $html;
}



/**
    *
    * @author Kieran Hogg
    * @param $name string. name/id to use for the select element
    * @returns HTML select
*/
function softwareproduct_drop_down($name, $id, $productid, $visibility='internal')
{
    global $dbSoftware, $dbSoftwareProducts;
    // extract software
    $sql  = "SELECT id, name FROM `{$dbSoftware}` AS s, ";
    $sql .= "`{$dbSoftwareProducts}` AS sp WHERE s.id = sp.softwareid ";
    $sql .= "AND productid = '$productid' ";
    $sql .= "ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) >=1)
    {
        $html = "<select name='$name'>";

        if ($visibility == 'internal' AND $id == 0)
        {
            $html .= "<option selected='selected' value='0'></option>\n";
        }
        elseif ($visiblity = 'external' AND $id == 0)
        {
            $html .= "<option selected='selected' value=''>{$GLOBALS['strUnknown']}</option>\n";
        }

        while ($software = mysql_fetch_array($result))
        {
            $html .= "<option";
            if ($software['id'] == $id)
            {
                $html .= " selected='selected'";
            }
            $html .= " value='{$software['id']}'>{$software['name']}</option>\n";
        }
        $html .= "</select>\n";
    }
    else
    {
        $html = "-";
    }

    return $html;
}


/**
    * A HTML Select listbox for vendors
    * @author Ivan Lucas
    * @param $name string. name/id to use for the select element
    * @param $id int. Vendor ID to preselect
    * @returns HTML select
*/
function vendor_drop_down($name, $id)
{
    global $dbVendors;
    $sql = "SELECT id, name FROM `{$dbVendors}` ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $html = "<select name='$name'>";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($row = mysql_fetch_array($result))
    {
        $html .= "<option";
        if ($row['id'] == $id)
        {
            $html .= " selected='selected'";
        }
        $html .= " value='{$row['id']}'>{$row['name']}</option>\n";
    }
    $html .= "</select>";

    return $html;
}


/**
    * A HTML Select listbox for Site Types
    * @author Ivan Lucas
    * @param $name string. name/id to use for the select element
    * @param $id int. Site Type ID to preselect
    * @todo TODO i18n needed
    * @returns HTML select
*/
function sitetype_drop_down($name, $id)
{
    global $dbSiteTypes;
    $sql = "SELECT typeid, typename FROM `{$dbSiteTypes}` ORDER BY typename ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $html .= "<select name='$name'>\n";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($row = mysql_fetch_array($result))
    {
        $html .= "<option ";
        if ($row['typeid'] == $id)
        {
            $html .="selected='selected' ";
        }

        $html .= "value='{$row['typeid']}'>{$row['typename']}</option>\n";
    }
    $html .= "</select>";
    return $html;
}


/**
    * Returns the HTML for a drop down list of upported products for the given contact and with the
    * given name and with the given product selected
    * @author Ivan Lucas
    * @todo FIXME this should use the contract and not the contact
*/
function supported_product_drop_down($name, $contactid, $productid)
{
    global $CONFIG, $dbSupportContacts, $dbMaintenance, $dbProducts, $strXIncidentsLeft;

    $sql = "SELECT *, p.id AS productid, p.name AS productname FROM `{$dbSupportContacts}` AS sc, `{$dbMaintenance}` AS m, `{$dbProducts}` AS p ";
    $sql .= "WHERE sc.maintenanceid = m.id AND m.product = p.id ";
    $sql .= "AND sc.contactid='$contactid'";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if ($CONFIG['debug']) $html .= "<!-- Original product {$productid}-->";
    $html .= "<select name=\"$name\">\n";
    if ($productid == 0)
    {
        $html .= "<option selected='selected' value='0'>No Contract - Not Product Related</option>\n";
    }

    if ($productid == -1)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($products = mysql_fetch_array($result))
    {
        $remainingstring = sprintf($strXIncidentsLeft, incidents_remaining($products["incidentpoolid"]));
        $html .= "<option ";
        if ($productid == $products['productid'])
        {
            $html .= "selected='selected' ";
        }
        $html .= "value='{$products['productid']}'>";
        $html .= servicelevel_name($products['servicelevelid'])." ".$products['productname'].", Exp:".date($CONFIG['dateformat_shortdate'], $products["expirydate"]).", $remainingstring";
        $html .= "</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}


/**
    * prints the HTML for a drop down list of  users, with the given name and with the given id selected.
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $id integer. User ID to pre-select
    * @param $accepting boolean. when true displays the accepting status. hides it when false
    * @param $exclude integer. User ID not to list
    * @param $attribs string. Extra attributes for the select control
*/
function user_drop_down($name, $id, $accepting = TRUE, $exclude = FALSE, $attribs= '', $return = FALSE)
{
    // INL 1Jul03 Now only shows users with status > 0 (ie current users)
    // INL 2Nov04 Optional accepting field, to hide the status 'Not Accepting'
    // INL 19Jan05 Option exclude field to exclude a user, or an array of
    // users
    global $dbUsers;
    $sql  = "SELECT id, realname, accepting FROM `{$dbUsers}` WHERE status > 0 ORDER BY realname ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html .= "<select name='{$name}' id='{$name}' ";
    if (!empty($attribs))
    {
        $html .= " $attribs";
    }

    $html .= ">\n";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($users = mysql_fetch_array($result))
    {
        $show = TRUE;
        if ($exclude != FALSE)
        {
            if (is_array($exclude))
            {
                if (!in_array($users['id'], $exclude)) $show = TRUE;
                else $show = FALSE;
            }
            else
            {
                if ($exclude!=$users['id']) $show = TRUE;
                else $show = FALSE;
            }
        }
        if ($show == TRUE)
        {
            $html .= "<option ";
            if ($users["id"] == $id) $html .= "selected='selected' ";
            if ($users['accepting'] == 'No' AND $accepting == TRUE)
            {
                $html .= " class='expired' ";
            }

            $html .= "value='{$users['id']}'>";
            $html .= "{$users['realname']}";
            if ($users['accepting'] == 'No' AND $accepting == TRUE)
            {
                $html .= ", {$GLOBALS['strNotAccepting']}";
            }
            $html .= "</option>\n";
        }
    }
    $html .= "</select>\n";

    if ($return)
    {
        return $html;
    }
    else
    {
        echo $html;
    }
}


/**
    * A HTML Select listbox for user roles
    * @author Ivan Lucas
    * @param $name string. name to use for the select element
    * @param $id int. Role ID to preselect
    * @returns HTML select
*/
function role_drop_down($name, $id)
{

    global $dbRoles;
    $sql  = "SELECT id, rolename FROM `{$dbRoles}` ORDER BY rolename ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html = "<select name='{$name}'>";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($role = mysql_fetch_object($result))
    {
        $html .= "<option value='{$role->id}'";
        if ($role->id==$id)
        {
            $html .= " selected='selected'";
        }

        $html .= ">{$role->rolename}</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}


/**
    * A HTML Select listbox for user groups
    * @author Ivan Lucas
    * @param $name string. name attribute to use for the select element
    * @param $selected int. Group ID to preselect
    * @returns HTML select
*/
function group_drop_down($name, $selected)
{
    global $grouparr, $numgroups;
    $html = "<select name='$name'>";
    $html .= "<option value='0'>{$GLOBALS['strNone']}</option>\n";
    if ($numgroups >= 1)
    {
        foreach ($grouparr AS $groupid => $groupname)
        {
            $html .= "<option value='$groupid'";
            if ($groupid == $selected)
            {
                $html .= " selected='selected'";
            }
            $html .= ">$groupname</option>\n";
        }
    }
    $html .= "</select>\n";
    return $html;
}


/**
    * A HTML Form and Select listbox for user groups, with javascript to reload page
    * @param $selected int. Group ID to preselect
    * @param $urlargs string. (Optional) text to pass after the '?' in the url (parameters)
    * @returns HTML select
*/
function group_selector($selected, $urlargs='')
{
    $gsql = "SELECT * FROM `{$GLOBALS['dbGroups']}` ORDER BY name";
    $gresult = mysql_query($gsql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    while ($group = mysql_fetch_object($gresult))
    {
        $grouparr[$group->id] = $group->name;
    }
    $numgroups = count($grouparr);

    if ($numgroups >= 1)
    {
        echo "<form action='{$_SERVER['PHP_SELF']}?{$urlargs}' class='filterform' method='get'>";
        echo "{$GLOBALS['strGroup']}: <select name='choosegroup' onchange='window.location.href=this.options[this.selectedIndex].value'>";
        echo "<option value='{$_SERVER['PHP_SELF']}?{$urlargs}&amp;gid=all'";
        if ($selected == 'all') echo " selected='selected'";
        echo ">{$GLOBALS['strAll']}</option>\n";
        echo "<option value='{$_SERVER['PHP_SELF']}?{$urlargs}&amp;gid=allonline'";
        if ($selected == 'allonline') echo " selected='selected'";
        echo ">{$GLOBALS['strAllOnline']}</option>\n";
        foreach ($grouparr AS $groupid => $groupname)
        {
            echo "<option value='{$_SERVER['PHP_SELF']}?{$urlargs}&amp;gid={$groupid}'";
            if ($groupid == $selected) echo " selected='selected'";
            echo ">{$groupname}</option>\n";
        }
        echo "<option value='{$_SERVER['PHP_SELF']}?{$urlargs}&amp;gid=0'";
        if ($selected == '0') echo " selected='selected'";
        echo ">{$GLOBALS['strUsersNoGroup']}</option>\n";
        echo "</select>\n";
        echo "</form>\n";
    }

    return $numgroups;
}


/**
    * Return HTML for a box to select interface style/theme
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $id integer. Interface style ID
    * @returns string.  HTML
*/
function interfacestyle_drop_down($name, $id)
{
    global $dbInterfaceStyles;
    // extract statuses
    $sql  = "SELECT id, name FROM `{$dbInterfaceStyles}` ORDER BY name ASC";
    $result = mysql_query($sql);
    $html = "<select name=\"{$name}\">";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($styles = mysql_fetch_array($result))
    {
        $html .= "<option ";
        if ($styles["id"] == $id)
        {
            $html .= "selected='selected'";
        }

        $html .= " value=\"{$styles["id"]}\">{$styles["name"]}</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}


/**
    * Retrieve cssurl and headerhtml for given interface style
    * @author Ivan Lucas
    * @param $id Integer. Interface style ID
    * @returns asoc array.
*/
function interface_style($id)
{
    global $CONFIG, $dbInterfaceStyles;

    $sql  = "SELECT cssurl, headerhtml FROM `{$dbInterfaceStyles}` WHERE id='$id'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 0)
    {
        mysql_free_result($result);
        $style = (array($CONFIG['default_css_url'],''));  // default style
    }
    else
    {
        $style = mysql_fetch_assoc($result);
        mysql_free_result($result);
    }

    if (empty($style))
    {
        $style = (array($CONFIG['default_css_url'],''));  // default style
    }

    return ($style);
}


/**
    * prints the HTML for a drop down list of incident status names (EXCLUDING 'CLOSED'),
    * with the given name and with the given id selected.
    * @author Ivan Lucas
    * @param $name string. Text to use for the HTML select name and id attributes
    * @param $id Integer. Status ID to preselect
    * @param $disabled Bool. Disable the select box when TRUE
    * @returns string. HTML.
*/

function incidentstatus_drop_down($name, $id, $disabled = FALSE)
{
    global $dbIncidentStatus;
    // extract statuses
    $sql  = "SELECT id, name FROM `{$dbIncidentStatus}` WHERE id<>2 AND id<>7 AND id<>10 ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    if (mysql_num_rows($result) < 1)
    {
        trigger_error("Zero rows returned",E_USER_WARNING);
    }

    $html = "<select id='{$name}' name='{$name}'";
    if ($disabled)
    {
        $html .= " disabled='disabled' ";
    }
    $html .= ">";
    // if ($id == 0) $html .= "<option selected='selected' value='0'></option>\n";
    while ($statuses = mysql_fetch_array($result))
    {
        $html .= "<option ";
        if ($statuses['id'] == $id)
        {
            $html .= "selected='selected' ";
        }

        $html .= "value='{$statuses['id']}'";
        $html .= ">{$GLOBALS[$statuses['name']]}</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}


/**
    * Return HTML for a select box of closing statuses
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $id integer. ID of Closing Status to pre-select. None selected if 0 or blank.
    * @todo Requires database i18n
    * @returns string. HTML
*/
function closingstatus_drop_down($name, $id, $required = FALSE)
{
    global $dbClosingStatus;
    // extract statuses
    $sql  = "SELECT id, name FROM `{$dbClosingStatus}` ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $html = "<select name='{$name}'";
    if ($required)
    {
        $html .= " class='required' ";
    }
    $html .= ">";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($statuses = mysql_fetch_array($result))
    {
        $html .= "<option ";
        if ($statuses["id"] == $id)
        {
            $html .= "selected='selected' ";
        }
        $html .= "value='{$statuses["id"]}'>{$GLOBALS[$statuses["name"]]}</option>\n";
    }
    $html .= "</select>\n";

    return $html;
}


/**
    * Return HTML for a select box of user statuses
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $id integer. ID of User Status to pre-select. None selected if 0 or blank.
    * @param $userdisable boolean. (optional). When TRUE an additional option is given to allow disabling of accounts
    * @returns string. HTML
*/
function userstatus_drop_down($name, $id, $userdisable = FALSE)
{
    global $dbUserStatus;
    // extract statuses
    $sql  = "SELECT id, name FROM `{$dbUserStatus}` ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html = "<select name='$name'>\n";
    if ($userdisable)
    {
        $html .= "<option class='disable' selected='selected' value='0'>ACCOUNT DISABLED</option>\n";
    }

    while ($statuses = mysql_fetch_array($result))
    {
        if ($statuses["id"] > 0)
        {
            $html .= "<option ";
            if ($statuses["id"] == $id)
            {
                $html .= "selected='selected' ";
            }
            $html .= "value='{$statuses["id"]}'>";
            $html .= "{$GLOBALS[$statuses["name"]]}</option>\n";
        }
    }
    $html .= "</select>\n";

    return $html;
}


/**
    * Return HTML for a select box of user statuses with javascript to effect changes immediately
    * Includes two extra options for setting Accepting yes/no
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $id integer. ID of User Status to pre-select. None selected if 0 or blank.
    * @returns string. HTML
*/
function userstatus_bardrop_down($name, $id)
{
    global $dbUserStatus;
    // extract statuses
    $sql  = "SELECT id, name FROM `{$dbUserStatus}` ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html = "<select name='$name' title='{$GLOBALS['strSetYourStatus']}' onchange=\"if ";
    $html .= "(this.options[this.selectedIndex].value != 'null') { ";
    $html .= "window.open(this.options[this.selectedIndex].value,'_top') }\">";
    $html .= "\n";
    while ($statuses = mysql_fetch_array($result))
    {
        if ($statuses["id"] > 0)
        {
            $html .= "<option ";
            if ($statuses["id"] == $id)
            {
                $html .= "selected='selected' ";
            }

            $html .= "value='set_user_status.php?mode=setstatus&amp;";
            $html .= "userstatus={$statuses['id']}'>";
            $html .= "{$GLOBALS[$statuses['name']]}</option>\n";
        }
    }
    $html .= "<option value='set_user_status.php?mode=setaccepting";
    $html .= "&amp;accepting=Yes' class='enable seperator'>";
    $html .= "{$GLOBALS['strAccepting']}</option>\n";
    $html .= "<option value='set_user_status.php?mode=setaccepting&amp;";
    $html .= "accepting=No' class='disable'>{$GLOBALS['strNotAccepting']}";
    $html .= "</option></select>\n";

    return $html;
}


/**
    * Return HTML for a select box of user email templates
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $id integer. ID of Template to pre-select. None selected if 0 or blank.
    * @param $type string. Type to display.
    * @returns string. HTML
*/
function emailtemplate_drop_down($name, $id, $type)
{
    global $dbEmailTemplates;
    // INL 22Apr05 Added a filter to only show user templates

    $sql  = "SELECT id, name, description FROM `{$dbEmailTemplates}` WHERE type='{$type}' ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $html = "<select name=\"{$name}\">";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($template = mysql_fetch_array($result))
    {
        $html .= "<option ";
        if (!empty($template['description']))
        {
            $html .= "title='{$template['description']}' ";
        }

        if ($template["id"] == $id)
        {
            $html .= "selected='selected' ";
        }
        $html .= "value='{$template['id']}'>{$template['name']}</option>";
        $html .= "\n";
    }
    $html .= "</select>\n";

    return $html;
}


/**
    * Return HTML for a select box of priority names (with icons)
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $id integer. ID of priority to pre-select. None selected if 0 or blank.
    * @param $max integer. The maximum priority ID to list.
    * @param $disable boolean. Disable the control when TRUE.
    * @returns string. HTML
*/
function priority_drop_down($name, $id, $max=4, $disable = FALSE)
{
    global $CONFIG, $iconset;
    // INL 8Oct02 - Removed DB Query
    $html = "<select id='priority' name='$name' ";
    if ($disable)
    {
        $html .= "disabled='disabled'";
    }

    $html .= ">";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    $html .= "<option style='text-indent: 14px; background-image: url({$CONFIG['application_webpath']}images/low_priority.gif); background-repeat:no-repeat;' value='1'";
    if ($id == 1)
    {
        $html .= " selected='selected'";
    }

    $html .= ">{$GLOBALS['strLow']}</option>\n";
    $html .= "<option style='text-indent: 14px; background-image: url({$CONFIG['application_webpath']}images/med_priority.gif); background-repeat:no-repeat;' value='2'";
    if ($id == 2)
    {
        $html .= " selected='selected'";
    }

    $html .= ">{$GLOBALS['strMedium']}</option>\n";
    $html .= "<option style='text-indent: 14px; background-image: url({$CONFIG['application_webpath']}images/high_priority.gif); background-repeat:no-repeat;' value='3'";
    if ($id==3)
    {
        $html .= " selected='selected'";
    }

    $html .= ">{$GLOBALS['strHigh']}</option>\n";
    if ($max >= 4)
    {
        $html .= "<option style='text-indent: 14px; background-image: url({$CONFIG['application_webpath']}images/crit_priority.gif); background-repeat:no-repeat;' value='4'";
        if ($id==4)
        {
            $html .= " selected='selected'";
        }
        $html .= ">{$GLOBALS['strCritical']}</option>\n";
    }
    $html .= "</select>\n";

    return $html;
}


/**
    * Return HTML for a select box for accepting yes/no. The given user's accepting status is displayed.
    * @author Ivan Lucas
    * @param $name string. Name attribute
    * @param $userid integer. The user ID to check
    * @returns string. HTML
*/
function accepting_drop_down($name, $userid)
{
    if (user_accepting($userid) == "Yes")
    {
        $html = "<select name=\"$name\">\n";
        $html .= "<option selected='selected' value=\"Yes\">{$GLOBALS['strYes']}</option>\n";
        $html .= "<option value=\"No\">{$GLOBALS['strNo']}</option>\n";
        $html .= "</select>\n";
    }
    else
    {
        $html = "<select name=\"$name\">\n";
        $html .= "<option value=\"Yes\">{$GLOBALS['strYes']}</option>\n";
        $html .= "<option selected='selected' value=\"No\">{$GLOBALS['strNo']}</option>\n";
        $html .= "</select>\n";
}
return $html;
}


/**
    * Return HTML for a select box for escalation path
    * @param $name string. Name attribute
    * @param $userid integer. The escalation path ID to pre-select
    * @returns string. HTML
*/
function escalation_path_drop_down($name, $id)
{
    global $dbEscalationPaths;
    $sql  = "SELECT id, name FROM `{$dbEscalationPaths}` ";
    $sql .= "ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $html = "<select name='{$name}' id='{$name}' >";
    $html .= "<option selected='selected' value='0'>{$GLOBALS['strNone']}</option>\n";
    while ($path = mysql_fetch_array($result))
    {
        $html .= "<option value='{$path['id']}'";
        if ($path['id']==$id)
        {
            $html .= " selected='selected'";
        }
        $html .= ">{$path['name']}</option>\n";
    }
    $html .= "</select>\n";

    return $html;
}


/* Returns a string representing the name of   */
/* the given priority. Returns an empty string if the         */
/* priority does not exist.                                   */
function priority_name($id)
{
    switch ($id)
    {
        case 1: $value = $GLOBALS['strLow']; break;
        case 2: $value = $GLOBALS['strMedium']; break;
        case 3: $value = $GLOBALS['strHigh']; break;
        case 4: $value = $GLOBALS['strCritical']; break;
        case '': $value = $GLOBALS['strNotSet']; break;
        default: $value = $GLOBALS['strUnknown']; break;
}
return $value;
}


// Returns HTML for an icon to indicate priority
function priority_icon($id)
{
    global $CONFIG;
    switch ($id)
    {
        case 1: $html = "<img src='{$CONFIG['application_webpath']}images/low_priority.gif' width='10' height='16' alt='{$strLowPriority}' title='Low Priority' />"; break;
        case 2: $html = "<img src='{$CONFIG['application_webpath']}images/med_priority.gif' width='10' height='16' alt='{$strMediumPriority}' title='Medium Priority' />"; break;
        case 3: $html = "<img src='{$CONFIG['application_webpath']}images/high_priority.gif' width='10' height='16' alt='{$strHighPriority}' title='High Priority' />"; break;
        case 4: $html = "<img src='{$CONFIG['application_webpath']}images/crit_priority.gif' width='16' height='16' alt='{$strCriticalPriority}' title='Critical Priority' />"; break;
        default: $html = '?'; break;
    }
    return $html;
}


/**
    * Returns an array of fields from the most recent update record for a given incident id
    * @author Ivan Lucas
    * @param $id An incident ID
    * @returns array
*/
function incident_lastupdate($id)
{
    global $dbUpdates;
    // Find the most recent update
    $sql = "SELECT userid, type, sla, currentowner, currentstatus, LEFT(bodytext,500) AS body, timestamp, nextaction, id ";
    $sql .= "FROM `{$dbUpdates}` WHERE incidentid='$id' ORDER BY timestamp DESC, id DESC LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 0)
    {
        trigger_error("Zero records while retrieving incident last update",E_USER_WARNING);
    }
    else
    {
        $update = mysql_fetch_array($result);

        // In certain circumstances go back even further, find an earlier update
        if (($update['type'] == "reassigning" AND !isset($update['body'])) OR ($update['type'] == 'slamet' AND $update['sla'] == 'opened'))
        {
            //check if the previous update was by userid == 0 if so then we can assume this is a new call
            $sqlPrevious = "SELECT userid, type, currentowner, currentstatus, LEFT(bodytext,500) AS body, timestamp, nextaction, id, sla, type ";
            $sqlPrevious .= "FROM `{$dbUpdates}` WHERE id < ".$update['id']." AND incidentid = '$id' ORDER BY id DESC";
            $resultPrevious = mysql_query($sqlPrevious);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

            if (mysql_num_rows($result) == 0)
            {
                trigger_error("Zero records while retrieving incident last update",E_USER_WARNING);
            }
            else
            {
                $row = mysql_fetch_array($resultPrevious);
                if ($row['userid'] == 0)
                {
                    $last = "";
                    //This was an initial assignment so we now want the first update - looping round data retrieved rather than second query
                    while ($row = mysql_fetch_array($resultPrevious))
                    {
                        $last = $row;
                        if ($row['userid'] != 0)
                        {
                            if ($row['type'] == 'slamet')
                            {
                                $last = mysql_fetch_array($resultPrevious);
                            }
                            break;
                        }
                    }
                    mysql_free_result($resultPrevious);

                    return array($last['userid'], $last['type'] ,$last['currentowner'], $last['currentstatus'], $last['body'], $last['timestamp'], $last['nextaction'], $last['id']);

                }
            }

        }
        mysql_free_result($result);
        // Remove Tags from update Body
        $update['body'] = trim($update['body']);
        $update['body'] = $update['body'];
        return array($update['userid'], $update['type'] ,$update['currentowner'], $update['currentstatus'], $update['body'], $update['timestamp'], $update['nextaction'], $update['id']);
    }
}


/**
    * Returns a string containing the body of the first update (that is visible to customer)
    * in a format suitable for including in an email
    * @author Ivan Lucas
    * @param $id An incident ID
*/
function incident_firstupdate($id)
{
    global $dbUpdates;
    $sql = "SELECT bodytext FROM `{$dbUpdates}` WHERE incidentid='$id' AND customervisibility='show' ORDER BY timestamp ASC, id ASC LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) >= 1)
    {
        list($bodytext) = mysql_fetch_row($result);
        $bodytext = strip_tags($bodytext);
    }
    else
    {
        $bodytext = '';
    }

    return $bodytext;
}


/**
    * Converts an incident status ID to an internationalised status string
    * @author Ivan Lucas
    * @param $id integer incident status ID
    * @param $type string. 'internal' or 'external', where external means customer/client facing
    * @returns string Internationalised incident status.
    *                 Or empty string if the ID is not recognised.
    * @note The incident status database table must contain i18n keys.
*/
function incidentstatus_name($id, $type='internal')
{
    global $dbIncidentStatus;

    if ($type == 'external')
    {
        $type = 'ext_name';
    }
    else
    {
        $type = 'name';
    }

    $sql = "SELECT {$type} FROM `{$dbIncidentStatus}` WHERE id='{$id}'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 0)
    {
        $name = '';
    }
    else
    {
        $incidentstatus = mysql_fetch_assoc($result);
        $name =  $GLOBALS[$incidentstatus[$type]];
    }
    return $name;
}


function closingstatus_name($id)
{
    global $dbClosingStatus;
    if ($id != '')
    {
        $closingstatus = db_read_column('name', $GLOBALS['dbClosingStatus'], $id);
    }
    else
    {
        $closingstatus = 'strUnknown';
    }

    return ($GLOBALS[$closingstatus]);
}



/* Returns a string representing the name of   */
/* the given user status. Returns an empty string if the      */
/* status does not exist.                                     */
function userstatus_name($id)
{
    $status = db_read_column('name', $GLOBALS['dbUserStatus'], $id);
    return $GLOBALS[$status];
}



/* Returns a string representing the name of   */
/* the given product. Returns an empty string if the product  */
/* does not exist.                                            */
function product_name($id)
{
    return db_read_column('name', $GLOBALS['dbProducts'], $id);
}



// Returns a string with all occurrences of emailtype special identifiers (in angle brackets) replaced
// with their appropriate values.
// DEPRECATED in favour of trigger_replace_specials() from 3.40
function emailtype_replace_specials($string, $incidentid=0, $userid=0)
{
    global $CONFIG, $application_version, $application_version_string, $dbIncidents;

    trigger_error('emailtype_replace_specials() is DEPRECATED', E_USER_WARNING);

    $contactid = incident_contact($incidentid);

    $url = parse_url($_SERVER['HTTP_REFERER']);
    $baseurl = "{$url['scheme']}://{$url['host']}{$CONFIG['application_webpath']}";

    // INL 13Jun03 Do one query to grab the incident details instead of doing a query
    // per replace-keyword - this should save a few queries

    $sql = "SELECT * FROM `{$dbIncidents}` WHERE id='$incidentid'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $incident = mysql_fetch_object($result);

    $email_regex = array(0 => '/<contactemail>/s',
                    1 => '/<contactname>/s',
                    2 => '/<contactfirstname>/s',
                    3 => '/<contactsite>/s',
                    4 => '/<contactphone>/s',
                    5 => '/<contactmanager>/s',
                    6 => '/<contactnotify>/s',
                    7 => '/<incidentid>/s',
                    8 => '/<incidentexternalid>/s',
                    9 => '/<incidentccemail>/s',
                    10 => '/<incidentexternalengineer>/s',
                    11 => '/<incidentexternalengineerfirstname>/s',
                    12 => '/<incidentexternalemail>/s',
                    13 => '/<incidenttitle>/s',
                    14 => '/<incidentpriority>/s',
                    15 => '/<incidentsoftware>/s',
                    16 => '/<incidentowner>/s',
                    17 => '/<useremail>/s',
                    18 => '/<userrealname>/s',
                    19 => "/<applicationname>/s",
                    20 => '/<applicationshortname>/s',
                    21 => '/<applicationversion>/s',
                    22 => '/<supportemail>/s',
                    23 => '/<salesemail>/s',
                    24 => '/<supportmanageremail>/s',
                    25 => '/<signature>/s',
                    26 => '/<globalsignature>/s',
                    27 => '/<todaysdate>/s',
                    28 => '/<salespersonemail>/s',
                    29 => '/<incidentfirstupdate>/s',
                    30 => '/<contactnotify2>/s',
                    31 => '/<contactnotify3>/s',
                    32 => '/<contactnotify4>/s',
                    33 => '/<feedbackurl>/s'
                );

    $email_replace = array(0 => contact_email($contactid),
                    1 => contact_realname($contactid),
                    2 => strtok(contact_realname($contactid),' '),
                    3 => contact_site($contactid),
                    4 => contact_phone($contactid),
                    5 => contact_notify_email($contactid),
                    6 => contact_notify_email($contactid),
                    7 => $incidentid,
                    8 => $incident->externalid,
                    9 => incident_ccemail($incidentid),
                    10 => incident_externalengineer($incidentid),
                    11 => strtok(incident_externalengineer($incidentid),' '),
                    12 => incident_externalemail($incidentid),
                    13 => incident_title($incidentid),
                    14 => priority_name(incident_priority($incidentid)),
                    15 => software_name($incident->softwareid),
                    16 => user_realname($incident->owner),
                    17 => user_email($userid),
                    18 => user_realname($userid),
                    19 => $CONFIG['application_name'],
                    20 => $CONFIG['application_shortname'],
                    21 => $application_version_string,
                    22 => $CONFIG['support_email'],
                    23 => $CONFIG['sales_email'],
                    24 => $CONFIG['support_manager_email'],
                    25 => user_signature($userid),
                    26 => global_signature(),
                    27 => date("jS F Y"),
                    28 => user_email(db_read_column('owner', $GLOBALS['dbSites'], db_read_column('siteid', $GLOBALS['dbContacts'], $contactid))),
                    29 => incident_firstupdate($incidentid),
                    30 => contact_email(contact_notify($contactid, 2)),
                    31 => contact_email(contact_notify($contactid, 3)),
                    32 => contact_email(contact_notify($contactid, 4)),
                    33 => $baseurl.'feedback.php?ax='.urlencode(trim(base64_encode(gzcompress(str_rot13(urlencode($CONFIG['feedback_form']).'&&'.urlencode($contactid).'&&'.urlencode($incidentid))))))
                );

    if ($incident->towner != 0)
    {
        //$return_string = str_replace("<incidentreassignemailaddress>", user_email($incident->towner), $return_string);
        $email_regex[] = '/<incidentreassignemailaddress>/s';
        $email_replace[] = user_email($incident->towner);
    }
    else
    {
        //$return_string = str_replace("<incidentreassignemailaddress>", user_email($incident->owner), $return_string);
        $email_regex[] = '/<incidentreassignemailaddress>/s';
        $email_replace[] = user_email($incident->owner);
    }
}


/**
    * Formats a given number of seconds into a readable string showing days, hours and minutes.
    * @author Ivan Lucas
    * @param $seconds integer number of seconds
    * @param $showseconds bool If TRUE and $seconds is less than 60 the function returns 1 minute.
    * @returns string Readable date/time
*/
function format_seconds($seconds, $showseconds = FALSE)
{
    global $str1Hour, $str1Minute, $str1Day, $str1Month, $strXSeconds, $str1Second;
    global $strXHours, $strXMinutes, $strXDays, $strXMonths, $strXYears;

    if ($seconds <= 0)
    {
        return sprintf($strXMinutes, 0);
    }
    elseif ($seconds <= 60 AND $seconds >= 1 AND $showseconds == FALSE)
    {
        return $str1Minute;
    }
    elseif ($seconds < 60 AND $seconds >= 1 AND $showseconds == TRUE)
    {
        if ($seconds == 1)
        {
            return $str1Second;
        }
        else
        {
            return sprintf($strXSeconds, $seconds);
        }
    }
    else
    {
        $years = floor($seconds / ( 2629800 * 12));
        $remainder = ($seconds % ( 2629800 * 12));
        $months = floor($remainder / 2629800);
        $remainder = ($seconds % 2629800);
        $days = floor($remainder / 86400);
        $remainder = ($remainder % 86400);
        $hours = floor($remainder / 3600);
        $remainder = ($remainder % 3600);
        $minutes = floor($remainder / 60);

        if ($years > 0)
        {
            $return_string .= sprintf($strXYears, $years).' ';
        }

        if ($months > 0 AND $years < 2)
        {
            if ($months == 1)
            {
                $return_string .= $str1Month." ";
            }
            else
            {
                $return_string .= sprintf($strXMonths, $months).' ';
            }
        }

        if ($days > 0 AND $months < 6)
        {
            if ($days == 1)
            {
                $return_string .= $str1Day." ";
            }
            else
            {
                $return_string .= sprintf($strXDays, $days)." ";
            }
        }

        if ($months < 1 AND $days < 7 AND $hours > 0)
        {
            if ($hours == 1)
            {
                $return_string .= $str1Hour." ";
            }
            else
            {
                $return_string .= sprintf($strXHours, $hours)." ";
            }
        }
        elseif ($months < 1 AND $days < 1 AND $hours > 0)
        {
            if ($minutes == 1)
            {
                $return_string .= $str1Minute." ";
            }
            elseif ($minutes > 1)
            {
                $return_string .= sprintf($strXMinutes, $minutes)." ";
            }
        }

        if ($months < 1 AND $days < 1 AND $hours < 1)
        {
            if ($minutes <= 1)
            {
                $return_string .= $str1Minute." ";
            }
            else
            {
                $return_string .= sprintf($strXMinutes, $minutes)." ";
            }
        }

        $return_string = trim($return_string);
        if (empty($return_string)) $return_string = "({$seconds})";
        return $return_string;
    }
}


/**
    * Return a string containing the time remaining as working days/hours/minutes (eg. 9am - 5pm)
    * @author Ivan Lucas
    * @returns string. Length of working time, in readable days, hours and minutes
    * @note The working day is calculated using the $CONFIG['end_working_day'] and
    * $CONFIG['start_working_day'] config variables
*/
function format_workday_minutes($minutes)
{
    global $CONFIG, $strXMinutes, $str1Minute, $strXHours, $strXHour;
    global $strXWorkingDay, $strXWorkingDays;
    $working_day_mins = ($CONFIG['end_working_day'] - $CONFIG['start_working_day']) / 60;
    $days = floor($minutes / $working_day_mins);
    $remainder = ($minutes % $working_day_mins);
    $hours = floor($remainder / 60);
    $minutes = floor($remainder % 60);

    if ($days == 1)
    {
        $time = sprintf($strXWorkingDay, $days);
    }
    elseif ($days > 1)
    {
        $time = sprintf($strXWorkingDays, $days);
    }

    if ($days <= 3 AND $hours == 1)
    {
        $time .= " ".sprintf($strXHour, $hours);
    }
    elseif ($days <= 3 AND $hours > 1)
    {
        $time .= " ".sprintf($strXHours, $hours);
    }
    elseif ($days > 3 AND $hours >= 1)
    {
        $time = "&gt; ".$time;
    }

    if ($days < 1 AND $hours < 8 AND $minutes == 1)
    {
        $time .= " ".$str1Minute;
    }
    elseif ($days < 1 AND $hours < 8 AND $minutes > 1)
    {
        $time .= " ".sprintf($strXMinutes, $minutes);
    }

    if ($days == 1 AND $hours < 8 AND $minutes == 1)
    {
        $time .= " ".$str1Minute;
    }
    elseif ($days == 1 AND $hours < 8 AND $minutes > 1)
    {
        $time .= " ".sprintf($strXMinutes, $minutes);
    }

    $time = trim($time);

    return $time;
}


/**
    * Make a readable and friendly date, i.e. say Today, or Yesterday if it is
    * @author Ivan Lucas
    * @param $date a UNIX timestamp
    * @returns string. Date in a readable friendly format
    * @note See also readable_date() dupe?
*/
function format_date_friendly($date)
{
    global $CONFIG, $now;
    if (ldate('dmy', $date) == ldate('dmy', time()))
    {
        $datestring = "{$GLOBALS['strToday']} @ ".ldate($CONFIG['dateformat_time'], $date);
    }
    elseif (ldate('dmy', $date) == ldate('dmy', (time() - 86400)))
    {
        $datestring = "{$GLOBALS['strYesterday']} @ ".ldate($CONFIG['dateformat_time'], $date);
    }
    elseif ($date < $now-86400 AND
            $date > $now-(86400*6))
    {
        $datestring = ldate('l', $date)." @ ".ldate($CONFIG['dateformat_time'], $date);
    }
    else
    {
        $datestring = ldate($CONFIG['dateformat_datetime'], $date);
    }

    return ($datestring);
}

/**
    * Generate HTML for a redirect/confirmation page
    * @author Ivan Lucas
    * @param $url string. URL to redirect to
    * @param $success boolean. TRUE = Success, FALSE = Failure
    * @param $message string. HTML message to display on the page before redirection
    * @note Replaces confirmation_page() from versions prior to 3.35
    * @note If a header HTML has already been displayed a continue link is printed
    * @note a meta redirect will also be inserted, which is invalid HTML but appears
    * @note to work in most browswers.
    * @note The recommended way to use this function is to call it without headers/footers
    * @note already displayed.
*/
function html_redirect($url, $success = TRUE, $message='')
{
    global $CONFIG, $headerdisplayed;

    if (!empty($_REQUEST['dashboard']))
    {
        $headerdisplayed = TRUE;
    }

    if (empty($message))
    {
        $refreshtime = 1;
    }
    elseif ($success == FALSE)
    {
        $refreshtime = 3;
    }
    else
    {
        $refreshtime = 6;
    }

    $refresh = "{$refreshtime}; url={$url}";

    $title = $GLOBALS['strPleaseWaitRedirect'];
    if (!$headerdisplayed)
    {
        include ('htmlheader.inc.php');
    }
    else
    {
        echo "<meta http-equiv=\"refresh\" content=\"$refreshtime; url=$url\" />\n";
    }

    echo "<h3>";
    if ($success)
    {
        echo "<span class='success'>{$GLOBALS['strSuccess']}</span>";
    }
    else
    {
        echo "<span class='failure'>{$GLOBALS['strFailed']}</span>";
    }

    if (!empty($message))
    {
        echo ": {$message}";
    }

    echo "</h3>";
    if (empty($_REQUEST['dashboard']))
    {
        echo "<h4>{$GLOBALS['strPleaseWaitRedirect']}</h4>";
        if ($headerdisplayed)
        {
            echo "<p align='center'><a href=\"{$url}\">{$GLOBALS['strContinue']}</a></p>";
        }
    }
    // TODO 3.35 Add a link to refresh the dashlet if this is run inside a dashlet

    if ($headerdisplayed)
    {
        include ('htmlfooter.inc.php');
    }
}


/*  calculates the value of the unix timestamp  */
/* which is the number of given days, hours and minutes from  */
/* the current time.                                          */
function calculate_time_of_next_action($days, $hours, $minutes)
{
    $now = time();
    $return_value = $now + ($days * 86400) + ($hours * 3600) + ($minutes * 60);
    return ($return_value);
}


// Returns the HTML for a drop down list of service levels,
// with the given name and with the given id selected.
function servicelevel_drop_down($name, $id, $collapse = FALSE)
{
    global $dbServiceLevels;

    if ($collapse)
    {
        $sql = "SELECT DISTINCT id, tag FROM `{$dbServiceLevels}`";
    }
    else
    {
        $sql  = "SELECT id, priority FROM `{$dbServiceLevels}`";
    }
    $result = mysql_query($sql);

    $html = "<select name='$name'>\n";
    // INL 30Mar06 Removed this ability to select a null service level
    // if ($id == 0) $html .= "<option selected='selected' value='0'></option>\n";
    while ($servicelevels = mysql_fetch_object($result))
    {
        $html .= "<option ";
        $html .= "value='{$servicelevels->id}' ";
        if ($servicelevels->id == $id)
        {
            $html .= "selected='selected'";
        }

        $html .= ">";
        if ($collapse)
        {
            $html .= $servicelevels->tag;
        }
        else
        {
            $html .= "{$servicelevels->tag} ".priority_name($servicelevels->priority);
        }

        $html .= "</option>\n";
    }
    $html .= "</select>";
    return $html;
}


function serviceleveltag_drop_down($name, $tag, $collapse = FALSE)
{
    global $dbServiceLevels;

    if ($collapse)
    {
        $sql = "SELECT DISTINCT tag FROM `{$dbServiceLevels}`";
    }
    else
    {
        $sql  = "SELECT tag, priority FROM `{$dbServiceLevels}`";
    }
    $result = mysql_query($sql);


    $html = "<select name='$name'>\n";
    if ($tag == '')
    {
        $html .= "<option selected='selected' value=''></option>\n";
    }

    while ($servicelevels = mysql_fetch_object($result))
    {
        $html .= "<option ";
        $html .= "value='{$servicelevels->tag}' ";
        if ($servicelevels->tag == $tag)
        {
            $html .= "selected='selected'";
        }

        $html .= ">";
        if ($collapse)
        {
            $html .= $servicelevels->tag;
        }
        else
        {
            $html .= "{$servicelevels->tag} ".priority_name($servicelevels->priority);
        }

        $html .= "</option>\n";
    }
    $html .= "</select>";
    return $html;
}


/* Returns a string representing the name of   */
/* the given servicelevel. Returns an empty string if the     */
/* priority does not exist.                                   */
function servicelevel_name($id)
{
    global $CONFIG;

    $servicelevel = db_read_column('tag', $GLOBALS['dbServiceLevels'], $id);

    if ($servicelevel == '')
    {
        $servicelevel = $CONFIG['default_service_level'];
    }
    return $servicelevel;
}


/**
    * Find whether a given servicelevel is timed
    * @author Ivan Lucas
    * @param $slid Integer. Service level tag
    * @returns. Bool. TRUE if any part of the service level is timed, otherwise returns FALSE
*/
function servicelevel_timed($sltag)
{
    global $dbServiceLevels;
    $timed = FALSE;

    $sql = "SELECT COUNT(tag) FROM `{$dbServiceLevels}` WHERE tag = '{$sltag}' AND timed = 'yes'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    list($count) = mysql_fetch_row($result);
    if ($count > 0) $timed = TRUE;

    return $timed;
}


/**
    * Retrieves the service level ID of a given maintenance contract
    * @author Ivan Lucas
    * @param $maintid Integer. Contract ID
    * @returns. Integer. Service Level ID
    * @note Service level ID's are DEPRECATED service level tags should be used in favour of service level ID's
*/
function maintenance_servicelevel($maintid)
{
    global $CONFIG, $dbMaintenance;
    $sql = "SELECT servicelevelid FROM `{$dbMaintenance}` WHERE id='{$maintid}' ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) < 1)
    {
        // in case there is no maintenance contract associated with the incident, use default service level
        // if there is a maintenance contract then we should throw an error because there should be
        // service level
        if ($maintid == 0)
        {
            // Convert the default service level tag to an ide and use that
            $servicelevelid = servicelevel_tag2id($CONFIG['default_service_level']);
        }
    }
    else
    {
        list($servicelevelid) = mysql_fetch_row($result);
    }
    return $servicelevelid;

}


function maintenance_siteid($id)
{
    return db_read_column('site', $GLOBALS['dbMaintenance'], $id);

}


/**
    * @author Ivan Lucas
    * @deprecated
    * @note DEPRECATED service level tags should be used in favour of service level ID's
    * @note Temporary solution, eventually we will move away from using servicelevel id's  and just use tags instead
*/
function servicelevel_id2tag($id)
{
    global $dbServiceLevels;
    return db_read_column('tag', $dbServiceLevels, $id);
}


/**
    * @author Ivan Lucas
    * @deprecated
    * @note DEPRECATED service level tags should be used in favour of service level ID's
    * @note Temporary solution, eventually we will move away from using servicelevel id's  and just use tags instead
*/
function servicelevel_tag2id($sltag)
{
    $sql = "SELECT id FROM `{$GLOBALS['dbServiceLevels']}` WHERE tag = '{$sltag}' AND priority=1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($id) = mysql_fetch_row($result);

    return $id;
}

// Returns the number of remaining incidents given an incident pool id
// Returns 'Unlimited' if theres no match on ID
function incidents_remaining($id)
{
    $remaining = db_read_column('incidentsremaining', $GLOBALS['dbIncidentPools'], $id);
    if (empty($remaining))
    {
        $remaining = '&infin;';
    }

    return $remaining;
}


function decrement_free_incidents($siteid)
{
    global $dbSites;
    $sql = "UPDATE `{$dbSites}` SET freesupport = (freesupport - 1) WHERE id='$siteid'";
    mysql_query($sql);
    if (mysql_affected_rows() < 1)
    {
        trigger_error("No rows affected while updating freesupport",E_USER_ERROR);
    }

    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    else return TRUE;
}


function increment_incidents_used($maintid)
{
    global $dbMaintenance;
    $sql = "UPDATE `{$dbMaintenance}` SET incidents_used = (incidents_used + 1) WHERE id='$maintid'";
    mysql_query($sql);
    if (mysql_affected_rows() < 1) trigger_error("No rows affected while updating freesupport",E_USER_ERROR);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    else return TRUE;
}


/**
    * Handle a PHP triggered error
    * @author Ivan Lucas
    * @note Not called directly but triggered by PHP's own error handling
    *       and the trigger_error function.
    * @note Parameters as per http://www.php.net/set_error_handler
    * @note This function is not internationalised in order that bugs can
    *       be reported to developers and still be sure that they will be
    *       understood
**/
function sit_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{
    global $CONFIG, $sit;
    $errortype = array(
    E_ERROR           => 'Fatal Error',
    E_WARNING         => 'Warning',
    E_PARSE           => 'Parse Error',
    E_NOTICE          => 'Notice',
    E_CORE_ERROR      => 'Core Error',
    E_CORE_WARNING    => 'Core Warning',
    E_COMPILE_ERROR   => 'Compile Error',
    E_COMPILE_WARNING => 'Compile Warning',
    E_USER_ERROR      => 'Application Error',
    E_USER_WARNING    => 'Application Warning',
    E_USER_NOTICE     => 'Application Notice');

    if (defined('E_STRICT')) $errortype[E_STRICT] = 'Strict Runtime notice';


    $trace_errors = array(E_ERROR, E_USER_ERROR);

    $user_errors = E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE;
    $system_errors = E_ERROR | E_WARNING | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
    $warnings = E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING;
    $notices = E_NOTICE | E_USER_NOTICE;
    if (($errno & $user_errors) OR ($errno & $system_errors))
    {
        if ($errno & $notices) $class = 'info';
        elseif ($errno & $warnings) $class = 'warning';
        else $class='error';
        echo "<p class='{$class}'><strong>{$errortype[$errno]} [{$errno}]</strong><br />";
        echo "{$errstr} in {$errfile} @ line {$errline}";
        if ($CONFIG['debug'])
        {
            $backtrace = debug_backtrace();
            echo "<br /><strong>Backtrace</strong>:";
            foreach ($backtrace AS $trace)
            {
                if (!empty($trace['file']))
                {
                    echo "<br />{$trace['file']} @ line {$trace['line']}";
                    if (!empty($trace['function']))
                    {
                        echo " {$trace['function']}() ";
//                         foreach ($trace['args'] AS $arg)
//                         {
//                             echo "$arg &bull; ";
//                         }
                    }
                }
            }
            if (!empty($CONFIG['error_logfile']) AND is_writable($CONFIG['error_logfile']))
            {
                $fp=fopen($CONFIG['error_logfile'], 'a+');
                if ($errno != E_NOTICE)
                {
                    fwrite($fp, date('r')." {$errortype[$errno]} [{$errno}] {$errstr} (in line {$errline} of file {$errfile})\n");
                }
                if ($errno==E_ERROR
                    || $errno==E_USER_ERROR
                    || $errno==E_CORE_ERROR
                    || $errno==E_CORE_WARNING
                    || $errno==E_COMPILE_ERROR
                    || $errno==E_COMPILE_WARNING) fwrite($fp, "Context:\n".print_r($errcontext, TRUE)."\n----------\n\n");
                fclose($fp);
            }
        }
        echo "</p>";
        // Tips, to help diagnose errors
        if (strpos($errstr, 'Unknown column') !== FALSE OR
            preg_match("/Table '(.*)' doesn't exist/", $errstr))
        {
            echo "<p class='tip'>The SiT schema may need updating to fix this problem.";
            if (user_permission($sit[2], 22)) echo "Visit <a href='setup.php'>Setup</a>"; // Only show this to admin
            echo "</p>";
        }

        if (strpos($errstr, 'You have an error in your SQL syntax') !== FALSE OR
            strpos($errstr, 'Query Error Incorrect table name') !== FALSE)
        {
            echo "<p class='tip'>You may have found a bug in SiT, please <a href=\"{$CONFIG['bugtracker_url']}\">report it</a>.</p>";
        }
    }
}


/**
    * @author Ivan Lucas
    * @deprecated Remove after 3.40 release
    * @note DEPRECATED and replaced by sit_error_handler() / trigger_error()
**/
function throw_error($message, $details)
{
    trigger_error("{$message}: {$details}", E_USER_WARNING);
}


/**
    * Displays user errors
    * @param $message array. An array of error strings
    * @returns Nothing. Outputs HTML list of user errors directly
*/
function throw_user_error($message, $details='')
{
    $html = "<div class='error'>";
    if (is_array($message)) echo "<p class='error'>{$GLOBALS['strError']}</p>";

    if (is_array($message))
    {
        $html .= "<ul>";
        // Loop through the array
        foreach ($message AS $msg)
        {
            $html .= "<li>{$msg}</li>";
        }
        $html .- "</ul>";
    }
    else
    {
        $html .= "<p class='error'>{$message}</p>";
    }

    $html .= "</div>\n";

    echo $html;
}


/*  prints the HTML for a drop down list of     */
/* sites, with the given name and with the given id selected. */
function site_drop_down($name, $id, $required = FALSE)
{
    global $dbSites;
    $sql  = "SELECT id, name, department FROM `{$dbSites}` ORDER BY name ASC";
    $result = mysql_query($sql);

    $html = "<select name='{$name}'";
    if ($required)
    {
        $html .= " class='required' ";
    }
    $html .= ">\n";
    if ($id == 0)
    {
        $html .="<option selected='selected' value='0'></option>\n";
    }

    while ($sites = mysql_fetch_object($result))
    {
        $text = $sites->name;
        if (!empty($sites->department))
        {
            $text.= ", ".$sites->department;
        }
//         if (strlen($text) >= 55) $text=htmlspecialchars(substr(trim($text), 0, 55))."&hellip;";
//         else $text=htmlspecialchars($text);
        if (strlen($text) >= 55)
        {
            $text = substr(trim($text), 0, 55)."&hellip;";
        }
        else
        {
            $text = $text;
        }

        $html .= "<option ";
        if ($sites->id == $id)
        {
            $html .= "selected='selected' ";
        }

        $html .= "value='{$sites->id}'>{$text}</option>\n";
    }
    $html .= "</select>\n";

    return $html;
}


function site_name($id)
{
    $sitename = db_read_column('name', $GLOBALS['dbSites'], $id);
    if (empty($sitename))
    {
        $sitename = $GLOBALS['strUnknown'];
    }

    return ($sitename);
}


/**
 * prints the HTML for a drop down list of maintenance contracts
 * @param $name name of the drop down box
 * @param $id 
 * @param $return Whether to return to HTML or echo
 * @param $showonlyactive True show only active (with a future expiry date), false shows all
 * 
 */
function maintenance_drop_down($name, $id, $excludes = '', $return = FALSE, $showonlyactive = FALSE)
{
    global $GLOBALS, $now;
    // TODO make maintenance_drop_down a hierarchical selection box sites/contracts
    // extract all maintenance contracts
    $sql  = "SELECT s.name AS sitename, p.name AS productname, m.id AS id ";
    $sql .= "FROM `{$GLOBALS['dbMaintenance']}` AS m, `{$GLOBALS['dbSites']}` AS s, `{$GLOBALS['dbProducts']}` AS p ";
    $sql .= "WHERE site = s.id AND product = p.id ";
    
    if ($showonlyactive)
    {
    	$sql .= "AND m.expirydate > {$now} ";
    }
    
    $sql .= "ORDER BY s.name ASC";
    $result = mysql_query($sql);
    $results = 0;
    // print HTML
    $html .= "<select name='{$name}'>";
    if ($id == 0)
    {
        $html .= "<option selected='selected' value='0'></option>\n";
    }

    while ($maintenance = mysql_fetch_array($result))
    {
        if (!is_array($excludes) OR (is_array($excludes) AND !in_array($maintenance['id'], $excludes)))
        {
            $html .= "<option ";
            if ($maintenance["id"] == $id)
            {
                $html .= "selected='selected' ";
            }
            $html .= "value='{$maintenance['id']}'>{$maintenance['sitename']} | {$maintenance['productname']}</option>";
            $html .= "\n";
            $results++;
        }
    }
    
    if ($results == 0)
    {
        $html .= "<option>{$GLOBALS['strNoRecords']}</option>";
    }
    $html .= "</select>";

    if ($return)
    {
        return $html;
    }
    else
    {
        echo $html;
    }
}


//  prints the HTML for a drop down list of resellers, with the given name and with the given id
// selected.                                                  */
function reseller_drop_down($name, $id)
{
    global $dbResellers;
    $sql  = "SELECT id, name FROM `{$dbResellers}` ORDER BY name ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    // print HTML

    echo "<select name='{$name}'>";

    if ($id == 0 OR empty($id))
    {
        echo "<option selected='selected' value='0'></option>\n";
    }
    else
    {
        echo "<option value='0'></option>\n";
    }

    while ($resellers = mysql_fetch_array($result))
    {
        echo "<option ";
        if ($resellers["id"] == $id)
        {
            echo "selected='selected' ";
        }

        echo "value='{$resellers['id']}'>{$resellers['name']}</option>";
        echo "\n";
    }

    echo "</select>";
}


//  prints the HTML for a drop down list of
// licence types, with the given name and with the given id
// selected.
function licence_type_drop_down($name, $id)
{
    global $dbLicenceTypes;
    $sql  = "SELECT id, name FROM `{$dbLicenceTypes}` ORDER BY name ASC";
    $result = mysql_query($sql);

    // print HTML
    echo "<select name='{$name}'>";

    if ($id == 0)
    {
        echo "<option selected='selected' value='0'></option>\n";
    }

    while ($licencetypes = mysql_fetch_array($result))
    {
        echo "<option ";
        if ($licencetypes["id"] == $id)
        {
            echo "selected='selected' ";
        }

        echo "value='{$licencetypes['id']}'>{$licencetypes['name']}</option>";
        echo "\n";
    }

    echo "</select>";
}


/**
    * @author Ivan Lucas
*/
function countdayincidents($day, $month, $year)
{
    // Counts the number of incidents opened on a specified day
    global $dbIncidents;
    $unixstartdate = mktime(0,0,0,$month,$day,$year);
    $unixenddate = mktime(23,59,59,$month,$day,$year);
    $sql = "SELECT count(id) FROM `{$dbIncidents}` ";
    $sql .= "WHERE opened BETWEEN '$unixstartdate' AND '$unixenddate' ";
    $result = mysql_query($sql);
    list($count) = mysql_fetch_row($result);
    mysql_free_result($result);
    return $count;
}


/**
    * @author Ivan Lucas
*/
function countdayclosedincidents($day, $month, $year)
{
    // Counts the number of incidents closed on a specified day
    global $dbIncidents;
    $unixstartdate = mktime(0,0,0,$month,$day,$year);
    $unixenddate = mktime(23,59,59,$month,$day,$year);
    $sql = "SELECT COUNT(id) FROM `{$dbIncidents}` ";
    $sql .= "WHERE closed BETWEEN '$unixstartdate' AND '$unixenddate' ";
    $result = mysql_query($sql);
    list($count) = mysql_fetch_row($result);
    mysql_free_result($result);
    return $count;
}


/**
    * @author Ivan Lucas
*/
function countdaycurrentincidents($day, $month, $year)
{
    global $dbIncidents;
    // Counts the number of incidents currently open on a specified day
    $unixstartdate = mktime(0,0,0,$month,$day,$year);
    $unixenddate = mktime(23,59,59,$month,$day,$year);
    $sql = "SELECT COUNT(id) FROM `{$dbIncidents}` ";
    $sql .= "WHERE opened <= '$unixenddate' AND closed >= '$unixstartdate' ";
    $result = mysql_query($sql);
    list($count) = mysql_fetch_row($result);
    mysql_free_result($result);
    return $count;
}


/**
    * Inserts an entry into the Journal table and marks the user online
    * @author Ivan Lucas, Kieran Hogg
    * @retval TRUE success, entry logged
    * @retval FALSE failure. entry not logged
    * @note Produces an audit log
*/
function journal($loglevel, $event, $bodytext, $journaltype, $refid)
{
    global $CONFIG, $sit, $dbJournal;
    // Journal Types
    // 1 = Logon/Logoff
    // 2 = Support Incidents
    // 3 = -Unused-
    // 4 = Sites
    // 5 = Contacts
    // 6 = Admin
    // 7 = User Management

    // Logging Level
    // 0 = No logging
    // 1 = Minimal Logging
    // 2 = Normal Logging
    // 3 = Full Logging
    // 4 = Max Debug Logging

    $bodytext = mysql_real_escape_string($bodytext);
    if ($loglevel <= $CONFIG['journal_loglevel'])
    {
        $sql  = "INSERT INTO `{$dbJournal}` ";
        $sql .= "(userid, event, bodytext, journaltype, refid) ";
        $sql .= "VALUES ('".$sit[2]."', '$event', '$bodytext', '$journaltype', '$refid') ";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        return TRUE;
    }
    else
    {
        // Below minimum log level - do nothing
        return FALSE;
    }
}


// prints the HTML for a checkbox, the 'state' value should be a 1, yes, true or 0, no, false */
function html_checkbox($name, $state, $return = FALSE)
{
    if ($state == 1 || $state == 'Yes' || $state == 'yes' || $state == 'true' || $state == 'TRUE')
    {
        $html = "<input type='checkbox' checked='checked' name='{$name}' id='{$name}' value='{$state}' />" ;
    }
    else
    {
        $html = "<input type='checkbox' name='{$name}' id='{$name}' value='{$state}' />" ;
    }

    if ($return)
    {
        return $html;
    }
    else
    {
        echo $html;
    }
}


/**
    * Sends an email, replacing certain special keys with values based on the email
    * template chosen
    * @deprecated
    * @author Ivan Lucas
*/
function send_template_email($template, $incidentid, $info1='', $info2='')
{
    trigger_error("send_template_email() is deprecated in 3.40+", "Use trigger() instead", E_USER_WARNING);
    global $CONFIG, $application_version_string, $sit, $now;
    global $dbUpdates, $dbEmailTemplates;
    if (empty($template)) trigger_error('Blank template ID:', 'send_template_email()', E_USER_WARNING);
    if (empty($incidentid)) trigger_error('Blank incident ID:', 'send_template_email()', E_USER_WARNING);

    if (is_numeric($template))
    {
        $templateid = $template;
    }
    else
    {
        // Lookup the template id using the name
        $sql = "SELECT id FROM `{$dbEmailTemplates}` WHERE name='$template' LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        list($templateid) = mysql_fetch_row($result);
    }

    // Set up headers
    $email_to      = trim(emailtype_replace_specials(emailtype_to($templateid), $incidentid, $sit[2]));
    $email_from    = trim(emailtype_replace_specials(emailtype_from($templateid), $incidentid, $sit[2]));
    $email_replyto = trim(emailtype_replace_specials(emailtype_replyto($templateid), $incidentid, $sit[2]));
    $email_cc      = trim(emailtype_replace_specials(emailtype_cc($templateid), $incidentid, $sit[2]));
    $email_bcc     = trim(emailtype_replace_specials(emailtype_bcc($templateid), $incidentid, $sit[2]));
    $email_subject = trim(emailtype_replace_specials(emailtype_subject($templateid), $incidentid, $sit[2]));
    $email_body    = trim(emailtype_replace_specials(emailtype_body($templateid), $incidentid, $sit[2]));
    $email_customervisibility = trim(emailtype_customervisibility($templateid));
    $email_storeinlog = trim(emailtype_storeinlog($templateid));

    // Additional information
    if (empty($info1) == FALSE || empty($info2) == FALSE)
    {
        $email_body = str_replace("<info1>", "$info1", $email_body);
        $email_subject = str_replace("<info1>", "$info1", $email_subject);
        $email_body = str_replace("<info2>", "$info2", $email_body);
        $email_subject = str_replace("<info2>", "$info2", $email_subject);
    }

    ##echo "Sending email to $email_to with subject '".stripslashes($email_subject)."'";

    // build the extra headers string for email
    $extra_headers  = "From: $email_from\r\nReply-To: $email_replyto\r\nErrors-To: {$CONFIG['support_email']}\r\n";
    $extra_headers .= "X-Mailer: {$CONFIG['application_shortname']} {$application_version_string}/PHP " . phpversion()."\r\n";
    $extra_headers .= "X-Originating-IP: {$_SERVER['REMOTE_ADDR']}\r\n";
    if ($email_cc != '')
    {
        $extra_headers .= "CC: $email_cc\r\n";
    }
    if ($email_bcc != '')
    {
        $extra_headers .= "BCC: $email_bcc\r\n";
    }

    $extra_headers .= "\r\n";

    if ($email_storeinlog == 'Yes')
    {
        $bt   = "To: <b>$email_to</b>\nFrom: <b>$email_from</b>\nReply-To: <b>$emailreplyto</b>\n";
        $bt  .= "BCC: <b>$email_bcc</b>\nSubject: <b>$email_subject</b>\n<hr>".$email_body;
        $sql = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp, customervisibility) VALUES ";
        $sql .= "($incidentid, 0, 'email', '".mysql_real_escape_string($bt)."', ";
        $sql .= "$now, '$email_customervisibility')";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    }

    // send email
    if ($CONFIG['demo'])
    {
        $rtnvalue = TRUE;
    }
    else
    {
        $rtnvalue = mail($email_to, $email_subject, $email_body, $extra_headers);
    }

    return $rtnvalue;
}

/**
    Send an email from SiT
    * @param $to string. Destination email address
    * @param $from string. Source email address
    * @param $subject string. Email subject line
    * @param $body string. Email body text
    * @param $replyto string. (optional) Address to send reply to
    * @param $cc string. (optional) Carbon copy address
    * @param $bcc string. (optional) Blind carbon copy address
    * @returns The return value from PHP mail() function or TRUE when in Demo mode
    * @note Returns TRUE but does not actually send mail when SiT is in Demo mode
*/
function send_email($to, $from, $subject, $body, $replyto='', $cc='', $bcc='')
{
    global $CONFIG;

    if (empty($to)) trigger_error('Empty TO address in email', E_USER_WARNING);

    $extra_headers  = "From: {$from}\n";
    if (!empty($replyto)) $extra_headers .= "Reply-To: {$replyto}\n";
    if (!empty($email_cc))
    {
        $extra_headers .= "CC: {$cc}\n";
    }
    if (!empty($email_bcc))
    {
        $extra_headers .= "BCC: {$bcc}\n";
    }
    if (!empty($CONFIG['support_email'])) $extra_headers .= "Errors-To: {$CONFIG['support_email']}\n";
    $extra_headers .= "X-Mailer: {$CONFIG['application_shortname']} {$application_version_string}/PHP " . phpversion()."\n";
    $extra_headers .= "X-Originating-IP: {$_SERVER['REMOTE_ADDR']}";
    $extra_headers .= "\r\n";

    if ($CONFIG['demo'])
    {
        $rtnvalue = TRUE;
    }
    else
    {
        $rtnvalue = mail($to, $subject, $body, $extra_headers);
    }
    return $rtnvalue;
}


/**
    * Generates and returns a random alphanumeric password
    * @author Ivan Lucas
    * @note Some characters (0 and 1) are not used to avoid user confusion
*/
function generate_password($length=8)
{
    $possible = '0123456789'.'abcdefghijkmnpqrstuvwxyz'.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.'-';
    // $possible = '23456789'.'abcdefghjkmnpqrstuvwxyz'.'ABCDEFGHJKLMNPQRSTUVWXYZ'.'-';
                // not using 1's 0's etc. to save confusion
                // '-=!&';
    $str = '';
    while (strlen($str) < $length)
    {
        $str .= substr($possible, (rand() % strlen($possible)),1);
    }
    return $str;
}


if (!function_exists('list_dir'))
{
    // returns an array contains all files in a directory and optionally recurses subdirectories
    function list_dir($dirname, $recursive = 1)
    {
        // try to figure out what delimeter is being used (for windows or unix)...
        $delim = (strstr($dirname,"/")) ? "/" : "\\";

        if ($dirname[strlen($dirname)-1] != $delim)
        $dirname .= $delim;

        $handle = opendir($dirname);
        if ($handle == FALSE) trigger_error('Error in list_dir() Problem attempting to open directory: {$dirname}',E_USER_WARNING);

        while ($file = readdir($handle))
        {
            if ($file == '.' || $file == '..')
            {
                continue;
            }

            if (is_dir($dirname.$file) && $recursive)
            {
                $x = list_dir($dirname.$file.$delim);
                $result_array = array_merge($result_array, $x);
            }
            else
            {
                $result_array[] = $dirname.$file;
            }
        }
        closedir($handle);

        if (sizeof($result_array))
        {
            natsort($result_array);

            if ($_SESSION['update_order'] == "desc")
            {
                $result_array = array_reverse($result_array);
            }
        }
        return $result_array;
    }
}


if (!function_exists('is_number'))
{
    function is_number($string)
    {
        $number = TRUE;
        for ($i=0; $i < strlen($string); $i++)
        {
            if (!(ord(substr($string,$i,1)) <= 57 && ord(substr($string,$i,1)) >= 48))
            {
                $number = FALSE;
            }
        }
        return $number;
    }
}


// recursive copy from one directory to another
function rec_copy ($from_path, $to_path)
{
    if ($from_path == '') trigger_error('Cannot move file', 'from_path not set', E_USER_WARNING);
    if ($to_path == '') trigger_error('Cannot move file', 'to_path not set', E_USER_WARNING);

    $mk = mkdir($to_path, 0700);
    if (!$mk) trigger_error('Failed creating directory: {$to_path}',E_USER_WARNING);
    $this_path = getcwd();
    if (is_dir($from_path))
    {
        chdir($from_path);
        $handle = opendir('.');
        while (($file = readdir($handle)) !== false)
        {
            if (($file != ".") && ($file != ".."))
            {
                if (is_dir($file))
                {
                    rec_copy ($from_path.$file."/",
                    $to_path.$file."/");
                    chdir($from_path);
                }
                if (is_file($file))
                {
                    if (!(substr(rtrim($file),strlen(rtrim($file))-8,4) == 'mail'
                        || substr(rtrim($file),strlen(rtrim($file))-10,5) == 'part1'
                        || substr(rtrim($file),strlen(rtrim($file))-8,4) == '.vcf'))
                    {
                        copy($from_path.$file, $to_path.$file);
                    }
                }
            }
        }
        closedir($handle);
    }
}


/**
    * @author Ivan Lucas
*/
function getattachmenticon($filename)
{
    global $CONFIG, $iconset;
    // Maybe sometime make this use mime typesad of file extensions
    $ext = strtolower(substr($filename, (strlen($filename)-3) , 3));
    $imageurl = "{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/unknown.png";

    $type_image = "{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/file_image.png";

    $filetype[]="gif";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/image.png";
    $filetype[]="jpg";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/image.png";
    $filetype[]="bmp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/image.png";
    $filetype[]="png";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/image.png";
    $filetype[]="pcx";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/image.png";
    $filetype[]="xls";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/spreadsheet.png";
    $filetype[]="csv";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/spreadsheet.png";
    $filetype[]="zip";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/tgz.png";
    $filetype[]="arj";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/zip.png";
    $filetype[]="rar";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/rar.png";
    $filetype[]="cab";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/tgz.png";
    $filetype[]="lzh";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/tgz.png";
    $filetype[]="txt";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/txt.png";
    $filetype[]="f90";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source_f.png";
    $filetype[]="f77";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source_f.png";
    $filetype[]="inf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source.png";
    $filetype[]="ins";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source.png";
    $filetype[]="adm";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source.png";
    $filetype[]="f95";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source_f.png";
    $filetype[]="cpp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source_cpp.png";
    $filetype[]="for";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source_f.png";
    $filetype[]=".pl";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source_pl.png";
    $filetype[]=".py";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source_py.png";
    $filetype[]="rtm";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/misc_doc.png";
    $filetype[]="doc";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/wordprocessing.png";
    $filetype[]="rtf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/wordprocessing.png";
    $filetype[]="wri";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/wordprocessing.png";
    $filetype[]="wri";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/wordprocessing.png";
    $filetype[]="pdf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/pdf.png";
    $filetype[]="htm";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/html.png";
    $filetype[]="tml";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/html.png";
    $filetype[]="wav";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/sound.png";
    $filetype[]="mp3";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/sound.png";
    $filetype[]="voc";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/sound.png";
    $filetype[]="exe";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="com";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="nlm";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="evt";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/log.png";
    $filetype[]="log";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/log.png";
    $filetype[]="386";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="dll";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="asc";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/txt.png";
    $filetype[]="asp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/html.png";
    $filetype[]="avi";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/video.png";
    $filetype[]="bkf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/tar.png";
    $filetype[]="chm";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/man.png";
    $filetype[]="hlp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/man.png";
    $filetype[]="dif";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/txt.png";
    $filetype[]="hta";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/html.png";
    $filetype[]="reg";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/resource.png";
    $filetype[]="dmp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/core.png";
    $filetype[]="ini";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source.png";
    $filetype[]="jpe";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/image.png";
    $filetype[]="mht";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/html.png";
    $filetype[]="msi";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="aot";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="pgp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="dbg";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="axt";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/source.png"; // zen text
    $filetype[]="rdp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="sig";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/document.png";
    $filetype[]="tif";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/image.png";
    $filetype[]="ttf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/font_ttf.png";
    $filetype[]="for";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/font_bitmap.png";
    $filetype[]="vbs";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/shellscript.png";
    $filetype[]="vbe";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/shellscript.png";
    $filetype[]="bat";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/shellscript.png";
    $filetype[]="wsf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/shellscript.png";
    $filetype[]="cmd";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/shellscript.png";
    $filetype[]="scr";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="xml";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/xml.png";
    $filetype[]="zap";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]=".ps";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/postscript.png";
    $filetype[]=".rm";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/real_doc.png";
    $filetype[]="ram";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/real_doc.png";
    $filetype[]="vcf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/vcard.png";
    $filetype[]="wmf";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/vectorgfx.png";
    $filetype[]="cer";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/document.png";
    $filetype[]="tmp";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/unknown.png";
    $filetype[]="cap";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]="tr1";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/binary.png";
    $filetype[]=".gz";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/tgz.png";
    $filetype[]="tar";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/tar.png";
    $filetype[]="nfo";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/info.png";
    $filetype[]="pal";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/colorscm.png";
    $filetype[]="iso";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/cdimage.png";
    $filetype[]="jar";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/java_src.png";
    $filetype[]="eml";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/message.png";
    $filetype[]=".sh";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/shellscript.png";
    $filetype[]="bz2";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/tgz.png";
    $filetype[]="out";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/log.png";
    $filetype[]="cfg";    $imgurl[]="{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/mimetypes/log.png";

    $cnt = count($filetype);
    if ( $cnt > 0 )
    {
        $a = 0;
        $stop = FALSE;
        while ($a < $cnt && $stop == FALSE)
        {
            if ($ext==$filetype[$a])
            {
                $imageurl = $imgurl[$a];
                $stop = TRUE;
            }
            $a++;
        }
    }
    unset ($filetype);
    unset ($imgurl);
    return $imageurl;
}


function count_incoming_updates()
{
    global $dbUpdates;
    $sql = "SELECT id FROM `{$dbUpdates}` WHERE incidentid=0";
    $result = mysql_query($sql);
    $count = mysql_num_rows($result);
    mysql_free_result($result);
    return $count;
}


function global_signature()
{
    global $dbEmailSig;
    $sql = "SELECT signature FROM `{$dbEmailSig}` ORDER BY RAND() LIMIT 1";
    $result = mysql_query($sql);
    list($signature) = mysql_fetch_row($result);
    mysql_free_result($result);
    return $signature;
}


// checks the spelling of a word and returns true if spelled correctly and
// false if misspelled.  Uses the pspell library using link provided.
function spellcheck_word($pspell_link, $word)
{
    return pspell_check($pspell_link,$word);
}


function spellcheck_addword($word)
{
    global $CONFIG;
    $pspell_config = pspell_config_create ("en");
    pspell_config_personal ($pspell_config, $CONFIG['main_dictionary_file']);
    pspell_config_repl ($pspell_config, $CONFIG['main_dictionary_file']);
    $pspell_link = pspell_new_personal ($CONFIG['custom_dictionary_file'], 'en' , 'british', '', 'iso8859-1', PSPELL_FAST);

    pspell_add_to_personal ($pspell_link, $word);
    pspell_save_wordlist ($pspell_link);
}


// urltext should take the form '&var=value'
// FIXME i18n
function spellcheck_text($text, $urltext)
{
    global $CONFIG;
    $pspell_config = pspell_config_create ("en");
    pspell_config_personal ($pspell_config, $CONFIG['main_dictionary_file']);
    pspell_config_repl ($pspell_config, $CONFIG['main_dictionary_file']);
    $pspell_link = pspell_new_personal ($CONFIG['custom_dictionary_file'], 'en' , 'british', '', 'iso8859-1', PSPELL_FAST);

    if (!$pspell_link) trigger_error('Dictionary Link Error', E_USER_WARNING);

    // try and stop html getting through in the source text (INL 2July03)
    $text = str_replace('<','&#060;', $text);
    $text = str_replace('>','&#062;', $text);

    for ($c=0; $c <= strlen($text); $c++)
    {
        $char = strtolower(substr($text,$c,1));
        if (!(ord($char) >= 97 && ord($char) <= 122))
        {
            if ($endwordpos==-1 && $startwordpos==-1)
            {
                $newtext .= $char;
            }

            if ($startwordpos==-1)
            {
                $startwordpos=$c+1;
            }
            else
            {
                $endwordpos=$c;
            }
        }
        if ($c == 0 && (ord($char) >= 97 && ord($char) <= 122))
        {
            $startwordpos=0;
        }

        if ($endwordpos!=-1 && $startwordpos!=-1)
        {
            $word = substr($text, $startwordpos, ($endwordpos-$startwordpos));
            if (!spellcheck_word($pspell_link, $word))
            {
                $suggestions = pspell_suggest($pspell_link, $word);
                if (count($suggestions) > 1)
                {
                    $tooltiptext = "Possible spellings:<br /><br />"; // FIXME i18n
                    $tooltiptext .= "<table summary='suggestions'>";
                    $col = 0;
                    foreach ($suggestions as $suggestion)
                    {
                        if ($col > 3)
                        {
                            $tooltiptext .= "</tr>\n<tr>"; $col=0;
                        }

                        $tooltiptext .= "<td valign='top' align='left'><a href='{$_SERVER['PHP_SELF']}?changepos=$c&amp;replacement=$suggestion$urltext&amp;step=3'>$suggestion</a></td>";
                        $col++;
                    }
                    $tooltiptext .= "</tr>\n</table>\n";
                }
                else
                {
                    $tooltiptext = "Sorry, there are no reasonable suggested spellings for '$word' in the dictionary<br />";
                }
                $tooltiptext .= "<br /><a href='{$_SERVER['PHP_SELF']}?addword=$word$urltext&amp;step=3' onclick='return confirm_addword();'>Add</a> '$word' to the dictionary.";
                echo "<script type=\"text/javascript\">var linkHelp$c = \"$tooltiptext\";</script>\n";

                $newtext .= "<a class=\"spellLink\" href=\"?\" onclick=\"showHelpTip(event, linkHelp$c); return false\">$word</a>";
            }
            else
            {
                $newtext .= "$word";
            }

            $c--;
            $startwordpos=-1;
            $endwordpos=-1;
        }
    }
    return $newtext;
}


// replace word in text
function replace_word($text, $changepos, $replacement)
{
    // changepos is the position of the end of the word needing to be changed

    // read backwards until the end of the word and store the word end position
    $limit = $changepos-30;
    $c = $changepos-1;
    do
    {
        $char = strtolower(substr($text,$c,1));
        $startwordpos = $c;
        $c--;
    } while ((ord($char) >= 97 && ord($char) <= 122) && $c > 1 );

    $newtext = substr($text, 0, $startwordpos+1 );
    $newtext .= $replacement;
    $newtext .= substr($text, $changepos);

    return $newtext;
}


function holiday_type ($id)
{
    switch ($id)
    {
        case 1: $holidaytype = $GLOBALS['strHoliday']; break;
        case 2: $holidaytype = $GLOBALS['strAbsentSick']; break;
        case 3: $holidaytype = $GLOBALS['strWorkingAway']; break;
        case 4: $holidaytype = $GLOBALS['strTraining']; break;
        case 5: $holidaytype = $GLOBALS['strCompassionateLeave']; break;
        case 10: $holidaytype = $GLOBALS['strPublicHoliday']; break;
        default: $holidaytype = $GLOBALS['strUnknown']; break;
    }
    return ($holidaytype);
}

function holiday_approval_status($approvedid, $approvedby=-1)
{
    global $strApproved, $strApprovedFree, $strRequested, $strNotRequested;
    global $strArchivedDenied, $strArchivedNotRequested, $strArchivedRequested;
    global $strArchivedApproved, $strArchivedApprovedFree, $strApprovalStatusUnknown;

    // We add 10 to normal status when we archive holiday
    switch ($approvedid)
    {
        case -2: $status = $strNotRequested; break;
        case -1: $status = $strDenied; break;
        case 0:
            if ($approvedby == 0) $status = $strNotRequested;
            else $status = $strRequested;
        break;
        case 1: $status = $strApproved; break;
        case 2: $status = $strApprovedFree; break;
        case 8: $status = $strArchivedNotRequested; break;
        case 9: $status = $strArchivedDenied; break;
        case 10: $status = $strArchivedRequested; break;
        case 11: $status = $strArchivedApproved; break;
        case 12: $status = $strArchivedApprovedFree; break;
        default: $status = $strApprovalStatusUnknown; break;
    }
    return $status;
}


function holidaytype_drop_down($name, $id)
{
    $holidaytype[1] = $GLOBALS['strHoliday'];
    $holidaytype[2] = $GLOBALS['strAbsentSick'];
    $holidaytype[3] = $GLOBALS['strWorkingAway'];
    $holidaytype[4] = $GLOBALS['strTraining'];
    $holidaytype[5] = $GLOBALS['strCompassionateLeave'];

    $html = "<select name='$name'>";
    if ($id == 0)
    {
        $html .= "<option selected value='0'></option>\n";
    }

    foreach ($holidaytype AS $htypeid => $htype)
    {
        $html .= "<option";
        if ($htypeid == $id)
        {
            $html .= " selected='selected'";
        }
        $html .= " value='{$htypeid}'>{$htype}</option>\n";
    }
    $html .= "</select>\n";
    return $html;
}


/**
  * @author Paul Heaney
  * @param $userid - userid to find group for
  * @return A int of the groupid
*/
function user_group_id($userid)
{
    global $dbUsers;
    // get groupid
    $sql = "SELECT groupid FROM `{$dbUsers}` WHERE id='{$userid}' ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($groupid) = mysql_fetch_row($result);
    return $groupid;
}


/**
  * check to see if any fellow group members have holiday on the date specified
  * @author Ivan Lucas
  * @param $userid int - user ID
  * @param $date int - UNIX Timestamp
  * @param $length string - 'day', 'pm' or 'am'
  * @return HTML space seperated list of users that are away on the date specified
*/
function check_group_holiday($userid, $date, $length='day')
{
    global $dbUsers, $dbHolidays;

    $namelist = '';
    $groupid = user_group_id($userid);
    if (!empty($groupid))
    {
        // list group members
        $msql = "SELECT id AS userid FROM `{$dbUsers}` ";
        $msql .= "WHERE groupid='{$groupid}' AND id != '$userid' ";
        $mresult = mysql_query($msql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        while ($member = mysql_fetch_object($mresult))
        {
            // check to see if this group member has holiday
            $hsql = "SELECT id FROM `{$dbHolidays}` WHERE userid='{$member->userid}' AND startdate='{$date}' ";
            if ($length == 'am' || $length == 'pm')
            {
                $hsql .= "AND (length = '$length' OR length = 'day') ";
            }
            $hresult = mysql_query($hsql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            if (mysql_num_rows($hresult) >= 1)
            {
                $namelist .= user_realname($member->userid)." ($length)";
                $namelist .= "&nbsp;&nbsp;";
            }
        }
    }
    return $namelist;
}


/**
  * Print a listbox of countries
  * @author Ivan Lucas
  * @param $name string - HTML select 'name' attribute
  * @param $country string - Country to pre-select (default to config file setting)
  * @param $extraattributes string - Extra attributes to put on the select tag
  * @return HTML
  * @note if the $country given is not in the list, an editable input box is given instead of a select box
  * @todo TODO i18n country list (How do we do this?)
*/
function country_drop_down($name, $country, $extraattributes='')
{
    global $CONFIG;
    if ($country == '') $country = $CONFIG['home_country'];

    if ($country == 'UK') $country = 'UNITED KINGDOM';
    $countrylist[] = 'ALBANIA';
    $countrylist[] = 'ALGERIA';
    $countrylist[] = 'AMERICAN SAMOA';
    $countrylist[] = 'ANDORRA';
    $countrylist[] = 'ANGOLA';
    $countrylist[] = 'ANGUILLA';
    $countrylist[] = 'ANTIGUA';
    $countrylist[] = 'ARGENTINA';
    $countrylist[] = 'ARMENIA';
    $countrylist[] = 'ARUBA';
    $countrylist[] = 'AUSTRALIA';
    $countrylist[] = 'AUSTRIA';
    $countrylist[] = 'AZERBAIJAN';
    $countrylist[] = 'BAHAMAS';
    $countrylist[] = 'BAHRAIN';
    $countrylist[] = 'BANGLADESH';
    $countrylist[] = 'BARBADOS';
    $countrylist[] = 'BELARUS';
    $countrylist[] = 'BELGIUM';
    $countrylist[] = 'BELIZE';
    $countrylist[] = 'BENIN';
    $countrylist[] = 'BERMUDA';
    $countrylist[] = 'BHUTAN';
    $countrylist[] = 'BOLIVIA';
    $countrylist[] = 'BONAIRE';
    $countrylist[] = 'BOSNIA HERZEGOVINA';
    $countrylist[] = 'BOTSWANA';
    $countrylist[] = 'BRAZIL';
    $countrylist[] = 'BRUNEI';
    $countrylist[] = 'BULGARIA';
    $countrylist[] = 'BURKINA FASO';
    $countrylist[] = 'BURUNDI';
    $countrylist[] = 'CAMBODIA';
    $countrylist[] = 'CAMEROON';
    $countrylist[] = 'CANADA';
    $countrylist[] = 'CANARY ISLANDS';
    $countrylist[] = 'CAPE VERDE ISLANDS';
    $countrylist[] = 'CAYMAN ISLANDS';
    $countrylist[] = 'CENTRAL AFRICAN REPUBLIC';
    $countrylist[] = 'CHAD';
    $countrylist[] = 'CHANNEL ISLANDS';
    $countrylist[] = 'CHILE';
    $countrylist[] = 'CHINA';
    $countrylist[] = 'COLOMBIA';
    $countrylist[] = 'COMOROS ISLANDS';
    $countrylist[] = 'CONGO';
    $countrylist[] = 'COOK ISLANDS';
    $countrylist[] = 'COSTA RICA';
    $countrylist[] = 'CROATIA';
    $countrylist[] = 'CUBA';
    $countrylist[] = 'CURACAO';
    $countrylist[] = 'CYPRUS';
    $countrylist[] = 'CZECH REPUBLIC';
    $countrylist[] = 'DENMARK';
    $countrylist[] = 'DJIBOUTI';
    $countrylist[] = 'DOMINICA';
    $countrylist[] = 'DOMINICAN REPUBLIC';
    $countrylist[] = 'ECUADOR';
    $countrylist[] = 'EGYPT';
    $countrylist[] = 'EL SALVADOR';
    $countrylist[] = 'EQUATORIAL GUINEA';
    $countrylist[] = 'ERITREA';
    $countrylist[] = 'ESTONIA';
    $countrylist[] = 'ETHIOPIA';
    $countrylist[] = 'FAROE ISLANDS';
    $countrylist[] = 'FIJI ISLANDS';
    $countrylist[] = 'FINLAND';
    $countrylist[] = 'FRANCE';
    $countrylist[] = 'FRENCH GUINEA';
    $countrylist[] = 'GABON';
    $countrylist[] = 'GAMBIA';
    $countrylist[] = 'GEORGIA';
    $countrylist[] = 'GERMANY';
    $countrylist[] = 'GHANA';
    $countrylist[] = 'GIBRALTAR';
    $countrylist[] = 'GREECE';
    $countrylist[] = 'GREENLAND';
    $countrylist[] = 'GRENADA';
    $countrylist[] = 'GUADELOUPE';
    $countrylist[] = 'GUAM';
    $countrylist[] = 'GUATEMALA';
    $countrylist[] = 'GUINEA REPUBLIC';
    $countrylist[] = 'GUINEA-BISSAU';
    $countrylist[] = 'GUYANA';
    $countrylist[] = 'HAITI';
    $countrylist[] = 'HONDURAS REPUBLIC';
    $countrylist[] = 'HONG KONG';
    $countrylist[] = 'HUNGARY';
    $countrylist[] = 'ICELAND';
    $countrylist[] = 'INDIA';
    $countrylist[] = 'INDONESIA';
    $countrylist[] = 'IRAN';
    $countrylist[] = 'IRELAND, REPUBLIC';
    $countrylist[] = 'ISRAEL';
    $countrylist[] = 'ITALY';
    $countrylist[] = 'IVORY COAST';
    $countrylist[] = 'JAMAICA';
    $countrylist[] = 'JAPAN';
    $countrylist[] = 'JORDAN';
    $countrylist[] = 'KAZAKHSTAN';
    $countrylist[] = 'KENYA';
    $countrylist[] = 'KIRIBATI, REP OF';
    $countrylist[] = 'KOREA, SOUTH';
    $countrylist[] = 'KUWAIT';
    $countrylist[] = 'KYRGYZSTAN';
    $countrylist[] = 'LAOS';
    $countrylist[] = 'LATVIA';
    $countrylist[] = 'LEBANON';
    $countrylist[] = 'LESOTHO';
    $countrylist[] = 'LIBERIA';
    $countrylist[] = 'LIBYA';
    $countrylist[] = 'LIECHTENSTEIN';
    $countrylist[] = 'LITHUANIA';
    $countrylist[] = 'LUXEMBOURG';
    $countrylist[] = 'MACAU';
    $countrylist[] = 'MACEDONIA';
    $countrylist[] = 'MADAGASCAR';
    $countrylist[] = 'MALAWI';
    $countrylist[] = 'MALAYSIA';
    $countrylist[] = 'MALDIVES';
    $countrylist[] = 'MALI';
    $countrylist[] = 'MALTA';
    $countrylist[] = 'MARSHALL ISLANDS';
    $countrylist[] = 'MARTINIQUE';
    $countrylist[] = 'MAURITANIA';
    $countrylist[] = 'MAURITIUS';
    $countrylist[] = 'MEXICO';
    $countrylist[] = 'MOLDOVA, REP OF';
    $countrylist[] = 'MONACO';
    $countrylist[] = 'MONGOLIA';
    $countrylist[] = 'MONTSERRAT';
    $countrylist[] = 'MOROCCO';
    $countrylist[] = 'MOZAMBIQUE';
    $countrylist[] = 'MYANMAR';
    $countrylist[] = 'NAMIBIA';
    $countrylist[] = 'NAURU, REP OF';
    $countrylist[] = 'NEPAL';
    $countrylist[] = 'NETHERLANDS';
    $countrylist[] = 'NEVIS';
    $countrylist[] = 'NEW CALEDONIA';
    $countrylist[] = 'NEW ZEALAND';
    $countrylist[] = 'NICARAGUA';
    $countrylist[] = 'NIGER';
    $countrylist[] = 'NIGERIA';
    $countrylist[] = 'NIUE';
    $countrylist[] = 'NORWAY';
    $countrylist[] = 'OMAN';
    $countrylist[] = 'PAKISTAN';
    $countrylist[] = 'PANAMA';
    $countrylist[] = 'PAPUA NEW GUINEA';
    $countrylist[] = 'PARAGUAY';
    $countrylist[] = 'PERU';
    $countrylist[] = 'PHILLIPINES';
    $countrylist[] = 'POLAND';
    $countrylist[] = 'PORTUGAL';
    $countrylist[] = 'PUERTO RICO';
    $countrylist[] = 'QATAR';
    $countrylist[] = 'REUNION ISLAND';
    $countrylist[] = 'ROMANIA';
    $countrylist[] = 'RUSSIAN FEDERATION';
    $countrylist[] = 'RWANDA';
    $countrylist[] = 'SAIPAN';
    $countrylist[] = 'SAO TOME & PRINCIPE';
    $countrylist[] = 'SAUDI ARABIA';
    $countrylist[] = 'SENEGAL';
    $countrylist[] = 'SEYCHELLES';
    $countrylist[] = 'SIERRA LEONE';
    $countrylist[] = 'SINGAPORE';
    $countrylist[] = 'SLOVAKIA';
    $countrylist[] = 'SLOVENIA';
    $countrylist[] = 'SOLOMON ISLANDS';
    $countrylist[] = 'SOUTH AFRICA';
    $countrylist[] = 'SPAIN';
    $countrylist[] = 'SRI LANKA';
    $countrylist[] = 'ST BARTHELEMY';
    $countrylist[] = 'ST EUSTATIUS';
    $countrylist[] = 'ST KITTS';
    $countrylist[] = 'ST LUCIA';
    $countrylist[] = 'ST MAARTEN';
    $countrylist[] = 'ST VINCENT';
    $countrylist[] = 'SUDAN';
    $countrylist[] = 'SURINAME';
    $countrylist[] = 'SWAZILAND';
    $countrylist[] = 'SWEDEN';
    $countrylist[] = 'SWITZERLAND';
    $countrylist[] = 'SYRIA';
    $countrylist[] = 'TAHITI';
    $countrylist[] = 'TAIWAN';
    $countrylist[] = 'TAJIKISTAN';
    $countrylist[] = 'TANZANIA';
    $countrylist[] = 'THAILAND';
    $countrylist[] = 'TOGO';
    $countrylist[] = 'TONGA';
    $countrylist[] = 'TRINIDAD & TOBAGO';
    $countrylist[] = 'TURKEY';
    $countrylist[] = 'TURKMENISTAN';
    $countrylist[] = 'TURKS & CAICOS ISLANDS';
    $countrylist[] = 'TUVALU';
    $countrylist[] = 'UGANDA';
    // $countrylist[] = 'UK';
    $countrylist[] = 'UKRAINE';
    $countrylist[] = 'UNITED KINGDOM';
    $countrylist[] = 'UNITED STATES';
    $countrylist[] = 'URUGUAY';
    $countrylist[] = 'UTD ARAB EMIRATES';
    $countrylist[] = 'UZBEKISTAN';
    $countrylist[] = 'VANUATU';
    $countrylist[] = 'VENEZUELA';
    $countrylist[] = 'VIETNAM';
    $countrylist[] = 'VIRGIN ISLANDS';
    $countrylist[] = 'VIRGIN ISLANDS (UK)';
    $countrylist[] = 'WESTERN SAMOA';
    $countrylist[] = 'YEMAN, REP OF';
    $countrylist[] = 'YUGOSLAVIA';
    $countrylist[] = 'ZAIRE';
    $countrylist[] = 'ZAMBIA';
    $countrylist[] = 'ZIMBABWE';

    if (in_array(strtoupper($country), $countrylist))
    {
        // make drop down
        $html = "<select name=\"$name\" $extraattributes>";
        foreach ($countrylist as $key => $value)
        {
            $value = htmlspecialchars($value);
            $html .= "<option value='$value'";
            if ($value == strtoupper($country))
            {
                $html .= " selected='selected'";
            }
            $html .= ">$value</option>\n";
        }
        $html .= "</select>";
    }
    else
    {
        // make editable input box
        $html = "<input maxlength='100' name='{$name}' size='40' value='{$country}' {$extraattributes} />";
    }
    return $html;
}


function check_email($email, $check_dns = FALSE)
{
    if ((preg_match('/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/', $email)) ||
    (preg_match('/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/',$email)))
    {
        if ($check_dns)
        {
            $host = explode('@', $email);
            // Check for MX record
            if ( checkdnsrr($host[1], 'MX') ) return TRUE;
            // Check for A record
            if ( checkdnsrr($host[1], 'A') ) return TRUE;
            // Check for CNAME record
            if ( checkdnsrr($host[1], 'CNAME') ) return TRUE;
        }
        else
        {
            return TRUE;
        }
    }
    return FALSE;
}


function incident_get_next_target($incidentid)
{
    global $now, $dbUpdates;
    // Find the most recent SLA target that was met
    $sql = "SELECT sla,timestamp FROM `{$dbUpdates}` WHERE incidentid='$incidentid' AND type='slamet' ORDER BY id DESC LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) > 0)
    {
        $upd = mysql_fetch_object($result);

        switch ($upd->sla)
        {
            case 'opened': $target->type = 'initialresponse'; break;
            case 'initialresponse': $target->type = 'probdef'; break;
            case 'probdef': $target->type = 'actionplan'; break;
            case 'actionplan': $target->type = 'solution'; break;
            // case 'solution': $target->type='closed'; break;
            case 'solution': $target->type = 'probdef'; break;
            case 'closed': $target->type = 'opened'; break;
        }

        $target->since = calculate_incident_working_time($incidentid,$upd->timestamp,$now);
    }
    else
    {
        $target->type = 'regularcontact';
        $target->since = 0;
    }
    return $target;
}


function target_type_name($targettype)
{
    switch ($targettype)
    {
        case 'opened': $name = $GLOBALS['strOpened']; break;
        case 'initialresponse': $name = $GLOBALS['strInitialResponse']; break;
        case 'probdef': $name = $GLOBALS['strProblemDefinition']; break;
        case 'actionplan': $name = $GLOBALS['strActionPlan']; break;
        case 'solution': $name = $GLOBALS['strResolutionReprioritisation']; break;
        case 'closed': $name=''; break;
        case 'regularcontact': $name=''; break; // Contact Customer
        default: $name=''; break;
    }
    return $name;
}


function incident_get_next_review($incidentid)
{
    global $now, $dbUpdates;
    $sql = "SELECT timestamp FROM `{$dbUpdates}` WHERE incidentid='$incidentid' AND type='reviewmet' ORDER BY id DESC LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) > 0)
    {
        $upd = mysql_fetch_object($result);
        $timesincereview = floor(($now - ($upd->timestamp)) / 60);
    }
    return $timesincereview;
}


function strip_anchor_tags ($string)
{
    return preg_replace('/<a class=\"spellLink\" href=\"?\" onclick=\"showHelpTip.event, linkHelp(.*).; return false\">/', '', $string);
}



/**
    * Converts a MySQL date to a UNIX Timestamp
    * @author Ivan Lucas
    * @param $mysqldate string. A date column from mysql
    * @returns integer. a UNIX Timestamp
*/
function mysql2date($mysqldate)
{
    // for the zero/blank case, return 0
    if (empty($mysqldate))
    {
        return 0;
    }

    if ($mysqldate == '0000-00-00 00:00:00' OR $mysqldate == '0000-00-00')
    {
        return 0;
    }

    // Takes a MYSQL date and converts it to a proper PHP date
    $day = substr($mysqldate,8,2);
    $month = substr($mysqldate,5,2);
    $year = substr($mysqldate,0,4);

    if (strlen($mysqldate) > 10)
    {
        $hour = substr($mysqldate,11,2);
        $minute = substr($mysqldate,14,2);
        $second = substr($mysqldate,17,2);
        $phpdate = mktime($hour,$minute,$second,$month,$day,$year);
    }
    else
    {
        $phpdate = mktime(0, 0, 0, $month, $day, $year);
    }

    return $phpdate;
}


/**
    * Converts a MySQL timestamp to a UNIX Timestamp
    * @author Ivan Lucas
    * @param $mysqldate string. A timestamp column from mysql
    * @returns integer. a UNIX Timestamp
*/
function mysqlts2date($mysqldate)
{
    // for the zero/blank case, return 0
    if (empty($mysqldate)) return 0;

    // Takes a MYSQL date and converts it to a proper PHP date
    if (strlen($mysqldate) == 14)
    {
        $day = substr($mysqldate,6,2);
        $month = substr($mysqldate,4,2);
        $year = substr($mysqldate,0,4);
        $hour = substr($mysqldate,8,2);
        $minute = substr($mysqldate,10,2);
        $second = substr($mysqldate,12,2);
    }
    elseif (strlen($mysqldate) > 14)
    {
        $day = substr($mysqldate,8,2);
        $month = substr($mysqldate,5,2);
        $year = substr($mysqldate,0,4);
        $hour = substr($mysqldate,11,2);
        $minute = substr($mysqldate,14,2);
        $second = substr($mysqldate,17,2);
    }
    $phpdate = mktime($hour,$minute,$second,$month,$day,$year);
    return $phpdate;
}


function iso_8601_date($timestamp)
{
    $date_mod = date('Y-m-d\TH:i:s', $timestamp);
    $pre_timezone = date('O', $timestamp);
    $time_zone = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
    $date_mod .= $time_zone;
    return $date_mod;
}

/**
    * Decide whether the time is during a public holiday
    * @author Paul Heaney
    * @param $time integer. Timestamp to identify
    * @param $publicholidays array of Holiday. Public holiday to compare against
    * @returns integer. If > 0 number of seconds left in the public holiday
*/
function is_public_holiday($time, $publicholidays)
{
    if (!empty($publicholidays))
    {
        foreach ($publicholidays AS $holiday)
        {
            if ($time >= $holiday->starttime AND $time <= $holiday->endtime)
            {
                return $holiday->endtime-$time;
            }
        }
    }

    return 0;
}

/**
    * Calculate the working time between two timestamps
    * @author Tom Gerrard, Ivan Lucas, Paul Heaney
    * @param $t1 integer. The start timestamp (earliest date/time)
    * @param $t2 integer. The ending timetamp (latest date/time)
    * @returns integer. the number of working minutes (minutes in the working day)
*/
function calculate_working_time($t1, $t2, $publicholidays)
{
    // PH 16/12/07 Old function commented out, rewritten to support public holidays. Old code to be removed once we're happy this is stable
    // KH 13/07/08 Use old function again for 3.35 beta
    // Note that this won't work if we have something
    // more complicated than a weekend

    global $CONFIG;
    $swd = $CONFIG['start_working_day'] / 3600;
    $ewd = $CONFIG['end_working_day'] / 3600;

    // Just in case the time params are the wrong way around ...
    if ( $t1 > $t2 )
    {
        $t3 = $t2;
        $t2 = $t1;
        $t1 = $t3;
    }

    // We don't need all the elements here.  hours, days and year are used
    // later on to calculate the difference.  wday is just used in this
    // section
    $at1 = getdate($t1);
    $at2 = getdate($t2);

    // Make sure that the start time is on a valid day and within normal hours
    // if it isn't then move it forward to the next work minute
    if ($at1['hours'] > $ewd)
    {
        do
        {
            $at1['yday'] ++;
            $at1['wday'] ++;
            $at1['wday'] %= 7;
            if ($at1['yday'] > 365)
            {
                $at1['year'] ++;
                $at1['yday'] = 0;
            }
        } while (!in_array($at1['wday'],$CONFIG['working_days']));

        $at1['hours']=$swd;
        $at1['minutes']=0;

    }
    else
    {
        if (($at1['hours']<$swd) || (!in_array($at1['wday'],$CONFIG['working_days'])))
        {
            while (!in_array($at1['wday'], $CONFIG['working_days']))
            {
                $at1['yday'] ++;
                $at1['wday'] ++;
                $at1['wday'] %= 7;
                if ($at1['days']>365)
                {
                    $at1['year'] ++;
                    $at1['yday'] = 0;
                }
            }
            $at1['hours'] = $swd;
            $at1['minutes'] = 0;
        }
    }

    // Same again but for the end time
    // if it isn't then move it backward to the previous work minute
    if ( $at2['hours']<$swd)
    {
        do
        {
            $at2['yday'] --;
            $at2['wday'] --;
            if ($at2['wday'] < 0) $at2['wday'] = 6;
            if ($at2['yday'] < 0)
            {
                $at2['yday'] = 365;
                $at2['year'] --;
            }
        } while (!in_array($at2['wday'], $CONFIG['working_days']));

        $at2['hours'] = $ewd;
        $at2['minutes'] = 0;
    }
    else
    {
        if (($at2['hours']>$ewd) || (!in_array($at2['wday'],$CONFIG['working_days'])))
        {
            while (!in_array($at2['wday'],$CONFIG['working_days']))
            {
                $at2['yday'] --;
                $at2['wday'] --;
                if ($at2['wday'] < 0) $at2['wday'] = 6;
                if ($at2['yday'] < 0)
                {
                    $at2['yday'] = 365;
                    $at2['year'] --;
                }
            }
            $at2['hours'] = $ewd;
            $at2['minutes'] = 0;
        }
    }

    $t1 = mktime($at1['hours'], $at1['minutes'], 0, 1, $at1['yday'] + 1, $at1['year']);
    $t2 = mktime($at2['hours'], $at2['minutes'], 0, 1, $at2['yday'] + 1, $at2['year']);

    $weeks = floor(($t2 - $t1) / (60 * 60 * 24 * 7));
    $t1 += $weeks * 60 * 60 * 24 * 7;

    while ( date('z',$t2) != date('z',$t1) )
    {
        if (in_array(date('w',$t1),$CONFIG['working_days'])) $days++;
        $t1 += (60 * 60 * 24);
    }

    // this could be negative and that's not ok
    $coefficient = 1;
    if ($t2 < $t1)
    {
        $t3 = $t2;
        $t2 = $t1;
        $t1 = $t3;
        $coefficient =- 1;
    }

    $min = floor( ($t2 - $t1) / 60 ) * $coefficient;

    $minutes= $min + ($weeks * count($CONFIG['working_days']) + $days ) * ($ewd-$swd) * 60;

    return $minutes;

//new version below
/*
    global $CONFIG;
    $swd = $CONFIG['start_working_day']/3600;
    $ewd = $CONFIG['end_working_day']/3600;

// Just in case they are the wrong way around ...

    if ( $t1 > $t2 )
    {
        $t3 = $t2;
        $t2 = $t1;
        $t1 = $t3;
    }

    $currenttime = $t1;

    $timeworked = 0;

    $t2date = getdate($t2);

    $midnight = 1440; // 24 * 60  minutes

    while ($currenttime < $t2) // was <=
    {
        $time = getdate($currenttime);

        $ph = 0;

        if (in_array($time['wday'], $CONFIG['working_days']) AND $time['hours'] >= $swd
            AND $time['hours'] <= $ewd AND (($ph = is_public_holiday($currenttime, $publicholidays)) == 0))
        {
            if ($t2date['yday'] == $time['yday'] AND $t2date['year'] == $time['year'])
            {
                // if end same day as time
                $c = $t2 - $currenttime;
                $timeworked += $c/60;
                $currenttime += $c;
            }
            else
            {
                // End on a different day
                $secondsintoday = (($t2date['hours']*60)*60)+($t2date['minutes']*60)+$t2date['seconds'];

                $timeworked += ($CONFIG['end_working_day']-$secondsintoday)/60;

                $currenttime += ($midnight*$secondsintoday)+$swd;
            }
        }
        else
        {
            // Jump closer to the next work minute
            if (!in_array($time['wday'], $CONFIG['working_days']))
            {
                // Move to next day
                $c = ($time['hours'] * 60) + $time['minutes'];
                $diff = $midnight - $c;
                $currenttime += ($diff * 60); // to seconds

                // Jump to start of working day
                $currenttime += ($swd * 60);
            }
            else if ($time['hours'] < $swd)
            {
                // jump to beginning of working day
                $c = ($time['hours'] * 60) + $time['minutes'];
                $diff = ($swd * 60) - $c;
                $currenttime += ($diff * 60); // to seconds
            }
            else if ($time['hours'] > $ewd)
            {
                // Jump to the start of the next working day
                $c = ($midnight - (($time['hours'] * 60) + $time['minutes'])) + ($swd * 60);
                $currenttime += ($c * 60);
            }
            else if ($ph != 0)
            {
                // jump to the minute after the public holiday
                $currenttime += $ph + 60;

                // Jump to start of working day
                $currenttime += ($swd * 60);
            }
            else
            {
                $currenttime += 60;  // move to the next minute
            }
        }
    }

    return $timeworked;
 */
}


/**
* @author Ivan Lucas
*/
function is_active_status($status, $states)
{
    if (in_array($status, $states)) return false;
    else return true;
}


/**
* Function to get an array of public holdidays
* @author Paul Heaney
* @param $startdate int - Start of the period to find public holidays in
* @param $enddate int - Start of the period to find public holidays in
* @return array of Holiday
*/
function get_public_holidays($startdate, $enddate)
{
    $sql = "SELECT * FROM `{$GLOBALS['dbHolidays']}` ";
    $sql .= "WHERE type = 10 AND (startdate >= '{$startdate}' AND startdate <= '{$enddate}')";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $publicholidays;

    if (mysql_num_rows($result) > 0)
    {
        // Assume public holidays are ALL day
        while ($obj = mysql_fetch_object($result))
        {
            $holiday = new Holiday();
            $holiday->starttime = $obj->startdate;
            $holiday->endtime = ($obj->startdate+(60*60*24));

            $publicholidays[] = $holiday;
        }
    }

    return $publicholidays;
}

/**
    * Calculate the engineer working time between two timestamps for a given incident
    i.e. ignore times when customer has action
    * @author Ivan Lucas
    @param $incidentid integer - The incident ID to perform a calculation on
    @param $t1 integer - UNIX Timestamp. Start of range
    @param $t2 integer - UNIX Timestamp. End of range
    @param $states array (optional) Does not count time when the incident is set to
        any of the states in this array. (Default is closed, awaiting closure and awaiting customer action)
*/
function calculate_incident_working_time($incidentid, $t1, $t2, $states=array(2,7,8))
{
    global $dbHolidays, $dbUpdates;

    if ( $t1 > $t2 )
    {
        $t3 = $t2;
        $t2 = $t1;
        $t1 = $t3;
    }

    $startofday = mktime(0,0,0, date("m",$t1), date("d",$t1), date("Y",$t1));
    $endofday = mktime(23,59,59, date("m",$t2), date("d",$t2), date("Y",$t2));

    $publicholidays = get_public_holidays($startofday, $endofday);

    $sql = "SELECT id, currentstatus, timestamp FROM `{$dbUpdates}` WHERE incidentid='$incidentid' ORDER BY id ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $time = 0;
    $timeptr = 0;
    $laststatus = 2; // closed
    while ($update = mysql_fetch_array($result))
    {
        //  if ($t1<=$update['timestamp'])
        if ($t1 <= $update['timestamp'])
        {
            if ($timeptr == 0)
            {
                // This is the first update
                // If it's active, set the ptr = t1
                // otherwise set to current timestamp ???
                if (is_active_status($laststatus, $states))
                {
                    $timeptr = $t1;
                }
                else
                {
                    $timeptr = $update['timestamp'];
                }
            }

            if ($t2 < $update['timestamp'])
            {
                // If we have reached the very end of the range, increment time to end of range, break
                if (is_active_status($laststatus, $states))
                {
                    $time += calculate_working_time($timeptr,$t2,$publicholidays);
                }
                break;
            }

            // if status has changed or this is the first (active update)
            if (is_active_status($laststatus, $states) != is_active_status($update['currentstatus'], $states))
            {
                // If it's active and we've not reached the end of the range, increment time
                if (is_active_status($laststatus, $states) && ($t2 >= $update['timestamp']))
                {
                    $time += calculate_working_time($timeptr,$update['timestamp'], $publicholidays);
                }
                else
                {
                    $timeptr = $update['timestamp'];
                }
                // if it's not active set the ptr
            }
        }
        $laststatus = $update['currentstatus'];
    }
    mysql_free_result($result);

    // Calculate remainder
    if (is_active_status($laststatus, $states) && ($t2 >= $update['timestamp']))
    {
        $time += calculate_working_time($timeptr,$t2,$publicholidays);
    }

    return $time;
}


function strip_comma($string)
{
    // also strips Tabs, CR's and LF's
    $string = str_replace(",", " ", $string);
    $string = str_replace("\r", " ", $string);
    $string = str_replace("\n", " ", $string);
    $string = str_replace("\t", " ", $string);
    return $string;
}


function leading_zero($length,$number)
{
    $length = $length-strlen($number);
    for ($i = 0; $i < $length; $i++)
    {
        $number = "0" . $number;
    }
    return ($number);
}



/**
 * @param $lang string takes either 'user' or 'system' as to which language to use
 **/
function readable_date($date, $lang = 'user')
{
    global $SYSLANG;
    // Takes a UNIX Timestamp and returns a string with a pretty readable date
    // e.g. Yesterday @ 5:28pm
    if (ldate('dmy', $date) == ldate('dmy', time()))
    {
        if ($lang == 'user')
        {
            $datestring = "{$GLOBALS['strToday']} @ ".ldate('g:ia', $date);
        }
        else
        {
            $datestring = "{$SYSLANG['strToday']} @ ".ldate('g:ia', $date);
        }
    }
    elseif (ldate('dmy', $date) == ldate('dmy', (time()-86400)))
    {
        if ($lang == 'user')
        {
            $datestring = "{$GLOBALS['strYesterday']} @ ".ldate('g:ia', $date);
        }
        else
        {
            $datestring = "{$SYSLANG['strYesterday']} @ ".ldate('g:ia', $date);
        }
    }
    else
    {
        $datestring = ldate("l jS M y @ g:ia", $date);
    }
    return $datestring;
}


// Select a header style, h1, h2 etc.
function header_listbox($headersize,$header,$element)
{
    $html .= "<select id='header$element' name='header$element' style='display:inline' onchange=\"change_header($element,'$header');\">\n";
    $html .= "<option value='h1' ";  if ($headersize=='h1') $html .= "selected='selected'";  $html .= ">H1 (Largest)</option>\n";
    $html .= "<option value='h2' ";  if ($headersize=='h2') $html .= "selected='selected'";  $html .= ">H2</option>\n";
    $html .= "<option value='h3' ";  if ($headersize=='h3') $html .= "selected='selected'";  $html .= ">H3</option>\n";
    $html .= "<option value='h4' ";  if ($headersize=='h4') $html .= "selected='selected'";  $html .= ">H4</option>\n";
    $html .= "<option value='h5' ";  if ($headersize=='h5') $html .= "selected='selected'";  $html .= ">H5 (Smallest)</option>\n";
    $html .= "</select>\n";
    return $html;
}


function distribution_listbox($name, $distribution)
{
    $html  = "<select name='$name'>\n";
    $html .= "<option value='public' ";  if ($distribution=='public') $html .= "selected='selected'";  $html .= ">{$GLOBALS['strPublic']}</option>\n";
    $html .= "<option value='private' style='color: blue;' ";  if ($distribution=='private') $html .= "selected='selected'";  $html .= ">{$GLOBALS['strPrivate']}</option>\n";
    $html .= "<option value='restricted' style='color: red;' ";  if ($distribution=='restricted') $html .= "selected='selected'";  $html .= ">{$GLOBALS['strRestricted']}</option>\n";
    $html .= "</select>\n";
    return $html;
}


function remove_slashes($string)
{
    $string = str_replace("\\'", "'", $string);
    $string = str_replace("\'", "'", $string);
    $string = str_replace("\\'", "'", $string);
    $string = str_replace("\\\"", "\"", $string);

    return $string;
}


/**
    * Return the email address of the notify contact of the given contact
    * @author Ivan Lucas
    * @returns string. email address.
*/
function contact_notify_email($contactid)
{
    global $dbContacts;
    $sql = "SELECT notify_contactid FROM `{$dbContacts}` WHERE id='$contactid' LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($notify_contactid) = mysql_fetch_row($result);

    $sql = "SELECT email FROM `{$dbContacts}` WHERE id='$notify_contactid' LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($email) = mysql_fetch_row($result);

    return $email;
}


/**
    * Returns the contact ID of the notify contact for the given contact ID
    * @author Ivan Lucas
    * @param $contactid integer. Contact ID
    * @param $level integer. Number of levels to recurse upwards
    * @note If Level is specified and is >= 1 then the notify contact is
    * found recursively, ie. the notify contact of the notify contact etc.
*/
function contact_notify($contactid, $level=0)
{
    global $dbContacts;
    $notify_contactid = 0;
    if ($level == 0)
    {
        return $contactid;
    }
    else
    {
        $sql = "SELECT notify_contactid FROM `{$dbContacts}` WHERE id='$contactid' LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        list($notify_contactid) = mysql_fetch_row($result);

        if ($level > 0)
        {
            $newlevel = $level -1;
            $notify_contactid = contact_notify($notify_contactid, $newlevel);

        }
        return $notify_contactid;
    }
}


/**
    * HTML select box listing substitute engineers
    * @author Ivan Lucas
*/
function software_backup_dropdown($name, $userid, $softwareid, $backupid)
{
    global $dbUsers, $dbUserSoftware, $dbSoftware;
    $sql = "SELECT *, u.id AS userid FROM `{$dbUserSoftware}` AS us, `{$dbSoftware}` AS s, `{$dbUsers}` AS u ";
    $sql .= "WHERE us.softwareid = s.id ";
    $sql .= "AND s.id = '$softwareid' ";
    $sql .= "AND userid != '{$userid}' AND u.status > 0 ";
    $sql .= "AND us.userid = u.id ";
    $sql .= " ORDER BY realname";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $countsw = mysql_num_rows($result);
    if ($countsw >= 1)
    {
        $html = "<select name='$name'>\n";
        $html .= "<option value='0'";
        if ($user->userid==0) $html .= " selected='selected'";
        $html .= ">{$GLOBALS['strNone']}</option>\n";
        while ($user = mysql_fetch_object($result))
        {
            $html .= "<option value='{$user->userid}'";
            if ($user->userid == $backupid) $html .= " selected='selected'";
            $html .= ">{$user->realname}</option>\n";
        }
        $html .= "</select>\n";
    }
    else
    {
        $html .= "<input type='hidden' name='$name' value='0' />{$GLOBALS['strNoneAvailable']}";
    }
    return ($html);
}


/**
    *
    * @author Ivan Lucas
*/
function software_backup_userid($userid, $softwareid)
{
    global $dbUserSoftware;
    $backupid = 0; // default
    // Find out who is the substitute for this user/skill
    $sql = "SELECT backupid FROM `{$dbUserSoftware}` WHERE userid = '$userid' AND softwareid = '$softwareid'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($backupid) = mysql_fetch_row($result);
    $backup1 = $backupid;

    // If that substitute is not accepting then try and find another
    if (empty($backupid) OR user_accepting($backupid) != 'Yes')
    {
        $sql = "SELECT backupid FROM `{$dbUserSoftware}` WHERE userid='$backupid' AND userid!='$userid' ";
        $sql .= "AND softwareid='$softwareid' AND backupid!='$backup1'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        list($backupid) = mysql_fetch_row($result);
        $backup2=$backupid;
    }

    // One more iteration, is the backup of the backup accepting?  If not try another
    if (empty($backupid) OR user_accepting($backupid)!='Yes')
    {
        $sql = "SELECT backupid FROM `{$dbUserSoftware}` WHERE userid='$backupid' AND userid!='$userid' ";
        $sql .= "AND softwareid='$softwareid' AND backupid!='$backup1' AND backupid!='$backup2'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        list($backupid) = mysql_fetch_row($result);
    }
    return ($backupid);
}


/**
    * Switches incidents temporary owners to the backup/substitute engineer depending on the setting of 'accepting'
    * @author Ivan Lucas
    * @param $userid integer. The userid of the user who's status has changed.
    * @param $accepting string. 'yes' or 'no' to indicate whether the user is accepting
    * @note if the $accepting parameter is 'no' then the function will attempt to temporarily assign
    * all the open incidents that the user owns to the users defined substitute engineers
    * If Substitute engineers cannot be found or they themselves are not accepting, the given users incidents
    * are placed in the holding queue
*/
function incident_backup_switchover($userid, $accepting)
{
    global $now, $dbIncidents, $dbUpdates, $dbTempAssigns, $dbUsers, $dbUserStatus;

    $usersql = "SELECT u.*, us.name AS statusname ";
    $usersql .= "FROM `{$dbUsers}` AS u, `{$dbUserStatus}` AS us ";
    $usersql .= "WHERE u.id = '{$userid}' AND u.status = us.id";
    $userresult = mysql_query($usersql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $user = mysql_fetch_row($userresult);

    if (strtolower($accepting) == 'no')
    {
        // Look through the incidents that this user OWNS (and are not closed)
        $sql = "SELECT * FROM `{$dbIncidents}` WHERE (owner='$userid' OR towner='$userid') AND status!=2";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        while ($incident = mysql_fetch_object($result))
        {
            // Try and find a backup/substitute engineer
            $backupid = software_backup_userid($userid, $incident->softwareid);

            if (empty($backupid))
            {
                // no backup engineer found so add to the holding queue
                // Look to see if this assignment is in the queue already
                $fsql = "SELECT * FROM `{$dbTempAssigns}` WHERE incidentid='{$incident->id}' AND originalowner='{$userid}'";
                $fresult = mysql_query($fsql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
                if (mysql_num_rows($fresult) < 1)
                {
                    // it's not in the queue, and the user isn't accepting so add it
                    //$userstatus=user_status($userid);
                    $userstatus = $user['status'];
                    $usql = "INSERT INTO `{$dbTempAssigns}` (incidentid,originalowner,userstatus) VALUES ('{$incident->id}', '{$userid}', '$userstatus')";
                    mysql_query($usql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                }
            }
            else
            {
                // do an automatic temporary reassign
                // update incident
                $rusql = "UPDATE `{$dbIncidents}` SET ";
                $rusql .= "towner='{$backupid}', lastupdated='$now' WHERE id='{$incident->id}' LIMIT 1";
                mysql_query($rusql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

                // add update
                $username=user_realname($userid);
                //$userstatus = userstatus_name(user_status($userid));
                $userstatus = $user['statusname'];
                //$usermessage=user_message($userid);
                $usermessage = $user['message'];
                $bodytext = "Previous Incident Owner ({$username}) {$userstatus}  {$usermessage}";
                $assigntype = 'tempassigning';
                $risql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, bodytext, type, timestamp, currentowner, currentstatus) ";
                $risql .= "VALUES ('{$incident->id}', '0', '$bodytext', '$assigntype', '$now', ";
                $risql .= "'{$backupid}', ";
                $risql .= "'{$incident->status}')";
                mysql_query($risql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

                // Look to see if this assignment is in the queue already
                $fsql = "SELECT * FROM `{$dbTempAssigns}` WHERE incidentid='{$incident->id}' AND originalowner='{$userid}'";
                $fresult = mysql_query($fsql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
                if (mysql_num_rows($fresult) < 1)
                {
                    //$userstatus=user_status($userid);
                    $userstatus = $user['status'];
                    $usql = "INSERT INTO `{$dbTempAssigns}` (incidentid,originalowner,userstatus,assigned) VALUES ('{$incident->id}', '{$userid}', '$userstatus','yes')";
                    mysql_query($usql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                }
                else
                {
                    // mark the temp assigns table so it's not showing in the holding queue
                    $tasql = "UPDATE `{$dbTempAssigns}` SET assigned='yes' WHERE originalowner='$userid' AND incidentid='{$incident->id}' LIMIT 1";
                    mysql_query($tasql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                }
            }
        }
    }
    elseif ($accepting=='')
    {
        // Do nothing when accepting status doesn't exist
    }
    else
    {
        // The user is now ACCEPTING, so first have a look to see if there are any reassignments in the queue
        $sql = "SELECT * FROM `{$dbTempAssigns}` WHERE originalowner='{$userid}' ";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        while ($assign = mysql_fetch_object($result))
        {
            if ($assign->assigned == 'yes')
            {
                // Incident has actually been reassigned, so have a look if we can grab it back.
                $lsql = "SELECT id,status FROM `{$dbIncidents}` WHERE id='{$assign->incidentid}' AND owner='{$assign->originalowner}' AND towner!=''";
                $lresult = mysql_query($lsql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
                while ($incident = mysql_fetch_object($lresult))
                {
                    // Find our tempassign
                    $usql = "SELECT id,currentowner FROM `{$dbUpdates}` WHERE incidentid='{$incident->id}' AND userid='0' AND type='tempassigning' ORDER BY id DESC LIMIT 1";
                    $uresult = mysql_query($usql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
                    list($prevassignid,$tempowner) = mysql_fetch_row($uresult);

                    // Look to see if the temporary owner has updated the incident since we temp assigned it
                    // If he has, we leave it in his queue
                    $usql = "SELECT id FROM `{$dbUpdates}` WHERE incidentid='{$incident->id}' AND id > '{$prevassignid}' AND userid='$tempowner' LIMIT 1 ";
                    $uresult = mysql_query($usql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
                    if (mysql_num_rows($uresult) < 1)
                    {
                        // Incident appears not to have been updated by the temporary owner so automatically reassign back to orignal owner
                        // update incident
                        $rusql = "UPDATE `{$dbIncidents}` SET ";
                        $rusql .= "towner='', lastupdated='$now' WHERE id='{$incident->id}' LIMIT 1";
                        mysql_query($rusql);
                        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

                        // add update
                        $username = user_realname($userid);
                        //$userstatus = userstatus_name(user_status($userid));
                        $userstatus = $user['statusname'];
                        //$usermessage=user_message($userid);
                        $usermessage = $user['message'];
                        $bodytext = "Reassigning to original owner {$username} ({$userstatus})";
                        $assigntype = 'reassigning';
                        $risql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, bodytext, type, timestamp, currentowner, currentstatus) ";
                        $risql .= "VALUES ('{$incident->id}', '0', '$bodytext', '$assigntype', '$now', ";
                        $risql .= "'{$backupid}', ";
                        $risql .= "'{$incident->status}')";
                        mysql_query($risql);
                        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

                        // remove from assign queue now, all done
                        $rsql = "DELETE FROM `{$dbTempAssigns}` WHERE incidentid='{$assign->incidentid}' AND originalowner='{$assign->originalowner}'";
                        mysql_query($rsql);
                        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                    }
                }
            }
            else
            {
                // now have a look to see if the reassign was completed
                $ssql = "SELECT id FROM `{$dbIncidents}` WHERE id='{$assign->incidentid}' LIMIT 1";
                $sresult = mysql_query($ssql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
                if (mysql_num_rows($sresult) >= 1)
                {
                    // reassign wasn't completed, or it was already assigned back, simply remove from assign queue
                    $rsql = "DELETE FROM `{$dbTempAssigns}` WHERE incidentid='{$assign->incidentid}' AND originalowner='{$assign->originalowner}'";
                    mysql_query($rsql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                }
            }
        }
    }
    return;
}


/**
    * Suggest the userid of a suitable person to handle the given incident
    * @author Ivan Lucas
    * @param $incidentid integer. An incident ID to suggest a new owner for
    * @param $exceptuserid integer. This user ID will not be suggested (e.g. the existing owner)
    * @returns A user ID of the suggested new owner
    * @retval FALSE failure.
    * @retval integer The user ID of the suggested new owner
    * @note Users are chosen randomly in a weighted lottery depending on their
    * avilability and queue status
*/
function suggest_reassign_userid($incidentid, $exceptuserid = 0)
{
    global $now, $dbUsers, $dbIncidents, $dbUserSoftware;
    $sql = "SELECT product, softwareid, priority, contact, owner FROM `{$dbIncidents}` WHERE id={$incidentid} LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (!$result)
    {
        $userid = FALSE;
    }
    else
    {
        $incident = mysql_fetch_object($result);
        // If this is a critical incident the user we're assigning to must be online
        if ($incident->priority >= 4) $req_online = TRUE;
        else $req_online = FALSE;

        // Find the users with this skill (or all users)
        if (!empty($incident->softwareid))
        {
            $sql = "SELECT us.userid, u.status, u.lastseen FROM `{$dbUserSoftware}` AS us, `{$dbUsers}` AS u ";
            $sql .= "WHERE u.id = us.userid AND u.status > 0 AND u.accepting='Yes' ";
            if ($exceptuserid > 0) $sql .= "AND u.id != '$exceptuserid' ";
            $sql .= "AND softwareid = {$incident->softwareid}";
        }
        else
        {
            $sql = "SELECT id AS userid, status, lastseen FROM `{$dbUsers}` WHERE status > 0 AND users.accepting='Yes' ";
            if ($exceptuserid > 0) $sql .= "AND id != '$exceptuserid' ";
        }
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

        // Fallback to all users if we have no results from above
        if (mysql_num_rows($result) < 1)
        {
            $sql = "SELECT id AS userid, status, lastseen FROM `{$dbUsers}` WHERE status > 0 ";
            if ($exceptuserid > 0) $sql .= "AND id != '$exceptuserid' ";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        }

        while ($user = mysql_fetch_object($result))
        {
            // Get a ticket for being skilled
            // Or in the case we don't know the skill, just get a ticket for accepting
            $ticket[] = $user->userid;

            // Get a ticket for being seen in the past 30 minutes
            if (mysql2date($user->lastseen) > $now - 1800) $ticket[] = $user->userid;

            // Get two tickets for being marked in-office or working at home
            if ($user->status == 1 OR $user->status == 6)
            {
                $ticket[] = $user->userid;
                $ticket[] = $user->userid;
            }

            // Get one ticket for being marked at lunch or in meeting
            // BUT ONLY if the incident isn't critical
            if ($incident->priority < 4 AND ($user->status == 3 OR $user->status == 4))
            {
                $ticket[] = $user->userid;
            }

            // Have a look at the users incident queue (owned)
            $qsql = "SELECT id, priority, lastupdated, status, softwareid FROM `{$dbIncidents}` WHERE owner={$user->userid}";
            $qresult = mysql_query($qsql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            $queue_size = mysql_num_rows($qresult);
            if ($queue_size > 0)
            {
                $queued_critical = 0;
                $queued_high = 0;
                $queue_lastupdated = 0;
                $queue_samecontact = FALSE;
                while ($queue = mysql_fetch_object($qresult))
                {
                    if ($queue->priority == 3) $queued_high++;
                    if ($queue->priority >= 4) $queued_critical++;
                    if ($queue->lastupdated > $queue_lastupdated) $queue_lastupdated = $queue->lastupdated;
                    if ($queue->contact == $incident->contact) $queue_samecontact = TRUE;
                }
                // Get one ticket for your queue being updated in the past 4 hours
                if ($queue_lastupdated > ($now - 14400)) $user->userid;

                // Get two tickets for dealing with the same contact in your queue
                if ($queue_samecontact == TRUE)
                {
                    $ticket[] = $user->userid;
                    $ticket[] = $user->userid;
                }

                // Get one ticket for having five or less incidents
                if ($queued_size <=5) $ticket[] = $user->userid;

                // Get up to three tickets, one less ticket for each critical incident in queue
                for ($c=1;$c < (3 - $queued_critical);$c++) $ticket[] = $user->userid;

                // Get up to three tickets, one less ticket for each high priority incident in queue
                for ($c=1;$c < (3 - $queued_high);$c++) $ticket[] = $user->userid;
            }
            else
            {
                // Get one ticket for having an empty queue
                $ticket[] = $user->userid;
            }
        }

        // Do the lottery - "Release the balls"
        $numtickets = count($ticket)-1;
        $rand = mt_rand(0, $numtickets);
        $userid = $ticket[$rand];
    }
    if (empty($userid)) $userid = FALSE;
    return $userid;
}


/**
    * Format an external ID (From an escalation partner) as HTML
    * @author Ivan Lucas
    * @param $externalid integer. An external ID to format
    * @param $escalationpath integer. Escalation path ID
    * @returns HTML
*/
function format_external_id($externalid, $escalationpath='')
{
    global $CONFIG, $dbEscalationPaths;

    if (!empty($escalationpath))
    {
        // Extract escalation path
        $epsql = "SELECT id, name, track_url, home_url, url_title FROM `{$dbEscalationPaths}` ";
        if (!empty($escalationpath)) $epsql .= "WHERE id='$escalationpath' ";
        $epresult = mysql_query($epsql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        if (mysql_num_rows($epresult) >= 1)
        {
            while ($escalationpath = mysql_fetch_object($epresult))
            {
                $epath['name'] = $escalationpath->name;
                $epath['track_url'] = $escalationpath->track_url;
                $epath['home_url'] = $escalationpath->home_url;
                $epath['url_title'] = $escalationpath->url_title;
            }
            if (!empty($externalid))
            {
                $epathurl = str_replace('%externalid%',$externalid,$epath['track_url']);
                $html = "<a href='{$epathurl}' title='{$epath['url_title']}'>{$externalid}</a>";
            }
            else
            {
                $epathurl = $epath['home_url'];
                $html = "<a href='{$epathurl}' title='{$epath['url_title']}'>{$epath['name']}</a>";
            }
        }
    }
    else
    {
        $html = $externalid;
    }
    return $html;
}


// Converts a PHP.INI integer into a byte value
function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch ($last)
    {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}


function draw_tabs($tabsarray, $selected='')
{
    if ($selected=='') $selected=key($tabsarray);
    $html .= "<div id='tabcontainer'>";
    $html .= "<ul id='tabnav'>";
    foreach ($tabsarray AS $tab => $url)
    {
        $html .= "<li><a href='$url'";
        if (strtolower($tab) == strtolower($selected)) $html .= " class='active'";
        $tab=str_replace('_', ' ', $tab);
        $html .= ">$tab</a></li>\n";
    }
    $html .= "</ul>";
    $html .= "</div>";

return ($html);
}


function send_feedback($contractid)
{
    global $CONFIG;
    foreach ($CONFIG['no_feedback_contracts'] AS $contract)
    {
        if ($contract == $contractid)
        {
            return FALSE;
        }
    }

    return TRUE;
}

// Creates a blank feedback form response
function create_incident_feedback($formid, $incidentid)
{
    global $dbFeedbackRespondents;
    $contactid = incident_contact($incidentid);
    $email = contact_email($respondent);  // BUGBUG where is this variable comeing from ?

    $sql = "INSERT INTO `{$dbFeedbackRespondents}` (formid, contactid, email, incidentid) VALUES (";
    $sql .= "'".mysql_real_escape_string($formid)."', ";
    $sql .= "'".mysql_real_escape_string($contactid)."', ";
    $sql .= "'".mysql_real_escape_string($email)."', ";
    $sql .= "'".mysql_real_escape_string($incidentid)."') ";
    mysql_query($sql);
    if (mysql_error()) trigger_error ("MySQL Error: ".mysql_error(), E_USER_ERROR);
    $blankformid=mysql_insert_id();
    return $blankformid;
}


function file_permissions_info($perms)
{
    if (($perms & 0xC000) == 0xC000) $info = 's';
    elseif (($perms & 0xA000) == 0xA000) $info = 'l';
    elseif (($perms & 0x8000) == 0x8000) $info = '-';
    elseif (($perms & 0x6000) == 0x6000) $info = 'b';
    elseif (($perms & 0x4000) == 0x4000) $info = 'd';
    elseif (($perms & 0x2000) == 0x2000) $info = 'c';
    elseif (($perms & 0x1000) == 0x1000) $info = 'p';
    else $info = 'u';

    // Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x' ) :
            (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x' ) :
            (($perms & 0x0400) ? 'S' : '-'));

    // World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x' ) :
            (($perms & 0x0200) ? 'T' : '-'));

    return $info;
}


/**
    * Make an external variable safe for database and HTML display
    * @author Ivan Lucas, Kieran Hogg
    * @param mixed $var variable to replace
    * @param bool $striphtml whether to strip html
    * @param bool $transentities whether to translate all aplicable chars (true) or just special chars (false) into html entites
    * @param bool $mysqlescape whether to mysql_escape()
    * @param array $disallowedchars array of chars to remove
    * @param array $replacechars array of chars to replace as $orig => $replace
    * @returns variable
*/
function cleanvar($vars, $striphtml = TRUE, $transentities = TRUE,
                  $mysqlescape = TRUE, $disallowedchars = array(),
                  $replacechars = array())
{
    if (is_array($vars))
    {
        foreach ($vars as $key => $singlevar)
        {
            $var[$key] = cleanvar($singlevar, $striphtml, $transentities, $mysqlescape,
                     $disallowedchars, $replacechars);
        }
    }
    else
    {
        $var = $vars;
        if ($striphtml === TRUE)
        {
            $var = strip_tags($var);
        }

        if (!empty($disallowedchars))
        {
            $var = str_replace($disallowedchars, '', $var);
        }

        if (!empty($replacechars))
        {
            foreach ($replacechars as $orig => $replace)
            {
                $var = str_replace($orig, $replace, $var);
            }
        }

        if ($transentities)
        {
            $var = htmlentities($var, ENT_COMPAT, $GLOBALS['i18ncharset']);
        }
        else
        {
            $var = htmlspecialchars($var, ENT_COMPAT, $GLOBALS['i18ncharset']);
        }

        if ($mysqlescape)
        {
            $var = mysql_real_escape_string($var);
        }

        $var = trim($var);
    }
    return $var;
}


function external_escalation($escalated, $incid)
{
    foreach ($escalated as $i => $id)
    {
        if ($id == $incid)
        {
            return "yes";
        }
    }

    return "no";
}



/**
    * Converts BBcode to HTML
    * @author Paul Heaney
    * @param $text string. Text with BBCode
    * @returns string HTML
*/
function bbcode($text)
{
    $bbcode_regex = array(0 => "/\[b\](.*?)\[\/b\]/s",
                         1 => "/\[i\](.*?)\[\/i\]/s",
                         2 => "/\[u\](.*?)\[\/u\]/s",
                         3 => "/\[quote\](.*?)\[\/quote\]/s",
                         4 => "/\[size=(.+?)\](.+?)\[\/size\]/is",
                         //5 => "/\[url\](.*?)\[\/url\]/s",
                         6 => "/\[size=(.+?)\](.+?)\[\/size\]/is",
                         7 => "/\[img\](.*?)\[\/img\]/s",
                         8 => "/\[size=(.+?)\](.+?)\[\/size\]/is",
                         9 => "/\[color\](.*?)\[\/color\]/s",
                         10 => "/\[size=(.+?)\](.+?)\[\/size\]/is",
                         11 => "/\[size\](.*?)\[\/size\]/s",
                         12 => "/\[code\](.*?)\[\/code\]/s",
                         13 => "/\[hr\]/s",
                         14 => "/\[s\](.*?)\[\/s\]/s",
                         15 => "/\[\[att\=(.*?)]](.*?)\[\[\/att]]/s",
                        16 => "/\[url=(.+?)\](.+?)\[\/url\]/is");

    $bbcode_replace = array(0 => "<strong>$1</strong>",
                             1 => "<em>$1</em>",
                             2 => "<u>$1</u>",
                             3 => "<blockquote><p>$1</p></blockquote>",
                             4 => "<blockquote cite=\"$1\"><p>$1 said:<br />$2</p></blockquote>",
                             //5 => '<a href="$1" title="$1">$1</a>',
                             6 => "<a href=\"$1\" title=\"$1\">$2</a>",
                             7 => "<img src=\"$1\" alt=\"User submitted image\" />",
                             8 => "<span style=\"color:$1\">$2</span>",
                             9 => "<span style=\"color:red;\">$1</span>",
                             10 => "<span style=\"font-size:$1\">$2</span>",
                             11 => "<span style=\"font-size:large\">$1</span>",
                             12 => "<code>$1</code>",
                             13 => "<hr />",
                             14 => "<span style=\"text-decoration:line-through\">$1</span>",
                             15 => "<a href=\"{$_SERVER['HTTP_HOST']}/{$CONFIG['application_webpath']}download.php?id=$1\">$2</a>",
                            16 => "<a href=\"$1\">$2</a>");
                                                        
    $html = preg_replace($bbcode_regex, $bbcode_replace, $text);
    return $html;
}


function strip_bbcode_tooltip($text)
{
    $bbcode_regex = array(0 => '/\[url\](.*?)\[\/url\]/s',

                        1 => '/\[url\=(.*?)\](.*?)\[\/url\]/s',
                        2 => '/\[color\=(.*?)\](.*?)\[\/color\]/s',
                        3 => '/\[size\=(.*?)\](.*?)\[\/size\]/s',
                        4 => '/\[blockquote\=(.*?)\](.*?)\[\/blockquote\]/s',
                        5 => '/\[blockquote\](.*?)\[\/blockquote\]/s');
    $bbcode_replace = array(0 => '$1',
                            1 => '$2',
                            2 => '$2',
                            3 => '$2',
                            4 => '$2',
                            5 => '$1'
                            );

    return preg_replace($bbcode_regex, $bbcode_replace, $text);
}


/**
    * Produces a HTML toolbar for use with a textarea or input box for entering bbcode
    * @author Ivan Lucas
    * @param $elementid string. HTML element ID of the textarea or input
    * @returns string HTML
*/
function bbcode_toolbar($elementid)
{
    $html = "\n<div class='bbcode_toolbar'>BBCode: ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[b]', '[/b]')\">B</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[i]', '[/i]')\">I</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[u]', '[/u]')\">U</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[s]', '[/s]')\">S</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[quote]', '[/quote]')\">Quote</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[url]', '[/url]')\">Link</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[img]', '[/img]')\">Img</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[color]', '[/color]')\">Color</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[size]', '[/size]')\">Size</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '[code]', '[/code]')\">Code</a> ";
    $html .= "<a href=\"javascript:insertBBCode('{$elementid}', '', '[hr]')\">HR</a> ";
    $html .= "</div>\n";
    return $html;
}


/**
    * Uses calendar.js to make a popup date picker
    * @author Ivan Lucas
    * @param $formelement string. form element id, eg. myform.dateinputbox
    * @returns string HTML
*/
function date_picker($formelement)
{
    global $CONFIG, $iconset;

    $divid = "datediv".str_replace('.','',$formelement);
    $html = "<img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/16x16/pickdate.png' ";
    $html .= "onmouseup=\"toggleDatePicker('$divid','$formelement')\" width='16' height='16' alt='date picker' style='cursor: pointer; vertical-align: bottom;' />";
    $html .= "<div id='$divid' style='position: absolute;'></div>";
    return $html;
}


/**
    * Produces HTML for a percentage indicator
    * @author Ivan Lucas
    * @param $percent int. Number between 0 and 100
    * @returns string HTML
*/
function percent_bar($percent)
{
    if ($percent == '') $percent = 0;
    if ($percent < 0) $percent = 0;
    if ($percent > 100) $percent = 100;
    // #B4D6B4;
    $html = "<div class='percentcontainer'>";
    $html .= "<div class='percentbar' style='width: {$percent}%;'>  {$percent}&#037;";
    $html .= "</div></div>\n";
    return $html;
}


function incident_open($incidentid)
{
    global $dbIncidents;
    $sql = "SELECT id FROM `{$dbIncidents}` WHERE id='$incidentid' AND status!=2";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    if (mysql_num_rows($result) > 0)
    {
        return $GLOBALS['strYes'];
    }
    else
    {
        $sql = "SELECT id FROM `{$dbIncidents}` WHERE id = '$incidentid'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        if (mysql_num_rows($result) > 0)
        {
            //closed
            return $GLOBALS['strNo'];
        }
        else
        {
            //doesn't exist
            return "Doesn't exist";
        }
    }
}

// Return HTML for a table column header (th and /th) with links for sorting
// Filter parameter can be an assocative array containing fieldnames and values
// to pass on the url for data filtering purposes
function colheader($colname, $coltitle, $sort = FALSE, $order='', $filter='', $defaultorder='a', $width='')
{
    global $CONFIG;
    if ($width !=  '')
    {
        $html = "<th width='".intval($width)."%'>";
    }
    else
    {
        $html = "<th>";
    }

    $qsappend='';
    if (!empty($filter) AND is_array($filter))
    {
        foreach ($filter AS $key => $var)
        {
            if ($var != '') $qsappend .= "&amp;{$key}=".urlencode($var);
        }
    }
    else
    {
        $qsappend='';
    }

    if ($sort==$colname)
    {
        //if ($order=='') $order=$defaultorder;
        if ($order=='a')
        {
            $html .= "<a href='{$_SERVER['PHP_SELF']}?sort=$colname&amp;order=d{$qsappend}'>{$coltitle}</a> ";
            $html .= "<img src='{$CONFIG['application_webpath']}images/sort_a.png' width='5' height='5' alt='{$GLOBALS['SortAscending']}' /> ";
        }
        else
        {
            $html .= "<a href='{$_SERVER['PHP_SELF']}?sort=$colname&amp;order=a{$qsappend}'>{$coltitle}</a> ";
            $html .= "<img src='{$CONFIG['application_webpath']}images/sort_d.png' width='5' height='5' alt='{$GLOBALS['SortDescending']}' /> ";
        }
    }
    else
    {
        if ($sort === FALSE) $html .= "{$coltitle}";
        else $html .= "<a href='{$_SERVER['PHP_SELF']}?sort=$colname&amp;order={$defaultorder}{$qsappend}'>{$coltitle}</a> ";
    }
    $html .= "</th>";
    return $html;
}


function parse_updatebody($updatebody, $striptags = TRUE)
{
    if (!empty($updatebody))
    {
        $updatebody = str_replace("&lt;hr&gt;", "[hr]\n", $updatebody);
        if ($striptags)
        {
            $updatebody = strip_tags($updatebody);
        }
        else
        {
            $updatebody = str_replace("<hr>", "", $updatebody);
        }
        $updatebody = nl2br($updatebody);
        $updatebody = str_replace("&amp;quot;", "&quot;", $updatebody);
        $updatebody = str_replace("&amp;gt;", "&gt;", $updatebody);
        $updatebody = str_replace("&amp;lt;", "&lt;", $updatebody);
        // Insert path to attachments
        //new style
        $updatebody = preg_replace("/\[\[att\=(.*?)\]\](.*?)\[\[\/att\]\]/","$2", $updatebody);
        //old style
        $updatebody = preg_replace("/\[\[att\]\](.*?)\[\[\/att\]\]/","$1", $updatebody);
        //remove tags that are incompatable with tool tip
        $updatebody = strip_bbcode_tooltip($updatebody);
        //then show compatable BBCode
        $updatebody = bbcode($updatebody);
        if (strlen($updatebody) > 490) $updatebody .= '...';
    }

    return $updatebody;
}


function add_note_form($linkid, $refid)
{
    global $now, $sit, $iconset;
    $html = "<form name='addnote' action='add_note.php' method='post'>";
    $html .= "<div class='detailhead note'> <div class='detaildate'>".readable_date($now)."</div>\n";
    $html .= icon('note', 16, $GLOBALS['strNote ']);
    $html .= " New Note by ".user_realname($sit[2])."</div>\n";
    $html .= "<div class='detailentry note'>";
    $html .= "<textarea rows='3' cols='40' name='bodytext' style='width: 94%; margin-top: 5px; margin-bottom: 5px; margin-left: 3%; margin-right: 3%; background-color: transparent; border: 1px dashed #A2A86A;'></textarea>";
    if (!empty($linkid))
    {
        $html .= "<input type='hidden' name='link' value='$linkid' />";
    }
    else
    {
        $html .= "&nbsp;Link <input type='text' name='link' size='3' />";
    }

    if (!empty($refid))
    {
        $html .= "<input type='hidden' name='refid' value='{$refid}' />";
    }
    else
    {
        $html .= "&nbsp;Ref ID <input type='text' name='refid' size='4' />";
    }

    $html .= "<input type='hidden' name='action' value='addnote' />";
    $html .= "<input type='hidden' name='rpath' value='{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}' />";
    $html .= "<div style='text-align: right'><input type='submit' value='{$GLOBALS['strAddNote']}' /></div>\n";
    $html .= "</div>\n";
    $html .= "</form>";
    return $html;
}


function show_notes($linkid, $refid, $delete = TRUE)
{
    global $sit, $iconset, $dbNotes;
    $sql = "SELECT * FROM `{$dbNotes}` WHERE link='{$linkid}' AND refid='{$refid}' ORDER BY timestamp DESC, id DESC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $countnotes = mysql_num_rows($result);
    if ($countnotes >= 1)
    {
        while ($note = mysql_fetch_object($result))
        {
            $html .= "<div class='detailhead note'> <div class='detaildate'>".readable_date(mysqlts2date($note->timestamp));
            if ($delete)
            {
                $html .= "<a href='delete_note.php?id={$note->id}&amp;rpath=";
                $html .= "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}' ";
                $html .= "onclick=\"return confirm_action('{$strAreYouSureDelete}');\">";
                $html .= icon('delete', 16)."</a>";
            }
            $html .= "</div>\n"; // /detaildate
            $html .= icon('note', 16)." ";
            $html .= sprintf($GLOBALS['strNoteAddedBy'], user_realname($note->userid,TRUE));
            $html .= "</div>\n"; // detailhead
            $html .= "<div class='detailentry note'>";
            $html .= nl2br(bbcode($note->bodytext));
            $html .= "</div>\n";
        }
    }
    return $html;
}


/**
    * Produces a HTML dashlet 'window' for display on the dashboard
    * @author Ivan Lucas
    * @param $dashboard string. Dashboard component name.
    * @param $dashletid string. The table row ID of that we are 'drawing' this dashlet into and
    *                           the ID of the dashboard component instance as recorded in the users settings
    *                           as a single string, this is received by the dashlet from dashboard_do()
    * @param $icon string. HTML for an icon to be displayed on the dashlet window
    * @param $title string. A title for the dashlet, also displayed on the dashlet window
    * @param $link string. URL of a page to link to from the dashlet window (link on the title)
    * @param $content string. HTML content to display inside the dashlet window
    * @note This function looks for the existence of two dashboard component functions
    *       dashboard_*_display() and dashboard_*_edit(), (where * is the name of the dashlet)
    *       if these are found the dashlet will use ajax and call these functions for it's
    *       main display (and refreshing) and to edit settings.
    * @returns string HTML
*/
function dashlet($dashboard, $dashletid, $icon, $title='', $link='', $content='')
{
    global $strLoading;
    if (empty($icon)) $icon = icon('dashboard', 16);
    if (empty($title)) $title = $GLOBALS['strUntitled'];
    $displayfn = "dashboard_{$dashboard}_display";
    $editfn = "dashboard_{$dashboard}_edit";

    $html .= "<div class='windowbox' id='{$dashletid}'>";
    $html .= "<div class='windowtitle'>";
    $html .= "<div>";
    if (function_exists($displayfn))
    {
        $html .= "<a href=\"javascript:get_and_display('ajaxdata.php?action=dashboard_display&amp;dashboard={$dashboard}&amp;did={$dashletid}','win{$dashletid}',true);\">";
        $html .= icon('reload', 16, '', '', "refresh{$dashletid}")."</a>";
    }
    
    if (function_exists($editfn))
    {
        $html .= "<a href=\"javascript:get_and_display('ajaxdata.php?action=dashboard_edit&amp;dashboard={$dashboard}&amp;did={$dashletid}','win{$dashletid}',false);\">";
        $html .= icon('edit', 16)."</a>";
    }
    $html .= "</div>";
    if (!empty($link)) $html .= "<a href=\"{$link}\">{$icon}</a> <a href=\"{$link}\">{$title}</a>";
    else $html .= "{$icon} {$title}";
    $html .= "</div>\n";
    $html .= "<div class='window' id='win{$dashletid}'>";
    $html .= $content;
    $displayfn = "dashboard_{$dashboard}_display";
    if (function_exists($displayfn))
    {
        $html .= "<script type='text/javascript'>\n//<![CDATA[\nget_and_display('ajaxdata.php?action=dashboard_display&dashboard={$dashboard}','win{$dashletid}',true);\n//]]>\n</script>\n";
    }
    $html .= "</div></div>";

    return $html;
}


/**
    * Creates a link that opens within a dashlet window
    * @author Ivan Lucas
    * @param $dashboard string. Dashboard component name.
    * @param $dashletid string. The table row ID of that we are 'drawing' this dashlet into and
    *                           the ID of the dashboard component instance as recorded in the users settings
    *                           as a single string, this is received by the dashlet from dashboard_do()
    * @param $text string. The text of the hyperlink for the user to click
    * @param $action string. edit|save|display
                                edit = This is a link to a dashlet config form page
                                save = Submit a dashlet config form (see $formid param)
                                display = Display a regular dashlet page
    * @param $params array. Associative array of parameters to pass on the URL of the link
    * @param $refresh boolean. The link will be automatically refreshed when TRUE
    * @param $formid string. The form element ID to be submitted when using 'save' action
    * @returns string HTML
*/
function dashlet_link($dashboard, $dashletid, $text='', $action='', $params='', $refresh = FALSE, $formid='')
{
    if ($action == 'edit') $action = 'dashboard_edit';
    elseif ($action == 'save') $action = 'dashboard_save';
    else $action = 'dashboard_display';
    if (empty($text)) $text = $GLOBALS['strUntitled'];

    // Convert refresh boolean to javascript text for boolean
    if ($refresh) $refresh = 'true';
    else $refresh = 'false';

    if ($action == 'dashboard_save' AND $formid == '') $formid = "{$dashboard}form";

    if ($action == 'dashboard_save') $html .= "<a href=\"javascript:ajax_save(";
    else  $html .= "<a href=\"javascript:get_and_display(";
    $html .= "'ajaxdata.php?action={$action}&dashboard={$dashboard}&did={$dashletid}";
    if (is_array($params))
    {
        foreach ($params AS $pname => $pvalue)
        {
            $html .= "&{$pname}={$pvalue}";
        }
    }
    //$html .= "&editaction=do_add&type={$type}";

    if ($action != 'dashboard_save')
    {
        $html .= "', '{$dashletid}'";
        $html .= ", $refresh";
    }
    else
    {
        $html .= "', '{$formid}'";
    }
    $html .= ");\">{$text}</a>";

    return $html;
}


function dashboard_do($context, $row=0, $dashboardid=0)
{
    global $DASHBOARDCOMP;
    $dashletid = "{$row}-{$dashboardid}";
    $action = $DASHBOARDCOMP[$context];
    if ($action != NULL || $action != '')
    {
        if (function_exists($action)) $action($dashletid);
    }
}


function show_dashboard_component($row, $dashboardid)
{
    global $dbDashboard;
    $sql = "SELECT name FROM `{$dbDashboard}` WHERE enabled = 'true' AND id = '$dashboardid'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) == 1)
    {
        $obj = mysql_fetch_object($result);
        dashboard_do("dashboard_".$obj->name, 'db_'.$row, $dashboardid);
    }
}


/**
    * Recursive function to list links as a tree
    * @author Ivan Lucas
*/
function show_links($origtab, $colref, $level=0, $parentlinktype='', $direction='lr')
{
    global $dbLinkTypes, $dbLinks;
    // Maximum recursion
    $maxrecursions = 15;

    if ($level <= $maxrecursions)
    {
        $sql = "SELECT * FROM `{$dbLinkTypes}` WHERE origtab='$origtab' ";
        if (!empty($parentlinktype)) $sql .= "AND id='{$parentlinktype}'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        while ($linktype = mysql_fetch_object($result))
        {
            // Look up links of this type
            $lsql = "SELECT * FROM `{$dbLinks}` WHERE linktype='{$linktype->id}' ";
            if ($direction=='lr') $lsql .= "AND origcolref='{$colref}'";
            elseif ($direction=='rl') $lsql .= "AND linkcolref='{$colref}'";
            $lresult = mysql_query($lsql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            if (mysql_num_rows($lresult) >= 1)
            {
                if (mysql_num_rows($lresult) >= 1)
                {
                    $html .= "<ul>";
                    $html .= "<li>";
                    while ($link = mysql_fetch_object($lresult))
                    {
                        $recsql = "SELECT {$linktype->selectionsql} AS recordname FROM {$linktype->linktab} WHERE ";
                        if ($direction=='lr') $recsql .= "{$linktype->linkcol}='{$link->linkcolref}' ";
                        elseif ($direction=='rl') $recsql .= "{$linktype->origcol}='{$link->origcolref}' ";
                        $recresult = mysql_query($recsql);
                        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
                        while ($record = mysql_fetch_object($recresult))
                        {
                            if ($link->direction == 'bi')
                            {
                                $html .= "<strong>{$linktype->name}</strong> ";
                            }
                            elseif ($direction == 'lr')
                            {
                                $html .= "<strong>{$linktype->lrname}</strong> ";
                            }
                            elseif ($direction == 'rl')
                            {
                                $html .= "<strong>{$linktype->rlname}</strong> ";
                            }
                            else
                            {
                                $html = $GLOBALS['strError'];
                            }

                            if ($direction == 'lr')
                            {
                                $currentlinkref = $link->linkcolref;
                            }
                            elseif ($direction == 'rl')
                            {
                                $currentlinkref = $link->origcolref;
                            }

                            $viewurl = str_replace('%id%',$currentlinkref,$linktype->viewurl);

                            $html .= "{$currentlinkref}: ";
                            if (!empty($viewurl)) $html .= "<a href='$viewurl'>";
                            $html .= "{$record->recordname}";
                            if (!empty($viewurl)) $html .= "</a>";
                            $html .= " - ".user_realname($link->userid,TRUE);
                            $html .= show_links($linktype->linktab, $currentlinkref, $level+1, $linktype->id, $direction); // Recurse
                            $html .= "</li>\n";
                        }
                    }
                    $html .= "</ul>\n";
                }
                else $html .= "<p>{$GLOBALS['strNone']}</p>";
            }
        }
    }
    else $html .= "<p class='error'>{$GLOBALS['strError']}: Maximum number of {$maxrecursions} recursions reached</p>";
    return $html;
}


function show_create_links($table, $ref)
{
    global $dbLinkTypes;
    $html .= "<p align='center'>{$GLOBALS['strAddLink']}: ";
    $sql = "SELECT * FROM `{$dbLinkTypes}` WHERE origtab='$table' OR linktab='$table' ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $numlinktypes = mysql_num_rows($result);
    $rowcount = 1;
    while ($linktype = mysql_fetch_object($result))
    {
        if ($linktype->origtab == $table AND $linktype->linktab != $table)
        {
            $html .= "<a href='add_link.php?origtab=tasks&amp;origref={$ref}&amp;linktype={$linktype->id}'>{$linktype->lrname}</a>";
        }
        elseif ($linktype->origtab != $table AND $linktype->linktab == $table)
        {
            $html .= "<a href='add_link.php?origtab=tasks&amp;origref={$ref}&amp;linktype={$linktype->id}'>{$linktype->rlname}</a>";
        }
        else
        {
            $html .= "<a href='add_link.php?origtab=tasks&amp;origref={$ref}&amp;linktype={$linktype->id}'>{$linktype->lrname}</a> | ";
            $html .= "<a href='add_link.php?origtab=tasks&amp;origref={$ref}&amp;linktype={$linktype->id}&amp;dir=rl'>{$linktype->rlname}</a>";
        }

        if ($rowcount < $numlinktypes) $html .= " | ";
        $rowcount++;
    }
    $html .= "</p>";
    return $html;
}


/**
    * Create a PNG chart
    * @author Ivan Lucas
    * @param $type string. The type of chart to draw. (e.g. 'pie').
    * @returns a PNG image resource
    * @note Currently only has proper support for pie charts (type='pie')
    * @todo TODO Support for bar and line graphs
*/
function draw_chart_image($type, $width, $height, $data, $legends, $title='', $unit='')
{
    global $CONFIG;
    // Graph settings
    if (empty($width)) $width = 500;
    if (empty($height)) $height = 150;
    $fontfile="{$CONFIG['application_fspath']}FreeSans.ttf";

    if (!empty($fontfile) AND file_exists($fontfile)) $use_ttf = TRUE;
    else $use_ttf = FALSE;

    $countdata = count($data);
    $sumdata = array_sum($data);

    if ($countdata > 8) $height += (($countdata - 8) * 14);

    $img = imagecreatetruecolor($width, $height);

    $white = imagecolorallocate($img, 255, 255, 255);
    $blue = imagecolorallocate($img, 240, 240, 255);
    $midblue = imagecolorallocate($img, 204, 204, 255);
    $darkblue = imagecolorallocate($img, 32, 56, 148);
    $black = imagecolorallocate($img, 0, 0, 0);
    $grey = imagecolorallocate($img, 224, 224, 224);
    $red = imagecolorallocate($img, 255, 0, 0);

    imagefill($img, 0, 0, $white);

    $rgb[] = "190,190,255";
    $rgb[] = "205,255,255";
    $rgb[] = "255,255,156";
    $rgb[] = "156,255,156";
    $rgb[] = "255,205,195";
    $rgb[] = "255,140,255";
    $rgb[] = "100,100,155";
    $rgb[] = "98,153,90";
    $rgb[] = "205,210,230";
    $rgb[] = "192,100,100";
    $rgb[] = "204,204,0";
    $rgb[] = "255,102,102";
    $rgb[] = "0,204,204";
    $rgb[] = "0,255,0";
    $rgb[] = "255,168,88";
    $rgb[] = "128,0,128";
    $rgb[] = "0,153,153";
    $rgb[] = "255,230,204";
    $rgb[] = "128,170,213";
    $rgb[] = "75,75,75";
    // repeats...
    $rgb[] = "190,190,255";
    $rgb[] = "156,255,156";
    $rgb[] = "255,255,156";
    $rgb[] = "205,255,255";
    $rgb[] = "255,205,195";
    $rgb[] = "255,140,255";
    $rgb[] = "100,100,155";
    $rgb[] = "98,153,90";
    $rgb[] = "205,210,230";
    $rgb[] = "192,100,100";
    $rgb[] = "204,204,0";
    $rgb[] = "255,102,102";
    $rgb[] = "0,204,204";
    $rgb[] = "0,255,0";
    $rgb[] = "255,168,88";
    $rgb[] = "128,0,128";
    $rgb[] = "0,153,153";
    $rgb[] = "255,230,204";
    $rgb[] = "128,170,213";
    $rgb[] = "75,75,75";

    switch ($type)
    {
        case 'pie':
            $cx = '120';$cy ='60'; //Set Pie Postition. CenterX,CenterY
            $sx = '200';$sy='100';$sz ='15';// Set Size-dimensions. SizeX,SizeY,SizeZ

            // Title
            if (!empty($title))
            {
                $cy += 10;
                if ($use_ttf) imagettftext($img, 10, 0, 2, 10, $black, $fontfile, $title);
                else imagestring($img,2, 2, ($legendY-1), "{$title}", $black);
            }

            //convert to angles.
            for ($i=0;$i<=$countdata;$i++)
            {
                $angle[$i] = (($data[$i] / $sumdata) * 360);
                $angle_sum[$i] = array_sum($angle);
            }

            $background = imagecolorallocate($img, 255, 255, 255);
            //Random colors.

            for ($i=0;$i<=$countdata;$i++)
            {
                $rgbcolors = explode(',',$rgb[$i]);
                $colors[$i] = imagecolorallocate($img,$rgbcolors[0],$rgbcolors[1],$rgbcolors[2]);
                $colord[$i] = imagecolorallocate($img,($rgbcolors[0]/1.5),($rgbcolors[1]/1.5),($rgbcolors[2]/1.5));
            }

            //3D effect.
            $legendY = 80 - ($countdata * 10);
            if ($legendY < 10) $legendY = 10;
            for ($z=1; $z <= $sz; $z++)
            {
                for ($i=0;$i<$countdata;$i++)
                {
                    imagefilledarc($img,$cx,($cy+$sz)-$z,$sx,$sy,$angle_sum[$i-1],$angle_sum[$i],$colord[$i],IMG_ARC_PIE);
                }

            }
            imagerectangle($img, 250, $legendY-5, 470, $legendY+($countdata*15), $black);
            //Top pie.
            for ($i = 0; $i < $countdata; $i++)
            {
                imagefilledarc($img,$cx,$cy,$sx,$sy,$angle_sum[$i-1] ,$angle_sum[$i], $colors[$i], IMG_ARC_PIE);
                imagefilledrectangle($img, 255, ($legendY+1), 264, ($legendY+9), $colors[$i]);
                // Legend
                if ($unit == 'seconds')
                {
                    $data[$i]=format_seconds($data[$i]);
                }

                if ($use_ttf)
                {
                    imagettftext($img, 8, 0, 270, ($legendY+9), $black, $fontfile, substr(urldecode($legends[$i]),0,27)." ({$data[$i]})");
                }
                else
                {
                    imagestring($img,2, 270, ($legendY-1), substr(urldecode($legends[$i]),0,27)." ({$data[$i]})", $black);
                }
                // imagearc($img,$cx,$cy,$sx,$sy,$angle_sum[$i1] ,$angle_sum[$i], $blue);
                $legendY+=15;
            }
        break;

        case 'line':
            $maxdata = 0;
            $colwidth=round($width/$countdata);
            $rowheight=round($height/10);
            foreach ($data AS $dataval)
            {
                if ($dataval > $maxdata) $maxdata = $dataval;
            }

            imagerectangle($img, $width-1, $height-1, 0, 0, $black);
            for ($i=1; $i<$countdata; $i++)
            {
                imageline($img, $i*$colwidth, 0, $i*$colwidth, $width, $grey);
                imageline($img, 2, $i*$rowheight, $width-2, $i*$rowheight, $grey);
            }

            for ($i=0; $i<$countdata; $i++)
            {
                $dataheight=($height-($data[$i] / $maxdata) * $height);
                $legendheight = $dataheight > ($height - 15) ? $height - 15 : $dataheight;
                $nextdataheight=($height-($data[$i+1] / $maxdata) * $height);
                imageline($img, $i*$colwidth, $dataheight, ($i+1)*$colwidth, $nextdataheight, $red);
                imagestring($img, 3, $i*$colwidth, $legendheight, substr($legends[$i],0,6), $darkblue);
            }
            imagestring($img,3, 10, 10, $title, $red);
        break;

        case 'bar':
            $maxdata = 0;
            $colwidth=round($width/$countdata);
            $rowheight=round($height/10);
            foreach ($data AS $dataval)
            {
                if ($dataval > $maxdata) $maxdata = $dataval;
            }

            imagerectangle($img, $width-1, $height-1, 0, 0, $black);
            for ($i=1; $i<$countdata; $i++)
            {
                imageline($img, $i*$colwidth, 0, $i*$colwidth, $width, $grey);
                imageline($img, 2, $i*$rowheight, $width-2, $i*$rowheight, $grey);
            }

            for ($i=0; $i<$countdata; $i++)
            {
                $dataheight=($height-($data[$i] / $maxdata) * $height);
                $legendheight = $dataheight > ($height - 15) ? $height - 15 : $dataheight;
                imagefilledrectangle($img, $i*$colwidth, $dataheight, ($i+1)*$colwidth, $height, $darkblue);
                imagefilledrectangle($img, ($i*$colwidth)+1, $dataheight+1, (($i+1)*$colwidth)-3, ($height-2), $midblue);
                imagestring($img, 3, ($i*$colwidth)+4, $legendheight, substr($legends[$i],0,5), $darkblue);
            }
            imagestring($img,3, 10, 10, $title, $red);
        break;


        default:
            imagerectangle($img, $width-1, $height-1, 1, 1, $red);
            imagestring($img,3, 10, 10, "Invalid chart type", $red);
    }

    // Return a PNG image
    return $img;
}


/**
    * @author Ivan Lucas
*/
function get_tag_id($tag)
{
    global $dbTags;
    $sql = "SELECT tagid FROM `{$dbTags}` WHERE name = LOWER('$tag')";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    if (mysql_num_rows($result) == 1)
    {
        $id = mysql_fetch_row($result);
        return $id[0];
    }
    else
    {
        //need to add
        $sql = "INSERT INTO `{$dbTags}` (name) VALUES (LOWER('$tag'))";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        return mysql_insert_id();
    }
}


/**
    * @author Ivan Lucas
*/
function add_tag($id, $type, $tag)
{
    global $dbSetTags;
    /*
    TAG TYPES
    1 - contact
    2 - incident
    3 - Site
    4 - task
    5 - product
    6 - skill
    7 - kb article
    8 - report
    */
    if ($tag!='')
    {
        $tagid = get_tag_id($tag);
        // Ignore errors, die silently
        $sql = "INSERT INTO `{$dbSetTags}` VALUES ('$id', '$type', '$tagid')";
        $result = @mysql_query($sql);
    }
    return true;
}


/**
    * @author Ivan Lucas
*/
function remove_tag($id, $type, $tag)
{
    global $dbSetTags, $dbTags;
    if ($tag != '')
    {
        $tagid = get_tag_id($tag);
        // Ignore errors, die silently
        $sql = "DELETE FROM `{$dbSetTags}` WHERE id = '$id' AND type = '$type' AND tagid = '$tagid'";
        $result = @mysql_query($sql);

        // Check tag usage count and remove disused tags completely
        $sql = "SELECT COUNT(id) FROM `{$dbSetTags}` WHERE tagid = '$tagid'";
        $result = mysql_query($sql);
        list($count) = mysql_fetch_row($result);
        if ($count == 0)
        {
            $sql = "DELETE FROM `{$dbTags}` WHERE tagid = '$tagid' LIMIT 1";
            @mysql_query($sql);
        }
        purge_tag($tagid);
    }
    return true;
}


/**
    * Remove existing tags and replace with a new set
    * @author Ivan Lucas
*/
function replace_tags($type, $id, $tagstring)
{
    global $dbSetTags;
    // first remove old tags
    $sql = "DELETE FROM `{$dbSetTags}` WHERE id = '$id' AND type = '$type'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    // Change seperators to spaces
    $seperators = array(', ',';',',');
    $tags = str_replace($seperators, ' ', trim($tagstring));
    $tag_array = explode(" ", $tags);
    foreach ($tag_array AS $tag)
    {
        add_tag($id, $type, trim($tag));
    }
}

/**
    * Purge a single tag (if needed)
    * @author Ivan Lucas
*/
function purge_tag($tagid)
{
    // Check tag usage count and remove disused tag completely
    global $dbSetTags, $dbTags;
    $sql = "SELECT COUNT(id) FROM `{$dbSetTags}` WHERE tagid = '$tagid'";
    $result = mysql_query($sql);
    list($count) = mysql_fetch_row($result);
    if ($count == 0)
    {
        $sql = "DELETE FROM `{$dbTags}` WHERE tagid = '$tagid' LIMIT 1";
        @mysql_query($sql);
    }
}


/**
    * Purge all tags (if needed)
    * @author Ivan Lucas
*/
function purge_tags()
{
    global $dbTags;
    $sql = "SELECT tagid FROM `{$dbTags}`";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    if (mysql_num_rows($result) > 0)
    {
        while ($tag = mysql_fetch_object($result))
        {
            purge_tag($tag->tagid);
        }
    }
}


/**
    * Produce a list of tags
    * @author Ivan Lucas
    * @param $html boolean. Return HTML when TRUE
*/
function list_tags($recordid, $type, $html = TRUE)
{
    global $CONFIG, $dbSetTags, $dbTags, $iconset;

    $sql = "SELECT t.name, t.tagid FROM `{$dbSetTags}` AS s, `{$dbTags}` AS t WHERE s.tagid = t.tagid AND ";
    $sql .= "s.type = '$type' AND s.id = '$recordid'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $numtags = mysql_num_rows($result);

    if ($html AND $numtags > 0)
    {
        $str .= "<div class='taglist'>";
    }

    $count = 1;
    while ($tags = mysql_fetch_object($result))
    {
        if ($html)
        {
            $str .= "<a href='view_tags.php?tagid={$tags->tagid}'>".$tags->name;
            if (array_key_exists($tags->name, $CONFIG['tag_icons']))
            {
                $str .= "&nbsp;<img src='images/icons/{$iconset}/16x16/{$CONFIG['tag_icons'][$tags->name]}.png' alt='' />";
            }
            $str .= "</a>";
        }
        else
        {
            $str .= $tags->name;
        }

        if ($count < $numtags) $str .= ", ";
        if ($html AND !($count%5)) $str .= "<br />\n";
        $count++;
    }
    if ($html AND $numtags > 0) $str .= "</div>";
    return trim($str);
}


/**
    * Return HTML to display a list of tag icons
    * @author Ivan Lucas
    * @returns string. HTML
*/
function list_tag_icons($recordid, $type)
{
    global $CONFIG, $dbSetTags, $dbTags;
    $sql = "SELECT t.name, t.tagid ";
    $sql .= "FROM `{$dbSetTags}` AS st, `{$dbTags}` AS t WHERE st.tagid = t.tagid AND ";
    $sql .= "st.type = '$type' AND st.id = '$recordid' AND (";
    $counticons = count($CONFIG['tag_icons']);
    $count = 1;
    foreach ($CONFIG['tag_icons'] AS $icon)
    {
        $sql .= "t.name = '{$icon}'";
        if ($count < $counticons) $sql .= " OR ";
        $count++;
    }
    $sql .= ")";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    $numtags = mysql_num_rows($result);
    if ($numtags > 0)
    {
        while ($tags = mysql_fetch_object($result))
        {
            $str .= "<a href='view_tags.php?tagid={$tags->tagid}' title='{$tags->name}'>";
            $str .= "<img src='images/icons/{$iconset}/16x16/{$CONFIG['tag_icons'][$tags->name]}.png' alt='{$tags->name}' />";
            $str .= "</a> ";
        }
    }
    return $str;
}

/**
    * Generate a tag cloud
    * @author Ivan Lucas, Tom Gerrard
    * @returns string. HTML
*/
function show_tag_cloud($orderby="name", $showcount = FALSE)
{
    global $CONFIG, $dbTags, $dbSetTags, $iconset;

    // First purge any disused tags
    purge_tags();
    $sql = "SELECT COUNT(name) AS occurrences, name, t.tagid FROM `{$dbTags}` AS t, `{$dbSetTags}` AS st WHERE t.tagid = st.tagid GROUP BY name ORDER BY $orderby";
    if ($orderby == "occurrences") $sql .= " DESC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $countsql = "SELECT COUNT(id) AS counted FROM `{$dbSetTags}` GROUP BY tagid ORDER BY counted DESC LIMIT 1";
    $countresult = mysql_query($countsql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($max) = mysql_fetch_row($countresult);

    $countsql = "SELECT COUNT(id) AS counted FROM `{$dbSetTags}` GROUP BY tagid ORDER BY counted ASC LIMIT 1";
    $countresult = mysql_query($countsql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($min) = mysql_fetch_row($countresult);
    unset($countsql, $countresult);

    if (substr($_SERVER['SCRIPT_NAME'],-8) != "main.php")
    {
        //not in the dashbaord
        $html .= "<p align='center'>{$GLOBALS['strSort']}: <a href='view_tags.php?orderby=name'>{$GLOBALS['strAlphabetically']}</a> | ";
        $html .= "<a href='view_tags.php?orderby=occurrences'>{$GLOBALS['strPopularity']}</a></p>";
    }

    if (mysql_num_rows($result) > 0)
    {
        $html .= "<table align='center'><tr><td class='tagcloud'>";
        while ($obj = mysql_fetch_object($result))
        {
            $size = round(log($obj->occurrences * 100) * 32);
            if ($size == 0) $size = 100;
            if ($size > 0 AND $size <= 100) $taglevel = 'taglevel1';
            if ($size > 100 AND $size <= 150) $taglevel = 'taglevel2';
            if ($size > 150 AND $size <= 200) $taglevel = 'taglevel3';
            if ($size > 200) $taglevel = 'taglevel4';
            $html .= "<a href='view_tags.php?tagid=$obj->tagid' class='$taglevel' style='font-size: {$size}%; font-weight: normal;' title='{$obj->occurrences}'>";
            if (array_key_exists($obj->name, $CONFIG['tag_icons']))
            {
                $html .= "{$obj->name}&nbsp;<img src='images/icons/{$iconset}/";
                if ($size <= 200)
                {
                    $html .= "16x16";
                }
                else
                {
                    $html .= "32x32";
                }
                $html .= "/{$CONFIG['tag_icons'][$obj->name]}.png' alt='' />";
            }
            else $html .= $obj->name;
            $html .= "</a>";
            if ($showcount) $html .= "({$obj->occurrences})";
            $html .= " \n";//&nbsp;\n";
        }
        $html .= "</td></tr></table>";
    }
    else $html .= "<p align='center'>{$GLOBALS['strNothingToDisplay']}</p>";
    return $html;
}


/**
    * @author Paul Heaney
*/
function display_drafts($type, $result)
{
    global $iconset;
    global $id;
    global $CONFIG;

    if ($type == 'update')
    {
        $page = "update_incident.php";
        $editurlspecific = '';
    }
    else if ($type == 'email')
    {
        $page = "email_incident.php";
        $editurlspecific = "&amp;step=2";
    }

    echo "<p align='center'>{$GLOBALS['strDraftChoose']}</p>";

    $html = '';

    while ($obj = mysql_fetch_object($result))
    {
        $html .= "<div class='detailhead'>";
        $html .= "<div class='detaildate'>".date($CONFIG['dateformat_datetime'], $obj->lastupdate);
        $html .= "</div>";
        $html .= "<a href='{$page}?action=editdraft&amp;draftid={$obj->id}&amp;id={$id}{$editurlspecific}' class='info'>";
        $html .= icon('edit', 16, $GLOBALS['strDraftEdit'])."</a>";
        $html .= "<a href='{$page}?action=deletedraft&amp;draftid={$obj->id}&amp;id={$id}' class='info'>";
        $html .= icon('delete', 16, $GLOBALS['strDraftDelete'])."</a>";
        $html .= "</div>";
        $html .= "<div class='detailentry'>";
        $html .= nl2br($obj->content)."</div>";
    }

    return $html;
}


function ansort($x,$var,$cmp='strcasecmp')
{
    // Numeric descending sort of multi array
    if ( is_string($var) ) $var = "'$var'";

    if ($cmp=='numeric')
    {
        uasort($x, create_function('$a,$b', 'return '.'( $a['.$var.'] < $b['.$var.']);'));
    }
    else
    {
        uasort($x, create_function('$a,$b', 'return '.$cmp.'( $a['.$var.'],$b['.$var.']);'));
    }
    return $x;
}


function array_remove_duplicate($array, $field)
{
    foreach ($array as $sub)
    {
        $cmp[] = $sub[$field];
    }

    $unique = array_unique($cmp);
    foreach ($unique as $k => $rien)
    {
        $new[] = $array[$k];
    }
    return $new;
}


// This function doesn't exist for PHP4 so use this instead
if (!function_exists("stripos"))
{
function stripos($str,$needle,$offset=0)
{
    return strpos(strtolower($str),strtolower($needle),$offset);
}
}


function array_multi_search($needle, $haystack, $searchkey)
{
    foreach ($haystack AS $thekey => $thevalue)
    {
        if ($thevalue[$searchkey] == $needle) return $thekey;
    }
    return FALSE;
}


function string_find_all($haystack, $needle, $limit=0)
{
    $positions = array();
    $currentoffset = 0;

	$offset = 0;
    $count = 0;
    while (($pos = stripos($haystack, $needle, $offset)) !== false && ($count < $limit || $limit == 0))
    {
        $positions[] = $pos;
        $offset = $pos + strlen($needle);
        $count++;
    }
    return $positions;
}


// Implode assocative array
function implode_assoc($glue1, $glue2, $array)
{
    foreach ($array as $key => $val)
    {
        $array2[] = $key.$glue1.$val;
    }

    return implode($glue2, $array2);
}


/**
    * @author Kieran Hogg
    * @param $name string. name of the html entity
    * @param $time string. the time to set it to, format 12:34
    * @returns string. HTML
*/
function time_dropdown($name, $time='')
{
    if ($time)
    {
        $time = explode(':', $time);
    }

    $html = "<select name='$name'>\n";
    $html .= "<option></option>";
    for ($hours = 0; $hours < 24; $hours++)
    {
        for ($mins = 0; $mins < 60; $mins+=15)
        {
            $hours = str_pad($hours, 2, "0", STR_PAD_LEFT);
            $mins = str_pad($mins, 2, "0", STR_PAD_RIGHT);

            if ($time AND $time[0] == $hours AND $time[1] == $mins)
            {
                $html .= "<option selected='selected' value='$hours:$mins'>$hours:$mins</option>";
            }
            else
            {
                if ($time AND $time[0] == $hours AND $time[1] < $mins AND $time[1] > ($mins - 15))
                {
                    $html .= "<option selected='selected' value='$time[0]:$time[1]'>$time[0]:$time[1]</option>\n";
                }
                else
                {
                    $html .= "<option value='$hours:$mins'>$hours:$mins</option>\n";
                }
            }
        }
    }
    $html .= "</select>";
    return $html;
}


/**
    * @author Kieran Hogg
    * @param $seconds Int. Number of seconds
    * @returns string. Readable time in seconds
*/
function exact_seconds($seconds)
{
    $days = floor($seconds / (24 * 60 * 60));
    $seconds -= $days * (24 * 60 * 60);
    $hours = floor($seconds / (60 * 60));
    $seconds -=  $hours * (60 * 60);
    $minutes = floor($seconds / 60);
    $seconds -= $minutes * 60;

    $string = "";
    if ($days != 0) $string .= "{$days} {$GLOBALS['strDays']}, ";
    if ($hours != 0) $string .= "{$hours} {$GLOBALS['strHours']}, ";
    if ($minutes != 0) $string .= "{$minutes} {$GLOBALS['strMinutes']}, ";
    $string .= "{$seconds} {$GLOBALS['strSeconds']}";

    return $string;
}


/**
    * An icon showing a users online status
    * @author Kieran Hogg
    * @param $user The user ID of the user to check
    * @returns string. HTML of a 16x16 status icon.
*/
function user_online_icon($user)
{
    global $iconset, $now, $dbUsers, $strOffline, $strOnline;
    $sql = "SELECT lastseen FROM `{$dbUsers}` WHERE id={$user}";
    $result = mysql_query($sql);
    $users = mysql_fetch_object($result);
    if (($now - mysql2date($users->lastseen) < (60 * 30)))
    {
        return icon('online', 16);
    }
    else
    {
        return icon('offline', 16);
    }
}


/**
    * Returns users online status
    * @author Kieran Hogg
    * @param $user The user ID of the user to check
    * @returns boolean. TRUE if online, FALSE if not
*/
function user_online($user)
{
    global $iconset, $now, $dbUsers;
    $sql = "SELECT lastseen FROM `{$dbUsers}` WHERE id={$user}";
    $result = mysql_query($sql);
    $users = mysql_fetch_object($result);
    if (($now - mysql2date($users->lastseen) < (60 * 30)))
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
    * Shows errors from a form, if any
    * @author Kieran Hogg
    * @returns string. HTML of the form errors stored in the users session
*/
function show_form_errors($formname)
{
    if ($_SESSION['formerrors'][$formname])
    {
        foreach ($_SESSION['formerrors'][$formname] as $error)
        {
            $html .= "<p class='error'>$error</p>";
        }
    }
    return $html;
}


/**
    * Cleans form errors
    * @author Kieran Hogg
    * @returns nothing
*/
function clear_form_errors($formname)
{
    unset($_SESSION['formerrors'][$formname]);
}


/**
    * Cleans form data
    * @author Kieran Hogg
    * @returns nothing
*/
function clear_form_data($formname)
{
    unset($_SESSION['formdata'][$formname]);
}

/**
    * Trims a string so that it is not longer than the length given and
    * add ellipses (...) to the end
    * @author Ivan Lucas
    * @param $text string. Some plain text to shorten
    * @param $maxlength int. Length of the resulting string (in characters)
    * @param $html bool. Set to TRUE to include HTML in the output (for ellipses)
    *                    Set to FALSE for plain text only
    * @returns string. A shortned string (optionally with html)
*/
function truncate_string($text, $maxlength=255, $html = TRUE)
{

    if (strlen($text) > $maxlength)
    {
        // Leave space for ellipses
        if ($html == TRUE)
        {
            $maxlength -= 1;
        }
        else
        {
            $maxlength -= 3;
        }

        $text = utf8_encode(wordwrap(utf8_decode($text), $maxlength, '^\CUT/^', 1));
        $parts = explode('^\CUT/^', $text);
        $text = $parts[0];

        if ($html == TRUE)
        {
            $text .= '&hellip;';
        }
        else
        {
            $text .= '...';
        }
    }
    return $text;
}


/**
    * Returns a localised and translated date
    * @author Ivan Lucas
    * @param $format string. date() format
    * @param $date int. UNIX timestamp.  Uses 'now' if ommitted
    * @param $utc bool. Is the timestamp being passed as UTC or system time
                        TRUE = passed as UTC
                        FALSE = passed as system time
    * @returns string. An internationised date/time string
    * @todo  th/st and am/pm maybe?
*/
function ldate($format, $date = '', $utc = TRUE)
{
    if ($date == '') $date = $GLOBALS['now'];
    if ($_SESSION['utcoffset'] != '')
    {
        if (!$utc)
        {
            // Adjust the date back to UTC
            $tz = strftime('%z', $date);
            $tzmins = (substr($tz, -4, 2) * 60) + substr($tz, -2, 2);
            $tzsecs = $tzmins * 60; // convert to seconds
            if (substr($tz, 0, 1) == '+') $date -= $tzsecs;
            else $date += $tzsecs;
        }
        // Adjust the display time to the users local timezone
        $useroffsetsec = $_SESSION['utcoffset'] * 60;
        $date += $useroffsetsec;
    }
    $datestring = gmdate($format, $date);

    // Internationalise date endings (e.g. st)
    if (strpos($format, 'S') !== FALSE)
    {
        $endings = array('st', 'nd', 'rd', 'th');
        $i18nendings = array($GLOBALS['strst'], $GLOBALS['strnd'],
                            $GLOBALS['strrd'], $GLOBALS['strth']);
        $datestring = str_replace($endings, $i18nendings, $datestring);
    }


    // Internationalise full day names
    if (strpos($format, 'l') !== FALSE)
    {
        $days = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
        $i18ndays = array($GLOBALS['strMonday'], $GLOBALS['strTuesday'], $GLOBALS['strWednesday'],
                        $GLOBALS['strThursday'], $GLOBALS['strFriday'], $GLOBALS['strSaturday'], $GLOBALS['strSunday']);
        $datestring = str_replace($days, $i18ndays, $datestring);
    }

    // Internationalise abbreviated day names
    if (strpos($format, 'D') !== FALSE)
    {
        $days = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
        $i18ndays = array($GLOBALS['strMon'], $GLOBALS['strTue'], $GLOBALS['strWed'],
                        $GLOBALS['strThu'], $GLOBALS['strFri'], $GLOBALS['strSat'], $GLOBALS['strSun']);
        $datestring = str_replace($days, $i18ndays, $datestring);
    }

    // Internationalise full month names
    if (strpos($format, 'F') !== FALSE)
    {
        $months = array('January','February','March','April','May','June','July','August','September','October','November','December');
        $i18nmonths = array($GLOBALS['strJanuary'], $GLOBALS['strFebruary'], $GLOBALS['strMarch'],
                        $GLOBALS['strApril'], $GLOBALS['strMay'], $GLOBALS['strJune'], $GLOBALS['strJuly'],
                        $GLOBALS['strAugust'], $GLOBALS['strSeptember'], $GLOBALS['strOctober'],
                        $GLOBALS['strNovember'], $GLOBALS['strDecember']);
        $datestring = str_replace($months, $i18nmonths, $datestring);
    }

    // Internationalise short month names
    if (strpos($format, 'M') !== FALSE)
    {
        $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $i18nmonths = array($GLOBALS['strJanAbbr'], $GLOBALS['strFebAbbr'], $GLOBALS['strMarAbbr'],
                        $GLOBALS['strAprAbbr'], $GLOBALS['strMayAbbr'], $GLOBALS['strJunAbbr'], $GLOBALS['strJulAbbr'],
                        $GLOBALS['strAugAbbr'], $GLOBALS['strSepAbbr'], $GLOBALS['strOctAbbr'],
                        $GLOBALS['strNovAbbr'], $GLOBALS['strDecAbbr']);
        $datestring = str_replace($months, $i18nmonths, $datestring);
    }

    // Internationalise am/pm
    if (strpos($format, 'a') !== FALSE)
    {
        $months = array('am','pm');
        $i18nmonths = array($GLOBALS['strAM'], $GLOBALS['strPM']);
        $datestring = str_replace($months, $i18nmonths, $datestring);
    }

    return $datestring;
}

/**
    * Returns the number of open activities/timed tasks for an incident
    * @author Paul Heaney
    * @param $incidentid int. Incident ID you want
    * @returns int. Number of open activities for the incident (0 if non)
*/
function open_activities_for_incident($incientid)
{
    global $dbLinks, $dbLinkTypes, $dbTasks;
    // Running Activities

    $sql = "SELECT DISTINCT origcolref, linkcolref ";
    $sql .= "FROM `{$dbLinks}` AS l, `{$dbLinkTypes}` AS lt ";
    $sql .= "WHERE l.linktype=4 ";
    $sql .= "AND linkcolref={$incientid} ";
    $sql .= "AND direction='left'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) > 0)
    {
        //get list of tasks
        $sql = "SELECT * FROM `{$dbTasks}` WHERE enddate IS NULL ";
        while ($tasks = mysql_fetch_object($result))
        {
            if (empty($orSQL)) $orSQL = "(";
            else $orSQL .= " OR ";
            $orSQL .= "id={$tasks->origcolref} ";
        }

        if (!empty($orSQL))
        {
            $sql .= "AND {$orSQL})";
        }
        $result = mysql_query($sql);

        $num = mysql_num_rows($result);
    }
    else
    {
        $num = 0;
    }

    return $num;
}


/**
    * Returns the number of open activities/timed tasks for a site
    * @author Paul Heaney
    * @param $siteid int. Site ID you want
    * @returns int. Number of open activities for the site (0 if non)
*/
function open_activities_for_site($siteid)
{
    global $dbIncidents, $dbContacts;

    $openactivites = 0;

    if (!empty($siteid) AND $siteid != 0)
    {
        $sql = "SELECT i.id FROM `{$dbIncidents}` AS i, `{$dbContacts}` AS c ";
        $sql .= "WHERE i.contact = c.id AND ";
        $sql .= "c.siteid = {$siteid} AND ";
        $sql .= "(i.status != 2 AND i.status != 7)";

        $result = mysql_query($sql);

        while ($obj = mysql_fetch_object($result))
        {
            $openactivites += open_activities_for_incident($obj->id);
        }
    }

    return $openactivites;
}


function mark_task_completed($taskid, $incident)
{
    global $dbNotes, $dbTasks;
    if (!$incident)
    {
        // Insert note to say what happened
        $bodytext = "Task marked 100% complete by {$_SESSION['realname']}:\n\n".$bodytext;
        $sql = "INSERT INTO `{$dbNotes}` ";
        $sql .= "(userid, bodytext, link, refid) ";
        $sql .= "VALUES ('0', '{$bodytext}', '10', '{$taskid}')";
        mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    }

    $enddate = date('Y-m-d H:i:s');
    $sql = "UPDATE `{$dbTasks}` ";
    $sql .= "SET completion='100', enddate='$enddate' ";
    $sql .= "WHERE id='$taskid' LIMIT 1";
    mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
}

/**
    * Finds out which scheduled tasks should be run right now
    * @author Ivan Lucas, Paul Heaney
**/
function schedule_actions_due()
{
    global $now;
    global $dbScheduler;

    $actions = FALSE;
    $sql = "SELECT * FROM `{$dbScheduler}` WHERE status = 'enabled' AND type = 'interval' ";
    $sql .= "AND UNIX_TIMESTAMP(start) <= $now AND (UNIX_TIMESTAMP(end) >= $now OR UNIX_TIMESTAMP(end) = 0) ";
    $sql .= "AND IF(UNIX_TIMESTAMP(lastran) > 0, UNIX_TIMESTAMP(lastran) + `interval` < $now, UNIX_TIMESTAMP(NOW())) ";
    $sql .= "AND laststarted <= lastran";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    if (mysql_num_rows($result) > 0)
    {
        while ($action = mysql_fetch_object($result))
        {
            $actions[$action->action] = $actions->params;
        }
    }

    // Month
    $sql = "SELECT * FROM `{$dbScheduler}` WHERE status = 'enabled' AND type = 'date' ";
    $sql .= "AND UNIX_TIMESTAMP(start) <= $now AND (UNIX_TIMESTAMP(end) >= $now OR UNIX_TIMESTAMP(end) = 0) ";
    $sql .= "AND ((date_type = 'month' AND (DAYOFMONTH(CURDATE()) > date_offset OR (DAYOFMONTH(CURDATE()) = date_offset AND CURTIME() >= date_time)) ";
    $sql .= "AND DATE_FORMAT(CURDATE(), '%Y-%m') != DATE_FORMAT(lastran, '%Y-%m') ) ) ";  // not run this month
    $sql .= "AND laststarted <= lastran";
    //$sql .= "OR ";
    //$sql .= "(date_type = 'year'))";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    if (mysql_num_rows($result) > 0)
    {
        while ($action = mysql_fetch_object($result))
        {
            $actions[$action->action] = $actions->params;
        }
    }

    // Year TODO CHECK
    $sql = "SELECT * FROM `{$dbScheduler}` WHERE status = 'enabled' AND type = 'date' ";
    $sql .= "AND UNIX_TIMESTAMP(start) <= $now AND (UNIX_TIMESTAMP(end) >= $now OR UNIX_TIMESTAMP(end) = 0) ";
    $sql .= "AND ((date_type = 'year' AND (DAYOFYEAR(CURDATE()) > date_offset OR (DAYOFYEAR(CURDATE()) = date_offset AND CURTIME() >= date_time)) ";
    $sql .= "AND DATE_FORMAT(CURDATE(), '%Y') != DATE_FORMAT(lastran, '%Y') ) ) ";  // not run this year
    $sql .= "AND laststarted <= lastran";
    //$sql .= "OR ";
    //$sql .= "(date_type = 'year'))";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    if (mysql_num_rows($result) > 0)
    {
        while ($action = mysql_fetch_object($result))
        {
            $actions[$action->action] = $actions->params;
        }
    }


    return $actions;
}


/**
 * Marks a schedule action as started
 * @author Paul Heaney
 * @param $action string Name of scheduled action
 * @return boolean Success of update
 */
function schedule_action_started($action)
{
    global $now;
    
    $nowdate = date('Y-m-d H:i:s', $now);
    
    $sql = "UPDATE `{$GLOBALS['dbScheduler']}` SET laststarted = '$nowdate' ";
    $sql .= "WHERE action = '{$action}'";
    mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_ERROR);
        return FALSE;
    }
    if (mysql_affected_rows() > 0) return TRUE;
    else return FALSE;
}

/**
    * Mark a schedule action as done
    * @author Ivan Lucas
    * @param $doneaction string. Name of scheduled action
    * @param $success bool. Was the run successful, TRUE = Yes, FALSE = No
**/
function schedule_action_done($doneaction, $success = TRUE)
{
    global $now;
    global $dbScheduler;

    $nowdate = date('Y-m-d H:i:s', $now);
    $sql = "UPDATE `{$dbScheduler}` SET lastran = '$nowdate' ";
    if ($success == FALSE) $sql .= ", success=0, status='disabled' ";
    else $sql .= ", success=1 ";
    $sql .= "WHERE action = '{$doneaction}'";
    mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_ERROR);
        return FALSE;
    }
    if (mysql_affected_rows() > 0) return TRUE;
    else return FALSE;
}


/**
* Make a billing array for a incident
* @author Paul Heaney
* @param $incidentid - Incident number of the incident to create the array from
* @todo Can this be merged into make_incident_billing_array? Does it serve any purpose on its own?
**/
function get_incident_billing_details($incidentid)
{
    global $dbUpdates;
    /*
     $array[owner][] = array(owner, starttime, duration)
     */
    $sql = "SELECT * FROM `{$dbUpdates}` WHERE incidentid = {$incidentid} AND duration IS NOT NULL";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        return FALSE;
    }

    if (mysql_num_rows($result) > 0)
    {
        while($obj = mysql_fetch_object($result))
        {
            if ($obj->duration > 0)
            {
                $temparray['owner'] = $obj->userid;
                $temparray['starttime'] = ($obj->timestamp-$obj->duration);
                $temparray['duration'] = $obj->duration;
                $billing[$obj->userid][] = $temparray;
            }
            else
            {
                if (empty($billing['refunds'])) $billing['refunds'] = 0;
                $billing['refunds'] += $obj->duration;
            }
        }
    }

    return $billing;
}


/**
* TODO
* @author Paul Heaney
* @param $count TODO
* @param $countType TODO
* @param $activity TODO
* @param $period TODO
* @return TODO
**/
function group_billing_periods(&$count, $countType, $activity, $period)
{
    $duration = $activity['duration'];
    $startTime = $activity['starttime'];

    if (!empty($count[$countType]))
    {
        while ($duration > 0)
        {
            $saved = "false";
            foreach ($count[$countType] AS $ind)
            {
                /*
                echo "<pre>";
                print_r($ind);
                echo "</pre>";
                */
                //echo "IN:{$ind}:START:{$act['starttime']}:ENG:{$engineerPeriod}<br />";

                if($ind <= $activity['starttime'] AND $ind <= ($activity['starttime'] + $period))
                {
                    //echo "IND:{$ind}:START:{$act['starttime']}<br />";
                    // already have something which starts in this period just need to check it fits in the period
                    if($ind + $period > $activity['starttime'] + $duration)
                    {
                        $remainderInPeriod = ($ind + $period) - $activity['starttime'];
                        $duration -= $remainderInPeriod;

                        $saved = "true";
                    }
                }
            }
            //echo "Saved: {$saved}<br />";
            if ($saved == "false" AND $activity['duration'] > 0)
            {
                //echo "BB:".$activity['starttime'].":SAVED:{$saved}:DUR:{$activity['duration']}<br />";
                // need to add a new block
                $count[$countType][$startTime] = $startTime;

                $startTime += $period;

                $duration -= $period;
            }
        }
    }
    else
    {
        $count[$countType][$activity['starttime']] = $activity['starttime'];
        $localDur = $activity['duration'] - $period;

        while ($localDur > 0)
        {
            $startTime += $period;
            $count[$countType][$startTime] = $startTime;
            $localDur -= $period; // was just -
        }
    }
}

/**
  * @author Paul Heaney
  * @note  based on periods
 */
function make_incident_billing_array($incidentid, $totals=TRUE)
{
    $billing = get_incident_billing_details($incidentid);

//echo "<pre>";
//print_r($billing);
//echo "</pre><hr />";

    $sql = "SELECT servicelevel, priority FROM `{$GLOBALS['dbIncidents']}` WHERE id = {$incidentid}";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        return FALSE;
    }

    $incident = mysql_fetch_object($result);
    $servicelevel_tag = $incident->servicelevel;
    $priority = $incident->priority;

    if (!empty($billing))
    {
        $billingSQL = "SELECT * FROM `{$GLOBALS['dbBillingPeriods']}` WHERE tag='{$servicelevel_tag}' AND priority='{$priority}'";

        /*
        echo "<pre>";
        print_r($billing);
        echo "</pre>";

        echo "<pre>";
        print_r(make_billing_array($incidentid));
        echo "</pre>";
        */

        //echo $billingSQL;

        $billingresult = mysql_query($billingSQL);
        // echo $billingSQL;
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        $billingObj = mysql_fetch_object($billingresult);

        unset($billingresult);

        $engineerPeriod = $billingObj->engineerperiod * 60;  //to seconds
        $customerPeriod = $billingObj->customerperiod * 60;

        if (empty($engineerPeriod) OR $engineerPeriod == 0) $engineerPeriod = 3600;
        if (empty($customerPeriod) OR $customerPeriod == 0) $customerPeriod = 3600;

        /*
        echo "<pre>";
        print_r($billing);
        echo "</pre>";
        */

        foreach ($billing AS $engineer)
        {
            /*
                [eng][starttime]
            */

            if (is_array($engineer))
            {
                $owner = "";
                $duration = 0;

                unset($count);

                $count['engineer'];
                $count['customer'];

                foreach ($engineer AS $activity)
                {
                    $owner = user_realname($activity['owner']);
                    $duration += $activity['duration'];

                    /*
                    echo "<pre>";
                    print_r($count);
                    echo "</pre>";
                    */

                    group_billing_periods($count, 'engineer', $activity, $engineerPeriod);

                    // Optimisation no need to compute again if we already have the details
                    if ($engineerPeriod != $customerPeriod)
                    {
                        group_billing_periods($count, 'customer', $activity, $customerPeriod);
                    }
                    else
                    {
                        $count['customer'] = $count['engineer'];
                    }
                }

                $tduration += $duration;
                $totalengineerperiods += sizeof($count['engineer']);
                $totalcustomerperiods += sizeof($count['customer']);
                /*
                echo "<pre>";
                print_r($count);
                echo "</pre>";
                */

                $billing_a[$activity['owner']]['owner'] = $owner;
                $billing_a[$activity['owner']]['duration'] = $duration;
                $billing_a[$activity['owner']]['engineerperiods'] = $count['engineer'];
                $billing_a[$activity['owner']]['customerperiods'] = $count['customer'];
            }

            if ($totals == TRUE)
            {
                if (empty($totalengineerperiods)) $totalengineerperiods = 0;
                if (empty($totalcustomerperiods)) $totalcustomerperiods = 0;
                if (empty($tduration)) $tduration = 0;

                $billing_a[-1]['totalduration'] = $tduration;
                $billing_a[-1]['totalengineerperiods'] = $totalengineerperiods;
                $billing_a[-1]['totalcustomerperiods'] = $totalcustomerperiods;
                $billing_a[-1]['customerperiod'] = $customerPeriod;
                $billing_a[-1]['engineerperiod'] = $engineerPeriod;
            }

            if (!empty($billing['refunds'])) $billing_a[-1]['refunds'] = $billing['refunds']/$customerPeriod; // return refunds as a number of units

        }

    }

//echo "<pre>";
//print_r($billing_a);
//echo "</pre>";

    return $billing_a;
}

/**
 *Function to make an array with the number of units at each billable multiplier, broken down by engineer
 * @author Paul Heaney
 *
 */
function get_incident_billable_breakdown_array($incidentid)
{
    $billable = make_incident_billing_array($incidentid, FALSE);

    //echo "<pre>";
    //print_r($billable);
    //echo "</pre>";

    if (!empty($billable))
    {

        foreach ($billable AS $engineer)
        {
            if (is_array($engineer) AND empty($engineer['refunds']))
            {
                $engineerName = $engineer['owner'];
                foreach ($engineer['customerperiods'] AS $period)
                {
                    // $period is the start time
                    $day = date('D', $period);
                    $hour = date('H', $period);

                    $dayNumber = date('d', $period);
                    $month = date('n', $period);
                    $year = date('Y', $period);
                    // echo "DAY {$day} HOUR {$hour}";

                    $dayofweek = strtolower($day);

                    if (is_day_bank_holiday($dayNumber, $month, $year))
                    {
                        $dayofweek = "holiday";
                    }

                    $multiplier = get_billable_multiplier($dayofweek, $hour, 1); //FIXME make this not hard coded

                    $billing[$engineerName]['owner'] = $engineerName;
                    $billing[$engineerName][$multiplier]['multiplier'] = $multiplier;
                    if (empty($billing[$engineerName][$multiplier]['count']))
                    {
                        $billing[$engineerName][$multiplier]['count'] = 0;
                    }

                    $billing[$engineerName][$multiplier]['count']++;
                }
            }
        }

        if (!empty($billable[-1]['refunds'])) $billing['refunds'] = $billable[-1]['refunds'];

    }

    return $billing;
}

/**
* TODO
* NOTE: The following returns the billable periods of a site,
* could run into issues if multiple different periods used for a site
* @author Paul Heaney
* @param $siteid TODO
* @param $startdate TODO
* @param $enddate TODO
* @returns $units TODO
**/
function billable_units_site($siteid, $startdate=0, $enddate=0)
{
    $sql = "SELECT i.id FROM `{$GLOBALS['dbIncidents']}` AS i, `{$GLOBALS['dbContacts']}` AS c ";
    $sql .= "WHERE c.id = i.contact AND c.siteid = {$siteid} ";
    if ($startdate != 0)
    {
        $sql .= "AND closed >= {$startdate} ";
    }

    if ($enddate != 0)
    {
        $sql .= "AND closed <= {$enddate} ";
    }

    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        return FALSE;
    }

    $units = 0;

    if (mysql_num_rows($result) > 0)
    {
        while ($obj = mysql_fetch_object($result))
        {
            $a = make_incident_billing_array($obj->id);
            $units += $a[-1]['totalcustomerperiods'];
        }
    }

    return $units;
}


/**
* Return an array of contacts allowed to use this contract
* @author Kieran Hogg
* @param $maintid integer - ID of the contract
* @returns array of supported contacts, NULL if none
**/
function supported_contacts($maintid)
{
    global $dbSupportContacts, $dbContacts;
    $sql  = "SELECT c.forenames, c.surname, sc.contactid AS contactid ";
    $sql .= "FROM `{$dbSupportContacts}` AS sc, `{$dbContacts}` AS c ";
    $sql .= "WHERE sc.contactid=c.id AND sc.maintenanceid='{$maintid}' ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    if (!empty($result))
    {
        while ($row = mysql_fetch_object($result))
        {
            $returnarray[] = $row->contactid;
        }
        return $returnarray;
    }
    else return NULL;
}


/**
* Return an array of contracts which the contact is an admin contact for
* @author Kieran Hogg
* @param $maintid integer - ID of the contract
* @param $siteid integer - The ID of the site
* @returns array of contract ID's for which the given contactid is an admin contact, NULL if none
**/
function admin_contact_contracts($contactid, $siteid)
{
    $sql = "SELECT DISTINCT m.id ";
    $sql .= "FROM `{$GLOBALS['dbMaintenance']}` AS m ";
    $sql .= "WHERE m.admincontact={$contactid} ";
    $sql .= "AND m.site={$siteid} ";

    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    if ($result)
    {
        while ($row = mysql_fetch_object($result))
        {
            $contractsarray[] = $row->id;
        }
    }

    return $contractsarray;
}


/**
* Return an array of contracts which the contact is an named contact for
* @author Kieran Hogg
* @param $maintid integer - ID of the contract
* @returns array of supported contracts, NULL if none
**/
function contact_contracts($contactid, $siteid, $checkvisible = TRUE)
{
    $sql = "SELECT DISTINCT m.id AS id
            FROM `{$GLOBALS['dbMaintenance']}` AS m,
            `{$GLOBALS['dbContacts']}` AS c,
            `{$GLOBALS['dbSupportContacts']}` AS sc
            WHERE m.site={$siteid}
            AND sc.maintenanceid=m.id
            AND sc.contactid=c.id ";
    if ($checkvisible)
    {
        $sql .= "AND m.var_incident_visible_contacts = 'yes'";
    }

    if ($result = mysql_query($sql))
    {
        while ($row = mysql_fetch_object($result))
        {
            $contractsarray[] = $row->id;
        }
    }
    return $contractsarray;
}


/**
* Return an array of contracts which non-contract contacts can see incidents
* @author Kieran Hogg
* @param $maintid integer - ID of the contract
* @returns array of supported contracts, NULL if none
**/
function all_contact_contracts($contactid, $siteid)
{
    $sql = "SELECT DISTINCT m.id AS id
            FROM `{$GLOBALS['dbMaintenance']}` AS m,
            WHERE m.site={$siteid}
            AND m.var_incident_visible_all = 'yes'";

    if ($result = mysql_query($sql))
    {
        while ($row = mysql_fetch_object($result))
        {
            $contractsarray[] = $row->id;
        }
    }
    return $contractsarray;
}


/**
* Checks is a given username is unique
* @author Kieran Hogg
* @param $username string - username
* @returns bool TRUE if valid, FALSE if not
**/
function valid_username($username)
{
    $username = cleanvar($username);
    $valid = TRUE;

    $tables = array('dbUsers', 'dbContacts');

    foreach ($tables AS $table)
    {
        $sql = "SELECT username FROM `{$GLOBALS[$table]}` WHERE username='{$username}'";
        if ($result = mysql_query($sql) AND mysql_num_rows($result) != 0)
        {
            $valid = FALSE;
        }
    }

    return $valid;
}


/**
* Update the current session id with a newly generated one
* @author Ivan Lucas
* @note Wrap the php function for different versions of php
**/
function session_regenerate()
{
//     if (function_exists('session_regenerate_id'))
//     {
//         if (!version_compare(phpversion(),"5.1.0",">=")) session_regenerate_id(FALSE);
//         else session_regenerate_id();
//     }
}


/**
* Finds the software associated with a contract
* @author Ivan Lucas
* @note Wrap the php function for different versions of php
**/
function contract_software()
{
    $contract = intval($contract);
    $sql = "SELECT s.id
            FROM `{$GLOBALS['dbMaintenance']}` AS m,
                `{$GLOBALS['dbProducts']}` AS p,
                `{$GLOBALS['dbSoftwareProducts']}` AS sp,
                `{$GLOBALS['dbSoftware']}` AS s
            WHERE m.product=p.id
            AND p.id=sp.productid
            AND sp.softwareid=s.id ";
    $sql .= "AND (1=0 ";
    if (is_array($_SESSION['contracts']))
    {
        foreach ($_SESSION['contracts'] AS $contract)
        {
            $sql .= "OR m.id={$contract} ";
        }
    }
    $sql .= ")";

    if ($result = mysql_query($sql))
    {
        while ($row = mysql_fetch_object($result))
        {
            $softwarearray[] = $row->id;
        }
    }

    return $softwarearray;
}


/**
* HTML for an ajax help link
* @author Ivan Lucas
* @param $context string.  The base filename of the popup help file in htdocs/help/en-GB/ (without the .txt extension)
**/
function help_link($context)
{
    global $strHelpChar;
    $html = "<span class='helplink'>[<a href='#' tabindex='-1' onmouseover=\"contexthelp(this, '$context');return false;\">{$strHelpChar}<span></span></a>]</span>";

    return $html;
}


/**
* Function to return an user error message when a file fails to upload
* @author Paul Heaney
* @param errorcode The error code from $_FILES['file']['error']
* @param name The file name which was uploaded from $_FILES['file']['name']
* @return String containing the error message (in HTML)
*/
function get_file_upload_error_message($errorcode, $name)
{
    $str = "<div class='detailinfo'>\n";

    $str .=  "An error occurred while uploading <strong>{$_FILES['attachment']['name']}</strong>";

    $str .=  "<p class='error'>";
    switch ($errorcode)
    {
        case UPLOAD_ERR_INI_SIZE:  $str .= "The file exceded the maximum size set in PHP"; break;
        case UPLOAD_ERR_FORM_SIZE:  $str .=  "The uploaded file was too large"; break;
        case UPLOAD_ERR_PARTIAL: $str .=  "The file was only partially uploaded"; break;
        case UPLOAD_ERR_NO_FILE: $str .=  "No file was uploaded"; break;
        case UPLOAD_ERR_NO_TMP_DIR: $str .=  "Temporary folder is missing"; break;
        default: $str .=  "An unknown file upload error occurred"; break;
    }
    $str .=  "</p>";
    $str .=  "</div>";

    return $str;
}


/**
* Function to produce a user readable file size i.e 2048 bytes 1KB etc
*
* @author Paul Heaney
* @param filesize - filesize in bytes
* @return String filesize in readable format
*
*/
function readable_file_size($filesize)
{
    global $strBytes, $strKBytes, $strMBytes, $strGBytes, $strTBytes;
    $j = 0;

    $ext = array($strBytes, $strKBytes, $strMBytes, $strGBytes, $strTBytes);
    while ($filesize >= pow(1024,$j))
    {
        ++$j;
    }
    $filemax = round($filesize / pow(1024,$j-1) * 100) / 100 . ' ' . $ext[$j-1];

    return $filemax;
}


/**
* Return the html of contract detatils
* @author Kieran Hogg
* @param $maintid integer - ID of the contract
* @param $mode string. 'internal' or 'external'
* @returns array of supported contracts, NULL if none
**/
function contract_details($id, $mode='internal')
{
    global $CONFIG, $iconset, $dbMaintenance, $dbSites, $dbResellers, $dbLicenceTypes, $now;

    $sql  = "SELECT m.*, m.notes AS maintnotes, s.name AS sitename, ";
    $sql .= "r.name AS resellername, lt.name AS licensetypename ";
    $sql .= "FROM `{$dbMaintenance}` AS m, `{$dbSites}` AS s, ";
    $sql .= "`{$dbResellers}` AS r, `{$dbLicenceTypes}` AS lt ";
    $sql .= "WHERE s.id = m.site ";
    $sql .= "AND m.id='{$id}' ";
    $sql .= "AND m.reseller = r.id ";
    $sql .= "AND (m.licence_type IS NULL OR m.licence_type = lt.id)";

    $maintresult = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

    $maintrow = mysql_fetch_array($maintresult);

    $html = "<table align='center' class='vertical'>";
    $html .= "<tr><th>{$GLOBALS[strContract]} {$GLOBALS[strID]}:</th>";
    $html .= "<td><h3>".icon('contract', 32)." ";
    $html .= "{$maintrow['id']}</h3></td></tr>";
    $html .= "<tr><th>{$GLOBALS[strStatus]}:</th><td>";
    if ($maintrow['term'] == 'yes')
    {
        $html .= "<strong>{$GLOBALS[strTerminated]}</strong>";
    }
    else
    {
        $html .= $GLOBALS[strActive];
    }

    if ($maintrow['expirydate'] < $now AND $maintrow['expirydate'] != '-1')
    {
        $html .= "<span class='expired'>, {$GLOBALS[strExpired]}</span>";
    }
    $html .= "</td></tr>\n";
    $html .= "<tr><th>{$GLOBALS[strSite]}:</th>";

    if ($mode == 'internal')
    {
        $html .= "<td><a href=\"site_details.php?id=".$maintrow['site']."\">".$maintrow['sitename']."</a></td></tr>";
    }
    else
    {
        $html .= "<td><a href=\"sitedetails.php\">".$maintrow['sitename']."</a></td></tr>";
    }
    $html .= "<tr><th>{$GLOBALS[strAdminContact]}:</th>";

    if ($mode == 'internal')
    {
        $html .= "<td><a href=\"contact_details.php?id=";
        $html .= "{$maintrow['admincontact']}\">";
        $html .= contact_realname($maintrow['admincontact'])."</a></td></tr>";
    }
    else
    {
        $html .= "<td><a href='contactdetails.php?id={$maintrow['admincontact']}'>";
        $html .= contact_realname($maintrow['admincontact'])."</a></td></tr>";
    }

    $html .= "<tr><th>{$GLOBALS[strReseller]}:</th><td>";

    if (empty($results['resellername']))
    {
        $html .= $GLOBALS[strNoReseller];
    }
    else
    {
        $html .= $maintrow['resellername'];
    }
    $html .= "</td></tr>";
    $html .= "<tr><th>{$GLOBALS[strProduct]}:</th><td>".product_name($maintrow['product'])."</td></tr>";
    $html .= "<tr><th>{$GLOBALS[strIncidents]}:</th>";
    $html .= "<td>";
    $incidents_remaining = $maintrow['incident_quantity'] - $maintrow['incidents_used'];

    if ($maintrow['incident_quantity'] == 0)
    {
        $quantity = $GLOBALS[strUnlimited];
    }
    else
    {
        $quantity = $maintrow['incident_quantity'];
    }

    $html .= sprintf($GLOBALS[strUsedNofN], $maintrow['incidents_used'], $quantity);
    if ($maintrow['incidents_used'] >= $maintrow['incident_quantity'] AND
        $maintrow['incident_quantity'] != 0)
    {
        $html .= " ($GLOBALS[strZeroRemaining])";
    }

    $html .= "</td></tr>";
    if ($maintrow['licence_quantity'] != '0')
    {
        $html .= "<tr><th>{$GLOBALS[strLicense]}:</th>";
        $html .= "<td>{$maintrow['licence_quantity']} {$maintrow['licensetypename']}</td></tr>\n";
    }

    $html .= "<tr><th>{$GLOBALS[strServiceLevel]}:</th><td>".servicelevel_name($maintrow['servicelevelid'])."</td></tr>";
    $html .= "<tr><th>{$GLOBALS[strExpiryDate]}:</th><td>";
    if ($maintrow['expirydate'] == '-1')
    {
        $html .= "{$GLOBALS[strUnlimited]}";
    }
    else
    {
        $html .= ldate($CONFIG['dateformat_date'], $maintrow['expirydate']);
    }

    $html .= "</td></tr>";

    $html .= "<tr><th>{$GLOBALS['strService']}</th><td>";
    $html .= contract_service_table($id);
    $html .= "</td></tr>\n";

    // FIXME not sure if this should be here
    $html .= "<tr><th>{$GLOBALS['strBalance']}</th><td>{$CONFIG['currency_symbol']}".number_format(get_contract_balance($id), 2);
    $multiplier = get_billable_multiplier(strtolower(date('D', $now)), date('G', $now));
    $html .= " (&cong;".contract_unit_balance($id)." units)";
    $html .= "</td></tr>";

    if ($maintrow['maintnotes'] != '' AND $mode == 'internal')
    {
        $html .= "<tr><th>{$GLOBALS[strNotes]}:</th><td>".$maintrow['maintnotes']."</td></tr>";
    }
    $html .= "</table>";

    if ($mode == 'internal')
    {
        $html .= "<p align='center'>";
        $html .= "<a href=\"edit_contract.php?action=edit&amp;maintid=$id\">{$GLOBALS[strEditContract]}</a> | ";
        $html .= "<a href='billing/addservice.php?contractid={$id}'>{$GLOBALS['strAddService']}</a></p>";
    }
    $html .= "<h3>{$GLOBALS['strContacts']}</h3>";

    if (mysql_num_rows($maintresult) < 1)
    {
        trigger_error("{$GLOBALS[strNoContractsFound]}: {$id}", E_USER_WARNING);
    }
    else
    {
        if ($maintrow['allcontactssupported'] == 'yes')
        {
            $html .= "<p class='info'>{$GLOBALS['strAllSiteContactsSupported']}</p>";
        }
        else
        {
            $allowedcontacts = $maintrow['supportedcontacts'];
            
            $supportedcontacts = supported_contacts($id);
            $numberofcontacts = 0;

                $numberofcontacts = sizeof($supportedcontacts);
                if ($allowedcontacts == 0)
                {
                    $allowedcontacts = $GLOBALS['strUnlimited'];
                }
                $html .= "<table align='center'>";
                $supportcount = 1;
                
                if ($numberofcontacts > 0)
                {
                    foreach ($supportedcontacts AS $contact)
                    {
                        $html .= "<tr><th>{$GLOBALS[strContact]} #{$supportcount}:</th>";
                        $html .= "<td>".icon('contact', 16)." ";
                        if ($mode == 'internal')
                        {
                            $html .= "<a href=\"contact_details.php?";
                        }
                        else
                        {
                            $html .= "<a href=\"contactdetails.php?";
                        }
                        $html .= "id={$contact}\">".contact_realname($contact)."</a>, ";
                        $html .= contact_site($contact). "</td>";

                        if ($mode == 'internal')
                        {
                            $html .= "<td><a href=\"delete_maintenance_support_contact.php?contactid=".$contact."&amp;maintid=$id&amp;context=maintenance\">{$GLOBALS[strRemove]}</a></td></tr>\n";
                        }
                        else
                        {
                            $html .= "<td><a href=\"{$_SERVER['PHP_SELF']}?id={$id}&amp;contactid=".$contact."&amp;action=remove\">{$GLOBALS[strRemove]}</a></td></tr>\n";
                        }
                        $supportcount++;
                    }
                    $html .= "</table>";
                }
                else
                {
                    $html .= "<p class='info'>{$GLOBALS[strNoRecords]}<p>";
                }
        }
    }

    if ($maintrow['allcontactssupported'] != 'yes')
    {
        $html .= "<p align='center'>";
        $html .= sprintf($GLOBALS['strUsedNofN'],
                        "<strong>".$numberofcontacts."</strong>",
                        "<strong>".$allowedcontacts."</strong>");
        $html .= "</p>";

        if ($numberofcontacts < $allowedcontacts OR $allowedcontacts == 0 AND $mode == 'internal')
        {
            $html .= "<p align='center'><a href='add_contact_support_contract.php?maintid={$id}&amp;siteid={$maintrow['site']}&amp;context=maintenance'>";
            $html .= "{$GLOBALS[strAddContact]}</a></p>";
        }
        else
        {
            $html .= "<h3>{$GLOBALS['strAddContact']}</h3>";
            $html .= "<form action='{$_SERVER['PHP_SELF']}?id={$id}&amp;action=";
            $html .= "add' method='post' >";
            $html .= "<p align='center'>{$GLOBLAS['strAddNewSupportedContact']} ";
            $html .= contact_site_drop_down('contactid',
                                            'contactid',
                                            maintenance_siteid($id),
                                            supported_contacts($id));
            $html .= help_link('NewSupportedContact');
            $html .= " <input type='submit' value='{$GLOBALS['strAdd']}' /></p></form>";
        }

        $html .= "<p align='center'><a href='addcontact.php'>";
        $html .= "{$GLOBALS['strAddNewSiteContact']}</a></p>";
    }

    $html .= "<br />";
    $html .= "<h3>{$GLOBALS[strSkillsSupportedUnderContract]}:</h3>";
    // supported software
    $sql = "SELECT * FROM `{$GLOBALS[dbSoftwareProducts]}` AS sp, `{$GLOBALS[dbSoftware]}` AS s ";
    $sql .= "WHERE sp.softwareid = s.id AND productid='{$maintrow['product']}' ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

    if (mysql_num_rows($result)>0)
    {
        $html .="<table align='center'>";
        while ($software=mysql_fetch_array($result))
        {
            $html .= "<tr><td> ".icon('skill', 16)." ";
            if ($software->lifetime_end > 0 AND $software->lifetime_end < $now)
            {
                $html .= "<span class='deleted'>";
            }
            $html .= $software['name'];
            if ($software->lifetime_end > 0 AND $software->lifetime_end < $now)
            {
                $html .= "</span>";
            }
            $html .= "</td></tr>\n";
        }
        $html .= "</table>\n";
    }
    else
    {
        $html .= "<p align='center'>{$GLOBALS[strNone]} / {$GLOBALS[strUnknown]}<p>";
    }

    return $html;
}

/**
* Uploads a file
* @author Kieran Hogg
* @param $file mixed file to upload
* @param $id
* @returns string path of file
**/
function upload_file($file, $incidentid, $updateid, $type='public')
{
    global $CONFIG, $now;
    $att_max_filesize = return_bytes($CONFIG['upload_max_filesize']);
    $incident_attachment_fspath = $CONFIG['attachment_fspath'] . $id; //FIXME $id never declared
    if ($file['name'] != '')
    {
        // try to figure out what delimeter is being used (for windows or unix)...
        //.... // $delim = (strstr($filesarray[$c],"/")) ? "/" : "\\";
        $delim = (strstr($file['tmp_name'],"/")) ? "/" : "\\";

        // make incident attachment dir if it doesn't exist
        $umask = umask(0000);
        if (!file_exists($CONFIG['attachment_fspath'] . "$id"))
        {
            $mk = @mkdir($CONFIG['attachment_fspath'] ."$id", 0770);
            if (!$mk) trigger_error("Failed creating incident attachment directory: {$incident_attachment_fspath }{$id}", E_USER_WARNING);
        }
        $mk = @mkdir($CONFIG['attachment_fspath'] .$id . "{$delim}{$now}", 0770);
        if (!$mk) trigger_error("Failed creating incident attachment (timestamp) directory: {$incident_attachment_fspath} {$id} {$delim}{$now}", E_USER_WARNING);
        umask($umask);
        $returnpath = $id.$delim.$now.$delim.$file['name'];
        $filepath = $incident_attachment_fspath.$delim.$now.$delim;
        $newfilename = $filepath.$file['name'];

        // Move the uploaded file from the temp directory into the incidents attachment dir
        $mv = move_uploaded_file($file['tmp_name'], $newfilename);
        if (!$mv) trigger_error('!Error: Problem moving attachment from temp directory to: '.$newfilename, E_USER_WARNING);

        // Check file size before attaching
        if ($file['size'] > $att_max_filesize)
        {
            trigger_error("User Error: Attachment too large or file upload error - size: {$file['size']}", E_USER_WARNING);
            // throwing an error isn't the nicest thing to do for the user but there seems to be no guaranteed
            // way of checking file sizes at the client end before the attachment is uploaded. - INL
            return FALSE;
        }
        else
        {
            if (!empty($sit[2]))
            {
                $usertype = 'user';
                $userid = $sit[2];
            }
            else
            {
                $usertype = 'contact';
                $userid = $_SESSION['contactid'];
            }
            $sql = "INSERT INFO `{$GLOBALS['dbFiles']}`
                    (category, filename, size, userid, usertype, path, filedate, refid)
                    VALUES
                    ('{$type}', '{$file['name']}', '{$file['size']}', '{$userid}', '{$usertype}', '{$filepath}', '{$now}', '{$id}')";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            return $returnpath;
        }
    }
}


/**
* Function to return a logged in ftp connection
* @author Ivan Lucas
*/
function create_ftp_connection()
{
    global $CONFIG;

    $conn_id = ftp_connect($CONFIG['ftp_hostname']);

    // login with username and password
    $login_result = ftp_login($conn_id, $CONFIG['ftp_username'], $CONFIG['ftp_password']);

    // check connection
    if ((!$conn_id) || (!$login_result))
    {
        trigger_error("FTP Connection failed, connecting to {$CONFIG['ftp_hostname']} for user {$CONFIG['ftp_hostname']}}", E_USER_WARNING);
    }
    else
    {
        echo "Connected to {$CONFIG['ftp_hostname']}, for user {$CONFIG['ftp_username']}<br />";
    }

    return $conn_id;
}


/**
* Fucntion to return a HTML table row with two columns.
* Giving radio boxes for groups and if the level is 'management' then you are able to view the users (de)selcting
* @param $title - text to go in the first column
* @param $level either management or engineer, management is able to (de)select users
* @param $groupid  Defalt group to select
* @return table of format <tr><th /><td /></tr>
* @author Paul Heaney
*/
function group_user_selector($title, $level="engineer", $groupid)
{
    global $dbUsers, $dbGroups;
    $str .= "<tr><th>{$title}</th>";
    $str .= "<td align='center'>";

    $sql = "SELECT DISTINCT(g.name), g.id FROM `{$dbUsers}` AS u, `{$dbGroups}` AS g ";
    $sql .= "WHERE u.status > 0 AND u.groupid = g.id ORDER BY g.name";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

    while ($row = mysql_fetch_object($result))
    {
        $str .= "<input type='radio' name='group' value='byweek' onclick='groupMemberSelect(\"{$row->name}\")' ";

        if ($groupid == $row->id)
        {
            $str .= " checked='checked' ";
            $groupname = $row->name;
        }

        $str .= "/>{$row->name} \n";
    }

    $str .="<br />";


    $sql = "SELECT u.id, u.realname, g.name FROM `{$dbUsers}` AS u, `{$dbGroups}` AS g ";
    $sql .= "WHERE u.status > 0 AND u.groupid = g.id ORDER BY username";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    if ($level == "management")
    {
        $str .= "<select name='users[]' id='include' multiple='multiple' size='20'>";
    }
    elseif ($level == "engineer")
    {
        $str .= "<select name='users[]' id='include' multiple='multiple' size='20' style='display:none'>";
    }

    while ($row = mysql_fetch_object($result))
    {
        $str .= "<option value='{$row->id}'>{$row->realname} ({$row->name})</option>\n";
    }
    $str .= "</select>";
    $str .= "<br />";
    if ($level == "engineer")
    {
        $visibility = " style='display:none'";
    }

    $str .= "<input type='button' id='selectall' onclick='doSelect(true, \"include\")' value='Select All' {$visibility} />";
    $str .= "<input type='button' id='clearselection' onclick='doSelect(false, \"include\")' value='Clear Selection' {$visibility} />";

    $str .= "</td>";
    $str .= "</tr>\n";

    // FIXME make this XHTML valid
    $str .= "<script type='text/javascript'>\n//<![CDATA[\ngroupMemberSelect(\"{$groupname}\");\n//]]>\n</script>";

    return $str;
}


/**
* Output html for the 'time to next action' box
* Used in add incident and update incident
* @return $html string html to output
* @author Kieran Hogg
* @TODO populate $id
*/
function show_next_action()
{
	global $now;
    $html = "{$GLOBALS['strPlaceIncidentInWaitingQueue']}<br />";

    $oldtimeofnextaction = incident_timeofnextaction($id); //FIXME $id never populated
    if ($oldtimeofnextaction < 1)
    {
        $oldtimeofnextaction = $now;
    }
    $wait_time = ($oldtimeofnextaction - $now);

    $na_days = floor($wait_time / 86400);
    $na_remainder = $wait_time % 86400;
    $na_hours = floor($na_remainder / 3600);
    $na_remainder = $wait_time % 3600;
    $na_minutes = floor($na_remainder / 60);
    if ($na_days < 0) $na_days = 0;
    if ($na_hours < 0) $na_hours = 0;
    if ($na_minutes < 0) $na_minutes = 0;

    $html .= "<label><input checked='checked' type='radio' ";
    $html .= "name='timetonextaction_none' id='ttna_none' ";
    $html .= "onchange=\"update_ttna();\" onclick=\"window.document.updateform.";
    $html .= "timetonextaction_days.value = ''; window.document.updateform.";
    $html .= "timetonextaction_hours.value = ''; window.document.updateform.";
    $html .= "timetonextaction_minutes.value = '';\" value='None' />{$GLOBALS['strNo']}";
    $html .= "</label><br />";

    $html .= "<label><input type='radio' name='timetonextaction_none' ";
    $html .= "id='ttna_time' value='time' onchange=\"update_ttna();\" />";
    $html .= "{$GLOBALS['strForXDaysHoursMinutes']}</label><br />";
    $html .= "<span id='ttnacountdown'";
    if (empty($na_days) AND
        empty($na_hours) AND
        empty($na_minutes))
    {
        $html .= " style='display: none;'";
    }
    $html .= ">";
    $html .= "&nbsp;&nbsp;&nbsp;<input maxlength='3' name='timetonextaction_days'";
    $html .= " id='timetonextaction_days' value='{$na_days}' onclick='window.";
    $html .= "document.updateform.timetonextaction_none[0].checked = true;' ";
    $html .= "size='3' /> {$GLOBALS['strDays']}&nbsp;";
    $html .= "<input maxlength='2' name='timetonextaction_hours' ";
    $html .= "id='timetonextaction_hours' value='{$na_hours}' onclick='window.";
    $html .= "document.updateform.timetonextaction_none[0].checked = true;' ";
    $html .= "size='3' /> {$GLOBALS['strHours']}&nbsp;";
    $html .= "<input maxlength='2' name='timetonextaction_minutes' id='";
    $html .= "timetonextaction_minutes' value='{$na_minutes}' onclick='window.";
    $html .= "document.updateform.timetonextaction_none[0].checked = true;' ";
    $html .= "size='3' /> {$GLOBALS['strMinutes']}";
    $html .= "<br /></span>";

    $html .= "<input type='radio' name='timetonextaction_none' id='ttna_date' ";
    $html .= "value='date' onchange=\"update_ttna();\" />";
    $html .= "{$GLOBALS['strUntilSpecificDateAndTime']}<br />";
    $html .= "<span id='ttnadate' style='display: none;'>";
    $html .= "<input name='date' id='date' size='10' value='{$date}' onclick=";
    $html .= "\"window.document.updateform.timetonextaction_none[1].checked = true;\"/> ";
    $html .= date_picker('updateform.date');
    $html .= " <select name='timeoffset' id='timeoffset' onchange='window.";
    $html .= "document.updateform.timetonextaction_none[1].checked = true;'>";
    $html .= "<option value='0'></option>";
    $html .= "<option value='0'>8:00 AM</option>";
    $html .= "<option value='1'>9:00 AM</option>";
    $html .= "<option value='2'>10:00 AM</option>";
    $html .= "<option value='3'>11:00 AM</option>";
    $html .= "<option value='4'>12:00 PM</option>";
    $html .= "<option value='5'>1:00 PM</option>";
    $html .= "<option value='6'>2:00 PM</option>";
    $html .= "<option value='7'>3:00 PM</option>";
    $html .= "<option value='8'>4:00 PM</option>";
    $html .= "<option value='9'>5:00 PM</option>";
    $html .= "</select>";
    $html .= "<br /></span>";

    return $html;
}


/**
* Output the html for an icon
*
* @param string $filename filename of the string, minus extension, we assume .png
* @param int $size size of the icon, from: 12, 16, 32
* @param string $alt alt text of the icon (optional)
* @param string $title (optional)
* @param string $id ID attribute (optional)
* @return string $html icon html
* @author Kieran Hogg, Ivan Lucas
*/
function icon($filename, $size='', $alt='', $title='', $id='')
{
    global $iconset, $CONFIG;
    $sizes = array(12, 16, 32);

    if (!in_array($size, $sizes) OR empty($size))
    {
        trigger_error("Incorrect image size for '{$filename}.png' ", E_USER_WARNING);
        $size = 16;
    }

    $file = "{$CONFIG['application_fspath']}htdocs/images/icons/{$iconset}";
    $file .= "/{$size}x{$size}/{$filename}.png";

    $urlpath = "{$CONFIG['application_webpath']}images/icons/{$iconset}";
    $urlpath .= "/{$size}x{$size}/{$filename}.png";

    if (!file_exists($file))
    {
        $alt = "Missing icon: '$filename.png', ($file) size {$size}";
        if ($CONFIG['debug']) trigger_error($alt, E_USER_WARNING);
        $urlpath = "{$CONFIG['application_webpath']}images/icons/sit";
        $urlpath .= "/16x16/blank.png";
    }
    $icon = "<img src=\"{$urlpath}\"";
    if (!empty($alt))
    {
        $icon .= " alt=\"{$alt}\" ";
    }
    else
    {
        $alt = $filename;
        $icon .= " alt=\"{$alt}\" ";
    }
    if (!empty($title))
    {
        $icon .= " title=\"{$title}\"";
    }
    else
    {
        $icon .= " title=\"{$alt}\"";
    }

    if (!empty($id))
    {
        $icon .= " id=\"{$id}\"";
    }
    $icon .= " />";

    return $icon;
}

/**
* Output the html for a KB article
*
* @param int $id ID of the KB article
* @param string $mode whether this is internal or external facing, defaults to internal
* @returns string $html kb article html
* @author Kieran Hogg
*/
function kb_article($id, $mode='internal')
{
    global $CONFIG, $iconset;
    $id = intval($id);
    if (!is_number($id) OR $id == 0)
    {
        trigger_error("Incorrect KB ID", E_USER_ERROR);
        include 'htmlfooter.inc.php';
        exit;
    }

    $sql = "SELECT * FROM `{$GLOBALS['dbKBArticles']}` WHERE docid='{$id}' LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    $kbarticle = mysql_fetch_object($result);

    if (empty($kbarticle->title))
    {
        $kbarticle->title = $GLOBALS['strUntitled'];
    }
    $html .= "<div id='kbarticle'";
    if ($kbarticle->distribution == 'private') $html .= " class='expired'";
    if ($kbarticle->distribution == 'restricted') $html .= " class='urgent'";
    $html .= ">";
    $html .= "<h2 class='kbtitle'>{$kbarticle->title}</h2>";

    if (!empty($kbarticle->distribution) AND $kbarticle->distribution != 'public')
    {
        $html .= "<h2 class='kbdistribution'>{$GLOBALS['strDistribution']}: ".ucfirst($kbarticle->distribution)."</h2>";
    }

    // Lookup what software this applies to
    $ssql = "SELECT * FROM `{$GLOBALS['dbKBSoftware']}` AS kbs, `{$GLOBALS['dbSoftware']}` AS s ";
    $ssql .= "WHERE kbs.softwareid = s.id AND kbs.docid = '{$id}' ";
    $ssql .= "ORDER BY s.name";
    $sresult = mysql_query($ssql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    if (mysql_num_rows($sresult) >= 1)
    {
        $html .= "<h3>{$GLOBALS['strEnvironment']}</h3>";
        $html .= "<p>{$GLOBALS['strTheInfoInThisArticle']}:</p>\n";
        $html .= "<ul>\n";
        while ($kbsoftware = mysql_fetch_object($sresult))
        {
            $html .= "<li>{$kbsoftware->name}</li>\n";
        }
        $html .= "</ul>\n";
    }

    $csql = "SELECT * FROM `{$GLOBALS['dbKBContent']}` WHERE docid='{$id}' ";
    $cresult = mysql_query($csql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    $restrictedcontent = 0;
    while ($kbcontent = mysql_fetch_object($cresult))
    {
        switch ($kbcontent->distribution)
        {
            case 'private':
                if ($mode != 'internal')
                {
                    echo "<p class='error'>{$GLOBALS['strPermissionDenied']}</p>";
                    include 'htmlfooter.inc.php';
                    exit;
                }
                $html .= "<div class='kbprivate'><h3>{$kbcontent->header} (private)</h3>";
                $restrictedcontent++;
            break;

            case 'restricted':
                if ($mode != 'internal')
                {
                    echo "<p class='error'>{$GLOBALS['strPermissionDenied']}</p>";
                    include 'htmlfooter.inc.php';
                    exit;
                }
                $html .= "<div class='kbrestricted'><h3>{$kbcontent->header}</h3>";
                $restrictedcontent++;
            break;

            default:
                $html .= "<div><h3>{$kbcontent->header}</h3>";
        }
        //$html .= "<{$kbcontent->headerstyle}>{$kbcontent->header}</{$kbcontent->headerstyle}>\n";
        $html .= '';
        $kbcontent->content=nl2br($kbcontent->content);
        $search = array("/(?<!quot;|[=\"]|:\/{2})\b((\w+:\/{2}|www\.).+?)"."(?=\W*([<>\s]|$))/i", "/(([\w\.]+))(@)([\w\.]+)\b/i");
        $replace = array("<a href=\"$1\">$1</a>", "<a href=\"mailto:$0\">$0</a>");
        $kbcontent->content = preg_replace("/href=\"www/i", "href=\"http://www", preg_replace ($search, $replace, $kbcontent->content));
        $html .= bbcode($kbcontent->content);
        $author[]=$kbcontent->ownerid;
        $html .= "</div>\n";

    }

    if ($restrictedcontent > 0)
    {
        $html .= "<h3>{$GLOBALS['strKey']}</h3>";
        $html .= "<p><span class='keykbprivate'>{$GLOBALS['strPrivate']}</span>".help_link('KBPrivate')." &nbsp; ";
        $html .= "<span class='keykbrestricted'>{$GLOBALS['strRestricted']}</span>".help_link('KBRestricted')."</p>";
    }


    $html .= "<h3>{$GLOBALS['strArticle']}</h3>";
    //$html .= "<strong>{$GLOBALS['strDocumentID']}</strong>: ";
    $html .= "<p><strong>{$CONFIG['kb_id_prefix']}".leading_zero(4,$kbarticle->docid)."</strong> ";
    $pubdate = mysql2date($kbarticle->published);
    if ($pubdate > 0)
    {
        $html .= "{$GLOBALS['strPublished']} ";
        $html .= ldate($CONFIG['dateformat_date'],$pubdate)."<br />";
    }

    if ($mode == 'internal')
    {
        if (is_array($author))
        {
            $author=array_unique($author);
            $countauthors=count($author);
            $count=1;
            if ($countauthors > 1)
            {
                $html .= "<strong>{$GLOBALS['strAuthors']}</strong>:<br />";
            }
            else
            {
                $html .= "<strong>{$GLOBALS['strAuthor']}:</strong> ";
            }
            foreach ($author AS $authorid)
            {
                $html .= user_realname($authorid,TRUE);
                if ($count < $countauthors) $html .= ", " ;
                $count++;
            }
        }
    }

    $html .= "<br />";
    if (!empty($kbarticle->keywords))
    {
        $html .= "<strong>{$GLOBALS['strKeywords']}</strong>: ";
        if ($mode == 'internal')
        {
            $html .= preg_replace("/\[([0-9]+)\]/", "<a href=\"incident_details.php?id=$1\" target=\"_blank\">$0</a>", $kbarticle->keywords);
        }
        else
        {
            $html .= $kbarticle->keywords;
        }
        $html .= "<br />";
    }

    //$html .= "<h3>{$GLOBALS['strDisclaimer']}</h3>";
    $html .= "</p><hr />";
    $html .= $CONFIG['kb_disclaimer_html'];
    $html .= "</div>";

    if ($mode == 'internal')
    {
        $html .= "<p align='center'>";
        $html .= "<a href='browse_kb.php'>{$GLOBALS['strBackToList']}</a> | ";
        $html .= "<a href='kb_article.php?id={$kbarticle->docid}'>{$GLOBALS['strEdit']}</a></p>";
    }
    return $html;
}

/**
* Output the html for the edit site form
*
* @param int $site ID of the site
* @param string $mode whether this is internal or external facing, defaults to internal
* @return string $html edit site form html
* @author Kieran Hogg
*/
function show_edit_site($site, $mode='internal')
{
    $sql = "SELECT * FROM `{$GLOBALS['dbSites']}` WHERE id='$site' ";
    $siteresult = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    while ($siterow = mysql_fetch_array($siteresult))
    {
        if ($mode == 'internal')
        {
            $html .= "<h2>".icon('site', 32)." {$GLOBALS['strEditSite']}: {$site} - ";
            $html .= site_name($site)."</h2>";
        }
        else
        {
            $html .= "<h2>".icon('site', 32)." ".site_name($site)."</h2>";
        }

        $html .= "<form name='edit_site' action='{$_SERVER['PHP_SELF']}";
        $html .= "?action=update' method='post' onsubmit='return ";
        $html .= "confirm_action(\"{$GLOBALS['strAreYouSureMakeTheseChanges']}\")'>";
        $html .= "<table align='center' class='vertical'>";
        $html .= "<tr><th>{$GLOBALS['strName']}:</th>";
        $html .= "<td><input class='required' maxlength='50' name='name' size='40' value='{$siterow['name']}' />";
        $html .= "<span class='required'>{$GLOBALS['strRequired']}</span></td></tr>\n";
        if ($mode == 'internal')
        {
            $html .= "<tr><th>{$GLOBALS['strTags']}:</th><td><textarea rows='2' cols='60' name='tags'>";
            $html .= list_tags($site, TAG_SITE, false)."</textarea>\n";
        }
        $html .= "<tr><th>{$GLOBALS['strDepartment']}:</th>";
        $html .= "<td><input maxlength='50' name='department' size='40' value='{$siterow['department']}' />";
        $html .= "</td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strAddress1']}:</th>";
        $html .= "<td><input maxlength='50' name='address1'";
        $html .= "size='40' value='{$siterow['address1']}' />";
        $html .= "</td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strAddress2']}: </th><td><input maxlength='50' name='address2' size='40' value='{$siterow['address2']}' /></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strCity']}:</th><td><input maxlength='255' name='city' size='40' value='{$siterow['city']}' /></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strCounty']}:</th><td><input maxlength='255' name='county' size='40' value='{$siterow['county']}' /></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strPostcode']}:</th><td><input maxlength='255' name='postcode' size='40' value='{$siterow['postcode']}' /></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strCountry']}:</th><td>".country_drop_down('country', $siterow['country'])."</td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strTelephone']}:</th><td>";
        $html .= "<input class='required' maxlength='255' name='telephone' size='40' value='{$siterow['telephone']}' />";
        $html .= "<span class='required'>{$GLOBALS['strRequired']}</span></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strFax']}:</th><td>";
        $html .= "<input maxlength='255' name='fax' size='40' value='{$siterow['fax']}' /></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strEmail']}:</th><td>";
        $html .= "<input class='required' maxlength='255' name='email' size='40' value='{$siterow['email']}' />";
        $html .= "<span class='required'>{$GLOBALS['strRequired']}</span></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strWebsite']}:</th><td>";
        $html .= "<input maxlength='255' name='websiteurl' size='40' value='{$siterow['websiteurl']}' /></td></tr>\n";
        $html .= "<tr><th>{$GLOBALS['strSiteType']}:</th><td>\n";
        $html .= sitetype_drop_down('typeid', $siterow['typeid']);
        $html .= "</td></tr>\n";
        if ($mode == 'internal')
        {
            $html .= "<tr><th>{$GLOBALS['strSalesperson']}:</th><td>";
            $html .= user_drop_down('owner', $siterow['owner'], $accepting = FALSE, '', '', TRUE);
            $html .= "</td></tr>\n";
        }
        if ($mode == 'internal')
        {
            $html .= "<tr><th>{$GLOBALS['strIncidentPool']}:</th>";
            $incident_pools = explode(',', "{$GLOBALS['strNone']},{$CONFIG['incident_pools']}");
            if (array_key_exists($siterow['freesupport'], $incident_pools) == FALSE)
            {
                array_unshift($incident_pools,$siterow['freesupport']);
            }
            $html .= "<td>".array_drop_down($incident_pools,'incident_poolid',$siterow['freesupport'])."</td></tr>";
            $html .= "<tr><th>{$GLOBALS['strActive']}:</th><td><input type='checkbox' name='active' ";
            if ($siterow['active'] == 'true')
            {
                $html .= "checked='".$siterow['active']."'";
            }
            $html .= " value='true' /></td></tr>\n";
            $html .= "<tr><th>{$GLOBALS['strNotes']}:</th><td>";
            $html .= "<textarea rows='5' cols='30' name='notes'>{$siterow['notes']}</textarea>";
            $html .= "</td></tr>\n";
        }
        plugin_do('edit_site_form');
        $html .= "</table>\n";
        $html .= "<input name='site' type='hidden' value='$site' />";
        $html .= "<p><input name='submit' type='submit' value='{$GLOBALS['strSave']	}' /></p>";
        $html .= "</form>";
    }
    return $html;
}


/**
* Output the html for an add contact form
*
* @param int $siteid - the site you want to add the contact to
* @param string $mode - whether this is internal or external facing, defaults to internal
* @return string $html add contact form html
* @author Kieran Hogg
*/
function show_add_contact($siteid = 0, $mode = 'internal')
{
	global $CONFIG;
    $html = show_form_errors('add_contact');
    clear_form_errors('add_contact');
    $html .= "<h2>".icon('contact', 32)." ";
    $html .= "{$GLOBALS['strNewContact']}</h2>";

    if ($mode == 'internal')
    {
        $html .= "<h5 class='warning'>{$GLOBALS['strAvoidDupes']}</h5>";
    }
    $html .= "<form name='contactform' action='{$_SERVER['PHP_SELF']}' ";
    $html .= "method='post' onsubmit=\"return confirm_action('{$GLOBALS['strAreYouSureAdd']}')\">";
    $html .= "<table align='center' class='vertical'>";
    $html .= "<tr><th>{$GLOBALS['strName']}</th>\n";

    $html .= "<td>";
    $html .= "\n<table><tr><td align='center'>{$GLOBALS['strTitle']}<br />";
    $html .= "<input maxlength='50' name='courtesytitle' title=\"";
    $html .= "{$GLOBALS['strCourtesyTitle']}\" size='7'";
    if ($_SESSION['formdata']['add_contact']['courtesytitle'] != '')
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['courtesytitle']}'";
    }
    $html .= "/></td>\n";

    $html .= "<td align='center'>{$GLOBALS['strForenames']}<br />";
    $html .= "<input class='required' maxlength='100' name='forenames' ";
    $html .= "size='15' title=\"{$GLOBALS['strForenames']}\"";
    if ($_SESSION['formdata']['add_contact']['forenames'] != '')
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['forenames']}'";
    }
    $html .= "/></td>\n";

    $html .= "<td align='center'>{$GLOBALS['strSurname']}<br />";
    $html .= "<input class='required' maxlength='100' name='surname' ";
    $html .= "size='20' title=\"{$GLOBALS['strSurname']}\"";
    if ($_SESSION['formdata']['add_contact']['surname'] != '')
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['surname']}'";
    }
    $html .= " /> <span class='required'>{$GLOBALS['strRequired']}</span></td></tr>\n";
    $html .= "</table>\n</td></tr>\n";

    $html .= "<tr><th>{$GLOBALS['strJobTitle']}</th><td><input maxlength='255'";
    $html .= " name='jobtitle' size='35' title=\"{$GLOBALS['strJobTitle']}\"";
    if ($_SESSION['formdata']['add_contact']['jobtitle'] != '')
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['jobtitle']}'";
    }
    $html .= " /></td></tr>\n";
    if ($mode == 'internal')
    {
        $html .= "<tr><th>{$GLOBALS['strSite']}</th><td>";
        $html .= site_drop_down('siteid',$siteid, TRUE)."<span class='required'>{$GLOBALS['strRequired']}</span></td></tr>\n";
    }
    else
    {
        // For external always force the site to be the session site
        $html .= "<input type='hidden' name='siteid' value='{$_SESSION['siteid']}' />";
    }

    $html .= "<tr><th>{$GLOBALS['strDepartment']}</th><td><input maxlength='255' name='department' size='35'";
    if ($_SESSION['formdata']['add_contact']['department'] != '')
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['department']}'";
    }
    $html .= "/></td></tr>\n";

    $html .= "<tr><th>{$GLOBALS['strEmail']}</th><td>";
    $html .= "<input class='required' maxlength='100' name='email' size='35'";
    if ($_SESSION['formdata']['add_contact']['email'])
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['email']}'";
    }
    $html .= "/><span class='required'>{$GLOBALS['strRequired']}</span> ";

    $html .= "<label>";
    $html .= html_checkbox('dataprotection_email', 'No', TRUE);
    $html .= "{$GLOBALS['strEmail']} {$GLOBALS['strDataProtection']}</label>".help_link("EmailDataProtection");
    $html .= "</td></tr>\n";

    $html .= "<tr><th>{$GLOBALS['strTelephone']}</th><td><input maxlength='50' name='phone' size='35'";
    if ($_SESSION['formdata']['add_contact']['phone'] != '')
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['phone']}'";
    }
    $html .= "/> ";

    $html .= "<label>";
    $html .= html_checkbox('dataprotection_phone', 'No', TRUE);
    $html .= "{$GLOBALS['strTelephone']} {$GLOBALS['strDataProtection']}</label>".help_link("TelephoneDataProtection");
    $html .= "</td></tr>\n";

    $html .= "<tr><th>{$GLOBALS['strMobile']}</th><td><input maxlength='100' name='mobile' size='35'";
    if ($_SESSION['formdata']['add_contact']['mobile'] != '')
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['mobile']}'";
    }
    $html .= "/></td></tr>\n";

    $html .= "<tr><th>{$GLOBALS['strFax']}</th><td><input maxlength='50' name='fax' size='35'";
    if ($_SESSION['formdata']['add_contact']['fax'])
    {
        $html .= "value='{$_SESSION['formdata']['add_contact']['fax']}'";
    }
    $html .= "/></td></tr>\n";

    $html .= "<tr><th>{$GLOBALS['strAddress']}</th><td><label>";
    $html .= html_checkbox('dataprotection_address', 'No', TRUE);
    $html .= " {$GLOBALS['strAddress']} {$GLOBALS['strDataProtection']}</label>";
    $html .= help_link("AddressDataProtection")."</td></tr>\n";
    $html .= "<tr><th></th><td><label><input type='checkbox' name='usesiteaddress' value='yes' onclick=\"$('hidden').toggle();\" /> {$GLOBALS['strSpecifyAddress']}</label></td></tr>\n";
    $html .= "<tbody id='hidden' style='display:none'>";
    $html .= "<tr><th>{$GLOBALS['strAddress1']}</th>";
    $html .= "<td><input maxlength='255' name='address1' size='35' /></td></tr>\n";
    $html .= "<tr><th>{$GLOBALS['strAddress2']}</th>";
    $html .= "<td><input maxlength='255' name='address2' size='35' /></td></tr>\n";
    $html .= "<tr><th>{$GLOBALS['strCity']}</th><td><input maxlength='255' name='city' size='35' /></td></tr>\n";
    $html .= "<tr><th>{$GLOBALS['strCounty']}</th><td><input maxlength='255' name='county' size='35' /></td></tr>\n";
    $html .= "<tr><th>{$GLOBALS['strCountry']}</th><td>";
    $html .= country_drop_down('country', $CONFIG['home_country'])."</td></tr>\n";
    $html .= "<tr><th>{$GLOBALS['strPostcode']}</th><td><input maxlength='255' name='postcode' size='35' /></td></tr>\n";
    $html .= "</tbody>";
    if ($mode == 'internal')
    {
        $html .= "<tr><th>{$GLOBALS['strNotes']}</th><td><textarea cols='60' rows='5' name='notes'>";
        if ($_SESSION['formdata']['add_contact']['notes'] != '')
        {
            $html .= $_SESSION['formdata']['add_contact']['notes'];
        }
        $html .= "</textarea></td></tr>\n";
    }
    $html .= "<tr><th>{$GLOBALS['strEmailDetails']}</th>";
    $html .= "<td><input type='checkbox' name='emaildetails' checked='checked'>";
    $html .= "<label for='emaildetails'>{$GLOBALS['strEmailContactLoginDetails']}</td></tr>";
    $html .= "</table>\n\n";
    $html .= "<p><input name='submit' type='submit' value=\"{$GLOBALS['strAddContact']}\" /></p>";
    $html .= "</form>\n";

    //cleanup form vars
    clear_form_data('add_contact');

    return $html;
}


/**
* Procceses a new contact
*
* @author Kieran Hogg
*/
function process_add_contact($mode = 'internal')
{
    global $now, $CONFIG, $dbContacts, $sit;
    // Add new contact
    // External variables
    $siteid = mysql_real_escape_string($_REQUEST['siteid']);
    $email = strtolower(cleanvar($_REQUEST['email']));
    $dataprotection_email = mysql_real_escape_string($_REQUEST['dataprotection_email']);
    $dataprotection_phone = mysql_real_escape_string($_REQUEST['dataprotection_phone']);
    $dataprotection_address = mysql_real_escape_string($_REQUEST['dataprotection_address']);
    $username = cleanvar($_REQUEST['username']);
    $courtesytitle = cleanvar($_REQUEST['courtesytitle']);
    $forenames = cleanvar($_REQUEST['forenames']);
    $surname = cleanvar($_REQUEST['surname']);
    $jobtitle = cleanvar($_REQUEST['jobtitle']);
    $address1 = cleanvar($_REQUEST['address1']);
    $address2 = cleanvar($_REQUEST['address2']);
    $city = cleanvar($_REQUEST['city']);
    $county = cleanvar($_REQUEST['county']);
    if (!empty($address1)) $country = cleanvar($_REQUEST['country']);
    else $country='';
    $postcode = cleanvar($_REQUEST['postcode']);
    $phone = cleanvar($_REQUEST['phone']);
    $mobile = cleanvar($_REQUEST['mobile']);
    $fax = cleanvar($_REQUEST['fax']);
    $department = cleanvar($_REQUEST['department']);
    $notes = cleanvar($_REQUEST['notes']);
    $_SESSION['formdata']['add_contact'] = $_REQUEST;

    $errors = 0;
    // check for blank name
    if ($surname == '')
    {
        $errors++;
        $_SESSION['formerrors']['add_contact']['surname'] = $GLOBALS['strMustEnterSurname'];
    }
    // check for blank site
    if ($siteid == '')
    {
        $errors++;
        $_SESSION['formerrors']['add_contact']['siteid'] = $GLOBALS['strMustSelectCustomerSite'];
    }
    // check for blank email
    if ($email == '' OR $email=='none' OR $email=='n/a')
    {
        $errors++;
        $_SESSION['formerrors']['add_contact']['email'] = $GLOBALS['strMustEnterEmail'];
    }
    if ($siteid==0 OR $siteid=='')
    {
        $errors++;
        $_SESSION['formerrors']['add_contact']['siteid'] = $GLOBALS['strMustSelectSite'];
    }
    // Check this is not a duplicate
    $sql = "SELECT id FROM `{$dbContacts}` WHERE email='$email' AND LCASE(surname)=LCASE('$surname') LIMIT 1";
    $result = mysql_query($sql);
    if (mysql_num_rows($result) >= 1)
    {
        $errors++;
        $_SESSION['formerrors']['add_contact']['duplicate'] = $GLOBALS['strContactRecordExists'];
    }


    // add contact if no errors
    if ($errors == 0)
    {
        if (!empty($dataprotection_email))
        {
            $dataprotection_email='Yes';
        }
        else
        {
            $dataprotection_email='No';
        }

        if (!empty($dataprotection_phone))
        {
            $dataprotection_phone='Yes';
        }
        else
        {
            $dataprotection_phone='No';
        }

        if (!empty($dataprotection_address))
        {
            $dataprotection_address='Yes';
        }
        else
        {
            $dataprotection_address='No';
        }

        // generate username and password

        $username = strtolower(substr($surname, 0, strcspn($surname, " ")));
        $prepassword = generate_password();

        $password = md5($prepassword);

        $sql  = "INSERT INTO `{$dbContacts}` (username, password, courtesytitle, forenames, surname, jobtitle, ";
        $sql .= "siteid, address1, address2, city, county, country, postcode, email, phone, mobile, fax, ";
        $sql .= "department, notes, dataprotection_email, dataprotection_phone, dataprotection_address, ";
        $sql .= "timestamp_added, timestamp_modified) ";
        $sql .= "VALUES ('$username', '$password', '$courtesytitle', '$forenames', '$surname', '$jobtitle', ";
        $sql .= "'$siteid', '$address1', '$address2', '$city', '$county', '$country', '$postcode', '$email', ";
        $sql .= "'$phone', '$mobile', '$fax', '$department', '$notes', '$dataprotection_email', ";
        $sql .= "'$dataprotection_phone', '$dataprotection_address', '$now', '$now')";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        // concatenate username with insert id to make unique
        $newid = mysql_insert_id();
        $username = $username . $newid;
        $sql = "UPDATE `{$dbContacts}` SET username='$username' WHERE id='$newid'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        if (!$result)
        {
            if ($mode == 'internal')
            {
                html_redirect("add_contact.php", FALSE);
            }
            else
            {
                html_redirect("addcontact.php", FALSE);
            }
        }
        else
        {
            clear_form_data('add_contact');
            clear_form_errors('add_contact');
            $sql = "SELECT username, password FROM `{$dbContacts}` WHERE id=$newid";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
            else
            {
                if ($CONFIG['portal'] AND $_POST['emaildetails'] == 'on')
                {
                    trigger('TRIGGER_NEW_CONTACT', array('contactid' => $newid, 'prepassword' => $prepassword, 'userid' => $sit[2]));
                }

                if ($mode == 'internal')
                {
                    html_redirect("contact_details.php?id=$newid");
                    exit;
                }
                else
                {
                    html_redirect("contactdetails.php?id={$newid}");
                    exit;
                }
            }
        }

    }
    else
    {
        if ($mode == 'internal')
        {
            html_redirect('add_contact.php', FALSE);
        }
        else
        {
            html_redirect('addcontact.php', FALSE);
        }
    }
}


/**
* Outputs the name of a KB article, used for triggers
*
* @param int $kbid ID of the KB article
* @return string $name kb article name
* @author Kieran Hogg
*/
function kb_name($kbid)
{
    $kbid = intval($kbid);
    $sql = "SELECT title FROM `{$GLOBALS['dbKBArticles']}` WHERE docid='{$kbid}'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    else
    {
        $row = mysql_fetch_object($result);
        return $row->title;
    }
}


/**
* Outputs the full base url of the install, e.g. http://www.example.com/
*
* @return string base url of the install
* @author Kieran Hogg
*/
function application_url()
{
    global $CONFIG;
    $url = parse_url($_SERVER['HTTP_REFERER']);
    if ($_SERVER['HTTPS'] == 'off' OR empty($_SERVER['HTTPS']))
    {
        $baseurl = "http://";
    }
    else
    {
        $baseurl = "https://";
    }
    $baseurl .= "{$_SERVER['HTTP_HOST']}";
    $baseurl .= "{$CONFIG['application_webpath']}";
    return $baseurl;
}


/**
* Outputs the product name of a contract
*
* @param $maintid ID of the contract
* @return string the name of the product
* @author Kieran Hogg
*/
function contract_product($maintid)
{
    $maintid = intval($maintid);
    $productid = db_read_column('product', $GLOBALS['dbMaintenance'], $maintid);
    $sql = "SELECT name FROM `{$GLOBALS['dbProducts']}` WHERE id='{$productid}'";
    $result = mysql_query($sql);
    $productobj = mysql_fetch_object($result);
    if (!empty($productobj->name))
    {
        return $productobj->name;
    }
    else
    {
        return $GLOBALS['strUnknown'];
    }
}


/**
* Outputs the contract's site name
*
* @param $maintid ID of the contract
* @return string name of the site
* @author Kieran Hogg
*/
function contract_site($maintid)
{
    $maintid = intval($maintid);
    $sql = "SELECT site FROM `{$GLOBALS['dbMaintenance']}` WHERE id='{$maintid}'";
    $result = mysql_query($sql);
    $maintobj = mysql_fetch_object($result);

    $sitename = site_name($maintobj->site);
    if (!empty($sitename))
    {
        return $sitename;
    }
    else
    {
        return $GLOBALS['strUnknown'];
    }
}


/**
* Sets up default triggers for new users or upgraded users
*
* @param $userid ID of the user
* @return bool TRUE on success, FALSE if not
* @author Kieran Hogg
*/
function setup_user_triggers($userid)
{
    $return = TRUE;
    $userid = intval($userid);
    if ($userid != 0)
    {
        $sqls[] = "INSERT INTO `{$GLOBALS['dbTriggers']}` (`triggerid`, `userid`, `action`, `template`, `parameters`, `checks`)
                VALUES('TRIGGER_INCIDENT_ASSIGNED', {$userid}, 'ACTION_NOTICE', 'NOTICE_INCIDENT_ASSIGNED', '', '{userid} == {$userid}');";
        $sqls[] = "INSERT INTO `{$GLOBALS['dbTriggers']}` (`triggerid`, `userid`, `action`, `template`, `parameters`, `checks`)
                VALUES('TRIGGER_SIT_UPGRADED', {$userid}, 'ACTION_NOTICE', 'NOTICE_SIT_UPGRADED', '', '');";
        $sqls[] = "INSERT INTO `{$GLOBALS['dbTriggers']}` (`triggerid`, `userid`, `action`, `template`, `parameters`, `checks`)
                VALUES('TRIGGER_INCIDENT_CLOSED', {$userid}, 'ACTION_NOTICE', 'NOTICE_INCIDENT_CLOSED', '', '{userid} != {$userid}');";
        $sqls[] = "INSERT INTO `{$GLOBALS['dbTriggers']}` (`triggerid`, `userid`, `action`, `template`, `parameters`, `checks`)
                VALUES('TRIGGER_INCIDENT_NEARING_SLA', {$userid}, 'ACTION_NOTICE', 'NOTICE_INCIDENT_NEARING_SLA', '',
                '{ownerid} == {$userid} OR {townerid} == {$userid}');";
        $sqls[] = "INSERT INTO `{$GLOBALS['dbTriggers']}` (`triggerid`, `userid`, `action`, `template`, `parameters`, `checks`)
                VALUES('TRIGGER_LANGUAGE_DIFFERS', {$userid}, 'ACTION_NOTICE', 'NOTICE_LANGUAGE_DIFFERS', '', '');";


        foreach ($sqls AS $sql)
        {
            mysql_query($sql);
            if (mysql_error())
            {
                trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                $return = FALSE;
            }
        }
    }
    else
    {
        trigger_error("setup_user_triggers() Invalid userid '{$userid}' specified", E_USER_NOTICE);
        $return = FALSE;
    }

    return $return;
}


/**
* Returns the SLA ID of a contract
*
* @param $maintid ID of the contract
* @return int ID of the SLA
* @author Kieran Hogg
*/
function contract_slaid($maintid)
{
    $maintid = intval($maintid);
    $slaid = db_read_column('servicelevelid', $GLOBALS['dbMaintenance'], $maintid);
    return $slaid;
}


/**
* Returns the salesperson ID of a site
*
* @param $siteid ID of the site
* @return int ID of the salesperson
* @author Kieran Hogg
*/
function site_salespersonid($siteid)
{
    $siteid = intval($siteid);
    $salespersonid = db_read_column('owner', $GLOBALS['dbSites'], $siteid);
    return $salespersonid;
}


/**
* Returns the salesperson's name of a site
*
* @param $siteid ID of the site
* @return string name of the salesperson
* @author Kieran Hogg
*/
function site_salesperson($siteid)
{
    $siteid = intval($siteid);
    $salespersonid = db_read_column('owner', $GLOBALS['dbSites'], $siteid);
    return user_realname($salespersonid);
}


/**
 * Function to return currently running SiT! version
 * @return String - Currently running application version
 */
function application_version_string()
{
    global $application_version_string;
    return $application_version_string;
}


/**
 * Returns the currently running schema version
 * @author Paul Heaney
 * @return String - currently running schema version
 */
function database_schema_version()
{
    $return = '';
    $sql = "SELECT `schemaversion` FROM `{$GLOBALS['dbSystem']}` WHERE id = 0";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        $return = FALSE;
    }

    if (mysql_num_rows($result) > 0)
    {
        list($return) = mysql_fetch_row($result);
    }

    return $return;
}


/**
* Returns whether the user is accepting or not
*
* @param $userid ID of the user
* @return string 'accepting'|'not accepting'
* @author Kieran Hogg
*/
function user_accepting_status($userid)
{
    if (user_accepting($userid) == 'Yes')
    {
        return 'accepting';
    }
    else
    {
        return 'not accepting';
    }
}

/**
* Returns the status of a user
*
* @param $userid ID of the user
* @return string user status
* @author Kieran Hogg
*/
function user_status_name($userid)
{
    $status = db_read_column('name', $GLOBALS['dbUserStatus'], $userid);
    return $GLOBALS[$status];
}


/**
* Returns the user's porta username
*
* @param $userid ID of the user
* @return string username
* @author Kieran Hogg
*/
function contact_username($userid)
{
    $userid = intval($userid);
    return db_read_column('username', $GLOBALS['dbContacts'], $userid);
}

/**
* Populates $_SESSION['syslang]
*
* @author Kieran Hogg
*/
function populate_syslang()
{
    global $CONFIG;
    // Populate $SYSLANG with system lang
    $file = "{$CONFIG['application_fspath']}includes/i18n/{$CONFIG['default_i18n']}.inc.php";
    if (file_exists($file))
    {
        $fh = fopen($file, "r");

        $theData = fread($fh, filesize($file));
        fclose($fh);
        $lines = explode("\n", $theData);
        foreach ($lines as $values)
        {
            $badchars = array("$", "\"", "\\", "<?php", "?>");
            $values = trim(str_replace($badchars, '', $values));
            if (substr($values, 0, 3) == "str")
            {
                $vars = explode("=", $values);
                $vars[0] = trim($vars[0]);
                $vars[1] = trim(substr_replace($vars[1], "",-2));
                $vars[1] = substr_replace($vars[1], "",0, 1);
                $SYSLANG[$vars[0]] = $vars[1];
            }
        }
        $_SESSION['syslang'] = $SYSLANG;
    }
    else
    {
        die("File specified in \$CONFIG['default_i18n'] can't be found");
    }
}


/**
* Outputs a user's contract associate, if the viewing user is allowed
*
* @param $userid ID of the user
* @return string output html
* @author Kieran Hogg
*/
function user_contracts_table($userid, $mode = 'internal')
{
    global $now, $CONFIG, $sit;
    if ((!empty($sit[2]) AND user_permission($sit[2], 30)
    OR ($_SESSION['usertype'] == 'admin'))) // view supported products
    {
        $html .= "<h4>".icon('contract', 16)." {$GLOBALS['strContracts']}:</h4>";
        $sql  = "SELECT sc.maintenanceid AS maintenanceid, m.product, p.name AS productname, ";
        $sql .= "m.expirydate, m.term ";
        $sql .= "FROM `{$GLOBALS['dbSupportContacts']}` AS sc, ";
        $sql .= "`{$GLOBALS['dbMaintenance']}` AS m, ";
        $sql .= "`{$GLOBALS['dbProducts']}` AS p, ";
        $sql .= "`{$GLOBALS['dbContacts']}` AS c ";
        $sql .= "WHERE ((sc.maintenanceid=m.id AND sc.contactid='$userid') ";
        $sql .= "OR m.allcontactssupported = 'yes') ";
        $sql .= "AND m.product=p.id  ";
        $sql .= "AND c.id = '{$userid}' ";
        $sql .= "AND m.site = c.siteid ";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        if (mysql_num_rows($result)>0)
        {
            $html .= "<table align='center' class='vertical'>";
            $html .= "<tr>";
            $html .= "<th>{$GLOBALS['strID']}</th><th>{$GLOBALS['strProduct']}</th><th>{$GLOBALS['strExpiryDate']}</th>";
            $html .= "</tr>\n";

            $supportcount=1;
            $shade='shade2';
            while ($supportedrow = mysql_fetch_array($result))
            {
                if ($supportedrow['term'] == 'yes')
                {
                    $shade='expired';
                }

                if ($supportedrow['expirydate'] < $now AND $supportedrow['expirydate'] != -1)
                {
                    $shade='expired';
                }

                $html .= "<tr><td class='$shade'>";
                $html .= ''.icon('contract', 16)." ";
                if ($mode == 'internal')
                {
                    $html .= "<a href='contract_details.php?id=";
                }
                else
                {
                    $html .= "<a href='contracts.php?id=";
                }
                $html .= "{$supportedrow['maintenanceid']}'>";
                $html .= "{$GLOBALS['strContract']}: ";
                $html .= "{$supportedrow['maintenanceid']}</a></td>";
                $html .= "<td class='$shade'>{$supportedrow['productname']}</td>";
                $html .= "<td class='$shade'>";
                if ($supportedrow['expirydate'] == -1)
                {
                    $html .= $GLOBALS['strUnlimited'];
                }
                else
                {
                    $html .= ldate($CONFIG['dateformat_date'], $supportedrow['expirydate']);
                }
                if ($supportedrow['term'] == 'yes')
                {
                    $html .= " {$GLOBALS['strTerminated']}";
                }

                $html .= "</td>";
                $html .= "</tr>\n";
                $supportcount++;
                $shade = 'shade2';
            }
            $html .= "</table>\n";
        }
        else
        {
            $html .= "<p align='center'>{$GLOBALS['strNone']}</p>\n";
        }

        if ($mode == 'internal')
        {
            $html .= "<p align='center'>";
            $html .= "<a href='add_contact_support_contract.php?contactid={$userid}&amp;context=contact'>";
            $html .= "{$GLOBALS['strAssociateContactWithContract']}</a></p>\n";
        }

    }

    return $html;
}

// -------------------------- // -------------------------- // --------------------------
// leave this section at the bottom of functions.inc.php ================================

// Evaluate and Load plugins
if (is_array($CONFIG['plugins']))
{
    foreach ($CONFIG['plugins'] AS $plugin)
    {
        // Remove any dots
        $plugin = str_replace('.','',$plugin);
        // Remove any slashes

        $plugin = str_replace('/','',$plugin);
        if ($plugin != '')
        {
            include ("{$CONFIG['application_fspath']}plugins/{$plugin}.php");
        }
    }
}

/**
    * @author Ivan Lucas
*/
function plugin_register($context, $action)
{
    global $PLUGINACTIONS;
    $PLUGINACTIONS[$context][] = $action;
}


/**
    * @author Ivan Lucas
*/
function plugin_do($context, $optparams = FALSE)
{
    global $PLUGINACTIONS;
    $rtnvalue = '';
    if (is_array($PLUGINACTIONS[$context]))
    {
        foreach ($PLUGINACTIONS[$context] AS $action)
        {
            // Call Variable function (function with variable name)
            if ($optparams)
            {
                $rtn = $action($optparams);
            }
            else
            {
                $rtn = $action();
            }

            // Append return value
            if (is_array($rtn) AND is_array($rtnvalue))
            {
                array_push($rtnvalue, $rtn);
            }
            elseif (is_array($rtn) AND !is_array($rtnvalue))
            {
                $rtnvalue=array(); array_push($rtnvalue, $rtn);
            }
            else
            {
                $rtnvalue .= $rtn;
            }
        }
    }
    return $rtnvalue;
}


/**
  * @author Paul Heaney
  * @param dayofweek string.    'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' or 'holiday'
 */
function get_billable_multiplier($dayofweek, $hour, $billingmatrix = 1)
{
    $sql = "SELECT `{$dayofweek}` AS rate FROM {$GLOBALS['dbBillingMatrix']} WHERE hour = {$hour} AND id = {$billingmatrix}";

    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        return FALSE;
    }

    $rate = 1;

    if (mysql_num_rows($result) > 0)
    {
        $obj = mysql_fetch_object($result);
        $rate = $obj->rate;
    }

    return $rate;
}


/**
  * @author Paul Heaney
  * @param $contractid  The Contract ID
  * @param $date  UNIX timestamp. The function will look for service that is current as of this timestamp
  * @return mixed.     Service ID, or -1 if not found, or FALSE on error
 */
function get_serviceid($contractid, $date = '')
{
    global $now, $CONFIG;
    if (empty($date)) $date = $now;

    $sql = "SELECT serviceid FROM `{$GLOBALS['dbService']}` AS p ";
    $sql .= "WHERE contractid = {$contractid} AND UNIX_TIMESTAMP(startdate) <= {$date} ";
    $sql .= "AND UNIX_TIMESTAMP(enddate) > {$date} ";
    
    if (!$CONFIG['billing_allow_incident_approval_against_overdrawn_service'])
    {
    	$sql .= "AND balance > 0 ";
    }
    
    $sql .= "ORDER BY priority DESC, enddate, balance ASC LIMIT 1";

    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        return FALSE;
    }

    $serviceid = -1;

    if (mysql_num_rows($result) > 0)
    {
        list($serviceid) = mysql_fetch_row($result);
    }

    return $serviceid;
}


/**
 * Function to find the most applicable unit rate for a particular contract
 * @param $contractid - The contract id
 * @param $date UNIX timestamp. The function will look for service that is current as of this timestamp
 * @return int th eunit rate, -1 if non found
  * @author Paul Heaney
 */
function get_unit_rate($contractid, $date='')
{
    $serviceid = get_serviceid($contractid, $date);

    $sql = "SELECT unitrate FROM `{$GLOBALS['dbService']}` AS p WHERE serviceid = {$serviceid}";

    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        return FALSE;
    }

    $unitrate = -1;

    if (mysql_num_rows($result) > 0)
    {
        $obj = mysql_fetch_object($result);
        $unitrate = $obj->unitrate;
    }

    return $unitrate;
}


/**
 * Function passed a day, month and year to identify if this day is defined as a public holiday
 * @author Paul Heaney
 * FIXME this is horribily inefficient, we should load a table ONCE with all the public holidays
         and then just check that with this function
 */
function is_day_bank_holiday($day, $month, $year)
{
    global $dbHolidays;

    $date = mktime(0, 0, 0, $month, $year, $year);
    $sql = "SELECT * FROM `{$dbHolidays}` WHERE type = 10 AND startdate = {$date}";

    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_ERROR);
        return FALSE;
    }

    if (mysql_num_rows($result) > 0) return TRUE;
    else return FALSE;
}



/**
 * Function to get an array of all billing multipliers for a billing matrix
 * @author Paul Heaney
 */
function get_all_available_multipliers($matrixid=1)
{
    $days = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'holiday');

    foreach ($days AS $d)
    {
        $sql = "SELECT DISTINCT({$d}) AS day FROM `{$GLOBALS['dbBillingMatrix']}` WHERE id = {$matrixid}";
        $result = mysql_query($sql);
        if (mysql_error())
        {
            trigger_error(mysql_error(),E_USER_WARNING);
            return FALSE;
        }

        while ($obj = mysql_fetch_object($result))
        {
            $a[$obj->day] = $obj->day;
        }
    }

    ksort($a);

    return $a;
}


/**
 * Function to identofy if incident has been approved for billing
 * @returns TRUE for approved, FALSE otherwise
 * @author Paul Heaney
 */
function is_billable_incident_approved($incidentid)
{
    global $dbLinks, $dbLinkTypes;

    $sql = "SELECT DISTINCT origcolref, linkcolref ";
    $sql .= "FROM `{$dbLinks}` AS l, `{$dbLinkTypes}` AS lt ";
    $sql .= "WHERE l.linktype = 6 ";
    $sql .= "AND linkcolref = {$incidentid} ";
    $sql .= "AND direction = 'left'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    if (mysql_num_rows($result) > 0) return TRUE;
    else return FALSE;
}


/**
    * Get the current contract balance
    * @author Ivan Lucas
    * @param $contractid int. Contract ID of the contract to credit
    * @param $includenonapproved boolean. Include incidents which have not been approved
    * @note The balance is a sum of all the current service that have remaining balance
    * @todo FIXME add a param that makes this optionally show the incident pool balance
      in the case of non-timed type contracts
*/
function get_contract_balance($contractid, $includenonapproved = FALSE)
{
    global $dbService, $now;
    $balance = 0.00;

    $sql = "SELECT SUM(balance) FROM `{$dbService}` ";
    $sql .= "WHERE contractid = $contractid AND UNIX_TIMESTAMP(startdate) <= $now ";
    $sql .= "AND UNIX_TIMESTAMP(enddate) >= $now  ";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
    list($balance) = mysql_fetch_row($result);

    if ($includenonapproved)
    {
        // Need to get sum of non approved incidents for this contract and deduct

        $balance -= total_awaiting_approval($contractid);
    }

    return $balance;
}


// TODO document (PH)
function total_awaiting_approval($contractid)
{
    $sqlcontract = "SELECT i.* FROM `{$GLOBALS['dbIncidents']}` AS i, `{$GLOBALS['dbServiceLevels']}` AS sl ";
    $sqlcontract .= "WHERE sl.tag = i.servicelevel AND sl.priority = i.priority AND sl.timed = 'yes' ";
    $sqlcontract .= "AND i.status = 2 AND i.maintenanceid = {$contractid}";

    $resultcontract = mysql_query($sqlcontract);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

    $cost = 0;

    if (mysql_num_rows($resultcontract) > 0)
    {
        $multipliers = get_all_available_multipliers();

        while($obj = mysql_fetch_object($resultcontract))
        {
            $billableunitsincident = 0;

            $unitrate = get_unit_rate(incident_maintid($obj->id));

            if (!is_billable_incident_approved($obj->id))
            {
                $a = make_incident_billing_array($obj->id);

                if ($a[-1]['totalcustomerperiods'] > 0)
                {
                    $bills = get_incident_billable_breakdown_array($obj->id);

                    foreach ($bills AS $bill)
                    {
                        foreach ($multipliers AS $m)
                        {
                            if (!empty($bill[$m]))
                            {
                                $billableunitsincident += $m * $bill[$m]['count'];
                            }
                        }
                    }

                    $cost += (($billableunitsincident + $a[-1]['refunds']) * $unitrate);
                }
            }
        }
    }
    return $cost;
}

/**
    * Update contract balance by an amount and log a transaction to record the change
    * @author Ivan Lucas
    * @param $contractid int. Contract ID of the contract to credit
    * @param $description string. A useful description of the transaction
    * @param $amount. float. The amount to credit or debit to the contract balance
                      positive for credit and negative for debit
    * @param $serviceid    int.    optional serviceid to use. This is calculated if ommitted.
    * @return boolean - status of the balance update
    * @note The actual service to credit will be calculated automatically if not specified
*/
function update_contract_balance($contractid, $description, $amount, $serviceid='')
{
    global $now, $dbService, $dbTransactions;
    $rtnvalue = TRUE;

    if ($serviceid == '')
    {
        // Find the correct service record to update
        $serviceid = get_serviceid($contractid);
        if ($serviceid < 1) trigger_error("Invalid service ID",E_USER_ERROR);
    }

    if (trim($amount) == '') $amount = 0;
    $date = date('Y-m-d H:i:s', $now);

    // Update the balance
    $sql = "UPDATE `{$dbService}` SET balance = (balance + {$amount}) WHERE serviceid = '{$serviceid}' LIMIT 1";
    mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_ERROR);
        $rtnvalue = FALSE;
    }

    if (mysql_affected_rows() < 1 AND $amount != 0)
    {
        trigger_error("Contract balance update failed",E_USER_ERROR);
        $rtnvalue = FALSE;
    }

    if ($rtnvalue != FALSE)
    {
        // Log the transaction
        $sql = "INSERT INTO `{$dbTransactions}` (serviceid, amount, description, userid, date) ";
        $sql .= "VALUES ('{$serviceid}', '{$amount}', '{$description}', '{$_SESSION['userid']}', '{$date}')";
        $result = mysql_query($sql);

        $rtnvalue = mysql_insert_id();

        if (mysql_error())
        {
            trigger_error(mysql_error(),E_USER_ERROR);
            $rtnvalue = FALSE;
        }
        if (mysql_affected_rows() < 1)
        {
            trigger_error("Transaction insert failed",E_USER_ERROR);
            $rtnvalue = FALSE;
        }
    }

    return $rtnvalue;
}



/**
 * Function to approve an incident, this adds a transaction and confirms the 'bill' is correct.
 * @author Paul Heaney
 * @param incidentid ID of the incident to approve
 */
function approve_incident($incidentid)
{
    global $dbLinks, $sit, $CONFIG, $strUnits;

    $rtnvalue = TRUE;

    if (!is_billable_incident_approved($incidentid))
    {
        $bills = get_incident_billable_breakdown_array($incidentid);

        $multipliers = get_all_available_multipliers();

        $numberofunits = 0;

        foreach ($bills AS $bill)
        {
            foreach ($multipliers AS $m)
            {
                $a[$m] += $bill[$m]['count'];
            }
        }

        foreach ($multipliers AS $m)
        {
            $s .= sprintf($GLOBALS['strXUnitsAtX'], $a[$m], $m);
            $numberofunits += ($m * $a[$m]);
        }

        $unitrate = get_unit_rate(incident_maintid($incidentid));

        $numberofunits += $bills['refunds'];

        $cost = ($numberofunits * $unitrate) * -1;

        $desc = trim("{$numberofunits} {$strUnits} @ {$CONFIG['currency_symbol']}{$unitrate} for incident {$incidentid}. {$s}"); //FIXME i18n

        $rtn = update_contract_balance(incident_maintid($incidentid), $desc, $cost);

        if ($rtn != FALSE)
        {

            $sql = "INSERT INTO `{$dbLinks}` VALUES (6, {$rtn}, {$incidentid}, 'left', {$sit[2]})";
            mysql_query($sql);
            if (mysql_error())
            {
                trigger_error(mysql_error(),E_USER_ERROR);
                $rtnvalue = FALSE;
            }
            if (mysql_affected_rows() < 1)
            {
                trigger_error("Approval failed",E_USER_ERROR);
                $rtnvalue = FALSE;
            }
        }
    }
    else
    {
        $rtnvalue = FALSE;
    }

    return $rtnvalue;
}



function update_last_billed_time($serviceid, $date)
{
    global $dbService;

    $rtnvalue = FALSE;

    if (!empty($serviceid) AND !empty($date))
    {
        $rtnvalue = TRUE;
        $sql .= "UPDATE `{$dbService}` SET lastbilled = '{$date}' WHERE serviceid = {$serviceid}";
        mysql_query($sql);
        if (mysql_error())
        {
            trigger_error(mysql_error(),E_USER_ERROR);
            $rtnvalue = FALSE;
        }

        if (mysql_affected_rows() < 1)
        {
            trigger_error("Approval failed",E_USER_ERROR);
            $rtnvalue = FALSE;
        }
    }

    return $rtnvalue;
}

/**
    * HTML table showing a summary of current contract service periods
    * @author Ivan Lucas
    * @param $contractid int. Contract ID of the contract to show service for
    * @returns string. HTML table
*/
function contract_service_table($contractid)
{
    global $CONFIG, $dbService;

    $sql = "SELECT * FROM `{$dbService}` WHERE contractid = {$contractid} ORDER BY enddate DESC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    if (mysql_num_rows($result) > 0)
    {
        $shade = '';
        $html = "\n<table align='center'>";
        $html .= "<tr><th>{$GLOBALS['strStartDate']}</th><th>{$GLOBALS['strEndDate']}</th><th>{$GLOBALS['strRemainingBalance']}</th><th></th>";
        $html .= "</tr>\n";
        while ($service = mysql_fetch_object($result))
        {
            $service->startdate = mysql2date($service->startdate);
            $service->enddate = mysql2date($service->enddate);
            $service->lastbilled = mysql2date($service->lastbilled);
            $html .= "<tr class='$shade'>";
            $html .= "<td><a href='transactions.php?serviceid={$service->serviceid}' class='info'>".ldate($CONFIG['dateformat_date'],$service->startdate);

            $span = '';
            if (!empty($service->notes))
            {
                $span .= "<strong>{$GLOBALS['strNotes']}</strong>: {$service->notes}<br />";
            }

            if ($service->creditamount != 0)
            {
                $span .= "<strong>{$GLOBALS['strAmount']}</strong>: {$CONFIG['currency_symbol']}".number_format($service->creditamount, 2)."<br />";
            }

            if ($service->unitrate != 0)
            {
                $span .= "<strong>{$GLOBALS['strUnitRate']}</strong>: {$CONFIG['currency_symbol']}{$service->unitrate}<br />";
            }

            if ($service->lastbilled > 0)
            {
                $span .= "<strong>{$strLastBilled}</strong>: ".ldate($CONFIG['dateformat_date'], $service->lastbilled);
            }

            if (!empty($span))
            {
                    $html .= "<span>{$span}</span>";
            }

            $html .= "</a></td>";
            $html .= "<td>";
            $html .= ldate($CONFIG['dateformat_date'], $service->enddate)."</td>";

            $html .= "<td>{$CONFIG['currency_symbol']}".number_format($service->balance, 2)."</td>";
            $html .= "<td><a href='billing/edit_service.php?mode=editservice&amp;serviceid={$service->serviceid}&amp;contractid={$contractid}'>{$GLOBALS['strEditService']}</a> | ";
            $html .= "<a href='billing/edit_service.php?mode=showform&amp;sourceservice={$service->serviceid}&amp;contractid={$contractid}'>{$GLOBALS['strEditBalance']}</a></td>";
            $html .= "</tr>\n";
        }
        $html .= "</table>\n";
        if ($shade == 'shade1') $shade = 'shade2';
        else $shade = 'shade1';
    }
    return $html;
}


/**
    * @author Ivan Lucas
    * @param $contractid int. Contract ID of the contract to show a balance for
    * @returns int. Number of available units according to the service balances and unit rates
    * @todo Use the includenonapproved variable and calc non approved incidents
**/
function contract_unit_balance($contractid, $includenonapproved = FALSE)
{
    global $now, $dbService;

    $unitbalance = 0;

    $sql = "SELECT * FROM `{$dbService}` WHERE contractid = {$contractid} ORDER BY enddate DESC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

    if (mysql_num_rows($result) > 0)
    {
        while ($service = mysql_fetch_object($result))
        {
            $multiplier = get_billable_multiplier(strtolower(date('D', $now)), date('G', $now));
            $unitamount = $service->unitrate * $multiplier;
            if ($unitamount > 0) $unitbalance += round($service->balance / $unitamount);
        }
    }

    return $unitbalance;
}


/**
 * Returns if the contact has a timed contract or if the site does in the case of the contact not.
 * @author Paul Heaney
 * @return either NO_BILLABLE_CONTRACT, CONTACT_HAS_BILLABLE_CONTRACT or SITE_HAS_BILLABLE_CONTRACT the latter is if the site has a billable contract by the contact isn't a named contact
 */
function does_contact_have_billable_contract($contactid)
{
    global $now;
    $return = NO_BILLABLE_CONTRACT;

    $siteid = contact_siteid($contactid);
    $sql = "SELECT DISTINCT m.id FROM `{$GLOBALS['dbMaintenance']}` AS m, `{$GLOBALS['dbServiceLevels']}` AS sl ";
    $sql .= "WHERE m.servicelevelid = sl.id AND sl.timed = 'yes' AND m.site = {$siteid} ";
    $sql .= "AND m.expirydate > {$now} AND m.term != 'yes'";
    $result = mysql_query($sql);

    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

    if (mysql_num_rows($result) > 0)
    {
        // We have some billable/timed contracts
        $return = SITE_HAS_BILLABLE_CONTRACT;

        // check if the contact is listed on one of these

        while ($obj = mysql_fetch_object($result))
        {
            $sqlcontact = "SELECT * FROM `{$GLOBALS['dbSupportContacts']}` ";
            $sqlcontact .= "WHERE maintenanceid = {$obj->id} AND contactid = {$contactid}";

            $resultcontact = mysql_query($sqlcontact);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            if (mysql_num_rows($resultcontact) > 0)
            {
                $return = CONTACT_HAS_BILLABLE_CONTRACT;
                break;
            }
        }
    }

    return $return;
}


/**
 * Gets the billable contract ID for a contact, if multiple exist then the first one is choosen
 * @author Paul Heaney
 * @param int $contactid - The contact ID you want to find the contract for
 * @return int the ID of the contract, -1 if not found
 */
function get_billable_contract_id($contactid)
{
    global $now;

    $return = -1;

    $siteid = contact_siteid($contactid);
    $sql = "SELECT DISTINCT m.id FROM `{$GLOBALS['dbMaintenance']}` AS m, `{$GLOBALS['dbServiceLevels']}` AS sl ";
    $sql .= "WHERE m.servicelevelid = sl.id AND sl.timed = 'yes' AND m.site = {$siteid} ";
    $sql .= "AND m.expirydate > {$now} AND m.term != 'yes'";

    $result = mysql_query($sql);

    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

    if (mysql_num_rows($result) > 0)
    {
        $return = mysql_fetch_object($result)->id;
    }

    return $return;
}


/**
 * Function to display/generate the transactions table
 * @author Paul Heaney
 * @param int $serviceid - The service ID to show transactons for
 * @param Date $startdate - Date in format yyyy-mm-dd when you want to start the report from
 * @param Date $enddate - Date in  format yyyy-mm-dd when you want to end the report, empty means today
 * @param int[] $sites - Array of sites to report on
 * @param String $display either csv or html
 * @param boolean $sitebreakdown - Breakdown per site
 * @param boolean showfoc - Show free of charge as well (defaults to true);
 * @return String -either HTML or CSV
 */
function transactions_report($serviceid, $startdate, $enddate, $sites, $display, $sitebreakdown=TRUE, $showfoc=TRUE, $focaszero=FALSE)
{
	global $CONFIG;

    $csv_currency = html_entity_decode($CONFIG['currency_symbol'], ENT_NOQUOTES, "ISO-8859-15"); // Note using -15 as -1 doesnt support euro

	$sql = "SELECT DISTINCT t.*, m.site FROM `{$GLOBALS['dbTransactions']}` AS t, `{$GLOBALS['dbService']}` AS p, ";
	$sql .= "`{$GLOBALS['dbMaintenance']}` AS m, `{$GLOBALS['dbServiceLevels']}` AS sl, `{$GLOBALS['dbSites']}` AS s ";
	$sql .= "WHERE t.serviceid = p.serviceid AND p.contractid = m.id "; // AND t.date <= '{$enddateorig}' ";
	$sql .= "AND m.servicelevelid = sl.id AND sl.timed = 'yes' AND m.site = s.id ";
	//// $sql .= "AND t.date > p.lastbilled AND m.site = {$objsite->site} ";
	if ($serviceid > 0) $sql .= "AND t.serviceid = {$serviceid} ";
	if (!empty($startdate)) $sql .= "AND t.date >= '{$startdate}' ";
	if (!empty($enddate)) $sql .= "AND t.date <= '{$enddate}' ";

    if (!showfoc) $sql .= "AND s.foc = 'no' ";

	if (!empty($sites))
	{
	    $sitestr = '';

	    foreach ($sites AS $s)
	    {
	        if (empty($sitestr)) $sitestr .= "m.site = {$s} ";
	        else $sitestr .= "OR m.site = {$s} ";
	    }

	    $sql .= "AND {$sitestr} ";
	}

	if (!empty($site)) $sql .= "AND m.site = {$site} ";

	$sql .= "ORDER BY s.name ";

	$result = mysql_query($sql);
	if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

	if (mysql_num_rows($result) > 0)
	{
	    $shade = 'shade1';

	    $total = 0;
	    $totalcredit = 0;
	    $totaldebit = 0;

	    while ($transaction = mysql_fetch_object($result))
	    {
	        if ($display == 'html')
	        {
	            $str = "<tr class='$shade'>";
	            $str .= "<td>{$transaction->date}</td>";
	            $str .= "<td>{$transaction->transactionid}</td>";
	            $str .= "<td>{$transaction->serviceid}</td>";
	            $str .= "<td>".site_name($transaction->site)."</td>";
	            $str .= "<td>{$transaction->description}</td>";
	        }
	        elseif ($display == 'csv')
	        {
	            $str = "\"{$transaction->date}\",";
	            $str .= "\"{$transaction->transactionid}\",";
	            $str .= "\"{$transaction->serviceid}\",\"";
	            $str .= site_name($transaction->site)."\",";
	            $str .= "\"".html_entity_decode($transaction->description)."\",";
	        }

            if ($focaszero)
            {
            	$transaction->amount = 0;
            }

	        $total += $transaction->amount;
	        if ($transaction->amount < 0)
	        {
	            $totaldebit += $transaction->amount;
	            if ($display == 'html')
	            {
	                $str .= "<td></td><td>{$CONFIG['currency_symbol']}".number_format($transaction->amount, 2)."</td>";
	            }
	            elseif ($display == 'csv')
	            {
	                $str .= ",\"{$csv_currency}".number_format($transaction->amount, 2)."\",";
	            }
	        }
	        else
	        {
	            $totalcredit += $transaction->amount;
	            if ($display == 'html')
	            {
	                $str .= "<td>{$CONFIG['currency_symbol']}".number_format($transaction->amount, 2)."</td><td></td>";
	            }
	            elseif ($display == 'csv')
	            {
	                $str .= "\"{$csv_currency}".number_format($transaction->amount, 2)."\",,";
	            }
	        }

	        if ($display == 'html') $str .= "</tr>";
	        elseif ($display == 'csv') $str .= "\n";

	        if ($sitebreakdown == TRUE)
	        {
	            $table[$transaction->site]['site'] = site_name($transaction->site);
	            $table[$transaction->site]['str'] .= $str;
	            if ($transaction->amount < 0)
	            {
	                $table[$transaction->site]['debit'] += $transaction->amount;
	            }
	            else
	            {
	                $table[$transaction->site]['credit'] += $transaction->amount;
	            }
	        }
	        else
	        {
	            $table .= $str;
	        }
	    }

	    if ($sitebreakdown == TRUE)
	    {
	        foreach ($table AS $e)
	        {
	            if ($display == 'html')
	            {
	                $text .= "<h3>{$e['site']}</h3>";
	                $text .= "<table align='center'  width='60%'>";
	                //echo "<tr><th colspan='7'>{$e['site']}</th></tr>";
	                $text .= "<tr><th>{$GLOBALS['strDate']}</th><th>{$GLOBALS['strID']}</th><th>{$GLOBALS['strServiceID']}</th>";
	                $text .= "<th>{$GLOBALS['strSite']}</th><th>{$GLOBALS['strDescription']}</th><th>{$GLOBALS['strCredit']}</th><th>{$GLOBALS['strDebit']}</th></tr>";
	                $text .= $e['str'];
	                $text .= "<tr><td colspan='5' align='right'>{$GLOBALS['strTotal']}</td>";
	                $text .= "<td>{$CONFIG['currency_symbol']}".number_format($e['credit'], 2)."</td>";
	                $text .= "<td>{$CONFIG['currency_symbol']}".number_format($e['debit'], 2)."</td></tr>";
	                $text .= "</table>";
	            }
	            elseif ($display == 'csv')
	            {
	                $text .= "\"{$e['site']}\"\n\n";
	                $text .= "\"{$GLOBALS['strDate']}\",\"{$GLOBALS['strID']}\",\"{$GLOBALS['strServiceID']}\",";
	                $text .= "\"{$GLOBALS['strSite']}\",\"{$GLOBALS['strDescription']}\",\"{$GLOBALS['strCredit']}\",\"{$GLOBALS['strDebit']}\"\n";
	                $text .= $e['str'];
	                $text .= ",,,,{$GLOBALS['strTotal']},";
	                $text .= "\"{$csv_currency}".number_format($e['credit'], 2)."\",\"";
	                $text .="{$csv_currency}".number_format($e['debit'], 2)."\"\n";
	            }
	        }
	    }
	    else
	    {
	        if ($display == 'html')
	        {
	            $text .= "<table align='center'>";
	            $text .= "<tr><th>{$GLOBALS['strDate']}</th><th>{$GLOBALS['strID']}</th><th>{$GLOBALS['strServiceID']}</th>";
	            $text .= "<th>{$GLOBALS['strSite']}</th>";
	            $text .= "<th>{$GLOBALS['strDescription']}</th><th>{$GLOBALS['strCredit']}</th><th>{$GLOBALS['strDebit']}</th></tr>";
	            $text .= $table;
	            $text .= "<tr><td colspan='5' align='right'>{$strTOTALS}</td>";
	            $text .= "<td>{$CONFIG['currency_symbol']}".number_format($totalcredit, 2)."</td>";
	            $text .= "<td>{$CONFIG['currency_symbol']}".number_format($totaldebit, 2)."</td></tr>";
	            $text .= "</table>";
	        }
	        elseif ($display == 'csv')
	        {
	            $text .= "\"{$GLOBALS['strDate']}\",\"{$GLOBALS['strID']}\",\"{$GLOBALS['strServiceID']}\",";
	            $text .= "\"{$GLOBALS['strSite']}\",";
	            $text .= "\"{$GLOBALS['strDescription']}\",\"{$GLOBALS['strCredit']}\",\"{$GLOBALS['strDebit']}\"\n";
	            $text .= $table;
	            $text .= ",,,,{$GLOBALS['strTOTALS']},";
	            $text .= "\"{$csv_currency}".number_format($totalcredit, 2)."\",\"";
	            $text .= "{$csv_currency}".number_format($totaldebit, 2)."\"\n";
	        }
	    }


	    if ($shade == 'shade1') $shade = 'shade2';
	    else $shade = 'shade1';
	}
	else
	{
	    if ($display == 'html')
	    {
	        $text = "<p align='center'>{$GLOBALS['strNoTransactionsMatchYourSearch']}</p>";
	    }
	    elseif ($display == 'csv')
	    {
	        $text = $GLOBALS['strNoTransactionsMatchYourSearch']."\n";
	    }
	}

	return $text;
}


/**
 * Outputs a table or csv file based on csv-based array
 * @author Kieran Hogg
 * @param array $data Array of data, see @note for format
 * @param string $ouput Whether to show a table or create a csv file
 * @return string $html The html to produce the output
 * @note format: $array[] = 'Colheader1,Colheader2'; $array[] = 'data1,data2';
 */
function create_report($data, $output = 'table', $filename = 'report.csv')
{
    if ($output == 'table')
    {
        $html = "\n<table align='center'><tr>\n";
        $data = explode("\n", $data);
        $headers = explode(',', $data[0]);
        $rows = sizeof($headers);
        foreach ($headers as $header)
        {
            $html .= colheader($header, $header);
        }
        $html .= "</tr>";

        if (sizeof($data) == 1)
        {
            $html .= "<tr><td rowspan='{$rows}'>{$GLOBALS['strNoRecords']}</td></tr>";
        }
        else
        {
            // use 1 -> sizeof as we've already done one row
            for ($i = 1; $i < sizeof($data); $i++)
            {
                $html .= "<tr>";
                $values = explode(',', $data[$i]);
                foreach ($values as $value)
                {
                    $html .= "<td>$value</td>";
                }
                $html .= "</tr>";
            }
        }
        $html .= "</table>";
    }
    else
    {
        $html = header("Content-type: text/csv\r\n");
        $html .= header("Content-disposition-type: attachment\r\n");
        $html .= header("Content-disposition: filename={$filename}");

        foreach($data as $line)
        {
            $html .= $line;
        }
    }

    return $html;
}


/**
 * Postpones a task's due date 24 hours
 * @author Kieran Hogg
 * @param int $taskid The ID of the task to postpone
 */
function postpone_task($taskid)
{
    global $dbTasks;
    if (is_numeric($taskid))
    {
        $sql = "SELECT duedate FROM `{$dbTasks}` AS t ";
        $sql .= "WHERE id = '{$taskid}'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        $task = mysql_fetch_object($result);
        if ($task->duedate != "0000-00-00 00:00:00")
        {
            $newtime = date("Y-m-d H:i:s", (mysql2date($task->duedate) + 60 * 60 * 24));
            $sql = "UPDATE `{$dbTasks}` SET duedate = '{$newtime}' WHERE id = '{$taskid}'";
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        }
    }
}


/**
 * Returns HTML for a gravatar (Globally recognised avatar)
 * @author Ivan Lucas
 * @param string $email - Email address
 * @param int $size - Size in pixels
 * @returns string - HTML img tag
 */
function gravatar($email, $size)
{
    global $CONFIG, $iconset;
    $default = $CONFIG['default_gravatar'];

    $grav_url = "http://www.gravatar.com/avatar.php?
                 gravatar_id=".md5(strtolower($email)).
                "&default=".urlencode($CONFIG['default_gravatar']).
                "&size=".$size;

    $html = "<img src='{$grav_url}' />";

    return $html;
}


/**
 * Returns the percentage remaining for ALL services on a contract
 * @author Kieran Hogg
 * @param string $mainid - contract ID
 * @returns mixed - percentage between 0 and 1 if services, FALSE if not
 */
function get_service_percentage($maintid)
{
    global $dbService;
    if (does_contact_have_billable_contract(maintenance_siteid($maintid)) != NO_BILLABLE_CONTRACT)
    {
        $sql = "SELECT * FROM `{$dbService}` ";
        $sql .= "WHERE contractid = '{$maintid}'";
        $result = mysql_query($sql);
        while ($service = mysql_fetch_object($result))
        {
            $total += (float) $service->balance / (float) $service->creditamount;
            $num++;
        }
        $return = (float) $total / (float) $num;
    }
    else
    {
        $return = FALSE;
    }
    
    return $return;
}


// ** Place no more function defs below this **


// These are the modules that we are dependent on, without these something
// or everything will fail, so let's throw an error here.
// Check that the correct modules are loaded
if (!extension_loaded('pspell')) $CONFIG['enable_spellchecker'] = FALSE; // FORCE Turn off spelling if module not found
if (!extension_loaded('mysql')) trigger_error('SiT requires the php/mysql module', E_USER_ERROR);
if (!extension_loaded('imap') AND $CONFIG['enable_inbound_mail'] == 'POP/IMAP')
{
    trigger_error('SiT requires the php IMAP module to recieve incoming mail.'
                  .' If you really don\'t need this, you can set \$CONFIG[\'enable_inbound_mail\'] to false');
}
if (version_compare(PHP_VERSION, "5.0.0", "<")) trigger_error('INFO: You are running an older PHP version, some features may not work properly.', E_USER_NOTICE);
if (@ini_get('register_globals') == 1 OR strtolower(@ini_get('register_globals')) == 'on')
{
    trigger_error('Error: php.ini MUST have register_globals set to off, there are potential security risks involved with leaving it as it is!', E_USER_ERROR);
}
?>
