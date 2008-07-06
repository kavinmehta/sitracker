<?php
// edit_contact.php - Form for editing a contact
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// This Page Is Valid XHTML 1.0 Transitional!  31Oct05
@include ('set_include_path.inc.php');
$permission = 10; // Edit Contacts

require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

$title = $strEditContact;

// External variables
$contact = cleanvar($_REQUEST['contact']);
$action = cleanvar($_REQUEST['action']);

include ('htmlheader.inc.php');

// User has access
if (empty($action) OR $action == "showform" OR empty($contact))
{
    // Show select contact form
    echo "<h2>".icon('contact', 32)." {$strEditContact}</h2>";
    echo "<form action='{$_SERVER['PHP_SELF']}?action=edit' method='post'>";
    echo "<table align='center'>";
    echo "<tr><th>{$strContact}:</th><td>".contact_site_drop_down("contact", 0)."</td></tr>";
    echo "</table>";
    echo "<p align='center'><input name='submit' type='submit' value='{$strContinue}' /></p>";
    echo "</form>\n";
}
elseif ($action == "edit" && isset($contact))
{
    // FIMXE i18n
    // Show edit contact form
    $sql="SELECT * FROM `{$dbContacts}` WHERE id='$contact' ";
    $contactresult = mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
    while ($contactrow=mysql_fetch_array($contactresult))
    {
        // User does not have access
        echo "<h2>".icon('contact', 32)." ";
        echo "{$strEditContact}: {$contact}</h2>";
        echo "<form name='contactform' action='{$_SERVER['PHP_SELF']}?action=update' method='post' onsubmit='return confirm_action(\"{$strAreYouSureMakeTheseChanges}\");'>";
        echo "<p align='center'>".sprintf($strMandatoryMarked, "<sup class='red'>*</sup>")."</p>";
        echo "<table align='center' class='vertical'>";
        echo "<tr><th>{$strName}: <sup class='red'>*</sup><br />{$strTitle}, {$strForenames}, {$strSurname}</th>";
        echo "<td><input maxlength='50' name='courtesytitle' title='Courtesy Title (Mr, Mrs, Miss, Dr. etc.)' size='7' value='{$contactrow['salutation']}' />\n"; // i18n courtesy title
        echo "<input maxlength='100' name='forenames' size='15' title='Firstnames (or initials)' value='{$contactrow['forenames']}' />\n";
        echo "<input maxlength='100' name='surname' size='20' title='{$strSurname}' value='{$contactrow['surname']}' />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strTags}:</th><td><textarea rows='2' cols='60' name='tags'>";
        echo list_tags($contact, TAG_CONTACT, false)."</textarea></td></tr>\n";
        echo "<tr><th>{$strJobTitle}:</th><td>";
        echo "<input maxlength='255' name='jobtitle' size='40' value=\"{$contactrow['jobtitle']}\" />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strSite}: <sup class='red'>*</sup></th><td>";
        echo site_drop_down('siteid', $contactrow['siteid'])."</td></tr>\n";
        echo "<tr><th>{$strDepartment}:</th><td>";
        echo "<input maxlength='100' name='department' size='40' value='{$contactrow['department']}' />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strEmail}: <sup class='red'>*</sup></th><td>";
        echo "<input maxlength='100' name='email' size='40' value='{$contactrow['email']}' />";
        echo "<label>";
        html_checkbox('dataprotection_email', $contactrow['dataprotection_email']);
        echo "{$strEmail} {$strDataProtection}</label>";
        echo "</td></tr>\n";
        echo "<tr><th>{$strTelephone}:</th><td>";
        echo "<input maxlength='50' name='phone' size='40' value='{$contactrow['phone']}' />";
        echo "<label>";
        html_checkbox('dataprotection_phone', $contactrow['dataprotection_phone']);
        echo "{$strTelephone} {$strDataProtection}</label>";
        echo "</td></tr>\n";
        echo "<tr><th>{$strMobile}:</th><td>";
        echo "<input maxlength='50' name='mobile' size='40' value='{$contactrow['mobile']}' /></td></tr>\n";
        echo "<tr><th>{$strFax}:</th><td>";
        echo "<input maxlength='50' name='fax' size='40' value='{$contactrow['fax']}' /></td></tr>\n";
        echo "<tr><th>{$strActive}:</th><td><input type='checkbox' name='active' ";
        if ($contactrow['active'] == 'true') echo "checked='checked'";
        echo " value='true' /></td></tr> <tr><th></th><td>";
        echo "<input type='checkbox' name='usesiteaddress' value='yes' onclick='togglecontactaddress();' ";
        if ($contactrow['address1'] !='')
        {
            echo "checked='checked'";
            $extraattributes = '';
        }
        else
        {
          $extraattributes = "disabled='disabled' ";
        }
        echo "/> ";
        echo "{$strSpecifyAddress}</td></tr>\n";
        echo "<tr><th>{$strAddress}:</th><td><label>";
        html_checkbox('dataprotection_address', $contactrow['dataprotection_address']);
        echo " {$strAddress} {$strDataProtection}</label></td></tr>\n";
        echo "<tr><th>{$strAddress1}:</th><td>";
        echo "<input maxlength='255' name='address1' size='40' value='{$contactrow['address1']}' {$extraattributes} />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strAddress2}:</th><td>";
        echo "<input maxlength='255' name='address2' size='40' value='{$contactrow['address2']}' {$extraattributes} />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strCity}:</th><td>";
        echo "<input maxlength='255' name='city' size='40' value='{$contactrow['city']}' {$extraattributes} />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strCounty}:</th><td>";
        echo "<input maxlength='255' name='county' size='40' value='{$contactrow['county']}' {$extraattributes} />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strPostcode}:</th><td>";
        echo "<input maxlength='255' name='postcode' size='40' value='{$contactrow['postcode']}' {$extraattributes} />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strCountry}:</th><td>";
        echo country_drop_down('country', $contactrow['country'], $extraattributes);
        echo "</td></tr>\n";
        echo "<tr><th>{$strNotifyContact}:</th><td>";
        echo contact_site_drop_down('notify_contactid', $contactrow['notify_contactid'], $contactrow['siteid'], $contact);
        echo "</td></tr>\n";
        echo "<tr><th>{$strNotes}:</th><td>";
        echo "<textarea rows='5' cols='60' name='notes'>{$contactrow['notes']}</textarea></td></tr>\n";

        plugin_do('edit_contact_form');
        echo "</table>";

        echo "<input name='contact' type='hidden' value='{$contact}' />";

        echo "<p align='center'><input name='submit' type='submit' value='{$strSave}' /></p>";
        echo "</form>\n";
    }
}
else if ($action == "update")
{
    // External variables
    $contact = cleanvar($_POST['contact']);
    $courtesytitle = cleanvar($_POST['courtesytitle']);
    $surname = cleanvar($_POST['surname']);
    $forenames = cleanvar($_POST['forenames']);
    $siteid = cleanvar($_POST['siteid']);
    $email = strtolower(cleanvar($_POST['email']));
    $phone = cleanvar($_POST['phone']);
    $mobile = cleanvar($_POST['mobile']);
    $fax = cleanvar($_POST['fax']);
    $address1 = cleanvar($_POST['address1']);
    $address2 = cleanvar($_POST['address2']);
    $city = cleanvar($_POST['city']);
    $county = cleanvar($_POST['county']);
    $postcode = cleanvar($_POST['postcode']);
    $country = cleanvar($_POST['country']);
    $notes = cleanvar($_POST['notes']);
    $dataprotection_email = cleanvar($_POST['dataprotection_email']);
    $dataprotection_address = cleanvar($_POST['dataprotection_address']);
    $dataprotection_phone = cleanvar($_POST['dataprotection_phone']);
    $active = cleanvar($_POST['active']);
    $jobtitle = cleanvar($_POST['jobtitle']);
    $department = cleanvar($_POST['department']);
    $notify_contactid = cleanvar($_POST['notify_contactid']);
    $tags = cleanvar($_POST['tags']);

    // Save changes to database
    $errors = 0;

    // VALIDATION CHECKS */

    // check for blank name
    if ($surname == '')
    {
        $errors = 1;
        echo "<p class='error'>{$strMustEnterSurname}</p>\n";
    }
    // check for blank site
    if ($siteid == '')
    {
        $errors = 1;
        echo "<p class='error'>{$strMustEnterSiteName}</p>\n";
    }
    // check for blank name
    if ($email == '' OR $email=='none' OR $email=='n/a')
    {
        $errors = 1;
        echo "<p class='error'>{$strMustEnterEmail}</p>\n";
    }
    // check for blank contact id
    if ($contact == '')
    {
        $errors = 1;
        echo "<p class='error'>Something weird has happened, better call technical support</p>\n";
    }

    // edit contact if no errors
    if ($errors == 0)
    {
        // update contact
        if ($dataprotection_email != '') $dataprotection_email='Yes'; else $dataprotection_email='No';
        if ($dataprotection_phone  != '') $dataprotection_phone='Yes'; else $dataprotection_phone='No';
        if ($dataprotection_address  != '') $dataprotection_address='Yes'; else $dataprotection_address='No';

        if ($active=='true') $activeStr = 'true';
        else $activeStr = 'false';

        /*
            TAGS
        */
        replace_tags(1, $contact, $tags);

        $sql = "UPDATE `{$dbContacts}` SET courtesytitle='$courtesytitle', surname='$surname', forenames='$forenames', siteid='$siteid', email='$email', phone='$phone', mobile='$mobile', fax='$fax', ";
        $sql .= "address1='$address1', address2='$address2', city='$city', county='$county', postcode='$postcode', ";
        $sql .= "country='$country', dataprotection_email='$dataprotection_email', dataprotection_phone='$dataprotection_phone', ";
        $sql .= "notes='$notes', dataprotection_address='$dataprotection_address' , department='$department' , jobtitle='$jobtitle', ";
        $sql .= "notify_contactid='$notify_contactid', ";
        $sql .= "active = '{$activeStr}', ";
        $sql .= "timestamp_modified=$now WHERE id='$contact'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

        if (!$result) throw_error('Update of contact failed:',$sql);
        else
        {
            plugin_do('save_contact_form');

            journal(CFG_LOGGING_NORMAL, 'Contact Edited', "Contact {$contact} was edited", CFG_JOURNAL_CONTACTS, $contact);
            html_redirect("contact_details.php?id={$contact}");
            exit;
        }
    }
}
include ('htmlfooter.inc.php');
?>
