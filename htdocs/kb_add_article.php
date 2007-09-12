<?php
// kb_add_article.php - Form to add a knowledgebase article
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// This Page Is Valid XHTML 1.0 Transitional! 22Feb06

// Authors: Ivan Lucas <ivanlucas[at]users.sourceforge.net>, Tom Gerrard
$permission=54; // view KB

require('db_connect.inc.php');
require('functions.inc.php');
// This page requires authentication
require('auth.inc.php');

// Valid user, check permission
if (user_permission($sit[2],$permission))
{
    // External variables
    $process = cleanvar($_POST['process']);


    if (empty($process))
    {
        include('htmlheader.inc.php');
        ?>
        <script type="text/javascript">
        <!--
        function editbox(object, boxname)
        {
            var boxname;
            object.boxname.disabled=true;
        }
        -->
        </script>
        <?php
        echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/kb.png' width='32' height='32' alt='' /> ";
        echo "Add KB Article</h2>";
        ?>
        <p align='center'>Mandatory fields are marked <sup class='red'>*</sup></p>
        <form name="articleform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <table align='center' class='vertical' width='600'>
        <tr><th>Title: <sup class='red'>*</sup></th><td><input type="text" name="title" size="50" maxlength="255" /></td></tr>
        <tr><th>Keywords: <sup class='red'>*</sup></th><td><input type="text" name="keywords" size="50" maxlength="255" /></td></tr>
        <tr><th>Distribution: <sup class='red'>*</sup></th><td>
        <select name="distribution">
        <option value="public">Public</option>
        <option value="private" style='color: blue;'>Private</option>
        <option value="restricted" style='color: red;'>Restricted</option>
        </select>
        </td></tr>

        <tr><th>&nbsp;</th><td>Select the sections you'd like to include in the article by checking the boxes beside each heading, you can add further sections later.  You don't need to include all sections, just use the ones that are relevant.</td></tr>

        <tr><th>Summary: <input type='checkbox' name='incsummary' onclick="if (this.checked) {document.articleform.summary.disabled = false; document.articleform.summary.style.display='';} else { saveValue=document.articleform.summary.value; document.articleform.summary.disabled = true; document.articleform.summary.style.display='none';}" /></th>
        <td><textarea id="summary" name="summary" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.summary.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Symptoms: <input type='checkbox' name='incsymptoms' onclick="if (this.checked) {document.articleform.symptoms.disabled = false; document.articleform.symptoms.style.display=''} else { saveValue=document.articleform.symptoms.value; document.articleform.symptoms.disabled = true; document.articleform.symptoms.style.display='none'}" /></th>
        <td><textarea id="symptoms" name="symptoms" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.symptoms.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Cause: <input type='checkbox' name='inccause' onclick="if (this.checked) {document.articleform.cause.disabled = false; document.articleform.cause.style.display=''} else { saveValue=document.articleform.cause.value; document.articleform.cause.disabled = true; document.articleform.cause.style.display='none'}" /></th>
        <td><textarea id="cause" name="cause" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.cause.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Question: <input type='checkbox' name='incquestion' onclick="if (this.checked) {document.articleform.question.disabled = false; document.articleform.question.style.display=''} else { saveValue=document.articleform.question.value; document.articleform.question.disabled = true; document.articleform.question.style.display='none'}" /></th>
        <td><textarea id="question" name="question" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.question.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Answer: <input type='checkbox' name='incanswer' onclick="if (this.checked) {document.articleform.answer.disabled = false; document.articleform.answer.style.display=''} else { saveValue=document.articleform.answer.value; document.articleform.answer.disabled = true; document.articleform.answer.style.display='none'}" /></th>
        <td><textarea id="answer" name="answer" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.answer.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Solution: <input type='checkbox' name='incsolution' onclick="if (this.checked) {document.articleform.solution.disabled = false; document.articleform.solution.style.display=''} else { saveValue=document.articleform.solution.value; document.articleform.solution.disabled = true; document.articleform.solution.style.display='none'}" /></th>
        <td><textarea id="solution" name="solution" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.solution.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Workaround: <input type='checkbox' name='incworkaround' onclick="if (this.checked) {document.articleform.workaround.disabled = false; document.articleform.workaround.style.display=''} else { saveValue=document.articleform.workaround.value; document.articleform.workaround.disabled = true; document.articleform.workaround.style.display='none'}" /></th>
        <td><textarea id="workaround" name="workaround" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.workaround.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Status: <input type='checkbox' name='incstatus' onclick="if (this.checked) {document.articleform.status.disabled = false; document.articleform.status.style.display=''} else { saveValue=document.articleform.status.value; document.articleform.status.disabled = true; document.articleform.status.style.display='none'}" /></th>
        <td><textarea id="status" name="status" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.status.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>Additional Info: <input type='checkbox' name='incadditional' onclick="if (this.checked) {document.articleform.additional.disabled = false; document.articleform.additional.style.display=''} else { saveValue=document.articleform.additional.value; document.articleform.additional.disabled = true; document.articleform.additional.style.display='none'}" /></th>
        <td><textarea id="additional" name="additional" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.additional.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        <tr><th>References: <input type='checkbox' name='increferences' onclick="if (this.checked) {document.articleform.references.disabled = false; document.articleform.references.style.display=''} else { saveValue=document.articleform.references.value; document.articleform.references.disabled = true; document.articleform.references.style.display='none'}" /></th>
        <td><textarea id="references" name="references" cols='100' rows='8' style="display: none;" onfocus="if (this.enabled) { this.value = saveValue; setTimeout('document.articlform.references.blur()',1); } else saveValue=this.value;"></textarea></td></tr>

        </table>
        <p align='center'>
        <input type="hidden" name="process" value="true" />
        <input type="submit" value="Add Article" />
        </p>
        </form>
        <?php
        include('htmlfooter.inc.php');
    }
    else
    {
        $title = cleanvar($_POST['title']);
        $distribution = cleanvar($_POST['distribution']);
        $keywords = cleanvar($_POST['keywords']);
        $summary = cleanvar($_POST['summary'],FALSE,FALSE);
        $symptoms = cleanvar($_POST['symptoms'],FALSE,FALSE);
        $cause = cleanvar($_POST['cause'],FALSE,FALSE);
        $question = cleanvar($_POST['question'],FALSE,FALSE);
        $answer = cleanvar($_POST['answer'],FALSE,FALSE);
        $solution = cleanvar($_POST['solution'],FALSE,FALSE);
        $workaround = cleanvar($_POST['workaround'],FALSE,FALSE);
        $status = cleanvar($_POST['status'],FALSE,FALSE);
        $additional = cleanvar($_POST['additional'],FALSE,FALSE);
        $references = cleanvar($_POST['references'],FALSE,FALSE);

        $sql = "INSERT INTO kbarticles (doctype, title, distribution, author, published, keywords) VALUES ";
        $sql .= "('1', ";
        $sql .= "'{$title}', ";
        $sql .= "'{$distribution}', ";
        $sql .= "'{$sit[2]}', ";
        $sql .= "'".date('Y-m-d H:i:s', mktime(date('H'),date('i'),date('s'),date('m'),date('d'),date('Y')))."', ";
        $sql .= "'{$keywords}') ";
        mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $docid = mysql_insert_id();

        // Force private if not specified
        if (empty($_POST['distribution'])) $_POST['distribution']='private';

        if (!empty($summary)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Summary', '1', '{$summary}', '{$distribution}') ";
        if (!empty($symptoms)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Symptoms', '1', '{$symptoms}', '{$distribution}') ";
        if (!empty($cause)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Cause', '1', '{$cause}', '{$distribution}') ";
        if (!empty($question)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Question', '1', '{$question}', '{$distribution}') ";
        if (!empty($answer)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Answer', '1', '{$answer}', '{$distribution}') ";
        if (!empty($solution)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Solution', '1', '{$solution}', '{$distribution}') ";
        if (!empty($workaround)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Workaround', '1', '{$workaround}', '{$distribution}') ";
        if (!empty($status)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Status', '1', '{$status}', '{$distribution}') ";
        if (!empty($additional)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Additional Information', '1', '{$additional}', '{$distribution}') ";
        if (!empty($references)) $query[]="INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'References', '1', '{$references}', '{$distribution}') ";

        if (count($query) < 1) $query[] = "INSERT INTO kbcontent (docid, ownerid, headerstyle, header, contenttype, content, distribution) VALUES ('$docid', '".mysql_escape_string($sit[2])."', 'h1', 'Summary', '1', 'Enter details here...', 'restricted') ";

        foreach ($query AS $sql)
        {
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        }

        $id = mysql_insert_id();
        journal(CFG_LOGGING_NORMAL, 'KB Article Added', "KB Article $id was added", CFG_JOURNAL_KB, $id);

        header("Location: kb_view_article.php?id=$docid");
        exit;
    }
}
?>
