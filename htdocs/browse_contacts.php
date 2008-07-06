<?php
// browse_contacts.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

// TODO This page fails XHTML validation because of dojo attributes - INL 12/12/07

@include ('set_include_path.inc.php');
$permission = 12; // View Contacts

require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

$title = $strBrowseContacts;

// External variables
$search_string = cleanvar($_REQUEST['search_string']);
$submit_value = cleanvar($_REQUEST['submit']);
$displayinactive = cleanvar($_REQUEST['displayinactive']);
if (empty($displayinactive)) $displayinactive = "false";

if ($submit_value == 'go')
{
        // build SQL
        $sql  = "SELECT * FROM `{$dbContacts}` ";
        $search_string_len=strlen($search_string);
        if ($search_string != '*')
        {
            $sql .= "WHERE ";
            if ($search_string_len<=6) $sql .= "id=('$search_string') OR ";
            if ($search_string_len<=2)
            {
                $sql .= "SUBSTRING(surname,1,$search_string_len)=('$search_string') ";
            }
            else
            {
                $sql .= "surname LIKE '%$search_string%' OR forenames LIKE '%$search_string%' OR ";
                $sql .= "CONCAT(forenames,' ',surname) LIKE '%$search_string%'";
            }
        }
        $sql .= " ORDER BY surname ASC, forenames ASC";

        // execute query
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);

        if (mysql_num_rows($result) == 1)
        {
            //go straight to the contact
            $row = mysql_fetch_array($result);
            $url = "contact_details.php?id=".$row["id"];
            header("Location: $url");
        }
}
$pagescripts = array('dojo/dojo.js');
include ('htmlheader.inc.php');

if ($search_string=='') $search_string='a';
?>
<script type="text/javascript">
//<![CDATA[
function contact_products_window(contactid)
{
    URL = "contact_products.php?id=" + contactid;
    window.open(URL, "contact_products_window", "toolbar=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=520,height=240");
}

dojo.require ("dojo.widget.ComboBox");

//]]>
</script>

<?php
echo "<h2>".icon('contact', 32)." ";
echo "{$strBrowseContacts}</h2>";
?>
<table summary="alphamenu" align="center">
<tr>
<td align="center">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
    <!-- <input type="text" name="search_string" />-->
    <?php
    echo "<p>{$strBrowseContacts}: "; ?>
    <input dojoType='ComboBox' dataUrl='autocomplete.php?action=contact' style='width: 300px;' name='search_string' />
    <?php echo "<input name='submit' type='submit' value=\"{$strGo}\" /></p>";
    echo "</form>\n";
        if ($displayinactive=="true")
        {
            echo "<a href='".$_SERVER['PHP_SELF']."?displayinactive=false";
            if (!empty($search_string)) echo "&amp;search_string={$search_string}";
            echo "'>{$strShowActiveOnly}</a>";
            $inactivestring="displayinactive=true";
        }
        else
        {
            echo "<a href='".$_SERVER['PHP_SELF']."?displayinactive=true";
            if (!empty($search_string)) echo "&amp;search_string={$search_string}";
            echo "'>Show inactive</a>";
            $inactivestring="displayinactive=false";
        }
    ?>
</td>
</tr>
<tr>
<td valign="middle">
    <?php echo "<a href='add_contact.php'>{$strAdd}</a> | "; ?>
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=A&amp;<?php echo $inactivestring; ?>">A</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=B&amp;<?php echo $inactivestring; ?>">B</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=C&amp;<?php echo $inactivestring; ?>">C</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=D&amp;<?php echo $inactivestring; ?>">D</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=E&amp;<?php echo $inactivestring; ?>">E</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=F&amp;<?php echo $inactivestring; ?>">F</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=G&amp;<?php echo $inactivestring; ?>">G</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=H&amp;<?php echo $inactivestring; ?>">H</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=I&amp;<?php echo $inactivestring; ?>">I</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=J&amp;<?php echo $inactivestring; ?>">J</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=K&amp;<?php echo $inactivestring; ?>">K</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=L&amp;<?php echo $inactivestring; ?>">L</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=M&amp;<?php echo $inactivestring; ?>">M</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=N&amp;<?php echo $inactivestring; ?>">N</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=O&amp;<?php echo $inactivestring; ?>">O</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=P&amp;<?php echo $inactivestring; ?>">P</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=Q&amp;<?php echo $inactivestring; ?>">Q</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=R&amp;<?php echo $inactivestring; ?>">R</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=S&amp;<?php echo $inactivestring; ?>">S</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=T&amp;<?php echo $inactivestring; ?>">T</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=U&amp;<?php echo $inactivestring; ?>">U</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=V&amp;<?php echo $inactivestring; ?>">V</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=W&amp;<?php echo $inactivestring; ?>">W</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=X&amp;<?php echo $inactivestring; ?>">X</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=Y&amp;<?php echo $inactivestring; ?>">Y</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?search_string=Z&amp;<?php echo $inactivestring; ?>">Z</a> |
    <?php echo "<a href='{$_SERVER['PHP_SELF']}?search_string=*&amp;{$inactivestring}'>{$strAll}</a>"; ?>
    </td>
</tr>
</table>
<?php

if (empty($search_string))
{
    throw_error('No Search String','');
}
else
{
    // perform search
    // check input
    if ($search_string == '')
    {
        $errors = 1;
        echo "<p class='error'>You must enter a search string</p>\n";
    }
    // search for criteria
    if ($errors == 0)
    {
        if ($submit_value != 'go')
        {
            // Don't  need to do this again, already done above, us the results of that
            // build SQL
            $sql  = "SELECT c.* FROM `{$dbContacts}` AS c, `{$dbSites}` AS s ";
            $sql .= "WHERE c.siteid = s.id ";
            $search_string_len=strlen($search_string);
            if ($search_string != '*')
            {
                $sql .= " AND (";
                if ($search_string_len<=6) $sql .= "c.id=('$search_string') OR ";
                if ($search_string_len<=2)
                {
                    $sql .= "SUBSTRING(c.surname,1,$search_string_len)=('$search_string') ";
                }
                else
                {
                    $sql .= "c.surname LIKE '%$search_string%' OR c.forenames LIKE '%$search_string%' OR ";
                    $sql .= "CONCAT(c.forenames,' ',c.surname) LIKE '%$search_string%'";
                }
                $sql .= " ) ";
            }
            if ($displayinactive=="false")
            {
                $sql .= " AND c.active = 'true' AND s.active = 'true'";
            }
            $sql .= " ORDER BY surname ASC";

            // execute query
            $result = mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_WARNING);
        }

        if (mysql_num_rows($result) == 0)
            echo "<p align='center'>Sorry, unable to find any contacts matching <em>'$search_string</em>'</p>\n";
        else
        {
            ?>
            <p align='center'>Displaying <?php echo mysql_num_rows($result) ?> contact(s) matching <em>'<?php echo $search_string; ?>'</em></p>
            <?php
            echo "<table align='center'>
            <tr>
            <th>{$strName}</th>
            <th>{$strSite}</th>
            <th>{$strEmail}</th>
            <th>{$strTelephone}</th>
            <th>{$strFax}</th>
            <th>{$strAddIncident}</th>
            </tr>";
            $shade = 0;
            while ($results = mysql_fetch_array($result))
            {
                // define class for table row shading
                if ($shade) $class = "shade1";
                else $class = "shade2";
                if ($results['active'] == 'false') $class='expired';
                ?>
                <tr class='<?php echo $class ?>'>
                    <td><a href="contact_details.php?id=<?php echo $results["id"] ?>" ><?php echo $results['surname'].', '.$results['forenames']; ?></a></td>
                    <td><a href="site_details.php?id=<?php echo $results['siteid'] ?>"><?php echo site_name($results['siteid']) ?></a></td>
                    <td><?php echo $results["email"]; ?></td>
                    <td><?php if ($results["phone"] == '') { echo "<em>{$strNone}</em>"; } else { echo $results["phone"]; } ?></td>
                    <td><?php if ($results["fax"] == '') { echo "<em>{$strNone}</em>"; } else { echo $results["fax"]; } ?></td>
                    <?php echo "<td><a href='add_incident.php?action=findcontact&amp;contactid={$results['id']}'>{$strAddIncident}</a></td>"; ?>
                </tr>
                <?php
                // invert shade
                if ($shade == 1) $shade = 0;
                else $shade = 1;
            }
            ?>
            </table>
            <?php
        }
        // free result
        mysql_free_result($result);
    }
}
include ('htmlfooter.inc.php');
?>
