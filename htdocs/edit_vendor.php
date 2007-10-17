<?php
// edit_vendor.php - Page to edit vendor details
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Paul Heaney <paulheaney[at]users.sourceforge.net>

$permission=56; //add software
require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

$action = cleanvar($_REQUEST['action']);

switch ($action)
{
    case 'save':
        $vendorname = cleanvar($_REQUEST['name']);
        $vendorid = cleanvar($_REQUEST['vendorid']);

        // check for blank name
        if ($vendorname == "")
        {
            $errors = 1;
            $errors_string .= "<p class='error'>You must enter a name</p>\n";
        }

        if($errors == 0)
        {
            $sql = "UPDATE vendors SET name = '{$vendorname}' WHERE id = '{$vendorid}'";
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
            confirmation_page("2", "main.php", "<h2>Edit Successful</h2><h5>Please wait while you are redirected...</h5>");
        }
        else
        {
            include('htmlheader.inc.php');
            echo $errors_string;
            include('htmlfooter.inc.php');
        }
        break;
    case 'edit':
        $vendorid = cleanvar($_REQUEST['vendorid']);
        $vendorname = cleanvar($_REQUEST['vendorname']);
        include('htmlheader.inc.php');
        echo "<h2>Edit vendor {$vendorname}</h2>";
        echo "<form action='{$_SERVER['PHP_SELF']}' name'editvendor'>";
        echo "<table align='center'>";
        echo "<tr><th>{$strVendor} Name:</th><td><input maxlength='50' name='name' size='30' value='$vendorname'/></td></tr>";
        echo "</table>";
        echo "<input type='hidden' name='action' value='save' />";
        echo "<input type='hidden' name='vendorid' value='{$vendorid}' />";
        echo "<p align='center'><input name='submit' type='submit' value='Edit Vendor' /></p>";
        echo "</form>";
        include('htmlfooter.inc.php');
        break;
    default:
        include('htmlheader.inc.php');
        echo "<h2>Edit Vendor</h2>";
        $sql = "SELECT * FROM vendors";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
        if(mysql_num_rows($result) > 0)
        {
            echo "<table class='vertical' align='center'>";
            $shade='shade1';
            while($row = mysql_fetch_object($result))
            {
                echo "<tr class='{$shade}'><td><a href={$_SERVER['PHP_SELF']}?action=edit&vendorid=$row->id&vendorname=".urlencode($row->name).">$row->name</a></td></tr>";

                if($shade=='shade1') $shade='shade2';
                else $shade='shade1';
            }
            echo "</table>";
        }
        echo "<p align='center'><a href='add_vendor.php'>Add vendor</a></p>";
        include('htmlfooter.inc.php');
        break;
}

?>