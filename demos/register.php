<?php
/**
 * This is the login page for Third I Labs. It is the entry point for all Third I demos
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */
session_start();
?>
<!DOCTYPE html>
<html >
  <head>
    <meta charset="UTF-8">
    <title>Third I Labs | Register</title>
 	<link rel="stylesheet" href="css/style.css">
 	<link rel="stylesheet" href="css/sweetalert.css">
</head>
<body>
    <div class="wrapper">
		<div class="container">
			<h1>Register</h1>
			
			<form class="form">
				<input type="text" id="firstName" placeholder="First Name">
				<input type="text" id="lastName" placeholder="Last Name">
				<input type="text" id="loginID" placeholder="Username">
				<input type="password" id="pwdStr" placeholder="Password">
				<input type="password" id="pwdConfStr" placeholder="Confirm Password">
				<button type="button" id="registerBtn">Register</button>
			</form>
		</div>
		
		<ul class="bg-bubbles">
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
		</ul>
	</div>
    <script src='js/jquery-1.11.3.min.js'></script>
    <script src="js/sweetalert.min.js"></script>
	<script src="js/register.js"></script>
</body>
</html>
