<?php
// defaults.inc.php - Provide configuration defaults
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
//  Author: Ivan Lucas
//  Notes: These variables are overwridden by config.inc.php and/or webtrack.conf or sit.conf

###########################################################
####                                                   ####
####  IMPORTANT:                                       ####
####                                                   ####
####    Don't modify this file to configure your       ####
####    SiT installation, instead edit the             ####
####    config.inc.php file.                           ####
####                                                   ####
####    If you don't have a config.inc.php file        ####
####    you can use the config.inc-dist.php file       ####
####    as a template.                                 ####
####                                                   ####
###########################################################

$CONFIG['application_name'] = 'SiT! Support Incident Tracker';
$CONFIG['application_shortname'] = 'SiT!';

// The path to SiT! in your filesystem (e.g. /var/www/vhtdocs/sit/
$CONFIG['application_fspath'] = '';
$CONFIG['application_webpath'] = '/';

// The URI prefix to use when referring to this application (in emails etc.)
$CONFIG['application_uriprefix'] = "http://{$_SERVER['HTTP_HOST']}";

$CONFIG['db_hostname'] = 'localhost';
$CONFIG['db_username'] = '';
$CONFIG['db_password'] = '';
// the name of the database to use
$CONFIG['db_database'] = 'sit';

// Prefix database tables with the a string (e.g. 'sit_', use this if the database you are using is shared with other applications
$CONFIG['db_tableprefix'] = '';

$CONFIG['home_country'] = 'UNITED KINGDOM';

$CONFIG['support_email'] = 'support@localhost';
$CONFIG['sales_email'] = 'sales@localhost';
$CONFIG['support_manager_email'] = 'support_manager@localhost';

// These are the settings for the account to download incoming mail from, settings POP/IMAP or MTA (for piping message in)
$CONFIG['enable_inbound_mail'] = 'POP/IMAP';
$CONFIG['email_username'] = '';
$CONFIG['email_password'] = '';
$CONFIG['email_address'] = '';
$CONFIG['email_server'] = '';
//'imap' or 'pop'
$CONFIG['email_servertype'] = '';
// e.g. Gmail needs '/ssl', secure Groupwise needs /novalidate-cert etc.
// see http://uk2.php.net/imap_open for examples
$CONFIG['email_options'] = '';
$CONFIG['email_port'] = '';

$CONFIG['bugtracker_name'] = 'Bug Tracker';
$CONFIG['bugtracker_url'] = 'http://sitracker.sourceforge.net/Bugs';

// See http://www.php.net/manual/en/function.date.php for help with date formats
$CONFIG['dateformat_datetime'] = 'jS M Y @ g:ia';
$CONFIG['dateformat_filedatetime'] = 'd/m/Y H:i';
$CONFIG['dateformat_shortdate'] = 'd/m/y';
$CONFIG['dateformat_shorttime'] = 'H:i';
$CONFIG['dateformat_date'] = 'jS M Y';
$CONFIG['dateformat_time'] = 'g:ia';
$CONFIG['dateformat_longdate'] = 'l jS F Y';

// Array containing working days (0=Sun, 1=Mon ... 6=Sat)
$CONFIG['working_days'] = array(1,2,3,4,5);
// Times of the start and end of the working day (in seconds)
$CONFIG['start_working_day'] = (9 * 3600);
$CONFIG['end_working_day'] = (17 * 3600);

$CONFIG['attachment_fspath'] = "/var/www/sit/attachments/";
$CONFIG['attachment_webpath'] = "attachments/";

// Incoming mail spool directory, the location of mail processed by mailfilter shell script
$CONFIG['mailin_spool_path'] = "{$CONFIG['application_fspath']}mailin/";

$CONFIG['upload_max_filesize'] = get_cfg_var('upload_max_filesize');
// Convert a PHP.INI integer value into a byte value

// FTP Server details, for file upload functionality
$CONFIG['ftp_hostname'] = '';
$CONFIG['ftp_username'] = '';
$CONFIG['ftp_password'] = '';

// Set whether to use passive mode ftp
$CONFIG['ftp_pasv'] = TRUE;
// The path to the directory where we store files, (e.g. /pub/support/) the trailing slash is important
$CONFIG['ftp_path'] = '/';

// Set to TRUE to enable spellchecking or FALSE to disable
$CONFIG['enable_spellchecker'] = FALSE;
// Spell check dictionaries
$CONFIG['main_dictionary_file'] = '/usr/share/dict/linux.words';
$CONFIG['custom_dictionary_file'] = "{$CONFIG['application_fspath']}dictionary/custom.words";

// The CSS file to use when no other is configured
$CONFIG['default_css_url'] = 'styles/sit8.css';

// The interface style that new users should use (user default style)
$CONFIG['default_interface_style'] = 8;

// Knowledgebase ID prefix, inserted before the ID to give it uniqueness
$CONFIG['kb_id_prefix'] = 'KB';
// Knowledgebase disclaimer, displayed at the bottom of every article
$CONFIG['kb_disclaimer_html']  = '<strong>THE INFORMATION IN THIS DOCUMENT IS PROVIDED ON AN AS-IS BASIS WITHOUT WARRANTY OF ANY KIND.</strong> ';
$CONFIG['kb_disclaimer_html'] .= 'PROVIDER SPECIFICALLY DISCLAIMS ANY OTHER WARRANTY, EXPRESS OR IMPLIED, INCLUDING ANY WARRANTY OF MERCHANTABILITY ';
$CONFIG['kb_disclaimer_html'] .= 'OR FITNESS FOR A PARTICULAR PURPOSE. IN NO EVENT SHALL PROVIDER BE LIABLE FOR ANY CONSEQUENTIAL, INDIRECT, SPECIAL ';
$CONFIG['kb_disclaimer_html'] .= 'OR INCIDENTAL DAMAGES, EVEN IF PROVIDER HAS BEEN ADVISED BY USER OF THE POSSIBILITY OF SUCH POTENTIAL LOSS OR DAMAGE. ';
$CONFIG['kb_disclaimer_html'] .= 'USER AGREES TO HOLD PROVIDER HARMLESS FROM AND AGAINST ANY AND ALL CLAIMS, LOSSES, LIABILITIES AND EXPENSES.';

// The service level to use in case the contact does not specify (text not the tag)
$CONFIG['default_service_level'] = 'SLA1';
// The number of days to elapse before we are prompted to contact the customer (usually overridden by SLA)
$CONFIG['regular_contact_days'] = 7;

// Number of free support incidents that can be logged to a site
$CONFIG['free_support_limit'] = 2;

// Comma seperated list specifying the numbers of incidents to assign to contracts
$CONFIG['incident_pools'] = '1,2,3,4,5,10,20,25,50,100,150,200,250,500,1000';

// Incident feedback form (the id number of the feedback form to use or empty to disable sending feedback forms out)
$CONFIG['feedback_form'] = '';

// If you set 'trusted_server' to TRUE, passwords will no longer be used or required, this assumes that you are using
// another mechanism for authentication
$CONFIG['trusted_server'] = FALSE;

// Lock records for (number of seconds)
$CONFIG['record_lock_delay'] = 1800;  // 30 minutes

// maximum no. of incoming emails per incident before a mail-loop is detected
$CONFIG['max_incoming_email_perday'] = 15;

$CONFIG['spam_forward'] = '';

// String to look for in email message subject to determine a message is spam
$CONFIG['spam_email_subject'] = 'SPAMASSASSIN';

$CONFIG['feedback_max_score'] = 9;

// Paths to various required files
$CONFIG['licensefile']= '../doc/LICENSE';
$CONFIG['changelogfile']= '../doc/Changelog';
$CONFIG['creditsfile']= '../doc/CREDITS';

// The session name for use in cookies and URL's, Must contain alphanumeric characters only
$CONFIG['session_name'] = 'SiTsessionID';


// Notice Threshold, flag items as 'notice' when they are this percentage complete.
$CONFIG['notice_threshold'] = 85;

// Urgent Threshold, flag items as 'urgent' when they are this percentage complete.
$CONFIG['urgent_threshold'] = 90;

// Urgent Threshold, flag items as 'critical' when they are this percentage complete.
$CONFIG['critical_threshold'] = 95;


// Run in demo mode, some features are disabled or replaced with mock-ups
$CONFIG['demo'] = FALSE;

// Output extra debug information, some as HTML comments and some in the page footer
$CONFIG['debug'] = FALSE;

// Enable user portal
$CONFIG['portal'] = TRUE;

// Journal Logging Level
//      0 = No logging
//      1 = Minimal Logging
//      2 = Normal Logging
//      3 = Full Logging
//      4 = Maximum/Debug Logging
$CONFIG['journal_loglevel'] = 3;

// How long should we keep journal entries, entries older than this will be purged (deleted)
$CONFIG['journal_purge_after'] = 60 * 60 * 24 * 180;  // 180 Days

// When left blank this defaults to $CONFIG['application_webpath'], setting that here will take the value of the default
$CONFIG['logout_url'] = '';

$CONFIG['error_logfile'] = "{$CONFIG['application_fspath']}logs/sit.log";

// Filename to log authentication failures
$CONFIG['access_logfile'] = '';

// The plugins configuration is an array
//$CONFIG['plugins'] = array();
$CONFIG['plugins'] =array('');

// The URL for pages that do not exist yet.
$CONFIG['error_notavailable_url']="/?msg=not+available";

$CONFIG['no_feedback_contracts'] = array();

$CONFIG['preferred_maintenance'] = array();

// Use an icon for specified tags, format: array('tag' => 'icon', 'tag2' => 'icon2')";
$CONFIG['tag_icons'] = array ('redflag' => 'redflag', 'yellowflag' => 'yellowflag', 'blueflag' => 'blueflag', 'cyanflag' => 'cyanflag', 'greenflag' => 'greenflag', 'whiteflag' => 'whiteflag', 'blackflag' => 'blackflag');

// Default Internationalisation tag (rfc4646/rfc4647/ISO 639 code), note the corresponding i18n file must exist in includes/i18n before you can use it
$CONFIG['default_i18n'] = 'en-GB';

$CONFIG['timezone'] = 'Europe/London';

// Incidents closed more than this number of days ago aren't show in the incident queue, -1 means disabled
$CONFIG['hide_closed_incidents_older_than'] = 90;

// Following is still BETA
$CONFIG['auto_chase'] = FALSE;
$CONFIG['chase_email_minutes'] = 0; // number of minutes incident has been 'awaiting customer action' before sending a chasing email, 0 is disabled
$CONFIG['chase_phone_minutes'] = 0; // number of minutes incident has been 'awaiting customer action' before putting in the 'chase by phone queue', 0 is disabled
$CONFIG['chase_manager_minutes'] = 0; // number of minutes incident has been 'awaiting customer action' before putting in the 'chase manager queue', 0 is disabled
$CONFIG['chase_managers_manager_minutes'] = 0; // number of minutes incident has been 'awaiting customer action' before putting in the 'chase managers_manager queue', 0 is disabled
$CONFIG['chase_email_template'] = ''; // The template to use to send chase email
$CONFIG['dont_chase_maintids'] = array(1 => 1); // maintence IDs not to chase

//Enable/Disable sections
$CONFIG['kb_enabled'] = TRUE;
$CONFIG['portal_kb_enabled'] = TRUE;
$CONFIG['tasks_enabled'] = TRUE;
$CONFIG['calendar_enabled'] = TRUE;
$CONFIG['holidays_enabled'] = TRUE;
$CONFIG['feedback_enabled'] = TRUE;
$CONFIG['timesheets_enabled'] = FALSE;

$CONFIG['portal_site_incidents'] = TRUE; //users in the portal can view site incidents based on the contract options
$CONFIG['portal_usernames_can_be_changed'] = TRUE; //portal usernames can be changed by the users

// The interface style to use for the portal
$CONFIG['portal_interface_style'] = 16;

// incidents are automatically assigned based on a lottery weighted towards who
// are less busy, assumes everyone set to accepting is an engineer and willing to take incidents
$CONFIG['auto_assign_incidents'] = TRUE;

// Default role for new users, where 1 is admin, 2 is manager and 3 is user
$CONFIG['default_roleid'] = 3;

// Default gravatar, can be 'wavatar', 'identicon', 'monsterid' a URL to an image, or blank for a blue G
// see www.gravatar.com to learn about gravatars
$CONFIG['default_gravatar'] = 'identicon';

// Default for whom the billing reports should be mailed to, multiple address can be seperared by commas 
$CONFIG['billing_reports_email'] = 'admin@localhost';

// Allow incidents to be approved against overdrawn services
$CONFIG['billing_allow_incident_approval_against_overdrawn_service'] = TRUE;

$CONFIG['inventory_types']['cisco vpn'] = 'Cisco VPN';
$CONFIG['inventory_types']['go_to_my_pc'] = 'Go to my PC';
$CONFIG['inventory_types']['nortel vpn'] = 'Nortel VPN';
$CONFIG['inventory_types']['pc_anywhere'] = 'PC Anywhere';
$CONFIG['inventory_types']['rdp_tunneled_ssh'] = 'RDP tunneled through SSH';
$CONFIG['inventory_types']['rdp'] = 'RDP';
$CONFIG['inventory_types']['reverse_vnc'] = 'Reverse VNC';
$CONFIG['inventory_types']['server'] = 'Server';
$CONFIG['inventory_types']['software'] = 'Software';
$CONFIG['inventory_types']['ssh_port_tunneling'] = 'SSH (port tunneled)';
$CONFIG['inventory_types']['ssh'] = 'SSH';
$CONFIG['inventory_types']['ssl_vpn'] = 'SSL VPN';
$CONFIG['inventory_types']['vnc'] = 'VNC';
$CONFIG['inventory_types']['webex'] = 'Webex';
$CONFIG['inventory_types']['workstation'] = 'Workstation/PC';

?>