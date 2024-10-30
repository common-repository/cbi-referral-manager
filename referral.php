<?php
/*
Plugin Name: CBI Referral Manager
Plugin URI: http://createbigincome.com/referral-manager-wordpress-plugin/
Description: Add an affiliate management system to your WordPress site. 
Automate comission payment to affiliates so they can market your product and services.
Version: 1.2.1
Author: Jon Taylor
Author URI: http://www.createbigincome.com/
*/
/*  
Copyright 2009  CreateBigIncome.com  (email : jon@publisherpartner.com)

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

include_once(dirname(__FILE__) . '../../../../wp-config.php');

//
// Template Tags
//
function cbiReferringSites(){
  echo "<h3>Partner Sites</h3>";

  // Get Post header for keyword targeting
  global $wp_query;
  $postID = $wp_query->post->ID;
  $post = get_post($postID); 
  $postTitle = $post->post_title;
  $postBody = $post->post_content;
  $postCategoryObjects = get_the_category();
  $postCategory = array();
  foreach ($postCategoryObjects as $cat){
    array_push( $postCategory, $cat->cat_name );
  }
  $postTokens = explode(" ", $postTitle);
  $postTokens = array_merge( explode(" ", $postBody), $postTokens );
  $postTokens = array_merge( $postCategory, $postTokens );
  $postTokens = array_unique($postTokens);
  echo "<!-- post: ". $postID . " " . sizeof($postTokens) . " -->";

  // Get affiliate links from database
  $affiliate_table_name = $wpdb->prefix . "wp_cbi_affiliate";
  $db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("Connection Failure to Database");
  mysql_select_db(DB_NAME, $db) or die (DB_NAME . " Database not found. " . DB_USER);

  $referringLinkCount = get_option("cbi_referring_site_link_count");
  if($referringLinkCount == ""){
	$referringLinkCount = "20";
  } 

  $select = "select * from " . $affiliate_table_name . " where Approved = 1 order by Rank desc"; // limit " . $referringLinkCount;
  //echo "" . $select;
  $result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
  $sortedLinkArray = array();
  while($row=mysql_fetch_assoc($result)){
    $linkContent = $row['LinkCategory'] . " " . $row['LinkTitle'];

    $keywordMatches = 0;
    foreach ($postTokens as $token){
      $token = trim($token, " .,!?");
      if( $linkContent != "" && $token != "" &&  stripos($linkContent, $token) !== false ){
        $keywordMatches = $keywordMatches + 1;
      }
    }    

    $rank = $row['Rank'] + $keywordMatches;
    $link = "<li><a href='" . $row['LinkURL'] . "' ".
	"title='".$row['LinkCategory'] . " (Relevance: ".$keywordMatches."  Popularity: " . $row['Rank'] . ")' > " . 
	$row['LinkTitle'] . "</a> <font size='1'> </font> </li> ";
    if( $sortedLinkArray[$rank] != "" ){
      $existing = $sortedLinkArray[$rank] . $link;
      $sortedLinkArray[$rank] = $existing;
    } else {
      $sortedLinkArray[$rank] = $link; 
    }

  }
  krsort($sortedLinkArray);
  // display
  $count = 0;
  foreach ($sortedLinkArray as $link){
    if($count < $referringLinkCount){
      echo $link;
    }
    $count = $count + 1;
  }


  echo "<script language=\"javascript\" type=\"text/javascript\">
  <!--
  function popitup(url) {
    newwindow=window.open(url,'name','height=400,width=450');
    if (window.focus) {newwindow.focus()}
      return false;
    }
  // -->
  </script>".
  "<a href='/wp-content/plugins/cbi-referral-manager/addSiteLink.php'  
  onclick=\"return popitup('/wp-content/plugins/cbi-referral-manager/addSiteLink.php')\"
  >Add Your Site Link Here</a>";
}
add_action('wp_meta', 'cbiReferringSites');

//
// Administration Functions
//
add_action('admin_menu', 'my_plugin_menu');
function my_plugin_menu() {
  $pendingLinks = 0;
  $affiliate_table_name = $wpdb->prefix . "wp_cbi_affiliate";
  $db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("Connection Failure to Database");
  mysql_select_db(DB_NAME, $db) or die (DB_NAME . " Database not found. " . DB_USER);

  $select = "select count(ID) as count from " . $affiliate_table_name . " where Approved = 0";
  $result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
  if($row=mysql_fetch_assoc($result)){
    $pendingLinks = $row['count'];
  }
  add_options_page('CBI Referral Management Options', 'Referral Mgt. (' . $pendingLinks . ')', 8, __FILE__, 'my_plugin_options');
}
function my_plugin_options() {
  $page = $_GET['page'];
  echo '<div class="wrap">';
  echo "<h2>CBI Referral Management</h2>";
  echo '<p>Manage your referral program settings.</p>';
  $landing = get_option("cbi_landing_page");
  if($_POST['land'] != ""){
        $landing = $_POST['land'];
        update_option("cbi_landing_page", $landing);
  }
  echo "<form action='?page=$page' method='post'  >" .
   
  "<p>Enter your site landing or signup page URL: <input type='text' size='35' name='land' value='";
  if($landing == ""){
    bloginfo('url');    
  } else {
    echo $landing;
  }  	
  echo "' /> ".
  "&nbsp; <input type='submit' class='button-primary' name='referring' value='Save' >" .
  "</p>".
  "</form>";
  
  
  $referringLinkCount = get_option("cbi_referring_site_link_count");
  if($referringLinkCount == ''){
	$referringLinkCount = 20;
	add_option("cbi_referring_site_link_count", $referringLinkCount);
  }
  if($_POST['referring'] != "" && $_POST['siteLinkCount'] != ""){
	$referringLinkCount = $_POST['siteLinkCount'];
	update_option("cbi_referring_site_link_count", $referringLinkCount);
  }
  echo "<form action='?page=$page' method='post'  >" .
	"Number of referring site links to display: <input type='text' name='siteLinkCount' value='$referringLinkCount' size='6'>".
	"&nbsp; <input type='submit' class='button-primary' name='referring' value='Save' >" .
  	"</form>";

  $affiliate_table_name = $wpdb->prefix . "wp_cbi_affiliate";
  $affiliate_network_table_name = $wpdb->prefix . "wp_cbi_affiliate_network";
  $affiliate_network_reference_table_name = $wpdb->prefix . "cbi_affiliate_network_reference";

  $db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die("Connection Failure to Database");
  mysql_select_db(DB_NAME, $db) or die (DB_NAME . " Database not found. " . DB_USER);

  if($_GET['approve'] != ""){
    //echo "Approve";
    $update = "update " . $affiliate_table_name . " set Approved='1' where ID=" . $_GET['approve'];
    mysql_query($update);


    $id = $_GET['approve'];
    $select = "select * from " . $affiliate_table_name . " where ID=" .$id;
    $result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
    $email = "";
    $title = "";
    $description = "";
    $url = "";
    if($row=mysql_fetch_assoc($result)){
      $email = $row['Email'];
      $title = $row['LinkTitle'];
      $description = $row['LinkCategory'];
      $url = $row['LinkURL'];
    }

    // add or update the approved site to the cbi referral network
    $submitURL = "http://createbigincome.com/wp-content/plugins/cbi-referral-manager/addNetworkSite.php" .
		"?title=" .urlencode($title) . "&description=" . urlencode($description) . "&url=".urlencode($url)."&email=" . urlencode($email);    
    //echo ": " . $submitURL;
    $result = file_get_contents($submitURL);
  }
  if($_GET['delete'] != ""){
    //echo "Delete";
    $update = "delete from " . $affiliate_table_name . " where ID=" . $_GET['delete'];
    mysql_query($update);
  }
  if($_GET['updateform'] != ""){
    $id = $_GET['updateform'];
    $select = "select * from " . $affiliate_table_name . " where ID=" .$id;
    $result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select ); 
    $email = "";
    $title = "";
    $description = "";
    $url = "";
    if($row=mysql_fetch_assoc($result)){
      $email = $row['Email'];
      $title = $row['LinkTitle'];  
      $description = $row['LinkCategory'];  
      $url = $row['LinkURL'];
    } 
    echo "<br><h3>Edit Site</h3>";
    echo 
    "<form action='?page=$page&update=".$_GET['update']."' method='GET' >" .
    "<table><tr><td>".
    " Email: </td><td> <input type='text' name='email' value='$email' > </td> </tr> " .
    "<tr><td> Title: </td><td> <input type='text' name='title' value='$title' > </td></tr> ".
    "<tr><td> Description: </td><td> <input type='text' name='description' value='$description' > </td></tr> ".
    "<tr><td> URL: </td><td> <input type='text' name='url' value='$url' > </td></tr>" .
    "<tr><td> </td><td>  ".
    "<input type='hidden' name='id' value='".$id."' >" .
    "<input type='hidden' name='page' value='".$page."' >" .
    "<input type='submit' name='update_action' value='Update' class='button-primary'> </td></tr> ".
    "</td></tr></table>" .
    "</form>";
  }
  if($_GET['update_action'] != ""){
    $update = "update " . $affiliate_table_name . " set Email='".$_GET['email']."', " .
	"LinkTitle='".$_GET['title']."', LinkURL='".$_GET['url']."', LinkCategory='".$_GET['description']."'  where ID=" . $_GET['id'];
    //echo "" . $update; 
    mysql_db_query(DB_NAME, $update ) or die("Failed Query of " . $update );
  }
  if($_GET['add'] != ""){ // Add site from network
    $email = $_GET['email'];
    $title = $_GET['title'];
    $description = $_GET['description'];
    $url = $_GET['url'];
    $insert = "INSERT INTO " . $affiliate_table_name .
            " (LinkURL, LinkTitle, LinkCategory, Email, Approved) " .
            "VALUES ('" . $url . "', '". $title . "', '". $description ."', '" . $email . "', 1)";
        //echo "insert: " . $insert;
        mysql_db_query(DB_NAME, $insert );
  }
 

  $select = "select * from " . $affiliate_table_name . " where Approved = 0";
  $result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
  echo "<table><tr><td width='700' valign='top' >";
  echo "<br><h3>Pending Sites</h3>"; 
  echo "<table width='100%' border=''><tr><td width='180'><b>Email</b></td><td width='180' ><b>Title</b></td><td width='180'><b>Url</b></td></tr>";
  $count = 0;
  while($row=mysql_fetch_assoc($result)){
    echo "<tr><td><font size='1'>" . $row['Email'] . "</font></td>".
	"<td><font size='1'>" . substr($row['LinkTitle'],0,32) . "</font></td>".
	"<td><font size='1'><a href='".$row['LinkURL']."' target='_blank' title='".$row['LinkURL']."'>" . substr($row['LinkURL'],0,32) . "</a></font></td>".
	"<td><a href='?page=$page&approve=".$row['ID']."'>Approve</a> |".
      " <a href='?page=$page&delete=".$row['ID']."'>Delete</a>  </td></tr>";
    $count = $count + 1;
  }
  if($count == 0){
    echo "<tr><td colspan='4'> There are currently no links.  </td></tr>";
  }
  echo "</table>";
  echo "</td></tr><tr><td width='700' valign='top' bgcolor='#BBBBBB'> ";
  echo "<br><h3>Approved Sites</h3>";
  $select = "select * from " . $affiliate_table_name . " where Approved = 1 order by Rank desc";
  $result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
  echo "<table width='100%'  bgcolor='#BBBBBB'><tr><td width='180'><b>Email</b></td><td width='180'><b>Title</b></td><td width='180'><b>Url</b></td></tr>";
  $count = 0;
  while($row=mysql_fetch_assoc($result)){
    echo "<tr><td><font size='1'>" . substr($row['Email'],0,32). "</font></td>".
	"<td><font size='1'>" . substr($row['LinkTitle'],0,32) . "</font></td>".
	"<td><font size='1'><a href='".$row['LinkURL']."' target='_blank' title='".$row['LinkURL']."'>" . substr($row['LinkURL'],0,32) . "</a></font></td>".
        "<td><a href='?page=$page&delete=".$row['ID']."'>Delete</a> ".
	" | <a onclick=\"alert('http://createbigincome.com/wp-content/plugins/cbi-referral-manager/referred.php?by=".$row['ID']  ."');\" >Link</a> " .
    " | <a href='?page=$page&updateform=".$row['ID']."' >Update</a> ".	
    " </td></tr>";
    $count = $count + 1;
  }
  if($count == 0){
    echo "<tr><td colspan='4'> There are currently no links.  </td></tr>";
  }
  echo "</table>";

  echo "</td></tr><tr><td width='700' valign='top' bgcolor='#BBBBBB'> ";
 
  $searchString = $_GET['searchString']; 
  $resultsPage = $_GET['resultsPage'];
  $request = "http://createbigincome.com/wp-content/plugins/cbi-referral-manager/getNetworkSites.php".
	"?page=$page&searchString=" . $searchString . "&resultsPage=" . $resultsPage;
  $result = file_get_contents($request);
  echo $result;


  echo "</table>";
  echo '</div>';
}




//
// Database Administration
//


function cbi_affiliate_manager_install() {
   global $wpdb;

   $affiliate_table_name = $wpdb->prefix . "cbi_affiliate";
   if($wpdb->get_var("SHOW TABLES LIKE '$affiliate_table_name'") != $affiliate_table_name) {
	$sql = "CREATE TABLE " . $affiliate_table_name . " (
	  ID bigint(11) NOT NULL AUTO_INCREMENT,
	  IP text NULL,
	  Name text NULL,
	  Email text NULL,
	  Rank mediumint(9) DEFAULT '0' NOT NULL,
	  LinkTitle text NULL,
	  LinkCategory text NULL,
	  LinkURL text NULL,
	  Approved smallint DEFAULT 0,
	  UNIQUE KEY ID (ID)
	);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	add_option("cbi_referral_manager_db_version", "1.0");
   }
   
   $affiliate_history_table_name = $wpdb->prefix . "cbi_affiliate_history";
   if($wpdb->get_var("SHOW TABLES LIKE '$affiliate_history_table_name'") != $affiliate_history_table_name) {
	$sql = "CREATE TABLE " . $affiliate_history_table_name . " (
	  ID bigint(11) NOT NULL AUTO_INCREMENT,
	  AffiliateID mediumint(9) NOT NULL,
	  Date DATE NULL,
	  ReferredVisitors bigint(11) DEFAULT '0' NOT NULL,
	  UNIQUE KEY ID (ID)
	);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	add_option("cbi_referral_manager_db_version", "1.0");
   }

   $ip_history_table_name = $wpdb->prefix . "cbi_ip_history";
   if($wpdb->get_var("SHOW TABLES LIKE '$ip_history_table_name'") != $ip_history_table_name) {
	$sql = "CREATE TABLE " . $ip_history_table_name . " (
	  ID bigint(11) NOT NULL AUTO_INCREMENT,
	  Date DATE NULL,
	  IP text NULL,
	  UNIQUE KEY ID (ID)
	);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	add_option("cbi_referral_manager_db_version", "1.0");
   }

  
   $affiliate_network_table_name = $wpdb->prefix . "cbi_affiliate_network";
   if($wpdb->get_var("SHOW TABLES LIKE '$affiliate_network_table_name'") != $affiliate_network_table_name) {
        $sql = "CREATE TABLE " . $affiliate_network_table_name . " (
          ID bigint(11) NOT NULL AUTO_INCREMENT,
          IP text NULL,
          Name text NULL,
          Email text NULL,
          Rank mediumint(9) DEFAULT '0' NOT NULL,
          LinkTitle text NULL,
          LinkDescription text NULL,
          LinkURL text NULL,
	  RefersToAffiliateCSV text NULL,
          Approved smallint DEFAULT 0,
          UNIQUE KEY ID (ID)
        );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option("cbi_referral_manager_db_version", "1.0");
   }

   $affiliate_network_reference_table_name = $wpdb->prefix . "cbi_affiliate_network_reference";
   if($wpdb->get_var("SHOW TABLES LIKE '$affiliate_network_reference_table_name'") != $affiliate_network_reference_table_name) {
        $sql = "CREATE TABLE " . $affiliate_network_reference_table_name . " (
          ID bigint(11) NOT NULL AUTO_INCREMENT,
          AffiliateNetworkID bigint(11) DEFAULT '0' NOT NULL,
          RefersToAffiliate bigint(11) DEFAULT '0' NOT NULL,
          UNIQUE KEY ID (ID)
        );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option("cbi_referral_manager_db_version", "1.0");
   }


}
register_activation_hook(__FILE__,'cbi_affiliate_manager_install');

?>
