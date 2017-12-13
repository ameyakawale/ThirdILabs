<?php
/**
 * This page is unsets all the session and logs a user out
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */

unset( $_SESSION['user_id'] );
session_unset();
session_destroy();

//Redirect to the login page
header('Location: ' . ' index.php' );

?>
