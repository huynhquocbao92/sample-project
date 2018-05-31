$(document).ready(function() {
    /**
     * Datetime validate
     * @author baohq
     * @date   2017-10-06
     */

    // Define error messages
    var startDateError      = "公開開始日時を入力してください。";
    var startDateNotValid   = "終了日は開始日以降の日付を設定してください。";
	// Generate datetime
	$('.js-date-start').datetimepicker({
		format: 'YYYY/MM/DD HH:mm',
		locale: 'ja'
	});

	$('.js-date-end').datetimepicker({
		format: 'YYYY/MM/DD HH:mm',
		locale: 'ja',
	});

    /**
     * Viewdate end validate
     * @param  {[type]}   e
     * @return {[type]}
     */
	$('.js-date-end').on("dp.change", function(e) {
		clearMessage();
		var dateStart = new Date($('.js-date-start input').val()).getTime();
		var dateEnd = new Date($('.js-date-end input').val()).getTime();
		if (isNaN(dateStart)) {
			$('.js-date-end input').val('');
			showMessage(startDateError, 'error');
		}
		if (dateStart > dateEnd) {
			showMessage(startDateNotValid, 'error');
			$('.js-date-end input').val('');
			return false;
		}
		$("#viewdate_nolimit").prop('checked', false);
	});

    /**
     * Viewdate start validate
     * @param  {[type]}   e
     * @return {[type]}
     */
	$('.js-date-start').on("dp.change", function(e) {
		clearMessage();
		var dateStart = new Date($('.js-date-start input').val()).getTime();
		var dateEnd = new Date($('.js-date-end input').val()).getTime();
		if (dateStart > dateEnd) {
			showMessage(startDateNotValid, 'error');
			$('.js-date-start input').val('');
			return false;
		}
	});
});
