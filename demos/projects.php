<?php
/**
 * This is the projects page for Third I Labs.
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */
session_start();
include("lib/database.php");
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>Third I Labs | Projects</title>
 	<link rel="stylesheet" href="css/style.css">
 	<link rel="stylesheet" href="css/sweetalert.css">
 	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-inverse" style="border-radius: 0px !important;"><div class="container-fluid"><div class="navbar-header"><a class="navbar-brand" href="#">Solutions</a></div><ul class="nav navbar-nav navbar-right"><li><a href="logout.php">Logout</a></li></ul></div></nav>
<br/>

<?php 

	if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
		$userID = $_SESSION['user_id'];
	} else {
		$userID = '-1';
	}
	
	$conn = db_connecti();
	//Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	$projectsQuery = "select project_id, project_name, project_desc, project_img, project_url from PROJECTS_DIM;";
	$projectsResults = $conn->query($projectsQuery);
	$resultArr = array();
	while ($row = $projectsResults->fetch_array(MYSQLI_ASSOC)) {
		$resultArr[] = $row;
	}
	
	echo '<ul class="tileWrapper">';
	for ($i = 0; $i < sizeof($resultArr); $i++) {
		echo '<li class="box"><a href="#" onclick="showSolution(\'' . $resultArr[$i]['project_url'] . '\');"><img src="images/' . $resultArr[$i]['project_img'] . '.png" style="width:365px;height:300px"></a></li>';
	}
	echo '</ul>';

?>
    <script src='js/jquery-1.11.3.min.js'></script>
    <script src="js/sweetalert.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="js/projects.js"></script>
</body>
</html>
