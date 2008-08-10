<?php
// auto.php - Regular SiT! maintenance tasks (for scheduling)
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

// This file should be called from a cron job (or similar) to run tasks periodically

@include ('set_include_path.inc.php');
require ('db_connect.inc.php');
include ('strings.inc.php');
require ('functions.inc.php');


/**
    * @author Ivan Lucas
**/
function saction_test()
{
    echo "<h2>Testing testing 1 2 3.</h2>";

    return TRUE;
}


/**
    * Select incidents awaiting closure for more than a week where the next action time is not set or has passed
    * @author Ivan Lucas
    * @param $closure_delay int. The amount of time (in seconds) to wait before closing
**/
function saction_CloseIncidents($closure_delay)
{
    $success = TRUE;
    global $dbIncidents, $dbUpdates;

    if ($closure_delay < 1) $closure_delay = 554400; // Default  six days and 10 hours

    $sql = "SELECT * FROM `{$dbIncidents}` WHERE status='7' AND (($now - lastupdated) > '{$closure_delay}') AND (timeofnextaction='0' OR timeofnextaction<='$now') ";
    $result=mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        $success = FALSE;
    }
    if ($verbose) echo "Found ".mysql_num_rows($result)." Incidents to close{$crlf}";
    while ($irow = mysql_fetch_array($result))
    {
        $sqlb = "UPDATE `{$dbIncidents}` SET lastupdated='$now', closed='$now', status='2', closingstatus='4', timeofnextaction='0' WHERE id='".$irow['id']."'";
        $resultb = mysql_query($sqlb);
        if (mysql_error())
        {
            trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
            $success = FALSE;
        }
        if ($verbose) echo "  Incident ".$row['id']." closed{$crlf}";

        $sqlc = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, currentowner, currentstatus, bodytext, timestamp, nextaction, customervisibility) ";
        $sqlc .= "VALUES ('".$irow['id']."', '0', 'closing', '".$irow['owner']."', '".$irow['status']."', 'Incident Closed by {$CONFIG['application_shortname']}', '$now', '', 'show' ) ";
        $resultc = mysql_query($sqlc);
        if (mysql_error())
        {
            trigger_error(mysql_error(),E_USER_WARNING);
            $success = FALSE;
        }
    }
    return $success;
}


/**
    * @author Ivan Lucas
**/
function saction_PurgeJournal()
{
    global $dbJournal;
    $success = TRUE;
    $purgedate = date('YmdHis',($now - $CONFIG['journal_purge_after']));
    $sql = "DELETE FROM `{$dbJournal}` WHERE timestamp < $purgedate";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        $success = FALSE;
    }
    if ($verbose) echo "Purged ".mysql_affected_rows()." journal entries{$crlf}";

    return $success;
}


/** Calculate SLA times
    * @author Tom Gerrard
    * @note Moved from htdocs/auto/timecalc.php by INL for 3.40 release
**/
function saction_TimeCalc()
{
    global $now;
    global $dbIncidents, $dbServiceLevels, $dbMaintenance, $dbUpdates;
    global $SYSLANG, $CONFIG;

    $success = TRUE;
    // FIXME this should only run INSIDE the working day
    // FIXME ? this will not update the database fully if two SLAs have been met since last run - does it matter ?

    if ($verbose) echo "Calculating SLA times{$crlf}";

    $sql = "SELECT id, title, maintenanceid, priority, slaemail, slanotice, servicelevel, status, owner ";
    $sql .= "FROM `{$dbIncidents}` WHERE status != ".STATUS_CLOSED." AND status != ".STATUS_CLOSING;
    $incident_result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error(mysql_error(),E_USER_WARNING);
        $success = FALSE;
    }

    while ($incident = mysql_fetch_array($incident_result))
    {
        // Get the service level timings for this class of incident, we may have one
        // from the incident itself, otherwise look at contract type
        if ($incident['servicelevel'] ==  '')
        {
            $sql = "SELECT tag FROM `{$dbServiceLevels}` s, `{$dbMaintenance}` m ";
            $sql .= "WHERE m.id = '{$incident['maintenanceid']}' AND s.id = m.servicelevelid";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
            $t = mysql_fetch_row($sql);
            $tag = $t[0];
            mysql_free_result($result);
        }
        else $tag = $incident['servicelevel'];

        if ($verbose) echo $incident['id']." is a $tag incident{$crlf}";

        $newReviewTime = -1;
        $newSlaTime = -1;

        $sql = "SELECT id, type, sla, timestamp, currentstatus FROM `{$dbUpdates}` WHERE incidentid='{$incident['id']}' ";
        $sql .=" AND type = 'slamet' ORDER BY id DESC LIMIT 1";
        $update_result = mysql_query($sql);
        if (mysql_error())
        {
            trigger_error(mysql_error(),E_USER_WARNING);
            $success = FALSE;
        }

        if (mysql_num_rows($update_result) != 1)
        {
            if ($verbose) echo "Cannot find SLA information for incident ".$incident['id'].", skipping{$crlf}";
        }
        else
        {
            $slaInfo = mysql_fetch_array($update_result);
            $newSlaTime = calculate_incident_working_time($incident['id'],$slaInfo['timestamp'],$now);
            if ($verbose)
            {
                echo "   Last SLA record is ".$slaInfo['sla']." at ".date("jS F Y H:i",$slaInfo['timestamp'])." which is $newSlaTime working minutes ago{$crlf}";
            }
        }
        mysql_free_result($update_result);

        $sql = "SELECT id, type, sla, timestamp, currentstatus, currentowner FROM `{$dbUpdates}` WHERE incidentid='{$incident['id']}' ";
        $sql .= "AND type='reviewmet' ORDER BY id DESC LIMIT 1";
        $update_result = mysql_query($sql);
        if (mysql_error())
        {
            trigger_error(mysql_error(),E_USER_WARNING);
            $success = FALSE;
        }

        if (mysql_num_rows($update_result) != 1)
        {
            if ($verbose) echo "   Cannot find review information for incident ".$incident['id'].", skipping{$crlf}";
        }
        else
        {
            $reviewInfo = mysql_fetch_array($update_result);
            $newReviewTime = floor($now-$reviewInfo['timestamp'])/60;
            if ($verbose)
            {
                if ($reviewInfo['currentowner'] != 0) echo "   There has been no review on this incident, which was opened $newReviewTime minutes ago{$crlf}";
                else echo "   The last review took place $newReviewTime minutes ago{$crlf}";
            }
            trigger('TRIGGER_INCIDENT_REVIEW_DUE', array('incidentid' => $incident['id'], 'time' => $newReviewTime));
        }
        mysql_free_result($update_result);


        if ($newSlaTime != -1)
        {
            // Get these time of NEXT SLA requirement in minutes
            $coefficient = 1;
            $NextslaName = $SYSLANG['strSLATarget'];

            switch ($slaInfo['sla'])
            {
                case 'opened':
                    $slaRequest='initial_response_mins';
                    $NextslaName = $SYSLANG['strInitialResponse'];
                    break;
                case 'initialresponse':
                    $slaRequest='prob_determ_mins';
                    $NextslaName = $SYSLANG['strProblemDefinition'];
                    break;
                case 'probdef':
                    $slaRequest = 'action_plan_mins';
                    $NextslaName = $SYSLANG['strActionPlan'];
                    break;
                case 'actionplan':
                    $slaRequest = 'resolution_days';
                    $NextslaName = $SYSLANG['strResolutionReprioritisation'];
                    $coefficient = ($CONFIG['end_working_day'] - $CONFIG['start_working_day']) / 60;
                    break;
                case 'solution':
                    $slaRequest = 'initial_response_mins';
                    $NextslaName = $SYSLANG['strInitialResponse'];
                    break;
            }

            // Query the database for the next SLA and review times...

            $sql = "SELECT ($slaRequest*$coefficient) as 'next_sla_time', review_days ";
            $sql .= "FROM `{$dbServiceLevels}` WHERE tag = '$tag' AND priority = '{$incident['priority']}'";
            $result = mysql_query($sql);
            if (mysql_error())
            {
                trigger_error(mysql_error(),E_USER_WARNING);
                $success = FALSE;
            }
            $times = mysql_fetch_assoc($result);
            mysql_free_result($result);

            if ($verbose)
            {
                echo "   The next SLA target should be met in ".$times['next_sla_time']." minutes{$crlf}";
                echo "   Reviews need to be made every ".($times['review_days']*24*60)." minutes{$crlf}";
            }

            if ($incident['slanotice'] == 0)
            {
                //reaching SLA
                if ($times['next_sla_time'] > 0) $reach = $newSlaTime / $times['next_sla_time'];
                else $reach = 0;
                if ($reach >= ($CONFIG['urgent_threshold'] * 0.01))
                {
                    $timetil = $times['next_sla_time']-$newSlaTime;

                    trigger('TRIGGER_INCIDENT_NEARING_SLA', array('incidentid' => $incident['id'],
                                                                  'nextslatime' => $times['next_sla_time'],
                                                                  'nextsla' => $NextslaName));
                }
            }
        }
    }
    mysql_free_result($incident_result);

    return $success;
}


function saction_SetUserStatus()
{
    global $dbHolidays, $dbUsers;
    // Find users with holidays today who don't have correct status
    $success = TRUE;
    $startdate = mktime(0,0,0,date('m'),date('d'),date('Y'));
    $enddate = mktime(23,59,59,date('m'),date('d'),date('Y'));
    $sql = "SELECT * FROM `{$dbHolidays}` ";
    $sql .= "WHERE startdate >= '$startdate' AND startdate < '$enddate' AND (type >='1' AND type <= 5) ";
    $sql .= "AND (approved=1 OR approved=2 OR approved=11 OR approved=12)";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        $success = FALSE;
        trigger_error(mysql_error(),E_USER_WARNING);
    }
    while ($huser = mysql_fetch_object($result))
    {
        if ($huser->length == 'day'
            OR ($huser->length == 'am' AND date('H') < 12)
            OR ($huser->length == 'pm' AND date('H') < 12))
        {
            $currentstatus = user_status($huser->userid);
            $newstatus = $currentstatus;
            // Only enabled users
            if ($currentstatus > 0)
            {
                if ($huser->type == 1 AND $currentstatus != 5) $newstatus = 5; // Holiday
                if ($huser->type == 2 AND $currentstatus != 8) $newstatus = 8; // Sickness
                if ($huser->type == 3 AND ($currentstatus != 6 AND $currentstatus != 9)) $newstatus = 9; // Work away
                if ($huser->type == 4 AND $currentstatus != 7) $newstatus = 7; // Training
                if ($huser->type == 5 AND ($currentstatus != 2 AND $currentstatus != 8)) $newstatus = 8; // Compassionate
            }
            if ($newstatus != $currentstatus)
            {
                $accepting = '';
                // FIXME these should be constants
                switch ($newstatus)
                {
                    case 1: // in office
                        $accepting = 'Yes';
                    break;

                    case 2: // Not in office
                        $accepting = 'No';
                    break;

                    case 3: // In Meeting
                        // don't change
                        $accepting = '';
                    break;

                    case 4: // At Lunch
                        $accepting = '';
                    break;

                    case 5: // On Holiday
                        $accepting = 'No';
                    break;

                    case 6: // Working from home
                        $accepting = 'Yes';
                    break;

                    case 7: // On training course
                        $accepting = 'No';
                    break;

                    case 8: // Absent Sick
                        $accepting =' No';
                    break;

                    case 9: // Working Away
                        // don't change
                        $accepting = '';
                    break;

                    default:
                        $accepting='';
                }
                $usql = "UPDATE `{$dbUsers}` SET status='{$newstatus}'";
                if ($accepting != '') $usql .= ", accepting='{$accepting}'";
                $usql .= " WHERE id='{$huser->userid}' LIMIT 1";
                if ($accepting == 'No') incident_backup_switchover($huser->userid, 'no');
                
                if ($verbose)
                {
                    echo user_realname($huser->userid).': '.userstatus_name($currentstatus).' -> '.userstatus_name($newstatus).$crlf;
                    echo $usql.$crlf;
                }
                
                mysql_query($usql);
                if (mysql_error())
                {
                    $success = FALSE;
                    trigger_error(mysql_error(),E_USER_WARNING);
                }
            }
        }
    }
    // Find users who are set away but have no entry in the holiday calendar
    $sql = "SELECT * FROM `{$dbUsers}` WHERE status=5 OR status=7 OR status=8 OR status=9 ";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        $success = FALSE;
        trigger_error(mysql_error(),E_USER_WARNING);
    }
    return $success;
}


/** Chase customers
    * @author Paul Heaney
    * @note Moved from htdocs/auto/chase_customer.php by INL for 3.40
*/
function saction_ChaseCustomers()
{
    global $CONFIG, $now;
    global $dbIncidents, $dbUpdates;
    $success = TRUE;

    /**
        * @author Paul Heaney
    */
    function not_auto_type($type)
    {
        if ($type != 'auto_chase_email' AND $type != 'auto_chase_phone' AND $type != 'auto_chase_manager')
        {
            return TRUE;
        }
        return FALSE;
    }

    if ($CONFIG['auto_chase'] == TRUE)
    {
        // if 'awaiting customer action' for more than $CONFIG['chase_email_minutes'] and NOT in an auto state, send auto email

        //$sql = "SELECT incidents.id, contacts.forenames,contacts.surname,contacts.id AS managerid FROM incidents,contacts WHERE status = ".STATUS_CUSTOMER." AND contacts.notify_contactid = contacts.id";
        $sql = "SELECT i.id, i.timeofnextaction FROM `{$dbIncidents}` AS i WHERE status = ".STATUS_CUSTOMER;

        $result = mysql_query($sql);
        if (mysql_error())
        {
            trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
            $success = FALSE;
        }

        while ($obj = mysql_fetch_object($result))
        {
            if (!in_array($obj->maintenanceid, $CONFIG['dont_chase_maintids']))
            {
                // only annoy these people
                $sql_update = "SELECT * FROM `{$dbUpdates}` WHERE incidentid = {$obj->id} ORDER BY timestamp DESC LIMIT 1";
                $result_update = mysql_query($sql_update);
                if (mysql_error())
                {
                    trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
                    $success = FALSE;
                }

                $obj_update = mysql_fetch_object($result_update);

                if ($CONFIG['chase_email_minutes'] != 0)
                {
                    //if (not_auto_type($obj_update->type) AND $obj_update->timestamp <= ($now-$CONFIG['chase_email_minutes']*60))
                    if (not_auto_type($obj_update->type) AND (($obj->timeofnextaction == 0 AND calculate_working_time($obj_update->timestamp, $now) >= $CONFIG['chase_email_minutes']) OR ($obj->timeofnextaction != 0 AND calculate_working_time($obj->timeofnextupdate, $now) >= $CONFIG['chase_email_minutes'])))
                    {
                        send_template_email($CONFIG['chase_email_template'],$obj->id);
                        $sql_insert = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp, customervisibility) VALUES ('{$obj_update->incidentid}','{$sit['2']}','auto_chase_email','Sent auto chase email to customer','{$now}','show')";
                        mysql_query($sql_insert);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }

                        $sql_update = "UPDATE `{$dbIncidents}` SET lastupdated = '{$now}', nextactiontime = 0 WHERE id = {$obj->id}";
                        mysql_query($sql_update);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }
                    }
                }

                if ($CONFIG['chase_phone_minutes'] != 0)
                {
                    //if ($obj_update->type == 'auto_chase_email' AND $obj_update->timestamp <= ($now-$CONFIG['chase_phone_minutes']*60))
                    if ($obj_update->type == 'auto_chase_email' AND  (($obj->timeofnextaction == 0 AND calculate_working_time($obj_update->timestamp, $now) >= $CONFIG['chase_phone_minutes']) OR ($obj->timeofnextaction != 0 AND calculate_working_time($obj->timeofnextupdate, $now) >= $CONFIG['chase_phone_minutes'])))
                    {
                        $sql_insert = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp, customervisibility) VALUES ('{$obj_update->incidentid}','{$sit['2']}','auto_chase_phone','Status: Awaiting Customer Action -&gt; <b>Active</b><hr>Please phone the customer to get an update on this call as {$CONFIG['chase_phone_minutes']} have passed since the auto chase email was sent. Once you have done this please use the update type \"Chased customer - phone\"','{$now}','hide')";
                        mysql_query($sql_insert);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }

                        $sql_update = "UPDATE `{$dbIncidents}` SET lastupdated = '{$now}', nextactiontime = 0, status = 1 WHERE id = {$obj->id}";
                        mysql_query($sql_update);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }
                    }
                }

                if ($CONFIG['chase_manager_minutes'] != 0)
                {
                    //if ($obj_update->type == 'auto_chased_phone' AND $obj_update->timestamp <= ($now-$CONFIG['chase_manager_minutes']*60))
                    if ($obj_update->type == 'auto_chased_phone' AND (($obj->timeofnextaction == 0 AND calculate_working_time($obj_update->timestamp, $now) >= $CONFIG['chase_manager_minutes']) OR ($obj->timeofnextaction != 0 AND calculate_working_time($obj->timeofnextupdate, $now) >= $CONFIG['chase_manager_minutes'])))
                    {
                        $update = "Status: Awaiting Customer Action -&gt; <b>Active</b><hr>";
                        $update .= "Please phone the customers MANAGER to get an update on this call as ".$CONFIG['chase_manager_minutes']." have passed since the auto chase email was sent.<br />";
                        $update .= "The manager is <a href='contact_details.php?id={$obj->managerid}'>{$obj->forenames} {$obj->surname}</a><br />";
                        $update .= " Once you have done this please email the actions to the customer and select the \"Was this a customer chase?\"'";

                        $sql_insert = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp, customervisibility) VALUES ('{$obj_update->incidentid}','{$sit['2']}','auto_chase_manager',$update,'{$now}','hide')";
                        mysql_query($sql_insert);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }

                        $sql_update = "UPDATE `{$dbIncidents}` SET lastupdated = '{$now}', nextactiontime = 0, status = 1 WHERE id = {$obj->id}";
                        mysql_query($sql_update);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }
                    }
                }

                if ($CONFIG['chase_managers_manager_minutes'] != 0)
                {
                    //if ($obj_update->type == 'auto_chased_manager' AND $obj_update->timestamp <= ($now-$CONFIG['chase_managers_manager_minutes']*60))
                    if ($obj_update->type == 'auto_chased_manager' AND (($obj->timeofnextaction == 0 AND calculate_working_time($obj_update->timestamp, $now) >= $CONFIG['chase_amanager_manager_minutes']) OR ($obj->timeofnextaction != 0 AND calculate_working_time($obj->timeofnextupdate, $now) >= $CONFIG['chase_amanager_manager_minutes'])))
                    {
                        $sql_insert = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp, customervisibility) VALUES ('{$obj_update->incidentid}','{$sit['2']}','auto_chase_managers_manager','Status: Awaiting Customer Action -&gt; <b>Active</b><hr>Please phone the customers managers manager to get an update on this call as {$CONFIG['chase_manager_minutes']} have passed since the auto chase email was sent. Once you have done this please email the actions to the customer and manager and select the \"Was this a manager chase?\"','{$now}','hide')";
                        mysql_query($sql_insert);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }

                        $sql_update = "UPDATE `{$dbIncidents}` SET lastupdated = '{$now}', nextactiontime = 0, status = 1 WHERE id = {$obj->id}";
                        mysql_query($sql_update);
                        if (mysql_error())
                        {
                            trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                            $success = FALSE;
                        }
                    }
                }
            }
        }
    }
    return $success;
}


/** Check the holding queue for waiting email
    * @author Ivan Lucas
*/
function saction_CheckWaitingEmail()
{
    global $dbTempIncoming, $dbUpdates;
    $success = TRUE;

    $sql = "SELECT COUNT(ti.id), UNIX_TIMESTAMP(NOW()) - `timestamp` AS minswaiting FROM `{$dbTempIncoming}` AS ti ";
    $sql .= "LEFT JOIN `{$dbUpdates}` AS u ON ti.updateid = u.id GROUP BY ti.id";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error("MySQL Query Error".mysql_error(), E_USER_WARNING);
        $success = FALSE;
    }
    list($count, $minswaiting) = mysql_fetch_row($result);
    if ($count > 0)
    {
        trigger("TRIGGER_WAITING_HELD_EMAIL", array('minswaiting' => $minswaiting));
    }

    return $success;
}

/**
 * Checks for expired FTP files (where expired is before now) and removes them
 * @author Paul Heaney
 */
function saction_PurgeExpiredFTPItems()
{
    global $dbFiles, $now;
    $success = TRUE;

    // Retreieve names first so we can delete them from FTP site
    $sql = "SELECT * FROM `{$dbFiles}` WHERE expiry <= '{$now}' AND expiry != 0";
    $result = mysql_query($sql);
    if (mysql_error())
    {
        trigger_error("MySQL Query Error".mysql_error(), E_USER_WARNING);
        $success = FALSE;
    }

    if (mysql_numrows($result) > 0)
    {
        $connection = create_ftp_connection();

        while ($obj = mysql_fetch_object($result))
        {
            $success &= ftp_delete($connection, $obj->path."/".$obj->filename);

            $sqlDel = "DELETE FROM `{$dbFiles}` WHERE id = {$obj->id}";
            $resultdel = mysql_query($sqlDel);
            if (mysql_error())
            {
                trigger_error("MySQL Query Error".mysql_error(), E_USER_WARNING);
                $success = FALSE;
            }
        }

        ftp_close($connection);
    }
    return $success;
}

// TODO PurgeAttachments
// Look for the review due trigger, where did it go

/**
 *
 * @author Paul Heaney 
*/
function saction_MailPreviousMonthsTransactions()
{
	global $CONFIG;
    /*
     Get todays date
     Subtract one from the month and find last month
     Find the last day of last month
     fope(transactions.php?mode=csv&start=X&end=Y&breakdonw=yes
     mail to people

     TODO need a mechanism to subscribe to scheduled events? Could this be done with a trigger? Hmmhhhhhh

    */
    $currentmonth = date('m');
    $currentyear = date('y');
    if ($currentmonth == 1)
    {
        $currentyear--;
        $lastmonth = 12;
    }
    else
    {
        $lastmonth = $currentmonth - 1;
    }

    $startdate = "{$currentyear}-{$lastmonth}-01";
    // Find last date of previous month, 5 day an arbitary choice
    $lastday = date('t', strtotime('{$currentyear}-{$lastmonth}-05'));
    $enddate = 	"{$currentyear}-{$lastmonth}-{$lastday}";

    $csv = transactions_report('', $startdate, $enddate, '', 'csv', TRUE);
    
    $extra_headers = "Reply-To: {$CONFIG['support_email']}\nErrors-To: {$CONFIG['support_email']}\n"; // TODO should probably be different
    $extra_headers .= "X-Mailer: {$CONFIG['application_shortname']} {$application_version_string}/PHP " . phpversion() . "\n";
    $extra_headers .= "X-Originating-IP: {$_SERVER['REMOTE_ADDR']}\n";
//    if ($ccfield != '')  $extra_headers .= "cc: $ccfield\n";
//    if ($bccfield != '') $extra_headers .= "Bcc: $bccfield\n";

    $extra_headers .= "\n"; // add an extra crlf to create a null line to separate headers from body
                        // this appears to be required by some email clients - INL

    $subject = sprintf($GLOBALS['strBillableIncidentsForPeriodXtoX'], $startdate, $enddate);

    $bodytext = $GLOBALS['strAttachedIsBillableIncidentsForAbovePeriod'];

    $mime = new MIME_mail($CONFIG['support_email'], $CONFIG['billing_reports_email'], html_entity_decode($subject), $bodytext, $extra_headers, '');
    $mime->attach($csv, "Billable report", OCTET, BASE64, "filename=billable_incidents_{$lastmonth}_{$currentyear}.csv");
    return $mime->send_mail();
}

// =======================================================================================
$actions = schedule_actions_due();
if ($actions !== FALSE)
{
    foreach ($actions AS $action => $params)
    {
        $fn = "saction_{$action}";
        echo "<strong>{$fn}()</strong> ";
        // Possibly initiate a trigger here named TRIGGER_SCHED_{$action} ?
        if (function_exists($fn))
        {
            $success = $fn($params);
            schedule_action_done($action, $success);
        }
        else schedule_action_done($action, FALSE);
        if ($success) echo "TRUE<br />";
        else echo "FALSE<br />";
    }
}
plugin_do('automata');

?>
