<?php
namespace App\Libs;

use App\AppTraits\SettingTrait;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
use Request;
use DateTime;
use Log;

Class DateTimeHelper
{
	use SettingTrait;

	/**
	 * Format date
	 * @param  [type]     $date   [description]
	 * @param  string     $format [default: Y-m-d H:i:s]
	 * @return [type]     date
	 */
	public static function formatDate($date, $format='Y-m-d H:i:s')
	{
		$date=date_create($date);
		return date_format($date, $format);
	}

	/**
	 * Convert from mongodb date object to date format
	 * @param  [type]     $dateObject [description]
	 * @param  string     $format     [description]
	 * @return [type]     $dateObject
	 */
	public static function convertDateFormat($dateObject, $format='Y-m-d')
	{
		if (!is_null($dateObject)) {
			$dateTime = $dateObject->toDateTime();
			return date($format, $dateTime->getTimestamp());
		}
		return $dateObject;
	}

	/**
	 * Convert String to Date
	 * @param  [type]     $s [description]
	 * @return [type]     date with format
	 */
	public static function convertStringToDate($s)
	{
		$date = date_create($s);
		return date_format($date,"Y-m-d H:i:s");
	}

	/**
	 * Convert String to Date
	 * @param  [type]     $s [string]
	 * @return [type]     '' or date with format
	 */
	public static function convertStringToDateTool($s)
	{
		if(date_create($s) == false) {
			return '';
		}
		else {
			$date = date_create($s);
		}
		return date_format($date,"Y-m-d H:i:s");
	}

	/**
	 * Get current date with format YYYY-MM-DD H:M:S
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateNow()
	{
		return Carbon::now(env('TIMEZONE','Asia/Tokyo'))->format('Y-m-d H:i:s');
	}

	/**
	 * Get yesterday with format YYYY-MM-DD H:M:S
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateYesterday()
	{
		return Carbon::yesterday(env('TIMEZONE','Asia/Tokyo'))->format('Y-m-d H:i:s');
	}

	/**
	 * Get tomorrow with format YYYY-MM-DD H:M:S
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateTomorrow()
	{
		return Carbon::tomorrow(env('TIMEZONE','Asia/Tokyo'))->format('Y-m-d H:i:s');
	}

	/**
	 * Date add day
	 * @param  [type]     $value [number day]
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateAddDay($value)
	{
		return Carbon::now(env('TIMEZONE','Asia/Tokyo'))->addDays($value)->format('Y-m-d H:i:s');
	}

	/**
	 * Date add month
	 * @param  [type]     $value [number month]
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateAddMonth($value)
	{
		return Carbon::now(env('TIMEZONE','Asia/Tokyo'))->addMonths($value);
	}

	/**
	 * Date add hour
	 * @param  [type]     $value [number hour]
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateAddHour($value)
	{
		return Carbon::now(env('TIMEZONE','Asia/Tokyo'))->addHours($value);
	}

	/**
	 * Date add minute
	 * @param  [type]     $value [number minute]
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateAddMinute($value)
	{
		return Carbon::now(env('TIMEZONE','Asia/Tokyo'))->addMinutes($value);
	}

	/**
	 * Date add second
	 * @param  [type]     $value [number second]
	 * @return [type]     date[YYYY-MM-DD H:M:S]
	 */
	public static function dateAddSecond($value)
	{
		return Carbon::now(env('TIMEZONE','Asia/Tokyo'))->addSeconds($value);
	}

	/**
	 * Get minute of two date
	 * @param  [type]     $date1 [date]
	 * @param  [type]     $date2 [date]
	 * @return [type]     minute
	 */
	public static function getMinuteOfTwoDate($date1, $date2)
	{
		$dateTimestamp1 = strtotime($date1);
		$dateTimestamp2 = strtotime($date2);
		$minute= round(($dateTimestamp1 - $dateTimestamp2)/60, 0);
		return $minute;
	}

	/**
	 * Convert date to mongo date
	 * @param  [type]     $dateString [string]
	 * @return [type]     Mongo date
	 */
	public static function convertDateToMongoDate($dateString)
	{
		return new UTCDateTime((new \DateTime($dateString))->getTimestamp()*1000);
	}

	/**
	 * Convert date to mongo date
	 * @param  [type]     $dateString [string]
	 * @return [type]     '' or mongo date
	 */
	public static function convertDateToMongoDateTool($dateString)
	{
		if($dateString == 0) {
			return '';
		}
		return new UTCDateTime((new \DateTime($dateString))->getTimestamp()*1000);
	}

	/**
	 * Get current mongo date
	 * @return [type]     Mongo date
	 */
	public static function mongoDate()
	{
		return new UTCDateTime((new \DateTime(Helper::dateNow()))->getTimestamp()*1000);
	}

	/**
	 * Convert Mongo date to date time
	 * @param  [type]     $value [description]
	 * @return [type]            [description]
	 */
	public static function convertMongoDateToDateTime($value)
    {
        if(strlen($value) <= 0 || is_null($value)) {
            return null;
        }
        $time = (string)$value;
        $timestamp = (int)$time;
        $utcdatetime = new UTCDateTime($timestamp);

        $datetime = $utcdatetime->toDateTime();
        return $datetime;
    }

    /**
     * Get value is instance of UTCDateTime
     * @param  [type]     $value [description]
     * @return [type]     datetime or null
     */
	public static function mongoDateToDateTime($value)
	{
		// Convert UTCDateTime instances.
		if ($value instanceof UTCDateTime) {
			return Carbon::createFromTimestamp($value->toDateTime()->getTimestamp());
		}
		return null;
	}



	/**
	 * Convert date string to carbon
	 * @param  [type]     $dateString
	 * @return [type]     date
	 */
	public static function dateStringToCarbon($dateString)
	{
		$timezone = new \DateTimeZone(env('TIMEZONE','Asia/Tokyo'));
		$format = 'Y-m-d H:i:s';
		$dateTime = \DateTime::createFromFormat($format, $dateString, $timezone);
		return Carbon::instance($dateTime);
	}


	/**
	 * Find a randomDate between $start_date and $end_date
	 * @param  [type]     $startDate [string|datetime]
	 * @param  [type]     $endDate   [string|datetime]
	 * @return [type]     false|string
	 */
	public static function randomDate($startDate, $endDate)
	{
		// Convert to timetamps
		$min = strtotime($startDate);
		$max = strtotime($endDate);

		$daystep = 86400;       // 1 day
		$datebetween = abs(($max - $min) / $daystep);
		$randomday = random_int(0, $datebetween);

		// Convert back to desired date format
		return date('Y-m-d', $min + $randomday * $daystep);
	}

	/**
	 * Get array day of week
	 * @return [type] array
	 */
	public static function dayOfWeek()
	{
		$dayArr = ['全て', '日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
		return $dayArr;
	}

	/**
	 * Get day of week by id
	 * @param  [type]     $id [description]
	 * @return [type]     day name
	 */
	public static function getDayName($id)
	{
		$dayArr = ['全て', '日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
		return $dayArr[$id];
	}

	/**
	 * Get default date time
	 * @return default date time
	 */
	public static function getDefaultDateTime()
	{
		return '0000-00-00 00:00:00';
	}
}
