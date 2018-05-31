<?php
namespace App\Libs;

use App\AppTraits\SettingTrait;
use Cache;
use Session;
use Log;
use Request;

Class SessionCacheHelper
{
	use SettingTrait;

	/**
	 * Set cache with time
	 * @param  [type]     $key    [cache key]
	 * @param  [type]     $value  [cache value]
	 * @param  [type]     $minute [time]
	 */
	public static function setCache($key, $value, $minute)
	{
		$expiresAt = Helper::dateAddMinute($minute);
		Cache::put($key, $value, $expiresAt);
		return true;
	}

	/**
	 * Get cache value
	 * @param  [type]     $key [cache key]
	 * @return [type]     cache value or null
	 */
	public static function getCache($key)
	{
		if (Cache::has($key)) {
			return Cache::get($key);
		}
		return null;
	}

	/**
	 * Check cache exist or not
	 * @param  [type]     $key [cache key]
	 * @return [type]     true or false
	 */
	public static function checkCache($key)
	{
		if (Cache::has($key)) {
			return true;
		}
		return false;
	}

	/**
	 * Forget cache with key
	 * @param  [type]     $key [cache key]
	 * @return [type]     true or false
	 */
	public static function forgetCache($key)
	{
		Cache::forget($key);
		return true;
	}

	/**
	 * Clear all cache
	 * @return [type]     true
	 */
	public static function cacheClearAll()
	{
		Cache::flush();
		return true;
	}

	/**
	 * Cache increase
	 * @param  [type]     $key [cache key]
	 * @param  integer    $i   [description]
	 * @return [type]     true
	 */
	public static function cacheIncrease($key, $i = 1)
	{
		Cache::increment($key, $i);
		return true;
	}

	/**
	 * Hit cache
	 * @param  [type]     $key          [cache key]
	 * @param  integer    $decayMinutes [description]
	 * @return [type]     number
	 */
	public static function hitCache($key, $decayMinutes = 1)
	{
		Cache::add($key, 0, $decayMinutes);
		return (int) Cache::increment($key);
	}

	/**
	 * Set session
	 * @param  [type]     $key   [session key]
	 * @param  [type]     $value [session value]
	 */
	public static function setSession($key, $value)
	{
		Session::put($key, $value);
		return true;
	}

	/**
	 * Get session from key
	 * @param  [type]     $key [session key]
	 * @return [type]     session value or null
	 */
	public static function getSession($key)
	{
		if(Session::has($key)) {
			return Session::get($key);
		}
		return null;
	}

	/**
	 * Forget session
	 * @param  [type]     $key [session key]
	 * @return [type]     true
	 */
	public static function forgetSession($key)
	{
		Session::forget($key);
		return true;
	}
}
