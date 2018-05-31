/**
 * Set action value when submit form
 * @author: baohq
 * @date: 2017-10-05
 */
$('.js-page-btn button').on('click', function(e) {
    e.preventDefault();
    $('input[name=action]').val($(this).attr('action'));
    $('form').submit();
});

/**
 * Clear message content
 * @author: baohq
 * @date: 2017-10-05
 */
function clearMessage() {
    $('.ibox-content').find('.alert').remove();
}

/**
 * Show message content
 * @author: baohq
 * @date: 2017-10-05
 * @param  string  msg
 * @param  string  type
 */
function showMessage(msg, type='success') {
    $('.wrapper-content').find('.alert').remove();
    if(type == 'success') {
        $('.ibox-content').prepend("<div class='alert alert-success'>" + msg + "</div>");
    }
    else {
        $('.ibox-content').prepend("<div class='alert alert-danger'>" + msg + "</div>");
    }
}

/**
 * Sort function on list
 * @author baohq
 * @date   2017-10-27
 * @return result
 */
$('.js-sort').on('click', function() {
	// Update sort param hidden value
	$('#sort_field').val($(this).attr('sort-field'));
	$('#sort_type').val($(this).attr('sort-type'));

	// storage param
	localStorage.setItem('stgSortField', $(this).attr('sort-field'));
	localStorage.setItem('stgSortType', $(this).attr('sort-type'));

	// Get current URL
	var url = window.location.href;

	// Update param value
	url = modURLParam(url, 'sort_field', $(this).attr('sort-field'));
	url = modURLParam(url, 'sort_type', $(this).attr('sort-type'));

	// Submit form
	$('form').prop('action', url);
	$('form').submit();
});

// Load sort field active
checkSortActive();

/**
 * Check sort active when load page
 * @author baohq
 * @date   2017-10-27
 * @return {[type]}   [description]
 */
function checkSortActive() {
	// Create local storage param
	var sortField  = localStorage.getItem('stgSortField');
	var sortType   = localStorage.getItem('stgSortType');

	if(getUrlParam('sort_field') == null && getUrlParam('sort_type') == null) {
		sortField = 'updated_at';
		sortType  = 'desc';
	}

	$('.js-sort').each(function(i, obj) {
		var _this = $(this);
		if(_this.attr('sort-field') == sortField && _this.attr('sort-type') == sortType) {
			_this.addClass('active');
		}
	});
}

/**
 * Get param value from URL
 * @author baohq
 * @date   2017-10-27
 * @param  string param
 * @return string value
 */
function getUrlParam(param) {
	var vars = {};
	window.location.href.replace( location.hash, '' ).replace(
		/[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
		function( m, key, value ) { // callback
			vars[key] = value !== undefined ? value : '';
		}
	);

	if (param) {
		return vars[param] ? vars[param] : null;
	}

	return vars;
}

/**
 * Update param value into URL
 * @author baohq
 * @date   2017-10-27
 */
(function(expCharsToEscape, expEscapedSpace, expNoStart, undefined) {
/**
 * Modifies the given URL, returning it with the given parameter
 * changed to the given value.  The parameter is added if it didn't
 * already exist.  The parameter is removed if null or undefined is
 * specified as the value.
 * @param {string} url  The URL to be modified.
 * @param {string} paramName  The URL parameter whose value will be
 *     modified.
 * @param {string} paramValue  The value to assign.  This will be
 *     escaped using encodeURIComponent.
 * @return {string}  The updated URL.
 */
 modURLParam = function(url, paramName, paramValue) {
 	paramValue = paramValue != undefined
 	? encodeURIComponent(paramValue).replace(expEscapedSpace, '+')
 	: paramValue;
 	var pattern = new RegExp(
 		'([?&]'
 		+ paramName.replace(expCharsToEscape, '\\$1')
 		+ '=)[^&]*'
 		);
 	if(pattern.test(url)) {
 		return url.replace(
 			pattern,
 			function($0, $1) {
 				return paramValue != undefined ? $1 + paramValue : '';
 			}
 		).replace(expNoStart, '$1?');
 	}
 	else if (paramValue != undefined) {
 		return url + (url.indexOf('?') + 1 ? '&' : '?')
 		+ paramName + '=' + paramValue;
 	}
 	else {
 		return url;
 	}
 };
})(/([\\\/\[\]{}().*+?|^$])/g, /%20/g, /^([^?]+)&/);
