<?php
// add_feedback_question.php - Form for adding feedback questions
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// by Ivan Lucas, June 2004

// FIXME i18n Whole Page

@include('set_include_path.inc.php');
$permission=48; // Add Feedback Forms

require('db_connect.inc.php');
require('functions.inc.php');
// This page requires authentication
require('auth.inc.php');

function qtype_listbox($type)
{
    global $CONFIG;

    $html .= "<select name='type'>\n";
    $html .= "<option value='rating'";
    if ($type=='rating') $html .= " selected";
    $html .= ">Rating, accepts score between 1 and {$CONFIG['feedback_max_score']}, label low and high (one per line) below</option>";

    $html .= "<option value='options'";
    if ($type=='options') $html .= " selected";
    $html .= ">Options, accepts one of the values listed below (one per line)</option>";

    $html .= "<option value='multioptions'";
    if ($type=='multioptions') $html .= " selected";
    $html .= ">MultiOptions, accepts multiple values as isted below (one per line)</option>";

    $html .= "<option value='text'";
    if ($type=='text') $html .= " selected";
    $html .= ">Text, accepts free-text, enter columns and rows (one per line) to accept below</option>";

    $html .= "</select>\n";

    return $html;
}

if ($_SESSION['tcs']!=$tcs) { header("Location: login.php"); exit; }

// External variables
$qid = cleanvar($_REQUEST['id']);
$fid = cleanvar($_REQUEST['fid']);
$formid = cleanvar($_POST['formid']);
$question = cleanvar($_POST['question']);
$questiontext = cleanvar($_POST['questiontext']);
$sectiontext = cleanvar($_POST['sectiontext']);
$taborder = cleanvar($_POST['taborder']);
$type = cleanvar($_POST['type']);
$required = cleanvar($_POST['required']);
$options = cleanvar($_POST['options']);

switch ($_REQUEST['action'])
{
    case 'save':
        $sql = "INSERT INTO `{$dbFeedbackQuestions}` ";
        $sql .= "(formid, question, questiontext, sectiontext, taborder, type, required, options) VALUES (";
        $sql .= "'{$formid}',";
        $sql .= "'{$question}',";
        $sql .= "'{$questiontext}',";
        $sql .= "'{$sectiontext}',";
        $sql .= "'{$taborder}',";
        $sql .= "'{$type}',";
        $sql .= "'{$required}',";
        $sql .= "'{$options}')";
        mysql_query($sql);
        if (mysql_error()) trigger_error ("MySQL Error: ".mysql_error(), E_USER_ERROR);
        $newqid=$qid+1;
        header("Location: add_feedback_question.php?fid={$formid}&qid={$newqid}");
        exit;
    break;

    default:
        $title = "Add Feedback Question";
        include('htmlheader.inc.php');
        $qid = strip_tags($_REQUEST['qid']);

        echo "<h2 align='center'>$title</h2>\n";

        echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
        echo "<table summary='Form' align='center'>";
        echo "<tr>";

        echo "<th>Section Text:<br />(When this question starts a new section,<br />enter information here to appear above this question,<br />leave blank for no new section)</th>";
        echo "<td><textarea name='sectiontext' cols='80' rows='5'>";
        echo $question->sectiontext."</textarea></td>";
        echo "</tr>\n<tr>";

        echo "<th>Q#:</th>";
        echo "<td><input type='text' name='taborder' size='3' maxlength='5' value='{$qid}' /></td>";
        echo "</tr>\n<tr>";

        echo "<th>Question:</th>";
        echo "<td><input type='text' name='question' size='35' maxlength='255' value='".$question->question."' /></td>";
        echo "</tr>\n<tr>";

        echo "<th>Question Text:<br />(Information and Instructions)</th>";
        echo "<td><textarea name='questiontext' cols='80' rows='5'>";
        echo $question->questiontext."</textarea></td>";
        echo "</tr>\n<tr>";

        echo "<th>Type:</th>";
        echo "<td>";
        echo qtype_listbox($question->type);
        echo "</td></tr>\n<tr>";

        echo "<th>Options:<br />(For this question-type)<br /><br />(One per line)</th>";
        echo "<td><textarea name='options' cols='80' rows='10'>";
        echo $question->options."</textarea></td>";
        echo "</tr>\n<tr>";

        echo "<th>Required:</th>";
        echo "<td>";
        if ($question->required=='true') echo "<input type='checkbox' name='required' value='true' checked='checked' />";
        else echo "<input type='checkbox' name='required' value='true' />";
        echo "</td></tr>\n<tr>";

        echo "<td><input type='hidden' name='id' value='{$qid}' />";
        echo "<input type='hidden' name='formid' value='{$fid}' />";
        echo "<input type='hidden' name='action' value='save' /></td>";
        echo "<td><input type='submit' value='{$strSave}' /></td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
        include('htmlfooter.inc.php');
}
?>