<?php
// ftp_edit_file.php -
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

// This Page Is Valid XHTML 1.0 Transitional!   4Nov05

$permission=44; // Publish Files to FTP site

$title='Edit FTP File Details and Publish';
require('db_connect.inc.php');
require('functions.inc.php');

// This page requires authentication
require('auth.inc.php');

// External Vars
$id = cleanvar($_REQUEST['id']);
$mode = cleanvar($_REQUEST['mode']);
if (empty($mode)) $mode='form';

switch ($mode)
{
    case 'form':
        // display file details
        include('htmlheader.inc.php');
        $sql = "SELECT * FROM files WHERE id='$id'";
        $result = mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        $frow=mysql_fetch_array($result);

        // calculate filesize
        $j = 0;
        $ext =
        array("Bytes","KBytes","MBytes","GBytes","TBytes");
        $pretty_file_size = $frow['size'];
        while ($pretty_file_size >= pow(1024,$j)) ++$j;
        $pretty_file_size = round($pretty_file_size / pow(1024,$j-1) * 100) / 100 . ' ' . $ext[$j-1];

        echo "<h2>$title</h2>";
        echo "<form action='{$_SERVER['PHP_SELF']}' method='post'>";
        echo "<table summary='edit file details' align='center' width='60%' class='vertical'>";
        echo "<tr><th>File:</th><td>";
        echo "<img src='".getattachmenticon($frow['filename'])."' alt='{$frow['filename']} ({$pretty_file_size})' border='0' />";
        echo "<strong>{$frow['filename']}</strong> ({$pretty_file_size})</td></tr>";
        if ($frow['path']=='') $ftp_path=$CONFIG['ftp_path']; else $ftp_path=$CONFIG['ftp_path'].substr($frow['path'],1).'/';

        echo "<tr><th>Location:</th><td><a href=\"ftp://{$CONFIG['ftp_hostname']}{$ftp_path}{$frow['filename']}\"><code>";
        echo "ftp://{$CONFIG['ftp_hostname']}{$ftp_path}{$frow['filename']}</code></a></td></tr>\n";
        echo "<tr><th>Title:</th><td>";
        echo "<input type='text' size='40' name='shortdescription' value='".$frow['shortdescription']."' />";
        echo "</td></tr>\n";
        echo "<tr><th>Web Category:</th><td>";
        echo "<input type='text' size='40' name='webcategory' value='".$frow['webcategory']."' />";
        echo "</td></tr>\n";
        echo "<tr><th>{$strDescription}:</th><td>";
        echo "<textarea rows='6' cols='40' name='longdescription'>{$frow['longdescription']}</textarea>";
        echo "</td></tr>\n";
        echo "<tr><th>File Version:</th><td>";
        echo "<input type='text' size='40' name='fileversion' value='{$frow['fileversion']}' />";
        echo "</td></tr>\n";
        echo "<tr><th>File Date:</th><td>".date('D jS M Y @ g:i A',$frow['filedate'])." <strong>by</strong> ".user_realname($frow['userid'],TRUE). "</td></tr>\n";

        if ($frow['expiry']>0) echo "<tr><th>Expiry:</th><td>".date('D jS M Y @ g:i A',$frow['expiry'])." </td></tr>\n";

        echo "</table>\n\n";
        echo "<input type='hidden' name='id' value='{$id}' />";
        echo "<input type='hidden' name='mode' value='save' />";
        echo "<p align='center'><input type='submit' value='Save &amp; Publish' /></p>";
        echo "</form>";
        include('htmlfooter.inc.php');
    break;

    case 'save':
        $shortdescription=mysql_real_escape_string($_REQUEST['shortdescription']);
        $longdescription=mysql_real_escape_string($_REQUEST['longdescription']);
        $fileversion=mysql_real_escape_string($_REQUEST['fileversion']);
        $webcategory=mysql_real_escape_string($_REQUEST['webcategory']);
        $sql = "UPDATE files SET ";
        $sql .= "shortdescription='$shortdescription', longdescription='$longdescription', fileversion='$fileversion', ";
        $sql .= "webcategory='$webcategory', published='yes'";
        $sql .= " WHERE id='{$id}' LIMIT 1";
        mysql_query($sql);
        if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
        header("Location: ftp_list_files.php");
        exit;
    break;
}
?>
