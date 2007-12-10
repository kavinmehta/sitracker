<?php
// feedback.php - Display a form for customers to provide feedback
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>, June 2004

// FIXME i18n

@include('set_include_path.inc.php');
require('db_connect.inc.php');
require('functions.inc.php');

// External variables
$hashcode=$_REQUEST['ax'];
$mode=$_REQUEST['mode'];
$decodehash=str_rot13(gzuncompress(base64_decode(urldecode($hashcode))));

$hashvars=explode('&&',$decodehash);
$formid=mysql_real_escape_string($hashvars['0']);
$contactid=mysql_real_escape_string($hashvars['1']);
$incidentid=urldecode(mysql_real_escape_string($hashvars['2']));
unset($errorfields);

/**
    * @author Ivan Lucas
*/
function feedback_html_rating($name, $required, $options, $answer='')
{
    global $CONFIG;
    // Rate things out of 'score_max' number
    $score_max=$CONFIG['feedback_max_score'];

    $option_list=explode('{@}', $options);
    $promptleft=$option_list[0];
    $promptright=$option_list[1];

    $colwidth=round(100/$score_max);

    $html .= "<table class='feedback'>\n";
    if (empty($promptleft)==FALSE OR empty($promptright)==FALSE)
    {
        $html .= "<tr>";
        /*  for($c=1;$c<=$score_max;$c++)
        {
        if ($c==1) $html.="<th width='$colwidth%'>$promptleft</th>";
        elseif ($c==$score_max) $html.="<th width='$colwidth%'>$promptright</th>";
        else $html.="<th width='$colwidth%'>&nbsp;</th>";
        }
        */
        $html.="<th colspan='$score_max' style='text-align: left;'><div style='float: right;'>$promptright</div><div>$promptleft</div></th>";
        if ($required!='true') $html .= "<th>&nbsp;</th>";
        $html .= "</tr>\n";
    }
    echo "<tr>";
    for($c=1;$c<=$score_max;$c++)
    {
        $html.="<td width='$colwidth%' style='text-align: center;'><input type='radio' name='$name' value='$c' ";
        if ($answer==$c) $html .= "checked='checked'";
        $html .= " />$c</td>\n";
    }
    if ($required!='true')
    {
        $html .= "<td><input type='radio' name='$name' value='0' ";
        if ($answer==0) $html .= "checked='checked'";
        $html .= "/>N/A</td>";
    }
    $html .= "</tr>\n";
    $html .= "</table>\n";

    return $html;
}


/**
    * @author Ivan Lucas
*/
function feedback_html_options($name, $required, $options, $answer='')
{
    $option_list=explode('{@}', $options);
    $option_count=count($option_list);
    if ($option_count > 3)
    {
        $html .= "<select name='$name'>\n";
        foreach($option_list AS $key=>$option)
        {
            $value=strtolower(trim(str_replace(' ', '_', $option)));
            $html .= "<option value='$value'";
            if ($answer==$value) $html .= " selected='selected'";
            $html .= ">".trim($option)."</option>\n";
        }
        $html .= "</select>\n";
    }
    else
    {
        foreach($option_list AS $key=>$option)
        {
            $value=strtolower(trim(str_replace(' ', '_', $option)));
            $html .= "<input type='radio' name='$name' value='$value'";
            if ($answer==$value) $html .= " selected='selected'";
            $html .= " />".trim($option)." &nbsp; \n";
        }
    }
    return $html;
}


/**
    * @author Ivan Lucas
*/
function html_multioptions($name, $required, $options)
{
    $option_list=explode('{@}', $options);
    $option_count=count($option_list);
    if ($option_count > 3)
    {
        $html .= "<select name='{$name}[]' multiple='multiple'>\n";
        foreach($option_list AS $key=>$option)
        {
            $value=strtolower(trim(str_replace(' ', '_', $option)));
            $html .= "<option value='$value'>".trim($option)."</option>\n";
        }
        $html .= "</select>\n";
    }
    else
    {
        foreach($option_list AS $key=>$option)
        {
            $value=strtolower(trim(str_replace(' ', '_', $option)));
            $html .= "<input type='checkbox' name='$name' value='$value' />".trim($option)." &nbsp; \n";
        }
    }
    return $html;
}


/**
    * @author Ivan Lucas
*/
function feedback_html_text($name, $required, $options, $answer='')
{
    $option_list=explode('{@}', $options);
    $cols=$option_list[0] ? $option_list[0] : 60;
    $rows=$option_list[1] ? $option_list[1] : 5;

    if ($rows==1) $html .= "<input type='text' name='$name' size='$cols' value='$answer' />\n";
    else  $html .= "<textarea name ='$name' rows='$rows' cols='$cols' />{$answer}</textarea>\n";

    return $html;
}


/**
    * @author Ivan Lucas
*/
function feedback_html_question($type, $name, $required, $options, $answer='')
{
    $options=nl2br(trim($options));
    $options=str_replace('<br>', '{@}', $options);
    $options=str_replace('<br />', '{@}', $options);
    $options=str_replace('<br/>', '{@}', $options);
    switch($type)
    {
        case 'rating':
            $html = feedback_html_rating($name, $required, $options, $answer);
        break;

        case 'options':
            $html = feedback_html_options($name, $required, $options, $answer);
        break;

        case 'multioptions':
            $html = feedback_html_multioptions($name, $required, $options, $answer);
        break;

        case 'text':
            $html = feedback_html_text($name, $required, $options, $answer);
        break;

        default:
            $html = "Error: Unable to accept a response for this question, no handler for question of type '$type'.";
        break;
  }
  return $html;
}



switch ($_REQUEST['action'])
{
    case 'save':
        // FIXME external vars
        // Have a look to see if this respondant has already responded to this form
        // Get respondentid
        //print_r($_REQUEST);
        $sql = "SELECT id AS respondentid FROM feedbackrespondents WHERE contactid='$contactid' AND formid='$formid' AND incidentid='$incidentid'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        if (mysql_num_rows($result) < 1)
        {
            // FIXME: Proper error here
            echo "<p>Error, could not locate empty form to store results.</p>";
        }
        else
        {
            list($respondentid)=mysql_fetch_row($result);
        }
        // Store this respondent and references

        // Loop through the questions in this form and store the results
        $sql = "SELECT * FROM feedbackquestions WHERE formid='{$formid}'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        while ($question = mysql_fetch_object($result))
        {
            $qid = $question->id;

            $options=nl2br(trim($question->options));
            $options=str_replace('<br>', '{@}', $options);
            $options=str_replace('<br />', '{@}', $options);
            $options=str_replace('<br/>', '{@}', $options);
            $option_list=explode('{@}', $options);

            $fieldname="Q{$question->id}";

            // Check required fields are filled
            if ($question->required=='true' AND (strlen($_POST[$fieldname])<1 OR isset($_POST[$fieldname])==false)) $errorfields[]="{$question->id}";

            // Store text responses in the appropriate field
            if ($question->type=='text')
            {
                if (strlen($_POST[$fieldname]) < 255 AND $option_list[1]<2)
                {
                    // If we've got just one row and less than 255 characters store it in the result field
                    $qresult = $_POST[$fieldname];
                    $qresulttext = '';
                }
                else
                {
                    // If we've got more than one row or more than 255 chars store it in the resulttext field (which is a blob)
                    $qresult = '';
                    $qresulttext = $_POST[$fieldname];
                }
            }
            /*
            elseif ($question->type='multioptions')
            {
                $qresult = '';
                $qresulttext=implode(',',$_POST[$fieldname]);
            }
            */
            else
            {
                // Store all other types of results in the result field.
                $qresult = $_POST[$fieldname];
                $qresulttext = $_POST[$fieldname];
            }

            $debugtext .= "_POST[$fieldname]={$_POST[$fieldname]}\n";

            // Put the SQL to be executed into an array to execute later
            $rsql[] = "INSERT INTO feedbackresults (respondentid, questionid, result, resulttext) VALUES ('$respondentid', '$qid','$qresult', '$qresulttext')";
            // Store the field in an array
            $fieldarray[$question->id]=$_POST[$fieldname];
        }

        if (count($errorfields) >= 1)
        {
            $error=implode(",",$errorfields);
            $fielddata=base64_encode(serialize($fieldarray));
            //echo "<p>Error: $errortext</p>";
            //print_r($errorfields);
            //exit;
            $errortext=urlencode($fielddata.','.$error);
            echo "<?";
            echo "xml version=\"1.0\" encoding=\"\"?";
            echo ">";
    // FIXME check this code
            ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<meta http-equiv="refresh" content="0;URL=feedback.php?ax=<?php echo "{$hashcode}&error={$errortext}&mode={$mode}"; ?>" />
<title>Please wait</title>
<style>
body { font:10pt Arial, Helvetica, sans-serif; }
</style>
</head>
<body>
<p>Please wait while we redirect you...</p>
<p>If your browser does not reload the page within a few seconds <a href='feedback.php?ax=<?php echo "{$hashcode}&error={$errortext}&mode={$mode}"; ?>'>follow this link</a>.</p>
</body>
</head>
<?php
//             header("Location: feedback.php?ax={$hashcode}&error={$errortext}");
            exit;
        }

        if (empty($_REQUEST['rr'])) $rsql[] = "UPDATE feedbackrespondents SET completed='yes' WHERE formid='{$formid}' AND contactid='$contactid' AND incidentid='$incidentid'";

        // Loop through array and execute the array to insert the form data
        foreach ($rsql AS $sql)
        {
            ## echo $sql."<br />";
            mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            $sqltext.=$sql."\n";
        }

        //    $sql = "UPDATE feedbackrespondents ";
        //$sql .= "SET completed='yes' ";
        //$sql .= "WHERE formid='$formid' AND respondent='$respondent' AND responseref='$responseref' ";
        //mysql_query($sql);
        //if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
        //if (mysql_affected_rows() < 1) echo "<p>No rows affected: ($sql)</p>";

        // Output thanks page
        echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
        echo "<html>\n";
        echo "<head>\n";
        echo "<title>Thank you</title>\n";
        echo "</head>\n";
        echo "<body>\n";
        echo "<div id='pagecontent'><h1>Thank you</h1>";
        echo "<p>Thank you for taking the time to complete this form.</p>";
        //echo "<!-- \n {$sqltext} \n\n\n {$debugtext} -->";
        echo "</div>\n</body>\n";
        echo "</html>\n";
    break;

    default:
        if ($_REQUEST['mode']!='bare') include('htmlheader.inc.php');
        else echo "<html>\n<head>\n<title>Feedback Form</title>\n</head>\n<body>\n<div id='pagecontent'>\n\n";
        $errorfields = explode(",",urldecode($_REQUEST['error']));
        $fielddata=unserialize(base64_decode($errorfields[0])); // unserialize(

        // Have a look to see if this person has a form waiting to be filled
        $rsql = "SELECT id FROM feedbackrespondents WHERE contactid='$contactid' AND incidentid='$incidentid' AND formid='$formid' ";
        if ($_REQUEST['rr']) $rsql .= "AND completed='yes' ";

        $rresult = mysql_query($rsql);
        if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);

        $waitingforms = mysql_num_rows($rresult);
        $waitingform = mysql_fetch_object($rresult);

        if ($waitingforms<1)
        {
            echo "<h2>Error</h2>";
            echo "<p>There is no feedback form waiting to be completed at this address, this could be because you have ";
            echo "already provided feedback.  Please check that the URL you entered is correct.</p>";
            echo "\n\n<!-- f: $formid r:$respondent rr:$responseref dh:$decodehash  hc:$hashcode -->\n\n";
        }
        else
        {
            $sql = "SELECT * FROM feedbackforms WHERE id='{$formid}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);

            $reqd=0;
            while ($form = mysql_fetch_object($result))
            {
                echo "<form action='feedback.php' method='post'>\n";
                echo "<h1>{$form->name}</h1>\n";
                echo "<p>Relating to incident <strong>#{$incidentid}</strong> &mdash; <strong>".incident_title($incidentid)."</strong><br />";
                echo "Opened by <strong>".contact_realname(incident_contact($incidentid))."</strong> on ".date($CONFIG['dateformat_date'],db_read_column('opened', 'incidents', $incidentid))." and closed on ".date($CONFIG['dateformat_date'],db_read_column('closed', 'incidents', $incidentid)).".</p>";

                if (!empty($_REQUEST['error'])) echo "<p style='color: red'>Error, you did not complete all required questions, please check your answers and try again.</p>";
                echo nl2br($form->introduction);

                $qsql  = "SELECT * FROM feedbackquestions ";
                $qsql .= "WHERE feedbackquestions.formid='{$form->id}' ";
                $qsql .= "ORDER BY taborder ASC";
                $qresult=mysql_query($qsql);
                if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
                while ($question = mysql_fetch_object($qresult))
                {
                    if (strlen(trim($question->sectiontext))>3) echo "<hr />{$question->sectiontext}\n";
                    echo "<h4>Q{$question->taborder}: {$question->question}";
                    if ($question->required=='true')
                    {
                        echo "<sup style='color: red; font-size: 120%;'>*</sup>";
                        $reqd++;
                    }
                    echo "</h4>";

                    if (!empty($question->questiontext)) echo "<p>{$question->questiontext}</p>";
                    if (!empty($fielddata[$question->id])) $answer=$fielddata[$question->id];
                    else $answer='';
                    echo feedback_html_question($question->type, "Q{$question->id}", $question->required, $question->options, $answer);
                    if (in_array($question->id,$errorfields)) echo "<p style='color: red'>Question {$question->taborder} requires an answer before continuing.</p>";
                    echo "<br />";
                }

                echo nl2br($form->thanks);

                echo "<br /><input type='hidden' name='action' value='save' />\n";
                echo "<input type='hidden' name='ax' value='".strip_tags($_REQUEST['ax'])."' />\n";
                echo "<input type='submit' value='Submit' />\n";
                echo "</form>\n";
                if ($reqd>=1) echo "<p><sup style='color: red; font-size: 120%;'>*</sup> Questions marked with this symbol are required and must be answered before continuing.</p>";
            }
        }
        if ($_REQUEST['mode']!='bare') include('htmlfooter.inc.php');
        else echo "\n</div>\n</body>\n</html>\n";
    break;
}

?>