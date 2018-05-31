//============== List Component init - Start
/**
 * Created by TungDT on 12/3/2017.
 */

//Date range picker - Start
$(function() {
	var url = window.location.pathname;

	if(url == '/be/sale/agency') // Check Sale Agency Select Month
	{
		$('input[name="daterange"]').daterangepicker({
			locale: {
				format: 'YYYY-MM',
			},
			"opens": "left",
			ranges: {
			'今月': [moment().startOf('month'), moment().endOf('month')], // THIS MONTH
			'全期間': ['2010-01-01', moment()], // ALL PERIOD
		}
	},
	function (start, end, label) {

			// Submit search form
			$('input[name="start_date"]').val(start.format('YYYY-MM'));
			$('input[name="end_date"]').val(end.format('YYYY-MM'));
			$("#frm").submit();
		}
		);
	}
	else
	{
		$('input[name="daterange"]').daterangepicker({
			locale: {
				format: 'YYYY-MM-DD',
			},
			"opens": "left",
			ranges: {
			'今日': [moment(), moment()], // TODAY
			// '昨日': [moment().subtract(1, 'days'), moment().subtract(1, 'days')], // YESTERDAY
			// '先週': [moment().subtract(6, 'days'), moment()], // LAST WEEK
			'今月': [moment().startOf('month'), moment().endOf('month')], // THIS MONTH
			// '先月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')], // LAST MONTH
			'全期間': ['2010-01-01', moment()], // ALL PERIOD
		}
	},
	function (start, end, label) {

			// Submit search form
			$('input[name="start_date"]').val(start.format('YYYY-MM-DD'));
			$('input[name="end_date"]').val(end.format('YYYY-MM-DD'));
			$("#frm").submit();
		}
		);
	}


});
//Date range picker - End