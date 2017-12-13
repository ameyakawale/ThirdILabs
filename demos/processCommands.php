<?php
/**
 * This page processes various command from other files
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */
session_start();
header("Access-Control-Allow-Origin: *");
include ("lib/database.php");
require_once "customer.php";

ini_set ( 'display_errors', 1 );
$responseMsg = array();
$userID = trim($_POST['loginID']);

if (isset ( $_POST ['cmd'] )) {
	if ($_POST['cmd'] == 'registerUser') {
		if ((isset($_POST['loginID'])) && (isset($_POST['pwdStr']))) {
			$cust = new customer();
			// check if customer exists in 'Company' table (lookup by email); if no, add to the Company table.
			$newCustomerID = $cust->checkIfUserExists($userID);
			if ($newCustomerID == -1) {
				// add current user's ID and other information to Users table.
				$newCustomerID = $cust->addCustomer($userID, $_POST['firstName'], $_POST['lastName'] );
				$responseMsg ['response'] = 1;
				$responseMsg ['redirectURL'] = 'index.php';
				$responseMsg ['error'] = 'Account has been created successfully.';
			} else {
				// email already exists in the DB. Give corresponding message to end user.
				$responseMsg ['response'] = 2;
				$responseMsg ['error'] = 'User ' . $userID . ' is already registered. Please proceed to login.';
			}
		} else {
			$responseMsg ['response'] = 0;
			$responseMsg ['error'] = 'Error! Please check your parameters!';
		}
	} elseif ($_POST['cmd'] == 'signinUser') {
		if ((isset($_POST['loginID'])) && (isset($_POST['pwdStr']))) {
			$pwdhash = hash ('sha256', $_POST['pwdStr']);
			$hashedPwd = '';
			$conn = db_connecti();
			mysqli_query($conn, 'SET AUTOCOMMIT=0');
			$query = "select user_id, user_pwd from USERS_DIM where user_id =?;";
			$stmt = $conn->prepare($query) or die("Prepare failed : $query");
			$stmt->bind_param ("s", $userID) or die("Bind param failed : $query");
			$stmt->execute() or die ("Query failed $query " . mysqli_stmt_error($stmt));
			$stmt->bind_result($userID, $hashedPwd) or die ("Bind result failed : $query");
			if ($stmt->fetch ()) {
				if ($pwdhash == $hashedPwd) {
					$_SESSION['user_id'] = $userID;
					$responseMsg ['response'] = 1;
					$responseMsg ['redirectURL'] = 'projects.php';
				} else {
					$responseMsg ['response'] = 0;
					$responseMsg ['error'] = 'Your password is invalid!';
				}
			} else {
				$responseMsg ['response'] = 0;
				$responseMsg ['error'] = 'User ' . $username . ' does not exist!';
			}
			$stmt->close ();
			$conn->close ();
		} else {
			$responseMsg ['response'] = 0;
			$responseMsg ['error'] = 'ERROR! Cannot sign in user!';
		}
	} else {
		$responseMsg ['response'] = 0;
		$responseMsg ['error'] = 'Error! Invalid command!';
	}
} else {
	$responseMsg ['response'] = 0;
	$responseMsg ['error'] = 'Error! Invalid command!';
}
header ( 'Content-type: application/json' );
echo json_encode ( $responseMsg );
// session_destroy();

?>
