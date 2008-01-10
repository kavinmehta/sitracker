<?php
// edit_global_signature.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// This Page Is Valid XHTML 1.0 Transitional!   4Nov05

// Authors: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
//          Paul Heaney <paulheaney[at]users.sourceforge.net>


@include('set_include_path.inc.php');

function get_globalsignature($sig_id)
{
    $sql = "SELECT signature FROM emailsig WHERE id = $sig_id";
    $result=mysql_query($sql);
    list($signature)=mysql_fetch_row($result);
    mysql_free_result($result);
    return $signature;
}

function delete_signature($sig_id)
{
    $sql = "DELETE FROM emailsig WHERE id = $sig_id";
    mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

    journal(CFG_LOGGING_NORMAL, 'Global Signature deleted', "A global signature was deleted", CFG_JOURNAL_ADMIN, 0);
    html_redirect("edit_global_signature.php");
}

$permission=43; // Edit global signature


require('db_connect.inc.php');
require('functions.inc.php');
// This page requires authentication
require('auth.inc.php');

$title = $strGlobalSignature;

// External variables
$action = cleanvar($_REQUEST['action']);
$sig_id = cleanvar($_REQUEST['sig_id']);
$signature = cleanvar($_REQUEST['signature']);
$formaction = cleanvar($_REQUEST['formaction']);

if(!empty($signature))
{
    //we've been passed a signature - ie we must either be deleting or editing on actual signature
    switch($formaction)
    {
        case 'add':
            //then we're adding a new signature
            $sql = "INSERT INTO emailsig (signature) VALUES ('$signature') ";
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            journal(CFG_LOGGING_NORMAL, 'Global Signature added', "A new global signature was added", CFG_JOURNAL_ADMIN, 0);
            html_redirect("edit_global_signature.php");
        break;

        case 'edit':
            $sql = "UPDATE emailsig SET signature = '$signature' WHERE id = ".$sig_id;
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            journal(CFG_LOGGING_NORMAL, 'Global Signature updated', "A global signature was updated", CFG_JOURNAL_ADMIN, 0);
            html_redirect("edit_global_signature.php");
      break;
  }

}
elseif(empty($action))
{
    //The just view the global signatures
    include('htmlheader.inc.php');

    echo "<h2>{$title}</h2>";

    $sql = "SELECT id, signature FROM emailsig ORDER BY id ASC";
    $result = mysql_query($sql);
    if(mysql_error()) trigger_error(mysql_error(), E_USER_WARNING);

    echo "<p align='center'>{$strOneOfTheSignaturesWillBeInserted}<br /><br />";
    echo "{$strGlobalSignatureRemember}</p>";

    echo "<p align='center'><a href='edit_global_signature.php?action=add'>{$strAdd}</a></p>";

    echo "<table align='center' width='60%'>";
    echo "<tr><th>{$strGlobalSignature}</th><th>{$strOperation}</th></tr>";
    while($signature = mysql_fetch_array($result))
    {
        $id = $signature['id'];
        echo "<tr>";
        echo "<td class='shade1' width='70%'>".ereg_replace("\n", "<br />", $signature['signature'])."</td>";
        echo "<td class='shade2' align='center'><a href='edit_global_signature.php?action=edit&amp;sig_id=$id'>{$strEdit}</a> | ";
        echo "<a href='edit_global_signature.php?action=delete&amp;sig_id=$id'>{$strDelete}</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    include('htmlfooter.inc.php');
}
elseif(!empty($action))
{
    include('htmlheader.inc.php');
    switch($action)
    {
        case 'add':
            echo "<h2>{$strGlobalSignature}: {$strAdd}</h2>";
            echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
            echo "<input type='hidden' name='formaction' value='add' />";
            echo "<table class='vertical' width='50%'>";
            echo "<tr>";

            echo "<td align='right' valign='top' class='shade1'><strong>{$strGlobalSignature}</strong>:<br />\n";
            echo "{$strGlobalSignatureDescription}<br /><br />";
            echo "$strGlobalSignatureRemember";
            echo "</td>";

            echo "<td class='shade1'><textarea name='signature' rows='15' cols='65'></textarea></td>";
            echo "</tr>";
            echo "</table>";
            echo "<p align='center'><input name='submit' type='submit' value=\"{$strAdd}\" /></p>";
            echo "</form>\n";
        break;

        case 'delete':
            delete_signature($sig_id);
        break;

        case 'edit':
            echo "<h2>{$strGlobalSignature}: {$strEdit}</h2>";
            ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="hidden" name="formaction" value="edit" />
            <input type="hidden" name="sig_id" value="<?php echo $sig_id ?>" />
            <table class='vertical' width='50%'>
            <tr>
            <?php
            echo "<td align='right' valign='top' class='shade1'><strong>{$strGlobalSignature}</strong>:<br />\n";
            echo "{$strGlobalSignatureDescription}<br /><br />";
            echo "$strGlobalSignatureRemember";
            ?>
            </td>
            <td class="shade1"><textarea name="signature" rows="15" cols="65"><?php echo get_globalsignature($sig_id); ?></textarea></td>
            </tr>
            </table>
            <?php
            echo "<p align='center'><input name='submit' type='submit' value=\"{$strSave}\" /></p>";
            echo "</form>\n";
        break;
    }
    include('htmlfooter.inc.php');
}
?>
