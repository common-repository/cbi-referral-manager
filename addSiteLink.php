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
<html>
<head>
<title>Submit Your Site</title>
<!--<link rel="stylesheet" type="text/css" href="<?php //bloginfo('stylesheet_url'); ?>" />-->

<style type="text/css">
  body {
	font-family: verdana;
	font-size: 2;
  } 
  h3 {
	font-family: verdana;
	font-size: 4;
  } 
</style>

</head>
<body>
<?php 

include_once(dirname(__FILE__) . '../../../../wp-config.php');
include_once('referral.php');

$title = $_POST['title'];
$description = $_POST['description'];
$url = $_POST['url'];
$email = $_POST['email'];
$affiliate_table_name = $wpdb->prefix . "cbi_affiliate";
if($_POST['send'] != "" && $url != ""){
	$insert = "INSERT INTO " . $affiliate_table_name .
            " (LinkURL, LinkTitle, LinkCategory, Email) " .
            "VALUES ('" . $wpdb->escape($url) . "', '". $wpdb->escape($title) . "', '". $wpdb->escape($description) ."', '" . $wpdb->escape($email) . "')";
	//echo "insert: " . $insert;
	$results = $wpdb->query( $insert );

   	$query = "select ID from " . $affiliate_table_name . " where LinkURL='". $wpdb->escape($url) ."' and Email='".$wpdb->escape($email) ."' ";
	$result = mysql_db_query(DB_NAME, $query ) or die("Failed Query of " . $query );
	$id = 0;
	if($row=mysql_fetch_assoc($result)){
		$id = $row['ID'];
	} 
 

?>
<h3><font size="3">Your Site has Been Submitted</font></h3>
<br><br>
<table cellspacing='0' cellpadding='1' bgcolor='#DDDDDD' align='center' width='400' ><tr><td>
<table cellspacing='4' cellpadding='0' bgcolor='#EEEEEE' align='center' width='100%' ><tr><td>
<font size="2">
To ensure that your link is displayed higher on the list and not cut off at the end please
place the following link to our blog on your site:

<br><br>
&nbsp;
<i>
  <?php echo bloginfo('url') . "/wp-content/plugins/cbi-referral-manager/referred.php?by=" . $id; ?>
</i>
</font>
<?php // get_option("cbi_landing_page") ?>

</td></tr></table>
</td></tr></table>
<br>
<br>
<table width="100%">
<tr><td height='66'></td></tr>
<tr><td align="center">
<input type="submit" name="close" value="Close" onclick="javascript:window.close();">
</td></tr>
<tr><td height='66'></td></tr>
</table>

<br> &nbsp; 
<br> &nbsp;
<br> &nbsp;
<br> &nbsp;
<table cellspacing='0' cellpadding='1' bgcolor='#DDDDDD' align='center' width='400' ><tr><td>
<table cellspacing='4' cellpadding='0' bgcolor='#EEEEEE' align='center' width='100%' ><tr><td>
<font size="1">
This WordPress Referral Manager plugin is available from <a href='http://createbigincome.com/referral-manager-wordpress-plugin/' target='_blank'>Create Big Income Blog</a>
</font>
</td></tr></table>
</td></tr></table>

<?
} else {

echo "<h3><font size='3'>Submit Your Site Link</font></h3> ";

?>
<br>
<br>
<table cellspacing='0' cellpadding='1' bgcolor='#DDDDDD' align='center' width='400' ><tr><td>
<table cellspacing='4' cellpadding='0' bgcolor='#EEEEEE' align='center' width='100%' ><tr><td>
<font size="2">
Please note that this site list is sorted and limited by referral visitors sent to this blog. 
Once you submit your site you will be given a link to place on your site. The more traffic you send
the higher your link will be displayed in the list.
</font>
<br>
<?php // get_option("cbi_landing_page") ?> 

</td></tr></table>
</td></tr></table>

<br><br> &nbsp; 
<br><br>
<form method="post">
<table width="350" border='0' align="center">
	<tr><td height='30'></td></tr>
	<tr><td><font size="2">Your Site Link Text:</font> </td><td> <input type="text" name="title" > </td></tr>
	<tr><td><font size="2">Your Site Link Description:</font> </td><td> <input type="text" name="description" > </td></tr>
	<tr><td><font size="2">Your Site Link URL:</font> </td><td> <input type="text" name="url" > </td></tr>

	<tr><td>
	<font size="2">Your Email:</font> </td><td> <input type="text" name="email" >
	</td></tr>
	<tr><td></td><td align="center">
		<input type="submit" name="send" value="Send">
	</td></tr>
	<tr><td height='30'></td></tr>
</table>
</form>
<br>
<br><br>
<table cellspacing='0' cellpadding='1' bgcolor='#DDDDDD' align='center' width='400' ><tr><td>
<table cellspacing='4' cellpadding='0' bgcolor='#EEEEEE' align='center' width='100%' ><tr><td>
<font size="1">
This WordPress Referral Manager plugin is available from <a href='http://createbigincome.com/referral-manager-wordpress-plugin/' target='_blank'>Create Big Income Blog</a>
</font>
</td></tr></table>
</td></tr></table>

<?}?>
</body>
</html>


