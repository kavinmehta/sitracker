<?php
// strings.inc.php - Set up strings
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Hierarchical Menus
/* Arrays containing menu options for the top menu, each menu has an associated permission number and this is used */
/* to decide which menu to display.  In addition each menu item has an associated permission   */
/* This is so we can decide whether a user should see the menu option or not.                                     */
/* perm = permission number */
/*
$hmenu[1031] = array (10=> array ( 'perm'=> 0, 'name'=> "Option1", 'url'=>""),
                      20=> array ( 'perm'=> 0, 'name'=> "Option2", 'url'=>""),
                      30=> array ( 'perm'=> 0, 'name'=> "Option3", 'url'=>"")
);
*/

//
// Main Menu
//
if (!is_array($hmenu[0])) $hmenu[0] = array();
$hmenu[0] = array_merge(array (10=> array ( 'perm'=> 0, 'name'=> $CONFIG['application_shortname'], 'url'=>"{$CONFIG['application_webpath']}main.php", 'submenu'=>"10"),
                   20=> array ( 'perm'=> 11, 'name'=> $strCustomers, 'url'=>"{$CONFIG['application_webpath']}browse_sites.php", 'submenu'=>"20"),
                   30=> array ( 'perm'=> 6, 'name'=> $strSupport, 'url'=>"{$CONFIG['application_webpath']}incidents.php?user=current&amp;queue=1&amp;type=support", 'submenu'=>"30"),
                   40=> array ( 'perm'=> 0, 'name'=> $strTasks, 'url'=>"{$CONFIG['application_webpath']}tasks.php", 'submenu'=>"40", 'enablevar' => 'tasks_enabled'),
                   50=> array ( 'perm'=> 54, 'name'=> $strKnowledgeBase, 'url'=>"{$CONFIG['application_webpath']}browse_kb.php", 'submenu'=>"50", 'enablevar' => 'kb_enabled'),
                   60=> array ( 'perm'=> 37, 'name'=> $strReports, 'url'=>"", 'submenu'=>"60"),
                   70=> array ( 'perm'=> 0, 'name'=> $strHelp, 'url'=>"{$CONFIG['application_webpath']}help.php", 'submenu'=>"70")
), $hmenu[0]);
if (!is_array($hmenu[10])) $hmenu[10] = array();
$hmenu[10] = array_merge(array (1=> array ( 'perm'=> 0, 'name'=> $strDashboard, 'url'=>"{$CONFIG['application_webpath']}main.php"),
                    10=> array ( 'perm'=> 60, 'name'=> $strSearch, 'url'=>"{$CONFIG['application_webpath']}search.php"),
                    20=> array ( 'perm'=> 4, 'name'=> $strMyDetails, 'url'=>"{$CONFIG['application_webpath']}edit_profile.php", 'submenu'=>"1020"),
                    30=> array ( 'perm'=> 4, 'name'=> $strControlPanel, 'url'=>"{$CONFIG['application_webpath']}control_panel.php", 'submenu'=>"1030"),
                    40=> array ( 'perm'=> 14, 'name'=> $strUsers, 'url'=>"{$CONFIG['application_webpath']}users.php", 'submenu'=>"1040"),
                    50=> array ( 'perm'=> 0, 'name'=> $strLogout, 'url'=>"{$CONFIG['application_webpath']}logout.php")
), $hmenu[10]);
$hmenu[1020] = array (10=> array ( 'perm'=> 4, 'name'=> $strMyProfile, 'url'=>"{$CONFIG['application_webpath']}edit_profile.php"),
                      20=> array ( 'perm'=> 58, 'name'=> $strMySkills, 'url'=>"{$CONFIG['application_webpath']}edit_user_skills.php"),
                      30=> array ( 'perm'=> 58, 'name'=> $strMySubstitutes, 'url'=>"{$CONFIG['application_webpath']}edit_backup_users.php"),
                      40=> array ( 'perm'=> 27, 'name'=> $strMyHolidays, 'url'=>"{$CONFIG['application_webpath']}holidays.php", 'enablevar' => 'holidays_enabled'),
                      50=> array ( 'perm'=> 4, 'name'=> $strMyDashboard, 'url'=>"{$CONFIG['application_webpath']}manage_user_dashboard.php")
);
// configure

// TODO v3.40 set a permission for triggers
if (!is_array($hmenu[1030])) $hmenu[1030] = array();
$hmenu[1030] = array_merge(array (10=> array ( 'perm'=> 22, 'name'=> $strUsers, 'url'=>"{$CONFIG['application_webpath']}manage_users.php", 'submenu'=>"103010"),
                      20=> array ( 'perm'=> 43, 'name'=> $strGlobalSignature, 'url'=>"{$CONFIG['application_webpath']}edit_global_signature.php"),
                      30=> array ( 'perm'=> 22, 'name'=> $strTemplates, 'url'=>"{$CONFIG['application_webpath']}templates.php"),
                      40=> array ( 'perm'=> 22, 'name'=> $strSetPublicHolidays, 'url'=>"{$CONFIG['application_webpath']}holiday_calendar.php?type=10", 'enablevar' => 'holidays_enabled'),
                      50=> array ( 'perm'=> 22, 'name'=> $strFTPFilesDB, 'url'=>"{$CONFIG['application_webpath']}ftp_list_files.php"),
                      60=> array ( 'perm'=> 22, 'name'=> $strServiceLevels, 'url'=>"{$CONFIG['application_webpath']}service_levels.php"),
                      70=> array ( 'perm'=> 7, 'name'=> $strBulkModify, 'url'=>"{$CONFIG['application_webpath']}bulk_modify.php?action=external_esc"),
                      80=> array ( 'perm'=> 64, 'name'=> $strEscalationPaths, 'url'=>"{$CONFIG['application_webpath']}escalation_paths.php"),
                      90=> array ( 'perm'=> 66, 'name'=> $strManageDashboardComponents, 'url'=>"{$CONFIG['application_webpath']}manage_dashboard.php"),
                      100=> array ( 'perm'=> 69, 'name'=> $strNotices, 'url'=>"{$CONFIG['application_webpath']}notices.php"),
                      110=> array ( 'perm'=> 22, 'name'=> $strTriggers, 'url'=>"{$CONFIG['application_webpath']}triggers.php"),
                      120=> array ( 'perm'=> 22, 'name'=> $strScheduler, 'url'=>"{$CONFIG['application_webpath']}scheduler.php"),
                      130=> array ( 'perm'=> 49, 'name'=> $strFeedbackForms, 'url'=>"", 'submenu'=>"103090", 'enablevar' => 'feedback_enabled')
), $hmenu[1030]);
if (!is_array($hmenu[103010])) $hmenu[103010] = array();
$hmenu[103010] = array_merge(array (10=> array ( 'perm'=> 22, 'name'=> $strManageUsers, 'url'=>"{$CONFIG['application_webpath']}manage_users.php"),
                        20=> array ( 'perm'=> 20, 'name'=> $strAddUser, 'url'=>"{$CONFIG['application_webpath']}add_user.php?action=showform"),
                        30=> array ( 'perm'=> 9, 'name'=> $strSetPermissions, 'url'=>"{$CONFIG['application_webpath']}edit_user_permissions.php"),
                        40=> array ( 'perm'=> 23, 'name'=> $strUserGroups, 'url'=>"{$CONFIG['application_webpath']}usergroups.php"),
                        50=> array ( 'perm'=> 22, 'name'=> $strEditHolidayEntitlement, 'url'=>"{$CONFIG['application_webpath']}edit_holidays.php", 'enablevar' => 'holidays_enabled')
), $hmenu[103010]);
if (!is_array($hmenu[103090])) $hmenu[103090] = array();
$hmenu[103090] = array_merge(array (10=> array ( 'perm'=> 49, 'name'=> $strAddFeedbackForm, 'url'=>"{$CONFIG['application_webpath']}edit_feedback_form.php?action=new", 'enablevar' => 'feedback_enabled'),
                        20=> array ( 'perm'=> 49, 'name'=> $strBrowseFeedbackForms, 'url'=>"{$CONFIG['application_webpath']}browse_feedback_forms.php", 'enablevar' => 'feedback_enabled')
), $hmenu[103090]);
if (!is_array($hmenu[1040])) $hmenu[1040] = array();
$hmenu[1040] = array_merge(array (10=> array ( 'perm'=> 0, 'name'=> $strViewUsers, 'url'=>"{$CONFIG['application_webpath']}users.php"),
                      20=> array ( 'perm'=> 0, 'name'=> $strListSkills, 'url'=>"{$CONFIG['application_webpath']}user_skills.php"),
                      21=> array ( 'perm'=> 0, 'name'=> $strSkillsMatrix, 'url'=>"{$CONFIG['application_webpath']}skills_matrix.php"),
                      30=> array ( 'perm'=> 27, 'name'=> $strHolidayPlanner, 'url'=>"{$CONFIG['application_webpath']}holiday_calendar.php?display=month", 'enablevar' => 'holidays_enabled'),
                      40=> array ( 'perm'=> 50, 'name'=> $strApproveHolidays, 'url'=>"{$CONFIG['application_webpath']}holiday_request.php?user=all&amp;mode=approval", 'enablevar' => 'holidays_enabled')
), $hmenu[1040]);



// Customers
if (!is_array($hmenu[20])) $hmenu[20] = array();
$hmenu[20] = array_merge(array (10=> array ( 'perm'=> 0, 'name'=> $strSites, 'url'=>"{$CONFIG['application_webpath']}browse_sites.php", 'submenu'=>"2010"),
                    20=> array ( 'perm'=> 0, 'name'=> $strContacts, 'url'=>"{$CONFIG['application_webpath']}browse_contacts.php?search_string=A", 'submenu'=>"2020"),
                    30=> array ( 'perm'=> 0, 'name'=> $strMaintenance, 'url'=>"{$CONFIG['application_webpath']}browse_contract.php?search_string=A", 'submenu'=>"2030"),
                    40=> array ( 'perm'=> 0, 'name'=> $strBrowseFeedback, 'url'=>"{$CONFIG['application_webpath']}browse_feedback.php", 'enablevar' => 'feedback_enabled')
), $hmenu[20]);

if (!is_array($hmenu[2010])) $hmenu[2010] = array();
$hmenu[2010] = array_merge(array (10=> array ( 'perm'=> 11, 'name'=> $strBrowse, 'url'=>"{$CONFIG['application_webpath']}browse_sites.php"),
                      20=> array ( 'perm'=> 2, 'name'=> $strNewSite, 'url'=>"{$CONFIG['application_webpath']}add_site.php?action=showform")
), $hmenu[2010]);
if (!is_array($hmenu[2020])) $hmenu[2020] = array();
$hmenu[2020] = array_merge(array (10=> array ( 'perm'=> 11, 'name'=> $strBrowse, 'url'=>"{$CONFIG['application_webpath']}browse_contacts.php?search_string=A"),
                      20=> array ( 'perm'=> 1, 'name'=> $strNewContact, 'url'=>"{$CONFIG['application_webpath']}add_contact.php?action=showform")
), $hmenu[2020]);
if (!is_array($hmenu[2030])) $hmenu[2030] = array();
$hmenu[2030] = array_merge(array (10=> array ( 'perm'=> 19, 'name'=> $strBrowse, 'url'=>"{$CONFIG['application_webpath']}browse_contract.php?search_string=A"),
                      20=> array ( 'perm'=> 39, 'name'=> $strNewContract, 'url'=>"{$CONFIG['application_webpath']}add_contract.php?action=showform"),
                      30=> array ( 'perm'=> 21, 'name'=> $strEditContract, 'url'=>"{$CONFIG['application_webpath']}edit_contract.php?action=showform"),
                      40=> array ( 'perm'=> 2, 'name'=> $strNewReseller, 'url'=>"{$CONFIG['application_webpath']}add_reseller.php"),
                      50=> array ( 'perm'=> 19, 'name'=> $strShowRenewals, 'url'=>"{$CONFIG['application_webpath']}search_renewals.php?action=showform"),
                      60=> array ( 'perm'=> 19, 'name'=> $strShowExpired, 'url'=>"{$CONFIG['application_webpath']}search_expired.php?action=showform"),
                      70=> array ( 'perm'=> 0, 'name'=> "{$strProducts} &amp; {$strSkills}", 'url'=>"{$CONFIG['application_webpath']}products.php", 'submenu'=>"203010"),
), $hmenu[2030]);
if (!is_array($hmenu[203010])) $hmenu[203010] = array();
$hmenu[203010] = array_merge(array (10=> array ( 'perm'=> 56, 'name'=> $strAddVendor, 'url'=>"{$CONFIG['application_webpath']}add_vendor.php"),
                        20=> array ( 'perm'=> 24, 'name'=> $strAddProduct, 'url'=>"{$CONFIG['application_webpath']}add_product.php"),
                        30=> array ( 'perm'=> 28, 'name'=> $strListProducts, 'url'=>"{$CONFIG['application_webpath']}products.php"),
                        35=> array ( 'perm'=> 28, 'name'=> $strListSkills, 'url'=>"{$CONFIG['application_webpath']}products.php?display=skills"),
                        40=> array ( 'perm'=> 56, 'name'=> $strAddSkill, 'url'=>"{$CONFIG['application_webpath']}add_software.php"),
                        50=> array ( 'perm'=> 24, 'name'=> $strLinkProducts, 'url'=>"{$CONFIG['application_webpath']}add_product_software.php"),
                        60=> array ( 'perm'=> 25, 'name'=> $strAddProductQuestion, 'url'=>"{$CONFIG['application_webpath']}add_productinfo.php"),
                        70=> array ('perm'=> 56, 'name'=> $strEditVendor, 'url'=>"{$CONFIG['application_webpath']}edit_vendor.php")
), $hmenu[203010]);


// Support
if (!is_array($hmenu[30])) $hmenu[30] = array();
$hmenu[30] = array_merge(array (10=> array ( 'perm'=> 5, 'name'=> $strAddIncident, 'url'=>"{$CONFIG['application_webpath']}add_incident.php"),
                    20=> array ( 'perm'=> 0, 'name'=> $strViewIncidents, 'url'=>"{$CONFIG['application_webpath']}incidents.php?user=current&amp;queue=1&amp;type=support"),
                    30=> array ( 'perm'=> 0, 'name'=> $strWatchIncidents, 'url'=>"{$CONFIG['application_webpath']}incidents.php?user=all&amp;queue=1&amp;type=support"),
                    40=> array ( 'perm'=> 42, 'name'=> $strHoldingQueue, 'url'=>"{$CONFIG['application_webpath']}review_incoming_updates.php")
), $hmenu[30]);


// Tasks
if (!is_array($hmenu[40])) $hmenu[40] = array();
$hmenu[40] = array_merge(array (10=> array ( 'perm'=> 0, 'name'=> $strAddTask, 'url'=>"{$CONFIG['application_webpath']}add_task.php"),
                    20=> array ( 'perm'=> 0, 'name'=> $strViewTasks, 'url'=>"{$CONFIG['application_webpath']}tasks.php")
), $hmenu[40]);


// KB
if (!is_array($hmenu[50])) $hmenu[50] = array();
$hmenu[50] = array_merge(array (10=> array ( 'perm'=> 54, 'name'=> $strNewKBArticle, 'url'=>"{$CONFIG['application_webpath']}kb_article.php"),
                    20=> array ( 'perm'=> 54, 'name'=> $strBrowse, 'url'=>"{$CONFIG['application_webpath']}browse_kb.php")
), $hmenu[50]);


if (!is_array($hmenu[60])) $hmenu[60] = array();
// Reports
        $hmenu[60] = array_merge(array (10=> array ( 'perm'=> 37, 'name'=>"{$strMarketingMailshot}", 'url'=>"{$CONFIG['application_webpath']}reports/marketing.php"),
                    20=> array ( 'perm'=> 37, 'name'=> "{$strCustomerExport}", 'url'=>"{$CONFIG['application_webpath']}reports/cust_export.php"),
                    30=> array ( 'perm'=> 37, 'name'=> "{$strQueryByExample}", 'url'=>"{$CONFIG['application_webpath']}reports/qbe.php"),
                    50=> array ( 'perm'=> 37, 'name'=> "{$strIncidentsBySite}", 'url'=>"{$CONFIG['application_webpath']}reports/yearly_customer_export.php"),
                    55=> array ( 'perm'=> 37, 'name'=> "{$strIncidentsByEngineer}", 'url'=>"{$CONFIG['application_webpath']}reports/yearly_engineer_export.php"),
                    60=> array ( 'perm'=> 37, 'name'=> "{$strSiteProducts}", 'url'=>"{$CONFIG['application_webpath']}reports/site_products.php"),
                    65=> array ( 'perm'=> 37,  'name'=> "{$strCountContractsByProduct}", 'url'=>"{$CONFIG['application_webpath']}reports/count_contracts_by_product.php"),
                    70=> array ( 'perm'=> 37, 'name'=> "{$strSiteContracts}", 'url'=>"{$CONFIG['application_webpath']}reports/supportbycontract.php"),
                    80=> array ( 'perm'=> 37, 'name'=> "{$strCustomerFeedback}", 'url'=>"{$CONFIG['application_webpath']}reports/feedback.php", 'enablevar' => 'feedback_enabled'),
                    90=> array ( 'perm'=> 37, 'name'=> "{$strSiteIncidents}", 'url'=>"{$CONFIG['application_webpath']}reports/site_incidents.php"),
                    100=> array ( 'perm'=> 37, 'name'=> "{$strRecentIncidents}", 'url'=>"{$CONFIG['application_webpath']}reports/recent_incidents_table.php"),
                    110=> array ( 'perm'=> 37, 'name'=> "{$strIncidentsLoggedOpenClosed}", 'url'=>"{$CONFIG['application_webpath']}reports/incident_graph.php"),
                    120=> array ( 'perm'=> 37, 'name'=> "{$strAverageIncidentDuration}", 'url'=>"{$CONFIG['application_webpath']}reports/average_incident_duration.php"),
                    130=> array ( 'perm'=> 37, 'name'=> "{$strIncidentsBySkill}", 'url'=>"{$CONFIG['application_webpath']}reports/incidents_by_software.php"),
                    140=> array ( 'perm'=> 37, 'name'=> "{$strIncidentsByVendor}", 'url'=>"{$CONFIG['application_webpath']}reports/incidents_by_vendor.php"),
                    150=> array ( 'perm'=> 37, 'name'=> "{$strEscalatedIncidents}",
                    'url'=>"{$CONFIG['application_webpath']}reports/external_engineers.php",
)), $hmenu[60]);

if (!is_array($hmenu[70])) $hmenu[70] = array();
$hmenu[70] = array_merge(array (10=> array ( 'perm'=> 0, 'name'=> "{$strHelpContents}...", 'url'=>"{$CONFIG['application_webpath']}help.php"),
                    20=> array ( 'perm'=> 0, 'name'=> "{$strTranslate}", 'url'=>"{$CONFIG['application_webpath']}translate.php"),
                    30=> array ( 'perm'=> 0, 'name'=> "{$strReportBug}", 'url'=>$CONFIG['bugtracker_url']),
                    40=> array ( 'perm'=> 0, 'name'=> "{$strReleaseNotes}", 'url'=>"{$CONFIG['application_webpath']}releasenotes.php"),
                    50=> array ( 'perm'=> 41, 'name'=> $strHelpAbout, 'url'=>"{$CONFIG['application_webpath']}about.php")
), $hmenu[70]);

// Sort the top level menu, so that plugin menus appear in the right place
ksort($hmenu[0]);

//
// Non specific update types
//
$updatetypes['actionplan'] = array('icon' => 'actionplan', 'text' => sprintf($strActionPlanBy,'updateuser'));
$updatetypes['auto'] = array('icon' => 'auto', 'text' => sprintf($strUpdatedAutomaticallyBy, 'updateuser'));
$updatetypes['closing'] = array('icon' => 'close', 'text' => sprintf($strMarkedforclosureby,'updateuser'));
$updatetypes['editing'] = array('icon' => 'edit', 'text' => sprintf($strEditedBy,'updateuser'));
$updatetypes['email'] = array('icon' => 'emailout', 'text' => sprintf($strEmailsentby,'updateuser'));
$updatetypes['emailin'] = array('icon' => 'emailin', 'text' => sprintf($strEmailreceivedby,'updateuser'));
$updatetypes['emailout'] = array('icon' => 'emailout', 'text' => sprintf($Emailsentby,'updateuser'));
$updatetypes['externalinfo'] = array('icon' => 'externalinfo', 'text' => sprintf($strExternalInfoAddedBy,'updateuser'));
$updatetypes['probdef'] = array('icon' => 'probdef', 'text' => sprintf($strProblemDefinitionby,'updateuser'));
$updatetypes['research'] = array('icon' => 'research', 'text' => sprintf($strResearchedby,'updateuser'));
$updatetypes['reassigning'] = array('icon' => 'reassign', 'text' => sprintf($strReassignedToBy,'currentowner','updateuser'));
$updatetypes['reviewmet'] = array('icon' => 'review', 'text' => sprintf($strReviewby, 'updatereview', 'updateuser')); // conditional
$updatetypes['tempassigning'] = array('icon' => 'tempassign', 'text' => sprintf($strTemporarilyAssignedto,'currentowner','updateuser'));
$updatetypes['opening'] = array('icon' => 'open', 'text' => sprintf($strOpenedby,'updateuser'));
$updatetypes['phonecallout'] = array('icon' => 'callout', 'text' => sprintf($strPhonecallmadeby,'updateuser'));
$updatetypes['phonecallin'] = array('icon' => 'callin', 'text' => sprintf($strPhonecalltakenby,'updateuser'));
$updatetypes['reopening'] = array('icon' => 'reopen', 'text' => sprintf($strReopenedby,'updateuser'));
$updatetypes['slamet'] = array('icon' => 'sla', 'text' => sprintf($strSLAby,'updatesla', 'updateuser'));
$updatetypes['solution'] = array('icon' => 'solution', 'text' => sprintf($strResolvedby, 'updateuser'));
$updatetypes['webupdate'] = array('icon' => 'webupdate', 'text' => sprintf($strWebupdate));
$updatetypes['auto_chase_phone'] = array('icon' => 'chase', 'text' => $strChase);
$updatetypes['auto_chase_manager'] = array('icon' => 'chase', 'text' => $strChase);
$updatetypes['auto_chase_email'] = array('icon' => 'chased', 'text' => $strChased);
$updatetypes['auto_chased_phone'] = array('icon' => 'chased', 'text' => $strChased);
$updatetypes['auto_chased_manager'] = array('icon' => 'chased', 'text' => $strChased);
$updatetypes['auto_chased_managers_manager'] = array('icon' => 'chased', 'text' => $strChased);
$updatetypes['customerclosurerequest'] = array('icon' => 'close', 'text' => $strCustomerRequestedClosure);
$updatetypes['fromtask'] = array('icon' => 'webupdate', text => sprintf($strUpdatedFromActivity, 'updateuser'));
$slatypes['opened'] = array('icon' => 'open', 'text' => $strOpened);
$slatypes['initialresponse'] = array('icon' => 'initialresponse', 'text' => $strInitialResponse);
$slatypes['probdef'] = array('icon' => 'probdef', 'text' => $strProblemDefinition);
$slatypes['actionplan'] = array('icon' => 'actionplan', 'text' => $strActionPlan);
$slatypes['solution'] = array('icon' => 'solution', 'text' => $strSolution);
$slatypes['closed'] = array('icon' => 'close', 'text' => $strClosed);


// List of *Available* languages, must match files in includes/i18n
// TODO allow this list to be configured via config.inc.php
$availablelanguages = array('en-GB' => 'English (British)',
                            'en-US' => 'English (US)',
                            'zh-CN' => '简体中文',
                            'zh-TW' => '繁體中文',
                            'de-DE' => 'Deutsch',
                            'es-ES' => 'Español',
                            'es-CO' => 'Español (Colombia)',
                            'fr-FR' => 'Français',
                            'ja-JP' => '日本語',
                            'it-IT' => 'Italiano',
                            'lt-LT' => 'Lietuvių',
                            'cy-GB' => 'Cymraeg'
                           );


// List of timezones, with UTC offset in minutes
// Source: http://en.wikipedia.org/wiki/List_of_time_zones (where else?)
$availabletimezones = array('-720' => 'UTC-12',
                            '-660' => 'UTC-11',
                            '-600' => 'UTC-10',
                            '-570' => 'UTC-9:30',
                            '-540' => 'UTC-9',
                            '-480' => 'UTC-8',
                            '-420' => 'UTC-7',
                            '-360' => 'UTC-6',
                            '-300' => 'UTC-5',
                            '-270' => 'UTC-4:30',
                            '-240' => 'UTC-4',
                            '-210' => 'UTC-3:30',
                            '-180' => 'UTC-3',
                            '-120' => 'UTC-2',
                            '-60' => 'UTC-1',
                            '0' => 'UTC',
                            '60' => 'UTC+1',
                            '120' => 'UTC+2',
                            '180' => 'UTC+3',
                            '210' => 'UTC+3:30',
                            '240' => 'UTC+4',
                            '300' => 'UTC+5',
                            '330' => 'UTC+5:30',
                            '345' => 'UTC+5:45',
                            '360' => 'UTC+6',
                            '390' => 'UTC+6:30',
                            '420' => 'UTC+7',
                            '480' => 'UTC+8',
                            '525' => 'UTC+8:45',
                            '540' => 'UTC+9',
                            '570' => 'UTC+9:30',
                            '600' => 'UTC+10',
                            '630' => 'UTC+10:30',
                            '660' => 'UTC+11',
                            '690' => 'UTC+11:30',
                            '720' => 'UTC+12',
                            '765' => 'UTC+12:45',
                            '780' => 'UTC+13',
                            '840' => 'UTC+14',
                           );


/**
    * Template variables (Alphabetical order)
    * description - Friendly label
    * replacement - Quoted PHP code to be run to perform the template var replacement
    * requires -Optional field. single string or array. Specifies the 'required' params from the trigger that is needed for this replacement
    * action - Optional field, when set the var will only be available for that action
    * type - Optional field, defines where a variable can be used, system, incident or user
*/
$ttvararray['{applicationname}'] =
array('description' => $CONFIG['application_name'],
      'replacement' => '$CONFIG[\'application_name\'];'
      );

$ttvararray['{applicationurl}'] =
array('description' => 'System URL',
      'replacement' => 'application_url();'
      );

$ttvararray['{applicationpath}'] =
array('description' => 'System base path',
      'replacement' => '$CONFIG[\'application_webpath\'];'
      );

$ttvararray['{applicationshortname}'] =
array('description' => $CONFIG['application_shortname'],
      'replacement' => '$CONFIG[\'application_shortname\'];'
      );

$ttvararray['{applicationversion}'] =
array('description' => $application_version_string,
      'replacement' => 'application_version_string();'
      );

$ttvararray['{contactid}'][] =
array('description' => 'Contact ID',
      'requires' => 'incidentid',
      'replacement' => 'incident_contact($paramarray[\'incidentid\']);'
      );

$ttvararray['{contactid}'][] =
array('description' => 'Contact ID',
      'requires' => 'incidentid',
      'replacement' => '$paramarray[\'contactid\'];'
      );

$ttvararray['{contactemail}'][] =
array('description' => $strIncidentsContactEmail,
      'requires' => 'contactid',
      'replacement' => 'contact_email($paramarray[\'contactid\']);',
      'action' => 'ACTION_EMAIL'
      );

$ttvararray['{contactemail}'][] =
array('description' => $strIncidentsContactEmail,
      'requires' => 'incidentid',
      'replacement' => 'contact_email(incident_contact($paramarray[\'incidentid\']));',
      'action' => 'ACTION_EMAIL'
      );

$ttvararray['{contactfirstname}'][] =
array('description' => 'First Name of contact',
      'requires' => 'contactid',
      'replacement' => 'strtok(contact_realname($paramarray[\'contactid\'])," ");'
      );

$ttvararray['{contactfirstname}'][] =
array('description' => 'First Name of contact',
      'requires' => 'incidentid',
      'replacement' => 'strtok(contact_realname(incident_contact($paramarray[\'incidentid\']))," ");'
      );

$ttvararray['{contactname}'][] =
array('description' => 'Full Name of contact',
      'requires' => 'contactid',
      'replacement' => 'contact_realname($paramarray[\'contactid\']);'
      );

$ttvararray['{contactname}'][] =
array('description' => 'Full Name of contact',
      'requires' => 'incidentid',
      'replacement' => 'contact_realname(incident_contact($paramarray[\'incidentid\']));'
      );

$ttvararray['{contactnotify}'] =
array('description' => 'The Notify Contact email address (if set)',
      'requires' => 'contactid',
      'replacement' => 'contact_notify_email($paramarray[\'contactid\']);'
      );

$ttvararray['{contactphone}'] =
array('description' => 'Contact phone number',
      'requires' => 'contactid',
      'replacement' => 'contact_site($paramarray[\'contactid\']);'
      );

$ttvararray['{contractid}'] =
array('description' => 'Contact ID',
      'requires' => 'contractid',
      'replacement' => '$paramarray[\'contractid\']);'
      );

$ttvararray['{sitename}'][] =
array('description' => 'Site name',
      'requires' => 'incidentid',
      'replacement' => 'contact_site(incident_contact($paramarray[\'incidentid\']));'
      );

$ttvararray['{sitename}'][] =
array('description' => 'Site name',
      'requires' => 'contactid',
      'replacement' => 'contact_site($paramarray[\'contactid\']);'
      );

$ttvararray['{sitename}'][] =
array('description' => 'Site name',
      'requires' => 'contractid',
      'replacement' => 'contract_site($paramarray[\'contractid\']);'
      );

$ttvararray['{sitename}'][] =
array('description' => 'Site name',
      'requires' => 'siteid',
      'replacement' => 'site_name($paramarray[\'siteid\']);'
      );

$ttvararray['{feedbackurl}'] =
array('description' => 'Feedback URL',
      'requires' => 'incidentid',
      'replacement' => '$baseurl.\'feedback.php?ax=\'.urlencode(trim(base64_encode(gzcompress(str_rot13(urlencode($CONFIG[\'feedback_form\']).\'&&\'.urlencode($contactid).\'&&\'.urlencode($incidentid))))));'
      );

$ttvararray['{globalsignature}'] =
array('description' => $strGlobalSignature,
      'replacement' => 'global_signature();'
      );

$ttvararray['{incidentccemail}'] =
array('description' => $strIncidentCCList,
      'requires' => 'incidentid',
      'replacement' => 'incident_ccemail($paramarray[\'incidentid\']);'
      );

$ttvararray['{incidentexternalemail}'] =
array('description' => $strExternalEngineerEmail,
      'requires' => 'incidentid',
      'replacement' => 'incident_externalemail($paramarray[incidentid]);'
      );

$ttvararray['{incidentexternalengineer}'] =
array('description' => $strExternalEngineer,
      'requires' => 'incidentid',
      'replacement' => 'incident_externalengineer($paramarray[incidentid]);'
      );

$ttvararray['{incidentexternalengineerfirstname}'] =
array('description' => $strExternalEngineersFirstName,
      'requires' => 'incidentid',
      'replacement' => 'strtok(incident_externalengineer($paramarray[incidentid]),\' \');'
      );

$ttvararray['{incidentexternalid}'] =
array('description' => "{$GLOBALS['strExternalID']}",
      'requires' => 'incidentid',
      'replacement' => '$incident->externalid;'
      );

$ttvararray['{incidentfirstupdate}'] =
array('description' => $strFirstCustomerVisibleUpdate,
      'replacement' => ''
      );

$ttvararray['{incidentid}'] =
array('description' => $GLOBALS['strIncidentID'],
      'requires' => 'incidentid',
      'replacement' => '$paramarray[\'incidentid\'];'
      );

$ttvararray['{incidentowner}'] =
array('description' => $strIncidentOwnersFullName,
      'requires' => 'incidentid',
      'replacement' => 'user_realname(incident_owner($paramarray[incidentid]));'
      );

$ttvararray['{incidentowneremail}'] =
array('description' => 'Incident Owners Email Address',
      'requires' => 'incidentid',
      'replacement' => 'user_email(incident_owner($paramarray[incidentid]));'
      );

$ttvararray['{incidentpriority}'] =
array('description' => $strIncidentPriority,
      'requires' => 'incidentid',
      'replacement' => 'priority_name(incident_priority($paramarray[incidentid]));'
      );

$ttvararray['{incidentsoftware}'] =
array('description' => $strSkillAssignedToIncident,
      'requires' => 'incidentid',
      'replacement' => 'software_name(db_read_column("softwareid", $GLOBALS["dbIncidents"], $paramarray[incidentid]));'
      );

$ttvararray['{incidenttitle}'] =
array('description' => $strIncidentTitle,
      'requires' => 'incidentid',
      'replacement' => 'incident_title($paramarray[incidentid]);'
      );

$ttvararray['{kbname}'] =
array('description' => $strKnowledgeBase,
      'requires' => 'kbid',
      'replacement' => 'kb_name($paramarray[\'kbid\']);'
      );

$ttvararray['{salesperson}'] =
array('description' => 'Salesperson',
      'requires' => 'siteid',
      'replacement' => 'user_realname(db_read_column(\'owner\', $GLOBALS[\'dbSites\'], $siteid));'
      );

$ttvararray['{salespersonemail}'] =
array('description' => $strSalespersonAssignedToContactsSiteEmail,
      'requires' => 'siteid',
      'replacement' => 'user_email(db_read_column(\'owner\', $GLOBALS[\'dbSites\'], $siteid));'
      );

$ttvararray['{signature}'] =
array('description' => $strCurrentUsersSignature,
      'replacement' => 'user_signature($_SESSION[\'userid\']);'
      );

$ttvararray['{supportemail}'] =
array('description' => $strSupportEmailAddress,
      'replacement' => '$CONFIG[\'support_email\'];'
      );

$ttvararray['{supportmanageremail}'] =
array('description' => $strSupportManagersEmailAddress,
      'replacement' => '$CONFIG[\'support_manager_email\'];'
      );

$ttvararray['{todaysdate}'] =
array('description' => $strCurrentDate,
      'replacement' => 'ldate("jS F Y");'
      );

$ttvararray['{useremail}'] =
array('description' => $strCurrentUserEmailAddress,
      'replacement' => 'user_email($paramarray[\'userid\']);'
      );

$ttvararray['{userrealname}'] =
array('description' => $strFullNameCurrentUser,
      'replacement' => 'user_realname($paramarray[\'userid\']);'
      );

$ttvararray['{passwordreseturl}'] =
array('description' => 'Hashed URL to reset a password',
      'replacement' => '$paramarray[\'passwordreseturl\'];',
      'requires' => 'passwordreseturl',
      'type' => 'system'
      );

$ttvararray['{prepassword}'] =
array('description' => 'The plaintext contact password',
      'replacement' => '$paramarray[\'prepassword\'];',
      'requires' => 'prepassword'
      );

$ttvararray['{nextslatime}'] =
array('description' => $strTimeToNextAction,
      'replacement' => 'format_workday_minutes($GLOBALS[\'now\'] - $paramarray[\'nextslatime\']);',
      'requires' => 'nextslatime'
      );

$ttvararray['{nextsla}'] =
array('description' => 'Next SLA name',
      'replacement' => '$paramarray[\'nextsla\'];',
      'requires' => 'nextsla'
      );

$ttvararray['{contractid}'] =
array('description' => 'Contract ID',
      'replacement' => '$paramarray[\'contractid\'];',
      'requires' => 'contractid'
      );

$ttvararray['{contractproduct}'] =
array('description' => 'Contact Product',
      'replacement' => 'contract_product($paramarray[\'contractid\']);',
      'requires' => 'contractid'
      );

$ttvararray['{contractsla}'] =
array('description' => 'SLA of the maintenance',
      'replacement' => 'maintenance_servicelevel($paramarray[\'contractid\']);',
      'requires' => 'contractid'
      );

$ttvararray['{userid}'] =
array('description' => 'UserID the trigger passes',
      'replacement' => '$paramarray[\'userid\'];'
      );

$ttvararray['{ownerid}'] =
array('description' => 'Incident owner ID',
      'replacement' => 'incident_owner($paramarray[\'incidentid\']);',
      'requires' => 'incidentid'
      );

$ttvararray['{townerid}'] =
array('description' => 'Incident temp owner ID',
      'replacement' => 'incident_towner($paramarray[\'incidentid\']);',
      'requires' => 'incidentid'
      );

$ttvararray['{holdingemailid}'] =
array('description' => 'ID of the new email in the holding queue',
      'replacement' => '$paramarray[\'holdingemailid\'];',
      'requires' => 'holdingemailid'
      );

$ttvararray['{slaid}'] =
array('description' => 'ID of the SLA',
      'replacement' => 'contract_slaid($paramarray[\'contractid\']);',
      'requires' => 'contractid'
      );

$ttvararray['{slatag}'] =
array('description' => 'The SLA tag',
      'replacement' => 'servicelevel_id2tag(contract_slaid($paramarray[\'contractid\']));',
      'requires' => 'contractid'
      );

$ttvararray['{sitesalespersonid}'] = 
array('description' => 'The ID of the site\'s salesperson',
      'replacement' => 'site_salespersonid($paramarray[\'siteid\']);',
      'requires' => 'siteid'
      );
      
$ttvararray['{sitesalesperson}'] = 
array('description' => 'The name of the site\'s salesperson',
      'replacement' => 'site_salesperson($paramarray[\'siteid\']);',
      'requires' => 'siteid'
      );
      
$ttvararray['{currentlang}'] = 
array('description' => 'The language the user has selected to login using',
      'replacement' => '$paramarray[\'currentlang\'];',
      'requires' => 'currentlang'
      );
      
$ttvararray['{profilelang}'] =
array('description' => 'The language the user has stored in their profile',
      'replacement' => '$paramarray[\'profilelang\'];',
      'requires' => 'profilelang'
      );
      
      

?>