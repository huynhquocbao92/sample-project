var keyFlag = $('input[name=keyword_flag]').is(':checked');

// If keyword_flag will disabled keyword field
if(keyFlag === false) {
    $('input[name=keyword]').attr('disabled', true);
}

// keyword_flag change event
$('input[name=keyword_flag]').change(function () {
    if ($(this).prop('checked')) {
        $('input[name=keyword]').attr('disabled', false);
    } else {
        $('input[name=keyword]').attr('disabled', true);
    }
});
