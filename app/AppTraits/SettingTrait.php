<?php
namespace App\AppTraits;

use App\Libs\Helper;
use App\Models\Setting;
use App\Models\Log;
use MongoDB\BSON\ObjectID;

trait SettingTrait
{
    public $ERROR_DEFAULT                           = '000-001';
    public $CATCH_EXCEPTION                         = '000-002';

    // Common error code
    public $ERROR_SERVER_MAINTENANCE                = '001-001';
    public $NEW_VERSION_UPDATE                      = '001-002';
    public $ERROR_FORCE_UPDATE                      = '001-003';
    public $ERROR_DATA_NOT_FOUND                    = '001-004';
    public $ERROR_INVALID_PARAM                     = '001-005';

    // User error code
    public $ERROR_USER_INVALID_PARAM                = '002-001';
    public $ERROR_USER_INVALID_DATA                 = '002-002';
    public $ERROR_USER_LOGIN_FAILED                 = '002-003';
    public $ERROR_USER_UPDATE_FAILED                = '002-004';
    public $ERROR_USER_NOT_FOUND                    = '002-005';
    public $ERROR_USER_TOKEN_EXPIRED                = '002-006';
    public $ERROR_USER_TOKEN_REQUIRE                = '002-007';
    public $ERROR_USER_LOGIN_ID_EXISTED             = '002-008';
    public $ERROR_PASSWORD_NOT_MATCH                = '002-009';
    public $ERROR_USER_HAS_BEEN_EXIST               = '002-010';


    public $NORMAL                  = 200;
    public $UNAUTHORIZED            = 401;
    public $FORBIDDEN               = 403;
    public $NOT_FOUND               = 404;
    public $SERVICE_UNAVAILABLE     = 503;
    public $DATA_INVALID            = 421;
    public $INTERNAL                = 500;
    public $FORCE_UPDATE            = 426;
    public $SERVER_MAINTENANCE      = 503;

    /**
     * Get all setting
     * @return [type]     $setting
     */
    public function traitGetAllSetting()
    {
        $cacheKey = 'setting'.env('CACHE_KEY');
        $setting = Helper::getCache($cacheKey);
        if(is_null($setting)) {
            $setting = Setting::getSetting();
            Helper::setCache($cacheKey, $setting, 1440);
        }
        return $setting;
    }

    /**
     * Get setting by key
     * @param  [type]     $key     [setting key]
     * @param  [type]     $default
     * @param  [type]     $setting
     * @return [type]     $setting value
     */
    public function traitGetSetting($key, $default=null, $setting = null)
    {
        if(is_null($setting)) {
            $setting = $this->traitGetAllSetting();
        }
        return isset($setting[$key]) ? $setting[$key] : $default;
    }

    /**
     * Get object id
     * @author baohq
     * @date   2017-09-28
     * @param  [type]     $value [description]
     * @return [type]            [description]
     */
    public function getObjectId($value)
    {
        if(preg_match('/^[a-f\d]{24}$/i',$value)) {
            return new ObjectID($value);
        }
        return null;
    }

}
