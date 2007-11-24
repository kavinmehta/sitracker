<?php
// incidents_table.inc.php - Prints out a table of incidents based on the query that was executed in the page that included this file
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

// This Page Is Valid XHTML 1.0 Transitional!

// Prevent script from being run directly (ie. it must always be included
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']))
{
    exit;
}

if ($CONFIG['debug']) echo "<!-- Support Incidents Table -->";

if(empty($incidents_minimal)) echo "<table align='center' style='width:95%;'>";
else echo  "<table align='center' style='width:99%;'>";

if(!empty($incidents_minimal)) echo "<col width='15%'></col>";
else echo "<col width='10%'></col>";
if(!empty($incidents_minimal)) echo "<col width='30%'></col>";
else echo "<col width='23%'></col>";
if(!empty($incidents_minimal)) echo "<col width='23%'></col>";
else echo "<col width='17%'></col>";
if(!empty($incidents_minimal)) echo "<col width='7%'></col>";
else echo "<col width='7%'></col>";
if(empty($incidents_minimal)) echo "<col width='10%'></col>";
if(!empty($incidents_minimal)) echo "<col width='30%'></col>";
else echo "<col width='15%'></col>";
if(!empty($incidents_minimal)) echo "<col width='15%'></col>";
else echo "<col width='10%'></col>";
if(empty($incidents_minimal)) echo "<col width='10%'></col>";
if(empty($incidents_minimal)) echo "<col width='8%'></col>";

echo "<tr>";

$filter=array('queue' => $queue,
              'user' => $user,
              'type' => $type);
echo colheader('id',$strID,$sort, $order, $filter);
echo colheader('title',$strTitle,$sort, $order, $filter);
echo colheader('contact',$strContact,$sort, $order, $filter);
echo colheader('priority',$strPriority,$sort, $order, $filter);
if(empty($incidents_minimal)) echo colheader('status',$strStatus,$sort, $order, $filter);
echo colheader('lastupdated',$strLastUpdated,$sort, $order, $filter);
if(empty($incidents_minimal)) echo colheader('nextaction',$strSLATarget,$sort, $order, $filter);
if(empty($incidents_minimal)) echo colheader('duration',$strInfo,$sort, $order, $filter);
echo "</tr>";
// Display the Support Incidents Themselves
$shade = 0;
while ($incidents = mysql_fetch_array($result))
{
    // calculate time to next action string
    if ($incidents["timeofnextaction"] == 0) $timetonextaction_string = "&nbsp;";  // was 'no time set'
    else
    {
        if (($incidents["timeofnextaction"] - $now) > 0) $timetonextaction_string = format_seconds($incidents["timeofnextaction"] - $now);
        else $timetonextaction_string = "<strong>{$strNow}</strong>";
    }
    // Make a readable site name
    $site = site_name($incidents['siteid']);
    $site = strlen($site) > 30 ? substr($site,0,30)."..." : $site;

    // Make a readble last updated field
    if ($incidents['lastupdated'] > $now - 300)
    {
        $when = sprintf($strAgo, format_seconds($now - $incidents['lastupdated']));
        if($when == 0) $when = $strJustNow;
        $updated = "<em style='color: #640000; font-weight: bolder;'>{$when}</em>";
    }
    elseif ($incidents['lastupdated'] > $now - 1800)
        $updated = "<em style='color: #640000;'>".sprintf($strAgo, format_seconds($now - $incidents['lastupdated']))."</em>";
    elseif ($incidents['lastupdated'] > $now - 3600)
        $updated = "<em>".sprintf($strAgo, format_seconds($now - $incidents['lastupdated']))."</em>";
    elseif (date('dmy', $incidents['lastupdated']) == date('dmy', $now))
        $updated = "{$strToday} @ ".date($CONFIG['dateformat_time'], $incidents['lastupdated']);
    elseif (date('dmy', $incidents['lastupdated']) == date('dmy', ($now-86400)))
        $updated = "{$strYesterday} @ ".date($CONFIG['dateformat_time'], $incidents['lastupdated']);
    elseif ($incidents['lastupdated'] < $now-86400 AND
            $incidents['lastupdated'] > $now-(86400*6))
        $updated = date('l', $incidents['lastupdated'])." @ ".date($CONFIG['dateformat_time'], $incidents['lastupdated']);
    else
        $updated = date($CONFIG['dateformat_datetime'], $incidents["lastupdated"]);

    // Fudge for old ones
    $tag = $incidents['servicelevel'];
    if ($tag=='') $tag = servicelevel_id2tag(maintenance_servicelevel($incidents['maintenanceid']));

    $slsql = "SELECT * FROM servicelevels WHERE tag='{$tag}' AND priority='{$incidents['priority']}' ";
    $slresult = mysql_query($slsql);
    if (mysql_error()) trigger_error("mysql query error ".mysql_error(), E_USER_ERROR);
    $servicelevel = mysql_fetch_object($slresult);
    if (mysql_num_rows($slresult) < 1) trigger_error("could not retrieve service level ($slsql)", E_USER_WARNING);

    // Get Last Update
    list($update_userid, $update_type, $update_currentowner, $update_currentstatus, $update_body, $update_timestamp, $update_nextaction, $update_id)=incident_lastupdate($incidents['id']);

    // Get next target
    $target = incident_get_next_target($incidents['id']);
    $working_day_mins = ($CONFIG['end_working_day'] - $CONFIG['start_working_day']) / 60;
    // Calculate time remaining in SLA
    switch ($target->type)
    {
        case 'initialresponse': $slatarget=$servicelevel->initial_response_mins; break;
        case 'probdef': $slatarget=$servicelevel->prob_determ_mins; break;
        case 'actionplan': $slatarget=$servicelevel->action_plan_mins; break;
        case 'solution': $slatarget=($servicelevel->resolution_days * $working_day_mins); break;
        default: $slaremain=0; $slatarget=0;
    }
    if ($slatarget >0) $slaremain=($slatarget - $target->since);
    else $slaremain=0;

    // Get next review time
    $reviewsince = incident_get_next_review($incidents['id']);  // time since last review in minutes
    $reviewtarget=($servicelevel->review_days * 1440);          // how often reviews should happen in minutes
    if ($reviewtarget >0) $reviewremain=($reviewtarget - $reviewsince);
    else $reviewremain=0;

    ##echo "<!-- target-info: ";
    ##print_r($target);
    ##echo "-->";

    // Remove Tags from update Body
    $update_body = parse_updatebody($update_body);
    $update_user = user_realname($update_userid,TRUE);

    // ======= Row Colors / Shading =======
    // Define Row Shading lowest to highest priority so that unimportant colors are overwritten by important ones
    switch($queue)
    {
        case 1: // Action Needed
            $class='shade2';
            $explain='';
            if ($slaremain >= 1)
            {
                if (($slaremain - ($slatarget * ((100 - $CONFIG['notice_threshold']) /100))) < 0 ) $class='notice';
                if (($slaremain - ($slatarget * ((100 - $CONFIG['urgent_threshold']) /100))) < 0 ) $class='urgent';
                if (($slaremain - ($slatarget * ((100 - $CONFIG['critical_threshold']) /100))) < 0 ) $class='critical';
                if ($incidents["priority"]==4) $class='critical';  // Force critical incidents to be critical always
            }
            elseif ($slaremain < 0) $class='critical';
            else
            {
                $class='shade1';
                $explain='';  // No&nbsp;Target
            }
            // if ($target->time > $now + ($target->targetval * 0.10 )) $class='critical';
        break;

        case 2: // Waiting
            $class='idle';
            $explain='No Action Set';
        break;

        case 3: // All Open
            $class='shade2';
            $explain='No Action Set';
        break;

        case 4: // All Closed
            $class='expired';
            $explain='No Action';
        break;
    }

    // Set Next Action text if not already set
    if ($update_nextaction=='') $update_nextaction=$explain;

    // Create URL for External ID's
    $externalid='';
    $escalationpath=$incidents['escalationpath'];
    if (!empty($incidents['escalationpath']) AND !empty($incidents['externalid']))
    {
        $epathurl = str_replace('%externalid%',$incidents['externalid'],$epath[$escalationpath]['track_url']);
        $externalid = "<a href='{$epathurl}' title='{$epath[$escalationpath]['url_title']}'>".stripslashes($incidents['externalid'])."</a>";
    }
    elseif (empty($incidents['externalid']) AND $incidents['escalationpath']>=1)
    {
        $epathurl = $epath[$escalationpath]['home_url'];
        $externalid = "<a href='{$epathurl}' title='{$epath[$escalationpath]['url_title']}'>{$epath[$escalationpath]['name']}</a>";
    }
    elseif (empty($incidents['escalationpath']) AND !empty($incidents['externalid'])) $externalid = format_external_id($incidents['externalid']);
    echo "<tr class='{$class}'>";
    echo "<td align='center'>";
    // Note: Sales incident type is obsolete
    if ($incidents['type']!='Support') echo "<strong>".ucfirst($incidents['type'])."</strong>: ";
    echo "<a href='incident_details.php?id={$incidents['id']}' style='color: #000000;'>{$incidents['id']}</a>";
    if ($externalid != "") echo "<br />{$externalid}";
    echo "</td>";
    echo "<td>";
    if (!empty($incidents['softwareid'])) echo software_name($incidents['softwareid'])."<br />";
    echo "<a href=\"javascript:incident_details_window('{$incidents['id']}','incident{$incidents['id']}')\" class='info'>";
    if (trim($incidents['title']) !='') echo (stripslashes($incidents['title'])); else echo $strUntitled;
    if (!empty($update_body) AND $update_body!='...') echo "<span>{$update_body}</span>";
    else
    {
        $update_currentownername = user_realname($update_currentowner,TRUE);
        $update_headertext = $updatetypes[$update_type]['text'];
        $update_headertext = str_replace('currentowner', $update_currentownername,$update_headertext);
        $update_headertext = str_replace('updateuser', $update_user, $update_headertext);
        echo "<span>{$update_headertext} on ".date($CONFIG['dateformat_datetime'],$update_timestamp)." </span>";
    }
    echo "</a></td>";

    echo "<td valign='top'>";
    echo "<a href='contact_details.php?id={$incidents['contactid']}' class='info'><span>{$incidents['phone']}<br />{$incidents['email']}</span>".stripslashes($incidents['forenames'].' '.$incidents['surname'])."</a><br />".htmlspecialchars($site)." </td>";

    echo "<td align='center' valign='top' >";
    // Service Level / Priority
    if (!empty($incidents['maintenanceid'])) echo $servicelevel->tag."<br />";
    elseif (!empty($incidents['servicelevel'])) echo $incidents['servicelevel']."<br />";
    else echo "Unknown service level<br />";
    $blinktime=(time()-($servicelevel->initial_response_mins * 60));
    if ($incidents['priority']==4 AND $incidents['lastupdated']<= $blinktime) echo "<strong style='text-decoration: blink;'>".priority_name($incidents["priority"])."</strong>";
        else echo priority_name($incidents['priority']);
    echo "</td>\n";

    if(empty($incidents_minimal))
    {
        echo "<td align='center' valign='top'>";
        echo incidentstatus_name($incidents["status"]);
        if ($incidents['status']==2) echo "<br />".closingstatus_name($incidents['closingstatus']);
        echo "</td>\n";
    }
    echo "<td align='center' valign='top'>";
    echo "{$updated}";
    if(empty($incidents_minimal)) echo "<br />by {$update_user}";

    if(empty($incidents_minimal))
    {
        if ($incidents['towner'] > 0 AND $incidents['towner']!=$user) echo "<br />Temp: <strong>".user_realname($incidents['towner'],TRUE)."</strong>";
        elseif ($incidents['owner']!=$user) echo "<br />{$strOwner}: <strong>".user_realname($incidents['owner'],TRUE)."</strong>";
    }
    echo "</td>\n";

    if(empty($incidents_minimal))
    {
        echo "<td align='center' valign='top' title='{$explain}'>";
        // Next Action
        /*
            if ($target->time > $now) echo target_type_name($target->type);
            else echo "<strong style='color: red; background-color: white;'>&nbsp;".target_type_name($target->type)."&nbsp;</strong>";
        */
        $targettype = target_type_name($target->type);
        if ($targettype!='')
        {
            echo $targettype;
            if ($slaremain > 0)
            {
                echo "<br />in ".format_workday_minutes($slaremain);  //  ." left"
            }
            elseif ($slaremain < 0)
            {
                echo "<br />".format_workday_minutes((0-$slaremain))." late";  //  ." left"
            }
        }
        else
        {
            ## Don't print anything, because there is no target to meet
            //echo "...";
        }
        ##print_r($target);

        echo "</td>";
    }


    ##echo target_type_name($target->type);
    ##echo "<br />";
    ##if ($update_nextaction!=target_type_name($target->type))
    ##  echo "$update_nextaction";
    ##if (!empty($timetonextactionstring)) echo "<br />$timetonextaction_string";
    if(empty($incidents_minimal))
    {
        // Final column
        if ($reviewremain>0 && $reviewremain<=2400)
        {
            // Only display if review is due in the next five days
            echo "<td align='center' valign='top'>";
            echo sprintf($strReviewIn, format_workday_minutes($reviewremain));
        }
        elseif ($reviewremain<=0)
        {
            echo "<td align='center' valign='top' class='review'>";
            if ($reviewremain > -86400) echo "<img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/16x16/review.png' width='16' height='16' alt='' /> {$strReviewDueNow}";
            else echo "<img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/16x16/review.png' width='16' height='16' alt='' /> ".sprintf($strReviewDueAgo ,format_workday_minutes($reviewremain*-1));
        }
        else
        {
            echo "<td align='center' valign='top'>";
            if ($incidents['status'] == 2) echo "{$strAge}: ".format_seconds($incidents["duration_closed"]);
            else echo format_seconds($incidents["duration"])." old";
        }
        echo "</td>";
    }
    echo "</tr>\n";
}
echo "</table>\n\n";
if(empty($incidents_minimal) && $user != 'all')
    if($rowcount != 1) echo "<p align='center'>".sprintf($strNumIncidents, $rowcount)."</p>";
    else echo "<p align='center'>".sprintf($strSingleIncident, $rowcount)."</p>";
if ($CONFIG['debug']) echo "<!-- End of Support Incidents Table -->\n";
?>