<?php
// add_user.php - Form for adding users
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

@include ('set_include_path.inc.php');
$permission=20; // Add Users

require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

$title = $strAddUser;

// External variables
$submit = $_REQUEST['submit'];

include ('htmlheader.inc.php');
?>
<script type="text/javascript">
function confirm_submit()
{
    return window.confirm('Are you sure you want to add this user?');
}
</script>

<?php
if (empty($submit))
{
    // Show add user form
    $gsql = "SELECT * FROM groups ORDER BY name";
    $gresult = mysql_query($gsql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    while ($group = mysql_fetch_object($gresult))
    {
        $grouparr[$group->id]=$group->name;
    }

    $numgroups = count($grouparr);

    echo show_form_errors('add_user');
    clear_form_errors('add_user');

    echo "<h2><img src='{$CONFIG['application_webpath']}images/icons/{$iconset}/32x32/user.png' width='32' height='32' alt='' /> ";
    echo "{$strNewUser}</h2>";
    echo "<h5>".sprintf($strMandatoryMarked,"<sup class='red'>*</sup>")."</h5>";
    echo "<form action='{$_SERVER['PHP_SELF']}' method='post' onsubmit='return confirm_submit();'>";
    echo "<table align='center' class='vertical'>\n";
    echo "<tr><th>{$strRealName} <sup class='red'>*</sup></th><td><input maxlength='50' name='realname' size='30'";
    if ($_SESSION['formdata']['add_user']['realname'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['realname']}'";
    echo "/></td></tr>\n";

    echo "<tr><th>{$strUsername} <sup class='red'>*</sup></th><td><input maxlength='50' name='username' size='30'";
    if ($_SESSION['formdata']['add_user']['username'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['username']}'";
    echo "/></td></tr>\n";

    echo "<tr id='password'><th>{$strPassword} <sup class='red'>*</sup></th><td><input maxlength='50' name='password' size='30'";
    if ($_SESSION['formdata']['add_user']['password'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['password']}'";
    echo "/></td></tr>\n";

    echo "<tr><th>{$strGroup}</th>";
    if ($_SESSION['formdata']['add_user']['groupid'] != "")
        echo "<td>".group_drop_down('groupid', $_SESSION['formdata']['add_user']['groupid'])."</td>";
    else
        echo "<td>".group_drop_down('groupid', 0)."</td>";
    echo "</tr>";

    echo "<tr><th>{$strRole}</th>";
    if ($_SESSION['formdata']['add_user']['roleid'] != "")
        echo "<td>".role_drop_down('roleid', $_SESSION['formdata']['add_user']['roleid'])."</td>";
    else
        echo "<td>".role_drop_down('roleid', 1)."</td>";
    echo "</tr>";

    echo "<tr><th>{$strJobTitle} <sup class='red'>*</sup></th><td><input maxlength='50' name='jobtitle' size='30'";
    if ($_SESSION['formdata']['add_user']['jobtitle'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['jobtitle']}'";
    echo "/></td></tr>\n";

    echo "<tr id='email'><th>{$strEmail} <sup class='red'>*</sup></th><td><input maxlength='50' name='email' size='30'";
    if ($_SESSION['formdata']['add_user']['email'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['email']}'";
    echo "/></td></tr>\n";

    echo "<tr><th>{$strTelephone}</th><td><input maxlength='50' name='phone' size='30'";
    if ($_SESSION['formdata']['add_user']['phone'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['phone']}'";
    echo "/></td></tr>\n";

    echo "<tr><th>{$strMobile}</th><td><input maxlength='50' name='mobile' size='30'";
    if ($_SESSION['formdata']['add_user']['mobile'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['mobile']}'";
    echo "/></td></tr>\n";

    echo "<tr><th>{$strFax}</th><td><input maxlength='50' name='fax' size='30'";
    if ($_SESSION['formdata']['add_user']['fax'] != "")
        echo "value='{$_SESSION['formdata']['add_user']['fax']}'";
    echo "/></td></tr>\n";

    echo "<tr><th>{$strHolidayEntitlement}</th><td><input maxlength='3' name='holiday_entitlement' size='3' ";
    if ($_SESSION['formdata']['add_user']['holiday_entitlement'] != "") echo "value='{$_SESSION['formdata']['add_user']['holiday_entitlement']}'";
    echo " /> {$strDays}</td></tr>\n";
    plugin_do('add_user_form');
    echo "</table>\n";
    echo "<p><input name='submit' type='submit' value=\"{$strAddUser}\" /></p>";
    echo "</form>\n";
    include ('htmlfooter.inc.php');

    clear_form_data('add_user');
}
else
{
    // External variables
    $username = mysql_real_escape_string(strtolower(trim(strip_tags($_REQUEST['username']))));
    $realname = cleanvar($_REQUEST['realname']);
    $password = mysql_real_escape_string($_REQUEST['password']);
    $groupid = cleanvar($_REQUEST['groupid']);
    $roleid = cleanvar($_REQUEST['roleid']);
    $jobtitle = cleanvar($_REQUEST['jobtitle']);
    $email = cleanvar($_REQUEST['email']);
    $phone = cleanvar($_REQUEST['phone']);
    $mobile = cleanvar($_REQUEST['mobile']);
    $fax = cleanvar($_REQUEST['fax']);
    $holiday_entitlement = cleanvar($_REQUEST['holiday_entitlement']);

    $_SESSION['formdata']['add_user'] = $_REQUEST;
    // Add user
    $errors = 0;
    // check for blank real name
    if ($realname == "")
    {
        $errors++;
        $_SESSION['formerrors']['add_user']['realname']= "You must enter a real name</p>\n";
    }
    // check for blank username
    if ($username == "")
    {
        $errors++;
        $_SESSION['formerrors']['add_user']['username']= "You must enter a username</p>\n";
    }
    // check for blank password
    if ($password == "")
    {
        $errors++;
        $_SESSION['formerrors']['add_user']['password']= "You must enter a password</p>\n";
    }
    // check for blank job title
    if ($jobtitle == "")
    {
        $errors++;
        $_SESSION['formerrors']['add_user']['jobtitle']= "You must enter a job title</p>\n";
    }
    // check for blank email
    if ($email == "")
    {
        $errors++;
        $_SESSION['formerrors']['add_user']['email']= "You must enter an email address</p>\n";
    }
    // Check username is unique
    $sql = "SELECT COUNT(id) FROM users WHERE username='$username'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    list($countexisting) = mysql_fetch_row($result);
    if ($countexisting >= 1)
    {
        $errors++;
        $_SESSION['formerrors']['add_user']['']= "Username must be unique</p>\n";
    }
    // Check email address is unique (discount disabled accounts)
    $sql = "SELECT COUNT(id) FROM users WHERE status > 0 AND email='$email'";
    $result = mysql_query($sql);
    if (mysql_error()) trigger_error(mysql_error(),E_USER_ERROR);
    list($countexisting) = mysql_fetch_row($result);
    if ($countexisting >= 1)
    {
        $errors++;
        $_SESSION['formerrors']['add_user']['duplicate_email'] = "Email must be unique</p>\n";
    }

    // add information if no errors
    if ($errors == 0)
    {
        $password=strtoupper(md5($password));
        $sql = "INSERT INTO users (username, password, realname, roleid, groupid, title, email, phone, mobile, fax, status, var_style, holiday_entitlement) ";
        $sql .= "VALUES ('$username', '$password', '$realname', '$roleid', '$groupid', '$jobtitle', '$email', '$phone', '$mobile', '$fax', 1, '{$CONFIG['default_interface_style']}', '$holiday_entitlement')";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $newuserid = mysql_insert_id();

        // Create permissions (set to none)
        $sql = "SELECT * FROM permissions";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        while ($perm = mysql_fetch_object($result))
        {
            $psql = "INSERT INTO userpermissions (userid, permissionid, granted) ";
            $psql .= "VALUES ('$newuserid', '{$perm->id}', 'false')";
            mysql_query($psql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        }

        plugin_do('user_created');

        if (!$result) echo "<p class='error'>Addition of user failed\n";
        else
        {
            journal(CFG_LOGGING_NORMAL, 'User Added', "User $username was added", CFG_JOURNAL_ADMIN, $id);
            html_redirect("manage_users.php#userid{$newuserid}");
        }
        clear_form_data('add_user');
        clear_form_errors('add_user');
    }
    else
        html_redirect("add_user.php", FALSE);
}
?>
