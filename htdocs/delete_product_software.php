<?php
// delete_product_software.php
//
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2000-2007 Salford Software Ltd. and Contributors
//
// This software may be used and distributed according to the terms
// of the GNU General Public License, incorporated herein by reference.
//


// Author: Ivan Lucas <ivanlucas[at]users.sourceforge.net>

// Removes link between a product and software

$permission=24;  // Add Product
require('db_connect.inc.php');
require('functions.inc.php');
$title="Disassociate skill with a product";

// This page requires authentication
require('auth.inc.php');

// External variables
$productid = cleanvar($_REQUEST['productid']);
$softwareid = cleanvar($_REQUEST['softwareid']);

if (!empty($productid) && !empty($softwareid))
{
    $sql = "DELETE FROM softwareproducts WHERE productid='$productid' AND softwareid='$softwareid' LIMIT 1";
    mysql_query($sql);
    if (mysql_error()) trigger_error("MySQL Query Error ".mysql_error(), E_USER_ERROR);
    journal(CFG_LOGGING_NORMAL, 'Skill Unlinked', "Skill $softwareid was unlinked from Product $productid", CFG_JOURNAL_PRODUCTS, $productid);
    confirmation_page("2", "products.php", "<h2>Linked skill removed Successfully</p><p align='center'>{$strPleaseWaitRedirect}...</p>");
}
else
{
    throw_error("Could not delete softwareproduct", "Parameter(s) missing");
}
?>