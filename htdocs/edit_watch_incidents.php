<?php
// edit_watch_incidents.php - Interface to allow users to change the preferences of the watch incidents on the dashboard
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2006 Salford Software Ltd.
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Paul Heaney <paulheaney[at]users.sourceforge.net>

$permission=0; // not required
require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');


$action = $_REQUEST['action'];

switch($action)
{
    case 'add':
        include('htmlheader.inc.php');
        $type = $_REQUEST['type'];
        echo "<h2>Add new set of watched incidents</h2>";
        echo "<form action='{$_SERVER['PHP_SELF']}?action=do_add&type={$type}' method='post'>";
        echo "<table class='vertical'>";
        echo "<tr><td>";

        switch($type)
        {
            case '0': //site
                echo "{$strSite}: ";
                echo site_drop_down('id','');
                break;
            case '1': //contact
                echo "{$strContact}: ";
                echo contact_drop_down('id','');
                break;
            case '2': //engineer
                echo "{$strEngineer}: ";
                echo user_drop_down('id','');
                break;
        }

        echo "</td><tr>";
        echo "</table>";
        echo "<p align='center'><input name='submit' type='submit' value='{$strAdd}' /></p>";
        include('htmlfooter.inc.php');
        break;
    case 'do_add':
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $sql = "INSERT INTO dashboard_watch_incidents VALUES ({$sit[2]},'{$type}','{$id}')";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        if(!$result) echo "<p class='error'>Failed to add watch incident</p>";
        else
        {
            confirmation_page("2", "edit_watch_incidents.php", "<h2>Watch Incidents added</h2><h5>Please wait while you are redirected...</h5>");
        }
        break;
    case 'delete':
        $id = $_REQUEST['id'];
        $type = $_REQUEST['type'];
        $sql = "DELETE FROM dashboard_watch_incidents WHERE id = '{$id}' AND userid = {$sit[2]} AND type = '{$type}'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

        if(!$result) echo "<p class='error'>Delete watch failed</p>";
        else
        {
            confirmation_page("2", "edit_watch_incidents.php", "<h2>Watch incidents removal succeded</h2><h5>Please wait while you are redirected...</h5>");
        }
        break;
    default:
        include('htmlheader.inc.php');
        echo "<h2>Edit watched incidents</h2>";

        for($i = 0; $i < 3; $i++)
        {
            $sql = "SELECT * FROM dashboard_watch_incidents WHERE userid = {$sit[2]} AND type = {$i}";

            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);

            echo "<table align='center'>";
            echo "<tr><td align='left'><b>";
            switch($i)
            {
                case 0: echo $strSites;
                    break;
                case 1: echo $strContacts;
                    break;
                case 2: echo $strEngineers;
                    break;
            }
            echo "</b></td><td align='right'>";
            echo "<a href='{$_SERVER['PHP_SELF']}?type={$i}&amp;action=add'>";
            switch($i)
            {
                case 0: echo $strAddSite;
                    break;
                case 1: echo $strAddContact;
                    break;
                case 2: echo "Add Engineer";
                    break;
            }
            echo "</a></td></tr>";

            if(mysql_num_rows($result) > 0)
            {
                $shade='shade1';
                while($obj = mysql_fetch_object($result))
                {
                    $name = '';
                    switch($obj->type)
                    {
                        case 0: //site
                            $sql = "SELECT name FROM sites WHERE id = {$obj->id}";
                            $iresult = mysql_query($sql);
                            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                            $iobj = mysql_fetch_object($iresult);
                            $name = stripslashes($iobj->name);
                            break;
                        case 1: //contact
                            $sql = "SELECT forenames, surname FROM contacts WHERE id = {$obj->id}";
                            $iresult = mysql_query($sql);
                            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                            $iobj = mysql_fetch_object($iresult);
                            $name = stripslashes($iobj->forenames)." ".stripslashes($iobj->surname);
                            break;
                        case 2: //Engineer
                            $sql = "SELECT realname FROM users WHERE id = {$obj->id}";
                            $iresult = mysql_query($sql);
                            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
                            $iobj = mysql_fetch_object($iresult);
                            $name = stripslashes($iobj->realname);
                            break;
                    }

                    echo "<tr class='$shade'><td>{$name}</td><td><a href='{$_SERVER['PHP_SELF']}?type={$obj->type}&amp;id={$obj->id}&amp;action=delete'>Remove</a></td></tr>";
                    if ($shade=='shade1') $shade='shade2';
                    else $shade='shade1';
                }
            }
            else
            {
                echo "<tr><td colspan='2'>No watches set up for this type</td></tr>";
            }

            echo "</table>";
        }
        include('htmlfooter.inc.php');
        break;

}

?>