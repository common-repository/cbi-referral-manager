<?php
/*  Copyright 2009  CreateBigIncome.com  (email : jon@publisherpartner.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php

include_once(dirname(__FILE__) . '../../../../wp-config.php');
include_once('referral.php');

$title = $_POST['title'];
if($_GET['title'] != ""){
        $title = $_GET['title'];
}
$description = $_POST['description'];
if($_GET['description'] != ""){
        $url = $_GET['description'];
}
$url = $_POST['url'];
if($_GET['url'] != ""){
	$url = $_GET['url'];
}
$email = $_POST['email'];
if($_GET['email'] != ""){
        $email = $_GET['email'];
}
$affiliate_network_table_name = $wpdb->prefix . "cbi_affiliate_network";
if($url != ""){
        $insert = "INSERT INTO " . $affiliate_network_table_name .
            " (LinkURL, LinkTitle, LinkDescription, Email) " .
            "VALUES ('" . $wpdb->escape($url) . "', '". $wpdb->escape($title) . "', '". $wpdb->escape($description) ."', '" . $wpdb->escape($email) . "')";
        //echo "insert: " . $insert;
        $results = $wpdb->query( $insert );

	echo "OK";
} else {
	echo "ERROR";
}
?>
