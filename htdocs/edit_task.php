<?php
// exit_task.php - Edit existing task
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission = 70;

require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');
if(!$CONFIG['tasks_enabled'])
{
    header("Location: main.php");
}
$title = $strEditTask;

// External variables
$action = $_REQUEST['action'];
$id = cleanvar($_REQUEST['id']);
$incident = cleanvar($_REQUEST['incident']);

switch ($action)
{
    case 'edittask':
        // External variables
        $name = cleanvar($_REQUEST['name']);
        $description = cleanvar($_REQUEST['description']);
        $priority = cleanvar($_REQUEST['priority']);
        if (!empty($_REQUEST['duedate']))
        {
            $duedate = strtotime($_REQUEST['duedate']);
        }
        else
        {
            $duedate = '';
        }

        if (!empty($_REQUEST['startdate']))
        {
            $startdate = strtotime($_REQUEST['startdate']);
        }
        else
        {
            $startdate = '';
        }

        $completion = cleanvar(str_replace('%','',$_REQUEST['completion']));
        if ($completion!='' AND !is_numeric($completion)) $completion=0;
        if ($completion > 100) $completion=100;
        if ($completion < 0) $completion=0;
        if (!empty($_REQUEST['enddate']))
        {
            $enddate = strtotime($_REQUEST['enddate']);
        }
        else
        {
            $enddate = '';
        }

        if ($completion==100 AND $enddate == '') $enddate = $now;
        $value = cleanvar($_REQUEST['value']);
        $distribution = cleanvar($_REQUEST['distribution']);
        $old_name = cleanvar($_REQUEST['old_name']);
        $old_description = cleanvar($_REQUEST['old_description']);
        $old_priority = cleanvar($_REQUEST['old_priority']);
        $old_startdate = cleanvar($_REQUEST['old_startdate']);
        $old_duedate = cleanvar($_REQUEST['old_duedate']);
        $old_completion = cleanvar($_REQUEST['old_completion']);
        $old_enddate = cleanvar($_REQUEST['old_enddate']);
        $old_value = cleanvar($_REQUEST['old_value']);
        $old_distribution = cleanvar($_REQUEST['old_distribution']);
        if ($distribution=='public') $tags = cleanvar($_POST['tags']);
        else $tags='';

        // Validate input
        $error=array();
        if ($name=='') $error[]='Task name must not be blank';
        if ($startdate > $duedate AND $duedate != '' AND $duedate > 0 ) $startdate=$duedate;
        if (count($error) >= 1)
        {
            include ('htmlheader.inc.php');
            echo "<p class='error'>Please check the data you entered</p>";
            echo "<ul class='error'>";
            foreach ($error AS $err)
            {
                echo "<li>$err</li>";
            }
            echo "</ul>";
            include ('htmlfooter.inc.php');
        }
        else
        {
            replace_tags(4, $id, $tags);
            if ($startdate > 0) $startdate = date('Y-m-d',$startdate);
            else $startdate = '';
            if ($duedate > 0) $duedate = date('Y-m-d',$duedate);
            else $duedate='';
            if ($enddate > 0) $enddate = date('Y-m-d',$enddate);
            else $enddate='';
            if ($startdate < 1 AND $completion > 0) $startdate = date('Y-m-d H:i:s');
            $sql = "UPDATE tasks ";
            $sql .= "SET name='$name', description='$description', priority='$priority', ";
            $sql .= "duedate='$duedate', startdate='$startdate', ";
            $sql .= "completion='$completion', enddate='$enddate', value='$value', ";
            $sql .= "distribution='$distribution' ";
            $sql .= "WHERE id='$id' LIMIT 1";
            mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            // if (mysql_affected_rows() < 1) trigger_error("Task update failed",E_USER_ERROR);

            // Add a note to say what changed (if required)
            $bodytext='';
            if ($name != $old_name) $bodytext .= "Name: {$old_name} -&gt; [b]{$name}[/b]\n";
            if ($description != $old_description) $bodytext .= "Description: {$old_description} -&gt; [b]{$description}[/b]\n";
            if ($priority != $old_priority) $bodytext .= "Priority: ".priority_name($old_priority)." -&gt; [b]".priority_name($priority)."[/b]\n";
            $old_startdate = substr($old_startdate,0,10);
            if ($startdate != $old_startdate) $bodytext .= "Start Date: {$old_startdate} -&gt; [b]{$startdate}[/b]\n";
            $old_duedate = substr($old_duedate,0,10);
            if ($duedate != $old_duedate) $bodytext .= "Due Date: {$old_duedate} -&gt; [b]{$duedate}[/b]\n";
            if ($completion != $old_completion) $bodytext .= "Completion: {$old_completion}% -&gt; [b]{$completion}%[/b]\n";
            if ($enddate != $old_enddate) $bodytext .= "End Date: {$old_enddate} -&gt; [b]{$enddate}[/b]\n";
            if ($value != $old_value) $bodytext .= "Value: {$old_value} -&gt; [b]{$value}[/b]\n";
            if ($distribution != $old_distribution) $bodytext .= "Privacy: {$old_distribution} -&gt; [b]{$distribution}[/b]\n";
            if (!empty($bodytext))
            {
                $bodytext="Task Edited by {$_SESSION['realname']}:\n\n".$bodytext;
                // Link 10 = Tasks
                $sql = "INSERT INTO `{$dbNotes}` ";
                $sql .= "(userid, bodytext, link, refid) ";
                $sql .= "VALUES ('0', '{$bodytext}', '10',' $id')";
                mysql_query($sql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            }
            html_redirect("view_task.php?id={$id}", TRUE, $strTaskEditedSuccessfully); // FIXME redundant i18n string
        }
    break;

    case 'markcomplete':
        //this task is for an incident, enter an update from all the notes
        if ($incident)
        {
            //get current incident status
            $sql = "SELECT status FROM `{$dbIncidents}` WHERE id={$incident}";
            $result = mysql_query($sql);
            $status = mysql_fetch_object($result);
            $status = $status->status;

            $sql = "SELECT * FROM `{$dbTasks}` WHERE id='{$id}'";

            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            if (mysql_num_rows($result) >= 1)
            {
                $task = mysql_fetch_object($result);
                $startdate = mysql2date($task->startdate);
                $duedate = mysql2date($task->duedate);
                $enddate = mysql2date($task->enddate);
            }

            //get all the notes
            $notearray = array();
            $numnotes = 0;
            $sql = "SELECT * FROM `{$dbNotes}` WHERE link='10' AND refid='{$id}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            if (mysql_num_rows($result) >= 1)
            {
                while ($notes = mysql_fetch_object($result))
                {
                    $notesarray[$numnotes] = $notes;
                    $numnotes++;
                }
            }
            //delete all the notes
            $sql = "DELETE FROM `{$dbNotes}` WHERE refid='{$id}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

            $enddate = $now;
            $duration = $enddate - $startdate;

            $startdate = readable_date($startdate);
            $enddate = readable_date($enddate);

            $updatehtml = "Update created from <a href=\"tasks.php?incident={$incident}\">Activity {$id}</a><br />Activity started: {$startdate}\n\n";
            for($i = $numnotes-1; $i >= 0; $i--)
            {
                $updatehtml .= "[b]".readable_date(mysql2date($notesarray[$i]->timestamp))."[/b]\n{$notesarray[$i]->bodytext}\n\n";
            }
            $updatehtml .= "Activity completed: {$enddate}, duration was: [b]".format_seconds($duration)."[/b]";

            //create update
            $sql = "INSERT INTO `{$dbUpdates}` (incidentid, userid, type, currentstatus, bodytext, timestamp, duration) ";
            $sql .= "VALUES('{$incident}', '{$sit[2]}', 'fromtask', {$status}, '{$updatehtml}', '$now', '$duration')";
            mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

            mark_task_completed($id, TRUE);
        }
        else
        {
            mark_task_completed($id, FALSE);
        }

        // FIXME redundant i18n strings
        if ($incident) html_redirect("tasks.php?incident={$incident}", TRUE, $strActivityMarkedCompleteSuccessfully);
        else html_redirect("tasks.php", TRUE, $strTaskMarkedCompleteSuccessfully);
    break;

    case 'delete':
        $sql = "DELETE FROM `{$dbTasks}` ";
        $sql .= "WHERE id='$id' LIMIT 1";
        mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        $sql = "DELETE FROM `{$dbNotes}` ";
        $sql .= "WHERE link=10 AND refid='$id' ";
        mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        // FIXME redundant i18n strings
        html_redirect("tasks.php", TRUE, $strTaskDeletedSuccessfully);
    break;

    case '':
    default:
        include ('htmlheader.inc.php');
        echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/task.png' width='32' height='32' alt='' /> ";
        echo "$title</h2>";
        $sql = "SELECT * FROM `{$dbTasks}` WHERE id='$id'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        if (mysql_num_rows($result) >= 1)
        {
            while ($task = mysql_fetch_object($result))
            {
                $startdate=mysql2date($task->startdate);
                $duedate=mysql2date($task->duedate);
                $enddate=mysql2date($task->enddate);
                echo "<form id='edittask' action='{$_SERVER['PHP_SELF']}' method='post'>";
                echo "<table class='vertical'>";
                echo "<tr><th>{$strTitle}</th>";
                echo "<td><input type='text' name='name' size='35' maxlength='255' value=\"{$task->name}\" /></td></tr>";
                echo "<tr><th>{$strDescription}</th>";
                echo "<td><textarea name='description' rows='4' cols='30'>{$task->description}</textarea></td></tr>";
                if ($task->distribution=='public')
                {
                    echo "<tr><th>{$strTags}:</th>";
                    echo "<td><textarea rows='2' cols='30' name='tags'>".list_tags($id, 4, false)."</textarea></td></tr>";
                }
                echo "<tr><th>{$strPriority}</th>";
                echo "<td>".priority_drop_down('priority',$task->priority)."</td></tr>";
                echo "<tr><th>{$strStartDate}</th>";
                echo "<td><input type='text' name='startdate' id='startdate' size='10' value='";
                if ($startdate > 0) echo date('Y-m-d',$startdate);
                echo "' /> ";
                echo date_picker('edittask.startdate');
                echo " ".time_dropdown("starttime", date('H:i',$startdate));
                echo "</td></tr>";
                echo "<tr><th>{$strDueDate}</th>";
                echo "<td><input type='text' name='duedate' id='duedate' size='10' value='";
                if ($duedate > 0) echo date('Y-m-d',$duedate);
                echo "' /> ";
                echo date_picker('edittask.duedate');
                echo " ".time_dropdown("duetime", date('H:i',$duedate));
                echo "</td></tr>";
                echo "<tr><th>{$strCompletion}</th>";
                echo "<td><input type='text' name='completion' size='3' maxlength='3' value='{$task->completion}' />&#037;</td></tr>";
                echo "<tr><th>{$strEndDate}</th>";
                echo "<td><input type='text' name='enddate' id='enddate' size='10' value='";
                if ($enddate > 0) echo date('Y-m-d',$enddate);
                echo "' /> ";
                echo date_picker('edittask.enddate');
                echo " ".time_dropdown("endtime", date('H:i',$enddate));
                echo "</td></tr>";
                echo "<tr><th>{$strValue}</th>";
                echo "<td><input type='text' name='value' size='6' maxlength='12' value='{$task->value}' /></td></tr>";
                echo "<tr><th>{$strPrivacy}</th>";
                echo "<td>";
                echo "<input type='radio' name='distribution' ";
                if ($task->distribution=='public') echo "checked='checked' ";
                echo "value='public' /> {$strPublic}<br />";
                echo "<input type='radio' name='distribution' ";
                if ($task->distribution=='private') echo "checked='checked' ";
                echo "value='private' /> {$strPrivate} <img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/16x16/private.png' width='16' height='16' title='Private' alt='Private' /></td></tr>";
                echo "</table>";
                echo "<p><input name='submit' type='submit' value='{$strSave}' /></p>";
                echo "<input type='hidden' name='action' value='edittask' />";
                echo "<input type='hidden' name='id' value='{$id}' />";
                // Send copy of existing data so we can see when it is changed
                echo "<input type='hidden' name='old_name' value=\"{$task->name}\" />";
                echo "<input type='hidden' name='old_description' value=\"{$task->description}\" />";
                echo "<input type='hidden' name='old_priority' value=\"{$task->priority}\" />";
                echo "<input type='hidden' name='old_startdate' value='{$task->startdate}' />";
                echo "<input type='hidden' name='old_duedate' value='{$task->duedate}' />";
                echo "<input type='hidden' name='old_completion' value='{$task->completion}' />";
                echo "<input type='hidden' name='old_enddate' value='{$task->enddate}' />";
                echo "<input type='hidden' name='old_value' value='{$task->value}' />";
                echo "<input type='hidden' name='old_distribution' value='{$task->distribution}' />";
                echo "</form>";
            }
        }
        else echo "<p class='error'>No matching task found</p>";


        echo "<p align='center'><a href='tasks.php'>{$strTaskList}</a></p>";
        include ('htmlfooter.inc.php');
}

?>