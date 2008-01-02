<?php
// feedback3.php - Feedback scores by contact
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
// Hacked: Tom Gerrard <tom.gerrard[at]salfordsoftware.co.uk>

@include('../set_include_path.inc.php');
$permission=37; // Run Reports

require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

include('htmlheader.inc.php');

$maxscore = $CONFIG['feedback_max_score'];
$formid=$CONFIG['feedback_form'];
$now = time();

echo "<script type='text/javascript'>";
echo "
function incident_details_window(incidentid,win)
{
    URL = '../incident_details.php?id=' + incidentid;
    window.open(URL, 'sit_popup', 'toolbar=yes,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=700,height=600');
}
";
echo "</script>";
echo "<div style='margin: 20px'>";
echo "<h2><a href='{$CONFIG['application_webpath']}reports/feedback.php'>Feedback</a> Scores: By Contact</h2>";
echo "<p>This report shows customer responses and a percentage figure indicating the overall positivity of customers toward ";
echo "incidents logged by the user(s) shown:</p>";

$qsql = "SELECT * FROM feedbackquestions WHERE formid='{$formid}' AND type='rating' ORDER BY taborder";
$qresult = mysql_query($qsql);
if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
while ($qrow = mysql_fetch_object($qresult))
{
    $q[$qrow->taborder]=$qrow;
}


$msql = "SELECT *, closingstatus.name AS closingstatusname, sites.name AS sitename, sites.id as siteid, (incidents.closed - incidents.opened) AS duration, \n";
$msql .= "feedbackrespondents.id AS reportid, contacts.id AS contactid ";
$msql .= "FROM feedbackrespondents, incidents, contacts, sites, closingstatus WHERE feedbackrespondents.incidentid=incidents.id \n";
$msql .= "AND incidents.contact=contacts.id ";
$msql .= "AND contacts.siteid=sites.id ";
$msql .= "AND incidents.closingstatus=closingstatus.id ";
$msql .= "AND feedbackrespondents.incidentid > 0 \n";
$msql .= "AND feedbackrespondents.completed = 'yes' \n";
if (!empty($id)) $msql .= "AND incidents.contact='$id' \n";
else $msql .= "ORDER BY contacts.surname ASC, contacts.forenames ASC, incidents.contact ASC , incidents.id ASC \n";
$mresult = mysql_query($msql);
if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);

if (mysql_num_rows($mresult) >= 1)
{
    $prevcontactid=0;
    $countcontacts=0;
    for ($i = 0; $i <= 10; $i++)
      $counter[$i] = 0;

    $surveys=0;
    if ($CONFIG['debug']) echo "<h4>$msql</h4>";


    $firstrun=0;
    while ($mrow = mysql_fetch_object($mresult))
    {
        // Only print if we have a value ({$prevcontactid} / {$mrow->contactid})
        if ($prevcontactid!=$mrow->contactid AND $firstrun!=0)
        {
            $numones=count($storeone);
            if ($numones>0)
            {
                for($c=1;$c<=$numones;$c++)
                {
                    if ($storeone[$c]>0) $qr=number_format($storeone[$c]/$storetwo[$c],2);
                    else $qr=0;
                    if ($storeone[$c]>0) $qp=number_format((($qr -1) * (100 / ($maxscore -1))), 0);
                    else $qp=0;
                    $html .= "Q$c: {$q[$c]->question} {$qr} <strong>({$qp}%)</strong><br />";
                    $gtotal+=$qr;
                }
                if ($c>0) $c--;
                $total_average=number_format($gtotal/$c,2);
                // $total_percent=number_format((($gtotal / ($maxscore * $c)) * 100), 0);
                $total_percent=number_format((($total_average -1) * (100 / ($maxscore -1))), 0);
                if ($total_percent < 0) $total_percent=0;

                $html .= "<p>Positivity: {$total_average} <strong>({$total_percent}%)</strong>, after $surveys survey";
                if ($surveys<>1) $html.='s';
                $html .= "</p><br /><br />";
            }
            else $html = "";

            if ($total_average>0)
            {
                echo "{$html}";
                $countcontacts++;

                $counter[floor($total_percent / 10)] ++;
            }
            unset($qavgavg);
            unset($qanswer);
            unset($dbg);
            unset($storeone);
            unset($storetwo);
            unset($gtotal);
            $surveys=0;
        }
        $firstrun=1;

        // Loop through reports
        $totalresult=0;
        $numquestions=0;
        $surveys++;
        $html = "<h4 style='text-align: left;'><a href='../contact_details.php?id={$mrow->contactid}' title='Jump to Contact'>{$mrow->forenames} {$mrow->surname}</a>, <a href='../site_details.php?id={$mrow->siteid}&action=show' title='Jump to site'>{$mrow->sitename}</a></h4>";
        $csql = "SELECT * FROM feedbackquestions WHERE formid='{$formid}' AND type='text' ORDER BY id DESC";
        $cresult = mysql_query($csql);
        if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
        $crow = mysql_fetch_object($cresult);
        $textquestion = $crow->id;
        $csql = "SELECT distinct incidents.id as incidentid, result, incidents.title as title FROM feedbackrespondents, incidents, users, feedbackresults
                WHERE feedbackrespondents.incidentid=incidents.id
                AND incidents.owner=users.id
                AND feedbackrespondents.id=feedbackresults.respondentid
                AND feedbackresults.questionid='$textquestion'
                AND feedbackrespondents.id='$mrow->reportid'
                ORDER BY incidents.contact, incidents.id";
        $cresult = mysql_query($csql);

        if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);

        while ($crow = mysql_fetch_object($cresult)){
          if ($crow->result != "")
            $html.= "<p>{$crow->result}<br /><em><a href=\"javascript:incident_details_window(\'{$crow->incidentid}\',\'incident35393\')\">{$crow->incidentid}</a> {$crow->title}</em></p>";
        }

        $qsql = "SELECT * FROM feedbackquestions WHERE formid='{$formid}' AND type='rating' ORDER BY taborder";
        $qresult = mysql_query($qsql);

        if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
        while ($qrow = mysql_fetch_object($qresult))
        {
            $numquestions++;
            $sql = "SELECT * FROM feedbackrespondents, incidents, users, feedbackresults ";
            $sql .= "WHERE feedbackrespondents.incidentid=incidents.id ";
            $sql .= "AND incidents.owner=users.id ";
            $sql .= "AND feedbackrespondents.id=feedbackresults.respondentid ";
            $sql .= "AND feedbackresults.questionid='$qrow->id' ";
            $sql .= "AND feedbackrespondents.id='$mrow->reportid' ";
            $sql .= "ORDER BY incidents.contact, incidents.id";
            $result = mysql_query($sql);

            if (mysql_error()) trigger_error(mysql_error(), E_USER_ERROR);
            $numresults=0;
            $cumul=0;
            $percent=0;
            $average=0;
            $answercount=mysql_num_rows($result);

            if ($answercount>0)
            {
                while ($row = mysql_fetch_object($result))
                {
                    // Loop through the results
                    if (!empty($row->result))
                    {
                        $cumul+=$row->result;
                        $numresults++;
                        $storeone[$qrow->taborder]+=$row->result;
                        $storetwo[$qrow->taborder]++;
                        $storethree[$qrow->taborder]=$qrow->id;
                    }
                }
            }

            if ($numresults>0) $average=number_format(($cumul/$numresults), 2);
            $percent =number_format((($average / $maxscore ) * 100), 0);
            $totalresult+=$average;

            $qanswer[$qrow->taborder]+=$average;
            $qavgavg=$qanswer[$qrow->taborder];
        }

        $prevcontactid=$mrow->contactid;
    }
    echo "<h2>Summary</h2><p>This graph shows different levels of positivity of the contacts shown above:</p>";


    $adjust=13;
    $min=4;
    for ($i = 0; $i <= 10; $i++) {
      if ($countcontacts > 0) $weighted = number_format((($counter[$i] / $countcontacts) * 100), 0);
      else $weighted = 0;
      echo "<div style='background: #B";
      echo dechex(floor($i*1.5));
      echo "0; color: #FFF; float:left; width: ".($min + ($weighted * $adjust))."px;'>&nbsp;</div>&nbsp; ";
      echo ($i*10);
      if ($i<10) {
        echo " - ";
        echo ($i*10) + 9;
      }
      echo "% ({$weighted}%)<br />";
    }


}
else
{
    echo "<p class='error'>No feedback found</p>";
}

echo "</div>\n";
include('htmlfooter.inc.php');
?>
