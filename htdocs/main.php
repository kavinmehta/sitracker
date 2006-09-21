<?php
// main.php - Front page
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2006 Salford Software Ltd.
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
// This Page Is Valid XHTML 1.0 Transitional! 27Oct05

$permission=0; // not required
require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

// Valid user
include('htmlheader.inc.php');

echo "<script type=\"text/javascript\" src=\"scripts/dojo/dojo.js\"></script>";
?>
<script type="text/javascript">
<!--
    dojo.require("dojo.dnd.*");
    dojo.require("dojo.event.*");

    function byId(id){
        return document.getElementById(id);
    }

    function init(){
        // list one
        var dl = byId("dragList1");
        new dojo.dnd.HtmlDropTarget(dl, ["li1"]);
        var lis = dl.getElementsByTagName("div");
        for(var x=0; x<lis.length; x++){
            new dojo.dnd.HtmlDragSource(lis[x], "li1");
        }

        // list two
        var dl = byId("dragList2");
        new dojo.dnd.HtmlDropTarget(dl, ["li1"]);
        var lis = dl.getElementsByTagName("div");
        for(var x=0; x<lis.length; x++){
            new dojo.dnd.HtmlDragSource(lis[x], "li1");
        }

        // list three
        var dl = byId("dragList3");
        new dojo.dnd.HtmlDropTarget(dl, ["li2"]);
        var lis = dl.getElementsByTagName("li");
        for(var x=0; x<lis.length; x++){
            new dojo.dnd.HtmlDragSource(lis[x], "li2");
        }
    }

    dojo.event.connect(dojo, "loaded", "init");
-->
</script>
<?php

echo "<table border=\"0\" width=\"99%\"><tr><td width=\"33%\"><div id='dragList1'>AAA</div></td><td width=\"33%\">";


echo "<div style='height: 400px;'  id='dragList2'>";

dashboard_do("dashboard_random_tip");

dashboard_do("dashboard_statistics");

echo "</div>";


echo "</td><td width=\"33%\" valign=\"top\">";

dashboard_do("users_incidents");

echo "</td></tr></table>";


// Check users email address
if (empty($_SESSION['email']) OR !preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/',$_SESSION['email']))
    echo "<p class='error'>Please <a href='edit_profile.php'>edit your profile</a>i and set a valid email address</p>";


//  Users Login Details
echo "<div id='userbar'>Logged in as: <strong>{$sit[0]}</strong>, ";
echo "currently <strong>".userstatus_name(user_status($sit[2]))."</strong> and ";

if (user_accepting($sit[2])!='Yes')
{
    echo "<span class=\"error\">Not Accepting</span>";
}
else
{
    echo "<strong>Accepting</strong>";
}
echo " calls";
if ($sit[3]=='public')
{
    echo "- Public/Shared Computer (Increased Security)";
}

?>



</div>
<br />
<div id='footerbar'>
<?php
echo "<form style='margin: 0px;' action='{$_SERVER['PHP_SELF']}'>";
?>
Set your Status: <?php if(isset($sit[2])) userstatus_bardrop_down("status", user_status($sit[2])); ?></form>
</div>
<?php
include('htmlfooter.inc.php');
?>