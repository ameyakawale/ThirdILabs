/**
 * This is the JavaScript file for the login page
 * @author Sushil Muzumdar
 * @version 1.0
 * @copyright 2016 Third I Inc.
 */
var urlStr = window.location.href;
var urlArr = urlStr.split('/');
var baseurl = urlArr[0] + '//' + urlArr[2] + '/' + urlArr[3] + '/';
//var baseurl = "http://45.63.12.123/demos/";

function showSolution(solutionURL) {
	if (solutionURL == '') {
		swal("Whoops!", "The solution isn't ready yet! Try again later");
	} else {
		//window.location.href = solutionURL;
		window.open(solutionURL, '_blank');
	}
}

function showGenericError(errStr) {
	swal("Oops...", errStr, "error");
}

function filterCategories(categoryID) {
	$('ul.navbar-nav.navbar-header-menu.mr-auto li').each(function(i) {
		if ($(this).attr('id') == categoryID + '-menu') {
    		$(this).attr('class', 'nav-item active');
		} else {
			$(this).attr('class', 'nav-item');
			showAllCategories();
			if (categoryID != 'all-categories') {
				hideOtherCategories(categoryID);
			}
		}
	});
}

function showAllCategories() {
	$(".col-md-4.col-lg-4.col-xl-4.mb-4").each(function(i) {
		$(this).show();
	});
}

function hideOtherCategories(catID) {
	$(".col-md-4.col-lg-4.col-xl-4.mb-4").each(function(i) {
		if ($(this).attr('id') != catID) {
			$(this).hide();
		}
	});
}
