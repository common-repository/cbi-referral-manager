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

include_once(dirname(__FILE__) . '../../../../wp-config.php');
include_once('referral.php');

$affiliate_network_table_name = $wpdb->prefix . "cbi_affiliate_network";

$page = $_GET['page'];
$searchString = $_GET['searchString'];
$resultsPage = $_GET['resultsPage'];
if($resultsPage == ""){
  $resultsPage = 0;
}
$resultsPerPage = 10;

echo "<br><h3>Network Sites</h3>";


echo "<form method='get' action='?page=$page' >" .
	"Search <input type='text' name='searchString' value='".$searchString."' />" .
	"<input type='hidden' name='page' value='".$page."'>" .
	"<input type='submit' name='search' value='Filter' >" .
	"</form>";

  $select = "select * from " . $affiliate_network_table_name . 
	" where LinkTitle like '%".$searchString."%' ".
	" or LinkURL like '%".$searchString."%' ".
	" or LinkDescription like '%".$searchString."%' " .
	" order by Rank desc" .
	" limit " . $resultsPage . ", ". $resultsPerPage;
  $result = mysql_db_query(DB_NAME, $select ) or die("Failed Query of " . $select );
  echo "<table width='100%'  bgcolor='#BBBBBB'><tr><td width='180'><b>Email</b></td><td width='180'><b>Title</b></td><td width='180'><b>Url</b></td></tr>";
  $count = 0;
  while($row=mysql_fetch_assoc($result)){
    echo "<tr><td><font size='1'>" . $row['Email'] . "</font></td><td><font size='1'>" .
    $row['LinkTitle'] . "</font></td><td><font size='1'><a href='".$row['LinkURL']."' target='_blank'>" . $row['LinkURL'] . "</a></font></td>".
    " </td><td align='right'> <a href='?page=$page&add=".$row['ID'].
	"&email=".urlencode($row['Email']).
	"&title=".urlencode($row['LinkTitle']).
	"&description=".urlencode($row['LinkDescription']).
	"&url=".urlencode($row['LinkURL'])."' >Add</a> ".
    " </td></tr>";
    $count = $count + 1;
  }

  // warn if no records
  if($count == 0){
    echo "<tr><td colspan='4'> There are currently no network affiliates that match your site.  </td></tr>";
  }

  // Next Prev
  echo "<tr><tr> &nbsp; </td></tr>";
  echo "<tr><td colspan='2' align='left'> ";
  if($resultsPage > 0){
	echo " <a href='?page=$page&searchString=$searchString&resultsPage=".( ($resultsPage- ($resultsPerPage) )  )."'><b>Previous</b></a>  ";
  }
  echo "</td><td colspan='2' align='right'> ";
  if($count > 0){
	echo " <a href='?page=$page&searchString=$searchString&resultsPage=".( ($resultsPage+1) * $resultsPerPage )."'><b>Next</b></a> ";
  }
  echo " </td></tr>";

  echo "</table>";

?>
