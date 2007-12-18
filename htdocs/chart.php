<?php
// chart.php - Outputs a chart in png format using the GD library
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//

@include ('set_include_path.inc.php');
require ('db_connect.inc.php');
require ('functions.inc.php');
// This page requires authentication
require ('auth.inc.php');

if (!extension_loaded('gd')) trigger_error("{$CONFIG['application_name']} requires the gd module", E_USER_ERROR);

// External variables
$type = $_REQUEST['type'];
$data = explode('|',cleanvar($_REQUEST['data']));
$legends = explode('|',cleanvar($_REQUEST['legends']));
$title = urldecode(cleanvar($_REQUEST['title']));
$unit = cleanvar($_REQUEST['unit']);

$img = draw_chart_image($type, 500, 150, $data, $legends, $title, $unit);

// output to browser
// flush image
header('Content-type: image/png');
header("Content-disposition-type: attachment\r\n");
header("Content-disposition: filename=sit_chart_".date('Y-m-d').".png");
imagepng($img);
imagedestroy($img);

?>