<?php
/**
 * This is the database utility page
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */

$servername = "45.63.12.123";
$username = "thirdi";
$password = "Welcome20";
$schema = "labsDB";

function db_connecti() {

	$servername = "45.63.12.123";
	$username = "thirdi";
	$password = "Welcome20";
	$schema = "labsDB";
	
	//print "SERVER:".$servername;
	//print "USER:".$username;
	//print "PASSWORD:".$password;
	
	$result = mysqli_connect($servername, $username, $password);
	
	
	if (!$result)
		return false;

	if (!mysqli_select_db($result, $schema))
		return false;

	return $result;
}

function db_connect() {
	$servername = "45.63.12.123";
	$username = "thirdi";
	$password = "Welcome20";
	$schema = "labsDB";
	$result = mysql_pconnect($servername, $username, $password);

	if (!$result)
		return false;

	if (!mysql_select_db($schema))
		return false;

	return $result;
}

/* End of file database.php */
/* Location: /lib/database.php */
