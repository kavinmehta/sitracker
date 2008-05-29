<?php
// dashboard_watch_incidents.php - Watch incidents on your dashboard either from a site, a customer or a user
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Paul Heaney <paulheaney[at]users.sourceforge.net>

$dashboard_watch_incidents_version = 1;

function dashboard_watch_incidents($dashletid)
{
    global $sit, $CONFIG, $iconset;

    $content = "<p align='center'><img src='{$CONFIG['application_webpath']}images/ajax-loader.gif' alt='Loading icon' /></p>";
    echo dashlet('watch_incidents', $dashletid, icon('support', 16), $GLOBALS['strWatchIncidents'], '', $content);

/*    echo "<div class='windowbox' id='$row-$dashboardid'>";

    // echo "<div class='windowtitle'><div><a href='edit_watch_incidents.php'>";
    echo "<div class='windowtitle'><div>";
    echo "<a href=\"javascript:get_and_display('ajaxdata.php?action=dashboard_edit&amp;dashboard=watch_incidents','watch_incidents_windows', false);\">";
    echo "{$GLOBALS['strEdit']}</a> | <a href='edit_watch_incidents.php'>oldedit</a> | ";
    echo "<a href=\"javascript:get_and_display('ajaxdata.php?action=dashboard_display&amp;dashboard=watch_incidents','watch_incidents_windows', false);\">R</a></div>".icon('support', 16)." ";
    echo "{$GLOBALS['strWatchIncidents']}"; //, user_realname($user, TRUE));

    echo "</div><div class='window' id='watch_incidents_windows'>";



    echo "</div>";
    echo "</div>";
    //echo "<script type='text/javascript'>\n//<![CDATA[\nget_and_display('display_watch_incidents.inc.php','watch_incidents_windows');\n//]]>\n</script>";
    echo "<script type='text/javascript'>\n//<![CDATA[\nget_and_display('ajaxdata.php?action=dashboard_display&dashboard=watch_incidents','watch_incidents_windows', false);\n//]]>\n</script>";*/
}

function dashboard_watch_incidents_install()
{
    global $CONFIG;
    $schema = "CREATE TABLE IF NOT EXISTS `{$CONFIG['db_tableprefix']}dashboard_watch_incidents` (
        `userid` tinyint(4) NOT NULL,
        `type` tinyint(4) NOT NULL,
        `id` int(11) NOT NULL,
        PRIMARY KEY  (`userid`,`type`,`id`)
        ) ENGINE=MyISAM ;";

    $result = mysql_query($schema);
    if (mysql_error())
    {
        echo "<p>Dashboard watch incidents failed to install, please run the following SQL statement on the SiT database to create the required schema.</p>";
        echo "<pre>{$schema}</pre>";
        $res = FALSE;
    }
    else $res = TRUE;

    return $res;
}


function dashboard_watch_incidents_display($dashletid)
{
    global $CONFIG, $sit;

    $html = "<script type='text/javascript'>
    //<![CDATA[
    function statusform_submit(user)
    {
        URL = \"incidents.php?status=\" + window.document.statusform.status.options[window.document.statusform.status.selectedIndex].value + \"&amp;user=\" + user;
        window.confirm(URL);
        window.location.href = URL;
    }
    //]]>
    </script>";

// FIXME, commented out the queue selector, needs recoding to work with one-file dashboards - Ivan 22May08

//     $html .= "<form action='{$_SERVER['PHP_SELF']}' style='text-align: center;'>";
//     $html .= "{$GLOBALS['strQueue']}: <select class='dropdown' name='queue' onchange='window.location.href=this.options[this.selectedIndex].value'>\n";
//     $html .= "<option ";
//     if ($queue == 5)
//     {
//         $html .= "selected='selected' ";
//     }
//     $html .= "value=\"javascript:get_and_display('display_watch_incidents.inc.php?queue=5','watch_incidents_windows');\">{$GLOBALS['strAll']}</option>\n";
//     $html .= "<option ";
//     if ($queue == 1)
//     {
//         $html .= "selected='selected' ";
//     }
//     $html .= "value=\"javascript:get_and_display('display_watch_incidents.inc.php?queue=1','watch_incidents_windows');\">{$GLOBALS['strActionNeeded']}</option>\n";
//     $html .= "<option ";
//     if ($queue == 3)
//     {
//         $html .= "selected='selected' ";
//     }
//     $html .= "value=\"javascript:get_and_display('display_watch_incidents.inc.php?queue=3','watch_incidents_windows');\">{$GLOBALS['strAllOpen']}</option>\n";
//     $html .= "</select>\n";
//     $html .= "</form>";

    $sql = "SELECT type, id FROM `{$CONFIG['db_tableprefix']}dashboard_watch_incidents` WHERE userid = {$sit[2]} ORDER BY type";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error().$sql,E_USER_WARNING);


    if (mysql_num_rows($result) > 0)
    {
        $header_printed = FALSE;
        $previous = 0;
        while ($obj = mysql_fetch_object($result))
        {
            if ($obj->type !=3 AND $previous == 3)
            {
                $html .= "</table>";
            }

            if ($obj->type == 3 AND !$header_printed)
            {
                $html .= "<table>";
            }
            else if($obj->type != 3)
            {
                $html .= "<table>";
            }

            switch ($obj->type)
            {
                case '0': //Site
                    $sql = "SELECT i.id, i.title, i.status, i.servicelevel, i.maintenanceid, i.priority, c.forenames, c.surname, c.siteid ";
                    $sql .= "FROM `{$GLOBALS['dbIncidents']}` AS i, `{$GLOBALS['dbContacts']}`  AS c ";
                    $sql .= "WHERE i.contact = c.id AND c.siteid = {$obj->id} ";
                    $sql .= "AND i.status != 2 AND i.status != 7 ";

                    $lsql = "SELECT name FROM `{$GLOBALS['dbSites']}` WHERE id = {$obj->id}";
                    $lresult = mysql_query($lsql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                    $lobj = mysql_fetch_object($lresult);
                    $html .= "<tr><th colspan='3'>{$lobj->name} ({$GLOBALS['strSite']})</th></tr>";
                    break;
                case '1': //contact
                    $sql = "SELECT i.id, i.title, i.status, i.servicelevel, i.maintenanceid, i.priority, c.forenames, c.surname, c.siteid ";
                    $sql .= "FROM `{$GLOBALS['dbIncidents']}` AS i, `{$dbContacts}`  AS c ";
                    $sql .= "WHERE i.contact = c.id AND i.contact = {$obj->id} ";
                    $sql .= "AND i.status != 2 AND i.status != 7 ";

                    $lsql = "SELECT forenames, surname FROM `{$GLOBALS['dbContacts']}` WHERE id = {$obj->id} ";
                    $lresult = mysql_query($lsql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                    $lobj = mysql_fetch_object($lresult);
                    $html .= "<tr><th colspan='3'>{$lobj->forenames} {$lobj->surname} ({$GLOBALS['strContact']})</th></tr>";
                    break;
                case '2': //engineer
                    $sql = "SELECT i.id, i.title, i.status, i.servicelevel, i.maintenanceid, i.priority, c.forenames, c.surname, c.siteid ";
                    $sql .= "FROM `{$GLOBALS['dbIncidents']}` AS i, `{$GLOBALS['dbContacts']}`  AS c ";
                    $sql .= "WHERE i.contact = c.id AND (i.owner = {$obj->id} OR i.towner = {$obj->id}) ";
                    $sql .= "AND i.status != 2 AND i.status != 7 ";

                    $lsql = "SELECT realname FROM `{$GLOBALS['dbUsers']}` WHERE id = {$obj->id}";
                    $lresult = mysql_query($lsql);
                    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                    $lobj = mysql_fetch_object($lresult);
                    $html .= "<tr><th colspan='3'>";
                    printf($GLOBALS['strIncidentsForEngineer'], $lobj->realname);
                    $html .= "</th></tr>";

                    break;
                case '3': //incident
                    $sql = "SELECT i.id, i.title, i.status, i.servicelevel, i.maintenanceid, i.priority ";
                    $sql .= "FROM `{$GLOBALS['dbIncidents']}` AS i ";
                    $sql .= "WHERE i.id = {$obj->id} ";
                    //$sql .= "AND incidents.status != 2 AND incidents.status != 7";
                    break;
                default:
                    $sql = '';
            }

            if (!empty($sql))
            {
                switch ($queue)
                {
                    case 1: // awaiting action
                        $sql .= "AND ((timeofnextaction > 0 AND timeofnextaction < $now) OR ";
                        $sql .= "(IF ((status >= 5 AND status <=8), ($now - lastupdated) > ({$CONFIG['regular_contact_days']} * 86400), 1=2 ) ";  // awaiting
                        $sql .= "OR IF (status='1' OR status='3' OR status='4', 1=1 , 1=2) ";  // active, research, left message - show all
                        $sql .= ") AND timeofnextaction < $now ) ";
                        break;
                    case 3: // All Open
                        $sql .= "AND status!='2' ";
                        break;
                    case 5: // ALL
                    default:
                        break;
                }

                $iresult = mysql_query($sql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

                if (mysql_num_rows($iresult) > 0)
                {
                    if ($obj->type == 3 AND !$header_printed)
                    {
                        $html .= "<tr><th colspan='4'>{$GLOBALS['strIncidents']}</th></tr>";
                        $html .= "<tr>";
                        $html .= colheader('id', $GLOBALS['strID']);
                        $html .= colheader('title', $GLOBALS['strTitle']);
                        //$html .= colheader('customer', $GLOBALS['strCustomer']);
                        $html .= colheader('status', $GLOBALS['strStatus']);
                        $html .= "</tr>\n";
                        $header_printed = TRUE;
                    }
                    else if($obj->type != 3)
                    {
                        $html .= "<tr>";
                        $html .= colheader('id', $GLOBALS['strID']);
                        $html .= colheader('title', $GLOBALS['strTitle']);
                        //$html .= colheader('customer', $GLOBALS['strCustomer']);
                        $html .= colheader('status', $GLOBALS['strStatus']);
                        $html .= "</tr>\n";
                    }

                    $shade='shade1';
                    while ($incident = mysql_fetch_object($iresult))
                    {
                        $html .= "<tr class='$shade'>";
                        $html .= "<td>{$incident->id}</td>";
                        $html .= "<td><a href='javascript:incident_details_window({$incident->id}) '  class='info'>".$incident->title;
                        $html .= "<span><strong>{$GLOBALS['strCustomer']}:</strong> ".sprintf($GLOBALS['strXofX'], "{$incident->forenames} {$incident->surname}",site_name($incident->siteid));
                        list($update_userid, $update_type, $update_currentowner, $update_currentstatus, $update_body, $update_timestamp, $update_nextaction, $update_id)=incident_lastupdate($incident->id);
                        $update_body = parse_updatebody($update_body);
                        if (!empty($update_body) AND $update_body!='...')
                        {
                            $html .= "<br />{$update_body}";
                        }
                        $html .= "</span></a></td>";
                        $html .= "<td>".incidentstatus_name($incident->status)."</td>";
                        $html .= "</tr>\n";
                        if ($shade=='shade1') $shade='shade2';
                        else $shade='shade1';
                    }
                }
                else
                {
                    if($obj->type == 3 AND !$header_printed)
                    {
                        $html .= "<tr><th colspan='3'>{$GLOBALS['strIncidents']}</th></tr>";
                        $html .= "<tr><td colspan='3'>{$GLOBALS['strNoIncidents']}</td></tr>\n";
                        $header_printed = TRUE;
                    }
                    else if($obj->type != 3)
                    {
                        $html .= "<tr><td colspan='3'>{$GLOBALS['strNoIncidents']}</td></tr>\n";
                    }
                }
            }
            if ($obj->type == 3 AND !$header_printed)
            {
                $html .= "</table>\n";
            }

            $previous = $obj->type;
        }
    }
    else
    {
        $html .= "<p align='center'>{$GLOBALS['strNoRecords']}</p>";
    }

    return $html;
}


function dashboard_watch_incidents_edit($dashletid)
{
    global $CONFIG, $sit;
    $editaction = $_REQUEST['editaction'];

    switch ($editaction)
    {
        case 'add':
            $type = $_REQUEST['type'];
            echo "<h2>{$GLOBALS['strWatchAddSet']}</h2>";
            echo "<form id='dwiaddform' action='{$_SERVER['PHP_SELF']}?action=do_add&type={$type}' method='post'>";
            echo "<table class='vertical'>";
            echo "<tr><td>";

            switch ($type)
            {
                case '0': //site
                    echo "{$GLOBALS['strSite']}: ";
                    echo site_drop_down('id','');
                    break;
                case '1': //contact
                    echo "{$GLOABLS['strContact']}: ";
                    echo contact_drop_down('id','');
                    break;
                case '2': //engineer
                    echo "{$GLOBALS['strEngineer']}: ";
                    echo user_drop_down('id','',FALSE);
                    break;
                case '3': //Incident
                    echo "{$GLOBALS['strIncident']}:";
                    echo "<input class='textbox' name='id' size='30' />";
                    break;
            }

            echo "</td><tr>";
            echo "</table>";
            echo "<p align='center'><a href=\"javascript:ajax_save('ajaxdata.php?action=dashboard_edit&dashboard=watch_incidents&did={$dashletid}&editaction=do_add&type={$type}', 'dwiaddform');\">{$GLOBALS['strAdd']}</a></p>";
            // echo "<p align='center'><input name='submit' type='submit' value='{$GLOBALS['strAdd']}' /></p>";
            break;

        case 'do_add':
            $id = $_REQUEST['id'];
            $type = $_REQUEST['type'];
            $sql = "INSERT INTO `{$CONFIG['db_tableprefix']}dashboard_watch_incidents` VALUES ({$sit[2]},'{$type}','{$id}')";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

            if (!$result)
            {
                echo "<p class='error'>{$strWatchAddFailed}</p>";
            }
            else
            {
                html_redirect("edit_watch_incidents.php", TRUE, $strAddedSuccessfully);
            }
            break;
        case 'delete':
            $id = $_REQUEST['id'];
            $type = $_REQUEST['type'];
            $sql = "DELETE FROM `{$CONFIG['db_tableprefix']}dashboard_watch_incidents` WHERE id = '{$id}' AND userid = {$sit[2]} AND type = '{$type}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

            if (!$result)
            {
                echo "<p class='error'>{$strWatchDeleteFailed}</p>";
            }
            else
            {
                html_redirect("edit_watch_incidents.php", TRUE, $strRemovedSuccessful);
            }
            break;
        default:
            echo "<h3>{$GLOBALS['strEditWatchedIncidents']}</h3>";

            echo "<table align='center'>";
            for($i = 0; $i < 4; $i++)
            {
                $sql = "SELECT * FROM `{$CONFIG['db_tableprefix']}dashboard_watch_incidents` WHERE userid = {$sit[2]} AND type = {$i}";

                $result = mysql_query($sql);
                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

                echo "<tr><td align='left'><strong>";
                switch ($i)
                {
                    case 0: echo $GLOBALS['strSites'];
                        break;
                    case 1: echo $GLOBALS['strContacts'];
                        break;
                    case 2: echo $GLOBALS['strEngineers'];
                        break;
                    case 3: echo $GLOBALS['strIncidents'];
                        break;
                }
                echo "</strong></td><td align='right'>";
                echo "<a href=\"javascript:get_and_display('ajaxdata.php?action=dashboard_edit&dashboard=watch_incidents&did={$dashletid}&editaction=add&type={$i}', '{$dashletid}', false);\">";

                //'ajaxdata.php?action=dashboard_edit&amp;dashboard=watch_incidents&amp;type={$i}&amp;editaction=add'>";
                switch ($i)
                {
                    case 0: echo $GLOBALS['strAddSite'];
                        break;
                    case 1: echo $GLOBALS['strAddContact'];
                        break;
                    case 2: echo $GLOBALS['strAddUser'];
                        break;
                    case 3: echo $GLOBALS['strAddIncident'];
                        break;
                }
                echo "</a></td></tr>";

                if (mysql_num_rows($result) > 0)
                {
                    $shade = 'shade1';
                    while ($obj = mysql_fetch_object($result))
                    {
                        $name = '';
                        switch ($obj->type)
                        {
                            case 0: //site
                                $sql = "SELECT name FROM `{$GLOBALS['dbSites']}` WHERE id = {$obj->id}";
                                $iresult = mysql_query($sql);
                                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                                $iobj = mysql_fetch_object($iresult);
                                $name = $iobj->name;
                                break;
                            case 1: //contact
                                $sql = "SELECT forenames, surname FROM `{$GLOBALS['dbContacts']}` WHERE id = {$obj->id}";
                                $iresult = mysql_query($sql);
                                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                                $iobj = mysql_fetch_object($iresult);
                                $name = $iobj->forenames.' '.$iobj->surname;
                                break;
                            case 2: //Engineer
                                $sql = "SELECT realname FROM `{$GLOBALS['dbUsers']}` WHERE id = {$obj->id}";
                                $iresult = mysql_query($sql);
                                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                                $iobj = mysql_fetch_object($iresult);
                                $name = $iobj->realname;
                                break;
                            case 3: //Incident
                                $sql = "SELECT title FROM `{$GLOBALS['dbIncidents']}` WHERE id = {$obj->id}";
                                $iresult = mysql_query($sql);
                                if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                                $iobj = mysql_fetch_object($iresult);
                                $name = "<a href=\"javascript:incident_details_window('{$obj->id}','incident{$obj->id}')\" class='info'>[{$obj->id}] {$iobj->title}</a>";
                                break;
                        }

                        echo "<tr class='$shade'><td>{$name}</td><td><a href='{$_SERVER['PHP_SELF']}?type={$obj->type}&amp;id={$obj->id}&amp;action=delete'>{$strRemove}</a></td></tr>";
                        if ($shade == 'shade1') $shade = 'shade2';
                        else $shade = 'shade1';
                    }
                }
                else
                {
                    echo "<tr><td colspan='2'>{$GLOBALS['strNoIncidentsBeingWatchOfType']}</td></tr>";
                }
            }
            echo "</table>";
            break;
    }

    return $html;
}

?>