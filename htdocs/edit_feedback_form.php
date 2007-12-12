<?php
// edit_feedback_form.php - Form for editing feedback forms
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// by Ivan Lucas, June 2004
@include('set_include_path.inc.php');
$permission=49; // Edit Feedback Forms

require('db_connect.inc.php');
require('functions.inc.php');
// This page requires authentication
require('auth.inc.php');

// External Variables
$formid = cleanvar($_REQUEST['formid']);

if (empty($formid)) $formid=1;

switch ($_REQUEST['action'])
{
    case 'save':
        // External variables
        $name = cleanvar($_REQUEST['name']);
        $description = cleanvar($_REQUEST['description']);
        $introduction = cleanvar($_REQUEST['introduction']);
        $thanks = cleanvar($_REQUEST['thanks']);
        $isnew = cleanvar($_REQUEST['isnew']);

        if($isnew == "yes")
        {
            // need to insert
            $sql = "INSERT INTO feedbackforms (name,introduction,thanks,description) VALUES ";
            $sql .= "('{$name}','{$introduction}','{$thanks}','{$description}')";
            mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            $formid = mysql_insert_id();
        }
        else
        {
            $sql = "UPDATE feedbackforms ";
            $sql .= "SET name='$name', description='$description', introduction='$introduction', thanks='$thanks' ";
            $sql .= "WHERE id='$formid' LIMIT 1";
            mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        }

        header("Location: edit_feedback_form.php?formid={$formid}");
        exit;
    break;

    case 'new':
        include('htmlheader.inc.php');
        echo "<h3>Create feedback form</h3>";
        echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
        echo "<table summary='Form' align='center'>";
        echo "<tr>";

        /*echo "<th>Form ID:</th>";
        echo "<td><strong>{$form->id}</strong></td>";
        echo "</tr>\n<tr>";*/

        echo "<th>{$strName}:</th>";
        echo "<td><input type='text' name='name' size='35' maxlength='255' value='' /></td>";
        echo "</tr>\n<tr>";

        echo "<th>{$strDescription}:<br />(For Internal Use, not displayed)</th>";
        echo "<td><textarea name='description' cols='80' rows='6'>";
        echo "</textarea></td>";
        echo "</tr>\n<tr>";

        echo "<th>Introduction:<br />(Simple HTML Allowed)</th>";
        echo "<td><textarea name='introduction' cols='80' rows='10'>";
        echo "</textarea></td>";
        echo "</tr>\n<tr>";

        echo "<th>Closing Thanks:<br />(Simple HTML Allowed)</th>";
        echo "<td><textarea name='thanks' cols='80' rows='10'>";
        echo "</textarea></td>";
        echo "</tr>\n";

        // If there are no reponses to this feedback form, allow questions to be modified also
        echo "<tr>";
        echo "<th>Questions:</th>";
        echo "<td>";
        //echo "<p><a href='add_feedback_question.php?fid=$formid'>Add Question</a><br />Save the main form first</p>";
        echo "<p>Save the main form first</p>";
        echo "</td></tr>\n";
        echo "<tr>";
        echo "<td><input type='hidden' name='formid' value='{$formid}' />";
        echo "<input type='hidden' name='isnew' value='yes' />";
        echo "<input type='hidden' name='action' value='save' /></td>";
        echo "<td><input type='submit' value='{$strSave}' /></td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
        include('htmlfooter.inc.php');
        break;
    default:
        $sql = "SELECT * FROM feedbackforms WHERE id='{$formid}'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

        include('htmlheader.inc.php');
        echo "<h3>{$title}</h3>";

        $sql = "SELECT * FROM feedbackforms WHERE id = '$formid'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error ("MySQL Error: ".mysql_error(), E_USER_ERROR);
        if (mysql_num_rows($result) >= 1)
        {
            while ($form = mysql_fetch_object($result))
            {
                echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
                echo "<table summary='Form' align='center'>";
                echo "<tr>";

                echo "<th>Form ID:</th>";
                echo "<td><strong>{$form->id}</strong></td>";
                echo "</tr>\n<tr>";

                echo "<th>{$strName}:</th>";
                echo "<td><input type='text' name='name' size='35' maxlength='255' value=\"{$form->name}\" /></td>";
                echo "</tr>\n<tr>";

                echo "<th>{$strDescription}:<br />(For Staff Use, not displayed)</th>";
                echo "<td><textarea name='description' cols='80' rows='6'>";
                echo $form->description."</textarea></td>";
                echo "</tr>\n<tr>";

                echo "<th>Introduction:<br />(Simple HTML Allowed)</th>";
                echo "<td><textarea name='introduction' cols='80' rows='10'>";
                echo $form->introduction."</textarea></td>";
                echo "</tr>\n<tr>";

                echo "<th>Closing Thanks:<br />(Simple HTML Allowed)</th>";
                echo "<td><textarea name='thanks' cols='80' rows='10'>";
                echo $form->thanks."</textarea></td>";
                echo "</tr>\n";w

                // If there are no reponses to this feedback form, allow questions to be modified also
                echo "<tr>";
                echo "<th>Questions:</th>";
                echo "<td>";

                // echo "<tr><th>Q</th><th>Question</th><th>Text</th></tr>\n<tr><th>Type</th><th>Reqd</th><th>Options</th></tr>\n";
                $qsql  = "SELECT * FROM feedbackquestions ";
                $qsql .= "WHERE formid='$formid' ORDER BY taborder";
                $qresult = mysql_query($qsql);
                if (mysql_num_rows($qresult) > 0)
                {
                    echo "<table width='100%'>";
                    while ($question = mysql_fetch_object($qresult))
                    {
                        echo "<tr>";
                        echo "<td><strong>Q{$question->taborder}</strong></td>";
                        echo "<td><a href='edit_feedback_question.php?qid={$question->id}&amp;fid={$formid}'><strong>{$question->question}</strong></a></td>";
                        echo "<td>{$question->questiontext}</td>";
                        echo "</tr>\n<tr>";
                        echo "<td>{$question->type}</td>";
                        echo "<td colspan='2'>";
                        if ($question->required=='true') echo "<strong>Required</strong> ";
                        echo "<samp>{$question->options}</samp></td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                echo "<p><a href='add_feedback_question.php?fid=$formid'>Add Question</a><br />Save the main form first</p>";
                echo "</td></tr>\n";
                echo "<tr>";
                echo "<td><input type='hidden' name='formid' value='{$formid}' />";
                echo "<input type='hidden' name='action' value='save' /></td>";
                echo "<td><input type='submit' value='{$strSave}' /></td>";
                echo "</tr>";
                echo "</table>";
                echo "</form>";
            }
        }
        else echo "<p class='error'>No feedback form found</p>";
        include('htmlfooter.inc.php');
    break;
}
?>
