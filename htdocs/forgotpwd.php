<?php
// forgotpwd.php - Forgotten password page
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// Authors: Paul Heaney <paulheaney[at]users.sourceforge.net>
//          Ivan Lucas <ivanlucas[at]users.sourceforge.net>
//          Kieran Hogg <kieran_hogg[at]users.sourceforge.net>

@include ('set_include_path.inc.php');
$permission = 0; // not required
require ('db_connect.inc.php');

session_name($CONFIG['session_name']);
session_start();
require 'strings.inc.php';
require ('functions.inc.php');

$title = $strForgottenDetails;

// External variables
$email = cleanvar($_REQUEST['emailaddress']);
$username = cleanvar($_REQUEST['username']);
$userid = cleanvar($_REQUEST['userid']);
$contactid = cleanvar($_REQUEST['contactid']);

if (!empty($userid))
{
    $mode = 'user';
}
elseif (!empty($contactid))
{
    $mode = 'contact';
}
$userhash = cleanvar($_REQUEST['hash']);

switch ($_REQUEST['action'])
{
    case 'forgotpwd':
    case 'sendpwd':
    {
        include ('htmlheader.inc.php');
        // First look to see if this is a SiT user
        if (empty($email) AND !empty($userid))
        {
            $sql = "SELECT id, username, password FROM `{$dbUsers}` WHERE id = '{$userid}' LIMIT 1";
        }
        else
        {
            $sql = "SELECT id, username, password FROM `{$dbUsers}` WHERE email = '{$email}' LIMIT 1";
        }
        $userresult = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        $usercount = mysql_num_rows($userresult);
        $userdetails = mysql_fetch_object($userresult);
        if ($usercount == 1)
        {
            $hash = md5($userdetails->username.'.'.$userdetails->password);
            $url = parse_url($_SERVER['HTTP_REFERER']);
            $reseturl = "{$url['scheme']}://{$url['host']}{$CONFIG['application_webpath']}forgotpwd.php?action=confirmreset&userid={$userdetails->id}&hash={$hash}";
            trigger('TRIGGER_USER_RESET_PASSWORD', array('userid' => $userdetails->id, 'passwordreseturl' => $reseturl));
            echo "<h3>{$strInformationSent}</h3>";
            echo "<p>{$strInformationSentRegardingSettingPassword}</p>";
            if ($_REQUEST['action'] == 'forgotpwd')
            {
                echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
            }
            else
            {
                echo "<p><a href='{$_SERVER['HTTP_REFERER']}'>{$strReturnToPreviousPage}</a></p>";
            }

        }
        else
        {
            // This is a SiT contact, not a user
            if (empty($email) AND !empty($contactid))
            {
               $sql = "SELECT id, username, password, email FROM `{$dbContacts}` WHERE id = '{$contactid}' LIMIT 1";
            }
            else
            {
                $sql = "SELECT id, username, password, email FROM `{$dbContacts}` WHERE email = '{$email}' LIMIT 1";
            }
            $contactresult = mysql_query($sql);
            if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);

            $contactcount = mysql_num_rows($contactresult);
            if ($contactcount == 1)
            {
                $row = mysql_fetch_object($contactresult);
                $hash = md5($row->username.'.'.$row->password);
                $url = parse_url($_SERVER['HTTP_REFERER']);
                $reseturl = "{$url['scheme']}://{$url['host']}{$CONFIG['application_webpath']}forgotpwd.php?action=confirmreset&contactid={$row->id}&hash={$hash}";
                trigger('TRIGGER_CONTACT_RESET_PASSWORD', array('contactid' => $row->id, 'passwordreseturl' => $reseturl));
                echo "<h3>{$strInformationSent}</h3>";
                echo "<p>{$strInformationSentRegardingSettingPassword}</p>";
                if (empty($email) AND !empty($contactid))
                {
                   echo "<p><a href='contact_details.php?id={$contactid}'>{$strContactDetails}</a></p>";
                }
                else
                {
                    echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
                }
            }
            else
            {
                echo "<h3>{$strInvalidEmailAddress}</h3>";
                echo "<p>".sprintf($strForFurtherAssistance, $CONFIG['support_email'])."</p>";
                echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
            }
        }
        include ('htmlfooter.inc.php');
        break;
    }

    case 'confirmreset':
    {
        include ('htmlheader.inc.php');
        if ($mode == 'user')
        {
            $sql = "SELECT id, username, password FROM `{$dbUsers}` WHERE id = '{$userid}' LIMIT 1";
        }
        elseif ($mode == 'contact')
        {
            $sql = "SELECT id, username, password FROM `{$dbContacts}` WHERE id = '{$contactid}' LIMIT 1";
        }
        $userresult = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        $usercount = mysql_num_rows($userresult);
        if ($usercount > 0)
        {
            $userdetails = mysql_fetch_object($userresult);
            $hash = md5($userdetails->username.'.'.$userdetails->password);

            if ($hash == $userhash)
            {
                echo "<h2>{$strResetPassword}</h2>";
                echo "<p align='center'>{$strPleaseConfirmUsername}</p>";
                echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";

                echo "<table class='vertical'>";
                echo "<tr><th>{$strUsername}</th>";
                echo "<td><input name='username' size='30' type='text' /></td></tr>";
                echo "</table>";
                echo "<p><input type='submit' value='{$strContinue}' /></p>";

                if ($mode == 'user')
                {
                    echo "<input type='hidden' name='userid' value='{$userid}' />";
                }
                elseif ($mode == 'contact')
                {
                    echo "<input type='hidden' name='contactid' value='{$contactid}' />";
                }
                echo "<input type='hidden' name='hash' value='{$userhash}' />";
                echo "<input type='hidden' name='action' value='resetpasswordform' />";
                echo "</form>";
            }
            else
            {
                echo "<h3>{$strError}</h3>";
                echo "<p>{$strDidYouPasteFullURL}</p>";
                echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
            }
        }
        else
        {
            echo "<h3>{$strError}</h3>";
            echo "<p>{$strDidYouPasteFullURL}</p>";
            echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
        }
        include ('htmlfooter.inc.php');
    break;
    }

    case 'resetpasswordform':
        include ('htmlheader.inc.php');
        if ($mode == 'user')
        {
            $sql = "SELECT id, username, password FROM `{$dbUsers}` WHERE id = '{$userid}' LIMIT 1";
        }
        elseif ($mode == 'contact')
        {
            $sql = "SELECT id, username, password FROM `{$dbContacts}` WHERE id = '{$contactid}' LIMIT 1";
        }

        $userresult = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        $usercount = mysql_num_rows($userresult);
        if ($usercount > 0)
        {
            $userdetails = mysql_fetch_object($userresult);
            $hash = md5($userdetails->username.'.'.$userdetails->password);
            if ($hash == $userhash AND $username==$userdetails->username)
            {
                $newhash = md5($userdetails->username.'.ok.'.$userdetails->password);
                echo "<h2>{$strSetPassword}</h2>";
                echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
                echo "<table align='center' class='vertical'>";
                echo "<tr class='password'><th>{$strNewPassword}:</th>";
                echo "<td><input maxlength='50' name='newpassword1' size='30' type='password' />";
                echo "</td></tr>";
                echo "<tr class='password'><th>{$strConfirmNewPassword}:</th>";
                echo "<td><input maxlength='50' name='newpassword2' size='30' type='password' />";
                echo "</td></tr>";
                echo "</table>";
                if ($mode == 'user')
                {
                    echo "<input type='hidden' name='userid' value='{$userid}' />";
                }
                elseif ($mode == 'contact')
                {
                    echo "<input type='hidden' name='contactid' value='{$contactid}' />";
                }
                echo "<input type='hidden' name='hash' value='{$newhash}' />";
                echo "<input type='hidden' name='action' value='savepassword' />";
                echo "<p><input type='submit' value='{$strSetPassword}' />";
                echo "</form>";
                echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
            }
            else
            {
                echo "<h3>{$strError}</h3>";
                echo "<p>Have you forgotten your username?  If so you should contact an administrator.</p>"; // FIXME i18n
                echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
            }
        }
        else
        {
            echo "<h3>{$strError}</h3>";
            echo "<p>{$strInvalidUserID}</p>";
            echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
        }
        include ('htmlfooter.inc.php');
    break;

    case 'savepassword':
        $newpassword1 = cleanvar($_REQUEST['newpassword1']);
        $newpassword2 = cleanvar($_REQUEST['newpassword2']);
        include ('htmlheader.inc.php');
        if ($mode == 'user')
        {
            $sql = "SELECT id, username, password FROM `{$dbUsers}` WHERE id = '{$userid}' LIMIT 1";
        }
        elseif ($mode == 'contact')
        {
            $sql = "SELECT id, username, password FROM `{$dbContacts}` WHERE id = '{$contactid}' LIMIT 1";
        }

        $userresult = mysql_query($sql);
        if (mysql_error()) trigger_error(mysql_error(),E_USER_WARNING);
        $usercount = mysql_num_rows($userresult);
        if ($usercount > 0)
        {
            $userdetails = mysql_fetch_object($userresult);
            $newhash = md5($userdetails->username.'.ok.'.$userdetails->password);
            if ($newhash == $userhash)
            {
                if ($newpassword1 == $newpassword2)
                {
                    if ($mode == 'user')
                    {
                        $usql = "UPDATE `{$dbUsers}` SET password=MD5('{$newpassword1}') WHERE id={$userid} LIMIT 1";
                    }
                    elseif ($mode == 'contact')
                    {
                        $usql = "UPDATE `{$dbContacts}` SET password=MD5('{$newpassword1}') WHERE id={$contactid} LIMIT 1";
                    }
                    mysql_query($usql);
                    echo "<h3>{$strPasswordReset}</h3>";
                    echo "<p>{$strPasswordHasBeenReset}</p>";
                    echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
                }
                else
                {
                    echo "<h3>{$strError}</h3>";
                    echo "<p>{$strPasswordsDoNotMatch}</p>";
                    echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
                }
            }
            else
            {
                echo "<h3>{$strError}</h3>";
                echo "<p>{$strInvalidDetails}</p>";
                echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
            }
        }
        else
        {
            echo "<h3>{$strError}</h3>";
            echo "<p>{$strInvalidUserID}</p>";
            echo "<p><a href='index.php'>{$strBackToLoginPage}</a></p>";
        }
        include ('htmlfooter.inc.php');
    break;

    case 'form':
    default:
        include ('htmlheader.inc.php');
        echo "<h2>{$title}</h2>";
        echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";

        echo "<table class='vertical'>";
        echo "<tr><th>{$strEmailAddress}</th><td><input name='emailaddress' size='30' type='text' /></td></tr>";
        echo "</table>";
        echo "<p><input type='submit' value='{$strContinue}' /></p>";
        echo "<input type='hidden' name='action' value='forgotpwd' />";
        echo "</form>";

        include ('htmlfooter.inc.php');
    break;
}

?>
