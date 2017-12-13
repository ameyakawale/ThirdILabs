/**
 * This is the JavaScript file for the login page
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */

var baseurl = "http://45.63.12.123/demos/";

$(document).ready(function() {
	
	$("#rgstr").click(function() {
		window.location.href = baseurl + 'register.php';
	});

	$("input").keypress(function (e) {
		if (e.which == 13) {
			var urlStr = baseurl + 'processCommands.php';
			$.ajax({
				type: "POST",
				url: urlStr,
				data: {
					cmd: 'signinUser',
					loginID: $("#loginID").val(),
					pwdStr: $("#pwdStr").val()
				},
				dataType: "json",
				success: function(data) {
					if (data.response == 1) {
						window.location.href = data.redirectURL;
		            } else {
		            	showGenericError(data.error);
		            }
		    	},
		    	error: function(xhr, ajaxOptions, thrownError) {
		    		swal("Oops...", "Error: " + xhr.status, "error");
		    		swal("Oops...", "Error: " + thrownError, "error");
		    	}
		    });
		    return false;
		}
	});
	
	$("#loginBtn").click(function() {
		var urlStr = baseurl + 'processCommands.php';
		$.ajax({
			type: "POST",
			url: urlStr,
			data: {
				cmd: 'signinUser',
				loginID: $("#loginID").val(),
				pwdStr: $("#pwdStr").val()
			},
			dataType: "json",
			success: function(data) {
				if (data.response == 1) {
					window.location.href = data.redirectURL;
	            } else {
	            	showGenericError(data.error);
	            }
	    	},
	    	error: function(xhr, ajaxOptions, thrownError) {
	    		swal("Oops...", "Error: " + xhr.status, "error");
	    		swal("Oops...", "Error: " + thrownError, "error");
	    	}
	    });
	});
	
});

function showGenericError(errStr) {
	swal("Oops...", errStr, "error");
}