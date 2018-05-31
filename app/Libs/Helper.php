<?php
namespace App\Libs;

use App\AppTraits\SettingTrait;
use App\Models\MailTemplate;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Cache;
use Mail;
use MongoDB\BSON\UTCDateTime;
use Session;
use Log;
use Request;
use File;
use DateTime;

Class Helper
{
	use SettingTrait;
	public $frontend = 'frontend';
	public $backend  = 'backend';
	public $api  = 'api';

	/**
	 * Get value between 2 text in string xxx
	 * @param  [type]     $firstStr [description]
	 * @param  [type]     $lastStr  [description]
	 * @param  [type]     $string   [description]
	 * @return [type]     string    [description]
	 */
	public static function getStringBetween($firstStr, $lastStr, $string)
	{
		$temp1 = strpos($string, $firstStr) + strlen($firstStr);
		$result = substr($string, $temp1, strlen($string));
		$value=strpos($result, $lastStr);
		if($value == 0) {
			$value = strlen($result);
		}
		return substr($result, 0, $value);
	}

	/**
	 * Compare two string
	 * @param  [type]     $string1 [description]
	 * @param  [type]     $string2 [description]
	 * @return [type]     boolean
	 */
	public static function compareTwoString($string1, $string2)
	{
		if(strcmp($string1, $string2) == 0)
			return true;

		return false;
	}

	/**
	 * Format currency
	 * @param  [type]     $number     [description]
	 * @param  boolean    $fractional [description]
	 * @return [type]     $number
	 */
	public static function formatCurrency($number, $fractional=false)
	{
		if ($fractional) {
			$number = sprintf('%.2f', $number);
		}
		while (true) {
			$replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
			if ($replaced != $number) {
				$number = $replaced;
			} else {
				break;
			}
		}
		return $number;
	}

	/**
	 * Convert string to array
	 * @param  [type]     $string [description]
	 * @param  [type]     $mark   [description]
	 * @return [type]     array
	 */
	public static function stringToArray($string, $mark)
	{
		$arr = explode($mark, $string);
		$arr = array_filter($arr);
		return $arr;
	}

	/**
	 * Convert array to string
	 * @param  [type]     $arr  [description]
	 * @param  [type]     $mark [description]
	 * @return [type]     string
	 */
	public static function arrayToString($arr, $mark)
	{
		$arr = array_filter($arr);
		$string =  implode($mark, $arr);
		return $string;
	}

	/**
	 * Get platform
	 * @param  [type]     $usrAgent [description]
	 * @return [type]               [description]
	 */
	public static function getPlatform($usrAgent = null)
	{
		if(is_null($usrAgent)) {
			$usrAgent = Request::server('HTTP_USER_AGENT');
			Log::info('Vi Null - User agent '.$usrAgent);
		}
		$agent = new Agent;
		if($agent->isRobot($usrAgent)) {
			return 4;
		}
		$platform = $agent->platform($usrAgent);
		if($platform == 'iOS' || $platform == 'IOS') {
			return env('IOS', 2);
		}
		elseif($platform == 'AndroidOS' || $platform == 'Android') {
			return env('ANDROID', 3);
		}
		else {
			return 1;
		}
	}

	/**
	 * Get Ip client
	 * @return [type]     [description]
	 */
	public static function getIpClient()
	{
		return Request::ip();
	}

	/**
	 * Get user agent
	 * @return [type]     [description]
	 */
	public static function getUserAgent()
	{
		return Request::server('HTTP_USER_AGENT');
	}

	/**
	 * Generate key
	 * @param  integer    $length [number]
	 * @return [type]     string key
	 */
	public static function genKey($length = 6)
	{
		$keyGen = "";
		$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHGKLMNPQSTUVWZYZ";
		for ($p = 0; $p < $length; $p++) {
			$keyGen .= $characters[mt_rand(0, strlen($characters)-1)];
		}
		return $keyGen;
	}

	/**
	 * Generate token
	 * @return [type]     string token
	 */
	public static function gentToken()
	{
		return md5(uniqid(mt_rand(), true));
	}

	/**
	 * Generate code
	 * @param  integer    $length [description]
	 * @return [type]     string key
	 */
	public static function generateCode($length = 4)
	{
		$keyGen = "";
		$characters = "0123456789";
		for ($p = 0; $p < $length; $p++) {
			$keyGen .= $characters[mt_rand(0, strlen($characters)-1)];
		}
		return $keyGen;
	}

	/**
	 * Get debug mode
	 * @return [type]     true or false
	 */
	public static function debugMode()
	{
		return env('APP_DEBUG', false);
	}

	/**
	 * Encrypt Md5
	 * @param  [type]     $value [string]
	 * @return [type]     md5 string
	 */
	public static function encryptMd5($value)
	{
		return hash('md5', $value);
	}

	/**
	 * Examine of a string is null or empty value.
	 *
	 * @param string|null $text
	 * @param bool $ignoreWhiteSpace
	 * @return bool
	 */
	public static function isNullOrEmpty($text, $ignoreWhiteSpace = true)
	{
		if(is_null($text)) {
			return true;
		} else if(is_string($text)) {
			if($ignoreWhiteSpace) {
				$text = trim($text);
			}
			return empty($text);
		} else { // Non-string input
			throw new InvalidArgumentException();
		}
	}

	/**
	 * Get root of URL
	 * @param  [type]     $url      [url]
	 * @param  integer    $numParam
	 * @return [type]
	 */
	public static function getRootUrl($url, $numParam = 0)
	{
		$array = Helper::stringToArray($url, '/');
		$array = array_slice($array, 0, count($array) - $numParam);
		return Helper::arrayToString($array, '/');
	}

	/**
	 * Get sub URL
	 * @param  [type]     $url
	 * @param  integer    $start
	 * @param  integer    $numParam
	 * @return [type]
	 */
	public static function getSubUrl($url, $start = 0, $numParam = 0)
	{
		$array = Helper::stringToArray($url, '/');
		$array = array_slice($array, $start, count($array) - $numParam);
		return Helper::arrayToString($array, '/');
	}

	/**
	 * Get setting by key
	 * @param  [type]     $keyString [setting key]
	 * @param  [type]     $default
	 * @return [type]     setting value
	 */
	public static function getSettingByKey($keyString, $default = null)
	{
		$setting = Setting::where('key', $keyString)
							->where('status', 1)
							->first();
		if(!empty($setting)) {
			return $setting->value;
		}
		return $default;
	}

	/**
	 * Get content between two character
	 * @param  [type]     $content
	 * @param  [type]     $start
	 * @param  [type]     $end
	 * @return [type]     '' or string
	 */
	public static function getBetweenContent($content, $start, $end)
	{
		$r = explode($start, $content);
		if (isset($r[1])) {
			$r = explode($end, $r[1]);
			return $r[0];
		}
		return '';
	}

	/**
	 * Merge 2 array of strings
	 * @param  array      $array1 [array]
	 * @param  array      $array2 [array]
	 * @return [type]
	 */
	public static function mergeArrayString(array $array1, array $array2){
		return array_keys(array_merge(array_flip($array1), array_flip($array2)));
	}

	/**
     * Get Frontend prefix name
     * @author baohq
     * @date   2017-09-26
     * @return string 	$prefix
     */
    public static function getFePrefix()
    {
        $prefix = config('site.frontend_prefix', 'fe');
        return $prefix;
    }

    /**
     * Get Frontend URL
     * @author baohq
     * @date   2017-10-03
     * @param  string     $path
     */
    public static function getFeUrl($path='')
    {
        $prefix = config('site.frontend_prefix', 'fe');
        return '/' . $prefix . '/' . $path;
    }

	/**
     * Get Backend prefix name
     * @author baohq
     * @date   2017-09-26
     * @return string     $prefix
     */
    public static function getBePrefix()
    {
        $prefix = config('site.backend_prefix', 'be');
        return $prefix;
    }

	/**
     * Get Backend URL
     * @author baohq
     * @date   2017-10-03
     * @param  string     $path
     */
    public static function getBeUrl($path='')
    {
        $prefix = config('site.backend_prefix', 'be');
        return '/' . $prefix . '/' . $path;
    }
}
