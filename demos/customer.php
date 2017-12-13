<?php
/**
 * This is a customer class that performs operations related to customers
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */
session_start();
require_once 'lib/database.php';

class customer {
	public $customerName;
	public $domain;
	
	// add a company's value
	function addCompanyValue($company_id, $title, $subtitle, $desc) {
		$conn = db_connecti ();
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' );
		
		$query = "insert into company_values (company_id, value_title,value_subtitle, value_desc)
				values (? , ? , ?, ? )";
		$stmt = $conn->prepare ( $query );
		$stmt->bind_param ( "isss", $company_id, $title, $subtitle, $desc );
		
		$stmt->execute ();
		
		$stmt->close ();
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	
	// figure out the current survey question and send it out to the email listed.
	function sendDemoEmail($company_id, $title, $email, $name, $emoticon) {
		// this is the question that should be emailed out. If no survey is running, it should still return qid=1.
		$qid = $this->getSurveyQuestions ( $company_id, $email, 0, TRUE );
		$this->sendDemoSurveyEmail ( $company_id, $qid, $emoticon, $email );
	}
	
	/*
	 * Delete Company Values from the DB
	 */
	function deleteCompanyValues($company_id) {
		$conn = db_connecti ();
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' );
		
		$query = "delete from company_values where company_id=?";
		$stmt = $conn->prepare ( $query );
		$stmt->bind_param ( "i", $company_id );
		
		$stmt->execute ();
		
		$stmt->close ();
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	
	// if user exists, returns the company_id associated with the user id
	function checkIfUserExists($uid) {
		$conn = db_connecti();
		$userID = -1;
		$query = "SELECT user_id FROM USERS_DIM WHERE user_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ("s", $uid) or die ("Bind param failed : $query");
		$stmt->execute() or die ("Query failed $query " . mysqli_stmt_error($stmt));
		$stmt->bind_result($userID) or die ("Bind result failed : $query");
		// $exist = false;
		if (!$stmt->fetch()) {
			// $exist = true;
			$userID = -1;
		}
		$stmt->close ();
		$conn->close ();
		return $userID;
	}
	
	// if emails exists, returns the company_id associated with the email
	function getGoogleRefreshToken($userEmail) {
		$conn = db_connecti ();
		$token = - 1;
		$query = "SELECT refresh_token FROM googleapps WHERE admin_email=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "s", $userEmail ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $token ) or die ( "Bind result failed : $query" );
		
		// $exist = false;
		if (! $stmt->fetch ()) {
			// $exist = true;
			$token = - 1;
		}
		$stmt->close ();
		$conn->close ();
		return $token;
	}
	
	// check if employee exists in the employee table
	function checkIfEmployeeExists($userEmail, $companyID) {
		$conn = db_connecti ();
		$ret = TRUE;
		$query = "SELECT email FROM employees WHERE email=? and company_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "ss", $userEmail, $companyID ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $companyId ) or die ( "Bind result failed : $query" );
		
		if (! $stmt->fetch ()) {
			// $exist = true;
			$ret = FALSE;
		}
		$stmt->close ();
		$conn->close ();
		return $ret;
	}
	
	function addCustomer($userID, $firstName, $lastName) {
		$conn = db_connecti();
		mysqli_query($conn, 'SET AUTOCOMMIT=0');
		$query = "insert into USERS_DIM(user_id, user_pwd, first_name, last_name) values (?,?,?,?);";
		$stmt = $conn->prepare($query);
		$now = date ( "Y-m-d H:i:s" );
		$pwdhash = hash('sha256', $_POST['pwdStr']);
		$stmt->bind_param('ssss', $userID, $pwdhash, $firstName, $lastName);
		$stmt->execute();
		$stmt->close ();
		mysqli_query($conn, "COMMIT");
		return $userID;
	}
	
	function doesGoogleUserExist($userEmail) {
		$conn = db_connecti ();
		$ret = FALSE;
		$query = "SELECT refresh_token FROM googleapps WHERE admin_email=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "s", $userEmail ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $companyId ) or die ( "Bind result failed : $query" );
		
		if (! $stmt->fetch ()) {
			$ret = FALSE;
		} else {
			$ret = TRUE;
		}
		$stmt->close ();
		$conn->close ();
		return $ret;
	}
	
	function addEmployeeList($users, $companyID) {
		$conn = db_connecti ();
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' );
		$length = count ( $users );
		// print "HERE11111111, USERS: " . $length . " Company ID: " . $companyID;
		$now = date ( "Y-m-d H:i:s" );
		
		for($i = 0; $i < $length; $i ++) {
			$photo = $manager = $fname = $lname = $email = $workAddress = $homeAddress = $workPhone = $homePhone = $mobilePhone = $title = $dept = '';
			// print "EMAIL: " . $users[$i][primaryEmail];
			// print "FirstName: " . $users[$i][name][givenName];
			// print "LastName: " . $users[$i][name][familyName];
			
			$email = $users [$i] [primaryEmail];
			$fname = $users [$i] [name] [givenName];
			$lname = $users [$i] [name] [familyName];
			$photo = $users [$i] [name] [thumbnailPhotoUrl];
			// within Google Apps, an employee's status can be set to suspended.
			$employeeStatus = "ENABLED";
			if ($users [$i] [suspended] == "1") {
				$employeeStatus = "SUSPENDED";
			}
			
			$address = $users [$i] [addresses];
			for($j = 0; $j < count ( $address ); $j ++) {
				if ($address [$j] [type] == "work") {
					$workAddress = $users [$i] [addresses] [$j] [formatted]; // type=work
				} else if ($address [$j] [type] == "home") {
					
					$homeAddress = $users [$i] [addresses] [$j] [formatted]; // type=home
				}
			}
			
			// print "Address Work: " . $users[$i][addresses][0][formatted];//type=work
			// print "Address Home: " . $users[$i][addresses][1][formatted];//type=home
			
			$title = $users [$i] [organizations] [0] [title];
			$dept = $users [$i] [organizations] [0] [department];
			
			$relations = $users [$i] [relations];
			for($j = 0; $j < count ( $relations ); $j ++) {
				if ($relations [$j] [type] == "manager") {
					$manager = $relations [$j] [value]; // type=manager
				}
			}
			
			$phones = $users [$i] [phones];
			for($j = 0; $j < count ( $phones ); $j ++) {
				if ($phones [$j] [type] == "work") {
					$workPhone = $users [$i] [phones] [$j] [value]; // type=work
				} else if ($phones [$j] [type] == "mobile") {
					$mobilePhone = $users [$i] [phones] [$j] [value]; // type=mobile
				} else if ($phones [$j] [type] == "home") {
					$homePhone = $users [$i] [phones] [$j] [value]; // type=home
				}
			}
			
			// print "<br>" . $email . $fname . $lname . $title . $manager . $workAddress . $homeAddress . $dept . $workPhone . $mobilePhone . $homePhone . $companyID . $photo;
			
			// check if employee information exists in the employees table. If it does, then just update with latest information.
			// if it does not, then insert new row
			
			if ($this->checkIfEmployeeExists ( $email, $companyID )) {
				
				$query = "UPDATE employees set update_date=?, status=?, profile_pic=?, first_name=?,last_name=?, title=?, manager_id=?, work_address=?,home_address=?,department_desc=?,office_phone=?,mobile_phone=?,home_phone=? where employee_id=? and company_id=?";
				$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
				$stmt->bind_param ( "sssssssssssssss", $now, $employeeStatus, $photo, $fname, $lname, $title, $manager, $workAddress, $homeAddress, strtoupper ( $dept ), $workPhone, $mobilePhone, $homePhone, $email, $companyID );
			} else {
				
				$query = "INSERT INTO employees (update_date, status, employee_id, profile_pic, email,first_name,last_name, title, manager_id, work_address,home_address,department_desc,office_phone,mobile_phone,home_phone,company_id) VALUES (?,? , ? , ?, ?, ?, ?,?, ?, ?, ?,?,?,?,?,?)";
				$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
				
				// print "<BR>INSERT INTO employees (update_date, status, employee_id, profile_pic, email,first_name,last_name, title, manager_id, work_address,home_address,department_desc,office_phone,mobile_phone,home_phone,company_id) VALUES ("
				// . $now .",". $employeeStatus.",". $email.",". $photo.",". $email.",". $fname.",". $lname.",". $title.",". $manager.",". $workAddress.",". $homeAddress.",". strtoupper ( $dept ).",". $workPhone.",". $mobilePhone.",". $homePhone.",". $companyID . ")";
				
				$stmt->bind_param ( "sssssssssssssssi", $now, $employeeStatus, $email, $photo, $email, $fname, $lname, $title, $manager, $workAddress, $homeAddress, strtoupper ( $dept ), $workPhone, $mobilePhone, $homePhone, $companyID );
			}
			$stmt->execute ();
		}
		
		$stmt->close ();
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	
	// this function adds question and user specific text to the survey email message.
	function getSurveyMessage($cid, $to, $qid, $answer1, $answer2, $answer3, $answer4, $answer5, $question_desc, $template) {
		$idKey = encrypt ( $to, ENCRYPT_KEY );
		$idKey = urlencode ( $idKey );
		
		// if($question_desc[0]!="I"){
		// $question_desc = strtolower ( $question_desc[0] ) . substr($question_desc,1);
		// }
		
		$message = '<html><div style="min-height:100%;margin:0;padding:0;width:100%;background-color:#fafafa">        <center>            <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse:collapse;height:100%;margin:0;padding:0;width:100%;background-color:#fafafa">                <tbody><tr>                    <td align="center" valign="top" style="height:100%;margin:0;padding:10px;width:100%;border-top:0">                        						                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px!important">                            <tbody><tr>                                <td valign="top" style="background-color:#fafafa;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:left">                                                    <div style="text-align:center">Approximately 30 seconds to complete this survey.</div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                            <tr>                                <td valign="top" style="background-color:#ffffff;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>            <tr>                <td valign="top" style="padding:9px">                    <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" style="min-width:100%;border-collapse:collapse">                        <tbody><tr>                            <td valign="top" style="padding-right:9px;padding-left:9px;padding-top:0;padding-bottom:0;text-align:center">                                                                                                            <img align="center" alt="" src="https://app.everlign.com/images/e1_logo.png" width="70" style="max-width:70px;padding-bottom:0;display:inline!important;vertical-align:bottom;border:0;min-height:auto;outline:none;text-decoration:none" class="CToWUd">                                                                                                </td>                        </tr>                    </tbody></table>                </td>            </tr>    </tbody></table><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%;text-align:left">                                                    <div style="text-align:center"><span style="font-size:16px"><strong>[QUESTION_DESC]?</strong></span></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding:9px 4px;text-align:justify;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%">                                                    <div style="text-align:center"><a href="[option]1" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="1" height="45" src="https://everlign.appspot.com/img/unnamed.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 0px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]2" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="2" height="45" src="https://everlign.appspot.com/img/unnamed.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 1px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]3" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="3" height="45" src="https://everlign.appspot.com/img/unnamed.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 1px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]4" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="4" height="45" src="https://everlign.appspot.com/img/unnamed.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 1px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]5" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="5" height="45" src="https://everlign.appspot.com/img/unnamed.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 0px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                            <tr>                                <td valign="top" style="background-color:#ffffff;border-top:0;border-bottom:2px solid #eaeaea;padding-top:0;padding-bottom:9px"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%;text-align:left">                                                    <div style="text-align:center">[answer5] &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [answer1]</div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%;text-align:left">                                                    <div style="text-align:center"><span style="color:#a9a9a9;font-size:12px;line-height:20.8px;text-align:center">All answers are always anonymous</span></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                            <tr>                                <td valign="top" style="background-color:#fafafa;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:center">                                                    <div style="text-align:center"><br><span style="font-size:12px">Powered by&nbsp;<a href="https://www.everlign.com" title="Everlign Inc" style="color:#656565;font-weight:normal;text-decoration:underline" target="_blank">Everlign Inc</a></span></div>&nbsp;<div style="text-align:center"><br><span style="font-size:12px"><a style="line-height:1.6em;text-align:center;color:#656565;font-weight:normal;text-decoration:underline">unsubscribe</a></span></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                        </tbody></table>						                                            </td>                </tr>            </tbody></table>        </center></div>        </html>';
		
		if ($template == "emoticons") {
			// Using emoticons, not stars
			$message = '<html><div style="min-height:100%;margin:0;padding:0;width:100%;background-color:#fafafa">        <center>            <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse:collapse;height:100%;margin:0;padding:0;width:100%;background-color:#fafafa">                <tbody><tr>                    <td align="center" valign="top" style="height:100%;margin:0;padding:10px;width:100%;border-top:0">                        						                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px!important">                            <tbody><tr>                                <td valign="top" style="background-color:#fafafa;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:left">                                                    <div style="text-align:center">Approximately 30 seconds to complete this survey.</div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                            <tr>                                <td valign="top" style="background-color:#ffffff;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>            <tr>                <td valign="top" style="padding:9px">                    <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" style="min-width:100%;border-collapse:collapse">                        <tbody><tr>                            <td valign="top" style="padding-right:9px;padding-left:9px;padding-top:0;padding-bottom:0;text-align:center">                                                                                                            <img align="center" alt="" src="https://app.everlign.com/images/e1_logo.png" width="70" style="max-width:70px;padding-bottom:0;display:inline!important;vertical-align:bottom;border:0;min-height:auto;outline:none;text-decoration:none" class="CToWUd">                                                                                                </td>                        </tr>                    </tbody></table>                </td>            </tr>    </tbody></table><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%;text-align:left">                                                    <div style="text-align:center"><span style="font-size:16px;color:#696969"><strong>[QUESTION_DESC]?</strong></span></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding:9px 4px;text-align:justify;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%">                                                    <div style="text-align:center"><a href="[option]1" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="1" height="45" src="https://everlign.appspot.com/img/1.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 0px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]2" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="2" height="45" src="https://everlign.appspot.com/img/2.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 1px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]3" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="3" height="45" src="https://everlign.appspot.com/img/3.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 1px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]4" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="4" height="45" src="https://everlign.appspot.com/img/4.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 1px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a>&nbsp;<a href="[option]5" style="color:#2baadf;font-weight:normal;text-decoration:underline"><img align="none" alt="5" height="45" src="https://everlign.appspot.com/img/5.png" style="line-height:20.8px;width:45px;min-height:45px;margin:10px 0px 1px;border:0;outline:none;text-decoration:none" width="45" class="CToWUd"></a></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                            <tr>                                <td valign="top" style="background-color:#ffffff;border-top:0;border-bottom:2px solid #eaeaea;padding-top:0;padding-bottom:9px"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%;text-align:left">                                                    <div style="text-align:center">[answer5] &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [answer1]</div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#202020;font-family:Helvetica;font-size:16px;line-height:150%;text-align:left">                                                    <div style="text-align:center"><span style="color:#a9a9a9;font-size:12px;line-height:20.8px;text-align:center">All answers are always anonymous</span></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                            <tr>                                <td valign="top" style="background-color:#fafafa;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">    <tbody>        <tr>            <td valign="top">                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;border-collapse:collapse">                    <tbody><tr>                                                <td valign="top" style="padding-top:9px;padding-right:18px;padding-bottom:9px;padding-left:18px;word-break:break-word;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:center">                                                    <div style="text-align:center"><br><span style="font-size:12px">Powered by&nbsp;<a href="https://www.everlign.com" title="Everlign Inc" style="color:#656565;font-weight:normal;text-decoration:underline" target="_blank">Everlign Inc</a></span></div>&nbsp;<div style="text-align:center"><br><span style="font-size:12px"><a style="line-height:1.6em;text-align:center;color:#656565;font-weight:normal;text-decoration:underline">unsubscribe</a></span></div>                        </td>                    </tr>                </tbody></table>                            </td>        </tr>    </tbody></table></td>                            </tr>                        </tbody></table>						                                            </td>                </tr>            </tbody></table>        </center></div>        </html>';
		}
		$params = "https://app.everlign.com/emailanswer.html?duid=" . $idKey . "&cid=" . $cid . "&qid=" . $qid . "&aid=";
		
		// Just for the email part, we are showing answer1=answer2 and answer5=answer4 so that the words 'agree' and 'disagree' gets shown within email.
		// this is because 'strongly agree' and 'strongly disagree' are wrapping.
		$message = str_replace ( "[option]", $params, $message );
		$message = str_replace ( "[answer1]", $answer2, $message );
		$message = str_replace ( "[answer2]", $answer2, $message );
		$message = str_replace ( "[answer3]", $answer3, $message );
		$message = str_replace ( "[answer4]", $answer4, $message );
		$message = str_replace ( "[answer5]", $answer4, $message );
		$message = str_replace ( "[QUESTION_DESC]", $question_desc, $message );
		return $message;
	}
	
	// create personalized welcome message for each employee
	function getWelcomeMessage($companyid, $to, $qid) {
		$html = '<html><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse:collapse"><tbody><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400"><span class="HOEnZb"><font color="#888888"></font></span><table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;max-width:600px;margin:auto;background-color:#ffffff"><tbody><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400"></td></tr><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;padding-bottom:20px"><img align="center" src="https://everlign.appspot.com/img/everlign_emaillogo2.png" style="outline:none;text-decoration:none;border:none;width:100%;max-width:600px;max-height:600px" class="CToWUd"></td></tr><tr><td align="left" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><p style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400">[EMAIL_BODY]</p></td></tr><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px"><table border="0" cellpadding="0" cellspacing="0" width="60%" style="border-collapse:collapse;background-color:#ff5600;border-radius:5px"><tbody><tr><td align="center" valign="middle" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;color:#ffffff;font-weight:600;line-height:150%;padding-top:15px;padding-right:30px;padding-bottom:15px;padding-left:30px"><a href="[EVERLIGN_LINK]" title="Get Started" style="font-size:1em;line-height:150%;display:block;color:#ffffff;text-decoration:none" target="_blank">Get Started&nbsp;<img alt="" height="10" src="https://everlign.appspot.com/img/GT.png" width="8" style="outline:none;text-decoration:none;border:none" class="CToWUd"></a></td></tr></tbody></table></td></tr><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400"><table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse"><tbody><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;background-color:#fafafa;padding-right:10px;padding-bottom:10px;padding-left:10px;padding-top:20px"><table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="border-collapse:collapse"><tbody><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-weight:400;padding-right:10px;padding-left:10px;padding-top:10px;font-size:12px"><div style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-weight:400;font-size:0.8em">Powered by Everlign</div></td></tr><tr><td align="center" valign="top" style="font-family:&quot;Open Sans&quot;,&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;padding-right:20px;padding-left:20px;padding-top:10px"></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table><span class="HOEnZb"><font color="#888888"></font></span></td></tr></tbody></table></html>';
		$templateMessage = $this->getCompanyAttribute ( $companyid, "welcome_email" );
		
		/*
		 * Removing link to login in th welcome email, but keeping it.
		 * $userUrl = genFullUrl ( "/login.html?id=$idKey" );
		 */
		
		$idKey = encrypt ( $to, ENCRYPT_KEY );
		$idKey = urlencode ( $idKey );
		$userUrl = genFullUrl ( "/emailanswer.html?cid=" . $companyid . "&qid=" . $qid . "&id=$idKey" );
		
		$html = str_replace ( "[EMAIL_BODY]", $templateMessage, $html );
		$html = str_replace ( "[EVERLIGN_LINK]", $userUrl, $html );
		
		return $html;
	}
	
	// sends out the survey question email to demo list;
	//
	function sendDemoSurveyEmail($cid, $qid, $template, $to) {
		$conn = db_connecti ();
		$query = "select s.answer_id, s.answer_desc, q.question_desc
		from survey_answer_options s, survey_questions q
		where q.company_id=? and q.question_id=s.question_id and q.question_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "ss", $cid, $qid ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		mysqli_stmt_bind_result ( $stmt, $answer_id, $answer_desc, $question_desc );
		
		// this is assuming that all questions will always have 5 and only 5 answer options.
		$answer1 = $answer2 = $answer3 = $answer4 = $answer5 = null;
		while ( $stmt->fetch () ) {
			if ($answer_id == 1) {
				$answer1 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 2) {
				$answer2 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 3) {
				$answer3 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 4) {
				$answer4 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 5) {
				$answer5 = htmlspecialchars ( $answer_desc );
			}
		}
		mysql_close ( $conn );
		
		$subject = $question_desc;
		
		$message = $this->getSurveyMessage ( $cid, $to, $qid, $answer1, $answer2, $answer3, $answer4, $answer5, $question_desc, $template ); // . generateUnsubscribeText ( $to );
		$message = nl2br ( $message );
		sendemailViaSendgrid ( $to, $subject, $message, 'pulse@everlign.com', 'Pulse Survey' );
	}
	
	// sends out the survey question email to all employees;
	// TODO: Only send it to employees who have confirmed email.
	function sendPulseSurveyEmail($cid, $qid, $template) {
		$conn = db_connecti ();
		$query = "select s.answer_id, s.answer_desc, q.question_desc
		from survey_answer_options s, survey_questions q
		where q.company_id=? and q.question_id=s.question_id and q.question_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "ss", $cid, $qid ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		mysqli_stmt_bind_result ( $stmt, $answer_id, $answer_desc, $question_desc );
		
		// this is assuming that all questions will always have 5 and only 5 answer options.
		$answer1 = $answer2 = $answer3 = $answer4 = $answer5 = null;
		while ( $stmt->fetch () ) {
			if ($answer_id == 1) {
				$answer1 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 2) {
				$answer2 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 3) {
				$answer3 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 4) {
				$answer4 = htmlspecialchars ( $answer_desc );
			} else if ($answer_id == 5) {
				$answer5 = htmlspecialchars ( $answer_desc );
			}
		}
		mysql_close ( $conn );
		
		$subject = $question_desc;
		
		// iterate through the list of all employees
		$list = $this->getEmployeeList ( $cid );
		
		$elist = $list ['employees'];
		$enabled_count = 0;
		foreach ( $elist as $employee ) {
			// only send the email if the employee is 'enabled'
			if ($employee->status == "ENABLED") {
				$to = $employee->employee_id;
				$message = $this->getSurveyMessage ( $cid, $to, $qid, $answer1, $answer2, $answer3, $answer4, $answer5, $question_desc, $template ); // . generateUnsubscribeText ( $to );
				$message = nl2br ( $message );
				// TODO: The "FROM field is not being used; email is being sent from EVERLIGN_SUPPORT.
				sendemailViaSendgrid ( $to, $subject, $message, NULL, NULL );
				$enabled_count = $enabled_count + 1;
			}
		}
		// now that all emails have been sent, update the DB with the 'max number of possible respondants' so that the response rate can be calculated.
		$this->addPossibleRespondants ( $cid, $qid, $enabled_count );
	}
	
	// updates survey_questions with data about max possible respondants for the survey timeframe.
	function addPossibleRespondants($companyid, $questionid, $count) {
		$conn = db_connecti ();
		
		// first find the start and end dates for the current question.
		// then find the number of questions that are being asked with the same start and end time frame
		// update survey_questions with max respondants and max possible responses.
		
		$query = "select max(start_date),max(end_date) from survey_questions where company_id=? and question_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "ss", $companyid, $questionid ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $start, $end ) or die ( "Bind result failed : $query" );
		
		if (! $stmt->fetch ()) {
			// log error in file
		} else {
			$stmt->close ();
			// now get number of questions for this start and end timeframe.
			$query = "select count(question_id) from survey_questions where company_id=? and start_date=? and end_date=?";
			$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
			$stmt->bind_param ( "sss", $companyid, $start, $end ) or die ( "Bind param failed : $query" );
			$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
			$stmt->bind_result ( $questions_per_survey ) or die ( "Bind result failed : $query" );
			if (! $stmt->fetch ()) {
				// log error in file
			} else {
				$stmt->close ();
				
				// print ($count . ' ' . $max_responses. ' ' . $companyid. ' ' . $start. ' ' . $end);
				
				// now write max possible respondants to DB
				mysqli_query ( $conn, 'SET AUTOCOMMIT=0' );
				
				$query = "update survey_questions set max_respondants=?, max_responses=? where company_id=? and start_date=? and end_date=?";
				$stmt = $conn->prepare ( $query );
				$max_responses = $count * $questions_per_survey;
				$stmt->bind_param ( "iisss", $count, $max_responses, $companyid, $start, $end );
				
				$stmt->execute ();
				
				$stmt->close ();
				mysqli_query ( $conn, "COMMIT" );
			}
		}
		$stmt->close ();
		$conn->close ();
	}
	
	// Send 'welcome email' to the complete list of employees.
	// Welcome email only gets sent to 'ENABLED' accounts.
	// For example, this should not be sent to a generic email like 'sales@companyname.com' as its state is not expected to be ENABLED.
	function sendWelcomeEmail($cid, $from) {
		$welcomeSubject = $this->getCompanyAttribute ( $cid, "welcome_email_subject" );
		
		$qid = $this->getCurrentSurveyQuestion ( $cid );
		if ($qid > 0) {
			// iterate through the list of all employees
			$list = $this->getEmployeeList ( $cid );
			
			$elist = $list ['employees'];
			foreach ( $elist as $employee ) {
				if ($employee->status == "ENABLED") {
					$to = $employee->employee_id;
					$message = $this->getWelcomeMessage ( $cid, $to, $qid ) . generateUnsubscribeText ( $email );
					$message = nl2br ( $message );
					// TODO: The "FROM field is not being used; email is being sent from EVERLIGN_SUPPORT.
					sendemailViaSendgrid ( $to, $welcomeSubject, $message, NULL, NULL );
				}
			}
		}else {
			// LOG THAT NO SURVEY IS LIVE CURRENTLY.
		}
	}
	
	// Confirm if the registration email is valid.
	function sendRegistrationEmail($email) {
		$idKey = encrypt ( $email, ENCRYPT_KEY );
		$idKey = urlencode ( $idKey );
		$returnUrl = genFullUrl ( "/confirmemail.php?id=$idKey" );
		$message = "
		Thank you for registering at Everlign.com. There is one more step to complete
		your registration.
		Please confirm your email address by clicking the link below:
	
		$returnUrl
	
		If you did not register at Everlign, then someone probably
		mis-typed their email address. You can ignore this message, and we
		apologize for the inconvenience.
	
	
		" . generateUnsubscribeText ( $email );
		
		$message = nl2br ( $message );
		
		sendemailViaSendgrid ( $email, "Everlign email address verification", $message, NULL, NULL );
	}
	
	// Send an email to help users reset their password
	function sendForgotPasswordEmail($email) {
		$idKey = encrypt ( $email, FORGOTPASSWD_ENCRYPT_KEY );
		$idKey = urlencode ( $idKey );
		$returnUrl = genFullUrl ( "/reset?id=$idKey" );
		
		$message = "
		Hello,<br/><br/>
		We received a request to update your password on Everlign. Please follow this link to create a new password: </p>
		<br/>$returnUrl
		<br/>
		<br/>Team Everlign.<br/><br/>" . generateUnsubscribeText ( $email );
		
		$message = nl2br ( $message );
		
		sendemailViaSendgrid ( $email, "Everlign password reset", $message, NULL, NULL );
	}
	
	// Update user password
	function updatePassword($email, $pswd) {
		$pwdhash = hash ( 'sha256', $pswd );
	}
	
	/**
	 * update user attribute
	 */
	function updateUser($userid, $name, $value) {
		$conn = db_connecti () or error_page ( 'Error connecting to mysql' );
		
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' ) or error_page ( "Error, Autocommit" );
		$query = "update employees set $name=? where email=? ";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "si", $value, $userid );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->close ();
		
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	function updateUserProfile($userid, $phone, $title) {
		$conn = db_connecti () or error_page ( 'Error connecting to mysql' );
		
		// print ($userid . ' ' . $phone . ' ' . $title. "<BR>");
		
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' ) or error_page ( "Error, Autocommit" );
		$query = "update employees set title=?, mobile_phone=? where employee_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "sss", $title, $phone, $userid );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->close ();
		
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	function updateCompanyAttribute($companyid, $name, $value) {
		$conn = db_connecti () or error_page ( 'Error connecting to mysql' );
		
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' ) or error_page ( "Error, Autocommit" );
		$query = "update company set $name=? where company_id=? ";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "si", $value, $companyid );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->close ();
		
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	
	// pass in company_id and name of field to retrive its value.
	function getCompanyAttribute($companyid, $name) {
		$conn = db_connecti ();
		$value = "default";
		$query = "SELECT $name FROM company WHERE company_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "s", $companyid ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $value ) or die ( "Bind result failed : $query" );
		
		if (! $stmt->fetch ()) {
			echo "empty value";
		}
		$stmt->close ();
		$conn->close ();
		return $value;
	}
	
	/**
	 * Set Mission Information
	 */
	function setMission($companyid, $mission) {
		$conn = db_connecti ();
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' );
		
		$query = "update company set mission_stmt=? where company_id=?";
		$stmt = $conn->prepare ( $query );
		$stmt->bind_param ( "ss", $mission, $companyid );
		$stmt->execute ();
		$stmt->close ();
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	
	/**
	 * Get Mission Information
	 */
	function getMission($companyid) {
		$conn = db_connecti ();
		
		$companymission ['mission'] = array ();
		$query = "SELECT mission_stmt FROM company WHERE company_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "s", $companyid ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		mysqli_stmt_bind_result ( $stmt, $mission_stmt );
		while ( $stmt->fetch () ) {
			$mission = new Mission ();
			$mission->title = htmlspecialchars ( $mission_stmt );
			array_push ( $companymission ['mission'], $mission );
		}
		mysql_close ( $conn );
		return $companymission;
	}
	
	/**
	 * Get Employee Information
	 */
	function getEmployee($companyid, $employeeid) {
		$conn = db_connecti ();
		
		$employees ['employees'] = array ();
		if (empty ( $companyid )) {
			$query = "SELECT user_type, company_id, status, profile_pic, first_name,last_name, title, manager_id, work_address,home_address,department_desc,office_phone,mobile_phone,home_phone FROM employees WHERE employee_id=?";
			$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
			$stmt->bind_param ( "s", $employeeid ) or die ( "Bind param failed : $query" );
		} else {
			$query = "SELECT user_type, company_id, status, profile_pic, first_name,last_name, title, manager_id, work_address,home_address,department_desc,office_phone,mobile_phone,home_phone FROM employees WHERE company_id=? and employee_id=?";
			$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
			$stmt->bind_param ( "ss", $companyid, $employeeid ) or die ( "Bind param failed : $query" );
		}
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		mysqli_stmt_bind_result ( $stmt, $usertype, $companyid, $status, $profile_pic, $first_name, $last_name, $title, $manager_id, $work_address, $home_address, $department_desc, $office_phone, $mobile_phone, $home_phone );
		
		// $results = mysql_query ( $query, $conn ) or die;
		// $employees = array ();
		
		while ( $stmt->fetch () ) {
			$employee = new Employee ();
			$employee->status = $status;
			$employee->profile_pic = $profile_pic;
			$employee->first_name = htmlspecialchars ( $first_name );
			$employee->last_name = htmlspecialchars ( $last_name );
			$employee->title = htmlspecialchars ( $title );
			$employee->manager_id = htmlspecialchars ( $manager_id );
			$employee->work_address = htmlspecialchars ( $work_address );
			$employee->home_address = htmlspecialchars ( $home_address );
			$employee->department_desc = htmlspecialchars ( $department_desc );
			$employee->office_phone = htmlspecialchars ( $office_phone );
			$employee->mobile_phone = htmlspecialchars ( $mobile_phone );
			$employee->home_phone = htmlspecialchars ( $home_phone );
			$employee->employee_id = $employeeid;
			$employee->companyid = $companyid;
			if ($usertype == NULL) {
				$employee->user_role = '0';
			} else {
				$employee->user_role = $usertype;
			}
			array_push ( $employees ['employees'], $employee );
		}
		mysql_close ( $conn );
		
		$features ['features'] = array ();
		array_push ( $features ['features'], "1", "2", "3", "4" );
		array_push ( $employees ['employees'], $features );
		return $employees;
	}
	
	/**
	 * Get Employee List as Arrays
	 */
	function getEmployeeList($companyid) {
		$conn = db_connecti ();
		
		$employees ['employees'] = array ();
		$query = "SELECT employee_id, status, profile_pic, first_name,last_name, title, manager_id, work_address,home_address,department_desc,office_phone,mobile_phone,home_phone FROM employees WHERE company_id=? ORDER BY last_name ASC,first_name ASC";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "s", $companyid ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		mysqli_stmt_bind_result ( $stmt, $employee_id, $status, $profile_pic, $first_name, $last_name, $title, $manager_id, $work_address, $home_address, $department_desc, $office_phone, $mobile_phone, $home_phone );
		
		// $results = mysql_query ( $query, $conn ) or die;
		// $employees = array ();
		
		while ( $stmt->fetch () ) {
			$employee = new Employee ();
			$employee->employee_id = $employee_id;
			$employee->status = $status;
			$employee->profile_pic = $profile_pic;
			$employee->first_name = htmlspecialchars ( $first_name );
			$employee->last_name = htmlspecialchars ( $last_name );
			$employee->title = htmlspecialchars ( $title );
			$employee->manager_id = htmlspecialchars ( $manager_id );
			// $employee->work_address = htmlspecialchars($work_address);
			// $employee->home_address = htmlspecialchars($home_address);
			$employee->department_desc = htmlspecialchars ( $department_desc );
			// $employee->office_phone = htmlspecialchars($office_phone);
			// $employee->mobile_phone = htmlspecialchars($mobile_phone);
			// $employee->home_phone = htmlspecialchars($home_phone);
			
			array_push ( $employees ['employees'], $employee );
			// $employees [] = $employee;
		}
		mysql_close ( $conn );
		
		// return $employees;
		return $employees;
	}
	
	/*
	 * Return array of company Values
	 *
	 */
	function getCompanyValues($companyid) {
		$conn = db_connecti ();
		
		$values ['values'] = array ();
		$query = "SELECT value_title, value_subtitle, value_desc, value_icon  FROM company_values WHERE company_id=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "s", $companyid ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		mysqli_stmt_bind_result ( $stmt, $value_title, $value_subtitle, $value_desc, $value_icon );
		
		while ( $stmt->fetch () ) {
			$value = new Value ();
			$value->title = htmlspecialchars ( $value_title );
			$value->sub_title = htmlspecialchars ( $value_subtitle );
			$value->desc = htmlspecialchars ( $value_desc );
			$value->icon = $value_icon;
			
			array_push ( $values ['values'], $value );
		}
		mysql_close ( $conn );
		return $values;
	}
	
	/*
	 * Returns the 1st question of the current pulse survey. If no survey is running, qid=-1 will be returned.
	 * Currently, this function is used when sending out the 'welcome email'.
	 */
	function getCurrentSurveyQuestion($companyid) {
		$questionID = null;
		$qid = - 1;
		$now = date ( 'Y-m-d h:i:s', time () );
		$conn = db_connecti ();
		$query = "SELECT min(question_id) FROM survey_questions WHERE company_id=? and start_date<? and end_date>?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "sss", $companyid, $now, $now ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $questionID ) or die ( "Bind result failed : $query" );
		while ( $stmt->fetch () ) {
			$qid = $questionID;
		}
		$stmt->close ();
		mysql_close ( $conn );
		return $qid;
	}
	
	/*
	 * Returns the list of surveys questions which are valid for the current company and user;
	 * checks if user has already answered the question.
	 */
	// MEM CACHE TODO: CACHE "QID of which question is in current Active Survey" and also CACHE "SURVEY JSON"
	function getSurveyQuestions($companyid, $userid, $days, $justFirstQuestion) {
		// for the current user and company; figure out which questions are 'live'
		$now = date ( 'Y-m-d h:i:s', time () );
		
		if (! empty ( $days )) {
			$now = date ( 'Y-m-d h:i:s', strtotime ( "+" . $days . " day" ) );
		}
		
		// echo 'check if '.$userid .' has already answered<BR>';
		
		$questions ['questions'] = array ();
		// user has already answered the questions
		if ($this->hasUserAnsweredActiveSurvey ( $userid, $companyid, $now )) {
		}  // user has not answered the questions
else {
			
			$conn = db_connecti ();
			
			$tempquestions = array ();
			$tempanswers ['answers'] = array ();
			
			$query = "SELECT s.question_id, s.question_desc, a.answer_id, a.answer_desc, a.category_id, a.category_desc, s.start_date, s.end_date FROM survey_questions s, survey_answer_options a WHERE s.company_id=? and s.question_id=a.question_id and s.start_date< ? and s.end_date> ? ORDER BY question_id, answer_id";
			// print "SELECT s.question_id, s.question_desc, a.answer_id, a.answer_desc, a.category_id, a.category_desc, s.start_date, s.end_date FROM survey_questions s, survey_answer_options a WHERE s.company_id=" . $companyid . " and s.question_id=a.question_id and s.start_date<'" . $now . "' and s.end_date> '" . $now . "' ORDER BY question_id, answer_id";
			$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
			$stmt->bind_param ( "sss", $companyid, $now, $now ) or die ( "Bind param failed : $query" );
			$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
			mysqli_stmt_bind_result ( $stmt, $question_id, $question_description, $answer_id, $answer_desc, $category_id, $category_desc, $startdate, $enddate );
			
			// used to keep track of the number of questions in the current survey
			$number = 0;
			
			$currentQuestion = NULL;
			$answerOptions = NULL;
			$question = NULL;
			$firstquestion = 1;
			// Used to keep track of the previous question_id if there are multiple questions in the survey
			$prev_questionid = NULL;
			// Used to keep track of the previous question_text if there are multiple questions in the survey
			$prev_questiontext = NULL;
			$prev_category_desc = NULL;
			$prev_category_id = NULL;
			
			while ( $stmt->fetch () ) {
				// create a new "Question" object. We will iterate through the rows from the SQL response and for each question
				// we put it in a temporary array of questions. At the end, we add this temporary array to the $questions array and return it
				$question = new Question ();
				$question->question_id = $question_id;
				$question->category_id = $category_id;
				$question->category_desc = $category_desc;
				$question->question_text = htmlspecialchars ( $question_description );
				if ($number == 0) {
					$firstquestion = $question_id;
				}
				// if currentQuestion != question_id, means that its a new/different question
				if ($currentQuestion != $question_id) {
					// make sure that $currentQuestion is not NULL; cause we only want to create a new Question when we have all the parameters. If its null, its the first pass
					if ($currentQuestion != NULL) {
						// $tempanswers["size"] = 10;
						
						// This the scenario where the question being added to the temporary array is the previous question in the list of questions from the survey.
						// We say its 'previous' cause currentQuestion != question_id.
						// We can now put the current question into the temp array.
						$question->question_id = $prev_questionid;
						$question->question_text = $prev_questiontext;
						$question->category_id = $prev_category_id;
						$question->category_desc = $prev_category_desc;
						$question->answer_options = $tempanswers ['answers'];
						array_push ( $tempquestions, $question );
						// we reset the temporary 'asnwers' array where the answer_options for the previous question were being stored
						unset ( $tempanswers ['answers'] );
						$tempanswers ['answers'] = array ();
					}
					
					$currentQuestion = $question_id;
					$number ++;
					// create new 'Answers' object and start added answer_options to it. Later, we will associate this will a question.
					$answerOptions = new Answers ();
					$answerOptions->answer_id = $answer_id;
					$answerOptions->answer_desc = $answer_desc;
					
					array_push ( $tempanswers ['answers'], $answerOptions );
				} else {
					// ELSE loop means that the question is not a new one, we are iteration through the answer_choices for the question.
					// we store the current questions text and id in a variable which will be used when adding the question to the temp array. (done above in the 'if' loop)
					$prev_questionid = $question->question_id;
					$prev_questiontext = $question->question_text;
					$prev_category_id = $question->category_id;
					$prev_category_desc = $question->category_desc;
					$answerOptions = new Answers ();
					$answerOptions->answer_id = $answer_id;
					$answerOptions->answer_desc = $answer_desc;
					array_push ( $tempanswers ['answers'], $answerOptions );
				}
			}
			mysql_close ( $conn );
			
			// now that we have gone through all the rows of the SQL response, set the 'number of question' value
			$questions ["number_of_questions"] = $number;
			$questions ["start_time"] = strtotime ( $startdate );
			$questions ["end_time"] = strtotime ( $enddate );
			$questions ["category_id"] = $category_id;
			$questions ["category_desc"] = $category_desc;
			
			// We still need to add the last 'question' to the temporary array
			// $tempanswers["size"] = 10;
			$question->answer_options = $tempanswers ['answers'];
			array_push ( $tempquestions, $question );
			
			// now iterate thru the temp array and add it to $questions
			foreach ( $tempquestions as $q ) {
				array_push ( $questions ['questions'], $q );
			}
		}
		if ($justFirstQuestion) {
			return $firstquestion;
		}
		return $questions;
	}
	
	// check if the user has already answered the currently active survey
	// returns "TRUE" if user has answered the questions already, else FALSE
	function hasUserAnsweredActiveSurvey($uid, $cid, $now) {
		
		// first find the question which is in the active survey
		// $now = date ( 'Y-m-d h:i:s', time () );
		$questionID = null;
		$start_date = null;
		$end_date = null;
		
		$conn = db_connecti ();
		$query = "SELECT min(question_id),start_date, end_date FROM survey_questions WHERE company_id=? and start_date< ? and end_date> ?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "sss", $cid, $now, $now ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $questionID, $start_date, $end_date ) or die ( "Bind result failed : $query" );
		while ( $stmt->fetch () ) {
			$qid = $questionID;
			$sdt = $start_date;
			$edt = $end_date;
		}
		$stmt->close ();
		
		// echo ( "QID: " . $questionID . " ST: " . $start_date . " END: ". $end_date ) ;
		
		// now find if this questions has been answered already
		$query = "SELECT answer_id FROM survey_history WHERE employee_id=? and question_id=? and start_date=? and end_date=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "ssss", $uid, $questionID, $start_date, $end_date ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $answerId ) or die ( "Bind result failed : $query" );
		$ret = TRUE;
		if (! $stmt->fetch ()) {
			$ret = FALSE;
		}
		$stmt->close ();
		$conn->close ();
		return $ret;
	}
	
	// returns the first (minimum) questionID of the current active survey
	function getActiveSurveyFirstQuestion($cid) {
		$now = date ( 'Y-m-d h:i:s', time () );
		$conn = db_connecti ();
		$query = "SELECT min(question_id),start_date,end_date FROM survey_questions WHERE company_id=? and start_date< ? and end_date> ?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "sss", $cid, $now, $now ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $questionID, $start_date, $end_date ) or die ( "Bind result failed : $query" );
		$stmt->close ();
		$conn->close ();
		while ( $stmt->fetch () ) {
			$qid = $questionID;
			$sdt = $start_date;
			$edt = $end_date;
		}
		return $questionID;
	}
	
	// used for testing purposes when the current user's answer for the active survey needs to be deleted.
	function deleteActiveSurveyFirstQuestion($cid, $eid) {
		$now = date ( 'Y-m-d h:i:s', time () );
		$conn = db_connecti ();
		$query = "SELECT min(question_id),start_date,end_date FROM survey_questions WHERE company_id=? and start_date< ? and end_date> ?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "sss", $cid, $now, $now ) or die ( "Bind param failed : $query" );
		$stmt->execute () or die ( "Query failed $query " . mysqli_stmt_error ( $stmt ) );
		$stmt->bind_result ( $questionID, $start_date, $end_date ) or die ( "Bind result failed : $query" );
		
		while ( $stmt->fetch () ) {
			$qid = $questionID;
			$sdt = $start_date;
			$edt = $end_date;
		}
		$stmt->close ();
		// echo "<BR>delete from survey_history where company_id=".$cid." and employee_id=".$eid." and start_date=". $sdt . " and end_date=" . $edt . "<BR>";
		
		$query = "delete from survey_history where company_id=? and employee_id=? and start_date=? and end_date=?";
		$stmt = $conn->prepare ( $query );
		$stmt->bind_param ( "ssss", $cid, $eid, $start_date, $end_date );
		
		$stmt->execute ();
		
		$stmt->close ();
		mysqli_query ( $conn, "COMMIT" );
		
		$conn->close ();
	}
	/*
	 * Sends the survey email to all employees.
	 * It is assumed that this email goes at the beginning of the polling period and we do not need to check if employees have already answered
	 */
	function sendSurveyEmails($companyid) {
		// find the questions in the survey. Only look at the first question even if there are multiple questions.
		
		// figure out the subject
		// find the complete employee list
		//
	}
	
	/*
	 * Used to add the optional comments that employees might have
	 */
	function addComments($companyid, $userid, $start, $end, $comments) {
		$conn = db_connecti ();
		mysqli_query ( $conn, 'SET AUTOCOMMIT=0' );
		$now = date ( "Y-m-d H:i:s" );
		$query = "UPDATE survey_history set survey_comments=?, comments_timestamp=? where company_id=? AND employee_id=? AND start_date=? AND end_date=?";
		$stmt = $conn->prepare ( $query ) or die ( "Prepare failed : $query" );
		$stmt->bind_param ( "ssssss", $comments, $now, $companyid, $userid, date ( "Y-m-d H:i:s", $start ), date ( "Y-m-d H:i:s", $end ) );
		$stmt->execute ();
		$stmt->close ();
		mysqli_query ( $conn, "COMMIT" );
		$conn->close ();
	}
	
	/*
	 * Please note that we have created a unique index on the survey_history table to include columns:
	 * EMPLOYEE_ID
	 * START_DATE
	 * END_DATE
	 * QUESTION_ID
	 *
	 * This ensures that the insert into .. on duplicate key query successfully updates an existing entry instead of creating
	 * a new row if the user is updating their answer. This can happen if a user clicks on the link withing the survey email twice.
	 *
	 * Having unique index can be bad from a performance point of view at a later stage
	 */
	function insertSurveyResponse($companyid, $userid, $question, $questiontext, $answer, $answertext, $start, $end, $category_id, $category_desc, $comments) {
		// print "<BR>HERE: " . $userid;
		if (empty ( $comments )) {
			
			$conn = db_connecti ();
			mysqli_query ( $conn, 'SET AUTOCOMMIT=0' );
			
			$list = $this->getEmployee ( $companyid, $userid );
			// get the employee's manager, dept details
			$elist = $list ['employees'];
			$manager = 'UNSPECIFIED';
			$department = 'UNSPECIFIED';
			foreach ( $elist as $employee ) {
				if (! empty ( $employee->department_desc )) {
					$department = $employee->department_desc;
				}
				if (! empty ( $employee->manager_id )) {
					$manager = $employee->manager_id;
				}
				
				// print $department . " : " . $manager;
			}
			
			$query = "INSERT INTO survey_history (company_id, employee_id,question_id,question_desc, answer_id, answer_desc, start_date, end_date, manager_id, department_id, category_id, category_desc) 
				VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?) 
				ON DUPLICATE KEY UPDATE 
				company_id = VALUES(company_id),
				employee_id = VALUES(employee_id),
				question_id = VALUES(question_id), 
				question_desc = VALUES(question_desc), 
				answer_id = VALUES(answer_id), 
				start_date = VALUES(start_date), 
				end_date = VALUES(end_date),
				manager_id = VALUES(manager_id),
				department_id = VALUES(department_id),
				category_id = VALUES(category_id),
				category_desc = VALUES(category_desc);
				
				";
			$stmt = $conn->prepare ( $query );
			
			// echo $companyid . " | " . $userid . " | " . $category_id . " | " . $category_desc;
			
			$stmt->bind_param ( "isssssssssss", $companyid, $userid, $question, $questiontext, $answer, $answertext, date ( "Y-m-d H:i:s", $start ), date ( "Y-m-d H:i:s", $end ), $manager, $department, $category_id, $category_desc );
			$stmt->execute ();
			
			$stmt->close ();
			mysqli_query ( $conn, "COMMIT" );
			$conn->close ();
		} else {
			$this->addComments ( $companyid, $userid, $start, $end, $comments );
		}
	}
}
class Employee {
	public $employee_id;
	public $status;
	public $profile_pic;
	public $first_name;
	public $last_name;
	public $title;
	public $manager_id;
	public $work_address;
	public $home_address;
	public $department_desc;
	public $office_phone;
	public $mobile_phone;
	public $home_phone;
	public $companyid;
	public $user_role;
}
class Features {
	public $myteam; // 1
	public $mission; // 2
	public $values; // 3
	public $culture; // 4
	public $dashboard; // 5
	public $analytics; // 6
	public $feedback; // 7
	public $profile; // 8
	public $settings; // 9
}

// Company Values
class Value {
	public $title;
	public $sub_title;
	public $desc;
	public $icon;
}

// Company Mission
class Mission {
	public $title;
	public $desc;
}
class Question {
	public $question_id;
	public $question_text;
	public $answer_options;
	public $category_id;
	public $category_desc;
}
class Answers {
	public $answer_id;
	public $answer_desc;
}
class Comment {
	public $text;
	public $timestamp;
	public $sentiment;
}

?>