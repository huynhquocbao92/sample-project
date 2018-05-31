function isNumber(evt, element) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (
		(charCode != 45 || $(element).val().indexOf('-') == -1) &&      // “-” CHECK MINUS, AND ONLY ONE.
		(charCode != 46 || $(element).val().indexOf('.') == -1) &&      // “.” CHECK DOT, AND ONLY ONE.
		(charCode < 48 || charCode > 57) && (charCode != 8 || charCode != 9))              // check backspace
		return false;
	return true;
}

$(document).ready(function () {
	$('#start_date').on('change', function () {
		var startDate = new Date($('#start_date').val());
		var endDate = new Date($('#end_date').val());

		if (startDate > endDate){
			$('#start_date').val('');
		}
	});

	$('#end_date').on('change', function () {
		var startDate = new Date($('#start_date').val());
		var endDate = new Date($('#end_date').val());

		if (startDate > endDate){
			$('#end_date').val('');
		}
	});

	// VALIDATION FOR SUBMIT FORM
	$('.js-button-sm').on('click', function (e) {
		e.preventDefault();
		// Remove error content
		$('.js-validate-error').empty();
		$('.validation-error').remove();
		// List error
		var error_list ="";
		// Define regex
		var require_reg = /^[ 　\r\n\t]*$/
		var special_char_reg = /[<(.*)>.*<\/\1>]/
		var number_reg = /^[0-9]+$/
		var email_reg = /^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/

		// Define error message
		var require_msg = "を入力してください。";
		var invalid_msg = "が無効です。";
		var number_msg = "は数字で入力してください。";
		var email_msg = "の書式が正しくありません。";

		// Validation for input tag
		$('input[class*="js-input-"]').each(function(i, el){
			// Get title for print error
			var text_content = $(this).closest('.control-group').find('label').text().trim();
			// Remove text "Require" in label
			text_content = text_content.replace('必須項目', '').trim();
			// Value for check validation
			var in_val = $(this).val();

			// Check input value is empty
			if($(this).hasClass('js-input-text')) {
				if(in_val.match(special_char_reg)) {
					error_list += "<p>" + text_content + invalid_msg + "</p>";
				}
			}

			// Check input value empty and has special character
			if($(this).hasClass('js-input-text-req')) {
				if(in_val.match(require_reg)) {
					error_list += "<p>" + text_content + require_msg + "</p>";
				}
				else if(in_val.match(special_char_reg)) {
					error_list += "<p>" + text_content + invalid_msg + "</p>";
				}
			}

			// Check input value is number
			if($(this).hasClass('js-input-number')) {
				if(in_val!="" && !in_val.match(number_reg)) {
					error_list += "<p>" + text_content + number_msg + "</p>";
				}
			}

			// Check input field require and value is number
			if($(this).hasClass('js-input-number-req')) {
				if(in_val == "") {
					error_list += "<p>" + text_content + require_msg + "</p>";
				}
				else if(!in_val.match(number_reg)) {
					error_list += "<p>" + text_content + number_msg + "</p>";
				}
			}

			// Check input value is email
			if($(this).hasClass('js-input-email')) {
				if(in_val!="" && !in_val.match(email_reg)) {
					error_list += "<p>" + text_content + email_msg + "</p>";
				}
			}

			// Check input field require and value is email
			if($(this).hasClass('js-input-email-req')) {
				if(in_val == "") {
					error_list += "<p>" + text_content + require_msg + "</p>";
				}
				else if(!in_val.match(email_reg)) {
					error_list += "<p>" + text_content + email_msg + "</p>";
				}
			}
		});

		// Validation for textarea tag
		$('textarea[class*="js-input-"]').each(function(i, el){
			var text_content = $(this).closest('.control-group').find('label').text().trim();
			text_content = text_content.replace('必須項目', '').trim();
			var in_val = $(this).val();
			// Check input value is empty
			if($(this).hasClass('js-input-text')) {
				if(in_val.match(special_char_reg)) {
					error_list += text_content + invalid_msg;
				}
			}

			// Check input value empty and has special character
			if($(this).hasClass('js-input-text-req')) {
				if(in_val.match(require_reg)) {
					error_list += "<p>" + text_content + require_msg + "</p>";
				}
				else if(in_val.match(special_char_reg)) {
					error_list += "<p>" + text_content + invalid_msg + "</p>";
				}
			}
		});

		// Print Error
		if(error_list.length > 0) {
			$('.js-validate-error').addClass('alert alert-danger').prepend(error_list);
			return false;
		}
		else {
			$('.js-validate-error').remove();
			$('.js-form-sm').submit();
		}
	});
});
