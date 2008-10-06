<?php
// ftp_upload_file.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2008 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//
// FIXME needs i18n
// TODO HTML to PHP

// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>
@include ('set_include_path.inc.php');
$permission = 44; // ftp publishing
require ('db_connect.inc.php');
require ('functions.inc.php');

// This page requires authentication
require ('auth.inc.php');

// External variables
$file = cleanvar($_REQUEST['file']);
$action = cleanvar($_REQUEST['action']);

$max_filesize = return_bytes($CONFIG['upload_max_filesize']);


if (empty($action))
{
    include ('htmlheader.inc.php');
 
    echo "<h2>Upload Public File</h2>";
    ?>
    <p align='center'>IMPORTANT: Files published here are <strong>public</strong> and available to all ftp users.</p>
    <form name="publishform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <table class='vertical'>
    <tr><th>File <small>(&lt;<?php echo readable_file_size($max_filesize); ?>)</small>:</th>
    <td class='shade2'><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_filesize; ?>" />
    <input type="file" name="file" size="40" /></td></tr>

    <tr><th>Title:</th><td><input type="text" name="shortdescription" maxlength="255" size="40" /></td></tr>
    <tr><th>Description:</th><td><textarea name="longdescription" cols="40" rows="3"></textarea></td></tr>
    <tr><th>File Version:</th><td><input type="text" name="fileversion" maxlength="50" size="10" /></td></tr>
    <tr><th>Expire:</th><td>
    <input type="radio" name="expiry_none" value="time" /> In <em>x</em> days, hours, minutes<br />&nbsp;&nbsp;&nbsp;
    <input maxlength="3" name="expiry_days" value="<?php echo $na_days ?>" onclick="window.document.publishform.expiry_none[0].checked = true;" size="3" /> Days&nbsp;
    <input maxlength="2" name="expiry_hours" value="<?php echo $na_hours ?>" onclick="window.document.publishform.expiry_none[0].checked = true;" size="3" /> Hours&nbsp;
    <input maxlength="2" name="expiry_minutes" value="<?php echo $na_minutes ?>" onclick="window.document.publishform.expiry_none[0].checked = true;" size="3" /> Minutes<br />
    <input type="radio" name="expiry_none" value="date" />On specified Date<br />&nbsp;&nbsp;&nbsp;
    <?php
    // Print Listboxes for a date selection
    echo "<select name='day' onclick=\"window.document.publishform.expiry_none[1].checked = true;\">";
    
    for ($t_day=1;$t_day<=31;$t_day++)
    {
        echo "<option value=\"{$t_day}\" ";
        if ($t_day == date("j"))
        {
            echo "selected='selected'";
        }
        echo ">$t_day</option>\n";
    }
    
    echo "</select><select name='month' onclick=\"window.document.publishform.expiry_none[1].checked = true;\">";
  
    for ($t_month = 1; $t_month <= 12; $t_month++)
    {
        echo "<option value=\"{$t_month}\"";
        if ($t_month == date("n"))
        {
            echo " selected='selected'";
        }
        echo ">". date ("F", mktime(0,0,0,$t_month,1,2000)) ."</option>\n";
    }
    
    echo "</select><select name='year' onclick=\"window.document.publishform.expiry_none[1].checked = true;\">";
    
    for ($t_year = (date("Y")-1); $t_year <= (date("Y")+5); $t_year++)
    {
        echo "<option value=\"{$t_year}\" ";
        if ($t_year == date("Y"))
        {
            echo "selected='selected'";
        }
        echo ">$t_year</option>\n";
    }
    echo "</select>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "<p align='center'><input type='submit' value='{$strPublish}' />";
    echo "<input type='hidden' name='action' value='publish' /></p>";
    echo "<p align='center'><a href='ftp_list_files.php'>{$strBackToList}</a></p>";
    echo "</form>";
    
    include ('htmlfooter.inc.php');
}
else
{
//     echo "<pre>".print_r($_REQUEST,true)."</pre>";
//     echo "<pre>".print_r($_FILES,true)."</pre>";

    // TODO v3.2x ext variables
    $file_name = $_FILES['file']['name'];

    $shortdescription = cleanvar($_REQUEST['shortdescription']);
    $longdescription = cleanvar($_REQUEST['longdescription']);
    $fileversion = cleanvar($_REQUEST['fileversion']);

    $expirytype = cleanvar($_REQUEST['expiry_none']);
    
   
    if ($expirytype == 'time')
    {
        $days = cleanvar($_REQUEST['expiry_days']);
        $hours = cleanvar($_REQUEST['expiry_hours']);
        $minutes = cleanva($_REQUEST['expiry_minutes']);
        
        if ($days < 1 && $hours < 1 && $minutes < 1)
        {
            $expirydate = 0;
        }
        else
        {
            $expirydate = calculate_time_of_next_action($days, $hours, $minutes);
        }
    }
    elseif ($expirytype == 'date')
    {
        $day = cleanvar($_REQUEST['day']);
        $month = cleanvar($_REQUEST['month']);
        $year = cleanvar($_REQUEST['year']);
        
        $date = explode("-", $date);
        $expirydate = mktime(0, 0, 0, $month, $day, $year);
    }
    else
    {
        $expirydate = 0;
    }

    // receive the uploaded file to a temp directory on the local server
    if ($_FILES['file']['error'] != '' AND $_FILES['file']['error'] != UPLOAD_ERR_OK)
    {
        echo get_file_upload_error_message($_FILES['file']['error'], $_FILES['file']['name']);
    }
    else
    {
        $filepath = $CONFIG['attachment_fspath'].$file_name;
        $mv = move_uploaded_file($_FILES['file']['tmp_name'], $filepath);
        if (!mv) trigger_error("Problem moving uploaded file from temp directory: {$filepath}", E_USER_WARNING);

        if (!file_exists($filepath)) trigger_error("Error the temporary upload file ($file) was not found at: {$filepath}", E_USER_WARNING);

        // Check file size
        $filesize = filesize($filepath);
        if ($filesize > $CONFIG['upload_max_filesize'])
        {
            trigger_error("User Error: Attachment too large or file ('.$file.') upload error - size: ".filesize($filepath), E_USER_WARNING);
            // throwing an error isn't the nicest thing to do for the user but there seems to be no way of
            // checking file sizes at the client end before the attachment is uploaded. - INL
        }
        if ($filesize == FALSE) trigger_error("Error handling uploaded file: {$file}", E_USER_WARNING);

        // set up basic connection
        $conn_id = create_ftp_connection();
        
        $destination_filepath = $CONFIG['ftp_path'] . $file_name;

        // check the source file exists
        if (!file_exists($filepath)) trigger_error("Source file cannot be found: {$filepath}", E_USER_WARNING);

        // set passive mode if required
        if (!ftp_pasv($conn_id, $CONFIG['ftp_pasv'])) trigger_error("Problem setting passive ftp mode", E_USER_WARNING);

        // upload the file
        $upload = ftp_put($conn_id, "$destination_filepath", "$filepath", FTP_BINARY);

        // check upload status
        if (!$upload)
        {
            trigger_error("FTP upload has failed!",E_USER_ERROR);
        }
        else
        {
            // store file details in database
            // important: path must be blank for public files (all go in same dir)
            $sql = "INSERT INTO `{$dbFiles}` (filename, size, userid, shortdescription, longdescription, path, filedate, expiry, fileversion) ";
            $sql .= "VALUES ('$file_name', '$filesize', '".$sit[2]."', '$shortdescription', '$longdescription', '{$CONFIG['ftp_path']}', '$now', '$expirydate' ,'$fileversion')";
            mysql_query($sql);
            if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);

            journal(CFG_LOGGING_NORMAL, 'FTP File Uploaded', "FTP File $file_name Uploaded", CFG_JOURNAL_OTHER, 0);

            html_redirect('ftp_upload_file.php');
            echo "<code>{$ftp_url}</code>";
        }

        // close the FTP stream
        ftp_close($conn_id);
    }

}
?>
