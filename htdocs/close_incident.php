 <?php
// close_incident.php - Display a form for closing an incident
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

@include ('set_include_path.inc.php');
$permission = 18; //  Close Incidents

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

// External Variables
$id = cleanvar($_REQUEST['id']);
$incidentid = $id;

$title = $strClose;

// No submit detected show closure form
if (empty($_REQUEST['process']))
{
    $sql = "SELECT owner FROM `{$dbIncidents}` WHERE id = '{$incidentid}'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    list($owner) = mysql_fetch_row($result);

    if ($owner == 0)
    {
        html_redirect("incident_details.php?id={$incidentid}", FALSE, $strCallMustBeAssignedBeforeClosure);
        exit;
    }

    if (open_activities_for_incident($incidentid) > 0)
    {
        html_redirect("incident_details.php?id={$incidentid}", FALSE, $strMustCompleteActivitiesBeforeClosure);
        exit;
    }

    include('incident_html_top.inc.php');

    ?>
    <script type="text/javascript">
    <!--
    function enablekb()
    {
        // INL 28Nov07 Yes I know a lot of this javascript is horrible
        // it's old and I'm tired and can't be bothered right now
        // the newer stuff at the bottom is pretty and uses prototype.js
        // syntax
        if (document.closeform.kbtitle.disabled==true)
        {
            // Enable KB
            document.closeform.kbtitle.disabled=false;
            //document.closeform.cust_vis1.disabled=true;
            //document.closeform.cust_vis1.checked=true;
            //document.closeform.cust_vis2.checked=true;
            //document.closeform.cust_vis2.disabled=true;
            // Enable KB includes
            //document.closeform.incsummary.disabled=false;
            document.closeform.summary.disabled=false;
            document.closeform.incsymptoms.disabled=false;
            document.closeform.symptoms.disabled=false;
            document.closeform.inccause.disabled=false;
            document.closeform.cause.disabled=false;
            document.closeform.incquestion.disabled=false;
            document.closeform.question.disabled=false;
            document.closeform.incanswer.disabled=false;
            document.closeform.answer.disabled=false;
            //document.closeform.incsolution.disabled=false;
            document.closeform.solution.disabled=false;
            document.closeform.incworkaround.disabled=false;
            document.closeform.workaround.disabled=false;
            document.closeform.incstatus.disabled=false;
            document.closeform.status.disabled=false;
            document.closeform.incadditional.disabled=false;
            document.closeform.additional.disabled=false;
            document.closeform.increferences.disabled=false;
            document.closeform.references.disabled=false;
            if (document.all)
            {
            document.all('helptext').innerHTML = "Select the sections you'd like to include in the article by checking the boxes beside each heading, you can add further sections later.  You don't need to include all sections, just use the ones that are relevant.<br /><strong>Knowledge Base Article</strong>:";
            }
            else if (document.getElementById)
            {
            document.getElementById('helptext').innerHTML = "Select the sections you'd like to include in the article by checking the boxes beside each heading, you can add further sections later.  You don't need to include all sections, just use the ones that are relevant.<br /><strong>Knowledge Base Article</strong>:";
            }
            // Show the table rows for KB article
            $('titlerow').show();
            $('symptomsrow').show();
            $('causerow').show();
            $('questionrow').show();
            $('answerrow').show();
            $('workaroundrow').show();
            $('statusrow').show();
            $('inforow').show();
            $('referencesrow').show();
        }
        else
        {
            // Disable KB
            document.closeform.kbtitle.disabled=true;
            //document.closeform.cust_vis1.disabled=false;
            //document.closeform.cust_vis2.disabled=false;
            // Disable KB includes
            document.closeform.incsymptoms.checked=false;
            document.closeform.incsymptoms.disabled=true;
            document.closeform.symptoms.disabled=true;
            document.closeform.inccause.checked=false;
            document.closeform.inccause.disabled=true;
            document.closeform.cause.disabled=true;
            document.closeform.incquestion.checked=false;
            document.closeform.incquestion.disabled=true;
            document.closeform.question.disabled=true;
            document.closeform.incanswer.checked=false;
            document.closeform.incanswer.disabled=true;
            document.closeform.answer.disabled=true;
            // document.closeform.incsolution.checked=false;
            // document.closeform.incsolution.disabled=true;
            // document.closeform.solution.disabled=true;
            document.closeform.incworkaround.checked=false;
            document.closeform.incworkaround.disabled=true;
            document.closeform.workaround.disabled=true;
            document.closeform.incstatus.checked=false;
            document.closeform.incstatus.disabled=true;
            document.closeform.status.disabled=true;
            document.closeform.incadditional.checked=false;
            document.closeform.incadditional.disabled=true;
            document.closeform.additional.disabled=true;
            document.closeform.increferences.checked=false;
            document.closeform.increferences.disabled=true;
            document.closeform.references.disabled=true;
            document.closeform.incworkaround.checked=false;
            document.closeform.incworkaround.disabled=true;
            document.closeform.workaround.disabled=true;
            if (document.all)
            {
                document.all('helptext').innerHTML = "Enter some details about the incident to be stored in the incident log for future use.  You should provide a summary of the problem and information about how it was resolved.<br /><strong>Final Update</strong>:";
            }
            else if (document.getElementById)
            {
                document.getElementById('helptext').innerHTML = "Enter some details about the incident to be stored in the incident log for future use.  You should provide a summary of the problem and information about how it was resolved.<br /><strong>Final Update</strong>:";
            }
            // Hide the table rows for KB article
            $('titlerow').hide();
            $('symptomsrow').hide();
            $('causerow').hide();
            $('questionrow').hide();
            $('answerrow').hide();
            $('workaroundrow').hide();
            $('statusrow').hide();
            $('inforow').hide();
            $('referencesrow').hide();
        }
    }

    function editbox(object, boxname)
    {
        var boxname;
        object.boxname.disabled=true;
    }

    -->
    </script>
    <?php

    echo "<form name='closeform' action='{$_SERVER['PHP_SELF']}' method='post'>";
    echo "<table class='vertical' width='100%'>";
    echo "<tr><th width='30%'>{$strClose}:</th>";
    echo "<td><label><input type='radio' name='wait' value='yes' checked='checked' />";
    echo "{$strMarkForClosure}</label><br />";
    echo "<label><input type='radio' name='wait' value='no' />{$strCloseImmediately}</label></td></tr>\n";
    echo "<tr><th>{$strKnowledgeBase}";
    echo "</th><td><label><input type='checkbox' name='kbarticle' onchange='enablekb();' value='yes' />";
    echo "{$strNewKBArticle}</label></td></tr>\n";

    echo "<tr id='titlerow' style='display:none;'><th>{$strTitle}</th>";
    echo "<td><input type='text' name='kbtitle' id='kbtitle' size='30' value='{$incident_title}' disabled='disabled' />";
    echo "</td></tr>\n";
    echo "<tr><th>&nbsp;</th><td>";
    echo "<span id='helptext'>{$strEnterDetailsAboutIncidentToBeStoredInLog}";
    echo "{$strSummaryOfProblemAndResolution}<br /><strong>{$strFinalUpdate}</strong>:</span></td></tr>\n";

    echo "<tr><th>{$strSummary}:<sup class='red'>*</sup>\n ";
    echo "<input type='checkbox' name='incsummary' onclick=\"if (this.checked) {document.closeform.summary.disabled = false; ";
    echo "document.closeform.summary.style.display='';} else { saveValue=document.closeform.summary.value; ";
    echo "document.closeform.summary.disabled = true; document.closeform.summary.style.display='none';}\" checked='checked' disabled='disabled' /></th>";

    echo "<td>{$strSummaryOfProblem}<br />\n";
    echo "<textarea id='summary' name='summary' cols='40' rows='8' onfocus=\"if (this.enabled) { this.value = saveValue; ";
    echo "setTimeout('document.articlform.summary.blur()',1); } else saveValue=this.value;\">";

    //  style="display: none;"
    $sql = "SELECT * FROM `{$dbUpdates}` WHERE incidentid='$id' AND type='probdef' ORDER BY timestamp ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    while ($row = mysql_fetch_object($result))
    {
        echo $row->bodytext;
        echo "\n\n";
    }
    echo "</textarea>\n";
    echo "</td></tr>";

    echo "<tr id='symptomsrow' style='display:none;'><th><label>{$strSymptoms}: <input type='checkbox' name='incsymptoms' onclick=\"if (this.checked) {document.closeform.symptoms.disabled = false; document.closeform.symptoms.style.display=''} else { saveValue=document.closeform.symptoms.value; document.closeform.symptoms.disabled = true; document.closeform.symptoms.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='symptoms' name='symptoms' cols='40' style='display: none;' rows='8' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.symptoms.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr id='causerow' style='display:none;'><th><label>{$strCause}: <input type='checkbox' name='inccause' onclick=\"if (this.checked) {document.closeform.cause.disabled = false; document.closeform.cause.style.display=''} else { saveValue=document.closeform.cause.value; document.closeform.cause.disabled = true; document.closeform.cause.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='cause' name='cause' cols='40' rows='8' style='display: none;' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.cause.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr id='questionrow' style='display:none;'><th><label>{$strQuestion}: <input type='checkbox' name='incquestion' onclick=\"if (this.checked) {document.closeform.question.disabled = false; document.closeform.question.style.display=''} else { saveValue=document.closeform.question.value; document.closeform.question.disabled = true; document.closeform.question.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='question' name='question' cols='40' rows='8' style='display: none;' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.question.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr id='answerrow' style='display:none;'><th><label>{$strAnswer}: <input type='checkbox' name='incanswer' onclick=\"if (this.checked) {document.closeform.answer.disabled = false; document.closeform.answer.style.display=''} else { saveValue=document.closeform.answer.value; document.closeform.answer.disabled = true; document.closeform.answer.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='answer' name='answer' cols='40' rows='8' style='display: none;' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.answer.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr><th><label>{$strSolution}: <sup class='red'>*</sup><input type='checkbox' name='incsolution' onclick=\"if (this.checked) {document.closeform.solution.disabled = false; document.closeform.solution.style.display=''} else { saveValue=document.closeform.solution.value; document.closeform.solution.disabled = true; document.closeform.solution.style.display='none'}\" checked='checked' disabled='disabled' /></label></th>";

    echo "<td><textarea id='solution' name='solution' cols='40' rows='8' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articleform.solution.blur()',1); } else saveValue=this.value;\">";
    $sql = "SELECT * FROM `{$dbUpdates}` WHERE incidentid='$id' AND type='solution' ORDER BY timestamp ASC";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    while ($row = mysql_fetch_object($result))
    {
        echo trim($row->bodytext);
        echo "\n\n";
    }
    echo "</textarea>\n";
    echo "</td></tr>";

    echo "<tr id='workaroundrow' style='display:none;'><th><label>{$strWorkaround}: <input type='checkbox' name='incworkaround' onclick=\"if (this.checked) {document.closeform.workaround.disabled = false; document.closeform.workaround.style.display=''} else { saveValue=document.closeform.workaround.value; document.closeform.workaround.disabled = true; document.closeform.workaround.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='workaround' name='workaround' cols='40' rows='8' style='display: none;' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.workaround.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr id='statusrow' style='display:none;'><th><label>{$strStatus}: <input type='checkbox' name='incstatus' onclick=\"if (this.checked) {document.closeform.status.disabled = false; document.closeform.status.style.display=''} else { saveValue=document.closeform.status.value; document.closeform.status.disabled = true; document.closeform.status.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='status' name='status' cols='40' rows='8' style='display: none;' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.status.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr id='inforow' style='display:none;'><th><label>{$strAdditionalInfo}: <input type='checkbox' name='incadditional' onclick=\"if (this.checked) {document.closeform.additional.disabled = false; document.closeform.additional.style.display=''} else { saveValue=document.closeform.additional.value; document.closeform.additional.disabled = true; document.closeform.additional.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='additional' name='additional' cols='40' rows='8' style='display: none;' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.additional.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr id='referencesrow' style='display:none;'><th><label>{$strReferences}: <input type='checkbox' name='increferences' onclick=\"if (this.checked) {document.closeform.references.disabled = false; document.closeform.references.style.display=''} else { saveValue=document.closeform.references.value; document.closeform.references.disabled = true; document.closeform.references.style.display='none'}\" disabled='disabled' /></label></th>";
    echo "<td><textarea id='references' name='references' cols='40' rows='8' style='display: none;' onfocus=\"if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.references.blur()',1); } else saveValue=this.value;\"></textarea></td></tr>";

    echo "<tr><th>{$strClosingStatus}: <sup class='red'>*</sup></th><td>".closingstatus_drop_down("closingstatus", 0)."</td></tr>\n";
    echo "<tr><th>".sprintf($strInformX, $strCustomer).":</th>";
    echo "<td>{$strSendEmailExplainingIncidentClosure}<br />";
    echo "<label><input name='send_email' checked='checked' type='radio' value='no' />{$strNo}</label> ";
    echo "<input name='send_email' type='radio' value='yes' />{$strYes}</td></tr>\n";
    $externalemail=incident_externalemail($id);
    if ($externalemail)
    {
        echo "<tr><th>".sprintf($strInformX, $strExternalEngineer).":<br />";
        printf($strSendEmailExternalIncidentClosure, "<em>{$externalemail}</em>");
        echo "</th>";
        echo "<td class='shade2'><label><input name='send_engineer_email' type='radio' value='no' />{$strNo}</label> ";
        echo "<label><input name='send_engineer_email' type='radio' value='yes' checked='checked' />{$strYes}</label></td></tr>\n";
    }
    echo "</table>\n";
    echo "<p align='center'>";
    echo "<input name='type' type='hidden' value='Support' />";
    echo "<input name='id' type='hidden' value='$id' />";
    echo "<input type='hidden' name='process' value='closeincident' />";
    echo "<input name='submit' type='submit' value=\"{$strClose}\" /></p>";
    echo "</form>";
    include ('incident_html_bottom.inc.php');
}
else
{
    // External variables
    $closingstatus = cleanvar($_POST['closingstatus']);
    $summary = cleanvar($_POST['summary']);
    $id = cleanvar($_POST['id']);
    $solution = cleanvar($_POST['solution']);
    $kbarticle = cleanvar($_POST['kbarticle']);
    $kbtitle = cleanvar($_POST['kbtitle']);
    $symptoms = cleanvar($_POST['symptoms']);
    $cause = cleanvar($_POST['cause']);
    $question = cleanvar($_POST['question']);
    $answer = cleanvar($_POST['answer']);
    $workaround = cleanvar($_POST['workaround']);
    $status = cleanvar($_POST['status']);
    $additional = cleanvar($_POST['additional']);
    $references = cleanvar($_POST['references']);
    $wait = cleanvar($_POST['wait']);
    $send_email = cleanvar($_POST['send_email']);

    // Close the incident
    $errors = 0;

    // check for blank closing status field
    if ($closingstatus == 0)
    {
        $errors = 1;
        $error_string = "<p class='error'>{$strMustSelectClosingStatus}</p>\n";
    }

    if ($_REQUEST['summary']=='' && $_REQUEST['solution']=='')
    {
        $errors = 1;
        $error_string = "<p class='error'>{$strMustEnterTextInBothSummaryAndSolution}</P>\n";
    }

    if ($errors == 0)
    {
        $addition_errors = 0;

        // update incident
        if ($wait=='yes')
        {
            // mark incident as awaiting closure
            $timeofnextaction = $now + $CONFIG['closure_delay'];
            $sql = "UPDATE `{$dbIncidents}` SET status='7', lastupdated='$now', timeofnextaction='$timeofnextaction' WHERE id='$id'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        }
        else
        {
            // mark incident as closed
            $sql = "UPDATE `{$dbIncidents}` SET status='2', closingstatus='$closingstatus', lastupdated='$now', closed='$now' WHERE id='$id'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        }

        if (!$result)
        {
            $addition_errors = 1;
            $addition_errors_string .= "<p class='error'>Update of incident failed</p>\n";
        }

        // add update(s)
        if ($addition_errors == 0)
        {
            ## if ($cust_vis == "yes") $show='show'; else $show='hide';
            if ($_REQUEST['kbarticle'] != 'yes')
            {
                // No KB Article, so just add updates to log for Summary and Solution
                if (strlen($_REQUEST['summary']) > 3)
                {
                    // Problem Definition
                    $sql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp, customervisibility) ";
                    $sql .= "VALUES ('$id', '$sit[2]', 'probdef', '$summary', '$now', 'hide')";
                    $result = mysql_query($sql);
                    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                }

                if (strlen($_REQUEST['solution']) > 3)
                {
                    // Final Solution
                    $sql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp, customervisibility) ";
                    $sql .= "VALUES ('$id', '$sit[2]', 'solution', '$solution', '$now', 'hide')";
                    $result = mysql_query($sql);
                    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                }
            }

            // Meet service level 'solution'
            $sql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, timestamp, currentowner, customervisibility, sla, bodytext) ";
            $sql .= "VALUES ('$id', '".$sit[2]."', 'slamet', '$now', '{$sit[2]}', 'show', 'solution','')";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            //
            if ($wait=='yes')
            {
                // Update - mark for closure
                $sql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp) ";
                $sql .= "VALUES ('$id', '{$sit[2]}', 'closing', 'Marked for Closure', '$now')";
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
            }
            else
            {
                // Update - close immediately
                $sql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp) ";
                $sql .= "VALUES ('$id', '{$sit[2]}', 'closing', 'Incident Closed', '$now')";
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
            }

            // Make Journal Entry
            //FIXME add trigger for this
            journal(CFG_LOGGING_NORMAL, 'Incident Closed',"Incident $id was closed",CFG_JOURNAL_SUPPORT,$id);
            if (incident_owner($id) != $sit[2])
            {
                trigger("TRIGGER_INCIDENT_OWNED_CLOSED_BY_USER", array('incidentid' => id, 'userid' => incident_owner($id), 'closedby' => $sit[2]));
            }


            if (!$result)
            {
                $addition_errors = 1;
                $addition_errors_string .= "<p class='error'>{$strUpdateIncidentFailed}</p>\n";
            }

            //notify related inicdents this has been closed
            $sql = "SELECT distinct (relatedid) FROM `{$dbRelatedIncidents}` AS r, `{$dbIncidents}` AS i WHERE incidentid = '$id' ";
            $sql .= "AND i.id = r.relatedid AND i.status != 2";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            $relatedincidents;

            while ($a = mysql_fetch_array($result))
            {
                $relatedincidents[] = $a[0];
            }

            $sql = "SELECT distinct (incidentid) FROM `{$dbRelatedIncidents}` AS r, `{$dbIncidents}` AS i WHERE relatedid = '$id' ";
            $sql .= "AND i.id = r.incidentid AND i.status != 2";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            while ($a = mysql_fetch_array($result))
            {
                $relatedincidents[] = $a[0];
            }
            if (is_array($relatedincidents))
            {
                $uniquearray = array_unique($relatedincidents);

                foreach ($uniquearray AS $relatedid)
                {
                    //dont care if I'm related to myself
                    if ($relatedid != $id)
                    {
                        $sql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp) ";
                        $sql .= "VALUES ('$relatedid', '{$sit[2]}', 'research', 'New Status: [b]Active[/b]<hr>\nRelated incident [$id] has been closed', '$now')";
                        $result = mysql_query($sql);
                        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

                        $sql = "UPDATE `{$dbIncidents}` SET status = 1 WHERE id = '$relatedid'";
                        $result = mysql_query($sql);
                        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                    }
                }
            }
            //tidy up temp reassigns
            $sql = "DELETE FROM `{$dbTempAssigns}` WHERE incidentid = '$id'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        }
        $bodytext = "Closing Status: <b>" . closingstatus_name($closingstatus) . "</b>\n\n" . $bodytext;

        if ($addition_errors == 0)
        {   //maintenceid
            $send_feedback = send_feedback(db_read_column('maintenanceid', $dbIncidents, $id));
            if ($CONFIG['feedback_form'] != '' AND $CONFIG['feedback_form'] > 0 AND $send_feedback == TRUE)
            {
                create_incident_feedback($CONFIG['feedback_form'], $id);
            }

            plugin_do('incident_closed');

            if ($send_engineer_email == 'yes')
            {
                $eml=send_template_email('INCIDENT_CLOSED_EXTERNAL', $id);  // close with external engineer
                if (!$eml) throw_error('!Error: Failed while sending close with engineer email, error code: ', $eml);
            }

            if ($send_email == 'yes')
            {
                if ($wait=='yes')
                {
                    // send awaiting closure email
                    $eml=send_template_email('INCIDENT_CLOSURE', $id);  // awaiting closure
                    if (!$eml) throw_error('!Error: Failed while sending awaiting closure email to customer, error code:', $eml);
                }
                else
                {
                    // send incident closed email
                    $eml=send_template_email('INCIDENT_CLOSED', $id);  // incident closed
                    if (!$eml) throw_error('!Error: Failed while sending incident closed email to customer, error code:', $eml);
                }
            }

            // Tidy up drafts i.e. delete
            $draft_sql = "DELETE FROM `{$dbDrafts}` WHERE incidentid = {$id}";
            mysql_query($draft_sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            // Check for knowledge base stuff, prior to confirming:
            if ($_REQUEST['kbarticle']=='yes')
            {
                $sql = "INSERT INTO `{$dbKBArticles}` (doctype, title, distribution, author, published, keywords) VALUES ";
                $sql .= "('1', ";
                $sql .= "'{$kbtitle}', ";
                $sql .= "'public', ";
                $sql .= "'".mysql_real_escape_string($sit[2])."', ";
                $sql .= "'".date('Y-m-d H:i:s', mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y')))."', ";
                $sql .= "'[$id]') ";
                mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                $docid = mysql_insert_id();

                // Update the incident to say that a KB article was created, with the KB Article number
                $update = "<b>Knowledge base article</b> created from this incident, see: {$CONFIG['kb_id_prefix']}".leading_zero(4,$docid);
                $sql  = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, bodytext, timestamp) ";
                $sql .= "VALUES ('$id', '$sit[2]', 'default', '$update', '$now')";
                $result = mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);


                // Get softwareid from Incident record
                $sql = "SELECT softwareid FROM `{$dbIncidents}` WHERE id='$id'";
                $result=mysql_query($sql);
                if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                list($softwareid)=mysql_fetch_row($result);

                if (!empty($_POST['summary'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Summary', '1', '{$summary}', 'private') ";
                if (!empty($_POST['symptoms'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Symptoms', '1', '{$symptoms}', 'private') ";
                if (!empty($_POST['cause'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Cause', '1', '{$cause}', 'private') ";
                if (!empty($_POST['question'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Question', '1', '{$question}', 'private') ";
                if (!empty($_POST['answer'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Answer', '1', '{$answer}', 'private') ";
                if (!empty($_POST['solution'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Solution', '1', '{$solution}', 'private') ";
                if (!empty($_POST['workaround'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Workaround', '1', '{$workaround}', 'private') ";
                if (!empty($_POST['status'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Status', '1', '{$status}', 'private') ";
                if (!empty($_POST['additional'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Additional Information', '1', '{$additional}', 'private') ";
                if (!empty($_POST['references'])) $query[]="INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'References', '1', '{$references}', 'private') ";

                if (count($query) < 1) $query[] = "INSERT INTO `{$dbKBContent}` (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_real_escape_string($sit[2])."', 'h1', 'Summary', '1', 'Enter details here...', 'restricted') ";

                foreach ($query AS $sql)
                {
                    mysql_query($sql);
                    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                }

                // Add Software Record
                if ($softwareid>0)
                {
                    $sql="INSERT INTO `{$dbKBSoftware}` (docid,softwareid) VALUES ('{$docid}', '{$softwareid}')";
                    mysql_query($sql);
                    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
                    journal(CFG_LOGGING_NORMAL, 'KB Article Added', "KB Article {$docid} was added", CFG_JOURNAL_KB, $docid);
                }

                //html_redirect("incident_details.php?id={$id}", TRUE, "Knowledge Base Article {$CONFIG['kb_id_prefix']}{$docid} created");

                echo "<html>";
                echo "<head></head>";
                ?>
                <script type="text/javascript">
                function confirm_close_window()
                {
                    window.opener.location='incident_details.php?id=<?php echo $id; ?>';
                    window.close();
                }
                </script>
                <?php
                echo "<body onload=\"confirm_close_window();\">";
                echo "</body>";
                echo "</html>";
            }
            else
            {
                echo "<html>";
                echo "<head></head>";
                ?>
                <script type="text/javascript">
                function confirm_close_window()
                {
                    window.opener.location='incident_details.php?id=<?php echo $id; ?>';
                    window.close();
                }
                </script>
                <?php
                echo "<body onload=\"confirm_close_window();\">";
                echo "</body>";
                echo "</html>";
            }
        }
        else
        {
            include ('incident_html_top.inc.php');
            echo $addition_errors_string;
            include ('incident_html_bottom.inc.php');
        }
    }
    else
    {
        include ('incident_html_top.inc.php');
        echo $error_string;
        include ('incident_html_bottom.inc.php');
    }
}
?>