/**
 * This is the JavaScript file for the register page
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */

var baseurl = "http://45.63.12.123/demos/";

$(document).ready(function() {
	
	$("#registerBtn").click(function() {
		var pw = $("#pwdStr").val();
		var pwConf = $("#pwdConfStr").val();
		if (pw === pwConf) {
			var urlStr = baseurl + 'processCommands.php';
			$.ajax({
				type: "POST",
				url: urlStr,
				data: {
					cmd: 'registerUser',
					loginID: $("#loginID").val(),
					pwdStr: $("#pwdStr").val(),
					pwdConf: $("#pwdConfStr").val(),
					firstName: $("#firstName").val(),
					lastName: $("#lastName").val()
				},
				dataType: "json",
				success: function(data) {
					if (data.response == 1) {
						window.location = data.redirectURL;
		            } else if (data.response == 2) {
		            	swal("Oops...", "This email is already registered! Please login.", "error");
		            } else {
						showGenericError(data.error);
		            }
		    	},
		    	error: function(xhr, ajaxOptions, thrownError) {
		    		swal("Oops...", "Error: " + xhr.status, "error");
		    		swal("Oops...", "Error: " + thrownError, "error");
		    	}
		    });
		} else {
			showGenericError('Your passwords did not match! Please try again!');
		}
	});
	
	$("input").keypress(function (e) {
		if (e.which == 13) {
			var pw = $("#pwdStr").val();
			var pwConf = $("#pwdConfStr").val();
			if (pw === pwConf) {
				var urlStr = baseurl + 'processCommands.php';
				$.ajax({
					type: "POST",
					url: urlStr,
					data: {
						cmd: 'registerUser',
						loginID: $("#loginID").val(),
						pwdStr: $("#pwdStr").val(),
						pwdConf: $("#pwdConfStr").val(),
						firstName: $("#firstName").val(),
						lastName: $("#lastName").val()
					},
					dataType: "json",
					success: function(data) {
						if (data.response == 1) {
							window.location = data.redirectURL;
			            } else if (data.response == 2) {
			            	swal("Oops...", "This email is already registered! Please login.", "error");
			            } else {
							showGenericError(data.error);
			            }
			    	},
			    	error: function(xhr, ajaxOptions, thrownError) {
			    		swal("Oops...", "Error: " + xhr.status, "error");
			    		swal("Oops...", "Error: " + thrownError, "error");
			    	}
			    });
			} else {
				showGenericError('Your passwords did not match! Please try again!');
			}
		}
	});
	
});

function showGenericError(errStr) {
	swal("Oops...", errStr, "error");
}