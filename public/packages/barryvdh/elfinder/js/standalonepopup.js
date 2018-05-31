/* Commnet default setting start */
/*$(document).on('click','.popup_selector',function (event) {
    event.preventDefault();
    var updateID = $(this).attr('data-inputid'); // Btn id clicked
    var elfinderUrl = '/elfinder/popup/';

    // trigger the reveal modal with elfinder inside
    var triggerUrl = elfinderUrl + updateID;
    $.colorbox({
        href: triggerUrl,
        fastIframe: true,
        iframe: true,
        width: '70%',
        height: '50%'
    });

});*/
// function to update the file selected by elfinder
/*function processSelectedFile(filePath, requestingField) {
    $('#' + requestingField).val(filePath).trigger('change');
}*/
/* Commnet default setting end */

$sThis = {};
$(document).on('click','.popup_selector',function (event) {
	event.preventDefault();
	var updateID = $(this).attr('data-inputid'); // Btn id clicked
	var elfinderUrl = '/elfinder/popup/';

	// trigger the reveal modal with elfinder inside
	var triggerUrl = elfinderUrl + updateID;
	$.colorbox({
		href: triggerUrl,
		fastIframe: true,
		iframe: true,
		width: '70%',
		height: '50%'
	});
	$sThis = this;
});

// function to update the file selected by elfinder
function processSelectedFile(filePath, requestingField) {
	// $('#' + requestingField).val(filePath).trigger('change');
	$path = ('https:' == document.location.protocol ? 'https://' : 'http://') + $(location).attr('host') + '/' + filePath;
	$path = $path.replace('\\', '/'); // format file path
	$('#' + requestingField).val(filePath);
	if($($sThis).parent().find('input').hasClass('media-change')) {
		$($sThis).parent().find('input.media-change').attr('value', $path).change();
	}
	if($($sThis).parent().find('input').hasClass('movie-change')) {
		$($sThis).parent().find('input.movie-change').attr('value', $path).change();
	}
	if($($sThis).parent().find('input').hasClass('audio-change')) {
		$($sThis).parent().find('input.audio-change').attr('value', $path).change();
	}
}
