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

$id = $_GET['by'];

$db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("Connection Failure to Database");
mysql_select_db(DB_NAME, $db) or die (DB_NAME . " Database not found. " . DB_USER);

$affiliate_table_name = $wpdb->prefix . "cbi_affiliate";
$affiliate_history_table_name = $wpdb->prefix . "cbi_affiliate_history";
$ip_history_table_name = $wpdb->prefix . "cbi_ip_history";

$today = mktime(0,0,0,date("m"),date("d"),date("Y"));
$date = date("Y/m/d", $today);
$monthago = date("Y/m/d", $today - ( /*days*/30 * 24 * 60 * 60)  );
$yesterday = date("Y/m/d", $today - (/*days*/1 	* 24 * 60 * 60));

$new_visitor = true;
$ip=getVisitorIP();
$select = "select ID from " . $ip_history_table_name . " where IP='".$ip."'";
$result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
if($row=mysql_fetch_assoc($result)){
	$new_visitor = false;
	//echo "ip exists!";
} else {
	$insert = "INSERT INTO " . $ip_history_table_name .
        " (Date, IP) " .
        "VALUES ('".$date."', '".$ip."')";
	mysql_db_query(DB_NAME, $insert );
	//echo "". $insert;
}

if($new_visitor){ // || true for testing
	//echo "date: " . $monthago . " " . $today.  "<br>";
	$select = "SELECT ID from " . $affiliate_history_table_name . " where Date='".$date."' and AffiliateID=" . $id;
	$result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
	if($row=mysql_fetch_assoc($result)){
	  $update = "update ". $affiliate_history_table_name . " set ReferredVisitors=ReferredVisitors+1 " .
		" where Date='".$date."' and AffiliateID=" . $id;
	  mysql_db_query(DB_NAME, $update );
	} else {
	  $insert = "INSERT INTO " . $affiliate_history_table_name .           
		" (AffiliateID, Date, ReferredVisitors) " .            
		"VALUES ('" . $wpdb->escape($id) . "', '".$date."', 1)";
	  mysql_db_query(DB_NAME, $insert );
	}

	// Update affiliate link rank
	$affiliate_rank = 0;
	$select = "select sum(ReferredVisitors) as count from " . $affiliate_history_table_name . " where AffiliateID=" . $id;
	$result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
	if($row=mysql_fetch_assoc($result)){
		$affiliate_rank = $row['count']; 

		$update = "update ". $affiliate_table_name . " set Rank=". $affiliate_rank . " where ID=" . $id ;
		mysql_db_query(DB_NAME, $update );
	}
}

$delete = "delete from " . $affiliate_history_table_name . " where Date < '". $monthago ."'";
mysql_db_query(DB_NAME, $delete );

$delete = "delete from " . $ip_history_table_name . " where Date <= '". $yesterday ."'";
mysql_db_query(DB_NAME, $delete );


//$select = "SELECT * from " . $affiliate_history_table_name ;
//$result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
//while($row=mysql_fetch_assoc($result)){
//	echo  "" .$row['AffiliateID']." ".$row['ReferredVisitors']." ".$row['Date']."  <br>";
//}

$landing = get_option("cbi_landing_page");

function getVisitorIP(){ 
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $TheIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else $TheIp=$_SERVER['REMOTE_ADDR'];
 
    return trim($TheIp);
}

?>
<html>
<head>
<meta HTTP-EQUIV="REFRESH" content="0; url=<?php 
if($landing == ""){
    bloginfo('url');
} else {
    echo $landing;
}
?>">
</head>
</html>
