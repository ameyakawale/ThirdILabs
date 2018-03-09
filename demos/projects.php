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
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Third I Labs">
	<meta name="author" content="Sushil Muzumdar">
	<link rel="icon" href="assets/img/favicon.png">
    <title>Third I Labs | Projects</title>
    <!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,700&amp;subset=latin-ext" rel="stylesheet">

	<!-- CSS - REQUIRED - START -->
	<!-- Batch Icons -->
	<link rel="stylesheet" href="assets/fonts/batch-icons/css/batch-icons.css">
	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="assets/css/bootstrap/bootstrap.min.css">
	<!-- Material Design Bootstrap -->
	<link rel="stylesheet" href="assets/css/bootstrap/mdb.min.css">
	<!-- Custom Scrollbar -->
	<link rel="stylesheet" href="assets/plugins/custom-scrollbar/jquery.mCustomScrollbar.min.css">
	<!-- Hamburger Menu -->
	<link rel="stylesheet" href="assets/css/hamburgers/hamburgers.css">

	<!-- CSS - REQUIRED - END -->

	<!-- CSS - OPTIONAL - START -->
	<!-- Font Awesome -->
	<link rel="stylesheet" href="assets/fonts/font-awesome/css/font-awesome.min.css">
	<!-- JVMaps -->
	<link rel="stylesheet" href="assets/plugins/jvmaps/jqvmap.min.css">
	<!-- CSS - OPTIONAL - END -->

	<!-- QuillPro Styles -->
	<link rel="stylesheet" href="assets/css/quillpro/quillpro.css">
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<nav class="navbar-sidebar-horizontal navbar navbar-expand-lg navbar-light bg-white fixed-top" style="padding-top: 1rem!important;">
				<a class="navbar-brand" href="#">
					<div class="profile-name" style="font-size: 2.0rem!important;">Third I Labs</div>
				</a>
				<button class="hamburger hamburger--slider" type="button" data-target="#navbar-header-menu-outer" aria-controls="navbar-header-menu-outer" aria-expanded="false" aria-label="Toggle Header Menu">
					<span class="hamburger-box">
						<span class="hamburger-inner"></span>
					</span>
				</button>

				<div class="navbar-collapse" id="navbar-header-content">
					<ul class="navbar-nav navbar-language-translation mr-auto">
					</ul>
					<ul class="navbar-nav navbar-notifications">
					</ul>
					<ul class="navbar-nav ml-5 navbar-profile">
						<li class="nav-item">
							<a class="nav-link waves-effect waves-light" href="logout.php">
								<i class="batch-icon batch-icon-outgoing"></i>
								Logout
							</a>
						</li>
					</ul>
				</div>
			</nav>
			<div class="right-column">
				<nav class="sidebar-horizontal navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
					<div class="navbar-collapse" id="navbar-header-menu-outer">
						<ul class="navbar-nav navbar-header-menu mr-auto">
							<li class="nav-item active" id="all-categories-menu">
								<a class="nav-link" href="#" onclick="filterCategories('all-categories');">
									<i class="batch-icon batch-icon-star"></i>
									ALL
								</a>
							</li>
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
							$categoryQuery = "select distinct project_category from PROJECTS_DIM;";
							$categoryResults = $conn->query($categoryQuery);
							$catResultArr = array();
							while ($row = $categoryResults->fetch_array(MYSQLI_ASSOC)) {
								$catResultArr[] = $row;
							}
							for ($i = 0; $i < sizeof($catResultArr); $i++) {
								$categoryDesc = str_replace(' ', '-', strtolower($catResultArr[$i]['project_category']));
								echo '<li class="nav-item" id="' . $categoryDesc . '-menu">
											<a class="nav-link" href="#" onclick="filterCategories(\'' . $categoryDesc . '\');">
												<i class="batch-icon batch-icon-star"></i>
												' . $catResultArr[$i]['project_category'] . '
											</a>
										</li>';
							}
							?>
						</ul>
					</div>
				</nav>
				<main class="main-content p-5" role="main"> <!--style="padding-top: 8rem!important;">-->
					<div class="row">
						<?php 

							if ($conn->connect_error) {
								die("Connection failed: " . $conn->connect_error);
							}
							$projectsQuery = "select project_id, project_name, project_desc, project_img, project_url, project_category from PROJECTS_DIM;";
							$projectsResults = $conn->query($projectsQuery);
							$resultArr = array();
							while ($row = $projectsResults->fetch_array(MYSQLI_ASSOC)) {
								$resultArr[] = $row;
							}
							for ($i = 0; $i < sizeof($resultArr); $i++) {
								$categoryDesc = str_replace(' ', '-', strtolower($resultArr[$i]['project_category']));
								echo '<div class="col-md-4 col-lg-4 col-xl-4 mb-4" id="' . $categoryDesc . '" style="display: block;">
									<div class="card card-md">
										<div class="card-header">' . $resultArr[$i]['project_name'] . '</div>
											<div class="card-body text-center">
												<a href="#" onclick="showSolution(\'' . $resultArr[$i]['project_url'] . '\');">
													<img src="images/' . $resultArr[$i]['project_img'] . '.png" style="width: 310px;height: 255px;">
												</a>
											</div>
										</div>
									</div>';
							}
						?>
					</div>
					<div class="row mb-5">
						<div class="col-md-12">
							<footer>
								Copyright &copy; Third I Inc.
							</footer>
						</div>
					</div>
				</main>
			</div>
		</div>
	</div>
	<!-- SCRIPTS - REQUIRED START -->
	<!-- Placed at the end of the document so the pages load faster -->
	<!-- Bootstrap core JavaScript -->
	<!-- JQuery -->
	<script type="text/javascript" src="assets/js/jquery/jquery-3.1.1.min.js"></script>
	<!-- Popper.js - Bootstrap tooltips -->
	<script type="text/javascript" src="assets/js/bootstrap/popper.min.js"></script>
	<!-- Bootstrap core JavaScript -->
	<script type="text/javascript" src="assets/js/bootstrap/bootstrap.min.js"></script>
	<!-- MDB core JavaScript -->
	<script type="text/javascript" src="assets/js/bootstrap/mdb.min.js"></script>
	<!-- Velocity -->
	<script type="text/javascript" src="assets/plugins/velocity/velocity.min.js"></script>
	<script type="text/javascript" src="assets/plugins/velocity/velocity.ui.min.js"></script>
	<!-- Custom Scrollbar -->
	<script type="text/javascript" src="assets/plugins/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
	<!-- jQuery Visible -->
	<script type="text/javascript" src="assets/plugins/jquery_visible/jquery.visible.min.js"></script>
	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<script type="text/javascript" src="assets/js/misc/ie10-viewport-bug-workaround.js"></script>

	<!-- SCRIPTS - REQUIRED END -->

	<!-- SCRIPTS - OPTIONAL START -->
	<!-- ChartJS -->
	<script type="text/javascript" src="assets/plugins/chartjs/chart.bundle.min.js"></script>
	<!-- JVMaps -->
	<script type="text/javascript" src="assets/plugins/jvmaps/jquery.vmap.min.js"></script>
	<script type="text/javascript" src="assets/plugins/jvmaps/maps/jquery.vmap.usa.js"></script>
	<!-- Image Placeholder -->
	<script type="text/javascript" src="assets/js/misc/holder.min.js"></script>
	<!-- SCRIPTS - OPTIONAL END -->

	<!-- QuillPro Scripts -->
	<script type="text/javascript" src="assets/js/scripts.js"></script>
	<!--
    <script src='js/jquery-1.11.3.min.js'></script>
    <script src="js/sweetalert.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>-->
	<script src="js/projects.js"></script>
</body>
</html>
