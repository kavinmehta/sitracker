<?php
// config.php - Interface for configuring SiT
//
//     NOTE: This is not the configuration file, see config.inc.php
//           or config.inc.php-dist
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Author: Ivan Lucas, <ivanlucas[at]users.sourceforge.net


$permission = 22; // Administrate

require ('core.php');
require (APPLICATION_LIBPATH . 'functions.inc.php');
// This page requires authentication
require (APPLICATION_LIBPATH . 'auth.inc.php');

// External variables
$selcat = cleanvar($_REQUEST['cat']);
$seltab = cleanvar($_REQUEST['tab']);
$action = cleanvar($_REQUEST['action']);


require(APPLICATION_LIBPATH . 'configvars.inc.php');

if ($action == 'save' AND ($CONFIG['demo'] !== TRUE OR $_SESSION['userid'] == 1))
{
    if (!empty($selcat))
    {
        $savevar = array();
        foreach ($CFGCAT[$selcat] AS $catvar)
        {
            $value = cleanvar($_REQUEST[$catvar]);
            // Type conversions
            switch ($CFGVAR[$catvar]['type'])
            {
                case '1darray':
                    $parts = explode(',', $value);
                    foreach ($parts AS $k => $v)
                    {
                        $parts[$k] = "'{$v}'";
                    }
                    $value = 'array(' . implode(',', $parts) . ')';
                break;

                case '2darray':
                    $value = str_replace('\n', ',', $value);
                    $value = str_replace('\r', '', $value);
                    $value = str_replace("\r", '', $value);
                    $value = str_replace("\n", '', $value);
                    $parts = explode(",", $value);
                    foreach ($parts AS $k => $v)
                    {
                        $y = explode('=&gt;', $v);
                        $parts[$k] = "'{$y[0]}'=>'{$y[1]}'";
                    }
                    $value = 'array(' . implode(',', $parts) . ')';
                break;

                case 'languagemultiselect':
                    if ($_REQUEST['available_i18ncheckbox'] != '')
                    {
                        $value = '';
                    }
                    else
                    {
                        foreach ($value AS $k => $v)
                        {
                            $parts[$k] = "'{$v}'";
                        }
                        $value = 'array(' . implode(',', $parts) . ')';
                    }
                break;
            }
            $savevar[$catvar] = mysql_real_escape_string($value);
            if (substr($value, 0, 6)=='array(')
            {
                eval("\$val = $value;");
                $value = $val;
            }
            $CONFIG[$catvar] = $value;
        }
        if ($CONFIG['debug']) $dbg .= "<pre>".print_r($savevar,true)."</pre>";
        cfgSave($savevar);
    }
}

$pagescripts = array('FormProtector.js');
include (APPLICATION_INCPATH . 'htmlheader.inc.php');

echo "<h2>".icon('settings', 32, $strConfiguration);
echo " {$CONFIG['application_shortname']} {$strConfiguration}</h2>";

echo "<div class='tabcontainer'>";
echo "<ul>";
foreach ($CFGTAB AS $tab => $cat)
{
    if (empty($seltab)) $seltab = 'application';
    echo "<li";
    if ($seltab == $tab) echo " class='active'";
    echo "><a href='{$_SERVER['PHP_SELF']}?tab={$tab}'>{$TABI18n[$tab]}</a></li>";
}
echo "</ul>";
echo "</div>";


echo "<div class='smalltabs'>";
echo "<ul>";
foreach ($CFGTAB[$seltab] AS $cat)
{
    if (empty($selcat)) $selcat = $CFGTAB[$seltab][0];
    echo "<li";
    if ($selcat == $cat) echo " class='active'";
    $catname = $CATI18N[$cat];
    if (empty($catname)) $catname = $cat;
    echo "><a href='{$_SERVER['PHP_SELF']}?tab={$seltab}&amp;cat={$cat}'>{$catname}</a></li>";
}
echo "</ul>";
echo "</div>";

echo "<div style='clear: both;'></div>";

echo "<form id='configform' action='{$_SERVER['PHP_SELF']}' method='post'>";
echo "<fieldset>";
$catname = $CATI18N[$selcat];
if (empty($catname)) $catname = $selcat;
echo "<legend>{$catname}</legend>";
if (!empty($CATINTRO[$selcat]))
{
    echo "<div id='catintro'>{$CATINTRO[$selcat]}</div>";
}
if (!empty($selcat))
{
    foreach ($CFGCAT[$selcat] AS $catvar)
    {
        echo cfgVarInput($catvar, $CONFIG['debug']);
    }

}
echo "</fieldset>";
echo "<input type='hidden' name='cat' value='{$selcat}' />";
echo "<input type='hidden' name='tab' value='{$seltab}' />";
echo "<input type='hidden' name='action' value='save' />";
if ($CONFIG['demo'] !== TRUE OR $_SESSION['userid'] == 1)
{
    echo "<p><input type='reset' value=\"{$strReset}\" /> ";
    echo "<input type='submit' value=\"{$strSave}\" />";
    echo "</p>";
}
echo "</form>";
echo protectform('configform');

include (APPLICATION_INCPATH . 'htmlfooter.inc.php');
?>
