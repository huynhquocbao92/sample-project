var telFlag = $('input[name=response_tel_flag]').is(':checked');
// If response_tel_flag will disabled select Character tel
if(telFlag === false) {
    $('select[name=char_tel_id]').attr('disabled', true);
}
// response_tel_flag change event
$('input[name=response_tel_flag]').change(function () {
    if ($(this).prop('checked')) {
        $('select[name=char_tel_id]').attr('disabled', false);
    } else {
        $('select[name=char_tel_id]').attr('disabled', true);
    }
});
